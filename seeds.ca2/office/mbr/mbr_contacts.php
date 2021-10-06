<?php

/* Contact and Login manager

   Copyright (c) 2009-2021 Seeds of Diversity Canada

   Contact database:
       Read only view of the mbr_contacts database

   Login manager:
       Management of seeds_1.SEEDSession_Users
       Does not allow fine management of user logins (see SEEDSessionUGP).
       Instead, this does high level diagnostics and repairs based on Contact database and user login policies.
 */

define( "SITEROOT", "../../" );
include_once( SITEROOT."site2.php" );
//include_once( STDINC."SEEDCSV.php" );  not used anymore?
include_once( SEEDCOMMON."console/console01kfui.php" );
include_once( SEEDCOMMON."mbr/mbrDB.php" );
include_once( SEEDCOMMON."mbr/mbrBulletin.php" );
include_once( "_mbr.php" );
include_once( "_mbr_mail.php" );

include_once( SEEDAPP."mbr/mbr_ts_ebulletin.php" );
include_once( SEEDCORE."SEEDTableSheets.php" );

define( "MBRCONTACTS_TABNAME_BULLETIN", "Bulletin" );    // so per-tab TabSetGetSVA knows its name


$raPerms = array( 'Contacts'                   => array('R MBR'),
                  'Summary'                    => array('A MBR'),
                  'Logins'                     => array('A MBR'),
                  MBRCONTACTS_TABNAME_BULLETIN => array('W BULL'),
                                                  '|'   // the above are disjunctions for application access
);

list($kfdb2, $sess) = SiteStartSessionAccount( $raPerms );
$kfdb1 = SiteKFDB( SiteKFDB_DB_seeds1 ) or die( "Cannot connect to database" );
$bReadonly = !($sess->CanWrite( "MBR" ));

header( "Content-type: text/html; charset=ISO-8859-1");    // this should be on all pages so accented chars look right (on Linux anyway)

$kfdb2->SetDebug(1);

$oApp = SEEDConfig_NewAppConsole_LoginNotRequired( ['db'=>'seeds2'] );


// Implement Post-Redirect-Get paradigm.
SEEDPRG();
//var_dump($_REQUEST);  // after SEEDPRG because that resets _REQUEST to contain any _POST from prior to an http 303



/*                  "SearchToolCols"  => array( "Contact #"=>"_key",
                                              "First Name" => "firstname",
                                              "Last Name" => "lastname",
                                              "First Name 2" => "firstname2",
                                              "Last Name 2" => "lastname2",
                                              "Company" => "company",
                                              "Address" => "address",
                                              "City" => "city",
                                              "Province" => "province",
                                              "Postcode" => "postcode",
                                              "Phone" => "phone",
                                              "Email" => "email",
                                              "Expiry" => "expires" ),
*/
//                "fnListFilter"    => "Item_rowFilter",
//                "fnListTranslate" => "mbr_contacts_listTranslate",

class MyConsole extends Console01KFUI
{
    public $oW;

    function __construct( KeyFrameDB $kfdb, SEEDSessionAccount $sess, $raParms )   // kfdb is kfdb2
    {
        parent::__construct( $kfdb, $sess, $raParms );
    }

    function TabSetInit( $tsid, $tabname )
    {
        if( $tsid == 'main' ) {
            switch( $tabname ) {
                case 'Contacts':  $this->oW = new mbrContacts_Contacts( $this, $this->kfdb, $this->sess );  break;
                case 'Summary':   $this->oW = new mbrContacts_Summary( $this, $this->kfdb, $this->sess );  break;
                case 'Logins':    global $kfdb1; $this->oW = new mbrContacts_Logins( $this, $kfdb1, $this->kfdb, $this->sess );  break;
                case MBRCONTACTS_TABNAME_BULLETIN:  $this->oW = new mbrContacts_Bulletin( $this, $this->kfdb, $this->sess );  break;
            }
            if( $this->oW ) $this->oW->Init();
        }
    }

    function TabSetPermission( $tsid, $tabname )
    {
        global $raPerms;

        return( ($tsid == 'main' && is_array($ra = @$raPerms[$tabname]) && $this->sess->TestPermRA( $ra ))
                ? Console01::TABSET_PERM_SHOW
                : Console01::TABSET_PERM_GHOST );
    }

    function TabSetControlDraw( $tsid, $tabname )
    {
        if( $tsid == 'main' ) {
            switch( $tabname ) {
                case 'Contacts':
                    $s = "";
                    if( ($kMbr = ($this->oComp && $this->oComp->oForm ) ? $this->oComp->oForm->GetKey() : 0) ) {
                        $s .= "<div style='float:right'>"
                             ."<form action='https://seeds.ca/office/mbr/mbr_labels.php' target='MbrLabels' method='get'>"
                             ."<input type='hidden' name='mbradd' value='$kMbr'/><input type='submit' value='Add $kMbr to Label Maker'/></form></div>";
                    }
                    return( $s.$this->oComp->SearchToolDraw() );

                case 'Logins':    break;
                case MBRCONTACTS_TABNAME_BULLETIN:  return( $this->oW->ControlDraw() );
            }
        }
        return( "" );
    }

    function TabSetContentDraw( $tsid, $tabname )
    {
        if( $tsid == 'main' ) {
            switch( $tabname ) {
                case 'Contacts':  return( $this->CompListForm_Vert() );
                case 'Summary':   return( $this->oW->ContentDraw() );
                case 'Logins':    return( $this->oW->ContentDraw() );
                case MBRCONTACTS_TABNAME_BULLETIN:  return( $this->oW->ContentDraw() );
            }
        }
        return( "" );
    }

}



/*******************************************************/

class mbrContacts_Contacts extends Console01_Worker1
{
    function __construct( Console01 $oC, KeyFrameDB $kfdb, SEEDSessionAccount $sess )
    {
        parent::__construct( $oC, $kfdb, $sess );
    }

    function Init()
    {
        $kfrel = MbrContacts::KfrelBase( $this->kfdb, $this->sess->GetUID(), array("logfile" => SITE_LOG_ROOT."mbr_contacts.log"));
        $raCompParms = array(
            "Label" => "Contact",
            "ListCols" => array( array( "label"=>"Contact #",     "colalias"=>"_key",      "w"=>20 ),
                                 array( "label"=>"First name",    "colalias"=>"firstname", "w"=>150 ),
                                 array( "label"=>"Last name",     "colalias"=>"lastname",  "w"=>150 ),
                                 array( "label"=>"Company",       "colalias"=>"company",   "w"=>150, "trunc" => 30 ),
                                 array( "label"=>"Province",      "colalias"=>"province",  "w"=>150,   "colsel" => array() ),
                                 array( "label"=>"Expires",       "colalias"=>"expires",   "w"=>150,   "colsel" => array() ),
                               ),
            "ListSize" => 15,
            "ListSizePad" => false,
            "fnFormDraw" => array($this,"mbrContactsForm"),
            "fnListRowTranslateRA" => array($this,"mbrContactsListRowTranslateRA"),
            "bReadonly"=> !($this->sess->CanWrite( "MBR" )),
            'raSEEDFormParms' => array('DSParms'=>array('fn_DSPreStore'=>[$this,'dsPreStore'])),
            'fnPreDelete'=>[$this,'fnPreDelete']
        );

        $this->oC->CompInit( $kfrel, $raCompParms );
    }

