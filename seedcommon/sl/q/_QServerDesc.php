<?php

/* _QServerDesc
 *
 * Copyright 2016 Seeds of Diversity Canada
 *
 * Serve queries about crop descriptors
 */

include_once( SEEDCOMMON."sl/sl_desc_db.php" );
include_once( "Q.php" );

class QServerDesc
{
    private $oQ;
    private $oSLDBMaster;

    function __construct( Q $oQ )
    {
        $this->oQ = $oQ;
        //$this->oDescDB     = new SL_DescDB( $oQ->kfdb, $oQ->sess->GetUID() );  will need this for queries about crop records
        $this->oDescDB_Cfg = new SLDescDB_Cfg( $oQ->kfdb, $oQ->sess->GetUID() );
    }

    function Cmd( $cmd, $parms )
    {
        $rQ = $this->oQ->GetEmptyRQ();

        /* Updaters
         *
         * cmds containing -- require write access (at a minimum - cmd might have other more stringent requirements too)
         */
        if( strpos( $cmd, "--" ) !== false ) {
            if( !$this->oQ->sess->TestPerm( 'SLDesc', 'W' ) ) {
                $rQ['sErr'] = "Command requires SL Description write permission";
                goto done;
            }

            switch( $cmd ) {
                case 'descCfg--updateTag':
                    var_dump( $parms );
                    break;

                case 'descCfg--updateMultiple':
                    var_dump( $parms );
                    break;
            }

            goto done;
        }


        /* bOk = true if any read is successful
         */
        $raOut = array();
        switch( $cmd ) {
            case 'descCfgTags':         $raOut = $this->getTags( $parms );          break;
            case 'descCfgMultiples':    $raOut = $this->getMultiples( $parms );     break;
        }
        if( $raOut ) {
            $rQ['bOk'] = true;
            $rQ['raOut'] = $raOut;
        }

        done:
        return( $rQ );
    }

    private function getTags( $parms )
    /*********************************
        Get a list of descriptor tags

        raParms:
            sp       : filter by species prefix {sp}_

            outFmt   : keys         : return array( key => array( _key, tag, data )
                       tagKeys      : return array( tag => array( _key, tag, data )
                       '' (default) : return array( array( _key, tag, data )
     */
    {
        $raOut = array();
        $raParms = array();

        if( ($sp = @$parms['sp']) ) {
            $raParms['sp'] = $sp;
        }
        $ra = $this->oDescDB_Cfg->GetListCfgTags( $raParms );

        foreach( $ra as $tag => $raTag ) {
            $a = array( 'tag' => $tag,
                        'label_en' => $this->oQ->QCharset($raTag['label_en']),
                        'label_fr' => $this->oQ->QCharset($raTag['label_fr']),
                        'q_en'     => $this->oQ->QCharset($raTag['q_en']),
                        'q_fr'     => $this->oQ->QCharset($raTag['q_fr']),
                      );

            switch( @$raParms['outFmt'] ) {
                case 'keys':     $raOut[$raTag['_key']] = $a;    break;
                case 'tagkeys':  $raOut[$raTag['tag']] = $a;     break;
                default:         $raOut[] = $a;                  break;
            }
        }

        return( $raOut );
    }


    private function getMultiples( $parms )
    /**************************************
        Get a list of multiple choices for descriptor tags

        raParms:
            sp       : filter by species prefix {sp}_

            outFmt   : keys         : return array( key => array( _key, tag, data )
                       '' (default) : return array( array( _key, tag, data )
     */
    {
        $raOut = array();
        $raParms = array();

        if( ($sp = @$parms['sp']) ) {
            $raParms['sp'] = $sp;
        }
        $ra = $this->oDescDB_Cfg->GetListCfgMultiples( $raParms );

        foreach( $ra as $tag => $raTag ) {
            $a = array( 'tag'   => $raTag['tag'],
                        'v'     => $this->oQ->QCharset($raTag['v']),
                        'l_en'  => $this->oQ->QCharset($raTag['l_en']),
                        'l_fr'  => $this->oQ->QCharset($raTag['l_fr']),
                      );

            switch( @$raParms['outFmt'] ) {
                case 'keys':     $raOut[$raTag['_key']] = $a;    break;
                default:         $raOut[] = $a;                  break;
            }
        }

        return( $raOut );
    }
}

?>
