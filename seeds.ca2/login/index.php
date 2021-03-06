<?php
/* Main Login page for seeds.ca
 */

include_once( "../site.php" );
include_once( SEEDCOMMON."console/console01.php" );
include_once( SEEDCOMMON."siteTemplate.php" );

list($kfdb, $sess, $lang) = SiteStartSessionAccount( array() );  // requires a valid login, but no specific permissions


$oC = new Console01( $kfdb, $sess );
$oLP = new SiteStartLoginPage( $sess, $lang );

$raTmplParms = array();
$oMaster = new MasterTemplate( $kfdb, $sess->GetUID(), $lang, $raTmplParms );
$oTmpl = $oMaster->GetTmpl();


$sBody = "";

$sRight = "<div style='float:right'>";

$sRight .= $oTmpl->ExpandTmpl( "SeedUI-Box1", array( 'heading'=>'Your Membership', 'content'=>"You are member #".$sess->GetUID() ) );



// Manage the SEEDTesting flag
if( $sess->TestPerm( "Traductions", "W" ) || $sess->TestPerm( "DocRepMgr", "W") ) {
    $bTesting = $sess->VarGetBool("SEEDTesting");

    if( SEEDSafeGPC_GetInt('ToggleSEEDTesting') ) {
        $bTesting = !$bTesting;
        $sess->VarSet( 'SEEDTesting', $bTesting );
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
$sRight .= "</div>";

$sBody .= $sRight;

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


$sBody .= $oLP->DrawLogin( $raLoginDef );

echo $oLP->DrawPage( "Login", $oC->Style(), $sBody );

?>
