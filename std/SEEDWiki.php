<?php

/* SEEDWiki.php
 *
 * Copyright (c) 2007-2012 Seeds of Diversity Canada
 *
 * An extensible wiki-markup translator.
 *
 * Parses a generic wiki-markup format, and processes a default set of markup tags.
 *
 * Use ::Translate() for full wiki processing
 *     ::TranslateLinksOnly() to just process [[this kind of link]]
 *
 * Override
 *     ::ParseLink() to customize the way [[links]] are parsed
 *     ::HandleLink() to add new link types, or change the way the default link types are handled
 *     other methods to refine how various links are parsed and handled
 */


include_once( "os/class_WikiParser.php" );

class SEEDWikiParser extends WikiParser
{
    var $cssPrefix = "SEEDWiki";

    function __construct()
    {
        parent::__construct();

        // initialize base class variables
        $this->reference_wiki = '';   // relative location of internal links (override handle_internallinks instead)
        $this->image_uri = '';        // relative location of images (override handle_images instead)
        $this->ignore_images = false; // default is true
    }

    function Translate( $s )
    /***********************
     */
    {
        return( parent::parse( $s ) );
    }


    function TranslateLinksOnly( $s )
    /********************************
        Based on class_WikiParser, but only internallink and {{variables}} are processed
     */
    {
        $output = "";
        $lines = explode("\n",$s);

        foreach ($lines as $k=>$line) {
            //$line = $this->parse_line($line);

            $line = rtrim($line);

            // [[internal links]]
            $regex = '('.
                '\[\['. // opening brackets
                    '(([^\]]*?)\:)?'. // namespace (if any)
                    '([^\]]*?)'. // target
                    '(\|([^\]]*?))?'. // title (if any)
                '\]\]'. // closing brackets
//                '([a-z]+)?'. // any suffixes
                ')';

            $line = preg_replace_callback("/$regex/i",array(&$this,"handle_internallink"),$line);

            // {{variables}}
            $regex = '('. '\{\{' . '([^\}]*?)' . '\}\}' . ')';
            $line = preg_replace_callback("/$regex/i",array(&$this,"handle_variable"),$line);

            $output .= $line."\n";
        }
        return( $output );
    }


    function handle_internallink( $raMatches )
    /*****************************************
        Override WikiParser's handler
     */
    {
        $raLink = $this->ParseLink( $raMatches );

        return( $this->HandleLink( $raLink ) );

        // Construct [[click]]ing  ->  <A href='click'>clicking</A>
        // $title = $raLink['parms'] ? $raLink[parms] : $raLink['target'].$raLink['suffix'];
    }

    function handle_variable( $raMatches ) {
        switch( $raMatches[2]) {
            case 'CURRENTMONTH': return date('m');
            case 'CURRENTMONTHNAMEGEN':
            case 'CURRENTMONTHNAME': return date('F');
            case 'CURRENTDAY': return date('d');
            case 'CURRENTDAYNAME': return date('l');
            case 'CURRENTYEAR': return date('Y');
            case 'CURRENTTIME': return date('H:i');
            case 'NUMBEROFARTICLES': return 0;
            //case 'PAGENAME': return $this->page_title;
            case 'NAMESPACE': return 'None';
            case 'SITENAME': return $_SERVER['HTTP_HOST'];

// Additional variables (should be factored as an override of WikiParser)
	    case 'NEXTYEAR': return( date('Y') + 1 );

            default: return '';
        }
    }

    function HandleLink( $raLink )
    /*****************************
        Override this method to handle custom links, or to handle standard links differently.
        If you process the link, return its expansion as a string.
        If you do not recognize the link, call parent::HandleLink() to use the base processor.
        (To stop processing with no output, return an empty string).

        See ParseLink() below for format of $raLink
     */
    {

// Kluge: this suppresses stupid output for mbr: tags when viewed on seeds1 where they aren't handled. The right way to suppress
// these is for this line to be in DocRepWiki (not really, but it's better than here) or someplace where it'll get hit before the base
// wikiparser does the wrong thing with it (see comment in DocRepWiki) but after it's handled correctly by seeds2 code, e.g. _mbr_mail.
// This is here instead of DocRepWiki because DocRepWiki is not using the HandleLink method yet.
if( $raLink['namespace'] == 'mbr' )  return("");


        switch( $raLink['namespace'] ) {
            case 'Image':   return( $this->doImage( $raLink ) );

            case 'ftp':
            case 'http':
            case 'https':   return( $this->doHttpLink( $raLink ) );

            case 'mailto':  return( $this->doMailtoLink( $raLink ) );

            case 'Link':    // same as doLocalLink (useful when you want to put ':' in the link attrs or caption, because the parser finds it in the namespace)
            case '':        return( $this->doLocalLink( $raLink ) );

            // there aren't supposed to be any namespaces that aren't handled by this point - parent::handle_internallink does nothing useful
            default:
                if( isset($this->raParms) && @$this->raParms['kluge_dontEatMyTag'] ) {
                    // Deprecating SEEDWiki in favour of SEEDTag: some code calls SEEDTag after DocRepWiki and needs
                    // new tags to be left intact
                    return( "[[{$raLink['namespace']}:{$raLink['target']}|".@$raLink['parms'][1]."|".@$raLink['parms'][2]."]]" );
                }
                return( "" );
        }
    }

