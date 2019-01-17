<?php
// remove the blank line when it shows up

// in verify step, if lastname changes, flag this as something to check manually
// in verify step, warn of duplicate rows (firstname+lastname match || address matches || email matches || phone matches), flag for manual handling
// in verify step, warn of rows removed from Access, flag for manual deletion/hiding
// or put this into an integrity-check process
// add a flag system for ignoring irregularities  bIgnoreDupName - these can all be cleared and re-approved occasionally

// Make policy for mailings:
// English = lang==E
// French = lang==F
// Bilingual = lang==F || province==QC || province==NB


/* Member list uploader
 *
 * Allows an Access membership database to be loaded into the online master database.
 * Duplicate records are updated, new records are inserted.
 */
define( "SITEROOT", "../../" );
include_once( SITEROOT."site2.php" );
include_once( SEEDCOMMON."siteutil.php" );      // Site_Log
include_once( SEEDCOMMON."console/console01.php" );
include_once( SEEDCOMMON."mbr/mbrCommon.php" );
include_once( "_mbr_upload.php" );

//var_dump($_REQUEST);
//var_dump($_FILES);

//$mbr_cols = "num,code,firstname,lastname,company,department,address,city,province,country,postcode,phone,phone_ext,status,startdate,expires,bCurrent,renewed,language,mailing,referral,lastrenew,email";

list($kfdb,$sess) = SiteStartSessionAccount( array("A MBR") );

$oUpload = new MbrUpload();

$stepDef =   array( "Title_EN" => "",//Membership List Upload",
                    "Steps" => array( array( "fn"=>array($oUpload,"GetFile"),             "Title_EN"=>"Select File" ),
                                      array( "fn"=>array($oUpload,"MoveFile"),            "Title_EN"=>"Begin Data Transfer" ),
                                      array( "fn"=>array($oUpload,"LoadFile"),            "Title_EN"=>"Read Access File" ),
                                      array( "fn"=>array($oUpload,"Validate"),            "Title_EN"=>"Verify" ),
                                      array( "fn"=>array($oUpload,"UpdateDB"),            "Title_EN"=>"Commit Changes" )
                                    )
                  );
$oStep = new Console01_Stepper( $stepDef, array( 'kfdb' => $kfdb, 'sess' => $sess ) );

$s = $oStep->DrawStep( -1 );  // -1 == use the Console class's own http parm to increment the step

$oC = new Console01( $kfdb, $sess,
                     array( 'HEADER' => "Membership List Upload",
                            'bBootstrap' => true,
                            'bLogo' => true,
                            'sCharset' => 'Windows-1252'
) );
echo $oC->DrawConsole( $s );


/*** END ***/


class MbrUpload
{
    function __construct() {}

    function GetFile()  { return( MbrUpload_GetFile() ); }
    function MoveFile() { return( MbrUpload_MoveFile() ); }
    function LoadFile() { global $oStep; return( MbrUpload_LoadFile( $oStep ) ); }
    function Validate() { global $oStep; return( MbrUpload_Validate( $oStep ) ); }
    function UpdateDB() { global $oStep; return( MbrUpload_UpdateDB( $oStep ) ); }
}



