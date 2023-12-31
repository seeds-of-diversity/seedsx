<?php

/* Seed Sources - common methods and db access
 *
 * Copyright 2010-2018 Seeds of Diversity Canada
 *
 */
include_once( "sl_db.php" );
include_once( "sl_sources_charts.php" );   // probably want to put SLSourcesUI in its own file that references this
include_once( STDINC."SEEDLocal.php" );

class SLSourcesCommon
{
    const SLSOURCES_MIN_COMPANY_KEY = 3;

    public $kfdb;        // it's available in all the kfrels, but it seems weird to randomly get it from one of them
    public $kfrelSources;
    public $kfrelCVSources;
    public $kfrelCxS;
//    public $kfrelCxS_P;  // CVSources x Sources left-joined with sl_pcv

    function __construct( KeyFrameDB $kfdb, $uid, $sLogFile = "" )
    {
        $this->kfdb = $kfdb;
        $this->initKfrel( $kfdb, $uid, $sLogFile );
    }

    function GetSpeciesName( $kSpecies, $lang )
    {
        $s = "";

        if( $kSpecies ) {
            $ra = $this->kfdb->QueryRA( "SELECT name_en,name_fr FROM sl_species WHERE _key='$kSpecies'" );
            $s = ($lang == 'EN' ? (@$ra['name_en'] ? $ra['name_en'] : @$ra['name_fr'])
                                : (@$ra['name_fr'] ? $ra['name_fr'] : @$ra['name_en']));
        }
        return( $s );
    }

    private function getSpName( $ra, $lang, $bIndex )
    {
        $sp = "";

        if( $lang == 'FR' ) {
            $sp = ($bIndex && @$ra['iname_fr']) ? $ra['iname_fr'] : @$ra['name_fr'];
        }
        if( !$sp ) {
            $sp = ($bIndex && @$ra['iname_en']) ? $ra['iname_en'] : @$ra['name_en'];
        }
        return( $sp );
    }

    function GetSpeciesRA( $sCond = "", $lang = "EN", $raParms = array() )
    /*********************************************************************
        Get an array of the unique species of sl_cv_sources that meet the condition.

        raParms:
            bCompaniesOnly : don't count PGRC or NPGS
            bCount         : return the count of matching rows for each species
            bIndex         : true: get Index names; false: get regular names

        return( array( sl_species._key => array( 'name'=>computed name, 'n'=>optional count ), ...
     */
    {
        $raSp = array();

        $bIndex = intval(@$raParms['bIndex']);  // true: get Index names; false: get regular names

        if( @$raParms['bCompaniesOnly'] ) {
            if( $sCond )  $sCond = "($sCond) AND ";
            $sCond .= "C.fk_sl_sources >= 3";
        }
        $bCount = intval(@$raParms['bCount']);

// this fails in MariaDB because some of the returned fields are non-aggregated by the GROUP BY, but it works in MySQL because
// that engine recognizes that the non-aggregated columns are functionally dependent on the group column because it is a PRIMARY_KEY
        if( ($dbc = $this->kfdb->CursorOpen(
                        "SELECT S._key as k,S.name_en as name_en,S.name_fr as name_fr,S.iname_en as iname_en,S.iname_fr as iname_fr"
                       .($bCount ? ",count(*) as n" : "")
                       ." FROM sl_species S,sl_cv_sources C"
                       ." WHERE S._key=C.fk_sl_species AND C.fk_sl_species <> 0".($sCond ? " AND ($sCond)" : "")
                       ." GROUP BY S._key,S.name_en,S.name_fr,S.iname_en,S.iname_fr")) ) {
            while( $ra = $this->kfdb->CursorFetch( $dbc ) ) {
                $kSp = $ra['k'];

                $raSp[$kSp]= array( 'name_en' => $ra['name_en'],
                                    'name_fr' => $ra['name_fr'],
                                    'iname_en' => $ra['iname_en'],
                                    'iname_fr' => $ra['iname_fr'],
                                    'n'       => $bCount ? $ra['n'] : 0,    // counting isn't performed if not requested
                                    // compute the name by $lang and availability
                                    'name'    => $this->getSpName( $ra, $lang, $bIndex )
                                  );
            }
        }

        uasort( $raSp, array($this,'fnSpSortCallback') );    // sort the values, keeping the keys associated

        return( $raSp );
    }

