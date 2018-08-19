<?php

/* LoginAuth.php
 *
 * Allows fairly secure password login without SSL.
 *
 * Copyright Seeds of Diversity Canada 2004.  Distributed under the GPL licence.
 *
 * Based on Paul Johnson's description of a modified CHAP login, http://pajhome.org.uk/crypt/md5/chaplogin.html
 * Based on a PHP implementation by Jakub Vr�na, jakub@vrana.cz
 * Need JavaScript MD5 library made by Paul Johnston, http://pajhome.org.uk/crypt/md5/
 *
 * How it works:
 *  Server sends a unique session id on each login.
 *  Client gets userid and password from user.
 *  On submit, client-side Javascript concatenates the password and session id, clears the password field,
 *  computes md5(sessionid + password).  userid, session id and md5 transmitted to server.
 *  Server looks up password based on userid, performs md5(sessionid + password) and compares the md5 values
 *  to authenticate the user.
 *
 * Security:
 *  Lightweight.  Although the password is never transmitted openly, the md5 and sessionid are all that are needed
 *  to authenticate, and they are transmitted openly.  The point is that the transmitted authentication values
 *  (session id and md5) are only valid for the session (which can be set to expire after N seconds).
 *  Each new session uses a new md5 for the same password.
 *
 *  So a packet sniffer could get the session id and md5 and use them immediately (during the active session), but
 *  those values would be useless after the session expired.  e.g. if the md5 were transmitted as an URL parm (not
 *  recommended, but allowed) it could be retrieved from server logs, but it would only be useful if that session
 *  were still active.
 *
 * How to use it:
 *
 *  1) CREATE TABLES
 *  2) Put the following two lines near the top of your private pages:
 *     $la = new LoginAuth;
 *     $la->LoginAuth_Authenticate( $_REQUEST, "MyPermCode" );  // MyPermCode is the code in login_perms for this page
 *  3) Extensions are available to use a non-default login form, change the session expiry, etc.
 *
 *  If valid authentication parms are not available, a login form is presented.  On submit, the page is reloaded
 *  with original parms plus new authentication parms.


DROP TABLE IF EXISTS login_sessions;
DROP TABLE IF EXISTS login_users;
DROP TABLE IF EXISTS login_groups;
DROP TABLE IF EXISTS login_perms;

CREATE TABLE login_sessions (
    sid    INTEGER NOT NULL AUTO_INCREMENT,
    ts     DATETIME,
    uid    INTEGER,
    closed BOOL NOT NULL DEFAULT 0,
    PRIMARY KEY (sid)
);

CREATE TABLE login_users (
    uid         INTEGER NOT NULL AUTO_INCREMENT,
    userid      VARCHAR(80) NOT NULL,
    realname    VARCHAR(80) NOT NULL,
    password    VARCHAR(80) NOT NULL,
    groupid     INTEGER NULL,
    perm_login  BOOL NOT NULL DEFAULT 0,     -- 1=Login allowed
    PRIMARY KEY (uid)
);
CREATE INDEX login_users_userid ON login_users(userid);

CREATE TABLE login_groups (
    groupid     INTEGER NOT NULL,
    groupname   VARCHAR(80) NOT NULL,
    PRIMARY KEY (groupid)
);

CREATE TABLE login_perms (
    uid         INTEGER,
    groupid     INTEGER,
    perm        VARCHAR(20),
    mode        VARCHAR(20)
);

INSERT INTO login_users  VALUES (1,'Admin','Administrator', 'secret123',1, 1);
INSERT INTO login_groups VALUES (1,'Admin Group' );
INSERT INTO login_perms  VALUES (1, 1, 'AdminPerms', 'RW' );

 */


