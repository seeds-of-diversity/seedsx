<?

/* CRON calls this @daily
 *
 * Since the cron user runs this, php doesn't have all the modules that are installed via httpd.
 * Crucially, the database module is not available (on the current system as of this comment).
 * This script can perform operations that do not require db access.  To access the full features of httpd-php,
 * it issues an http request to the cronwww.php script.
 */

// Do non-db scripts here

mail( "bob@seeds.ca", "cron.php executed on office", "" );


// Call cronwww.php to do db scripts

$ch = curl_init();
curl_setopt( $ch, CURLOPT_URL, "http://office.seeds.ca/int/cronwww.php" );
curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
curl_setopt( $ch, CURLOPT_REFERER, "office.seeds.ca" );
$sResponse = curl_exec( $ch );
curl_close( $ch );

?>
