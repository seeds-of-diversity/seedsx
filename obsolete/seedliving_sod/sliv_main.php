<?php

define( "SEEDLIVING_ROOT", "./" );
include_once( SEEDLIVING_ROOT."sl_defs.php" );

include_once( SEEDLIVING_ROOT."lib/sliv_lib.php" );
include_once( SEEDLIVING_ROOT."lib/sliv_basket.php" );
include_once( SEEDLIVING_ROOT."lib/sliv_seeds.php" );
include_once( SEEDLIVING_ROOT."lib/sliv_users.php" );


include_once( SEEDCOMMON."doc/docUtil.php" );   // DocRepDB

$oSLiv = new SeedLiving();


// old init code which now uses oSLiv
include_once( SEEDLIVING_ROOT."sliv_init.php" );



class SeedLiving
{
    public $kfdb;     // seedliving db
    public $kfdb1;    // seeds1 db
    public $sess;
    public $lang;

    public $oSLivParms;
    public $oBasket;
    public $oSeeds;
    public $oUser;
    public $oTmpl;
    public $oDocRepDB;

    private $oSVA;    // seedliving session vars - only accessed via this class's methods

    function __construct()
    {
        $this->oSLivParms = new SeedLivingParms();

        list($this->kfdb1, $this->sess, $this->lang) = SiteStartSessionAccountNoUI(); // no perms required, right?

        // from SiteKFDB - not sure if adding other constants (e.g. SiteKFDB_DB_seedliving) to the case statements will break that code if constants not defined
        if( !($this->kfdb = new KeyFrameDB( "localhost", SiteKFDB_USERID_seedliving, SiteKFDB_PASSWORD_seedliving )) ||
            !($this->kfdb->Connect( SiteKFDB_DB_seedliving )) )
        {
            die( "Cannot connect to SeedLiving database" );
        }

        //$this->kfdb1->SetDebug(1);
        //$this->kfdb->SetDebug(1);

        $this->oTmpl = new SLivTemplate( $this );
        $this->oBasket = new SLiv_Basket( $this );  // depends on oTmpl to set up the SEEDSession UI
        $this->oSeeds = new SLiv_Seeds( $this );  // depends on oTmpl to set up the SEEDSession UI
        $this->oUser = new SLiv_Users( $this );  // depends on oTmpl to set up the SEEDSession UI
        $this->oDocRepDB = New_DocRepDB_WithMyPerms( $this->kfdb1, $this->oUser->GetUID(), array('bReadonly'=>true) );

        // keep session state stuff here
        $this->oSVA = new SEEDSessionVarAccessor( $this->sess, "seedliving" );
    }

    function SessVarGet( $k )     { return( $this->oSVA->VarGet( $k ) ); }
    function SessVarSet( $k, $v ) { $this->oSVA->VarSet( $k, $v ); }

    function GotoPage( $sPage )
    {
        header( "Location: /".SEONAME."/$sPage/" );
        exit;
    }
    function GotoLoginPage()  { $this->GotoPage( 'login' ); }


    function Tmpl2( $name, $tokens = null, $raParms = array(), $type = 1 )
    {
        global $tmpl, $tt;

        $s = "";

        if( !$tokens ) $tokens = array();    // not enough to set arg default to array() because if the caller makes it null that doesn't trigger the default

        if( $this->oTmpl->Exists($name) ) {
            // use new templating

            // old tt -> new arrays
            $raParms = array_merge($raParms, GetTokensRA($tokens) );
            $s = $this->oTmpl->ExpandTmpl( $name, $raParms );
        } else {
            // use old templating

            // new arrays -> old tt
            foreach( $raParms as $k => $v )  tkntbl_add( $tt, $k, $v, 1 );
            //return( tmplt_proc_ex( TEMPLROOT, tkntbl_search($tmpl,$name), OPENTAG, CLOSETAG, stdout, $type, array_merge($tokens,array($tt)) ) ); // $tt overwrites $tokens
            $s = $this->oTmpl->Expand( TEMPLROOT, tkntbl_search($tmpl,$name), $type, array_merge($tokens,array($tt)) ); // $tt overwrites $tokens
        }
        return( $s );
    }

