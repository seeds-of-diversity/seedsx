<?php

/* _QServerSourceCV
 *
 * Copyright 2015-2018 Seeds of Diversity Canada
 *
 * Serve queries about sources of cultivars
 * (basically queries involving sl_sources and/or sl_cv_sources)
 */

include_once( SEEDCOMMON."sl/sl_db.php" );
include_once( "Q.php" );

class QServerSourceCV
{
    private $sHelp = "
    <h2>Data about seed sources</h2>
    <h4>Seed companies</h4>
    <p><i>Use this to get metadata about seed companies, filtered by company, location, or what they offer (species/cultivars/organic).</i></p>
    <p style='margin-left:30px'>cmd=srcSources&[parameters...]<p>
    <ul style='margin-left:30px'>
    <li>kSp (integer key of a seed species) : return companies that sell this species</li>
    <li>kPcv (integer key of a seed cultivar) : return companies that sell this cultivar</li>
    <li>bOrganic (boolean) : limit results to certified organic seeds of the above species/varieties</li>
    <li>sProvinces (string e.g. 'QC SK NB') : return companies located in the given province(s)</li>
    <li>sRegions (string e.g. 'QC AC') : return companies located in the given regions BC, PR=prairies, ON, QC, AC=Atlantic Canada</li>
    </ul>
    <p style='margin-left:30px'>Return (one result per company)</p>
    <ul style='margin-left:30px'>
    <li>SRC__key : integer key of seed company</li>
    <li>SRC_name : name of seed company</li>
    <li>SRC_address, SRC_city, SRC_prov, SRC_postcode : address of seed company</li>
    <li>SRC_email : email address of seed company</li>
    <li>SRC_web : web site of seed company</li>
    </ul>

    <h4>Species available from seed companies</h4>
    <p><i>Use this to get the species offered by a subset of seed companies, filtered by company, location, etc.</i></p>
    <p style='margin-left:30px'>cmd=srcSpecies&[parameters...]<p>
    <ul style='margin-left:30px'>
    <li>bAllComp : override other parameters, include species from every seed company</li>
    <li>rngComp (a range string) : include species from a range of seed companies (not implemented)</li>
    <li>bAll : override other parameters, include species from every possible source (not implemented)</li>
    <li>bPGRC : include species in the PGRC collection (not implemented)</li>
    <li>bNPGS : include species in the NPGC collection (not implemented)</li>
    <li>bSoDSL : include species in the SoD seed library (not implemented)</li>
    <li>bSoDMSD : include species in the SoD member seed directory (not implemented)</li>
    <li>bOrganic (boolean) : limit results to certified organic seeds (not implemented)</li>
    <li>sProvinces (string e.g. 'QC SK NB') : return companies located in the given province(s) (not implemented)</li>
    <li>sRegions (string e.g. 'QC AC') : return companies located in the given regions BC, PR=prairies, ON, QC, AC=Atlantic Canada (not implemented)</li>
    <li>outFmt : NameKey = return array(name=>kSp), KeyName = return array(kSp=>name), Name = return array(name), Key => return array(kSp)</li>
    </ul>
    <p style='margin-left:30px'>Return (one result per species)</p>
    <ul style='margin-left:30px'>
    <li>see outFmt above</li>
    </ul>

    <h4>Cultivars available from seed companies</h4>
    <p><i>Use this to search for cultivars available from a subset of seed companies, filtered by company, location, etc.</i></p>
    <p style='margin-left:30px'>cmd=srcCultivars&[parameters...]<p>
    <ul style='margin-left:30px'>
    <li>sSrch : search string that matches species and cultivar names, limited by other parameters</li>
    <li>kSp (integer key of a seed species) : limit to cultivars of this species</li>
    <li>bOrganic (boolean) : limit to cultivars available as certified organic</li>
    <li>sProvinces (string e.g. 'QC SK NB') : return companies located in the given province(s) (not implemented)</li>
    <li>sRegions (string e.g. 'QC AC') : return companies located in the given regions BC, PR=prairies, ON, QC, AC=Atlantic Canada</li>
    <li>sMode='TopChoices' : overrides all other parameters and returns the most popular cultivars - can be a nice default if search is blank</li>
    </ul>
    <p style='margin-left:30px'>Return (one result per cultivar)</p>
    <ul style='margin-left:30px'>
    <li>P__key : integer key for cultivar</li>
    <li>P_name : cultivar name</li>
    <li>S_name_en : English name of the cultivar's species</li>
    </ul>


