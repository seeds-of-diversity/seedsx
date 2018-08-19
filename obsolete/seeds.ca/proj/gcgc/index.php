<?
/* Great Canadian Garlic Collection home page
 */

define( "SITEROOT", "../../" );
include_once( SITEROOT."site.php" );
include_once( PAGE1_TEMPLATE );
include_once( "_gcgc.php" );



Page1( $page1parms );


function Page1Body() {
?>
<h2>The Great Canadian Garlic Collection &#153;</h2>
<P>
<IMG src='logo_GCGC2.gif' align=right>
Seeds of Diversity's Great Canadian Garlic Collection is a
national project that explores and documents the many varieties of garlic
grown in Canada.
</P>
<P>
There are well over 100 varieties of garlic that are suited to Canadian
growing conditions. Our goal is to grow as many varieties as possible in
all of Canada's major agricultural areas and to record their success and
characteristics.
The collected information will be posted to our web site for use by all.
</P>
<P>
Garlic grows differently in different climates. Some varieties have
particular colours, shapes or other characteristics in certain areas of the
country, but not in others. For instance, some varieties grow scapes in
the east but not in the west. Some have a purple or red colour when
grown in certain climates, but are white elsewhere. More importantly,
some varieties grow better than others in different regions.
</P>
<P>
We want to find out which kinds of garlic grow best in your area, and this is how you can help.
</P>


<DIV style='border:solid medium red;width:20%;text-align:center;float:center;padding:1em; margin:1em;'>
<A HREF='varieties.php'>List of Varieties</A>
</DIV>

<? /*
<H3>How the Project Works</H3>
<P>
Seeds of Diversity member-volunteers throughout Canada receive free samples of diverse varieties
of garlic each year. They grow each variety for at least two years and fill
out a simple standardized form that records their garlics' characteristics.
</P>
*/ ?>

<H3>More about the Project</H3>
<BLOCKQUOTE>
<P><A HREF='gcgc1.php'>Your Role as a Garlic Grower</A></P>
<P><A HREF='gcgc2.php'>About Garlic</A></P>
<P><A HREF='gcgc3.php'>Growing Garlic</A></P>
</BLOCKQUOTE>

<H3>Resources</H3>
<BLOCKQUOTE>
<?
proj_link( "GCGC Booklet 2007.pdf",
           "Booklet - The Great Canadian Garlic Collection (2nd edition)",
           "The complete 8-page GCGC booklet is ready to download, print and fold.",
           array( "icon"   => SITEIMG_STDIMG."icon-pdf-l.gif",
                  "target" => "_blank",
                  "author" => "Seeds of Diversity",
                  "date"   => "2007" ) );

proj_link( "GCGC Garlic Observation Form 2005.pdf",
           "Garlic Observation Form",
           "Download and fill out this form for each of your varieties.",
           array( "icon"   => SITEIMG_STDIMG."icon-pdf-l.gif",
                  "target" => "_blank",
                  "author" => "Seeds of Diversity",
                  "date"   => "2005" ) );

proj_link( "Formulaire ail 2008.pdf",
           "Formulaire d'observation ail",
           "T&eacute;l&eacute;chargez et compl&eacute;tez cette formulaire pour chacune de vos vari&eacute;t&eacute;s.",
           array( "icon"   => SITEIMG_STDIMG."icon-pdf-l.gif",
                  "target" => "_blank",
                  "author" => "Seeds of Diversity",
                  "date"   => "2008" ) );

proj_link( SITEROOT."lib/articles/2004_03_Dyer_Garlic.htm",
           "Choosing, Growing, Using and Selling Garlic for Small-Scale Growers in Ontario",
           "A 22-page paper on Ontario-based best practices for garlic, prepared for Seeds of Diversity ".
           "with funding from the CanAdapt Small Projects Initiative, a joint program of ".
           "Ontario Agri-Food Industry and Agriculture and Agri-Food Canada, administered by the ".
           "Agricultural Adaptation Council.",
           array( "author" => "Jim Dyer",
                  "date"   => "March 2004",
                  "target" => "_blank" ) );

}

?>
