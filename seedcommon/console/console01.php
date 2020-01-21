<?php

/* console01
 *
 * Basic console framework
 *
 * Copyright (c) 2009-2015 Seeds of Diversity Canada
 */

define("console01_css_fontfamily01", "font-family:verdana,geneva,arial,helvetica,sans-serif;");
define("console01_css_color01",      "green");

define("console01_green_xlight", "#d8fcd8" );
define("console01_green_light",  "#c8ecc4" );
define("console01_green_med",    "#77b377" );
define("console01_green_dark",   "#397a37" );

include_once( STDINC."SEEDFormUI.php" );  // SEEDFormUI class


class Console01
/* Parms:
 *
 * HEADER:      the page title
 * HEADER_TAIL: html appended after the page title
 * HEADER_LINKS: add more links at the top -- array( array( 'label'=>text, 'href'=>url, {'target'=>_window} ), ... )
 *
 * CONSOLE_NAME: application name prevents different console apps from conflicting in the session var space
 * TABSETS:    array( tsid1 => array( 'tabs' => array( tabname1 => array('label' => tablabel1, ...),    // tabname is propagated, tablabel is shown
 *                                                      tabname2 => array('label' => tablabel2, ...), ... ),
 *                                          DEPRECATE: 'labels' => array(tablabel1, tablabel2, tablabel3, ...),
 *                                          'divstyle' => '{css}', ... ), ... )
 *
 * sSkin:       blue | green
 */
{
    // These are available for public use
    // The console needs a sess to keep track of its tabs and nav state.
    // Db connections and lang should be stored in Console01_Worker (note that any db-based string localization would have to go there too)
    // sess is duplicated here and in Console01_Worker for convenience.
    // oSVA is here because the tabset oSVAs are managed here too, and this allows the namespaces to look conventional, also oSVA depends only on sess

    public $sess;
    public $oSVA;      // user's stuff
    private $oSVAInt;  // console's own stuff

    public $kfdb;  //deprecate use Console01_Worker instead
    public $lang;  //deprecate use Console01_Worker instead

    var $raParms = array();
    private $sConsoleName = "";  // from raParms['CONSOLE_NAME'] : this keeps different console apps from conflicting in the session var space
    private $sSkin = "blue";
    protected $bBootstrap = false;
    private $raMsg = array();

    const TABSET_PERM_HIDE  = 0;  // don't show the tab at all
    const TABSET_PERM_SHOW  = 1;  // show the tab fully
    const TABSET_PERM_GHOST = 2;  // show a non-clickable tab, no way to hack to the content (users will know that tab exists but can't use it)

    function __construct( KeyFrameDB $kfdb = null, //deprecate use Console01_Worker instead (pass in null if you aren't using oC->kfdb)
                          SEEDSession $sess, $raParms = array() )
    {
        $this->kfdb = $kfdb;  //deprecate use Console01_Worker instead
        $this->sess = $sess;

        //deprecate lang, use Console01_Worker instead
        $this->lang = SEEDStd_ArraySmartVal( $raParms, 'lang', array("EN"), false ); // any non-blank value is allowed, blank defaults to "EN"
        $this->raParms = $raParms;
        $this->sConsoleName = @$raParms['CONSOLE_NAME'];

        if( @$raParms['sSkin'] ) $this->sSkin = $raParms['sSkin'];
        $this->bBootstrap = (@$raParms['bBootstrap']==true);

        /* How SVAs work
         *
         * oSVAInt takes care of the console's housekeeping - clients should not use it
         * oSVA is for the client's stuff, segregated by sConsoleName.  i.e. in $_SESSION['console01$sConsoleName'].
         *     This would be useful by console applications that don't use TABSETs.
         * Also, every tab in a TABSET has its own oSVA segregated by sConsoleName_tabset_tabname.  Use TabSetGetSVA to obtain one.
         */
        $this->oSVA = new SEEDSessionVarAccessor( $this->sess, "console01".$this->sConsoleName );
        $this->oSVAInt = new SEEDSessionVarAccessor( $this->sess, "console01i_".$this->sConsoleName );

        // Get console tabset parms into $sess
        foreach( $_REQUEST as $k => $v ) {
            if( substr($k,0,6) == 'c01tf_' ) {
$this->sess->VarSet( "console01".$this->sConsoleName."TF".substr($k,6), SEEDSafeGPC_MagicStripSlashes($v) );
                $this->oSVAInt->VarSet( "TF".substr($k,6), SEEDSafeGPC_MagicStripSlashes($v) );
            }
        }

        if( !isset($this->raParms['EnableC01Scrolling']) )  $this->raParms['EnableC01Scrolling'] = true;
    }

    function SetConfig( $raConfig )
    /******************************
       Same as the parms in the constructor
     */
    {
        $this->raParms = array_merge( $this->raParms, $raConfig );
    }


    function ErrMsg( $s )   { $this->AddMsg( $s, 'errmsg' ); } //$this->errmsg .= $s."<br/>"; }
    function UserMsg( $s )  { $this->AddMsg( $s, 'usermsg' ); } //$this->usermsg .= $s."<br/>"; }

    function GetMsg( $sKey )
    {
        return( @$this->raMsg[$sKey] );
    }

    function AddMsg( $s, $sKey )
    {
        @$this->raMsg[$sKey] .= $s;
    }


    function DrawConsole( $sTemplate, $bExpand = true )
    {
        $sHead = "";
        $sBody = "";

        $title = @$this->raParms['HEADER'];
        $tail  = @$this->raParms['HEADER_TAIL'];

        // Do this here so template callbacks can set usermsg and errmsg, etc
        $sTemplate = ($bExpand ? $this->ExpandTemplate( $sTemplate ) : $sTemplate);

        // Some browsers (like IE and Chrome) won't load jquery from http:// if the page is https:// so make the prefix match
        $sHttpPrefix = @$_SERVER['HTTPS'] == 'on' ? "https" : "http";

        // default is draw the console for a logged-in user; consoles for anonymous users have to set this false
        $bLogin = @$this->raParms['bLogin'] !== false;

        $sHead = "<title>$title</title>";
        // Console01 css and js
        $sHead .= "<link rel='stylesheet' type='text/css' href='".W_ROOT."seedcommon/console/console01blue/console01.css'></link>"
                 ."<link rel='stylesheet' type='text/css' href='".W_ROOT."seedcommon/console/console01phrameset/console01phrameset.css'></link>"
                 ."<script src='".W_ROOT."seedcommon/console/console01.js' type='text/javascript'></script>";
        if( @$this->raParms['css_files'] ) {
            foreach( $this->raParms['css_files'] as $v ) {
                $sHead .= "<link rel='stylesheet' type='text/css' href='$v'></link>";
            }
        }
        if( @$this->raParms['script_files'] ) {
            foreach( $this->raParms['script_files'] as $v ) {
                $sHead .= "<script src='$v' type='text/javascript'></script>";
            }
        }

        if( $this->raParms['EnableC01Scrolling'] ) {
            /* How scrolling works:
             *
             * 1) Javascript will scroll the window if console01scrollYOffset=[pixel Y offset of top of window]
             *    You can set this by writing javascript to set that variable, or by sending the http parm c01FormYScroll.
             *    Note that the C01Form sends this parm.
             * 2) Javascript will also scroll to any div with id='console01scrollhere' or whose id is named by the var console01scrollhere.
             *
             * The config parm EnableC01Scrolling only enables/disables the handling of http parm c01FormYScroll. Since it is sent by C01Form,
             * there might be a case for disabling/ignoring it.
             */
            if( ($yOffset = SEEDSafeGPC_GetInt('c01FormYScroll')) ) {
                $sBody .= "<SCRIPT>console01scrollYOffset=$yOffset;</SCRIPT>";
            }
        }
        if( @$this->raParms['EnableC01Form'] ) {
            $sBody .=
                "<script type='text/javascript'>"
                ."function console01FormSubmit(action,arg1, arg2, arg3) {"
                ."e=document.getElementById('c01FormAction');  e.value=action; "
                ."e=document.getElementById('c01FormArg1');    e.value=(typeof(arg1)==\"undefined\" ? \"\" : arg1);"
                ."e=document.getElementById('c01FormArg2');    e.value=(typeof(arg2)==\"undefined\" ? \"\" : arg2);"
                ."e=document.getElementById('c01FormArg3');    e.value=(typeof(arg3)==\"undefined\" ? \"\" : arg3);"
                ."e=document.getElementById('c01FormYScroll'); e.value=console01_getYOffset(); "
                ."f=document.getElementById('console01Form');  f.submit(); }"
                ."</script>"
                ."<form id='console01Form' method='post' action='{$_SERVER['PHP_SELF']}'>"
                ."<input type='hidden' id='c01FormAction' name='c01FormAction'/>"
                ."<input type='hidden' id='c01FormYScroll' name='c01FormYScroll'/>"
                ."<input type='hidden' id='c01FormArg1' name='c01FormArg1'/>"
                ."<input type='hidden' id='c01FormArg2' name='c01FormArg2'/>"
                ."<input type='hidden' id='c01FormArg3' name='c01FormArg3'/>"
                ."</form>";
        }
        $sBody .= $this->Style();
        if( !empty($this->raMsg['errmsg']) ) {
            if( $this->bBootstrap ) {
                $sBody .= "<div class='alert alert-danger'>".$this->raMsg['errmsg']."</div>";
            } else {
                $sBody .= "<p style='background-color:#fee;color:red;padding:1em'>".$this->raMsg['errmsg']."</p>";
            }
        }
        if( !empty($this->raMsg['usermsg']) ) {
            if( $this->bBootstrap ) {
                $sBody .= "<div class='alert alert-success'>".$this->raMsg['usermsg']."</div>";
            } else {
                $sBody .= "<p style='background-color:#eee;color:black;padding:1em'>".$this->raMsg['usermsg']."</p>";
            }
        }

        /* Heading and header links
         */
        $sBody .= "<table border='0' width='100%'><tr>"
                 ."<td valign='top'>"
                 // since this is used by both seeds and seeds2, fetch the logo from www.seeds.ca rather than store it in two local places
                 .(@$this->raParms['bLogo'] ? "<img src='//www.seeds.ca/i/img/logo/logoA-60x.png' width='60' height='50' style='display:inline-block'/>" : "")
                 .($title ? "<span class='console01-header-title'>$title</span>" : "")
                 ."</td>"
                 ."<td valign='top'>$tail &nbsp;</td>"
                 ."<td valign='top' style='float:right'>";
        if( isset($this->raParms['HEADER_LINKS']) ) {
            foreach( $this->raParms['HEADER_LINKS'] as $ra ) {
                $sBody .= "<a href='${ra['href']}' class='console01-header-link'"
                         .(isset($ra['target']) ? " target='${ra['target']}'" : "")
                         .(isset($ra['onclick']) ? " onclick='${ra['onclick']}'" : "")
                         .">"
                         .$ra['label']."</a>".SEEDStd_StrNBSP("",5);
            }
            $sBody .= SEEDStd_StrNBSP("",20);
        }
        if( $bLogin ) {
            $sBody .= "<a href='".SITEROOT."login/' class='console01-header-link'>Home</a>".SEEDStd_StrNBSP("",5)
                     ."<a href='".SITEROOT."login/?sessioncmd=logout' class='console01-header-link'>Logout</a>";
        }
        $sBody .= "</td></tr></table>"
                 ."<div id='console-body' width='100%'>"
                 .$sTemplate
                 ."</div>";

        if( $this->bBootstrap ) {
            // Bootstrap seems to reset a margin/padding that (some) browsers put around the body by default (and we've come to expect)
            $sBody = "<div style='margin:10px;'>".$sBody."</div>";
        }

        /* Assemble the html output
         */
        $s = self::HTMLPage( $sBody, $sHead, $this->lang, array( 'bBootstrap' => $this->bBootstrap,
                                                                 'sCharset' => @$this->raParms['sCharset'] ?: "ISO-8859-1",    // lots of console apps use iso8859 data
                                                                 'sBodyAttr' => "onLoad='console01_onload()'"
                                                               ) );

        return( $s );
    }

    static function HTMLPage( $sBody, $sHead, $lang, $raParms = array() )    // DEPRECATE, use static class
    {
        return( Console01Static::HTMLPage( $sBody, $sHead, $lang, $raParms ) );
    }

    function ExpandTemplate( $sTemplate )
    {
        $regex = '\[\['. // opening brackets
                     '(([^\]]*)\:)?'. // namespace (if any)
                     '([^\]]*?)'. // target
                     '(\|([^\]]*?))?'. // title (if any)
                 '\]\]'; // closing brackets

        $sOut = preg_replace_callback("/$regex/i",array(&$this,"_expandtemplate_callback"), $sTemplate );
        return( $sOut );
    }

    function _expandtemplate_callback( $raMatches )
    /* Handle tags of the form: [[namespace: tag | title]]
     *
     * raMatches[0] = whole tag content including [[ ]]
     * raMatches[1] = namespace (if any) with colon
     * raMatches[2] = namespace (if any) without colon
     * raMatches[3] = tag
     * raMatches[4] = title (if any) with leading |
     * raMatches[5] = title (if any)
     */
    {
        return( $this->ExpandTemplateTag( trim(@$raMatches[2]), trim(@$raMatches[3]), trim(@$raMatches[5]) ) );
    }

    function ExpandTemplateTag( $namespace, $tag, $title )
    {
        $s = "";
        switch( $namespace ) {
            case "TabSet":
                $s .= $this->TabSetDraw( $tag );
                break;
            case "":
                $s .= $this->DrawTag( $tag, $title );
                break;
            default:
                $s .= $this->DrawTagNS( $namespace, $tag, $title );
                break;
        }
        return( $s );
    }



    /* TabSets
     *
     * A tabset is a set of tabs that operate as a group. One tab is always active, by default the first one.
     * More than one tabset can be in a console, and they can exist in any arrangement (side by side, nested)
     *
     * Each tab has a tabname (used by the software to identify it) and a label (for display). Typically, punctuation and spaces
     * should be avoided in the name, but anything is allowed in the label. That's why there are both.
     *
     * Each tabset has a tsid.
     * So a tab can be uniquely identified by consoleAppName_tsid_tabname
     */

    function TabSetGetSVA( $tsid, $tabname )
    {
        $oSVA = new SEEDSessionVarAccessor( $this->sess, "console01".$this->sConsoleName."_${tsid}_${tabname}" );
        return( $oSVA );
    }


    function TabSetDraw( $tsid )
    /***************************
     * Draw a tabbed form, getting the current tab from the console session vars
     */
    {
        $s = "";

        if( !isset($this->raParms['TABSETS'][$tsid]) )  return("");

        $raTF = $this->raParms['TABSETS'][$tsid];

/* If using the old 'labels' config, create the new 'tabname' config
 */
if( !isset($raTF['tabs']) ) {
    $raTF['tabs'] = array();
    foreach( $raTF['labels'] as $l ) {
        $raTF['tabs'][$l]['label'] = $l;   // array( 'tabs' => array( tabname => array('label'=>l) ) )   where tabname==l
    }
}

        // Get the current tab, defaulting to the first one
        $sTabCurr = $this->TabSetGetCurrentTab( $tsid );
        if( !$sTabCurr || !isset($raTF['tabs'][$sTabCurr]) ) {
            reset( $raTF['tabs'] );            // point to the first element
            $sTabCurr = key( $raTF['tabs'] );  // the key of the first element (the first tabname)
        }
        $sLabelCurr = $raTF['tabs'][$sTabCurr]['label'];

        // Tell the TabSet to initialize itself on the current tab
        $mInit = $tsid.$sTabCurr.'Init';
        ( method_exists( $this, $mInit ) ? $this->$mInit() : $this->TabSetInit( $tsid, $sTabCurr ) );

        $s .= "<BR/<BR/><DIV class='console01_tabsetframe'>"
             ."<TABLE class='console01_TFtabs' border='0' cellspacing='0' cellpadding='0'><TR>";

        $i = 0;
        $tabnamePrev = "";
        foreach( $raTF['tabs'] as $tabname => $raTab ) {
            $eAllowed = $this->TabSetPermission( $tsid, $tabname );
            if( $eAllowed == Console01::TABSET_PERM_HIDE )  continue;

            // tabA0  first, not current
            // tabA1  first, current
            // tabB01 non-first, current
            // tabB10 non-first, previous current
            // tabB00 non-first, not current and previous not current
            // tabC0  tail of the last tab, not current
            // tabC1  tail of the last tab, current

            $bCurrent = ($tabname == $sTabCurr);

            if( $i == 0 ) {
                // first tab
                $class = 'console01_TFtabA'.($bCurrent ? "1" : "0");
            } else {
                // not the first tab
                $class = 'console01_TFtabB'.($bCurrent ? "01" :
                ($tabnamePrev == $sTabCurr ? "10" : "00") );
            }
            if( $eAllowed == Console01::TABSET_PERM_SHOW ) {
                $raLinkParms = $this->TabSetExtraLinkParms( $tsid, $tabname, array('bCurrent'=>$bCurrent) );
                // tell console to make tabname the active tab in the tabset tsid
                $raLinkParms['c01tf_'.$tsid] = $tabname;
                $sLink = "<A HREF='{$_SERVER['PHP_SELF']}?".SEEDStd_ParmsRA2URL($raLinkParms)."'>{$raTab['label']}</A>";
            } else {
            	// TABSET_PERM_GHOST
                $sLink = "<SPAN style='color:grey'>{$raTab['label']}</SPAN>";
            }
            $s .= "<TD class='$class'><NOBR>$sLink</NOBR></TD>";

            ++$i;
            $tabnamePrev = $tabname;
        }
        $s .= "<TD class='console01_TFtabC".($tabnamePrev == $sTabCurr ? "1" : "0")."'>&nbsp;</TD>"
             ."</TR></TABLE>";

        // Control and Content areas
        $sControl = $sContent = "";
        if( $this->TabSetPermission($tsid,$sTabCurr) == Console01::TABSET_PERM_SHOW ) {
            $mContent = $tsid.$sTabCurr.'Content';
            $mControl = $tsid.$sTabCurr.'Control';

            $sControl = method_exists( $this, $mControl ) ? $this->$mControl() : $this->TabSetControlDraw($tsid,$sTabCurr);
            $sContent = method_exists( $this, $mContent ) ? $this->$mContent() : $this->TabSetContentDraw($tsid,$sTabCurr);
        }

        $sSpacer1_1 = "<IMG height='1' width='1' src='".W_ROOT_SEEDCOMMON."console/spacer.gif'/>";
        $sSpacer12_1  = "<IMG height='1' width='12' src='".W_ROOT_SEEDCOMMON."console/spacer.gif'/>";
        $sSpacer12_19 = "<IMG height='19' width='12' src='".W_ROOT_SEEDCOMMON."console/spacer.gif'/>";
        $sSpacer12_20 = "<IMG height='20' width='12' src='".W_ROOT_SEEDCOMMON."console/spacer.gif'/>";
        $sSpacer15_20 = "<IMG height='20' width='15' src='".W_ROOT_SEEDCOMMON."console/spacer.gif'/>";

$s .= "<div class='console01_frame2-ctrl'>"
     ."<div class='console01_frame2-ctrl-tl'></div>"
     ."<div class='console01_frame2-ctrl-tc'></div>"
     ."<div class='console01_frame2-ctrl-tr'></div>"
     ."<div class='console01_frame2-ctrl-cl'></div>"
     ."<div class='console01_frame2-ctrl-cc'></div>"
     ."<div class='console01_frame2-ctrl-cr'></div>"
     ."<div class='console01_frame2-ctrl-bl'></div>"
     ."<div class='console01_frame2-ctrl-bc'></div>"
     ."<div class='console01_frame2-ctrl-br'></div>"
     ."<div class='console01_frame2-ctrl-body'>"
     .(!empty($sControl) ? "<DIV style='margin:10px'>$sControl</DIV>" : "&nbsp;")
     ."</div>"
     ."</div>";

        $s .= "<TABLE border='0' cellpadding='0' cellspacing='0' width='100%'>"
/*        // frame top
        ."<TR valign='top'>"
        ."<TD class='console01_frame-1-1-topleft' width='12' height='20'>$sSpacer1_1</TD>"
        ."<TD class='console01_frame-1-2-topmiddle' height='20'>$sSpacer1_1</TD>"
        ."<TD class='console01_frame-1-3-topright' width='15' height='20'>$sSpacer1_1</TD>"
        ."</TR>"
        ."<TR valign='top'>"
        ."<TD class='console01_frame-2-1-topleft' width='12'>$sSpacer1_1</TD>"
        ."<TD class='console01_frame-2-2-topmiddle'>"
        .(!empty($sControl) ? "<DIV style='margin:10px'>$sControl</DIV>" : "&nbsp;")
        ."</TD>"
        ."<TD class='console01_frame-2-3-topright' width='15'>$sSpacer1_1</TD>"
        ."</TR>"
        ."<TR valign='top'>"
        ."<TD class='console01_frame-3-1-topleft' width='12' height='20'>$sSpacer1_1</TD>"
        ."<TD class='console01_frame-3-2-topmiddle' height='20'>$sSpacer1_1</TD>"
        ."<TD class='console01_frame-3-3-topright' width='12' height='20'>$sSpacer1_1</TD>"
        ."</TR>"
*/
        // frame body
        ."<TR valign='top'>"
        ."<TD class='console01_frame-4-1-middleleft' width='12'>$sSpacer1_1</TD>"
        ."<TD>"
        ."<DIV class='console01_tabsetcontent' style='".@$raTF['divstyle']."'>"
        .$sContent."&nbsp;"
        ."</DIV></TD>"
        ."<TD class='console01_frame-4-3-middleright' width='15'>$sSpacer12_19</TD>"
        ."</TR>"
        // frame bottom
        ."<TR valign='top'>"
        ."<TD class='console01_frame-5-1-bottomleft' width='12' height='19'>$sSpacer1_1</TD>"
        ."<TD class='console01_frame-5-2-bottommiddle' height='19'>$sSpacer1_1</TD>"
        ."<TD class='console01_frame-5-3-bottomright' width='15' height='19'>$sSpacer1_1</TD>"
        ."</TR></TABLE>"
        ."</DIV>";

        return( $s );
    }


    function TabSetGetCurrentTab( $tsid )
    {
//        return( $this->sess->VarGet( "console01".$this->sConsoleName."TF".$tsid ) );
        return( $this->oSVAInt->VarGet( "TF".$tsid ) );
    }

    /* OVERRIDES:  the following methods implement the tabs' controls and content
     */
    function TabSetInit( $tsid, $tabname )
    {
        // initialize the current tab
    }

    function TabSetExtraLinkParms( $tsid, $tabname, $raParms )
    {
        // Return an array of k=>v that should be encoded into the link on the given tab label
        //
        // $raParms    : parms used by this method
        //                   bCurrent = this is the current tab
        return( array() );
    }

    function TabSetPermission( $tsid, $tabname )
    {
        // Return values: TABSET_PERM_HIDE, TABSET_PERM_SHOW, TABSET_PERM_GHOST

        return( Console01::TABSET_PERM_SHOW );
    }

    function TabSetControlDraw( $tsid, $tabname )
    {
        // override to place controls in the upper frame area
        return( "" );
    }

    function TabSetContentDraw( $tsid, $tabname )
    {
        // override to draw the main content
        return( "" );
    }


    function DrawTag( $tag, $title )
    {
        // base method just draws the tag (this is important for apps like Events that use [[mailto]] verbatim within the UI and should not be translated)
        //                                                             Might be smarter to transition to a different tag mechanism in console like {{}}
        return( "[[$tag]]"); //return( "!".$tag."!" );
    }

    function DrawTagNS( $namespace, $tag, $title )
    {
        return( "[[$namespace:$tag]]" );  //return( "!".$namespace.":".$tag."!" );
    }


    function Style()
    /***************
     */
    {
        $color1 = ($this->sSkin == 'green' ? 'green' : '#39b');

        $s = "<STYLE>"
        .".console01-header-title { display:inline-block;" // IE needs this display type to draw borders
                                  ."font-size:14pt;font-weight:bold;padding:3px;".console01_css_fontfamily01
        ."border-top:2px $color1 solid;"
        ."border-bottom:2px $color1 solid; }\n"
        .".console01-header-link {".console01_css_fontfamily01."font-size:10pt;color:green;text-decoration:none }\n"
        ."#console-body {}\n"


        .".console01_guideText {".console01_css_fontfamily01."font-size:11pt;font-weight:bold;color:".console01_css_color01."; }\n"
        .".console01_controlbox { border: solid black 2px; padding: 5px; }\n"
        .".console01_controlbox, .console01_controlbox td {"
        ."font-family:verdana,geneva,arial,helvetica,sans serif; font-size:10pt; }\n"
        .".console01_controlbox_label {"
        ."background-image: url(". W_ROOT_SEEDCOMMON ."console/console01_controlbox_label_bg.gif); "
        ."background-repeat: repeat; "
        //          ."-color:".console01_color01.";
        ."color: white; text-align:center; "
        ."padding: 5px; margin:-5px; margin-bottom:10px; font-weight:bold; }\n"
        .".console01_notes_readonly { height: 200px; "  // width:500px
        ."background: #f7f2d0; font-size: 9pt; padding: 10px; "
        ."overflow: auto; overflow-y: auto; }\n"

        .".console01_tabsetframe {}"
        .".console01_tabsetcontent { margin:10px; }" // border:0px groove #aaa; }"

        //        .".console01_tabsettabs { "
        //                ."color:fff; background-color: white;"  // console01_green_med
        //                ."text-decoration:none;"
        //                ."font-size:10pt; font-weight:bold; ".console01_css_fontfamily01."}"
        //        .".console01_tabsettabcurrent { background-color: ".console01_green_light.";}"

        .".console01_TFtabs { "
        ."font-size:13px; font-weight:bold; ".console01_css_fontfamily01.";"
        ."margin: 1em auto -1px 25px; "
        ."position:relative;"
        ."z-index:1; }"
        .".console01_TFtabs TD { padding: 4px 7px 3px 17px; }"
        .".console01_TFtabs A { "
        ."color: #030; "
        ."text-decoration:none;"
        ."text-align:center; }";

        $dirImg = W_ROOT_SEEDCOMMON ."console/console01"."{$this->sSkin}/";

        // graphics for TabSet
        foreach( array( 'A0', 'A1', 'B00', 'B01', 'B10', 'C0', 'C1' ) as $tab ) {
            $s .= ".console01_TFtab$tab { background-image: url( ${dirImg}c01_TFtab${tab}.png ); background-repeat: no-repeat; }";
        }

        // graphics for Frame
        foreach( array( '-1-1-topleft' => 'no-repeat',
                        '-1-2-topmiddle' => 'repeat-x',
                        '-1-3-topright' => 'no-repeat',
                        '-2-1-topleft' => 'repeat-y',
                        '-2-2-topmiddle' => 'repeat-xy',
                        '-2-3-topright' => 'repeat-y',
                        '-3-1-topleft' => 'no-repeat',
                        '-3-2-topmiddle' => 'repeat-x',
                        '-3-3-topright' => 'no-repeat',
                        '-4-1-middleleft' => 'repeat-y',
                        '-4-3-middleright' => 'repeat-y',
                        '-5-1-bottomleft' => 'no-repeat',
                        '-5-2-bottommiddle' => 'repeat-x',
                        '-5-3-bottomright' => 'no-repeat',
        ) as $place=>$repeat ) {
            $s .= ".console01_frame${place} { background-image: url( ${dirImg}c01_frame${place}.png ); background-repeat: ${repeat}; }";
        }

        $s .= "</STYLE>";

        return( $s );
    }

    /* Stateless utilities
     */
    function DrawInABox( $sContent )
    {
        $s = "<table border='0' cellspacing='0' cellpadding='0'>"
            ."<tr>"
            ."<td><img src='".W_ROOT."img/containers/border1_11.png'/></td>"
            ."<td style='background-image:url(".W_ROOT."img/containers/border1_12.png);background-repeat:repeat-x;'>&nbsp;</td>"
            ."<td><img src='".W_ROOT."img/containers/border1_13.png'/></td>"
            ."</tr>"
            ."<tr>"
            ."<td style='background-image:url(".W_ROOT."img/containers/border1_21.png);background-repeat:repeat-y;'>&nbsp;</td>"
            ."<td style='background-color:#c8ecc4'>"
            .$sContent
            ."&nbsp;</td>"
            ."<td style='background-image:url(".W_ROOT."img/containers/border1_23.png);background-repeat:repeat-y;'>&nbsp;</td>"
            ."</tr>"
            ."<tr>"
            ."<td><img src='".W_ROOT."img/containers/border1_31.png'/></td>"
            ."<td style='background-image:url(".W_ROOT."img/containers/border1_32.png);background-repeat:repeat-x;'>&nbsp;</td>"
            ."<td><img src='".W_ROOT."img/containers/border1_33.png'/></td></tr>"
            ."</table>";
        return( $s );
    }

    function DrawPhrameSet( $raContent, $raParms )
    {
        $s = "";

        if( isset($raParms['v']) )  $s .= $this->drawPhrameSetVert( $raContent, $raParms['v'], $raParms );
        if( isset($raParms['h']) )  $s .= $this->drawPhrameSetHorz( $raContent, $raParms['h'], $raParms );

        $s = "<DIV class='c01PhrameSet'>".$s."</DIV>";

        return( $s );
    }

    private function drawPhrameBox()
    {
// combine the Vert check, Horz check, and Block draw.  Call Vert to draw the vert structure and call this for each box. Call Horz to draw the horz structure and call this for each box.
    }

    private function drawPhrameSetHorz( $raContent, $raTree, $raParms )
    {
        $s = "";

        $s .= "<table border='0' cellspacing='10' cellpadding='0' width='100%'><tr valign='top'>";
        foreach( $raTree as $box ) {
            $sAttr = (isset($box['width']) ? ("width='{$box['width']}'") : "");
            $s .= "<td $sAttr>";
            if( isset($box['v']) ) {
                $s .= $this->drawPhrameSetVert( $raContent, $box['v'], $raParms );
            } else if( isset($box['h']) ) {
                $s .= $this->drawPhrameSetHorz( $raContent, $box['h'], $raParms );
            } else {
                $s .= $this->drawPhrame( $box, $raContent[$box['kContent']], $raParms );
            }
            $s .= "</td>";
        }
        $s .= "</tr></table>";

        return( $s );
    }

    private function drawPhrameSetVert( $raContent, $raTree, $raParms )
    {
        $s = "";

        $s .= "<div>";
        foreach( $raTree as $box ) {
            if( isset($box['v']) ) {
                $s .= $this->drawPhrameSetVert( $raContent, $box['v'], $raParms );
            } else if( isset($box['h']) ) {
                $s .= $this->drawPhrameSetHorz( $raContent, $box['h'], $raParms );
            } else {
                $s .= $this->drawPhrame( $box, $raContent[$box['kContent']], $raParms );
            }
        }
        $s .= "</div>";
        return( $s );
    }

    private function drawPhrame( $box, $content, $raParms )
    {
        $name = $box['id'];
        $style = SEEDStd_ArraySmartVal( $box, 'style', array('White'), false );

        $s = "<div class='c01PhrameBlock'>"
            ."<div class='c01PhrameBlock-tl c01PhrameBlock{$style}-tl'></div>"
            ."<div class='c01PhrameBlock-tc c01PhrameBlock{$style}-tc'></div>"
            ."<div class='c01PhrameBlock-tr c01PhrameBlock{$style}-tr'></div>"
            ."<div class='c01PhrameBlock-cl c01PhrameBlock{$style}-cl'></div>"
            ."<div class='c01PhrameBlock-cc c01PhrameBlock{$style}-cc'></div>"
            ."<div class='c01PhrameBlock-cr c01PhrameBlock{$style}-cr'></div>"
            ."<div class='c01PhrameBlock-bl c01PhrameBlock{$style}-bl'></div>"
            ."<div class='c01PhrameBlock-bc c01PhrameBlock{$style}-bc'></div>"
            ."<div class='c01PhrameBlock-br c01PhrameBlock{$style}-br'></div>"
            ."<div class='c01PhrameBlock-content'>".$content."</div>"
            ."</div>";
        return( $s );

    }




}


