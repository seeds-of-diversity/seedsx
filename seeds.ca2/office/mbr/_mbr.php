<?php

include_once( SEEDCOMMON."mbr/mbrCommon.php" );
/*
_key is mbr_id.  It is crucial that members don't change mbr numbers, since this _key connects them to our universe.
*/


class MbrContacts
{
    var $kfdb;
    var $kfrel;
    var $kMbr = 0;
    var $kfr = NULL;

    function __construct( KeyFrameDB $kfdb, $uid = 0 )
    {
        $this->kfdb = $kfdb;
        $this->kfrel = self::KfrelBase( $kfdb, $uid );
    }

    function SetKMbr( $kMbr )
    {
        if( $kMbr && $kMbr == $this->kMbr ) goto done;

        if( $kMbr && ($this->kfr = $this->kfrel->GetRecordFromDBKey( $kMbr )) ) {
            $this->kMbr = $kMbr;
        } else {
            $this->kMbr = 0;
            $this->kfr = NULL;
        }

        done:
        return( $this->kMbr );
    }

    function GetKFRByKey( $kMbr )
    {
        return( $this->SetKMbr($kMbr) ? $this->kfr : NULL );
    }

    function GetKFRByEmail( $sEmail, $bPersist = false )
    {
        $kfr = null;

        if( $sEmail && ($kfr = $this->kfrel->GetRecordFromDB( "email='".addslashes($sEmail)."'" )) ) {
            if( $bPersist ) {
                $this->kfr = $kfr;
                $this->kMbr = $kfr->Key();
            }
        }
        return( $kfr );
    }

    function DrawAddressBlock( $raParms = array() )
    /**********************************************
        Draw a nicely formatted address for the current member

        raParms:
            bEnt   : true=expand values using htmlspecialchars (default true)
            bPhone : draw the phone number too (default false)
            bEmail : draw the email address too (default false)
     */
    {
        if( !$this->kfr ) return( "" );

        $bEnt = SEEDStd_ArraySmartVal( $raParms, 'bEnt', array( true, false ) );

// style='white-space: nowrap' will prevent breaking in weird places like the middle of a postal code
        $s = $this->kfr->Expand( "<DIV class='mbr_address'>[[firstname]] [[lastname]]", $bEnt );
        if( !$this->kfr->IsEmpty('company') || !$this->kfr->IsEmpty('dept') ) $s .= $this->kfr->Expand( "<BR/>[[company]] [[dept]]", $bEnt );
        $s .= $this->kfr->Expand( "<BR/>[[address]]<BR/>[[city]] [[province]]  [[postcode]]", $bEnt );
        if( @$raParms['bPhone'] )  $s .= $this->kfr->ExpandIfNotEmpty( 'phone', "<BR/>[[]]", $bEnt );
        if( @$raParms['bEmail'] )  $s .= $this->kfr->ExpandIfNotEmpty( 'email', "<BR/>[[]]", $bEnt );
        $s .= "</DIV>";

        return( $s );
    }

    function MakeName( $raParms = array() )
    /**************************************
        Return a nice string for the current member's name
            firstname lastname
            firstname lastname & firstname2 lastname2
            company [dept]
            firstname lastname, company [dept]
            firstname lastname<BR/> company [dept]  -- if $raParms['sCompany_sep']=="<BR/>"
     */
    {
        if( !$this->kfr ) return( "" );

        $fn1 = $this->kfr->value('firstname');
        $ln1 = $this->kfr->value('lastname');
        $fn2 = $this->kfr->value('firstname2');
        $ln2 = $this->kfr->value('lastname2');
        $c   = $this->kfr->value('company');
        $d   = $this->kfr->value('dept');

        $company_sep = isset($raParms['sCompany_sep']) ? $raParms['sCompany_sep'] : ", ";

        $name1 = $fn1.(!empty($fn1) && !empty($ln1) ? " " :"").$ln1;
        $name2 = $fn2.(!empty($fn2) && !empty($ln2) ? " " :"").$ln2;
        $cmpny = $c.  (!empty($c)   && !empty($d)   ? " " :"").$d;

        $s = $name1 .(!empty($name1) && !empty($name2) ? " &amp; " : "").$name2;
        $s .= (!empty($s) && !empty($cmpny) ? $company_sep : "").$cmpny;

        return( $s );
    }


    function ExpandTemplate( $sTemplate )
    /************************************
        Substitute each tag in the template. Unknown tags are discarded.
     */
    {
        return( "" );
    }

