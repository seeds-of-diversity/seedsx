<?php
    include_once( "../../std.php" );
//echo "PAGE";
    include( "dr_common.php" );

    $sLogo = "http://www.seeds.ca/img/logo/logo04_colour_x_${tpl_sLang}_800.png";
    $sImgUrl = $tpl_sHome."/sites/all/themes/seeds2013/sod_images/";
    $sContentType = @$variables['node']->type;  // this is undefined on the front page (and presumably other places where there isn't a node)

    if( STD_isLocal ) {
        $sStore = $tpl_sHome.($tpl_bEN ? "?q=store" : "?q=magasin");
    } else {
        if( substr($tpl_sHome, 0, 8) == "https://" ) {
            $sStore = $tpl_sHome;
        } else if( substr($tpl_sHome, 0, 7) == "http://" ) {
            $sStore = "https://".substr($tpl_sHome, 7);
        } else {
            $sStore = "https://$tpl_sHome";  // never seen this case actually happen
        }
        $sStore .= ($tpl_bEN ? "/store" : "/magasin");

        // production server: redirect to https for any pages that need it
        if( $sContentType == 'seeds_node_mbr' && @$_SERVER['HTTPS'] != 'on' ) {
            header( "Location: $sStore" );
        }
    }
//kluge because we are not in global scope here so can't use 'global' to get these in function below
$_REQUEST['myurl_tpl_sHome'] = $tpl_sHome;
$_REQUEST['myurl_tpl_bEN'] = $tpl_bEN;


    function myurl( $sLinkEN, $sLinkFR = "" )
    {
        //global $tpl_sHome, $tpl_bEN;
$tpl_sHome = $_REQUEST['myurl_tpl_sHome'];
$tpl_bEN = $_REQUEST['myurl_tpl_bEN'];

        if( !$sLinkFR ) $sLinkFR = $sLinkEN;

        if( STD_isLocal ) {
            $sUrl = $tpl_sHome."?q=".($tpl_bEN ? $sLinkEN : $sLinkFR);
        } else {
            $sUrl = $tpl_sHome."/".($tpl_bEN ? $sLinkEN : $sLinkFR);
        }

        return( $sUrl );
    }

    function myhref( $sLinkEN, $sLinkFR = "" )
    {
        return( "href='".myurl($sLinkEN,$sLinkFR)."'" );
    }

    //$sStore = url( ($tpl_bEN ? "store" : "magasin"), array( "https"=>true, "absolute"=>true ) );
