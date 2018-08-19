<?php

/* Included from sites/all/modules/seeds/seeds.module
 *
 * To include() files from here, the include path is probably ./;{drupal root}/
 * SITEROOT etc have been set accordingly in the seeds.module
 */

include_once( SEEDCOMMON."siteStart.php" );
//include_once( SEEDCORE."SEEDTag.php");    // SEEDTagParser
include_once( SEEDCOMMON."doc/docUtil.php" );
include_once( SEEDCOMMON."siteTemplate.php" );


/* Hook functions redirect here so we can change them in version control and conveniently update the code
 * without having to update the module in drupal.
 */

function DMod_Seeds_Menu()
{
    // 'access arguments' => array('access content') -- from old example

    $raBase  = array( 'title' => '',
                      'page callback' => '_DMod_Seeds_PageContent',
                      'page arguments' => array(0,""),  // pass the first (only) component of the path name e.g. 'store'
                      'access callback' => TRUE,
                      'menu_name' => 'menu-green',
                      'type' => MENU_NORMAL_ITEM,
                      'weight' => 0 );
    // use this for pages with paths like abd/def - it passes the path components as two arguments to the callback
    $raBaseTwoPath = array_merge( $raBase, array( 'page arguments' => array(0,1) ) );


    $items = array( 'store'      =>  array_merge( $raBase, array() ),
                    'boutique'   =>  array_merge( $raBase, array() ),

                    'events'     =>  array_merge( $raBase, array() ),
                    'evenements' =>  array_merge( $raBase, array() ),

                    //'csci'       =>  array_merge( $raBase, array( 'title' => 'Canadian Seed Catalogues' ) ),

                    //'sources'    =>  array_merge( $raBase, array( 'title' => 'Seed Sources' ) ),

                    'diversity/seed-library-list' => array_merge( $raBaseTwoPath, array( 'title' => 'Canadian Seed Library - List of Varieties' ) ),
                    'diversite/bibliotheque-semences-liste' => array_merge( $raBaseTwoPath, array( 'title' => 'Biblioth&egrave;que des semences' ) ),

                    'heritage/historic-seed-catalogues' => array_merge( $raBaseTwoPath, array( 'title' => 'Historic Seed Catalogues' ) ),
                    'patrimoine/catalogues-historiques' => array_merge( $raBaseTwoPath, array( 'title' => 'Les Catalogues Historiques') ),


    );

    return( $items );
}

/*
function DMod_Seeds_NodeInfo()
{
    return( array(
                'seeds_node_sources' =>
                    array( 'name' => 'Seeds - Sources',
                           'base' => 'seeds_node_sources',
                           'description' => 'Generates a formatted list of seed sources'
                    ),
                'seeds_node_csci' =>
                    array( 'name' => 'Seeds - CSCI',
                           'base' => 'seeds_node_csci',
                           'description' => 'Generates the formatted content for Canadian Seed Catalogue Inventory'
                    ),
                'seeds_node_store' =>
                    array( 'name' => 'Seeds - Membership Donation Order Form',
                           'base' => 'seeds_node_mbr',
                           'description' => 'Generates the formatted Membership Donation Order Form'
                    ),
                'seeds_node_sl' =>
                    array( 'name' => 'Seeds - Seed Library',
                           'base' => 'seeds_node_sl',
                           'description' => 'Generates the formatted content for the Seed Library'
                    ),
                'seeds_node_events' =>
                    array( 'name' => 'Seeds - Events',
                           'module' => 'seeds',
                           'base' => 'seeds_node_events',
                           'description' => 'Generates a formatted list of events',
                           // 'has_title' => TRUE,
                           // 'title_label' => t('Title'),
                           // 'has_body' => TRUE,
                           // 'body_label' => t('Body'),
                       ),
                ));
}
*/

function DMod_Seeds_NodeView( $node, $view_mode, $lang )
{
/*
    $raNodes = array( 'seeds_node_sources' => "sources",
                      'seeds_node_csci'    => "csci",
                      'seeds_node_store'   => "store",
                      'seeds_node_mbr'     => "store",     // deprecate
                      'seeds_node_sl'      => "seed-library",
                      'seeds_node_events'  => "events" );

    if( isset($raNodes[$node->type]) ) {
        $node->content['output'] = array( '#markup' => _DMod_Seeds_PageContent( $raNodes[$node->type] ),
                                          '#weight' => 0 );
    }
*/

    /* If node content appears to belong to us, and it contains [[tags]], translate them
     */
    //var_dump($node->content['body'][0]['#markup']); exit;
    //var_dump($node->type);
    if( in_array( $node->type, array('article', 'book','page') ) ||
        $node->type == 'semences_page_de_base' ||
        substr($node->type,-5) == '_book' ||
        substr($node->type,-6) == '_livre' )
    {
        $kfdb = SiteKFDB() or die( "Cannot connect to database" );
        $uid = 0;
        $lang = 'EN';

        if( ($oTmpl = New_DrupalTmpl( $kfdb, $uid, $lang )) ) {
            $node->content['body'][0]['#markup'] = $oTmpl->ExpandStr( @$node->content['body'][0]['#markup'] );
        }
    }
}

