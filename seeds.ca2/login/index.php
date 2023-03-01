<?php

/* Main Login page for seeds.ca
 */

include_once( "../site.php" );
include_once( SEEDAPP."website/login.php" );
include_once( SEEDLIB."SEEDTemplate/masterTemplate.php" );

$oApp = SEEDConfig_NewAppConsole( ['db'=>'seeds1'] );       // requires a valid login, but no specific permissions

SEEDPRG();

$oLP = new SEEDLoginPage($oApp);

$oTmpl = (new SoDMasterTemplate( $oApp, [] ))->GetTmpl();

$kMbr = $oApp->sess->GetUID();

$sRight = "";

/* Membership box
 */
$sRight .= $oTmpl->ExpandTmpl("SeedUI-Box1", ['sTitle'=>'Your Membership', 'sContent'=>"You are member #{$kMbr}"])
          ."<br/>";

/* Donations box
 */
include( SEEDLIB."mbr/MbrDonations.php" );
$oDon = new MbrDonations($oApp);
if( $kMbr && ($sMbrReceiptsLinks = $oDon->DrawReceiptLinks($kMbr)) ) {
    $sMbrReceiptsLinks = "<p>Thanks so much for your support of Seeds of Diversity!<br/> Click below to download your official donation receipts</p>
                          <div style='margin:10px; padding:10px; background-color:#eee;text-align:left'>$sMbrReceiptsLinks</div>";
    $sRight .= $oTmpl->ExpandTmpl( "SeedUI-Box1Expandable", array( 'sTitle'=>'Your Donations', 'sContent'=>$sMbrReceiptsLinks ) )
               ."<br/>";
}

/* SEEDTesting box
 */
if( $oApp->sess->GetUID() == 1499 ) { // $sess->TestPerm( "Traductions", "W" ) || $sess->TestPerm( "DocRepMgr", "W") ) {
    $bTesting = $oApp->sess->VarGetBool("SEEDTesting");

    if( SEEDSafeGPC_GetInt('ToggleSEEDTesting') ) {
        $bTesting = !$bTesting;
        $oApp->sess->VarSet( 'SEEDTesting', $bTesting );
    }
    $sColour = $bTesting ? "green" : "black";
    $sBackground = $bTesting ? "efe" : "eee";
    $sRight .= "<DIV style='border:1px solid $sColour;padding:1em;background-color:#$sBackground;width:200px;text-align:center'>"
              ."<H3 style='color:$sColour'>Testing mode is ".($bTesting ? "on" : "off")."</H3>"
              ."<FORM action='{$_SERVER['PHP_SELF']}' method='POST'>"
              ."<INPUT type='hidden' name='ToggleSEEDTesting' value='1'/>"
              ."<INPUT type='submit' value='Turn Testing mode ".($bTesting ? "off" : "on")."'/>"
              ."</FORM></DIV>";
}


$raLoginDef = array(
    array( "Web site", "Site Web",
           array(
               array( "user",                    "W DocRepMgr",   "Update drupal web site" ),
               array( "d/docedit.php",           "W DocRepMgr",   "Update SoD Docs Site" ),
               array( "app/traductions.php",     "W Traductions", "Traductions / Translations" ),
        ) ),
    array( "Member Seed Directory", "Catalogue de semences",
           array(
               array( "app/seedexchange",        "PUBLIC",        "Member Seed Directory listings", "Catalogue de semences" ),
               array( "app/mbr/edit.php",        "W sed",         "Edit my Seed Directory Listings" ),
        ) ),

    array( "My Seed Collection", "My Seed Collection",
           array(
               array( "ONE-OF",
                  array( "app/collection",      "A SL",              "Administrate All Seed Collections", "Administrate All Seed Collections" ),
                  array( "app/collection",      "W SLCollection",    "Manage Your Own Seed Collection", "Manage Your Own Seed Collection" ),

               ) ),
           ),
    array( "Variety Descriptions", "Variety Descriptions",
           array(
               array( "bauta/descriptions",      "PUBLIC",        "Record Your Crop Descriptions", "Record Your Crop Descriptions" ),
        ) ),
    array( "Logos", "",
           array(
               array( "app/logos.php",           "PUBLIC",        "Download Logos" ),
        ) ),
    array( "My Account", "Mon compte",
           array(
               array( "",                        "PUBLIC",        "Change Password", "", "${_SERVER['PHP_SELF']}?sessioncmd=changepwd" ),
        ) ),
);


$sBody = "<div class='container-fluid'><div class='row'>
            <div class='col-md-9'>{$oLP->DrawLogin($raLoginDef)}</div>
            <div class='col-md-3'>$sRight</div>
          </div></div>";

echo $oLP->DrawPage( "Login", "", $sBody );

