<?
/* SiteStart - include and initialize the most common stuff
 */

include_once( SITEINC."siteKFDB.php" );
include_once( SITEINC."sodlogin.php");
include_once( STDINC."SEEDSession.php");


function SiteStart()
/*******************
    returns list($kfdb)

    Succeed or die.
 */
{
    $kfdb =& SiteKFDB() or die( "Cannot connect to database" );

    return( array($kfdb) );
}


function SiteStartAuth( $perms )
/*******************************
    returns list($kfdb,$la)

    Succeed or die.
 */
{
    $kfdb =& SiteKFDB() or die( "Cannot connect to database" );

    $la = new SoDLoginAuth;
    if( !$la->SoDLoginAuth_Authenticate( $_REQUEST, $perms ) ) { exit; }

    return( array($kfdb, $la) );
}


function SiteStartSession( $raPerms = array(), $bSSL = true )
/************************************************************
    Get a kfdb and an authenticated user session

    $raPerms is an array of perm->mode, all of which are required to permit access
    An empty array always succeeds.

    returns list($kfdb,$sess)

    Succeed or die.
 */
{
    if( $_SERVER['HTTP_HOST'] == 'localhost' ) $bSSL = false;

    if( $bSSL && $_SERVER['HTTPS'] != "on" ) {
        $ra = array();
        foreach( $_GET  as $k => $v )   $ra[] = $k."=".urlencode($v);
        foreach( $_POST as $k => $v )   $ra[] = $k."=".urlencode($v);

        header( "Location: https://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].(count($ra) ? ("?".implode('&',$ra)) : "") );
        exit;
    }

    $kfdb =& SiteKFDB() or die( "Cannot connect to database" );

    $sess = new SEEDSessionAuth( $kfdb );
    $sess->EstablishSession( $raPerms, 'SiteStartSessionLoginForm' ) or die( "Cannot login" );

    return( array($kfdb, $sess) );
}


function SiteStartSessionLoginForm( $sess, $raParms )
/****************************************************
    Callback from EstablishSession.
    $raParms contains the names of the uid and pwd parameters expected in the login form
    $sess->error is the result of initial attempts to find/create a session.
 */
{
    $bCopyGP = true;
    $destURL = $_SERVER['PHP_SELF'];


    echo "<DIV><IMG src='".SITEIMG."logo_BI.gif'></DIV>";


    switch( $sess->error ) {
        case SEEDSESSION_ERR_NOERR:                                                                                      break;
        case SEEDSESSION_ERR_GENERAL:         echo "<P>An error occurred during login.</P><H2>Please login again</H2>";  break;
        case SEEDSESSION_ERR_NOTFOUND:        echo "<H2>Please login</H2>";                                              break;
        case SEEDSESSION_ERR_EXPIRED:         echo "<P>Your session has expired. For security, please login again.</P>"; break;
        case SEEDSESSION_ERR_UID_UNKNOWN:     echo "<P>The user id or password is not recognised. Please try again.</P>";break;
        case SEEDSESSION_ERR_WRONG_PASSWORD:  echo "<P>The user id or password is not recognised. Please try again.</P>";break;
        case SEEDSESSION_ERR_PERM_NOT_FOUND:  echo "<P>You do not have permission to access this page.</P>";             break;
        default:                              echo "<P>Unknown error {$sess->error}.  Please login</P>";                 break;
    }


    echo "<FORM action='$destURL' method='post'>";

    if( $bCopyGP ) {
        foreach( $_GET as $k => $v ) {
            if( $k != $raParms['nameUID'] && $k != $raParms['namePWD'] )
                echo "<INPUT type=hidden name='$k' value='".htmlspecialchars(SEEDSafeGPC_MagicStripSlashes($v),ENT_QUOTES)."'>";
        }
        foreach( $_POST as $k => $v ) {
            if( $k != $raParms['nameUID'] && $k != $raParms['namePWD'] )
                echo "<INPUT type=hidden name='$k' value='".htmlspecialchars(SEEDSafeGPC_MagicStripSlashes($v),ENT_QUOTES)."'>";
        }
    }

    echo "<TABLE cellspacing=10>";
    echo "<TR><TD>User:</TD><TD><INPUT type='text' name='${raParms['nameUID']}'";
    if( !empty($_REQUEST[$raParms['nameUID']]) ) {
        echo " value='${_REQUEST[$raParms['nameUID']]}'";
    }
    echo "></TD></TR>";
    echo "<TR><TD>Password:</TD><TD><INPUT type='password' name='${raParms['namePWD']}'></TD></TR>";
    echo "<TR><TD>&nbsp;</TD><TD><INPUT type='submit' value='Login'></TD></TR>";
    echo "</TABLE>";
    echo "</FORM>";
}


?>
