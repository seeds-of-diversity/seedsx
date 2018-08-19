<?
/* mbr_PPipn.php
 *
 * PayPal calls here to notify us of payment
 */

/* The following code was adapted from a sample at http://www.paypal.com/cgi-bin/webscr?cmd=p/xcl/rec/ipn-code-outside
 */

define( "SITEROOT", "../" );
require_once( SITEROOT."site.php" );
require_once( SEEDCOMMON."siteStart.php" );
require_once( STDINC."KeyFrame/KFRelation.php" );
require_once( MBR_ROOT."_mbr.php" );


class SoD_PP_IPN {
    var $response = "";

    function SoD_PP_IPN() {

    }

    function Validate() {
        $ok = false;
//$this->mail("Start Validate");

        $req = 'cmd=_notify-validate';
        foreach( $_POST as $key => $value ) {
            $value = urlencode(SEEDSafeGPC_MagicStripSlashes($value));
            $req .= "&$key=$value";
        }

        // post back to PayPal system to validate
        $errno = 0;
        $errstr = "";
        $header = "POST /cgi-bin/webscr HTTP/1.0\r\n";
        $header .= "Content-Type: application/x-www-form-urlencoded\r\n";
        $header .= "Content-Length: " . strlen($req) . "\r\n\r\n";

        $fp = fsockopen ('www.paypal.com', 80, $errno, $errstr, 30);
        if( $fp ) {
            fputs( $fp, $header . $req );
            $s = "";
            while( !feof($fp) ) {
                $s = fgets( $fp, 1024 );    // get each line of the header and content.  The last line is the response.
            }
            $this->response = $s;
            fclose ($fp);
            $ok = true;
        } else {
            $this->log( "HTTP Error: $errno, $errstr\n$req" );
            $this->mail( "HTTP Error: $errno, $errstr\n$req" );
        }
//$this->mail("End Validate $ok");
        return( $ok );
    }


    function log( $s ) {
        if( $fp = fopen( SITE_LOG_ROOT."ppipn.log", "a" ) ) {
            $out = sprintf( "-----\n%d\n%s\n%s\n", time(), date( "D M j G:i:s T Y", time() ), $s );
            fwrite( $fp, $out );
            fclose( $fp );
        }
    }

    function mail( $s ) {
        $date = date( "D M j G:i:s T Y", time() );
        mail( "bob@seeds.ca", "[$date] PayPal Payment Notification", $s );
    }

    function postedParms() {
        $s = "Parms:\n";
        $s .= "name = ".$_POST['first_name']." ".$_POST['last_name']."\n";
	$s .= "item = ".$_POST['item_name']."\n";
	$s .= "amount = ".$_POST['mc_gross']."\n";
	$s .= "email = ".$_POST['payer_email']."\n";
	$s .= "----------\n";
        foreach( $_POST as $k => $v ) {
            $s .= "$k : $v\n";
        }
        return( $s );
    }
};


$ipn = new SoD_PP_IPN();

$ipn->Validate() or die;
$ipn->mail( $ipn->response." response from PP verification.\n".$ipn->postedParms() );
$ipn->log( $ipn->response." response from PP verification.\n".$ipn->postedParms() );

if( $ipn->response != "VERIFIED" ) {
    $ipn->mail( $ipn->response." response from PP verification.\n".$ipn->postedParms() );
    die;
}


/*  The IPN is real.   Process the POSTed parms.
 */

$kfdb = SiteKFDB() or die( "Cannot connect to database" );
$kfrel = new KeyFrameRelation( $kfdb, $kfrdef_mbrPending, 0 );

$kfrel->SetLogFile( SITE_LOG_ROOT."ppipn.log" );   // write kfr log to the same log file that parms go to

$rowid = intval($_POST['invoice']);
if( !$kfr = $kfrel->GetRecordFromDBKey($rowid) ) {
    $ipn->log( "Can't get row $rowid" );
    $ipn->mail( "PPIPN: Can't get row $rowid" );
    die;
}

