<?php

/* SEEDTable
 *
 * Copyright 2014-2017 Seeds of Diversity Canada
 *
 * Load and save tabular data, mainly with spreadsheets
 */

class SEEDTable     // implemented for a single table - use SEEDTableSheets for spreadsheets with multiple sheets
/**************
    Manage a table like this:

        headA    headB    headC    -- the header row is required
        valA1    valB1    valC1
        valA2    valB2    valC2

    Allow the data to be accessed like this:

        headA=>valA1, headB=>valB1, headC=>valC1
        headA=>valA2, headB=>valB2, headC=>valC2

    Parms:
        header-required : header labels required in top row
        header-optional : header labels optional in top row
        charset         : charset of the in-memory data, default Windows-1252
 */
{
    private $raParms;
    private $raRows = array();

    function __construct( $raParms = array() )
    {
        $this->raParms = $raParms;
        if( !isset($this->raParms['charset']) ) $this->raParms['charset'] = "Windows-1252";
    }

    function GetNRows()    { return( ($n=count($this->raRows)) > 0 ? $n-1 : 0 ); }                 // top row is always the header
    function GetNCols()    { return( isset($this->raRows[0]) ? count($this->raRows[0]) : 0 ); }    // implementation must make this true


    function LoadFromFile( $sFilename, $raLoadParms = array() )
    /**********************************************************
        Reads the specified spreadsheet and stores a 2-d array of rows like
            array( array( valA1, valA2, valA3,... ),
                   array( valB1, valB2, valB3,... ),
                   ... )

        N.B. This does nothing with the header row except load it with the other rows. Use other functions to map it.

        Blank cells are stored as nulls
        PHPExcel does a pretty good job of setting the values' types
        Not sure if this computes formulae, yet - apparently it does in rangeToArray()

        Table height == count(raRows)
        Table width == count(raRows[0])   (or any row, but they should all be the same)

        Parms:
            bCSV         = the input file is csv format
            bCSVDoNative = use native code to read the file (e.g. too large for PHPExcel)
            charset-file = charset of input file, default utf-8 (this only matters for csv files)
     */
    {
        $this->raRows = array();

        $bCSV = @$raLoadParms['bCSV'] || (@$raLoadParms['bCSVDoNative'] && inarray(substr($sFilename,-4),array('.csv','.CSV')) );

        $sCharsetFile = @$raLoadParms['charset-file'] ?: 'utf-8';

        if( $bCSV ) {
            $this->loadFromCSV( $sFilename, $sCharsetFile );
        } else {
            include_once( W_ROOT."os/PHPExcel1.8/Classes/PHPExcel.php" );
            include_once( W_ROOT."os/PHPExcel1.8/Classes/PHPExcel/IOFactory.php" );

            if( ($objPHPExcel = PHPExcel_IOFactory::load( $sFilename )) ) {
                $sheet = $objPHPExcel->getSheet(0);
                $highestRow = $sheet->getHighestDataRow();
                $highestColumn = $sheet->getHighestDataColumn();

                for( $row = 1; $row <= $highestRow; $row++ ) {
                    $ra = $sheet->rangeToArray( 'A'.$row.':'.$highestColumn.$row,
                                                NULL, TRUE, FALSE );
                    if( $this->raParms['charset'] != 'utf-8' ) {
                        for( $i = 0; $i < count($ra[0]); ++$i ) {
                            if( is_string($ra[0][$i]) ) {
                                $ra[0][$i] = iconv( 'utf-8', $this->raParms['charset'], $ra[0][$i] );
                            }
                        }
                    }
                    $this->raRows[] = $ra[0];     // $ra is an array of rows, with only one row
                }
            }
        }
        return( count($this->raRows) > 0 );
    }

    function Validate()
    /******************
        Given an array table with a header row, confirm that the header row contains the required names
     */
    {
        $bOk = false;
        $sErrMsg = "";

        if( !count($this->raRows) ) {
            goto done;
        }

        if( isset($this->raParms['headers-required']) ) {
            foreach( $this->raParms['headers-required'] as $head ) {
                if( !in_array( $head, $this->raRows[0] ) ) {
                    $sErrMsg = "The file's first row must have the labels <span style='font-weight:bold'>"
                              .implode( ", ", $this->raParms['headers-required'] )
                              ."</span> (in any order). Like this:<br/>".$this->SampleHead();
                    goto done;
                }
            }
        }
        $bOk = true;

        done:
        return( array($bOk,$sErrMsg) );
    }


    function Associate()
    /*******************
        Given a table as a 2-d array( array( head1, head2, head3,... ), array( vB1, vB2, vB3,... ),...)
        where the first row contains header names, return a 2-d array like
        array( array( head1=>vB1, head2=>vB2, ... ), array( head1=>vC1, head2=>vC2, ... ) )
     */
    {
        $raOut = array();

        if( !count($this->raRows) ) {
            goto done;
        }

        $raHead = $this->raRows[0];

        for( $i = 1; $i < count($this->raRows); ++$i ) {
            $ra = array();
            for( $j = 0; $j < count($raHead); ++$j ) {
                if( empty($raHead[$j]) ) continue;    // skip columns with blank headers
                $ra[$raHead[$j]] = $this->raRows[$i][$j];
            }
            $raOut[] = $ra;
        }

        done:
        return( $raOut );
    }

    function SampleHead()
    {
        $s = "<table class='table' border='1'><tr>";
        if( isset($this->raParms['headers-required']) ) {
            foreach( $this->raParms['headers-required'] as $v ) {
                $s .= "<th>$v</th>";
            }
        }
        if( isset($this->raParms['headers-optional']) ) {
            foreach( $this->raParms['headers-optional'] as $v ) {
                $s .= "<th>$v <span style='font-size:8pt'>(optional)</span></th>";
            }
        }
        $s .= "</th></tr></table>";

        return( $s );
    }

    private function loadFromCSV( $sFilename, $sCharsetFile )
    /********************************************************
        Read csv or tab-delimited file into $this->raRows

        This does nothing with the header row, except load it with the other rows. Other functions do the mapping later.

        Skip blank lines (don't store them).

        $parms: bTab (default:false)    = same as (sDelimiter="\t", sEnclosure='', sEscape='\')
                nSkip                   = # rows to skip before reading (e.g. use nSkip=1,bHeader=false to skip a header row)
                sDelimiter              = single char separating fields
                sEnclosure              = single char before and after fields (not required if not necessary)
                sEscape                 = single char to escape delimiter and enclosure chars
     */
    {
        $nCols = 0;

        $parms = array();    // hook this into arguments if you want
        $nSkip = intval(@$parms['nSkip']);

        if( !($f = @fopen( $sFilename, "r" )) ) {
            $parms['sErrMsg'] = "Cannot open $filename<br/>";
            return( null );
        }

        /* if bTab is not set use the first row to try to determine the format
         */
        if( isset($parms['bTab']) ) {
            $bTab = SEEDCore_ArraySmartVal( $parms, 'bTab', [false,true] );
        } else {
            $line = fgets($f);
            $bTab = ( strpos( $line, "\t" ) !== false );
            rewind( $f );
        }


        if( !$bTab ) {
            $sDelimiter = @$parms['sDelimiter'] ?: ",";
            $sEnclosure = @$parms['sEnclosure'] ?: "\"";
        } else {
            //$sDelimiter = SEEDStd_ArraySmartVal( $parms, 'sDelimiter', array("\t") );
            //$sEnclosure = SEEDStd_ArraySmartVal( $parms, 'sEnclosure', array("") );
        }
        $sEscape = @$parms['sEscape'] ?: "\\";

        while( !feof( $f ) ) {
            if( $bTab ) {
                // fgetcsv doesn't seem to like a blank sEnclosure, so here it's implemented our way.
                $s = fgets( $f );
                $s = rtrim( $s, " \r\n" );    // fgets retains the linefeed
                if( !strlen( $s ) ) continue;
                $raFields = explode( "\t", $s );
            } else {
// escape parm is available since PHP 5.3 -- try it out
                $raFields = fgetcsv( $f, 0, $sDelimiter, $sEnclosure ); //, $sEscape );
                if($raFields == null)   break;     // eof or error
                if($raFields[0]===null) continue;  // blank line
            }
//var_dump($raFields);

            if( $nSkip ) { --$nSkip; continue; }

            if( $this->raParms['charset'] != $sCharsetFile ) {
                // convert charsets of the fields (could do this above when it's just one string, except it doesn't come that way from fgetcsv)
                array_walk( $raFields,
                            function (&$v,$k,$ra){ $v = iconv( $ra['fromCharset'], $ra['toCharset'], $v ); },
                            array('fromCharset'=>$sCharsetFile, 'toCharset'=>$this->raParms['charset']) );
            }

            $nCols = max( $nCols, count($raFields) );

            $this->raRows[] = $raFields;
        }

        fclose( $f );

        /* Fix column ends if nCols increased during the read
         * Walk through each column, starting with the last, filling !isset with '' until a column where all isset.
         */
        for( $i = 0; $i < count($this->raRows); ++$i ) {
            while( count($this->raRows[$i]) < $nCols ) {
                $this->raRows[$i][] = '';
            }
        }
    }

}

