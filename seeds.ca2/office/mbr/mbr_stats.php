<?php
/* Show statistics about members
 */
define( "SITEROOT", "../../" );
include_once( SITEROOT."site2.php" );
//include_once( STDINC."KeyFrame/KFUIAppSimple.php" );
include_once( "_mbr.php" );

list($kfdb, $sess) = SiteStartSessionAccount( array("R MBR") );
//$bReadonly = !($sess->CanWrite( "MBR" ));
//$kfdb->KFDB_SetDebug(2);


$year = SEEDSafeGPC_GetInt("year");
if( !$year )  $year = date( "Y" );


//SiteApp_KFUIAppHeader( "Seeds of Diversity Membership Statistics for $year" );
echo "<h3>Seeds of Diversity Membership Statistics for $year</h3>";

echo "<STYLE>"
    ."table#stat { margin-left:3em; }"
    ."td.stat_num { text-align:right; font-family:courier new; }"
    ."</STYLE>";

$nMbr = $kfdb->KFDB_Query1( "SELECT count(*) FROM mbr_contacts WHERE YEAR(expires)>='$year'" );
echo "<H4>Total current members:  $nMbr</H4>";


mbr_by_expiry();
mbr_by_province();

echo "<H2>Seed Directory Statistics</H2>";

sed_stats_basic();
sed_varieties_compare();


function mbr_by_expiry()
/**********************
 */
{
    global $kfdb, $sess, $year;

    $fee1 = 30;   // the one-year fee
    $fee3 = 85;   // the three-year fee

    echo "<H4>Membership by expiry</H4>";
echo "TODO: also list the complimentary and auto members";

    $ra = array();
    for( $i = 0; $i <=5; ++$i ) {
        $ra['n'][$i] = $kfdb->KFDB_Query1( "SELECT count(*) FROM mbr_contacts WHERE YEAR(expires)='".($year+$i)."'" );
    }

    $ra['defer'][0] = (5/12)*$fee1;
    $ra['defer'][1] = (5+12)/36*$fee3;
    $ra['defer'][2] = (5+12+12)/36*$fee3;
    $ra['defer'][3] = $fee3;
    $ra['defer'][4] = $fee3 + $fee1;
    $ra['defer'][5] = $fee3 + $fee1 + $fee1;

    $ra['label'][0] = "(5/12)*$fee1";
    $ra['label'][1] = "(5+12)/36*$fee3";
    $ra['label'][2] = "(5+12+12)/36*$fee3";
    $ra['label'][3] = "$fee3";
    $ra['label'][4] = "$fee3 + $fee1";
    $ra['label'][5] = "$fee3 + $fee1 + $fee1";

    echo "<TABLE id='stat' border='1'><TR>"
        ."<TR><TH>Year</TH><TH># members expiry</TH><TH>deferment per member</TH><TH>deferment</TH></TR>";

    for( $i = 0; $i <=5; ++$i ) {
        echo "<TR><TD>".($year+$i)."</TD><TD>".$ra['n'][$i]."</TD>"
	    ."<TD>".$ra['label'][$i]." = $".$ra['defer'][$i]."</TD><TD>$".($ra['n'][$i] * $ra['defer'][$i])."</TD></TR>";
    }
    echo "<TR><TD><B>TOTAL</B></TD><TD><B>";
    $t = 0;
    for( $i = 0; $i <=5; ++$i ) {
        $t += $ra['n'][$i];
    }
    echo $t."</TD><TD>&nbsp</TD><TD><B>";
    $t = 0;
    for( $i = 0; $i <=5; ++$i ) {
        $t += ($ra['n'][$i] * $ra['defer'][$i]);
    }
    echo $t."</B></TD></TR>"
        ."</TABLE>";
}