    function TranslateTag( $raTag, $lang = 'EN' )
    /********************************************
        Compatible with SEEDWiki HandleLink: [[(namespace:)target(|parms)]]suffix
                                             return value; "" = null result; false = not handled

        All handled tags start with 'mbr_'
        If a tag is supposed to be handled, but kMbr is not set, a null result is returned

        [[mbr:ExpiryNotice    | frame {div_attrs} ]]    use GetVar:lang to draw a Membership Status notice for $this->kMbr (can be 0), frame: draw a border around it
        [[mbr:ExpiryNotice_EN | frame {div_attrs} ]]    force english
        [[mbr:ExpiryNotice_FR | frame {div_attrs} ]]    force french
     */
    {
// TODO: transition to always using mbr: namespace.  Only the August grower lists still use mbr_ prefixes

        if( $raTag['namespace'] == 'mbr' ) {
            $tag = $raTag['target'];
        } else if( substr($raTag['target'],0,4) == 'mbr_' ) {
            $tag = substr($raTag['target'],4);
        } else {
            return( false );
        }

        /* Handle tags where kfr is allowed to be NULL (no member has been set)
         */
        /* mbr:ExpiryNotice / mbr:ExpiryNotice_EN / mbr:ExpiryNotice_FR
         *
         *    [[mbr:ExpiryNotice_EN | {frame {div_attrs}} ]]        e.g. div_attrs = "style='border:foo'"
         */
// can div_attrs be a $variable
        if( substr($tag, 0, strlen("ExpiryNotice")) == "ExpiryNotice" ) {
            if( $tag == 'ExpiryNotice_EN' ) { $lang = "EN"; }  // override $lang in these cases
            if( $tag == 'ExpiryNotice_FR' ) { $lang = "FR"; }

            $s = $this->getExpiryNotice( $tag, $lang );

            if( substr(@$raTag['parms'][0], 0, 5) == 'frame' ) {
                if( !($attrs = trim(substr( $raTag['parms'][0], 5 ))) ) {
                    $attrs = "style='border:1px solid black;padding:5px;margin:10px;'";
                }
                return( "<div $attrs>$s</div>" );
            } else {
                return( $s );
            }
        }


        /* Handle tags where kfr must be set (kMbr is valid)
         */
        if( !$this->kfr ) return( "*$tag*" );    // assumes that all tags starting with 'mbr_' are intended to be handled by this function

        /* Translate any tags that have the form mbr:foo where foo is a base field of mbr_contacts
         */
        if( $this->kfrel->IsBaseField( $tag ) ) {
            return( $this->kfr->value($tag) );
        }

        switch( $tag ) {
// code case below goes away when all docs use mbr: namespace (it will be mbr:mbr_code)
           // Exception to the above form: mbr_code has that prefix in the database column name
           case 'code':         return( $this->kfr->value('mbr_code') );
           case 'addressBlock': return( $this->DrawAddressBlock() );
        }


        return( "*$tag*" );  // assumes that all tags starting with 'mbr_' are intended to be handled by this function
    }

    private function getExpiryNotice( $tag, $lang )
    {
        /* 1) Never been a member (also handles case where kfr=NULL; e.g. an email recipient not in mbr_contacts) : Return str[nonmember]
         *
         * 2) Special member : "Membership: Automatic"
         *
         * 3) Regular member expires this year : "Membership expires: Dec 201?  Renew your membership at [this link] "
         *
         * 4) Regular member expires future year : "Membershipi expires: Dec 201?"
         *
         * 5) Regular member expired : str[renew]
         */
        $raStr = array( "nonmember"=>array( 'EN' => "Thank you for your support. Join us as a member at <A href='https://www.seeds.ca/member' target='_blank'>https://www.seeds.ca/member</A>",
                                            'FR' => "Merci pour votre soutien. Vous pouvez nous joindre &agrave; <A href='https://www.semences.ca/membre' target='_blank'>https://www.semences.ca/membre</A>"),
                        "mbr_code" =>array( 'EN' => "Your Membership: ",
                                            'FR' => "Votre Adh&eacute;sion: " ),
                        "mbr_exp"  =>array( 'EN' => "Your Membership expires: ",
                                            'FR' => "Votre Adh&eacute;sion expire: " ),
                        "renew"    =>array( 'EN' => "Renew your membership now at <A href='https://www.seeds.ca/member' target='_blank'>https://www.seeds.ca/member</A>",
                                            'FR' => "Renouvellez votre adh&eacute;sion maintenant &agrave; <A href='https://www.semences.ca/membre' target='_blank'>https://www.semences.ca/membre</A>" ) );

        $iYCurrent = intval( date( "Y", time() + 3600*24*120 ) );  // the year of 120 days hence

        $sSal = $this->kfr ? ($this->MakeName().$this->kfr->ExpandIfNotEmpty('email', "<BR/>[[]]")."</BR><BR/>") : "";

        // 1
        if( !$this->kfr || !($sExpires = $this->kfr->value('expires')) ) { return( $sSal.$raStr['nonmember'][$lang] ); }

        // 2
        if( ($sCode = MbrExpiryDate2Code( $sExpires )) ) { return( $sSal.$raStr['mbr_code'][$lang].MbrExpiryCode2Label($sCode) ); }

        $iYExpiry = intval(substr($sExpires,0,4));
        $sExpiry = ($lang == 'EN' ? 'Dec ' : 'D&eacute;c ').$iYExpiry; // put Dec in front of the expiry year

        // 3
        if( $iYExpiry == $iYCurrent ) { return( $sSal.$raStr['mbr_exp'][$lang].$sExpiry ); }

        // 4
        if( $iYExpiry > $iYCurrent ) { return( $sSal.$raStr['mbr_exp'][$lang].$sExpiry ); }

        // 5
        return( $sSal.$raStr['mbr_exp'][$lang].$sExpiry.". ".$raStr['renew'][$lang] );
    }

