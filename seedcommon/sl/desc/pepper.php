<?
include_once( SEEDCOMMON."sl/desc/garlic_defs.php" );
include_once( SEEDCOMMON."sl/sl_desc_db.php" );

include_once( "_sl_desc.php" );

function pepperForm( $oSLDescDB, $kVI ){
$oF = new SLDescForm( $oSLDescDB, $kVI );
$oF->Update();

$f = $oF->Style();



$raPepperFormCommon = array(
	array( 'cmd'=>'head', 'head_EN'=>"pepper"),

    array( 'cmd'=>'section', 'title_EN'=>"Dates", 'title_FR'=>"Les dates" ),
    array(     'cmd'=>'q_d', 'k'=>'common_SoD_d__sowdate' ),
    array(     'cmd'=>'q_d', 'k'=>'common_SoD_d__flowerdate' ),
    array(     'cmd'=>'q_d', 'k'=>'common_SoD_d__harvestdate' ),

);

$oF->SetDefs( SLDescDefsCommon::$raDefsCommon );      // this tells SLDescForm how to interpret the 'common' descriptors
$f .= $oF->DrawForm( $raPepperFormCommon );  // this tells SLDescForm to draw a form using those common descriptors, as organized in the array above

$raPepperForm = array(

	array( 'cmd'=>'section', 'title_EN'=>"General", 'title_FR'=>"Genral" ),
	array(     'cmd'=>'q_m', 'k'=>'pepper_GRIN_m__COMMCAT' ),
	array(     'cmd'=>'q_m', 'k'=>'pepper_GRIN_m__PUNGENCY2' ),

	array( 'cmd'=>'section', 'title_EN'=>"Early-Season", 'title_FR'=>"Early-Season" ),
	array(     'cmd'=>'q_m', 'k'=>'pepper_SoD_m__stemcolour' ),
	array(     'cmd'=>'q_m', 'k'=>'pepper_SoD_m__stemshape' ),
	array(     'cmd'=>'q_m', 'k'=>'pepper_SoD_m__pubescence' ),
	array(     'cmd'=>'q_m', 'k'=>'pepper_SoD_m__branchhabit' ),

	array( 'cmd'=>'section', 'title_EN'=>"Leaves", 'title_FR'=>"Leaves" ),
	array(     'cmd'=>'q_m', 'k'=>'pepper_SoD_m__leafcolour' ),
	array(     'cmd'=>'q_m_t', 'k'=>'pepper_SoD_m__leafshape' ),
	array(     'cmd'=>'q_m', 'k'=>'pepper_GRIN_m__LEAFTEXT' ),
	array(     'cmd'=>'q_f', 'k'=>'pepper_SoD_f__leaflength' ),
	array(     'cmd'=>'q_f', 'k'=>'pepper_SoD_f__leafwidth' ),

	array( 'cmd'=>'section', 'title_EN'=>"Flowers", 'title_FR'=>"Flowers" ),
	array(     'cmd'=>'q_m', 'k'=>'pepper_SoD_m__flowerposition' ),
	array(     'cmd'=>'q_m', 'k'=>'pepper_SoD_m__flowercolour' ),
	array(     'cmd'=>'q_m', 'k'=>'pepper_SoD_m__anthercolour' ),

	array( 'cmd'=>'section', 'title_EN'=>"Fruit", 'title_FR'=>"Fruit" ),
	array(     'cmd'=>'q_m', 'k'=>'pepper_SoD_m__fruitcolourunripe' ),
	array(     'cmd'=>'q_m', 'k'=>'pepper_SoD_m__fruitcolourripe' ),
	array(     'cmd'=>'q_m', 'k'=>'pepper_GRIN_m__FRUITPOS' ),
	array(     'cmd'=>'q_m_t', 'k'=>'pepper_SoD_m__fruitshape' ),
	array(     'cmd'=>'q_f', 'k'=>'pepper_GRIN_f__FRUITLNGTH' ),

	array( 'cmd'=>'section', 'title_EN'=>"Late-Season", 'title_FR'=>"Late-Season" ),
	array(     'cmd'=>'q_f', 'k'=>'pepper_SoD_f__plantheight' ),
	array(     'cmd'=>'q_m', 'k'=>'pepper_GRIN_m__STEMNUM' ),
	array(     'cmd'=>'q_m', 'k'=>'pepper_SoD_m__planthabit' ),

	array( 'cmd'=>'section', 'title_EN'=>"Seeds", 'title_FR'=>"Seeds" ),
	array(     'cmd'=>'q_m', 'k'=>'pepper_SoD_m__seedcolour' ),
	array(     'cmd'=>'q_m', 'k'=>'pepper_SoD_m__seedsurface' ),

);

$oF->SetDefs( SLDescDefsPepper::$raDefsPepper );  // this tells SLDescForm how to interpret the 'garlic' descriptors

$f .= $oF->DrawForm( $raPepperForm );  // this tells SLDescForm to draw a form using those garlic descriptors, as organized in the array above

   //dw_sect( "Dates" );
return ($f);
}


?>
