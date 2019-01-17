<?php

define("SITEROOT", "../../");
include_once( SITEROOT."site2.php" );
include_once( STDINC."SEEDLocal.php" );
include_once( SEEDCOMMON."mbr/mbrOrder.php" );
include_once( SEEDCOMMON."mbr/mbrCommon.php" );

list($kfdb, $sess) = SiteStartSessionAccount( array("R MBRORDER") );
$kfdb->SetDebug(1);

$oStats = new MbrOrderStats( $kfdb );


$raConsoleParms = array(
    'HEADER' => "Seeds of Diversity - Order Statistics",
    'CONSOLE_NAME' => "MbrOrderStats",
    'bBootstrap' => true,
);
$oC = new Console01( $kfdb, $sess, $raConsoleParms );

$s = "";



class MbrOrderStats
{
    public  $oMbr;
    private $kfdb;
    public  $yCurrent;

    function __construct( KeyFrameDB $kfdb )
    {
        $this->kfdb = $kfdb;
        $this->oMbr = new MbrOrder( $kfdb, 'EN' );
        $this->yCurrent = intval( date("Y") );
    }

    function getN( $cond, $year = 0 )
    /********************************
        Return the number of paid/filled orders that match the condition & year
     */
    {
        if( empty($cond) ) $cond = "1=1";

        $q = "SELECT count(*) from seeds.mbr_order_pending "
            ."WHERE ($cond) AND eStatus IN ('".MBRORDER_STATUS_PAID."','".MBRORDER_STATUS_FILLED."')"
            .($year ? " AND (_created >= '${year}-08-01' AND _created < '".($year + 1)."-08-01')" : "");

        return( $this->kfdb->Query1( $q ) );
    }

    function getSum( $field, $year = 0, $cond = "" )
    /***********************************************
        Return the sum of the given field for paid/filled orders in the given year
     */
    {
        if( empty($cond) ) $cond = "1=1";

        $q = "SELECT sum($field) from seeds.mbr_order_pending "
            ."WHERE ($cond) AND eStatus IN ('".MBRORDER_STATUS_PAID."','".MBRORDER_STATUS_FILLED."')"
            .($year ? " AND (_created >= '${year}-08-01' AND _created < '".($year + 1)."-08-01')" : "");

        return( $this->kfdb->Query1( $q ) );
    }


    function showMemberships( $cond, $nCost )
    /****************************************
     */
    {
        $s = "";

        for( $y = 2005; $y <= $this->yCurrent; ++$y ) {
            $n = $this->getN( $cond, $y );
            $s .= "<td>$n<br/>$".($n * $nCost)."</td>";
        }
        $n = $this->getN( $cond, 0 );   // Total
        $s .= "<td>$n<br/>$".($n * $nCost)."</td>";

        return( $s );
    }

    function showDonations()
    /***********************
     */
    {
        $s = "";

        for( $y = 2005; $y <= $this->yCurrent; ++$y ) {
            $n = $this->getSum( 'donation', $y );
            $s .= "<td>$".$n."</td>";
        }
        $n = $this->getSum( 'donation', 0 );
        $s .= "<td>$".$n."</td>";

        return( $s );
    }

    function showTotalRevenue( $cond )
    /*********************************
     */
    {
        $s = "";

        for( $y = 2005; $y <= $this->yCurrent; ++$y ) {
            $n = $this->getSum( 'pay_total', $y, $cond );
            $s .= "<td>$".$n."</td>";
        }
        $n = $this->getSum( 'pay_total', 0, $cond );
        $s .= "<td>$".$n."</td>";

        return( $s );
    }

    function drawSpecialMembers( $bPrivacy = true )
    {
        $s = "<h3>Auto-renewing, Complementary, Life Members</h3>"
            ."<table border='1' cellpadding='10'><tr><th>Auto-renew</th><th>Complementary</th><th>Lifetime</th></tr>";
        foreach( array('A','C','L') as $code ) {
            $s .= "<td valign='top'>";
            $raRows = $this->kfdb->QueryRowsRA( "SELECT * FROM mbr_contacts WHERE expires='".MbrExpiryCode2Date( $code )."'" );
            if( $bPrivacy ) {
                $s .= count($raRows);
            } else {
                foreach( $raRows as $ra ) {
                    $s .= SEEDStd_ArrayExpand( $ra, "[[firstname]] [[lastname]] [[company]] [[dept]]<br/>" );
                }
            }
            $s .= "</td>";
        }
        $s .="</tr></table>";

        return( $s );
    }

