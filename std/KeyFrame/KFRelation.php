<?php

/* KFRelation.php
 *
 * Copyright (c) 2006-2018 Seeds of Diversity Canada


KeyFrameRelation allows complex multi-table data relationships to be specified, and managed in a relation-based manner.

A Relation is a logical tuple of columns from one or more tables.
A View is an ordered set of data rows for a Relation, after (optional) filter and sort.
A Window is a contiguous span of ordered rows from a View, numbering from one to count(View) rows.


The relation is defined at construction of KeyFrameRelation.
Every relation has a base table, and optionally foreign tables that are related by keys.  Any number of foreign tables
may be connected to the relation, at any level of removal from the base table.


KeyFrameRelation, and its Record classes, can SELECT complete rows of the relation, INSERT new rows into the base table,
and UPDATE columns in the base table.

The system is divided into three levels:
1) KeyFrameDB: allow complete access to the database engine
2) KeyFrameRelation: uses KeyFrameDB to read/write db within the constraints of the defined relation
3) KFRecord: created by KeyFrameRelation to contain/update data of a single record of the relation
   KFRecordCursor: an extension of KFRecord, created by KeyFrameRelation to read/update a set of records


1) Create a KeyFrameDB
2) Create a KeyFrameRelation, using the KFDB and a relation definition.
3) Use KeyFrameRelation to create cursors on the relation, select single rows into Record classes
4) Use cursors and Records to read, write, insert individual rows


KeyFrameRelation::CreateRecord()        - make an empty KFRecord for the relation
KeyFrameRelation::CreateRecordFromRA()  - make a new KFRecord from the data in a given array (does not consider magic_quotes)
KeyFrameRelation::CreateRecordFromGPC() - make a new KFRecord from a GPC global array (handles magic_quotes)
KeyFrameRelation::CreateRecordCursor()  - get a KFRecordCursor on the relation
KeyFrameRelation::GetRecordFromDB()     - fetch a KFRecord from the database
KeyFrameRelation::GetRecordFromDBKey()  - fetch a KFRecord from the database by base _key
KFRecordCursor::CursorFetch()           - load the KFRecord with the next record from the cursor
KFRecord::GetValue()/SetValue()         - get and set values of the record
KFRecord::PutDBRow()                    - write any changed base table values to the database (use this for INSERT and UPDATE)



Database tables that use KeyFrameRelation/KFRecord must have the following columns.
The _key column must be the first AUTO_INCREMENT column in the table.

        _key        INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT,
        _created    DATETIME,
        _created_by INTEGER,
        _updated    DATETIME,
        _updated_by INTEGER,
        _status     INTEGER DEFAULT 0,

***
alter table TTT change _rowid _key INTEGER NOT NULL AUTO_INCREMENT;
***

TODO: add "fnPrePopulateNewRecord" = "{fn}" - this is invoked in CreateRecord, after fkDefaults.


Sample KeyFrameRelation definition:
$kfreldef =
    array( "Tables"=>array( array( "Table" => 'table1',
                                   "Type"  => 'Base',
                                   "Fields" => array( array("col"=>"fk_table2", "type"=>"K"),
                                                      array("col"=>"title",     "type"=>"S"),
                                                      array("col"=>"quantity",  "type"=>"I", "default"=> 1) ) ),
                            array( "Table" => "table2",
                                   "Alias" => "MyTable2",
                                   "Type"  => "Parent",
                                   "Fields" => array( array("col"=>"name",    "type"=>"S"),
                                                      array("col"=>"year",    "type"=>"I", "alias"=>"t2year") ) ) ) );



Data types:
    S+  = Value() returns the db value of the string field.
          GetFromRA stores new values in a separate array, to be prepended at db update


*/


include_once( "KFDB.php" );


/* _status codes
 */
define("KFRECORD_STATUS_NORMAL",        "0");
define("KFRECORD_STATUS_DELETED",       "1");
define("KFRECORD_STATUS_HIDDEN",        "2");


/* These constants tell the KFRecord array-reading code what kind of array it is reading
 */
define("KFRECORD_DATASOURCE_RA_GPC",    "1");
define("KFRECORD_DATASOURCE_RA_NONGPC", "2");
define("KFRECORD_DATASOURCE_DB",        "3");



define("KFRECORD_STD_COLUMNS", "_key        INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT,"
                              ."_created    DATETIME,"
                              ."_created_by INTEGER,"
                              ."_updated    DATETIME,"
                              ."_updated_by INTEGER,"
                              ."_status     INTEGER DEFAULT 0" );



class KeyFrameRelation {
/*********************
 */
    // constructor parms
    var $kfdb;              // ref to a KeyFrameDB instance
    var $kfrdef;
    var $uid;

    // internal constants
    var $baseTable = null;  // ref to the base table definition in $this->kfrdef
    private $baseTableAlias;
    var $raTableN2A;        // store all table names and aliases for reference ( array of tableName => tableAlias )
    var $raColAlias;        // store all field names for reference ( array of colAlias => tableAlias.col )

    private $qSelect = NULL;            // cache the constant part of the SELECT query (with the fields clause substitutable)
    private $qSelectFieldsClause = "";  // cache the default fields clause (caller can override)

    // variables
    private $_logFile = NULL;

    function SetLogFile( $filename )    { $this->_logFile = $filename; }


