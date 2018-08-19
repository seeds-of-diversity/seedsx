<?php

defined( '_JEXEC' ) or die( 'Restricted access' );  // no direct access

$pLang = strtoupper( $params->get('modlang') );  if( $pLang != 'FR' ) $pLang = 'EN';
$pType = $params->get('modtype');

$bDebug = (@$_REQUEST['debug'] == 1);

$raParms = array();
if( $params->get('generic_a') )  $raParms[] = "sod_generic_a=".urlencode( $params->get('generic_a') );
if( $params->get('generic_b') )  $raParms[] = "sod_generic_b=".urlencode( $params->get('generic_b') );
if( $params->get('generic_c') )  $raParms[] = "sod_generic_c=".urlencode( $params->get('generic_c') );
if( $params->get('generic_d') )  $raParms[] = "sod_generic_d=".urlencode( $params->get('generic_d') );
if( $params->get('generic_e') )  $raParms[] = "sod_generic_e=".urlencode( $params->get('generic_e') );
if( $params->get('generic_f') )  $raParms[] = "sod_generic_f=".urlencode( $params->get('generic_f') );
if( $params->get('generic_g') )  $raParms[] = "sod_generic_g=".urlencode( $params->get('generic_g') );

foreach( $_REQUEST as $k => $v ) {
    $v = ( get_magic_quotes_gpc() ? stripslashes($v) : $v );

    if( substr( $k, 0, 4 ) == 'sod_' )  $raParms[] = $k."=".urlencode($v);
}


if( $pType == 'seedfinder' ) {
    $sGet = "http://www.seeds.ca/bauta/seedfinder.php?lang=$pLang&".implode( '&', $raParms );

    if( ($ch = curl_init()) ) {
        curl_setopt( $ch, CURLOPT_URL, $sGet );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        $sOutput = curl_exec( $ch );
        curl_close( $ch );
    } else if( $bDebug ) {
        $sOutput = "<p style='color:red'>Debug Client: cannot initialize CURL</p>";
    }

//    $sOutput = file_get_contents( $sGet );
} else if( $bDebug ) {
    $sOutput = "<p style='color:red'>Debug Client: unknown modtype - supported values are (seedfinder)</p>";
}

require( JModuleHelper::getLayoutPath( 'mod_seedsofdiversity' ) );
?>