    private function fnSpSortCallback( $a, $b )
    {
        return( strcasecmp( $a['name'], $b['name'] ) );
    }

// Move to SLSourcesDraw
    function SourceItemDraw( $kfr, $lang = 'EN', $raParms = array() )
    {
        $s = "";

        if( $lang == 'FR' ) {
            $lang = "fr";  $langother = "en";
        } else {
            $lang = "en";  $langother = "fr";
        }
        $bEdit = SEEDStd_ArraySmartVal( $raParms, 'bEdit', array(false,true) );

        $name = $kfr->valueEnt( !$kfr->IsEmpty('name_'.$lang) ? ('name_'.$lang) : ('name_'.$langother) );
        $addr = $kfr->valueEnt( !$kfr->IsEmpty('addr_'.$lang) ? ('addr_'.$lang) : ('addr_'.$langother) );
        $desc = $kfr->valueEnt( !$kfr->IsEmpty('desc_'.$lang) ? ('desc_'.$lang) : ('desc_'.$langother) );

        // Caller might wrap the name with a link or something
        // e.g. subst_name = "<a href=foo>[[name]]</a>   -- [[name]] is substituted with $name (which could be EN or FR as decided above)
        if( isset($raParms['subst_name']) ) {
// easier to use str_replace('[[name]]')
            $name = SEEDCore_ArrayExpand( array('name'=>$name), $raParms['subst_name'], false );  // bEnt=false because entities already expanded
        }

        $s .= "<SPAN style='font-size:11pt;font-weight:bold'>$name</SPAN><BR/>"
        ."<FONT size='2'>"
        .($addr ? ("<nobr>$addr</nobr><BR/>"
                   .$kfr->Expand( "<nobr>[[city]] [[prov]] [[postcode]]</nobr><BR/>"))
                : "")
        .$kfr->Expand( "Phone: [[phone]]<BR/>"
                      // stopPropagation() is a kluge to prevent the onclick of the containing div (which selects the company)
                      ."Web: <A HREF='http://[[web]]' TARGET='_blank' onclick='event.stopPropagation();'>[[web]]</A><BR/>"
                      ."Email: <A HREF='mailto:[[email]]'>[[email]]</A><BR/>" )
        .$kfr->ExpandIfNotEmpty( 'year_est', "Established: [[]]</BR>" )
        //.($kfr->value('bSupporter') ? "*<BR/>" : "")
        ."<div style=''>$desc</div>"
        ."</FONT><BR/>";

        if( $bEdit ) {
            $sNeeded = "";
            if( $kfr->value('bNeedXlat') )    $sNeeded .= "Translation ";
            if( $kfr->value('bNeedVerify') )  $sNeeded .= "Verification ";
            //if( $kfr->value('bNeedProof') )   $sNeeded .= "Proofreading ";
            if( $sNeeded )  $s .= "<BR/><FONT color='red' size='2'>Needs: $sNeeded</FONT>";
            $s .= $kfr->ExpandIfNotEmpty( 'comments', "<BR/><FONT size='2' color='blue'>Private comments: [[]]</FONT>" );
        }

        return( $s );
    }

    static public function RARegions( $lang = 'EN' )
    {
        $raRegions = $lang == 'EN'
                       ? array( 'bc' => 'B.C.', 'pr' => 'Prairies', 'on' => 'Ontario', 'qc' => 'Quebec', 'at' => 'Atlantic Canada' )
                       : array( 'bc' => 'C.-B.', 'pr' => 'Prairies', 'on' => 'Ontario', 'qc' => 'Qu&eacute;bec', 'at' => 'Canada atlantique' );
        return( $raRegions );
    }

    static public function RARegionSelect( $lang = 'EN' )
    {
        $raRegions = $lang == 'EN'
                       ? array(' All Regions '=>'','B.C.'=>'bc','Prairies'=>'pr','Ontario'=>'on','Quebec'=>'qc','Atlantic'=>'at')
                       : array(' Toutes les r&eacute;gions '=>'','Colombie-Brittanique'=>'bc','Prairies'=>'pr','Ontario'=>'on','Qu&eacute;bec'=>'qc','Canada atlantique'=>'at');
        return( $raRegions );
    }

    function initKfrel( &$kfdb, $uid, $sLogFile )
    {
        $fld_Sources = array(
        array("col"=>"sourcetype",    "type"=>"S"),
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
        array("col"=>"bNeedXlat",     "type"=>"I") );

        $fld_CVSources = array(
        array("col"=>"fk_sl_sources", "type"=>"K"),
        array("col"=>"fk_sl_pcv",     "type"=>"K"),
        array("col"=>"osp",           "type"=>"S"),
        array("col"=>"ocv",           "type"=>"S"),
        array("col"=>"bOrganic",      "type"=>"I")
        );

        $oApp = SEEDConfig_NewAppConsole_LoginNotRequired([]);

        $kfreldef_Sources = array(
            "Tables" => array( array( "Table" => 'sl_sources', "Alias" => 'S',
                                      "Fields" => $fld_Sources ) ) );
        $kfreldef_CVSources = array(
            "Tables" => array( array( "Table" => 'sl_cv_sources', "Alias" => 'C',
                                      "Fields" => $fld_CVSources ) ) );
        $kfreldef_CxS = array(
            "ver" => 2,
            "Tables" => array( "C" => array( "Table" => $oApp->DBName('seeds1').'.sl_cv_sources',
                                             "Type" => 'Base',
                                             "Fields" => $fld_CVSources ),
                               "S" => array( "Table" => $oApp->DBName('seeds1').'.sl_sources',
                                             "Fields" => $fld_Sources ) ) );
/*
        $kfreldef_CxS_P = array(
            "Tables" => array( array( "Table" => 'sl_cv_sources', "Alias" => 'C',
                                      "Type" => 'Base',
                                      "Fields" => $fld_CVSources ),
        array( "Table" => 'sl_sources', "Alias" => 'S',
                                      "Fields" => $fld_Sources ),
        array( "Table" => 'sl_pcv', "Alias" => 'P',
                                      "LeftJoinOn" => 'P._key=C.fk_sl_pcv',
                                      "Fields" => 'Auto' ) ) );
*/
        $this->kfrelSources   = new KeyFrameRelation( $kfdb, $kfreldef_Sources,   $uid, array('logfile' => $sLogFile) );
        $this->kfrelCVSources = new KeyFrameRelation( $kfdb, $kfreldef_CVSources, $uid, array('logfile' => $sLogFile) );
        $this->kfrelCxS       = new KeyFrameRelation( $kfdb, $kfreldef_CxS,       $uid, array('logfile' => $sLogFile) );
//        $this->kfrelCxS_P     = new KeyFrameRelation( $kfdb, $kfreldef_CxS_P,     $uid, array('logfile' => $sLogFile) );
    }
}