    function dsPreStore( $oDS )
    {
        // bNoEBull is integer and cannot be '' ; causes an error on insert
        if( $oDS->Value('bNoEBull') != 1 ) $oDS->SetValue( 'bNoEBull', 0 );
        return( true );
    }

    function fnPreDelete( $kfr )
    {
        // Don't delete a contact if it's referenced in a table (return false to disallow delete)
        // This function only tests for fk rows with _status==0 because deletion causes the contact row to be _status=1 so
        // referential integrity is preserved if all related rows are "deleted"

        $bDelete = false;

        if( $kfr && $kfr->Key() ) {
            global $oApp;
            $ra = Mbr_WhereIsContactReferenced( $oApp, $kfr->Key() );

            $bDelete = true;
            $sErr = "";
            if( ($n = $ra['nSBBaskets']) )   { $sErr .= "<li>Has $n orders recorded in the order system</li>"; }
            if( ($n = $ra['nSProducts']) )   { $sErr .= "<li>Has $n offers in the seed exchange</li>"; }
            if( ($n = $ra['nDescSites']) )   { $sErr .= "<li>Has $n crop descriptions in their name</li>"; }
            if( ($n = $ra['nMSD']      ) )   { $sErr .= "<li>Is listed in the seed exchange</li>"; }
            if( ($n = $ra['nSLAdoptions']) ) { $sErr .= "<li>Has $n seed adoptions in their name</li>"; }
            if( ($n = $ra['nDonations']) )   { $sErr .= "<li>Has $n donation records in their name</li>"; }

            if( $sErr ) {
                $this->oC->ErrMsg( "Cannot delete contact {$kfr->Key()}:<br/><ul>$sErr</ul>" );
                $bDelete = false;
            }
        }
        return( $bDelete );
    }

    function mbrContactsForm( $oForm )
    {
        $raP = array( 'size' => 40 );
        $raPDisabled = array_merge( $raP, array( 'disabled'=>true ) );

        $s = "<DIV style='font-size:x-small;font-family:verdana,geneva,sans serif; margin-bottom:10px;'>The information in this database is private and confidential.<BR/>"
            ."It may only be used by Seeds of Diversity staff, and core volunteers.</DIV>"
            ."<TABLE border='0' cellpadding='0' cellspacing='0' width='90%' align='center'>"
            ."<TR valign='top'>"
            .$oForm->TextTD( '_key', "Contact #",
                             $this->sess->CanAdmin('MBR') ? array('sRightTail'=>" (Admin)", 'size'=>10 ) :
                                                            array('readonly'=>true) )
            ."<TD>&nbsp;</TD><TD align='center'><INPUT type='submit' value='Save'></TD>"
            ."</TR>"
            ."<TR valign='top'>"
            ."<TD>First / last name</TD><TD>".$oForm->Text( 'firstname', "", array( 'size'=>15 ) )
                ."&nbsp;&nbsp;".$oForm->Text( 'lastname', "", array( 'size'=>19 ) )."</TD>"
            .$oForm->TextTD( 'address', "Address", $raP )
            ."</TR>"
            ."<TR valign='top'>"
            ."<TD>First / last name 2</TD><TD>".$oForm->Text( 'firstname2', "", array( 'size'=>15 ) )
                ."&nbsp;&nbsp;".$oForm->Text( 'lastname2', "", array( 'size'=>19 ) )."</TD>"
            ."<TD>City</TD><TD>".$oForm->Text( 'city', "", $raP )
                    ." <A HREF='https://maps.google.ca/?q=".urlencode($oForm->ValueEnt('city')." ".$oForm->ValueEnt('province'))."' target='_blank'>Where's that?</A>"
                    ."</TD>"
            ."</TR>"
            ."<TR valign='top'>"
            .$oForm->TextTD( 'company', "Company", $raP )
            .$oForm->TextTD( 'province', "Province", $raP )
            ."</TR>"
            ."<TR valign='top'>"
            .$oForm->TextTD( 'dept', "Dept", $raP )
            .$oForm->TextTD( 'postcode', "Postcode", $raP )
            ."</TR>"
            ."<TR valign='top'>"
            .$oForm->TextTD( 'phone', "Phone", $raP )
//                "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".KFRForm_Text( $kfr, "Ext", 'ext', 5 )."</TD>";
            .$oForm->TextTD( 'country', "Country", $raP )
            ."</TR>"
            ."<TR valign='top'>"
            .$oForm->TextTD( 'email', "Email", $raP )
            ."</TR>"
            ."<TR valign='top'>"
            .$oForm->TextTD( 'lang', "Language", $raP )
            .$oForm->TextTD( 'status', "Status", $raPDisabled )
            ."</TR>"
            ."<TR valign='top'>"
            .$oForm->TextTD( 'referral', "Referral", $raP )
            .$oForm->TextTD( 'expires', "Expires", [] ) // $raPDisabled )
//if( $oForm->Value('expires') == '2100-01-01' ) $kfr->SetValue('expires','Automatic');  // Kluge to translate the coded date through the KFRForm function
//    echo        KFRForm_TextTD( $kfr, "Expires",      'expires', 40, "DISABLED" );
//if( $kfr->value('expires') == 'Automatic' )  $kfr->SetValue('expires','2100-01-01');
            ."</TR>"
            ."<TR valign='top'>"
            .$oForm->TextTD( 'bNoEBull', "No E-Bulletin", $raP )
            .$oForm->TextTD( 'startdate', "Start Date", $raPDisabled )
            ."</TR>"
            ."<TR valign='top'>"
            ."<TD>&nbsp;</TD><TD>&nbsp;</TD>"
            .$oForm->TextTD( 'lastrenew', "Lastrenew", [] ) // $raPDisabled )
            ."</TR>"
//            ."<tr><td colspan='2'>&nbsp;</td>"
//            .$oForm->TextTD( 'bNoSED', "Online MSD", $raP )
//            ."</tr>"
            ."<tr><td colspan='2'>&nbsp;</td>"
            .$oForm->TextTD( 'bPrintedMSD', "Printed MSD", $raP )
            ."</tr>"
            ."<TR valign='top'>"
            .$oForm->TextAreaTD( 'comment', "Comment", 35, 6 )
            ."</TR>"
            ."</TABLE>"
            ."<INPUT type='submit' value='Save'>";

        return( $s );
    }

    function mbrContactsListRowTranslateRA( $raValues )
    /**************************************************
     */
    {
	    if( ($l = MbrExpiryCode2Label( MbrExpiryDate2Code( $raValues['expires'] ) )) ) {
	        $raValues['expires'] = $l;
    	}

        return( $raValues );
    }
}


class mbrContacts_Logins extends Console01_Worker2
{
	public  $yCurrent;
    private $oMbrDB;
    private $eSection;

    private $oSessUGP;

    private $dbname1;
    private $dbname2;

