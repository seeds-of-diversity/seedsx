<?php

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
    global $mas, $oSLiv;

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

tkntbl_add($tt,"SL2WROOT",SL2WROOT,1);
tkntbl_add($tt,"W_ROOT",SL2WROOT,1);    // SEEDSession.html template uses W_ROOT, and SL2WROOT points there (different than W_ROOT because of the mod_rewrite)

tkntbl_add($gtt,"DEV",DEV,1);
tkntbl_add($tt,"DEV",DEV,1);
tkntbl_add($temptt,"DEV",DEV,1);

// Store the global parameters in SeedLivingParms
// these are not as useful as having the same values in an array, because the template can read them from an array
$oSLiv->oSLivParms->SEONAME = SEONAME;
$oSLiv->oSLivParms->SEONAME2 = SEONAME2;


tkntbl_snprintf($cfgtt,"HTM_LOCATION",1,MAX_RESULTS,"%s%s",TEMPLROOT,TMPLNAME);
$oSLiv->oTmpl->Load( TEMPLROOT.TMPLNAME, "%%", $tmpl );
//if (!tmplt_load($tmpl,TEMPLROOT.TMPLNAME,"%%")) criterr("Unable to load Master Template %s",ttn($cfgtt,"HTM_LOCATION"));


tkntbl_add( $gtt, "IsSeedliving", 1, 1 );


$ov = ttn($tt,"overlord");

if( $ov == "purple-502" ) header("Location: http://www.seedliving.ca");


if( strstr($ov,"-") ) {
    $temp = explode( "-", $ov );
    $ov = $temp[0];
    tkntbl_add($tt,"overlord",$temp[0],1);
    tkntbl_add($tt,"@id",$temp[1],1);
}

if( $ov == "swaps" ) {
    $ov = "swapSearch";
    tkntbl_add($tt,"overlord","swapSearch",1);
}

tkntbl_add( $gtt, "bc_overlord", $ov == "searchall" ? ttn($tt,"@search") : $ov, 1 );

if( $ov == "searchall" ) {
    // Match a user profile if the whole account_username is @search
    // This should be part of a global partial-name search that returns a grid of icons for seeds, users, categories, tags, etc that match like %@search%.
    if( ($accid = $oSLiv->kfdb->Query1( "SELECT account_id FROM accounts WHERE account_username='".addslashes(ttn($tt,"@search"))."'")) ) {
        // userProfile shows someone else's profile; accountProfile shows the current user
        $ov = "userProfile";
        tkntbl_add($tt,"overlord","userProfile",1);
        tkntbl_add($tt,"@id",$accid,1);
    }
}

