<?php

/* Support for seeds1 MSD viewers and editors.
 * This could go in seedlib/msd/ when its dependencies are there.
 */

include_once( SEEDCOMMON."sl/sed/sedCommon.php" );
include_once( SEEDCOMMON."mbr/mbrSitePipe.php" );
include_once( SEEDAPP."basket/basketProductHandlers.php" );
include_once( SEEDAPP."basket/basketProductHandlers_seeds.php" );

class MSDView extends SEDCommon
/******************************
    Derivation for the read-only public and members view of the Seed Directory
 */
{
    function __construct( SEEDApp_Worker $oW )  // kfdb is seeds1
    {
        parent::__construct( $oW->kfdb, $oW->sess, $oW->lang, $oW->sess->CanRead("sed") ? 'VIEW-MBR' : 'VIEW-PUB' );
    }

    function GetMbrContactsRA( $kMbr )   // SEDCommon::drawGrowerBlock calls back here to get the MbrContacts array for the given member
    {
        $raM = MbrSitePipeGetContactsRA2( $this->kfdb, $kMbr );

        return( $raM );
    }
}

class MSDBasketCore extends SEEDBasketCore
{
    public $oW;
    public $bIsMember;

    function __construct( SEEDApp_Worker $oW, SEEDAppConsole $oApp ) {
        // make SEEDBasketCore take $oW, stop storing it in this derived class and start storing it in the base class
        $this->oW = $oW;
        $this->bIsMbrLogin = $oW->sess->CanRead("sed");   // only members get this perm; this implies IsLogin()

        parent::__construct( $oW->kfdb, $oW->sess, $oApp,
                             //SEEDBasketProducts_SoD::$raProductTypes );
                             array( 'seeds'=>SEEDBasketProducts_SoD::$raProductTypes['seeds'] ),
                             array( 'fn_sellerNameFromUid' => array($this,"cb_SellerNameFromUid")),
                             array( 'logdir'=>SITE_LOG_ROOT ) );
    }

    function cb_SellerNameFromUid( $uidSeller )
    /******************************************
        SEEDBasketCore uses this to draw the name of a seller
     */
    {
        $ra = $this->oW->kfdb->QueryRA( "SELECT * FROM seeds.SEEDSessionUsers WHERE _key='$uidSeller'" );
        if( !($sSeller = @$ra['realname']) ) {
//kluge, not in seedapp
            include_once( SEEDCOMMON."mbr/mbrSitePipe.php" );
            $ra = MbrSitePipeGetContactsRA2( $this->oW->kfdb, $uidSeller );
            if( @$ra['firstname'] ) {
                $sSeller = SEEDCore_ArrayExpand( $ra, "[[firstname]] [[lastname]]" );
            } else if( @$ra['company'] ) {
                $sSeller = SEEDCore_ArrayExpand( $ra, "[[company]]" );
            } else {
                $sSeller = "Seller # $uidSeller";
            }
        }
        return( $sSeller );
    }
}

?>