    function __construct( KeyFrameDB $kfdb, $kfrdef, $uid, $raKfrelParms = array() )
    /*******************************************************************************
     */
    {
        $this->kfdb   = $kfdb;
        $this->kfrdef = $kfrdef;    // copy because we modify it
        $this->uid    = $uid;

        if( @$raKfrelParms['logfile'] ) { $this->SetLogFile( $raKfrelParms['logfile'] ); }

        if( @$this->kfrdef['ver'] == 2 ) {
            /* Make sure every column has an alias.
             * Default col alias for base table is the column name.  Default col alias for other tables is tableAlias_col
             */
            $bFirst = true;
            foreach( $this->kfrdef['Tables'] as $a => &$t ) {
                if( empty($t['Type']) ) {
                    $t['Type'] = $bFirst ? "Base" : "Join";
                }
                $bFirst = false;
                if( $t['Type'] == 'Base' ) {
                    $this->baseTableAlias = $a;
                }

                /* Auto-fields are designated by the string "Auto" as the Fields value (instead of an array of fields definitions.
                 * The database is queried for the fields in the table.  This is only supported on database platforms where this feature is implemented.
                 * The basic KF fields are ignored here, but appended below.
                 */
                if( $t['Fields'] == "Auto" ) {
                    $t['Fields'] = array();

                    $ra = $kfdb->GetFields( $t['Table'] );
                    foreach( $ra as $fld => $raFld ) {
                        if( in_array( $fld, array("_key","_created","_created_by","_updated","_updated_by","_status") ) )  continue;

                        $t['Fields'][] = array( 'type'=>$raFld['kf_type'], 'col'=>$fld, 'default'=>$raFld['kf_default'] );
                    }
                }

                /* Add KF fields, unless KFCompat=="no".
                 * Non-KF tables defined here are joined, but not constrained. Conditions (e.g. WHERE A.foo=B.bar) should be
                 * specified at CreateRecordCursor etc.
                 */
                if( @$t['KFCompat'] != "no" ) {
                    $t['Fields'][] = array( 'type'=>'I', 'col'=>'_key',        'default'=>0 );
                    $t['Fields'][] = array( 'type'=>'S', 'col'=>'_updated',    'default'=>0 );
                    $t['Fields'][] = array( 'type'=>'I', 'col'=>'_updated_by', 'default'=>$this->uid );
                    $t['Fields'][] = array( 'type'=>'S', 'col'=>'_created',    'default'=>0 );
                    $t['Fields'][] = array( 'type'=>'I', 'col'=>'_created_by', 'default'=>$this->uid );
                    $t['Fields'][] = array( 'type'=>'I', 'col'=>'_status',     'default'=>0 );
                }

                $this->raTableN2A[$t['Table']] = $a;

                foreach( $t['Fields'] as &$f ) {
                    if( empty($f['alias']) ) {
                        $f['alias'] = ($t['Type'] == 'Base' ? $f['col'] : ($a."_".$f['col']));
                    }
                    $this->raColAlias[$f['alias']] = $a.".".$f['col'];
                }
                unset($f);
            }
            unset($t);  // always do this after foreach with reference, especially if you use $t again

            $this->qSelect = $this->makeQSelect();

            $this->baseTable = $this->kfrdef['Tables'][$this->baseTableAlias];  // copy this after all is changed because a reference is causing weird behaviour
        } else {

            /* Make sure every table has an alias.
             * Default table alias for base is Base.  Default table alias for other tables is a generated code.
             *
             * Make sure every column has an alias.
             * Default col alias for base table is the column name.  Default col alias for other tables is tableAlias_col
             */
            $bBaseFound = false;
            for( $i = 0; $i < count($this->kfrdef['Tables']); $i++ ) {
                $t = &$this->kfrdef['Tables'][$i];      // is there a way to do this with a foreach( kfrdef['Tables'] as &$t )?

                if( empty($t['Type']) ) {
                    $t['Type'] = $bBaseFound ? "" : "Base";
                }
                if( $t['Type'] == 'Base' ) {
                    $this->baseTable = &$this->kfrdef['Tables'][$i];
                //  $this->baseTable["Fields"][] = array( "type"=>"I", "col"=>"_key",        "default"=>0 );
                //  $this->baseTable["Fields"][] = array( "type"=>"S", "col"=>"_updated",    "default"=>0 );
                //  $this->baseTable["Fields"][] = array( "type"=>"I", "col"=>"_updated_by", "default"=>$this->uid );
                //  $this->baseTable["Fields"][] = array( "type"=>"S", "col"=>"_created",    "default"=>0 );
                //  $this->baseTable["Fields"][] = array( "type"=>"I", "col"=>"_created_by", "default"=>$this->uid );
                //  $this->baseTable["Fields"][] = array( "type"=>"I", "col"=>"_status",     "default"=>0 );
                    $bBaseFound = true;
                }

                /* Auto-fields are designated by the string "Auto" as the Fields value (instead of an array of fields definitions.
                 * The database is queried for the fields in the table.  This is only supported on database platforms where this feature is implemented.
                 * The basic KF fields are ignored here, but appended below.
                 */
                if( $this->kfrdef["Tables"][$i]["Fields"] == "Auto" ) {
                    $this->kfrdef["Tables"][$i]["Fields"] = array();

                    $ra = $kfdb->GetFields( $this->kfrdef["Tables"][$i]['Table'] );

                    foreach( $ra as $fld => $raFld ) {
                        if( in_array( $fld, array("_key","_created","_created_by","_updated","_updated_by","_status") ) )  continue;

                        $this->kfrdef["Tables"][$i]["Fields"][] = array( "type" => $raFld['kf_type'], "col" => $fld, "default" => $raFld['kf_default'] );
                    }
                }

                /* Add KF fields, unless KFCompat=="no".
                 * Non-KF tables defined here are joined, but not constrained. Conditions (e.g. WHERE A.foo=B.bar) should be
                 * specified at CreateRecordCursor etc.
                 */
                if( @$this->kfrdef["Tables"][$i]["KFCompat"] != "no" ) {
                    $this->kfrdef["Tables"][$i]["Fields"][] = array( "type"=>"I", "col"=>"_key",        "default"=>0 );
                    $this->kfrdef["Tables"][$i]["Fields"][] = array( "type"=>"S", "col"=>"_updated",    "default"=>0 );
                    $this->kfrdef["Tables"][$i]["Fields"][] = array( "type"=>"I", "col"=>"_updated_by", "default"=>$this->uid );
                    $this->kfrdef["Tables"][$i]["Fields"][] = array( "type"=>"S", "col"=>"_created",    "default"=>0 );
                    $this->kfrdef["Tables"][$i]["Fields"][] = array( "type"=>"I", "col"=>"_created_by", "default"=>$this->uid );
                    $this->kfrdef["Tables"][$i]["Fields"][] = array( "type"=>"I", "col"=>"_status",     "default"=>0 );
                }

                if( empty( $t['Alias'] ) ) {
                    $t['Alias'] = 'T'.($i+1);
                }
                $this->raTableN2A[$t['Table']] = $t['Alias'];

                for( $j = 0; $j < count($t['Fields']); ++$j ) {
                    $f = &$t['Fields'][$j];
                    if( empty($f['alias']) ) {
                        $f['alias'] = ($t['Type'] == 'Base' ? $f['col'] : ($t['Alias']."_".$f['col']));
                    }
                    $this->raColAlias[$f['alias']] = $t['Alias'].".".$f['col'];
                }
            }
            $this->baseTableAlias = $this->baseTable['Alias'];
        }
    }

    function IsBaseField( $q )
    /*************************
        return true if $q is a field name of the base table
     */
    {
        $ret = false;
        foreach( $this->baseTable['Fields'] as $f ) {
            if( $f['alias'] == $q ) {
                $ret = true;
                break;
            }

        }
        return( $ret );
    }

    function GetBaseTableName()
    {
        return( $this->baseTable['Table'] );
    }

    function GetListColAliases( $bBaseOnly = true )
    /**********************************************
     */
    {
        $ra = array();
        if( $bBaseOnly ) {
            foreach( $this->baseTable['Fields'] as $f ) {
                $ra[] = $f['alias'];
            }
        } else {
            foreach( $this->raColAlias as $a => $col ) {
                $ra[] = $a;
            }
        }
        return( $ra );
    }

    function GetRealColName( $alias )
    /********************************
        Given a column alias, return the column name that is used in a SELECT query.

        e.g. Alias=Users_realname, return Users.realname
     */
    {
        return( $this->raColAlias[$alias] );
    }

    function GetDBColName( $table, $col )
    /************************************
        Return the name that is used for the given column in SELECT queries.  (e.g. T2.foo)
        This can be used to generate condition expressions with tables that don't have user-defined alias names (e.g. T2.foo)
     */
    {
        return( $this->raTableN2A[$table].".$col" );  // the value of the array is the tableAlias
    }

    function GetDBTableAlias( $table )
    /*********************************
        Return the alias that is used for the given table.
        e.g. if no alias is specified for table blart and T1 is assigned, then GetDBTableAlias("blart") returns "T1"

        Note that this can be used (for example, by a base db access class) to determine whether a particular table exists in the relation.
        e.g. NULL means the table is not in the definition
     */
    {
        return( isset($this->raTableN2A[$table]) ? $this->raTableN2A[$table] : NULL );
    }

    function GetDBColAlias( $table, $col )
    /*************************************
        Return the alias that is used for the given column in SELECT queries.  (e.g. T2_foo)
        This can be used to retrieve fields from tables that don't have user-defined alias names
     */
    {
        $alias = "";

        if( @$this->kfrdef['ver'] == 2 ) {
            $t = $this->getTableDef( $table );
            foreach( $t['Fields'] as $f ) {
                if( $f['col'] == $col ) {
                    $alias = $f['alias'];
                    break;
                }
            }
        } else {
            foreach( $this->kfrdef['Tables'] as $t ) {
                if( $t['Table'] == $table ) {
                    foreach( $t['Fields'] as $f ) {
                        if( $f['col'] == $col ) {
                            $alias = $f['alias'];
                            break;
                        }
                    }
                }
            }
        }
        return( $alias );
    }

    private function getTableDef( $tableName )
    {
        $a = $this->raTableN2A( $tableName );
        return( $a ? $this->kfrdef['Tables'][$a] : null );
    }

    /*******************************************************************************************************************
     * Create KFRecords
     */


    function CreateRecord( $raFK = NULL )
    /************************************
        Return an empty KFRecord with default values.
        Only the base table values are populated.

        $raFK allows prepopulation of foreign keys and fk values.  It has the form (tablename=>key, tablename=>key)
     */
    {
        $kfr = $this->factory_KFRecord();
        if( is_array($raFK) )  $kfr->prot_setFKDefaults( $raFK );
        return( $kfr );
    }

    function CreateRecordFromRA( $ra, $bForceDefaults = false, $raFK = NULL )
    /************************************************************************
        Return a KFRecord with the data given by $ra (does not consider magic_quotes)
        Only the base table values are populated.
        $bForceDefaults should be false when the record already contains values and $ra is a subset
     */
    {
        /* In general, bForceDefaults should always be false because we always create a new record,
         * and default values are set in the KFRecord constructor. bForceDefaults just reinitiates the same
         * defaults if a field is not present in the input $ra.
         *
         * The bForceDefaults mechanism was used to reuse existing record objects, but we are now using a destroy-create
         * paradigm. This parm might be obsolete.
         */
        if( ($kfr = $this->CreateRecord( $raFK )) ) {
            $kfr->prot_getBaseValuesFromRA( $ra, $bForceDefaults, KFRECORD_DATASOURCE_RA_NONGPC );
        }
        return( $kfr );
    }

