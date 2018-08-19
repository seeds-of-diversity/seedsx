<?php

// bugs.kde.org has the perfect create-new-account process, and a nice landing screen

// Debian did a funny thing in php4 to prevent session hijacking in multi-user computers. They disabled regular garbage collection by setting
// gc_probability to zero.  They put the session files in /var/lib/php4 instead of /tmp,
// with perms on that directory to disallow read by anyone except root, but allow w and x by anyone. That way, only root can see the directory listing,
// but the www user can write and read the files, since it knows the filenames.
// Then they set up a cron job to run every 30 minutes, as root, that gets the maximum session lifetime from all php.ini files in /etc/php4/* and
// deletes the files in /var/lib/php4 that are that old.
// So sessions disappear every 24 minutes (or 30 minutes) because a cron job is deleting them as root.
// The solution is:
//     Create an alternate session.save_path
//     Set session.save_path=??? and session.gc_probability=1 using ini_set() before session_start().
// Then, session_start() will activate PHP's garbage collector within the save_path.
// This is less secure than Debian's solution, but allows control of session lifetime.


/* SEEDSession
 *
 * Copyright 2006-2015 Seeds of Diversity Canada
 *
 * Manage authenticated user sessions.
 *
 * The client provides a uid/password or email/password. If authenticated, user and group permissions are computed and
 * stored in a session record.
 *
 * The important feature is that the userid/password/groups/perms are all isolated from the session record, so the
 * authentication and permission computation can be performed in an environment separate from the database location
 * where the session record is stored.
 *
 * SEEDSession                  - a wrapper for PHP sessions, with variable access
 * SEEDSessionAuth              - a SEEDSession that requires the user to login - no UI
 *                                  FindSession - tries to find an active session
 *                                  MakeSession - tries to auth a userid/password, creates a session record with perms
 *                                  TestPerm - tests given perms against a current session
 *                                  EstablishSession - uses the above to do the right thing on secure pages
 * SEEDSessionAuthUI            - a SEEDSessionAuth with UI functions.  Override to customize the UI
 *                                  In practice, you'll use SEEDSessionAuthUI everywhere instead of SEEDSessionAuth because you'll want
 *                                  a login screen to appear if a session is not active.
 * SEEDSessionMagic             - extends SEEDSessionAuth() with
 *                                  PrepareMagicSession - set up a magic session for later use
 *                                  MakeMagicSession - use a prepared magic session
 */

/*
Security zones: Sess, Auth and Magic.
    These refer to database areas where different parts of the SEEDSession data are stored.
    If you have no reason to separate them, put all tables in the same database.
    If you have users who can access Session records, but shouldn't be allowed to access Auth data (such as passwords),
    put the tables in different databases, and run SEEDSession from a user account that can read and write all such db's.
*/

define("SEEDSESSION_DB_TABLE_SEEDSESSION_MAGICLOGIN",
"
CREATE TABLE IF NOT EXISTS SEEDSession_MagicLogin (
    # Security zone: Magic
    #
    # Stores magic logins that are available. If a client provides the magic_idstr, they are automagically logged in
    # to a new session with the given perms, or all allowed perms

        _key        INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT,
        _created    DATETIME,
        _created_by INTEGER,
        _updated    DATETIME,
        _updated_by INTEGER,
        _status     INTEGER DEFAULT 0,

    magic_idstr VARCHAR(200) NOT NULL,
    uid         INTEGER,
    ts_expiry   INTEGER,                # Unix time() + duration till expiry (0=no expiry)
    about       VARCHAR(200),           # optional description of purpose
    sess_parms  TEXT,                   # optional parms for the session (permsR=A,B,C&permsW=D,E,F&permsA=G,H&expiry=1000) - if empty, all perms allowed and default session expiry
    act_parms   TEXT,                   # optional parms to tell client page what action to take

    INDEX (magic_idstr)
);
"
);


include_once( "SEEDStd.php" );
include_once( SEEDCORE."SEEDSession.php" );
include_once( "SEEDForm.php" );
include_once( "SEEDLocal.php" );
include_once( "SEEDSessionAuthDB.php" );
include_once( "KeyFrame/KFRelation.php" );


define( "SEEDSESSION_EXPIRY_DEFAULT",  "7200" );

/*
define( "SEEDSESSION_ERR_NOERR",               "0" );
define( "SEEDSESSION_ERR_GENERAL",             "1" );
define( "SEEDSESSION_ERR_NOSESSION",           "2" );
define( "SEEDSESSION_ERR_EXPIRED",             "3" );
define( "SEEDSESSION_ERR_UID_UNKNOWN",         "4" );
define( "SEEDSESSION_ERR_USERSTATUS_PENDING",  "5" );
define( "SEEDSESSION_ERR_USERSTATUS_INACTIVE", "6" );
define( "SEEDSESSION_ERR_WRONG_PASSWORD",      "7" );
define( "SEEDSESSION_ERR_PERM_NOT_FOUND",      "8" );
define( "SEEDSESSION_ERR_MAGIC_NOT_FOUND",     "9" );
*/

define( "SEEDSESSION_PARM_KEY", "seedsession_key" );
define( "SEEDSESSION_PARM_UID", "seedsession_uid" );
define( "SEEDSESSION_PARM_PWD", "seedsession_pwd" );

// Set ts_expiry to this value at logout.
// We don't use 0 because that means never-expire to magic login. Different table, but let's avoid confusion.
define( "SEEDSESSION_TSEXPIRY_LOGOUT", 1 );


$kfreldefMagic =
    array( "Tables"=>array( array( "Table" => 'SEEDSession_MagicLogin',
                                   "Fields" => array( array("col"=>"magic_idstr","type"=>"S"),
                                                      array("col"=>"uid",        "type"=>"I"),
                                                      array("col"=>"about",      "type"=>"S"),
                                                      array("col"=>"sess_parms", "type"=>"S"),
                                                      array("col"=>"act_parms",  "type"=>"S"),
                                                      array("col"=>"ts_expiry",  "type"=>"I") ) ) ) );



