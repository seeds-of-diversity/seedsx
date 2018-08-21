<?php

// DocRepDB knows how to store a DocRepository but it doesn't know what the codes mean
// DocRepMgr knows your uid and a client-supplied method to translate it into an array of permclasses (R,W,P,A). It knows how to use that to mediate fetches
// and updates using DocRepDB, through a set of parms that define all the document properties that a UI would use, and an enumerated set of update actions.
// DocRepMgrUI provides UI widgets that use DocRepMgr's parms and update actions.
// DocRepAppNN is a frame for the UI widgets, with its own modes that define different collections of UI pieces.  It could be a derivation of DocRepApp,
// which does the standard set up of the support classes.


// Non-TEXT documents shouldn't download when you click on the title.  That's different behaviour than TEXT, and you get used to
// just clicking on the title to see properties.  There should be a green Download link.
// Actually, no reason we can't show a lot of DOC types in a div or iframe below the tree.  Graphics, anyway.  Maybe pdfs in an iframe?


// Versions shouldn't be a green link, but rather a tab.


// Preview should have a switch to show with/without a defined dr_template.  You do this the same way as DocRepWebsite, if a page
// has a dr_template variable.

// Can't change a Wiki (Links Only) page to a Wiki page.  Have to change it to Plain, then to Wiki.

// class DocRepApp defines the control structure for app modes (view, edit, rename, versions) and page transitions
// class DocRepAppUI defines the UI layout for each mode, built out of DocRepAppUIComp
// class DocRepAppUIComp defines the drawing and behaviour of UI components (tabs, tree, preview)
// class DocRepAppUIEditor does what DocRepAppUI and DocRepAppUIComp would do for the editor - all packaged here instead
// two main variables are mode (persistent) and action (non-persistent)
// mode tells DocRepAppUI which layout to show, and there may be other persistent submode variables starting
//     with a certain prefix which are erased when mode changes, or which are propagated explicitly per-page
// action is handled before drawing begins.  Maybe it goes to a central handler, or maybe each DocRepAppUI has a separate handler.



/* Make DocRepApp01 a derivation of DocRepApp
 * Split the actions into DocRep actions and UI actions.
 * DocRep actions are handled by a method in DocRepApp, which takes a set of commands that may be different than the action names. Derived class
 * typically calls this method as needed, and marshals the action strings to do the right thing at page init.
 * UI actions are marshalled into a UI state setup, and that state defines the behaviour of the various UI pieces.
 * A method draws the screen by positioning the pieces (maybe this is a DocRepApp method).
 */

/* DocRepApp01
 *
 * Copyright (c) 2006-2011 Seeds of Diversity Canada
 *
 * Application class for a DocRep document manager
 *
 * Editor options:
 *
 *  Default:    a plaintext control in which you can type html
 *  Wiki:       a plaintext control in which you can type wiki markup
 *  TinyMCE:    wysiwyg editor
 */

// TODO: This refers to doc.php and docdiff.php, which is not part of DocRepApp01
//       Most of the reusable UI components should be moved to DocRepMgrUI.php, in overridable methods
//       Mode to show versions of a doc with DXD flags, control area shows metadata and allows update of DXD flags, Delete versions (in admin mode)?
//       Move
//       Delete (which actually just removes the PUB flag?), actual delete in admin mode

include_once( "DocRep.php" );
include_once( "DocRepMgr.php" );
include_once( "DocRepWiki.php" );

include_once( STDINC."SEEDForm.php" );
include_once( STDINC."SEEDEditor.php" );


define( "DOCREPAPP01_SEEDPERMS_APP", "DocRep" );    // the app name in SEEDPerms


function kluge_MyDoc()
{
    // address of the script that serves images on this server (should be elevated to a method, parm, or callback in seedcommon)
    return( SITEROOT."int/doc/doc.php" );
}



class DocRepAppTextTypes
{
    /* public */ var $raTextTypes = array("TEXTTYPE_PLAIN", "TEXTTYPE_HTML", "TEXTTYPE_WIKI", "TEXTTYPE_WIKILINK");
    /* public */ var $raFullNames = array("TEXTTYPE_PLAIN"=>"Plain Text", "TEXTTYPE_HTML"=>"HTML", "TEXTTYPE_WIKI"=>"Wiki", "TEXTTYPE_WIKILINK"=>"Wiki (Links Only)");

    function DocRepAppTextTypes()  {}

    function NormalizeTextType( $tt, $ttDefault = "TEXTTYPE_PLAIN" )
    /***************************************************************
        Return $tt if valid, $ttDefault if not
     */
    {
        return( in_array($tt, $this->raTextTypes) ? $tt : $ttDefault );
    }

    function GetFromTagStr( $s )
    /***************************
        Parse the texttype out of the given tag str (string with a texttype surrounded by spaces
     */
    {
        $eTextType = "";
        if( ($s = strstr($s, ' TEXTTYPE_')) !== false ) {       // $s is the tagstr starting at ' TEXTTYPE_'...
            if( ($s = strtok( $s, " " )) !== false ) {          // $s is the texttype after ' TEXTTYPE_'
                $eTextType = $s;
            }
        }
        return( $eTextType );
    }

    function GetFullName( $tt )
    /**************************
     */
    {
        return( array_key_exists($tt,$this->raFullNames) ? $this->raFullNames[$tt] : "Unknown Type" );
    }

    function TagStrContainsWiki( $s )
    /********************************
        Return true if $s contains a WIKI texttype surrounded by spaces
     */
    {
        return( strstr($s, ' TEXTTYPE_WIKI     ') !== false ||
                strstr($s, ' TEXTTYPE_WIKILINK ') !== false );
    }

}


class ConsoleTabBook {
    var $raParms;

    function ConsoleTabBook( $raParms = array() )
    {
        $this->raParms = $raParms;  // copy the array because we'll modify it

        if( !@$this->raParms['fnSessionVarSet'] )  $this->raParms['fnSessionVarSet'] = array($this,"dummy");
        if( !@$this->raParms['fnSessionVarGet'] )  $this->raParms['fnSessionVarGet'] = array($this,"dummy");
    }

    function DrawTabBook( $id, $raTabBook, $raParms )
    {
        $s = "<STYLE>"
            .".console_guideText_small  { font-size:8pt;font-family:verdana,geneva,arial,helvetica,sans-serif; }"
            .".console_TabBookTab       { }"
            .".console_TabBookTabSelected { font-weight:bold; }"
            ."</STYLE>";

        if( empty($raParms['tabDestUrl']) )  $raParms['tabDestUrl'] = $_SERVER['PHP_SELF'];

        $currTab = @$_REQUEST["consoleTab${id}"];
        if( empty($currTab) ) { $currTab = call_user_func($this->raParms['fnSessionVarGet'], "consoleTab${id}" ); }
        if( empty($currTab) ) { reset($raTabBook); $currTab = key($raTabBook); }            // default tab is the first key of the array
        call_user_func($this->raParms['fnSessionVarSet'], "consoleTab${id}", $currTab );    // set the tab name in a session var

        $s .= "<TABLE border='1'><TR>";
        foreach( $raTabBook as $k => $v ) {
            $s .= "<TD width='50' class='".($currTab==$k ? "console_TabBookTabSelected" : "console_TabBookTab")."'><A HREF='${raParms['tabDestUrl']}?${raParms['tabDestUrlQuery']}&consoleTab${id}=$k'>$k</A></TD>";
        }
        $s .= "</TR></TABLE>";

        if( ($fn = @$raTabBook[$currTab]) ) {
            $s .= call_user_func($fn);
        }
        return( $s );
    }

    function dummy()
    {
        // give the SessionVarSet/Get variables a valid function to point to, to prevent errors if not set (though session vars won't work)
    }
}


class DocRepApp01 {
    var $oDocMgr;
    var $oDocUI;
    var $oTextTypes;

    var $iDocSelVersion = 0;
    var $pAction = '';
    var $pMode = '';
    var $bSaveNewVersionDone = false;       // Set when a doc is saved, and propagated as long as the user stays in that edit session.
                                            // Purpose is to enable the Save (no new version) button.

