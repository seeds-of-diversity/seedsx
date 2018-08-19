<?php
/* Seed Library: sl_desc_db
 *
 * Copyright 2009-2017 Seeds of Diversity Canada
 *
 * Descriptor module - database access
 */

class Mbr_SiteDB
/***************
    Encapsulate Mbr Site db access

    Mbr Sites are shared between Seed Library and Pollinator Watch
 */
{
    // Was intended to be protected but KeyFrameUIForm (and potentially other generic record-management classes) needs a kfrel.
    // Access this via GetKfrelMbrSite()
    var $kfrelMbrSite;

    // protected - available to derived classes
    var $kfdb;
    var $uid;

    // protected - a derived class for a child table will use this in joined kfrel
    var $fldMbrSite = array(
            array( "col"=>"uid",                 "type"=>"I" ),
            array( "col"=>"sitename",            "type"=>"S" ),
            array( "col"=>"address",                "type"=>"S" ),
            array( "col"=>"city",                "type"=>"S" ),
            array( "col"=>"province",            "type"=>"S" ),
            array( "col"=>"postcode",            "type"=>"S" ),
            array( "col"=>"country",             "type"=>"S" ),
            array( "col"=>"latitude",            "type"=>"S" ),
            array( "col"=>"longitude",           "type"=>"S" ),
            array( "col"=>"metadata",            "type"=>"S" )
        );

    function __construct( KeyFrameDB $kfdb, $uid = 0 )
    /*************************************************
     */
    {
        $this->kfdb = &$kfdb;
        $this->uid = $uid;
        $this->initKFRelMbrSite();
    }

    function GetListMbrSite( $raParms = array() )
    /********************************************
        Return a list of mbr_site, filtered by the given parms.
     */
    {
        $ra = array();

        $raCond = $this->getMbrSiteFilterRA( $raParms );

        if( ($kfr = $this->kfrelMbrSite->CreateRecordCursor( implode(" AND ", $raCond) )) ) {
            while( $kfr->CursorFetch() ) {
                $ra[] = $kfr->ValuesRA();
            }
        }
        return( $ra );
    }

    function PutMbrSite( $kSite, $uid, $raParms = array() )
    {
         if( $kSite ) {
             $kfr = $this->kfrelMbrSite->GetRecordFromDBKey( $kSite );
         } else {
             $kfr = $this->kfrelMbrSite->CreateRecord();
         }
         if( $kfr ) {
             $kfr->SetValue( 'uid', $uid );
             foreach( $raParms as $k => $v ) {
                 if( in_array( $k, array('sitename','city','province','postcode','country','latitude','longitude') ) ) {
                     $kfr->SetValue( $k, $v );
                 } else {
                     $kfr->UrlParmSet( 'metadata', $k, $v );
                 }
             }
             // sitename is not allowed to be blank
             if( $kfr->IsEmpty('sitename') ) {
                 $kfr->SetValue( 'sitename', "Site #",(count($this->raSites)+1) );
             }
             $kfr->PutDBRow();
         }
    }

    function GetMbrSiteGPC( $raGPC = NULL )
    {
        if( $raGPC === NULL ) $raGPC = $_REQUEST;

        $ra = array();


    }

    // protected: derived classes can use this to get VarInst filters in a consistent way
    function getMbrSiteFilterRA( $raParms, $kfrel = NULL )
    /*****************************************************
        Return an array of SQL conditions that filter a desired set of Mbr Sites based on the $raParms

        If you're going to use a derived class's kfrel to fetch records, pass it in to be sure that the correct tables aliases are used

        $raParms:
            uid   = key of SEEDSession_Users
            kSite = key of mbr_sites
     */
    {
        if( !$kfrel )  $kfrel = $this->kfrelMbrSite;

        $raCond = array();

        if( @$raParms['uid'] )     $raCond[] = $kfrel->GetDBColName("mbr_sites", "uid")."='".addslashes($raParms['uid'])."'";
        if( @$raParms['kSite'] )   $raCond[] = $kfrel->GetDBColName("mbr_sites", "_key")."='".addslashes($raParms['kSite'])."'";

        return( $raCond );
    }


    function GetKfrelMbrSite() { return( $this->kfrelMbrSite ); }

    function initKFRelMbrSite()
    /**************************
     */
    {
        $defSite =
            array( "Tables" => array(
                   array( "Table" => "mbr_sites",
                          "Type"  => "Base",
                          "Fields" => $this->fldMbrSite ) ) );

        $this->kfrelMbrSite = new KeyFrameRelation( $this->kfdb, $defSite, $this->uid );
    }
}


