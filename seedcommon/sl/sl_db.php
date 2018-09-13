<?php

/* Seed Library: sl_db
 *
 * Copyright 2010-2018 Seeds of Diversity Canada
 *
 * Database access module for Seed Library
 *
 * SLDB_Accession - get rows from the sl_accession table
 * SLDB_Inventory - get rows from the sl_inventory table
 * SLDB_Adoption  - get rows from the sl_adoption table
 * SLDB_Pcv       - get rows from the sl_pcv table
 *
 * These objects allow fetches/updates to joined tables: the first table is the KF base table
 * SLDB_AP        - sl_accession X(M:1) sl_pcv
 * SLDB_DP        - sl_adoption X(M:1) sl_pcv
 * SLDB_PA        - sl_pcv LEFT JOIN sl_accession -- because some pcv have no accessions
 * SLDB_PD        - sl_pcv LEFT JOIN sl_adoption  -- because some pcv have no adoptions
 *
 * SLDB_PAD       - a complex of SLDB_PA and SLDB_PD that gets all info about pcv
 *                -- To get a list of donors and accessions for a particular pcv, you need to fetch lists separately because many-many
 *                   relationship will return duplicates, and non-adopted accessions would require a left join to be returned which
 *                   makes the cursor difficult to understand at a higher level
 *
 * Standard filters:
 *     kPCV               : limit results to one cultivar
 *     kAdoptMbr          : limit results to one adopter - sl_adoption.fk_mbr_contacts
 *     sAdoptDonorLike    : limit results to adoptions that contain the string in donor_name or public_name
 *     sAdoptRequestLike  : limit results to adoptions that contain the string in sPCV_request
 *     sPSP               : sl_pcv.psp exact match
 *     sPSPLike           : sl_pcv.psp %LIKE%
 *     sPName             : sl_pcv.name exact match
 *     sPNameLike         : sl_pcv.name %LIKE%
 *     sCond              : verbatim condition
 */

/********************************************
    EVERYTHING TO THE CORRESPONDING NOTE IS IN sldb.php
 */
class sldb__base
/***************
    Protected class to contain defs
 */
{
    protected function __construct() {}

    static function kfrelFldSLCollection()
    {
        return( array( array( "col"=>"name",                "type"=>"S" ),
                       array( "col"=>"uid_owner",           "type"=>"I" ),
                       array( "col"=>"inv_prefix",          "type"=>"S" ),
                       array( "col"=>"inv_counter",         "type"=>"I" ),
                       array( "col"=>"permclass",           "type"=>"I" ),
                       array( "col"=>"eReadAccess",         "type"=>"S" ),
        ));
    }

    static function kfrelFldSLAccession()
    {
        return( array( array( "col"=>"fk_sl_pcv",           "type"=>"K" ),
                       array( "col"=>"spec",                "type"=>"S" ),   // e.g. tomato colour, bush/pole bean

                       array( "col"=>"batch_id",            "type"=>"S" ),
                       array( "col"=>"location",            "type"=>"S" ),
                       array( "col"=>"parent_src",          "type"=>"S" ),
                       array( "col"=>"parent_acc",          "type"=>"I" ),

                       array( "col"=>"g_original",          "type"=>"F" ),
                       array( "col"=>"g_have",              "type"=>"F" ),
                       array( "col"=>"g_pgrc",              "type"=>"F" ),
                       array( "col"=>"bDeAcc",              "type"=>"I" ),

                       array( "col"=>"notes",               "type"=>"S" ),

                       array( "col"=>"oname",               "type"=>"S" ),
                       array( "col"=>"x_member",            "type"=>"S" ),   // source of seeds - should just be a string?
                       array( "col"=>"x_d_harvest",         "type"=>"S" ),   // should be a date, except some are ranges and guesses
                       array( "col"=>"x_d_received",        "type"=>"S" ),   // should be a date, except some are ranges and guesses

                       array( "col"=>"psp_obsolete",        "type"=>"S" ) ) );
    }

    static function kfrelFldSLInventory()
    {
        return( array( array( "col"=>"fk_sl_collection",    "type"=>"K" ),
                       array( "col"=>"fk_sl_accession",     "type"=>"K" ),
                       array( "col"=>"inv_number",          "type"=>"I" ),
                       array( "col"=>"g_weight",            "type"=>"S" ),
                       array( "col"=>"location",            "type"=>"S" ),
                       array( "col"=>"parent_kInv",         "type"=>"K" ),
                       array( "col"=>"dCreation",           "type"=>"S" ),
                       array( "col"=>"bDeAcc",              "type"=>"I" ),
        ));
    }

    static function kfrelFldSLAdoption()
    {
        return( array( array( "col"=>"fk_mbr_contacts",     "type"=>"K" ),
                       array( "col"=>"donor_name",          "type"=>"S" ),
                       array( "col"=>"public_name",         "type"=>"S" ),
                       array( "col"=>"amount",              "type"=>"F" ),
                       array( "col"=>"sPCV_request",        "type"=>"S" ),
                       array( "col"=>"d_donation",          "type"=>"S" ),
       array( "col"=>"x_d_donation",        "type"=>"S" ),   // remove when migrated to date

                       array( "col"=>"fk_sl_pcv",           "type"=>"K" ),
                       array( "col"=>"notes",               "type"=>"S" ),

                       array( "col"=>"bDoneCV",             "type"=>"I" ),
                       array( "col"=>"bDoneHaveSeed",       "type"=>"I" ),
                       array( "col"=>"bDoneBulkStored",     "type"=>"I" ),
                       array( "col"=>"bDoneAvail",          "type"=>"I" ),
                       array( "col"=>"bDoneBackup",         "type"=>"I" ),

                       array( "col"=>"bAckDonation",        "type"=>"I" ),
                       array( "col"=>"bAckCV",              "type"=>"I" ),
                       array( "col"=>"bAckHaveSeed",        "type"=>"I" ),
                       array( "col"=>"bAckBulkStored",      "type"=>"I" ),
                       array( "col"=>"bAckAvail",           "type"=>"I" ),
                       array( "col"=>"bAckBackup",          "type"=>"I" ) )
              );
    }

    static function kfrelFldSLGerm()
    {
        return( array( array( "col"=>"fk_sl_inventory",     "type"=>"S" ),
                       array( "col"=>"dStart",              "type"=>"S" ),
                       array( "col"=>"dEnd",                "type"=>"S" ),
                       array( "col"=>"nSown",               "type"=>"I" ),
                       array( "col"=>"nGerm",               "type"=>"I" ),
                       array( "col"=>"notes",               "type"=>"S" ) )
              );
    }


    /**********************
        Rosetta
     */
    static function kfrelFldSLSpecies()
    {
        return( array( array( "col"=>"psp",                 "type"=>"S" ),
                       array( "col"=>"name_en",             "type"=>"S" ),
                       array( "col"=>"name_fr",             "type"=>"S" ),
                       array( "col"=>"name_bot",            "type"=>"S" ),
                       array( "col"=>"iname_en",            "type"=>"S" ),
                       array( "col"=>"iname_fr",            "type"=>"S" ),
                       array( "col"=>"family_en",           "type"=>"S" ),
                       array( "col"=>"family_fr",           "type"=>"S" ),
                       array( "col"=>"category",            "type"=>"S" ),
                       array( "col"=>"notes",               "type"=>"S" ) )
              );
    }

    static function kfrelFldSLPCV()
    {
        return( array( array( "col"=>"fk_sl_species",       "type"=>"K" ),
                       array( "col"=>"psp",                 "type"=>"S" ),
                       array( "col"=>"name",                "type"=>"S" ),
                       array( "col"=>"t",                   "type"=>"I" ),
                       array( "col"=>"notes",               "type"=>"S" ) )
                // sound_* are not here because they're only used during rebuild-index and associated manual steps
              );
    }

    static function kfrelFldSLSpeciesSyn()
    {
        return( array( array( "col"=>"fk_sl_species",       "type"=>"K" ),
                       array( "col"=>"name",                "type"=>"S" ),
                       array( "col"=>"t",                   "type"=>"I" ),
                       array( "col"=>"notes",               "type"=>"S" ) )
        );
    }

    static function kfrelFldSLPCVSyn()
    {
        return( array( array( "col"=>"fk_sl_pcv",           "type"=>"K" ),
                       array( "col"=>"name",                "type"=>"S" ),
                       array( "col"=>"t",                   "type"=>"I" ),
                       array( "col"=>"notes",               "type"=>"S" ) )
        );
    }


    /**********************
        Sources
     */
    static function kfrelFldSLSources()
    {
        return( array( array("col"=>"sourcetype",    "type"=>"S"),
                       array("col"=>"name_en",       "type"=>"S"),
                       array("col"=>"name_fr",       "type"=>"S"),
                       array("col"=>"addr_en",       "type"=>"S"),
                       array("col"=>"addr_fr",       "type"=>"S"),
                       array("col"=>"city",          "type"=>"S"),
                       array("col"=>"prov",          "type"=>"S"),
                       array("col"=>"country",       "type"=>"S", "default"=>"Canada"),
                       array("col"=>"postcode",      "type"=>"S"),
                       array("col"=>"phone",         "type"=>"S"),
                       //array("col"=>"fax",           "type"=>"S"),  not our job to keep track of this - look it up on their web site
                       array("col"=>"web",           "type"=>"S"),
                       array("col"=>"web_alt",       "type"=>"S"),
                       array("col"=>"email",         "type"=>"S"),
                       array("col"=>"email_alt",     "type"=>"S"),
                       array("col"=>"desc_en",       "type"=>"S"),
                       array("col"=>"desc_fr",       "type"=>"S"),
                       array("col"=>"year_est",      "type"=>"I"),
                       array("col"=>"comments",      "type"=>"S"),
                       array("col"=>"bShowCompany",  "type"=>"I"),
                       array("col"=>"bSupporter",    "type"=>"I"),
                       array("col"=>"tsVerified",    "type"=>"S"),
                       array("col"=>"bNeedVerify",   "type"=>"I"),
                       array("col"=>"bNeedProof",    "type"=>"I"),
                       array("col"=>"bNeedXlat",     "type"=>"I") )
              );
    }

    static function kfrelFldSLSourcesCV()
    {
        return( array( array("col"=>"fk_sl_sources", "type"=>"K"),
                       array("col"=>"fk_sl_pcv",     "type"=>"K"),
                       array("col"=>"osp",           "type"=>"S"),
                       array("col"=>"ocv",           "type"=>"S"),
                       array("col"=>"bOrganic",      "type"=>"I"),
                       array("col"=>"notes",         "type"=>"S"),
        )
            // fk_sl_species and sound* are not here because they're only used during rebuild-index and its associated manual steps
        );
    }
}


