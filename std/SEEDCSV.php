<?php

/* SEEDCSV.php
 *
 * Copyright 2012 Seeds of Diversity Canada
 *
 * Read and manage data from csv or tab-separated files.
 */


class SEEDCSV {
    /* bHeader==false:
     *  The file has no header row of column labels
     *  Values are stored in $raTable[row][col] origin 0
     *
     * bHeader==true
     *  The file has a header row of column labels
     *  Col names are stored in order $raColNames[0...]
     *  Values are stored in $raTable[row][col_name]. Row 0 is the first data row.
     *
     *
     * Various methods are provided for mapping the named columns in the file to an arbitrary set of named columns in the resulting array.
     *
     * The simplest is a parameter in raParms['mapColNames'] = array('col1','col2',...) which yields a data set containing only those columns
     *
     * Filtering of rows is enabled by the EachRow() method, which reports each mapped row and allows the client to choose whether or
     * not to store it. This is also a good way to implement a non-storing parser for large data files (preventing out-of-memory issues).
     */

    private $raParms = array();
    private $raTable = array();
    private $raColNames = array();     // col names after mapping
    private $raColNamesOrig = array(); // col names before mapping
    private $raColMap = array();
    private $nCols = 0;
    private $nRows = 0;
    private $bHeader = false;

    function __construct( $raParms )
    {
        $this->raParms = $raParms;
    }

    function Cols() { return( $this->nCols ); }
    function Rows() { return( $this->nRows ); }

    function Value( $iCol, $iRow )
    /*****************************
     */
    {
        return( $this->bHeader ? $this->raTable[$iRow][$this->raColNames[$iCol]]
                               : $this->raTable[$iRow][$iCol] );
    }

    function ValueNamedCol( $sColName, $iRow )
    /*****************************************
     */
    {
        if( !$this->bHeader ) return( NULL );

        return( $this->raTable[$iRow][$sColName] );
    }

    function GetRow( $iRow )
    /***********************
     */
    {
        return( $this->raTable[$iRow] );
    }

// seems to not be used.
// use SEEDTable::LoadFromFile() instead