    <h4>Seeds available from seed companies</h4>
    <p><i>Use this to look up specific relations between seed cultivars and sources</i></p>
    <p style='margin-left:30px'>cmd=srcSrcCv&[parameters...]<p>
    <ul style='margin-left:30px'>
    <li>kSrc (integer key of a seed company) : return seed varieties sold by this company</li>
    <li>kSp (integer key of a seed species) : return seed varieties/companies for this species</li>
    <li>kPcv (integer key of a seed cultivar) : return companies that sell this cultivar</li>
    <li>bOrganic (boolean) : limit results to certified organic seeds of the above species/varieties</li>
    <li>bAllComp (boolean) : search all companies (kSrc==0) does not imply this)</li>
    </ul>
    <p style='margin-left:30px'>Return (one result per company x cultivar)</p>
    <ul style='margin-left:30px'>
    <li>SRCCV__key : internal key for this (company,cultivar)</li>
    <li>SRCCV_fk_sl_species : integer key for species</li>
    <li>SRCCV_fk_sl_pcv : integer key for cultivar</li>
    <li>SRCCV_osp : species name</li>
    <li>SRCCV_ocv : cultivar name</li>
    <li>SRCCV_bOrganic (boolean) : seed cultivar is certified organic from this company</li>
    </ul>
    ";

    private $oQ;
    private $oSLDBSrc;
    private $bUTF8 = false;

