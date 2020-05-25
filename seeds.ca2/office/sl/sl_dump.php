<?php

/* Dump SL data
 */

if( !defined("SITEROOT") )  define("SITEROOT", "../../");
include_once( SITEROOT."site2.php" );

include_once( "../mbr/_mbr.php" );
include_once( SEEDCOMMON."sl/sl_db.php" );
//include_once( STDINC."KeyFrame/KFRTable.php" );
include_once( STDINC."SEEDTable.php" );

list($kfdb2, $sess) = SiteStartSessionAccount( array("R SL") );
$kfdb1 = SiteKFDB( SiteKFDB_DB_seeds1 ) or die( "Cannot connect to database" );


$bCSV = (SEEDSafeGPC_GetStrPlain('mode') == 'csv');
$bDebug = SEEDSafeGPC_GetInt( 'debug' );

if( $bDebug ) { $kfdb1->SetDebug(2); $kfdb2->SetDebug(2); }

$year = date("Y");

if( $bCSV || $bDebug ) {
    header( "Content-type: text/plain; charset=ISO-8859-1" );
} else {
}



/*
class MyDumpAdoptions extends KFTableDump
{
    function MyDump()  { $this->KFTableDump(); }

    function RowTranslate( $raVal )
    {
        global $kfdb1;

        // add computed value
        $raVal['X_have'] = intval( $kfdb1->Query1("SELECT sum(g_have) FROM seeds.sl_accession WHERE fk_sl_pcv ='".$raVal['fk_sl_pcv']."'") );
        $raVal['X_pgrc'] = intval( $kfdb1->Query1("SELECT sum(g_pgrc) FROM seeds.sl_accession WHERE fk_sl_pcv ='".$raVal['fk_sl_pcv']."'") );

        return( $raVal );
    }
}
*/

/*
 * Do Commands
 */
