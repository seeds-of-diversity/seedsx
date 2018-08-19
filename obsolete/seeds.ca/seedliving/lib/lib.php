<?
//require_once("/home/154977/domains/seedliving.ca/html/lib/class/captcha.php");
require_once(SEEDLIVING_ROOT."lib/class/captcha.php");
require_once(SEEDLIVING_ROOT."lib/class/mas.php");
require_once(SEEDLIVING_ROOT."lib/class/tkntbl.php");
require_once(SEEDLIVING_ROOT."lib/tmplt.php");
require_once(SEEDLIVING_ROOT."lib/aslib.php");
require_once(SEEDLIVING_ROOT."lib/mas.php");
require_once(SEEDLIVING_ROOT."lib/tkntbl.php");
require_once(SEEDLIVING_ROOT."lib/paypalplatform.php");

define("OPENTAG","[SL]");
define("CLOSETAG","[/SL]");


define("SL_COOKIE","sllive");
define("SL_CART","slcart");
define("TEMPLROOT", SEEDLIVING_ROOT."templates/" ); //"/home/154977/domains/seedliving.ca/html/templates/");
define("IMAGEROOT", SEEDLIVING_ROOT."i/" );  //"home/154977/domains/seedliving.ca/html/i/");
define("CAPTCHA", SEEDLIVING_ROOT."i/elephant.tff" );  //"/home/154977/domains/seedliving.ca/html/i/elephant.ttf");
define("CAPTCHA_DIR", SEEDLIVING_ROOT."_captcha" );  //"/home/154977/domains/seedliving.ca/html/_captcha");
define("COOKIE_DOMAIN", "" ); //"seedliving.ca");
define("SEARCH_TRACKER","st");
?>