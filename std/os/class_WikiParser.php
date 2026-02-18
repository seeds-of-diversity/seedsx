<?php
/* WikiParser
 * Version 1.0
 * Copyright 2005, Steve Blinch
 * http://code.blitzaffe.com
 *
 * This class parses and returns the HTML representation of a document containing
 * basic MediaWiki-style wiki markup.
 *
 *
 * USAGE
 *
 * Refer to class_WikiRetriever.php (which uses this script to parse fetched
 * wiki documents) for an example.
 *
 *
 * LICENSE
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 *
 *
 */

class WikiParser {

    var $reference_wiki,$image_uri,$ignore_images,$emphasis,$preformat;

    function __construct() {
        $this->reference_wiki = '';
        $this->image_uri = '';
        $this->ignore_images = true;
        $this->emphasis = array();        // Bob: prevent index error when empty
        $this->preformat = false;         // Bob: prevent non-init notice
    }

    function handle_sections($matches) {
        $level = strlen($matches[1]);
        $content = $matches[2];

        $this->stop = true;
        // avoid accidental run-on emphasis
        return $this->emphasize_off() . "\n\n<h{$level}>{$content}</h{$level}>\n\n";
    }

    function handle_newline($matches) {
        if ($this->suppress_linebreaks) return $this->emphasize_off();

        $this->stop = true;
        // avoid accidental run-on emphasis
        return $this->emphasize_off() . "<br /><br />";
    }

    function handle_list($matches,$close=false) {

        $listtypes = array(
            '*'=>'ul',
            '#'=>'ol',
        );

        $output = "";

        $newlevel = ($close) ? 0 : strlen($matches[1]);

        while ($this->list_level!=$newlevel) {
            $listchar = substr($matches[1],-1);
            $listtype = @$listtypes[$listchar];	// Bob: throwing a warning of "offset 0" sometimes

            //$output .= "[".$this->list_level."->".$newlevel."]";

            if ($this->list_level>$newlevel) {
                $listtype = '/'.array_pop($this->list_level_types);
                $this->list_level--;
            } else {
                $this->list_level++;
                array_push($this->list_level_types,$listtype);
            }
            $output .= "\n<{$listtype}>\n";
        }

        if ($close) return $output;

        $output .= "<li>".$matches[2]."</li>\n";

        return $output;
    }

    function handle_definitionlist($matches,$close=false) {

        if ($close) {
            $this->deflist = false;
            return "</dl>\n";
        }


        $output = "";
        if (!$this->deflist) $output .= "<dl>\n";
        $this->deflist = true;

        switch($matches[1]) {
            case ';':
                $term = $matches[2];
                $p = strpos($term,' :');
                if ($p!==false) {
                    list($term,$definition) = explode(':',$term);
                    $output .= "<dt>{$term}</dt><dd>{$definition}</dd>";
                } else {
                    $output .= "<dt>{$term}</dt>";
                }
                break;
            case ':':
                $definition = $matches[2];
                $output .= "<dd>{$definition}</dd>\n";
                break;
        }

        return $output;
    }

    function handle_preformat($matches,$close=false) {
        if ($close) {
            $this->preformat = false;
            return "</pre>\n";
        }

        $this->stop_all = true;

        $output = "";
        if (!$this->preformat) $output .= "<pre>";
        $this->preformat = true;

        $output .= $matches[1];

        return $output."\n";
    }

    function handle_horizontalrule($matches) {
        return "<hr />";
    }

    function wiki_link($topic) {
        return ucfirst(str_replace(' ','_',$topic));
    }

    function handle_image($href,$title,$options) {
        if ($this->ignore_images) return "";
        if (!$this->image_uri) return $title;

        $href = $this->image_uri . $href;

        $imagetag = sprintf(
            '<img src="%s" alt="%s" />',
            $href,
            $title
        );
        foreach ($options as $k=>$option) {
            switch($option) {
                case 'frame':
                    $imagetag = sprintf(
                        '<div style="float: right; background-color: #F5F5F5; border: 1px solid #D0D0D0; padding: 2px">'.
                        '%s'.
                        '<div>%s</div>'.
                        '</div>',
                        $imagetag,
                        $title
                    );
                    break;
                case 'right':
                    $imagetag = sprintf(
                        '<div style="float: right">%s</div>',
                        $imagetag
                    );
                    break;
            }
        }

        return $imagetag;
    }

