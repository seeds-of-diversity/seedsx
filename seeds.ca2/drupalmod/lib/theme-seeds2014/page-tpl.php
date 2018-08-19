<?php

/* Included from sites/all/themes/seeds2014bootstrap/templates
 *
 * To include() files from here, the include path is probably ./;{drupal root}/
 */

include( "seeds2014defs.php" );

/**
 * @file
 * Default theme implementation to display a single Drupal page.
 *
 * The doctype, html, head and body tags are not in this template. Instead they
 * can be found in the html.tpl.php template in this directory.
 *
 * Available variables:
 *
 * General utility variables:
 * - $base_path: The base URL path of the Drupal installation. At the very
 *   least, this will always default to /.
 * - $directory: The directory the template is located in, e.g. modules/system
 *   or themes/bartik.
 * - $is_front: TRUE if the current page is the front page.
 * - $logged_in: TRUE if the user is registered and signed in.
 * - $is_admin: TRUE if the user has permission to access administration pages.
 *
 * Site identity:
 * - $front_page: The URL of the front page. Use this instead of $base_path,
 *   when linking to the front page. This includes the language domain or
 *   prefix.
 * - $logo: The path to the logo image, as defined in theme configuration.
 * - $site_name: The name of the site, empty when display has been disabled
 *   in theme settings.
 * - $site_slogan: The slogan of the site, empty when display has been disabled
 *   in theme settings.
 *
 * Navigation:
 * - $main_menu (array): An array containing the Main menu links for the
 *   site, if they have been configured.
 * - $secondary_menu (array): An array containing the Secondary menu links for
 *   the site, if they have been configured.
 * - $breadcrumb: The breadcrumb trail for the current page.
 *
 * Page content (in order of occurrence in the default page.tpl.php):
 * - $title_prefix (array): An array containing additional output populated by
 *   modules, intended to be displayed in front of the main title tag that
 *   appears in the template.
 * - $title: The page title, for use in the actual HTML content.
 * - $title_suffix (array): An array containing additional output populated by
 *   modules, intended to be displayed after the main title tag that appears in
 *   the template.
 * - $messages: HTML for status and error messages. Should be displayed
 *   prominently.
 * - $tabs (array): Tabs linking to any sub-pages beneath the current page
 *   (e.g., the view and edit tabs when displaying a node).
 * - $action_links (array): Actions local to the page, such as 'Add menu' on the
 *   menu administration interface.
 * - $feed_icons: A string of all feed icons for the current page.
 * - $node: The node object, if there is an automatically-loaded node
 *   associated with the page, and the node ID is the second argument
 *   in the page's path (e.g. node/12345 and node/12345/revisions, but not
 *   comment/reply/12345).
 *
 * Regions:
 * - $page['help']: Dynamic help text, mostly for admin pages.
 * - $page['highlighted']: Items for the highlighted content region.
 * - $page['content']: The main content of the current page.
 * - $page['sidebar_first']: Items for the first sidebar.
 * - $page['sidebar_second']: Items for the second sidebar.
 * - $page['header']: Items for the header region.
 * - $page['footer']: Items for the footer region.
 *
 * @see bootstrap_preprocess_page()
 * @see template_preprocess()
 * @see template_preprocess_page()
 * @see bootstrap_process_page()
 * @see template_process()
 * @see html.tpl.php
 *
 * @ingroup themeable
 */


// resources accessed by url (e.g. images) are based from the url server root
$dir_theme = base_path().$directory."/";
//echo base_path();echo $base_url;
$dir_seeds2014 = $dir_theme."seeds2014/";

$sLogo = "//seeds.ca/i/img/logo/logoA_h-${tpl['sLangLC']}-750x.png";


// Copied from bootstrap:page.vars.php:bootstrap_preprocess_page()
// That code creates the primary_nav for English using main-menu.
// This replaces primary_nav with main-french-menu using the same method
if( $tpl_sLang == "FR" ) {
    // Build links.

    // menu_tree() is menu_tree_output(menu_tree_page_data()) but for some reason that returns nothing on French content type pages (but not French basic pages).
    //$primary_nav = menu_tree(variable_get('menu_main_links_source', 'menu-main-francais'));
    //$primary_nav = menu_tree( 'menu-main-francais' );
    $primary_nav = menu_tree_output( menu_tree_all_data( 'menu-main-francais' ));
    // Provide default theme wrapper function.
    $primary_nav['#theme_wrappers'] = array('menu_tree__primary');
}

