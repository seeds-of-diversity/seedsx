<?php

/* DocRepH2o
 *
 * Copyright (c) 2013-2015 Seeds of Diversity Canada
 *
 * Derivation of H2o that retrieves template files from the DocRep
 */

include_once( STDINC."os/h2o/h2o.php" );

class DocRepH2o_Loader {
    private $oDocRepH2o;

    function __construct( $oDocRepH2o ) {
        $this->oDocRepH2o = $oDocRepH2o;
    }

    function read( $file )
    {
        $s = "";

        if( ($kDoc = $this->oDocRepH2o->oDocRepDB->GetDocFromName( $file )) ) {
            $s = $this->oDocRepH2o->oDocRepDB->GetDocAsStr( $kDoc, $this->oDocRepH2o->sFlag );
        }
        return( $this->runtime->parse($s) );
    }

    function read_cache( $file )  { return $this->read($file); }
    function setOptions()         {}
}


class DocRepH2o
{
    public $oDocRepDB;
    public $sFlag;

    function __construct( $oDocRepDB, $sFlag )
    {
        $this->oDocRepDB = $oDocRepDB;
        $this->sFlag = $sFlag;
    }

    function Render( $sTemplate )
    /****************************
        Creates an H2o parser and parses the template.
        The loader is for the parser to retrieve sub-templates.
     */
    {
        $oLoader = new DocRepH2o_Loader( $this );
        $instance = new H2o( null, array('loader' => $oLoader) );
        $instance->nodelist = $instance->parse($sTemplate);
        $s = $instance->render();
        return( $s );
    }

    function RenderDocKey( $kDoc )
    /*****************************
     */
    {
        $sTemplate = $this->oDocRepDB->GetDocAsStr( $kDoc, $this->sFlag );
        return( $this->Render( $sTemplate ) );
    }
}

?>