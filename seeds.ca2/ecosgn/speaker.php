<?php

// doesn't work with new SoDMbrOrderCheckout
exit;

if( !defined("SITEROOT") )  define("SITEROOT", "../");
include_once( SITEROOT."site.php" );

include_once( SEEDCOMMON."mbr/seedCheckout.php" );

list($kfdb, $sess, $lang) = SiteStartSessionAccountNoUI();

class ECOSGN2014OrderCheckout extends SoDMbrOrderCheckout
{
    // use the SoD order mechanism, but override the form so the user can only register for ECOSGN

    function __construct( KeyFrameDB $kfdb, SEEDSession $sess, $lang )
    {
        $this->SoDMbrOrderCheckout( $kfdb, $sess, $lang, false );
    }

    function FormDrawOrderCol()
    {
        $oReg = new MbrRegistrations();

        $s = $this->FormBox( $this->oL->GetLang() != 'FR' ? "Speaker Registration for the ECOSGN 2014 Conference"
                                                          : "Enregistrement des pr&eacute;sentateurs pour le symposium de semences ECOSGN 2014",
                             $this->drawRegBody( 'ecosgn2014speaker', $oReg->raRegistrations['ecosgn2014speaker'] ) );

        return( $s );
    }
}

$oMbrOC = new ECOSGN2014OrderCheckout( $kfdb, $sess, $lang );
$tmpPageContent = $oMbrOC->Checkout();


echo "<html>"
    ."<head>"
    ."<link rel='stylesheet' type='text/css' href='".W_ROOT."os/bootstrap3/dist/css/bootstrap.min.css'></link>"
    ."</head>"
    ."<body>";

include( "page-tpl-tmp.php" );

echo "</body></html>";

