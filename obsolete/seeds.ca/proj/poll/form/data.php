<?

if( !defined("SITEROOT") )  define("SITEROOT", "../../../");
include_once( SITEROOT."site.php" );
include_once( SEEDCOMMON."siteStart.php" );
include_once( STDINC."SEEDForm.php" );
include_once( STDINC."SEEDLocal.php" );
include_once( STDINC."KeyFrame/KFRForm.php" );
include_once( "_dataEntry.php" );

list($kfdb,$sess) = SiteStartSessionAuth();


site_define_lang( $sess->SmartGPC( 'lang', array('','EN','FR') ) );
$sess->VarSet('lang', ($lang = SITE_LANG) );


/* The state machine for this app
 */
define( "PCDE_STATE_START",    "START" );
define( "PCDE_STATE_LOGIN",    "LOGIN" );
define( "PCDE_STATE_ADDUSER",  "ADDUSER" );
define( "PCDE_STATE_HOME",     "HOME" );
define( "PCDE_STATE_PASSWORD", "PASSWORD" );
define( "PCDE_STATE_SITE",     "SITE" );
define( "PCDE_STATE_VISIT",    "VISIT" );


class PollCan_DataEntry_App extends PollCan_DataEntry
/****************************************************
    Implements a state-machine app for data entry of pollinator observations.
    Reporting functions are in the base app.

    GPC:
        pcdeState   = next state transition
        pcdeUser    = LOGIN, ADDUSER: user
        pcdePwd     = LOGIN: password
        pcdePwd1/2  = ADDUSER, PASSWORD: passwords must be the same
        pcdeSubmit  = a form was submitted (triggers action based on state, else we just draw the form)
        pcdeSite    = SITE, VISIT: _key of pollcan_sites
        pcdeVisit   = VISIT: _key of pollcan_visits (zero for Create, non-zero for Edit)

    Sess:
        pcdeState   = current state
 */
{
    var $sLoginMsg = "";


    function PollCan_DataEntry_App( &$kfdb, &$sess, $lang )
    /******************************************************
     */
    {
        parent::PollCan_DataEntry( $kfdb, $sess, $lang );
    }

    function App()
    /*************
        Just call this
     */
    {
        if( $this->bDebug ) {
            echo "<B>REQUEST:</B> "; print_r( $_REQUEST ); echo "<BR>";
            echo "<B>SESSION:</B> "; print_r( $_SESSION ); echo "<BR>";
        }

        $this->Style();                 // output the CSS for all possible pages
        $this->EstablishUserSession();  // establish current user session
        $this->Update();                // based on state and GPC, update the database
        $this->Controller();            // based on state and GPC, set the new state
        $this->DrawPage();              // based on state, draw the page
    }

    function Controller()
    /********************
        Based on the current state from the session, or found in GPC, evaluate parms and set the new state accordingly.

        Users, Sites, Visits are added/edited using this method:
            - forms are initialized with _keys in POST. If zero, it's a Create form, else it's an Edit form.
            - forms submit to their own states, propagate _key
            - forms submit pcdeSubmit.
                  If this is absent, we're initializing the form so just draw it with current db valies.
                  Else if this is Cancel/Retour, the user hit the cancel button, so go Home.
                  Else, get parms from POST.
            - if parms validate, insert/update db, proceed to the home state
              else, draw the form with POSTed parms instead of db values. Report errors, let the user try again.
     */
    {
        $sErrorMsg = "";

        $state = $this->sess->SmartGPC( 'pcdeState', array( PCDE_STATE_START, PCDE_STATE_LOGIN, PCDE_STATE_ADDUSER,
                                                            PCDE_STATE_HOME, PCDE_STATE_PASSWORD, PCDE_STATE_SITE, PCDE_STATE_VISIT ) );

        /* If not logged in, disallow states that require login
         */
        if( !$this->IsUserActive() && !($state == PCDE_STATE_START || $state == PCDE_STATE_LOGIN || $state == PCDE_STATE_ADDUSER) ) {
            $state = PCDE_STATE_LOGIN;
        }

        /* pcdeSubmit is the name of the submit button on each form.
         *     = blank            : form is initializing or there is no form in this state
         *     = Cancel or Retour : the user cancelled - go to Start
         *     = else             : form was submitted
         */
        $p = @$_POST['pcdeSubmit'];
        $bSubmitted = !empty($p);
        if( $p == $this->S('button_cancel') ) {
            $state = PCDE_STATE_START;
        }


        switch( $state ) {
            case PCDE_STATE_START:
                /* Initial and default state - decide whether we need to login
                 */
                $state = ($this->IsUserActive() ? PCDE_STATE_HOME : PCDE_STATE_LOGIN);
                break;

            case PCDE_STATE_LOGIN:
                /* Kill any current user session.
                 * If login parms present, try to create a session.
                 *      success - go to HOME
                 *      fail - prompt to try again
                 */
                $this->sLoginMsg = "";
                if( $this->IsUserActive() ) {
                    $this->UserLogout();
                }

                $soU = new SEEDInput_Get( 'pcdeUser' );
                $soP = new SEEDInput_Get( 'pcdePwd' );
                if( $soU['plain'] && $soP['plain'] ) {
                    if( ($ra = $this->kfdb->QueryRA( "SELECT * FROM pollcan_users WHERE email='{$soU['db']}' "
                                                          ."AND password='{$soP['db']}'" ))
                        && $ra['email']==$soU['plain'] )
                    {
                        // make a new session
                        $this->UserActivate( $ra['_key'], $ra['email'] );
                        $state = PCDE_STATE_HOME;
                    } else {
                        $this->sLoginMsg = $this->S( 'Unknown user or password' );
                    }
                }
                break;

            case PCDE_STATE_ADDUSER:
                /* If valid user session, this is Edit User
                 * else this is Create New User.
                 *
                 * Create: if new user parms submitted, validate password, check for duplicate user id, try to create the new user and create a new session

                    PASSWORD SHOULD BE GENERATED AND MAILED

                 *
                 * Store info about user.
                 *      success - go to HOME
                 *      else report errors
                 */
                $bOk = false;
                if( $bSubmitted ) {
                    $soU = new SEEDInput_Get( 'pcdeUser' );
                    if( $soU['plain'] ) {
                        if( ($ra = $this->kfdb->QueryRA( "SELECT * FROM pollcan_users WHERE email='{$soU['db']}'" )) ) {
                            $this->sLoginMsg = $this->S('Account_U_already_exists', array($soU['ent']));
                        } else {
                            if( $this->bDebug ) {
                                $pwd = "g7ytlux";
                            } else {
                                $pwd = substr(md5(time),0,6);
                            }

                            $this->kfdb->KFDB_Execute( "INSERT INTO pollcan_users (email,password) "
                                                      ."VALUES ('{$soU['db']}','".addslashes($pwd)."')" );
                            MailFromOffice( $soU['plain'], $this->S('AddUser_Mail_Subject'),
                                            $this->S('AddUser_Mail_Body', array($soU['ent'],$pwd) ) );
                            $this->sLoginMsg = $this->S( 'AddUser_check_your_email', array($soU['ent']) );
                            $state = PCDE_STATE_LOGIN;
                        }
                    }
                }
                break;

            case PCDE_STATE_HOME:
                // no action
                break;

            case PCDE_STATE_PASSWORD:
                /* If the password form was submitted, verify that the passwords are valid and matching. Update db.
                 *      success - go to HOME
                 *      fail - report errors
                 */
                if( $bSubmitted ) {
                    // validate password1 and 2
                    //      success: update db, $state = PCDE_STATE_HOME;
                    //      fail: report error
                }
                break;

            case PCDE_STATE_SITE:
                /* If pcdeSite == 0, this is Add Site
                 * else, this is Edit Site
                 *
                 * If the Site form was submitted, validate, insert/update db
                 *      success - go to HOME
                 *      fail - report errors
                 */
                if( $bSubmitted ) {
                    $kfr = $this->GetKFRSiteFromParms();

                    // validate - nSite and siteName must be unique
                    if( $this->kfdb->KFDB_Query1(
                        "SELECT count(*) FROM pollcan_sites "
                        ."WHERE (nSite='".addslashes($kfr->value('nSite'))."' OR siteName='".addslashes($kfr->value('siteName'))."') "
                               ."AND fk_pollcan_users='".$this->GetUserKey()."' "
                               ."AND _key <> ".intval($kfr->Key()) ) ) {
                        $this->sErrorMsg = $this->S('Site # and Location Name must not duplicate another site');
                    } else {
                        $kfr->PutDBRow();
                        $state = PCDE_STATE_HOME;
                    }
                }
                break;

            case PCDE_STATE_VISIT:
                /* This form can transition through several modes, as insects and flowers are added to the db during recording of a visit.
                 *
                 * Create: pcdeSite is POSTed
                 * Edit:   pcdeVisit is POSTed
                 *
                 * If the visit form was submitted, validate, insert/update db
                 *      success - go to HOME
                 *      fail - report errors
                 *
                 * If a new insect/flower is added, validate and add to pollcan_insects/pollcan_flowers.
                 * It might be necessary to store visit parms in the session, unless they can all be POSTed through this process.
                 */
                if( $bSubmitted ) {
                    $kfr = $this->GetKFRVisitFromParms();
                    if( !$kfr || !$kfr->value('fk_pollcan_sites') ) { return; } // probably incorrect error behaviour

                    // validate - nVisit must be unique
                    if( $this->kfdb->KFDB_Query1(
                        "SELECT count(*) FROM pollcan_visits "
                        ."WHERE (nVisit='".intval($kfr->value('nVisit'))."' "
                               ."AND fk_pollcan_sites='".intval($kfr->value('fk_pollcan_sites'))."' "
                               ."AND _key <> ".intval($kfr->Key()) ) ) {
                        $this->sErrorMsg = $this->S('Visit # must not duplicate another visit');
                    } else {
                        $kfr->PutDBRow();
                        $this->updateInsectsFlowers( $kfr );
                        //$state = PCDE_STATE_HOME;     stay in VISIT state to show the new visit
                    }
                }
                break;
        }

        $this->sess->VarSet( 'pcdeState', $state );
    }

    function DrawPage()
    /******************
     */
    {
        if( $this->bDebug ) { echo "<B>State in DrawPage".$this->sess->VarGet('pcdeState')."</B><BR>"; }

        switch( $this->sess->VarGet( 'pcdeState' ) ) {
            case PCDE_STATE_START:
                // shouldn't get here
                break;

            case PCDE_STATE_LOGIN:
                /* Show login screen
                 * Submit -> LOGIN
                 * Link to subscreen - send password by email
                 * Link to ADDUSER page with no parms
                 */
                echo "<IMG src='".$this->S('img_login')."'><BR><BR>";
                echo $this->sLoginMsg;
                echo "<FORM method='post' action='${_SERVER['PHP_SELF']}'>"
                    .SEEDForm_Hidden('pcdeState', PCDE_STATE_LOGIN)
                    ."<TABLE border=0 cellspacing=0 cellpadding=10>"
                    ."<TR><TD valign='top'>".$this->S('Your email address')."</TD>"
                    ."<TD valign='top'><INPUT type='text' name='pcdeUser'></TD></TR>"
                    ."<TR><TD valign='top'>".$this->S('Password')."</TD>"
                    ."<TD valign='top'><INPUT type='password' name='pcdePwd'></TD></TR>"
                    ."<TR><TD valign='top'>&nbsp;</TD>"
                    ."<TD valign='top'><INPUT type='submit' value='".$this->S('Login')."'></TD></TR>"
                    ."</TABLE></FORM>"
                    ."<BR><BR>"
                    ."<P>".$this->S("Don't have an account?")."<BR>"
                    ."<FORM method='post' action='${_SERVER['PHP_SELF']}'>"
                    .SEEDForm_Hidden('pcdeState', PCDE_STATE_ADDUSER)
                    ."<INPUT type='submit' name='pcdeSubmit' value='".$this->S('Create an account')."'></FORM></P>";
                break;

            case PCDE_STATE_ADDUSER:
                /* If valid user session, this is Edit User
                 * else this is Create New User.
                 *
                 * Show the User form, with info from active session if exists.
                 * Submit -> ADDUSER:
                 * Cancel -> START
                 */
                echo "<IMG src='".$this->S('img_login')."'><BR><BR>";
                echo $this->sLoginMsg;
                // show Create New Observer / Edit Observer screen.  Top portion of Site form (the observer data).
                // Create: Get email, password1/2
                // Submit to ADDUSER
                // Cancel to START
                echo "<H3>".$this->S('Create an account')."</H3>";
                echo "<FORM method='post' action='${_SERVER['PHP_SELF']}'>"
                    .SEEDForm_Hidden('pcdeState', PCDE_STATE_ADDUSER)
                    ."<TABLE border=0 cellspacing=0 cellpadding=10>"
                    ."<TR><TD valign='top'>".$this->S('Your email address')."</TD>"
                    ."<TD valign='top'><INPUT type='text' name='pcdeUser'></TD></TR>"
                    ."<TR><TD valign='top'>&nbsp;</TD>"
                    ."<TD valign='top'><INPUT type='submit' name='pcdeSubmit' value='".$this->S('Create')."'>"
                    .SEEDStd_StrNBSP("",6)
                    ."<INPUT type='submit' name='pcdeSubmit' value='".$this->S('button_cancel')."'>"
                    ."</TD></TR>"
                    ."</TABLE></FORM>";
                break;


            case PCDE_STATE_PASSWORD:
                /* Show the Change Password screen.
                 * Submit -> PASSWORD
                 * Cancel -> HOME
                 */
                // show Change Password
                // Submit to PASSWORD
                // Cancel to HOME
                break;

            case PCDE_STATE_HOME:
            case PCDE_STATE_SITE:
                /* If pcdeSite == 0, this is Add Site
                 * else, this is Edit Site
                 *
                 * Show the Add/Edit Site form.
                 * Submit -> SITE
                 * Cancel -> HOME
                 */
                //$this->drawPage_AddSite();
            case PCDE_STATE_VISIT:
                /* Create: pcdeSite is POSTed
                 * Edit:   pcdeVisit is POSTed
                 *
                 * Show the Add/Edit Visit
                 * Weather
                 * Add an insect, or choose from a list of yours, or choose from a standard list of not-yet-seen
                 * Add a flower, or choose from a list of yours
                 * Submit -> VISIT
                 * Cancel -> HOME
                 */
                $this->drawPage_Home();
                break;
        }
    }


    function Update()
    /****************
     */
    {
    }

    /* User:
        Name, Email, Street Address, City, Province, Postal Code



       Site:
        Site # (suggest)
        Location section - Location name will be used to identify the site, should be unique
        Landscape section


       Visit:
        Site #, Visit #(suggest)
        Date, Start time, Time spent
        Weather

        Flowers
        Insects

     */

    function updateInsectsFlowers( $kfrV )
    /*************************************
        Given a valid, updated Visit record, get parms that add/modify insects and flowers for the visit

        Delete is not currently possible
     */
    {
        for( $i = 1; isset($_REQUEST["i_key$i"]); ++$i ) {
            $k = intval($_REQUEST["i_key$i"]);

            if( $k ) {
                $kfrI = $this->kfrelInsects->GetRecordFromDBKey( $k );
                if( $kfrI->value('fk_pollcan_visits') != $kfrV->Key() ) {
                    // bad key!
                    continue;
                }
            } else {
                $kfrI = $this->kfrelInsects->CreateRecord();
                $kfrI->SetValue( 'fk_pollcan_visits', $kfrV->Key() );
            }
            $kfrI->SetValue( 'name',      SEEDSafeGPC_GetStrPlain( "i_name$i" ) );
            $kfrI->SetValue( 'size_mm',   SEEDSafeGPC_GetInt( "i_size$i" ) );
            $kfrI->SetValue( 'eType',     SEEDSafeGPC_GetStrPlain( "i_type$i" ) );
            $kfrI->SetValue( 'nObserved', SEEDSafeGPC_GetInt( "i_nObserved$i" ) );
            $kfrI->SetValue( 'last_seen', SEEDSafeGPC_GetStrPlain( "i_seen$i" ) );

            if( $kfrI->IsEmpty( 'name' ) && $kfrI->IsEmpty( 'eType' ) &&
                $kfrI->IsEmpty( 'size_mm' ) && $kfrI->IsEmpty( 'nObserved' ) ) {

                // consider this to be an empty input row

                continue;
            }

            $kfrI->PutDBRow();
        }
    }


    function drawFormText( $kfr, $idLabel, $fld, $size = 10, $colspan=1 )
    /************************************************************************
     */
    {
        return( "<TH>".$this->S($idLabel)."</TH>"
               ."<TD colspan='$colspan'>".KFRForm_Text( $kfr, "", $fld, $size )."</TD>" );
    }


    function drawForm_Site()
    /***********************
     */
    {
        //$kfr = $kSite ? $this->kfrelSites->GetRecordFromDBKey($kSite) : $this->kfrelSites->CreateRecord();
        $kfr =& $this->GetKFRSiteFromParms();
        $kSite = $kfr->Key();

        if( !$kSite ) {
            // suggest site number to be the next one
            $kfr->SetValue( "nSite", intval($this->kfdb->KFDB_Query1("SELECT MAX(nSite) FROM pollcan_sites WHERE fk_pollcan_users='".$this->GetUserKey()."'"))+1 );
        }

        echo "<DIV class='pc_box'>";
        echo "<H2>".$this->S( $kSite ? 'Edit Site' : 'Add a Site')."</H2>";
        echo "<P>".$this->S('AddSite_instructions')."</P>";

        if( !empty($this->sErrorMsg) ) {
            echo "<P style='color:red'>".$this->sErrorMsg."</P>";
        }

        echo "<FORM method='post' action='${_SERVER['PHP_SELF']}'>"
            .SEEDForm_Hidden( 'pcdeState', PCDE_STATE_SITE )
            .SEEDForm_Hidden( 'pcdeSite', $kSite )
            ."<TABLE class='pc_form_table' cellspacing=0 cellpadding=0>"
            ."<TR>".$this->drawFormText( $kfr, 'Site #', 'nSite', 5, 5 )."</TR>"
            ."<TR>".$this->drawFormText( $kfr, 'Location Name', 'siteName', 30 )
                   .$this->drawFormText( $kfr, 'Distance from road', 'distance from road', 5, 3 )."</TR>"
            ."<TR>".$this->drawFormText( $kfr, 'Latitude', 'latitude', 10 )
                   .$this->drawFormText( $kfr, 'Address', 'address', 30, 3 )."</TR>"
            ."<TR>".$this->drawFormText( $kfr, 'Longitude', 'longitude', 10 )
                   .$this->drawFormText( $kfr, 'City', 'city', 10 )
                   .$this->drawFormText( $kfr, 'Province', 'province', 3 )."</TR>"

            ."</TABLE>"
            ."<BR>"
            ."<B>".$this->S("Landscape")."</B>"
            ."<TABLE class='pc_form_table' cellspacing=0 cellpadding=0>"
            ."<TR>";

        global $raLandscapeTypes;

        $i = 0;
        foreach( $raLandscapeTypes as $t => $v ) {
            echo "<TD><INPUT type='checkbox' name='pcde_landtype_$t' value='1'";
            if( strstr( $kfr->value('landscape'), ' '.$t.' ' ) !== false )  echo " checked";
            echo ">&nbsp;&nbsp;".$this->S('landtype_'.$t)."</TD>";
            if( ((++$i) % 5) == 0 )  echo "</TR><TR>";
        }
        echo "</TR></TABLE>";

//      echo "<BR>".KFRForm_Text( $kfr, $this->S('Landscape'), 'landscape' );


        echo "<BR><BR>".SEEDStd_StrNBSP("",10).$this->ButtonSubmit( $kSite ? 'button_update' : 'button_add' ).SEEDStd_StrNBSP("",10).$this->ButtonCancel();
        echo "</FORM></DIV>";

    }

    function drawForm_Visit()
    /************************
     */
    {
        $kfrV =& $this->GetKFRVisitFromParms();
        $kVisit = $kfrV->Key();

        $kfrS = $this->kfrelSites->GetRecordFromDBKey( $kfrV->value('fk_pollcan_sites') );

        if( !$kVisit ) {
            /* Adding a new visit.  Site should be specified in parms.
             */
//          $kSite = SEEDSafeGPC_GetInt( 'pcdeSite' );
//          if( !$kSite ) { return; }

            // suggest visit number to be the next one
            $kfrV->SetValue( "nVisit", intval($this->kfdb->KFDB_Query1(
                "SELECT MAX(nVisit) FROM pollcan_visits WHERE fk_pollcan_sites='".$kfrS->Key()."'"))+1 );
        }

        echo $this->drawBox_Site( $kfrS );

        echo "<DIV class='pc_box'>";
        echo "<H2>".$this->S( $kVisit ? 'Edit Visit' : 'Add a Visit')."</H2>";
        echo "<P>".$this->S('AddVisit_instructions')."</P>";

        if( !empty($this->sErrorMsg) ) {
            echo "<P style='color:red'>".$this->sErrorMsg."</P>";
        }

        echo "<FORM method='post' action='${_SERVER['PHP_SELF']}'>"
            .SEEDForm_Hidden( 'pcdeState', PCDE_STATE_VISIT )
            .SEEDForm_Hidden( 'pcdeSite', $kfrV->value('fk_pollcan_sites'))
            .SEEDForm_Hidden( 'pcdeVisit', $kVisit )
            ."<TABLE class='pc_form_table' cellspacing=0 cellpadding=0>"
            ."<TR>".$this->drawFormText( $kfrV, 'Visit #', 'nVisit', 5, 5 )."</TR>"
            ."<TR>".$this->drawFormText( $kfrV, 'Date', 'date_visit', 15 )
                   .$this->drawFormText( $kfrV, 'Start Time', 'time_start', 15 )
                   .$this->drawFormText( $kfrV, 'Time spent observing', 'time_duration', 15 )."</TR>"
            ."</TABLE>";

        echo "<P><B>".$this->S('Weather')."</B></P>"
            ."<TABLE class='pc_form_table' cellspacing=0 cellpadding=0>"
            ."<TR>"
            ."<TH valign='top'>".$this->S("wth_Sky")."</TD>"
            ."<TD valign='top'>".KFRForm_Select( $kfrV, 'weather_sky',
                                    array( '' => '-----',
                                           'sunny'   => $this->S('wth_sunny'),
                                           'cloudy'  => $this->S('wth_cloudy'),
                                           'overcast'=> $this->S('wth_overcast') ) )."</TD>"
            ."<TH valign='top'>".$this->S("wth_Shade")."</TD>"
            ."<TD valign='top'>".KFRForm_Select( $kfrV, 'weather_shade',
                                    array( '' => '-----',
                                           'shaded'    => $this->S('wth_shaded'),
                                           'not_shaded'=> $this->S('wth_not_shaded') ) )."</TD>"
            ."</TR><TR>"
            ."<TH valign='top'>".$this->S("wth_Wind")."</TD>"
            ."<TD valign='top'>".KFRForm_Select( $kfrV, 'weather_wind',
                                    array( '' => '-----',
                                           'windy_steady' => $this->S('wth_windy_steady'),
                                           'windy_gusts'  => $this->S('wth_windy_gusts'),
                                           'light_steady' => $this->S('wth_light_steady'),
                                           'light_gusts'  => $this->S('wth_light_gusts'),
                                           'calm'         => $this->S('wth_calm') ) )."</TD>"
            ."<TH valign='top'>".$this->S("wth_Temperature")."</TD>"
            ."<TD valign='top'>".KFRForm_Select( $kfrV, 'weather_temp',
                                    array( '' => '-----',
                                           'cold'     => $this->S('wth_cold'),
                                           'cool'     => $this->S('wth_cool'),
                                           'seasonal' => $this->S('wth_seasonal'),
                                           'warm'     => $this->S('wth_warm'),
                                           'hot'      => $this->S('wth_hot') ) )."</TD>"
            ."</TR></TABLE>";

        echo "<BR><BR>";

        echo "<TABLE class='pc_form_table' cellspacing=0 cellpadding=0>"
            ."<TR>"
            ."<TH>".$this->S('I_name_size')."</TH>"
            ."<TH>".$this->S('I_type')."</TH>"
            ."<TH>".$this->S('I_nObserved')."</TH>"
            ."<TH>".$this->S('I_seen')."</TH>"
//            ."<TH>".$this->S('I_flowers')."</TH>"
            ."</TR>";
        $i = 1;
        if( ($kfrI = $this->kfrelInsects->CreateRecordCursor( "fk_pollcan_visits='$kVisit'", array('sSortCol'=> '_key') )) ) {
            while( $kfrI->CursorFetch() ) {
                echo $this->drawInsectRow( $kfrI, $i );
                ++$i;
            }
        }
        $kfrI = $this->kfrelInsects->CreateRecord();
        echo $this->drawInsectRow( $kfrI, $i++ );
        echo $this->drawInsectRow( $kfrI, $i++ );
        echo $this->drawInsectRow( $kfrI, $i++ );
        echo $this->drawInsectRow( $kfrI, $i++ );
        echo "</TABLE>";


        echo "<BR><BR>".SEEDStd_StrNBSP("",10).$this->ButtonSubmit( $kVisit ? 'button_update' : 'button_add' ).SEEDStd_StrNBSP("",10).$this->ButtonCancel();
        echo "</FORM></DIV>";
    }

    function drawInsectRow( $kfrI, $i )
    /**********************************
     */
    {
        $s = "<TR>";

        $s .= SEEDForm_Hidden( "i_key$i", $kfrI->Key() )
             ."<TD valign='top'>"
             .SEEDForm_Text( "i_name$i", $kfrI->value('name'), "", 20 )
             ."<BR>".$this->S('I_size_mm')
             .SEEDForm_Text( "i_size$i", $kfrI->value('size_mm'), "", 5 )
             ."</TD><TD valign='top'>"
             .SEEDForm_Select( "i_type$i", array( ""          => "-----",
                                                  "BEE"       => $this->S('I_type_bee'),
                                                  "FLY"       => $this->S('I_type_fly'),
                                                  "WASP"      => $this->S('I_type_wasp'),
                                                  "BEETLE"    => $this->S('I_type_beetle'),
                                                  "BUTTERFLY" => $this->S('I_type_butterfly'),
                                                  "MOTH"      => $this->S('I_type_moth'),
                                                  "OTHER"     => $this->S('I_type_other'),
                                                  "UNKNOWN"   => $this->S('I_type_unknown') ),
                               $kfrI->value('eType') )
             ."</TD><TD valign='top'>"
             .SEEDForm_Text( "i_nObserved$i", $kfrI->value('nObserved'), "", 5 )
             ."</TD><TD valign='top'>"
             .SEEDForm_Select( "i_seen$i", array( ""                => "-----",
                                                  "NEVER"           => $this->S('I_seen_never'),
                                                  "NOT_THIS_SUMMER" => $this->S('I_seen_not_this_summer'),
                                                  "THIS_SUMMER"     => $this->S('I_seen_this_summer'),
                                                  "PAST_MONTH"      => $this->S('I_seen_past_moth') ),
                               $kfrI->value('last_seen') )
           //  ."</TD><TD valign='top'>"
           //  ."Flowers visited"

           //  ."</TD>"
             ."</TR>";

        return( $s );
    }

    function drawPage_Home()
    /***********************
        Mode "":
            Show list of sites with # visits per site - drill down to Mode S.
            Show list of insects - drill down to Mode I.
            Show list of flowers - drill down to Mode F.
        Mode S:
            Show list of insects related to site S - drill down to Mode I.
            Show list of flowers related to site S - drill down to Mode F.
            Show list of visits related to site S - drill down to Mode V.
        Mode I:
            Show list of flowers related to insect I - drill down to Mode F.
            Show list of sites/visits related to insect I - drill down to Mode S/V.
        Mode F:
            Show list of insects related to flower F - drill down to Mode I.
            Show list of sites/visits related to flower F - drill down to Mode V.
        Mode V:
            Show form for visit V. "Edit" links to VISIT.

        Link to SITE
        Edit site, using SITE
        Link to VISIT, must choose a site from a list
        Link to PASSWORD
        Log out

        [show Pollinator Points]
        [upload your photos]
        [contacts]
        [affiliate with a group]
     */
    {
        $pMode = SEEDSafeGPC_GetStrPlain('m');
        $pKey  = SEEDSafeGPC_GetInt('k');

        echo "<BODY>";

        /* HEADER
         */
        echo "<TABLE border=0 cellspacing=0 cellpadding=0 width='100%'><TR>"
            ."<TD valign='top' style='padding-right:20px;'><IMAGE src='".$this->S('img_home_banner')."'></TD>"
            ."<TD valign='top'><DIV class='pc_box' style='float:right'>"
            ."<P style='font-size:12px;'>".$this->S('Observer').": ".$this->GetUserEmail()."</P>"
            ."<BLOCKQUOTE><A HREF='?pcdeState=LOGIN'>".$this->S('Logout')."</A>"
            ."</BLOCKQUOTE>"
            ."</DIV></TD>"
            ."</TR></TABLE>";


        echo "<TABLE id='pc_home_table' width='100%'><TR>";

        /*** SITES
         */
        echo "<TD id='pc_homecol_left' width='20%' valign='top'>";
        $s = "<FORM method='POST' action='${_SERVER['PHP_SELF']}'><INPUT type='hidden' name='pcdeState' value='".PCDE_STATE_SITE."'><P align='right'><INPUT type='submit' value='".$this->S('Add Site')."'></P></FORM>";
        if( ($kfrS = $this->kfrelSites->CreateRecordCursor( "fk_pollcan_users='".$this->GetUserKey()."'", array("sSortCol"=>"nSite") )) ) {
            while( $kfrS->CursorFetch() ) {
                $s .= "<P><A HREF='?pcdeState=".PCDE_STATE_HOME."&m=S&k=".$kfrS->Key()."'>"
                //$s .= "<P><A HREF='?pcdeState=".PCDE_STATE_SITE."&pcdeSite=".$kfrS->Key()."'>"
                     .$kfrS->value('nSite').": ".$kfrS->value('siteName')."</A></P>";

                $s .= "<BLOCKQUOTE>";
                if( ($kfrV = $this->kfrelVisits->CreateRecordCursor( "fk_pollcan_sites='".$kfrS->Key()."'", array("sSortCol"=>"nVisit") )) ) {
                    while( $kfrV->CursorFetch() ) {
                        //$s .= "<P><A HREF='?pcdeState=".PCDE_STATE_HOME."&m=V&k=".$kfrV->Key()."'>"
                        $s .= "<P><A HREF='?pcdeState=".PCDE_STATE_VISIT."&pcdeVisit=".$kfrV->Key()."'>"
                             .$this->S('Visit #')." ".$kfrV->value('nVisit').": ".$kfrV->value('date_visit')."</A></P>";
                    }
                }
                $s .= "</BLOCKQUOTE>";
            }
        }
        echo $this->DrawHomeBox( $this->S('My Sites'), $s );

        $kfrS = $kfrV = NULL;
        echo "</TD>";


        /*** MIDDLE
         */
        echo "<TD id='pc_homecol_middle' valign='top'>";
        $s = "";
        switch( $this->sess->VarGet( 'pcdeState' ) ) {
            case PCDE_STATE_SITE:
                $this->drawForm_Site();
                break;

            case PCDE_STATE_VISIT:
                $this->drawForm_Visit();
                break;

            default:
                if( $pMode == 'S' && $pKey ) {
                    echo $this->drawPage_HomeSite( $pKey );
                }
                if( $pMode == 'I' && $pKey ) {
                    echo $this->drawPage_HomeInsect( $pKey );
                }
                echo "&nbsp;";
                break;
        }
        echo $s;
        echo "</TD>";


        /*** RIGHT COLUMN
         */
        echo "<TD id='pc_homecol_right' width='20%' valign='top'>";

        /*** INSECTS
         */
        $ra = array();
        $s = "";
        if( ($kfr = $this->kfrelInsects->CreateRecordCursor( "Site.fk_pollcan_users='".$this->GetUserKey()."'", array('sSortCol'=>'name') )) ) {
            while( $kfr->CursorFetch() ) {
                $ra[$kfr->value('name')] = $kfr->Key(); // make unique name
            }
        }
        foreach( $ra as $n => $k ) {
            $s .= "<P><A HREF='?pcdeState=".PCDE_STATE_HOME."&m=I&k=$k'>$n</A></P>";
        }
        echo $this->DrawHomeBox( $this->S('My Insects'), $s );
        echo "<BR><BR><BR>";

        /*** FLOWERS
         */
        $s = "<P>My House</P><P>Road near the highway</P><P>Pepper Park</P><P>Campsite</P>";
        if( ($kfr = $this->kfrelFlowers->CreateRecordCursor( "fk_pollcan_users='".$this->GetUserKey()."'", array() )) ) {
            while( $kfr->CursorFetch() ) {
                $s .= "<P><A HREF='?pcdeState=".PCDE_STATE_HOME."&m=F&k=".$kfr->Key()."'>".$kfr->value('name')."</A></P>";
            }
        }
//        echo $this->DrawHomeBox( $this->S('My Flowers'), $s );

        echo "</TD></TR></TABLE>";

        echo "</BODY>";
    }

    function drawPage_HomeSite( $pKey )
    /**********************************
     */
    {
        $s = "";

        $kfr = $this->kfrelSites->GetRecordFromDBKey($pKey);

        if( $kfr && $kfr->Key() ) {
            $s .= $this->drawBox_Site( $kfr );
            $s .= "<DIV class='pc_box'>"
                 ."<DIV style='float:right'>"
                 ."<FORM method='POST' action='${_SERVER['PHP_SELF']}'>".SEEDForm_Hidden('pcdeState', PCDE_STATE_VISIT).SEEDForm_Hidden('pcdeSite', $kfr->Key())
                 ."<INPUT type='submit' value='".$this->S('Add a Visit')."'>"
                 ."</FORM>"
                 ."</DIV>"
                 ."<H3>".$this->S('Visits')."</H3>";
            if( ($kfrV = $this->kfrelVisits->CreateRecordCursor( "fk_pollcan_sites='$pKey'", array("sSortCol"=>"nVisit") )) ) {
                while( $kfrV->CursorFetch() ) {
                    //$s .= "<P><A HREF='?pcdeState=".PCDE_STATE_HOME."&m=V&k=".$kfrV->Key()."'>"
                    $s .= "<P><A HREF='?pcdeState=".PCDE_STATE_VISIT."&pcdeVisit=".$kfrV->Key()."'>"
                         .$kfrV->value('nVisit').": ".$kfrV->value('date_visit')."</A></P>";
                }
            }
            $s .= "</DIV>";
        }
        return( $s );
    }

    function drawBox_Site( $kfr )
    /****************************
     */
    {
        $s .= "<DIV class='pc_box'>"
             ."<DIV style='float:right'>"
             ."<FORM method='POST' action='${_SERVER['PHP_SELF']}'>".SEEDForm_Hidden('pcdeState', PCDE_STATE_SITE).SEEDForm_Hidden('pcdeSite', $kfr->Key())
             ."<INPUT type='submit' value='".$this->S('Edit Site')."'>"
             ."</FORM>"
             ."</DIV>"
             ."<H3>".$this->S('Site #')." ".$kfr->value('nSite')."</H3>"
             ."<H3>".$kfr->value('siteName')."</H3>"
             ."<P>".$this->S('Address').": ".$kfr->value('address')." ".$kfr->value('city')." ".$kfr->value('province')."</P>"
             ."<P>".$this->S('Latitude').": ".$kfr->value('latitude')."<BR>"
             ."<P>".$this->S('Longitude').": ".$kfr->value('longitude')."</P>"
             ."<P>".$this->S('Distance from road').": ".$kfr->value('distance_from_road')."</P>"
             ."<P>".$this->S('Landscape').": ";
        global $raLandscapeTypes;
        $raTmp = array();
        foreach( $raLandscapeTypes as $t => $v ) {
            if( strstr( $kfr->value('landscape'), ' '.$t.' ' ) !== false )  $raTmp[] = $this->S('landtype_'.$t);
        }
        $s .= implode( ", ", $raTmp );
        $s .= "</DIV>";
        return( $s );
    }

    function drawPage_HomeInsect( $pKey )
    /************************************
     */
    {
        $s = "";

        $kfr = $this->kfrelInsects->GetRecordFromDBKey($pKey);

        $s .= "<H3>".$this->S("Insect").": ".$kfr->value('name')."</H3>";

        if( ($kfrI = $this->kfrelInsects->CreateRecordCursor( "name='".addslashes($kfr->value('name'))."' AND "
                                                             ."Site.fk_pollcan_users='".$this->GetUserKey()."'",
                                                              array('sSortCol'=> 'Site.nSite,Visit.nVisit') )) ) {
            $nSite = NULL;
            while( $kfrI->CursorFetch() ) {
                if( $kfrI->value('Site_nSite') !== $nSite ) {
                    if( $nSite !== NULL ) $s .= "</BLOCKQUOTE>";

                    $nSite = $kfrI->value('Site_nSite');
                    $s .= "<P><A HREF='?pcdeState=".PCDE_STATE_HOME."&m=S&k=".$kfrI->value('Site__key')."'>"
                       .$this->S('Site #')." $nSite: ".$kfrI->value('Site_siteName')."</A></P><BLOCKQUOTE>";
                }
                $s .= "<P><A HREF='?pcdeState=".PCDE_STATE_VISIT."&pcdeVisit=".$kfrI->value('Visit__key')."'>"
                   .$this->S('Visit #')." ".$kfrI->value('Visit_nVisit')." (".$kfrI->value('nObserved').")</A></P>";
            }
            if( $nSite !== NULL ) $s .= "</BLOCKQUOTE>";
        }



        return( $s );
    }

    function ButtonSubmit( $idLocal = 'button_update' )
    /**************************************************
     */
    {
        return( "<INPUT type='submit' name='pcdeSubmit' value='".$this->S($idLocal)."'>" );
    }

    function ButtonCancel()
    /**********************
     */
    {
        return( "<INPUT type='submit' name='pcdeSubmit' value='".$this->S('button_cancel')."'>" );
    }

    function GetKFRSiteFromParms()
    /*****************************
        In SITE, this fetches the current values of the current site, from a combination of db and GPC.

        It could be an empty record, if a new Add Site has just been started with no kSite.
        It could be a db record, if Edit Site has been started.
        It could be a partially filled Site with no _key, if Add Site was submitted.
        It could be a db record, modified by GPC, if Edit Site was submitted.
     */
    {
        $kSite = intval($_POST['pcdeSite']);

        $kfr = $kSite ? $this->kfrelSites->GetRecordFromDBKey($kSite) : $this->kfrelSites->CreateRecord();
        if( $kSite && $kfr->value( 'fk_pollcan_users' ) != $this->GetUserKey() ) {
            die( "Invalid site user access" );
        }

        $kfr->SetValue( "fk_pollcan_users", $this->GetUserKey() );

        if( !empty($_POST['pcdeSubmit']) ) {
            /* Assume that the form POSTed all values (except unchecked checkboxes)
             */
            foreach( array('nSite', 'siteName', 'distance_from_road', 'latitude', 'longitude',
                           'address', 'city', 'province' ) as $t ) {
                $s = SEEDSafeGPC_GetStrPlain($t);
                $kfr->SetValue( $t, $s );
            }
            $kfr->SetValue( "fk_pollcan_users", $this->GetUserKey() );

            global $raLandscapeTypes;
            $sLand = " ";
            foreach( $raLandscapeTypes as $t => $v ) {
                if( @$_POST['pcde_landtype_'.$t] )  $sLand .= $t." ";
            }
            $kfr->SetValue( "landscape", $sLand );
        }

        return( $kfr );
    }

    function GetKFRVisitFromParms()
    /******************************
        In VISIT, this fetches the current values of the current site, from a combination of db and GPC.

        It could be an empty record, if a new Add Visit has just been started with no kVisit
        It could be a db record, if Edit Visit has been started.
        It could be a partially filled Visit with no _key, if Add Visit was submitted.
        It could be a db record, modified by GPC, if Edit Visit was submitted.
     */
    {
        $kSite = intval($_REQUEST['pcdeSite']);
        $kVisit = intval($_REQUEST['pcdeVisit']);

        $kfr = $kVisit ? $this->kfrelVisits->GetRecordFromDBKey($kVisit) : $this->kfrelVisits->CreateRecord();

        if( $kVisit ) {
            $kSite = $kfr->value( 'fk_pollcan_sites' );
        } else {
            /* New Visit.  Prepopulate the join fields for convenience.
             */
            $kfr->SetValue( "fk_pollcan_sites", $kSite );
            $ra = $this->kfrelVisits->kfdb->KFDB_QueryRA( "SELECT fk_pollcan_users,siteName FROM pollcan_sites WHERE _key='$kSite'" );
            $kfr->SetValue( "Site_fk_pollcan_users", @$ra['fk_pollcan_users'] );
            $kfr->SetValue( "Site_siteName", @$ra['siteName'] );
        }
        if( !$kSite ) { die( "Invalid visit site" ); }

        if( $kfr->value( 'Site_fk_pollcan_users' ) != $this->GetUserKey() ) {
            die( "Invalid visit user access" );
        }


        if( !empty($_POST['pcdeSubmit']) ) {
            /* Assume that the form POSTed all values (except unchecked checkboxes)
             */
            foreach( array('nVisit', 'date_visit', 'time_start', 'time_duration',
                           'weather_sky', 'weather_shade', 'weather_wind', 'weather_temp' ) as $t ) {
                $s = SEEDSafeGPC_GetStrPlain($t);
                $kfr->SetValue( $t, $s );
            }
        }

        return( $kfr );
    }
}




$pcde = new PollCan_DataEntry_App( $kfdb, $sess, $lang );
$pcde->App();

?>
