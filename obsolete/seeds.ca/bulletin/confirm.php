<?

// Bulletins should contain an unsubscribe link to here.  It could have parms a={}&b={} where
// a is the user id, b is our hash of a. Or a single concatenated parm c=ab


/* Subscribe/Unsubscribe confirmation control
 *
 * This is normally only linked to from confirmation emails.
 *
 *  $id = the rowid of the confirming user
 *  $c  = the secret hash that confirms this user's choice
 */

include_once( "../site.php" );
include_once( SITEINC."sitedb.php" );
include_once( "_bull.php" );

$id = intval(@$_REQUEST['i']);
$c  = @$_REQUEST['c'];

if( !$id ) { BXStd_HTTPRedirect( "./index.php" ); }

bull_page_header();

$ra = db_query( "SELECT * FROM bull_list WHERE _key=$id" );
if( !$ra['_key'] ) {
    die( "<P>Sorry, unable to locate your confirmation in our database.  Please try again or contact our ".MAILTO_Webmaster." for assistance</P>" );
}

if( $ra['status'] == 1 ) {
    bull_youAreSubscribed( $ra );
} else {
    if( $ra['hash'] != $c ) {
        die( "<P>Sorry, your confirmation code does not match the code that we sent you.  Please try again or contact our ".MAILTO_Webmaster." for assistance</P>" );
    }

    if( $ra['status'] == 0 ) {
        // Subscription confirmation
        if( !db_exec( "UPDATE bull_list SET status=1,ts1=NOW() WHERE _key=$id" ) ) {
            die( "<P>Sorry, unable to update email list.  Please try again or contact our ".MAILTO_Webmaster." for assistance</P>" );
        }
        bull_youAreSubscribed( $ra );

    } else {
        // Unsubscription confirmation
        $now = db_query1( "SELECT NOW()" );
        //BXStd_Log( "Bulletin.log", "DELETE: ${ra['_key']}\t${ra['email']}\t${ra['ts0']}\t${ra['ts1']}\t${ra['ts2']}\t$now" );

        if( !db_exec( "DELETE FROM bull_list WHERE _key=$id" ) ) {
            die( "<P>Sorry, unable to update email list.  Please try again or contact our ".MAILTO_Webmaster." for assistance</P>" );
        }
        echo "<P>Your subscription to Seeds of Diversity's Email Bulletin has been removed.  We have deleted <B>${ra['email']}</B> from our list.</P>";
        echo "<P>We'd like to take this moment to thank you for your past interest in our Bulletin.  We hope that we can serve you again.</P>";
        echo "<BR><P><FONT size=-1>Privacy: Your email address has been permanently deleted from our database.  You will not receive further unsolicited email from us.</P>";
    }
}


bull_page_footer();



function bull_youAreSubscribed( $ra )
/************************************
 */
{
    echo "<P>Welcome, <B>${ra['email']}</B>!  You are now subscribed to our FREE email bulletin.</P>";
    echo "<P>We hope you enjoy our updates and articles about heritage plants, gardening in Canada, seed saving and plant diversity.</P>";
    echo "<P>At any time, you can unsubscribe just by following the simple instructions at <A HREF='http://www.seeds.ca/bulletin'>http://www.seeds.ca/bulletin</A></P>";
    echo "<BR><P><FONT size=-1>Privacy: Your email address, like all of our member information, is always confidential.  We never exchange or sell our subscribers' contact information.</P>";
}

?>
