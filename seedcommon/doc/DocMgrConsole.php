<?php

// friend can write to doc 50 if you enable the UI again - is it because permclass is not being checked?

//Publish button should preserve the mode, so you can save in the editor, publish, and stay in the editor

// Expansions in variables:
//   %name% = this doc's name
//   %parent% = parent's name
//   %folder% = this doc's name minus its leaf if there is one
//

// Can I have more than one tree open in multiple tabs? That would rock.

/*
 * DocMgrConsole
 *
 * Copyright 2012-2015 Seeds of Diversity Canada
 *
 * Implement a console application for the docrep manager
 *
 * Caller must set:
 *
 * SITE*
 * SEEDCOMMON
 *
 * $DocMgrParms['title']
 */

include_once( SEEDCOMMON."siteStart.php" );
include_once( SEEDCOMMON."siteApp.php" );
include_once( SEEDCOMMON."console/console01.php" );
include_once( SEEDCOMMON."doc/docUtil.php" );
include_once( STDINC."DocRep/DocRepImg.php" );
include_once( STDINC."DocRep/DocRepApp02.php" );


/* Allow readers to see the application; doc access controlled by SEEDPerms
 */
list($kfdb, $sess) = SiteStartSessionAccount( array("DocRepMgr"=>"R") );
//$kfdb->SetDebug(2);
//var_dump($_REQUEST);
//var_dump($_SESSION);

class MyDocRepApp extends DocRepApp02 {
    private $oPerms;
    private $sess;
    private $raSVA = array();  // SessionVarAccessors for docmgrs (array keyed by docmgr id)

    function __construct( KeyFrameDB $kfdb, SEEDSessionAccount $sess )
    {
        $this->sess = $sess;

        // note that DocRepApp02 instantiates raSVA via derived methods, so the Session setup happens here and the SVA is stored in DocRepApp
        parent::__construct( $kfdb, $sess->GetUID(), DOCREP_KEY_HASH_SEED );
        $this->oPerms = New_DocRepSEEDPermsFromUID( $kfdb, $sess->GetUID() );
    }

    function GetUserName( $uid )
    /***************************
     */
    {
        switch( $uid ) {
            case 1499:  return( "Bob" );
            case -2:    return( "Judy" );
            case -3:    return( "Val&eacute;rie" );
            case -5:    return( "Diana" );
            case 10923: return( "Val&eacute;rie" );
        }
        return( "user #".$uid );
    }

    /* PermClass overrides.  DocRepApp01 is not dependent on SEEDPerms, so it has methods to retrieve information
     * about the docrep_docs.permclass integers. These methods are intended to be overridden by a derived class that
     * knows something about permissions
     */
    function GetPermClassName( $permclass )
    /**************************************
     */
    {
        return( $this->oPerms->GetClassName( $permclass ) );
    }

    function EnumPermClassNames( $mode )
    /***********************************
     */
    {
        return( $this->oPerms->EnumClassNames( "W" ) );
    }

/*
    function IsPermClassModeAllowed( $kDoc, $mode )
    {
        $oDoc = new DocRepDoc( $this->oDocMgr->oDocRepDB, $kDoc );
        return( $oDoc ? $this->IsPermClassModeAllowed_DocObj( $oDoc, $mode ) : false );
    }

    function IsPermClassModeAllowed_DocObj( $oDoc, $mode )
    {
        $p = $oDoc->GetPermclass();
        return( $p ? $this->oPerms->IsClassModeAllowed( $p, $mode ) : false );
    }
*/

    function Session_oSVA( $iDocMgr = 0 )
    /************************************
        Override the virtual methods in DocRepApp02.  The base class doesn't know about sessions, so this is used to store session variables.
        Actually implements multiple session spaces for multiple docmgrs.
     */
    {
        if( !isset($this->raSVA[$iDocMgr]) )  $this->raSVA[$iDocMgr] = new SEEDSessionVarAccessor( $this->sess, 'DocMgr'.$iDocMgr );
        return( $this->raSVA[$iDocMgr] );
    }

    function SessionVarSet( $k, $v, $iDocMgr = 0 )
    /*********************************************
     */
    {
        $this->Session_oSVA($iDocMgr)->VarSet( $k, $v );
    }

