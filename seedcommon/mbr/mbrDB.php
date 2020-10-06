<?php

/*
 * mbrDB
 *
 * Copyright 2015-2016 Seeds of Diversity Canada
 *
 * Database layer for membership information, login, bulletin subscription
 */

/*

Although this is in seedcommon, it makes general use of mbr_contacts. Some of this can be used
with a kfdb1, but it will be complicated. Note that anybody can initialize a kfrel, even if it will cause
a db error when they use it.

A better solution is to use MbrAccount for kfdb1, and this code only for kfdb2

*/

class Mbr_DB
{
    public $kfrelContacts = NULL;

    public $kfrelCxU1 = NULL;           // contacts x seeds_1.users
    public $kfrelC_U1 = NULL;           // contacts left join seeds_1.users
    public $kfrelU1_C = NULL;           // seeds_1.users left join contacts

    public $yCurrent;

    private $kfdb;

    private $kfrelProfile = NULL;
    private $kfrelProfileExtra  = NULL;

    private $oSessUGP = NULL;

    function __construct( KeyFrameDB $kfdb, $uid, $yCurrent )
    {
        $this->kfdb = $kfdb;
        $this->yCurrent = intval($yCurrent);

        $this->init( $kfdb, $uid );

        global $config_KFDB;
        $this->oSessUGP = new SEEDSessionAuthDB( $kfdb, $uid, $config_KFDB['seeds1']['kfdbDatabase'] );  // 'seeds' );
    }

    function GetMembersWithoutLogins()
    /*********************************
        Returns array( k => array( name, email ), ... ) for current members with emails that don't have logins
     */
    {
        $raOut = array();

        if( ($kfrc = $this->kfrelC_U1->CreateRecordCursor( "U._key is null AND year(C.expires)>='{$this->yCurrent}' AND C.email<>''" )) ) {
            while( $kfrc->CursorFetch() ) {
                $raOut[$kfrc->Key()] = array( 'email' => $kfrc->Value('email'),
                                              'name' => trim($kfrc->Expand("[[firstname]] [[lastname]] [[company]]")) );
            }
        }
        return( $raOut );
    }

    private function getMembersWithLogins( $sCond = "" )
    /***************************************************
        Returns array( k => array( name, email ), ... ) for current members with emails that have logins
        (the logins might not be active, or might not have membership perms)
     */
    {
        $raOut = array();

        if( $sCond )  $sCond = $sCond." AND ";

        if( ($kfrc = $this->kfrelCxU1->CreateRecordCursor( $sCond."year(C.expires)>='{$this->yCurrent}' AND C.email<>''" )) ) {
            while( $kfrc->CursorFetch() ) {
                $raOut[$kfrc->Key()] = array( 'email' => $kfrc->Value('email'),
                                              'name' => trim($kfrc->Expand("[[firstname]] [[lastname]] [[company]]")),
                                              'eStatus' => $kfrc->Value('U_eStatus') );
            }
        }
        return( $raOut );
    }

    function GetMembersWithLoginsButNoPerms()
    /****************************************
        Returns array( k => array( name, email), ... ) for current members with emails and ACTIVE logins but not membership perms
     */
    {
        $raAccounts = $this->oSessUGP->GetUsersFromGroup( 2 );

        $raMbr = $this->getMembersWithLogins( "U.eStatus='ACTIVE'" );
        $raOut = array();
        foreach( $raMbr as $k => $ra ) {
            if( !isset($raAccounts[$k]) ) {
                $raOut[$k] = $ra;
            }
        }
        return( $raOut );
    }

    function GetMembersWithLoginsButNotActive()
    /******************************************
        Returns array( k => array( name, email), ... ) for current members with emails and logins but not ACTIVE
     */
    {
        $raOut = $this->getMembersWithLogins( "U.eStatus<>'ACTIVE'" );
        return( $raOut );
    }

    function GetAccountsWithPermsButNotCurrentMember()
    {
        $raAccounts = $this->oSessUGP->GetUsersFromGroup( 2 );

        $raOut = array();

        foreach( $raAccounts as $k => $ra ) {
            $kfr = $this->kfrelContacts->GetRecordFromDBKey( $k );
            if( !$kfr || !$this->IsCurrentFromExpires($kfr->value('expires') ) ) {
                $raOut[$k] = array( 'email' => $ra['email'],
                                    'name' => @$ra['realname'] );  // actually, raAccounts doesn't provide this
            }
        }
        return( $raOut );
    }

