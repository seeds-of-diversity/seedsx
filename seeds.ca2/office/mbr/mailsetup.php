<?php

if( !defined("SITEROOT") )  define("SITEROOT", "../../");
include_once( SITEROOT."site.php" );
include_once( SEEDCORE."console/console02.php" );

include_once( SEEDLIB."mail/SEEDMailer.php" );


$consoleConfig = [
        'consoleSkin' => 'green',
        'TABSETS' => ['main'=> ['tabs' => [ 'pending' => ['label'=>'Pending'],
                                            'sent'    => ['label'=>'Sent'   ],
                                            'ghost'   => ['label'=>'Ghost'  ]
                                          ],
                                // this doubles as sessPermsRequired and console::TabSetPermissions
                                'perms' =>[ 'pending' => [],
                                            'sent'    => [],
                                            'ghost'   => ['A notyou'],
                                            '|'  // allows screen-login even if some tabs are ghosted
                                          ],
] ] ];



$oApp = new SEEDAppConsole( $config_KFDB['seeds2']
                            + array( 'sessPermsRequired' => $consoleConfig['TABSETS']['main']['perms'],
                                     'consoleConfig' => $consoleConfig,
                                     'logdir' => SITE_LOG_ROOT )
);

if( !$oApp->sess->IsLogin() ) die( "Login first" );

$oMail = new SEEDMailerSetup( $oApp );

$sMailTable = "";
$sPreview = "";
$sControls = "[[TabSet:main]]"; // $oConsole->TabSetDraw( "right" )


$s = "<table cellspacing='0' cellpadding='10' style='width:100%;border:1px solid #888'><tr>"
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



$s = $oApp->oC->DrawConsole( $s );


echo Console02Static::HTMLPage( utf8_encode($s), "", 'EN', array( 'consoleSkin'=>'green') );   // sCharset defaults to utf8

?>