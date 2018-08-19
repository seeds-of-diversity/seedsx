<?
// BUG: With NULL page selected I can still press the Save button on the form and insert an empty (default) row


$kfuiDef_taskmanager =
    array( "A" =>
           array( "Label" => "Task",
                  "ListCols" => array( array( "label"=>"Name",           "alias"=>"Users_realname", "w"=>90,  "showColsel"=>1, "colselDefault"=>0 ), // set default programmatically below
                                       array( "label"=>"Category",       "alias"=>"category",       "w"=>100, "showColsel"=>1 ),
                                       array( "label"=>"Title",          "alias"=>"title",          "w"=>250),
                                       array( "label"=>"Deadline",       "alias"=>"enddate",        "w"=>100),
                                  //   array( "label"=>"Start",          "alias"=>"startdate",      "w"=>100),
                                       array( "label"=>"Priority",       "alias"=>"priority",       "w"=>70,  "showColsel"=>1 ),
                                       array( "label"=>"Status",         "alias"=>"status",         "w"=>90,  "showColsel"=>1 ),
                                       array( "label"=>"Private",        "alias"=>"private",        "w"=>10),
                                      ),
                  "ListSize" => 15,
//                "ListSizePad" => 1,
                  "SearchToolCols"  => array( "Name" => "Users.realname",
                                              "Category" => "category",
                                              "Title" => "title",
                                              "Priority" => "priority",
                                              "Status" => "status",
                                              "Details" => "details",
                                              "Comments" => "comments" ),
                  "fnListFilter"    => "Task_listFilter",
                  "fnFormDraw"      => "Task_formDraw",
                  "fnListTranslate" => "Task_listTranslate"
                ) );


include_once( "../site.php" );
include_once( STDINC."SEEDForm.php" );
include_once( STDINC."KeyFrame/KFUIAppSimple.php" );
include_once( STDINC."KeyFrame/KFRForm.php" );
include_once( SEEDCOMMON."siteApp.php" );
include_once( "taskmanager.share.php" );


list($kfdb, $sess) = SiteStartSessionAccount( array("TASK" => "R") );
$bReadonly = !($sess->CanWrite( "TASK" ));
//$kfdb->KFDB_SetDebug(2);

/* Set the Name select to the current user by default.
 */
$l = &$kfuiDef_taskmanager['A']['ListCols'][0];
if( $l['label'] != 'Name' )  die( "ListCol change: expecting zero index" );
$l['colselDefault'] = $sess->GetName();


$today = $kfdb->KFDB_Query1( "SELECT CURDATE()" );

SiteApp_KFUIAppHeader( "Seeds of Diversity Tasks" );


// Kluge1: Remove checkbox value from the parm stream when we don't want it. Otherwise, it stays 1 when the box is checked, so you can't uncheck it.
if( empty($_REQUEST['_kfuA_action']) )  $_REQUEST['private'] = "";
// Kluge2: On action where 'private' is not checked, force a false value into the parm stream
if( !empty($_REQUEST['_kfuA_action']) && !@$_REQUEST['private'] )  $_REQUEST['private'] = 0;
// The above state transitions do not allow toggling of private on consecutive updates. An intermediate page access is needed to reset the parm stream
// by Kluge1 before unchecking via Kluge2.
// There are two problems here: kfui doesn't remove 'private' (or any user parms) from the parm stream, so unchecking can't work because it doesn't
// overwrite the propagated private=1 with any zero value. This could be fixed by using kfui parms. But secondly, there is still no uncheck action to
// tell kfui to reset the field. This can only be done with a force-default, but kfui.DoAction doesn't use that (and it shouldn't). Maybe it could use
// force-defaults if a certain definition is given, but that's heavy handed. Maybe we need to tell kfui what the checkboxes are, so it can force defaults
// on those fields only.



// Get names of all users who have W access to Tasks
$raUsers = array();
$raU1 = SEEDSession_Admin_GetUsersFromPerm( $kfdb, 'TASK', 'W' );
if( count($raU1) ) {
    $dbc = $kfdb->KFDB_CursorOpen( "SELECT _key,realname FROM SEEDSession_Users WHERE _status=0 AND _key IN (".implode(",",$raU1).")" );
    while( $ra = $kfdb->KFDB_CursorFetch($dbc) ) {
        $raUsers[$ra['_key']] = $ra['realname'];
    }
    $kfdb->KFDB_CursorClose( $dbc );
}


if( ($kInit = SEEDSafeGPC_GetInt("consoleAppRowInit")) ) {
    /* Initialize the list with the given row (_key).  Remove this parm from the parm stream.
     *
     * This should be a standard function in the console.
     */
    $_REQUEST['consoleAppRowInit'] = 0;
    unset($_REQUEST['consoleAppRowInit']);
}


$raParms = array( "kfLogFile"    => SITE_LOG_ROOT."taskmanager.log",
                  "fnDrawSearch" => "Task_DrawSearch",
                  "kfuiAppRowInit" => $kInit );
