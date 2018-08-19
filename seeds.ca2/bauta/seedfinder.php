<?php

// include("../app/seedfinder/index.php" );  didn't work from Bauta's iframe, just blank. Worked from seeds.ca/bauta/seedfinder.php
header( "Location: http://seeds.ca/app/seedfinder/index.php" );

exit;
?>
<?php

/* seedfinder
 *
 * Copyright 2012-2013 Seeds of Diversity Canada
 *
 * Interface to show sources and distribution of seeds.
 */


include( "../site.php" );
include( SEEDCOMMON."sl/sl_sources_common.php" );

list($kfdb) = SiteStart();

$lang = site_define_lang(); //  SEEDSafeGPC_GetStrPlain( 'lang' );

$o = new SLSourcesUI( $kfdb, array( "linkToMe" => "?", 'lang'=>$lang ) );

$bSEEDIFrame = true;

$s =
     "<style>"
    ."body {"
        ."font-family: PT Sans,Trebuchet, Arial, Tahoma;"
        ."color:#A98875;"
        ."background-color:#fff;"
    ."}"
    ."p, td p {"
        ."font-family: PT Sans, Arial, Tahoma;"
        //."font-size:1.3em;"
        //."line-height: 1.3;"
    ."}"
    .".sod_srcui_modebox {"
        ."font-size:12px;"
    ."}"
    ."</style>";

if( $bSEEDIFrame ) {
} else {
$s .=
     "<table border='0'><tr valign='center'>"
    ."<td><img src='logo/".($lang=='FR' ? "BFICSS-logo-fr-300x117.png" : "BFICSS-logo-en-300x117.png")."' style='float:left'/></td>"
    ."<td><h1>".($lang=='FR' ? "Localisateur de semences &eacute;cologiques" : "Ecological Seed Finder")."</h1></td>"
    ."</tr></table>";
}

$s .=
     "<div class='sod-seedfinder' style='clear:left;width:840px;margin:0 auto'>"
    //.$o->Style()
    .$o->DrawDrillDown()
    ."</div>";


echo $s;


/*

It's pretty easy to confuse the UI and get a null output (choose a species, then a region, then click on a variety, then change region).


There is a way to alter the <head> tag of a Joomla page during the
execution of a given module.

Look into JDocument http://docs.joomla.org/JDocument

My suggestion would be to add the JavaScript files dynamically with
JDocument.

*/

?>
