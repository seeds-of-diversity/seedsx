<?php

/* CSCI Archive tool
 *
 * Copyright (c) 2018 Seeds of Diversity Canada
 */

if( !defined("SITEROOT") )  define("SITEROOT", "../../");
include_once( SITEROOT."site.php" );
include_once( SEEDCORE."console/console02.php" );
include_once( SEEDLIB."sl/sldb.php" );


$oApp = new SEEDAppConsole(
                array_merge( $SEEDKFDB1,
                             array( 'sessPermsRequired' => array(),
                                    'logdir' => SITE_LOG_ROOT )
                           )
);

$oApp->kfdb->SetDebug(1);


$s = "";


/*
$oSrc = new SLDBSources( $oApp, array( 'logdir'=>SITE_LOG_ROOT ) );
$raCompanies = $oSrc->GetList( "SRC",     "", array("iStatus"=>-1, "sSortCol"=>'SRC.name_en' ) );
$raSpecies   = $oSrc->GetList( "SRCCVxS", "SRCCV.fk_sl_sources>='3'", array('sGroupCols'=>'S_name_en,S__key',"sSortCol"=>'S.name_en' ) );

$s .= "<form method='post'>"
     ."<div class='container' style='border:1px solid #aaa;border-radius:5px;'>"
     ."<div class='row' style='padding:10px 0px'>"
         ."<div class='col-md-4'>"
             ."<div><select name='company'>"
                 ."<option value='0'>-- All companies --</option>"
                 .SEEDCore_ArrayExpandRows( $raCompanies, "<option value='[[_key]]'>[[name_en]]</option>" )
                 ."</select>"
             ."</div>"
             ."<div style='font-size:8pt'>Choose one or both of these before searching</div>"
             ."<div><select name='species'>"
                 ."<option value='0'>-- All species --</option>"
                 .SEEDCore_ArrayExpandRows( $raSpecies, "<option value='[[S__key]]'>[[S_name_en]]</option>" )
                 ."</select>"
             ."</div>"
         ."</div>"
         ."<div class='col-md-6'>"
             ."YEARS"
         ."</div>"
         ."<div class='col-md-2'>"
             ."<input type='submit' value='Search'/>"
         ."</div>"
     ."</div></div>"
     ."</form>";


$kCompany = SEEDInput_GetInt( 'company' );
$kSpecies = SEEDInput_GetInt( 'species' );

if( !$kCompany && !$kSpecies ) {
    $s = "<p>Choose a company and/or a species before searching. "
        ."Otherwise you would get the entire seed finder archive, which is too many results all at once.</p>";
    goto done;
}

$cond = array();
if( $kCompany ) {
    $cond[] = "SRCCV.fk_sl_sources='$kCompany'";
}
if( $kSpecies ) {
    $cond[] = "SRCCV.fk_sl_sp"
}
*/

//$oApp->kfdb->SetDebug(2);
$oSrc = new SLDBSources( $oApp, array( 'logdir'=>SITE_LOG_ROOT ) );
// where fk_sl_species is set -- iStatus=-1 because some sl_sources will be "deleted"
$raSpecies1 = $oSrc->GetList( "SRCCVAxSRC_S", "SRC._key>='3' AND S._key IS NOT NULL",
                              array('sGroupCols'=>'S_name_en,year','iStatus'=>-1 ) );
// where fk_sl_species is zero
$raSpecies2 = $oSrc->GetList( "SRCCVA", "fk_sl_sources>='3' AND osp <> ''",
                              array('sGroupCols'=>'osp') );
$raSp = array();
foreach( $raSpecies1 as $ra ) {
    if( !isset( $raSp[$ra['S_name_en']] ) ) {
        $raSp[$ra['S_name_en']] = array('kSL'=>true,'name'=>$ra['S_name_en'],'raYears'=>array());
    }
    $raSp[$ra['S_name_en']]['raYears'][$ra['year']] = true;
}
foreach( $raSpecies2 as $ra ) {
    if( !isset( $raSp[$ra['osp']] ) ) {
        $raSp[$ra['osp']] = array('kSL'=>false,'name'=>"<span style='color:orange'>{$ra['osp']}</span>",'raYears'=>array());
    }
    $raSp[$ra['osp']]['raYears'][$ra['year']] = true;
}
ksort( $raSp );

foreach( $raSp as $ra ) {
    $raYears = array_keys($ra['raYears']);
    $s .= "<p>".$ra['name']." ".(count($raYears) ? SEEDCore_MakeRangeStr($raYears) : "")."</p>";
}


done:

echo Console02Static::HTMLPage( $s, "", 'EN', array('sCharset'=>'cp1252') );

/*
$o = new SLDBCollection( $oApp, array( 'logdir'=>SITE_LOG_ROOT ) );
$ra = $o->GetList( 'IxGxAxPxS', "I._key BETWEEN 1000 and 1010" );
echo SEEDCore_ArrayExpandRows( $ra, "<p>[[inv_number]] [[P_name]]</p>" );

echo "<hr/";

$ra = $o->GetList( 'IxA_P', "I._key BETWEEN 1000 and 1010" );
echo SEEDCore_ArrayExpandRows( $ra, "<p>[[_key]] [[P_name]]</p>" );

echo "<hr/";

$o = new SLDBSources( $oApp, array( 'logdir'=>SITE_LOG_ROOT ) );
$ra = $o->GetList( 'SRCCVxSRC_P', "SRCCV._key BETWEEN 14750 and 14760" );
echo SEEDCore_ArrayExpandRows( $ra, "<p>[[_key]] [[osp]] [[ocv]] [[SRC_name_en]]</p>" );
*/

?>