    function __construct( Console01 $oC, KeyFrameDB $kfdb1, KeyFrameDB $kfdb2, SEEDSessionAccount $sess )
    {
        parent::__construct( $oC, $kfdb1, $kfdb2, $sess );

        $this->oSessUGP = new SEEDSessionAuthDB( $this->kfdb1, $this->sess->GetUID() );

        if( !($this->yCurrent = SEEDSafeGPC_GetInt("CurrentYear")) ) {
            //$this->yCurrent = date( "Y", time() + (3600*24*120) );      // the year of 120 days hence
            $this->yCurrent = date( "Y" );
        }

        $this->oMbrDB = new Mbr_DB( $kfdb2, $sess->GetUID(), $this->yCurrent );

        // Some array items require $this, and $this->yCurrent, so those have to exist before the array is created
        $this->setRaSections();

        // And the array has to be created before this happens
        if( !($this->eSection = SEEDSafeGPC_GetStrPlain( 'eSection' )) ) {
            // key of the first element
            reset($this->raSections);
            $this->eSection = key($this->raSections);
        }

        global $config_KFDB;
        $this->dbname1 = $config_KFDB['seeds1']['kfdbDatabase'];
        $this->dbname2 = $config_KFDB['seeds2']['kfdbDatabase'];
    }

    function Init()
    {
        /* Do Actions
         */
        $bDoAddLogin      = SEEDSafeGPC_GetStrPlain( 'action_addLogin' )      ? true : false;
        $bDoActivateLogin = SEEDSafeGPC_GetStrPlain( 'action_activateLogin' ) ? true : false;
        $bDoSendMSD       = SEEDSafeGPC_GetStrPlain( 'action_sendMSD' )       ? true : false;
        $bDoChangeEmail   = SEEDSafeGPC_GetStrPlain( 'action_chgEmail' )      ? true : false;
        $bRemoveMbrGroup  = SEEDSafeGPC_GetStrPlain( 'action_removeFromMbrGroup' ) ? true : false;
        $bDoMbrIndStatus    = SEEDSafeGPC_GetStrPlain( 'action_mbrIndStatus' ) ? true : false;


        $raMbr = array();
        foreach( $_REQUEST as $k => $v ) {
            if( substr( $k, 0, 4 ) == 'mbr_' && ($n = intval(substr($k,4)) ) ) { $raMbr[] = $n; }
        }
        if( ($n = SEEDSafeGPC_GetInt('kMbr')) && !in_array($n, $raMbr) )  { $raMbr[] = $n; }


        if( ($sAction = SEEDSafeGPC_GetStrPlain('action')) && isset($this->raSections[$sAction]) ) {
            $raS = $this->raSections[$sAction];

            if( @$raS['bActionEmails'] ) {
                foreach( $_REQUEST as $k => $v ) {
                    if( substr($k,0,6) == 'email_' && $v ) {
                        $sGood = $sBad = "";
                        if( call_user_func( $raS['actionFn'], $v ) ) {
                            $sGood .= " $v";
                        } else {
                            $sBad .= " $v";
                        }
                        if( $sGood ) $this->oC->UserMsg( $raS['outputGood'].$sGood );
                        if( $sBad )  $this->oC->ErrMsg( $raS['outputBad'].$sBad );
                    }
                }
            }
        }

        // Add Logins
        if( $bDoAddLogin && count($raMbr) ) {
            $sGood = $sBad = "";
            foreach( $raMbr as $n ) {
                if( $this->doAddLogin( $n ) ) {
                    $sGood .= " $n";
                } else {
                	$sBad .= " $n";
                }
            }
            if( $sGood ) $this->oC->UserMsg( "Added login for member $sGood<br/>" );
            if( $sBad )  $this->oC->ErrMsg( "Error adding login for member $sBad<br/>" );
        }

        // Activate Logins
        if( $bDoActivateLogin && count($raMbr) ) {
            $sGood = $sBad = "";
            foreach( $raMbr as $n ) {
                if( $this->doActivateLogin( $n ) ) {
                    $sGood .= " $n";
                } else {
                	$sBad .= " $n";
                }
            }
            if( $sGood ) $this->oC->UserMsg( "Activated login for member $sGood" );
            if( $sBad )  $this->oC->ErrMsg( "Error activating login for member $sBad" );
        }

        // Remove from member group
        if( $bRemoveMbrGroup && count($raMbr) ) {
            $sGood = $sBad = "";
            foreach( $raMbr as $n ) {
                if( $this->doRemoveMbrGroup( $n ) ) {
                    $sGood .= " $n";
                } else {
                	$sBad .= " $n";
                }
            }
            if( $sGood ) $this->oC->UserMsg( "Removed member privileges for $sGood" );
            if( $sBad )  $this->oC->ErrMsg( "Error removing member privileges for $sBad" );
        }

        // Change Email
        if( $bDoChangeEmail && count($raMbr) ) {
            $sGood = $sBad = "";
            foreach( $raMbr as $n ) {
                if( $this->doChangeEmail( $n ) ) {
                    $sGood .= " $n";
                } else {
                    $sBad .= " $n";
                }
            }
            if( $sGood ) $this->oC->UserMsg( "Changed email for member $sGood" );
            if( $sBad )  $this->oC->ErrMsg( "Error changing email for member $sBad" );
        }

        // Send MSD notice
        if( $bDoSendMSD && count($raMbr) ) {
            $sGood = $sBad = "";
            foreach( $raMbr as $n ) {
                if( $this->doSendMSD( $n ) ) {
                    $sGood .= " $n";
                } else {
                    $sBad .= " $n";
                }
            }
            if( $sGood ) $this->oC->UserMsg( "Sent MSD notice for member $sGood" );
            if( $sBad )  $this->oC->ErrMsg( "Error sending MSD notice for member $sBad" );
        }

        // Individual login status
        if( $bDoMbrIndStatus && count($raMbr) == 1 ) {
            $this->doMbrIndStatus( $raMbr[0] );
        }
    }