class SLSourcesDraw
/******************
    Get data and draw it.  This is readonly: parms for db-writing use defaults here.
 */
{
    private $oSrcCommon;

    function __construct( KeyFrameDB $kfdb )
    {
        $this->oSrcCommon = new SLSourcesCommon( $kfdb, 0 );
    }

/*
Actually these styles are just defined in drupal page-tpl because it isn't necessarily clear when to output this
    function Style()
    {
        $s = "<style>"
            .".slsrc_dcvblock_cv {}"
            .".slsrc_dcvblock_companies { margin:0px 0px 10px 30px;font-size:10pt; }"
            ."</style>";

        return( $s );
    }
*/

    function DrawSpeciesList( $sCond, $lang, $raParms = array() )
    {
        $s = "";

        $bIndex = intval(@$raParms['bIndex']);  // true: get Index names; false: get regular names

        $oTag = null;
        $oTagParser = new SEEDTagParser();

        //$raParms['bCount'] = false;
        //$raSp = $this->oSrcCommon->GetSpeciesRA( $sCond, $lang, $raParms );
        include_once( SEEDLIB."q/QServerSources.php");
        $oApp = SEEDConfig_NewAppConsole_LoginNotRequired([]);
        $oSrc = new QServerSourceCV( $oApp, ['config_bUTF8'=>false] );  // get species names in cp1252
        $rQ = $oSrc->Cmd( 'srcSpecies', ['bAllComp'=>true, 'outFmt'=>'KeyName', 'opt_spMap'=>'ESF', 'opt_bIndex'=>$bIndex] );

        foreach( $rQ['raOut'] as $k=>$spName ) {
            $oTagParser->SetVars( ['k' => $k, 'name' => $spName, 'n' => 0] );
            $s .= $oTagParser->ProcessTags( $raParms['sTemplate'] );
        }

        return( $s );
    }

    function DrawCompanies( $lang )
    {
        $s = "<h3>Seed Companies in Canada</h3>";

        // $sSortCol = $lang=='EN' ? "S.country,S.name_en" : "S.country,S.name_fr";
        // except the name_fr are mostly blank
        if( ($kfr = $this->oSrcCommon->kfrelSources->CreateRecordCursor( "S._key >= 3 AND S.country='Canada'", array('sSortCol'=>"S.name_en") )) ) {
            while( $kfr->CursorFetch() ) {
                $s .= $this->oSrcCommon->SourceItemDraw( $kfr, $lang );
            }
        }
        return( $s );
    }

    function DrawCompaniesVarieties( SEEDAppConsole $oApp, $kSp, $sSpKluge, $lang, $raParms = array() )
    {
include_once( SEEDCOMMON."siteutil.php" );
Site_Log( "csci_sp.log", date("Y-m-d H:i:s")." {$_SERVER['REMOTE_ADDR']} | $kSp $sSpKluge" );

        $s = "";

        if( !($sTemplateBlock = @$ra['sTemplateBlock']) ) {
            $sTemplateBlock = "<div class='slsrc_dcvblock_cv'>[[Var:cv]]</div>"
                             ."<div class='slsrc_dcvblock_companies'>[[Var:companies]]</div>";
        }

        if( $kSp ) {
            $sSpecies = $this->oSrcCommon->GetSpeciesName( $kSp, $lang );
            $sCond = "C.fk_sl_species=$kSp AND C.fk_sl_sources >= 3";    // limit to commercial companies only
        } else if( $sSpKluge ) {
            $sSpecies = $sSpKluge;
            $sCond = "C.osp='".addslashes($sSpKluge)."' AND C.fk_sl_sources >= 3";    // limit to commercial companies only
        } else {
            return( "" );
        }


        $s .= "<h3>$sSpecies - Varieties Sold in Canada</h3>";

// We really want to collect pcv and ocv and sort them
        if( ($kfr = $this->oSrcCommon->kfrelCxS->CreateRecordCursor( $sCond, array('sSortCol'=>'C.ocv,S.name_en') ) ) ) {
            $ocv = NULL;    // copy of sl_cv_sources.ocv for tracking when a group ends
            $kCV = 0;       // copy of sl_cv_sources.fk_sl_pcv for tracking when a group ends
            $sCV = "";
            $raCompanies = array();
            $bFirstTime = true;
            while( $kfr->CursorFetch() ) {
                if( $bFirstTime ||
                    ($kCV && $kCV != $kfr->value('fk_sl_pcv')) ||
                    (!$kCV && $ocv != $kfr->value('ocv')) )
                {
                    // new cultivar - draw the previous block if any, and reset the accumulator
                    if( count($raCompanies) ) {
                        $s .= $this->dcv_block( $sCV, $raCompanies, $sTemplateBlock );
                    }
                    $raCompanies = array();
                    $ocv = $kfr->value('ocv');
                    $kCV = $kfr->value('fk_sl_pcv');
                    if( $kCV ) {
                        $sCV = $this->oSrcCommon->kfdb->Query1( "SELECT name FROM {$oApp->DBName('seeds1')}.sl_pcv WHERE _key='$kCV'" );
                        $raSyn = $this->oSrcCommon->kfdb->QueryRowsRA1( "SELECT name FROM {$oApp->DBName('seeds1')}.sl_pcv_syn WHERE fk_sl_pcv='$kCV' AND t='1' AND _status='0'" );
                        if( count($raSyn) ) {
                            $sCV .= " / ".implode( " / ", $raSyn );
                        }
                    } else {
                        $sCV = $ocv;
                    }
                }

                $sCompany = $kfr->Expand( "<NOBR>[[S_name_en]]</NOBR>" );
                if( ($web = $kfr->value('S_web')) ) {
                    $sCompany = "<a href='http://$web' target='sl_source_company'>$sCompany</a>";
                }
                $raCompanies[] = $sCompany;
                $bFirstTime = false;
            }
            if( count($raCompanies) ) {
                $s .= $this->dcv_block( $sCV, $raCompanies, $sTemplateBlock );
            }
            if( $bFirstTime ) {
                $s .= "No records";
            }
        }

        return( $s );
    }

    private function dcv_block( $cv, $raCompanies, $sTemplateBlock )
    /***************************************************************
     */
    {
        $oTagParser = new SEEDTagParser();
        $oTagParser->SetVars(
                array( 'cv' => $cv,
                       // this puts two spaces between names, but allows line breaking to happen without inserting leading spaces
                       'companies' => implode( ",&nbsp; ", $raCompanies )
                     ) );
        $s = $oTagParser->ProcessTags( $sTemplateBlock );
        return( $s );
    }

}


