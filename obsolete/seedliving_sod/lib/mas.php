<?php
/**************************************************************
MySQL Access Structure Functions Ver 1.0 - August 26,2007 -
-Provides a MySQL functions
***************************************************************/
define("XPMYSQL_BLANKFIELD","1");
define("XPMYSQL_NOBLANKS","2");
define("XPMYSQL_NOISSUE","3");


/*
Generic record insert.
0 - if no option required wanted
XPMYSQL_NOISSUE - build query but do not issue it
*/

function mas_insert_id(&$m){
	return $m->mas_insert_id();
}
function mas_gri( &$mas, &$tt, &$ctt, $options, $tablename){
	$retcode = 0;
	$iserror = 0;

	$tempa = array();
	for($c=5;$c<func_num_args();$c++){
		$tempa[$i] = func_get_arg($c);
		$i++;
	}
	$tablename = vsprintf($tablename,$tempa);

	$sql = sprintf("insert into %s (",$tablename);

	$cnt = $pcnt = 0;

	while( ( $cnt < $tt->tkntbl_cnt ) && !$iserror ){

		if( $ctt->tkntbl_cnt && !tkntbl_search( $ctt, $tt->tkn_name($cnt) ) ){
			$cnt++;
			continue;
		}

	   if( $pcnt ){
	   		$sql .= ", ";
	   }

	   	$sql .= sprintf("`%s`", $tt->tkn_name($cnt) );
		$pcnt++;
		$cnt++;

	}
	$sql .= ") values (";

	$cnt = $pcnt = 0;

	while( ( $cnt < $tt->tkntbl_cnt ) && !$iserror ){

		if( $ctt->tkntbl_cnt && !tkntbl_search( $ctt,  $tt->tkn_name($cnt) ) ){
			$cnt++;
			continue;
		}

		if( $pcnt ){
	   		$sql .= ", ";
	   }

	   mas_real_escape_string($tbuf, $tt->tkn_val($cnt));
	   $sql .= sprintf( "'%s'", $tbuf );

	   $pcnt++;
	   $cnt++;


	}

	$sql .=")";

	unset($tbuf);


	if( $options == XPMYSQL_NOISSUE ) {
		 $retcode = 1;
		 $mas->mas_querbuf = $sql;
	} else {

		if($mas->mas_query($sql,0)) $retcode =  $mas->mas_insert_id();
	}


	return $retcode;

}
/*
Generic record update.
	0: update only fields existing in control token table
	1XPMYSQL_BLANKFIELD: blank any fields that exist in control token table but not in primary
	2XPMYSQL_NOBLANKS: ignore any tt tokens with empty values
	3XPMYSQL_NOISSUE - build query but do not issue it

*/
function mas_gru( &$mas, &$tt, &$ctt, $prisqlfieldname, $prisqlid, $options, $tablename){
	$retcode = 0;
	$iserror = 0;
	$i=0;

	$tempa = array();
	for($c=7;$c<func_num_args();$c++){
		$tempa[$i] = func_get_arg($c);
		$i++;
	}
	$tablename = vsprintf($tablename,$tempa);

	$sql = sprintf("update %s set ",$tablename);

	if( !$ctt->tkntbl_cnt ){
		$cnt=0;
		while( ( $cnt < $tt->tkntbl_cnt ) && !$iserror ){
			if( ($options == XPMYSQL_NOBLANKS) && !$tt->tkn_val($cnt) ){
				$cnt++;
				continue;
			}

				if( $cnt ) $sql .= ", ";
				mas_real_escape_string($tbuf, $tt->tkn_val($cnt));

				$sql .= sprintf("%s='%s'", $tt->tkn_name($cnt), $tbuf);

				$cnt++;
		}

	}else{
		$cnt = $pcnt = 0;
		while( ( $cnt < $ctt->tkntbl_cnt ) && !$iserror ){

			$ptr = tkntbl_search( $tt, $ctt->tkn_name($cnt) );

			if( $ptr || ( $options == XPMYSQL_BLANKFIELD )){
				if( strcmp( $ctt->tkn_name($cnt), $prisqlfieldname ) ){
					if( !$ptr ){
						if( $pcnt ) $sql .= ", ";
						$sql .= sprintf( "%s=NULL", $ctt->tkn_name($cnt) );
					}else{
						if( ($options != XPMYSQL_NOBLANKS) && $ptr ){
							if( $pcnt ) $sql .= ", ";
							mas_real_escape_string($tbuf, $ptr);

							$sql .= sprintf("%s='%s'", $ctt->tkn_name($cnt), $tbuf );
						}

					}
					$pcnt++;
				}
			}
			$cnt++;
		}
	}
	$sql .= sprintf(" where %s='%s'",$prisqlfieldname, $prisqlid );

	unset($tbuf);

	/* perform the query */
	if( $options == XPMYSQL_NOISSUE ){
		 $retcode = -1;
		 $mas->mas_querbuf = $sql;
	} else {

		if($mas->mas_query($sql,0)) $retcode = -1;
	}


	return $retcode;

}
function mas_init(&$tmas,$tokens){
	for($c=0;$c<count($tokens);$c++){
		$tokens[$c] = $mas;
	}

}
function mas_real_escape_string(&$tbuf,$value){
	$tbuf = addslashes($value);
}
function mas_initlib($m){
	for($c=0;$c<count($m);$c++){
		$m[$c] = new mas;
	}
}
function mas_initsock(&$m, $host, $login, $pw, $db, $port, $flags ){
	if(!$m->mas_initsock($host,$login,$pw, $db, $port, $flags )) {
		return false;
	}else return true;
}
function mas_q1(&$m,&$token,$querystr){
    $tempa = array();
    $i=0;
	for($c=3;$c<func_num_args();$c++){
		$tempa[$i] = func_get_arg($c);
		$i++;
	}
	$querystr = vsprintf($querystr,$tempa);

	// the kfdb code below does the same thing but mas_query sets some properties that are used by the caller
	$m->mas_free_result();
	if(!$m->mas_query($querystr,0)) return false;
/*
	else{
		if($m->mas_row_cnt){
			$tempa = $m->mas_fetch_row(MYSQL_ASSOC);
			foreach($tempa as $key => $value){
				tkntbl_add($token, $key, $value, 1);
			}
		}
	}
*/

	global $oSLiv;

	//$oSLiv->kfdb->SetDebug(2);
	if( !($ra = $oSLiv->kfdb->QueryRA( $querystr )) )  return(false);
	//var_dump($querystr,$ra);
	foreach( $ra as $k => $v ){
	    tkntbl_add( $token, $k, $v, 1);
	}

	return true;
}
function mas_query(&$m,$querystr){
	$m->mas_free_result();
	if(!$m->mas_query($querystr,0)) return false;
	return true;
}
function mas_select_db(&$m,$db){
	if(!$m->mas_select_db($db)) return false;
	else return true;
}
function mas_num_fields(&$m){
	return $m->mas_col_cnt;
}
function mas_fetch_fields(&$m,&$token){
	$m->mas_fetch_fields();
	foreach($m->mas_fields as $key => $value){
		tkntbl_add($token, $key, $value, 1);
	}
}
function mas_qi(&$m, &$intval, $querystr){
	$tempa = array();
    $i=0;
	for($c=3;$c<func_num_args();$c++){
		$tempa[$i] = func_get_arg($c);
		$i++;
	}
	$querystr = vsprintf($querystr,$tempa);
	$m->mas_free_result();
	if(!$m->mas_query($querystr,0)) return false;
	else{
		$intval = $m->mas_row_cnt;
//var_dump($querystr,$intval);
		return true;
	}
	return false;
}
function mas_qnr(&$m, $querystr){
   	$tempa = array();
	$i=0;
	for($c=2;$c<func_num_args();$c++){
		$tempa[$i] = func_get_arg($c);
		$i++;
	}

	$querystr = @vsprintf($querystr,$tempa);
	$m->mas_free_result();

	if(!$m->mas_query($querystr,0)) return false;
	/*Removed Sept 30 else{
		if($m->mas_row_cnt) return true;
		else return false;
	}*/

	return true;
}
function mas_qe(&$m){
	$m->mas_free_result();
}
function mas_qg(&$m,&$token){
	if($m->mas_result){
		if($tempa = $m->mas_fetch_row(MYSQL_ASSOC)){
			foreach($tempa as $key => $value){
				tkntbl_add($token, $key, $value, 1);
			}
			return true;
		} else return false;
	} else return false;
}
function mas_Fetch( $kfdb, $dbc, $tt )
{
    if( $dbc && ($ra = $kfdb->CursorFetch($dbc, KFDB_RESULT_ASSOC)) ) {
        foreach( $ra as $k => $v ) {
            tkntbl_add( $tt, $k, $v, 1 );
        }
        return true;
    }
    return false;
}
function mas_qb(&$m, $querystr){
	$tempa = array();
    $i=0;
	for($c=2;$c<func_num_args();$c++){
		$tempa[$i] = func_get_arg($c);
		$i++;
	}
	$querystr = vsprintf($querystr,$tempa);
	$m->mas_free_result();

	if(!$m->mas_query($querystr,0)) return false;
	else return true;

}
function mas_lts(&$m,&$token,$tablename){
	$tempa = array();
    $i=0;
	for($c=3;$c<func_num_args();$c++){
		$tempa[$i] = func_get_arg($c);
		$i++;
	}
	$tablename = vsprintf($tablename,$tempa);
	$m->mas_free_result();

	if(!$m->mas_query("DESCRIBE ".$tablename,0)) return false;
	else{
		if($m->mas_row_cnt){
			while($tempa = $m->mas_fetch_row(MYSQL_BOTH)){
				tkntbl_add($token, $tempa[0], $tempa[1], 1);
			}

			return true;
		}
	}
}
function mas_q1ren(&$m, &$token, $oldbase, $newbase, $querystr){
	$tempa = array();
    $i=0;
	for($c=5;$c<func_num_args();$c++){
		$tempa[$i] = func_get_arg($c);
		$i++;
	}
	$querystr = vsprintf($querystr,$tempa);
	$m->mas_free_result();
	if(!$m->mas_query($querystr,0)) return false;
	else{
		if($m->mas_row_cnt){
			$tempa = $m->mas_fetch_row(MYSQL_ASSOC);
			foreach($tempa as $key => $value){
				if(strstr($key,$oldbase)){
					if(isset($newbase)) tkntbl_add($token, str_replace($oldbase,$newbase,$key), $value, 1);
					else tkntbl_add($token, str_replace($oldbase,"",$key), $value, 1);
				}
				else return false;
			}
		}
	}
	return true;
}
function mas_make_indices(&$m, $table, $fieldlist, $options ){
	$tempa = explode(",",$fieldlist);
	$m->mas_free_result();
	for($c=0;$c<count($tempa);$c++){
		if(!$m->mas_query("CREATE INDEX ".$tempa[$c]." ON ".$table." (".$tempa[$c].")",0)) return false;
	}
}
function mas_ctt(&$m, $table, &$fld, &$ndx ){


}
function mas_dnit(&$m){
	unset($m);
}
?>