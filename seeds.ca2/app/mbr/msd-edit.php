<?php

/*
 * Seed Directory member interface
 *
 * Copyright 2011-2022 Seeds of Diversity Canada
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

        switch( $tabname ) {
            case 'Growers':     $s = $this->oW->ControlDraw_Grower();       break;
            case 'Seeds':       $s = $this->oW->ControlDraw_Seeds();        break;
            case 'Office':
                if( $this->oMSDLib->PermOfficeW() ) {
                    $s = $this->oW->DrawControl();
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
            case 'Office':
                if( $this->oMSDLib->PermOfficeW() ) {
                    $s = $this->oW->DrawContent();
                }
                break;
        }
        return( $s );
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
    'script_files' => [ W_ROOT."std/js/SEEDStd.js", W_CORE."js/SEEDCore.js", W_CORE."js/console02.js",W_ROOT."std/js/SEEDPopover.js" ],
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

?>
<style>
.popover { width: 20%; }    /* when container:'body' specified ths makes the popover 20% of the window width */
</style>
<script type='text/javascript'>
$(document).ready( function() {
    SEEDPopover();
});

SEEDPopover_Def = {
	mbr_code:
	    { placement:'right', trigger: 'hover', container: 'body',
	      title:   "Member Code",
		  content: "We use this in the printed seed directory as a shorthand code to identify you. If you don't have one yet, our office will set it up for you."
		},
	unlisted:
	    { placement:'right', trigger: 'hover', container: 'body',
	      title:   "Unlisted email/phone",
		  content: "You can keep your email address or phone number hidden from other members. Remember that you might want them to contact you though."
		},
	frost_free:
	    { placement:'right', trigger: 'hover', container: 'body',
	      title:   "Frost free days",
		  content: "Estimate the typical length of your season from last spring frost to first fall frost. This helps other members know whether their season is compatible."
		},
	organic:
	    { placement:'right', trigger: 'hover', container: 'body',
	      title:   "Organic seeds",
		  content: "Your seeds don't necessarily have to be certified organic. Members just want to know if you avoid chemicals in your garden."
		}
};

</script>