function MbrUpload_GetFile()
/***************************
    Tell the user how to upload a member file, get the file
 */
{
    $ok = true;
    $s = "<p>Find the current Access membership file and Upload it to our online database. "
        ."The online database is read-only for staff, directors, regional reps, project volunteers, etc.</P>"
// note this ini value can't be set dynamically: only in php.ini, .htaccess, http.conf
        ."<p style='font-weight:bold'>The file can be up to ".ini_get('upload_max_filesize')." in size. If it's larger, get Bob's help.</p>"
        ."<br/>"
        ."<form action='${_SERVER['PHP_SELF']}' method='post' enctype='multipart/form-data'>"
        ."<table border='0'><tr>"
        ."<td><input type='file' name='mbrfile'/></td>"
        ."<td>".SEEDStd_StrNBSP("     [[next]]")."</td>"
        ."</tr></table>"
        ."</form>"
        ."<br/><br/>";
    $sAfter =
         "<H4>Why You Should Be Doing This</H4>"
        ."<P>Do this whenever the contact information on this web site is out of date.  e.g. there are new members in the Access database. "
        ."Repeat this operation as often as you wish, to keep the online information accurate.</P>"
        ."<H4>What You Need</H4>"
        ."<UL><LI>Permission to upload membership databases.  Only people with the right passwords are allowed to do this. "
        ."If you've gotten this far, then you can do it!</LI>"
        ."<LI>A current membership database in an Access file (a .mdb file).</LI></UL>"
        ."<H4>What Will Happen</H4>"
        ."<UL><LI>Use the Browse button to find the current Access file. Click the Upload button.</LI>"
        ."<LI>It will take a long time to upload the file. Be patient. "
        ."If it doesn't happen, or you get an error on the screen, tell Bob.</LI>"
        ."<LI>The Access file will be compared with the current information in the web database. You will be shown all "
        ."the differences, and you'll get to choose whether to commit the changes or cancel. "
        ."You can also use this step to double-check the changes that you've made since the last upload.</LI>"
        ."<LI>When you click Commit, the changes will be written to the online database, available to other people.</LI>"
        ."<LI>If you click Cancel, the online database will not be changed. You can upload again anytime.</LI></UL>"
        ."<H4>Some Rules</H4>"
        ."<UL><LI>Every contact must have a unique contact number (the member number). This number is used in other parts of the online "
        ."database to refer to members, non-members, donors, friends, media contacts, etc. "
        ."If the number is blank, or not unique, you'll probably get an error telling you to fix that problem.</LI>"
        ."<LI>Each person's contact number must never change, since that would break the connections with other "
        ."lists in the online database. The system will do its best to notice if a number changes (like if you "
        ."enter a new member who was already there with an old number)</LI>"
        ."<LI>It's okay to add, change or delete columns in the Access database, but a corresponding change has to "
        ."be made to the online database. You won't be able to upload Access files with different columns. "
        ."Just tell Bob what changes are needed.</LI>"
        ."<LI>Be careful changing email addresses. Everyone's email address is also their login user id. (It isn't clear "
        ."yet how we'll handle email address changes).</LI></UL>"
        ."<H4>It's hard to go wrong</H4>"
        ."<P>Don't worry about making a mistake. There's not much that can go wrong, that can't be fixed. Go ahead and upload your file.</P>";

    return( array( 'sForm' => $s,
                   'sAfter' => $sAfter,
                   'btnNext' => 'Upload',
                   'buttons' => ""            // no need for repeat or cancel on the first screen
                 )
    );
}


function MbrUpload_MoveFile()
/****************************
    The MDB file has been uploaded. Now put it in the processing directory.
    This step allows the next step to be repeated without re-posting the whole MDB
*/
{
    $s = "";
    $ok = true;

    $f = @$_FILES['mbrfile'];

    if( !isset($f['error']) || $f['error'] != 0 ) {
        $s .= "<p class='alert alert-danger'>The upload was not successful. ";
        if( $f['size'] == 0 ) {
            $s .= "No file was uploaded.  Please try again.";
        } else if( !isset($f['error']) ) {
            $s .= "No error was recorded.  Please tell Bob.";
        } else {
            $s .= "Please tell Bob that error # ${f['error']} was reported.";
        }
        $s .= "</p>";
        $ok = false;
    }

    if( $ok && !is_uploaded_file( $f['tmp_name'] ) ) {
        $s .= "<p class='alert alert-danger'>The upload was not successful.  Please tell Bob that is_upload_file failed.</p>";
        $ok = false;
    }

    if( $ok ) {
        @unlink( REALPATH_MDB_FILE );
        if( !move_uploaded_file( $f['tmp_name'], REALPATH_MDB_FILE ) ) {
            // DIR_MBRMDB should be rwx----wx
            $s .= "<p class='alert alert-danger'>The upload was not successful.  Please tell Bob that move_upload_file failed.</p>";
            $ok = false;
        }
    }

    if( $ok ) {
        $s .= "<p class='alert alert-success'>You uploaded <B>${f['name']}</B> successfully (${f['size']} bytes).</p>"
             ."<p>Click Next to open and verify the uploaded file.</p>";
    }

    return( array( 's' => $s,
                   'buttons' => $ok ? "next cancel" : "cancel"
                 )
    );
}


