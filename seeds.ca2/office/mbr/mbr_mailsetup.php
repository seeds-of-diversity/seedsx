<?php

/* mbr_mailsetup.php
 *
 * Copyright 2010-2017 Seeds of Diversity Canada
 *
 * Prepare mail to be sent to members / donors / subscribers.
 * Use mbr_mailsend to send the mail.
 */
if( !defined("SITEROOT") )  define("SITEROOT", "../../");
include_once( SITEROOT."site2.php" );
include_once( STDINC."KeyFrame/KFUIForm.php" );
//include_once( SEEDCORE."SEEDPerms.php" );
include_once( SEEDCOMMON."console/console01.php" );
include_once( SEEDCOMMON."doc/docUtil.php" );
include_once( "_mbr_mail.php" );

list($kfdb2,$sess) = SiteStartSessionAccount(array('W MBRMAIL'));
$kfdb1 = SiteKFDB( SiteKFDB_DB_seeds1 ) or die( "Cannot connect to database" );

//$kfdb2->SetDebug(2);
//var_dump($_REQUEST);
//var_dump($_SESSION);
//$_SESSION=array();  // sometimes something is too hard for the server and you have to get it out of whatever state it's trying to be in


class MyConsole extends Console01
{
    public $oMS;

    function __construct( KeyFrameDB $kfdb1, KeyFrameDB $kfdb2, SEEDSessionAccount $sess, $raParms )
    {
        parent::__construct( $kfdb2, $sess, $raParms );
        $this->oMS = new mail_setup( $this, $kfdb1, $kfdb2, $sess );
        $this->oMS->DoAction();
    }

    function TabSetGetCurrentTab( $tsid )
    {
        // if the user clicked "Create a New Message", make Mail Item the current tab
        if( $this->oMS->IsCreate() ) {
            return( "Mail-Item" );
        }
        return( parent::TabSetGetCurrentTab( $tsid ) );
    }

    function TabSetContentDraw( $tsid, $tabname )
    {
        switch( $tabname ) {
            case "Text":      return( $this->oMS->drawText() );
            case "Controls":  return( $this->oMS->drawControls() );
            case "Delete":    return( $this->oMS->drawDelete() );
            case "Mail-Item":
            default:          return( $this->oMS->drawMailItem() );
        }
    }

    function TabSetPermission( $tsid, $tabname )
    {
        $ePerm = Console01::TABSET_PERM_SHOW;
        if( $tabname == 'Text' ) {
            $ePerm = Console01::TABSET_PERM_GHOST;
        }
        if( $this->oMS->IsCreate() && $tabname != "Mail-Item" ) {
            $ePerm = Console01::TABSET_PERM_GHOST;
        }
        return( $ePerm );
    }

    function TabSetExtraLinkParms( $tsid, $tabname, $raParms )
    {
        // encodes additional url parms into the links in tab labels, so the tab set remembers the current kMail
        return( array( ) ); // 'p_kMail' => $raPassThru['oMS']->kMail ) );
    }
}



/* UI model:
 *
 * 1) sess[mbrmailsetup][kMail] governs the state of all forms
 * 2) Left  <FORM> : links change kMail, buttons change state of UI or active record
 * 3) Right <FORM> : edit/control active record
 * 4) KFUIForm(A) encodes active record for BOTH <FORM>s
 * 5) SEEDForm(B) encodes UI stuff for BOTH <FORM>s
 * 6) All submit buttons have the same name; functions are determined by submit value
 *
 * The console is divided into two <FORM>s because when you hit Enter on a text field, it activates the first submit button in the form.
 * You want the create/update button (on the right side) when you edit a field on the right side, so it's important to demarcate the submit
 * buttons on each side.
 *
 * This is totally legitimate as long as you put all necessary hidden parms e.g. 'A'.HiddenKey into both forms, and you don't expect
 * input fields to be submitted from the opposite form.  The only important design aspect is to ensure that hitting Enter in a text field
 * activates the desired submit button - if too ambiguous, put submit buttons in different tabsets.
 */


class mail_setup extends Console01_Worker2
{
   // var $sess;
    var $oMail;
    var $oKFormMail;  // manage the form elements pertaining to mail records
    var $oSFormMisc;  // manage the form elements pertaining to navigation, filtering, previewing, and other UI stuff

    var $kMail = 0;

    public $bCanWrite;
    public $bCanAdmin;

    // preview controls
    var $s_PreviewEmailTo = "";

    // filters
    public $p_eStatusFilter = "";    // ui control to filter table
//    var $p_bFullTable;

    private $bCreateMail = false;   // true if creating a new mail item, instead of editing an existing one
    function IsCreate() { return( $this->bCreateMail ); }


    // common mail set-up parms
//    var $p_e_sFrom;
//    var $p_e_sSubject;
//    var $p_e_docrepid;    // name or number of the doc to email
//    var $e_kDoc = 0;      // kDoc validated from docrepid

