<?php

/* Crop Profiles admin
 *
 * Copyright (c) 2018 Seeds of Diversity Canada
 */

define( "SITEROOT", "../../../" );
include_once( SITEROOT."site2.php" );
include_once( SEEDCOMMON."console/console01.php" );
include_once( SEEDCORE."SEEDGrid.php" );
include_once( SEEDCORE."SEEDUI.php" );
include_once( "profiles.php" );

//$oApp = SiteAppConsole( ['sessPermsRequired' => ['W slProfilesOffice'] ] );
$oApp = new SEEDAppConsole( $config_KFDB['seeds2']
                            + array( 'sessPermsRequired' => array('W slProfilesOffice'),
                                     'logdir' => SITE_LOG_ROOT ) );
if( !$oApp->sess->IsLogin() )  header( "Location: ../../login/" );

//var_dump($_REQUEST);
$oApp->kfdb->SetDebug(1);

$oCP = new CropProfiles( $oApp );

$sTop = "<form method='get'><input type='submit' name='doNew' value='Add New'/></form>";
$sBottom = "";
$sReport = "";
$kVI = 0;


if( SEEDInput_Str( 'doNew' ) ) {
    // Clicked on Add New
    $oF = new SEEDCoreForm( 'B' );
    $sReport .= "<form method='get'>"
        ."<table>"
        ."<tr>".$oF->TextTD( 'uid', "Member #" )."</tr>"
        ."<tr>".$oF->TextTD( 'fk_sl_accession', "Lot #" )."</tr>"
        ."<tr>".$oF->TextTD( 'fk_sl_pcv', "PCV #" )."</tr>"
        ."<tr>".$oF->TextTD( 'osp', "Species" )."</tr>"
        ."<tr>".$oF->TextTD( 'oname', "Variety" )."</tr>"
        ."<tr>".$oF->TextTD( 'year', "Year" )."</tr>"
        ."</table>"
        ."<input type='submit' name='doNewVI' value='Add New Record'/>"
        ."</form>";

} else if( SEEDInput_Str( 'doNewVI' ) ) {
    // Clicked on Add New Record
    $oF = new SEEDCoreForm( 'B' );
    $oF->Load();

    // minimum required
    $uid = $oF->Value( 'uid' );
    $sp = $oF->Value( "osp");
    $cv = $oF->Value( "oname" );
    $year = $oF->Value( "year" );

    if( $uid && $sp && $cv && $year ) {
        if( (!$kfrM = $oCP->oProfilesDB->GetKFRCond( "Site", "uid='$uid'" )) ) {
            $kfrM = $oCP->oProfilesDB->GetKfrel( "Site" )->CreateRecord();
            $kfrM->SetValue( 'uid', $uid );
            $kfrM->PutDBRow();
        }

        $kfrVI = $oCP->oProfilesDB->GetKfrel( "VI" )->CreateRecord();
        $kfrVI->SetValue( "fk_mbr_sites", $kfrM->Key() );
        $kfrVI->SetValue( "fk_sl_accession", $oF->Value( "fk_sl_accession") );
        $kfrVI->SetValue( "fk_sl_pcv", $oF->Value( "fk_sl_pcv") );
        $kfrVI->SetValue( "osp", $sp );
        $kfrVI->SetValue( "oname", $cv );
        $kfrVI->SetValue( "year", $year );
        $kfrVI->PutDBRow();

        $kVI = $kfrVI->Key();

        $sReport .= "<p class='alert alert-success'>Added $sp : $cv</p>";
    } else {
        $sReport .= "<p class='alert alert-warning'>Please enter a Member #, species, variety, and year</p>";
    }
} else {
    $kVI = SEEDInput_Int('sfAui_k');
}

$currSp = $currCv = "";

$oUI = new SEEDUI();
$oComp = new SEEDUIComponent( $oUI );
$oComp->Update();
if( $kVI ) $oComp->Set_kCurr( $kVI );   // initialize the list to the right row e.g. if we just created a new row

$oList = new SEEDUIWidget_List( $oComp );
$oSrch = new SEEDUIWidget_SearchControl( $oComp, array('filters'=> array('First Name'=>'firstname','Last Name'=>'lastname')) );
$oComp->Start();



$raList = array();


if( ($iSortCol = $oComp->GetUIParm('sortdown')) ) {
    $bSortDown = true;
} else {
    $iSortCol = $oComp->GetUIParm('sortup');
    $bSortDown = false;
}
switch( $iSortCol ) {
    default:
    case 1: $sSort = $bSortDown ? 'VI.osp DESC,VI.oname' : 'VI.osp,VI.oname';     break;
    case 2: $sSort = $bSortDown ? 'VI.oname DESC,VI.osp' : 'VI.oname,VI.osp';     break;
    case 3: $sSort = 'Site.uid';                                                  break;
    case 4: $sSort = 'VI.year';                                                   break;
    case 5: $sSort = 'VI._key';                                                   break;
}


