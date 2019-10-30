<?php
/*
 * docUtil
 *
 * Copyright 2009-2019 Seeds of Diversity Canada
 *
 * Functions to simplify DocRep applications
 */
include_once( SEEDCORE."SEEDPerms.php" );
include_once( STDINC."SEEDSession.php" );
include_once( STDINC."DocRep/DocRepDB.php" );
include_once( STDINC."DocRep/DocRepAppCommon.php" );  // DocRep_GetDocGPC
include_once( STDINC."DocRep/DocRep.php" );           // DocRepTextTypes
include_once( STDINC."DocRep/DocRepWiki.php" );
include_once( SEEDCOMMON."siteTemplate.php" );
include_once( SEEDCORE."SEEDTag.php" );                 // SEEDTagBasicHandler


// replace this with a SEEDSessionPerms function that takes the DOCREP_SEEDPERMS_APP as an argument
function New_DocRepSEEDPermsFromUID( SEEDAppDB $oApp, $uid )
{
// kluge: $uid should never be 0 but if it is we undoubtedly mean the anonymous user
if( !$uid )  $uid = -1;

    SEEDSessionAuthStatic::Init($oApp->kfdb, 0);
    $raGroups = SEEDSessionAuthStatic::GetGroupsFromUserKey( $uid, false );
    return( new SEEDPermsTest( $oApp, DOCREP_SEEDPERMS_APP, array($uid), $raGroups ) );
}

function New_DocRepDB_WithMyPerms( $kfdb, $uid, $raParms = array() )
{
    $bReadonly = isset($raParms['bReadonly']) ? $raParms['bReadonly'] : false;

// only needed until this function takes oApp; otherwise we can't tell which db to use
if( ($db = @$raParms['db']) ) {
    global $config_KFDB;
    $oApp = new SEEDAppDB( $config_KFDB[$db] );
} else {
    $oApp = New_SiteAppDB();
}

    $parms = array();
    $oPerms = New_DocRepSEEDPermsFromUID( $oApp, $uid );
    $parms['raPermClassesR'] = $oPerms->GetClassesAllowed( "R", false );
    if( !$bReadonly ) {
        $parms['raPermClassesW'] = $oPerms->GetClassesAllowed( "W", false );
    }
    $oDocRepDB = new DocRepDB( $kfdb, $uid, $parms );
    return( $oDocRepDB );
}

class DocRepWiki_Site extends DocRepWiki
/***************************************
    A DocRepWiki that creates the right absolute links and img references for the given site "public" or "office"
 */
{
    function __construct( $eDB, &$oDocRepDB, $sFlag, $raDRWParms = array() )
    /***********************************************************************
        eDB:        public | office
        oDocRepDB:  seeds1 if public, seeds2 if office
        sFlag:      the doc_x_docdata flag
     */
    {
        $raParms = $raDRWParms; // copy to not overwrite caller's array

        // Depending on the situation, serve local links and images from:
        //     /seeds.ca/int/doc/doc.php
        //     /seeds.ca/int/doc/docpub.php
        //     /office/int/doc/doc.php
        //     /office/int/doc/docpub.php
        // (these last four must be stated independently of the browser address because some apps e.g. mbr_mail
        //  can refer to links & imgs on other servers and other ssl states)
        //     https://office.seeds.ca/int/doc/doc.php
        //     http://office.seeds.ca/int/doc/docpub.php
        //     https://www.seeds.ca/int/doc/doc.php
        //     http://www.seeds.ca/int/doc/docpub.php

        $s1 = ($sFlag == 'PUB' ? "docpub.php" : "doc.php");
        if( STD_isLocal ) {
            $sServe = SITEROOT.($eDB=='office' ? "office/" : "")."d/$s1";
        } else {
            $sServe = 'https://www.seeds.ca/'.($eDB=='office' ? "office/" : "")."d/$s1";
        }

        if( !@$raParms['php_serve_link'] ) $raParms['php_serve_link'] = $sServe;
        if( !@$raParms['php_serve_img'] )  $raParms['php_serve_img'] = $sServe;

        parent::__construct( $oDocRepDB, $sFlag, $raParms );
    }
}

function DocServeDoc( $kfdb, $sess, $flag )
/******************************************
    Serve a document to a logged-in user.  This is not for public doc serving: use DocWebsite or DocServeDocPub for that

    TODO: code can probably be shared for public and non-public, but the problem at the time was
          that IE got confused by session_start, so the serve code was forked.  Does DocRepWebsite
          create a session?   A - no it doesn't, if not in test mode
 */
{
    /* session_cache_limit("public") can also be used before session_start (the default is nocache)
     */

    // session_start() sets the following headers, which cause IE to never write the downloaded file directly to disk, ever,
    //                 even if it needs to do that to hand the file off to an attachment handler.
    // Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0
    // Pragma: no-cache
    //
    // These headers might fix that
    // ***** Must be after the session_start - do that first!
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Pragma: public");
    //
    // or session_cache_limit() might be the right solution

    serveDoc( $kfdb, $sess->GetUID(), $flag );
}


function DocServeDocPub( $kfdb )
/*******************************
    Serve a document to any user.  No login required.  Only documents with permclass readable by user 0.

    Issues: session_start is not generally called before this, but it is before DocServeDoc.  Don't know the effect.

 */
{
    serveDoc( $kfdb, 0, 'PUB' );
}


function serveDoc( $kfdb, $uid, $flag )
/**************************************
 */
{
    $kDoc = DocRep_GetDocGPC( '', DOCREP_KEY_HASH_SEED, $kfdb ) or die( "No document specified" );

    $oDocRepDB = New_DocRepDB_WithMyPerms( $kfdb, $uid, array('bReadonly'=>true) );

    $oDoc = new DocRepDoc( $oDocRepDB, $kDoc );
    if( $oDoc->GetType() == 'TEXT' ) {
        // Serve a text file by possibly expanding tags, variables, and a template

        list($kTemplate, $raDRVars)
            = DocRepApp_GetTemplateAndVars( $oDocRepDB, $kDoc, $flag, array()/*dr_vars*/ );

        $eTextType = DocRepTextTypes::GetFromTagStr( $oDoc->GetVerspec($flag) );
        switch( $eTextType ) {
//            case 'TEXTTYPE_HTML':
            case 'TEXTTYPE_PLAIN':
            default:                                    // unknown types default to plain
                $sDoc = $oDoc->GetText($flag);
                break;
            case 'TEXTTYPE_HTML_SOD':
            case 'TEXTTYPE_PLAIN_SOD':
                $lang = SEEDCore_ArraySmartVal( $raDRVars, 'lang', array('EN','FR') );
                $vars = array_merge( $raDRVars, array( 'uid' => $uid ) );

                $oMTmpl = new MasterTemplate( $kfdb, $uid, $lang, array( ) );
                $sDoc = $oMTmpl->GetTmpl()->ExpandStr( $oDoc->GetText($flag), $vars );
                break;

    case 'TEXTTYPE_HTML':
            case 'TEXTTYPE_WIKI':
            case 'TEXTTYPE_WIKILINK':
                $sDoc = siteTemplateGo( $oDocRepDB, $flag, $raDRVars, $kDoc, $kTemplate );
                break;
        }

        // There could be an oDoc metadata specifying utf8 to override this
        header( "Content-Type: text/html; charset=cp1252" );
        echo $sDoc;
    } else {
        // Serve a binary file
        $oDocRepDB->ServeDoc( $kDoc, $flag );
    }
}

?>
