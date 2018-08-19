<?php

/* docWebsite
 *
 * Copyright (c) 2008-2012 Seeds of Diversity Canada
 *
 * Serve one web resource from the DocRep repository to an anonymous user.
 * Several web resources make a web page, and we tend to format our pages so resources are all fetched from here.
 *
 * Usage:
 *  include( "site.php" );
 *  include( "docWebsite.php" );
 *  $parms = array( "docid_home"       =>     home page of the web site
 *                  "docid_root"       =>     root folder of the web site (contains viewable pages)
 *
 *                  // optional
 *                  "docid"            =>     current resource, if blank get from REQUEST
 *                  "docid_template"   =>     use the given template, if blank use vars['docid_template'], if blank just output the doc
 *                  "docid_extroots"   =>     array( other viewable trees, ... )
 *                  "lang"             =>     if blank, various smart methods are used
 *                  "vars"             =>     array of global vars for web site, overriden by vars stored with the doc or its ancestors
 *                  "bDebug"           =>     true: echo a bunch of debug info
 *                );
 *  $o = new DocWebsite( $parms );
 *  $o->Go();
 *
 * Anonymous read access:
 *     SEEDPerms knows nothing about anonymous users. That is to be handled above the std level.
 *     At the seedcommon level we reserve the user_id -1 for the anonymous user. If no one is logged in, then permsclass is
 *     retrieved using -1 as the user_id.
 *     By seedcommon convention, apps built on seedcommon/doc never have a SEEDSession_User uid=-1 and cannot login as that user.
 */

include_once( SEEDCOMMON."siteStart.php" );
include_once( STDINC."DocRep/DocRepWebsite.php" );
include_once( STDINC."SEEDSession.php" );
include_once( SEEDCOMMON."doc/docUtil.php" );


class DocWebsite extends DocRepWebsite {
    // public
    //var $kfdb;    DocRepWebsite stores this and intends for it to be available to derived classes
    var $sess;
    var $bTestMaxver = false;


    function DocWebsite( $parms = array() )
    /**************************************
     */
    {
        if( defined("DOCWEBSITE_TEST_MAXVER") || SEEDSafeGPC_GetInt('test')==1 ) {
            $this->bTestMaxver = true;

            list($kfdb, $this->sess) = SiteStartSessionAccount( array("DocRepMgr"=>"R") );
            $uid = $this->sess->GetUID();
        } else {
            list($kfdb) = SiteStart();
            $uid = -1;   // the anonymous user
        }

        if( !@$parms['docid_root'] || !@$parms['docid_home'] )  die( "Missing docid parms on DocWebsite" );

        if( !in_array( @$parms['lang'], array("EN","FR") ) ) {
            $parms['lang'] = site_define_lang();
        }

        $this->DocRepWebsite( $kfdb,
                              array( "dr_flag"        => ($this->bTestMaxver ? "" : "PUB"),
                                     "uid"            => $uid,
                                     "lang"           => $parms['lang'],
                                     "docid"          => @$parms['docid'],
                                     "docid_home"     => @$parms['docid_home'],
                                     "docid_root"     => @$parms['docid_root'],
                                     "docid_extroots" => @$parms['docid_extroots'],
                                     "docid_template" => @$parms['docid_template'],
                                     "vars"           => @$parms['vars'],
                                     "bDirHierarchy"  => (@$parms['bDirHierarchy']==true),
                                     "bDebug"         => (@$_REQUEST['bDebug']==1),
                                   ) );
    }

    function Go2()
    {
        // Get raPermsClassesR
        $uid = @$this->raParms['uid'] ? $this->raParms['uid'] : -1;  // -1 is the anonymous user

        $oPerms = New_DocRepSEEDPermsFromUID( $this->kfdb, $uid );
        $this->raParms['raPermClassesR'] = $oPerms->GetClassesAllowed( "R", false );

        $ret = $this->Main();    // true: served IMAGE/DOC, false: error, else $ret = page text
        return( $ret );
    }


    function Go()
    /************
        Set $this->raParms as needed
        Call Main() to do the work
     */
    {
        $ret = $this->Go2();
        if( $ret !== true && $ret !== false ) {
            echo ($this->bTestMaxver ? "<DIV style='color:red;width:100%;border-bottom:solid thin red'>DEVELOPMENT VERSION</DIV>" : "")
                .$ret
                .($this->bTestMaxver ? "<DIV style='color:red;width:100%;border-top:solid thin red'>DEVELOPMENT VERSION</DIV>" : "");
        }
    }

    function factory_DocRepWiki( $raDRWparms )
    /*****************************************
        This is called by DocRepWebsite to create the DocRepWiki that renders pages.
        We override it here to set the DocRepWiki parms, though this could also be done by passing those parms to DocRepWebsite somehow.

        The disadvantage of overriding is that we might miss improvements to DocRepWebsite.
     */
    {
        $flag = ($this->bTestMaxver ? "" : "PUB");  // this is handled properly by the base class factory_DocRepWiki, so it's redundant

        if( $this->bTestMaxver ) {    // this is the part that's different (the base class calls this method with the right parms for public viewing)
            $raDRWparms['php_serve_link_parmstr'] = "test=1";
            $raDRWparms['php_serve_img_parmstr']  = "test=1";
        }
        $oDocRepWiki = new DocRepWiki( $this->oDocRepDB, $flag, $raDRWparms );
        return( $oDocRepWiki );
    }
}

?>