    function DocRepApp01( &$kfdb, $uid, $keyHashSeed )
    /*************************************************
     */
    {
        $this->oDocMgr = new DocRepApp01_DocRepMgr( $kfdb, $uid );
        $this->oDocUI = new DocRepApp01_DocRepMgrUI( $this->oDocMgr, $keyHashSeed );

        $this->oTextTypes = new DocRepAppTextTypes();

        /* Get state from $_REQUEST and validate
         */
        $kDoc = SEEDSafeGPC_GetInt( 'k' );
        $this->oDocMgr->SetDocKey( $kDoc );

        $this->pMode = SEEDSafeGPC_Smart( 'dra01_mode',
                array(
                "",
                "show_insert_folder",   // show the create folder form
                "show_insert_file",     // show the insert-upload form
                "show_insert_text",     // show the text editor with blank input
                "view_text",            // show the selected text doc in the text area
                "edit",                 // show the update form for the selected folder/doc (as prep_insert_*)
                "show_versions"         // show the versions of the selected doc
                ));


        $this->pAction = SEEDSafeGPC_Smart( 'dra01_action',
                array(
                "",
                "insert_folder",        // after show_insert_folder: create a new folder
                "insert_file",          // after show_insert_file: upload a new file
                "insert_text",          // after show_insert_text: new doc with text from editor control
                "rename",               // after prep_rename: change the maxVer name/title/metadata (not a new version)
                "update_vars",          // on Variables tab: update the metadata of the maxVer (not a new version)
             // "update_folder",        // after edit: update metadata for the selected folder (creates a new version)  *** though this is fully implemented, the functionality is also performed by rename, so the UI does not issue this action
                "update_file",          // after edit: upload a file as a new version of the selected doc
                "update_text",          // after edit: editor control provides a new version of the selected doc
                "move_up",              // move to position after parent
                "move_down",            // move to first child of prev sib
                "move_right",           // swap with next sib
                "move_left",            // swap with prev sib
                "approve",              // give PUB flag to maxVer - only accessible if permsclass allows "aPprove"
                "dra01_ver_update"      // multiplexes ver_dxd_update and ver_delete in this app
                ));

        if( $this->pMode == 'edit' ) {
            if( $this->oDocMgr->GetDocKey() ) {
                switch( $this->oDocMgr->GetDocType() ) {
                    case "TEXT":    $this->pMode = 'edit_text';       break;
                    case "FOLDER":  $this->pMode = "";                break;  // edit_folder: at present, all folder modification is done by Rename
                    default:        $this->pMode = 'edit_file';       break;
                }
            } else {
                $this->pMode = "";
            }
        }

        if( $this->pMode == 'show_versions' && $this->oDocMgr->GetDocKey() ) {
            $this->iDocSelVersion = SEEDSafeGPC_GetInt( 'v' );
            if( !$this->iDocSelVersion ) {
                // default to the maxVer
                $this->iDocSelVersion = $this->oDocMgr->GetDocValue('maxVer');
            }
        }

    }

    function SessionVarSet( $k, $v ) { /* derived class should override this to provide access to session var storage */ }
    function SessionVarGet( $k )     { /* derived class should override this to provide access to session var storage */ return(NULL); }


    function DoAction()
    /******************
     */
    {
        if( $this->pAction == 'dra01_ver_update' ) {
            // This app multiplexes these actions in one form
            switch( $_REQUEST['dra01_ver_update_action'] ) {
                case 'Change':   $this->pAction = 'ver_dxd_update';  break;
                case 'Delete':   $this->pAction = 'ver_delete';      break;
                default:  return( false );
                }
                }


        $raParms = array();
        switch( $this->pAction ) {
            case 'insert_text':
            case 'update_text':
                    $bDoAction = false;

                $raParms = $this->_getInsertParms();
                $eTextType = $this->oTextTypes->NormalizeTextType( SEEDSafeGPC_GetStrPlain('dra01_texttype'), "TEXTTYPE_WIKI" );

                    // Submit buttons: Save new version, Save, Preview, Edit
                switch( @$_REQUEST['dra01_textsubmit'] ) {
                        case 'Save':
                        $raParms['dr_bReplaceCurrVersion'] = true;
                            $bDoAction = true;
                            break;
                        case 'Save new version':
                            $bDoAction = true;
                            break;
                        default:
                        if( $eTextType == 'TEXTTYPE_HTML' && empty($_REQUEST['dra01_textsubmit']) ) {
                                $bDoAction = true;      // probably the user clicked the save button on the TinyMCE toolbar - make a new version
                            }
                            break;
                    }

                $verspec = SEEDStd_TagStrAdd( @$raParms['dr_verspec'], $eTextType, $this->oTextTypes->raTextTypes );
                if( !$bDoAction && $verspec != @$raParms['dr_verspec'] ) {
                        /* The user changed the text type.  Do a Save (not a new version)
                         */
                    $raParms['dr_bReplaceCurrVersion'] = true;
                        $bDoAction = true;
                    }
                $raParms['dr_verspec'] = $verspec;

// $this->pAction = 'edit_text';
// this caused a bug that over-wrote the parent.
// if a new document created with text editor (mode=show_insert_text), and Preview is clicked before Save,
// we enter here because action=insert_text due to hidden var in the Create Document form. The code above correctly
// keeps $bDoAction==false, so no db insert is performed, but then we enter edit_text here.
// The problem is that $kDoc is currently the parent, because it's overloaded to indicate the parent doc on inserts.
// The Edit Document form is shown, with the submitted data from previous screen, but the parent doc loaded.  Somehow
// you can click Save new version and overwrite the parent data (possibly dr_bReplaceCurrVersion gets set), or maybe
// Save [not new version] is available and it can be clicked.  Somehow, parent data is overwritten on the next Save.
// The solution below just avoids the problem.
// The correct solution would be to not overload $kDoc: use ($kDoc==0,$kParent=N) on inserts. The code is getting complex now, so the
// old method is too error-prone.
                $this->pMode = ($bDoAction || $this->pAction=='update_text') ? 'edit_text' : 'show_insert_text';   // continue in edit mode


                if( !$bDoAction )   return( false );

                $raParms['dr_name'] = $this->oDocMgr->GetNewDocName( SEEDSafeGPC_GetStrPlain('doc_name'),
                                                                     ($this->pAction == 'insert_text' && intval(@$raParms['dr_posUnder'])) );
                $raParms['sText'] = SEEDSafeGPC_GetStrPlain( 'doc_text' );

                // enable the Save (not new version) button, because the user has saved at least one new version
                $this->bSaveNewVersionDone = true;
                break;


            case 'insert_folder':
            case 'update_folder':
                $raParms = $this->_getInsertParms();
                $raParms['dr_name'] = $this->oDocMgr->GetNewDocName( SEEDSafeGPC_GetStrPlain('doc_name'),
                                                                     ($this->pAction == 'insert_folder' && intval(@$raParms['dr_posUnder'])) );
                break;

            case 'insert_file':
            case 'update_file':
                if( !isset( $_FILES['docmgr'] ) )  return( false );
                if( !is_uploaded_file( $_FILES['docmgr']['tmp_name']) ) {
                    $this->oDocMgr->SetErrorMsg( "The file did not upload: there is a limit of 10 megabytes for files" );
                    return( false );
                }
                $raParms = $this->_getInsertParms();
                if( SEEDSafeGPC_GetInt('doc_name_bNameFromFilename') ) {
                    $sName = $_FILES['docmgr']['name'];
                } else {
                    $sName = SEEDSafeGPC_GetStrPlain('doc_name');
                }
                $raParms['dr_name'] = $this->oDocMgr->GetNewDocName( $sName, ($this->pAction == 'insert_file' && intval(@$raParms['dr_posUnder'])) );
                // DocRepDB uses the fileext of the doc name to get mimetype, unless dr_fileext is specified.
                // Since users will not do the right thing in keeping the correct fileext on the name, get fileext from the client-side file name.
                $raParms['dr_fileext'] = substr( strrchr( $_FILES['docmgr']['name'], '.' ), 1 );
                $raParms['sFilename'] = $_FILES['docmgr']['tmp_name'];
                break;

            case 'rename':
                $raParms = $this->_getUpdateParms( "title name permclass desc spec verspec" );
                $raParms['bRenameDescendants'] = true;
                break;

            case 'update_vars':
                $raParms = $this->_getUpdateParms( "vars spec" );
                break;

            case 'ver_dxd_update':
                // raParms['dr_dxdflags'] = array('flag1'=>k1, 'flag2'=>0)  :  remove any existing flag1 and flag2, insert new flag1 (k is fk_doc_data)
                //                                                             a doc_data can have multiple flags, but flags are unique per doc
                $raParms['dr_dxdflags'] = array();
                foreach( $_REQUEST as $k => $v ) {  // verdxd{kDocData} = {flag}[ {flag}]
                    if( substr( $k, 0, 6 ) != "verdxd" ) continue;

                    $k = intval( substr( $k, 6 ) );
                    $v = trim(SEEDSafeGPC_MagicStripSlashes($v));
                    if( !empty( $v ) ) {
                        $raV = explode( ' ', $v );
                        foreach( $raV as $flag ) {
                            $raParms['dr_dxdflags'][$flag] = $k;
                    }
                }
                }
                break;

            case 'ver_delete':
                // raParms['dr_verdelete'] = array(kDocData1, kDocData2, ...)
                $raParms['dr_verdelete'] = array();
                foreach( $_REQUEST as $k => $v ) {  // verdel{kDocData} = 1
                    if( substr( $k, 0, 6 ) != "verdel" || $v != 1 ) continue;

                    if( ($kDocData = intval( substr($k,6) )) ) {
                        $raParms['dr_verdelete'][] = $kDocData;
                    }
                }
                break;

            // No parms
            case 'move_up':
            case 'move_down':
            case 'move_left':
            case 'move_right':
            case 'approve':
                break;

        }

        $kDocUpdated = $this->oDocMgr->Update( $this->pAction, $raParms );

        if( $kDocUpdated && in_array( $this->pAction, array('insert_folder', 'insert_file', 'insert_text') ) ) {
            // If a new folder or doc was inserted, make it the current doc
            $this->oDocMgr->SetDocKey( $kDocUpdated );
        }
        return( $kDocUpdated != 0 );
    }