class SLDB_Base extends sldb__base
/**************
    Implement base kfrels and fetches (extend this class to add joined relations)
 */
{
    public $kfdb;
    public $uid;

    protected $raKfrel = array();   // kfrels go here indexed by their relation code. Derived classes just add more.
    protected $tDef = array();      // table defs for building kfreldefs. Derived classes just add more.

    function __construct( KeyFrameDB $kfdb, $uid )
    {
        $this->kfdb = $kfdb;
        $this->uid = $uid;

        $this->initKfrel();
    }

    function GetKfrel( $sRel ) { return( @$this->raKfrel[$sRel] ); }

    // these return a kfr with one result pre-loaded
    function GetKFR( $sRel, $k )  { return( ($kfrel = @$this->raKfrel[$sRel]) ? $kfrel->GetRecordFromDBKey( $k ) : null ); }
    function GetKFRCond( $sRel, $sCond, $raKFParms = array() )  { return( ($kfrel = @$this->raKfrel[$sRel]) ? $kfrel->GetRecordFromDB( $sCond, $raKFParms ) : null ); }

    // this returns a kfrc that needs CursorFetch to load the first result
    function GetKFRC( $sRel, $sCond = "", $raKFParms = array() ) { return( ($kfrel = @$this->raKfrel[$sRel]) ? $kfrel->CreateRecordCursor( $sCond, $raKFParms ) : null ); }

    function GetList( $sRel, $sCond, $raKFParms = array() )
    {
        return( ($kfrel = @$this->raKfrel[$sRel]) ? $this->getList_( $kfrel, $sCond, $raKFParms ) : array() );
    }

    protected function getlist_( KeyFrameRelation $kfrel, $sCond, $raKFParms )
    {
        return( $kfrel->GetRecordSetRA( $sCond, $raKFParms ) );
    }

    protected function newKfrel( $def, $sLogFile )
    {
        $kfrel = new KeyFrameRelation( $this->kfdb, $def, $this->uid );
        $kfrel->SetLogFile( SITE_LOG_ROOT.$sLogFile );
        return( $kfrel );
    }

    protected function newKfrel2( $raTableDefs, $sLogFile )
    {
        return( $this->newKfrel( array( "ver" => 2, "Tables" => $raTableDefs ), $sLogFile ) );
    }

    private function initKfrel()
    {
        $this->tDef['C'] = array( "Table" => "seeds.sl_collection", "Alias" => "C", "Fields" => SLDB_base::kfrelFldSLCollection() );
        $this->tDef['I'] = array( "Table" => "seeds.sl_inventory",  "Alias" => "I", "Fields" => SLDB_base::kfrelFldSLInventory() );
        $this->tDef['A'] = array( "Table" => "seeds.sl_accession",  "Alias" => "A", "Fields" => SLDB_base::kfrelFldSLAccession() );
        $this->tDef['D'] = array( "Table" => "seeds.sl_adoption",   "Alias" => "D", "Fields" => SLDB_base::kfrelFldSLAdoption() );
        $this->tDef['G'] = array( "Table" => "seeds.sl_germ",       "Alias" => "G", "Fields" => SLDB_base::kfrelFldSLGerm() );
        $this->tDef['P'] = array( "Table" => "seeds.sl_pcv",        "Alias" => "P", "Fields" => SLDB_base::kfrelFldSLPCV() );
        $this->tDef['S'] = array( "Table" => "seeds.sl_species",    "Alias" => "S", "Fields" => SLDB_base::kfrelFldSLSpecies() );
        $this->tDef['PY'] = array("Table" => "seeds.sl_pcv_syn",    "Alias" => "PY","Fields" => SLDB_base::kfrelFldSLPCVSyn() );
        $this->tDef['SY'] = array("Table" => "seeds.sl_species_syn","Alias" => "SY","Fields" => SLDB_base::kfrelFldSLSpeciesSyn() );

        $this->raKfrel['C'] = $this->newKfrel2( array( "C" => $this->tDef['C'] ), "slinventory.log" );
        $this->raKfrel['I'] = $this->newKfrel2( array( "I" => $this->tDef['I'] ), "slinventory.log" );
        $this->raKfrel['A'] = $this->newKfrel2( array( "A" => $this->tDef['A'] ), "slinventory.log" );
        $this->raKfrel['D'] = $this->newKfrel2( array( "D" => $this->tDef['D'] ), "slinventory.log" );
        $this->raKfrel['G'] = $this->newKfrel2( array( "G" => $this->tDef['G'] ), "slinventory.log" );

        $this->raKfrel['P'] = $this->newKfrel2( array( "P" => $this->tDef['P'] ),  "slrosetta.log" );
        $this->raKfrel['S'] = $this->newKfrel2( array( "S" => $this->tDef['S'] ),  "slrosetta.log" );
        $this->raKfrel['PY']= $this->newKfrel2( array( "PY"=> $this->tDef['PY'] ), "slrosetta.log" );
        $this->raKfrel['SY']= $this->newKfrel2( array( "SY"=> $this->tDef['SY'] ), "slrosetta.log" );
    }
}

// replace this with SLDB_Collection

class SLDB_Master extends SLDB_Base
/****************
    Joined relations
 */
{
    protected $kfrelAxD = null;  // Accession X Adoption
    protected $kfrelA_D = null;  // Accession left join Adoption (not all accessions are adopted)
    protected $kfrelD_A = null;  // Adoption left join Accession (not all adopted varieties have been accessioned)

    private $kfrelA_P = null;      // for creating/managing A records that might be erroneously unnamed
    private $kfrelIxA_P = null;    // for creating/managing IxA records that might be erroneously unnamed

    function __construct( KeyFrameDB $kfdb, $uid )
    {
        parent::__construct( $kfdb, $uid );

        // in php 5.4 you can have a private method in a derived class with THE SAME NAME as a private method in a base class, and they each
        // get called in their respective classes
        $this->initKfrel();
    }

    function GetKfrelA_P()    { return( $this->kfrelA_P ); }
    function GetKfrelIxA_P()  { return( $this->kfrelIxA_P ); }

    function GetListA_P($sCond, $raKFParms = array())     { return( $this->getList_( $this->kfrelA_P, $sCond, $raKFParms ) ); }
    function GetListIxA_P($sCond, $raKFParms = array())   { return( $this->getList_( $this->kfrelIxA_P, $sCond, $raKFParms ) ); }

    private function initKfrel()
    {
        $this->raKfrel['IxA']     = $this->newKfrel2( array( "I"=>$this->tDef['I'], "A"=>$this->tDef['A'] ), "slinventory.log" );

        $this->raKfrel['AxPxS']   = $this->newKfrel2( array( "A"=>$this->tDef['A'], "S"=>$this->tDef['S'], "P"=>$this->tDef['P'] ), "slinventory.log" );

        $this->raKfrel['IxAxPxS'] = $this->newKfrel2( array( "I"=>$this->tDef['I'], "S"=>$this->tDef['S'], "P"=>$this->tDef['P'], "A"=>$this->tDef['A'] ), "slinventory.log" );

        $this->raKfrel['IxGxAxPxS'] = $this->newKfrel2( array( "I"=>$this->tDef['I'], "G"=>$this->tDef['G'], "S"=>$this->tDef['S'], "P"=>$this->tDef['P'], "A"=>$this->tDef['A'] ), "slinventory.log" );

        $this->raKfrel['PxS']     = $this->newKfrel2( array( "P"=>$this->tDef['P'], "S"=>$this->tDef['S'] ), "slrosetta.log" );

        $this->raKfrel['SYxS']    = $this->newKfrel2( array( "SY"=>$this->tDef['SY'], "S"=>$this->tDef['S'] ), "slrosetta.log" );


        $this->kfrelA_P = $this->newKfrel(
                array( "Tables" => array( array( "Table" => "seeds.sl_accession",
                                                 "Alias" => "A",
                                                 "Type"  => "Base",
                                                 "Fields" => SLDB_base::kfrelFldSLAccession() ),
                                          array( "Table" => "seeds.sl_pcv",
                                                 "Alias" => "P",
                                                 "Type"  => "LEFT JOIN",
                                                 "LeftJoinOn" => "A.fk_sl_pcv=P._key",
                                                 "Fields" => SLDB_base::kfrelFldSLPCV() ) ) ),
                "slinventory.log" );

        $this->kfrelIxA_P = $this->newKfrel(
                array( "Tables" => array( array( "Table" => "seeds.sl_inventory",
                                                 "Alias" => "I",
                                                 "Type"  => "Base",
                                                 "Fields" => SLDB_base::kfrelFldSLInventory() ),
                                          array( "Table" => "seeds.sl_accession",
                                                 "Alias" => "A",
                                                 "Type"  => "Join",
                                                 "Fields" => SLDB_base::kfrelFldSLAccession() ),
                                          array( "Table" => "seeds.sl_pcv",
                                                 "Alias" => "P",
                                                 "Type"  => "LEFT JOIN",
                                                 "LeftJoinOn" => "A.fk_sl_pcv=P._key",
                                                 "Fields" => SLDB_base::kfrelFldSLPCV() ) ) ),
                "slinventory.log" );
    }
}

