<?php

/* Seed Library Admin: sl_db_admin
 *
 * Copyright 2017 Seeds of Diversity Canada
 *
 * Statistics and integrity management for Seed Library database
 */

// see QServerRosetta::cultivarOverview
class SLDB_Admin_Stats
{
    private $raReferencesToPCV = array();
    private $raReferencesToSp = array();

    function __construct( KeyFrameDB $kfdb )
    {
        $this->kfdb = $kfdb;
    }

    function GetReferencesToPCV( $kPCV )
    {
        if( !isset($this->raReferencesToPCV[$kPCV]) ) {
            $ra = array();
            $ra['nAcc']    = $this->kfdb->Query1( "SELECT count(*) FROM seeds_1.sl_accession WHERE _status='0' AND fk_sl_pcv='$kPCV'" );
            $ra['nAdopt']  = $this->kfdb->Query1( "SELECT count(*) FROM seeds_1.sl_adoption WHERE _status='0' AND fk_sl_pcv='$kPCV'" );
            $ra['nDesc']   = $this->kfdb->Query1( "SELECT count(*) FROM seeds_1.sl_varinst WHERE _status='0' AND fk_sl_pcv='$kPCV'" );

            $ra['nSrcCv1'] = $this->kfdb->Query1( "SELECT count(*) FROM seeds_1.sl_cv_sources WHERE _status='0' AND fk_sl_pcv='$kPCV' AND fk_sl_sources='1'" );
            $ra['nSrcCv2'] = $this->kfdb->Query1( "SELECT count(*) FROM seeds_1.sl_cv_sources WHERE _status='0' AND fk_sl_pcv='$kPCV' AND fk_sl_sources='2'" );
            $ra['nSrcCv3'] = $this->kfdb->Query1( "SELECT count(*) FROM seeds_1.sl_cv_sources WHERE _status='0' AND fk_sl_pcv='$kPCV' AND fk_sl_sources>='3'" );

            $ra['nTotal'] = $ra['nAcc'] + $ra['nAdopt'] + $ra['nDesc'] +
                            $ra['nSrcCv1'] + $ra['nSrcCv2'] + $ra['nSrcCv3'];

            $this->raReferencesToPCV[$kPCV] = $ra;
        }

        return( $this->raReferencesToPCV[$kPCV] );
    }

    function DrawReferencesToPCV( $kPCV )
    {
        $ra = $this->GetReferencesToPCV( $kPCV );

        $s = "Seed Library accessions: {$ra['nAcc']}<br/>"
            ."Seed Library adoptions: {$ra['nAdopt']}<br/>"
            ."Source list records: "
            .($ra['nSrcCv1'] ? "PGRC, " : "")
            .($ra['nSrcCv2'] ? "NPGS, " : "")
            .($ra['nSrcCv3']." compan".($ra['nSrcCv3'] == 1 ? "y" : "ies"))."<br/>"
            ."Seed Library crop descriptions: {$ra['nDesc']}";

        if( $ra['nTotal'] == 0 ) $s .= "<br/><br/><span style='color:red'>This cultivar is not used in the seed library or seed sources</span>";

        return( $s );
    }

}

?>
