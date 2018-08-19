<?php
include_once( SEEDCOMMON."sl/desc/garlic_defs.php" );
include_once( SEEDCOMMON."sl/sl_desc_db.php" );

include_once( "_sl_desc.php" );

function garlicForm( $oSLDescDB, $kVI ){
$oF = new SLDescForm( $oSLDescDB, $kVI );
$oF->Update();

$f = $oF->Style();


$raGarlicForm = array(
	array( 'cmd'=>'head', 'head_EN'=>"garlic"),

    array( 'cmd'=>'section', 'title_EN'=>"Dates", 'title_FR'=>"Dates" ),
    array(     'cmd'=>'q_d', 'k'=>'garlic_SoD_d__sowdate' ),
    array(     'cmd'=>'q_d', 'k'=>'garlic_SoD_d__harvestdate' ),

    array( 'cmd'=>'section', 'title_EN'=>"When you planted the cloves", 'title_FR'=>"When you planted the cloves" ),
	array(     'cmd'=>'q_f', 'k'=>'garlic_SoD_f__sowdistance' ),
	array(     'cmd'=>'q_b', 'k'=>'garlic_SoD_b__mulch' ),
    array(     'cmd'=>'q_f', 'k'=>'garlic_SoD_f__mulchthickness' ),
    array(     'cmd'=>'q_s', 'k'=>'garlic_SoD_s__mulchmaterial' ),

    array( 'cmd'=>'section', 'title_EN'=>"Cultivation", 'title_FR'=>"Cultivation" ),
    array(     'cmd'=>'q_b', 'k'=>'garlic_SoD_b__irrigated' ),
    array(     'cmd'=>'q_b', 'k'=>'garlic_SoD_b__fertilized' ),
    array(     'cmd'=>'q_s', 'k'=>'garlic_SoD_s__fertilizerandamount' ),
    array(     'cmd'=>'q_m', 'k'=>'garlic_SoD_m__weedcontrol' ),

    array( 'cmd'=>'section', 'title_EN'=>"Mid-Season", 'title_FR'=>"Mid-Season" ),
    array(     'cmd'=>'q_m', 'k'=>'garlic_SoD_m__leafcolour' ),
    array(     'cmd'=>'q_f', 'k'=>'garlic_SoD_f__leaflength' ),
    array(     'cmd'=>'q_f', 'k'=>'garlic_SoD_f__leafwidth' ),
    array(     'cmd'=>'q_m', 'k'=>'garlic_SoD_m__foliage' ),
    array(     'cmd'=>'q_m', 'k'=>'garlic_GRIN_m__PLANTVIGOR' ),

    array( 'cmd'=>'section', 'title_EN'=>"Scapes", 'title_FR'=>"Scapes" ),
    array(     'cmd'=>'q_m', 'k'=>'garlic_SoD_m__scapeproduced' ),
    array(     'cmd'=>'q_m', 'k'=>'garlic_SoD_m__scaperemoved' ),
    array(     'cmd'=>'q_m', 'k'=>'garlic_SoD_m__scapestemshape' ),
    array(     'cmd'=>'q_m', 'k'=>'garlic_SoD_m__scapebulbils' ),

    array( 'cmd'=>'section', 'title_EN'=>"Harvest", 'title_FR'=>"Harvest" ),
    array(     'cmd'=>'q_f', 'k'=>'garlic_GRIN_f__PLANTHEIGHT' ),
    array(     'cmd'=>'q_i', 'k'=>'garlic_SoD_i__bulbharvest' ),
    array(     'cmd'=>'q_f', 'k'=>'garlic_GRIN_f__BULBDIAM' ),
    array(     'cmd'=>'q_m_t', 'k'=>'garlic_GRIN_m__BULBSHAPE' ),
    array(     'cmd'=>'q_m', 'k'=>'garlic_SoD_m__bulbskincolour' ),
    array(     'cmd'=>'q_m', 'k'=>'garlic_SoD_m__cloveskincolour' ),
    array(     'cmd'=>'q_m', 'k'=>'garlic_SoD_m__clovesperbulb' ),
    array(     'cmd'=>'q_i', 'k'=>'garlic_SoD_i__clovesperbulb' ),
    array(     'cmd'=>'q_m', 'k'=>'garlic_SoD_m__clovearrangement' ),
    array(     'cmd'=>'q_b', 'k'=>'garlic_SoD_b__bulbpeel' ),
    array(     'cmd'=>'q_b', 'k'=>'garlic_SoD_b__clovepeel' ),
);

$oF->SetDefs( SLDescDefsGarlic::$raDefsGarlic );  // this tells SLDescForm how to interpret the 'garlic' descriptors

$f .= $oF->DrawForm( $raGarlicForm );  // this tells SLDescForm to draw a form using those garlic descriptors, as organized in the array above

   //dw_sect( "Dates" );
return ($f);
}

?>