    function SessionVarGet( $k, $iDocMgr = 0 )
    /*****************************************
     */
    {
        return( $this->Session_oSVA($iDocMgr)->VarGet( $k ) );
    }

    function Style()
    {
        $s = "<STYLE>"
            .".drcControls { background:white; border:2px groove #ccc; width:100%; }"
            .".drcControls,"
            .".drcControls td"
                    ."{ font-family:verdana,arial,helvetica,sans serif; font-size:8pt; }"
            ."</STYLE>";

        $s .= parent::Style();

        return( $s );
    }
}



class MyConsole extends Console01
{
    var $oDRApp;

    function __construct( KeyFrameDB $kfdb, SEEDSession $sess, $raParms, MyDocRepApp $oDRApp )
    {
        parent::__construct( $kfdb, $sess, $raParms );
        $this->oDRApp = $oDRApp;
    }

    function TabSetInit( $tsid, $tabname )
    {
        if( $tsid == 'TFmain' ) {

        }
    }

    function TabSetPermission( $tsid, $tabname )
    {
        if( $tsid == 'TFmain' ) {
            $perms = array( "Documents" => "R",
                            "Edit"      => "W",
                            "Versions"  => "R",
                            "Files"     => "W",
                            "Admin"     => "A", );

            if( !isset($perms[$tabname]) || !$this->sess->TestPerm( 'DocRepMgr', $perms[$tabname] ) )  return( 0 );  // hide the tab
            if( ($tabname=='Versions') && !$this->oDRApp->oDocMgr->GetDocKey() )                       return( 2 );  // show tab but grey link
            return( 1 );
        } else if( $tsid == 'TFDocControls' ) {
            return( $this->sess->TestPerm( 'DocRepMgr', ($tabname == 'DbFields' ? 'A' : 'W') ) );
        } else if( $tsid == 'TFFileControls' ) {
            return( 1 );
        }
        return( 0 );
    }

    /* main TabSet
     */
    function TFmainDocumentsControl()
    {
        if( !($kDoc = $this->oDRApp->oDocMgr->GetDocKey()) )  return( "" );

        $bPermW = $this->oDRApp->oDocMgr->IsPermClassModeAllowed( "W" );
        $bPermP = $this->oDRApp->oDocMgr->IsPermClassModeAllowed( "P" );

        $s = "<div class='drcControls'>"
            ."<table border='0'><tr>";

        $dt = $this->oDRApp->oDocMgr->GetDocType();
        if( $dt == 'TEXT' ) {
            $bEditMode = in_array( $this->oDRApp->pMode, array( 'edit_text', 'edit_text_new') );

            // Edit || Close Editor button
            $s .= "<td>"
                 ."<form method='POST' action='${_SERVER['PHP_SELF']}'>"
                 .SEEDForm_Hidden( 'k', $kDoc )
                 .($bEditMode
                     ? "<input type='submit' value='Close Editor'/>"
                     : (SEEDForm_Hidden( 'dra_mode', 'edit_text' )
                       ."<input type='submit' value='Edit'".($bPermW ? "" : " disabled='disabled'")."/>")
                  )
                 ."</form>"
                 ."</td><td>"

                 // Open Preview button
                 ."<form method='POST' action='doc.php' target='_blank'>"
                 .SEEDForm_Hidden( 'k', DocRep_Key2Hash($kDoc, $this->oDRApp->oDocUI->keyHashSeed) )
                 ."<input type='submit' value='Open Preview Window'/>"
                 ."</form>"
                 ."</td>";
        }
        if( $dt == 'TEXT' || $dt == 'FOLDER' || $dt == 'DOC' ) {
            // Publish button
            $s .= "<td>"
                 ."<form method='POST' action='${_SERVER['PHP_SELF']}'>"
                 .SEEDForm_Hidden( 'k', $kDoc )
                 .SEEDForm_Hidden( 'drm_action', 'approve' )
                 ."<input type='submit' value='Publish'".($bPermP ? "" : " disabled='disabled'")."/>"
                 ."</form>"
                 ."</td>";
        }

        $s .= "<td valign='top' style='padding-left:30px;'>"
                 ."Title:<br/>Name:</td>"
             ."<td valign='top' style='padding-left:5px;'>"
                 .$this->oDRApp->oDocMgr->GetDocTitle()."<br/>"
                 .$this->oDRApp->oDocMgr->GetDocName()."</td>"
             ."<td valign='top' style='padding-left:30px;'>"
                 ."Doc:<br/>Version:</td>"
             ."<td valign='top' style='padding-left:5px;'>"
                 .$kDoc."<br/>"
                 .$this->oDRApp->oDocMgr->GetDocValue('ver')."</td>"
             ."<td valign='top' style='padding-left:30px;'>"
                 ."Permission:<br/>Content type:</td>"
             ."<td valign='top' style='padding-left:5px;'>"
                 .$this->oDRApp->GetPermClassName($this->oDRApp->oDocMgr->GetDocValue('permclass'))."<br/>"
                 .$this->oDRApp->oDocMgr->GetDocValue('mimetype')."</td>"
             ."<td valign='top' style='padding-left:30px;'>".
                 "Created by:<br/>Updated by:</td>"
             ."<td valign='top' style='padding-left:5px;'>"
                 .$this->oDRApp->GetUserName( $this->oDRApp->oDocMgr->GetDocValue('doc_created_by') )."&nbsp;&nbsp;"
                 .$this->oDRApp->oDocMgr->GetDocValue('doc_created')
                 ."<br/>"
                 .$this->oDRApp->GetUserName( $this->oDRApp->oDocMgr->GetDocValue('doc_updated_by') )."&nbsp;&nbsp;"
                 .$this->oDRApp->oDocMgr->GetDocValue('doc_updated')."</td>"
             ."</tr></table></div>";

        return( $s );
    }

