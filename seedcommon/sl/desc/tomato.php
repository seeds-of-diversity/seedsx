<?
include_once( SEEDCOMMON."sl/desc/garlic_defs.php" );
include_once( SEEDCOMMON."sl/sl_desc_db.php" );

include_once( "_sl_desc.php" );

function tomatoForm( $oSLDescDB, $kVI ){
$oF = new SLDescForm( $oSLDescDB, $kVI );
$oF->Update();

$f = $oF->Style();

$raTomatoFormCommon = array(
	array( 'cmd'=>'head', 'head_EN'=>"tomato"),

    array( 'cmd'=>'section', 'title_EN'=>"Dates", 'title_FR'=>"Les dates" ),
    array(     'cmd'=>'q_d', 'k'=>'common_SoD_d__sowdate' ),
    array(     'cmd'=>'q_d', 'k'=>'common_SoD_d__flowerdate' ),
    array(     'cmd'=>'q_d', 'k'=>'common_SoD_d__harvestdate' ),

);

$oF->SetDefs( SLDescDefsCommon::$raDefsCommon );      // this tells SLDescForm how to interpret the 'common' descriptors
$f .= $oF->DrawForm( $raTomatoFormCommon );  // this tells SLDescForm to draw a form using those common descriptors, as organized in the array above

$raTomatoForm = array(


	array( 'cmd'=>'section', 'title_EN'=>"Mid-Season", 'title_FR'=>"Mid-Season" ),
	array(     'cmd'=>'inst', 'inst_EN'=>"For the next question: <br/> determinate (about 2-3 feet tall, produces one main crop of fruit then mostly stops growing, little if any side growth, usually don't need staking)" .
	"<br/> semi-determinate (about 3-5 feet tall, some slow side growth, grow well on short stakes)" .
	"<br/> indeterminate (continuously grows long vines with new flower clusters until frost, widely-spaced branches and lots of side shoots, needs staking)" ),
	array(     'cmd'=>'q_m', 'k'=>'tomato_SoD_m__planthabit' ),
	array(     'cmd'=>'q_m', 'k'=>'tomato_SoD_m__stempubescence' ),
	array(     'cmd'=>'q_m', 'k'=>'tomato_SoD_m__foliagedensity' ),
	array(     'cmd'=>'q_m', 'k'=>'tomato_SoD_m__leafattitude' ),
	array(     'cmd'=>'q_m_t', 'k'=>'tomato_SoD_m__leaftype' ),
	array(     'cmd'=>'q_m', 'k'=>'tomato_SoD_m__flowercolour' ),
	array(     'cmd'=>'q_m', 'k'=>'tomato_SoD_m__fruitcolourunripe' ),

	array( 'cmd'=>'section', 'title_EN'=>"Late-Season", 'title_FR'=>"Late-Season" ),
	array(     'cmd'=>'q_f', 'k'=>'tomato_SoD_f__vinelength' ),
	array(     'cmd'=>'q_f', 'k'=>'tomato_SoD_f__internodelength' ),

	array( 'cmd'=>'section', 'title_EN'=>"Fruit", 'title_FR'=>"Fruit" ),
	array(     'cmd'=>'inst', 'inst_EN'=>"Answer these questions when the fruit is fully ripe.  Please observe several typical fruit and average your observations." ),
	array(     'cmd'=>'q_m_t', 'k'=>'tomato_SoD_m__fruitshape' ),
	array(     'cmd'=>'q_m_t', 'k'=>'tomato_SoD_m__fruitshapecrosssection' ),
	array(     'cmd'=>'q_m', 'k'=>'tomato_SoD_m__fruitsize' ),
	array(     'cmd'=>'q_m', 'k'=>'tomato_SoD_m__fruitdetachment' ),
	array(     'cmd'=>'q_m', 'k'=>'tomato_SoD_m__fruitcolourexterior' ),
	array(     'cmd'=>'q_m', 'k'=>'tomato_SoD_m__fruitcolourinterior' ),
	array(     'cmd'=>'q_m', 'k'=>'tomato_GRIN_m__GELCOLOR' ),
	array(     'cmd'=>'q_m', 'k'=>'tomato_SoD_m__fruitfirmness' ),
	array(     'cmd'=>'q_m', 'k'=>'tomato_SoD_m__fruitpubescence' ),
);
$oF->SetDefs( SLDescDefsTomato::$raDefsTomato );  // this tells SLDescForm how to interpret the 'garlic' descriptors

$f .= $oF->DrawForm( $raTomatoForm );  // this tells SLDescForm to draw a form using those garlic descriptors, as organized in the array above

   //dw_sect( "Dates" );
return ($f);
}

?>
