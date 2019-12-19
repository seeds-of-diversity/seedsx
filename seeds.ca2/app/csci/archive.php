<?php

/* CSCI Archive tool
 *
 * Copyright (c) 2018-2019 Seeds of Diversity Canada
 */

if( !defined("SITEROOT") )  define("SITEROOT", "../../");
include_once( SITEROOT."site.php" );
include_once( SEEDCORE."SEEDXLSX.php" );
include_once( SEEDCORE."console/console02.php" );
include_once( SEEDLIB."sl/sldb.php" );


$oApp = new SEEDAppConsole( $config_KFDB['seeds1']
                            + array( 'sessPermsRequired' => array(),
                                     'logdir' => SITE_LOG_ROOT )
);

$oApp->kfdb->SetDebug(1);


$s = "";


/*
$oSrc = new SLDBSources( $oApp );
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


$o = new QSRCCVA( $oApp );


if( SEEDInput_Str( 'cmd' ) == 'downloadsummary-csv' ) {
    SLSrcCVArchiveSummaryCsv( $oApp );

    exit;
}
if( SEEDInput_Str( 'cmd' ) == 'downloadsummary-xls' ) {
    SLSrcCVArchiveSummaryXls( $oApp );

    exit;
}


$s .= "<div><form action='".$oApp->PathToSelf()."' method='post'>"
     ."<input type='submit' value='Download Summary CSV'/>"
     ."<input type='hidden' name='cmd' value='downloadsummary-csv'/>"
     ."</form></div>";
$s .= "<div><form action='".$oApp->PathToSelf()."' method='post'>"
     ."<input type='submit' value='Download Summary XLS'/>"
     ."<input type='hidden' name='cmd' value='downloadsummary-xls'/>"
     ."</form></div>";


$raSp = $o->GetSpecies();

$raYears = $oApp->kfdb->QueryRowsRA1( "SELECT year FROM seeds.sl_cv_sources_archive WHERE fk_sl_sources>='3' AND _status='0' GROUP BY 1 ORDER BY 1" );

$s .= "
<style>
.row0 { background-color:white; }
.row1 { background-color:#eee; }
td {border-right:1px solid #777;padding:2px 8px;font-size:9pt;}
</style>
";

$s .= "<table class='srca_table'><tr><th>Primary<br/>Species<br/></th><th>Actual names<br/>matched</th><th>Years</th>"
     .SEEDCore_ArrayExpandSeries( $raYears, "<th>[[]]</th>" )
     ."</tr>";
$r = 1;
foreach( $raSp as $ra ) {
    $r = intval(!$r);

    $name = $ra['kSp'] ? "{$ra['name']} ({$ra['kSp']})" : "<span style='color:orange'>{$ra['name']}</span>";
    $s .= "<tr class='row$r'><td style='font-size:11pt' valign='top'>$name</td>";
    $sSpAlt = "";
    if( $ra['kSp'] ) {
        $raOsp = $oApp->kfdb->QueryRowsRA( "SELECT osp,year FROM sl_cv_sources_archive WHERE fk_sl_species='{$ra['kSp']}' GROUP BY osp,year" );
        foreach( $raOsp as $r1 ) {
            if( $r1['osp'] !== $ra['name'] )  $sSpAlt .= "{$r1['osp']} {$r1['year']}<br/>";
        }
    }
    $s .= "<td valign='top' style='font-size:9pt'>$sSpAlt</td>";
    $s .= "<td valign='top'>".(count($ra['raYears']) ? SEEDCore_MakeRangeStr($ra['raYears']) : "")."</td>";
    foreach( $raYears as $y ) {
        $sCond = $ra['kSp'] ? "fk_sl_species='{$ra['kSp']}'" : "osp='{$ra['name']}'";
        $nSrc = $oApp->kfdb->Query1( "SELECT count(distinct fk_sl_sources) FROM seeds.sl_cv_sources_archive WHERE $sCond AND fk_sl_sources>='3' AND year='$y'" );
        $nCV  = $oApp->kfdb->Query1( "SELECT count(distinct ocv) FROM seeds.sl_cv_sources_archive WHERE $sCond AND fk_sl_sources>='3' AND year='$y'" );
        $s .= "<td valign='top'>";
        if( $nSrc )  $s .= "$nSrc companies<br/>";
        if( $nCV )   $s .= "$nCV varieties<br/>";
        $s .= "</td>";
    }
    $s .= "</tr>";
}
$s .= "</table>";

done:

echo Console02Static::HTMLPage( $s, "", 'EN', array('sCharset'=>'cp1252') );

class QSRCCVA
{
    private $oApp;

    function __construct( SEEDAppSession $oApp )
    {
        $this->oApp = $oApp;
        $this->oSrc = new SLDBSources( $oApp );
    }

    function GetSpecies( $raParms = array() )
    {
        // Until all of the archive's species names are known in Rosetta, some SRCCVA rows will have fk_sl_species and some won't.
        // This unifies those sets of names.

        $raSpecies = array();

        // Get names where fk_sl_species is set. iStatus=-1 because some sl_sources will be "deleted"
        $raSp1 = $this->oSrc->GetList( "SRCCVAxSRC_S", "SRC._key>='3' AND S._key IS NOT NULL",
                                       array('sGroupCols'=>'S_iname_en,S__key,year','iStatus'=>-1 ) );
        // Where fk_sl_species is zero
        $raSp2 = $this->oSrc->GetList( "SRCCVA", "fk_sl_sources>='3' AND osp <> '' AND fk_sl_species='0'",
                                       array('sGroupCols'=>'osp,year') );

        foreach( $raSp1 as $ra ) {
            // S_name_en and S__key should be correlated 1:1, but several rows can be from different years.
            if( !isset( $raSpecies[$ra['S_iname_en']] ) ) {
                $raSpecies[$ra['S_iname_en']] = array('kSp'=>$ra['S__key'],'name'=>$ra['S_iname_en'],'raYears'=>array());
            }
            $raSpecies[$ra['S_iname_en']]['raYears'][$ra['year']] = true;
        }
        foreach( $raSp2 as $ra ) {
            // Several rows can be returned with the same osp but different years.
            if( !isset( $raSpecies[$ra['osp']] ) ) {
                $raSpecies[$ra['osp']] = array('kSp'=>0,'name'=>$ra['osp'],'raYears'=>array());
            }
            $raSpecies[$ra['osp']]['raYears'][$ra['year']] = true;
        }
        // Sort the array by name, and sort the years within each species (years have been stored as keys to simplify uniqueness but change them to values).
        ksort( $raSpecies );    // sort by name
        foreach( $raSpecies as $k => $ra ) {
            $raSpecies[$k]['raYears'] = array_keys($ra['raYears']);
            sort($raSpecies[$k]['raYears']);
        }

        return( $raSpecies );
    }
}

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

function SLSrcCVArchiveSummaryCsv( SEEDAppConsole $oApp )
{
    list($raSrc,$raSummary) = getArchiveSummary( $oApp );

    header( "Content-Type:text/plain; charset=cp1252" );
    header( "Content-Disposition: attachment;filename=\"seed-archive.csv\"" );

// todo: use db query as above to get $raYears

    echo "species\tcultivar\tspecies_key\tcompany\tcompany_key\t2008\t2010\t2012\t2014\t2016\t2017\t2018\t2019\n";
    foreach( $raSummary as $k => $ra ) {
        list($sp,$cv,$kSp,$kSrc) = explode( '|', $k, 4 );
        echo "$sp\t$cv\t$kSp\t{$raSrc[$kSrc]}\t$kSrc\t".@$ra['c2008']."\t".@$ra['c2010']."\t".@$ra['c2012']."\t".@$ra['c2014']."\t".@$ra['c2016']."\t".@$ra['c2017']."\t".@$ra['c2018']."\t".@$ra['c2019']."\n";
    }
    return;
}

function SLSrcCVArchiveSummaryXls( SEEDAppConsole $oApp )
{
    list($raSrc,$raSummary) = getArchiveSummary( $oApp );

    $oXls = new SEEDXlsWrite( array('filename'=>'seed-archive.xlsx') );
    $oXls->WriteHeader( 0, array( 'species', 'cultivar', 'species_key', 'company', 'company_key',
                                  '2008', '2010', '2012', '2014', '2016', '2017', '2018', '2019' ) );;

    $row = 2;
    foreach( $raSummary as $k => $ra ) {
        list($sp,$cv,$kSp,$kSrc) = explode( '|', $k, 4 );
        $oXls->WriteRow( 0, $row++, array( $sp, $cv, $kSp, $raSrc[$kSrc], $kSrc,
                                           @$ra['c2008'], @$ra['c2010'], @$ra['c2012'], @$ra['c2014'], @$ra['c2016'], @$ra['c2017'], @$ra['c2018'], @$ra['c2019'] ) );
//if( $row>100) break;
    }
    $oXls->OutputSpreadsheet();

    return;
}

function getArchiveSummary( SEEDAppConsole $oApp )
{
    $raSrc = array();
    $raSummary = array();

//$oApp->kfdb->SetDebug(2);
    $oSrc = new SLDBSources( $oApp );

    // Make a map of kSrc=>sourcename
    $kfr = $oSrc->GetKFRC( "SRC", "", array('iStatus'=>-1) );
    while( $kfr->CursorFetch() ) {
        $raSrc[$kfr->Key()] = $kfr->Value('name_en');
    }
    $kfr = null;

    // Make a map of kSp=>speciesname
    $raSp = array();
    $kfr = $oSrc->GetKFRC( 'S', "", array() );
    while( $kfr->CursorFetch() ) {
        $raSp[$kfr->Key()] = $kfr->Value('iname_en');
    }
    $kfr = null;

    // Assemble summary kSp|ocv|kSrc => array of years when that variety was available from that company
//    $n = 0;
    if( ($kfr = $oSrc->GetKFRC( "SRCCVA", "fk_sl_sources>='3' AND fk_sl_species<>'0'")) ) { //, array('iStatus'=>-1) )) ) { needed for join with SRC not used
        while( $kfr->CursorFetch() ) {
            //$raSummary[$raSp[$kfr->Value('fk_sl_species')].'|'.$kfr->Value('ocv')]['c'.$kfr->Value('year')] = $kfr->Value('fk_sl_sources');//$kfr->Value('SRC_name_en');
            $raSummary[$raSp[$kfr->Value('fk_sl_species')].'|'.$kfr->Value('ocv').'|'.$kfr->Value('fk_sl_species').'|'.$kfr->Value('fk_sl_sources')]['c'.$kfr->Value('year')] = true;
//            if( ++$n > 100 ) break;
        }
    }
    ksort($raSummary);

    return( array($raSrc,$raSummary) );
}

?>
