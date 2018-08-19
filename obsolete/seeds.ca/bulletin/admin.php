<?
/* Administration functions for Email Bulletin
 *
 * Requires login.
 */
header( "Location: http://office.seeds.ca/mbr/mbr_contacts.php" );


include_once( "../site.php" );
include_once( "_bull.php" );
include_once( SITEINC."sitedb.php" );
include_once( SITEINC."sodlogin.php" );

$la = new SoDLoginAuth;
if( !$la->SoDLoginAuth_Authenticate( $_REQUEST, "W BULL" ) ) { exit; }

echo "<H2>Seeds of Diversity e-Bulletin Administration</H2>";
echo "<BLOCKQUOTE>";

$action = @$_REQUEST["action"];

if( $action == "Add" ) {
    echo "<H3>Adding email addresses</H3>";

    for( $i = 1; $i <= 5; ++$i ) {
        $e = BXStd_SafeGPCGetStr( "e".$i );
        if( !empty( $e['plain'] ) ) {
            echo "<BR>Adding ${e['plain']}: ";
            $b = db_query1( "SELECT email FROM bull_list WHERE email='${e['db']}'" );
            if( !empty($b) ) {
                echo "duplicate, not added";
            } else {
                $id = db_insert_autoinc_id( "INSERT INTO bull_list (id,email,hash,status,ts1) VALUES (NULL,'${e['db']}','Added by ".$la->LoginAuth_UID()."',1,NOW())" );
                if( !$id )  die( "<P>Sorry, unable to update email list.  Please try again or contact our ".MAILTO_Webmaster." for assistance</P>" );
                echo "successful";
            }
        }
    }
    echo "<HR>";

} else if( $action == "Delete" ) {
    echo "<H3>Deleting email addresses</H3>";

    for( $i = 1; $i <= 5; ++$i ) {
        $e = BXStd_SafeGPCGetStr( "e".$i );
        if( !empty( $e['plain'] ) ) {
            echo "<BR>Deleting ${e['plain']}: ";
            $ra = db_query( "SELECT * FROM bull_list WHERE email='${e['db']}'" );
            if( empty($ra['email']) ) {
                echo "not found in subscriber list";
            } else {
                $now = db_query1( "SELECT NOW()" );
                BXStd_Log( "Bulletin.log", "DELETE: ${ra['id']}\t${ra['email']}\t${ra['ts0']}\t${ra['ts1']}\t${ra['ts2']}\t$now : deleted by ".$la->LoginAuth_UID() );

                if( !db_exec( "DELETE FROM bull_list WHERE id=${ra['id']}" ) ) {
                    die( "<P>Sorry, unable to update email list.  Please try again or contact our ".MAILTO_Webmaster." for assistance</P>" );
                }
                echo "successful";
            }
        }
    }
    echo "<HR>";
}


echo "<P><B>To Add</B> emails to the e-Bulletin subscriber list, enter them below and click Add</P>";
echo "<P><B>To Delete</B> emails from the e-Bulletin subscriber list, enter them below and click Delete</P>";
echo "<P>Don't worry about duplicates; we screen for them.</P>";

echo "<FORM action='${_SERVER['PHP_SELF']}' method=get>";
echo $la->LoginAuth_GetHidden();
echo "<BR>Email 1: <INPUT type=text name=e1 size=50>";
echo "<BR>Email 2: <INPUT type=text name=e2 size=50>";
echo "<BR>Email 3: <INPUT type=text name=e3 size=50>";
echo "<BR>Email 4: <INPUT type=text name=e4 size=50>";
echo "<BR>Email 5: <INPUT type=text name=e5 size=50>";
echo "</P>";

echo "<P><INPUT type=submit name=action value=Add>&nbsp;&nbsp;&nbsp;<INPUT type=submit name=action value=Delete></P>";
echo "</FORM>";

echo "</BLOCKQUOTE>";
?>
