<?
include_once( SEEDCOMMON."sl/desc/garlic_defs.php" );
include_once( SEEDCOMMON."sl/sl_desc_db.php" );

include_once( "_sl_desc.php" );

function onionForm( $oSLDescDB, $kVI ){
$oF = new SLDescForm( $oSLDescDB, $kVI );
$oF->Update();

$f = $oF->Style();

$raOnionFormCommon = array(
	array( 'cmd'=>'head', 'head_EN'=>"onion"),

    array( 'cmd'=>'section', 'title_EN'=>"Dates", 'title_FR'=>"Les dates" ),
    array(     'cmd'=>'q_d', 'k'=>'common_SoD_d__sowdate' ),
    array(     'cmd'=>'q_d', 'k'=>'common_SoD_d__diestartdate' ),
    array(     'cmd'=>'q_d', 'k'=>'common_SoD_d__harvestdate' ),


);

$oF->SetDefs( SLDescDefsCommon::$raDefsCommon );      // this tells SLDescForm how to interpret the 'common' descriptors
$f .= $oF->DrawForm( $raOnionFormCommon );  // this tells SLDescForm to draw a form using those common descriptors, as organized in the array above

$raOnionForm = array(

	array( 'cmd'=>'section', 'title_EN'=>"Mid-Season", 'title_FR'=>"Mid-Season" ),
	array(     'cmd'=>'q_m', 'k'=>'onion_SoD_m__oniontype' ),
	array(     'cmd'=>'q_m', 'k'=>'onion_SoD_m__leafcolour' ),
	array(     'cmd'=>'q_m', 'k'=>'onion_SoD_m__leafattitude' ),
	array(     'cmd'=>'q_m', 'k'=>'onion_GRIN_m__LEAFSTRUCT' ),

	array( 'cmd'=>'section', 'title_EN'=>"Late-Season", 'title_FR'=>"Late-Season" ),
	array(     'cmd'=>'q_f', 'k'=>'onion_GRIN_f__PLANTHEIGHT' ),
	array(     'cmd'=>'q_f', 'k'=>'onion_SoD_f__leafwidth' ),

	array( 'cmd'=>'section', 'title_EN'=>"Flowers", 'title_FR'=>"Flowers" ),
	array(     'cmd'=>'inst', 'inst_EN'=>"This section is for onions that produce true flowers.  Note that some onions are biennial, producing flowers only in their second year.  Also, some produce topsets which are not flowers - make sure that you know the difference between a flower and a topset." ),
	array(     'cmd'=>'q_b', 'k'=>'onion_SoD_b__flowerability' ),
	array(     'cmd'=>'q_m', 'k'=>'onion_SoD_m__flowercolour' ),
	array(     'cmd'=>'q_m', 'k'=>'onion_GRIN_m__ANTHERCOL' ),

	array( 'cmd'=>'section', 'title_EN'=>"Bulbs", 'title_FR'=>"Bulbs" ),
	array(     'cmd'=>'q_f', 'k'=>'onion_GRIN_f__BULBDIAM' ),
	array(     'cmd'=>'q_m', 'k'=>'onion_GRIN_m__BULBSHAPE' ),
	array(     'cmd'=>'q_m', 'k'=>'onion_SoD_m__bulbskincolour' ),
	array(     'cmd'=>'q_m', 'k'=>'onion_SoD_m__bulbfleshcolour' ),
	array(     'cmd'=>'q_m', 'k'=>'onion_SoD_m__bulbhearts' ),

);
$oF->SetDefs( SLDescDefsOnion::$raDefsOnion );  // this tells SLDescForm how to interpret the 'garlic' descriptors

$f .= $oF->DrawForm( $raOnionForm );  // this tells SLDescForm to draw a form using those garlic descriptors, as organized in the array above

   //dw_sect( "Dates" );
return ($f);
}


?>