    function CreateRecordFromGPC( $gpc = NULL, $bForceDefaults = false, $raFK = NULL )
    /*********************************************************************************
        Return a KFRecord with the data given by $ra (uses magic_quotes)
        Only the base table values are populated.
        $bForceDefaults should be false when the record already contains values and $ra is a subset
     */
    {
        if( !$gpc ) $gpc = &$_REQUEST;

        if( ($kfr = $this->CreateRecord( $raFK )) ) {
            $kfr->prot_getBaseValuesFromRA( $gpc, $bForceDefaults, KFRECORD_DATASOURCE_RA_GPC );
        }
        return( $kfr );
    }

    function CreateRecordCursor( $cond = "", $parms = array() )
    /**********************************************************
        Return a KFRecordCursor to retrieve a record set

        parms: sSortCol  => name of column to sort (can be multiple columns comma-separated)
               bSortDown => true/false
               sGroupCol => GROUP BY sGroupCol
               iOffset   => offset of rows to return
               iLimit    => max rows to return (might help to optimize query on the server end)
               iStatus   => _status=iStatus  default 0

               raFieldsOverride => array of colalias=>fld to override the fields clause
     */
    {
        $kfrc = $this->factory_KFRecordCursor();

// seems like makeselect should be a method of kfr, and this should happen there
        if( isset($parms['raFieldsOverride']) )  $kfrc->raFieldsOverride = $parms['raFieldsOverride'];

        $q = $this->_makeSelect( $cond, $parms );
        $kfrc->_dbc = $this->kfdb->CursorOpen( $q );
        return( $kfrc->_dbc ? $kfrc : NULL );
    }

    function GetRecordFromDB( $cond = "", $raParms = array() )
    /*********************************************************
        Return a KFRecord from the database
     */
    {
        $ok = false;

        if( ($kfr = $this->CreateRecord()) ) {

            if( isset($parms['raFieldsOverride']) )  $kfr->raFieldsOverride = $parms['raFieldsOverride'];

            $q = $this->_makeSelect( $cond, $raParms );
            if( ($ra = $this->kfdb->QueryRA( $q )) ) {
                $kfr->prot_getAllDBValuesFromRA( $ra );
                $ok = true;
            }
        }
        return( $ok ? $kfr : NULL );
    }

    function GetRecordFromDBKey( $key )
    /**********************************
        Return a KFRecord from the database where the base row's _key is $key
     */
    {
        if( !$key ) return( null );
        return( $this->GetRecordFromDB( "{$this->baseTableAlias}._key=$key", array("iStatus"=>-1) ) );
    }

    function GetRecordSet( $sCond, $raParms = array() )
    /**************************************************
        Return an array of KFRecord for the given record set
     */
    {
        $ra = array();
        if( ($kfrc = $this->CreateRecordCursor( $sCond, $raParms ))) {
            while( $kfrc->CursorFetch() ) {
                $kfr = $kfrc->Copy();
                $ra[] = $kfr;
            }
        }
        return( $ra );
    }

    function GetRecordSetRA( $sCond, $raParms = array() )
    /****************************************************
        Return an array of array() for the given record set
     */
    {
        $ra = array();
        if( ($kfrc = $this->CreateRecordCursor( $sCond, $raParms ))) {
            while( $kfrc->CursorFetch() ) {
                $ra[] = $kfrc->ValuesRA();
            }
        }
        return( $ra );
    }

    // Override these to create custom KFRecord objects
    function factory_KFRecord()       { return( new KFRecord($this)); }
    function factory_KFRecordCursor() { return( new KFRecordCursor($this)); }

    function _Log( $s, $ok )
    /***********************
     */
    {
        if( !empty($this->_logFile) ) {
            if( $fp = fopen( $this->_logFile, "a" ) ) {
                if( !$ok )  $s .= " {{".$this->kfdb->GetErrMsg()."}}";

                fwrite( $fp, sprintf( "-- %d %s %s\n", time(), date("Y-m-d H:i:s"), $s ) );
                fclose( $fp );
            }
        }
    }


    /*******************************************************************************************************************
     * Private
     */
    private function makeQSelect()
    /*****************************
        Make the constant part of the SELECT statement. This is based only on kfrdef. Variable portions (filtering, sorting)
        are done in makeSelect

        The SELECT statement is composed of the following pieces:

          SELECT
             [fieldsClause]
         FROM
             [tablesClause]
         WHERE
             [condClause]     the fixed part defined by kfreldef
             AND
             [cond]           the variable part defined by the caller
         [other clauses]      like ORDER BY, GROUP BY, etc

         The statement up to and including [condClause] is defined by kfreldef, and is cached for multiple uses.
         The part of the statement after [condClause] can vary per query.

         [fieldsClause]   contains all column names. Columns of base tables are returned with no prefix, columns of
                          other tables are returned as tableAlias_columnName.

         [tablesClause]   is table1 AS tableAlias1 [jointype] {ON ([joincond])} table2 AS tableAlias2 ...
                          in the order that the tables are declared

                          When Type=="LeftJoin", [jointype] is LEFT JOIN, and [joincond] is JoinOn
                          When Type=="Join", [jointype] is the natural join keyword JOIN
                              If JoinOn is defined, [joincond] is JoinOn.
                              Else if an fk_ relationship exists, [joincond] uses fk_
                              Else the join has no ON clause.
                          This means the fk_ feature is only enabled if neither JoinOn nor LeftJoinOn are defined.
                          That's because the fk_ feature is normally useful for inner joins, and the JoinOn parameter
                          exists to specify non-fk_ relationships.

         [condClause]     contains all conditions defined by the kfreldef


         Table types:
             Base     : should be the first table, default if not specified
             Join     : natural join, default for non-first table
             LeftJoin : left join with the previous table
     */
    {
        $raFieldsClause = array();
        $sTablesClause = "";
        $raCondClause = array();


        $raTables = array();

        /* Pre-process each table by recording the alias, type, and whether there is a fk_ field that matches
         * another table name (modulo db prefix)
         */
        foreach( $this->kfrdef['Tables'] as $a => &$t ) {
            if( !@$t['JoinOn'] ) $t['JoinOn'] = "";
            $t['fk'] = array();
        }
        unset($t);  // always do this after foreach with reference, especially if you use $t again

        /* Find any fk_ fields that match another table in the kfrdef
         *
         * The join is constructed left to right in the order of tables specified. That means it's awkward for the first table
         * to have a fk_ to a later table, because the ON clause has to come after the second table. Much easier if the first table has no fk_
         *
         *      A JOIN B ON (A.fk_B=B._key)                             not too hard to implement if you store the dependency from A
         *      A JOIN B ON (A.fk_B=B._key) JOIN C ON (C.fk_B=B._key)   a lot harder to implement because B and C have to do different things
         *      A JOIN B ON (B.fk_A=A._key) JOIN C ON (C.fk_A=A._key)   easier to implement because each fk_ is in an ON clause with its own table
         *      A JOIN B ON (B.fk_A=A._key) JOIN C ON (C.fk_A=B._key)   this is also not a problem
         */
        foreach( $this->kfrdef['Tables'] as $a => $t ) {
            foreach( $t['Fields'] as $f ) {
                if( substr($f['col'],0,3) != "fk_" || !($foreignTable = substr($f['col'],3)) )  continue;

                /* This is a foreign key to another table. See if that table is in the kfrdef.
                 * Table names can have the db name appended e.g. db1.table, so do the compare after any '.'
                 */
                foreach( $this->raTableN2A as $t2 => $a2 ) {
                    if( ($i = strpos( $t2, '.' )) !== false ) {
                        $t2 = substr( $t2, $i + 1 );
                    }
                    if( $t2 == $foreignTable ) {
                        // $a.$f is a fk_ to $a2._key
                        $this->kfrdef['Tables'][$a]['fk'][] = array( $f['col'], $a2 );
                    }
                }
            }
        }

        // To make the JoinOn for an fk_, the dependent table cannot be the first in the list -- in "A JOIN B" we can't put ON immediately after A
        // If this happens, put a condition in the ConditionClause instead.

        // Also, you can't do A JOIN B ON (B.fk_C=C._key) JOIN C  -- forward reference to C
        // You have to do     A JOIN C JOIN B ON (B.fk_C=C._key)  -- the order of table definition matters
        $bFirst = true;
        foreach( $this->kfrdef['Tables'] as $a => &$t ) {
            if( count($t['fk']) && !$t['JoinOn'] ) {
                $joinOn = "";
                foreach( $t['fk'] as $ra ) {
                    $fkFld = $ra[0];
                    $aTarget = $ra[1];
                    if( $joinOn ) $joinOn .= " AND ";
                    $joinOn .= "$a.$fkFld=$aTarget._key";
                }
                if( !$bFirst ) {
                    $t['JoinOn'] = $joinOn;
                } else {
                    $raCondClause[] = $joinOn;
                }
            }
            $bFirst = false;
        }
        unset($t);

        /* Step through the tables and build the Fields, Table, and Condition clauses
         */
        $raFields = array();
        foreach( $this->kfrdef['Tables'] as $a => $t ) {
            /* Make the Fields clause
             */
            foreach( $t['Fields'] as $f ) {
                $raFieldsClause[] = "$a.{$f['col']} as {$f['alias']}";
            }

            /* Make the Tables clause
             */
            $sTA = "{$t['Table']} AS $a";
            if( !$sTablesClause ) {
                // the first table doesn't have a join
                $sTablesClause .= $sTA;
            } else if( $t['Type'] == "LeftJoin" ) {
                $sTablesClause .= " LEFT JOIN $sTA ON ({$t['JoinOn']})";
            } else {
                $sTablesClause .= " JOIN $sTA".($t['JoinOn'] ? " ON ({$t['JoinOn']})" : "");
            }

            /* Make the Condition clause
             */
            if( isset($t['sCond']) )  $raCondClause[] = $t['sCond'];
        }

        // cache the fields clause, to be substituted later if the caller doesn't override
        $this->qSelectFieldsClause = implode( ',', $raFieldsClause );

        // the kfreldef can define a condition that filters the results or constrains a join
        if( isset($this->kfrdef['Condition']) )  $raCondClause[] = $this->kfrdef['Condition'];

        $q = "SELECT [fields clause]\n"
                        ."FROM $sTablesClause\n"
                        ."WHERE ".(count($raCondClause) ? implode(' AND ', $raCondClause)
                                                         : "1=1");  // do this so additional conds can be added below
        return( $q );
    }


