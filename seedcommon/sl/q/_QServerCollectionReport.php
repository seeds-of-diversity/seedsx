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
                if( !($kCollection = intval(@$parms['kCollection'])) ) {
                    $rQ['sErr'] = "No collection specified";
                    goto done;
                }
                list($rQ['bOk'],$rQ['raOut']) = $this->cultivarSummary( $kCollection );
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

    private function cultivarSummary( $kCollection )
    {
        $bOk = false;
        $raOut = array();

        include_once( SEEDCOMMON."sl/sl_db_adoption.php" );

        $oSLDBMaster = new SLDB_Master( $this->oQ->kfdb, $this->oQ->sess->GetUID() );

        // Get the pcv of every variety in the specified collection
        $raPRows = $oSLDBMaster->GetList(
                        "IxAxPxS",
                        "I.fk_sl_collection='$kCollection' AND NOT I.bDeAcc",
                        array( 'sGroupCol' => 'P._key,P.name,S.name_en,S.name_fr,S.psp,S._key',
                               'raFieldsOverride' => array( 'S_name_en'=>"S.name_en", 'S_name_fr'=>"S.name_fr", 'S_psp'=>'S.psp', 'S__key'=>"S._key",
                                                            'P_name'=>"P.name", 'P__key'=>"P._key" ),
                               'sSortCol' => 'S.psp,P.name' ) );

        // Get the most recent harvest date and total weight of each pcv
        $c = 0;
        foreach( $raPRows as $ra ) {
            list($yNewest,$nWeightTotal,$sNotes, $fAdoption) = SLDBCultivar_GetInvDetailsForPCV( $oSLDBMaster, $ra['P__key'], $kCollection, true ); // compute fAdoption

            $raOut[] = array(
                    'cv'          => $ra['P__key'],
                    'species'     => $ra['S_psp'],
                    'cultivar'    => $this->oQ->QCharSet($ra['P_name']),
                    'adoption'    => $fAdoption,
                    'year_newest' => $yNewest,
                    'total_grams' => $nWeightTotal,
                    'notes'       => $sNotes
            );
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
        $raDRows = SLDBAdopt_GetAdoptedPCV_RA( $oSLDBMaster );

        // Get the most recent harvest date and total weight of each adopted pcv
        $c = 0;
        foreach( $raDRows as $raD ) {
            list($yNewest,$nWeightTotal,$sNotes,$fDummy) = SLDBCultivar_GetInvDetailsForPCV( $oSLDBMaster, $raD['P__key'], $kCollection, false); // already computed fAdoption above

            $raOut[] = array(
                    'cv'          => $raD['P__key'],
                    'species'     => $raD['S_psp'],
                    'cultivar'    => $this->oQ->QCharSet($raD['P_name']),
                    'adoption'    => $raD['amount'],
                    'year_newest' => $yNewest,
                    'total_grams' => $nWeightTotal,
                    'notes'       => $sNotes
            );
        }

        $bOk = true;

        done:
        return( array($bOk, $raOut) );
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
