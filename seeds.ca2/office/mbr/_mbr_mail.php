<?php

include_once( STDINC."DocRep/DocRepWiki.php" );
include_once( SEEDCOMMON."doc/docUtil.php" );  // New_DocRepDB_WithMyPerms, DocRepWiki_Site
include_once( SEEDCOMMON."siteTemplate.php" );
//include_once( SEEDLIB."SEEDTemplate/masterTemplate.php" );  move MasterTemplate here
include_once( "_mbr.php" );                    // kfrelDef_mbr_contacts


/* mbr_mailsend
 */
define("SEEDS2_DB_TABLE_MBR_MAIL_SEND___OBSOLETE",
"
CREATE TABLE mbr_mail_send (
        _key        INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT,
        _created    DATETIME,
        _created_by INTEGER,
        _updated    DATETIME,
        _updated_by INTEGER,
        _status     INTEGER DEFAULT 0,

    fk_docrep_docs  INTEGER NOT NULL,  # doc to mail
    dr_vars         TEXT,              # url-encoded string of variables to be applied to the doc
    fk_mbr_contacts INTEGER DEFAULT 0, # recipient (can be zero if sending to non-recorded contact e.g. eBull subscriber)
    email_to        TEXT,              # To:  (can contain comma-separated list)
    email_cc        TEXT,              # Cc:  (can contain comma-separated list)
    email_bcc       TEXT,              # Bcc: (can contain comma-separated list)
    email_from      VARCHAR(100),      # From:
    email_subject   TEXT,
    eStatus         enum('NEW','READY','SENDING','SENT','FAILED') DEFAULT 'NEW',
    iResult         INTEGER DEFAULT 0, # return value from smtp
    ts_sent         TIMESTAMP,
    sExtra          TEXT,              # urlencoded extensions

    INDEX (eStatus)
);
"
);


/* mbr_mail_send            : each row is an email message, defining the document, subject, from
 * mbr_mail_send_recipients : each row is one recipient of one email, defining the destination email address (and/or mbr _key), document variables, status
 */

define("SEEDS2_DB_TABLE_MBR_MAIL_SEND",
"
CREATE TABLE mbr_mail_send (
        _key        INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT,
        _created    DATETIME,
        _created_by INTEGER,
        _updated    DATETIME,
        _updated_by INTEGER,
        _status     INTEGER DEFAULT 0,

    fk_docrep_docs  INTEGER NOT NULL,  # doc to mail
    eDB             ENUM('public','office') NOT NULL DEFAULT 'public',
    email_from      VARCHAR(100),      # From:
    email_subject   TEXT,              # Subject:  (can contain wiki tags expanded per-recipient)
    eStatus         enum('NEW','APPROVE','READY','SENDING','SENT','FAILED') DEFAULT 'NEW',
    sExtra          TEXT               # urlencoded extensions and working data
);
"
);

define("SEEDS2_DB_TABLE_MBR_MAIL_SEND_RECIPIENTS",
"
CREATE TABLE mbr_mail_send_recipients (
        _key        INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT,
        _created    DATETIME,
        _created_by INTEGER,
        _updated    DATETIME,
        _updated_by INTEGER,
        _status     INTEGER DEFAULT 0,

    fk_mbr_mail_send INTEGER NOT NULL,
    email_to        TEXT,              # To:  (can contain comma-separated list)
    email_cc        TEXT,              # Cc:  (can contain comma-separated list)
    email_bcc       TEXT,              # Bcc: (can contain comma-separated list)
    fk_mbr_contacts INTEGER DEFAULT 0, # recipient (can be zero if sending to non-recorded contact e.g. eBull subscriber)
    dr_vars         TEXT,              # url-encoded string of variables to be applied to the doc
    eStatus         enum('READY','SENDING','SENT','FAILED') DEFAULT 'READY',
    iResult         INTEGER DEFAULT 0, # return value from smtp
    ts_sent         TIMESTAMP,
    sExtra          TEXT,              # urlencoded extensions

    INDEX (fk_mbr_mail_send,eStatus)   # optimize grouping by message, also lookup for a READY recipient of a given message
);
"
);