    private function makeSelect2( $cond = "", $parms = array() )
    /***********************************************************
     */
    {
        $sGroupCol = @$parms['sGroupCol'] ?? "";
        $sSortCol  = @$parms['sSortCol'] ?? "";
        $bSortDown = intval(@$parms['bSortDown']);
        $iOffset   = intval(@$parms['iOffset']);
        $iLimit    = intval(@$parms['iLimit']);
        $iStatus   = intval(@$parms['iStatus']);

        /* $this->qSelect is completely defined by kfrel.
         * Now customize the query by appending conditions and call-specific clauses e.g. ORDER, GROUP
         */
        $q = $this->qSelect;

        /* Make the SELECT field clause.
         *
         * raFieldsOverride takes precedence over all computed select fields.
         *     array( alias=>fld, ... ) generates {fld as alias},...
         *
         * If raGroup is defined, use it to create the select fields.
         *     array( alias=>fld, ... ) uses {fld} as grouping cols and {fld as alias} as select fields
         *
         *     raGroupAlso => array( alias=>fld, .... ) makes {ANY_VALUE(fld) as alias} to retrieve cols not dependent on the group columns
         *
         * Otherwise use the default select fields computed from the kfrel.
         */
        $sFieldsClause = "";
        if( isset($parms['raFieldsOverride']) ) {
            foreach( $parms['raFieldsOverride'] as $alias=>$fld ) {
                $sFieldsClause .= ($sFieldsClause ? "," : "")
                                 ."$fld as $alias";
            }
        } else if( isset($parms['raGroup']) ) {
            foreach( $parms['raGroup'] as $alias=>$fld ) {
                $sFieldsClause .= ($sFieldsClause ? "," : "")
                                 ."$fld as $alias";
                $sGroupCol     .= ($sGroupCol ? "," : "").$fld;
            }
// MariaDB doesn't have ANY_VALUE()
            if( isset($parms['raGroupAnyValue']) ) {
                foreach( $parms['raGroupAnyValue'] as $alias=>$fld ) {
                    $sFieldsClause .= ($sFieldsClause ? "," : "")
                                     ."ANY_VALUE($fld) as $alias";
                }
            }
        } else {
            $sFieldsClause = $this->qSelectFieldsClause;
        }

        $q = str_replace( '[fields clause]', $sFieldsClause, $q );


        if( $cond ) $q .= " AND ($cond)";

        foreach( $this->kfrdef['Tables'] as $a => $t ) {
            if( $iStatus != -1 )  $q .= " AND ($a._status='$iStatus')";
        }

        if( $sGroupCol )  $q .= " GROUP BY $sGroupCol";
        if( $sSortCol )   $q .= " ORDER BY $sSortCol". ($bSortDown ? " DESC" : " ASC");

        if( $iLimit > 0 || $iOffset > 0 ) {
            /* The correct syntax is LIMIT [offset,] limit
             * For compatibility with PostgreSQL, "LIMIT limit OFFSET offset" is supported but "OFFSET offset" is illegal (when LIMIT is infinite).
             * So the only way to make LIMIT infinite in MySQL is to set it to a very big number.
             * The first row is OFFSET 0
             */
            if( $iLimit < 1 )  $iLimit = "4294967295";  // 2^32-1 - make this a string so php doesn't do something weird converting to signed int or something
            $q .= " LIMIT ".($iOffset>0 ? "$iOffset," : "").$iLimit;
        }

        return( $q );
    }