    function handle_internallink($matches) {
        //var_dump($matches);
        $nolink = false;
        $newwindow = false;	// Bob: prevent non-init warning

        $href = $matches[4];
        $title = @$matches[6] ? $matches[6] : $href.@$matches[7];
        $namespace = $matches[3];

        if ($namespace=='Image') {
            $options = explode('|',$title);
            $title = array_pop($options);

            return $this->handle_image($href,$title,$options);
        }

        $title = preg_replace('/\(.*?\)/','',$title);
        $title = preg_replace('/^.*?\:/','',$title);

        if ($this->reference_wiki) {
            $href = $this->reference_wiki.($namespace?$namespace.':':'').$this->wiki_link($href);
        } else {
            $nolink = true;
        }

        if ($nolink) return $title;

        return sprintf(
            '<a href="%s"%s>%s</a>',
            $href,
            ($newwindow?' target="_blank"':''),
            $title
        );
    }

    function handle_externallink($matches) {
        $href = $matches[2];
        $title = $matches[3];
        if (!$title) {
            $this->linknumber++;
            $title = "[{$this->linknumber}]";
        }
        $newwindow = true;

        return sprintf(
            '<a href="%s"%s>%s</a>',
            $href,
            ($newwindow?' target="_blank"':''),
            $title
        );
    }

    function emphasize($amount) {
        $amounts = array(
            2=>array('<em>','</em>'),
            3=>array('<strong>','</strong>'),
            4=>array('<strong>','</strong>'),
            5=>array('<em><strong>','</strong></em>'),
        );

        $output = "";

        // handle cases where emphasized phrases end in an apostrophe, eg: ''somethin'''
        // should read <em>somethin'</em> rather than <em>somethin<strong>
        if ( (!@$this->emphasis[$amount]) && (@$this->emphasis[$amount-1]) ) {
            $amount--;
            $output = "'";
        }

        $output .= $amounts[$amount][(int) @$this->emphasis[$amount]];

        $this->emphasis[$amount] = !@$this->emphasis[$amount];

        return $output;
    }

    function handle_emphasize($matches) {
        $amount = strlen($matches[1]);
        return $this->emphasize($amount);

    }

    function emphasize_off() {
        $output = "";
        foreach ($this->emphasis as $amount=>$state) {
            if ($state) $output .= $this->emphasize($amount);
        }

        return $output;
    }

    function handle_eliminate($matches) {
        return "";
    }

    function handle_variable($matches) {
        switch($matches[2]) {
            case 'CURRENTMONTH': return date('m');
            case 'CURRENTMONTHNAMEGEN':
            case 'CURRENTMONTHNAME': return date('F');
            case 'CURRENTDAY': return date('d');
            case 'CURRENTDAYNAME': return date('l');
            case 'CURRENTYEAR': return date('Y');
            case 'CURRENTTIME': return date('H:i');
            case 'NUMBEROFARTICLES': return 0;
            case 'PAGENAME': return $this->page_title;
            case 'NAMESPACE': return 'None';
            case 'SITENAME': return $_SERVER['HTTP_HOST'];
            default: return '';
        }
    }

