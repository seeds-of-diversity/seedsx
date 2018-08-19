<?
include_once( SEEDCOMMON."sl/desc/apple_defs.php" );
include_once( SEEDCOMMON."sl/sl_desc_db.php" );

include_once( "_sl_desc.php" );

function appleForm( $oSLDescDB, $kVI ){
$oF = new SLDescForm( $oSLDescDB, $kVI );
$oF->Update();

$f = $oF->Style();

$raAppleForm = array(
    array( 'cmd'=>'section', 'title_EN'=>"General", 'title_FR'=>"General" ),
    array(     'cmd'=>'q_f', 'k'=>'apple_SoD_i__age' ),
    array(     'cmd'=>'q_m', 'k'=>'apple_SoD_m__condition' ),

    array( 'cmd'=>'section', 'title_EN'=>"Spring", 'title_FR'=>"Spring"),
    array(     'cmd'=>'q_m', 'k'=>'apple_SoD_m__hardy' ),
    array(     'cmd'=>'q_m', 'k'=>'apple_SoD_m__habit' ),
    array(     'cmd'=>'q_m', 'k'=>'apple_SoD_m__vigour' ),

    array( 'cmd'=>'section', 'title_EN'=>"Flowers (late spring, early summer)", 'title_FR'=>"Flowers (late spring, early summer)"),
	array(     'cmd'=>'q_m', 'k'=>'apple_SoD_m__flowerseason' ),
	array(     'cmd'=>'q_i', 'k'=>'apple_SoD_i__flowerduration' ),
	array(     'cmd'=>'q_m', 'k'=>'apple_SoD_m__flowerregularity' ),
	array(     'cmd'=>'q_m', 'k'=>'apple_SoD_m__flowersecondary' ),
	array(     'cmd'=>'q_m', 'k'=>'apple_SoD_m__selfcompatible' ),
	array(     'cmd'=>'q_m', 'k'=>'apple_GRIN_m__ANTHERCOL' ),
	array(     'cmd'=>'q_m', 'k'=>'apple_GRIN_m__CARPELARR' ),
	array(     'cmd'=>'q_m', 'k'=>'apple_GRIN_m__FLWRCOLOR' ),
	array(     'cmd'=>'q_f', 'k'=>'apple_GRIN_f__FLOWERSIZE' ),
	array(     'cmd'=>'q_i', 'k'=>'apple_GRIN_i__FLWRINFLOR' ),


	array( 'cmd'=>'section', 'title_EN'=>"Leaves (Mid summer)", 'title_FR'=>"Leaves (Mid summer)"),
	array(     'cmd'=>'q_m', 'k'=>'apple_GRIN_m__LFHAIRSURF' ),
	array(     'cmd'=>'q_f', 'k'=>'apple_GRIN_f__LEAFLENGTH' ),
	//img echo "<DIV class='d_a' align=center><IMG src='../img/apple/apple LEAFLENGTH.gif' height=120></DIV>";
	array(     'cmd'=>'q_m_t', 'k'=>'apple_GRIN_m__LEAFSHAPE' ),
	array(     'cmd'=>'q_m', 'k'=>'apple_GRIN_m__LEAFLOBING' ),


	array( 'cmd'=>'section', 'title_EN'=>"Fruit (Harvest season)", 'title_FR'=>"Fruit (Harvest season)"),
	array(     'cmd'=>'q_m_t', 'k'=>'apple_SoD_m__fruitshape' ),
	array(     'cmd'=>'q_m_t', 'k'=>'apple_GRIN_m__TOPFRTSHAPE' ),
	array(     'cmd'=>'q_m', 'k'=>'apple_GRIN_m__FRUITTENAC' ),
	array(     'cmd'=>'q_m', 'k'=>'apple_SoD_m__fruitearliness' ),
	array(     'cmd'=>'q_m', 'k'=>'apple_SoD_m__fruitsize' ),
	array(     'cmd'=>'inst', 'inst_EN'=>"Apple skin colour is often made up of two layers: an underlying \"ground colour\" that appears first, and an \"over colour\" that usually appears when the fruit ripens. ".
										 "<BR/>e.g. Golden Delicious has an even yellow ground colour, sometimes with a blush of pink over colour. ".
										 "<BR/>e.g. Gala has an underlying yellow-orange ground colour, often with stripes of red over colour. ".
										 "<BR/>e.g. McIntosh has an underlying green ground colour with a large splash of red over colour."),
    array(     'cmd'=>'q_m', 'k'=>'apple_SoD_m__skingroundcolour' ),
	array(     'cmd'=>'q_m', 'k'=>'apple_SoD_m__skinovercolour' ),
	array(     'cmd'=>'q_m', 'k'=>'apple_SoD_m__skinoverpattern' ),
	array(     'cmd'=>'q_m', 'k'=>'apple_GRIN_m__FRUITBLOOM' ),
	array(     'cmd'=>'q_m', 'k'=>'apple_SoD_m__bruising' ),
	array(     'cmd'=>'q_i', 'k'=>'apple_GRIN_i__CARPELNUM' ),
	array(     'cmd'=>'q_m', 'k'=>'apple_GRIN_m__FRTFLSHCOL' ),
	array(     'cmd'=>'q_m', 'k'=>'apple_GRIN_m__FRTFLSHFRM' ),
	array(     'cmd'=>'q_m', 'k'=>'apple_SoD_m__texture' ),
	array(     'cmd'=>'q_m', 'k'=>'apple_GRIN_m__FRTFLSHFLA' ),
	array(     'cmd'=>'q_m', 'k'=>'apple_GRIN_m__SEEDCOLOR' ),
	array(     'cmd'=>'q_m', 'k'=>'apple_GRIN_m__SEEDSHAPE' ),


);

$oF->SetDefs( SLDescDefsApple::$raDefsApple );  // this tells SLDescForm how to interpret the 'garlic' descriptors

$f .= $oF->DrawForm( $raAppleForm );  // this tells SLDescForm to draw a form using those garlic descriptors, as organized in the array above

   //dw_sect( "Dates" );
return ($f);
}

?>