// bUTF8 should be defined in Q instead
    function __construct( Q $oQ, $raParms = array() )
    {
        $this->oQ = $oQ;
        $this->oSLDBSrc = new SLDB_Sources( $oQ->kfdb, $oQ->sess->GetUID() );
        $this->bUTF8 = intval(@$raParms['bUTF8']);
    }

    function Cmd( $cmd, $parms )
    {
        $rQ = $this->oQ->GetEmptyRQ();

        if( $cmd == 'srcHelp' ) {
            $rQ['bOk'] = true;
            $rQ['sOut'] = $this->sHelp;
        }


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

            $rQ['sLog'] = SEEDStd_ImplodeKeyValue( $raParms, "=", "," );

// add sSrch to match SRC.name LIKE '%sSrch%'
            if( ($raSources = $this->GetSources( $raParms )) ) {
                $rQ['bOk'] = true;
                $rQ['raOut'] = $raSources;
            }
        }

        /* Species offered by seed companies (one row per species)
         * Filter by species criteria and seed company criteria.
         */
        if( $cmd == 'srcSpecies' ) {
            if( ($ra = $this->ListSpecies( $parms )) ) {
                $rQ['bOk'] = true;
                $rQ['raOut'] = $ra;
            }
            $rQ['sLog'] = SEEDStd_ImplodeKeyValue( $parms, "=", "," );
        }

        /* Cultivars offered by seed companies (one row per cultivar)
         */
        if( $cmd == 'srcCultivars'
            || $cmd == 'srcCultivarSearch' )    // Deprecate in favour of srcCultivars
        {
            $raParms = array();
            if( ($p = intval(@$parms['kSp'])) )       $raParms['kSp'] = $p;
            if( ($p = intval(@$parms['bOrganic'])) )  $raParms['bOrganic'] = $p;
            if( ($p = @$parms['sSrch']) )             $raParms['sSrch'] = $p;
            if( ($p = @$parms['sRegions']) )          $raParms['sRegions'] = $p;
            if( ($p = @$parms['sMode']) )             $raParms['sMode'] = $p;

            $rQ['sLog'] = SEEDStd_ImplodeKeyValue( $raParms, "=", "," );

            // This has to have at least some parameters or it tries to fetch the whole SrcCV table (use a parm to do that, not the default).
            if( count($raParms) && ($ra = $this->listCultivars( $raParms )) ) {
                $rQ['bOk'] = true;
                $rQ['raOut'] = $ra;
            }
        }

        /* Cultivars X Sources offered by seed companies and/or seed banks (one row per SrcCv)
         */
        if( $cmd == 'srcSrcCv' ) {
            $raParms = $this->normalizeParms( $parms );

            // Currently default is true. This should possibly not be a public user parm. Or maybe it's just not advertised or encouraged.
            //$raParms['bSanitize'] = intval(@$parms['bSanitize']);

            // you can make app/q work really hard if you try to read too much
// maybe not needed anymore if normalizeParms is forcing bAllComp when src=""?
//            if( (@$raParms['bNPGS'] || @$raParms['bPGRC']) && !(@$raParms['kSp'] || @$raParms['kPcv']) )  goto done;

            $rQ['sLog'] = SEEDStd_ImplodeKeyValue( $raParms, "=", "," );

            if( ($ra = $this->getSrcCV( $raParms )) ) {
                $rQ['bOk'] = true;
                $rQ['raOut'] = $ra;
            }
        }
        /* A variation of srcSrcCv that produces a CSCI update spreadsheet
         */
        if( $cmd == 'srcCSCI' ) {
            $raParms = $this->normalizeParms( array_merge( $parms,  array( 'bCSCICols'=>true,
                                                                           'kfrcParms'=>array('sSortCol'=>'SRCCV.osp,SRCCV.ocv') )) );

            $rQ['sLog'] = SEEDStd_ImplodeKeyValue( $raParms, "=", "," );

            if( ($ra = $this->getSrcCV( $raParms )) ) {
                $rQ['bOk'] = true;
                $rQ['raOut'] = $ra;

                if( ($k = intval(@$parms['kSrc'])) ) {
                    $rQ['raMeta']['name'] = $this->oQ->kfdb->Query1( "SELECT name_en FROM seeds.sl_sources WHERE _key='$k'" );
                } else if( $raParms['bAllComp'] ) {
                    $rQ['raMeta']['name'] = "All Companies";
                }
            }
        }

        /* Download ESF/CSCI statistics based on the log files
         */
        if( $cmd == 'srcESFStats' ) {
            $raParms = array( 'v' => intval(@$parms['v']) );    // select the type of report

            $rQ['sLog'] = SEEDStd_ImplodeKeyValue( $raParms, "=", "," );

            if( ($ra = $this->getSrcESFStats( $raParms )) ) {
                $rQ['bOk'] = true;
                $rQ['raOut'] = $ra;
            }
        }

        done:
        return( $rQ );
    }


    private function ListSpecies( $raParms )
    /***************************************
        Return sorted list of species available from given sources

            raParms:
                bAll     : default=false    include species from every possible source
                bAllComp : default=false    include species from any company
                rngComp  : a range string   include species from companies in this range
                bPGRC    : default=false    include species in the pgrc collection
                bNPGS    : default=false    include species in the npgs collection
                bSoDSL   : default=false    include species in the SoD seed library
                bSoDMSD  : default=false    include species in the SoD member seed directory

                outFmt   : NameKey : return array( name => _key )
                           KeyName : return array( _key => name )
                           Name    : return array( name )
                           Key     : return array( _key )

                spMap    : the namespace of sl_species_map for which map.appnames and map.keys are returned (default: sl_species names and keys)
     */
    {
        $raOut = array();

        $condDB = "";   // default is to read all sl_cv_sources
        $bReadSLCV = true;

        $raParms['outFmt'] = SEEDCore_SmartVal( @$raParms['outFmt'], array("Key","Name","KeyName","NameKey") );

        if( @$raParms['bAll'] ) {
            $raParms['bSoDSL'] = true;
            $raParms['bSoDMSD'] = true;
        } else {
            $raParms['bSoDSL']  = intval(@$raParms['bSoDSL']);
            $raParms['bSoDMSD'] = intval(@$raParms['bSoDMSD']);

            $raBank = array();
            if( @$raParms['bPGRC'] )  $raBank[] = 1;
            if( @$raParms['bNPGS'] )  $raBank[] = 2;

            $fld = "SRCCV.fk_sl_sources";

            if( @$raParms['bAllComp'] ) {
                // read all companies, and possibly seed banks too
                $condDB = "$fld >= 3";
                if( count($raBank) ) {
                    $sCondDB .= " OR ".SEEDCore_MakeRangeStrDB( $raBank, $fld );
                }
            } else if( ($r = @$raParms['rngComp']) ) {
                // a range of companies is given - merge with seed banks
                list($raR,$sRdummy) = SEEDCore_ParseRangeStr( $r );
                $raR = array_merge($raR,$raBank);
                if( count($raR) ) {
                    $condDB = SEEDCore_MakeRangeStrDB( $raR, $fld );
                }
            } else if( count($raBank) ) {
                // no companies, but seed banks
                $condDB = SEEDCore_MakeRangeStrDB( $raBank, $fld );
            } else {
                // no companies, no seed banks
                $bReadSLCV = false;
            }
        }

        if( $bReadSLCV ) {
            /* Get unique fk_sl_species referenced by sl_cv_sources where fk_sl_sources meets condDB
             * In a joined relation the fields clause limits the sql return to only the defined fields, but their
             * names have to match the kfrdef.  So S._key has to be called S__key here, or kfr won't know what to do with it.
             * Then, the kfrs (and arrays here) will have all the kfrdef values, but the non-fetched will be zero/blank.
             */
//$this->oSLDBSrc->kfdb->SetDebug(2);
            if( ($kfr = $this->oSLDBSrc->GetKFRC( "SRCCVxPxS", $condDB,
                                                  array( 'raFieldsOverride'=> array('S__key'=>"S._key",'S_name_en'=>"S.name_en",'S_name_fr'=>"S.name_fr"),
                                                         'sGroupCol'=>'S._key' ) )) )
            {
                while( $kfr->CursorFetch() ) {
                    $sp = $this->oQ->QCharset($kfr->Value('S_name_en'));
                    if( @$this->oQ->raParms['klugeESFLang'] == "FR" && ($spFR = $kfr->Value('S_name_fr')) ) {
                        $sp = $this->oQ->QCharset($spFR);
                    }
                    switch( $raParms['outFmt'] ) {
                        case "Key":     $raOut[] = $kfr->Value('S__key');      break;
                        case "Name":    $raOut[] = $sp;                        break;
                        case "KeyName": $raOut[$kfr->Value('S__key')] = $sp;   break;
                        case "NameKey": $raOut[$sp] = $kfr->Value('S__key');   break;
                    }
                }
            }

            /* If a species map is specified, use it to map sl_species._key/name to map._key/name
             * (when there are multiple map rows with the same fk_sl_species, any map._key of those
             *  rows is equivalently valid to identify the map relation)
             */
            if( @$raParms['spMap'] ) {
                // Get the map rows, keyed by fk_sl_species.
                // If multiple rows have the same fk_sl_species they will overwrite each other so one
                // random row will remain (any map._key is equivalent)
                $raMap = array();
                $raR = $this->oQ->kfdb->QueryRowsRA( "SELECT _key,fk_sl_species,appname_en FROM seeds.sl_species_map WHERE ns='".addslashes($raParms['spMap'])."'" );
                foreach( $raR as $ra ) {
                    $raMap[$ra['fk_sl_species']] = $ra;
                }

                // overwrite any fk_sl_species matches with the map key/name
                $raOld = $raOut;
                $raOut = array();
                if( $raParms['outFmt'] == 'KeyName' ) {
                    foreach( $raOld as $kSp => $sSpName ) {
                        if( @$raMap[$kSp] ) {
                            // found a mapped species
                            $kMap = $raMap[$kSp]['_key'];
                            $raOut['spapp'.$kMap] = $raMap['appname_en'];
                        } else {
                            // non-mapped species
                            $raOut['spk'.$kSp] = $sSpName;
                        }
                    }
                }

                if( $raParms['outFmt'] == 'NameKey' ) {
                    foreach( $raOld as $sSpName => $kSp ) {
                        if( @$raMap[$kSp] ) {
                            // found a mapped species
                            $kMap = $raMap[$kSp]['_key'];
                            $raOut[$raMap[$kSp]['appname_en']] = 'spapp'.$kMap;
                        } else {
                            // non-mapped species
                            $raOut[$sSpName] = 'spk'.$kSp;
                        }
                    }
                }

            }

            /* Sort by name (there could be a parm to disable this but why)
             */
            switch( $raParms['outFmt'] ) {
                case "Key":                       break;
                case "Name":    sort($raOut);     break;
                case "KeyName": asort($raOut);    break;
                case "NameKey": ksort($raOut);    break;
            }
        }
        return( $raOut );
    }

    private function listCultivars( $raParms )
    {
        $raOut = array();

        $sMode = @$raParms['sMode'];

        $raCond = array();
        $raCondKluge = array();
        $raKFParms = array();
        if( ($dbSrch = addslashes(@$raParms['sSrch'])) ) {
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
        $raKFParms['raFieldsOverride'] = array( 'S_name_en'=>"S.name_en", 'S_name_fr'=>"S.name_fr", 'S__key'=>"S._key", 'P_name'=>"P.name", 'P__key'=>"P._key", 'c'=>"count(*)" );
        $raKFParms['sGroupCol']     = 'S._key,P._key';

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
    if( ($ocv = $this->oSLDBSrc->kfdb->Query1("SELECT ocv FROM seeds.sl_cv_sources WHERE _key='".($kPcv-10000000)."'")) &&
        ($osp = $this->oSLDBSrc->kfdb->Query1("SELECT osp FROM seeds.sl_cv_sources WHERE _key='".($kPcv-10000000)."'")) ) {
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
    if( ($ra = $this->oSLDBSrc->kfdb->QueryRA("SELECT osp,ocv FROM seeds.sl_cv_sources WHERE _key='".($k-10000000)."'")) ) {
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

    private function getSrcESFStats( $raParms )
    {
        $raOut = array();

        switch( intval(@$raParms['v']) ) {
            case 1: $raOut = $this->getSrcESFStats1();  break;
            case 2: $raOut = $this->getSrcESFStats2();  break;
        }

        return( $raOut );
    }

    private function getSrcESFStats1()
    // Report on the contents of the CSCI log (species selected) and ESF log (species searched)
    {
        $raOut = array();
        $raTmp = array();   // collect stats here, then sort and copy them to raOut in Q format

        if( file_exists( ($fname = (SITE_LOG_ROOT."csci_sp.log")) ) &&
            ($f = fopen( $fname, "r" )) )
        {
            while( ($line = fgets($f)) !== false ) {
                $ra = array();
                preg_match( "/^([^\s]+) ([^\s]+) ([^\s]+) \| (.*)$/", $line, $ra );

                if( ($kSp = intval($ra[4])) ) {
                    $sp = $this->oQ->kfdb->Query1( "SELECT name_en FROM seeds.sl_species WHERE _key='$kSp'" );
                } else {
                    $sp = substr( $ra[4], 2 );
                }

                $sp = str_replace( "+", " ", $sp );                 // for some reason some names have + instead of spaces
                $sp = str_replace( "Broccooli", "Broccoli", $sp );  // typo in earlier logs
                $sp = str_replace( "Oriental", "Asian", $sp );      // don't call it that

                $raTmp[$sp] = intval(@$raTmp[$sp]) + 1;
            }
            fclose( $f );
        }

        if( file_exists( ($fname = (SITE_LOG_ROOT."q.log")) ) &&
            ($f = fopen( $fname, "r" )) )
        {
            while( ($line = fgets($f)) !== false ) {
            }
        }

        /* Species hits have been counted as array( sSp => n )
         * Sort by sSp and convert to array( 'sp'=>charset(sSp), 'n'=>n )
         */
        ksort($raTmp);
        foreach( $raTmp as $sp => $n ) {
            $raOut[] = array( 'sp'=>$this->charset($sp), 'n'=>$n );
        }

        return( $raOut );
    }

    private function getSrcESFStats2()
    // Report on the contents of the ESF log
    {
        $raOut = array();
        $raTmp = array();

        if( file_exists( ($fname = (SITE_LOG_ROOT."q.log")) ) &&
            ($f = fopen( $fname, "r" )) )
        {
            while( ($line = fgets($f)) !== false ) {
                $ra = array();
                preg_match( "/^([^\s]+)\s+([^\s]+)\s+([^\s]+)\s+([^\s]+)\s+([^\s]+)\s*(.*)$/", $line, $ra );

                $cmd = @$ra[5];
                if( $cmd == 'srcSources' &&
                    (substr( ($r = @$ra[6]), 0, 5 ) == 'kPcv=') &&
                    ($kPCV = intval(substr($r,5))) )
                {
                    if( $kPCV >= 10000000 ) {
                        list($kSp,$sCV) = $this->oQ->kfdb->QueryRA( "SELECT fk_sl_species,ocv FROM seeds.sl_cv_sources WHERE _key='".($kPCV-10000000)."'" );
                    } else {
                        list($kSp,$sCV) = $this->oQ->kfdb->QueryRA( "SELECT fk_sl_species,name FROM seeds.sl_pcv WHERE _key='$kPCV'" );
                    }
                    if( $kSp && $sCV ) {
                        $psp = $this->oQ->kfdb->Query1( "SELECT psp FROM seeds.sl_species WHERE _key='$kSp'" );
                        $raTmp[$psp."|".$sCV] = intval(@$raTmp[$psp."|".$sCV]) + 1;
                    }
                }
            }
        }

        /* CV source hits have been counted as array( psp|pname => n )
         * Sort by sp,cv and convert to array( 'sp'=>charset(sSp), 'cv'=>charset(pname), 'n'=>n )
         */
        ksort($raTmp);
        foreach( $raTmp as $k => $n ) {
            list($psp,$pname) = explode( '|', $k );
            $raOut[] = array( 'sp'=>$this->charset($psp), 'cv'=>$this->charset($pname), 'n'=>$n );
        }

        return( $raOut );
    }

    private function charset( $s )
    {
        return( $this->bUTF8 ? utf8_encode( $s ) : $s );
    }




}

?>