function MbrUpload_LoadFile( $oStep )
/**********************************
    Load the rows from the Access file to a tmp table
    This step allows the next step to be repeated after hand-editing the tmp table (without reloading from the Access file)
 */
{
    global $kfdb;

    $s = MbrUpload_ReadMDB2TmpTable( REALPATH_MDB_FILE, $kfdb );  // returns a boostrap alert paragraph

    $s .= "<p><b>Click Next to see a summary of the new information.</b>"
         ." (You'll have a chance to Cancel before the changes are committed)</p>";

    return( array( 's' => $s,
                   'buttons' => "next repeat cancel",
                 )
    );
}


function MbrUpload_Validate( $pg2 )
/**********************************
    Look for errors in the tmp table
 */
{
    global $kfdb;

    $s = "";
    $ok = true;

    // non-unique mbrid
    $n = 0;
    if( ($dbc = $kfdb->CursorOpen( "SELECT A.mbrid, count(*) FROM ".DBT_MBR_TMP_UPLOAD." A, ".DBT_MBR_TMP_UPLOAD." B "
                                  ."WHERE A.mbrid=B.mbrid GROUP BY 1 ORDER BY 2 DESC" )) ) {
        while( $ra = $kfdb->CursorFetch( $dbc ) ) {
            if( $ra[1] > 1 ) {
                $s .= "<p>There are duplicate rows with contact number '${ra[0]}'</p>";
                ++$n;
            }
        }
        $kfdb->CursorClose( $dbc );
    }
    if( $n ) {
        $s .= "<p class='alert alert-danger'>Please fix the Access database and upload again.</p>";
        $ok = false;
    }

    if( $ok ) {
        /* Report the changes
        */
        $s .= "<p>The following changes were found in the Access file. "
             ." Please review them, and click Commit at the bottom to copy the changes to the online database.</p>";

        $raChanges = mbrUpload_FindChanges( $kfdb );

        $s .= mbrUpload_ReportChanges( $raChanges, $kfdb );

        $s .= "<P>Click Commit to copy the changes to the online database.</P>";
    }

    return( array( 's' => $s,
                   'btnNext' => "Commit",
                   'buttons' => ($ok ? "next repeat cancel" : "cancel"),
                 )
    );
}


