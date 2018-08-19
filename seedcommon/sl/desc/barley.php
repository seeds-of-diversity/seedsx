<?
include_once( "_sl_desc.php" );

$oF = new SLDescForm();

$s = $oF->Style();

$raSLDescDefsBarley = array(
/*
''   => array( 'l_EN' => "",
									 	 'q_EN' => "",
									 	 'm' => array(
									 			0=>"don't know",
									 			) ),

*/

);

$raBarleyFormCommon = array(
	array( 'cmd'=>'head', 'head_EN'=>"barley"),
/*
    array( 'cmd'=>'section', 'title_EN'=>"Dates", 'title_FR'=>"Les dates" ),
    array(     'cmd'=>'q_d', 'k'=>'common_SoD_d__sowdate' ),
    array(     'cmd'=>'q_d', 'k'=>'common_SoD_d__flowerdate' ),
    array(     'cmd'=>'q_d', 'k'=>'common_SoD_d__harvestdate' ),
*/
);

$oF->SetDefs( $raSLDescDefsCommon );      // this tells SLDescForm how to interpret the 'common' descriptors
echo $oF->DrawForm( $raBarleyFormCommon );  // this tells SLDescForm to draw a form using those common descriptors, as organized in the array above

$raBarleyForm = array(
	/*
	array( 'cmd'=>'section', 'title_EN'=>"Sample", 'title_FR'=>"Sample" ),
	array(     'cmd'=>'inst', 'inst_EN'=>"Sample" ),
	array(     'cmd'=>'q_', 'k'=>'' ),
	*/

);
$oF->SetDefs( $raSLDescDefsBarley );
echo $oF->DrawForm( $raBarleyForm );

?>