class SLSourcesUI
{
    public  $oSrcCommon;
    private $kfdb; // shouldn't have to use this
    private $pSpec = "";
    private $oL;

    private $sLinkToMe;

    function __construct( KeyFrameDB $kfdb, $raParms = array() )
    {
        $this->oSrcCommon = new SLSourcesCommon( $kfdb, 0 );
        $this->kfdb = $kfdb;

        $lang = SEEDStd_ArraySmartVal( $raParms, 'lang', array( "EN", "FR" ) );
        $bDebug = SEEDSafeGPC_GetInt('debug');
        $this->oL = new SEEDLocalDBServer( $kfdb, $lang, "www.seeds.ca", "BautaSeedFinder", array( 'Testing' => $bDebug ) );

        // raParms['linkToMe']  = the_current_page?a=b&
        $this->sLinkToMe = SEEDStd_ArraySmartVal( $raParms, 'linkToMe', array( $_SERVER['PHP_SELF']."?" ), false );

        $this->pSpec   = SEEDSafeGPC_GetStrPlain( 'pSpec' );
    }

    function Style()
    {
        $s = "<STYLE>"
            .".sod_srcui_chartcontainer { width:450px;border:1px solid grey; margin:10px; padding:10px; }"
            .".sod_srcui_main  { font-family:arial,helvetica,sans serif; }"
            .".sod_srcui_main h2 {}"
            .".sod_srcui_main p { font-size:10pt; margin-left:40px }"
            .".sod_srcui_ctrl  { font-size:10pt; margin-left:40px; padding:10px;border:1px solid #888;background-color:#eee; }"
            .".sod_srcui_link  { font-weight:bold;}"
            .".sod_srcui_cvlist { font-size:10pt; margin-left:40px }"
            ."</STYLE>";

        return( $s );
    }

    function DrawDrillDown()
    {
        $s = "";

        $pMode = SEEDSafeGPC_Smart( 'sod_k', array( '', 'Com', 'Ov' /*, 'OvUS' */ ) );
        $kSpecies = SEEDSafeGPC_GetInt( 'sod_s' );
        $sRegion = SEEDSafeGPC_Smart( 'sod_r', array( '', 'bc', 'pr', 'on', 'qc', 'at' ) );
        $bOrganic = SEEDSafeGPC_GetInt( 'sod_o' ) ? true : false;

        switch( $pMode ) {
            default:
            case 'Com':  $s .= $this->Commercial( $kSpecies, $sRegion, $bOrganic );  break;
            case 'Ov':   $s .= $this->Overview( $kSpecies, false );    break;
            case 'OvUS': $s .= $this->Overview( $kSpecies, true );     break;
        }

        return( $s );
    }

    function Overview( $kSpecies, $bUS )
    {
        $s = "";


$bUS = false;


        $oChart = new SLSourcesCharts( $this->kfdb );

        $sSpecies = $this->getSpeciesName( $kSpecies );
        $raSpecies = $this->listSpecies();

        $sWhere = "Canada".($bUS ? " and the U.S." : "");
        $sTitle = $this->oL->S("Seed Varieties in _where_", array($sWhere) ).($sSpecies ? " (&nbsp;$sSpecies&nbsp;)" : "");

        $sChartFloat =
            "<DIV class='sod_srcui_chartfloat' style='float:left'>"
           ."<DIV class='sod_srcui_chartcontainer' style='height:400px'>"
            //."<div id='chart_div'></div>"
           .$oChart->SourcesOverview( $bUS, $kSpecies, $this->oL->S("Number of Varieties") )//$sTitle )
           ."<FORM action='' method='post'>"
           .SEEDForm_Hidden( 'sod_k', $bUS ? "US" : "" )
           ."<DIV class=sod_srcui_ctrl' style='text-align:center;margin-bottom:20px;'>".$this->oL->S("Show").": "
           .SEEDForm_Select2( 'sod_s', array_merge( array($this->oL->S2(" --- [[All Crops]] --- ")=>0), $raSpecies), $kSpecies, array('selectAttrs'=>"onchange='submit();'" ) )
           ."</DIV>"
           ."</FORM>"
           ."</DIV>"
           ."</DIV>";

        $sGoFindSeeds =
            "<div class='sod_srcui_modebox' style='float:right;margin:1em;padding:1em;border:1px solid #888;background-color:#e8e8e8;width:25%'>"
                .$this->oL->S('ESF_link_desc')
                ."<DIV class='sod_srcui_link' style='text-align:center'>"
                ."<h4><A href='".$this->linkToMe( array('sod_k'=>'Com','sod_s'=>$kSpecies) )."'>".$this->oL->S("Find Seeds Close to Home")."</A></h4>"
                ."</DIV>"
           ."</div>";


        $s .= "<DIV class='sod_srcui_main'>"
             .$sGoFindSeeds
             ."<H2>$sTitle</H2>"
             .$this->oL->S('Overview_instructions')
             ."<P>&nbsp</P>"
             .$sChartFloat

             ."<DIV class='sod_srcui_link'>"
//        .($bUS ? ("<A href='".$this->linkToMe( array('sod_k'=>'','s'=>$kSpecies) )."'>Show Canada only</A>")
//               : ("<A href='".$this->linkToMe( array('sod_k'=>'US','s'=>$kSpecies) )."'>Show Canada and U.S. Combined</A>") )
            ."</DIV>"
            ."</DIV>"; // sod_srcui_main
        return( $s );
    }

