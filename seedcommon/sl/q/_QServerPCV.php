<?php

/* _QServerPCV
 *
 * Copyright 2015-2016 Seeds of Diversity Canada
 *
 * Serve queries about species/cultivar names
 * (this will probably be replaced by something like _QServerRosetta)
 */

include_once( SEEDCOMMON."sl/sl_db.php" );
include_once( "Q.php" );

class QServerPCV
{
    private $sHelp = "
    <h2>Seed species and cultivar names</h2>

    <p>Species metadata</p>
    <p style='margin-left:30px'>cmd=rosettaSpecies&[parameters...]<p>
    <ul style='margin-left:30px'>
    <li>kSp : integer key for a species</li>
    </ul>
    <p style='margin-left:30px'>Return (single result in array)</p>
    <ul style='margin-left:30px'>
    <li>psp : the human-readable species code</li>
    <li>name_en, name_fr : common species names</li>
    <li>iname_en, iname_fr : common species names suitable for an alphabetized index</li>
    <li>name_bot : botanical species name</li>
    <li>family_en, family_fr : botanical family names</li>
    <li>category : GRAIN, HERB, FRUIT, FLOWER, VEG</li>
    </ul>

    <p>Cultivar metadata</p>
    <p style='margin-left:30px'>cmd=rosettaPCV&[parameters...]<p>
    <ul style='margin-left:30px'>
    <li>kPCV (integer key for a cultivar) : return information about this cultivar</li>
    <li>kSp,name (integer key for a species, plus a cultivar name) : return information about this named cultivar of this species</li>
    <li>eTable=pcv : search primary cultivar names only (used with kSp,name)</li>
    <li>eTable=syn : search synonym cultivar names only (used with kSp,name)</li>
    <li>eTable=pcv+syn : search primary and synonym cultivar names (used with kSp,name)</li>
    </ul>
    <p style='margin-left:30px'>Return (single result in array)</p>
    <ul style='margin-left:30px'>
    <li>P__key : integer key for the cultivar</li>
    <li>P_name : the cultivar name</li>
    <li>S__key : integer key for the cultivar's species</li>
    <li>S_psp : human-readable code for the cultivar's species</li>
    <li>S_name_en, S_name_fr : common names for the cultivar's species</li>
    </ul>

    <p>Species list</p>
    <p style='margin-left:30px'>cmd=rosettaSpeciesList&[parameters...]<p>
    <ul style='margin-left:30px'>
    <li>fmt=psp-key : return results in array keyed by species code {psp:kSp}</li>
    <li>fmt=key-psp : return results in array keyed by species key {kSp:psp}</li>
    </ul>
    <p style='margin-left:30px'>Return (one result per species)</p>
    <ul style='margin-left:30px'>
    <li>psp : the human-readable species code</li>
    <li>kSp : the machine-readable species key</li>
    </ul>

    <p>Cultivars of a given species</p>
    <p style='margin-left:30px'>cmd=rosettaPCVList&[parameters...]<p>
    <ul style='margin-left:30px'>
    <li>kSp : integer key for a species</li>
    </ul>
    <p style='margin-left:30px'>Return (one result for each cultivar)</p>
    <ul style='margin-left:30px'>
    <li>fmt=name-key : return results in array keyed by cultivar name {name:kPCV}</li>
    <li>fmt=key-name : return results in array keyed by cultivar key {kPCV:name}</li>
    </ul>