    function ReadFile( $filename, &$parms )
    /**************************************
        Read tab-delimited file.
        Skip blank lines (don't store them).

        $parms: bTab (default:false)    = same as (sDelimiter="\t", sEnclosure='', sEscape='\')
                bHeader (default:true)  = true: the first row contains column names
                nSkip                   = # rows to skip before reading (e.g. use nSkip=1,bHeader=false to skip a header row)
                sDelimiter              = single char separating fields
                sEnclosure              = single char before and after fields (not required if not necessary)
                sEscape                 = single char to escape delimiter and enclosure chars

                Output:
                sErrMsg

     */
    {
        $this->bHeader = SEEDStd_ArraySmartVal( $parms, 'bHeader', array(true, false) );
        $nSkip = intval(@$parms['nSkip']);

        if( !($f = @fopen( $filename, "r" )) ) {
            $parms['sErrMsg'] = "Cannot open $filename<BR/>";
            return( null );
        }

        /* if bTab is not set use the first row to try to determine the format
         */
        if( isset($parms['bTab']) ) {
            $bTab = SEEDStd_ArraySmartVal( $parms, 'bTab', array(false,true) );
        } else {
            $line = fgets($f);
            $bTab = ( strpos( $line, "\t" ) !== false );
            rewind( $f );
        }


        if( !$bTab ) {
            $sDelimiter = SEEDStd_ArraySmartVal( $parms, 'sDelimiter', array(",") );
            $sEnclosure = SEEDStd_ArraySmartVal( $parms, 'sEnclosure', array("\"") );
            $sEscape    = SEEDStd_ArraySmartVal( $parms, 'sEscape', array("\\") );
        }

        while( !feof( $f ) ) {
            if( $bTab ) {
                // fgetcsv doesn't seem to like a blank sEnclosure, so here it's implemented our way.
                $s = fgets( $f );
                $s = rtrim( $s, " \r\n" );    // fgets retains the linefeed
                if( !strlen( $s ) ) continue;
                $raFields = explode( "\t", $s );
            } else {
                $raFields = fgetcsv( $f, 0, $sDelimiter, $sEnclosure ); //, $sEscape );     escape parm is only available in PHP 5.3
                if($raFields == null)   break;     // eof or error
                if($raFields[0]===null) continue;  // blank line
            }
//var_dump($raFields);

            if( $nSkip ) { --$nSkip; continue; }

            if( $this->bHeader && !$this->nCols ) {
                /* Header row: Store column labels
                 */
                $this->raColNamesOrig = $raFields;
                $this->raColNames = $this->MapColNames( $raFields );
                $this->nCols = count($this->raColNames);

                /* Make sure all columns have names, because of the way we store raTable
                 */
                for( $i = 0; $i < $this->nCols; ++$i ) {
                    if( empty($this->raColNames[$i]) )  $this->raColNames[$i] = "csv$i";
                }
            } else {
                /* Data row: Store column values
                 */
                $raFields = $this->MapDataRow( $raFields );

                if( !$this->EachRow( $raFields ) )  continue;  // EachRow returns false if it doesn't want us to store this row

                if( count($raFields) > $this->nCols ) {
                    // expand the table to admit more columns than we first expected
                    if( $this->bHeader ) {
                        // pad raColNames with some dummy names
                        for( $i = count($raFields)-1; $i >= $this->nCols; --$i ) {
                            $this->raColNames[$i] = "csv$i";
                        }
                    }
                    $this->nCols = count($raFields);
                }
                for( $i = 0; $i < $this->nCols; ++$i ) {
                    if( $this->bHeader ) {
                        $this->raTable[$this->nRows][$this->raColNames[$i]] = (isset($raFields[$i]) ? $raFields[$i] : '');
                    } else {
                        $this->raTable[$this->nRows][$i] = (isset($raFields[$i]) ? $raFields[$i] : '');
                    }
                }
                ++$this->nRows;
            }
        }

        fclose( $f );

        /* Fix column ends if nCols increased during the read
         * Walk through each column, starting with the last, filling !isset with '' until a column where all isset.
         */
        for( $i = $this->nCols-1; $i; --$i ) {
            $bOK = true;
            for( $j = 0; $j < $this->nRows; ++$j ) {
                if( $this->bHeader ) {
                    if( !isset($this->raTable[$j][$this->raColNames[$i]]) ) {
                        $this->raTable[$j][$this->raColNames[$i]] = '';
                        $bOK = false;
                    }
                } else {
                    if( !isset($this->raTable[$i][$j]) ) {
                        $this->raTable[$j][$i] = '';
                        $bOK = false;
                    }
                }
            }
            if( $bOK ) break;
        }

        return( $this->raTable );
    }

    function MapColNames( $raNames )
    /*******************************
        $raNames is the array of names appearing at the file header (if any).
        Return the array of names corresponding to the columns that will actually be stored in raTable

        a) override this method to implement your own
        b) set fnMapColNames callback in raParms
        c) set raParms['mapColNames'] to use the built-in mapper
        d) default: this base method just uses the file's column names as-is
     */
    {
        if( isset( $this->raParms['fnMapColNames'] ) ) {
            $raNames = call_user_func($this->raParms['fnMapColNames'], $raNames );
        } else if( isset($this->raParms['mapColNames']) && count($this->raColNamesOrig) ) {
            // mapColNames contains a list of column names. The output data set will match this list.
            $raNames = $this->raParms['mapColNames'];
            // make a reverse map of raColNamesOrig (col name => col index) so we can find the data fields per name in MapDataRow
            $this->raColMap = array();
            foreach( $this->raColNamesOrig as $k => $v ) {
                $this->raColMap[$v] = $k;
            }
        }
        return( $raNames );
    }

