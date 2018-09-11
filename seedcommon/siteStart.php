<?php

/* SiteStart - include and initialize the most common stuff
 */

include_once( "siteKFDB.php" );
include_once( "siteutil.php" );     // MailFromOffice
include_once( STDINC."SEEDSession.php");
include_once( STDINC."SEEDSessionAccount.php");
include_once( STDINC."SEEDSessionAuthUI.php");
include_once( STDINC."SEEDForm.php");
include_once( "console/console01.php" );  // HTMLPage()
include_once( "siteCommon.php" );  // site_define_lang()

/*
class siteSEEDSessionAuthUI extends SEEDSessionAuthUI
[**************************
    Implement our own UI for login, logout, create account, password send, password change
 *]
{
    private $sUrlHome = "";
    private $sUrlLogin = "";

    function siteSEEDSessionAuthUI( $kfdb )
    {
        parent::__construct( $kfdb, "EN" );

        $this->sUrlHome  = SITE_LOGIN_ROOT;
        $this->sUrlLogin = SITE_LOGIN_ROOT;
    }

    function DrawLoginForm( $sErrMsg, $raParms = array() )
    [*****************************************************
        $raParms: valueUID = the recent userid value if any, to pre-populate the userid in the form
     *]
    {
        $s = "";
        $bCopyGP = true;    // propagate http parms so a relogin happens seamlessly
        $destURL = $_SERVER['PHP_SELF'];
        $sUid = @$raParms['valueUID'];

        $s .= "<FORM action='$destURL' method='post'>";

        if( $bCopyGP ) {
            // Some (all?) browsers keep the url address intact on the next page when you submit a POST form, so these parms should be propagated anyway
            foreach( $_GET as $k => $v ) {
                if( $k != $this->httpNameUID && $k != $this->httpNamePWD )
                $s .= "<INPUT type=hidden name='$k' value='".SEEDStd_HSC(SEEDSafeGPC_MagicStripSlashes($v))."'>";
            }
            foreach( $_POST as $k => $v ) {
                if( $k != $this->httpNameUID && $k != $this->httpNamePWD )
                $s .= "<INPUT type=hidden name='$k' value='".SEEDStd_HSC(SEEDSafeGPC_MagicStripSlashes($v))."'>";
            }
        }

        // here say "Check Your Email" if account was just created

        $s .= "<TABLE border='0' cellspacing='0' cellpadding='10'>"
             ."<TR>".SEEDForm_TextTD( $this->httpNameUID, $sUid, $this->S('Your email address') )."</TR>"
             ."<TR><TD valign='top'>".$this->S('Password')."</TD>"
             ."<TD valign='top'><INPUT type='password' name='{$this->httpNamePWD}'></TD></TR>"
             ."<TR><TD valign='top'>&nbsp;</TD>"
             ."<TD valign='top'><INPUT type='submit' value='".$this->S('Login')."'></TD></TR>"
             ."</TABLE></FORM>"
             ."<BR/>"
             ."<TABLE border='0' cellspacing='0' cellpadding='20' style='border-top:1px solid #bbb'><TR>";
        if( @$this->raConfig['bEnableCreateAccount'] ) {
            $s .= "<TD valign='top'><P style='font-size:8pt'>".$this->S("Don't have an account?")."</P>"
                 ."<FORM method='post' action='${_SERVER['PHP_SELF']}'>"
                 .SEEDForm_Hidden($this->httpNameMode, 'createacct')
                 .SEEDForm_Hidden($this->httpNameUID, $sUid)
                 ."<INPUT type='submit' name='seedsession' value='".$this->S('Create an account')."' style='font-size:8pt'/></FORM></TD>";
        }
        $s .= "<TD valign='top'><P style='font-size:8pt'>".$this->S("Forgot your password?")."</P>"
             ."<FORM method='post' action='${_SERVER['PHP_SELF']}'>"
             .SEEDForm_Hidden($this->httpNameMode, 'sendpwd')
             .SEEDForm_Hidden($this->httpNameUID, $sUid)
             ."<INPUT type='submit' name='seedsession' value='".$this->S('Send me my password')."' style='font-size:8pt'/></FORM></TD>"
             ."</TR></TABLE>";

        return( $this->PageTemplate( $s, $sErrMsg ) );
    }


    function CreateAccountDrawForm( $sErrMsg = "" )
    {
        if( !@$this->raConfig['bEnableCreateAccount'] )  die();  // you shouldn't be doing this

        $s = "";

        $sUid = SEEDSafeGPC_GetStrPlain( $this->httpNameUID );   // if the user entered this earlier, propagate it to the form

        $s .= $this->S("CreateAcct_intro")
             ."<FORM method='post' action='${_SERVER['PHP_SELF']}'>"
             .SEEDForm_Hidden( $this->httpNameMode, 'createacct' )
             ."<TABLE border='0' cellspacing='0' cellpadding='10'>"
             ."<TR>".SEEDForm_TextTD( $this->httpNameUID, $sUid, $this->S('Your email address') )."</TR>"
             ."<TR>".SEEDForm_TextTD( $this->httpNamePWD.'1', "", $this->S('Enter a password'),       20, "", array('bPassword'=>true) )."</TR>"
             ."<TR>".SEEDForm_TextTD( $this->httpNamePWD.'2', "", $this->S('Enter a password again'), 20, "", array('bPassword'=>true) )."</TR>"
             ."<TR><TD valign='top'>&nbsp;</TD>"
             ."<TD valign='top'><INPUT type='submit' value='".$this->S('Create an account')."'>"
             ."[[BackToLogin]]"
             ."</TD></TR>"
             ."</TABLE></FORM>";

        return( $this->PageTemplate( $s, $sErrMsg ) );
    }

    function CreateAccountDrawResult()
    {
        return( $this->PageTemplate( $this->S( "CreateAcct_success" )."[[BackToLogin]]" ) );
    }

    function SendPasswordDrawForm( $sErrMsg = "" )
    [*********************************************
        $this->httpNameUID propagates any existing uid from the login form, for this form's initial value
        $this->httpNameUID.'1' is the uid submitted by this form
        (they're different parms so SendPassword can tell when the user submits this form)
     *]
    {
        $s = "";

        $sUid = SEEDSafeGPC_GetStrPlain( $this->httpNameUID );

        $s .= $this->S("SendPassword_intro")
             ."<FORM method='post' action='${_SERVER['PHP_SELF']}'>"
             .SEEDForm_Hidden( $this->httpNameMode, 'sendpwd' )
             .SEEDForm_Hidden( $this->httpNameUID, $sUid )
             ."<TABLE border='0' cellspacing='0' cellpadding='10'>"
             ."<TR>".SEEDForm_TextTD( $this->httpNameUID.'1', $sUid, $this->S('Your email address') )."</TR>"
             ."<TR><TD valign='top'>&nbsp;</TD>"
             ."<TD valign='top'><INPUT type='submit' value='".$this->S('Send me my password')."'>"
             ."[[BackToLogin]]"
             ."</TD></TR>"
             ."</TABLE></FORM>";

        return( $this->PageTemplate( $s, $sErrMsg ) );
    }

    function SendPasswordDrawResult()
    {
        return( $this->PageTemplate( $this->S( "SendPassword_success" )."[[BackToLogin]]" ) );
    }

    function ActivateAccountDrawResult( $bOk, $sErrMsg )
    {
        return( $this->PageTemplate( ($bOk ? $this->S( "ActivateAcct_success" ) : ($sErrMsg."<BR/><BR/>".$this->S("ActivateAcct_error")))
               ."[[BackToLogin]]" ) );
    }


    function ChangePasswordDrawForm( $sErrMsg = "" )
    [***********************************************
        This only gets called if there is a current and valid session, so $this->kfrSession can be used.
     *]
    {
        $s = $this->S("ChangePwd_intro")
             ."<FORM method='post' action='${_SERVER['PHP_SELF']}'>"
             .SEEDForm_Hidden( $this->httpNameMode, 'changepwd' )
             ."<TABLE border='0' cellspacing='0' cellpadding='10'>"
             // Must use httpNamePWD'0' instead of just httpNamePWD because MakeSession uses it and clears it (it thinks we're logging in)
             ."<TR>".SEEDForm_TextTD( $this->httpNamePWD.'0', "", $this->S('Current_password'),   20, "", array('bPassword'=>true) )."</TR>"
             ."<TR>".SEEDForm_TextTD( $this->httpNamePWD.'1', "", $this->S('New_password'),       20, "", array('bPassword'=>true) )."</TR>"
             ."<TR>".SEEDForm_TextTD( $this->httpNamePWD.'2', "", $this->S('New_password_again'), 20, "", array('bPassword'=>true) )."</TR>"
             ."<TR><TD valign='top'>&nbsp;</TD>"
             ."<TD valign='top'><INPUT type='submit' value='".$this->S('Change Password')."'>"
             ."[[Cancel]]"
             ."</TD></TR>"
             ."</TABLE></FORM>";

        return( $this->PageTemplate( $s, $sErrMsg ) );
    }

    function ChangePasswordDrawResult()
    {
        return( $this->PageTemplate( $this->S( "ChangePwd_success" )."[[BackToHome]]" ) );
    }

    function LogoutDrawResult( $bSuccess )
    {
        if( $bSuccess ) {
            $s = $this->S( "Logout_success", array($this->GetName()) );
        } else if( $this->error == SEEDSESSION_ERR_EXPIRED ) {
            $s = $this->S( "Logout_expired" );
        } else {
            // SEEDSESSION_ERR_NOTFOUND or SEEDSESSION_ERR_GENERAL
            $s = $this->S( "Logout_failed" );
        }
        $s .= "[[BackToLogin]]";

        $s = $this->PageTemplate( $s );
        return( $s );
    }

    function PageTemplate( $sBody, $sErrMsg = "" )
    {
        $sBody = str_replace( "[[BackToLogin]]", "<P style='font-size:10pt;margin-top:2em;'><A HREF='{$this->sUrlLogin}'>".$this->S('Back to Login')."</A></P>", $sBody );
        $sBody = str_replace( "[[BackToHome]]",  "<P style='font-size:10pt;margin-top:2em;'><A HREF='{$this->sUrlHome}'>".$this->S('Back Home')."</A></P>", $sBody );
        $sBody = str_replace( "[[Cancel]]",      "<P style='font-size:10pt;margin-top:2em;'><A HREF='{$_SERVER['PHP_SELF']}'>".$this->S('Cancel')."</A></P>", $sBody );


        $sHead = "";
        $sBody =
            "<div style='margin:20px'>"
           ."<img src='//www.seeds.ca/img/logo/logoA_h-en-750x.png' width='400'/><br/><br/>"
           .(!empty($sErrMsg) ? ("<div style='width:420px' class='alert alert-danger'>$sErrMsg</div>") : "" )
           .$sBody;

        $s = Console01::HTMLPage( $sBody, $sHead, $this->oL->GetLang(),
                                  // some user names will be in iso8859, and some SEEDLocal strings might be encoded this way
                                  array( 'sCharset'=>'ISO-8859-1') );
        return( $s );
    }
}
*/