class SEEDSessionAuth extends SEEDSession
/********************
 */
{
    var $kfrSession = NULL;   // the current session record
    var $bSessValid = false;  // internal flag to verify that a valid session exists (e.g. when change password request received)
    var $error = SEEDSESSION_ERR_NOERR;

    private $raMetadata = array();    // the current user's metadata from SEEDSession_UsersMetaData

    // internal
    var $kfdbSess = NULL;   // ref to the database where the Session table is
    var $kfdbAuth = NULL;   // ref to the database where the Authentication tables are
    private $kfrelSess = NULL;  // kfrel created for the Session table
    var $oL = NULL;         // SEEDLocal


    var $bDebug = false; var $sDebug = "";

    var $httpNameUID = SEEDSESSION_PARM_UID;    // the http parm that identifies the user login userid  (change if an override wants to use a different parm name)
    var $httpNamePWD = SEEDSESSION_PARM_PWD;    // the http parm that identifies the user login password

    function __construct( KeyFrameDB $kfdbSess, $lang = 'EN' )
    /*********************************************************
     */
    {
        global $SEEDSessionAuth_Local;

        parent::__construct();
        $this->kfdbSess = $kfdbSess;
        $this->kfdbAuth = $kfdbSess;       // this can be overridden by SetAuthDB

        // uid 0 because we don't know who's doing this, and it doesn't matter
        $this->initKfrel(0);

//        $this->bDebug = (@$_COOKIE[SEEDSESSION_PARM_UID] == 'bob@seeds.ca');

        $this->oL = new SEEDLocal( $SEEDSessionAuth_Local, $lang );
    }

    function S( $k, $raSubst = array() )  { return( $this->oL->S( $k, $raSubst ) ); }

    function GetSessIDStr() { return( $this->kfrSession ? $this->kfrSession->Value('sess_idstr') : "" ); }
    function GetUID()       { return( $this->kfrSession ? $this->kfrSession->Value('uid') : 0 ); }
    function GetRealname()  { return( $this->kfrSession ? $this->kfrSession->Value('realname') : "" ); }
    function GetEmail()     { return( $this->kfrSession ? $this->kfrSession->Value('email') : "" ); }
    function GetName()
    {
        if( !($name = $this->GetRealname()) ) {
            if( !($name = $this->GetEmail()) ) {
                $name = ($uid = $this->GetUID()) ? "#$uid" : "";
            }
        }
        return( $name );
    }

    function MetadataGetValue( $k )  { return( @$this->raMetadata[$k] ); }
    function MetadataGetRA()         { return( $this->raMetadata ); }
//TODO:should use a stateless SEEDSessionAuthDB method, so the same code can be used by an admin process
    function MetadataSetValue( $k, $v ) {}
    function MetadataDelete( $k ) {}

    function SetAuthDB( KeyFrameDB $kfdbAuth )
    /*****************************************
        Set a different database connection for the Auth data if you want it to be stored in a
        different security zone than the Session records. (i.e. in a database not visible by users of the Session's db)
     */
    {
        $this->kfdbAuth = $kfdbAuth;
    }

    function MakeSession( $userid, $password, $sess_idstr = "" )
    /***********************************************************
        Make a session for the given user, if the given password is correct. Use given sess_idstr or generate a random one.

        userid can be either a uid or an email
     */
    {
        $this->kfrSession = NULL;
        $this->bSessionValid = false;
        $this->error = SEEDSESSION_ERR_NOERR;

        $raUser = $this->GetUserInfo( $userid );
        if( !@$raUser['_key'] ) {
            $this->error = SEEDSESSION_ERR_UID_UNKNOWN;

        /* Does the password match?
         */
        } else if( $password != $raUser['password'] ) {
            $this->error = SEEDSESSION_ERR_WRONG_PASSWORD;

        } else if( $raUser['eStatus'] != 'ACTIVE' ) {
            $this->error = $raUser['eStatus'] == 'PENDING'  ? SEEDSESSION_ERR_USERSTATUS_PENDING :
                          ($raUser['eStatus'] == 'INACTIVE' ? SEEDSESSION_ERR_USERSTATUS_INACTIVE :
                           SEEDSESSION_ERR_GENERAL);

        /* Create a session record
         */
        } else if( !$this->_makeSession( $raUser['_key'],
                                         $raUser['realname'],
                                         $raUser['email'],
                                         array(),   // raSessParms
                                         $sess_idstr ) ) {
            $this->error = SEEDSESSION_ERR_GENERAL;
            $this->kfrSession = NULL;
        }

        $this->bSessionValid = $this->kfrSession != NULL;

        return( $this->bSessionValid );
    }

    function FindSession( $sess_idstr = "", $sid = 0, $uid = 0 )
    /***********************************************************
        Find an active session by either one of the input parms
     */
    {
        $this->kfrSession = NULL;
        $this->bSessionValid = false;
        $this->error = SEEDSESSION_ERR_NOERR;

        if( !empty($sess_idstr) ) {
            /* Look for a session using the idstr
             */
            $this->kfrSession = $this->kfrelSess->GetRecordFromDB( "sess_idstr='".addslashes($sess_idstr)."'" );

        } else if( $sid ) {
            /* Look for a session using the key
             */
            $this->kfrSession = $this->kfrelSess->GetRecordFromDBKey( $sid );

        } else if( $uid ) {
            /* Look for a session using the uid - N.B. a user may have more than one session open at once
             */
            $this->kfrSession = $this->kfrelSess->GetRecordFromDB( "uid='".intval($uid)."'" );
        }


        if( !$this->kfrSession ) {
            if( $this->bDebug ) { $this->sDebug = "NOSESSION ".$sess_idstr."<BR>"; }

            $this->error = SEEDSESSION_ERR_NOSESSION;

        /* Has the session expired?

         * Use db time instead of server time:  $now = db_query( "SELECT NOW()" );  $t = strtotime($now[0]);   // this is time in seconds

         */
        } else if( $this->kfrSession->Value('ts_expiry') == SEEDSESSION_TSEXPIRY_LOGOUT ) {
            /* The last session was logged out.
             */
            if( $this->bDebug ) { $this->sDebug = "LOGGED OUT"; }

            $this->error = SEEDSESSION_ERR_NOSESSION;
            $this->kfrSession = NULL;
        } else if( $this->kfrSession->Value('ts_expiry') < time() ) {
            if( $this->bDebug ) { $this->sDebug = "EXPIRED<BR>".$this->kfrSession->Value('ts_expiry')."<BR>".time()."<BR>"; }

            if( time() - $this->kfrSession->Value('ts_expiry') < 3600 ) {
                // if the session expired less than an hour ago, note that it expired
                $this->error = SEEDSESSION_ERR_EXPIRED;
            } else {
                // otherwise, don't bother the user with an expiry message because it could be a long time ago (days or weeks) and that would seem weird
                $this->error = SEEDSESSION_ERR_NOSESSION;
            }
            $this->kfrSession = NULL;
        }

        $this->bSessionValid = $this->kfrSession != NULL;

        if( $this->bSessionValid ) {
//            $this->raMetadata = $this->GetUserMetadata( $this->kfrSession->Value('uid') );
//var_dump($this->raMetadata);
        }

        return( $this->bSessionValid );
    }

    function LogoutSession( $sess_idstr = "", $sid = 0, $uid = 0 )
    /*************************************************************
        If a session is active, make it unusable.
        If no parms specified, use EstablishSession's FindSession method to find a current valid session.

        Return true if session found and logged out. Else false and set error.
        Leave the kfrSession intact, so the client can write "Goodbye $sess->GetName".
     */
    {
        $ok = false;
        $this->error = SEEDSESSION_ERR_NOERR;

        if( !$this->kfrSession ) {
            $this->FindSession( $this->VarGet(SEEDSESSION_PARM_KEY) );  // if fails, sets error
        }

        if( $this->kfrSession ) {
            $this->kfrSession->SetValue( 'ts_expiry', SEEDSESSION_TSEXPIRY_LOGOUT );
            if( !($ok = $this->kfrSession->PutDBRow()) )  $this->error = SEEDSESSION_ERR_GENERAL;
        }
        return( $ok );
    }

    function TestPermRA( $raPerms )
    /******************************
        Return true if the current user has permissions that match the given array.
        An empty array always succeeds.

        $raPerms can have two forms:
            1) an array of conjunctions of permissions:  'X'=>'R', 'Y'=>'W'  = read perms on X AND write perms on Y
            2) an array of disjunctions of those arrays: array( 'X'=>'R', 'Y'=>'W' ), array( 'C'=>'A' )  = (read X AND write Y) OR admin C

               note that the second form can have arbitrary (and ignored) keys on the array
               e.g. 'oneWay' => array( 'X'=>'R', 'Y'=>'W' ), 'anotherWay' => array( 'C'=>'A' )
     */
    {
        $bLoginOK = false;

        if( is_array($raPerms) && count($raPerms) == 0 ) {
            // an empty array always succeeds
            $bLoginOK = true;
        } else if( !is_array( reset($raPerms) ) ) {  // reset returns the first value
            // 1) array of permissions joined by AND
            $bLoginOK = true;                           // perms are restrictive, so begin by assuming success
            foreach( $raPerms as $p => $m ) {
                if( !$this->TestPerm( $p, $m ) ) {      // sets SEEDSESSION_ERR_PERM_NOT_FOUND
                    $bLoginOK = false;
                    break;
                }
            }
        } else {
            // 2) disjunction of conjunctions of perms
            foreach( $raPerms as $ra ) {
                $bLoginOK = true;                           // perms are restrictive, so begin by assuming success
                foreach( $ra as $p => $m ) {
                    if( !$this->TestPerm( $p, $m ) ) {      // sets SEEDSESSION_ERR_PERM_NOT_FOUND
                        $bLoginOK = false;
                        break;
                    }
                }
                if( $bLoginOK ) break;
            }
        }
        return( $bLoginOK );
    }

    function TestPerm( $perm, $mode )
    /********************************
        Return true if the given perm is available in the column 'perms$mode'. i.e. $mode = R, W, A
     */
    {
        $ok = false;

        if( $this->kfrSession ) {
            $ok = (strpos($this->kfrSession->value("perms$mode"), " ".$perm." ") !== false);  // NB !== because 0 means first position
        }
        if( !$ok ) $this->error = SEEDSESSION_ERR_PERM_NOT_FOUND;

        return( $ok );
    }

    function CanRead( $perm )   { return( $this->TestPerm( $perm, "R" ) ); }
    function CanWrite( $perm )  { return( $this->TestPerm( $perm, "W" ) ); }
    function CanAdmin( $perm )  { return( $this->TestPerm( $perm, "A" ) ); }


//TODO: Move the DrawLoginForm part to SEEDSessionAuthUI so that all UI is in there, and then move $this->oL there too

    function EstablishSession( $raPerms = array(), $callback_LoginForm = NULL, $raParms = array() )
    /**********************************************************************************************
        *** This is an example of how to implement a session-login handler using FindSession, MakeSession, and TestPerm.
            Many other variations of this are possible.
            Many possible cases are handled here, but your situation might be different.

        Try to find a valid session, or create one using login parms found in _REQUEST, or return false.

        $raPerms is a TestPerms array, which must pass to permit access
        An empty array always succeeds (but requires the login/password to exist).

        $fnLoginForm is a function that draws the login form (default login form used if not given)

        $raParms: bQuietFail = false
                      Ordinarily if a valid session doesn't exist we show a login form.  With this option, just return false if session not found.
                      This is useful for pages where behaviour would be different if logged in vs. not logged in.
                      Be really careful not to expose secret stuff if no user session, and don't use the methods of this derivation like GetUID().

        This handler uses a PHP session (via SEEDSession) to store the sess_idstr. Since the session_id() can persist
        for a long time, the sess_idstr can change during that lifetime.
        There is also the possibility of propagating the sess_idstr to the browser via cookies (instead of session_id)
        which would be a more sophisticated way to prevent session hijacking.

        Possible situations:
            1) Valid SEEDSession exists, no login parms                         - test perms and go ahead
            2) Valid SEEDSession exists, same/different user trying to login    - logout the old SEEDSession, authenticate, create new session, test perms
            3) No SEEDSession, no login parms                                   - draw the login form
            4) No SEEDSession, same/different user trying to login              - authenticate, create new session, test perms
            5) Expired SEEDSession, no login parms                              - draw the login form
            6) Expired SEEDSession, same/different user trying to login         - authenticate, create new session, test perms

            2 & 6 could reuse the SEEDSession if user is the same and, say, if expiry were greater than time()+15minutes.
            This would not be an additional security risk, since session_id() is reused throughout anyway.
     */
    {
        $bLoginOK = false;
        $bTestPerms = false;    // true if the process gets far enough to test perms

        $bQuietFail = (@$raParms['bQuietFail']==true);

        $bValidSession = $this->FindSession( $this->VarGet(SEEDSESSION_PARM_KEY) );

        // Get the uid from POST because it is stored in a cookie, which overrides the POST parm in _REQUEST.
        $sUid = SEEDSafeGPC_GetStrPlain( $this->httpNameUID, $_POST );
        $sPwd = SEEDSafeGPC_GetStrPlain( $this->httpNamePWD, $_POST );

        /* It is imperative that these be removed from the _REQUEST array, because several applications copy
         * and reissue GPC parms to subsequent pages.  This would reveal the password in client application links.
         */
         unset($_POST[$this->httpNameUID]);
         unset($_POST[$this->httpNamePWD]);
         unset($_GET[$this->httpNameUID]);
         unset($_GET[$this->httpNamePWD]);
         unset($_REQUEST[$this->httpNameUID]);
         unset($_REQUEST[$this->httpNamePWD]);


        if( !empty($sUid) && !empty($sPwd) ) {
            /* Login parms are present. Override the current session, if any, and relogin.
             */
    // For this to work, FindSession has to retain the data about expired sessions.
    //
    //      $bSameUser = (($bValidSession || session expired) && new user == old user);
    //      if( $bSameUser ) gather all metadata, vars, etc related to the old session

            /* Destroy any current session discovered by FindSession
             */
            $this->LogoutSession();

            /* Authenticate the uid/pwd, create a new session record in $this->kfrSession
             */
            if( $this->MakeSession( $sUid, $sPwd ) ) {
                $this->VarSet( SEEDSESSION_PARM_KEY, $this->kfrSession->Value('sess_idstr') );
                // Set the uid in a cookie so it will appear in the form on relogin.
                // There are no security concerns here because we never use this to identify the user, just for their convenience
                setcookie( SEEDSESSION_PARM_UID, $sUid, time()+3600*24*365 /* a year */, "/" );

    //          if( $bSameUser ) copy all metadata, vars, etc from the old session

                $bTestPerms = true;
            }
        } else if( $bValidSession ) {
            $bTestPerms = true;
        }

        if( $bTestPerms ) {
            /* A valid session existed, or was created. Now test whether the specific page is accessible in this session.
             */
            $bLoginOK = $this->TestPermRA( $raPerms );
        }

        if( !$bLoginOK && !$bQuietFail ) {
            /* Either a session was uncreatable, authentication failed, or perms failed.
             * Draw the login form.  It should use $this->error to explain what's going on.
             * The script dies here, control does not return.
             */
            echo $this->sDebug;

            // $this->error is the result of initial attempts to find/create a session.
            $sErrMsg = "";
            switch( $this->error ) {
                case SEEDSESSION_ERR_NOERR:               break;
                case SEEDSESSION_ERR_NOSESSION:             $dummy = "<H2>Please login</H2>";                   break;
                case SEEDSESSION_ERR_GENERAL:             $sErrMsg = $this->S('login_err_general');             break;
                case SEEDSESSION_ERR_EXPIRED:             $sErrMsg = $this->S('login_err_expired');             break;
                case SEEDSESSION_ERR_UID_UNKNOWN:         $sErrMsg = $this->S('login_err_uid_unknown');         break;
                case SEEDSESSION_ERR_WRONG_PASSWORD:      $sErrMsg = $this->S('login_err_wrong_password');      break;
                case SEEDSESSION_ERR_PERM_NOT_FOUND:      $sErrMsg = $this->S('login_err_perm_not_found');      break;
                case SEEDSESSION_ERR_USERSTATUS_PENDING:  $sErrMsg = $this->S('login_err_userstatus_pending', array($sUid));  break;
                case SEEDSESSION_ERR_USERSTATUS_INACTIVE: $sErrMsg = $this->S('login_err_userstatus_inactive', array($sUid)); break;
                default:                                  $sErrMsg = "<P>Unknown error {$this->error}.</P>";    break;
            }

            echo $this->DrawLoginForm( $sErrMsg, array( "valueUID" => $sUid ) );     // let the login form use the current uid, if any (e.g. on relogin))
            exit;       // stop here and wait for user to resubmit the page
        }
        return( $bLoginOK );
    }

    function DrawLoginForm( $sErrMsg, $raParms = array() )
    /*****************************************************
        Override this to implement a custom login form

        raParms: nameUID => the name of the http parm identifying the user (default SEEDSESSION_PARM_UID)
                 namePWD => the name of the http parm identifying the password (default SEEDSESSION_PARM_PWD)
                 valueUID => the current uid (allows the form to fill it in e.g. on relogin)
     */
    {
        $sUid = @$raParms['valueUID'];
        return( $sErrMsg
               ."<FORM method='post' action='${_SERVER['PHP_SELF']}'>"
               .SEEDForm_Text( $this->httpNameUID, $sUid, "User" )."<BR/>"
               ."Password: <INPUT type=password name='{$this->httpNamePWD}'><BR/>"
               ."<INPUT type=submit></FORM>" );
    }

//TODO: move to a stateless SEEDSessionAuthDB
    function ChangePassword( $kUser, $sPwdOld, $sPwdNew )
    /****************************************************
        Change the user's password.  Obviously, you've been very careful to make sure this is being done with the user's knowledge and permission.
     */
    {
        $bOk = false;

        $sP = $this->kfdbAuth->Query1("SELECT password FROM SEEDSession_Users WHERE _key='$kUser'");
        if( $sP == $sPwdOld ) {
            $bOk = $this->kfdbAuth->Execute( "UPDATE SEEDSession_Users SET password='$sPwdNew' WHERE _key='$kUser'" );
        }
        return( $bOk );
    }

//TODO: move to a stateless SEEDSessionAuthDB
    function GetUserInfo( $userid, $status = 0 )
    /*******************************************
       Retrieve a SEEDSession_Users row.  The user does not have to be logged in
       (so this is useful for user admin apps, sending passwords to users who are not logged in, etc)

       userid can be a uid or an email
       status defines the _status filter: -1=no filter
     */
    {
        $cond = (is_numeric( $userid ) ? ("_key=".intval($userid)) : ("email='".addslashes($userid)."'"));
        if( $status != -1 ) {
            $cond .= " AND _status='$status'";
        }
        return( $this->kfdbAuth->QueryRA( "SELECT * FROM SEEDSession_Users WHERE $cond" ) );
    }

//TODO: move to a stateless SEEDSessionAuthDB
    function GetUserPerms( $kUser )
    /******************************
        Get the given user's permissions from perms and group-perms.  The user does not have to be logged in
        (so this is useful for user admin apps).

            e.g. for perms  'app1','R'
                            'app2','W'  & group 'app2','R'
                            'app3','RW' & group 'app3'=>'RWA'

                 'perm2modes' => array( 'app1'=>'R', 'app2'=>'RW', 'app3'=>'RWA' ),
                 'mode2perms' => array( 'R'=>array('app1','app2','app3'), 'W'=>array('app2','app3'), 'A'=>array('app3') )
     */
    {
        return( array( 'perm2modes' => array(),
                       'mode2perms' => array() ) );
    }

    function _makeSession( $uid, $realname, $email, $raSessParms = array(), $sess_idstr = "" )
    /*****************************************************************************************
        $raSessParms: permsR, permsW, permsA, ts_expiry
     */
    {
        if( empty($sess_idstr) )  $sess_idstr = SEEDStd_UniqueId();


        /* If the session already exists, reuse the record. We are probably reactivating it after expiry.
         */
        $this->kfrSession = $this->kfrelSess->GetRecordFromDB( "sess_idstr='".addslashes($sess_idstr)."'" );
        if( !$this->kfrSession ) {
            $this->kfrSession = $this->kfrelSess->CreateRecord();
        }
        $this->kfrSession->SetValue( "sess_idstr", $sess_idstr );
        $this->kfrSession->SetValue( "uid",        $uid );
        $this->kfrSession->SetValue( "realname",   $realname );
        $this->kfrSession->SetValue( "email",      $email );

        if( empty($raSessParms["permsR"]) && empty($raSessParms["permsW"]) && empty($raSessParms["permsA"]) ) {
//TODO: use stateless SEEDSessionAuthDB
            /* perms for the session are not defined by input parms, so generate a complete list of perms for this user
             */
            $permsR = $permsW = $permsA = " ";

            $dbcPerms = $this->kfdbAuth->KFDB_CursorOpen(
                                // Get perms explicitly set for this uid
                                "SELECT perm,modes FROM SEEDSession_Perms WHERE _status=0 AND uid='$uid' "
                                ."UNION "
                                // Get perms associated with the user's primary group
                                ."SELECT P.perm AS perm, P.modes as modes "
                                ."FROM SEEDSession_Perms P, SEEDSession_Users U "
                                ."WHERE P._status=0 AND U._status=0 AND "
                                ."U._key='$uid' AND U.gid1 >=1 AND P.gid=U.gid1 "
                                ."UNION "
                                // Get perms from groups
                                ."SELECT P.perm AS perm, P.modes as modes "
                                ."FROM SEEDSession_Perms P, SEEDSession_UsersXGroups GU "
                                ."WHERE P._status=0 AND GU._status=0 AND "
                                ."GU.uid='$uid' AND GU.gid >=1 AND GU.gid=P.gid" );
            while( $ra = $this->kfdbAuth->KFDB_CursorFetch( $dbcPerms ) ) {
                if( strchr($ra['modes'],'R') && !strstr($permsR, " ".$ra['perm']." ") )  $permsR .= $ra['perm']." ";
                if( strchr($ra['modes'],'W') && !strstr($permsW, " ".$ra['perm']." ") )  $permsW .= $ra['perm']." ";
                if( strchr($ra['modes'],'A') && !strstr($permsA, " ".$ra['perm']." ") )  $permsA .= $ra['perm']." ";
            }
            $this->kfdbAuth->KFDB_CursorClose( $dbcPerms );

            $this->kfrSession->SetValue( "permsR", $permsR );
            $this->kfrSession->SetValue( "permsW", $permsW );
            $this->kfrSession->SetValue( "permsA", $permsA );

        } else {
            $this->kfrSession->SetValue( "permsR", @$raSessParms["permsR"] );
            $this->kfrSession->SetValue( "permsW", @$raSessParms["permsW"] );
            $this->kfrSession->SetValue( "permsA", @$raSessParms["permsA"] );
        }

        $this->kfrSession->SetValue( "ts_expiry", time() + (!empty($raSessParms["ts_expiry"]) ? $raSessParms["ts_expiry"]
                                                                                              : SEEDSESSION_EXPIRY_DEFAULT) );

        return( $this->kfrSession->PutDBRow() );
    }

    private function initKfrel( $uid )
    {
        $def = array(
                "Tables"=>array( array( "Table" => 'SEEDSession',
                                        "Fields" => array( array("col"=>"sess_idstr", "type"=>"S"),
                                                           array("col"=>"uid",        "type"=>"I"),
                                                           array("col"=>"realname",   "type"=>"S"),
                                                           array("col"=>"email",      "type"=>"S"),
                                                           array("col"=>"permsR",     "type"=>"S"),
                                                           array("col"=>"permsW",     "type"=>"S"),
                                                           array("col"=>"permsA",     "type"=>"S"),
                                                           array("col"=>"ts_expiry",  "type"=>"I")
                                             ) ) ) );
        $this->kfrelSess = new KeyFrameRelation( $this->kfdbSess, $def, $uid, array('logfile'=>SITE_LOG_ROOT."seedsession.log") );
    }

}