    function __construct( Console01 $oC, KeyFrameDB $kfdb1, KeyFrameDB $kfdb2, SEEDSessionAccount $sess )
    {
        parent::__construct( $oC, $kfdb1, $kfdb2, $sess );
        $this->bCanWrite = $sess->CanWrite('MBRMAIL');
        $this->bCanAdmin = $sess->CanAdmin('MBRMAIL');

        $this->oMail = new mbr_mail( $kfdb1, $kfdb2, $sess->GetUID() );
        $this->oKFormMail = new KeyFrameUIForm( $this->oMail->kfrelMail, 'A',
                                                array( "DSParms" => array('fn_DSPreStore'=>array(&$this,'kfuMail_DSPreStore')) ) );
        $this->oSFormMisc = new SEEDForm( 'B' );
        $this->oSFormMisc->Update();  // get and deserialize any parms

        $this->kMail = $this->oC->oSVA->SmartGPC( 'kMail' );

        // filters
        $this->p_eStatusFilter = $this->oC->oSVA->SmartGPC('p_eStatusFilter');

        // if the status filter changed, don't show the old kMail
        $sPrevFilter = $this->oC->oSVA->VarGet('prevStatusFilter');
        $this->oC->oSVA->VarSet( 'prevStatusFilter', $this->p_eStatusFilter );
        if( $this->p_eStatusFilter != $sPrevFilter ) {
            $this->kMail = 0;
        }
    }


    function UpdateMail()
    /********************
        Process any KFUIForm parms for the main kfrelMail relation, and get a kfr containing the updated data
        Note that $this->kfuMail_DSPreStore() is called during this process
     */
    {
$p_action = SEEDSafeGPC_GetStrPlain('p_action');
$bNoStore = ( $p_action != 'Create' && $p_action != 'Update' );

        $this->kMail = (($kfr = $this->oKFormMail->Update( array('bNoStore'=>$bNoStore))) ? $kfr->Key() : 0);
        $this->oC->oSVA->VarSet( 'kMail', $this->kMail );
    }

