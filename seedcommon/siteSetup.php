<?php

/* siteSetup.php
 *
 * Copyright (c) 2009-2014 Seeds of Diversity Canada
 *
 * Set up the common elements of a Seeds of Diversity site.
 *
 * Test for databases, filesystems, etc.  Create them if directed.
 *
 * To set up a site:
 *  - get the source
 *  - create the db def file (not versioned: copy from seeds_def_sample.php)
 *  - create databases
 *  - create tables
 *  - populate tables
 *  - create docrep_upload directory
 *  - create seeds_log directory
 *  - copy images, javascript that need to be under the docroot
 *  - set up site-specific elements

 * Usage:
 *  - include the local site.php
 *  - include this
 *  - call SiteSetup() with no parms
 */

require_once( STDINC."KeyFrame/KFDB.php" );
require_once( SEEDCOMMON."console/console01.php" );
require_once( STDINC."SEEDSetup.php");
require_once( STDINC."SEEDSession.php" );       // SEEDSession_Setup()
require_once( SEEDCORE."SEEDPerms.php" );         // SEEDPerms_Setup()
require_once( STDINC."SEEDMetaTable.php" );     // SEEDMetaTable_Setup()
require_once( STDINC."DocRep/DocRepDB.php" );   // DocRep_Setup()


$raSites = array( "seeds"      => array( "uid" => "seeds1",             // user code for this site (must be the same as the main db name below, compared in code)
                                         "raDbid" => array("seeds1"),   // all databases used by this site
                                  ),
                  "office"     => array( "uid" => "seeds2",
                                         "raDbid" => array("seeds1","seeds2"),
                                  ),
                  "pollinator" => array( "uid" => "seeds3",
                                         "raDbid" => array("seeds3"),
                                  ),
                  "generic"    => array( "uid" => "generic",
                                         "raDbid" => array("generic") )
                );


function SiteSetup( $sSite, $fnSetupMore = NULL )
/************************************************
    Use SiteSetupClass to set up the given site.
    If the base setup succeeds, call fnSetupMore to set up site-specific stuff.

    http bGo
        false: test the setup and report if anything is missing
        true:  test the setup and install anything missing
 */
{
    global $raSites;

    if( !array_key_exists( $sSite, $raSites ) )  die( "Unknown setup '$sSite'" );

    $bGo = SEEDSafeGPC_GetInt("bGo");

    $oSetup = new SiteSetupClass();
    $bOk = $oSetup->SiteSetup_Setup( $sSite, $bGo );

    $s = "<h2>Seeds of Diversity Setup : $sSite</h2>"
        ."<div style='border:1px solid #aaa;border-radius:5px;padding:10px;width:70%;float:left'>"
        ."<h3>".($bGo ? "Installing" : "Testing")."</h3>"
        .$oSetup->sOut
        ."</div>";
    if( !$bGo ) {
        $s .= "<div style='float:left;margin-left:20px'>"
             ."<form action='${_SERVER['PHP_SELF']}'><input type='hidden' name='bGo' value='1'/>"
             ."<input type='submit' value='Setup $sSite site'/></form>"
             ."</div>";
    }

    echo Console01Static::HTMLPage( $s, "", "EN", array( 'bBootstrap' => true,
                                                         'bBodyMargin' => true ) );
}


class SiteSetupClass {

    public $sOut = "";
    private $bGo = false;
    private $oSetup = NULL;


    function __construct() {}

