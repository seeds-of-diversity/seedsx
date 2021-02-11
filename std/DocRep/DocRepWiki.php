<?php

// [[Include:]] creates a new docrepwiki which inherits variables from the parent. This means you can't use Include to set variables FOR the parent.
// Maybe it's better to use a single global variable scope for all included docs.


/* DocRepWiki
 *
 * Copyright (c) 2008-2017 Seeds of Diversity Canada
 *
 * Override SEEDWikiParser to provide DocRep's internal-link functionality
 */

include_once( STDINC."SEEDWiki.php" );
include_once( "DocRep.php" );           // DocRep_Key2Hash()
include_once( "DocRepDB.php" );


class DocRepWiki extends SEEDWikiParser
{
    public $oDocRepDB = NULL;  // available to clients for convenience
    var $sFlag;
    var $raParms;

    // internal
    var $kDoc = 0;
    var $raDocInfo = NULL;
    var $bKlugeDontExpandDocTag = false;

    var $bDebug = false;

    function __construct( DocRepDB $oDocRepDB, $sFlag, $raParms = array() )
    /**********************************************************************
        $sFlag = the doc_x_data flag that marks the version to read for each document  ""==maxVer

        Parms:
            lang           = EN|FR;
            php_serve_link = url of the php script that serves DocRep links     <A HREF='{$raParms['php_serve_links']}?n=name&{$raParms['php_serve_link_parmstr']}'>
            php_serve_img  = url of the php script that serves DocRep images    <IMG SRC='{$raParms['php_serve_img']}?n=name&{$raParms['php_serve_link_parmstr']}'>
            php_serve_link_parmstr = url parm string to append
            php_serve_img_parmstr = url parm string to append

            hide_docname_prefix = when writing doc names (e.g. in doTree) trim off the given prefix if exists

            keyHashSeed    = if you want to use numeric DocRep id links [[1234]]
                             and you want the outgoing links to be hashed, set this hash seed
                             (not used if your outgoing links are all named)
            vars           = array of global variables, overridden by document metadata.  Included docs use a new DocRepWiki to inherit vars & metadata from parent doc.
     */
    {
        $this->oDocRepDB = $oDocRepDB;
        $this->sFlag = $sFlag;
        $this->raParms = $raParms;
        if( !isset($this->raParms['vars']) )  $this->raParms['vars'] = array();

        parent::__construct();
    }


    function SetVars( $raVars ) { $this->raParms['vars'] = $raVars; }                                           // array( $k1 => $v, $k2 => $v2, ... )
    function SetVar( $k, $v )   { $this->raParms['vars'][$k] = $v; }                                            // add or overwrite a var
    function AddVars( $raVars ) { $this->raParms['vars'] = array_merge( $this->raParms['vars'], $raVars ); }    // add or overwrite an array of vars
    function AddVar( $k, $v )   { $this->SetVar( $k, $v ); }

    function GetVar( $k )
    /********************
        Return the value of the given variable.
        Must do LoadDoc first to get values from document metadata.  Else you'll just get vars specified at constructor.
     */
    {
        // accept $k with or without a leading $
        if( substr( $k, 0, 1 ) == '$' )  $k = substr( $k, 1 );

        if( empty($k) )  return( "" );

        switch( $k ) {
            case 'TITLE':       return( @$this->raDocInfo['Data_meta_title'] );
            case 'NAME':        return( @$this->raDocInfo['name'] );
//DocRepDoc uses some complicated algorithm but isn't this the same thing?  -- unless the current doc doesn't have a name
            case 'PARENT_NAME': return( substr(@$this->raDocInfo['name'], 0, strrpos(@$this->raDocInfo['name'], '/') ) );
            case 'DOCPUB_KEY':  return( DocRep_Key2Hash( $this->kDoc, DOCREP_KEY_HASH_SEED ) );  // should use $raParms['keyHashSeed'] but it needs to be set by all clients
        }


        // current document metadata overrides global vars
        if( $this->raDocInfo && isset($this->raDocInfo['Data_metadata'][$k]) ) {    // isset allows metadata blank value to override global var
            return( $this->raDocInfo['Data_metadata'][$k] );
        } else {
            return( @$this->raParms['vars'][$k] );
        }
    }


