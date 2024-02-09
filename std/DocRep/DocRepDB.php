<?php

include_once( SEEDROOT."DocRep/DocRepDB.php" );

/*
$_FILES[foo]['type'] is the mimetype if the browser sends it
getimagesize() can tell the mimetype of supported image files

end of _insertDocData:
// write records to db, and put dxd here so at least we have non-orphaned records if file upload fails


Put permclass check in lots of places in DocRepDB


SEEDSession_Perms:DocRep:Admin permission overrides SEEDPerms - set bPermClass_allaccess=1

 */






define("DOCREP_DB_TABLE_DOCREP_DOCS",
"
CREATE TABLE docrep_docs (
    # Each row is a logical representation of a doc in the system. Contains non-versioned metadata and a reference
    # to the current version.

        _key        INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT,
        _created    DATETIME,
        _created_by INTEGER,
        _updated    DATETIME,
        _updated_by INTEGER,
        _status     INTEGER DEFAULT 0,

    name                    VARCHAR(200) NOT NULL,
    type                    VARCHAR(200) NOT NULL,      # TEXT, IMAGE, DOC, TEXTFRAGMENT, FOLDER, LINK, etc. U_* are user-defined types
    spec                    VARCHAR(200) DEFAULT '',    # user defined for searching, grouping, ordering, etc
    status                  ENUM('NEW','APPROVE','ACTIVE','INACTIVE','DELETED') NOT NULL DEFAULT 'NEW',
    maxVer                  INTEGER,                    # MAX(ver) FROM docrep_docdata for this document (saves a lookup on insertion of new versions)

    permclass               INTEGER NOT NULL,

-- (parent,siborder)==(0,0) is a set of uncontained docs
    docrep_docs_parent      INTEGER NOT NULL DEFAULT 0, # fk_docrep_docs: doc that is this doc's parent (not sure how KF handles circles)
    siborder                INTEGER NOT NULL DEFAULT 0,

    INDEX (name(20)),
    INDEX (docrep_docs_parent)
);
"
);


define("DOCREP_DB_TABLE_DOCREP_DOC_X_DATA",
"
CREATE TABLE docrep_docxdata (
    # Join docs and docdata through a screen of flags. This allows particular versions of a doc to be flagged for
    # workflow, or other purposes.
    # Extensible: any number of flags can be attached to any version, and moved between versions atomically.
    # Efficient indexing of flagged versions: since the number of flags is probably much less than the number of versions,
    # this allows highly efficient lookup of docdata for the most likely desired versions.

        _key        INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT,
        _created    DATETIME,
        _created_by INTEGER,
        _updated    DATETIME,
        _updated_by INTEGER,
        _status     INTEGER DEFAULT 0,

    fk_docrep_docs          INTEGER NOT NULL,   # denormalized from docrep_docdata for very efficient lookups of flagged versions
    fk_docrep_docdata       INTEGER NOT NULL,
    flag                    VARCHAR(200) NOT NULL,

    INDEX (fk_docrep_docs),
    INDEX (fk_docrep_docdata)
);
"
);


define("DOCREP_DB_TABLE_DOCREP_DOCDATA",
"
CREATE TABLE docrep_docdata (
    # Each row is a version of a document's data and metadata.

        _key        INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT,
        _created    DATETIME,
        _created_by INTEGER,
        _updated    DATETIME,
        _updated_by INTEGER,
        _status     INTEGER DEFAULT 0,

    fk_docrep_docs      INTEGER NOT NULL,               # the document of which this is a version
    ver                 INTEGER NOT NULL DEFAULT 1,
    src                 ENUM('TEXT','FILE','SFILE') NOT NULL,
    data_text           TEXT NULL,                      # src=TEXT ? the text is stored here
    data_fileext        VARCHAR(20) NULL,               # src=FILE ? the file is stored as {_key}.{data_fileext}
    sfile_name          VARCHAR(200) NULL,              # src=SFILE ? the filesystem name under the sfile root
    mimetype            VARCHAR(100) NULL,              # standalone docs should be served with this type in the http header
    verspec             VARCHAR(200) DEFAULT '',        # user defined for searching, grouping, ordering, etc
    meta_title          VARCHAR(200),
    meta_author         VARCHAR(200),
    meta_date           VARCHAR(200),
    meta_desc           TEXT,
    metadata            TEXT,                           # url-encoded

    link_doc            INTEGER,                        # LINK: the destination doc key

    INDEX (fk_docrep_docs)
);
"
);


/*
-- Create an initial doc (DocRepApp01 doesn't have a way to do this)

INSERT INTO docrep_docs    VALUES (1,NOW(),0,NOW(),0,0,
                                   'Top','FOLDER','','NEW',1,0,0,1);
INSERT INTO docrep_docdata VALUES (1,NOW(),0,NOW(),0,0,
                                   1,1,'TEXT','',NULL,NULL,'','','Top','','','','',0);



*/

include_once( STDINC."KeyFrame/KFRelation.php" );


define( "DOCREP_TYPE_TEXT",         "TEXT" );
define( "DOCREP_TYPE_IMAGE",        "IMAGE" );
define( "DOCREP_TYPE_DOC",          "DOC" );
define( "DOCREP_TYPE_TEXTFRAGMENT", "TEXTFRAGMENT" );
define( "DOCREP_TYPE_FOLDER",       "FOLDER" );
define( "DOCREP_TYPE_LINK",         "LINK" );


define( "DOCREP_PARENT_DOCS", 0 );    // docs with this parent are the roots of the document forest
define( "DOCREP_PARENT_SFILE", -1 );  // docs with this parent are in the sfile set


/*****/

define( "PERMSR_SCOND_ERR", "1=0" );


function UrlParmsPack( $raParms )
/********************************
    Same as SEEDStd_ParmsRA2URL
    Return an urlencoded string containing the parms in the given array
 */
{
    $s = "";
    foreach( $raParms as $k => $v ) {
        if( !empty($s) )  $s .= "&";
        $s .= $k."=".urlencode($v);
    }
    return( $s );
}

function UrlParmsUnpack( $sUrlParms )
/************************************
    Same as SEEDStd_ParmsURL2RA
    Return an array containing the parms in the given urlencoded string
 */
{
    $raOut = array();
    if( !empty($sUrlParms) ) {   // the code below works properly with an empty string, but with display_errors turned on it throws a notice at the second explode
        $ra = explode( "&", $sUrlParms );
        foreach( $ra as $m ) {
            list($k,$v) = explode( '=', $m, 2 );
            if( $k )  $raOut[$k] = urldecode($v);
        }
    }
    return( $raOut );
}


class DocRepDB extends DocRep_DB
{
    protected /*private*/ $raDRDocs = array();

    // DocRepDoc wants to use these
    public $kfrelDocBase = NULL;
    public $kfrelDataBase = NULL;
    public $kfrelDXDBase = NULL;
    public $kfrelDoc_x_DXD_x_Data = NULL;
    public $kfrelData_x_DXD_x_Doc = NULL;
    public $kfrelDoc_x_Data = NULL;
    public $kfrelData_x_Doc = NULL;



    function __construct( KeyframeDatabase $kfdb, $uid, $parms = array() )
    {
        parent::__construct( $kfdb, $uid, $parms );
        $this->initKfrel();
    }


//TODO: pass a parm drStatus = "'NEW','ACTIVE'";  which goes in ...status IN ($drStatus)...
//      make this argument list extendible anyway
//      same parm needed anywhere the DocRep is queried, since we don't want non-admin people seeing metadata from deleted items
//      and get rid of ListChildren below

// use this throughout DocRepDB wherever any _load is done. The cache makes it worthwhile creating the object.
    function GetDocRepDoc( $sDoc )
    /*****************************
        Get a DocRepDoc by kDoc or name
     */
    {
        $oDRDoc = NULL;

        if( empty($sDoc) )  return( NULL );

        $kDoc = 0;

        if( is_numeric($sDoc) ) {
            $kDoc = intval($sDoc);
        } else {
            if( ($kfr = $this->kfrelDocBase->GetRecordFromDB( "name='".addslashes($sDoc)."'" )) ) {
                $kDoc = $kfr->Key();
            }
        }

        if( $kDoc ) {
            if( isset($this->raDRDocs[$kDoc] ) ) {
                $oDRDoc = &$this->raDRDocs[$kDoc];
            } else {
                $oDRDoc = new DocRepDoc( $this, $kDoc );
                if( $oDRDoc->GetKey() ) {
                    $this->raDRDocs[$kDoc] = &$oDRDoc;
                } else {
                    $oDRDoc = NULL;
                }
            }
        }
        return( $oDRDoc );
    }

    function GetSubtree( $kParent, $depth = -1, $raParms = array() )
    /***************************************************************
        Return an array tree of descendants of $kParent
            array( childFolder1 => array( 'visible' => true,
                                          'children' => array( grandchildFolder1 => array( 'visible' => false,
                                                                                           'children' => array( ggchildDoc1 => array( 'visible' => true,
                                                                                                                                      'children' => array() ) ) ),
                                                               grandchildDoc2 => array( 'visible' => true,
                                                                                        'children' => array() ) ),
                   childDoc2 => array( 'visible' => true,
                                       'children' => array() ) )
            where every key is an integer kDoc

        If a non-visible FOLDER doc contains a visible descendant, then the folder is returned but marked invisible.
        The 'visible' flag is stored for convenience to the caller because it's difficult to compute, but in practice it should be ignored since
        all returned nodes are "visible" according to the normal rules of DocRep trees.
        All other information can be obtained by getting a DocRepDoc for each node.

        depth==x:  descend x levels (e.g. 1 gets the children only)
        depth==-1: no limit to depth
        depth==0:  used internally to indicate that recursion has gone below depth to evaluate folder visibility

        *** The structure relies on the fact that PHP adds keys in the order you define them.
            So adding to the array folder 13, then folder 2, will give array(13 => array(children), 2 => array(children))
     */
    {
        $raRet = array();

        $bIncludeDeleted = SEEDStd_ArraySmartVal( $raParms, 'bIncludeDeleted', array(false,true) );

        /* Get all the children of kParent
         */
        $raChildren = array();
        $kfr = $this->kfrelDocBase->CreateRecordCursor( "docrep_docs_parent='$kParent'", array('sSortCol'=>'siborder') );
        while( $kfr && $kfr->CursorFetch() ) {
            $raChildren[] = $kfr->Key();
        }
        if( $kfr ) $kfr->CursorClose();

        /* For each child of kParent, expand visible subtrees and non-visible folders for visible children
         */
        foreach( $raChildren as $kDoc ) {
            if( !($oDoc = $this->GetDocRepDoc( $kDoc ) ) )  continue;   // happens if child is non-visible

            // optionally skip deleted docs/folders
            if( !$bIncludeDeleted && $oDoc->GetStatus() == 'DELETED' ) continue;

// Redundancy: the GetDocRepDoc uses GetSubtree to check for invisible child folders that contain visible descendants,
//             so by the time we get here this same code has already done the recursive steps on children to determine
//             that this doc is visible (otherwise it would have returned null above). That means we don't have to do all
//             the stuff below but it has to be there for GetDocRepDoc.  Maybe it's better for this descent check to just be
//             in PermsR_Okay?
            $bVisible = $oDoc->PermsR_Okay();
            if( !$bVisible && $oDoc->GetType() != 'FOLDER' )  continue;

            if( $bVisible ) {
                // This is a visible doc or folder.
                // If depth > 1 or -1 recurse normally.
                // If depth == 1 make all nodes at this level look like leaves (no children).
                // If depth == 0 we're looking below depth for a visible descendant of an invisible folder. Found it, so return 'visible' and no children.
                $raRet[$kDoc]['children'] = ( $depth > 1 || $depth == -1 )
                                            ? $this->GetSubTree( $kDoc, ($depth == -1 ? -1 : $depth - 1), $raParms )
                                            : array();
                $raRet[$kDoc]['visible'] = true;
            } else {
                // This is an invisible folder. Recurse to find a visible descendant, and go as deep as necessary to find one.
                // If the search goes below depth, only store the descendants of the required depth.
                //
                // If depth == 0 we're looking below depth for a visible descendant. Keep looking to the next level.
                //     If a child is returned (visible or invisible), it means some visible descendant was found. Return this folder, with no children,
                //     marked invisible so the parent recursion gets the same message.
                //     If no child is returned, it means no visible descendant was found. Do not return this folder, so the parent recursion gets the
                //     same message (unless there are visible siblings here).
                // If depth == 1 this is a leaf folder that will be either visible or invisible depending on the visibility of descendants. Check for
                //     visible descendants at depth=0.
                //     If a child is returned (visible or invisible), return this folder, with no children, marked invisible .  Else do not return this folder.
                // If depth > 1 or depth == -1 we're recursing normally. Get the folder's subtree at depth-1.
                //     If a child is returned (visible or invisible), return this folder, with children, marked invisible.
                //     If no child is returned, do not return the folder.
                //
                // In summary: if no child is returned, no visible descendant was found, so ignore this folder completely.
                //             if a child is returned, at depth > 1 or depth == -1 return this folder with children; at depth 0 or 1 return it with no children.
                //             always mark this folder as invisible.
                $raChildren = $this->GetSubTree( $kDoc, (($depth == -1 || $depth == 0) ? $depth : $depth - 1), $raParms );
                if( count($raChildren) ) {
                    $raRet[$kDoc]['children'] = ($depth == 0 || $depth == 1) ? array() : $raChildren;
                    $raRet[$kDoc]['visible'] = false;
                }
            }
        }
        return( $raRet );
    }

