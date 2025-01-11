<?php

// we want non-filtered search to return TopChoices, but we also want a non-filtered way to get a complete spreadsheet

include_once("../site.php");
include_once(STDINC."SEEDTemplate.php");
include_once(SEEDLIB."q/QServerSources.php");
include_once(SEEDLIB."sl/sldb.php");

list($kfdb, $sess, $lang) = SiteStartSessionAccountNoUI();
$oApp = SEEDConfig_NewAppConsole_LoginNotRequired([]);

$oSrc = new QServerSourceCV( $oApp, array() );

// Make the species <select>
$rQ = $oSrc->Cmd( 'srcSpecies', ['bAllComp'=>true, 'outFmt'=>'NameKey', 'opt_spMap'=>'ESF'] );
$spOpts = "";
if( $rQ['bOk'] ) {
    foreach( $rQ['raOut'] as $sSp => $kSp ) {
// this should process spapp keys too
        if( substr($kSp,0,3) == 'spk' ) {
            $spOpts .= "<option value='".substr($kSp,3)."'>$sSp</option>";
        }
    }
}

/* Output a js object containing sl_sources details. Cultivar data contains keys of sl_sources so this is the lookup table.
 */
// oldQ::srcSources joins on SRCCV so it can get sources of particular species, etc, which is maybe not what we really want here
$oSLDBSrc = new SLDBSources($oApp);
$raSLSources = [];
foreach( $oSLDBSrc->GetListKeyed('SRC', '_key', "_key>=3", ['sSortCol'=>'name_en']) as $k => $ra ) {
    $raSLSources[$k] = ['name_en'=>$ra['name_en'], 'name_fr'=>$ra['name_fr'], 'web'=>$ra['web']];
}

$scriptAtBottom =
    /* Object contains SRC data
     */
    "<script>var oSLSources = ".json_encode(SEEDCore_utf8_encode($raSLSources))."</script>"

    /* Boot up the google charts
     */
   ."<script type='text/javascript' src='https://www.gstatic.com/charts/loader.js'></script>
     <script type='text/javascript'>
         // Load the Visualization API and the corechart package.
         google.charts.load('current', {'packages':['corechart']});
         // google.setOnLoadCallback(drawChart);        instead call drawChart() when we want to
     </script>";


$raTmplVars = ['lang'=>$lang,
               'yCurrent'=>date('Y'),
               'spOptions'=>$spOpts,
               'scriptAtBottom'=>$scriptAtBottom,
               'mode'=>SEEDInput_Smart('mode',['finder','research']),
               'nTotalSources' => $oSLDBSrc->GetCount('SRCCV','fk_sl_sources>=3',['sGroupCol'=>'fk_sl_sources']),
               'nTotalCultivars' => $oSLDBSrc->GetCount('SRCCV','fk_sl_sources>=3',['sGroupCol'=>'fk_sl_species,ocv']),
];

$o = new SEEDTemplate_Generator( array( 'fTemplates' => array(SITEROOT."seedfinder/seedfinder.html"),
                                        'SEEDTagParms' => array(),
                                        'vars' => $raTmplVars
) );
$oTmpl = $o->MakeSEEDTemplate();

$s = $oTmpl->ExpandTmpl( "main" );

echo $s;
