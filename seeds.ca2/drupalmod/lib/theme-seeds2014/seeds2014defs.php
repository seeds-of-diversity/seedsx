<?php

/* Template parameters for seeds2014
 *
 * Here's what you get:
 *
 *     tpl_sLang      = EN or FR
 *     tpl_bEN
 *     tpl_urlHome    = the complete url including http: to the root of the site, with no trailing /
 *     tpl_pathHome   = the /-prefixed and /-suffixed path after the hostname to the root of the site
 *     tpl_pageAlias  = the name of the current page with no leading /
 *     tpl_raThemes   = array of supported themes
 *     tpl_sTheme     = name of current theme (the name is a colour)
 *     tpl_rgbLight   = light colour for the current theme (redundant because you can look it up from tpl_raThemes and tpl_sTheme)
 *     tpl_rgbMed     = medium colour for the current theme (redundant because you can look it up from tpl_raThemes and tpl_sTheme)
 *     tpl_urlStore   = url to the store page
 */


$tpl_sLang = (@$_REQUEST['lang'] == 'FR' ||
              strpos( $_SERVER['HTTP_HOST'], 'semences.ca' ) !== false ||
              strpos( $_SERVER['HTTP_HOST'], 'pollinisationcanada.ca' ) !== false) ? "FR" : "EN";
$tpl_bEN = ($tpl_sLang == 'EN');

// global $base_url = the complete url including http: to the root of the site, with NO trailing /
// $base_path       = the /-prefixed path after the hostname to the root of the site, with trailing /
// base_path()      = same as $base_path
// page alias       = the name of the current page with no leading /
global $base_url;
$tpl_urlHome = $base_url;
$tpl_pathHome = $base_path;
$tpl_pageAlias = drupal_get_path_alias();  // argument is sometimes $_GET['q'] but NULL seems to be the same thing?

//var_dump($tpl_urlHome,$tpl_pathHome,$tpl_pageAlias);


/* [theme]['paths'][0] is the English home page for that section
 * [theme]['paths'][1] is the French home page for that section
 * [theme]['paths'][n] are leading path segments that also trigger the theme
 *
 * [theme]['colours'][0] is the light colour for the theme
 * [theme]['colours'][1] is the medium colour for the theme
 */
$tpl_raThemes = array( 'blue'    => array( 'paths' => array('diversity',     'diversite'),            'colours' => array('d7e9f4', '61a6d1') ),
                       'yellow'  => array( 'paths' => array('pollination',   'pollinisation'),        'colours' => array('fef0cd', 'fdd368') ),
                       'brown'   => array( 'paths' => array('organic-seeds', 'semences-biologiques'), 'colours' => array('ecd9c6', 'bf8040') ),
                       'magenta' => array( 'paths' => array('heritage',      'patrimoine'),           'colours' => array('f1daeb', 'c969ae') ),
                       'green'   => array( 'paths' => array('default',       'default'),              'colours' => array('e1f3d8', '91c877') ),

                       );
$tpl_sTheme = 'green';
foreach( $tpl_raThemes as $colour => $ra ) {
    foreach( $ra['paths'] as $p ) {
        if( $p == $tpl_pageAlias ||
            ($p == substr($tpl_pageAlias,0,strlen($p)) && substr($tpl_pageAlias,strlen($p),1) == '/') )
        {
            $tpl_sTheme = $colour;
            break;
        }
    }
}
$tpl_rgbLight = $tpl_raThemes[$tpl_sTheme]['colours'][0];
$tpl_rgbMed   = $tpl_raThemes[$tpl_sTheme]['colours'][1];



if( STD_isLocal ) {
    $tpl_urlStore = $tpl_urlHome.($tpl_bEN ? "?q=store" : "?q=boutique");
} else {
    if( substr($tpl_urlHome, 0, 8) == "https://" ) {
        $tpl_urlStore = $tpl_urlHome;
    } else if( substr($tpl_urlHome, 0, 7) == "http://" ) {
        $tpl_urlStore = "https://".substr($tpl_urlHome, 7);    // or str_replace( "http://", "https://", $tpl_urlHome );
    } else {
        $tpl_urlStore = "https://$tpl_urlHome";  // never seen this case actually happen
    }
    $tpl_urlStore .= ($tpl_bEN ? "/store" : "/boutique");
}


$tpl['bEN']      = $tpl_bEN;
$tpl['sLangLC']  = ($tpl_bEN ? "en" : "fr");    // lower case, e.g. for use in image filenames
$tpl['urlHome']  = $tpl_urlHome;
$tpl['pathHome'] = $tpl_pathHome;
$tpl['urlStore'] = $tpl_urlStore;
$tpl['pageAlias']= $tpl_pageAlias;
$tpl['sTheme']   = $tpl_sTheme;
$tpl['raThemes'] = $tpl_raThemes;
$tpl['rgbLight'] = $tpl_rgbLight;
$tpl['rgbMed']   = $tpl_rgbMed;

$tpl['sVisibleXS']    = "visible-xs hidden-sm hidden-md hidden-lg";
$tpl['sVisibleXS-SM'] = "visible-xs visible-sm hidden-md hidden-lg";
$tpl['sHiddenXS']     = "hidden-xs visible-sm visible-md visible-lg";
$tpl['sHiddenXS-SM']  = "hidden-xs hidden-sm visible-md visible-lg";

?>