function hmac_md5( $key, $data ) {
    if( extension_loaded("mhash") )
        return bin2hex( mhash( MHASH_MD5, $data, $key ) );
    //echo "mhash not installed<BR>";

    // RFC 2104 HMAC implementation for php. Hacked by Lance Rushing
    $b = 64; // byte length for md5
    if (strlen($key) > $b)
        $key = pack("H*", md5($key));
    $key = str_pad($key, $b, chr(0x00));
    $ipad = str_pad("", $b, chr(0x36));
    $opad = str_pad("", $b, chr(0x5c));
    $k_ipad = $key ^ $ipad ;
    $k_opad = $key ^ $opad;

    return md5($k_opad . pack("H*", md5($k_ipad . $data)));
}


/* private */ function _login_authenticate( $parms, $expiry_sec )
/****************************************************************

    *** Regarding escaping quotes, we assume that $parms is from GPC

    $parms['auth_sid'] = session id provided by the server before login
    $parms['auth_uid'] = userid provided by the user
    $parms['auth_md5'] = md5(sid . password)  encoded on the client side so the password is never transported in plaintext

    Ensure that the sid has not expired.
    Compare the encoded password with the stored password that the server knows for the given uid.

    $ret['ok']       = true if authenticated
    $ret['realname'] = real name of the user if authenticated
    $ret['uid']      = rowid of the user if authenticated
    $ret['perm_*']   = permissions of the user if authenticated
    $ret['error']    = error code
    $ret['errormsg'] = error string if not authenticated
 */
{
    $ret = array();
    $ret['ok'] = false;
    $ret['error'] = "NOERROR";
    $ret['errormsg'] = "";
    $ret['realname'] = "";

    $sid = intval(@$parms['auth_sid']);
    $uid = SEEDSafeGPC_MagicAddSlashes(@$parms['auth_uid']);
    $md5 = SEEDSafeGPC_MagicAddSlashes(@$parms['auth_md5']);  // escape any nasty fake parms (shouldn't be anything to escape)

    if( !$sid || empty($uid) || empty($md5) ) {
        $ret['error'] = "NOLOGINPARMS";
        $ret['errormsg'] = "Please login first";

    /* Get session record
     */
    } else if( !($raS = db_query( "SELECT * FROM login_sessions WHERE sid='$sid'" )) ) {
        $ret['error'] = "SID UNKNOWN";
        $ret['errormsg'] = "Session $sid not found.  Please login again.";
    } else if( $raS['closed'] == 1 ) {
        $ret['error'] = "SID CLOSED";
        $ret['errormsg'] = "Session $sid is closed.  Please login again.";
    } else {
        /* Verify session expiry
         */
        $now = db_query( "SELECT NOW()" );
        $t2 = strtotime($now[0]);
        $t1 = strtotime($raS['ts']);
        if( $t2 - $t1 > $expiry_sec ) {     // Unix timestamp is in seconds
            $ret['error'] = "SID EXPIRED";
            $ret['errormsg'] = "Your login has expired.  Please login again.";
        } else {
            /* Get user record
             */
            if( !($raU = db_query( "SELECT * FROM login_users WHERE userid='$uid'" )) ) {
                $ret['error'] = "UID UNKNOWN";
                $ret['errormsg'] = "User '$uid' is unknown";

            } elseif( !$raU['perm_login'] ) {
                $ret['error'] = "PERM NOLOGIN";
                $ret['errormsg'] = "User '$uid' does not have Login permission";

            } elseif( hmac_md5( $sid, $raU['password'] ) != $md5 ) {
                $ret['error'] = "PASSWORD BAD";
                $ret['errormsg'] = "Incorrect password";

            } elseif( $raS['uid'] && $raS['uid'] != $raU['uid'] ) {
                $ret['error'] = "REUSED SESSION";
                $ret['errormsg'] = "This session is already in use.  Please close your browser and login again.";

            } else {
                if( !$raS['uid'] )  db_exec( "UPDATE login_sessions SET uid='${raU['uid']}' WHERE sid='$sid'" );

                $ret['realname'] = $raU['realname'];
                $ret['uid']      = $raU['uid'];
                $ret['groupid']  = $raU['groupid'];

                /* copy the values of the perm_* fields
                 */
                foreach( $raU as $k => $v ) {
                    if( !strncmp( $k, "perm_", 5 ) ) {
                        $ret[$k] = $v;
                    }
                }
                $ret['ok'] = true;
            }
        }
    }

    return( $ret );
}


