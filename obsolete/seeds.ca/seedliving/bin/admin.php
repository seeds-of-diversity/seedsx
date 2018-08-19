<?
/**************************************************************
Seed Living Engine - Colin Mackenzie - Feb 1, 2010
***************************************************************/
//ini_set('display_errors', 1);
//error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
//error_reporting(E_ALL);
/* Include Library */
//require_once("/var/www/vhosts/seedliving.ca/httpdocs/lib/lib.php");
//require_once("/home/154977/domains/seedliving.ca/html/lib/lib.php");
define("FEATURE","0.15");

define("SEEDLIVING_ROOT","../");
include_once( SEEDLIVING_ROOT."sl_defs.php" );

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

tkntbl_snprintf($cfgtt,"HTM_LOCATION",1,MAX_RESULTS,"%sindex.html",TEMPLROOT);



if (!tmplt_load($tmpl,ttn($cfgtt,"HTM_LOCATION"),"%%")) criterr("Unable to load Master Temaplate %s",ttn($cfgtt,"HTM_LOCATION"));

if(!$argv[1]){
	tkntbl_add($tt,"numofdays",1,1);
	tkntbl_snprintf($tt,"numofsec",1,MAX_RESULTS,"%s",(ttn($tt,"numofdays")*86400));
} else {
	tkntbl_add($tt,"numofdays","",1);
	tkntbl_add($tt,"numofsec","",1);
}



/* New Users */
if(!ttn($tt,"numofsec")) mas_qb($mas,"select * from accounts,users where account_userid = user_id");
else mas_qb($mas,"select * from accounts,users where account_userid = user_id AND (account_tsadd > (unix_timestamp() - %s))",ttn($tt,"numofsec"));

//printf("select * from accounts,users where account_userid = user_id AND (user_tsadd > (unix_timestamp() - %s))",ttn($tt,"numofsec"));
//die();

if(!$mas->mas_row_cnt){
	tkntbl_add($temptt,"users","<tr><td>No new users",1);
} else {
	while(mas_qg($mas,$temptt)){
		tkntbl_snprintf($temptt,"users",2,MAX_RESULTS,"<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td><a href='http://www.seedliving.ca//sl/%s/' target='_new'>Profile</a></td><td></tr>"
		,ttn($temptt,"user_fname")
		,ttn($temptt,"user_lname")
		,ttn($temptt,"account_username")
		,ttn($temptt,"account_email")
		,ttn($temptt,"user_city")
		,ttn($temptt,"user_state")
		,(ttn($temptt,"account_accesslevel")=='B'?"Buyer":"Seller")
		,strtolower(ttn($temptt,"account_username"))
		);
	} mas_qe($mas);
}


/* New Seeds */
if(!ttn($tt,"numofsec")) mas_qb($mas,"select * from accounts,users,seeds where account_userid = user_id and seed_userid = user_id");
else mas_qb($mas,"select * from accounts,users,seeds where account_userid = user_id and seed_userid = user_id AND (seed_tsadd > (unix_timestamp() -  %s))",ttn($tt,"numofsec"));
if(!$mas->mas_row_cnt){
	tkntbl_add($temptt,"seeds","<tr><td>No new seeds",1);
} else {
	while(mas_qg($mas,$temptt)){
		mas_q1($mas2,$ftt,"SELECT * FROM cats where cat_id = '%s'",ttn($temptt,"seed_topcat"));
		tkntbl_snprintf($temptt,"seeds",2,MAX_RESULTS,"<tr><td>%s</td><td>%s</td><td>$ %s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td><a href='http://www.seedliving.ca/sl/productDetails-%s/' target='_new'>Profile</a></td><td></tr>"
		,ttn($temptt,"account_username")
		,ttn($temptt,"seed_title")
		,number_format(ttn($temptt,"seed_price"),2)
		,ttn($ftt,"cat_name")
		,ttn($temptt,"seed_tagdesc")
		,ttn($temptt,"seed_zone")
		,ttn($temptt,"seed_tradetable")
		,strtolower(ttn($temptt,"seed_id"))
		);
	} mas_qe($mas);
}

/* New Donations */
if(!ttn($tt,"numofsec")) mas_qb($mas,"select * from accounts,donation where account_id = d_accountid");
else mas_qb($mas,"select * from accounts,donation where account_id = d_accountid AND (d_tsadd > (unix_timestamp() -  %s))",ttn($tt,"numofsec"));
if(!$mas->mas_row_cnt){
	tkntbl_add($temptt,"donations","<tr><td>No new donations</td></tr>",1);
} else {
	while(mas_qg($mas,$temptt)){
		tkntbl_snprintf($temptt,"donations",2,MAX_RESULTS,"<tr><td>%s</td><td>%s</td><td>$%s</td><td>%s</td><td>%s</td><td><a href='http://www.seedliving.ca/sl/%s/' target='_new'>Profile</a></td><td></tr>"
		,ttn($temptt,"account_username")
		,ttn($temptt,"account_email")
		,number_format(ttn($temptt,"d_amount"),2)
		,ttn($temptt,"d_enabled")
		,date("F j, Y, g:i a",ttn($temptt,"d_tsadd"))
		,strtolower(ttn($temptt,"account_username"))
		);
	} mas_qe($mas);
}

