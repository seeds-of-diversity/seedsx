<?

print_r($_REQUEST);

if( !defined("SITEROOT") )  define("SITEROOT", "../../");
include_once( SITEROOT."site.php" );
include_once( STDINC."KeyFrame/KFUI.php" );
include_once( SITEINC."siteKFDB.php" );
include_once( SITEINC ."sodlogin.php" );
include_once( "_ccsl.php" );

$kfdb =& SiteKFDB() or die( "Cannot connect to database" );

$la = new SoDLoginAuth;
if( !$la->SoDLoginAuth_Authenticate( $_REQUEST, "W CCSL" ) ) { exit; }

$kfrelAcc = new KeyFrameRelation( $kfdb, $kfrdef_CCSL_Acc, $la->LoginAuth_UID() );

$kfuiAcc = new KeyFrameUI( $kfuiDef_CCSL_Acc );
$kfuiAcc->InitUIParms();
$kfuiAcc->SetComponentKFRel( "A", $kfrelAcc );
$kfuiAcc->DoAction("A");


echo "<H2>Seeds of Diversity's Canadian Community Seed Library</H2>";

if( !$kfuiAcc->GetKey( "A" ) ) {
    $kfuiAcc->Draw( "A", "List" );
    $kfuiAcc->Draw( "A", "Form" );
} else {
    echo "<H3>Transactions of Accession ".$kfuiAcc->GetKey('A');
    echo " (".$kfuiAcc->GetValue('A','pspecies');
    echo " / ".$kfuiAcc->GetValue('A','oname');
    echo " / ".$kfuiAcc->GetValue('A','source');
    echo " / ".$kfuiAcc->GetValue('A','batch').")</H3>";


    $kfrelAccXAction = new KeyFrameRelation( $kfdb, $kfrdef_CCSL_Acc_XAction, $la->LoginAuth_UID() );

//  $kfuiAccXAction = new KeyFrameUI( $kfuiDef_CCSL_Acc_XAction );
//  $kfuiAccXAction->InitUIParms();
    $kfuiAcc->SetComponentKFRel( "B", $kfrelAccXAction );//, array("fkDefaults"=>array("ccsl_accession"=> $kfuiAcc->GetKey( "A" ))) );
    $kfuiAcc->DoAction("B");

    //
    $kfuiAcc->Draw( "B", "List" );
    $kfuiAcc->Draw( "B", "Form" );

}

exit;



echo "<H2 align=center>Seed Samples</H2>";
$kfui->Draw( "A", "List" );

echo "<TABLE><TR><TD>";
$kfui->Draw( "A", "Controls" );
echo "</TD><TD>";
$kfui->Draw( "A", "Form" );
echo "</TD></TR></TABLE>";

?>
