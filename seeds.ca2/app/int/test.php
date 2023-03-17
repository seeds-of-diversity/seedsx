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

SEEDPRG();


$raConsoleParms = [
    'raScriptFiles' => [W_CORE_URL."js/SEEDUI.js"],
    'raCSSFiles'    => [W_CORE_URL."css/SEEDUI.css"]
];


$s = "<div style='border:1px solid #aaa;margin-bottom:30px;padding:10px'>"
    ."<div><a href='{$oApp->PathToSelf()}?test='>Generic test</a></div>"
    ."<div><a href='{$oApp->PathToSelf()}?test=home-en'>Home page test - English</a></div>"
    ."<div><a href='{$oApp->PathToSelf()}?test=home-fr'>Home page test - French</a></div>"
    ."<div><a href='{$oApp->PathToSelf()}?test=home-edit'>Home page configuration</a></div>"
    ."<div><a href='{$oApp->PathToSelf()}?test=store'>Store test</a></div>"
    ."<div><a href='{$oApp->PathToSelf()}?test=events'>Events test</a></div>"
    ."<div><a href='{$oApp->PathToSelf()}?test=eventsOld'>Old Events test</a></div>"
    ."<div><a href='{$oApp->PathToSelf()}?test=csci'>CSCI test</a></div>"
    ."<div><a href='{$oApp->PathToSelf()}?test=sl-search'>Seed Library Search test</a></div>"
    ."<div><a href='{$oApp->PathToSelf()}?test=sl-list'>Seed Library List via Drupal test</a></div>"
    ."<div><a href='{$oApp->PathToSelf()}?test=bulletin'>bulletin signup test</a></div>"
    ."<div><a href='{$oApp->PathToSelf()}?test=docrep'>DocRep test</a></div>"
    ."<div><a href='{$oApp->PathToSelf()}?test=docrep_p_new'>DocRep test with New SEEDSessionAccount_Password enabled</a></div>"
    ."<div><a href='{$oApp->PathToSelf()}?test=docrep_p_old'>DocRep test with Old SEEDSessionAccount_Password enabled</a></div>"
    ."<div><a href='{$oApp->PathToSelf()}?test=msd'>MSE my seeds</a></div>"
    ."<div><a href='{$oApp->PathToSelf()}?test=dompdf'>DomPDF</a></div>"
    ."</div>";


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
        <div class='col-md-6'>
            <div class='SoDHomeBlock SoDHomeBlock01'>
            <a href='[[Var:linkA]]'>
                    <div class='SoDHomeBlockImg' style='background-image: url("[[Var:imgA]]");'>
                <div class='SoDHomeBlockCaption'>[[Var:captionA]]</div>
            </div>
            </a>
            </div>
        </div>
        <div class='col-md-6'>
            <div class='row'>    
                <div class='col-sm-6'>
                    <div class='SoDHomeBlock SoDHomeBlock02'>
                    <a href='[[Var:linkB]]'>
                    <div class='SoDHomeBlockImg' style='background-image: url("[[Var:imgB]]");'>
                        <div class='SoDHomeBlockCaption'>[[Var:captionB]]</div>
                    </div>
                    </a>
                    </div>
                </div>
                <div class='col-sm-6'>
                    <div class='SoDHomeBlock SoDHomeBlock02'>
                    <a href='[[Var:linkC]]'>
                    <div class='SoDHomeBlockImg' style='background-image: url("[[Var:imgC]]");'>
                        <div class='SoDHomeBlockCaption'>[[Var:captionC]]</div>
                    </div>
                    </a>
                    </div>
                </div>
            </div>
            <div class='row'>    
                <div class='col-sm-6'>
                    <div class='SoDHomeBlock SoDHomeBlock02'>
                    <a href='[[Var:linkD]]'>
                    <div class='SoDHomeBlockImg' style='background-image: url("[[Var:imgD]]");'>
                        <div class='SoDHomeBlockCaption'>[[Var:captionD]]</div>
                    </div>
                    </a>
                    </div>
                </div>
                <div class='col-sm-6'>
                    <div class='SoDHomeBlock SoDHomeBlock02'>
                    <a href='[[Var:linkE]]'>
                    <div class='SoDHomeBlockImg' style='background-image: url("[[Var:imgE]]");'>
                        <div class='SoDHomeBlockCaption'>[[Var:captionE]]</div>
                    </div>
                    </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
TmpHome;

$sTmpl = "";
$raParmsTmpl = [];

$oTmpl = new Drupal8Template( $oApp, [] );