    private function _makeSelect( $cond = "", $parms = array() )
    /***********************************************************
        $status == -1:  do not write a _status condition clause
     */
    {
        if( ($ver = @$this->kfrdef['ver']) && $ver == 2 ) {
            return( $this->makeSelect2( $cond, $parms ) );
        }


        $sGroupCol = @$parms['sGroupCol'] ?? "";
        $sSortCol  = @$parms['sSortCol'] ?? "";
        $bSortDown = intval(@$parms['bSortDown']);
        $iOffset   = intval(@$parms['iOffset']);
        $iLimit    = intval(@$parms['iLimit']);
        $iStatus   = intval(@$parms['iStatus']);

        if( empty($this->qSelect) ) {
            /* Make the constant part of the SELECT once and cache it
             */
            $raSelFields = array();
            $sSelTables = "";
            $raSelCond = array();

            /* LEFT JOINS:
             *
             * if a LEFT JOIN is on a fk_ column, the fk_ joining mechanism would compose ... a LEFT JOIN b ON (a.x=b.y) WHERE (a.x=b.y) ...
             * which filters out the unmatched rows. Normally, you only want the fk_ feature in natural joins.
             *
             * The fk_ feature is enabled on any pair of Tables that do not include a table of Type = LEFT JOIN
             *
             * Supported:
             *     any number of natural joins  (table1 A, table2 B, table3 C, ...)
             *     any number of left joins     (table1 A LEFT JOIN table2 B ON (A.x=B.y) LEFT JOIN table3 C on (A.z=C.w) ...)
             *     natural joins followed by left joins (table1 A, table2 B LEFT JOIN table3 C on (B.x=C.y) LEFT JOIN table4 D on (C.z=D.w)
             *
             * [Not really sure what happens when you mix natural joins with left joins, then natural joins again]
             */
            //$bLeftJoinExists = false;
            $raNaturalJoinAliases = array();   // use Alias because it's easier to match below where we disambiguate db1.foo vs foo
            $raLeftJoinAliases = array();
            foreach( $this->kfrdef['Tables'] as $t ) {
                if( $t['Type'] == "LEFT JOIN" ) {
                    //$bLeftJoinExists = true;
                    $raLeftJoinAliases[] = $t['Alias'];
                } else {
                    $raNaturalJoinAliases[] = $t['Alias'];
                }
            }

            // compose SELECT field list, table list, condition list
            foreach( $this->kfrdef['Tables'] as $t ) {

                if( $t['Type'] == "LEFT JOIN" ) {
                    // The LEFT JOIN table cannot be the first one in the definition
                    $sSelTables .= " LEFT JOIN ${t['Table']} ${t['Alias']} ON (${t['LeftJoinOn']})";
                } else {
                    // Natural join
                    if( !empty($sSelTables) )  $sSelTables .= ", ";
                    $sSelTables .= $t['Table'].' '.$t['Alias'];
                }

                foreach( $t['Fields'] as $f ) {
                    $sCol = $f['col'];
                    if( $t['Type'] != "LEFT JOIN" && substr($sCol,0,3) == "fk_" && strlen($sCol) > 3 ) {
                        /* This is a foreign key to another table.
                         * If that table is in the kfrdef, AND NEITHER THIS NOR THAT TABLE ARE DECLARED as LEFT JOIN,
                         * compose a natural join condition.
                         * Foreign key fields are included in the SelFields list in case a client wants to
                         * see them; in particular they are necessary in the Base table for updating and copying.
                         *
                         * Table names can have the db name appended e.g. db1.table, so do the compare after any '.'
                         */
                        $foreignTable = substr($sCol,3);
                        $matchAlias = NULL;
                        foreach( $this->raTableN2A as $k => $v ) {
                            if( ($i = strpos( $k, '.' )) !== false ) {
                                $k = substr( $k, $i + 1 );
                            }
                            if( $k == $foreignTable ) {
                                $matchAlias = $v;
                                break;
                            }
                        }
                        if( $matchAlias ) {
                            if( in_array( $matchAlias, $raNaturalJoinAliases ) ) {
                            //if( !$bLeftJoinExists ) {
                                // Found the _key corresponding to the fk_, and neither are in a LEFT JOIN table
                                $raSelCond[] = "(".$t['Alias'].".$sCol=$matchAlias._key)";
                            }
// this only filters _status for tables containing the fk_*. If the base is the only table with fk_foo, then foo._status
// is never filtered.  Shouldn't we just be forcing this on all tables that get joined?
// put SetDebug(2) on mode==G in gcgcadmin to see this
                            if( $iStatus != -1 ) $raSelCond[] = "(".$t['Alias']."._status=$iStatus)";
                        }
                    }
                    $raSelFields[] = $t['Alias'].".$sCol as ".$f['alias'];
                }
            }

            // the kfreldef can define a condition that filters the results or constrains a join
            if( isset($this->kfrdef['Condition']) )  $raSelCond[] = $this->kfrdef['Condition'];

            if( !count($raSelCond) )  $raSelCond[] = "1=1";

            $this->qSelect = "SELECT ".implode(',', $raSelFields)." FROM $sSelTables WHERE ".implode(' AND ', $raSelCond);
        }

        /* Compose the specific SELECT query by appending parm-defined conditions to the constant part of the statement
         */
        $q = $this->qSelect;
        if( $iStatus != -1 ) {
            $q .= " AND {$this->baseTable['Alias']}._status=$iStatus";
        }

        if( !empty($cond) ) $q .= " AND ($cond)";
        if( !empty($sGroupCol) )  $q .= " GROUP BY $sGroupCol";
        if( !empty($sSortCol) )  $q .= " ORDER BY $sSortCol". ($bSortDown ? " DESC" : "");

        /* The correct syntax is LIMIT [offset,] limit
         * For compatibility with PostgreSQL, "LIMIT limit OFFSET offset" is supported but "OFFSET offset" is illegal (when LIMIT is infinite).
         * So the only way to make LIMIT infinite in MySQL is to set it to a very big number.
         * The first row is OFFSET 0
         */
//        if( $iLimit  > 0 )  $q .= " LIMIT $iLimit";
//        if( $iOffset > 0 )  $q .= " OFFSET $iOffset";   // OFFSET=0 is the first row

        if( $iLimit > 0 || $iOffset > 0 ) {
            if( $iLimit < 1 )  $iLimit = "4294967295";  // 2^32-1 - make this a string so php doesn't do something weird converting to signed int or something
            $q .= " LIMIT ".($iOffset>0 ? "$iOffset," : "").$iLimit;
        }

        return( $q );
    }
}



class KFRecord {
/***************
    Contains the data of a single record, driven by KeyFrameRelation.
    This is created by KeyFrameRelation, and should not normally be constructed independently by user code.
 */
    var $kfrel;             // ref to the KeyFrameRelation that governs this record

    // the Record
    var $_key;
    var $_values;
    var $_valPrepend;       // values for S+ go here before being prepended to _values at db update
    var $_dbValSnap;        // a snapshot of the _values most recently retrieved from the db.  For change detection.

    private $keyForce = 0;

// should be private if makeSelect is in KFRecord
    public $raFieldsOverride = null;   // array of colalias=>fld if the caller defines custom fields clause

    /* There are two namespaces for columns:
            col names   = the db column names, not necessarily unique in multi-table relations
            alias names = unique names for each column in the relation

       By default, these are identical for the Base table, but alias can be defined for Base columns.
       The convention is that _values, _valPrepend, _dbValSnap all use 'alias' names as their keys.
     */


    function __construct( KeyFrameRelation $kfrel )    // KFRecord and KFRecordCursor constructors have to do the same thing
    /**********************************************
     */
    {
        $this->kfrel = $kfrel;
        $this->Clear();
    }

    function Clear()
    /***************
        Clear the values and set defaults
     */
    {
        $this->_key = 0;
        $this->_values = array();
        $this->_valPrepend = array();
        $this->_dbValSnap = array();
        $this->keyForce = 0;

        foreach( $this->kfrel->baseTable["Fields"] as $k ) {
            $this->_setDefault($k);
        }
    }

    function Copy()
    /**************
        Return a KFRecord that contains the same data as this one
     */
     {
         $kfr = $this->kfrel->factory_KFRecord();   // in case a client is overriding the constructor
         $kfr->kfrel = $this->kfrel;
         $kfr->_key = $this->_key;
         $kfr->_values = $this->_values;
         $kfr->_valPrepend = $this->_valPrepend;
         $kfr->_dbValSnap = $this->_dbValSnap;
         $kfr->keyForce = 0;  // not copying this
         return( $kfr );
     }

    function Value( $k )
    /*******************
        $k is the alias name of a column
     */
    {
        $v = null;
        if( array_key_exists( $k, $this->_values ) ) {
            $v = $this->_values[$k];
        }
        return( $v );
    }

    function ValueEnt( $k )     { return( SEEDStd_HSC($this->Value($k)) ); }
    function ValueXlat( $k )    { return( $this->Value( $k ) ); }
    function ValueXlatEnt( $k ) { return( $this->ValueEnt( $k ) ); }
    function ValueDB( $k )      { return( addslashes($this->Value($k)) ); }
    function ValuesRA()         { return( $this->_values ); }

    function Key()              { return( $this->_key ); }
    function IsEmpty( $k )      { $v = $this->Value($k); return( empty($v) ); } // because empty doesn't work on methods
    function SetKey( $i )       { $this->_key = $i;      $this->SetValue('_key', $i ); }
    function SetValue( $k, $v )
    {
        $bFound = false;
        foreach( $this->kfrel->baseTable['Fields'] as $f ) {
            if( $f['alias'] == $k ) {
                if( $f['type'] == "S+" ) {
                    $this->_valPrepend[$k] = $v;
                    $bFound = true;
                }
                break;
            }
        }
        if( !$bFound ) { $this->_values[$k] = $v; }
    }
    // simulate the function of an S+ type
    function SetValuePrepend( $k, $v ) { $this->_values[$k] = $v . $this->_values[$k]; }
    function SetValueAppend( $k, $v )  { $this->_values[$k] = $this->_values[$k] . $v; }

    function SmartValue( $k, $raValues, $v = NULL )
    /* Ensure that the named value is in the given array. Set it to the first one if not.
     */
    {
        if( $v !== NULL ) $this->SetValue( $k, $v );
        if( !in_array( $this->Value($k), $raValues ) )  $this->SetValue( $k, $raValues[0] );
    }

    /* functions to manage lists of urlparms
     */
    function UrlParmGet( $fld, $k )
    /******************************
        Get the value from an urlparm
     */
    {
        $ra = $this->UrlParmGetRA( $fld );
        return( @$ra[$k] ?? "" );
    }