    function _getUpdateParms( $sParmList )
    {
                    $raParms = array();

        $raP = explode( ' ', $sParmList );
        foreach( $raP as $sP ) {
            switch( $sP ) {
            	// not all parms are implemented by the UI (e.g. spec is available only if PermAdmin) so these methods test for existence
                case 'title':     $this->_getUpdateParmStr( 'dr_title', 'doc_title', $raParms );                                break;
                case 'name':      $raParms['dr_name'] = $this->oDocMgr->GetNewDocName( SEEDSafeGPC_GetStrPlain('doc_name') );   break;  // should probably check for existence, but it's always defined in the UI if it's requested in the $sParmList
                case 'permclass': $this->_getUpdateParmInt( 'dr_permclass', 'doc_permclass', $raParms );                        break;
                case 'desc':      $this->_getUpdateParmStr( 'dr_desc', 'doc_desc', $raParms );                                  break;
                case 'spec':      $this->_getUpdateParmStr( 'dr_spec', 'doc_spec', $raParms );                                  break;
                case 'verspec':   $this->_getUpdateParmStr( 'dr_verspec', 'doc_verspec', $raParms );                            break;

                case 'pos':
                    $raParms['dr_posAfter'] = ( SEEDSafeGPC_GetStrPlain('dr_pos') == 'after' ? $this->oDocMgr->GetDocKey() : 0 );
                    $raParms['dr_posUnder'] = ( SEEDSafeGPC_GetStrPlain('dr_pos') != 'after' ? $this->oDocMgr->GetDocKey() : 0 );
                    break;

                case 'vars':
                    foreach( $_REQUEST as $p => $k ) {      // p is doc_varkN, k is the key of the variable
                        if( substr($p,0,8) == "doc_vark" && !empty($k) ) {
                            if( ($n = intval(substr($p,8))) ) {
                                $raParms['dr_metadata'][$k] = SEEDSafeGPC_GetStrPlain( "doc_varv$n" );
                            }
                        }
                    }
                break;
        }
        }
        return( $raParms );
    }

    function _getUpdateParmStr( $kP, $sP, &$raParms )  { if( isset($_REQUEST[$sP]) )  $raParms[$kP] = SEEDSafeGPC_GetStrPlain( $sP ); }
    function _getUpdateParmInt( $kP, $sP, &$raParms )  { if( isset($_REQUEST[$sP]) )  $raParms[$kP] = SEEDSafeGPC_GetInt( $sP ); }


    function _getInsertParms()
    {
        $ra = $this->_getUpdateParms( "title permclass desc pos spec verspec" );  // callers get name separately
        $ra['dr_flag'] = "";
        return( $ra );
                }


    function Style()
    /***************
     */
    {
        echo "<STYLE>"
            .".DocRepTree_level           { font-family:verdana,arial,helvetica,sans serif; font-size:10pt; }"
            .".DocRepApp_controlArea_Text { font-family:verdana,arial,helvetica,sans serif; font-size:10pt; }"
            .".DocRepApp_treeControls     { font-family:verdana,arial,helvetica,sans serif; font-size:8pt; color:green; }"
            ."td, th                      { font-size:10pt; }"  // this applies to user tables in the TextArea, because not inherited from the style attr in the TextArea div. This means all other tables in the app might need explicit size
            ."</STYLE>";
        echo $this->oDocUI->Style();
    }

    function DrawTreeArea( $keyTree )
    /********************************
     */
    {
        if( $this->pMode == "show_versions" ) {
            echo "<DIV style='margin-left:3em;'>";
            echo "<H3>Versions of <U>".$this->oDocMgr->GetDocTitle()."</U> (doc #".$this->oDocMgr->GetDocKey().")"
                 .SEEDStd_StrNBSP('',5)
                 ."<A HREF='${_SERVER['PHP_SELF']}?k=".$this->oDocMgr->GetDocKey()."'>Back to Main View</A>"
                 ."</H3>";

            $parms = array();
            // Allow DXD flag management and Delete Versions, only for users with Admin perms on this document.
            // This could be a little more transparent in oDocUI than just a hamfisted 'bAdmin' - which was a kluge.
            if( $this->oDocMgr->PermAdmin() ) {
                $parms['bAdmin'] = 1;
            }
            $this->oDocUI->DrawDocRepVersions( $this->oDocMgr->GetDocKey(), $this->iDocSelVersion, $parms );

            echo "</DIV>";
        } else {
            /* Show the current DocRepTree
             */
            // no box style is defined for the whole TreeArea, but it would be here
            echo $this->oDocUI->DrawDocRepTree( $keyTree );
        }
    }

    function drawMainLink( $kDoc, $raParms, $label )
    {
        $sQ = "k=".$kDoc;
        foreach( $raParms as $k => $v ) {
            $sQ .= "&".$k."=".urlencode($v);
        }
        return( "<A href='${_SERVER['PHP_SELF']}?$sQ'>$label</A>" );
    }

    function dcaCreate()
    {
        $s = "";
        if( !$this->pMode || $this->pMode == 'view_text' ) {
        	// show the Create menu and details of the selected doc
            if( $this->oDocMgr->GetDocKey() ) {
                if( $this->oDocMgr->PermWrite() ) {
                    $s .= "<P>".$this->drawMainLink( $this->oDocMgr->GetDocKey(), array('dra01_mode'=>'show_insert_folder'), "Create a new folder" )."</P>";
                    $s .= "<P>".$this->drawMainLink( $this->oDocMgr->GetDocKey(), array('dra01_mode'=>'show_insert_text'), "Create a new document with an on-screen editor" )."</P>";
                    $s .= "<P>".$this->drawMainLink( $this->oDocMgr->GetDocKey(), array('dra01_mode'=>'show_insert_file'), "Upload a new file (document, image, etc)" )."</P>";
                }
                $s .= $this->dcaShowInfo();
            }
        } else if( $this->oDocMgr->GetDocKey() && $this->oDocMgr->PermWrite() ) {
            // show 'Create' forms

            switch( $this->pMode ) {
                case 'show_insert_folder':
                    $s .= "<H4>Create Folder</H4>".$this->oDocUI->DrawControls_InsertFolder_Rename( "insert_folder" );
                    break;
                case 'show_insert_file':
                    $s .= "<H4>Upload a New File</H4>".$this->oDocUI->DrawControls_InsertFile( 'insert_file' );
                    break;
                case 'edit_file':
                    $s .= "<H4>Replace the Selected File</H4>".$this->oDocUI->DrawControls_InsertFile( 'update_file' )
                         .$this->dcaShowInfo();
                    break;
                case 'show_versions':
                    $this->draw_controls_versions();
                    break;
                default:
                    break;
            }
        }
        return( $s );
    }

    function dcaRename()
    {
        $s = "<BR/>";
        if( $this->oDocMgr->GetDocKey() ) {
            if( $this->oDocMgr->PermWrite() ) {
                $s .= $this->oDocUI->DrawControls_InsertFolder_Rename( "rename" );
            }
        }
        $s .= $this->dcaShowInfo();
        return( $s );
    }