    private function initKfrel()
    {
        if( $this->kfrelDocBase )  return;  // already initialized

        $fldDoc = array( array("col"=>"name",                 "type"=>"S"),
                        array("col"=>"type",                 "type"=>"S"),
                         array("col"=>"spec",                 "type"=>"S"),
                         array("col"=>"status",               "type"=>"S"),
                         array("col"=>"maxVer",               "type"=>"I"),
                         array("col"=>"permclass",            "type"=>"I"),
                         array("col"=>"docrep_docs_parent",   "type"=>"K"),
                         array("col"=>"siborder",             "type"=>"I") );

        $fldDocData = array( array("col"=>"fk_docrep_docs",       "type"=>"K"),
                             array("col"=>"ver",                  "type"=>"I"),
                             array("col"=>"src",                  "type"=>"S"),
                             array("col"=>"data_text",            "type"=>"S"),
                             array("col"=>"data_fileext",         "type"=>"S"),
                             array("col"=>"sfile_name",           "type"=>"S"),
                             array("col"=>"mimetype",             "type"=>"S"),
                             array("col"=>"verspec",              "type"=>"S"),
                             array("col"=>"meta_title",           "type"=>"S"),
                             array("col"=>"meta_author",          "type"=>"S"),
                             array("col"=>"meta_date",            "type"=>"S"),
                             array("col"=>"meta_desc",            "type"=>"S"),
                             array("col"=>"metadata",             "type"=>"S"),
                             array("col"=>"link_doc",             "type"=>"I") );

        $fldDXD = array( array("col"=>"fk_docrep_docs",      "type"=>"K"),
                         array("col"=>"fk_docrep_docdata",   "type"=>"K"),
                         array("col"=>"flag",                "type"=>"S" ) );


        /*****
        Base defs
        */
        $kfdef_Doc_Base =
            array( "Tables"=>array( array( "Table" => 'docrep_docs',
                                           "Type"  => 'Base',
                                           "Fields" => $fldDoc ) ) );
        $kfdef_DocData_Base =
            array( "Tables"=>array( array( "Table" => 'docrep_docdata',
                                           "Type"  => 'Base',
                                           "Fields" => $fldDocData ) ) );
        $kfdef_DXD_Base =
            array( "Tables"=>array( array( "Table" => 'docrep_docxdata',
                                           "Type"  => 'Base',
                                           "Fields" => $fldDXD ) ) );

        /*****
        Join defs
        */
        // Doc x Data
        $kfdef_Doc_x_Data = $kfdef_Doc_Base;
        $kfdef_Doc_x_Data["Tables"][] =
            array( "Table"  => 'docrep_docdata',
                   "Type"   => 'Child',
                   "Alias"  => 'Data',
                   "Fields" => $fldDocData );

        // Data x Doc
        $kfdef_Data_x_Doc = $kfdef_DocData_Base;
        $kfdef_Data_x_Doc["Tables"][] =
            array( "Table"  => 'docrep_docs',
                   "Type"   => 'Parent',
                   "Alias"  => 'Doc',
                   "Fields" => $fldDoc );

        // Doc x DXD x Data
        $kfdef_Doc_x_DXD_x_Data = $kfdef_Doc_Base;
        $kfdef_Doc_x_DXD_x_Data["Tables"][] =
            array( "Table"  => 'docrep_docdata',
                   "Type"   => 'Child',
                   "Alias"  => 'Data',
                   "Fields" => $fldDocData );
        $kfdef_Doc_x_DXD_x_Data["Tables"][] =
            array( "Table"  => 'docrep_docxdata',
                   "Type"   => 'X',
                   "Alias"  => 'DXD',
                   "Fields" => $fldDXD );

        // Data x DXD x Doc
        $kfdef_Data_x_DXD_x_Doc = $kfdef_DocData_Base;
        $kfdef_Data_x_DXD_x_Doc["Tables"][] =
            array( "Table"  => 'docrep_docs',
                   "Type"   => 'Parent',
                   "Alias"  => 'Doc',
                   "Fields" => $fldDoc );
        $kfdef_Data_x_DXD_x_Doc["Tables"][] =
            array( "Table"  => 'docrep_docxdata',
                   "Type"   => 'X',
                   "Alias"  => 'DXD',
                   "Fields" => $fldDXD );

        $this->kfrelDocBase          = new KeyFrameRelation( $this->kfdb, $kfdef_Doc_Base,         $this->uid );
        $this->kfrelDataBase         = new KeyFrameRelation( $this->kfdb, $kfdef_DocData_Base,     $this->uid );
        $this->kfrelDXDBase          = new KeyFrameRelation( $this->kfdb, $kfdef_DXD_Base,         $this->uid );
        $this->kfrelDoc_x_Data       = new KeyFrameRelation( $this->kfdb, $kfdef_Doc_x_Data,       $this->uid );
        $this->kfrelData_x_Doc       = new KeyFrameRelation( $this->kfdb, $kfdef_Data_x_Doc,       $this->uid );
        $this->kfrelDoc_x_DXD_x_Data = new KeyFrameRelation( $this->kfdb, $kfdef_Doc_x_DXD_x_Data, $this->uid );
        $this->kfrelData_x_DXD_x_Doc = new KeyFrameRelation( $this->kfdb, $kfdef_Data_x_DXD_x_Doc, $this->uid );
    }


/* OLD OLD OLD OLD OLD
 */

    // internal record cache (guarantee integrity if not cleared after each top-level method)
// Don't want DocRepDB to be stateful - eliminate this and use DocRepDoc if you want to cache stuff
    var $kfrDoc = NULL;
    var $cachedDocFlag = "";

    var $kfrData = NULL;


// Deprecate - use GetSubtree instead
    function ListChildTree( $kParent, $flag, $depth = -1, $sCond = "", $raKFRParms = array() )
    /*****************************************************************************************
        Return an array of descendants of $kParent:
            array( kDoc => array( "visible"=>0|1,
                                  "doc"=>array("name"=>...,"type"=>...,"Data_title"=>...),  // Doc X Data fields
                                  "children"=> array( kDoc => array(), kDoc => array(), ... ) )
                   kDoc => array( ...

        Recurse into non-visible folders (even below depth) and make them visible if they contain visible elements.
        Don't leave cursors open during recursion.

        depth==x:  descend x levels (e.g. 1 gets the children only)
        depth==-1: no limit to depth
        depth==0:  used internally to indicate that recursion has gone below depth to evaluate folder visibility
     */
    {

// TODO: add ['ancestors'] => array( kDoc, kDoc,... ) to each node, which is easy to build on top of single GetDocAncestors()

        $raRet = array();

// this should be from a parm drStatus
        $sCondStatus = "(status in ('NEW','APPROVED','ACTIVE'))";
        if( $sCond ) {
            $sCond .= " AND $sCondStatus";
        } else {
            $sCond = $sCondStatus;
        }


        /* for each child of kParent, ordered by siborder, which is visible OR a folder
         */
        $ra = $this->_listChildren( $kParent, $flag, $sCond, $raKFRParms );
        foreach( $ra as $kDoc => $raDoc ) {
            // skip non-visible non-folders
            $bVisible = $this->_permsR_Okay($raDoc['permclass']);
            if( !$bVisible && $raDoc['type'] != 'FOLDER' )  continue;

            if( ($raRet[$kDoc]['visible'] = $bVisible) ) {
                // visible doc or folder. Record the doc data and recurse to its children.
                $raRet[$kDoc]['doc'] = $this->_xlatDocFieldNames( $raDoc );

                $raRet[$kDoc]['children'] = ( $depth > 1 || $depth == -1 )
                                          ? $this->ListChildTree( $kDoc, $flag, ($depth == -1 ? -1 : $depth - 1),
                                                                  $sCond, $raKFRParms )
                                          : array();
            } else {
                // invisible folder. Recurse to find a visible descendant, and go as deep as necessary to find one,
                // because an application might want to descend interactively one level at a time, until it reaches a
                // visible child.
                // If this goes below depth, the depth is 0 for all descending folders.
                $raChildren = $this->ListChildTree( $kDoc, $flag, (($depth == -1 || $depth == 0) ? $depth : $depth - 1),
                                                    $sCond, $raKFRParms );
//var_dump($raChildren);
                if( count($raChildren) ) {
                    $raRet[$kDoc]['visible'] = true;
                    $raRet[$kDoc]['doc'] = $this->_xlatDocFieldNames( $raDoc );
                    $raRet[$kDoc]['children'] = ($depth == 0 || $depth == 1) ? array() : $raChildren;
                } else {
                    // this invisible folder has no visible descendents; skip it
                    unset( $raRet[$kDoc] );
                }
            }
        }
//      print_r($raRet);
        return( $raRet );
    }


    private function _listChildren( $kParent, $flag, $sCond, $raKFRParms )
    /*********************************************************************
        Get all children of $kParent, version by $flag, filtered by $sCond, all perms, ordered by siborder

        Return array( kDoc => array( Doc X Data fields ), ...
        except Data_metadata is expanded from a1=b2&a2=b2 to array(a1=>b1,a2=>b2...)
     */
    {
        $raRet = array();

        if( !isset($raKFRParms['sSort']) ) {
            $raKFRParms['sSortCol'] = "siborder";
        }

//      if( ($sPermCond = $this->_permsR_sCond()) == PERMSR_SCOND_ERR )
//          return( array() );
//
//      if( !empty($sPermCond) ) {
//          $sCond = "($sPermCond)".(empty($sCond) ? "" : " AND ($sCond)");
//      }
        if( empty($flag) ) {
            /* Get the Doc and Data for maxVer of each child
             */
            $sCond = "docrep_docs_parent='$kParent' AND maxVer=Data.ver".(empty($sCond) ? "" : " AND ($sCond)");
            $kfr = $this->kfrelDoc_x_Data->CreateRecordCursor( $sCond, $raKFRParms );
        } else {
            /* Get the Doc and Data for the flagged DXD of each child
             */
            $sCond = "docrep_docs_parent='$kParent' AND DXD.flag='$flag'".(empty($sCond) ? "" : " AND ($sCond)");
            $kfr = $this->kfrelDoc_x_DXD_x_Data->CreateRecordCursor( $sCond, $raKFRParms );
        }

        while( $kfr && $kfr->CursorFetch() ) {
            $raRet[$kfr->Key()] = $kfr->_values;
            $raRet[$kfr->Key()]['Data_metadata'] = UrlParmsUnpack( $kfr->Value("Data_metadata") );  // transforms return value to an array of metadata parms
        }
        if( $kfr ) $kfr->CursorClose();
        return( $raRet );
    }


    function ListChildren( $keyParent, $flag, $sCond = "", $raKFRParms = array() )
    /*****************************************************************************
        Return an array of docs and metadata

        return array( kDoc => array( "name"=>*, "type"=>*, ..., "metadata"=>array( "foo"=>"bar" )
     */
    {
// TODO: replace this with ListChildTree( depth=1 ) since this doesn't do the right thing with invisible folders
        $raRet = array();
        if( ($sPermCond = $this->_permsR_sCond()) == PERMSR_SCOND_ERR )
            return( array() );

        if( !empty($sPermCond) ) {
            $sCond = "($sPermCond)".(empty($sCond) ? "" : " AND ($sCond)");
        }

        $ra = $this->_listChildren( $keyParent, $flag, $sCond, $raKFRParms );
        foreach( $ra as $k => $v ) {
            $raRet[$k] = $this->_xlatDocFieldNames( $v );
        }
        return( $raRet );
    }

    function ListSFileTree( $dir )
    /*****************************
        Return a tree of dirs and files below the given dir
        Non-perm files are excluded.
        Orphaned files (the file is in sfile but it has no object) are always included.
        Dirs have no perm info (no object) so they are only included if they contain a visible file (or a dir with a visible file).

        Return: array( 'dirs' => array( dirA => recursive return, dirB => recursive return, ... ),
                       'files' => array( nameA => array( 'obj' => oDoc ), nameB => array( 'obj' => oDoc ), ... ),
                     )
     */
    {
        $raOut = array( 'dirs' => array(), 'files' => array() );

        $dirSfile = STD_SCRIPT_REALDIR.$this->GetSFileDir()."/";

        if( $dir && substr( $dir, -1, 1 ) != '/' )  $dir = $dir.'/';

        /* Get all dirs and files in the current directory.
         * For each dir, recurse, store the returned array unless it's null
         * For each file, store the info about each visible file
         * If no dirs return non-null recursion and no visible files, return null
         */
        include_once( SEEDCORE."SEEDFile.php" );
        $oSEEDFile = new SEEDFile();
        $oSEEDFile->Clear();
        $oSEEDFile->Traverse( $dirSfile.$dir, array('eFetch'=>'DIR', 'bRecurse'=>false) );
        $raDirs = $oSEEDFile->GetTraverseItems();
        $oSEEDFile->Clear();
        $oSEEDFile->Traverse( $dirSfile.$dir, array('eFetch'=>'FILE', 'bRecurse'=>false) );
        $raFiles = $oSEEDFile->GetTraverseItems();

        foreach( $raDirs as $k => $ra ) {
            $sDirName = $ra[1];
            if( ($raD = $this->ListSFileTree( $dir.$sDirName )) ) {
                $raOut['dirs'][$sDirName] = $raD;
            }
        }
        foreach( $raFiles as $k => $ra ) {
            // if the file has an obj and it's visible, add it to the array
            // if the file has an obj and it's invisible, skip
            // if the file doesn't have an obj, add null to the array to indicate that it is orphaned
            $sName = $ra[1];
            $sFullName = $dir.$sName;
            if( ($oDoc = $this->GetDocObjectFromName($sFullName)) ) {
                // only works if file is visible re permclass
                $raOut['files'][$sName]['obj'] = $oDoc;
            } else if( ($kfr = $this->getDocKfrFromName($sFullName)) ) {    // use permclass-free method
                // so the object exists; must be invisible -- skip it
            } else {
                // the file doesn't have an object; we include this so the UI can choose to alert the user
                $raOut['files'][$sName]['obj'] = null;
            }
        }

        return( (count($raOut['dirs']) || count($raOut['files'])) ? $raOut : null );
    }

    function ListSFileFlat( $dir )
    /*****************************
        Return a list of the oSFiles that match the given dir, and have read perm.
        Unlike ListSFileTree(), this only searches the DocRep db, not the filesystem.
        The flat list is a list of filenames, not folders:
            where $dir=='a' : array( 'a/b.jpg' => obj, 'a/c.jpg' => obj, 'a/d/e.jpg' => obj )
     */
    {
        $raOut = array();

        if( ($kfr = $this->kfrelDocBase->CreateRecordCursor( "name like '".addslashes($dir)."%'",
                                                             array( "sSortCol"=>"name", "bSortDown"=>true,) )) )
        {
            while( $kfr->CursorFetch() ) {
                if( $this->_permsR_Okay( $kfr->Value('permclass') ) &&
                    ($oDoc = $this->GetDocObject( $kfr->Key()) ) )
                {
                    $raOut[$kfr->Value('name')] = $oDoc;
                }
            }
        }
        return( $raOut );
    }

    function _xlatDocFieldNames( $v )
    /********************************
     */
    {
        return( array( "name"      => $v["name"],
                       "type"      => $v["type"],
                       "spec"      => $v["spec"],
                       "status"    => $v["status"],
                       "parent"    => $v["docrep_docs_parent"],
                       "siborder"  => $v["siborder"],
                       "Data__key" => $v["Data__key"],
                       "ver"       => $v["Data_ver"],
                       "file_ext"  => $v["Data_data_fileext"],
                       "mimetype"  => $v["Data_mimetype"],
                       "title"     => $v["Data_meta_title"],
                       "desc"      => $v["Data_meta_desc"],
                       "author"    => $v["Data_meta_author"],
                       "date"      => $v["Data_meta_date"],
                       "metadata"  => $v["Data_metadata"]
                     ) );
    }



