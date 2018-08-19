<?
include_once( "../site.php" );
include_once( SITEINC."sitedb.php" );
include_once( PAGE1_TEMPLATE );


function HPD_SpeciesCount()
/**************************
 */
{
    return( db_query1( "SELECT COUNT(*) FROM hvd_species" ) );
}

function HPD_PNamesCount()
/*************************
 */
{
    return( db_query1( "SELECT COUNT(*) FROM hvd_pnames" ) );
}

function HPD_ONamesCount()
/*************************
 */
{
    return( db_query1( "SELECT COUNT(*) FROM hvd_onames" ) );
}

function HPD_SEDCount()
/**********************
 */
{
    return( db_query1( "SELECT COUNT(*) FROM hvd_sodclist" ) );
}

function HPD_CatListCount()
/**************************
 */
{
    return( db_query1( "SELECT COUNT(*) FROM hvd_catlist" ) );
}

function HPD_SourceListCount()
/*****************************
 */
{
    return( db_query1( "SELECT COUNT(*) FROM hvd_sourcelist" ) );
}



$page1parms = array (
                "lang"      => "EN",
                "title"     => "Heritage Plants Database",
                "tabname"   => "HPD",
//              "box1title" => "Box1Title",
//              "box1text"  => "Box2Text",
//              "box1fn"    => "Box1Fn",
//              "box2title" => "Box2Title",
//              "box2text"  => "Box2Text",
//              "box2fn"    => "Box2Fn",

             );



Page1( $page1parms );


function Page1Body() {
?>
<h2>Welcome to Seeds of Diversity's on-line <br>
Heritage Plants Database!</h2>

<p>
In the following pages you will find descriptions, stories, history, cultivation details,
and real gardeners' comments on <font size="+2" color="green"><?= HPD_PNamesCount() ?></font> cultivars of Canadian garden vegetables, fruit, grains and
ornamentals.</p>
<p>
Seeds of Diversity is dedicated to bringing you the most comprehensive information anywhere about Canadian horticultural plants.
This database is absolutely free, open to the public, and continually expanding with more information about Canada's horticultural
heritage.</p>
<p>
Please consider sending a donation (tax receipts available for $20 and more) to help us continue to provide this valuable information
to the public.</p>
<p>
Please take a moment to thank our sponsors and donors.</p>

<br>
<center>
<p><a href="hpd.php"><font size="+3" color="green">Enter Here</font></a></p>
</center>
<br>
<br>

<p>Database contains a total of <B><?= HPD_SEDCount() + HPD_CatListCount() + HPD_SourceListCount() ?></B> records
<br>Number of distinct species: <B><?= HPD_SpeciesCount() ?></B>
<br>Number of distinct cultivars: <B><?= HPD_PNamesCount() ?></B>
<?  /*  <br>Last Updated:  <B>29 December 2007</B>  */  ?> 
</p>

<hr>
<p><font size=5 face='helvetica,arial'>Thanks to our donors and supporters!</font></p>
<table cellspacing=10>
<tr><td align=center>                                                              <img src="credits/logo_sodc.jpg"       border=0><hr></a></td> <td><font face='helvetica,arial' size=2>The volunteers and individual donor members of Seeds of Diversity Canada were the inspiration and founding support of the Heritage Varieties Database.  A great thanks to each and every one of you!</font><br></td></tr>
<tr><td align=center><a href="http://www.metcalffoundation.com" target="hvd_link"> <img src="credits/logo_metcalf.gif"    border=0><hr></a></td> <td><font face='helvetica,arial' size=2>The Metcalf Foundation is providing significant funding toward the Heritage Varieties Database, in addition to their ongoing support of several of our other essential projects such as the annual Seed Exchange Directory.</font><br></td></tr>
<tr><td align=center><a href="http://www.tidescanada.org"       target="hvd_link"> <img src="credits/logo_tides.gif"      border=0><hr></a></td> <td><font face='helvetica,arial' size=2>The Tides Canada Foundation provided significant funding toward the Heritage Varieties Database through a grant from their Happy Planet Fund.</font><br></td></tr>
<tr><td align=center><a href="http://www.icangarden.com"        target="hvd_link"> <img src="credits/logo_icangarden.jpg" border=0><hr></a></td> <td><font face='helvetica,arial' size=2>ICanGarden.com generously donated the proceeds from their Spring 2000 Online Charity Auction</font><br></td></tr>
<tr><td align=center><a href="http://www.hrdc-drhc.gc.ca"       target="hvd_link"> <img src="credits/logo_hrdc.gif"       border=0></a></td>     <td><font face='helvetica,arial' size=2>HRDC's Summer Career Placement program allowed us to hire a student in the summers of 2000-2001 to lay the groundwork for the Heritage Varieties Database.</font><br></td></tr>
</table>

<P>&nbsp;</P>

<p><font size=5 face='helvetica,arial'>Special thanks to:</font></p>
<table>
<tr><td align=center><a href="http://www.eco-initiatives.qc.ca" target="hvd_link"><img src="credits/logo_ecoi.gif"        border=0><hr></a></td> <td><font face='helvetica,arial' size=2>Eco-Initiatives has been a major contributor to the Heritage Varieties Database content, especially for fruit and vegetable cultivars of historical and cultural importance in Quebec.</font><br></td></tr>
<tr><td align=center><a href="http://www.peaceworks.ca"         target="hvd_link"><img src="credits/logo_peaceworks.gif"  border=0><hr></a></td> <td><font face='helvetica,arial' size=2>Our Technical Experts.  We couldn't have done it without them!</font><br></td></tr>
<tr><td align=center><a href="http://pgrc3.agr.ca"              target="hvd_link"><img src="credits/logo_aafc.gif"        border=0></a></td>     <td><font face='helvetica,arial' size=2>The staff of Plant Gene Resources of Canada, the federal government plant gene bank, have been most helpful and a pleasure to work with.</font><br></td></tr>
</table>
<?
}


function box1fn() {
    echo "<div><a href='".SITEROOT."about/faq.htm'>Frequently Asked Questions</a></div>";
}


function box2fn() {
    echo "<div><a href='mailto:mail@seeds.ca'>Email</a></div>";
}

?>