    function ContentDraw()
    {
        $s = "";

        $raAccounts = $this->oSessUGP->GetUsersFromGroup( 2 );

        // Get current members who don't have login accounts
        $raMbrNoLogin = $this->oMbrDB->GetMembersWithoutLogins();

        // Get current members who have login accounts, but don't have membership permissions or are inactive
        $raMbrNoPerm   = $this->oMbrDB->GetMembersWithLoginsButNoPerms();
        $raMbrNoActive = $this->oMbrDB->GetMembersWithLoginsButNotActive();

        // Get non-current members who have active login accounts with membership permissions
        $raAcctNoMbr   = $this->oMbrDB->GetAccountsWithPermsButNotCurrentMember();

        // Get accounts that have no corresponding membership record, ignoring those outside of the membership numerical range
        $raAcctOrphan  = $this->oMbrDB->GetAccountsWithNoMember( true );

        // Get accounts where email differs from membership email
        $raMbrAddrChange = $this->oMbrDB->GetAccountsWithDifferentEmail();

        // Get accounts for current members where the MSD notice is not current
        $raOldMSD = $this->oMbrDB->GetAccountsWithOldMSD();


        $s .= "<DIV style='border:1px solid #333;background-color:#eee;padding:0 10px;margin:0 0 10px 10px;font-family:verdana,sans serif;font-size:9pt;width:50%;float:right'>"
             ."<P>How this works</P>"
             ."<P style='margin-left:3em;'>The master list of members and their email addresses is uploaded to office.seeds.ca, where it is kept confidential.</P>"
             ."<P style='margin-left:3em;'>Member accounts and email addresses are stored on www.seeds.ca.</P>"
             ."<P style='margin-left:3em;'>This screen helps you keep the two sets synchronized, e.g. when emails change, new members join, expired members renew.</P>"
             ."</DIV>"

             ."<H4>Based on membership expiry of December {$this->yCurrent} or later</H4>"
             ."<P style='margin-left:3em'>There are "
             .$this->kfdb2->Query1( "SELECT count(*) FROM mbr_contacts WHERE year(expires)>='{$this->yCurrent}'" )
             ." current members in the member database on office.seeds.ca, and we have email addresses for "
             .$this->kfdb2->Query1( "SELECT count(*) FROM mbr_contacts WHERE year(expires)>='{$this->yCurrent}' AND email<>''" )
             ." of them.</P>"
             ."<P style='margin-left:3em'>There are ".count($raAccounts)." member user accounts on www.seeds.ca.</P>";


        $s .= "<table style='clear:both' border='0' cellpadding='10'><tr>"
             ."<td valign='top' width='40%'>"
             ."<p>Click on these boxes to see details and fix problems</p>"
             .$this->drawSectionA( 'mbrNoLogin', $raMbrNoLogin )
             .$this->drawSectionA( 'mbrExpired', $raMbrNoPerm )
             .$this->drawSectionA( 'mbrInactivated', $raMbrNoActive )
             .$this->drawSectionA( 'mbrNonMembersHavePerms', $raAcctNoMbr )
             .$this->drawSectionA( 'emailChanged', $raMbrAddrChange );

/*
             ."<div class='well'>";
        $n = count($raAcctNoMbr);
        $s .="<P".($n?" style='color:red'":"").">$n people have login accounts with membership permission but aren't current members (membership expired)</P>";
        if( $n ) {
            $s .= "<P>Ask Bob to fix this</P><DIV style='margin-top:2em;font-family:monospace;font-size:10pt'>";
            foreach( $raAcctNoMbr as $k => $ra ) {
                $s .= SEEDStd_ArrayExpand( $ra, "$k: [[name]]<BR/>[[email]]<BR/><BR/>" );
                            }
            $s .= "</DIV>";
        }
        $s .= "</div>";
*/
        $s .= $this->drawSectionA( 'mbrOrphan', $raAcctOrphan )
             .$this->drawSectionA( 'msdNotice', $raOldMSD )
             .$this->drawSectionA_SendMSDEmail()
             .$this->drawSectionA_MbrIndStatus()
             ."</td>"
             ."<td valign='top' width='40%'>";

        switch( $this->eSection ) {
            default:
            case 'mbrNoLogin':
                $s .= $this->drawSectionB( $this->eSection, $raMbrNoLogin );
                break;
            case 'mbrExpired':
                $s .= $this->drawSectionB( $this->eSection, $raMbrNoPerm );
                break;
            case 'mbrInactivated':
                $s .= $this->drawSectionB( $this->eSection, $raMbrNoActive );
                break;
            case 'mbrNonMembersHavePerms':
                $s .= $this->drawSectionB( $this->eSection, $raAcctNoMbr );
                break;
            case 'mbrOrphan':
                $s .= $this->drawSectionB( $this->eSection, $raAcctOrphan );
                break;
            case 'emailChanged':
                $s .= $this->drawSectionB( $this->eSection, $raMbrAddrChange );
                break;
            case 'msdNotice':
                $s .= $this->drawSectionB( $this->eSection, $raOldMSD );
                break;
            case 'sendMSDEmail':
                $s .= $this->drawSectionB_SendMSDEmail();
                break;
            case 'mbrIndStatus':
                $s .= $this->drawSectionB_MbrIndStatus();
                break;
/*
        $s .= "<TD><H4>Other Tools</H4>"
             ."<FORM method='post' action='${_SERVER['PHP_SELF']}'>"
             ."<P style='font-size:8pt'>Enter a member number. The Add Login, Send MSD Notice, and Change Email buttons will act on this member.</P>"
             ."<INPUT type='text' name='kMbr'/><BR/><BR/>"
             ."</FORM>"
             ."<BR/><BR/>"
             ."</TD>";
*/
        }
        $s .= "</td>"
             ."</tr></table>";

        return( $s );
    }

    private $raSections;
    private function setRaSections() {
        // Because $this, and $this->yCurrent can't be set in the array until after construction

        $this->raSections = array(

        'mbrNoLogin' => array( 'heading' => "Members Without Logins (New members)",
                               'desc'    => "current members do not have login accounts",
                               'do'      => "create login accounts",
                               'action'  => "addLogin",
                               'button'  => "Create Selected Login Accounts",
                               'expand'  => "" ),
        'mbrExpired' => array( 'heading' => "Members With Expired Logins (renewed recently)",
                               'desc'    => "current members have login accounts but not membership permission on the web site",
                               'do'      => "reactivate login accounts",
                               'action'  => "activateLogin",
                               'button'  => "Activate Selected Login Accounts",
                               'expand'  => "" ),
        'mbrInactivated' => array(
                               'heading' => "Members With Inactive Logins",
                               'desc'    => "members have inactivated login accounts",
                               'do'      => "reactivate login accounts",
                               'action'  => "activateLogin",
                               'button'  => "Activate Selected Login Accounts",
                               'expand'  => "" ),

        'mbrNonMembersHavePerms' => array(
                               'heading' => "Non-members With Member Privileges",
                               'desc'    => "non-members have member privileges (probably expired memberships)",
                               'do'      => "remove member privileges",
                               'action'  => "removeFromMbrGroup",
                               'button'  => "Remove Member Privileges",
                               'expand'  => "" ),

        'mbrOrphan' => array(  'heading' => "Logins Without Member Records",
                               'desc'    => "people have login accounts but no record in the membership database",
                               'do'      => "",
                               'action'  => "",
                               'button'  => "",
                               'expand'  => "" ),

        'emailChanged' => array(
                               'heading' => "Email Address Changed",
                               'desc'    => "current members have different email addresses in the member database and their login account. Probably they changed their email address recently.",
                               'do'      => "update login account emails",
                               'action'  => "chgEmail",
                               'button'  => "Change Email",
                               'expand'  => "<BR/>Contacts &lt;<b>[[email]]</b>&gt;<br/>Login &lt;<b>[[U_email]]</b>&gt;" ),

        'msdNotice' => array(
                               'heading' => "Seed Directory Notice Not Sent",
                               'desc'    => "current members have not received a Seed Directory notice during {$this->yCurrent}",
                               'do'      => "send MSD notices",
                               'action'  => "sendMSD",
                               'button'  => "Send MSD Notice",
                               'expand'  => "" ),

        // implemented with separate methods
        'sendMSDEmail' => array(
                               'heading' => "Send the Seed Directory to somebody",
                               'do'      => "send MSD notices",
                               'button'  => "Send MSD Notice",
                               'bActionEmails' => true,
                               'action'  => "sendMSDEmail",
                               'actionFn' => array($this,'doSendMSDEmail'),
                               'outputGood' => "",//"Sent MSD notice for email",    doSendMSDEmail uses UserMsg
                               'outputBad'  => "Error sending MSD notice for email",
        ),
        'mbrIndStatus' => array(
                               'heading' => "Individual status of a login",
                               'do'      => "Status of login",
                               'action'  => "mbrIndStatus",
                               'button'  => "Status of login",
                               'outputGood' => "Good!",
                               'outputBad'  => "Bad!" ),
        );
    }

