<?php

/* SEEDSetup
 *
 * Copyright 2009-2010 Seeds of Diversity Canada
 *
 * Help to install web sites on servers
 */


class SEEDSetup
{
    var $kfdb;  // the database where tables will be created

    function __construct( KeyFrameDB $kfdb )
    {
        $this->kfdb = $kfdb;
    }

    function SetupTable( $table, $sqlCreateTable, $bCreate, &$sReport )
    /******************************************************************
        $bCreate == false : test for existence of table
        $bCreate == true  : if not exist, execute sqlCreateTable to create a database table

        Return bool success
        sReport: return string reporting what happened
     */
    {
        if( !($bRet = $this->kfdb->TableExists( $table )) ) {
            if( $bCreate ) {
                $bRet = $this->kfdb->Execute( $sqlCreateTable );
                $sReport .= ($bRet ? "Created table $table\n" : ("Failed to create $table. ".$this->kfdb->GetErrMsg()."\n"));
            } else {
                $sReport .= "Table $table does not exist\n";
            }
        }
        return( $bRet );
    }
}

?>