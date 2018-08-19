<?php
$bCanada = true;    // in admin mode we always show all provinces

include_once( "view.php" );

if( $_SESSION['user'] != "admin" ) { header("Location: index.php?cmd=login"); }

if( in_array( @$_REQUEST['cmd'], array('edit','delete')) ) {
    include_once( "update.php" );
    Update();
}

$result_string = Search();

$oView = new View( $kfdb );

$kShowPlant = @$_REQUEST['showplant'];

$raTmplVars['sSearchPanel'] = ViewSearchPanel();
$raTmplVars['sNewPlantForm'] = $kShowPlant ? "" : ViewPlantForm( 0 );
$raTmplVars['sPlantDetails'] = $kShowPlant ? ViewPlantDetails( $kShowPlant ) : "";

echo $oTmpl->ExpandTmpl( "admin", $raTmplVars );

?>