class SEEDTableSheets
/********************
    Manage a set of tables, such as a spreadsheet with multiple sheets

    SheetA
        headA    headB    headC    -- the header row is required
        valA1    valB1    valC1
        valA2    valB2    valC2
    SheetB
        headA    headB    headC    -- the header row is required
        valA1    valB1    valC1
        valA2    valB2    valC2

    Allow the data to be accessed like this:

        SheetA => array( array(headA=>valA1, headB=>valB1, headC=>valC1)
                         array(headA=>valA2, headB=>valB2, headC=>valC2))
        SheetB => array( array(headA=>valA1, headB=>valB1, headC=>valC1)
                         array(headA=>valA2, headB=>valB2, headC=>valC2))

    Parms:
        header-required : header labels required in top row
        header-optional : header labels optional in top row
        charset         : charset of the in-memory data, default Windows-1252
 */
{
    private $raParms;
    private $raSheets = array();    // each element is an array of rows

    function __construct( $raParms = array() )
    {
        $this->raParms = $raParms;
        if( !isset($this->raParms['charset']) ) $this->raParms['charset'] = "Windows-1252";
    }

    function GetNRows()    { return( ($n=count($this->raRows)) > 0 ? $n-1 : 0 ); }                 // top row is always the header
    function GetNCols()    { return( isset($this->raRows[0]) ? count($this->raRows[0]) : 0 ); }    // implementation must make this true


    function LoadFromFile( $sFilename, $raLoadParms = array() )
    /**********************************************************
        Reads the specified spreadsheet and stores a 3-d array of sheets (named) => rows (numbered) => values (named)

        Height of sheet i == count(raSheet[i])
        Width of sheet i == count(raSheet[i][0])   (or any row, but they should all be the same)

        N.B. This does nothing with the header row except load it with the other rows. Use other functions to map it.

        Blank cells are stored as nulls
        PHPExcel does a pretty good job of setting the values' types
        Not sure if this computes formulae, yet - apparently it does in rangeToArray()

        Parms:
            bCSV         = the input file is csv format
            bCSVDoNative = use native code to read the file (e.g. too large for PHPExcel)
            charset-file = charset of input file, default utf-8 (this only matters for csv files)
     */
    {
        $this->raRows = array();

        $bCSV = @$raLoadParms['bCSV'] || (@$raLoadParms['bCSVDoNative'] && in_array(substr($sFilename,-4),array('.csv','.CSV')) );

        $sCharsetFile = @$raLoadParms['charset-file'] ?: 'utf-8';

        if( $bCSV ) {
// this will only be meaningful for a single-sheet table (2-d rows/cols)
            $this->loadFromCSV( $sFilename, $sCharsetFile );
        } else {
            include_once( W_ROOT."os/PHPExcel1.8/Classes/PHPExcel.php" );
            include_once( W_ROOT."os/PHPExcel1.8/Classes/PHPExcel/IOFactory.php" );

            if( ($objPHPExcel = PHPExcel_IOFactory::load( $sFilename )) ) {
                $raSheets = $objPHPExcel->getAllSheets();
                $iSheet = 1;
                foreach( $raSheets as $sheet ) {
                    $highestRow = $sheet->getHighestDataRow();
                    $highestColumn = $sheet->getHighestDataColumn();

                    $raRows = array();
                    for( $row = 1; $row <= $highestRow; $row++ ) {
                        $ra = $sheet->rangeToArray( 'A'.$row.':'.$highestColumn.$row,
                                                    NULL, TRUE, FALSE );
                        if( $this->raParms['charset'] != 'utf-8' ) {
                            for( $i = 0; $i < count($ra[0]); ++$i ) {
                                if( is_string($ra[0][$i]) ) {
                                    $ra[0][$i] = iconv( 'utf-8', $this->raParms['charset'], $ra[0][$i] );
                                }
                            }
                        }
                        $raRows[] = $ra[0];     // $ra is an array of rows, with only one row
                    }
                    if( !($sheetName = $sheet->getTitle()) ) {
                        $sheetName = "Sheet".$iSheet;
                    }
                    $this->raSheets[$sheetName] = $raRows;
                    ++$iSheet;
                }
            }
        }
        return( count($this->raSheets) > 0 );
    }

    function Validate()
    /******************
        Given an array table with a header row, confirm that the header row contains the required names
     */
    {
        $bOk = false;
        $sErrMsg = "";

        if( !count($this->raSheets) ) {
            goto done;
        }

        if( isset($this->raParms['headers-required']) ) {
            foreach( $this->raSheets as $raRows ) {
                foreach( $this->raParms['headers-required'] as $head ) {
                    if( !in_array( $head, $raRows[0] ) ) {
                        $sErrMsg = "Every sheet's first row must have the labels <span style='font-weight:bold'>"
                                  .implode( ", ", $this->raParms['headers-required'] )
                                  ."</span> (in any order). Like this:<br/>".$this->SampleHead();
                        goto done;
                    }
                }
            }
        }
        $bOk = true;

        done:
        return( array($bOk,$sErrMsg) );
    }


    function Associate()
    /*******************
        Given a table as a 3-d array( SheetA => array( array( head1, head2, head3,... ), array( vB1, vB2, vB3,... ),...))
        where the first row of each sheet contains header names, return a 3-d array like
        array( SheetA => array( array( head1=>vB1, head2=>vB2, ... ), array( head1=>vC1, head2=>vC2, ... ) ))
     */
    {
        $raSheetsOut = array();

        if( !count($this->raSheets) ) {
            goto done;
        }

        foreach( $this->raSheets as $sheetName => $raRows ) {
            $raHead = $raRows[0];

            $raRowsOut = array();
            for( $r = 1; $r < count($raRows); ++$r ) {
                $ra = array();
                for( $j = 0; $j < count($raHead); ++$j ) {
                    if( empty($raHead[$j]) ) continue;    // skip columns with blank headers
                    $ra[$raHead[$j]] = $raRows[$r][$j];
                }
                $raRowsOut[] = $ra;
            }
            $raSheetsOut[$sheetName] = $raRowsOut;
        }
        done:
        return( $raSheetsOut );
    }

    function SampleHead()
    {
        $s = "<table class='table' border='1'><tr>";
        if( isset($this->raParms['headers-required']) ) {
            foreach( $this->raParms['headers-required'] as $v ) {
                $s .= "<th>$v</th>";
            }
        }
        if( isset($this->raParms['headers-optional']) ) {
            foreach( $this->raParms['headers-optional'] as $v ) {
                $s .= "<th>$v <span style='font-size:8pt'>(optional)</span></th>";
            }
        }
        $s .= "</th></tr></table>";

        return( $s );
    }

    private function loadFromCSV( $sFilename, $sCharsetFile )
    /********************************************************
        Read csv or tab-delimited file into $this->raRows

        This does nothing with the header row, except load it with the other rows. Other functions do the mapping later.

        Skip blank lines (don't store them).

        $parms: bTab (default:false)    = same as (sDelimiter="\t", sEnclosure='', sEscape='\')
                nSkip                   = # rows to skip before reading (e.g. use nSkip=1,bHeader=false to skip a header row)
                sDelimiter              = single char separating fields
                sEnclosure              = single char before and after fields (not required if not necessary)
                sEscape                 = single char to escape delimiter and enclosure chars
     */
    {
        $nCols = 0;

        $parms = array();    // hook this into arguments if you want
        $nSkip = intval(@$parms['nSkip']);

        if( !($f = @fopen( $sFilename, "r" )) ) {
            $parms['sErrMsg'] = "Cannot open $filename<br/>";
            return( null );
        }

        /* if bTab is not set use the first row to try to determine the format
         */
        if( isset($parms['bTab']) ) {
            $bTab = SEEDCore_ArraySmartVal( $parms, 'bTab', [false,true] );
        } else {
            $line = fgets($f);
            $bTab = ( strpos( $line, "\t" ) !== false );
            rewind( $f );
        }


        if( !$bTab ) {
            $sDelimiter = @$parms['sDelimiter'] ?: ",";
            $sEnclosure = @$parms['sEnclosure'] ?: "\"";
        } else {
            //$sDelimiter = SEEDStd_ArraySmartVal( $parms, 'sDelimiter', array("\t") );
            //$sEnclosure = SEEDStd_ArraySmartVal( $parms, 'sEnclosure', array("") );
        }
        $sEscape = @$parms['sEscape'] ?: "\\";

        while( !feof( $f ) ) {
            if( $bTab ) {
                // fgetcsv doesn't seem to like a blank sEnclosure, so here it's implemented our way.
                $s = fgets( $f );
                $s = rtrim( $s, " \r\n" );    // fgets retains the linefeed
                if( !strlen( $s ) ) continue;
                $raFields = explode( "\t", $s );
            } else {
// escape parm is available since PHP 5.3 -- try it out
                $raFields = fgetcsv( $f, 0, $sDelimiter, $sEnclosure ); //, $sEscape );
                if($raFields == null)   break;     // eof or error
                if($raFields[0]===null) continue;  // blank line
            }
//var_dump($raFields);

            if( $nSkip ) { --$nSkip; continue; }

            if( $this->raParms['charset'] != $sCharsetFile ) {
                // convert charsets of the fields (could do this above when it's just one string, except it doesn't come that way from fgetcsv)
                array_walk( $raFields,
                            function (&$v,$k,$ra){ $v = iconv( $ra['fromCharset'], $ra['toCharset'], $v ); },
                            array('fromCharset'=>$sCharsetFile, 'toCharset'=>$this->raParms['charset']) );
            }

            $nCols = max( $nCols, count($raFields) );

            $this->raRows[] = $raFields;
        }

        fclose( $f );

        /* Fix column ends if nCols increased during the read
         * Walk through each column, starting with the last, filling !isset with '' until a column where all isset.
         */
        for( $i = 0; $i < count($this->raRows); ++$i ) {
            while( count($this->raRows[$i]) < $nCols ) {
                $this->raRows[$i][] = '';
            }
        }
    }

}