    function kfuMail_DSPreStore( $oDS )
    /**********************************
        KFUIForm calls this when the main Mail form is updated, after http parms are processed into the oDS's kfr, but
        before the kfr is written to the db.
        Validate, make any other changes that are necessary, return true to write the kfr to the database.
     */
    {
        $bValid = true;

        if( !($kfr = $oDS->GetKFR()) )  return( false );

        // Disallow updating the record after approval is final
        if( !in_array( $kfr->value('eStatus'), array('NEW','APPROVE') ) )  return( false );

        /* Document: the user can type a doc name or key, so the value in the kfr might be a string at this point - convert to key.
         *           GetDocName returns zero if the doc doesn't exist or the user doesn't have read access.
         */
        $kDoc = $this->oMail->GetKDoc($kfr);
        $kfr->SetValue( 'fk_docrep_docs', $kDoc );

        /* From:
         */
// TODO: VALIDATE THAT THIS IS A VALID INDEX
        $sFrom = $kfr->Value('email_from');

        /* Status: the email can't be sent until there is a valid document, from, and subject
         */
        $sStatus = ( $kDoc && !empty($sFrom) && !$kfr->IsEmpty('email_subject') ? "APPROVE" : "NEW" );
        $kfr->SetValue( 'eStatus', $sStatus );

        /* Addresses: get p_email_addresses, store in urlencoded sExtra.
         *            This can include email addresses and/or mbr_contacts keys
         */
        $sList = SEEDSafeGPC_GetStrPlain('p_email_addresses');
        $this->oMail->MailSendPutEmails( $sList, $kfr );

        return( $bValid );
    }



// TODO: Generalize this with an API so other apps can put records in the mbr_mail_send table. This app would still be used for management.
//    function doAdd() {
//        global $raFrom;
//
//        if( !$this->e_kDoc || empty($this->p_e_sFrom) || empty($this->p_e_sSubject) )  return;
//        $p_addrlist = SEEDSafeGPC_GetStrPlain('p_e_addrlist');
//        /* Extract the email addresses. The rule is supposed to be that each address is on a separate line with no punctuation, but
//         * there are often commas and semi-colons in address lists, as well as "quoted email addresses" and <RFC email addresses>.
//         * Also, word-wrapping often simulates a line break, causing several addresses to be in the same 'line' without the user's knowledge.
//         * So just explode everything.
//         */
//        $raNonEmailChars = array( "\r", "\n", "\t", ',', ';', ':', '"', "'", '(', ')', '<', '>', '[', ']', '|' );
//        $p_addrlist = str_replace( $raNonEmailChars, ' ', $p_addrlist );  // replace all whitespace and likely separators with spaces, then explode those
//        $raAddrList = explode(' ', $p_addrlist);
//
//        foreach( $raAddrList as $sEmailTo ) {
//            $sEmailTo = trim($sEmailTo);
//            if( !empty($sEmailTo) ) {
//                $kfr = $this->oMail->kfrelMail->CreateRecord();
//                $kfr->SetValue( 'fk_docrep_docs',  $this->e_kDoc );
//                $kfr->SetValue( 'dr_vars',         "" );
//                $kfr->SetValue( 'fk_mbr_contacts', 0 );
//                $kfr->SetValue( 'email_to',        $sEmailTo );
//                $kfr->SetValue( 'email_cc',        "" );
//                $kfr->SetValue( 'email_bcc',       "" );
//                $kfr->SetValue( 'email_from',      $raFrom[$this->p_e_sFrom] );   // TODO: VALIDATE THAT THIS IS A VALID INDEX
//                $kfr->SetValue( 'email_subject',   $this->p_e_sSubject );
//                $kfr->SetValue( 'eStatus',         "NEW" );
//                $kfr->SetValue( 'iResult',         0 );
////              $kfr->SetValue( 'ts_sent', );
//                $kfr->PutDBRow();
//            }
//        }
//    }
//
//    function doApproveNew()
//    {
//        if( ($kfr = $this->oMail->kfrelMail->CreateRecordCursor( "eStatus='NEW'" )) ) {
//            while( $kfr->CursorFetch() ) {
//                $this->doApprove( $kfr );
//            }
//        }
//    }
//
//    function doApproveList( $ra )
//    {
//        foreach( $ra as $kMail ) {
//            if( ($kfr = $this->oMail->kfrelMail->GetRecordFromDBKey($kMail)) ) {
//                $this->doApprove( $kfr );
//            }
//        }
//    }
//
    function doApprove( $bOverride = false )
    {
        if( !$this->kMail || !($kfr = $this->oMail->kfrelMail->GetRecordFromDBKey( $this->kMail )) )  return( false );

        if( $kfr->value('eStatus') == 'APPROVE' ) {
            $a1 = $kfr->UrlParmGet( 'sExtra', 'Approval1' );
            $a2 = $kfr->UrlParmGet( 'sExtra', 'Approval2' );

            if( $bOverride ) {
                // a super-user can set both approvals
                $kfr->UrlParmSet( 'sExtra', 'Approval1', $this->sess->GetUID() );
                $kfr->UrlParmSet( 'sExtra', 'Approval2', $this->sess->GetUID() );
                $kfr->SetValue( 'eStatus', "READY" );
            } else if( empty($a1) ) {
                // set the first approval
                $kfr->UrlParmSet( 'sExtra', 'Approval1', $this->sess->GetUID() );
            } else if( empty($a2) && $a1 != $this->sess->GetUID() ) {
                // set the second approval if current user is different from the first approver
                $kfr->UrlParmSet( 'sExtra', 'Approval2', $this->sess->GetUID() );
                $kfr->SetValue( 'eStatus', "READY" );
            }
            $kfr->PutDBRow();

            if( $kfr->value('eStatus') == 'READY' ) {
                // This is the final approval. Set up the individual mails
                list($raEmails,$raKMbr) = $this->oMail->MailSendGetEmailsAndMbrKeys( $kfr );
                foreach( $raEmails as $sEmail ) {
                    $kfrRec = $this->oMail->kfrelRecipients->CreateRecord();
                    $kfrRec->SetValue( 'fk_mbr_mail_send', $this->kMail );
                    $kfrRec->SetValue( 'email_to', $sEmail );
                    $kfrRec->SetValue( 'eStatus', "READY" );
                    $kfrRec->PutDBRow();
                }
                foreach( $raKMbr as $kMbr ) {
                    $kfrRec = $this->oMail->kfrelRecipients->CreateRecord();
                    $kfrRec->SetValue( 'fk_mbr_mail_send', $this->kMail );
                    $kfrRec->SetValue( 'fk_mbr_contacts', $kMbr );
                    $kfrRec->SetValue( 'eStatus', "READY" );
                    $kfrRec->PutDBRow();
                }
                // Remove the addresses from sExtra because they make a join of MSxMSR really really big
                $kfr->UrlParmSet( 'sExtra', 'email_addresses', '' );
                $kfr->UrlParmSet( 'sExtra', 'mbr_keys', '' );
                $kfr->UrlParmSet( 'sExtra', 'bull_keys', '' );
                $kfr->PutDBRow();
            }
        }
    }

//    function doCancelNew()
//    {
//        if( ($kfr = $this->oMail->kfrelMail->CreateRecordCursor( "eStatus='NEW'" )) ) {
//            while( $kfr->CursorFetch() ) {
//                $this->doCancel( $kfr );
//            }
//        }
//    }
//
//    function doCancelList( $ra )
//    {
//        foreach( $ra as $kMail ) {
//            if( ($kfr = $this->oMail->kfrelMail->GetRecordFromDBKey($kMail)) ) {
//                $this->doCancel( $kfr );
//            }
//        }
//    }
//
//    function doCancel( $kfr )
//    /* Set the record to _status=1
//     * Only works for NEW records, because otherwise it's too late to cancel.  Another mechanism should be used to hide messages that are already sent.
//     */
//    {
//        if( $kfr && $kfr->value('eStatus') == 'NEW' ) {
//            $kfr->StatusSet( "Deleted" );
//            $kfr->PutDBRow();
//        }
//    }
//
    function doDelete( $kMail )
    {
        if( ($kfr = $this->oMail->kfrelMail->GetRecordFromDBKey($kMail)) ) {
            //if( in_array( $kfr->value('eStatus'), array('NEW', 'READY') ) ) {
                $kfr->StatusSet( KFRECORD_STATUS_DELETED );
                $kfr->PutDBRow();
            //}
        }
    }

    function drawPreview()
    /*********************
        Draw a preview of the current mail document
        $this->s_PreviewEmailTo : personalize the mail for this recipient
     */
    {
        if( !($sEmailTo = $this->s_PreviewEmailTo) ) {
            $sEmailTo = " *** ";
        }
        if( is_numeric($sEmailTo) && ($kMbr = intval($sEmailTo)) ) {
            $sEmailTo = "";
        } else {
            $kMbr = 0;
        }

        $sHeader = $sDocOutput = "";
        if( ($kfr = $this->oMail->kfrelMail->GetRecordFromDBKey( $this->kMail )) ) {

            $sFrom = $this->oMail->GetFullFrom( $kfr->Value('email_from') );

            $sTo = ($kfrMbr = $this->oMail->oMbr->GetKFRByKey($kMbr)) ? $kfrMbr->value('email') : $sEmailTo;
            if( empty($sTo) ) $sTo = "< Unknown recipient >";

            $sHeader = $kfr->Expand( "From: ".SEEDStd_HSC($sFrom)    // because Expand's hsc expansion is only for the tagged values
                                    ."\nTo: ".SEEDStd_HSC($sTo)
//                                  ."\nCc: [[email_cc]]"
//                                  ."\nBcc: [[email_bcc]]"
                                    ."\nSubject: [[email_subject]]",
                                    true );  // expand using valueEnt

            $sDocOutput = $this->oMail->DrawMail( $kfr->value('fk_docrep_docs'), $kfr->value('eDB'),  $sEmailTo, $kMbr,
                                                  $kfr->value('email_subject'), array() /* dr_vars */ );
        }

        $s = "<DIV style='margin:0px auto; padding:10px; border: medium solid black;'>"
            ."<PRE style='margin-top:0; padding-top:0'># {$this->kMail}\n\n"
            .$sHeader."</PRE>"
            ."<DIV style='padding:1.5em; border:thin solid black'>"
            .$sDocOutput
            ."</DIV>"
            ."</DIV>\n";

        return( $s );
    }

