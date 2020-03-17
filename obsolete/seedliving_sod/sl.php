<?php

if( !file_exists( "go" ) )  exit;

//session_start();
//if( @$_SESSION['sliv_test'] != 1 ) exit;

/*
rename table request to sliv_requests;

*/


/**************************************************************
Seed Living Engine - Colin Mackenzie - Feb 1, 2010
                     Seeds of Diversity Canada 2013 - 2016
***************************************************************/

//echo "<PRE>";var_dump($_SERVER);echo "</PRE>";exit;

define("FEATURE","0.15");

include_once( "sliv_main.php" );


if( ttn($gtt,"REDIRECT_QUERY_STRING") ) {
    $oSLiv->kfdb->Execute( sprintf("INSERT INTO breadcrumbs VALUES('','%s','%s','%s','%s','%s','%s','%s','%s')",
            addslashes(ttn($gtt,"REDIRECT_URL")),
            addslashes(tkntbl_encstr($tt, NULL, "*", "*",NULL)),
            (strpos(ttn($tt,"overlord"),"searchall")===false ? "N" : "Y"),
            addslashes(ttn($gtt,"bc_overlord")),
            addslashes(ttn($tt,"page") ? (ttn($gtt,"bc_overlord")."-".ttn($tt,"page")) : ttn($gtt,"bc_overlord") ),
            addslashes(ttn($gtt,"account_id")),
            addslashes(ttn($gtt,"REMOTE_ADDR")),
            time() ) );
}


//if(!strcmp(ttn($gtt,"REMOTE_ADDR"),"68.144.69.157")) tkntbl_printf($tt);

$ov = ttn($tt,"overlord");

// Handle User module commands and output
list($bHandled,$sOut) = $oSLiv->oUser->Command( $ov );
if( $sOut )  echo $sOut;
if( $bHandled )  exit;

// Handle Seeds module commands and output
list($bHandled,$sOut) = $oSLiv->oSeeds->Command( $ov );
if( $sOut )  echo $sOut;
if( $bHandled )  exit;


switch( $ov ) {
    case "Disable":
        unlink( "go" );
        die( "Disabled" );

    case "LoadSED":
        LoadSED( $oSLiv );
        criterr(NULL);
        break;

    case "slDonateCheck":
		if(!strcmp(ttn($gtt,"fee_enabled"),"N")){
			tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"slDonateCheck"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		}
		criterr(NULL);
	break;
	case "secureFees":
		if(ttn($tt,"@id") && !strcmp(ttn($gtt,"slAdmin"),"1")){
			switch(ttn($tt,"@id")){
				case "Y":
					/* Turn off */
					mas_qnr($mas,"UPDATE fees SET fee_enabled = 'N'");
					mas_qnr($mas,"TRUNCATE accounts_copy");
					mas_qnr($mas,"INSERT INTO accounts_copy (SELECT * FROM accounts)");
					mas_qnr($mas,"UPDATE accounts SET account_accesslevel = 'S', account_unl2 = 'N', account_unl4 = 'Y', account_prepaid = 'N', account_preapproval = 'N',account_feestatus = 'N'");
					mas_qnr($mas,"TRUNCATE unlimited_copy");
					mas_qnr($mas,"INSERT INTO unlimited_copy (SELECT * FROM unlimited)");
					mas_qnr($mas,"TRUNCATE unlimited");
					mas_qb($mas,"SELECT * FROM accounts");
					while(mas_qg($mas,$temptt)){
						mas_qnr($mas2,"INSERT INTO unlimited VALUES('','%s','%s','40','Y','%s','')",ttn($temptt,"account_id"),(time()+10000000),time());
					} mas_qe($mas);

				break;

				case "N":
					/* Turn On */
					mas_qnr($mas,"UPDATE fees SET fee_enabled = 'Y'");
					mas_qb($mas,"SELECT * FROM accounts");
					while(mas_qg($mas,$temptt)){
						mas_q1($mas2,$ftt,"SELECT * FROM accounts_copy WHERE account_id = '%s'",ttn($temptt,"account_id"));
						if(!$mas2->mas_row_cnt){
							mas_qnr($mas2,"INSERT INTO accounts_copy (SELECT * FROM accounts WHERE acount_id = '%s')",ttn($temptt,"account_id"));
							mas_qnr($mas2,"UPDATE accounts_copy SET account_accesslevel = 'B',account_unl2 = 'N', account_unl4 = 'N', account_prepaid = 'N', account_preapproval = 'N' WHERE acount_id = '%s'",ttn($temptt,"account_id"));
						}
						mas_qnr($mas,"TRUNCATE accounts");
						mas_qnr($mas,"INSERT INTO accounts (SELECT * FROM accounts_copy)");
						mas_qnr($mas,"TRUNCATE unlimited");
						mas_qnr($mas,"INSERT INTO unlimited (SELECT * FROM unlimited_copy)");
					} mas_qe($mas);
				break;


			}
			$oSLiv->GotoPage( "userLogout" );
		}
		criterr(NULL);
	break;
	case "displayNews":
		if(ttn($tt,"@id")){
			tkntbl_add($ftt,"slTags",ttn($gtt,"slTags"),1);
			mas_q1($mas,$temptt,"SELECT * FROM news WHERE new_id = '%s'",ttn($tt,"@id"));
			mas_q1($mas2,$ftt,"SELECT * FROM accounts,users WHERE user_id = account_userid AND account_id= '%s'",ttn($temptt,"new_postedby"));
			tkntbl_add($temptt,"new_desc",stripslashes(ttn($temptt,"new_desc")),1);
			if(file_exists(IMAGEROOT."news/".ttn($tt,"@id")."_2.jpg")) tkntbl_add($tt,"slNewsI2","1",1);
			if(file_exists(IMAGEROOT."news/".ttn($tt,"@id")."_3.jpg")) tkntbl_add($tt,"slNewsI3","1",1);

			tkntbl_add($tt,"new_title",ttn($temptt,"new_name"),1);
			tkntbl_add($tt,"new_desc",substr(ttn($temptt,"new_desc"),0,200),1);

			tkntbl_add($tt,"new_desc2",strip_tags(substr(ttn($temptt,"new_desc"),0,200)),1);



			tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Top"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
			tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"displayNews"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$ftt,&$temptt));
			tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Bottom"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		}
		criterr(NULL);
	break;
	case "secureMassEmail":
		if(!strcmp(ttn($gtt,"slAdmin"),"1")){
			tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Top"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
			tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"secureMassEmail"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
			tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Bottom"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		} else slNoOperation($tmpl,$gtt);
	break;
	case "secureMassEmailSend":
		if(!strcmp(ttn($gtt,"slAdmin"),"1")){
			switch(ttn($tt,"email_options")){
				case "1":
					tkntbl_add($tt,"email_message_br",str_replace(array("\r","\n","\r\n"),"<br>",ttn($tt,"email_message")),1);
					tmplt_proc_ex_tt(TEMPLROOT,tkntbl_search($tmpl,"secureMassEmailSend1"),OPENTAG,CLOSETAG,1,stdout,$tt,"message",array(&$tt,&$gtt));
					$headers  = "MIME-Version: 1.0\n";
					$headers .= "Content-type: text/html; charset=iso-8859-1\n";
					$headers .= "To: ".SITEEMAIL."\n";
					//$headers .= "To: colin@anlanda.com\n";
					$headers .= "From: SeedLiving <".SITEEMAIL.">\n";
					$subject = ttn($tt,"email_subject");
					mail(NULL,$subject,ttn($tt,"message"),$headers);
					tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Top"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
					tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"secureMassEmailSend2"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
					tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Bottom"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
				break;

				default:
					mas_qb($mas,"SELECT * FROM accounts %s",(ttn($tt,"email_to")==1?"":sprintf("WHERE account_maillist = 'Y'")));
					while(mas_qg($mas,$temptt)){
						tkntbl_add($tt,"email_message_br",str_replace(array("\r","\n","\r\n"),"<br>",ttn($tt,"email_message")),1);
						tmplt_proc_ex_tt(TEMPLROOT,tkntbl_search($tmpl,"secureMassEmailSend1"),OPENTAG,CLOSETAG,1,stdout,$tt,"message",array(&$tt,&$temptt));
						$headers  = "MIME-Version: 1.0\n";
						$headers .= "Content-type: text/html; charset=iso-8859-1\n";
						$headers .= "To: ".ttn($temptt,"account_email")."\n";
						$headers .= "From: SeedLiving <".SITEEMAIL.">\n";
						$subject = ttn($tt,"email_subject");
						mail(NULL,$subject,ttn($tt,"message"),$headers);
					} mas_qe($mas);
					tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Top"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
					tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"secureMassEmailSend4"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
					tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Bottom"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
				break;
			}
		}
	break;
	case "slAccountChDisplay":
		if(ttn($tt,"@id")){
			switch(ttn($tt,"@id")){
				case "1":
					tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Top"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
					tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"slAccountChDisplay1"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
					tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Bottom"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
				break;

				case "2":
					tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Top"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
					tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"slAccountChDisplay2"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
					tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Bottom"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
				break;

				case "3":
					if(ttn($tt,"account_email")){
						mas_q1($mas,$temptt,"SELECT * FROM accounts WHERE account_email = '%s'",ttn($tt,"account_email"));
						if($mas->mas_row_cnt){
							if(ttn($temptt,"account_validation")){
									mas_q1($mas,$temptt,"SELECT * FROM users WHERE user_id = '%s'",ttn($temptt,"account_userid"));
									tkntbl_rmv($temptt,"account_password");
									tmplt_proc_ex_tt(TEMPLROOT,tkntbl_search($tmpl,"secureIntroEmail"),OPENTAG,CLOSETAG,1,stdout,$tt,"message",array(&$tt,&$temptt));
									$headers  = "MIME-Version: 1.0\n";
									$headers .= "Content-type: text/html; charset=iso-8859-1\n";
									//$headers .= "To: colin@anlanda.com\n";
									$headers .= "To: ".ttn($temptt,"account_email")."\n";
									$headers .= "From: SeedLiving <".SITEEMAIL.">\n";
									$subject = "Welcome to SeedLiving.";
									mail(NULL,$subject,ttn($tt,"message"),$headers);
									tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Top"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
									tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"slAccountChDisplay3"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
									tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Bottom"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
							} else {
								tkntbl_add($tt,"slErrorMsg","Your account is already validated. Please click sign in to login into your garden.",1);
								tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Top"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
								tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"slAccountChDisplay2"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
								tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Bottom"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
							}
						} else {
							tkntbl_add($tt,"slErrorMsg","Email address entered is not in our records.",1);
							tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Top"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
							tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"slAccountChDisplay2"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
							tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Bottom"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
						}
					}
				break;

				case "4":
					if(ttn($tt,"account_email") && ttn($tt,"account_username")){
						mas_q1($mas,$temptt,"SELECT * FROM accounts WHERE account_username = '%s' AND account_email = '%s'",ttn($tt,"account_username"),ttn($tt,"account_email"));
						if($mas->mas_row_cnt){
							tkntbl_add($gtt,"account_password",rand_string(7),1);
							tkntbl_add($tt,"account_password",md5(ttn($gtt,"account_password")),1);
							tkntbl_rmv($temptt,"account_password");
							mas_qnr($mas,"UPDATE accounts SET account_password = '%s' WHERE account_id = '%s'",ttn($tt,"account_password"),ttn($temptt,"account_id"));
							slUpdateRequest($mas4,"password","U",ttn($temptt,"account_id"));
							tmplt_proc_ex_tt(TEMPLROOT,tkntbl_search($tmpl,"slPasswordReset"),OPENTAG,CLOSETAG,1,stdout,$tt,"message",array(&$gtt,&$temptt));
							$headers  = "MIME-Version: 1.0\n";
							$headers .= "Content-type: text/html; charset=iso-8859-1\n";
							//$headers .= "To: colin@anlanda.com\n";
							$headers .= "To: ".ttn($temptt,"account_email")."\n";
							$headers .= "From: SeedLiving <".SITEEMAIL.">\n";
							$subject = "SeedLiving - account password change.";
							mail(NULL,$subject,ttn($tt,"message"),$headers);
							tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Top"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
							tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"slAccountChDisplay5"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$temptt,&$gtt));
							tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Bottom"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
						} else {
							tkntbl_add($tt,"slErrorMsg","We were unable to find your account. Please reenter your details and try again",1);
							tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Top"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
							tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"slAccountChDisplay1"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
							tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Bottom"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
						}
					} else slNoOperation($tmpl,$gtt);
				break;

				default:
					slNoOperation($tmpl,$gtt);
				break;

			}
		} else slNoOperation($tmpl,$gtt);
		criterr(NULL);
	break;
	case "slCheckCaptcha":
		$img = new captcha();
		$valid = $img->check(ttn($tt,"@id"));
		if(!$valid) criterr("0");
		else criterr("1");
	break;
	case "captcha":
		$img = new captcha();
		$img->show();
	break;
	case "secureUserPasswordSave":
		header("Cache-Control: no-cache");
		if(ttn($gtt,"account_id")){
			if(!strcmp(ttn($tt,"@id"),ttn($gtt,"account_id"))){
				mas_q1($mas,$temptt,"SELECT * FROM accounts WHERE account_id = '%s'",ttn($tt,"@id"));
				if($mas->mas_row_cnt){
					mas_qnr($mas,"UPDATE accounts SET account_password = '%s' WHERE account_id = '%s'",md5(ttn($tt,"account_password")),ttn($gtt,"account_id"));
					slUpdateRequest($mas4,"password","U",ttn($gtt,"account_id"));
					mas_qnr($mas,"DELETE FROM carts WHERE cart_userid = '%s'",ttn($gtt,"account_id"));
					mas_qnr($mas,"DELETE FROM breadcrumbs WHERE bc_accountid = '%s' OR bc_ip = '%s'",ttn($gtt,"account_id"),ttn($gtt,"account_ip"));
					setcookie(SL_COOKIE, ttn($ctt,SL_COOKIE), time() - 36000000,"/",COOKIE_DOMAIN);
					header("Location: /".SEONAME."/login-1/");
					criterr(NULL);
				}
			}
		}
		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Top"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"slOperationalError"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Bottom"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		criterr(NULL);
	break;
	case "secureUserAddEditPassword":
		if(ttn($gtt,"account_id")){
			if(!strcmp(ttn($tt,"@id"),ttn($gtt,"account_id"))){
				mas_q1($mas,$temptt,"SELECT * FROM accounts WHERE account_id = '%s'",ttn($tt,"@id"));
				if($mas->mas_row_cnt){
					tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Top"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
					tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"secureUserAddEditPassword"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt,&$temptt));
					tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Bottom"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
					criterr(NULL);
				}
			}
		}
		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Top"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"slOperationalError"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Bottom"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		criterr(NULL);
	break;
	case "slLoadBasket":
		if(ttn($ctt,SL_COOKIE)){
			 mas_q1($mas,$temptt,"SELECT count(*) cartTotal FROM carts WHERE cart_userid = '%s'",ttn($gtt,"account_id"));
			 if(!ttn($temptt,"cartTotal")) printf("<div id=\"slTopBasket\"><a href='#'>basket <span id=\"cartTotal\">0</span></a> items</div>");
			 else printf("<div id=\"slTopBasket\"><a href='/".SEONAME."/mycart/'>basket <span id=\"cartTotal\">%s</span></a> items</div>",ttn($temptt,"cartTotal"));
		} else printf("");
		criterr(NULL);
	break;
	case "slLoadUser":
		if(ttn($gtt,"account_id")){
			printf("<div id=\"slTopAccount\">Welcome %s</div>",ttn($gtt,"account_username"));
		} else {
			printf("");
		}
		criterr(NULL);
	break;
	case "slCheckLogin":
		if(ttn($ctt,SL_COOKIE)) printf("<li class=\"first\"><a href=\"/".SEONAME."/secureUser/\">my garden</a></li><li><a href=\"/".SEONAME."/userLogout/\">logout</a></li>");
		else printf("<li class=\"first\"><a href=\"/".SEONAME."/account/\">create my account</a></li><li><a href=\"/".SEONAME."/login/\">sign in</a></li>");
	break;
	case "slCheckUserName":
		if(ttn($tt,"@user")){
			mas_q1($mas,$temptt,"SELECT * FROM accounts WHERE account_username = '%s'",ttn($tt,"@user"));
			if($mas->mas_row_cnt){
				criterr("Username already exists. Please choose another.");
			}
		}
		criterr(NULL);
	break;
	case "slCheckUserEmail":
		if(ttn($tt,"@email")){
			mas_q1($mas,$temptt,"SELECT * FROM accounts WHERE account_email = '%s'",ttn($tt,"@email"));
			if($mas->mas_row_cnt){
				criterr("Email already exists. Please choose another.");
			}
		}
		criterr(NULL);
	break;
	case "seedCommentLoad":
		mas_qb($mas,"SELECT * FROM seedComments,accounts WHERE sc_seedid = '%s' AND sc_accountid = account_id order by sc_tsadd desc",ttn($tt,"@id"));
		while(mas_qg($mas,$temptt)){
			tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"seedCommentsSummary"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$temptt));
		} mas_qe($mas);

		criterr(NULL);
	break;
	case "userCommentLoad":
		mas_qb($mas,"SELECT * FROM userComments,accounts WHERE uc_accountid = '%s' AND uc_accountidby = account_id order by uc_tsadd desc",ttn($tt,"@id"));
		while(mas_qg($mas,$temptt)){
			tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"userCommentsSummary"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt,&$temptt));
		} mas_qe($mas);

		criterr(NULL);
	break;
	case "updateCart":
		if(!ttn($ctt,SL_CART)){
			if(ttn($gtt,"account_id")) {
				tkntbl_add($gtt,"id",ttn($gtt,"account_id"),1);
			} else {
				tkntbl_add($gtt,"id",rand(9000,1000000),1);
				setcookie(SL_CART, ttn($gtt,"id"), time() + 3600,"/",COOKIE_DOMAIN);
			}
		} else {
			tkntbl_add($gtt,"id",ttn($ctt,SL_CART),1);
		}
		mas_q1($mas,$temptt,"SELECT * FROM carts WHERE cart_seedid = '%s' AND cart_userid = '%s'",ttn($tt,"@id"),ttn($gtt,"account_id"));
		if(!$mas->mas_row_cnt){
			mas_q1($mas,$temptt,"SELECT seed_currency as cart_currency FROM carts,seeds WHERE cart_seedid = seed_id AND cart_userid = '%s'",ttn($gtt,"account_id"));
			mas_q1($mas,$temptt,"SELECT seed_currency  FROM seeds WHERE seed_id  = '%s'",ttn($tt,"@id"));
			if(ttn($temptt,"cart_currency")){
				if(strcmp(ttn($temptt,"cart_currency"),ttn($temptt,"seed_currency"))) criterr(0);
			}

			mas_qnr($mas,"INSERT INTO carts VALUES('','%s','%s','','%s','')",ttn($tt,"@id"),ttn($gtt,"id"),time());
			mas_q1($mas,$temptt,"SELECT count(*) as cartTotal FROM carts WHERE cart_userid = '%s'",ttn($gtt,"id"));
			criterr("<span id=\"cartTotal\"><a href=\"/".SEONAME."/mycart/\">basket %s</a> items</span>",ttn($temptt,"cartTotal"));

		} else criterr("1");


	break;

	case "slCancelPayment":
		mas_qnr($mas,"DELETE FROM carts WHERE cart_userid = '%s'",ttn($gtt,"account_id"));
		mas_q1($mas,$temptt,"SELECT count(*) cartTotal FROM carts WHERE cart_userid = '%s'",ttn($gtt,"account_id"));
		if(!ttn($temptt,"cartTotal")) tkntbl_add($gtt,"cartTotal","<span id=\"cartTotal\"><a href=\"#\">basket 0</a> items</span>",1);
		else tkntbl_add($gtt,"cartTotal","<span id=\"cartTotal\"><a href=\"#\">basket ".ttn($temptt,"cartTotal")."</a> items</span>",1);

		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Top"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"slDoPaymentHeaderCancelled"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		tkntbl_add($tt,"error","You have cancelled this transaction",1);
		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"slDoPaymentCancelled"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Bottom"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		criterr(NULL);
	break;

	case "slPurchaseConfirmation":
		tkntbl_add($tt,"transid",rand_string(7),1);
		mas_qb($mas,"SELECT * FROM carts,seeds WHERE seed_id = cart_seedid AND cart_userid = '%s'",ttn($gtt,"account_id"));
			while(mas_qg($mas,$temptt)){
				if(!strcmp(ttn($temptt,"cart_quantity"),ttn($temptt,"seed_quantity"))){
					mas_qnr($mas2,"UPDATE seeds SET seed_enabled = 'N',seed_quantity = '0' WHERE seed_id = '%s'",ttn($temptt,"seed_id"));
					tkntbl_add($tt,"slOutofStock","1",1);
					slUpdateRequest($mas4,"seed_enabled","U",ttn($temptt,"seed_id"));
					$dev_null = system("php -q ".SEEDLIVING_ROOT_DIR."bin/tags.php");
				} else{
					mas_qnr($mas2,"UPDATE seeds SET  seed_quantity = '%s' WHERE seed_id = '%s'",(ttn($temptt,"seed_quantity") - ttn($temptt,"cart_quantity")),ttn($temptt,"seed_id"));
					slUpdateRequest($mas4,"seed_quantity","U",ttn($temptt,"seed_id"));
				}

				/* Figure payment to seedliving */
				mas_q1($mas2,$ftt,"SELECT * FROM accounts WHERE account_userid =  '%s'",ttn($temptt,"seed_userid"));

				if(!strcmp(ttn($ftt,"account_prepaid"),"Y")){
					$total = round((ttn($temptt,"seed_price")*ttn($temptt,"cart_quantity")),2);
					$fee1 = ($total*ttn($gtt,"fee_percent_pp"));
					$fee2 = (ttn($temptt,"cart_quantity")*ttn($gtt,"fee_peritem_pp"));


					mas_qnr($mas2,"INSERT INTO sales VALUES('','%s','%s','%s','%s','%s','%s','%s','%s','%s','Y','','%s','')"
					,ttn($ftt,"account_id")
					,ttn($temptt,"seed_id")
					,ttn($temptt,"seed_price")
					,ttn($temptt,"seed_shipcost")
					,($total+ttn($temptt,"seed_shipcost"))
					,ttn($temptt,"cart_quantity")
					,ttn($gtt,"account_id")
					,stripNum($fee1,2)
					,stripNum($fee2,2)
					,time()
					);
					slUpdateRequest($mas4,"sales","A",mas_insert_id($mas2));
					$totalfees = stripNum(($fee1+$fee2),2);
					mas_qnr($mas2,"UPDATE pres SET pre_reamount = pre_reamount - '%s' WHERE pre_accountid = '%s'",$totalfees,ttn($ftt,"account_id"));
					slUpdateRequest($mas4,"pres","U",ttn($ftt,"account_id"));
				} elseif(!strcmp(ttn($ftt,"account_preapproval"),"Y")){
					if(ttn($ftt,"account_pakey")){
						$total = (ttn($temptt,"seed_price")*ttn($temptt,"cart_quantity"));
						$fee1 = ($total*ttn($gtt,"fee_percent_pa"));
						$fee2 = (ttn($temptt,"cart_quantity")*ttn($gtt,"fee_peritem_pa"));
						mas_qnr($mas2,"INSERT INTO sales VALUES('','%s','%s','%s','%s','%s','%s','%s','%s','%s','N','%s','%s','')"
						,ttn($ftt,"account_id")
						,ttn($temptt,"seed_id")
						,ttn($temptt,"seed_price")
						,ttn($temptt,"seed_shipcost")
						,($total+ttn($temptt,"seed_shipcost"))
						,ttn($temptt,"cart_quantity")
						,ttn($gtt,"account_id")
						,stripNum($fee1,2)
						,stripNum($fee2,2)
						,ttn($tt,"transid")
						,time()
						);
						slUpdateRequest($mas4,"sales","A",mas_insert_id($mas2));
						$totalfees = stripNum(($fee1+$fee2),2);
						mas_qnr($mas2,"UPDATE preas SET prea_reamount = prea_reamount - '%s' WHERE prea_accountid = '%s'",$totalfees,ttn($ftt,"account_id"));
						slUpdateRequest($mas4,"preas","U",ttn($ftt,"account_id"));
					}
				} elseif(!strcmp(ttn($ftt,"account_unl4"),"Y") || !strcmp(ttn($ftt,"account_unl2"),"Y")){
						$total = (ttn($temptt,"seed_price")*ttn($temptt,"cart_quantity"));
						$fee1 = ($total*ttn($gtt,"fee_percent_pa"));
						$fee2 = (ttn($temptt,"cart_quantity")*ttn($gtt,"fee_peritem_pa"));
						mas_qnr($mas2,"INSERT INTO sales VALUES('','%s','%s','%s','%s','%s','%s','%s','%s','%s','N','%s','%s','')"
						,ttn($ftt,"account_id")
						,ttn($temptt,"seed_id")
						,ttn($temptt,"seed_price")
						,ttn($temptt,"seed_shipcost")
						,($total+ttn($temptt,"seed_shipcost"))
						,ttn($temptt,"cart_quantity")
						,ttn($gtt,"account_id")
						,stripNum($fee1,2)
						,stripNum($fee2,2)
						,ttn($tt,"transid")
						,time()
						);
						slUpdateRequest($mas4,"sales","A",mas_insert_id($mas2));
				} else criterr("Payment Error");



		} mas_qe($mas);

		/* Send Payments to seedliving */
		if(!strcmp(ttn($ftt,"account_unl4"),"Y") || !strcmp(ttn($ftt,"account_unl2"),"Y")){
			mas_qb($mas,"select sale_accountid,(SUM(sale_fee1)+SUM(sale_fee2)) as totalfeesown from sales where sale_feespaid = 'N' AND sale_transid = '%s' group by sale_accountid",ttn($tt,"transid"));
			tkntbl_ftable($ftt);
			while(mas_qg($mas,$temptt)){
				mas_q1($mas2,$ftt,"SELECT * FROM accounts WHERE account_id = '%s'",ttn($temptt,"sale_accountid"));
				mas_qnr($mas2,"UPDATE sales SET sale_feespaid = 'Y' WHERE sale_accountid = '%s' AND sale_transid = '%s'",ttn($ftt,"account_id"),ttn($tt,"transid"));
			} mas_qe($mas);
		} else {
			mas_qb($mas,"select sale_accountid,(SUM(sale_fee1)+SUM(sale_fee2)) as totalfeesown from sales where sale_feespaid = 'N' AND sale_transid = '%s' group by sale_accountid",ttn($tt,"transid"));
			tkntbl_ftable($ftt);
			while(mas_qg($mas,$temptt)){
				mas_q1($mas2,$ftt,"SELECT * FROM accounts WHERE account_id = '%s'",ttn($temptt,"sale_accountid"));
				$err = pp_chainpayments("PAY","http://www.seedliving.ca","http://www.seedliving.ca","CAD",ttn($ftt,"account_email"),ttn($ftt,"account_pakey"),"EACHRECEIVER",array(ADMIN_EMAIL),array("false"),array(stripNum(ttn($temptt,"totalfeesown"),2)),"Seed Living Fees");
				if(!$err){
					mas_qnr($mas2,"UPDATE sales SET sale_feespaid = 'Y' WHERE sale_accountid = '%s' AND sale_transid = '%s'",ttn($ftt,"account_id"),ttn($tt,"transid"));
				} else die($err);
			} mas_qe($mas);
		}




				/* Send To Buyer */
		tkntbl_ftable($temptt);
		tkntbl_ftable($dtt);
		tkntbl_ftable($ftt);


		$userItemCount=array();
		mas_qb($mas,"SELECT seed_userid, count(seed_id) as cnt, sum(cart_quantity) as qcnt FROM carts,seeds WHERE cart_seedid = seed_id AND cart_userid = '%s' group by seed_userid",ttn($gtt,"account_id"));
		while(mas_qg($mas,$temptt)){
			$userItemCount[ttn($temptt,"seed_userid")][0] =  ttn($temptt,"cnt");
			$userItemCount[ttn($temptt,"seed_userid")][1] =  1;
			$userItemCount[ttn($temptt,"seed_userid")][2] =  ttn($temptt,"qcnt");
		} mas_qe($mas);

		tkntbl_ftable($temptt);

		mas_qb($mas,"SELECT * FROM sales WHERE sale_transid = '%s'",ttn($tt,"transid"));
		tmplt_proc_ex_tt(TEMPLROOT,tkntbl_search($tmpl,"slCheckoutSummaryTopEmail"),OPENTAG,CLOSETAG,1,stdout,$tt,"userPurchaseSummary",array(&$tt,&$ftt));
		while(mas_qg($mas,$temptt)){
				mas_q1($mas2,$dtt,"SELECT * FROM seeds WHERE seed_id = '%s'",ttn($temptt,"sale_seedid"));
				mas_q1($mas2,$dtt,"SELECT * FROM accounts WHERE account_id = '%s'",ttn($temptt,"sale_buyerid"));
				mas_q1($mas2,$dtt,"SELECT * FROM users WHERE user_id = '%s'",ttn($dtt,"account_userid"));
				mas_q1($mas2,$dtt,"SELECT account_username as account_na FROM accounts WHERE account_id = '%s'",ttn($temptt,"sale_accountid"));
				tkntbl_add($dtt,"seed_q",ttn($temptt,"sale_numitems"),1);
				tkntbl_snprintf($dtt,"itemTotal",1,MAX_RESULTS,"%s",(ttn($dtt,"seed_q")*ttn($dtt,"seed_price")));
				$subtotal = ($subtotal+ttn($dtt,"itemTotal"));

				if($userItemCount[ttn($dtt,"seed_userid")][0] > 1){
					if($userItemCount[ttn($dtt,"seed_userid")][1]){
						if(ttn($dtt,"seed_shipcost2")>0) {
							if($userItemCount[ttn($dtt,"seed_userid")][2]>10) tkntbl_add($dtt,"seed_shipcost",(ttn($dtt,"seed_shipcost2")*2),1);
							else tkntbl_add($dtt,"seed_shipcost",ttn($dtt,"seed_shipcost2"),1);
						}
							$userItemCount[ttn($dtt,"seed_userid")][1] = 0;
					} else {
						tkntbl_add($dtt,"seed_shipcost","",1);
					}
				} else {
					if($userItemCount[ttn($dtt,"seed_userid")][2]>10) tkntbl_add($dtt,"seed_shipcost",(ttn($dtt,"seed_shipcost")*2),1);
				}

				$shiptotal = ($shiptotal+ttn($dtt,"seed_shipcost"));


				tmplt_proc_ex_tt(TEMPLROOT,tkntbl_search($tmpl,"slCheckoutSummaryRowEmail"),OPENTAG,CLOSETAG,2,stdout,$tt,"userPurchaseSummary",array(&$tt,&$ftt,&$dtt));
		} mas_qe($mas);

	if($shiptotal) tkntbl_add($tt,"shiptotal",$shiptotal,1);
	else tkntbl_add($tt,"shiptotal","0.00",1);

	tkntbl_add($tt,"subtotal",$subtotal,1);
	tkntbl_add($tt,"grandtotal",(ttn($tt,"subtotal")+ttn($tt,"shiptotal")),1);

	tmplt_proc_ex_tt(TEMPLROOT,tkntbl_search($tmpl,"slCheckoutSummaryBottomEmail"),OPENTAG,CLOSETAG,2,stdout,$tt,"userPurchaseSummary",array(&$tt,&$ftt));

	tmplt_proc_ex_tt(TEMPLROOT,tkntbl_search($tmpl,"secureBuyerSaleEmail_dev"),OPENTAG,CLOSETAG,1,stdout,$tt,"message",array(&$tt,&$gtt,&$dtt,&$temptt));
	$headers  = "MIME-Version: 1.0\n";
	$headers .= "Content-type: text/html; charset=iso-8859-1\n";
	$headers .= "To: ".ttn($dtt,"account_email")."\n";
	$headers .= "bcc: seedliving@seeds.ca\n";
	//$headers .= "bcc: colin@anlanda.com\n";
	//$headers .= "bcc: sunshine@seedliving.ca\n";
	$headers .= "From: SeedLiving <".SITEEMAIL.">\n";
	$subject = "SeedLiving - Purchase Summary - ".date("F j, Y, g:i a");
	mail(NULL,$subject,ttn($tt,"message"),$headers);


	   /* Send Email Seller */
	    tkntbl_ftable($temptt);
		tkntbl_ftable($dtt);
		mas_qb($mas,"SELECT * FROM sales WHERE sale_transid = '%s'",ttn($tt,"transid"));
		while(mas_qg($mas,$temptt)){
			/* send to seller */
			mas_q1($mas2,$dtt,"SELECT * FROM accounts WHERE account_id = '%s'",ttn($temptt,"sale_accountid"));
			mas_q1($mas2,$dtt,"SELECT * FROM users WHERE user_id = '%s'",ttn($dtt,"account_userid"));
			mas_q1($mas2,$dtt,"SELECT * FROM seeds WHERE seed_id = '%s'",ttn($temptt,"sale_seedid"));
			mas_q1($mas2,$dtt,"SELECT account_email as account_email_b, account_username as account_username_b,user_lname as user_lname_b,user_fname as user_fname_b,user_address as user_address_b,user_city as user_city_b,user_state as user_state_b,user_country as user_country_b,user_zip as user_postalcode_b FROM accounts,users WHERE account_id = '%s' and account_userid = user_id",ttn($temptt,"sale_buyerid"));
			tmplt_proc_ex_tt(TEMPLROOT,tkntbl_search($tmpl,"secureSellerSaleEmail"),OPENTAG,CLOSETAG,1,stdout,$tt,"message",array(&$tt,&$gtt,&$dtt,&$temptt));
			$headers  = "MIME-Version: 1.0\n";
			$headers .= "Content-type: text/html; charset=iso-8859-1\n";
			$headers .= "To: ".ttn($dtt,"account_email")."\n";
			$headers .= "bcc: seedliving@seeds.ca\n";
// 			$headers .= "bcc: colin@anlanda.com\n";
//			$headers .= "bcc: sunshine@seedliving.ca\n";
			$headers .= "From: SeedLiving <".SITEEMAIL.">\n";
			$subject = "SeedLiving - Notification of sale for ".ttn($dtt,"seed_title");
			mail(NULL,$subject,ttn($tt,"message"),$headers);

		} mas_qe($mas);

		/* Empty Cart */
		mas_qnr($mas,"DELETE FROM carts WHERE cart_userid = '%s'",ttn($gtt,"account_id"));
		/* Update */

		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Top"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"slDoPaymentHeader"),OPENTAG,CLOSETAG,stdout,1,array(&$tt));
		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"slDoPaymentSuccess"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Bottom"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		criterr(NULL);
	break;

	case "slDoPayment":
			$actionType			= "PAY";
			$cancelUrl			= "http://www.seedliving.ca/".SEONAME."/slCancelPayment/";
			$returnUrl			= "http://www.seedliving.ca/".SEONAME."/slPurchaseConfirmation/";
			$ipnNotificationUrl = "";
			$currencyCode		= ttn($tt,"currency");
			$reverseAllParallelPaymentsOnError = "";

			$receiverEmailArray = array();
            $receiverAmountArray = array();
            $receiverPrimaryArray = array();
            $receiverInvoiceIdArray = array();

			$senderEmail= ttn($gtt,"account_email");
			$preapprovalKey= "";
			$feesPayer= "EACHRECEIVER";
			$pin = "";

			$temp_a = explode(",",ttn($tt,"recievers"));
			for($c=0;$c<count($temp_a);$c++){
				$receiverEmailArray[$c] = $temp_a[$c];
				$receiverPrimaryArray[$c] = "false";
			}

			$temp_a = explode(",",ttn($tt,"recievers_amount"));
			for($c=0;$c<count($temp_a);$c++){
				$receiverAmountArray[$c] = $temp_a[$c];
			}

			$resArray = CallPay ($actionType, $cancelUrl, $returnUrl, $currencyCode, $receiverEmailArray,
						$receiverAmountArray, $receiverPrimaryArray, $receiverInvoiceIdArray,
						$feesPayer, $ipnNotificationUrl, "SeedLiving Purchase", $pin, $preapprovalKey,
						$reverseAllParallelPaymentsOnError, "", $trackingId
			);

			$ack = strtoupper($resArray["responseEnvelope.ack"]);

			if($ack=="SUCCESS"){
				if ("" == $preapprovalKey){
					$cmd = "cmd=_ap-payment&paykey=" . urldecode($resArray["payKey"]);
					RedirectToPayPal ( $cmd );
				} else {
					tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Top"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
					tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"slDoPaymentHeaderFailed"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
					tkntbl_add($tt,"error","You were not preapproved for this transaction.",1);
					tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"slDoPaymentError"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
					tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Bottom"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
				}
			} else {
				tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Top"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
				tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"slDoPaymentHeaderFailed"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
				tkntbl_add($tt,"error",$resArray['error(0).message'],1);
				tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"slDoPaymentError"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
				tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Bottom"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
			}

	break;

	case "checkout":
		header("Cache-Control: no-cache");
		$subtotal= "";
		$shiptotal = 0.0;
		$itemcount = 0;
		$recievers = array();
		$c=0;
		mas_q1($mas,$dtt,"select * from breadcrumbs where bc_search = 'Y' and (bc_accountid = '%s' OR bc_ip = '%s' ) order by bc_tsadd desc",ttn($gtt,"account_id"),ttn($gtt,"REMOTE_ADDR"));

		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Top"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"slCheckoutHeader"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt,&$dtt));
		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"slCheckoutSummaryTop"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));

		/* update cart q's */
		mas_qb($mas,"SELECT * FROM carts WHERE cart_userid = '%s'",ttn($gtt,"account_id"));
		while(mas_qg($mas,$temptt)){
			tkntbl_snprintf($temptt,"seed_q",1,MAX_RESULTS,"%s",ttn($tt,ttn($temptt,"cart_seedid")."q"));
			if(ttn($temptt,"seed_q")) mas_qnr($mas2,"UPDATE carts SET cart_quantity = '%s' WHERE cart_seedid = '%s'",ttn($temptt,"seed_q"),ttn($temptt,"cart_seedid"));
		} mas_qe($mas);


		tkntbl_ftable($temptt);
		$userItemCount=array();
		mas_qb($mas,"SELECT seed_userid, count(seed_id) as cnt, sum(cart_quantity) as qcnt FROM carts,seeds WHERE cart_seedid = seed_id AND cart_userid = '%s' group by seed_userid",ttn($gtt,"account_id"));
		while(mas_qg($mas,$temptt)){
			$userItemCount[ttn($temptt,"seed_userid")][0] =  ttn($temptt,"cnt");
			$userItemCount[ttn($temptt,"seed_userid")][1] =  1;
			$userItemCount[ttn($temptt,"seed_userid")][2] =  ttn($temptt,"qcnt");
		} mas_qe($mas);

		tkntbl_ftable($temptt);


		mas_qb($mas,"SELECT * FROM carts,seeds WHERE cart_seedid = seed_id AND cart_userid = '%s' order by seed_userid,cart_quantity desc",ttn($gtt,"account_id"));
		//printf("SELECT * FROM carts,seeds WHERE cart_seedid = seed_id AND cart_userid = '%s'",ttn($gtt,"account_id"));
		while(mas_qg($mas,$temptt)){
			//if(ttn($temptt,"cart_quantity")) tkntbl_snprintf($temptt,"seed_q",1,MAX_RESULTS,"%s",ttn($temptt,"cart_quantity"));
			//else

			tkntbl_snprintf($temptt,"seed_q",1,MAX_RESULTS,"%s",ttn($tt,ttn($temptt,"seed_id")."q"));

			if($userItemCount[ttn($temptt,"seed_userid")][0] > 1){
				if($userItemCount[ttn($temptt,"seed_userid")][1]){
					if(ttn($temptt,"seed_shipcost2")>0) {
						if($userItemCount[ttn($temptt,"seed_userid")][2]>10) tkntbl_add($temptt,"seed_shipcost",(ttn($temptt,"seed_shipcost2")*2),1);
						else tkntbl_add($temptt,"seed_shipcost",ttn($temptt,"seed_shipcost2"),1);
					}
					$userItemCount[ttn($temptt,"seed_userid")][1] = 0;
				} else {
					tkntbl_add($temptt,"seed_shipcost","",1);
				}
			} else {
				if($userItemCount[ttn($temptt,"seed_userid")][2]>10) tkntbl_add($temptt,"seed_shipcost",(ttn($temptt,"seed_shipcost")*2),1);
			}

			tkntbl_add($tt,"seed_currency",ttn($temptt,"seed_currency"),1);


			tkntbl_snprintf($temptt,"itemTotal",1,MAX_RESULTS,"%s",(ttn($temptt,"seed_q")*ttn($temptt,"seed_price")));
			$subtotal = ($subtotal+ttn($temptt,"itemTotal"));
			$shiptotal = ($shiptotal+ttn($temptt,"seed_shipcost"));
			$itemcount = ($itemcount+ttn($temptt,"seed_q"));
			mas_q1($mas2,$dtt,"SELECT * FROM accounts,users WHERE account_userid = user_id AND user_id = '%s'",ttn($temptt,"seed_userid"));
			if(count($recievers)){
				if(in_array(ttn($dtt,"account_email"),$recievers)){
					$ky = array_search(ttn($dtt,"account_email"),$recievers);
					$recievers_amount[$ky] = ($recievers_amount[$ky]+(ttn($temptt,"itemTotal")+ttn($temptt,"seed_shipcost")));
				} else {
					$recievers[$c] = ttn($dtt,"account_email");
					$recievers_amount[$c] = (ttn($temptt,"itemTotal")+ttn($temptt,"seed_shipcost"));
				}
			} else {
				$recievers[$c] = ttn($dtt,"account_email");
				$recievers_amount[$c] = (ttn($temptt,"itemTotal")+ttn($temptt,"seed_shipcost"));
			}
			tkntbl_add($tt,"account_na",ttn($dtt,"account_username"),1);
			tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"slCheckoutSummaryRow"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt,&$temptt));
			tkntbl_ftable($temptt);
			tkntbl_ftable($dtt);
			$c++;
		} mas_qe($mas);


		$userItemCount=array();

		tkntbl_snprintf($tt,"recievers",2,MAX_RESULTS,"%s",implode(",", $recievers));
		tkntbl_snprintf($tt,"recievers_amount",2,MAX_RESULTS,"%s",implode(",", $recievers_amount));


		if($shiptotal) tkntbl_add($tt,"shiptotal",$shiptotal,1);
		else tkntbl_add($tt,"shiptotal","0.00",1);
		tkntbl_add($tt,"subtotal",$subtotal,1);
		//tkntbl_add($tt,"subtotalfee",($subtotal*ttn($gtt,"fee_percent")),1);
		//tkntbl_add($tt,"itemfeetotal",($itemcount*ttn($gtt,"fee_peritem")),1);
		//tkntbl_add($tt,"grandtotal",(ttn($tt,"subtotal")+ttn($tt,"subtotalfee")+ttn($tt,"itemfeetotal")+ttn($tt,"shiptotal")),1);
		tkntbl_add($tt,"grandtotal",(ttn($tt,"subtotal")+ttn($tt,"shiptotal")),1);

		//tkntbl_add($tt,"seed_amount",(ttn($tt,"subtotalfee")+ttn($tt,"itemfeetotal")),1);

		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"slCheckoutSummaryBottom"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"slPaymentDetails"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Bottom"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
	break;

	case "feeadmin":
		if(ttn($tt,"fee_percent_pp") && ttn($tt,"fee_percent_pa") && ttn($tt,"fee_peritem_pp") && ttn($tt,"fee_peritem_pa") && ttn($tt,"fee_feature")){
			mas_qnr($mas,"UPDATE fees SET fee_percent_pp='%s', fee_percent_pa='%s', fee_peritem_pp='%s', fee_peritem_pa='%s', fee_feature='%s' WHERE fee_id = '1'",
			ttn($tt,"fee_percent_pp"),
			ttn($tt,"fee_percent_pa"),
			ttn($tt,"fee_peritem_pp"),
			ttn($tt,"fee_peritem_pa"),
			ttn($tt,"fee_feature"));
			//printf("UPDATE fees SET fee_percent='%s', fee_peritem='%s' WHERE fee_id = '1'",ttn($tt,"fee_percent"),ttn($tt,"fee_peritem"));
			mas_q1($mas,$gtt,"SELECT * FROM fees");
		}
		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Top"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"feeAdmin"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Bottom"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		criterr(NULL);
	break;

	case "mycart":
        list($ok,$s) = cart($mas,$mas2,$dtt,$tmpl,$gtt,$tt,$temptt,$oSLiv);
        if( $ok ) {
            echo $s;
        } else {
            $oSLiv->GotoLoginPage();
        }
        criterr(NULL);
        break;

	case "userProfile":