class SEEDSessionAuthUI extends SEEDSessionAuth
/**********************
    Open a session, handle login procedure, plus send password, create account, change password, logout.

    Usage:
    $o = SEEDSessionAuth();              // create object
    $o->HandleLoginActions( $raPerms );  // Handle send password, create account, do not return
                                         // or find a valid session, return ok
                                         // or create a valid session, return ok
                                         // or draw login form, do not return

    // these require a valid session (login successful)
    $o->Logout();           // end session
    $o->ChangePassword();   // handle change password request
 */
{
    var $httpNameMode = 'sessioncontrol';  // the http parm that identifies the mode
    var $raConfig = array();

    function __construct( KeyFrameDB $kfdbSess, $lang = 'EN' )
    {
        parent::__construct( $kfdbSess, $lang );

        // Since this object can be created by many clients, potentially everywhere that a SEEDSessionAuth is needed, it is easier
        // to set the AuthUI config using a global variable rather than pass it as a parm
        global $SEEDSessionAuthUI_Config;
        if( is_array( $SEEDSessionAuthUI_Config) ) {
            $this->raConfig = $SEEDSessionAuthUI_Config;
        }
    }


    function HandleLoginActions( $raPerms )
    /**************************************
        User may or may not be logged in.  Everything should work either way, though normally modes like 'create account' are only offered when not logged in.

        raPerms = array( perm => modes, perm => modes, ... ) for the login case

        Handle the following modes:
            ''           = default: regular login
                             if valid session exists, return ok
                             if parms allow valid session, create it and return ok
                             if parms are invalid, show error message and draw form, do not return
                             if no parms, draw form, do not return
            'sendpwd'    = Send password by email - never return to the calling script, since this is normally a subfunction of the login form
                             if parm is valid user, send password by email, draw result
                             if parm is invalid, show error message and draw form
                             if no parms, draw form
            'createacct' = Create account - never return to the calling script
                             if parms valid, create account and send confirmation email
                             if parms invalid, show error message and draw form
                             if no parms, draw form
            'activateacct'= Activate account - never return to the calling script
                             if parms valid, and account is PENDING, activate it and draw result
                             else draw error
            'logout'     = Logout and draw result
            'changepwd'  = Change password - only if logged in
                             if parms valid, change password and send email notification
                             if parms invalid, show error message and draw form
                             if no parms, draw form
     */
    {
        // This is normally implemented as part of the login process, so any function that does not result in a successful login must not return from
        // here. Otherwise the client page may assume that a login was successful and draw itself (e.g. after a successful createacct inserted in http parms)
        $bProceed = false;

        /* Handle the login actions that can occur without being logged in
         */
        switch( SEEDSafeGPC_Smart( $this->httpNameMode, array( '','sendpwd','createacct','activateacct' ) ) ) {
            case 'sendpwd':      echo $this->SendPassword();   // validate parms & send password, or draw form.  Does not need to be logged in.
                break;
            case 'createacct':   echo $this->CreateAccount();  // validate parms & create account, or draw form.  Does not need to be logged in.
                break;
            case 'activateacct': echo $this->ActivateAccount();// validate parms & activate account, or show error message. Does not need to be logged in.
                break;
            default:
                $this->EstablishSession( $raPerms ) or die( "Cannot login" );
                $bProceed = true;
                break;
        }

        if( !$bProceed )  exit;  // don't let a client page draw itself unless this resulted in a successful session

        /* Handle the login actions that require a valid login
         */
        $bProceed = false;
        switch( SEEDSafeGPC_Smart( $this->httpNameMode, array( '','logout','changepwd' ) ) ) {
            case 'logout':       echo $this->LogoutUI();         // logout, draw result.
                break;
            case 'changepwd':    echo $this->ChangePasswordUI(); // validate parms & change password, or draw form. Must be logged in.
                break;
            default:
                $bProceed = true;
                break;
        }

        if( !$bProceed )  exit;  // handled the action, don't let the client page draw itself (because it might not remember to check a return code)
    }

    function LogoutUI()
    /******************
        Logout any current session.  If a session is not active look for one.
        This would normally only be available to the user when a session is active, but we use a greedy logout policy anyway to ensure that
        people can close their open sessions.
     */
    {
        $bSuccess = $this->LogoutSession();    // if a current session exists, close it and keep values in $this->kfrSession so we can say goodbye nicely
        return( $this->LogoutDrawResult( $bSuccess ) );
    }

    function LogoutDrawResult( $bSuccess )
    /*************************************
       After a logout, $bSuccess is true if there was a session and it closed correctly.
       If there was a session, $this->kfrSession still contains the session values (e.g. realname) so we can say goodbye nicely
     */
    {
        if( $bSuccess ) {
            $s = $this->S( "Logout_success", array($this->GetName()) );
        } else if( $this->error == SEEDSESSION_ERR_EXPIRED ) {
            $s = $this->S( "Logout_expired" );
        } else {
            // SEEDSESSION_ERR_NOTFOUND or SEEDSESSION_ERR_GENERAL
            $s = $this->S( "Logout_failed" );
        }
        return( $s."<P><A HREF='{$_SERVER['PHP_SELF']}'>".$this->S("Back to login")."</A></P>" );
    }


    function CreateAccount()
    /***********************
        If valid parms, create account, send confirmation email, draw result
        If invalid parms, show error message, draw form
        If no parms, draw form

        Form submits the 'send password' login mode to get back here.

        Never return to the caller, because this is a subfunction of the login form, and the caller might (probably does) assume that the login is
        successful if control returns.
     */
    {
        if( !@$this->raConfig['bEnableCreateAccount'] )  die();  // you shouldn't be doing this

        $s = "";
        $sErrMsg = "";
        $bOk = false;

        $sUid = SEEDSafeGPC_GetStrPlain( $this->httpNameUID, $_POST );
        $sP1  = SEEDSafeGPC_GetStrPlain( $this->httpNamePWD.'1', $_POST );
        $sP2  = SEEDSafeGPC_GetStrPlain( $this->httpNamePWD.'2', $_POST );

        if( !empty($sUid) && !empty($sP1) && !empty($sP2) ) {
            if( $sP1 != $sP2 ) {
                $sErrMsg = $this->S( "CreateAcct_Passwords must match" );
            } else {
                $raUserInfo = $this->GetUserInfo( $sUid );
                if( !empty($raUserInfo['email']) ) {
                    $sErrMsg = $this->S( "CreateAcct_user_already_exists", array($sUid) );
                } else if( 0 ) { // invalid email address
                    $sErrMsg = $this->S( "CreateAcct_user_not_valid_email", array($sUid) );
                } else {
                    $sCode = substr(md5(time()), 0, 15);

                    // create this as needed, because at some earlier point uid might not be set yet (if it even is now)
                    $oSessUGP = new SEEDSessionAuthDB( $this->kfdbAuth, $this->GetUID() );
                    if( ($kUser = $oSessUGP->CreateUser( $sUid, $sP1, array('eStatus'=>'PENDING','sExtra'=>SEEDStd_ParmsURLAdd("","activatecode",$sCode) ) )) ) {
                        assert( !empty($this->raConfig['urlActivation']) );
                        $sUrl = $this->raConfig['urlActivation']."?{$this->httpNameMode}=activateacct&k=$kUser&m=$sCode";
                        $sMail = $this->S( "CreateAcct_email_body", array( $sUrl, $sUid, $sP1 ) );
// TODO: elevate this, it's in seedcommon/siteutil.php
                        $bOk = MailFromOffice( $sUid,          $this->S('CreateAcct_eSubject'), $sMail );
                               MailFromOffice( "bob@seeds.ca", $this->S('CreateAcct_eSubject'), $sMail );

                        if( $bOk )  $s .= $this->CreateAccountDrawResult();
                    }
                }
            }
        }
        if( !$bOk ) {
            $s .= $this->CreateAccountDrawForm( $sErrMsg );
        }
        return( $s );
    }

    function CreateAccountDrawForm( $sErrMsg = "" )
    {
        if( !@$this->raConfig['bEnableCreateAccount'] )  die();  // you shouldn't be doing this

        $sUid = SEEDSafeGPC_GetStrPlain( $this->httpNameUID );
        return( $sErrMsg
               ."<FORM method='post' action='${_SERVER['PHP_SELF']}'>"
               .SEEDForm_Hidden( $this->httpNameMode, 'createacct' )
               .SEEDForm_Text( $this->httpNameUID.'0', $sUid, "User" )."<BR/>"
               .SEEDForm_Text( $this->httpNamePWD.'1', "", "Password",       20, "", array('bPassword'=>true) )."<BR/>"
               .SEEDForm_Text( $this->httpNamePWD.'2', "", "Password again", 20, "", array('bPassword'=>true) )."<BR/>"
               ."<BR/><INPUT type=submit></FORM>" );
    }

    function CreateAccountDrawResult()
    /*********************************
        Called from CreateAccount - override to customize what the user sees
     */
    {
        return( $this->S( "CreateAccount_success" )."<P><A HREF='{$_SERVER['PHP_SELF']}'>Back to login</A></P>" );
    }

    function ActivateAccount()
    /*************************
        Received an Activate link containing the codes assigned in CreateAccount.
        This confirms that the user email is valid.
        Activate the account and set initial permissions using gid1
     */
    {
        $bOk = false;
        $sErrMsg = "";

        $k = SEEDSafeGPC_GetInt( 'k' );
        $sCodeEmail = SEEDSafeGPC_GetStrPlain( 'm' );
        if( $k && !empty($sCodeEmail) ) {
// Isn't this just the same as $this->GetUserInfo($k) ?
            $ra = $this->kfdbAuth->QueryRA( "SELECT eStatus,sExtra FROM SEEDSession_Users WHERE _key='$k'");
            $sCodeDB = SEEDStd_ParmsUrlGet( $ra['sExtra'], 'activatecode' );
            if( $ra['eStatus'] != 'PENDING' ) {
                $sErrMsg = "Account has already been activated";
            } else if( $sCodeDB != $sCodeEmail ) {
                $sErrMsg = "The code is not recognized.";
            } else {
                $bOk = $this->kfdbAuth->Execute( "UPDATE SEEDSession_Users SET eStatus='ACTIVE', "
                                                ." gid1=".intval(@$this->raConfig['iActivationInitialGid1'])
                                                ." WHERE _key='$k' ");
            }
        }

        if( !$bOk && empty($sErrMsg) )  $sErrMsg = "Unable to activate account";

        return( $this->ActivateAccountDrawResult( $bOk, $sErrMsg ) );
    }

    function ActivateAccountDrawResult( $bOk, $sErrMsg )
    {
        return( $bOk ? "Account activated" : "Unable to activate account: $sErrMsg" );
    }

    function SendPassword()
    /**********************
        If valid parms, send password, draw result
        If invalid parms, show error message, draw form
        If no parms, draw form

        Form submits the 'send password' login mode to get back here.

        Never return to the caller, because this is a subfunction of the login form, and the caller might (probably does) assume that the login is
        successful if control returns.
     */
    {
        $s = "";
        $sErrMsg = "";
        $bOk = false;

        // Get the uid from $this->httpNameUID.'1' so the uid can be propagated to the Send Password form's initial value without confusion
        $sUid = SEEDSafeGPC_GetStrPlain( $this->httpNameUID.'1', $_POST );

        if( !empty($sUid) ) {
            $raUserInfo = $this->GetUserInfo( $sUid );
            if( empty($raUserInfo['email']) ) {
                $sErrMsg = $this->S( "SendPassword_user_not_registered", array($sUid) );
            } else if( $raUserInfo['eStatus'] != 'ACTIVE' ) {
                $sErrMsg = $raUserInfo['eStatus'] == 'PENDING'  ? $this->S( "login_err_userstatus_pending", array($sUid) )
                                                                : $this->S( "login_err_userstatus_inactive", array($sUid) );
            } else {
                assert( !empty($this->raConfig['urlSendPasswordSite']) );
                $sMail = $this->S( "SendPassword_email_body", array( $this->raConfig['urlSendPasswordSite'], $raUserInfo['email'], $raUserInfo['password'] ) );
// TODO: elevate this, it's in seedcommon/siteutil.php
                $bOk = MailFromOffice( $raUserInfo['email'], $this->S('SendPassword_eSubject'), $sMail );
                       MailFromOffice( "bob@seeds.ca",       $this->S('SendPassword_eSubject'), $sMail );

                if( $bOk )  $s .= $this->SendPasswordDrawResult();
            }
        }
        if( !$bOk ) {
            $s .= $this->SendPasswordDrawForm( $sErrMsg );
        }

        return( $s );
    }

    function SendPasswordDrawForm( $sErrMsg = "" )
    /*********************************************
        $this->httpNameUID propagates any existing uid from the login form, for this form's initial value
        $this->httpNameUID.'1' is the uid submitted by this form
        (they're different parms so SendPassword can tell when the user submits this form)
     */
    {
        $sUid = SEEDSafeGPC_GetStrPlain( $this->httpNameUID );
        return( $sErrMsg
               ."<FORM method='post' action='${_SERVER['PHP_SELF']}'>"
               .SEEDForm_Hidden( $this->httpNameMode, 'sendpwd' )
               .SEEDForm_Hidden( $this->httpNameUID, $sUid )
               .SEEDForm_Text( $this->httpNameUID.'1', $sUid, "User" )
               ."<BR/><INPUT type=submit></FORM>" );
    }

    function SendPasswordDrawResult()
    /*********************************
        Called from SendPassword - override to customize what the user sees
     */
    {
        return( $this->S( "SendPassword_success" )."<P><A HREF='{$_SERVER['PHP_SELF']}'>Back to login</A></P>" );
    }

    function ChangePasswordUI()
    /**************************
        First, verify that a user is logged in.

        If valid parms, change password, draw result
        If invalid parms, show error message, draw form
        If no parms, draw form

        Form submits the 'change password' login mode to get back here.

        Never return to the caller, because this can be implemented as a subfunction of the login form, and the caller might (probably does) assume that the login is
        successful if control returns.
     */
    {
        $s = "";
        $sErrMsg = "";
        $bOk = false;

        if( !$this->kfrSession || !$this->kfrSession->Value('_key') ) {
            die( "You must login before you can change your password." );
        }

        // Must use httpNamePWD'0' instead of just httpNamePWD because MakeSession uses it and clears it (it thinks we're logging in)
        $sPwd = SEEDSafeGPC_GetStrPlain( $this->httpNamePWD.'0', $_POST );
        $sP1  = SEEDSafeGPC_GetStrPlain( $this->httpNamePWD.'1', $_POST );
        $sP2  = SEEDSafeGPC_GetStrPlain( $this->httpNamePWD.'2', $_POST );

        if( !empty($sPwd) && !empty($sP1) && !empty($sP2) ) {
            $raUserInfo = $this->GetUserInfo( $this->GetUID() );

            if( $raUserInfo['password'] != $sPwd ) {
                $sErrMsg = $this->S( "ChangePwd_CurrPassword_incorrect" );
            } else if( $sP1 != $sP2 ) {
                $sErrMsg = $this->S( "ChangePwd_Passwords must match" );
            } else {
                if( 0 ) { // poor password
                    $sErrMsg = $this->S( "ChangePwd_poor_password" );
                } else {
                    if( $this->ChangePassword( $this->GetUID(), $sPwd, $sP1 ) ) {
                        $sMail = $this->S( "ChangePwd_email_notice", array( $raUserInfo['email'] ) );
// TODO: elevate this, it's in seedcommon/siteutil.php
                        $bOk = MailFromOffice( $raUserInfo['email'], $this->S('ChangePwd_eSubject'), $sMail );
                               MailFromOffice( "bob@seeds.ca",       $this->S('ChangePwd_eSubject'), $sMail );

                        if( $bOk )  $s .= $this->ChangePasswordDrawResult();
                    }
                }
            }
        }
        if( !$bOk ) {
            $s .= $this->ChangePasswordDrawForm( $sErrMsg );
        }
        return( $s );
    }

    function ChangePasswordDrawForm( $sErrMsg = "" )
    /***********************************************
     */
    {
        return( $sErrMsg
               ."<FORM method='post' action='${_SERVER['PHP_SELF']}'>"
               .SEEDForm_Hidden( $this->httpNameMode, 'changepwd' )
               // Must use httpNamePWD'0' instead of just httpNamePWD because MakeSession uses it and clears it (it thinks we're logging in)
               .SEEDForm_Text( $this->httpNamePWD.'0', "", "Current password",   20, "", array('bPassword'=>true) )."<BR/>"
               .SEEDForm_Text( $this->httpNamePWD.'1', "", "New Password",       20, "", array('bPassword'=>true) )."<BR/>"
               .SEEDForm_Text( $this->httpNamePWD.'2', "", "New Password again", 20, "", array('bPassword'=>true) )."<BR/>"
               ."<BR/><INPUT type=submit></FORM>" );
    }

    function ChangePasswordDrawResult()
    /**********************************
        Called from ChangePassword - override to customize what the user sees
     */
    {
        return( $this->S( "ChangePwd_success" )."<P><A HREF='{$_SERVER['PHP_SELF']}'>Back to login</A></P>" );
    }
}