if( $bReadonly )  $raParms['bReadonly'] = true;


KFUIApp_ListForm( $kfdb, $kfrelDef_taskmanager, $kfuiDef_taskmanager, $sess->GetUID(), $raParms );



// END



function Task_DrawSearch( $kfui )
/********************************
 */
{
    global $sess;

    echo "<TABLE><TR><TD>";
    $kfui->Draw( "A", "Search" );
    echo "</TD><TD>".SEEDStd_StrNBSP("",35)."</TD>";
    echo "<TD valign='top'><FORM method='post' action='${_SERVER['PHP_SELF']}'>";
//  HIDDEN FORM PARMS NEED TO BE HERE TO PROPAGATE KFUI STATE - there used to be a public method for this, but it seems to have been replaced with a kfuiControl method
//seems to work fine without them

    echo SEEDForm_Select( "taskfltstatus", array( 0=>"Active Tasks", 1=>"Completed Tasks", 2=>"Overdue Tasks" ),
                          SEEDSafeGPC_Smart('taskfltstatus', array( 0, 1, 2 )),
                          array( "selectAttrs" => "onChange='submit();'" ) );
    echo "&nbsp;&nbsp;&nbsp;";
    echo "<INPUT type=submit value='Show'></FORM></TD></TR></TABLE>";
}


function Task_listFilter()
/*************************
 */
{
    global $sess;

    $fltstat = SEEDSafeGPC_GetInt('taskfltstatus');

    $sComplete = "(status='DONE' OR status='CANCELLED')";

    $cond = "(private=0 OR (private=1 AND fk_SEEDSession_Users='".$sess->GetUID()."'))";

    switch( $fltstat ) {
        case 1: // Completed
            $cond .= "AND $sComplete";
            break;
        case 2: // Overdue
            $cond .= " AND (NOT $sComplete AND enddate IS NOT NULL AND enddate <> '' AND enddate <> '0000-00-00' AND enddate < CURDATE())";
            break;
        default:
            $cond .= " AND NOT $sComplete";
    }

    return( $cond );
}


function Task_listTranslate( $kfr )
/**********************************
 */
{
    global $today;

    $ra = array();

    if( $kfr->value('status') != "DONE" && $kfr->value('status') != "CANCELLED" &&
          substr($kfr->value('enddate'),0,1) == '2' && $kfr->value('enddate') < $today ) {
        $ra['enddate'] = "<B><FONT color=red>".$kfr->value('enddate')."</FONT></B>";
    }

    if( $kfr->value('startdate') == "0000-00-00" )  $ra['startdate'] = "";
    if( $kfr->value('enddate')   == "0000-00-00" )  $ra['enddate'] = "";

    return( $ra );
}


function Task_formDraw( $kfr )
/*****************************
 */
{
    global $kfdb, $sess;
    global $raUsers;
    global $raTaskPriority, $raTaskStatus;

    if( !$kfr->Key() ) $kfr->SetValue("fk_SEEDSession_Users", $sess->GetUID() );    // initialize new record to default to current user

    echo "<P>".KFRForm_Text( $kfr, "Title", "title", 100 )."</P>\n";
    echo "<P>".KFRForm_Text( $kfr, "Category", "category", 30 );
    echo SEEDStd_StrNBSP("", 5);

    echo "Name:&nbsp;";
    echo KFRForm_Select( $kfr, "fk_SEEDSession_Users", $raUsers );
    echo SEEDStd_StrNBSP("", 5);

    echo "Status:&nbsp;";
    echo KFRForm_Select( $kfr, "status", $raTaskStatus );
    echo "</P>\n";

    echo "<P>";
    echo "Priority:&nbsp;";
    echo KFRForm_Select( $kfr, "priority", $raTaskPriority );
    echo SEEDStd_StrNBSP("", 5);

    echo "<INPUT type=checkbox name='private' value=1".($kfr->value('private') ? " CHECKED" : "").">&nbsp;Private";
//  echo KFRForm_Checkbox( $kfr, "_kfuAp_private" )."&nbsp;Private";
    echo SEEDStd_StrNBSP("", 5);
//  echo KFRForm_Text( $kfr, "Start YYYY-MM-DD", "startdate", 15 );
    echo SEEDStd_StrNBSP("", 5);
    echo KFRForm_Text( $kfr, "Deadline <FONT size='-1'>(YYYY-MM-DD)</FONT>", "enddate", 15 );
    echo SEEDStd_StrNBSP("", 5);
    echo "</P>\n";
    echo "<TABLE>";
    echo "<TR>".KFRForm_TextAreaTD( $kfr, "Details:",  "details",  70, 10, "WRAP='SOFT'" )."</TR>";
    echo "<TR>".KFRForm_TextAreaTD( $kfr, "Comments:", "comments", 70,  5, "WRAP='SOFT'" )."</TR>";
    echo "</TABLE>";
    echo "<INPUT type='submit' value='Save'>";
}


?>
