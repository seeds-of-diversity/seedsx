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

list($kfdb, $sess, $lang) = SiteStartSessionAccount( ["W sed"] );

$oApp = new SEEDAppConsole( $config_KFDB['seeds1']
                            + array( 'sessPermsRequired' => array(["W sed"]),
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

    function UpdateGrower( $kCurrGrower )
    {
        // Do this in TabSetInit so the list is correct in TabSetControlDraw

        $kfrG = $this->oC->oSed->kfrelG->GetRecordFromDB( "mbr_id='$kCurrGrower'" );

        if( ($k = SEEDSafeGPC_GetInt( 'gdone' )) && $k == $kCurrGrower ) {
            $kfrG->SetValue( 'bDone', !$kfrG->value('bDone') );
            $kfrG->SetValue( 'bDoneMbr', $kfrG->value('bDone') );  // make this match bDone
            if( !$kfrG->PutDBRow() ) {
                die( "<P style='color:red'>Update didn't work.  Please email this message to Bob:</P>".$kfrG->kfrel->kfdb->GetErrMsg() );
            }
        }
        if( ($k = SEEDSafeGPC_GetInt( 'gskip' )) && $k == $kCurrGrower ) {
            $kfrG->SetValue( 'bSkip', !$kfrG->value('bSkip') );
            if( !$kfrG->PutDBRow() ) {
                die( "<P style='color:red'>Update didn't work.  Please email this message to Bob:</P>".$kfrG->kfrel->kfdb->GetErrMsg() );
            }
        }
        if( ($k = SEEDSafeGPC_GetInt( 'gdelete' )) && $k == $kCurrGrower ) {
            $kfrG->SetValue( 'bDelete', !$kfrG->value('bDelete') );
            if( !$kfrG->PutDBRow() ) {
                die( "<P style='color:red'>Update didn't work.  Please email this message to Bob:</P>".$kfrG->kfrel->kfdb->GetErrMsg() );
            }
        }
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

//necessary?
        $oKForm->SetKFR( $kfrG );

        $sLeft = "<h3>".$kfrG->value('mbr_code')." : ".$this->oC->GetGrowerName($kGrower)."</h3>"
                ."<p>".$oSed->S('Grower block heading')."</p>"
                ."<div class='sed_grower' ".($oKForm->oDS->Value('bDone') ? "style='color:green;background:#cdc;'" : "").">"
                .$oSed->drawGrowerBlock( $kfrG )
                ."</div>"
                .($oKForm->oDS->Value('bDone') ? "<p style='font-size:16pt;margin-top:20px;'>Done! Thank you!</p>" : "")
                ."<p><a href='${_SERVER['PHP_SELF']}?gdone=".$kGrower."'>"
                    .($oKForm->oDS->Value('bDone')
                        ? "Click here if you're not really done"
                        : $oSed->S("Click here when you are done"))
                ."</a></p>"
                .($this->oC->oSed->bOffice ? $this->drawGrowerOfficeSummary( $kfrG ) : "");

        $sRight = "<form method='post' action='${_SERVER['PHP_SELF']}'>"
                  // N.B. DSPreStore prevents cross-user hacks
                 .$oKForm->HiddenKey()
                 ."<div style='border:1px solid black; margin:10px; padding:10px'>"
                 .$oSed->drawGrowerForm( $oKForm )
                 ."</div>"
                 ."</form>";


        $s = "<div class='container-fluid><div class='row'>"
            ."<div class='col-lg-6'>$sLeft</div>"
            ."<div class='col-lg-6'>$sRight</div>"
            ."</div></div>";

        return( $s );
    }

    private function drawGrowerOfficeSummary( KFRecord $kfrG )
    {
        $kGrower = $kfrG->Value('mbr_id');

        // Grower record
        $dGUpdated = substr( $kfrG->Value('_updated'), 0, 10 );
        $kGUpdatedBy = $kfrG->Value('_updated_by');

        // Seed records
        $ra = $this->oC->oApp->kfdb->QueryRA(
                "SELECT _updated,_updated_by FROM
                     (
                     (SELECT _updated,_updated_by FROM seeds.SEEDBasket_Products
                         WHERE product_type='seeds' AND _status='0' AND
                               uid_seller='$kGrower' ORDER BY _updated DESC LIMIT 1)
                     UNION
                     (SELECT PE._updated,PE._updated_by FROM seeds.SEEDBasket_ProdExtra PE,seeds.SEEDBasket_Products P
                         WHERE P.product_type='seeds' AND _status='0' AND
                               P.uid_seller='$kGrower' AND P._key=PE.fk_SEEDBasket_Products ORDER BY 1 DESC LIMIT 1)
                     ) as A
                 ORDER BY 1 DESC LIMIT 1" );
        $dSUpdated = @$ra['_updated'];
        $kSUpdatedBy = @$ra['_updated_by'];

        $nSActive = $this->oC->oApp->kfdb->Query1( "SELECT count(*) FROM seeds.SEEDBasket_Products
                                                    WHERE product_type='seeds' AND _status='0' AND
                                                          uid_seller='$kGrower' AND eStatus='ACTIVE'" );

        $dMbrExpiry = $this->oC->oApp->kfdb->Query1( "SELECT expires FROM seeds2.mbr_contacts WHERE _key='$kGrower'" );

        $sSkip = $kfrG->Value('bSkip')
                    ? ("<div style='background-color:#ee9'><span style='font-size:12pt'>Skipped</span>"
                      ." <a href='{$_SERVER['PHP_SELF']}?gskip=$kGrower'>Unskip this grower</a></div>")
                    : ("<div><a href='{$_SERVER['PHP_SELF']}?gskip=$kGrower'>Skip this grower</a></div>");
        $sDel = $kfrG->Value('bDelete')
                    ? ("<div style='background-color:#fdf'><span style='font-size:12pt'>Deleted</span>"
                      ." <a href='{$_SERVER['PHP_SELF']}?gdelete=$kGrower'>UnDelete this grower</a></div>")
                    : ("<div><a href='{$_SERVER['PHP_SELF']}?gdelete=$kGrower'>Delete this grower</a></div>");

        $s = "<div style='border:1px solid black; margin:10px; padding:10px'>"
            ."<p>Seeds active: $nSActive</p>"
            ."<p>Membership expiry: $dMbrExpiry</p>"
            ."<p>Last grower record change: $dGUpdated by $kGUpdatedBy</p>"
            ."<p>Last seed record change: $dSUpdated by $kSUpdatedBy</p>"
            .$sSkip
            .$sDel
            ."</div>";

        return( $s );
    }
}


class MyConsole extends Console01
{
    public  $oApp;
    public  $oW;
    public  $oSed;

    private $kCurrGrower;
    private $kCurrSpecies;
    public $oMSDLib;

    function __construct( SEDMbr $oSed, SEEDAppConsole $oApp, $raParms )
    {
        $this->oSed = $oSed;
        $this->oApp = $oApp;
        parent::__construct( $oSed->kfdb, $oSed->sess, $raParms );

        $this->oMSDLib = new MSDLib( $oApp );

        if( $this->oMSDLib->PermOfficeW() ) {
            $this->oSed->bOffice = true;

            $this->kCurrGrower = $this->oSVA->SmartGPC( 'selectGrower', array(0) );
            $this->kCurrSpecies = intval($this->oSVA->SmartGPC( 'selectSpecies', array(0) ));
        } else {
            $this->kCurrGrower = $this->oApp->sess->GetUID();
            $this->kCurrSpecies = 0;   // all species
        }
    }

    function TabSetInit( $tsid, $tabname )
    {
        switch( $tabname ) {
            case 'Growers':
                $this->oW = new SEDMbrGrower( $this, $this->kfdb, $this->sess );
                $this->oW->UpdateGrower( $this->kCurrGrower );
                break;
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
        $s = "";

        if( !$this->oMSDLib->PermOfficeW() ) goto done;

        switch( $tabname ) {
            case 'Growers':
                $s = $this->growerSelect();
                break;
            case 'Seeds':
                $s = $this->growerSelect();
                if( $this->kCurrSpecies ) {
                    $s .= "<div style='margin-top:10px'><strong>Showing ".$this->oMSDLib->GetSpeciesNameFromKey($this->kCurrSpecies)."</strong>"
                         ." <a href='{$_SERVER['PHP_SELF']}?selectSpecies=0'><button type='button'>Cancel</button></div>";
                }
                break;
        }

        done:
        return( $s );
    }

    function TabSetContentDraw( $tsid, $tabname )
    {
        switch( $tabname ) {
            case 'Growers':  return( $this->oW->DrawGrowerContent( $this->kCurrGrower ) );
            case 'Seeds':
                $oMSDAppSeedEdit = new MSDAppSeedEdit( $this->oSB );
                return( $oMSDAppSeedEdit->Draw( $this->kCurrGrower, $this->kCurrSpecies ) );
        }
        return( "" );
    }

    function GetGrowerName( $kGrower )
    {
        $ra = $this->oApp->kfdb->QueryRA( "SELECT firstname,lastname,company FROM seeds2.mbr_contacts WHERE _key='$kGrower'" );
        if( !($name = trim($ra['firstname'].' '.$ra['lastname'])) ) {
            $name = $ra['company'];
        }
        return( $name );
    }

    private function growerSelect()
    {
        $raG = $this->oApp->kfdb->QueryRowsRA( "SELECT mbr_id,bSkip,bDelete,bDone FROM seeds.sed_curr_growers WHERE _status='0'" );
        $raG2 = array( '-- All Growers --' => 0 );
        foreach( $raG as $ra ) {
            $kMbr = $ra['mbr_id'];
            $bSkip = $ra['bSkip'];
            $bDelete = $ra['bDelete'];
            $bDone = $ra['bDone'];

            $name = $this->GetGrowerName( $kMbr )
                   ." ($kMbr)"
                   .($bDone ? " - Done" : "")
                   .($bSkip ? " - Skipped" : "")
                   .($bDelete ? " - Deleted" : "");
            if( $this->TabSetGetCurrentTab( 'main' ) != 'Growers' )  $name = utf8_encode(trim($name));
            $raG2[$name] = $kMbr;
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