<?php

define( "DIR_MBRMDB", SEEDSX_ROOT_REALDIR."mbrmdb/" );
define( "REALPATH_MDB_FILE", DIR_MBRMDB."up.mdb" );
define( "DBT_MBR_TMP_UPLOAD", "mbr_tmp_mdb_upload" );


$mapUpload2Contact = array(
/* The upload columns are from the schema extracted by mdb-schema
 * The contact columns are from our definition of mbr_contacts
 */
//         mbr_tmp_mdb_upload       mbr_contacts
// mbrid -> _key
    array( "member_id",             "mbr_code",     "S" ),
    array( "firstname",             "firstname",    "S" ),
    array( "lastname",              "lastname",     "S" ),
    array( "company",               "company",      "S" ),
    array( "department",            "dept",         "S" ),
    array( "address",               "address",      "S" ),
    array( "city",                  "city",         "S" ),
    array( "province",              "province",     "S" ),
    array( "country",               "country",      "S" ),
    array( "pcode",                 "postcode",     "S" ),
    array( "email",                 "email",        "S" ),
    array( "phone",                 "phone",        "S" ),
//    array( "status",                "status",       "S" ),
    array( "startdate",             "startdate",    "D" ),
    array( "expires2",              "expires",      "D" ),  // tmp.expires is translated to tmp.expires2, which maps to contacts.expires
    array( "lastrenew",             "lastrenew",    "D" ),
    array( "french",                "lang",         "S" ),
//    array( "mailing",               "",             "S" ),  // not sure: indicates Betty gets no mailing
    array( "referral",              "referral",     "S" ),
    array( "comment",               "comment",      "S" ),
//    array( "receipt__",             "",             "S" ),  // receipt number
//    array( "s_e__grower",           "",             "S" ),
//    array( "donation",              "donation",     "F" ),
//    array( "date_of_donation",      "donation_date","D" ),
//    array( "extension",             "phone_ext",    "S" ),
//    array( "Cumulative_donations",  "",             "F" ),
//    array( "1st_donation_of_year",  "",             "S" ),
//    array( "Total_07_Donations",    "",             "S" ),
    array( "NO E bulletin (check)", "bNoEBull",     "I" ),
    array( "NO Donor Appeals",      "bNoDonorAppeals","I" ),
    array( "NO SED",                "bNoSED",       "I" ),
);



