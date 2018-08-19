<?
/* Template for a page that uses the Page1 format
 */

include_once( "../site.php" );
include_once( PAGE1_TEMPLATE );



$page1parms = array (
                "lang"      => "EN",
                "title"     => "Template Title",
                "tabname"   => "[Template]",
                "box1title" => "Box1Title",
                "box1text"  => "Box2Text",
                "box1fn"    => "Box1Fn",
                "box2title" => "Box2Title",
                "box2text"  => "Box2Text",
                "box2fn"    => "Box2Fn",

             );



Page1( $page1parms );


function Page1Body() {
?>
<h2>Heading</h2>
<P>Paragraph</P>
<?
}


function box1fn() {
    return( "<div><a href='".SITEROOT."about/faq.htm'>Frequently Asked Questions</a></div>" );
}


function box2fn() {
    echo "<div><a href='mailto:mail@seeds.ca'>Email</a></div>";
}

?>