    function GetDocFromName( $sName )
    /********************************
        Return kDoc of the document with the given name.  Plain integer names are converted to intval.
     */
    {
// TODO: allow invisible folder if contains visible item
        if( empty($sName) )       return( 0 );

        $kDoc = 0;

        if( is_numeric($sName) ) {
            $kDoc = intval($sName);
            if( !$this->_loadDoc( $kDoc, "" ) ) $kDoc = 0;	 // flag="" because name is non-versioned
        } else {
            if( ($kfr = $this->getDocKfrFromName($sName)) &&
                $this->_permsR_Okay( $kfr->Value('permclass') ) )
            {
                $kDoc = $kfr->Key();
            }
        }
        return( $kDoc );
    }

    function GetDocObject( $kDoc )
    /*****************************
     */
    {
        $o = new DocRepDoc( $this, $kDoc );
        return( $o );
    }

    function GetDocObjectFromName( $sName )
    /**************************************
        Not necessarily the most efficient way to get this, but the easiest.
     */
    {
        return( ($kDoc = $this->GetDocFromName( $sName )) ? $this->GetDocObject( $kDoc ) : NULL );
    }

    private function getDocKfrFromName( $sName )
    /*******************************************
        Find a document without a permclass check, for internal use only.
     */
    {
        return( $this->kfrelDocBase->GetRecordFromDB( "name='".addslashes($sName)."'" ) );
    }

    function InsertDoc( $docType, $eSrcType, $src, $parms = array() )
    /****************************************************************
     */
    {
        $k = $this->Insert( 0, $docType, $eSrcType, $src, $parms );
        return( $k ? new DocRepDoc( $this, $k ) : NULL );
    }

    function InsertDocFolder( $parms = array() )
    /*******************************************
     */
    {
        $k = $this->InsertFolder( 0, $parms );
        return( $k ? new DocRepDoc( $this, $k ) : NULL );
    }

    function GetDocName( $kDoc )
    /* Return the given doc's name
     */
    {
        if( !$this->_loadDoc( $kDoc, "" ) ) return( "" );	// flag="" because name is non-versioned
        return( $this->kfrDoc->value('name') );
    }

//not used?
    function GetFirstChild( $kDoc )
    /******************************
        Return kDoc of the first (readable) child of $kDoc
     */
    {
        $ret = 0;

// verify that $kDoc is readable

        // get the child with the lowest siborder
// AND permclass in $this->raPermsR
// TODO: allow invisible folder if contains visible item
        $kfr = $this->kfrelDocBase->CreateRecordCursor( "docrep_docs_parent='$kDoc'",
                                                        array( "sSortCol"=>"siborder", "bSortDown"=>false, "iLimit"=> 1 ));
        return( ($kfr && $kfr->CursorFetch()) ? $kfr->Key() : 0 );
    }

    function GetDocInfo( $kDoc, $flag )
    /**********************************
     */
    {
        //TODO: maybe these should go through some further transformation, through a standard key filter like _xlatDocFieldNames
        if( ($raOut = ($this->_loadDoc( $kDoc, $flag ) ? $this->kfrDoc->_values : array())) ) {
            $raOut['Data_metadata'] = UrlParmsUnpack($raOut['Data_metadata']);  // transform to an array of parms
        }
        return( $raOut );
    }

    function GetDocAncestors( $kDoc )
    /********************************
        Return a list of all ancestors including kDoc but not 0
        kDoc is the first element, the tree root is the last element.
     */
    {
        $raAncestors = array();
        while( $kDoc ) {
// TODO: allow invisible folder if contains visible item

            list($kParent,$permclass) = $this->kfdb->KFDB_QueryRA( "SELECT docrep_docs_parent,permclass FROM docrep_docs WHERE _key='$kDoc'" );

            if( $this->_permsR_Okay( $permclass ) ) {
                $raAncestors[] = $kDoc;
            }
            $kDoc = intval($kParent);
        }
        return( $raAncestors );
    }

    function GetDocAsStr( $kDoc, $flag )
    /***********************************
     */
    {
        $s = "";

        if( !$this->_loadDoc( $kDoc, $flag ) ) return( "" );

        switch( $this->kfrDoc->Value("Data_src") ) {
            case "TEXT":
                $s = $this->kfrDoc->Value("Data_data_text");
                break;
            case "FILE":
            case "SFILE":
                if( ($fp = fopen( $this->getDataFilenameUsingDoc(), "rb" )) ) {
                    $s = fread( $fp );
                    fclose( $fp );
                }
                break;
        }
        return( $s );
    }

    function ServeDoc( $kDoc, $flag, $bExpressContentType = true )
    /*************************************************************
        Serves the current public version of a document to stdout
     */
    {
/*<?php
$file = 'monkey.gif';

if (file_exists($file)) {
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename='.basename($file));
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($file));
    ob_clean();
    flush();
    readfile($file);
    exit;
}
?>
*/
        $raRet = array();

        if( !$this->_loadDoc( $kDoc, $flag ) ) return;

//        header( "Cache-Control: max-age=0, no-cache, no-store" );
//        header( "Pragma: no-cache" );

        if( $bExpressContentType && !$this->kfrDoc->IsEmpty("Data_mimetype") ) {
            header( "Content-type: ".$this->kfrDoc->Value("Data_mimetype") );
            // use inline instead of attachment to make simple graphics (jpeg) appear in the browser instead of
            // a separate viewer. This seems to be true for FireFox and IE.
            header( "Content-disposition: inline; filename=\"".$this->kfrDoc->Value("name")."\"" );
        }

        switch( $this->kfrDoc->Value("Data_src") ) {
            case "TEXT":
                echo $this->kfrDoc->Value("Data_data_text");
                break;
            case "FILE":
            case "SFILE":
                if( ($fp = fopen( $this->getDataFilenameUsingDoc(), "rb" )) ) {
                    fpassthru( $fp );
                    fclose( $fp );
                }
                break;
        }
    }

    function getDataFilenameUsingDoc()
    /*********************************
        With a valid kfrDoc, get the filename of the data (for src == FILE or SFILE)
     */
    {
        switch( $this->kfrDoc->Value("Data_src") ) {
            case "FILE":    return( DOCREP_UPLOAD_DIR.$this->kfrDoc->Value("Data__key").".".$this->kfrDoc->Value("Data_data_fileext") );
            case "SFILE":   return( $this->GetSFileFilename( $this->kfrDoc->Value("Data_sfile_name") ) );
            case "TEXT":
            default:        return( "" );
        }
    }

    function getDataFilenameUsingData( $kfrData = NULL )
    /***************************************************
        With a valid kfrData, get the filename of the data (for src == FILE or SFILE)
     */
    {
        if( $kfrData == NULL )  $kfrData = $this->kfrData;

        return( $this->GetDataFilename( $kfrData ) );
    }

    function GetDataFilename( $kfrData, $bReal = false )
    /***************************************************
        With a valid kfrData, get the filename of the data (for src == FILE or SFILE)
     */
    {
        switch( $kfrData->Value("src") ) {
            case "FILE":    return( ($bReal ? DOCREP_UPLOAD_REALDIR : DOCREP_UPLOAD_DIR).$kfrData->Key().".".$kfrData->Value("data_fileext") );
            case "SFILE":   return( $this->GetSFileFilename( $kfrData->Value("sfile_name"), $bReal ) );
            case "TEXT":
            default:        return( "" );
        }
    }

    function GetSFileDir( $bReal = false )
    /*************************************
     */
    {
        return( ($bReal ? DOCREP_UPLOAD_REALDIR : DOCREP_UPLOAD_DIR)."sfile" );
    }

    function GetSFileFilename( $sfile_name, $bReal = false )
    /*******************************************************
       Return the real filename of a named sfile
     */
    {
        return( $this->GetSFileDir($bReal)."/".$sfile_name );
    }

    function SFileIsFile( $sfile_name )    { return( file_exists( $this->GetSFileFilename( $sfile_name ) ) ); }
    function SFileIsDir( $sfile_name )     { return( is_dir( $this->GetSFileFilename( $sfile_name ) ) ); }


    function Insert( $kDoc, $docType, $eSrcType, $src, $parms = array() )
    /********************************************************************
        kDoc =  0 : insert new doc
        kDoc != 0 : update doc

        docType = TEXT | IMAGE | DOC | TEXTFRAGMENT | FOLDER | LINK | U_* (user type treated like DOC)
        docType = '' is allowed for updates
        eSrcType = TEXT | FILE | SFILE
        src     = eSrcType=TEXT: the content | eSrcType=FILE: the temp file name | eSrcType=SFILE: the static file name | docType=LINK: dest kDoc

        parms['dr_name'] = the user name of the doc
        parms['dr_spec']    = user string for searching, grouping, etc - for the document (applies to all versions)
        parms['dr_verspec'] = user string for searching, grouping, etc - for the version
        parms['dr_flag'] = the flag associated with the new version
        parms['dr_permclass'] = integer permclass
        parms['dr_mimetype'] = the mime type
        parms['dr_fileext'] = the file extension

        Control parms:
        parms['dr_bEraseOldVersion']            mainly for use with FILE to delete old files to save disk space (new data goes in a new data record)
        parms['dr_bReplaceCurrVersion']         mainly for use with TEXT for minor updates that don't preserve current version (new data overwrites current data record)
        parms['dr_posUnder']  = kDoc of parent (make this doc the first child)
        parms['dr_posAfter'] = kDoc of sibling (make this doc the next sibling)

        Standard Metadata:
        parms['dr_title']
        parms['dr_desc']
        parms['dr_author']
        parms['dr_date']

        User Metadata:
        parms['dr_metadata'][]  // not implemented, undefined whether these override or totally replace existing metadata
     */
    {
        $this->kfrDoc = $this->kfrData = NULL;      // clear the internal record cache

        if( $kDoc ) {
            if( !($this->kfrDoc = $this->kfrelDocBase->GetRecordFromDBKey( $kDoc )) ) {
                die( "Can't find doc record $kDoc");
            }
            if( empty($docType) ) {
                $docType = $this->kfrDoc->value('type');
            } else {
                if( $docType != $this->kfrDoc->value('type') )  die( "DocRep Type mismatch on insert" );
            }
        }

        if( $docType == "FOLDER" || $docType == "LINK" ) {
            $eSrcType = "TEXT";     // there is no storage for these types, but normalize to this value to be tidy
        } else if( !in_array( $eSrcType, array('TEXT', 'FILE', 'SFILE') ) ) {
            die( "Invalid insertion srcType" );
        }

        /* Create a new docdata row, and a new doc row if needed
         */
        if( !$this->_insertDocData( $kDoc, $docType, $eSrcType, $parms ) )  return( NULL );
        if( !($kDoc = $this->kfrDoc->Key()) )  return( NULL );

        /* The new data row is written, but empty. If inserting a new doc, the new doc row is written, but empty.
         * Key() gives the correct value for each.
         * Now put content in the new data row and rewrite it.
         * Finally, update and write/rewrite the doc row.
         * This puts the doc in a stable state. An existing doc is not affected by fatal errors during update
         * (e.g. move_uploaded_file fails), because old doc records are only updated at the very end.
         */
        switch( $docType ) {
            case "FOLDER":
                break;
            case "LINK":
                $this->kfrData->SetValue( "link_doc", intval($src) );
                break;

            default:
                switch( $eSrcType ) {
                    case "TEXT":
                        $this->kfrData->SetValue( "data_text", $src );
                        break;
                    case "FILE":
                    case "SFILE":
                        $this->_insertFile( $eSrcType, $src, $parms );
                        break;
                }
        }

        if( !$this->kfrData->PutDBRow() )   die( "Cannot rewrite data row" );
        if( !$this->kfrDoc->PutDBRow() )    die( "Cannot rewrite doc row" );
        $this->_insertDXD( $parms );

        // kluge: ideally, the doc and data records should be cached for later use but this method doesn't
        //        do the same thing as _loaddoc(), because it only stores the Base record fields.
        //        i.e. kfrDoc->Value('Data_*') are blank, whereas _loaddoc() retrieves those fields
        //        So clear these caches so future lookups will cause _loaddoc to do the right thing.
        $k = $this->kfrDoc->Key();
        $this->kfrDoc = $this->kfrData = NULL;
        return( $k );
        // return( $this->kfrDoc->Key() );
    }

    function InsertFolder( $kDoc, $parms = array() )
    /***********************************************
     */
    {
        return( $this->Insert( $kDoc, "FOLDER", "", "", $parms ) );
    }

    function InsertLink( $kDoc, $parms = array() )
    /*********************************************
     */
    {
        return( $this->Insert( $kDoc, "LINK", "", "", $parms ) );
    }

    function InsertFile( $kDoc, $docType, $tmp_fname, $parms = array() )
    /*******************************************************************
     */
    {
        return( $this->Insert( $kDoc, $docType, "FILE", $tmp_fname, $parms ) );
    }

    function InsertSFile( $kDoc, $docType, $fname, $parms = array() )   // deprecated!
    /****************************************************************
     */
    {
        return( $this->Insert( $kDoc, $docType, "SFILE", $fname, $parms ) );
    }

    function InsertText( $kDoc, $docType, $sText, $parms = array() )
    /***************************************************************
     */
    {
        return( $this->Insert( $kDoc, $docType, "TEXT", $sText, $parms ) );
    }

    private function _insertDocData( $kDoc, $docType, $eSrcType, $parms )
    /********************************************************************
        $docType is stored on insertion only, and never changes for later versions
     */
    {
        $bInsertion = ($kDoc == 0);
        $bReuseDataRecord = ($kDoc && @$parms['dr_bReplaceCurrVersion']);
        $raCopy = array();


        if( $kDoc ) {
            // If updating, get the current record for copying or reuse
            $this->kfrData = $this->kfrelDataBase->GetRecordFromDB( "fk_docrep_docs='$kDoc' AND "
                                                                      ."ver='".$this->kfrDoc->Value('maxVer')."'" );
            if( $this->kfrData ) {
                $raCopy = $this->kfrData->_values;
            }
        }

        if( $bReuseDataRecord ) {
            if( !$this->kfrData || $this->kfrData->value('src') != 'TEXT' ) {
                // *** ReuseDataRecord only implemented if the previous data is TEXT, because we haven't implemented FILE replacement.
                // *** Would need to delete old file transactionally, which means renaming it, writing the new file, and deleting (or restoring) the renamed file
                $bReuseDataRecord = false;
            }
        }
        if( !$this->kfrData || !$bReuseDataRecord ) {
            /* Create a new data record, fill it in and rewrite it.
             * N.B. A fatal error can leave this record orphaned.
             */
            $this->kfrData = $this->kfrelDataBase->CreateRecord();
        }

        $this->kfrData->SetValue( "src", $eSrcType );
        if( !$this->kfrData->PutDBRow() ) {
            die( "Error creating data row" );
        }


        if( $kDoc ) {
            /* Updating a document. The new data record is the next version of this document.
             */
            // $this->kfrDoc has already been fetched
            if( !$bReuseDataRecord ) {
                $nVer = $this->kfrDoc->value('maxVer') + 1;
                $this->kfrData->SetValue( "ver", $nVer );
                $this->kfrDoc->SetValue( "maxVer", $nVer );

                // copy the metadata from the old record and allow it to be overwritten by parms later
                $this->kfrData->SetValue( "verspec", $raCopy['verspec'] );
                $this->kfrData->SetValue( "meta_title", $raCopy['meta_title'] );
                $this->kfrData->SetValue( "metadata", $raCopy['metadata'] );
            }
        } else {
            /* Inserting a new document. Create a new doc record and link it to the data record.
             */
            $this->kfrDoc = $this->kfrelDocBase->CreateRecord();
            $this->kfrDoc->SetValue( "type", $docType );
            $this->kfrDoc->SetValue( "status", "NEW" );
            $this->kfrDoc->SetValue( "maxVer", 1 );
            $this->kfrData->SetValue( "ver", 1 );
            if( !$this->kfrDoc->PutDBRow() ) {
                die( "Error creating doc row" );
            }
        }

        $this->kfrData->SetValue( "fk_docrep_docs", $this->kfrDoc->Key() );

        $retParms = $this->_processInsertParms( $parms );

        /* Set the parent/sib only if this is an insertion. This is not a 'move' function.
         */
        if( $bInsertion ) {
            $this->_insertSetParentSiborder( $retParms['posUnderParent'], $retParms['posAfterSibling'] );
        }

        return( true );
    }

