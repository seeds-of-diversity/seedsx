<?php

function slNoOperation(&$tmplf,&$fgtt){
		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmplf,"Top"),OPENTAG,CLOSETAG,stdout,1,array(&$fgtt));
		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmplf,"slOperationalError"),OPENTAG,CLOSETAG,stdout,1,array(&$fgtt));
		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmplf,"Bottom"),OPENTAG,CLOSETAG,stdout,1,array(&$fgtt));
		criterr(NULL);
}
function slLogin(&$tmplf,&$fgtt){
	tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmplf,"Top"),OPENTAG,CLOSETAG,stdout,1,array(&$fgtt));
	tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmplf,"slMustLogin"),OPENTAG,CLOSETAG,stdout,1,array(&$fgtt));
	tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmplf,"Bottom"),OPENTAG,CLOSETAG,stdout,1,array(&$fgtt));
	criterr(NULL);

}
function slGenericError(&$tmplf,&$fgtt,$err) {
	tkntbl_add($fgtt,"slGenericError",$err,1);
	tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmplf,"Top"),OPENTAG,CLOSETAG,stdout,1,array(&$fgtt));
	tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmplf,"slGenericError"),OPENTAG,CLOSETAG,stdout,1,array(&$fgtt));
	tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmplf,"Bottom"),OPENTAG,CLOSETAG,stdout,1,array(&$fgtt));
	criterr(NULL);
}
function search(&$tmpl,&$temptt,&$mas,&$gtt,&$tt)
{
    global $oSLiv;

    $oSLiv->kfdb->Execute( "CREATE TEMPORARY TABLE search (search_id INT (15));" );
    $sSearch = addslashes(ttn($tt,"@search"));
    $oSLiv->kfdb->Execute( "INSERT INTO search (SELECT seed_id from seeds "
                              ."WHERE seed_enabled = 'Y' AND seed_quantity > 0 AND "
                              ."(INSTR(seed_desc,'$sSearch') OR INSTR(seed_title,'$sSearch') OR INSTR(seed_zone,'$sSearch')))" );
    $oSLiv->kfdb->Execute( "INSERT INTO search (SELECT distinct tagrel_seedid from seeds, tagrel,tags "
                              ."WHERE seed_enabled = 'Y' AND seed_quantity > 0 AND seed_id = tagrel_seedid AND "
                              ."INSTR(tag_name,'$sSearch') and tag_id = tagrel_tagid)" );

    $c=0;

    $nTotal = $oSLiv->kfdb->Query1( "SELECT count(*) FROM search" );

    if( !$nTotal ) {
        tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Top"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
        tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"search"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
        tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Bottom"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
        criterr(NULL);
    }

		$page = ttn($tt,"page");
		$totalperpage = "18";
		if(!$page) $page = 1;
		$offset = ($page*$totalperpage)-$totalperpage;
		if( $nTotal <=$totalperpage) $totalpages = 1;
		else $totalpages = ceil(( $nTotal/$totalperpage));

		tkntbl_add( $temptt, "Total", $nTotal, 1);
		pagination($tt,$temptt,$tmpl,$gtt,$page,ttn($tt,"@search"),$totalpages);

				tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Top"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		tmplt_proc_ex_tt(TEMPLROOT,tkntbl_search($tmpl,"seedSplashTop"),OPENTAG,CLOSETAG,1,stdout,$tt,"catListings",array(&$tt,&$temptt));

		$dbc = $oSLiv->kfdb->CursorOpen(
		            "SELECT DISTINCT * FROM search,seeds,accounts WHERE search_id = seed_id AND account_userid = seed_userid "
		           ."ORDER BY seed_tsadd LIMIT $offset,$totalperpage" );
		while(mas_Fetch( $oSLiv->kfdb, $dbc, $temptt ) ) {
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
			tmplt_proc_ex_tt(TEMPLROOT,tkntbl_search($tmpl,"seedSplashRow"),OPENTAG,CLOSETAG,2,stdout,$tt,"catListings",array(&$tt,&$temptt,&$gtt));

			$c++;

			if(!strcmp(ttn($tt,"@search"),ttn($temptt,"seed_zone"))) tkntbl_add($tt,"iszone","Zone ",1);

		    if($c==3) {
				$c=0;
				tkntbl_add($tt,"last","",1);
			}

		} mas_qe($mas);
		tmplt_proc_ex_tt(TEMPLROOT,tkntbl_search($tmpl,"seedSplashBottom"),OPENTAG,CLOSETAG,2,stdout,$tt,"catListings",array(&$tt,&$temptt));
		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"seedSearch"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt,&$temptt));
		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Bottom"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		$oSLiv->kfdb->Execute( "DROP TEMPORARY TABLE search;" );
		criterr(NULL);
}
function slUpdateRequest( $tmasDummy, $rtype, $raction, $rid )
{
    global $oSLiv;

    $rtype = addslashes($rtype);
    $raction = addslashes($raction);
    $rid = addslashes($rid);
    $oSLiv->kfdb->Execute( "INSERT INTO sliv_requests VALUES ('','$rtype','$rid','$raction','".time()."')" );
}
function pagination($tktt,$tktemp,$tktmpl,&$tkgtt,$page,$fieldname,$totalpages){

		tkntbl_snprintf($tkgtt,"searchPagination",1,MAX_RESULTS,"<a href='/".SEONAME."/%s-1' title='First Page' title='First Page'><<</a>",$fieldname);
		if($page!=1) tkntbl_snprintf($tkgtt,"searchPagination",2,MAX_RESULTS,"<a href='/".SEONAME."/%s-%s' title='Previous Page' title='Previous Page'><</a>",$fieldname,($page-1));

		for($c=($page<5?(($page+1)-$page):($page-4)); $c<$page; $c++){
			tkntbl_snprintf($tkgtt,"searchPagination",2,MAX_RESULTS,"<a href='/".SEONAME."/%s-%s'>%s</a>",$fieldname,$c,$c);
		}

		tkntbl_snprintf($tkgtt,"searchPagination",2,MAX_RESULTS,"<b>%s</b>",$page);

		for($c=($page+1); $c<($page+5) && $c<($totalpages+1) ; $c++){
			tkntbl_snprintf($tkgtt,"searchPagination",2,MAX_RESULTS,"<a href='/".SEONAME."/%s-%s'>%s</a>",$fieldname,$c,$c);
		}

		if($page!=$totalpages) tkntbl_snprintf($tkgtt,"searchPagination",2,MAX_RESULTS,"<a href='/".SEONAME."/%s-%s' title='Next Page' title='Next Page'>></a>",$fieldname,($page+1));

		tkntbl_snprintf($tkgtt,"searchPagination",2,MAX_RESULTS,"<a href='/".SEONAME."/%s-%s' title='Last Page' title='Last Page'>>></a></div>",$fieldname,$totalpages);

}
function pp_preapproval($id,$tmas,$returnURL, $cancelURL, $currencyCode, $startingDate, $endingDate, $maxTotalAmountOfAllPayments,$senderEmail, $maxNumberOfPayments, $paymentPeriod, $dateOfMonth, $dayOfWeek,$maxAmountPerPayment, $maxNumberOfPaymentsPerPeriod, $pinType){
	$resArray = CallPreapproval ($returnURL, $cancelURL, $currencyCode, $startingDate, $endingDate, $maxTotalAmountOfAllPayments,
								$senderEmail, $maxNumberOfPayments, $paymentPeriod, $dateOfMonth, $dayOfWeek,
								$maxAmountPerPayment, $maxNumberOfPaymentsPerPeriod, $pinType);

	$ack = strtoupper($resArray["responseEnvelope.ack"]);
	if($ack=="SUCCESS"){
		$cmd = "cmd=_ap-preapproval&preapprovalkey=" . urldecode($resArray["preapprovalKey"]);
		mas_qnr($tmas,"UPDATE accounts SET account_pakey = '%s' WHERE account_id = '%s'",$resArray["preapprovalKey"],$id);
		RedirectToPayPal ( $cmd );
	} else {
		print_r($resArray);
		return $resArray['error(0).message'];
	}
}

