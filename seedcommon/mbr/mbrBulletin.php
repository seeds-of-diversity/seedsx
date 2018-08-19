<?php

/* Manage eBulletin subscriptions
 */

class MbrBulletin
{
    private $kfrelBull = null;
    private $uid;

    function __construct( KeyFrameDB $kfdb, $uid = 0 )
    {
        $this->uid = $uid;
        $this->initKfrel( $kfdb, $uid );
    }

    function GetKFR( $id )
    /*********************
       Get a bull_list kfr for either
           $id == _key
           $id == email
     */
    {
        if( !$id )  return( null );

        $kfr = is_numeric($id) ? $this->kfrelBull->GetRecordFromDBKey( $id )
                               : $this->kfrelBull->GetRecordFromDB( "email='".addslashes($id)."'" );
        return( $kfr );
    }

    function AddSubscriber( $email, $realname, $lang, $sComment )
    {
// use the kfrel
        if( ($k = $this->kfrelBull->kfdb->Query1( "SELECT _key FROM seeds.bull_list WHERE email='".addslashes($email)."'" )) ) {
            $eRet = "dup";
        } else {
            $id = $this->kfrelBull->kfdb->InsertAutoInc( "INSERT INTO seeds.bull_list (_key,name,email,lang,comment,hash,status,ts1)"
                                                        ." VALUES (NULL,'".addslashes($realname)."','".addslashes($email)."',"
                                                        ."'".addslashes($lang)."','".addslashes($sComment)."',"
                                                        ."'Added by {$this->uid}',1,NOW())" );
            $eRet = $id ? "ok" : false;
        }
        return( $eRet );
    }

    function RemoveSubscriber( $email )
    {
        if( !($k = $this->kfrelBull->kfdb->Query1( "SELECT _key FROM seeds.bull_list WHERE email='".addslashes($email)."'" )) ) {
            $eRet = 'notfound';
        } else {
            $eRet = $this->kfrelBull->kfdb->Execute( "DELETE FROM seeds.bull_list WHERE _key='$k'" )
                        ? 'ok' : 'err';
        }
        return( $eRet );
    }

    private function initKfrel( $kfdb )
    {
        $def = array( "Tables" => array( array( "Table" => "seeds.bull_list",
                                                "Alias" => "BL",
                                                "Type"  => "Base",
                                                "Fields" => array( array("col"=>"email",   "type"=>"S"),
                                                                   array("col"=>"name",    "type"=>"S"),
                                                                   array("col"=>"hash",    "type"=>"S"),
                                                                   array("col"=>"status",  "type"=>"I"),
                                                                   array("col"=>"ts0",     "type"=>"S"),
                                                                   array("col"=>"ts1",     "type"=>"S"),
                                                                   array("col"=>"ts2",     "type"=>"S"),
                                                                   array("col"=>"lang",    "type"=>"S"),
                                                                   array("col"=>"comment", "type"=>"S"),
                                                ))));

        $this->kfrelBull = new KeyFrameRelation( $kfdb, $def, $this->uid );
        $this->kfrelBull->SetLogFile( SITE_LOG_ROOT."bull_list.log" );
    }
}

?>