class Console01Static
{
    static function HTMLPage( $sBody, $sHead, $lang, $raParms = array() )
    /********************************************************************
        Assemble an html page

        raParms:
            bBootstrap    : use bootstrap by default, =>false to disable
            bJQuery       : load JQuery by default
            sCharset      : UTF-8 by default
            bCTHeader     : output header(Content-type) by default, =>false to disable
            sTitle        : <title>
            sHttpPrefix   : specify http or https, same as page by default
            sBodyAttr     : attrs for body tag e.g. onload
            bBodyMargin   : put a 15px margin around the body (some browsers do this by default, but bootstrap makes it zero)
            raScriptFiles : script files for the header
            raCSSFiles    : css files for the header
     */
    {
        // use bootstrap and JQuery by default
        $bBootstrap = (@$raParms['bBootstrap'] !== false);
        $bJQuery    = (@$raParms['bJQuery'] !== false);

        // match <head> links to the page's ssl
        if( !($sHttpPrefix = @$raParms['sHttpPrefix']) ) {
            $sHttpPrefix = @$_SERVER['HTTPS'] == 'on' ? "https" : "http";
        }

        // by default we output header(Content-type)
        $bCTHeader = (@$raParms['bCTHeader'] !== false);
        $sCharset  = (@$raParms['sCharset'] ? $raParms['sCharset'] : "UTF-8");

        // body can have attrs and an optional margin (use the margin with bootstrap)
        $sBodyAttr   = (@$raParms['sBodyAttr']);
        $bBodyMargin = (@$raParms['bBodyMargin'] == true);

        $s = "";

        if( $bCTHeader ) {
            // Why specify charset in both http and html?
            // Because 1) if Apache sends a different default charset in http header, the browser could/will trust that (chrome, at least, does)
            //         2) if someone downloads the html to file, there is no http header to define charset
            header( "Content-Type:text/html; charset=$sCharset" );
        }

//TODO: transition to html5
        $s .= $bBootstrap ? "<!DOCTYPE html>" : "<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01 Transitional//EN'>";

        if( @$raParms['sTitle'] ) {
            $sHead = "<title>{$raParms['sTitle']}</title>".$sHead;
        }

        $sHead = ($bBootstrap ? "<meta charset='$sCharset'>"                                               // the html5 way
                              : "<meta http-equiv='Content-type' content='text/html;charset=$sCharset'>")  // the older way
                .$sHead;

        if( $bJQuery ) {
            // prepend jQuery so it precedes any jQuery code in our header script (otherwise $ is not known)
            $sHead = "<script src='".W_CORE_JQUERY."'></script>".$sHead;
        }

        if( $bBootstrap ) {
            $sHead .= "<link rel='stylesheet' type='text/css' href='".W_CORE_URL."os/bootstrap3/dist/css/bootstrap.min.css'></link>"
                     ."<script src='".W_CORE_URL."os/bootstrap3/dist/js/bootstrap.min.js'></script>"
                     ."<meta name='viewport' content='width=device-width, initial-scale=1.0'>"
                     ."<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->\n"
                     ."<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->\n"
                     ."<!--[if lt IE 9]>\n"
                     ."<script src='$sHttpPrefix://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js'></script>"
                     ."<script src='$sHttpPrefix://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js'></script>"
                     ."<![endif]-->";
        }
        if( @$raParms['raCSSFiles'] ) {
            foreach( $raParms['raCSSFiles'] as $v ) {
                $sHead .= "<link rel='stylesheet' type='text/css' href='$v'></link>";
            }
        }
        if( @$raParms['raScriptFiles'] ) {
            foreach( $raParms['raScriptFiles'] as $v ) {
                $sHead .= "<script src='$v' type='text/javascript'></script>";
            }
        }

        $s .= "<html lang='".($lang == 'FR' ? 'fr' : 'en')."'>"
             ."<head>".$sHead."</head>"
             ."<body ".($bBodyMargin ? "style='margin:15px'" : "")." $sBodyAttr>".$sBody."</body>"
             ."</html>";

        return( $s );
    }
}

