<?php

define( "SITEROOT", "../../" );
include_once( SITEROOT."site.php" );
include_once( SEEDAPP."events/eventsApp.php" );

$oApp = SEEDConfig_NewAppConsole_LoginNotRequired( ['db'=>'seeds1', /* 'lang'=>$lang */] );
//$oApp->lang = 'FR';
SEEDPRG();

$oEvApp = new EventsApp( $oApp );

$s = $oEvApp->DrawEventsPage();

//        $s .= $oTmpl->ExpandStr( "[[SEEDContent:events]]", [] );
//$s = DrawEvents( $this->kfdb, $contentName == 'events' ? "EN" : "FR" );


$raParms = [
    'raScriptFiles' => [W_CORE_URL."js/SEEDUI.js", "dist/jquery.vmap.js", "dist/maps/jquery.vmap.canada.js"],
    'raCSSFiles'    => [W_CORE_URL."css/SEEDUI.css", "dist/jqvmap.css"]
];

echo Console02Static::HTMLPage( $s, "", $oApp->lang, $raParms );