    function Lang()
    /**************
        Get lang from various sources because Vars can be added at any time after construction
     */
    {
        $lang = @$this->raParms['lang'];
        if( empty($lang) )  $lang = @$this->raParms['vars']['lang'];
        if( $lang != 'FR' )  $lang = 'EN';
        return( $lang );
    }

    function LoadDoc( $sDoc )
    /************************
     */
    {
        $ok = false;

        if( ($this->kDoc = $this->getKDoc($sDoc)) ) {
            $this->raDocInfo = $this->oDocRepDB->GetDocInfo( $this->kDoc, $this->sFlag );
            $ok = true;
        }
        return( $ok );
    }

    function TranslateDoc( $sDoc, $sAlternateText = "" )
    /***************************************************
        This class knows how to translate the different TEXTTYPEs, because it greatly simplifies inclusion of documents of different TEXTTYPEs
        for that decision to be handled by the including mechanism.

        If $sAlternateText is non-empty, it is translated instead of the stored doc text, with all other metadata taken from the stored doc.
        This is useful mainly for editors that allow a preview of altered text before changes are saved to the DocRep.
        Also used for <excerpt> function, where the doc text is filtered prior to translation but we still want to use the doc's TEXTTYPE, etc
     */
    {
        $this->bKlugeDontExpandDocTag = true;
        $s = "";
        if( $this->LoadDoc( $sDoc ) ) {
            $s = empty($sAlternateText) ? $this->oDocRepDB->GetDocAsStr( $this->kDoc, $this->sFlag ) : $sAlternateText;
            $s = $this->_Translate( $this->raDocInfo, $s );
        }
        return( $s );
    }

    function TranslateDocWithTemplate( $sDoc, $sTemplate )
    /*****************************************************
        Given two documents, load up sDoc, translate the content of sTemplate in sDoc's object
     */
    {
        $s = "";
        if( $this->LoadDoc( $sDoc ) ) {
            if( ($kTemplate = $this->getKDoc($sTemplate)) ) {
                $raTemplateInfo = $this->oDocRepDB->GetDocInfo( $kTemplate, $this->sFlag );
                $s = $this->oDocRepDB->GetDocAsStr( $kTemplate, $this->sFlag );

                if( $kTemplate == $this->kDoc ) {
                    // special case: we are looking at the template directly (e.g. editing it in DocRep editor).  Including it as the Doc makes infinite loop.
                    $this->bKlugeDontExpandDocTag = true;
                    echo "FORMATTING DOC"; exit;
                }

                $s = $this->_Translate( $raTemplateInfo, $s );
            }
        }
        return( $s );
    }


    function _Translate( $raDocInfo, $s )
    /************************************
     */
    { // actually, we should just pass TEXTTYPE_PLAIN back unchanged. some apps use this for any docrep text doc
        return( (strstr(@$raDocInfo['Data_verspec'], " TEXTTYPE_WIKI ") === false) ? $this->TranslateLinksOnly($s) : $this->Translate($s) );
    }


    function getKDoc( $sDoc )
    /************************
     */
    {
        return( $sDoc == 'SELF' ? $this->kDoc : $this->oDocRepDB->GetDocFromName($sDoc) );
    }


    /******************************
        Overrides of methods under Translate()
     */