    function drawSectionA( $eSection, $raTest )
    {
        $raS = $this->raSections[$eSection];
        $n = count($raTest);

        $sClass = "alert ";
        $sAttrs = "";

        // Colour the alert green or red based on the test result
        if( count($raTest) ) {
            $sClass .= " alert-danger";
            $sBorder = "#f88";
        } else {
            $sClass .= " alert-success";
            $sBorder = "green";
        }

        // if this is the current section, make it stand out
        // else the div is a link to make it the current section
        if( $eSection == $this->eSection ) {
            $sAttrs = "style='padding:3px;margin:3px;font-weight:bold;border:2px solid $sBorder;'";
        } else {
            $sClass .= " small";
            $sAttrs = "style='padding:3px;margin:3px;cursor:pointer' onclick='location.replace(\"?eSection=$eSection\")'";
        }

        $s =  "<div class='$sClass' $sAttrs>"
             ."<p>$n ${raS['desc']}</p>"
             ."</div>";
        return( $s );
    }

// TODO: simplify action http values
//       why did it say I didn't have member perms when my expiry was 2015
//       why did it say it couldn't activate robdom
//       action to cancel membership permission for old members
//       move more code to mbrDB.php

    function drawSectionB( $eSection, $raTest )
    {
        $raS = $this->raSections[$eSection];

        $sExpand = @$raS['expand'] ? $raS['expand'] : "[[name]]<br/>[[email]]";

        $n = count($raTest);
        $s = "<h4>${raS['heading']}</h4>";
        $s .= "<p".($n?" style='color:red'":"").">$n ${raS['desc']}</p>";
        if( $n ) {
            if( $raS['action'] ) {
                // some sections have no automatable action, so those show the list but no buttons
                $s .= "<form method='post' action='${_SERVER['PHP_SELF']}'>"
                     .SEEDForm_Hidden( 'eSection', $this->eSection )
                     .SEEDForm_Hidden( 'action', $this->eSection )
                     ."<div>"
                     ."<input style='display:inline' type='submit' name='action_${raS['action']}' value='${raS['button']}'/> "
                     ."<span style='font-size:8pt;margin:5px 0 0 30px'>Click here to ${raS['do']} for members selected below.</span>"
                     ."</div>"

                     ."<div style='margin-top:20px'>"
                     ."<input style='display:inline' type='button' id='checkall_${raS['action']}' value='Select All Below'/>"
                     ."<span style='font-size:8pt;margin:5px 0 0 30px'>Click here to select all the accounts below.</span>"
                     ."</div>"

                     ."<script>"
                     ."$('#checkall_${raS['action']}').click(function(){"
                     ."  $('#mbr_${raS['action']} :checkbox').prop('checked',true);"
                     ."});"
                     ."</script>";
            } else {
                $s .= "<p style='color:red'>Ask Bob to fix this</p>";
            }
            $s .= "<DIV id='mbr_${raS['action']}' style='clear:both;margin-top:2em;font-family:monospace;font-size:10pt'>"
             ."<TABLE border='0' cellspacing='3' cellpadding='3'>";
            foreach( $raTest as $k => $ra ) {
                 $s .= "<TR>"
                      ."<TD valign='top'>".SEEDForm_Checkbox( "mbr_$k", 0, "" )."</TD>"
                      ."<TD valign='top'>".SEEDStd_ArrayExpand( $ra, "$k: $sExpand" )."</TD>"
                      ."</TR>";
            }
            $s .= "</TABLE></DIV></FORM>";
        }
        return( $s );
    }

    private function drawSectionA_SendMSDEmail()
    /*******************************************
        Draw the left-hand tab that lets you send a MSD notice to some arbitrary email addresses
     */
    {
        $eSection = "sendMSDEmail";
        $raS = $this->raSections[$eSection];

        $sClass = "well";
        $sBorder = "black";
        if( $eSection == $this->eSection ) {
            $sAttrs = "style='padding:3px;margin:3px;font-weight:bold;border:2px solid $sBorder;'";
        } else {
            $sClass .= " small";
            $sAttrs = "style='padding:3px;margin:3px;cursor:pointer' onclick='location.replace(\"?eSection=$eSection\")'";
        }

        $s =  "<div class='$sClass' $sAttrs>"
             ."<p>{$raS['heading']}</p>"
             ."</div>";

        return( $s );
    }

    private function drawSectionB_SendMSDEmail()
    /*******************************************
        Draw the right-hand form that lets you send a MSD notice to some arbitrary email addresses
     */
    {
        $eSection = "sendMSDEmail";
        $raS = $this->raSections[$eSection];

        $s = "<style> .loginsCtlEmail { margin:15px 0px 0px 20px } </style>";

        $s .= "<h4>${raS['heading']}</h4>"
             ."<form method='post' action='${_SERVER['PHP_SELF']}'>"
             .SEEDForm_Hidden( 'eSection', $this->eSection )
             .SEEDForm_Hidden( 'action', $this->eSection )
             ."<div>"
             ."<input type='submit' name='action_${raS['action']}' value='${raS['button']}'/> "
             ."<p style='font-size:8pt;margin:5px 0 0 30px'>Enter emails below, then click here to ${raS['do']}.</p>"
             ."</div>"
             ."<div class='loginsCtlEmail'>".SEEDForm_Text( "email_1", "","" )."</div>"
             ."<div class='loginsCtlEmail'>".SEEDForm_Text( "email_2", "","" )."</div>"
             ."<div class='loginsCtlEmail'>".SEEDForm_Text( "email_3", "","" )."</div>"
             ."<div class='loginsCtlEmail'>".SEEDForm_Text( "email_4", "","" )."</div>"
             ."<div class='loginsCtlEmail'>".SEEDForm_Text( "email_5", "","" )."</div>"
             ."<div class='loginsCtlEmail'>".SEEDForm_Text( "email_6", "","" )."</div>";

        return( $s );
    }

    private function drawSectionA_MbrIndStatus()
    /*******************************************
        Draw the left-hand tab that lets you send a MSD notice to some arbitrary email addresses
     */
    {
        $eSection = "mbrIndStatus";
        $raS = $this->raSections[$eSection];

        $sClass = "well";
        $sBorder = "black";
        if( $eSection == $this->eSection ) {
            $sAttrs = "style='padding:3px;margin:3px;font-weight:bold;border:2px solid $sBorder;'";
        } else {
            $sClass .= " small";
            $sAttrs = "style='padding:3px;margin:3px;cursor:pointer' onclick='location.replace(\"?eSection=$eSection\")'";
        }

        $s =  "<div class='$sClass' $sAttrs>"
             ."<p>{$raS['heading']}</p>"
             ."</div>";

        return( $s );
    }

