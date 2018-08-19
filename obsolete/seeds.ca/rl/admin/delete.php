<?
define( "SITEROOT", "../../" );
define( "RLROOT",   "../" );

define( "RL_pageType", "update" );


/* Delete an rl_cmp item.
 *
 * $i      = rl_cmp_id
 * $action = 'delete': delete the item, '{anything else}': confirm and call self with action=='delete'
 */

$i = intval(@$_REQUEST["i"]);
if( $i < 1 ) {
    die( "<P>Invalid use of this page.</P>" );
}
$action = @$_REQUEST['action'];
if( $action != 'delete' )  $action = 'confirm';


include_once( SITEROOT."site.php" );
include_once( SITEINC."sitedb.php" );
include_once( STDINC ."BXRecord.php" );
include_once( RLROOT  ."_rl_inc.php" );
include_once( SITEINC ."sodlogin.php" );

$la = new SoDLoginAuth;
if( !$la->SoDLoginAuth_Authenticate( $_REQUEST, "W perm_rl" ) ) { exit; }

$rec = new BXRecord($rl_cmp_recdef);
$rec->BXRecord_GetFromDB($i);

$getbackurl = RLROOT."admin/start.php?".$la->login_auth_get_urlparms();

/* confirm/delete the record
 */
BXRecordAdmin_Delete( $action, $la, $rec, $getbackurl, 'rl_cmp_show_from_rec' );

?>