class SEEDSessionMagic extends SEEDSessionAuth {
/*********************
 */

    var $raMagicActParms = NULL;
    var $kfdbMagic = NULL;  // ref to the database where the MagicLogin table is
    var $kfrelMagic = NULL; // kfrel created for the MagicLogin table

    function __construct( KeyFrameDB $kfdbMagic, KeyFrameDB $kfdbSess )
    /******************************************************************
     */
    {
        global $kfreldefMagic;

        parent::__construct( $kfdbSess );
        $this->kfdbMagic = $kfdbMagic;

        // uid 0 because we don't know who's doing this, and it doesn't matter
        $this->kfrelMagic = new KeyFrameRelation( $this->kfdbMagic, $kfreldefMagic, 0 );
    }

    function MakeMagicSession( $magic_idstr, $sess_idstr = "" )
    /**********************************************************
        Create a session for the given magic login. Set the sess_idstr to the value given, or generate a random one.
        Retrieve action parms in an array.
        Delete the magic login if successful
        Return true if successful
     */
    {
        $this->error = SEEDSESSION_ERR_NOERR;
        $this->kfrSession = NULL;

        /* Does the magic_idstr exist in SEEDSession_MagicLogin?
         */
        if( !($kfrMagic = $this->kfrelMagic->GetRecordFromDB( "magic_idstr='".addslashes($magic_idstr)."'" )) ) {
            $this->error = SEEDSESSION_ERR_MAGIC_NOT_FOUND;

        /* Has the magic login expired?  ts_expiry==0: never expires
         */
        } else if( $kfr->Value("ts_expiry") != 0 && $kfr->Value("ts_expiry") < time() ) {
            $this->error = SEEDSESSION_ERR_EXPIRED;


        } else if( !($raUser = $this->kfdbAuth->KFDB_QueryRA(
                        "SELECT * FROM SEEDSession_Users WHERE _key='".$kfrMagic->Value("uid")."' AND _status=0")) ) {
            $this->error = SEEDSESSION_ERR_UID_UNKNOWN;

        } else {
// TODO: there's a function for this now
            $raSessParms = explode( "&", $kfrMagic->Value("sess_parms") );
            foreach( $raSessParms as $k => $v ) {
                $raSessParms[$k] = urldecode( $v );
            }

            if( !$this->_makeSession( $kfrMagic->Value('uid'),
                                      $raUser['realname'],
                                      $raUser['email'],
                                      $raSessParms, $sess_idstr ) ) {
                $this->error = SEEDSESSION_ERR_GENERAL;
                $this->kfrSession = NULL;
            } else {
                /* Success. Save the action parms and delete the magic login so it can't be reused.
                 */
// TODO: there's a function for this now
                $this->raMagicActParms = explode( "&", $kfrMagic->Value("act_parms") );
                foreach( $this->raMagicActParms as $k => $v ) {
                    $this->raMagicActParms[$k] = urldecode( $v );
                }
                $this->kfdbMagic->KFDB_Exec( "DELETE FROM SEEDSession_MagicLogin WHERE _key='".$kfrMagic->Key()."'" );
            }
        }

        return( $this->kfrSession != NULL );
    }