function mbr_by_province()
/*************************
 */
{
    global $kfdb, $sess, $year, $nMbr;

    $raOutUSA = array();
    $nTotal = 0;

    echo "<H4>Number and Percentage (of $nMbr) of Members by Province</H4>";

    echo "<TABLE id='stat' border='1'>"
        ."<TR><TH>Province</TH><TH colspan='2'>Members</TH></TR>";
    if( ($dbc = $kfdb->KFDB_CursorOpen( "SELECT count(*) as c,province,country FROM mbr_contacts WHERE YEAR(expires)>='$year' "
                                       ."GROUP BY province,country ORDER BY province" )) ) {
        while( $ra = $kfdb->KFDB_CursorFetch($dbc) ) {
            if( $ra['country'] == 'CANADA' ) {
                echo "<TR><TD><B>".$ra['province']."</B></TD>"
                    ."<TD class='stat_num'>".$ra['c']."</TD>"
                    ."<TD class='stat_num'>".sprintf("%0.1f",($ra['c']/$nMbr)*100)."%</TD></TR>";
                $nTotal += $ra['c'];
            } else {
	        $raOutUSA[] = $ra;
            }
        }
    }
    echo "<TR bgcolor='gray'><TD>&nbsp;</TD><TD class='stat_num'><B>$nTotal</B></TD>"
        ."<TD class='stat_num'><B>".sprintf("%0.1f",$nTotal/$nMbr*100)."%</B></TD></TR>"
        ."<TR><TD>&nbsp;</TD><TD>&nbsp;</TD><TD>&nbsp;</TD></TR>";
    foreach( $raOutUSA as $k => $v ) {
        echo "<TR><TD><B>".$v['province'].", ".$v['country']."</B></TD>"
	    ."<TD class='stat_num'>".$v['c']."</TD><TD class='stat_num'>".sprintf("%0.1f",($v['c']/$nMbr)*100)."%</TD></TR>";
        $nTotal += $v['c'];
    }
    echo "<TR bgcolor='gray'><TD>&nbsp;</TD><TD class='stat_num'><B>$nTotal</B></TD>"
        ."<TD class='stat_num'><B>".sprintf("%0.1f",$nTotal/$nMbr*100)."%</B></TD></TR>"
        ."</TABLE>";
}


function sed_stats_basic()
/*************************
 */
{
    global $kfdb, $sess, $year;

    echo "<H4>Annual counts</H4>";

    list($y_min, $y_max) = $kfdb->KFDB_QueryRA( "SELECT MIN(year),MAX(year) FROM seeds_1.sed_growers" );

    echo "<TABLE class='stat' border='1'><TR><TH>Year</TH><TH>Growers</TH><TH>Types</TH><TH>Varieties</TH><TH>Offers</TH></TR>";
    for( $y = $y_min; $y <= $y_max; ++$y ) {
        $g = $kfdb->KFDB_Query1( "SELECT count(*) FROM seeds_1.sed_growers WHERE year='$y'" );
        $ra = $kfdb->KFDB_QueryRA( "SELECT count(distinct type),count(distinct type,variety),count(*) FROM seeds_1.sed_seeds WHERE year='$y'" );
        echo "<TR><TD class='stat_num'>$y</TD>"
                ."<TD class='stat_num'>$g</TD>"
                ."<TD class='stat_num'>${ra[0]}</TD>"
                ."<TD class='stat_num'>${ra[1]}</TD>"
                ."<TD class='stat_num'>${ra[2]}</TD></TR>";
    }
    echo "</TABLE>";
}