class SEEDTableWrite
{
    private $oPHPE = null;
    private $iSheet = 0;
    private $iRow = 1;
    private $filename = "default.xls";

    function __construct()
    {

    }

    function Start( $raParms = array() )
    /***********************************
        filename    = default filename for saving
        created_by  = your name
        updated_by  = your name, creator if blank
        title
        subject
        description
        keywords
        category

     */
    {
        include_once( W_ROOT."os/PHPExcel1.8/Classes/PHPExcel.php" );
        include_once( W_ROOT."os/PHPExcel1.8/Classes/PHPExcel/IOFactory.php" );

        if( ($p = @$raParms['filename']) )  $this->filename = $p;

        $this->oPHPE = new PHPExcel();
        $this->iSheet = 0;
        $this->iRow = 1;
        $this->SetSheetName( 'Sheet' );

        $oP = $this->oPHPE->getProperties();
        if( ($creator = @$raParms['created_by']) )  $oP->setCreator($creator);
        if( ($p = @$raParms['updated_by']) ) { $oP->setLastModifiedBy($p); }
        else if( $creator )                  { $oP->setLastModifiedBy($creator); }

        if( ($p = @$raParms['title']) )       { $oP->setTitle($p); }
        if( ($p = @$raParms['subject']) )     { $oP->setSubject($p); }
        if( ($p = @$raParms['description']) ) { $oP->setDescription($p); }
        if( ($p = @$raParms['keywords']) )    { $oP->setKeywords($p); }
        if( ($p = @$raParms['category']) )    { $oP->setCategory($p); }
    }

