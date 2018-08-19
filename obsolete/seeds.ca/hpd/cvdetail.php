<?

/* Cultivar detail page.
 * This is the right frame of the CV frameset.
 *
 * $qtype: the left-hand frame is a query results list
 *
 * OR
 *
 * $species required
 * $cultivar: empty  = no cultivar selected, show species info;
 *            "NULL" = cultivar is unnamed, show details for cv ""
 *            other  = show details for cv
 */

include_once( "../site.php" );
include_once( SITEINC."sitedb.php" );
include_once( "_hpd.php" );
include_once( "_func.php" );

include_once( SEEDCOMMON."siteStart.php" );

list($kfdb) = SiteStart();

$qtype   = @$_REQUEST['qtype'];
$gpc_species  = BXStd_SafeGPCGetStr( 'species' );
$gpc_cultivar = BXStd_SafeGPCGetStr( 'cultivar' );

if( empty($qtype) && empty($gpc_species['plain']) )  BXStd_HTTPRedirect( HPD_PAGE_START );   // BAD: this loads the start page into the frame

?>
<HTML>
<HEAD></HEAD>
<BODY bgcolor="#ffffff">
<?
// if the left-hand frame is the initial presentation of a results list, just wait for the user to choose a result
if( empty($gpc_species['plain']) ) { echo "<P>Please select from the list on the left.</P>";  exit; }


