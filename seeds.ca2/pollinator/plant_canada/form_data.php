<?php
$bee_resource_data = "";
$plant_type_data = "";
$location_data= "";
$season_data = "";
$sub_season_data = "";

include( "../plant_common/lib/fcinit.php" );

$bee_sql = "SELECT * FROM bee";
$plant_sql = "SELECT * FROM plant_type";
$location_sql = "SELECT * FROM location";
$season_sql = "SELECT * FROM season";
$sub_season_sql = "SELECT * FROM subseason";

$bee_result=mysqli_query($bee_sql) or die(mysqli_error());
$plant_result=mysqli_query($plant_sql) or die(mysqli_error());
$location_result=mysqli_query($location_sql) or die(mysqli_error());
$season_result=mysqli_query($season_sql) or die(mysqli_error());
$sub_season_result=mysqli_query($sub_season_sql) or die(mysqli_error());

while ( $row = mysqli_fetch_array($bee_result) ) {

}

while ( $row = mysqli_fetch_array($plant_result) ) {

}

while ( $row = mysqli_fetch_array($location_result) ) {

}

while ( $row = mysqli_fetch_array($season_result) ) {

}

while ( $row = mysqli_fetch_array($sub_season_result) ) {

}

?>