<?php

// Edit Document screen should have a checkbox for "Save as a new version", which is disabled (and checked)
// on the first save, then enabled and unchecked.  This is better than a separate button because the HTML
// editor doesn't give you two buttons.
// Also, a Publish checkbox to publish on save (is there some logic to detect when Publish is required? like WEBROOT).

// DocRepDB knows how to store a DocRepository but it doesn't know what the codes mean
// DocRepMgr (drm) knows your uid and a client-supplied method to translate it into an array of permclasses (R,W,P,A). It knows how to use that to mediate fetches
// and updates using DocRepDB, through a set of parms that define all the document properties that a UI would use, and an enumerated set of update actions.
// DocRepMgrUI (dru) provides UI widgets that use DocRepMgr's parms and update actions.
// DocRepAppNN (dra) is a frame for the UI widgets, with its own modes that define different collections of UI pieces.  It could be a derivation of DocRepApp,
// which does the standard set up of the support classes.


// Non-TEXT documents shouldn't download when you click on the title.  That's different behaviour than TEXT, and you get used to
// just clicking on the title to see properties.  There should be a green Download link.
// Actually, no reason we can't show a lot of DOC types in a div or iframe below the tree.  Graphics, anyway.  Maybe pdfs in an iframe?


// DrawControls_InsertFolder_Rename in show_insert_folder mode should have js to switch the name prefix based on the radio buttons

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