// this shows someone else's profile; use accountProfile for the current user
/*TODONEXT*/
		mas_q1($mas,$temptt,"SELECT * FROM accounts, users WHERE account_userid = user_id AND account_id = '%s'",ttn($tt,"@id"));
		mas_q1($mas,$temptt,"SELECT count(*) as sellersSeedCount FROM seeds WHERE seed_quantity > 0 AND seed_userid = '%s' AND seed_enabled = 'Y' AND seed_tradetable = 'N'",ttn($temptt,"user_id"));
		mas_q1($mas,$temptt,"SELECT count(*) as sellersSwapCount FROM seeds WHERE seed_quantity > 0 AND seed_userid = '%s' AND seed_enabled = 'Y' AND seed_trade = 'Y'",ttn($temptt,"user_id"));
		mas_q1($mas,$temptt,"SELECT count(*) as totalItems FROM seeds WHERE seed_quantity > 0 AND seed_userid = '%s' AND seed_enabled = 'Y'",ttn($temptt,"user_id"));

$o = new imgman();
$fname = $o->getFilename( 'users', ttn($temptt,"user_id") );
if( empty($fname) ) $fname = 'noImageAvailable.jpg';
tkntbl_add($gtt,"user_image",$fname,1);

//		if(file_exists(IMAGEROOT."users/".ttn($temptt,"user_id")."_1.jpg")){
//				 	tkntbl_snprintf($gtt,"user_image",1,MAX_RESULTS,"users/%s_1.jpg",ttn($temptt,"user_id"));
//		} else tkntbl_add($gtt,"user_image","noImageAvailable.jpg",1);

		/* User Comments */
		mas_qb($mas,"SELECT * FROM userComments,accounts WHERE uc_accountid = '%s' AND uc_accountidby = account_id order by uc_tsadd desc",ttn($tt,"@id"));
		if(!$mas->mas_row_cnt) tkntbl_add($tt,"userComments","There are currently no comments",1);
		while(mas_qg($mas,$ftt)){
			tmplt_proc_ex_tt(TEMPLROOT,tkntbl_search($tmpl,"userCommentsSummary"),OPENTAG,CLOSETAG,2,stdout,$tt,"userComments",array(&$tt,&$ftt));
		} mas_qe($mas);


		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Top"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		echo $oSLiv->oTmpl->ExpandTmpl( "userProfile", GetTokensRA(array(&$tt,&$gtt,&$temptt)));
		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Bottom"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		criterr(NULL);
	break;

	case "userCommentSave":
		if(ttn($gtt,"account_id")){
			mas_qnr($mas,"INSERT INTO userComments VALUES ('','%s','%s','%s','%s')",ttn($tt,"uc_text"),ttn($tt,"user"),ttn($gtt,"account_id"),time());
			slUpdateRequest($mas4,"userComments","A",mas_insert_id($mas));
			criterr("Comment has been added.");
		} else criterr("You are not logged in.");
		criterr(NULL);
	break;

	case "seedCommentSave":
		if(ttn($gtt,"account_id")){
			mas_qnr($mas,"INSERT INTO seedComments VALUES ('','%s','%s','%s','%s')",ttn($tt,"seed"),ttn($tt,"sc_text"),ttn($gtt,"account_id"),time());
			slUpdateRequest($mas4,"seedComments","A",mas_insert_id($mas));
			criterr("Comment has been added.");
		} else criterr("You are not logged in.");
		criterr(NULL);
	break;

	case "userSearchSwap":
		$c=0;
		mas_q1($mas,$temptt,"SELECT count(*) as Total FROM seeds,accounts WHERE seed_quantity > 0 AND account_userid = seed_userid AND account_id = '%s' AND seed_enabled = 'Y' AND seed_trade = 'Y'",ttn($tt,"@id"));


		$page = ttn($tt,"page");
		$totalperpage = "18";
		if(!$page) $page = 1;
		$offset = ($page*$totalperpage)-$totalperpage;
		if(ttn($temptt,"Total")<=$totalperpage) $totalpages = 1;
		else $totalpages = ceil((ttn($temptt,"Total")/$totalperpage));



		mas_q1($mas,$temptt,"SELECT * FROM accounts WHERE account_id = '%s'",ttn($tt,"@id"));
		mas_qb($mas,"SELECT * FROM seeds,accounts WHERE seed_quantity > 0 AND account_userid = seed_userid AND account_id = '%s' AND seed_enabled = 'Y' AND seed_trade = 'Y' ORDER BY seed_tsadd LIMIT %s,%s",ttn($tt,"@id"),$offset,$totalperpage);



		pagination($tt,$temptt,$tmpl,$gtt,$page,ttn($temptt,"account_username")."_Swap",$totalpages);

		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Top"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		tmplt_proc_ex_tt(TEMPLROOT,tkntbl_search($tmpl,"seedSplashTop"),OPENTAG,CLOSETAG,1,stdout,$tt,"userSwapListings",array(&$tt,&$temptt));
		while(mas_qg($mas,$temptt)){
			if($c==2){
				tkntbl_add($tt,"last"," last",1);
			}

			if(file_exists(IMAGEROOT."seeds/thmb/".ttn($temptt,"seed_id")."_1.jpg")){
				 	tkntbl_snprintf($gtt,"seed_image",1,MAX_RESULTS,"seeds/thmb/%s_1.jpg",ttn($temptt,"seed_id"));
				} else tkntbl_add($gtt,"seed_image","noImageAvailable.jpg",1);
$img = $oSLiv->ImgSrc('seeds/thmb',ttn($temptt,"seed_id"),1,true,false);
tkntbl_add($gtt,"seed_image",$img,1);
tkntbl_add($gtt,"seed_sfile",$img,1);
tkntbl_add($gtt,"account_username",ttn($temptt,"account_username"),1);
			tmplt_proc_ex_tt(TEMPLROOT,tkntbl_search($tmpl,"seedSplashRow"),OPENTAG,CLOSETAG,2,stdout,$tt,"userSwapListings",array(&$tt,&$temptt,&$gtt));

			$c++;
			if($c==3){
				$c=0;
				tkntbl_add($tt,"last","",1);
			}


		} mas_qe($mas);
		tmplt_proc_ex_tt(TEMPLROOT,tkntbl_search($tmpl,"seedSplashBottom"),OPENTAG,CLOSETAG,2,stdout,$tt,"userSwapListings",array(&$tt,&$temptt));
		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"userSearchSwap"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt,&$temptt));
		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Bottom"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		criterr(NULL);
	break;

	case "userSearch":
		$c=0;
		mas_q1($mas,$temptt,"SELECT count(*) as Total FROM seeds,accounts WHERE seed_quantity > 0 AND account_userid = seed_userid AND account_id = '%s' AND seed_tradetable = 'N' AND seed_enabled = 'Y'",ttn($tt,"@id"));

		$page = ttn($tt,"page");
		$totalperpage = "18";
		if(!$page) $page = 1;
		$offset = ($page*$totalperpage)-$totalperpage;
		if(ttn($temptt,"Total")<=$totalperpage) $totalpages = 1;
		else $totalpages = ceil((ttn($temptt,"Total")/$totalperpage));

		mas_q1($mas,$temptt,"SELECT * FROM accounts WHERE account_id = '%s'",ttn($tt,"@id"));
		mas_qb($mas,"SELECT * FROM seeds,accounts WHERE seed_quantity > 0 AND account_userid = seed_userid AND account_id = '%s' AND seed_enabled = 'Y' AND seed_tradetable = 'N' ORDER BY seed_tsmod LIMIT %s,%s",ttn($tt,"@id"),$offset,$totalperpage);


		pagination($tt,$temptt,$tmpl,$gtt,$page,ttn($temptt,"account_username")."_Items",$totalpages);

		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Top"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		tmplt_proc_ex_tt(TEMPLROOT,tkntbl_search($tmpl,"seedSplashTop"),OPENTAG,CLOSETAG,1,stdout,$tt,"userListings",array(&$tt,&$temptt));
		while(mas_qg($mas,$temptt)){
			if($c==2){
				tkntbl_add($tt,"last"," last",1);
			}

			if(file_exists(IMAGEROOT."seeds/thmb/".ttn($temptt,"seed_id")."_1.jpg")){
				 	tkntbl_snprintf($gtt,"seed_image",1,MAX_RESULTS,"seeds/thmb/%s_1.jpg",ttn($temptt,"seed_id"));
				} else tkntbl_add($gtt,"seed_image","noImageAvailable.jpg",1);
$img = $oSLiv->ImgSrc('seeds/thmb',ttn($temptt,"seed_id"),1,true,false);
tkntbl_add($gtt,"seed_image",$img,1);
tkntbl_add($gtt,"seed_sfile",$img,1);

			tkntbl_add($gtt,"account_username",ttn($temptt,"account_username"),1);
			tmplt_proc_ex_tt(TEMPLROOT,tkntbl_search($tmpl,"seedSplashRow"),OPENTAG,CLOSETAG,2,stdout,$tt,"userListings",array(&$tt,&$temptt,&$gtt));

			$c++;
			if($c==3){
				$c=0;
				tkntbl_add($tt,"last","",1);
			}

		} mas_qe($mas);
		tmplt_proc_ex_tt(TEMPLROOT,tkntbl_search($tmpl,"seedSplashBottom"),OPENTAG,CLOSETAG,2,stdout,$tt,"userListings",array(&$tt,&$temptt));
		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"userSearch"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt,&$temptt));
		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Bottom"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		criterr(NULL);
	break;

    case "productDetails":
        $seed_id = ttn($tt,"@id");