class SLDB_Rosetta extends SLDB_Base
{
    function __construct( KeyFrameDB $kfdb, $uid )
    {
        parent::__construct( $kfdb, $uid );

        // in php 5.4 you can have a private method in a derived class with THE SAME NAME as a private method in a base class, and they each
        // get called in their respective classes
        $this->initKfrel();
    }

    private function initKfrel()
    {
        $this->raKfrel['PxS']    = $this->newKfrel2( array( "P"=>$this->tDef['P'], "S"=>$this->tDef['S'] ), "slrosetta.log" );

        // kluge put S before P to resolve forward fk dependency
        // $this->raKfrel['PYxPxS'] = $this->newKfrel2( array( "PY"=>$this->tDef['PY'], "P"=>$this->tDef['P'], "S"=>$this->tDef['S'] ), "slrosetta.log" );
        $this->raKfrel['PYxPxS'] = $this->newKfrel2( array( "PY"=>$this->tDef['PY'], "S"=>$this->tDef['S'],"P"=>$this->tDef['P'] ), "slrosetta.log" );
        $this->raKfrel['SYxS']   = $this->newKfrel2( array( "SY"=>$this->tDef['SY'], "S"=>$this->tDef['S'] ), "slrosetta.log" );

    }
}

class SLDB_Collection extends SLDB_Rosetta
{
    function __construct( KeyFrameDB $kfdb, $uid )
    {
        parent::__construct( $kfdb, $uid );

        // in php 5.4 you can have a private method in a derived class with THE SAME NAME as a private method in a base class, and they each
        // get called in their respective classes
        $this->initKfrel();
    }

    private function initKfrel()
    {
        $this->raKfrel['IxA']     = $this->newKfrel2( array( "I"=>$this->tDef['I'], "A"=>$this->tDef['A'] ), "slinventory.log" );
    }
}

class SLDB_Sources extends SLDB_Base
{
    function __construct( KeyFrameDB $kfdb, $uid )
    {
        parent::__construct( $kfdb, $uid );

        // in php 5.4 you can have a private method in a derived class with THE SAME NAME as a private method in a base class, and they each
        // get called in their respective classes
        $this->initKfrel();
    }

    private function initKfrel()
    {
        $this->tDef['SRC']   = array( "Table" => "seeds.sl_sources",    "Alias" => "SRC",   "Fields" => SLDB_base::kfrelFldSLSources() );
        $this->tDef['SRCCV'] = array( "Table" => "seeds.sl_cv_sources", "Alias" => "SRCCV", "Fields" => SLDB_base::kfrelFldSLSourcesCV() );

        $this->raKfrel['SRC']       = $this->newKfrel2( array( "SRC"=>$this->tDef['SRC'] ), "slsources.log" );
        $this->raKfrel['SRCCV']     = $this->newKfrel2( array( "SRCCV"=>$this->tDef['SRCCV'] ), "slsources.log" );
        $this->raKfrel['SRCCVxSRC'] = $this->newKfrel2( array( "SRCCV"=>$this->tDef['SRCCV'], "SRC"=>$this->tDef['SRC'] ), "slsources.log" );
        $this->raKfrel['SRCCVxPxS'] = $this->newKfrel2( array( "SRCCV"=>$this->tDef['SRCCV'], "S"=>$this->tDef['S'], "P"=>$this->tDef['P'] ), "slsources.log" );

        // kluge: Forward dependencies are not allowed in ON() clauses and this causes two of them: SRCCV.fk_sl_pcv and P.fk_sl_species
        //        The first gets solved by kfrelation because it has to put the condition in a WHERE clause, because SRCCV is the first table (no place for ON)
        //        The second is solved here by putting S before P
        $this->raKfrel['SRCCVxSRCxPxS'] = $this->newKfrel2( array( "SRCCV"=>$this->tDef['SRCCV'], "SRC"=>$this->tDef['SRC'],
                                                                   //"P"=>$this->tDef['P'], "S"=>$this->tDef['S'] ),
                                                                   "S"=>$this->tDef['S'], "P"=>$this->tDef['P'] ),
                                                                   "slsources.log" );

        // every SrcCV must have a Src, but it might not have a PCV
        $this->raKfrel['SRCCVxSRC_P'] = $this->newKfrel(
                array( "Tables" => array( array( "Table" => "seeds.sl_cv_sources",
                                                 "Alias" => "SRCCV",
                                                 "Type"  => "Base",
                                                 "Fields" => SLDB_base::kfrelFldSLSourcesCV() ),

                                          array( "Table" => "seeds.sl_pcv",
                                                 "Alias" => "P",
                                                 "Type"  => "LEFT JOIN",
                                                 "LeftJoinOn" => "SRCCV.fk_sl_pcv=P._key",
                                                 "Fields" => SLDB_base::kfrelFldSLPCV() ),
                array( "Table" => "seeds.sl_sources",
                                                 "Alias" => "SRC",
                                                 "Type"  => "Join",
                                                 "Fields" => SLDB_base::kfrelFldSLSources() ), ) ),
                "slsources.log" );
    }
}

/********************************************
    ALL OF THE ABOVE IS IN sldb.php
 */



