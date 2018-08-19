<?

echo "<H3>KeyFrame Example 3</H3>"
    ."<P>This example shows a table of editable values from the database, using KeyFrameUIForm.</P>"

."<P>No, all this has been reimplemented with KeyFrameUIFormTable.  See example4.php</P>";


include( "example.php" );    // includes KFRelation.php
include( KEYFRAME_DIR."KFUIForm.php" );


$kfdb = new KeyFrameDB();
$kfdb->Connect( EXAMPLE_DB );
//$kfdb->KFDB_SetDebug(2);      // uncomment to see what's going on with the database
//print_r($_REQUEST);           // uncomment to see what's being submitted from the form


$kfuiFormDef = array( "name" => array( "type"=>"text" ),
                      "age"  => array( "type"=>"text" ) );



$kfrel = new KeyFrameRelation( $kfdb, $kfrel_def_people, 0 );

$oForm = new KeyFrameUIForm( $kfrel, "A", array( "formdef" => array( 'name' => array( 'type'=>'text' ),
                                                                     'age'  => array( 'type'=>'text') ) ) );
$oForm->Update();


/* Draw the form with current values
 */
echo "<FORM method='post'>"
    .$oForm->FormTableStart()
    .$oForm->FormTableHeader();
if( ($kfr = $kfrel->CreateRecordCursor()) ) {
    $oForm->SetKFR( $kfr );
    echo $oForm->FormTableStart();
    while( $kfr->CursorFetch() ) {
        echo $oForm->FormTableRow();
    }
    // draw a blank row at the bottom
    $kfrBlank = $kfrel->CreateRecord();
    $oForm->SetKFR( $kfrBlank );
    echo $oForm->FormTableRow();

    echo $oForm->FormTableEnd();
}

echo "<INPUT type='Submit'></FORM>";

?>
