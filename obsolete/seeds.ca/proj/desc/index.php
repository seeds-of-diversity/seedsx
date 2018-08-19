<?
/* Template for a page that uses the Page1 format
 */

define( "SITEROOT", "../../" );
include_once( SITEROOT."site.php" );
include_once( PAGE1_TEMPLATE );
include_once( STDINC."DocRep/DocRep.php" );


$page1parms = array (
                "lang"      => "EN",
                "title"     => "Descriptive Keys for Horticultural Crops",
                "tabname"   => "Projects",
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
<H2>Descriptive Keys for Horticultural Crops</H2>
<P>Seeds of Diversity's <I>Descriptive Keys</I> project is designed to help gardeners to describe their plants
in a systematic way.  We have developed observation forms for several common horticultural plants, with multiple choice
questions about the features that distinguish each variety from the others.</P>

<H4>What are they for?</H4>
<P>Our members use the Descriptive Keys to record the characteristics of their plants.  Each year, their observations are added
to our database.  Over time, this accumulated information can help gardeners to identify unknown varieties, locate varieties
that exhibit particular characteristics, and even track down family relationships between similar heritage varieties.</P>
<P>If observations are linked to location, soil type, or horticultural practice, we can learn how each variety
performs under different conditions.  The key is to make each observer's information comparable to the others', by using
a systematic method of describing plant characteristics.</P>

<H4>How did we do that?</H4>
<P>Most food plants have been studied in detail for a long time, and their important characteristics have been
listed and described by scientists.
Botanists have developed descriptive keys to record characteristics such as fruit colour, leaf shape, etc,
but these "descriptors" use complex scientific terms that most gardeners don't know.
We based our Descriptive Keys on the standard characteristics used by the International Plant Genetic Resources
Institute (IPGRI) and Canada's Germplasm Resource Information Network (GRIN-CA).  We selected the characteristics that
are most important for gardeners, and translated the scientific language into plain questions with clear multiple choices.
We also used pictures instead of botanical terms to identify anatomical parts of the plants.  Regular gardeners can use
these Keys, with only a basic familiarity with plants, but the results of their observations are scientifically meaningful,
and compatible with standard international methods of botanical description.</P>

<H4>Why hasn't this information been collected already?</H4>
<P>Plant scientists and horticulturists have described many varieties of plants, but there are so many varieties of
fruits and vegetables that they haven't all been documented yet.  Many rare heritage beans and tomatoes just haven't been
grown and studied by experts.  More than that, the observations on record so far have only been made in a few growing
regions - we don't know how those varieties grow in other areas.  That's why we need lots of people like you to help.</P>

<P>&nbsp;</P>
<P>
A common system of description is a missing link in many horticultural projects.  These Descriptive Keys will
revolutionize the ability of amateur gardeners to contribute toward plant testing and evaluation. Gardeners know the
features that set each variety apart from others; now they can describe those features in a systematic way.</P>

<P>&nbsp;</P>
<P><IMG src='<?= SITEIMG ?>OTF_HORZTL_CLR_4_Microsoft.jpg' width=350 align=right>
The research and development for this project was fully funded by the
<A HREF='http://www.trilliumfoundation.org'>Ontario Trillium Foundation</A>.</P>

<P>&nbsp;</P>
<H4>Download these forms</H4>
<?
    function linkform( $fname, $title )
    {
        echo "<TR><TD>";  DR_link( "form/$fname.pdf",        $title." Observation Form - Expert", "" );
        echo "</TD><TD>"; DR_link( "form/$fname simple.pdf", $title." Observation Form - Basic", "" );
        echo "</TD></TR>";
    }
    echo "<TABLE cellspacing=20>";
    linkform( "apple",    "Apple" );
    linkform( "bean",     "Bean" );
    linkform( "garlic",   "Garlic" );
    linkform( "lettuce",  "Lettuce" );
    linkform( "onion",    "Onion" );
    linkform( "pea",      "Pea" );
    linkform( "pepper",   "Pepper" );
    linkform( "potato",   "Potato" );
    linkform( "squash",   "Squash" );
    linkform( "tomato",   "Tomato" );
    echo "</TABLE>";
?>

<P>&nbsp;</P>
<H4>Online Forms for Direct Input to our Database</H4>
<?
    function linkweb( $fname, $title )
    {
        DR_link( "web/$fname.php", $title." Observation Form", "" );
    }
    linkweb( "apple",    "Apple" );
    linkweb( "bean",     "Bean" );
    linkweb( "garlic",   "Garlic" );
    linkweb( "lettuce",  "Lettuce" );
    linkweb( "onion",    "Onion" );
    linkweb( "pea",      "Pea" );
    linkweb( "pepper",   "Pepper" );
    linkweb( "potato",   "Potato" );
    linkweb( "squash",   "Squash" );
    linkweb( "tomato",   "Tomato" );

}

?>
