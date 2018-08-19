<?
/* Generate reports about GCGC data
 */

if( !defined("SITEROOT") )  define("SITEROOT", "../");
include_once( SITEROOT."site.php" );
include_once( SITEINC."mbrutil.php" );      // mbr_drawAddress
include_once( "_gcgc.php" );

list($kfdb, $sess) = SiteStartSessionAccount( array("gcgcadmin" => "R") );

$report = SEEDSafeGPC_Smart( "report", array("G","B","G_allcsv","G_all") );

switch( $report ) {
    case "B":
        doB_CSVAddressesToSendSamples( $kfdb, $sess );
        exit;
    case "G_allcsv":
        doG_allcsv_CSVAllGrowerAddresses( $kfdb, $sess );
        exit;
    case "G_all":
        doG_all_XLSAllGrowerAddresses( $kfdb, $sess );
        exit;
}


echo "<H2>Seeds of Diversity's Great Canadian Garlic Collection</H2>";

echo "<STYLE>";
echo ".gcgc_grower      { font-family: verdana,arial,helvetica,sans serif; font-size: 10pt; }";
echo ".gcgc_grower_addr { font-size: 9pt; }";
echo "</STYLE>";


if( $report == "G" ) {
    echo "<H3>Current requests from growers</H3>";

    if( ($dbc = $kfdb->KFDB_CursorOpen( "SELECT * FROM gcgc_growers".
                                        " WHERE workflow LIKE '%REQ06%' AND workflow NOT LIKE '%SENT06%'" )) ) {
        while( $ra = $kfdb->KFDB_CursorFetch( $dbc ) ) {
            echo "<DIV class='gcgc_grower'><B>${ra['name']}</B> (${ra['fk_mbr_contacts']}) ";

            if( $ra['fk_mbr_contacts'] ) {
                echo mbr_drawAddress( $kfdb, $ra['fk_mbr_contacts'], array("bEmail"=>1) );
            }

            echo "<BR><PRE>${ra['notes']}</PRE></DIV><HR>";
        }
    }
}


function doB_CSVAddressesToSendSamples( &$kfdb, &$sess )
/*******************************************************
    Dump text/plain list of grower addresses for people who have outstanding requests for samples
 */
{
    header( "Content-Type: text/plain" );

    $dbc = $kfdb->KFDB_CursorOpen( "SELECT * FROM gcgc_growers WHERE (status='NEW' OR status='ACTIVE') AND "
                                  ."(workflow LIKE '%REQUEST".date("Y")."%' AND "
                                  ." workflow NOT LIKE '%SENT".date("Y")."%')" );
    while( $ra = $kfdb->KFDB_CursorFetch($dbc) ) {
        $raMbr = $kfdb->KFDB_QueryRA("SELECT * FROM mbr_contacts WHERE _key='{$ra['fk_mbr_contacts']}'" );
        echo implode( "\t", array( $raMbr['firstname'],
                                   $raMbr['lastname'],
                                   $raMbr['company'],
                                   $raMbr['address'],
                                   $raMbr['city'],
                                   $raMbr['province'],
                                   $raMbr['postcode'] ) )."\n";
    }
}

function doG_allcsv_CSVAllGrowerAddresses( &$kfdb, &$sess )
/*******************************************************
    Dump text/plain list of all grower addresses
 */
{
    header( "Content-Type: text/plain" );

    $dbc = $kfdb->KFDB_CursorOpen( "SELECT * FROM gcgc_growers WHERE _status=0" );
    dumpGrowerAddresses( $kfdb, $dbc, true );
}

function doG_all_XLSAllGrowerAddresses( &$kfdb, &$sess )
/*******************************************************
    Dump xsl list of all grower addresses
 */
{
    $dbc = $kfdb->KFDB_CursorOpen( "SELECT * FROM gcgc_growers WHERE _status=0" );
    dumpGrowerAddresses( $kfdb, $dbc, false );
}



function dumpGrowerAddresses( &$kfdb, &$dbc, $bCSV = true )
/**********************************************************
 */
{
    if( !$bCSV ) {
        include_once( STDINC."KeyFrame/KFRTable.php" );

        $xls = new KFTableDump();
        $xls->xlsStart( "gcgc_growers.xls" );
    }

    /* Header row
     */

    $raHdr = array( "member",
                    "GCGC_status",
                    "expiry",
                    "language",
                    "firstname",
                    "lastname",
                    "company",
                    "address",
                    "city",
                    "province",
                    "postcode",
                    "email" );

    $row = $i = 0;
    foreach( $raHdr as $h ) {
        if( $bCSV ) {
            echo $h."\t";
        } else {
            $xls->xlsWrite( 0, $i++, $h );
        }
    }
    if( $bCSV ) echo "\n";


    while( $raG = $kfdb->KFDB_CursorFetch($dbc) ) {
        $raMbr = $kfdb->KFDB_QueryRA("SELECT * FROM mbr_contacts WHERE _key='{$raG['fk_mbr_contacts']}'" );
        $yExpires = intval(substr($raMbr['expires'],0,4));

        $ra = array();
        $ra[] = $raMbr['_key'];
        $ra[] = $raG['status'];
        $ra[] = ($yExpires >= 2020 ? "AUTO" : $yExpires);
        $ra[] = $raMbr['lang'];
        $ra[] = $raMbr['firstname'];
        $ra[] = $raMbr['lastname'];
        $ra[] = $raMbr['company'];
        $ra[] = $raMbr['address'];
        $ra[] = $raMbr['city'];
        $ra[] = $raMbr['province'];
        $ra[] = $raMbr['postcode'];
        $ra[] = $raMbr['email'];

        if( $bCSV ) {
            echo implode( "\t", $ra )."\n";
        } else {
            $i = 0;
            $row++;
            foreach( $ra as $s ) {
                $xls->xlsWrite( $row, $i, $ra[$i] );
                $i++;
            }
        }
    }

    if( !$bCSV ) $xls->xlsEnd();
}


?>
