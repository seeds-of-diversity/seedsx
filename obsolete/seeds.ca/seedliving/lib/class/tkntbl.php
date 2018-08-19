<?
/**************************************************************
Token Table Class Ver 1.0 - August 26,2007 -
-Provides a class bass for linked list.
***************************************************************/
class tkn{
	var $tkntbl_cnt;
	var $tkn;

	function tkn(){
		$this->tkntbl_cnt = 0;
		$this->tkn = array();
	}
	function tkn_lnbrk($b){
		$this->lnbrk = $b;
	}
	function tkn_name($pos){
		if ( ($pos < 0) || ( $pos >= $this->tkntbl_cnt ) )
         return "NULL";

		reset($this->tkn);

		for($i = 0;$i < $pos; $i++) next($this->tkn);

		return key($this->tkn);
	}
	function tkn_val($pos){
		if ( ($pos < 0) || ( $pos >= $this->tkntbl_cnt ) )
         return "NULL";

		reset($this->tkn);

		for($i = 0;$i < $pos; $i++) next($this->tkn);

		return current($this->tkn);
	}
	function tkn_add($key,$value){
		$this->tkn[$key] = $value;
		$this->tkntbl_cnt++;
	}
	function tkn_concat($key,$value){
		@$this->tkn[$key] .= $value;
	}
	function tkn_get($key){
		return @$this->tkn[$key];
	}
	function tkn_remove($key){
		unset($this->tkn[$key]);
		$this->tkntbl_cnt--;
	}
	function tkn_tok($key,$str,$tk,$options){
		$tkn_temp = explode($tk,$str);
		$tkn_temp2 = explode($tk,$key);
         for($c=0;$c<count($tkn_temp2);$c++){
            $this->tkn[$tkn_temp2[$c]] = $tkn_temp[$c];
			$this->tkntbl_cnt++;
		}
	}
	function tkn_ftable(){
		unset($this->tkn);
		$this->tkntbl_cnt = 0;

	}
	function tkn_rename($key,$newname){
		if(isset($this->tkn)){
			foreach($this->tkn as $keys => $value){
				if(!strcmp($key,$keys)){
					$temp[$newname] = $value;
				}else{
					$temp[$keys] = $value;
				}
			}
			$this->tkn = $temp;
		}
	}
	function tkn_load($cfgfile, $comdel, $vardel, $command, $maxchunk){
		if(file_exists($cfgfile)){
			$content = file($cfgfile);
			for($c=0;$c<count($content);$c++){
				if(strlen($content[$c])>2){
					if(strcmp($content[$c][0],$comdel)){
						$temp = explode($vardel,$content[$c]);
						if(strlen($temp[0])){
							if($command == 1) $this->tkn_add($temp[0],$temp[1]);
							else $this->tkn_concat($temp[0],$temp[1]);
							$this->tkntbl_cnt++;
						}
					}
				}
			}
		}else return false;

		return true;
	}
	function tkn_searchva($key,$agrv){
		$temp = vsprintf($key,$agrv);
		return $this->tkn_get($temp);
	}
	function tkn_printf(){
	   if(isset($this->tkn)){
		 foreach($this->tkn as $key => $value){
			echo "[".$key."]:".$value."\n";
		 }
	  }
	}
	function tkn_print(){
	  if(isset($this->tkn)){
		foreach($this->tkn as $key => $value){
			echo "[".$key."]:".$value."<br>";
		}
	   }
	}
	function tkn_dtable(){
		unset($this->tkn);
		unset($this->tkntbl_cnt);
		unset($this);
	}
}
?>