    function dcaMove()
    {
        $s = "";
        if( $this->oDocMgr->GetDocKey() ) {
            if( $this->oDocMgr->PermWrite() ) {
                $s .= "<P>".$this->drawMainLink( $this->oDocMgr->GetDocKey(), array('dra01_action'=>'move_left'), "Move up" )."</P>";
                $s .= "<P>".$this->drawMainLink( $this->oDocMgr->GetDocKey(), array('dra01_action'=>'move_right'), "Move down" )."</P>";
                $s .= "<P>".$this->drawMainLink( $this->oDocMgr->GetDocKey(), array('dra01_action'=>'move_up'), "Move to parent level" )."</P>";
                $s .= "<P>".$this->drawMainLink( $this->oDocMgr->GetDocKey(), array('dra01_action'=>'move_down'), "Move into sub-level of previous" )."</P>";
            }
            $s .= $this->dcaShowInfo();
        }
        return( $s );
    }

    function dcaVar()
    {
        $s = "";
        if( $this->oDocMgr->GetDocKey() ) {
            $s .= "<P class='console_guideText_small'>These are special values associated with the selected document or folder. e.g. control codes or substitution text</P>"
                 ."<FORM method='POST' action='${_SERVER['PHP_SELF']}'>"
                 .SEEDForm_Hidden( 'dra01_action', 'update_vars' )
                 .SEEDForm_Hidden( 'k', $this->oDocMgr->GetDocKey() )
                 ."<TABLE border=0><TR><TH>Name</TH><TH>Value</TH></TR>";

            $n = 1;
            foreach( $this->oDocMgr->GetDocValue('raMetadata') as $k => $v ) {
                $s .= "<TR>"
                     ."<TD>".SEEDForm_Text( "doc_vark$n", $k )."</TD>"
                     ."<TD>".SEEDForm_Text( "doc_varv$n", $v, "",40 )."</TD>"
                    ."</TR>";
                ++$n;
            }

            for( $i = 0; $i < 3; ++$i ) {
                $s .= "<TR>"
                     ."<TD>".SEEDForm_Text( "doc_vark$n", '' )."</TD>"
                     ."<TD>".SEEDForm_Text( "doc_varv$n", '', "", 40 )."</TD>"
                    ."</TR>";
                ++$n;
            }
            $s .= "</TABLE>";

            if( $this->oDocMgr->PermAdmin() ) {
                $s .= "<P>Spec: ".SEEDForm_Text( 'doc_spec', $this->oDocMgr->GetDocValue('spec') )."</P>";
        }

            $s .= "<BR/><INPUT type='submit' value='Update'/></FORM>"
                 .$this->dcaShowInfo();
    }
        return( $s );
    }

    function dcaShowInfo()
    {
        if( !$this->oDocMgr->GetDocType() ) return( "" );  // in extreme cases where something fails

        $sWhat = ($this->oDocMgr->GetDocType()=='FOLDER' ? 'folder'
                 : ($this->oDocMgr->GetDocType()=='TEXT' ? 'document'
                                                         : 'file'));
        $s = "<BR/><HR/><BR/>"
            ."<DIV style='background-color:white;padding:1em;font-size:9pt;font-family:arial,helvetica,sans serif;'>"
            ."<B>Selected $sWhat</B> (".$this->oDocMgr->GetDocKey().")<BR/>"
            .SEEDStd_ExpandIfNotEmpty( $this->oDocMgr->GetDocTitle(), "<BR/>Title: [[]]" )
            .SEEDStd_ExpandIfNotEmpty( $this->oDocMgr->GetDocName(),  "<BR/>Name: [[]]" )
            ."<BR/>Version: ".$this->oDocMgr->GetDocValue('ver')
            ."<BR/>Created: ".$this->oDocMgr->GetDocValue('doc_created')." by ".$this->GetUserName( $this->oDocMgr->GetDocValue('doc_created_by') )
            ."<BR/>Last update: ".$this->oDocMgr->GetDocValue('doc_updated')." by ".$this->GetUserName( $this->oDocMgr->GetDocValue('doc_updated_by') )
            .SEEDStd_ExpandIfNotEmpty( $this->oDocMgr->GetDocValue('mimetype'), "<BR/>Mimetype: [[]]")
            ."<BR/>Permission: ".$this->GetPermClassName($this->oDocMgr->GetDocValue('permclass'))
            ."</DIV>";

        return( $s );
    }

    function dcaAdminDB()
    {
        return( "Admin DB Fields" );
    }

    function DrawControlArea()
    /*************************
     */
    {
        $oTabBook = new ConsoleTabBook( array("fnSessionVarSet" => array($this, "SessionVarSet"),
                                              "fnSessionVarGet" => array($this, "SessionVarGet")) );


        $raTabBook = array( "Create" => array($this,"dcaCreate"),
                            "Rename" => array($this,"dcaRename"),
                            "Move"   => array($this,"dcaMove"),
                            "Variables" => array($this,"dcaVar") );
        if( $this->oDocMgr->PermAdmin() )  $raTabBook['Edit db fields'] = array($this,"dcaAdminDB");

        $raParms['tabDestUrlQuery'] = "k=".$this->oDocMgr->GetDocKey();
        echo $oTabBook->DrawTabBook( "A", $raTabBook, $raParms );
    }


    function DrawTextArea()
    /**********************
     */
    {
        $s = "";
        switch( $this->pMode ) {
            case 'view_text':
                if( $this->oDocMgr->GetDocType() == 'TEXT' ) {
                    $eTextType = $this->oTextTypes->GetFromTagStr( $this->oDocMgr->GetDocValue('verspec') );
                    $bShowSource = @$_REQUEST['dra01_showsource'];
                    $s .= "<DIV style='background-color:#eeeeee;padding:1em;'>"
                         ."<FORM method='POST' action='${_SERVER['PHP_SELF']}'>"
                         .SEEDForm_Hidden( 'dra01_mode', 'view_text' )
                         .SEEDForm_Hidden( 'k', $this->oDocMgr->GetDocKey() )
                         ."<P style='font-size:10pt'><SPAN style='font-size:14pt;font-weight:bold'>".$this->oDocMgr->GetDocTitle()."</SPAN>"
                        .SEEDStd_StrNBSP("",10)
                        ."This is a ".$this->oTextTypes->GetFullName($eTextType)." page"
                        .SEEDStd_StrNBSP("",10)
                         ."<INPUT type='checkbox' name='dra01_showsource' value='1' onChange='submit();'".($bShowSource ? " CHECKED" : "").">"
                        ."Show source"
                        .SEEDStd_StrNBSP("",10)
                        ."<A HREF='doc.php?k=".DocRep_Key2Hash($this->oDocMgr->GetDocKey(),$this->oDocUI->keyHashSeed)."' target='_blank'>Open Preview Window</A>"
                        ."</P></FORM>";

// TODO: maybe this should be in an iframe, because we want to set styles like the TD-font-size that cannot be set in
//       this DIV, but setting them globally complicates the application UI
//       Note that putting a <STYLE> here affects the whole page, but I don't know why
                    $s .= "<DIV style='background-color:white;border:solid thin black;padding:1em;font-family:arial,helvetica,sans serif;font-size:9pt;'>";
                    if( $bShowSource || $eTextType == "TEXTTYPE_PLAIN" ) {
                        $s .= "<PRE>"
                              .wordwrap( SEEDStd_HSC($this->oDocMgr->GetDocText() ), 150, "\n", 1 )
                             ."</PRE>";
                    } else {
                        switch( $eTextType ) {
                            case "TEXTTYPE_WIKI":
                            case "TEXTTYPE_WIKILINK":
                                $oDocRepWiki = new DocRepWiki( $this->oDocMgr->oDocRepDB, "", array('php_serve_img'=> kluge_MyDoc(),
                                                                                           'php_serve_link'=> 'DO NOT FOLLOW' ) );
                                $s .= $oDocRepWiki->TranslateDoc( $this->oDocMgr->GetDocKey() );      // TranslateDoc knows about TEXTTYPE_WIKI vs TEXTTYPE_WIKILINK
                                break;
                            case "TEXTTYPE_HTML":
                                $s .= $this->oDocMgr->GetDocText();
                                break;
                            case "TEXTTYPE_PLAIN":  // handled as ShowSource
                                break;
                        }
                    }
                    $s .= "</DIV></DIV>";
                }
                break;

            case 'edit_text':
            case 'show_insert_text':
                $s .= "<DIV style='background-color:#eeeeee;padding:1em;'>"
                     .$this->draw_controls_form_insert_text( $this->pMode=='edit_text' ? $this->oDocMgr->GetDocKey() : 0 )
                     ."</DIV>";
                break;

            case 'show_versions':
                if( $this->iDocSelVersion && $this->oDocMgr->GetDocType()=='TEXT' ) {
                    // do this with an oDocMgr/oDoc method like GetDocVerText($ver) or use a parm other than flag
                    $ra = $this->oDocMgr->oDocRepDB->kfdb->KFDB_QueryRA("SELECT * FROM docrep_docdata WHERE fk_docrep_docs='".$this->oDocMgr->GetDocKey()."' AND ver={$this->iDocSelVersion}" );

                    $s .= "<DIV style='background-color:#eeeeee;padding:1em;'>"
                         ."<H3>".$ra['meta_title']."</H3>"
                         ."<DIV style='background-color:white;border:solid thin black;padding:1em;font-family:arial,helvetica,sans serif;font-size:9pt;'>";
                    if( strstr($this->oDocMgr->GetDocValue('verspec'), ' TEXTTYPE_HTML ') === false ) {
                        $s .= "<PRE>".SEEDStd_HSC($ra['data_text'])."</PRE>";
                    } else {
                        $s .= $ra['data_text'];
                    }
                    $s .= "</DIV></DIV>";
                }
                break;
        }

        echo $s;  // return( $s );
        }

