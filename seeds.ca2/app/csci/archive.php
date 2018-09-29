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
$raSp = $o->GetSpecies();

$raYears = $oApp->kfdb->QueryRA( "SELECT year FROM seeds.sl_cv_sources_archive WHERE fk_sl_sources>='3' AND _status='0' GROUP BY 1 ORDER BY 1" );

$s .= "<table>";
foreach( $raSp as $ra ) {
    $name = $ra['kSp'] ? "{$ra['name']} ({$ra['kSp']})" : "<span style='color:orange'>{$ra['name']}</span>";
    $s .= "<tr><td>$name</td>";
    $s .= "<td>".(count($ra['raYears']) ? SEEDCore_MakeRangeStr($ra['raYears']) : "")."</td>";
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
                                       array('sGroupCols'=>'S_name_en,S__key,year','iStatus'=>-1 ) );
        // Where fk_sl_species is zero
        $raSp2 = $this->oSrc->GetList( "SRCCVA", "fk_sl_sources>='3' AND osp <> ''",
                                       array('sGroupCols'=>'osp,year') );

        foreach( $raSp1 as $ra ) {
            // S_name_en and S__key should be correlated 1:1, but several rows can different years.
            if( !isset( $raSpecies[$ra['S_name_en']] ) ) {
                $raSpecies[$ra['S_name_en']] = array('kSp'=>$ra['S__key'],'name'=>$ra['S_name_en'],'raYears'=>array());
            }
            $raSpecies[$ra['S_name_en']]['raYears'][$ra['year']] = true;
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

?>