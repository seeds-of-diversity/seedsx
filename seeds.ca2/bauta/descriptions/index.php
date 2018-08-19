<?php

// In multiple choice, if there is no 0 option, force it


/* Crop Description interface
 *
 * Copyright (c) 2014-2015 Seeds of Diversity Canada
 *
 * UI entry point for the direct web page (use iframe.php for applications hosted through an iframe)
 */

define( "SITEROOT", "../../" );
include_once( SITEROOT."site.php" );
include_once( SEEDCOMMON."console/console01.php" );
include_once( "descUI.php" );

$oCD = new CropDesc();
$sHead = "<title>Record Your Own Crop Descriptions</title>"
        .$oCD->Style()
        .$oCD->Script();

$sUI = $oCD->DrawBody();

$sStyle = "
    <style>
    .CropDescSheet         { width:100%; height:100%; position:absolute; top:0;left:0;bottom:0;right:0;}
    .CropDescSheetRowTop   { background:url('img/bannerH.jpg') repeat-x;background-size:auto 60px; height:60px;}
    .CropDescSheetColLeft  { background:url('img/bannerV.jpg') repeat-y;background-size:60px auto; width:60px;}
    </style>";

$sBody =
     $sStyle
    ."<table class='CropDescSheet' border='0' cellspacing='0' cellpadding='0'>"
    ."<tr><td class='CropDescSheetRowTop' colspan='2'>&nbsp;</td></tr>"
    ."<tr><td class='CropDescSheetColLeft' valign='top'>&nbsp;</td>"
    ."<td class='CropDescSheetBody' valign='top'>"
    ."<img src='http://www.seeds.ca/bauta/logo/BFICSS-logo-en-300.png' height='60'"
    ." style='float:right;margin:20px;'/>"
    ."<h2 style='margin-left:1em'>".($oCD->lang=='EN'?"Record Your Own Crop Descriptions":"Enregister une description de culture")."</h2>"
    ."<div style='padding:10%;'>"
    .$sUI
    ."</div>"
    ."</td></tr>"
    ."</table>";

echo Console01Static::HTMLPage( $sBody, $sHead, $oCD->lang, array( 'bBootstrap' => true, 'sCharset'=>'ISO-8859-1' ) );

?>