    private function isNew( $kfr, $bFailDeletedItems = true )
    /********************************************************
        Return true if the mail message is not new or awaiting approval

         bFailDeletedItems : if you're testing whether the UI should allow the user to edit a mail item, deleted messages fail this test
        !bFailDeletedItems : if you want to know whether recipients) were ever created, assess deleted messages like all others
     */
    {
        return( in_array($kfr->Value('eStatus'), array('NEW','APPROVE')) &&
                (!$bFailDeletedItems || $kfr->StatusGet() != KFRECORD_STATUS_DELETED) );
    }

    function drawMailItem()
    {
        $sOut = "";

        if( $this->IsCreate() ) {
            $this->kMail = 0;
            $kfr = $this->oMail->kfrelMail->CreateRecord();
        } else {
            if( !$this->kMail )  return( "<P style='margin:1em;'>Choose a mail message in the table at the left.</P>" );
            $kfr = $this->oMail->kfrelMail->GetRecordFromDBKey( $this->kMail );
        }

        if( !$kfr )  return( "<p>Cannot find the mail record</p>" );

        $sOut = $this->isNew( $kfr ) ? $this->drawMailItemNew( $kfr ) : $this->drawMailItemNotNew( $kfr );

        return( $sOut );
    }

    private function drawMailItemNew( $kfr )
    /***************************************
        Mail Item tab, for mail that is new or not approved yet
     */
    {
        $sOut = "";

        $this->oKFormMail->SetKFR($kfr);

        $bDisable = !in_array( $kfr->Value('eStatus'), array("NEW","APPROVE") ) || $kfr->StatusGet() == KFRECORD_STATUS_DELETED;


        if( !$bDisable ) {
            $sOut .= "<FORM method='post' action='${_SERVER['PHP_SELF']}'>"
                    .$this->oKFormMail->HiddenKey();
        }
        $sOut .= "<table cellpadding='5' cellspacing='0' border='0' width='100%'>"
                ."<tr><td><h4>".($this->bCreateMail ? "Create New Mail" : ("Edit Mail (id #".$kfr->Key().")") )."</h4></td>"
                ."<td>"
                .($bDisable ? "" : ("<INPUT type='submit' name='p_action' value='".($this->bCreateMail ? "Create" : "Update")."'>"))
                ."</td>"
                ."</tr>";

        // Document
        $kDoc = $kfr->Value('fk_docrep_docs');
        $sDocName = $this->oMail->GetDocName( $kfr );
        $sOrigDocParm = SEEDSafeGPC_GetStrPlain( $this->oKFormMail->oFormParms->sfParmField('fk_docrep_docs') );

        $sOut .= "<TR><TD valign='top'>Document:&nbsp;&nbsp;"
                .$this->oKFormMail->Select('eDB','',array('public'=>'Public','office'=>'Office'))."<BR/>"
                .$this->oKFormMail->Text('fk_docrep_docs', "", array('size'=>15))." <I>$sDocName</I>";
        if( !$kDoc && !empty($sOrigDocParm) ) {
            // a doc name or id was submitted, but the updater rejected it
            $sOut .= "<BR/><SPAN style='color:red'>Document '$sOrigDocParm' doesn't exist or is not readable.</SPAN>";
        }
        $sOut .= "</TD><TD valign='top'>"
                ."<P class='msInstructions'>Put your message in the Office Documents. Type the document's name or number here.</P>"
                ."</TD></TR>";

        // From and Subject
        $raFromOptions = array_merge( array(''=>'--- Choose ---' ), $this->oMail->raFrom );
        foreach( $this->oMail->raFrom as $k => $v )  $raFromOptions[$k] = $k;    // build array of just email=>email to save horizontal space on the form

        $sOut .= "<TR><TD valign='top'>From:<BR/>".$this->oKFormMail->Select('email_from',"",$raFromOptions)."</TD>"
                ."<TD valign='top'><P class='msInstructions'>Choose whom the emails will be 'From', and enter the Subject line.</P></TD></TR>"
                ."<TR><TD colspan='2'>Subject:<BR/>".$this->oKFormMail->Text('email_subject',"",array('size'=>50))."<BR/></TD></TR>";

        // Addresses
        $raEmails = $this->oMail->MailSendGetEmailsRA( $kfr );
        $sEmails = implode( "\n", $raEmails );

        $sOut .= "<TR valign='top'><TD colspan='2'><P class='msInstructions'>Type or paste email addresses and member numbers below. "
                                                  ."A single separate email will be sent to each address and each member.</P></TD>"
                ."<TR valign='top'><TD colspan='2'>Email addresses / member numbers:<BR/><TEXTAREA   name='p_email_addresses' rows='20' style='width:100%'>".SEEDStd_HSC($sEmails)."</TEXTAREA></TD>"
                                 //."<TD>Member Keys:<BR/><TEXTAREA name='p_member_keys'     rows='20' cols='30'>".htmlspecialchars($sMbrid, ENT_QUOTES)."</TEXTAREA></TD>"
                                 ."</TR>"
                ."</TABLE>";
        if( !$bDisable ) {
            $sOut .= "</FORM>";
        }

        return( $sOut );
    }