    function GetAccountsWithNoMember( $bExcludeNonMemberRange = false )
    {
        $raOut = array();

        if( ($kfrc = $this->kfrelU1_C->CreateRecordCursor( "C._key is null" )) ) {
            while( $kfrc->CursorFetch() ) {
                // member numbers are between 1 and 999999; we often add non-member accounts outside of this range
                if( $bExcludeNonMemberRange && $kfrc->Key() < 0)         continue;
                if( $bExcludeNonMemberRange && $kfrc->Key() >= 1000000 ) continue;

                $raOut[$kfrc->Key()] = array( 'email' => $kfrc->Value('email'),
                                              'name' => $kfrc->Value('realname') );
            }
        }
        return( $raOut );
    }

    function GetAccountsWithDifferentEmail()
    {
        $raOut = array();

        // constrain to current members because Judy only updates emails for current members
        if( ($kfrc = $this->kfrelCxU1->CreateRecordCursor( "year(C.expires)>='{$this->yCurrent}' AND C.email<>U.email AND U.eStatus='ACTIVE'" )) ) {  //  AND C.email<>'' -- no, let's catch the case where they lose email
            while( $kfrc->CursorFetch() ) {
                $raOut[$kfrc->Key()] = array( 'email' => $kfrc->Value('email'),
                                              'U_email' => $kfrc->Value('U_email'),
                                              'name' => trim($kfrc->Expand("[[firstname]] [[lastname]] [[company]]")) );
            }
        }
        return( $raOut );
    }

    function GetAccountsWithOldMSD()
    {
        $raOut = array();

        // The query for this is Contacts x User1 x UserMetadata1
        // with C.expires >= yCurrent, C.email <> '', U.eStatus='ACTIVE', UM.dSentMSD not exist OR UM.dSentMSD < yCurrent

// KFRelation
        $sql = "SELECT C._key as kMbr,C.email as email,C.firstname as firstname,C.lastname as lastname,C.company as company "
              ."FROM seeds_2.mbr_contacts C "
              ."JOIN seeds_1.SEEDSession_Users U ON (C._key=U._key) "
              ."LEFT JOIN seeds_1.SEEDSession_UsersMetadata UM ON (U._key=UM.uid AND UM.k='dSentMSD' AND UM._status='0') "
              ."WHERE C._status='0' AND U._status='0' " // don't put UM._status='0' here or no null rows will be returned
              ."AND C.expires >= '{$this->yCurrent}' "
              ."AND C.email <> '' "
              ."AND U.eStatus='ACTIVE' "
              ."AND (UM.v is null OR year(UM.v)<'{$this->yCurrent}')";

        $raRows = $this->kfdb->QueryRowsRA( $sql );
        foreach( $raRows as $ra ) {
            $raOut[$ra['kMbr']] = array( 'email' => $ra['email'],
                                         'name' => trim(SEEDStd_ArrayExpand( $ra, "[[firstname]] [[lastname]] [[company]]")),
                                         'eStatus' => 'ACTIVE' );
        }

        return( $raOut );
    }


    function GetMemberInfoAndValidate( $kMbr, $sTests = "" )
    /*******************************************************
        Return all fields from mbr_contacts, plus validation of the given tests
     */
    {
        $bOk = true;
        $sErr = "";
        if( !($kfrMbr = $this->kfrelContacts->GetRecordFromDBKey( $kMbr )) ) {
            $bOk = false;
            $sErr = "Member $kMbr is not in the contact database";
            goto done;
        }

        if( $sTests ) {
            foreach( explode( " ", $sTests ) as $test ) {
                switch( $test ) {
                    case "AccountExists":
                        if( !$this->kfdb->Query1( "SELECT _key FROM seeds_1.SEEDSession_Users WHERE _key='$kMbr'" ) ) {
                            $bOk = false;
                            $sErr .= "Contact $kMbr does not have a login account. ";
                        }
                        break;

                    case "MembershipCurrent":
                        if( !$this->IsCurrentFromExpires($kfrMbr->value('expires')) ) {
                            $bOk = false;
                            $sErr .= "Contact # $kMbr is not a current member";
                        }
                        break;

                    case "EmailNotBlank":
                        if( $kfrMbr->IsEmpty('email') ) {
                            $bOk = false;
                            $sErr .= "Contact # $kMbr does not have an email address in the contact database";
                        }
                        break;

                    default:
                        die( "Unknown validation code $test" );
                }
            }
        }

        done:
        return( array($kfrMbr,$bOk,$sErr) );
    }

    private function IsCurrentFromExpires( $sExpires )
    {
        $yExpires = intval(substr($sExpires, 0, 4));
        return( $yExpires >= $this->yCurrent );
    }