// To make drop-down menus activate on hover, get https://github.com/CWSpear/bootstrap-hover-dropdown

// To make a menu item into a placeholder
//     1) get Special Menu Items module and use <nolink> as the menu item's target
//     2) use hook_menu_alter and try to make the menu item a MENU_CALLBACK that does nothing (maybe it returns true)
//     3) do both and steal the code from Special Menu Items so we don't need that module



//$sStore = url( ($tpl_bEN ? "store" : "magasin"), array( "https"=>true, "absolute"=>true ) );



echo seeds2014PageStyle( $tpl, $dir_seeds2014 );

?>

<div class='SeedSheet'>

<?php
/****************************************************************************************************
 * Background is absolutely positioned and cropped within the Container
 */
?>
<div class='SeedBannerContainer'>
<div class='SeedBannerBackground'></div>
<div class='SeedBannerLogo'>
    <a href='<?php echo $tpl_pathHome;?>' title='<?php echo t('Home');?>'>
    <img style='width:90%;height:auto;max-width:300px;' src='<?php echo $sLogo; ?>'/>
    </a>
</div>
</div>


<?php
/****************************************************************************************************
 * Menu bar
 */

/* NOT USING
  <header role="banner" id="page-header">
    <?php if (!empty($site_slogan)): ?>
      <p class="lead"><?php print $site_slogan; ?></p>
    <?php endif; ?>

    <?php print render($page['header']); ?>
  </header>
*/
?>
<header id="navbar" role="banner"
        class="navbar container-fluid navbar-default" <?php // <?php print $navbar_classes; (was "navbar container navbar-default")  ?>
        <?php // override .navbar {min-height:50px}
              // override overrides.min.css {margin:20px 0px 20px 0px}
              // override grid .container-fluid {padding-left:15px;padding-right:15px;}
              // put a line above the menu
        ?>
        style="min-height:30px;
               margin:0px 0px 15px 0px;
               padding-left:0px;padding-right:0px;
               border-top:1px solid #<?php echo $tpl_rgbMed; ?>; ">
    <div class="navbar-header">
      <?php
      /* NOT USING
      if( $logo ) {
          echo "<a class='logo navbar-btn pull-left' href='$front_page' title='".t('Home')."'>"
              ."<img src='$logo' alt='".t('Home')."'/>"
              ."</a>";
      }
      if( !empty($site_name) ) {
          echo "<a class='name navbar-brand' href='$front_page' title='".t('Home')."'>$site_name</a>";
      }
      */
      ?>
      <!-- .btn-navbar is used as the toggle for collapsed navbar content -->
      <button type="button" class="navbar-toggle" style='padding-top:0px;padding-bottom:3px;' data-toggle="collapse" data-target=".navbar-collapse">
        <span style='font-size:x-small'>Menu</span>
        <span class="sr-only">Show menu</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        </button>
    </div>

    <?php
    $s = "";
    if( !empty($primary_nav) || !empty($secondary_nav) || !empty($page['navigation']) ) {
        //var_dump($primary_nav,$secondary_nav,$page['navigation']);
        $s .= "<div class='navbar-collapse collapse' style='background-color:#$tpl_rgbLight;'>"
             ."<nav role='navigation'>";
        if( !empty($primary_nav) )         $s .= render($primary_nav);
        if( !empty($secondary_nav) )       $s .= render($secondary_nav);
        if( !empty($page['navigation']) )  $s .= render($page['navigation']);
        $s .= "</nav></div>";
    }
    echo $s;
    ?>
</header>

<?php
/****************************************************************************************************
 * Content
 *
 */

$bNoSeedIcons = in_array( $tpl_pageAlias, array( 'store', 'boutique' ) );

$s = "";

?>
<div class="main-container container-fluid" style=''>

<?php

if( !$bNoSeedIcons && $tpl_pageAlias != 'home' ) {
    $s .= seeds2014IconsTop( $dir_seeds2014, $tpl );
}

/* Main table containing Content and Sidebar (which might be hidden)
 */
//$s .= "<table width='100%'><tr><td valign='top'>";
$s .= "<div class='container-fluid' style='padding-right:10px'><div class='row'>";

