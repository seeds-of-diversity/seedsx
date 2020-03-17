<?php
/**************************************************************
Seed Living Engine - Colin Mackenzie - Feb 1, 2010
***************************************************************/
//ini_set('display_errors', 1);
//error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
//error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
/* Dev Settting
//require_once("/var/www/vhosts/seedliving.ca/httpdocs/lib/lib_dev.php");
//define("ADMIN_EMAIL","colin_1269826654_biz@anlanda.com");
//define("SITEEMAIL","colin@anlanda.com");
define("PHPNAME","sl_dev.php");
define("SEONAME","sl_dev");
define("TMPLNAME","index.html");
define("DEV","1");*/


/* Live Settings */

//require_once("/var/www/vhosts/seedliving.ca/httpdocs/lib/lib.php");
define("SEEDLIVING_ROOT","../");
include_once( SEEDLIVING_ROOT."sl_defs.php" );


define("DEV","0");
define("FEATURE","0.15");

/* Intialize TOKEN TABLES */
tkntbl_init(array(&$tt, &$ctt, &$gtt, &$temptt, &$cfgtt, &$dtt, &$ott, &$tmpl, &$utt, &$ftt, &$sll, &$rtt, &$ltstt,&$restt,&$mll));
/* End Intialize TOKEN TABLES*/

/* Intialize MYSQL ACCESS SOCKETS */
mas_initlib(array(&$mas,&$mas2,&$mas3));
if(!mas_initsock($mas,MYSQL_SERVER,MYSQL_LOGIN,MYSQL_PASS,MYSQL_DB,0,0)) die("<br>could init MAS sockets<br>");
if(!mas_initsock($mas2,MYSQL_SERVER,MYSQL_LOGIN,MYSQL_PASS,MYSQL_DB,0,0)) die("<br>could init MAS sockets<br>");
if(!mas_initsock($mas3,MYSQL_SERVER,MYSQL_LOGIN,MYSQL_PASS,MYSQL_DB,0,0)) die("<br>could init MAS sockets<br>");
/* End Intialize MYSQL ACCESS SOCKETS */


load_env($tt,$ctt,$gtt);


tkntbl_add($gtt,"PHPNAME",PHPNAME,1);
tkntbl_add($tt,"PHPNAME",PHPNAME,1);
tkntbl_add($temptt,"PHPNAME",PHPNAME,1);

tkntbl_add($gtt,"SEONAME",SEONAME,1);
tkntbl_add($tt,"SEONAME",SEONAME,1);
tkntbl_add($temptt,"SEONAME",SEONAME,1);

tkntbl_add($gtt,"DEV",DEV,1);
tkntbl_add($tt,"DEV",DEV,1);
tkntbl_add($temptt,"DEV",DEV,1);

tkntbl_snprintf($cfgtt,"HTM_LOCATION",1,MAX_RESULTS,"%s%s",TEMPLROOT,TMPLNAME);
if (!tmplt_load($tmpl,ttn($cfgtt,"HTM_LOCATION"),"%%")) criterr("Unable to load Master Temaplate %s",ttn($cfgtt,"HTM_LOCATION"));


mas_qb($mas,"SELECT * FROM sales where sale_transid = '1000001'");
while(mas_qg($mas,$temptt)){
	/* send to seller */
	mas_q1($mas2,$dtt,"SELECT * FROM accounts WHERE account_id = '%s'",ttn($temptt,"sale_accountid"));
	mas_q1($mas2,$dtt,"SELECT * FROM users WHERE user_id = '%s'",ttn($dtt,"account_userid"));
	mas_q1($mas2,$dtt,"SELECT * FROM seeds WHERE seed_id = '%s'",ttn($temptt,"sale_seedid"));
	mas_q1($mas2,$dtt,"SELECT account_username as account_username_b,user_lname as user_lname_b,user_fname as user_fname_b,user_address as user_address_b,user_city as user_city_b,user_state as user_state_b,user_country as user_country_b,user_zip as user_postalcode_b FROM accounts,users WHERE account_id = '%s' and account_userid = user_id",ttn($temptt,"sale_buyerid"));
	tmplt_proc_ex_tt(TEMPLROOT,tkntbl_search($tmpl,"secureSellerSaleEmail"),OPENTAG,CLOSETAG,1,stdout,$tt,"message",array(&$tt,&$gtt,&$dtt,&$temptt));
	$headers  = "MIME-Version: 1.0\n";
	$headers .= "Content-type: text/html; charset=iso-8859-1\n";
	$headers .= "To: ".ttn($dtt,"account_email")."\n";
//	$headers .= "bcc: colin@anlanda.com\n";
//	$headers .= "bcc: sunshine@seedliving.ca\n";
	$headers .= "bcc: seedliving@seeds.ca\n";
	$headers .= "From: SeedLiving <".SITEEMAIL.">\n";
	$subject = "SeedLiving - Notification of sale for ".ttn($dtt,"seed_title");
	mail(NULL,$subject,ttn($tt,"message"),$headers);

	tmplt_proc_ex_tt(TEMPLROOT,tkntbl_search($tmpl,"secureBuyerSaleEmail"),OPENTAG,CLOSETAG,1,stdout,$tt,"message",array(&$tt,&$gtt,&$dtt,&$temptt));
	$headers  = "MIME-Version: 1.0\n";
	$headers .= "Content-type: text/html; charset=iso-8859-1\n";
	$headers .= "To: ".ttn($dtt,"account_email_b")."\n";
//	$headers .= "bcc: colin@anlanda.com\n";
//	$headers .= "bcc: sunshine@seedliving.ca\n";
	$headers .= "bcc: seedliving@seeds.ca\n";
	$headers .= "From: SeedLiving <".SITEEMAIL.">\n";
	$subject = "SeedLiving - Notification of purchase for ".ttn($dtt,"seed_title");
	mail(NULL,$subject,ttn($tt,"message"),$headers);
} mas_qe($mas);

?>