    function draw_controls_form_insert_text( $kDoc = 0 )
    /***************************************************
        Draw the form for inserting a text doc

        kDoc == 0 : insert
        kDoc != 0 : update
     */
    {
        $s = "";

// ISN'T THIS JUST $this->pAction?  - or does it get modified after an Update()
// It means 'did the user already hit Save, or is this the initial display of the editor form'
// There should be a clearer way to communicate this
        if( in_array(SEEDSafeGPC_GetStrPlain('dra01_action'), array("insert_text","update_text")) ) {
            // Form has been submitted. Get texttype from UI.
            $eTextType = $this->oTextTypes->NormalizeTextType(SEEDSafeGPC_GetStrPlain('dra01_texttype'));
        } else {
            // Initial draw of this form
// initial editor could be configurable, based on folder variable, etc
            $eTextType = !$kDoc ? "TEXTTYPE_HTML" : $this->oTextTypes->GetFromTagStr($this->oDocMgr->GetDocValue('verspec'));
        }
        if( !$eTextType )  $eTextType = "TEXTTYPE_PLAIN";

        $s .= "<FORM method='POST' action='${_SERVER['PHP_SELF']}'>"
             .SEEDForm_Hidden( 'dra01_action', $kDoc ? "update_text" : "insert_text" )
             .SEEDForm_Hidden( 'k', $this->oDocMgr->GetDocKey() )
             ."<TABLE border=0><TR><TD>"
            ."<H2>".($kDoc ? "Edit Document" : "Create Document").SEEDStd_StrNBSP("",10)."</H2>"
            ."</TD><TD valign='top'>"
            ."Page Type: "
             .SEEDForm_Select( "dra01_texttype",
                              array("TEXTTYPE_HTML"=>"HTML with Wiki Links","TEXTTYPE_WIKI"=>"Wiki","TEXTTYPE_WIKILINK"=>"Wiki (Links Only)","TEXTTYPE_PLAIN"=>"Plain Text"),
                              $eTextType,
                              array( "selectAttrs" => "onChange='submit();'" ) )
            .SEEDStd_StrNBSP("",10)
            ."</TD><TD valign='top'>";

        $oDoc = ($kDoc ? $this->oDocMgr->oDocCurr : new DocRepDoc($this->oDocMgr->oDocRepDB, 0) );

        if( !$kDoc ) {
            $s .= $this->oDocUI->drawControlsTemplate( $oDoc, "<P>[[Position]]</P>" );
        }

        $s .= "</TD></TR></TABLE>";

        $sTemplate = "<TR><TD valign='top'>"
                        ."<TABLE border='0' cellspacing='0' cellpadding='2'>"
                        ."<TR>[[Title]] [[nbsp3]] [[Perms]]    [[nbsp3]] [[Version]]</TR>"
                        ."<TR>[[Name]]  [[nbsp3]] [[Comments]] [[nbsp]]  [[nbsp]][[nbsp]]</TR>"
                        ."</TABLE>"
                    ."</TD></TR>";
        $s .= "<TABLE width='100%' cellpadding='0' cellspacing='0' border='0'>"
             .$this->oDocUI->drawControlsMetaItems( $oDoc, $sTemplate, " ".$eTextType." " )    // third parm is the default verspec (the texttype with spaces)
             ."<TR><TD valign='top'>";


        $docText = SEEDSafeGPC_GetStrPlain('doc_text');
        if( empty($docText) && $kDoc )  $docText = @$this->oDocMgr->GetDocText();

        if( $eTextType=='TEXTTYPE_HTML' ) {
            $oEdit = new SEEDEditor( "TinyMCE" );
            $oEdit->SetFieldName( "doc_text" );
            $oEdit->SetContent( $docText );
            $s .= $oEdit->Editor( array('controls'=>'Joomla', 'width_css'=>'100%','height_px'=>600) );
        } else {
            // texttype can be WIKI, WIKILINK, PLAIN

            $bDrawEditBox = true;
            if( $eTextType != 'TEXTTYPE_PLAIN' ) {
                /* Show preview/edit tabs.
                 *
                 * It would be pretty nice to preserve the Preview/Edit mode through a save, but the save overrides that state.
                 */
                $bDrawEditBox = (@$_REQUEST['dra01_textsubmit'] != 'Preview');

                $s .= "<INPUT type='submit' name='dra01_textsubmit' value='Preview'".(!$bDrawEditBox ? " DISABLED" : "")."/>"
                    .SEEDStd_StrNBSP("",6)
                     ."<INPUT type='submit' name='dra01_textsubmit' value='Edit'".($bDrawEditBox ? " DISABLED" : "")."/>"
                    .SEEDStd_StrNBSP("",6);
            }
            $s .= $this->_draw_controls_form_insert_text_savebuttons();

            if( $bDrawEditBox ) {
                $s .= "<TEXTAREA name='doc_text' rows=40 style='width:100%'>".SEEDStd_HSC($docText)."</TEXTAREA>";
            } else {
                // Show preview for HTML, WIKI, WIKILINK
                $s .= "<DIV style='background-color:white;border:solid thin black;padding:1em;font-family:arial,helvetica,sans serif;font-size:9pt;'>";
                if( $eTextType == 'TEXTTYPE_HTML' ) {
                    $s .= $docText;
                } else {
                    $oDocRepWiki = new DocRepWiki( $this->oDocMgr->oDocRepDB, "", array('php_serve_img'=> kluge_MyDoc(),
                                                                               'php_serve_link'=> 'DO_NOT_FOLLOW' ) );
                    $s .= $oDocRepWiki->TranslateDoc( $this->oDocMgr->GetDocKey(), $docText );        // overrides the stored text using special second parm; TranslateDoc knows WIKI/WIKILINK
                }
                $s .= "</DIV>"
                      .SEEDForm_Hidden( 'doc_text', $docText ); // propagate current edited text
            }
        }
        $s .= "</TD></TR>";

        if( $eTextType == 'TEXTTYPE_HTML' ) {
            $s.= "<TR><TD valign='top'><BR/>"
                 .$this->_draw_controls_form_insert_text_savebuttons()
                 ."</TD></TR>";
        }
        $s .= "</TABLE></FORM>";

        return( $s );
    }

    function _draw_controls_form_insert_text_savebuttons()
    {
        $s = "<INPUT type='submit' name='dra01_textsubmit' value='Save new version'/>";
        if( $this->bSaveNewVersionDone || @$_REQUEST['dra01_SaveNewVersionDone'] ) {
            // A new version was saved on a previous submission in this session, so give the option to Save without making a new version
            $s .= SEEDStd_StrNBSP("",6)."<INPUT type='submit' name='dra01_textsubmit' value='Save'/>";
            // This continues to be propagated as long as the user keeps using buttons on the edit box (Preview, Edit, Save, Save New Version).
            $s .= "<INPUT type='hidden' name='dra01_SaveNewVersionDone' value='1'/>";
        }
        return( $s );
    }