    function PrepareMagicLogin( $uid, $expiry = 0, $about = "", $sessparms = array(), $actparms = array() )
    /******************************************************************************************************
        Set a magic login in SEEDSession_MagicLogin

        A magic id is generated and returned. When MakeMagicSession is given this id, a session will be created
        for the user given here, with perms and expiry as given here.

        expiry is the duration until expiry of the magic login (not the session that it might create) - 0=no expiry
        about is a description of this magic login for human readability
        parms are converted into url-encoded parm lists for MakeMagicSession()
     */
    {
        $magicid = SEEDStd_UniqueId();
        if( $expiry )  $expiry += time();       // 0 means no expiry, else Unix time
// TODO: there's a function for this now
        $sSessParms = "";
        foreach( $sessparms as $k => $v ) {
            $sSessParms .= $k."=".urlencode($v)."&";
        }
        $sActParms = "";
        foreach( $actparms as $k => $v ) {
            $sActParms .= $k."=".urlencode($v)."&";
        }

        $kfr = $this->kfrelMagic->CreateRecord();
        $kfr->SetValue( "magic_idstr", $magicid );
        $kfr->SetValue( "uid",         intval($uid) );
        $kfr->SetValue( "ts_expiry",   $expiry );
        $kfr->SetValue( "about",       $about );
        $kfr->SetValue( "sess_parms",  $sSessParms );
        $kfr->SetValue( "act_parms",   $sActParms );
        if( !$kfr->PutDBRow() ) {
            $magicid = NULL;
        }

        return( $magicid );
    }
}


