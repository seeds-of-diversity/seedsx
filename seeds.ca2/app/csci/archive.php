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