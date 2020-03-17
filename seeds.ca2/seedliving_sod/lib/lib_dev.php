<?php

require_once(SEEDLIVING_ROOT."lib/class/captcha.php");
require_once(SEEDLIVING_ROOT."lib/class/mas.php");
require_once(SEEDLIVING_ROOT."lib/class/tkntbl.php");
require_once(SEEDLIVING_ROOT."lib/tmplt.php");
require_once(SEEDLIVING_ROOT."lib/aslib.php");
require_once(SEEDLIVING_ROOT."lib/mas.php");
require_once(SEEDLIVING_ROOT."lib/tkntbl.php");
require_once(SEEDLIVING_ROOT."lib/paypalplatform_dev.php");

define("OPENTAG","[SL]");
define("CLOSETAG","[/SL]");

//define("MYSQL_SERVER","internal-db.s154977.gridserver.com");
//define("MYSQL_LOGIN","db154977_sldb");
//define("MYSQL_PASS","s@@dLiv1ngDB");
//define("MYSQL_DB","db154977_sldb");

define("SL_COOKIE","sllive");
define("SL_CART","slcart");
define("TEMPLROOT", SEEDLIVING_ROOT."templates/");    // "/var/www/vhosts/seedliving.ca/httpdocs/templates/");
define("IMAGEROOT", SEEDLIVING_ROOT."i/");            // "/var/www/vhosts/seedliving.ca/httpdocs/i/");
define("CAPTCHA", SEEDLIVING_ROOT."i/elephant.ttf");  // "/var/www/vhosts/seedliving.ca/httpdocs/i/elephant.ttf");
define("CAPTCHA_DIR", SEEDLIVING_ROOT."_captcha");    // "/var/www/vhosts/seedliving.ca/httpdocs/_captcha");
define("COOKIE_DOMAIN","seedliving.ca");
define("SEARCH_TRACKER","st");
?>