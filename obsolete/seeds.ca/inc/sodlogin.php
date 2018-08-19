<?
/* Tools to implement SoD Login
 */

include_once( "sitedb.php" );
include_once( STDINC."LoginAuth.php" );


class SoDLoginAuth extends LoginAuth {

    function SoDLoginAuth_Authenticate( $parms, $perms ) {
        return( $this->LoginAuth_Authenticate( $parms, $perms, "SodLoginForm", 7200 ) );
    }
}

function SoDLoginForm( $destURL, $bCopyGP, $err, $errmsg )
/*********************************************************
 */
{
    echo "<DIV><IMG src='".SITEIMG."logo_BI.gif'></DIV>";
    LoginAuth_LoginForm( $destURL, $bCopyGP, $err, $errmsg );
}

?>