if( $bNoSeedIcons ) { $s .= "<div class='col-md-12'>"; } else { $s .= "<div class='col-md-10'>"; }

if( $tpl_pageAlias == 'home' ) {
echo $s;

    include( "home-tpl.php" );
} else {

/*  NOT USING
    <?php if (!empty($page['sidebar_first'])): ?>
      <aside class="col-sm-2" role="complementary">
        <?php print render($page['sidebar_first']); ?>
      </aside>
    <?php endif; ?>
*/

// bs columns have 15px padding each, and rows have -15 margins.
// That makes the edges nice but 30px of space where columns meet.
// That also means stacked columns are nicely centered as-is, so any messing with margins/padding to get rid
// of the inter-column padding just messes up the stacking.

//    $s .= "<div class='row'>";


    $s .= "<section>";    // col-sm-9 col-md-10
    if( !empty($page['highlighted']) ) { $s .= "<div class='highlighted jumbotron'>".render($page['highlighted'])."</div>"; }

    // might be nice to have this somewhere else with different formatting
    // if( !empty($breadcrumb) ) { $s .= $breadcrumb; }

    $s .= "<a id='main-content'></a>"
         .$messages;
    if( !empty($tabs) ) { $s .= render($tabs); }
    if( !empty($page['help']) ) { $s .= render($page['help']); }
    if( !empty($action_links) ) { $s .= "<ul class='action-links'>".render($action_links)."</ul>"; }

    $s .= "<div id='SeedBody'>"
         ."<table border='0' cellpadding='0' cellspacing='0' width='100%'><tr valign='top'>";
    if( !$bNoSeedIcons ) {
        $s .= "<td class='${tpl['sHiddenXS-SM']}' width='120'>" // width='15%'
             .seeds2014IconsLeft( $dir_seeds2014, $tpl )
             ."</td>";
    }
    $s .= "<td>"
         ."<div class='SeedContentBox'>"
         .( !empty($title) && $tpl_pageAlias != 'home'
             ? (render($title_prefix)
               ."<h2 class='page-header'>$title</h2>"
               .render($title_suffix) )
             : "" )
         .render( $page['content'] )
         ."</div>"
         ."</td></tr></table>"
         ."</div>" // SeedBody
         ."</section>";

}   // not home.tpl

//$s .= "</td><td valign='top' width='150' class='${tpl['sHiddenXS']}'>";
$s .= "</div>";

if( !$bNoSeedIcons ) {
$s .= "<div class='col-md-2'>";

    $s .= "<aside  style='' role='complementary'>";  // 'col-sm-3 col-md-2'
    $s .= seeds2014Sidebar( $tpl, $page );
    $s .= "</aside>";
    $s .= "</div>";
}


//$s .= "</td></tr></table>";
$s .= "</div></div>";  // row, container

//    $s .= "<aside  class='${tpl['sVisibleXS']}' style='' role='complementary'>";  // 'col-sm-3 col-md-2'
//    $s .= seeds2014Sidebar( $tpl, $page );
//    $s .= "</aside>";



echo $s;



echo "</div>";  // main-container

/****************************************************************************************************
 * Footer
 *
 */
$s = "<footer class='footer container'>"
    ."<div style='font-size:small'><i>"
    /* print render($page['footer']); */
    ."Copyright &copy ".date("Y")
    .( $tpl_bEN
        ? (" Seeds of Diversity Canada")
        : (" Programme semencier du patrimoine Canada") )
    ."</i></div></footer>";

$s .= "</div>"; // SeedSheet

echo $s;






