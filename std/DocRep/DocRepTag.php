<?php

/* DocRepTag
 *
 * Copyright (c) 2017 Seeds of Diversity Canada
 *
 * Parse and process tags of the form [[tag: p0 | p1...]]
 */

include_once( "DocRepDB.php" );

class DocRepTagHandler
/*********************
    Expands DocRep tags.
    Knows how to create the correct absolute links to images etc.
 */
{
    private $oDocRepDB;
    private $sFlag;
    private $sServeLink;
    private $sServeImg;
    private $bDebug = false;

    function __construct( DocRepDB $oDocRepDB, $sFlag, $raParms = array() )
    {
        $this->oDocRepDB = $oDocRepDB;
        $this->sFlag = $sFlag;

        $eDB = SEEDCore_ArraySmartVal( $raParms, 'site', array('public','office') );

        $s1 = ($sFlag == 'PUB' ? "docpub.php" : "doc.php");
        if( STD_isLocal ) {
            $sServe = SITEROOT.($eDB=='office' ? "office/" : "")."d/$s1";
        } else {
            $sServe = 'https://www.seeds.ca/'.($eDB=='office' ? "office/" : "")."d/$s1";
        }

// These are defined in the SEEDTagBasicResolver
        $this->sServeLink = SEEDCore_ArraySmartVal1( $raParms, 'php_serve_link', $sServe );
        $this->sServeImg  = SEEDCore_ArraySmartVal1( $raParms, 'php_serve_img',  $sServe );
        $this->bDebug = (@$raParms['bDebug']==true);
    }


    function ResolveTag( $raTag, SEEDTagParser $oTagParser, $raParms )
    /*****************************************************************
        Given a SEEDTagParser-parsed $raTag, expand it for DocRep tags

        If the given SEEDTagParser is a SEEDTemplate_SEEDTagParser then this code knows how to use ExpandStr to invoke all of SEEDTemplate's processors.

        The form for tag handlers is: if the tag is handled, return array(true,s); else return array(false,'')

        What if a parm in the $raTag is a variable or something?  It won't be.  There are only a few ways that variables can be accessed
        (e.g. by [[Var:foo]] or $foo) and those are all handled before this happens.  Why?  Because $foo is normalized by the parser,
        and all nested [[tags:]] are expanded recursively bottom-up.

        The bottom line is that all parms of $raTag are already fully expanded so they can be assumed to be verbatim text.
     */
    {
        //var_dump($raTag);
        $s = "";
        $bHandled = true;

        $oDoc = null;

        $tag = strtolower($raTag['tag']);
        if( substr($tag,0,3) == 'dr-' ) {
            if( !($oDoc = $this->oDocRepDB->GetDocObjectFromName( $raTag['target'] )) ) {
                if( $this->bDebug ) {
                    $s = "Can't $tag:{$raTag['target']}";
                }
                $bHandled = true;   // eat all tags starting with "dr-" ?
                goto done;
            }
        }

        switch( $tag ) {
            case 'docreptest':  $s = "Yes, you found docreptaghandler";     break;

            case 'dr-title':    $s = $oDoc->GetTitle( $this->sFlag );       break;
            case 'dr-name':     $s = $oDoc->GetName();                      break;
            case 'dr-parent':   $s = $oDoc->GetParent();                    break;
            case 'dr-include':  $s = DocRepDrawSEEDTagDoc( $oDoc, $this->sFlag, $oTagParser );  break;
            case 'dr-excerpt':  $s = $this->doExcerpt( $oDoc, $raTag['raParms'][1], $oTagParser );  break;

            case 'dr-foreach-tmpl':
                /* p0 is a docrepid for a folder (or some doc that has children)
                 * p1 is a docrepid for a template that is expanded in the context of each child of p0
                 */
                $s = $this->doForEachTmpl( $raTag['target'], $raTag['raParms'][1], $oTagParser );
                break;

/*
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
*/

            default:
                $bHandled = false;
                break;
        }

        done:

        return( array($bHandled,$s) );
    }

    private function doExcerpt( DocRepDoc $oDoc, $p1, SEEDTagParser $oTagParser )
    /****************************************************************************
        [[dr-excerpt:docid|more]]

        Show the <excerpt> part of the given document. If p1=='more' show a Read More link to the document.
     */
    {
        $bMore = ($p1 == 'more');

        $sTxt = $oDoc->GetText( $this->sFlag );
        $raExcerpt = SEEDStd_BookEnds( $sTxt, "<excerpt>", "</excerpt>" );  // get an array of the text contained within the bookends
        $sTxt = implode( ' ', $raExcerpt );

        if( $bMore ) {
            $sTxt .= "<p style='text-align:right'>"
                    // kMailSend is set by the mail_send script: it is the key of the recipient-email row.
                    // That means it is zero whenever you aren't looking at an actual email in an inbox.
                    ."<a href='https://seeds.ca/d?t=".DocRep_Key2HashTrack($oDoc->GetKey(),DOCREP_KEY_HASH_SEED,$oTagParser->GetVar('kMailSend'))."' target='_blank'>"
                    .($this->lang() == 'FR' ? "Lisez plus..." : "Read more...")."</a></p>";
        }

        return( $sTxt );
//drawDoc should take alt text
//return( $this->_doIncl( $kDoc, $sTxt ) );   // Expand the excerpted text using its doc's metadata, TEXTTYPE, etc

    }

    private function doForEachTmpl( $pFolder, $pTemplate, SEEDTagParser $oTagParser )
    /********************************************************************************
        [[dr-foreach-tmpl: docid of folder (or doc with children) to enumerate | docid of template to output for each child]]

        Output a template for each doc under the given folder.  The first parm can be anything with children, not just a folder.

        Sets the variable nEach = the number of the current iteration
     */
    {
        $s = "";

        // Scope any existing nEach so ForEach can be nested.
        // Todo: store this in an enumerated var like Var:nEach2 so all nested levels can be accessed by a template
        $saveNEach = $oTagParser->GetVar('drNEach');
        $oTagParser->SetVar( 'drNEach', 0 );

        $kFolder = $this->oDocRepDB->GetDocFromName( $pFolder );
        $kTemplate = $this->oDocRepDB->GetDocFromName( $pTemplate );

        if( $kFolder && $kTemplate ) {
            $raDocs = $this->oDocRepDB->ListChildren( $kFolder, $this->sFlag );
            foreach( $raDocs as $k => $v ) {
                $oTagParser->SetVar( 'drNEach', $oTagParser->GetVar('drNEach') + 1 );
                $s .= $this->drawDocInSkin( $k, $kTemplate, $oTagParser );
            }
        } else if( $this->bDebug ) {
            $s = "Can't dr-foreach-tmpl:$pFolder|$pTemplate";
        }

        $oTagParser->SetVar( 'drNEach', $saveNEach );

        return( $s );
    }

    private function drawDocInSkin( $pDoc, $pSkin, SEEDTagParser $oTagParser )
    /*************************************************************************
        Draw the given skin template in the document context of the given doc.
        The doc context allows the doc's variables to be in scope, over the skin's variables.

        It was chosen to use drDoc for the skin var, and drDocPrime for the doc var for consistency with the normal
        case with editors. If it were the other way around and you looked at a skin in an editor preview it would recurse infinitely.

        DR variables set: drDoc        = skin template being drawn
                          drDocPrime   = the document to put in the skin
                          drIncludedBy = document including this, if any
     */
    {
        $s = "";

        if( ($oDoc = $this->oDocRepDB->GetDocObjectFromName( $pDoc )) &&
            ($oDocSkin = $this->oDocRepDB->GetDocObjectFromName( $pSkin )) )
        {
            $s = DocRepDrawSEEDTagDocInSkin( $oDoc, $oDocSkin, $this->sFlag, $oTagParser );
        } else if( $this->bDebug ) {
            $s = "Can't draw doc $pDoc in skin $pSkin";
        }

        return( $s );
    }

    private function lang()
    {
// oTagParser->GetValue('lang') might have a value. If not, maybe this uses site_define_lang to get that value?
// Should it be somewhere more obvious? Should this object get a oW ?

        return( "FR" );
    }

}


