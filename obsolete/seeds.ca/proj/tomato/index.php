<?
/* Canadian Tomato Project home page
 */

define( "SITEROOT", "../../" );
include_once( SITEROOT."site.php" );
include_once( PAGE1_ROOT."page1.php" );
include_once( "_ctp.php" );



$page1parms = array (
                "lang"      => "EN",
                "title"     => "Canadian Tomato Project",
                "tabname"   => "Projects",
//              "box1title" => "Canadian Tomato Project",
//              "box1text"  => "Box2Text",
//              "box1fn"    => "Box1Fn",
//              "box2title" => "Box2Title",
//              "box2text"  => "Box2Text",
//              "box2fn"    => "Box2Fn"
             );



Page1( $page1parms );


function Page1Body() {
?>
<H2>Canadian Tomato Project &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <FONT size=-1><A HREF='index_fr.php'>En Français</A></FONT></H2>
<P>Calling all tomato gardeners!</P>
<P>
<P>Help us to grow over 100 varieties of Canadian tomatoes from coast to coast!
<H4>What's a Canadian Tomato?</H4>
<P>
A tomato is Canadian if it was bred in Canada, or if it has been grown in Canada long enough to have "adapted" to our
growing conditions.  We have found over 100 tomatoes that were bred or adapted in Canada.  Many were introduced by Agriculture
Canada between
1890 and 1980, some were developed at Canadian universities and a few were created by individuals through their
own backyard garden crossing and selecting.  We also offer Canadian "citizenship" to family heirloom varieties brought
to Canada by immigrants and grown here for many generations.</P>
<P>
<A href='cdntomatoes.php'>Click here for the list of Canadian tomatoes</A></P>

<H4>Our Project</H4>
<P>
<IMG src='<?= SITEIMG ?>earlirouge4.jpg' align=right height='200'>
Seeds of Diversity's Canadian Tomato Project invites gardeners throughout Canada to grow all known Canadian tomatoes
in a multiyear project with careful documentation.   During the last century over 100 tomato varieties were bred or
adapted to grow well in Canadian growing conditions, but seeds for most of these are difficult to find and information
about them is scattered and incomplete.</P>
<P>
There are no longer any garden tomato breeding programs in Canada.  All new varieties on the market are bred for American gardens
and growing conditions.  Plus, large seed companies don't sell all-Canadian seeds anymore.  Mostly they import seeds from
California, Mexico and Asia.  Our tried and true Canadian varieties are still out there, but who is growing them?</P>

<H4>It's time to rediscover our own Canadian tomatoes!</H4>
<P>
You can help by growing a Canadian tomato variety in your garden.  Learn to save seeds so you can pass them along to other
Canadian gardeners.  Download our free Tomato Information Sheet (see link below) and help us to learn more about the
specific characteristics of each variety across this great country.</P>


<H4>Where Can I Get Seeds?</H4>

<? /*
<P>
Seeds of Diversity members can obtain free Canadian tomato seeds courtesy of Jim Ternier of Prairie Garden Seeds.  Many of
our members offer Canadian tomatoes through our member Seed Exchange.  For information please contact our
<?= BXStd_EmailAddress( "office", "seeds.ca", "office" ) ?>.</P>
*/ ?>

<P>You can buy Canadian tomato seeds from mail-order heritage seed companies throughout Canada.
Check our <A HREF='<?= SITEROOT ?>sl/csci'>Canadian Seed Catalogue Inventory</A> to find sources of specific
varieties.</P>

<DIV style='border:1px solid #333;background-color:#ddd;padding:1em;width:50%'">
Unfortunately we do not have seeds available at this time.
</DIV>



<H4>Tell Us Your Tomato Stories</H4>
<P>Besides growing and propagating, we plan to assemble information about the origins of each cultivar.  If you know of
a tomato with a Canadian connection, please write to us and tell us as much history as you can.</P>
<HR>
<?
CTP_articles();

}


function box1fn() {
    echo "<div><a href='".SITEROOT."about/faq.htm'>Frequently Asked Questions</a></div>";
}


function box2fn() {
    echo "<div><a href='mailto:mail@seeds.ca'>Email</a></div>";
}

?>
