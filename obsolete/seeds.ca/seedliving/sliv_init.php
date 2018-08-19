<?php


$oSLivParms = new SeedLivingParms;


// Old initialization code here, included by index.php and sl.php.
// The goal is to move all functionality to a single entry point at index.php using Redirect seedliving/blart -> seedliving/index.php?page=blart

/* Initialize MYSQL ACCESS SOCKETS */
mas_initlib(array(&$mas,&$mas2,&$mas3,&$mas4));
if(!mas_initsock($mas, MYSQL_SERVER,MYSQL_LOGIN,MYSQL_PASS,MYSQL_DB,0,0)) die("<br>could not init MAS sockets<br>");
if(!mas_initsock($mas2,MYSQL_SERVER,MYSQL_LOGIN,MYSQL_PASS,MYSQL_DB,0,0)) die("<br>could not init MAS sockets<br>");
if(!mas_initsock($mas3,MYSQL_SERVER,MYSQL_LOGIN,MYSQL_PASS,MYSQL_DB,0,0)) die("<br>could not init MAS sockets<br>");
if(!mas_initsock($mas4,MYSQL_SERVER,MYSQL_LOGIN,MYSQL_PASS,MYSQL_DB,0,0)) die("<br>could not init MAS sockets<br>");
/* End Intialize MYSQL ACCESS SOCKETS */


tkntbl_init(array(&$tt, &$ctt, &$gtt, &$temptt, &$cfgtt, &$dtt, &$ott, &$tmpl, &$utt, &$ftt, &$sll, &$rtt, &$ltstt,&$restt,&$mll));

