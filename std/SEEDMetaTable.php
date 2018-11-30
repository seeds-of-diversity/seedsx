<?php

include( SEEDCORE."SEEDMetaTable.php" );    // some of this file has been moved here - move the rest

/* SEEDMetaTable
 *
 * Copyright (c) 2011-2015 Seeds of Diversity Canada
 *
 * Emulate a set of database tables, storing their data in a single table structure.
 * This is a handy way to store stuff in a table without really creating a new database table.
 *
 * Only simple lookups are possible, not joins or other advanced database features.
 *
 * There are 3 implementations here:
 *     1) SEEDMetaTable_Tables : a system of virtual tables, columns, rows, and fields
 *            fairly complex, powerful enough to support an application that uses tabular data.
 *            Designed with ordered cols and rows like a spreadsheet.
 *
 *     2) SEEDMetaTable_TablesLite : virtual tables that group urlparm tuples into sets of rows indexable by arbitrary string keys
 *            A simpler implementation than SEEDMetaTables_Tables, but appropriate for many applications.
 *
 *     3) SEEDMetaTable_StringBucket : a place to store strings, keyed by (namespace,key)
 *            sometimes you just want a place to throw random stuff
 */

/****************************************************************************
 * TablesLite
 */
define("SEEDMETATABLE_DB_TABLE_SEEDMETATABLE_TABLESLITE",
"
CREATE TABLE SEEDMetaTable_TablesLite (
    # These are the virtual tables in the TablesLite system

        _key        INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT,
        _created    DATETIME,
        _created_by INTEGER,
        _updated    DATETIME,
        _updated_by INTEGER,
        _status     INTEGER DEFAULT 0,

    table_name              VARCHAR(200) NOT NULL,
    permclass               INTEGER NOT NULL,

    INDEX (table_name(20))
);
"
);

define("SEEDMETATABLE_DB_TABLE_SEEDMETATABLE_TABLESLITE_ROWS",
"
CREATE TABLE SEEDMetaTable_TablesLite_Rows (
    # These are the rows of the virtual tables in the TablesLite system

        _key        INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT,
        _created    DATETIME,
        _created_by INTEGER,
        _updated    DATETIME,
        _updated_by INTEGER,
        _status     INTEGER DEFAULT 0,

    fk_SEEDMetaTable_TablesLite  INTEGER NOT NULL,
    k1                      VARCHAR(200) NULL,        # arbitrary indexed key
    k2                      VARCHAR(200) NULL,        # arbitrary indexed key
    k3                      VARCHAR(200) NULL,        # arbitrary indexed key
    vals                    TEXT NOT NULL,            # urlparm of non-indexed values  e.g. field1=val1&field2=val2...

    INDEX (fk_SEEDMetaTable_TablesLite),
    INDEX (fk_SEEDMetaTable_TablesLite, k1),
    INDEX (fk_SEEDMetaTable_TablesLite, k2),
    INDEX (fk_SEEDMetaTable_TablesLite, k3)
);
"
);


/****************************************************************************
 * SEEDMetaTables_Tables
 */
define("SEEDMETATABLE_DB_TABLE_SEEDMETATABLE_TABLES",
"
CREATE TABLE SEEDMetaTable_Tables (
    # These are the virtual tables in the SEEDMetaTable system

        _key        INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT,
        _created    DATETIME,
        _created_by INTEGER,
        _updated    DATETIME,
        _updated_by INTEGER,
        _status     INTEGER DEFAULT 0,

    table_name              VARCHAR(200) NOT NULL,
    permclass               INTEGER NOT NULL,

    INDEX (name(20))
);
"
);

define("SEEDMETATABLE_DB_TABLE_SEEDMETATABLE_COLS",
"
CREATE TABLE SEEDMetaTable_Cols (
    # These are the columns in each virtual table

        _key        INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT,
        _created    DATETIME,
        _created_by INTEGER,
        _updated    DATETIME,
        _updated_by INTEGER,
        _status     INTEGER DEFAULT 0,

    fk_SEEDMetaTable_Tables INTEGER NOT NULL,
    col_name                VARCHAR(200) NOT NULL,
    nOrder                  INTEGER NOT NULL,             # the order that the columns should be returned, by ascending number
    eType                   ENUM('S','I','F','DT','D','T','R') NOT NULL,

    INDEX (fk_SEEDMetaTable_Tables)
);
"
);