    function HandleLink( $raLink )
    /*****************************
        Override the base WikiParser's internal-link handler

        SEEDWikiParser handles link and image tag syntax, except for URL resolution (translating DocRep names to URLs). This is handled
        by overriding imageGetURL() and linkGetURL().
     */
    {
//echo "<BR>";print_r($raLink);
        switch( strtolower( $raLink['namespace'] ) ) {
            case 'include':        return( $this->doInclude( $raLink ) );
            case 'includevar':     return( $this->doIncludeVar( $raLink ) );     // deprecated: use Include:$var instead
            case 'includelang':    return( $this->doIncludeLang( $raLink ) );
            case 'includevarlang': return( $this->doIncludeVarLang( $raLink ) ); // deprecated: use IncludeLang:$var instead
            case 'excerpt':        return( $this->doIncludeExcerpt( $raLink ) );
            case 'var':            return( $this->doVar( $raLink ) );
            case 'setvar':         return( $this->doSetVar( $raLink ) );
            case 'setvarifempty':  return( $this->doSetVar( $raLink, true ) );
            case 'setvarmap':      return( $this->doSetVarMap( $raLink ) );
            case 'tree':           return( $this->doTree( $raLink, false, false ) );
            case 'treevar':        return( $this->doTree( $raLink, true,  false ) );  // deprecated: use Tree:$var instead
            case 'treelang':       return( $this->doTree( $raLink, false, true ) );
            case 'treevarlang':    return( $this->doTree( $raLink, true,  true ) );   // deprecated: use TreeLang:$var instead
            case 'doc':            return( $this->doDoc( $raLink ) );
            case 'lang':           return( $this->doLang( $raLink ) );
            case 'langsetvar':     return( $this->doLangSetVar( $raLink ) );
            case 'imagevar':       return( $this->doImageVar( $raLink ) );            // deprecated: make parent::HandleLink use Image:$var instead
            case 'foreachin':      return( $this->doForEachIn( $raLink ) );
            case 'getname':        return( $this->doGetName( $raLink ) );    // ""==curr doc's name,  "#Parent", "#Grandparent"...
            case 'gettitle':       return( $this->doGetTitle( $raLink ) );   // ""==curr doc's title, "#Parent", "#Grandparent"...

            // Kluge: we put mbr tags in ebulletins stored on seeds1, but the tags are only processed when mailed by seeds2.
            //        People who look at the ebulletins on seeds1 should not see member info.
            //        The base wikiparser does the wrong thing with [[mbr: a | b]] -- it echos b
//This should be here when this method is overriding HandleLink like it should be
//            case 'mbr':            return( "" );

            default:               return( parent::HandleLink( $raLink ) );
        }
    }


    function imageGetURL( $raLink )
    /******************************
        SEEDWikiParser calls this to translate an image target into an url.
        Input: $raLink['target'] can be 'http://www...' or 'myInternalLinkName'
        Output: a full url that retrieves the target image
     */
    {
        if( substr( $raLink['target'], 0, 4 ) == "http" ) {
            return( $raLink['target'] );
        }

        // assume the target is a named DocRep image, or a doc id
        return( @$this->raParms['php_serve_img']."?".$this->_makeLinkName( $raLink )
                .(@$this->raParms['php_serve_img_parmstr'] ? ("&".$this->raParms['php_serve_img_parmstr']) : "") );
    }


    function linkGetURL( $raLink )
    /*****************************
        SEEDWikiParser calls this to translate a link target into an url.

        Input:
            (namespace='http', target='//www.seeds.ca')
            (namespace='',     target='myInternalLinkName')
            (namespace='',     target='1234')                   a doc id

        Output: a full url that maps to the target
     */
    {
        if( $raLink['namespace'] == "http" ) {
            return( $raLink['namespace'].":".$raLink['target'] );
        }

        // assume the target is a named DocRep page, or a doc id
        return( @$this->raParms['php_serve_link']."?".$this->_makeLinkName( $raLink )
                .(@$this->raParms['php_serve_link_parmstr'] ? ("&".$this->raParms['php_serve_link_parmstr']) : "") );

    }

    function _makeLinkName( $raLink )
    /********************************
     */
    {
        // use DocRepDB to check that the named/numbered target is accessible?
        // No, it would be too hard to debug the page if the link weren't formed.
        // Instead, let the link point to a resource that will fail to be fetched.

        return( is_numeric($raLink['target'])
                    ? ("k=".DocRep_Key2Hash( $raLink['target'], @$this->raParms['keyHashSeed'] ))       // *** might not want it hashed (not hashed in Tree)
                    : ("n=".urlencode($raLink['target']) ) );
    }


    /*********************************
        Handle DocRep wiki tags
     */

    function doInclude( $raLink )
    /****************************
        [[Include: myNamedDoc]]
        [[Include: 1234]]

        Return the doc specified by target (a docrep name or id).  This begins a sub-wikiparse, so the result is wiki-translated.
     */
    {
        return( $this->_doIncl( $raLink['target'] ) );
    }

    function doIncludeVar( $raLink )
    /*******************************
        Include the document specified by the given variable
     */
    {
        return( $this->_doInclVar( $raLink['target'] ) );
    }

