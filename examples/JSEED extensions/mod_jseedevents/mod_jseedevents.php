<?php

defined( '_JEXEC' ) or die( 'Restricted access' );  // no direct access

define("SITEROOT", "../");   // because one of the searches is relative to the primary script at seeds.ca/Joomla/index.php
include_once( SITEROOT."site.php" );
include_once(SEEDCOMMON."siteStart.php");
include_once( STDINC."SEEDDate.php" );  // SEEDDateDB2Str
include_once( SEEDCOMMON."ev/_ev.php" );

$kfdb = SiteKFDB() or die( "Cannot connect to database" );

$lang = (strtolower(substr($_REQUEST['lang'],0,2))=='fr' ? "FR" : "EN");  // handles FR, fr, or fr-FR

// copied from en.php
class MyEV extends EV_Events
/* EV_Events fetches events and calls virtual method Write() for each.
 * This class writes the events to sOut
 */
{
    // public
    var $sOut = "";
    // private
    var $lang;
    var $prevDate = "";

    function MyEV( &$kfdb, $lang = "EN" ) { $this->EV_Events( $kfdb ); $this->lang = $lang; }

    function Write( $kfr ) {
        $alt = $kfr->value('date_alt'.($this->lang=='FR' ? '_fr' : ''));
        $d = !empty($alt) ? $alt : SEEDDateDB2Str( $kfr->value('date_start'), $this->lang );

        if( $d != $this->prevDate ) {
            $this->sOut .= "<BR/><A HREF='".EV_ROOT."events.php'>$d</A><BR/>";
            $this->prevDate = $d;
        }
        $this->sOut .= "<B>".$kfr->value('city').", ".$kfr->value('province')."</B><BR/>";
    }
}



//require_once( dirname(__FILE__).DS.'helper.php' );

//$hello = modHelloWorldHelper::getHello( $params );

$oEV = new MyEV( $kfdb, $lang );
$oEV->FetchFuture( 100 );
$events = "<H3>".JText::_($lang == "FR" ? "&Eacute;v&eacute;nements" : "Events")."</H3>"
          .$oEV->sOut
          ."<BR/><BR/><A href='".EV_ROOT."events.php'>Details and more events...</A>";


require( JModuleHelper::getLayoutPath( 'mod_jseedevents' ) );
?>