    function DrawRecentRow( $col1 = "&nbsp;", $col2 = "&nbsp;", $fld )
    {
        global $nWeeks, $raWeek;

        $sum = 0;

        $s = "<tr><th>$col1</th><th>$col2</th>";
        for( $w = 0; $w <= $nWeeks; ++$w ) {
            $sum += @$raWeek[$w][$fld];
            $s .= "<td>".(isset($raWeek[$w][$fld]) ? $raWeek[$w][$fld] : "")."</td>";
        }
        $s .= "<td>$sum</td></tr>";

        return( $s );
    }
}

function getStats2( $ps, $pt, $year = 0 )
/****************************************
 */
{
    global $kfdb;

    $q = "SELECT count(*),sum(pay_total) from seeds.mbr_order_pending where eStatus='$ps' and ePayType='$pt'";
    if( $year ) {
        $q .= " AND _created >= '${year}-08-01' AND _created < '".($year + 1)."-08-01'";
    }

    $ra = $kfdb->KFDB_QueryRA( $q );

    return( intval($ra[0])." : $".intval($ra[1]) );
}

function getStats( $ps, $pt )
/****************************
 */
{
    return( "<TD>".getStats2( $ps, $pt, 2005 )."</TD>".
            "<TD>".getStats2( $ps, $pt, 2006 )."</TD>".
            "<TD>".getStats2( $ps, $pt       )."</TD>" );
}




function getBreak2a( $field, $year = 0 )
/***************************************
 */
{
    global $kfdb;

    $q = "SELECT count(*),sum($field) from seeds.mbr_order_pending where (eStatus='".MBRORDER_STATUS_PAID."' OR eStatus='".MBRORDER_STATUS_FILLED."')";
    if( $year ) {
        $q .= " AND _created >= '${year}-08-01' AND _created < '".($year + 1)."-08-01'";
    }

    $ra = $kfdb->KFDB_QueryRA( $q );

    return( intval($ra[0])." : $".intval($ra[1]) );
}


function getBreak2( $field )
/***************************
 */
{
    return( "<TD>".getBreak2a( $field, 2005 )."</TD>".
            "<TD>".getBreak2a( $field, 2006 )."</TD>".
            "<TD>".getBreak2a( $field       )."</TD>" );
}

function getBreak3a( $field, $n, $year = 0 )
/*******************************************
 */
{
    global $kfdb;

    $q = "SELECT count(*),sum($field) from seeds.mbr_order_pending where (eStatus='".MBRORDER_STATUS_PAID."' OR eStatus='".MBRORDER_STATUS_FILLED."')";
    if( $year ) {
        $q .= " AND _created >= '${year}-08-01' AND _created < '".($year + 1)."-08-01'";
    }

    $ra = $kfdb->KFDB_QueryRA( $q );

    return( intval($ra[0])." : $".(intval($ra[0])*$n) );
}


function getBreak3( $field, $n )
/*******************************
 */
{
    return( "<TD>".getBreak3a( $field, $n, 2005 )."</TD>".
            "<TD>".getBreak3a( $field, $n, 2006 )."</TD>".
            "<TD>".getBreak3a( $field, $n       )."</TD>" );
}


/* Total Online Revenues
 */
$s .= "<H3>Total Online Revenues (by fiscal year)</H3>"
     ."<TABLE border=1 cellpadding=10><TR><TH>Category</TH>";
for( $y = 2005; $y <= $oStats->yCurrent; ++$y ) {
    $s .= "<th>$y-".sprintf("%02d",$y-2000+1)."</th>";
}
$s .= "<TH>Total</TH></TR>";

$s .= "<tr><td>Paid and Filled - Paypal</td>" .$oStats->showTotalRevenue( "ePayType='PayPal'" )."</tr>" //getStats( MBRORDER_STATUS_FILLED,    'PayPal' )."</TR>"
     ."<tr><td>Paid and Filled - Cheque</td>" .$oStats->showTotalRevenue( "ePayType='Cheque'" )."</tr>"
