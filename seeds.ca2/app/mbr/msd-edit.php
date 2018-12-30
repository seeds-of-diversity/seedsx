<?php

/*
 * Seed Directory member interface
 *
 * Copyright 2011-2019 Seeds of Diversity Canada
 *
 * Gives the current user an interface to their own listings in the Member Seed Directory
 */

// The report that we use to send printouts to people would be really nice as an email, per grower. It looks good in an email.

// Grower edit screen should have some clickable boxes:
//    I'm still thinking about which seeds I want to offer.
//    I'm done making changes, please list what I've said here.
//    I want my seed list to be exactly the same as last year.
// the problem is with people who logged in but didn't change anything - are they thinking, or do they want the list to remain the same?


define( "SITEROOT", "../../" );
include( SITEROOT."site.php" );
include_once( SEEDCOMMON."console/console01.php" );
include_once( SEEDCOMMON."sl/sed/sedCommon.php" );
include_once( SEEDCOMMON."mbr/mbrSitePipe.php" );
include_once( SEEDCORE."SEEDBasket.php" );
include_once( SEEDAPP."basket/basketProductHandlers_seeds.php" );
include_once( SEEDAPP."seedexchange/msdedit.php" );
include_once( SEEDLIB."msd/msdlib.php" );

list($kfdb, $sess, $lang) = SiteStartSessionAccount( array("sed" => "W") );

$oApp = new SEEDAppConsole( $config_KFDB['seeds1']
                            + array( 'sessPermsRequired' => array(),
                                     'logdir' => SITE_LOG_ROOT,
                                     'lang' => $lang )
);


//var_dump($_SESSION);
//echo "<BR/><BR/>";
//var_dump($_REQUEST);


class SEDMbr extends SEDCommon  // the member-access derivation of SED object
{
    function __construct( KeyFrameDB $kfdb1, SEEDSessionAccount $sess, $lang )
    {
        parent::__construct( $kfdb1, $sess, $lang, 'EDIT' );    // user is logged in
        $this->kGrowerActive = $sess->GetUID();    // always only show the session user's info and seeds
    }

    /*protected*/ function GetMbrContactsRA( $kMbr )
    {
        $ra = MbrSitePipeGetContactsRA2( $this->kfdb, $kMbr );

/*
// use /l/mbr/mbrPipe.php::MbrPipeGetContactRA( $this->kfdb, $kMbr )
        $oPipe = new SitePipe( $this->kfdb );
        list( $kPipeRow, $sPipeSignature ) = $oPipe->CreatePipeRequest( array('cmd'=>'GetMbrContactsRA', 'kMbr'=>$kMbr) );

        list( $bOk, $hdr, $resp ) = $oPipe->SendPipeRequest( array( "kPipeRow"=>$kPipeRow, "sPipeSignature"=>$sPipeSignature ) );

        if( $bOk ) {
// remote server should indicate success of its processing, because it always sends a 200 http response
            $ra = $oPipe->GetAndDeletePipeResponse( $kPipeRow );
        }
*/

        return( $ra );
    }
}


class SEDMbrGrower extends SEDGrowerWorker
{
    function __construct( &$oC, &$kfdb, &$sess )
    {
        parent::__construct( $oC, $kfdb, $sess );
    }

    function DrawGrowerControl()
    {
        return( "" ); //GrowerControl" );
    }