    function CreateLoginFromContact( $kMbr )
    /***************************************
        For an existing contact, create an active login.

        If they're a current member, give them membership permissions.
     */
    {
        $bOk = true;
        $sErr = "";

        // The contact must exist and have an email address
        list($kfrMbr,$bOk,$sErr) = $this->GetMemberInfoAndValidate( $kMbr, "EmailNotBlank" );  // "MembershipCurrent" -- don't require this anymore
        if( !$bOk ) {
            goto done;
        }

        $sdbEmail = $kfrMbr->ValueDB('email');

// put this in GetMemberInfoAndValidate - or is it only ever needed here
        // The login must not exist
        if( $this->kfdb->Query1( "SELECT _key FROM seeds_1.SEEDSession_Users WHERE _key='$kMbr'" ) ) {
            $bOk = false;
            $sErr = "Member $kMbr already has a login account. If the report here says otherwise, it might be inactivated?<br/>";
            goto done;
        }

        // Another login may not have the same email address (CreateUser checks this, but doesn't report an error - it should)
        if( ($kDup = $this->kfdb->Query1( "SELECT _key FROM seeds_1.SEEDSession_Users WHERE email='$sdbEmail' and _status='0'" )) ) {
            $bOk = false;
            $sErr = "Member $kMbr already has a login account ($kDup). If the report here says otherwise, it might be inactivated?<br/>";
            goto done;
        }


        // use mysql to generate an initial password
        $p = $this->kfdb->Query1( "SELECT left(md5('$sdbEmail'),6)" );
        // membership permissions if current member
        $gid1 = $this->IsCurrentFromExpires($kfrMbr->value('expires')) ? 2 : 0;
        $kMbr = $this->oSessUGP->CreateUser( $kfrMbr->value('email'), $p,
                                             array( 'k'=>$kMbr,
                                                    'realname'=> trim($kfrMbr->Expand("[[firstname]] [[lastname]] [[company]]")),
                                                    'eStatus'=>'ACTIVE',
                                                    'gid1'=> $gid1,
                                                    'lang'=> $kfrMbr->value('lang')
                                            ) );
        $bOk = ($kMbr != 0);

        //$bOk = $this->kfdb1->Execute(
        //        "INSERT INTO seeds_1.SEEDSession_Users (_key,_created,_created_by,_status,"
        //                                            ."email,password,realname,gid1,eStatus,dSentmsd)"
        //       ." VALUES ".SEEDStd_ArrayExpand($raMbr, "('[[_key]]',now(),0,0,'[[email]]',left(md5('[[email]]'),6),"
        //                                              ."trim('[[firstname]] [[lastname]] [[company]]'),2,'ACTIVE',0)") );
        if( !$bOk ) {
            $sErr = "Database error adding login account for member $kMbr : ".$this->kfdb->GetErrMsg();
        }

        done:
        return( array( $bOk, $sErr ) );
    }

    function ActivateLogin( $kMbr )
    /******************************
     */
    {
        $sErr = "";

        if( !($bOk = $this->oSessUGP->ActivateUser( $kMbr )) ) {
            $sErr = "Database error activating login $kMbr.";
        }

        return( array( $bOk, $sErr ) );
    }

    function DeactivateLogin( $kMbr )
    /********************************
        Deactivate an existing login that is ACTIVE
     */
    {
        list($kfrMbr,$bOk,$sErr) = $this->GetMemberInfoAndValidate( $kMbr, "AccountExists EmailNotBlank" );
        if( !$bOk ) {
            goto done;
        }

        $raAccount = $this->kfdb->QueryRA( "SELECT * FROM seeds_1.SEEDSession_Users WHERE _key='$kMbr'" );

        // if the account has not been deleted or hidden, and it is ACTIVE, make it INACTIVE
        if( $raAccount['_status'] == 0 && $raAccount['eStatus'] == "ACTIVE" ) {
            if( !$this->kfdb->Execute( "UPDATE seeds_1.SEEDSession_Users SET eStatus='INACTIVE' WHERE _key='$kMbr'" ) ) {
                $bOk = false;
                $sErr = "Database error activating login account for member $kMbr : ".$this->kfdb->GetErrMsg();
                goto done;
            }
        }

        done:
        return( array($bOk, $sErr) );
    }

    function AddToMembersGroup( $kMbr )
    /**********************************
        If the given contact is a current member, make sure they are in the members group
     */
    {
        $bOk = false;
        $sErr = "";

        if( ($kfrMbr = $this->kfrelContacts->GetRecordFromDBKey( $kMbr )) &&
            $this->IsCurrentFromExpires( $kfrMbr->Value('expires') ) )
        {
            $bOk = $this->oSessUGP->AddUserToGroup( $kMbr, $this->MembersGroupKey() );
            if( !$bOk ) $sErr = "Error adding member $kMbr to members group.";
        } else {
            $sErr = "Contact $kMbr is not a current member";
        }
        return( array($bOk,$sErr) );
    }

