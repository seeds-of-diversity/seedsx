<?

// USC Canada
// Kokopelli
// Designate a person to update the links, via an interface.

define( "SITEROOT", "../../" );

include_once( SITEROOT."site.php" );
include_once( PAGE1_TEMPLATE );



$page1parms = array (
                "lang"      => "EN",
                "title"     => "Online Library",
                "tabname"   => "Links",
//              "box1title" => "Box2Title",
//              "box1text"  => "Box2Text",
//              "box1fn"    => "Box1Fn",
//              "box2title" => "Box2Title",
//              "box2text"  => "Box2Text",
//              "box2fn"    => "Box2Fn",

             );



Page1( $page1parms );


function Page1Body() {
?>
<h2>Our Favourite Links</h2>
<p>
<font size="4">Other Seed-Saving Organizations</font></p>
<blockquote>

<p><font size="3">
<a href="http://www.seedsavers.org">Seed Savers Exchange (U.S.A.)</a>
</font></p>
<p><font size="2">
Seed Savers Exchange (SSE), a nonprofit tax-exempt organization that is saving
oldtime food crops from extinction.
SSE's 8000 members are working together to rescue endangered vegetable and
fruit varieties from extinction. These members are maintaining thousands of
heirloom varieties, traditional Indian crops, garden varieties of the
Mennonite and Amish, vegetables dropped from all seed catalogs and outstanding
foreigh varieties. Each year 1000 members use SSE's publications to distribute
such seeds to ensure their survival. SSE has no monetary interest whatsoever
in any of these varieties and wants only to save them for future generations
to enjoy.
</font></p>

<p><font size="3">
<a href="http://www.hdra.org.uk">Henry Doubleday Research Association (U.K.)</a>
</font></p>
<p><font size="2">
HDRA's Heritage Seed Library is one of Europe's largest non-government
 genetic conservation bodies. The library contains over 700 varieties of
 interesting and traditional vegetables that can't legally be traded in
 Europe. Many are family heirlooms nurtured from generation to
 generation; others were once commercial varieties, now no longer
 offered by seed companies, the seeds being victims of commercial
 pressures and draconian European rules. Members of the library are able
 to receive up to seven packets of free seed each year.
</font></p>

<!--
<p><font size="3">
<a href="http://www.ozemail.com.au/~hsca">Heritage Seeds Curators Australia</a>
</font></p>
<p><font size="2">
The HSCA is a non-profit incorporated organization, formed in 1992. We are a
"Seed Saving" organization of curators, supporters and associated organizations, who
want to preserve Australia's horticultural and garden heritage. Curators are people who are
committed to maintaining rare varieties of vegetables, fruits, tubers or flowers. The
association is managed by an elected committee of 6 curator members.
</font></p>
-->

</blockquote>

<br>

<!-- ==================================================================== -->

<p>
<font size="4">Related Organizations</font></p>
<blockquote>

<p><font size="3">
<img src="<?= SITEIMG ?>canleaf.gif" width="20"><a href="http://www.cog.ca">Canadian Organic Growers</a>
</font></p>
<p><font size="2">
COG is a national information network for organic farmers, gardeners and
consumers. Founded in 1975, we are a federally incorporated registered charity.
Our mandate is to be a leading organic information and networking resource for
Canada, promoting the methods and techniques of organic growing along with the
associated environmental, health and social benefits.
</font></p>

<p><font size="3">
<img src="<?= SITEIMG ?>canleaf.gif" width="20"><a href="http://www.rarebreedscanada.ca">Rare Breeds Canada</a>
</font></p>
<p><font size="2">
A federally incorporated, charitable organization dedicated to the
conservation, evaluation and study of rare, endangered and minority breeds of
livestock and poultry.
</font></p>

</blockquote>

<br>

<!-- ==================================================================== -->

<p>
<font size="4">Gardening Web Sites and On-line Seed Exchanges</font></p>
<blockquote>

<p><font size="3">
<img src="<?= SITEIMG ?>canleaf.gif" width="20"><a href="http://www.icangarden.com">ICanGarden</a>
</font></p>
<p><font size="2">
</font></p>

<p><font size="3">
<a href="http://www.gardenweb.com">GardenWeb</a>
</font></p>
<p><font size="2">
</font></p>

</blockquote>

<!-- ==================================================================== -->

<p>
<font size="4">Seed Company and Nursery Catalogues</font></p>
<blockquote>

<p><font size="3">
<img src="<?= SITEIMG ?>canleaf.gif" width="20"><a href="<?= SITEROOT ?>rl/rl.php">Seeds of Diversity's Resource List</a>
</font></p>
<p><font size="2">
We maintain a list of seed companies and nurseries which specialize in heritage seeds, hard-to-find varieties,
and sustainable agricultural and horticultural practices.  These are recommendations from our members, and we hope
you will find them useful.
</font></p>

<p><font size="3">
<a href="http://www.gardenlist.com">Cyndi's Catalog of Garden Catalogs</a>
</font></p>
<p><font size="2">
This is the most comprehensive list of mail-order garden catalogues that we know of.
</font></p>

</blockquote>
<?
}