class mbr_mail_DocRepWiki extends DocRepWiki_Site
{
    private $oMail;

    function __construct( mbr_mail $oMail, $eDB, $sDocRepFlag )
    {
        $oDR = ($eDB == 'office' ? $oMail->oDocRepDB2 : $oMail->oDocRepDB1);
        parent::__construct( $eDB, $oDR, $sDocRepFlag,
                             // tell SEEDWiki to leave unknown tags intact, so we can use SEEDTag to process them
                             array('kluge_dontEatMyTag'=>true) );

        $this->oMail = $oMail;
    }

    function HandleLink( $raLink )
    {
        if( $raLink['namespace'] == 'mbr' ) {
            // N.B.: DocRepWiki is powered by kfdb1 or kfdb2 depending on the mbr_mail_send record,
            // but oMbr is always powered by kfdb2.
            // That means a mail message stored in docrep1 with mbr stuff like ExpiryNotice looks right on here on seeds2, but
            // not when you look at it in docrep of seeds1

            if( ($s = $this->oMail->oMbr->TranslateTag( $raLink, $this->Lang() )) !== false )
                return( $s );

            return( "" ); // this indicates that we handle all mbr: tags, so any unknown are not valid
        }

/* Replaced by SEEDSessionAccount_Password

        if( $raLink['target'] == "password_seeds1" ) {
            // Don't put this in a MasterTemplate hander because we don't want somebody to be able to write arbitrary SEEDTag code that prints peoples' passwords
            // Instead, see [[SeedSessionPasswordTest:]], which just sets the variable bSeedSessionPasswordAutoGen so the MSD notice can tell whether a password
            // is one of our auto-generated passwords, or whether it has been changed.
            if( !($kMbr = $this->oMail->oMbr->kMbr) ) return( "" );
            $pwd = $this->oMail->kfdb1->Query1( "SELECT password FROM seeds_1.SEEDSession_Users WHERE _key='$kMbr'" );
            return( $pwd );
        }
*/

        if( $raLink['namespace'] == 'SITEROOT' ) {
            return( SITEROOT );
        }

        return( parent::HandleLink( $raLink ) );
    }
}