class SL_VarInstDB extends Mbr_SiteDB
/*****************
    Encapsulate SL Variety Instance db access

    A Variety Instance is a relation of (grower,variety or accession,year)

    Variety Instances are shared between various Seed Library components e.g. SL Multiplication, SL Descriptors
 */
{
    // Was intended to be protected but KeyFrameUIForm (and potentially other generic record-management classes) needs a kfrel.
    // Access this via GetKfrelSLVarInst()
    var $kfrelSLVarInst;

    // protected - a derived class for a child table will use this in joined kfrel
    var $fldSLVarInst = array(
            array( "col"=>"fk_mbr_sites",        "type"=>"K" ),
            array( "col"=>"fk_sl_pcv",           "type"=>"K" ),
            array( "col"=>"osp",                 "type"=>"S" ),
            array( "col"=>"oname",               "type"=>"S" ),
            array( "col"=>"fk_sl_accession",     "type"=>"K" ),
            array( "col"=>"year",                "type"=>"I" ),
            array( "col"=>"metadata",            "type"=>"S" )
        );

    function __construct( KeyFrameDB $kfdb, $uid = 0 )
    /****************************************
     */
    {
        parent::__construct( $kfdb, $uid );
        $this->initKFRelSLVarInst();
    }

    function GetVarInst( $kVI )
    /**************************
        Get info about one variety instance
     */
    {
    	$ra = array();

        if( ($kfr = $this->kfrelSLVarInst->GetRecordFromDBKey( $kVI )) ) {
            $ra = $this->computeVIRA( $kfr->ValuesRA() );
        }
        return( $ra );
    }

    function GetListVarInst( $raParms = array() )
    /********************************************
        Return a list of varinst, with mbr_site, filtered by the given parms.
        e.g. get all variety names grown at a certain site in a certain year
        e.g. get all variety names grown by a certain person in a certain year
        e.g. get all years that a certain person grew a certain variety
        e.g. get all variety names for a certain species

     */
    {
        $raVI = array();

        $raCond = $this->getVarInstFilterRA( $raParms );

        if( ($kfr = $this->kfrelSLVarInst->CreateRecordCursor( implode(" AND ", $raCond) )) ) {
            while( $kfr->CursorFetch() ) {
                $raVI[] = $this->computeVIRA( $kfr->ValuesRA() );
            }
        }

        return( $raVI );
    }

    function computeVIRA( $raIn, $prefix = "" )
    /******************************************
        Given multiplexed varinst record, fill in the blanks.  Prefix is "" for base varinst, typically VarInst_ for descobs
     */
    {
    	$raOut = $raIn;

    	if( @$raIn[$prefix.'fk_sl_accession'] ) {
            // the variety is recorded by accession: set up the pcv for the next test
    	    $raOut[$prefix.'fk_sl_pcv'] = $this->kfrelSLVarInst->kfdb->Query1( "SELECT fk_sl_pcv FROM sl_accession WHERE _key='{$raIn[$prefix.'fk_sl_accession']}'" );
    	}
        if( ($k = @$raIn[$prefix.'fk_sl_pcv']) ) {
        	// the variety is recorded by pcv (or accession)
            $ra = $this->kfdb->QueryRA( "SELECT psp, name FROM sl_pcv WHERE _key='$k'" );
            $raOut[$prefix.'csp'] = @$ra[$prefix.'psp'];
            $raOut[$prefix.'ccv'] = @$ra[$prefix.'name'];
        }
        if( !@$raOut[$prefix.'csp'] || !@$raOut[$prefix.'ccv'] ) {
            $raOut[$prefix.'csp'] = @$raIn[$prefix.'psp'];
            $raOut[$prefix.'ccv'] = @$raIn[$prefix.'pname'];
        }
        if( !@$raOut[$prefix.'csp'] || !@$raOut[$prefix.'ccv'] ) {
            $raOut[$prefix.'csp'] = @$raIn[$prefix.'osp'];
            $raOut[$prefix.'ccv'] = @$raIn[$prefix.'oname'];
        }

        return( $raOut );
    }

    // protected: derived classes can use this to get VarInst filters in a consistent way
    function getVarInstFilterRA( $raParms, $kfrel = NULL )
    /*****************************************************
        Return an array of SQL conditions that filter a desired set of Variety Instances based on the $raParms

        If you're going to use a derived class's kfrel to fetch records, pass it in to be sure that the correct tables aliases are used

        $raParms:
            uid    = key of SEEDSession_Users (via MbrSites)
            kSite  = key of mbr_sites         (via MbrSites)
            kPCV   = key of sl_pcv
            osp    = name of species
            oname  = name of variety
            kAcc   = key of sl_acc
            year   = year
     */
    {
        if( !$kfrel )  $kfrel = $this->kfrelSLVarInst;

        $raCond = $this->getMbrSiteFilterRA( $raParms, $kfrel );

        if( @$raParms['kPCV'] )    $raCond[] = $kfrel->GetDBColName("sl_varinst", "fk_sl_pcv" )."='".addslashes($raParms['kPCV'])."'";
        if( @$raParms['osp'] )     $raCond[] = $kfrel->GetDBColName("sl_varinst", "osp" )."='".addslashes($raParms['osp'])."'";
        if( @$raParms['oname'] )   $raCond[] = $kfrel->GetDBColName("sl_varinst", "oname" )."='".addslashes($raParms['oname'])."'";
        if( @$raParms['kAcc'] )    $raCond[] = $kfrel->GetDBColName("sl_varinst", "fk_sl_acc" )."='".addslashes($raParms['kAcc'])."'";
        if( @$raParms['year'] )    $raCond[] = $kfrel->GetDBColName("sl_varinst", "year" )."='".addslashes($raParms['year'])."'";

        return( $raCond );
    }


    function GetKfrelSLVarInst() { return( $this->kfrelSLVarInst ); }

    function initKFRelSLVarInst()
    /****************************
     */
    {
        $defVarInst =
            array( "Tables" => array(
                   array( "Table" => "sl_varinst",
                          "Type"  => "Base",
                          "Fields" => $this->fldSLVarInst ),
                   array( "Table" => "mbr_sites",
                          "Alias" => "Site",
                          "Type"  => "Parent",
                          "Fields" => $this->fldMbrSite ) ) );

        $this->kfrelSLVarInst = new KeyFrameRelation( $this->kfdb, $defVarInst, $this->uid );
    }
}