function MbrUpload_ReadMDB2TmpTable( $fnameMDB, $kfdb )
/******************************************************
    $fnameMDB is the full path of the MDB file to read
    The schema is compared to the stored schema.
    The data is read into DBT_MBR_TMP_UPLOAD

    Returns 0=success, otherwise dies
 */
{
    global $mapUpload2Contact;

    $s = "";

    $fnameSchema     = DIR_MBRMDB."tmp_s.sql";                  // the schema extracted from the MDB
    $fnameSchema2    = DIR_MBRMDB."tmp_s2.sql";                 // the schema file after sed
    $fnameInsert     = DIR_MBRMDB."tmp_i.sql";                  // the insert statements extracted from the MDB
    $fnameInsert1    = DIR_MBRMDB."tmp_i1.sql";                 // the insert file after sed
    $fnameInsert2    = DIR_MBRMDB."tmp_i2.sql";                 // the insert file after iconv
    $fnameSchemaCmp  = DIR_MBRMDB."perm_schema_extracted.sql";  // Perm: the expected schema, should match upschema.sql
    //                            "perm_schema_edited.sql"      // Perm: the hand-edited stored schema that creates mbr_tmp_upload_mdb

    /* Clean up the processing directory
     */
    @unlink( $fnameSchema );
    @unlink( $fnameSchema2 );
    @unlink( $fnameInsert );
    @unlink( $fnameInsert1 );
    @unlink( $fnameInsert2 );

    /* Generate the schema file and the insert statements file from the mdb.
     *
     * Full path names needed on mdb-tools because apparently the apache process doesn't have our PATH handy.
     * The mdb-* commands work from our command line but return error 127 via the web script, which is sh's error for command not found.
     */
    $s .= "<br/>Extracting schema from Access file";
    $iRet = 0;
    $cmd = "/usr/bin/mdb-schema --no-drop-table --table \"'CURRENT Members\" $fnameMDB mysql > $fnameSchema";
    system( $cmd, $iRet );
    if( $iRet ) {
        $s .= "<p class='alert alert-danger'>Error '$iRet' from mdb-schema</p>";
        return( $s );
    }

    $s .= "<br/>Extracting data from Access file";
    // -H       = no headers (doesn't make headers without this anyway)
    // -I mysql = inserts instead of csv
    // -D       = date format
    $cmd = "/usr/bin/mdb-export -H -I mysql -D \"%Y-%m-%d\" $fnameMDB \"'CURRENT Members\" > $fnameInsert";
    system( $cmd, $iRet );
    if( $iRet ) {
        $s .= "<p class='alert alert-danger'>Error '$iRet' from mdb-export</p>";
        return( $s );
    }

    /* sed the schema file to fix invalid or awkward names
     */
    $s .= "<br/>Renaming schema columns";
    $sed = "s/\`'CURRENT Members\`/".DBT_MBR_TMP_UPLOAD."/; " // change the table name
          ."s/\`NUMBER\`/\`MBRID\`/; "                        // change _key name from NUMBER (reserved token!)
          ."s/^--.*$//; "                                     // mdb-schema puts some lines of hyphens that mysql doesn't like (huh?)
                                                              //     This actually rids all comments
          ."s/^COMMENT.*$//;";                                // Judy put a comment on a column, which comes out as a COMMENT...; statement after the CREATE TABLE;
    $cmd = "sed \"$sed\" $fnameSchema > $fnameSchema2";
    system( $cmd, $iRet );
    if( $iRet ) {
        $s .= "<p class='alert alert-danger'>Error '$iRet' from sed on schema</p>";
        return( $s );
    }

    /* sed the inserts file to fix invalid or awkward names
     */
    $s .= "<br/>Renaming data columns";
    $sed = "s/\`'CURRENT Members\`/".DBT_MBR_TMP_UPLOAD."/; "  // change the table name
          ."s/\`NUMBER\`/\`MBRID\`/; ";                        // change _key name from NUMBER (reserved token!)
          //."s/)$/); /; ";                                    // mdb-export doesn't put ; after each statement
    $cmd = "sed \"$sed\" $fnameInsert > $fnameInsert1";
    system( $cmd, $iRet );
    if( $iRet ) {
        $s .= "<p class='alert alert-danger'>Error '$iRet' from sed on inserts</p>";
        return( $s );
    }


    /* Convert utf-8 characters to cp1252
     *
     * mdb-export outputs microsoft's utf-8 but our web site uses cp1252
     *
     * Access does stupid microsoft things like converting perfectly good hyphens and apostrophes into funny unicode characters.
     * Conversion to iso-8859-1 doesn't work, because iconv isn't quite that smart. Conversion to cp1252 probably is okay, because those funny
     * characters were Windows things sometime in the past. If iconv can't do a conversion, it stops unless the -c flag is specified. So we
     * specify -c and accept that really weird characters of UTF-8 will just be omitted.
    */
    $s .= "<br/>Converting character set";
    $cmd = "/usr/bin/iconv -c  --from-code utf-8  --to-code cp1252  --output $fnameInsert2  $fnameInsert1";
    system( $cmd, $iRet );
    // even with -c it still throws a return value of 1 if it encounters a char that it can't convert
    if( $iRet != 0 && $iRet != 1 ) {
        $s .= "<p class='alert alert-danger'>Error '$iRet' from iconv</p>";
        return( $s );
    }


    /* DB: re-create the temporary table using the schema file
     */
    $s .= "<BR/>Creating temporary database table";

    $sMyUser = SiteKFDB_USERID;
    $sMyPwd  = SiteKFDB_PASSWORD;

    // mdb-schema can put this in its sql but it can't do IF EXISTS, and the mysql client returns an error if the table doesn't exist
    $kfdb->Execute( "DROP TABLE ".DBT_MBR_TMP_UPLOAD.";" );

    $cmd = "/usr/bin/mysql -u $sMyUser --password=\"$sMyPwd\" -D seeds2 < $fnameSchema2";
    system( $cmd, $iRet );
    if( $iRet ) {
        $s .= "<p class='alert alert-danger'>Error '$iRet' from mysql creating temp table</p>";
        return( $s );
    }
    /* DB: load the data using the insert statements file
     */
    $s .= "<BR/>Loading data into temporary database table";
    $cmd = "/usr/bin/mysql -u $sMyUser --password=\"$sMyPwd\" -D seeds2 --default-character-set=latin1 < $fnameInsert2";
    system( $cmd, $iRet );
    if( $iRet ) {
        $s .= "<p class='alert alert-danger'>Error '$iRet' from mysql reading inserts</p>";
        return( $s );
    }


    /* Recompute the expires date
     */
    $kfdb->Execute( "ALTER TABLE ".DBT_MBR_TMP_UPLOAD." ADD expires2 date" );

    $kfdb->Execute( "UPDATE ".DBT_MBR_TMP_UPLOAD." SET expires2=concat('20',right(expires,2),'-12-31') "
                   ."WHERE left(expires,6)='12/31/' AND mid(expires,7,1)<>'9' AND length(expires)=8");
    $kfdb->Execute( "UPDATE ".DBT_MBR_TMP_UPLOAD." SET expires2=concat('19',right(expires,2),'-12-31') "
                   ."WHERE left(expires,6)='12/31/' AND mid(expires,7,1)='9' AND length(expires)=8");

    $kfdb->Execute( "UPDATE ".DBT_MBR_TMP_UPLOAD." SET expires2='".MbrExpiryCode2Date( 'C' )."' WHERE expires IN ('c','C')");
    $kfdb->Execute( "UPDATE ".DBT_MBR_TMP_UPLOAD." SET expires2='".MbrExpiryCode2Date( 'A' )."' WHERE expires IN ('a','A')");
    $kfdb->Execute( "UPDATE ".DBT_MBR_TMP_UPLOAD." SET expires2='".MbrExpiryCode2Date( 'L' )."' WHERE expires IN ('L')");








    /* Compare it with the saved schema.
     * Our tmp table uses manually-fixed types since mdbtools doesn't output MySQL schemas.
     * If the schema is different than the saved copy, we have to do the manual changes to our table.
     */
/*
    echo "<BR>Verifying schema";
    echo "<PRE>";  // system writes its output to stdout. Its return val is just the last line of output.
    system( "diff $fnameSchemaCmp $fnameSchema", $iRet );
    echo "</PRE>";
    if( $iRet )  echo "<P style='color:red'>The Access database seems to have different columns since the last update. If these are just minor changes there shouldn't be a problem, but major differences (e.g. changing the address column) will require an update to the web database columns.</P>"
                                    ."<P style='color:red'>Error '$iRet' from diff: comparing schemas</P>";
*/


    /* Load the insert statements to the temp table
     */
/*
    if( !$kfdb->TableExists(DBT_MBR_TMP_UPLOAD) )  die( "Table ".DBT_MBR_TMP_UPLOAD." does not exist. Please create it from the schema file." );
    $kfdb->Execute( "TRUNCATE TABLE ".DBT_MBR_TMP_UPLOAD ) or die( "Cannot truncate ".DBT_MBR_TMP_UPLOAD );

    echo "<BR>Loading data to temporary database table";
    system( "mysql -u seeds2 --password=\"".SiteKFDB_PASSWORD."\" -D seeds2 < $fnameTmp2", $iRet );
    if( $iRet )  die( "<P style='color:red'>Error '$iRet' from mysql: reading inserts</P>" );
*/

    /* Pad NULLs because they screw up some queries, and particularly CONCAT()
     */
    $s .= "<br/>Padding NULLs";
    foreach( $mapUpload2Contact as $map ) {
        if( $map[2] != "D" ) {
            /* Not sure how to pad null dates - leave them alone for now
             */
            $kfdb->Execute("UPDATE ".DBT_MBR_TMP_UPLOAD." SET ".$map[0]."=".($map[2]=='S'?"''":0)." WHERE ".$map[0]." IS NULL" );
        }
    }

    /* Examine the 'expires' column and translate it to datetime
     *
     * Valid values of 'expires' are 12/31/YY, 'A', and NULL
     *
     * 12/31/YY is translated to 20YY-12-31
     * A (automatic renewal) is translated to the arbitrary but unambiguous value 2100-01-01
     */
    $bBadExpiry = false;
    if( ($dbc = $kfdb->CursorOpen( "SELECT distinct(expires) FROM ".DBT_MBR_TMP_UPLOAD." ORDER BY 1" )) ) {
        while( $ra = $kfdb->CursorFetch( $dbc ) ) {
            if( !empty($ra[0]) &&
                $ra[0] != 'a' &&
                $ra[0] != 'A' &&
                $ra[0] != 'L' &&
                $ra[0] != 'C' &&
                substr( $ra[0], 0, 6 ) != "12/31/" )
            {
                $s .= "<br/><font color='red'>Invalid expiry date '${ra[0]}' in 'expires' column</font>";
                $bBadExpiry = true;
            }
        }
        $kfdb->CursorClose( $dbc );
    }


    /* Clean up data
     */
    $kfdb->Execute( "DELETE FROM ".DBT_MBR_TMP_UPLOAD." WHERE MBRID=0 OR MBRID IS NULL" );


    $n = intval( $kfdb->Query1( "SELECT count(*) FROM ".DBT_MBR_TMP_UPLOAD ) );
    $s .= "<p class='alert alert-success'>Found $n records in uploaded Access file.</p>";

    return( $s );
}

?>
