<?php

/* DocRepMgr
 *
 * Copyright (c) 2012-2017 Seeds of Diversity Canada
 *
 * Provides sub-UI support for DocRep Manager applications.
 * Encapsulates DocRepDB under an app-friendly API.
 *
 * DocRepDB knows how to store a consistent Document Repository, but it doesn't know what any of the codes mean.  This class knows what
 * a lot of the codes mean in a standard DocRepApp context.  It also knows your uid, and the caller provides a method to translate that into an
 * array of permclasses.  Then this class uses that information to fetch and update all the information that would be needed by a DocRep Manager app.
 *
 * API: A "current document" is loaded and information provided.
 *      Update actions on the currDoc are marshalled through a single Update() method using a set of named actions.
 *      Document data and properties are coded in both directions using a set of named parms.
 */

include_once( "DocRepDB.php" );


class DocRepMgr
{
    var $oDocRepDB;

    var $oDocCurr = NULL;  // when a document is active this DocRepDoc caches its information
    var $errMsg = "";

    private $sfileCurr = "";
    private $sfileCurrFolder = "";
    private $sfileCurrObj = null;

    function __construct( KeyFrameDB $kfdb, $uid )
    {
        $parms['raPermClassesR'] = $this->GetPermClasses("R");
        $parms['raPermClassesW'] = $this->GetPermClasses("W");
        $this->oDocRepDB = new DocRepDB( $kfdb, $uid, $parms );
    }

    function GetDocKey()                       { return( $this->oDocCurr ? $this->oDocCurr->GetKey() : 0 ); }
    function GetDocName()                      { return( $this->oDocCurr ? $this->oDocCurr->GetName() : "" ); }
    function GetDocFolderName()                { return( $this->oDocCurr ? $this->oDocCurr->GetFolderName() : "" ); }
    function GetDocTitle( $flag = '' )         { return( $this->oDocCurr ? $this->oDocCurr->GetTitle($flag) : "" ); }
    function GetDocType()                      { return( $this->oDocCurr ? $this->oDocCurr->GetType() : "" ); }
    function GetDocText( $flag = '' )          { return( $this->oDocCurr ? $this->oDocCurr->GetText($flag) : "" ); }
    function GetDocValue( $sName, $flag = '' ) { return( $this->oDocCurr ? $this->oDocCurr->GetValue( $sName, $flag ) : NULL ); }

    function GetDocAncestors()                 { return( $this->oDocCurr ? $this->oDocCurr->GetAncestors() : array() ); }

    function GetNewDocName( $sName, $bInsertInto = false ) { return( $this->oDocCurr ? $this->oDocCurr->GetNewDocName($sName, $bInsertInto) : "" ); }

    function SetDocKey( $kDoc )
    {
        $this->oDocCurr = $kDoc ? ( new DocRepDoc( $this->oDocRepDB, $kDoc ) ) : NULL;
        return( $this->GetDocKey() );  // if successful and the user can access the doc this will be $kDoc else 0
    }

    function GetSFile()       { return( $this->sfileCurr ); }
    function GetSFileFolder() { return( $this->sfileCurrFolder ); }
    function GetSFileObj()    { return( $this->sfileCurrObj ); }

    function SetSFile( $sfile )
    /**************************
        Set the current sfile reference, whether or not it is a real file or a real object.
        Return the same name, or "" if fail.
     */
    {
        $this->sfileCurr = "";
        $this->sfileCurrFolder = "";
        $this->sfileCurrObj = null;

        // actually, not sure what the caller should do if the sfile is a folder or an orphaned file. it probably wants this stored anyway.

        if( $this->oDocRepDB->SFileIsDir($sfile) ) {
            // sfile folders are special because they have no existence within DocRep.
            // There are no DocRep records for sfile folders, so no DocRepDoc object.
            // The DocRepMgr code should never try to do anything file-related in this case, except to use this folder
            // as an insertion point for an upload.
            // So the sfileCurr is blank, even though this is somewhat inconsistent with the way it's used in the file case below.
            $this->sfileCurrFolder = $sfile;
        } else {
            $this->sfileCurr = $sfile;
            if( ($i = strrpos( $sfile, '/' )) !== false ) {
                $this->sfileCurrFolder = substr( $sfile, 0, $i );
            }
            $this->sfileCurrObj = $this->oDocRepDB->GetDocObjectFromName( $sfile );
        }

        return( $sfile );
    }