    private function drawMailItemNotNew( $kfr )
    /******************************************
        Mail Item tab, for mail that has been approved, sent, or deleted
     */
    {
        $s = "<h4>Mail Message #".$kfr->Key()."</h4>"
            ."<p>Status: ".$kfr->value('eStatus')
            .($kfr->StatusGet()==KFRECORD_STATUS_DELETED ? " <span style='color:red'>(Deleted)</span>" : "")
            ."</p>"
            ."<p>Created: ".substr($kfr->value('_created'),0,10)."</p>"
            ;

        if( $this->isNew( $kfr, false ) ) {
            // Mail item is New or Approve (recipients have not been created)
            // Since this method is called when !isNew(), this case will only happen for deleted items that were New/Approve
            $raEmails = $this->oMail->MailSendGetEmailsRA( $kfr );
            $s .= "<p>Preparing to send to:<div style='margin-left:20px'>".implode( "<br/>", $raEmails )."</div></p>";

        } else {
            // recipients have been created
// The kfrelRecipients relation makes a join that involves the very large eExtra column which blows up the server.
// Implement this method with a ver2 kfrel that uses raFieldsOverride to make the join less expensive
//            $s .= $this->drawMailItemNotNew_Status( "READY", $kfr->Key() )
//                 .$this->drawMailItemNotNew_Status( "SENT", $kfr->Key() )
//                 .$this->drawMailItemNotNew_Status( "FAILED", $kfr->Key() );
        }

        return( $s );
    }

    private function drawMailItemNotNew_Status( $eStatus, $kMail )
    {
        $s = "<p>";

//$this->oMail->kfrelRecipients->kfdb->SetDebug(2);
        if( ($kfrc = $this->oMail->kfrelRecipients->CreateRecordCursor( "MSR.eStatus='$eStatus' and fk_mbr_mail_send='$kMail'",
                                                                        array('raFieldsOverride'=>array('ts_sent'=>'ts_sent','email_to'=>'email_to','fk_mbr_contacts'=>'fk_mbr_contacts')) )) ) {
            $s .= $kfrc->CursorNumRows()." $eStatus:"
                 ."<div style='margin-left:20px'>";
            while( $kfrc->CursorFetch() ) {
                $ts = $kfrc->Value('ts_sent');
                if( substr($ts,0,4) == "0000" )  $ts = "";

                $s .= $kfrc->Expand( "[[email_to]] [[fk_mbr_contacts]] " )
                     .($ts ? date( "Y-M-d hh:mm:ss", $ts) : "")
                     ."<br/>";
            }
            $s .= "</div>";
        }
        $s .= "</p>";

        return( $s );
    }

    private function drawText()
    /**************************
        Tab where you can type the email body
     */
    {
        $s = "";

        $s .= "Email Body";

        return( $s );
    }

    function drawControls()
    {
        if( !$this->kMail )  return( "<P style='margin:1em;'>Choose a mail message in the table at the left.</P>" );

        $sOut = "<h4>Mail Message #{$this->kMail}</h4>"
               ."<p>Preview customized content for a recipient.</p>"
               ."<form id='ctrlForm' method='post' action='${_SERVER['PHP_SELF']}'>"
               .$this->oSFormMisc->Text('p_preview_email_to',"Email address or member number " )
               ."<br/><input type='submit'/><input type='hidden' name='p_action' value='Preview'/>"
               ."<script>function ctrlSetPreviewRecipient(n) { $('#sfBp_p_preview_email_to').val(n);}</script>";

        if( ($kfr = $this->oMail->kfrelMail->GetRecordFromDBKey( $this->kMail )) ) {
            $raEmails = $this->oMail->MailSendGetEmailsRA( $kfr );

            if( count($raEmails) ) {
                $sOut .= "<p>Or click on an address below.</p>";
            }

            $c = 100;
            foreach( $raEmails as $sEmail ) {
                $sStyle = ($sEmail == $this->oSFormMisc->Value('p_preview_email_to'))
                            ? "padding:10px;background-color:#eee;font-weight:bold;" : "";
                $sOut .= "<div style='margin:5px;cursor:pointer;$sStyle' onclick='ctrlSetPreviewRecipient(\"$sEmail\");document.getElementById(\"ctrlForm\").submit();'>$sEmail</div>";

                if( --$c == 0 ) break;
            }
            if( count($raEmails) > 100 ) $sOut .= "<div>... and more ...</div>";
        }

        $sOut .= "</form>";

        return( $sOut );
    }

    function drawDelete()
    {
        if( !$this->kMail )  return( "Choose a mail message in the table" );

        $sOut = "<H4>Mail Message #".$this->kMail."</H4>"
               ."<FORM method='post' action='${_SERVER['PHP_SELF']}'>"
               ."<BR/><BR/><INPUT type='submit' name='p_action' value='Delete'/> Delete the mail message"
               ."</FORM>";

        return( $sOut );
    }