    function TFmainFilesControl()
    {
        $sfile = $this->oDRApp->oDocMgr->GetSFile();
        $sfileFolder = $this->oDRApp->oDocMgr->GetSFileFolder();
        $oSfile = $this->oDRApp->oDocMgr->GetSFileObj();

        $bExists = $sfile && $this->oDRApp->oDocMgr->oDocRepDB->SFileIsFile( $sfile );
        $bPermP = $oSfile && $this->oDRApp->oDocMgr->IsPermClassModeAllowedSfile( "P" );

        if( $oSfile ) {
            // Publish the current file
            $sPublishButton = SEEDForm_Hidden( 'k', $oSfile->GetKey() )
                             .SEEDForm_Hidden( 'drm_action', 'approve' )
                             ."<input type='submit' value='Publish'".($bPermP ? "" : " disabled='disabled'")."/>";
        } else if( $sfileFolder && !$sfile ) {
            // Publish all files in the current folder
            // Allow this regardless of the perms on the contents, and non-approvable files will just be skipped
            $sPublishButton = SEEDForm_Hidden( 'drm_action', 'approve_sfile_folder' )
                             ."<input type='submit' value='Publish All Contents'/>";
        } else {
            $sPublishButton = "<input type='submit' value='Publish' disabled='disabled'/>";
        }


        $s = "<div class='drcControls'>"
            ."<table border='0'><tr>"
            ."<td>"
            // Publish button
            ."<form method='POST' action='${_SERVER['PHP_SELF']}'>".$sPublishButton."</form>"
            ."</td>"
            // Metadata
            ."<td valign='top' style='padding-left:30px;'>"
                .($sfile ? "File:" : "")."<br>"
                ."Folder:</td>"
            ."<td valign='top' style='padding-left:5px;'>"
                .$sfile."<br/>"
                .$sfileFolder."</td>";
        if( $sfile ) {
            $s .= "<td valign='top' style='padding-left:30px;'>"
                      ."Doc:<br/></td>"
                 ."<td valign='top' style='padding-left:5px;'>"
                     .($oSfile ? $oSfile->GetKey() : "")."<br/>"
                     ."</td>"
                 ."<td valign='top' style='padding-left:30px;'>"
                     ."Permission:<br/>Content type:</td>"
                 ."<td valign='top' style='padding-left:5px;'>"
                     .($oSfile ? $this->oDRApp->GetPermClassName($oSfile->GetPermclass()) : "")."<br/>"
                     .($oSfile ? $oSfile->GetValue('mimetype','') : "")."</td>"
                 ."<td valign='top' style='padding-left:30px;'>"
                     ."Created by:<br/>Updated by:</td>"
                 ."<td valign='top' style='padding-left:5px;'>"
                     .($oSfile ? ($this->oDRApp->GetUserName( $oSfile->GetValue('doc_created_by','') )."&nbsp;&nbsp;"
                                  .$oSfile->GetValue('doc_created',''))
                               : "")
                     ."<br/>"
                     .($oSfile ? ($this->oDRApp->GetUserName( $oSfile->GetValue('doc_updated_by','') )."&nbsp;&nbsp;"
                                  .$oSfile->GetValue('doc_updated',''))
                               : "")
                     ."</td>";
        }
        $s .= "</tr></table>"
             ."</div>";

        return( $s );
    }

