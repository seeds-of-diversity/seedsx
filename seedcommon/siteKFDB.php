<?php
include_once( STDINC."KeyFrame/KFDB.php" );

/* A SiteKFDB installation is defined by
 *
 *     SiteKFDB_DB       = the database name
 *     SiteKFDB_USERID   = db userid
 *     SiteKFDB_PASSWORD = db password
 *
 * In this implementation, all parms are given explicitly to KeyFrameDB (its default defs are not used).
 * HOST is always "localhost".
 *
 * The www.seeds.ca (public) installation can only access its own database, so that installation defines
 * only its default installation parms.
 *
 * The office.seeds.ca installation can access both its own and the public databases, so it defines its own database
 * as the default installation, plus
 *
 *     SiteKFDB_DB_seeds1
 *     SiteKFDB_USERID_seeds1
 *     SiteKFDB_PASSWORD_seeds1
 */


function SiteKFDB( $db = SiteKFDB_DB )
/*************************************
    Use SiteKFDB("foo") to get a connection to a non-default database
 */
{
    $ok = false;

    $host = "localhost";

    if( $db == SiteKFDB_DB ) {
        // use the default connection

        $userid = SiteKFDB_USERID;
        $password = SiteKFDB_PASSWORD;
    } else {

        switch( $db ) {
            case SiteKFDB_DB_seeds1;
                $userid   = SiteKFDB_USERID_seeds1;
                $password = SiteKFDB_PASSWORD_seeds1;
                break;

// How does this not break in seeds1 or seeds3 when this constant is undefined??
            case SiteKFDB_DB_seeds2:
                $userid   = SiteKFDB_USERID_seeds2;
                $password = SiteKFDB_PASSWORD_seeds2;
                break;
            default:
                die( "Unknown database name" );
        }
    }


    if( ($kfdb = new KeyFrameDB( "localhost", $userid, $password )) &&
        ($ok = $kfdb->Connect( $db )) )
    {
        // this is the SQL_MODE for MySQL 5.7, which is much more stringent than that shipped with MariaDB
        if( STD_isLocal ) {
            $kfdb->Execute( "SET SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION'" );
            $kfdb->SetDebug(1);
        }
    }
    return( $ok ? $kfdb : NULL );
}

?>