    protected function MembersGroupKey()
    {
// look up the members group key
        return( 2 );
    }

    private function init( $kfdb, $uid ) {
        $kdefC =
            array( "Tables" => array( array( "Table" => 'seeds_2.mbr_contacts',
                                             "Alias" => "C",
                                             "Type" => "Base",
                                             "Fields" => "Auto" ) ) );
        $kdefCxU1 =
            array( "Tables" => array( array( "Table" => 'seeds_2.mbr_contacts',
                                             "Alias" => "C",
                                             "Type" => "Base",
                                             "Fields" => "Auto" ),
                                      array( "Table" => 'seeds_1.SEEDSession_Users',
                                             "Alias" => "U",
                                             "Type" => "Other",
                                             "Fields" => "Auto" ) ),
                   "Condition" => "C._key=U._key" );
        $kdefC_U1 =
            array( "Tables" => array( array( "Table" => 'seeds_2.mbr_contacts',
                                             "Alias" => "C",
                                             "Type" => "Base",
                                             "Fields" => "Auto" ),
                                      array( "Table" => 'seeds_1.SEEDSession_Users',
                                             "Alias" => "U",
                                             "Type"  => "LEFT JOIN",
                                             "LeftJoinOn" => "C._key=U._key",
                                             "Fields" => "Auto" ) ) );
        $kdefU1_C =
            array( "Tables" => array( array( "Table" => 'seeds_1.SEEDSession_Users',
                                             "Alias" => "U",
                                             "Type" => "Base",
                                             "Fields" => "Auto" ),
                                      array( "Table" => 'seeds_2.mbr_contacts',
                                             "Alias" => "C",
                                             "Type"  => "LEFT JOIN",
                                             "LeftJoinOn" => "C._key=U._key",
                                             "Fields" => "Auto" ) ) );
        $kdefP =
            array( "Tables" => array( array( "Table" => 'seeds_1.mbr_profile',
                                             "Alias" => "P",
                                             "Type" => "Base",
                                             "Fields" => "Auto" ) ) );
        $kdefPX =
            array( "Tables" => array( array( "Table" => 'seeds_1.mbr_profile_extra',
                                             "Alias" => "PX",
                                             "Type" => "Base",
                                             "Fields" => "Auto" ) ) );

        $raParms = array( 'logfile' => SITE_LOG_ROOT."mbr.log" );
        $this->kfrelContacts = new KeyFrameRelation( $kfdb, $kdefC, $uid, $raParms );
        $this->kfrelCxU1     = new KeyFrameRelation( $kfdb, $kdefCxU1, $uid, $raParms );
        $this->kfrelC_U1     = new KeyFrameRelation( $kfdb, $kdefC_U1, $uid, $raParms );
        $this->kfrelU1_C     = new KeyFrameRelation( $kfdb, $kdefU1_C, $uid, $raParms );

// implement a standard member profile using SEEDSession_UsersMetaData
//        $this->kfrelProfile  = new KeyFrameRelation( $kfdb, $kdefP, $uid, $raParms );
//        $this->kfrelProfileExtra = new KeyFrameRelation( $kfdb, $kdefPX, $uid, $raParms );
    }
}

/* mbr_profile : _key is the uid, same as SEEDSession_Users._key and mbr_contacts._key
 */
define("MBR_DB_TABLE_MBR_PROFILE",
"
CREATE TABLE IF NOT EXISTS mbr_profile (
        _key        INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT,
        _created    DATETIME,
        _created_by INTEGER,
        _updated    DATETIME,
        _updated_by INTEGER,
        _status     INTEGER DEFAULT 0,

    lang        ENUM('E','F') DEFAULT 'E',
    bEbull      INTEGER NOT NULL DEFAULT 0,
    eEbull_lang ENUM('E','F','B') DEFAULT 'E',
    dMSDNotice  DATE NOT NULL DEFAULT ''
);
"
);

define("MBR_DB_TABLE_MBR_PROFILE_EXTRA",
"
CREATE TABLE IF NOT EXISTS mbr_profile_extra (
        _key        INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT,
        _created    DATETIME,
        _created_by INTEGER,
        _updated    DATETIME,
        _updated_by INTEGER,
        _status     INTEGER DEFAULT 0,

    fk_mbr_profile INTEGER NOT NULL,              -- this is the user/mbr_contacts key
    k              VARCHAR(200) NOT NULL DEFAULT '',
    v              TEXT NOT NULL,

    INDEX (fk_mbr_profile)
);
"
);

?>
