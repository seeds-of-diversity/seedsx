<?php

/* DocRepWebsite
 *
 * Copyright (c) 2007-2018 Seeds of Diversity Canada
 *
 * Serve a website contained in a DocRepository.
 */

include_once( "DocRepAppCommon.php" );      // DocRep_GetDocGPC()
include_once( "DocRepDB.php" );
include_once( "DocRepWiki.php" );
//include_once( SEEDCORE."SEEDPerms.php" );


class DocRepWebsite
/******************
    Use DocRepWiki to draw a web page.

    If the page is text, it consists of a document (with metadata) plus a template. The doc is loaded, and the template is processed.
    The template typically references doc and metadata content, which is taken from the current document.

    parms:  dr_flag    => ""=maxVer, "PUB", etc (default = PUB)
            uid        => the uid for permclass access (there is no default for anonymous readers: the caller must define a non-zero anonymous user)
            lang       => default = EN
            template   => name or key of the page template
            docid_home => default docid if that is unspecified or if a sneaky user tries to access a hidden or out-of-root page
            docid_root => name or key of folder containing the web site.  Access disallowed outside of this tree, and to the root too.
            docid_extroots => array( names/keys of folder(s) where access is also permitted )  e.g. image folders
            docid      => name or key of the page to draw (optional)
            vars       => array( k => v, ... )
            raPermClassesR => array( permclasses of visible documents )
            bDebug     => true: echo a bunch of debug information

    In practice, the docid is specified by http parms as the web site is traversed.
    The lang is specified for various language features of DocRepWiki, but the client must specify appropriate values of
    docid_home, docid_root, and possibly vars based on the language, especially if different language web sites are kept under different roots.

    Template is defined by var['dr_template'], or var['dr_template_override_vars']
        We look in the following places for these (in order):
            programmatic parms: docid_template_override_vars    (use this to hard-code a template that authors cannot override)
            doc metadata:       vars['dr_template']             (authors can override the hard-coded default)
            programmatic parms: vars['dr_template']             (use this to hard-code a default template that an author can override)

    How variables are handled at Include points:
        1) DocRepWiki [Include] inclusion adds scoped variables of the included doc, but not vars of the included doc's ancestors
        2) Template [Doc] includes the current doc at that point, but there is no scoped variable change at the inclusion point.
           Instead, all variables of the template and doc are merged before the template is processed, so the template is processed within
           the doc's variable space.
           Funny case:
           Docs [Included] by the template will scope-add variables to the combined doc/template var space. If [Doc] is included within one of
           those [Included] docs, the scope-added vars will apply to the doc, because [Doc] does not alter the variable space.
        This funny case could be fixed by treating a [Doc] exactly like an [Include] i.e. scoping the doc vars into the variable space at [Doc].
        It would be redundant 99.9% of the time, but it would do the right thing.  Although docs should put their vars into the template space,
        template vars should never override doc vars.  This would still leave the lesser funny case that doc vars would not override vars defined
        in the template's Include.


    General variable inheritance in order of overrides:
        1) programmatic $raParms['vars'] passed to this class

        2) vars defined in template's metadata (if template defined)

        **DocRepWiki overlays metadata when including docs
// Because this is a scoped-include that occurs after the doc and template vars are merged (which happens at the very beginning),
// this override actually happens at the bottom of this list (overrides all other vars)
        3) metadata values in docs included by the template (relevant if they in turn include the Doc)

        **If current doc is under docroot (instead of being in an extroot)
        4) metadata values in docroot folder
        5) metadata values in current doc's ancestors
        6) metadata values in current doc

        **DocRepWiki overlays metadata when including docs (not when incl the template)
        7) metadata values in included docs. Scope limited to included file.
        8) metadata values in nested includes.

        N.B. metadata is not fetched for ancestors of included files, nor for ancestors of requested docs extroots (i.e. not in the docroot)
 */
{
    // protected
    var $kfdb = NULL;       // derived classes may use this
    var $raParms = array();
    var $kDoc = 0;
    var $raDocAncestors = array();
    var $raDocInfo = array();
    var $kTemplate = 0;
    var $kHome = 0;
    var $kRoot = 0;

    // private
    var $oDocRepDB;


    function DocRepWebsite( &$kfdb, $raParms = array() )
    /***************************************************
     */
    {
        $this->kfdb = &$kfdb;
        $this->raParms = $raParms;

        if( !isset($raParms['uid']) )      die( 'DocRepWebsite: uid must be defined' );  // don't use 0 as the anonymous user, caller must define the anonymous user
        if( !isset($raParms['dr_flag']) )  $this->raParms['dr_flag'] = 'PUB';
        if( !isset($raParms['vars']) )     $this->raParms['vars'] = array();
        if( empty($raParms['lang']) )      $this->raParms['lang'] = 'EN';
    }

    function Main()
    /**************
        Return true:   served a binary doc to stdout
               false:  error
               string: text page content

     */
    {
        $this->Init();
        if( $this->BinaryServe() )  return( true );     // true: served a binary object
        return( $this->DrawPage() );                    // false: error;  else return text of page
    }

    function Init()
    /**************
        Validate input
        docid must be readable by the user
        docid must be under docid_root

        docid_home should also be readable and under the root.  In practice this parm is hard coded, so we don't check this.
        Clients that allow it to be specified by the user should validate it.
     */
    {
        $kDoc = 0;

        if( !isset($this->raParms['raPermClassesR']) ) die( 'DocRepWebsite: raPermClassesR must be defined' );
        if( !count($this->raParms['raPermClassesR']) ) die( 'DocRepWebsite: raPermClassesR has no contents' );

        $bDebug = (@$this->raParms['bDebug'] === true);

        $this->oDocRepDB = new DocRepDB( $this->kfdb, $this->raParms['uid'],      // uid is just a formality, since operations are read-only
                                         array("raPermClassesR" => @$this->raParms['raPermClassesR']) );

        // Get the home and root docs.  Important to use GetDocFromName, even for numeric ids, because it enforces accessibility.
        $this->kHome = $this->oDocRepDB->GetDocFromName( $this->raParms['docid_home'] );
        $this->kRoot = $this->oDocRepDB->GetDocFromName( $this->raParms['docid_root'] );

        if( $bDebug ) {
            echo "<H3>DocRepWebsite:raParms</H3>";
            var_dump( $this->raParms );
        }

        if( !$this->kHome || !$this->kRoot )  die( "docid_home and/or docid_root not specified by name or number, or not readable by user {$this->raParms['uid']}" );

        // Get all the trees within which the doc is allowed
        $raAllowedTrees = array( $this->kRoot );
        if( count(@$this->raParms['docid_extroots']) ) {
            foreach( $this->raParms['docid_extroots'] as $d ) {
                if( ($k = $this->oDocRepDB->GetDocFromName( $d )) ) {
                    $raAllowedTrees[] = $k;
                }
            }
        }

        if( $bDebug ) {
            echo "<H3>DocRepWebsite:raAllowedTrees</H3>";
            var_dump( $raAllowedTrees );
            echo "<H3>DocRepWebsite doc locate</H3>";
        }

        // Get the kDoc by various means, and the raDocAncestors
        // doc must be visible and under a valid root
        $sDoc = @$this->raParms['docid'];
        if( empty($sDoc) )  $sDoc = SEEDSafeGPC_GetStrPlain("n");
        if( empty($sDoc) )  $sDoc = SEEDSafeGPC_GetStrPlain("k");
        if( empty($sDoc) )  $sDoc = $this->kHome;
        if( $bDebug ) { echo "<P>document requested: $sDoc</P>"; }

        if( is_numeric( $sDoc ) ) {
            $kDoc = intval($sDoc);
            if( !$this->accesscheck( $kDoc, $raAllowedTrees, $bDebug ) )  $kDoc = 0;
        } else if( !@$this->raParms['bDirHierarchy'] ) {    // DEPRECATED
            $kDoc = $this->oDocRepDB->GetDocFromName( $sDoc );
            if( !$this->accesscheck( $kDoc, $raAllowedTrees, $bDebug ) )  $kDoc = 0;
        } else {
            // look for the named docid in the named doc_root (doc_root can be specified by a number, but it has to have a name)
            $d = $this->raParms['docid_root'];
            if( is_numeric( $d ) )  $d = $this->oDocRepDB->GetDocName( $d );
            if( $bDebug ) { echo "<P>docid_root expanded to '$d'</P>"; }

            $kDoc = $this->oDocRepDB->GetDocFromName( $d.'/'.$sDoc );
            if( $bDebug ) { echo "<P>docid_root/request expanded to '$d/$sDoc' = docid $kDoc</P>"; }

            if( !$this->accesscheck( $kDoc, $raAllowedTrees, $bDebug ) ) {
                // look for the named docid as a fully-normalized name
                $kDoc = $this->oDocRepDB->GetDocFromName( $sDoc );
                if( $bDebug ) { echo "<P>trying request with no docid_root prefix: '$sDoc' = docid $kDoc</P>"; }
                if( !$this->accesscheck( $kDoc, $raAllowedTrees, $bDebug ) ) {
                    $bFound = false;
                    // look for the named docid under extroots, skipping any extroots that don't have names
                    if( count(@$this->raParms['docid_extroots']) ) {
                        foreach( $this->raParms['docid_extroots'] as $d ) {
                            if( $bDebug ) { echo "<P>trying request under docid_extroot : '$d'</P>"; }
                            if( is_numeric($d) )  $d = $this->oDocRepDB->GetDocName( $d );
                            if( !empty($d) ) {
                                $kDoc = $this->oDocRepDB->GetDocFromName( $d.'/'.$sDoc );
                                if( $bDebug ) { echo "<P>expanding '$d/$sDoc' = docid $kDoc</P>"; }
                                if( $this->accesscheck( $kDoc, $raAllowedTrees, $bDebug ) ) {
                                    $bFound = true;
                                    break;
                                }
                            }
                        }
                    }
                    if( !$bFound ) $kDoc = 0;
                }
            }
        }

        if( $kDoc ) {
            $this->kDoc = $kDoc;
        } else {
            $this->kDoc = $this->kHome;
            if( $bDebug ) { echo "<P>Defaulting to docid_home {$this->kHome}</P>"; }
        }
        $this->raDocAncestors = $this->oDocRepDB->GetDocAncestors($this->kDoc);
        $this->raDocInfo = $this->oDocRepDB->GetDocInfo( $this->kDoc, $this->raParms['dr_flag'] );
        if( $bDebug ) {
            echo "<H3>DocRepWebsite:raDocAncestors</H3>";
            var_dump( $this->raDocAncestors );
        }

        /* Find the template, collect variables from the doc, doc ancestors, and template in that priority.
         * doc/ancestor/template vars override the vars provided by the caller in raParms['vars'], making that the master variable set
         */
        list($this->kTemplate,$this->raParms['vars'])
            = DocRepApp_GetTemplateAndVars( $this->oDocRepDB, $this->kDoc, $this->raParms['dr_flag'],
                                            $this->raParms['vars'], $this->raDocAncestors, $this->kRoot,
                                            @$this->raParms['docid_template_override_vars'] );

// it would be good to do an accesscheck on the kTemplate, but that requires the webadmin to specify the template in raAllowedTrees
//     $this->accesscheck( $this->kTemplate, $raAllowedTrees, $bDebug ) ) {
    }


    function BinaryServe()
    /*********************
        If the doc is IMAGE or DOC, serve it here
     */
    {
        if( $this->kDoc && in_array(@$this->raDocInfo['type'], array( 'IMAGE', 'DOC')) ) {
            $this->oDocRepDB->ServeDoc( $this->kDoc, $this->raParms['dr_flag'] );
            return( true );
        }
        return( false );
    }

    function DrawPage()
    /******************
     */
    {
        $oDocRepWiki = $this->factory_DocRepWiki( array( "php_serve_link" => $_SERVER['PHP_SELF'],
                                                         "php_serve_img"  => $_SERVER['PHP_SELF'],
                                                         "lang"           => $this->raParms['lang'],
                                                         "hide_docname_prefix" => $this->raParms['docid_root'].'/' ) );

        if( @$this->raParms['vars'] ) {     // copy the array because DocRepWiki might change it
            foreach( $this->raParms['vars'] as $k => $v ) {
                $oDocRepWiki->raParms['vars'][$k] = $v;
            }
        }
        return( $this->kTemplate ? $oDocRepWiki->TranslateDocWithTemplate( $this->kDoc, $this->kTemplate )
                                 : $oDocRepWiki->TranslateDoc( $this->kDoc ) );
    }

    function accessCheck( $kDoc, $raAllowedTrees, $bDebug = false )
    /**************************************************************
        Make sure the requested doc is in an allowed tree, and readable
     */
    {
        $bOk = false;

        // Readability:  raAncestors includes the kDoc, and it only contains readable docs.
        //               Therefore, if kDoc and any kRoot are in raAncestors it means that both kDoc and that kRoot are readable
        if( $kDoc &&
            ($raAncestors = $this->oDocRepDB->GetDocAncestors( $kDoc )) &&
            (in_array( $kDoc, $raAncestors )) )
        {
            // make sure the doc is in an allowed tree
            foreach( $raAllowedTrees as $kRoot ) {
                if( in_array( $kRoot, $raAncestors ) && ($kDoc != $kRoot) ) {
                    $bOk = true;
                    break;
                }
            }
        }

        if( $bDebug ) { echo "<P>docid $kDoc ".($bOk ? "passes" : "fails")." access check</P>"; }

        return( $bOk );
    }

    function factory_DocRepWiki( $raDRWparms )
    /*****************************************
        Return a DocRepWiki.  This allows a derived class to override the wiki object, with its own derived wiki class, or with its own parms
        The raDRWparms are passed to give the derived class a sample of the default parms, but it doesn't have to use them or care about them.
     */
    {
// maybe extend this and override an access check in _doIncl() and doTree(), to make sure that the given targets are allowed as accessCheck() above
        $oDocRepWiki = new DocRepWiki( $this->oDocRepDB, $this->raParms['dr_flag'], $raDRWparms );
        return( $oDocRepWiki );
    }
}

?>