    function parse_line($line) {
        $line_regexes = array(
            'preformat'=>'^\s(.*?)$',
            'definitionlist'=>'^([\;\:])\s*(.*?)$',
            'newline'=>'^$',
            'list'=>'^([\*\#]+)(.*?)$',
            'sections'=>'^(={1,6})(.*?)(={1,6})$',
            'horizontalrule'=>'^----$',
        );
        $char_regexes = array(
            'emphasize'=>'(\'{2,5})',       // moved this before internal_link because a blank attribute (href='') looks like an emphasis mark

//          'link'=>'(\[\[((.*?)\:)?(.*?)(\|(.*?))?\]\]([a-z]+)?)',

            /* Bob: internal link returns matches[]     * = useful
             *
             *     [[(namespace:)target(|parms)]]suffix
             *
             *     *0 = whole double-bracketed link
             *      1 = same as 0 (the whole regex has parentheses around it)
             *      2 = namespace with colon, any chars preceding optional colon (parentheses that make the whole namespace optional cause this match)
             *     *3 = namespace without colon
             *     *4 = content after an optional colon and preceding an optional pipe
             *      5 = pipe and content after it
             *     *6 = content after an optional pipe
             *     *7 = text following ]] with no whitespace  (so you can construct [[click]]ing with target 'click' and link label 'clicking')
             */
            'internallink'=>'('.
                '\[\['. // opening brackets
                    '(([^\]]*?)\:)?'. // namespace (if any)
                    '([^\]]*?)'. // target
                    '(\|([^\]]*?))?'. // title (if any)
                '\]\]'. // closing brackets
//                '([a-z]+)?'. // any suffixes
                ')',
            'externallink'=>'('.
                '\['.
                    '([^\]]*?)'.
                    '(\s+[^\]]*?)?'.
                '\]'.
                ')',
            'eliminate'=>'(__TOC__|__NOTOC__|__NOEDITSECTION__)',
            'variable'=>'('. '\{\{' . '([^\}]*?)' . '\}\}' . ')',
        );

        $this->stop = false;
        $this->stop_all = false;

        $called = array();

        $line = rtrim($line);

        foreach ($line_regexes as $func=>$regex) {
            if (preg_match("/$regex/i",$line,$matches)) {
                $called[$func] = true;
                $func = "handle_".$func;
                $line = $this->$func($matches);
                if ($this->stop || $this->stop_all) break;
            }
        }
        if (!$this->stop_all) {
            $this->stop = false;
            foreach ($char_regexes as $func=>$regex) {
                $line = preg_replace_callback("/$regex/i",array(&$this,"handle_".$func),$line);
                if ($this->stop) break;
            }
        }

        $isline = strlen(trim($line))>0;

        // if this wasn't a list item, and we are in a list, close the list tag(s)
        if (($this->list_level>0) && !@$called['list']) $line = $this->handle_list(false,true) . $line;
        if ($this->deflist && !@$called['definitionlist']) $line = $this->handle_definitionlist(false,true) . $line;
        if ($this->preformat && !@$called['preformat']) $line = $this->handle_preformat(false,true) . $line;

        // suppress linebreaks for the next line if we just displayed one; otherwise re-enable them
        if ($isline) $this->suppress_linebreaks = (@$called['newline'] || @$called['sections']);

        return $line;
    }