    function SetSheetName( $sheetName )
    {
        $this->oPHPE->getActiveSheet()->setTitle( $sheetName );
    }

    function StartSheet( $sheetName = "" )
    {
        $this->iRow = 1;
        ++$this->iSheet;

        if( !$sheetName ) $sheetName = "Sheet".$this->iSheet;

        $this->oPHPE->createSheet( null );  // null adds to the end, which should be the same as iSheet
        $this->oPHPE->setActiveSheetIndex($this->iSheet);
        $this->SetSheetName( $sheetName );
    }

    function WriteRow( $raData )
    {
        $iCol = 1;
        foreach( $raData as $v ) {
            $cell = chr(64+$iCol).$this->iRow;
            $this->oPHPE->setActiveSheetIndex($this->iSheet)->setCellValue($cell, $v);
            ++$iCol;
        }

        ++$this->iRow;
    }

    function WriteRowMap( $raData, $raMap )
    /**************************************
        Instead of just writing the data row, write one value for each element of raMap: values are raData[raMap[iCol]]
        This is very useful when you have an array with lots of named items, maybe fetched from a db, and you want to put a subset of them in a row.

        e.g. raData = array( 'name'=>'John', 'car'=>'Toyota', 'pet'=>'Sparky' );  raMap = array( 'pet', 'name' );
             output is:  Sparky  |  John
     */
    {
        $iCol = 1;
        foreach( $raMap as $kData ) {
            $v = $raData[$kData];
            $cell = chr(64+$iCol).$this->iRow;
            $this->oPHPE->setActiveSheetIndex($this->iSheet)->setCellValue($cell, $v);
            ++$iCol;
        }

        ++$this->iRow;
    }