    private function frenchDeSpecies( $sSpecies )
    {
        $sFrenchSpecies = "";

        if( $sSpecies ) {
            if( in_array( strtolower(substr($sSpecies,0,1)), array('a','e','i','o','u') ) ) {
                $sFrenchSpecies = "d'".$sSpecies;
            } else {
                $sFrenchSpecies = "de ".$sSpecies;
            }
        }
        return( $sFrenchSpecies );
    }

    function Commercial( $kSpecies, $sRegion, $bOrganic )
    {
//$this->kfdb->SetDebug(2);
        $s = "";

        $oChart = new SLSourcesCharts( $this->kfdb );

        $sSpecies = $this->getSpeciesName( $kSpecies );
        $raSpecies = $this->listSpecies();

        $raRegions = $this->oSrcCommon->RaRegions( $this->oL->GetLang() );
        if( !($labelRegion = @$raRegions[$sRegion]) ) {
            $sRegion = '';
            $labelRegion = "Canada";
        }

        // Kluge: this heading is hard to bilingualize
        //        EN: [Certified Organic] Sources of [species] Seed in [Canada | region]
        //            this works if [species] is blank
        //
        //        FR: Sources de semences [au Canada | dans la region [region] ] [(certifi�es biologiques)]
        //            Sources de semences de [species] [au/dans] [(certifi�es biologiques)]
        //            Sources de semences d'[species] [au/dans] [(certifi�es biologiques)]
        $sTitle = $this->oL->S2( "[[Sources of _species_ Seed in _where_ ]]",
                                 array( 'species' => $sSpecies, 'region' => 'Canada'/*$labelRegion*/, 'bOrganic' => $bOrganic ) );

        $sForm1 =
             "<form action='' method='get'>"
            .SEEDForm_Hidden( 'sod_k', 'Com' )
            .SEEDForm_Hidden( 'sod_r', $sRegion )
            ."<div class='sod_srcui_ctrl' style='text-align:left;margin-bottom:20px'>".$this->oL->S("Show").": "
            .SEEDForm_Select2( 'sod_s', array_merge( array($this->oL->S2(" --- [[All Crops]] --- ")=>0), $raSpecies), $kSpecies, array('selectAttrs'=>"onchange='submit();'" ) )
            ."</div>"
            ."<div class='sod_srcui_ctrl' style='text-align:left;margin-bottom:20px'>"
            .SEEDForm_Checkbox( 'sod_o', $bOrganic, $this->oL->S("Only show certified organic"), array( 'attrs' => "onChange='submit();'") )
            ."</div>"
            ."</form>";

        $sForm2 =
             "<form action='' method='get'>"
            .SEEDForm_Hidden( 'sod_k', 'Com' )
            .SEEDForm_Hidden( 'sod_s', $kSpecies )
            .SEEDForm_Hidden( 'sod_o', $bOrganic )
            ."<DIV class='sod_srcui_ctrl' style='text-align:left;margin-bottom:20px'>"
            .$this->oL->S("provided in")."&nbsp;".SEEDForm_Select2( 'sod_r', $this->oSrcCommon->RARegionSelect($this->oL->GetLang()), $sRegion, array('selectAttrs'=>"onchange='submit();'" ) )
            ."</DIV>"
            ."</form>";


        $sChartFloat =
             "<DIV class='sod_srcui_chartfloat' style='float:left'>"
            ."<DIV class='sod_srcui_chartcontainer' >" // style='height:350px'>"
            // ."<div id='chart_div'></div>"
            .$oChart->SourcesCommercial( $kSpecies, $bOrganic, $this->oL->GetLang(), "" /*"Number of Seed Suppliers"*/ ) // $sTitle )
            ."</DIV>"
            .$this->showSuppliers( $kSpecies, $sRegion, $bOrganic )
            ."</DIV>";

        $sVarList =
            ($kSpecies ? (($this->oL->GetLang() == 'EN'
                             ? ("<h3>".$this->oL->S("Varieties of _species_", array($this->getSpeciesName( $kSpecies )) )."</h3>")
                             : ("<h3>Vari&eacute;t&eacute;s ".$this->frenchDeSpecies($this->getSpeciesName( $kSpecies ))."</h3>"))
                           .($sForm2.$this->showVarieties( $kSpecies, $sRegion, $bOrganic )))
                       : "<h3>Choose a crop species to find local varieties.</h3>" );

        $sGoOverview =
            "<div class='sod_srcui_modebox' style='float:right;margin:1em;padding:1em;border:1px solid #888;background-color:#e8e8e8;width:25%'>"
                ."[[Overview_link_desc]]"
                ."<DIV class='sod_srcui_link' style='text-align:center'>"
                ."<h4><A href='".$this->linkToMe( array('sod_k'=>'Ov','sod_s'=>$kSpecies) )."'>[[Overview_link_label]]</A></h4>"
                ."</DIV>"
           ."</div>";


        $s .= "<DIV class='sod_srcui_main'>"
             .$this->oL->S2(
                $sGoOverview
                ."<h2>[[Find Seeds Close to Home]]</h2>"
                ."[[Find_Seeds_instructions]]"
                 )
             ."<h3 style='clear:both;margin-top:30px'>$sTitle</h3>"

             ."<table style='clear:both' border='0' cellpadding='10' cellspacing='0'><tr valign='top'>"
             ."<td>"
                 .$sForm1
                 .$sChartFloat
             ."</td>"
             ."<td>"
                 .$this->oL->S('ESF_chart_instructions')
                 .$sVarList
             ."</td>"
             ."</tr></table>"
             ."</DIV>"; // sod_srcui_main

        return( $s );
    }