?>

    <div class="PageBackgroundGlare">
      <div class="PageBackgroundGlareImage"></div>
    </div>
    <div class="Main">
      <div class="Sheet">
        <div class="Sheet-tl"></div>
        <div class="Sheet-tr"></div>
        <div class="Sheet-bl"></div>
        <div class="Sheet-br"></div>
        <div class="Sheet-tc"></div>
        <div class="Sheet-bc"></div>
        <div class="Sheet-cl"></div>
        <div class="Sheet-cr"></div>
        <div class="Sheet-cc"></div>
        <div class="Sheet-border" style='min-width:903px'>  <!-- prevent shrinking smaller than table of header icons (873px) plus Sheet-body's margin (15px*2) -->
         <div class="Sheet-body">
          <div id="SeedHeader">
            <div class="logo">
                <div id="logo">
                  <?php echo "<a href='$tpl_sHome' title='".t('Home')."'>"
                            ."<img src='$sLogo' style='' alt='".t('Home')."' />"
                            ."</a>"; ?>
                </div>
            </div>
          </div>
          <div class="cleared"></div>

          <div id='SeedMenuTop'>
          <?php echo render($page['nicemenu1']); ?>
          </div>

          <?php if (!empty($navigation)): ?>
          <div class="nav">
            <div class="l"></div>
            <div class="r"></div>
            <?php echo $navigation; ?>
          </div>
          <?php endif; ?>

          <div class="cleared"></div>

          <div id="SeedBody" style='margin-top:10px'>

            <!--  Icons on left side
            <div id='SeedIconsLeft' style='position:relative;float:left;width:120px;margin-top:-2px'>
            <div style='position:absolute;width:120px;height:120px;top:0;left:0;' onclick='location.replace("sl");'></div>
            <div style='position:absolute;width:120px;height:120px;top:125px;left:0;' onclick='location.replace("pc");'></div>
            </div> -->
            <div id='SeedIconsLeft' style='position:relative;float:left;width:120px;margin-top:-2px'>
            <img src='<?php echo $tpl_sThemeCommonDir."/sod_images/icons01left-${tpl_sTheme}.png" ?>' usemap='#SeedIconsLeftMap'/>
            <map name='SeedIconsLeftMap' id='SeedIconsLeftMap'>
                <area shape='rect' coords='20, 10,100,115' href="<?php echo url($tpl_raThemes['blue'][$tpl_bEN ? 0 : 1]); ?>"/>
                <area shape='rect' coords='20,135,100,240' href="<?php echo url($tpl_raThemes['yellow'][$tpl_bEN ? 0 : 1]); ?>"/>
                <area shape='rect' coords='20,260,100,365' href="<?php echo url($tpl_raThemes['brown'][$tpl_bEN ? 0 : 1]); ?>"/>
                <area shape='rect' coords='20,385,100,490' href="<?php echo url($tpl_raThemes['magenta'][$tpl_bEN ? 0 : 1]); ?>"/>
            </map>
            </div>

            <!--  Stuff on right side  -->
            <div id='SeedSidebarRight' style='float:right;width:150px;'>
                <?php //if (!empty($page['sidebar_first'])) { echo render($page['sidebar_first']); }
                      if (!empty($page['sidebar_second'])) { echo render($page['sidebar_second']); } ?>
                <center>
                <p><a href="<?php echo $sStore; ?>"><img alt="Join Us" src="<?php echo $sImgUrl.($tpl_bEN ? "sb_join_en.png" : "sb_join_fr.png"); ?>" /></a></p>
                </center>
                <center>
                <p><a href="<?php echo $sStore; ?>"><img alt="Donate" src="<?php echo $sImgUrl.($tpl_bEN ? "sb_donate_en.png" : "sb_donate_fr.png"); ?>" /></a></p>
                </center>
                <?php if( !empty($search_box) ): ?>
                    <div id="search-box">
                    <?php echo $search_box; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!--  Main page area  -->
            <div id="SeedBodyMain" style='margin-left:120px;margin-right:150px;min-height:510px;background-color:#d6d9c6;'>
                <?php
                    if( $is_front ) { echo "<div id='featured'></div>"; }

                    if (!empty($banner1)) echo $banner1;
                    // if (!$is_front) echo $breadcrumb;
                    $sTabs = render($tabs); if (!empty($sTabs)) { echo "<ul class='tabs'>$sTabs</ul>"; }
                    if ($title) { echo "<h1 class='title'>$title</h1>"; }
                    if (!empty($page['highlighted'])) { echo '<div id="mission">' . render($page['highlighted']) . '</div>'; }
                    if (!empty($page['help'])) { echo render($page['help']); }
                    if ($action_links) { echo '<ul class="action-links">' . render($action_links) . '</ul>'; }
                    if (!empty($messages)) echo $messages;
                    echo render($page['content']);
                    if (!empty($page['content_bottom'])) echo render($page['content_bottom']);
                ?>
            </div>
            <div class='cleared'></div>


          </div>
          <div class="cleared"></div>
          <div class="Footer">
            <div class="Footer-inner">
<?php
/*              <a href="<?php echo $base_url; ?>/rss.xml" class="rss-tag-icon" title="RSS"></a>
 */
?>
              <div class="Footer-text">
                <?php if (!empty($page['copyright'])) echo render($page['copyright']); ?>
              </div>
            </div>
            <div class="Footer-background"></div>
          </div>
        </div>
       </div>
      </div>
      <div class="cleared"></div>
      <p class="page-footer"><?php /* echo $footer_message; */ ?></p>
    </div>