function sed_varieties_compare()
/*******************************
 */
{
    global $kfdb, $sess, $year;

    list($y_min, $y_max) = $kfdb->KFDB_QueryRA( "SELECT MIN(year),MAX(year) FROM seeds_1.sed_growers" );


    // Can't do this with the database easily, because there are multiple records with the same types and varieties.
    // Need a distinct list for each year to avoid multiple results per tuple.

    $raDistinct = array();
    for( $y = $y_min; $y <= $y_max; ++$y ) {
        if( ($dbc = $kfdb->KFDB_CursorOpen("SELECT type,variety FROM seeds_1.sed_seeds WHERE year='$y' GROUP BY type,variety")) ) {
            while( $ra = $kfdb->KFDB_CursorFetch($dbc) ) {
                $raDistinct[$y][] = $ra['type'].":".$ra['variety'];
            }
            $dbc = NULL;
        }
    }

    echo "<H4>Number of Varieties present in row year, and also in column year</H4>";
    echo "<TABLE class='stat' border='1'><TR><TH>&nbsp</TH>";
    for( $y = $y_min; $y <= $y_max; ++$y ) {
        echo "<TH>$y</TH>";
    }
    echo "</TR>";
    for( $y = $y_min; $y <= $y_max; ++$y ) {
        echo "<TR><TD>$y</TD>";
        for( $yx = $y_min; $yx <= $y_max; ++$yx ) {
            if( $yx >= $y ) {
                $raX = array_intersect( $raDistinct[$y], $raDistinct[$yx] );
	        echo "<TD class='stat_num'>".count($raX)." (".sprintf("%0.1f",count($raX)*100.00/count($raDistinct[$y]))."%)</TD>";
            } else {
	        echo "<TD>&nbsp;</TD>";
            }
        }
	echo "</TR>";
    }
    echo "</TABLE>";

    echo "<H4>Number of Varieties present in row year, but dropped in column year</H4>";
    echo "<TABLE class='stat' border='1'><TR><TH>&nbsp</TH>";
    for( $y = $y_min; $y <= $y_max; ++$y ) {
        echo "<TH>$y</TH>";
    }
    echo "</TR>";
    for( $y = $y_min; $y <= $y_max; ++$y ) {
        echo "<TR><TD>$y</TD>";
        for( $yx = $y_min; $yx <= $y_max; ++$yx ) {
            if( $yx >= $y ) {
                $raX = array_diff( $raDistinct[$y], $raDistinct[$yx] );
	        echo "<TD class='stat_num'>".count($raX)." (".sprintf("%0.1f",count($raX)*100.00/count($raDistinct[$y]))."%)</TD>";
            } else {
	        echo "<TD>&nbsp;</TD>";
            }
        }
	echo "</TR>";
    }
    echo "</TABLE>";


    echo "<H4>Number of Varieties not present in row year, but added in column year</H4>";
    echo "<TABLE class='stat' border='1'><TR><TH>&nbsp</TH>";
    for( $y = $y_min; $y <= $y_max; ++$y ) {
        echo "<TH>$y</TH>";
    }
    echo "</TR>";
    for( $y = $y_min; $y <= $y_max; ++$y ) {
        echo "<TR><TD>$y</TD>";
        for( $yx = $y_min; $yx <= $y_max; ++$yx ) {
            if( $yx >= $y ) {
                $raX = array_diff( $raDistinct[$yx], $raDistinct[$y] );
	        echo "<TD class='stat_num'>".count($raX)." (".sprintf("%0.1f",count($raX)*100.00/count($raDistinct[$yx]))."%)</TD>";
            } else {
	        echo "<TD>&nbsp;</TD>";
            }
        }
	echo "</TR>";
    }
    echo "</TABLE>";
}


exit;

$mbr_cols = "num,code,firstname,lastname,company,department,address,city,province,country,postcode,phone,phone_ext,status,startdate,expires,bCurrent,renewed,language,mailing,referral,lastrenew,email";

echo "<H2>Membership Summary</H2>";
$ra = $kfdb->KFDB_QueryRA( "SELECT MIN(year) AS y1, MAX(year) AS y2 FROM mbr_members WHERE year <> -1" );
$y_min = $ra['y1'];
$y_max = $ra['y2'];
echo "<P>The following statistics are gathered from our master membership database, which currently ";
echo "contains membership records from <B>$y_min to $y_max</B>.  It should be kept up to date as frequently as possible.</P>";

echo "<H4>Number of Members per Year</H4>";
echo "<BLOCKQUOTE>";
for( $i = $y_max; $i >= $y_min; --$i ) {
    echo "<I>$i</I>: ".db_query1( "SELECT count(*) FROM mbr_members WHERE year=$i" ).($i==date("Y")?" (so far)" : "")."<BR>";
}
echo "</BLOCKQUOTE>";


echo "<H4>Number of Members Who Told Us Their Email Address</H4>";
echo "<BLOCKQUOTE>";
for( $i = $y_max; $i >= $y_min; --$i ) {
    echo "<I>$i</I>: ".db_query1( "SELECT count(*) FROM mbr_members WHERE year=$i AND email <> '' AND email IS NOT NULL" ).($i==date("Y")?" (so far)" : "")."<BR>";
}
echo "</BLOCKQUOTE>";



// number of members who renewed (and didn't) each year
// number of members who renewed after X years (these results should be stored historically for past years)
//  vertically: year  horizontally: was a member X years previously

?>