    function SetErrorMsg( $errMsg ) { $this->errMsg = $errMsg; }
    function GetErrorMsg()          { return( $this->errMsg ); }

    function IsPermClassModeAllowed( $mode )  // maybe there is a nicer way to cache this for each mode
    /***************************************
        Return true if the given permclass mode is allowed for the current doc (e.g. mode is R, W, A, P)
     */
    {
        return( $this->isPermAllowed( $this->oDocCurr, $mode ) );
    }

    function IsPermClassModeAllowedSfile( $mode )
    {
        return( $this->isPermAllowed( $this->sfileCurrObj, $mode ) );
    }

    private function isPermAllowed( $oDoc, $mode )
    {
        return( $oDoc && ($p = $oDoc->GetPermclass()) ? in_array( $p, $this->GetPermClasses($mode) ) : false );
    }

    function PermRead()     { return( $this->IsPermClassModeAllowed( 'R' ) ); }
    function PermWrite()    { return( $this->IsPermClassModeAllowed( 'W' ) ); }
    function PermAdmin()    { return( $this->IsPermClassModeAllowed( 'A' ) ); }
    function PermApprove()  { return( $this->IsPermClassModeAllowed( 'P' ) ); }

    function Update( $sAction, $raParms )
    /************************************
     * Parms:
     *     dr_name, dr_title, dr_permclass, dr_desc, dr_spec, dr_verspec
     *     bRenameDescendants (for 'rename')
     *
     * Return:
     *     the updated, or new, document key is returned.  0 if failure.
     *
     * Reloading: any changes to the current doc are reloaded into $this->oDocCurr
     *            N.B. when a doc is inserted, it does not become the active doc.  The new doc key is returned, and the client must decide whether to
     *            load up the oDocCurr or not.
     */
    {
//var_dump($raParms);
        $bHandled = false;
        $kDocInserted = 0;

        /* Actions that don't need a current doc
         */
        switch( $sAction ) {
            /* insert_sfile : dr_name is the full folder/name
             * insert_sfilefolder : dr_name is the full folder name
             *
             * update_sfile : operate on the current selection
             *                dr_name is optional (rename) - a full folder/name
             *                dr_filename is optional - file to replace
             *
             * trash_sfile : operate on the current selection
             */
            case 'insert_sfile':
                // Create a new file called $raParms['dr_name'] using $raParms['sFilename'] and metadata from $raParms.
                // If the name already exists, use a unique name.
                if( ($oDoc = new DocRepDoc_Insert($this->oDocRepDB)) &&
                    $oDoc->InsertSFile( $raParms['dr_name'], $raParms['sFilename'], $raParms ) )
                {
                    $bHandled = true;
                    $kDocInserted = $oDoc->GetKey();
                    $this->SetSFile( $oDoc->GetName() );
                }
                break;
            case 'insert_sfilefolder':
                // Create a new folder called $raParms['dr_name'] with metadata from $raParms.
                // If the folder already exists, fail gracefully with a warning.
                if( ($oDoc = new DocRepDoc_Insert($this->oDocRepDB)) &&
                    $oDoc->InsertSFileFolder( $raParms['dr_name'], $raParms ) )
                {
                    $bHandled = true;
                    $kDocInserted = $oDoc->GetKey();
                }
                break;

            case 'update_sfile':
                // Operate on the current sfile selection to change the file and/or name and/or metadata.
                // If the current selection is a
                //     folder:            dr_name + metadata is a rename (move) of the folder and contents
                //     file:              dr_name + metadata is a rename (move), sFilename + metadata is a replace.
                //     file with no oDoc: dr_name is rename, dr_filename is replace, but no metadata can be stored
                $sfile = $this->GetSFile();
                $sfileFolder = $this->GetSFileFolder();
                $sfileObj = $this->GetSFileObj();
//validate permclass of updated file
//can't rename a folder containing non-perm files
                $sNewName = "";
                if( $sfileFolder && !$sfile ) {
                    // it's a folder
                    if( @$raParms['dr_name'] ) {
                        $sNewName = $this->oDocRepDB->MoveSFileFolder( $sfileFolder, $raParms['dr_name'] );  // might return a uniquefied name
                    }
                } else if( $sfile && !$sfileObj ) {
                    // it's an orphaned file
                    if( @$raParms['dr_filename'] ) {
                        $bHandled = $this->oDocRepDB->ReplaceSFileOrphan( $sfile, $raParms['dr_filename'] );
                    }
                    if( @$raParms['dr_name'] ) {
                        $sNewName = $this->oDocRepDB->MoveSFileOrphan( $sfile, $raParms['dr_name'] );  // might return a uniquefied name
                    }
                } else if( $sfileObj ) {
                    // So this is a legitimate sfile.
                    if( @$raParms['dr_filename'] ) {
                        $bHandled = $sfileObj->ReplaceSFile( $raParms['dr_filename'], $raParms );
                    }
                    if( @$raParms['dr_name'] ) {
                        $sNewName = $sfileObj->RenameSFile( $raParms['dr_name'], $raParms );  // might return a uniquefied name
                    }
                }
                if( $sNewName ) {
                    $bHandled = true;
                    $this->SetSFile( $sNewName );
                }
                break;

            case 'trash_sfile':
                $sfile = $this->GetSFile();
                $sfileFolder = $this->GetSFileFolder();
                $sfileObj = $this->GetSFileObj();
//validate permclass of deleted file
//can't delete a folder containing non-perm files
                if( $sfileObj ) {
                    $bHandled = $sfileObj->TrashSFile();
                } else if( $sfile ) {
                    // sfile with no oDoc -- it is not possible to put this in the trash, so it's a permanent delete
                  //  $bHandled = $this->oDocRepDB->DeleteSFileOrphan( $sfile );
                } else if( $sfileFolder ) {
                    // delete a whole folder
                  //  $bHandled = $this->oDocRepDB->DeleteSFileFolder( $sfileFolder );
                }
                if( $bHandled ) {
                    // Set the current sfile somewhere!
                }
                break;

            case 'undelete_sfile':
                if( ($sfileObj = $this->GetSFileObj()) ) {
                    $bHandled = $sfileObj->UndeleteSFile();
                }
                break;
            case 'purge_deleted_sfile':
                if( ($sfileObj = $this->GetSFileObj()) ) {
                    $bHandled = $sfileObj->PurgeForever();
                }
                break;

            case 'approve_sfile_folder':
                // Publish all files within the current folder.
                // If they do not have P perms, skip them.
                if( ($sfileFolder = $this->GetSFileFolder()) && !$this->GetSFile() ) {
                    $ra = $this->oDocRepDB->ListSFileFlat( $sfileFolder );
                    foreach( $ra as $sfile => $oSFile ) {
                        if( $this->isPermAllowed( $oSFile, "P" ) ) {
                            $oSFile->SetVersionFlag( "", "PUB" );
                        }
                    }
                }
                break;
        }

        /* Actions that need a current doc that is writable by the current user
         */
        if( ($kDoc = $this->GetDocKey()) && $this->PermWrite() ) {
            switch( $sAction ) {
                case 'insert_folder':
                case 'update_folder':
                    $bInsert = ($sAction == 'insert_folder');
                    $k = $this->oDocRepDB->InsertFolder( $bInsert ? 0 : $kDoc, $raParms );
                    $bHandled = ($k != 0);
                    $kDocInserted = $bInsert ? $k : 0;
                    break;

                case 'insert_file':
                case 'update_file':
                    /* Parms:
                     *     sFilename - the local filename
                     */
                    $bInsert = ($sAction == 'insert_file');
                    $k = $this->oDocRepDB->InsertFile( $bInsert ? 0 : $kDoc, "DOC", $raParms['sFilename'], $raParms );
                    $bHandled = ($k != 0);
                    $kDocInserted = $bInsert ? $k : 0;
                    break;

                case 'insert_text':
                case 'update_text':
                    /* Parms:
                     *     sText
                     */
                    $bInsert = ($sAction == 'insert_text');
                    $k = $this->oDocRepDB->InsertText( $bInsert ? 0 : $kDoc, "TEXT", $raParms['sText'], $raParms );
                    $bHandled = ($k != 0);
                    $kDocInserted = $bInsert ? $k : 0;
                    break;

                case 'rename':
                    /* Change the name, maxVer title, and other metadata fields, do not create a new version.
                     * Parms are all optional, and only those specified will be changed:
                     *     dr_title, dr_name, dr_permclass, dr_desc, dr_spec, dr_verspec (for maxVer)
                     * Also control parm:
                     *     bRenameDescendants
                     */
                    $bHandled = $this->oDocRepDB->Rename( $kDoc, $raParms, "", array('bRenameDescendants'=>@$raParms['bRenameDescendants']) );
                    break;

                case 'move_up':     $bHandled = $this->oDocRepDB->MoveUp( $kDoc );     break;
                case 'move_down':   $bHandled = $this->oDocRepDB->MoveDown( $kDoc );   break;
                case 'move_left':   $bHandled = $this->oDocRepDB->MoveLeft( $kDoc );   break;
                case 'move_right':  $bHandled = $this->oDocRepDB->MoveRight( $kDoc );  break;

                case 'trash':       $bHandled = $this->oDocRepDB->TrashDoc( $kDoc );   break;

                case 'update_vars':
                    /* Replace the metadata array
                     * Parms: dr_metadata
                     */
                    $bHandled = $this->oDocRepDB->MetadataUpdate( $kDoc, $raParms );
                    break;

                case 'ver_dxd_flag_update':
                    /* Set the given flags in dxd.
                     * A doc_data can have multiple flags, but flags are unique per doc.
                     * Parms: dr_dxdflags = array('flag1'=>k1, 'flag2'=>0)  :  remove any existing flag1 and flag2, insert new flag1 (k is fk_doc_data)
                     */
                    $bHandled = $this->oDocRepDB->VersionChangeDXD( $kDoc, $raParms['dr_dxdflags'] );
                    break;

                case 'ver_delete':
                    /* Delete the given docdata record(s)
                     * Parms: dr_verdelete = array( kDocData1, kDocData2, ...)
                     */
                    foreach( $raParms['dr_verdelete'] as $k ) {
                        $this->oDocRepDB->VersionDelete( $k );
                    }
                    $bHandled = true;
                    break;
            }
        }

        /* Actions that need a current doc that is approvable by the current user
         */
        if( ($kDoc = $this->GetDocKey()) && $this->PermApprove() ) {
            if( $sAction == 'approve' ) {
                // Set the dxd flag 'PUB' on the maxVer data record
                // The current DocMgr doc is joined with the maxVer, so the current data_key is the maxVer key
                $this->oDocRepDB->VersionSetDXDFlag( $kDoc, $this->GetDocValue('data_key'), "PUB" );
                $bHandled = true; // reload the current doc
            }
        }

        if( $bHandled && $kDoc ) {
            // Reload the current document
            $this->SetDocKey( $kDoc );
        }

        if( $this->oDocRepDB->sErrMsg ) {
            $this->SetErrorMsg( $this->oDocRepDB->sErrMsg );
            $this->oDocRepDB->sErrMsg = "";
        }

        return( $bHandled ? ($kDocInserted ? $kDocInserted : $kDoc) : 0 );
    }



    function GetPermClasses( $mode )
    /* OVERRIDE BY DERIVED CLASS
     *
     * Return array of integers: permclasses that the current user can access by given mode.
     */
    {
        return( array(0) );
    }
}

?>