<?php
// Don't output anything until a function is called because some pages include this file before header() statements.

include_once( SITEROOT."quotes.php" );

function Page1( $parms )
/***********************
    $parms:
        lang         = EN | FR;
        tabname      = code name for the tab to highlight
        bannerstring = HTML to insert into the bottom-right cell of the border
        box{n}title  = title of {n}th box in leftcol
        box{n}text   = HTML to insert into {n}th box in leftcol
        box{n}fn     = call function within {n}th box in leftcol

        fnBody       = call function to get the body
        sBody        = body is provided as a string
 */
{
    if( @$parms['lang'] != "FR" )  $parms['lang'] = "EN";
    header( "Content-type: text/html; charset=ISO-8859-1");
    echo p1Str1( $parms );

    if( isset($parms['fnBody']) ) {
        echo $parms['fnBody']();
    } else if( isset($parms['sBody']) ) {
        echo $parms['sBody'];
    } else {
        // the caller has to define this function, and it must echo its output
        Page1Body();
    }

    echo p1Str2( $parms );
}


function Page1Str( $parms )
/**************************
    Like Page1(), but returns the complete page as a string.

    If Page1Body() is used, it must return its content as a string instead of echoing it
 */
{
    if( @$parms['lang'] != "FR" )  $parms['lang'] = "EN";
    header( "Content-type: text/html; charset=ISO-8859-1");  // hopefully nothing else has been sent yet - otherwise, can this be done with a meta tag?
    $s = p1Str1( $parms );

    if( isset($parms['fnBody']) ) {
        $s .= $parms['fnBody']();
    } else if( isset($parms['sBody']) ) {
        $s .= $parms['sBody'];
    } else {
        // the caller has to define this function, and it must return its output as a string
        $s .= Page1Body();
    }

    $s .= p1Str2( $parms );

    return( $s );
}