function SiteStart()
/*******************
    Returns list($kfdb)

    Succeed or die.
 */
{
    $kfdb = SiteKFDB() or die( "Cannot connect to database" );

    return( array($kfdb) );
}


function SiteStartSession( $bSSL = false )
/*****************************************
    Get a kfdb and a non-authenticated session (just manages session vars).  Force SSL if necessary.
    Returns list($kfdb,$sess)

    Succeed or die.
 */
{
    if( $bSSL ) _doSSL();

    $kfdb = SiteKFDB() or die( "Cannot connect to database" );
    $sess = new SEEDSession();

    return( array($kfdb, $sess) );
}


function SiteStartSendMailKluge( $mailto, $subject, $body )
{
    // this is used to send password reminders, confirmations, etc
    return( MailFromOffice( $mailto, $subject, "", nl2br($body), array( 'from' => array('office@seeds.ca','Seeds of Diversity') )));
}

function SiteStartSessionAccount( $raPerms = array(), $raParms = array(), $bSSL = true )
{
    $oAuthUI = null;
    $bHandled = false;
    $sBody = "";

    list($kfdb,$sess,$lang) = SiteStartSessionAccountNoUI( $raPerms, $raParms, $bSSL );

    // will use the UI if there's a sessioncmd or not logged in
// should be getting 'sessioncmd' from SEEDSessionAccount_UI::httpCmdParm but seems like a waste to create it just for that - static var??
// Also there are some config parms defined in SEEDSessionAuthUI_Config which could be put here instead, or passed here instead.
// And it seems like a lot of this logic could be in SEEDSessionAccountUI anyway.

    if( ($cmd = @$_REQUEST['sessioncmd']) || !$sess->IsLogin() ) {
        global $SEEDSessionAuthUI_Config;
        $oAuthUI = new SEEDSessionAccount_UI( $kfdb, $sess,
                                              array_merge( $SEEDSessionAuthUI_Config,
                                                           array( 'fTemplates' => array(SEEDCOMMON."templates/session.html"),
                                                                  'lang'=>$lang,
                                                                  'fnSendMail' => "SiteStartSendMailKluge"
                                            )));
    }

    // regardless of IsLogin, if there's a sessioncmd try to handle it
    if( $cmd ) {
        list($bHandled,$sBody) = $oAuthUI->Command( $cmd );
    }

    // if (no sessioncmd, or sessioncmd not handled) and IsLogin, proceed as an authenticated user of the calling app
    if( !$bHandled && $sess->IsLogin() ) {
        return( array($kfdb, $sess, $lang) );
    }

    // otherwise get a login screen through SEEDSessionAccount_UI (assume it handles acctLogin)
    if( !$bHandled ) {
// not showing "Userid or password not known" / "You don't have perms" errors when you can't login
        list($bHandled,$sBody) = $oAuthUI->Command( "acctLogin" );
    }

    $sHead = ""; //$sHead = "<style>label,input.text,#SEEDSessionUser_loginButton {display:block;margin-left:50px;margin-bottom:10px;}</style>";
    echo Console01::HTMLPage( $sBody, $sHead, $lang,
                              array( 'sCharset'=>'ISO-8859-1') );
    exit;
}