    function TFmainDocumentsContent()
    {
        $s = "";

        if( in_array( $this->oDRApp->pMode, array( 'edit_text', 'edit_text_new') ) ) {
            $s .= $this->oDRApp->DrawEditor();
        } else {
            $s = "<TABLE border='0' width='100%'><TR><TD valign='top' width='60%'>";
                //."<DIV style='width:45ex;padding:8px;margin:10px;font-family:verdana,arial,helvetica;font-size:9pt;background-color:#eeeeee;'>"
                //."Click on an icon to select a folder/document.<BR>Click on the title to activate it.</DIV>";
            ob_start();
            $this->oDRApp->DrawTreeArea( 0 );
            $s .= ob_get_contents();
            ob_end_clean();
            $s .= "</TD><TD valign='top' class='docrepapp_controlArea_Text' style='padding-left:2em;'>"
                 .$this->ExpandTemplate( "[[TabSet:TFDocControls]]")
                 ."</TD></TR></TABLE>";
        }
        return( $s );
    }

    function TFmainVersionsContent()
    {
        $s = "";

        $s .= "<DIV style='margin-left:3em;'>"
             ."<DIV style='margin:20px;padding:10px;border:1px solid #999;float:right;width:50%'>".$this->oDRApp->oDocUI->DrawVersionPreview()."</DIV>"
             ."<H3>Versions of <U>".$this->oDRApp->oDocMgr->GetDocTitle()."</U> (doc #".$this->oDRApp->oDocMgr->GetDocKey().")</H3>";

        $parms = array();
        // Allow DXD flag management and Delete Versions, only for users with Admin perms on this document.
        // This could be a little more transparent in oDocUI than just a hamfisted 'bAdmin' - which was a kluge.
        if( $this->oDRApp->oDocMgr->PermAdmin() ) {
            $parms['bAdmin'] = 1;
        }
        $s .= $this->oDRApp->oDocUI->DrawDocRepVersions( $parms )
             ."</DIV>";

        return( $s );
    }

    function TFmainFilesContent()
    {
        $s = "<TABLE width='100%' cellpadding='0' cellspacing='0'><TR valign='top'>"
            ."<TD width='60%'>";

        $s .= $this->oDRApp->oDocUI->DrawDocRepSfileTree( "" );    // Show from the root of sfile

        $s .= "</TD><TD valign='top' class='docrepapp_controlArea_Text' style='padding-left:2em;'>"
                 .$this->ExpandTemplate( "[[TabSet:TFFileControls]]");

        $s .= "</TD></TR></TABLE>";

        return( $s );
    }



    /* DocControls TabSet
     */
    function TFDocControlsPreviewControl()  // tsid = DocControls, tabname = Preview
    {
        $s = "";
        $kDoc = $this->oDRApp->oDocMgr->GetDocKey();

        if( $kDoc && $this->oDRApp->oDocMgr->GetDocType() == 'TEXT' ) {

            $selOpts = array( 'Preview'=>'Preview', 'Source'=>'Source' );
            if( $this->oDRApp->oDocMgr->PermWrite() ) {
                $selOpts['Quick Edit'] = 'Quick Edit';
            }
            $s .= "<FORM method='POST' action='{$_SERVER['PHP_SELF']}'>"
                 .SEEDForm_Select( 'dra02_preview_mode', $selOpts,
                                   SEEDSafeGPC_GetStrPlain('dra02_preview_mode'), array('selectAttrs'=>"onChange='submit();'") )
                 ."</FORM>";
        }
        return( $s );
    }


