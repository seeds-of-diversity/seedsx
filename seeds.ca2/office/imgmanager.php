<?php

if( !defined("SITEROOT") )  define("SITEROOT", "../");
include_once( SITEROOT."site2.php" );
include_once( SEEDAPP."imgman/ImgManager.php" );

$oApp = new SEEDAppConsole(
                array_merge( $SEEDKFDB2,
                             array( 'sessPermsRequired' => array(), //  'imgman' => 'W' ),
                                    'logdir' => SITE_LOG_ROOT )
                           )
);

ImgManagerApp( $oApp, '/home/bob/junk/imgtest/2017/02/' );


?>