<?
/* Projects home page
 */

include_once( "../site.php" );
include_once( PAGE1_TEMPLATE );



$page1parms = array (
                "lang"      => "EN",
                "title"     => "Projects",
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
<h2>Our Projects</h2>
<BLOCKQUOTE>
<H3><A HREF='tomato/'>Canadian Tomato Project</A></H3>
<H3><A HREF='gcgc/'>Great Canadian Garlic Collection</A></H3>
<H3><A HREF='poll/'>Pollination Canada</A></H3>
<H3><A HREF='http://www.seeds.ca/ecosgn/'>Eastern Canadian Organic Seed Growers Network</A></H3>
<?  // Change this to www.seeds.ca/ecosgn once that's the root of the project
    // Also change organic-seed-growers.ca 
?>
</BLOCKQUOTE>
<?
}


function box1fn() {
    echo "<div><a href='".SITEROOT."about/faq.htm'>Frequently Asked Questions</a></div>";
}


function box2fn() {
    echo "<div><a href='mailto:mail@seeds.ca'>Email</a></div>";
}

?>
