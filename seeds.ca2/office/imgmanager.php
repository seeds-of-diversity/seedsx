<?php

if( !defined("SITEROOT") )  define("SITEROOT", "../");
include_once( SITEROOT."site2.php" );
include_once( SEEDAPP."imgman/ImgManager.php" );

$oApp = new SEEDAppConsole( $config_KFDB['seeds2'] + [ 'sessPermsRequired' => array(), //  'imgman' => 'W' ),
                                                       'logdir' => SITE_LOG_ROOT ]
);

ImgManagerApp( $oApp, '/home/bob/junk/imgtest/',
    [ 'imgmanlib' => [ 'fSizePercentThreshold' => 90.0,    // if filesize is reduced below this threshold, use the new file
                       'bounding_box' => 1200,             // scale down to 1200x1200 if larger
                       'jpg_quality' => 85
    ]] );

?>