/* Console Worker classes
 *
 * Almost always, a console is useless without some logic control, db connections, etc.
 * So every Console01 object is linked with a Console_Worker object, which is normally a derivation of one of the base classes below.
 */

class Console01_Worker
{
    public $oC;
    public $kfdb;
    public $sess;
    public $lang;

    function __construct( Console01 $oC, KeyFrameDB $kfdb, SEEDSession $sess, $lang = "EN" )
    {
        $this->oC   = $oC;
        $this->kfdb = $kfdb;
        $this->sess = $sess;
        $this->lang = $lang;
    }
}

class Console01_Worker1    // DEPRECATE
{
    public $oC;
    public $kfdb;
    public $sess;
    public $lang;

    function __construct( Console01 $oC, KeyFrameDB $kfdb, SEEDSessionAccount $sess, $lang = "EN" )
    {
        $this->oC   = $oC;
        $this->kfdb = $kfdb;
        $this->sess = $sess;
        $this->lang = $lang;
    }
}

class Console01_Worker2
{
    public $oC;
    public $kfdb1;
    public $kfdb2;
    public $sess;
    public $lang;

    function __construct( Console01 $oC, KeyFrameDB $kfdb1, KeyFrameDB $kfdb2, SEEDSessionAccount $sess, $lang = "EN" )
    {
        $this->oC    = $oC;
        $this->kfdb1 = $kfdb1;
        $this->kfdb2 = $kfdb2;
        $this->sess  = $sess;
        $this->lang  = $lang;
    }
}