// This was a horrible kluge. The init code puts the current user's account_* in gtt, but the seeds,users,accounts join here is for the
// seed's seller's account.  So this overwrites the current user's name with the seller's name, which the template used (previously) to make links
// to the seller's profile.  This has been remedied by using sellerUsername instead of account_username.
// Still, this join is bound to confuse somebody who uses account_*
        $raSUA = $oSLiv->kfdb->QueryRA( "SELECT * FROM seeds,users,accounts WHERE user_id=seed_userid AND account_userid=user_id AND "
                                       ."seed_quantity > 0 AND seed_id='$seed_id'" );
        if( !$raSUA ) {
// do this nicer
            slGenericError($tmpl,$gtt,"The seed you were looking for has been removed by user or is currently out of stock.");
        }
        $raTmpl = array_merge( $oSLiv->oSLivParms->GetRA(), $raSUA );

// Get rid of instances of account_* in the template, remove accounts from the join above.
// Use the User name instead of the account_username anyway.
        $raTmpl['sellerUsername'] = $raSUA['account_username'];

        mas_q1($mas,$temptt,"SELECT * FROM cats WHERE cat_id = '{$raSUA['seed_topcat']}'" );

        mas_q1($mas,$temptt,"SELECT count(*) as sellersSeedCount FROM seeds WHERE seed_userid = '{$raSUA['user_id']}' AND seed_enabled = 'Y' AND seed_tradetable = 'N'" );
        mas_q1($mas,$temptt,"SELECT count(*) as sellersSwapCount FROM seeds WHERE seed_userid = '{$raSUA['user_id']}' AND seed_enabled = 'Y' AND seed_trade = 'Y'" );
        mas_q1($mas,$temptt,"SELECT count(*) as totalItems FROM seeds WHERE seed_userid = '{$raSUA['user_id']}' AND seed_enabled = 'Y'" );

        /* Load Tags */
        mas_qb($mas,"SELECT * FROM tags,tagrel WHERE tagrel_seedid = '$seed_id' AND tag_id = tagrel_tagid" );

        $c=0;
        while( mas_qg($mas,$temptt) ) {
            if( ($c+1) != $mas->mas_row_cnt ) {
                tkntbl_add($temptt,"tag_name",$c == 0 ? (ucwords(ttn($temptt,"tag_name")).",") : (ttn($temptt,"tag_name").","),1);
            }
            tmplt_proc_ex_tt(TEMPLROOT,tkntbl_search($tmpl,"seedTagDesc"),OPENTAG,CLOSETAG,2,stdout,$tt,"seedTagDesc",array(&$tt,&$temptt,&$gtt));
            $c++;
        } mas_qe($mas);

        /* Load Comments */
        mas_qb($mas,"SELECT * FROM seedComments,accounts WHERE sc_accountid = account_id AND sc_seedid = '$seed_id' ORDER BY sc_tsadd desc" );
        if(!$mas->mas_row_cnt) tkntbl_add($tt,"seedComments","There are currently no comments",1);
        while(mas_qg($mas,$ftt)){
            tmplt_proc_ex_tt(TEMPLROOT,tkntbl_search($tmpl,"seedCommentsSummary"),OPENTAG,CLOSETAG,2,stdout,$tt,"seedComments",array(&$ftt));
        } mas_qe($mas);

        /* Images */
        $sfile = $oSLiv->ImgSrc('seeds',$seed_id,1,true,false);
        tkntbl_add($gtt,"seed_sfile",$sfile,1);
        tkntbl_add($gtt,"seed_sfile_1","",1);
        tkntbl_add($gtt,"seed_sfile_2","",1);
        tkntbl_add($gtt,"seed_sfile_3","",1);

        if( ($sfile = $oSLiv->ImgSrc( 'seeds',$seed_id, 1, true, false )) ) {
            tkntbl_add($gtt,"seed_sfile_1", "<img class='imgOver' src='$sfile' width='75px'/><br/>", 1 );
        }
        if( ($sfile = $oSLiv->ImgSrc( 'seeds',$seed_id, 2, true, false )) ) {
            tkntbl_add($gtt,"seed_sfile_2", "<img class='imgOver' src='$sfile' width='75px'/><br/>", 1 );
        }
        if( ($sfile = $oSLiv->ImgSrc( 'seeds',$seed_id, 3, true, false )) ) {
            tkntbl_add($gtt,"seed_sfile_3", "<img class='imgOver' src='$sfile' width='75px'/><br/>", 1 );
        }

        tkntbl_add($temptt,'seed_id',$seed_id,1);
        $raTmpl['seed_id'] = $seed_id;    // redundant because it comes from the SELECT above, but crucial for templates

        $raTmpl['basketLink'] = $oSLiv->Tmpl2( 'productDetailsLinkAddToBasket', null, $raTmpl );

        if( $raTmpl['seed_trade'] == "N" ) {
            // "add to basket" button
            if( !ttn($gtt,"account_id") ) {
                $raTmpl['slButtons'] = $oSLiv->Tmpl2( 'productDetailsBuyButton', null, $raTmpl );
            }
        } else if( ttn($gtt,"account_accesslevel") == "S" ) {
            // user can request a swap
            tmplt_proc_ex_tt(TEMPLROOT,tkntbl_search($tmpl,"productDetailsPreSwap"),OPENTAG,CLOSETAG,1,stdout,$tt,"productDetailsPreSwap",array(&$tt,&$ftt,&$temptt,&$gtt));
            tmplt_proc_ex_tt(TEMPLROOT,tkntbl_search($tmpl,"productDetailsRequestSwap"),OPENTAG,CLOSETAG,1,stdout,$tt,"productDetailsRequestSwap",array(&$tt,&$ftt,&$temptt,&$gtt));

            if( $raTmpl['seed_trade'] == "S" ) {
                // Replace the "Add to Basket" link with "Request a swap" link
                $raTmpl['basketLink'] = $oSLiv->Tmpl2( 'productDetailsLinkSwap', null, $raTmpl );
            }
        } else if( !ttn($gtt,"account_id") ) {
            $raTmpl['slButtons'] = $oSLiv->Tmpl2( $raTmpl['seed_trade'] == "S" ? 'productDetailsSwapButton' : 'productDetailsBuyButton',
                                                  null, $raTmpl );
        }

        mas_q1($mas,$dtt,"select * from breadcrumbs where bc_search = 'Y' and (bc_accountid = '%s' OR bc_ip = '%s' ) order by bc_tsadd desc",ttn($gtt,"account_id"),ttn($gtt,"REMOTE_ADDR"));

        // Set the <head> elements to refer to this product
        tkntbl_add($tt,"new_title",$raTmpl['seed_title'],1);
        tkntbl_add($tt,"new_desc2",$raTmpl['seed_desc'],1);
        tkntbl_add($tt,"new_keys",$raTmpl['seed_tagdesc'],1);

        $s = $oSLiv->Tmpl2( "productDetails", array(&$tt,&$gtt,&$temptt,$dtt), $raTmpl );
        echo $s;
        criterr(NULL);
        break;

    case "categorySearch":
        $c=0;
        if( ttn($tt,"tag_id") ) {
            mas_q1($mas,$temptt,"SELECT count(*) as Total1 FROM tagrel, seeds, accounts WHERE seed_quantity > 0 AND account_userid = seed_userid AND seed_id = tagrel_seedid AND seed_enabled = 'Y' and seed_topcat <> '%s' AND tagrel_tagid = '%s'",ttn($tt,"@id"),ttn($tt,"tag_id"));
            mas_q1($mas,$temptt,"SELECT count(*) as Total2 FROM seeds,accounts WHERE seed_quantity > 0 AND account_userid = seed_userid AND seed_topcat = '%s' AND seed_enabled = 'Y'",ttn($tt,"@id"));
            tkntbl_add($temptt,"Total",(ttn($temptt,"Total1")+ttn($temptt,"Total2")),1);
        } else {
            mas_q1($mas,$temptt,"SELECT count(*) as Total FROM seeds,accounts WHERE seed_quantity > 0 AND account_userid = seed_userid AND seed_topcat = '%s' AND seed_enabled = 'Y'",ttn($tt,"@id"));
        }
        $page = ttn($tt,"page");
        if( !$page ) $page = 1;
        $totalperpage = 18;
        $offset = ($page*$totalperpage)-$totalperpage;
        $totalpages = ttn($temptt,"Total") <= $totalperpage ? 1 : ceil((ttn($temptt,"Total")/$totalperpage));

        mas_q1($mas,$temptt,"SELECT * FROM cats WHERE cat_id = '%s'",ttn($tt,"@id"));

        if( ttn($tt,"tag_id") ) {
            mas_qb($mas,"SELECT * FROM seeds,accounts WHERE seed_quantity > 0 AND account_userid = seed_userid AND seed_topcat = '%s' AND seed_enabled = 'Y'"
                       ." union SELECT a.*,b.* FROM seeds as a,accounts as b,tagrel WHERE seed_quantity > 0 and account_userid = seed_userid AND tagrel_tagid = '%s' AND seed_id = tagrel_seedid AND seed_enabled = 'Y' ORDER BY seed_tsadd LIMIT %s,%s",
                       ttn($tt,"@id"),ttn($tt,"tag_id"),$offset,$totalperpage);
        } else {
            mas_qb($mas,"SELECT * FROM seeds,accounts WHERE seed_quantity > 0 AND account_userid = seed_userid AND seed_topcat = '%s' AND seed_enabled = 'Y' ORDER BY seed_tsadd LIMIT %s,%s",ttn($tt,"@id"),$offset,$totalperpage);
        }

        pagination($tt,$temptt,$tmpl,$gtt,$page,ttn($temptt,"cat_url"),$totalpages);

        $oSLiv->Tmpl( "Top", array(&$tt,&$gtt) );
        tmplt_proc_ex_tt(TEMPLROOT,tkntbl_search($tmpl,"seedSplashTop"),OPENTAG,CLOSETAG,1,stdout,$tt,"catListings",array(&$tt,&$temptt));
        while(mas_qg($mas,$temptt)) {
            if( $c==2 ) {
                tkntbl_add($tt,"last"," last",1);
            }
$img = $oSLiv->ImgSrc('seeds/thmb',ttn($temptt,"seed_id"),1,true,false);
            tkntbl_add($gtt,"seed_sfile",$img,1);

            $sRow = tkntbl_search($tmpl,"seedSplashRow");

            tkntbl_add($gtt,"account_username",ttn($temptt,"account_username"),1);
            tmplt_proc_ex_tt(TEMPLROOT,tkntbl_search($tmpl,"seedSplashRow"),OPENTAG,CLOSETAG,2,stdout,$tt,"catListings",array(&$tt,&$temptt,&$gtt));
            $c++;
            if($c==3){
                $c=0;
                tkntbl_add($tt,"last","",1);
            }
        } mas_qe($mas);
        tmplt_proc_ex_tt(TEMPLROOT,tkntbl_search($tmpl,"seedSplashBottom"),OPENTAG,CLOSETAG,2,stdout,$tt,"catListings",array(&$tt,&$temptt));
        $oSLiv->Tmpl( "categorySearch", array(&$tt,&$gtt,&$temptt) );
        $oSLiv->Tmpl( "Bottom", array(&$tt,&$gtt) );
        criterr(NULL);
        break;

	case "secure":
		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Top"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"secureAdmin"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Bottom"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		criterr(NULL);
	break;
	case "eventsSplash":
		tkntbl_rmv($gtt,"account_username");
		mas_qb($mas,"SELECT * FROM events WHERE event_enabled = 'Y' AND event_enddate > '%s' ORDER BY event_tsadd desc LIMIT 1",time());
		if(!$mas->mas_row_cnt) criterr("There are currently no events listed.");
		while(mas_qg($mas,$temptt)){
			if(!file_exists(IMAGEROOT."events/".ttn($temptt,"event_id")."_1.jpg")){
				tkntbl_add($tt,"noImages",1,1);
			}
			if(ttn($temptt,"event_startdate")==ttn($temptt,"event_enddate")) tkntbl_add($temptt,"event_time",date("F j, Y",ttn($temptt,"event_startdate")),1);
			else tkntbl_add($temptt,"event_time",date("F j, Y",ttn($temptt,"event_startdate"))."-".date("F j, Y",ttn($temptt,"event_startdate")),1);

			if(!strcmp(ttn($temptt,"event_location"),ttn($temptt,"event_city"))) tkntbl_add($temptt,"event_location",ttn($temptt,"event_location"),1);
			else tkntbl_add($temptt,"event_location",ttn($temptt,"event_location").",".ttn($temptt,"event_city"),1);

			mas_q1($mas2,$tt,"SELECT account_username,user_city FROM accounts,users WHERE account_id = '%s' and user_accountid = account_id",ttn($temptt,"event_postedby"));

			tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"eventsList"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt,&$temptt));
		} mas_qe($mas);
		criterr(NULL);
	break;
	case "newsSplash":
		$c=1;
		mas_qb($mas,"SELECT * FROM news WHERE new_enabled = 'Y' ORDER BY new_tsadd desc");
		if(!$mas->mas_row_cnt) criterr("There are currently no news items.");
		tkntbl_add($ftt,"slNewsPreview","",1);
		while(mas_qg($mas,$temptt)){
			mas_q1($mas2,$ftt,"SELECT * FROM accounts,users WHERE user_id = account_userid AND account_id= '%s'",ttn($temptt,"new_postedby"));
			tkntbl_snprintf($temptt,"new_desc",1,MAX_RESULTS,"%s",stripslashes(ttn($temptt,"new_desc")));
			if($c==10) tkntbl_add($tt,"newsClass"," snewsClass",1);
			tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"newsList"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$ftt,&$temptt));
			if($c==10) {
				tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"newsListMore"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$ftt,&$temptt));
			}
			$c++;
		} mas_qe($mas);
		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"newsListMoreLast"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$ftt,&$temptt));
		criterr(NULL);
	break;
	case "events":
		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Top"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		tkntbl_rmv($gtt,"account_username");
		mas_qb($mas,"SELECT * FROM events WHERE event_enabled = 'Y' AND event_enddate > '%s' ORDER BY event_tsadd desc LIMIT 1",time());
		if(!$mas->mas_row_cnt) criterr("There are currently no events listed.");
		while(mas_qg($mas,$temptt)){
			if(!file_exists(IMAGEROOT."events/".ttn($temptt,"event_id")."_1.jpg")){
				tkntbl_add($tt,"noImages",1,1);
			}
			if(ttn($temptt,"event_startdate")==ttn($temptt,"event_enddate")) tkntbl_add($temptt,"event_time",date("F j, Y",ttn($temptt,"event_startdate")),1);
			else tkntbl_add($temptt,"event_time",date("F j, Y",ttn($temptt,"event_startdate"))."-".date("F j, Y",ttn($temptt,"event_startdate")),1);

			if(!strcmp(ttn($temptt,"event_location"),ttn($temptt,"event_city"))) tkntbl_add($temptt,"event_location",ttn($temptt,"event_location"),1);
			else tkntbl_add($temptt,"event_location",ttn($temptt,"event_location").",".ttn($temptt,"event_city"),1);

			mas_q1($mas2,$tt,"SELECT account_username,user_city FROM accounts,users WHERE account_id = '%s' and user_accountid = account_id",ttn($temptt,"event_postedby"));

			tmplt_proc_ex_tt(TEMPLROOT,tkntbl_search($tmpl,"eventsList"),OPENTAG,CLOSETAG,2,stdout,$tt,"eventsList",array(&$tt,&$temptt));
		} mas_qe($mas);
		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"events"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Bottom"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		criterr(NULL);
	break;

	case "news":
		mas_q1($mas,$tt,"SELECT count(*) as totalNews FROM news WHERE new_enabled = 'Y'");
		mas_qb($mas,"SELECT * FROM news WHERE new_enabled = 'Y' ORDER BY new_tsadd desc");
		while(mas_qg($mas,$temptt)){
			mas_q1($mas2,$dtt,"SELECT * FROM accounts WHERE account_id = '%s'",ttn($temptt,"new_postedby"));
			tmplt_proc_ex_tt(TEMPLROOT,tkntbl_search($tmpl,"newsList"),OPENTAG,CLOSETAG,2,stdout,$tt,"newsList",array(&$tt,&$temptt,&$dtt));
			tkntbl_ftable($dtt);
		} mas_qe($mas);
		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Top"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"news"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Bottom"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		criterr(NULL);
	break;

	case "secureEventAdd":
		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Top"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"eventAdd"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"eventAddEdit"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Bottom"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		criterr(NULL);
	break;
	case "eventSave":
		header("Cache-Control: no-cache");
		tkntbl_add($tt,"event_startdate",mktime(ttn($tt,"event_starthour"),ttn($tt,"event_startmin"),0,ttn($tt,"start_month"),ttn($tt,"start_day"),ttn($tt,"start_year")),1);
		tkntbl_add($tt,"event_enddate",mktime(ttn($tt,"event_endhour"),ttn($tt,"event_endmin"),0,ttn($tt,"end_month"),ttn($tt,"end_day"),ttn($tt,"end_year")),1);
		tkntbl_snprintf($tt,"event_starttime",1,MAX_RESULTS,"%s:%s:00 %s",ttn($tt,"event_starthour"),ttn($tt,"event_startmin"),ttn($tt,"event_starttype"));
		tkntbl_snprintf($tt,"event_endtime",1,MAX_RESULTS,"%s:%s:00 %s",ttn($tt,"event_endhour"),ttn($tt,"event_endmin"),ttn($tt,"event_endtype"));
        tkntbl_add($tt,"event_postedby",ttn($gtt,"account_id"),1);
		tkntbl_add($tt,"event_enabled","Y",1);
		tkntbl_add($tt,"event_paid","Y",1);

		mas_lts($mas,$ftt,"events");
		if(!ttn($tt,"@id")){
			tkntbl_add($tt,"event_enabled","Y",1);
			tkntbl_add($tt,"event_tsadd",time(),1);
			mas_gri($mas,$tt,$ftt,0,"events");
			tkntbl_add($tt,"@id",mas_insert_id($mas),1);
			slUpdateRequest($mas4,"events","A",ttn($tt,"@id"));
		} else {
			tkntbl_add($tt,"event_tsmod",time(),1);
			mas_gru($mas,$tt,$ftt,"event_id",ttn($tt,"@id"),1,"events");
			slUpdateRequest($mas4,"events","U",ttn($tt,"@id"));
		}

		/* Handle Images */
		$c=1;
		while(list($key,$value) = each($_FILES['event_image']['name']))
		{
			if(!empty($value)){   // this will check if any blank field is entered
				$filename = $value;    // filename stores the value

				$filename=str_replace(" ","_",$filename);// Add _ inplace of blank space in file name, you can remove this line

				$add = IMAGEROOT."events/".ttn($tt,"@id")."_".$c.".jpg";   // upload directory path is set

				copy($_FILES['event_image']['tmp_name'][$key], $add);     //  upload the file to the server
				chmod("$add",0777);                 // set permission to the file.
				$c++;
			}
		}
		header("Location: /".SEONAME."/events/");
		criterr(NULL);
	break;
	case "secureEventEdit":
		mas_q1($mas,$temptt,"SELECT * FROM events WHERE event_id = '%s'",ttn($tt,"@id"));
		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Top"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"eventEdit"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"eventAddEdit"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt,&$temptt));
		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Bottom"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
	break;

	case "tableactions":
		if(!ttn($tt,"table")) criterr("Update failed - Error 2");
		if(!ttn($tt,"action")) criterr("Update failed - Error 2");
		if(!ttn($tt,"id")) criterr("Update failed - Error 2");

		$ids = explode("-",ttn($tt,"id"));
		for($c=0;$c<count($ids);$c++){
			if(!strcmp(ttn($tt,"action"),"Enable")) {
				mas_qnr($mas,"UPDATE %s SET %s_enabled = 'Y' WHERE %s_id = '%s'",ttn($tt,"table"),substr(ttn($tt,"table"),0,-1),substr(ttn($tt,"table"),0,-1),$ids[$c]);
				slUpdateRequest($mas4,substr(ttn($tt,"table"),0,-1),"U",$ids[$c]);
			}
			if(!strcmp(ttn($tt,"action"),"Disable")) {
				mas_qnr($mas,"UPDATE %s SET %s_enabled = 'N' WHERE %s_id = '%s'",ttn($tt,"table"),substr(ttn($tt,"table"),0,-1),substr(ttn($tt,"table"),0,-1),$ids[$c]);
				slUpdateRequest($mas4,substr(ttn($tt,"table"),0,-1),"U",$ids[$c]);
			}
			if(!strcmp(ttn($tt,"action"),"Delete")) {
				mas_qnr($mas,"DELETE FROM %s WHERE %s_id = '%s'",ttn($tt,"table"),substr(ttn($tt,"table"),0,-1),$ids[$c]);
				if(!strcmp(ttn($tt,"table"),"seeds")) {
					unlink(SEEDLIVING_ROOT_DIR."i/seeds/".$ids[$c]."_1.jpg");
					unlink(SEEDLIVING_ROOT_DIR."i/seeds/thmb/".$ids[$c]."_1.jpg");
					unlink(SEEDLIVING_ROOT_DIR."i/seeds/".$ids[$c]."_2.jpg");
					unlink(SEEDLIVING_ROOT_DIR."i/seeds/".$ids[$c]."_3.jpg");
					$dev_null= system("php -q ".SEEDLIVING_ROOT_DIR."bin/tags.php");
				}
			}
		}
		criterr("Actions complete.");
	break;
	case "setFeaturedSuccess":
		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Top"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"setFeaturedSuccess"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Bottom"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
	break;

	case "removeFeaturedSuccess":
		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Top"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"removeFeaturedSuccess"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Bottom"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
	break;

	case "removeFeatured":
		header("Cache-Control: no-cache");
		if(ttn($tt,"@id")){
			mas_q1($mas,$dtt,"SELECT * FROM seeds,accounts WHERE seed_userid = account_userid AND account_id = '%s' AND seed_id = '%s'",ttn($gtt,"account_id"),ttn($tt,"@id"));
			if($mas->mas_row_cnt && !strcmp(ttn($dtt,"seed_featured"),"Y")){
				mas_qnr($mas,"UPDATE seeds SET seed_featured = 'N' WHERE seed_id = '%s'",ttn($tt,"@id"));
				slUpdateRequest($mas4,"seedfeatured","U",ttn($tt,"@id"));
				header("Location: /".SEONAME."/removeFeaturedSuccess/");
			}
		}
	break;

	case "setFeatured":
		header("Cache-Control: no-cache");
		if(ttn($tt,"@id")){
			mas_q1($mas,$dtt,"SELECT * FROM seeds,accounts WHERE seed_userid = account_userid AND account_id = '%s' AND seed_id = '%s'",ttn($gtt,"account_id"),ttn($tt,"@id"));
			if($mas->mas_row_cnt && !strcmp(ttn($dtt,"seed_featured"),"N")){
				mas_q1($mas,$temptt,"SELECT * FROM pres WHERE pre_accountid = '%s'",ttn($gtt,"account_id"));
				if($mas->mas_row_cnt){
					if(ttn($temptt,"pre_reamount")>ttn($gtt,"fee_feature")){
						mas_qnr($mas,"UPDATE pres SET pre_reamount = (pre_reamount - %s) WHERE pre_accountid = '%s'",ttn($gtt,"fee_feature"),ttn($gtt,"account_id"));
						mas_qnr($mas,"UPDATE seeds SET seed_featured = 'Y' WHERE seed_id = '%s'",ttn($tt,"@id"));
						slUpdateRequest($mas4,"seedfeatured","U",ttn($tt,"@id"));
						header("Location: /".SEONAME."/setFeaturedSuccess/");
					} else {
						tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Top"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
						tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"slNoPrepaidFunds"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
						tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Bottom"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
					}
				} else {
					mas_q1($mas,$temptt,"SELECT * FROM preas WHERE prea_accountid = '%s'",ttn($gtt,"account_id"));
					if($mas->mas_row_cnt){
						if(!strcmp(ttn($temptt,"prea_enabled"),"Y") && ttn($gtt,"account_pakey")){
							if(ttn($temptt,"prea_reamount")>ttn($gtt,"fee_feature")){
								$err = pp_chainpayments("PAY","http://www.seedliving.ca/","http://www.seedliving.ca/".SEONAME."/setFeaturedSuccess/","CAD",ttn($gtt,"account_email"),ttn($gtt,"account_pakey"),"EACHRECEIVER",array(ADMIN_EMAIL),array("false"),array(ttn($gtt,"fee_feature")),"SeedLiving featured item");
								if(!$err){
									mas_qnr($mas,"UPDATE preas SET prea_reamount = (prea_reamount - %s) WHERE prea_accountid = '%s'",ttn($gtt,"fee_feature"),ttn($gtt,"account_id"));
									mas_qnr($mas,"UPDATE seeds SET seed_featured = 'Y' WHERE seed_id = '%s'",ttn($tt,"@id"));
									header("Location: /".SEONAME."/setFeaturedSuccess/");
								} else criterr($err);
							} else {
								tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Top"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
								tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"slNoPreApprovedFunds"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
								tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Bottom"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
							}
						} else {
							tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Top"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
							tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"slNoPreApprovedFunds"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
							tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Bottom"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
						}
					} else {
						mas_q1($mas,$temptt,"SELECT * FROM unlimited WHERE u_accountid = '%s' and u_amount = '20'",ttn($gtt,"account_id"));
						if($mas->mas_row_cnt){
							$amount = "0.50";
							pp_chainpayments("PAY","http://www.seedliving.ca/".SEONAME."/myseedsList/","http://www.seedliving.ca/".SEONAME."/slSaveFeatured-".ttn($tt,"@id")."/","CAD",ttn($gtt,"account_email"),"","SENDER",array(ADMIN_EMAIL),array("false"),array($amount),"SeedLiving featured item");
						} else {
							tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Top"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
							tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"slNoPreApprovedFunds"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
							tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Bottom"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
						}
					}
				}
			} else {
				tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Top"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
				tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"slOperationalError"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
				tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Bottom"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
			}
		}
		criterr(NULL);
	break;
	case "slSaveFeatured":
		header("Cache-Control: no-cache");
		if(ttn($tt,"@id") && ttn($gtt,"account_id")){
			mas_q1($mas,$temptt,"SELECT * FROM seeds WHERE seed_id = '%s' AND seed_featured = 'N'",ttn($tt,"@id"));
			if($mas->mas_row_cnt){
				mas_qnr($mas,"UPDATE seeds SET seed_featured = 'Y' WHERE seed_id = '%s'",ttn($tt,"@id"));
				slUpdateRequest($mas4,"seedfeatured","U",ttn($tt,"@id"));
				header("Location: /".SEONAME."/setFeaturedSuccess/");
			}
		}
		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Top"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"slOperationalError"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Bottom"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		criterr(NULL);
	break;
	case "secureCategories":
		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Top"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"secureCategories"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));

		mas_qb($mas,"SELECT * FROM cats ORDER BY cat_tsadd");
		if(!$mas->mas_row_cnt){
			mas_qb($mas,"SELECT cat_id as optionVal, cat_name as optionDisplay FROM cats ORDER BY cat_name");
			if(!$mas->mas_row_cnt) tmplt_proc_ex_tt(TEMPLROOT,tkntbl_search($tmpl,"secureOptionsNone"),OPENTAG,CLOSETAG,2,stdout,$tt,"catlist",array(&$tt,&$temptt));
			while(mas_qg($mas,$temptt)){
				tmplt_proc_ex_tt(TEMPLROOT,tkntbl_search($tmpl,"secureOptions"),OPENTAG,CLOSETAG,2,stdout,$tt,"catlist",array(&$tt,&$temptt));
			} mas_qe($mas);
			tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"secureCategoriesNone"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
			tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"categoryAddEdit"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		} else {
			tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"categoryTop"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
			while(mas_qg($mas,$temptt)){
				mas_q1($mas2,$temptt,"SELECT cat_name as cat_parentid FROM cats WHERE cat_id = '%s'",ttn($temptt,"cat_parentid"));
				tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"categoryRow"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt,&$temptt));
			} mas_qe($mas);
			tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"categoryBottom"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		}
		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Bottom"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));

		criterr(NULL);
	break;
	case "secureCategoryAdd":
		mas_qb($mas,"SELECT cat_id as optionVal, cat_name as optionDisplay FROM cats ORDER BY cat_name");
		if(!$mas->mas_row_cnt) tmplt_proc_ex_tt(TEMPLROOT,tkntbl_search($tmpl,"secureOptionsNone"),OPENTAG,CLOSETAG,2,stdout,$tt,"catlist",array(&$tt,&$temptt));
		while(mas_qg($mas,$temptt)){
			tmplt_proc_ex_tt(TEMPLROOT,tkntbl_search($tmpl,"secureOptions"),OPENTAG,CLOSETAG,2,stdout,$tt,"catlist",array(&$tt,&$temptt));
		} mas_qe($mas);
		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Top"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"categoryAdd"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"categoryAddEdit"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Bottom"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		criterr(NULL);
	break;
	case "secureCategoryEdit":
		mas_q1($mas,$temptt,"SELECT * FROM cats WHERE cat_id = '%s'",ttn($tt,"@id"));
		if(!strcmp(ttn($temptt,"cat_enabled"),"Y")) tkntbl_add($tt,"checkbox","checked",1);

		mas_qb($mas,"SELECT cat_id as optionVal, cat_name as optionDisplay FROM cats ORDER BY cat_name");
		if(!$mas->mas_row_cnt) tmplt_proc_ex_tt(TEMPLROOT,tkntbl_search($tmpl,"secureOptionsNone"),OPENTAG,CLOSETAG,2,stdout,$tt,"catlist",array(&$tt,&$temptt));
		while(mas_qg($mas,$dtt)){
			if(!strcmp(ttn($temptt,"cat_parentid"),ttn($dtt,"optionVal"))) tkntbl_add($dtt,"selected","selected",1);
			tmplt_proc_ex_tt(TEMPLROOT,tkntbl_search($tmpl,"secureOptions"),OPENTAG,CLOSETAG,2,stdout,$tt,"catlist",array(&$tt,&$dtt));
			tkntbl_ftable($dtt);
		} mas_qe($mas);

		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Top"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"categoryEdit"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"categoryAddEdit"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt,&$temptt));
		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Bottom"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
	break;
	case "secureCategorySave":
		header("Cache-Control: no-cache");
		if(!strcmp(ttn($tt,"cat_enabled"),"on")) tkntbl_add($tt,"cat_enabled","Y",1);
		else tkntbl_add($tt,"cat_enabled","N",1);;

		tkntbl_add($tt,"cat_userid","1",1);
		mas_lts($mas,$ftt,"cats");
		if(!ttn($tt,"@id")){
			tkntbl_add($tt,"cat_tsadd",time(),1);
			mas_gri($mas,$tt,$ftt,0,"cats");
			tkntbl_add($tt,"@id",mas_insert_id($mas),1);
			slUpdateRequest($mas4,"category","A",ttn($tt,"@id"));
		} else {
			tkntbl_add($tt,"cat_tsmod",time(),1);
			mas_gru($mas,$tt,$ftt,"cat_id",ttn($tt,"@id"),1,"cats");
			slUpdateRequest($mas4,"category","U",ttn($tt,"@id"));
		}

		header("Location: /".SEONAME."/secureCategories/");
		criterr(NULL);
	break;

	case "secureTags":
		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Top"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"secureTags"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));

		mas_qb($mas,"SELECT * FROM tags ORDER BY tag_tsadd");
		if(!$mas->mas_row_cnt){
			tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"secureTagsNone"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
			tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"tagAddEdit"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		} else {
			tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"tagTop"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
			while(mas_qg($mas,$temptt)){
				tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"tagRow"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt,&$temptt));
			} mas_qe($mas);
			tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"tagBottom"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		}
		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Bottom"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));

		criterr(NULL);
	break;
	case "secureTagAdd":
		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Top"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"tagAdd"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"tagAddEdit"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Bottom"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		criterr(NULL);
	break;
	case "slLinks":
		if(ttn($tt,"link_name") && ttn($tt,"link_url")){
			mas_qnr($mas,"INSERT INTO links VALUES ('','%s','%s','%s')",ttn($tt,"link_name"),ttn($tt,"link_url"),time());
			$fp = fopen(SEEDLIVING_ROOT_DIR."includes/links.html","a");
			fwrite($fp,"<a style=\"text-decoration:none;\" target=\"_new\" href=\"".ttn($tt,"link_url")."\"><span style=\"color:#949292;\">".ttn($tt,"link_name")."</span></a><br />");
			fclose($fp);
		}
		tkntbl_add($tt,"page",file_get_contents(SEEDLIVING_ROOT_DIR."includes/links.html"),1);
		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Top"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"slLinksHeader"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Bottom"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		criterr(NULL);
	break;
	case "secureTagEdit":
		mas_q1($mas,$temptt,"SELECT * FROM tags WHERE tag_id = '%s'",ttn($tt,"@id"));
		if(!strcmp(ttn($temptt,"tag_enabled"),"Y")) tkntbl_add($tt,"checkbox","checked",1);

		mas_qb($mas,"SELECT cat_id as optionVal, cat_name as optionDisplay FROM cats ORDER BY cat_name");

		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Top"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"tagEdit"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"tagAddEdit"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt,&$temptt));
		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Bottom"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
	break;
	case "secureTagSave":
		header("Cache-Control: no-cache");
		if(!strcmp(ttn($tt,"tag_enabled"),"on")) tkntbl_add($tt,"tag_enabled","Y",1);
		else tkntbl_add($tt,"tag_enabled","N",1);;

		tkntbl_add($tt,"tag_userid","1",1);
		mas_lts($mas,$ftt,"tags");
		if(!ttn($tt,"@id")){
			tkntbl_add($tt,"tag_tsadd",time(),1);
			mas_gri($mas,$tt,$ftt,0,"tags");
			tkntbl_add($tt,"@id",mas_insert_id($mas),1);
			slUpdateRequest($mas4,"tags","A",ttn($tt,"@id"));
		} else {
			tkntbl_add($tt,"tag_tsmod",time(),1);
			mas_gru($mas,$tt,$ftt,"tag_id",ttn($tt,"@id"),1,"tags");
			slUpdateRequest($mas4,"tags","U",ttn($tt,"@id"));
		}

		header("Location: /".SEONAME."/secureTags/");
		criterr(NULL);
	break;

	case "secureNews":
		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Top"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"secureNews"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));

		mas_qb($mas,"SELECT * FROM news ORDER BY new_tsadd");
		if(!$mas->mas_row_cnt){
			mas_qb($mas2,"SELECT * FROM zones order by z_id");
			while(mas_qg($mas2,$temptt)){
				tkntbl_snprintf($tt,"slZones",2,MAX_RESULTS,"<option value='%s'>%s</option>",ttn($temptt,"z_id"),ttn($temptt,"z_zone"));
			} mas_qe($mas2);
			tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"newAddEdit"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		} else {
			tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"newTop"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
			while(mas_qg($mas,$temptt)){
				tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"newRow"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt,&$temptt));
			} mas_qe($mas);
			tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"newBottom"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		}
		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Bottom"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));

		criterr(NULL);
	break;

	case "secureEvents":
		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Top"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"secureEvents"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));

		mas_qb($mas,"SELECT * FROM events ORDER BY event_tsadd");
		if(!$mas->mas_row_cnt){
			tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"eventAddEdit"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		} else {
			tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"eventTop"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
			while(mas_qg($mas,$temptt)){
				tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"eventRow"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt,&$temptt));
			} mas_qe($mas);
			tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"eventBottom"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		}
		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Bottom"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));

		criterr(NULL);
	break;

	case "secureNewAdd":
		mas_qb($mas,"SELECT * FROM zones order by z_id");
			while(mas_qg($mas,$temptt)){
				tkntbl_snprintf($tt,"slZones",2,MAX_RESULTS,"<option value='%s'>%s</option>",ttn($temptt,"z_id"),ttn($temptt,"z_zone"));
				tkntbl_snprintf($tt,"slZones2",2,MAX_RESULTS,"<option value='%s'>%s</option>",ttn($temptt,"z_id"),ttn($temptt,"z_zone"));
			} mas_qe($mas);
		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Top"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"newAdd"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"newAddEdit"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Bottom"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		criterr(NULL);
	break;
	case "stripSlashes":
		mas_qb($mas,"SELECT * FROM news");
		while(mas_qg($mas,$temptt)){
			mas_qnr($mas2,"UPDATE news set new_desc = '%s' where new_id = '%s'",addslashes(stripslashes(ttn($temptt,"new_desc"))),ttn($temptt,"new_id"));
		} mas_qe($mas);
	break;
	case "newSave":
		header("Cache-Control: no-cache");
		if(ttn($gtt,"account_id")){
			if(!ttn($tt,"new_title") && !ttn($tt,"new_desc")) slNoOperation($tmpl,$gtt);

			if(!strcmp(ttn($gtt,"slAdmin"),"1")) tkntbl_add($tt,"new_enabled","Y",1);
			else tkntbl_add($tt,"new_enabled","N",1);

			tkntbl_snprintf($tt,"new_desc",1,MAX_RESULTS,"%s",addslashes(stripslashes(ttn($tt,"new_desc"))));


			mas_lts($mas,$ftt,"news");

			if(!ttn($tt,"@id")){
				tkntbl_add($tt,"new_tsadd",time(),1);
				tkntbl_add($tt,"new_postedby",ttn($gtt,"account_id"),1);
				tkntbl_add($tt,"new_expired",(time()+7776000),1);
				mas_gri($mas,$tt,$ftt,0,"news");
				tkntbl_add($tt,"@id",mas_insert_id($mas),1);
				slUpdateRequest($mas4,"news","A",ttn($tt,"@id"));
			} else {
				mas_q1($mas,$tt,"SELECT new_postedby, new_expires FROM news WHERE new_id = '%s'",ttn($tt,"@id"));
				tkntbl_rmv($ftt,"new_tsadd");
				tkntbl_add($tt,"new_tsmod",time(),1);
				mas_gru($mas,$tt,$ftt,"new_id",ttn($tt,"@id"),0,"news");
				slUpdateRequest($mas4,"news","U",ttn($tt,"@id"));
			}

			/* Handle Images */
			$c=1;
			while(list($key,$value) = each($_FILES['new_image']['name']))
			{
				if(!empty($value)){   // this will check if any blank field is entered
					$filename = $value;    // filename stores the value

					$filename=str_replace(" ","_",$filename);// Add _ inplace of blank space in file name, you can remove this line

					$add = SEEDLIVING_ROOT_DIR."i/news/".ttn($tt,"@id")."_".$c.".jpg";   // upload directory path is set

					copy($_FILES['new_image']['tmp_name'][$key], $add);     //  upload the file to the server
					chmod("$add",0777);                 // set permission to the file.
					$c++;
					smart_resize_image($add,200,0,true,$add,false,false);
				}
			}

			//header("Location: /".SEONAME."/secureUser/");
		if(!strcmp(ttn($gtt,"slAdmin"),"1")){
			tkntbl_ftable($ftt);
			tkntbl_add($ftt,"slNewsPreview","1",1);
			tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Top"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
			mas_q1($mas,$temptt,"SELECT * FROM news WHERE new_id = '%s'",ttn($tt,"@id"));
			mas_q1($mas2,$ftt,"SELECT * FROM accounts,users WHERE user_id = account_userid AND account_id= '%s'",ttn($temptt,"new_postedby"));
			tmplt_proc_ex_tt(TEMPLROOT,tkntbl_search($tmpl,"newsList"),OPENTAG,CLOSETAG,2,stdout,$tt,"newsList",array(&$tt,&$temptt,&$ftt));
			tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"newsSaveAdmin"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
			tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Bottom"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		} else {
			mas_q1($mas,$tt,"SELECT * FROM news WHERE new_id = '%s'",ttn($tt,"@id"));
			//tkntbl_add($tt,"email_to","".SITEEMAIL."",1);

			mas_q1($mas,$gtt,"SELECT * FROM users where user_id = '%s'",ttn($gtt,"account_userid"));
			tmplt_proc_ex_tt(TEMPLROOT,tkntbl_search($tmpl,"newsApproveEmail"),OPENTAG,CLOSETAG,1,stdout,$tt,"message",array(&$tt,&$gtt));
			$headers  = "MIME-Version: 1.0\n";
			$headers .= "Content-type: text/html; charset=iso-8859-1\n";
			//$headers .= "To: colin@anlanda.com\n";
			$headers .= "To: ".SITEEMAIL."\n";
			$headers .= "From: ".ttn($gtt,"user_fname")." ".ttn($gtt,"user_lname")."<".ttn($gtt,"account_email").">\n";
			$subject = "News Notification: ".ttn($gtt,"user_fname")." ".ttn($gtt,"user_lname")." has added a news item for your approval.";
			mail(NULL,$subject,ttn($tt,"message"),$headers);

			tkntbl_ftable($ftt);
			tkntbl_add($ftt,"slNewsPreview","1",1);
			mas_q1($mas2,$ftt,"SELECT * FROM accounts,users WHERE user_id = account_userid AND account_id= '%s'",ttn($tt,"new_postedby"));
			tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Top"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
			tmplt_proc_ex_tt(TEMPLROOT,tkntbl_search($tmpl,"newsList"),OPENTAG,CLOSETAG,2,stdout,$tt,"newsList",array(&$tt,&$temptt,&$ftt));
			tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"newsSaveUser"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
			tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Bottom"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));


		}
	} else slLogin($tmpl,$gtt);
		criterr(NULL);
	break;
	case "newAdminApprove":
	    tkntbl_add($tt,"newsAdmin","1",1);
		if(ttn($gtt,"account_id")){
			if(!strcmp(ttn($gtt,"slAdmin"),"1")){
				mas_q1($mas,$temptt,"SELECT * FROM news WHERE new_id = '%s' AND new_enabled = 'N'",ttn($tt,"@id"));
				tkntbl_snprintf($temptt,"new_desc",1,MAX_RESULTS,"%s",html_entity_decode(stripslashes(ttn($temptt,"new_desc"))));
				if($mas->mas_row_cnt){
					mas_qb($mas,"SELECT * FROM zones order by z_id");
					while(mas_qg($mas,$temptt)){
						if(!strcmp(ttn($temptt,"new_zone"),ttn($temptt,"z_id"))) tkntbl_snprintf($tt,"slZones",2,MAX_RESULTS,"<option selected value='%s'>%s</option>",ttn($temptt,"z_id"),ttn($temptt,"z_zone"));
						else tkntbl_snprintf($tt,"slZones",2,MAX_RESULTS,"<option value='%s'>%s</option>",ttn($temptt,"z_id"),ttn($temptt,"z_zone"));
					} mas_qe($mas);
					tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Top"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
					tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"newAdminHeader"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
					tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"newAddEdit"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt,&$temptt));
					tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Bottom"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
					criterr(NULL);
				} else slNoOperation($tmpl,$gtt);
			}
		}
		slNoOperation($tmpl,$gtt);
		criterr(NULL);
	break;
	case "newApproveSave":
		/* UPDATE - use mieka is */
		if(ttn($gtt,"account_id")){
	    	switch(ttn($tt,"newsAction")){
				case "a":
					mas_q1($mas,$temptt,"SELECT * FROM news WHERE new_id = '%s' AND new_enabled = 'N'",ttn($tt,"@id"));
					if($mas->mas_row_cnt){
						mas_qnr($mas,"UPDATE news SET new_enabled = 'Y', new_name = '%s', new_desc = '%s', new_location = '%s', new_city = '%s',new_province = '%s' WHERE new_id = '%s'",addslashes(ttn($tt,"new_name")),strip_tags(addslashes(ttn($tt,"new_desc")),"<a><i><b><p><h1><h2><h3><strong>"),ttn($tt,"new_location"),ttn($tt,"new_city"),ttn($tt,"new_province"),ttn($tt,"@id"));
						mas_q1($mas,$gtt,"SELECT * FROM news WHERE new_id = '%s'",ttn($tt,"@id"));
						mas_q1($mas,$ftt,"SELECT * FROM users,accounts WHERE account_userid = user_id AND account_id = '%s'",ttn($gtt,"new_postedby"));
						tmplt_proc_ex_tt(TEMPLROOT,tkntbl_search($tmpl,"newsApprovedEmail"),OPENTAG,CLOSETAG,1,stdout,$tt,"message",array(&$tt,&$ftt,&$gtt));
						$headers  = "MIME-Version: 1.0\n";
						$headers .= "Content-type: text/html; charset=iso-8859-1\n";
						$headers .= "To: ".ttn($gtt,"user_fname")." ".ttn($gtt,"user_lname")."<".ttn($gtt,"account_email").">\n";
						$headers .= "From: SeedLiving <".SITEEMAIL.">\n";
						$subject = "SeedLiving - Your news item has been approved";
						mail(NULL,$subject,ttn($tt,"message"),$headers);
						tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Top"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
						tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"newAprrovedThanks"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
						tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Bottom"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
					} else slNoOperation($tmpl,$gtt);
				break;

				case "r":
					mas_q1($mas,$temptt,"SELECT * FROM news WHERE new_id = '%s' AND new_enabled = 'N'",ttn($tt,"@id"));
					if($mas->mas_row_cnt){
						mas_q1($mas,$gtt,"SELECT * FROM news where new_id = '%s'",ttn($tt,"@id"));
						mas_q1($mas,$ftt,"SELECT * FROM users,accounts WHERE account_userid = user_id AND account_id = '%s'",ttn($gtt,"new_postedby"));
						mas_qnr($mas,"DELETE FROM news WHERE new_id = '%s'",ttn($tt,"@id"));
						tmplt_proc_ex_tt(TEMPLROOT,tkntbl_search($tmpl,"newsRejectedEmail"),OPENTAG,CLOSETAG,1,stdout,$tt,"message",array(&$tt,&$gtt,&$ftt));
						$headers  = "MIME-Version: 1.0\n";
						$headers .= "Content-type: text/html; charset=iso-8859-1\n";
						$headers .= "To: ".ttn($gtt,"user_fname")." ".ttn($gtt,"user_lname")."<".ttn($gtt,"account_email").">\n";
						$headers .= "From: SeedLiving <".SITEEMAIL.">\n";
						$subject = "SeedLiving - Your news item has been rejected";
						mail(NULL,$subject,ttn($tt,"message"),$headers);
						tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Top"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
						tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"newRejectedThanks"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
						tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Bottom"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));

					}
				break;

			}
		}
		criterr(NULL);
	break;
	case "secureNewEdit":
		mas_q1($mas,$temptt,"SELECT * FROM news WHERE new_id = '%s'",ttn($tt,"@id"));
		mas_qb($mas,"SELECT * FROM zones order by z_id");
					while(mas_qg($mas,$temptt)){
						if(!strcmp(ttn($temptt,"new_zone"),ttn($temptt,"z_id"))) tkntbl_snprintf($tt,"slZones",2,MAX_RESULTS,"<option selected value='%s'>%s</option>",ttn($temptt,"z_id"),ttn($temptt,"z_zone"));
						else tkntbl_snprintf($tt,"slZones",2,MAX_RESULTS,"<option value='%s'>%s</option>",ttn($temptt,"z_id"),ttn($temptt,"z_zone"));

						if(!strcmp(ttn($temptt,"new_zone2"),ttn($temptt,"z_id"))) tkntbl_snprintf($tt,"slZones2",2,MAX_RESULTS,"<option selected value='%s'>%s</option>",ttn($temptt,"z_id"),ttn($temptt,"z_zone"));
						else tkntbl_snprintf($tt,"slZones2",2,MAX_RESULTS,"<option value='%s'>%s</option>",ttn($temptt,"z_id"),ttn($temptt,"z_zone"));

					} mas_qe($mas);
		tkntbl_add($temptt,"new_desc",stripslashes(ttn($temptt,"new_desc")),1);
		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Top"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"newEdit"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"newAddEdit"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt,&$temptt));
		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Bottom"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
	break;

	case "secureSeedSave":
		header("Cache-Control: no-cache");
		tkntbl_rmv($tt,"overlord");
		tkntbl_rmv($tt,"seed_image");
		mas_lts($mas,$ftt,"seeds");

		tkntbl_add($tt,"addswap","0",1);

		if(ttn($tt,"seed_tradeopt")){
			if(!strcmp(ttn($tt,"seed_tradeopt"),"N")){
				tkntbl_add($tt,"seed_trade","N",1);
				tkntbl_add($tt,"seed_tradetable","N",1);
			}
			if(!strcmp(ttn($tt,"seed_tradeopt"),"Y")){
				tkntbl_add($tt,"seed_trade","Y",1);
				tkntbl_add($tt,"seed_tradetable","N",1);
				tkntbl_add($tt,"addswap","1",1);
			}
			if(!strcmp(ttn($tt,"seed_tradeopt"),"T")){
				tkntbl_add($tt,"seed_trade","N",1);
				tkntbl_add($tt,"seed_tradetable","Y",1);
			}
			if(!strcmp(ttn($tt,"seed_tradeopt"),"S")){
				tkntbl_add($tt,"seed_trade","S",1);
				tkntbl_add($tt,"seed_tradetable","N",1);
				tkntbl_add($tt,"addswap","1",1);
			}

			tkntbl_rmv($tt,"seed_tradeopt");
		} else {
			tkntbl_add($tt,"seed_trade","N",1);
			tkntbl_add($tt,"seed_tradetable","N",1);
		}

		$seedid = ttn($tt,"seed_id");
		if( !$seedid ){
			tkntbl_rmv($tt,"@id");
			tkntbl_add($tt,"seed_tsadd",time(),1);
			if(!strcmp(ttn($gtt,"account_unl4"),"Y")){
				tkntbl_add($tt,"seed_featured","Y",1);
			} else {
				tkntbl_add($tt,"seed_featured","N",1);
			}
			mas_gri($mas,$tt,$ftt,1,"seeds");
			$seedid = mas_insert_id($mas);
			tkntbl_add($tt,"seed_id",$seedid,1);
			slUpdateRequest($mas4,"seeds","A",ttn($tt,"@id"));
		} else {
			mas_q1($mas,$dtt,"SELECT * FROM seeds WHERE seed_id = '%s'",$seedid);
			tkntbl_add($tt,"seed_tsadd",ttn($dtt,"seed_tsadd"),1);
			tkntbl_add($tt,"seed_tsmod",time(),1);
			mas_gru($mas,$tt,$ftt,"seed_id",$seedid,1,"seeds");
			slUpdateRequest($mas4,"seeds","U",$seedid);
			tkntbl_ftable($dtt);
		}
		$c=1;
		while(list($key,$value) = @each($_FILES['seed_image']['name']))
		{
			if(!empty($value)){   // this will check if any blank field is entered
				$filename = $value;    // filename stores the value

				$filename=str_replace(" ","_",$filename);// Add _ inplace of blank space in file name, you can remove this line

				$add = IMAGEROOT."seeds/".$seedid."_".$c.".jpg";   // upload directory path is set

				copy($_FILES['seed_image']['tmp_name'][$key], $add);     //  upload the file to the server
				chmod("$add",0777);                 // set permission to the file.

				if($c==1){
					$image = new SimpleImage();
					$image->load($add);
					$image->resizeToWidth(200);
					$image->save(SEEDLIVING_ROOT_DIR."i/seeds/thmb/".$seedid."_".$c.".jpg");
				}
			}

			$c++;
		}

		/* Save Tags */
		mas_qnr($mas,"DELETE FROM tagrel WHERE tagrel_seedid = '%s'",$seedid);
		if(ttn($tt,"addswap")){
			if(!strstr(ttn($tt,"seed_tagdesc"),"swap")){
				if(ttn($tt,"seed_tagdesc")) ttn($tt,"seed_tagdesc").";swap";
				else tkntbl_add($tt,"seed_tagdesc","swap",1);
			}
		}
		$temp_v = str_replace(",",";",ttn($tt,"seed_tagdesc"));
     	$temp_a = explode(";",$temp_v);

		for($c=0; $c<count($temp_a); $c++){
			if($temp_a){
				mas_q1($mas,$dtt,"SELECT * FROM tags WHERE tag_name = '%s'",$temp_a[$c]);
				if(!$mas->mas_row_cnt){
					mas_qnr($mas,"INSERT INTO tags VALUES ('','%s','','%s','%s','Y','%s','')",trim($temp_a[$c]),str_replace(" ","",strtolower($temp_a[$c])),ttn($gtt,"user_id"),time());
					tkntbl_add($dtt,"tag_id",mas_insert_id($mas),1);
					slUpdateRequest($mas4,"tags","A",ttn($dtt,"tag_id"));
				}
				mas_qnr($mas,"INSERT INTO tagrel VALUES ('','%s','%s')",ttn($dtt,"tag_id"),$seedid);
			}
		}
		$dev_null = system("php -q ".SEEDLIVING_ROOT_DIR."bin/tags.php");
		header("Location: /".SEONAME."/myseedsList/");
		criterr(NULL);
	break;

	case "slCancelCredits":
		if(ttn($gtt,"account_id")){
			if(ttn($tt,"@id")){
				if(!strcmp(ttn($tt,"@id"),ttn($gtt,"account_id"))){
					mas_q1($mas,$temptt,"SELECT * FROM pres WHERE pre_enabled = 'N' AND pre_accountid = '%s' AND pre_tsmod = '0'",ttn($tt,"@id"));
					if($mas->mas_row_cnt){
						mas_qnr($mas,"DELETE FROM pres WHERE pre_enabled = 'N' AND pre_accountid = '%s' AND pre_tsmod = '0'",ttn($tt,"@id"));
						tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Top"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
						tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"slGetCreditsCancel"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
						tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Bottom"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
						criterr(NULL);
					} else {
						mas_q1($mas,$temptt,"SELECT * FROM preas WHERE prea_enabled = 'N' AND prea_accountid = '%s' AND prea_tsmod = '0'",ttn($tt,"@id"));
						if($mas->mas_row_cnt){
							mas_qnr($mas,"DELETE FROM preas WHERE prea_enabled = 'N' AND prea_accountid = '%s' AND prea_tsmod = '0'",ttn($tt,"@id"));
							tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Top"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
							tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"slGetCreditsCancel"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
							tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Bottom"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
							criterr(NULL);
						} else {
							mas_q1($mas,$temptt,"SELECT * FROM unlimited WHERE u_enabled = 'N' AND u_accountid = '%s' AND u_tsmod = '0'",ttn($tt,"@id"));
							if($mas->mas_row_cnt){
								mas_qnr($mas,"DELETE FROM unlimited WHERE u_enabled = 'N' AND u_accountid = '%s' AND u_tsmod = '0'",ttn($tt,"@id"));
								tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Top"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
								tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"slGetCreditsCancel"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
								tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Bottom"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
								criterr(NULL);

							}
						}
					}
				}
			}
		}
		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Top"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
			tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"slOperationalError"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
			tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Bottom"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		criterr(NULL);
	break;

	case "slSaveCredits":
		if(ttn($gtt,"account_id")){
			if(ttn($tt,"@id")){
				if(!strcmp(ttn($tt,"@id"),ttn($gtt,"account_id"))){

					mas_q1($mas,$temptt,"SELECT * FROM pres WHERE pre_enabled = 'N' AND pre_accountid = '%s' AND pre_tsmod = '0'",ttn($tt,"@id"));
					if($mas->mas_row_cnt){

						mas_qnr($mas,"UPDATE pres SET pre_enabled = 'Y', pre_tsmod = '%s' WHERE pre_enabled = 'N' AND pre_accountid = '%s' AND pre_tsmod = '0'",time(),ttn($tt,"@id"));
						mas_qnr($mas,"UPDATE accounts SET account_accesslevel = 'S', account_prepaid = 'Y', account_preapproval = 'N', account_pakey = '' WHERE account_id = '%s'",ttn($tt,"@id"));

						tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Top"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
						tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"slGetCreditsSuccess"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
						tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Bottom"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
						criterr(NULL);
					} else {
						mas_q1($mas,$temptt,"SELECT * FROM preas WHERE prea_enabled = 'N' AND prea_accountid = '%s' AND prea_tsmod = '0'",ttn($tt,"@id"));
						if($mas->mas_row_cnt){
							mas_qnr($mas,"UPDATE preas SET prea_enabled = 'Y', prea_tsmod = '%s' WHERE prea_enabled = 'N' AND prea_accountid = '%s' AND prea_tsmod = '0'",time(),ttn($tt,"@id"));
						    mas_qnr($mas,"UPDATE accounts SET account_accesslevel = 'S', account_prepaid = 'N', account_preapproval = 'Y' WHERE account_id = '%s'",ttn($tt,"@id"));
							tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Top"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
							tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"slGetCreditsSuccess"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
							tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Bottom"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
						   criterr(NULL);
						} else {
							mas_q1($mas,$temptt,"SELECT * FROM unlimited WHERE u_enabled = 'N' AND u_accountid = '%s' AND u_tsmod = '0'",ttn($tt,"@id"));
							if($mas->mas_row_cnt){
								mas_qnr($mas,"UPDATE unlimited SET u_enabled = 'Y', u_tsmod = '%s' WHERE u_enabled = 'N' AND u_accountid = '%s' AND u_tsmod = '0'",time(),ttn($tt,"@id"));
						    	if(!strcmp(ttn($temptt,"u_amount"),"40")){
									mas_qnr($mas,"UPDATE accounts SET account_accesslevel = 'S', account_unl4 = 'Y', account_preapproval = 'N', account_prepaid = 'N',account_unl2 = 'N'  WHERE account_id = '%s'",ttn($tt,"@id"));
								} else if(!strcmp(ttn($temptt,"u_amount"),"20")) {
									mas_qnr($mas,"UPDATE accounts SET account_accesslevel = 'S', account_unl2 = 'Y', account_preapproval = 'N', account_prepaid = 'N', account_unl4 = 'N'  WHERE account_id = '%s'",ttn($tt,"@id"));
								}
								tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Top"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
								tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"slGetCreditsSuccess"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
								tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Bottom"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
						   		criterr(NULL);

							}
						}
					}
				}
			}
		}
			tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Top"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
			tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"slOperationalError"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
			tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Bottom"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));

		criterr(NULL);
	break;

	case "slDoCredits":
		if(ttn($gtt,"account_id")){
			if(!strcmp(ttn($tt,"slSellerFees"),"1")){
				if(ttn($tt,"slSellerFees_pp")){
					$amount = (ttn($tt,"slSellerFees_pp")*10);
					mas_qnr($mas,"INSERT INTO pres VALUES('','%s','%s','%s','N','%s','')",ttn($gtt,"account_id"),$amount,$amount,time());
					slUpdateRequest($mas4,"pres","A",mas_insert_id($mas));
					pp_chainpayments("PAY","http://www.seedliving.ca/".SEONAME."/slCancelCredits-".ttn($gtt,"account_id")."/","http://www.seedliving.ca/".SEONAME."/slSaveCredits-".ttn($gtt,"account_id")."/","CAD",ttn($gtt,"account_email"),"","SENDER",array(ADMIN_EMAIL),array("false"),array($amount),"SeedLiving credits");
					if($err){
						criterr($err);
					}
					criterr(NULL);
				} else criterr("No amount");
			} elseif(!strcmp(ttn($tt,"slSellerFees"),"2")){
				if(ttn($tt,"slSellerFees_pa")){
					mas_qnr($mas,"INSERT INTO preas VALUES('','%s','%s','%s','N','%s','%s','')",ttn($gtt,"account_id"),ttn($tt,"slSellerFees_pa"),ttn($tt,"slSellerFees_pa"),(time()+2628000),time());
					slUpdateRequest($mas4,"preas","A",mas_insert_id($mas));
					pp_preapproval(ttn($gtt,"account_id"),$mas,"http://www.seedliving.ca/".SEONAME."/slSaveCredits-".ttn($gtt,"account_id")."/","http://www.seedliving.ca/".SEONAME."/slCancelCredits-".ttn($gtt,"account_id")."/","CAD",date("Y-m-d",time())."T".date("H:i:s",time()),date("Y-m-d",(time()+2628000))."T".date("H:i:s",(time()+2628000)),ttn($tt,"slSellerFees_pa"),ttn($gtt,"account_email"),"1000","NO_PERIOD_SPECIFIED", "", "",ttn($tt,"slSellerFees_pa"),"","");
				}
			} elseif(!strcmp(ttn($tt,"slSellerFees"),"3")){
				$amount="20";
				mas_qnr($mas,"INSERT INTO unlimited VALUES('','%s','%s','20','N','%s','')",ttn($gtt,"account_id"),(time()+31536000),time());
				slUpdateRequest($mas4,"unlimited","A",mas_insert_id($mas));
				pp_chainpayments("PAY","http://www.seedliving.ca/".SEONAME."/slCancelCredits-".ttn($gtt,"account_id")."/","http://www.seedliving.ca/".SEONAME."/slSaveCredits-".ttn($gtt,"account_id")."/","CAD",ttn($gtt,"account_email"),"","SENDER",array(ADMIN_EMAIL),array("false"),array($amount),"SeedLiving credits");
			} elseif(!strcmp(ttn($tt,"slSellerFees"),"4")){
				$amount="40";
				mas_qnr($mas,"INSERT INTO unlimited VALUES('','%s','%s','40','N','%s','')",ttn($gtt,"account_id"),(time()+31536000),time());
				slUpdateRequest($mas4,"unlimited","A",mas_insert_id($mas));
				pp_chainpayments("PAY","http://www.seedliving.ca/".SEONAME."/slCancelCredits-".ttn($gtt,"account_id")."/","http://www.seedliving.ca/".SEONAME."/slSaveCredits-".ttn($gtt,"account_id")."/","CAD",ttn($gtt,"account_email"),"","SENDER",array(ADMIN_EMAIL),array("false"),array($amount),"SeedLiving credits");

			}
		}
		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Top"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"slOperationalError"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Bottom"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));

		criterr(NULL);
	break;

	case "slDoDonationDone":
		header("Cache-Control: no-cache");
		if(ttn($gtt,"account_id")){
			if(ttn($tt,"@id")){
				mas_q1($mas,$temptt,"SELECT * FROM donation WHERE d_accountid = '%s' AND d_tsadd = '%s' AND d_enabled = 'N'",ttn($gtt,"account_id"),ttn($tt,"@id"));
				if($mas->mas_row_cnt){
					mas_qnr($mas,"UPDATE donation SET d_enabled = 'Y' WHERE d_tsadd = '%s'",ttn($tt,"@id"));
					tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Top"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
					tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"slDonateThankyou"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
					tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Bottom"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));

					/* Notify Meika */
					tmplt_proc_ex_tt(TEMPLROOT,tkntbl_search($tmpl,"slDoDonationDoneEmail"),OPENTAG,CLOSETAG,1,stdout,$tt,"message",array(&$tt,&$gtt,&$temptt));
					$headers  = "MIME-Version: 1.0\n";
					$headers .= "Content-type: text/html; charset=iso-8859-1\n";
					$headers .= "To: ".SITEEMAIL."\n";
					$headers .= "From: ".ttn($gtt,"account_email")."\n";
					$subject = "Notification: ".ttn($gtt,"account_username")." has just donated $".ttn($temptt,"d_amount")." to SeedLiving";
					mail(NULL,$subject,ttn($tt,"message"),$headers);
				}
			}
		}
		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Top"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"slOperationalError"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Bottom"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		criterr(NULL);
	break;

	case "slDoPaymentDonate":
		header("Cache-Control: no-cache");
		if(ttn($gtt,"account_id")){
			if(ttn($tt,"total")){
				$ts = time();
				mas_qnr($mas,"INSERT INTO donation VALUES('','%s','%s','N','%s')",ttn($tt,"total"),ttn($gtt,"account_id"),$ts);
				slUpdateRequest($mas4,"donation","A",mas_insert_id($mas));
				$err = pp_chainpayments("PAY","http://www.seedliving.ca/".SEONAME."/secureUser/","http://www.seedliving.ca/".SEONAME."/slDoDonationDone-".$ts."/","CAD",ttn($gtt,"account_email"),"","SENDER",array(ADMIN_EMAIL),array("false"),array(ttn($tt,"total")),"SeedLiving donation");
				if($err){
					slGenericError($tmpl,$gtt,$err);
					slUpdateRequest($mas4,"donatepaypal","E",ttn($gtt,"account_id"));
				}
			} else {
				slUpdateRequest($mas4,"donatetotals","E",ttn($gtt,"account_id"));
				slGenericError($tmpl,$gtt,"You have not entered a total");
			}
		} else {
			slUpdateRequest($mas4,"donateloggedin","E",ttn($gtt,"account_id"));
			slGenericError($tmpl,$gtt,"Please Sign In to Complete this Transaction");
		}

		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Top"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"slOperationalError"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Bottom"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));

		criterr(NULL);
	break;


	case "slDonate":
		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Top"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"slDonateCheckout"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Bottom"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));

		criterr(NULL);
	break;

	case "slGetCredits":
		if(ttn($gtt,"account_id")){
			tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Top"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
			tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"slGetCredits"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
			tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Bottom"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		}
		criterr(NULL);
	break;

	case "saveAccount":
	    header("Cache-Control: no-cache");
		if(!ttn($tt,"@id")) if(!ttn($tt,"captcha_code")) criterr(NULL);

		if(ttn($tt,"@id")){
			mas_q1($mas,$temptt,"SELECT * FROM accounts WHERE account_id = '%s'",ttn($tt,"@id"));
			if(!strcmp(ttn($temptt,"account_prepaid"),"P")){
				mas_q1($mas,$temptt,"SELECT * FROM pres WHERE pre_enabled = 'N' AND pre_accountid = '%s' order by pre_tsadd desc",ttn($tt,"@id"));
				if($mas->mas_row_cnt){
					mas_qnr($mas,"UPDATE pres SET pre_enabled = 'Y' WHERE pre_accountid = '%s'",ttn($tt,"@id"));
					mas_qnr($mas,"UPDATE accounts SET account_prepaid = 'Y' WHERE account_id = '%s'",ttn($tt,"@id"));
					header("Location: /".SEONAME."/login/");
				} else criterr("Error 3");
			} elseif(!strcmp(ttn($temptt,"account_preapproval"),"P")){
				if(ttn($temptt,"account_pakey")){
					mas_q1($mas,$temptt,"SELECT * FROM preas WHERE prea_enabled = 'N' AND prea_accountid = '%s' order by prea_tsadd desc",ttn($tt,"@id"));
					if($mas->mas_row_cnt){
						mas_qnr($mas,"UPDATE preas SET prea_enabled = 'Y', prea_expired = '%s' WHERE prea_accountid = '%s'",(time()+2628000),ttn($tt,"@id"));
						mas_qnr($mas,"UPDATE accounts SET account_preapproval = 'Y' WHERE account_id = '%s'",ttn($tt,"@id"));
						header("Location: /".SEONAME."/login/");
					} else criterr("Error 6");
				} else criterr("Error 5");
			} elseif(!strcmp(ttn($temptt,"account_unl2"),"P")){
				mas_q1($mas,$temptt,"SELECT * FROM unlimited WHERE u_enabled = 'N' AND u_accountid = '%s' AND u_amount='20' order by u_tsadd desc",ttn($tt,"@id"));
				if($mas->mas_row_cnt){
					mas_qnr($mas,"UPDATE unlimited SET u_enabled = 'Y', u_tsmod = '%s'  WHERE u_accountid = '%s'",time(),ttn($tt,"@id"));
					mas_qnr($mas,"UPDATE accounts SET account_unl2 = 'Y' WHERE account_id = '%s'",ttn($tt,"@id"));
					header("Location: /".SEONAME."/login/");
				} else criterr("Error 8");
			} elseif(!strcmp(ttn($temptt,"account_unl4"),"P")){
				mas_q1($mas,$temptt,"SELECT * FROM unlimited WHERE u_enabled = 'N' AND u_accountid = '%s' AND u_amount='40' order by u_tsadd desc",ttn($tt,"@id"));
				if($mas->mas_row_cnt){
					mas_qnr($mas,"UPDATE unlimited SET u_enabled = 'Y', u_tsmod = '%s'  WHERE u_accountid = '%s'",time(),ttn($tt,"@id"));
					mas_qnr($mas,"UPDATE accounts SET account_unl4 = 'Y' WHERE account_id = '%s'",ttn($tt,"@id"));
					header("Location: /".SEONAME."/login/");
				} else criterr("Error 10");
			} else criterr("Error 4");
			criterr(NULL);
		}

		if(ttn($tt,"account_username") && ttn($tt,"account_email")){

		if(!strcmp(ttn($tt,"seller"),"no")){
			tkntbl_rmv($tt,"slSellerFees");
		}

		tkntbl_add($tt,"account_feestatus","Y",1);

		if(!strcmp(ttn($gtt,"fee_enabled"),"N")){
			tkntbl_add($tt,"slSellerFees","4",1);
			tkntbl_add($tt,"account_feestatus","N",1);
		}



		mas_q1($mas,$temptt,"SELECT * FROM accounts WHERE account_username = '%s' AND account_email = '%s'",ttn($tt,"account_username"),ttn($tt,"account_email"));
		if(!$mas->mas_row_cnt){
			tkntbl_add($gtt,"seller",ttn($tt,"seller"),1);
			tkntbl_add($gtt,"slSellerFees_pp",ttn($tt,"slSellerFees_pp"),1);
			tkntbl_add($gtt,"slSellerFees_pa",ttn($tt,"slSellerFees_pa"),1);

			if(!strcmp(ttn($tt,"slSellerFees"),"1")) {tkntbl_add($tt,"account_prepaid","P",1); tkntbl_add($tt,"account_accesslevel","S",1);}
			else if(!strcmp(ttn($tt,"slSellerFees"),"2")) {tkntbl_add($tt,"account_preapproval","P",1); tkntbl_add($tt,"account_accesslevel","S",1);}
			else if(!strcmp(ttn($tt,"slSellerFees"),"3")) {tkntbl_add($tt,"account_unl2","P",1); tkntbl_add($tt,"account_accesslevel","S",1);}
			else if(!strcmp(ttn($tt,"slSellerFees"),"4")) {tkntbl_add($tt,"account_unl4","P",1); tkntbl_add($tt,"account_accesslevel","S",1);}
			else {
				tkntbl_add($tt,"account_prepaid","N",1);
				tkntbl_add($tt,"account_preapproval","N",1);
				tkntbl_add($tt,"account_accesslevel","B",1);
			}

			if(ttn($tt,"pid")) tkntbl_add($gtt,"pid",ttn($tt,"pid"),1);
			foreach($tt->tkn as $key => $value){
				if(!strstr($key,"account_")){
					tkntbl_rmv($tt,$key);
				}
			}

			mas_lts($mas,$ftt,"accounts");
			tkntbl_add($tt,"account_validation",rand_string(7),1);
			tkntbl_add($tt,"account_ip",ttn($gtt,"REMOTE_ADDR"),1);
			tkntbl_add($tt,"account_tsadd",time(),1);
			tkntbl_add($gtt,"account_password",ttn($tt,"account_password"),1);
			tkntbl_add($tt,"account_password",md5(ttn($tt,"account_password")),1);
			tkntbl_snprintf($gtt,"hash_temp",1,MAX_RESULTS,"%s%s%s",ttn($tt,"account_validation"),time(),ttn($tt,"username"));
			tkntbl_add($tt,"account_hash",base64_encode(ttn($gtt,"hash_temp")),1);
			mas_gri($mas,$tt,$ftt,0,"accounts");
			tkntbl_add($tt,"@id",mas_insert_id($mas),1);
			tkntbl_add($tt,"account_password",ttn($gtt,"account_password"),1);
			tkntbl_add($tt,"email_to",ttn($tt,"account_email"),1);
			tkntbl_add($tt,"email_from","seedliving@seeds.ca",1);
			tkntbl_add($tt,"email_subject","Welcome to SeedLiving",1);


			tmplt_proc_ex_tt(TEMPLROOT,tkntbl_search($tmpl,"secureIntroEmail"),OPENTAG,CLOSETAG,1,stdout,$tt,"message",array(&$tt,&$temptt,&$dtt));
			$headers  = "MIME-Version: 1.0\n";
			$headers .= "Content-type: text/html; charset=iso-8859-1\n";
            $headers .= "To: ".ttn($tt,"email_to")."\n";
            $headers .= "Bcc: ".SITEEMAIL."\n";
//			$headers .= "Bcc: colin@anlanda.com\n";
			$headers .= "From: SeedLiving <".SITEEMAIL.">\n";
			$subject = "Welcome to SeedLiving";
			mail(NULL,$subject,ttn($tt,"message"),$headers);
		    //tmplt_mail($tt,$tmpl,"secureIntroEmail");
			slUpdateRequest($mas4,"account","A",ttn($tt,"@id"));

			if(ttn($gtt,"pid")){
				mas_qnr($mas,"INSERT INTO carts_a VALUES('','%s','%s','','%s','')",ttn($gtt,"pid"),ttn($tt,"@id"),time());
			}

			if(!strcmp(ttn($gtt,"fee_enabled"),"N") && ttn($tt,"@id")){
				mas_qnr($mas,"INSERT INTO unlimited VALUES('','%s','%s','40','Y','%s','')",ttn($tt,"@id"),(time()+31536000),time());
				mas_qnr($mas,"UPDATE unlimited SET u_enabled = 'Y', u_tsmod = '%s'  WHERE u_accountid = '%s'",time(),ttn($tt,"@id"));
				mas_qnr($mas,"UPDATE accounts SET account_unl4 = 'Y' WHERE account_id = '%s'",ttn($tt,"@id"));
			}

			if(!strcmp(ttn($gtt,"seller"),"yes")){
				if(!strcmp(ttn($tt,"account_prepaid"),"P")){
					if(ttn($gtt,"slSellerFees_pp")){
						$amount = (ttn($gtt,"slSellerFees_pp")*10);
						mas_qnr($mas,"INSERT INTO pres VALUES('','%s','%s','%s','N','%s','')",ttn($tt,"@id"),$amount,$amount,time());
						slUpdateRequest($mas4,"pres","A",mas_insert_id($mas));
						$err = pp_chainpayments("PAY","http://www.seedliving.ca/".SEONAME."/cancelAccount-".ttn($tt,"@id")."/","http://www.seedliving.ca/".SEONAME."/saveAccount-".ttn($tt,"@id")."/","CAD",ttn($tt,"account_email"),"","SENDER",array(ADMIN_EMAIL),array("false"),array($amount),"SeedLiving prepaid sellers fees");
					} else criterr("Error 1");
				} else if(!strcmp(ttn($tt,"account_preapproval"),"P")){
					if(ttn($gtt,"slSellerFees_pa")){
						mas_qnr($mas,"INSERT INTO preas VALUES('','%s','%s','%s','N','%s','%s','')",ttn($tt,"@id"),ttn($gtt,"slSellerFees_pa"),ttn($gtt,"slSellerFees_pa"),(time()+2628000),time());
						slUpdateRequest($mas4,"preas","A",mas_insert_id($mas));
						$err =  pp_preapproval(ttn($tt,"@id"),$mas,"http://www.seedliving.ca/".SEONAME."/saveAccount-".ttn($tt,"@id")."/","http://www.seedliving.ca/".SEONAME."/cancelAccount-".ttn($tt,"@id")."/","CAD",date("Y-m-d",time())."T".date("H:i:s",time()),date("Y-m-d",(time()+2628000))."T".date("H:i:s",(time()+2628000)),ttn($gtt,"slSellerFees_pa"),ttn($tt,"account_email"),"1000","NO_PERIOD_SPECIFIED", "", "",ttn($gtt,"slSellerFees_pa"),"","");
					}
				} else if(!strcmp(ttn($tt,"account_unl2"),"P")){
						$amount = "20";
						mas_qnr($mas,"INSERT INTO unlimited VALUES('','%s','%s','20','N','%s','')",ttn($tt,"@id"),(time()+31536000),time());
						slUpdateRequest($mas4,"unlimited","A",mas_insert_id($mas));
						$err =  pp_chainpayments("PAY","http://www.seedliving.ca/".SEONAME."/cancelAccount-".ttn($tt,"@id")."/","http://www.seedliving.ca/".SEONAME."/saveAccount-".ttn($tt,"@id")."/","CAD",ttn($tt,"account_email"),"","SENDER",array(ADMIN_EMAIL),array("false"),array($amount),"SeedLiving unlimited 20 fees");
		         } else if(!strcmp(ttn($tt,"account_unl4"),"P")){
						$amount = "40";
						mas_qnr($mas,"INSERT INTO unlimited VALUES('','%s','%s','40','N','%s','')",ttn($tt,"@id"),(time()+31536000),time());
						slUpdateRequest($mas4,"unlimited","A",mas_insert_id($mas));
						$err = pp_chainpayments("PAY","http://www.seedliving.ca/".SEONAME."/cancelAccount-".ttn($tt,"@id")."/","http://www.seedliving.ca/".SEONAME."/saveAccount-".ttn($tt,"@id")."/","CAD",ttn($tt,"account_email"),"","SENDER",array(ADMIN_EMAIL),array("false"),array($amount),"SeedLiving unlimited 40 fees");
				} else slNoOperation($tmpl,$gtt);
			} else {
				header("Location: /".SEONAME."/login-1/");
			}
		} else {
			slNoOperation($tmpl,$gtt);
		}
		} else slNoOperation($tmpl,$gtt);

		if($err){
			tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Top"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
			mas_qnr($mas,"DELETE FROM accounts WHERE account_id = '%s'",ttn($tt,"@id"));
			mas_qnr($mas,"DELETE FROM unlimited WHERE u_accountid = '%s'",ttn($tt,"@id"));
			mas_qnr($mas,"DELETE FROM pres WHERE pre_accountid = '%s'",ttn($tt,"@id"));
			mas_qnr($mas,"DELETE FROM preas WHERE prea_accountid = '%s'",ttn($tt,"@id"));
			tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"slPaypalError"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
			tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Bottom"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		}

		criterr(NULL);
	break;

	case "cancelAccount":
		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Top"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		if(ttn($tt,"@id")){
			mas_q1($mas,$temptt,"SELECT * FROM accounts WHERE account_validation<>'' AND (account_prepaid='P' OR account_preapproval='P') AND account_id = '%s'",ttn($tt,"@id"));
			if($mas->mas_row_cnt){
				tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"cancelAccountSuccess"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
				mas_qnr($mas,"DELETE FROM accounts WHERE account_id = '%s'",ttn($tt,"@id"));
				mas_qnr($mas,"DELETE FROM unlimited WHERE u_accountid = '%s'",ttn($tt,"@id"));
				mas_qnr($mas,"DELETE FROM pres WHERE pre_accountid = '%s'",ttn($tt,"@id"));
				mas_qnr($mas,"DELETE FROM preas WHERE prea_accountid = '%s'",ttn($tt,"@id"));

			} else {
				tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"cancelAccountError"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));

			}
		}
		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Bottom"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		criterr(NULL);
	break;

	case "slCompleteSwap":
		if(ttn($gtt,"account_id")){
	    	if(ttn($tt,"@id")){
				mas_q1($mas,$temptt,"SELECT * FROM tradingtable WHERE tt_completed = 'N' AND tt_id = '%s'",ttn($tt,"@id"));
				if($mas->mas_row_cnt){
					mas_qnr($mas,"UPDATE tradingtable SET tt_completed = 'Y' WHERE tt_id = '%s'",ttn($tt,"@id"));

					/*SELLER*/
					tkntbl_add($ftt,"account_username_sl",ttn($gtt,"account_username"),1);
					/* BUYER */
					mas_q1($mas,$dtt,"SELECT * FROM accounts,users WHERE account_userid = user_id AND account_id = '%s'",ttn($temptt,"tt_accountid"));
					mas_q1($mas,$temptt,"SELECT * FROM seeds WHERE seed_id = '%s'",ttn($temptt,"tt_seedid"));

					tmplt_proc_ex_tt(TEMPLROOT,tkntbl_search($tmpl,"secureTradeTableEmailComplete"),OPENTAG,CLOSETAG,1,stdout,$tt,"message",array(&$tt,&$ftt,&$temptt,&$dtt));
					$headers  = "MIME-Version: 1.0\n";
					$headers .= "Content-type: text/html; charset=iso-8859-1\n";
					$headers .= "To: ".ttn($dtt,"account_email")."\n";
					$headers .= "Bcc: ".SITEEMAIL."\n";
//					$headers .= "Bcc: colin@anlanda.com\n";
					$headers .= "From: SeedLiving <".SITEEMAIL.">\n";
					$subject = "SeedLiving - Your giveaway table request for ".ttn($temptt,"seed_title")." has been shipped.";
					mail(NULL,$subject,ttn($tt,"message"),$headers);

				} else slNoOperation($tmpl,$gtt);
			} else slNoOperation($tmpl,$gtt);
		} else slLogin($tmpl,$gtt);
		criterr(NULL);
	break;

	case "secureSwap":
		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Top"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));


		mas_q1($mas,$ftt,"SELECT * FROM userSwapCount WHERE us_accountid = '%s' AND us_enabled = 'Y' AND us_blocks>0",ttn($gtt,"account_id"));
		  if($mas->mas_row_cnt){
			tmplt_proc_ex_tt(TEMPLROOT,tkntbl_search($tmpl,"slSwapAccountWarningSC2"),OPENTAG,CLOSETAG,1,stdout,$tt,"slSwapAccountWarning",array(&$tt,&$temptt,&$gtt));
			tkntbl_ftable($ftt);
		} else {
			if(!strcmp(ttn($gtt,"account_prepaid"),"Y")){
				tmplt_proc_ex_tt(TEMPLROOT,tkntbl_search($tmpl,"slSwapAccountWarningPP2"),OPENTAG,CLOSETAG,1,stdout,$tt,"slSwapAccountWarning",array(&$tt,&$temptt,&$gtt));
			} elseif(!strcmp(ttn($gtt,"account_preapproval"),"Y")){
				tmplt_proc_ex_tt(TEMPLROOT,tkntbl_search($tmpl,"slSwapAccountWarningPA2"),OPENTAG,CLOSETAG,1,stdout,$tt,"slSwapAccountWarning",array(&$tt,&$temptt,&$gtt));
			} elseif(!strcmp(ttn($gtt,"account_unl4"),"Y") || !strcmp(ttn($gtt,"account_unl2"),"Y")){
			} else criterr(NULL);
		}


		mas_qb($mas,"SELECT * FROM swaprequests WHERE sr_approved = 'N' and sr_user2id = '%s'",ttn($gtt,"account_id"));
		if($mas->mas_row_cnt) tkntbl_add($tt,"secureSwapRow","<h2>Swap Requests</h2>",2);
		while(mas_qg($mas,$temptt)){
			mas_q1($mas2,$temptt,"SELECT account_username as sr_user1id, seed_title as sr_seed1name  FROM accounts,seeds WHERE seed_userid = account_userid AND account_id = '%s'",ttn($temptt,"sr_user1id"));
			mas_q1($mas2,$temptt,"SELECT seed_title as sr_seed2name FROM seeds WHERE seed_userid = '%s'",ttn($gtt,"account_userid"));
			tmplt_proc_ex_tt(TEMPLROOT,tkntbl_search($tmpl,"secureSwapRow"),OPENTAG,CLOSETAG,2,stdout,$tt,"secureSwapRow",array(&$tt,&$temptt,&$ftt));
		} mas_qe($mas);

		tkntbl_ftable($temptt);

		mas_qb($mas,"SELECT * FROM tradingtable WHERE tt_approved = 'Y' and tt_seeduserid = '%s' ORDER BY tt_tsmod desc",ttn($gtt,"account_id"));
		if($mas->mas_row_cnt) tkntbl_add($tt,"secureSwapRow","<h2>giveaway table Requests</h2>",2);
		while(mas_qg($mas,$temptt)){
			mas_q1($mas2,$temptt,"SELECT account_username as sr_user1id FROM accounts WHERE  account_id = '%s'",ttn($temptt,"tt_accountid"));
			mas_q1($mas2,$temptt,"SELECT seed_title as sr_seed1name,seed_shipcost,seed_currency FROM seeds WHERE seed_id = '%s'",ttn($temptt,"tt_seedid"));
			tmplt_proc_ex_tt(TEMPLROOT,tkntbl_search($tmpl,"secureSwapRow2"),OPENTAG,CLOSETAG,2,stdout,$tt,"secureSwapRow",array(&$tt,&$temptt,&$ftt));
		} mas_qe($mas);



		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"secureSwapHeader"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Bottom"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		criterr(NULL);
	break;

	case "secureUserValidate":
		header("Cache-Control: no-cache");
		if(ttn($tt,"account_validation")){
			mas_q1($mas,$temptt,"SELECT * FROM accounts WHERE account_validation = '%s' AND account_id = '%s'",ttn($tt,"account_validation"),ttn($gtt,"account_id"));
			if(!$mas->mas_row_cnt) criterr("Validation Code Error");
			else{
				mas_qnr($mas,"UPDATE accounts SET account_validation = '' WHERE account_validation = '%s' AND account_id = '%s'",ttn($tt,"account_validation"),ttn($gtt,"account_id"));
				header("Location: /".SEONAME."/secureUser/");
			}
		}
		criterr(NULL);
	break;