    function UrlParmSet( $fld, $k, $v )
    /**********************************
        Set the given value into an urlparm
     */
    {
        $ra = $this->UrlParmGetRA( $fld );
        $ra[$k] = $v;
        $this->UrlParmSetRA( $fld, $ra );
    }

    function UrlParmRemove( $fld, $k )
    /*********************************
        Remove the given parm from an urlparm
     */
    {
        $ra = $this->UrlParmGetRA( $fld );
        if( isset($ra[$k]) )  unset($ra[$k]);
        $this->UrlParmSetRA( $fld, $ra );
    }

    function UrlParmGetRA( $fld )
    /****************************
        Return an array containing all values in an urlparm
     */
    {
        return( SEEDStd_ParmsURL2RA( $this->value($fld) ) );
    }

    function UrlParmSetRA( $fld, $raParms )
    /**************************************
        Store the given array as an urlparm
     */
     {
         $s = SEEDStd_ParmsRA2URL( $raParms );
         $this->SetValue( $fld, $s );
     }

    function Expand( $sTemplate, $bEnt = true )
    /******************************************
        Return template string with all [[value]] replaced
     */
    {
        for(;;) {
            $s1 = strpos( $sTemplate, "[[" );
            $s2 = strpos( $sTemplate, "]]" );
            if( $s1 === false || $s2 === false )  break;
            $k = substr( $sTemplate, $s1 + 2, $s2 - $s1 - 2 );
            if( empty($k) ) break;

            $sTemplate = substr( $sTemplate, 0, $s1 )
                        .($bEnt ? $this->valueEnt($k) : $this->value($k))
                        .substr( $sTemplate, $s2+2 );
        }
        return( $sTemplate );
    }

    function ExpandIfNotEmpty( $fld, $sTemplate, $bEnt = true )
    /**********************************************************
        Return template string with [[]] replaced by the value of the field, if it is not empty.
        This lets you do this:  ( !$kfr->IsEmpty('foo') ? ($kfr->value('foo')." items<BR/>") : "" )
                    with this:  ExpandTemplateIfNotEmpty( 'foo', "[[]] items<BR/>" )
     */
    {
        if( !$this->IsEmpty($fld) )  return( str_replace( "[[]]", ($bEnt ? $this->valueEnt($fld) : $this->value($fld)), $sTemplate ) );
    }

    // find values in the given array that match base field names - alters only those that match - GPC handles slashes
    function UpdateBaseValuesFromRA( $p_ra )    { $this->prot_getBaseValuesFromRA( $p_ra, false, KFRECORD_DATASOURCE_RA_NONGPC ); }
    function UpdateBaseValuesFromGPC( $p_ra )   { $this->prot_getBaseValuesFromRA( $p_ra, false, KFRECORD_DATASOURCE_RA_GPC ); }
    function ForceAllBaseValuesFromRA( $p_ra )  { $this->prot_getBaseValuesFromRA( $p_ra, true,  KFRECORD_DATASOURCE_RA_NONGPC ); }
    function ForceAllBaseValuesFromGPC( $p_ra ) { $this->prot_getBaseValuesFromRA( $p_ra, true,  KFRECORD_DATASOURCE_RA_GPC ); }


    function KeyForce( $kForce )
    /***************************
        Force the _key to a particular (different) value, only if that _key is not already being used. The change is made on PutDBRow().
     */
    {
        $this->keyForce = 0;

        // if forcing to current value do nothing but return success
        if( $kForce == $this->_key ) return( true );

        if( $kForce && !$this->kfrel->kfdb->Query1( "SELECT _key FROM {$this->kfrel->baseTable['Table']} WHERE _key='$kForce'" ) ) {
            $this->keyForce = $kForce;
        }

        return( $this->keyForce != 0 );
    }


    function PutDBRow( $bUpdateTS = false )
    /**************************************
        Insert/Update the row as needed.  The choice is based on $this->key==0.

        This does NOT automatically update $this->_values('_created') and ('_updated'), since that requires an extra fetch.
        $bUpdateTS==true causes this fetch
     */
    {
        $ok = false;

        /* Handle prepend types (S+)
Why is this done via _valPrepend? Can't we just prepend to _values using a method? This way it makes a sync problem: SetValue(B); GetValue() != B.
         */
        foreach( $this->kfrel->baseTable['Fields'] as $f ) {
            if( $f['type'] == 'S+' ) {
                if( empty($this->_valPrepend[$f['alias']]) ) continue;

                if( empty($this->_values[$f['alias']]) ) {
                    $this->_values[$f['alias']] = $this->_valPrepend[$f['alias']];
                } else {
                    $this->_values[$f['alias']] = $this->_valPrepend[$f['alias']]."\n".$this->_values[$f['alias']];
                }
                $this->_valPrepend[$f['alias']] = "";
            }
        }


        if( $this->_key ) {
            /* UPDATE all user fields, plus _status, _updated and _updated_by.
             * _key doesn't change unless $this->keyForce
             * _created* never change
             */
            $bDo = false;
            $bSnap = (array_key_exists( "_key", $this->_dbValSnap ) && ($this->_dbValSnap["_key"] == $this->_key) );
            $bKeyForce = $this->keyForce && $this->keyForce != $this->_key;

            $s = "UPDATE {$this->kfrel->baseTable['Table']} SET _updated=NOW(),_updated_by={$this->kfrel->uid}";
            $sClause = "";
            if( $bKeyForce ) {
                $sClause .= ",_key='{$this->keyForce}'";
                $bDo = true;
            }
            foreach( $this->kfrel->baseTable['Fields'] as $f ) {
                if( $f['col'] != '_key' &&
                    $f['col'] != '_created' &&
                    $f['col'] != '_created_by' )
                {
                    /* Use the dbVal snapshot to inhibit update of unchanged fields.  Though most db engines do this
                     * anyway, this makes kfr log files much more readable.
                     */
                    if( $bSnap && array_key_exists( $f['alias'], $this->_dbValSnap ) &&
                          ($this->_values[$f['alias']] == $this->_dbValSnap[$f['alias']]) ) {
                        continue;
                    }
                    $sClause .= ",".$f['col']."=".$this->_putFmtVal($this->_values[$f['alias']], $f['type'] );
                    $bDo = true;
                }
            }
            if( $bDo ) {
                $s .= $sClause." WHERE _key={$this->_key}";
                $ok = $this->kfrel->kfdb->Execute( $s );

                // Log U table _key uid: update clause {{err}}
                // Do this before SetKey(keyForce) so it shows the old key
                $this->kfrel->_Log( "U {$this->kfrel->baseTable['Table']} {$this->_key} {$this->kfrel->uid}: $sClause", $ok );

                if( $ok && $bKeyForce ) {
                    $this->SetKey( $this->keyForce );
                }
            } else {
                $ok = true;
            }
        } else {
            /* INSERT all client fields, plus kfr fields.  Set _created=_updated=NOW().  Set _key to a new autoincrement.
             * Other fields default to the correct initial values.
             */
            $sk = "";
            $sv = "";
            foreach( $this->kfrel->baseTable['Fields'] as $f ) {
                if( $f['col'] != '_key' &&
                    $f['col'] != '_created' &&
                    $f['col'] != '_created_by' &&
                    $f['col'] != '_updated' &&
                    $f['col'] != '_updated_by' )
                {
                    $sk .= ",".$f['col'];
                    $sv .= ",".$this->_putFmtVal( $this->_values[$f['alias']], $f['type'] );
                }
            }

            $sKey = $this->keyForce ? "'{$this->keyForce}'" : "NULL";

            $s = "INSERT INTO {$this->kfrel->baseTable['Table']} (_key,_created,_updated,_created_by,_updated_by $sk) ";
            $s .= "VALUES ($sKey,NOW(),NOW(),{$this->kfrel->uid},{$this->kfrel->uid} $sv)";

            /* In MySQL, this depends on _key being the first AUTOINCREMENT column.
             */
            if( ($r = $this->kfrel->kfdb->InsertAutoInc( $s )) ) {
                $this->SetKey( $r );
                $ok = true;
            }
            // Log I table _key uid: insert clauses {{err}}
            $this->kfrel->_Log( "I {$this->kfrel->baseTable['Table']} {$sKey}->{$r} {$this->kfrel->uid}: ($sk) ($sv)", $ok );
        }
        if( $ok ) {
            if( $bUpdateTS ) {
                if( ($ra = $this->kfrel->kfdb->QueryRA( "SELECT _created,_updated FROM {$this->kfrel->baseTable['Table']} WHERE _key={$this->_key}" )) ) {
                    $this->_values['_created'] = $ra['_created'];
                    $this->_values['_updated'] = $ra['_updated'];
                }
            }
            $this->_snapValues();   // reset the "clean" record state, since the db now matches the KFRecord
        }
        return( $ok );
    }

