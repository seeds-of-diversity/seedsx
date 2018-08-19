<?
/* Member list summary
 *
 * Dumps statistics about our membership, based on the mbr_members table
 */
include_once( "../site.php" );
include_once( SITEINC."sitedb.php" );
include_once( SITEINC ."sodlogin.php" );


$mbr_cols = "num,code,firstname,lastname,company,department,address,city,province,country,postcode,phone,phone_ext,status,startdate,expires,bCurrent,renewed,language,mailing,referral,lastrenew,email";


$la = new SoDLoginAuth;
if( !$la->SoDLoginAuth_Authenticate( $_REQUEST, "W perm_mbr" ) ) { exit; }

echo "<H2>Membership Summary</H2>";
$ra = db_query( "SELECT MIN(year) AS y1, MAX(year) AS y2 FROM mbr_members WHERE year <> -1" );
$y_min = $ra['y1'];
$y_max = $ra['y2'];
echo "<P>The following statistics are gathered from our master membership database, which currently ";
echo "contains membership records from <B>$y_min to $y_max</B>.  It should be kept up to date as frequently as possible.</P>";

echo "<H4>Number of Members per Year</H4>";
echo "<BLOCKQUOTE>";
for( $i = $y_max; $i >= $y_min; --$i ) {
    echo "<I>$i</I>: ".db_query1( "SELECT count(*) FROM mbr_members WHERE year=$i" ).($i==date("Y")?" (so far)" : "")."<BR>";
}
echo "</BLOCKQUOTE>";


echo "<H4>Number of Members Who Told Us Their Email Address</H4>";
echo "<BLOCKQUOTE>";
for( $i = $y_max; $i >= $y_min; --$i ) {
    echo "<I>$i</I>: ".db_query1( "SELECT count(*) FROM mbr_members WHERE year=$i AND email <> '' AND email IS NOT NULL" ).($i==date("Y")?" (so far)" : "")."<BR>";
}
echo "</BLOCKQUOTE>";


echo "<H4>Number and Percentage of Members by Province</H4>";
echo "<BLOCKQUOTE><TABLE border=1><TR><TH>BC</TH><TH>AB</TH><TH>SK</TH><TH>MB</TH><TH>ON</TH><TH>QC</TH><TH>NB</TH><TH>NS</TH><TH>PE</TH><TH>NF</TH><TR>";
for( $i = $y_max; $i >= $y_min; --$i ) {
    echo "<TR>";
    echo "<I>$i</I>: ".db_query1( "SELECT count(*) FROM mbr_members WHERE year=$i AND email <> '' AND email IS NOT NULL" ).($i==date("Y")?" (so far)" : "")."<BR>";
    echo "</TR>";
}
echo "</TABLE></BLOCKQUOTE>";



// number of members who renewed (and didn't) each year
// number of members who renewed after X years (these results should be stored historically for past years)
//  vertically: year  horizontally: was a member X years previously


// how many cultivars have been dropped out of our seed exchange each year?


exit;

$p_step = BXStd_SafeGPCGetInt( "step" );
if( $p_step < 1 || $p_step > 3 )  $p_step = 1;


show_hdr( $p_step );

