<?
echo "Don't run this by accident - it loads the seeds and growers from Janet McNeill's spreadsheets";
exit;

define( "SITEROOT", "../../" );

include_once( SITEROOT."site.php" );
include_once( SITEINC."siteKFDB.php" );
include( STDINC."KeyFrame/KFRecord.php" );
include( STDINC."KeyFrame/KFRFile.php" );
include( "_sed.php" );



$kfdb = SiteKFDB() or die( "Cannot connect to database" );
$kfrSeeds = new KeyFrameRecord( $kfdb, $kfrdef_SEDCurrSeeds, 0 );
$kfrGrowers = new KeyFrameRecord( $kfdb, $kfrdef_SEDCurrGrowers, 0 );


function loadFromFile( $kfr, $filename )
{
    $kfl = new KFRFileLoad( $kfr );
    if( $kfl->LoadFile( $filename ) ) {
        echo "Successfully";
    } else {
        echo "With error: ".$kfr->kfdb->KFDB_GetErrMsg();
    }
    echo " loaded ". $kfl->GetRowsLoaded() ." rows from $filename";
}


loadFromFile( $kfrSeeds, "c:/seeds06.txt" );
echo "<BR><BR>";
loadFromFile( $kfrGrowers, "c:/growers06.txt" );


?>
