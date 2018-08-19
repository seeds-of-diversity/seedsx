<?php

/* Substitute template section for the home page.
 * This is included from page-tpl so all of that template's variables are available here.
 */

class DThemeSeeds_Home
{
    private $kfdb;

    function __construct()
    {
        $this->kfdb = SiteKFDB() or die( "Cannot connect to database" );
        $this->oDocRepDB = New_DocRepDB_WithMyPerms( $this->kfdb, 0, array( 'bReadonly' => true ) );
    }

    private function whatsNewGetTxtImg( $n, $bEN )
    {
        $sTxt = "";
        $sImg = "";

        $oDoc = $this->oDocRepDB->GetDocRepDoc( "web/main/home/box$n".($bEN ? "en" : "fr") );

        $sTxt = $oDoc ? $oDoc->GetText( 'PUB' ) : "";

        $sImg = $oDoc ? $oDoc->GetMetadataValue( 'photo', 'PUB' ) : "";
        if( substr($sImg,0,4) != 'http' ) {
            $sImg = "//seeds.ca/d?n=".$sImg;
        }

        return( array($sTxt, $sImg) );
    }

    function WhatsNewBoxes( $bEN )
    {
        /* Whats New boxes - CSS is explained above
         */
        list( $sTxt1, $sImg1 ) = $this->whatsNewGetTxtImg( 1, $bEN );
        list( $sTxt2, $sImg2 ) = $this->whatsNewGetTxtImg( 2, $bEN );
        list( $sTxt3, $sImg3 ) = $this->whatsNewGetTxtImg( 3, $bEN );

        $s  = "<div class='row'>"
             ."<div id='WhatsNew1' class='col-sm-4 SeedWhatsNewBox'>"
                 ."<div class='SeedWhatsNewImg'><img src='$sImg1'/></div>"
                 ."<div class='SeedWhatsNewTextBox'><div class='SeedWhatsNewText'>$sTxt1</div></div>"
                 ."</div>"
             ."<div id='WhatsNew2' class='col-sm-4 SeedWhatsNewBox'>"
                 ."<div class='SeedWhatsNewImg'><img src='$sImg2'/></div>"
                 ."<div class='SeedWhatsNewTextBox'><div class='SeedWhatsNewText'>$sTxt2</div></div>"
                 ."</div>"
             ."<div id='WhatsNew3' class='col-sm-4 SeedWhatsNewBox'>"
                 ."<div class='SeedWhatsNewImg'><img src='$sImg3'/></div>"
                 ."<div class='SeedWhatsNewTextBox'><div class='SeedWhatsNewText'>$sTxt3</div></div>"
                 ."</div>"
             ."</div>";  // row


    $s .= <<<WhatsNewScript
            <script type='text/javascript'>
            function whatsNewResize($) {
                var h1 = $("#WhatsNew1 .SeedWhatsNewImg img").height() + $("#WhatsNew1 .SeedWhatsNewText").height();
                var h2 = $("#WhatsNew2 .SeedWhatsNewImg img").height() + $("#WhatsNew2 .SeedWhatsNewText").height();
                var h3 = $("#WhatsNew3 .SeedWhatsNewImg img").height() + $("#WhatsNew3 .SeedWhatsNewText").height();
                var h = Math.max( h1, Math.max( h2, h3 ) );
                if( h > 0 ) {
                    h = h + 25;
                    $("#WhatsNew1").height(h);
                    $("#WhatsNew2").height(h);
                    $("#WhatsNew3").height(h);
                }
            }

            // this syntax allows jQuery functions to be defined in drupal templates
            (function($){
                $(window).load( function() { whatsNewResize($); });
            }(jQuery));

            (function($){
                $(window).resize( function() { whatsNewResize($); });
            }(jQuery));
            </script>
WhatsNewScript;

        return( $s );
    }

