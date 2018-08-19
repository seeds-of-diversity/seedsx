<?

// This only works with details="We're having fun" because of magic_quotes
// if( !get_magic_quotes_gpc )  escape( str );

// use addslashes() and htmlspecialchars(,ENT_QUOTES) on insert, update


define( "SITEROOT", "../../" );

/* Insert or update an event item.
 * This is called from the Add/Edit Event page.
 *
 * $i = "new" OR event item code
 *
 * $p            = event page code (only if $i=="new")
 * $title(_fr)   = event title
 * $city         = city
 * $province     = province abbreviation
 * $month        = the month (1..12)
 * $day          = the day (1..31)
 * $date_alt(_fr) = alternate date string
 * $time         = time string
 * $details(_fr) = details of event
 */

$p = intval(@$_REQUEST["p"]);
$i = @$_REQUEST["i"];
if( $i != "new" ) {
    $i = intval($i);
}
if( ($i < 1 && $i != "new") || $p < 1 ) {
    die( "<P>Invalid use of this page.</P>" );
}


/*
 *$title      = @$_REQUEST["title"];
 *$title_fr   = @$_REQUEST["title_fr"];
 *$city       = @$_REQUEST["city"];
 *$province   = @$_REQUEST["province"];
 *$month      = @$_REQUEST["month"];
 *$day        = @$_REQUEST["day"];
 *$date_alt   = @$_REQUEST["date_alt"];
 *$date_alt_fr = @$_REQUEST["date_alt_fr"];
 *$time       = @$_REQUEST["time"];
 *$details    = @$_REQUEST["details"];
 *$details_fr = @$_REQUEST["details_fr"];
 */

include_once( SITEROOT."site.php" );
include_once( EV_ROOT."_ev_inc.php" );
include_once( SITEINC."sodlogin.php" );


$la = new SoDLoginAuth;
if( !$la->SoDLoginAuth_Authenticate( $_REQUEST, "W EV" ) ) { exit; }

$kfdb = SiteKFDB() or die( "Cannot connect to database" );
$kfr = new KeyFrameRecord( $kfdb, $kfrdef_EVItems, $la->LoginAuth_UID() );
$kfr->kfr_SetLogFile( LOG_ROOT."evadmin.log" );
//$kfdb->KFDB_SetDebug(2);

// compose the record from the url parms
if( $i == "new" ) {
    $kfr->kfr_SetDefault( array("ev_pages"=> $p) );
} else {
    if( !$kfr->kfr_GetDBRow($i))  die( "Error: Cannot find record to update" );
}
$kfr->kfr_GetFromArrayGPC($_REQUEST,false); // do not force defaults if fields missing in gpc

/* Validate input parms
 */
if( $kfr->value('Page_type') == 'EV' && $kfr->value('Page_bEN') && $kfr->kfr_Empty('title') )    die( "<P>English Title is required.  Go back.</P>" );
if( $kfr->value('Page_type') == 'EV' && $kfr->value('Page_bFR') && $kfr->kfr_Empty('title_fr') ) die( "<P>French Title is required.  Go back.</P>" );
if( $kfr->kfr_Empty('city') )                                                                    die( "<P>City is required.  Go back.</P>" );
if( !checkdate( $kfr->value('month'), $kfr->value('day'), $kfr->value('Page_year') ) )           die( "<P>Date is invalid.  Go back.</P>" );

/* Insert/Update the row
 */
if( !$kfr->kfr_PutDBRow() )  die( "<P>The update did not succeed.</P>".$kfdb->KFDB_GetErrMsg() );

// show the new/updated record
$kfr->kfr_GetDBRow($kfr->kfr_Rowid());  // all fields should be accurate, but reload for paranoia's sake
event_item_show_kfr( $kfr, "*", 0 );    // show in non-edit mode for verification
echo "<HR>";

echo "<P><A HREF='".EV_ROOT."admin/page.php?p=$p&".$la->LoginAuth_GetUrlParms()."'>The event has been  updated.  Click here to return to the events page.</A></P>";

?>