class SL_DescDB extends SL_VarInstDB
/**************
    Encapsulate SL Descriptor db access
 */
{
    // Was intended to be private but KeyFrameUIForm (and potentially other generic record-management classes) needs a kfrel.
    // Access this via GetKfrelSLDescObs()
    var $kfrelSLDescObs;

    // protected - a derived class for a child table will use this in joined kfrel
    var $fldSLDescObs = array(
            array( "col"=>"fk_sl_varinst",       "type"=>"K" ),
            array( "col"=>"k",                   "type"=>"S" ),
            array( "col"=>"v",                   "type"=>"S" )
        );

    function __construct( KeyFrameDB $kfdb, $uid = 0 )
    /*************************************
     */
    {
        parent::__construct( $kfdb, $uid );
        $this->initKFRelSLDescObs();
    }

    function GetListDescObs( $raParms = array() )
    /********************************************
        Return a list of desc_obs, with varinst and mbr_site, filtered by the given parms.
        Parms are the union of VarInst filter parms, and these:

        $raParms:
            desc_k        = a Descriptor code
            desc_k_prefix = all Descriptor codes LIKE foo%
            kVarinst      = sl_varinst._key
     */
    {
        $raRet = array();

        // Marshal the conditions relating to varinst and mbr_sites
        // using our own kfrel to get the correct table aliases
        $raCond = $this->getVarInstFilterRA( $raParms, $this->kfrelSLDescObs );

        // add filters for this class
        if( @$raParms['desc_k'] )         $raCond[] = $this->kfrelSLDescObs->GetDBColName("sl_desc_obs", "k" )."='".addslashes($raParms['desc_k'])."'";
        if( @$raParms['desc_k_prefix'] )  $raCond[] = $this->kfrelSLDescObs->GetDBColName("sl_desc_obs", "k" )." LIKE '".addslashes($raParms['desc_k_prefix'])."%'";
        if( @$raParms['kVarinst'] )       $raCond[] = $this->kfrelSLDescObs->GetDBColName("sl_desc_obs", "fk_sl_varinst" )."='".addslashes($raParms['kVarinst'])."'";

        if( ($kfr = $this->kfrelSLDescObs->CreateRecordCursor( implode(" AND ", $raCond) )) ) {
            while( $kfr->CursorFetch() ) {
                $raRet[] = $this->computeVIRA( $kfr->ValuesRA(), "VarInst_" );
            }
        }

        return( $raRet );
    }


    function GetKfrelSLDescObs() { return( $this->kfrelSLDescObs ); }

    function initKFRelSLDescObs()
    /****************************
     */
    {
        $defObs =
            array( "Tables" => array(
                   array( "Table" => "sl_desc_obs",
                          "Type"  => "Base",
                          "Fields" => $this->fldSLDescObs ),
                   array( "Table" => "sl_varinst",
                          "Alias" => "VarInst",
                          "Type"  => "Parent",
                          "Fields" => $this->fldSLVarInst ),
                   array( "Table" => "mbr_sites",
                          "Alias" => "Site",
                          "Type"  => "GrandParent",
                          "Fields" => $this->fldMbrSite ) ) );

        $this->kfrelSLDescObs = new KeyFrameRelation( $this->kfdb, $defObs, $this->uid );
    }
}


