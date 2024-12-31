<?php

// we want non-filtered search to return TopChoices, but we also want a non-filtered way to get a complete spreadsheet

include_once("../site.php");
include_once(STDINC."SEEDTemplate.php");
include_once(SEEDLIB."q/QServerSources.php");

list($kfdb, $sess, $lang) = SiteStartSessionAccountNoUI();

// Make the species <select>
$oApp = SEEDConfig_NewAppConsole_LoginNotRequired([]);
$oSrc = new QServerSourceCV( $oApp, array() );
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

$raTmplVars = array( 'lang'=>$lang, 'spOptions'=>$spOpts, 'yCurrent'=>date('Y'), 'mode'=>SEEDInput_Smart('mode',['finder','research']) );

$o = new SEEDTemplate_Generator( array( 'fTemplates' => array(SITEROOT."seedfinder/seedfinder.html"),
                                        'SEEDTagParms' => array(),
                                        'vars' => $raTmplVars
) );
$oTmpl = $o->MakeSEEDTemplate();

$s = $oTmpl->ExpandTmpl( "main" );

echo $s;
