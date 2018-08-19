<?php

/* Crop Description interface
 *
 * Copyright (c) 2014-2015 Seeds of Diversity Canada
 *
 * UI entry point for iframes
 */

define( "SITEROOT", "../../" );
include_once( SITEROOT."site.php" );
include_once( SEEDCOMMON."console/console01.php" );
include_once( "descUI.php" );

$oCD = new CropDesc();
$sHead = "<title>Record Your Own Crop Descriptions</title>"
        .$oCD->Style()
        .$oCD->Script();

$sBody = $oCD->DrawBody();

echo Console01Static::HTMLPage( $sBody, $sHead, $oCD->lang,
                                array( 'bBootstrap' => true, 'sCharset'=>'ISO-8859-1',
                                       'sBodyAttr'=>"style='background:none transparent;'" ) );

?>