    function MapDataRow( $raFields )
    /*******************************
        $raFields is the array of fields in the current data row.
        Return the array of fields corresponding to the columns mapped as in MapColNames
     */
    {
        if( isset( $this->raParms['fnMapDataRow'] ) ) {
            $raFields = call_user_func($this->raParms['fnMapDataRow'], $raFields );
        } else if( isset( $this->raParms['mapColNames'] ) && count($this->raColMap) ) {
            $raOut = array();
            foreach( $this->raParms['mapColNames'] as $col ) {
                $raOut[$col] = @$raFields[$this->raColMap[$col]];
            }
            $raFields = $raOut;
        }
        return( $raFields );
    }

    function EachRow( $raRow )
    /*************************
        Called for each row read from the file, after mapping via MapDataRow.
        If there are named columns in a header row, the row is keyed by name; otherwise keyed by number as returned by MapDataRow.
        Return true to store the row in raTable; false to not store it.

        For very large files, this allows a derived class to process the rows on-the-fly.
     */
    {
        return( isset( $this->raParms['fnEachRow'] ) ? call_user_func($this->raParms['fnEachRow'], $raRow )
                                                     : true );
    }
}


function SEEDCSV_LoadDataToDB( $kfdb, $sFile, $sTableName, &$raParms )
/*********************************************************************
    Load the given CSV file into the given table.
    The file must have a header row that corresponds to field names in the table (in any order).
    If the table exists, rows are added to it using INSERT (so rows with existing primary keys might fail to insert).
    If the table doesn't exist, it's created as a temporary table.
    If $sTableName="" create a unique temporary table and return its name

    Return the table name if successful, or NULL if not

    raParms: raCols is a subset of columns to read (if absent, read all columns as strings)
                 array( col1 => "text", col2 => "integer not null", col3 => "date", ... )
                 The cols in the array do not have to be in the same order as the cols in the file

             deleteIfBlank : control blank rows using "DELETE FROM table WHERE (deleteIfBlank IS NULL OR deleteIfBlank='')"
             raTrimCols    : trim() the columns named here

             sErrMsg (output)

    Case 1: the file matches the db table exactly - raCols not necessary
    Case 2: the file and db table have the same columns but in a different order - raCols not necessary (they'll be mapped)
    Case 3: the file has more columns than the db table - currently need raCols to define the subset (order not important)
TODO        but this could be solved using DESCRIBE to make a subset map
    Case 4: db table doesn't exist - raCols defines the temp table, possibly a subset of the file's columns, order not important
 */
{
// $kfdb->SetDebug(2);

    if( $sTableName ) {
        $sTableExists = ( $kfdb->Query1( "SHOW TABLES LIKE '$sTableName'" ) == $sTableName );
    } else {
        $sTableName = "tmp".time();
        $sTableExists = false;  // for now assuming no two people are using this at the same time!
    }

    // Get the first line, with line ending intact, and analyse it for CSV-style and field names
    if( !($f = fopen( $sFile, "r" )) ) {
        $raParms['sErrMsg'] = "LoadCSVToDB: Cannot open $sFile";
        return( NULL );
    }
    $oldSetting = ini_get( "auto_detect_line_endings" );  // this is almost always false anyway according to PHP docs
    ini_set( "auto_detect_line_endings", false );
    $line = fgets($f);
    ini_set( "auto_detect_line_endings", $oldSetting );
    fclose($f);

    // Figure out which end-of-line this file uses
    if( substr( $line, -1, 1) == "\r" ) {
        $sEol = "\\r";  // text representation \r for the LOAD DATA statement (not an actual CR character)
        $line = substr( $line, 0, strlen($line)-1 );
    } else if( substr( $line, -2, 2 ) == "\r\n" ) {
        $sEol = "\\r\\n";
        $line = substr( $line, 0, strlen($line)-2 );
    } else if( substr( $line, -1, 1) == "\n" ) {
        $sEol = "\\n";
        $line = substr( $line, 0, strlen($line)-1 );
    } else {
        $raParms['sErrMsg'] = "LoadCSVToDB: Unrecognized line ending";
        return( NULL );
    }

    // Figure out which field separator this file uses
    // assuming that any \t is a field separator and only tabs and commas are ever used
    if( strpos( $line, "\t" ) !== false ) {
        $sep = "\t";    // actual tab character
        $sSep = "\\t";  // text representation \t
    } else {
        $sep = $sSep = ",";
    }

    // Get the column names from the file header
    $raColsFile = explode( $sep, $line );


    // Define what the database table (or subset of columns) should look like.
    //
    // If raCols is defined, use it to create a map between the file and db table, and to define a temp table if needed.
    // If raCols is not defined, then the db table is assumed to have the same columns as the file (don't have to be in the same order).
    $raColsDB = array();
    $raColsSql = array();
    if( !isset($raParms['raCols']) ) {
        // All we know are the column names in the file header.
        // The db table must have the same columns as the file (not necessarily in the same order).
        if( !$sTableExists ) {  // only need to do this if making a temp table
            foreach( $raColsFile as $v ) {
                $raColsDB[$v] = 'TEXT';
            }
        }
        $raColsSql = $raColsFile;
    } else {
        // The caller has defined a set of db columns, probably a subset of the file columns.  Map the file to the db subset.
        foreach( $raColsFile as $v ) {
            if( @$raParms['raCols'][$v] ) {
                $raColsDB[$v] = $raParms['raCols'][$v];  // col => type
                $raColsSql[] = $v;
            } else {
                $raColsSql[] = "@dummy";  // LOAD DATA will load this file column to a dummy sql variable instead of a table col
            }
        }
    }
    $sColsSql = implode( ',', $raColsSql );

    // Create the table if necessary
    if( !$sTableExists ) {
        $sql = "CREATE TEMPORARY TABLE IF NOT EXISTS $sTableName ("
              .SEEDStd_ImplodeKeyValue( $raColsDB, " ", "," )
              .")";
        if( !$kfdb->Execute($sql) ) {
            $raParms['sErrMsg'] = "LoadCSVToDB: cannot create table $sql";
            return( NULL );
        }
    }

    // Load the file
    // Unintuitively, LOCAL means to load from the client's machine. This might be disabled on a hosted server.
    // Without LOCAL, you need GRANT FILE permission to access files on the server, which is also likely disabled on a hosted server
    // because it's a global privilege.  N.B. GRANT ALL does not include GRANT FILE!
    $sql = "LOAD DATA LOCAL INFILE '$sFile' INTO TABLE $sTableName"
          ." FIELDS TERMINATED BY '$sSep'"
          ." LINES TERMINATED BY '$sEol'"
          ." IGNORE 1 LINES"
          ." ($sColsSql)";
    if( !$kfdb->Execute($sql) ) {
        $raParms['sErrMsg'] = "LoadCSVToDB: failed loading file $sql";
        return( NULL );
    }

    // Trim specified columns
    if( isset($raParms['raTrimCols']) ) {
        $sql = "UPDATE $sTableName SET";
        $ra = array();
        foreach( $raParms['raTrimCols'] as $col ) {
            $ra[] = " $col=TRIM($col)";
        }
        $sql .= implode( ",", $ra );
        $kfdb->Execute( $sql );
    }

    // Control blank rows by identifying columns that must not be blank (after trimming)
    if( ($sCol = @$raParms['deleteIfBlank']) ) {
        $sql = "DELETE FROM $sTableName WHERE ($sCol IS NULL OR $sCol='')";
        $kfdb->Execute( $sql );
    }
    if( isset($raParms['raDeleteIfBlank']) ) {
        foreach( $raParms['raDeleteIfBlank'] as $sCol ) {
            $sql = "DELETE FROM $sTableName WHERE ($sCol IS NULL OR $sCol='')";
            $kfdb->Execute( $sql );
        }
    }

    return( $sTableName );
}

?>