$cmd = SEEDSafeGPC_GetStrPlain( 'cmd' );
if( $cmd == 't' ) {
    /* Dump whatever is in table t
     */
    $kfreldef = array( "Tables" => array( array( "Table" => 't',
                                                 "Type" => 'Base',
                                                 "Fields" => 'Auto' )));
    $kfrel = new KeyFrameRelation( $kfdb1, $kfreldef, $sess->GetUID() );
    $kfrc = $kfrel->CreateRecordCursor( "" );
    $sFormat = ($bCSV ? 'csv' : 'xls');
    $oDump = new KFTableDump();
    $oDump->Dump( $kfrc, array( 'format' => $sFormat,
                                'header_filename' => "dump.$sFormat",
                                /* cols - what happens if this is not defined */ ) );
} else if( $cmd == 'accessions' ) {
    /* Get seed weights from SL
     */
    $format = ($bCSV ? 'csv' : 'xls');

    $oSLDB = new SLDB_IxAxP( $kfdb1, $sess->GetUID() );
    $kfrel = $oSLDB->GetKFRel();
    if( ($kfr = $kfrel->CreateRecordCursor()) ) {
        $oDump = new KFTableDump();
        $oDump->Dump( $kfr, array( 'format'=>$format,
                                   'header_filename' => "seed-library-inventory.$format",
                                   'cols' => array( "_key", "A__key", "P_psp", "P_name", "g_weight", "location" ) ) );
    }

} else if( $cmd == 'adoptions') {
// this kfreldef should be provided by SLDB, except it would need to be in an office-level derived class
    $kfreldef = array( "Tables" => array(array( "Table" => "seeds.sl_adoption",
                                                "Alias" => "D",
                                                "Type"  => "Base",
                                                "Fields" => _sldb_base::kfrelFldSLAdoption() ),
                                         array( "Table" => "seeds.sl_pcv",
                                                "Alias" => "P",
                                                "Type"  => "LEFT JOIN",
                                                "LeftJoinOn" => "D.fk_sl_pcv=P._key",
                                                "Fields" => _sldb_base::kfrelFldSLPCV() ),
                                         array( "Table" => "seeds2.mbr_contacts",
                                                "Alias" => "M",
                                                "Type"  => "LEFT JOIN",
                                                "LeftJoinOn" => "D.fk_mbr_contacts=M._key",
                                                "Fields" => "Auto" )
                                         ) );

    $sqlCond = "";

    $raFlds = array( '_key','donor_name', 'public_name', 'amount', 'sPCV_request', 'd_donation', 'notes',
                     'P_psp','P_name',
                     'M__key','M_firstname','M_lastname','M_company','M_dept','M_address','M_city','M_province','M_country','M_postcode','M_email',
                     'M_startdate','M_expires','M_lang','X_have','X_pgrc'
                   );

    $kfrel = new KeyFrameRelation( $kfdb2, $kfreldef, $sess->GetUID() );
    $kfr = $kfrel->CreateRecordCursor( $sqlCond, array( "sSortCol" => 'M._key' ) );
    $format = ($bCSV ? 'csv' : 'xls');
    $oDump = new MyDumpAdoptions();
    $oDump->Dump( $kfr, array( 'format' => $format,
                               'header_filename' => "sl_dump.$format",
                               'cols' => $raFlds ) );

} else if( $cmd == 'pgrc') {

// first shipment predated this software
// second shipment was A._key>789 AND g_pgrc>0"
// third shipment was A._key>=2644 and g_pgrc>0

    $sqlCond = "A._key>=2644 AND g_pgrc>0";

    $oSLDB = new SLDB_AxP( $kfdb1, $sess->GetUID() );
    $kfrel = $oSLDB->GetKFRel();
    $kfr = $kfrel->CreateRecordCursor( $sqlCond, array( "sSortCol" => '_key' ) );
    $format = ($bCSV ? 'csv' : 'xls');
    $oDump = new MyDumpAdoptions();
    $oDump->Dump( $kfr, array( 'format' => $format,
                               'header_filename' => "sl_dump.$format",
                               'cols' => array( '_key','P_psp','P_name','g_pgrc','x_member','x_d_harvest' ) ) );

} else if( $cmd == 'orderme' ) {

    // SED listings that should be ordered for SL

    $raCols = array( "_key", "type", "variety" );
    $ra = array();
    $ra[] = array( "_key"=>"1", "type"=>"tomato", "variety"=>"fooey" );

    $ra = $kfdb1->QueryRowsRA( "SELECT * FROM seeds.sed_curr_seeds S LEFT JOIN csci_seeds C on S.variety=C.icv LEFT JOIN sl_pcv P ON S.variety=P.name "
                              ." WHERE S.category='VEGETABLES' AND NOT S.bSkip AND NOT S.bDelete"
                              ." AND C._key IS NULL AND P._key IS NULL"
                               );





    $oDump = new KFTableDump();
    $oDump->bXLS = true;
    $oDump->start( "orderme.xls", "-" );
    $i = 0;
    foreach( $ra as $raRow ) {
        $oDump->writeRowData( $i, $raCols, $raRow );
        ++$i;
    }
    $oDump->end();

} else if( $cmd == 'csci' ) {
    include( SEEDCOMMON."sl/csci.php" );

    $oCSCI = new SL_CSCI( $kfdb1 );
    $kfr = $oCSCI->kfrelSeeds->CreateRecordCursor();
    $format = ($bCSV ? 'csv' : 'xls');
    $oDump = new KFTableDump();
    $oDump->Dump( $kfr, array( 'format'=>$format,
                               'header_filename' => "csci_dump.$format",
                               'cols' => array( "_key", "company_name", "psp", "icv" ) ) );

} else if( $cmd == 'overview' ) {

    $raAll = array();

    /* Get seed weights from SL
     */
    $oSLDB = new SLDB_AxP( $kfdb1, $sess->GetUID() );
    $kfrel = $oSLDB->GetKFRel();
    if( ($kfr = $kfrel->CreateRecordCursor()) ) {
        while( $kfr->CursorFetch() ) {
            $psp = strtolower( $kfr->value('P_psp') );
            $cv  = $kfr->value('P_name');
            $k = $psp.'_'.strtolower($cv);
            if( !isset($raAll[$k]) ) {
                $raAll[$k]['psp'] = $psp;    // makes it easier to dump the array later
                $raAll[$k]['name'] = $cv;
            }
            $raAll[$k]['sl'] = @$raAll[$k]['sl'] + $kfr->value('g_have');
        }
    }

    // the code above should be included with this
    if( ($dbc = $kfdb1->CursorOpen( "SELECT * FROM sl_pcv where _status=0" )) ) {
        while( $ra = $kfdb1->CursorFetch( $dbc ) ) {
            $kPCV = $ra['_key'];
            $psp = strtolower( $ra['psp'] );
            $cv  = $ra['name'];
            $k = $psp.'_'.strtolower($cv);
            if( !isset($raAll[$k]) ) {
                $raAll[$k]['psp'] = $psp;    // makes it easier to dump the array later
                $raAll[$k]['name'] = $cv;
            }
            $raAll[$k]['kPCV'] = $kPCV;

            $raAll[$k]['adoption'] = $kfdb1->Query1( "SELECT sum(amount) FROM sl_adoption where _status=0 AND fk_sl_pcv='$kPCV'" );

// get the most recent germ result for each accession
            if( ($dbc2 = $kfdb1->CursorOpen( "SELECT A.g_have as g_have,  FROM sl_accession A LEFT JOIN sl_germ G ON (A._key=G.fk_sl_accession) WHERE A._status=0 and A._key='$kPCV'" )) ) {
                $nHigh = $nLow = $nTotal = 0;
                while( $ra = $kfdb1->CursorFetch( $dbc2 ) ) {
                    $nTotal += 0;
                }
            }
       }
    }


    /* Get csci company counts
     */
    include( SEEDCOMMON."sl/csci.php" );
    $oCSCI = new SL_CSCI( $kfdb1 );
    if( ($kfr = $oCSCI->kfrelSeeds->CreateRecordCursor()) ) {
        while( $kfr->CursorFetch() ) {
            $psp = strtolower( $kfr->value('psp') );
            $cv  = $kfr->value('icv');
            $k = $psp.'_'.strtolower($cv);
            if( !isset($raAll[$k]) ) {
                $raAll[$k]['psp'] = $psp;    // makes it easier to dump the array later
                $raAll[$k]['name'] = $cv;
            }
            $raAll[$k]['csci'] = @$raAll[$k]['csci'] + 1;
            $raAll[$k]['csci_company'] = $kfr->value('company_name');
        }
    }

    /* Get SED counts
     */$j = 0;
    include( SEEDCOMMON."sl/sed/sedCommon.php" );
    $oSED = new SEDCommon( $kfdb1, $sess, "EN", "VIEW" );
    if( ($kfr = $oSED->kfrelSxG->CreateRecordCursor("S.mbr_id=G.mbr_id AND (category in ('VEGETABLES','FRUIT','GRAIN')) AND NOT S.bSkip")) ) {
        while( $kfr->CursorFetch() ) {
            $psp = strtolower( $kfr->value('type') );
            if( substr($psp,0,6) == 'tomato' )   $psp = 'tomato';
            if( substr($psp,0,4) == 'bean' )     $psp = 'bean';
            if( substr($psp,0,7) == 'lettuce' )  $psp = 'lettuce';
            if( substr($psp,0,6) == 'squash' )   $psp = 'squash';
            if( substr($psp,0,6) == 'turnip' )   $psp = 'turnip';
// use in_array to avoid things like peafeather
            if( substr($psp,0,3) == 'pea' )      $psp = 'pea';
            if( substr($psp,0,6) == 'pepper' )      $psp = 'pepper';
            if( substr($psp,0,8) == 'cucumber' )      $psp = 'cucumber';
            if( substr($psp,0,4) == 'corn' )      $psp = 'corn';
            $cv  = $kfr->value('variety');
            $k = $psp.'_'.strtolower($cv);
            if( !isset($raAll[$k]) ) {
                $raAll[$k]['psp'] = $psp;    // makes it easier to dump the array later
                $raAll[$k]['name'] = $cv;
            }
            $raAll[$k]['sed'] = @$raAll[$k]['sed'] + 1;
            $raAll[$k]['sed_grower'] = $kfr->value('G_mbr_code');
        }
    }

    ksort($raAll);

    $xls = new KFTableDump();
    $xls->bXLS = true;
    $xls->start( "seed_overview.xls" );

    $i = 0;
    $xls->writeRowHeaders( $i++, array('kPCV','species', 'variety','adoption','SL (g)','SL high germ (g)','SL low germ (g)','companies','member directory','single company','single member') );

    foreach( $raAll as $k => $ra ) {      // psp and name are duplicated in $ra for convenience here (so $k is redundant)
        if( @$ra['csci'] > 1 ) $ra['csci_company']="";
        if( @$ra['sed'] > 1 ) $ra['sed_grower']="";
        $xls->writeRowData( $i++, array('kPCV','psp','name','adoption','sl','sl_high','sl_low','csci','sed','csci_company','sed_grower'), $ra );
    }

    $xls->end();
} else if( $cmd == 'sl-available' ) {
    /* Seed Library inventory items available for distribution
     */
    $raCols = array( "kInv", "psp", "pcv", "Reserve", "g_weight", "location" );

    $oDump = new KFTableDump();
    $oDump->bXLS = true;
    $oDump->start( "sl-available.xls", "-" );

    $i = 0;
    $oDump->writeRowData( $i++, array('dummy'), array("dummy"=> "Reserve = (Min population size x 10 / germination rate) / (# seeds/g)") );
    $oDump->writeRowData( $i++, array('dummy'), array("dummy"=> "") );

    $oDump->writeRowHeaders( $i++, $raCols );

    $ra = $kfdb1->QueryRowsRA( "SELECT I.location as location, I._key as kInv, I.g_weight as g_weight, "
                              ."P.psp as psp, P.name as pcv "
                              ."FROM sl_inventory I,sl_accession A,sl_pcv P WHERE "
                              ."I.fk_sl_accession=A._key and A.fk_sl_pcv=P._key and"
                              ." left(I.location,1) in ('A','B','C','D','E','F') "
                              ."ORDER BY P.psp,P.name");
    foreach( $ra as $raRow ) {
        $nSamplesReserve = 10;  // keep this many samples in reserve, distribute the rest
        $fGerm = 0.5;           // assume mediocre-poor germination

        $nMinPop = SLDB_MinPopulation( $raRow['psp'] );
        $nSeedsPerGram = SLDB_SeedsPerGram( $raRow['psp'] );

        $nReserveGrams = floatval($nMinPop * $nSamplesReserve / $fGerm) / $nSeedsPerGram;

        $nReserveGrams = intval($nReserveGrams*100+0.5)/floatval(100);

        if( $nReserveGrams > $raRow['g_weight'] )  continue;
        $raRow['Reserve'] = $nReserveGrams;

        $oDump->writeRowData( $i, $raCols, $raRow );
        ++$i;
    }
    $oDump->end();
} else if( $cmd == 'sl-report-adopted-inv' ) {
    // moved to Q:"collreport-adoptedsummary"

} else if( $cmd == 'sl-species' ) {
//QServer should provide this
    $raRowsOut = array();

    $oSLDBMaster = new SLDB_Master( $kfdb2, $sess->GetUID() );    // this can be either kfdb2 or kfdb1 because SLDB uses db prefixes


    if( ($kfr = $oSLDBMaster->GetKfrel("S")->CreateRecordCursor( "" )) ) {
        while( $kfr->CursorFetch() ) {
            $raRowsOut[] = array( '_key' => $kfr->Key(),
                                  'psp' => $kfr->Value('psp'),
                                  'name_en' => SEEDCore_utf8_encode($kfr->Value('name_en')),
                                  'name_fr' => SEEDCore_utf8_encode($kfr->Value('name_fr')),
                                  'iname_en' => SEEDCore_utf8_encode($kfr->Value('iname_en')),
                                  'iname_fr' => SEEDCore_utf8_encode($kfr->Value('iname_fr')),
                                  'name_bot' => SEEDCore_utf8_encode($kfr->Value('name_bot')),
                                  'family_en' => SEEDCore_utf8_encode($kfr->Value('family_en')),
                                  'family_fr' => SEEDCore_utf8_encode($kfr->Value('family_fr')),
                                  'category' => SEEDCore_utf8_encode($kfr->Value('category')),
                                  'notes' => SEEDCore_utf8_encode($kfr->Value('notes')),
                                 );
        }
    }

    SEEDTable_OutputXLSFromRARows( $raRowsOut,
                                   array( 'columns' => array('_key','psp','name_en','iname_en','name_fr','iname_fr','name_bot',
                                                             'family_en','family_fr','category','notes'),
                                          'filename'=>'sl-species.xls',
                                          'created_by'=>$sess->GetName(), 'title'=>'Species Table' ) );
} else if( $cmd == 'sl-pcv' ) {
//QServer should provide this
    /* N.B. Any rows where fk_sl_species=0 (or just where fk_sl_species does not match an S._key) will not be shown here.
     *      That case should be tested by an integrity check anyway.
     */

    $raRowsOut = array();

    $oSLDBMaster = new SLDB_Master( $kfdb2, $sess->GetUID() );    // this can be either kfdb2 or kfdb1 because SLDB uses db prefixes


    if( ($kfr = $oSLDBMaster->GetKfrel("PxS")->CreateRecordCursor( "" )) ) {
        while( $kfr->CursorFetch() ) {
            $raRowsOut[] = array( '_key'     => $kfr->Key(),
                                  'species'  => SEEDCore_utf8_encode($kfr->Value('S_name_en')),
                                  'cultivar' => SEEDCore_utf8_encode($kfr->Value('name')),
                                  't'        => $kfr->Value('t'),
                                  'notes'    => SEEDCore_utf8_encode($kfr->Value('notes')),
                                 );
        }
    }

    SEEDTable_OutputXLSFromRARows( $raRowsOut,
                                   array( 'columns' => array('_key','species','cultivar','t','notes'),
                                          'filename'=>'sl-pcv.xls',
                                          'created_by'=>$sess->GetName(), 'title'=>'Primary Cultivars Table' ) );
} else if( $cmd == 'sl-accessions' || $cmd == 'sl-accessions-verbatim' ) {
    doAccessions( $cmd == 'sl-accessions' );
} else if( $cmd == 'sl-inventory' || $cmd == 'sl-inventory-A' || $cmd == 'sl-inventory-verbatim' ) {
    doInventory( $cmd == 'sl-inventory' ? "AxPxS" : ($cmd == 'sl-inventory-A' ? "A" : "") );
} else {

    $s = "<h3>Seed Library tables as xls</h3>"
        ."<div style='margin-left:30px'>"
        ."<p><a href='".Site_path_self()."?cmd=sl-species' target='_blank'>Species</a></p>"
        ."<p><a href='".Site_path_self()."?cmd=sl-pcv' target='_blank'>Cultivars</a></p>"
        ."<p><a href='".Site_path_self()."?cmd=sl-accessions' target='_blank'>Accessions</a></p>"
        ."<p><a href='".Site_path_self()."?cmd=sl-accessions-verbatim' target='_blank'>Accessions no joins</a></p>"
        ."<p><a href='".Site_path_self()."?cmd=sl-inventory' target='_blank'>Inventory</a></p>"
        ."<p><a href='".Site_path_self()."?cmd=sl-inventory-A' target='_blank'>Inventory joined only to Accessions</a></p>"
        ."<p><a href='".Site_path_self()."?cmd=sl-inventory-verbatim' target='_blank'>Inventory no joins</a></p>"
        ."</div>";

    echo $s;
}