    function getSpeciesName( $kSpecies )
    {
        $s = "";

        if( $kSpecies ) {
            $ra = $this->kfdb->QueryRA( "SELECT name_en,name_fr FROM sl_species WHERE _key='$kSpecies'" );
            if( $this->oL->GetLang() == 'EN' ) {
                $s = @$ra['name_en'] ? $ra['name_en'] : @$ra['name_fr'];
            } else {
                $s = @$ra['name_fr'] ? $ra['name_fr'] : @$ra['name_en'];
            }
        }
        return( $s );
    }

    function listSpecies() // use SLSourcesCommon::GetSpeciesRA()
    {
        $raSp = array();

        if( ($dbc = $this->kfdb->CursorOpen( "SELECT S._key as k,S.name_en as name_en,S.name_fr as name_fr,C.fk_sl_species"
                                            ." FROM sl_species S,sl_cv_sources C"
                                            ." WHERE S._key=C.fk_sl_species AND C.fk_sl_species <> 0" )) ) {
            while( $ra = $this->kfdb->CursorFetch( $dbc ) ) {
                if( $this->oL->GetLang() == 'EN' ) {
                    $sSp = @$ra['name_en'] ? $ra['name_en'] : @$ra['name_fr'];
                } else {
                    $sSp = @$ra['name_fr'] ? $ra['name_fr'] : @$ra['name_en'];
                }
                if( $sSp ) {
                    $raSp[$sSp] = $ra['k'];
                }
            }
        }
        ksort($raSp);
        return( $raSp );
    }

    function showVarieties( $kSpecies, $sRegion, $bOrganic )
    {
//$this->kfdb->SetDebug(2);
        $s = "";

//factor this
        switch( $sRegion ) {
            case 'bc': $sCondRegion = " AND S.prov in ('BC','B.C.')"; break;
            case 'pr': $sCondRegion = " AND S.prov in ('AB','SK','MB')"; break;
            case 'on': $sCondRegion = " AND S.prov in ('ON','Ontario')"; break;
            case 'qc': $sCondRegion = " AND S.prov in ('QC','Quebec')"; break;
            case 'at': $sCondRegion = " AND S.prov in ('NB','NS','PE','NF')"; break;
            default:   $sCondRegion = "";
        }
        $sCondOrganic = $bOrganic ? " AND C.bOrganic" : "";




        $raCV = array();
        $raCV1 = $this->kfdb->QueryRowsRA( "SELECT C.ocv FROM sl_cv_sources C,sl_sources S WHERE C.fk_sl_sources=S._key"
                                         ." AND C.fk_sl_species='$kSpecies' AND S._key >=3 "
                                         .$sCondRegion
                                         .$sCondOrganic
                                         ." ORDER BY 1" );
        foreach( $raCV1 as $ra ) {
            $raCV[] = $ra[0];
        }
        $raCV = array_unique( $raCV );

/*
        // simplify when all combined into sl_pcv
        $raVars = array();
        $raVars1 = $this->kfdb->QueryRowsRA( "SELECT ocv FROM sl_cv_sources WHERE fk_sl_species='$kSpecies' AND fk_sl_sources >= 3 "
                                            ."UNION "
                                            ."SELECT variety FROM sed_curr_seeds WHERE fk_sl_species='$kSpecies' "
                                            ."UNION "
                                            ."SELECT P.name FROM sl_pcv P,sl_accession A,sl_species S "
                                                ."WHERE P._key=A.fk_sl_pcv AND P.psp=S.name_en AND S._key='$kSpecies'" );
        foreach( $raVars1 as $racv ) {
            $raVars[] = ucwords(strtolower($racv[0]) );
        }
        $raVars = array_unique( $raVars );
        sort( $raVars );
*/
/*
           $raVarList = $this->pagedVarieties( $kSpecies );

            $s .= "<h1>";
            foreach( $raVarList as $ra ) {
                $s .= "<A href='".$this->linkToMe( array( 'pSpec'=>$this->pSpec, 'pVarOff'=>$ra[0],'pVarLim'=>$ra[1]))."'>"
                .($ra[2]==$ra[3] ? $ra[2] : "{$ra[2]}-{$ra[3]}")
                ."</A>"
                .SEEDStd_StrNBSP("",5);
            }
            $s .= "</h1>";
*/


        if( count($raCV) ) {
            foreach( $raCV as $cv ) {
                $s .= "<DIV class='sod_srcui_cvlist'><A href='"
                     .$this->linktoMe( array('sod_k'=>'Com','sod_s'=>$kSpecies,'sod_r'=>$sRegion,'sod_o'=>$bOrganic,'sod_pcv'=>$cv) )
                     ."'>$cv</A></DIV>";
            }
        }
/*
            if( !$pVarLim ) {
                $pVarOff = @$raVarList[0][0];
                $pVarLim = @$raVarList[0][1];
            }
            if( ($dbc = $this->kfdb->CursorOpen("SELECT name,_key FROM sl_pcv WHERE _status=0 AND psp='{$this->pSpec}' ORDER BY 1 LIMIT $pVarOff,$pVarLim" )) ) {
                while( $ra = $this->kfdb->CursorFetch( $dbc ) ) {
                    $s .= "<DIV><A href='".$this->linktoMe( array('pSpec'=>$this->pSpec, 'pVarOff'=>$pVarOff,'pVarLim'=>$pVarLim,'pVarK'=>$ra[1]) )."'>"
                    .ucwords(strtolower($ra[0]))."</A></DIV>";
                }
                $this->kfdb->CursorClose( $dbc );
            }
*/

        return( $s );
    }

