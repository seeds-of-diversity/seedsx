<?php

echo "<H3>KeyFrame Example 4</H3>"
    ."<P>This example shows a table of editable values from the database, using SEEDFormTable / KeyFrameUIFormTable.</P>";


include( "example.php" );
include( KEYFRAME_DIR."KFUIFormTable.php" );
//include( KEYFRAME_DIR."KFUIFormUpdate.php" );


$kfdb = new KeyFrameDB();
$kfdb->Connect( EXAMPLE_DB );
//$kfdb->KFDB_SetDebug(2);      // uncomment to see what's going on with the database
//print_r($_REQUEST);           // uncomment to see what's being submitted from the form

$kfrelDef = array( "Tables" => array( array( "Table" => 'keyframe_example_people',
                                             "Fields" => "Auto" ) ) );

$kfuiFormDef = array( "name" => array( "type"=>"text" ),
                      "age"  => array( "type"=>"text" ) );

$kfrel = new KeyFrameRelation( $kfdb, $kfrelDef, 0 );


/* Do updates
 */
//$oFormParms = new KeyFrameUIFormParms( "A" );
//$raUpdates = $oFormParms->Deserialize( $_REQUEST, "A", true );
//KFUIFormUpdate( $kfrel, $raUpdates, NULL );


// Though the forms are identical, A gets updated first then B.
// It does the right thing if you only change one form at a time.
// Also does the right thing if you change both forms, though you have to know what the right thing is.
$oFormA = new KeyFrameUIForm( $kfrel, "A" );
$oFormA->Update();
$oFormB = new KeyFrameUIForm( $kfrel, "B" );
$oFormB->Update();

// Use oFormA
echo "<p>This implements the form with KFUIForm and SEEDFormTable.</p>";
$oFT = new KeyFrameUIFormTable( $oFormA );
echo "<FORM method='post'>"
    .$oFT->Start( $kfuiFormDef )
    .$oFT->Header();
$kfr = $kfrel->CreateRecordCursor();
while( $kfr->CursorFetch() ) {
    echo $oFT->RowKFR( $kfr );
}
$kfr = $kfrel->CreateRecord();      // add a blank record at the end for inserting new rows
echo $oFT->RowKFR( $kfr );
echo $oFT->End();
echo "<br/><INPUT type='Submit'></FORM>";


echo "<hr/>";


// Use oFormB
echo "<p>Here is the same form but using KeyFrameUIFormTable::DrawTable().</p>";
$oFT = new KeyFrameUIFormTable( $oFormB );
echo "<FORM method='post'>"
    .$oFT->DrawTable( $kfuiFormDef, "", array() )
    ."<br/><INPUT type='Submit'></FORM>";

?>