class mbr_mail
/*************
    Support class for all mailing applications (mail setup, mail sending, etc)
 */
{
    var $kfdb1;
    var $kfdb2;
    var $kfrelMail;         // mbr_mail_send : one row per message
    var $kfrelRecipients;   // mbr_mail_send_recipients : one row per recipient of each message

    // used by friends
    var $oMbr;
    var $oDocRepDB1;   // a readonly docrep-accessor with my perms on db-seeds
    var $oDocRepDB2;   // a readonly docrep-accessor with my perms on db-seeds2


    // We can only email PUB documents because the user will probably not be logged into DocRep.
    // DocRepWiki_Site image and links will use doc.pub to issue "" or docpub.php to issue "PUB", so we should always force it to use docpub.php.
    private $sDocRepFlag = "PUB";

    public $raFrom = array(
                "eBulletin@seeds.ca"        => "Seeds of Diversity Canada - eBulletin <eBulletin@seeds.ca>",
                "eBulletin@semences.ca"     => "Semences du patrimoine Canada - eBulletin <eBulletin@semences.ca>",
                "info@pollinationcanada.ca" => "Pollination Canada <info@pollinationcanada.ca>",
                "office@seeds.ca"           => "Seeds of Diversity <office@seeds.ca>",
                "courriel@semences.ca"      => "Semences du patrimoine <courriel@semences.ca>",
                "judy@seeds.ca"             => "Judy at Seeds of Diversity <judy@seeds.ca>",
                "bob@seeds.ca"              => "Bob at Seeds of Diversity <bob@seeds.ca>",
    );


    function __construct( KeyFrameDB $kfdb1, KeyFrameDB $kfdb2, $uid )
    {
        $this->kfdb1 = $kfdb1;
        $this->kfdb2 = $kfdb2;
        $this->initKfrel( $uid );
        $this->oMbr = new MbrContacts( $kfdb2 );
        $this->oDocRepDB1 = New_DocRepDB_WithMyPerms( $kfdb1, $uid, array('bReadonly'=>true) );
        $this->oDocRepDB2 = New_DocRepDB_WithMyPerms( $kfdb2, $uid, array('bReadonly'=>true) );
    }

    function Clear()
    {
        $this->oMbr->SetKMbr(0);
    }

    function GetKDoc( $kfrMail )
    /***************************
        Get the kdoc of the mail document for the given kfrMail
     */
    {
        $oDocRepDB = ($kfrMail->value('eDB') == 'office' ? $this->oDocRepDB2 : $this->oDocRepDB1);
        return( $oDocRepDB->GetDocFromName( $kfrMail->value('fk_docrep_docs') ) );
    }

    function GetDocName( $kfrMail )
    /******************************
        Get the name of the mail document for the given kfrMail
     */
    {
        $oDocRepDB = ($kfrMail->value('eDB') == 'office' ? $this->oDocRepDB2 : $this->oDocRepDB1);
        return( $oDocRepDB->GetDocName( $kfrMail->value('fk_docrep_docs') ) );
    }

    function GetDocTitle( $kfrMail )
    {
        $sTitle = "";
        if( ($oDoc = $this->GetDoc($kfrMail)) ) {
            $sTitle = $oDoc->GetTitle( $this->sDocRepFlag );
        }
        return( $sTitle );
    }

    function GetDoc( $kfrMail )
    {
        $oDocRepDB = ($kfrMail->value('eDB') == 'office' ? $this->oDocRepDB2 : $this->oDocRepDB1);
        $oDoc = $oDocRepDB->GetDocObject($kfrMail->value('fk_docrep_docs'));
        return( $oDoc );
    }

    function DrawMailFromKFR( $kfrRecipient )
    {
        $raDRVars = $kfrRecipient->UrlParmGetRA('dr_vars');

        if( !($kfrMail = $this->kfrelMail->GetRecordFromDBKey( $kfrRecipient->value('fk_mbr_mail_send') )) ) { return( NULL ); }

// Use this to uniquely identify this email, e.g. for email tracking. The reason is we want to track members and ebulletin subscribers.
// So when those are all in SEEDSession_Users, this could become the user key, which would be better. The reason for that is the tracking
// link could be seen in the mailsetup preview, which it can't right now because it's based on the recipient-email key, not the user.
$raDRVars['kMailSend'] = $kfrRecipient->Key();

        return( $this->DrawMail( $kfrMail->value('fk_docrep_docs'),
                                 $kfrMail->value('eDB'),
                                 $kfrRecipient->value('email_to'),
                                 $kfrRecipient->value('fk_mbr_contacts'),
                                 $kfrMail->value('email_subject'),
                                 $raDRVars ) );
    }

    function DrawMail( $kDoc, $eDB, $sEmailTo, $kMbr, $sEmailSubject, $raDRVars )
    /****************************************************************************
        kDoc            : docrep key of the mail body
        eDB             : public | office
        sEmailTo | kMbr : email address and/or member key
        sEmailSubject   : email subject
        raDRVars        : docrep vars (can include dr_template)
     */
    {
        // Set the oMbr using the given kMbr/sEmailTo so [[mbr:]] tags can be expanded
        if( $kMbr && !$sEmailTo ) {
            if( ($kfrM = $this->oMbr->GetKFRByKey( $kMbr )) ) {
                $sEmailTo = $kfrM->Value('email');
            }
        } else if( $sEmailTo && !$kMbr ) {
            if( ($kfrM = $this->oMbr->GetKFRByEmail( $sEmailTo )) ) {
                $kMbr = $kfrM->Key();
            }
        } else {
            // if both are given, assume they are consistent
            //$kMbr = 0;
        }
        $this->oMbr->SetKMbr( $kMbr );

        /* At this point, sEmailTo should be the recipient's email whether or not they are in mbr_contacts,
         * and kMbr should be their mbr_contacts key if that exists.
         *
         * Mail authors have to make sure that all cases work gracefully.
         * All [[mbr:]] tag expansions have to work gracefully for kMbr==0
         */

        $oDocRepWiki = new mbr_mail_DocRepWiki( $this, $eDB, $this->sDocRepFlag );

        // get vars from the doc, its ancestors, and any template defined there
        list($kTemplate, $raDRVars)
            = DocRepApp_GetTemplateAndVars( $oDocRepWiki->oDocRepDB, $kDoc, $this->sDocRepFlag, $raDRVars );

        $oDocRepWiki->AddVars( $raDRVars );
        $oDocRepWiki->AddVar( 'kMbrTo', $kMbr );
        $oDocRepWiki->AddVar( 'sEmailTo', $sEmailTo );
        $oDocRepWiki->AddVar( 'sEmailSubject', $sEmailSubject );
        $sDoc = $kTemplate ? $oDocRepWiki->TranslateDocWithTemplate( $kDoc, $kTemplate )
                           : $oDocRepWiki->TranslateDoc( $kDoc );

        $uid = 0;                   // maybe this should be $uid instead of 0
        $lang = $oDocRepWiki->Lang();

        $raMT['EnableDocRep']['site'] = $eDB;
        $raMT['EnableDocRep']['flag'] = $this->sDocRepFlag;
        $raMT['EnableDocRep']['oDocRepDB'] = ($eDB == 'office' ? $this->oDocRepDB2 : $this->oDocRepDB1);
// make a MailResolver or something for the special tags in mbr_mail_DocRepWiki::HandleLink

        include_once( STDINC."SEEDSessionAccountTag.php" );
        $raMT['EnableSEEDSession']['oSessTag'] = new SEEDSessionAccountTag( $this->kfdb1, $uid, array( 'bAllowKMbr'=>true, 'bAllowPwd'=>true ) );

        $raMT['raSEEDTemplateMakerParms'] = ['kluge_dontEatMyTag'=>true];
        $oMaster = new MasterTemplate( $this->kfdb1, $uid, $lang, $raMT );
        if( ($oTmpl = $oMaster->GetTmpl()) ) {
            $sDoc = $oTmpl->ExpandStr( $sDoc, array( 'kMbrTo' => $kMbr, 'lang'=>$lang ) );
        }

        include( SEEDLIB."SEEDTemplate/masterTemplate.php" );
        $oApp = SEEDConfig_NewAppConsole_LoginNotRequired( [] );   // seeds1 and no perms required
        $oMaster2 = new SoDMasterTemplate( $oApp, [] );
        if( ($oTmpl = $oMaster2->GetTmpl()) ) {
            $sDoc = $oTmpl->ExpandStr( $sDoc, array( 'kMbrTo' => $kMbr, 'lang'=>$lang ) );
        }

        return( $sDoc );
    }


    function MailSendPutEmails( $sList, KFRecord $kfrMail )
    /******************************************************
        Given a string containing email addresses and fk_mbr_contact ids, set those into a mbr_mail_send record
        in as compressed a form as possible (because sExtra cannot have more than 64K under mysql).
     */
    {
        // Normalize the list by replacing all likely separators, CRLF, etc, with single spaces and exploding on those
        $raNonEmailChars = array( "\r", "\n", "\t", ',', ';', ':', '"', "'", '(', ')', '<', '>', '[', ']', '|' );
        $sList = str_replace( $raNonEmailChars, ' ', $sList );
        $sList = trim($sList);

        // Get rid of multiple spaces so we can simply explode on ' ' to get an array of addresses.
        // str_replace's fourth argument returns the number of needles replaced
        for( $n=1; $n; ) { $sList = str_replace( '  ', ' ', $sList, $n ); }

        // explode the list: numeric values are stored as member numbers;
        //                   emails are converted to member numbers if possible;
        //                   remaining emails are converted to ebulletin keys if possible;
        //                   remaining emails are stored verbatim
        $raList = explode( ' ', $sList );
        $raEmails = array();
        $raMbr = array();
        $raBull = array();
        foreach( $raList as $e ) {
            if( !$e ) continue;

            $eDB = addslashes($e);

            // is it a member number?
            if( is_numeric($e) ) {
                if( $this->kfdb2->Query1( "SELECT _key FROM mbr_contacts WHERE _key='$eDB'" ) ) {
                    $raMbr[] = $e;
                }
            // is it a member email?
            } else if( ($k = $this->kfdb2->Query1( "SELECT _key FROM mbr_contacts WHERE email='$eDB'")) ) {
                $raMbr[] = $k;
            // is it a bulletin email?
            } else if( ($k = $this->kfdb2->Query1( "SELECT _key FROM seeds_1.bull_list WHERE email='$eDB'")) ) {
                // It doesn't matter if this is a valid bulletin subscription. The point is that someone assigned this
                // email address to the send list, and this _key is a short way to look up that email address.
                $raBull[] = $k;
            } else {
                $raEmails[] = $e;
            }
        }
        $sListEmails = implode( ' ', $raEmails );
        $sListMbr = implode( ' ', $raMbr );
        $sListBull = implode( ' ', $raBull );

        $kfrMail->UrlParmSet( 'sExtra', 'email_addresses', $sListEmails );
        $kfrMail->UrlParmSet( 'sExtra', 'mbr_keys', $sListMbr );
        $kfrMail->UrlParmSet( 'sExtra', 'bull_keys', $sListBull );


        if( false ) {
            // Show how long the strings are
            echo "Posted address string length = ".strlen($sList)."<br/>"
                ."Extracted mbr keys string length = ".strlen($sListMbr)."<br/>"
                ."Extracted ebull keys string length = ".strlen($sListBull)."<br/>"
                ."Remaining addresses string length = ".strlen($sListEmails)."<br/>"
                ."Urlparms storage size for those three = ".strlen(urlencode($sListEmails.$sListMbr.$sListBull))."<br/>"
                ;
        }
    }

    function MailSendGetEmailsRA( KFRecord $kfrMail )
    /************************************************
        Same as MailSendGetEmailsAndMbrKeysRA except look up the email addresses for mbr keys - all returned values are email addresses
     */
    {
        list($raEmails,$raKMbr) = $this->MailSendGetEmailsAndMbrKeys( $kfrMail );

        // get mbr emails
        foreach( $raKMbr as $k ) {
            $k = addslashes($k);
            if( ($sE = $this->kfdb2->Query1( "SELECT email FROM mbr_contacts where _key='$k'")) ) {
                $raEmails[] = $sE;
            }
        }
        sort($raEmails);

        return( $raEmails );
    }

    function MailSendGetEmailsAndMbrKeys( KFRecord $kfrMail )
    /********************************************************
        Given a mbr_mail_send record, return two strings containing the mbr keys and the email addresses for non-mbr-keys
     */
    {
        $raEmails = explode( ' ', $kfrMail->UrlParmGet( 'sExtra', 'email_addresses' ) );
        $raKMbr1  = explode( ' ', $kfrMail->UrlParmGet( 'sExtra', 'mbr_keys' ) );
        $raBull   = explode( ' ', $kfrMail->UrlParmGet( 'sExtra', 'bull_keys' ) );

        // normalize the kmbr just in case
        $raKMbr = array();
        foreach( $raKMbr1 as $k ) {
            if( ($k = intval($k)) )  $raKMbr[] = $k;
        }

        // get bulletin emails
        foreach( $raBull as $k ) {
            $k = addslashes($k);
            if( ($sE = $this->kfdb2->Query1( "SELECT email FROM seeds_1.bull_list where _key='$k'")) ) {
                $raEmails[] = $sE;
            }
        }
        sort($raEmails);

        return( array($raEmails,$raKMbr) );
    }

    function GetFullFrom( $sFrom )
    /*****************************
        Given a "from" email address get the full address (with optional name) to put in the header.

        e.g. eBulletin@seeds.ca is converted to Seeds of Diversity - eBulletin <eBulletin@seeds.ca>
     */
    {
        if( ($s1 = @$this->raFrom[$sFrom]) ) {
            $sFrom = $s1;
        }
        return( $sFrom );
    }

    function FinalizeSent( $kMail )
    /******************************
        Call after a mail doc has been fully sent, and its eStatus is changed to SENT.
        This cleans up the old address and recipient records to save db space.
     */
    {
        $this->kfdb2->Execute( "UPDATE seeds_2.mbr_mail_send SET sExtra='' WHERE _key='$kMail" );
        if( ($kfrc = $this->kfrelRecipients->CreateRecordCursor( "fk_mbr_mail_send='$kMail'" )) ) {
            while( $kfrc->CursorFetch() ) {
                $ts = $kfrc->Value('ts_sent');
                if( substr($ts,0,4) == "0000" )  $ts = "";

                $line = $kfrc->Expand( "[[fk_mbr_mail_send]] [[email_to]] [[fk_mbr_contacts]] [[eStatus]] [[iResult]]" )
                       ."{".($ts ? date( "Y-M-d hh:mm:ss", $ts) : "")."}";
                Site_Log( "mbr_mailsend_finalizesent", $line );
            }
            //$this->kfdb2->Execute( "DELETE FROM seeds_2.mbr_mail_send_recipients WHERE fk_mbr_mail_send='$kMail'" );
        }
    }

    function initKfrel( $uid )
    {
        $fldMailSend = array( array("col"=>"fk_docrep_docs",  "type"=>"K"),
                              array("col"=>"eDB",             "type"=>"S", "default"=>'public'),
                              array("col"=>"email_from",      "type"=>"S"),
                              array("col"=>"email_subject",   "type"=>"S"),
                              array("col"=>"eStatus",         "type"=>"S", "default"=>'NEW'),
                              array("col"=>"sExtra",          "type"=>"S") );

        $kfreldef_mbrMailSend =
            array( "Tables"=>array( array( "Table" => 'mbr_mail_send',
                                           "Fields" => $fldMailSend ) ) );

        $kfreldef_mbrMailSendRecipients =
            array( "Tables"=>array( array( "Table" => 'mbr_mail_send_recipients',
                                           "Type" => 'Base',
                                           "Alias" => 'MSR',
                                           "Fields" => array( array("col"=>"fk_mbr_mail_send","type"=>"K"),
                                                              array("col"=>"email_to",        "type"=>"S"),
                                                              array("col"=>"email_cc",        "type"=>"S"),
                                                              array("col"=>"email_bcc",       "type"=>"S"),
                                                              array("col"=>"fk_mbr_contacts", "type"=>"K"),
                                                              array("col"=>"dr_vars",         "type"=>"S"),
                                                              array("col"=>"eStatus",         "type"=>"S", "default"=>'NEW'),
                                                              array("col"=>"iResult",         "type"=>"I"),
                                                              //array("col"=>"ts_sent",         "type"=>"S"),
                                                              array("col"=>"sExtra",          "type"=>"S") ) ),
                                    array( "Table" => 'mbr_mail_send',
                                           "Type" => 'Parent',
                                           "Alias" => "MS",
                                           "Fields" => $fldMailSend ) ) );

        $this->kfrelMail = new KeyFrameRelation( $this->kfdb2, $kfreldef_mbrMailSend, $uid );
        $this->kfrelRecipients = new KeyFrameRelation( $this->kfdb2, $kfreldef_mbrMailSendRecipients, $uid );
    }
}


function MbrMail_Setup( $oSetup, &$sReport, $bCreate = false )
/*************************************************************
    Test whether the mbr_mail_* tables exist.
    bCreate: create the tables and insert initial data if they don't exist.

    Return true if exists (or create is successful if bCreate); return a text report in sReport

    N.B. $oSetup is a SEEDSetup.  This file doesn't include SEEDSetup.php because the setup code is very rarely used.
         Instead, the code that calls this function knows about SEEDSetup.
 */
{
    $sReport = "";
    $bRet = $oSetup->SetupTable( "mbr_mail_send",            SEEDS2_DB_TABLE_MBR_MAIL_SEND,            $bCreate, $sReport ) &&
            $oSetup->SetupTable( "mbr_mail_send_recipients", SEEDS2_DB_TABLE_MBR_MAIL_SEND_RECIPIENTS, $bCreate, $sReport );

    return( $bRet );
}

?>
