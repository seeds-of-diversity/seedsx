<?php

/*
 * SEEDJoomlaModule
 *
 * Copyright 2011-2013 Seeds of Diversity Canada
 *
 * Process Joomla modules
 */

include_once( SEEDCOMMON."sl/sl_desc_report.php" );
include_once( SEEDCOMMON."sl/sl_sources_common.php" );


class SEEDJoomlaModule
{
    var $kfdb;
    var $sOutput = "";

    function __construct( KeyFrameDB $kfdb )
    {
        $this->kfdb = $kfdb;
    }

    function DoModule( $params )
    {
        $pLang = $params->get('modlang');  if( $pLang != 'FR' ) $pLang = 'EN';
        $pType = $params->get('modtype');

        switch( $pType ) {
            case "Seed Sources":   $this->sOutput = $this->doSeedSources();  break;
            case "Seed Desc":      $this->sOutput = $this->doSeedDesc();     break;
            case "Seed Buyers":    $this->sOutput = $this->doSeedBuyers();   break;
            default:
                break;
        }

        if( $pType == 'DocRepWebsite' ) {
            include_once( SEEDCOMMON."doc/docWebsite.php" );

            $ra = array( "lang"       => $pLang,
                         "docid_root" => $params->get('generic_a'),    // web/book/section
                         "docid_home" => $params->get('generic_b'),    // web/book/section/pg1
                         "docid_extroots" => array( $params->get('generic_c') ),  // use ; to separate multiple ext_roots
//             "vars" => array( "dr_template" => "guelph/template/template01" ),  // this can be overridden by var "dr_template" in any page or folder
//             "docid_template" => "guelph/template/template01",
                         "bDirHierarchy" => true
            );

            $oD = new DocWebsite( $ra );
            $this->sOutput = $oD->Go2();  // or $oD->Init(); $this->sOutput = $oD->DrawPage();
        }

        if( $pType == 'CSCI_Companies' ) {


            $this->sOutput = "";
        }

        if( $pType == 'CSCI_Seeds' ) {


            $this->sOutput = "";
        }
    }

    function Output() { return( $this->sOutput ); }


    function doSeedSources()
    {
        $o = new SLSourcesUI( $this->kfdb, array( 'linkToMe'=>$this->getLinkToMe() ) );

        return( $o->DrawDrillDown() );
    }

    function doSeedDesc()
    {
        $o = new SLDescReportUI( $this->kfdb, array( 'linkToMe'=>$this->getLinkToMe() ) );

        return( $o->DrawDrillDown() );
    }

    function doSeedBuyers()
    {

    }

    function getLinkToMe()
    {
        $linkToMe = $_SERVER['PHP_SELF'];
        if( $_SERVER['QUERY_STRING'] ) {
            $linkToMe .= "?".$_SERVER['QUERY_STRING']."&";
        } else {
            $linkToMe .= "?";
        }
        return( $linkToMe );
    }
}

?>