function doAccessions( $bJoined )
{
    global $kfdb2, $sess;

//QServer should provide this

    /* N.B. For the joined version, any rows where fk_sl_species or fk_sl_pcv are zero or widowed will not be shown here.
     *      That case should be tested by an integrity check anyway.
     *
     *      The non-joined version is here to help debug those cases.
     */
    if( $bJoined ) {
        $rel = "AxPxS";
        $fname = "sl-accession.xls";
    } else {
        $rel = "A";
        $fname = "sl-accession-verbatim.xls";
    }

    $raRowsOut = array();

    $oSLDBMaster = new SLDB_Master( $kfdb2, $sess->GetUID() );    // this can be either kfdb2 or kfdb1 because SLDB uses db prefixes

    if( ($kfr = $oSLDBMaster->GetKfrel($rel)->CreateRecordCursor( "" )) ) {
        while( $kfr->CursorFetch() ) {
            $row = array( 'acc-key'       => $kfr->Key(),
                          'orig-name'     => SEEDCore_utf8_encode($kfr->Value('oname')),
                          'pcv-key'       => $kfr->Value('fk_sl_pcv'),
                          'grower-source' => SEEDCore_utf8_encode($kfr->Value('x_member')),
                          'date-harvest'  => SEEDCore_utf8_encode($kfr->Value('x_d_harvest')),
                          'date-received' => SEEDCore_utf8_encode($kfr->Value('x_d_received')),
                          'parent'        => SEEDCore_utf8_encode($kfr->Value('parent_src')),
                          'notes'         => SEEDCore_utf8_encode($kfr->Value('notes')),
                        );
            if( $bJoined ) {
                $row['species'] = SEEDCore_utf8_encode($kfr->Value('S_name_en'));
                $row['cultivar'] = SEEDCore_utf8_encode($kfr->Value('P_name'));
            }
            $raRowsOut[] = $row;
        }
    }

    SEEDTable_OutputXLSFromRARows( $raRowsOut,
                                   array( 'columns' => array_merge( array('acc-key'),
                                                                    ($bJoined ? array('species','cultivar') : array() ),
                                                                    array('orig-name','pcv-key','grower-source',
                                                                          'date-harvest','date-received','parent','notes')),
                                          'filename'=>$fname,
                                          'created_by'=>$sess->GetName(), 'title'=>'Accessions Table' ) );
}

