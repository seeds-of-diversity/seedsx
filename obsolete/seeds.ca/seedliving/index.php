<?php
// instead of Pay What You Can say "SeedLiving is a free service from Seeds of Diversity, a Canadian charity that... - please consider making a donation to support..."
// put something like that at the bottom of the checkout form with suggested $2 -- click here to donate $2 to Seeds of Diversity

include_once( "sliv_main.php" );

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Welcome to SeedLiving your online venue for buying, selling and swapping open pollinated seeds and live plants.</title>
<meta name="description" content="SeedLiving was created so that at some point in the future everyone with access to the internet can make a living or supplement their income from seeds, while, at the same time, promoting and enhancing biodiversity on our planet. The goal of SeedLiving and of buying, selling and swapping seeds is to augment biodiversity and to foster sustainable living. There is no reason why making money and living sustainably cannot go hand in hand. Seeds are easy to package and can be sent in the mail. If you have access to a great garden you can buy, sell and swap seeds and live plants online. If you have access to endangered seeds you can sell them to another gardener or farmer across the country. If you are a small family farm with some fabulous heirloom seeds you can easily list all your items for free and reach millions of new growers. SeedLiving is the first and only website of its kind entirely devoted to buying, selling and trading seeds and live plants. SeedLiving is your chance to help the planet and supplement your income." />
<meta name="keywords" content="seeds,sell,buy,swap,vegetables,flowers,herbs,fruits,cover crops,grasses,live plants,bulbs,certified orgranic" />
<meta name="owner" content="Seeds of Diversity Canada" />
<meta name="copyright" content="Copyright 2013" />
<meta name="expires" content="never" />
<meta name="distribution" content="global" />
<meta name="revisit-after" content="30 days" />
<meta name="robots" content="index, follow" />
<meta name="language" content="english" />
<meta name="rating" content="general" />
<meta http-equiv="imagetoolbar" content="no" />
<link rel="stylesheet" href="c/print.css" type="text/css" media="print" />
<link rel="stylesheet" href="c/screen.css" type="text/css" media="screen, projection" />
<!--[if lte IE 7]>
<style type="text/css" media="all">
@import url("c/lib/ie.css");
</style>
<![endif]-->
<script type="text/javascript" src="s/jquery-1.4.2.js"></script>
<script type="text/javascript" src="s/jquery-ui-1.8rc3.custom.min.js"></script>
<script type="text/javascript" src="s/jquery.tablesorter.min.js"></script>
<script type="text/javascript" src="s/jquery.cmxforms.js"></script>
<script type="text/javascript" src="s/site.js"></script>
<script type="text/javascript">

  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-18689937-1']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();

</script>
</head>
<body>
<div id="fb-root"></div>
<script>(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = "//connect.facebook.net/en_US/all.js#xfbml=1";
  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));</script>
<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
<div id="wrapper">
    <div id="slTop">
        <!--#include virtual="sl.php?overlord=slLoadBasket"-->
        <!--#include virtual="sl.php?overlord=slLoadUser"-->
    </div>
    <div id="slHeader">
        <div id="slHeaderImg"><script type="text/javascript">document.write("<img src='i/"+Math.floor(Math.random()*6)+".jpg' width='385px'/>");</script></div>
        <div id="slHeaderMinorWrapper">
            <div id="slHeaderMenu">
                <ul>
                    <!--#include virtual="sl.php?overlord=slCheckLogin"-->
                    <li><a href="sl2/buy/">buy</a></li>
                    <li><a href="sl2/sell/">sell</a></li>
                    <li><a href="sl2/tradetable/">swap</a></li>
                    <li><a href="sl2/community/" class="last">community</a></li>
                 </ul>
            </div>
            <div id="slHeaderSlogan"><a href="#"><img src="i/slogan.png" /></a></div>
            <div id="slHeaderSearch">
            <form action="sl2/searchall/" method="post">
                <input type="text" class="text" value="search" id="generalSearch" name="@search" />
                <button alt="Click to search" title="Click to search"></button>
            </form>
            </div>
        </div>
    </div>
    <div id="slCategory">
        <ul>
            <li class="first"><a class="first" href="sl2/beeproduct/">Bee products</a></li>
            <li><a href="sl2/bulbs/">Bulbs</a></li>
            <li><a href="sl2/certifiedorganic/">Certified organic</a></li>
            <li><a href="sl2/flowers/">Flower seeds</a></li>
            <li><a href="sl2/fruits/">Fruit seeds</a></li>
            <li><a href="sl2/grasses/">Grass seeds</a></li>
            <li><a href="sl2/herbs/">Herb seeds</a></li><br />
            <li><a href="sl2/liveplants/">Live plants</a></li>
            <li><a href="sl2/naturalfibreproduct/">Natural fibre products</a></li>
            <li><a href="sl2/potatoes/">Potatoes</a></li>
            <li><a href="sl2/trees/">Trees</a></li>
            <li><a href="sl2/vegetables/">Vegetable seeds</a></li>
            <li><a href="sl2/woodproduct/">Wood products</a></li>
            <li><a class="last" href="sl2/usedproducts/">Used Products</a></li>
            <!--<li class="first"><a href="sl2/vegetables/">vegetables</a></li>
            <li><a href="sl2/flowers/">flowers</a></li>
            <li><a href="sl2/herbs/">herbs</a></li>
            <li><a href="sl2/fruits/">fruits</a></li>
            <li><a href="sl2/covercrops/">cover crops</a></li>
            <li><a href="sl2/grasses/">grasses</a></li>
            <li><a href="sl2/liveplants/">live plants</a></li>
            <li><a href="sl2/trees/">trees</a></li>
            <li><a href="sl2/bulbs/">bulbs</a></li>
            <li><a href="sl2/certifiedorganic/">certified organic</a></li>
            <li><a href="sl2/heirloom/" class="last">heirloom</a></li>-->
          </ul>
    </div>
    <div id="slContentWrapper">
        <div id="slContentLeft">
            <!--#include virtual="includes/tags.html"-->
            <br /><br />
            <!--#include virtual="sl.php?overlord=slDonateCheck"-->
             <br /><br /><br />
            <script src="http://widgets.twimg.com/j/2/widget.js"></script>