function MbrUpload_UpdateDB( $pg2 )
/**********************************
 */
{
    define("MBRUPLOAD_LOG","mbrupload.log");            // log the INSERT and UPDATE statements
    define("MBRUPLOAD_HTML_LOG","mbrupload_html.log");  // log the friendly HTML summary

    global $mapUpload2Contact;
    global $kfdb;

    $s = "";

    $nNewGood = $nNewBad = $nUpdateGood = $nUpdateBad = 0;

    $raChanges = mbrUpload_FindChanges( $kfdb );
    $sReportChanges = mbrUpload_ReportChanges( $raChanges, $kfdb );

    // log the changes (the log doesn't reflect failed inserts/updates)
    Site_Log( MBRUPLOAD_HTML_LOG, $sReportChanges );
    MailFromOffice( "bob@seeds.ca", "Contact Database Update", "", $sReportChanges );
    MailFromOffice( "judy@seeds.ca", "Contact Database Update", "", $sReportChanges );


    /* Copy the new rows to mbr_contacts
     */
    $fields0 = $fields1 = "";
    foreach( $mapUpload2Contact as $map ) {
        if( empty( $map[1] ) ) continue;
        $fields0 .= ",`{$map[0]}`";
        $fields1 .= ",{$map[1]}";
    }
    foreach( $raChanges['new'] as $mbrid ) {
        $q = "INSERT INTO mbr_contacts (_key,_created,_updated $fields1 ) "
            ."SELECT mbrid,NOW(),NOW() $fields0 FROM ".DBT_MBR_TMP_UPLOAD." WHERE mbrid='$mbrid'";
        if( $kfdb->Execute( $q ) ) {
            Site_Log( MBRUPLOAD_LOG, $q );
            $s .= "<br/>Added member # $mbrid";
            ++$nNewGood;
        } else {
            $s .= "<br/><font color='red'>Error inserting member # $mbrid : ".$kfdb->GetErrMsg()."</font>";
            ++$nNewBad;
        }
    }

    /* Update the changed rows to mbr_contacts
     */
    foreach( $raChanges['updates'] as $mbrid => $raCols ) {
        if( !isset( $raCols['cols'] ) ) {   // see similar code in ReportChanges
            continue;
        }

        $set = "";
        foreach( $raCols['cols'] as $colName => $raVals ) {
            $set .= ",$colName = '".addslashes($raVals[0])."'";
        }
        $q = "UPDATE mbr_contacts SET _updated=NOW() $set WHERE _key='$mbrid'";
        if( $kfdb->Execute( $q ) ) {
            Site_Log( MBRUPLOAD_LOG, $q );
            $s .= "<br/>Updated member # $mbrid ${raCols['name']} : $set";
            ++$nUpdateGood;
        } else {
            $s .= "<br/><font color='red'>Error updating member # $mbrid : $set</font>";
            ++$nUpdateBad;
        }
    }

    $s .= "<h4>Summary</h4>"
         ."<p><b>$nNewGood</b> new contacts were added</p>"
         ."<p><b>$nNewBad</b> new contacts failed to be added</p>"
         ."<p><b>$nUpdateGood</b> contacts were updated</p>"
         ."<p><b>$nUpdateBad</b> contacts failed to be updated</p>"

         ."<br/><p>All done.  Thankyou!</p>";

    return( array( 's' => $s ) );
}



function mbrUpload_findChanges( $kfdb )
/**************************************
    Compare the rows in DBT_MBR_TMP_UPLOAD with the (possibly superset) of rows in mbr_contacts.
    Return the changes.
    Basic data integrity of the tmp table has been tested: all mbrid are non-zero and unique
 */
{
    global $mapUpload2Contact;

    $raChanges = array();
    $raChanges['new'] = array();        // array of mbrid that are new
    $raChanges['updates'] = array();    // array of [mbrid][mbr_contact col name] => array(new val, old val)
    $raChanges['deletes'] = array();    // array of mbrid that are removed

    /* Find mbrid in the tmp table that are not in the contact table
     */
    if( ($dbc = $kfdb->CursorOpen( "SELECT A.mbrid FROM ".DBT_MBR_TMP_UPLOAD." A LEFT JOIN mbr_contacts B "
                                  ."ON (A.mbrid=B._key) WHERE B._key IS NULL" )) ) {
        while( $ra = $kfdb->CursorFetch( $dbc ) ) {
            $raChanges['new'][] = $ra[0];
        }
        $kfdb->CursorClose( $dbc );
    }

    /* Find rows in the tmp table that are in the contact table, but which have altered cols
     */
    $fieldsA = $fieldsB = "";
    $raCondChanged = array();
    foreach( $mapUpload2Contact as $map ) {
        if( empty($map[1]) ) continue;
        $fieldsA .= "A.`{$map[0]}` AS `A_{$map[0]}`,";
        $fieldsB .= "B.".$map[1]." AS B_{$map[1]},";
        $raCondChanged[] = "A.`{$map[0]}`<>B.{$map[1]}";    // IFNULL(A.$map[0],".($map[2]='S'?'':0).",A.$map[0]) <>B.$map[1]
    }
    if( ($dbc = $kfdb->CursorOpen( "SELECT $fieldsA $fieldsB B._key AS mbrid FROM ".DBT_MBR_TMP_UPLOAD." A, mbr_contacts B "
                                  ."WHERE (A.mbrid=B._key) AND (". implode(" OR ", $raCondChanged) .")" )) ) {
        while( $ra = $kfdb->CursorFetch( $dbc ) ) {
            $raChanges['updates'][$ra['mbrid']]['name'] = $ra['B_firstname']." ".$ra['B_lastname'];
            foreach( $mapUpload2Contact as $map ) {
                if( !empty($map[1]) && ($ra["A_".$map[0]] != $ra["B_".$map[1]]) ) {
                    $raChanges['updates'][$ra['mbrid']]['cols'][$map[1]][0] = $ra["A_".$map[0]];
                    $raChanges['updates'][$ra['mbrid']]['cols'][$map[1]][1] = $ra["B_".$map[1]];
                }
            }
        }
        $kfdb->CursorClose( $dbc );
    }

    // find mbrid in the contact table that are absent from the tmp table - log these or flag them?

    return( $raChanges );
}