class _sldb_base
/***************
    Base class for SLDB objects. Each relation has its own derived class, and should have methods like:
        SLDB_Foo()      - constructor
        GetListFoo()    - optional wrapper for GetList, makes client code more readable
        getFilterRA()   - if you want to define any non-standard filters
        initKFRel()     - override construction of kfrel to implement the derived class's main data model
        initKFRelBar()  - if you want a secondary kfrel in the object
        GetKFRelBar()   - if you want to expose a secondary kfrel in the object
        GetListBar()    - for example, to implement secondary kfrel
 */
{
    var $kfdb;
    var $uid;
    var $kfrel;     // kfrel for the main relation managed by a derived object

    function __construct( KeyFrameDB $kfdb, $uid = 0 )
    {
        $this->kfdb = $kfdb;
        $this->uid = $uid;
        $this->initKFRel();
    }

    function GetKFRel()  { return( $this->kfrel ); }

    function GetList( $raFlt = array(), $raParmsKFRC = array() )
    /***********************************************************
        Return a list of rows filtered by the given parms.
     */
    {
        $ra = array();
        if( ($kfrc = $this->GetRecordCursor( $raFlt, $raParmsKFRC )) ) {
            while( $kfrc->CursorFetch() ) {
                $ra[] = $kfrc->ValuesRA();
            }
        }
        return( $ra );
    }

    function GetListByCond( $sCond, $raParmsKFRC = array() )
    {
        $ra = array();
        if( ($kfrc = $this->GetCursor( $sCond, $raParmsKFRC )) ) {
            while( $kfrc->CursorFetch() ) {
                $ra[] = $kfrc->ValuesRA();
            }
        }
        return( $ra );
    }

    function GetRecordByKey( $k )
    {
        return( $this->kfrel->GetRecordFromDBKey( $k ) );
    }

    function GetCursor( $sCond, $raParmsKFRC = array() )
    {
        return( $this->kfrel->CreateRecordCursor( $sCond, $raParmsKFRC ) );
    }

    function GetRecordCursor( $raFlt = array(), $raParmsKFRC = array() )
    /*******************************************************************
        Return a kfrc using the same parms as GetList
     */
    {
        $raCond = $this->getFilterRA( $raFlt );
        return( $this->kfrel->CreateRecordCursor( implode(" AND ", $raCond), $raParmsKFRC ) );
    }

    function getFilterRA( $raFlt )
    /*****************************
        Return an array of SQL conditions that filter a desired set of rows based on the $raFlt
     */
    {
        $raCond = $this->getFilterRAStd( $raFlt, $this->kfrel );
        // You need to put conditions here in derived class

        return( $raCond );
    }

    function getFilterRAStd( $raFlt, &$kfrel )
    /*****************************************
        See standard filter specs at top of file.
        The kfrel is required to get the table aliases and column names for filters.
     */
    {
        $raCond = array();

        if( isset($raFlt['kPCV']) ) {   // 0 is a valid value (searching for adoptions that have not been assigned a cultivar)
            $col = "";
            if( $kfrel->GetBaseTableName() == 'sl_pcv' ) {
                $col = $kfrel->GetDBColName("sl_pcv", "_key");
            } else if( $kfrel->GetDBTableAlias("sl_adoption") ) {  // detect whether the table is in the relation
                $col = $kfrel->GetDBColName("sl_adoption", "fk_sl_pcv");
            } else if( $kfrel->GetDBTableAlias("sl_accession") ) {
                $col = $kfrel->GetDBColName("sl_accession", "fk_sl_pcv");
            }
            if( $col )  $raCond[] = $col."='".intval($raFlt['kPCV'])."'";
        }

        if( isset($raFlt['kAdoptMbr']) ) {   // 0 is a valid value (searching for adoptions that have not been assigned a mbr_contacts key)
            $raCond[] = $kfrel->GetDBColName("sl_adoption", "fk_mbr_contacts" )."='".addslashes($raFlt['kAdoptMbr'])."'";
        }

        if( @$raFlt['sAdoptDonorLike'] ) {
            $sSrch = " LIKE '%".addslashes($raFlt['sAdoptDonorLike'])."%'";
            $raCond[] = "(".$kfrel->GetDBColName("sl_adoption", "donor_name").$sSrch." OR "
                           .$kfrel->GetDBColName("sl_adoption", "public_name").$sSrch.")";
        }
        if( @$raFlt['sAdoptRequestLike'] ) {
            $raCond[] = $kfrel->GetDBColName("sl_adoption", "sPCV_request")." LIKE '%".addslashes($raFlt['sAdoptRequestCV'])."%'";
        }
        if( @$raFlt['sDP_RequestLikeOrPNameLike'] ) {
            $sSrch = " LIKE '%".addslashes($raFlt['sDP_RequestLikeOrPNameLike'])."%'";
            $raCond[] = "(".$kfrel->GetDBColName("sl_adoption", "sPCV_request").$sSrch." OR "
                           .$kfrel->GetDBColName("sl_pcv", "name").$sSrch.")";
        }

        if( @$raFlt['sPSP'] )       $raCond[] = $kfrel->GetDBColName("sl_pcv", "psp" )."='".addslashes($raFlt['sPSP'])."'";
        if( @$raFlt['sPName'] )     $raCond[] = $kfrel->GetDBColName("sl_pcv", "name" )."='".addslashes($raFlt['sPName'])."'";
        if( @$raFlt['sPSPLike'] )   $raCond[] = $kfrel->GetDBColName("sl_pcv", "psp" )." LIKE '%".addslashes($raFlt['sPSPLike'])."%'";
        if( @$raFlt['sPNameLike'] ) $raCond[] = $kfrel->GetDBColName("sl_pcv", "name" )." LIKE '%".addslashes($raFlt['sPNameLike'])."%'";


        // allow client to express a verbatim condition
        if( @$raFlt['sCond'] )      $raCond[] = $raFlt['sCond'];

        return( $raCond );
    }

    function initKFRel()
    /*******************
        Option 1: Override this method and create $this->kfrel
        Option 2: Override initKFRelParms and let this method create $this->kfrel
     */
    {
        list( $kfreldef, $sLogFile ) = $this->initKFRelParms();

        if( empty($kfreldef) || empty($sLogFile) ) die( "SLDB_Base: You have to define the initkfrel parms" );

        $this->kfrel = new KeyFrameRelation( $this->kfdb, $kfreldef, $this->uid );
        $this->kfrel->SetLogFile( SITE_LOG_ROOT.$sLogFile );
    }

    function initKFRelParms() { die("Override initKFRelParms"); }

    /****************************************
     * Field specifications for kfrel defs
     */
// kluge: Accession form allows a string to be typed: SEEDForm doesn't enforce type but KFRecord::_getValsFromRA does
//        so fk_sl_pcv can be 'K' in adoption ListForm (because it uses SEEDForm) but it can't in the old KFUI (because
//        it uses KFRecord to get http parms).  So make this a 'S' type until everything uses SEEDForm
// Also, this shouldn't be static because it would be better for it to be accessed through a method (or not at all from outside)

    static $kfrelFldSLAccession =
            array( array( "col"=>"fk_sl_pcv",           "type"=>"S" ),//"type"=>"K" ),

                   array( "col"=>"spec",                "type"=>"S" ),   // e.g. tomato colour, bush/pole bean

                   array( "col"=>"batch_id",            "type"=>"S" ),
                   array( "col"=>"location",            "type"=>"S" ),
                   array( "col"=>"parent_src",          "type"=>"S" ),
                   array( "col"=>"parent_acc",          "type"=>"I" ),

                   array( "col"=>"g_original",          "type"=>"F" ),
                   array( "col"=>"g_have",              "type"=>"F" ),
                   array( "col"=>"g_pgrc",              "type"=>"F" ),
                   array( "col"=>"bDeAcc",              "type"=>"I" ),

                   array( "col"=>"notes",               "type"=>"S" ),

                   array( "col"=>"oname",               "type"=>"S" ),
                   array( "col"=>"x_member",            "type"=>"S" ),   // source of seeds - should just be a string?
                   array( "col"=>"x_d_harvest",         "type"=>"S" ),   // should be a date, except some are ranges and guesses
                   array( "col"=>"x_d_received",        "type"=>"S" ),   // should be a date, except some are ranges and guesses

                   array( "col"=>"psp_obsolete",        "type"=>"S" ) );


    static function kfrelFldSLInventory()
    {
        return( array( array( "col"=>"fk_sl_accession",     "type"=>"K" ),
                       array( "col"=>"g_weight",            "type"=>"S" ),
                       array( "col"=>"location",            "type"=>"S" ),
                       array( "col"=>"parent_kInv",         "type"=>"K" ),
                       array( "col"=>"dCreation",           "type"=>"S" ),
                       array( "col"=>"bDeAcc",              "type"=>"I" ),
        ));
    }

    static function kfrelFldSLAdoption()
    {
        return( array( array( "col"=>"fk_mbr_contacts",     "type"=>"K" ),
                       array( "col"=>"donor_name",          "type"=>"S" ),
                       array( "col"=>"public_name",         "type"=>"S" ),
                       array( "col"=>"amount",              "type"=>"F" ),
                       array( "col"=>"sPCV_request",        "type"=>"S" ),
                       array( "col"=>"d_donation",          "type"=>"S" ),
       array( "col"=>"x_d_donation",        "type"=>"S" ),   // remove when migrated to date

                       array( "col"=>"fk_sl_pcv",           "type"=>"K" ),
                       array( "col"=>"notes",               "type"=>"S" ),

                       array( "col"=>"bDoneCV",             "type"=>"I" ),
                       array( "col"=>"bDoneHaveSeed",       "type"=>"I" ),
                       array( "col"=>"bDoneBulkStored",     "type"=>"I" ),
                       array( "col"=>"bDoneAvail",          "type"=>"I" ),
                       array( "col"=>"bDoneBackup",         "type"=>"I" ),

                       array( "col"=>"bAckDonation",        "type"=>"I" ),
                       array( "col"=>"bAckCV",              "type"=>"I" ),
                       array( "col"=>"bAckHaveSeed",        "type"=>"I" ),
                       array( "col"=>"bAckBulkStored",      "type"=>"I" ),
                       array( "col"=>"bAckAvail",           "type"=>"I" ),
                       array( "col"=>"bAckBackup",          "type"=>"I" ) )
              );
    }

    static function kfrelFldSLPCV()
    {
        return( array( array( "col"=>"psp",                 "type"=>"S" ),
                       array( "col"=>"name",                "type"=>"S" ),
                       array( "col"=>"notes",               "type"=>"S" ) )
              );
    }

/*
    function kfrelFldSLGerm()
    {
        return( array( array( "col"=>"fk_sl_accession",     "type"=>"S" ),
                       array( "col"=>"dSown",               "type"=>"S" ),
                       array( "col"=>"nSown",               "type"=>"I" ),
                       array( "col"=>"nGerm",               "type"=>"I" ),
                       array( "col"=>"notes",               "type"=>"S" ) )
              );
    }
*/
}


class SLDB_Accession extends _sldb_base
/*******************
    Encapsulate SL Accession db access
 */
{
    function __construct( KeyFrameDB $kfdb, $uid = 0 ) { parent::__construct( $kfdb, $uid ); } // calls $this->initKFRel()

    function initKFRelParms()
    {
        $def = array( "Tables" => array( array( "Table" => "seeds.sl_accession",
                                                "Type"  => "Base",
                                                "Fields" => _sldb_base::$kfrelFldSLAccession ) ) );
        return( array($def, "slaccession.log") );
    }
}