    private function drawSectionB_MbrIndStatus()
    /*******************************************
        Draw the right-hand form that lets you send a MSD notice to some arbitrary email addresses
     */
    {
        $eSection = "mbrIndStatus";
        $raS = $this->raSections[$eSection];

        $s = "<style> .loginsCtlEmail { margin:15px 0px 0px 20px } </style>";

        $s .= "<h4>${raS['heading']}</h4>"
             ."<form method='post' action='${_SERVER['PHP_SELF']}'>"
             .SEEDForm_Hidden( 'eSection', $this->eSection )
             .SEEDForm_Hidden( 'action', $this->eSection )
             ."<div>"
             ."<input type='submit' name='action_${raS['action']}' value='${raS['button']}'/> "
             ."<p style='font-size:8pt;margin:5px 0 0 30px'>Enter contact #.</p>"
             ."</div>"
             ."<div class='loginsCtlEmail'>".SEEDForm_Text( "kMbr", "","" )."</div>"
             ."</form>";

        if( ($kMbr = SEEDSafeGPC_GetInt('kMbr')) ) {
            $s .= $this->getMbrIndStatus( $kMbr );

            $s .= "<form method='post' action='${_SERVER['PHP_SELF']}'>"
                 .SEEDForm_Hidden( 'eSection', $this->eSection )
                 .SEEDForm_Hidden( 'kMbr', $kMbr )
                 .SEEDForm_Hidden( 'localaction_deactivate', 1 )
                 ."<input type='submit' value='Deactivate $kMbr'/>"
                 ."</form>";

            if( SEEDSafeGPC_GetInt('localaction_deactivate') ) {
// TODO: use DeactivateLogin -- no it just sets INACTIVE, we actually want to delete it
//                if( $this->oMbrDB->DeactivateLogin( $kMbr ) ) {
                $this->kfdb2->Execute( "UPDATE {$this->dbname1}.SEEDSession_Users SET eStatus='INACTIVE' WHERE _key='$kMbr'" );
                $this->kfdb2->Execute( "UPDATE {$this->dbname1}.SEEDSession_Users SET _status=1 WHERE _key='$kMbr'" );
                    $s .= "<p>$kMbr deactivated</p>";
                //}
            }
        }

        return( $s );
    }

    function validate( $kMbr, $sTests )
    {
        $raMbr = $this->kfdb2->QueryRA( "SELECT * FROM mbr_contacts WHERE _key='$kMbr' AND _status='0'" );

        $raTests = explode( " ", $sTests );
        foreach( $raTests as $test ) {
            switch( $test ) {
                case "AccountExists":
                    if( !$this->kfdb1->Query1( "SELECT _key FROM {$this->dbname1}.SEEDSession_Users WHERE _key='$kMbr'" ) ) {
                        $this->oC->ErrMsg( "Member $kMbr does not have a login account." );
                        return( false );
                    }
                    break;

                case "CurrentMember":
                    if( intval(substr(@$raMbr['expires'],0,4)) < $this->yCurrent ) {
                        $this->oC->ErrMsg( "Contact # $kMbr is not a current member" );
                        return( false );
                    }
                    break;

                case "EmailExists":
                    if( empty($raMbr['email']) ) {
                        $this->oC->ErrMsg( "Contact # $kMbr does not have an email address in the contact database" );
                        return( false );
                    }
                    break;

                default:
                    die( "Unknown validation code $test" );
            }
        }

        return( $raMbr );
    }

    function doAddLogin( $kMbr )
    {
        list($bOk,$sErr) = $this->oMbrDB->CreateLoginFromContact( $kMbr );
        $this->oC->ErrMsg($sErr);

        return( $bOk );
    }

    function doActivateLogin( $kMbr )
    {
        list($bOk,$sErr) = $this->oMbrDB->ActivateLogin( $kMbr );
        $this->oC->ErrMsg($sErr);

        if( $bOk ) {
            // Make sure this user has Member permission
            list($bOk,$sErr) = $this->oMbrDB->AddToMembersGroup( $kMbr );
            $this->oC->ErrMsg($sErr);
        }

        // If the user isn't in group 2, add them to group 2
        //SEEDSessionAuthStatic::Init( $this->kfdb1, $this->sess->GetUID() );
        //$bOk = SEEDSessionAuthStatic::AddUserToGroup( $kMbr, 2 );

        return( $bOk );
    }

    function doRemoveMbrGroup( $kMbr )
    {
        // kMbr has an active login with member privileges, but is not a current member.
        // Remove from member Group
        if( ($raMbr = $this->validate( $kMbr, "AccountExists" )) === false ) {
            return( false );
        }

        // Remove the user from group 2 (this is safe if it isn't in group 2)
        SEEDSessionAuthStatic::Init( $this->kfdb1, $this->sess->GetUID() );
        $bOk = SEEDSessionAuthStatic::RemoveUserFromGroup( $kMbr, 2 );

        return( $bOk );
    }

    function doChangeEmail( $kMbr )
    {
        if( ($raMbr = $this->validate( $kMbr, "AccountExists EmailExists" )) === false ) {
            return( false );
        }

        $bOk = $this->kfdb1->Execute( "UPDATE {$this->dbname1}.SEEDSession_Users SET email='".addslashes($raMbr['email'])."' WHERE _key='$kMbr'" );
        if( !$bOk ) {
            $this->oC->ErrMsg( "Database error updating email for member $kMbr : ".$this->kfdb1->GetErrMsg() );
        }

        return( $bOk );
    }

    function doSendMSD( $kMbr )
    {
        $bOk = false;

        if( ($raMbr = $this->validate( $kMbr, "CurrentMember AccountExists EmailExists" )) === false ) {
            return( false );
        }
        $sEmail1 = $this->kfdb1->Query1( "SELECT email FROM {$this->dbname1}.SEEDSession_Users WHERE _key='$kMbr'" );
        if( empty($sEmail1) ) {
            $this->oC->ErrMsg( "Member # $kMbr does not have an email address in their login account" );
            return( false );
        }

        return( $this->doSendMSDEmail( $sEmail1, $kMbr ) );
    }

    function doSendMSDEmail( $sEmail1, $kMbr = 0 )
    {
        if( !$kMbr ) {
            $edb = addslashes($sEmail1);
            // Sending an MSD email to an arbitrary email. If we know who it is, that's helpful.
            // Might not be a real contact; it's okay if it's zero after this.
            if( !($kMbr = $this->kfdb1->Query1( "SELECT _key FROM seeds_2.mbr_contacts WHERE email='$edb'")) ) {
                $kMbr = $this->kfdb1->Query1( "SELECT _key FROM {$this->dbname1}.SEEDSession_Users WHERE email='$edb'");
            }
        }

        include_once( SEEDLIB."mail/SEEDMail.php" );
        $oApp = SEEDConfig_NewAppConsole_LoginNotRequired( ['db'=>'seeds2'] );   // anonymous access
        $oMail = new SEEDMail($oApp, 'MSDLoginInstructions');
        $oMail->AddRecipient($sEmail1);
        $oMail->StageMail();

        $bOk = true;

/*
        $sSubject = "Your Member Seed Directory is on-line! - Votre Catalogue de semences est disponible par Internet!";

        $oMail = new mbr_mail( $this->kfdb1, $this->kfdb2, 1499 );    // USING Bob's perms to make sure we can see the document in DocRep
        $sDoc = $oMail->DrawMail( 368, 'office', $sEmail1, $kMbr, $sSubject, array() );
        $sDoc = trim($sDoc);
        if( empty($sDoc) ) {
            $this->oC->ErrMsg( "Error drafting email for $kMbr : $sEmail1. " );
            return( false );
        }

        $bOk = true; // comment out the line below for testing
        $bOk = (MailFromOffice( $sEmail1, $sSubject, "", $sDoc, array( "from"=>array("eBulletin@seeds.ca") ) ) == 1);

        if( $bOk ) {
            MailFromOffice( "bob@seeds.ca", "$sSubject -- $sEmail1", "", $sDoc, array( "from"=>array("eBulletin@seeds.ca") ) );
        }
*/


        if( $bOk ) {
            if( $kMbr ) {
                $this->oSessUGP->SetUserMetadata( $kMbr, 'dSentMSD', date('Y-m-d') );
            }
            $this->oC->UserMsg( "Staged Member Seed Directory email to $kMbr : $sEmail1<br/>" );
         // don't show my password here            ."<DIV style='border:1px solid black'>$sDoc</DIV>" );
        } else {
            $this->oC->ErrMsg( "Error mailing Seed Directory email to $kMbr : $sEmail1" );
        }

        return( $bOk );
    }

