<?php

define( "SITEROOT", "../../" );
include_once( SITEROOT."site.php" );
include_once( SEEDCOMMON."siteStart.php" );
include_once( SEEDCOMMON."console/console01.php" );


$sHead = "";
$sBody = "";
$lang = "EN";

switch( @$_REQUEST['cmd'] ) {
    case 'More':    $sBody .= MoreText();     break;
    case 'CDForm':  $sBody .= CDFormText();   break;
}


echo Console01Static::HTMLPage( $sBody, $sHead, $lang, array( 'bBootstrap' => true, 'sCharset'=>'ISO-8859-1' ) );




function MoreText()
{
    $s = "<img src='//www.seeds.ca/bauta/logo/BFICSS-logo-en-300.png' height='60'"
        ." style='float:left;margin:20px;'/>"
        ."<img src='//www.seeds.ca/i/img/logo/logoA_h-en-750x.png' height='80'"
        ." style='float:right;margin:20px;'/>"
        ."<div style='margin:0px 20px;'>"
        ."<h2 style='clear:both'>Crop Description Records</h2>"
        ."<p>This is a crop-records system made for farmers and gardeners like you. "
        ."Help document Canada's diverse plant varieties by recording simple observations about the plants you grow. "
        ."You'll find simple multiple-choice forms in paper and web formats, that let you make systematic observations with other growers "
        ."across Canada and beyond.</p>"
        ."<h3>Why Should I Use This?</h3>"
        ."<p>If you grow unusual varieties of grains, fruit, or vegetables, and you want to share your observations about them, this is the "
        ."way to do it. The observation forms in this system give you a detailed, proven system for describing your plants and produce, and "
        ."they guide you through the process of making systematic observations that can be easily compared with other growers.</p>"
        ."<h3>How Does It Work?</h3>"
        ."<p>We have observations forms for over 20 crop species. Choose the forms that match your crops, and read the questions to familiarize "
        ."yourself with what you should look for. Some observations are made early in the season, some in mid-season, and some at harvest time.</p>"
        ."<p>All forms are available in printable format, and in web-based format, so you can choose how you want to use them.</p>"
        ."<p><img style='display:inline-block' src='img/descGridThumbMobile.png' height='30'/> Use a mobile device to enter your observations, right in the field.</p>"
        ."<p style='margin-left:40px'>OR</p>"
        ."<p><img style='display:inline-block' src='img/descGridThumb04.png' height='30'/> Use printable forms in the field and enter your observations on-line later.</p>"
        ."<h3>Where Did These Forms Come From?</h3>"
        ."<p>We wanted to make sure that the data we collect is useful, so the criteria in the forms comes from standardized crop descriptor "
        ."sets that are used by plant experts and researchers all over the world. This system is based on the most widely-used standards, so "
        ."the questions are well thought-out, the multiple-choices are comprehensive, and the information can be easily exchanged with other "
        ."groups and researchers internationally.</p>"
        ."<p>You can find lots more information at the <a href='http://pgrc3.agr.gc.ca/grinca-rirgc_e.html' target='_blank'>Germplasm Resources Information Network</a> "
        ."and <a href='http://www.bioversityinternational.org/' target='_blank'>Bioversity International</a></p>"
        ."<h3>Who Uses the Information?</h3>"
        ."<p>You do, as well as everyone else who uses this system. Click on the box called <i>See What Other People Reported</i> to find out "
        ."what growers all across Canada have reported about their crops. When you add your observations, they'll be connected here too, and "
        ."you'll contribute to an ever-growing body of knowledge about biodiverse food crops in Canada.</p>"
        ."<div class='well' style='color:#F07020;'><i><b>Privacy Note:</b> We never show your personal information or your location: only the data about your plants is shared with other people</i></div>"
        ."<h3>How Do I Get Started?</h3>"
        ."<p>Click on the <i>Get Started</i> box. If you have a web account with Seeds of Diversity, you can login right away. If not, it's "
        ."easy to create one. You'll find a place to create a profile of your farm or garden site (location, etc) and an assortment of crop "
        ."description forms for the plants you grow.</p>"
        ."</div>";

    return( $s );
}


function CDFormText()
{
    include_once( SEEDCOMMON."sl/desc/_sl_desc.php" );

    $s = "";

    if( (!$k = SEEDSafeGPC_GetInt('k')) )  return( "" );

    list($kfdb) = SiteStart();
    $ra = $kfdb->QueryRA( "SELECT * FROM sl_desc_cfg_forms WHERE _key='$k'" );
    if( !@$ra['_key'] ) return( "" );

    $lang = 'EN';

    $oDescDB   = new SL_DescDB( $kfdb, 0 ); // uid==0
    $oDescForm = new SLDescForm( $oDescDB, 0 /*kVI not defined, so all form values will be default */, $lang );
    $oDescForm->LoadDefs();

    $s .= $oDescForm->DrawFormExpandTags( $ra['form'] );

    return( $s );
}

?>
