<?
define( "SITEROOT", "../../" );
include_once( SITEROOT."site.php" );
include_once( PAGE1_TEMPLATE );



$page1parms = array (
                "lang"      => "EN",
                "title"     => "Rosabec Tomato",
                "tabname"   => "Library",
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
<H2>Rosabec Tomato</H2>

<P><FONT size=-1>
Jim Ternier<BR>
Prairie Garden Seeds<BR>
Box 118 Cochin, SK S0M 0L0 Canada</FONT></P>


<P>
In the 1960’s and 1970’s Roger Doucet was in charge of a tomato breeding program at Station Provinciale de Recherches
Agricole, St. Hyacinthe, Quebec. The aim of th eprogram was to develop cultivars for fresh eating that grew well in the
Quebec climate and especially tolerated cold nights in June. He released a total of 12 cultivars whose names all end in
"bec" (for Quebec) from 1967 to 1976. I was given a complete set of these cultivars by Raymond Tratt, a Quebec tomato
collector who obtained them from St. Hyacinthe. Mr. Doucet also deposited them all at the Gene Bank (PGRC) in Saskatoon.
In 1972 he began a program to breed "firm" tomatoes for mechanical harvesting for processing tomatoes and released several
cultivars. In 1980 he began to work on disease resistance for greenhouse tomatoes.
</P>
<P>
Quebec consumers are fond of pink tomatoes so two of Doucet’s cultivars are Rosabec (1975) and Canabec Rose (1976).
Slightly earlier, in 1973, MacDonald Agricultural College (McGill University) released MacPink.
</P>
<P>
I am never sure if I should call a tomato red or pink so I was happy to find some information on this subject in
<I>100 Heirloom Tomatoes for the American Garden</I> by Carolyn Male. The colour of the fruit I sdetermined by the colour
of the flesh and the colour of the skin. Most tomatoes have yellow (or yellowish orange) skins but some cultivars have
clear (colourless) skins. It is easy to pull a bit of skin off of a very ripe tomato. If any pulp adheres to the skin
this can be scraped off. Then hold the skin up to a fairly bright white light and the skin colour shows clearly. Red
tomatoes have red flesh and yellow skin, and pink tomatoes have red flesh and clear skin.
</P>
<P>
In 2004 one of the tomatoes I grew was Rosabec. We had good moisture all summer but the summer was unusually cool and
none of my tomatoes ripened on the vine except for a few early, small-fruited varieties. I started my bedding plants
April 27 and set out a dozen Rosabec seedlings on June 25. I harvested the plants in the last week of September, and got
a good yield of medium-sized uniform-green fruits. They ripened well indoors an dwe ate them into November. The largest
fruits were flattened (beefsteak-like) and some had too large a "scar" at the blossom end. There was no problem with
disease, cracking or blossom end rot. The fruits were smooth with some of the larger ones having a few wrinkles. The
flesh colour was ared and the skin clear. The plants were fairly large with normal leaves.
</P>
<P>
Here is what Doucet says about this cultivar:
<BLOCKQUOTE>Rosabec is a determinate cultivar bearing fruits without dark green
shoulders and ripening pink. Named in 1975, it was the first determinate Quebec cultivar with pink fruits. The fruits are
big (210g), a bit variable, from round to slightly flattened, from smooth to less mooth, and less subject to blossom end
rot and growth cracks than other pink cultivars. It is early and well adapted to cold nights in spring. The plant is
vigourous and reaches 80 cm in diameter; the foliage is dark green and covers the fruits well, expecially when planted
close together (45 cm apart in a row). Sometimes this cultivar gives late harvests. Rosabec comes from a cross between
Canabec and a pink breeding line selected from a cross between PI263726 (from the US Department of Agriculture, Geneva, NY)
and an unknown pink Japanese hybrid.</BLOCKQUOTE>
</P>
<P>
Thanks to Raymond Tratt for the information about Roger Doucet’s tomato breeding.
</P>
<P>Reference: <I>100 Heirloom Tomatoes for the American Garden</I> by Carolyn Male, Workman Publishing, New York, 1999.</P>

<?
}


function box1fn() {
    echo "<div><a href='".SITEROOT."about/faq.htm'>Frequently Asked Questions</a></div>";
}


function box2fn() {
    echo "<div><a href='mailto:mail@seeds.ca'>Email</a></div>";
}

?>