    function doMbrIndStatus( $kMbr )
    {
        $bOk = true;

        // this is shown in sectionB
        //$this->oC->UserMsg( $this->getMbrIndStatus() );

        return( $bOk );
    }

    function getMbrIndStatus( $kMbr )
    {
        $sCUCond = "_created_by='$kMbr' OR _updated_by='$kMbr'";
        $raDBTables = array( 'mbr_sites',
                             'sl_desc_obs',
                             'sl_varinst',
                             'sl_collection',
                             'sl_accession',
                             'sl_inventory',
         );

        $s = "Checking login # $kMbr.<br/>";
        $s .= $this->kfdb2->Query1( "SELECT count(*) FROM {$this->dbname1}.sed_curr_growers WHERE $sCUCond OR mbr_id='$kMbr'" )." sed_curr_growers<br/>";
        $s .= $this->kfdb2->Query1( "SELECT count(*) FROM {$this->dbname1}.sed_curr_seeds   WHERE $sCUCond OR mbr_id='$kMbr'" )." sed_curr_seeds<br/>";

        foreach( $raDBTables as $t ) {
            $s .= $this->kfdb2->Query1( "SELECT count(*) FROM {$this->dbname1}.$t WHERE $sCUCond" )." $t<br/>";
        }

        return( $s );
    }
}


class mbrContacts_Summary extends Console01_Worker1
{
    function __construct( Console01 $oC, KeyFrameDB $kfdb2, SEEDSession $sess )
    {
        parent::__construct( $oC, $kfdb2, $sess );
    }

    function Init()
    {
    }

    function ContentDraw()
    {
        $s = "";

        $s .= "<style>"
             .".mbr_address {  }"
             .".summaryWindow1 { float:right;border:1px solid #aaa;padding:10px;width:50% }"
             .".summaryWindowMbr { background-color:#eee; margin-bottom:20px; border-radius:10px;padding:10px; }"
             .".summaryWindowMbr1 { display:inline-block; vertical-align:top; margin-right:20px; }"
             ."</style>";


        $s .= $this->summaryWindow();

        $s .= "<p>There are ".$this->kfdb->Query1( "SELECT count(*) FROM seeds_2.mbr_contacts WHERE _status='0'" )." people in the Contacts database.</p>";
        $s .= "<p>There are ".$this->kfdb->Query1( "SELECT count(*) FROM seeds_1.SEEDSession_Users WHERE _status='0'" )." Logins for members and non-members.</p>";
        $s .= "<p>&nbsp;</p>";


        /* Full Outer Join of mbr_contacts and SEEDSession_Users
         */
        $this->kfdb->Execute( "CREATE TEMPORARY TABLE seeds_2.MbrSummary ( km INTEGER, ku INTEGER, email_m TEXT, email_u TEXT, yExpires INTEGER )" );


        $this->kfdb->Execute(
            "INSERT INTO seeds_2.MbrSummary (km,ku,email_m,email_u,yExpires) "
           ."SELECT M._key,U._key,M.email,U.email,year(M.expires) "
            //."FROM seeds_2.mbr_contacts M FULL OUTER JOIN seeds_1.SEEDSession_Users U ON (M._key=U._key) "
            // Because mysql doesn't have full joins, this is the same thing iff there are no rows that
            // are full duplicates (otherwise it seems you can use UNION ALL to preserve duplicate rows)
           ."FROM seeds_2.mbr_contacts M LEFT JOIN seeds_1.SEEDSession_Users U ON (M._key=U._key) "
           ."UNION "
           ."SELECT M._key,U._key,M.email,U.email,year(M.expires) "
           ."FROM seeds_2.mbr_contacts M RIGHT JOIN seeds_1.SEEDSession_Users U ON (M._key=U._key) "
        );
        $raRows = $this->kfdb->QueryRowsRA( "SELECT * from seeds_2.MbrSummary" );

        $s .= "<h4>Contacts Database and User Logins</h4>"
             ."<p>There are ".count($raRows)." combined rows.</p>";


        /* Duplicate emails in mbr_contacts
         */
        $raRows = $this->kfdb->QueryRowsRA( "SELECT M1.email as email,M1._key as k1,M2._key as k2 FROM mbr_contacts M1,mbr_contacts M2 "
                                           ."WHERE M1._status='0' and M2._status='0' AND "
                                           ."M1._key < M2._key AND M1.email=M2.email AND "
                                           ."M1.email <> '' AND M1.email is not null" );
        $s .= "<h4>Duplicate emails in Contacts database</h4>"
             ."<p>There are ".count($raRows)." duplicate emails in the Contacts database.</p>";
        if( count($raRows) ) {
            $s .= "<table class='table-striped'>";
            foreach( $raRows as $ra ) {
                $s .= "<tr><td><a href='".Site_path_self()."?cmd=dupemail&k1={$ra['k1']}&k2={$ra['k2']}'>{$ra['email']}</a></td><td>{$ra['k1']}</td><td>{$ra['k2']}</td></tr>";
            }
            $s .= "</table>";
        }

        return( $s );
    }

    private function summaryWindow()
    {
        $s = $sOut = "";

        switch( SEEDSafeGPC_GetStrPlain('cmd') ) {
            case 'dupemail':
                $k1 = SEEDSafeGPC_GetInt('k1');
                $k2 = SEEDSafeGPC_GetInt('k2');
                $s .= $this->drawMbr( $k1 );
                $s .= $this->drawMbr( $k2 );
                break;

        }

        if( $s ) {
            $sOut = "<div class='summaryWindow1'>$s</div>";
        }
        return( $sOut );
    }

    private function drawMbr( $k )
    {
        $oMbr = new MbrContacts( $this->kfdb );
        $oMbr->SetKMbr( $k );
        $kfr = $oMbr->GetKFRByKey( $k );
        if( !$kfr ) return( "" );

        $s = "<div class='summaryWindowMbr'>"
                ."<div class='summaryWindowMbr1'>".$oMbr->DrawAddressBlock( array( 'bPhone'=>true, 'bEmail'=>true ) )."</div>"
                .$kfr->Expand( "<div class='summaryWindowMbr1'>"
                              ."# [[_key]]<br/>"
                              ."Expiry: [[expires]]<br/>"
                              ."Start: [[startdate]]<br/>"
                              ."Renewed: [[lastrenew]]<br/>"
                              ."Lang: [[lang]]<br/>"
                              ."</div>" )
                ."<div class='summaryWindowMbr1'>".$kfr->Value('comment')."</div>"
            ."</div>";

        return( $s );
    }

}