    private function _processInsertParms( $parms )
    /*********************************************
        Use input parms to set kfrDoc and kfrData values.
        The following are set in retParms[]:
            posUnderParent
            posAfterSibling
     */
    {
        $retParms = array();
        $retParms['posUnderParent'] = $retParms['posAfterSibling'] = 0;

        foreach( $parms as $k => $v ) {
            switch( $k ) {
                case "dr_flag": break;
                case "dr_posUnder": $retParms['posUnderParent'] = $v;    break;
                case "dr_posAfter": $retParms['posAfterSibling'] = $v;   break;

                case "dr_name":     $this->kfrDoc->SetValue( "name", $v );          break;
                case "dr_spec":     $this->kfrDoc->SetValue( "spec", $v );          break;
                case "dr_permclass":$this->kfrDoc->SetValue( "permclass", $v );     break;
                case "dr_mimetype": $this->kfrData->SetValue( "mimetype", $v );     break;
                case "dr_verspec":  $this->kfrData->SetValue( "verspec", $v );      break;
                case "dr_title":    $this->kfrData->SetValue( "meta_title", $v );   break;
                case "dr_desc":     $this->kfrData->SetValue( "meta_desc", $v );      break;
                case "dr_author":   $this->kfrData->SetValue( "meta_author", $v );    break;
                case "dr_date":     $this->kfrData->SetValue( "meta_date", $v );      break;
                case "dr_metadata": $this->kfrData->SetValue( "metadata", UrlParmsPack( $v ) ); break;
            }
        }
        return( $retParms );
    }

    function _insertSetParentSiborder( $posUnderParent, $posAfterSibling )
    /*********************************************************************
     */
    {

// There is no way to insert at (0,1) - the first root position
// Haven't decided whether to allow uncontained documents (0,0)
        if( $posUnderParent ) {
            /* Insert the new document as the first sibling of the given parent
             */
            $i = intval( $this->kfdb->KFDB_Query1( "SELECT MIN(siborder) FROM docrep_docs WHERE docrep_docs_parent='$posUnderParent'" ) );
            if( $i == 1 ) {
                $this->kfdb->KFDB_Execute( "UPDATE docrep_docs SET siborder=siborder+1 WHERE docrep_docs_parent='$posUnderParent'" );
            }
            $this->kfrDoc->SetValue( "docrep_docs_parent", $posUnderParent );
            $this->kfrDoc->SetValue( "siborder", 1 );

        } else if( $posAfterSibling ) {
            /* Insert the new document as the next sibling after the given sibling
             */
            $ra = $this->kfdb->KFDB_QueryRA( "SELECT docrep_docs_parent as parent,siborder FROM docrep_docs WHERE _key='$posAfterSibling'" );
            $parent = intval($ra['parent']);
            $siborder = intval($ra['siborder']);
            if( $parent || $siborder ) {
                $this->kfdb->KFDB_Execute( "UPDATE docrep_docs SET siborder=siborder+1 WHERE docrep_docs_parent='$parent' and siborder > '$siborder'" );
                $this->kfrDoc->SetValue( "docrep_docs_parent", $parent );
                $this->kfrDoc->SetValue( "siborder", $siborder + 1 );
            } else {
                // ???  This condition should probably not happen, so not sure what to do.
                $this->kfrDoc->SetValue( "docrep_docs_parent", 0 );
                $this->kfrDoc->SetValue( "siborder", 0 );
            }
        } else {
            // ??? unspecified position - put it at 0,0 = uncontained documents

            $this->kfrDoc->SetValue( "docrep_docs_parent", 0 );
            $this->kfrDoc->SetValue( "siborder", 0 );
        }
    }

    function _insertDXD( $parms )
    /****************************
        Find or create the docxdata record for the given flag.
        If flag is blank, do not create a docxdata.
        kfrDoc and kfrData must be valid
     */
    {
        $kfrDXD = NULL;
        $flag = @$parms['dr_flag'];
        if( !empty($flag) ) {
            if( !($kfrDXD = $this->kfrelDXDBase->GetRecordFromDB( "fk_docrep_docs='".$this->kfrDoc->Key()."' AND flag='$flag'" )) ) {
                $kfrDXD = $this->kfrelDXDBase->CreateRecord();
                $kfrDXD->SetValue( "fk_docrep_docs", $this->kfrDoc->Key() );
                $kfrDXD->SetValue( "flag", $flag );
            }
            $kfrDXD->SetValue( "fk_docrep_docdata", $this->kfrData->Key() );
            if( !$kfrDXD->PutDBRow() )    die( "Cannot write dxd row" );
        }
    }

    function _insertFile( $eSrcType, $fname, $parms )
    /************************************************
        "FILE":  fname is the tmp uploaded file.  Calculate its file extension and mimetype, and move it to the DOCREP_UPLOAD_DIR
        "SFILE": fname is the static file in DOCREP_UPLOAD_DIR."sfile/".  Find its file ext and mimetype.
     */
    {
        global $fileExt2Mimetype;

        $fExt = @$parms['dr_fileext'];
        if( empty($fExt) && $eSrcType == "SFILE" )  $fExt = substr( strrchr( $fname, '.' ), 1 );
        if( empty($fExt) )  $fExt = substr( strrchr( $this->kfrDoc->value('name'), '.' ), 1 );
        if( !empty($fExt) ) $this->kfrData->SetValue( "data_fileext", $fExt );

        if( $this->kfrData->IsEmpty("mimetype") ) {
            $mimetype = @$fileExt2Mimetype[strtolower($this->kfrData->Value("data_fileext"))];
            if( empty($mimetype) ) {
                $mimetype = "application/octet-stream";
            }
            $this->kfrData->SetValue( "mimetype", $mimetype );
        }

        if( $eSrcType == "SFILE" ) {
            // all docs in the sfile set have this parent, to isolate them from the regular doc forest (rooted at DOCREP_PARENT_DOCS)
            $this->kfrDoc->SetValue( 'docrep_docs_parent', DOCREP_PARENT_SFILE );

            if( $fname ) {
                // a temp file was uploaded
                $this->kfrData->SetValue( "sfile_name", $this->kfrDoc->value('name') );
                if( !is_uploaded_file( $fname ) ) {
                    die( "File was not uploaded" );
                }
                if( !move_uploaded_file( $fname, $this->getDataFilenameUsingData() ) ) {
                    die( "Cannot move file" );
                }
            } else {
                // assume somebody put a file in the same place as the name
                $this->kfrData->SetValue( "sfile_name", $this->kfrDoc->value('name') );
            }
        } else {
            // FILE
            if( !is_uploaded_file( $fname ) ) {
                die( "File was not uploaded" );
            }
            if( !move_uploaded_file( $fname, $this->getDataFilenameUsingData() ) ) {
                die( "Cannot move file" );
            }
        }
    }

    function Rename( $kDoc, $parms, $flag = "", $raParms = array() )
    /***************************************************************
        $raParms: bRenameDescendants = rename all descendants with leading "path components" that match the old name
     */
    {
        if( @$raParms['bRenameDescendants'] ) {
            // get the name that will govern the descendants' folder-paths (i.e. the doc name, or the folder name if doc name is blank)
            $oDoc = new DocRepDoc( $this, $kDoc );
            $sOldRootName = $oDoc->GetName();
            if( empty($sOldRootName) ) {
                $sOldRootName = $oDoc->GetFolderName();  // this changes oDocRepDB->kfrDoc, so do it before the _loadDoc (until a better method is made)
            }
            $oDoc = NULL;
        }


        if( !$this->_loadDoc( $kDoc, $flag ) ) return( false );

// probably _loadData should get this in a conventional way
        if( !($this->kfrData = $this->kfrelDataBase->GetRecordFromDB( "fk_docrep_docs='$kDoc' AND "
                                                                         ."ver='".$this->kfrDoc->Value('maxVer')."'" )) ) {
            die( "Can't find maxVer data record for $kDoc");
        }

        $retParms = $this->_processInsertParms( $parms );
        if( !$this->kfrData->PutDBRow() )   die( "Cannot rewrite data row" );
        if( !$this->kfrDoc->PutDBRow() )    die( "Cannot rewrite doc row" );

        /* Recursively rename descendants if required
         */
        if( @$raParms['bRenameDescendants'] ) {
            // get the name that will govern the descendants' folder-paths (i.e. the doc name, or the folder name if doc name is blank)
            $oDoc = new DocRepDoc( $this, $kDoc );
            $sNewRootName = $oDoc->GetName();
            if( empty($sNewRootName) ) {
                $sNewRootName = $oDoc->GetFolderName();
            }
            $this->RenameDescendants( $kDoc, $sOldRootName, $sNewRootName );
        }

        // force reload next time, since kfrDoc's Data_* values might now be wrong
        $this->kfrDoc = $this->kfrData = NULL;

        return( true );
    }

    function RenameDescendants( $kDoc, $sOldFolderName, $sNewFolderName )
    /********************************************************************
        Given the old and new folder names of a doc (i.e. the closest named ancestors before and after a rename - which could be different
        ancestors if a rename changed a name from "" or to ""), rename all descendants so their names follow a folder-path convention.
        Ignore all descendants with blank names.
     */
    {
        $flag = "";  // do this for the current version (name and visibility is non-versioned)

        if( ($raChildren = $this->ListChildTree( $kDoc, $flag, 1 )) ) {  // 1 is just the current level: don't get a recursive tree because this function is recursive
            foreach( $raChildren as $kChild => $ra ) {
                $sOldName = @$ra['doc']['name'];
                if( !empty($sOldName) ) {
                	// rename the doc and its children
                    $sNewName = $this->makeNewNameAfterRenameOrMove( $sOldName, $sOldFolderName, $sNewFolderName );
                    $this->Rename( $kChild, array('dr_name'=>$sNewName), $flag, array('bRenameDescendants'=>true) );
                } else {
                    // this doc is unnamed so just rename its children
                    $this->RenameDescendants( $kChild, $sOldFolderName, $sNewFolderName );
                }
            }
        }
    }

    function makeNewNameAfterRenameOrMove( $sOldName, $sOldFolderName, $sNewFolderName )
    /***********************************************************************************
        This is used when a doc is moved, to find its new folder-path name (and those of its descendants),
        and when an ancestor is renamed to find the descendants' folder-path names.

        This doesn't compute a new name of a Renamed document - just the folder-paths of any named descendants.

        sOldName       = the doc's old name as stored in the db
        sOldFolderName = the old name of the doc's closest named ancestor
        sNewFolderName = the new name of the doc's closest named ancestor

        If the old name was blank, the new name is also blank.
        If the old or new location has a folder name, substitute the new one for the old one.
            N.B. we only substitute if the old name starts with the old folder name: otherwise, we just prepend the new folder
     */
    {
        $sNewName = "";

        if( !empty($sOldName) ) {
            if( !empty($sOldFolderName) && ($sOldFolderName."/") == substr($sOldName,0,strlen($sOldFolderName)+1) ) {
                $sOldName = substr($sOldName,strlen($sOldFolderName)+1);  // remove the old folder name
            }
            $sNewName = empty($sNewFolderName) ? $sOldName : ($sNewFolderName.'/'.$sOldName);
        }
        return( $sNewName );
    }


    function MetadataUpdate( $kDoc, $parms, $flag = "" )
    /***************************************************
     */
    {
        // Rename works by substituting the given record's data with any parms specified in $parms, including dr_name, dr_title, etc.
        // Any unrecognized parms (typically not prefixed with "dr_") are collected in to metadata.
        // N.B.  All metadata is completely replaced by the parms received here.

        // *** RENAME is inadequate for this because there's no way to delete all metadata.  Blank metadata input causes no change to the stored metadata.

        // As implemented, you could use a metadata update to modify things like verspec and parent/sibling, by specifying dr_verspec, dr_posUnder, dr_posAfter

        return( $this->Rename( $kDoc, $parms, $flag ) );
    }

    function Move( $kDoc, $kParent, $iSiborder )
    /*******************************************
        Move the doc $kDoc to $kParent/$iSiborder, move any existing siblings to the right to make room
     */
    {
        $bOk = false;

        // Currently, this function does not allow move to parent zero. permclass is not defined for zero, and we require parent to be writable.
        // TODO: An ADMIN permission could be added to allow operations like "move to parent zero".
        if( !$kParent )  return( false );
        if( !$iSiborder )  $iSiborder = 1;

        $oDoc = new DocRepDoc( $this, $kDoc );
        $oDocParent = new DocRepDoc( $this, $kParent );

        if( $oDoc->PermsW_Okay() && $oDocParent->PermsW_Okay() ) {
            $oldName = $oDoc->GetName();
            $oldFolderName = $oDoc->GetFolderName();
            $newFolderName = $oDocParent->GetName();
            if( empty($newFolderName) )  $newFolderName = $oDocParent->GetFolderName();

            if( ($kfr = $this->kfrelDocBase->GetRecordFromDBKey( $kDoc )) ) {
                $this->_shiftSibsRight( $kParent, $iSiborder, 1 );    // shift siblings out of the way if there are any

                // Put the doc in its new location
                $kfr->SetValue( "docrep_docs_parent", $kParent );
                $kfr->SetValue( "siborder", $iSiborder );
                $bOk = $kfr->PutDBRow();

                if( $bOk && !empty($oldName) ) {
                    $newName = $this->makeNewNameAfterRenameOrMove( $oldName, $oldFolderName, $newFolderName );
                    $this->Rename( $kDoc, array('dr_name'=>$newName), "", array('bRenameDescendants'=>true) );
                }
            }
        }
        return( $bOk );
    }

