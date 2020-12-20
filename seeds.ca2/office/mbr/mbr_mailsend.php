<?php

/* mbr_mailsend.php
 *
 * Copyright 2009-2018 Seeds of Diversity Canada
 *
 * Send mail to members / donors / subscribers.
 * Use mbr_mailsetup to prepare the mailing.
 */

if( !defined("SITEROOT") )  define("SITEROOT", "../../");
include_once( SITEROOT."site2.php" );
include_once( SEEDCOMMON."siteutil.php" );   // Site_Log
//include_once( SEEDCORE."SEEDPerms.php" );
include_once( "_mbr_mail.php" );


list($kfdb2) = SiteStart();   // no sess, no user because this script is typically invoked non-interactively
$kfdb1 = SiteKFDB( SiteKFDB_DB_seeds1 ) or die( "Cannot connect to database" );

//$kfdb->KFDB_SetDebug(2);
//print_r($_REQUEST);

$n = $kfdb2->Query1("SELECT count(*) FROM mbr_mail_send_recipients WHERE _status='0' AND eStatus='READY'");

$sBody = "<h2>Seeds of Diversity Bulk Mailer</h2>"
        ."<p>There are $n emails ready to send.</p>";
if( $n ) {
    $sBody .= "<p>Sending one email every 20 seconds.</p>"
             ."<p>You can see the progress in the Bulk Mailer table by clicking the Refresh link.</p>";
}

$sBody .= "<br/><br/>";
$oSend = new mbr_mailsend( $kfdb1, $kfdb2, 1499 );   /* **************  UID ************************************/

for( $i = 0; $i < 1; ++$i ) {
    list($kRec,$sMsg) = $oSend->sendOne();
    $sBody .= $kRec." ".microtime()."<br/>".($sMsg ? "$sMsg<br/>" : "");
    if( !$kRec )  break;

    /* Send 10 emails in a burst, then refresh the page after a 5-second delay.
     */
    //set_time_limit( 30 );
    //usleep( 500000 ); // half of a second
}
sleep( 20 );


echo "<html><head>"
    .($n ? "<meta http-equiv='refresh' CONTENT='1; URL=http://seeds.ca/office/mbr/mbr_mailsend.php'>" : "")
    ."</head><body>"
    .$sBody
    ."</body></html>";


class mbr_mailsend {
    private $oMail;

    function mbr_mailsend( $kfdb1, $kfdb2, $uid )
    {
        $this->oMail = new mbr_mail( $kfdb1, $kfdb2, $uid );
    }

    function sendOne()
    /* Send one email that is marked 'READY'
     * Return the _key of the email that was attempted, or 0 if no more are 'READY'
     */
    {
        $sMsg = "";
        $ok = false;

        $this->oMail->Clear();  // Reset any state data from the last email

        $kfrRec = $this->oMail->kfrelRecipients->GetRecordFromDB( "MSR.eStatus='READY'" );

        if( !$kfrRec || !$kfrRec->Key() ) {
            $sMsg = "No emails are ready to send";
            goto done;
        }

        $kfrMail = $this->oMail->kfrelMail->GetRecordFromDBKey( $kfrRec->value('fk_mbr_mail_send') );
        if( !$kfrMail || !$kfrMail->Key() ) {
            $sMsg = "Error: cannot find mbr_mail_send record ".$kfrRec->value('fk_mbr_mail_send');
            goto done;
        }

        /* Send one email
         */
        if( !($sTo = $kfrRec->Value('email_to')) ) {
            $sTo = ($kfrMbr = $this->oMail->oMbr->GetKFRByKey($kfrRec->Value('fk_mbr_contacts'))) ? $kfrMbr->Value('email') : "";
        }
        $sFrom = $kfrMail->Value("email_from");
        $sSubject = $kfrMail->Value("email_subject");

        if( !$sTo || !$sFrom || !$sSubject ) {
            $sMsg = $kfrRec->Key()." cannot send. To='$sTo', From='$sFrom', Subject='$sSubject'";
            $kfrRec->SetValue( "eStatus", "FAILED" );
            $kfrRec->PutDBRow();
        } else {
            $kfrRec->SetValue( "eStatus", "SENDING" );   // this is a feeble attempt to prevent two processes from sending the same mail - do better
            $kfrRec->PutDBRow();
        }
        if( $kfrRec->value('eStatus') == 'SENDING' ) {
            $i = 0;
            if( ($sDoc = $this->oMail->DrawMailFromKFR( $kfrRec )) ) {
                $i = MailFromOffice( $sTo, $sSubject,
                                     "", $sDoc,
                                     array( "from"=>array($this->oMail->GetFullFrom($sFrom) ) ) );
            }
            $kfrRec->SetValue( "iResult", $i );
            $kfrRec->SetValue( "eStatus", $i==1 ? "SENT" : "FAILED");
            $kfrRec->PutDBRow();
            $this->oMail->kfdb2->Execute( "UPDATE mbr_mail_send_recipients SET ts_sent=NOW() WHERE _key='".$kfrRec->Key()."'");
            Site_Log( "mbr_mailsend", $kfrRec->Expand( "[[_key]] [[eStatus]] [[email_to]] [[fk_mbr_contacts]] ").time() );
        }

        /* Update the master mail record status
         */
        if( $kfrMail->value('eStatus') == 'READY' ) {
            $kfrMail->SetValue('eStatus',"SENDING");
            $kfrMail->PutDBRow();
        }

        /* If there are no more READY emails for this mail doc, record that SENDING is finished.
         * This clears mail.sExtra where the addresses are stored in the APPROVE stage since this field takes a lot of space
         * and is no longer needed.
         * Also copy the results of the SENDING to a file and clear the mbr_mail_send_recipients because it also takes a lot of space.
         */
        $kfrRecTest = $this->oMail->kfrelRecipients->GetRecordFromDB( "MSR.eStatus='READY' AND MSR.fk_mbr_mail_send='".$kfrMail->Key()."'" );
        if( !$kfrRecTest || !$kfrRecTest->Key() ) {
            $kfrMail->SetValue('eStatus',"SENT");
            $kfrMail->PutDBRow();

            $sMsg = $this->oMail->FinalizeSent( $kfrMail->Key() );
        }

        $ok = true;

        done:
        return( array($ok ? $kfrRec->Key() : 0, $sMsg) );  // don't return success/failure, just return whether a message was processed (0 mean no more messages)
    }
}

?>
