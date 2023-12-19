<?php

define( "SITEROOT", "../../" );
include( SITEROOT."site.php" );
include( SEEDAPP."seedexchange/app_mse-edit.php" );


/*
    This is the old console01 code.


$oSed = new SEDMbr( $kfdb, $sess, $lang );
$oSed->update();

class SEDMbr extends SEDCommon  // the member-access derivation of SED object
{
    function __construct( KeyFrameDB $kfdb1, SEEDSessionAccount $sess, $lang )
    {
        parent::__construct( $kfdb1, $sess, $lang, 'EDIT' );    // user is logged in
        $this->kGrowerActive = $sess->GetUID();    // always only show the session user's info and seeds
    }

    function GetMbrContactsRA( $kMbr )
    {
        $ra = MbrSitePipeGetContactsRA2( $this->kfdb, $kMbr );

        return( $ra );
    }
}

class SEDMbrGrower extends MSEEditAppTabGrower
{
    public $oC;
    public $kfdb;
    public $sess;

    function __construct( $oC, $oApp, $kfdb, $sess )
    {
        $this->oC   = $oC;
        $this->kfdb = $kfdb;
        $this->sess = $sess;

        parent::__construct( $oApp );
    }
}
*/

/*
class MyConsole extends Console01
{
    public  $oApp;
    public  $oW;
    public  $oSed;

    private $kCurrGrower;
    private $kCurrSpecies;
    public $oMSDLib;

    function __construct( SEDMbr $oSed, SEEDAppConsole $oApp, MSDLib $oMSDLib, $raParms )
    {
        $this->oSed = $oSed;
        $this->oApp = $oApp;
        parent::__construct( $oSed->kfdb, $oSed->sess, $raParms );

        $this->oMSDLib = $oMSDLib;

        if( $this->oMSDLib->PermOfficeW() ) {
            $this->oSed->bOffice = true;

            $this->kCurrGrower = $this->oSVA->SmartGPC( 'selectGrower', [0] );
        } else {
            $this->kCurrGrower = $this->oApp->sess->GetUID();
            $this->kCurrSpecies = 0;   // all species
        }
        $this->kCurrSpecies = $this->oSVA->SmartGPC( 'selectSpecies', [0] );    // normally an int, but can be tomatoAC, tomatoDH, etc

        // Growers and Office are cp1252, but make sure '' is too. Growers was being rendered in utf-8 on initialization, which led some members to enter notes in that charset.
        $this->klugeThisPageIsUTF8 = ($this->TabSetGetCurrentTab('main') == 'Seeds');
    }

    function TabSetInit( $tsid, $tabname )
    {
        switch( $tabname ) {
            case 'Growers':
                $this->oW = new SEDMbrGrower( $this, $this->oApp, $this->kfdb, $this->sess );
                $this->oW->Init_Grower( $this->kCurrGrower );
                break;
            case 'Seeds':
                $this->oW = new MSEEditAppTabSeeds($this->oApp);
                $this->oW->Init_Seeds( $this->kCurrGrower, $this->kCurrSpecies );
                break;
            case 'Edit':
                $this->oW = new MSDOfficeEditTab( $this->oMSDLib );
                break;
            case 'Office':
                $this->oW = new MSDAdminTab( $this->oMSDLib );
                break;

            case 'Archive':
//select A.year,nGrowers,nSeeds, nSeeds/nGrowers
//  from (select year,count(*) as nGrowers from sed_growers group by 1) as A,
//       (select year,count(*) as nSeeds from sed_seeds group by 1) as B
//  where A.year=B.year order by 1;
                break;
        }
    }

    function TabSetPermission( $tsid, $tabname )
    {
        switch( $tabname ) {
            case 'Growers':
            case 'Seeds':
                return( 1 );
            case 'Edit':
            case 'Office':
                return( $this->oMSDLib->PermOfficeW() ? 1 : 0 );
        }
        return( 0 );
    }

    function TabSetControlDraw( $tsid, $tabname )
    {
        $s = "";

        switch( $tabname ) {
            case 'Growers':     $s = $this->oW->ControlDraw_Grower();       break;
            case 'Seeds':       $s = $this->oW->ControlDraw_Seeds();        break;
            case 'Office':
                if( $this->oMSDLib->PermOfficeW() ) {
                    $s = $this->oW->DrawControl();      // MSDOfficeEditTab or MSDAdminTab
                }
                break;
        }

        done:
        return( $s );
    }

    function TabSetContentDraw( $tsid, $tabname )
    {
        $s = "";

        switch( $tabname ) {
            case 'Growers':     $s = $this->oW->ContentDraw_Grower();       break;
            case 'Seeds':       $s = $this->oW->ContentDraw_Seeds();        break;
            case 'Edit':
            case 'Office':
                if( $this->oMSDLib->PermOfficeW() ) {
                    $s = $this->oW->DrawContent();
                }
                break;
        }
        return( $s );
    }
}
*/

/*
$raConsoleParms = array(
    'HEADER' => $oSed->S("MSD title"),
    'CONSOLE_NAME' => "msdedit",
//    'HEADER_LINKS' => array( array( 'href' => 'mbr_email.php',    'label' => "Email Lists",  'target' => '_blank' ),
//                             array( 'href' => 'mbr_mailsend.php', 'label' => "Send 'READY'", 'target' => '_blank' ) ),

    'TABSETS' => array( "main" => array( 'tabs' => array( 'Growers' => array( 'label' => $oMSDLib->oTmpl->ExpandTmpl("MSEEdit_tablabel_G") ),
                                                          'Seeds'   => array( 'label' => $oMSDLib->oTmpl->ExpandTmpl("MSEEdit_tablabel_S") ),
                                                          'Edit'    => array( 'label' => "Edit" ),
                                                          'Office'  => array( 'label' => "Office" ) ) ) ),
    'lang' => $lang,
    'bBootstrap' => true,
    'css_files' => [ W_CORE."css/console02.css" ],
    'script_files' => [ W_ROOT."std/js/SEEDStd.js", W_CORE."js/SEEDCore.js", W_CORE."js/console02.js",W_CORE."js/SEEDPopover.js" ],
    'sCharset' => 'utf-8'
);
$oC = new MyConsole( $oSed, $oApp, $oMSDLib, $raConsoleParms );

if( !$oC->klugeThisPageIsUTF8 ) {
    $oC->SetConfig( ['sCharset' => 'cp1252'] );
}

echo $oC->DrawConsole( "[[TabSet: main]]" );
*/
