<?
/**************************************************************
 Template Processing Functions Ver 1.0 - August 26,2007 -
-Provides a functions for template processing
***************************************************************/
define("APACHE_INCL","<!--#include");
define("APACHE_INCR","-->");
function tmplt_mail(&$token,&$tmpltt,$chunk){

	//tkntbl_snprintf($token,"mailcommand",1,MAX_RESULTS,"/usr/sbin/sendmail -t -f%s",ttn($token,"email_from"));
	//$pmail = popen(ttn($token,"mailcommand"),"w");

	tkntbl_snprintf($token,"email_headers",1,MAX_RESULTS,"To:%s\n",ttn($token,"email_to"));
	tkntbl_snprintf($token,"email_headers",2,MAX_RESULTS,"From:%s\n",ttn($token,"email_from"));
	//tkntbl_snprintf($token,"email_headers",2,MAX_RESULTS,"Subject:%s\n",ttn($token,"email_subject"));
	tkntbl_add($token, "email_headers", "MIME-Version: 1.0\n", 2);
	tkntbl_add($token, "email_headers", "Content-type: text/html; charset=iso-8859-1\n", 2);



	//if($pmail){
		//if(!tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpltt,$chunk),OPENTAG,CLOSETAG,$pmail,1,array($token))) criterr("<br>Chunk Error : %s",$chunk);
	//} else  criterr("Fatal Mail Error");
	tmplt_proc_ex_tt(TEMPLROOT,tkntbl_search($tmpltt,$chunk),OPENTAG,CLOSETAG,1,stdout,$token,"message",array(&$token));
	mail(NULL,ttn($token,"email_subject"),ttn($token,"message"),ttn($token,"email_headers"));

	//fputs($pmail,"\n\n.\n\n");
	//pclose($pmail);

}
function tmplt_proc_ex_tt($srcpath,$tempptr,$tokleft,$tokright,$comm,$type,&$token,$chunkname,$tokens)
{
    /* init temp token */
    tkntbl_init(array(&$tempa));

	/*put tokens into one array*/
	foreach($tokens as $t => $v){
		foreach($tokens[$t]->tkn as $key => $value){
			$tempa->tkn[$key] = $value;
		}
	}

	/* init content */
	if(true) { //$type) {
	    $content = tmplt_proc_ssi($tempptr,$tempa);
	} else {
	    $content = tmplt_proc_ssi(@file_get_contents($srcpath.$tempptr),$tempa);
	}

	do{
	  $spos = strpos($content,$tokleft);
	  if($spos !== false){
	  	 $epos = strpos($content,$tokright);
		 if($epos !== false){
		 	$logic = substr($content,$spos,($epos+strlen($tokright))-$spos);
		 		if(strstr($logic,"#if")){
					$lpos = strpos($content,$tokleft."#endif".$tokright);
					$logiccmp = substr($content,$spos,($lpos+strlen($tokleft."#endif".$tokright))-$spos);
					$logicbak = $logiccmp;
					/*extract condition and true and false statements*/
					$condition = trim(str_replace(array($tokleft."#if",$tokright),"",$logic));
					if(strstr($logiccmp,"#else")){
						$temp = explode($tokleft."#else".$tokright,$logiccmp);
						$true = trim(str_replace(array($tokleft."#if",$condition.$tokright),"",$temp[0]));
						$false = trim(str_replace(array($tokleft."#endif".$tokright),"",$temp[1]));
					}else{
						/* just extract true statement */
						$true = trim(str_replace(array($tokleft."#if",$condition.$tokright,$tokleft."#endif".$tokright),"",$logiccmp));
						$false="";
					}
					/* test condition */
					if(strstr($condition,"=")){
						$temp2 = explode("=",$condition);
						if(!strcmp(ttn($tempa,$temp2[0]),str_replace("'","",$temp2[1]))) $rvalue = $true;
						else $rvalue = $false;
					}elseif(strstr($condition,"!")){
						$temp2 = explode("!",$condition);
						if(strcmp(ttn($tempa,$temp2[0]),str_replace("'","",$temp2[1]))) $rvalue = $true;
						else $rvalue = $false;
					}else{
						if(ttn($tempa,$condition)) $rvalue = $true;
						else $rvalue = $false;
					}
				}elseif(strstr($logic,"#for")){
					/* loop future support */
				}elseif(strstr($logic,"#select")){
					/* control box future support */
				}elseif(strstr($logic,"#query")){
					/* control box future support */
				}elseif(strstr($logic,"#include")){
					$lpos = strpos($content,$tokleft."#endinclude".$tokright);
					$logiccmp = substr($content,$spos,($lpos+strlen($tokleft."#endinclude".$tokright))-$spos);
					$logicbak = $logiccmp;
					$condition = trim(str_replace(array($tokleft."#include",$tokright,$tokleft."#endinclude"),"",$logiccmp));
					$rvalue =  file_get_contents("http://".$_SERVER['HTTP_X_FORWARDED_HOST'].$condition);
					//echo "http://".$_SERVER['HTTP_X_FORWARDED_HOST'].$condition;
					//foreach($tempa->tkn as $key => $value){
						//$rvalue = str_replace($tokleft.$key.$tokright,$value,$rvalue);
				       // }
				}else{
					$logicbak = $logic;
					$logic = str_replace(array($tokleft,$tokright),"",$logic);
					if(strstr($logic,":")){
						$temp = explode(":",$logic);
						if(ttn($tempa,$temp[0])){
						   switch($temp[1]){
						   	case "$":
						   	    $n = ttn($tempa,$temp[0]);
							 	$rvalue = is_numeric( $n ) ? number_format($n,2) : $n;  // because some people put "$2.00 or trade" which makes a number_format warning
							 break;

						   	 case "N":
							 	$rvalue = ttn($tempa,$temp[0])."&nbsp;";
							 break;

							 case "NC":
							 	$rvalue = ttn($tempa,$temp[0]).":&nbsp;";
							 break;

							 case "D":
								$rvalue = date("Y-m-d",ttn($tempa,$temp[0]));
							 break;

							 case "DF":
								$rvalue = date("F j, Y",ttn($tempa,$temp[0]));
							 break;

							 case "$$0":
								$rvalue = "$".number_format(ttn($tempa,$temp[0]),0);
							 break;

							 case "$$":
							  $rvalue = "$".number_format(ttn($tempa,$temp[0]));
			                                 break;

							 case "F":
								$rvalue = number_format(ttn($tempa,$temp[0]),0);
							 break;

							 case "UC":
							 	$rvalue = ucwords(strtolower(ttn($tempa,$temp[0])));
							 break;

							 case "L":
							 	$rvalue = strtolower(ttn($tempa,$temp[0]));
							 break;

							 case "U":
							 	$rvalue = strtoupper(strtolower(ttn($tempa,$temp[0])));
							 break;

							 case "NP":
							 	$rvalue = str_replace("#","",ttn($tempa,$temp[0]));
							 break;

							 case "SUB":
							  $rvalue = substr(ttn($tempa,$temp[0]),strlen(ttn($tempa,$temp[0]))-1,1);
							 break;

							 case "CON":
							  $rvalue = substr(ttn($tempa,$temp[0]),0,350);
							 break;

							 case "PER":
							  $rvalue = (ttn($tempa,$temp[0])*100);
							 break;

						   }
						}else{
							switch($temp[1]){
						   	 case "$":
							 	$rvalue = "&nbsp;";
							 break;

						   	 case "N":
							 	$rvalue = "&nbsp;";
							 break;

							 case "NC":
							 	$rvalue = ":&nbsp;";
							 break;

							 case "L":
							 	$rvalue = strtolower(ttn($tempa,$temp[0]));
							 break;

							 case "NA":
							  $rvalue = "N/A";
							 break;

							 default:
							 	$rvalue = "";
							 break;
							}
						}
					}else{
						if(ttn($tempa,$logic)){
							$rvalue = ttn($tempa,$logic);
						}else{
							$rvalue = "";
						}
					}
				}
			 $content = str_replace($logicbak,$rvalue,$content);
		 }
	  }

	}
	while($spos !== false);


	/*output template*/
	if($comm==1) $token->tkn_add($chunkname,$content);
	else $token->tkn_concat($chunkname,$content);


	return true;

}
function tmplt_proc_ex($srcpath,$tempptr,$tokleft,$tokright,$out,$type,$tokens){
    /* init temp token */
	tkntbl_init(array(&$tempa));

	/*put tokens into one array*/
	foreach($tokens as $t => $v){
		foreach($tokens[$t]->tkn as $key => $value){
			$tempa->tkn[$key] = $value;
		}
	}

	/* init content */
	if($type) $content = tmplt_proc_ssi($tempptr,$tempa);
	else $content = tmplt_proc_ssi(@file_get_contents($srcpath.$tempptr),$tempa);

	do{
	  $spos = strpos($content,$tokleft);
	  if($spos !== false){
	  	 $epos = strpos($content,$tokright);
		 if($epos !== false){
		 	$logic = substr($content,$spos,($epos+strlen($tokright))-$spos);
		 		if(strstr($logic,"#if")){
					$lpos = strpos($content,$tokleft."#endif".$tokright);
					$logiccmp = substr($content,$spos,($lpos+strlen($tokleft."#endif".$tokright))-$spos);
					$logicbak = $logiccmp;
					/*extract condition and true and false statements*/
					$condition = trim(str_replace(array($tokleft."#if",$tokright),"",$logic));
					if(strstr($logiccmp,"#else")){
						$temp = explode($tokleft."#else".$tokright,$logiccmp);
						$true = trim(str_replace(array($tokleft."#if",$condition.$tokright),"",$temp[0]));
						$false = trim(str_replace(array($tokleft."#endif".$tokright),"",$temp[1]));
					}else{
						/* just extract true statement */
						$true = trim(str_replace(array($tokleft."#if",$condition.$tokright,$tokleft."#endif".$tokright),"",$logiccmp));
						$false="";
					}
					/* test condition */
					if(strstr($condition,"=")){
						$temp2 = explode("=",$condition);
						if(!strcmp(ttn($tempa,$temp2[0]),str_replace("'","",$temp2[1]))) $rvalue = $true;
						else $rvalue = $false;
					}elseif(strstr($condition,"!")){
						$temp2 = explode("!",$condition);
						if(strcmp(ttn($tempa,$temp2[0]),str_replace("'","",$temp2[1]))) $rvalue = $true;
						else $rvalue = $false;
					}else{
						if(ttn($tempa,$condition)) $rvalue = $true;
						else $rvalue = $false;
					}
				}elseif(strstr($logic,"#for")){
					/* loop future support */
				}elseif(strstr($logic,"#select")){
					/* control box future support */
				}elseif(strstr($logic,"#query")){
					/* control box future support */
				}elseif(strstr($logic,"#include")){
					$lpos = strpos($content,$tokleft."#endinclude".$tokright);
					$logiccmp = substr($content,$spos,($lpos+strlen($tokleft."#endinclude".$tokright))-$spos);
					$logicbak = $logiccmp;
					$condition = trim(str_replace(array($tokleft."#include",$tokright,$tokleft."#endinclude"),"",$logiccmp));
					$rvalue = file_get_contents("http://".$_SERVER['HTTP_X_FORWARDED_HOST'].$condition);
					//echo "http://".$_SERVER['HTTP_X_FORWARDED_HOST'].$condition;
					//foreach($tempa->tkn as $key => $value){
						//echo $tokleft.$key.$tokright."<br>";
					   // $rvalue = str_replace($tokleft.$key.$tokright,$value,$rvalue);
				       //}
				}else{
					$logicbak = $logic;
					$logic = str_replace(array($tokleft,$tokright),"",$logic);
					if(strstr($logic,":")){
						$temp = explode(":",$logic);
						if(ttn($tempa,$temp[0])){
                            switch($temp[1]){
                                case "$":
                                    $n = ttn($tempa,$temp[0]);
                                    $rvalue = is_numeric($n) ? number_format($n,2) : $n;  // number_format throws a Notice if non-numeric
                                    break;

						   	 case "N":
							 	$rvalue = ttn($tempa,$temp[0])."&nbsp;";
							 break;

							 case "NC":
							 	$rvalue = ttn($tempa,$temp[0]).":&nbsp;";
							 break;

							 case "D":
								$rvalue = date("Y-m-d", ttn($tempa,$temp[0]));
							 break;

							 case "DF":
								$rvalue = date("F j, Y",ttn($tempa,$temp[0]));
							 break;

							 case "$$0":
								$rvalue = "$".number_format(ttn($tempa,$temp[0]),0);
							 break;

							 case "$$":
							  $rvalue = "$".number_format(ttn($tempa,$temp[0]));
							 break;

							 case "F":
								$rvalue = number_format(ttn($tempa,$temp[0]),0);
							 break;

							 case "UC":
				                             $rvalue = ucwords(strtolower(ttn($tempa,$temp[0])));
							 break;

							 case "L":
							 	$rvalue = strtolower(ttn($tempa,$temp[0]));
								 break;
								 case "U":
							 	$rvalue = strtoupper(strtolower(ttn($tempa,$temp[0])));

							 break;

							 case "NP":
                                                            $rvalue = str_replace("#","",ttn($tempa,$temp[0]));
						         break;

						         case "SUB":
                                                          $rvalue = substr(ttn($tempa,$temp[0]),strlen(ttn($tempa,$temp[0]))-1,1);
                                                         break;
								case "CON":
							  $rvalue = substr(ttn($tempa,$temp[0]),0,200);
							 break;
							 case "PER":
							  $rvalue = (ttn($tempa,$temp[0])*100);
							 break;
						   }
						}else{
							switch($temp[1]){
						   	 case "$":
							 	$rvalue = "&nbsp;";
							 break;

						   	 case "N":
							 	$rvalue = "&nbsp;";
							 break;

							 case "NC":
							 	$rvalue = ":&nbsp;";
							 break;

							 case "L":
							 	$rvalue = strtolower(ttn($tempa,$temp[0]));
							 break;

							 case "NA":
							  $rvalue = "N/A";
							 break;

							 default:
							 	$rvalue = "";
							 break;
							}
						}
					}else{
						if(ttn($tempa,$logic)){
							$rvalue = ttn($tempa,$logic);
						}else{
							$rvalue = "";
						}
					}
				}
			 $content = str_replace($logicbak,$rvalue,$content);
		 }
	  }

	}
	while($spos !== false);

	/*output template*/
	//if(is_resource($out)) fputs($out,$content);
	//else
	    echo $content;

	return true;

}
function tmplt_enumtkns($chunk,$tokleft,$tokright){
	$content = strip_tags($chunk);

	$c=0;

	while($lpos = strpos($content,$tokleft)){
		//find pos of the right token
		$rpos = strpos($content,$tokright);

		//replace the token string
 		$temp[$c]= substr($content,$lpos,(($rpos+strlen($tokright)) - $lpos));
		$content = str_replace(substr($content,$lpos,(($rpos+strlen($tokright)) - $lpos)),"",$content);
		$c++;
	}

	for($c=0;$c<count($temp);$c++){
		$t = str_replace($tokright,"",str_replace($tokleft,"",$temp[$c]));
		$t_ = explode(":",$t);
		$temp[$c] = $t_[0];
	}

	return $temp;

}
function tmplt_load(&$token, $tempname, $temptoken){
	if(file_get_contents($tempname)){
		if($content = file_get_contents($tempname)){
			do {
				$spos = strpos($content,$temptoken);

				if($spos !== false){
					$npos = strpos(substr($content,($spos+strlen($temptoken))),$temptoken);
					if($npos !== false) $chunk = substr($content,$spos,($npos+strlen($temptoken)));
					else $chunk = $content;
					$bk = $chunk;
					$temp = explode("\n",$chunk);
					tkntbl_add($token,trim(str_replace($temptoken,"",$temp[0])),"",1);
					for($c=1;$c<count($temp);$c++){
						tkntbl_add($token,trim(str_replace($temptoken,"",$temp[0])),$temp[$c]."\n",2);
					}

					$content = str_replace($bk ,"",$content);
				}


			} while( $spos !== false );
		} else return false;
	} else return false;

	return true;
}
function tmplt_create($srcpath,$tempname,$temptoken){
	$c=0;
	//get file list
	$d = dir($srcpath);
	while (false !== ($entry = $d->read())) {
  		 if(strstr($entry,".htm") || strstr($entry,".html")){
		 	$filelist[$c] = $entry;
			$c++;
		 }
	}
	//open new file
	if(!$fp = fopen($srcpath.$tempname,"w")) die("Unable to create ".$srcpath.$tempname);
	for($c=0;$c<count($filelist);$c++){
		//output header
		fwrite($fp,$temptoken." ".str_replace(array(".html",".htm"),"",$filelist[$c])."\n");
		//output content
		fwrite($fp,file_get_contents($srcpath.$filelist[$c])."\n\n");
	}
	fclose($fp);

}
function tmplt_proc_ssi($out,$token)
{
	$found=1;
	$first=0;
	$last=0;

	if($out){
		while($found){
			//if($_SERVER['TERM']) echo "hello\n";

			$first = strpos($out,APACHE_INCL);
			$last = strpos($out,APACHE_INCR,$first);

			if(!$first && !$last) $found=0;
			if(!strlen($first)) $found=0;

			if($found){
					$ssi = substr($out,$first,(($last+strlen(APACHE_INCR))-$first));

					$filename = trim(str_replace(array("virtual=\"","\" "),"",substr($out,($first+strlen(APACHE_INCL)),($last-($first+strlen(APACHE_INCL))))));
					//if($_SERVER['TERM']) echo $filename."\n";

					foreach($token->tkn as $key => $value){
						//echo $key."\n";
						$filename = str_replace("[IBG]".$key."[/IBG]",$value,$filename);
					}
					//if($_SERVER['TERM']) echo $filename."\n";

					if(!$temp = file_get_contents($_SERVER['DOCUMENT_ROOT'].$filename)) $temp = file_get_contents("http://".$_SERVER['HTTP_X_FORWARDED_HOST'].$filename);
					$out = str_replace($ssi,$temp,$out);

			}
		}
	}

	return $out;
}
?>