    function draw_controls_versions()
    /********************************
     */
    {
        $s = "";
        if( $this->oDocMgr->GetDocKey() && $this->iDocSelVersion ) {
            // do this with an oDocRepDB method like GetDocVerInfo($kDoc,$ver)
            $ra = $this->oDocMgr->oDocRepDB->kfdb->KFDB_QueryRA("SELECT * FROM docrep_docdata WHERE fk_docrep_docs='".$this->oDocMgr->GetDocKey()."' AND ver={$this->iDocSelVersion}" );

            $s .= "<DIV style='background-color:white;padding:1em;font-size:9pt;font-family:arial,helvetica,sans serif;'>"
                 .SEEDStd_ExpandIfNotEmpty( $ra['meta_title'], "<BR/>Title: [[]]" )
//               name is not versioned, should be shown at the top of the page
                 ."<BR/>Created: ".$this->oDocMgr->GetDocValue('doc_created')." by ".$this->GetUserName($this->oDocMgr->GetDocValue('doc_created_by') )
                 ."<BR/>Last update: ".$ra['_updated']." by ".$this->GetUserName($ra['_updated_by'])
                 .SEEDStd_ExpandIfNotEmpty( $ra['mimetype'], "<BR/>Mimetype: [[]]" )
                 ."</DIV>";
        }
        echo $s;
    }

    /* OVERRIDE THESE
     */
    function GetUserName( $uid )                     { return( "User #".$uid ); }
    function GetPermClassName( $permclass )          { return( "Permclass #".$permclass ); }
    function IsPermClassModeAllowed( $kDoc, $mode )  { return( false ); }
    }


class DocRepApp01_DocRepMgr extends DocRepMgr
    {
    var $raPermsR = array();
    var $raPermsW = array();
    var $raPermsP = array();
    var $raPermsA = array();
    var $raPermsAll = array();
    var $oPerms;

    function DocRepApp01_DocRepMgr( &$kfdb, $uid )
    {
// SEEDPerms and SEEDSession should not be known here. The raPermClasses should be passed into the contructor as parms.
// no they shouldn't, there should be a factory_DocRepDB method
// no not good enough, this needs to know about P and A perms, and an enumeration of permclass names for DocUI (unless DocUI has its own SEEDPerms for that)

        $this->oPerms = New_DocRepSEEDPermsFromUID( New_SiteAppDB(), $uid );
        $this->raPermsR   = $this->oPerms->GetClassesAllowed( "R", false );
        $this->raPermsW   = $this->oPerms->GetClassesAllowed( "W", false );
        $this->raPermsP   = $this->oPerms->GetClassesAllowed( "P", false );
        $this->raPermsA   = $this->oPerms->GetClassesAllowed( "A", false );
        $this->raPermsAll = $this->oPerms->GetClassesAllowed( "RWPA", true );

        $this->DocRepMgr( $kfdb, $uid );
    }

    function GetPermClasses( $mode )
    /*******************************
     */
    {
        switch( $mode ) {
            case 'R':  return( $this->raPermsR );
            case 'W':  return( $this->raPermsW );
            case 'P':  return( $this->raPermsP );
            case 'A':  return( $this->raPermsA );
            default:   die( "DocRepApp01_DocRepMgr::GetPermsClasses not implemented for $mode" );
    }
    }

    function EnumPermClassNames( $mode )
    /***********************************
     */
    {
        return( $this->oPerms->EnumClassNames( "W" ) );
    }
}


class DocRepApp01_DocRepMgrUI extends DocRepMgrUI {
    var $keyHashSeed = '';              // for hashing keys to make links hard to guess
    var $raWebroot = array();           // kDoc of all documents in the DocRep that have spec containing '% WEBROOT %'

    function DocRepApp01_DocRepMgrUI( &$oDocRepMgr, $keyHashSeed )
    {
        $this->keyHashSeed = $keyHashSeed;

        /* Get the Webroots so we can colour-code their contents
         */
        $dbc = $oDocRepMgr->oDocRepDB->kfdb->CursorOpen( "SELECT _key FROM docrep_docs WHERE spec LIKE '% WEBROOT %'" );
        while( $ra = $oDocRepMgr->oDocRepDB->kfdb->CursorFetch($dbc) ) {
            $this->raWebroot[] = $ra[0];
        }
        $oDocRepMgr->oDocRepDB->kfdb->CursorClose($dbc);

        $this->DocRepMgrUI( $oDocRepMgr );
    }

    /* Override the default methods to draw the DocRepTree
     */
    function DrawDocRepTree_titleStart( $k, $v )
    {
        $s = "<A HREF='${_SERVER['PHP_SELF']}?k=$k'>";
        if( $v['type'] == 'FOLDER' ) {
            $s .= "<IMG src='".DOCREP_ICON_DIR."folder.gif' border=0>&nbsp;";
        } else {
            $s .= "<IMG src='".DOCREP_ICON_DIR.$this->_getIconName($v)."' border=0>&nbsp;"
                  ."</A>";

            if( $v['type'] == 'TEXT' ) {
                $s .= "<A HREF='${_SERVER['PHP_SELF']}?k=$k&dra01_mode=view_text'>";
            } else {
                $s .= "<A HREF='doc.php?k=".DocRep_Key2Hash($k,$this->keyHashSeed)."' target='_blank'>";
            }
        }
        $s .= "<NOBR>";
        return( $s );
    }

    function DrawDocRepTree_title( $k, $v )
    {
        /* If the doc is in a Webroot tree (an ancestor has a spec containing the flag " WEBROOT ") colour-code
         * its PUB status.
         *
         * Since GetDocAncestors includes its starting kDoc in the ancestor list, the lookup can be short-cut by
         * starting at each doc's parent. This means that a flat group of docs will do the same query, so the db
         * will probably cache the result effectively.
         */
        $raAnc = $this->oDocRepMgr->oDocRepDB->GetDocAncestors( $v['parent'] );
        $raInt = array_intersect( $this->raWebroot, $raAnc );
        if( count($raInt) ) {
            // this doc is in a Webroot tree, so colour-code it
            $ePub = $this->getPubState($k);
            $s = "<SPAN style='color:".($ePub==0 ? "green" : ($ePub==1 ? "red" : "orange"))."'>";
        } else {
            // not in a Webroot tree, so use default colour
            $s = "<SPAN>";
        }

        $s .= (!empty($v['title']) ? $v['title'] : (!empty($v['name']) ? $v['name'] : "Untitled") );
        $s .= "</SPAN>";

        return( $s );
    }

    function getPubState( $k )
    /* return 0 if doc $k and all descendants are PUB
              1 if doc $k is not PUB
              2 if doc $k is PUB but at least one descendant is not PUB
     */
    {
        // query for a PUB flag on the maxVer of $k
        $flag = $this->oDocRepMgr->oDocRepDB->kfdb->Query1(
            "SELECT DXD.flag as flag FROM docrep_docs Doc, docrep_docdata Data, docrep_docxdata DXD "
           ."WHERE Doc._key='$k' AND Doc._key=Data.fk_docrep_docs AND Doc.maxVer=Data.ver AND Data._key=DXD.fk_docrep_docdata AND DXD.flag='PUB'" );
        if( $flag != 'PUB' ) { return( 1 ); }

        // test each child tree
        if( ($raChildren = $this->oDocRepMgr->oDocRepDB->ListChildren( $k, '' )) ) {
            foreach( $raChildren as $k => $ra ) {
                $ret = $this->getPubState( $k );
                if( $ret > 0 )  return( 2 );
            }
        }
        return( 0 );
    }

    function DrawDocRepTree_titleEnd( $k, $v )
    {
        $s = "</NOBR></A>";
        if( $k == $this->oDocRepMgr->GetDocKey() ) {
            $bPermW = $bPermP = false;
            if( ($p = $this->oDocRepMgr->GetDocValue('permclass')) ) {
                $bPermW = in_array( $p, $this->oDocRepMgr->GetPermClasses("W") );
                $bPermP = in_array( $p, $this->oDocRepMgr->GetPermClasses("P") );
            }

            $s .= SEEDStd_StrNBSP('',10)."<SPAN class='DocRepApp_treeControls'>&lt;==".SEEDStd_StrNBSP('  ');

            $sLinkAction = "<A HREF='${_SERVER['PHP_SELF']}?k=$k&dra01_action=%s' class='DocRepApp_treeControls'>%s</A>";
            $sLinkMode   = "<A HREF='${_SERVER['PHP_SELF']}?k=$k&dra01_mode=%s' class='DocRepApp_treeControls'>%s</A>";

            if( $bPermW ) {
                switch( $this->oDocRepMgr->GetDocType() ) {
                    case 'FOLDER':  $sEdit = "";        break;
                    case 'TEXT':    $sEdit = "Edit";    break;
                    default:        $sEdit = "Replace"; break;
                }
                if( $sEdit ) {
                    $s .= sprintf( $sLinkMode, 'edit', $sEdit ).SEEDStd_StrNBSP('  |  ');
                }
            }

            $s .= sprintf( $sLinkMode, 'show_versions', 'Versions' );

            // Allow aPprovers to approve publication.
            if( $bPermP ) {
                $s .= SEEDStd_StrNBSP('  |  ').sprintf( $sLinkAction, 'approve', 'Pub' );
            }

            $s .= "</SPAN>";
        }
        return( $s );
    }
}





