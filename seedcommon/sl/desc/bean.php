<?php
include_once( SEEDCOMMON."sl/desc/bean_defs.php" );
include_once( SEEDCOMMON."sl/desc/common_defs.php" );
include_once( SEEDCOMMON."sl/sl_desc_db.php" );

include_once( "_sl_desc.php" );

function beanForm( $oSLDescDB, $kVI ){
$oF = new SLDescForm( $oSLDescDB, $kVI );
$oF->Update();

$f = $oF->Style();
//TODO: TERMI_SHAPE needs a picture
//TODO: GRAI_COLOR should be in the next section (snap harvest)

$raBeanFormCommon = array(
	array( 'cmd'=>'head', 'head_EN'=>"bean"),

    array( 'cmd'=>'section', 'title_EN'=>"Dates", 'title_FR'=>"Les dates" ),
    array(     'cmd'=>'q_d', 'k'=>'common_SoD_d__sowdate' ),
    array(     'cmd'=>'q_d', 'k'=>'common_SoD_d__flowerdate' ),
    array(     'cmd'=>'q_d', 'k'=>'common_SoD_d__poddate' ),
    array(     'cmd'=>'q_d', 'k'=>'common_SoD_d__seeddate' ),
);
$oF->SetDefs( SLDescDefsCommon::$raDefsCommon );      // this tells SLDescForm how to interpret the 'common' descriptors
$f .= $oF->DrawForm( $raBeanFormCommon );  // this tells SLDescForm to draw a form using those common descriptors, as organized in the array above

$raBeanForm = array(
    array( 'cmd'=>'section', 'title_EN'=>"Seedling", 'title_FR'=>"Seedling" ),
    array(     'cmd'=>'q_m', 'k'=>'bean_NOR_m__PLAN_ANTHO' ),

    array( 'cmd'=>'section', 'title_EN'=>"Mid summer", 'title_FR'=>"Mid summer" ),
    array(     'cmd'=>'q_m', 'k'=>'bean_NOR_m__PLAN_GROWT' ),
    array(     'cmd'=>'q_m', 'k'=>'bean_NOR_m__PLANT_TYPE' ),
    array(     'cmd'=>'q_m', 'k'=>'bean_NOR_m__PLAN_CLIMB' ),
    array(     'cmd'=>'q_m', 'k'=>'bean_NOR_m__LEAF_COLOR' ),
    array(     'cmd'=>'q_m', 'k'=>'bean_NOR_m__LEAF_SIZE' ),
    array(     'cmd'=>'q_m', 'k'=>'bean_NOR_m__LEAF_RUGOS' ),

    array( 'cmd'=>'section', 'title_EN'=>"Flowers", 'title_FR'=>"Les fleurs" ),
    array(     'cmd'=>'q_m', 'k'=>'bean_NOR_m__PLAN_FLOWE' ),
    array(     'cmd'=>'q_m', 'k'=>'bean_NOR_m__FLOW_LOCAT' ),
    array(     'cmd'=>'q_m', 'k'=>'bean_NOR_m__FLOW_BRACT' ),
    array(     'cmd'=>'inst', 'inst_EN'=>"Bean flowers have two kinds of petals:  standards curled at the bottom and wings spread at the top."),
    array(     'cmd'=>'q_m', 'k'=>'bean_NOR_m__FLOW_STAND' ),
    array(     'cmd'=>'q_m', 'k'=>'bean_NOR_m__FLOW_WINGS' ),

    array( 'cmd'=>'section', 'title_EN'=>"2-3 weeks after flowering", 'title_FR'=>"2-3 weeks after flowering" ),
    array(     'cmd'=>'q_f', 'k'=>'bean_NOR_f__PLANT_CM' ),
    array(     'cmd'=>'q_m', 'k'=>'bean_NOR_m__TERMI_SHAPE' ),
    array(     'cmd'=>'q_m', 'k'=>'bean_NOR_m__TERMI_SIZE' ),
    array(     'cmd'=>'q_m', 'k'=>'bean_NOR_m__TERMI_APEX' ),
    array(     'cmd'=>'q_m', 'k'=>'bean_NOR_m__GRAI_COLOR' ),

    array( 'cmd'=>'section', 'title_EN'=>"Snap beans, harvest", 'title_FR'=>"Snap beans, harvest" ),
    array(     'cmd'=>'inst', 'inst_EN'=>"Please answer the following questions for ripe pods of varieties that are suitable for fresh (snap) use."),
    array(     'cmd'=>'q_f', 'k'=>'bean_SoD_f__podlength' ),
    array(     'cmd'=>'q_m_t', 'k'=>'bean_NOR_m__POD_SECTIO' ),
    array(     'cmd'=>'inst', 'inst_EN'=>"Most bean pods are one uniform colour; some have a main background colour with extra markings such as stripes or spots."),
    array(     'cmd'=>'q_m', 'k'=>'bean_NOR_m__POD_GROUND' ),
    array(     'cmd'=>'q_m', 'k'=>'bean_NOR_m__POD_INTENS' ),
    array(     'cmd'=>'q_m', 'k'=>'bean_NOR_m__POD_PIGMEN' ),
    array(     'cmd'=>'q_m', 'k'=>'bean_NOR_m__POD_PIGCOL' ),
    array(     'cmd'=>'q_m', 'k'=>'bean_NOR_m__POD_STRING' ),
    array(     'cmd'=>'q_m', 'k'=>'bean_NOR_m__POD_CURVAT' ),
    array(     'cmd'=>'q_m_t', 'k'=>'bean_NOR_m__POD_SHACUR' ),
    array(     'cmd'=>'q_m', 'k'=>'bean_NOR_m__POD_SHATIP' ),
    array(     'cmd'=>'q_m', 'k'=>'bean_NOR_m__POD_LEBEAK' ),
    array(     'cmd'=>'q_m_t', 'k'=>'bean_NOR_m__POD_CURBEA' ),
    array(     'cmd'=>'q_m', 'k'=>'bean_NOR_m__POD_PROMIN' ),
    array(     'cmd'=>'q_m', 'k'=>'bean_NOR_m__POD_TEXTUR' ),

    array( 'cmd'=>'section', 'title_EN'=>"Dry pods", 'title_FR'=>"Dry pods" ),
    array(     'cmd'=>'inst', 'inst_EN'=>"Please answer these questions after the pods have dried naturally on the plants."),
    array(     'cmd'=>'q_m', 'k'=>'bean_NOR_m__POD_PARDRY' ),
    array(     'cmd'=>'q_m', 'k'=>'bean_NOR_m__POD_CONSTR' ),

	array( 'cmd'=>'section', 'title_EN'=>"Seeds", 'title_FR'=>"Seeds"),
	array(     'cmd'=>'q_m', 'k'=>'bean_NOR_m__GRAIN_SIZE' ),
	array(     'cmd'=>'q_m_t', 'k'=>'bean_NOR_m__SEED_SHAPE' ),
	array(     'cmd'=>'q_m', 'k'=>'bean_SoD_m__SEED_SHAPE2' ),
	array(     'cmd'=>'inst', 'inst_EN'=>"Most bean seeds are one uniform colour, but some are multicoloured."),
    array(     'cmd'=>'q_m', 'k'=>'bean_SoD_m__seedcolours' ),
	array(     'cmd'=>'q_m', 'k'=>'bean_NOR_m__GRAIN_MAIN' ),
	array(     'cmd'=>'q_m', 'k'=>'bean_NOR_m__GRAI_MASEC' ),
	array(     'cmd'=>'q_m', 'k'=>'bean_NOR_m__GRAI_DISTR' ),
	array(     'cmd'=>'q_m', 'k'=>'bean_NOR_m__SEED_VEINS' ),
	array(     'cmd'=>'q_m', 'k'=>'bean_NOR_m__SEED_HILAR' ),


);

$oF->SetDefs( SLDescDefsBean::$raDefsBean );  // this tells SLDescForm how to interpret the 'garlic' descriptors

$f .= $oF->DrawForm( $raBeanForm );  // this tells SLDescForm to draw a form using those garlic descriptors, as organized in the array above

   //dw_sect( "Dates" );
return ($f);
}

?>
