<?
/* KeyFrame Examples
 *
 * This file defines constants and functions for use by the other example scripts.
 * Follow the instructions below to configure for your system.
 */


// 1) run the example.sql script on your database to create example tables

// 2) Change this if your KeyFrame files (e.g. KFDB.php) are somewhere else
if(1) { // for this installation
    define( "SITEROOT", "../../seeds.ca2/" );
    include_once( SITEROOT."site.php" );
    define( "KEYFRAME_DIR", STDINC."KeyFrame/" );
    include_once( KEYFRAME_DIR."KFRelation.php" );
    define( "KFDB_HOST",     SiteKFDB_HOST );
    define( "KFDB_USERID",   SiteKFDB_USERID );   // your database user
    define( "KFDB_PASSWORD", SiteKFDB_PASSWORD ); // your database password
    define( "EXAMPLE_DB", "seeds" );              // the database where you created the example tables
} else {
    define( "KEYFRAME_DIR", "../" );
    include( KEYFRAME_DIR."KFRelation.php" );  // change this if your KeyFrame files are somewhere else
}

// 3) Change these as necessary so you can login to your database
if( !defined("KFDB_HOST") ) {
    define( "KFDB_HOST",     "localhost" );
    define( "KFDB_USERID",   "myUser" );        // your database user
    define( "KFDB_PASSWORD", "myPassword" );    // your database password
    define( "EXAMPLE_DB",    "kfexample" );     // the database where you created the example tables
}



/* Define the KeyFrameRelation for the People table
 */
$kfrel_def_people = array( "Tables" => array( array( "Table" => 'keyframe_example_people',
                                                     "Fields" => array( array( "col"=>"name", "type"=>"S"),
                                                                        array( "col"=>"age",  "type"=>"I") ) ) ) );

/* Define the KeyFrameRelation for a join of People and Pets
 *
 *    The join is made automatically because the pets table has a column named "fk_*" where * is the name of
 *    another table in the relation.
 *    The alias 'pets' allows conditions on the pets table to be made using column names like 'pets.name',
 *    removing ambiguity about what alias KeyFrame will use.
 */
$kfrel_def_peopleXpets = array( "Tables" => array( array( "Table" => 'keyframe_example_people',
                                                          "Type"  => 'Base',
                                                          "Fields" => array( array( "col"=>"name", "type"=>"S"),
                                                                             array( "col"=>"age",  "type"=>"I") ) ),
                                                   array( "Table" => 'keyframe_example_pets',
                                                          "Alias" => 'pets',
                                                          "Fields" => array( array( "col"=>"fk_keyframe_example_people", "type"=>"K"),
                                                                             array( "col"=>"name", "type"=>"S"),
                                                                             array( "col"=>"age",  "type"=>"I") ) ) ) );


function show_people( $kfrel )
{
    $s = "";
    if( ($kfr = $kfrel->CreateRecordCursor()) ) {
        $s .= "<TABLE>";
        while( $kfr->CursorFetch() ) {
            $s .= "<TR><TD>".$kfr->Value('name')."</TD><TD>".$kfr->Value('age')."</TD></TR>";
        }
        $s .= "</TABLE>";
    }
    return( $s );
}

function show_people_pets( $kfrel )
{
    $s = "";
    if( ($kfr = $kfrel->CreateRecordCursor()) ) {
        $s .= "<TABLE cellspacing='10'><TR><TH>person name</TH><TH>person age</TH><TH>pet name</TH><TH>pet age</TH></TR>";
        while( $kfr->CursorFetch() ) {
            $s .= "<TR><TD>".$kfr->Value('name')."</TD><TD>".$kfr->Value('age')."</TD>"
                     ."<TD>".$kfr->Value("pets_name")."</TD><TD>".$kfr->Value("pets_age")."</TD></TR>";
        }
        $s .= "</TABLE>";
    }
    return( $s );
}

?>
