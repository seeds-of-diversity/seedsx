<?php

if( $_SERVER['PHP_SELF'] == "/site.php" ) exit;  // don't let anyone look at this directly


if( !defined("SITEROOT") )  { define("SITEROOT", "../"); }

define("STDROOT", SITEROOT."../");
define("SEEDCOMMON", STDROOT."seedcommon/");    // application files shared by sites
include_once( STDROOT."std.php" );
include_once( SEEDCOMMON."siteCommon.php" );


SiteCommon_init( array(
    "SITE_DB_DEF_FILE"  => (STDROOT."seeds1_def.php"),  // this file is unversioned - it contains installation-specific defs
    "SITE_LOG_ROOT"     => (STDROOT."seeds_log/"),
    "DOCREP_UPLOAD_DIR" => (STDROOT."docrep_upload1/"),

    ) );




// *** put this stuff in siteCommon

include_once( STDINC."BXStd.php" );


define("SITE_isLocal", STD_isLocal );   // phase out


define("SITEINC_STDJS", SITEINC."std/js/");     // STDINC/js or a copy that the web server can see
define("SITEIMG_STDIMG", SITEINC."std/img/");   // STDINC/img or a copy that the web server can see



// a lot of common stuff can go in seedcommon/siteInit.php or the body of siteStart.php.
//   First, set the exceptions, then include siteInit, which uses if !defined...

// use SITEROOT_REAL in siteSetup.


define("PAGE1_ROOT",     SITEROOT."page/");
define("PAGE1_TEMPLATE", PAGE1_ROOT."page1.php" );
define("PAGE2_ROOT",     SITEROOT."page/");
define("PAGE2_TEMPLATE", PAGE2_ROOT."page2.php" );

define("EV_ROOT",  SITEROOT."ev/");
define("HPD_ROOT", SITEROOT."hpd/");
define("MBR_ROOT", SITEROOT."mbr/" );
define("RL_ROOT",  SITEROOT."rl/" );
define("ADMIN_HOME", SITEROOT."login/admin.php" );

define("MBR_FORM_URL_EN", MBR_ROOT."member.php");
define("MBR_FORM_URL_FR", MBR_ROOT."membre.php");

// this points to https pages directly instead of using relative links to ensure that the end user feels safe
function site_MbrUrl( $lang ) { return( STD_isLocal ? ($lang == 'EN' ? MBR_FORM_URL_EN : MBR_FORM_URL_FR)
                                                    : ($lang == 'EN' ? "https://www.seeds.ca/mbr/member.php" : "https://www.semences.ca/mbr/membre.php" )); }

define( "CLR_BG_editEN","#e0e0e0");
define( "CLR_BG_editFR","#e0e0ff");
define( "CLR_hdr",      "#007700");

define( "CLR_green_xlight", "#d8fcd8" );
define( "CLR_green_light", "#c8ecc4" );
define( "CLR_green_med",   "#77b377" );
define( "CLR_green_dark",  "#397a37" );

define( "ALT_PAGE_WIDTH", 640 );        // for pages that don't use Page1


$SEEDSessionAuthUI_Config = array( 'urlActivation'          => 'https://www.seeds.ca/login',   // link sent in Create Account activation email
                                   'urlSendPasswordSite'    => 'https://www.seeds.ca/login',   // 'web site' in Send Password email
                                   'iActivationInitialGid1' => 3,                              // Public
                                   'bEnableCreateAccount'   => true /*false*/ );               // Random people may create accounts



// MAKE THESE FUNCTIONS OF $lang THAT RETURN THE SEEDStd_EmailAddress CODE
define( "MAILTO_Webmaster", "<A HREF='mailto:webmaster@seeds.ca'>Website Administrator</A>" );
define( "MAILTO_Office",    "<A HREF='mailto:office@seeds.ca'>Office</A>" );


/* phase out */
function std_banner1( $title )      { site_banner1( $title ); }
function std_footer( $lang = "EN" ) { echo site_footer( $lang ); }


function site_banner1( $title )
/******************************
 */
{
    /*  The empty table cell forces the <HR>s to be at least 300 wide.
     */
    echo "<TABLE align='center' border=0><TR><TD align='center'>";
    echo     "<TABLE border=0><TR><TD width=300>&nbsp;</TD></TR></TABLE>";
    echo     "<HR><H2><FONT COLOR='".CLR_hdr."'>$title</FONT></H2><HR>";
    echo "</TD></TR></TABLE><BR>";
}


function site_footer( $lang = "EN" )
/***********************************
    $lang: EN - draw English notice at left
           FR - draw French notice at left
           BOTH - draw English at left, French at right
 */
{
    $s = "";

    if( $lang != "EN" && $lang != "FR" )  $lang = "BOTH";

    // there is a css for this in page1 but non-page1 pages use this function
    $s .= "<BR><HR><DIV id='site_footer' style='font-family:Verdana,Helvetica,Arial,sans-serif;font-size:7.5pt'>";

    if( $lang != "FR" ) {
        $s .= "<DIV style='float:left;margin: 0 2em 2em 2em;'>"
             ."Copyright &copy; ". date("Y") ." <A HREF='".SITEROOT."en.php'>Seeds of Diversity Canada</A>"
             ."<BR><BR>P.O. Box 36, Stn Q, Toronto ON  M4T 2L7"
             ."<BR>905-372-8983"
             ."<BR>".SEEDStd_EmailAddress( "mail", "seeds.ca" )
             ."<BR><A HREF='http://www.seeds.ca'>www.seeds.ca</A>"
             ."</DIV>";
    }
    if( $lang != "EN" ) {
        $x = ($lang == "FR") ? "left" : "right";
        $s .= "<DIV style='float:$x;margin: 0 2em 2em 2em;'>"
             ."&copy; ". date("Y") ." <A HREF='".SITEROOT."fr.php'>Programme semencier du patrimoine Canada</A>"
             ."<BR><BR>B.P. 36, Stn Q, Toronto ON  M4T 2L7"
             ."<BR>905-372-8983"
             ."<BR>".SEEDStd_EmailAddress( "courriel", "semences.ca" )
             ."<BR><A HREF='http://www.semences.ca'>www.semences.ca</A>"
             ."</DIV>";
    }
    $s .= "</DIV>";     // id=site_footer

    return( $s );
}


?>
