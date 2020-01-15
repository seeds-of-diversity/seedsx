<?php

/* _QServerCollectionReport
 *
 * Copyright 2017 Seeds of Diversity Canada
 *
 * Serve reports about sl_collection, sl_accession, sl_adoption, etc
 * This is intended mainly for internal use, so permissions are restricted to SoD personnel.
 */

include_once( SEEDCOMMON."sl/sl_db.php" );

class QServerCollectionReport
{
    private $oQ;
    private $oSLDB;

    function __construct( Q $oQ, $raParms = array() )
    {
        $this->oQ = $oQ;
        $this->oSLDB = new SLDB_Collection( $oQ->kfdb, $oQ->sess->GetUID() );
    }

    function Cmd( $cmd, $parms )
    {
        $raParms = array();

        $rQ = $this->oQ->GetEmptyRQ();

        /* These commands are intended mainly for internal use, so permissions are restricted to SoD personnel.
         */
        list($bAccess,$rQ['sErr']) = $this->oQ->CheckPerms( $cmd, 'SLCollectionReport', "Seed Collection Report" );
// also check per-collection RWA permission
        if( !$bAccess ) goto done;

        switch( strtolower($cmd) ) {
            case 'collreport-cultivarsummary':
            case 'collreport-cultivarsummaryunioncsci':
                if( !($kCollection = intval(@$parms['kCollection'])) ) {
                    $rQ['sErr'] = "No collection specified";
                    goto done;
                }
                list($rQ['bOk'],$rQ['raOut']) = $this->cultivarSummary( $kCollection, strtolower($cmd)=='collreport-cultivarsummaryunioncsci' );
                $rQ['raMeta']['title'] = "Summary of All Varieties";
                $rQ['raMeta']['name'] = "collreport-cultivar-summary";
                break;

            case 'collreport-adoptedsummary':
                if( !($kCollection = intval(@$parms['kCollection'])) ) {
                    $rQ['sErr'] = "No collection specified";
                    goto done;
                }
                list($rQ['bOk'],$rQ['raOut']) = $this->adoptedSummary( $kCollection );
                $rQ['raMeta']['title'] = "Summary of Adopted Varieties";
                $rQ['raMeta']['name'] = "collreport-adopted-summary";
                break;

            case 'collreport-germsummary':
                if( !($kCollection = intval(@$parms['kCollection'])) ) {
                    $rQ['sErr'] = "No collection specified";
                    goto done;
                }
                list($rQ['bOk'],$rQ['raOut']) = $this->germSummary( $kCollection );
                $rQ['raMeta']['title'] = "Germination Tests";
                $rQ['raMeta']['name'] = "collreport-germ-summary";
                break;

            default:
                break;
        }

        done:
        return( $rQ );
    }

