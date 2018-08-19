<?

if( !defined("POLL_ROOT") )  { define("POLL_ROOT", "./"); }

define( "CLR_green_light", "#C8ECC4" );


function poll_header( $currpage, $lang = "EN" )
/********************************************
 */
{
    $link = @$_REQUEST['link'];     if( empty($link) )  $link = "php";


    echo "<HTML><HEAD><TITLE>Pollination Canada</TITLE>";
    echo "<STYLE type='text/css'>";

    echo "body       { font-family: verdana, helvetica, arial, sans-serif; }";
    echo "dt         { font-weight: bold; }";
    echo "dd         { margin-bottom: 1em; }";

    echo "#banner    { margin-top: 10px; margin-bottom: 10px; }";
    echo "#tabs      { background-color: ".CLR_green_light."; }";
    echo "#leftcol   { background-color: ".CLR_green_light."; }";
    echo "#leftcol   { padding-left: 10px; padding-right: 10px; }";
    echo "#leftcol a { text-decoration:none; }";
    echo "#leftcol .level1 { font-size: 11pt; margin-top:15px; }";
    echo "#leftcol .level2 { font-size: 9pt; margin-left:2em; margin-top:5px; }";
    echo "#bodycol   { padding-left: 20px; padding-top: 20px; font-size:11pt; }";

    echo ".img1        { padding:10px; border: 1px solid black; font-size: 9pt; }";
    echo ".img1caption { padding:10px; border: 1px solid black; font-size: 9pt; text-align: center; }";

    echo "</STYLE>";
    echo "</HEAD>";
    echo "<BODY bgcolor='#FFFFFF'>";

    echo "<DIV id='banner'>";
    echo "<A href=".POLL_ROOT." style='text-decoration:none;'><img src='img/poll_banner_en.gif' alt='Pollination Canada' border='0'></A>";
    echo "<BR><BR>";
    echo "</DIV>";


    echo "<TABLE border='0' cellspacing='0' cellpadding='0' width='100%' bgcolor='#FFFFFF' id='main'>";
    echo "<TR><TD colspan=2 id='tabs'>&nbsp;</TD></TR>";
    echo "<TR>";

    // LEFTCOL
    echo "<TD valign='top' id='leftcol' width='20%'>";
    leftcol_link( $currpage, $link, $lang, 1, "index",         "Pollination Canada" );
    leftcol_link( $currpage, $link, $lang, 1, "howto",         "Be a Pollinator Observer" );
    leftcol_link( $currpage, $link, $lang, 2, "poll_manual",   "Observer's Manual" );
    leftcol_link( $currpage, $link, $lang, 2, "poll_forms",    "Observation Forms" );
    leftcol_link( $currpage, $link, $lang, 2, "poll_submit",   "Submit Your Observations" );
    leftcol_link( $currpage, $link, $lang, 1, "why",           "Why do We Need Insects?" );
    leftcol_link( $currpage, $link, $lang, 1, "insects",       "Insect Profiles" );
    leftcol_link( $currpage, $link, $lang, 2, "i_bees",        "Bees" );
    leftcol_link( $currpage, $link, $lang, 2, "i_wasps",       "Wasps" );
    leftcol_link( $currpage, $link, $lang, 2, "i_flies",       "Flies" );
    leftcol_link( $currpage, $link, $lang, 2, "i_butterflies", "Butterflies and moths" );
    leftcol_link( $currpage, $link, $lang, 2, "i_beetles",     "Beetles" );
    leftcol_link( $currpage, $link, $lang, 1, "flowers",       "Flowers" );
    leftcol_link( $currpage, $link, $lang, 2, "f_anatomy",     "What's in a Flower?" );
    leftcol_link( $currpage, $link, $lang, 2, "f_morph",       "Six flower shapes" );
    leftcol_link( $currpage, $link, $lang, 2, "f_pollen",      "Two kinds of pollen" );
    leftcol_link( $currpage, $link, $lang, 1, "lit",           "Resources" );


    echo "<DIV class='level1'>".mySEEDStd_EmailAddress( 'info', 'pollinationcanada.ca', 'Contact Us', array('subject'=>'Question about Pollination Canada') )."</DIV>";

/*
   <table cellpadding="4" width="100%" cellspacing="0" border="0" background="/english/plantwatch/images/plantwatch_menu_background.jpg">
        <tr><td align="right">&nbsp;</td></tr>
      <tr><td align="right"><a class="pwmenu" href="/english/plantwatch/intro.html">What is PlantWatch?</a>&nbsp;</td></tr>
        <tr><td align="right"><a class="pwmenu" href="/english/plantwatch/why_monitor.html">Why Monitor Plants?</a>&nbsp;</td></tr>
        <tr><td align="right"><a class="pwmenu" href="/english/plantwatch/how_to_plantwatch.html">How To PlantWatch</a>&nbsp;</td></tr>
        <tr><td align="right"><a class="pwmenu" href="/english/plantwatch/identifying_plants.html">Identifying Plants</a>&nbsp;</td></tr>
        <tr><td align="right"><a class="pwmenu" href="/english/plantwatch/learn_plants.asp">Plant Descriptions</a>&nbsp;</td></tr>
        <tr><td align="right"><a class="pwmenu" href="/english/observations/intro.html?WatchProgram=PlantWatch">Submit Observations</a>&nbsp;</td></tr>
        <tr><td align="right"><a class="pwmenu" href="/english/plantwatch/view_results.html">View Results</a>&nbsp;</td></tr>
        <!--tr><td align="right"><a class="pwmenu" href="/cgi-bin/view_observations/view_plant_observations.asp?language=english">View Results</a>&nbsp;</td></tr-->
        <tr><td align="right"><a class="pwmenu" href="/english/plantwatch/faqs.html">Frequently Asked Questions</a>&nbsp;</td></tr>
        <tr><td align="right"><a class="pwmenu" href="/english/plantwatch/printable_observation_form.pdf">Observation Form</a>&nbsp;</td></tr>
        <tr><td align="right"><a class="pwmenu" href="/english/plantwatch/program_coordinators.html">Program Coordinators</a>&nbsp;</td></tr>
        <tr><td align="right"><a class="pwmenu" href="/english/plantwatch/glossary.html">Glossary</a>&nbsp;</td></tr>
        <tr><td align="right"><a class="pwmenu" href="/cgi-bin/quiz/plantwatch/step1.asp?language=english">PlantWatch Quiz</a>&nbsp;</td></tr>
        <tr><td align="right"><a class="pwmenu" href="/english/plantwatch/ootm/index.html">Observer of the Month</a>&nbsp;</td></tr>
        <tr><td>&nbsp;</td>
      </table>
*/

    echo "<BR><BR></TD><TD valign='top' id='bodycol'>";     // <BR> to pad bottom of leftcol if bodycontent is smaller


}