function DocRepDrawSEEDTagDoc( DocRepDoc $oDoc, $flag, SEEDTagParser $oTagParser, $bSetVars = true )
/***************************************************************************************************
    Draw the given doc with its variables in scope.
    This can be used to dr-include a doc, putting its variables in scope.
    !bSetVars skips the variable setting so you can set up the variable space your way instead. e.g. when this doc is actually a skin for another doc

    If the given oTagParser is a SEEDTemplate_SEEDTagParser, use ::ExpandStr so all SEEDTemplate processors are invoked.

    DR variables set: drDoc        = document being drawn; use this to find metadata or other docs nearby in the tree
                      drIncludedBy = document including this, if any
 */
{
    $s = "";

    if( !($sTxt = $oDoc->GetText( $flag )) )  goto done;    // nothing to do

    // These variables let the current document know its identity and its includer, scoped to each level of inclusion.
    $saveIncl = $oTagParser->GetVar( 'drIncludedBy' );
    $saveDoc  = $oTagParser->GetVar( 'drDoc' );
    $oTagParser->SetVar( 'drIncludedBy', $saveDoc );   // previous kDoc if any
    $oTagParser->SetVar( 'drDoc', $oDoc->GetKey() );

// There should be a DocRep function that expands by TEXTTYPE_ appropriately - some types shouldn't get expanded

    $raVars = $bSetVars ? $oDoc->GetValue('raMetadata', $flag ) : array();

    if( $oTagParser instanceof SEEDTemplate_SEEDTagParser ) {
        /* If the tag parser is a SEEDTemplate_SEEDTagParser, use the SEEDTemplate to expand this doc.
         * That allows full functionality of multiple processors, etc. e.g. things like H2O will work.
         * The SEEDDataStore should be the same object via oTagParser->GetVar or oTagParser->oTmpl->GetVar,
         * so it shouldn't matter which one you use.
         */
        $s = $oTagParser->oTmpl->ExpandStr( $sTxt, $raVars );
    } else {
        /* Just an ordinary SEEDTagParser.
         * Variables are added to the datastore, so if this is an included doc they persist afterward
         */
        $oTagParser->AddVars( $raVars );
        $s = $oTagParser->ProcessTags( $sTxt );
    }

    $oTagParser->SetVar( 'drIncludedBy', $saveIncl );
    $oTagParser->SetVar( 'drDoc', $saveDoc );

    done:
    return( $s );
}

