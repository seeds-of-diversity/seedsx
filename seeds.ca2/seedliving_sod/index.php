<?php

if( !file_exists( "go" ) )  exit;
/*
session_start();

if( @$_REQUEST['test']=='stop' ) { $_SESSION['sliv_test'] = 0; die( "Stopping" ); }

if( @$_REQUEST['test']=='start' ) { $_SESSION['sliv_test'] = 1; die ( "Starting test: go to <a href='http://seedliving.ca'>SeedLiving</a>" ); }

if( @$_SESSION['sliv_test'] != 1 ) {
    echo "<div style='width:100%;text-align:center;'>"
        ."<img src='http://seeds.ca/seedliving/i/logo.png'/>"
        ."<p style='font-family:sans serif;font-size:16pt;'>is being updated for 2016</p>"
        ."</div>";
    exit;
}
*/

// instead of Pay What You Can say "SeedLiving is a service of Seeds of Diversity, a Canadian charity that... - please consider making a donation to support..."
// put something like that at the bottom of the checkout form with suggested $2 -- click here to donate $2 to Seeds of Diversity

include_once( "sliv_main.php" );

$oSLiv->Tmpl( "SLivHome", array($tt,$gtt), array() );

?>