/* New Events */
if(!ttn($tt,"numofsec")) mas_qb($mas,"select * from accounts,events where account_id = event_postedby");
else mas_qb($mas,"select * from accounts,events where account_id = event_postedby AND (event_tsadd > (unix_timestamp() -  %s))",ttn($tt,"numofsec"));
if(!$mas->mas_row_cnt){
	tkntbl_add($temptt,"events","<tr><td>No new events",1);
} else {
	while(mas_qg($mas,$temptt)){
		tkntbl_snprintf($temptt,"events",2,MAX_RESULTS,"<tr><td>%s</td><td>%s</td><td>%s</td><td><a href='http://www.seedliving.ca/sl/displayEvent-%s/' target='_new'>Event Profile</a></td><td></tr>"
		,ttn($temptt,"account_username")
		,ttn($temptt,"account_email")
		,ttn($temptt,"event_name")
		,strtolower(ttn($temptt,"event_id"))
		);
	} mas_qe($mas);
}

/* New News */
if(!ttn($tt,"numofsec")) mas_qb($mas,"select * from accounts,news where account_id = new_postedby");
else mas_qb($mas,"select * from accounts,news where account_id = new_postedby AND (new_tsadd > (unix_timestamp() -  %s))",ttn($tt,"numofsec"));
if(!$mas->mas_row_cnt){
	tkntbl_add($temptt,"news","<tr><td>No new news",1);
} else {
	while(mas_qg($mas,$temptt)){
		tkntbl_snprintf($temptt,"news",2,MAX_RESULTS,"<tr><td>%s</td><td>%s</td><td>%s</td><td><a href='http://www.seedliving.ca/sl/displayNews-%s/' target='_new'>Event Profile</a></td><td></tr>"
		,ttn($temptt,"account_username")
		,ttn($temptt,"account_email")
		,ttn($temptt,"new_name")
		,strtolower(ttn($temptt,"new_id"))
		);
	} mas_qe($mas);
}


/* New Sales */
if(!ttn($tt,"numofsec")) mas_qb($mas,"select * from sales");
else mas_qb($mas,"select * from sales where (sale_tsadd > (unix_timestamp() -  %s))",ttn($tt,"numofsec"));
if(!$mas->mas_row_cnt){
	tkntbl_add($temptt,"sales","<tr><td>No new sales",1);
} else {
	while(mas_qg($mas,$temptt)){
		mas_q1($mas2,$ftt,"SELECT account_username as buyer FROM accounts WHERE account_id = '%s'",ttn($temptt,"sale_buyerid"));
		mas_q1($mas2,$ftt,"SELECT account_username as seller FROM accounts WHERE account_id = '%s'",ttn($temptt,"sale_accountid"));
		mas_q1($mas2,$ftt,"SELECT seed_title as title FROM seeds WHERE seed_id = '%s'",ttn($temptt,"sale_seedid"));
		tkntbl_snprintf($temptt,"sales",2,MAX_RESULTS,"<tr><td>%s</td><td>%s</td><td>%s</td><td>$%s</td><td>$%s</td><td>$%s</td><td>%s</td></tr>"
		,ttn($ftt,"buyer")
		,ttn($ftt,"title")
		,ttn($temptt,"sale_numitems")
		,number_format(ttn($temptt,"sale_price"),2)
		,number_format(ttn($temptt,"sale_shipcost"),2)
		,number_format(ttn($temptt,"sale_total"),2)
		,ttn($ftt,"seller")
		);
	} mas_qe($mas);
}

/* New trades */
if(!ttn($tt,"numofsec")) mas_qb($mas,"select * from tradingtable");
else mas_qb($mas,"select * from tradingtable where (tt_tsadd > (unix_timestamp() -  %s))",ttn($tt,"numofsec"));
if(!$mas->mas_row_cnt){
	tkntbl_add($temptt,"trades","<tr><td>No new trades",1);
} else {
	while(mas_qg($mas,$temptt)){
		mas_q1($mas2,$ftt,"SELECT account_username as buyer FROM accounts WHERE account_id = '%s'",ttn($temptt,"tt_accountid"));
		mas_q1($mas2,$ftt,"SELECT account_username as seller FROM accounts WHERE account_userid = '%s'",ttn($temptt,"tt_seeduserid"));
		mas_q1($mas2,$ftt,"SELECT seed_title as title FROM seeds WHERE seed_id = '%s'",ttn($temptt,"tt_seedid"));
		tkntbl_snprintf($temptt,"trades",2,MAX_RESULTS,"<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>"
		,ttn($ftt,"buyer")
		,ttn($ftt,"title")
		,ttn($temptt,"tt_quantity")
		,ttn($temptt,"tt_completed")
		,ttn($ftt,"seller")
		);
	} mas_qe($mas);
}

