<?
/**************************************************************
Token Table Functions Ver 1.0 - August 26,2007 - 
-Provides a functions for linked list.
***************************************************************/
function tkntbl_init($t){
	for($c=0;$c<count($t);$c++){
		$t[$c] = new tkn;
	}
}
function tkntbl_add(&$token, $key, $value, $command){
	if($command==1) $token->tkn_add($key,$value);
	else $token->tkn_concat($key,$value);
}
function ttn(&$token,$key){
	return $token->tkn_get($key);
}
function tkntbl_rmv(&$token,$key){
	$token->tkn_remove($key);
}
function tkntbl_printf(&$token){
	echo "<pre>";
	$token->tkn_printf();
	echo "</pre>";
}
function tkntbl_print(&$token){
	$token->tkn_print();
}
function tkntbl_tok(&$token,$key,$str,$tk,$options){
	$token->tkn_tok($key,$str,$tk,$options);
}
function tkntbl_ftable(&$token){
	$token->tkn_ftable();
}
function tkntbl_rename(&$token,$key,$newname){
	$token->tkn_rename($key,$newname);
}
function tkntbl_load(&$token, $cfgfile, $comdel, $vardel, $command, $maxchunk){
	$token->tkn_load($cfgfile, $comdel, $vardel, $command, $maxchunk);
}
function tkntbl_dtable(&$token){
	$token->tkn_dtable();
}
function tkntbl_search(&$token,$key){
	return $token->tkn_get($key);
}
function tkntbl_searchva(&$token,$key){
	$i=0;
	for($c=2;$c<func_num_args();$c++){
		$tempa[$i] = func_get_arg($c);
		$i++;
	}
	return $token->tkn_searchva($key,$tempa);
}
function tkntbl_snprintf(&$token, $key, $command, $maxvalue, $valstr){
	$i=0;
	for($c=5;$c<func_num_args();$c++){
		$tempa[$i] = func_get_arg($c);
		$i++;
	}
	$temp = vsprintf($valstr,$tempa);
	
	if($command==1) $token->tkn_add($key,$temp);
	else $token->tkn_concat($key,$temp);
	
}
function tkntbl_searchall($key,$tokens){
	for($c=0;$c<count($tokens);$c++){
		if(tkntbl_search($tokens[$c],$key)) return tkntbl_search($tokens[$c],$key);
	}
	
	return false;
}
function tkntbl_ftime(&$token, $key, $timeformat, $ts, $command){
	$t = strftime($timeformat,strtotime($ts));
	if($command==1) $token->tkn_add($key,$t);
	else $token->tkn_concat($key,$t);
}
function tkntbl_xtime(&$token, $key, $date, $command){
	$m = substr($date, 0, 2);
	$d = substr($date, 3, 2);
	$y = substr($date, 6, 4);
	$t = mktime(0,0,0, $m, $d, $y);
	if($command==1) $token->tkn_add($key,$t);
	else $token->tkn_concat($key,$t);

}
function tkntbl_encstr(&$token, $controltt, $incmask, $excmask,$encoding){
	$c=0;
	
	if(!$incmask) $incmask = "*";
	else{
		$incArray = explode(",",$incmask);
	}
	
	if($excmask) $excArray = explode(",",$excmask);
	
	foreach($token->tkn as $key => $value){
		if(!$c){
			if(!strcmp($incmask,"*")){
				if(isset($excArray)){
					if(!in_array($key,$excArray))  $str = $key."=".$value;
				}else{
					$str = $key."=".$value;
				}
			}else{
				if(in_array($key,$incArray))  $str = $key."=".$value;
			}
		}else{
			if(!strcmp($incmask,"*")){
				if(isset($excArray)){
					if(!in_array($key,$excArray))  $str .= "&".$key."=".$value;
				}else{
					$str .= "&".$key."=".$value;
				}
			}else{
				if(in_array($key,$incArray))  $str .= "&".$key."=".$value;
			}
		}
		$c++;
	}
	
	if($encoding==1) return base64_encode($str);
	else if($encoding==1) return urlencode($str);
	else return $str;
}
function tkntbl_decstr(&$token,$encstr,$encoding){
	if($encoding==1) $str = base64_decode($encstr);
	else if($encoding==1) $str =  urldecode ($encstr);
	else $str = $encstr;
	
	$tempa = explode("&",$str);
	
	for($c=0;$c<count($tempa);$c++){
		$temp = explode("=",$tempa[$c]);
		$token->tkn_add($temp[0],$temp[1]);
	}
	
	return true;
}
function tkntbl_loadfile(&$token, $filename, $key){
	if(file_exists($filename)){
		if($contents = file_get_contents($filename)){
			$token->tkn_add($key,$contents);
			return true;
		}else return false;
	
	}else return false;
}

function tkntbl_trim(&$token, $key){
	return trim(ttn($token,$key));	
}
function tkntbl_triml(&$token, $key){
	return ltrim(ttn($token,$key));
}
function tkntbl_trimr(&$token, $key){
	return rtrim(ttn($token,$key));
}


?>