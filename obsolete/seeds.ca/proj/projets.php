<?
/* Projects home page FR
 */

include_once( "../site.php" );
include_once( PAGE1_TEMPLATE );



$page1parms = array (
                "lang"      => "FR",
                "title"     => "Projets",
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
<h2>Notre Projets</h2>
<BLOCKQUOTE>
<H3><A HREF='tomato/index_fr.php'>Projet tomates canadiennes</A></H3>
<H3><A HREF='gcgc/'>La Grande collection canadienne d’ail (anglais seulement)</A></H3>
<H3><A HREF='poll/'>Pollinisation Canada (anglais seulement)</A></H3>
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
