<?php

if( !defined("SITEROOT") )  define("SITEROOT", "../../");
include_once( SITEROOT."site.php" );
include_once( SEEDCORE."console/console02.php" );

include_once( SEEDLIB."mail/SEEDMailer.php" );

$oApp = new SEEDAppConsole( $config_KFDB['seeds1']
                            + array( 'sessPermsRequired' => array(),
                                     'logdir' => SITE_LOG_ROOT )
);

$oMail = new SEEDMailerSetup( $oApp );

$sMailTable = "";
$sPreview = "";
$sControls = ""; // $oConsole->TabSetDraw( "right" )


$s = "<table border='0' cellspacing='0' cellpadding='10' width='100%'><tr>"
    ."<td valign='top'>"
        ."<form method='post' action='${_SERVER['PHP_SELF']}'>"
        //.SEEDForm_Hidden( "p_kMail", $oMS->kMail )
        .$sMailTable
        ."</form>"
        .$sPreview
    ."</td>"
    ."<td valign='top' style='border-left:solid grey 1px;padding-left:2em;width:50%'>"
        .$sControls
    ."</td>"
    ."</tr></table>";

echo Console02Static::HTMLPage( utf8_encode($s), "", 'EN', array() );   // sCharset defaults to utf8

?>