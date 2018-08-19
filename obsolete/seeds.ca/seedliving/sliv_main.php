<?php

define( "SEEDLIVING_ROOT", "./" );
include_once( SEEDLIVING_ROOT."sl_defs.php" );
include_once( SEEDLIVING_ROOT."sliv_init.php" );
include_once( SEEDLIVING_ROOT."lib/sliv_lib.php" );

$oSLiv = new SeedLiving();

class SeedLiving
{
    public $kfdb;
    public $bFeeEnabled = false;



    function __construct()
    {
        // from SiteKFDB - not sure if adding other constants (e.g. SiteKFDB_DB_seedliving) to the case statements will break that code if constants not defined
        if( !($this->kfdb = new KeyFrameDB( "localhost", SiteKFDB_USERID_seedliving, SiteKFDB_PASSWORD_seedliving )) ||
            !($this->kfdb->Connect( SiteKFDB_DB_seedliving )) )
        {
            die( "Cannot connect to SeedLiving database" );
        }
    }


    function DrawSeedsSplash()
    {
        global $tt, $gtt, $mas, $tmpl, $temptt;
		$c=0;
		if(!strcmp(ttn($gtt,"fee_enabled"),"N")){
			mas_qb($mas,"SELECT * FROM seeds,accounts WHERE seed_userid = account_userid AND seed_quantity > 0 AND seed_tradetable = 'N' ORDER BY rand() LIMIT 15");
		} else {
			mas_qb($mas,"SELECT * FROM seeds,accounts WHERE seed_userid = account_userid AND seed_featured = 'Y' AND seed_tradetable = 'N' AND seed_quantity > 0 ORDER BY seed_tsmod LIMIT 15");
		}
		if(!$mas->mas_row_cnt) tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"seedsSplashNone"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		else{
			tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"seedSplashTop"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
			while(mas_qg($mas,$temptt)){
				if($c==2){
					tkntbl_add($tt,"last"," last",1);
				}
				if(file_exists(IMAGEROOT."seeds/thmb/".ttn($temptt,"seed_id")."_1.jpg")){
				 	tkntbl_snprintf($gtt,"seed_image",1,MAX_RESULTS,"seeds/thmb/%s_1.jpg",ttn($temptt,"seed_id"));
				} else tkntbl_add($gtt,"seed_image","noImageAvailable.jpg",1);

				 tkntbl_add($gtt,"account_username",ttn($temptt,"account_username"),1);
				tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"seedSplashRow"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt,&$temptt));

				$c++;
				if($c==3) {
					$c=0;
					tkntbl_add($tt,"last","",1);
				}
			} mas_qe($mas);

			tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"seedSplashBottom"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		}
		criterr(NULL);
    }

}