function seeds2014IconsTop( $dir_seeds2014, $tpl )
{
    $s = "<style>
           .SeedIconsHorzClickBox  { position:absolute;
                                     /*border:1px solid red;*/
                                     top:0px; height:90%; z-index:1;
                                   }
           .SeedIconsHorzClickSpan { position:absolute; top:0; left:0; width:100%; height:100%;}
           </style>"
        ."<div class='${tpl['sVisibleXS-SM']}' style='width:100%'>"
            ."<div style='margin:0px auto;width:100%;max-width:350px;display:block;position:relative'>"
               ."<img style='width:100%' src='${dir_seeds2014}seeds2014img/icons01h-${tpl['sLangLC']}.png'/>"
               ."<div class='SeedIconsHorzClickBox' style='left:5%;  width:20%;'><a href='".$tpl['pathHome'].$tpl['raThemes']['blue'   ]['paths'][$tpl['bEN'] ? 0 : 1]."'><span class='SeedIconsHorzClickSpan'>&nbsp;</span></a></div>"
               ."<div class='SeedIconsHorzClickBox' style='left:28%; width:20%;'><a href='".$tpl['pathHome'].$tpl['raThemes']['yellow' ]['paths'][$tpl['bEN'] ? 0 : 1]."'><span class='SeedIconsHorzClickSpan'>&nbsp;</span></a></div>"
               ."<div class='SeedIconsHorzClickBox' style='left:52%; width:20%;'><a href='".$tpl['pathHome'].$tpl['raThemes']['brown'  ]['paths'][$tpl['bEN'] ? 0 : 1]."'><span class='SeedIconsHorzClickSpan'>&nbsp;</span></a></div>"
               ."<div class='SeedIconsHorzClickBox' style='left:76%; width:20%;'><a href='".$tpl['pathHome'].$tpl['raThemes']['magenta']['paths'][$tpl['bEN'] ? 0 : 1]."'><span class='SeedIconsHorzClickSpan'>&nbsp;</span></a></div>"
               ."</div>"
        ."</div>";

    return( $s );
}

function seeds2014IconsLeft( $dir_seeds2014, $tpl )
{
    $s = "<div id='SeedIconsLeft' style='position:relative;float:left;width:120px;margin-top:-2px'>" // width:100%
        ."<img src='${dir_seeds2014}seeds2014img/icons01left-{$tpl['sTheme']}-{$tpl['sLangLC']}.png'>" // usemap='#SeedIconsLeftMap' width='100%'

        /*  <map name='SeedIconsLeftMap' id='SeedIconsLeftMap'>
                <area shape='rect' coords='20, 10,100,115' href=""/>
                <area shape='rect' coords='20,135,100,240' href=""/>
                <area shape='rect' coords='20,260,100,365' href=""/>
                <area shape='rect' coords='20,385,100,490' href=""/>
            </map>
         */

        /* Better image map:
         * SeedIconsClickBox positions a rectangle over each clickable area
         * SeedIconsClickSpan expands <a> to fill each of those rectangles
         *
         * TODO: IE7/8 might want the <span> to have an invisible background image
         *       instead of just a &nbsp; {background-image:url('empty.gif')}
         */
        ."<style>"
        .".SeedIconsClickBox  { position:absolute; left:20px; width:80px; height:105px; z-index:1; }" //width:100%; padding:100% 0 0 0;
        .".SeedIconsClickSpan { position:absolute; top:0; left:0; width:100%; height:100%;}"

        /*
          #myicons {
              background-image:url(http://localhost/~bob/seeds.ca2/sites/all/themes/seeds2014bootstrap/seeds2014/seeds2014img/icons01left-yellow-en.png) no-repeat;;
              background-size:cover;
              width:auto;height:500px;
          }
          @media screen and (min-width: @screen-sm-min) {
              #myicons { }
          }
        */
        ."</style>"

        ."<div class='SeedIconsClickBox' style='top:10px' ><a href='".$tpl['pathHome'].$tpl['raThemes']['blue'   ]['paths'][$tpl['bEN'] ? 0 : 1]."'><span class='SeedIconsClickSpan'>&nbsp;</span></a></div>"
        ."<div class='SeedIconsClickBox' style='top:135px'><a href='".$tpl['pathHome'].$tpl['raThemes']['yellow' ]['paths'][$tpl['bEN'] ? 0 : 1]."'><span class='SeedIconsClickSpan'>&nbsp;</span></a></div>"
        ."<div class='SeedIconsClickBox' style='top:260px'><a href='".$tpl['pathHome'].$tpl['raThemes']['brown'  ]['paths'][$tpl['bEN'] ? 0 : 1]."'><span class='SeedIconsClickSpan'>&nbsp;</span></a></div>"
        ."<div class='SeedIconsClickBox' style='top:385px'><a href='".$tpl['pathHome'].$tpl['raThemes']['magenta']['paths'][$tpl['bEN'] ? 0 : 1]."'><span class='SeedIconsClickSpan'>&nbsp;</span></a></div>"

        ."</div>";

    return( $s );
}

