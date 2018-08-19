<?php

define( "SITEROOT", "../" );
include( SITEROOT."site2.php" );
include_once( STDINC."SEEDTable.php" );
include_once( SEEDCOMMON."console/console01.php" );

list($kfdb, $sess, $lang) = SiteStartSessionAccount( array() );  // must have a login account in the office

header( "Content-Type:text/html; charset=Windows-1252" );

$s = "";

switch( ($cmd = SEEDSafeGPC_GetStrPlain( 'cmd' )) ) {
    case 'uploadxls':    // use PHPExcel to upload any spreadsheet file to db table
    case 'uploadcsv':    // use native code to upload a csv file to db table (because large files blow up PHPExcel)
        $s .= uploadFile( $kfdb, ($cmd == 'uploadcsv') );
        break;

    case 'downloadxls':
        // use PHPExcel to output the db table as a spreadsheet
        downloadXLS( $kfdb, $sess );
        goto done;    // function either outputs its error message or outputs xls file

    case 'downloadcsv':
        // use native code to output the db table as a csv file (if PHPExcel imposes memory limits with large table/file)
//        $s .= downloadCSV( $kfdb );
        break;

    default:
        $s .= "<style>"
             .".ctrl {border:1px solid #aaa;margin-bottom:20px;padding:10px;}"
             ."</style>";

        $s .= "<div class='ctrl'>"
            ."Upload any spreadsheet to 'xlsupload' using PHPExcel - the top row will be used as keys<br/><br/>"
            ."<form action='${_SERVER['PHP_SELF']}' method='post' enctype='multipart/form-data'>"
            ."<input type='hidden' name='MAX_FILE_SIZE' value='10000000' />"
            ."<input type='hidden' name='cmd' value='uploadxls' />"
            ."<input type='file' name='uploadfile'/>"
            ."<select name='uploadcharset'><option value='Windows-1252'>Windows-1252</option></select> Charset in db table (currently hardcoded but easy to make this an option)<br/>"
            ."<input type='submit' value='Upload'/>"
            ."</form>"
            ."</div>";

        $s .= "<div class='ctrl'>"
            ."Upload a csv file to 'xlsupload' using native code (for large files that PHPExcel can't handle) - the top row will be used as keys<br/><br/>"
            ."<form action='${_SERVER['PHP_SELF']}' method='post' enctype='multipart/form-data'>"
            ."<input type='hidden' name='MAX_FILE_SIZE' value='10000000' />"
            ."<input type='hidden' name='cmd' value='uploadcsv' />"
            ."<input type='file' name='uploadfile'/>"
            ."<select name='uploadcharset'>"
                ."<option value='Win/Win'>Windows-1252 / Windows-1252</option>"
                ."<option value='Win/UTF8'>Windows-1252 / UTF-8</option>"
                ."<option value='UTF8/Win'>UTF-8 / Windows-1252</option>"
                ."<option value='UTF8/UTF8'>UTF-8 / UTF-8</option>"
                ."</select> Charset in file/table<br/>"
            ."<input type='submit' value='Upload'/>"
            ."</form>"
            ."</div>";

        $s .= "<div class='ctrl'>"
            ."Download 'xlsupload' as an xls file using PHPExcel<br/><br/>"
            ."<form action='${_SERVER['PHP_SELF']}'>"
            ."<input type='hidden' name='cmd' value='downloadxls' />"
            ."<input type='submit' value='Download'/>"
            ."</form>"
            ."</div>";

        $s .= "<div class='ctrl'>"
            ."Download 'xlsupload' as a csv file using native code (if PHPExcel can't handle the size)<br/><br/>"
            ."<form action='${_SERVER['PHP_SELF']}'>"
            ."<input type='hidden' name='cmd' value='downloadcsv' />"
            ."<input type='submit' value='Download'/>"
            ."</form>"
            ."</div>";

        break;
}


if( $s ) {
    echo Console01Static::HTMLPage( $s, "", "EN", array('bBootstrap'=>true, 'sCharset'=>"Windows-1252") );
}

done:


function uploadFile( KeyFrameDB $kfdb, $bCSV )
{
    $s = "";

    switch( SEEDSafeGPC_GetStrPlain('uploadcharset') ) {
        case 'Win/Win':
            $sCharsetFile  = 'Windows-1252';
            $sCharsetTable = 'Windows-1252';
            break;
        case 'Win/UTF8':
            $sCharsetFile  = 'Windows-1252';
            $sCharsetTable = 'utf-8';
            break;
        case 'UTF8/UTF8':
            $sCharsetFile  = 'utf-8';
            $sCharsetTable = 'utf-8';
            break;

        case 'UTF8/Win':    // this is the default since spreadsheets are usually utf-8 and our tables are usually win-1252
        default:
            $sCharsetFile  = 'utf-8';
            $sCharsetTable = 'Windows-1252';
            break;
    }

    // parms for SEEDTable
    $raSEEDTableDef = array( 'charset'=>$sCharsetTable );

    // parms for SEEDTable::LoadFromFile
    $raSEEDTableLoadParms = array( 'bCSV'=>$bCSV, 'charset-file'=>$sCharsetFile );


    list($bOk,$raRows,$sErr) = SEEDTable_LoadFromUploadedFile( 'uploadfile',
                                                               array( 'raSEEDTableDef'       => $raSEEDTableDef,
                                                                      'raSEEDTableLoadParms' => $raSEEDTableLoadParms ) );
    if( !$bOk ) die( "Error: $sErr" );
    if( !count($raRows) )  die( "Empty spreadsheet" );

    $keys = array_keys($raRows[0]);
    //var_dump($keys,$raRows);
    $kfdb->SetDebug(2);

    /* Drop the db-table if it was left over from last time
     */
    $kfdb->Execute( "DROP TABLE IF EXISTS xlsupload" );

    /* Create a db-table with the required fields
     */
    $raK = array();
    foreach( $keys as $k ) {
        // the purpose of addslashes is to prevent sql insertion attacks, not to make this work if a field name contains quotes or other weird characters
        $raK[] = addslashes($k)." text";
    }
    $kfdb->Execute( "CREATE TABLE xlsupload (".implode(",",$raK).");" );

    /* Generate an INSERT statement to fill the db-table.
     * A single INSERT is efficent:  INSERT INTO xlsupload (keys) VALUES (values-row-1),(values-row-2),(values-row-3)...
     */
    $raK = array();
    $raR = array();
    foreach( $keys as $k ) {
        $raK[] = addslashes($k);
    }
    foreach( $raRows as $ra ) {
        $raV = array();
        foreach( $keys as $k ) {
            $raV[] = "'".addslashes($ra[$k])."'";
        }
        $raR[] = "(".implode(',',$raV).")";
    }
    $kfdb->Execute( "INSERT INTO xlsupload (".implode(',',$raK).") VALUES ".implode(',',$raR) );

    $s .= "xlsupload is ready with ".$kfdb->Query1( "SELECT count(*) FROM xlsupload" )." rows!";

    return( $s );
}

function downloadXLS( KeyFrameDB $kfdb, SEEDSessionAccount $sess )
{
    $raCols = array();
    $raColsRA = $kfdb->QueryRowsRA( "SHOW COLUMNS FROM xlsupload" );
    foreach( $raColsRA as $ra ) {
        $raCols[] = $ra[0];
    }
    if( !count($raCols) ) {
        echo "Table xlsupload not found or has no columns";
        return;
    }

    $raRows = $kfdb->QueryRowsRA( "SELECT ".implode( ',', $raCols )." FROM xlsupload" );

    // PHPExcel requires the data to be in utf8
// should use a <select> to say whether this is needed.
// also it can be a closure instead of a function
    array_walk_recursive( $raRows, 'utf8_my_leaves' );

    SEEDTable_OutputXLSFromRARows( $raRows, array( 'columns' => $raCols,
                                                   'filename'=>'download.xls',
                                                   'created_by'=>$sess->GetName(), 'title'=>'XLS Download' ) );
}

function utf8_my_leaves( &$v, $k )
{
    $v = utf8_encode($v);
}

?>