function p1Str1( $parms )
/************************
 */
{
    $s = "";

    $lang = @$parms["lang"];

    $sitename = $lang=="EN" ? "Seeds of Diversity" : "Semences du patrimoine";

    $s .= "<HTML><HEAD><TITLE>". $sitename . (!empty($parms["title"]) ? " - ".$parms["title"] : "") . "</TITLE>\n";
    $s .= "<LINK REL='SHORTCUT ICON' HREF='".SITEROOT."favicon.ico'>";

    foreach( $parms as $k => $v ) {
        if( substr( $k, 0, 3 ) == 'css' ) {    // $v is cssFile{,media}
            $v = explode( ',', $v );
            $s .= "<LINK REL='StyleSheet' TYPE='text/css' HREF='${v[0]}'".(empty($v[1]) ? "" : " MEDIA='${v[1]}'").">";
        }
    }

    $s .= "<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>"
         ."<meta name='Author' content='Seeds of Diversity Canada'>"
         ."<meta name='description' content='Canadian charitable organization dedicated to the conservation and documentation of horticultural biodiversity'>"
         ."<meta name='keywords' content='heritage seeds, heirloom seeds, seed saving, garden, farm, biodiversity, conservation, "
                                       ."seed list, seed catalogue, seed catalog, "
                                       ."seeds of diversity canada, heritage seed program, semences du patrimoine, "
                                       ."horticulture, agriculture, "
                                       ."tomato, bean, garlic, fruit, vegetable, flower, herb'>"
         .Page1_style()
         ."</HEAD><BODY>";

    /* ***** BANNER *****
     */
    $goHome = SITEROOT.($lang=='EN' ? 'en' : 'fr').".php";

    $s .= "<DIV id='P01_banner'>"
        ."<TABLE border='0' cellspacing='0' cellpadding='0' width='100%'><TR>"
        ."<TD width=666><A href='$goHome' style='text-decoration:none;'>"
        ."<IMG src='".SITEIMG."logo/header01a_{$lang}.png' alt='$sitename' border='0'>"
        ."</A></TD>"
        ."<TD style='background-image:url(".SITEIMG."logo/header01b.png);background-repeat:repeat-x;'>&nbsp;</TD>"
        // This is kind of complicated: we want to use image-links and anchor links within the same table cell.
        // We could use a plain background and css offsets to position the link images.  Instead, we make the link images part of the larger image to
        // simplify positioning.  Ideally, we'd put the larger image in the background and put anchor links on top of it.
        // However, background images can't be image-mapped.
        // So, we put a regular image in the cell, image map it, and write a div after it containing the anchor links. A negative margin on the div
        // pulls it up to the right place on top of the image.
        ."<TD width='350' valign='top'>"
        ."<IMG src='".SITEIMG."logo/header01c_{$lang}.png' border=0 usemap='#P01_map1'>"
        ."<DIV align='right' id='P01_contact' style='margin-top:-125px'>";
    if( $lang == "EN" ) {
        $s .= SEEDStd_EmailAddress( "mail", "seeds.ca", "Contact Us", array("subject"=>"Question for Seeds of Diversity"), "style='color:#a6d5ec'" )
             ."  |  <A style='color:#a6d5ec' HREF='".SITEROOT."fr.php'>Fran&ccedil;ais</A>";
    } else {
        $s .= SEEDStd_EmailAddress( "courriel", "semences.ca", "Contactez Nous", array("subject"=>"Question pour Semences du patrimoine"), "style='color:#a6d5ec'" )
             ."  |  <A style='color:#a6d5ec' HREF='".SITEROOT."en.php'>English</A>";
    }
    $s .= "&nbsp;&nbsp;&nbsp;</DIV></TD>"
         ."</TR></TABLE></DIV>"; // id=P01_banner

    $s .= "<map name='P01_map1'>"
         ."<area shape='rect' coords='150,125,350,160' href='".site_MbrUrl($lang)."' target='_blank'>"
         ."</map>";


    $s .= "<TABLE cellpadding='0' cellspacing='0' border='0' width='100%'><TR><TD bgcolor='". CLR_green_med ."' width='162'>"
        ."<P style='color:".CLR_green_dark."; font-size:15pt; font-weight:bold; text-align:center;"
                  ."font-family:Antique Oakland,Geneva,Verdana,Arial,Helvetica,sans-serif'>"
        ."<A style='color:".CLR_green_xlight."' href='".site_MbrUrl($lang)."'>".($lang=='EN' ? "Donate<BR/>Now" : "Donnez<BR/>Maintenant")."</A>"
        .@$parms['bannerstring']
        ."</P></TD>"
        ."<TD style='background-image:url(".SITEIMG."logo/header01d.gif);background-repeat:repeat-x;' height='96'>&nbsp;</TD></TR></TABLE>";


    /* ***** MAIN TABLE *****
     */
    $s .= "<TABLE border='0' cellspacing='0' cellpadding='4' width='100%'>"
         ."<TR>";

    /* ***** LEFTCOL *****
     */
    $s .= "<TD valign='top' id='P01_leftcol'>";


    for( $i = 1; !empty($parms["box".$i."title"]); ++$i ) {
        $boxtitle = $parms["box".$i."title"];
        $boxtext  = @$parms["box".$i."text"];
        $boxfn    = @$parms["box".$i."fn"];

        $s .= "<DIV class='P01_boxborder'><DIV class='P01_boxlabel'>$boxtitle</DIV>"
             ."<DIV class='P01_boxbody'>$boxtext";
        if( $boxfn )  $s .= $boxfn( $lang, $parms );
        $s .= "</DIV></DIV>";
    }


    // FORTUNE COOKIE
if( $lang == "EN" ) {                   // this condition should be removed when French fortune cookies are added
    $s .= "<DIV id='P01_quotebox'>";
    $q = SoD_Quote( $lang );
    $s .= $q[0];
    if( !empty($q[1]) ) $s .= "<BR>- ".$q[1];
    $s .= "</DIV>";
}

    if( $lang == "EN" ) {
        $s .= "<DIV style='font-size:8pt;margin-top:2em;'>"
            ."Seeds of Diversity thanks the<BR>"
            //."<A href='http://www.metcalffoundation.org' target='_blank'>George Cedric Metcalf Foundation</A><BR>"
            ."<A href='http://www.mcconnellfoundation.ca' target='_blank'>J.W. McConnell Family Foundation</A>,<BR>"
            ."the October Hill Foundation,<BR/>"
            ."and<BR/>"
            ."<A href='http://www.seedsecurity.ca' target='_blank'>The Bauta Family Initiative on Canadian Seed Security</A><BR>"
            ."for their support.</DIV>";
    }

    $s .= "<BR><BR><BR><DIV style='color:".CLR_green_dark."'>"
        .($lang == "EN" ? "Design by" : "Conception par" )."<BR>Allison Prindiville</DIV>";

    $s .= "</TD>";  // LEFTCOL

    /* ***** BODYCOL *****
     */
    $s .= "<TD valign='top'>";
    $s .= Page1_drawTabs( $lang, $parms );
    $s .= "<DIV id='P01_bodycol'>";

    return( $s );
}


function p1Str2( $parms )
/************************
 */
{
    return( "</DIV>"           // P01_bodycol
           ."</TD>"            // BODYCOL
           ."</TR></TABLE>"    // MAIN TABLE

            /* ***** FOOTER *****
             */
           .site_footer( $parms['lang'] )
           ."</BODY></HTML>" );
}


function Page1_draw_tab( $text, $link, $current = 0 )
/****************************************************
 */
{
    return( ($current ? "<TH>" : "<TD>")
           ."<A HREF='".SITEROOT.$link."'><nobr>$text</nobr></A>"
           .($current ? "</TH>" : "</TD>") );
}