    function DrawGrowerContent( $kGrower )
    {
        $oSed = $this->oC->oSed;

        if( !$kGrower || !$this->oC->oMSDLib->PermOfficeW() ) {
            $kGrower = $this->oC->sess->GetUID();
        }

        $oKForm = $this->NewGrowerForm();
        /******
 * The mbr_id is passed through http, but DSPreStore checks that it is the same as $sess->GetUID() to prevent cross-user hacks.
 */
        $oKForm->Update();



        $kfrG = $oSed->kfrelG->GetRecordFromDB( "mbr_id='$kGrower'" );
        if( !$kfrG ) {
            if( !$this->oC->sess->GetUID() )  die( "You have to be logged in to list seeds in the Member Seed Directory" );

            $s = "<h4>Hello ".$this->oC->sess->GetName()."</h4>"
                ."<p>This is your first time listing seeds in our Member Seed Directory. "
                ."Please fill in the form below to register as a seed grower. <br/>"
                ."After that, you will be able to enter the seeds that you want to offer to other Seeds of Diversity members.</p>"
                ."<p>Thanks for sharing your seeds!</p>";

            // box showing our membership info
            $raMbr = $oSed->GetMbrContactsRA( $kGrower );  // derived class fetches mbr_contacts row
            $s .= SEEDStd_ArrayExpand( $raMbr, "<div style='border:1px solid #aaa;margin:10px;padding:10px;float:right'>"
                                              ."<b>[[firstname]] [[lastname]] [[company]] (member [[_key]])</b><br/>"
                                              ."[[address]], [[city]] [[province]] [[postcode]]<br/>"
                                              ."[[phone]]<br/>"
                                              ."[[email]]"
                                              ."</div>" );


            $kfrG = $oSed->kfrelG->CreateRecord();
            $kfrG->SetValue( 'mbr_id', $kGrower );
            $oKForm->SetKFR( $kfrG );

            $s .= "<FORM method='post' action='${_SERVER['PHP_SELF']}'>"
            // N.B. DSPreStore prevents cross-user hacks
          .$oKForm->Hidden('mbr_id')
          ."<DIV style='border:1px solid black; margin:10px; padding:10px'>"  // console01 does this style in the office app
          .$oSed->drawGrowerForm( $oKForm )
          ."</DIV>"
          ."</FORM>";

            return( $s );
        }


        if( ($k = SEEDSafeGPC_GetInt( 'gdone' )) && $k == $kGrower ) {
            $kfrG->SetValue( 'bDone', !$kfrG->value('bDone') );
            $kfrG->SetValue( 'bDoneMbr', $kfrG->value('bDone') );  // make this match bDone
            if( !$kfrG->PutDBRow() ) {
                die( "<P style='color:red'>Update didn't work.  Please email this message to Bob:</P>".$kfrG->kfrel->kfdb->GetErrMsg() );
            }
        }

//necessary?
        $oKForm->SetKFR( $kfrG );

        $s = "<TABLE cellpadding='0' cellspacing='0' border='0'><TR valign='top'>"
            ."<TD width='50%'>"
            ."<P>".$oSed->S('Grower block heading')."</P>"
            ."<DIV class='sed_grower' ".($oKForm->oDS->Value('bDone') ? "style='color:green;background:#cdc;'" : "").">"
            .$oSed->drawGrowerBlock( $kfrG )
            ."</DIV>"
            .($oKForm->oDS->Value('bDone') ? "<P style='font-size:16pt;margin-top:20px;'>Done! Thank you!</P>" : "")
            ."<P><A href='${_SERVER['PHP_SELF']}?gdone=".$kGrower."'>"
                .($oKForm->oDS->Value('bDone')
                     ? "Click here if you're not really done"
                     : $oSed->S("Click here when you are done"))
            ."</A></P>"
            ."</TD>"
            ."<TD>"
            ."<FORM method='post' action='${_SERVER['PHP_SELF']}'>"
            // N.B. DSPreStore prevents cross-user hacks
          .$oKForm->HiddenKey()
          ."<DIV style='border:1px solid black; margin:10px; padding:10px'>"  // console01 does this style in the office app
          .$oSed->drawGrowerForm( $oKForm )
          ."</DIV>"
          ."</FORM>"
          ."</TD>"
          ."</TR></TABLE>";

        return( $s );
    }
}


class MyConsole extends Console01
{
    public  $oApp;
    public  $oW;
    public  $oSed;

    private $kCurrGrower;
    public $oMSDLib;

    function __construct( SEDMbr $oSed, SEEDAppConsole $oApp, $raParms )
    {
        $this->oSed = $oSed;
        $this->oApp = $oApp;
        parent::__construct( $oSed->kfdb, $oSed->sess, $raParms );

        $this->oMSDLib = new MSDLib( $oApp );

        $this->kCurrGrower = $this->oMSDLib->PermOfficeW() ? $this->oSVA->SmartGPC( 'selectGrower', array($this->oApp->sess->GetUID()) )
                                                           : $this->oApp->sess->GetUID();

        if( $this->oMSDLib->PermOfficeW() )  $this->oSed->bOffice = true;
    }

