<?
/* Load/Save KFR records in a file
 */

include_once( "KFRecord.php" );


class KFRFileLoad {
    var $nRowsLoaded = 0;
    var $nFirstRowid = 0;
    var $nLastRowid  = 0;
    var $sReport     = "";
    var $kfr;

    function KFRFileLoad( &$kfr ) {
        $this->kfr = &$kfr;
    }

    function GetRowsLoaded()  { return( $this->nRowsLoaded ); }
    function GetReport()      { return( $this->sReport ); }


    function LoadFile( $filename, $map = array(), $parms = array() )
    /***************************************************************
        Load records from a tab-delimited file into a KFR table.
        File may have a header row and/or a map
            - if $map, array indicates db names of file columns in order
            - if no $map, header row required; consitutes a map from file column to db column (names must match)

        Blank rows are ignored, but header row (if any) must be the first row.

        Returns true/false.

        Uses:

        1)  count($map) == 0
            File must contain a header row, corresponding to db column names.
            Cols with unknown header labels are not loaded, warning written in report.
            Header row is not loaded into db; interpretation of iStartRow is not affected by this condition.

        2)  count($map) > 0
            Names in $map must be in KFR, unmatched names cause an error.
            $map[x]=='.' means skip (do not load) column x
            All rows are loaded into the db, starting at index iStartRow (if file has a header row, make this 1 to skip it).


        $filename = a tab-delimited file
        $kfr      = KFR object
        $map      = optional: an array of db column names, which indicate the order of columns in the file.
        $parms    = optional parameters
                        iStartRow => n  : 0-based row of the file to begin loading (default 0).  Useful for skipping
                                          a header.  This is an absolute row number of the file, not adjusted by other
                                          parms or existence of a header row.
     */
    {
        $labels    = array();
        $iStartRow = intval(@$parms['iStartRow']);
        $ok        = true;

        $this->nRowsLoaded = 0;

        if( count($map) > 0 ) {
            /* Cols in file correspond to labels in $map
             * For count($map)==0 case, get labels after file has been opened.
             */
            $labels = $map;
            $this->_checkFieldNames( $labels );
        }


        if( ($f = fopen( $filename, "r" )) ) {

            for( $iRow = 0; !feof($f); ++$iRow ) {
                $s1 = fgets( $f );
                $s1 = rtrim( $s1, " \r\n" );    // fgets retains the linefeed
                if( !strlen($s1) )  continue;   // skip rows with no content.  This always happens at the bottom of the file.
                $s = explode( "\t", $s1 );

                if( !$iRow && count($map) == 0 ) {
                    /* Store column labels, don't load this row.
                     */
                    $labels = $s;
                    $this->_checkFieldNames( $labels );
                } else {
                    /* Process column values
                     */
                    if( $iStartRow <= $iRow ) {
                        $this->kfr->kfr_Clear();
                        for( $i = 0; $i < count($s); ++$i ) {
                            if( $labels[$i] != "." ) {
                                $this->kfr->kfr_SetValue( $labels[$i], $s[$i] );
                            }
                        }
                        if( $this->kfr->kfr_PutDBRow() ) {
                            ++$this->nRowsLoaded;
                            if( !$this->nFirstRowid )  $this->nFirstRowid = $this->kfr->kfr_Rowid();
                            $this->nLastRowid = $this->kfr->kfr_Rowid();
                        } else {
                            $ok = false;
                            break;
                        }
                    }
                }
            }
            fclose( $f );
            $this->kfr->kfr_Clear();
        } else {
            $this->sReport .= "Error: Cannot open $filename.<BR>";
        }

        if( $ok ) {
            $this->sReport .= "Successfully";
        } else {
            $this->sReport .= "With error: ".$this->kfr->kfdb->KFDB_GetErrMsg();
        }
        $this->sReport .= " loaded ".$this->nRowsLoaded." rows from $filename.<BR>";
        if( $ok )  $this->sReport .= "Rowids: ".$this->nFirstRowid." to ".$this->nLastRowid.".<BR>";
        $this->sReport .= "<BR>";

        return( $ok );
    }

    function _checkFieldNames( $labels )
    /***********************************
     */
    {
        $sY = $sN = array();
        foreach( $labels as $v ) {
            if( $v == '.' ) continue;
            if( $this->kfr->kfr_IsBaseField( $v ) ) {
                $sY[] = $v;
            } else {
                $sN[] = $v;
            }
        }
        if( count($sY) )  $this->sReport .= "Found labels: ".implode(", ", $sY)."</BR>";
        if( count($sN) )  $this->sReport .= "<FONT color=red>Unknown labels: ".implode(", ", $sN)."</FONT></BR>";
    }
}

?>
