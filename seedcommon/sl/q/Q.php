<?php

include_once( SEEDLIB."q/Q.php" );

class Qold
{
    public $oApp;
    public $kfdb;
    public $sess;
    public $raParms;
    public $bUTF8 = false;

    function __construct( KeyFrameDB $kfdb, SEEDSessionAccount $sess,
                          SEEDAppSessionAccount $oApp = null,       // can be null for now if cmd doesn't use oApp
                          $raParms = array() )
    {
        $this->oApp = $oApp;
        $this->kfdb = $kfdb;
        $this->sess = $sess;
        $this->raParms = $raParms;
        $this->bUTF8 = intval(@$raParms['bUTF8']);
    }

    function Cmd( $cmd, $parms )
    {
        $rQ = $this->GetEmptyRQ();

        // cmds containing ! are insecure for ajax access: use them via your own instance of a QServer* object
        if( strpos($cmd,'!') !== false ) {
            $rQ['sErr'] = "cmd $cmd not available at this access point";
            goto done;
        }

        /* Send cmd through SEEDLIB/Q.
         * If it is not handled there, handle it below.
         */
        $oQnew = new Q( $this->oApp, [] );
        $rQnew = $oQnew->Cmd( $cmd, $parms );
        if( $rQnew['bHandled'] ) {
            $rQ = $rQnew;
            goto done;
        }

        if( substr( $cmd, 0, 4 ) == 'desc' ) {
            include_once( "_QServerDesc.php" );

            $o = new QServerDesc( $this );
            $rQ = $o->Cmd( $cmd, $parms );
        }

        if( substr( $cmd, 0, 7 ) == 'rosetta' ) {
            include_once( "_QServerPCV.php" );

            $o = new QServerPCV( $this );
            $rQ = $o->Cmd( $cmd, $parms );
        }

        if( substr( $cmd, 0, 3 ) == 'src' ) {
            include_once( "_QServerSourceCV.php" );
            $o = new QServerSourceCV_Old( $this, array( 'bUTF8' => true ) );
            $rQ = $o->Cmd( $cmd, $parms );
        }

        if( substr( $cmd, 0, 10 ) == 'collection' ) {
            include_once( "_QServerCollection.php" );
            global $config_KFDB;
            $oApp = new SEEDAppSessionAccount( $config_KFDB['seeds1']
                            + array( 'sessPermsRequired' => array(),
                                     'logdir' => SITE_LOG_ROOT,
                                     'lang' => 'EN' )
            );
            $o = new QServerCollection( $this, $oApp, array( ) );
            $rQ = $o->Cmd( $cmd, $parms );
        }

        if( substr( $cmd, 0, 10 ) == 'collreport' ) {
            include_once( "_QServerCollectionReport.php" );
            $o = new QServerCollectionReport( $this, array( ) );
            $rQ = $o->Cmd( $cmd, $parms );
        }

        if( SEEDCore_StartsWith( $cmd, 'mbr' ) ) {
            include_once( SEEDLIB."mbr/QServerMbr.php" );
            $o = new QServerMbr( $this->oApp, array() );
            $rQ = $o->Cmd( $cmd, $parms );
        }

        done:
        return( $rQ );
    }

    function QCharset( $s )
    /**********************
        If the input is cp1252, the output will be the charset defined by $this->bUTF8
     */
    {
        return( $this->bUTF8 ? utf8_encode( $s ) : $s );
    }

    function GetEmptyRQ()
    /********************
     */
    {
        return( array( 'bOk'=>false, 'sOut'=>"", 'sErr'=>"", 'sLog'=>"", 'raOut'=>array(), 'raMeta'=>array() ) );
    }
}


class QCursor
{
    public $kfrc;
    private $fnGetNextRow;    // function to translate kfrc->values to the GetNextRow values
    private $raParms;

    function __construct( KFRecord $kfrc, $fnGetNextRow, $raParms )
    {
        $this->kfrc = $kfrc;
        $this->fnGetNextRow = $fnGetNextRow;
        $this->raParms = $raParms;
    }

    function GetNextRow()
    {
        $raOut = null;
        if( $this->kfrc->CursorFetch() ) {
            if( $this->fnGetNextRow ) {
                $raOut = call_user_func( $this->fnGetNextRow, $this, $this->raParms );
            } else {
                $raOut = $this->kfrc->ValuesRA();
            }
        }
        return( $raOut );
    }
}