    function _doInclVar( $sVar )
    /***************************
     */
    {
        $v = $this->GetVar($sVar);

        if( $this->bDebug && empty($v) )  return( "<FONT color='red'><B>IncludeVar:$sVar</B></FONT>" );

        return( $this->_doIncl( $v ) );
    }

    function doIncludeExcerpt( $raLink )
    /***********************************
        This is like Include, but it only outputs the portion(s) of the included doc contained by <excerpt> </excerpt>
     */
    {
        $sDoc = $raLink['target'];
        $bMore = ($raLink['parms'][0] == 'more');

        if( !($kDoc = $this->getKDoc( $sDoc )) ) {
            return( $this->bDebug ? "<FONT color='red'><B>Excerpt:$sDoc</B></FONT>" : "" );
        }

        $sTxt = $this->oDocRepDB->GetDocAsStr( $kDoc, $this->sFlag );
        $raExcerpt = SEEDStd_BookEnds( $sTxt, "<excerpt>", "</excerpt>" );  // get an array of the text contained within the bookends
        $sTxt = implode( ' ', $raExcerpt );

        if( $bMore ) {
            $sTxt .= "<p style='text-align:right'>"
                    // kMailSend is set by the mail_send script: it is the key of the recipient-email row.
                    // That means it is zero whenever you aren't looking at an actual email in an inbox.
                    ."<a href='https://seeds.ca/d?t=".DocRep_Key2HashTrack($kDoc,DOCREP_KEY_HASH_SEED,$this->GetVar('kMailSend'))."' target='_blank'>"
                    .($this->Lang() == 'FR' ? "Lisez plus..." : "Read more...")."</a></p>";
        }

        return( $this->_doIncl( $kDoc, $sTxt ) );   // Expand the excerpted text using its doc's metadata, TEXTTYPE, etc
    }

    function _doIncl( $sDoc, $sAlternateText = "" )
    /**********************************************
     */
    {
        /* Instantiate a new DocRepWiki to format the included document, because the object has state variables.
         * Copy the current document's metadata vars into the new DocRepWiki environment, so they're inherited (overridden by included document's metadata)
         */

        // Check for sDoc == $this->kDoc (including self creates an infinite loop that could break the editor, so it can't be corrected)
// Call an accessCheck stub method, which can be overridden by a class that knows more about the include perms.
// A rogue author could [[Include]] a doc that's outside of a docroot, but DocRepDB would only allow it to be seen
// if someone already had read-access. So a bad author could break docroot restrictions, allowing a web site to contain
// a doc that isn't meant to be in that web site. but they couldn't use this to access docs that they can't already see
        $copyRaParms = $this->raParms;
        foreach( $this->raDocInfo['Data_metadata'] as $k => $v ) {
            $copyRaParms['vars'][$k] = $v;
        }

        $oDRW = new DocRepWiki( $this->oDocRepDB, $this->sFlag, $copyRaParms );
        $s = $oDRW->TranslateDoc( $sDoc, $sAlternateText );
        $oDRW = NULL;
        return( $s );
    }

    function _doInclTemplate( $sDoc, $sTemplate )
    /********************************************
        Translate a template in the context of the given doc.
        Instantiate a new DocRepWiki so the translation is independent of the current object context (doesn't affect the current doc).
        Copy the current document's metadata vars into the new DocRepWiki, so they're inherited (overriden by included doc's metadata).
     */
    {
        // Check for sDoc == $this->kDoc (including self creates an infinite loop that could break the editor, so it can't be corrected)
// Call an accessCheck stub method, which can be overridden by a class that knows more about the include perms.
// A rogue author could [[Include]] a doc that's outside of a docroot, but DocRepDB would only allow it to be seen
// if someone already had read-access. So a bad author could break docroot restrictions, allowing a web site to contain
// a doc that isn't meant to be in that web site. but they couldn't use this to access docs that they can't already see
        $copyRaParms = $this->raParms;
        foreach( $this->raDocInfo['Data_metadata'] as $k => $v ) {
            $copyRaParms['vars'][$k] = $v;
        }

        $oDRW = new DocRepWiki( $this->oDocRepDB, $this->sFlag, $copyRaParms );
        $s = $oDRW->TranslateDocWithTemplate( $sDoc, $sTemplate );
        $oDRW = NULL;
        return( $s );
    }


