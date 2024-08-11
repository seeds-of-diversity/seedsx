<?php

include_once( SEEDCOMMON."siteStart.php" );
//include_once( SEEDCORE."SEEDTag.php");    // SEEDTagParser
//include_once( SEEDCOMMON."doc/docUtil.php" );
include_once( SEEDCOMMON."siteTemplate.php" );
include_once( SEEDLIB."SEEDTemplate/masterTemplate.php" );


/*
in preprocess_html in *.theme, this is how to add a D7-like nodeid identifier so css can act on specific pages

if ($node = \Drupal::request()->attributes->get('node')) {
    $variables['attributes']['class'][] = 'page-node-' . $node->id();
}

 */


function D8SeedBreeze_PreprocessPage( &$vars )
/*********************************************
    Called by the template before the page is processed
    A similar function is called from the seeds module see D8SeedsModule_PreprocessPage() which can basically do exactly the same thing.
 */
{
    /* This is a good place to set variables for the template.
     */

    //list($kfdb) = SiteStart();
    //$a = $kfdb->Query1( "SELECT count(*) FROM sl_inventory" );
    //$vars['count_sl_inventory'] = $a;

    if( $vars['is_front'] ) {
        $vars['myFrontContent'] = "This is the home page";
    } else {
        $vars['notFrontContent'] = "This is some page";
    }

}



function D8SeedsModule_PreprocessPage( &$vars )
/**********************************************
   Called from seeds.module::seeds_preprocess_page().
   This has nothing to do with the SeedBreeze theme, but there's no reason to put this code in a separate file.
 */
{
    //var_dump($vars);
//    $node = \Drupal::request()->attributes->get('node');if($node)var_dump($node->getTitle());
    /* This is a good place to set variables for the template.
     */

    //list($kfdb) = SiteStart();
    //$a = $kfdb->Query1( "SELECT count(*) FROM SEEDSession_Users" );
    //$vars['count_SEEDSession_Users'] = $a;

    /* PostRender:
     * When you set #postrender to a function, it will be called when the html is all ready.
     * function D8Seeds_PostRender( $html, $vars ) where $html is the finished output, and $vars is the original variables array for reference.
     * The function returns amended $html which becomes the new page output.
     * Pros: the rendering engine filters things that can be used in XSS attacks, such as <script> and <style> so it's better to do stuff related to
     * that after rendering.
     * Cons: for some reason, any changes made in the postrender function are recorded through an edit cycle. So you can't use this for tag substitution
     * because the tags get replaced permanently when you edit the page.
     */
     //$vars['page']['content']['#post_render'] = array("D8Seeds_PostRender");
}


function D8SeedsModule_NodeViewAlter( $build, $node, $display )
{
    //var_dump($build->content);
    //$build->content['body'] = array( '#markup' => "GHI", '#weight'=>1 );
}


function D8SeedsModule_NodeView( array &$build, \Drupal\Core\Entity\EntityInterface $entity,
                                 \Drupal\Core\Entity\Display\EntityViewDisplayInterface $display, $view_mode )
/*************************************************************************************************************
    Called from seeds.module::seeds_node_view().
    This is the right place to substitute SEEDTags in drupal content.
 */
{
/* Could probably also do this in seedbreeze_preprocess_html because by then the page has been rendered to vars['page'], etc.
 */
    //var_dump($build);

    if( !@$build['body'][0]['#text'] )  return;

    $oApp = SEEDConfig_NewAppConsole_LoginNotRequired( ['db'=>'seeds1'] );

    $oTmpl = new Drupal8Template( $oApp );

    $build['body'][0]['#text'] = $oTmpl->ExpandStr( $build['body'][0]['#text'], [] );
    $build['body'][0]['#cache']['max-age'] = 0;
}