    function TabSetInit( $tsid, $tabname )
    {
        switch( $tabname ) {
            case 'Growers':  $this->oW = new SEDMbrGrower( $this, $this->kfdb, $this->sess );  break;
            case 'Seeds':    $this->oSB = new SEEDBasketCore( $this->oApp->kfdb, $this->oApp->sess, $this->oApp,
                                                              SEEDBasketProducts_SoD::$raProductTypes, array('logdir'=>SITE_LOG_ROOT) );  break;
        }
    }

/*
should be okay to open any tab
    function TabSetPermission( $tsid, $tabname )
    {
        if( $tsid == 'main' ) {
            return( $this->oSed->sess->TestPerm( 'sed', 'W' ) ? 1 : 0 );
        }
        return( 0 );
    }
*/
    function TabSetControlDraw( $tsid, $tabname )
    {
        switch( $tabname ) {
            case 'Growers':  return( $this->oMSDLib->PermOfficeW() ? $this->growerSelect() : "" );
            case 'Seeds':    return( $this->oMSDLib->PermOfficeW() ? $this->growerSelect() : "" );
        }
        return( "" );
    }

    function TabSetContentDraw( $tsid, $tabname )
    {
        switch( $tabname ) {
            case 'Growers':  return( $this->oW->DrawGrowerContent( $this->kCurrGrower ) );
            case 'Seeds':
                $oMSDAppSeedEdit = new MSDAppSeedEdit( $this->oSB );
                return( $oMSDAppSeedEdit->Draw( $this->kCurrGrower ) );
        }
        return( "" );
    }

    private function growerSelect()
    {
        $raG = $this->oApp->kfdb->QueryRowsRA1( "SELECT mbr_id FROM seeds.sed_curr_growers WHERE _status='0'" );
        $raG2 = array( '-- Grower --' => 0 );
        foreach( $raG as $kMbr ) {
            $ra = $this->oApp->kfdb->QueryRA( "SELECT firstname,lastname,company FROM seeds2.mbr_contacts WHERE _key='$kMbr'" );
            if( !($name = trim($ra['firstname'].' '.$ra['lastname'])) ) {
                if( !($name = $ra['company']) ) {
                    continue;
                }
            }
            if( $this->TabSetGetCurrentTab( 'main' ) != 'Growers' )  $name = utf8_encode(trim($name));
            $raG2["$name ($kMbr)"] = $kMbr;
        }
        ksort($raG2);
        $oForm = new SEEDCoreForm( 'Plain' );
        return( "<form method='post'>".$oForm->Select( 'selectGrower', $raG2, "", array('selected'=>$this->kCurrGrower, 'attrs'=>"onChange='submit();'") )."</form>" );
    }
}


$oSed = new SEDMbr( $kfdb, $sess, $lang );
//$oSed->update();

$raConsoleParms = array(
    'HEADER' => $oSed->S("MSD title"),
    'CONSOLE_NAME' => "msdedit",
//    'HEADER_LINKS' => array( array( 'href' => 'mbr_email.php',    'label' => "Email Lists",  'target' => '_blank' ),
//                             array( 'href' => 'mbr_mailsend.php', 'label' => "Send 'READY'", 'target' => '_blank' ) ),

    'TABSETS' => array( "main" => array( 'tabs' => array( 'Growers' => array( 'label' => $oSed->S("Tab G") ),
                                                          'Seeds'   => array( 'label' => $oSed->S("Tab S") ) ) ) ),
    'lang' => $lang,
    'bBootstrap' => true,
    'script_files' => array( W_ROOT."std/js/SEEDStd.js", W_CORE."js/SEEDCore.js" ),
    'sCharset' => 'utf-8'
);
$oC = new MyConsole( $oSed, $oApp, $raConsoleParms );

if( $oC->TabSetGetCurrentTab( 'main' ) == 'Growers' ) {
    $oC->SetConfig( array( 'sCharset' => 'cp1252' ) );
}

echo $oC->DrawConsole( $oSed->SEDStyle()."[[TabSet: main]]" );

?>