    function ParseLink( $raMatches )
    /*******************************
        Translate the base parser's regex matches to an extensible set of parms.
        Override this method to customize the parsing of wiki links.  Output should use the same format,
        unless you handle the link yourself in HandleLink().
        The WikiParser base class passes its raw regex matches to handle_internallink.
        This allows the underlying parser to be changed (and the structure of $raMatches to change) without affecting code in derived classes.

        [[(namespace:)target(|parms)]]suffix

        Input:
        *0 = whole double-bracketed link incl suffix
         1 = same as 0 (the whole regex has parentheses around it)
         2 = namespace with colon (parentheses that make the whole namespace optional cause this match)
        *3 = namespace without colon
        *4 = content after an optional colon and preceding an optional pipe
         5 = pipe and content after it
        *6 = content after an optional pipe
        *7 = text following ]] with no whitespace  (so you can construct [[click]]ing with target 'click' and link label 'clicking')

        Output:
        'link'      = the whole link incl brackets and suffix
        'namespace' = see format above
        'target'    = see format above
        'parms'     = array( 0 = whole parms string excluding initial pipe, 1... = each pipe-delimited parm )
        'suffix'    = see format above
     */
    {
        $ra['link']      = @$raMatches[0];
        $ra['namespace'] = trim(@$raMatches[3]);
        $ra['target']    = trim(@$raMatches[4]);
        $ra['suffix']    = @$raMatches[7];

        $ra['parms'][0]  = trim(@$raMatches[6]);
        $parms = explode( '|', $ra['parms'][0] );
        for( $i = 0; $i < count($parms); ++$i ) {
            $ra['parms'][$i+1] = trim($parms[$i]);
        }

        return($ra);
    }

    function doImage( $raLink )
    /**************************
        Found a link like [[Image:target(|parms)]]   where parms can contain parm1|parm2...

        [[Image: src | left/right/frame/frame left/frame right {imgAttrs} | caption]]

        src = url of the image.  Override imageGetURL to resolve local names to a global url
        left/right/frame = frame puts a DIV around the image, optionally aligned left or right.  left/right alone aligns the image.
        img attrs = content of {} is inserted into img tag. Useful for specifying width, height
        caption = placed in alt text, formatted into a caption if frame specified
     */
    {
        $src = $this->imageGetURL( $raLink );
        $caption = @$raLink['parms'][2];
        //sscanf( $raLink['parms'][1], "%s {%s}", $align, $attrs );
        $raMatches = array();
        preg_match( "/([^\{]*)\{?([^\}]*)\}?/", $raLink['parms'][1], $raMatches );
        $align = (strpos( @$raMatches[1], "left" ) !== false ? "left" :
                 (strpos( @$raMatches[1], "right" ) !== false ? "right" : ""));
        $bFrame = (strpos( @$raMatches[1], "frame" ) !== false);
        $imgAttrs = @$raMatches[2];

        if( !$bFrame && !empty($align) ) $imgAttrs .= " align='$align'";
        if( !empty($caption) )           $imgAttrs .= " alt='$caption'";
        $s = "<IMG src='$src' class='{$this->cssPrefix}_img' $imgAttrs>";

        if( $bFrame ) {
            // Put a DIV around the IMG
            $style = empty($align) ? "" : "style='float:$align'";
            $s = "<DIV class='{$this->cssPrefix}_imgFrame' $style>".$s;
            if( $caption ) $s .= "<DIV class='{$this->cssPrefix}_imgCaption'>$caption</DIV>";
            $s .= "</DIV>";

        }

        return( $s );
    }