    function MoveRelative( $kDoc, $kDocTarget, $sRelative )
    /******************************************************
        Move the doc $kDoc to a position relative to the given target.

        $sRelative = "BEFORE"
        $sRelative = "AFTER"
        $sRelative = "FIRSTCHILD"
     */
    {
        $bOk = false;

        if( $sRelative == "FIRSTCHILD" ) {
            $bOk = $this->Move( $kDoc, $kDocTarget, 1 );
        } else if( ($kfrTarget = $this->kfrelDocBase->GetRecordFromDBKey( $kDocTarget )) ) {
            if( $sRelative == "BEFORE" ) {
                $bOk = $this->Move( $kDoc, $kfrTarget->value("docrep_docs_parent"), $kfrTarget->value("siborder") - 1 );
            } else if( $sRelative == "AFTER" ) {
                $bOk = $this->Move( $kDoc, $kfrTarget->value("docrep_docs_parent"), $kfrTarget->value("siborder") + 1 );
            }
        }
        return( $bOk );
    }


    function MoveLeft( $kDoc )
    /*************************
        Move the doc so it swaps with its left sibling.
     */
    {
        return( $this->_moveLeftOrRight( $kDoc, true ) );
    }

    function MoveRight( $kDoc )
    /**************************
        Move the doc so it swaps with its right sibling.
     */
    {
        return( $this->_moveLeftOrRight( $kDoc, false ) );
    }

    function MoveUp( $kDoc )
    /***********************
        Move the doc to become the next sibling of its parent
     */
    {
        $bOk = false;

        // optimize
        if( ($kfr = $this->kfrelDocBase->GetRecordFromDBKey( $kDoc )) &&
            $this->_permsW_Okay( $kfr->Value('permclass') ) )
        {
            $bOk = $this->MoveRelative( $kDoc, $kfr->value("docrep_docs_parent"), "AFTER" );
        }
        return( $bOk );
    }

    function MoveDown( $kDoc )
    /*************************
        Move the doc to become the first child of its left sibling
     */
    {
        $bOk = false;

        // optimize
        if( ($kfr = $this->kfrelDocBase->GetRecordFromDBKey( $kDoc )) &&
            $this->_permsW_Okay( $kfr->Value('permclass') ) )
        {
            if( ($kfr2 = $this->_getLeftOrRightSibKFR( $kfr, true )) ) {
                $bOk = $this->MoveRelative( $kDoc, $kfr2->Key(), "FIRSTCHILD" );
            } else {
                $bOk = true;    // if there is no left sib, no problem
            }
        }
        return( $bOk );
    }


    function _moveLeftOrRight( $kDoc, $bLeft )
    /*****************************************
        Move the doc so it swaps with its left or right sibling.
     */
    {
        $bOk = false;

        if( ($kfr = $this->kfrelDocBase->GetRecordFromDBKey( $kDoc )) &&
            $this->_permsW_Okay( $kfr->Value('permclass') ) )
        {
            // find the neighbouring sib (left or right)
            if( ($kfr2 = $this->_getLeftOrRightSibKFR( $kfr, $bLeft )) ) {
                $so1 = $kfr->value("siborder");
                $so2 = $kfr2->value("siborder");
                $kfr->SetValue( "siborder", $so2 );
                $kfr2->SetValue( "siborder", $so1 );
                $bOk = $kfr->PutDBRow() && $kfr2->PutDBRow();   // seems like we shouldn't short-circuit, but if the first fails it's okay not to do the second
            } else {
                $bOk = true;    // if there is no neighbouring sib, no problem
            }
        }
        return( $bOk );
    }

    function ReplaceSFileOrphan( $sfile, $sFilename )    // use DocRepDoc::ReplaceSFile() to replace sfiles that have a DocRepDoc
    /************************************************
        Do a filesystem move on an sfile that doesn't have a db record.
        All we have to do is make sure it doesn't conflict with an existing file.
     */
    {

    }

    function MoveSFileOrphan( $sfile, $sNewName )    // use DocRepDoc::RenameSFile() to move sfiles that have a DocRepDoc
    /********************************************
        Do a filesystem move on an sfile that doesn't have a db record.
        All we have to do is make sure it doesn't conflict with an existing file.
     */
    {

    }

    function MoveSFileFolder( $sfileFolder, $sNewName )
    /**************************************************
        Rename and filesystem-move all the files prefixed by the given folder, whether or not they are real or orphaned.
        But not if the current user doesn't have W permission on all of the files.
     */
    {
        // trim trailing slashes to normalize
        if( substr( $sfileFolder, -1, 1 ) == '/' )  $sfileFolder = substr( $sfileFolder, 0, -1 );
        if( substr( $sNewName, -1, 1 ) == '/' )     $sNewName = substr( $sNewName, 0, -1 );

        if( $sfileFolder == "" || $sNewName == "" ) return( null );            // fail
        if( $sfileFolder == $sNewName )             return( $sfileFolder );    // trivial success

        /* Get DocRep names that will be renamed, and test for conflicts
         *
         * Overwriting is not allowed, but we do want to be able to move files into an existing folder
         * e.g. if we have a/b.jpg and c/d.jpg we do want to be able to rename c/ to a/
         * Making unique names is hard and prone to causing broken links.
         * Instead, test for overwriting and fail.
         * N.B. Don't care too much about messing up non-unique orphaned files, so just compute the test based on DocRep names
         *
         * Also, do not allow renaming a folder that contains a non-perm file.
         */
        $raNames = array();
        $bConflict = false;
        if( ($kfr = $this->kfrelDoc_x_Data->CreateRecordCursor("name LIKE '".addslashes($sfileFolder)."/%' "
                                                              ."AND T1.maxVer=Data.ver "
                                                              ."AND Data.src='SFILE'") )) {
            while( $kfr->CursorFetch() ) {
                $oldname = $kfr->value('name');
                $newname = $sNewName.substr( $oldname, strlen($sfileFolder) );

                // check for non-visible file (not allowed to move it)
                if( !$this->_permsR_Okay( $kfr->Value('permclass') ) ) {
                    $this->ErrMsg( "Cannot rename the folder because it contains another user's file" );
                    return( null );
                }

                // check for conflict using permclass-free lookup, so conflicts with non-visible files are detected
                if( ($kfrConflict = $this->getDocKfrFromName( $newname )) ) {
                    if( $this->_permsR_Okay( $kfrConflict->Value('permclass') ) ) {
                        $this->ErrMsg( "Renaming the folder would overwrite file $newname" );
                    } else {
                        $this->ErrMsg( "Renaming the folder would overwrite another user's file" );
                    }
                    return( null );
                }

                $raNames[$oldname] = $newname;
            }
        }

        /* $raNames now contains the DocRep names of files that will be moved/renamed, and their new names.
         * All renames are perm-checked.
         *
         * Rename all the DocRep names.
         * Move the files.
         *     1) if the destination folder is new, just rename the folder. This renames all orphaned files not included in $raNames.
         *     2) if the destination folder already exists, it means we're merging the two folders. Each file has to be moved
         *        separately, and orphaned files have to be discovered and moved too.
         */
        var_dump($raNames);

        // there is probably a single sql command to rename the docrepdb names
        // do filesystem moves 1) and 2)


        return( $sNewName );
    }

    function _getLeftOrRightSibKFR( $kfrDoc, $bLeft )
    /************************************************
        Return kfr of $kfrDoc's left or right sibling
     */
    {
        $sSibSide  = $bLeft ? "<" : ">";
        $bSortDown = $bLeft ? true : false;

        $kfr = $this->kfrelDocBase->CreateRecordCursor( "docrep_docs_parent='".$kfrDoc->value("docrep_docs_parent")."'"
                                                       ." AND siborder ".$sSibSide." ".$kfrDoc->value("siborder"),
                                                       array( "sSortCol"=>"siborder", "bSortDown"=>$bSortDown, "iLimit"=> 1 ));
        if( !$kfr || !$kfr->CursorFetch() )  $kfr = NULL;
        return( $kfr );
    }

    function _shiftSibsLeft( $kParent, $iSiborder, $n )
    /**************************************************
        Shift the given doc and all right siblings $n spaces to the left.
        The caller has already ensured that $iSiborder-1 is vacant, and $iSiborder>1
     */
    {
        $this->kfdb->KFDB_Execute( "UPDATE docrep_docs SET siborder=siborder-1 WHERE docrep_docs_parent='$kParent' AND siborder >= $iSiborder" );
    }

    function _shiftSibsRight( $kParent, $iSiborder, $n )
    /***************************************************
        Shift the given doc and all right siblings $n spaces to the right
     */
    {
        // caller expects that this will not cause problems if iSiborder doesn't exist; existence is not tested first
        $this->kfdb->KFDB_Execute( "UPDATE docrep_docs SET siborder=siborder+1 WHERE docrep_docs_parent='$kParent' AND siborder >= $iSiborder" );
    }

// use DocRepDoc::Trash
    function TrashDoc( $kDoc )
    {
//TODO: this really has to do something with descendants too!
        return( $this->kfdb->Execute( "UPDATE docrep_docs SET status='DELETED' WHERE _key='$kDoc'" ) );
    }

    function TrashRestoreDoc( $kDoc )
    {
        return( $this->kfdb->Execute( "UPDATE docrep_docs SET status='ACTIVE' WHERE _key='$kDoc'" ) );
    }

    function TrashPurgeDoc( $kDoc )
    {
        if( ($kfr = $this->kfrelDataBase->CreateRecordCursor( "fk_docrep_docs='$kDoc'" )) ) {
            while( $kfr->CursorFetch() ) {
                $this->VersionDelete( $kfr->Key() );
            }
        }
        $this->kfdb->Execute( "DELETE FROM docrep_docs WHERE _key='$kDoc'" );
    }

    function VersionSetDXDFlag( $kDoc, $kData, $flag )
    /*************************************************
        Set the given DXD tuple.  Remove any tuple with the same kDoc/flag
     */
    {
        $this->kfdb->Execute( "DELETE FROM docrep_docxdata WHERE fk_docrep_docs='$kDoc' AND flag='$flag'" );
        $kfrDXD = $this->kfrelDXDBase->CreateRecord();
        $kfrDXD->SetValue( "fk_docrep_docs", $kDoc );
        $kfrDXD->SetValue( "fk_docrep_docdata", $kData );
        $kfrDXD->SetValue( "flag", $flag );
        if( !$kfrDXD->PutDBRow() )  die( "Cannot write dxd row" );
    }

    function VersionChangeDXD( $kDoc, $raFlagKey )
    /*********************************************
        Update all DXD flags for one doc

        $raFlagKey is a complete set of DXD flags for kDoc: array of [flag]=>kDocData  ( [flag]=>0 indicates delete )
     */
    {
        /* Compare the DXD records with the array and make the necessary changes
         */
        if( ($kfrDXD = $this->kfrelDXDBase->CreateRecordCursor( "fk_docrep_docs=$kDoc" )) ) {
            while( $kfrDXD->CursorFetch() ) {

                $flag = $kfrDXD->Value('flag');
                $kDocData = $kfrDXD->Value('fk_docrep_docdata');

                if( @$raFlagKey[$flag] ) {
                    if( $raFlagKey[$flag] != $kDocData ) {
                        // The flag is still there, but we're moving it to a different version
                        $kfrDXD->SetValue( "fk_docrep_docdata", $raFlagKey[$flag] );
                        if( !$kfrDXD->PutDBRow() )  die( "Cannot write dxd row" );
                    }
                    $raFlagKey[$flag] = 0;  // mark as done (note that if the same flag appears later in the list this 0 will trigger DeleteRow(),
                                            // but there shouldn't be duplicate flags anyway so this is actually a self-repairing feature)
                } else {
                    // The flag has been removed from this doc
                    $kfrDXD->DeleteRow();
                }
            }
            $kfrDXD->CursorClose();
        }

        /* Any remaining $raFlagKey items are being added
         */
        foreach( $raFlagKey as $flag => $kDocData ) {
            if( !$kDocData ) continue;

            $kfrDXD = $this->kfrelDXDBase->CreateRecord();
            $kfrDXD->SetValue( "fk_docrep_docs", $kDoc );
            $kfrDXD->SetValue( "fk_docrep_docdata", $kDocData );
            $kfrDXD->SetValue( "flag", $flag );
            if( !$kfrDXD->PutDBRow() )  die( "Cannot write dxd row" );
        }
        return( true );
    }

    function VersionDelete( $kDocData )
    /**********************************
        Remove the given docdata record.
        Also remove data file, if any, but not for SFILE.
        Also remove all related DXD records.
        If this is the top version, change the maxVer because it's used as a join criteria to the current top version.
     */
    {
        if( !($kfrData_x_Doc = $this->kfrelData_x_Doc->GetRecordFromDBKey( $kDocData )) )  goto done;

        $kDoc = $kfrData_x_Doc->Value("Doc__key");

        // Do not allow a solitary version to be removed.  A doc without any versions has undefined behaviour.
        $n = $this->kfdb->Query1( "SELECT count(*) FROM docrep_docdata "
                                 ."WHERE fk_docrep_docs='".$kfrData_x_Doc->Value('Doc__key')."' AND _status='0'" );
        if( $n < 2 )  return;

        $bTopVersion = ($kfrData_x_Doc->Value("Doc_maxVer") == $kfrData_x_Doc->Value("ver"));

        // Destroy the version records, DXD records, and FILE
        $this->_destroyVersion( $kfrData_x_Doc );

        // If the top version was deleted, reset doc.maxVer to the current max(ver)
        if( $bTopVersion &&
            ($ver = $this->kfdb->Query1( "SELECT MAX(ver) FROM docrep_docdata WHERE fk_docrep_docs='$kDoc' AND _status='0'" )) )
        {
            $this->kfdb->Execute( "UPDATE docrep_docs SET maxVer='$ver' WHERE _key='$kDoc'" );
        }

        done:;
    }

    function _destroyVersion( $kfrData )
    /***********************************
        Destroy the docdata and docxdata related to this version.
        Erase any FILE, but not SFILE because multiple versions can share that.
        $kfrData should be at least docrep_docdata but it can also be docrep_docdata-X-docrep_docs

        Should be protected and available to friend DocRepDoc, if that were possible in PHP.
     */
    {
        if( $kfrData->Value("src") == "FILE" ) {
            @unlink( $this->getDataFilenameUsingData( $kfrData ) );
        }

        $this->kfdb->Execute( "DELETE FROM docrep_docxdata WHERE fk_docrep_docdata='".$kfrData->Key()."'" );
        $this->kfdb->Execute( "DELETE FROM docrep_docdata WHERE _key='".$kfrData->Key()."'" );
    }


