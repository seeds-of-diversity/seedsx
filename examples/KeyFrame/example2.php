<?

echo "<H3>KeyFrame Example 2</H3>"
    ."<P>This example shows the join of the 'people' and 'pets' database tables.</P>";


include( "example.php" );    // includes KFRelation.php


$kfdb = new KeyFrameDB();
$kfdb->Connect( EXAMPLE_DB );


$kfrel = new KeyFrameRelation( $kfdb, $kfrel_def_peopleXpets, 0 );

echo show_people_pets( $kfrel );

echo "<HR/>";

?>
