<?

include_once( "../site.php" );
include_once( SITEINC."siteStart.php" );


list($kfdb, $sess) = SiteStartSession( array("roster" => "R") );


$p_Mode1 = @$_REQUEST["mode1"];  // board | everyone
$p_Mode2 = @$_REQUEST["mode2"];  // lessinfo | moreinfo

$bOk = true;

switch( $p_Mode1 ) {
    case "board":       $bEveryone = false;  break;
    case "everyone":    $bEveryone = true;   break;
    default:            $bOk       = false;
}
switch( $p_Mode2 ) {
    case "lessinfo":    $bLess = true;  break;
    case "moreinfo":    $bLess = false; break;
    default:            $bOk   = false;
}

if( !$bOk ) {
    echo "<FORM method=post>";
    echo "<P>Show:</P>";
    echo "<TABLE><TR><TD width=200>";
    echo "<INPUT type=radio name=mode1 value=board> Directors Only<BR>";
    echo "<INPUT type=radio name=mode1 value=everyone> Directors and Staff</TD>";
    echo "<TD width=200>";
    echo "<INPUT type=radio name=mode2 value=moreinfo> All Info<BR>";
    echo "<INPUT type=radio name=mode2 value=lessinfo> Names and Addresses Only</TD>";
    echo "</TR></TABLE>";
    echo "<BR><BR><INPUT type=submit></FORM>";
    exit;
}


echo "<H2>Seeds of Diversity Canada</H2>";
if( $bEveryone ) {
    echo "<H3>Directors and Staff</H3>";
} else {
    echo "<H3>Board of Directors</H3>";
}
echo "<H3>January 2006</H3>";

echo "<TABLE>";
// the base number for the 866 is 905-372-8983
echo "<TR><TD width=300>Seeds of Diversity Canada</TD><TD>phone 1-866-509-SEED (7333)</TD></TR>";
echo "<TR><TD>P.O. Box 36 Station Q<BR>Toronto, ON  M4T 2L7</TD><TD>http://www.seeds.ca<BR>http://www.semences.ca</TD</TR>";
echo "</TABLE>";

echo "<P>Charitable organization & business number BN 89650 8157 RR0001</P>";


function writePerson( $title, $name, $since, $addr1, $addr2, $phone, $fax, $email )
/******************* **************************************************************
 */
{
    global $bLess;

    $s = "<TR><TD valign=top>".(empty($title) ? "Director" : $title)."</TD>";
    $s .= "<TD valign=top>$name";
    if( !$bLess && !empty($since) ) {
        $s .= " (director since $since)";
    }
    if( !empty($addr1) ) {
        $s .= "<BR>".$addr1;
    }
    if( !empty($addr2) ) {
        $s .= "<BR>".$addr2;
    }
    $s .= "</TD><TD valign=top>";
    if( !$bLess && !empty($phone) ) {
        $s .= $phone."<BR>";
    }
    if( !$bLess && !empty($fax) ) {
        $s .= $fax."<BR>";
    }
    if( !$bLess && !empty($email) ) {
        $s .= $email;
    }
    $s .= "</TD></TR>\n";
    return( $s );
}


if( $bEveryone ) {
    echo "<H4>Directors</H4>";
}

echo "<TABLE cellpadding=10>";

//echo writePerson( "Past President", "Hugh Daubeny", "1996", "3558 West 15th Avenue,", "Vancouver  BC  V6R 2Z4",
//                  "604-731-8537", "", "hugh@seeds.ca" );

echo writePerson( "President", "Jim Ternier", "1996", "Box 2758", "Humboldt  SK  S0K 2A0",
                  "306-682-1475", "", "jim@seeds.ca" );

echo writePerson( "Vice President", "Garrett Pittenger", "2004 and 1992-2000", "RR 3, 16812 Humber Station Rd.", "Caledon, ON  L7E 0Z1",
                  "905-880-4848", "", "garrett@seeds.ca" );

//mbrid:5343
echo writePerson( "Treasurer", "Helen Mills", "2004", "81 Glen Rd", "Toronto ON M4W 2V5",
                  "416-731-4582", "", "helen@seeds.ca" );

// Gwynne stays with her brother when in Toronto: 416-963-8023
echo writePerson( "Secretary", "Gwynne Basen", "1998", "63 Nelson Ave,", "Outremont QC H2V 3Z8",
                  "Phone & fax 514-272-5185", "", "gwynne@seeds.ca" );

echo writePerson( "Director of Fundraising", "Ghan Chee", "2003", "1381 Lansdowne Ave.", "Toronto, ON M6H 3Z9",
                  "416-658-1698", "Fax: 416-658-0810", "ghan@seeds.ca" );

echo writePerson( "", "Joanne Henderson", "2006", "430 Gus Wuori Rd.", "Thunder Bay  ON  P7G 2G6",
                  "807-767-5897", "", "joanne@seeds.ca" );

echo writePerson( "", "Jane Seabrook", "2006", "1343 Lincoln Rd.", "Fredericton  NB  E3B 8J5",
                  "506-459-7862", "", "jane@seeds.ca" );

echo writePerson( "", "Anne Goodman", "2006", "79 Alameda Avenue", "Toronto  ON  M6C 3W4",
                  "416-657-8095", "", "anne@seeds.ca" );

echo writePerson( "", "Frédéric Sauriol", "2006", "1555 Coteau des Hetres", "St-Andre-d'Argenteuil  QC  J0V 1X0",
                  "450-562-0104", "", "frederic@semences.ca" );

echo "</TABLE>";


if( $bEveryone ) {
    echo "<H4>Staff and Contractors</H4>";

    echo "<TABLE cellpadding=10>";
    echo writePerson( "Executive Director", "Bob Wildfong", "", "68 Dunbar Rd South", "Waterloo ON N2L 2E3",
                      "Phone & fax: 519-886-7542", "", "bob@seeds.ca" );

    // Judy: 293 O'Connor Court, Cobourg ON K9A 5X5 / 905-377-9481
    echo writePerson( "Office Manager", "Judy Newman", "", "", "", "Use P.O. Box and 1-866 number", "", "judy@seeds.ca" );

    echo writePerson( "Magazine Editor", "Elaine Freedman", "", "", "", "416-762-8361", "", "magazine@seeds.ca" );
//  echo writePerson( "Publications", "Jean-Michel Komarnicki", "", "", "", "905-", "", "" );

    echo "</TABLE>";

}

/*

Lower Mainland BC - Rep.
Hugh Daubeny
604-731-8537

Vancouver Island Rep
Shirley Bellows
shirley@seeds.ca

Interior BC Rep
Gregoire Lamoureux
250-226-7302
gregoire@seeds.ca

Alberta Rep
Barb Philips
780-674-6225
barb@seeds.ca

Quebec Rep
Diane Joubert
450-466-6004
diane@semences.ca

Ontario Rep
Talia Erlich
416-985-0435
talia@seeds.ca

*/



?>




