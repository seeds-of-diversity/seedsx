<?php

$base_path = "./";
$page = array();

function render($a) { echo $a; }


include( "seeds-def-tmp.php" );


// resources accessed by url (e.g. images) are based from the url server root
$dir_theme = SITEROOT."ecosgn/img/";
//echo base_path();echo $base_url;
$dir_seeds2014 = $dir_theme."seeds2014/";

$sLogo = "http://www.seeds.ca/img/logo/logoA_h-".($tpl_bEN ? "en" : "fr")."-750x.png";

$content_column_class = ' class="col-sm-12"';

?>
<style>
    #SeedsLogo {
        margin:4px -11px 0 -11px;     /* navbar has padding 0 15px 0 15px, so this makes it look like 4 4 x 4 */
        background: url(h01_green.jpg) right top no-repeat;
    }
    #block-book-navigation {
        margin-top: 10px;
        background-color:#<?php echo $tpl_rgbLight;?>;
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

    .SeedsSheet {
        margin: 0px 60px;
        background-color:#<?php echo $tpl_rgbMed;?>;

    }
    .SeedsSheetBody {
        background-color:#fff;
        margin: 0px 10px 10px 10px;
    }
    .SeedsBanner {
        background: url(h01_green.jpg) right top no-repeat;
        min-height:120px;
    }
    .SeedsSidebar {
        background-color:#<?php echo $tpl_rgbLight;?>;
        border: 1px solid #<?php echo $tpl_rgbMed;?>;
        border-radius: 10px;
        margin: 10px 10px 10px -15px;    /* bootstrap puts 15px margin on each column group, so 30px between content section and sidebar. This makes it 15px */
        padding: 10px;


    }

    #SeedBody {
        margin: 10px;
    }

    .SeedContentBox {
        padding:10px;
        background-color:#<?php echo $tpl_rgbLight;?>;    /* background-color:#d6d9c6; */
        min-height:520px;
        <?php if( $tpl_sTheme != "blue" ) { echo "border-radius:10px"; } ?>
    }
body {background-color:#f3f3f3;}
    /* Override Bootstrap's navbar padding. It's 15px all around, and that makes a much thicker menu bar than we want.
     * Also set the menu text colour and weight.
     */
    .navbar-nav > li > a { padding-top:5px; padding-bottom:5px;color:#444;font-weight:bold; }


    /* App-specific styles
     */
    .slsrc_dcvblock_cv { font-weight:bold; }
    .slsrc_dcvblock_companies { margin:0px 0px 10px 30px;font-size:10pt; }

    </style>

<div class='SeedsSheet'>
<div class='SeedsSheetBody'>
    <div class='SeedsBanner'>
    <?php echo "<a href='$tpl_pathHome' title='Home'>"
              ."<img height='100' src='$sLogo' style='' alt='Home' />"
              ."</a>";
    ?>
    </div>
</header>

<div class="main-container container-fluid">

  <div class="row">

    <section <?php print $content_column_class; ?>>

      <div id='SeedBody'>
        <div class='SeedContentBox'>
        <?php
            echo $tmpPageContent;
        ?>
        </div>
      </div>
    </section>

  </div>
</div>
<footer class="footer container">
  <div style='font-size:small'>
  <?php
  /* print render($page['footer']); */
  if( $tpl_bEN ) {
      echo "Copyright &copy ".date("Y")." Seeds of Diversity Canada<br/>";

  } else {
      echo "Copyright &copy ".date("Y")." Programme semencier du patrimoine Canada<br/>";
  }
  ?>
  </div>
</footer>

</div> <!-- SeedsSheetBody -->
</div> <!-- SeedsSheet -->


