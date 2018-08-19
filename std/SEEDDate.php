<?php

class SEEDDate
{
    static public $raMonths = array(                                // wanted to put number of days here but leap years make that hard
         1 => array( 'en' => "January",   'fr' => "Janvier" ),
         2 => array( 'en' => "February",  'fr' => "F&eacute;vrier" ),
         3 => array( 'en' => "March",     'fr' => "Mars" ),
         4 => array( 'en' => "April",     'fr' => "Avril" ),
         5 => array( 'en' => "May",       'fr' => "Mai" ),
         6 => array( 'en' => "June",      'fr' => "Juin" ),
         7 => array( 'en' => "July",      'fr' => "Juillet" ),
         8 => array( 'en' => "August",    'fr' => "A&ouml;ut" ),
         9 => array( 'en' => "September", 'fr' => "Septembre" ),
        10 => array( 'en' => "October",   'fr' => "Octobre" ),
        11 => array( 'en' => "November",  'fr' => "Novembre" ),
        12 => array( 'en' => "December",  'fr' => "Decembre" ),
    );

    static public $raDaysOfWeek = array(
        0 => array( 'en' => 'Sunday',    'en3' => "Sun", 'fr' => "Dimanche", 'fr3' => 'Dim' ),
        1 => array( 'en' => 'Monday',    'en3' => "Mon", 'fr' => "Lundi",    'fr3' => 'Lun' ),
        2 => array( 'en' => 'Tuesday',   'en3' => "Tue", 'fr' => "Mardi",    'fr3' => 'Mar' ),
        3 => array( 'en' => 'Wednesday', 'en3' => "Wed", 'fr' => "Mercredi", 'fr3' => 'Mer' ),
        4 => array( 'en' => 'Thursday',  'en3' => "Thu", 'fr' => "Jeudi",    'fr3' => 'Jeu' ),
        5 => array( 'en' => 'Friday',    'en3' => "Fri", 'fr' => "Vendredi", 'fr3' => 'Ven' ),
        6 => array( 'en' => 'Saturday',  'en3' => "Sat", 'fr' => "Samedi",   'fr3' => 'Sam' ),
    );

    static function IsLeapYear( $y )
    {
        return( intval($y % 4)==0 && (intval($y % 100)!=0 || intval($y % 400)==0) );
    }

    static function DaysInMonth( $month, $year )    // year is required because of leap years
    {
        $days = 0;

        if( $month < 1 || $month > 12 )  return( 0 );

        if( in_array( $month, array(4,6,9,11) ) ) {
            $days = 30;
        } else if( $month == 2 ) {
            $days = self::IsLeapyear($year) ? 29 : 28;
        } else {
            $days = 31;
        }

        return( $days );
    }

    static function DayOfWeek( $year, $month, $day )
    /***********************************************
        Return a number between 0..6 representing Sun..Sat
     */
    {
        $leap_years = intval( ($month < 3 ? ($year-1) : $year) / 4 );

        switch( $month ) {
            default:
            case 1:    $month_year_day=0;    break;
            case 2:    $month_year_day=31;   break;
            case 3:    $month_year_day=59;   break;
            case 4:    $month_year_day=90;   break;
            case 5:    $month_year_day=120;  break;
            case 6:    $month_year_day=151;  break;
            case 7:    $month_year_day=181;  break;
            case 8:    $month_year_day=212;  break;
            case 9:    $month_year_day=243;  break;
            case 10:   $month_year_day=273;  break;
            case 11:   $month_year_day=304;  break;
            case 12:   $month_year_day=334;  break;
        }
        $w = (-473
              +365*($year-1970)
              +$leap_years
              -intval($leap_years/25)
              +((intval($leap_years % 25)<0) ? 1 : 0)
              +intval((intval($leap_years/25))/4)
              +$month_year_day
              +$day
              -1);
        return( intval( (intval($w %7) +7) %7) );    // don't know why
    }

}


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