    function Tmpl( $name, $tokens, $raParms = array(), $type = 1 )
    {
        echo $this->Tmpl2( $name, $tokens, $raParms, $type );
    }

    function ImgSrc( $prefix, $id, $n = 1, $bTest = true, $bDefault = true )
    {
        $o = new imgman( $this->oDocRepDB );
        $fname = $o->getDocRepSFile( $prefix, $id, $n, $bTest, $bDefault );

        return( $fname );
    }

    function DrawSeedsSplash()
    {
        global $tt, $gtt, $mas, $tmpl, $temptt;

        $s = "";

        $c = 0;

        $sSql = "SELECT * FROM seeds JOIN accounts ON (seed_userid=account_userid) WHERE seed_quantity > 0 AND seed_tradetable='N'";
        if( $this->oSLivParms->bFeesEnabled ) {
            mas_qb($mas, "$sSql AND seed_featured = 'Y' ORDER BY seed_tsmod" ); // LIMIT 15");
        } else {
            mas_qb($mas, "$sSql ORDER BY rand()" ); // LIMIT 15");
        }
        if( !$mas->mas_row_cnt ) {
            $s .= $this->Tmpl2( "seedsSplashNone", array($tt,$gtt) );
        } else {
            $nWithImg = 6;
            $nWithoutImg = 9;

            $sWithImg = $sWithoutImg = "";
            $countWithImg = $countWithoutImg = 0;

            while( mas_qg($mas,$temptt) ) {
                if( ($img = $this->ImgSrc('seeds/thmb',ttn($temptt,"seed_id"),1,true,false)) ) {
                    if( $countWithImg < $nWithImg ) {
                        tkntbl_add($tt,"last", $countWithImg % 3 == 0 ? " last" : "",1);
                        tkntbl_add($gtt,"seed_sfile",$img,1);
                        tkntbl_add($gtt,"account_username",ttn($temptt,"account_username"),1);
                        $sWithImg .= $this->Tmpl2( "seedSplashRow", array($tt,$gtt,$temptt) );
                        $countWithImg++;
                    }
                } else {
                    if( $countWithoutImg < $nWithoutImg ) {
                        tkntbl_add($tt,"last", $countWithoutImg % 3 == 0 ? " last" : "",1);
                        tkntbl_add($gtt,"seed_sfile","",1);
                        tkntbl_add($gtt,"account_username",ttn($temptt,"account_username"),1);
                        $sWithoutImg .= $this->Tmpl2( "seedSplashRow", array($tt,$gtt,$temptt) );
                        $countWithoutImg++;
                    }
                }
                if( $countWithImg >= $nWithImg && $countWithoutImg >= $nWithoutImg )  break;
            } mas_qe($mas);

            $s .= $this->Tmpl2( "seedSplashTop", array($tt,$gtt) );
            $s .= $sWithImg
                 .$sWithoutImg;
            $s .= $this->Tmpl( "seedSplashBottom", array($tt,$gtt) );
        }

        return( $s );
    }

    function DrawTags()
    {
        $c = $this->kfdb->Query1( "SELECT count(*) FROM seeds WHERE (seed_trade = 'S' or seed_trade = 'Y') AND seed_quantity > 0 and seed_enabled = 'Y'");

        $content = "<h4 style=\"color:#b3b3b3;\">top ten tags</h4>"
                  ."<a style='text-decoration:none;' href='".SL2URL."/swaps/'><span style='color:#3333FF;'>swaps</span></a>&nbsp;"
                  ."<span style='color:#B3B3B3;font-size:10px;'>$c items with this tag</span><br />";


        if( ($dbc = $this->kfdb->CursorOpen( "SELECT tagrel_tagid, tag_name,tag_url,count(*) as total "
                                            ."FROM tagrel,tags,seeds "
                                            ."WHERE seed_enabled='Y' and seed_id=tagrel_seedid and seed_quantity>0 and tag_id=tagrel_tagid "
                                            ."GROUP BY tagrel_tagid ORDER BY total desc LIMIT 9")) ) {
            while( $ra = $this->kfdb->CursorFetch( $dbc ) ) {
                $content .= "<a style='text-decoration:none' href='".SL2URL."/${ra['tag_url']}'>"
                           ."<span style='color:#3333FF;'>${ra['tag_name']}</span></a>&nbsp;"
                           ."<span style='color:#B3B3B3;font-size:10px;'>${ra['total']} items with this tag</span><br />";
            }
        }
        return( $content );
    }
}

