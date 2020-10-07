<?php

/*
 * mbrAccount
 *
 * Copyright 2016 Seeds of Diversity Canada
 *
 * Manage logins, bulletin subscriptions, in concert with mbr_contacts but with either kfdb1 or kfdb2.
 *
 * Builds on top of SEEDSessionAuthDB, providing the logic for consistency between accounts, bulletin, mbr_contacts
 */

include_once( STDINC."SEEDSessionAuthDB.php" );

class MbrAccount
{
    private $kfdb;
    private $oSessUGP;

    function __construct( KeyFrameDB $kfdb, $uid )    // works with kfdb1 or kfdb2
    {
        $this->kfdb = $kfdb;
        global $config_KFDB;
        $this->oSessUGP = new SEEDSessionAuthDB( $kfdb, $uid, $config_KFDB['seeds1']['kfdbDatabase'] );
    }


/*
    private function validate( $kMbr, $sTests )
    [******************************************
     *]
    {
        $ok = true;

        foreach( explode( " ", $sTests ) as $test ) {
            switch( $test ) {
                case "AccountExists":
                    list($k, $raUserInfo) = $this->oSessUGP->GetUserInfo( $kMbr, false );
                    if( !$k ) {
                        $ok = false;
                        goto done;
                    }
                    break;

            }
        }

        goto done:
        return( $ok );
    }
*/


}

?>
