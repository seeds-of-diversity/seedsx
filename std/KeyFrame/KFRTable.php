<?
/* KFTable
 *
 * Copyright 2008 Seeds of Diversity Canada
 *
 * Load / output data relations in various output formats.
 */

include_once( "KFRelation.php" );

if( defined("WRITE_EXCEL_DIR") )  require(WRITE_EXCEL_DIR."Worksheet.php");
if( defined("WRITE_EXCEL_DIR") )  require(WRITE_EXCEL_DIR."Workbook.php");


class KFTableDump {

    var $bXLS = true;
    var $kfr = NULL;
    var $xlsbook = NULL;
    var $xls = NULL;

    function KFTableDump()
    /*********************
        Output a given KFRecordCursor in various output formats
     */
    {
    }

    function Dump( &$kfrc, $raParms = array(), $bBaseOnly = false )
    /**************************************************************
        Given a KFRecordCursor, output the data set as a file

Want KFRecordCursor and KFRecord to be created via a factory method that allows the client to override ValueXlat.
Want a variation of this function for KFRecordSet

        $raParms:
            format          = {'xls', 'csv'}
            header_filename = the name that appears in the http header, which your browser should prompt for saving
            dest_filename   = the filename on the local disk where the file will be written. "-" causes output to stdout with an http header
            cols            = array of col names to output
            bNoHeader       = inhibit the header row
     */
    {
        $this->bXLS      = (@$raParms['format'] != 'csv');
        $header_filename = empty($raParms['header_filename']) ? ($this->bXLS ? "file.xls" : "file.csv") : $raParms['header_filename'];
        $dest_filename   = empty($raParms['dest_filename'])   ? "-"                                     : $raParms['dest_filename'];
        if( empty($raParms['cols']) ) {
            $raColAliases = $kfrc->kfrel->GetListColAliases( $bBaseOnly );
    // this probably doesn't work if bBaseOnly is false and the kfrel is a join
        } else {
            $raColAliases = $raParms['cols'];
        }

        $this->start( $header_filename, $dest_filename );
        $iRow = 0;

        if( !@$raParms['bNoHeader'] ) {
            // output the header row of column names
            $this->writeRowHeaders( $iRow, $raColAliases );
            ++$iRow;
        }

        while( $kfrc->CursorFetch() ) {
            $raVal = $kfrc->ValuesRA();
            $raVal = $this->RowTranslate( $raVal );
            $this->writeRowData( $iRow, $raColAliases, $raVal );
            ++$iRow;
        }
        $this->end();
    }

    function RowTranslate( $raVal )
    {
        // OVERRIDE to add or change values in a row
        return( $raVal );
    }

    function start( $header_filename, $dest_filename = "-", $charset = "ISO-8859-1" )
    {
        if( $this->bXLS ) {
            $this->xlsStart( $header_filename, $dest_filename, $charset );
        } else {
            header("Content-type: text/plain; charset=$charset");   // the charset should be configurable
        //    header("Content-type: text/csv; charset=ISO-8859-1");   // the charset should be configurable
        //    header("Content-Disposition: attachment; filename=${header_filename}" );
        //    header("Expires: 0");
        //    header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
        //    header("Pragma: public");
        }
    }

    function end()
    {
        if( $this->bXLS )  $this->xlsEnd();
    }

    function writeRowHeaders( $iRow, $raColAliases )
    /***********************************************
       This is used by Dump but it's useful for writers that don't use a KFRecordCursor
     */
    {
        $i = 0;
        foreach( $raColAliases as $a ) {
            if( $this->bXLS ) {
                $this->xlsWrite( $iRow, $i++, $a );
            } else {
                echo $a."\t";
            }
        }
        if( !$this->bXLS )  echo "\r\n";
    }

    function writeRowData( $iRow, $raColNames, $raData )
    /***************************************************
       This is used by Dump but it's useful for writers that don't use a KFRecordCursor
       raData is keyed by the names in raColNames, so it can be a KFRecord::ValuesRA()
     */
    {
        $i = 0;
        foreach( $raColNames as $a ) {
            if( $this->bXLS ) {
                $this->xlsWrite( $iRow, $i++, @$raData[$a] );
            } else {
                echo @$raData[$a]."\t";
            }
        }
        if( !$this->bXLS )  echo "\r\n";
    }


    function xlsStart( $header_filename = "file.xls", $dest_filename = "-", $charset = "ISO-8859-1" )
// Actually the xls writer doesn't seem to know about utf-8 so the charset has to be iso8859-1
    /************************************************************************************************
     */
    {
        if( defined("WRITE_EXCEL_DIR") ) {
            if( $dest_filename = "-" ) {
                header("Content-type: application/vnd.ms-excel; charset=$charset");
                header("Content-Disposition: attachment; filename=${header_filename}" );
                header("Expires: 0");
                header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
                header("Pragma: public");
            }
            if( ($this->xlsbook = new Workbook( $dest_filename )) ) {
                $this->xls =& $this->xlsbook->add_worksheet();
            }
        }
        return( $this->xls );
    }

    function xlsEnd()
    /****************
     */
    {
        if( $this->xlsbook )  $this->xlsbook->close();
    }

    function xlsWrite( $row, $col, $s )
    /**********************************
        Write_Excel interprets the type of $s and saves accordingly.
     */
    {
        $this->xls->write( $row, $col, $s );
    }

    function XLS_Dump( &$kfrc, $raParms = array(), $bBaseOnly = false ) { $raParms['format'] = 'xls'; $this->Dump($kfrc,$raParms,$bBaseOnly); }  // deprecated
    function xlsWriteRowHeaders( $iRow, $raColNames ) { $this->writeRowHeaders($iRow,$raColNames);}                                              // deprecated
    function xlsWriteRowData( $iRow, $raColNames, $raData ) { $this->writeRowHeaders($iRow,$raColNames,$raData);}                                // deprecated
}


?>