$dbOv = addslashes($ov);
mas_q1($mas,$ftt,"SELECT * FROM cats WHERE LEFT(cat_url, length('$dbOv'))  = '$dbOv'");
if($mas->mas_row_cnt){
	mas_q1($mas,$ftt,"SELECT * FROM tags WHERE LEFT(tag_url, length('$dbOv'))  = '$dbOv'");
	if($mas->mas_row_cnt){
		tkntbl_add($tt,"tag_id",ttn($ftt,"tag_id"),1);
	}
	$ov = "categorySearch";
	tkntbl_add($tt,"overlord","categorySearch",1);
	tkntbl_add($tt,"page",ttn($tt,"@id"),1);
	tkntbl_rmv($tt,"@id");
	tkntbl_add($tt,"@id",ttn($ftt,"cat_id"),1);
	tkntbl_ftable($ftt);
} else {
	mas_q1($mas,$ftt,"SELECT * FROM tags WHERE SUBSTRING(tag_url FROM 1 FOR length('$dbOv')) = SUBSTRING('$dbOv' FROM 1 FOR length('$dbOv'))");

	//printf("SELECT * FROM tags WHERE SUBSTRING(tag_url FROM 1 FOR length('%s'))  = SUBSTRING('%s' FROM 1 FOR length('%s'))",ttn($tt,"overlord"),ttn($tt,"overlord"),ttn($tt,"overlord"));
	if($mas->mas_row_cnt){
		tkntbl_add($tt,"overlord2",$ov,1);
		$ov = "tagSearch";
		tkntbl_add($tt,"overlord","tagSearch",1);
		tkntbl_add($tt,"page",ttn($tt,"@id"),1);
		tkntbl_rmv($tt,"@id");
		tkntbl_add($tt,"@id",ttn($ftt,"tag_id"),1);
		tkntbl_ftable($ftt);
	} else {
	    $zoneId = str_replace( array("zone","Zone"), "", trim($ov) );
		mas_q1($mas,$ftt,"SELECT * FROM seeds WHERE  seed_enabled = 'Y' AND seed_zone = '%s'", addslashes($zoneId));
		//printf("SELECT * FROM seeds WHERE seed_enabled = 'Y' AND seed_zone = '%s'",str_replace(array("zone","Zone"),"",trim(ttn($tt,"overlord"))));
		if($mas->mas_row_cnt){
			tkntbl_add($ftt,"@id",$zoneId,1);
			$ov = "zoneSearch";
			tkntbl_add($tt,"overlord","zoneSearch",1);
			tkntbl_add($tt,"page",ttn($tt,"@id"),1);
			tkntbl_rmv($tt,"@id");
			tkntbl_add($tt,"@id",ttn($ftt,"@id"),1);
			tkntbl_ftable($ftt);
		} else if( strstr($ov,"_Items") ) {
		    $username = addslashes(str_replace("_Items","",$ov));
		    if( $username && ($accid = $oSLiv->kfdb->Query1( "SELECT account_id FROM accounts WHERE account_username='$username'" )) ) {
	            $ov = "userSearch";
	            tkntbl_add($tt,"overlord","userSearch",1);
	            tkntbl_add($tt,"page",ttn($tt,"@id"),1);
	            tkntbl_add($tt,"@id",$accid,1);
	        }
	    } else if( strstr($ov,"_Swap") ) {
		    $username = addslashes(str_replace("_Swap","",$ov));
		    if( $username && ($accid = $oSLiv->kfdb->Query1( "SELECT account_id FROM accounts WHERE account_username='$username'" )) ) {
	            $ov = "userSearchSwap";
	            tkntbl_add($tt,"overlord","userSearchSwap",1);
	            tkntbl_add($tt,"page",ttn($tt,"@id"),1);
	            tkntbl_add($tt,"@id",$accid,1);
	        }
	    } else {
	        $dbOv = addslashes($ov);
	        if( ($accid = $oSLiv->kfdb->Query1( "SELECT account_id FROM accounts WHERE account_username='$dbOv'" )) ) {
	            // userProfile shows someone else's profile; accountProfile shows the current user
	            $ov = "userProfile";
	            tkntbl_add($tt,"overlord","userProfile",1);
	            tkntbl_add($tt,"@id",$accid,1);
	        }
	    }
	}
}