class SLDB_Inventory extends _sldb_base
/*******************
    Encapsulate SL Inventory db access
 */
{
    function __construct( KeyFrameDB $kfdb, $uid = 0 ) { parent::__construct( $kfdb, $uid ); } // calls $this->initKFRel()

    function initKFRelParms()
    {
        $def = array( "Tables" => array( array( "Table" => "seeds.sl_inventory",
                                                "Type"  => "Base",
                                                "Fields" => _sldb_base::kfrelFldSLInventory() ) ) );
        return( array($def, "slinventory.log") );
    }
}


class SLDB_Adoption extends _sldb_base
/******************
    Encapsulate SL Accession db access
 */
{
    function __construct( KeyFrameDB $kfdb, $uid = 0 ) { parent::__construct( $kfdb, $uid ); } // calls $this->initKFRel()

    function initKFRelParms()
    {
        $def = array( "Tables" => array( array( "Table" => "seeds.sl_adoption",
                                                "Type"  => "Base",
                                                "Fields" => _sldb_base::kfrelFldSLAdoption() ) ) );
        return( array($def, "sladoption.log") );
    }
}

/*TODO: call this SLDB_Pcv*/
class SLDB_PCV extends _sldb_base
/*************
    Encapsulate SL PCV db access
 */
{
    function __construct( KeyFrameDB $kfdb, $uid = 0 ) { parent::__construct( $kfdb, $uid ); } // calls $this->initKFRel()

    function initKFRelParms()
    {
        $def = array( "Tables" => array( array( "Table" => "seeds.sl_pcv",
                                                "Type"  => "Base",
                                                "Fields" => _sldb_base::kfrelFldSLPCV() ) ) );
        return( array($def, "slpcv.log") );
    }
}

/*
class SLDB_Germ extends _sldb_base
[**************
    Encapsulate SL Germination db access
 *]
{
    function __construct( KeyFrameDB $kfdb, $uid = 0 ) { parent::__construct( $kfdb, $uid ); } // calls $this->initKFRel()

    function initKFRel()
    {
        $def = array( "Tables" => array( array( "Table" => "seeds.sl_germ",
                                                "Type"  => "Base",
                                                "Fields" => _sldb_base::kfrelFldSLGerm() ) ) );
        return( array($def, "slgerm.log") );
    }
}
*/

class SLDB_AxP extends _sldb_base
/********************************
   Work with Accessions that definitely have a Pcv.  For Accessions editing interfaces (where Pcv could be blank) see SLDB_A_P
 */
{
    function __construct( KeyFrameDB $kfdb, $uid = 0 ) { parent::__construct( $kfdb, $uid ); } // calls $this->initKFRel()

    function initKFRelParms()
    {
        $def = array( "Tables" => array( array( "Table" => "seeds.sl_accession",
                                                "Alias" => "A",
                                                "Type"  => "Base",
                                                "Fields" => _sldb_base::$kfrelFldSLAccession ),
                                         array( "Table" => "seeds.sl_pcv",
                                                "Alias" => "P",
                                                "Type"  => "Join",
                                                "Fields" => _sldb_base::kfrelFldSLPCV() )
                                         ) );
        return( array($def, "slaccession.log") );
    }
}

class SLDB_A_P extends _sldb_base
/********************************
   Work with Accessions whose Pcv might be in flux or blank (e.g. the Accessions entry interface)
 */
{
    function __construct( KeyFrameDB $kfdb, $uid = 0 ) { parent::__construct( $kfdb, $uid ); } // calls $this->initKFRel()

    function initKFRelParms()
    {
        $def = array( "Tables" => array( array( "Table" => "seeds.sl_accession",
                                                "Alias" => "A",
                                                "Type"  => "Base",
                                                "Fields" => _sldb_base::$kfrelFldSLAccession ),
                                         array( "Table" => "seeds.sl_pcv",
                                                "Alias" => "P",
                                                "Type"  => "LEFT JOIN",
                                                "LeftJoinOn" => "A.fk_sl_pcv=P._key",
                                                "Fields" => _sldb_base::kfrelFldSLPCV() )
                                         ) );
        return( array($def, "slaccession.log") );
    }
}

class SLDB_IxAxP extends _sldb_base
{
    function __construct( KeyFrameDB $kfdb, $uid = 0 ) { parent::__construct( $kfdb, $uid ); } // calls $this->initKFRel()

    function initKFRelParms()
    {
        $def = array( "Tables" => array( array( "Table" => "seeds.sl_inventory",
                                                "Alias" => "I",
                                                "Type"  => "Base",
                                                "Fields" => _sldb_base::kfrelFldSLInventory() ),
                                         array( "Table" => "seeds.sl_accession",
                                                "Alias" => "A",
                                                "Type"  => "Join",
                                                "Fields" => _sldb_base::$kfrelFldSLAccession ),
                                         array( "Table" => "seeds.sl_pcv",
                                                "Alias" => "P",
                                                "Type"  => "Join",
                                                "Fields" => _sldb_base::kfrelFldSLPCV() )
                                         ) );
        return( array($def, "slinventory.log") );
    }
}

class SLDB_IxA_P extends _sldb_base
/**********************************
   Work with Inventory/Accessions whose Pcv might be in flux or blank (e.g. the Accessions entry interface)
 */
{
    function __construct( KeyFrameDB $kfdb, $uid = 0 ) { parent::__construct( $kfdb, $uid ); } // calls $this->initKFRel()

    function initKFRelParms()
    {
        $def = array( "Tables" => array( array( "Table" => "seeds.sl_inventory",
                                                "Alias" => "I",
                                                "Type"  => "Base",
                                                "Fields" => _sldb_base::kfrelFldSLInventory() ),
                                         array( "Table" => "seeds.sl_accession",
                                                "Alias" => "A",
                                                "Type"  => "Join",
                                                "Fields" => _sldb_base::$kfrelFldSLAccession ),
                                         array( "Table" => "seeds.sl_pcv",
                                                "Alias" => "P",
                                                "Type"  => "LEFT JOIN",
                                                "LeftJoinOn" => "A.fk_sl_pcv=P._key",
                                                "Fields" => _sldb_base::kfrelFldSLPCV() )
                                         ) );
        return( array($def, "slinventory.log") );
    }
}

class SLDB_PD extends _sldb_base
/************
    sl_pcv LEFT JOIN sl_adoption
 */
{
    function __construct( KeyFrameDB $kfdb, $uid = 0 ) { parent::__construct( $kfdb, $uid ); } // calls $this->initKFRel()

    function initKFRelParms()
    {
        $def = array( "Tables" => array( array( "Table" => "seeds.sl_pcv",
                                                "Alias" => "P",
                                                "Type"  => "Base",
                                                "Fields" => _sldb_base::kfrelFldSLPCV() ),
                                         array( "Table" => "seeds.sl_adoption",
                                                "Alias" => "D",
                                                "Type"  => "LEFT JOIN",
                                                "LeftJoinOn" => "D.fk_sl_pcv=P._key",
                                                "Fields" => _sldb_base::kfrelFldSLAdoption() ) ) );
        return( array($def, "slpcv.log") );
    }
}

class SLDB_PA extends _sldb_base
/************
    sl_pcv LEFT JOIN sl_accession
 */
{
    function __construct( KeyFrameDB $kfdb, $uid = 0 ) { parent::__construct( $kfdb, $uid ); } // calls $this->initKFRel()

    function initKFRelParms()
    {
        $def = array( "Tables" => array( array( "Table" => "seeds.sl_pcv",
                                                "Alias" => "P",
                                                "Type"  => "Base",
                                                "Fields" => _sldb_base::kfrelFldSLPCV() ),
                                         array( "Table" => "seeds.sl_accession",
                                                "Alias" => "A",
                                                "Type"  => "LEFT JOIN",
                                                "LeftJoinOn" => "A.fk_sl_pcv=P._key",
                                                "Fields" => _sldb_base::$kfrelFldSLAccession ) ) );
        return( array($def, "slpcv.log") );
    }
}

class SLDB_PDA
/*************
 */
{
    var $oSLDB_PD;
    var $oSLDB_PA;

    function __construct( KeyFrameDB $kfdb, $uid = 0 )
    {
        $this->oSLDB_PD = new SLDB_PD( $kfdb, $uid );
        $this->oSLDB_PA = new SLDB_PA( $kfdb, $uid );
    }

    function GetPDA( $raFltPD = array(), $raFltPA = array(), $raParmsKFRC_PD = array(), $raParmsKFRC_PA = array() )
    // It actually doesn't work well to filter the results this way because there's no mechanism for cross-filtering.
    // i.e. you can set a filter for adoptions, but then you get all the pcvs from the accessions.
    // A solution would be to generate the intersection set of pcv X D and pcv X A, then just record information pertaining to that set.
    // That's a lot of processing for a filtering job that could be done in other ways.
    {
        $raPD = array(); // broken $this->oSLDB_PD->GetListPD( $raFltPD, $raParmsKFRC_PD );
        $raPA = array(); // broken $this->oSLDB_PA->GetListPA( $raFltPA, $raParmsKFRC_PA );

        /* The lists are denormalized with pcv+adoption for each pcv and adoption, and pcv+accession similarly.
         * Normalize to array( kPCV => array( psp, pname, {summarized data}, array(adoptions), array(accessions) ), kPCV => array( psp, ... ) )
         * N.B. A copy of the key is also placed in the value, to allow the caller to use array-sorting functions that destroy the key.
         *      The keys are handy for collating the raPD and raPA arrays, but can be considered temporary.
         */
        $raOut = array();
        foreach( $raPD as $ra ) {
            $kPCV = $ra['_key'];
            if( !isset($raOut[$kPCV]) ) {
                $raOut[$kPCV] = array( 'kPCV' => $kPCV, 'psp' => $ra['psp'], 'name' => $ra['name'], 'nAdoption' => 0,
                                       'raAdoptions' => array(), 'raAccessions' => array() );
            }
            $raOut[$kPCV]['raAdoptions'][] = array( 'amount' => $ra['D_amount'] );
            $raOut[$kPCV]['nAdoption'] += $ra['D_amount'];
        }

        // the pcv stuff should be identical, so just add the accession information
        foreach( $raPA as $ra ) {
            $kPCV = $ra['_key'];
            $raOut[$kPCV]['raAccessions'][] = array( 'g_have' => $ra['A_g_have'] );
            $raOut[$kPCV]['nGHave'] += $ra['A_g_have'];
        }

        return( $raOut );
    }
}