    function SiteSetup_Setup( $sSite, $bGo )
    /***************************************
        Return true if everything is installed, false if something is missing or installation fails

        bGo==false : test the setup
        bGo==true  : install anything missing

        $this->sOut contains the readable results
     */
    {
        global $raSites;

        $this->bGo = $bGo;

        umask(0);  // this allows directories to be created with our preferred permissions

        $this->sOut .= "<STYLE>p {font-family:sans serif;font-size:10pt;}</STYLE>";

        $ra = $raSites[$sSite];
        if( !is_array($ra) )  die( "SiteSetup: Invalid site name" );

        /* Test for db def file.
         */
        if( !defined("SITE_DB_DEF_FILE") ) {
            $this->s_error( "SITE_DB_DEF_FILE is not defined.  This probably means site.php is not being included or it is not correct." );
            return( false );
        }
        $fnameDef = STD_SCRIPT_REALDIR.SITE_DB_DEF_FILE;    // site.php defines SITE_DB_DEF_FILE relative to the main script file
        if( file_exists( $fnameDef ) ) {
            $this->s_okay( "SITE_DB_DEF_FILE exists: $fnameDef" );
        } else {
            $fnameSample = SEEDSX_ROOT_REALDIR."seeds_def_sample.php";
            //echo "Sample file: $fnameSample<BR/>Def file: $fnameDef<BR/>";

            $s = "SITE_DB_DEF_FILE did not exist: '".SITE_DB_DEF_FILE."'. ";
            if( $bGo ) {
                if( copy( $fnameSample, $fnameDef ) ) {
                    $s .= "Successfully created $fnameDef -- you must edit it now to contain your db parms.";
                } else {
                    $s .= "Tried to copy it from $fnameSample but the copy failed! Make sure the web server has write permission on the SEEDSX_ROOT directory.";
                }
            }
            $this->s_error( $s );
            return( false );
        }

        /* Test db connection(s)
         */
        $kfdb = NULL;
        switch( $ra['uid'] ) {
            case 'seeds1':
                $h = SiteKFDB_HOST_seeds1;
                $u = SiteKFDB_USERID_seeds1;
                $p = SiteKFDB_PASSWORD_seeds1;
                break;
            case 'seeds2':
                $h = SiteKFDB_HOST_seeds2;
                $u = SiteKFDB_USERID_seeds2;
                $p = SiteKFDB_PASSWORD_seeds2;
                break;
            case 'seeds3':
                $h = SiteKFDB_HOST_seeds3;
                $u = SiteKFDB_USERID_seeds3;
                $p = SiteKFDB_PASSWORD_seeds3;
                break;
            case 'generic':
                $h = SiteKFDB_HOST;
                $u = SiteKFDB_USERID;
                $p = SiteKFDB_PASSWORD;
                break;
            default:
                $this->s_error( "Unknown uid '{$ra['uid']}'" );
        }
        foreach( $ra['raDbid'] as $dbid ) {
            switch( $dbid ) {
                case 'seeds1':    $d = SiteKFDB_DB_seeds1;    break;
                case 'seeds2':    $d = SiteKFDB_DB_seeds2;    break;
                case 'seeds3':    $d = SiteKFDB_DB_seeds3;    break;
                case 'generic':   $d = SiteKFDB_DB;           break;
                default:
                    $this->s_error( "Unknown dbid '$dbid'" );
                    return( false );
            }
            $kfdb1 = new KeyFrameDB( $h, $u , $p );
            if( $kfdb1->Connect( $d ) ) {
                $this->s_okay("DB connection '$dbid' exists");
            } else {
                $this->s_error("DB connection $dbid does not exist.  Login to your database client as an administrator, and use these commands:<BR/>"
                              ."CREATE DATABASE $d;<BR/>"
                              ."GRANT ALL ON $d.* TO '$u'@'localhost' IDENTIFIED BY 'seeds'"
                              ."&nbsp&nbsp<FONT color='black'>&lt - - substitute your password as you entered it in ".SITE_DB_DEF_FILE."</FONT> ");
                $kfdb1 = null;
            }
            if( $dbid == $ra['uid'] )  $kfdb = $kfdb1;
        }

        if( !$kfdb ) {
            $this->s_error( "Default database connection was not established" );
            return( false );
        }

        $this->oSetup = new SEEDSetup( $kfdb );

        /* Setup common modules
         */
        $this->moduleSetup( "SEEDSession",   "SEEDSession_Setup" );
        $this->moduleSetup( "SEEDPerms",     "SEEDPerms_Setup" );
        $this->moduleSetup( "SEEDMetaTable", "SEEDMetaTable_Setup" );
        $this->moduleSetup( "SEEDLocal",     "SEEDLocal_Setup" );
        //$this->moduleSetup( "DocRep",        "DocRep_Setup" );
        DRSetup( $kfdb );

        /* Setup seeds_log directory
         */
        $this->dir_create( "SITE_LOG_ROOT" );

        /* Setup DocRep upload directory   perms 703 rwx----wx
         *
         * Installations where Apache runs as the directory owner can use 703
         * Some development installations should use 707 to allow read access by DocRep File UI.
         */
        $this->dir_create( "DOCREP_UPLOAD_DIR" );
        $this->dir_create( NULL, DOCREP_UPLOAD_DIR."sfile" );

        /* Setup stdimg directory
         */
$this->sOut .= "<P>Be sure to cp -R {W_ROOT} ~/public_html/w  -- production servers only</P>";


        /* Site-specific setup
         */
        if( $sSite == "seeds" ) {
            $s = "";

            // Set up the mbr_order_pending table
            require(SEEDCOMMON."mbr/mbrOrder.php");    // MbrOrder_Setup
            $this->moduleSetup( "Mbr Order", "MbrOrder_Setup" );

            // Set up the ev_events table
            require(SEEDCOMMON."ev/_ev.php");    // Events_Setup
            $this->moduleSetup( "Events", "Events_Setup" );

            // Set up the sl_sources* tables
            require(SEEDCOMMON."sl/sl_sources_common.php");    // SLSources_Setup
            $this->moduleSetup( "SL Sources", "SLSources_Setup" );


            // other tables that should be created, tested, or mentioned
            $s .= $this->tableTest( "bull_list", "don't worry too much about this" )
//            $this->tableTest( "csci_company" );
//            $this->tableTest( "csci_seeds" );
//            $this->tableTest( "csci_seeds_archive" );
                 .$this->tableTest( "doclib_document", "don't worry about this" )
                 .$this->tableTest( "mbr_sites", "you should get the SLDesc package or ignore this" )
                 .$this->tableTest( "pollcan_users", "you should get the PollCan package or ignore this" )
                 .$this->tableTest( "pollcan_sites", "you should get the PollCan package or ignore this" )
                 .$this->tableTest( "pollcan_visits", "you should get the PollCan package or ignore this" )
                 .$this->tableTest( "pollcan_flowers", "you should get the PollCan package or ignore this" )
                 .$this->tableTest( "pollcan_insects", "you should get the PollCan package or ignore this" )
                 .$this->tableTest( "pollcan_insectsxflowers", "you should get the PollCan package or ignore this" )
//            $this->tableTest( "rl_companies" );
                 .$this->tableTest( "sed_curr_seeds", "you should get the MSD package or ignore this" )
                 .$this->tableTest( "sed_curr_growers", "you should get the MSD package or ignore this" )
                 .$this->tableTest( "sed_seeds", "you should get the MSD package or ignore this" )
                 .$this->tableTest( "sed_growers", "you should get the MSD package or ignore this" )
                 .$this->tableTest( "sl_accession", "you should get the Seed Library package or ignore this" )
                 .$this->tableTest( "sl_adoption", "you should get the Seed Library package or ignore this" )
                 .$this->tableTest( "sl_pcv", "you should get the Seed Library package or ignore this" )
                 .$this->tableTest( "sl_species", "you should get the Seed Library package or ignore this" )
                 .$this->tableTest( "sl_germ", "you should get the Seed Library package or ignore this" )
                 .$this->tableTest( "sl_varinst", "you should get the SLDesc package or ignore this" )
                 .$this->tableTest( "sl_desc_obs", "you should get the SLDesc package or ignore this" );

            echo $s;
        }

        if( $sSite == "office" ) {
            /* Create upload/download directories
             */
            $this->dir_create( NULL, SEEDSX_ROOT."mbrmdb" );
            $this->dir_create( NULL, SEEDSX_ROOT."sl_download" );
            $this->dir_create( NULL, SEEDSX_ROOT."sl_download/npgs" );

            $s = "";

            // Setup mbr_contacts
            require( SITEROOT."office/mbr/_mbr.php" );    // MbrContacts_Setup
            $this->moduleSetup( "Office MbrContacts", "MbrContacts_Setup" );

            // Setup mbr_mail_*
            require( SITEROOT."office/mbr/_mbr_mail.php" ); // MbrMail_Setup
            $this->moduleSetup( "Office MbrMail", "MbrMail_Setup" );

            // Setup Task Manager
//            require( SITEROOT."int/taskmanager.share.php" );    // Tasks_Setup
//            $this->moduleSetup( "Tasks", "Tasks_Setup" );

            // Setup Pay Tracker
//            require( SITEROOT."int/pay/_pay.php" );    // Pay_Setup
//            $this->moduleSetup( "Pay", "Pay_Setup" );

            // other tables that should be created, tested, or mentioned
//            $this->tableTest( "gcgc_growers" );
//            $this->tableTest( "gcgc_varieties" );
//            $this->tableTest( "gcgc_gxv" );
//            $this->tableTest( "mbr_donations", "This has not been implemented." );
            $this->tableTest( "mbr_tmp_mdb_upload", "This is normally created from the extracted schema in mbrmdb." );

            echo $s;
        }


        /* Unzip TinyMCE
         */
        if( !defined("TINYMCE_DIR") )      $this->s_error( "SiteSetup: TINYMCE_DIR is not defined." );
        if( !file_exists( TINYMCE_DIR ) )  $this->s_error( "SiteSetup: TINYMCE_DIR directory '".TINYMCE_DIR."' does not exist.  Unzip TinyMCE to this directory and make sure it's world-readable." );
        $this->s_okay( "TinyMCE installed" );


        $this->s_okay( "<BR/>" );
        $this->s_okay( "All set up!" );
        return( true );
    }

