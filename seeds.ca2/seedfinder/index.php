<?php

// we want non-filtered search to return TopChoices, but we also want a non-filtered way to get a complete spreadsheet

include_once("../site.php");
include_once(STDINC."SEEDTemplate.php");
include_once(SEEDCOMMON."sl/q/_QServerSourceCV.php");


list($kfdb, $sess, $lang) = SiteStartSessionAccountNoUI();


    // Make the species <select>
$oQ = new Qold( $kfdb, $sess, null, array('bUTF8'=>true,'klugeESFLang'=>$lang) );  // oApp can be null for now
$oSrc = new QServerSourceCV_Old( $oQ, array() );
$rQ = $oSrc->Cmd( 'srcSpecies', array( 'bAllComp'=>true, 'outFmt'=>'NameKey', 'spMap'=>'ESF' ) );

$spOpts = "";
if( $rQ['bOk'] ) {
    foreach( $rQ['raOut'] as $sSp => $kSp ) {
        if( substr($kSp,0,3) == 'spk' ) {
            $spOpts .= "<option value='".substr($kSp,3)."'>$sSp</option>";
        }
    }
}

    $raTmplVars = array( 'lang'=>$lang, 'spOptions'=>$spOpts );

    $o = new SEEDTemplate_Generator( array( 'fTemplates' => array(SITEROOT."seedfinder/seedfinder.html"),
                                            'SEEDTagParms' => array(),
                                            'vars' => $raTmplVars
    ) );
    $oTmpl = $o->MakeSEEDTemplate();

    $s = $oTmpl->ExpandTmpl( "main" );

    echo $s;
?>

