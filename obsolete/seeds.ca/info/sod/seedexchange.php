<?
define( "SITEROOT", "../../" );
include_once( SITEROOT."site.php" );
include_once( PAGE1_TEMPLATE );
include_once( "../_info.php" );


$page1parms = array (
                "lang"      => "EN",
                "title"     => "Seed Exchange",
                "tabname"   => "ABOUT",
                "box1title" => "More Information",
                "box1fn"    => "box1fn_en",
                "box2title" => "Contact Us",
                "box2fn"    => "box2fn_en"
             );



Page1( $page1parms );


function Page1Body() {
?>
<h2>Seed Exchange</h2>
<p>
At the beginning of each year, Seeds of Diversity publishes its annual member Seed Exchange Directory.
This directory lists seeds and plants of many varieties of vegetables, fruit, flowers, herbs and grains,
which individual members offer to other members.
</p><p>
Below is a sample of the over 1500 kinds of plants and seeds that Seeds of Diversity members offer through our Seed Exchange.
</p>
<p>
If you are interested in receiving the next Seed Exchange Directory and obtaining any of these varieties,
please join Seeds of Diversity by sending in the <a href="<?= MBR_FORM_URL_EN ?>">membership form</a>.
</p><p>
<b>
<font size="3" color="005500">
Note: You must be a member of Seeds of Diversity to participate in the Seed Exchange and get these seeds.
</font>
</b>
</p> <br> <hr> <h3>
Sample Index of the annual Seed Exchange Directory:
</h3>


<table>
<tr valign="top">
<td><h3>
<a name="Flowers">
</a>Flowers and Wildflowers
</h3></td>
<td><table cellspacing="0" cellpadding="5">
<tr>
<td width="150">Allium</td>
<td width="150">Amaranthus</td>
<td width="150">Anemone </td>
</tr>
<tr>
<td>Anthemis</td>
<td>Aster</td>
<td>Bedstraw</td>
</tr>
<tr>
<td>Bee Balm </td>
<td>Bishop's Flower or Laceflower </td>
<td>Blue-Eyed Grass </td>
</tr>
<tr>
<td>Bupleurum</td>
<td>Calendula</td>
<td>California Poppy </td>
</tr>
<tr>
<td>Campanula</td>
<td>Castor Bean </td>
<td>Centaurea</td>
</tr>
<tr>
<td>Clematis</td>
<td>Cleome</td>
<td>Columbine</td>
</tr>
<tr>
<td>Coneflower</td>
<td>Cosmos</td>
<td>Crown Vetch</td>
</tr>
<tr>
<td>Daisy</td>
<td>Dames Rocket</td>
<td>Datura </td>
</tr>
<tr>
<td>Delphinium</td>
<td>False Indigo </td>
<td>Farewell-to-Spring (Double)</td>
</tr>
<tr>
<td>Flax</td>
<td>Forget-me-not</td>
<td>Four O'clocks</td>
</tr>
<tr>
<td>Foxglove</td>
<td>Fritillaria</td>
<td>Globe Thistle</td>
</tr>
<tr>
<td>Gromwell</td>
<td>Heuchera</td>
<td>Hollyhock</td>
</tr>
<tr>
<td>Impatiens</td>
<td>Inula</td>
<td>Job's Tears</td>
</tr>
<tr>
<td>Joshua's Trumpet</td>
<td>Knautia</td>
<td>Lamb's Ears</td>
</tr>
<tr>
<td>Larkspur</td>
<td>Lavatera</td>
<td>Lily</td>
</tr>
<tr>
<td>Lily of the Valley</td>
<td>Love in a Mist</td>
<td>Lupins</td>
</tr>
<tr>
<td>Mallow</td>
<td>Maltese Cross</td>
<td>Marigold</td>
</tr>
<tr>
<td>Meadow Foam</td>
<td>Mignonette</td>
<td>Morning Glory</td>
</tr>
<tr>
<td>Mullein</td>
<td>Nicotiana</td>
<td>Pasque Flower </td>
</tr>
<tr>
<td>Petunia</td>
<td>Phlox</td>
<td>Pinks</td>
</tr>
<tr>
<td>Poppy</td>
<td>Prairie Mallow </td>
<td>Pussytoes </td>
</tr>
<tr>
<td>Pyrethrum Daisy</td>
<td>Ribbon Grass</td>
<td>Rose</td>
</tr>
<tr>
<td>Rose Campion </td>
<td>Rudbeckia</td>
<td>Salvia</td>
</tr>
<tr>
<td>Sea Holly</td>
<td>Shoo-fly Plant</td>
<td>Silver Dollar </td>
</tr>
<tr>
<td>Snapdragon</td>
<td>Snowy Woodrush</td>
<td>Soapwort </td>
</tr>
<tr>
<td>Stock</td>
<td>Sunflower</td>
<td>Sweet Pea </td>
</tr>
<tr>
<td>Sweet William </td>
<td>Teasel</td>
<td>Tithonia</td>
</tr>
<tr>
<td>Viola</td>
<td>Wild Cucumber Vine</td>
<td>Yarrow</td>
</tr>
<tr>
<td>Zinnia</td>
</tr>
</table> <hr></td>
</tr>
<tr valign="top">
<td><h3>
<a name="Fruits">
</a>Fruits
</h3></td>
<td><table cellspacing="0" cellpadding="5">
<tr>
<td width="150">Cherry</td>
<td width="150">Fig</td>
<td width="150">Garden Huckleberry</td>
</tr>
<tr>
<td>Melon/Honeydew or Casaba</td>
<td>Melon/Muskmelon</td>
<td>Melon/Other</td>
</tr>
<tr>
<td>Rhubarb</td>
<td>Strawberry</td>
<td><a href="#WATERMELON">
Watermelon
</a></td>
</tr>
</table> <hr></td>
</tr>
<tr valign="top">
<td><h3>
<a name="Grains">
</a>Grains
</h3></td>
<td><table cellspacing="0" cellpadding="5">
<tr>
<td width="150">Barley</td>
<td width="150">Buckwheat</td>
<td width="150">Flax</td>
</tr>
<tr>
<td>Millet</td>
<td>Oats</td>
<td>Quince</td>
</tr>
<tr>
<td>Rye</td>
<td>Sorghum</td>
<td>Wheat </td>
</tr>
</table> <hr></td>
</tr>
<tr valign="top">
<td><h3>
<a name="Herbs">
Herbs
</a>
</h3></td>
<td><table cellspacing="0" cellpadding="5">
<tr>
<td width="150">Angelica</td>
<td width="150">Anise </td>
<td width="150">Anise Hyssop</td>
</tr>
<tr>
<td>Arugula </td>
<td>Basil</td>
<td>Borage</td>
</tr>
<tr>
<td>Caraway </td>
<td>Catnip </td>
<td>Celandine</td>
</tr>
<tr>
<td>Chamomile</td>
<td>Chervil</td>
<td>Chicory</td>
</tr>
<tr>
<td>Chives</td>
<td>Clary Sage</td>
<td>Comfrey</td>
</tr>
<tr>
<td>Coriander/Cilantro </td>
<td>Dill </td>
<td>Dyer's Broom </td>
</tr>
<tr>
<td>Elecampane</td>
<td>Epazote </td>
<td>Evening Primrose </td>
</tr>
<tr>
<td>Fennel</td>
<td>Fenugreek</td>
<td>Feverfew</td>
</tr>
<tr>
<td>Germander</td>
<td>Good King Henry</td>
<td>Goutweed</td>
</tr>
<tr>
<td>Hops</td>
<td>Horehound</td>
<td>Horseradish </td>
</tr>
<tr>
<td>Hyssop</td>
<td>Kasuri Methi</td>
<td>Lemon Balm </td>
</tr>
<tr>
<td>Lovage </td>
<td>Marjoram</td>
<td>Motherwort</td>
</tr>
<tr>
<td>Nigella</td>
<td>Oregano</td>
<td>Pennyroyal</td>
</tr>
<tr>
<td>Psyllium</td>
<td>Queen of the Meadow</td>
<td>Rue</td>
</tr>
<tr>
<td>Sage</td>
<td>Salad Burnet</td>
<td>Self-heal</td>
</tr>
<tr>
<td>Skullcap </td>
<td>Sorrel</td>
<td>Sweet Annie</td>
</tr>
<tr>
<td>Tobacco </td>
<td>Valerian </td>
<td>Woad</td>
</tr>
<tr>
<td>Wood Betony</td>
</tr>
</table> <hr></td>
</tr>
<tr valign="top">
<td><h3>
<a name="Vegetables">
</a>Vegetables
</h3></td>
<td><table cellspacing="0" cellpadding="5">
<tr>
<td width="150">Amaranth (Leaf and Grain) </td>
<td width="150">Asparagus </td>
<td width="150"><a href="#BEAN/DRY/BUSH">
Bean/Dry/Bush
</a></td>
</tr>
<tr>
<td><a href="#BEAN/DRY/POLE">
Bean/Dry/Pole
</a> </td>
<td>Bean/ Fava (Broad)</td>
<td>Bean/Lima/Bush</td>
</tr>
<tr>
<td><a href="#BEAN/RUNNER">
Bean/Runner
</a> </td>
<td><a href="#BEAN/SNAP/BUSH">
Bean/Snap/Bush
</a></td>
<td><a href="#BEAN/SNAP/POLE">
Bean/Snap/Pole
</a> </td>
</tr>
<tr>
<td><a href="#BEAN/SOY">
Bean/Soy
</a> </td>
<td>Bean/Wax/Bush </td>
<td>Bean/Wax/Pole</td>
</tr>
<tr>
<td>Bean/Other</td>
<td>Beet </td>
<td>Broccoli </td>
</tr>
<tr>
<td>Broccoli Raab</td>
<td>Cabbage </td>
<td>Cabbage/Chinese </td>
</tr>
<tr>
<td><a href="#CARROT">
Carrot
</a> </td>
<td>Celery</td>
<td>Corn/Dent </td>
</tr>
<tr>
<td><a href="#CORN/FLOUR">
Corn/Flour
</a></td>
<td>Corn/Pop</td>
<td><a href="#CORN/SWEET">
Corn/Sweet
</a> </td>
</tr>
<tr>
<td>Cress </td>
<td>Cucumber/Pickling</td>
<td>Cucumber/Slicing</td>
</tr>
<tr>
<td>Eggplant</td>
<td>Gourd</td>
<td>Ground Cherry</td>
</tr>
<tr>
<td>Jerusalem Artichoke</td>
<td>Kale </td>
<td>Lettuce/Head </td>
</tr>
<tr>
<td><a href="#LETTUCE/LEAF">
Lettuce/Leaf
</a></td>
<td>Lettuce/Romaine</td>
<td>Mustard/Greens</td>
</tr>
<tr>
<td><a href="#ONION/GARLIC">
Onion/Garlic
</a></td>
<td>Onion/Leek </td>
<td>Onion/Multiplier/Root </td>
</tr>
<tr>
<td>Onion/Multiplier/Top</td>
<td>Onion/Yellow</td>
<td>Parsley</td>
</tr>
<tr>
<td><a href="#PEA">
Pea
</a></td>
<td><a href="#PEA/EDIBLE_PODDED">
Pea/Edible Podded
</a></td>
<td><a href="#PEPPER/HOT">
Pepper/Hot
</a></td>
</tr>
<tr>
<td>Pepper/Sweet </td>
<td><a href="#POTATO">
Potato
</a></td>
<td>Radish</td>
</tr>
<tr>
<td>Rutabaga </td>
<td>Spinach </td>
<td><a href="#SQUASH_C_MAXIMA">
Squash (Cucurbita maxima)
</a></td>
</tr>
<tr>
<td>Squash (C. mixta) </td>
<td>Squash (C. moschata)</td>
<td>Squash (C. pepo)</td>
</tr>
<tr>
<td>Sweet Potato</td>
<td>Swiss Chard</td>
<td><a href="#TOMATO/PINK">
Tomato/Pink to Purple Skin
</a></td>
</tr>
<tr>
<td><a href="#TOMATO/RED">
Tomato/Red Skin
</a></td>
<td><a href="#TOMATO/YELLOW">
Tomato/Yellow to Orange Skin
</a></td>
<td><a href="#TOMATO/OTHER">
Tomato/Other
</a></td>
</tr>
</table> <hr></td>
</tr>
<tr valign="top">
<td><h3>
Miscellaneous Vegetables
</h3></td>
<td><table cellspacing="0" cellpadding="5">
<tr>
<td width="150">Apios</td>
<td width="150">Edible Burdock</td>
<td width="150">New Zealand Spinach</td>
</tr>
<tr>
<td>Orach</td>
<td>Sea Kale</td>
<td>Shiso</td>
</tr>
<tr>
<td>Shungiku</td>
<td>Skirret</td>
<td>Strawberry Spinach</td>
</tr>
</table> <hr></td>
</tr>
<tr valign="top">
<td><h3>
Trees and Shrubs
</h3></td>
<td><table cellspacing="0" cellpadding="5">
<tr>
<td width="150">Currant, Ornamental</td>
<td width="150">Dogwood</td>
<td width="150">Highbush Cranberry</td>
</tr>
<tr>
<td>Horse Chestnut</td>
<td>Sumac</td>
</tr>
</table></td>
</tr>
</table> <br> <hr> <h2>
Some Sample Listings <font size="3">
(There are about 1500 of these!)
</font>
</h2> <br> <table>
<tr valign="top">
<td><h3 align="center">
<a name="BEAN/DRY/BUSH">
</a>Bean/Dry/Bush
</h3></td>
<td><p>
<b>
Black Turtle<br>
</b>100 days to maturity. This small, black seed with white hilum grows 9 to 10 mm long, 4 to 5 mm thick, 5 to 6 mm high. Good producer. Has nutty flavour and is essential for frijoles negros.
</p> <p>
<b>
Magpie<br>
</b>80 days to maturity. Heavy-bearing plant, long pods with 5 to 8 long, slender seeds. Seed is black with fine black speckles fading to white at the other end. Dries well even in wet weather. Cultivated 21 oz. of seed from 16 seeds planted.
</p> <p>
<b>
Norwegian<br>
</b>75 days to maturity. Heirloom. Medium size, upright, strong plant, heavy producing. Pods green, 5', producing 4 to 7 seeds. Can shell out in garden when ripe. Must soak overnight. Cooks quickly. Delicious. Seed is rust colour with small white eye. Grown here since 1957. A family bean given to me by Mrs. R. Vesey, Saskatchewan.
</p> <p>
<b>
Soldat de la Beauce<br>
</b>80 to 90 days to maturity. Identified as a European soldier type which has been grown in Beauce, Quebec for a long time. White, oval seed (14 to 15 mm long) with red marks like a soldier standing up. Productive, no disease. Good taste and fast cooking.
</p> <hr></td>
</tr>
<tr valign="top">
<td><h3 align="center">
<a name="BEAN/DRY/POLE">
</a>Bean/Dry/Pole
</h3></td>
<td><p>
<b>
Gerald's White<br>
</b>Tasty white bean. A semi-climber that does best with support. Passed down from neighbour to neighbour for 60 years.
</p> <p>
<b>
Montezuma Red<br>
</b>90 days to maturity. Attractive red bean used in chili or baked beans or soup. Started as a bush variety, but now I grow it as a pole bean, as it wanted support. Disease resistant. An heirloom variety found in 3000-year-old tombs of Aztec Indians.
</p> <p>
<b>
Ruth Bible<br>
</b>110 days to maturity. 5' tall. Plump, medium-sized, greyish-brown seed. Strong, leafy plant is very productive, but beans hard to shell. Originally from Bouys family in Kentucky, 1832. Low quantity.
</p> <hr></td>
</tr>
<tr valign="top">
<td><h3 align="center">
<a name="BEAN/RUNNER">
</a>Bean/Runner
</h3></td>
<td><p>
<b>
Enorma Scarlet<br>
</b>115 days to maturity. Very large, deep mauve seed with black speckles. Tasty as a snap bean when young. Long vines with bright red flowers which attract hummingbirds.
</p> <hr></td>
</tr>
<tr valign="top">
<td><h3 align="center">
<a name="BEAN/SNAP/BUSH">
</a>Bean/Snap/Bush
</h3></td>
<td><p>
<b>
Refugee<br>
</b>90 days to maturity. Small plant yields small, tan seed with black streaks. This bean is believed to have been brought to England by the Huguenots.
</p> <hr></td>
</tr>
<tr valign="top">
<td><h3 align="center">
<a name="BEAN/SNAP/POLE">
</a>Bean/Snap/Pole
</h3></td>
<td><p>
<b>
Cherokee Trail of Tears<br>
</b>Small, shiny black seed in purple pods. Sweet tasting. Originally from Cherokee Indians over 150 years ago.
</p> <hr></td>
</tr>
<tr valign="top">
<td><h3 align="center">
<a name="BEAN/SOY">
</a>Bean/Soy
</h3></td>
<td><p>
<b>
Black Jet<br>
</b>100 days to maturity. Prolific and easy to grow, self-seeding every year. Young, tender pods boiled with pinch of salt are favourite snack for the beer drinker in Japan.
</p> <hr></td>
</tr>
<tr valign="top">
<td><h3 align="center">
<a name="CARROT">
</a>Carrot
</h3></td>
<td><p>
<b>
Oxheart<br>
</b>90 days to maturity. Blocky 3" by 3"; roots are smooth and colourful, with strong tops. Good keeper. Dates back to 1884.
</p> <hr></td>
</tr>
<tr valign="top">
<td><h3 align="center">
<a name="CORN/FLOUR">
</a>Corn/Flour
</h3></td>
<td><p>
<b>
Mandan Bride<br>
</b>100 days to maturity. Striking, multi-coloured ears from Mandan Indians tribes of North Dakota. Mandan corns are "combination" corns containing both flint and flour kernels on the same ear. Easier to grind than 100% flint corn. 8 to 10 rows, stalks about 5 to 6' tall.
</p> <hr></td>
</tr>
<tr valign="top">
<td><h3 align="center">
<a name="CORN/SWEET">
</a>Corn/Sweet
</h3></td>
<td><p>
<b>
Luther Hill<br>
</b>85 days to maturity. White kernels, often two 5" ears per 5 to 6' plant, excellent quality, extra sweet. Developed by Luther Hill of Andover Township, New Jersey about 1902. Plants should be spaced 12' apart in rows to allow suckers to develop and produce ears. Isolate from other corn, save seed from as many plants as possible.
</p> <hr></td>
</tr>
<tr valign="top">
<td><h3 align="center">
<a name="LETTUCE/LEAF">
</a>Lettuce/Leaf
</h3></td>
<td><p>
<b>
Deer Tongue<br>
</b>Light green, upright, triangular-shaped, loose leaves have a crisp texture. Slow to bolt. Pennsylvania German heritage. Probably grown before 1900.
</p> <hr></td>
</tr>
<tr valign="top">
<td><h3 align="center">
<a name="ONION/GARLIC">
</a>Onion/Garlic
</h3></td>
<td><p>
<b>
Rocambole, Spanish Roja<br>
</b>Red-skinned cloves have a crunchy texture and delicate taste. Large, wedge-shaped cloves are easy to peel. Available in September. Takes 10 months to mature. Harvest in mid-summer.
</p> <hr></td>
</tr>
<tr valign="top">
<td><h3 align="center">
<a name="PEA">
</a>Pea
</h3></td>
<td><p>
<b>
Champion of England<br>
</b>75 days to maturity. 3 to 4' tall. Oblong, green, wrinkled seed. Productive. Very old, grown by Thomas Jefferson at Monticello.
</p> <p>
<b>
King Tut<br>
</b>Tall plant has purple pods which contain 6 wrinkled seeds which are light olive-brown and darken to mahogany with age. Seed found in Pharaoh Tutankhamon's tomb, a rather common myth in the seed world.
</p> <p>
<b>
Spanish Skyscraper<br>
</b>80 days to maturity. Long, climbing vines produce an abundance of pale green shell peas. Dry peas are brown and wrinkled.
</p> <hr></td>
</tr>
<tr valign="top">
<td><h3 align="center">
<a name="PEA/EDIBLE_PODDED">
</a>Pea/Edible Podded
</h3></td>
<td><p>
<b>
Slocan Valley Snow Pea<br>
</b>65 days to maturity. 6' vines have white flowers and give a good yield of 4" flat pods. Best flavour and texture obtained before seeds swell within pods. Originated from Japanese community in Slocan Valley of BC in 1941.
</p> <hr></td>
</tr>
<tr valign="top">
<td><h3 align="center">
<a name="PEPPER/HOT">
</a>Pepper/Hot
</h3></td>
<td><p>
<b>
Habanero<br>
</b>120 days to maturity. Thin-walled, puckered pod is 2 to 3" long and ripens to orange. 100,000 to 300,000 Scoville units of heat! One of the world's hottest. Low quantity.
</p> <hr></td>
</tr>
<tr valign="top">
<td><h3 align="center">
<a name="POTATO">
</a>Potato
</h3></td>
<td><p>
<b>
BC Blue<br>
</b>90 days to maturity. Good yields of round tubers with deep purple skin and violet-blue mottled flesh. Individual tubers don't get as large as All Blue but are more uniform size at harvest. Seems scab-resistant, but there was some wire worm damage. Very good flavour, nice baked.
</p> <p>
<b>
Banana Finger<br>
</b>100 days to maturity. Large fingerling variety with smooth, tan skin and yellow flesh. High yields. Some get very large. Seems scab and disease-resistant. Excellent flavour.
</p> <p>
<b>
Kajaan<br>
</b>Late season. Long, irregular, many-eyed. Vines vigorous. Very productive yield would have been much greater but for an early heavy frost. Waxy texture, good scrubbed and roasted. First Nations heirloom, from Tlingit village, SE Alaska.
</p> <p>
<b>
Six Weeks<br>
</b>Very early. Round, medium tubers have light red skin and moist flesh with excellent flavour and creamy, tender texture. I have selected for shallower eyes. Has been continuously grown in our family since at least 1930s. Possibly a Russian Mennonite heirloom.
</p> <hr></td>
</tr>
<tr valign="top">
<td><h3 align="center">
<a name="SQUASH_C_MAXIMA">
</a>Squash (C. maxima)
</h3></td>
<td><p>
<b>
Arikara<br>
</b>95 days to maturity. A large hubbard-type variety grown by the Arikara Indians of North Dakota. Pinkish-orange with a grey-green star at the blossom end. A long keeper, ideal for soup (as per Garrett's recipe on page 40 of the April 1995 issue of HSP magazine).
</p> <hr></td>
</tr>
<tr valign="top">
<td><h3 align="center">
<a name="TOMATO/PINK">
</a>Tomato/Pink to Purple Skin
</h3></td>
<td><p>
<b>
Cherokee Purple<br>
</b>86 days to maturity. Semi-determinate. High yield of uniformly round, large fruit (most 1.5 + lbs) that is low in acid and mildly sweet. No cracking, good keeping.
</p> <p>
<b>
Japanese Oxheart<br>
</b>70-80 days to maturity. Large, oxheart-shaped, pink fruit has good flavour. Large plant is productive. Originally from Japan.
</p> <p>
<b>
Maritza Rose<br>
</b>75 days to maturity. Origin: France. Medium to large size, seedy, pink fruit has very good to excellent flavour. Large plant is very productive.
</p> <p>
<b>
Oddshape<br>
</b>80 days to maturity. Tall, vigorous vines produce many 2-3 oz fruit, with dull purple-pink skin. Squarish, minimally lumpy fruit has good flavour but unusual skin texture. Good disease resistance in vines.
</p> <p>
<b>
Pink Brandywine<br>
</b>120 days to maturity. Indeterminate. Beefsteak type, slightly lobed. Fruit is over 2 lbs and grows on large plants with potato leaves; stakes well. Outstanding flavour; our best-tasting tomato. Listed in seed catalogues in 1890s.
</p> <hr></td>
</tr>
<tr valign="top">
<td><h3 align="center">
<a name="TOMATO/RED">
</a>Tomato/Red Skin
</h3></td>
<td><p>
<b>
Angelina's Italian<br>
</b>75 days to maturity. Excellent paste type. Sparse vines, but quite tolerant of irregular watering. Disease and crack resistant under excessive rain. Brought from Italy originally in 1931.
</p> <p>
<b>
Camp Joy<br>
</b>80 days to maturity. Very productive, large cherry tomato with lovely sweet taste. Plant with sunflowers to save space. Only killer frost can fell this strong runner.
</p> <p>
<b>
Cherriettes<br>
</b>60-70 days to maturity. Dime-size fruit was bred for currant cherry size. Sweet but quite tart. Very productive. The best cherry for drying on the plant. Good in hanging baskets and pots.
</p> <p>
<b>
Cuostralee<br>
</b>90 days to maturity. Very large (up to 2 lb), meaty fruit is often lobed with calyx indented. Reminds me of the way tomatoes tasted 50 years ago.
</p> <p>
<b>
Konigin der Fruhen<br>
</b>65-75 days to maturity. 3" flat fruit produces well in cold weather. Very old, rare, non-commercial variety from Germany. Name means "Queen of Earlies".
</p> <p>
<b>
Mystery Keeper<br>
</b>90 days to maturity. Indeterminate. Plant found on compost pile in 1994. Fruit were kept on kitchen shelf until May 1995. Planted sprouted seeds from inside this stored fruit. The top plant produced 110 fruits in 1995; in cool 1996 conditions, 90 fruits were produced. 3" 6-oz fruit picked at yellow-orange stage in Sept. were stored in a box in a cool place and ripened one by one until spring. Fairly good flavour. Better producer and better keeping quality than any longkeeper I've grown.
</p> <p>
<b>
Peace Vine<br>
</b>75 days to maturity. 1" fruit borne in grape-like clusters. Excellent yield and flavour. Said to have high levels of buteric acid, a natural sedative to calm the jitters. Also high vitamin C content.
</p> <p>
<b>
Ross Red Salad<br>
</b>Indeterminate. 6' to 8' tall plants should be staked. 2" fruit is very sweet and has excellent flavour. Frost resistant. Disease resistant. Tie up and hang in cool place until Xmas. Discovered in a Safeway store in Toronto in 1956 by my father-in-law.
</p> <p>
<b>
Sandia Gem<br>
</b>70-75 days to maturity. Indeterminate. Very good flavour. Productive. Seeds were found in leather pouch on the Sandia Mountain in 1985. The pouch carbon dated from around early 1800s. 3 seeds out of 150 germinated.
</p> <hr></td>
</tr>
<tr valign="top">
<td><h3 align="center">
<a name="TOMATO/YELLOW">
</a>Tomato/Yellow to Orange Skin
</h3></td>
<td><p>
<b>
Coyote<br>
</b>Very prolific cherry tomato is pale yellow to tan in colour with good flavour. Vines tend to be weedy. Said to be a wild variety from Vera Cruz, Mexico.
</p> <p>
<b>
Elbe<br>
</b>80-85 days to maturity. Indeterminate. Yellow-orange beefsteak type has medium to almost-large fruit that has a unique flavour, sweet and tart at the same time. Large plant. Potato leaf. Named after the Elbe River in Germany where it originated. This variety has been a favourite since 1889.
</p> <p>
<b>
Ilse's Yellow<br>
</b>75 days to maturity. Very vigorous vines yielded many large, globe-shaped fruits. Disease free. When frost knocked down 32 other varieties, this variety was barely touched and still producing. Frost hardy? This variety should be preserved.
</p> <p>
<b>
Mennonite Heirloom<br>
</b>70 days to maturity. Beefsteak type with very small seed cavities; an excellent slicing tomato. Heirloom brought to Ontario from Pennsylvania pre-1900.
</p> <p>
<b>
Morden Yellow<br>
</b>60-70 days to maturity. Determinate. Early mid-season. Medium size with excellent flavour. Best yellow I've tasted. Saved this variety after it was dropped from commercial catalogues in late 1960s.
</p> <hr></td>
</tr>
<tr valign="top">
<td><h3 align="center">
<a name="TOMATO/OTHER">
</a>Tomato/Other
</h3></td>
<td><p>
<b>
Beaute Blanche du Canada<br>
</b>90 days to maturity. White to pale cream, ribbed fruit has nice, mild flavour. Large robust plants produce large beefsteak-type fruit.
</p> <p>
<b>
Black Prince<br>
</b>70-80 days to maturity. This fruit is a deep garnet in colour, darkest red to brown with shadings of chestnut. Harvest when shoulders are dark and still showing a trace of green. Medium sized, some are round and some are oval. Very good flavour. Originally from Irkutsk, Siberia.
</p> <p>
<b>
Garden Lime<br>
</b>75 days to maturity. 6 oz ribbed, green fruit with yellow tinge grows on very productive, 4' tall plants. Neon green flesh. Very interesting variety.
</p> <p>
<b>
Green Grape<br>
</b>Good-tasting, 1 oz 1.25" green fruit grows in clusters like grapes on cherry-type plant. Mid to late season. A hit with kids at the fair.
</p> <p>
<b>
Paul Robeson<br>
</b>75 days to maturity. Large plant has large, dusky, dark red fruit with dark green, dusky shoulders, which is quite dark when fully ripe. Named after Paul Robeson, an operatic singer who was an advocate of equal rights for Blacks. Originally from Russia.<br>
</p> <hr></td>
</tr>
<tr valign="top">
<td><h3 align="center">
<a name="WATERMELON">
</a>Watermelon
</h3></td>
<td><p>
<b>
Citron, Red-Seeded<br>
</b>95 days to maturity. Dark green with pale green stripes, 6 to 10 lbs. Reliable, disease-free melon has hard, bland, yellow flesh which is good only for preserves. Was very popular for this use in early 1900s; several delicious candying and preserving recipes available on request. "Indestructible"; melon keeps for several months in cool cellar.
</p>
</td></tr></table>
<?
}

?>
