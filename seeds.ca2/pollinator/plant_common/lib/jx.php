<?php

/* AJAX server for Floral Calendars
 */
include_once("view.php");

$raJX = array();

switch( @$_REQUEST['jx'] ) {
    case 'plantdetails':
        include_once("view.php");
        $raJX['sOut'] = utf8_encode( ViewPlantDetails( $_REQUEST["thisID"] ) );
        $raJX['bOk'] = true;

        echo json_encode( $raJX );
        break;

    case 'plantedit':
        ob_start();
        JXEdit();
        $raJX['sOut'] = utf8_encode( ob_get_clean() );
        $raJX['bOk'] = true;

        echo json_encode( $raJX );
        break;

/*
    case 'showplant':
        include_once("view.php");
        echo ViewPlantDetails( $_GET["thisID"] );
        break;

    case 'edit':
        JXEdit();
        break;
*/

    default:
        break;
}

function JXEdit()
{
    global $kfdb;

    $image_habitus = "";
    $image_flower = "";
    $image_fruit = "";
    $image_leaves = "";

    if( ($thisID = SEEDSafeGPC_GetInt('thisID')) ) {
        $returnTable = "";

        $row = $kfdb->QueryRA( "SELECT * FROM main WHERE ID='$thisID'" );

        $sDir = "../plant_common/img/{$row['ID']}/";
        if( $row["image_habitus"] )  $image_habitus = $sDir.$row["image_habitus"];
        if( $row["image_flower"] )   $image_flower  = $sDir.$row["image_flower"];
        if( $row["image_fruit"] )    $image_fruit   = $sDir.$row["image_fruit"];
        if( $row["image_leaves"] )   $image_leaves  = $sDir.$row["image_leaves"];

        $description = $row["info_text"];
        $sc_name = $row["Scientific_Name"];
        $com_name = $row["Common_Name"];

        $bee_code = rtrim($row["Bee_Resource"],",");
        $plant_code = rtrim($row["Plant_Type"],",");
        $location_code = rtrim($row["Location"],",");
        $season_code = rtrim($row["Season"],",");

        $seasons = explode(",", $season_code);
    }

    $s = "<form id='editPlant' action='edit.php' method='post' enctype='multipart/form-data'>"
        .ViewPlantForm( SEEDSafeGPC_GetInt('thisID') )
        ."</form>";

    echo $s;
}

function seasonCheck( $SID, $arr2 )
{
    foreach( $arr2 as $season ) {
        if( $SID == trim($season) )  return( ' checked' );
    }
    return( "" );
}

?>