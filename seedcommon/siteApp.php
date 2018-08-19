<?php
/* Helpers for online applications.
 */

function SiteApp_KFUIAppHeader( $title, $tail = "" )  { SiteApp_Header( $title, $tail ); }


function SiteApp_Header( $title, $tail = "" )
/********************************************
 */
{
    echo "<TABLE border=0 width='100%'><TR><TD><SPAN style='font-size:18pt;font-weight:bold;'>$title</SPAN></TD>";
    echo "<TD valign='top'>$tail</TD>";
    echo "<TD style='float:right'>";
    echo "<A HREF='".SITEROOT."login/' style='font-size:10pt;color:green;text-decoration:none'>Home</A>";
    echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
    echo "<A HREF='".SITEROOT."login/logout.php' style='font-size:10pt;color:green;text-decoration:none'>Logout</A>";
    echo "</TD></TR></TABLE>";
}

?>
