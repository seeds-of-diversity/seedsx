<?php

if( !defined("SITEROOT") )  define("SITEROOT", "../../");
include_once( SITEROOT."site2.php" );
include_once( SEEDAPP."EmailMeApp.php" );

$oApp = new SEEDAppConsole( $config_KFDB['seeds2'] + [ 'sessPermsRequired' => array('MBR'=>'R'),
                                                       'logdir' => SITE_LOG_ROOT ]
);

$s = (new EmailMeApp($oApp, array('logdir'=>SITE_LOG_ROOT."emailme/")))->App();

echo Console02Static::HtmlPage( $oApp->oC->DrawConsole( $s ), "", 'EN' );

?>