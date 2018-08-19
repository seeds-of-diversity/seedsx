<?
/* Main Login page for SEEDS.CA Administration
 */

include_once( "../site.php" );
include_once( SITEINC."sitedb.php" );
include_once( SITEINC ."sodlogin.php" );

// Authenticate has to happen before any output since it sets cookies

$la = new SoDLoginAuth;
if( !$la->SoDLoginAuth_Authenticate( $_REQUEST, "" ) ) { exit; }

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Admin Start</title>
<LINK REL="SHORTCUT ICON" HREF="<?= SITEROOT ?>favicon.ico">

<STYLE>
.adm_lines {margin-left: 5em;}
#admin     {margin-left: 2em;}
#admin h3  {font-family: verdana, helvetica, sans-serif;
            font-weight: normal;
           }
</STYLE>
</head>

<body>

<?
function line( $url, $label ) { global $la; return( "<P><A HREF='".SITEROOT.$url."?".$la->LoginAuth_GetUrlParms()."'>$label</A></P>" ); }


echo "<IMG SRC='".SITEIMG."logo_BI.gif'>";
echo "<H2>Seeds of Diversity Administration - Welcome ".$la->realname."</H2>";

echo "<DIV id=admin>";

if( $la->LoginAuth_CanEdit( "MBRORDER" ) ) {
    echo "<H3>Members</H3>";
    echo "<DIV class=adm_lines>";
    echo line( "mbr/admin/mbr_order_report.php", "New Memberships, Donations and Orders Online" );
    echo "</DIV>";
}

if( $la->LoginAuth_CanEdit( "EV" ) ) {
    echo "<H3>Events</H3>";
    echo "<DIV class=adm_lines>";
    echo line( "ev/admin/start.php",   "Update Events List" );
    echo line( "ev/admin/evadmin.php", "Update Events List - New Way!" );
//  echo line( "ev/admin/dump.php",    "Get Full Dump of Events List (for backup)" );
    echo "</DIV>";
}

if( $la->LoginAuth_CanEdit( "RL" ) ) {
    echo "<H3>Resource List</H3>";
    echo "<DIV class=adm_lines>";
    echo line( "rl/admin/start.php", "Update Resource List" );
    echo "</DIV>";
}


if( $la->LoginAuth_CanEdit( "CSCI" ) ) {
    echo "<H3>Canadian Seed Catalogue Inventory Administration</H3>";
    echo "<DIV class=adm_lines>";
    echo line( "hpd/admin/csci_insert.php", "Canadian Seed Catalogue Inventory - Insert New Varieties" );
    echo line( "hpd/admin/csci_update.php", "Canadian Seed Catalogue Inventory - Update/Delete" );
    echo "</DIV>";
}


if( $la->LoginAuth_CanRead( "BULL" ) || $la->LoginAuth_CanRead( "MBR" ) ) {
    echo "<H3>Email and e-Bulletin</H3>";
    echo "<DIV class=adm_lines>";

    if( $la->LoginAuth_CanRead( "BULL" ) && $la->LoginAuth_CanRead( "MBR" ) ) {
        echo line( "int/email_list.php", "Get email lists of Members and  e-Bulletin subscribers" );
    }
    if( $la->LoginAuth_CanEdit( "BULL" ) ) {
        echo line( "bulletin/admin.php", "Add or Remove e-Bulletin subscribers" );
    }
    echo "</DIV>";
}

if( $la->LoginAuth_CanEdit( "TASK" ) ) {
    echo "<H3>Task Manager</H3>";
    echo "<DIV class=adm_lines>";
    echo line( "int/taskmanager.php", "Task Manager" );
    echo "</DIV>";
}
echo "</DIV>";

?>

</body>
</html>