    function End()
    {
        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $this->oPHPE->setActiveSheetIndex(0);

        // Redirect output to a client's web browser (Excel5)
        header('Content-type: application/vnd.ms-excel; charset=utf8');
        header("Content-Disposition: attachment;filename=\"{$this->filename}\"");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0,pre-check=0"); // http/1.1
        header("Pragma: public");                                           // http/1.0

//        header("Pragma: no-cache");

//        header('Cache-Control: max-age=0');
//        // If you're serving to IE 9, then the following may be needed
//        header('Cache-Control: max-age=1');
//
//        // If you're serving to IE over SSL, then the following may be needed
//        header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
//        header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
//        header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
//        header ('Pragma: public'); // HTTP/1.0

        $oWriter = PHPExcel_IOFactory::createWriter( $this->oPHPE, 'Excel5' );
        $oWriter->save( 'php://output' );
    }
}


function SEEDTable_LoadFromFile( $filename, $raParms = array() )
/***************************************************************
    Read a spreadsheet file, return an array of rows

    raSEEDTableDef       = the parms for SEEDTable()
    raSEEDTableLoadParms = the parms for SEEDTable::LoadFromFile()
    eLoadType            = SingleSheet | MultiSheet
 */
{
    $bOk = false;
    $sErr = "";
    $raRows = array();

    $oTable = (@$raParms['eLoadType'] == "MultiSheet") ? new SEEDTableSheets( $raParms['raSEEDTableDef'] )    // raRows below will actually be raSheets
                                                       : new SEEDTable( $raParms['raSEEDTableDef'] );

    if( $oTable->LoadFromFile( $filename, @$raParms['raSEEDTableLoadParms'] ) ) {
        list($bOk,$sErr) = $oTable->Validate();
        if( $bOk ) {
            $raRows = $oTable->Associate();
        }
    } else {
        $sErr = "The file was uploaded, but it doesn't seem to be a spreadsheet file.";
    }
    return( array($bOk,$raRows,$sErr) );
}


