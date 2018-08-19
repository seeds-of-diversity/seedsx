<?

echo "<H3>KeyFrame Example 1</H3>"
    ."<P>This example shows the contents of the 'people' database table, fetches Alice's record, increases her age, "
    ."and shows the updated contents of the table.</P>";


include( "example.php" );    // includes KFRelation.php


$kfdb = new KeyFrameDB();
$kfdb->Connect( EXAMPLE_DB );


$kfrel = new KeyFrameRelation( $kfdb, $kfrel_def_people, 0 );


/* Show the initial values in the "people" table
 */
echo "<P>Here are the initial values</P>";
echo show_people( $kfrel );

echo "<HR/>";


/* Get Alice's record, increment her age, update the database
 */
if( ($kfr = $kfrel->GetRecordFromDB( "name='Alice'" )) ) {
    $kfr->SetValue( 'age', $kfr->Value('age') + 1 );
    if( $kfr->PutDBRow() ) {
        echo "<P>Increased Alice's age to ".$kfr->Value('age').".</P>";
    } else {
        echo "<P>Error updating record</P>";
    }
} else {
    echo "<P style='red'>Alice not found</P>";
}


echo "<HR/>";


/* Show the updated values in the "people" table
 */
echo "<P>Here are the updated values</P>";
echo show_people( $kfrel );


?>
