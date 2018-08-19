<?
/* Subscribe/Unsubscribe request control
 *
 * Records request, sends confirmation email
 *
 *  $e   = the email address to subscribe or unsubscribe
 *  $req = "Subscribe" or "Unsubscribe" (capitalized because they come from submit button values)
 */

include_once( "../site.php" );
include_once( SITEINC."sitedb.php" );
include_once( "_bull.php" );
include_once( SEEDCOMMON."siteutil.php" );

$e = @$_REQUEST['e'];
$unsub = (@$_REQUEST['req'] == "Unsubscribe");

if( empty($e) ) { BXStd_HTTPRedirect( "./index.php" ); }

if( !get_magic_quotes_gpc() )  addslashes($e);      // this only changes the string in attack cases; shouldn't be any quotes


$msg_confirm1 =
"Thankyou for your interest in Seeds of Diversity's email Bulletin.\n
This message has been sent to you because your email address was entered
for subscription to our free Bulletin.  To confirm this address and to
begin your subscription, please click on the link below or copy and paste
it into your web browser.\n\n";

$msg_unsub_confirm1 =
"This message has been sent to you because your email address was
entered for removal from our free Bulletin.  To confirm this address
and cancel your subscription, please click on the link below or copy
and paste it into your web browser.\n\n";

$msg_2 =
"-----
Seeds of Diversity never uses unsolicited, automated email to contact
potential members.  If this message has been sent to you in error, or
to report illegitimate use of our subscription service, please contact
our Website Administrator at webmaster@seeds.ca.  We apologise for any
inconvenience.  You may disregard this message and you will not receive
further emails from us.";


bull_page_header();

/* Look up the email in bull_list
 */
$ra = db_query( "SELECT * FROM bull_list WHERE email='$e'" );
$bExists = !@empty( $ra['email'] );

/* status == 0: subscribe has been requested but not confirmed
 * status == 1: subscription is active
 * status == 2: unsubscribe has been requested but not confirmed
 */
if( $unsub ) {
    if( $bExists && $ra['status'] > 0 ) {
        // The email is active - generate a deletion hash and send confirmation
        // OR an unsubscribe request is pending - send the confirmation again in case it was lost

        if( $ra['status'] == 1 ) {
            $c = bull_hash();
            if( !db_exec( "UPDATE bull_list SET hash='$c',status=2,ts2=NOW() WHERE _key=${ra['_key']}" ) ) {
                die( "<P>Sorry, unable to update email list.  Please try again or contact our ".MAILTO_Webmaster." for assistance</P>" );
            }
        } else {
            $c = $ra['hash'];
        }
        $id = $ra['_key'];


        $msg = $msg_unsub_confirm1;
        $msg .= (STD_isLocal ? "http://localhost/seeds.ca" : "http://www.seeds.ca")
                ."/bulletin/confirm.php?req=Unsubscribe&i=$id&c=$c \n\n";
        $msg .= $msg_2;

        if( MailFromOffice( $e, "Seeds of Diversity's Email Bulletin - Please confirm removal", $msg ) ) {
            echo "<P>A confirmation email has been sent to <B>$e</B>.  It is necessary for us to confirm your email address this way to prevent unwanted removal of subscriptions.</P>";
            echo "<P>When you receive the confirmation email, please follow the instructions to confirm your removal from our list.  When we receive your confirmation, your email address will be permanently deleted from our database.</P>";
            echo "<P>Thanks for your past interest in our Email Bulletin.  We hope that we may serve you again.</P>";
        } else {
            echo "<P>There was an error sending a confirmation message to <B>$e</B>.  Please try again or contact our ".MAILTO_Webmaster." for assistance.";
        }
    } else {
        echo "<P>The email address <B>$e</B> is not currently subscribed to Seeds of Diversity's email Bulletin.</P>";
        echo "<P>If you wish to subscribe with this address, <A HREF='${_SERVER['PHP_SELF']}?e=".urlencode($e)."'>Click here</A></P>";
    }
} else {
    if( !$bExists || $ra['status'] == 0 ) {
        // The email is not in our list - create a new record and send confirmation
        // OR a subscribe request is pending - send the confirmation again in case it was lost.

        if( !$bExists ) {
            $c = bull_hash();
            $id = db_insert_autoinc_id( "INSERT INTO bull_list (_key,email,hash,status,ts0) VALUES (NULL,'$e','$c',0,NOW())" );
            if( !$id )  die( "<P>Sorry, unable to update email list.  Please try again or contact our ".MAILTO_Webmaster." for assistance</P>" );
        } else {
            $c = $ra['hash'];
            $id = $ra['_key'];
        }

        $msg = $msg_confirm1;
        $msg .= (STD_isLocal ? "http://localhost/seeds.ca" : "http://www.seeds.ca")
                ."/bulletin/confirm.php?req=Subscribe&i=$id&c=$c \n\n";
        $msg .= $msg_2;

        if( MailFromOffice( $e, "Seeds of Diversity's Email Bulletin - Please confirm subscription", $msg ) ) {
            echo "<P>Thankyou for subscribing to Seeds of Diversity's email Bulletin.  A confirmation email has been ";
            echo "sent to <B>$e</B>.  It is necessary for us to confirm your email address this way to prevent unwanted subscriptions.</P>";
            echo "<P>When you receive the confirmation email, please follow the instructions to confirm your email address and begin your subscription.</P>";
        } else {
            echo "<P>There was an error sending a confirmation message to <B>$e</B>.  Please try again or contact our ".MAILTO_Webmaster." for assistance.";
        }
    } else {
        echo "<P>The email address <B>$e</B> is already subscribed to Seeds of Diversity's Email Bulletin.</P>";
        echo "<P>If you wish to unsubscribe this address, <A HREF='${_SERVER['PHP_SELF']}?e=".urlencode($e)."&req=Unsubscribe'>Click here</A></P>";
    }
}

bull_page_footer();
?>