class Console01_ListEdit
/***********************
 Show a list of items from a database relation, and manage them using a form.

 Console draws the table and manages the UI.

 The caller supplies methods to draw items and the form.
 */
{
    var $kfrel;
    var $oKFUForm;

    // names of the parms that control scrolling and editing.  Change if multiple derived classes need parms simultaneously.
    var $httpNameEdit   = 'c01_leEdit';
    var $httpNameScroll = 'c01_leScroll';
    var $httpNameSubmit = 'c01_leSubmit';   // indicates that the form has been submitted

    /* Parms for scroll and edit represent _keys for those functions. This keeps the parms compact and simple.  kedit==-1 means draw an "Add new" form
     * But internally these are translated into:
     *     p_kSelect = selected row.  Scroll here.  0 == no selection
     *     p_bEdit   = true: draw a form beside item kSelect; "new" form at top if kSelect==0
     */
    var $p_kSelect;
    var $p_bEdit;

    function __construct( KeyFrameRelation $kfrel, $raParms = array() ) {
        $this->kfrel = $kfrel;

        if( ($c = @$raParms['httpNameSuffix']) ) {
            // differentiate this instance's http parms by adding a suffix.  This is necessary if multiple ListEdit controls interact in an application
            $this->httpNameScroll .= $c;
            $this->httpNameEdit   .= $c;
            $this->httpNameSubmit .= $c;
        }

        $kEdit = SEEDSafeGPC_GetInt($this->httpNameEdit);

        if( $kEdit == 0 ) {
            $this->p_kSelect = SEEDSafeGPC_GetInt($this->httpNameScroll);
            $this->p_bEdit = false;
        } else if( $kEdit == -1 ) {
            $this->p_kSelect = 0;
            $this->p_bEdit = true;
        } else {
            $this->p_kSelect = $kEdit;
            $this->p_bEdit = true;
        }

        $this->oKFUForm = $this->factory_KeyFrameUIForm( $this->kfrel );   // use the default form cid
    }

    function SetScroll( $kSelect, $bEdit = false )
    {
        $this->p_kSelect = $kSelect;
        $this->p_bEdit = $bEdit;
    }

    function Update()
    /****************
     If the form has been submitted, update the row
     */
    {
        if( SEEDSafeGPC_GetInt( $this->httpNameSubmit ) ) {  // the edit form was submitted
            $kfr = $this->oKFUForm->Update();
            if( $kfr && $kfr->Key() )  $this->SetScroll( $kfr->Key(), true );  // scroll to the row (e.g. if it's new and continue to show the form)
        }
    }

    function factory_KeyFrameUIForm( KeyFrameRelation $kfrel )
    {
        $oKFU = new KeyFrameUIForm( $kfrel );
        return( $oKFU );
    }

    function DrawList( $kfrc, $raParms = array() )
    /* Draw a table containing the rows returned by the kfrCursor, scroll to the row identified by kSelect.
     * If bEdit, put an edit form beside the kSelect row
     * $kSelect==-1 means add a new row, with form beside the first row or in a dummy row if kfrc contains no rows (i.e. the list is empty)
     */
    {
        $s = "";

        $kSelect = $this->p_kSelect;
        $bEdit = $this->p_bEdit;

        if( $this->p_bEdit ) {
            $s .= "<STYLE>"
            .".c01_leForm       { border:1px solid black; margin-left:10px; padding:10px; }"
            .".c01_leForm label { width: 45%; float: left; margin: 2px 4px 6px 4px; text-align: left; "
            .                    " font-family:verdana,helvetica,sans-serif; font-size:10pt; }"
            .".c01_leForm br    { clear: left; }"
            .".c01_leForm input { background: #eee;}"  // border: 1px solid #333;
            .".slUserForm input:hover { border: 1px solid #00f; background: #cec; }"
            .".slUserFormButton { background: #ccf; padding: 2px 8px; }"  // border: 1px solid #006;
            .".slUserFormButton:hover { border: 1px solid #f00; padding: 2px 8px; }" // background: #eef;
            ."</STYLE>"
            ."<TABLE border='0' cellspacing='0' cellpadding='0'>";
        }
        $bStart = true;
        $width = (@$raParms['itemWidth'] ? $raParms['itemWidth'] : '60%');
        while( $kfrc->CursorFetch() ) {
            if( !$this->p_bEdit ) {
                $s .= "<DIV id='c01_le".$kfrc->Key()."' style='width:$width'>";
            } else {
                $s .= "<TR><TD id='c01_le".$kfrc->Key()."' valign='top' style='width:$width'>";
            }
            $sStyle = ( $kSelect == $kfrc->Key() ? "border:3px solid blue;margin-top:1.5em;margin-bottom:0.5em;padding:5px;" : "" );
            $s .= "<DIV style='width:90%;$sStyle'>"
            .$this->DrawListItem( $kfrc, $raParms )
            ."</DIV>";
            if( !$this->p_bEdit ) {
                $s .= "</DIV>";
            } else {
                $s .= "</TD>";

                if( ($this->p_kSelect == 0 && $bStart) || $this->p_kSelect == $kfrc->Key() ) {
                    $s .= "<TD rowspan='10' valign='top'>".$this->drawForm( ($this->p_kSelect ? $kfrc : NULL), $raParms );
                } else {
                    $s .= "<TD>&nbsp;";
                }
                "</TD></TR>";
            }
            $bStart = false;
        }
        if( $this->p_bEdit  ) {
            if( $bStart && $this->p_kSelect == 0 ) {
                $s .= "<TR><TD rowspan='10' valign='top'>".$this->drawForm( NULL, $raParms )."</TD></TR>";
            }
            $s .= "</TABLE>";
        }

        if( $this->p_kSelect > 0 ) {
            $s .= "<SCRIPT>document.all.c01_le".$this->p_kSelect.".scrollIntoView(true);</SCRIPT>";
        }

        // This could be another way to position the form if the table were not used
        //            $s .= "<DIV id='geditForm' style='position:absolute;border:1px solid black;margin:10px;width:35%;'>"
        //               .$this->geditForm($this->p_kGedit)
        //               ."</DIV>"
        //                 ."<SCRIPT>"
        //                     ."document.getElementById('geditForm').style.top = document.getElementById('Grower{$kGBeside}').offsetTop;"
        //                     ."document.getElementById('geditForm').style.left = document.getElementById('Grower{$kGBeside}').offsetLeft "
        //                                                                     ."+ document.getElementById('Grower{$kGBeside}').offsetWidth;"
        //                 ."</SCRIPT>";


        return( $s );
    }

    function drawForm( $kfrc, $raParms )
    {
        if( !$kfrc ) {
            // Drawing a form for a new row.  Create a new kfrc for the form.
            $kfrc = $this->kfrel->CreateRecord();
        }

        $this->oKFUForm->SetKFR( $kfrc );

        $s = "<FORM method='post' action='${_SERVER['PHP_SELF']}'>".$this->Hidden('AddButton')."<INPUT type='submit' name='x' value='Add New'/></FORM>"
        ."<DIV class='c01_leForm'><FORM method='post' action='${_SERVER['PHP_SELF']}'>"
        .$this->Hidden('FormSubmitted') // this tells Update() that the form was submitted
        .$this->oKFUForm->HiddenKey()  // put the key in the parms by default.  In the unusual case where the user can change the key, an raParm could disable this
        .$this->DrawListForm( $kfrc, $raParms )
        ."</FORM></DIV>";
        return( $s );
    }

    function ExpandTags( $k, $s )
    /****************************
     Replace the following tags:
     [[LinkParmEdit]] with the urlparms to cause a form to be drawn for k
     [[LinkParmScroll]] with the urlparms to cause the page to scroll to k (no edit form)
     */
    {
        $s = str_replace( "[[LinkParmScroll]]", $this->httpNameScroll."=$k", $s );
        $s = str_replace( "[[LinkParmEdit]]",   $this->httpNameEdit."=$k",   $s );
        return( $s );
    }

    function Hidden( $eType, $sParm = NULL )
    /***************************************
     Draw a hidden form tag
     */
    {
        if( $eType == 'AddButton' ) {
            // the hidden form tag that tells Console to draw a "New" form
            return( "<INPUT type='hidden' name='{$this->httpNameEdit}' value='-1'/>" );
        } else if( $eType == 'FormSubmitted' ) {
            // the hidden form tag that tells Console the Form has been submitted
            return( "<INPUT type='hidden' name='{$this->httpNameSubmit}' value='1'/>" );
        }
    }

    function DrawListItem( $kfrc, $raParms ) { return("OVERRIDE"); }
    function DrawListForm( $kfrc, $raParms ) { return("OVERRIDE"); }
}


