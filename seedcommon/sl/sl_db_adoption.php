<?php

/* Seed Library: sl_db_adoption
 *
 * Copyright 2016 Seeds of Diversity Canada
 *
 * Higher level database functions for Seed Library adoptions
 */

include_once( "sl_db.php" );


function SLDBAdopt_GetAdoptedPCV_RA( SLDB_Master $oSLDBMaster )
/**************************************************************
    Get a list of all adopted varieties (adoptions where amount>0 and a pcv is specified)

    This does not filter by collection because sl_adoption only belongs to Seeds of Diversity
 */
{
    $kfrelDummy = $oSLDBMaster->GetKFRel( "A" );
    $ra = $kfrelDummy->kfdb->QueryRowsRA( "SELECT P._key as P__key,P.name as P_name,S.psp as S_psp,SUM(D.amount) as amount "
                                            ."FROM seeds.sl_adoption D,seeds.sl_pcv P,seeds.sl_species S "
                                            ."WHERE D.amount AND D.fk_sl_pcv=P._key AND P.fk_sl_species=S._key "
                                            ."AND D._status='0' AND P._status='0' AND S._status='0' "
                                            ."GROUP BY P._key ORDER BY S.psp,P.name" );

    return( $ra );
}

function SLDBCultivar_GetInvDetailsForPCV( $oSLDBMaster, $kPCV, $kCollection, $bAdoption = false )
/*************************************************************************************************
    Get some reporting details about the SL inventory for the given pcv
 */
{
    $kfrcI = $oSLDBMaster->GetKFRC( "IxA", "A.fk_sl_pcv='$kPCV' AND I.fk_sl_collection='$kCollection' AND NOT I.bDeAcc" );

    $yNewest = 0;
    $nWeightTotal = 0.0;
    $sNotes = "";
    $raNotes = array();
    $fAdoption = 0.0;

    /* Count the total weight and find the newest lot.
     * Also record the weight and year of the lots in reverse chronological order. This is hard because the year can come from
     * two different places, so store array( '0year i'=>weight, ... ) where i is a unique number, then sort, then unpack.
     */
    $i = 0;
    while( $kfrcI->CursorFetch() ) {
        // sometimes these fields contain a date and sometimes just the year. Mysql doesn't allow dates to just be years, so these are plain strings.
        $y = intval(substr($kfrcI->Value('A_x_d_harvest'),0,4)) or $y = intval(substr($kfrcI->Value('A_x_d_received'),0,4));
        if( $y > $yNewest )  $yNewest = $y;

        $g = intval($kfrcI->Value('g_weight')*100.0)/100.0;
        $nWeightTotal += $g;

        $raNotes["0$y $i"] = $g;    // this will ksort by year, and you can get the year with intval() even if $y is 0
        $i++;
    }
    krsort($raNotes);
    foreach( $raNotes as $y => $g ) {
        $y = intval($y);
        $sNotes .= ($sNotes ? " | " : "")."$g g from ".($y ? $y : "unknown year");
    }

    if( $bAdoption ) {
        // this is independent of kCollection, and really should only be used if kCollection==1
        $kfrelDummy = $oSLDBMaster->GetKFRel( "A" );
        $fAdoption = $kfrelDummy->kfdb->Query1( "SELECT SUM(amount) FROM seeds.sl_adoption WHERE fk_sl_pcv='$kPCV' AND _status='0'" );
    }

    return( array($yNewest,$nWeightTotal,$sNotes, $fAdoption) );
}

?>