<?php

include_once( SEEDCOMMON."siteStart.php" );
//include_once( SEEDCORE."SEEDTag.php");    // SEEDTagParser
//include_once( SEEDCOMMON."doc/docUtil.php" );
include_once( SEEDCOMMON."siteTemplate.php" );


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

    list($kfdb,$sess) = SiteStartSession();
    $uid = 0;
    $lang = "EN";

    // [[SEEDContent:]]
    $oHandler = new DrupalModTagHandler( $kfdb, $sess );
    $raTmplParms['raSEEDTemplateMakerParms']['raResolvers'][] = array( 'fn'=>array($oHandler,'ResolveTag'), 'raParms'=>array() );

    // [[DocRep tags]]
    $raTmplParms['EnableDocRep'] = array( 'site'=>'public', 'flag'=>'PUB' );

    $oMaster = new MasterTemplate( $kfdb, $uid, $lang, $raTmplParms );
    $oTmpl = $oMaster->GetTmpl();

    $build['body'][0]['#text'] = $oTmpl->ExpandStr( $build['body'][0]['#text'] );
}


class DrupalModTagHandler
{
    private $kfdb;
    private $sess;      // some UIs use this for session variables

    function __construct( KeyFrameDB $kfdb, SEEDSession $sess )
    {
        $this->kfdb = $kfdb;
        $this->sess = $sess;
    }

    function ResolveTag( $raTag, SEEDTagParser $oTagDummy, $raParmsDummy )
    /****************************
        [[SEEDContent: target | {EN or FR}]]
     */
    {
        $s = "";
        $bHandled = false;

        if( $raTag['tag'] != 'SEEDContent' )  goto done;

        $bHandled = true;

        $contentName = $raTag['target'];
        $lang = strtolower(@$raTag['1'])=='fr' ? "FR" : "EN";

        $pathSelf = \Drupal\Core\Url::fromRoute('<current>')->toString();

        switch( $contentName ) {
            case 'helloworld':
                $s = "Hello World!";
                break;
            case 'bulletin-action':
                include_once( SITEROOT."l/mbr/bulletin.php" );
                $s = BulletinHandleAction( $this->kfdb, $lang );
                $s = iconv( 'Windows-1252', 'UTF-8//IGNORE', $s );
                break;
            case 'bulletin-control':
                include_once( SITEROOT."l/mbr/bulletin.php" );
                $s = BulletinDrawControl( $this->kfdb, $lang );
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

                    $s .= "<p><a href='$pathSelf'>Back to Companies</a></p>"
                         .$oSLSrc->DrawCompaniesVarieties( $kSp, $sSp, $lang,
                              array( /*'sTemplate' => "<div style=''><a href='".Site_path_self()."?psp=[[var:k]]'>[[var:name]] ([[var:n]])</a></div>"*/ ) );
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

    function HandleTag( $raTag )
    {
        $s = parent::HandleTag( $raTag );

        return( $s );
    }
}

?>