function Page1_drawTabs( $lang, $parms )
/***************************************
 */
{
    $s = "";

    $tabs = array(
      "Home"       => array( "Home",                      "en.php",            "D'Accueil",                                "fr.php"           ),
      "ABOUT"      => array( "About Seeds of Diversity",  "info/sod/",         "De Semences du patrimoine",                "info/sdp/"        ),
      "MBR"        => array( "Membership and Order Form", "mbr/member.php",    "Formulaire d'adh&eacute;sion et bon de commande", "mbr/membre.php"   ),
      "Library"    => array( "Library",                   "lib/",              "" /*"Biblioth&egrave;que"*/,               "" /*"biblio/"*/   ),
      "Projects"   => array( "Projects",                  "proj/projects.php", "Projets",                                  "proj/projets.php" ),
//      "HPD"        => array( "Heritage Plants Database",  "hpd/",              "Heritage Plants Database",                 "hpd/"             ),
      "VEND"       => array( "Store",                     "vend/forsale.php",  "&Agrave; Vendre",                          "vend/vendre.php"  ),
      "EV"         => array( "Events",                    "ev/events.php",     "&Eacute;v&eacute;nements",                 "ev/evenements.php"),
      "Links"      => array( "Links",                     "info/links/",       "" /*"Liens"*/,                             ""                 )
    );


    $s .= "<DIV id='P01_tabs' align='center'>"
         ."<TABLE border='0' cellspacing='0' cellpadding='0'><TR>";

    foreach( $tabs as $k => $raV ) {
        $label = $raV[ $lang=="EN" ? 0 : 2 ];
        $dest  = $raV[ $lang=="EN" ? 1 : 3 ];
        if( empty($label) || empty($dest) )  continue;

        $s .= Page1_draw_tab( $label, $dest, $parms["tabname"] == $k );
    }

    $s .= "</TR></TABLE></DIV>";

    return( $s );
}

function Page1_style()
/*********************
 */
{
    return( "
<STYLE type='text/css'>

body  {
  color: #000;
  background-color: #fff;
  margin: 0;
  padding: 0;
}
body, p, td, li, ul, ol, th, td {
  font-family: Verdana, Helvetica, Arial, sans-serif;
}
h1, h2, h3, h4, h5, h6 {
  font-family: 'Trebuchet MS', Geneva, Arial, Helvetica, SunSans-Regular, Verdana, sans-serif;
  margin: 0.5em;
}

#P01_tabs {
    margin-top: 6px;
    margin-right: 2px;
    margin-left: 2px;
    padding-left: 8px;
    border-bottom: 6px ". CLR_green_light ." solid;
}

#P01_tabs td, #P01_tabs th {
    background-image: url(". SITEROOT ."page/tab.gif);
    background-repeat: no-repeat;
    padding: 3px 9px;
    font-weight: bold;
}

#P01_tabs th {
    background-color: ". CLR_green_light .";
    border-left: 1px solid #fff;
    border-right: 1px solid #333;
}

#P01_tabs td {
    background-color: ". CLR_green_med .";
    border-left: 1px solid #fff;
    border-right: 1px solid #fff;
    border-bottom: 1px solid #fff;
}

#P01_tabs th, #P01_tabs th a:link, #P01_tabs th a:visited {
    color: #555;
}

#P01_tabs td, #P01_tabs td a:link, #P01_tabs td a:visited {
    color: #fff;
}

#P01_tabs a, #P01_leftcol a {
    text-decoration: none;
}

small, div#P01_contact, div#P01_tabs th, div#P01_tabs td, #P01_leftcol {
    font-size: 7.5pt;
}

#P01_leftcol {
    width: 146px;
    background-color: ". CLR_green_light .";
    padding: 8px;
}


.P01_boxborder {
    margin-top: 6px;
    margin-bottom: 10px;
    background: #fff;
}

.P01_boxborder .P01_boxlabel {
    border-bottom: 1px solid #666;
    border-right: 2px solid #aca;
    background: #ddd;
    color: #fff;
    padding: 4px;
    background-color: ". CLR_green_med .";
    font-weight: bold;
}

.P01_boxborder .P01_boxbody {
    border-right: 2px solid #aca;
    border-bottom: 2px solid #aca;
    padding: 4px 4px 4px 0;
}

.P01_boxborder .P01_boxbody div {
    padding-bottom: .3em;
    padding-left: 1em;
}

.P01_boxborder .P01_boxbody div div {
    margin-top: .3em;
    padding-bottom: 0;
}

#P01_quotebox {
    border-right: 3px solid #aca;
    border-bottom: 3px solid #aca;
    border-top: 1px solid #aca;
    border-left: 1px solid #aca;
    margin-top: 2em;
    padding: 1em 1em 1em 1em;
    font-size: 8pt;
    background: #fff;
}

#P01_bodycol {
    padding-left: 12px;
    padding-right: 12px;
    padding-top: 10px;
    width: 95%;
    font-family:inherit;
    font-size: 11pt;
}

.P01_navbox01 {
     float:right; border: 2px green solid; font-size:10pt; margin-left:2em;padding:1em; background-color: ". CLR_green_light .";
}

</STYLE>" );
}

?>