//TODO: move to stateless SEEDSessionAuthDB (stateless is better than static because it avoids the weird Init)
class SEEDSessionAuthStatic
/**************************
    Static functions that read/write the Auth data of arbitrary users (so you don't have to be logged in as the affected user)
 */
{
    private static $kfdbAuth;
    private static $uid;      // for recording in _created_by,_updated_by

    public static function Init( $kfdbAuth, $uid = 0 ) { self::$kfdbAuth = $kfdbAuth; self::$uid = $uid; }

    static function GetGroupsFromUserKey( $kUser, $bNames = true )
    /*************************************************************
        Return the list of groups in which kUser is a member: gid1 + UsersXGroups

         bNames : Return array of kGroup=>groupname
        !bNames : Return array of kGroup
     */
    {
        $raRet = array();
        if( ($dbc = self::$kfdbAuth->CursorOpen(
                "SELECT gid1 FROM SEEDSession_Users WHERE _key='$kUser' AND _status=0 "
               ."UNION "
               ."SELECT gid FROM SEEDSession_UsersXGroups WHERE uid='$kUser' AND _status=0" )) ) {
            while( $ra = self::$kfdbAuth->CursorFetch( $dbc ) ) {
                if( !empty($ra[0]) ) {
                    if( $bNames ) {
                        $raRet[$ra[0]] = self::$kfdbAuth->Query1("SELECT groupname FROM SEEDSession_Groups WHERE _key='{$ra[0]}'");
                    } else {
                        $raRet[] = $ra[0];
                    }
                }
            }
            self::$kfdbAuth->CursorClose($dbc);
        }
        asort( $raRet );  // sort by group name

        return( $raRet );
    }

    static function GetPermsFromUserKey( $kUser )
    /********************************************
        Get the given user's permissions from perms and group-perms.

            e.g. for perms  'app1','R'
                            'app2','W'  & group 'app2','R'
                            'app3','RW' & group 'app3'=>'RWA'

                 'perm2modes' => array( 'app1'=>'R', 'app2'=>'RW', 'app3'=>'RWA' ),
                 'mode2perms' => array( 'R'=>array('app1','app2','app3'), 'W'=>array('app2','app3'), 'A'=>array('app3') )
     */
    {
        return( self::getPermsList(
                // Get perms explicitly set for this uid
                "SELECT perm,modes FROM SEEDSession_Perms WHERE _status=0 AND uid='$kUser' "
               ."UNION "
                // Get perms associated with the user's primary group
               ."SELECT P.perm AS perm, P.modes as modes "
               ."FROM SEEDSession_Perms P, SEEDSession_Users U "
               ."WHERE P._status=0 AND U._status=0 AND "
               ."U._key='$kUser' AND U.gid1 >=1 AND P.gid=U.gid1 "
               ."UNION "
                // Get perms from groups
               ."SELECT P.perm AS perm, P.modes as modes "
               ."FROM SEEDSession_Perms P, SEEDSession_UsersXGroups GU "
               ."WHERE P._status=0 AND GU._status=0 AND "
               ."GU.uid='$kUser' AND GU.gid >=1 AND GU.gid=P.gid" ) );
    }

    static function GetPermsFromGroupKey( $kGroup )
    /**********************************************
        Get the given group's permissions in the same format as GetPermsFromUserKey
     */
    {
        return( self::getPermsList( "SELECT P.perm AS perm, P.modes as modes FROM SEEDSession_Perms P "
                                   ."WHERE P._status=0 AND P.gid='$kGroup'" ) );
    }

    static function getPermsList( $sql )
    {
        $raRet = array( 'perm2modes' => array(), 'mode2perms' => array( 'R'=>array(), 'W'=>array(), 'A'=>array() ) );
        if( ($dbc = self::$kfdbAuth->CursorOpen( $sql )) ) {
            while( $ra = self::$kfdbAuth->CursorFetch( $dbc ) ) {
                if( strchr($ra['modes'],'R') && !in_array($ra['perm'], $raRet['mode2perms']['R']) ) { $raRet['mode2perms']['R'][] = $ra['perm']; }
                if( strchr($ra['modes'],'W') && !in_array($ra['perm'], $raRet['mode2perms']['W']) ) { $raRet['mode2perms']['W'][] = $ra['perm']; }
                if( strchr($ra['modes'],'A') && !in_array($ra['perm'], $raRet['mode2perms']['A']) ) { $raRet['mode2perms']['A'][] = $ra['perm']; }
            }
            self::$kfdbAuth->CursorClose( $dbc );
        }
        foreach( $raRet['mode2perms']['R'] as $p ) { $raRet['perm2modes'][$p]  = "R"; }
        foreach( $raRet['mode2perms']['W'] as $p ) { @$raRet['perm2modes'][$p] .= "W"; } // the @ prevents warning if R is not set so index not found for concatenation
        foreach( $raRet['mode2perms']['A'] as $p ) { @$raRet['perm2modes'][$p] .= "A"; }

        return( $raRet );
    }

    static function AddUserToGroup( $kUser, $kGroup )
    /************************************************
        If the user is in the group, return true
        If gid1 is 0, set it to kGroup
        else add a row to UsersXGroups
     */
    {
    	$ok = false;

        $kfrelUsers = self::KfrelUsers();
        $kfrU = $kfrelUsers->GetRecordFromDBKey( $kUser );

        // validate that the user and group exist
        $kfrelGroups = self::KfrelGroups();
        $kfrG = $kfrelGroups->GetRecordFromDBKey( $kGroup );
        if( !$kfrU || !$kfrG ) return( false );

        $raGroups = self::GetGroupsFromUserKey( $kUser, false );
        if( in_array( $kGroup, $raGroups ) ) {
            return( true );
        }

        if( !$kfrU->Value('gid1') ) {
            $kfrU->SetValue( 'gid1', $kGroup );
            $ok = $kfrU->PutDBRow();
        } else {
            $kfrelUxG = self::KfrelUsersXGroups();
            $kfr = $kfrelUxG->CreateRecord();
            $kfr->SetValue( 'uid', $kUser );
            $kfr->SetValue( 'gid', $kGroup );
            $ok = $kfr->PutDBRow();
        }

        return( $ok );
    }

    static function RemoveUserFromGroup( $kUser, $kGroup )
    /*****************************************************
        If the user is not in the group, return true
        If gid1 is kGroup, set it to 0
        Delete any matching UsersXGroups
     */
    {
        $ok = false;

        $kfrelUsers = self::KfrelUsers();
        $kfrU = $kfrelUsers->GetRecordFromDBKey( $kUser );

        // validate that the user and group exist
        $kfrelGroups = self::KfrelGroups();
        $kfrG = $kfrelGroups->GetRecordFromDBKey( $kGroup );
        if( !$kfrU || !$kfrG ) return( false );


        $raGroups = self::GetGroupsFromUserKey( $kUser, false );
        if( !in_array( $kGroup, $raGroups ) ) {
            return( true );
        }

        if( $kfrU->Value('gid1') == $kGroup ) {
            $kfrU->SetValue( 'gid1', 0 );
            $ok = $kfrU->PutDBRow();
        }

        $kfrelUxG = self::KfrelUsersXGroups();
        if( ($kfr = $kfrelUxG->CreateRecordCursor( "uid='$kUser' AND gid='$kGroup'" )) ) {
            while( $kfr->CursorFetch() ) {
                $ok = $kfr->DeleteRow() && $ok;
            }
        }

        return( $ok );
    }

    static function KfrelUsers()
    {
        $kfreldef = array(
            "Tables"=>array( array( "Table" => 'SEEDSession_Users',
                                    "Alias" => 'U',
                                    "Type"  => 'Base',
                                    "Fields" => array( array("col"=>"realname",    "type"=>"S"),
                                                       array("col"=>"email",       "type"=>"S"),
                                                       array("col"=>"password",    "type"=>"S"),
                                                       array("col"=>"lang",        "type"=>"S"),
                                                       array("col"=>"gid1",        "type"=>"I"),
                                                       array("col"=>"eStatus",     "type"=>"S"),
                                                       array("col"=>"sExtra",      "type"=>"S"),
                                                       //  array("col"=>"bEBull",      "type"=>"I"),
                                                       ) ),
                             array( "Table" => 'SEEDSession_Groups',
                                    "Alias" => "G",
                                    "Type"  => "LEFT JOIN",
                                    "LeftJoinOn" => "U.gid1=G._key",
                                    "Fields" => array( array("col"=>"groupname",   "type"=>"S"),
                             ) ) ) );
        return( new KeyFrameRelation( self::$kfdbAuth, $kfreldef, self::$uid ) );
    }

    static function KfrelGroups()
    {
        $kfreldef = array(
            "Tables"=>array( array( "Table" => 'SEEDSession_Groups',
                                    "Type"  => 'Base',
                                    "Fields" => array( array("col"=>"groupname",   "type"=>"S" )
                             ) ) ) );
        return( new KeyFrameRelation( self::$kfdbAuth, $kfreldef, self::$uid ) );
    }

    static function KfrelUsersXGroups()
    {
        $kfreldef = array(
            "Tables"=>array( array( "Table" => 'SEEDSession_UsersXGroups',
                                    "Type"  => 'Base',
                                    "Fields" => array( array("col"=>"uid", "type"=>"I"),
                                                       array("col"=>"gid", "type"=>"I")

                             ) ) ) );
        return( new KeyFrameRelation( self::$kfdbAuth, $kfreldef, self::$uid ) );
    }

    static function KfrelPerms()
    {
        $kfreldef = array(
            "Tables"=>array( array( "Table" => 'SEEDSession_Perms',
                                    "Alias" => 'P',
                                    "Type"  => 'Base',
                                    "Fields" => array( array("col"=>"perm",   "type"=>"S"),
                                                       array("col"=>"modes",  "type"=>"S"),
                                                       array("col"=>"uid",    "type"=>"I"),
                                                       array("col"=>"gid",    "type"=>"I"),
                                                       ) ),
                             array( "Table" => 'SEEDSession_Users',
                                    "Alias" => "U",
                                    "Type"  => "LEFT JOIN",
                                    "LeftJoinOn" => "P.uid=U._key",
                                    "Fields" => array( array("col"=>"realname",   "type"=>"S"),
                                                       array("col"=>"email",      "type"=>"S"),
                                                       ) ),
                             array( "Table" => 'SEEDSession_Groups',
                                    "Alias" => "G",
                                    "Type"  => "LEFT JOIN",
                                    "LeftJoinOn" => "P.gid=G._key",
                                    "Fields" => array( array("col"=>"groupname",   "type"=>"S"),
                             ) ) ) );
        return( new KeyFrameRelation( self::$kfdbAuth, $kfreldef, self::$uid ) );
    }
}

//TODO: move to SEEDSessionAuthDB
function SEEDSessionAuth_Admin_GetUserInfoWithoutSession( $sessAuth, $userid, $status = 0 )
/******************************************************************************************
    Use a SEEDSessionAuth to retrieve the user information from an email address.
    This does not rely on any session being active.

    userid can be a uid or an email
    status defines the _status filter: -1=no filter
 */
{
    return( $sessAuth->GetUserInfo( $userid, $status ) );
}

//TODO: move to SEEDSessionAuthDB
function SEEDSession_Admin_GetUsersFromPerm( $kfdb, $perm, $mode = "" )
/**********************************************************************
    Get array of users that can access perm / mode.

    mode = {char}   : return array( uid1, uid2, ... ) of users with that access mode
    mode = ""       : return array( uid1 => modes, uid2 => modes, ... ) of users with any access mode
 */
{
    $raRet = array();

    if( $mode ) {
        $condPerm = "P.perm='$perm' AND P.modes LIKE '%$mode%'";
    } else {
        $condPerm = "P.perm='$perm' AND P.modes <> '' AND P.modes IS NOT NULL";
    }

    if( ($dbc = $kfdb->KFDB_CursorOpen(
                        // users related to the perm
                        "SELECT P.uid as uid,P.modes as modes "
                        ."FROM SEEDSession_Perms P,SEEDSession_Users U "
                        ."WHERE P.uid<>0 AND P.uid IS NOT NULL AND $condPerm AND P.uid=U._key " // some uid are negative
                        ."UNION "
                        // users in groups related to the perm
                        ."SELECT UG.uid as uid,P.modes as modes "
                        ."FROM SEEDSession_Perms P,SEEDSession_UsersXGroups UG,SEEDSession_Users U "
                        ."WHERE $condPerm AND P.gid>=1 AND P.gid=UG.gid AND UG.uid=U._key "
                        // users with primary group related to perm
                        ."UNION "
                        ."SELECT U._key as uid,P.modes as modes "
                        ."FROM SEEDSession_Users U,SEEDSession_Perms P "
                        ."WHERE $condPerm AND P.gid>=1 AND P.gid=U.gid1" )) ) {
        while( $ra = $kfdb->KFDB_CursorFetch( $dbc ) ) {
            if( $mode ) {
                // make an array of unique uid
                if( !in_array( $ra['uid'], $raRet ) )  $raRet[] = $ra['uid'];
            } else {
                // make an array of uid->modes
                foreach( $ra['modes'] as $c ) {
                    if( !strchr( @$raRet[$ra['uid']], $c ) )  @$raRet[$ra['uid']] .= $c;
                }
            }
        }
        $kfdb->KFDB_CursorClose( $dbc );
    }
    return( $raRet );
}


