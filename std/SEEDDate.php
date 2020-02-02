<?php

include_once( SEEDCORE."SEEDDate.php" );

function SEEDDateStr( $unixTime, $lang = "EN" )
/**********************************************
    Return a nice string format of the given timestamp, in the given language
 */
{
    if( $lang == "FR" ) {
// Locales are not set up on localhost or seeds.ca
//$l = setlocale( LC_ALL, "fr_CA" );
//echo "<P>".$l." : ".strftime ("%A %e %B %Y", mktime (0, 0, 0, 12, 22, 1978))."</P>";


        $raDayOfWeekFR[0] = "Dimanche";
        $raDayOfWeekFR[1] = "Lundi";
        $raDayOfWeekFR[2] = "Mardi";
        $raDayOfWeekFR[3] = "Mercredi";
        $raDayOfWeekFR[4] = "Jeudi";
        $raDayOfWeekFR[5] = "Vendredi";
        $raDayOfWeekFR[6] = "Samedi";

// use SEEDDate::raMonths
        $raMonthFR[1]  = "Janvier";
        $raMonthFR[2]  = "F&eacute;vrier";
        $raMonthFR[3]  = "Mars";
        $raMonthFR[4]  = "Avril";
        $raMonthFR[5]  = "Mai";
        $raMonthFR[6]  = "Juin";
        $raMonthFR[7]  = "Juillet";
        $raMonthFR[8]  = "A&ouml;ut";
        $raMonthFR[9]  = "Septembre";
        $raMonthFR[10] = "Octobre";
        $raMonthFR[11] = "Novembre";
        $raMonthFR[12] = "Decembre";

        $raGetDate = getdate( $unixTime );
        $date = $raDayOfWeekFR[$raGetDate["wday"]] ." ". $raGetDate["mday"] ." ". $raMonthFR[$raGetDate["mon"]] ." ". $raGetDate["year"];
//      $date = ($date_ex ? $date_ex : $raDayOfWeekFR[$raGetDate["wday"]] ." ". $ra["day"] ." ". $raMonthFR[$ra["month"]] ." ". $raPage["year"]);

//$date .= " | ". date("l F j, Y", $unixTime );


    } else {
        // English
        $date = date( "l F j, Y", $unixTime );
    }

    return( $date );
}

function SEEDDateDB2Str( $sDbDate, $lang = "EN" )
/************************************************
    Convert YYYY-MM-DD or YYYY/MM/DD to a nice string in the given language

 */
{
    return( SEEDDateStr( SEEDDateDB2Unixtime($sDbDate), $lang ) );
}

function SEEDDateDB2Unixtime( $sDbDate )
/***************************************
    Convert YYYY-MM-DD or YYYY/MM/DD to unix time

 */
{
// pretty sure strtotime() does a good job of this

// Upgraded to handle YYYY-M-D since some calendar controls return non-zero-padded date numbers
//    $y = substr( $sDbDate, 0, 4 );
//    $m = substr( $sDbDate, 5, 2 );
//    $d = substr( $sDbDate, 8, 2 );

    if( !in_array( ($delim = substr( $sDbDate, 4, 1 )), array('-','/') ) )  return( 0 );

    $ra = explode( $delim, $sDbDate );
    $y = @$ra[0];
    $m = @$ra[1];
    $d = @$ra[2];

    return( mktime(0,0,0,$m,$d,$y) );
}

class SEEDDateCalendar
/*********************
 */
{
    function __construct() {}

    function Setup() {
        return( "<STYLE type='text/css' media='screen'>"
               ."@import url('".W_ROOT."std/js/datepicker.css');"
               ."</STYLE>"
               ."<SCRIPT language='javascript' type='text/javascript' src='".W_ROOT."std/js/datepicker.js'></SCRIPT>" );
    }

    function DrawCalendarControl( $name, $sDate, $size = 10, $raParms = array() )
    {
        $pAttrs = @$raParms['attrs'];
        return( "<input name='$name' id='$name' type='text' value='$sDate' size='$size' $pAttrs onfocus=\"dpCreateCal('$name', 'cal-$name', null);\" />"
               ."<DIV id='cal-$name' style='display: inline;z-index:1;position:relative'></DIV>" );
    }
}

?>
