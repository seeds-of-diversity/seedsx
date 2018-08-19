<?

// set _created and _updated in insert/update
// use htmlspecialchars(,ENT_QUOTES) on insert, update


define( "SITEROOT", "../../" );
define( "RLROOT",   "../" );

define( "RL_pageType", "update" );

/* Insert or update a record
 * This is called from the Add/Edit Event page.
 *
 * $i = "new" OR rowid
 */

$i = @$_REQUEST["i"];
if( $i != "new" ) {
    $i = intval($i);
}
if( $i < 1 && $i != "new" ) {
    die( "<P>Invalid use of this page.</P>" );
}


include_once( SITEROOT."site.php" );
include_once( SITEINC."sitedb.php" );
include_once( STDINC ."BXRecord.php" );
include_once( RLROOT  ."_rl_inc.php" );
include_once( SITEINC ."sodlogin.php" );

$la = new SoDLoginAuth;
if( !$la->SoDLoginAuth_Authenticate( $_REQUEST, "W perm_rl" ) ) { exit; }

// compose the record from the url parms
$rec = new BXRecord($rl_cmp_recdef);
$rec->BXRecord_GetFromArrayGPC($_REQUEST);


/* Validate input parms
 */
if( $rec->BXR_empty('name_en') )            die( "<P>English Name is required.  Go back.</P>" );

// xlat is a checkbox: value not sent if unchecked
if( $rec->BXR_value('xlat') != 1 )  $rec->BXR_setValue( 'xlat', 0 );
if( $rec->BXR_value('supporter') != 1 )  $rec->BXR_setValue( 'supporter', 0 );

// cat_cost is the textbox, cat_cost1==0 for Free, -1 for Specified in Description
if( @$_REQUEST['cat_cost1'] == 1  && intval($rec->BXR_value('cat_cost')) < 1 )  die( "<P>Catalogue cost is required.  Go back.</P>" );
if( @$_REQUEST['cat_cost1'] == 0  )  $rec->BXR_setValue( 'cat_cost', 0 );
if( @$_REQUEST['cat_cost1'] == -1 )  $rec->BXR_setValue( 'cat_cost', -1 );


if( $i == "new" ) {
    $query = $rec->BXRecord_MakeInsertCmd( "", "" );
} else {
    $query = $rec->BXRecord_MakeUpdateCmd( "", "rl_cmp_id=$i" );
}

$result = db_exec( $query );
if( !$result )  die( "<P>The update did not succeed.</P>".db_errmsg($query) );

// show the new/updated record
$rec2 = new BXRecord($rl_cmp_recdef);
if( $i == "new") { $rec2->BXRecord_GetDBLast(); }
    else         { $rec2->BXRecord_GetFromDB( $i ); }
//$ra = ($i == "new") ? db_query( "SELECT * FROM rl_companies ORDER BY rl_cmp_id DESC" )
//                    : db_query( "SELECT * from rl_companies WHERE rl_cmp_id=". $i );
//rl_cmp_show( $ra, "*", 0 );
rl_cmp_show_from_rec( $rec2 );
echo "<HR>";

echo "<P><A HREF='".RLROOT."admin/start.php?".$la->login_auth_get_urlparms()."'>The company has been updated.  Click here to return to the main page.</A></P>";

?>
