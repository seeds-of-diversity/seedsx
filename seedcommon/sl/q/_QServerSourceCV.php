<?php

/* _QServerSourceCV
 *
 * Copyright 2015-2024 Seeds of Diversity Canada
 *
 * Serve queries about sources of cultivars
 * (basically queries involving sl_sources and/or sl_cv_sources)
 */

include_once( SEEDCOMMON."sl/sl_db.php" );
include_once( "Q.php" );

class QServerSourceCV_Old
{
    private $oQ;
    private $oSLDBSrc;
    private $bUTF8 = false;

// bUTF8 should be defined in Q instead
    function __construct( Qold $oQ, $raParms = array() )
    {
        $this->oQ = $oQ;
        $this->oSLDBSrc = new SLDB_Sources( $oQ->kfdb, $oQ->sess->GetUID() );
        $this->bUTF8 = intval(@$raParms['bUTF8']);
    }

    function Cmd( $cmd, $parms )
    {
        $rQ = $this->oQ->GetEmptyRQ();

        /* Seed companies that fit criteria (one row per company)
        */
        if( $cmd == 'srcSources' ) {
            $raParms = array();
            if( ($p = intval(@$parms['kPcv'])) )      $raParms['kPcv'] = $p;
            if( ($p = intval(@$parms['kSp'])) )       $raParms['kSp'] = $p;
            if( ($p = intval(@$parms['bOrganic'])) )  $raParms['bOrganic'] = $p;
            if( ($p = intval(@$parms['bPGRC'])) )     $raParms['bPGRC'] = $p;
            if( ($p = intval(@$parms['bNPGS'])) )     $raParms['bNPGS'] = $p;
            if( ($p = @$parms['sProvinces']) )        $raParms['sProvinces'] = $p;
            if( ($p = @$parms['sRegions']) )          $raParms['sRegions'] = $p;

            $rQ['sLog'] = SEEDCore_ImplodeKeyValue( $raParms, "=", "," );

// add sSrch to match SRC.name LIKE '%sSrch%'
            if( ($raSources = $this->GetSources( $raParms )) ) {
                $rQ['bOk'] = true;
                $rQ['raOut'] = $raSources;
            }
        }

        /* Cultivars offered by seed companies (one row per cultivar)
         */
        if( $cmd == 'srcCultivars'
            || $cmd == 'srcCultivarSearch' )    // Deprecate in favour of srcCultivars
        {
            $raParms = array();
            if( ($p = intval(@$parms['kSp'])) )       $raParms['kSp'] = $p;
            if( ($p = intval(@$parms['bOrganic'])) )  $raParms['bOrganic'] = $p;
            if( ($p = intval(@$parms['bBulk'])) )     $raParms['bBulk'] = $p;
            if( ($p = @$parms['sSrch']) )             $raParms['sSrch'] = $p;
            if( ($p = @$parms['sRegions']) )          $raParms['sRegions'] = $p;
            if( ($p = @$parms['sMode']) )             $raParms['sMode'] = $p;

            $rQ['sLog'] = SEEDCore_ImplodeKeyValue( $raParms, "=", "," );

            // This has to have at least some parameters or it tries to fetch the whole SrcCV table (use a parm to do that, not the default).
            if( count($raParms) && ($ra = $this->listCultivars( $raParms )) ) {
                $rQ['bOk'] = true;
                $rQ['raOut'] = $ra;
            }
        }

        /* A variation of srcSrcCv that produces a CSCI update spreadsheet
         */
        if( $cmd == 'srcCSCI' ) {
            $raParms = $this->normalizeParms( array_merge( $parms,  array( 'bCSCICols'=>true,
                                                                           'kfrcParms'=>array('sSortCol'=>'SRCCV.osp,SRCCV.ocv') )) );

            $rQ['sLog'] = SEEDCore_ImplodeKeyValue( $raParms, "=", "," );

            if( ($ra = $this->getSrcCV( $raParms )) ) {
                $rQ['bOk'] = true;
                $rQ['raOut'] = $ra;

                if( ($k = intval(@$parms['kSrc'])) ) {
                    $rQ['raMeta']['name'] = $this->oQ->kfdb->Query1( "SELECT name_en FROM seeds_1.sl_sources WHERE _key='$k'" );
                } else if( $raParms['bAllComp'] ) {
                    $rQ['raMeta']['name'] = "All Companies";
                }
            }
        }

        done:
        return( $rQ );
    }


