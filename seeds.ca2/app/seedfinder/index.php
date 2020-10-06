<?php

// Where is the geographically closest seed company to me?
// How do I find all the supplies of a certain species or variety? And which are closest?
// Show all species
// Show the closest supplier - postal code or map


/* Seed Finder
 *
 * Copyright (c) 2012-2016 Seeds of Diversity Canada
 *
 */

if( !defined("SITEROOT") )  define("SITEROOT", "../../");
include_once( SITEROOT."site.php" );
include_once( SEEDCOMMON."siteStart.php" );
include_once( SEEDCOMMON."sl/sl_db.php" );
include_once( "seedfinder.php" );
include_once( SEEDCOMMON."sl/q/_QServerSourceCV.php" );
include_once( SEEDCOMMON."sl/q/_QServerRosetta.php" );

list($kfdb, $sess, $lang) = SiteStartSessionAccountNoUI();

$raConsoleParms = array(
    'CONSOLE_NAME' => "SeedFinder",
    'bBootstrap' => true,
);
$oC = new Console01( $kfdb, $sess, $raConsoleParms );

$oSF = new SeedFinder( $oC, $kfdb, $sess, $lang );

$sBody = $sHead = "";

$sHead .= "
    <script src='".W_ROOT."std/js/SEEDStd.js'></script>
    <style>
    .seedfinder-controls     { background-color:#ccc; margin: 5px 5px 0px 5px; padding:15px; }
    .seedfinder-controls div { text-align:center; font-weight:bold; font-size:10pt }
    .seedfinder-body         { width:100%; margin-top:5px }

    .seedfinder-itemheader   { text-align:center; font-size:16pt }
    .seedfinder-itemheader-organic
                             { margin:0px 10px 5px 10px; padding:3px auto; color:green; background-color:#e1f3d8; border-radius:10px; text-align:center;}
    .seedfinder-item         { width:100%; border:1px solid #aaa; border-radius:5px; margin:0px 0px 2px 10px; padding:0px 3px; }
    .seedfinder-item0        { background-color: #ffd; }
    .seedfinder-item1        { background-color: #f8f8f8; }
    .seedfinder-item-sp      { font-size:9pt; }
    .seedfinder-item-cv      { font-size:10pt; font-weight:bold; margin:3px 12px; }

    .seedfinder-supplier     { margin-bottom: 5px; }
    </style>";


$oQ = new Qold( $kfdb, $sess, null, array() );     // oApp null for now

$oRosetta = new QServerRosetta( $kfdb );    // change arg to $oQ


// Species <select> options
$oSrc = new QServerSourceCV_Old( $oQ, array() );
$rQ = $oSrc->Cmd( 'srcSpecies', array( 'bAllComp'=>true, 'outFmt'=>'NameKey', 'spMap'=>'ESF' ) );
$raSp = array_merge( array( "All seed types"=>0 ), $rQ['raOut'] );


$oForm = new SEEDFormSession( $sess, 'ESFCtrl', 'A' );
$oForm->Update();

//$sBody .= "<h2 style='margin-left:15px'>Seed Finder</h2>";

/* Draw Controls
 */
$sBody .=
    "<div class='seedfinder-controls'><div class='container'>"
       ."<form method='post'>"
       ."<div class='row'>"
           ."<div class='col-sm-3'>"
               ."Looking for<br/>"
               .$oForm->Select2( 'sp', $raSp, "", array( 'classes'=>'form-control') )
           ."</div>"
           ."<div class='col-sm-3'>"
               ."</br>"//."Search for<br/>"
               .$oForm->Text( 'srch', "", array( 'classes'=>'form-control', 'attrs'=>"placeholder='Search by Variety'"  ) )
           ."</div>"
           ."<div class='col-sm-3'>"
               ."From suppliers in<br/>"
               .$oForm->Select2( 'reg', $oSF->GetRegionOpts(), "", array( 'classes'=>'form-control') )
           ."</div>"
           ."<div class='col-sm-2'>"
               ."Only show organic<br/>"
               .MyToggle( 'organic', $oForm->Value('organic'), $oForm->Name('organic') )
           ."</div>"
           ."<div class='col-sm-1'>"
               ."<br/><button class='btn' onclick='submit();'>Find</button>"
           ."</div>"
       ."</div>"
       ."</form>"
   ."</div></div>";


/* Fetch the matches
 */
$sCVList = "";

$raCond = array();
$raCondKluge = array();
$raKFParms = array();
if( ($dbSrch = $oForm->ValueDB('srch')) ) {
    $raCond[] = "(P.name LIKE '%$dbSrch%' OR S.name_en LIKE '%$dbSrch%')";
    $raCondKluge[] = "(SRCCV.ocv LIKE '%$dbSrch%' OR S.name_en LIKE '%$dbSrch%')";
}


$sSpeciesName = "";
if( ($pSp = $oForm->Value('sp')) ) {
    // spkNNN is a sl_species._key
    // spappNNN is a sl_species_map._key
    if( substr($pSp,0,3) == 'spk' ) {
        if( ($kSp = intval(substr($pSp,3))) ) {
            $raCond[] = "S._key='$kSp'";
            $raCondKluge[] = "S._key='$kSp'";

            $raSp = $oRosetta->GetSpeciesDetails( $kSp );
            $sSpeciesName = $raSp['name_en'];
        }
    } else if( substr($pSp,0,5) == 'spapp' ) {
        if( ($kMap = intval(substr($pSp,5))) ) {
            $sSpeciesName = $kfdb->Query1( "SELECT appname_en FROM seeds_1.sl_species_map WHERE _status='0' AND _key='$kMap'" );
            $raR = $kfdb->QueryRowsRA( "SELECT fk_sl_species FROM seeds_1.sl_species_map WHERE _status='0' AND appname_en='".addslashes($sSpeciesName)."' AND ns='ESF'" );
            $raMap = array();
            foreach( $raR as $ra ) {
                $raMap[] = $ra[0];
            }

            $rng = SEEDCore_MakeRangeStrDB( $raMap, "S._key" );
            $raCond[] = $rng;
            $raCondKluge[] = $rng;
        }
    }


}
if( ($reg = $oForm->Value('reg')) ) {
    switch( $reg ) {
        case 'bc':  $raCond[] = "SRC.prov='BC'";                     $raCondKluge[] = "SRC.prov='BC'";                     break;
        case 'pr':  $raCond[] = "SRC.prov in ('AB','SK','MB')";      $raCondKluge[] = "SRC.prov in ('AB','SK','MB')";      break;
        case 'on':  $raCond[] = "SRC.prov='ON'";                     $raCondKluge[] = "SRC.prov='ON'";                     break;
        case 'qc':  $raCond[] = "SRC.prov='QC'";                     $raCondKluge[] = "SRC.prov='QC'";                     break;
        case 'at':  $raCond[] = "SRC.prov in ('NB','NS','PE','NL')"; $raCondKluge[] = "SRC.prov in ('NB','NS','PE','NL')"; break;
    }
}
if( ($bOrganic = intval($oForm->Value('organic'))) ) {
    $raCond[] = "SRCCV.bOrganic";
    $raCondKluge[] = "SRCCV.bOrganic";
}

/* Draw the Item List
 */
$sCVHeader = "";
$sMode = "";
if( $dbSrch || $reg ) {
    $sCVHeader = "<h4 class='seedfinder-itemheader'>Best Matches</h4>";
} else if( $sSpeciesName ) {
    // the name is only defined if a species was selected
    $sCVHeader = "<h4 class='seedfinder-itemheader'>All ".SEEDCore_HSC($sSpeciesName)." Varieties</h4>";
} else {
    $sCVHeader = "<h4 class='seedfinder-itemheader'>Popular Choices</h4>";
    $sMode = "TopChoices";
}
if( $bOrganic ) $sCVHeader .= "<div class='seedfinder-itemheader-organic'>Certified Organic!</div>";


//$kfdb->SetDebug(2);
$nItems = 0;
$raKlugeCollector = array();
if( ($kfr = $oSrc->GetCultivarsKFRC( implode(" AND ", $raCond), array("mode"=>$sMode) )) ) {
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

        $raKlugeCollector[$kfr->Value('S_name_en').' '.$kfr->Value('P_name')] = array(
                'S_name_en' => $kfr->Value('S_name_en'),
                'P_name'    => $kfr->Value('P_name'),
                'P__key'    => $kfr->Value('P__key'),
        );
    }

    if( !count($raCondKluge) )  $raCondKluge = array("1=1");

    // Kluge: for matches where fk_sl_pcv==0 and the species,name are not already in the list (shouldn't be!), add them to the list
    //        with P__key=SRCCV__key+10,000,000
    if( ($dbc = $kfdb->CursorOpen( "SELECT SRCCV._key AS kluge_key, S.name_en AS S_name_en, SRCCV.ocv AS ocv "
                                  ."FROM sl_cv_sources SRCCV, sl_sources SRC, sl_species S "
                                  ."WHERE SRCCV._status='0' AND SRC._status='0' AND S._status='0' AND "
                                        ."SRCCV.fk_sl_species=S._key AND SRCCV.fk_sl_sources=SRC._key AND "
                                        ."SRCCV.fk_sl_pcv='0' AND SRCCV.fk_sl_sources >= 3 AND "
                                        ."(".(implode(' AND ',$raCondKluge)).")" ) ) )
    {
        while( $ra = $kfdb->CursorFetch($dbc) ) {
            $raKlugeCollector[$ra['S_name_en'].' '.$ra['ocv']] = array(
                'S_name_en' => $ra['S_name_en'],
                'P_name'    => $ra['ocv'],
                'P__key'    => $ra['kluge_key'] + 10000000,
            );
        }
    }
    ksort($raKlugeCollector);

// do this above in the kfrcursor when the kluge is gone
    $nItems = 0;
    foreach( $raKlugeCollector as $ra ) {
        $sItem = SEEDStd_ArrayExpand( $ra,
            "<span class='seedfinder-item-sp'>&nbsp;[[S_name_en]]</span>"
           ."<div class='seedfinder-item-cv'>[[P_name]]</div>"
           ."<span style='font-size:8pt;font-family:serif;float:right;margin-top:-15px;display:none'>[[c]]"./*" source".($kfr->Value('c')==1?"":"s").*/"&nbsp;</span>"
        ,true );  // expand with entities

        $sCVList .= SEEDStd_ArrayExpand( $ra,
                    "<div class='seedfinder-item seedfinder-item".($nItems%2)."' "
                   ." onclick='showSuppliers([[P__key]],\"[[P_name]] [[S_name_en]]\");'>", true )    // expand with entities
                   .$sItem
                   ."</div>";
        $nItems++;
    }

}
if( !$nItems )  $sCVHeader = "<h4>No Matches Found</h4>";


$sBody .=
    "<div class='seedfinder-body'><div class='container-fluid'><div class='row'>"
       ."<div class='col-sm-3' style='border-rightXXX:1px solid #aaa; border-bottomXXX:1px solid #aaa'>"
           .$sCVHeader
           ."<div style='max-height:600px;overflow-y:auto;padding-right:15px;'>"
           .$sCVList
           ."</div>"
       ."</div>"

       ."<div class='col-sm-9'>"
           ."<div id='supplierWindow' "
                ."style='padding:5px 15px;margin:20px 20px 0px 0px;box-shadow:10px 10px 5px #aaa;border:1px solid#aaa;border-radius:10px'>"
               ."<div id='instructions'>"
                   ."<h3>Find your favourite local seed varieties!</h3>"
                   ."<ul>"
                   ."<li>Click on a variety in the list to see the Canadian seed companies that sell it.</li>"
                   ."<li>You can also search for particular fruits and vegetables by typing their name above.</li>"
                   ."<li>Search within your region for the seeds that grow best locally.</li>"
                   ."<li>We can even help you find certified organic seeds!<br/></li>"
                   ."</ul>"
                   ."<p style='margin-left:20px;font-size:8pt;'></p>" //<i>ESF fine print and disclaimers here</i></p>"
               ."</div>"
           ."</div>"
       ."</div>"

   ."</div></div></div>";

echo Console01Static::HTMLPage( $sBody, $sHead, $lang, $raParms = array( 'bBootstrap'=>true, 'sCharset'=>'ISO-8859-1' ) );


function MyToggle( $fld, $val, $actual_fld )
{
    $s = "<style>
         .btn-primary {background-color:#ddd; color:#555; height:22px; margin-top:5px; padding-top:0px; border-color:#777 }
         .btn-primary.active {background-color:green; border-color:green; font-weight:bold }
         </style>";

    $sNoActive  = !$val ? "active" : "";
    $sNoChecked = !$val ? "checked" : "";
    $sYesActive  = $val ? "active" : "";
    $sYesChecked = $val ? "checked" : "";


    $s .= "<div class='btn-group' data-toggle='buttons'>"
         ."<label class='btn btn-primary $sNoActive'>"
         ."<input type='radio' name='$actual_fld' id='option1' value='0' autocomplete='off' $sNoChecked> No"
         ."</label>"
         ."<label class='btn btn-primary $sYesActive'>"
         ."<input type='radio' name='$actual_fld' id='option2' value='1' autocomplete='off' $sYesChecked> Yes"
         ."</label>"
         ."</div>";

    return( $s );
}

?>

<script>

function showSuppliers( kPcv, sPname )
{
    var region = "<?php echo $reg; ?>";
    var organic = "<?php echo $bOrganic; ?>";
    var urlQ = "<?php echo Site_UrlQ(); ?>";

    var jxData = { cmd : 'srcSources',
                   lang : "EN",
                   kPcv : kPcv,
                   sRegions : region,
                   bOrganic : organic
                 };

    o = SEEDJX( urlQ, jxData );

    if( o && typeof o != 'undefined' && typeof o['raOut'] != 'undefined' ) {
        var nSuppliers = o['raOut'].length;

        s = "<h3>"+sPname+"</h3>"
           +"<p>Available from "+nSuppliers+" supplier"+(nSuppliers=="1" ? "" : "s")+"</p>";
        for( var i = 0; i < nSuppliers; i++ ) {
            ra = o['raOut'][i];
            s += "<p class='seedfinder-supplier'><b>"+ra['SRC_name']+"</b><br/>"
                +(ra['SRC_address'] ? (ra['SRC_address']+", ") : "")+ra['SRC_city']+" "+ra['SRC_prov']+" "+ra['SRC_postcode']+"<br/>"
                +"<a target='_blank' href='http://"+ra['SRC_web']+"'>"+ra['SRC_web']+"</a><br/><hr style='margin:5px 0px 10px 0px'/>";
            // and a link to show the description, and more info
        }
        $('#supplierWindow').html(s);
        $('#supplierWindow').show();
        $('#instructions').hide();
    }
}


/* // Make the supplierWindow stay in a fixed position -- confusing inside an iframe because it depends on which window is scrolling
$(window).scroll(function(){
	  $("#supplierWindow").css({"margin-top": ($(window).scrollTop()) + "px", "margin-left":($(window).scrollLeft()) + "px"});
	});
*/
//$('document').ready(function() { $('#supplierWindow').hide(); } );
</script>