/* DocRepApp02
 *
 * Copyright (c) 2006-2018 Seeds of Diversity Canada
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
include_once( "DocRepH2o.php" );

include_once( STDINC."SEEDForm.php" );
include_once( STDINC."SEEDEditor.php" );

define( "DOCREPAPP01_SEEDPERMS_APP", "DocRep" );    // the app name in SEEDPerms


function kluge_MyDoc()
{
    // address of the script that serves images on this server (should be elevated to a method, parm, or callback in seedcommon)
    return( SITEROOT."d/doc.php" );
}



// class ConsoleTabBook {
//     var $raParms;

//     function ConsoleTabBook( $raParms = array() )
//     {
//         $this->raParms = $raParms;  // copy the array because we'll modify it

//         if( !@$this->raParms['fnSessionVarSet'] )  $this->raParms['fnSessionVarSet'] = array($this,"dummy");
//         if( !@$this->raParms['fnSessionVarGet'] )  $this->raParms['fnSessionVarGet'] = array($this,"dummy");
//     }

//     function DrawTabBook( $id, $raTabBook, $raParms )
//     {
//         $s = "<STYLE>"
//             .".console_guideText_small  { font-size:8pt;font-family:verdana,geneva,arial,helvetica,sans-serif; }"
//             .".console_TabBookTab       { }"
//             .".console_TabBookTabSelected { font-weight:bold; }"
//             ."</STYLE>";

//         if( empty($raParms['tabDestUrl']) )  $raParms['tabDestUrl'] = $_SERVER['PHP_SELF'];

//         $currTab = @$_REQUEST["consoleTab${id}"];
//         if( empty($currTab) ) { $currTab = call_user_func($this->raParms['fnSessionVarGet'], "consoleTab${id}" ); }
//         if( empty($currTab) ) { reset($raTabBook); $currTab = key($raTabBook); }            // default tab is the first key of the array
//         call_user_func($this->raParms['fnSessionVarSet'], "consoleTab${id}", $currTab );    // set the tab name in a session var

//         $s .= "<TABLE border='1'><TR>";
//         foreach( $raTabBook as $k => $v ) {
//             $s .= "<TD width='50' class='".($currTab==$k ? "console_TabBookTabSelected" : "console_TabBookTab")."'><A HREF='${raParms['tabDestUrl']}?${raParms['tabDestUrlQuery']}&consoleTab${id}=$k'>$k</A></TD>";
//         }
//         $s .= "</TR></TABLE>";

//         if( ($fn = @$raTabBook[$currTab]) ) {
//             $s .= call_user_func($fn);
//         }
//         return( $s );
//     }

//     function dummy()
//     {
//         // give the SessionVarSet/Get variables a valid function to point to, to prevent errors if not set (though session vars won't work)
//     }
// }


class DocRepApp02 {
    var $oDocMgr;
    var $oDocUI;

    var $iDocSelVersion = 0;
    var $pAction = '';
    var $pMode = '';

    function __construct( KeyFrameDB $kfdb, $uid, $keyHashSeed )
    /**********************************************************
     */
    {
        /* Create the DocRepMgr and perform actions
         */
        $this->oDocMgr = new DocRepApp02_DocRepMgr( $kfdb, $uid );

        /* In the Documents tab, navigation is done by the k parm.
         * In the Files tab, navigation is done by the sfile parm.
         * The Files tab can't use doc keys all the time because there aren't any for folders and orphaned files,
         * but it could do that for most files.  Better not to be confusing though.
         * We remember k and sfile in SessionVar* so they don't have to be propagated by every form.
         */
        $kDoc = SEEDSafeGPC_GetInt( 'k' );
        if( !$kDoc || !$this->oDocMgr->SetDocKey( $kDoc ) ) {
            // Either k was not set or SetDocKey failed because the doc is not accessible by this user.
            // If SessionVar* overrides are implemented, get the last-used doc key
            if( ($kDoc = $this->SessionVarGet('doc_k')) ) {
                $kDoc = $this->oDocMgr->SetDocKey( $kDoc );
            }
        }
        $this->SessionVarSet( 'doc_k', $kDoc );

        $sfile = SEEDSafeGPC_GetStrPlain( 'sfile' );
        if( !$sfile || !$this->oDocMgr->SetSFile( $sfile ) ) {
            if( ($sfile = $this->SessionVarGet('doc_sfile')) ) {
                $sfile = $this->oDocMgr->SetSFile( $sfile );
            }
        }
        $this->SessionVarSet( 'doc_sfile', $sfile );

        $this->pMode = SEEDSafeGPC_Smart( 'dra_mode',
                array(
                "",
                "show_insert_folder",   // show the create folder form
                "show_insert_file",     // show the insert-upload form
                "edit_text_new",        // show the full text editor with blank input (k is the parent)
                "edit_text",            // show the full text editor for the selected doc
                "edit_text_quick",      // show the editor in the preview pane for the selected folder/doc
                "edit_file"             // show the update-upload form
                ));
        if( empty($this->pMode) )
            $this->pMode = SEEDSafeGPC_Smart( 'dra01_mode',
                array(
                "",
                "view_text",            // show the selected text doc in the text area
                "show_versions"         // show the versions of the selected doc
                ));

        $this->pAction = SEEDSafeGPC_Smart( 'drm_action',
                array(
                "",
                "insert_folder",        // dra_mode:show_insert_folder, create a new folder
                "insert_file",          // dra_mode:show_insert_file, upload a new file
                "insert_text",          // dra_mode:edit_text_new, new doc with text from editor control
                //*** though update_folder is fully implemented in DocRepMgr, rename does the same thing so the DRApp does not issue this action
                // "update_folder",        // after edit: update metadata for the selected folder (creates a new version)
                "update_file",          // upload a file as a new version of the selected doc
                "update_text",          // text editor saves a new version of the selected doc
                "rename",               // after prep_rename: change the maxVer name/title/metadata (not a new version)
                "update_vars",          // on Variables tab: update the metadata of the maxVer (not a new version)
                "move_up",              // move to position after parent
                "move_down",            // move to first child of prev sib
                "move_right",           // swap with next sib
                "move_left",            // swap with prev sib
                "trash",                // move to trash
                "approve",              // give PUB flag to maxVer - only accessible if permsclass allows "aPprove"
                "ver_dxd_flag_update",  // update the given dxd flags - flag="" means delete it
                "ver_delete",           // delete a version (delete a docrep_docdata record and all references to it)

                "insert_sfile",         // insert a new file or folder
                "update_sfile",         // rename (filesystem-move) and/or replace a file; if folder rename (move) the whole folder
                "trash_sfile",          // move a file or folder to trash
                "undelete_sfile",       // undelete a file or folder
                "purge_deleted_sfile",  // purge a file or folder (must be in the trash already)
                "approve_sfile_folder", // publish all sfiles under the current folder
        ));

/* Since the DRA implements the version management form with two buttons Change and Delete, it isn't easy to encode the version actions
 * as http parms. Do this with JS in the form some time and use the above drm_action code instead of this.
 */
if( @$_REQUEST['dra01_ver_update_action'] == 'Change' ) {
    $this->pAction = 'ver_dxd_flag_update';
}
if( @$_REQUEST['dra01_ver_update_action'] == 'Delete' ) {
    $this->pAction = 'ver_delete';
}


        if( in_array($this->pMode, array('edit_text','edit_text_quick','edit_file')) && !$this->oDocMgr->GetDocKey() )
        {
            $this->pMode = "";
        }

        if( $this->pMode == 'show_versions' && $this->oDocMgr->GetDocKey() ) {
            $this->iDocSelVersion = SEEDSafeGPC_GetInt( 'v' );
            if( !$this->iDocSelVersion ) {
                // default to the maxVer
                $this->iDocSelVersion = $this->oDocMgr->GetDocValue('maxVer');
            }
        }

        /* Create the DocRepMgrUI and prepare it to draw the UI.
         */
        // DocRepMgrUI wants to know about UI state, but it isn't supposed to know about sessions. Neither is this class.
        // So we're using oSVA as if it were a virtual datasource, and getting it via derived method. Its implementation is above the App level.
        $odsUIState = $this->Session_oSVA();
        $this->oDocUI = new DocRepApp02_DocRepMgrUI( $this->oDocMgr, $keyHashSeed, $odsUIState );
    }

    function SessionVarSet( $k, $v, $iDocMgr = 0 ) { /* derived class should override this to provide access to session var storage */ }
    function SessionVarGet( $k, $iDocMgr = 0 )     { /* derived class should override this to provide access to session var storage */ return(NULL); }
    function Session_oSVA( $iDocMgr = 0 )          { /* derived class should override this to provide access to session var accessor */ return(NULL); }

    /* Updates
     *
     * DocRepApp:
     *
     * insert_sfile
     *     doc_name_sfile : full name of the sfile to insert
     *       or
     *       doc_name_sfilefolder + doc_name_sfilebase : composite name of the sfile to insert
     *       or
     *       doc_name_sfilefolder (other two blank) : name of empty folder to create -- supported by DocRepMgr:insert_sfilefolder but deprecated in this app
     *     sFilename : file to copy to docrep/sfile/
     *
     * update_sfile
     *     on a folder
     *         doc_name_sfile : full name to rename (filesystem-move) - also rename descendants
     *         metadata       : perms, etc
     *
     *     on a file
     *         doc_name_sfile : full name to rename (filesystem-move)
     *         doc_filename   : optional file replacement
     *         metadata       : perms, etc
     *
     *     on a file with no obj
     *         doc_name_sfile : full name to rename (filesystem-move)
     *         doc_filename   : optional file replacement
     *
     * trash_sfile
     *     doc_name_sfile : full name of the sfile to delete
     *     or
     *     doc_name_sfilefolder + doc_name_sfilebase : composite name of the sfile to delete -- supported but deprecated
     *     or
     *     doc_name_sfilefolder (other two blank) : name of folder & contents to delete
     *
     *
     * DocRepMgr:
     *
     * insert_sfile
     *     dr_name : full name of the sfile to insert
     * insert_sfilefolder
     *     dr_name : full name of the folder to create
     *
     * update_sfile
     *     dr_name (optional) : if currently on a file, rename/move to this full name; if on folder, rename/move folder and all descendants
     *     dr_filename        : if currently on a file, replace with this file
     *
     * trash_sfile
     *     dr_name : full name of the sfile or folder to delete
     */

    function DoAction()
    /******************
     */
    {
        $raParms = array();
        switch( $this->pAction ) {
            case 'insert_text':
            case 'update_text':
                /* The Texttype select control issues a form submit onChange.
                 * If the Texttype has changed, don't interpret this as a Save. Just draw the editor again with current values.
                 */
                if( SEEDSafeGPC_GetInt( 'dru_texttype_changed' ) )  return( false );


                $raParms = $this->_getInsertParms();

                $raParms['sText'] = SEEDSafeGPC_GetStrPlain( 'doc_text' );

                // Not all the update parms are propagated by the Quick editor (e.g. no title, name, perms, etc).
                // This does not mean they should be blanked out!
                $bQuickEdit = ($this->pAction=='update_text' && $this->pMode=='edit_text_quick');
                if( $bQuickEdit ) {
//                    $raParms['dr_name'] = $this->oDocMgr->GetDocName();
//                    $raParms['dr_title'] = $this->oDocMgr->GetDocTitle('');
//                    $raParms['dr_verspec'] = $this->oDocMgr->GetDocValue('verspec');

                } else {
                    $raParms['dr_name'] = $this->oDocMgr->GetNewDocName( SEEDSafeGPC_GetStrPlain('doc_name'),
                                                                         ($this->pAction == 'insert_text' && intval(@$raParms['dr_posUnder'])) );

                    $eTextType = DocRepTextTypes::NormalizeTextType( SEEDSafeGPC_GetStrPlain('dru_texttype') );

                    $verspec = SEEDStd_TagStrAdd( @$raParms['dr_verspec'], $eTextType, DocRepTextTypes::$raTextTypes );
//                    if( @$raParms['dr_verspec'] && $verspec != $raParms['dr_verspec'] ) {
//                        return( false );
//                    }
                    $raParms['dr_verspec'] = $verspec;
                }

                /* The user clicked a Save button:  Save new version, Save, or a WYSIWYG editor's Save
                 */
                switch( @$_REQUEST['dru_textsubmit'] ) {
                    case 'Save':
                        $raParms['dr_bReplaceCurrVersion'] = true;
                        break;
                    case 'Save new version':
                        break;
                    default:
                        // Probably the user clicked a WYSIWYG editor's Save button.
                        // If a new version has not been saved yet in this editing session, do that. If it has, overwrite the new version.
                        // The user should be able to click Save New Version if they really want to preserve their previous save.
                        if( @$_REQUEST['dru_SaveNewVersionDone'] ) {
                            $raParms['dr_bReplaceCurrVersion'] = true;
                        }
                        break;
                }
                // enable the Save (not new version) button on the next draw of the editor, because at least one new version will have been saved
                $this->oDocUI->bSaveNewVersionDone = true;
                break;


            case 'insert_folder':
                $raParms = $this->_getInsertParms();
                $raParms['dr_name'] = $this->oDocMgr->GetNewDocName( SEEDSafeGPC_GetStrPlain('doc_name'),
                                                                     ($this->pAction == 'insert_folder' && intval(@$raParms['dr_posUnder'])) );
                break;

            case 'insert_file':
            case 'update_file':
            case 'insert_sfile':
            case 'update_sfile':
                // file is optional for update_sfile (it can just be rename) but required for others
                $bFExists = isset( $_FILES['docmgrFile'] );
                $bFUpload = $bFExists && is_uploaded_file( $_FILES['docmgrFile']['tmp_name'] );
                if( ($bFExists && !$bFUpload) || ($this->pAction != 'update_sfile' && !$bFUpload ) ) {
                    $this->oDocMgr->SetErrorMsg( $bFExists ? "No file to upload"
                                                           : "The file did not upload: there is a limit of 10 megabytes for files" );
                    return( false );
                }
                $sUploadName = @$_FILES['docmgrFile']['name'];
                $sUploadFilename = @$_FILES['docmgrFile']['tmp_name'];
                $raParms = $this->_getInsertParms();

                switch( $this->pAction ) {
                    case 'insert_sfile':
                        if( ($sfile = SEEDSafeGPC_GetStrPlain('doc_name_sfile')) ) {
                            if( ($sfileFolder = SEEDSafeGPC_GetStrPlain('doc_name_sfilefolder')) && substr($sfileFolder,-1) != '/' ) {
                                $sfileFolder .= "/";
                            }
                            $raParms['dr_name'] = $sfileFolder . $sfile;
                        } else {
                            $raParms['dr_name'] = $sUploadName;
                        }
                        if(empty($raParms['dr_name']) )  die( "SFILE doc doesn't have a name" );
                        break;
                    case 'update_sfile':
                        // DocRepMgr::Update() handles this as a rename if dr_name not empty, replace if dr_filename not empty, or both in sequence
                        $raParms['dr_name'] = SEEDSafeGPC_GetStrPlain('doc_name_sfile');
                        //if(empty($raParms['dr_name']) )  die( "SFILE doc doesn't have a name" );  -- uses current name if empty
                        $raParms['dr_filename'] = $sUploadFilename;
                        break;
                    default:
                        if( SEEDSafeGPC_GetInt('doc_name_bNameFromFilename') ) {
                            $sName = $sUploadName;
                        } else {
                            $sName = SEEDSafeGPC_GetStrPlain('doc_name');
                        }
                        $raParms['dr_name'] = $this->oDocMgr->GetNewDocName( $sName, ($this->pAction == 'insert_file' && intval(@$raParms['dr_posUnder'])) );
                        break;
                }
                // DocRepDB uses the fileext of the doc name to get mimetype, unless dr_fileext is specified.
                // Since users will not do the right thing in keeping the correct fileext on the name, get fileext from the client-side file name.
                $raParms['dr_fileext'] = substr( strrchr( $sUploadName, '.' ), 1 );
                $raParms['sFilename'] = $sUploadFilename;
                break;

            case 'trash_sfile':
                // Currently only supporting deletion of individual files, implemented in DocRepMgr::Update()
                break;
            case 'undelete_sfile':
                break;
            case 'purge_deleted_sfile':
                break;

            case 'rename':
                $raParms = $this->_getUpdateParms( "title name permclass desc spec verspec" );
                $raParms['bRenameDescendants'] = true;
                break;

            case 'update_vars':
                $raParms = $this->_getUpdateParms( "vars spec" );
                break;

            case 'ver_dxd_flag_update':
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
            case 'trash':
            case 'approve':
                break;

        }

        $kDocUpdated = $this->oDocMgr->Update( $this->pAction, $raParms );

        if( $kDocUpdated && in_array( $this->pAction, array('insert_folder', 'insert_file', 'insert_text', 'insert_sfile') ) ) {
            // If a new folder or doc was inserted, make it the current doc
            $this->oDocMgr->SetDocKey( $kDocUpdated );
        }

        if( in_array( $this->pAction, array('insert_sfile','update_sfile') ) ) {
            // DocRepMgr did SetSFile with the new name, but didn't save it in the session so the position in the tree is lost
            $this->SessionVarSet( 'doc_sfile', $this->oDocMgr->GetSFile() );
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
                case 'name_sfile':$this->_getUpdateparmStr( 'dr_name_sfile', 'doc_name_sfile', $raParms );                      break;

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
        $s = "<STYLE>"
            .".DocRepTree_level           { font-family:verdana,arial,helvetica,sans serif; font-size:10pt; }"
            .".DocRepApp_controlArea_Text,"
            .".DocRepApp_controlArea_Text td"
                                        ."{ font-family:verdana,arial,helvetica,sans serif; font-size:10pt; }"
            .".DocRepApp_treeControls     { font-family:verdana,arial,helvetica,sans serif; font-size:8pt; color:green; }"
            ."td, th                      { font-size:10pt; }"  // this applies to user tables in the TextArea, because not inherited from the style attr in the TextArea div. This means all other tables in the app might need explicit size
            ."</STYLE>";

        $s .= $this->oDocUI->Style();

        return( $s );
    }

    function DrawTreeArea( $keyTree )
    /********************************
     */
    {
        if( $this->pMode == "show_versions" ) {
        } else {
            /* Show the current DocRepTree
             */
            // no box style is defined for the whole TreeArea, but it would be here
            //echo $this->oDocUI->DrawDocRepTree( $keyTree );
            echo $this->oDocUI->DrawDocRepTree2( $keyTree );
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
                    $s .= "<P>".$this->drawMainLink( $this->oDocMgr->GetDocKey(), array('dra_mode'=>'show_insert_folder'), "Create a new folder" )."</P>";
                    $s .= "<P>".$this->drawMainLink( $this->oDocMgr->GetDocKey(), array('dra_mode'=>'edit_text_new'), "Create a new document with an on-screen editor" )."</P>";
                    $s .= "<P>".$this->drawMainLink( $this->oDocMgr->GetDocKey(), array('dra_mode'=>'show_insert_file'), "Upload a new file (document, image, etc)" )."</P>";
                }
                $s .= $this->dcaShowInfo();
            }
        } else if( $this->oDocMgr->GetDocKey() && $this->oDocMgr->PermWrite() ) {
            // show 'Create' forms

            switch( $this->pMode ) {
                case 'show_insert_folder':
                    $s .= "<H4>Create Folder</H4>".$this->oDocUI->DrawControls_InsertFolder_Rename( 'insert_folder' );
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
            $s .= $this->dcaShowInfo();
        }
        return( $s );
    }

    function dcaMove()
    {
        $s = "";
        if( $this->oDocMgr->GetDocKey() ) {
            if( $this->oDocMgr->PermWrite() ) {
                $s .= "<P>".$this->drawMainLink( $this->oDocMgr->GetDocKey(), array('drm_action'=>'move_left'), "Move up" )."</P>"
                     ."<P>".$this->drawMainLink( $this->oDocMgr->GetDocKey(), array('drm_action'=>'move_right'), "Move down" )."</P>"
                     ."<P>".$this->drawMainLink( $this->oDocMgr->GetDocKey(), array('drm_action'=>'move_up'), "Move to parent level" )."</P>"
                     ."<P>".$this->drawMainLink( $this->oDocMgr->GetDocKey(), array('drm_action'=>'move_down'), "Move into sub-level of previous" )."</P>"
                     ."<BR/><HR/><BR/>"
                     ."<P>".$this->drawMainLink( $this->oDocMgr->GetDocKey(), array('drm_action'=>'trash'), "Move to Trash" )."</P>";
            }
            $s .= $this->dcaShowInfo();
        }
        return( $s );
    }

    function dcaPreview()
    {
        $s = "";
        if( ($kDoc = $this->oDocMgr->GetDocKey()) ) {
            if( $this->oDocMgr->GetDocType() == 'TEXT' ) {
                $sPreviewMode = SEEDSafeGPC_GetStrPlain( 'dra02_preview_mode' );

                switch( $sPreviewMode ) {
                    case "Quick Edit":
                        if( $this->oDocMgr->PermWrite() ) {
                            $s .= $this->oDocUI->DrawTextEditor( false, true, array('hiddenParms'=>array('dra02_preview_mode'=>$sPreviewMode)) );
                        }
                        break;
                    case "Source":
                        $s .= $this->oDocUI->DrawPreview( NULL, NULL, true ); // show source
                        break;
                    default:
                        $s .= $this->oDocUI->DrawPreview( NULL, NULL );  // don't show source
                        break;
                }
            } else if( $this->oDocMgr->GetDocType() == 'IMAGE' ) {
                $s .= "";
            }
        }
        return( $s );
    }

    function dcaVar()
    {
        $s = "";
        if( $this->oDocMgr->GetDocKey() ) {
            $s .= "<P class='console_guideText_small'>These are special values associated with the selected document or folder. e.g. control codes or substitution text</P>"
                 ."<FORM method='POST' action='${_SERVER['PHP_SELF']}'>"
                 .SEEDForm_Hidden( 'drm_action', 'update_vars' )
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
        return("");

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

//     function DrawControlArea()
//     /*************************
//      */
//     {
//         $oTabBook = new ConsoleTabBook( array("fnSessionVarSet" => array($this, "SessionVarSet"),
//                                               "fnSessionVarGet" => array($this, "SessionVarGet")) );


//         $raTabBook = array( "Create" => array($this,"dcaCreate"),
//                             "Rename" => array($this,"dcaRename"),
//                             "Move"   => array($this,"dcaMove"),
//                             "Variables" => array($this,"dcaVar") );
//         if( $this->oDocMgr->PermAdmin() )  $raTabBook['Edit db fields'] = array($this,"dcaAdminDB");

//         $raParms['tabDestUrlQuery'] = "k=".$this->oDocMgr->GetDocKey();
//         echo $oTabBook->DrawTabBook( "A", $raTabBook, $raParms );
//     }


    /*****************
        SFILE UI
     */
    function dcaSfileView()
    {
        $s = "";

        if( ($sfile = $this->oDocMgr->GetSFile()) &&
            file_exists($this->oDocMgr->oDocRepDB->GetSFileDir()."/".$sfile) )
        {
            if( ($obj = $this->oDocMgr->GetSFileObj()) ) {
                if( substr( $obj->GetValue('mimetype',''), 0, 6 ) == "image/" ) {
                    $s .= "<img src='doc.php?n=".urlencode($sfile)."' style='max-width:500px'/>";
                } else {
                    $s .= "<a href='doc.php?n=".urlencode($sfile)."' target='_blank'>Download the file</a>";
                }
            } else {
                $s .= "<p>Orphaned file : use the Verify tab</p>";
            }
        }

        return( $s );
    }

    function dcaSfileNew()      { return( $this->oDocUI->DrawControls_InsertFile( 'insert_sfile' ) ); }
    function dcaSfileReplace()  { return( $this->oDocUI->DrawControls_ReplaceSfile() ); }
    function dcaSfileRename()   { return( $this->oDocUI->DrawControls_RenameSfile() ); }
    function dcaSfileDelete()   { return( $this->oDocUI->DrawControls_DeleteSfile() ); }

    function dcaSfileSync()
/*** Since this is called during the console draw, the side-effects of clean-up (like adding sfiles to the db) are not
 * shown in the tree view until the next draw.
 */
    {
        $s = "";

        $sfileFolder = $this->oDocMgr->GetSFileFolder();

// this is where you set the permclass for files that will be added
        $raParms = array( 'permclass' => 1 );
        $oIM = new myDRImg( $this->oDocMgr->oDocRepDB, $raParms );
        $oDRClean = new DocRepSFileClean( $oIM );
        $s .= "<DIV style='border:1px solid black;background-color:#ccc;float:right'>"
             .$oDRClean->Go( $sfileFolder )
             ."</DIV>";

        return( $s );
    }



    function DrawEditor()
    /********************
     */
    {
        $s = "";
        switch( $this->pMode ) {
            case 'view_text':
                if( $this->oDocMgr->GetDocType() == 'TEXT' ) {
                    $eTextType = DocRepTextTypes::GetFromTagStr( $this->oDocMgr->GetDocValue('verspec') );
                    $bShowSource = @$_REQUEST['dra01_showsource'];
                    $s .= "<DIV style='background-color:#eeeeee;padding:1em;'>"
                         ."<FORM method='POST' action='${_SERVER['PHP_SELF']}'>"
                         .SEEDForm_Hidden( 'dra01_mode', 'view_text' )
                         .SEEDForm_Hidden( 'k', $this->oDocMgr->GetDocKey() )
                         ."<P style='font-size:10pt'><SPAN style='font-size:14pt;font-weight:bold'>".$this->oDocMgr->GetDocTitle()."</SPAN>"
                        .SEEDStd_StrNBSP("",10)
                        ."This is a ".DocRepTextTypes::GetFullName($eTextType)." page"
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
                    $s .= $this->oDocUI->DrawPreview( $eTextType, NULL, $bShowSource );
                    $s .= "</DIV></DIV>";
                }
                break;

            case 'edit_text':
            case 'edit_text_new':
                $s .= "<DIV style='background-color:#eeeeee;padding:1em;'>"
                     .$this->oDocUI->DrawTextEditor( $this->pMode == 'edit_text_new' )
                     ."</DIV>";
                break;

// I think we never use this now
/*
            case 'show_versions':
                if( $this->iDocSelVersion && $this->oDocMgr->GetDocType()=='TEXT' ) {
                    // do this with an oDocMgr/oDoc method like GetDocVerText($ver) or use a parm other than flag
                    $ra = $this->oDocMgr->oDocRepDB->kfdb->KFDB_QueryRA("SELECT * FROM docrep_docdata WHERE fk_docrep_docs='".$this->oDocMgr->GetDocKey()."' AND ver={$this->iDocSelVersion}" );

                    $s .= "<DIV style='background-color:#eeeeee;padding:1em;'>"
                         ."<H3>".$ra['meta_title']."</H3>"
                         ."<DIV style='background-color:white;border:solid thin black;padding:1em;font-family:arial,helvetica,sans serif;font-size:9pt;'>";
                    $eTextType = DocRepTextTypes::GetFromTagStr( $this->oDocRepMgr->GetDocValue('verspec') );
                    if( in_array( $eTextType, array( 'TEXTTYPE_HTML', 'TEXTTYPE_SOD' ) ) ) {
                        $s .= SEEDStd_HSC($ra['data_text']);
                    } else {
                        $s .= "<PRE>".SEEDStd_HSC($ra['data_text'])."</PRE>";
                    }
                    $s .= "</DIV></DIV>";
                }
                break;
*/
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
    //function IsPermClassModeAllowed( $kDoc, $mode )  { return( false ); }
}


class DocRepApp02_DocRepMgr extends DocRepMgr
    {
    var $raPermsR = array();
    var $raPermsW = array();
    var $raPermsP = array();
    var $raPermsA = array();
    var $raPermsAll = array();
    var $oPerms;

    function __construct( KeyFrameDB $kfdb, $uid )
    {
// SEEDPerms and SEEDSession should not be known here. The raPermClasses should be passed into the contructor as parms.
// no they shouldn't, there should be a factory_DocRepDB method
// no not good enough, this needs to know about P and A perms, and an enumeration of permclass names for DocUI (unless DocUI has its own SEEDPerms for that)
// and now this is duplicated in doc/ebulletin (and it should be done whereever you want to make a DocRepDB)

        $this->oPerms = New_DocRepSEEDPermsFromUID( New_SiteAppDB(), $uid );
        $this->raPermsR   = $this->oPerms->GetClassesAllowed( "R", false );
        $this->raPermsW   = $this->oPerms->GetClassesAllowed( "W", false );
        $this->raPermsP   = $this->oPerms->GetClassesAllowed( "P", false );
        $this->raPermsA   = $this->oPerms->GetClassesAllowed( "A", false );
        $this->raPermsAll = $this->oPerms->GetClassesAllowed( "RWPA", true );

        parent::__construct( $kfdb, $uid );
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
            default:   die( "DocRepApp02_DocRepMgr::GetPermsClasses not implemented for $mode" );
        }
    }

    function EnumPermClassNames( $mode )
    /***********************************
     */
    {
        return( $this->oPerms->EnumClassNames( "W" ) );
    }
}


class DocRepApp02_DocRepMgrUI extends DocRepMgrUI {
    var $keyHashSeed = '';              // for hashing keys to make links hard to guess
    var $raWebroot = array();           // kDoc of all documents in the DocRep that have spec containing '% WEBROOT %'

    function __construct( &$oDocRepMgr, $keyHashSeed, &$odsUIState )
    {
        $this->keyHashSeed = $keyHashSeed;

        /* Get the Webroots so we can colour-code their contents
         */
        $dbc = $oDocRepMgr->oDocRepDB->kfdb->CursorOpen( "SELECT _key FROM docrep_docs WHERE spec LIKE '% WEBROOT %'" );
        while( $ra = $oDocRepMgr->oDocRepDB->kfdb->CursorFetch($dbc) ) {
            $this->raWebroot[] = $ra[0];
        }
        $oDocRepMgr->oDocRepDB->kfdb->CursorClose($dbc);

        parent::__construct( $oDocRepMgr, $odsUIState );
    }

    /* Override the default methods to draw the DocRepTree
     */
    function DrawDocRepTree_titleStart( $k, $v, $sExpand = '' )
    /**********************************************************
        sExpand: "expand", "collapse", ""
     */
    {
        $s = "";

        switch( $sExpand ) {
            case 'expand':
                $s .= "<A HREF='${_SERVER['PHP_SELF']}?kexpand=$k'><IMG src='".DOCREP_ICON_DIR."tree_closed.png' border='0'/></A> ";
                break;
            case 'collapse':
                $s .= "<A HREF='${_SERVER['PHP_SELF']}?kcollapse=$k'><IMG src='".DOCREP_ICON_DIR."tree_opened.png' border='0'/></A> ";
                break;
            default:
                $s .= "<IMG src='".DOCREP_ICON_DIR."tree_blank.png' border='0'/> ";
        }

        if( @$v['treetype'] == 'SFILE' ) {
            // Have to propagate location by sfile name because folders are not stored as DocRep records
            // (you have to be able to click on a folder in the tree)
            $s .= "<a href='${_SERVER['PHP_SELF']}?sfile=$k'>";
        } else {
            $s .= "<A HREF='${_SERVER['PHP_SELF']}?k=$k'>";
        }
        switch( $v['type'] ) {
            case 'FOLDER':    $sIcon = DOCREP_ICON_DIR."folder.gif";               break;
            case 'MISSING!':  $sIcon = DOCREP_ICON_DIR."X.gif";                    break;
            default:          $sIcon = DOCREP_ICON_DIR.$this->_getIconName($v);    break;
        }
        $s .= "<IMG src='$sIcon' border='0'>&nbsp;<NOBR>";

        return( $s );
    }

    function DrawDocRepTree_title( $k, $v )
    {
        if( @$v['treetype'] == 'SFILE' ) {
            // since sfile shows folders, just show the file portion
            $s = $v['name'];
            if( ($i = strrpos( $s, '/' )) !== false ) {
                $s = substr( $s, $i+1 );
            }
            // colour-code if it's an indexed doc
            if( ($oSfile = $this->oDocRepMgr->oDocRepDB->GetDocObjectFromName( $v['name'] ) ) ) {
                $ePub = $this->getPubStateSfile( $oSfile );

                $sTextDec = ($oSfile->GetStatus()=='DELETED' ? "text-decoration:line-through;" : "");
                $s = "<SPAN style='color:".($ePub==0 ? "green" : ($ePub==1 ? "red" : "orange")).";$sTextDec'>".$s."</SPAN>";
            }
        } else {
            $s = (!empty($v['title']) ? $v['title'] : (!empty($v['name']) ? $v['name'] : "Untitled") );

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
                $s = "<SPAN style='color:".($ePub==0 ? "green" : ($ePub==1 ? "red" : "orange"))."'>".$s."</SPAN>";
            }
        }

        return( $s );
    }
    function DrawDocRepTree_titleStart2( $k, $oDoc, $sExpand = '' )
    /**************************************************************
        sExpand: "expand", "collapse", ""
     */
    {
        $s = "";

        switch( $sExpand ) {
            case 'expand':
                $s .= "<A HREF='${_SERVER['PHP_SELF']}?kexpand=$k'><IMG src='".DOCREP_ICON_DIR."tree_closed.png' border='0'/></A> ";
                break;
            case 'collapse':
                $s .= "<A HREF='${_SERVER['PHP_SELF']}?kcollapse=$k'><IMG src='".DOCREP_ICON_DIR."tree_opened.png' border='0'/></A> ";
                break;
            default:
                $s .= "<IMG src='".DOCREP_ICON_DIR."tree_blank.png' border='0'/> ";
        }

        if( @$v['treetype'] == 'SFILE' ) {
            // Have to propagate location by sfile name because folders are not stored as DocRep records
            // (you have to be able to click on a folder in the tree)
            $s .= "<a href='${_SERVER['PHP_SELF']}?sfile=$k'>";
        } else {
            $s .= "<A HREF='${_SERVER['PHP_SELF']}?k=$k'>";
        }
        switch( $oDoc->GetType() ) {
            case 'FOLDER':    $sIcon = DOCREP_ICON_DIR."folder.gif";               break;
            case 'MISSING!':  $sIcon = DOCREP_ICON_DIR."X.gif";                    break;
            default:          $sIcon = DOCREP_ICON_DIR.$this->_getIconName2($oDoc);    break;
        }
        $s .= "<IMG src='$sIcon' border='0'>&nbsp;<NOBR>";

        return( $s );
    }

    function DrawDocRepTree_title2( $k, $oDoc )
    {
        if( @$v['treetype'] == 'SFILE' ) {
            // since sfile shows folders, just show the file portion
            $s = $oDoc->GetName();
            if( ($i = strrpos( $s, '/' )) !== false ) {
                $s = substr( $s, $i+1 );
            }
            // colour-code if it's an indexed doc
            if( ($oSfile = $this->oDocRepMgr->oDocRepDB->GetDocObjectFromName( $v['name'] ) ) ) {
                $ePub = $this->getPubStateSfile( $oSfile );
                $s = "<SPAN style='color:".($ePub==0 ? "green" : ($ePub==1 ? "red" : "orange"))."'>".$s."</SPAN>";
            }
        } else {
            $s = $oDoc->GetTitle('');
            if( !$s ) $s = $oDoc->GetName();
            if( !$s ) $s = "Untitled";

            /* If the doc is in a Webroot tree (an ancestor has a spec containing the flag " WEBROOT ") colour-code
             * its PUB status.
             *
             * Since GetDocAncestors includes its starting kDoc in the ancestor list, the lookup can be short-cut by
             * starting at each doc's parent. This means that a flat group of docs will do the same query, so the db
             * will probably cache the result effectively.
             */
            $raAnc = $this->oDocRepMgr->oDocRepDB->GetDocAncestors( $oDoc->GetParent() );
            $raInt = array_intersect( $this->raWebroot, $raAnc );
            if( count($raInt) ) {
                // this doc is in a Webroot tree, so colour-code it
                $ePub = $this->getPubState($k);
                $s = "<SPAN style='color:".($ePub==0 ? "green" : ($ePub==1 ? "red" : "orange"))."'>".$s."</SPAN>";
            }
        }

        return( $s );
    }

    function DrawDocRepTree_titleEnd2( $k, $oDoc )
    {
        $s = "</NOBR></A>";

// All of this can go away if controls are in the control box
        if( @$v['treetype'] != 'SFILE' && $k == $this->oDocRepMgr->GetDocKey() ) {
            $bPermW = $bPermP = false;
            if( ($p = $this->oDocRepMgr->GetDocValue('permclass')) ) {
                $bPermW = in_array( $p, $this->oDocRepMgr->GetPermClasses("W") );
                $bPermP = in_array( $p, $this->oDocRepMgr->GetPermClasses("P") );
            }

            $s .= SEEDStd_StrNBSP('',10)."<SPAN class='DocRepApp_treeControls'>&lt;==".SEEDStd_StrNBSP('  ');

            if( $bPermW ) {
                switch( $this->oDocRepMgr->GetDocType() ) {
                    case 'FOLDER':  break; // $s .= "";        break;
                    case 'TEXT':    break; // $sEdit = "Edit";    break;
                    default:        $s .= "<A HREF='${_SERVER['PHP_SELF']}?k=$k&dra_mode=edit_file' class='DocRepApp_treeControls'>Replace</A>".SEEDStd_StrNBSP('  |  ');
                }
            }

            //$s .= sprintf( $sLinkMode, 'show_versions', 'Versions' );

            $type = $oDoc->GetType();
            if( $type != 'TEXT' && $type != 'FOLDER' ) {
                $s .= SEEDStd_StrNBSP('  |  ')
                     ."<A HREF='doc.php?k=".DocRep_Key2Hash($k,$this->keyHashSeed)."' target='_blank' class='DocRepApp_treeControls'>Download</A>";
            }

            // Allow aPprovers to approve publication.
            if( $bPermP ) {
           //     $s .= SEEDStd_StrNBSP('  |  ')."<A HREF='${_SERVER['PHP_SELF']}?k=$k&drm_action=approve' class='DocRepApp_treeControls'>Pub</A>";
            }

            $s .= "</SPAN>";
        }
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

    private function getPubStateSfile( $oSfile )
    {
// use DocRepDoc in code above and factor this into it
        return( $oSfile->GetFlagOfCurrVer( "PUB" ) ? 0 : 1 );
    }

    function DrawDocRepTree_titleEnd( $k, $v )
    {
        $s = "</NOBR></A>";

// All of this can go away if controls are in the control box
        if( @$v['treetype'] != 'SFILE' && $k == $this->oDocRepMgr->GetDocKey() ) {
            $bPermW = $bPermP = false;
            if( ($p = $this->oDocRepMgr->GetDocValue('permclass')) ) {
                $bPermW = in_array( $p, $this->oDocRepMgr->GetPermClasses("W") );
                $bPermP = in_array( $p, $this->oDocRepMgr->GetPermClasses("P") );
            }

            $s .= SEEDStd_StrNBSP('',10)."<SPAN class='DocRepApp_treeControls'>&lt;==".SEEDStd_StrNBSP('  ');

            if( $bPermW ) {
                switch( $this->oDocRepMgr->GetDocType() ) {
                    case 'FOLDER':  break; // $s .= "";        break;
                    case 'TEXT':    break; // $sEdit = "Edit";    break;
                    default:        $s .= "<A HREF='${_SERVER['PHP_SELF']}?k=$k&dra_mode=edit_file' class='DocRepApp_treeControls'>Replace</A>".SEEDStd_StrNBSP('  |  ');
                }
            }

            //$s .= sprintf( $sLinkMode, 'show_versions', 'Versions' );

            if( $v['type'] != 'TEXT' && $v['type'] != 'FOLDER' ) {
                $s .= SEEDStd_StrNBSP('  |  ')
                     ."<A HREF='doc.php?k=".DocRep_Key2Hash($k,$this->keyHashSeed)."' target='_blank' class='DocRepApp_treeControls'>Download</A>";
            }

            // Allow aPprovers to approve publication.
            if( $bPermP ) {
           //     $s .= SEEDStd_StrNBSP('  |  ')."<A HREF='${_SERVER['PHP_SELF']}?k=$k&drm_action=approve' class='DocRepApp_treeControls'>Pub</A>";
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
    private $odsUIState;  // a virtual datasource containing ui state information (typically implemented as session vars)
    public  $bSaveNewVersionDone = false;   // Activates the "Save" (overwrite) button in the text editor.
                                            // drApp should set it when a doc is saved, and propagate through the edit session.

    function __construct( $oDocRepMgr, $odsUIState )
    /*************************************************
        This should only be instantiated when the oDocRepMgr has been updated to reflect current actions
     */
    {
        $this->oDocRepMgr = $oDocRepMgr;
        $this->odsUIState = $odsUIState;
    }

    function Style()
    /***************
     */
    {
        return( "<STYLE>"
               .".DocRepTree_level   { margin-left:2em; margin-bottom:5px; }"
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

        $kDoc = $this->oDocRepMgr->GetDocKey();
        $raAncestors = $this->oDocRepMgr->GetDocAncestors();
        $raTreeExpanded = $this->getRATreeExpanded();

// echo "kSelect = ".$this->oDocRepMgr->GetDocKey(); var_dump($this->oDocRepMgr->GetDocAncestors());
        $raTree = $this->oDocRepMgr->oDocRepDB->ListChildTree( $kTree, "", 2 );  // going two levels deep to see if current level docs are expandable
// if($kTree==1) var_dump($raTree,$this->oDocRepMgr->GetDocAncestors());
        $s .= "<DIV class='DocRepTree_level'>"           // defines the basic attributes of structure
             ."<DIV class='DocRepTree_level$iLevel'>";   // defines variations per-level, if defined
        foreach( $raTree as $k => $ra ) {
            if( @$raTreeExpanded[$k] ) {
                $sExpand = 'collapse';
            } else if( count($ra['children']) ) {
                $sExpand = 'expand';
            } else {
                $sExpand = '';
            }
            $s .= "<DIV class='DocRepTree_title'>"
                 .$this->DrawDocRepTree_titleStart( $k, $ra['doc'], $sExpand )
                 .( $k == $kDoc ? "<SPAN class='DocRepTree_titleSelected'>" : "" )
                 .$this->DrawDocRepTree_title( $k, $ra['doc'] )
                 .( $k == $kDoc ? "</SPAN>" : "" )  // titleSelected
                 .$this->DrawDocRepTree_titleEnd( $k, $ra['doc'] );
            if( @$raTreeExpanded[$k] || in_array( $k, $raAncestors ) ) {
                $s .= $this->DrawDocRepTree( $k, $kDoc, $raAncestors, $iLevel + 1 );
            }
            $s .= "</DIV>";  // title
        }
        $s .= "</DIV>"   // level$level
             ."</DIV>";  // level

        return( $s );
    }


    function DrawDocRepTree2( $kTree, $iLevel = 1 )
    /*********************************************
        Draw the tree rooted at $kTree.
        Don't draw $kTree. This allows the drawn part to be a forest (children of $kTree),
            or a tree with a single root (single child of $kTree).

        $iLevel is a recursion marker for internal use (don't use it).
     */
    {
        $s = "";

        $kDoc = $this->oDocRepMgr->GetDocKey();
        $raAncestors = $this->oDocRepMgr->GetDocAncestors();
        $raTreeExpanded = $this->getRATreeExpanded();

// depth== 2: get the immediate children but also count the grandchildren so count($ra['children']) is set.
// other than that count we only need depth==1; there's probably a more efficient way to get count($ra['children'])
        $raTree = $this->oDocRepMgr->oDocRepDB->GetSubTree( $kTree, 2 );
        $s .= "<DIV class='DocRepTree_level'>"           // defines the basic attributes of structure
             ."<DIV class='DocRepTree_level$iLevel'>";   // defines variations per-level, if defined
        foreach( $raTree as $k => $ra ) {
            if( !($oDoc = $this->oDocRepMgr->oDocRepDB->GetDocRepDoc( $k )) )  continue;

            if( @$raTreeExpanded[$k] ) {
                $sExpand = 'collapse';
            } else if( count($ra['children']) ) {
                $sExpand = 'expand';
            } else {
                $sExpand = '';
            }
            $s .= "<DIV class='DocRepTree_title'>"
                 .$this->DrawDocRepTree_titleStart2( $k, $oDoc, $sExpand )
                 .( $k == $kDoc ? "<SPAN class='DocRepTree_titleSelected'>" : "" )
                 .$this->DrawDocRepTree_title2( $k, $oDoc )
                 .( $k == $kDoc ? "</SPAN>" : "" )  // titleSelected
                 .$this->DrawDocRepTree_titleEnd2( $k, $oDoc );
            if( @$raTreeExpanded[$k] || in_array( $k, $raAncestors ) ) {
                $s .= $this->DrawDocRepTree2( $k, $iLevel + 1 );
            }
            $s .= "</DIV>";  // title
        }
        $s .= "</DIV>"   // level$level
             ."</DIV>";  // level

        return( $s );
    }


    function DrawDocRepTree_titleStart( $k, $v, $sExpand = '' )
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

    function DrawDocRepTree_titleStart2( $k, $oDoc, $sExpand = '' )
    {
        return( "<A HREF='${_SERVER['PHP_SELF']}?k=$k'><NOBR>" );
    }

    function DrawDocRepTree_title2( $k, $oDoc )
    {
        $s = $oDoc->GetTitle('');
        if( !$s ) $s = $oDoc->GetName();
        if( !$s ) $s = "Untitled";

        return( $s );
    }

    function DrawDocRepTree_titleEnd2( $k, $oDoc )
    {
        return( "</NOBR></A>" );
    }

    function getRATreeExpanded( $bSfile = false )
    /********************************************
        Return array of tree nodes that are currently expanded.
        Retrieve expanded nodes from persistent session storage, modify based on user expand/collapse,
        add current doc and its ancestors - this is important when you jump to a subtree using a url
     */
    {
        $raTreeExpanded = array();

        // Get ui state information from the persistent datasource (e.g. session vars)
        $raVars = $this->odsUIState->VarGetAllRA();
        foreach( $raVars as $k => $v ) {
            if( substr($k,0,7) == 'kexpand' ) {
                $n = substr($k,7);
                $n = $bSfile ? $n : intval($n);
                $raTreeExpanded[$n] = true;
            }
        }
        // get modifications to ui state information
        foreach( $_REQUEST as $k => $v ) {
            if( substr($k,0,7) == 'kexpand' ) {
                $n = $bSfile ? $v : intval($v);
                $raTreeExpanded[$n] = true;
                $this->odsUIState->VarSet( 'kexpand'.$n, 1 );
            }
            if( substr($k,0,9) == 'kcollapse' ) {
                $n = $bSfile ? $v : intval($v);
                if( isset($raTreeExpanded[$n]) )  unset($raTreeExpanded[$n]);
                $this->odsUIState->VarUnSet( 'kexpand'.$n );
            }
        }

        if( !$bSfile ) {
            // add current doc and its ancestors
            $kDoc = $this->oDocRepMgr->GetDocKey();
            if( $kDoc && !@$raTreeExpanded[$kDoc] ) {  // no need to compute this if it's already there (which is very likely)
                // add current doc if it is expandable
                // kluge: we currently have no information cached about children. If DocRepDoc ever caches this, use it. Otherwise, this
                //        will give false-positives if all children are invisible (worst case: an expansion icon is shown but no children expand).
                if( $this->oDocRepMgr->oDocRepDB->kfdb->Query1( "SELECT count(*) FROM docrep_docs WHERE docrep_docs_parent='$kDoc'" ) ) {
                    $raTreeExpanded[$kDoc] = true;
                    $this->odsUIState->VarSet( 'kexpand'.$kDoc, 1 );
                }
                $raAncestors = $this->oDocRepMgr->GetDocAncestors();
                foreach( $raAncestors as $k ) {
                    if( $k == $kDoc ) continue;  // already computed this one above
                    $raTreeExpanded[$k] = true;
                    $this->odsUIState->VarSet( 'kexpand'.$k, 1 );
                }
            }
        }
        return( $raTreeExpanded );
    }

    function DrawDocRepSfileTree( $dirTree, $iLevel = 1 )
    /****************************************************
        Draw the dirs and files that are immediately within the given directory.
        Don't draw the directory itself. This allows any member of a forest to be drawn separately.

        There are two ways to do this:
            1) Walk through the filesystem and get metadata from DocRep.
            2) Parse the DocRep names to find directory structures.

            The former is way more efficient because the filesystem is structured, whereas DocRep names are flat.
            The latter is somewhat more robust because it naturally reveals orphaned docrep records (without searching for them)
            whereas the former would only reveal orphaned files which is less of a DocRep integrity problem.
            For level-by-level integrity tests (which can be performed during regular operation, as opposed to global integrity
            tests), either method is probably equally efficient.

            Therefore this is implemented using the filesystem method for efficiency, and level-by-level integrity testing is advised.
     */
    {
        $s = "";

        $raTreeExpanded = $this->getRATreeExpanded( true );

        $sfileCurr = $this->oDocRepMgr->GetSFile();                          // the currently selected sfile
        if( !$sfileCurr ) $sfileCurr = $this->oDocRepMgr->GetSFileFolder();  // or a folder could be selected

        $raSfileTree = $this->oDocRepMgr->oDocRepDB->ListSfileTree( $dirTree );

        $s .= $this->drawSfileTree( $dirTree, $raSfileTree, $sfileCurr );


/* Replace this with oDocRepDB->ListSFileChildTree( $dir )
 * which gets a DocRepDoc for each child and takes permclass visibility into account
 */
/*
        $dirSfile = STD_SCRIPT_REALDIR.$this->oDocRepMgr->oDocRepDB->GetSFileDir()."/";

        if( $dirTree && substr( $dirTree, -1, 1 ) != '/' )  $dirTree = $dirTree.'/';

        $oSEEDFile = new SEEDFile();
        $oSEEDFile->Clear();
        $oSEEDFile->Traverse( $dirSfile.$dirTree, array('eFetch'=>'DIR', 'bRecurse'=>false) );
        $raDirs = $oSEEDFile->raTraverseItems;
        $oSEEDFile->Clear();
        $oSEEDFile->Traverse( $dirSfile.$dirTree, array('eFetch'=>'FILE', 'bRecurse'=>false) );
        $raFiles = $oSEEDFile->raTraverseItems;


        $s .= "<DIV class='DocRepTree_level'>"           // defines the basic attributes of structure
             ."<DIV class='DocRepTree_level$iLevel'>";   // defines variations per-level, if defined
        foreach( $raDirs as $k => $ra ) {
            $ra['name'] = $dirTree.$ra[1];    // because of the recursion
            $ra['title'] = "";
            $ra['type'] = 'FOLDER';
            $ra['parent'] = 0;
            $ra['treetype'] = 'SFILE';

            $bExpanded = @$raTreeExpanded[$ra['name']];
            if( $bExpanded ) {
                $sExpand = 'collapse';
            } else if( true ) { // count($ra['children']) ) {
                $sExpand = 'expand';
            } else {
                $sExpand = '';
            }
            $s .= "<DIV class='DocRepTree_title'>"
                 .$this->DrawDocRepTree_titleStart( $ra['name'], $ra, $sExpand )
                 .( $ra['name'] == $sfileCurr ? "<SPAN class='DocRepTree_titleSelected'>" : "" )
                 .$this->DrawDocRepTree_title( $ra[1], $ra )
                 .( $ra['name'] == $sfileCurr ? "</SPAN>" : "" )  // titleSelected
                 .$this->DrawDocRepTree_titleEnd( $ra['name'], $ra );
            // don't allow collapse if the selected file/dir is inside this dir
            $bCurrInThisFolder = (substr( $sfileCurr, 0, strlen($ra['name']) ) == $ra['name']);
            if( $bCurrInThisFolder || $bExpanded ) {
                $s .= $this->DrawDocRepSfileTree( $ra['name'], $iLevel + 1 );
            }
            $s .= "</DIV>";  // title
        }

        foreach( $raFiles as $k => $ra ) {
            $ra['name'] = $dirTree.$ra[1];    // because of the recursion

            $oDoc = $this->oDocRepMgr->oDocRepDB->GetDocObjectFromName( $ra['name'] );

            if( $oDoc && !$this->oDocRepMgr->IsPermClassModeAllowed_AnyDocObj( $oDoc, "W" ) )  continue;

            $ra['title'] = "";       // keep this blank for DrawDocRepTree_title
            $ra['type'] = $oDoc ? $oDoc->GetType() : "MISSING!";
            $ra['parent'] = 0;
            $ra['file_ext'] = $oDoc ? $oDoc->GetSFileExt() : "";    // for the icon beside the name
            $ra['treetype'] = 'SFILE';

            $s .= "<DIV class='DocRepTree_title'>"
                 .$this->DrawDocRepTree_titleStart( $ra['name'], $ra, '' )
                 .( $ra['name'] == $sfileCurr ? "<SPAN class='DocRepTree_titleSelected'>" : "" )
                 .$this->DrawDocRepTree_title( $ra[1], $ra )
                 .( $ra['name'] == $sfileCurr ? "</SPAN>" : "" )  // titleSelected
                 .$this->DrawDocRepTree_titleEnd( $ra['name'], $ra );
            $s .= "</DIV>";  // title
        }

        $s .= "</DIV>"   // level$level
             ."</DIV>";  // level
*/
        return( $s );
    }

    private function drawSfileTree( $dirTree, $raTree, $sfileCurr, $iLevel = 1 )
    {
        if( !$raTree )  return( "" );

// parm
$raTreeExpanded = $this->getRATreeExpanded( true );

        $s = "<div class='DocRepTree_level'>"           // defines the basic attributes of structure
            ."<div class='DocRepTree_level$iLevel'>";   // defines variations per-level, if defined

        foreach( $raTree['dirs'] as $dir => $raSubtree ) {
            $ra['name'] = $dirTree.$dir;    // because of the recursion
            $ra['title'] = "";
            $ra['type'] = 'FOLDER';
            $ra['parent'] = 0;
            $ra['treetype'] = 'SFILE';

            $bExpanded = @$raTreeExpanded[$ra['name']];
            if( $bExpanded ) {
                $sExpand = 'collapse';
            } else if( true ) { // count($ra['children']) ) {
                $sExpand = 'expand';
            } else {
                $sExpand = '';
            }
            $s .= "<div class='DocRepTree_title'>"
                 .$this->DrawDocRepTree_titleStart( $ra['name'], $ra, $sExpand )
                 .( $ra['name'] == $sfileCurr ? "<span class='DocRepTree_titleSelected'>" : "" )
                 .$this->DrawDocRepTree_title( $dir, $ra )
                 .( $ra['name'] == $sfileCurr ? "</span>" : "" )  // titleSelected
                 .$this->DrawDocRepTree_titleEnd( $ra['name'], $ra );
            // don't allow collapse if the selected file/dir is inside this dir
            $bCurrInThisFolder = (substr( $sfileCurr, 0, strlen($ra['name']) ) == $ra['name']);
            if( $bCurrInThisFolder || $bExpanded ) {
                $s .= $this->drawSfileTree( $dirTree.$dir.'/', $raSubtree, $sfileCurr, $iLevel + 1 );
            }
            $s .= "</div>";  // title
        }

        foreach( $raTree['files'] as $k => $raF ) {
            $ra['name'] = $dirTree.$k;    // because of the recursion

            $oDoc = $raF['obj'];

            $ra['title'] = "";       // keep this blank for DrawDocRepTree_title
            $ra['type'] = $oDoc ? $oDoc->GetType() : "MISSING!";
            $ra['parent'] = 0;
            $ra['file_ext'] = $oDoc ? $oDoc->GetSFileExt() : "";    // for the icon beside the name
            $ra['treetype'] = 'SFILE';

            $s .= "<div class='DocRepTree_title'>"
                 .$this->DrawDocRepTree_titleStart( $ra['name'], $ra, '' )
                 .( $ra['name'] == $sfileCurr ? "<span class='DocRepTree_titleSelected'>" : "" )
                 .$this->DrawDocRepTree_title( $k, $ra )
                 .( $ra['name'] == $sfileCurr ? "</span>" : "" )  // titleSelected
                 .$this->DrawDocRepTree_titleEnd( $ra['name'], $ra );
            $s .= "</div>";  // title
        }

        $s .= "</div>"   // level$level
             ."</div>";  // level

        return( $s );
    }

    function DrawControls_InsertFolder_Rename( $nextAction )
    /*******************************************************
        $nextAction == insert_folder: draw the form for inserting a folder - gets dr_pos
                       rename:        draw the form to rename a folder or document (doesn't make a new version)
     */
    {
        // if inserting a new folder, use a blank oDoc to show blank controls
        $oDoc = ($nextAction == 'insert_folder' ? new DocRepDoc($this->oDocRepMgr->oDocRepDB, 0) : $this->oDocRepMgr->oDocCurr);

        $s = "<FORM method='POST' action='${_SERVER['PHP_SELF']}'>"
            .SEEDForm_Hidden( 'drm_action', $nextAction )
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

                       insert_sfile: draw the form for uploading a new sfile
     */
    {
        $oDoc = $nextAction == 'update_file' ? $this->oDocRepMgr->oDocCurr
                                             : new DocRepDoc($this->oDocRepMgr->oDocRepDB, 0);

        $s = "<FORM enctype='multipart/form-data' method='POST' action='${_SERVER['PHP_SELF']}'>"
            ."<INPUT type='hidden' name='drm_action' value='$nextAction' />"
            ."<INPUT type='hidden' name='MAX_FILE_SIZE' value='10000000' />"
            ."<INPUT type='hidden' name='k' value='".$this->oDocRepMgr->GetDocKey()."'/>";

        if( $nextAction == 'insert_file' ) {
            $s .= $this->drawControlsTemplate( $oDoc, "<P>[[Position]]</P>" );
        }

        $sTemplate = "";
        if( $nextAction == 'insert_file' || $nextAction == 'update_file' ) {
            $sTemplate = "<TR>[[Title]]</TR>"
                        ."<TR>[[Name_FileKluge]]</TR>";  // kluge draws extra check box control in the second TD of this field
        } else if( $nextAction == 'insert_sfile' ) {
            $sTemplate .= "<TR>[[Name-sfile-folder]]</TR>"
                         ."<TR>[[Name-sfile-blank]]</TR>";
        } else if( $nextAction == 'update_sfile' ) {
            $sTemplate .= "<TR>[[Name-sfile]]</TR>";
        }
        $sTemplate .= "<TR>[[Perms]]</TR>"
                     ."<TR>[[Comments]]</TR>";

        $sAttrsUploadBtn = "";
        $sAttrsFileCtl = "";
        if( $nextAction == 'insert_sfile' ) {
            // initialize button as disabled and only enable it when a file is chosen
            $sAttrsUploadBtn = " disabled='disabled'";
            $sAttrsFileCtl = " onChange='doUploadFileChange(\"doc_name_sfile\");'";
            $s .= "<script>"
                 ."function doUploadFileChange(ctlName) {"
                     ."/* Put the upload filename in the name field, if the name field is empty */"
                     ."var n = $('#'+ctlName).val();"
                     ."var f = $('#docmgrFile').val();"
                     // Chrome and IE prepend C:\fakepath\
                     //  (the '\\\\' below is a single '\' by the time split sees it)
                     //  BUT not if you move this out of php double-quotes, like into a .js file
                     ."if( !n ) { "
                         ."n = f.split('\\\\').pop();"
                         // use regex global because normal js replace only replaces the first occurrance
                         ."n = n.replace(/\\s/g, '-');"    // the double \\ is for php; change to \ if moving to .js file
                         ."$('#'+ctlName).val(n);"
                     ."}"
                     ."$('#docmgrBtnUpload').prop('disabled', (f=='' || n==''));"
                 ."} </script>";
        }
        $s .= "<TABLE class='DocRepApp_controlArea_Text'>"
             .$this->drawControlsMetaItems( $oDoc, $sTemplate )
             ."<TR><TD>Upload this file: </TD><TD><INPUT name='docmgrFile' id='docmgrFile' type='file' size='40' $sAttrsFileCtl/></TD></TR>"
             ."<TR><TD><BR><INPUT type='submit' name='docmgrBtnUpload' id='docmgrBtnUpload' value='Upload' $sAttrsUploadBtn/></TD></TR>"
             ."</TABLE></FORM>";
        return( $s );
    }

    function DrawControls_ReplaceSFile()
    /***********************************
        Although DocRep might allow renaming and replacing in a single step, the UI is much more straightforward
        if those are separated. There's hardly ever a time when someone wants both.
     */
    {
        $s = "";

        $oDoc = $this->oDocRepMgr->GetSFileObj();
        $sfile = $this->oDocRepMgr->GetSFile();

        if( !$sfile ) {
            $s = "This is for replacing files, not folders";
            goto done;
        }

        $sAttrsUploadBtn = " disabled='disabled'";
        $sAttrsFileCtl = " onChange='doReplaceSFileCtrl();'";
        $s .= "<script>"
             ."function doReplaceSFileCtrl() {"
                 ."/* Enable the Upload button when a file has been chosen */"
                 ."var f = $('#docmgrFile').val();"
                 ."$('#docmgrBtnUpload').prop('disabled', (f==''));"
             ."} </script>";

        $s .= "<form enctype='multipart/form-data' method='post' action='".Site_path_self()."'>"
             .SEEDForm_Hidden( 'drm_action', 'update_sfile' )
             .SEEDForm_Hidden( 'MAX_FILE_SIZE', '10000000' )    // don't prefix this with sfAp_ if SEEDForm is ever used
             ."<table class='DocRepApp_controlArea_Text'>"
             ."<tr><td>File: </td><td>$sfile</td></tr>"
             .$this->drawControlsMetaItems( $oDoc, "<tr>[[Comments]]</tr>" )
             ."<tr><td>Replace with this file: </TD>"
                 ."<td><input name='docmgrFile' id='docmgrFile' type='file' size='40' $sAttrsFileCtl/></TD></TR>"
             ."<tr><td><br/><input type='submit' name='docmgrBtnUpload' id='docmgrBtnUpload' value='Upload' $sAttrsUploadBtn/></td></tr>"
             ."</table></form>";

        done:
        return( $s );
    }

    function DrawControls_RenameSfile()
    {
        $oDoc = $this->oDocRepMgr->GetSFileObj();

        $s = "<form method='post' action='".Site_path_self()."'>"
            .SEEDForm_Hidden( 'drm_action', 'update_sfile' )
            ."<table class='DocRepApp_controlArea_Text'>"
             // it's okay to have oDoc==NULL for Name-sfile
            .$this->drawControlsMetaItems( $oDoc, "<tr>[[Name-sfile]]</tr>"
                                                 .($oDoc ? "<tr>[[Perms-sfile]]</tr><tr>[[Comments]]</tr>" : "") )
            ."<tr><td><br/><input type='submit' value='Rename'/></td></tr>"
            ."<tr><td>&nbsp;</td><td style='font-size:8pt'>The file name includes the folder name.<br/>e.g. folderA/folderB/file.jpg<br/><br/>"
                                ."To move a file to a different folder, just type the full folder+file name. New folders are created automatically.</td></tr>"
            ."</table></form>";

        return( $s );
    }

    function DrawControls_DeleteSfile()
    {
        $s = "";

        $oDoc = $this->oDocRepMgr->GetSFileObj();
        $sfile = $this->oDocRepMgr->GetSFile();

        if( !$sfile ) {
            $s = "This is for deleting individual files.";
            goto done;
        }

        if( $oDoc && $oDoc->GetStatus() == 'DELETED' ) {
            $sTrashState = " in the Trash";
            $sAction = "<div style='display:inline-block'>".$this->actionButton( 'undelete_sfile', 'Undelete' )."</div>"
                      ."&nbsp;&nbsp;&nbsp;"
                      ."<div style='display:inline-block'>".$this->actionButton( 'purge_deleted_sfile', 'Purge Forever' )."</div>";
        } else {
            $sTrashState = "";
            $sAction = $this->actionButton( 'trash_sfile', 'Move to Trash' );
        }

        $s .= "<table class='DocRepApp_controlArea_Text'>"
             ."<tr><td>File$sTrashState: </td><td>$sfile</td></tr>"
             ."<tr><td colspan='2'><br/>$sAction</td></tr>"
            ."</table>";

        done:
        return( $s );
    }

    private function actionButton( $pAction, $sButton )
    {
        return( "<form method='post' action='".Site_path_self()."'>"
               .SEEDForm_Hidden( 'drm_action', $pAction )
               ."<input type='submit' value='$sButton'/>"
               ."</form>" );
    }

/*
    private function drawControlsForm( $kDoc, $drm_action, $sBody, $sSubmitLabel )
    {
        $s = "<form method='post' action='${_SERVER['PHP_SELF']}'>"
            .SEEDForm_Hidden( 'drm_action', $drm_action )
            .SEEDForm_Hidden( 'k', $kDoc )
            .$sBody
            ."<input type='submit' value='$sSubmitLabel'/>"
            ."</form>";
    }
*/

    function DrawPreview( $eTextType = "", $sDocText = NULL, $bShowSource = false )
    /******************************************************************************
     */
    {
        $s  = "";

        if( $this->oDocRepMgr->GetDocType() != 'TEXT' )  return( "" );

        if( !$eTextType )  $eTextType = DocRepTextTypes::GetFromTagStr( $this->oDocRepMgr->GetDocValue('verspec') );

        if( $bShowSource || !DocRepTextTypes::IsHTML($eTextType) ) {    // use !HTML to catch old WIKI types and errors
            $s .= "<PRE>"
                 .wordwrap( SEEDStd_HSC( $sDocText === NULL ? $this->oDocRepMgr->GetDocText() : $sDocText ), 150, "\n", 1 )
                 ."</PRE>";
        } else {
            if( $eTextType == 'TEXTTYPE_HTML_SOD' ) {
// use MasterTemplate to expand
                $s .= $sDocText === NULL ? $this->oDocRepMgr->GetDocText() : $sDocText;
            } else {
                $s .= $sDocText === NULL ? $this->oDocRepMgr->GetDocText() : $sDocText;
            }
        }

        return( $s );
    }

    function DrawTextEditor( $bInsert, $bQuick = false, $raParms = array() )
    /***********************************************************************
        Draw the editor form
            $bInsert: creating a new doc with current doc as parent (can be changed to sibling by this form)
            $bQuick:  only allowed for updating current docs, edit content only, using editor for doc_type
     */
    {
        $s = "";

        if( $bInsert ) {
            $kDoc = 0;
            $kParent = $this->oDocRepMgr->GetDocKey();
        } else {
            $kDoc = $this->oDocRepMgr->GetDocKey();
            $kParent = 0;
        }

        if( $bQuick && !$kDoc ) {
            return( "" );
        }

        $oDoc = ($kDoc ? $this->oDocRepMgr->oDocCurr : new DocRepDoc($this->oDocRepMgr->oDocRepDB, 0) );

        $bChangedUI = SEEDSafeGPC_GetInt( 'dru_texttype_changed' );
        if( !$bQuick && ($bChangedUI || $this->bSaveNewVersionDone) ) {
            // This is not the initial draw of this form, so some submitted form elements might override the oDoc
            // (only applies to full editor that submits dru_texttype)
            $eTextType = DocRepTextTypes::NormalizeTextType(SEEDSafeGPC_GetStrPlain('dru_texttype'));
        } else {
            // Initial draw of this form.
            $eTextType = $kDoc ? DocRepTextTypes::GetFromTagStr($oDoc->GetValue('verspec', '')) : "TEXTTYPE_HTML";
        }
        if( !$eTextType )  $eTextType = "TEXTTYPE_PLAIN";

        $s .= "<FORM method='POST' action='${_SERVER['PHP_SELF']}'>";
        if( isset($raParms['hiddenParms']) ) {
            foreach( $raParms['hiddenParms'] as $k => $v ) {
                $s .= SEEDForm_Hidden( $k, $v );
            }
        }
// Technically, DocMgrUI is not supposed to know about dra_mode but this has to be differentiated edit_text vs edit_text_new
        $s .= SEEDForm_Hidden( 'dra_mode', $bQuick ? 'edit_text_quick' : 'edit_text' )  // next page is this editor in update mode
             .SEEDForm_Hidden( 'drm_action', $kDoc ? "update_text" : "insert_text" )
             .SEEDForm_Hidden( 'k', $kDoc )                   // update_text: k=kDoc ; insert_text: k=kParent
             ."<TABLE border='0'><TR valign='top'><TD valign='top'>";
        if( !$bQuick ) {
             $s .= "<SPAN style='font-size:14pt;font-weight:bold;'>".($kDoc ? "Edit Document" : "Create Document")."</SPAN>"
                  .SEEDStd_StrNBSP("",10)
                  ."Page Type: "
                  .SEEDForm_Select( "dru_texttype",
                                    array("TEXTTYPE_HTML"=>"HTML",        "TEXTTYPE_HTML_SOD"=>"HTML with SoD tags",
                                          "TEXTTYPE_PLAIN"=>"Plain Text", "TEXTTYPE_PLAIN_SOD"=>"Plain with SoD tags",
                                          "TEXTTYPE_WIKILINK"=>"Wiki (Links Only)", "TEXTTYPE_WIKI"=>"Wiki",    // deprecate
                                    ),
                                    $eTextType,
                                    array( "selectAttrs" => "onChange='e=document.getElementById(\"dru_texttype_changed\");e.value=1;submit();'" ) )
                  .SEEDForm_Hidden( "dru_texttype_changed", 0 )
                  .SEEDStd_StrNBSP("",10);

            if( !$kDoc ) { // can't happen in Quick Edit
                $s .= "<DIV style='display:inline-block'>".$this->drawControlsTemplate( $oDoc, "[[Position]]" )."</DIV>";
            }
        }
        $s .= "</TD></TR></TABLE>";

        if( !$bQuick ) {
            $s .= $this->drawControlsMetaItems( $oDoc, "<TABLE border='0' cellspacing='0' cellpadding='2'>"
                                                      ."<TR>[[Title]] [[nbsp3]] [[Perms]]    [[nbsp3]] [[Version]]</TR>"
                                                      ."<TR>[[Name]]  [[nbsp3]] [[Comments]] [[nbsp]]  [[nbsp]]</TR>"
                                                      ."</TABLE>" );
        }

        $docText = SEEDSafeGPC_GetStrPlain('doc_text');
        if( empty($docText) && $kDoc )  $docText = @$this->oDocRepMgr->GetDocText();

        switch( $eTextType ) {
            case 'TEXTTYPE_HTML':
                $s .= $this->dteHTMLEditor( $docText ).$this->dteSaveButtons();
                break;
            case 'TEXTTYPE_HTML_SOD':
                $bPreview = (@$_REQUEST['dra01_textsubmit'] == 'Preview');
                $s .= $this->dtePreviewButtons( $bPreview )
                     .($bPreview ? $this->dtePreviewSOD( $docText ) : ($this->dteHTMLEditor( $docText ).$this->dteSaveButtons()) );
                break;
            case 'TEXTTYPE_PLAIN':
            default:                                    // expect the unexpected
                $s .= $this->dteSavebuttons()
                     .$this->dtePlainEditor( $docText );
                break;
            case 'TEXTTYPE_PLAIN_SOD':
                $bPreview = (@$_REQUEST['dra01_textsubmit'] == 'Preview');
                $s .= $this->dtePreviewButtons( $bPreview )
                     .($bPreview ? $this->dtePreviewSOD( $docText ) : ($this->dteSavebuttons().$this->dtePlainEditor( $docText )) );
                break;
        }

        $s .= "</FORM>";

        return( $s );
    }

    private function dtePreviewButtons( $bPreview )
    {
        $s = "<input type='submit' name='dra01_textsubmit' value='Preview'".($bPreview ? " DISABLED" : "")."/>"
            .SEEDStd_StrNBSP("",6)
            ."<input type='submit' name='dra01_textsubmit' value='Edit'".(!$bPreview ? " DISABLED" : "")."/>"
            .SEEDStd_StrNBSP("",6);
        return( $s );
    }

    private function dteSaveButtons()
    {
        $s = "<input type='submit' name='dru_textsubmit' value='Save new version'/>";
        if( $this->bSaveNewVersionDone || @$_REQUEST['dru_SaveNewVersionDone'] ) {
            // A new version was saved on a previous submission in this session, so give the option to Save without making a new version
            $s .= SEEDStd_StrNBSP("",6)."<input type='submit' name='dru_textsubmit' value='Save'/>";
            // This continues to be propagated as long as the user keeps using buttons on the edit box (Preview, Edit, Save, Save New Version).
            $s .= SEEDForm_Hidden( 'dru_SaveNewVersionDone', '1' );
        }
        return( $s );
    }

    private function dtePreviewSoD( $docText )
    {
        $s = "<div style='background-color:white;border:solid thin black;padding:1em;font-family:arial,helvetica,sans serif;font-size:9pt;'>";

// use a MasterTemplate preview generator
$oDocRepWiki = new DocRepWiki( $this->oDocRepMgr->oDocRepDB, "", array('php_serve_img'=> kluge_MyDoc(),
                                                                       'php_serve_link'=> 'DO_NOT_FOLLOW' ) );
$s .= $oDocRepWiki->TranslateDoc( $this->oDocRepMgr->GetDocKey(), $docText );        // overrides the stored text using special second parm; TranslateDoc knows WIKI/WIKILINK
        $s .= "</div>"
             .SEEDForm_Hidden( 'doc_text', $docText ); // propagate current edited text

        return( $s );
    }

    private function dteHTMLEditor( $docText )
    {
        $oEdit = new SEEDEditor( "TinyMCE" );
        $oEdit->SetFieldName( "doc_text" );
        $oEdit->SetContent( $docText );

        return( $oEdit->Editor( array('controls'=>'Joomla', 'width_css'=>'100%','height_px'=>600) ) );
    }

    private function dtePlainEditor( $docText )
    {
        $s = "<textarea name='doc_text' rows=40 style='width:100%'>".SEEDStd_HSC($docText)."</textarea>";
        return( $s );
    }


    function drawControlsTemplate( $oDoc, $sTemplate )
    /*************************************************
        Draw the templated controls for the given oDoc.  The oDoc is either the current doc in oDocRepMgr or a blank one for inserts
     */
    {
        // Some controls don't need oDoc, and e.g. for sfile folders oDoc==NULL
        if( strpos( $sTemplate, "[[Name-sfile-folder]]" ) !== false ) {
            // Just put the current folder in the text field - this is used for New
            if( ($sfileFolder = $this->oDocRepMgr->GetSFileFolder()) ) {
                $sfileFolder .= "/";
            }
            $s = SEEDForm_TextTD( 'doc_name_sfilefolder', $sfileFolder, "Folder: ", 0, "style='width:100%';" );
            $sTemplate = str_replace( "[[Name-sfile-folder]]", $s, $sTemplate );
        }
        if( strpos( $sTemplate, "[[Name-sfile-blank]]" ) !== false ) {
            $s = SEEDForm_TextTD( 'doc_name_sfile', "", "File: ", 0, "style='width:100%';" );
            $sTemplate = str_replace( "[[Name-sfile-blank]]", $s, $sTemplate );
        }
        if( strpos( $sTemplate, "[[Name-sfile]]" ) !== false ) {
            if( ($sfile = $this->oDocRepMgr->GetSFile()) ) {
                $s = SEEDForm_TextTD( 'doc_name_sfile', $sfile, "File: ", 0, "style='width:100%';" );
            } else if( ($sfile = $this->oDocRepMgr->GetSFileFolder()) ) {  // or a folder could be selected
                $s = SEEDForm_TextTD( 'doc_name_sfile', $sfile, "Folder: ", 0, "style='width:100%';" );
            } else {
                $s = "<td>&nbsp;</td><td>Please select a file</td>";
            }
            $sTemplate = str_replace( "[[Name-sfile]]", $s, $sTemplate );
        }

        if( !$oDoc ) {
            return( $sTemplate );
        }


        // The rest of the controls use oDoc

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
        if( strpos( $sTemplate, "[[Perms-sfile]]" ) !== false ) {
            // use the oDocMgr permclasses since they apply to all inserts and updates
            $s = "<TD valign='top'>Permission: </TD><TD valign='top'><SELECT name='doc_permclass'>";
            $pcSel = $oDoc->GetPermclass();
            $raPCNames = $this->oDocRepMgr->EnumPermClassNames( "W" );
            foreach( $raPCNames as $pc => $pcname ) {
                $s .= "<OPTION value='$pc'".($pcSel==$pc ? " SELECTED" : "").">$pcname</OPTION>";
            }
            $s .= "</SELECT></TD>";

            $sTemplate = str_replace( "[[Perms-sfile]]", $s, $sTemplate );
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
                $s = "<INPUT type='radio' name='dr_pos' value='under' CHECKED> Inside the selected folder<BR>"
                    ."<INPUT type='radio' name='dr_pos' value='after'> After the selected folder";
            } else {
                $s = "<INPUT type='radio' name='dr_pos' value='after' CHECKED> After the selected document<BR>"
                    ."<INPUT type='radio' name='dr_pos' value='under'> Indented beneath the selected document";
            }
            $sTemplate = str_replace( "[[Position]]", $s, $sTemplate );
        }

        return( $sTemplate );
    }

//TODO: surely we can prevent over-writing spec if the parm is not set. Just overwrite the fields that are specified in parms.
    function drawControlsMetaItems( $oDoc, $sTemplate )
    {
        $s = $this->drawControlsTemplate( $oDoc, $sTemplate );
        // propagate spec back into the record on edit, because we would otherwise blank it out
        if( $oDoc ) {    // e.g. sfile folder has a null oDoc
            $s .= SEEDForm_Hidden( 'doc_spec',    $oDoc->GetValue('spec','') )
                 .SEEDForm_Hidden( 'doc_verspec', $oDoc->GetValue('verspec','') );
        }
        return( $s );
    }

    function DrawDocRepVersions( $parms = array() )
    /**********************************************
     */
    {
        $s  = "";

        if( !($kDoc = $this->oDocRepMgr->GetDocKey()) )  return( "" );

        $iVersionSelect = SEEDSafeGPC_GetInt( 'v' );
        if( !$iVersionSelect ) {
            // default to the maxVer
            $iVersionSelect = $this->oDocRepMgr->GetDocValue('maxVer');
        }

        $bAdmin = @$parms['bAdmin'];    // show DXD flags and allow Delete Versions

        if( $bAdmin ) {
            $s .= "<FORM method='post' action='${_SERVER['PHP_SELF']}'>"
// obsolete if a tabform is keeping the UI in version mode
."<INPUT type='hidden' name='dra_mode' value='show_versions'>"
                 ."<INPUT type='hidden' name='drm_action' value='dra01_ver_update'>";
        }
        $s .= "<TABLE border='0'>";

        // Diff is very klugey.
        // It refers to docdiff.php which is external to DocRepApp.
        // Also, since we process these in DESC ver order, the older ver key is not known when each row is formatted.
        // So we delay formatting of the end of each row, until the next (older) row is read.

        $newerDiffKey = 0;


        // use DocRepDB to ensure permclass access, etc
        $dbc = $this->oDocRepMgr->oDocRepDB->kfdb->CursorOpen( "SELECT * from docrep_docdata where fk_docrep_docs='$kDoc' ORDER BY ver DESC" );
        while( $ra = $this->oDocRepMgr->oDocRepDB->kfdb->CursorFetch( $dbc ) ) {
            if( $newerDiffKey ) {
                // finish formatting the last row, show diff link
                $s .= "<TD>"
//                     ."<A HREF='docdiff.php?kDataNew=$newerDiffKey&kDataOld=${ra['_key']}' target='_blank'>Difference</A>"
                     ."<A HREF='${_SERVER['PHP_SELF']}?k=$kDoc&kDataNew=$newerDiffKey&kDataOld=${ra['_key']}&dra01_mode=show_versions' style='$sStyle'>Difference</A>"
                ."</TD></TR>";
            }
            $newerDiffKey = $ra['_key'];

            $sStyle = "text-decoration:none;";
            if( $ra['ver'] == $iVersionSelect )  $sStyle .= "font-weight:bold;";
            $s .= "<TR><TD valign='top'>"
                 ."<A HREF='${_SERVER['PHP_SELF']}?k=$kDoc&v=${ra['ver']}&dra01_mode=show_versions' style='$sStyle'>"
                 ."${ra['ver']} : ${ra['meta_title']}</A></TD>";
            $sDXD = "";
            $dbcDXD = $this->oDocRepMgr->oDocRepDB->kfdb->CursorOpen( "SELECT * FROM docrep_docxdata WHERE fk_docrep_docdata='${ra['_key']}'" );
            while( $raDXD = $this->oDocRepMgr->oDocRepDB->kfdb->CursorFetch( $dbcDXD ) ) {
                if( !empty($sDXD) )  $sDXD .= " ";
                $sDXD .= $raDXD['flag'];
            }
            //$this->oDocRepDB->kfdb->KFDB_CursorClose( $dbcDXD );

            if( $bAdmin ) {
                $s .= "<TD>".SEEDStd_StrNBSP('',5)."<INPUT type='text' size='8' name='verdxd${ra['_key']}' value='$sDXD'></TD>"
                     ."<TD>".SEEDStd_StrNBSP('',10)."<INPUT type='checkbox' name='verdel${ra['_key']}' value='1'></TD>";
            } else {
                $s .= "<TD>".SEEDStd_StrNBSP('',5)."$sDXD</TD>";
            }
        }
        // finish formatting the last row. There is no diff link because there is no older version.
        $s .= "<TD>&nbsp;</TD></TR>";

        if( $bAdmin ) {
            $s .= "<TR><TD>&nbsp;</TD>"
                 ."<TD>".SEEDStd_StrNBSP('',5)."<INPUT type='submit' name='dra01_ver_update_action' value='Change'></TD>"
                 ."<TD>".SEEDStd_StrNBSP('',5)."<INPUT type='submit' name='dra01_ver_update_action' value='Delete'></TD></TR>";
        }

        $s .= "</TABLE>";
        if( $bAdmin )  $s .= "</FORM>";

        return( $s );
    }

    function DrawVersionPreview( $parms = array() )
    {
        $s = "";
        $sTitle = $sText = "";

        if( !($kDoc = $this->oDocRepMgr->GetDocKey()) )  return( "" );

        /* kDataNew + kDataOld : show differences between these versions
         * v                   : show this version
         * (else)              : show the max version
         */
        if( ($kDataNew = SEEDSafeGPC_GetInt('kDataNew')) && ($kDataOld = SEEDSafeGPC_GetInt('kDataOld')) ) {
            $sTitle = "Difference";
            $sText = DocRepDiff::DiffVersions( $this->oDocRepMgr->oDocRepDB, $kDataOld, $kDataNew );
        } else {
            if( !($iVersion = SEEDSafeGPC_GetInt('v')) ) {
                // default to the maxVer
                $iVersion = $this->oDocRepMgr->GetDocValue('maxVer');
            }

            if( $this->oDocRepMgr->GetDocType()=='TEXT' ) {
                // do this with an oDocMgr/oDoc method like GetDocVerText($ver) or use a parm other than flag
                $ra = $this->oDocRepMgr->oDocRepDB->kfdb->KFDB_QueryRA("SELECT * FROM docrep_docdata WHERE fk_docrep_docs='".$this->oDocRepMgr->GetDocKey()."' AND ver='$iVersion'" );

                $sTitle = $ra['meta_title'];

                $eTextType = DocRepTextTypes::GetFromTagStr( $this->oDocRepMgr->GetDocValue('verspec') );
                if( DocRepTextTypes::IsHTML($eTextType) ) {
                    $sText = $ra['data_text'];
                } else {
                    $sText = "<PRE>".SEEDStd_HSC($ra['data_text'])."</PRE>";
                }
            }
        }
        $s .= "<div style='background-color:#eee;padding:1em;'>"
             ."<h3>$sTitle</h3>"
             ."<div style='background-color:white;border:solid thin black;padding:1em;font-family:arial,helvetica,sans serif;font-size:9pt;'>"
             .$sText
             ."</div></div>";

        return( $s );
    }

    function _getIconName2( $oDoc )
    {
        return( $this->_getIconName( array( 'type'=>$oDoc->GetType(), 'file_ext'=>'jpeg') ) );
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