class mbrContacts_Bulletin extends Console01_Worker1
{
    private $sOut = "";
    private $raEmails = array( array( 'name'=>'', 'email'=>'', 'language'=>'E', 'comment'=>''),
                               array( 'name'=>'', 'email'=>'', 'language'=>'E', 'comment'=>''),
                               array( 'name'=>'', 'email'=>'', 'language'=>'E', 'comment'=>''),
                               array( 'name'=>'', 'email'=>'', 'language'=>'E', 'comment'=>''),
                               array( 'name'=>'', 'email'=>'', 'language'=>'E', 'comment'=>'') );

    // uploaded spreadsheets must have these columns
    private $seedTableDef = array( 'headers-required' => array('email','language'),
                                   'headers-optional' => array('name','comments') );

    function __construct( Console01 $oC, KeyFrameDB $kfdb2, SEEDSessionAccount $sess )
    {
//        include_once( STDINC."SEEDTable.php" );

        parent::__construct( $oC, $kfdb2, $sess );

        $this->oApp = SEEDConfig_NewAppConsole_LoginNotRequired( ['db'=>'seeds2'] );
        $this->oEbull = new MbrUIEbulletin( $this->oApp );
    }

    function Init()
    {
        if( ($action = SEEDInput_Str('action')) ) {
            list($s,$raEmails) = $this->oEbull->DoAction( $action );
            $this->sOut .= $s;
            if( $raEmails !== null ) $this->raEmails = $raEmails;
        }
        $this->oC->UserMsg($this->oApp->oC->GetUserMsg());
        $this->oC->ErrMsg($this->oApp->oC->GetErrMsg());      }

    function ControlDraw()
    {
        $s = "<form method='post'>"
            ."<input type='hidden' name='bSwapNameEmail' value='1'/>"
            ."<button style='font-size:10pt' onclick='submit()'>Switch Email and Name Columns</button></form>";

        return( $s );
    }

    function ContentDraw()
    {
        $s = "";

        // The Control area has a button that allows Email and Name columns to be switched.
        // Keep this setting in the tab's SVA.
        $oSVA = $this->oC->TabSetGetSVA( 'main', MBRCONTACTS_TABNAME_BULLETIN );
        if( !($sOrderNameEmail = $oSVA->VarGet( 'sOrderNameEmail' )) ) {
            $sOrderNameEmail = "E-N";    // default Email - Name
            $oSVA->VarSet( 'sOrderNameEmail', $sOrderNameEmail );
        }
        if( SEEDSafeGPC_GetInt('bSwapNameEmail') ) {
            // user clicked the <- Switch -> button
            $sOrderNameEmail = ($sOrderNameEmail == 'E-N') ? "N-E" : "E-N";
            $oSVA->VarSet( 'sOrderNameEmail', $sOrderNameEmail );
        }
        $bEmailColFirst = ($sOrderNameEmail == 'E-N');

        //$oTable = new SEEDTable( $this->seedTableDef );

        $sInstructions =
              "<div class='console01_instructions' style=''>"
             ."<p><b>To Add</b> to the e-Bulletin subscriber list, enter Email and Language below (Names are optional) and click Add</p>"
             ."<p><b>To Delete</b> from the e-Bulletin subscriber list, enter the Email below (Names and Language don't matter) and click Delete</p>"
             ."<p><b>To Upload</b> a spreadsheet file:</p>"
             ."<div style='margin:0 0 10px 30px'>"
             ."<ul>"
             ."<li>The first row of the spreadsheet must have these names (in any order).".SEEDTableSheets::SampleHead($this->seedTableDef)."</li>"
             ."<li>The values in the <b>language</b> column can be E, F, or B (for English, French, Both/bilingual).</li>"
             ."<li>Instead of <B>name</B> you can have <B>first name</B> and <B>last name</B> columns if you like. (There's no advantage: they will just be joined to make the name).</li>"
             ."<li>The Upload button just puts the information from the file into the form. Check that it looks right, then click the Add button.</li>"
             ."</ul>"
             ."</div>"
             ."<p>Don't worry about duplicates; we screen for them.</p>"
             ."</div>";

        $thE = "Email";
        $thN = "Name<br/><span style='font-size:8pt'>(optional, not needed for delete)</span>";

        $s .= "<table border='0'><tr valign='top'>"
             ."<td style='padding-right:2em;'>"
             ."<form action='${_SERVER['PHP_SELF']}' method='post'>"
             ."<table border='0'>"
             ."<tr valign='top'>"
                 ."<th>".($bEmailColFirst ? $thE : $thN)." </th>"
                 ."<th>".($bEmailColFirst ? $thN : $thE)."</th><th>Language</th><th>Comment</th></tr>";

        $i = 1;
        foreach( $this->raEmails as $raE ) {
            $sE = SEEDForm_Text( "e$i", $raE['email'], "", 25 );
            $sN = SEEDForm_Text( "n$i", $raE['name'], "", 25 );

            $s .= "<tr><td>"
                 .($bEmailColFirst ? $sE : $sN)
                 ."</td><td>"
                 .($bEmailColFirst ? $sN : $sE)
                 ."</td><td>"
                 .SEEDForm_Select2( "l$i", array("English"=>'E',"French"=>'F',"Bilingual"=>'B'), $raE['language'] )
                 ."</td><td>"
                 .SEEDForm_Text( "c$i", $raE['comment'], "", 25 )
                 ."</td></tr>";
            ++$i;
        }
        $s .= "</table>"
             ."<p style='margin-top:20px'><input type='submit' name='action' value='Add'/>&nbsp;&nbsp;&nbsp;<input type='submit' name='action' value='Delete'/></p>"
             ."</form>";

        $s .= "<div style='border:1px solid #aaa;padding:10px;margin:20px'>You can upload a spreadsheet into the form above, and then Add. See instructions to the right."
             ."<form action='${_SERVER['PHP_SELF']}' method='post' enctype='multipart/form-data'>"
             ."<input type='hidden' name='MAX_FILE_SIZE' value='10000000' />"
             ."<input style='display:inline' type='file' name='upfile'/>&nbsp;<input style='display:inline' type='submit' name='action' value='Upload'/>"
             ."</form></div>";

        $s .= "</td><td style='border-left:1px solid grey;padding-left:2em;'>";

        $s .= "<p>".$this->sOut."</p><hr/>"
             ."<p>There are "
             .$this->kfdb->Query1( "SELECT count(*) FROM seeds_1.bull_list WHERE status='1'" )
             ." subscribers in the ebulletin list.</p>"
             .$sInstructions;

        $s .= "</td></tr></table>";

        return( $s );
    }
}


$raConsoleParms = array(
    'HEADER' => "Seeds of Diversity Contact Database",
    'CONSOLE_NAME' => "mbrContacts",
    'TABSETS' => array( "main" => array( 'tabs'=> array( "Contacts" => array('label' => "Contacts" ),
                                                         "Summary"  => array('label' => "Summary" ),
                                                         "Logins"   => array('label' => "Logins" ),
                                                         MBRCONTACTS_TABNAME_BULLETIN => array('label' => "Bulletin" ) ) ) ),
    'bLogo' => true,
    'bBootstrap' => true,
);

$oC = new MyConsole( $kfdb2, $sess, $raConsoleParms );

echo $oC->DrawConsole( "[[TabSet: main]]" );

?>
