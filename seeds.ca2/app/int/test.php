<?php

/* Test station for modules normally seen via drupal
 */

if( !defined("SITEROOT") )  define("SITEROOT", "../../");
include_once( SITEROOT."site.php" );
include_once( SEEDCOMMON."mbr/seedCheckout.php" );

//include( SITEROOT."drupalmod/lib/dmod_seeds.php" );  Drupal 7
include_once( SITEROOT."drupalmod/lib/d8_seedbreeze.php" );


list($kfdb, $sess, $lang) = SiteStartSessionAccountNoUI();

$oApp = SEEDConfig_NewAppConsole_LoginNotRequired( ['db'=>'seeds1'] );

$oTmpl = new Drupal8Template( $oApp, [] );

$s = "<div style='border:1px solid #aaa;margin-bottom:30px;padding:10px'>"
    ."<div><a href='{$oApp->PathToSelf()}?test='>Generic test</a></div>"
    ."<div><a href='{$oApp->PathToSelf()}?test=home-en'>Home page test - English</a></div>"
    ."<div><a href='{$oApp->PathToSelf()}?test=home-fr'>Home page test - French</a></div>"
    ."<div><a href='{$oApp->PathToSelf()}?test=home-edit'>Home page configuration</a></div>"
    ."<div><a href='{$oApp->PathToSelf()}?test=store'>Store test</a></div>"
    ."<div><a href='{$oApp->PathToSelf()}?test=events'>Events test</a></div>"
    ."<div><a href='{$oApp->PathToSelf()}?test=csci'>CSCI test</a></div>"
    ."<div><a href='{$oApp->PathToSelf()}?test=sl-search'>Seed Library Search test</a></div>"
    ."<div><a href='{$oApp->PathToSelf()}?test=sl-list'>Seed Library List via Drupal test</a></div>"
    ."<div><a href='{$oApp->PathToSelf()}?test=bulletin'>bulletin signup test</a></div>"
    ."<div><a href='{$oApp->PathToSelf()}?test=docrep'>DocRep test</a></div>"
    ."<div><a href='{$oApp->PathToSelf()}?test=docrep_p'>DocRep test with SEEDSessionAccount_Password enabled</a></div>"
    ."<div><a href='{$oApp->PathToSelf()}?test=msd'>MSD my seeds</a></div>"
    ."</div>";


$docrep_p = "<p>Your user id is [[SEEDSessionAccount_email:3]]</p>"
           ."[[SEEDSessionAccount_TrustTest:3]] "
           ."[[if: \$bSEEDSessionPasswordAutoGen "
               ."| <p>Your password is [[SEEDSessionAccount_password:3]]</p> "  // password is original auto-gen so show it (the tag only works in _mbr_mail)
               ."| <p>Forgot your password? <a href='https://seeds.ca/login?sessioncmd=sendPwd' target='_blank'>Click here to get it back</a></p>"
           ."]]";


$test = $oApp->sess->SmartGPC( 'test' );

$sTmpHome = <<<TmpHome
<style>
.SoDHomeBlock {

}
.SoDHomeBlock01 {

}
.SoDHomeBlock02 {

}
.SoDHomeBlock .SoDHomeBlockImg {
    position:relative;
    overflow:hidden;
    padding-bottom:80%;
    background-position: center center;
    background-repeat: no-repeat;
    background-size:cover;
    margin:10px 0px;
}
.SoDHomeBlock .SoDHomeBlockCaption {
    position:absolute;
    bottom:0;
    left:10%;
    right:5%;
    margin:0px auto 15px auto;
    
    color: white;
    text-shadow: 1px 1px #888;
}

/* Screen < sm : all blocks are full width */
.SoDHomeBlock01 .SoDHomeBlockCaption h4 { font-size:xx-large; font-weight:bold; }
.SoDHomeBlock01 .SoDHomeBlockCaption    { font-size:x-large;   font-weight:bold; }
.SoDHomeBlock02 .SoDHomeBlockCaption h4 { font-size:xx-large;   font-weight:bold; }
.SoDHomeBlock02 .SoDHomeBlockCaption    { font-size:x-large;  font-weight:bold; }

/* Screen > sm: block01 is four times bigger than block02 */
@media only screen and (min-width : 768px){
.SoDHomeBlock01 .SoDHomeBlockCaption h4 { font-size:x-large; font-weight:bold; }
.SoDHomeBlock01 .SoDHomeBlockCaption    { font-size:large;   font-weight:bold; }
.SoDHomeBlock02 .SoDHomeBlockCaption h4 { font-size:large;   font-weight:bold; }
.SoDHomeBlock02 .SoDHomeBlockCaption    { font-size:medium;  font-weight:bold; }
}

.SoDHomeBlock a {
    text-decoration:none;
}
</style>