    function _loadDoc( $kDoc, $flag )
    /********************************
     */
    {
        if( !$kDoc )  return( false );

        if( !$this->kfrDoc || $this->kfrDoc->Key() != $kDoc || $this->cachedDocFlag != $flag ) {
            $cachedDocFlag = $flag;

            $this->kfrDoc = $this->_getKfrDoc( $kDoc, $flag );
        }
        return( $this->kfrDoc != NULL );
    }

    function _getKfrDoc( $kDoc, $flagOrVer )
    /***************************************
        Get kfr for doc X data and check permission
** does not check if invisible folder contains visible item

        $flagOrVer is either a DXD flag, a numeric version, or ""
        This implies that DXD flags cannot be numeric.
     */
    {
        $kfrDoc = $this->getKfrNoPermCheck( $kDoc, $flagOrVer );

// TODO: allow invisible folder if contains visible item
        if( $kfrDoc && !$this->_permsR_Okay( $kfrDoc->Value("permclass") ) ) {
            $kfrDoc = NULL;
            if( $this->bDebug )  die( "Doc $kDoc:".(empty($flagOrVer) ? "maxVer" : $flagOrVer)." exists but does not have R perms" );
        }
        return( $kfrDoc );
    }

    function getKfrNoPermCheck( $kDoc, $flagOrVer )
    /**********************************************
        Get kfr for doc X data with no permission check
     */
    {
        if( empty($flagOrVer) ) {
            /* Get Doc and Data for the maxVer
             */
            $colnameKey    = $this->kfrelDoc_x_Data->GetDBColName( "docrep_docs", "_key" );
            $colnameMaxVer = $this->kfrelDoc_x_Data->GetDBColName( "docrep_docs", "maxVer" );
            $kfrDoc = $this->kfrelDoc_x_Data->GetRecordFromDB( "$colnameKey='$kDoc' AND $colnameMaxVer=Data.ver" );
        } else if( is_numeric($flagOrVer) ) {
            /* Get Doc and Data for the given numbered version
             */
            $iVer = intval($flagOrVer);
            $kfrDoc = $this->kfrelDoc_x_Data->GetRecordFromDB( "_key='$kDoc' AND Data.ver='$iVer'" );
        } else {
            /* Get Doc and Data for the flagged DXD
             */
            $colnameKey = $this->kfrelDoc_x_DXD_x_Data->GetDBColName( "docrep_docs", "_key" );
            $kfrDoc = $this->kfrelDoc_x_DXD_x_Data->GetRecordFromDB( "$colnameKey='$kDoc' AND DXD.flag='$flagOrVer'" );
        }

        if( !$kfrDoc && $this->bDebug )  die( "Cannot find doc $kDoc:".(empty($flagOrVer) ? "maxVer" : $flagOrVer) );

        return( $kfrDoc );
    }

    function _permsR_Okay( $permclass )
    /**********************************
        Return true if the given permclass can be read by the current user
     */
    {
        return( @$this->parms['bPermclass_allaccess'] ||
                ((@$this->parms['bPermclass0_allaccess'] && $permclass == 0 )) ||
                (in_array( $permclass, $this->raPermsR )) );
    }


    function _permsW_Okay( $permclass )
    /**********************************
        Return true if the given permclass can be written by the current user
     */
    {
        return( in_array( $permclass, $this->raPermsW ) );
    }


    function _permsR_sCond()
    /***********************
        Return the condition string that filters permclass for read access.
        If no configuration is given, an invalid filter is returned.

        Configuration requires at least one of:
            raPermsR
            bPermclass_allaccess
            bPermclass0_allaccess
     */
    {
        $sCond = "";
        if( @$this->parms['bPermclass_allaccess'] ) {
            $sCond = "1=1";
        } else {
            if( count($this->raPermsR) ) {
                $sCond = "permclass IN (".implode(",", $this->raPermsR).")";
            }
            if( @$this->parms['bPermclass0_allaccess'] ) {
                if( !empty($sCond) )  $sCond .= " OR ";
                $sCond .= "permclass=0";
            }
        }
        return( !empty($sCond) ? $sCond : PERMSR_SCOND_ERR );
    }
}

class DocRepDoc_ReadOnly
{
    const   FLAG_INDEPENDENT = '';   // use this as a flag for GetValues which are flag-independent (values stored in docrep_docs)

    public    $oDocRepDB;
    public    $bValid = false;    // to test if the constructor worked
    protected $kDoc;

    private   $raValues = array();    // array( flag1 => array(vals), flag2 => array(vals) )
    private   $raAncestors = NULL;
    private   $sFolderName = NULL;

    function __construct( DocRepDB $oDocRepDB, $kDoc )
    {
        $this->oDocRepDB = $oDocRepDB;
        $this->kDoc = $kDoc;
        $this->GetValues( "" );    // load the "" version to validate that the doc exists and is at least readable
        if( isset($this->raValues[""]['doc_key']) ) {
            $this->bValid = true;
        } else {
            $this->voidDoc();   // make the object unusable
        }
    }

    function GetKey()          { return( $this->kDoc ); }
    function GetName()         { return( $this->GetValue( 'name', self::FLAG_INDEPENDENT ) ); }
    function GetTitle( $flag ) { return( $this->GetValue( 'title', $flag ) ); }
    function GetType()         { return( $this->GetValue( 'type', self::FLAG_INDEPENDENT ) ); }
    function GetStatus()       { return( $this->GetValue( 'status', self::FLAG_INDEPENDENT ) ); }
    function GetPermclass()    { return( $this->GetValue( 'permclass', self::FLAG_INDEPENDENT ) ); }
    function GetParent()       { return( $this->GetValue( 'parent', self::FLAG_INDEPENDENT ) ); }
    function GetVerspec($flag) { return( $this->GetValue( 'verspec', $flag ) ); }

    function GetValue( $k, $flag )   // return a doc property value; force caller to specify flag for safety
    {
        $ra = $this->GetValues($flag);
        return( is_array($ra) && isset($ra[$k]) ? $ra[$k] : NULL );
    }

    function GetText( $flag )
    /************************
        Return the data_text of this doc (returns "" if the data type is not text)
     */
    {
        $ra = $this->GetValues($flag);
        return( @$ra['type'] == 'TEXT' ? $ra['data_text'] : "" );
    }

    function GetMetadataValue( $k, $flag )
    /*************************************
        Return the value of Data_metadata[$k] for the 'flag' version
     */
    {
        $ra = $this->GetValues($flag);
        return( @$ra['raMetadata'][$k] );
    }

    function GetValuesVer( $iVer )
    /*****************************
        Return an array of standardized values for the given numbered version.
        These values are not cached because this method is probably not used much except in Version UI.
     */
    {

    }

    function GetValues( $flag )
    /**************************
        Return a complete array of standardized values for the given version flag
     */
    {
        if( isset($this->raValues[$flag]) ) { return( $this->raValues[$flag] ); }

        if( !($kfr = $this->oDocRepDB->getKfrNoPermCheck( $this->kDoc, $flag )) ) { return( NULL ); }

        // Check read permission
        //     - can't use $this->PermsR_Okay yet because the values aren't stored yet (_permsR_Okay works)
        //     - allow invisible folders to be readable if they contain a visible item
        if( !$this->oDocRepDB->_permsR_Okay( $kfr->Value("permclass") ) ) {
            // This doc is not normally visible but if it's an invisible folder containing a visible descendant, we treat it as readable.
            // If GetSubtree returns anything, that means there is at least one visible descendant. We only have to look at immediate
            // descendants (depth==1) because any invisible folders will automatically recurse to search for visible descendants
            if( !($kfr->Value('type') == 'FOLDER' && $this->oDocRepDB->GetSubtree( $this->kDoc, 1 )) ) {
                // doc is not visible/readable
                if( $this->oDocRepDB->bDebug )  die( "Doc {$this->kDoc}:".(empty($flag) ? "maxVer" : $flag)." exists but does not have R perms" );
                return( NULL );
            }
        }

        $ra = $kfr->ValuesRA();
        $ra['Data_metadata'] = UrlParmsUnpack(@$ra['Data_metadata']);

        // map kfr values to standardized keys (add any standardized keys you wish)
        $raV = array();
        $raV['name']           = @$ra['name'];
        $raV['type']           = @$ra['type'];
        $raV['spec']           = @$ra['spec'];
        $raV['status']         = @$ra['status'];
        $raV['maxVer']         = @$ra['maxVer'];
        $raV['permclass']      = @$ra['permclass'];
        $raV['parent']         = @$ra['docrep_docs_parent'];
        $raV['title']          = @$ra['Data_meta_title'];
        $raV['desc']           = @$ra['Data_meta_desc'];
        $raV['ver']            = @$ra['Data_ver'];
        $raV['verspec']        = @$ra['Data_verspec'];
        $raV['mimetype']       = @$ra['Data_mimetype'];
        $raV['raMetadata']     = @$ra['Data_metadata'];    // this has been unpacked into an array

        $raV['doc_key']        = $this->GetKey();
        $raV['doc_created']    = @$ra['_created'];
        $raV['doc_created_by'] = @$ra['_created_by'];
        $raV['doc_updated']    = @$ra['_updated'];
        $raV['doc_updated_by'] = @$ra['_updated_by'];
        $raV['data_key']       = @$ra['Data__key'];
        $raV['data_src']       = @$ra['Data_src'];
        $raV['data_text']      = @$ra['Data_data_text'];

        $this->raValues[$flag] = $raV;

        return( $raV );
    }

    function GetAncestors()
    /**********************
        Return a list of all ancestors of this doc, including kDoc but not 0.
        kDoc is the first element, the tree root is the last element.
     */
    {
        if( !$this->raAncestors ) {
            $this->raAncestors = $this->oDocRepDB->GetDocAncestors( $this->kDoc );
        }
        return( $this->raAncestors );
    }

    function GetParentObj()
    /**********************
        Return a DocRepDoc of the parent of this doc
     */
    {
        $kParent = $this->GetParent();
        return( $kParent ? new DocRepDoc( $this->oDocRepDB, $kParent ) : NULL );
    }

    /* Document Names
     *
     * The document name is stored as a full path name from the root, but the name tree does not necessarily match the
     * doc tree because docs can be unnamed. Unnamed structures subdivide the name heirarchy, which is handy, but imposes
     * tricky rules for name generation.
     *
     * The doc name can be split into a foldername (whether or not it is a FOLDER) and a basename.
     * Typically, this is used in UI where the user provides the base name and the foldername is derived from doc ancestors.
     *
     *  one                      name = one           foldername =             basename = one
     *      |
     *      - two                name = one/two       foldername = one         basename = two
     *      |
     *      - three              name = one/three     foldername = one         basename = three
     *      |
     *      - (noname)           name =               foldername = one         basename =
     *                |
     *                - four     name = one/four      foldername = one         basename = four
     *
     * The tricky part is that a doc's foldername is not necessarily the same as its parent's name. It is actually the name of
     * the closest named ancestor. Also, the foldername is not present within the doc name if the doc is unnamed. That seems
     * obvious when stated this way, but it's easy to forget while coding.
     *
     * Insertion rules:
     *      When inserting a named doc, the docname = foldername/name
     *      1) if foldername is blank, there is no '/'.  This would happen at parent=0 (example one),
     *         or if all ancestors were unnamed.
     *      2) if the parent has a name, then foldername=parent name
     *      3) if the parent doesn't have a name, then foldername=name of closest named ancestor.
     *      4) if inserting after a sibling, it's convenient to know that foldername of new doc = foldername of sibling
     *
     *  When inserting an unnamed doc, the docname is always blank.
     *  This is what creates the trickiness, because then the foldername is not stored in the doc, imposing an ancestor-search.
     *
     *  N.B. this scheme is used for all doc types, not just folders. Originally it was only used for folders because it was
     *  assumed that a doc inserted under a non-folder would not want the current doc's name e.g. foo.htm as part of the folder
     *  heirarchy.  In reality, the only time a non-folder name structure is useful is e.g. in a web site where the containing
     *  doc is naturally named _like_ a folder. e.g. one is a page, and one/two is a page. It would be strange to call these
     *  one.htm and one.htm/two.htm, and more confusing to put named images in a heirarchy e.g. "foo.jpg/bar.jpg" but you'd
     *  never do that.
     *  Therefore, the same name-structure conventions are used for all doctypes.
     *
     *  Can base names contain '/' ?
     *      No. So GetNewDocName converts '/' to '_'
     */

    function GetDocName()   { return( $this->GetName() ); }  // same thing; we call it docname to differentiate from basename and foldername
    function GetBaseName()
    {
        $sName = $this->GetDocName();
        if( ($n = strrpos($sName,'/')) > 0 ) {    // 0-based position of rightmost '/' == # chars to left of that '/'
            $sName = substr( $sName, $n + 1 );
        }
        return( $sName );
    }

    function GetFolderName( $kRoot = 0 )
    /***********************************
        This is actually the closest named ancestor's name, whether or not it is a folder.
        If the doc is named, it is substr(name,0,strrpos('/'))
        If the doc is not named, we have to walk backward through the ancestor list to find a named ancestor.

        kRoot is the highest ancestor to evaluate (not sure why this is ever useful)
     */
    {
        if( $this->sFolderName === NULL ) {
            $this->sFolderName = "";

            if( ($name = $this->GetDocName()) ) {
                if( ($n = strrpos($name,'/')) > 0 ) {    // 0-based position of rightmost '/' == # chars to left of that '/'
                    $this->sFolderName = substr( $name, 0, $n );
                }
            } else {
                // Foldername = name of closest named ancestor
                $o = $this;
                while( $o = $o->GetParentObj() ) {
                    if( ($this->sFolderName = $o->GetName()) ) break;    // found a named ancestor
                    if( $o->GetKey() == $kRoot )  break;                 // reached the root ancestor
                }
            }
        }
        return( $this->sFolderName );
    }

    function GetNewDocName( $sName, $bInsertInto = false )
    /*****************************************************
        Generate the docname of a new doc that is being inserted relative to this doc.

        $sName is the (new) base name of a document, either being inserted, updated or renamed.
        $bInsertInto == (inserting a doc && posUnder the current doc)  -- this was contracted from $bInsert and $bPosUnder
        Return the full path name of the inserted/updated doc.

        If updating/renaming, the folder name doesn't change
        If inserting after the current doc, the folder name is the same as the current doc's folder name.
        If inserting under the current doc, the folder name is this doc
     */
    {
        if( !empty($sName) ) {
            $sName = str_replace( '/', '_', $sName );  // eliminate slashes so users can't insert weird name heirarchy

            if( $bInsertInto ) {
                // new name is this->docname/sName
                // tricky bit: if !this->docname then use foldername instead
                $sFolderName = $this->GetDocName();
                if( !$sFolderName ) {
                    $sFolderName = $this->GetFolderName();
                }
            } else {
                // new name is this->foldername/sName
                $sFolderName = $this->GetFolderName();
            }
            if( !empty($sFolderName) ) {
                $sName = $sFolderName.'/'.$sName;
            }

            /* If there is another doc with this name, add a suffix.
             */
// Have to check if this is an update of the same doc. Checking the return of GetDocFromName()==$this->GetKey() is not enough
// unless we know this is an update, not an insert.
// OR let the Insert function add the suffix
            if( false )     $sName = $this->makeUniqueName( $sName );
        }
        return( $sName );
    }