$sVI = "";
$oGrid = new SEEDBootstrapGrid( array( 'classCol1'=>'col-sm-3', 'classCol2'=>'col-sm-5', 'classCol3'=>'col-sm-2', 'classCol4'=>'col-sm-2' ) );
if( ($kfr = $oCP->oProfilesDB->GetKFRC( "VISite", "", array('sSortCol'=>$sSort,'bSortDown'=>$bSortDown))) ) {
    while( $kfr->CursorFetch() ) {
        list($sp,$cv) = $oCP->oProfilesDB->ComputeVarInstName( $kfr->ValuesRA() );

        $raList[] = array( 'sp'=>$sp, 'cv'=>$cv, 'year'=>$kfr->Value('year'), 'observer'=>$kfr->Value('Site_uid'),
                           'k'=>$kfr->Key(),'_key'=>$kfr->Key(), 'sfuiLink'=>"?kVi=".$kfr->Key() );

        if( $kfr->Key() == $kVI ) {
            $currSp = $sp;
            $currCv = $cv;
            $sp = "<div style='background-color:#aaa;color:white'>$sp</div>";
            $cv = "<div style='background-color:#aaa;color:white'>$cv</div>";
        }

        $sp = "<a href='".Site_path_self()."?kVi=".$kfr->Key()."'>$sp</a>";
        $cv = "<a href='".Site_path_self()."?kVi=".$kfr->Key()."'>$cv</a>";
        $sVI .= $oGrid->Row( $sp, $cv, $kfr->Value('year'), "(".$kfr->Key().")" );
    }
}


$iOffset = 0;
$nSize = -1;
$raListParms = array( 'tableWidth' => "100%",
                      'cols' => array( array( 'label'=>'species',  'col'=>'sp',       '/w'=>'10%' ),
                                       array( 'label'=>'cultivar', 'col'=>'cv',       '/w'=>'40%' ),
                                       array( 'label'=>'observer', 'col'=>'observer', '/w'=>'20%'),
                                       array( 'label'=>'year',     'col'=>'year',     '/w'=>'10%'),
                                       array( 'label'=>'#',        'col'=>'k',        '/w'=>'10%') ),
                      'iViewOffset' => 0,
                      'nViewSize' => count($raList),
                      'nWindowSize' => count($raList),
                      //'fnRowTranslate' => array($this,'fnDrawListRowTranslate'),
                      'bUse_key' => true,
                      'kCurr' => $oComp->Get_kCurr()
);

$oViewWindow = new SEEDUIComponent_ViewWindow( $oComp, ['bEnableKeys'=>true] );
$oViewWindow->SetViewSlice( $raList, ['iViewSliceOffset' => 0, 'nViewSize' => count($raList)] );    // slice == full view

$sVI = $oList->Style()
      .$oList->ListDrawInteractive( $oViewWindow, $raListParms )
      //.$oList->ListDrawBasic( $raList, $iOffset, $nSize, $raListParms )
      //.$sVI;
      ;

if( $kVI ) {

    // this should be in oCP too
    $oF = new SLProfilesForm( $oCP->oProfilesDB, $kVI );
    $oF->Update();

    if( SEEDInput_Int('doForm') ) {
        // Show the form
        $sReport .= "<h3>Edit Record for $currSp : $currCv (#$kVI)</h3>"
                   .$oCP->oProfilesReport->DrawVIForm( $kVI, $oComp );
    } else {
        // Show the summary
        $sReport .= "<div style='border-left:1px solid #ddd;border-bottom:1px solid #ddd'>"
                   ."<div style='float:left;margin-right:20px;'>"
                       ."<form method='post'>"
                           .$oComp->HiddenFormUIParms( array('kCurr', 'sortup', 'sortdown') )
                           ."<input type='hidden' name='doForm' value='1'/>"
                           ."<input type='submit' value='Edit'/>"
                       ."</form>"
                   ."</div>"
                   ."<h3>Record #$kVI</h3>"
                   .$oCP->oProfilesReport->DrawVIRecord( $kVI, true )
                   ."</div>";
    }
}

$sHead = "";
$sBody = $sTop
        ."<div class='container'>"
        ."<div class='row'>"
            ."<div class='col-md-4'>$sVI</div>"
            ."<div class='col-md-8'>$sReport</div>"
        ."</div>"
        ."</div>"
        .$sBottom;


echo Console01Static::HTMLPage( $sBody, $sHead, $oApp->lang, array( 'bBootstrap' => true, 'sCharset'=>'ISO-8859-1' ) );

?>

<script type='text/javascript'>
var clean_uri = location.protocol + "//" + location.host + location.pathname;
window.history.replaceState({}, document.title, clean_uri);
</script>

