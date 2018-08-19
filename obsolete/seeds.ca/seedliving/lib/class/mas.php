<?
/**************************************************************
MySQL Access Structure Class Ver 1.0 - August 26,2007 -
-Provides a MySQL class
***************************************************************/
/*
mas
mas_init
mas_free_result
mas_initsock

*/
class mas {
	var $mas_querbuf;
	var $mas_errbuf;
	var $mas_row_cnt;
	var $mas_result;
	var $mas_dbsock;
	var $mas_host;
	var $mas_login;
	var $mas_pw;
	var $mas_port;
	var $mas_db;
	var $mas_col_cnt;
	var $mas_init;
	var $mas_fields;

	function mas(){
		$this->mas_querbuf = "";
		$this->mas_errbuf = mysql_error();
		$this->mas_row_cnt =0;
		$this->mas_result="";
		$this->mas_dbsock="";
		$this->mas_col_cnt = 0;
		$this->mas_init=1;
		$this->mas_fields = array();
	}
	function mas_init(){
		$this->mas_init=0;
		unset($this->mas_col_cnt,$this->mas_result,$this->mas_row_cnt,$this->mas_errbuf,$this->mas_querbuf,$this->mas_fields);
	}
	function mas_free_result(){
		@mysql_free_result(mas_result);
		//unset($this->mas_col_cnt,$this->mas_row_cnt,$this->mas_errbuf,$this->mas_querbuf,$this->mas_fields);
	}
	function mas_initsock($host, $login, $pw, $db, $port, $flags ){
		$this->mas_host = $host;
		$this->mas_login = $login;
		$this->mas_pw = $pw;
		$this->mas_port = $port;
		$this->mas_db = $db;

		if(!$this->mas_dbsock = mysql_connect($host,$login,$pw)){
			$this->mas_errbuf = mysql_error();
			return false;
		}else{
			if(!mysql_select_db($db,$this->mas_dbsock)){
				$this->mas_errbuf = mysql_error();
				return false;
			}else return true;
		}
	}
	function mas_query($querystr, $flags){
		$this->mas_querbuf = $querystr;
		//echo "\n".$this->mas_querbuf."\n";
		if(!$this->mas_result = mysql_query($this->mas_querbuf,$this->mas_dbsock)){
			$this->mas_errbuf = mysql_error();
			return false;
		}
		$this->mas_row_cnt = @mysql_num_rows($this->mas_result);
		$this->mas_col_cnt = @mysql_num_fields($this->mas_result);
		return true;
	}
	function mas_fetch_row($flag){
		return mysql_fetch_array($this->mas_result,$flag);
	}
	function mas_select_db($db){
		if(!mysql_select_db($db,$this->mas_dbsock)){
			$this->mas_errbuf = mysql_error();
			return false;
		}
		$this->mas_db = $db;
		return true;
	}
	function mas_fetch_fields(){
		for($c=0;$c<$this->mas_col_cnt;$c++){
			$this->mas_fields[$c] = mysql_field_name($this->mas_result,$c);
		}
	}
	function mas_insert_id(){
		return mysql_insert_id($this->mas_dbsock);
	}

}
?>