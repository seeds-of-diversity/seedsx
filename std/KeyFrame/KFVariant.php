<?

/* Store values of variant types.
   No one ever said this was an efficient way to store information, but it sure is convenient.

   Variant     manages a variant data type.
   KFDBVariant manages v_* columns in a db row
   KFRVariant  manages v_* columns in a KFRecord data object (does not update the row)



   Types:               Columns:
                            v_type              CHAR
       S = string           v_s                 TEXT
       I = integer          v_i                 INTEGER
       F = float            v_f                 DECIMAL(5,3)
       R = rational         v_r_num,v_r_den     INTEGER, INTEGER
       D = date             v_d                 DATE
       T = time             v_t                 TIME

CREATE TABLE KFVariantData (

        _rowid      INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT,
        _created    DATETIME,
        _created_by INTEGER,
        _updated    DATETIME,
        _updated_by INTEGER,
        _status     INTEGER DEFAULT 0,

    set             VARCHAR(200)    NOT NULL,   // namespace partition
    name            VARCHAR(200)    NOT NULL,   // name of the value
    v_type          CHAR            NOT NULL,
    v_s             TEXT            NULL,
    v_i             INTEGER         NULL.
    v_f             DECIMAL(5,3)    NULL,
    v_r_num         INTEGER         NULL,
    v_r_den         INTEGER         NULL,
    v_d             DATE            NULL,
    v_t             TIME            NULL,

    INDEX KFVD_set  (set(20)),
    INDEX KFVD_name (name(20))
);

*/

class Variant {
    var $set;
    var $type;
    var $val;       // for all but R
    var $val_r_num;
    var $val_r_den;

    function Variant( $type, $val, $val_r_den = 0 ) {
        $this->SetValue( $type, $val, $val_r_den );
    }

    function GetType()       { return( $this->type ); }
    function GetValue()      { return( $this->val ); }
    function GetValue_RNum() { return( $this->val_r_num ); }
    function GetValue_RDen() { return( $this->val_r_den ); }

    function SetValue( $type, $val, $val_r_den = 0 ) {
        $this->type = $type;
        switch( $type ) {
            case 'S':
                $this->val = strval($val);
                break;
            case 'I':
                $this->val = intval($val);
                break;
            case 'F':
                $this->val = floatval($val);
                break;
            case 'R':
                $this->val_r_num = intval($val);
                $this->val_r_den = intval($val_r_den);
                $this->val = floatval($this->val_r_den != 0 ? ($this->val_r_num / $this->val_r_den) : 0);
                break;
            case 'D':
                $this->val = strval($val);
                break;
            case 'T':
                $this->val = strval($val);
                break;
            default:
                $this->val = intval(0);
                break;
        }
    }
}


class VariantDB {
    /* Put/Get a variant into a database row.
     * Row requires _rowid and v_*
     *
     * N.B. This does not update all of the KF fields
     *      Maybe it should work differently
     */
    var $kfdb;      // ref to KeyFrameDB instance
    var $tablename;

    function VariantDB( &$kfdb, $tablename ) {
        $this->kfdb = &$kfdb;
        $this->tablename = $tablename;
    }

    function vdb_Put( $variant, $rowid = 0 ) {
        if( $rowid ) {
            /* We do not clear old fields if the type changes.  Not necessary, except perhaps for security.
             */
            $q = "UPDATE ".$this->tablename." SET v_type='".$variant->GetType()."',";
            if( $variant->GetType() == 'R' ) {
                $q .= "v_r_num='".$variant->GetValue_RNum()."',v_r_den='".$variant->GetValue_RDen()."'";
            } else {
                $q .= "v_".$variant->GetType()."='".$variant->GetValue()."'";
            }
            $q .= " WHERE _rowid=$rowid";
        } else {
            $q = "INSERT INTO ".$this->tablename." (v_type,";
            if( $variant->GetType() == 'R' ) {
                $q .= "v_r_num,v_r_den) VALUES (='".$variant->GetValue_RNum()."',v_r_den='".$variant->GetValue_RDen()."'";
            } else {
                $q .= "v_".$variant->GetType()."='".$variant->GetValue()."'";
            }
        }
        return( $this->kfdb->kfdb_Execute( $q ) );
    }

    function vbf_Get( $rowid ) {
        $v = NULL;
        if( ($ra = $this->kfdb->kfdb_QueryRA( "SELECT * FROM ".$this->tablename." WHERE _rowid=$rowid" )) ) {
            if( !empty($ra['v_type']) ) {
                if( $ra['v_type'] ) {
                    $v = new Variant( 'R', $ra['v_r_num'], $ra['v_r_den'] );
                } else {
                    $v = new Variant( $ra['v_type'], $ra['v_'.$ra['v_type']] );
                }
            }
        }
        return( $v );
    }
}


class VariantKFRec {
    /* Put/Get the variant fields in a KeyFrameRecord
     * The KFRec must contain all v_* fields
     *
     * This does not update the database, just the KFRec
     */
    var $kfrec;     // ref to KeyFrameRecord instance

    function VariantKFRec( &$kfrec ) {
        $this->kfrec = &$kfrec;
    }

    function vkfr_Put( $variant ) {
        $this->kfrec->kfr_SetValue( 'v_type', $variant->GetType() );
        if( $variant->GetType() == 'R' ) {
            $this->kfrec->kfr_SetValue( 'v_r_num', $variant->GetValue_RNum() );
            $this->kfrec->kfr_SetValue( 'v_r_dem', $variant->GetValue_RDem() );
        } else {
            $this->kfrec->kfr_SetValue( 'v_'.$variant->GetType(), $variant->GetValue() );
        }
    }

    function vkfr_Get() {
        if( $this->kfr_Value( 'v_type' ) == 'R' ) {
            $v = new Variant( 'R', $this->kfr_Value( 'v_r_num'), $this->kfr_Value('v_r_den') );
        } else {
            $v = new Variant( $this->kfr_Value('v_type'), $this->kfr_Value( 'v_'.$this->kfr_Value('v_type')) );
        }
        return( $v );
    }
}


?>
