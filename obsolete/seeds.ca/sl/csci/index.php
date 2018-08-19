<?php

/* Public (read-only) portal to CSCI in English or French
 *
 * $lang is defined by an including page.  If we get here directly, EN is default.
 */
define("SITEROOT","../../");
include_once( SITEROOT."site.php" );
include_once( SEEDCOMMON."siteStart.php" );
include_once( SEEDCOMMON."sl/csci.php" );
include_once( PAGE1_TEMPLATE );

$lang = site_define_lang();

$kfdb = SiteKFDB() or die( "Cannot connect to database" );
$oCSCI = new SL_CSCI( $kfdb );


Page1( array( "lang"      => $lang,
              "title"     => ($lang == "EN" ? "Canadian Seed Catalogue Inventory" : "" ),
              "tabname"   => "HPD",
            ) );


function Page1Body() {
    global $lang, $oCSCI;

    echo "<TABLE border='0' cellspacing='0' cellpadding='0'><TR><TD valign='top'>"   // main text in first col, psp list in right col
        ."<H2>Canadian Seed Catalogue Inventory</H2>";

    $sPsp = SEEDSafeGPC_GetStrPlain( "psp" );

    /* Introductory text
     */
    if( !empty($sPsp) ) {
        echo "<DIV style='border:1px solid gray; padding:1em; font-size:10pt;margin:2em;width:20em;float:right;'>"
            ."<B style='font-size:12pt'>Found what you were looking for?</B><P>Please consider making a donation. "
            ."We provide this information as a non-profit service, and we rely on donations to keep it up to date.</P>"
            ."<P style='text-align:center;margin-left:-2em;font-size:14pt;'><A href='".MBR_ROOT."' style='color:#397A37'>Donate</A></P>"
            ."<P>Seeds of Diversity sincerely thanks our sponsors who helped make this 2012 inventory possible.</P>"
            ."<DIV style='font-size:12pt'>"
            ."<P><B>Benefactors ($500+)</B></P>"
            ."<DIV style='margin-left:2em'>"
            ."<P style='clear:both;'><IMG src='../../img/companies/logo_boundary.png'  height='50'> <BR/>Boundary Garlic</P>"
            ."<P style='clear:both'><IMG src='../../img/companies/logo_mcfayden.png'  height='50'> <BR/>McFayden Seeds</P>"
            ."<P style='clear:both'>Prairie Garden Seeds</P>"
            ."</DIV>"

            ."<P style='clear:both'><B>Patrons: ($100+)</B>"
            ."<DIV style='margin-left:2em'>"
            ."<P>Prairie Orchard Organics</P>"
            ."<P>Tourne-Sol Co-operative</P>"
            ."</DIV>"

            ."<P style='clear:both'><B>Friends: ($50+)</B>"
            ."<DIV style='margin-left:2em'>"
            ."<P>Harmonic Herbs </P>"
            ."<P>Hope Seeds </P>"
            ."<P>Richter's Herbs </P>"
            ."<P>Salt Spring Seeds </P>"
            ."<P>Twin Meadows Organics </P>"
            ."<P>Urban Harvest</P>"
            ."</DIV>"

            ."</DIV></DIV>";
    }
    echo "<P>Looking for seeds?</P>"
        ."<P>This is a list of vegetable and fruit seeds that were sold in recent years by Canadian seed companies. "
        ."Click on a section in the right-hand box to see the varieties available, and the companies that sold them. "
        ."Visit these companies, buy their seeds, and enjoy a beautiful, diverse garden this summer.</P>";

    if( !empty($sPsp) ) {
        echo "<P style='border:1px solid gray; padding:1em; font-size:8pt;overflow:auto;'>"
            ."According to our records, the following varieties were offered by Canadian seed and plant companies in 2012."
            ."<BR/>This information is provided as is, to further our knowledge and conservation of food biodiversity. "
            ."Seeds of Diversity makes no claims regarding accuracy, errors, or omissions, but we appreciate any updates that you can provide.</P>"
            ."</P>"
            ."<DIV style='clear:left'>&nbsp;</DIV>";
    }


    /* Show cultivar list
     */
    if( !empty($sPsp) ) {
        echo $oCSCI->DrawSeedSourceList( $sPsp );
    }
    /* Show species list
     */
    echo "</TD><TD valign='top' style='width:20em'>"
        ."<BR/><BR/>"
        ."<DIV class='P01_navbox01'>"
        ."<H3>Vegetable and Fruit Seeds<BR/>Available in Canada</H3>"
        .$oCSCI->DrawSpeciesList()
        ."</DIV>"
        ."</TD></TR></TABLE>";
}

?>