echo "<H2><A HREF='${_SERVER['PHP_SELF']}?species=". urlencode($gpc_species['plain']) ."'>". $gpc_species['plain'] ."</A>";
if( empty( $gpc_cultivar['plain'] ) ) {
    echo "</H2>";

    /* Look for a species-summary file
     */
    $filename = "species/{$gpc_species['plain']}.php";
    if( file_exists( $filename ) ) {
        include( $filename );
    } else {
        $filename = "species/{$gpc_species['plain']}.html";
        if( file_exists( $filename ) ) {
            include( $filename );
        } else {
            echo "<P>Please select from the list on the left.</P>";
        }
    }
} else {
    if( $gpc_cultivar['plain'] == "NULL" ) {
        echo " : (Unnamed)</H2>";
        BXStd_SafeGPCSetStr( $gpc_cultivar, "" );
    } else {
        echo " : ". $gpc_cultivar['plain'] ."</H2>";
    }

    $ra_dbparms['species']  = $gpc_species['db'];
    $ra_dbparms['cultivar'] = $gpc_cultivar['db'];

    $desc = query( "cultivar", $ra_dbparms );
    if( !empty( $desc[0] ) ) {
        echo "<P>" . $desc[0]['description'] . "</P><HR>";
    }

    $catrefs = query( "catalogrefs", $ra_dbparms );
    if( count( $catrefs ) > 0 ) {
        echo "<h3>Historic Seed Catalogue References:</h3><table>\n";

        for( $i = 0; !empty( $catrefs[$i] ); $i++ ) {
            echo '<TR><TD valign="top"><A HREF="catalog.php?catalog=';
            echo $catrefs[$i]['refcode'] . '" target="_top">' . $catrefs[$i]['refdate'] . "</A></TD>\n";
            echo '<TD valign="top"><A HREF="catalog.php?catalog=';
            echo $catrefs[$i]['refcode'] . '" target="_top">';
            echo $catrefs[$i]['shortname'] . ", " . $catrefs[$i]['place'] . "</A></TD></TR>\n";

            echo "<TR><TD>&nbsp;</TD><TD valign='top'>";
            if( !empty( $catrefs[$i]['oname'] ) ) {
                echo "<B>" . $catrefs[$i]['oname'] . "</B>: ";
            }
            echo $catrefs[$i]['description'] . "</TD></TR>\n";
        }
        echo "</table><hr>";
    }

    $miscrefs = query( "miscrefs", $ra_dbparms );
    if( count( $miscrefs ) > 0 ) {
        echo "<h3>Miscellaneous References:</h3><table cellpadding=10>\n";
        for( $i = 0; !empty( $miscrefs[$i] ); $i++ ) {
            echo "<TR><TD valign='top'>" . BXStd_StrNoBreak($miscrefs[$i]['refdate']) . "</TD>\n";
            echo "<TD valign='top'>". $miscrefs[$i]['name'] ." ". $miscrefs[$i]['vol'] .", ". $miscrefs[$i]['place'] ."<BR>\n";
            echo "<B>". $miscrefs[$i]['oname'] ."</B>: ". $miscrefs[$i]['description'] ."</TD></TR>\n";
        }
        echo "</table><hr>";
    }

    $raMbrDesc = array();
    $mrefs = query( "memberrefs", $ra_dbparms );
    $i = 0;
    if( count( $mrefs ) > 0 ) {
        for( ; !empty( $mrefs[$i] ); $i++ ) {
            $raMbrDesc[$i]['year'] = $mrefs[$i]['year'];
            $raMbrDesc[$i]['cv'] = $mrefs[$i]['oname'];
            $raMbrDesc[$i]['sp_ex'] = $mrefs[$i]['species_ex'];
            $raMbrDesc[$i]['description'] = $mrefs[$i]['description'];
            $raMbrDesc[$i]['days'] = $mrefs[$i]['days_maturity'];
        }
    }

    if( ($dbc = $kfdb->KFDB_CursorOpen("SELECT * FROM sed_seeds WHERE _status=0 AND type like '".$gpc_species['db']."%' AND variety='".$gpc_cultivar['db']."'")) ) {
        while( $ra = $kfdb->KFDB_CursorFetch($dbc) ) {
            $raMbrDesc[$i]['year'] = $ra['year'];
            $raMbrDesc[$i]['cv'] = $ra['variety'];
            $raMbrDesc[$i]['sp_ex'] = ""; //$mrefs[$i]['species_ex'];
            $raMbrDesc[$i]['description'] = $ra['description'];
            $raMbrDesc[$i]['days'] = $ra['days_maturity'];
            $i++;
        }
    }

    if( count($raMbrDesc) ) {
        echo "<h3>What our members wrote:</h3><table cellpadding=10>\n";
        for( $i = 0; $i < count($raMbrDesc); ++$i ) {
            $bSame = false;
            echo "<TR>"
                ."<TD valign='top'>{$raMbrDesc[$i]['year']}</TD>"
                ."<TD valign='top'><B>{$raMbrDesc[$i]['cv']}</B>: ";
            if( !empty( $raMbrDesc[$i]['sp_ex'] ) ) {
                echo "[{$raMbrDesc[$i]['sp_ex']}] ";
            }
            if( $i > 0 && $raMbrDesc[$i]['description'] == $raMbrDesc[$i-1]['description'] ) {
                echo "<I>Same as above</I>";
                $bSame = true;
            } else {
                echo $raMbrDesc[$i]['description'];
            }
            if( !empty( $raMbrDesc[$i]['days'] ) ) {
                if( !$bSame || $raMbrDesc[$i]['days'] != $raMbrDesc[$i-1]['days'] ) {
                    echo "<BR>{$raMbrDesc[$i]['days']} days to maturity.";
                }
            }
            echo "</TD></TR>\n";
        }




        echo "<TR><TD colspan=2><FONT size=-1><I>If you are interested in receiving the next <A HREF='".SITEROOT."info/sod' target=_top>Seed Exchange Directory</A> and obtaining any of these varieties, please <A HREF='".SITEROOT."mbr/member.php' target='_top'>join Seeds of Diversity</A></I></FONT></TD></TR>";
        echo "</table><hr>";
    }

    $sources = query( "sourceinfo", $ra_dbparms );

//KLUGE
//$nCsci = db_query1( "SELECT count(*) from cat_item where pspecies='".$gpc_species['db']."' and pname='".$gpc_cultivar['db']."'" );
$nCsci = 0;  // read this out of the new csci, not the old one!

    if( $nCsci ) {
        echo "<h3>Canadian companies that sold ${gpc_cultivar['plain']} within the past three years:</h3><blockquote>";
        csci_drawCompanyList( $gpc_species['plain'], $gpc_cultivar['plain'], false );
        echo "</blockquote><HR>";
    }


    echo "<h3>Seed Availability:</h3><blockquote>";

    if( count($sources) == 0
//KLUGE
&& $nCsci == 0 ) {
        $sources = query( "sourcecount", $ra_dbparms );
        if( $sources[0][0] == 0 ) {
            echo "Seed availability statistics have not been collected for ". $gpc_species['plain'] .".<BR>";
        } else {
            echo "Seed availability statistics have not been collected for this cultivar.<BR>";
        }
    } else {
        $source = $sources[0];

        // Output the STATUS and DISTRIBUTION ICONS
        $status = 0;
        $distrib = 0;

//KLUGE
$status += $nCsci;
$distrib += $nCsci;
        if( $source['sodc'] > 0 ) {
            $status = $status + 2;
            $distrib = $distrib + 2;
        }
        if( $source['gsi'] > 0 ) {
            $status = $status + $source['gsi'];
            $distrib = $distrib + $source['gsi'];
        }
        if( $source['pgrc'] > 0 ) {
            $status = $status + 5;
            $distrib = $distrib + 1;
        }
        if( $source['npgs'] > 0 ) {
            $status = $status + 5;
            $distrib = $distrib + 1;
        }

        echo "<table cellpadding=12 border=1><tr><td align=center>STATUS</td>";
        if( $distrib > 0 ) {
            echo "<td align=center>DISTRIBUTION</td>";
        }
        echo "</tr><tr><td align=center>";

        if( $status == 0 ) {
            // echo "<img src='status_extinct.gif'>";
            echo "<font size=+2 color=brown>Apparently Extinct</font>";
        } else if( $status < 5 ) {
            // echo "<img src='status_endangered.gif'>";
            echo "<font size=+2 color=red>Endangered</font>";
        } else if( $status < 10 ) {
            // echo "<img src='status_rare.gif'>";
            echo "<font size=+2 color=orange>Rare</font>";
        } else {
            // echo "<img src='status_common.gif'>";
            echo "<font size=+2 color=green>Secure</font>";
        }
        echo "</td>";
        if( $distrib > 0 ) {
            echo "<td align=center>";
            if( $distrib < 5 ) {
                // echo "<img src='distrib_poor.gif'>";
                echo "<font size=+2 color=red>Poor</font>";
            } else if( $distrib < 10 ) {
                // echo "<img src='distrib_moderate.gif'>";
                echo "<font size=+2 color=orange>Moderate</font>";
            } else {
                // echo "<img src='distrib_good.gif'>";
                echo "<font size=+2 color=green>Good</font>";
            }
            echo "</td>";
        }
        echo "</tr></table><br>";


        // Output the checklist
        echo "<table><tr>";
        if( $source['sodc'] > 0 ) {
            // echo "<td><img src=green_check.gif></td>";
            echo "<td>Currently propagated by Seeds of Diversity's seed-savers.</td>";
        } else {
            // echo "<td><img src=red_X.gif></td>";
            echo "<td>Not currently propagated by Seeds of Diversity's seed-savers.</td>";
        }
        echo "</tr><tr>";

//KLUGE
$nCommerce = $source['gsi'] + $nCsci;

        if( $nCommerce > 20 ) {
            // echo "<td><img src=green_check.gif></td>";
            echo "<td>Commonly available from several mail-order seed companies.  Easy to find.</td>";
        } else if( $nCommerce > 10 ) {
            // echo "<td><img src=orange_check.gif></td>";
            echo "<td>Available from several mail-order seed companies.  Not difficult to find.</td>";
        } else if( $nCommerce > 5 ) {
            // echo "<td><img src=orange_check.gif></td>";
            echo "<td>Available from a small number of mail-order seed companies.  Difficult to find.</td>";
        } else if( $nCommerce > 0 ) {
            // echo "<td><img src=red_check.gif></td>";
            echo "<td>Available from only a few mail-order seed companies.  Very difficult to find.</td>";
        } else if( $source['gsi_dropped'] ) {
            // echo "<td><img src=red_X.gif></td>";
            echo "<td>Disappeared from commercial sale in North America during the past 20 years.  We believe that it is no longer available from mail-order seed companies.</td>";
        } else {
            // echo "<td><img src=red_X.gif></td>";
            echo "<td>Not known to have been sold commercially in North America for over 20 years.</td>";
        }
        echo "</tr><tr>";

        // echo "<td>" . ($source['pgrc'] ? "<img src=green_check.gif>" : "<img src=red_X.gif>") . "</td>";
        echo "<td>" . ($source['pgrc'] ? "M" : "Not m") . "aintained by the Canadian gene bank.</td>";
        echo "</tr><tr>";

        // echo "<td>" . ($source['pgrc'] ? "<img src=green_check.gif>" : "<img src=red_X.gif>") . "</td>";
        echo "<td>" . ($source['npgs'] ? "M" : "Not m") . "aintained by the U.S. gene bank.</td>";
        echo "</tr></table>";

        echo "</blockquote>";
    }
}

?>

<P>&nbsp;</P>
<P>&nbsp;</P>
<P><FONT size=-1>Our thanks to the<BR><A href='http://www.metcalffoundation.com' target='_top'>George Cedric Metcalf Foundation</A><BR>
for funding the construction of this database</P>

</BODY>
</HTML>