    function doVar( $raLink )
    /************************
     */
    {
        return( $this->GetVar($raLink['target']) );
    }

    function doSetVar( $raLink, $bOnlyIfEmpty = false )
    /**************************************************
        [[SetVar: var | p1 | $p2 | ... ]]

        Set variable var to the concatenation of p1+value($p2)+... where pN are strings or $variables

        bOnlyIfEmpty implements a default-setting variation that only sets the value if the variable is empty
     */
    {
        $k = @$raLink['target'];
        if( !$k ) return( '' );

        if( $bOnlyIfEmpty ) {
            if( $this->GetVar($k) != "" ) return( '' );
        }

        $sVal = "";
        $this->SetVar( $k, '' );                // so the variable is emptied if there are no parms
        if( isset( $raLink['parms'] ) ) {
            for( $i = 1; $i < count($raLink['parms']); $i++ ) { // parms[0] is the whole parms string - skip that
                $sVal .= $this->varToValue( $raLink['parms'][$i] );
            }
            $this->SetVar( $k, $sVal );
        }

        return( '' );
    }

    function doSetVarMap( $raLink )
    /******************************
        [[SetVarMap: targetVar | $test | $testVal1 | $targetVal1 | $testVal2 | $targetVal2 ... ]]

        if( $test == $testVal1 ) { targetVar = $targetVal1; }
        else
        if( $test == $testVal2 ) { targetVar = $targetVal2; }
        ...
     */
    {
        $k = @$raLink['target'];
        if( !$k || count(@$raLink['parms']) < 4 ) return( '' );

        $test = $this->varToValue( @$raLink['parms'][1] );

        $this->SetVar( $k, '' );                // so the variable is emptied if there are no matches
        for( $i = 2; $i < count($raLink['parms']); $i += 2 ) {   // parms[0] is the whole parms string, parms[1] is $test - skip those
            $testVal   = $this->varToValue( $raLink['parms'][$i] );
            $targetVal = $this->varToValue( $raLink['parms'][$i+1] );
            if( $test == $testVal ) {
                $this->SetVar( $k, $targetVal );
                break;
            }
        }

        return( "" );
    }

    private function varToValue( $p )
    /********************************
        Given either a variable $foo or a plain value foo, return the value
     */
    {
        if( substr( $p, 0, 1 ) == '$' )  $p = $this->GetVar( $p );  // GetVar works with or without $ but expects var name

        return( $p );
    }


    function doDoc( $raLink )
    /************************
        Translate the current doc in the current object. This is used in templates in the special case of LoadDoc();Translate(template);
        The parm is not currently used, since the current doc should already be loaded. If a parm were specified, wouldn't it just be like Include?
                        A: Sort of. The usual way of templates is to get the template vars + doc vars and process the template in the resulting
                           variable space.  Then there's no need to add the doc's vars at the include point.  It probably would have no effect though,
                           because the doc vars are added last in the pre-template init.  This could be a way to fix a funny case where the
                           doc vars are indeed overridden when the template has an Include which has has vars, and which does the [Doc]. i.e. the
                           Include overrides the original doc vars (this is wrong because though the doc should contribute vars to its template, template
                           vars should never override doc vars)
     */
    {
        if( $this->bKlugeDontExpandDocTag )  return( "DOCUMENT" );

        $s = "";

        if( $this->kDoc ) {
            $s = $this->oDocRepDB->GetDocAsStr( $this->kDoc, $this->sFlag );
            $raDocInfo = $this->oDocRepDB->GetDocInfo( $this->kDoc, $this->sFlag );

            $s = $this->_Translate( $raDocInfo, $s );
        }
        return( $s );
    }