//	case "seedsSplash":
//        $oSLiv->DrawSeedsSplash();
//	    break;

	case "swapSearch":
		$c=0;
		mas_q1($mas,$temptt,"select count(*) as Total from seeds where (seed_trade = 'S' or seed_trade = 'Y') and seed_quantity > 0 and seed_enabled = 'Y'");



		$page = ttn($tt,"page");
		$totalperpage = "18";
		if(!$page) $page = 1;
		$offset = ($page*$totalperpage)-$totalperpage;
		if(ttn($temptt,"Total")<=$totalperpage) $totalpages = 1;
		else $totalpages = ceil((ttn($temptt,"Total")/$totalperpage));


		mas_qb($mas,"SELECT * FROM seeds,accounts WHERE account_userid = seed_userid AND seed_quantity > 0 AND (seed_trade = 'S' or seed_trade = 'Y') AND  seed_enabled = 'Y' ORDER BY seed_tsadd LIMIT %s,%s",$offset,$totalperpage);




		pagination($tt,$temptt,$tmpl,$gtt,$page,ttn($temptt,"tag_url"),$totalpages);

		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Top"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		tmplt_proc_ex_tt(TEMPLROOT,tkntbl_search($tmpl,"seedSplashTop"),OPENTAG,CLOSETAG,1,stdout,$tt,"catListings",array(&$tt,&$temptt));
		while(mas_qg($mas,$temptt)){
			if($c==2){
					tkntbl_add($tt,"last"," last",1);
			}

$o = new imgman();
$fname = $o->getFilename( 'seeds/thmb', ttn($temptt,"seed_id") );
if( empty($fname) ) $fname = 'noImageAvailable.jpg';
tkntbl_add($gtt,"seed_image",$fname,1);
tkntbl_add($gtt,"seed_sfile",$img,1);

$img = $oSLiv->ImgSrc('seeds/thmb',ttn($temptt,"seed_id"),1,true,false);
tkntbl_add($gtt,"seed_image",$img,1);
tkntbl_add($gtt,"seed_sfile",$img,1);

/*
			if(file_exists(IMAGEROOT."seeds/thmb/".ttn($temptt,"seed_id")."_1.jpg")){
				 	tkntbl_snprintf($gtt,"seed_image",1,MAX_RESULTS,"seeds/thmb/%s_1.jpg",ttn($temptt,"seed_id"));
				} else tkntbl_add($gtt,"seed_image","noImageAvailable.jpg",1);
*/
			tkntbl_add($gtt,"account_username",ttn($temptt,"account_username"),1);
			tmplt_proc_ex_tt(TEMPLROOT,tkntbl_search($tmpl,"seedSplashRow"),OPENTAG,CLOSETAG,2,stdout,$tt,"catListings",array(&$tt,&$temptt,&$gtt));

			$c++;
		    if($c==3) {
				$c=0;
				tkntbl_add($tt,"last","",1);
			}

		} mas_qe($mas);
		tmplt_proc_ex_tt(TEMPLROOT,tkntbl_search($tmpl,"seedSplashBottom"),OPENTAG,CLOSETAG,2,stdout,$tt,"catListings",array(&$tt,&$temptt));
		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"tagSearch"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt,&$temptt));
		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Bottom"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		criterr(NULL);
	break;

	case "tagSearch":

		$c=0;
		mas_q1($mas,$temptt,"SELECT count(*) as Total1 FROM tagrel, seeds, accounts WHERE seed_quantity > 0 AND account_userid = seed_userid AND seed_id = tagrel_seedid AND seed_enabled = 'Y' AND tagrel_tagid = '%s'",ttn($tt,"@id"));


		mas_q1($mas,$temptt,"SELECT count(*) as Tota2l FROM seeds WHERE seed_quantity > 0  AND seed_enabled = 'Y' AND INSTR(seed_title,'%s')",ttn($tt,"overlord2"));


		tkntbl_add($temptt,"Total",(ttn($temptt,"Total1")+ttn($temptt,"Total2")),1);
		$page = ttn($tt,"page");
		$totalperpage = "18";
		if(!$page) $page = 1;
		$offset = ($page*$totalperpage)-$totalperpage;
		if(ttn($temptt,"Total")<=$totalperpage) $totalpages = 1;
		else $totalpages = ceil((ttn($temptt,"Total")/$totalperpage));

		mas_q1($mas,$temptt,"SELECT * FROM tags WHERE tag_id = '%s'",ttn($tt,"@id"));
		mas_qb($mas,"SELECT * FROM seeds,accounts,tagrel WHERE account_userid = seed_userid AND seed_quantity > 0 AND tagrel_tagid = '%s' AND seed_id = tagrel_seedid AND seed_enabled = 'Y' ORDER BY seed_tsadd LIMIT %s,%s",ttn($tt,"@id"),$offset,$totalperpage);




		pagination($tt,$temptt,$tmpl,$gtt,$page,ttn($temptt,"tag_url"),$totalpages);

		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Top"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		tmplt_proc_ex_tt(TEMPLROOT,tkntbl_search($tmpl,"seedSplashTop"),OPENTAG,CLOSETAG,1,stdout,$tt,"catListings",array(&$tt,&$temptt));
		while(mas_qg($mas,$temptt)){
			if($c==2){
					tkntbl_add($tt,"last"," last",1);
			}

$o = new imgman();
$fname = $o->getFilename( 'seeds/thmb', ttn($temptt,"seed_id") );
if( empty($fname) ) $fname = 'noImageAvailable.jpg';
tkntbl_add($gtt,"seed_image",$fname,1);

$img = $oSLiv->ImgSrc('seeds/thmb',ttn($temptt,"seed_id"),1,true,false);
tkntbl_add($gtt,"seed_image",$img,1);
tkntbl_add($gtt,"seed_sfile",$img,1);

/*
			if(file_exists(IMAGEROOT."seeds/thmb/".ttn($temptt,"seed_id")."_1.jpg")){
				 	tkntbl_snprintf($gtt,"seed_image",1,MAX_RESULTS,"seeds/thmb/%s_1.jpg",ttn($temptt,"seed_id"));
				} else tkntbl_add($gtt,"seed_image","noImageAvailable.jpg",1);
*/
			tkntbl_add($gtt,"account_username",ttn($temptt,"account_username"),1);
			tmplt_proc_ex_tt(TEMPLROOT,tkntbl_search($tmpl,"seedSplashRow"),OPENTAG,CLOSETAG,2,stdout,$tt,"catListings",array(&$tt,&$temptt,&$gtt));

			$c++;
		    if($c==3) {
				$c=0;
				tkntbl_add($tt,"last","",1);
			}

		} mas_qe($mas);
		tmplt_proc_ex_tt(TEMPLROOT,tkntbl_search($tmpl,"seedSplashBottom"),OPENTAG,CLOSETAG,2,stdout,$tt,"catListings",array(&$tt,&$temptt));
		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"tagSearch"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt,&$temptt));
		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Bottom"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		criterr(NULL);
	break;

	case "zoneSearch":
		$c=0;
		mas_q1($mas,$temptt,"SELECT count(*) as Total FROM seeds, accounts WHERE seed_quantity > 0 AND account_userid = seed_userid AND seed_zone = '%s' AND seed_enabled = 'Y'",ttn($tt,"@id"));
		//if(!strcmp(ttn($gtt,"REMOTE_ADDR"),"68.144.69.157")) printf("SELECT count(*) as Total FROM seeds, accounts WHERE account_userid = seed_userid AND seed_zone = '%s' AND seed_enabled = 'Y'",ttn($tt,"@id"));
		$page = ttn($tt,"page");
		$totalperpage = "18";
		if(!$page) $page = 1;
		$offset = ($page*$totalperpage)-$totalperpage;
		if(ttn($temptt,"Total")<=$totalperpage) $totalpages = 1;
		else $totalpages = ceil((ttn($temptt,"Total")/$totalperpage));

		mas_qb($mas,"SELECT * FROM seeds,accounts WHERE seed_quantity > 0 AND account_userid = seed_userid AND seed_zone = '%s' AND seed_enabled = 'Y' ORDER BY seed_tsadd LIMIT %s,%s",ttn($tt,"@id"),$offset,$totalperpage);

		pagination($tt,$temptt,$tmpl,$gtt,$page,ttn($temptt,"seed_zone"),$totalpages);

		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Top"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		tmplt_proc_ex_tt(TEMPLROOT,tkntbl_search($tmpl,"seedSplashTop"),OPENTAG,CLOSETAG,1,stdout,$tt,"catListings",array(&$tt,&$temptt));
		while(mas_qg($mas,$temptt)){
			if($c==2){
					tkntbl_add($tt,"last"," last",1);
			}

$o = new imgman();
$fname = $o->getFilename( 'seeds/thmb', ttn($temptt,"seed_id") );
if( empty($fname) ) $fname = 'noImageAvailable.jpg';
tkntbl_add($gtt,"seed_image",$fname,1);

$img = $oSLiv->ImgSrc('seeds/thmb',ttn($temptt,"seed_id"),1,true,false);
tkntbl_add($gtt,"seed_image",$img,1);
tkntbl_add($gtt,"seed_sfile",$img,1);

/*
			if(file_exists(IMAGEROOT."seeds/thmb/".ttn($temptt,"seed_id")."_1.jpg")){
				 	tkntbl_snprintf($gtt,"seed_image",1,MAX_RESULTS,"seeds/thmb/%s_1.jpg",ttn($temptt,"seed_id"));
				} else tkntbl_add($gtt,"seed_image","noImageAvailable.jpg",1);
*/
			tkntbl_add($gtt,"account_username",ttn($temptt,"account_username"),1);
			tmplt_proc_ex_tt(TEMPLROOT,tkntbl_search($tmpl,"seedSplashRow"),OPENTAG,CLOSETAG,2,stdout,$tt,"catListings",array(&$tt,&$temptt,&$gtt));

			$c++;
		    if($c==3) {
				$c=0;
				tkntbl_add($tt,"last","",1);
			}

		} mas_qe($mas);
		tmplt_proc_ex_tt(TEMPLROOT,tkntbl_search($tmpl,"seedSplashBottom"),OPENTAG,CLOSETAG,2,stdout,$tt,"catListings",array(&$tt,&$temptt));
		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"zoneSearch"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt,&$temptt));
		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Bottom"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		criterr(NULL);
	break;

	case "sendContactEmail":
		tkntbl_add($tt,"email_to","".SITEEMAIL."",1);
		tkntbl_add($tt,"email_from",ttn($tt,"email"),1);
		tkntbl_add($tt,"email_subject",ttn($tt,"subject"),1);
		tmplt_mail($tt,$tmpl,"contactEmail");
		header("Location: /".SEONAME."/thankyou/");
		criterr(NULL);
	break;

	case "slCancelEvents":
		if(ttn($gtt,"account_id")){
			tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Top"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
			tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"slCancelEvents"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
			tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Bottom"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		}
		criterr(NULL);
	break;

	case "slEventsComplete":
		if(ttn($gtt,"account_id")){
			tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Top"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
			tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"slEventsComplete"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
			tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"eventAddEdit"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
			tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Bottom"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		}
		criterr(NULL);
	break;

	case "slDoPostEvent":
		if(ttn($gtt,"account_id")){
			$err = pp_chainpayments("PAY","http://www.seedliving.ca/".SEONAME."/slCancelEvents/","http://www.seedliving.ca/".SEONAME."/slEventsComplete/","CAD",ttn($gtt,"account_email"),(ttn($gtt,"account_pakey")?ttn($ftt,"account_pakey"):""),"EACHRECEIVER",array(ADMIN_EMAIL),array("false"),array(round(ttn($tt,"total"),2)),"SeedLiving event fee");
			if($err){
				criterr("An unexpected error occured.");
			}
		}
		criterr(NULL);
	break;

	case "postEvent":
		if(ttn($gtt,"account_id")){
			if(!strcmp(ttn($gtt,"account_unl2"),"Y") || !strcmp(ttn($gtt,"account_unl4"),"Y"))
				header("Location: http://www.seedliving.ca/".SEONAME."/slEventsComplete/");
			else {
				tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Top"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
				tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"postEvent"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
				tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Bottom"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
			}
		}
		criterr(NULL);
	break;

	case "slDeleteCart":
		if(ttn($tt,"@id")){
			$temp_a = explode(",",ttn($tt,"@id"));
			for($c=0;$c<count($temp_a);$c++){
				mas_qnr($mas,"DELETE FROM carts WHERE cart_id = '%s'",$temp_a[$c]);
			}
		}
		criterr(NULL);
	break;

	case "postNews":
		if(ttn($gtt,"account_id")){
			mas_qb($mas2,"SELECT * FROM zones order by z_id");
			while(mas_qg($mas2,$temptt)){
				tkntbl_snprintf($tt,"slZones",2,MAX_RESULTS,"<option value='%s'>%s</option>",ttn($temptt,"z_id"),ttn($temptt,"z_zone"));
				tkntbl_snprintf($tt,"slZones2",2,MAX_RESULTS,"<option value='%s'>%s</option>",ttn($temptt,"z_id"),ttn($temptt,"z_zone"));
			} mas_qe($mas2);
			tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Top"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
			tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"postNews"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
			tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"newAddEdit"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
			tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Bottom"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		} else{
			slGenericError($tmpl,$gtt,"You must be logged in to perform this function.");
		}
		criterr(NULL);
	break;

	case "slAcceptSwap":
		$flag=0;
		if(ttn($gtt,"account_id") && ttn($tt,"@id")){
			mas_q1($mas,$temptt,"SELECT * FROM swaprequests WHERE sr_id = '%s' AND sr_approved = 'N'",ttn($tt,"@id"));
			if($mas->mas_row_cnt){

				if(!strcmp(ttn($gtt,"account_id"),ttn($temptt,"sr_user2id"))){
					mas_q1($mas,$temptt,"SELECT * FROM userSwapCount WHERE us_accountid = '%s'",ttn($gtt,"account_id"));
					if($mas->mas_row_cnt){
						mas_qnr($mas,"UPDATE userSwapCount SET us_blocks = (us_blocks-1) WHERE us_accountid = '%s'",ttn($gtt,"account_id"));
						$flag++;
					} else {
						if(!strcmp(ttn($gtt,"account_prepaid"),"Y")){
							mas_qnr($mas,"UPDATE pres SET pre_reamount = (pre_reamount-%s) WHERE pre_accountid = '%s'",ttn($gtt,"fee_swap"),ttn($gtt,"account_id"));
							$flag++;
						} else if(!strcmp(ttn($gtt,"account_preapproval"),"Y")){
							$err = pp_chainpayments("PAY","http://www.seedliving.ca","http://www.seedliving.ca","CAD",ttn($gtt,"account_email"),ttn($gtt,"account_pakey"),"EACHRECEIVER",array(ADMIN_EMAIL),array("false"),array(stripNum(ttn($gtt,"fee_swap"),2)),"SeedLiving swap fees");
							if(!$err){
								mas_qnr($mas,"UPDATE preas SET prea_reamount = (prea_reamount-%s) WHERE prea_accountid = '%s'",ttn($gtt,"fee_swap"),ttn($gtt,"account_id"));
								$flag++;
							}
						} else if(!strcmp(ttn($gtt,"account_unl4"),"Y") || !strcmp(ttn($gtt,"account_unl4"),"Y")){
							$flag=2;
						} else criterr(NULL);
					}


					mas_q1($mas,$dtt,"SELECT * FROM accounts WHERE account_id = '%s'",ttn($temptt,"sr_user1id"));
					mas_q1($mas,$temptt,"SELECT * FROM userSwapCount WHERE us_accountid = '%s'",ttn($dtt,"account_id"));
					if($mas->mas_row_cnt){
						mas_qnr($mas,"UPDATE userSwapCount SET us_blocks = (us_blocks-1) WHERE us_accountid = '%s'",ttn($dtt,"account_id"));
						$flag++;
					} else {
						if(!strcmp(ttn($dtt,"account_prepaid"),"Y")){
							mas_qnr($mas,"UPDATE pres SET pre_reamount = (pre_reamount-%s) WHERE pre_accountid = '%s'",ttn($gtt,"fee_swap"),ttn($dtt,"account_id"));
							$flag++;
						} elseif(!strcmp(ttn($dtt,"account_preapproval"),"Y")){
							$err = pp_chainpayments("PAY","http://www.seedliving.ca","http://www.seedliving.ca","CAD",ttn($dtt,"account_email"),ttn($dtt,"account_pakey"),"EACHRECEIVER",array(ADMIN_EMAIL),array("false"),array(stripNum(ttn($gtt,"fee_swap"),2)),"SeedLiving swap fees");
							if(!$err){
								mas_qnr($mas,"UPDATE preas SET prea_reamount = (prea_reamount-%s) WHERE prea_accountid = '%s'",ttn($gtt,"fee_swap"),ttn($dtt,"account_id"));
								$flag++;
							}
						} else if(!strcmp(ttn($gtt,"account_unl4"),"Y") || !strcmp(ttn($gtt,"account_unl4"),"Y")){
							$flag=2;
						} else criterr(NULL);
					}

					if($flag == "2") mas_qnr($mas,"UPDATE swaprequests SET sr_approved = 'Y', sr_tsmod ='%s' WHERE sr_id = '%s'",time(),ttn($tt,"@id"));
					else criterr(NULL);


					mas_q1($mas,$ftt,"SELECT account_username as account_username_1, account_email as account_email_1, user_fname as user_fname_1, user_lname as user_lname_1,  user_address as user_address_1, user_city as user_city_1, user_state as user_state_1, user_country as user_country_1, user_zip as user_zip_1 FROM accounts,users WHERE user_id = account_userid and account_id = '%s'",ttn($temptt,"sr_user1id"));
					mas_q1($mas,$ftt,"SELECT account_username as account_username_2, account_email as account_email_2, user_fname as user_fname_2, user_lname as user_lname_2,  user_address as user_address_2, user_city as user_city_2, user_state as user_state_2, user_country as user_country_2, user_zip as user_zip_2 FROM accounts,users WHERE user_id = account_userid and account_id = '%s'",ttn($temptt,"sr_user2id"));
					mas_q1($mas,$ftt,"SELECT seed_title as seed_title_1 FROM seeds WHERE seed_id = '%s'",ttn($temptt,"sr_seed1id"));
					mas_q1($mas,$ftt,"SELECT seed_title as seed_title_2 FROM seeds WHERE seed_id = '%s'",ttn($temptt,"sr_seed2id"));

					/* User 2 */
					tmplt_proc_ex_tt(TEMPLROOT,tkntbl_search($tmpl,"secureSwapAccept2Email"),OPENTAG,CLOSETAG,1,stdout,$tt,"message",array(&$tt,&$ftt,&$temptt,&$dtt));
					$headers  = "MIME-Version: 1.0\n";
					$headers .= "Content-type: text/html; charset=iso-8859-1\n";
					$headers .= "To: ".ttn($ftt,"account_email_2")."\n";
					$headers .= "Bcc: ".SITEEMAIL."\n";
//					$headers .= "Bcc: colin@anlanda.com\n";
					$headers .= "From: SeedLiving <".SITEEMAIL.">\n";
					$subject = "SeedLiving - Your swap request for ".ttn($ftt,"seed_title_2")." has been accepted.";
					mail(NULL,$subject,ttn($tt,"message"),$headers);

					/*User 1*/
					tmplt_proc_ex_tt(TEMPLROOT,tkntbl_search($tmpl,"secureSwapAccept1Email"),OPENTAG,CLOSETAG,1,stdout,$tt,"message",array(&$tt,&$ftt,&$temptt,&$dtt));
					$headers  = "MIME-Version: 1.0\n";
					$headers .= "Content-type: text/html; charset=iso-8859-1\n";
					$headers .= "To: ".ttn($ftt,"account_email_1")."\n";
					$headers .= "Bcc: ".SITEEMAIL."\n";
//					$headers .= "Bcc: colin@anlanda.com\n";
					$headers .= "From: SeedLiving <".SITEEMAIL.">\n";
					$subject = "SeedLiving - Your swap request for ".ttn($ftt,"seed_title_2")." has been accepted.";
					mail(NULL,$subject,ttn($tt,"message"),$headers);

					tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Top"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
					tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"slAcceptSwap"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
					tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Bottom"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
				}
			}
		}
		criterr(NULL);
	break;

	case "slRejectSwap":
		if(ttn($gtt,"account_id") && ttn($tt,"@id")){
			mas_q1($mas,$temptt,"SELECT * FROM swaprequests WHERE sr_id = '%s' AND sr_approved = 'N'",ttn($tt,"@id"));
			if($mas->mas_row_cnt){
				if(!strcmp(ttn($gtt,"account_id"),ttn($temptt,"sr_user2id"))){
					mas_qnr($mas,"DELETE FROM swaprequests WHERE sr_id = '%s'",ttn($tt,"@id"));
					tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Top"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
					tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"slRejectSwap"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
					tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Bottom"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));

					mas_q1($mas,$ftt,"SELECT account_username as account_username_1, account_email as account_email_1, user_fname as user_fname_1, user_lname as user_lname_1 FROM accounts,users WHERE user_id = account_userid and account_id = '%s'",ttn($temptt,"sr_user1id"));
					mas_q1($mas,$ftt,"SELECT seed_title as seed_title_1 FROM seeds WHERE seed_id = '%s'",ttn($temptt,"sr_seed1id"));
					mas_q1($mas,$ftt,"SELECT seed_title as seed_title_2 FROM seeds WHERE seed_id = '%s'",ttn($temptt,"sr_seed2id"));

					tmplt_proc_ex_tt(TEMPLROOT,tkntbl_search($tmpl,"secureSwapRejectedEmail"),OPENTAG,CLOSETAG,1,stdout,$tt,"message",array(&$tt,&$ftt,&$temptt,&$dtt,&$gtt));
					$headers  = "MIME-Version: 1.0\n";
					$headers .= "Content-type: text/html; charset=iso-8859-1\n";
					$headers .= "To: ".ttn($ftt,"account_email_1")."\n";
					$headers .= "Bcc: ".SITEEMAIL."\n";
//					$headers .= "Bcc: colin@anlanda.com\n";
					$headers .= "From: SeedLiving <".SITEEMAIL.">\n";
					$subject = "SeedLiving - Your swap request for ".ttn($ftt,"seed_title_2")." has been rejected";
					mail(NULL,$subject,ttn($tt,"message"),$headers);
				}
			}
		}
		criterr(NULL);
	break;

	case "slCheckSwapCount":
		if(ttn($gtt,"account_id")){
			if(!strcmp(ttn($gtt,"account_unl2"),"Y") || !strcmp(ttn($gtt,"account_unl4"),"Y")) criterr("2");
			mas_q1($mas,$temptt,"SELECT * FROM userSwapCount WHERE us_accountid = '%s' AND us_enabled = 'Y'",ttn($gtt,"account_id"));
			if($mas->mas_row_cnt) criterr("2");
			else criterr("1");
		} else criterr("0");
		criterr(NULL);
	break;

	case "slCancelSwaps":
		if(ttn($gtt,"account_id")){
			tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Top"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		    mas_qnr($mas,"DELETE FROM userSwapCount WHERE us_enabled = 'N' AND us_accountid = '%s'",ttn($gtt,"account_id"));
			tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"slCancelSwaps"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
			tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Bottom"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		}
		criterr(NULL);
	break;

	case "slSwapsComplete":
		if(ttn($gtt,"account_id")){
			mas_q1($mas,$temptt,"SELECT * FROM userSwapCount WHERE us_enabled = 'N' AND us_accountid = '%s'",ttn($gtt,"account_id"));
			if($mas->mas_row_cnt){
				mas_qnr($mas,"UPDATE userSwapCount SET us_enabled = 'Y', us_tsmod = '%s' WHERE us_accountid = '%s'",time(),ttn($gtt,"account_id"));
				tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Top"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
				tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"slSwapsComplete"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
				tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Bottom"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
			} else criterr("An unexpected error occured");
		}
		criterr(NULL);
	break;

	case "slSaveSwapRequest":
		if(ttn($gtt,"account_id")){
           if(ttn($tt,"sr_seed1id")){
		   	  if(ttn($tt,"sr_transid")){
			  mas_q1($mas,$dtt,"SELECT * FROM swaprequests WHERE sr_transid='%s'",ttn($tt,"sr_transid"));
			  if(!$mas->mas_row_cnt){
		   	  mas_qnr($mas,"INSERT INTO swaprequests VALUES('','%s','%s','%s','%s','%s','%s','N','%s','%s','')"
			  ,ttn($gtt,"account_id")
			  ,ttn($tt,"sr_seed1id")
			  ,ttn($tt,"sr_seed1q")
			  ,ttn($tt,"sr_user2id")
			  ,ttn($tt,"sr_seed2id")
			  ,ttn($tt,"sr_seed2q")
			  ,ttn($tt,"sr_transid")
			  ,time()
			  );
			  slUpdateRequest($mas4,"swaprequests","A",mas_insert_id($mas));
			  tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Top"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
			  tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"slSwapsRequestSent"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
			  tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Bottom"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
			  	mas_q1($mas,$temptt,"SELECT * FROM swaprequests WHERE sr_transid='%s'",ttn($tt,"sr_transid"));
			  	mas_q1($mas,$ftt,"SELECT account_username as account_username_2, account_email as account_email_2, user_fname as user_fname_2, user_lname as user_lname_2 FROM accounts,users WHERE user_id = account_userid and account_id = '%s'",ttn($temptt,"sr_user2id"));
				mas_q1($mas,$ftt,"SELECT seed_title as seed_title_1 FROM seeds WHERE seed_id = '%s'",ttn($temptt,"sr_seed1id"));
				mas_q1($mas,$ftt,"SELECT seed_title as seed_title_2 FROM seeds WHERE seed_id = '%s'",ttn($temptt,"sr_seed2id"));

				tmplt_proc_ex_tt(TEMPLROOT,tkntbl_search($tmpl,"secureSwapNotificationdEmail"),OPENTAG,CLOSETAG,1,stdout,$tt,"message",array(&$tt,&$ftt,&$temptt,&$dtt,&$gtt));
				$headers  = "MIME-Version: 1.0\n";
				$headers .= "Content-type: text/html; charset=iso-8859-1\n";
				$headers .= "To: ".ttn($ftt,"account_email_2")."\n";
				$headers .= "Bcc: ".SITEEMAIL."\n";
//				$headers .= "Bcc: colin@anlanda.com\n";
				$headers .= "From: SeedLiving <".SITEEMAIL.">\n";
				$subject = "SeedLiving - You have a new swap request.";
				mail(NULL,$subject,ttn($tt,"message"),$headers);


			  criterr(NULL);
			  }
			  }
		   }
		}
			    tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Top"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
				tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"slOperationalError"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
				tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Bottom"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		criterr(NULL);
	break;

	case "slCancelTTPayment":
		if(ttn($gtt,"account_id")){
			if(ttn($tt,"@id")){
				mas_q1($mas,$temptt,"SELECT * FROM tradingtable WHERE tt_transid = '%s' AND tt_approved = 'N' AND tt_accountid = '%s'",ttn($tt,"@id"),ttn($gtt,"account_id"));
				if($mas->mas_row_cnt){
					mas_qnr($mas,"DELETE FROM tradingtable WHERE tt_id = '%s'",ttn($temptt,"tt_id"));
					mas_q1($mas,$temptt,"SELECT * FROM seeds WHERE seed_id = '%s'",ttn($temptt,"tt_seedid"));
					tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Top"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		 			tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"slCancelTTPayment"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt,&$temptt));
		 			tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Bottom"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
				} else slNoOperation($tmpl,$gtt);
			} else slNoOperation($tmpl,$gtt);
		} else slLogin($tmpl,$gtt);
		criterr(NULL);
	break;

	case "slSuccessTTPayment":
		if(ttn($gtt,"account_id")){
			if(ttn($tt,"@id")){
				mas_q1($mas,$temptt,"SELECT * FROM tradingtable WHERE tt_transid = '%s' AND tt_approved = 'N' AND tt_accountid = '%s'",ttn($tt,"@id"),ttn($gtt,"account_id"));
				if($mas->mas_row_cnt){
					mas_qnr($mas,"UPDATE tradingtable SET tt_approved = 'Y', tt_tsmod = '%s' WHERE tt_transid = '%s' AND tt_approved = 'N' AND tt_accountid = '%s'",time(),ttn($tt,"@id"),ttn($gtt,"account_id"));
		 			mas_qnr($mas,"UPDATE userSwapCount SET us_blocks = (us_blocks-1) WHERE us_accountid = '%s'",ttn($gtt,"account_id"));
					mas_q1($mas,$temptt,"SELECT * FROM userSwapCount WHERE us_accountid = '%s'",ttn($gtt,"account_id"));
					mas_q1($mas,$temptt,"SELECT * FROM seeds WHERE seed_id = '%s'",ttn($temptt,"tt_seedid"));
					if(!strcmp(ttn($temptt,"tt_quantity"),ttn($temptt,"seed_quantity"))){
						mas_qnr($mas,"UPDATE seeds SET seed_quantity = '0',seed_enabled = 'N', seed_tradetable = 'N' , seed_featured = 'N' WHERE seed_id = '%s'",ttn($temptt,"tt_seedid"));
						$dev_null = system("php -q ".SEEDLIVING_ROOT_DIR."bin/tags.php");
						tkntbl_add($tt,"slNoStock",1,1);
					} else {
						mas_qnr($mas,"UPDATE seeds SET seed_quantity = (seed_quantity-%s) WHERE seed_id = '%s'",ttn($temptt,"tt_quantity"),ttn($temptt,"tt_seedid"));

					}
					mas_q1($mas,$temptt,"SELECT account_username as seed_accountname FROM accounts WHERE account_userid = '%s'",ttn($temptt,"seed_userid"));
					tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Top"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		 			tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"slSuccessTTPayment"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt,&$temptt));
		 			tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Bottom"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));


					/* Load Seed Owner */
					mas_q1($mas,$ftt,"SELECT * FROM accounts,users WHERE account_id = '%s' and account_userid = user_id",ttn($temptt,"tt_seeduserid"));

					tkntbl_add($ftt,"account_username_by",ttn($gtt,"account_username"),1);
					mas_q1($mas,$dtt,"SELECT user_fname as user_fname_by, user_lname as user_lname_by, user_address as user_address_by, user_city as user_city_by, user_state as user_state_by, user_zip as user_zip_by, user_country as user_country_by FROM users WHERE user_accountid = '%s'",ttn($gtt,"account_id"));

					tmplt_proc_ex_tt(TEMPLROOT,tkntbl_search($tmpl,"secureTradeTableEmail"),OPENTAG,CLOSETAG,1,stdout,$tt,"message",array(&$tt,&$ftt,&$temptt,&$dtt));
					$headers  = "MIME-Version: 1.0\n";
					$headers .= "Content-type: text/html; charset=iso-8859-1\n";
					$headers .= "To: ".ttn($ftt,"account_email")."\n";
					$headers .= "Bcc: ".SITEEMAIL."\n";
//					$headers .= "Bcc: colin@anlanda.com\n";
					$headers .= "From: SeedLiving <".SITEEMAIL.">\n";
					$subject = "SeedLiving - giveaway table request has been completed for ".ttn($temptt,"seed_title");
					mail(NULL,$subject,ttn($tt,"message"),$headers);

				} else slNoOperation($tmpl,$gtt);
			} else slNoOperation($tmpl,$gtt);
		} else slLogin($tmpl,$gtt);
		criterr(NULL);
	break;

	case "slDoTTPayment":
		if(ttn($gtt,"account_id")){
			mas_q1($mas,$temptt,"SELECT * FROM tradingtable WHERE tt_accountid = '%s' AND tt_transid = '%s' AND tt_seedid = '%s' AND tt_tsmod ='0'",ttn($gtt,"account_id"),ttn($tt,"tt_transid"),ttn($tt,"tt_seedid"));
			if(!$mas->mas_row_cnt){
				mas_qnr($mas,"INSERT INTO tradingtable VALUES ('','%s','%s','%s','%s','N','%s','%s','N','%s','0')"
				,ttn($gtt,"account_id")
				,ttn($tt,"tt_seedid")
				,ttn($tt,"tt_seeduserid")
				,ttn($tt,"seed_q")
				,ttn($tt,"tt_shipcost")
				,ttn($tt,"tt_transid")
				,time()
				);
				slUpdateRequest($mas4,"tradingtable","A",mas_insert_id($mas));
				mas_q1($mas,$ftt,"SELECT * FROM accounts WHERE account_id = '%s'",ttn($tt,"tt_seeduserid"));
				mas_q1($mas,$ftt,"SELECT * FROM seeds WHERE seed_id = '%s'",ttn($tt,"tt_seedid"));
				$err = pp_chainpayments("PAY","http://www.seedliving.ca/".SEONAME."/slCancelTTPayment-".ttn($tt,"tt_transid")."/","http://www.seedliving.ca/".SEONAME."/slSuccessTTPayment-".ttn($tt,"tt_transid")."/",ttn($ftt,"seed_currency"),ttn($gtt,"account_email"),(ttn($gtt,"account_pakey")?ttn($ftt,"account_pakey"):""),"EACHRECEIVER",array(ttn($ftt,"account_email")),array("false"),array(round(ttn($tt,"tt_shipcost"),2)),"SeedLiving giveaway table");
				if($err){
					slGenericError($tmpl,$gtt,$err);
				}
			} else slNoOperation($tmpl,$gtt);
		} else slLogin($tmpl,$gtt);
		criterr(NULL);
	break;
	case "slDoTTSwap":
		if(ttn($gtt,"account_id")){
			if(ttn($tt,"@id")){
				mas_q1($mas,$temptt,"SELECT * FROM seeds, users,accounts WHERE seed_id = '%s' AND seed_userid = user_id AND seed_tradetable = 'Y' AND account_userid = seed_userid",ttn($tt,"@id"));
				if($mas->mas_row_cnt){
					mas_q1($mas,$temptt,"SELECT (SUM(us_blocks)-1) as slSwapBlocks FROM userSwapCount WHERE us_accountid = '%s' AND us_enabled = 'Y'",ttn($gtt,"account_id"));
					if(!strcmp(ttn($gtt,"fee_enabled"),"N")) tkntbl_add($temptt,"slSwapBlocks","1",1);
					if(ttn($temptt,"slSwapBlocks")){
						for($c=1;$c<=ttn($temptt,"seed_quantity");$c++){
							tkntbl_snprintf($temptt,"seed_q",2,MAX_RESULTS,"<option value=\"%s\">%s</option>",$c,$c);
						}
						tkntbl_add($tt,"tt_transid",rand_string(7),1);
						tkntbl_add($tt,"slTags",ttn($gtt,"slTags"),1);
						tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Top"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
						tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"slDoTTSwap"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$temptt));
						tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Bottom"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
					} else slNoOperation($tmpl,$gtt);
				} else slNoOperation($tmpl,$gtt);
			}
		} else slLogin($tmpl,$gtt);
		criterr(NULL);
	break;
	case "slDoSwap":
		if(ttn($gtt,"account_id")){
			if(ttn($tt,"@id")){
				tkntbl_add($tt,"sr_transid",time(),1);
				mas_q1($mas,$ftt,"SELECT * FROM seeds, accounts WHERE seed_userid = account_userid AND seed_id = '%s'",ttn($tt,"@id"));
				mas_q1($mas2,$ftt,"SELECT * FROM cats where cat_id  = '%s'",ttn($ftt,"seed_topcat"));
				tkntbl_snprintf($tt,"slSeedSwap2",2,MAX_RESULTS,"%s",ttn($ftt,"seed_title"));
				tkntbl_snprintf($tt,"slUserSwap2",2,MAX_RESULTS,"%s",ttn($ftt,"account_username"));
				tkntbl_snprintf($tt,"sr_user2id",2,MAX_RESULTS,"%s",ttn($ftt,"account_id"));
				for($c=1;$c<=ttn($ftt,"seed_quantity");$c++){
					tkntbl_snprintf($ftt,"seed_q",2,MAX_RESULTS,"<option value=\"%s\">%s</option>",$c,$c);
				}
				tmplt_proc_ex_tt(TEMPLROOT,tkntbl_search($tmpl,"swapListingsTop"),OPENTAG,CLOSETAG,1,stdout,$tt,"swapListings1",array(&$tt,&$ftt));
				tmplt_proc_ex_tt(TEMPLROOT,tkntbl_search($tmpl,"swapListings"),OPENTAG,CLOSETAG,2,stdout,$tt,"swapListings1",array(&$tt,&$ftt));
				tmplt_proc_ex_tt(TEMPLROOT,tkntbl_search($tmpl,"swapListingsBottom"),OPENTAG,CLOSETAG,2,stdout,$tt,"swapListings1",array(&$tt,&$ftt));
				tkntbl_ftable($ftt);
				tkntbl_add($tt,"swapflag","1",1);
				mas_qb($mas,"SELECT * FROM seeds WHERE seed_enabled = 'Y' AND (seed_trade = 'Y' OR seed_tradetable = 'Y') AND seed_userid = '%s'",ttn($gtt,"account_userid"));
				if($mas->mas_row_cnt){
						tmplt_proc_ex_tt(TEMPLROOT,tkntbl_search($tmpl,"swapListingsTop"),OPENTAG,CLOSETAG,1,stdout,$tt,"swapListings",array(&$tt,&$temptt,&$gtt));
						while(mas_qg($mas,$temptt)){
							mas_q1($mas2,$temptt,"SELECT * FROM cats where cat_id  = '%s'",ttn($temptt,"seed_topcat"));
							for($c=1;$c<=ttn($temptt,"seed_quantity");$c++){
								tkntbl_snprintf($temptt,"seed_q",2,MAX_RESULTS,"<option value=\"%s\">%s</option>",$c,$c);
							}
							tmplt_proc_ex_tt(TEMPLROOT,tkntbl_search($tmpl,"swapListings"),OPENTAG,CLOSETAG,2,stdout,$tt,"swapListings",array(&$tt,&$temptt,&$gtt));
						} mas_qe($mas);



						mas_q1($mas,$ftt,"SELECT * FROM userSwapCount WHERE us_accountid = '%s' AND us_enabled = 'Y' AND us_blocks>0",ttn($gtt,"account_id"));
						if($mas->mas_row_cnt){
							tmplt_proc_ex_tt(TEMPLROOT,tkntbl_search($tmpl,"slSwapAccountWarningSC"),OPENTAG,CLOSETAG,1,stdout,$tt,"slSwapAccountWarning",array(&$tt,&$temptt,&$gtt));
							tkntbl_ftable($ftt);
						} else {

							if(!strcmp(ttn($gtt,"account_prepaid"),"Y")){
								tmplt_proc_ex_tt(TEMPLROOT,tkntbl_search($tmpl,"slSwapAccountWarningPP"),OPENTAG,CLOSETAG,1,stdout,$tt,"slSwapAccountWarning",array(&$tt,&$temptt,&$gtt));
							} elseif(!strcmp(ttn($gtt,"account_preapproval"),"Y")){
								tmplt_proc_ex_tt(TEMPLROOT,tkntbl_search($tmpl,"slSwapAccountWarningPA"),OPENTAG,CLOSETAG,1,stdout,$tt,"slSwapAccountWarning",array(&$tt,&$temptt,&$gtt));
							} elseif (!strcmp(ttn($gtt,"account_unl4"),"Y") ||!strcmp(ttn($gtt,"account_unl2"),"Y") ){
							} else criterr(NULL);
							tmplt_proc_ex_tt(TEMPLROOT,tkntbl_search($tmpl,"swapListingsBottom"),OPENTAG,CLOSETAG,2,stdout,$tt,"swapListings",array(&$tt,&$temptt,&$gtt));
						}
						tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Top"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
						tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"slDoSwap"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
						tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Bottom"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
				} else {
					if(!strcmp(ttn($gtt,"account_accesslevel"),"B")){
						tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Top"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
						tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"slDoSwapNone"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
						tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Bottom"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
					} else {
						tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Top"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
						tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"slDoSwapNoSeed"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
						tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Bottom"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
					}

				}
			} else slNoOperation($tmpl,$gtt);
		} else slLogin($tmpl,$gtt);
		criterr(NULL);
	break;

	case "slDoPaymentSwaps":
		if(ttn($gtt,"account_id")){
			mas_qnr($mas,"INSERT INTO userSwapCount VALUES('','%s','N','%s','%s','%s','')",ttn($gtt,"account_id"),ttn($tt,"total"),ttn($tt,"blocks"),time());
			slUpdateRequest($mas4,"userSwapCount","A",mas_insert_id($mas));
			$err = pp_chainpayments("PAY","http://www.seedliving.ca/".SEONAME."/slCancelSwaps/","http://www.seedliving.ca/".SEONAME."/slSwapsComplete/","CAD",ttn($gtt,"account_email"),(ttn($gtt,"account_pakey")?ttn($ftt,"account_pakey"):""),"EACHRECEIVER",array(ADMIN_EMAIL),array("false"),array(round(ttn($tt,"total"),2)),"SeedLiving swap payment");
			if($err){
				criterr("An unexpected error occured.");
			}
		}
		criterr(NULL);
	break;

	case "checkoutSwaps":
		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Top"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		tkntbl_add($tt,"itemTotal",(ttn($tt,"Swapq")*5),1);
		tkntbl_add($tt,"grandtotal",(ttn($tt,"Swapq")*5),1);
		tkntbl_add($tt,"blocks",(ttn($tt,"Swapq")*10),1);
		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"checkoutSwaps"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Bottom"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		criterr(NULL);
	break;

	case "slPurchaseSwaps":
		if(ttn($gtt,"account_id")){
		 tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Top"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		 mas_q1($mas,$temptt,"SELECT * FROM userSwapCount WHERE us_accountid = '%s' AND us_enabled = 'Y' AND us_blocks>'0'",ttn($gtt,"account_id"));
		 if(!$mas->mas_row_cnt){
		 	tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"slPurchaseSwaps"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		 } else {
		 	echo "You already have prepaid swaps";
		 }
		  tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Bottom"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		 criterr(NULL);
		}
		header("Location: http://www.seedliving.ca/");
		criterr(NULL);
	break;

	case "tradetable":
		if(ttn($tt,"filter")){
			mas_qb($mas,"SELECT * FROM seeds WHERE seed_enabled = 'Y' AND seed_tradetable = 'Y' AND seed_userid <> '%s' order by %s %s",ttn($gtt,"account_userid"),ttn($tt,"filter"),ttn($tt,"filtertype"));
			while(mas_qg($mas,$temptt)){
				mas_q1($mas2,$ftt,"SELECT * FROM accounts WHERE account_userid = '%s'",ttn($temptt,"seed_userid"));
				mas_q1($mas2,$ftt,"SELECT * FROM cats WHERE cat_id = '%s'",ttn($temptt,"seed_topcat"));

				tmplt_proc_ex_tt(TEMPLROOT,tkntbl_search($tmpl,"swapTableListings"),OPENTAG,CLOSETAG,2,stdout,$tt,"swapTableListings",array(&$tt,&$temptt,&$ftt));
				tkntbl_ftable($ftt);
				tkntbl_ftable($temptt);
			} mas_qe($mas);

			criterr(ttn($tt,"swapTableListings"));
		}
		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Top"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		mas_q1($mas,$gtt,"SELECT count(*) as slttTotals FROM seeds WHERE seed_enabled = 'Y' AND seed_tradetable = 'Y' AND seed_userid <> '%s'",ttn($gtt,"account_userid"));;
		mas_qb($mas,"SELECT * FROM seeds WHERE seed_enabled = 'Y' AND seed_tradetable = 'Y' AND seed_userid <> '%s' order by seed_tsadd desc",ttn($gtt,"account_userid"));
		while(mas_qg($mas,$temptt)){
			mas_q1($mas2,$ftt,"SELECT * FROM accounts WHERE account_userid = '%s'",ttn($temptt,"seed_userid"));
			mas_q1($mas2,$ftt,"SELECT * FROM cats WHERE cat_id = '%s'",ttn($temptt,"seed_topcat"));
			tmplt_proc_ex_tt(TEMPLROOT,tkntbl_search($tmpl,"swapTableListings"),OPENTAG,CLOSETAG,2,stdout,$tt,"swapTableListings",array(&$tt,&$temptt,&$ftt));
			tkntbl_ftable($ftt);
			tkntbl_ftable($temptt);
		} mas_qe($mas);
		mas_q1($mas,$temptt,"SELECT SUM(us_blocks) as userSwapAmount FROM userSwapCount WHERE us_enabled = 'Y' AND us_accountid = '%s'",ttn($gtt,"account_id"));
		if(!ttn($temptt,"userSwapAmount")) tkntbl_add($temptt,"userSwapAmount","You do not have any swap credit",1);
		else tkntbl_add($temptt,"userSwapAmount","You have <a href=\"#\">".ttn($temptt,"userSwapAmount")."</a> swaps remaining.",1);
	    tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"tradetableHeader"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt,&$temptt));
		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Bottom"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		criterr(NULL);
	break;

    default:
        $ov = ttn($tt,"overlord");

        if( $ov == "swap" ) mas_q1($mas,$tt,"SELECT count(*) as swapTotals FROM seeds WHERE seed_enabled = 'Y' AND seed_tradetable = 'Y'");

