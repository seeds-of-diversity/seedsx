<?
/* Template for a page that uses the Page1 format
 */
define("SITEROOT","../../");
include_once( SITEROOT."site.php" );
include_once( PAGE1_ROOT."page1.php" );



$page1parms = array (
                "lang"      => "EN",
                "title"     => "Canadian Tomato Cultivars",
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
// put this in docrep
    $fp = fopen( "jim.html", "r" );
    fpassthru( $fp );
    fclose( $fp );
}


function box1fn() {
    echo "<div><a href='".SITEROOT."about/faq.htm'>Frequently Asked Questions</a></div>";
}


function box2fn() {
    echo "<div><a href='mailto:mail@seeds.ca'>Email</a></div>";
}

?>