function pp_chainpayments($actionType,$cancelUrl,$returnUrl,$currencyCode,$senderEmail,$preapprovalKey,$feesPayer,$receiverEmailArray,$receiverPrimaryArray,$receiverAmountArray,$memo){


	$resArray = CallPay ($actionType, $cancelUrl, $returnUrl, $currencyCode, $receiverEmailArray,
						$receiverAmountArray, $receiverPrimaryArray, $receiverInvoiceIdArray,
						$feesPayer, $ipnNotificationUrl, $memo, $pin, $preapprovalKey,
						$reverseAllParallelPaymentsOnError, $senderEmail, $trackingId
			);


		$ack = strtoupper($resArray["responseEnvelope.ack"]);

		if($ack=="SUCCESS"){
			if ("" == $preapprovalKey){
				$cmd = "cmd=_ap-payment&paykey=" . urldecode($resArray["payKey"]);
				RedirectToPayPal ( $cmd );
			}  else {
				return;
			}

		} else {
			return $resArray['error(0).message'];
		}
}
function stripNum($num, $decplaces = 1) {
  $pos = strpos($num, '.');
  return substr($num, 0, $pos+1+$decplaces);
}
class SimpleImage {

   var $image;
   var $image_type;

   function load($filename) {
      $image_info = getimagesize($filename);
      $this->image_type = $image_info[2];
      if( $this->image_type == IMAGETYPE_JPEG ) {
         $this->image = imagecreatefromjpeg($filename);
      } elseif( $this->image_type == IMAGETYPE_GIF ) {
         $this->image = imagecreatefromgif($filename);
      } elseif( $this->image_type == IMAGETYPE_PNG ) {
         $this->image = imagecreatefrompng($filename);
      }
   }
   function save($filename, $image_type=IMAGETYPE_JPEG, $compression=75, $permissions=null) {
      if( $image_type == IMAGETYPE_JPEG ) {
         imagejpeg($this->image,$filename,$compression);
      } elseif( $image_type == IMAGETYPE_GIF ) {
         imagegif($this->image,$filename);
      } elseif( $image_type == IMAGETYPE_PNG ) {
         imagepng($this->image,$filename);
      }
      if( $permissions != null) {
         chmod($filename,$permissions);
      }
   }
   function output($image_type=IMAGETYPE_JPEG) {
      if( $image_type == IMAGETYPE_JPEG ) {
         imagejpeg($this->image);
      } elseif( $image_type == IMAGETYPE_GIF ) {
         imagegif($this->image);
      } elseif( $image_type == IMAGETYPE_PNG ) {
         imagepng($this->image);
      }
   }
   function getWidth() {
      return imagesx($this->image);
   }
   function getHeight() {
      return imagesy($this->image);
   }
   function resizeToHeight($height) {
      $ratio = $height / $this->getHeight();
      $width = $this->getWidth() * $ratio;
      $this->resize($width,$height);
   }
   function resizeToWidth($width) {
      $ratio = $width / $this->getWidth();
      $height = $this->getheight() * $ratio;
      $this->resize($width,$height);
   }
   function scale($scale) {
      $width = $this->getWidth() * $scale/100;
      $height = $this->getheight() * $scale/100;
      $this->resize($width,$height);
   }
   function resize($width,$height) {
      $new_image = imagecreatetruecolor($width, $height);
      imagecopyresampled($new_image, $this->image, 0, 0, 0, 0, $width, $height, $this->getWidth(), $this->getHeight());
      $this->image = $new_image;
   }
}
function cart($mas,$mas2,$dtt,$tmpl,$gtt,$tt,$temptt, $oSLiv)
{
    $s = "";
    $ok = false;

    if( !($uid = $oSLiv->oUser->GetUID()) )  goto done;

    header("Cache-Control: no-cache");
    mas_q1($mas2,$dtt,"select * from breadcrumbs where bc_search='Y' and (bc_accountid='$uid' OR bc_ip='%s' ) order by bc_tsadd desc", ttn($gtt,"REMOTE_ADDR"));

    mas_qb($mas,"SELECT * FROM carts,seeds WHERE cart_seedid=seed_id AND cart_userid='$uid' order by seed_userid,cart_quantity desc");

    tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Top"),OPENTAG,CLOSETAG,stdout,1,array($tt,$gtt));

    echo $oSLiv->oTmpl->ExpandTmpl( 'basketHeader', GetTokensRA(array($tt,$gtt,$dtt)))
        .$oSLiv->oTmpl->ExpandTmpl( 'basketTop', GetTokensRA(array($tt,$gtt)));
    $seedOpt = "";
    while( mas_qg($mas,$temptt) ) {
        for( $c=1; $c<=ttn($temptt,"seed_quantity"); $c++ ) {
            $sel = ttn($temptt,"cart_quantity") == $c ? "selected" : "";
            $seedOpt .= "<option $sel value='$c'>$c</option>";
        }
        // Get the screenname of the seller
        //mas_q1($mas2,$dtt,"SELECT * FROM accounts WHERE account_userid = '%s'",ttn($temptt,"seed_userid"));
        $raUD = $oSLiv->oUser->FetchUserMetadata(ttn($temptt,"seed_userid"));
        tkntbl_add($temptt,"user_screenname",@$raUD['user_screenname'],1);

        echo $oSLiv->oTmpl->ExpandTmpl( 'basketRow', array_merge(array('seedQ'=>$seedOpt), GetTokensRA(array($tt,$gtt,$temptt))));
        tkntbl_ftable($temptt);
    } mas_qe($mas);
    echo $oSLiv->oTmpl->ExpandTmpl( 'basketBottom', GetTokensRA(array($tt,$gtt)));
    tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"Bottom"),OPENTAG,CLOSETAG,stdout,1,array($tt,$gtt));

    $ok = true;

    done:

    return( array($ok, $s) );
}