class Drupal8Template
/********************
    Make a SEEDTemplate that handles [[SEEDContent:]] tags for our drupal pages
 */
 {
    private $oApp;
    private $kfdb;
    private $sess;      // some UIs use this for session variables

    private $oTmplOld;  // MasterTemplate
    private $oTmplNew;  // SoDMasterTemplate

    function __construct( SEEDAppConsole $oApp, $raConfig = [] )
    {
        $this->oApp = $oApp;
        list($kfdb,$sess) = SiteStartSession();
        $this->kfdb = $kfdb;
        $this->sess = $sess;

        $uid = 0;
        $lang = "EN";

        $raMT = $raConfig + [
            // [[SEEDContent:]]
            'raSEEDTemplateMakerParms' =>
                    [ 'raResolvers' => [[ 'fn'=>[$this,'ResolveTagOld'], 'raParms'=>[] ]],
                      'kluge_dontEatMyTag'=>true
                    ],
            // [[DocRep tags]]
            'EnableDocRep' => [ 'site'=>'public', 'flag'=>'PUB' ]
        ];
        $this->oTmplOld = (new MasterTemplate( $kfdb, $uid, $lang, $raMT ))->GetTmpl();

        $raMT2 = [];    // SoDMasterTemplate shouldn't need any setup parms, just variables
        $this->oTmplNew = (new SoDMasterTemplate( $oApp, $raMT2 ))->GetTmpl();
    }

    function ExpandStr( $s, $raParms )
    {
        if( $this->oTmplOld )  $s = $this->oTmplOld->ExpandStr( $s, $raParms );
        if( $this->oTmplNew )  $s = $this->oTmplNew->ExpandStr( $s, $raParms );

        return( $s );
    }

    function ResolveTagOld( $raTag, SEEDTagParser $oTagDummy, $raParmsDummy )
    /****************************
        [[SEEDContent: target | {EN or FR}]]
     */
    {
        $s = "";
        $bHandled = false;

        if( $raTag['tag'] != 'SEEDContent' )  goto done;

        $bHandled = true;

        $contentName = $raTag['target'];
        $lang = strtolower(@$raTag['raParms']['1'] ?? "")=='fr' ? "FR" : "EN";  // only defined this way for some tags

        if( method_exists('\Drupal\Core\Url', 'fromRoute') ) {
            $pathSelf = \Drupal\Core\Url::fromRoute('<current>')->toString();
        } else {
            $pathSelf = Site_path_self();
        }

        switch( $contentName ) {
            case 'homeimg':       // deprecated 3x4 image with caption
            case 'homeimg_v':     // deprecated image only, vertically centered
            case 'homeimgA':      // 4x3 image with caption
            case 'homeimgA1':     //    object-fit: contain instead of cover
            case 'homeimgB':      // 8.5x11 image with caption
            case 'homeimgB1':     //    object-fit: contain instead of cover
            case 'homeimgC':      // 1x1 image with caption
            case 'homeimgV':      // 1x1 image only, vertically centered
                $img = @$raTag['raParms'][1];
                $caption = @$raTag['raParms'][2];
                $link = @$raTag['raParms'][3];
                $sWidth = "width:85%";

                // homeimg_v is for images only, vertically centered.  vertical-align is not allowed for bootstrap's settings for display and float
                $styleVert = $contentName=='homeimg_v1' ? "display:inline-block;float:none;vertical-align:middle" : "";

                if( substr($img,0,4) != 'http' && substr($img,0,1) != '/' ) $img = "//seeds.ca/d?n=".$img;

                $sObjectFit = "cover";
                $sCaptionMarginTop = "0px";
                $sPaddingTop = '100%';
                switch( $contentName ) {
                    case 'homeimgA':
                    case 'homeimgA1':
                        // Make a 4x3 div inside the bootstrap grid div.
                        // Position the image inside. Shrink it 90% to make nicer whitespace, and because of that move it to the right 5% to center it.
                        $sPaddingTop = "75%";
                        if( $contentName == 'homeimgA1' )  $sObjectFit = "contain";
                        break;
                    case 'homeimgB':
                    case 'homeimgB1':
                        // Make a 8.5x11 div inside the bootstrap grid div.
                        // Position the image inside. Shrink it 90% to make nicer whitespace, and because of that move it to the right 5% to center it.
                        $sPaddingTop = "130%";
                        $sCaptionMarginTop = "-20px";  // close a gap above the caption
                        if( $contentName == 'homeimgB1' )  $sObjectFit = "contain";
                        break;
                    case 'homeimgC':
                        // Make a 1x1 div inside the bootstrap grid div.
                        // Position the image inside. Shrink it 90% to make nicer whitespace, and because of that move it to the right 5% to center it.
                        $sPaddingTop = "100%";
                        $sCaptionMarginTop = "-20px";  // close a gap above the caption
                        break;
                    case 'homeimgV':
                        // For vertically centering images.
                        // Make a 1x1 div inside the bootstrap grid div.
                        // Scale the image to fit in the div, centered horizontally and vertically.
                        $sPaddingTop = "100%";
                        $sObjectFit = "contain";
                        break;

                }
                $s = "<div style='width:100%;padding-top:$sPaddingTop;position:relative;'>"
                        ."<a href='$link' style='text-decoration:none;border:none'>"
                        ."<img src='$img' style='position:absolute;top:0;left:5%;width:90%;height:90%;border-radius:5px;object-fit:$sObjectFit' />"
                        ."</a>"
                    ."</div>";
                if( $caption ) {
                    $s .= "<p style='font-weight:bold;font-size:large;margin-top:$sCaptionMarginTop'><a href='$link' style='text-decoration:none'>$caption</a></p>";
                }

                if( $contentName == 'homeimg_v' ) {
                     $s = "<div style='display:inline-block;vertical-align:middle;float:none;'>"
                            ."<a href='$link' style='text-decoration:none;border:none'><img src='$img' style='$sWidth;border-radius:5px;object-fit:contain'/></a>"
                            ."</div>";
                } else if( $contentName == 'homeimg' ) {
                    $s = "<div style='width:100%;padding-top:130%;position:relative;'>"
                        ."<div style='position:absolute;top:0;bottom:0;left:0;right:0;'>"
                            ."<a href='$link' style='text-decoration:none;border:none'><img src='$img' style='$sWidth;border-radius:5px;'/></a>"
                            ."<p style='font-weight:bold;font-size:large;padding-top:10px'><a href='$link' style='text-decoration:none'>$caption</a></p>"
                        ."</div></div>";
                }

                $s = "<div class='col-xs-12 col-sm-6 col-md-4 col-lg-3' style='text-align:center;'>$s</div>";
                break;
            case 'helloworld':
                $s = "Hello World!";
                break;
            case 'bulletin-action':
                include_once( SITEROOT."l/mbr/bulletin.php" );
                $s = (new SoDBulletin($this->oApp))->HandleAction();
                $s = iconv( 'Windows-1252', 'UTF-8//IGNORE', $s );
                break;
            case 'bulletin-control':
                include_once( SITEROOT."l/mbr/bulletin.php" );
                $s = (new SoDBulletin($this->oApp))->ControlDraw();
                $s = iconv( 'Windows-1252', 'UTF-8//IGNORE', $s );
                break;
            case 'events':
            case 'evenements':
                include_once( SEEDCOMMON."ev/_ev.php" );
                $s = DrawEvents( $this->kfdb, $contentName == 'events' ? "EN" : "FR" );
                $s = iconv( 'Windows-1252', 'UTF-8//IGNORE', $s );
                break;
            case 'csci_species':
            case 'csci_companies':
            case 'csci_companies_varieties':
                /* csci_species draws the species list.
                 * csci_companies draws the companies list.
                 * csci_companies_varieties draws the varieties list if psp has a value,
                 *                          or the companies list if it doesn't (this simplifies the csci UI).
                 */
/*
                include_once( SEEDCOMMON."sl/csci.php" );
                $oCSCI = new SL_CSCI( $this->kfdb, $lang );
                $s = ($contentName == 'csci_species')
                    ? $oCSCI->DrawSpeciesList()
                    : $oCSCI->DrawSeedSourceList( SEEDSafeGPC_GetStrPlain('psp') );
*/
                include_once( SEEDCOMMON."sl/sl_sources_common.php" );
                $oSLSrc = new SLSourcesDraw( $this->kfdb );
                $sSp = ""; //because it can be short-circuited
                if( $contentName == 'csci_species' ) {
                    $s .= $oSLSrc->DrawSpeciesList( "", $lang,
                              array( 'bCompaniesOnly'=>true,
                                     'bCount' => true,
                                     'bIndex' => true,
                                     'sTemplate' => "<div class='csci_species' style=''><a href='$pathSelf?psp=[[var:k]]'>[[var:name]] [[ifnot0:\$n|([[var:n]])]]</a></div>" ) );
                } else if( $contentName == 'csci_companies_varieties' &&
                           (($kSp = SEEDSafeGPC_GetInt('psp'))
                             // kluge to pass non-indexed sp names
                             || ($sSp = SEEDSafeGPC_GetStrPlain('psp'))) ) {
// this should process spapp keys too
                    if( $sSp && SEEDCore_StartsWith($sSp, 'spk') ) {
                        $kSp = intval(substr($sSp,3));
                    }
                    if( $kSp ) {
                        $s .= "<p><a href='$pathSelf'>Back to Companies</a></p>
                                    <style>.slsrc_dcvblock_companies { padding-left:40px }
                                    </style>"
                             .$oSLSrc->DrawCompaniesVarieties( $this->oApp, $kSp, $sSp, $lang,
                                  array( /*'sTemplate' => "<div style=''><a href='".Site_path_self()."?psp=[[var:k]]'>[[var:name]] ([[var:n]])</a></div>"*/ ) );
                    }
                } else {
                    $s .= $oSLSrc->DrawCompanies( $lang );
                }
                //$s = iconv( 'ISO-8859-1', 'UTF-8', $s );
                $s = iconv( 'Windows-1252', 'UTF-8//IGNORE', $s );
                break;

            case 'historic-seed-catalogues':
                include_once( SITEROOT."l/seedcat/seedcat.php" );
                $s = DrawSeedcatPage( $this->kfdb, $lang );
                $s = iconv( 'Windows-1252', 'UTF-8//IGNORE', $s );
                break;

            case 'store':
            case 'boutique':
                include_once( SITEROOT."l/mbr/checkout.php" );
                $s = DrawMbr( $this->kfdb, $contentName == 'store' ? "EN" : "FR", true );
                //$s = iconv( 'ISO-8859-1', 'UTF-8', $s );
                $s = iconv( 'Windows-1252', 'UTF-8//IGNORE', $s );
                break;

            case 'diversity/seed-library-search':
                include_once( SEEDAPP."sl/search.php" );
                $s = SEEDCore_utf8_encode( (new SLSearchApp($this->oApp))->Draw() );
                break;

            case 'diversity/seed-library-list':
            case 'diversite/bibliotheque-semences-liste':
                include_once( SITEROOT."l/sl/sl_public.php" );
                $o = new SL_Public( $this->kfdb, $this->sess, $contentName == 'bibliotheque-semences' ? "FR" : "EN" );  // uses $sess for oSVA in UI
                $s = $o->Draw();
                $s = iconv( 'Windows-1252', 'UTF-8//IGNORE', $s );
                break;

            default:
                $bHandled = false;
                break;
        }

        done:
        return( array($bHandled,$s) );
    }

/*
    function HandleTag( $raTag )
    {
        $s = parent::HandleTag( $raTag );

        return( $s );
    }
*/
}