class DocRepMgrUI
/****************
    Implement some basic UI parts.
    This class is not stateless: oDocRepMgr knows which doc is current
 */
{
    var $oDocRepMgr;

    function DocRepMgrUI( &$oDocRepMgr )
    /********************************
     */
    {
        $this->oDocRepMgr = &$oDocRepMgr;
    }

    function Style()
    /***************
     */
    {
        return( "<STYLE>"
               .".DocRepTree_level   { margin-left:3em; margin-bottom:5px; }"
               .".DocRepTree_title   { margin-bottom:3px; }"
               .".DocRepTree_title a { text-decoration:none; }"
               .".DocRepTree_titleSelected { font-weight:bold; }"
               ."</STYLE>\n" );
    }


    function DrawDocRepTree( $kTree, $iLevel = 1 )
    /*********************************************
        Draw the tree rooted at $kTree.
        Don't draw $kTree. This allows the drawn part to be a forest (children of $kTree),
            or a tree with a single root (single child of $kTree).

        $iLevel is a recursion marker for internal use (don't use it).
     */
    {
        $s = "";

// echo "kSelect = ".$this->oDocRepMgr->GetDocKey(); var_dump($this->oDocRepMgr->GetDocAncestors());
        $raTree = $this->oDocRepMgr->oDocRepDB->ListChildTree( $kTree, "", 1 );
// if($kTree==1) var_dump($raTree,$this->oDocRepMgr->GetDocAncestors());
        $s .= "<DIV class='DocRepTree_level'>"           // defines the basic attributes of structure
             ."<DIV class='DocRepTree_level$iLevel'>";   // defines variations per-level, if defined
        foreach( $raTree as $k => $ra ) {
            $s .= "<DIV class='DocRepTree_title'>"
                 .$this->DrawDocRepTree_titleStart( $k, $ra['doc'] )
                 .( $k == $this->oDocRepMgr->GetDocKey() ? "<SPAN class='DocRepTree_titleSelected'>" : "" )
                 .$this->DrawDocRepTree_title( $k, $ra['doc'] )
                 .( $k == $this->oDocRepMgr->GetDocKey() ? "</SPAN>" : "" )  // titleSelected
                 .$this->DrawDocRepTree_titleEnd( $k, $ra['doc'] );
            if( in_array( $k, $this->oDocRepMgr->GetDocAncestors() ) ) {
                $s .= $this->DrawDocRepTree( $k, $this->oDocRepMgr->GetDocKey(), $this->oDocRepMgr->GetDocAncestors(), $iLevel + 1 );
        }
            $s .= "</DIV>";  // title
    }
        $s .= "</DIV>"   // level$level
             ."</DIV>";  // level
        return( $s );
    }


    function DrawDocRepTree_titleStart( $k, $v )
    {
        return( "<A HREF='${_SERVER['PHP_SELF']}?k=$k'><NOBR>" );
    }

    function DrawDocRepTree_title( $k, $v )
    {
        return( !empty($v['title']) ? $v['title'] : (!empty($v['name']) ? $v['name'] : "Untitled") );
    }

    function DrawDocRepTree_titleEnd( $k, $v )
    {
        return( "</NOBR></A>" );
    }

//TODO: insert_folder doesn't show the parent folder prefix on the name because oDoc is a new blank doc
    function DrawControls_InsertFolder_Rename( $nextAction )
    /*******************************************************
        $nextAction == insert_folder: draw the form for inserting a folder - gets dr_pos
                       update_folder: draw the form for updating a folder (renames and makes a new version)
                       rename:        draw the form to rename a folder or document (doesn't make a new version)
     */
    {
        // if inserting a new folder, use a blank oDoc to show blank controls
        $oDoc = ($nextAction == 'insert_folder' ? new DocRepDoc($this->oDocRepMgr->oDocRepDB, 0) : $this->oDocRepMgr->oDocCurr);

        $s = "<FORM method='POST' action='${_SERVER['PHP_SELF']}'>"
            .SEEDForm_Hidden( 'dra01_action', $nextAction )
            .SEEDForm_Hidden( 'k', $this->oDocRepMgr->GetDocKey() );

        if( $nextAction == 'insert_folder' ) {
            $s .= $this->drawControlsTemplate( $oDoc, "<P>[[Position]]</P>" );
        }

        $s .= "<TABLE class='DocRepApp_controlArea_Text'>"
             .$this->drawControlsMetaItems( $oDoc, "<TR>[[Title]]</TR>"
                                                  ."<TR>[[Name]]</TR>"
                                                  ."<TR>[[Perms]]</TR>"
                                                  ."<TR>[[Comments]]</TR>" )
             ."<TR><TD><BR/><INPUT type='submit' value='".($nextAction == 'rename' ? "Rename" : "Create")."'/></TD></TR>"
             ."</TABLE></FORM>";

        return( $s );
    }

    function DrawControls_InsertFile( $nextAction )
    /**********************************************
        $nextAction == insert_file: draw the form for uploading a new file
                       update_file: draw the form for uploading a replacement file
     */
    {
        // if inserting a new folder, use a blank oDoc to show blank controls
        $oDoc = ($nextAction == 'insert_file' ? new DocRepDoc($this->oDocRepMgr->oDocRepDB, 0) : $this->oDocRepMgr->oDocCurr);

        $s = "<FORM enctype='multipart/form-data' method='POST' action='${_SERVER['PHP_SELF']}'>"
            ."<INPUT type='hidden' name='dra01_action' value='$nextAction' />"
            ."<INPUT type='hidden' name='MAX_FILE_SIZE' value='1000000' />"
            ."<INPUT type='hidden' name='k' value='".$this->oDocRepMgr->GetDocKey()."'/>";

        if( $nextAction == 'insert_file' ) {
            $s .= $this->drawControlsTemplate( $oDoc, "<P>[[Position]]</P>" );
        }

        $sTemplate = "<TR>[[Title]]</TR>"
                    ."<TR>[[Name_FileKluge]]</TR>"  // kluge draws extra check box control in the second TD of this field
                    ."<TR>[[Perms]]</TR>"
                    ."<TR>[[Comments]]</TR>";
        $s .= "<TABLE class='DocRepApp_controlArea_Text'>"
             .$this->drawControlsMetaItems( $oDoc, $sTemplate )
             ."<TR><TD>Upload this file: </TD><TD><INPUT name='docmgr' id='docmgr' type='file' size='40'/></TD></TR>"
             ."<TR><TD><BR><INPUT type='submit' value='Upload'/></TD></TR>"
             ."</TABLE></FORM>";
        return( $s );
    }


    function drawControlsTemplate( $oDoc, $sTemplate )
    /*************************************************
        Draw the templated controls for the given oDoc.  The oDoc is either the current doc in oDocRepMgr or a blank one for inserts
     */
    {
        if( strpos( $sTemplate, "[[Title]]" ) !== false ) {
            $s = SEEDForm_TextTD( 'doc_title', $oDoc->GetTitle(''), "Title: ", 50 );
            $sTemplate = str_replace( "[[Title]]", $s, $sTemplate );
        }

        $bNameFileKluge = (strpos( $sTemplate, "[[Name_FileKluge]]" ) !== false);
        if( strpos( $sTemplate, "[[Name]]" ) !== false || $bNameFileKluge ) {
            $sFolderName =  $this->oDocRepMgr->GetDocFolderName();  // on insert, get parent's folder name; on update use current
            $sName = $oDoc->GetName();
            if( !empty($sFolderName) ) {
                if( substr( $sName, 0, strlen($sFolderName)+1 ) == ($sFolderName.'/') ) {
                    $sName = substr( $sName, strlen($sFolderName)+1 );
                }
                if( substr( $sFolderName, -1, 1 ) != '/' ) {
                    $sFolderName .= '/';
                }
            }
            $raSFTD = array('sRightHead'=>"$sFolderName");
            if( $bNameFileKluge )  $raSFTD['sRightTail'] = "&nbsp;&nbsp;&nbsp;<INPUT type='checkbox' name='doc_name_bNameFromFilename' value='1'/> Use file name";

            $s = SEEDForm_TextTD( 'doc_name', $sName, "Name: ", 30, "", $raSFTD );
            $sTemplate = str_replace( $bNameFileKluge ? "[[Name_FileKluge]]" : "[[Name]]", $s, $sTemplate );
        }

        if( strpos( $sTemplate, "[[Version]]" ) !== false ) {
            $s = "<TD valign='top'>Version: </TD><TD valign='top'>".intval($oDoc->GetValue('ver',''))."</TD>";
            $sTemplate = str_replace( "[[Version]]", $s, $sTemplate );
        }

        if( strpos( $sTemplate, "[[Comments]]" ) !== false ) {
            $s = "<TD valign='top'>Comments: </TD><TD valign='top'><TEXTAREA name='doc_desc' cols=40 rows=2>".SEEDStd_HSC($oDoc->GetValue('desc',''))."</TEXTAREA></TD>";
            $sTemplate = str_replace( "[[Comments]]", $s, $sTemplate );
        }

        if( strpos( $sTemplate, "[[Perms]]" ) !== false ) {
        	// use the oDocMgr permclasses since they apply to all inserts and updates
            $s = "<TD valign='top'>Permission: </TD><TD valign='top'><SELECT name='doc_permclass'>";
            $pcSel = $this->oDocRepMgr->GetDocValue('permclass');
            $raPCNames = $this->oDocRepMgr->EnumPermClassNames( "W" );
            foreach( $raPCNames as $pc => $pcname ) {
                $s .= "<OPTION value='$pc'".($pcSel==$pc ? " SELECTED" : "").">$pcname</OPTION>";
            }
            $s .= "</SELECT></TD>";

            $sTemplate = str_replace( "[[Perms]]", $s, $sTemplate );
        }
// make this smarter so any number of nbsp can be requested
        if( strpos( $sTemplate, "[[nbsp]]" ) !== false ) {
            $s = "<TD valign='top'>&nbsp;</TD>";
            $sTemplate = str_replace( "[[nbsp]]", $s, $sTemplate );
        }
        if( strpos( $sTemplate, "[[nbsp3]]" ) !== false ) {
            $s = "<TD valign='top'>&nbsp;&nbsp;&nbsp;</TD>";
            $sTemplate = str_replace( "[[nbsp3]]", $s, $sTemplate );
        }

        if( strpos( $sTemplate, "[[Position]]" ) !== false ) {
        	// use the oDocMgr doc type, because $oDoc might be a dummy blank doc for inserts (and probably is if this is being drawn)
            if( $this->oDocRepMgr->GetDocType() == 'FOLDER' ) {
                $s = "<INPUT type='radio' name='dr_pos' value='under' CHECKED> At the first position inside the selected folder<BR>"
                    ."<INPUT type='radio' name='dr_pos' value='after'> After the selected folder";
            } else {
                $s = "<INPUT type='radio' name='dr_pos' value='after' CHECKED> After the selected document<BR>"
                    ."<INPUT type='radio' name='dr_pos' value='under'> At the first indented position under the selected document";
            }
            $sTemplate = str_replace( "[[Position]]", $s, $sTemplate );
        }

        return( $sTemplate );
    }

//TODO: surely we can prevent over-writing spec if the parm is not set. Just overwrite the fields that are specified in parms.
    function drawControlsMetaItems( $oDoc, $sTemplate )
    {
        $s = $this->drawControlsTemplate( $oDoc, $sTemplate )
             // propagate spec back into the record on edit, because we would otherwise blank it out
            .SEEDForm_Hidden( 'doc_spec',    $oDoc->GetValue('spec','') )
            .SEEDForm_Hidden( 'doc_verspec', $oDoc->GetValue('verspec','') );
        return( $s );
    }

    function DrawDocRepVersions( $kSelect, $iVersionSelect, $parms = array() )
    /*************************************************************************
     */
    {
        $bAdmin = @$parms['bAdmin'];    // show DXD flags and allow Delete Versions

        if( $bAdmin ) {
            echo "<FORM method='post' action='${_SERVER['PHP_SELF']}'>"
                ."<INPUT type='hidden' name='dra01_mode' value='show_versions'>"
                ."<INPUT type='hidden' name='dra01_action' value='dra01_ver_update'>";
        }
        echo "<TABLE border='0'>";

        // Diff is very klugey.
        // It refers to docdiff.php which is external to DocRepApp.
        // Also, since we process these in DESC ver order, the older ver key is not known when each row is formatted.
        // So we delay formatting of the end of each row, until the next (older) row is read.

        $newerDiffKey = 0;



        // use DocRepDB to ensure permclass access, etc
        $dbc = $this->oDocRepMgr->oDocRepDB->kfdb->KFDB_CursorOpen( "SELECT * from docrep_docdata where fk_docrep_docs='$kSelect' ORDER BY ver DESC" );
        while( $ra = $this->oDocRepMgr->oDocRepDB->kfdb->KFDB_CursorFetch( $dbc ) ) {

            if( $newerDiffKey ) {
                // finish formatting the last row, show diff link
                echo "<TD>";
                echo "<A HREF='docdiff.php?kDataNew=$newerDiffKey&kDataOld=${ra['_key']}' target='_blank'>Difference</A>";
                echo "</TD>";
                echo "</TR>";
            }
            $newerDiffKey = $ra['_key'];

            $s = "text-decoration:none;";
            if( $ra['ver'] == $iVersionSelect )  $s .= "font-weight:bold;";
            echo "<TR><TD valign='top'>"
                 ."<A HREF='${_SERVER['PHP_SELF']}?k=$kSelect&v=${ra['ver']}&dra01_mode=show_versions' style='$s'>"
                 ."${ra['ver']} : ${ra['meta_title']}</A></TD>";
            $sDXD = "";
            $dbcDXD = $this->oDocRepMgr->oDocRepDB->kfdb->KFDB_CursorOpen( "SELECT * FROM docrep_docxdata WHERE fk_docrep_docdata='${ra['_key']}'" );
            while( $raDXD = $this->oDocRepMgr->oDocRepDB->kfdb->KFDB_CursorFetch( $dbcDXD ) ) {
                if( !empty($sDXD) )  $sDXD .= " ";
                $sDXD .= $raDXD['flag'];
            }
            //$this->oDocRepDB->kfdb->KFDB_CursorClose( $dbcDXD );

            if( $bAdmin ) {
                echo "<TD>".SEEDStd_StrNBSP('',5)."<INPUT type='text' size='8' name='verdxd${ra['_key']}' value='$sDXD'></TD>"
                    ."<TD>".SEEDStd_StrNBSP('',10)."<INPUT type='checkbox' name='verdel${ra['_key']}' value='1'></TD>";
            } else {
                echo "<TD>".SEEDStd_StrNBSP('',5)."$sDXD</TD>";
            }
        }
        // finish formatting the last row. There is no diff link because there is no older version.
        echo "<TD>&nbsp;</TD></TR>";

        if( $bAdmin ) {
            echo "<TR><TD>&nbsp;</TD>"
                ."<TD>".SEEDStd_StrNBSP('',5)."<INPUT type='submit' name='dra01_ver_update_action' value='Change'></TD>"
                ."<TD>".SEEDStd_StrNBSP('',5)."<INPUT type='submit' name='dra01_ver_update_action' value='Delete'></TD></TR>";
        }

        echo "</TABLE>";
        if( $bAdmin )  echo "</FORM>";
    }

    function _getIconName( $v )
    /**************************
     */
    {
        $icon = 'none.gif';
        if( $v['type'] == 'TEXT' ) {
            $icon = 'txt.gif';
        } else {
            $ext = strtolower($v['file_ext']);
            switch( $ext ) {
                case 'bmp':
                case 'doc':
                case 'gif':
                case 'htm':
                case 'jpg':
                case 'mdb':
                case 'mov':
                case 'mp3':
                case 'pdf':
                case 'ppt':
                case 'tif':
                case 'xls':
                case 'zip':
                    $icon = $ext.'.gif';    break;
                    break;
                case 'jpeg':    $icon = 'jpg.gif';  break;
                case 'html':    $icon = 'htm.gif';  break;
                case 'tiff':    $icon = 'tif.gif';  break;
                default:        $icon = 'none.gif'; break;
            }
        }
        return( $icon );
    }
    }


?>