//     ."<TR><TD>Paid, not filled - Paypal</TD>".getStats( MBRORDER_STATUS_PAID,      'PayPal' )."</TR>"
//     ."<TR><TD>Waiting for cheque</TD>"       .getStats( MBRORDER_STATUS_NEW,       'Cheque' )."</TR>"
//     ."<TR><TD>Paypal failed</TD>"            .getStats( MBRORDER_STATUS_NEW,       'PayPal' )."</TR>"
//     ."<TR><TD>Cancelled - cheque</TD>"       .getStats( MBRORDER_STATUS_CANCELLED, 'Cheque' )."</TR>"
//     ."<TR><TD>Cancelled - Paypal</TD>"       .getStats( MBRORDER_STATUS_CANCELLED, 'PayPal' )."</TR>"
     ."</TABLE>";





/* Breakdown of Paid Online Orders
 *
 */
$s .= "<H3>Breakdown of Paid Online Orders (by fiscal year)</H3>"
     ."<TABLE border=1 cellpadding=10><TR><TH>Category</TH>";
for( $y = 2005; $y <= $oStats->yCurrent; ++$y ) {
    $s .= "<th>$y-".sprintf("%02d",$y-2000+1)."</th>";
}
$s .= "<TH>Total</TH></TR>";

// Memberships
foreach( $oStats->oMbr->raMbrTypes as $k => $raM ) {
    $s .= "<tr><td>".$raM['EN']."</td>"
         .$oStats->showMemberships( "mbr_type='$k'", $raM['n'] )
         ."</tr>";
}

// Donations
$s .= "<tr><td>Donations</td>".$oStats->showDonations()."</tr>"
     ."</table>";

// Publications
/*
    ."<TR><TD>Seed Saving - English</TD>"     .getBreak3( "pub_ssh_en", 12 )."</TR>"
    ."<TR><TD>Seed Saving - French</TD>"      .getBreak3( "pub_ssh_fr", 12 )."</TR>"
    ."<TR><TD>Niche Market Development</TD>"  .getBreak3( "pub_nmd", 6 )."</TR>"
    ."<TR><TD>Selling Heritage Crops</TD>"    .getBreak3( "pub_shc", 8 )."</TR>"
    ."<TR><TD>Resource List</TD>"             .getBreak3( "pub_rl", 2 )."</TR>"
    ."</TABLE>";
*/


/* Auto, Complementary, Life Members
 */
$bPrivacy = true;   // make this false to see their names
$s .= $oStats->drawSpecialMembers( $bPrivacy );


/* Current and Recently Renewed Memberships
 */
$yPrev = $oStats->yCurrent - 1;
$raWeek = array();
$dRecent = ($oStats->yCurrent-1)."-10-01";
$tRecent = strtotime($dRecent);
$nWeeks = intval( (time()-strtotime($dRecent)) / 3600 / 24 / 7 ) + 1;

$s .= "<h3>Current and Recently Renewed Memberships</h3>";

$nCurrent = $kfdb->Query1( "SELECT count(*) FROM mbr_contacts WHERE year(expires)>='".$oStats->yCurrent."' AND year(expires)<'2100'");
$nRecent = $kfdb->Query1( "SELECT count(*) FROM mbr_contacts WHERE year(expires)>='".$oStats->yCurrent."' AND year(expires)<'2100' "
                         ."AND lastrenew > '$dRecent'");

$s .= "<p>Regularly paid ".$oStats->yCurrent." members = $nCurrent</p>";
$s .= "<p>Recent (after $dRecent) = $nRecent</p>";

// Get recent new/renewed memberships from the member database
$raMbrRecent = $kfdb->QueryRowsRA( "SELECT * FROM seeds2.mbr_contacts "
                                  ."WHERE year(expires)>='".$oStats->yCurrent."' AND year(expires)<'2100' "
                                  ."AND lastrenew >= '$dRecent'" );
// Get recent new/renewed memberships from the online orders
$raOrdersRecent = $kfdb->QueryRowsRA( "SELECT * FROM seeds.mbr_order_pending "
                                     ."WHERE _created >= '$dRecent' AND mbr_type<>'' AND eStatus='Filled'" );