function DocRepDrawSEEDTagDocInSkin( DocRepDoc $oDoc, DocRepDoc $oDocSkin, $flag, SEEDTagParser $oTagParser )
/************************************************************************************************************
    Draw the given document in the given skin.
    This is implemented by setting the main doc's variables and putting its docid in drDocPrime, then drawing the skin.
    The skin template has to [[dr-include:$drDocPrime]] in the correct place.
 */
{
    $s = "";

    $saveDocPrime = $oTagParser->GetVar( 'drDocPrime' );

    // Set the skin's variables, overwrite with the doc's variables, then draw the skin with variable-setting disabled.
    // Check the return from GetValue because it can fail e.g. if looking for a PUB flag but doc has never been published
    if( ($raVars = $oDocSkin->GetValue('raMetadata', $flag)) )  $oTagParser->AddVars( $raVars );
    if( ($raVars = $oDoc->GetValue    ('raMetadata', $flag)) )  $oTagParser->AddVars( $raVars );

    $oTagParser->SetVar( 'drDocPrime', $oDoc->GetKey() );
    $s = DocRepDrawSEEDTagDoc( $oDocSkin, $flag, $oTagParser, false );   // false prevents setting skin's vars over the doc's variables
    $oTagParser->SetVar( 'drDocPrime', $saveDocPrime );

    return( $s );
}

?>