function LoginAuth_GetUserName( $uid )
/*************************************
 */
{
    switch( $uid ) {
        case 0:     $rn = "Nobody"; break;
        case 1:     $rn = "Bob";    break;
        case 2:     $rn = "Judy";   break;
        default:    $rn = db_query1( "SELECT realname FROM login_users WHERE uid=$uid" );    break;
    }
    return( $rn );
}


function LoginAuth_NewSession()
/******************************
 */
{
    $auth_sid = db_insert_autoinc_id( "INSERT INTO login_sessions (sid, ts) VALUES (NULL, NOW())" );
    if( !$auth_sid )  die( "<P>Cannot create new login session</P>" );
    return( $auth_sid );
}


class LoginAuth
/**************
    How cookie-parms work:
        This function:
            - authenticates on $_REQUEST (parms can be anywhere in GPC)
            - looks for valid parms in $_COOKIE
            - if found, does nothing.  UrlParms and HiddenFormParms do nothing because cookies will be sent to next page.
              if not found, attempts to set cookies for the next page.  UrlParms and HiddenFormParms output parms in case
              cookies are not successfully set.

        The login form always posts parms (or it could put them on the url).  This function picks up the parms in
        POST (or GET) and if authenticates, tries to set the parms in cookies and then outputs the parms in UrlParms
        and HiddenFormParms anyway.  If cookies are successfully set, the next page authenticates on $_REQUEST
        somehow (depending on the order of GPC) and does not set cookies or output UrlParms or HiddenFormParms.
        If cookies are not successfully set, then the UrlParms and HiddenFormParms keep working on every page.
        When cookies expire, the next page fails to authenticate so the optional relogin function is called.
 */
{
    var $authenticated = false;
    var $auth_sid = "";             // session id
    var $auth_uid = "";             // user id
    var $auth_md5 = "";             // encrypted sid+password
    var $realname = "";             // user's real name
    var $error = "";                // error code
    var $errormsg = "";             // error message

    var $_la;
    var $_bValidCookieParms = false;
    var $_auth_hidden;
    var $_auth_urlparms;

    function LoginAuth_RealName()       { return( $this->realname ); }
    function LoginAuth_UID()            { return( $this->_la['uid'] ); }

    function LoginAuth_CanRead($perm)   { return( $this->getPermission( $perm, "R" ) ); }
    function LoginAuth_CanEdit($perm)   { return( $this->getPermission( $perm, "W" ) ); }

    function LoginAuth_GetHidden()      { return( $this->login_auth_get_hidden() ); }
    function LoginAuth_GetUrlParms()    { return( $this->login_auth_get_urlparms() ); }

    function LoginAuth_Authenticate( $parms, $perms="", $fnLoginForm="", $expiry = 3600 ) {
        $this->_la = _login_authenticate( $parms, $expiry );
        if( $this->_la['ok'] ) {
            /* Check optional $perms.  Syntax is "A B ..." where A is R (read) or W (write) and B is the name of a perm_*
             */
            if( !empty( $perms ) ) {
                $ra = explode( " ", $perms );
                $i = 0;
                while( !empty($ra[$i]) ) {
                    if( $ra[$i] == "R" ) {
                        if( !$this->LoginAuth_CanRead($ra[$i+1]) )  die( "<P>You do not have permission to read this</P>" );
                    }
                    if( $ra[$i] == "W" ) {
                        if( !$this->LoginAuth_CanEdit($ra[$i+1]) )  die( "<P>You do not have permission to edit this</P>" );
                    }
                    $i += 2;
                }
            }
            $this->authenticated = true;
            $this->auth_sid = intval($parms['auth_sid']);
            $this->auth_uid = SEEDSafeGPC_MagicStripSlashes($parms['auth_uid']);      // user name might contain apostrophe, slashed by magic
            $this->auth_md5 = $parms['auth_md5'];
            $this->realname = $this->_la['realname'];
            $this->error    = $this->_la['error'];
            $this->errormsg = $this->_la['errormsg'];

            $this->_bValidCookieParms = (@$_COOKIE['auth_sid'] == $this->auth_sid);
            if( !$this->_bValidCookieParms ) {
                /* We just blindly assume that no output has occurred yet.  If it has then the cookie will not be set
                 * and UrlParms and HiddenFormParms will not write parms so the next page loaded will not know about
                 * any login.
                 */
                setcookie( "auth_sid", $this->auth_sid, time() + $expiry, "/" );
                setcookie( "auth_uid", $this->auth_uid, time() + $expiry, "/" );
                setcookie( "auth_md5", $this->auth_md5, time() + $expiry, "/" );
            }

        } else {
            // We never return from here.
            // LoginForm function should write a form that allows the user to login/relogin with a new session

            if( empty($fnLoginForm) ) {
                $fnLoginForm = "LoginAuth_LoginForm";
            }
            $fnLoginForm( $_SERVER['PHP_SELF'], true, $this->_la['error'], $this->_la['errormsg'] );
            exit;
        }

        /* Note that this currently never returns anything but true, because the relogin is enforced.
         * Clients should probably check this return value and fail on false, in case behaviour changes in future.
         */
        return( $this->authenticated );
    }

    function LoginAuth_Logout( $parms )
    /**********************************
        If a session is active, close it and clear any cookies.  Return true.
        If a session is not active, clear cookies anyway.  Return false.

        Sets realname property
     */
    {
        $bLogout = false;

        $auth_sid = intval(@$parms['auth_sid']);

        // this deletes the cookies
        setcookie( "auth_sid", "", time() - 3600, "/" );
        setcookie( "auth_uid", "", time() - 3600, "/" );
        setcookie( "auth_md5", "", time() - 3600, "/" );

        if( $auth_sid ) {
            $bLogout = db_exec( "UPDATE login_sessions SET closed=1 WHERE sid='$auth_sid'" );
            if( !$bLogout ) db_error_die();
            $this->realname = db_query1( "SELECT U.realname FROM login_users U,login_sessions S WHERE U.uid=S.uid AND S.sid='$auth_sid'" );
        }
        return( $bLogout );
    }

    function getPermission( $perm, $mode ) {
        // Return true if the current user has $perm and $mode, either in user perms or in group perms

        $ok = false;

        if( substr( $perm, 0, 5 ) == "perm_" ) {
            $perm = substr( $perm, 5 );
        }
        $usermode = db_query1( "SELECT mode FROM login_perms WHERE perm='$perm' AND uid={$this->_la['uid']}" );
        $p = strpos( $usermode, $mode );    // returns index or false
        if( !is_bool($p) ) {
            $ok = true;
        } else if( $this->_la['groupid'] ) {
            $usermode .= db_query1( "SELECT mode FROM login_perms WHERE perm='$perm' AND groupid={$this->_la['groupid']}" );
            $p = strpos( $usermode, $mode );
            $ok = !is_bool($p);
        }
        return( $ok );
    }

    function login_auth_get_hidden() {
        if( !$this->authenticated )  die( "<P>Please login</P>" );
        if( $this->_bValidCookieParms ) return( "" );

        if( empty( $this->_auth_hidden ) ) {
            $this->_auth_hidden  = "<input type=hidden name='auth_sid' value='".$this->auth_sid."'>";
            $this->_auth_hidden .= "<input type=hidden name='auth_uid' value='".$this->auth_uid."'>";
            $this->_auth_hidden .= "<input type=hidden name='auth_md5' value='".$this->auth_md5."'>";
        }
        return( $this->_auth_hidden );
    }

    function login_auth_get_urlparms() {
        if( !$this->authenticated )  die( "<P>Please login</P>" );
        if( $this->_bValidCookieParms ) return( "" );

        if( empty( $this->_auth_urlparms ) ) {
            $this->_auth_urlparms = "auth_sid=".$this->auth_sid."&auth_uid=".$this->auth_uid."&auth_md5=".$this->auth_md5;
        }
        return( $this->_auth_urlparms );
    }
}


