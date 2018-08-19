<?
include_once( SEEDCOMMON."sl/desc/garlic_defs.php" );
include_once( SEEDCOMMON."sl/sl_desc_db.php" );

include_once( "_sl_desc.php" );

function peaForm( $oSLDescDB, $kVI ){
$oF = new SLDescForm( $oSLDescDB, $kVI );
$oF->Update();

$f = $oF->Style();


$raPeaFormCommon = array(
	array( 'cmd'=>'head', 'head_EN'=>"pea"),

    array( 'cmd'=>'section', 'title_EN'=>"Dates", 'title_FR'=>"Les dates" ),
    array(     'cmd'=>'q_d', 'k'=>'common_SoD_d__sowdate' ),
    array(     'cmd'=>'q_d', 'k'=>'common_SoD_d__flowerdate' ),
    array(     'cmd'=>'q_d', 'k'=>'common_SoD_d__poddate' ),
    array(     'cmd'=>'q_d', 'k'=>'common_SoD_d__seeddate' ),


);

$oF->SetDefs( SLDescDefsCommon::$raDefsCommon );      // this tells SLDescForm how to interpret the 'common' descriptors
$f .= $oF->DrawForm( $raPeaFormCommon );  // this tells SLDescForm to draw a form using those common descriptors, as organized in the array above

$raPeaForm = array(
	/*
	array( 'cmd'=>'section', 'title_EN'=>"Sample", 'title_FR'=>"Sample" ),
	array(     'cmd'=>'inst', 'inst_EN'=>"Sample" ),
	array(     'cmd'=>'q_', 'k'=>'' ),
	*/
	array( 'cmd'=>'section', 'title_EN'=>"Seedling", 'title_FR'=>"Seedling" ),
	array(     'cmd'=>'q_m', 'k'=>'pea_GRIN_m__COTYLCOLOR' ),

	array( 'cmd'=>'section', 'title_EN'=>"Flower", 'title_FR'=>"Flower" ),
	array(     'cmd'=>'q_m', 'k'=>'pea_GRIN_m__FLOWERCOL' ),
	array(     'cmd'=>'q_i', 'k'=>'pea_GRIN_i__FLOWPEDUNC' ),

	array( 'cmd'=>'section', 'title_EN'=>"Harvest", 'title_FR'=>"Harvest" ),
	array(     'cmd'=>'q_f', 'k'=>'pea_SoD_f__LEAFLENGTH' ),
	array(     'cmd'=>'q_f', 'k'=>'pea_SoD_f__LEAFWIDTH' ),
	array(     'cmd'=>'q_f', 'k'=>'pea_GRIN_f__HEIGHT' ),
	array(     'cmd'=>'q_f', 'k'=>'pea_GRIN_f__INTERNODE' ),
	array(     'cmd'=>'q_i', 'k'=>'pea_GRIN_i__NODES' ),
	array(     'cmd'=>'q_f', 'k'=>'pea_GRIN_f__STEMDIAM' ),

	array( 'cmd'=>'section', 'title_EN'=>"Pods", 'title_FR'=>"Pods" ),
	array(     'cmd'=>'q_m', 'k'=>'pea_GRIN_m__PODTYPE' ),
	array(     'cmd'=>'q_m', 'k'=>'pea_GRIN_m__PODCOLOR' ),
	array(     'cmd'=>'q_m', 'k'=>'pea_GRIN_m__PODSHAPE' ),
	array(     'cmd'=>'q_m', 'k'=>'pea_GRIN_m__PODAPEX' ),
	array(     'cmd'=>'q_f', 'k'=>'pea_GRIN_f__PODLENGTH' ),
	array(     'cmd'=>'q_f', 'k'=>'pea_GRIN_f__PODWIDTH' ),

	array( 'cmd'=>'section', 'title_EN'=>"Seeds", 'title_FR'=>"Seeds" ),
	array(     'cmd'=>'q_m', 'k'=>'pea_GRIN_m__HILUMCOLOR' ),
	array(     'cmd'=>'inst', 'inst_EN'=>"Most pea seeds are uniform green/white in colour, but some have other colours or markings." ),
	array(     'cmd'=>'q_m', 'k'=>'pea_GRIN_m__SDCOATCOL' ),
	array(     'cmd'=>'q_m', 'k'=>'pea_GRIN_m__SDPATTERN' ),
	array(     'cmd'=>'q_m', 'k'=>'pea_GRIN_m__SDPATCOLOR' ),
	array(     'cmd'=>'q_m', 'k'=>'pea_GRIN_m__SEEDSURF' ),
	array(     'cmd'=>'q_i', 'k'=>'pea_GRIN_i__SEEDSPOD' ),
	array(     'cmd'=>'q_m', 'k'=>'pea_GRIN_m__STEMFASC' ),

);
$oF->SetDefs( SLDescDefsPea::$raDefsPea );  // this tells SLDescForm how to interpret the 'garlic' descriptors

$f .= $oF->DrawForm( $raPeaForm );  // this tells SLDescForm to draw a form using those garlic descriptors, as organized in the array above

   //dw_sect( "Dates" );
return ($f);
}


?>