/* New swaps */
if(!ttn($tt,"numofsec")) mas_qb($mas,"select * from swaprequests");
else mas_qb($mas,"select * from swaprequests where (sr_tsadd > (unix_timestamp() -  %s))",ttn($tt,"numofsec"));
if(!$mas->mas_row_cnt){
	tkntbl_add($temptt,"swaps","<tr><td>No new swaps",1);
} else {

	while(mas_qg($mas,$temptt)){
		mas_q1($mas2,$ftt,"SELECT account_username as user1 FROM accounts WHERE account_id = '%s'",ttn($temptt,"sr_user1id"));
		mas_q1($mas2,$ftt,"SELECT account_username as user2 FROM accounts WHERE account_userid = '%s'",ttn($temptt,"sr_user2id"));
		mas_q1($mas2,$ftt,"SELECT seed_title as title1 FROM seeds WHERE seed_id = '%s'",ttn($temptt,"sr_seed1id"));
		mas_q1($mas2,$ftt,"SELECT seed_title as title2 FROM seeds WHERE seed_id = '%s'",ttn($temptt,"sr_seed2id"));
		tkntbl_snprintf($temptt,"swaps",2,MAX_RESULTS,"<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>"
		,ttn($ftt,"user1")
		,ttn($ftt,"title1")
		,ttn($temptt,"sr_seed1q")
		,ttn($ftt,"user2")
		,ttn($ftt,"title2")
		,ttn($temptt,"sr_seed2q")
		,ttn($temptt,"sr_approved")
		);
	} mas_qe($mas);
}

/* User comments */
if(!ttn($tt,"numofsec")) mas_qb($mas,"select * from userComments");
else mas_qb($mas,"select * from userComments where (uc_tsadd > (unix_timestamp() -  %s))",ttn($tt,"numofsec"));
if(!$mas->mas_row_cnt){
	tkntbl_add($temptt,"ucomments","<tr><td>No new user comments",1);
} else {
	while(mas_qg($mas,$temptt)){
		mas_q1($mas2,$ftt,"SELECT account_username as user1 FROM accounts WHERE account_id = '%s'",ttn($temptt,"uc_accountid"));
	mas_q1($mas2,$ftt,"SELECT account_username as user2 FROM accounts WHERE account_userid = '%s'",ttn($temptt,"uc_accountidby"));
		tkntbl_snprintf($temptt,"ucomments",2,MAX_RESULTS,"<tr><td>%s</td><td>%s</td><td>%s</td></tr>"
		,ttn($ftt,"user1")
		,ttn($temptt,"uc_text")
		,ttn($ftt,"user2")
		);
	} mas_qe($mas);
}


/* Seed comments */
if(!ttn($tt,"numofsec")) mas_qb($mas,"select * from seedComments");
else mas_qb($mas,"select * from seedComments where (sc_tsadd > (unix_timestamp() -  %s))",ttn($tt,"numofsec"));
if(!$mas->mas_row_cnt){
	tkntbl_add($temptt,"scomments","<tr><td>No new seed comments",1);
} else {

	while(mas_qg($mas,$temptt)){
		mas_q1($mas2,$ftt,"SELECT account_username as user1 FROM accounts WHERE account_id = '%s'",ttn($temptt,"sc_accountid"));
	mas_q1($mas2,$ftt,"SELECT seed_title as title1 FROM seeds WHERE seed_id = '%s'",ttn($temptt,"sc_seedid"));

		tkntbl_snprintf($temptt,"scomments",2,MAX_RESULTS,"<tr><td>%s</td><td>%s</td><td>%s</td></tr>"
		,ttn($ftt,"title1")
		,ttn($temptt,"sc_text")
		,ttn($ftt,"user1")
		);
	} mas_qe($mas);
}

tmplt_proc_ex_tt(TEMPLROOT,tkntbl_search($tmpl,"slDailySummary"),OPENTAG,CLOSETAG,1,stdout,$tt,"message",array(&$tt,&$temptt));
$headers  = "MIME-Version: 1.0\n";
$headers .= "Content-type: text/html; charset=iso-8859-1\n";
$headers .= "To: seedliving@seeds.ca\n";
//$headers .= "To: sunshine@seedliving.ca\n";
////$headers .= "To: colin@anlanda.com\n";
//$headers .= "Bcc: colin@anlanda.com\n";
//$headers .= "From: SeedLiving <sunshine@seedliving.ca>\n";
$headers .= "From: SeedLiving <seedliving@seeds.ca>\n";
$subject = "SeedLiving - Daily Summary";
mail(NULL,$subject,ttn($tt,"message"),$headers);







?>