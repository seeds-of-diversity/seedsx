<?
/* Output CSCI seeds list
 */
define("SITEROOT","../../");
include_once( SITEROOT."site.php" );
include_once( STDINC."KeyFrame/KFUIAppSimple.php" );
include_once( SEEDCOMMON."siteApp.php" );

list($kfdb, $sess) = SiteStartSessionAccount( array("sedadmin" => "R") );

$bCSV = (SEEDSafeGPC_GetStrPlain('mode') == 'csv');

$year = date("Y");

if( $bCSV ) {
    header( "Content-type: text/plain" );
} else {
    include_once( STDINC."KeyFrame/KFRTable.php" );
}


$kfreldef_csci_seeds = array( "Tables" => array( array( "Table" => "seeds.csci_seeds",
                                                        "Fields" => array(array("col"=>"company_name", "type"=>"S"),
                                                                          array("col"=>"psp",          "type"=>"S"),
                                                                          array("col"=>"icv",          "type"=>"S"),
                                                                          array("col"=>"bOrganic",     "type"=>"I"),
                                                                          array("col"=>"year",         "type"=>"I"),
                                                        ) ) ) );

$kfrel = new KeyFrameRelation( $kfdb, $kfreldef_csci_seeds, $sess->GetUID() );
if( @$_REQUEST['debug'] )  $kfdb->KFDB_SetDebug(2);


$kfr = $kfrel->CreateRecordCursor( "", array( "sSortCol" => 'T1.company_name,T1.psp,T1.icv' ) );
$kfdb->KFDB_SetDebug(0);

if( $bCSV ) {
    $xls = NULL;
} else {
    $xls = new KFTableDump($kfr);
    $xls->xlsStart( "csci_seeds.xls" );
}

$row = 0;
writeRow( $row, array("Key","Company","Species","Variety","Organic","Year") );

function writeRow( &$rowX, $ra )
{
    global $bCSV, $xls, $row;

    $i = 0;
    foreach( $ra as $h ) {
        if( $bCSV ) {
            echo $h."\t";
        } else {
            $xls->xlsWrite( $row, $i++, $h );
        }
    }
    if( $bCSV )  echo "\n";
    ++$row;
}

$raTable = array();
while( $kfr->CursorFetch() ) {
    writeRow( $row, array($kfr->Key(), $kfr->value("company_name"), $kfr->value("psp"), $kfr->value("icv"), $kfr->value("bOrganic"), $kfr->value("year") ) );
}

if( !$bCSV ) $xls->xlsEnd();


?>

