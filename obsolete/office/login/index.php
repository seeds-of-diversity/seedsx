<?php
/* Main Login page for office.seeds.ca Administration
 */

include_once( "../site.php" );
include_once( SEEDCOMMON."console/console01.php" );
//include_once( SITEROOT."int/taskmanager.share.php" );

list($kfdb, $sess, $lang) = SiteStartSessionAccount( array() );   // requires a valid login, no specific perms required

$oC = new Console01( $kfdb, $sess );
$oLP = new SiteStartLoginPage( $sess, $lang );

$sBody = "";

/* Tasks List
 */
$raTasks = array(); //TasksGetUrgentList( $kfdb, $sess );
if( count($raTasks) ) {
    $sBody .= "<TABLE align=right width=20% class='console01_controlbox'>"
             ."<TR><TD class='console01_controlbox_label'>Tasks</TD></TR>"
             ."<TR><TD><TABLE border=0 cellpadding=5>";
    $s1 = $s2 = $s3 = $s4 = "";
    foreach( $raTasks as $rt ) {
        $bOverdue = ($rt['enddate'] && $rt['enddate'] < date('Y-m-d'));
        $bNow = $rt['priority'] == 'NOW';

        $s = "<TR><TD valign='top'><A HREF='".SITEROOT."int/taskmanager.php?consoleAppRowInit={$rt['_key']}'>{$rt['title']}</TD>"
        ."<TD valign='top'>"
        .($bNow ? "<FONT color='red'>" : "").$rt['priority'].($bNow ? "</FONT>" : "")."<BR>"
        .$rt['status']."<BR>"
        .($bOverdue ? "<FONT color='red'>" : "").$rt['enddate'].($bOverdue ? "</FONT>" : "")."</TD>"
        ."</TR>";
        // output overdue tasks first, then NOW, then those with dates coming soon, then the rest (URGENT)
        if( $bOverdue ) {
            $s1 .= $s;
        } else if( $bNow ) {
            $s2 .= $s;
        } else if( $rt['enddate'] ) {
            $s3 .= $s;
        } else {
            $s4 .= $s;
        }
    }
    $sBody .= $s1.$s2.$s3.$s4."</TABLE></TD></TR></TABLE>";
}


$raLoginDef = array(
    array( "Members, Volunteers, Donors, Contacts",
           "Membres, Volontaires, Donateurs, Contacts",
           array(
               array( "mbr/mbr_contacts.php",    "R MBR",         "Contact Database / Member Administration" ),   // W required to admin
               array( "mbr/mbr_upload.php",      "A MBR",         "Upload Access File to Contact Database" ),
               array( "mbr/checkout.php",        "W MBRORDER",    "Enter Orders and Payments" ),
               array( "mbr/mbr_order_report.php","R MBRORDER",    "View Orders and Payments" ),
               array( "mbr/mbr_mailsetup.php",   "W MBRMAIL",     "Send Bulk Email" ),
               array( "mbr/mbr_email.php",       "R MBRMAIL",     "Email addresses from members and e-Bulletin" )
        ) ),
    array( "Office", "Bureau",
           array(
               array( "",                        "R DocRepMgr",   "Office Documents",                      "", "http://seeds.ca/office/d/docedit.php" ),
               array( "int/taskmanager.php",     "R TASK",        "Task Manager" ),
               array( "int/pay",                 "W PAY",         "Time Tracking and Reimbursements" )
        ) ),
    array( "Web site", "Site Web",
           array(
               array( "",                        "W events",      "Update Events Lists (Seedy Saturdays)", "", "http://seeds.ca/office/ev_admin.php" ),
               array( "sl/sl_sources.php",       "W SLSources",   "Seed Companies Lists" ),
        ) ),
    array( "Seed Library", "Biblioth&egrave;que des semences",
           array(
               array( "sl/sl_admin.php",         "R SL",          "Seed Library Database" ),
        ) ),
    array( "Member Seed Directory", "Catalogue de semences (pour les membres)",
           array(
               array( "ONE-OF",
                      array( "int/sed/sed.php",  "A sedadmin",    "Edit and Admin Seed Directory, with Reports" ),
                      array( "int/sed/sed.php",  "W sedadmin",    "Edit Seed Directory, with Reports" ),
                      array( "int/sed/sed.php",  "R sedadmin",    "View Seed Directory and Reports" ) ),
        ) ),
    array( "Public (anyone can see this)", "",
           array(
               array( "public/logos",            "PUBLIC",        "Download Logos" ),
        ) ),
    array( "My Account", "Mon compte",
           array(
               array( "",                        "PUBLIC",        "Change Password", "", "${_SERVER['PHP_SELF']}?sessioncmd=changepwd" ),
        ) ),
);

//page_link( "Garlic",                                    "W gcgcadmin",  "gcgc/gcgc_admin.php",             "Manage Garlic Growers, Varieties, Samples" );

/*
 *if( $la->LoginAuth_CanEdit( "CSCI" ) ) {
 *    echo "<H3>Canadian Seed Catalogue Inventory Administration</H3>";
 *    echo "<DIV class=adm_lines>";
 *    echo line( "hpd/admin/csci_insert.php", "Canadian Seed Catalogue Inventory - Insert New Varieties" );
 *    echo line( "hpd/admin/csci_update.php", "Canadian Seed Catalogue Inventory - Update/Delete" );
 *    echo "</DIV>";
 *}
 *
 *if( $la->LoginAuth_CanRead( "BULL" ) || $la->LoginAuth_CanRead( "MBR" ) ) {
 *    echo "<H3>Email and e-Bulletin</H3>";
 *    echo "<DIV class=adm_lines>";
 *
 *    if( $la->LoginAuth_CanRead( "BULL" ) && $la->LoginAuth_CanRead( "MBR" ) ) {
 *        echo line( "int/email_list.php", "Get email lists of Members and  e-Bulletin subscribers" );
 *    }
 *    if( $la->LoginAuth_CanEdit( "BULL" ) ) {
 *        echo line( "bulletin/admin.php", "Add or Remove e-Bulletin subscribers" );
 *    }
 *    echo "</DIV>";
 *}
 */

$sBody .= $oLP->DrawLogin( $raLoginDef );

echo $oLP->DrawPage( "Administration", $oC->Style(), $sBody );

?>
