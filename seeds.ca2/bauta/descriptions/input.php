<?php

/* Crop Description interface
 *
 * Copyright (c) 2012-2017 Seeds of Diversity Canada
 *
 * UI to record sites and crop descriptors
 */


// Add a new Observation. Click Save.  Click Save again - duplicate entry.
// Images not shown
// Mobile?


define( "SITEROOT", "../../" );
include_once( SITEROOT."site.php" );
include_once( SEEDCOMMON."console/console01.php" );
include_once( "descInput.php" );

// require a login to exist, but you don't need SLDesc perms
list($kfdb,$sess,$lang) = SiteStartSessionAccount( array(/*'W SLDesc'*/) ) or die( "Cannot connect to database" );


//var_dump($_REQUEST);
//$kfdb->SetDebug(1);

$sTitle = "Record Your Crop Descriptions";

$sLogo = "logo/BFICSS-logo-".($lang=='EN' ? "en" : "fr")."-300.png";
$sLogoLink = $lang=='EN' ? "http://www.seedsecurity.ca" : "http://www.semencessecures.ca";


$oDesc = new CropDescUI( $kfdb, $sess, $lang );
$oDesc->DoAction();


$sVisLeft  = $oDesc->IsModal() ? 'hidden-xs' : "";
$sVisRight = $oDesc->IsModal() ? "" : 'hidden-xs';



$sBody =
    $oDesc->Style()

   ."<a href='$sLogoLink'><img src='../$sLogo' style='float:right'/></a>"

   ."<div class='container'>"
   ."<h1><a href='".Site_path_self()."' style='text-decoration:none;color:#333;'>$sTitle</a></h1>"
   ."<p>&nbsp;</p>"
   ."<div class='row'>"
       ."<div class='col-md-2 $sVisLeft'>".$oDesc->drawMySites().$oDesc->drawMyVI()."</div>"
       ."<div class='col-md-10 $sVisRight'>".$oDesc->drawMain()."</div>"
   ."</div>"
   ."</div>";


$oC = new Console01( $kfdb, $sess, array( 'bBootstrap' => true, 'sCharset'=>'ISO-8859-1' ) );
echo $oC->DrawConsole( $sBody, false );

?>
