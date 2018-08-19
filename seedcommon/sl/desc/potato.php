<?
include_once( SEEDCOMMON."sl/desc/garlic_defs.php" );
include_once( SEEDCOMMON."sl/sl_desc_db.php" );

include_once( "_sl_desc.php" );

function potatoForm( $oSLDescDB, $kVI ){
$oF = new SLDescForm( $oSLDescDB, $kVI );
$oF->Update();

$f = $oF->Style();


$raPotatoFormCommon = array(
	array( 'cmd'=>'head', 'head_EN'=>"potato"),

    array( 'cmd'=>'section', 'title_EN'=>"Dates", 'title_FR'=>"Les dates" ),
    array(     'cmd'=>'q_d', 'k'=>'common_SoD_d__sowdate' ),
    array(     'cmd'=>'q_d', 'k'=>'common_SoD_d__diestartdate' ),
    array(     'cmd'=>'q_d', 'k'=>'common_SoD_d__dieenddate' ),


);

$oF->SetDefs( SLDescDefsCommon::$raDefsCommon );      // this tells SLDescForm how to interpret the 'common' descriptors
$f .= $oF->DrawForm( $raPotatoFormCommon );  // this tells SLDescForm to draw a form using those common descriptors, as organized in the array above

$raPotatoForm = array(

	array( 'cmd'=>'section', 'title_EN'=>"Mid-Season", 'title_FR'=>"Mid-Season" ),
	array(     'cmd'=>'q_m', 'k'=>'potato_SoD_m__foliagematurity' ),
	array(     'cmd'=>'q_m', 'k'=>'potato_SoD_m__planthabit' ),
	array(     'cmd'=>'q_m', 'k'=>'potato_SoD_m__flowerfrequency' ),
	array(     'cmd'=>'q_s', 'k'=>'potato_SoD_s__flowercolour' ),
	array(     'cmd'=>'q_m', 'k'=>'potato_SoD_m__berrynumber' ),
	array(     'cmd'=>'q_m', 'k'=>'potato_GRIN_m__VIGOR' ),
	array(     'cmd'=>'q_s', 'k'=>'potato_SoD_s__leafcolour' ),

	array( 'cmd'=>'section', 'title_EN'=>"Tubers", 'title_FR'=>"Tubers" ),
	array(     'cmd'=>'q_m', 'k'=>'potato_SoD_m__tubergreening' ),
	array(     'cmd'=>'q_s', 'k'=>'potato_SoD_s__tubershape' ),
	array(     'cmd'=>'q_s', 'k'=>'potato_SoD_s__tuberskincolour' ),
	array(     'cmd'=>'q_m', 'k'=>'potato_SoD_m__skintexture' ),
	array(     'cmd'=>'q_s', 'k'=>'potato_SoD_s__tuberfleshcolour' ),
	array(     'cmd'=>'q_s', 'k'=>'potato_SoD_s__tubereyecolour' ),
	array(     'cmd'=>'q_m', 'k'=>'potato_SoD_m__eyedepth' ),
	array(     'cmd'=>'q_m', 'k'=>'potato_SoD_m__tubernumber' ),
	array(     'cmd'=>'q_m', 'k'=>'potato_SoD_m__tubersize' ),
	array(     'cmd'=>'q_m', 'k'=>'potato_SoD_m__hollowheart' ),
	array(     'cmd'=>'q_m', 'k'=>'potato_SoD_m__resistdamageexternal' ),
	array(     'cmd'=>'q_m', 'k'=>'potato_SoD_m__resistdamageinternal' ),
	array(     'cmd'=>'q_m', 'k'=>'potato_SoD_m__storageability' ),
	array(     'cmd'=>'q_m', 'k'=>'potato_SoD_m__drought' ),
	array(     'cmd'=>'q_m', 'k'=>'potato_SoD_m__frost' ),

);

$oF->SetDefs( SLDescDefsPotato::$raDefsPotato );  // this tells SLDescForm how to interpret the 'garlic' descriptors

$f .= $oF->DrawForm( $raPotatoForm );  // this tells SLDescForm to draw a form using those garlic descriptors, as organized in the array above

   //dw_sect( "Dates" );
return ($f);
}

?>