function LoginAuth_LoginForm( $destURL, $bCopyGP = true, $err = 0, $errmsg = "" )
/********************************************************************************
    This is the default login form.

    To use a different form, specify it as a parm on LoginAuth_Authenticate
 */
{
    switch( $err ) {
        case "NOLOGINPARMS":    echo "<H2>Please Login First</H2>";     break;
        case "SID EXPIRED":     echo "<P>Your session has expired.  For security, please relogin.</P><H2>Please Login Again</H2>";  break;
        default:                echo $errmsg;                           break;
    }

?>
<noscript>
<P><FONT size=+2 color=red>This login form requires JavaScript on your web browser.  Please enable JavaScript before proceeding.</FONT></P>
</noscript>

<script src="<?php echo SITEINC_STDJS ?>md5.js"></script>
<script language="JavaScript">
function authEncode(f) {
    f['auth_md5'].value = hex_hmac_md5(f['auth_sid'].value, f['auth_tmp'].value);
    f['auth_tmp'].value = '';
    return true;
}
</script>

<?php
    $auth_sid = LoginAuth_NewSession();   if( !$auth_sid ) exit;

    echo "<form id='LoginAuth' name='LoginAuth' action='$destURL' method='post' onSubmit='return authEncode(this);'>";
    echo "<input type='hidden' name='auth_sid' value='$auth_sid'>";
    echo "<input type='hidden' name='auth_md5' value=''>";

    if( $bCopyGP ) {
        foreach( $_GET as $k => $v ) {
            if( substr($k,0,5) != "auth_" ) echo "<INPUT type=hidden name='$k' value='".htmlspecialchars(SEEDSafeGPC_MagicStripSlashes($v),ENT_QUOTES)."'>";
        }
        foreach( $_POST as $k => $v ) {
            if( substr($k,0,5) != "auth_" ) echo "<INPUT type=hidden name='$k' value='".htmlspecialchars(SEEDSafeGPC_MagicStripSlashes($v),ENT_QUOTES)."'>";
        }
    }

    echo "<table cellspacing=10>";
    echo "<tr><td>User:</td><td><input type='text' name='auth_uid' id='auth_uid'";
    if( !empty($_REQUEST['auth_uid']) ) {
        echo " value='${_REQUEST['auth_uid']}'";
    }
    echo "></td></tr>";
    echo "<tr><td>Password:</td><td><input type='password' name='auth_tmp'></td></tr>";
    echo "<tr><td>&nbsp;</td><td><input type='submit' value='Login'></td></tr>";
    echo "</table>";
    echo "</form>";

// focus() must be after form or IE has a forward-reference error.
// IE seems to ignore this though, putting focus on auth_uid even when we don't say so or when we say to put it elsewhere.
// Firefox does not focus on anything unless we tell it to.
// should be in a BODY@onLoad, but this function doesn't have a BODY

//document.forms.LoginAuth['auth_uid'].focus();  - either syntax works on FireFox, both ignored by IE
?>
<script>
document.forms.LoginAuth.auth_uid.focus();
</script>
<?php

/*
<script language="JavaScript">
<!--
if (document.<FormName>) {
   document.<FormName>.<ElementName>.focus();
}
// -->
</script>
*/


/*
<body BGCOLOR=white onLoad='if (document.forms.length > 0) { document.forms[0].elements[0].focus();
                            if (document.forms[0].elements[0].type == "text") document.forms[0].elements[0].select(); }'>

*/
}

?>