function SEEDTable_LoadFromUploadedFile( $fileIndex, $raParms )
/**************************************************************
    Read a spreadsheet file that was uploaded as $FILE[$fileIndex]
 */
{
    $bOk = false;
    $sErr = "";
    $raRows = array();

    $f = @$_FILES[$fileIndex];
    if( $f && !@$f['error'] ) {
        list($bOk,$raRows,$sErr) = SEEDTable_LoadFromFile( $f['tmp_name'], $raParms );
    } else {
        $sErr = "The upload was not successful. ";
        if( $f['size'] == 0 ) {
            $sErr .= "No file was uploaded.  Please try again.";
        } else if( !isset($f['error']) ) {
            $sErr .= "No error was recorded.  Please tell Bob.";
        } else {
            $sErr .= "Please tell Bob that error # ${f['error']} was reported.";
        }
    }
    return( array($bOk,$raRows,$sErr) );
}

function SEEDTable_OutputXLSFromRASheets( $raSheets, $raParms = array() )
/************************************************************************
    $raSheets:        array( SheetA => array( array( k1 => v1a, k2 => v2a, ...), array( k1 => v1b, k2 => v2b, ... ) ) ... )

                      N.B. PHPExcel requires all accented chars here to be utf8

    filename:         the download filename
    (other metadata): see SEEDTable::Start
    columns:          the column headers and map for raRows (only these columns of raRows are put in the spreadsheet)
 */
{
    // if columns not defined, use all keys of the first data row
    if( !isset($raParms['columns']) )  $raParms['columns'] = array_keys($raRows[0]);

    $o = new SEEDTableWrite();
    $o->Start( $raParms );

    $bFirst = true;
    foreach( $raSheets as $sheetName => $raRows ) {
        $sheetName = strval($sheetName);    // There was a problem where a sheet name was a number e.g. 2016 and though it was a string until this point,
                                            // arrays seem to convert keys to integers if possible. PHPExcel doesn't like sheet names that are integers.
        if( $bFirst ) {
            // $o->Start creates an initial sheet so just add its name
            $o->SetSheetName( $sheetName );
            $bFirst = false;
        } else {
            $o->StartSheet( $sheetName );
        }

        // header row
        $o->WriteRow( $raParms['columns'] );
        foreach( $raRows as $k => $ra ) {
            $o->WriteRowMap( $ra, $raParms['columns'] );
        }
    }

    $o->End();
}