    function DoAction()
    {
        switch( SEEDSafeGPC_GetStrPlain('p_action') ) {
            /***********
             * submitted by left-hand form
             */
            case 'Create a New Message':
                $this->bCreateMail = true;
                $this->kMail = 0;
                // When you click the Create button you want to be able to see what you're creating.
                $this->p_eStatusFilter = "";
                $this->oC->oSVA->VarSet('p_eStatusFilter',"");
                break;

            case 'Preview':
                if( $this->kMail ) {
                    // Personalize the preview if 'to' is set in the Control tab
                    $this->s_PreviewEmailTo = $this->oSFormMisc->oDS->Value('p_preview_email_to');
                }
                break;

            case 'Approve':
                if( $this->bCanWrite )  $this->doApprove();
                break;

            case 'Approve Override':
                if( $this->bCanAdmin )  $this->doApprove( true );
                break;

            /***********
             * submitted by right-hand form - use oKFormMail
             */
            case 'Create':
            case 'Update':
                $this->UpdateMail();  // create/update a mail record based on oKFormMail parms
                break;

             case 'Delete':
                if( $this->bCanAdmin && $this->kMail )  $this->doDelete( $this->kMail );
                $this->kMail = 0;
                break;

            default:
                break;
        }
    }
}


$raConsoleParms = array(
    'HEADER'       => "Seeds of Diversity Bulk Mailer",
    'CONSOLE_NAME' => "MailSetup",
    'HEADER_LINKS' => array( array( 'href' => 'mbr_email.php',    'label' => "Email Lists",  'target' => '_blank' ),
                             array( 'href' => 'mbr_mailsend.php', 'label' => "Send 'READY'", 'target' => '_blank' ) ),
    'TABSETS' => array( "right" => array( 'tabs'=> array( 'Mail-Item' => array('label' => "Mail Item"),
                                                          'Text'     => array('label' => "Text"),
                                                          'Controls' => array('label' => "Controls"),
                                                          'Delete' => array('label' => "Delete"),
                                                        ) ) )
);

// Put this after the p_action because it resets the tab form on Create New
$oConsole = new MyConsole( $kfdb1, $kfdb2, $sess, $raConsoleParms );

$sMailTable = drawMailTable( $oConsole->oMS );
$sPreview = $oConsole->oMS->kMail ? $oConsole->oMS->drawPreview() : "";

$sOut = "<style>"
    ."td,th {font-size:10pt; font-family:verdana,helvetica,sans serif;}"
    .".msInstructions { background-color:#E0F0E0; font-size:10pt; margin:5px;padding:5px 15px;}"
    .".msTable, .msTable th, .msTable td { border:1px solid #777 }"
    ."          .msTable th, .msTable td { padding:5px }"
    ."          .msTable th              { background-color:#eee }"
    ."          .msTableCurrRow td       { background-color:#9df }"
    ."</style>"

    // Hitting Enter in a text field presses the first button in a form.
    // This is split into two forms so you can hit Enter in the right-hand form without activating buttons in the left-hand table.
    ."<table border='0' cellspacing='0' cellpadding='10' width='100%'><tr>"
    ."<td valign='top'>"
        ."<form method='post' action='${_SERVER['PHP_SELF']}'>"
        //.SEEDForm_Hidden( "p_kMail", $oMS->kMail )
        .$sMailTable
        ."</form>"
        .$sPreview
    ."</td>"
    ."<td valign='top' style='border-left:solid grey 1px;padding-left:2em;width:50%'>"
        .$oConsole->TabSetDraw( "right" )
    ."</td>"
    ."</tr></table>";


echo $oConsole->DrawConsole( $sOut );