function mbrUpload_ReportChanges( $raChanges, $kfdb )
/****************************************************
    return an HTML string that reports the changes recorded in the given array
 */
{
    global $mapUpload2Contact;

    $s = date( "l F j, Y", time() ).SEEDStd_StrNBSP("",10)."(last update was ".$kfdb->Query1("SELECT max(_updated) FROM mbr_contacts").")";

    $s.= "\n<H4>Changed rows</H4>\n";
    if( !count( $raChanges['updates'] ) ) {
        $s.= "<P>No changes.</P>\n";
    } else {
        $s1 = "";
        foreach( $raChanges['updates'] as $mbrid => $raCols ) {
            // Sometimes mysql finds a change but php doesn't see it in the
            // retrieved rows (e.g.''=NULL) so $raCols['cols'] can be missing
            if( !isset( $raCols['cols'] ) ) {
                $s .= "<P># $mbrid : ${raCols['name']} flagged with a change, but no changes found.</P>";
                continue;
            }
            $s1.= "<p># $mbrid: ${raCols['name']}<br/>";

            foreach( $raCols['cols'] as $colName => $raVals ) {
                if( $colName=='expires' ) {
                    $sCode = MbrExpiryDate2Code( $raVals[0] );  // convert special dates to A, L, C codes
                    if( $sCode )  $raVals[0] = $sCode;
                }
                $s1.= "$colName changed from <font color='red'>[${raVals[1]}]</font> to <font color='green'>[${raVals[0]}]</font><br/>";
            }
            $s1.= "</p>\n";
        }
        $s .= $s1;
    }

    $s.= "\n<H4>New rows</H4>";
    if( !count( $raChanges['new'] ) ) {
        $s.= "<P>No new rows.</P>\n";
    } else {
        // though SQL is case-insensitive, php arrays aren't. Since the table col names are mixed case and the map keys
        // are lower, "SELECT *" doesn't match the map. This forces the same case of col names to be returned as ra keys.
        $cols = "";
        foreach( $mapUpload2Contact as $map ) {
            $cols .= "`{$map[0]}`,";
        }
        $s.= "<TABLE border=0 cellpadding=10>\n";
        foreach( $raChanges['new'] as $mbrid ) {
if( !$mbrid ) continue;
            $s.= "<TR><TD valign='top'># $mbrid</TD><TD valign='top' style='font-size:8pt;'>";
            $ra = $kfdb->QueryRA( "SELECT $cols mbrid FROM ".DBT_MBR_TMP_UPLOAD." WHERE mbrid=$mbrid" );
            foreach( $mapUpload2Contact as $map ) {
                $s.= $map[0].": <font color='green'>".$ra[$map[0]]."</font><BR>";
            }
            $s.= "</TD></TR>\n";
        }
        $s.= "</TABLE>\n";
    }
    $s.= "<HR>\n";

    return( $s );
}

?>