if( $p_step == 1 ) {
    /* Tell user how to upload a file.
     */
    echo "<P>This screen lets you upload a current membership database to our master database server. ";
    echo "The membership information will only be accessible by Seeds of Diversity staff, and only in limited ways.</P>";
    echo "<H4>Why You Should Be Doing This</H4>";
    echo "<P>Do this when you want to update the member information on this web site.  e.g. the member email list. ";
    echo "You can do this operation as often as you wish, to keep the online information accurate.";
    echo "<H4>What You Need</H4>";
    echo "<OL><LI>Permission to upload membership databases.  Only people with the right passwords are allowed to do this. ";
    echo "If you've gotten this far, then your password is one of those.</LI>";
    echo "<LI>A current membership database in the correct format.  <B>Read carefully</B>.  We can only upload membership ";
    echo "files if they are saved in a \"tab-delimited\" text format.  Instructions are shown below. ";
    echo "If you need help, ask Bob.</LI></OL>";
    echo "<H4>How to Save a Membership File in the Right Format</H4>";
    echo "<OL><LI>Assuming that you have an Excel file, say member.xls, open it in your favourite spreadsheet program (such as Excel)</LI>";
    echo "<LI>Click <B>Save As</B> and choose <B>Text (Tab Delimited)</B> in the File Types list</LI>";
    echo "<LI>Type a new filename (e.g. member.txt) and click Save.  The filename should end in .txt</LI>";
    echo "<LI>Some spreadsheet programs give you some options at some point, either before or after you click the Save button. ";
    echo "If you can, choose <none> as the Text Qualifier.  Excel normally uses a double-quote (\"), but if you can prevent that then please do so.</LI></OL>";
    echo "<P>The .txt file that you saved is the file that you will upload below</P>";
    echo "<H4>How to Upload</H4>";
    echo "<P>Now that you have a tab-delimited .txt file, use the Browse button below to find it.  When the filename ";
    echo "appears in the box, click Upload.  The next screen will verify the content of the membership file, and check ";
    echo "for any problems.  <B>Don't worry about making a mistake at this point</B>; nothing will be really changed until ";
    echo "the next screen checks for problems.  Go ahead and try the upload</P>";
    echo "<BR>";
    echo "<BR>";
    echo "<FORM action='${_SERVER['PHP_SELF']}' method=post enctype='multipart/form-data'>";
    echo $la->login_auth_get_hidden();
    echo "<INPUT type=hidden name=step value=2>";
    echo "<INPUT type=file name=mbrfile size=60><BR><INPUT type=submit value=Upload>";
    echo "</FORM>";
} else if( $p_step == 2 ) {
    /* Verify the attributes of the uploaded file
     */
    $f = @$_FILES['mbrfile'];

    if( isset($f['error']) && $f['error'] == 0 ) {
        echo "<P>You uploaded <B>${f['name']}</B> successfully (${f['size']} bytes).</P>";
    } else {
        echo "<P>The upload was not successful. ";
        if( !isset($f['error']) ) {
            echo "No error was recorded.  Please tell Bob.";
        } else {
            echo"Please tell Bob that error # ${f['error']} was reported.";
        }
        echo "</P>";
        exit;
    }

    if( !is_uploaded_file($f['tmp_name']) ) {
        die( "<P>The upload was not successful.  Please tell Bob that is_upload_file failed.</P>" );
    }

    if( !db_exec( "DELETE FROM mbr_tmp_upload" ) ) {
        die( db_errmsg_admin() );
    }
    $fname = str_replace( "\\", "/", $f['tmp_name'] );
    $q = "LOAD DATA INFILE '$fname' into table mbr_tmp_upload fields terminated by '\\t' enclosed by '\\\"' lines terminated by 0x0d0a";
    if( !db_exec( $q ) ) {
        die( db_errmsg_admin( $q ) );
    }

    if( ($n = db_query1( "SELECT COUNT(*) FROM mbr_tmp_upload" )) ) {
        echo "<P>$n rows were imported into a temporary table.  Analyzing for correct format.</P>";
    } else {
        die( "No information could be imported from the uploaded file.  Please start over." );
    }

    echo "<H4>Header Row Check</H4>";
    if( db_query1( "SELECT lastrenew FROM mbr_tmp_upload WHERE email='EMAIL'" ) == 'LASTRENEW' ) {
        echo "<P>The file contained a header row.  ";
        if( db_exec( "DELETE FROM mbr_tmp_upload WHERE email='EMAIL'" ) ) {
            echo "Removed.  Member rows uploaded is now ".--$n.".</P>";
        } else {
            die( db_errmsg_admin() );
        }
    }

    if( $n1 = db_query1( "SELECT COUNT(*) from mbr_tmp_upload WHERE num=0 or num IS NULL" ) ) {
        echo "<H4>Suspicious Member Numbers Found</H4>";
        echo "<P>$n1 rows were imported with member id number = zero.  This should not happen. ";
        echo "Below are some first name / last names of these records.  If they do not look right, <B>do not proceed</B><BLOCKQUOTE>";
        $dbc = db_open( "SELECT firstname,lastname from mbr_tmp_upload WHERE num=0 or num IS NULL LIMIT 20" );
        while( $ra = db_fetch( $dbc ) ) {
            echo $ra['firstname'].", ".$ra['lastname']."<BR>";
        }
        echo "</BLOCKQUOTE></P>";
    }

    echo "<H4>A Sample Record from the Uploaded File</H4>";
    echo "<P>Here is Bob's record (member 1499) from the uploaded file.  If it doesn't look right, <B>do not proceed</B><BLOCKQUOTE>";
    $ra = db_query( "SELECT * FROM mbr_tmp_upload WHERE num=1499" );
    echo "<I>Name:</I> ${ra['firstname']} ${ra['lastname']}<BR>";
    echo "<I>Number/code:</I> ${ra['num']} / ${ra['code']}<BR>";
    echo "<I>Address:</I> ${ra['address']}<BR>";
    echo "<I>City, prov, ctry, pc:</I> ${ra['city']}, ${ra['province']}, ${ra['country']}, ${ra['postcode']}<BR>";
    echo "<I>Phone:</I> ${ra['phone']}<BR>";
    echo "<I>Email:</I> ${ra['email']}<BR>";
    echo "<I>Start:</I> ${ra['startdate']}<BR>";
    echo "<I>Renewed:</I> ${ra['lastrenew']}<BR>";
    echo "<I>Expire:</I> ${ra['expires']}<BR>";
    echo "</BLOCKQUOTE></P>";

    echo "<H4>Member Count</H4>";
    $y = date("Y");
    echo "<P>The uploaded information is for year <B>$y</B>.  If this is not correct, change the year below.</P>";
    $n1 = db_query1( "SELECT COUNT(*) FROM mbr_members where year=$y" );
    $n2 = db_query1( "SELECT COUNT(*) FROM mbr_members MM,mbr_tmp_upload MU WHERE MM.year=$y AND MM.num=MU.num" );
    echo "<P>Right now, there are <B>$n1</B> members in the master database for year $y.<BR>";
    echo "You have uploaded information for <B>$n</B> members.<BR>";
    echo "<B>$n2</B> of the uploaded members are already in the master database. ";
    echo "This should be the same as the number that are already there. [".(($n1==$n2) ? "It is" : "It isn't!")."].<BR>";
    echo "You are going to update $n2 members, possibly with corrected addresses etc, and add ".($n-$n2)." new members for $y.<BR>";
    echo "If this doesn't seem right, <B>do not proceed</B>.</P>";

    echo "<H4>Confirm Year</H4>";
    echo "<P>If all of the tests above look alright, then you can proceed to the next step.</P>";
    echo "<P>But first, it is crucial that the membership year is set correctly below.  Change it if necessary.  Then click Proceed.</P>";
    echo "<FORM action='${_SERVER['PHP_SELF']}' method=post>";
    echo $la->login_auth_get_hidden();
    echo "<INPUT type=hidden name=step value=3>";
    echo "The current membership year is <INPUT type=text name=year value='$y' size=10><INPUT type=submit value=Proceed>";
    echo "</FORM>";
    echo "<BR><BR>";
} else if( $p_step == 3 ) {
    $y = intval($_REQUEST['year']);                                if( $y < 2005 || $y > 2010 )  die( "Invalid input:  Year $y" );
    $n = db_query1( "SELECT COUNT(*) FROM mbr_tmp_upload" );       if( !$n )  die( "Upload table is empty" );
    $n1 = db_query1( "SELECT COUNT(*) FROM mbr_members where year=$y" );

    echo "<P><B>$n</B> records uploaded<BR>";
    echo "<B>$n1</B> members already in master database - replacing with uploaded information<BR>";
    echo "<B>".($n-$n1)."</B> members not already in master database - adding new information</P>";

    echo "<H4>Updating Master Database for Year $y</H4>";
    if( !db_exec( "DELETE FROM mbr_members WHERE year=$y" ) ) {
        die( db_errmsg_admin() );
    }
    if( !db_exec( "INSERT INTO mbr_members (year,$mbr_cols) SELECT $y,$mbr_cols FROM mbr_tmp_upload" ) ) {
        die( db_errmsg_admin() );
    }

    $n3 = db_query1( "SELECT COUNT(*) FROM mbr_members WHERE year=$y" );
    echo "<P><B>$n3</B> members were updated in the master database for year $y.</P>";
    echo "<BR><P>All done.  Thankyou!</P>";
}



function show_hdr( $step )
/*************************
 */
{
    echo "<H2>Membership List Upload</H2>";
    echo "<H2>Step&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
    echo "<FONT color=".($step==1 ? "black" : "#dddddd").">1</FONT>";
    echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
    echo "<FONT color=".($step==2 ? "black" : "#dddddd").">2</FONT>";
    echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
    echo "<FONT color=".($step==3 ? "black" : "#dddddd").">3</FONT>";
    echo "</H2>";
}

?>
