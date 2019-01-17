<?php

if( !defined("SITEROOT") )  define("SITEROOT", "../../");
include_once( SITEROOT."site.php" );

SiteStartSessionAccount();  // this forces login, should be replaced by something in seedlib
$oApp = SiteAppConsole();

$raGood = array(
    [ 'W sed', 'R sed'],
    [ 'W sed', 'R sed', '|'],
    [ 'W DocRepMgr', 'W sed'],
    [],
    [ 'W sed', [] ],
    [ ['W DocRepMgr', 'W sed'], '&', ['R DocRepMgr', 'R DocRepMgr'] ],
    [ 'W sed', 'W foo', '|'],
    [ '&', ['W sed', 'W foo', '|'], ['W sed', 'W DocRepMgr', '&'] ],
    [ '&', ['W foo', 'W sed', '|'], ['W sed', 'W DocRepMgr', '&'] ],
);

$raBad = array(
    [ 'W foo' ],
    [ 'W foo', '&', 'W sed' ],
    [ '&', ['W foo', 'W sed', '|'], ['W sed', 'W DocRep', '&', ['W foo']] ],
    );


$s = "<h3>These should work</h3>";
foreach( $raGood as $v ) {
    $s .= ($oApp->sess->TestPermRA($v) ? "Worked" : "Failed").SEEDCore_NBSP("",5)."</br>";
}

$s .= "<h3>These should fail</h3>";
foreach( $raBad as $v ) {
    $s .= ($oApp->sess->TestPermRA($v) ? "Worked" : "Failed").SEEDCore_NBSP("",5)."</br>";
}

echo $s;