class SLDB_DP extends _sldb_base
/************
    sl_adoption LEFT JOIN sl_pcv
 */
{
    function __construct( KeyFrameDB $kfdb, $uid = 0 ) { parent::__construct( $kfdb, $uid ); } // calls $this->initKFRel()

    function initKFRelParms()
    {
        $def = array( "Tables" => array( array( "Table" => "seeds.sl_adoption",
                                                "Alias" => "D",
                                                "Type"  => "Base",
                                                "Fields" => _sldb_base::kfrelFldSLAdoption() ),
                                         array( "Table" => "seeds.sl_pcv",
                                                "Alias" => "P",
                                                "Type"  => "LEFT JOIN",
                                                "LeftJoinOn" => "D.fk_sl_pcv=P._key",
                                                "Fields" => _sldb_base::kfrelFldSLPCV() ) ) );
        return( array($def, "slpcv.log") );
    }
}

class Priority
{

	public $kfdb;

	function __construct( KeyFrameDB $kfdb )
	{
		$this->kfdb = $kfdb;
	}
	function TestStuff()
	{
		$this->kfdb->SetDebug(2);
		$try = $this->kfdb->QueryRowsRA('Select sl_accession.oname,sl_pcv.psp, sl_accession.g_have,sl_species.name_en From sl_accession Inner Join sl_pcv on sl_accession.fk_sl_pcv=sl_pcv._key Join sl_species on sl_pcv.fk_sl_species = sl_species._key where sl_accession.g_have > 0 and sl_species.seedWeight = 0.0');
		//$spe = $this->kfdb->QueryRA('Select * from sl_pcv Join sl_species on sl_pcv.fk_sl_species = sl_species._key');
		//$acc = $this->kfdb->QueryRowsRA('Select * from sl_accession where g_have > 0');
		$namesRA = array();

		foreach($try as $t){
		    $namesRA[] = array('species' => $t['name_en'],'cultivar' =>$t['oname'],'psp' =>$t['psp']);
		 }
		 //var_dump($namesRA);
		 $uniqueNamesRA =array();
		 foreach($namesRA as $nRA){
		 	//var_dump($nRA);
		 	//var_dump($uniqueNamesRA);
		     if (empty($uniqueNamesRA)){
		         $uniqueNamesRA[] = $nRA;
		         //var_dump($uniqueNamesRA);
		     }else{
		     	$unique = TRUE;
		        foreach($uniqueNamesRA as $uRA){
		        	var_dump($uRA);
		        	//echo $nRA['species'].": ";
		        	//echo $uRA['species']."<br>";
		        	if($uRA['species'] == $nRA['species']){
		         	    $unique = FALSE;
		         	}
		        }
		        if($unique == TRUE){
		        	$uniqueNamesRA[] = $nRA;
		        }
		     }
		 }
		/*$uniqueNamesRA = array_unique($namesRA);*/
		$count = 0;
		foreach($uniqueNamesRA as $u){
			$count ++;
		    echo $count.": ".$u['species']."   -   Cultivar: ".$u['cultivar'].": ".$u['psp']."<br>";
		}
		//var_dump($spe);
		//var_dump($acc);

	}

	function WeightNeeded($species)
    {	//$species is a _key of sl_species
    	/*$this->kfdb->Execute("Alter Table sl_species Add regrowAmt float");
		$this->kfdb->Execute("Update sl_species Set sl_species.seedAmt = 1000 ");
		$this->kfdb->Execute("Update sl_species Set sl_species.seedWeight = 0.0 ");*/

		$sl_species = $this->kfdb->QueryRA("Select seedAmt,seedWeight from sl_species where sl_species._key=$species");
    	$g = $sl_species['seedAmt'] * $sl_species['seedWeight'];
    	return $g; //returns grams, the value represents the amount needed to not have to regrow
    }
    function WeightAvailable($variety,$bGerm)
    {	//$variety is a _key of sl_pcv
		/*$this->kfdb->Execute("Alter Table sl_accession Add germination float");
		$this->kfdb->Execute("Update sl_accession Set sl_accession.germination = 1.0");*/
    	$accession = $this->kfdb->QueryRowsRA("Select g_have,germination from sl_accession where sl_accession.fk_sl_pcv=$variety");
		if ($accession){
			$g = 0;
			foreach ($accession as $acc ){
		    	$g_have = $acc['g_have'];
				$germination = $acc['germination'];
				if($bGerm == TRUE){
		    		$g += $g_have * $germination;
				}else{
		    		$g += $g_have;
				}
			}
		}else{
    		$g = 0;
		}
		return $g; //return weight of good seeds if bGerm = true and weight of all seeds if bGerm = False
    }
    function WeightSufficient($variety)
    {	//$variety is a _key of sl_pcv
    	$sl_pcv = $this->kfdb->QueryRA("Select fk_sl_species from sl_pcv where sl_pcv._key=$variety");
    	$need = $this->WeightNeeded($sl_pcv['fk_sl_species']);
    	$have = $this->WeightAvailable($variety,TRUE);
		if ($need > $have){
		    $bSuff = FALSE;
		}else{
		    $bSuff = TRUE;
		}
		return $bSuff; //return true or false based on weightNeeded and weightAvailable
    }
    function AdoptionPriorities($amount,$species = 0)
    {	//$species = sl_species._key

		if($species == 0){
			$sl_pcv = $this->kfdb->QueryRowsRA("Select _key from sl_pcv");
		}else{
			$sl_pcv = $this->kfdb->QueryRowsRA("Select _key from sl_pcv where sl_pcv.fk_sl_species = $species");
		}
		foreach($sl_pcv as $row){
		    $preAmount = 0;
		    $k = $row['_key'];
		    $sl_adoption = $this->kfdb->QueryRowsRA("Select amount from sl_adoption where sl_adoption.fk_sl_pcv = $k");
		    foreach($sl_adoption as $adopt){
		        $preAmount += $adopt['amount'];
		    }
		    if ($preAmount < 250){
				if($this->WeightSufficient($row['_key']) == TRUE){
					$diff = 250 - $preAmount - $amount; //250 is max donation for a seed
					if ($diff >= 0){
						$adoptPrioritiesArray[] = array('pcvKey' => $row['_key'], 'priority' => $diff);
					}
				}else{
					$diff = 500 - $preAmount - $amount; //500  makes it so priority for non sufficient weight is always higher than sufficient weight
					if ($diff >= 250){
						$adoptPrioritiesArray[] = array('pcvKey' => $row['_key'], 'priority' => $diff);
					}
				}
		    }
		}
		//$adoptPrioritiesArray = array(array('pcvKey','priority'),array('pcvKey','priority'),array('pcvKey','priority'),array('pcvKey','priority'))
		foreach($adoptPrioritiesArray as $c=>$k){
		    $sort_p[]=$k['priority'];
		}
		array_multisort($sort_p,SORT_ASC,$adoptPrioritiesArray);

		$count = 0;
		while ($count < 10){
	    	$top10Array[] = $adoptPrioritiesArray[$count]['pcvKey'];
	    	$count+=1;
		}
		return $top10Array; //returns arry of sl_pcv._key

    }
    function RegrowPriorities($species = 0)
    {	//$species = sl_species._key
		if($species == 0){
			$sl_pcv = $this->kfdb->QueryRowsRA("Select _key from sl_pcv");
		}else{
			$sl_pcv = $this->kfdb->QueryRowsRA("Select _key from sl_pcv where sl_pcv.fk_sl_species = $species");
		}
		foreach($sl_pcv as $row){
		    $preAmount = 0;
		    $k = $row['_key'];
		    $sl_adoption = $this->kfdb->QueryRowsRA("Select amount from sl_adoption where sl_adoption.fk_sl_pcv = $k");
		    foreach($sl_adoption as $adopt){
		        $preAmount += $adopt['amount'];
		    }
		    $diff = 250 - $preAmount;
		    $weight = $this->WeightAvailable($row['_key'],TRUE);
		    if($this->WeightSufficient($row['_key']) == FALSE and $preAmount > 0){
				$regrowPrioritiesArray[] = array('pcvKey' => $row['_key'], 'priority1' => $diff, 'priority2' => $weight);
		    }elseif($this->WeightSufficient($row['_key']) == FALSE and $preAmount = 0){
				$regrowPrioritiesArray[] = array('pcvKey' => $row['_key'], 'priority1' => $diff, 'priority2' => $weight);
		    }
		}
		foreach($regrowPrioritiesArray as $c=>$k){
		    $sort_p1[]=$k['priority1'];
		    $sort_p2[]=$k['priority2'];
		}
		array_multisort($sort_p1,SORT_ASC,$sort_p2,SORT_ASC,$regrowPrioritiesArray);

		$count = 0;
		while ($count < 50){
	    	$top50Array[] = $regrowPrioritiesArray[$count]['pcvKey'];
	    	$count+=1;
		}
		return $top50Array; //returns array of sl_pcv._key
    }
}

