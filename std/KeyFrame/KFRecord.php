<?
/*
    Manage records in a database.

    A KeyFrameRecord object is constructed with a relation definition.
    It can then be used as a cursor to read a set of records.
    The current (cursor) record can be modified and updated back to the database.
    It can also be hidden or deleted.

    The tables must have the following fields:

        _rowid      INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT,
        _created    DATETIME,
        _created_by INTEGER,
        _updated    DATETIME,
        _updated_by INTEGER,
        _status     INTEGER DEFAULT 0,
                    # 0=normal, 1=hidden, 2=deleted

    A KeyFrameRecord constructor takes a KeyFrameRecordDefinition:

    array( "Tables" => array( array( "Table"  => "people",
                                     "Type"   => "Base",
                                     "Fields" => array( array( "col"=>"firstname", "type"=>'S', "default"=>"" ),
                                                        array( "col"=>"lastname",  "type"=>'S', "default"=>"" ),
                                                        array( "col"=>"birthyear", "type"=>'I', "default"=>0 ),
                                                        array( "col"=>"fk_homes",  "type"=>'K' ) ) ),

                              array( "Table"  => "homes",
                                     "Type"   => "Parent",
                                     "Alias"  => "H",
                                     "Fields" => array( array( "type"=>'S', "col"=>"address",   "alias"=>"address" ),
                                                        array( "type"=>'I', "col"=>"value",     "alias"=>"house_value" ),
                                                        array( "type"=>'K', "col"=>"fk_cities" ) ) ),

                              array( "Table"  => "cities",
                                     "Type"   => "Grandparent",
                                     "Alias"  => "C",
                                     "Fields" => array( array( "col"=>"name",                  "type"=>'S' ),
                                                        array( "col"=>"mayor",                 "type"=>'S' ),
                                                        array( "col"=>"fk_city_size_category", "type"=>'K' ) ) ),

                              array( "Table"  => "city_size_category",
                                     "Type"   => "CatalogPick",
                                     "Alias"  => "CS",
                                     "Fields" => array( array( "col"=>"category_name", "type"=>'S' ),
                                                        array( "col"=>"min",           "type"=>'I' ),
                                                        array( "col"=>"max",           "type"=>'I' ) ) )
                            )
    );

    Rules:
        One table must have Type==Base.  If none do, then the first table def is the Base.
        Foreign key column names must match the table names.
        Tables may have aliases.  Default base tableAlias is Base.
        Columns may have aliases.  Default base table colAliases are the same as the base column names.
        Default non-base colAliases are tableAlias.col
        User column names may not start with "_", since we use this clue to skip internal kfr values
*/

include_once( "KFDB.php" );


class KeyFrameRecord {
/*******************
 */
    // constructor parms
    var $kfdb;      // ref to a KeyFrameDB instance
    var $kfrdef;
    var $uid;

    // internal constants
    var $baseTable;     // ref to the base table definition in $this->kfrdef
    var $qSelect;       // cache the constant part of the SELECT query
    var $raTableN2A;    // store all table names and aliases for reference ( array of tableName => tableAlias )
    var $raColAlias;    // store all field names for reference ( array of colAlias => tableAlias.col )

    // the Record
    var $_rowid;
    var $_values;
    var $_dbValSnap;       // a snapshot of the _values most recently retrieved from the db.  For change detection.

    // variables
    var $_logFile;
    var $_dbc;


