<?php

/* Test station for modules normally seen via drupal
 */

if( !defined("SITEROOT") )  define("SITEROOT", "../../");
include_once( SITEROOT."site.php" );
include_once( SEEDCOMMON."mbr/seedCheckout.php" );

//include( SITEROOT."drupalmod/lib/dmod_seeds.php" );
include_once( SITEROOT."drupalmod/lib/d8_seedbreeze.php" );


list($kfdb, $sess, $lang) = SiteStartSessionAccountNoUI();

$oApp = SEEDConfig_NewAppConsole_LoginNotRequired( ['db'=>'seeds1'] );

$oTmpl = New_Drupal8Tmpl( $oApp, $oApp->sess->GetUID(), $lang, [] );

$s = "<div style='border:1px solid #aaa;margin-bottom:30px;padding:10px'>"
    ."<div><a href='".Site_path_self()."?test='>Generic test</a></div>"
    ."<div><a href='".Site_path_self()."?test=home-en'>Home page test - English</a></div>"
    ."<div><a href='".Site_path_self()."?test=home-fr'>Home page test - French</a></div>"
    ."<div><a href='".Site_path_self()."?test=home-edit'>Home page configuration</a></div>"
    ."<div><a href='".Site_path_self()."?test=store'>Store test</a></div>"
    ."<div><a href='".Site_path_self()."?test=events'>Events test</a></div>"
    ."<div><a href='".Site_path_self()."?test=csci'>CSCI test</a></div>"
    ."<div><a href='".Site_path_self()."?test=sl-list'>Seed Library List via Drupal test</a></div>"
    ."<div><a href='".Site_path_self()."?test=docrep'>DocRep test</a></div>"
    ."<div><a href='".Site_path_self()."?test=docrep_p'>DocRep test with SEEDSessionAccount_Password enabled</a></div>"
    ."</div>";


$docrep_p = "<p>Your user id is [[SEEDSessionAccount_email:3]]</p>"
           ."[[SEEDSessionAccount_TrustTest:3]] "
           ."[[if: \$bSEEDSessionPasswordAutoGen "
               ."| <p>Your password is [[SEEDSessionAccount_password:3]]</p> "  // password is original auto-gen so show it (the tag only works in _mbr_mail)
               ."| <p>Forgot your password? <a href='http://www.seeds.ca/login?sessioncmd=sendPwd' target='_blank'>Click here to get it back</a></p>"
           ."]]";


$test = $oApp->sess->SmartGPC( 'test' );

switch( $test ) {
    case 'home-en':    $s .= $oTmpl->ExpandStr( "[[SEEDContent:home-en]]" );    break;
    case 'home-fr':    $s .= $oTmpl->ExpandStr( "[[SEEDContent:home-fr]]" );    break;
    case 'home-edit':  $s .= $oTmpl->ExpandStr( "[[SEEDContent:home-edit]]" );  break;

    case 'store':
//        $s .= $oTagParser->ProcessTags( "[[SEEDContent:store]]" );
        $oMbrOC = new SoDMbrOrderCheckout( $kfdb, $sess, $lang, false );
        $s .= $oMbrOC->Checkout();
        break;

    case 'events':
        $s .= $oTmpl->ExpandStr( "[[SEEDContent:events]]" );
        break;

    case 'sl-list':
        $s .= $oTmpl->ExpandStr( "[[SEEDContent:diversity/seed-library-list]]" );
        break;

    case 'csci':
        $s .= $oTmpl->ExpandStr( "<div class='container-fluid'><div class='row'><div class='col-md-8'>[[SEEDContent:csci_companies_varieties]]</div><div class='col-md-4'>[[SEEDContent:csci_species]]</div></div></div>" );
        break;

    case 'docrep':
        $s .= $oTmpl->ExpandStr( $docrep_p );
        break;

    case 'docrep_p':
        include_once( STDINC."SEEDSessionAccountTag.php" );
        $oSessTag = new SEEDSessionAccountTag( $kfdb, $oApp->sess->GetUID(), array('bAllowKMbr'=>true,'bAllowPwd'=>true) );
        $oTmplP = New_Drupal8Tmpl( $oApp, $oApp->sess->GetUID(), $lang, array("EnableSEEDSession"=>array('oSessTag'=>$oSessTag)) );
        $s .= $oTmplP->ExpandStr( $docrep_p );
        break;

    default:
        $s .= $oTmpl->ExpandStr( "[[lower:Foo]] [[upper:Foo]] <br/><br/> [[Image://www.seeds.ca/i/img/logo/logoA_h-en-750x.png|{width=100}]]<br/><br/> [[docreptest:]] " );
        break;
}

$raParms = [
    'raScriptFiles' => [W_CORE_URL."js/SEEDUI.js"],
    'raCSSFiles'    => [W_CORE_URL."css/SEEDUI.css"]
];

echo Console02Static::HTMLPage( $s, "", $lang, $raParms );

?>