function poll_footer( $lang = "EN" )
/*********************************
 */
{
    echo "</TD></TR></TABLE>";  // bodycol

    echo "<BR>";
    echo "<TABLE border='0' cellspacing='0' cellpadding='4' width='100%' bgcolor='#FFFFFF' id='footer'>";
    echo "<TR><TD align=right valign=top nowrap>";
    echo "<A href='http://www.ec.gc.ca/'><IMG src='".POLL_ROOT."img/canada.gif' border=0 alt='Canada'></A>";
    echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
    echo "<A href='http://www.seeds.ca/'><IMG src='".POLL_ROOT."img/sodlogo.gif' border=0 alt='Seeds of Diversity Canada'></A>";
    echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
    echo "<A href='http://www.eman-rese.ca/'><IMG src='".POLL_ROOT."img/EMAN_colour_red_text_with_title.gif' border=0 alt='EMAN/RESE'></A>";
    echo "</TD></TR></TABLE>";

    echo "</BODY></HTML>";
}


function leftcol_link( $currpage, $link, $lang, $level, $pagename, $title )
/**************************************************************************
 */
{
    $style = "";
    if( $currpage == $pagename )  $style = "style='color:black; font-weight: bold;'";
    echo "<DIV class='level$level'><NOBR><A href='".POLL_ROOT."$pagename.$link' $style>$title</A></NOBR></DIV>";
}


function i_href( $name )
/***********************
 */
{
    $link = @$_REQUEST['link'];     if( empty($link) )  $link = "php";
    echo "<A href='".POLL_ROOT."$name.$link'>";
}

function mySEEDStd_EmailAddress( $s1, $s2, $label = "", $raMailtoParms = array() )
/*******************************************************************************
    Write a spam-proof email address on a web page in the form:

    <A HREF='mailto:$s1@$s2'>$label</A>  or
    <A HREF='mailto:$s1@$s2'>$s1@$s2</A> if label is blank
 */
{
    $mparms = "";
    foreach( $raMailtoParms as $k => $v ) {
        $mparms .= ( empty($mparms) ? "?" : "&" );
        $mparms .= $k."=".$v;       // I thought I should urlencode this, but Thunderbird doesn't decode it
    }

    $s = "<SCRIPT language='javascript'>var a=\"$s1\";var b=\"$s2\";";
    if( empty($label) ) {
        $s .= "var l=a+\"@\"+b;";
    } else {
        $s .= "var l=\"$label\";";
    }
    $s .= "document.write(\"<A HREF='mailto:\"+a+\"@\"+b+\"$mparms'>\"+l+\"</A>\");</SCRIPT>";
    return( $s );
}

?>