<div class='container-fluid' style='margin:0px auto;width:90%'>
    <div class='row'>
        <div class='col-sm-6'>
            <div class='SoDHomeBlock SoDHomeBlock01'>
            <a href='A'>
            <div class='SoDHomeBlockImg' style='background-image: url("../../../../docrep_upload1/sfile/01.jpg");'>
                <div class='SoDHomeBlockCaption'><h4>Walk With a Horse</h4>It's good for your heart!</div>
            </div>
            </a>
            </div>
        </div>
        <div class='col-sm-6'>
            <div class='row'>    
                <div class='col-sm-6'>
                    <div class='SoDHomeBlock SoDHomeBlock02'>
                    <a href='B'>
                    <div class='SoDHomeBlockImg' style='background-image: url("../../../../docrep_upload1/sfile/02.jpg');">
                        <div class='SoDHomeBlockCaption'><h4>Learn How to Grow Beans</h4>They're easier than you think!</div>
                    </div>
                    </a>
                    </div>
                </div>
                <div class='col-sm-6'>
                    <div class='SoDHomeBlock SoDHomeBlock02'>
                    <a href='C'>
                    <div class='SoDHomeBlockImg' style='background-image: url("../../../../docrep_upload1/sfile/03.jpg');">
                        <div class='SoDHomeBlockCaption'><h4>Learn How to Grow Flowers</h4>They're very pretty!</div>
                    </div>
                    </a>
                    </div>
                </div>
            </div>
            <div class='row'>    
                <div class='col-sm-6'>
                    <div class='SoDHomeBlock SoDHomeBlock02'>
                    <a href='D'>
                    <div class='SoDHomeBlockImg' style='background-image: url("../../../../docrep_upload1/sfile/04.jpg');">
                        <div class='SoDHomeBlockCaption'><h4>More Flowers</h4>You can never have enough!</div>
                    </div>
                    </a>
                    </div>
                </div>
                <div class='col-sm-6'>
                    <div class='SoDHomeBlock SoDHomeBlock02'>
                    <a href='E'>
                    <div class='SoDHomeBlockImg' style='background-image: url("../../../../docrep_upload1/sfile/05.jpg');">
                        <div class='SoDHomeBlockCaption'><h4>Learn How to Make Pickles</h4>What completes a sandwich better?</div>
                    </div>
                    </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
TmpHome;


switch( $test ) {
    case 'home-en':    $s .= $oTmpl->ExpandStr( $sTmpHome, ['lang'=>'EN'] );    break;
    case 'home-fr':    $s .= $oTmpl->ExpandStr( "[[SEEDContent:home-fr]]", [] );    break;
    case 'home-edit':  $s .= $oTmpl->ExpandStr( "[[SEEDContent:home-edit]]", [] );  break;

    case 'store':
//        $s .= $oTagParser->ProcessTags( "[[SEEDContent:store]]" );
        $oMbrOC = new SoDMbrOrderCheckout( $kfdb, $sess, $lang, false );
        $s .= $oMbrOC->Checkout();
        break;

    case 'events':
        $s .= $oTmpl->ExpandStr( "[[SEEDContent:events]]", [] );
        break;

    case 'sl-list':
        $s .= $oTmpl->ExpandStr( "[[SEEDContent:diversity/seed-library-list]]", [] );
        break;

    case 'sl-search':
        $s .= $oTmpl->ExpandStr( "[[SEEDContent:diversity/seed-library-search]]", [] );
        break;

    case 'csci':
        $s .= $oTmpl->ExpandStr( "<div class='container-fluid'><div class='row'><div class='col-md-8'>[[SEEDContent:csci_companies_varieties]]</div><div class='col-md-4'>[[SEEDContent:csci_species]]</div></div></div>", [] );
        break;

    case 'bulletin':
        $s .= $oTmpl->ExpandStr( "[[SEEDContent:bulletin-action]] Subscribe to Seeds of Diversity's free monthly e-bulletin! [[SEEDContent:bulletin-control]]", [] );

    case 'docrep':
        $s .= $oTmpl->ExpandStr( $docrep_p, [] );
        break;

    case 'docrep_p':
        include_once( STDINC."SEEDSessionAccountTag.php" );
        $oSessTag = new SEEDSessionAccountTag( $kfdb, $oApp->sess->GetUID(), array('bAllowKMbr'=>true,'bAllowPwd'=>true) );
        $oTmplP = new Drupal8Template( $oApp, ['EnableSEEDSession'=>['oSessTag'=>$oSessTag]] );
        $s .= $oTmplP->ExpandStr( $docrep_p, [] );
        break;

    case 'msd':
        $s .= $oTmpl->ExpandStr( "[[msd:seedlist|1499]]", [] );
        break;

    default:
        $s .= $oTmpl->ExpandStr( "[[lower:Foo]] [[upper:Foo]] <br/><br/> [[Image://www.seeds.ca/i/img/logo/logoA_h-en-750x.png|{width=100}]]<br/><br/> [[docreptest:]]", [] );
        break;
}

$raParms = [
    'raScriptFiles' => [W_CORE_URL."js/SEEDUI.js"],
    'raCSSFiles'    => [W_CORE_URL."css/SEEDUI.css"]
];

echo Console02Static::HTMLPage( $s, "", $lang, $raParms );
