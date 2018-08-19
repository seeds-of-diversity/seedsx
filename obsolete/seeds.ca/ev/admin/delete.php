<?
define( "SITEROOT", "../../" );

header("Location: https://office.seeds.ca");

/* Delete an event item.
 *
 * $i      = event item code
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
include_once( EV_ROOT ."_ev_inc.php" );
include_once( SITEINC ."sodlogin.php" );


$la = new SoDLoginAuth;
if( !$la->SoDLoginAuth_Authenticate( $_REQUEST, "W perm_ev" ) ) { exit; }

$rec = new dbPhrameRecord($evitem_recdef,$la->LoginAuth_UID());
$rec->dPR_GetDBRow($i);

$getbackurl = EV_ROOT."admin/page.php?p=".$rec->dPR_value('page_code')."&".$la->login_auth_get_urlparms();

/* confirm/delete the record
 */
//BXRecordAdmin_Delete( $action, $la, $rec, $getbackurl, 'event_item_show_from_rec' );
    if( $action == 'delete' ) {
        // Delete the record
        $query = "UPDATE {$rec->tablename} SET _status=2 WHERE _rowid=$i";
        echo db_exec( $query )
                ? "<P>The delete succeeded</P>"
                : "<P>The delete did not succeed.</P>".db_errmsg($query);
        echo "<P><A HREF='$getbackurl'>Click here to continue.</A></P>";
    } else {
        // Confirm
        $raPage = event_page_get_from_item( $rec->dPR_rowid() );
        event_item_show_rec( $rec, $raPage, "*", 0 );

        echo "<HR>";
        echo "<P>Are you sure you want to delete this record?</P>";
        echo "<FORM action='{$_SERVER['PHP_SELF']}'>";
        echo $la->login_auth_get_hidden();
        echo "<INPUT TYPE=HIDDEN NAME=i VALUE='".$rec->dPR_rowid()."'>";
        echo "<INPUT TYPE=HIDDEN NAME=action VALUE='delete'>";
        echo "<P><INPUT TYPE=SUBMIT VALUE='Delete'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
        echo "<A HREF='$getbackurl'>Cancel</A></TD></TR>";
        echo "</FORM>";
    }

?>
