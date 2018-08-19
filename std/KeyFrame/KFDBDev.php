<?
/* KeyFrame development database access (MySQL version)
 *
 * Extensions to the basic database access that would not be used in a production environment
 */

include_once( "KFDB.php" );


class KeyFrameDBDev extends KeyFrameDB {

    function CreateTable( $kfrdef )
    /******************************
        Test if base table exists.
        If not, create it.
     */
    {
        $ok = false;

        $this->KFDB_SetDebug( 2 );

        $table = null;
        foreach( $kfrdef['Tables'] as $t ) {
            if( empty($t['Type']) || $t['Type'] == 'Base' ) {
                $table = $t;
            }
        }

        if( $table ) {
            if( !($dbc = $this->KFDB_CursorOpen( "SELECT count(*) FROM {$table['Table']}" )) ) {
                /* Table probably hasn't been created yet.
                 */
                $s = "CREATE TABLE {$table['Table']} (".
                        "_rowid      INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT,".
                        "_created    DATETIME,".
                        "_created_by INTEGER,".
                        "_updated    DATETIME,".
                        "_updated_by INTEGER,".
                        "_status     INTEGER DEFAULT 0";    // 0=normal, 1=hidden, 2=deleted
                foreach( $table['Fields'] as $f ) {
                    $s .= ",".$f['col']." ".($f['type']=='S' ? "TEXT" : "INTEGER");
                }
                $s .= ")";

                $this->KFDB_Execute( $s );
            }
        }
        $this->KFDB_SetDebug( 0 );
        return( $ok );
    }
}


?>