function seeds2014Sidebar( $tpl, $page )
{
    /* Fixed sidebar content
     */
    $sAnnualMembership =
        $tpl['bEN'] ? "An annual membership to Seeds of Diversity includes our quarterly magazine and our annual seed directory."
                    : "Un abonnement annuel aux Semences du patrimoine inclu quatres revues et le Catalogue des semences des membres.";
    $sOurWork =
        $tpl['bEN'] ? "We depend on donations to do our work."
                    : "Notre travail d&eacute;pend de vos dons.";
    $sThankYou =
        $tpl['bEN'] ? "Thank you for your support!"
                    : "Merci beaucoup de votre soutien!";
    $sStayInTouch =
        $tpl['bEN'] ? "Stay in Touch!"
                    : "Tenez-vous au courant!";
    $sFacebook =
        $tpl['bEN'] ? "http://www.facebook.com/pages/Seeds-of-Diversity-Canada/44285486714"
                    : "http://www.facebook.com/pages/Semences-du-patrimoine-Canada/210151879003875";

    $s = "<div class='SeedSidebar'>"
            .(!empty($page['sidebar_second']) ? render($page['sidebar_second']) : "")

            ."<div style='text-align: center;margin-top:30px'>"
                ."<a href='${tpl['urlStore']}'>"
                ."<img alt='' src='//www.seeds.ca/bulletin/img/2014/join-us-${tpl['sLangLC']}.png' "
                     ."style='width:100%;max-width:150px;margin:10px auto; display:block;'>"
                ."</a>"
                ."<p>$sAnnualMembership</p>"
            ."</div>"

            ."<img alt='' src='//www.seeds.ca/bulletin/img/2014/wheat-head.png'"
                 ."style='width:100%;max-width:25px;margin:20px auto;display:block;'>"

            ."<h3 style='font-size:11pt;text-align:center;'>$sOurWork</h3>"
            ."<a href='${tpl['urlStore']}'>"
            ."<img alt='' src='//www.seeds.ca/bulletin/img/2014/donate-${tpl['sLangLC']}.png' "
                 ."style='width:100%;max-width:120px;margin:10px auto;display:block;'>"
            ."</a>"
            ."<p style='text-align:center'>$sThankYou</p>"

            ."<h3 style='font-size:12pt;text-align:center;'>$sStayInTouch</h3>"
            ."<div style='font-size:8pt;text-align:center;'>"
                ."<a target='_blank' href='$sFacebook' style='text-decoration:none;color:white;'>"
                ."<img width='20' height='20' alt='facebook' src='//www.seeds.ca/d?n=web/ebulletin/img/2012/facebook_f_logo'>"
                ."</a>"
                ."<a target='_blank' href='//twitter.com/Seeds_Diversity' style='text-decoration:none;color:white;'>"
                ."<img width='20' height='20' alt='twitter' src='//www.seeds.ca/bulletin/img/2014/twitter_logo_blue.png'>"
                ."</a>"
            ."</div>"
        ."</div>";

    return( $s );
}



function seeds2014PageStyle( $tpl, $dir_seeds2014 )
{
    $s = "
<style>

/****************************************************************************************************
 * Image banner
 *
 * Since the logo has to sit on the faded left-hand part of the background images when the viewport is narrow,
 * make the background area min-width:1000px and crop it using an overflow:hidden
 */
    .SeedBannerContainer {
        position:relative;
        height:120px;
        overflow:hidden;    /* crop the background image */
    }
    .SeedBannerBackground {
        position:absolute;
        top:0;left:0;
        z-index:0;

        background: url(${dir_seeds2014}/seeds2014img/h01_${tpl['sTheme']}.jpg) right top no-repeat;
        min-height:120px;
        min-width:1000px;  /* so the logo always sits on the faded left-hand part of the backgrounds (needs a container to crop this) */
        width:100%;
    }
    .SeedBannerLogo {
        position:absolute;
        top:0;left:0;
        z-index:1;
    }




    #block-book-navigation {
        margin-top: 10px;
        background-color:#{$tpl['rgbLight']};
    }
    #block-book-navigation h2 {
        font-size:14px; font-weight:bold;
    }
    #block-book-navigation ul li {
        font-size:12px;
    }
    #block-book-navigation ul li a {
        padding-bottom:0px;padding-right:0px;  /* override .nav>li>a padding to tighten the book menu items */
    }


    /* Search results put the author and date in div.search-info - this hides it
     * (though it's still in html; you can get rid by copying search-results.php and modifying)
     */
    .search-info { display:none; }

    .SeedSheet {
        background-color:#fff;
//        margin: 0px 5%;
//        margin: 0px 10px 10px 10px;    /* this makes the coloured border, using the background-color of .SeedSheet */
//        background-color:#{$tpl['rgbMed']};    /* the margin of SeedSheetBody becomes a border of this colour */
    }
    .SeedSidebar {
//        background-color:#{$tpl['rgbLight']};
//        border: 1px solid #{$tpl['rgbMed']};
//        border-radius: 10px;
        margin: 10px 10px 10px 10px;    /* bootstrap puts 15px margin on each column group, so 30px between content section and sidebar. This makes it 15px */
        padding: 10px;
    }

    #SeedBody {
        margin-top: 10px;
    }

    .SeedContentBox {
        padding:10px 10px 10px 10px;
        background-color:#{$tpl['rgbLight']};    /* background-color:#d6d9c6; */
        min-height:520px;
        ".($tpl['sTheme'] == "blue" ? "" : "border-radius:10px;")."
    }


