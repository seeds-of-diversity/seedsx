<?
define( "SITEROOT", "../../" );
include_once( SITEROOT."site.php" );
include_once( SITEINC."sitedb.php" );
include_once( PAGE1_TEMPLATE );
include_once( "../_info.php" );


$page1parms = array (
                "lang"      => "EN",
                "title"     => "Our Projects",
                "tabname"   => "ABOUT",
                "box1title" => "More Information",
                "box1fn"    => "box1fn_en",
                "box2title" => "Contact Us",
                "box2fn"    => "box2fn_en"
             );



Page1( $page1parms );


function Page1Body() {
?>
<h2>Our Projects</h2>
<p>
<img src='<?= SITEIMG."dot1.gif" ?>'>
<b>Seeds of Diversity magazine</b>
This 40-page magazine is published three times a year and is included
with membership.  It presents a range of articles on gardening, seeds, heritage vegetables and fruit,
plant science, genetics and history.</p>

<p>
<img src='<?= SITEIMG."dot1.gif" ?>'>
<b>Annual Seed Exchange</b>
Available only to members, our Seed Exchange allows
gardeners like you to access over 1500 different varieties of vegetables, fruit,
grains and ornamental plants in a member-to-member exchange.  2/3 of these varieties are not available from any seed
company in North America!
Our Grower Members conserve and offer these seeds to
ensure that the varieties can be grown, tested and adopted by gardeners throughout Canada.</p>

<p>
<img src='<?= SITEIMG."dot1.gif" ?>'>
<b>Seed-Saving Education</b>
Our handbook <i>How to Save Your Own Seeds</i> contains complete information for saving and storing seeds of
common vegetable crops, and is now available in English and French.  We also offer seed-saving workshops
and demonstrations at several events across the country and throughout the year.  Our educational
slideshow and read-along script is available for members to borrow at no charge, anywhere in Canada.
The slideshow features over
100 beautiful images of heritage vegetables, fruit and ornamentals, and outlines the history of gardening
in Canada, the origins of crop diversity, the importance of a healthy crop gene pool, threats to crop
diversity, and what people are doing to preserve this important resource.  The slideshow also demonstrates
the simple techniques of seed-saving, for a general gardening audience.</p>

<? /*
<p>
<img src='<?= SITEIMG."dot1.gif" ?>'>
<b>Heritage and Ancestral Wheat Collection</b>
With dedicated funding from the W. Garfield Weston Foundation, we maintain a collection of approximately
150 ancestral wheat varieties near Edmonton.  Plans are underway to make these varieties available for
field testing across Canada in 2001.</p>
*/ ?>

<p>
<img src='<?= SITEIMG."dot1.gif" ?>'>
<b>Heritage Plants Database</b>
Seeds of Diversity has undertaken this long-term
project to document plant varieties of regional, cultural, economic and historical significance to Canadians.
Our Heritage Plants Database currently has information on <?= db_query1("SELECT count(*) FROM hvd_pnames") ?> varieties
of cultivated plants, online at no charge.  Over 3/4 of these plants are endangered and need to be grown
and multiplied.  Our intention is to
document their traditional uses, and any valuable characteristics, to encourage gardeners to regrow them and save them
for future generations.</p>

<p>
<img src='<?= SITEIMG."dot1.gif" ?>'>
<b>Canadian Tomato Project</b>
New in 2005.  Seeds of Diversity invites all gardeners to grow a Canadian tomato variety in their garden.  We have
identified over 100 tomato varieties that have a Canadian origin, and we need your help to collect them and test them
across the country.  All of the new tomato varieties during the past five years have been bred in the U.S.; not developed
for Canadian conditions.  Although many Canadian tomatoes are "older", they were bred for our cool spring conditions and
often perform better than the "newer" southern varieties.  Our Canadian Tomato Project encourages gardeners to grow a
Canadian tomato and to record observations on its growth.  We provide free seeds for our members, and observation forms are
free to everyone online.</p>

<p>
<img src='<?= SITEIMG."dot1.gif" ?>'>
<b>Great Canadian Garlic Collection</b>
Canadians deserve to grow and eat great garlic, but more than half of supermarket stock is imported from overseas
and most of our domestic garlic is only one variety ("Music").  Canada could have a vigorous garlic industry, and
gardeners could have many more varieties to choose from, but sources and information are surprisingly scarce.
Seeds of Diversity has accumulated a collection of over sixty varieties, and has recruited eighty volunteers to
grow them across Canada.  Growers will record detailed observations, greatly enhancing our knowledge of
these varieties and their performance in different regions.  You can help!  We want to double the size of the program
next year.  All it takes is about 15 square feet of garden space and a commitment to grow two varieties of garlic for
two years.
</p>

<p>
<img src='<?= SITEIMG."dot1.gif" ?>'>
<B>Cemetery Conservation Gardens</B>
We have installed a heritage seed garden at Mt. Pleasant Cemetery in Toronto to initiate seed conservation in cemeteries.
The Mount Pleasant Heritage Seed Garden was created to celebrate the 175th anniversary of Mount Pleasant Group of
Cemeteries, and opened in 2001 to wide acclaim. It has been a source of inspiration and information to many visitors
since. The infrastructure of gardens and staff already exists in cemeteries, allowing project implementation at minimal
cost, and we’re hoping to joint-venture with other cemeteries to create conservation gardens. Should this program spread,
seed conservation and education would be greatly enhanced.
</p>

<p>
<img src='<?= SITEIMG."dot1.gif" ?>'>
<B>Educational Community Gardens</B>
<p>The award winning Eglinton Park Heritage Community Garden was created a decade ago as a joint venture with the
North Toronto Green Community, and has inspired and educated many of Toronto’s community gardeners. It has changed
pedestrian traffic in the park, been a site for environmental workshops and is run by a team of dedicated volunteers.
Community Gardens are a tremendous resource for seed conservation and education, and we intend to continue providing
seed saving education in community gardens across the country.
</p>

<p>
<img src='<?= SITEIMG."dot1.gif" ?>'>
<B>Seedy Saturdays</B>
Seedy Saturdays are community gatherings where gardeners meet to share and purchase rare seeds and to learn how to
grow them out. SoDC has been closely involved with these events for many years, dozens of which take place each
spring across the country.
</p>

<p>
<img src='<?= SITEIMG."dot1.gif" ?>'>
<b>Pollinator Watch</b>
Canadian gardeners and farmers depend on pollinating insects to turn flowers into fruit.
Over $1.2 billion of Canadian fruits and vegetables such as apples, pears, cucumbers and squash need bees,
wasps, flies and other insects for pollination.  Tragically, wild and domesticated populations of these "pollinators" are
threatened with loss of habitat, uncontrollable parasites and diseases.  A horticultural calamity is nearing, if
we do not begin to conserve our essential pollinating insects and the places where they live.  Seeds of Diversity is
leading the way with a new project aimed at recruiting volunteer observers to record the activities of pollinating insects
throughout Canada.  In partnership with EMAN, the Ecological Monitoring and Networking agency of Environment Canada,
this information will guide scientists in a crucial effort to restore this essential but often ignored natural resource.
</p>

<p>
<img src='<?= SITEIMG."dot1.gif" ?>'>
<b>Systematic Horticultural Description Keys</b>
This technical project is designed to help non-technical gardeners to describe their plants in a systematic way.
A common system of description is a missing link in many horticultural projects and it will revolutionize the ability
of amateur gardeners to contribute toward plant testing and evaluation. With funding from the Ontario Trillium
Foundation, Seeds of Diversity is developing plant description keys that are scientifically designed and easy to use.
These will become a fundamental tool in many of our future projects.
</p>

<p>
<img src='<?= SITEIMG."dot1.gif" ?>'>
<b>Policy Work on Agricultural Genetic Resources</b>
Seeds of Diversity is the NGO representative on the Expert Committee for Plant and Microbial Genetic
Resources, a committee which advises the Canadian government on issues relating to agricultural
genetic resource conservation.  We have also represented Canada in international delegations to
the FAO Commission on Genetic Resources.</p>

<p>
<img src='<?= SITEIMG."dot1.gif" ?>'>
<b>Cooperative Seed Rejuvenation with PGRC</b>
Under a longstanding agreement with Plant Gene Resources of Canada, the federal seed bank centered in Saskatoon, our
members help rejuvenate seed stocks and record information about vegetable seeds and grains from the collection.
PGRC holds over 100,000 accessions of seeds as a public resource and provides samples for researchers and seed
collectors throughout the world. Our members obtain samples of rare seed varieties from the gene bank, grow them
in their gardens and gather fresh seed to be returned to the federal seed bank for long-term storage. Our members
are careful to observe accepted guidelines to assure purity and quality, and are free to keep saved seeds for their
own use, for exchange, or for commercialization.
</p>

<p>
<img src='<?= SITEIMG."dot1.gif" ?>'>
<b>Niche Market Development</b>
It has come to be widely recognised that market forces can play an important role in the conservation
of agricultural and horticultural diversity.  To assist farmers and market gardeners to make
heritage varieties marketable, Seeds of Diversity has conducted two studies of niche market
practices and potential, and published handbooks on business planning for heritage crops and
livestock and techniques for selling heritage crops.  This is an important direction for the organization,
and further efforts will focus on identifying specific cultivars of use in various niche markets
and growing environments in Canada.</p>
<?
}

?>