<script>
new TWTR.Widget({
  version: 2,
  type: 'profile',
  rpp: 3,
  interval: 6000,
  width: 160,
  height: 420,
  theme: {
    shell: {
      background: '#c7c7c7',
      color: '#fcfcfc'
    },
    tweets: {
      background: '#ffffff',
      color: '#5e5e5e',
      links: '#fac905'
    }
  },
  features: {
    scrollbar: false,
    loop: false,
    live: false,
    hashtags: true,
    timestamp: true,
    avatars: false,
    behavior: 'all'
  }
}).render().setUser('SeedLiving').start();
</script>

            <br /><br /><br />
            <iframe src="http://www.facebook.com/plugins/likebox.php?id=122524504451093&amp;width=160&amp;connections=8&amp;stream=false&amp;header=false&amp;height=420" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:160px; height:420px;" allowTransparency="true"></iframe>
            <br /><br /><br />
            <!--#include virtual="includes/links.html"-->
	    <?php $f = fopen("http://www.seeds.ca/seedliving/includes/links.html", "r" ); fpassthru($f); fclose($f); ?>
        </div>
        <div id="slContentRight">
            <div class="slContentLeftHeader"><img src="i/fs.jpg" />
            <div style="float:right;padding-right:2px;">
            <a href="https://twitter.com/share" class="twitter-share-button" data-lang="en">Tweet</a>
    <!--        <a href="/pdf/Seedy Sat YYC 2012.pdf" target="_blank"><img src="i/seedy.png" /></a>&nbsp;   -->
            <a href="http://www.facebook.com/pages/SeedLiving/122524504451093" target="_blank"><img src="i/facebook.png" /></a>&nbsp;
            <a href="http://twitter.com/SeedLiving" target="_blank"><img src="i/twitter.png" /></a>
            </div></div>
            <!--#include virtual="sl.php?overlord=seedsSplash"-->
	        <?php $oSLiv->DrawSeedsSplash();  //$f = fopen("http://www.seeds.ca/seedliving/sl.php?overlord=seedsSplash", "r" ); fpassthru($f); fclose($f); ?>

            <!--<div class="slContentLeftHeader"><img src="i/news_tips.jpg" /></div>-->
           <!--include virtual="sl.php?overlord=newsSplash"-->
            <br />
            <div class="slContentLeftHeader"><img src="i/events.jpg" /></div>
           <!--#include virtual="sl.php?overlord=eventsSplash" -->


        </div>
    </div>
    </div>
    <div id="slFooter">
              <span class="first company">2013 Seeds of Diversity Canada</span>
              <span class="first"><a href="sl2/contact/">Contact</a></span>
              <span><a href="sl2/terms/">Terms of Use</a></span>
              <span><a href="sl2/privacy/">Privacy Policy</a></span>
              <span><a href="sl2/fees/">Fees</a></span>
              <span><a href="sl2/about/">Buy, Sell, Swap Homegrown Products Online</a></span>
           </div>
    </div>
<map name="Map2" id="Map2">
<area shape="rect" coords="4,5,70,39" href="sl2/blog/" alt="Our Blog" />
<area shape="rect" coords="92,7,157,32" href="http://www.facebook.com/pages/SeedLiving/122524504451093" alt="facebook" target="_new"/>
<area shape="rect" coords="260,4,369,41" href="http://www.youtube.com/user/YourSeedLiving" alt="Youtube" target="_new"/>
<area shape="rect"  coords="183,6,249,37" href="http://twitter.com/#!/seedliving" alt="Twitter" target="_new"/>
</map>
<div id="slSocialFooter">
    <img src="i/footer.png" usemap="#Map2"/>
</div>
</body>
</html>
