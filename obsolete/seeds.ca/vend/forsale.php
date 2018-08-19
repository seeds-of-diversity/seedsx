<?
/* Main english page of items for sale
 */

include_once( "../site.php" );
include_once( PAGE1_ROOT."page1.php" );
include_once( "_vend.php" );



$page1parms = array (
                "lang"      => "EN",
                "title"     => "Items For Sale",
                "tabname"   => "VEND",
//              "box1title" => "Box1Title",
//              "box1text"  => "Box2Text",
//              "box1fn"    => "Box1Fn",
//              "box2title" => "Box2Title",
//              "box2text"  => "Box2Text",
//              "box2fn"    => "Box2Fn",

             );


define( "VEND_GO_TO_ORDER_FORM", "<A class='vend_orderform_link' HREF='".SITEROOT."mbr/member.php'>Go to the order form</A>" );

Page1( $page1parms );


function Page1Body() {

vend_style();

?>
<h2>Items For Sale</h2>
<p>Seeds of Diversity offers these publications for the enjoyment
of members and non-members alike.
All proceeds are used to help fund our worthwhile
preservation and education projects.</p>
<p><b>All prices in are in Canadian dollars and include postage, handling and
all applicable taxes</b></p>
<p>Please direct questions to <?= SEEDStd_EmailAddress( "office", "seeds.ca" ); ?> or
1-866-509-SEED(7333).  Wholesale inquiries welcome.</p>

<p><?= VEND_GO_TO_ORDER_FORM; ?></p>


<table class='vend_top_array'><tr>
<td><a href="#ssh_e"><img src="<?= SITEIMG ?>vend/ssh6en150.jpg" width="60" height="75"></a></td>
<td><a href="#ssh_f"><img src="<?= SITEIMG ?>vend/ssh6fr150.jpg" width="60" height="75"></a></td>
<td><a href="#every_seed"><img src="<?= SITEIMG ?>vend/EverySeed150.png" width="60" height="75"></a></td>
<td><a href="#suechan2012"><img src="http://www.pollinationcanada.ca/?n=pc_web_image_root/Landowners%20title.jpg" width="60" height="75"></a></td>
<td><a href="#kent2012"><img src="http://www.pollinationcanada.ca/?n=pc_web_image_root/ClementKentPollinatorGardenBook%20small%20for%20web.jpg" width="60" height="75"></a></td>

<!--
<td><a href="#ssh_f"><img src="<?= SITEIMG ?>vend/ssh_f_cv.gif" width="60" height="75"></a></td>
<td><a href="#niche1"><img src="<?= SITEIMG ?>vend/niche1_cv.gif" width="60" height="75"></a></td>
<td><a href="#niche2"><img src="<?= SITEIMG ?>vend/niche2_cv.gif" width="60" height="75"></a></td>
<td><a href="#notecards"><img src="<?= SITEIMG ?>vend/cd_pansx.jpg" width="60" height="75"></a></td>
-->

<td><a href="#backissues"><img src="<?= SITEIMG ?>vend/mag.gif" width="60" height="75"></a></td>
</tr>
<tr>
<td width="90">How to Save Your Own Seeds <span style='color:red'>NEW!</span></td>
<td width="90">La conservation des semences <span style='color:red'>NEW!</span></td>
<td width="90">Every Seed Tells a Tale</td>
<td width="90">Conserving Native Pollinators in Ontario</td>
<td width="90">How to Make a Pollinator Garden</td>


<!--
<td width="90">La conservation des semences du patrimoine</td>
<td width="90">Niche Market Development</td>
<td width="90">Selling Heritage Crops</td>
<td width="90">Heritage Notecards</td>
-->
<td width="90">Back Issues of Seeds of Diversity Magazine</td>
</tr></table>


<!-- *********** SEED SAVING HANDBOOK *********** -->
<a name="ssh_e"/>
<?php
vend_ssh_e();
?>

<!-- *********** EVERY SEED *********** -->
<a name="every_seed">
<?php
vend_every_seed();
?>

<!-- *********** FRENCH SEED-SAVING MANUAL *********** -->
<a name="ssh_f">
<?php
vend_ssh_f();
?>

<!-- *********** NICHE MARKET DEVELOPMENT *********** -->
<?php
//<a name="niche1">
// vend_niche1();
?>

<!-- *********** SELLING HERITAGE CROPS *********** -->
<?php
//<a name="niche2">
// vend_niche2();
?>

<!-- *********** HERITAGE NOTECARDS *********** -->
<? /*
<a name="notecards">
</a><h2><font color="007700"><b>Heritage Notecards</b></font></h2>
<table align="left" cellspacing="20" width="240">
<tr>
<td valign="top" width="80"><a href="cd_pans.jpg"><img src="<?= SITEIMG ?>vend/cd_pansx.jpg" width="60" height="80" alt="Pansies Notecard"><br><font size="-1" face="Arial,Helvetica"><b>Pansies</b></font></a></td>
<td valign="top" width="80"><a href="cd_veg.jpg"><img src="<?= SITEIMG ?>vend/cd_vegx.jpg" width="60" height="80" alt="Vegetables Notecard"><br><font size="-1" face="Arial,Helvetica"><b>Vegetables</b></font></a></td>
<td valign="top" width="80"><a href="cd_iris.jpg"><img src="<?= SITEIMG ?>vend/cd_irisx.jpg" width="60" height="80" alt="Irises Notecard"><br><font size="-1" face="Arial,Helvetica"><b>Irises</b></font></a></td>
</tr><tr>
<td valign="top" width="80"><a href="cd_morn.jpg"><img src="<?= SITEIMG ?>vend/cd_mornx.jpg" width="60" height="80" alt="Morning Glories Notecard"><br><font size="-1" face="Arial,Helvetica"><b>Morning Glories</b></font></a></td>
<td valign="top" width="80"><a href="cd_tulp.jpg"><img src="<?= SITEIMG ?>vend/cd_tulpx.jpg" width="60" height="80" alt="Tulips Notecard"><br><font size="-1" face="Arial,Helvetica"><b>Tulips</b></font></a></td>
<td valign="top" width="80"><a href="cd_mayb.jpg"><img src="<?= SITEIMG ?>vend/cd_maybx.jpg" width="60" height="80" alt="May Berries Notecard"><br><font size="-1" face="Arial,Helvetica"><b>May Berries</b></font></a></td>
</tr>
<tr><td colspan="3">
<font size="-1" face="Arial,Helvetica">
<b>5" x 7" blank inside<br>
$1.00 each incl postage (minimum order 5)<br>
$0.50 each for orders of 50 or more (any assortment)<br>
Envelopes are included.<br>
<?= VEND_GO_TO_ORDER_FORM; ?>
</b></font>
</td></tr></table>

<p>
By the mid-1880s a profusion of seed catalogues offered the gardener a fantasy
of colours, tastes, and shapes. They not only depicted the products for sale,
but were works of art in their own right.
</p><p>
Seeds of Diversity is happy to share these beautiful images with plant lovers
today. We have taken illustrations from a Victorian scrapbook in the collection of
the Irving House Historic Centre in British Columbia and made them into
5" by 7" notecards. Six designs are available, and they can be ordered in any
assortment.</p>
<p>
The amazing richness of these cards expresses the Victorians' exuberant love
of colour. They are truly beautiful!</p>
<p>
Many varieties in the old seed catalogues have disappeared forever.
Seeds of Diversity Canada helps to ensure that our horticultural heritage
will survive for future generations to enjoy.
</p>


*/ ?>

<!-- *********** POLLINATION *********** -->
<a name="suechan2012">

<DIV style='clear:both' class='sect1'>
<H2>Conserving Native Pollinators in Ontario</H2>
<H4>by Sue Chan</H4>
<table align="left" cellspacing="20"><tr><td><img src="http://www.pollinationcanada.ca/?n=pc_web_image_root/Landowners%20title.jpg" width="150" height="200"></td></tr>
<tr><td class='vend_caption'>
$15.00 each incl postage<br>
<?= VEND_GO_TO_ORDER_FORM; ?>
</td></tr></table>
</DIV>

<a name="kent2012">
<DIV style='clear:both' class='sect1'>

<H2>How to Make a Pollinator Garden</H2>
<H4>by Clement Kent</H4>
<table align="left" cellspacing="20"><tr><td><img src="http://www.pollinationcanada.ca/?n=pc_web_image_root/ClementKentPollinatorGardenBook%20small%20for%20web.jpg" width="150" height="200"></td></tr>
<tr><td class='vend_caption'>
$8.00 each incl postage<br>
<?= VEND_GO_TO_ORDER_FORM; ?>
</td></tr></table>


</DIV>


<!-- *********** BACK ISSUES *********** -->
<a name="backissues">

<DIV style='clear:both' class='sect1'>
<H2>Back Issues of Seeds of Diversity Magazine</H2>
<H3>Issues previous to 1996 are called Heritage Seed Program magazine</H3>
<table align="left" cellspacing="20"><tr><td><img src="<?= SITEIMG ?>vend/mag.gif" width="150" height="200"></td></tr>
<tr><td class='vend_caption'>
$4.00 each incl postage<br>
<?= VEND_GO_TO_ORDER_FORM; ?>
</td></tr></table>

<p>
Back issues are available for all magazines published by Seeds of Diversity and
the Heritage Seed Program from December 1988 to the present
(December 1988 and December 1989 are available only as photocopies).
</p><p>
Check out the <a href="../lib/mag/">
index of articles
</a> by title and author.
</p><p>
As well, the Seeds of Diversity office has a database of articles searchable by
keyword.
Included are such things as kinds of vegetables, fruits, grains, flowers, and
herbs; provinces and countries; names of museums, organizations, and seed
companies; and words such as history, breeding, clonal germplasm, gardening,
disease, genetic preservation, how to, breeding.
</p><p>
If you would like to find out about specific topics which are not apparent
from titles in the index, please send a self-addressed, stamped envelope with
the list of topics you are interested in. We will send you a list of back
issues which match your topics.
</p>
</DIV>
<?
}

?>
