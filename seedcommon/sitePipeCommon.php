<?php

/* Create a secure pipe for transferring data between sites that share a database
 *
 * When two sites have access to the same database, they can communicate securely by:
 *
 * 1) Site A creates a db row containing a key, an unguessable code, and parms describing a request
 * 2) Site A sends an http request to Site B with the key, unguessable code
 * 3) Site B verifies the unguessable code and immediately erases it, serves the request, writes the
 *    response (including any error result) to the same db row, and completes the http with a success code
 * 4) Site A looks in the database for the response, and deletes the row
 *
 *
 * Using SEEDMetaTable_TablesLite, the command/parms are stored in raVals and the signature is stored in k1.
 */

include_once( STDINC."SEEDMetaTable.php" );

class SitePipe
{
    private $oTable;
    private $kTable;

    function __construct( KeyFrameDB $kfdb )
    // $kfdb is a database that is accessible to both sites
    {
        $this->oTable = new SEEDMetaTable_TablesLite( $kfdb );
        $this->kTable = $this->oTable->OpenTable( "SeedOfficePipe" );
    }

    function CreatePipeRequest( $raReq )
    /***********************************
        The local server creates a request.
        $raReq is an array of cmds/parms that tell the remote server what to do
        Create a row in a SeedMetaTable that contains these raVals and a unique signature.
        Return the row key and the unique id.
     */
    {
        $kRow = 0;
        $signature = SEEDStd_UniqueId();

        if( $this->kTable ) {
            $kRow = $this->oTable->PutRow( $this->kTable, 0, $raReq, $signature );  // row 0 means insert a new row
        }
        if( !$kRow )  $signature = NULL;

        return( array( $kRow, $signature ) );
    }

    function SendPipeRequest( $raPipeReq )
    /*************************************
       The local server sends the request to the remote server.
       $raPipeReq is array( kRow, signature ) obtained from CreatePipeRequest
     */
    {
        $ok = false;

        if( STD_isLocal ) {
            $host = "localhost";
            $page = SITEROOT_URL."../office/int/pipeServer.php";
            //echo "SendPipeRequest to ${host}${page}<br/>";        interferes with ajax output
        } else {
            $host = "office.seeds.ca";
            $page = "/int/pipeServer.php";
        }

        list( $ok, $sResponseHeader, $sResponseContent ) = SEEDStd_HttpRequest( $host, $page, $raPipeReq );

        return( array( $ok, $sResponseHeader, $sResponseContent ) );
    }


    function GetPipeRequest( $kRow, $signature )
    /*******************************************
        The remote server receives the row key and unique signature via unsecure means.
        Look up the row, verify the signature, erase the signature (so this only works once) and return the raVals.
        Or return NULL if fail.
     */
    {
        $raReq = NULL;
        if( $this->kTable && ($raRow = $this->oTable->GetRowByKey( $kRow )) ) {
            if( isset($raRow[$kRow]) && $raRow[$kRow]['k1'] == $signature ) {
                $raReq = $raRow[$kRow]['vals'];

                // Erase the signature (and the request too because it will be replaced by the response vals)
                $this->oTable->PutRow( $this->kTable, $kRow, array(), "" );
            }
        }
        return( $raReq );
    }

    function StorePipeResponse( $kRow, $raResp )
    /*******************************************
        The remote server has processed the request values.  It replaces them in the MetaTable row with its response values.
     */
    {
        return( $this->oTable->PutRow( $this->kTable, $kRow, $raResp ) );  // no k1 needed now because _key is known by both sides
    }

    function GetAndDeletePipeResponse( $kRow )
    /*****************************************
        The local server has received a completion reply from the remote server. It gets the response values from the MetaTable.
        This also deletes the MetaTable row.
     */
    {
        $raResp = NULL;

        if( $this->kTable && ($raRow = $this->oTable->GetRowByKey( $kRow )) ) {
            if( isset($raRow[$kRow]) ) {
                $raResp = $raRow[$kRow]['vals'];
            }
        }
        $this->oTable->DeleteRow( $kRow );

        return( $raResp );
    }
}

?>