<?php

/* RosettaSEED entry point
 *
 * Copyright (c) 2014-2019 Seeds of Diversity Canada
 *
 */

if( !defined("SITEROOT") )  define("SITEROOT", "../../");
include_once( SITEROOT."site.php" );
include_once( SEEDCORE."console/console02.php" );
include_once( SEEDLIB."sl/sldb.php" );

$consoleConfig = [
    'CONSOLE_NAME' => "rosetta",
    'HEADER' => "RosettaSEED",
//    'HEADER_LINKS' => array( array( 'href' => 'mbr_email.php',    'label' => "Email Lists",  'target' => '_blank' ),
//                             array( 'href' => 'mbr_mailsend.php', 'label' => "Send 'READY'", 'target' => '_blank' ) ),
    'TABSETS' => ['main'=> ['tabs' => [ 'species'  => ['label'=>'Species'],
                                        'cultivar' => ['label'=>'Cultivar'],
                                      ],
                            'perms' =>[ 'species'  => [],
                                        'cultivar' => [],
                                        'ghost'   => ['A notyou'],
                                        '|'  // allows screen-login even if some tabs are ghosted
                           ],
                  ],
    ],
    'urlLogin'=>'../login/',

    'consoleSkin' => 'green',
];


$oApp = new SEEDAppConsole( $config_KFDB['seeds1']
                            + array( 'sessPermsRequired' => ['W SL'],
                                     'sessUIConfig' => ['bTmpActivate'=>true, 'bLoginNotRequired'=>false, 'fTemplates'=>[SEEDAPP.'templates/seeds_sessionaccount.html'] ],
                                     'consoleConfig' => $consoleConfig,
                                     'logdir' => SITE_LOG_ROOT )
);
$oApp->kfdb->SetDebug(1);


class MyConsole02TabSet extends Console02TabSet
{
    private $oApp;
    private $oSLDB;

    function __construct( SEEDAppConsole $oApp )
    {
        global $consoleConfig;
        parent::__construct( $oApp->oC, $consoleConfig['TABSETS'] );

        $this->oApp = $oApp;
        $this->oSLDB = new SLDBRosetta( $this->oApp );
    }

    function TabSet_main_species_ControlDraw()
    {
        return( "<div style='padding:20px'>Foo</div>" );
    }

    function TabSet_main_species_ContentDraw()
    {


        return( "<div style='padding:20px'>Bar</div>" );
    }

    function TabSet_main_cultivar_ControlDraw()
    {
        return( "<div style='padding:20px'>AAA</div>" );
    }

    function TabSet_main_cultivar_ContentDraw()
    {
        return( "<div style='padding:20px'>BBB</div>" );
    }
}

$s = "[[TabSet:main]]";

$oCTS = new MyConsole02TabSet( $oApp );

$s = $oApp->oC->DrawConsole( $s, ['oTabSet'=>$oCTS] );


echo Console02Static::HTMLPage( utf8_encode($s), "", 'EN', array( 'consoleSkin'=>'green') );   // sCharset defaults to utf8

?>
