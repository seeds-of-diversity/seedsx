<?php

include_once( SEEDCOMMON."sl/desc/garlic_defs.php" );
include_once( SEEDCOMMON."sl/sl_desc_db.php" );

include_once( "_sl_desc.php" );

function lettuceForm( $oSLDescDB, $kVI ){
$oF = new SLDescForm( $oSLDescDB, $kVI );
$oF->Update();

$f = $oF->Style();

$raLettuceFormCommon = array(
	array( 'cmd'=>'head', 'head_EN'=>"lettuce"),

    array( 'cmd'=>'section', 'title_EN'=>"Dates", 'title_FR'=>"Les dates" ),
    array(     'cmd'=>'q_d', 'k'=>'common_SoD_d__sowdate' ),
    array(     'cmd'=>'q_d', 'k'=>'common_SoD_d__lharvestdate' ),
    array(     'cmd'=>'q_d', 'k'=>'common_SoD_d__boltdate' ),
    array(     'cmd'=>'q_d', 'k'=>'common_SoD_d__flowerdate' ),
    array(     'cmd'=>'q_d', 'k'=>'common_SoD_d__seeddate' ),

);

$oF->SetDefs( SLDescDefsCommon::$raDefsCommon );      // this tells SLDescForm how to interpret the 'common' descriptors
$f .= $oF->DrawForm( $raLettuceFormCommon );  // this tells SLDescForm to draw a form using those common descriptors, as organized in the array above

$raLettuceForm = array(

	array( 'cmd'=>'section', 'title_EN'=>"Harvest", 'title_FR'=>"Harvest" ),
	array(     'cmd'=>'inst', 'inst_EN'=>"Please answer these questions when the lettuce is ready to harvest."),
    array(     'cmd'=>'q_m', 'k'=>'lettuce_GRIN_m__HEADTYPE' ),
    array(     'cmd'=>'q_m', 'k'=>'lettuce_GRIN_m__LEAFCOLOR' ),
	array(     'cmd'=>'q_m', 'k'=>'lettuce_GRIN_m__ANTHOCYAN' ),
	array(     'cmd'=>'q_f', 'k'=>'lettuce_GRIN_f__HEADDEPTH' ),
	array(     'cmd'=>'q_f', 'k'=>'lettuce_GRIN_f__HEADDIAM' ),
	array(     'cmd'=>'q_m', 'k'=>'lettuce_GRIN_m__HEADSOLID' ),
	array(     'cmd'=>'q_m', 'k'=>'lettuce_GRIN_m__LEAFCRISP' ),
	array(     'cmd'=>'q_f', 'k'=>'lettuce_SoD_f__LEAFDIMEN_L' ),
	array(     'cmd'=>'q_f', 'k'=>'lettuce_SoD_f__LEAFDIMEN_W' ),
	array(     'cmd'=>'q_m', 'k'=>'lettuce_GRIN_m__LEAFFOLD' ),
	array(     'cmd'=>'q_m_t', 'k'=>'lettuce_GRIN_m__LEAFSHAPE' ),

	array( 'cmd'=>'section', 'title_EN'=>"Flowers", 'title_FR'=>"Flowers" ),
	array(     'cmd'=>'inst', 'inst_EN'=>"Please answer these question if you allowed your lettuce to bolt and produce flowers."),
    array(     'cmd'=>'q_m', 'k'=>'lettuce_GRIN_m__FLOWERCOL' ),
	array(     'cmd'=>'q_f', 'k'=>'lettuce_GRIN_f__FLOWERDIAM' ),

	array( 'cmd'=>'section', 'title_EN'=>"Seeds", 'title_FR'=>"Seeds" ),
	array(     'cmd'=>'inst', 'inst_EN'=>"Please answer these question if you allowed your lettuce to mature and produce ripe seeds."),
    array(     'cmd'=>'q_f', 'k'=>'lettuce_GRIN_f__PLANTHGT' ),
	array(     'cmd'=>'q_m', 'k'=>'lettuce_GRIN_m__SEEDCOLOR' ),

);
$oF->SetDefs( SLDescDefsLettuce::$raDefsLettuce );  // this tells SLDescForm how to interpret the 'garlic' descriptors

$f .= $oF->DrawForm( $raLettuceForm );  // this tells SLDescForm to draw a form using those garlic descriptors, as organized in the array above

   //dw_sect( "Dates" );
return ($f);
}
?>
