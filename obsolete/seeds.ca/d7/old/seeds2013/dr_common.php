<?php

/* [colour][0] is the English home page for that section
 * [colour][1] is the French home page for that section
 * [colour][n] are leading path segments that also trigger the theme
 */
$tpl_raThemes = array( 'blue'    => array( 'diversity',     'diversite' ),
                       'yellow'  => array( 'pollination',   'pollinisation' ),
                       'brown'   => array( 'organic-seeds', 'semences-biologique' ),
                       'magenta' => array( 'heritage',      'patrimoine' ) );


/* What's available in Drupal 7 templates?

    $template_file = file name of template starting after base_path()
    $base_url    = dir of drupal's index.php including http://host/  (must use global $base_url)

    Available as globals, also within $variables[] e.g. $variables['base_path']
    $base_path   = dir of drupal's index.php starting after the host name   == base_path() or use global $base_path
    $directory   = dir of theme starting after base_path()
    $user        = object
    $is_admin    = bool
    $logged_in   = bool
    $is_front    = bool
    $language    = object
*/

$tpl_sLang = in_array( $_SERVER['HTTP_HOST'], array('semences.ca','www.semences.ca',
                                                    'pollinisationcanada.ca','www.pollinisationcanada.ca') ) ? "FR" : "EN";
$tpl_bEN = ($tpl_sLang == 'EN');

global $base_url;
$tpl_sHome = $base_url;

$tpl_sThemeDir = base_path() . $directory;
$tpl_sThemeCommonDir = $tpl_sThemeDir;  // phase out

$sPageAlias = drupal_get_path_alias();  // argument is sometimes $_GET['q'] but NULL seems to be the same thing?

$tpl_sTheme = 'green';
foreach( $tpl_raThemes as $colour => $raPaths ) {
    foreach( $raPaths as $p ) {
        if( $p == $sPageAlias ||
            ($p == substr($sPageAlias,0,strlen($p)) && substr($sPageAlias,strlen($p),1) == '/') )
        {
            $tpl_sTheme = $colour;
            break;
        }
    }
}

?>