    <p>Cultivars found by a search token</p>
    <p style='margin-left:30px'>cmd=rosettaPCVSearch&[parameters...]<p>
    <ul style='margin-left:30px'>
    <li>srch : search string matched like this -> (name like '%{srch}%' or _key='{srch}'</li>
    </ul>
    <p style='margin-left:30px'>Return (one result for each match)</p>
    <ul style='margin-left:30px'>
    <li>see results for rosettaPCV</li>
    </ul>

    ";

    private $oQ;
    private $oSLDBMaster;
    private $oSLDBRosetta;

    function __construct( Qold $oQ )
    {
        $this->oQ = $oQ;
        $this->oSLDBMaster = new SLDB_Master( $oQ->kfdb, $oQ->sess->GetUID() );
        $this->oSLDBRosetta = new SLDB_Rosetta( $oQ->kfdb, $oQ->sess->GetUID() );
    }

    function Cmd( $cmd, $parms )
    {
        $rQ = $this->oQ->GetEmptyRQ();

        // cmds containing -- require write access (at least - other tests might be done too)
        if( strpos( $cmd, "--" ) !== false && !$this->oQ->sess->TestPerm( 'SLRosetta', 'W' ) ) {
            $rQ['sErr'] = "Command requires RosettaSEED write permission";
            goto done;
        }

        switch( $cmd ) {
            case 'rosettaHelp':        $rQ['bOk'] = true;  $rQ['sOut'] = $this->sHelp;    break;

            case 'rosettaSpeciesList': if( ($ra = $this->speciesList($parms) ) ) { $rQ['bOk'] = true; $rQ['raOut'] = $ra; }  break;
            case 'rosettaPCV':         if( ($ra = $this->pcvGet($parms) ) )      { $rQ['bOk'] = true; $rQ['raOut'] = $ra; }  break;
            case 'rosettaPCVList':     if( ($ra = $this->pcvList($parms) ) )     { $rQ['bOk'] = true; $rQ['raOut'] = $ra; }  break;
            case 'rosettaPCVSearch':   if( ($ra = $this->pcvSearch($parms)) )    { $rQ['bOk'] = true; $rQ['raOut'] = $ra; }  break;

            case 'rosettaPCV--Add':
                list($ra,$rQ['sErr']) = $this->pcvWriteAdd($parms);
                if( $ra ) { $rQ['bOk'] = true; $rQ['raOut'] = $ra; }
                break;
            case 'rosettaPCV--AddSyn':
                list($ra,$rQ['sErr']) = $this->pcvWriteAddSyn($parms);
                if( $ra ) { $rQ['bOk'] = true; $rQ['raOut'] = $ra; }
                break;

            case 'rosettaSpecies':
                if( ($kSp = intval(@$parms['kSp'])) ) {
                    if( ($kfr = $this->oSLDBMaster->GetKFR( "S", $kSp )) ) {
                        $rQ['bOk'] = true;
                        $rQ['raOut']['_key'] = $kfr->Key();
                        foreach( array('psp','name_en','name_fr','iname_en','iname_fr','name_bot','family_en','family_fr','category') as $k ) {
                            $rQ['raOut'][$k] = $this->oQ->QCharset( $kfr->Value($k) );
                        }
                    }
                }
                break;

            default:
                break;
        }

        done:
        return( $rQ );
    }

    private function pcvGet( $parms )
    /********************************
        Return info about a single pcv

        kPCV     : for this pcv
        kSp,name : for this pcv
        eTable   : used with (kSp,name)
                   'pcv' look in sl_pcv
                   'syn' look in sl_pcv_syn
                   'pcv+syn' look in both (default)

        Use cases:
        1) kPCV in pcv or pcv+syn      = just get the sl_pcv record
        2) kPCV,name in syn            = only matches if a syn record contains an exact (kPCV,name) match
        3) kSp,name in pcv or pcv+syn  = just get the sl_pcv record
        4) kSp,name in syn or pcv+syn  = if (3) fails look in syn for a matching kPCV
     */
    {
        $ra = array();

        $eTable = SEEDStd_ArraySmartVal( $parms, 'eTable', array('pcv+syn','pcv','syn') );
        $kPCV = intval(@$parms['kPCV']);
        $kSp = intval(@$parms['kSp']);
        $name = @$parms['name'];
        $dbName = addslashes($name);

        // 1) kPCV is always found in sl_pcv
        if( $kPCV && ($eTable == 'pcv' || $eTable == 'pcv+syn') ) {
            $kfr = $this->oSLDBMaster->GetKFR( "PxS", $kPCV );
            goto found;
        }

        // 2) kPCV,name in syn (don't test 3 or 4 if these parms are present)
        if( $kPCV && $name && $eTable=='syn' ) {
            $kfr = $this->oSLDBRosetta->GetKFRCond( "PYxPxS", "PY.name='$dbName' AND P._key='$kPCV'" );
            goto found;
        }

        // 3) kSp,name in pcv (fall through if not found)
        if( $kSp && $name && ($eTable == 'pcv' || $eTable == 'pcv+syn') ) {
            $kfr = $this->oSLDBMaster->GetKFRCond( "PxS", "P.name='$dbName' AND P.fk_sl_species='$kSp'" );
            if( $kfr ) goto found;
        }
        // 4) kSp,name in syn
        if( $kSp && $name && ($eTable == 'syn' || $eTable == 'pcv+syn') ) {
            $kfr = $this->oSLDBMaster->GetKFRCond( "PYxPxS", "PY.name='$dbName' AND P.fk_sl_species='$kSp'" );
        }

        found:
        if( $kfr ) {
            $ra = $this->pcvGetRaFromKFR( $kfr );
        }

        done:
        return( $ra );
    }


    private function pcvSearch( $parms )
// TODO: option to union this result with a search of sl_pcv_syn, with another option to uniquify the union on kPCV
    {
        $srch = @$parms['srch'];
        $ra = array();
        $dbSrch = addslashes($srch);
        $sCond = $srch ? ("P.name LIKE '%$dbSrch%' OR P._key='$dbSrch'") : "";
        if( ($kfr = $this->oSLDBMaster->GetKFRC( "PxS", $sCond, array() )) ) {
            while( $kfr->CursorFetch() ) {
                $ra[] = $this->pcvGetRaFromKFR( $kfr );
            }
        }
        return( $ra );
    }

    private function pcvGetRaFromKFR( $kfr )
    {
        if( $kfr->Value('P__key') ) {
            // PYxPxS : name is PY.name, _key is PY._key
            $P_name = $kfr->Value('P_name');
            $P__key = $kfr->Value('P__key');
        } else {
            // PxS : name is P.name, _key is P._key
            $P_name = $kfr->Value('name');
            $P__key = $kfr->Value('_key');
        }
        return( array( 'P_name'=>$this->oQ->QCharset($P_name),
                       'P__key'=>$P__key,
                       'S__key'=>$kfr->Value('S__key'),
                       'S_psp'=>$this->oQ->QCharset($kfr->Value('S_psp')),
                       'S_name_en'=>$this->oQ->QCharset($kfr->Value('S_name_en'))
              ));
    }

    private function speciesList( $parms )
    /*************************************
        Return a list of species in various formats

        TODO: filters
        TODO: an option to include synonyms
     */
    {
        $raOut = array();

        if( ($kfr = $this->oSLDBMaster->GetKFRC( "S", "S.psp<>''", array('sSortCol'=>'psp') )) ) {
            while( $kfr->CursorFetch() ) {
                switch( @$parms['fmt'] ) {
                    case 'key-psp':
                        $raOut[$kfr->Key()] = $kfr->Value('psp');
                        break;

                    case 'psp-key':     // array( psp=>key,...) appropriate for <select> list of psp names
                    default:
                        $raOut[$kfr->Value('psp')] = $kfr->Key();
                        break;
                }
            }
        }

        return( $raOut );
    }

    private function pcvList( $parms )
    /*********************************
        Return a list of cultivars in various formats

        kSp = for this species
     */
    {
        $raOut = array();

        $raCond = array( "P.name<>''" );
        if( ($kSp = intval(@$parms['kSp'])) ) {
            $raCond[] = "P.fk_sl_species='$kSp'";
        }

        if( ($kfr = $this->oSLDBMaster->GetKFRC( "P", implode(" AND ", $raCond), array('sSortCol'=>'name') )) ) {
            while( $kfr->CursorFetch() ) {
                switch( @$parms['fmt'] ) {
                    case 'key-name':
                        $raOut[$kfr->Key()] = $kfr->Value('name');
                        break;

                    case 'name-key':    // array( name=>key,...) appropriate for <select> list of names
                    default:
                        $raOut[$kfr->Value('name')] = $kfr->Key();
                        break;
                }
            }
        }

        return( $raOut );
    }

    private function pcvWriteAdd( $parms )
    /*************************************
        Add a row to sl_pcv
            kSp   = fk_sl_species
            name  = name
            t     = t
            notes = notes

        return array( kPCV => new key ) or empty array if error
     */
    {
        $raOut = array();
        $sErr = "";

        $kSp = intval(@$parms['kSp']);
        $name = @$parms['name'];

        if( !$kSp || !$name ) goto done;

        // Check if the name is already present in sl_pcv or sl_pcv_syn
        if( ($raPCV = $this->pcvGet( array('kSp'=>$kSp, 'name'=>$name, 'eTable'=>'pcv+syn') )) ) {
            $sErr = "Name $name for species $kSp is already present in the cultivars tables";
            goto done;
        }

        // insert the name into sl_pcv
        if( ($kfr = $this->oSLDBMaster->GetKfrel("P")->CreateRecord()) ) {
            $kfr->SetValue( 'fk_sl_species', $kSp );
            $kfr->SetValue( 'name', $name );
            $kfr->SetValue( 't', intval(@$parms['t']) );
            $kfr->SetValue( 'notes', @$parms['notes'] );
            if( $kfr->PutDBRow() ) {
                $raOut['kPCV'] = $kfr->Key();

                $this->oQ->kfdb->Execute( "UPDATE seeds_1.sl_pcv
                                           SET sound_soundex=soundex(name),sound_metaphone=metaphone(name)
                                           WHERE _key='".$kfr->Key()."'" );
            }
        }

        done:
        return( array($raOut,$sErr) );
    }

    private function pcvWriteAddSyn( $parms )
    /****************************************
        Add a row to sl_pcv_syn
            kPCV  = fk_sl_pcv
            name  = name
            t     = t
            notes = notes

        return array( kPY => new key ) or empty array if error
     */
    {
        $raOut = array();
        $sErr = "";

        $kPCV = intval(@$parms['kPCV']);
        $name = @$parms['name'];

        if( !$kPCV || !$name ) goto done;

        // Check if the synonym is already present in sl_pcv_syn
        if( ($raPCV = $this->pcvGet( array('kPCV'=>$kPCV, 'name'=>$name, 'eTable'=>'syn') )) ) {
            $sErr = "Name $name is already a synonym for pcv $kPCV";
            goto done;
        }

        // insert the name into sl_pcv_syn
        if( ($kfr = $this->oSLDBMaster->GetKfrel("PY")->CreateRecord()) ) {
            $kfr->SetValue( 'fk_sl_pcv', $kPCV );
            $kfr->SetValue( 'name', $name );
            $kfr->SetValue( 't', intval(@$parms['t']) );
            $kfr->SetValue( 'notes', @$parms['notes'] );
            if( $kfr->PutDBRow() ) {
                $raOut['kPY'] = $kfr->Key();
            }
        }

        done:
        return( array($raOut,$sErr) );
    }

}

?>