    function moduleSetup( $label, $fnSetup )
    {
        $sReport = "";
        if( $fnSetup( $this->oSetup, $sReport, false ) ) {
            $this->s_okay($sReport, "$label is okay");
        } else if( $this->bGo ) {
            if( ($r = $fnSetup( $this->oSetup, $sReport, true )) ) {
                $this->s_okay($sReport, "Created $label module");
            } else {
                $this->s_error($sReport, $r );
            }
        } else {
            $this->s_error($sReport, "$label is not installed" );
        }
    }

    function tableTest( $table, $comment = "" )
    {
        $this->oSetup->kfdb->TableExists( $table )
            ? $this->s_okay( "$table exists" )
            : $this->s_error( "$table does not exist".($comment ? "- $comment" : "") );
    }

    function dir_create( $defDir, $dir = "" )  // arg1 is the name of a defined constant, if that's NULL then arg2 is a dir relative to SCRIPT_FILENAME
    {
        if( $defDir !== NULL ) {
            if( !defined($defDir) )      $this->s_error( "SiteSetup: $defDir is not defined.  This probably means site.php is not being included or it is not correct." );
            $dir = constant($defDir);
        } else {
            $defDir = "[extra dir]";  // just make the messages look okay
        }

        $dirpath = dirname($_SERVER['SCRIPT_FILENAME'])."/$dir";
        if( file_exists($dirpath) ) {
            if( /*THIS IS NOT WINDOWS AND */ ($nPerm = fileperms($dirpath)) != 040703 ) {  // mode is an octal number (leading zero)
                $this->s_error( "Directory $dirpath exists but permissions are ".sprintf("%o",$nPerm)." instead of 703" );
            }
            $this->s_okay( "$defDir exists: ".realpath($dirpath) );
        } else if( $this->bGo ) {
            if( mkdir( $dirpath, 0703 ) ) {  // mode is an octal number (leading zero)
                $this->s_okay( "Created $defDir: ".realpath($dirpath) );
            } else {
                $this->s_error( "SiteSetup: Could not create $defDir directory '$dirpath' does not exist.  Create with perms 703 rwx----wx" );
            }
        } else {
            $this->s_error( "$defDir { $dirpath } does not exist" );
        }
    }

    function do_sys( $cmd )         { $iRet = 0; $sRet = system($cmd, /* & */$iRet);  $this->sOut .= "<p>$cmd ; <i>Returned $iRet: $sRet</i></p>"; }
    function s_okay( $s1, $s2="" )  {  $this->sOut .= "<p style='color:green'>".nl2br($s1)."</p>".($s2 ? "<p style='color:green'>".nl2br($s2)."</p>" : ""); }
    function s_error( $s1, $s2="" ) {  $this->sOut .= "<p style='color:red'>".nl2br($s1)."</p>".  ($s2 ? "<p style='color:red'>".nl2br($s2)."</p>" : ""); }
}

?>