//TODO: move to SEEDSessionAuthDB
// DEPRECATE: use the static method directly instead of this wrapper
function SEEDSession_GetRAUserGroups( $kfdbAuth, $uid )
/******************************************************
    Return an array of user group ids for the given userid
 */
{
    SEEDSessionAuthStatic::Init($kfdbAuth, 0);
    return( SEEDSessionAuthStatic::GetGroupsFromUserKey( $uid, false ) );
}


function SEEDSession_Setup( $oSetup, &$sReport, $bCreate = false )
/*****************************************************************
    Test whether the tables exist.
    bCreate: create the tables and insert initial data if they don't exist.

    Return true if exists (or create is successful if bCreate); return a report in sReport

    N.B. $oSetup is a SEEDSetup.  This file doesn't include SEEDSetup.php because the setup code is very rarely used.
         Instead, the code that calls this function knows about SEEDSetup.
 */
{
    $bRet = $oSetup->SetupTable( "SEEDSession",              SEEDSESSION_DB_TABLE_SEEDSESSION,              $bCreate, $sReport ) &&
            $oSetup->SetupTable( "SEEDSession_Users",        SEEDSESSION_DB_TABLE_SEEDSESSION_USERS,        $bCreate, $sReport ) &&
            $oSetup->SetupTable( "SEEDSession_Groups",       SEEDSESSION_DB_TABLE_SEEDSESSION_GROUPS,       $bCreate, $sReport ) &&
            $oSetup->SetupTable( "SEEDSession_UsersXGroups", SEEDSESSION_DB_TABLE_SEEDSESSION_USERSXGROUPS, $bCreate, $sReport ) &&
            $oSetup->SetupTable( "SEEDSession_Perms",        SEEDSESSION_DB_TABLE_SEEDSESSION_PERMS,        $bCreate, $sReport ) &&
            $oSetup->SetupTable( "SEEDSession_UsersMetadata",SEEDSESSION_DB_TABLE_SEEDSESSION_USERS_METADATA, $bCreate, $sReport ) &&
            $oSetup->SetupTable( "SEEDSession_GroupsMetadata",SEEDSESSION_DB_TABLE_SEEDSESSION_GROUPS_METADATA, $bCreate, $sReport ) &&
            $oSetup->SetupTable( "SEEDSession_MagicLogin",   SEEDSESSION_DB_TABLE_SEEDSESSION_MAGICLOGIN,   $bCreate, $sReport );

    /* Initialize users (dev, friend, guest) with typical groups, permissions
     */
    if( $bRet && !$oSetup->kfdb->Query1("SELECT count(*) FROM SEEDSession_Users") ) {
        foreach( array( 1 => array('Developer',      'dev',    1),
                        2 => array('Trusted Friend', 'friend', 2),
                        3 => array('Guest',          'guest',  3) )  as $uid => $ra )
        {
            $bRet = $oSetup->kfdb->Execute( "INSERT INTO SEEDSession_Users (_key,_created,_updated,realname,email,password,gid1,eStatus) "
                                           ."VALUES ($uid, NOW(), NOW(), '{$ra[0]}', '{$ra[1]}', 'seeds', {$ra[2]}, 'ACTIVE')");
            $sReport .= ($bRet ? "Inserted" : "Failed to insert") ." SEEDSession user '{$ra[1]}'.<BR/>";
        }
    }
    if( $bRet && !$oSetup->kfdb->Query1("SELECT count(*) FROM SEEDSession_Groups") ) {
        foreach( array( 1 => 'Dev Group',
                        2 => 'Friend Group',
                        3 => 'Guest Group' )  as $uid => $sGroup )
        {
            $bRet = $oSetup->kfdb->Execute( "INSERT INTO SEEDSession_Groups (_key,_created,_updated,groupname) "
                                           ."VALUES ($uid, NOW(), NOW(), '$sGroup')");
            $sReport .= ($bRet ? "Inserted" : "Failed to insert") ." SEEDSession group '$sGroup'.<BR/>";
        }
    }
    if( $bRet && !$oSetup->kfdb->Query1("SELECT count(*) FROM SEEDSession_UsersXGroups") ) {
        foreach( array( array(1,2), // dev (uid 1) is in group Friend (2)
                        array(1,3), // dev (uid 1) is in group Guest (3)
                        array(2,3)  // friend (2)  is in group Guest (3)
                      ) as $ra )
        {
            $bRet = $oSetup->kfdb->Execute( "INSERT INTO SEEDSession_UsersXGroups (_key,_created,_updated,uid,gid) "
                                           ."VALUES (NULL, NOW(), NOW(), '{$ra[0]}', '{$ra[1]}')");
            $sReport .= ($bRet ? "Inserted" : "Failed to insert") ." SEEDSession UserXGroup '{$ra[0]} => {$ra[1]}'.<BR/>";
        }
    }
    if( $bRet && !$oSetup->kfdb->Query1("SELECT count(*) FROM SEEDSession_Perms") ) {
                          //  perm              modes   uid     gid
        foreach( array( array('SEEDSessionUGP', 'RWA',       1,  'NULL'),
                        array('SEEDPerms',      'RWA',       1,  'NULL'),
                        array('DocRepMgr',      'A',         1,  'NULL'),
                        array('DocRepMgr',      'W',    'NULL',       2),
                        array('DocRepMgr',      'R',    'NULL',       3),
                        array('MBRORDER',       'RWA',       1,  'NULL'),
                        array('MBRORDER',       'RW',        2,  'NULL'),
                        array('events',         'RW',   'NULL',       2),
                        array('SL',             'A',    'NULL',       1),
                        array('SL',             'W',    'NULL',       2),
                        array('SL',             'R',    'NULL',       3),
                        array('SLDesc',         'RWA',  'NULL',       1),
                        array('SLDesc',         'RW',   'NULL',       3),
                        array('SLDesc',         'RW',   'NULL',       3),
                        array('sed',            'RWA',  'NULL',       2),
                        array('sed',            'R',    'NULL',       3),
                      ) as $ra )
        {
            $bRet = $oSetup->kfdb->Execute( "INSERT INTO SEEDSession_Perms (_key,_created,_updated,perm,modes,uid,gid) "
                                           ."VALUES (NULL, NOW(), NOW(), '{$ra[0]}', '{$ra[1]}', {$ra[2]}, {$ra[3]})");
            $sReport .= ($bRet ? "Inserted" : "Failed to insert") ." SEEDSession Perm {$ra[0]} {$ra[1]} => {$ra[2]} {$ra[3]}.<BR/>";
        }
    }

    return( $bRet );
}