class SLDescDB_Cfg
/*****************
    Encapsulate access to SLDesc config tables
 */
{
    private $kfrelCfgTags;
    private $kfrelCfgM;

    function __construct( KeyFrameDB $kfdb, $uid = 0 )
    /*************************************************
     */
    {
        $this->initKfrel( $kfdb, $uid );
    }

    public function GetKfrelCfgTags() { return( $this->kfrelCfgTags ); }
    public function GetKfrelCfgM()    { return( $this->kfrelCfgM ); }


    function GetListCfgTags( $raParms = array() )
    /********************************************
        Return a list of tags, filtered by the given parms.

        $raParms:
            sp = filter to species prefix {sp}_
     */
    {
        $raRet = array();
        $raCond = array();
        if( ($p = @$raParms['sp']) ) {
            $raCond[] = "tag like '".addslashes($p)."_%'";
        }

        if( ($kfr = $this->kfrelCfgTags->CreateRecordCursor( implode(" AND ", $raCond) )) ) {
            while( $kfr->CursorFetch() ) {
                $raRet[$kfr->Value('tag')] = $kfr->ValuesRA();
            }
        }

        return( $raRet );

    }


    function GetListCfgMultiples( $raParms = array() )
    /*************************************************
        Return a list of multiple choices for tags, filtered by the given parms.

        $raParms:
            sp = filter to species prefix {sp}_
     */
    {
        $raRet = array();
        $raCond = array();
        if( ($p = @$raParms['sp']) ) {
            $raCond[] = "tag like '".addslashes($p)."_%'";
        }

        if( ($kfr = $this->kfrelCfgM->CreateRecordCursor( implode(" AND ", $raCond) )) ) {
            while( $kfr->CursorFetch() ) {
                $raRet[] = $kfr->ValuesRA();
            }
        }

        return( $raRet );

    }



    private function initKfrel( $kfdb, $uid )
    /****************************************
     */
    {
        $defTags =
            array( "Tables" => array(
                array( "Table" => "sl_desc_cfg_tags",
                       "Type"  => "Base",
                       "Fields" => array( array( "col"=>"tag",       "type"=>"S" ),
                                          array( "col"=>"label_en",  "type"=>"S" ),
                                          array( "col"=>"label_fr",  "type"=>"S" ),
                                          array( "col"=>"q_en",      "type"=>"S" ),
                                          array( "col"=>"q_fr",      "type"=>"S" ) ),
            )));

        $defM =
            array( "Tables" => array(
                array( "Table" => "sl_desc_cfg_m",
                       "Type"  => "Base",
                       "Fields" => array( array( "col"=>"tag",  "type"=>"S" ),
                                          array( "col"=>"v",    "type"=>"S" ),
                                          array( "col"=>"l_en", "type"=>"S" ),
                                          array( "col"=>"l_fr", "type"=>"S" ) ),
            )));

        $parms = array('logfile'=>SITE_LOG_ROOT."sldesccfg.log");
        $this->kfrelCfgTags = new KeyFrameRelation( $kfdb, $defTags, $uid, $parms );
        $this->kfrelCfgM    = new KeyFrameRelation( $kfdb, $defM,    $uid, $parms );
    }

}

?>