define("SEEDMETATABLE_DB_TABLE_SEEDMETATABLE_ROWS",
"
CREATE TABLE SEEDMetaTable_Rows (
    # The rows in each virtual table are defined only by this _key, which groups the Fields.

        _key        INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT,
        _created    DATETIME,
        _created_by INTEGER,
        _updated    DATETIME,
        _updated_by INTEGER,
        _status     INTEGER DEFAULT 0,

    fk_SEEDMetaTable_Tables INTEGER NOT NULL,
    nOrder                  INTEGER NOT NULL,             # the order that the rows should be returned, by ascending number

    INDEX (fk_SEEDMetaTable_Tables)
);
"
);

define("SEEDMETATABLE_DB_TABLE_SEEDMETATABLE_FIELDS",
"
CREATE TABLE SEEDMetaTable_Fields (
    # The fields of every table are stored as tuples (table,row,col)

        _key        INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT,
        _created    DATETIME,
        _created_by INTEGER,
        _updated    DATETIME,
        _updated_by INTEGER,
        _status     INTEGER DEFAULT 0,

    fk_SEEDMetaTable_Tables INTEGER NOT NULL,
    fk_SEEDMetaTable_Cols   INTEGER NOT NULL,
    fk_SEEDMetaTable_Rows   INTEGER NOT NULL,
    val_s                   TEXT         NULL,
    val_i                   INTEGER      NULL,
    val_f                   DECIMAL(8,3) NULL,
    val_dt                  DATETIME     NULL,         # d stored with default time (which is not retrieved), t stored with default date (which is not retrieved)
#   val_rnum                INTEGER      NULL,         # stored as rnum|rden in val_s
#   val_rden                INTEGER      NULL,

    INDEX (fk_SEEDMetaTable_Tables)
);
"
);