/******************
    Console01Table_base : manages the structure and positioning of: list items, form, buttons. Doesn't know anything about control parms.
    SEEDFormUI          : creates links and buttons with the desired control parms (kCurr, bNew, kDel, etc).  Doesn't know anything about reading them or using them (Update).

    Derived class must:
        Implement Table_Item to draw each item in the table (can use Table_Button* and Table_Link* methods for navigation in the item
        Implement Table_Form to draw the form
        Call Update() on the form (if one is implemented). The caller of the derived class can do this too.

    Derived class may:
        Use a different SEEDFormUI via factory, to use a different control parm scheme (optional)
 */


class Console01Table_base
/************************
    Show a set of rows in a vertical column of divs with an optional floating form

    Only derived classes can construct this (protected constructor).

    Derived classes must:
        - implement SetParms() to read control parms (e.g. from http) and set p_kCurr, p_bNew
        - implement buttons and links to send control parms (kCurr, bNew, kDel)
 */
{
    protected $kfrel;
    protected $oForm;  // some derived classes have their own form; for the rest we create a temporary oForm below
    protected $oSEEDFormUI;

    // These parms are owned by oSEEDFormUI, and duplicated here for convenience
    protected $p_kCurr = 0;        // to highlight and draw edit form at the right place
    protected $p_bNew = false;     // to tell when a form is new i.e. blank  (kCurr=0 is not enough to cause a blank form)
    protected $p_kDel = 0;

    // only derived classes can construct this
    protected function __construct( KeyFrameRelation $kfrel, SEEDForm $oForm = NULL )
    {
        $this->kfrel = $kfrel;
        $this->oForm = $oForm;
        $this->oSEEDFormUI = $this->factory_SEEDFormUI();

        $this->SetParms();  // get kCurr and bNew
    }

    protected function factory_SEEDFormUI()
    {
        $o = new SEEDFormUI();
        return( $o );
    }

    protected function SetParms()
    {
        $this->p_kCurr = $this->oSEEDFormUI->Get_kCurr();
        $this->p_bNew = $this->oSEEDFormUI->Get_bNew();
        $this->p_kDel = $this->oSEEDFormUI->Get_kDel();
    }

    protected function base_DrawTable( $raKFR, $parms )
    {
        $s = "";

        $bEdit        = SEEDStd_ArraySmartVal( $parms, 'bEdit',        array( false, true ) );
        $sWidthList   = SEEDStd_ArraySmartVal( $parms, 'sWidthList',   array( '60%' ) ); // default is a 60:40 split between list:form
        $sWidthForm   = SEEDStd_ArraySmartVal( $parms, 'sWidthForm',   array( '35%' ) );
        $bAllowNew    = SEEDStd_ArraySmartVal( $parms, 'bAllowNew',    array( true, false ) );
        $bAllowDelete = SEEDStd_ArraySmartVal( $parms, 'bAllowDelete', array( true, false ) );

        $sLabelNew    = SEEDStd_ArraySmartVal( $parms, 'sLabelNew',    array( "" ) );  // use the default defined in the method
        $sLabelEdit   = SEEDStd_ArraySmartVal( $parms, 'sLabelEdit',   array( "" ) );  // use the default defined in the method
        $sLabelDelete = SEEDStd_ArraySmartVal( $parms, 'sLabelDelete', array( "" ) );  // use the default defined in the method


        // kCurr: current item. The base implementation propagates this to $this->p_kCurr via c01t_k
        //        If a derived implementation uses a different propagation, $parms['kCurr'] can be used to override the base
        // bNew:  use a blank form to create a new item. Also overridable.
        if( isset($parms['kCurr']) )  { $this->oSEEDFormUI->Set_kCurr( ($this->p_kCurr = $parms['kCurr']) ); }
        if( isset($parms['bNew']) )   { $this->oSEEDFormUI->Set_bNew( ($this->p_bNew = $parms['bNew']) ); }


        // kScrollHere: scroll to the item identified by this key. If 0, scroll to the edit form if it is open
        $kScrollHere  = SEEDStd_ArraySmartVal( $parms, 'kScrollHere',  array( 0 ) );


        $bFirst = true;
        foreach( $raKFR as $kfr ) {
            if( $bEdit ) {
                /* if this is the kCurr item, draw an Edit form and optionally a New button
                 * if there is no kCurr and this is the first item, draw a New button (bNew==false) OR a New form (bNew==true)
                 */
                if( $this->p_kCurr && $this->p_kCurr == $kfr->Key() ) {
                    $oForm = $this->getKFUForm( $this->p_bNew ? NULL : $kfr );
                    $s .= "<DIV ".($kScrollHere==0 ? "id='console01scrollhere' ": "")."style='float:right;width:$sWidthForm;'>"
                         .($bAllowNew ? ("<DIV style='float:left;margin:10px;'>".$this->Table_ButtonNew( $sLabelNew )."</DIV>") : "")
                         .($bAllowDelete ? ("<DIV style='float:right;margin:10px;'>".$this->Table_ButtonDelete( 0, $sLabelDelete )."</DIV>") : "")
                         ."<DIV style='clear:both'>".$this->Table_Form_Wrapper( $oForm )."</DIV>"
                         ."</DIV>";
                } else if( !$this->p_kCurr && $bFirst && $bAllowNew ) {
                    $s .= "<DIV style='float:right;width:$sWidthForm;'>";
                    if( $this->p_bNew ) {
                        $oForm = $this->getKFUForm( NULL );
                        $s .= $this->Table_Form_Wrapper( $oForm );
                    } else {
                        $s .= "<DIV style='float:left'>".$this->Table_ButtonNew( $sLabelNew )."</DIV>";
                    }
                    $s .= "</DIV>";
                }
            }
            $sStyle = ( $this->p_kCurr == $kfr->Key() ? "border:2px solid black;border-radius:10px;margin-top:1.5em;margin-bottom:0.5em;padding:5px;" : "" );

            $s .= "<DIV class='c01tableitem' ".($kScrollHere==$kfr->Key() ? "id='console01scrollhere' ": "")." style='width:$sWidthList;$sStyle'>".$this->Table_Item( $kfr )."</DIV>";
            $bFirst = false;
        }
        if( $bEdit && $bFirst && !$this->p_kCurr ) {
            // no items so draw the New form
            $oForm = $this->getKFUForm( NULL );
            $s .= "<DIV style='float:right;width:$sWidthForm;'>".$this->Table_Form_Wrapper( $oForm )."</DIV>";
        }

        return( $s );
    }

    private function getKFUForm( $kfr )
    /**********************************
        If the derived class specified an oForm, use that.
        Otherwise create a temporary oForm and fill it with the given kfr.
        If that's NULL, it means we're drawing a NewRow form, so create an empty kfr.
     */
    {
        if( isset($this->oForm) ) {
            $oForm = $this->oForm;
        } else {
            $oForm = $this->factory_KeyFrameUIForm( $this->kfrel );   // use the default form cid
        }
        if( !$kfr ) {
            // Drawing a form for a new row.  Create a new kfrc for the form.
            $kfr = $this->kfrel->CreateRecord();
        }
        $oForm->SetKFR( $kfr );
        return( $oForm );
    }

    protected function factory_KeyFrameUIForm( $kfrel )
    {
        $oKFU = new KeyFrameUIForm( $kfrel );
        return( $oKFU );
    }


    protected function Table_Item( KFRecord $kfr )
    /*********************************************
        Draw the table item for this kfr.
     */
    {
        return( "OVERRIDE_ITEM" );
    }

    protected function Table_Form( SEEDForm $oForm )
    /***********************************************
        Draw the form for this kfr
     */
    {
        return( "OVERRIDE_FORM" );
    }

    public function Table_Form_Wrapper( SEEDForm $oForm )
    /****************************************************
        Override this to change the base form behaviour
     */
    {
        $s = "<div style='border:1px solid #aaa;border-radius:10px;background-color:#eee'>"
            ."<form method='post' action='{$_SERVER['PHP_SELF']}'>"  // oSEEDFormUI should write the <form> element using its parms
             // HiddenKCurr is for Console01Table's navigation via oSEEDFormUI
             // HiddenKey is for oForm update, but it's only available in KFUIForm derivation
            .$this->oSEEDFormUI->HiddenKCurr()
            .(method_exists($oForm, 'HiddenKey') ? $oForm->HiddenKey() : "")
            .$this->Table_Form( $oForm )
            ."</form>"
            ."</div>";
        return( $s );
    }

    public function Table_Button( $sLabel, $raParms = array() )
    /**********************************************************
        Access oSEEDFormUI to draw a user-defined button
     */
    {
        if( @$raParms['bAutoScroll'] ) {
            if( !isset($raParms['onSubmit']) ) $raParms['onSubmit'] = "";
            $raParms['onSubmit'] .= 'console01_Form_ScrollToHere(this);';
        }
        return( $this->oSEEDFormUI->Button( $sLabel, $raParms ) );
    }

    public function Table_ButtonNew( $sLabel = "New", $raParms = array() )
    {
        return( $this->oSEEDFormUI->ButtonNew( $sLabel, $raParms ) );
    }

    public function Table_ButtonDelete( $kDeleteItem = 0, $sLabel = "Delete", $raParms = array() )
    {
        // kDeleteItem==0 means delete kCurr
        return( $this->oSEEDFormUI->ButtonDelete( $kDeleteItem, $sLabel, $raParms ) );
    }

    public function Table_ButtonEdit( $kEditItem, $sLabel = "Edit", $raParms = array() )
    {
        return( $this->oSEEDFormUI->ButtonEdit( $kEditItem, $sLabel, $raParms ) );
    }

    public function Table_LinkEdit( $kEditItem, $sLabel = "Edit", $raParms = array() )
    {
        return( $this->oSEEDFormUI->LinkEdit( $kEditItem, $sLabel, $raParms ) );
    }
}