if( $oSLiv->oUser->kSLivUserid ) {
    // $gtt stores these values for use everywhere
    // Preferring encapsulatable access via SEEDSessionAuthDB:GetUserMetadata, once everything uses that we can remove this
    mas_q1( $mas, $gtt, "SELECT * FROM accounts,users WHERE user_id=account_userid AND user_id='{$oSLiv->oUser->kSLivUserid}'" );  //$sCond" );
}

    if( strpos( $ov, "secure" ) !== false ) {
        if( !$oSLiv->oUser->IsLogin() ) { // || !ttn($ctt,SL_COOKIE) ) {
            $oSLiv->GotoLoginPage();
        }
        //if( ttn($ctt,SL_COOKIE) == "1" ) {
        //    tkntbl_add($gtt,"access","admin",1);
        //    tkntbl_add($gtt,"user_username","Administrator",1);
        //}

        //$raC = explode(",",ttn($ctt,SL_COOKIE));
        //$sCond = "account_id='".addslashes($raC[0])."' AND account_hash='".addslashes($raC[1])."'";
        //if( !$oSLiv->kfdb->Query1( "SELECT account_id FROM accounts WHERE $sCond" ) ) {
        //    $oSLiv->GotoLoginPage();
        //}

        // obsolete step where old seedliving could have a user that wasn't yet validated
        if( !in_array( $ov, array("secureUser", "secureUserValidate") ) ) {
            if( ttn($gtt,"account_validation") || !ttn($gtt,"account_userid") ) {
//                $oSLiv->GotoPage( "secureUser" );
            }
        }
    }

    if( $oSLiv->oUser->IsLogin() ) { // && ttn($ctt,SL_COOKIE) ) {
        //$raC = explode(",",ttn($ctt,SL_COOKIE));
        //$sCond = "account_id='".addslashes($raC[0])."' AND account_hash='".addslashes($raC[1])."'";
        //mas_q1($mas,$gtt,"SELECT * FROM accounts WHERE $sCond" );
        if( false ) { //!$mas->mas_row_cnt) {
            die( "SEEDSession login but not SeedLiving login" );
        } else {
            $ov = ttn($tt,"overlord");
//            if( !in_array( $ov, array("secureUser", "secureUserValidate") ) ) {
//                if( ttn($gtt,"account_validation") || !ttn($gtt,"account_userid") ) {
//                    $oSLiv->GotoLoginPage( "secureUser" );
//                }
//            }

            // Preferring encapsulatable access via SEEDSessionAuthDB:GetUserMetadata, once everything uses that we can remove this
            mas_q1( $mas, $gtt, "SELECT * FROM accounts,users WHERE user_id=account_userid AND user_id='{$oSLiv->oUser->kSLivUserid}'" );  //$sCond" );

if( !ttn($gtt,"account_id") ) {
    // You don't have an account_id. That means you're a new user and you need these default values.
    tkntbl_add($gtt,"account_id",$oSLiv->oUser->GetCurrUID(),1);
    tkntbl_add($gtt,"user_id",$oSLiv->oUser->GetCurrUID(),1);
    $raUD = $oSLiv->oUser->GetRACurrUserData();
    tkntbl_add($gtt,"account_username",@$raUD['user_firstname']." ".@$raUD['user_lastname'],1);

}


//            if( !in_array( $ov, array("secureUser", "secureUserValidate") ) ) {
//                if( ttn($gtt,"account_validation") || !ttn($gtt,"account_userid") ) {
//            //        $oSLiv->GotoPage( "secureUser" );
//                }
//            }


            switch( ($n = $oSLiv->oBasket->Count()) ) {
                case 0:  $sBasket = "Your basket is empty";                                           break;
                case 1:  $sBasket = "<a href='/".SEONAME."/mybasket/'>Your basket has 1 item</a>";    break;
                default: $sBasket = "<a href='/".SEONAME."/mybasket/'>Your basket has $n items</a>";  break;
            }
            // this has to go in gtt because breadcrumbs collects all tt in bc_raw, which goes in the breadcrumbs link - so don't have html in that!
            tkntbl_add($gtt,"cartTotal","<span id=\"cartTotal\">$sBasket</span>",1);
        }
    }

    // template uses this for logic
    $oSLiv->oSLivParms->bLoginSession = ($oSLiv->oUser->IsLogin() ? "Yes" : "No");
    tkntbl_add( $gtt, "SLivParm_bLoginSession", $oSLiv->oSLivParms->bLoginSession, 1 );


    if(!strcmp(ttn($tt,"overlord"),"secureSwapView")){
        $ov = "myseedsList";
        tkntbl_add($tt,"overlord",$ov,1);
        tkntbl_add($tt,"action","swaps",1);
    }

    mas_q1($mas,$gtt,"SELECT * FROM fees");

    $oSLiv->oSLivParms->bFeesEnabled = false;
    tkntbl_add($gtt,   "fee_enabled","N",1);
    tkntbl_add($tt,    "fee_enabled",ttn($gtt,"fee_enabled"),1);
    tkntbl_add($temptt,"fee_enabled",ttn($gtt,"fee_enabled"),1);

    tkntbl_add( $gtt, "fee_text", ($oSLiv->oSLivParms->bFeesEnabled ? "Turn fees off" : "Turn fees on"), 1 );

    tkntbl_add($gtt,"slAdmin",(false ? "1" : ""),1);


    /* Load tags */
    //$content = file_get_contents(SEEDLIVING_ROOT_DIR."includes/tags.html");
//Replace [SL]slTags[/SL] with [[slTags:]]
//Since the tags list doesn't change much, the text could be stored in a db.
//It used to be in a file, but it should have been out of the svn tree
    $content = $oSLiv->DrawTags();
    tkntbl_add($gtt,"slTags",$content,1);
}

?>