    function GetSFileExt()
    /*********************
        Get the file extension from an sfile name. This is pretty generic so you could also do this with any filename parser.
        You could use this for non-sfile FILE documents too, if you know they have extensions.

        N.B. the Data_data_file_ext field contains the file ext in the db anyway, but we're not sure we want to rely on it.
     */
    {
        $ext = "";

        $sBaseName = $this->GetBaseName();
        if( ($i = strrpos( $sBaseName, '.' )) !== false ) {
            $ext = substr( $sBaseName, $i + 1 );
        }

        return( $ext );
    }

    protected function makeUniqueName( $sName )
    /******************************************
        If the name already exists in the DocRep, add a suffix to make this name unique.
     */
    {
        if( $this->oDocRepDB->GetDocFromName( $sName ) ) {
            /* The name is already used. Figure out where to put a number to uniqueify it.
             * foo1.jpg is a lot better than foo.jpg1
             */
            if( ($iDot = strrpos( $sName, '.' )) === false ) {
                // no dot: put the suffix at the end of the name
                $iPos = strlen($sName);
            } else if( ($iSlash = strrpos( $sName, '/' )) === false ) {
                // no slash: put the suffix before the dot
                $iPos = $iDot;
            } else if( $iDot > $iSlash ) {
                // the last dot is after the last slash: put the suffix before the dot
                $iPos = $iDot;
            } else {
                // the last dot is before a slash: put the suffix at the end of the name
                $iPos = strlen($sName);
            }

            for( $iSuffix = 1; ; ++$iSuffix ) {
                $sNameNew = substr( $sName, 0, $iPos ).$iSuffix.substr( $sName, $iPos );
                if( !$this->oDocRepDB->GetDocFromName( $sNameNew ) ) {
                    $sName = $sNameNew;
                    break;
                }
            }
            $this->oDocRepDB->ErrMsg( "Duplicate name; calling this document <b>$sName</b> instead." );
        }
        return( $sName );
    }

    function GetFlagOfCurrVer( $flag )
    /*********************************
        Check for the dxd flag corresponding to the current version of this doc.
For sure, we can do something a lot more general than this, and probably use a kfrel to take advantage of _status checking too
     */
    {
        $ret = $this->oDocRepDB->kfdb->Query1(
            "SELECT DXD.flag as flag FROM docrep_docs Doc, docrep_docdata Data, docrep_docxdata DXD "
           ."WHERE Doc._key='".$this->kDoc."' AND Doc._key=Data.fk_docrep_docs AND Doc.maxVer=Data.ver AND Data._key=DXD.fk_docrep_docdata AND DXD.flag='".addslashes($flag)."'" );
        return( $ret == $flag );
    }


    function PermsR_Okay()
    {
        $permclass = $this->GetPermclass();
        if( !($bOk = $this->oDocRepDB->_permsR_Okay( $permclass )) ) {
            // This doc is not normally visible but if it's an invisible folder containing a visible descendant, we treat it as readable.
            // If GetSubtree returns anything, that means there is at least one visible descendant. We only have to look at immediate
            // descendants (depth==1) because any invisible folders will automatically recurse to search for visible descendants
            if( $this->GetType() == 'FOLDER' && $this->oDocRepDB->GetSubtree( $this->kDoc, 1 ) ) {
                $bOk = true;
            } else {
                // doc is not visible/readabe
                if( $this->oDocRepDB->bDebug )  die( "Doc {$this->kDoc} exists but does not have R perms" );
            }
        }
        return( $bOk );
    }

    function PermsW_Okay()
    {
        $permclass = $this->GetPermclass();
        return( $this->oDocRepDB->_permsW_Okay( $permclass ) );
    }







    protected function clearCache()
    /******************************
        Not really a readonly method, but it affects private stuff.
        Use this when data changes so the cache will be reloaded.
     */
    {
        $this->raValues = array();
    }

    protected function voidDoc()
    /***************************
        Not really a readonly method, but it affects private stuff.
        Use this to invalidate the doc object, e.g. when it is deleted from the db.
     */
    {
        unset($this->raValues);
        $this->bValid = false;
        $this->kDoc = 0;
    }


    // much of DocRepDoc will go here



}


class DocRepDoc extends DocRepDoc_ReadOnly
{
// Store this in a list in the DocRepDB, use as a cache in loadDoc.
// Some clients can have methods like Test( $kDoc, $parms ) such that the methods are generalized for any given kDoc but in reality they're called
// many times for the same kDoc. A cache would prevent repeated instantiations of the same DocRepDoc.

    function __construct( DocRepDB $oDocRepDB, $kDoc )
    {
        parent::__construct( $oDocRepDB, $kDoc );
    }

    function SetVersionFlag( $ver, $flag )
    /*************************************
        Set the given DXD flag on the given version of this document

        $ver can be a number or a flag, or "" for the current version (currently only implemented for current version)
     */
    {
        if( $ver )  die( "SetVersionFlag: non-current versions not implemented yet" );

        $this->oDocRepDB->VersionSetDXDFlag( $this->GetKey(), $this->GetValue('data_key', ''), $flag );

        $this->clearCache();    // maybe this operation messes up some cached version data, so reload from the db
    }

    function RenameSFile( $sNewName, $raParms = array() )
    /****************************************************
        Rename and filesystem-move the current doc, which must be an sfile.

        Return the filename that this doc was renamed to - which might be uniquefied - or "" if failed.
     */
    {
        $ok = true;

        if( $this->GetValue('data_src', "") != 'SFILE' )  return( "" );

        list($kfrDoc, $kfrData) = $this->getCurrKfrs();

        /* If the $sNewName is blank or the same as the current name, just update the metadata.
         */
        if( $sNewName && $sNewName != $this->GetName() ) {
            $sNewName = $this->makeUniqueName( $sNewName );

            $kfrDoc->SetValue( 'name', $sNewName );
            $kfrData->SetValue( 'sfile_name', $sNewName );

            /* If there's a real file, move it
             */
            if( $this->oDocRepDB->SFileIsFile( $this->GetName() ) ) {
                $fnameFrom = $this->oDocRepDB->GetSFileFilename( $this->GetName() );
                $fnameTo   = $this->oDocRepDB->GetSFileFilename( $sNewName );
                SEEDStd_MkDirForFile( $fnameTo );
                $ok = rename( $fnameFrom, $fnameTo );
            }
        }

        if( $ok ) {
            if( isset($raParms['dr_permclass']) )  $kfrDoc->SetValue( 'permclass',  $raParms['dr_permclass'] );
            if( isset($raParms['dr_spec']) )       $kfrDoc->SetValue( 'spec',       $raParms['dr_spec'] );
            if( isset($raParms['dr_desc']) )       $kfrData->SetValue( 'meta_desc', $raParms['dr_desc'] );

            $ok = $kfrDoc->PutDBRow() && $kfrData->PutDBRow();

            $this->clearCache();    // maybe this operation messes up some cached version data so reload from the db
        }

        return( $ok ? $sNewName : "" );    // could return array($ok,$sNewName) if you don't want to overload the error case
    }

    function ReplaceSFile( $sFilename, $raParms = array() )
    /******************************************************
        Replace the current sfile with the given file
        This could update metadata as well, but it's easier to do that with RenameSFile
     */
    {
        $ok = false;

        if( $this->GetValue('data_src', "") != 'SFILE' ) goto done;

        list($kfrDoc, $kfrData) = $this->getCurrKfrs();

/* Since the file format could change, the file ext also needs to change. This is NOT always available from the uploaded sFilename, but
 * it's probably available most of the time.
*/

        if( $this->insertUpdate_File( $kfrDoc, $kfrData, $sFilename, $raParms ) ) {
            $ok = $kfrDoc->PutDBRow() && $kfrData->PutDBRow();

            $this->clearCache();    // maybe this operation messes up some cached version data so reload from the db
        }

        done:
        return( $ok );
    }

// probably want to generalize this to Delete(), which should probably be called Trash()
// make sure descendants are deleted too
// or for now just don't allow this if there are any descendants, even DELETED
    function TrashSFile()
    /********************
     */
    {
        $ok = false;

        if( $this->GetValue('data_src', "") != 'SFILE' ) goto done;

//TODO: this really has to do something with descendants too!
        list($kfrDoc, $kfrData) = $this->getCurrKfrs();

        $kfrDoc->SetValue( 'status', 'DELETED' );
        $ok = $kfrDoc->PutDBRow();

        $this->clearCache();    // maybe this operation messes up some cached version data so reload from the db

        done:
        return( $ok );
    }

// probably want to generalize this to Delete(), which should probably be called Trash()
// make sure ancestors are undeleted too
    function UndeleteSFile()
    /***********************
     */
    {
        $ok = false;

        if( $this->GetValue('data_src', "") != 'SFILE' ) goto done;

        list($kfrDoc, $kfrData) = $this->getCurrKfrs();
        $kfrDoc->SetValue( 'status', 'ACTIVE' );
        $ok = $kfrDoc->PutDBRow();

        $this->clearCache();    // maybe this operation messes up some cached version data so reload from the db

        done:
        return( $ok );
    }

// purge all descendants too (they should all be in DELETED status)
// or for now just don't allow this if there are any descendants, even DELETED
    function PurgeForever()
    /**********************
        If this doc has state DELETED, delete it for real.
        The db records are actually deleted, and the filesystem files are actually deleted.
     */
    {
        $ok = false;

        if( $this->GetStatus() != 'DELETED' )  goto done;

        /* This works for all document types.
         *
         * VersionDelete for all version records of the doc.
         * Delete the doc.
         * For SFILE, delete the file.  VersionDelete doesn't do this, because multiple versions potentially share the same file.
         */
        $sfileName = ($this->GetValue('data_src', "") == 'SFILE') ? $this->oDocRepDB->GetSFileFilename($this->GetName()) : "";

        if( ($kfrData = $this->oDocRepDB->kfrelDataBase->CreateRecordCursor( "fk_docrep_docs='{$this->kDoc}'" )) ) {
            while( $kfrData->CursorFetch() ) {
                $this->oDocRepDB->_destroyVersion( $kfrData );
            }
        }
        $this->oDocRepDB->kfdb->Execute( "DELETE FROM docrep_docs WHERE _key='{$this->kDoc}'" );

        // VersionDelete takes care of FILE files, but leaves SFILE files alone because they can be shared by multiple versions
        if( $sfileName )  unlink( $sfileName );

        // It isn't really defined what happens to the object now. You shouldn't use it anymore.
        $this->voidDoc();

        $ok = true;

        done:
        return( $ok );
    }