    function HomeIconsRow( $bEN, $sPathLinks, $raThemes, $sDirImg )
    {
        $txtBlue    = $bEN ? "Seed and Food Biodiversity" : "Biodiversit&eacute; alimentaire";
        $txtYellow  = $bEN ? "Pollination Canada"         : "Pollinisation Canada";
        $txtBrown   = $bEN ? "Organic Seed Production"    : "Semences biologiques";
        $txtMagenta = $bEN ? "Seed and Plant Heritage"    : "Patrimoine agricole";

        $linkBlue    = $sPathLinks.$raThemes['blue']['paths'][$bEN ? 0 : 1];
        $linkYellow  = $sPathLinks.$raThemes['yellow']['paths'][$bEN ? 0 : 1];
        $linkBrown   = $sPathLinks.$raThemes['brown']['paths'][$bEN ? 0 : 1];
        $linkMagenta = $sPathLinks.$raThemes['magenta']['paths'][$bEN ? 0 : 1];

        $s = "<div class='col-sm-3 col-xs-6'>"
                ."<a href='$linkBlue'><img src='{$sDirImg}seeds2014img/01_blue.png' class='img-responsive' style='width:75%'/></a><br/>"
                ."$txtBlue</div>"
            ."<div class='col-sm-3 col-xs-6'>"
                ."<a href='$linkYellow'><img src='{$sDirImg}seeds2014img/01_yellow.png' class='img-responsive' style='width:75%'/></a><br/>"
                ."$txtYellow</div>"
            ."<div class='clearfix visible-xs'></div>"  // make a 4x4 grid line up vertically
            ."<div class='col-sm-3 col-xs-6'>"
                ."<a href='$linkBrown'><img src='{$sDirImg}seeds2014img/01_brown.png' class='img-responsive' style='width:75%'/></a><br/>"
                ."$txtBrown</div>"
            ."<div class='col-sm-3 col-xs-6'>"
                ."<a href='$linkMagenta'><img src='{$sDirImg}seeds2014img/01_magenta.png' class='img-responsive' style='width:75%'/></a><br/>"
                ."$txtMagenta</div>";

        return( $s );
    }
}


$oDTheme = new DThemeSeeds_Home;

$s = HomeStyle()
    ."
    <section>
      <div id='SeedBody'>
        <div id='SeedContentBoxHome'>"
          .$oDTheme->WhatsNewBoxes( $tpl_bEN )."
          <br/>
          <div class='SeedHomeIcons row' style=''>"
          .$oDTheme->HomeIconsRow( $tpl_bEN, $tpl_pathHome, $tpl_raThemes, $dir_seeds2014 )."
          </div>
        </div>  <!-- SeedContentBoxHome -->
      </div>  <!-- SeedBody -->
    </section>";

echo $s;

$s = "";