class SEEDMetaTable_TablesLite
/*****************************
    All rows of all tables are stored in SEEDMetaTables_TableLiteRows.
    Those rows are grouped by table.
    The caller can place values in various indexed key columns, which facilitate fast lookups.
    The caller can place other related named values in an urlparm field, which does not allow lookups.

    This object is stateless, so it can manage any number of open tables simultaneously.
 */
{
    public $kfdb;

// uid for _created_by and _updated_by, same as bucket above
    function __construct( KeyFrameDB $kfdb )
    {
        $this->kfdb = $kfdb;
    }

    function OpenTable( $tablename )
    {
        if( !($kTable = $this->kfdb->Query1( "SELECT _key FROM SEEDMetaTable_TablesLite WHERE _status='0' AND table_name='$tablename'" )) ) {
            $kTable = $this->kfdb->InsertAutoInc( "INSERT INTO SEEDMetaTable_TablesLite (_key, table_name) VALUES (NULL,'$tablename')" );
        }
        return( $kTable );
    }

    function GetRows( $kTable, $k1 = NULL, $k2 = NULL, $k3 = NULL )
    /**************************************************************
        Return all rows that match all of the lookup criteria (AND)
            array( TableLiteRow._key => array( k1 => v1, k2 => v2, k3 => v3, vals => array(...) ), ... )
     */
    {
        $raCond = array( "fk_SEEDMetaTable_TablesLite='$kTable'" );
        if( $k1 !== NULL )  $raCond[] = "k1='".addslashes($k1)."'";
        if( $k2 !== NULL )  $raCond[] = "k2='".addslashes($k2)."'";
        if( $k3 !== NULL )  $raCond[] = "k3='".addslashes($k3)."'";
        $sCond = implode( " AND ", $raCond );

        $raRet = array();
        if( ($dbc = $this->kfdb->CursorOpen( "SELECT * from SEEDMetaTable_TablesLite_Rows WHERE _status='0' AND $sCond" ) )) {
            while( $ra = $this->kfdb->CursorFetch( $dbc ) ) {
                $raRet[$ra['_key']] = $this->unpackRow($ra);
            }
        }
        return( $raRet );
    }

    function GetRowByKey2( $kRow, $raMapKeys = array() )
    /***************************************************
        Get the row with _key kRow, and return just the values. Optionally map k1, k2, k3 to meaningful names
     */
    {
        $ra = $this->kfdb->QueryRA( "SELECT * FROM SEEDMetaTable_TablesLite_Rows WHERE _key='$kRow'");

        return( @$ra['_key'] ? $this->unpackRow2($ra,$raMapKeys) : NULL );
    }

    function GetRowByKey( $kRow ) // deprecate
    {
        $ra = $this->kfdb->QueryRA( "SELECT * FROM SEEDMetaTable_TablesLite_Rows WHERE _key='$kRow'");

        return( @$ra['_key'] ? array( $ra['_key'] => $this->unpackRow($ra) ) : NULL );
    }

    function EnumKeys( $kTable, $keyname )
    {
        $ra = array();

        if( in_array( $keyname, array('k1','k2','k3')) ) {
            $ra = $this->kfdb->QueryRowsRA1(
                    "SELECT distinct $keyname FROM SEEDMetaTable_TablesLite_Rows "
                   ."WHERE _status='0' AND fk_SEEDMetaTable_TablesLite='$kTable'" );
        }
        return( $ra );
    }

    private function unpackRow( $ra )
    {
        $ra1 = array();
        $ra1['k1'] = $ra['k1'];
        $ra1['k2'] = $ra['k2'];
        $ra1['k3'] = $ra['k3'];
        $ra1['vals'] = SEEDStd_ParmsURL2RA( $ra['vals'] );
        return( $ra1 );
    }

    private function unpackRow2( $ra, $raMapKeys = array() )
    /*******************************************************
        Unpack all values into a plain array, optionally mapping k1,k2,k3 to meaningful names
     */
    {
        $ra1 = SEEDStd_ParmsURL2RA( $ra['vals'] );
        if( @$raMapKeys['k1'] ) { $ra1[$raMapKeys['k1']] = $ra['k1']; } else { $ra1['k1'] = $ra['k1']; }
        if( @$raMapKeys['k2'] ) { $ra1[$raMapKeys['k2']] = $ra['k2']; } else { $ra1['k2'] = $ra['k2']; }
        if( @$raMapKeys['k3'] ) { $ra1[$raMapKeys['k3']] = $ra['k3']; } else { $ra1['k3'] = $ra['k3']; }

        return( $ra1 );
    }


    function PutRow( $kTable, $kRow, $raVals, $k1 = NULL, $k2 = NULL, $k3 = NULL )
    /*****************************************************************************
        Put a row in the table.  If kRow is 0 make a new row else overwrite the existing row.
        Return the resulting kRow if successful.
     */
    {
        $sVals = SEEDStd_ParmsRA2URL( $raVals );
        $k1 = ($k1 === NULL ? "NULL" : "'$k1'");
        $k2 = ($k2 === NULL ? "NULL" : "'$k2'");
        $k3 = ($k3 === NULL ? "NULL" : "'$k3'");
        if( $kRow ) {
            if( !$this->kfdb->Execute( "UPDATE SEEDMetaTable_TablesLite_Rows SET _updated=NOW(),k1=$k1,k2=$k2,k3=$k3,vals='".addslashes($sVals)."' "
                                         ."WHERE _key='$kRow'" ) ) {
                $kRow = 0;
            }
        } else {
            $kRow = $this->kfdb->InsertAutoInc( "INSERT INTO SEEDMetaTable_TablesLite_Rows (_key,_created,_updated,fk_SEEDMetaTable_TablesLite,k1,k2,k3,vals) "
                                               ."VALUES (NULL,NOW(),NOW(),$kTable,$k1,$k2,$k3,'".addslashes($sVals)."')" );
        }
        return( $kRow );
    }

    function DeleteRow( $kRow )
    {
        $this->kfdb->Execute( "DELETE FROM SEEDMetaTable_TablesLite_Rows WHERE _key='$kRow'" );
    }

}

