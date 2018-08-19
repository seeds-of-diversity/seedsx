<?

include_once( SEEDCOMMON."sl/desc/garlic_defs.php" );
include_once( SEEDCOMMON."sl/sl_desc_db.php" );

include_once( "_sl_desc.php" );

function squashForm( $oSLDescDB, $kVI ){
$oF = new SLDescForm( $oSLDescDB, $kVI );
$oF->Update();

$f = $oF->Style();


$raSquashFormCommon = array(
	array( 'cmd'=>'head', 'head_EN'=>"squash"),
    array( 'cmd'=>'section', 'title_EN'=>"Dates", 'title_FR'=>"Les dates" ),
    array(     'cmd'=>'q_d', 'k'=>'common_SoD_d__sowdate' ),
    array(     'cmd'=>'q_d', 'k'=>'common_SoD_d__flowerdatemale' ),
    array(     'cmd'=>'q_d', 'k'=>'common_SoD_d__flowerdatefemale' ),
    array(     'cmd'=>'q_d', 'k'=>'common_SoD_d__harvestdate' ),


);

$oF->SetDefs( SLDescDefsCommon::$raDefsCommon );      // this tells SLDescForm how to interpret the 'common' descriptors
$f .= $oF->DrawForm( $raSquashFormCommon );  // this tells SLDescForm to draw a form using those common descriptors, as organized in the array above

$raSquashForm = array(
	array( 'cmd'=>'section', 'title_EN'=>"Mid-Season", 'title_FR'=>"Mid-Season" ),
	array(     'cmd'=>'q_m', 'k'=>'squash_GRIN_m__PLANTHABIT' ),
	array(     'cmd'=>'q_m', 'k'=>'squash_GRIN_m__VIGOR' ),

	array( 'cmd'=>'section', 'title_EN'=>"Leaves", 'title_FR'=>"Leaves" ),
	array(     'cmd'=>'q_f', 'k'=>'squash_SoD_f__leaflength' ),
	array(     'cmd'=>'q_f', 'k'=>'squash_SoD_f__leafwidth' ),
	array(     'cmd'=>'q_s', 'k'=>'squash_SoD_s__leafshape' ),

	array( 'cmd'=>'section', 'title_EN'=>"Flowers", 'title_FR'=>"Flowers" ),
	array(     'cmd'=>'q_s', 'k'=>'squash_SoD_s__flowercolour' ),
	array(     'cmd'=>'q_f', 'k'=>'squash_SoD_f__flowerlength' ),
	array(     'cmd'=>'q_f', 'k'=>'squash_SoD_f__flowerwidth' ),
	array(     'cmd'=>'q_s', 'k'=>'squash_SoD_s__anthercolour' ),

	array( 'cmd'=>'section', 'title_EN'=>"Fruit", 'title_FR'=>"Fruit" ),
	array(     'cmd'=>'q_b', 'k'=>'squash_GRIN_b__UNIFORMITY' ),
	array(     'cmd'=>'q_m', 'k'=>'squash_GRIN_m__FRUITCOLOR' ),
	array(     'cmd'=>'q_m', 'k'=>'squash_GRIN_m__FRUITSPOT' ),
	array(     'cmd'=>'q_m', 'k'=>'squash_GRIN_m__FLESHCOLOR' ),
	array(     'cmd'=>'q_f', 'k'=>'squash_GRIN_f__FLESHDEPTH' ),
	array(     'cmd'=>'q_f', 'k'=>'squash_GRIN_f__FRUITLEN' ),
	array(     'cmd'=>'q_f', 'k'=>'squash_GRIN_f__FRUITDIAM' ),
	array(     'cmd'=>'q_m', 'k'=>'squash_GRIN_m__FRUITRIB' ),
	array(     'cmd'=>'q_m', 'k'=>'squash_GRIN_m__FRUITSET' ),

	array( 'cmd'=>'section', 'title_EN'=>"Seeds", 'title_FR'=>"Seeds" ),
	array(     'cmd'=>'q_s', 'k'=>'squash_SoD_s__seedcolour' ),
	array(     'cmd'=>'q_m', 'k'=>'squash_SoD_m__seednumber' ),
	array(     'cmd'=>'q_f', 'k'=>'squash_SoD_f__seedlength' ),

);

$oF->SetDefs( SLDescDefsSquash::$raDefsSquash );  // this tells SLDescForm how to interpret the 'garlic' descriptors

$f .= $oF->DrawForm( $raSquashForm );  // this tells SLDescForm to draw a form using those garlic descriptors, as organized in the array above

   //dw_sect( "Dates" );
return ($f);
}

?>