    function test() {
        $text = "WikiParser stress tester. <br /> Testing...
__TOC__

== Nowiki test ==
<nowiki>[[wooticles|narf]] and '''test''' and stuff.</nowiki>

== Character formatting ==
This is ''emphasized'', this is '''really emphasized''', this is ''''grossly emphasized'''',
and this is just '''''freeking insane'''''.
Done.

== Variables ==
{{CURRENTDAY}}/{{CURRENTMONTH}}/{{CURRENTYEAR}}
Done.

== Image test ==
[[:Image:bao1.jpg]]
[[Image:bao1.jpg|frame|alternate text]]
[[Image:bao1.jpg|right|alternate text]]
Done.

== Horizontal Rule ==
Above the rule.
----
Done.

== Hyperlink test ==
This is a [[namespace:link target|bitchin hypalink]] to another document for [[click]]ing, with [[(some) hidden text]] and a [[namespace:hidden namespace]].

A link to an external site [http://www.google.ca] as well another [http://www.esitemedia.com], and a [http://www.blitzaffe.com titled link] -- woo!
Done.

== Preformat ==
Not preformatted.
 Totally preformatted 01234    o o
 Again, this is preformatted    b    <-- It's a face
 Again, this is preformatted   ---'
Done.

== Bullet test ==
* One bullet
* Another '''bullet'''
*# a list item
*# another list item
*#* unordered, ordered, unordered
*#* again
*# back down one
Done.

== Definition list ==
; yes : opposite of no
; no : opposite of yes
; maybe
: somewhere in between yes and no
Done.

== Indent ==
Normal
: indented woo
: more indentation
: second level of indentation
Done.

";
        return $this->parse($text);
    }

    function parse($text,$title="") {
        $this->redirect = false;

        $this->nowikis = array();
        $this->list_level_types = array();
        $this->list_level = 0;

        $this->deflist = false;
        $this->linknumber = 0;
        $this->suppress_linebreaks = false;

        $this->page_title = $title;

        $output = "";

// This preg matches the first <nowiki> and the last </nowiki>, turning the content within into a big nowiki.  Instead, here's a simple stream parser. - Bob
//      $text = preg_replace_callback('/<nowiki>([\s\S]*)<\/nowiki>/i',array(&$this,"handle_save_nowiki"),$text);
// Or, here's how MediaWiki parses it:
//      while ( "" != $text ) {
//          $p = preg_split( "/<\\s*nowiki\\s*>/i", $text, 2 );
//          $stripped .= $p[0];
//          if ( ( count( $p ) < 2 ) || ( "" == $p[1] ) ) { $text = ""; }
//          else {
//              $q = preg_split( "/<\\/\\s*nowiki\\s*>/i", $p[1], 2 );
//              ++$nwsecs;
//              $nwlist[$nwsecs] = wfEscapeHTMLTagsOnly($q[0]);
//              $stripped .= $unique;           // a long string of noisy chars - better than what we do because our placeholder string would confuse THEIR preg_replace if a nowiki were truly empty - though we could just omit empty nowikis
//              $text = $q[1];
//          }
// Then later it replaces:
//      for ( $i = 1; $i <= $nwsecs; ++$i ) {
//          $text = preg_replace( "/{$unique}/", str_replace( '$', '\$', $nwlist[$i] ), $text, 1 );
//      }

        {
        $text2 = $text;
        $text = "";
        while( ($n1 = strpos( $text2, "<nowiki>" )) !== false && ($n2 = strpos( $text2, "</nowiki>")) !== false && $n1 < $n2 ) {
            $text .= substr( $text2, 0, $n1 );
            array_push( $this->nowikis, substr( $text2, $n1 + 8, $n2 - $n1 - 8 ) );
            $text .= "<nowiki></nowiki>";
            $text2 = substr( $text2, $n2 + 9 );
        }
        $text .= $text2;
        }

        $lines = explode("\n",$text);

        if (preg_match('/^\#REDIRECT\s+\[\[(.*?)\]\]$/',trim($lines[0]),$matches)) {
            $this->redirect = $matches[1];
        }

        foreach ($lines as $k=>$line) {
            $line = $this->parse_line($line);
            $output .= $line;
            if( !$this->preformat ) $output .= "\n";         // Bob: added space because conjoining lines don't get a padding space when grouped into a paragraph
        }

        // <ref> put this after the wiki processing so [1] isn't interpreted as an external link
        {
        $raRefs = array();
        $text2 = $output;
        $text = "";
        $tag1 = "<ref>";
        $tag2 = "</ref>";
        while( ($n1 = strpos( $text2, "<ref>" )) !== false && ($n2 = strpos( $text2, "</ref>")) !== false && $n1 < $n2 ) {
            $text .= substr( $text2, 0, $n1 );
            array_push( $raRefs, substr( $text2, $n1 + strlen($tag1), $n2 - $n1 - strlen($tag1) ) );
            $n = count($raRefs);
            $text .= "<SUP class='reference'><A href='#ref$n'>$n</A></SUP>";
            $text2 = substr( $text2, $n2 + strlen($tag2) );
        }
        $text .= $text2;
        $output = $text;

        $sRefs = "";
        $nRef = 0;
        foreach( $raRefs as $s ) {
            $nRef++;
            $sRefs .= "<LI id='ref$nRef'>$s</LI>";
        }
        $output = str_replace( "<references/>", "<OL class='references'>$sRefs</OL>", $output );
        $output = str_replace( "<references />", "<OL class='references'>$sRefs</OL>", $output );
        }

        $output = preg_replace_callback('/<nowiki><\/nowiki>/i',array(&$this,"handle_restore_nowiki"),$output);

        return $output;
    }

    function handle_save_nowiki($matches) {
        array_push($this->nowikis,$matches[1]);
        return "<nowiki></nowiki>";
    }

    function handle_restore_nowiki($matches) {
//      return array_pop($this->nowikis);       The nowikis are stored and replaced FIFO, so push-shift is correct; push-pop is LIFO - Bob
        return array_shift($this->nowikis);
    }
}

?>