    private function cultivarSummary( $kCollection, $bUnionCSCI )
    /************************************************************
        Get a summary of information on all cultivars in the given Seed Library Collection.
        If bUnionCSCI get all varieties in the csci and left-join that with the collection information.
     */
    {
        $bOk = false;
        $raOut = array();

        include_once( SEEDCOMMON."sl/sl_db_adoption.php" );

        $oSLDBMaster = new SLDB_Master( $this->oQ->kfdb, $this->oQ->sess->GetUID() );

        $raRows = [];
        if( $bUnionCSCI ) {
            // Every sl_cv_sources that is in sl_pcv or sl_pcv_syn should have an fk_sl_pcv.
            // Every sl_cv_sources should have an fk_sl_species.
            // So get every P._key,P.name,S.name_en,S.name_fr,S.psp,S._key from sl_pcv UNION (those equivalents from sl_cv_sources where fk_sl_pcv=0)
            if( ($kfrc = $oSLDBMaster->GetKFRC('PxS')) ) {
                while( $kfrc->CursorFetch() ) {
                    $raRows[$kfrc->Value('S_psp').'|'.$kfrc->Value('name')] = [
                        'P__key' => $kfrc->Value('_key'),
                        'P_name' => $kfrc->Value('name'),
                        'S_name_en' => $kfrc->Value('S_name_en'),
                        'S_name_fr' => $kfrc->Value('S_name_fr'),
                        'S_psp' => $kfrc->Value('S_psp'),
                        'S__key' => $kfrc->Value('S__key'),
                    ];
                }
            }

            // Now get every psp/cv from sl_cv_sources where fk_sl_pcv=0 and add those to the list
            if( false || ($dbc = $this->oQ->kfdb->CursorOpen(
                    "SELECT osp,ocv,S.name_en as S_name_en,S.name_fr as S_name_fr,S.psp as S_psp,S._key as S__key,count(*) as c "
                   ."FROM seeds.sl_cv_sources SrcCV LEFT JOIN seeds.sl_species S ON (SrcCV.fk_sl_species=S._key) "
                   ."WHERE SrcCV.fk_sl_sources>='3' AND SrcCV._status='0' AND SrcCV.fk_sl_pcv='0' "
                   ."GROUP BY osp,ocv,S.name_en,S.name_fr,S.psp,S._key")) )
            {
                while( $ra = $this->oQ->kfdb->CursorFetch($dbc) ) {
                    $sp = @$ra['S_psp'] ?: $ra['osp'];
                    $raRows[$sp.'|'.$ra['ocv']] = [
                        'P__key' => 0,
                        'P_name' => $ra['ocv'],
                        'S_name_en' => $ra['S_name_en'],
                        'S_name_fr' => $ra['S_name_fr'],
                        'S_psp' => $sp,
                        'S__key' => $ra['S__key'],
                        'nCSCI' => $ra['c']
                    ];
                }
            }

            ksort($raRows);

            // Process the list
            foreach( $raRows as $ra ) {
                if( $ra['P__key'] ) {
                    // this row came from the Seed Library Collection
                    $raOut[] = $this->getDetailsForPCV( $ra, $oSLDBMaster, $kCollection, true );
                } else {
                    // this row came from the csci where fk_sl_pcv=0
/*
                    $nCSCI = $this->oQ->kfdb->Query1( "SELECT count(*) FROM seeds.sl_cv_sources "
                                                     ."WHERE _status='0' AND fk_sl_sources>='3' "
                                                     ."AND (fk_sl_species='".intval($ra['S__key'])."' OR osp='".addslashes($ra['S_psp'])."') "
                                                     ."AND ocv='".addslashes($ra['P_name'])."'" );
*/

                    $raOut[] = [
                        'cv'          => 0,
                        'species'     => $ra['S_psp'],
                        'cultivar'    => $this->oQ->QCharSet($ra['P_name']),
                        'csci_count'  => $ra['nCSCI'],
                        'adoption'    => '',
                        'year_newest' => '',
                        'total_grams' => '',
                        'notes'       => ''
                    ];
                }

            }

        } else {
            // Get the pcv of every variety in the specified collection
            $raRows = $oSLDBMaster->GetList(
                            "IxAxPxS",
                            "I.fk_sl_collection='$kCollection' AND NOT I.bDeAcc",
                            array( 'sGroupCol' => 'P._key,P.name,S.name_en,S.name_fr,S.psp,S._key',
                                   'raFieldsOverride' => array( 'S_name_en'=>"S.name_en", 'S_name_fr'=>"S.name_fr", 'S_psp'=>'S.psp', 'S__key'=>"S._key",
                                                                'P_name'=>"P.name", 'P__key'=>"P._key" ),
                                   'sSortCol' => 'S.psp,P.name' ) );

            foreach( $raRows as $ra ) {
                $raOut[] = $this->getDetailsForPCV( $ra, $oSLDBMaster, $kCollection, true );
            }
        }


        $bOk = true;

        done:
        return( array($bOk, $raOut) );
    }