    function TFDocControlsPreviewContent()   { return( $this->oDRApp->dcaPreview() ); }
    function TFDocControlsCreateContent()    { return( $this->oDRApp->dcaCreate() ); }
    function TFDocControlsRenameContent()    { return( $this->oDRApp->dcaRename() ); }
    function TFDocControlsMoveContent()      { return( $this->oDRApp->dcaMove() ); }
    function TFDocControlsVariablesContent() { return( $this->oDRApp->dcaVar() ); }
    function TFDocControlsDbFieldsContent()  { return( $this->oDRApp->dcaAdminDB() ); }



    /* FileControls TabSet
     */
    function TFFileControlsViewContent()     { return( $this->oDRApp->dcaSfileView() ); }
    function TFFileControlsNewContent()      { return( $this->oDRApp->dcaSfileNew() ); }
    function TFFileControlsReplaceContent()  { return( $this->oDRApp->dcaSfileReplace() ); }
    function TFFileControlsRenameContent()   { return( $this->oDRApp->dcaSfileRename() ); }
    function TFFileControlsDeleteContent()   { return( $this->oDRApp->dcaSfileDelete() ); }
    function TFFileControlsSyncContent()     { return( $this->oDRApp->dcaSfileSync() ); }


}

/* Support for SFILE
 */
class myDRImg extends DocRepImgMan
{
    function __construct( DocRepDB $oDocRepDB, $raParms = array() )  { parent::__construct( $oDocRepDB, $raParms ); }
    function Img2Url( $img )
    {
        $k = $this->Img2K($img);
        return( "doc.php?k=$k" );
    }

//    function factory_SEEDFile()
//    {
//        return( new jjcSEEDFile() );
//    }
}


$raConsoleParms = array(
    'HEADER' => $DocMgrParms['title'],
    'CONSOLE_NAME' => "DocMgr",
//    'HEADER_LINKS' => array( array( 'href' => 'mbr_email.php',    'label' => "Email Lists",  'target' => '_blank' ),
//                             array( 'href' => 'mbr_mailsend.php', 'label' => "Send 'READY'", 'target' => '_blank' ) ),

    'TABSETS' => array( "TFmain"         => array( 'tabs' => array( 'Documents' => array( 'label' => "Documents" ),
                                                                    'Versions' => array( 'label' => "Versions" ),
                                                                    'Files' => array( 'label' => "Files" ),
                                                                    'Admin' => array( 'label' => "Admin" ) ) ),
                        "TFDocControls"  => array( 'tabs' => array( 'Preview' => array( 'label' => "Preview" ),
                                                                    'Create' => array( 'label' => "Create" ),
                                                                    'Rename' => array( 'label' => "Rename" ),
                                                                    'Move' => array( 'label' => "Move" ),
                                                                    'Variables' => array( 'label' => "Variables" ),
                                                                    'DbFields' => array( 'label' => "DB Fields" ) ) ),
                        "TFFileControls" => array( 'tabs' => array( 'View' => array( 'label'=> "View" ),
                                                                    'New' => array( 'label' => "New" ),
                                                                    'Replace' => array( 'label' => "Replace" ),
                                                                    'Rename' => array( 'label' => "Rename" ),
                                                                    'Delete' => array( 'label' => "Delete" ),
                                                                    'Sync' => array( 'label' => "Sync" ) ) ),
));



$drApp = new MyDocRepApp( $kfdb, $sess );
$drApp->DoAction();

$oC = new MyConsole( $kfdb, $sess, $raConsoleParms, $drApp );

if( ($sErrMsg = $drApp->oDocMgr->GetErrorMsg()) ) {
    $oC->ErrMsg( $sErrMsg );
}

echo $drApp->Style();

echo $oC->DrawConsole( "[[TabSet: TFmain]]" );

?>