    static function KfrelBase( $kfdb, $uid = 0, $raParms = array() )
    {
        $kfreldef =
            array( "Tables"=>array( array( "Table" => 'mbr_contacts',
                                           "Type" => "Base",
                                           "Fields" => array( array("col"=>"mbr_code",          "type"=>"S"),
                                                              array("col"=>"firstname",         "type"=>"S"),
                                                              array("col"=>"lastname",          "type"=>"S"),
                                                              array("col"=>"firstname2",        "type"=>"S"),
                                                              array("col"=>"lastname2",         "type"=>"S"),
                                                              array("col"=>"company",           "type"=>"S"),
                                                              array("col"=>"dept",              "type"=>"S"),
                                                              array("col"=>"address",           "type"=>"S"),
                                                              array("col"=>"city",              "type"=>"S"),
                                                              array("col"=>"province",          "type"=>"S"),
                                                              array("col"=>"country",           "type"=>"S"),
                                                              array("col"=>"postcode",          "type"=>"S"),
                                                              array("col"=>"phone",             "type"=>"S"),
                                                              array("col"=>"phone_ext",         "type"=>"S"),
                                                              array("col"=>"email",             "type"=>"S"),
                                                              array("col"=>"status",            "type"=>"S"),
                                                              array("col"=>"startdate",         "type"=>"S"),
                                                              array("col"=>"expires",           "type"=>"S"),
                                                              array("col"=>"lang",              "type"=>"S"),
                                                              array("col"=>"referral",          "type"=>"S"),
                                                              array("col"=>"lastrenew",         "type"=>"S"),
                                                              array("col"=>"bNoEBull",          "type"=>"S"),  // I?
                                                              array("col"=>"bNoDonorAppeals",   "type"=>"I"),
                                                              array("col"=>"bNoPaperMail",      "type"=>"I"),
                                                              array("col"=>"bNoSED",            "type"=>"I"),  // obsolete and no longer updated
                                                              array("col"=>"bPrintedMSD",       "type"=>"I"),
                                                              array("col"=>"comment",           "type"=>"S"),
                                                              array("col"=>"donation",          "type"=>"S"),
                                                              array("col"=>"donation_date",     "type"=>"S") ) ) ) );

        $kfrel = new KeyFrameRelation( $kfdb, $kfreldef, $uid, $raParms );
        return( $kfrel );
    }
}


function Mbr_WhereIsContactReferenced( SEEDAppDB $oApp, $kMbr )
{
    $ra = [];

    $ra['nSBBaskets' ]  = $oApp->kfdb->Query1( "SELECT count(*) from {$oApp->GetDBName('seeds1')}.SEEDBasket_Baskets  WHERE _status='0' AND uid_buyer='$kMbr'" );
    $ra['nSProducts']   = $oApp->kfdb->Query1( "SELECT count(*) from {$oApp->GetDBName('seeds1')}.SEEDBasket_Products WHERE _status='0' AND uid_seller='$kMbr'" );
    $ra['nDescSites']   = $oApp->kfdb->Query1( "SELECT count(*) from {$oApp->GetDBName('seeds1')}.mbr_sites           WHERE _status='0' AND uid='$kMbr'" );
    $ra['nMSD']         = $oApp->kfdb->Query1( "SELECT count(*) from {$oApp->GetDBName('seeds1')}.sed_curr_growers    WHERE _status='0' AND mbr_id='$kMbr'" );
    $ra['nSLAdoptions'] = $oApp->kfdb->Query1( "SELECT count(*) from {$oApp->GetDBName('seeds1')}.sl_adoption         WHERE _status='0' AND fk_mbr_contacts='$kMbr'" );

    $ra['nDonations']   = $oApp->kfdb->Query1( "SELECT count(*) from {$oApp->GetDBName('seeds2')}.mbr_donations       WHERE _status='0' AND fk_mbr_contacts='$kMbr'" );

    return( $ra );
}

