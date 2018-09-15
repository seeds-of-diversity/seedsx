<?php

/* CSCI Archive tool
 *
 * Copyright (c) 2018 Seeds of Diversity Canada
 */

if( !defined("SITEROOT") )  define("SITEROOT", "../../");
include_once( SITEROOT."site.php" );
include_once( SEEDCORE."console/console02.php" );
include_once( SEEDLIB."sl/sldb.php" );


$oApp = new SEEDAppConsole(
                array_merge( $SEEDKFDB1,
                             array( 'sessPermsRequired' => array(),
                                    'logdir' => SITE_LOG_ROOT )
                           )
);

$oApp->kfdb->SetDebug(1);

$oSrc = new SLDBSources( $oApp, array( 'logdir'=>SITE_LOG_ROOT ) );
$raCompanies = $oSrc->GetList( "SRC",     "", array("iStatus"=>-1, "sSortCol"=>'SRC.name_en' ) );
$raSpecies   = $oSrc->GetList( "SRCCVxS", "SRCCV.fk_sl_sources>='3'", array("sSortCol"=>'S.name_en' ) );



$oApp->kfdb->SetDebug(0);


$s = "";

$s .= "<div class='container' style='border:1px solid #aaa;border-radius:5px;'>"
     ."<div class='row' style='padding:10px 0px'>"
         ."<div class='col-md-3'>"
             ."<div><select name='company'>"
                 ."<option value='0'>-- Select a company --</option>"
                 .SEEDCore_ArrayExpandRows( $raCompanies, "<option value='[[_key]]'>[[name_en]]</option>" )
                 ."</select>"
             ."</div>"
             ."<div style='font-size:8pt'>Choose one or both of these before searching</div>"
             ."<div><select name='species'>"
                 ."<option value='0'>-- Select a species --</option>"
                 .SEEDCore_ArrayExpandRows( $raSpecies, "<option value='[[_key]]'>[[S_name_en]]</option>" )
                 ."</select>"
             ."</div>"
         ."</div>"
     ."</div></div>";



echo Console02Static::HTMLPage( $s, "", 'EN', array('sCharset'=>'cp1252') );


$o = new SLDBCollection( $oApp, array( 'logdir'=>SITE_LOG_ROOT ) );
$ra = $o->GetList( 'IxGxAxPxS', "I._key BETWEEN 1000 and 1010" );
echo SEEDCore_ArrayExpandRows( $ra, "<p>[[inv_number]] [[P_name]]</p>" );

echo "<hr/";

$ra = $o->GetList( 'IxA_P', "I._key BETWEEN 1000 and 1010" );
echo SEEDCore_ArrayExpandRows( $ra, "<p>[[_key]] [[P_name]]</p>" );

echo "<hr/";

$o = new SLDBSources( $oApp, array( 'logdir'=>SITE_LOG_ROOT ) );
$ra = $o->GetList( 'SRCCVxSRC_P', "SRCCV._key BETWEEN 14750 and 14760" );
echo SEEDCore_ArrayExpandRows( $ra, "<p>[[_key]] [[osp]] [[ocv]] [[SRC_name_en]]</p>" );

?>