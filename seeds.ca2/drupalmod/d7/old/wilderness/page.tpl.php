<?php // $Id: page.tpl.php,v 1.1.2.1 2009/07/06 08:03:14 agileware Exp $ ?>
<?php global $base_url; ?>
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
        <div class="Sheet-body">
          <div class="Header">
            <div class="logo">
              <?php if ($logo): ?>
                <div id="logo">
                  <a href="<?php echo $base_path; ?>" title="<?php echo t('Home'); ?>"><img src="<?php echo $logo; ?>" alt="<?php echo t('Home'); ?>" /></a>
                </div>
              <?php endif; ?>
            </div>
            <!-- <?php /* if ($search_box): ?>
              <div id="search-box">
                <?php echo $search_box; ?>
              </div>
            <?php endif; */ ?> -->
          </div>
          <?php if (!empty($navigation)): ?>
            <div class="nav">
              <div class="l"></div>
              <div class="r"></div>
              <?php echo $navigation; ?>
            </div>
          <?php endif; ?>
          <div class="cleared"></div>
          <div class="contentLayout">
            <?php if (!empty($page['sidebar_first'])): ?>
              <div id="sidebar-left" class="sidebar fleft">
                <?php echo render($page['sidebar_first']); ?>
              </div>
            <?php endif; ?>
            <?php if (!empty($page['sidebar_second'])): ?>
              <div id="sidebar-right" class="sidebar fright">
                <?php echo render($page['sidebar_second']); ?>
              </div>
            <?php endif; ?>
            <div id="main">
              <div class="Post">
                <div class="Post-body">
                  <div class="Post-inner">
                    <div class="PostContent">
                      <?php if ($is_front): ?>
                        <div id="featured"></div>
                      <?php endif; ?>
                      <?php if (!empty($banner1)) echo $banner1; ?>
                      <?php /* if (!$is_front) echo $breadcrumb; */ ?>
                      <?php if (!empty($tabs)) echo '<ul class="tabs">' . render($tabs) . '</ul>'; ?>
                      <?php if ($title): ?><h1 class="title"><?php echo $title; ?></h1><?php endif; ?>
                      <?php if (!empty($page['highlighted'])) echo '<div id="mission">' . render($page['highlighted']) . '</div>'; ?>
                      <?php if (!empty($page['help'])) echo render($page['help']); ?>
                      <?php if ($action_links) echo '<ul class="action-links">' . render($action_links) . '</ul>'; ?>
                      <?php if (!empty($messages)) echo $messages; ?>
                      <?php echo render($page['content']); ?>
                      <?php if (!empty($page['content_bottom'])) echo render($page['content_bottom']); ?>
                    </div>
                    <div class="cleared"></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="cleared"></div>
          <div class="Footer">
            <div class="Footer-inner">
              <a href="<?php echo $base_url; ?>/rss.xml" class="rss-tag-icon" title="RSS"></a>
              <div class="Footer-text">
                <?php if (!empty($page['copyright'])) echo render($page['copyright']); ?>
              </div>
            </div>
            <div class="Footer-background"></div>
          </div>
        </div>
      </div>
      <div class="cleared"></div>
<!--      <p class="page-footer"><?php /*echo $footer_message;*/ ?> Theme developed by <a href="http://agileware.net">Agileware Pty Ltd</a></p> -->
    </div>