function doInventory( $eJoins )
/******************************
    eJoins can be "", "A", "AxPxS"
 */
{
    global $kfdb2, $sess;

//QServer should provide this

    /* N.B. For the joined version, any rows where fk_sl_species or fk_sl_pcv are zero or widowed will not be shown here.
     *      That case should be tested by an integrity check anyway.
     *
     *      The non-joined version is here to help debug those cases.
     */
    $raPxS = array();
    $raA = array();
    switch( $eJoins ) {
        case "":
            $rel = "I";
            $fname = "sl-inventory-verbatim.xls";
            break;
        case "A":
            $rel = "IxA";
            $raA = array('orig-name','pcv-key','grower-source','date-harvest','date-received','parent','notes');
            $fname = "sl-inventory-A.xls";
            break;
        case "AxPxS":
            $rel = "IxAxPxS";
            $raA = array('orig-name','pcv-key','grower-source','date-harvest','date-received','parent','notes');
            $raPxS = array('species','cultivar');
            $fname = "sl-inventory.xls";
            break;
        default:
            die( "Invalid I join $eJoins" );
    }

    $raRowsOut = array();

    $oSLDBMaster = new SLDB_Master( $kfdb2, $sess->GetUID() );    // this can be either kfdb2 or kfdb1 because SLDB uses db prefixes

    if( ($kfr = $oSLDBMaster->GetKfrel($rel)->CreateRecordCursor( "" )) ) {
        while( $kfr->CursorFetch() ) {
            $row = array( 'inv-key'       => $kfr->Key(),
                          'inv-number'    => $kfr->Value('inv_number'),
                          'acc-key'       => $kfr->Value('fk_sl_accession'),
                          'coll-key'      => $kfr->Value('fk_sl_collection'),
                          'grams'         => $kfr->Value('g_weight'),
                          'location'      => $kfr->Value('location'),
                          'date-inv'      => $kfr->Value('dCreation'),
                          'deaccessioned' => $kfr->Value('bDeAcc'),
            );
            if( $kfr->Value( "A__key" ) ) {
                $row['orig-name']     = utf8_encode($kfr->Value('A_oname'));
                $row['pcv-key']       = $kfr->Value('A_fk_sl_pcv');
                $row['grower-source'] = utf8_encode($kfr->Value('A_x_member'));
                $row['date-harvest']  = utf8_encode($kfr->Value('A_x_d_harvest'));
                $row['date-received'] = utf8_encode($kfr->Value('A_x_d_received'));
                $row['parent']        = utf8_encode($kfr->Value('A_parent_src'));
                $row['notes']         = utf8_encode($kfr->Value('A_notes'));
            }
            if( $kfr->Value( "P__key" ) ) {
                $row['species']       = utf8_encode($kfr->Value('S_name_en'));
                $row['cultivar']      = utf8_encode($kfr->Value('P_name'));
            }

            $raRowsOut[] = $row;
        }
    }

    SEEDTable_OutputXLSFromRARows( $raRowsOut,
                                   array( 'columns' => array_merge( array('inv-number'),
                                                                    $raPxS,
                                                                    array('grams','location','date-inv','deaccessioned','acc-key','coll-key'),
                                                                    $raA,
                                                                    array('inv-key')),
                                          'filename'=>$fname,
                                          'created_by'=>$sess->GetName(), 'title'=>'Inventory Table' ) );
}



?>