function SiteStartSessionAccountNoUI( $raPerms = array(), $raParms = array(), $bSSL = true )
/*******************************************************************************************
    Same as SiteStartSessionAccount but don't go to the login screen if a user session does not already exist.
    Also, don't process sessioncmd. If you're sending sessioncmd to a page that only uses this function, you're not going to get service.

    This is for pages that behave differently if the user is logged in vs not.
 */
{
    if( $_SERVER['SERVER_NAME'] == 'www.pollinator.ca' || substr($_SERVER['SERVER_NAME'],0,3)=='new' ) { $bSSL = false; }   // kluge: no certificate

    if( $bSSL ) _doSSL();

    $lang = site_define_lang();
    $kfdb = SiteKFDB() or die( "Cannot connect to database" );
    $sess = new SEEDSessionAccount( $kfdb, $raPerms, $raParms );

    return( array($kfdb, $sess, $lang) );
}

/*
function SiteStartSessionAuth( $raPerms = array(), $bSSL = true )
[****************************************************************
    Get a kfdb and an authenticated user session.  Force SSL if necessary.
    returns list($kfdb,$sess,$lang)

    list($kfdb,$sess) can retrieve the first two items if you don't need lang
    If you want to set lang to a specific value, call site_define_lang("foo") before this function.

    $raPerms is an array of perm->mode, all of which are required to permit access | or an array of disjunctions of such arrays
    An empty array always succeeds.
 *]
{
    if( $_SERVER['SERVER_NAME'] == 'www.pollinator.ca' || substr($_SERVER['SERVER_NAME'],0,3)=='new' ) { $bSSL = false; }   // kluge: no certificate

    if( $bSSL ) _doSSL();

    $lang = site_define_lang();
    $kfdb = SiteKFDB() or die( "Cannot connect to database" );

    $sess = new siteSEEDSessionAuthUI( $kfdb );
    $sess->HandleLoginActions( $raPerms );

    return( array($kfdb, $sess, $lang) );
}

function SiteStartSessionAuthIfExists( $raPerms = array(), $bSSL = true )
[************************************************************************
    Same as SiteStartSessionAuth but:
        if a valid session already exists and perms test correctly, return a working SEEDSessionAuth
        if not logged in or perms fail, return a SEEDSession.

    This is for pages that behave differently if the user is logged in vs not.
 *]
{
    if( $_SERVER['SERVER_NAME'] == 'www.pollinator.ca' || substr($_SERVER['SERVER_NAME'],0,3)=='new' ) { $bSSL = false; }   // kluge: no certificate

    if( $bSSL ) _doSSL();

    $lang = site_define_lang();
    $kfdb = SiteKFDB() or die( "Cannot connect to database" );

// this won't do the HandleLoginActions like create account, etc, but since we're not showing a login screen it probably doesn't have to
    $sess = new SEEDSessionAuthUI( $kfdb );
    if( $sess->EstablishSession( $raPerms, NULL, array('bQuietFail'=>true) ) ) {   // bQuietFail inhibits the login screen if no user session
        $bLogin = true;
    } else {
        $sess = new SEEDSession();
        $bLogin = false;
    }
    return( array($kfdb, $sess, $bLogin, $lang) );
}

function SiteStartSessionAuth_Logout()
{
    $lang = site_define_lang();
    $kfdb = SiteKFDB() or die( "Cannot connect to database" );

// this won't do the HandleLoginActions like create account, etc, but since we're not showing a login screen it probably doesn't have to
    $sess = new siteSEEDSessionAuthUI( $kfdb );
    $sOut = $sess->LogoutUI();  // calls this class's derivation of LogoutDrawResult() to show the logout page

    return( $sOut );
}

function SiteStartSessionAuth_LogoutOLD( &$kfdb, $urlLogin = NULL )   // DEPRECATED - use SiteStartSessionControl or SEEDSessionAuthUI->HandleLogin()
/[***************************************************************
    Logout of the current session
 *]/
{
    if( $urlLogin === NULL )  $urlLogin = SITE_LOGIN_ROOT;  // index.php

    $sess = new SEEDSessionAuth( $kfdb );
    $bLogout = $sess->LogoutSession();          // if a session is active, close it and retain the user info so we can say goodbye nicely

    ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<HTML>
<HEAD>
<TITLE>Logout</TITLE>
<LINK REL="SHORTCUT ICON" HREF="http://www.seeds.ca/favicon.ico">
</HEAD>
<BODY>
    <?

    echo "<img src='//www.seeds.ca/img/logo/logoA_h-en-750x.png' width='400'/>";

    if( $bLogout ) {
        echo "<H2>Goodbye ".$sess->GetRealname()."</H2>";
        echo "<P>You are now logged out.</P>";
    } else if( $sess->error == SEEDSESSION_ERR_EXPIRED ) {
        echo "<H2>Goodbye ".$sess->GetRealname()."</H2>";       // actually, kfrSession is reset by FindSession, so name==""
        echo "<P>Your session already expired. You are now logged out.</P>";
    } else {
        // SEEDSESSION_ERR_NOTFOUND or SEEDSESSION_ERR_GENERAL
        echo "<H2>You are not logged in</H2>";
        echo "<P>If you were logged in recently, your session will expire shortly.</P>";
    }

    echo "<P>Have a nice day!</P>";
    echo "<P><A HREF='$urlLogin'>Login again</A></P>";
    echo "</BODY></HTML>";
}

function SiteStartSessionLoginForm( $sess, $raParms )
/[****************************************************
    Callback from EstablishSession.
    $raParms contains the names of the uid and pwd parameters expected in the login form, and the recent uid value if any.
    $sess->error is the result of initial attempts to find/create a session.
 *]/
{
    $bCopyGP = true;
    $destURL = $_SERVER['PHP_SELF'];


    echo "<DIV><IMG src='https://www.seeds.ca/img/logo/logoA_h-en-750x.png'></DIV><BR><BR>";

    /[* A state machine to manage password-sending.
     *      0 = default: regular login
     *      1 = user requested send-password form
     *      2 = user submitted send-password form
     *]/
    $mode = SEEDSafeGPC_Smart( 'login-x', array( 0,1,2 ) );

    if( $mode == 1 ) {
        /[* Send-password form
         *]/
        echo "<H2>We'll send you your password by email</H2>";
        echo "<P>Type your email address or membership number here and click 'Send Password'. You will receive an email shortly.</P>";
        echo "<FORM action='$destURL' method='post'>";
        echo "<INPUT type='hidden' name='login-x' value='2'>";
        echo "<TABLE cellspacing=10>";
        echo "<TR><TD>User:</TD><TD><INPUT type='text' name='login-x-uid' value='".htmlspecialchars(@$raParms['valueUID'],ENT_QUOTES)."'>"
            ."&nbsp;&nbsp;&nbsp;(your email address or membership number)</TD></TR>";
        echo "<TR><TD>&nbsp;</TD><TD><INPUT type='submit' value='Send Password'></TD></TR>";
        echo "</TABLE>";
        echo "</FORM>";

        echo "<P><A HREF='$destURL'>Back to login</A></P>";
    }

    // put this before mode==0 because it can revert to that mode
    if( $mode == 2 ) {
        /[* Send the password
         *]/
        $uid = SEEDSafeGPC_GetStrPlain("login-x-uid");
        if( empty($uid) ) {
            // fall through to the regular login form
            $mode = 0;
        } else {
            $raUserInfo = SEEDSessionAuth_Admin_GetUserInfoWithoutSession( $sess, $uid );
            if( empty($raUserInfo['password']) ) {
                echo "<H2>User not registered</H2>"
                    ."<P>User '$uid' is not registered. You might be using a different email address than the one that "
                    ."we have on file. "
                    ."Try again using a different email address, or your membership number. "
                    ."Please contact our office if you need help.</P>";
                echo "<P><A HREF='$destURL?login-x=1'>Send me my password</A></P>";
            } else {
                $sMail = "You have requested a password reminder from Seeds of Diversity. Please use the following "
                        ."to login to our web site.\n\nWeb site: ${_SERVER['SERVER_NAME']}\nUser:     $uid\nPassword: ${raUserInfo['password']}\n\n"
                        ."If you have any questions, please contact our office.";
                MailFromOffice( $uid, "Seeds of Diversity web site access", $sMail );
                MailFromOffice( "bob@seeds.ca", "Seeds of Diversity web site access", $sMail );

                echo "<H2>Your password has been sent to you by email</H2>";
                echo "<P>You should receive an email shortly containing login instructions for this web site.</P>";
                echo "<P><A HREF='$destURL'>Back to login</A></P>";
            }
        }
    }
    if( $mode == 0 ) {
        /[* Login Form
         *]/
        switch( $sess->error ) {
            case SEEDSESSION_ERR_NOERR:           break;
            case SEEDSESSION_ERR_NOSESSION:       echo "<H2>Please login</H2>";                                              break;
            case SEEDSESSION_ERR_GENERAL:         echo "<P>An error occurred during login.</P><H2>Please login again</H2>";  break;
            case SEEDSESSION_ERR_EXPIRED:         echo "<P>Your session has expired. For security, please login again.</P>"; break;
            case SEEDSESSION_ERR_UID_UNKNOWN:     echo "<P>The user id or password is not recognised. Please try again.</P>";break;
            case SEEDSESSION_ERR_WRONG_PASSWORD:  echo "<P>The user id or password is not recognised. Please try again.</P>";break;
            case SEEDSESSION_ERR_PERM_NOT_FOUND:  echo "<P>You do not have permission to access this page.</P>";             break;
            default:                              echo "<P>Unknown error {$sess->error}.  Please login</P>";                 break;
        }


        echo "<FORM action='$destURL' method='post'>";

        if( $bCopyGP ) {
            // Some (all?) browsers keep the url address intact on the next page when you submit a POST form, so these parms should be propagated anyway
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
        echo "<TR><TD>User:</TD><TD><INPUT type='text' name='${raParms['nameUID']}' value='".htmlspecialchars(@$raParms['valueUID'],ENT_QUOTES)."'>";
        echo "&nbsp;&nbsp;&nbsp;(your email address or membership number)</TD></TR>";
        echo "<TR><TD>Password:</TD><TD><INPUT type='password' name='${raParms['namePWD']}'></TD></TR>";
        echo "<TR><TD>&nbsp;</TD><TD><INPUT type='submit' value='Login'></TD></TR>";
        echo "</TABLE>";
        echo "</FORM>";

        echo "<P><A HREF='$destURL?login-x=1'>Send me my password</A></P>";
    }
}
*/