$SEEDSessionAuth_Local =
array(
    // Cr�ez un compte
    // Votre compte
    // Ouvrez  une session
    // Retour au pr�c�dent
    // Changez Le E-mail
    // Changez Le Mot de passe
    // Vous avez d�j� un compte?
    //   Veuillez entrer votre adresse de courriel
    //   Veuillez entrer votre mot de passe
    // Vous avez oubli� votre mot de passe? Cliquez ici.
    // Vous n'avez pas de compte?
    //   Pr�nom:
    //   Nom:
    //   Veuillez entrer votre adresse de courriel:
    // Modifier les informations de votre compte
    // Vos param�tres
    // Ajouter - to add
    // Modifier - to modify
    // Supprimer - to remove

    "Your email address" => array(
            "EN" => "Your email address",
            "FR" => "Votre adresse de courriel" ),

    "Password" => array(
            "EN" => "Your password",
            "FR" => "Votre mot de passe" ),

    "Enter a password" => array(
            "EN" => "Enter a password",
/* ! */     "FR" => "Entrez un mot de passe" ),

    "Enter a password again" => array(
            "EN" => "Re-enter password",
/* ! */     "FR" => "Entrez le mot de passe encore" ),

    "Login" => array(
            "EN" => "Sign in",
            "FR" => "Ouvrez une session" ),

    "Logout" => array(
            "EN" => "Sign out",
/* ! */     "FR" => "Fermez la session" ),

    "Don't have an account?" => array(
            "EN" => "Don't have an account?",
            "FR" => "Vous n'avez pas un compte?"),

    "Create" => array(
            "EN" => "Create",
            "FR" => "Cr&eacute;ez" ),

    "Create an account" => array(
            "EN" => "Create an account",
            "FR" => "Cr&eacute;ez un compte" ),

    "Forgot your password?" => array(
            "EN" => "Forgot your password?",
            "FR" => "Oubliez votre mot de passe?" ),

    "Send me my password" => array(
            "EN" => "Send me my password",
            "FR" => "Envoyez-moi mon mot de passe" ),

    "Unknown user or password" => array(
            "EN" => "Unknown user or password",
/* ! */     "FR" => "L'adresse courriel ou le mot de passe n'est pas correct" ),


    "CreateAcct_intro" => array(
            "EN" => "<H2>Create a new account</H2>"
                   ."<P>Type your email address here and choose a password.</P>",
/* ! */     "FR" => "<H2>Create a new account</H2>"
                   ."<P>Type your email address here and choose a password.</P>" ),

    "CreateAcct_success" => array(
            "EN" => "<H2>Check your Email</H2>"
                   ."<P>Thanks for creating a new Seeds of Diversity user account. You should receive an email shortly containing a confirmation.</P>"
                   ."<P>Please click on the link in the email to complete your registration.</P>",
/* ! */     "FR" => "<H2>Check your Email</H2>"
                   ."<P>Thanks for creating a new Seeds of Diversity user account. You should receive an email shortly containing a confirmation.</P>"
                   ."<P>Please click on the link in the email to complete your registration.</P>" ),

    "CreateAcct_Passwords must match" => array(
            "EN" => "The passwords must match. Please enter a password again.",
/* ! */     "FR" => "The passwords must match. Please enter a password again." ),

    "CreateAcct_user_already_exists" => array(
            "EN" => "The account %1% already exists",
/* ! */     "FR" => "Le compte %1% existe" ),

    "CreateAcct_user_not_valid_email" => array(
            "EN" => "%1% is not a valid email address. Please enter your email address.",
/* ! */     "FR" => "%1% is not a valid email address. Please enter your email address." ),

    "CreateAcct_eSubject" => array(
            "EN" => "Seeds of Diversity web site - Your new account",
/* ! */     "FR" => "Seeds of Diversity web site - Your new account" ),

    "SendPassword_eSubject" => array(
            "EN" => "Seeds of Diversity web site - Password reminder",
/* ! */     "FR" => "Seeds of Diversity web site - Password reminder" ),

    "ChangePwd_eSubject" => array(
            "EN" => "Seeds of Diversity web site - Password change notice",
/* ! */     "FR" => "Seeds of Diversity web site - Password change notice" ),


//    "AddUser_Mail_Subject" => array(
//            "EN" => "Your Pollination Canada password",
//            "FR" => "Votre mot de passe de Pollinisation Canada" ),

    "CreateAcct_email_body" => array(
            "EN" => "You are receiving this email because you registered a new account on Seeds of Diversity's web site.\n\n"
                   ."Your sign-in information is:\n\n"
                   ."     Email: %2%\n"
                   ."     Password: %3%\n\n"
                   ."First, you must activate your new account by clicking on this link.\n\n%1%\n\n"
                   ."This activation step confirms that you are the owner of this email address.\n"
                   ."Please click on this link now, and enjoy your new Seeds of Diversity web site!\n\n"
                   ."If you have received this message in error, please contact webmaster@seeds.ca",
/* ! */     "FR" => "You are receiving this email because you registered a new account for the Pollination Canada program.\n\n"
                   ."Your sign-in information is:\n\n"
                   ."     Email: %1%\n"
                   ."     Password: %2%\n\n"
                   ."Please sign in to your account at www.pollinationcanada.ca\n\n"
                   ."If you have received this message in error, please contact info@pollinationcanada.ca" ),

    "CreateAcct_success" => array(
            "EN" => "<H2>Check Your Email</H2>"
                   ."<P>Your new Seeds of Diversity web account has been created, but you must activate it to confirm your email address.</P>"
                   ."<P>An activation link has been sent by email. Please check your email and follow the instructions.</P>",
/* ! */     "FR" => "<H2>Check Your Email</H2>"
                   ."<P>Your new Seeds of Diversity web account has been created, but you must activate it to confirm your email address.</P>"
                   ."<P>An activation link has been sent by email. Please check your email and follow the instructions.</P>" ),

//    "AddUser_check_your_email" => array(
//            "EN" => "<B>Your Pollination Canada password has been emailed to %1%</B>",
///* ! */     "FR" => "<B>Votre mot de passe de Pollinisation Canada est envoy� par courriel � l'adresse %1%</B>" ),

    "SendPassword_intro" => array(
            "EN" => "<H2>We'll send your password to you by email</H2>"
                   ."<P>Type your email address here and click 'Send me my password'. You will receive an email shortly.</P>",
            "FR" => "<H2>We'll send your password to you by email</H2>"
/* ! */            ."<P>Type your email address here and click 'Send me my password'. You will receive an email shortly.</P>" ),

    "Cancel" => array(
            "EN" => "Cancel",
/* ! */     "FR" => "Cancel" ),

    "Back to Login" => array(
            "EN" => "Back to Login",
/* ! */     "FR" => "Back to Login" ),

    "Back Home" => array(
            "EN" => "Back Home",
/* ! */     "FR" => "Back Home" ),

    "SendPassword_user_not_registered" => array(
            "EN" => "<H3>User not registered</H3>"
                    ."<P>User '%1%' is not registered. You might be using a different email address than the one that we have on file. "
                     ."Try again using a different email address.  Please contact our office if you need help.</P>",
/* ! */     "FR" => "<H3>User not registered</H3>"
                    ."<P>User '%1%' is not registered. You might be using a different email address than the one that we have on file. "
                     ."Try again using a different email address.  Please contact our office if you need help.</P>" ),

    "SendPassword_email_body" => array(
            "EN" => "You have requested a password reminder from Seeds of Diversity. Please use the following "
                   ."to login to our web site.\n\nWeb site: %1%\nUser:     %2%\nPassword: %3%\n\n"
                   ."If you have any questions, please contact our office at 1-866-509-SEED (7333) or office@seeds.ca",
/* ! */     "FR" => "You have requested a password reminder from Seeds of Diversity. Please use the following "
                   ."to login to our web site.\n\nWeb site: %1%\nUser:     %2%\nPassword: %3%\n\n"
                   ."If you have any questions, please contact our office at 1-866-509-7333 or courriel@semences.ca" ),

    "SendPassword_success" => array(
            "EN" => "<H2>Your password has been sent to you by email</H2>"
                   ."<P>You should receive an email shortly containing login instructions for this web site.</P>",
/* ! */     "FR" => "<H2>Your password has been sent to you by email</H2>"
                   ."<P>You should receive an email shortly containing login instructions for this web site.</P>" ),

    "ChangePwd_Passwords must match" => array(
            "EN" => "The passwords must match. Please enter the new password again.",
/* ! */     "FR" => "The passwords must match. Please enter the new password again." ),

    "ChangePwd_CurrPassword_incorrect" => array(
            "EN" => "The current password is not correct. Please enter your password again.",
/* ! */     "FR" => "The current password is not correct. Please enter your password again." ),

    "ChangePwd_intro" => array(
            "EN" => "<H2>Change Your Password</H2>"
                   ."<P>Please type your current password for verification and choose a new password.</P>",
/* ! */     "FR" => "<H2>Change Your Password</H2>"
                   ."<P>Please type your current password for verification and choose a new password.</P>" ),
    "ChangePwd_success" => array(
            "EN" => "<H2>Your Password Has Been Changed</H2>"
                   ."<P>Thanks for changing your password. We're pleased to provide this secure web site for your use.</P>",
/* ! */     "FR" => "<H2>Your Password Has Been Changed</H2>"
                   ."<P>Thanks for changing your password. We're pleased to provide this secure web site for your use.</P>" ),
    "ChangePwd_email_notice" => array(
            "EN" => "Dear %1%,\n\nThis is a notice to let you know that your password was changed on Seeds of Diversity's web site.  If you changed your password, "
                   ."and this doesn't come as a surprise, please disregard this notice.  If you didn't change the password yourself, and you suspect "
                   ."that it was done without your permission, please contact our office as soon as possible at 1-866-509-SEED (7333) or office@seeds.ca"
                   ."\n\nThank you very much.",
/* ! */     "FR" => "Dear %1%,\n\nThis is a notice to let you know that your password was changed on Seeds of Diversity's web site.  If you changed your password, "
                   ."and this doesn't come as a surprise, please disregard this notice.  If you didn't change the password yourself, and you suspect "
                   ."that it was done without your permission, please contact our office as soon as possible at 1-866-509-SEED (7333) or office@seeds.ca"
                   ."\n\nThank you very much." ),

    "Change Password" => array(
            "EN" => "Change Password",
/* ! */     "FR" => "Change Password" ),
    "Current_password" => array(
            "EN" => "Enter your current password",
/* ! */     "FR" => "Enter your current password" ),
    "New_password" => array(
            "EN" => "Type a new password",
/* ! */     "FR" => "Type a new password" ),
    "New_password_again" => array(
            "EN" => "Type the new password again",
/* ! */     "FR" => "Type the new password again" ),


    "ActivateAcct_success" => array(
            "EN" => "<H2>Your account has been activated</H2>"
                   ."<P>You can now login to the Seeds of Diversity web site.</P>",
/* ! */     "FR" => "<H2>Your account has been activated</H2>"
                   ."<P>You can now login to the Seeds of Diversity web site.</P>" ),

    "ActivateAcct_error" => array(
            "EN" => "<H2>There is a problem activating your account</H2>"
                   ."<P>Please contact our office for assistance.</P>",
/* ! */     "FR" => "<H2>There is a problem activating your account</H2>"
                   ."<P>Please contact our office for assistance.</P>" ),

    "Logout_success" => array(
            "EN" => "<H2>Goodbye %1%</H2><P>You are now logged out.</P>",
/* ! */     "FR" => "<H2>Goodbye %1%</H2><P>You are now logged out.</P>" ),
    "Logout_expired" => array(
            "EN" => "<H2>Session Expired</H2><P>Your session already expired. You are now logged out.</P>",
/* ! */     "FR" => "<H2>Session Expired</H2><P>Your session already expired. You are now logged out.</P>" ),
    "Logout_failed" => array(
            "EN" => "<H2>You are not logged in</H2>",
/* ! */     "FR" => "<H2>You are not logged in</H2>" ),


    "login_err_general" => array(
            "EN" => "<P>An error occurred during login.</P><H2>Please login again</H2>",
/* ! */     "FR" => "<P>An error occurred during login.</P><H2>Please login again</H2>" ),

    "login_err_expired" => array(
            "EN" =>  "<P>Your session has expired. For security, please login again.</P>",
/* ! */     "FR" =>  "<P>Your session has expired. For security, please login again.</P>" ),

    /* we are intentionally ambiguous about whether the problem is the uid or password, so people can't use this to test for valid member email addresses
     */
    "login_err_uid_unknown" => array(
            "EN" => "<P>The user id or password is not recognised. Please try again.</P>",
/* ! */     "FR" => "<P>The user id or password is not recognised. Please try again.</P>" ),
    "login_err_wrong_password" => array(
            "EN" => "<P>The user id or password is not recognised. Please try again.</P>",
/* ! */     "FR" => "<P>The user id or password is not recognised. Please try again.</P>" ),

    "login_err_perm_not_found" => array(
            "EN" => "<P>You do not have permission to access this page.</P>",
/* ! */     "FR" => "<P>You do not have permission to access this page.</P>" ),

    "login_err_userstatus_pending" => array(
            "EN" => "<H3>User not activated</H3>"
                    ."<P>User '%1%' is not activated. If you have recently created this account, you should receive an email with an activation link. "
                     ."Please click on the activation link in your email, or contact our office if you need help.</P>",
/* ! */     "FR" => "<H3>User not activated</H3>"
                    ."<P>User '%1%' is not activated. If you have recently created this account, you should receive an email with an activation link. "
                     ."Please click on the activation link in your email, or contact our office if you need help.</P>" ),

    "login_err_userstatus_inactive" => array(
            "EN" => "<P>User '%1%' has been deactivated. Please contact our office for assistance.</P>",
/* ! */     "FR" => "<P>User '%1%' has been deactivated. Please contact our office for assistance.</P>" ),

);


?>