/*TODONEXT*/
        if( $ov == "login" ) {
            if( ttn($tt,"@id") ) {
                if(strstr(ttn($gtt,"HTTP_REFERER"),"/".SEONAME."/account/")){
    				switch(ttn($tt,"@id")){
    					case "1":
    						tkntbl_add($tt,"SEEDSessionUser_loginMessage","<p>Thank you for creating your SeedLiving account. An email has been sent to you, containing your username, password and validation code. Please login to continue your registration.</p>",1);
    					break;
    				}
    			} elseif(strstr(ttn($gtt,"HTTP_REFERER"),"/".SEONAME."/secureUserAddEditPassword")){
    				switch(ttn($tt,"@id")){
    					case "1":
    						tkntbl_add($tt,"SEEDSessionUser_loginMessage","<p>Thank you, your password has been changed. For security reasons please login with your new password.</p>",1);
    					break;
    				}
    			}
            } else {
                list($bHandled,$sOut) = $oSLiv->oUser->Command( 'userLogin-0' );
                echo $sOut;
                criterr(NULL);
            }
        }

        if( $ov == "community" ) {
			mas_qb($mas,"SELECT * FROM news WHERE new_enabled = 'Y' ORDER BY new_tsadd desc");
			if(!$mas->mas_row_cnt) tkntbl_add($tt,"newsList","There are currently no news items.",1);
			while(mas_qg($mas,$temptt)){
				mas_q1($mas2,$ftt,"SELECT * FROM accounts,users WHERE user_id = account_userid AND account_id= '%s'",ttn($temptt,"new_postedby"));
				tkntbl_snprintf($temptt,"new_desc",1,MAX_RESULTS,"%s",stripslashes(ttn($temptt,"new_desc")));
				tmplt_proc_ex_tt(TEMPLROOT,tkntbl_search($tmpl,"newsList"),OPENTAG,CLOSETAG,2,stdout,$tt,"newsList",array(&$tt,&$temptt,&$ftt));
				tkntbl_ftable($ftt);

			} mas_qe($mas);

			mas_qb($mas,"SELECT * FROM zones order by z_id");
			while(mas_qg($mas,$temptt)){
				tkntbl_snprintf($tt,"slZones",2,MAX_RESULTS,"<option value='%s'>%s</option>",ttn($temptt,"z_id"),ttn($temptt,"z_zone"));
			} mas_qe($mas);
		}

        if( $ov == "account" ) {
			if(strstr(ttn($gtt,"HTTP_REFERER"),"productDetails")){
				$temp_a = strstr(ttn($gtt,"HTTP_REFERER"),"-");
				$temp_a = str_replace("-","",$temp_a);
				tkntbl_add($tt,"pid",$temp_a,1);
			}
		}


        if( ($t = tkntbl_search($tmpl,$ov)) ) {
            tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Top"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
            tmplt_proc_ex(TEMPLROOT,$t,OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
            tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Bottom"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
        } else if( $oSLiv->oTmpl->Exists( $ov ) ) {
            tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Top"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
            echo $oSLiv->oTmpl->ExpandTmpl( $ov, GetTokensRA(array($tt,$gtt)) );
            tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Bottom"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
        } else {
            search($tmpl,$temptt,$mas,$gtt,$tt);
        }
        criterr(NULL);
        break;
}

?>