function _doSSL()
/****************
 */
{
    if( $_SERVER['HTTP_HOST'] == 'localhost' || $_SERVER['HTTP_HOST']=='heritageseeds.ca' 
                                             || $_SERVER['HTTP_HOST']=='pollinationcanada.ca' ) return;              // can't do SSL on my development machine

    if( $_SERVER['HTTPS'] != "on" ) {
        $ra = array();
        foreach( $_GET  as $k => $v )   $ra[] = $k."=".urlencode($v);
        foreach( $_POST as $k => $v )   $ra[] = $k."=".urlencode($v);

        header( "Location: https://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].(count($ra) ? ("?".implode('&',$ra)) : "") );
        exit;
    }
}


class SiteStartLoginPage
/***********************
    Draws the links of a login landing page based on a definition of links, perms, and titles
 */
{
    private $sess;
    private $lang;

    function __construct( SEEDSessionAccount $sess, $lang )
    {
        $this->sess = $sess;
        $this->lang = $lang;
    }

    function Style()
    {
        $s = <<<HEREDOC
            <STYLE>
            .loginSection {
                margin: 2em;
            }
            .loginSection h {
                font-size: 14pt;
                font-weight: normal;
            }
            .loginSection p {
                margin-left: 3em;
                font-size:10pt;
            }
            </STYLE>
HEREDOC;
        return( $s );
    }

    function DrawLogin( $raDef )
    {
        $s = "";

        foreach( $raDef as $raSection ) {
            // [0] = EN section title, [1] = FR section title, [2] = array of links
            $sBlock = "";
            $sSectionTitle = ($this->lang == "FR" && @$raSection[1]) ? $raSection[1] : $raSection[0];

            foreach( $raSection[2] as $raLink ) {
                if( $raLink[0] == 'ONE-OF' ) {
                    // Multiple links are defined for this line. Use the first one that has qualifying perms.
                    for( $i = 1; $i < count($raLink); ++$i ) {
                        if( ($sTest = $this->drawLink($raLink[$i])) ) {
                            $sBlock .= $sTest;
                            break;
                        }
                    }
                } else {
                    $sBlock .= $this->drawLink( $raLink );
                }
            }
            if( $sBlock ) {
                $s .= "<DIV class='loginSection'><H>$sSectionTitle</H>$sBlock</DIV>";
            }
        }
        return( $s );
    }

    private function drawLink( $raLink )
    {
        // [0] = link relative to SITEROOT, [1] = perm, [2] = EN label, [3] = FR label, [4] = alternate url (full path)
        $url = @$raLink[4] ? $raLink[4] : (SITEROOT.$raLink[0]);
        $perm = $raLink[1];
        $sTitle = ($this->lang == "FR" && @$raLink[3]) ? $raLink[3] : $raLink[2];

        if( $perm == "PUBLIC" || $this->sess->TestPerm( substr($perm,2), substr($perm,0,1) ) ) {
            return( "<P><A HREF='$url'>$sTitle</A></P>" );
        } else {
            return( "" );
        }
    }

    function DrawPage( $sTitle, $sHead, $sBody )
    {
        $sHead = "<TITLE>$sTitle</TITLE>"
                ."<LINK REL='SHORTCUT ICON' HREF='".SITEROOT."favicon.ico' />"
                .$this->Style()
                .$sHead;

        $sBody =
            "<div style='margin:15px'>"
           ."<img src='//seeds.ca/i/img/logo/logoA_h-".($this->lang=="EN"?"en":"fr")."-750x.png' width='400'/>"
           ."<DIV style='float:right; margin:1em 2em;'>"
           ."<A HREF='${_SERVER['PHP_SELF']}?sessioncmd=logout' style='font-size:12pt;color:green;text-decoration:none;'>Logout</A>"
           ."</DIV>"
           ."<H3>".($this->lang == "EN" ? "Welcome" : "Bienvenue")." ".$this->sess->GetName()."</H3>"
           ."<P style='margin-left:2em'>".date('Y-m-d')."</P>"
           .$sBody
           ."</div>";

        $s = Console01Static::HTMLPage( $sBody, $sHead, $this->lang,
                                  // some user names will be in iso8859, and some SEEDLocal strings might be encoded this way
                                  array( 'sCharset'=>'ISO-8859-1') );
        return( $s );
    }
}

?>