    function StatusChange( $status ) { $this->StatusSet( $status ); }       // deprecate
    function StatusSet( $status )
    /****************************
        Allowed values of $status:
            KFRECORD_STATUS_NORMAL
            KFRECORD_STATUS_DELETED
            KFRECORD_STATUS_HIDDEN
            "Normal"
            "Deleted"
            "Hidden"
            any other integer that means something to you
     */
    {
        switch( $status ) {
            case "Normal":  $status = KFRECORD_STATUS_NORMAL;  break;
            case "Deleted": $status = KFRECORD_STATUS_DELETED; break;
            case "Hidden":  $status = KFRECORD_STATUS_HIDDEN;  break;
        }
        $this->SetValue( "_status", $status );
    }

    function StatusGet()
    {
        return( $this->Value('_status') );
    }

    function DeleteRow()
    /*******************
        Not the same as StatusChange.  This actually deletes the current row permanently.
     */
    {
        $ok = false;

        if( $this->_key ) {
            $s = "DELETE FROM {$this->kfrel->baseTable['Table']} WHERE _key={$this->_key}";

            $ok = $this->kfrel->kfdb->Execute( $s );
            // Log D table _key uid: {{err}}
            $this->kfrel->_Log( "D {$this->kfrel->baseTable['Table']} {$this->_key} {$this->kfrel->uid}: ", $ok );
        }
        return( $ok );
    }

    /*******************************************************************************************************************
     * Protected methods used by KeyFrameRelation
     */

    function prot_setFKDefaults( $raFK = array() )
    /*********************************************
        With no args, this is the same as Clear()
        Args of "table"=>"fk key" cause those foreign keys to be set in the relation, and foreign data to be
        retrieved for non-base tables.  This is especially useful for creating an "empty" row in a form that
        displays read-only data from a parent row.
     */
    {
        $this->Clear();

        // Leave _dbValSnap cleared because it's only used in updates to the base row, which is not set by this method.


        // N.B. only implemented for one level of indirection from the base table.
        //      Traversal or really smart joins required to fetch data for a second-level row e.g. grandparent
        //      We are assuming that the fk_* column name is not aliased (i.e. Field['col']==Field['alias']=='fk_'.$tableName
        foreach( $raFK as $tableName => $fkKey ) {
            $this->_values['fk_'.$tableName] = $fkKey;

            if( @$this->kfrdef['ver'] == 2 ) {
                if( ($a = $this->kfrel->raTableN2A[$tableName]) && ($t = $this->kfrdef['Tables'][$a]) ) {
                    $raSelFields = array();
                    foreach( $t['Fields'] as $f ) {
                        $raSelFields[] = "$a.{$f['col']} as {$f['alias']}";
                    }
                    $ra = $this->kfrel->kfdb->QueryRA( "SELECT ".implode(",",$raSelFields)." FROM {$t['Table']} $a"
                                                      ." WHERE $a._key='$fkKey'" );
                    // array_merge is easier, but KFDB returns duplicate entries in $ra[0],$ra[1],...
                    foreach( $t['Fields'] as $f ) {
                        $this->_values[$f['alias']] = $ra[$f['alias']];
                    }
                }
            } else {
                foreach( $this->kfrel->kfrdef['Tables'] as $t ) {
                    if( $t['Table'] != $tableName )  continue;
                    $raSelFields = array();
                    foreach( $t['Fields'] as $f ) {
                        $raSelFields[] = $t['Alias'].".".$f['col']." as ".$f['alias'];
                    }
                    $ra = $this->kfrel->kfdb->QueryRA( "SELECT ".implode(",",$raSelFields)." FROM ".$t['Table']." ".$t['Alias'].
                                                            " WHERE ".$t['Alias']."._key=$fkKey" );
                    // array_merge is easier, but KFDB returns duplicate entries in $ra[0],$ra[1],...
                    foreach( $t['Fields'] as $f ) {
                        $this->_values[$f['alias']] = $ra[$f['alias']];
                    }
                }
            }
        }
    }

    function prot_getBaseValuesFromRA( $p_ra, $bForceDefaults, $modeDS )
    /*******************************************************************
        Load base field values found in $ra.
        $bForceDefaults should be false when the record already contains values and $ra is a subset
     */
    {
        if( ($modeDS == KFRECORD_DATASOURCE_RA_GPC) && get_magic_quotes_gpc() ) {
            foreach( $this->kfrel->baseTable['Fields'] as $f ) {
                if( isset( $p_ra[$f['alias']] ) ) {
                    $ra[$f['alias']] = stripslashes( $p_ra[$f['alias']] );
                }
            }
        } else {
            $ra = $p_ra;
        }

        if( isset($ra['_key']) ) {          // _key won't necessarily be in ra if these are values posted from a form
            $this->_key = intval($ra['_key']);
        } else if( $bForceDefaults ) {
            $this->_key = 0;
        }
        foreach( $this->kfrel->baseTable['Fields'] as $f ) {
            $this->_getValFromRA( $f, $ra, $bForceDefaults, $modeDS );
        }
    }

    function prot_getAllDBValuesFromRA( $ra )
    /****************************************
        After reading a DB row, put all values in the record
     */
    {
        if( $this->raFieldsOverride ) {
            // Caller has defined a set of fields to return, overriding the defaults.
            // It is a bad idea to try to rewrite this kfr unless it contains everything needed, like a _key.
            foreach( $this->raFieldsOverride as $alias => $fld ) {
                $this->_values[$alias] = @$ra[$alias] ?? "";
            }
        } else {
            $this->prot_getBaseValuesFromRA( $ra, true, KFRECORD_DATASOURCE_DB );    // get base values, set defaults(why?), not gpc
            $this->_getFKValuesFromArray( $ra );                    // get all fk values
        }
        $this->_snapValues();
    }


    /*******************************************************************************************************************
     * Private
     */


    function _snapValues()
    /*********************
        After reading a DB row, set the record in a "clean" state to prevent unnecessary UPDATE in PutDBRow
     */
    {
        $this->_dbValSnap = $this->_values;
    }

    function _getFKValuesFromArray( $ra, $modeDS = KFRECORD_DATASOURCE_DB, $bForceDefaults = true )
    /***********************************************************************************************
     */
    {
        if( @$this->kfrdef['ver'] == 2 ) {
            foreach( $this->kfrel->kfrdef['Tables'] as $a => $t ) {
                if( $t['Type'] == 'Base' )  continue;

                foreach( $t['Fields'] as $f ) {
                    $this->_getValFromRA( $f, $ra, $bForceDefaults, $modeDS );
                }
            }
        } else {
            foreach( $this->kfrel->kfrdef['Tables'] as $t ) {
                if( $t['Type'] == 'Base' )  continue;

                foreach( $t['Fields'] as $f ) {
                    $this->_getValFromRA( $f, $ra, $bForceDefaults, $modeDS );
                }
            }
        }
    }

    function _getValFromRA( $f, $ra, $bForceDefaults, $modeDS )
    /**********************************************************
     */
    {
        if( isset( $ra[$f['alias']] ) ) {
            switch( $f['type'] ) {
                case 'S+':
                    if( $modeDS == KFRECORD_DATASOURCE_DB ) {
                        $this->_values[$f['alias']] = $ra[$f['alias']];
                    } else {
                        $this->_valPrepend[$f['alias']] = $ra[$f['alias']];
                    }
                    break;

                case 'S':   $this->_values[$f['alias']] = $ra[$f['alias']];             break;
                case 'F':   $this->_values[$f['alias']] = floatval($ra[$f['alias']]);   break;
                case 'K':
                case 'I':
                default:    $this->_values[$f['alias']] = intval($ra[$f['alias']]);     break;
            }



        } else if( $bForceDefaults ) {
            $this->_setDefault($f);
        }
    }