    private function listCultivars( $raParms )
    {
        $raOut = array();

        $sMode = @$raParms['sMode'];

        $raCond = array();
        $raCondKluge = array();
        $raKFParms = array();
        if( ($dbSrch = addslashes(@$raParms['sSrch'] ?? '')) ) {
// add a parm that specifies whether the search term applies to P, S, or both
            $raCond[] = "(P.name LIKE '%$dbSrch%' OR S.name_en LIKE '%$dbSrch%')";
            $raCondKluge[] = "(SRCCV.ocv LIKE '%$dbSrch%' OR S.name_en LIKE '%$dbSrch%')";
        }
        if( ($kSp = @$raParms['kSp']) ) {
            $raCond[] = "S._key='$kSp'";
            $raCondKluge[] = "S._key='$kSp'";
        }
        if( ($reg = @$raParms['sRegions']) ) {
            switch( $reg ) {
                case 'bc':  $raCond[] = "SRC.prov='BC'";                     $raCondKluge[] = "SRC.prov='BC'";                     break;
                case 'pr':  $raCond[] = "SRC.prov in ('AB','SK','MB')";      $raCondKluge[] = "SRC.prov in ('AB','SK','MB')";      break;
                case 'on':  $raCond[] = "SRC.prov='ON'";                     $raCondKluge[] = "SRC.prov='ON'";                     break;
                case 'qc':  $raCond[] = "SRC.prov='QC'";                     $raCondKluge[] = "SRC.prov='QC'";                     break;
                case 'at':  $raCond[] = "SRC.prov in ('NB','NS','PE','NL')"; $raCondKluge[] = "SRC.prov in ('NB','NS','PE','NL')"; break;
            }
        }
        if( ($bOrganic = @$raParms['bOrganic']) ) {
            $raCond[] = "SRCCV.bOrganic";
            $raCondKluge[] = "SRCCV.bOrganic";
        }
        if( ($bBulk = @$raParms['bBulk']) ) {
            $raCond[] = "SRCCV.bulk";
            $raCondKluge[] = "SRCCV.bulk";
        }


        //$kfdb->SetDebug(2);
        $nItems = 0;
        $raKlugeCollector = array();
//$this->oQ->kfdb->SetDebug(2);
        if( ($kfr = $this->GetCultivarsKFRC( implode(" AND ", $raCond), array("mode"=>$sMode) )) ) {
            while( $kfr->CursorFetch() ) {

        // really just want this once the kluge is gone
        //        $sItem = $kfr->Expand(
        //            "<span class='seedfinder-item-sp'>&nbsp;[[S_name_en]]</span>"
        //           ."<div class='seedfinder-item-cv'>[[P_name]]</div>"
        //           ."<span style='font-size:8pt;font-family:serif;float:right;margin-top:-15px;display:none'>[[c]]"./*" source".($kfr->Value('c')==1?"":"s").*/"&nbsp;</span>"
        //        );
        //
        //        $sCVList .= "<div class='seedfinder-item seedfinder-item".($nItems%2)."' "
        //                   ." onclick='showSuppliers(".$kfr->Value('P__key').",\"".$kfr->ValueEnt('P_name')." ".$kfr->ValueEnt('S_name_en')."\");'>"
        //                   .$sItem
        //                   ."</div>";
        //        $nItems++;

                $k1 = $this->charset($kfr->Value('S_name_en').' '.$kfr->Value('P_name'));
                $raKlugeCollector[$k1] = array(
                        'S_name_en' => $this->charset($kfr->Value('S_name_en')),
                        'S_name_fr' => $this->charset($kfr->Value('S_name_fr')),
                        'P_name'    => $this->charset($kfr->Value('P_name')),
                        'P__key'    => $kfr->Value('P__key'),
                );
            }

            if( $sMode == 'TopChoices' ) goto sortMe;

            if( !count($raCondKluge) )  $raCondKluge = array("1=1");    // this is not a good idea because there are potentially thousands of results

            // Kluge: for matches where fk_sl_pcv==0 and the species,name are not already in the list (shouldn't be!), add them to the list
            //        with P__key=SRCCV__key+10,000,000
            if( ($dbc = $this->oQ->kfdb->CursorOpen( "SELECT SRCCV._key AS kluge_key, S.name_en AS S_name_en, S.name_fr AS S_name_fr, SRCCV.ocv AS ocv "
                                          ."FROM sl_cv_sources SRCCV, sl_sources SRC, sl_species S "
                                          ."WHERE SRCCV._status='0' AND SRC._status='0' AND S._status='0' AND "
                                                ."SRCCV.fk_sl_species=S._key AND SRCCV.fk_sl_sources=SRC._key AND "
                                                ."SRCCV.fk_sl_pcv='0' AND SRCCV.fk_sl_sources >= 3 AND "
                                                ."(".(implode(' AND ',$raCondKluge)).")" ) ) )
            {
                while( $ra = $this->oQ->kfdb->CursorFetch($dbc) ) {
                    // utf8 encoding the key became necessary on www12 when the name contains characters that json won't allow. Somehow php was handling the encoding
                    // in keys before. If this doesn't work, try a base64 encoding of the key? It does seem like php is okay with utf8 strings in general.
                    $k1 = $this->charset($ra['S_name_en'].' '.$ra['ocv']);
                    $raKlugeCollector[$k1] = array(
                        'S_name_en' => $this->charset($ra['S_name_en']),
                        'S_name_fr' => $this->charset($ra['S_name_fr']),
                        'P_name'    => $this->charset($ra['ocv']),
                        'P__key'    => $ra['kluge_key'] + 10000000,
                    );
                }
            }

            sortMe: // the above kluge catcher catches a really large number of rows if there isn't a filter. TopChoices is designed to not need a filter.
            ksort($raKlugeCollector);
        }
        $raOut = $raKlugeCollector;

        return( $raOut );
    }



// this should be private
// and should not encapsulate TopChoices. Instead that option should set parms for this call (because currently the parms for TopChoices are specified in different parts of the code)
    function GetCultivarsKFRC( $sCond, $raParms = array() )
    /******************************************************
        Get the distinct cultivars that match the condition, ordered by sp name, cv name

        Parms:
            mode : TopChoices = limit to 30 results, ordered by count,s,p
     */
    {
        $raKFParms = array();

        // Why is this grouped by S,P instead of just P?  What does that even mean?
        // Filter by SRC, group by S,P
        $raKFParms['raFieldsOverride'] = ['S_name_en'=>"S.name_en", 'S_name_fr'=>"S.name_fr", 'S__key'=>"S._key",
                                          'P_name'=>"P.name", 'P__key'=>"P._key", 'c'=>"count(*)"];
        $raKFParms['sGroupCol']        = 'S.name_en,S.name_fr,S._key,P.name,P._key';

        if( @$raParms['mode'] == 'TopChoices' ) {
            $raKFParms['sSortCol'] = "c desc,S.name_en asc,P.name";
            $raKFParms['iLimit'] = 30;
        } else {
            $raKFParms['sSortCol']      = 'S.name_en asc,P.name';
        }
//$this->oSLDBSrc->kfdb->SetDebug(2);
        $kfr = $this->oSLDBSrc->GetKFRC( "SRCCVxSRCxPxS", $sCond, $raKFParms );

        return( $kfr );
    }