function _normalizePSP( $psp )
{
    $psp = strtolower( $psp );
            if( substr($psp,0,8) == 'amaranth' )   $psp = 'amaranth';
            if( substr($psp,0,6) == 'tomato' )   $psp = 'tomato';
            if( substr($psp,0,4) == 'bean' )     $psp = 'bean';
            if( substr($psp,0,7) == 'lettuce' )  $psp = 'lettuce';
            if( substr($psp,0,6) == 'squash' )   $psp = 'squash';
            if( substr($psp,0,6) == 'turnip' )   $psp = 'turnip';
// use in_array to avoid things like peafeather
            if( substr($psp,0,3) == 'pea' )      $psp = 'pea';
            if( substr($psp,0,6) == 'pepper' )      $psp = 'pepper';
            if( substr($psp,0,8) == 'cucumber' )      $psp = 'cucumber';
            if( substr($psp,0,4) == 'corn' )      $psp = 'corn';

            return( $psp );
}

function SLDB_SeedsPerGram( $psp )
{
    $psp = _normalizePSP( $psp );


    $raSeedsPerGram = array(
// from sustainableseedco.com - these numbers reveal approximations when you convert to ounces or pounds
                'artichoke'    => 22,
                'asparagus'    => 34,
                'bean'         => 3,  // range of 2-4 for bush, lima, pole, kidney, pinto, etc
                'bean-soy'     => 6,
                'lentil'       => 21,
                'beet'         => 53,
                'broccoli'     => 317,
                'brussel sprouts' => 282,
                'cabbage'      => 229,
                'cabbage-chinese' => 388,
                'carrot'        => 705,
                'cauliflower'    => 317,
                'celery'        => 2293,
                'chard'         => 53,
                'corn'        => 5,   //'corn-dent'    => 5, 'corn-sweet'    => 7,
                'collards'     => 300,
                'cucumber'     => 34,
                'eggplant'        => 229,
                'gourd'    => 30,
                'amaranth' => 1235,
                'barley'        => 18,
                'buckwheat' => 33,
                'millet'    => 176,
                'oat' => 34,
                'quinoa' => 353,
                'rye' => 18,
                'sorghum' => 35,
                'wheat' => 18,
                'basil' => 564,
                'kale' => 282,
                'kohlrabi' => 247,
                'leek' => 388,
                'lettuce' => 882,
                'melon'        => 389,
                'mustard' => 529,
                'okra' => 18,
                'onion' => 317,
                'parsnip' => 176,
                'pea'          => 4,
                'pepper' => 176,
                'radish' => 94,
                'spinach' => 74,
                'squash' => 6,  // winter=6, summer=9, pumpkin=8
                'sunflower' => 14,
                'tatsoi' => 423,
                'tomato' => 265,
                'tomato-cherry' => 353,
                'turnip' => 335,
                'watermelon' => 14,

        );

    $n = intval(@$raSeedsPerGram[$psp]);
    if( !$n )  $n = 60;

    return( $n );
}

function SLDB_MinPopulation( $psp )
{
    $psp = _normalizePSP( $psp );

    $raMinPop = array(
                'barley'       => 80,
                'bean'         => 40,
                'beet'         => 80,
                'carrot'       => 200,
                'corn'         => 200,
                'cucumber'     => 20,
                'eggplant'     => 80,
                'kale'         => 80,
                'lettuce'      => 20,
                'melon'        => 20,
                'oat'          => 80,
                'pea'          => 40,
                'squash'       => 20,
                'tomato'       => 20,
                'wheat'        => 80,
                'wheat, durum' => 80,
        );

    $n = intval(@$raMinPop[$psp]);
    if( !$n )  $n = 50;

    return( $n );
}

define("SL_DB_TABLE_SL_ACCESSION",
"
CREATE TABLE IF NOT EXISTS sl_accession (
        _key        INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT,
        _created    DATETIME,
        _created_by INTEGER,
        _updated    DATETIME,
        _updated_by INTEGER,
        _status     INTEGER DEFAULT 0,

    fk_sl_pcv   INTEGER NOT NULL,
    spec        TEXT,

    batch_id    VARCHAR(200) NOT NULL DEFAULT '',
    location    VARCHAR(200) NOT NULL DEFAULT '',
    parent_src  TEXT,                                   # description of parent
    parent_acc  INTEGER      NOT NULL DEFAULT 0,        # accession number of parent

    g_original  DECIMAL(12,3) NOT NULL DEFAULT 0,
    g_have      DECIMAL(12,3) NOT NULL DEFAULT 0,
    g_pgrc      DECIMAL(12,3) NOT NULL DEFAULT 0,
    bDeAcc      INTEGER      NOT NULL DEFAULT 0,

    notes       TEXT,

    oname       VARCHAR(200) NOT NULL DEFAULT '',
    x_member    TEXT,                                   # should be fk_mbr_contacts?
    x_d_harvest VARCHAR(200) NOT NULL DEFAULT '',       # should be a date, except some are ranges and guesses
    x_d_received VARCHAR(200) NOT NULL DEFAULT '',      # should be a date, except some are ranges and guesses

    psp_obsolete VARCHAR(200) NOT NULL DEFAULT '',

    INDEX(fk_sl_pcv)
);
"
);

define("SL_DB_TABLE_SL_INVENTORY",
"
CREATE TABLE IF NOT EXISTS sl_inventory (
        _key        INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT,
        _created    DATETIME,
        _created_by INTEGER,
        _updated    DATETIME,
        _updated_by INTEGER,
        _status     INTEGER DEFAULT 0,

    fk_sl_collection INTEGER NOT NULL,
    fk_sl_accession INTEGER NOT NULL,
    inv_number      INTEGER NOT NULL,
    g_weight        DECIMAL(12,3) NOT NULL DEFAULT 0,
    location        VARCHAR(200) NOT NULL DEFAULT '',
    parent_kInv     INTEGER NOT NULL DEFAULT 0,           # 0=original sample or original split; k=inventory item from which this was split
    dCreation       DATE,                                 # when this item was obtained or split

--obsolete
bDeAcc          INTEGER NOT NULL DEFAULT 0,
    eStatus         ENUM('L1-DISTRIB','L1-STORAGE','L2','L3','DEPLETED','DEACCESSIONED'),

    INDEX(fk_sl_collection),
    INDEX(fk_sl_accession),
    INDEX(inv_number),
    INDEX(eStatus)
);
"
);


define("SL_DB_TABLE_SL_ADOPTION",
"
CREATE TABLE IF NOT EXISTS sl_adoption (
        _key        INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT,
        _created    DATETIME,
        _created_by INTEGER,
        _updated    DATETIME,
        _updated_by INTEGER,
        _status     INTEGER DEFAULT 0,

    fk_mbr_contacts INTEGER  NOT NULL DEFAULT 0,        # the mbr_contact of the donor
    donor_name      VARCHAR(200) NOT NULL DEFAULT '',
    public_name     VARCHAR(200) NOT NULL DEFAULT '',
    amount          DECIMAL(7,2) NOT NULL DEFAULT 0,
    sPCV_request    VARCHAR(200) NOT NULL DEFAULT '',       # verbatim what they asked to adopt
    d_donation      DATE     NULL DEFAULT NULL,
    x_d_donation    VARCHAR(200) NOT NULL DEFAULT '',   # should be a date

    fk_sl_pcv       INTEGER  NOT NULL DEFAULT 0,        # 0 == cv not assigned yet
    notes           TEXT,

    bDoneCV         INTEGER  NOT NULL DEFAULT 0,        # we have assigned the CV and it's shown on the web site
    bDoneHaveSeed   INTEGER  NOT NULL DEFAULT 0,        # we have obtained a sample of seeds
    bDoneBulkStored INTEGER  NOT NULL DEFAULT 0,        # we have bulked up and stored seeds
    bDoneAvail      INTEGER  NOT NULL DEFAULT 0,        # we have made the seeds publicly available
    bDoneBackup     INTEGER  NOT NULL DEFAULT 0,        # we have backed up seeds

    bAckDonation    INTEGER  NOT NULL DEFAULT 0,        # we thanked them for the donation
    bAckCV          INTEGER  NOT NULL DEFAULT 0,        # we told them which CV is adopted and on web site
    bAckHaveSeed    INTEGER  NOT NULL DEFAULT 0,        # we told them we obtained a sample of seeds
    bAckBulkStored  INTEGER  NOT NULL DEFAULT 0,        # we told them we bulked up and stored seeds
    bAckAvail       INTEGER  NOT NULL DEFAULT 0,        # we told them the seeds are publicly available
    bAckBackup      INTEGER  NOT NULL DEFAULT 0,        # we told them we backed up seeds

    INDEX(fk_sl_pcv)
);
"
);

