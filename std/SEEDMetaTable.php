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
            $oSetup->SetupTable( "SEEDMetaTable_TablesLite",      SEEDMetaTable_TablesLite::SqlCreate,      $bCreate, $sReport ) );
}

?>