function drawMailTable( $oMS )
{
    $sOut = SEEDStd_StrNBSP("Show: ")
           .SEEDForm_Select2( 'p_eStatusFilter', array('Not Sent Yet'=>'','Sent'=>'SENT','Deleted'=>'DELETED',"Everything"=>"ALL"), $oMS->p_eStatusFilter,
                              array( 'selectAttrs' => "onChange='submit();'" ) )
           .SEEDStd_StrNBSP("          ")
           ."<INPUT type='submit' name='p_action' value='Create a New Message'/>"
           .SEEDStd_StrNBSP("          ")
           ."<A href='{$_SERVER['PHP_SELF']}'>Refresh</A>"
           ."<BR/><BR/>";


    /*
           .SEEDStd_StrNBSP("     Show: ")
           .SEEDForm_Select( 'p_eStatus', array(''=>'All','NEW'=>'NEW','READY'=>'READY','SENT'=>'SENT','FAILED'=>'FAILED'), $oMS->p_eStatus,
                             array( 'selectAttrs' => "onChange='submit();'" ) )
           .SEEDStd_StrNBSP("     Table: ")
           .SEEDForm_Select( 'p_bFullTable', array('0'=>'Simple','1'=>'Full'), $oMS->p_bFullTable,
                             array( 'selectAttrs' => "onChange='submit();'" ) )
          */


    switch( $oMS->p_eStatusFilter ) {
        case 'SENT':
            $sCond = "eStatus='SENT'";
            $iStatus = 0;
            break;
        case 'DELETED':
            $sCond = "";
            $iStatus = KFRECORD_STATUS_DELETED;
            break;
        case 'ALL':
            $sCond = "";
            $iStatus = -1;    // all _status
            break;
        default:
            $sCond = "eStatus<>'SENT'";
            $iStatus = 0;
            break;
    }

    /* Draw the table of current mail
     */
    $sOut .= "<table class='msTable'>"
            ."<tr><th>Mail Item</th><th>Envelope</th><TH>Status</TH></TR>";
    if( ($kfr = $oMS->oMail->kfrelMail->CreateRecordCursor($sCond, array('sSortCol'=>'_key','bSortDown'=>true,'iStatus'=>$iStatus))) ) {
        while( $kfr->CursorFetch() ) {
            $bCurrent = ($kfr->Key() == $oMS->kMail);
            $bStatusNew = ($kfr->value('eStatus') == 'NEW');
            if( $kfr->Value('fk_docrep_docs') ) {
                $sDocName = "<b>".$oMS->oMail->GetDocTitle( $kfr )."</b> ".$oMS->oMail->GetDocName( $kfr )." (".$kfr->Value('fk_docrep_docs').")";
            } else {
                $sDocName = "See TEXT";
            }
            $nEmails = $nMbrid = 0;
            list($raEmails,$raKMbr) = $oMS->oMail->MailSendGetEmailsAndMbrKeys( $kfr );
            $nEmails = count($raEmails);
            $nMbrid = count($raKMbr);
            //if( ($sEmails = $kfr->UrlParmGet('sExtra','email_addresses')) ) {
            //    $nEmails = count(explode(' ', $sEmails));
            //}
            //if( ($sMbrid = $kfr->UrlParmGet('sExtra','member_keys')) ) {
            //    $nMbrid = count(explode(' ', $sMbrid));
            //}

            $bApproveButton = $bApproveOverride = false;
            switch( $kfr->value('eStatus') ) {
                case 'NEW':
                    $sStatus = "Incomplete";
                    break;
                case 'APPROVE':
                    $a1 = $kfr->UrlParmGet( 'sExtra', 'Approval1' );
                    $a2 = $kfr->UrlParmGet( 'sExtra', 'Approval2' );
                    if( $a1 && $oMS->oMail->oMbr->SetKMbr($a1) ) $sa1 = $oMS->oMail->oMbr->MakeName();
                    if( $a2 && $oMS->oMail->oMbr->SetKMbr($a2) ) $sa2 = $oMS->oMail->oMbr->MakeName();

                    $sStatus = ($a1 ? ("Approved by $sa1".($a2 ? " and $sa2" : "").".") : "Needs approval" );
                    if( $a1 && !$a2 ) { $sStatus .= " Needs one more approval."; }
                    $bApproveButton = ( $bCurrent && $oMS->bCanWrite && !in_array( $oMS->sess->GetUID(), array($a1, $a2) ) );
                    $bApproveOverride = ($bCurrent && $oMS->bCanAdmin);
                    break;
                case 'READY':
                case 'SENDING':
                case 'SENT':
                case 'FAILED':
                    $sql = "SELECT count(*) FROM mbr_mail_send_recipients WHERE fk_mbr_mail_send='".$kfr->Key()."' AND ";
                    $sStatus = $kfr->value('sStatus')."<BR/>"
                              .$oMS->oMail->kfrelMail->kfdb->Query1( $sql."eStatus='READY'" )." ready to send<BR/>"
                              .$oMS->oMail->kfrelMail->kfdb->Query1( $sql."eStatus='SENT'" )." sent<BR/>"
                              .$oMS->oMail->kfrelMail->kfdb->Query1( $sql."eStatus='FAILED'" )." failed";
                    break;
            }
            $sTRAttrs = ($bCurrent ? "class='msTableCurrRow'" : "")
                       ." style='cursor: pointer' "
                       ." onclick='location.replace(\"${_SERVER['PHP_SELF']}?kMail=[[_key]]\");'";
            $sOut .= $kfr->Expand(
                        "<tr $sTRAttrs>"
                           ."<td valign='top'>"
                               ."[[_key]]: [[eStatus]]"
                               ."<br/>".substr($kfr->value('_created'),0,10)
                               .($kfr->StatusGet()==KFRECORD_STATUS_DELETED ? "<br/><span style='color:red'>(Deleted)</span>" : "")
                               ."</td>"
                           ."<td valign='top'>"
                               ."Subject: <b>[[email_subject]]</b><br/>"
                               ."From: ".SEEDStd_HSC($oMS->oMail->GetFullFrom($kfr->value('email_from')))."<br/>"
                               ."To: $nEmails emails, $nMbrid member keys<br/>"
                               ."Doc: $sDocName<br/>"
                               ."</td>"
                           ."<td valign='top'>"
                               .$sStatus
                               .($bApproveButton ? "<br/><input type='submit' name='p_action' value='Approve'/>" : "")
                               .($bApproveOverride ? "<br/><input type='submit' name='p_action' value='Approve Override'/>" : "")
                               ."</td>"
                       ."</tr>",
                       true );
        }
    }
    $sOut .= "</TABLE>";

/*
    $sOut .= "<BR/>"
        ."<INPUT type='submit' name='p_action' value='".($oMS->p_bFullTable ? "Approve Checked" : "Approve All NEW")."'/><BR/><BR/>"
        ."<TABLE border='1' cellpadding='5' cellspacing='1'>"
        ."<TR>".($oMS->p_bFullTable ? "<TH>&nbsp;</TH>" : "")."<TH>Status</TH><TH>Doc</TH><TH>To</TH><TH>Subject</TH>".($oMS->p_bFullTable ? "<TH>Result</TH><TH>Sent</TH>" : "")."</TR>\n";
    $sCond = "";
    if( !empty( $oMS->p_eStatus ) ) {
        //$sCond = "eStatus='{$oMS->p_eStatus}'";
    }

    if( !$oMS->p_bFullTable ) {       // no check boxes because we can't check off an arbitrary group of records
        $raRows = array();
        if( ($kfr = $oMS->oMail->kfrelMail->CreateRecordCursor( $sCond, array('sSortCol'=>'email_subject,eStatus') )) ) {
            while( $kfr->CursorFetch()) {
                $raRows[] = $kfr->ValuesRA();
            }
        }

        function _drawRow( $prevDoc, $prevKey, $prevStatus, $raEmails, $prevSubject )
        {
            $sDoc = "(".$prevDoc.")";
            if( $prevDoc ) {	// DocRepDB dies with an error message if this is zero, which is good for error trapping in general
                $sDoc = $oMS->oMail->oDocRepDB->GetDocName( $prevDoc ) ." ". $sDoc;
            }
            $sDoc = "<A HREF='${_SERVER['PHP_SELF']}?p_kPreview=".$prevKey."'>$sDoc</A>";
            $nShowEmails = 12;
            return(
                 "<TR>"
                ."<TD valign='top'>$prevStatus</TD>"
                ."<TD valign='top'>$sDoc</TD>"
                ."<TD valign='top'>".(count($raEmails) > 1 ? (count($raEmails)." addresses:<BR/>") : "")
                                    .implode(", ", array_slice( $raEmails, 0, $nShowEmails ))
				    .(count( $raEmails ) > $nShowEmails ? "&nbsp;..." : "")."</TD>"
                ."<TD valign='top'>$prevSubject</TD>"
                ."</TR>\n" );
        }


        $prevStatus = "";
        $prevSubject = "";
        $prevDoc = 0;
        $prevKey = 0;
        $raEmails = array();
        foreach( $raRows as $r ) {
	    /* Collect up batches of email addresses with the same attributes. When a message with different
             * attributes appears, write out the previous attributes and the collected addresses.
             *]
            $sStatus = $r['eStatus'];
            if( $sStatus == 'NEW' ) {
                $urlRA = SEEDStd_ParmsURL2RA( $r['sExtra'] );
                if( !empty( $urlRA['Approval1'] ) ) {
                    $sStatus .= "<BR/>Approved by ".$urlRA['Approval1'];
                }
            }

            if( count($raEmails) && ($r['fk_docrep_docs'] != $prevDoc || $sStatus != $prevStatus || $r['email_subject'] != $prevSubject) ) {
                $sOut .= _drawRow( $prevDoc, $prevKey, $prevStatus, $raEmails, $prevSubject );
                $raEmails = array();
            }
            $prevStatus = $sStatus;
            $prevSubject = $r['email_subject'];
            $prevDoc = $r['fk_docrep_docs'];
            $prevKey = $r['_key'];
            //$raEmails[] = $r['email_to'];
        }
        if( count($raEmails) ) {
            $sOut .= _drawRow( $prevDoc, $prevKey, $prevStatus, $raEmails, $prevSubject );
        }
    } else {

        if( ($kfr = $oMS->oMail->kfrelMail->CreateRecordCursor( $sCond, array('sSortCol'=>'email_subject,eStatus') )) ) {
            $sSubjPrev = "";
            while( $kfr->CursorFetch()) {
                $sReady = ($kfr->value('eStatus')=='NEW')
                        ? "<INPUT type='checkbox' name='chk".$kfr->Key()."' value='1'/>" : "&nbsp;";

                $sStatus = $kfr->value('eStatus');
                if( $sStatus == 'NEW' && $kfr->UrlParmGet( 'sExtra', 'Approval1' ) ) {
                    $sStatus .= "<BR/>Approved by ".$kfr->UrlParmGet( 'sExtra', 'Approval1' );
                }

                $sTS = ($kfr->value('ts_sent') != '0000-00-00 00:00:00') ? $kfr->value('ts_sent') : "&nbsp;";
                // kluge: the timestamp advances automatically whenever the record is updated. Not sure why. Hide this in pre-sent records.
                if( $kfr->value('eStatus') == 'NEW' || $kfr->value('eStatus') == 'READY' ) $sTS = "&nbsp;";

                $sDoc = "(".$kfr->value('fk_docrep_docs').")";
                if( $kfr->value('fk_docrep_docs') ) {	// DocRepDB dies with an error message if this is zero, which is good for error trapping in general
                    $sDoc = $oMS->oMail->oDocRepDB->GetDocName( $kfr->value('fk_docrep_docs') ) ." ". $sDoc;
                }
                $sDoc = "<A HREF='${_SERVER['PHP_SELF']}?p_kPreview=".$kfr->Key()."'>$sDoc</A>";
                $sSubj = ($sSubjPrev == $kfr->value('email_subject') ? "-same-" : $kfr->value('email_subject'));
                $sSubjPrev = $kfr->value('email_subject');
                $sOut .=
                     "<TR>"
                    ."<TD valign='top'>$sReady</TD>"
                    ."<TD valign='top'>$sStatus</TD>"
                    ."<TD valign='top'>$sDoc</TD>"
                    ."<TD valign='top'>".$kfr->valueEnt('email_to')."</TD>"
                    ."<TD valign='top'>".htmlspecialchars($sSubj,ENT_QUOTES)."</TD>"
                    ."<TD valign='top'>".$kfr->value('iResult')."</TD>"
                    ."<TD valign='top'>$sTS</TD>"
                    ."</TR>\n";
            }
        }
    }
    $sOut .= "</TABLE><BR/><BR/>"
            ."<INPUT type='submit' name='p_action' value='".($oMS->p_bFullTable ? "Cancel Checked" : "Cancel All NEW")."'/>"
           ;// ."</FORM>";
*/

    return( $sOut );
}

?>