$kfr->SetValue("pp_name",           $_POST['first_name']." ".$_POST['last_name'] );
$kfr->SetValue("pp_txn_id",         $_POST['txn_id'] );
$kfr->SetValue("pp_receipt_id",     $_POST['receipt_id'] );
$kfr->SetValue("pp_payer_email",    $_POST['payer_email'] );
$kfr->SetValue("pp_payment_status", $_POST['payment_status'] );


switch( $_POST['payment_status'] ) {
    case "Completed":
        if( $kfr->value('pay_status') == MBR_PS_CONFIRMED || $kfr->value('eStatus') == 'New' ) {
            $kfr->SetValue('pay_status', MBR_PS_PAID );
            $kfr->SetValue('eStatus', 'Paid' );
        } else {
            $ipn->log( "Got a PayPal Completed with MBR_PS=".$kfr->value('pay_status') );
            $ipn->mail( "Got a PayPal Completed with MBR_PS=".$kfr->value('pay_status') );
        }
        break;
    default:
        break;
}


// check the payment_status is Completed
// check that txn_id has not been previously processed
// check that receiver_email is your Primary PayPal email
// check that payment_amount/payment_currency are correct
// process payment




if( !$kfr->PutDBRow() ) {
    $ipn->log( "Can't Put row $rowid" );
    $ipn->mail( "PPIPN: Can't Put row $rowid" );
    die;
}


exit;


// assign posted variables to local variables
$item_name        = $_POST['item_name'];
$item_number      = $_POST['item_number'];
$payment_status   = $_POST['payment_status'];
$payment_amount   = $_POST['mc_gross'];
$payment_currency = $_POST['mc_currency'];
$txn_id           = $_POST['txn_id'];
$receiver_email   = $_POST['receiver_email'];
$payer_email      = $_POST['payer_email'];





exit;

require("paypal_ipn.php");

// PayPal will send the information through a POST
$paypal_info = $HTTP_POST_VARS;

// To disable https posting to PayPal uncomment the following
// $paypal_ipn = new paypal_ipn($paypal_info, "");

// Then comment out this one
$paypal_ipn = new paypal_ipn($paypal_info);

// where to contact us if something goes wrong
$paypal_ipn->error_email = "bob@seeds.ca";

// We send an identical response back to PayPal for verification
$paypal_ipn->send_response();

// PayPal will tell us whether or not this order is valid.
// This will prevent people from simply running your order script
// manually
if( !$paypal_ipn->is_verified() )
{
    // bad order, someone must have tried to run this script manually
    $paypal_ipn->error_out("Bad order (PayPal says it's invalid)");
}

// payment status
switch( $paypal_ipn->get_payment_status() )
{
    case 'Completed':   // order is good
        break;

    case 'Pending':
        // money isn't in yet, just quit.
        // paypal will contact this script again when it's ready
        $paypal_ipn->error_out("Pending Payment");
        break;

    case 'Failed':
        // whoops, not enough money
        $paypal_ipn->error_out("Failed Payment");
        break;

    case 'Denied':
        // denied payment by us
        // not sure what causes this one
        $paypal_ipn->error_out("Denied Payment");
        break;

    default:
        // order is no good
        $paypal_ipn->error_out("Unknown Payment Status" . $paypal_ipn->get_payment_status());
        break;
}



// If we made it down here, the order is verified and payment is complete.
// You could log the order to a MySQL database or do anything else at this point.

// Email the information to us
$date = date("D M j G:i:s T Y", time());

$message .= "\n\nThe following info was received from PayPal - $date:\n\n";
@reset($paypal_info);
while( @list($key,$value) = @each($paypal_info) )
{
    $message .= "$key : $value\n";
}
mail("bob@seeds.ca", "[$date] PayPal Payment Notification", $message);

            if( $fp = fopen( LOG_ROOT."ppipn.log", "a" ) ) {
                $out = sprintf( "-----\n%d\n%s\n", time(), $message );
                fwrite( $fp, $out );
                fclose( $fp );
            }

?>