function HomeStyle()
/*******************
A cool trick for stretching an image into a 4x3 aspect ratio.

    Since vertical padding % is based on width, not height, an empty div.A {width:100%;padding:75% 0 0 0;} will have a 4x3 aspect ratio
    based on its natural width.

    Then div.A img { position:absolute; left:0; right:0; top:0; bottom:0; } will be scaled to fit.

    ?? This doesn't seem to work if the img is smaller than the div though. The img shrinks to fit the div if the div is smaller,
       but the img stays at its normal size if the div is bigger.
       I added img {width:100%;} to scale it up, and that seemed to work with all cases.

    A downside is the padding messes up css height calculations somehow, or the absolute positioning makes it hard to use
    css for vertical positioning/size of the text box.


Other things to try:
    stackoverflow suggests this with background images, to crop to 4x3
    div {
        position: absolute;
        left: 0px;
        top: 0px;
        width: 100%;
        height: 75%;
        background:url(images/background.jpg) no-repeat;
        -moz-background-size:100% auto;
        -webkit-background-size:100% auto;
        background-size:100% auto;
    }

    also this will stretch a background image to 4x3
    div {
        background:url(image.jpg) no-repeat;
        -moz-background-size:100% 75%;
        -webkit-background-size:100% 75%;
        background-size:100% 75%;
    }

    and this will apparently crop it to 4x3
    div {
        background:url(image.jpg) repeat-x;
        -moz-background-size:auto 40%;
        -webkit-background-size:auto 40%;
        background-size:auto 40%;
    }
*/
{
    $s = <<<HomeStyleEnd

    <style>
    #SeedContentBoxHome  { padding:0px 10px 10px 10px; }

    /* Whats New boxes
     *
     * SeedWhatsNewBox     contains a div containing an image and a div containing text.
     * SeedWhatsNewImg     uses a padding % trick to force an image into a 4x3 aspect ratio. Unfortunately this confuses the actual height of
     *                     SeedWhatsNewBox and SeedWhatsNewTextBox so simple CSS methods of height positioning are thrown off (by the vertical
     *                     padding of this element).
     * SeedWhatsNewText    holds the text. This height varies with resizing but its height is only the height of the text so it isn't
     *                     good for showing the background. It has to be that height so the javascript calculations work.
     * SeedWhatsNewTextBox contains SeedWhatsNewText. This shows the background colour and its height is probably taller than SeedWhatsNewBox
     *                     but that container hides overflow.
     *
     * The height of SeedWhatsNewBox is set to the max of (SeedWhatsNewImg.height + SeedWhatsNewText.height) plus some padding.
     */
    .SeedWhatsNewBox     { overflow:hidden; }
    .SeedWhatsNewTextBox { background-color:#eee;border-left:2px solid #91c877;border-right:2px solid #91c877; height:100%;}
    .SeedWhatsNewText    { padding:10px; }

    /* Force the image width to fill the div width. Force the image height to be 75% of width.
     * This aspect ratio trick uses the fact that padding% is based on width, even for vertical padding.
     * It makes a 4x3 box using the padding trick, then positions/stretches the image to fill the box.
TODO: Some different value of overflow, combined with max-height:100% below, would crop the unstretched image instead.
     */
    .SeedWhatsNewImg {
        display: block;
        width: 100%;
        position: relative;
        height: 0;
        padding: 75% 0 0 0;
        overflow: hidden;
    }
    .SeedWhatsNewImg img {
        position: absolute;
        display: block;
        width:100%;
        max-width: 100%;
        //max-height: 100%;  this would preserve aspect ratio but we want the width to fill the div and the height to conform to 75% aspect ratio
        left: 0;
        right: 0;
        top: 0;
        bottom: 0;
        margin: auto;
    }

    .SeedContentHeading   { font-size:20px; padding:10px 20px; text-align:center; }
    .SeedHomeGridHeading  { font-size:20px; padding:10px 20px; text-align:center; font-weight:bold; }

    .SeedHomeIcons       { background-color:#91c877; padding:30px; margin:0 -15px; font-weight:bold }

    /* Grid items fit in the BS grid.
     * Images should be centered in each col, with a max width so they don't look huge on wide screens.
     * Captions should fit nicely under the images.
     *
     * ItemContainer centers itself in the col and enforces the max width.
     * Image         fills the Item Container
     * Caption       does text things
     */
    .SeedHomeGridItemContainer { margin: 10px auto; text-align:center; }
    .SeedHomeGridImage         { width:100%; max-width:150px; margin:0px auto;display: block; }
    .SeedHomeGridCaption       { font-weight:bold; }

    </style>

HomeStyleEnd;

    return( $s );
}


/*
    $s = "";

    [*******************
     * Top row
     *]
    $s .= "<div class='row'>"
         .SeedsHomeGridImage( $tpl_urlStore,
                              "http://www.seeds.ca/bulletin/img/2014/donate-${tpl['sLangLC']}.png" )
         .SeedsHomeGridImage( $tpl_urlStore,
                              "http://www.seeds.ca/bulletin/img/2014/join-us-${tpl['sLangLC']}.png",
                              array( 'gridClass' => "visible-xs hidden-sm hidden-md hidden-lg" ) )
         ."<div class='clearfix visible-xs'></div>"    // hint to clear in case the text makes the top row's text are different heights
         ."<div class='col-sm-6 SeedContentHeading'>"
         .($tpl_bEN ? "<strong>Seeds of Diversity's</strong> volunteers and members are saving Canada's seeds and pollinators! <strong>Join us!</strong>"
                    : ("<strong>Semences du patrimoine</strong>"
                      ." est le r&eacute;seau national d'&eacute;change et de pr&eacute;servation de semences &agrave; pollinisation libre. "
                    //  ."<p>Nous sommes un organisme sans but lucratif de jardiniers et fermiers qui produisent "
                    //  ."et pr�servent des semences de vari�t�s traditionnelles de fleurs, de l&eacute;gumes, "
                    //  ."d'herbes m�dicinales rares ou oubli�es, dans le but de sauvegarder cet important patrimoine g�n�tique.</p>"
                    ) )
         ."</div>"

         .SeedsHomeGridImage( $tpl_urlStore,
                              "http://www.seeds.ca/bulletin/img/2014/join-us-${tpl['sLangLC']}.png",
                              array( 'gridClass' => "hidden-xs" ) )
         ."</div>"; // row


    [*******************
     * Books and resources
     *]
//    $s .= "<div class='row'><div class='col-xs-12 SeedHomeGridHeading' style='margin-top:10px;margin-bottom:20px;text-align:center'>"
//         .($tpl_bEN ? "Buy a Book" : "")
//         ."</div></div>";
    $s .= "<div class='row'>"
         .SeedsHomeGridImage( $tpl_bEN ? "http://www.seeds.ca/diversity/seed-catalogue-index"
                                       : "http://www.semences.ca/diversite/indice-catalogues-semences",
                              "http://seeds.ca/d?n=www/home/csci-${tpl['sLangLC']}--600.jpg",
                              array( 'captionBottom' => ($tpl_bEN ? "Canadian Seed Catalogue Index"
                                                                  : "Indice de catalogues de semences canadiens"),
                                     'captionTop' => "<br/>" ) )
         .SeedsHomeGridImage( $tpl_bEN ? "http://seeds.ca/saveyourseeds" : "http://semences.ca/publications_francais",
                              "http://seeds.ca/d?n=pubs/cover-ssh6${tpl['sLangLC']}--150.png",
                              array( 'captionBottom' => $tpl_bEN ? "How to Save Your Own Seeds" : "La conservation des semences") )
         ."<div class='clearfix visible-xs'></div>"    // hint to clear in case the text makes the top row's text are different heights
         .SeedsHomeGridImage( "http://seeds.ca/publications",
                              "http://seeds.ca/d?n=pubs/cover-everyseed--600.png",
                              array( 'captionBottom' => "Every Seed Tells a Tale" ) )
         .SeedsHomeGridImage( $tpl_bEN ? "http://www.seeds.ca/diversity/seed-library"
                                       : "http://www.semences.ca/diversite/bibliotheque-semences",
                              "http://www.seeds.ca/d/?n=web/main_web_image_root/sl/sl001.jpg",
                              array( 'captionBottom' => ($tpl_bEN ? "Canadian Seed Library"
                                                                    : "La biblioth&egrave;que de semences canadienne") ) )
[*
         .SeedsHomeGridImage( "",
                              "",
                              array( 'captionBottom' => "How to Make a Pollinator Garden" ) )
         .SeedsHomeGridImage( "",
                              "",
                              array( 'captionBottom' => "Conserving Native Pollinators in Ontario" ) )
*]
         ."</div>";



    [*******************
     * Four pillars
     *]
    $txtBlue    = $tpl_bEN ? "Seed and Food Biodiversity" : "Biodiversit&eacute; alimentaire";
    $txtYellow  = $tpl_bEN ? "Pollination Canada"         : "Pollinisation Canada";
    $txtBrown   = $tpl_bEN ? "Organic Seed Production"    : "Semences biologiques";
    $txtMagenta = $tpl_bEN ? "Seed and Plant Heritage"    : "Patrimoine agricole";

    $linkBlue    = $tpl_pathHome.$tpl_raThemes['blue']['paths'][$tpl_bEN ? 0 : 1];
    $linkYellow  = $tpl_pathHome.$tpl_raThemes['yellow']['paths'][$tpl_bEN ? 0 : 1];
    $linkBrown   = $tpl_pathHome.$tpl_raThemes['brown']['paths'][$tpl_bEN ? 0 : 1];
    $linkMagenta = $tpl_pathHome.$tpl_raThemes['magenta']['paths'][$tpl_bEN ? 0 : 1];

    $s .= "<div class='row'>"
         .SeedsHomeGridImage( $linkBlue,    $dir_seeds2014."seeds2014img/01_blue.png",    array( 'captionBottom' => $txtBlue ) )
         .SeedsHomeGridImage( $linkYellow,  $dir_seeds2014."seeds2014img/01_yellow.png",  array( 'captionBottom' => $txtYellow ) )
         ."<div class='clearfix visible-xs'></div>"    // hint to clear in case the text makes the top row's text are different heights
         .SeedsHomeGridImage( $linkBrown,   $dir_seeds2014."seeds2014img/01_brown.png",   array( 'captionBottom' => $txtBrown ) )
         .SeedsHomeGridImage( $linkMagenta, $dir_seeds2014."seeds2014img/01_magenta.png", array( 'captionBottom' => $txtMagenta ) )
         ."</div>";

[*
        $s .= "<div class='row'>"
             ."<div class='col-sm-3 col-xs-6'><a href='$linkBlue'><img src='{$dir_seeds2014}seeds2014img/01_blue.png' style='max-width:75%'/></a><br/>$txtBlue</div>"
             ."<div class='col-sm-3 col-xs-6'><a href='$linkYellow'><img src='{$dir_seeds2014}seeds2014img/01_yellow.png' style='max-width:75%'/></a><br/>$txtYellow</div>"
             ."<div class='col-sm-3 col-xs-6'><a href='$linkBrown'><img src='{$dir_seeds2014}seeds2014img/01_brown.png' style='max-width:75%'/></a><br/>$txtBrown</div>"
             ."<div class='col-sm-3 col-xs-6'><a href='$linkMagenta'><img src='{$dir_seeds2014}seeds2014img/01_magenta.png' style='max-width:75%'/></a><br/>$txtMagenta</div>"
             ."</div>";
*]




    [*******************
     * Friends
     *]
//    $s .= "<div class='row'><div class='col-xs-12 SeedHomeGridHeading' style='margin-top:10px;margin-bottom:20px;text-align:center'>"
//         .($tpl_bEN ? "Check out our projects" : "")
//         ."</div></div>";
    $s .= "<div class='row'>"
         .SeedsHomeGridImage( $tpl_bEN ? "http://www.seedsecurity.ca" : "http://www.semencessecures.ca",
                              "http://seeds.ca/bauta/logo/BFICSS-logo-${tpl['sLangLC']}-300.png",
                              array( 'captionTop' => "<br/><br/>".($tpl_bEN ? "National<br/>partner" : "Partenaire<br/>national"),
                                     'linkExternal' => true ) )
         .SeedsHomeGridImage( $tpl_bEN ? "http://seeds.ca/ecosgn" : "http://semences.ca/ecosgn",
                              "http://www.seeds.ca/d/?n=ecosgn/logo/logo01-${tpl['sLangLC']}--300.png", array() )
         ."<div class='clearfix visible-xs'></div>"    // hint to clear in case the text makes the top row's text are different heights
         ."</div>";



    echo $s;
    ?>


<?php
function SeedsHomeGridImage( $sLink, $sImg, $raParms = array() )
{
    $sGridClass     = @$raParms['gridClass'];
    $sCaptionTop    = @$raParms['captionTop'];
    $sCaptionBottom = @$raParms['captionBottom'];
    $sLinkAttrs     = @$raParms['linkExternal'] == true ? "target='_blank'" : "";

    $s = "<div class='col-sm-3 col-xs-6 $sGridClass'>"  // $sClass is on this one so bs can visible-* or hidden-*
        ."<div class='SeedHomeGridItemContainer'>"
        .($sCaptionTop ? "<span class='SeedHomeGridCaption'>$sCaptionTop</span>" : "")
        ."<a href='$sLink' $sLinkAttrs><img alt='' src='$sImg' class='SeedHomeGridImage'/></a>"
        .($sCaptionBottom ? "<span class='SeedHomeGridCaption'>$sCaptionBottom</span>" : "")
        ."</div></div>";

    return( $s );
}
*/

?>
