<?php

/* _QServerRosetta
 *
 * Copyright 2016 Seeds of Diversity Canada
 *
 * Serve queries about species and cultivar names
 * (basically queries involving sl_species and/or sl_pcv and their synonym tables)
 */

include_once( SEEDCOMMON."sl/sl_db.php" );

class QServerRosetta
{
    private $oSLDBRosetta;
    private $bUTF8 = false;

    function __construct( KeyFrameDB $kfdb, $raParms = array() )
    {
        $this->oSLDBRosetta = new SLDB_Rosetta( $kfdb, 0 );
        $this->bUTF8 = intval(@$raParms['bUTF8']);
    }

    function GetSpeciesDetails( $kSp )
    /*********************************
        Given an sl_species key, return the nomenclature details
     */
    {
        $raOut = array();

        if( ($kfr = $this->oSLDBRosetta->GetKFR( "S", $kSp )) ) {
            $raOut['_key'] = $ra['S__key'] = $kfr->Key();

            foreach( array( 'psp','name_en','name_fr','name_bot','iname_en','iname_fr','family_en','family_fr') as $f )  {
                $raOut[$f] = $ra["S_$f"] = $kfr->Value($f);
            }
// $raOut['raSyn'] = array() the sl_species_syn
        }

        return( $raOut );
    }
}

?>
