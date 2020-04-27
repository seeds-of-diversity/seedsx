<?php

/* Floral Calendar
 *
 * Initialization
 */
define( "SITEROOT", "../../" );
include_once( SITEROOT."site.php" );
include_once( SEEDCOMMON."siteStart.php" );
include_once( SEEDAPP."SEEDApp.php" );
/* Currently, all shared files are under plant_common.
 * That would be a problem for browser-addressable files (.css,.js,img) if a floral calendar were at the root of a domain.
 * If you need to solve this, copy plant_common/c somewhere visible to the browser and change the location below.
 */
define( "PLANT_COMMON_C", SITEROOT."pollinator/plant_common/c/" );
define( "BOOTSTRAP", PLANT_COMMON_C."bootstrap/" );


$oApp = SEEDConfig_NewAppConsole_LoginNotRequired( ['db'=>'floralcal'] );

$kfdb = $oApp->kfdb;
$sess = $oApp->sess;


$raTmplParms = array( 'fTemplates' => array( PLANT_COMMON_C."floralcalTmpl.html" ),
                      'raResolvers' => array( array( 'fn'=>'ResolveTag', 'raParms'=>array() ) ) );
$oTmpl = SEEDTemplateMaker( $raTmplParms );

$raTmplVars = array( 'sHead' => ViewHead( "Honey Plants", array( 'bLightbox'=>true ) ),
                     'isLogin' => @$_SESSION['user']=="admin",
);