    function showSuppliers( $kSpecies, $sRegion, $bOrganic )
    {
        $s = "";

        $sSp = $this->getSpeciesName( $kSpecies );

//factor this
        switch( $sRegion ) {
            case 'bc': $sCondRegion = " AND S.prov in ('BC','B.C.')"; break;
            case 'pr': $sCondRegion = " AND S.prov in ('AB','SK','MB')"; break;
            case 'on': $sCondRegion = " AND S.prov in ('ON','Ontario')"; break;
            case 'qc': $sCondRegion = " AND S.prov in ('QC','Quebec')"; break;
            case 'at': $sCondRegion = " AND S.prov in ('NB','NS','PE','NF')"; break;
            default:   $sCondRegion = "";
        }

        $sCondOrganic = $bOrganic ? " AND C.bOrganic" : "";



    // pass sl_pcv._key when all names are normalized
        if( ($pVar = SEEDSafeGPC_GetStrPlain('sod_pcv')) ) {
            $raPCV = $this->kfdb->QueryRA( "SELECT * FROM sl_pcv WHERE name='".addslashes($pVar)."'" ); //_key='$pVarK'" );
            $kCV = $raPCV['_key'];
            $bSL = $kCV && $this->kfdb->Query1( "SELECT 1 FROM sl_accession WHERE fk_sl_pcv='$kCV'" );

            $bSED = $this->kfdb->Query1( "SELECT 1 FROM sed_curr_seeds WHERE fk_sl_species='$kSpecies' AND variety='".addslashes(strtoupper($pVar))."'" );

            $sCSCI = "";
            $raComm = $this->kfdb->QueryRowsRA(
                    "SELECT S.name_en as name,S.web as web,S.city as city,S.prov as prov FROM sl_cv_sources C,sl_sources S WHERE C.fk_sl_sources=S._key"
                   ." AND C.fk_sl_species='$kSpecies' AND C.ocv='".addslashes($pVar)."' AND S._key >=3 "
                   .$sCondRegion
                   .$sCondOrganic
                   ." ORDER BY 1" );
            foreach( $raComm as $ra ) {
                $sCSCI .= SEEDCore_ArrayExpand( $ra, "<div>"
                                                   .($ra['web'] ? "<a href='http://[[web]]' target='_blank'>[[name]]</A>" : "[[name]]")
                                                   ." ([[city]] [[prov]]) </div>" );
            }

/*
            if( ($dbc = $this->kfdb->CursorOpen( "SELECT S.company_name as company_name, C.web as web  FROM csci_seeds S, csci_company C "
                                                ."WHERE C.name_en=S.company_name AND C._status=0 AND "
                                                ."S.fk_sl_species='$kSpecies' AND S.icv='".addslashes($pVar)."' $sCondRegion $sCondOrganic" ))  ) {
                while( $ra = $this->kfdb->CursorFetch( $dbc ) ) {
                    $sCompanyName = $ra['company_name'];
                    $raCompany = $this->kfdb->QueryRA( "SELECT * FROM csci_company WHERE name_en='".addslashes($sCompanyName)."'" );
                    if( @$raCompany['web'] ) {
                        $sCSCI .= "<DIV><A href='http://{$raCompany['web']}' target='_blank'>{$ra['company_name']}</A></DIV>";
                    } else {
                        $sCSCI .= "<DIV>{$ra['company_name']}</DIV>";
                    }
                }
            }
*/

            $s .= "<DIV style='border:1px solid #888;padding:0 2em 20px 2em;margin:1em;'>";
            if( $this->oL->GetLang() == 'EN' ) {
                $s .= "<h3>".$this->oL->S("Suppliers of _seed_", array($pVar) )." $sSp</h3>";
            } else {
                $s .= "<h3>Fournisseurs de semences ".$this->frenchDeSpecies($pVar)."</h3>";
            }
            $s .= ($bSL ? "<DIV><A href='http://www.seeds.ca/sl' target='_blank'>Seeds of Diversity's Seed Library Collection</A></DIV>" : "")
                 .($bSED ? "<DIV><A href='http://www.seeds.ca' target='_blank'>Seeds of Diversity's Member Seed Directory</A></DIV>" : "")
                 .$sCSCI
                 ."</DIV>";
        }
        return( $s );
    }