class Console01TableKFRArray extends Console01Table_base
{
    function __construct( KeyFrameRelation $kfrel, SEEDForm $oForm = NULL ) { parent::__construct( $kfrel, $oForm ); }

    function DrawTable( $raKFR, $parms )
    {
        return( $this->base_drawTable( $raKFR, $parms ) );
    }
}

class Console01TableKFRCursor extends Console01Table_base
{
    function __construct( KeyFrameRelation $kfrel, SEEDForm $oForm = NULL ) { parent::__construct( $kfrel, $oForm ); }

    function DrawTableKFRCursor( KFRecord $kfrc, $parms )
    {
        $raKFR = array();
        while( $kfrc->CursorFetch() ) {
            $kfr = $kfrc->Copy();
            $raKFR[] = $kfr;
        }
        return( $this->base_drawTable( $raKFR, $parms ) );
    }
}


class Console01_Stepper {
/**********************
    Manage a user-guided process of 1..n steps

    def = array( 'Title_EN'=>..., 'Title_FR'=>...,
                 'Steps'=> array( array( 'fn'=> function for step one, 'Title_EN'=>..., 'Title_FR'=>...),
                                  array( 'fn'=> function for step two, 'Title_EN'=>..., 'Title_FR'=>...),
                                  ...
                ));
 */

