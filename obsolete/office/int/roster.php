<?php

/*
    James A. Ternier
    Nov 18 / 44

    Garrett H. Pittenger
    Dec 21 / 45

    Gwynne C. Basen
    Nov 20 / 49

    Helen M. Mills
    Apr 6 / 51

    Dr. Janet E. Seabrook
    Apr 2 / 38

    JoAnne R. Henderson
    Jan 26 / 51

    Ghan Chee
    Oct 19 / 52

    Dr. Anne Goodman
    Nov 29 / 50

    Frederic E. Sauriol
    Aug 24 / 70

    Francois J. Lebel
    Jan 8 / 61

    Dr. Vera G. Etches
    Sep 9 / 75



*/


include_once( "../site.php" );
include_once( SEEDCOMMON."siteStart.php" );


list($kfdb, $sess) = SiteStartSessionAccount( array("roster" => "R") );


$p_Mode1 = @$_REQUEST["mode1"];  // board | everyone
$p_Mode2 = @$_REQUEST["mode2"];  // lessinfo | moreinfo

$bDebug = @$_REQUEST["debug"];

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
    echo "<FORM method='post' action='${_SERVER['PHP_SELF']}'>";
    echo "<TABLE><TR><TH>Choose people</TH><TH>Choose amount of info</TH></TR><TR><TD width=200>";
    echo "<INPUT type=radio name=mode1 value=board> Directors Only<BR>";
    echo "<INPUT type=radio name=mode1 value=everyone> Directors and Staff</TD>";
    echo "<TD width=200>";
    echo "<INPUT type=radio name=mode2 value=moreinfo> All Info<BR>";
    echo "<INPUT type=radio name=mode2 value=lessinfo> Names and Addresses Only</TD>";
    echo "</TR></TABLE>";

    if( $sess->GetUID() == 1499 ) {
        echo "<BR><BR><INPUT type=checkbox name=debug value=1> debug";
    }
    echo "<BR><BR><INPUT type=submit></FORM>";
    exit;
}


echo "<H2>Seeds of Diversity Canada</H2>";
if( $bEveryone ) {
    echo "<H3>Directors and Staff</H3>";
} else {
    echo "<H3>Board of Directors</H3>";
}
echo "<H3>".date( "F Y" )."</H3>";

echo "<TABLE>";
// the base number for the 866 is 905-372-8983
echo "<TR><TD width=300>Seeds of Diversity Canada</TD><TD>phone 1-866-509-SEED (7333)</TD></TR>";
echo "<TR><TD>P.O. Box 36 Station Q<BR>Toronto, ON  M4T 2L7</TD><TD>http://www.seeds.ca<BR>http://www.semences.ca</TD</TR>";
echo "</TABLE>";

echo "<P>Charitable organization & business number BN 89650 8157 RR0001</P>";


function writePerson( $title, $name, $since, $addr1, $addr2, $phone, $fax, $email )
/**********************************************************************************
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


function writePerson2( $mbrid, $since, $parms = array() )
/********************************************************
 */
{
    global $kfdb, $bDebug;

    $ra = $kfdb->KFDB_QueryRA( "SELECT * FROM mbr_contacts WHERE _key=$mbrid" );

    /* parms starting with m_ override database fields
     */
    if( $bDebug && count($parms) )  echo "<TR><TD style='color:grey'>";
    foreach( $parms as $k => $v ) {
        if( substr($k,0,2) == "m_" ) {
            if( $bDebug ) echo "Replacing [".$ra[substr($k,2)]."] with [$v]<BR>";

            $ra[substr($k,2)] = $v;
        } else {
            if( $bDebug ) echo "Parm [$k] = [$v]<BR>";
        }
    }
    if( $bDebug && count($parms) )  echo "</TD></TR>";

    echo writePerson( (isset($parms['title']) ? $parms['title'] : "Director"),
                      $ra['firstname']." ".$ra['lastname'], $since,
                      $ra['address'], $ra['city']." ".$ra['province']." ".$ra['postcode'],
                      $ra['phone'],
                      @$parms['fax'],
                      $ra['email'] );
}


if( $bEveryone ) {
    echo "<H4>Directors</H4>";
}

echo "<TABLE cellpadding=10>";

// Jim
echo writePerson2( 1311, "1996", array("title"=>"President") );

// Garrett
echo writePerson2( 1077, "2004 & 1992-2000", array("title"=>"Vice President","m_email"=>"garrett@seeds.ca") );

// Helen
echo writePerson2( 5343, "2004", array("title"=>"Treasurer","m_email"=>"helen@seeds.ca") );

// Gwynne stays with her brother when in Toronto: 416-963-8023
echo writePerson2( 3988, "1998", array("title"=>"Secretary", "m_phone"=>"Phone & fax 514-272-5185",
                                       "m_email"=>"gwynne@seeds.ca") );
// Ghan
echo writePerson2( 3645, "2003", array("title"=>"Director of Fundraising","fax"=>"fax: 416-658-0810",
                                       "m_email"=>"ghan@seeds.ca") );

// JoAnne
echo writePerson2( 3770, "2006", array("m_email"=>"joanne@seeds.ca") );

// Jane
echo writePerson2( 4394, "2006", array("m_email"=>"jane@seeds.ca") );

// Anne
echo writePerson2( 10351, "2006", array("m_address"=>"79 Alameda Ave","m_city"=>"Toronto","m_postcode"=>"M6C 3W4") );

// Fr�d�ric
echo writePerson2( 6378, "2006", array("m_firstname"=>"Fr�d�ric","m_lastname"=>"Sauriol", "m_email"=>"frederic@semences.ca" ) );

// Fran�ois
echo writePerson2( 9280, "2007", array("m_email"=>"francois@semences.ca") );

// Vera
echo writePerson2( 10768, "2007", array("m_email"=>"vera@seeds.ca") );

echo "</TABLE>";


if( $bEveryone ) {
    echo "<H4>Staff and Contractors</H4>";

    echo "<TABLE cellpadding=10>";
    echo writePerson( "Executive Director", "Bob Wildfong", "", "68 Dunbar Rd South", "Waterloo ON N2L 2E3",
                      "Phone & fax: 519-886-7542", "", "bob@seeds.ca" );

    // Judy: 293 O'Connor Court, Cobourg ON K9A 5X5 / 905-377-9481
    echo writePerson( "Office Manager", "Judy Newman", "", "", "", "Use P.O. Box and 1-866 number", "", "judy@seeds.ca" );

    echo writePerson( "Communications Coordinator", "Val�rie Girard", "", "", "", "514-963-3730", "", "valerie@seeds.ca / valerie@semences.ca" );

    echo writePerson( "Magazine Editor", "Elaine Freedman", "", "670 Windermere Ave", "Toronto ON M6S 3M1", "416-762-8361", "", "elaine@seeds.ca / magazine@seeds.ca" );
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