    function drilldownold()
    {
        $pVarOff = SEEDSafeGPC_GetInt( 'pVarOff' );
        $pVarLim = SEEDSafeGPC_GetInt( 'pVarLim' );
        $pVarK   = SEEDSafeGPC_GetInt( 'pVarK' );


        if( !$this->pSpec ) {
            if( ($dbc = $this->kfdb->CursorOpen( "SELECT name_en FROM sl_species WHERE _status=0 AND category IN ('VEG','FRUIT','GRAIN') ORDER BY 1" )) ) {
                while( $ra = $this->kfdb->CursorFetch( $dbc ) ) {
                    $s .= "<DIV><A href='".$this->linktoMe( array('pSpec'=>$ra[0]) )."'>{$ra[0]}</A></DIV>";
                }
                $this->kfdb->CursorClose( $dbc );
            }
        } else {
            $s = "<h3>Varieties of {$this->pSpec}</h3>";

            $raVarList = $this->pagedVarieties( $this->pSpec );

            $s .= "<h1>";
            foreach( $raVarList as $ra ) {
                $s .= "<A href='".$this->linkToMe( array( 'pSpec'=>$this->pSpec, 'pVarOff'=>$ra[0],'pVarLim'=>$ra[1]))."'>"
                .($ra[2]==$ra[3] ? $ra[2] : "{$ra[2]}-{$ra[3]}")
                ."</A>"
                .SEEDStd_StrNBSP("",5);
            }
            $s .= "</h1>";

            if( $pVarK ) {
                $raPCV = $this->kfdb->QueryRA( "SELECT * FROM sl_pcv WHERE _key='$pVarK'" );

                $bSL = $this->kfdb->Query1( "SELECT 1 FROM sl_accession WHERE fk_sl_pcv='$pVarK'" );

                $sCSCI = "";
                if( ($dbc = $this->kfdb->CursorOpen( "SELECT * FROM csci_seeds WHERE _status=0 AND psp='{$raPCV['psp']}' AND icv='{$raPCV['name']}'" ))  ) {
                    while( $ra = $this->kfdb->CursorFetch( $dbc ) ) {
                        $sCompanyName = $ra['company_name'];
                        $raCompany = $this->kfdb->QueryRA( "SELECT * FROM csci_company WHERE name_en='$sCompanyName'" );
                        if( @$raCompany['web'] ) {
                            $sCSCI .= "<DIV><A href='http://{$raCompany['web']}' target='_blank'>{$ra['company_name']}</A></DIV>";
                        } else {
                            $sCSCI .= "<DIV>{$ra['company_name']}</DIV>";
                        }
                    }
                }

                $s .= "<DIV style='float:right; border:1px solid #888;padding:2em;margin:1em;'>"
                ."<h2>".$raPCV['name']."</h2>"
                .($bSL ? "<DIV><A href='http://www.seeds.ca/sl' target='_blank'>Seeds of Diversity</A></DIV>" : "")
                .$sCSCI
                ."</DIV>";
            }


            if( !$pVarLim ) {
                $pVarOff = @$raVarList[0][0];
                $pVarLim = @$raVarList[0][1];
            }
            if( ($dbc = $this->kfdb->CursorOpen("SELECT name,_key FROM sl_pcv WHERE _status=0 AND psp='{$this->pSpec}' ORDER BY 1 LIMIT $pVarOff,$pVarLim" )) ) {
                while( $ra = $this->kfdb->CursorFetch( $dbc ) ) {
                    $s .= "<DIV><A href='".$this->linktoMe( array('pSpec'=>$this->pSpec, 'pVarOff'=>$pVarOff,'pVarLim'=>$pVarLim,'pVarK'=>$ra[1]) )."'>"
                    .ucwords(strtolower($ra[0]))."</A></DIV>";
                }
                $this->kfdb->CursorClose( $dbc );
            }
        }

        return( $s );
    }

    function pagedVarieties( $pSp )
    {
        // n = number of items
        // m = number of items per group
        // g = number of groups

        $ra = array();

        $mThresh = 30; // preferred max number of items per group

        $n = $this->kfdb->Query1( "SELECT count(*) FROM sl_pcv WHERE psp='$pSp'" );

        $g = $n / $mThresh;
        if( $g < 1 )   $g = 1;
        if( $g > 26 )  $g = 26;
        $m = intval($n / $g);

        // So now we're dividing the list into $g groups of $m items.
        // Name each group by the letter that corresponds to its first item.
        $raTmp = array();
        for( $i = 0; $i < $g; ++$i ) { // offset is origin-0
            $cv = $this->kfdb->Query1( "SELECT name FROM sl_pcv WHERE _status=0 AND psp='$pSp' ORDER BY 1 LIMIT ".($i*$m).", 1" );
            $raTmp[$i] = strtoupper( substr($cv,0,1) );
        }
        for( $i = 0; $i < $g; ++$i ) { // offset is origin-0
            if( $i < $g-1 ) {
                $ra[] = array( $i*$m, $m, $raTmp[$i], $raTmp[$i+1] );
            } else {
                $ra[] = array( $i*$m, $m, $raTmp[$i], 'Z' );
            }
        }

        return( $ra );
    }

    function linkToMe( $ra )
    {
        return( $this->sLinkToMe.SEEDStd_ParmsRA2URL( $ra ) );
        //return( $_SERVER['PHP_SELF']."?".SEEDStd_ParmsRA2URL( $ra ) );
    }

}


function SLSources_Setup( $oSetup, &$sReport, $bCreate = false )
/***************************************************************
 Test whether the tables exist.
 bCreate: create the tables and insert initial data if they don't exist.

 Return true if exists (or create is successful if bCreate); return a text report in sReport

 N.B. $oSetup is a SEEDSetup.  This file doesn't include SEEDSetup.php because the setup code is very rarely used.
 Instead, the code that calls this function knows about SEEDSetup.
 */
{
    return( $oSetup->SetupTable( "sl_sources", SEEDS_DB_TABLE_SL_SOURCES, $bCreate, $sReport ) &&
    $oSetup->SetupTable( "sl_cv_sources", SEEDS_DB_TABLE_SL_CV_SOURCES, $bCreate, $sReport ) );
}

?>