    private $def;
    private $raParms;   // array of parms passed back to Step functions
    private $lang;
    private $step;      // origin-1 iteration number, 0 indicates END

    function __construct( $def, $raParms = array(), $lang="EN" ) {
        $this->def = $def;
        $this->lang = $lang;
        $this->step = 1;

        $this->raParms = $raParms;
        $this->raParms['oStep'] = $this;  // so called functions can access methods of this class
    }

    function GetStep() {
        // This method is also a bounds normalizer
        if( $this->step < 1 || $this->step > count($this->def['Steps']) )  $this->step = 1;

        return( $this->step );
    }

    function GetNextStep() {
        $step = $this->GetStep();
        ++$step;
        if( $step > count($this->def['Steps']) )  $step = 0;      // indicate END
        return( $step );
    }

    function SetStep( $step ) {
        $this->step = $step;
        return( $this->GetStep() );   // normalize bounds
    }

    function GetParms( $k ) {
        return( @$this->raParms[$k] );
    }

    function DrawStep( $step = 0 )
    /*****************************
        step > 0   : draw the given step
        step == 0  : draw the current $this->step (e.g. as set by SetStep)
        step == -1 : draw the step specified by $_REQUEST['c01step'] -- this is the normal method when DrawNextButton() is used
     */
    {
        if( $step > 0 ) {
            $this->SetStep( $step );
        } else if( $step == -1 ) {
            $this->SetStep( intval(@$_REQUEST['c01step']) );
        }

        $s = "<STYLE>"
            .".c01_step_header    { font-weight:bold; }"
            .".c01_step_title     { font-size:x-large;  margin: 1em 0; }"
            .".c01_step_steps     { margin:1em; }"
            .".c01_step_steps td  { width:50px; font-family:arial,helvetica,sans-serif; text-align:center; }"
            .".c01_step_curr      { color:black; }"
            .".c01_step_notcurr   { color:#999; }"
            .".c01_step_stepnum   { font-size:large; }"
            .".c01_step_steplabel { font-size:x-small; line-height:1.2; }"
            ."</STYLE>";

        $s .= "<div class='c01_step_header'>";
        if( isset( $this->def['Title_'.$this->lang] ) )  $s .= "<div class='c01_step_title'>".$this->def['Title_'.$this->lang]."</div>";

        $s .= "<table class='c01_step_steps' border='0'>"
             ."<tr valign='top'>";
        for( $i = 1; $i <= count($this->def['Steps']); ++$i ) {
            $s .= "<td class='".($i==$this->step ? "c01_step_curr" : "c01_step_notcurr")."'>"
                 ."<div class='c01_step_stepnum'>$i</div>"
                 ."<div class='c01_step_steplabel'>".$this->def['Steps'][$i-1]['Title_'.$this->lang]."</div>"
                 ."</td>";
        }
        if( isset( $this->def['Steps'][$this->step - 1]['Title_'.$this->lang] ) ) {
            $s .= "<td class='c01_step_curr c01_step_stepnum'>"    // give it the same css as the numbers
                 .SEEDStd_StrNBSP( "  :  ".$this->def['Steps'][$this->step - 1]['Title_'.$this->lang] )
                 ."</td>";
        }
        $s .= "</tr></table>";
        $s .= "</div>"; // c01_step_header

        $raRet = call_user_func($this->def['Steps'][$this->step - 1]['fn'], $this->raParms );

        /* Return from the user function:
         *
         *   Every submit button has its own form. The array returned from the user function defines the
         *   structure of text and buttons
         *
         *   Example 1) A typical structure is text-and-two-or-three-buttons. Implemented by returning
         *                  array( 's' => text, 'buttons' => 'next repeat cancel' )
         *              This outputs the text and draws two forms for Next, Repeat and Cancel.
         *
         *   Example 2) Sometimes the Next form needs other controls. It's really hard to put the buttons in a nice
         *              row, include the additional controls in the Next <form> and still maintain well-formed html.
         *              Also, sometimes the form needs special attrs like enctype so it's easiest to let the caller draw it.
         *                  array( 'sForm' => text, 'buttons' => 'repeat cancel' )
         *              This outputs text like Example 1 but processes substitutions like [[next]] so your text can
         *              contain your own Next form. Then draws a row of button forms for Repeat and Cancel.
         *
         *   Return parms:
         *
         *   buttons = [next] [repeat] [cancel]  - the single-button forms to be drawn in the button row
         *   s       = text to write before the buttons
         *   sForm   = text to write before the buttons, but process substitutions
         *   sAfter  = text to write after the buttons
         *
         *   btnNext   = label for Next button
         *   btnRepeat = label for Repeat button
         *   btnCancel = label for Cancel button
         *
         *   btnHiddenParms       = hidden parms written into next and repeat buttons
         *   btnHiddenCancelParms = hidden parms written into the cancel button
         */
        $s .= @$raRet['s'];

        if( isset($raRet['sForm']) ) {
            $s .= $this->drawFormSubst( $raRet['sForm'], $raRet );
        }

        if( isset($raRet['buttons']) ) {
            $s .= "<div>";
            if( strpos($raRet['buttons'], 'next') !== false ) {
                $s .= "<form action='${_SERVER['PHP_SELF']}' method='post' style='display:inline'>"
                     .$this->drawFormSubst( "[[next]]", $raRet )
                     ."</form>"
                     .SEEDStd_StrNBSP("",6);
            }
            if( strpos($raRet['buttons'], 'repeat') !== false ) {
                $s .= "<form action='${_SERVER['PHP_SELF']}' method='post' style='display:inline'>"
                     .$this->drawFormSubst( "[[repeat]]", $raRet )
                     ."</form>"
                     .SEEDStd_StrNBSP("",6);
            }
            if( strpos($raRet['buttons'], 'cancel') !== false ) {
                $s .= "<form action='${_SERVER['PHP_SELF']}' method='post' style='display:inline'>"
                     .$this->drawFormSubst( "[[cancel]]", $raRet )
                     ."</form>"
                     .SEEDStd_StrNBSP("",6);
            }
            $s .= "</div>";
        }

        $s .= @$raRet['sAfter'];

        return( $s );
    }