function MbrContacts_Setup( $oSetup, &$sReport, $bCreate = false )
/*****************************************************************
    Test whether the mbr_mail_* tables exist.
    bCreate: create the tables and insert initial data if they don't exist.

    Return true if exists (or create is successful if bCreate); return a text report in sReport

    N.B. $oSetup is a SEEDSetup.  This file doesn't include SEEDSetup.php because the setup code is very rarely used.
         Instead, the code that calls this function knows about SEEDSetup.
 */
{
    $sReport = "";
    $bRet = $oSetup->SetupTable( "mbr_contacts", SEEDS2_DB_TABLE_MBR_CONTACTS, $bCreate, $sReport );

    return( $bRet );
}

define("SEEDS2_DB_TABLE_MBR_CONTACTS",
"
CREATE TABLE mbr_contacts (
        _key        INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT,
        _created    DATETIME,
        _created_by INTEGER,
        _updated    DATETIME,
        _updated_by INTEGER,
        _status     INTEGER DEFAULT 0,

--  mbr_id          INTEGER,        -- NUMBER
    mbr_code        CHAR(10),       -- MEMBER_ID
    firstname       VARCHAR(200),   -- FIRSTNAME
    lastname        VARCHAR(200),   -- LASTNAME
    firstname2      VARCHAR(200),
    lastname2       VARCHAR(200),
    company         VARCHAR(200),   -- COMPANY
    dept            VARCHAR(200),   -- DEPARTMENT
    address         VARCHAR(200),   -- ADDRESS
    city            VARCHAR(200),   -- CITY
    province        VARCHAR(200),   -- PROVINCE
    country         VARCHAR(200),   -- COUNTRY
    postcode        VARCHAR(200),   -- PCODE
    phone           VARCHAR(200),   -- PHONE
    phone_ext       VARCHAR(200),   -- EXTENSION
    email           VARCHAR(200),   -- EMAIL
    status          VARCHAR(200),   -- STATUS
    startdate       VARCHAR(200),   -- STARTDATE
    expires         VARCHAR(200),   -- EXPIRES
    lang            ENUM('','E','F') NOT NULL DEFAULT '',     -- FRENCH
    referral        VARCHAR(200),   -- REFERRAL
    lastrenew       VARCHAR(200),   -- LASTRENEW
    bNoEBull        VARCHAR(200),   -- NO E bulletin (check)
    bNoDonorAppeals INTEGER,        -- No Donor Appeals (check)
    bNoSED          INTEGER,        -- No SED (check)            obsolete
    bPrintedMSD     INTEGER NOT NULL DEFAULT 0,        -- Member paid for a printed Member Seed Directory
    comment         VARCHAR(200),   -- Comment
    donation        VARCHAR(200),   -- Donation
    donation_date   VARCHAR(200),   -- Date of Donation

    INDEX mbr_contacts_firstname    (firstname(6)),
    INDEX mbr_contacts_lastname     (lastname(6)),
    INDEX mbr_contacts_firstname2   (firstname2(6)),
    INDEX mbr_contacts_lastname2    (lastname2(6)),
    INDEX mbr_contacts_city         (city(6)),
    INDEX mbr_contacts_postcode     (postcode),
    INDEX mbr_contacts_email        (email(6)),
    INDEX mbr_contacts_expires      (expires)
);
"
);

/*
CREATE TABLE mbr_donors (
        _key        INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT,
        _created    DATETIME,
        _created_by INTEGER,
        _updated    DATETIME,
        _updated_by INTEGER,
        _status     INTEGER DEFAULT 0,

    fk_mbr_contacts     INTEGER NOT NULL,
    lastname            VARCHAR(200),           -- redundant but nice for viewing from the command client
    donation_date       VARCHAR(200),           -- copy of mbr_contacts.donation_date
    donation            VARCHAR(200),           -- copy of mbr_contacts.donation
    receipt             VARCHAR(200),           -- copy of mbr_tmp_mdb_upload.receipt__
    cumulative          VARCHAR(200),           -- copy of mbr_tmp_mdb_upload.Cumulative_donations
    flagLast            INTEGER DEFAULT 0,

    INDEX mbr_donors_fk   (fk_mbr_contacts),
    INDEX mbr_donors_date (donation_date(6)),
);

SELECT * from mbr_contacts C, mbr_donors D
WHERE D.fk_mbr_contacts=C._key AND

*/

?>
