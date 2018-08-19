<?
/* Dump all Events tables to output
 */

define( "SITEROOT", "../../" );
include_once( SITEROOT."site.php" );
include_once( SITEINC."sitedb.php" );
include_once( STDINC ."dbutil.php" );
include_once( SITEINC ."sodlogin.php" );


$la = new SoDLoginAuth;
if( !$la->SoDLoginAuth_Authenticate( $_REQUEST, "W perm_ev" ) ) { exit; }

header( "Content-Type: text/plain" );

echo "--- EVENT_PAGES\n";
echo db_dumptable( "event_pages", "page_code" );
echo "\n";

echo "--- EVENT_ITEMS\n";
echo db_dumptable( "event_items", "_rowid" );
echo "\n";

?>