    function GetSources( $raParms = array() )
    /****************************************
        Get sl_sources information for all suppliers that fit the criteria

            kPcv     = suppliers that provide this cultivar
            kSp      = suppliers that provide this species
            bOrganic = suppliers where the matched sl_cv_sources row has bOrganic

            bPGRC    = (default false) include PGRC if it matches the other criteria
            bNPGS    = (default false) include NPGS if it matches the other criteria

            raRegions   = suppliers in the given regions
            sRegions    = same but the format is a string like "on,pr,at"
            raProvinces = suppliers in the given provinces
            sProvinces  = same but the format is a string like "bc,ON,qc"

            bSanitize = (default true) only return the common fields
     */
    {
        $bSanitize = SEEDCore_ArraySmartVal( $raParms, 'bSanitize', array(true,false) );     // by default only return the common fields

        $raOut = array();

        $kPcv     = intval(@$raParms['kPcv']);
        $kSp      = intval(@$raParms['kSp']);
        $bOrganic = intval(@$raParms['bOrganic']);
        $bBulk    = intval(@$raParms['bBulk']);
        $bPGRC    = intval(@$raParms['bPGRC']);
        $bNPGS    = intval(@$raParms['bNPGS']);

        /* Compose $sCondDB
         *
         *      kPcv AND kSP AND bOrganic AND ( bSeedbank OR src.prov in (...) )
         */
        $raCond = array();
        if( $kPcv ) {
// kluge: some kPcv are fakes, actually SRCCV._key+10,000,000 representing the ocv at that row
if( $kPcv > 10000000 ) {
    if( ($ocv = $this->oSLDBSrc->kfdb->Query1("SELECT ocv FROM {$this->oQ->oApp->DBName('seeds1')}.sl_cv_sources WHERE _key='".($kPcv-10000000)."'")) &&
        ($osp = $this->oSLDBSrc->kfdb->Query1("SELECT osp FROM {$this->oQ->oApp->DBName('seeds1')}.sl_cv_sources WHERE _key='".($kPcv-10000000)."'")) ) {
        $raCond[] = "SRCCV.osp='".addslashes($osp)."' AND SRCCV.ocv='".addslashes($ocv)."'";
    }
} else {
// this is the usual operation
            $raCond[] = "SRCCV.fk_sl_pcv='$kPcv'";
}
        }
        if( $kSp ) {
// kluge: this is handy because it's used during the source_cv build, however it is denormalized and could be deprecated
            $raCond[] = "SRCCV.fk_sl_species='$kPcv'";
        }
        if( $bOrganic ) {
            $raCond[] = "SRCCV.bOrganic";
        }
        if( $bBulk ) {
            $raCond[] = "SRCCV.bulk";
        }

        /* Compose seedbank / provinces subcondition
         */
        $raProvinces = array();
        if( @$raParms['raProvinces'] ) {
            $raProvinces = $raParms['raProvinces'];
        }
        if( ($p = @$raParms['sProvinces']) ) {
            $raProvinces = explode( ',', $p );
        }
        $tmpRaRegions = @$raParms['raRegions'];
        if( ($p = @$raParms['sRegions']) ) {
            $tmpRaRegions = explode( ',', $p );
        }
        if( is_array($tmpRaRegions) ) {
            foreach( $tmpRaRegions as $reg ) {
                switch( strtolower($reg) ) {
                    case 'bc':  $raProvinces[] = 'BC';  break;

                    case 'pr':  $raProvinces[] = 'AB';
                                $raProvinces[] = 'SK';
                                $raProvinces[] = 'MB';  break;

                    case 'on':  $raProvinces[] = 'ON';  break;

                    case 'qc':  $raProvinces[] = 'QC';  break;

                    case 'at':  $raProvinces[] = 'NB';
                                $raProvinces[] = 'NS';
                                $raProvinces[] = 'PE';
                                $raProvinces[] = 'NF';  break;
                }
            }
        }
        $condProvinces = count($raProvinces) ? ("SRC.prov in ('".implode( "','", $raProvinces )."')") : "";
        if( $bPGRC ) {
            $condProvinces .= ($condProvinces ? " OR " : "")."SRCCV.fk_sl_sources='1'";
        }
        if( $bNPGS ) {
            $condProvinces .= ($condProvinces ? " OR " : "")."SRCCV.fk_sl_sources='2'";
        }
        if( $condProvinces )  $raCond[] = "($condProvinces)";

        $sCondDB = implode( " AND ", $raCond );
//$this->oSLDBSrc->kfdb->SetDebug(2);

        /* FULL_GROUP_ONLY queries cannot group by SRC._key and then return all the fields for each row.
         * Therefore fetch just the SRC._keys that match the condition, and fetch the metadata for each of them.
         * Mysql still allows sorting by name_en, even though it is not a functionally dependent column of the group.
         */
        //                                          db field    basic  charset
        $raFields = array( 'SRC_name'     => array( "name_en",  true,  true ),
                           'SRC_name_en'  => array( "name_en",  true,  true ),
                           'SRC_address'  => array( "addr_en",  true,  true ),
                           'SRC_addr_en'  => array( "addr_en",  true,  true ),
                           'SRC_city'     => array( "city",     true,  true ),
                           'SRC_prov'     => array( "prov",     true,  true ),
                           'SRC_postcode' => array( "postcode", true,  true ),
                           'SRC_email'    => array( "email",    true,  true ),
                           'SRC_web'      => array( "web",      true,  true ),
                           'SRC_year_est' => array( "year_est", true,  false ),
                           'SRC_desc_en'  => array( "desc_en",  true,  true ),
                           'SRC_desc_fr'  => array( "desc_fr",  true,  true ),
        );
        if( ($kfr = $this->oSLDBSrc->GetKFRC( "SRCCVxSRC", $sCondDB,
                                              array( 'sGroupCol'=>'SRC._key', 'sSortCol'=>'SRC.name_en',
                                                     'raFieldsOverride'=> array("SRC__key"=>"SRC._key") ) )) ) {
            while( $kfr->CursorFetch() ) {
                $kfr2 = $this->oSLDBSrc->GetKFR( "SRC", $kfr->Value('SRC__key') );

                $raSrc = array();
                $raSrc['SRC__key'] = $kfr->Value( 'SRC__key' );
                foreach( $raFields as $alias => $ra ) {
                    if( $ra[1] || !$bSanitize ) {   // always do basic fields; also do non-basic fields if !bSanitize
                        $v = $kfr2->Value($ra[0]);
                        $raSrc[$alias] = $ra[2] ? $this->charset($v) : $v;
                    }
                }
                $raOut[] = $raSrc;
            }
        }

        return( $raOut );
    }

/*

*** Copied to Q2

 */
    private function normalizeParms( $parms )
    /****************************************
        Lots of input parms are allowed. Consolidate them into normalized parms.

        Input:
            kSrc, raSrc, rngSrc     one or more sl_sources._key
            kSp,  raSp,  rngSp      one or more sl_species._key
            kPcv, raPcv, rngPcv     one or more sl_pcv._key
            bPGRC                   include src 1
            bNPGS                   include src 2
            bAllComp                include src >=3

            bOrganic                true: fetch only organic (there is no way to fetch only non-organic)
            kfrcParms               array of parms for kfrc

        Normalized:
            rngSrc                  a SEEDRange of sl_sources._key (including special sources 1 and/or 2)
            rngSp                   a SEEDRange of sl_species._key
            rngPcv                  a SEEDRange of sl_pcv._key
            bAllComp                include src >=3 and exclude any of those numbers from rngSrc

            bOrganic                true: fetch only organic (there is no way to fetch only non-organic)
            kfrcParms               array of parms for kfrc
            bCSCICols               output the csci spreadsheet columns
     */
    {
//var_dump($parms);
        $raParms = array();

        // Species
        $ra = ($ra1 = @$parms['raSp']) ? $ra1 : array();
        if( ($k = intval(@$parms['kSp'])) ) $ra[] = $k;
        if( ($r = @$parms['rngSp']) ) {
            list($raR,$sRdummy) = SEEDCore_ParseRangeStr( $r );
            $ra = array_merge( $ra, $raR );
        }
        $raParms['rngSp'] = SEEDCore_MakeRangeStr( $ra );

        // Pcv
// raPcv and rngPcv only supported for non-kluged kPcv < 10000000
        $ra = ($ra1 = @$parms['raPcv']) ? $ra1 : array();
        if( ($k = intval(@$parms['kPcv'])) && $k < 10000000 ) $ra[] = $k;
        if( ($r = @$parms['rngPcv']) ) {
            list($raR,$sRdummy) = SEEDCore_ParseRangeStr( $r );
            $ra = array_merge( $ra, $raR );
        }
        $raParms['rngPcv'] = SEEDCore_MakeRangeStr( $ra );

// kluge: special handler for kPcv that are really sl_cv_sources._key+10000000
if( ($k = intval(@$parms['kPcv'])) && $k > 10000000 ) $raParms['kPcvKluge'] = $k;

        // Src
        $raSrc = ($ra1 = @$parms['raSrc']) ? $ra1 : array();
        if( ($k = intval(@$parms['kSrc'])) ) $raSrc[] = $k;
        if( ($r = @$parms['rngSrc']) ) {
            list($raR,$sRdummy) = SEEDCore_ParseRangeStr( $r );
            $raSrc = array_merge( $raSrc, $raR );
        }
        /* bAllComp overrides all kSrc >=3
         *
         *      bAllComp            -> bAllComp
         *      bAllComp + srcX>=3  -> bAllComp + ()
         *      bAllComp + bPGRC    -> bAllComp + (1)
         *      srcX>=3             -> (X)
         *      bPGRC               -> (1)
         *      srcX>=3 + bPGRC     -> (1,X)
         *      src=''              -> bAllComp         no src input (and no seed banks) implies all src>=3
         */
        if( ($bPGRC = @$parms['bPGRC']) )  $raSrc[] = 1;
        if( ($bNPGS = @$parms['bNPGS']) )  $raSrc[] = 2;
        $raParms['bAllComp'] = intval(@$parms['bAllComp']);

        if( !$raParms['bAllComp'] ) {
            if( count($raSrc) ) {
                // load the normalized range with the seedbanks and companies collected above
                $raParms['rngSrc'] = SEEDCore_MakeRangeStr( $raSrc );
            } else {
                // no seed banks or companies specified, so default to bAllComp
                $raParms['rngSrc'] = "";
                $raParms['bAllComp'] = true;
            }
        }

        $raParms['bOrganic'] = intval(@$parms['bOrganic']);

        $raParms['kfrcParms'] = isset($parms['kfrcParms']) ? $parms['kfrcParms'] : array();

        $raParms['bCSCICols'] = intval(@$parms['bCSCICols']);

        return( $raParms );
    }

/*
*** Copied to Q2
 */
    private function getSrcCV( $raParms = array() )
    /**********************************************
    */
    {
        $raOut = array();

        if( ($oCursor = $this->getSrcCVCursor( $raParms )) ) {
            while( ($ra = $oCursor->GetNextRow()) ) {
                $raOut[] = $ra;
            }
        }

        return( $raOut );
    }

/*
*** Copied to Q2
 */
    private function getSrcCVCursor( $raParms = array() )
    /****************************************************
        Get sl_cv_sources information for all entries that fit the criteria

        $raParms is the output from $this->normalizeParms
     */
    {
        $raCond = array();
        if( $raParms['rngSp'] )   $raCond[] = SEEDCore_RangeStrToDB( $raParms['rngSp'],  'SRCCV.fk_sl_species' );
        if( $raParms['rngPcv'] )  $raCond[] = SEEDCore_RangeStrToDB( $raParms['rngPcv'], 'SRCCV.fk_sl_pcv' );

        $sSrc = SEEDCore_RangeStrToDB( $raParms['rngSrc'], 'SRCCV.fk_sl_sources' );;
        if( !$sSrc || @$raParms['bAllComp'] ) {
            // if no Src parms are defined, default to bAllComp
            $sSrc = "(SRCCV.fk_sl_sources >= '3'".($sSrc ? " OR ($sSrc)" : "").")";
        }
        $raCond[] = $sSrc;

        if( @$raParms['bOrganic'] )  $raCond[] = "SRCCV.bOrganic";

if( ($k = intval(@$raParms['kPcvKluge'])) ) {
// kluge: some kPcv are fakes, actually SRCCV._key+10,000,000 representing the ocv at that row
    if( ($ra = $this->oSLDBSrc->kfdb->QueryRA("SELECT osp,ocv FROM {$this->oQ->oApp->DBName('seeds1')}.sl_cv_sources WHERE _key='".($k-10000000)."'")) ) {
        $raCond[] = "SRCCV.osp='".addslashes($ra['osp'])."' AND SRCCV.ocv='".addslashes($ra['ocv'])."'";
    }
}

        $sCondDB = implode( " AND ", $raCond );
//$this->oSLDBSrc->kfdb->SetDebug(2);

        $oCursor = null;
        if( ($kfrc = $this->oSLDBSrc->GetKFRC( "SRCCVxSRC", $sCondDB, @$raParms['kfrcParms'] )) ) {
            $oCursor = new QCursor( $kfrc, array($this,"GetSrcCVRow"), $raParms );
        }

        return( $oCursor );
    }

/*
*** Copied to Q2
 */
    function GetSrcCVRow( QCursor $oCursor, $raParms )
    {
        $ra = array();

        // This is not a normalized parm because if it is set to false it should be done so on purpose by the function
        // that handles the command. Maybe that can be done via an input parm, but it's probably best to assume that
        // extra or "hidden" information cannot be requested by a standardized parm.
        $bSanitize = SEEDStd_ArraySmartVal( $raParms, 'bSanitize', array(true,false) );     // by default only return the common fields

        $kfrc = $oCursor->kfrc;    // could use an accessor to encapsulate this

        if( $raParms['bCSCICols'] ) {
            $ra = array( 'k' => $kfrc->Value('_key'),
                         'company'           => $this->charset( $kfrc->Value('SRC_name_en') ),
                         'species'           => $this->charset( $kfrc->Value('osp') ),
                         'cultivar'          => $this->charset( $kfrc->Value('ocv') ),
                         'organic'           => $kfrc->Value('bOrganic'),
                         'bulk'              => $kfrc->Value('bulk'),
                         'notes'             => $kfrc->Value('notes')
            );


        } else if( $bSanitize ) {
            $ra = array( 'SRCCV__key'          => $kfrc->Value('_key'),
                         'SRCCV_fk_sl_species' => $kfrc->Value('fk_sl_species'),
                         'SRCCV_fk_sl_pcv'     => $kfrc->Value('fk_sl_pcv'),
                         'SRCCV_fk_sl_sources' => $kfrc->Value('fk_sl_sources'),
                         'SRCCV_osp'           => $this->charset( $kfrc->Value('osp') ),
                         'SRCCV_ocv'           => $this->charset( $kfrc->Value('ocv') ),
                         'SRCCV_bOrganic'      => $kfrc->Value('bOrganic'),
                       );
        } else {
            // does not support $this->bUTF8
            $ra = $kfrc->ValuesRA();
        }
        return( $ra );
    }

    private function charset( $s )
    {
        return( $this->bUTF8 ? SEEDCore_utf8_encode( $s ) : $s );
    }
}

?>
