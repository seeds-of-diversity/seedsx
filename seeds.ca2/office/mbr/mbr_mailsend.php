<?php

/* mbr_mailsend.php
 *
 * Copyright 2009-2020 Seeds of Diversity Canada
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

$oApp = SEEDConfig_NewAppConsole_LoginNotRequired( ['db'=>'seeds2'] );

$n = $oApp->kfdb->Query1("SELECT count(*) FROM {$oApp->GetDBName('seeds2')}.mbr_mail_send_recipients WHERE _status='0' AND eStatus='READY'");

$sBody = "<h2>Seeds of Diversity Bulk Mailer</h2>"
        ."<p>There are $n emails ready to send at ".date('Y-m-d H:i:s').".</p>";

list($bTestOk,$sTest) = testMailHistory( $oApp );
$sBody .= $sTest;

$bSendMail = ($bTestOk && $n);

if( $bSendMail ) {
    $sBody .= "<p>Sending one email every 20 seconds.</p>"
             ."<p>You can see the progress in the Bulk Mailer table by clicking the Refresh link.</p>";
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
    // don't delay here so results appear right away, refresh browser every 20 seconds
    //sleep( 20 );
}

echo Console02Static::HTMLPage( $sBody,
                                //($bSendMail ? "<meta http-equiv='refresh' CONTENT='20; URL=https://seeds.ca/office/mbr/mbr_mailsend.php'>" : ""),
                                ($bSendMail ? "<meta http-equiv='refresh' content='20'>" : ""),
                                'EN', [] );


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


function testMailHistory( SEEDAppConsole $oApp )
{
    $bOk = true;
    $s = "";

    $dirMail = (SEED_isLocal ? "/home/bob/" : "/home/seeds/")."mail/new";
    $dirArchive = (SEED_isLocal ? "/home/bob/" : "/home/seeds/")."mail/seed_mail_archive";

    if( !is_dir($dirMail) ) {
        $s = "<p class='alert alert-warning'>$dirMail does not exist</p>";  // not a failure, return bOk==true
        goto done;
    }

    $nFiles = 0;
    $raEmailsDiscarded = [];
    $raEmailsFailed = [];

    foreach( new DirectoryIterator($dirMail) as $f ) {
        if( $f->isDot() || $f->isDir() || !SEEDCore_StartsWith($f->getFilename(),'1') ) continue;

        $sFile = file_get_contents($f->getPathname());

        $email = "";
        // Check for discards first because they have the same headers as fails
        if( ($r = preg_match( "/exceeded the max defers and failures per hour .* discarded.\n/", $sFile)) ) {
            if( ($r = preg_match( "/\nX-Failed-Recipients: (.*)\n/", $sFile, $match)) ) {
                $raEmailsDiscarded[] = $match[1];
                $bOk = false;
            }
        }
        else
        if( ($r = preg_match( "/\nAction: failed\n/", $sFile)) ) {
            if( ($r = preg_match( "/\nX-Failed-Recipients: (.*)\n/", $sFile, $match)) ) {
                $raEmailsFailed[] = $match[1];
                //$bOk = false;
            }
        }

        ++$nFiles;
    }

    $s .= "<div class='alert' style='float:right;width:40%'>"
            ."<div>$nFiles files</div>"
            ."<div class='alert' style='float:left'>".count($raEmailsDiscarded)." emails were discarded:"
                ."<pre>".SEEDCore_ArrayExpandSeries( $raEmailsDiscarded, "<br/>[[]]" )."</pre>"
            ."</div>"
            ."<div class='alert' style='float:left'>".count($raEmailsFailed)." emails failed:"
                ."<pre>".SEEDCore_ArrayExpandSeries( $raEmailsFailed, "<br/>[[]]" )."</pre>"
            ."</div>"
         ."</div>";

    done:
    return( [$bOk,$s] );
}