    private function drawFormSubst( $sTmpl, $raParms )
    {
        if( strpos( $sTmpl, "[[next]]" ) !== false ) {
            $sBtn = $this->drawBtn( $raParms, $this->GetNextStep(), 'btnHiddenParms', 'btnNext', "Next" );
            $sTmpl = str_replace( "[[next]]", $sBtn, $sTmpl );
        }

        if( strpos( $sTmpl, "[[repeat]]" ) !== false ) {
            $sBtn = $this->drawBtn( $raParms, $this->step, 'btnHiddenParms', 'btnRepeat', "Repeat" );
            $sTmpl = str_replace( "[[repeat]]", $sBtn, $sTmpl );
        }

        if( strpos( $sTmpl, "[[cancel]]" ) !== false ) {
            $sBtn = $this->drawBtn( $raParms, 0, 'btnHiddenCancelParms', 'btnCancel', "Cancel" );
            $sTmpl = str_replace( "[[cancel]]", $sBtn, $sTmpl );
        }

        return( $sTmpl );
    }

    private function drawBtn( $raParms, $step, $elHiddenParms, $elLabel, $sLabel )
    {
        $sBtn = SEEDForm_Hidden( 'c01step', $step );

        if( isset($raParms[$elHiddenParms]) ) {
            foreach( $raParms[$elHiddenParms] as $k => $v ) {
                $sBtn .= SEEDForm_Hidden( $k, $v );
            }
        }
        $l = SEEDStd_ArraySmartVal( @$raParms, $elLabel, array($sLabel) );
        $sBtn .= "<input type='submit' value='$l'>";

        return( $sBtn );
    }

    function DrawNextButton( $raButtonText, $raHidden = array(), $raHiddenCancel = array(), $sNextForm = "" )
    /********************************************************************************************************
        raButtonText:
            next   => label for button going to next step
            repeat => label for button repeating this step
            cancel => label for button going back to home state
        raHidden:
            array of other hidden parms to pack into the forms
        raHiddenCancel:
            like raHidden but for the cancel button (if == NULL use raHidden)
     */
    {
        $sHidden = "";
        foreach( $raHidden as $k => $v ) {
            $sHidden .= SEEDForm_Hidden( $k, $v );
        }

        $sHiddenCancel = "";
        if( is_array($raHiddenCancel) ) {
            foreach( $raHiddenCancel as $k => $v ) {
                $sHiddenCancel .= SEEDForm_Hidden( $k, $v );
            }
        } else {
            $sHiddenCancel = $sHidden;
        }


        $s = "<DIV>";
        if( ($lNext = @$raButtonText['next']) ) {
            $s .= "<FORM action='${_SERVER['PHP_SELF']}' method='post' style='display:inline'>"
                 .SEEDForm_Hidden( 'c01step', $this->GetNextStep() )
                 .$sHidden
                 ."<INPUT type='submit' value='$lNext'>"
                 ."</FORM>"
                 .SEEDStd_StrNBSP("",6);
        }
        if( ($lRepeat = @$raButtonText['repeat']) ) {
            $s .= "<FORM action='${_SERVER['PHP_SELF']}' method='post' style='display:inline'>"
                 .SEEDForm_Hidden( 'c01step', $this->step )
                 .$sHidden
                 ."<INPUT type='submit' value='$lRepeat'>"
                 ."</FORM>"
                 .SEEDStd_StrNBSP("",6);
        }
        if( ($lCancel = @$raButtonText['cancel']) ) {
            $s .= "<FORM action='${_SERVER['PHP_SELF']}' method='post' style='display:inline'>"
                 .$sHiddenCancel
                 ."<INPUT type='submit' value='$lCancel'>"
                 ."</FORM>";
        }

        $s .= "</DIV>";


        return( $s );
    }
}



/* EXAMPLE

include("../../seeds.ca/site.php"); include("../siteStart.php");
list($kfdb,$sess) = SiteStartSession();


class MyConsole extends Console01
{
function MyConsole( &$kfdb, &$sess, &$raParms ) { parent::__construct( $kfdb, $sess, $raParms ); }

function DrawTag( $tag, $title )
{
$s = $this->getTemplate( $tag );
if( $s === NULL ) {
return( "{TAG: $tag}" );
} else {
return( $this->ExpandTemplate( $s ) );
}
}

function getTemplate( $tag )
{
$raTemplates = array( 'FORM' => "<P>Here's a [[Namespace: Tag | Title]]</P>"
."<P>And here's [[myNamespace: MyTag | another]] </P>"
."<P>And here's [[TagNoNamespace | TitleNoNamespace]] </P>"
."<P>And here's [[TagAlone]] </P>"
);
return( isset($raTemplates[$tag]) ? $raTemplates[$tag] : NULL );
}
}


$raParms = array(
"HEADER" => "Go Go Gadget Console!",
"HEADER_TAIL" => "<A HREF='dummy_link'>Don't click here</A>",
"HEADER_LINKS" => array("dummy_link2"=>"Go away"),
);


$o = new MyConsole($kfdb,$sess, $raParms);
echo $o->DrawConsole( "[[FORM]]");

*/

?>