function SEEDTable_OutputXLSFromRARows( $raRows, $raParms = array() )
/********************************************************************
    $raRows:          array( array( k1 => v1a, k2 => v2a, ...), array( k1 => v1b, k2 => v2b, ... ) )

                      N.B. PHPExcel requires all accented chars here to be utf8

    filename:         the download filename
    (other metadata): see SEEDTable::Start
    columns:          the column headers and map for raRows (only these columns of raRows are put in the spreadsheet)
 */
{
    // if columns not defined, use all keys of the first data row
    if( !isset($raParms['columns']) )  $raParms['columns'] = array_keys($raRows[0]);

    $o = new SEEDTableWrite();
    $o->Start( $raParms );
    // header row
    $o->WriteRow( $raParms['columns'] );

    foreach( $raRows as $k => $ra ) {
        $o->WriteRowMap( $ra, $raParms['columns'] );
    }

    $o->End();
}

function SEEDTable_OutputCSVFromRARows( $raRows, $raParms = array() )
/********************************************************************
    Same as SEEDTable_OutputXLSFromRaRows but in CSV format
    Maybe it's better to do this with PHPExcel, maybe not
 */
{
    // if columns not defined, use all keys of the first data row
    if( !isset($raParms['columns']) )  $raParms['columns'] = array_keys($raRows[0]);
    // if filename not defined, write to stdout
    if( empty($raParms['filename']) )  $raParms['filename'] = 'php://output';

    if( ($f = fopen($raParms['filename'], 'w')) ) {
        fputcsv( $f, $raParms['columns'] );
        foreach( $raRows as $dummy => $row ) {
            $outRow = array();
            foreach( $raParms['columns'] as $col ) {
                $outRow[] = @$row[$col];
            }
            fputcsv( $f, $outRow );
        }

        fclose( $f );
    }
}

?>