class SEEDMetaTable
/******************
    Each instance is a cursor on a single row in one table, so the value can be altered and re-written to database
 */
{
    var $kCurrTable = 0;          // _key of the current table
    var $raCols = array();        // list of col_name=>eType for the current table, sorted by nOrder
    var $raKRows = array();       // list of _keys of the rows being examined  ( 1 or many )
    var $iNextRow = 0;            // index of the next row in raKRows, i.e. the one fetched by GetNextRow ( 0 if just one row in raKRows )
    var $raCurrFields = array();  // fields of the current row, by col_name=>value

    var $kfdb = NULL;

    function __construct( KeyFrameDB $kfdb )
    {
        $this->kfdb = $kfdb;
        $this->Clear();
    }

    function Clear()
    {
        $this->kCurrTable = $this->iNextRow = 0;
        $this->raCols = array();
        $this->raKRows = array();
        $this->raCurrFields = array();
    }

    function OpenTable( $table_name )
    {
        $this->Clear();
        if( ($this->kCurrTable = $this->kfdb->Query1( "SELECT _key FROM SEEDMetaTable_Tables WHERE _status=0 AND table_name='$table_name'" )) ) {
            if( ($dbc = $this->kfdb->CursorOpen("SELECT col_name,eType FROM SEEDMetaTable_Cols "
                                               ."WHERE _status=0 AND fk_SEEDMetaTable_Tables='{$this->kCurrTable}' "
                                               ."ORDER BY nOrder")) ) {
                while( $ra = $this->kfdb->CursorFetch( $dbc ) ) {
                    $this->raCols[$ra[0]] = $ra[1];
                }
            }
            $this->kfdb->CursorClose( $dbc );

            if( ($dbc = $this->kfdb->CursorOpen("SELECT _key FROM SEEDMetaTable_Rows "
                                               ."WHERE _status=0 AND fk_SEEDMetaTable_Tables='{$this->kCurrTable}' "
                                               ."ORDER BY nOrder")) ) {
                while( $ra = $this->kfdb->CursorFetch( $dbc ) ) {
                    $this->raKRows[] = $ra[0];
                }
            }
            $this->kfdb->CursorClose( $dbc );
        }

        return( count( $this->raCols ) ? true : false );
    }

    function GetRow()
    /****************
        Get the next row.  The next row is the one currently referenced by iNextRow, so advance that index afterward.
     */
    {
        // get the next row
        $this->raCurrFields = array();
        if( $this->iNextRow + 1 >= count($this->raKRows) )  return( false );

        if( ($dbc = $this->kfdb->CursorOpen( "SELECT * FROM SEEDMetaTable_Fields F "
                                            ."WHERE _status=0 "
                                            ."AND fk_SEEDMetaTable_Rows='".$this->raKRows[$this->iNextRow]."'")) ) {
            while( $ra = $this->kfdb->CursorFetch($dbc) ) {
            	switch( $this->raCols[$ra['col_name']] ) {
            	    case 'S':  $this->raCurrFields[$ra['col_name']] = $ra['val_s'];                  break;
            	    case 'I':  $this->raCurrFields[$ra['col_name']] = intval($ra['val_i']);          break;
            	    case 'F':  $this->raCurrFields[$ra['col_name']] = floatval($ra['val_f']);        break;
            	    case 'DT': $this->raCurrFields[$ra['col_name']] = $ra['val_d'];                  break;
            	    case 'D':
                        // just get the date portion
            	        $this->raCurrFields[$ra['col_name']] = $ra['val_d'];
            	        break;
            	    case 'T':
            	        // just get the time portion
            	        $this->raCurrFields[$ra['col_name']] = $ra['val_d'];
            	        break;
            	    case 'R':
            	        $r = explode('|', $ra['val_s']);
            	        // how to store this?
                        break;
            	}
            }
        }
        ++$this->iNextRow;
        return( count($this->raCurrFields));
    }
}


function SEEDMetaTable_Setup( $oSetup, &$sReport, $bCreate = false )
/*******************************************************************
    Test whether the tables exist.
    bCreate: create the tables and insert initial data if they don't exist.

    Return true if exists (or create is successful if bCreate); return a text report in sReport

    N.B. $oSetup is a SEEDSetup.  This file doesn't include SEEDSetup.php because the setup code is very rarely used.
         Instead, the code that calls this function knows about SEEDSetup.
 */
{
    $sReport = "";
    return( $oSetup->SetupTable( "SEEDMetaTable_StringBucket",    SEEDMetaTable_StringBucket::SqlCreate,    $bCreate, $sReport ) &&
            $oSetup->SetupTable( "SEEDMetaTable_TablesLite",      SEEDMETATABLE_DB_TABLE_SEEDMETATABLE_TABLESLITE,      $bCreate, $sReport ) &&
            $oSetup->SetupTable( "SEEDMetaTable_TablesLite_Rows", SEEDMETATABLE_DB_TABLE_SEEDMETATABLE_TABLESLITE_ROWS, $bCreate, $sReport ) );
}

?>