    private function getCurrKfrs()
    /*****************************
        Get doc and docdata kfrs for the current version of $this->kDoc
     */
    {
        $kfrDoc = $this->oDocRepDB->kfrelDocBase->GetRecordFromDBKey( $this->kDoc ) or die( "Can't find doc record {$this->kDoc}" );
        $kfrData = $this->oDocRepDB->kfrelDataBase->GetRecordFromDB( "fk_docrep_docs='{$this->kDoc}' AND "
                                                                    ."ver='".$kfrDoc->Value('maxVer')."'" )
                                                                   or die( "Can't get current kfrDocData for doc {$this->kDoc}" );
        return( array( $kfrDoc, $kfrData ) );
    }


// Weird that this is in DocRepDoc instead of DocRepDoc_Insert, but updateDoc is needed by DocRepDoc and this is quite parallel
// and it uses a lot of the same helper methods as updateDoc
    protected function insertDoc( $docType, $eSrcType, $src, $parms )
    /****************************************************************
        Insert a new doc.
        $this must be a blank DocRepDoc with kDoc==0

        docType = TEXT | IMAGE | DOC | TEXTFRAGMENT | FOLDER | LINK | U_* (user type treated like DOC)
        eSrcType = TEXT | FILE | SFILE
        src     = eSrcType=TEXT: the content
                | eSrcType=FILE: the uploaded file name
                | eSrcType=SFILE: an optional uploaded file name
                | docType=LINK: dest kDoc


        For SFILE:
            $src is an optional uploaded file name - if blank create a doc pointing to dr_name and assume somebody put it there
            dr_name is the path rooted at sfile/
            dr_bReplaceCurrVersion is forced to true (for replace)
            dr_posUnder/posAfter are ignored, docrep_docs_parent is always DOCREP_PARENT_SFILE


        Basic parms:
        parms['dr_name'] = the name of the doc
        parms['dr_spec']    = user string for searching, grouping, etc - for the document (applies to all versions)
        parms['dr_verspec'] = user string for searching, grouping, etc - for the version
        parms['dr_flag'] = the flag associated with the new version
        parms['dr_permclass'] = integer permclass
        parms['dr_mimetype'] = the mime type
        parms['dr_fileext'] = the file extension

        Control parms:
        parms['dr_bEraseOldVersion']            mainly for use with FILE to delete old files to save disk space (new data goes in a new data record)
        parms['dr_bReplaceCurrVersion']         mainly for use with TEXT for minor updates that don't preserve current version (new data overwrites current data record)
        parms['dr_posUnder'] = kDoc of parent (make this doc the first child)
        parms['dr_posAfter'] = kDoc of sibling (make this doc the next sibling)

        Standard Metadata:
        parms['dr_title']
        parms['dr_desc']
        parms['dr_author']
        parms['dr_date']

        User Metadata:
        parms['dr_metadata'][]  // not implemented, undefined whether these override or totally replace existing metadata
     */
    {
        $kfrDoc = $this->oDocRepDB->kfrelDocBase->CreateRecord() or die( "Can't create blank kfrDoc" );
        $kfrData = $this->oDocRepDB->kfrelDataBase->CreateRecord() or die( "Can't create blank kfrDocData" );

        if( $docType == "FOLDER" || $docType == "LINK" ) {
            $eSrcType = "TEXT";     // there is no storage for these types, but normalize to this value to be tidy
        }
        if( !in_array( $eSrcType, array('TEXT', 'FILE', 'SFILE') ) ) {
            die( "Invalid insertion srcType $eSrcType" );
        }

        $kfrDoc->SetValue( "type", $docType );
        $kfrDoc->SetValue( "status", "NEW" );
        $kfrDoc->SetValue( "maxVer", 1 );
        $kfrDoc->PutDBRow() or die( "Cannot create doc record" );    // get the doc key for docdata.fk_docrep_docs
        if( !($this->kDoc = $kfrDoc->Key()) )  return( NULL );

        $kfrData->SetValue( "src", $eSrcType );
        $kfrData->SetValue( "ver", 1 );
        $kfrData->SetValue( "fk_docrep_docs", $kfrDoc->Key() );
        $kfrData->PutDBRow() or die( "Cannot create docdata record" );  // store the linked record now for integrity, in case of failures below

        /* Set the parent/sib
         */
        if( $eSrcType == 'SFILE' ) {
            // all docs in the sfile set have this parent, to isolate them from the regular doc forest (rooted at DOCREP_PARENT_DOCS)
            $kfrDoc->SetValue( 'docrep_docs_parent', DOCREP_PARENT_SFILE );
            $kfrDoc->SetValue( 'siborder', 0 );
        } else {
            $this->insert_ParentSiborder( $kfrDoc, intval(@$parms['dr_posUnder']), intval(@$parms['dr_posAfter']) );
        }

        /* Set the dr_parms into the records
         */
        $this->insertUpdate_Metadata( $kfrDoc, $kfrData, $parms );

        /* No guarantee that everything here will work (e.g. move_uploaded_file fails), but the doc records have
         * referential integrity at this point.
         */
            switch( $kfrDoc->Value('type') ) {
            case "FOLDER":  break;
            case "LINK":    $kfrData->SetValue( "link_doc", intval($src) );    break;

            default:
                switch( $kfrData->Value('src') ) {
                    case "TEXT":  $kfrData->SetValue( "data_text", $src );               break;
                    case "FILE":
                    case "SFILE": $this->insertUpdate_File( $kfrDoc, $kfrData, $src, $parms );  break;
                }
        }

        // rewrite the records to store the data/metadata
        $kfrData->PutDBRow() or die( "Cannot rewrite docdata row" );
        $kfrDoc->PutDBRow()  or die( "Cannot rewrite doc row" );
        if( ($flag = @$parms['dr_flag']) ) {
            // Make a DXD record (this doesn't change doc or data records)
            $this->oDocRepDB->VersionSetDXDFlag( $kfrDoc->Key(), $kfrData->Key(), $flag );
        }

        $this->clearCache();    // maybe this operation messes up some cached version data so reload from the db

        return( $this->kDoc );
    }

// nobody uses this?
    private function updateDoc( $src, $parms )
    /*****************************************
        Replace the current doc with new content. Either make a new version or replace the top version.

        Same parms as insertDoc
        - docType is not specified because it can't be changed; old versions wouldn't make sense
        - eSrcType is not specified because we have not implemented code for changing it from previous versions.
          In theory this is possible, since the data record stores all the information necessary to look at old versions.
        - parent/siborder are only set on insert or move, not update
     */
    {
        list( $kfrDoc, $kfrDataOld ) = $this->getCurrKfrs();

        // SFILE always reuses the data record, FILE doesn't know how to do that yet (it would be easy if we didn't care about transactional update)
        $eSrcType = $kfrDataOld->value('src');
        $bNewData = (!@$parms['dr_bReplaceCurrVersion'] && $eSrcType != 'SFILE') || $eSrcType == 'FILE';

        if( $bNewData ) {
            $kfrData = $this->oDocRepDB->kfrelDataBase->CreateRecord() or die( "Can't create blank kfrDocData" );
            $kfrData->SetValue( "src", $eSrcType );

            $nVer = $kfrDoc->value('maxVer') + 1;
            $kfrData->SetValue( "ver", $nVer );
            $kfrDoc->SetValue( "maxVer", $nVer );

            // copy the metadata from the old record and allow it to be overwritten by parms later
            $kfrData->SetValue( "verspec", $kfrDataOld->Value('verspec') );
            $kfrData->SetValue( "meta_title", $kfrDataOld->Value('meta_title') );
            $kfrData->SetValue( "metadata", $kfrDataOld->Value('metadata') );
            $kfrData->SetValue( "fk_docrep_docs", $kfrDoc->Key() );

            $kfrData->PutDBRow() or die( "Cannot create docdata record" );
        } else {
            $kfrData = $kfrDataOld;
        }

        $this->insertUpdate_Metadata( $kfrDoc, $kfrData, $parms );

        /* No guarantee that everything here will work (e.g. move_uploaded_file fails), but the doc records have
         * referential integrity at this point.
         */
        switch( $kfrDoc->Value('type') ) {
            case "FOLDER":  break;
            case "LINK":    $kfrData->SetValue( "link_doc", intval($src) );    break;

            default:
                switch( $kfrData->Value('src') ) {
                    case "TEXT":  $kfrData->SetValue( "data_text", $src );               break;
                    case "FILE":
                    case "SFILE": $this->insertUpdate_File( $kfrDoc, $kfrData, $src, $parms );  break;
                }
        }

        // rewrite the records to store the data/metadata
        $kfrData->PutDBRow() or die( "Cannot rewrite docdata row" );
        $kfrDoc->PutDBRow()  or die( "Cannot rewrite doc row" );
        if( ($flag = @$parms['dr_flag']) ) {
            // Make a DXD record (this doesn't change doc or data records)
            $this->oDocRepDB->VersionSetDXDFlag( $kfrDoc->Key(), $kfrData->Key(), $flag );
        }

        $this->clearCache();    // maybe this operation messes up some cached version data so reload from the db

        return( $this->kDoc );
    }

    private function insertUpdate_Metadata( $kfrDoc, $kfrData, $parms )
    /******************************************************************
        Use input parms to set kfrDoc and kfrData values for insertDoc and updateDoc

        dr_posUnder and dr_posAfter are not processed here: insertDoc() handles them
     */
    {
        foreach( $parms as $k => $v ) {
            switch( $k ) {
                case "dr_flag":
                case "dr_posUnder":    // insertDoc() handles these
                case "dr_posAfter":    // insertDoc() handles these
                    break;

                case "dr_name":     $kfrDoc->SetValue( "name", $v );          break;
                case "dr_spec":     $kfrDoc->SetValue( "spec", $v );          break;
                case "dr_permclass":$kfrDoc->SetValue( "permclass", $v );     break;
                case "dr_mimetype": $kfrData->SetValue( "mimetype", $v );     break;
                case "dr_verspec":  $kfrData->SetValue( "verspec", $v );      break;
                case "dr_title":    $kfrData->SetValue( "meta_title", $v );   break;
                case "dr_desc":     $kfrData->SetValue( "meta_desc", $v );      break;
                case "dr_author":   $kfrData->SetValue( "meta_author", $v );    break;
                case "dr_date":     $kfrData->SetValue( "meta_date", $v );      break;
                case "dr_metadata": $kfrData->SetValue( "metadata", UrlParmsPack( $v ) ); break;
            }
        }
    }

    private function insert_ParentSiborder( $kfrDoc, $posUnderParent, $posAfterSibling )
    /***********************************************************************************
     */
    {
// There is no way to insert at (0,1) - the first root position
// Haven't decided whether to allow uncontained documents (0,0)
        if( $posUnderParent ) {
            /* Insert the new document as the first sibling of the given parent
             */
            $i = intval( $this->oDocRepDB->kfdb->Query1( "SELECT MIN(siborder) FROM docrep_docs WHERE docrep_docs_parent='$posUnderParent'" ) );
            if( $i == 1 ) {
                $this->oDocRepDB->kfdb->Execute( "UPDATE docrep_docs SET siborder=siborder+1 WHERE docrep_docs_parent='$posUnderParent'" );
            }
            $kfrDoc->SetValue( "docrep_docs_parent", $posUnderParent );
            $kfrDoc->SetValue( "siborder", 1 );

        } else if( $posAfterSibling ) {
            /* Insert the new document as the next sibling after the given sibling
             */
            $ra = $this->oDocRepDB->kfdb->QueryRA( "SELECT docrep_docs_parent as parent,siborder FROM docrep_docs WHERE _key='$posAfterSibling'" );
            $parent = $ra['parent'];
            $siborder = $ra['siborder'];
            if( $parent || $siborder ) {
                $this->oDocRepDB->kfdb->Execute( "UPDATE docrep_docs SET siborder=siborder+1 WHERE docrep_docs_parent='$parent' and siborder > '$siborder'" );
                $kfrDoc->SetValue( "docrep_docs_parent", $parent );
                $kfrDoc->SetValue( "siborder", $siborder + 1 );
            } else {
                // ???  This condition should probably not happen, so not sure what to do.
                $kfrDoc->SetValue( "docrep_docs_parent", 0 );
                $kfrDoc->SetValue( "siborder", 0 );
            }
        } else {
            // ??? unspecified position - put it at 0,0 = uncontained documents
            $kfrDoc->SetValue( "docrep_docs_parent", 0 );
            $kfrDoc->SetValue( "siborder", 0 );
        }
    }

    private function insertUpdate_File( $kfrDoc, $kfrData, $fname, $parms )
    /**********************************************************************
        "FILE":  fname is the tmp uploaded file.  Calculate its file extension and mimetype, and move it to the DOCREP_UPLOAD_DIR
        "SFILE": fname is the tmp uploaded file or "" if a file equal to the name has already been placed in DOCREP_UPLOAD_DIR."sfile/".
                 Find its file ext and mimetype.
     */
    {
        global $fileExt2Mimetype;

        $fExt = @$parms['dr_fileext'];
        if( empty($fExt) && $kfrData->Value('src') == "SFILE" )  $fExt = substr( strrchr( $fname, '.' ), 1 );
        if( empty($fExt) )  $fExt = substr( strrchr( $kfrDoc->value('name'), '.' ), 1 );
        if( !empty($fExt) ) $kfrData->SetValue( "data_fileext", $fExt );

        if( $kfrData->IsEmpty("mimetype") ) {
            $mimetype = @$fileExt2Mimetype[strtolower($kfrData->Value("data_fileext"))];
            if( empty($mimetype) ) {
                $mimetype = "application/octet-stream";
            }
            $kfrData->SetValue( "mimetype", $mimetype );
        }

        if( $kfrData->Value('src') == "SFILE" ) {
// there could be another parm for sfile_name, which would allow name to be different from sfile_name
            $kfrData->SetValue( "sfile_name", $kfrDoc->value('name') );

            if( $fname ) {
                // a temp file was uploaded
                if( !is_uploaded_file( $fname ) ) {
                    die( "SFILE was not uploaded" );
                }
                $fnameDest = $this->oDocRepDB->GetDataFilename($kfrData, true);

                SEEDStd_MkDirForFile( $fnameDest );
                if( !move_uploaded_file( $fname, $fnameDest ) ) {
                    die( "Cannot move SFILE" );
                }
            } else {
                // assume somebody put a file in the same place as the name
            }
        } else {
            // FILE
            if( !is_uploaded_file( $fname ) ) {
                die( "File was not uploaded" );
            }
            if( !move_uploaded_file( $fname, $this->oDocRepDB->GetDataFilename($kfrData) ) ) {
                die( "Cannot move file" );
            }
        }

    }
}


class DocRepDoc_Insert extends DocRepDoc
/***************************************
    Nobody should be allowed to retrieve a DocRepDoc, and then use it to insert a doc.
    Let's not even expose those methods in DocRepDoc.
    So if you want to insert a new doc, use this object.
 */
{
    function __construct( DocRepDB $oDocRepDB )
    {
        parent::__construct( $oDocRepDB, 0 );
    }

    function InsertFolder( $parms = array() )
    /****************************************
     */
    {
        if( $this->GetKey() )  return( false );                        // $this must be a blank DocRepDoc

        if( @$parms['dr_name'] ) {
            $parms['dr_name'] = $this->makeUniqueName( $parms['dr_name'] );
        }

        return( $this->insertDoc( 'FOLDER', '', '', $parms ) );
    }

    function InsertText( $sText, $parms = array() )
    /**********************************************
        Create a TEXT document

        $this must be a blank DocRepDoc
     */
    {
        if( $this->GetKey() )  return( false );                        // $this must be a blank DocRepDoc

        if( @$parms['dr_name'] ) {
            $parms['dr_name'] = $this->makeUniqueName( $parms['dr_name'] );
        }

        return( $this->insertDoc( 'TEXT', 'TEXT', $tmp_fname, $parms ) );
    }

    function InsertFile( $tmp_fname, $parms = array() )
    /**************************************************
        Create a FILE document

        $this must be a blank DocRepDoc
     */
    {
        if( $this->GetKey() )  return( false );                        // $this must be a blank DocRepDoc

        if( @$parms['dr_name'] ) {
            $parms['dr_name'] = $this->makeUniqueName( $parms['dr_name'] );
        }

        return( $this->insertDoc( 'DOC', 'FILE', $tmp_fname, $parms ) );
    }

    function InsertSFile( $name, $tmp_fname = "", $parms = array() )
    /***************************************************************
        Create an SFILE document with the given name.
            name      = the full path name in sfile space (required)
            tmp_fname = optional file to copy (typically is_uploaded_file) - if not specified, assume somebody put the file in sfile
            parms     : permclass, comment, metadata

        $this must be a blank DocRepDoc
     */
    {
        if( $this->GetKey() )  return( false );                        // $this must be a blank DocRepDoc

        if( $name ) {
            $name = $this->makeUniqueName( $name );
        }
        $parms['dr_name'] = $name;

        return( $this->insertDoc( 'DOC', 'SFILE', $tmp_fname, $parms ) );
    }

    function InsertLink( $kLinkDoc, $parms = array() )
    /*************************************************
     */
    {
        if( $this->GetKey() )  return( false );                        // $this must be a blank DocRepDoc

        if( @$parms['dr_name'] ) {
            $parms['dr_name'] = $this->makeUniqueName( $parms['dr_name'] );
        }

        return( $this->insertDoc( 'LINK', '', '', $parms ) );
    }
}



$fileExt2Mimetype = array(
    "bmp"   =>  "image/bmp",
    "ico"   =>  "image/x-icon",
    "gif"   =>  "image/gif",
    "jpg"   =>  "image/jpeg",
    "jpeg"  =>  "image/jpeg",
    "png"   =>  "image/png",
    "tif"   =>  "image/tiff",
    "tiff"  =>  "image/tiff",
    "eps"   =>  "application/postscript",
    "pdf"   =>  "application/pdf",
    "rtf"   =>  "application/rtf",
    "zip"   =>  "application/zip",
    "js"    =>  "application/x-javascript",

    "doc"   =>  "application/msword",
    "dot"   =>  "application/msword",
    "xls"   =>  "application/vnd.ms-excel",
    "ppt"   =>  "application/vnd.ms-powerpoint",
    "mdb"   =>  "application/x-msaccess",
    "pub"   =>  "application/x-mspublisher",

    "css"   =>  "text/css",
    "htm"   =>  "text/html",
    "html"  =>  "text/html",
    "txt"   =>  "text/plain",

    "mp3"   =>  "audio/mpeg",
    "wav"   =>  "audio/x-wav",
    "mpg"   =>  "video/mpeg",
    "mpeg"  =>  "video/mpeg",
    "mov"   =>  "video/quicktime",
    "qt"    =>  "video/quicktime",
    "avi"   =>  "video/x-msvideo",
);

?>
