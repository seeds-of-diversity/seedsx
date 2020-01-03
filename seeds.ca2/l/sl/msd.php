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