function New_DrupalTmpl( KeyFrameDB $kfdb, $uid, $lang, $raTmplParms = array() )
/*******************************************************************************
    Make a SEEDTemplate that handles [[SEEDContent:]] tags for our drupal pages
 */
{
    // [[SEEDContent:]]
    $oHandler = new DrupalModTagHandler( $kfdb );
    $raTmplParms['raSEEDTemplateMakerParms']['raResolvers'][] = array( 'fn'=>array($oHandler,'ResolveTag'), 'raParms'=>array() );

    // [[DocRep tags]]
    $raTmplParms['EnableDocRep'] = array( 'site'=>'public', 'flag'=>'PUB' );

    $oMaster = new MasterTemplate( $kfdb, $uid, $lang, $raTmplParms );
    return( $oMaster->GetTmpl() );
}


class DrupalModTagHandler
{
    private $kfdb;

    function __construct( KeyFrameDB $kfdb )
    {
        $this->kfdb = $kfdb;
    }

    function ResolveTag( $raTag, SEEDTagParser $oTagDummy, $raParmsDummy )
    /****************************
        [[SEEDContent: target | {EN or FR}]]
     */
    {
// This also works if you put it in HandleTag. It doesn't really matter, but maybe having it here makes it more clear when it executes.

        $s = "";
        $bHandled = false;

        if( $raTag['tag'] != 'SEEDContent' )  goto done;

        $bHandled = true;

        $contentName = $raTag['target'];
        $lang = strtolower(@$raTag['1'])=='fr' ? "FR" : "EN";

        switch( $contentName ) {
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
                $s = DrawEvents( $this->kfdb, $contentName == 'events' ? "EN" : "FR", true );
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
                                     'sTemplate' => "<div class='csci_species' style=''><a href='".Site_path_self()."?psp=[[var:k]]'>[[var:name]] [[ifnot0:\$n|([[var:n]])]]</a></div>" ) );
                } else if( $contentName == 'csci_companies_varieties' &&
                           (($kSp = SEEDSafeGPC_GetInt('psp'))
                             // kluge to pass non-indexed sp names
                             || ($sSp = SEEDSafeGPC_GetStrPlain('psp'))) ) {

                    $s .= "<p><a href='".Site_path_self()."'>Back to Companies</a></p>"
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


function _DMod_Seeds_PageContent( $pagename, $pagename2 )
/********************************************************
    The arguments are the "page arguments" of the menu item, which are path components of the page name e.g. foo/bar is given as (foo,bar)
 */
{
    if( $pagename2 )  $pagename .= "/$pagename2";

    $s = "";

    //$kfdb = SiteKFDB() or die( "Cannot connect to database" );
    list($kfdb,$sess) = SiteStartSession() or die( "Cannot connect to database" );

    switch( $pagename ) {
        case 'sources':
            $s = "<p>Seed Sources</p>";
            break;

        case 'csci':
            include_once( SEEDCOMMON."sl/csci.php" );
            $s = SL_CSCI_DrawPublicPage( $kfdb, "EN" );
            $s = iconv( 'Windows-1252', 'UTF-8//IGNORE', $s );
            break;

        case 'store':
        case 'boutique':
            include_once( SITEROOT."l/mbr/checkout.php" );
            $s = DrawMbr( $kfdb, $pagename == 'store' ? "EN" : "FR", true );
            //$s = iconv( 'ISO-8859-1', 'UTF-8', $s );
            $s = iconv( 'Windows-1252', 'UTF-8//IGNORE', $s );
            break;

        case 'diversity/seed-library-list':
        case 'diversite/bibliotheque-semences-liste':
            include_once( SITEROOT."l/sl/sl_public.php" );
            $o = new SL_Public( $kfdb, $sess, $pagename == 'bibliotheque-semences' ? "FR" : "EN" );  // uses $sess for oSVA in UI
            $s = $o->Draw();
            $s = iconv( 'Windows-1252', 'UTF-8//IGNORE', $s );
            break;

        case 'events':
        case 'evenements':
            include_once( SEEDCOMMON."ev/_ev.php" );
            $s = DrawEvents( $kfdb, $pagename == 'events' ? "EN" : "FR", true );
            //$s = iconv( 'ISO-8859-1', 'UTF-8', $s );
            $s = iconv( 'Windows-1252', 'UTF-8//IGNORE', $s );
            break;

        default:
            break;
    }

    return( $s );
}

?>