switch( $test ) {
    case 'home-en':
        $oSB = new SEEDMetaTable_StringBucket( $oApp->kfdb, 0 );

        $def = ['lang'  => 'EN'];
        foreach( ['linkA', 'imgA', 'captionA',
                  'linkB', 'imgB', 'captionB',
                  'linkC', 'imgC', 'captionC',
                  'linkD', 'imgD', 'captionD',
                  'linkE', 'imgE', 'captionE' ] as $k )
        {
            $def[$k] = $oSB->GetStr('SeedsWPHomeTop', $k);
        }

/*
            'linkA'  => "A",
            'imgA'   => $oSB->GetStr('SeedsWPHomeTop', 'imgA'), // "../../d/?n=web/home/01.jpg",
            'labelA' => "<h4>Walk With a Horse</h4>It's good for your heart!",

            'linkB'  => "B",
            'imgB'   => "../../d/?n=web/home/02.jpg",
            'labelB' => "<h4>Learn How to Grow Beans</h4>They're easier than you think!",

            'linkC'  => "C",
            'imgC'   => "../../d/?n=web/home/03.jpg",
            'labelC' => "<h4>Learn How to Grow Flowers</h4>They're very pretty!",

            'linkD'  => "D",
            'imgD'   => "../../d/?n=web/home/04.jpg",
            'labelD' => "<h4>More Flowers</h4>You can never have enough!",

            'linkE'  => "E",
            'imgE'   => "../../d/?n=web/home/05.jpg",
            'labelE' => "<h4>Learn How to Make Pickles</h4>What completes a sandwich better?",
        ];
*/
        $s .= $oTmpl->ExpandStr( $sTmpHome, $def );
        break;
    case 'home-fr':    $s .= $oTmpl->ExpandStr( "[[SEEDContent:home-fr]]", [] );    break;
    case 'home-edit':  $s .= $oTmpl->ExpandStr( "[[SEEDContent:home-edit]]", [] );  break;

    case 'store':
//        $s .= $oTagParser->ProcessTags( "[[SEEDContent:store]]" );
        $oMbrOC = new SoDMbrOrderCheckout( $kfdb, $sess, $lang, false );
        $s .= $oMbrOC->Checkout();
        break;

    case 'events':
        $raConsoleParms['raScriptFiles'][] = "https://seeds.ca/app/ev/dist/jquery.vmap.js";
        $raConsoleParms['raScriptFiles'][] = "https://seeds.ca/app/ev/dist/maps/jquery.vmap.canada.js";
        $raConsoleParms['raCSSFiles'][]    = "https://seeds.ca/app/ev/dist/jqvmap.css";
        $sTmpl = "[[SEEDContent:events-page]]";
        break;

    case 'eventsOld':
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
        break;

    case 'docrep':
    case 'docrep_p_old':
    case 'docrep_p_new':
        $docrep_p = "<p>Your user id is [[SEEDSessionAccount_email:3]]</p>"
                   ."[[SEEDSessionAccount_TrustTest:3]] "
                   ."[[if: \$bSEEDSessionPasswordAutoGen "
                       ."| <p>Your password is [[SEEDSessionAccount_password:3]]</p> "  // password is original auto-gen so show it (the tag only works in _mbr_mail)
                       ."| <p>Forgot your password? <a href='https://seeds.ca/login?sessioncmd=sendPwd' target='_blank'>Click here to get it back</a></p>"
                   ."]]";
        switch($test) {
            case 'docrep':
                $s .= $oTmpl->ExpandStr( $docrep_p, [] );
                break;
            case 'docrep_p_old':
                include_once( STDINC."SEEDSessionAccountTag.php" );
                $oSessTag = new SEEDSessionAccountTag( $kfdb, $oApp->sess->GetUID(), array('bAllowKMbr'=>true,'bAllowPwd'=>true) );
                $oTmplP = new Drupal8Template( $oApp, ['EnableSEEDSession'=>['oSessTag'=>$oSessTag]] );
                $s .= $oTmplP->ExpandStr( $docrep_p, [] );
                break;
            case 'docrep_p_new':
                include_once( SEEDCORE."SEEDSessionAccountTag.php" );
                $oSessTag = new SEEDSessionAccountTagHandler( $oApp, ['bAllowKMbr'=>true,'bAllowPwd'=>true] );
                $oMT_P = new SoDMasterTemplate( $oApp, ['oSessionAccountTag'=>$oSessTag] );
                $s .= $oMT_P->GetTmpl()->ExpandStr( $docrep_p, [] );
                break;
        }
        break;

    case 'msd':     $sTmpl = "[[msd:seedlist|1499]]";   break;

    case 'dompdf':
        $dompdf = new Dompdf\Dompdf();
        $dompdf->loadHtml('hello world');
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();
        $dompdf->stream( 'file.pdf', ['Attachment' => 0] );
        exit;

    default:
        $s .= $oTmpl->ExpandStr( "[[lower:Foo]] [[upper:Foo]] <br/><br/> [[Image://www.seeds.ca/i/img/logo/logoA_h-en-750x.png|{width=100}]]<br/><br/> [[docreptest:]]", [] );
        break;
}

if( $sTmpl ) {
    $oMT = new SoDMasterTemplate( $oApp, ['config_bUTF8'=>true] );  // templates are utf8 and tags substituted are converted to utf8
    $s .= $oMT->GetTmpl()->ExpandStr( $sTmpl, $raParmsTmpl );
}


echo Console02Static::HTMLPage( $s, "", $lang, $raConsoleParms );
