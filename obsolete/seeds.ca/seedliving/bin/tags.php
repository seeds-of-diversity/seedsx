<?
/**************************************************************
Seed Living Engine - Colin Mackenzie - Feb 1, 2010
***************************************************************/
error_reporting(1);
//ini_set('display_errors', 1);
//error_reporting(E_ALL ^ E_NOTICE);
/* Include Library */
//require_once("/var/www/vhosts/seedliving.ca/httpdocs/lib/lib.php");
define("SEEDLIVING_ROOT","../");
include_once( SEEDLIVING_ROOT."sl_defs.php" );
include_once( SEEDLIVING_ROOT."lib/lib.php" );

/* Intialize TOKEN TABLES */
tkntbl_init(array(&$tt, &$ctt, &$gtt, &$temptt, &$cfgtt, &$dtt, &$ott, &$tmpl, &$utt, &$ftt, &$sll, &$rtt, &$ltstt,&$restt,&$mll));
/* End Intialize TOKEN TABLES*/

/* Intialize MYSQL ACCESS SOCKETS */
mas_initlib(array(&$mas,&$mas2,&$mas3));
if(!mas_initsock($mas,MYSQL_SERVER,MYSQL_LOGIN,MYSQL_PASS,MYSQL_DB,0,0)) die("<br>could init MAS sockets<br>");
if(!mas_initsock($mas2,MYSQL_SERVER,MYSQL_LOGIN,MYSQL_PASS,MYSQL_DB,0,0)) die("<br>could init MAS sockets<br>");
if(!mas_initsock($mas3,MYSQL_SERVER,MYSQL_LOGIN,MYSQL_PASS,MYSQL_DB,0,0)) die("<br>could init MAS sockets<br>");
/* End Intialize MYSQL ACCESS SOCKETS */

$fp = fopen(SEEDLIVING_ROOT_DIR."includes/tags.html","w");

$content = "<h4 style=\"color:#b3b3b3;\">top ten tags</h4>";

mas_q1($mas,$temptt,"select count(*) as cnt from seeds where (seed_trade = 'S' or seed_trade = 'Y') and seed_quantity > 0 and seed_enabled = 'Y'");

$content .= "<a style=\"text-decoration:none;\" href=\"/sl/swaps/\"><span style=\"color:#3333FF;\">swaps</span></a>&nbsp;<span style=\"color:#B3B3B3;font-size:10px;\">".ttn($temptt,"cnt")." items with this tag</span><br />";

mas_qb($mas,"select tagrel_tagid, tag_name,tag_url,count(*) as total from tagrel,tags,seeds WHERE seed_enabled = 'Y' and seed_id = tagrel_seedid and seed_quantity > 0 and tag_id = tagrel_tagid group by tagrel_tagid order by total desc LIMIT 9");
//printf("%s",$mas->mas_querbuf);
while(mas_qg($mas,$temptt)){
	$content .= "<a style=\"text-decoration:none;\" href=\"/sl/".ttn($temptt,"tag_url")."\"><span style=\"color:#3333FF;\">".ttn($temptt,"tag_name")."</span></a>&nbsp;<span style=\"color:#B3B3B3;font-size:10px;\">".ttn($temptt,"total")." items with this tag</span><br />";
} mas_qe($mas);


fwrite($fp,$content);
fclose($fp);


criterr(NULL);

?>