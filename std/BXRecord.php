<?
/* Manage records.

   BXRecord() constructor takes an array of this format:
        table    => table name
        rowid    => name of record id column
        disabled => name of boolean column that marks hidden/disabled rows
        fields   => array( array( field_name, field_type, default ), ... )
                        where the field type is s (string) or i (int)
*/

class BXRecord {
/*************
 */
    var $table;
    var $rowidname;
    var $disabledname;
    var $_fields;   // array of key names, types and defaults

    var $rowid;     // integer
    var $disabled;
    var $_values;   // field values keyed by key names

    function BXRecord( $def ) {
        $this->table         = $def['table'];
        $this->rowidname     = @$def['rowid'];
        $this->disabledname  = @$def['disabled'];
        $this->_fields       = $def['fields'];
        $this->rowid = 0;
        $this->_values = array();
    }

    function BXR_value( $k )        { return( $this->_values[$k] ); }
    function BXR_empty( $k )        { return( empty($this->_values[$k]) ); }
    function BXR_setRowid( $i )     { $this->rowid = $i; }
    function BXR_setValue( $k, $v ) { $this->_values[$k] = $v; }

    function BXRecord_LoadDefaults() {
        $this->rowid = 0;
        $this->disabled = 0;
        unset($this->_values);
        foreach( $this->_fields as $k ) {
            $this->_values[$k[0]] = $k[2];
        }
    }

    function BXRecord_GetFromDB( $i ) {
        $ra = db_query( "SELECT * FROM {$this->table} WHERE {$this->rowidname}=$i" );
        $this->BXRecord_GetFromArray( $ra );
    }

    function BXRecord_GetDBLast() {
        $ra = db_query( "SELECT * FROM {$this->table} ORDER BY {$this->rowidname} DESC" );
        $this->BXRecord_GetFromArray( $ra );
    }

    function BXRecord_GetFromArray( $ra ) {
        /* Get the keyed values found in $ra
         */
        if( !empty($this->rowidname) && !@empty($ra[$this->rowidname]) ) {   // might not be here if $ra==$_REQUEST.
            $this->rowid = $ra[$this->rowidname];
        }
        foreach( $this->_fields as $k ) {
            $this->_values[$k[0]] = $k[1]=='i' ? intval(@$ra[$k[0]]) : @$ra[$k[0]];
        }
    }

    function BXRecord_GetFromArrayGPC( $gpc ) {
        /* Same as GetFromArray, but strips slashes from $ra if magic_quotes_gpc is on
         */
        $ra = array();

        if( get_magic_quotes_gpc() ) {
            foreach( $this->_fields as $k ) {
                $ra[$k[0]] = stripslashes( $gpc[$k[0]] );
            }
        } else {
            $ra = $gpc;
        }
        $this->BXRecord_GetFromArray( $ra );
    }

    function BXRecord_MakeInsertCmd( $extra_keys, $extra_values ) {
        /* Return an SQL insert command for the current _keys and _values.
         *
         *  rowid is not inserted - must be AUTO_INCREMENT
         *  disabled is not inserted - must be DEFAULT 0
         */
        $s = "INSERT INTO {$this->table} ($extra_keys";

        // add the keys
        $comma = !empty($extra_keys);
        foreach( $this->_fields as $k ) {
            if( $comma )  $s .= ",";
            $s .= $k[0];
            $comma = true;
        }
        $s .= ") VALUES ($extra_values";

        // add the values
        $comma = !empty($extra_values);
        foreach( $this->_fields as $k ) {
            if( $comma )  $s .= ",";
            if( $k[1] == 's' ) {
                $s .= "'".addslashes($this->_values[$k[0]])."'";
            } else {
                $s .= $this->_values[$k[0]];
            }
            $comma = true;
        }
        $s .= ")";
        return( $s );
    }

    function BXRecord_MakeUpdateCmd( $extra_fields, $cond ) {
        /* Return an SQL Update command for the current _keys and _values
         */
        $s = "UPDATE {$this->table} SET $extra_fields";
        $comma = !empty($extra_fields);
        foreach( $this->_fields as $k ) {
            if( $comma )  $s .= ",";
            $s .= $k[0] ."=";
            if( $k[1] == 's' ) {
                $s .= "'".addslashes($this->_values[$k[0]])."'";
            } else {
                $s .= $this->_values[$k[0]];
            }
            $comma = true;
        }
        if( !empty($cond) )  $s .= " WHERE $cond";
        return( $s );
    }
}


function BXRecordAdmin_Delete( $action, $la, $rec, $getbackurl, $recShowCallback )
/*********************************************************************************
    $action          = 'delete': delete the record, '{anything else}': confirm and call self with 'delete'
    $la              = current LoginAuth
    $rec             = BXRecord of the record to be deleted
    $getbackurl      = url to get back to the admin page on Cancel or Complete
    $recShowCallback = func that draws the record when given $i
 */
{
    if( $action == 'delete' ) {
        // Delete the record
        $query = "UPDATE {$rec->table} SET {$rec->disabledname}=1 WHERE {$rec->rowidname}={$rec->rowid}";
        echo db_exec( $query )
                ? "<P>The delete succeeded</P>"
                : "<P>The delete did not succeed.</P>".db_errmsg($query);
        echo "<P><A HREF='$getbackurl'>Click here to continue.</A></P>";
    } else {
        // Confirm
        $recShowCallback( $rec );

        echo "<HR>";
        echo "<P>Are you sure you want to delete this record?</P>";
        echo "<FORM action='{$_SERVER['PHP_SELF']}'>";
        echo $la->login_auth_get_hidden();
        echo "<INPUT TYPE=HIDDEN NAME=i VALUE='{$rec->rowid}'>";
        echo "<INPUT TYPE=HIDDEN NAME=action VALUE='delete'>";
        echo "<P><INPUT TYPE=SUBMIT VALUE='Delete'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
        echo "<A HREF='$getbackurl'>Cancel</A></TD></TR>";
        echo "</FORM>";
    }
}


?>