foreach( $raMbrRecent as $ra ) {
    if( ($t = strtotime($ra['lastrenew']) - $tRecent) > 0 ) {

        // this could return zero indicating a new membership
        $previousExpires = $kfdb->Query1( "SELECT year(expires) FROM seeds2.mbr_contacts_old WHERE _key='{$ra['_key']}'" );

        // was this an online order?
        $bOnline = false;
        foreach( $raOrdersRecent as $raO ) {
            if( $ra['email'] && $ra['email']==$raO['mail_email'] ) {
                $bOnline = true;
                // use the actual order time
                if( $raO['_created'] ) {
                    $t = strtotime($raO['_created']) - $tRecent;
                }
                break;
            }
        }
        if( !$bOnline ) {
            foreach( $raOrdersRecent as $raO ) {
                if( $ra['lastname'] && $ra['lastname']==$raO['mail_lastname'] ) {
                    $bOnline = true;
                    // use the actual order time
                    if( $raO['_created'] ) {
                        $t = strtotime($raO['_created']) - $tRecent;
                    }
                    break;
                }
            }
        }

        $w = intval( $t / (3600*24*7) );
        $raWeek[$w]['total'] = @$raWeek[$w]['total'] + 1;

        if( $bOnline ) {
            if( !$previousExpires ) {
                $raWeek[$w]['onlineNew'] = @$raWeek[$w]['onlineNew'] + 1;
            } else if( $previousExpires == $yPrev ) {
                $raWeek[$w]['onlineRenewed'] = @$raWeek[$w]['onlineRenewed'] + 1;
            } else if( $previousExpires && $previousExpires < $yPrev ) {
                $raWeek[$w]['onlineLapsed'] = @$raWeek[$w]['onlineLapsed'] + 1;
            }
        } else {
            if( !$previousExpires ) {
                $raWeek[$w]['mailNew'] = @$raWeek[$w]['mailNew'] + 1;
            } else if( $previousExpires == $yPrev ) {
                $raWeek[$w]['mailRenewed'] = @$raWeek[$w]['mailRenewed'] + 1;
            } else if( $previousExpires && $previousExpires < $yPrev ) {
                $raWeek[$w]['mailLapsed'] = @$raWeek[$w]['mailLapsed'] + 1;
            }
        }
    }
}

foreach( $raOrdersRecent as $ra ) {
    if( ($t = strtotime($ra['_created']) - $tRecent) > 0 ) {
        $w = intval( $t / (3600*24*7) );
        $raWeek[$w]['online'] = @$raWeek[$w]['online'] + 1;
    }
}

$s .= "<table border='1' cellpadding='10'><tr><th colspan='2'>&nbsp;</th>";
for( $w = 0; $w <= $nWeeks; ++$w ) {
    $s .= "<td>".date("Y-m-d", $w * 3600 * 24 * 7 + $tRecent)."</td>";
}
$s .= "<td>Total</td></tr>"
     ."<tr><th colspan='2'>Total Added to Database</th>";
$sum = 0;
for( $w = 0; $w <= $nWeeks; ++$w ) {
    $sum += @$raWeek[$w]['total'];
    $s .= "<td>".(isset($raWeek[$w]['total']) ? $raWeek[$w]['total'] : "")."</td>";
}
$s .= "<td>$sum</td></tr>";


$s .= "<tr><th colspan='2'>Online</th>";
$sum = 0;
for( $w = 0; $w <= $nWeeks; ++$w ) {
    $sum += @$raWeek[$w]['online'];
    $s .= "<td>".(isset($raWeek[$w]['online']) ? $raWeek[$w]['online'] : "")."</td>";
}
$s .= "<td>$sum</td></tr>";
$s .= "<tr><th colspan='2'>Mail</th>";
$sumTotal = 0;
$sumOnline = 0;
$sumMail = 0;
for( $w = 0; $w <= $nWeeks; ++$w ) {
    $sumTotal += @$raWeek[$w]['total'];
    $sumOnline += @$raWeek[$w]['online'];
    if( ($nMail = $sumTotal - $sumOnline - $sumMail) <= 0 )  $nMail = 0;
    $sumMail += $nMail;
    $s .= "<td>".($nMail ? $nMail : "")."</td>";
}
$s .= "<td>$sumMail</td></tr>";

$s .= "<tr><td colspan='".($nWeeks + 4)."'>&nbsp;</td></tr>";

$s .= $oStats->DrawRecentRow( 'Online', 'New',        'onlineNew' )
     .$oStats->DrawRecentRow( '',       $yPrev,       'onlineRenewed' )
     .$oStats->DrawRecentRow( '',       "&lt;$yPrev", 'onlineLapsed' )
     .$oStats->DrawRecentRow( 'Mail',   'New',        'mailNew' )
     .$oStats->DrawRecentRow( '',       $yPrev,       'mailRenewed' )
     .$oStats->DrawRecentRow( '',       "&lt;$yPrev", 'mailLapsed' )
     ."</table>";

echo $oC->DrawConsole( $s );



?>