    function doTree( $raLink, $bTargetIsVar, $bLangDependent )
    /*********************************************************
        Draw the doc tree rooted at the given page, highlight the current doc.

        [[Tree: page | depth ]]
        [[TreeVar: var]]
        [[TreeLang: page_en | page_fr]]
        [[TreeLangVar: var_en | var_fr]]

        depth: optional (default=to children of current doc, -1=no limit, >0=recursion depth)
     */
    {
        $s = "";
        $maxDepth = 0;

        if( $bLangDependent && $this->Lang() != 'EN' ) {
            $sDoc = @$raLink['parms'][1];
        } else {
            $sDoc = @$raLink['target'];
        }

        if( $bTargetIsVar ) {
            $sDoc = $this->GetVar($sDoc);
        }

        if( !$bTargetIsVar && !$bLangDependent ) {
            // [[Tree: ]]
            $maxDepth = intval(@$raLink['parms'][1]);
        }
// call accessCheck method to make sure we're allowed to view sDoc
        if( ($kTree = $this->getKDoc( $sDoc )) ) {
            $raAncestors = $this->oDocRepDB->GetDocAncestors( $this->kDoc );
            $s = $this->_treeLevel( $kTree, $raAncestors, $maxDepth );
        }
        return( $s );
    }

    function _treeLevel( $kSubtree, $raAncestors, $maxDepth = 0, $level = 1 )
    /************************************************************************
        $maxDepth: >0 = depth to recurse
        $maxDepth:  0 = to the current doc's children ($raAncestors is current doc's ancestors including current doc)
        $maxDepth: -1 = all the way down
     */
    {
        $s = "<DIV class='DocRepTree_level'>"           // defines the basic attributes of structure
            ."<DIV class='DocRepTree_level$level'>";    // defines variations per-level, if defined
        $raDocs = $this->oDocRepDB->ListChildren( $kSubtree, $this->sFlag );
        foreach( $raDocs as $k => $v ) {
            $s .= "<DIV class='DocRepTree_title'>"
                 ."<A HREF='".$this->raParms['php_serve_link']."?";
            if( ($raInfo = $this->oDocRepDB->GetDocInfo($k, $this->sFlag)) && !empty($raInfo['name']) ) {
            	$name = $raInfo['name'];
            	if( !empty($this->raParms['hide_docname_prefix']) &&
            	    $this->raParms['hide_docname_prefix'] == substr($name,0,strlen($this->raParms['hide_docname_prefix'])) )
            	{
            	    $name = substr($name,strlen($this->raParms['hide_docname_prefix']));
            	}
                $s .= "n=".urlencode($name);
            } else {
                $s .= "k=$k";
            }
            if( @$this->raParms['php_serve_link_parmstr'] )  $s .= "&".$this->raParms['php_serve_link_parmstr'];
            $s .= "'><NOBR>";
            if( $k == $this->kDoc )  $s .= "<DIV class='DocRepTree_titleSelected'>";
            $s .= (!empty($v['title']) ? $v['title'] : (!empty($v['name']) ? $v['name'] : "Untitled"));
            if( $k == $this->kDoc )  $s .= "</DIV>";   // titleSelected
            $s .= "</NOBR></A>";
            $s .= "</DIV>";  // title       // isn't this in the wrong place? Should be right after </A>
            if( ($maxDepth == -1) || (!$maxDepth && in_array( $k, $raAncestors )) || ($maxDepth > $level) ) {
                $s .= $this->_treeLevel( $k, $raAncestors, $maxDepth, $level + 1 );
            }
        }
        $s .= "</DIV>"   // level$level
             ."</DIV>";  // level
        return( $s );
    }

    function doLang( $raLink )
    /*************************
        [[Lang: a | b]]

        Return 'a' if processing language EN, 'b' if FR
     */
    {
        return( $this->Lang() == 'EN' ? @$raLink['target'] : @$raLink['parms'][1] );
    }

    function doLangSetVar( $raLink )
    /*******************************
        [[LangSetVar: var | v_en | v_fr ]]

        Set variable var to v_en or v_fr based on the current language (v_en and v_fr can be values or $variables)
     */
    {
        $k = @$raLink['target'];
        if( !$k ) return( '' );

        $this->SetVar( $k, $this->varToValue( $this->Lang() == 'EN' ? @$raLink['parms'][1] : @$raLink['parms'][2] ) );

        return( '' );
    }

    function doIncludeLang( $raLink )
    /********************************
        [[IncludeLang: a | b]]

        Include doc 'a' if processing language EN, 'b' if FR
     */
    {
        return( $this->_doIncl( $this->Lang() == 'EN' ? @$raLink['target'] : @$raLink['parms'][1] ) );
    }