    function _setDefault( $f )
    /*************************
        $fieldDef is one element (an array itself) of a table's Fields array
     */
    {
        if( isset( $f['default'] ) ) {
            $this->_values[$f['alias']] = $f['default'];
        } else {
            //$this->_values[$f['alias']] = ($f['type'] == 'S' ? "" : 0);
            switch( $f['type'] ) {
                case 'S':
                case 'S+':
                            $this->_values[$f['alias']] = "";               break;
                case 'F':   $this->_values[$f['alias']] = floatval(0.0);    break;
                case 'K':
                case 'I':
                default:    $this->_values[$f['alias']] = intval(0);        break;

            }
        }
    }

    function _putFmtVal( $val, $type )
    /*********************************
        Return the correct Put format of the value
     */
    {
        switch( $type ) {
            case 'S+':
            case 'S':
                $s = "'".addslashes($val)."'";
                break;
            case 'F':
                $s = "'".floatval($val)."'";
                break;
            case 'I':
            case 'K':
                // protect against an empty value (default is 0)
                //
                // N.B. this is necessary because SetValue doesn't force an intval - maybe it should
                $s = intval($val);
                break;
            default:
                $s = $val;
                break;
        }
        return( $s );
    }
}



class KFRecordCursor extends KFRecord
/*******************
    A special KFRecord that can cursor over a set of records.
    UPDATE operations can be done between cursor fetches.
 */
{
    //protected:
    var $_dbc = NULL;       // KeyFrameRelation sets this on CursorOpen

    function __construct( KeyFrameRelation $kfrel )    // KFRecord and KFRecordCursor constructors have to do the same thing
    /**********************************************
     */
    {
        parent::__construct( $kfrel );
    }

    function CursorFetch()
    /*********************
     */
    {
        $ok = false;
        if( $this->_dbc && ($ra = $this->kfrel->kfdb->CursorFetch( $this->_dbc )) ) {
            $this->prot_getAllDBValuesFromRA( $ra );
            $ok = true;
        }
        return( $ok );
    }

    function CursorNumRows()
    /***********************
     */
    {
        return( $this->kfrel->kfdb->CursorGetNumRows($this->_dbc) );
    }

    function CursorClose()
    /*********************
     */
    {
        if( $this->_dbc ) {
            $this->kfrel->kfdb->CursorClose($this->_dbc);
            $this->_dbc = NULL;
        }
    }
}


class KFRelationView
/*******************
    A View is the set of rows from a Relation, after a filter, group, and sort.
    A Window is a range of contiguous rows from the View.

    View parms are defined at construction, and cannot change, so GetDataWindow can be repeated at different offsets with consistent results.
 */
{
    private $kfrel;
    private $p_sCond = "";
    private $raViewParms = array();
    private $numRowsCache = 0;

    function __construct( KeyFrameRelation $kfrel, $sCond = "", $raParms = array() )
    /*******************************************************************************
     */
    {
        $this->kfrel = $kfrel;
        $this->SetViewParms( $sCond, $raParms );
    }

    function SetViewParms( $sCond = "", $raParms = array() )
    /********************************************************
        raViewParms:
            sSortCol  - column to ORDER BY
            bSortDown - true:ASC, false:DESC
            sGroupCol - column to GROUP BY
            iStatus
     */
    {
        $this->p_sCond                  = $sCond;
        $this->raViewParms['sSortCol']  = (!empty($raParms['sSortCol']) ? $raParms['sSortCol']  : "_key");
        $this->raViewParms['bSortDown'] = (isset($raParms['bSortDown']) ? $raParms['bSortDown'] : true );
        $this->raViewParms['sGroupCol'] = @$raParms['sGroupCol'] ?? "";
        $this->raViewParms['iStatus']   = intval(@$raParms['iStatus']);
    }

    function GetDataWindow( $iOffset = 0, $nLimit = -1 )
    /***************************************************
        Return array(KFRecord) for the given span of rows.

        Examples (offset,limit):
            (0,10)   - the first ten rows
            (0,-1)   - all rows
            (10,10)  - rows 10 through 19
            (-1,1)   - the single last row
            (-10,10) - the last ten rows
            (-10,-1) - the last ten rows
     */
    {
        return( $this->kfrel->GetRecordSet( $this->p_sCond, $this->makeWindowParms( $iOffset, $nLimit ) ) );
    }

    function GetDataWindowRA( $iOffset = 0, $nLimit = -1 )
    /*****************************************************
        Like GetDataWindow but return array( array(values) ).
     */
    {
        return( $this->kfrel->GetRecordSetRA( $this->p_sCond, $this->makeWindowParms( $iOffset, $nLimit ) ) );
    }

    function GetDataRow( $iOffset )
    /******************************
        Get one row from the view
     */
    {
        $raKFR = $this->GetDataWindow( $iOffset, 1 );
        return( $raKFR && isset($raKFR[0]) ? $raKFR[0] : null );
    }

    private function makeWindowParms( $iOffset, $nLimit )
    {
        $raWindowParms = $this->raViewParms;
        if( $iOffset >= 0 ) {
            /* Offset from the top
             */
            $raWindowParms['iOffset'] = $iOffset;
            $raWindowParms['iLimit'] = $nLimit;
        } else {
            /* Offset from the bottom
             */
            $i = $this->GetNumRows() + $iOffset;        // the real-number offset
            if( $i < 0 ) $i = 0;
            $raWindowParms['iOffset'] = $i;
            $raWindowParms['iLimit'] = $nLimit;         // no problem if $i+$nLimit > numRows
        }
        return( $raWindowParms );
    }

    function FindOffsetByKey( $k )
    /*****************************
        Return the view offset of the row with key $k
        -1 == not found
     */
    {
        if( !$k )  return( -1 );

        // linear search is the only way I know
        $n = -1;
        $i = 0;
        // this could be optimized by adding an option to CreateRecordCursor to retrieve only the keys
        if( ($kfrc = $this->kfrel->CreateRecordCursor( $this->p_sCond, $this->raViewParms )) ) {
            while( $kfrc->CursorFetch() ) {
                if( $kfrc->Key() == $k ) {
                    $n = $i;
                    break;
                }
                ++$i;
            }
        }
        return( $n );
    }

    function GetNumRows()
    /********************
        Return the size of the view
     */
    {
        if( !$this->numRowsCache ) {
            if( ($kfrc = $this->kfrel->CreateRecordCursor( $this->p_sCond, $this->raViewParms )) ) {
                $this->numRowsCache = $kfrc->CursorNumRows();
                $kfrc->CursorClose();
            }
        }
        return( $this->numRowsCache );
    }
}


class KeyFrameNamedRelations
/***************************
    Simplify access to a set of relations by giving each a name like A, B, or C.
    Implements a set of standard accessors to those relations.
 */
{
    private $raKfrel = array();

    function __construct( KeyFrameDB $kfdb, $uid )
    {
        $this->raKfrel = $this->initKfrel( $kfdb, $uid );  // override this protected function to create an array('A'=>kfrelA, 'B'=>kfrelB)
    }

    function GetKfrel( $sRel ) { return( @$this->raKfrel[$sRel] ); }

    function GetKFR( $sRel, $k )
    /***************************
        Return a kfr with one result pre-loaded
     */
    {
        return( ($kfrel = $this->GetKfrel($sRel)) ? $kfrel->GetRecordFromDBKey( $k ) : null );
    }

    function GetKFRCond( $sRel, $sCond, $raKFParms = array() )
    /*********************************************************
        Return a kfr with one result pre-loaded
     */
    {
        return( ($kfrel = $this->GetKfrel($sRel)) ? $kfrel->GetRecordFromDB( $sCond, $raKFParms ) : null );
    }

    function GetKFRC( $sRel, $sCond = "", $raKFParms = array() )
    /***********************************************************
        Return a kfrc that needs CursorFetch to load the first result
     */
    {
        return( ($kfrel = $this->GetKfrel($sRel)) ? $kfrel->CreateRecordCursor( $sCond, $raKFParms ) : null );
    }

    function GetList( $sRel, $sCond, $raKFParms = array() )
    /******************************************************
        Return an array of results
     */
    {
        return( ($kfrel = $this->GetKfrel($sRel)) ? $kfrel->GetRecordSetRA( $sCond, $raKFParms ) : array() );
    }


    protected function initKfrel( KeyFrameDB $kfdb, $uid )
    {
        die( "OVERRIDE with function to create kfrel array" );
    }
}

?>