    function KeyFrameRecord( &$kfdb, $kfrdef, $uid )
    /***********************************************
     */
    {
        $this->kfdb   = &$kfdb;
        $this->kfrdef = $kfrdef;    // copy because we modify it
        $this->uid    = $uid;

        $this->qSelect = NULL;
        $this->_logFile = NULL;
        $this->_dbc     = NULL;


        /* Make sure every table has an alias.
         * Default table alias for base is Base.  Default table alias for other tables is a generated code.
         *
         * Make sure every column has an alias.
         * Default col alias for base table is the column name.  Default col alias for other tables is tableAlias_col
         */
        for( $i = 0; $i < count($this->kfrdef['Tables']); $i++ ) {
            $t = &$this->kfrdef['Tables'][$i];      // is there a way to do this with a foreach( kfrdef['Tables'] as &$t )?

            if( empty( $t['Type'] ) ) {
                $t['Type'] = 'Base';
            }
            if( $t['Type'] == 'Base' ) {
                $this->baseTable = &$this->kfrdef['Tables'][$i];
            //  $this->baseTable["Fields"][] = array( "type"=>"I", "col"=>"_rowid",      "default"=>0 );
            //  $this->baseTable["Fields"][] = array( "type"=>"S", "col"=>"_updated",    "default"=>0 );
            //  $this->baseTable["Fields"][] = array( "type"=>"I", "col"=>"_updated_by", "default"=>$this->uid );
            //  $this->baseTable["Fields"][] = array( "type"=>"S", "col"=>"_created",    "default"=>0 );
            //  $this->baseTable["Fields"][] = array( "type"=>"I", "col"=>"_created_by", "default"=>$this->uid );
            //  $this->baseTable["Fields"][] = array( "type"=>"I", "col"=>"_status",     "default"=>0 );
            }
            $this->kfrdef["Tables"][$i]["Fields"][] = array( "type"=>"I", "col"=>"_rowid",      "default"=>0 );
            $this->kfrdef["Tables"][$i]["Fields"][] = array( "type"=>"S", "col"=>"_updated",    "default"=>0 );
            $this->kfrdef["Tables"][$i]["Fields"][] = array( "type"=>"I", "col"=>"_updated_by", "default"=>$this->uid );
            $this->kfrdef["Tables"][$i]["Fields"][] = array( "type"=>"S", "col"=>"_created",    "default"=>0 );
            $this->kfrdef["Tables"][$i]["Fields"][] = array( "type"=>"I", "col"=>"_created_by", "default"=>$this->uid );
            $this->kfrdef["Tables"][$i]["Fields"][] = array( "type"=>"I", "col"=>"_status",     "default"=>0 );

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

        $this->kfr_Clear();
    }

    function value($k)    { return( $this->kfr_Value($k) ); }
    function valueEnt($k) { return( $this->kfr_ValueEnt($k) ); }

    function kfr_Value( $k )
    /***********************
     */
    {
        $v = null;
        if( array_key_exists( $k, $this->_values ) ) {
            $v = $this->_values[$k];
        }
        return( $v );
    }

    function kfr_ValueEnt( $k )     { return( htmlspecialchars($this->kfr_Value($k),ENT_QUOTES) ); }
    function kfr_Rowid()            { return( $this->_rowid ); }
    function kfr_Empty( $k )        { $v = $this->kfr_Value($k); return( empty($v) ); } // because empty doesn't work on methods
    function kfr_SetRowid( $i )     { $this->_rowid = $i;      $this->kfr_SetValue('_rowid', $i ); }
    function kfr_SetValue( $k, $v ) { $this->_values[$k] = $v; }

    function kfr_Clear()
    /*******************
        Clear the values and set defaults
     */
    {
        $this->_rowid = 0;
        $this->_values = array();
        $this->_dbValSnap = array();
        foreach( $this->baseTable["Fields"] as $k ) {
            $this->_setDefault($k);
        }
    }

    function kfr_SetDefault( $raFK = array() )
    /*******************************************
        With no args, this is the same as kfr_Clear()
        Args of "table"=>"fk rowid" cause those foreign keys to be set in the relation, and foreign data to be
        retrieved for non-base tables.  This is especially useful for creating an "empty" row in a form that
        displays read-only data from a parent row.
     */
    {
        $this->kfr_Clear();

        // Leave _dbValSnap cleared because it's only used in updates to the base row, which is not set by this method.


    // N.B. only implemented for one level of indirection from the base table.
    //      Traversal required to fetch data for a second-level row e.g. grandparent
        foreach( $raFK as $tableName => $fkRowid ) {
            $this->_values['fk_'.$tableName] = $fkRowid;

            foreach( $this->kfrdef['Tables'] as $t ) {
                if( $t['Table'] != $tableName )  continue;
                $raSelFields = array();
                foreach( $t['Fields'] as $f ) {
                    $raSelFields[] = $t['Alias'].".".$f['col']." as ".$f['alias'];
                }
                $ra = $this->kfdb->KFDB_QueryRA( "SELECT ".implode(",",$raSelFields)." FROM ".$t['Table']." ".$t['Alias'].
                                                 " WHERE ".$t['Alias']."._rowid=$fkRowid" );
                // array_merge is easier, but KFDB returns duplicate entries in $ra[0],$ra[1],...
                foreach( $t['Fields'] as $f ) {
                    $this->_values[$f['alias']] = $ra[$f['alias']];
                }
            }
        }
    }

    function kfr_IsBaseField( $q )
    /*****************************
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

    function kfr_GetDBRow( $rowid )
    /******************************
     */
    {
        $ok = false;

        $this->kfr_Clear();

        $q = $this->_makeSelect( "{$this->baseTable['Alias']}._rowid=$rowid", array("iStatus"=>-1) );

        if( ($ra = $this->kfdb->KFDB_QueryRA( $q )) ) {
            $this->_getBaseValuesFromArray( $ra );
            $this->_getFKValuesFromArray( $ra );
            $this->_dbValSnap = $this->_values;
            $ok = true;
        }
        return( $ok );
    }

    function kfr_GetFromArray( $ra, $bForceDefaults = true )
    /*******************************************************
     */
    {
        return( $this->_getBaseValuesFromArray( $ra, $bForceDefaults ) );
    }

    function kfr_GetFromArrayGPC( $gpc, $bForceDefaults = true )
    /***********************************************************
        Same as GetFromArray, but strips slashes from array values if magic_quotes_gpc is on
     */
    {
        $ra = array();

        if( get_magic_quotes_gpc() ) {
            foreach( $this->baseTable['Fields'] as $f ) {
                if( isset( $gpc[$f['alias']] ) ) {
                    $ra[$f['alias']] = stripslashes( $gpc[$f['alias']] );
                }
            }
        } else {
            $ra = $gpc;
        }
        $this->_getBaseValuesFromArray( $ra, $bForceDefaults );
    }

    function kfr_GetDBColName( $table, $col )
    /****************************************
        Return the name that is used for the given column in SELECT queries.  (e.g. T2.foo)
        This can be used to generate condition expressions with tables that don't have user-defined alias names (e.g. T2.foo)
     */
    {
        return( $this->raTableN2A[$table].".$col" );  // the value of the array is the tableAlias
    }

    function kfr_GetDBColAlias( $table, $col )
    /*****************************************
        Return the alias that is used for the given column in SELECT queries.  (e.g. T2_foo)
        This can be used to retrieve fields from tables what don't have user-defined alias names
     */
    {
        $alias = "";
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
        return( $alias );
    }

    function _getBaseValuesFromArray( $ra, $bForceDefaults = true )
    /**************************************************************
        Get the base field values found in $ra.
        Turn off defaults if updating an existing record with a subset of altered values.
     */
    {
        if( isset($ra['_rowid']) ) {        // _rowid won't be in ra if these are $_REQUEST values posted from a form
            $this->_rowid = intval($ra['_rowid']);
        } else if( $bForceDefaults ) {
            $this->_rowid = 0;
        }
        foreach( $this->baseTable['Fields'] as $f ) {
            $this->_getValFromRA( $f, $ra, $bForceDefaults );
        }
    }

    function _getFKValuesFromArray( $ra, $bForceDefaults = true )
    /************************************************************
     */
    {
        foreach( $this->kfrdef['Tables'] as $t ) {
            if( $t['Type'] == 'Base' )  continue;

            foreach( $t['Fields'] as $f ) {
                $this->_getValFromRA( $f, $ra, $bForceDefaults );
            }
        }
    }

    function _getValFromRA( $f, $ra, $bForceDefaults )
    /*************************************************
     */
    {
        if( isset( $ra[$f['alias']] ) ) {
            $this->_values[$f['alias']] = ($f['type']=='S' ? $ra[$f['alias']] : intval($ra[$f['alias']]));
        } else if( $bForceDefaults ) {
            $this->_setDefault($f);
        }
    }


    function kfr_Query( $cond, $parms = array() )
    /********************************************
        Get one row that matches the condition
     */
    {
        $ok = false;
        if( $this->kfr_CursorOpen($cond,$parms) )  $ok = $this->kfr_CursorFetch();
        $this->kfr_CursorClose();
        return( $ok );
    }

    function kfr_CursorOpen( $cond = "", $parms = array() )
    /******************************************************
        parms: sSortCol  => name of column to sort (can be multiple columns comma-separated)
               bSortDown => true/false
               iOffset   => offset of rows to return
               iLimit    => max rows to return (might help to optimize query on the server end)
               iStatus   => _status=iStatus  default 0
     */
    {
        $q = $this->_makeSelect( $cond, $parms );
        $this->_dbc = $this->kfdb->KFDB_CursorOpen( $q );
        return( $this->_dbc );
    }

    function kfr_CursorFetch()
    /*************************
     */
    {
        $ok = false;
        if( $this->_dbc && ($ra = $this->kfdb->KFDB_CursorFetch( $this->_dbc )) ) {
            $this->_getBaseValuesFromArray( $ra );
            $this->_getFKValuesFromArray( $ra );
            $this->_dbValSnap = $this->_values;
            $ok = true;
        }
        return( $ok );
    }

    function kfr_CursorClose()
    /*************************
     */
    {
        if( $this->_dbc ) {
            $this->kfdb->KFDB_CursorClose($this->_dbc);
            $this->_dbc = NULL;
        }
    }

    function _putFmtVal( $val, $type )
    /*********************************
        Return the correct Put format of the value
     */
    {
        switch( $type ) {
            case 'S':
                $s = "'".addslashes($val)."'";
                break;
            case 'I':
            case 'K':
                // protect against an empty value (default is 0)
                //
                // N.B. this is necessary because SetValue doesn't force an intval - maybe it should
                $s = intval($val);
                break;
/*
 *            case 'I':
 *                // protect against an empty value (quoted empty string should cause the SQL default)
 *                //
 *                // N.B. this is necessary because SetValue doesn't force an intval - maybe it should
 *                //
 *                // Although '' might not be platform-independent (?) so maybe this should just do intval so empty -> 0
 *                if( empty($val) && $val !== 0 ) {   // *** strong-type-checking operator.  !=0 and !empty() are the same
 *                    $s = "''";
 *                } else {
 *                    $s = intval($val);
 *                }
 *                break;
 */
            default:
                $s = $val;
                break;
        }
        return( $s );
    }

    function kfr_PutDBRow( $bUpdateTS = false )
    /******************************************
        Insert/Update the row as needed.  The choice is based on $this->rowid==0.

        This does NOT automatically update $this->_values('_created') and ('_updated'), since that requires an extra fetch.
        $bUpdateTS==true causes this fetch
     */
    {
        $ok = false;

        if( $this->_rowid ) {
            /* UPDATE all user fields, plus _status, _updated and _updated_by.
             * _rowid and _created* don't change.
             */
            $bDo = false;
            $bSnap = (array_key_exists( "_rowid", $this->_dbValSnap ) && ($this->_dbValSnap["_rowid"] == $this->_rowid) );

            $s = "UPDATE {$this->baseTable['Table']} SET _updated=NOW(),_updated_by={$this->uid}";
            foreach( $this->baseTable['Fields'] as $f ) {
                if( $f['col'] != '_rowid' &&
                    $f['col'] != '_created' &&
                    $f['col'] != '_created_by' )
                {
                    /* Use the dbVal snapshot to inhibit update of unchanged fields.  Though most db engines do this
                     * anyway, this makes kfr log files much more readable.
                     */
                    if( $bSnap && array_key_exists( $f['col'], $this->_dbValSnap ) &&
                          ($this->_values[$f['col']] == $this->_dbValSnap[$f['col']]) ) {
                        continue;
                    }
                    $s .= ",".$f['col']."=".$this->_putFmtVal($this->_values[$f['alias']], $f['type'] );
                    $bDo = true;
                }
            }
            if( $bDo ) {
                $s .= " WHERE _rowid={$this->_rowid}";
                $ok = $this->kfdb->KFDB_Execute( $s );
                $this->_log($s.($ok ? "" : $this->kfdb->KFDB_GetMsgErr()));
            } else {
                $ok = true;
            }
        } else {
            /* INSERT all client fields, plus kfr fields.  Set _created=_updated=NOW().  Set _rowid to a new autoincrement.
             * Other dPR fields default to the correct initial values.
             */
            $sk = "";
            $sv = "";
            foreach( $this->baseTable['Fields'] as $f ) {
                if( $f['col'] != '_rowid' &&
                    $f['col'] != '_created' &&
                    $f['col'] != '_created_by' &&
                    $f['col'] != '_updated' &&
                    $f['col'] != '_updated_by' )
                {
                    $sk .= ",".$f['col'];
                    $sv .= ",".$this->_putFmtVal( $this->_values[$f['alias']], $f['type'] );
                }
            }

            $s = "INSERT INTO {$this->baseTable['Table']} (_rowid,_created,_updated,_created_by,_updated_by $sk) ";
            $s .= "VALUES (NULL,NOW(),NOW(),{$this->uid},{$this->uid} $sv)";

            /* In MySQL, this depends on _rowid being the first AUTOINCREMENT column.
             */
            if( ($r = $this->kfdb->KFDB_InsertAutoInc( $s )) ) {
                $this->kfr_SetRowid( $r );
                $this->_log($s);
                $ok = true;
            }
        }
        if( $ok ) {
            if( $bUpdateTS ) {
                if( ($ra = $this->kfdb->KFDB_QueryRA( "SELECT _created,_updated FROM {$this->baseTable['Table']} WHERE _rowid={$this->_rowid}" )) ) {
                    $this->_values['_created'] = $ra['_created'];
                    $this->_values['_updated'] = $ra['_updated'];
                }
            }
            $this->_dbValSnap = $this->_values;
        }
        return( $ok );
    }

    function kfr_SetLogFile( $filename )    { $this->_logFile = $filename; }


    function _setDefault( $f )
    /*************************
        $fieldDef is one element (an array itself) of a table's Fields array
     */
    {
        if( isset( $f['default'] ) ) {
            $this->_values[$f['alias']] = $f['default'];
        } else {
            $this->_values[$f['alias']] = ($f['type'] == 'S' ? "" : 0);
        }
    }

    function _makeSelect( $cond = "", $parms = array() )
    /***************************************************
        $status == -1:  do not write a _status condition clause
     */
    {
        $sGroupCol = @$parms['sGroupCol'];
        $sSortCol  = @$parms['sSortCol'];
        $bSortDown = intval(@$parms['bSortDown']);
        $iOffset   = intval(@$parms['iOffset']);
        $iLimit    = intval(@$parms['iLimit']);
        $iStatus   = intval(@$parms['iStatus']);

        if( empty($this->qSelect) ) {
            /* Make the constant part of the SELECT once and cache it
             */
            $raSelFields = array();
            $raSelTables = array();
            $raSelCond = array();


            // compose SELECT field list, table list, condition list
            foreach( $this->kfrdef['Tables'] as $t ) {
                $raSelTables[] = $t['Table'].' '.$t['Alias'];

                foreach( $t['Fields'] as $f ) {
                    $sCol = $f['col'];
                    if( substr($sCol,0,3) == "fk_" ) {
                        /* This is a foreign key to another table.
                         * If that table is in the kfrdef, compose a join condition.
                         * Foreign key fields are included in the SelFields list in case a client wants to
                         * see them; in particular they are necessary in the Base table for updating and copying.
                         */
                        $ft = substr($sCol,3);
                        if( array_key_exists( $ft, $this->raTableN2A ) ) {
                            $raSelCond[] = "(".$t['Alias'].".$sCol=".$this->raTableN2A[$ft]."._rowid)";
                        }
                    }
                    $raSelFields[] = $t['Alias'].".$sCol as ".$f['alias'];
                }
            }
            if( !count($raSelCond) )  $raSelCond[] = "1=1";
            $this->qSelect = "SELECT ".implode(',', $raSelFields)." FROM ".implode(',', $raSelTables).
                             " WHERE ".implode(' AND ', $raSelCond);
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
        if( $iLimit  > 0 )  $q .= " LIMIT $iLimit";
        if( $iOffset > 0 )  $q .= " OFFSET $iOffset";

        return( $q );
    }

    function _log( $s )
    /******************
     */
    {
        if( !empty($this->_logFile) ) {
            if( $fp = fopen( $this->_logFile, "a" ) ) {
                $out = sprintf( "-----\n%d\n%s\n", time(), $s );
                fwrite( $fp, $out );
                fclose( $fp );
            }
        }
    }
}

?>