    function doURLLink( $raLink )
    /****************************
        [[Link: target | window | attrs | (extensible - put more here) | caption]]
             [[ target | window | attrs | (extensible - put more here) | caption]]

        This format is extensible.
        The caption is always the last parm.
        More fields can be added before the caption.
        Any fields preceding the caption can be omitted.
        The caption can be blank but its location must be indicated with a final '|'

        The Link: namespace does the same thing as the blank namespace with one exception:
            Since the parser looks for a ":" to find the namespace, a link like this is very bad [[target || style='color:green' | ]]
            This is okay [[Link: target || style='color:green' | ]]

        [[Link: target | caption]]
        [[Link: target | foo | caption]]      -- open link in new window foo
        [[Link: target | new | caption]]      -- open link in new window _blank
        [[Link: target | | attrs | caption]]  -- specify attrs, open link in same window
        [[Link: target | ]]                   -- caption=target
        [[Link: target | | attrs | ]]         -- caption=target
        [[Link: target]]                      -- special case, caption=target
     */
    {
        $caption = $window = $attrs = "";

        // caption is always the last parm.  Note that parms has an extra summary item at [0], so parms are indexed starting at 1
        $nCap = count($raLink['parms']);
        if( $nCap ) {
            $caption = $raLink['parms'][$nCap-1];
        }

        // window is always parm 1, unless parm 1 is the caption
        if( ($nCap-1) > 1 ) {
            $window = $raLink['parms'][1];
            if( $window == "new" )  $window = "_blank";
        }

        // attrs is always parm 2, unless parm 2 is the caption
        if( ($nCap-1) > 2 ) {
            $attrs = $raLink['parms'][2];
        }

        if( $raLink['namespace'] == 'mailto' ) {
            list($s1,$s2) = explode( '@', $raLink['target'] );  // split the email address for Javascript spamproofer

            $s = SEEDCore_EmailAddress2( $s1, $s2, $caption, array(), "class={$this->cssPrefix}_mailto" );
        } else {
            if( empty($raLink['namespace']) && substr($raLink['target'], 0, 4) === 'www.' && strpos(trim($raLink['target']),' ') === false ) {
                /* The author made a [[www.domain.com]] link and forgot to use http:
                 */
                $link = "http://".$raLink['target'];
                if( empty($caption) )  $caption = $raLink['target'];
            } else {
                $link = ($raLink['namespace'] ? ($raLink['namespace'].":") : "").$raLink['target'];
            }
            if( !empty($window) )  $window = " target='$window'";
            if( empty($caption) )  $caption = $link;
            $s = "<A class={$this->cssPrefix}_href HREF='$link'$window $attrs>$caption</A>";
        }

        return( $s );
    }

    /* Override these to handle links differently
     */
    function doMailtoLink( $raLink )    { return( $this->doURLLink( $raLink ) ); }
    function doHttpLink( $raLink )      { return( $this->doURLLink( $raLink ) ); }

    function doLocalLink( $raLink )
    {
        if( strpos($raLink['target'], '@') !== false  &&  strpos($raLink['target'], '/') === false  &&  strpos(trim($raLink['target']), ' ') === false ) {
            /* The author put an email address in the link tag, and forgot to use mailto:
             */
            $raLink['namespace'] = 'mailto';
            return( $this->doURLLink( $raLink ) );
        }

        if( empty($raLink['namespace']) && substr($raLink['target'], 0, 4) === 'www.' && strpos(trim($raLink['target']),' ') === false ) {
            // the author used [[www.domain.com]] without an explicit http:  The doURLLink knows what to do, so go there. Don't do the following stuff because
            // linkGetURL overrides can mess up this preferred default action for www.
            return( $this->doURLLink( $raLink ) );
        }

        // Though this is only called if namespace=='', an override of linkGetURL can return a newtarget with
        // a namespace. We parse this for doURLLink, though we could get away without it, to keep things regular
        // for any overrides of doURLLink
        $newtarget = $this->linkGetURL( $raLink );
        $ra = explode( ":", $newtarget, 2 );       // split into 2 parts, on the first ':'
        $raLink['namespace'] = count($ra)==2 ? $ra[0] : "";
        $raLink['target'] = count($ra)==2 ? $ra[1] : $ra[0];
        return( $this->doURLLink( $raLink ) );
    }

    function imageGetURL( $raLink )
    /******************************
        Override this to resolve local image references. Should return a full url.
     */
    {
        return( $raLink['target'] );        // at this level, assume that the target is a full URL  e.g. [[Image: http://www.seeds.ca/...]]
    }

    function linkGetURL( $raLink )
    /*****************************
        Override this to resolve local link references.

        Should return a full URL that maps to the target. Normally, the namespace will be blank because this is only used in that case, in this class,
        but overrides could defy that assumption.
     */
    {
        return( ($raLink['namespace'] ? ($raLink['namespace'].":") : "").$raLink['target'] );
    }

}


?>