/* Extra Small Devices, Phones */
@media only screen and (min-width : 480px) {

}
/* Small Devices, Tablets */
@media only screen and (max-width : 768px) {

}

/* At xs, the sidebar is at the bottom instead of the right, so put a right margin beside the content box.
 * 768 is the xs/sm threshold
 */
@media only screen and (max-width : 768px) {
    .SeedContentBox { margin-right:10px; }
}

/* At xs and sm, the Icons are at Top instead of Left, so put a left margin beside the content box.
 * Also the blue ContentBox has no border-radius at md and lg, because of the way the icons join it, so make rounded corners at xs and sm.
 * 992 is the sm/md threshold
 */
@media only screen and (max-width : 992px) {
    .SeedContentBox { margin-left:10px; border-radius:10px; }
}



    .SeedWell01 {
        border:1px solid #aaa;border-radius:5px;background-color:#ddd;margin:0 20px;padding:5px;
    }

    body {background-color:#f3f3f3;}

    /* Override Bootstrap's navbar padding. It's 15px all around, and that makes a much thicker menu bar than we want.
     * Also set the menu text colour and weight.
     */
    .navbar-default .navbar-nav > li > a { padding-top:5px; padding-bottom:5px;color:#444;font-weight:bold; }

    .navbar-default { border:0px; }

    /* App-specific styles
     */
    .slsrc_dcvblock_cv { font-weight:bold; }
    .slsrc_dcvblock_companies { margin:0px 0px 10px 30px;font-size:10pt; }

/*
    No, didn't like this. Just use bootstrap's link colour
    .csci_species a { color:#00f; }
*/

.sodbox {padding:10px 20px;background-color:#ddd;border:1px solid #888;border-radius:5px; }


/* Display dropdown menu contents when you hover on a menu item that has a dropdown.
   Only when the menu is visible.

   If you want the top menu item to be clickable, add this:
       $('.dropdown-toggle').click(function() { window.location.href = $(this).attr('href'); return false; });
 */
@media only screen and (min-width : 768px) {
    .dropdown:hover .dropdown-menu {
        display: block;
    }
}

</style>
";

    return( $s );
}


/*
==================================================
=           Bootstrap 3 Media Queries            =
==================================================

min-width means use these rules when view is >= X
max-width means use these rules when view is <= X

==========  Mobile First Method  ==========

// Custom, iPhone Retina
@media only screen and (min-width : 320px) {
}

// Extra Small Devices, Phones
@media only screen and (min-width : 480px) {
}

// Small Devices, Tablets
@media only screen and (min-width : 768px) {
}

// Medium Devices, Desktops
@media only screen and (min-width : 992px) {
}

// Large Devices, Wide Screens
@media only screen and (min-width : 1200px) {
}


==========  Non-Mobile First Method  ==========

// Large Devices, Wide Screens
@media only screen and (max-width : 1200px) {
}

// Medium Devices, Desktops
@media only screen and (max-width : 992px) {
}

// Small Devices, Tablets
@media only screen and (max-width : 768px) {
}

// Extra Small Devices, Phones
@media only screen and (max-width : 480px) {
}

// Custom, iPhone Retina
@media only screen and (max-width : 320px) {
}

*/


?>