define("SL_DB_TABLE_SL_GERM",
"
CREATE TABLE IF NOT EXISTS sl_germ (
        _key        INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT,
        _created    DATETIME,
        _created_by INTEGER,
        _updated    DATETIME,
        _updated_by INTEGER,
        _status     INTEGER DEFAULT 0,

    fk_sl_inventory INTEGER NOT NULL DEFAULT 0,
    dStart          DATE NOT NULL,
    dEnd            DATE NULL,
    nSown           INTEGER NOT NULL DEFAULT 0,
    nGerm           INTEGER NOT NULL DEFAULT 0,
    notes           TEXT,

    INDEX(fk_sl_inventory)
);
"
);

define("SL_DB_TABLE_SL_COLLECTION",
"
CREATE TABLE IF NOT EXISTS sl_collection (
        _key        INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT,
        _created    DATETIME,
        _created_by INTEGER,
        _updated    DATETIME,
        _updated_by INTEGER,
        _status     INTEGER DEFAULT 0,

    name            VARCHAR(200) NOT NULL DEFAULT '',                     # long name of the collection
    uid_owner       INTEGER NOT NULL,                                     # primary admin (there has to be one admin who is non-deletable)
    inv_prefix      VARCHAR(20) NOT NULL,                                 # show this before each inv number
    inv_counter     INTEGER NOT NULL DEFAULT 0,                           # next unique inv number for this collection
    permclass       INTEGER NOT NULL DEFAULT 0,                           # determines who can read/write/admin this collection
    eReadAccess     ENUM('0','PUBLIC','COLLECTORS') NOT NULL DEFAULT '0'  # PUBLIC/COLLECTORS: override read access, 0: only by permclass
);
"
);



/**************************
    Rosetta
 */

define("SL_DB_TABLE_SL_SPECIES",
"
CREATE TABLE IF NOT EXISTS sl_species (
        _key        INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT,
        _created    DATETIME,
        _created_by INTEGER,
        _updated    DATETIME,
        _updated_by INTEGER,
        _status     INTEGER DEFAULT 0,

    psp        VARCHAR(200) NOT NULL DEFAULT '',
    name_en    VARCHAR(200) NOT NULL DEFAULT '',
    name_fr    VARCHAR(200) NOT NULL DEFAULT '',
    iname_en   VARCHAR(200) NOT NULL DEFAULT '',
    iname_fr   VARCHAR(200) NOT NULL DEFAULT '',
    name_bot   VARCHAR(200) NOT NULL DEFAULT '',
    family_en  VARCHAR(200) NOT NULL DEFAULT '',
    family_fr  VARCHAR(200) NOT NULL DEFAULT '',
    category   ENUM('FLOWER','VEG','GRAIN','FRUIT','TREE','MISC') NOT NULL DEFAULT 'MISC',
    notes      TEXT,

    INDEX(psp),
    INDEX(name_en),
    INDEX(name_fr),
    INDEX(iname_en),
    INDEX(iname_fr),
    INDEX(name_bot)
);
"
);

define("SL_DB_TABLE_SL_SPECIES_SYN",
"
CREATE TABLE sl_species_syn (
        _key        INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT,
        _created    DATETIME,
        _created_by INTEGER,
        _updated    DATETIME,
        _updated_by INTEGER,
        _status     INTEGER DEFAULT 0,

    fk_sl_species   INTEGER NOT NULL,
    name            VARCHAR(200) NOT NULL,
    t               INTEGER NOT NULL DEFAULT 0,   -- differentiate sets or types of synonyms
    notes           TEXT,

    INDEX(fk_sl_species),
    INDEX(name)
);
"
);

define("SL_DB_TABLE_SL_SPECIES_MAP",
"
CREATE TABLE sl_species_map (
        _key        INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT,
        _created    DATETIME,
        _created_by INTEGER,
        _updated    DATETIME,
        _updated_by INTEGER,
        _status     INTEGER DEFAULT 0,

    fk_sl_species   INTEGER NOT NULL,
    ns              VARCHAR(200) NOT NULL,
    appname_en      VARCHAR(200) NOT NULL,
    appname_fr      VARCHAR(200) NOT NULL,
    notes           TEXT,

    INDEX(fk_sl_species),
    INDEX(ns),
    INDEX(appname_en),
    INDEX(appname_fr)
);
"
);



define("SL_DB_TABLE_SL_PCV",
"
CREATE TABLE IF NOT EXISTS sl_pcv (
        _key        INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT,
        _created    DATETIME,
        _created_by INTEGER,
        _updated    DATETIME,
        _updated_by INTEGER,
        _status     INTEGER DEFAULT 0,

    fk_sl_species   INTEGER NOT NULL,
    psp             VARCHAR(200) NOT NULL DEFAULT '',
    name            VARCHAR(200) NOT NULL DEFAULT '',
    t               INTEGER NOT NULL DEFAULT 0,   -- reasons name is here 0=manual,1=manual-src-rosetta,2=auto-src-rosetta
    notes           TEXT,

    sound_soundex   VARCHAR(100) NOT NULL DEFAULT '',
    sound_metaphone VARCHAR(100) NOT NULL DEFAULT '',

    INDEX(fk_sl_species),
    INDEX(name),
    INDEX(sound_soundex),
    INDEX(sound_metaphone)
);
"
);


define("SL_DB_TABLE_SL_PCV_META",
"
CREATE TABLE sl_pcv_meta (
        _key        INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT,
        _created    DATETIME,
        _created_by INTEGER,
        _updated    DATETIME,
        _updated_by INTEGER,
        _status     INTEGER DEFAULT 0,

    fk_sl_pcv       INTEGER NOT NULL,
    k               VARCHAR(200) NOT NULL,
    v               TEXT NOT NULL,

    INDEX(fk_sl_pcv, k)        -- this works like an index on (fk_sl_pcv) as well as (fk_sl_pcv,k)
);
");


define("SL_DB_TABLE_SL_PCV_SYN",
"
CREATE TABLE sl_pcv_syn (
        _key        INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT,
        _created    DATETIME,
        _created_by INTEGER,
        _updated    DATETIME,
        _updated_by INTEGER,
        _status     INTEGER DEFAULT 0,

    fk_sl_pcv       INTEGER NOT NULL,
    name            VARCHAR(200) NOT NULL,
    t               INTEGER NOT NULL DEFAULT 0,   -- differentiate sets or types of synonyms
    notes           TEXT,

    INDEX(fk_sl_pcv),
    INDEX(name)
);
");


/**************************
    Sources
 */

define("SEEDS_DB_TABLE_SL_SOURCES",
"
CREATE TABLE sl_sources (

        _key        INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT,
        _created    DATETIME,
        _created_by INTEGER,
        _updated    DATETIME,
        _updated_by INTEGER,
        _status     INTEGER DEFAULT 0,

    sourcetype  ENUM('company','seedbank','collector') NOT NULL DEFAULT 'company',
    name_en     VARCHAR(200) NOT NULL DEFAULT '',
    name_fr     VARCHAR(200) NOT NULL DEFAULT '',
    addr_en     VARCHAR(200) NOT NULL DEFAULT '',
    addr_fr     VARCHAR(200) NOT NULL DEFAULT '',
    city        VARCHAR(200) NOT NULL DEFAULT '',
    prov        VARCHAR(200) NOT NULL DEFAULT '',
    country     VARCHAR(200) NOT NULL DEFAULT '',
    postcode    VARCHAR(200) NOT NULL DEFAULT '',
    phone       VARCHAR(200) NOT NULL DEFAULT '',
    fax         VARCHAR(200) NOT NULL DEFAULT '',  -- not our job to keep track of this
    web         VARCHAR(200) NOT NULL DEFAULT '',
    web_alt     VARCHAR(200) NOT NULL DEFAULT '',
    email       VARCHAR(200) NOT NULL DEFAULT '',
    email_alt   VARCHAR(200) NOT NULL DEFAULT '',
    desc_en     TEXT NOT NULL,
    desc_fr     TEXT NOT NULL,
    year_est    INTEGER,

    -- internal
    comments    TEXT,
    bShowCompany INTEGER DEFAULT 1,
    bSupporter  INTEGER DEFAULT 0,

    -- when the 'This is Correct' checkbox is checked, tsVerified=NOW(),bNeedVerify=0
    -- when any data is changed by non-approvers, bNeedProof=1
    tsVerified  VARCHAR(200) NULL,   -- was DATETIME but mysql won't allow '' as a default,
    bNeedVerify INTEGER DEFAULT 1,
    bNeedProof  INTEGER DEFAULT 1,
    bNeedXlat   INTEGER DEFAULT 1,

    index (sourcetype)
);
"
);


?>