    private function adoptedSummary( $kCollection )
    {
        $bOk = false;
        $raOut = array();

        include_once( SEEDCOMMON."sl/sl_db_adoption.php" );

        $oSLDBMaster = new SLDB_Master( $this->oQ->kfdb, $this->oQ->sess->GetUID() );

        // Get the pcv of every adopted variety
        $raDRows = SLDBAdopt_GetAdoptedPCV_RA( $oSLDBMaster );  // P._key as P__key,P.name as P_name,S.psp as S_psp,SUM(D.amount) as amount

        //$raOut = $this->getDetailsForPCV( $raDRows, $oSLDBMaster, $kCollection, false );

        $bOk = true;

        done:
        return( array($bOk, $raOut) );
    }

    private function getDetailsForPCV( $raPCV, SLDB_Master $oSLDBMaster, $kCollection, $bComputeAdoption )
    {
        $raOut = [];

        // Get the most recent harvest date and total weight of each pcv
        list($yNewest,$nWeightTotal,$sNotes,$fAdoption) = SLDBCultivar_GetInvDetailsForPCV( $oSLDBMaster, $raPCV['P__key'], $kCollection, $bComputeAdoption );

        // Get the number of csci companies that have the given pcv
        $nCSCI = $this->oQ->kfdb->Query1( "SELECT count(*) FROM seeds.sl_cv_sources WHERE _status='0' AND fk_sl_pcv='{$raPCV['P__key']}' AND fk_sl_sources>='3'" );

        $raOut = [
                'cv'          => $raPCV['P__key'],
                'species'     => $raPCV['S_psp'],
                'cultivar'    => $this->oQ->QCharSet($raPCV['P_name']),
                'csci_count'  => $nCSCI,
                'adoption'    => $bComputeAdoption ? $fAdoption : $raPCV['amount'],    // could be pre-computed or not
                'year_newest' => $yNewest,
                'total_grams' => $nWeightTotal,
                'notes'       => $sNotes,
        ];

        return( $raOut );
    }


    private function germSummary( $kCollection )
    {
        $bOk = false;
        $raOut = array();

        include_once( SEEDCOMMON."sl/sl_db_adoption.php" );

        $oSLDBMaster = new SLDB_Master( $this->oQ->kfdb, $this->oQ->sess->GetUID() );

        // Get a record for every lot in thie collection that has had a germ test
        $raIRows = $oSLDBMaster->GetList(
                        "IxGxAxPxS",
                        "I.fk_sl_collection='$kCollection' AND NOT I.bDeAcc",
                        array( 'sGroupCol' => 'I._key',
                               'raFieldsOverride' => array( 'S_name_en'=>"S.name_en", 'S_name_fr'=>"S.name_fr", 'S_psp'=>'S.psp', 'S__key'=>"S._key",
                                                            'P_name'=>"P.name", 'P__key'=>"P._key", "I__key"=>"I._key", "I_inv_number"=>"I.inv_number", "I_g_weight"=>"I.g_weight" ),
                               'sSortCol' => 'S.psp,P.name,I._key' ) );

        // Get the germ test information for each lot
        $c = 0;
        foreach( $raIRows as $raI ) {
            $sNotes = "";
            $raGRows = $oSLDBMaster->GetList( "G", "G.fk_sl_inventory='{$raI['I__key']}'", array('sSortCol'=>"dStart",'bSortDown'=>true) );
            foreach( $raGRows as $raG ) {
                $sNotes .= ($sNotes ? " | " : "")."{$raG['nGerm']} % from {$raG['nSown']} seeds tested {$raG['dStart']} to {$raG['dEnd']}";
            }

            $raOut[] = array(
                    'cv'          => $raI['P__key'],
                    'species'     => $raI['S_psp'],
                    'cultivar'    => $this->oQ->QCharSet($raI['P_name']),
                    'lot'         => $raI['I_inv_number'],
                    'g_weight'    => $raI['I_g_weight'],
                    'tests'       => $sNotes
            );
        }

        $bOk = true;

        done:
        return( array($bOk, $raOut) );
    }
}

?>