class SeedLivingParms
{
    public $bFeesEnabled = false;
    public $bLoginSession = false;

    // set initial parms here because SeedLiving constructor passes them immediately to SLivTemplate (where they're stored as globals for templates)
    // none of this should be in sliv_init.php, though the defs can be in sl_defs.php
    public $SEONAME = SEONAME;
    public $SEONAME2 = SEONAME2;
    public $SL2URL = SL2URL;

    function __construct() {}

    function GetRA()
    {
        return( array( "SLivParm_bFeesEnabled" => $this->bFeesEnabled,
                       "SLivParm_bLoginSession" => $this->bLoginSession,

                       "bFeesEnabled" => $this->bFeesEnabled,
                       "bLoginSession" => $this->bLoginSession,

                       "SEONAME" => $this->SEONAME,
                       "SEONAME2" => $this->SEONAME2,
                       "SL2URL" => $this->SL2URL,
        ) );
    }
}


function LoadSED( SeedLiving $oSLiv )
{
    $kfdb1 = SiteKFDB( "seeds" );

    $s = "";

    // Force the sed_curr_growers into accounts and users
    // There is no overlap between our members and the original SeedLiving users because that was sorted out
    // when we made SEEDSession_Users accounts
    $nG = $nGAdded = 0;
    if( ($dbc = $kfdb1->CursorOpen( "SELECT * FROM sed_curr_growers WHERE NOT bSkip and NOT bDelete" )) ) {
        while( $ra = $kfdb1->CursorFetch( $dbc ) ) {
            $mbrid = $ra['mbr_id'];
            if( !$oSLiv->kfdb->Query1( "SELECT account_id FROM accounts where account_id='$mbrid'") ) {
                $oSLiv->kfdb->Execute( "INSERT INTO accounts (account_id,account_userid) VALUES('$mbrid','$mbrid')" );
                $oSLiv->kfdb->Execute( "INSERT INTO users    (user_id,user_accountid)    VALUES('$mbrid','$mbrid')" );
                ++$nGAdded;
            }
            ++$nG;
        }
    }
    echo "<p>$nG Growers checked, $nGAdded added</p>";

    // Copy the sed_curr_seeds into seeds
    $oSLiv->kfdb->Execute( "DELETE FROM seeds WHERE eOrigin='sed'" );

    $nS = 0;
    if( ($dbc = $kfdb1->CursorOpen( "SELECT * FROM sed_curr_seeds WHERE NOT bSkip and NOT bDelete "
                                   ."AND category='VEGETABLES' "
                                   ."AND type NOT LIKE 'TOMATO/MISC%'" )) ) {
        while( $ra = $kfdb1->CursorFetch( $dbc ) ) {
            $sp = addslashes( utf8_encode($ra['type']) );
            $cv = addslashes( utf8_encode($ra['variety']) );
            $desc = addslashes( utf8_encode($ra['description']) );
            $mbrid = $ra['mbr_id'];
            $oSLiv->kfdb->Execute( "INSERT INTO seeds (seed_id,seed_userid,seed_type,seed_title,seed_title2,seed_desc,seed_topcat,seed_price,seed_currency,seed_quantity,seed_enabled,seed_featured,eOrigin ) "
                                  ." VALUES           (NULL,   '$mbrid',   'S',      '$sp',     '$cv',      '$desc',  9,          '3.00',    'CAD',        20,           'Y',         'Y',          'sed')" );
            ++$nS;
        }
    }
    echo "<p>$nS added</p>";
}

?>