initTkn();
function initTkn()
{
    global $tt, $ctt, $gtt, $temptt, $cfgtt, $tmpl, $ftt;
    global $mas, $oSLivParms;


load_env($tt,$ctt,$gtt);    // tt = REQUEST and FILES but not COOKIES, ctt = COOKIES, gtt = SERVER


tkntbl_add($gtt,"PHPNAME",PHPNAME,1);
tkntbl_add($tt,"PHPNAME",PHPNAME,1);
tkntbl_add($temptt,"PHPNAME",PHPNAME,1);

tkntbl_add($gtt,"SEONAME",SEONAME,1);
tkntbl_add($tt,"SEONAME",SEONAME,1);
tkntbl_add($temptt,"SEONAME",SEONAME,1);

tkntbl_add($tt,"SEONAME2",SEONAME2,1);

//tkntbl_add($gtt,"SL2URL",SL2URL,1);
tkntbl_add($tt,"SL2URL",SL2URL,1);
//tkntbl_add($temptt,"SL2URL",SL2URL,1);

tkntbl_add($gtt,"DEV",DEV,1);
tkntbl_add($tt,"DEV",DEV,1);
tkntbl_add($temptt,"DEV",DEV,1);

tkntbl_snprintf($cfgtt,"HTM_LOCATION",1,MAX_RESULTS,"%s%s",TEMPLROOT,TMPLNAME);
if (!tmplt_load($tmpl,ttn($cfgtt,"HTM_LOCATION"),"%%")) criterr("Unable to load Master Template %s",ttn($cfgtt,"HTM_LOCATION"));


if(!strcmp(ttn($tt,"overlord"),"purple-502")) header("Location: http://www.seedliving.ca");


if(strstr(ttn($tt,"overlord"),"-")){
	$temp = explode("-",ttn($tt,"overlord"));
	tkntbl_add($tt,"overlord",$temp[0],1);
	tkntbl_add($tt,"@id",$temp[1],1);
}

if(!strcmp(ttn($tt,"overlord"),"swaps")) tkntbl_add($tt,"overlord","swapSearch",1);

if(!strcmp(ttn($tt,"overlord"),"searchall")) tkntbl_add($gtt,"bc_overlord",ttn($tt,"@search"),1);
else tkntbl_add($gtt,"bc_overlord",ttn($tt,"overlord"),1);

if(!strcmp(ttn($tt,"overlord"),"searchall")){
    mas_q1($mas,$ftt,"SELECT * FROM accounts WHERE SUBSTRING(account_username FROM 1 FOR length('%s'))  = SUBSTRING('%s' FROM 1 FOR length('%s'))",ttn($tt,"@search"),ttn($tt,"@search"),ttn($tt,"@search"));
    if($mas->mas_row_cnt){
        tkntbl_add($tt,"overlord","userProfile",1);
        tkntbl_add($tt,"@id",ttn($ftt,"account_id"),1);
        tkntbl_ftable($ftt);
    }
}


$ov = ttn($tt,"overlord");
mas_q1($mas,$ftt,"SELECT * FROM cats WHERE LEFT(cat_url, length('$ov'))  = '$ov'");
if($mas->mas_row_cnt){
	mas_q1($mas,$ftt,"SELECT * FROM tags WHERE LEFT(tag_url, length('$ov'))  = '$ov'");
	if($mas->mas_row_cnt){
		tkntbl_add($tt,"tag_id",ttn($ftt,"tag_id"),1);
	}
	tkntbl_add($tt,"overlord","categorySearch",1);
	tkntbl_add($tt,"page",ttn($tt,"@id"),1);
	tkntbl_rmv($tt,"@id");
	tkntbl_add($tt,"@id",ttn($ftt,"cat_id"),1);
	tkntbl_ftable($ftt);
} else {
	mas_q1($mas,$ftt,"SELECT * FROM tags WHERE SUBSTRING(tag_url FROM 1 FOR length('%s'))  = SUBSTRING('%s' FROM 1 FOR length('%s'))",ttn($tt,"overlord"),ttn($tt,"overlord"),ttn($tt,"overlord"));
	//printf("SELECT * FROM tags WHERE SUBSTRING(tag_url FROM 1 FOR length('%s'))  = SUBSTRING('%s' FROM 1 FOR length('%s'))",ttn($tt,"overlord"),ttn($tt,"overlord"),ttn($tt,"overlord"));
	if($mas->mas_row_cnt){
		tkntbl_add($tt,"overlord2",ttn($tt,"overlord"),1);
		tkntbl_add($tt,"overlord","tagSearch",1);
		tkntbl_add($tt,"page",ttn($tt,"@id"),1);
		tkntbl_rmv($tt,"@id");
		tkntbl_add($tt,"@id",ttn($ftt,"tag_id"),1);
		tkntbl_ftable($ftt);
	} else {
		mas_q1($mas,$ftt,"SELECT * FROM seeds WHERE  seed_enabled = 'Y' AND seed_zone = '%s'",str_replace(array("zone","Zone"),"",trim(ttn($tt,"overlord"))));
		//printf("SELECT * FROM seeds WHERE seed_enabled = 'Y' AND seed_zone = '%s'",str_replace(array("zone","Zone"),"",trim(ttn($tt,"overlord"))));
		if($mas->mas_row_cnt){
			tkntbl_add($ftt,"@id",str_replace(array("zone","Zone"),"",trim(ttn($tt,"overlord"))),1);
			tkntbl_add($tt,"overlord","zoneSearch",1);
			tkntbl_add($tt,"page",ttn($tt,"@id"),1);
			tkntbl_rmv($tt,"@id");
			tkntbl_add($tt,"@id",ttn($ftt,"@id"),1);
			tkntbl_ftable($ftt);
		} else {

				if(strstr(ttn($tt,"overlord"),"_Items")){
					mas_q1($mas,$ftt,"SELECT * FROM accounts WHERE account_username = '%s'",str_replace("_Items","",ttn($tt,"overlord")));
					if($mas->mas_row_cnt){
						tkntbl_add($tt,"overlord","userSearch",1);
						tkntbl_add($tt,"page",ttn($tt,"@id"),1);
						tkntbl_rmv($tt,"@id");
						tkntbl_add($tt,"@id",ttn($ftt,"account_id"),1);
						tkntbl_ftable($ftt);
					}
				 } else if(strstr(ttn($tt,"overlord"),"_Swap")){

					mas_q1($mas,$ftt,"SELECT * FROM accounts WHERE account_username = '%s'",str_replace("_Swap","",ttn($tt,"overlord")));
					if($mas->mas_row_cnt){
						tkntbl_add($tt,"overlord","userSearchSwap",1);
						tkntbl_add($tt,"page",ttn($tt,"@id"),1);
						tkntbl_rmv($tt,"@id");
						tkntbl_add($tt,"@id",ttn($ftt,"account_id"),1);
						tkntbl_ftable($ftt);
					}
				} else {
					mas_q1($mas,$ftt,"SELECT * FROM accounts WHERE SUBSTRING(account_username FROM 1 FOR length('%s'))  = SUBSTRING('%s' FROM 1 FOR length('%s'))",ttn($tt,"overlord"),ttn($tt,"overlord"),ttn($tt,"overlord"));
					if($mas->mas_row_cnt){
						tkntbl_add($tt,"overlord","userProfile",1);
						tkntbl_add($tt,"@id",ttn($ftt,"account_id"),1);
						tkntbl_ftable($ftt);
					}
				}
	 	}
	}
}




if(strstr(ttn($tt,"overlord"),"secure")){
	if(!ttn($ctt,SL_COOKIE)) header("Location: /".SEONAME."/login/");
	else{
		if(!strcmp(ttn($ctt,SL_COOKIE),"1")) {
			tkntbl_add($gtt,"access","admin",1);
			tkntbl_add($gtt,"user_username","Administrator",1);
		}
		tkntbl_add($gtt,"authenticated","Yes",1);
	}
	$temp_a = explode(",",ttn($ctt,SL_COOKIE));
	mas_q1($mas,$gtt,"SELECT * FROM accounts WHERE account_id = '%s' AND account_hash = '%s'",$temp_a[0],$temp_a[1]);
	if(!$mas->mas_row_cnt) header("Location: /".SEONAME."/login/");
	else{
		mas_q1($mas,$gtt,"SELECT * FROM accounts,users WHERE account_id = '%s' AND user_id = account_userid AND account_hash = '%s'",$temp_a[0],$temp_a[1]);
		if(strcmp(ttn($tt,"overlord"),"secureUserValidate")){
			if(ttn($gtt,"account_validation") && strcmp(ttn($tt,"overlord"),"secureUser")){
				 header("Location: /".SEONAME."/secureUser/");
			} else {
				if(!ttn($gtt,"account_userid") && strcmp(ttn($tt,"overlord"),"secureUser")){
					header("Location: /".SEONAME."/secureUser/");
				}
			}
		}
		tkntbl_add($gtt,"authenticated","Yes",1);
	}

}

if(ttn($ctt,SL_COOKIE)){
	$temp_a = explode(",",ttn($ctt,SL_COOKIE));
	mas_q1($mas,$gtt,"SELECT * FROM accounts WHERE account_id = '%s' AND account_hash = '%s'",$temp_a[0],$temp_a[1]);
	if(!$mas->mas_row_cnt) tkntbl_add($gtt,"authenticated","No",1);
	else {
		if(strcmp(ttn($tt,"overlord"),"secureUserValidate")){
			if(ttn($gtt,"account_validation") && strcmp(ttn($tt,"overlord"),"secureUser")){
				 header("Location: /".SEONAME."/secureUser/");
			} else {
				if(!ttn($gtt,"account_userid") && strcmp(ttn($tt,"overlord"),"secureUser")){
					header("Location: /".SEONAME."/secureUser/");
				}
			}
		}
		mas_q1($mas,$tt,"SELECT count(*) cartTotal FROM carts WHERE cart_userid = '%s'",ttn($gtt,"account_id"));
		if(!ttn($tt,"cartTotal")) tkntbl_add($tt,"cartTotal","<span id=\"cartTotal\"><a href=\"#\">basket 0</a> items</span>",1);
		else tkntbl_add($tt,"cartTotal","<span id=\"cartTotal\"><a href=\"/".SEONAME."/mycart/\">basket ".ttn($tt,"cartTotal")."</a> items</span>",1);
		tkntbl_add($gtt,"authenticated","Yes",1);
	}
}


if(!strcmp(ttn($tt,"overlord"),"secureSwapView")){
	tkntbl_add($tt,"overlord","secureSeeds",1);
	tkntbl_add($tt,"action","swaps",1);
}

mas_q1($mas,$gtt,"SELECT * FROM fees");


// don't know where this originally came from (probaby apache SetEnv made it a _SERVER variable but that should have been in .htaccess)
tkntbl_add($gtt,"fee_enabled","N",1);
$oSLivParms->bFeesEnabled = false;

tkntbl_add($tt,"fee_enabled",ttn($gtt,"fee_enabled"),1);
tkntbl_add($temptt,"fee_enabled",ttn($gtt,"fee_enabled"),1);

if(!strcmp(ttn($gtt,"fee_enabled"),"Y")) tkntbl_add($gtt,"fee_text","Turn fees off",1);
else tkntbl_add($gtt,"fee_text","Turn fees on",1);



if(!strcmp(ttn($gtt,"account_id"),"225") || !strcmp(ttn($gtt,"account_id"),"200") || !strcmp(ttn($gtt,"account_id"),"201")){
//if(!strcmp(ttn($gtt,"account_id"),"1") || !strcmp(ttn($gtt,"account_id"),"6")){
	tkntbl_add($gtt,"slAdmin","1",1);
} else {
	tkntbl_add($gtt,"slAdmin","",1);
}

/* Load tags */
$content = file_get_contents(SEEDLIVING_ROOT_DIR."includes/tags.html");
tkntbl_add($gtt,"slTags",$content,1);
}
// END initTkn


class SeedLivingParms
{
    public $bFeesEnabled = false;

    function __construct() {}
}

?>