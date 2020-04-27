<?php

/* SeedLiving Seeds Module
 *
 * Copyright (c) 2015-2016 Seeds of Diversity Canada
 *
 */

class SLiv_Seeds
{
    private $oSLiv;

    function __construct( SEEDLiving $oSLiv )
    {
        $this->oSLiv = $oSLiv;
    }

    function Command( $cmd )
    {
        $bHandled = false;
        $sOut = "";

        if( substr( $cmd, 0, 7 ) != 'myseeds' ) goto done;
        if( !$this->oSLiv->oUser->IsLogin() ) {
            $this->oSLiv->GotoLoginPage();    // doesn't return
            $bHandled = true;
            goto done;
        };

        $bHandled = true;

        switch( $cmd ) {
            case "myseedsList":           $sOut = $this->MySeedsList();    break;
            case "myseedsAdd":            $sOut = $this->MySeedsAdd();     break;
            case "myseedsEdit":           $sOut = $this->MySeedsEdit();    break;

            default:
                $bHandled = false;
        }

        done:
        return( array($bHandled, $sOut) );
    }

    function MySeedsList()
    {
        global $tt, $gtt, $mas, $temptt, $tmpl, $dtt, $mas2;
        $s = "";

        $uid = $this->oSLiv->oUser->GetUID();
        $bSwap = ttn($tt,"action")=="swaps";

        $raTmpl = array(
            'bSwap' => $bSwap,
            'bUnlimitedFeatures' => "Y" == $this->oSLiv->kfdb->Query1(
                                "SELECT account_unl4 FROM users, accounts "
                               ."WHERE account_userid=user_id AND user_id='$uid'" ),
            'nFeatured' => $this->oSLiv->kfdb->Query1(
                                "SELECT count(*) FROM seeds WHERE seed_featured='Y' AND seed_userid='$uid'" ),
            'sFeatured' => "",
            'sMyseedsListRows' => ""
        );

        if( $bSwap ) {
			mas_qb($mas,"SELECT * FROM seeds %s ORDER BY seed_tsadd",(!strcmp(ttn($gtt,"access"),"admin")?"":sprintf("WHERE seed_userid = '".$this->oSLiv->oUser->GetUID()."' AND (seed_trade='Y' OR seed_tradetable='Y')")));
        } else {
            $cond = ttn($gtt,"access")=="admin" ? "" : ("WHERE seed_userid='$uid'");
            $dbc = $this->oSLiv->kfdb->CursorOpen( "SELECT * FROM seeds $cond ORDER BY seed_tsadd" );
        }

        if( !$dbc || !$this->oSLiv->kfdb->CursorGetNumRows($dbc) ) {
			mas_qb($mas,"SELECT cat_id as optionVal, cat_name as optionDisplay FROM cats WHERE cat_parentid = '0' AND cat_enabled = 'Y' ORDER BY cat_name");
			if(!$mas->mas_row_cnt) tmplt_proc_ex_tt(TEMPLROOT,tkntbl_search($tmpl,"secureOptionsNone"),OPENTAG,CLOSETAG,2,stdout,$tt,"seedtopcat",array(&$tt,&$temptt));
			tkntbl_add($tt,"seedtopcat","<option value=\"\">--select--</option>",1);
			while(mas_qg($mas,$dtt)){
				tmplt_proc_ex_tt(TEMPLROOT,tkntbl_search($tmpl,"secureOptions"),OPENTAG,CLOSETAG,2,stdout,$tt,"seedtopcat",array(&$tt,&$dtt));
				tkntbl_ftable($dtt);
			} mas_qe($mas);

			tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"myseedsAddEdit"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt,&$temptt));
        } else {
            while( $ra = $this->oSLiv->kfdb->CursorFetch( $dbc ) ) {
                if( $ra['seed_featured'] == 'N' ) $ra['sFeatureOpts'] = SEEDStd_ArrayExpand( $ra, "<option value='[[seed_id]]'>[[seed_title]]</option>", true );
                if( $ra['seed_featured'] == 'Y' ) $ra['sFeatured'] = SEEDStd_ArrayExpand( $ra, "<li>[[seed_title]] - <a href='#' seedid='[[seed_id]]' class='slRemoveFeature'>Remove</a></li>", true );
                $ra['seed_topcat'] = $ra['seed_topcat'] ? $this->oSLiv->kfdb->Query1( "SELECT cat_name FROM cats WHERE cat_id='{$ra['seed_topcat']}'" )
                                                        : "";
                $raTmpl['sMyseedsListRows'] .= $this->oSLiv->oTmpl->ExpandTmpl( 'myseedsListRow', array_merge($raTmpl,$ra) );
            }
            $s .= $this->oSLiv->oTmpl->ExpandTmpl( 'myseedsList', $raTmpl );
        }

        return( $s );
    }

    function MySeedsAdd()
    {
        $s = "";
        global $tt, $gtt, $mas, $temptt, $tmpl, $dtt, $mas2;

        $raTmpl = array();
        $raTmpl['seedtopcat'] = $this->topCat();
        $raTmpl['user_id'] = $this->oSLiv->sess->GetUID();
        $s .= $this->oSLiv->oTmpl->ExpandTmpl( "myseedsAdd", $raTmpl );

        return( $s );
    }

    function MySeedsEdit()
    {
        $s = "";
        global $tt, $gtt, $mas, $temptt, $tmpl, $dtt, $mas2;

        $seedid = ttn( $tt, '@id' );
        $raSeed = $this->oSLiv->kfdb->QueryRA( "SELECT * FROM seeds WHERE seed_id='$seedid'" );

        $raTmpl = $raSeed;
        $raTmpl['seedtopcat'] = $this->topCat();
        $raTmpl['user_id'] = $this->oSLiv->sess->GetUID();

        if( $raSeed['seed_trade'] == 'Y' ) {
            $raTmpl['seed_tradeopt'] = $raSeed['seed_tradetable'] == 'Y' ? "Y" : "S";
        } else {
            $raTmpl['seed_tradeopt'] = $raSeed['seed_tradetable'] == 'Y' ? "T" : "N";
        }

/*
		for($c=1;$c<=3;$c++){
			if(file_exists(IMAGEROOT."seeds/".ttn($temptt,"seed_id")."_".$c.".jpg")){
				tkntbl_snprintf($tt,"seed_image",1,MAX_RESULTS,"seeds/%s_%s.jpg",ttn($temptt,"seed_id"),$c);
				tmplt_proc_ex_tt(TEMPLROOT,tkntbl_search($tmpl,"slSeedsEditImages"),OPENTAG,CLOSETAG,2,stdout,$tt,"slSeedsEditImages",array($tt));
			} else {
				tkntbl_add($tt,"seed_image","noImageAvailable.jpg",1);
				tmplt_proc_ex_tt(TEMPLROOT,tkntbl_search($tmpl,"slSeedsEditImages"),OPENTAG,CLOSETAG,2,stdout,$tt,"slSeedsEditImages",array($tt));
			}
		}

		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"seedEdit"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"myseedsAddEdit"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt,&$temptt));
 */

        $s .= $this->oSLiv->oTmpl->ExpandTmpl( "myseedsEdit", $raTmpl );

        return( $s );
    }

    private function topCat()
    {
        $sTopCat = "<option value=''>--select--</option>";

        $ra = $this->oSLiv->kfdb->QueryRowsRA( "SELECT cat_id,cat_name FROM cats WHERE cat_parentid='0' AND cat_enabled='Y' ORDER BY cat_name" );
        if( count($ra) ) {
            foreach( $ra as $raC ) {
                $sTopCat .= SEEDStd_ArrayExpand( $raC, "<option value='[[cat_id]]'>[[cat_name]]</option>" );
            }
        } else {
            $sTopCat = "<option value=''>None</option>";
        }
        return( $sTopCat );
    }
}

?>