    function doIncludeVarLang( $raLink )
    /***********************************
        [[IncludeVarLang: a | b]]

        Include the doc referenced by variable 'a' if processing language EN, 'b' if FR
     */
    {
        return( $this->_doInclVar( $this->Lang() == 'EN' ? @$raLink['target'] : @$raLink['parms'][1] ) );
    }

    function doImageVar( $raLink )
    /*****************************
        [[ImageVar: same as Image but target is a variable instead of a doc]]
     */
    {
        $raLink['namespace'] = 'Image';
        $raLink['target'] = $this->GetVar( $raLink['target'] );
        if( empty($raLink['target']) )  $raLink['target'] = 'Unknown_variable';

        return( $this->HandleLink( $raLink ) );
    }

    function doForEachIn( $raLink )
    /******************************
        [[ForEachIn: docid of folder (or doc with children) to enumerate | docid of template to output for each child]]

        Output a template for each doc under the given folder.  The first parm can be anything with children, not just a folder.

        Sets the variable nEach = the number of the current iteration
     */
    {
        $s = "";

        $saveNEach = @$this->raParms['vars']['nEach'];  // allow ForEachIn to nest safely (todo: store this in an enumerated docrep var like Var:nEach2 so all nested levels can be accessed by a template)
        $this->raParms['vars']['nEach'] = 0;

        $pFolder = @$raLink['target'];
        $pTemplate = @$raLink['parms'][1];

// TODO: Allow most of these functions to accept $foo as a variable or foo as a name (use varToValue in getKDoc()? )
        $pFolder = $this->varToValue( $pFolder );
        $pTemplate = $this->varToValue( $pTemplate );

        $kFolder = $this->getKDoc( $pFolder );
        $kTemplate = $this->getKDoc( $pTemplate );

        if( $kFolder && $kTemplate ) {
            $raDocs = $this->oDocRepDB->ListChildren( $kFolder, $this->sFlag );
            foreach( $raDocs as $k => $v ) {
                ++$this->raParms['vars']['nEach'];
                $s .= $this->_doInclTemplate( $k, $kTemplate );
            }
        }

        $this->raParms['vars']['nEach'] = $saveNEach;

        return( $s );
    }

    function doGetName( $raLink )
    {
        $s = "";

        switch( $raLink['target'] ) {
            case '#Parent':
                if( ($o = new DocRepDoc( $this->oDocRepDB, $this->kDoc )) &&
                    ($oParent = $o->GetParentObj()) )
                {
                    $s = $oParent->GetName();
                }
                break;
            case '#GrandParent':
            case '#Grandparent':
            case '#ParentParent':
                if( ($o = new DocRepDoc( $this->oDocRepDB, $this->kDoc )) &&
                    ($oParent = $o->GetParentObj()) &&
                    ($oGrandparent = $oParent->GetParentObj()) )
                {
                    $s = $oGrandparent->GetName();
                }
                break;

            default:
//TODO: make this target=='' : also support GetName( 123 ) and GetName( $var )
                $s = $this->raDocInfo['name'];
                break;
        }

        return( $s );
    }

    function doGetTitle( $raLink )
    {
        $s = "";

        switch( $raLink['target'] ) {
            case '#Parent':
                if( ($o = new DocRepDoc( $this->oDocRepDB, $this->kDoc )) &&
                    ($oParent = $o->GetParentObj()) )
                {
                    $s = $oParent->GetTitle( $this->sFlag );
                }
                break;
            case '#GrandParent':
            case '#Grandparent':
            case '#ParentParent':
                if( ($o = new DocRepDoc( $this->oDocRepDB, $this->kDoc )) &&
                    ($oParent = $o->GetParentObj()) &&
                    ($oGrandparent = $oParent->GetParentObj()) )
                {
                    $s = $oGrandparent->GetTitle( $this->sFlag );
                }

                break;
            default:
//TODO: make this target=='' : also support GetName( 123 ) and GetName( $var )
                $s = $this->raDocInfo['Data_meta_title'];
                break;
        }

        return( $s );
    }
}

?>
