<?php

/*
 * Seed Directory member interface
 *
 * Copyright 2011-2021 Seeds of Diversity Canada
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
include_once( SEEDAPP."seedexchange/msdadmin.php" );
include_once( SEEDLIB."msd/msdlib.php" );

list($kfdb, $sess, $lang) = SiteStartSessionAccount( ["W sed"] );

$oApp = SEEDConfig_NewAppConsole( ['db'=>'seeds1', 'sessPermsRequired' => ["W sed"], 'lang' => $lang ] );

SEEDPRG();

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

        return( $ra );
    }
}


class SEDMbrGrower extends SEDGrowerWorker
{
    private $oApp;
    private $oMSDLib;

    function __construct( $oC, $oApp, $kfdb, $sess )
    {
        parent::__construct( $oC, $kfdb, $sess );
        $this->oApp = $oApp;
        $this->oMSDLib = new MSDLib($oApp);
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
        $sLeft = "";

        if( !$kGrower || !$this->oC->oMSDLib->PermOfficeW() ) {
            $kGrower = $this->oApp->sess->GetUID();
        }

        $oGrowerForm = new MSDAppGrowerForm( $this->oMSDLib, $this->oC->oSed->currentYear, $this->oC->oSed->bOffice );
        $oGrowerForm->Update();
        // Fetch kfrGxM if there's a grower record for kGrower, or just get an empty kfrG if not
        $oGrowerForm->SetKGrower( $kGrower );

        if( !($oGrowerForm->Value('M__key')) ) {
// also show this if zero seeds have been entered and this is their first year
            $sLeft .=
                  "<h4>Hello ".$this->oApp->sess->GetName()."</h4>"
                 ."<p>This is your first time listing seeds in our Member Seed Exchange. "
                 ."Please fill in this form to register as a seed grower. <br/>"
                 ."After that, you will be able to enter the seeds that you want to offer to other Seeds of Diversity members.</p>"
                 ."<p>Thanks for sharing your seeds!</p>";
        }

        $sLeft .= "<h3>".$oGrowerForm->value('mbr_code')." : ".$this->oC->GetGrowerName($kGrower)."</h3>"
                ."<p>".$this->oC->oSed->S('Grower block heading')."</p>"
                ."<div class='sed_grower' ".($oGrowerForm->Value('bDone') ? "style='color:green;background:#cdc;'" : "").">"
                .$this->oMSDLib->DrawGrowerBlock( $oGrowerForm->GetKFR(), true )
                ."</div>"
                .($oGrowerForm->Value('bDone') ? "<p style='font-size:16pt;margin-top:20px;'>Done! Thank you!</p>" : "")
                ."<p><a href='{$this->oMSDLib->oApp->PathToSelf()}?gdone=$kGrower'>"
                    .($oGrowerForm->Value('bDone')
                        ? "Click here if you're not really done"
                        : "<div class='alert alert-warning'><h3>Your seed listings are not active yet</h3> Click here when you are ready (you can undo this)</div>")
                ."</a></p>"
                .($this->oC->oSed->bOffice ? $this->drawGrowerOfficeSummary( $oGrowerForm->GetKFR() ) : "");

        $sRight = "<div style='border:1px solid black; margin:10px; padding:10px'>"
                 .$oGrowerForm->DrawGrowerForm()
                 ."</div>";


        $s = "<div class='container-fluid><div class='row'>"
            ."<div class='col-lg-6'>$sLeft</div>"
            ."<div class='col-lg-6'>$sRight</div>"
            ."</div></div>";

        return( $s );
    }

    private function drawGrowerOfficeSummary( KeyframeRecord $kfrG )
    {
        $kGrower = $kfrG->Value('mbr_id');

        // Grower record
        $dGUpdated = substr( $kfrG->Value('_updated'), 0, 10 );
        $kGUpdatedBy = $kfrG->Value('_updated_by');

        // Seed records
/*
        $ra = $this->oC->oApp->kfdb->QueryRA(
                "SELECT _updated,_updated_by FROM
                     (
                     (SELECT _updated,_updated_by FROM seeds_1.SEEDBasket_Products
                         WHERE product_type='seeds' AND _status='0' AND
                               uid_seller='$kGrower' ORDER BY _updated DESC LIMIT 1)
                     UNION
                     (SELECT PE._updated,PE._updated_by FROM seeds_1.SEEDBasket_ProdExtra PE,seeds_1.SEEDBasket_Products P
                         WHERE P.product_type='seeds' AND _status='0' AND
                               P.uid_seller='$kGrower' AND P._key=PE.fk_SEEDBasket_Products ORDER BY 1 DESC LIMIT 1)
                     ) as A
                 ORDER BY 1 DESC LIMIT 1" );
        $dSUpdated = @$ra['_updated'];
        $kSUpdatedBy = @$ra['_updated_by'];
*/
        list($kP_dummy,$dSUpdated,$kSUpdatedBy) = $this->oC->oSB->oDB->ProductLastUpdated( "P.product_type='seeds' AND P.uid_seller='$kGrower'" );

        $nSActive = $this->oApp->kfdb->Query1( "SELECT count(*) FROM {$this->oApp->DBName('seeds1')}.SEEDBasket_Products
                                                WHERE product_type='seeds' AND _status='0' AND
                                                      uid_seller='$kGrower' AND eStatus='ACTIVE'" );

        $dMbrExpiry = $this->oApp->kfdb->Query1( "SELECT expires FROM {$this->oApp->DBName('seeds2')}.mbr_contacts WHERE _key='$kGrower'" );

        $sSkip = $kfrG->Value('bSkip')
                    ? ("<div style='background-color:#ee9'><span style='font-size:12pt'>Skipped</span>"
                      ." <a href='{$_SERVER['PHP_SELF']}?gskip=$kGrower'>Unskip this grower</a></div>")
                    : ("<div><a href='{$_SERVER['PHP_SELF']}?gskip=$kGrower'>Skip this grower</a></div>");
        $sDel = $kfrG->Value('bDelete')
                    ? ("<div style='background-color:#fdf'><span style='font-size:12pt'>Deleted</span>"
                      ." <a href='{$_SERVER['PHP_SELF']}?gdelete=$kGrower'>UnDelete this grower</a></div>")
                    : ("<div><a href='{$_SERVER['PHP_SELF']}?gdelete=$kGrower'>Delete this grower</a></div>");

        try {
            // days since GUpdate
            if( (new DateTime())->diff(new DateTime($dGUpdated))->days < 90 ) {
                $dGUpdated = "<span style='color:green;background-color:#cdc'>$dGUpdated</span>";
            }
            // days since SUpdate
            if( (new DateTime())->diff(new DateTime($dSUpdated))->days < 90 ) {
                $dSUpdated = "<span style='color:green;background-color:#cdc'>$dSUpdated</span>";
            }
        } catch (Exception $e) {}

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
    public $oSB;

    function __construct( SEDMbr $oSed, SEEDAppConsole $oApp, $raParms )
    {
        $this->oSed = $oSed;
        $this->oApp = $oApp;
        parent::__construct( $oSed->kfdb, $oSed->sess, $raParms );

        $this->oSB = new SEEDBasketCore( null, null, $this->oApp, SEEDBasketProducts_SoD::$raProductTypes, [] );
        $this->oMSDLib = new MSDLib( $oApp );

        if( $this->oMSDLib->PermOfficeW() ) {
            $this->oSed->bOffice = true;

            $this->kCurrGrower = $this->oSVA->SmartGPC( 'selectGrower', array(0) );
            $this->kCurrSpecies = intval($this->oSVA->SmartGPC( 'selectSpecies', array(0) ));
        } else {
            $this->kCurrGrower = $this->oApp->sess->GetUID();
            $this->kCurrSpecies = 0;   // all species
        }

        // Growers and Office are cp1252, but make sure '' is too. Growers was being rendered in utf-8 on initialization, which led some members to enter notes in that charset.
        $this->klugeThisPageIsUTF8 = ($this->TabSetGetCurrentTab('main') == 'Seeds');
    }

    function TabSetInit( $tsid, $tabname )
    {
        switch( $tabname ) {
            case 'Growers':
                $this->oW = new SEDMbrGrower( $this, $this->oApp, $this->kfdb, $this->sess );
                $this->oW->UpdateGrower( $this->kCurrGrower );
                break;
            case 'Seeds':
                break;
            case 'Office':
                $this->oW = new MSDAdminTab( $this->oMSDLib );
                break;
        }
    }

    function TabSetPermission( $tsid, $tabname )
    {
        switch( $tabname ) {
            case 'Growers':
            case 'Seeds':
                return( 1 );
            case 'Office':
                return( $this->oMSDLib->PermOfficeW() ? 1 : 0 );
        }
        return( 0 );
    }

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
            case 'Office':
                $s = $this->oW->DrawControl();
                break;
        }

        done:
        return( $s );
    }

    function TabSetContentDraw( $tsid, $tabname )
    {
        switch( $tabname ) {
            case 'Growers':
                return( $this->oW->DrawGrowerContent( $this->kCurrGrower ) );
            case 'Seeds':
                $oMSDAppSeedEdit = new MSDAppSeedEdit( $this->oSB );
                return( $oMSDAppSeedEdit->Draw( $this->kCurrGrower, $this->kCurrSpecies ) );
            case 'Office':
                return( $this->oW->DrawContent() );
        }
        return( "" );
    }

    function GetGrowerName( $kGrower )
    {
        $oMbr = new Mbr_Contacts($this->oApp);
        return( $oMbr->GetContactName($kGrower) );
    }

    private function growerSelect()
    {
        $raG = $this->oApp->kfdb->QueryRowsRA( "SELECT mbr_id,bSkip,bDelete,bDone FROM {$this->oApp->GetDBName('seeds1')}.sed_curr_growers WHERE _status='0'" );
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
            if( $this->klugeThisPageIsUTF8 )  $name = SEEDCore_utf8_encode(trim($name));    // Seeds is utf8 but Growers isn't
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
                                                          'Seeds'   => array( 'label' => $oSed->S("Tab S") ),
                                                          'Office'  => array( 'label' => "Office" ) ) ) ),
    'lang' => $lang,
    'bBootstrap' => true,
    'css_files' => [ W_CORE."css/console02.css" ],
    'script_files' => [ W_ROOT."std/js/SEEDStd.js", W_CORE."js/SEEDCore.js", W_CORE."js/console02.js" ],
    'sCharset' => 'utf-8'
);
$oC = new MyConsole( $oSed, $oApp, $raConsoleParms );

if( !$oC->klugeThisPageIsUTF8 ) {
    $oC->SetConfig( ['sCharset' => 'cp1252'] );
}

// Output reports in this window with no console.
// MSDLibReport sets header(charset) based on the format of report
if( $oC->oMSDLib->PermOfficeW() && SEEDInput_Str('doReport') ) {
    include_once( SEEDLIB."msd/msdlibReport.php" );
    echo (new MSDLibReport($oC->oMSDLib))->Report();
    exit;
}

echo $oC->DrawConsole( $oSed->SEDStyle()."[[TabSet: main]]" );