class imgman
{
    private $oDocRepDB;

    function __construct( DocRepDB $oDocRepDB = null )
    {
        $this->oDocRepDB = $oDocRepDB;
    }

    function getDocRepSFile( $category, $k, $n = 1, $bTest = true, $bDefault = true )
    {
        $fname = $this->getfilename( $category, $k, $n );

        if( $bTest && !$this->oDocRepDB->GetDocFromName( $fname ) ) {
            $fname = $bDefault ? "seedliving/i/noImageAvailable.jpg" : "";
        }

        if( $fname ) $fname = SL2URL."/../d?n=$fname";

        return( $fname );
    }

    function getfilename( $category, $k, $n = 1 )
    {
        return( "seedliving/{$category}/{$k}_{$n}.jpg" );
    }

    /*
    function upload( $category, $fparm, $kImg )
    {
        $n = 1;
        foreach( $_FILES[$fparm]['name'] as $k => $v ) {
            if( !$v )  continue;  // ignore blank fields

            //$filename = str_replace( " ", "_", $v );
            $fNew = IMAGEROOT_UPLOAD.$this->getfilename($category, $kImg, $n, false);

            copy( $_FILES[$fparm]['tmp_name'][$k], $fNew );
            chmod( $fNew, 0606 );
            $n++;
        }
    }
    */

    function uploadSfile( $category, $fparm, $kImg )
    {
        $n = 1;
        foreach( $_FILES[$fparm]['name'] as $k => $v ) {
            if( !$v )  continue;  // ignore blank fields

            //$filename = str_replace( " ", "_", $v );
            $sfile = $this->getfilename( $category, $kImg, $n );

            if( ($oDoc = $this->oDocRepDB->GetDocFromName($sfile)) ) {
                // Replace
                $oDoc->ReplaceSFile( $_FILES[$fparm]['tmp_name'][$k], array() );
            } else {
                // Insert
                if( ($oDoc = new DocRepDoc_Insert($this->oDocRepDB)) ) {
                    $raParms = array('dr_permclass'=>1);
                    if( ($kDoc = $oDoc->InsertSFile( $fNew, $_FILES[$fparm]['tmp_name'][$k], $raParms )) ) {
                        $oDoc->SetVersionFlag( "", "PUB" );;
                    }
                }
            }

            $n++;
        }
    }
}

?>
