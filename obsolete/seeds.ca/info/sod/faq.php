<?
define( "SITEROOT", "../../" );
include_once( SITEROOT."site.php" );
include_once( PAGE1_TEMPLATE );
include_once( "../_info.php" );


$page1parms = array (
                "lang"      => "EN",
                "title"     => "Frequently Asked Questions",
                "tabname"   => "ABOUT",
                "box1title" => "More Information",
                "box1fn"    => "box1fn_en",
                "box2title" => "Contact Us",
                "box2fn"    => "box2fn_en"
             );



Page1( $page1parms );


function Page1Body() {
?>
<h2>Frequently Asked Questions</h2>
<h3>Seeds of Diversity Magazine</h3>
<ul compact="compact">
<li><a href="#mag1">Who receives the magazine?</a></li>
<li><a href="#mag2">When is the magazine published?</a></li>
<li><a href="#mag3">How can I submit articles, letters and news?</a></li>
</ul>
<h3>Our National Seed Exchange</h3>
<ul compact="compact">
<li><a href="#se1">How does it work?</a></li>
<li><a href="#se2">Who can participate?</a></li>
<li><a href="#se3">What if I don't have any seeds to exchange?</a></li>
<li><a href="#se4">Why do we do this?</a></li>
<li><a href="#se5">What kinds of seeds are there?</a></li>
<li><a href="#se6">Are the seeds genetically modified?</a></li>
<li><a href="#se7">Do you sell seeds, or donate seeds to community gardens?</a></li>
<li><a href="#se8">Can I still participate if I live outside of Canada?</a></li>
</ul>
<h3>Membership</h3>
<ul compact="compact">
<li><a href="#memb1">How do I become a member?</a></li>
<li><a href="#memb2">I've lost my membership number!</a></li>
<li><a href="#memb3">Do you accept members from outside of Canada?</a></li>
</ul>
<h3>Our Publications</h3>
<ul compact="compact">
<li><a href="#pub1">How do I order your books?</a></li>
<li><a href="#pub2">Can I order your books from outside of Canada?</a></li>
</ul>
<hr>
<h1>Answers</h1>

<h3>Seeds of Diversity Magazine</h3>
<a name="mag1"></a><p><b>Who receives the magazine?</b></p>
<blockquote>
<p>A subscription to <i>Seeds of Diversity</i> magazine is included with membership.
You can also purchase back issues using our <a href="<?= MBR_FORM_URL_EN ?>">order form</a>.</p>
</blockquote>

<a name="mag2">
</a><p><b>When is the magazine published?</b></p>
<blockquote>
<p>There are two issues per year: in winter (mailed with the annual Member Seed Directory), and autumn.  Since our membership is by the
calendar year, your first issue will be the winter issue of the year that you start your membership.</p>
</blockquote>

<a name="mag3">
</a><p><b>How can I submit articles, letters and news?</b></p>
<blockquote>
<p>We welcome articles on topics related to seed-saving, plant propagation, interesting plant varieties
and general gardening.  Also, we encourage anyone to submit letters to the editor and news about garden events.</p>
<p><?php /*Submission deadlines for articles are: Nov 1 for the January issue, March 1 for the May issue, July 1
for the September issue.*/ ?>  Please send all items to:</p>
<pre>
Seeds of Diversity
attn: Magazine Editor
P.O. Box 36  Stn Q
Toronto ON  M4T 2L7
</pre>
</blockquote>
<hr>

<h3>Our National Seed Exchange</h3>
<a name="se1"></a><p><b>How does it work?</b></p>
<blockquote>
<p>Our seed exchange is a member-to-member programme, coordinated through an annual directory.
Each year at harvest time, we ask our members if they have seeds that they would like to offer to other members.
These offers are compiled into a directory which is mailed with the January magazine.  When a
member wants to request seeds, they write directly to the offering member and enclose a small fee
to cover packaging and return postage.</p>
<p>Some members accept stamps or "Canadian Tire money" instead of cash.</p>
</blockquote>
<a name="se2"></a><p><b>Who can participate?</b></p>
<blockquote>
<p>The seed exchange is strictly for members only.</p>
</blockquote>
<a name="se3"></a><p><b>What if I don't have any seeds to exchange?</b></p>
<blockquote>
<p>Members do not normally exchange seeds for seeds.  Instead, when a member requests seeds they enclose a small amount
of money with their request to cover packaging and return postage.  This way, new members can get started even if they
have no seeds to offer.</p>
</blockquote>
<a name="se4"></a><p><b>Why do we do this?</b></p>
<blockquote>
<p>Our purpose is to conserve the gene pool of traditional Canadian plants, and to make sure that those varieties
are available to people who can use them.  The seed exchange is our way of giving people access to these heritage
varieties, and our members are dedicated to propagating them for future generations to enjoy.</p>
</blockquote>
<a name="se5"></a><p><b>What kinds of seeds are there?</b></p>
<blockquote>
<p>Through our seed exchange, our members offer over 1500 different kinds of plants.  Most of them are vegetables and
garden fruit, such as tomatoes, beans and melons.  We also exchange many ornamental flowers and herbs, some grains, and
a few kinds of tree fruit and native plants.</p>
<p>All of these varieties are non-hybrid, non-patented, public domain varieties.  This means that they can be propagated
easily by amateur seed-savers, and that they are not owned by anyone.</p>
</blockquote>
<a name="se6"></a><p><b>Are the seeds genetically modified?</b></p>
<blockquote>
<p>Our seed exchange is only for traditional, public domain varieties.  We are dedicated to conserving the broad plant
gene pool, and modern engineered varieties are simply not part of that interest.  Besides, since most genetically
modified plants are patented, it would be illegal for our members to save seeds and exchange them.</p>
</blockquote>
<a name="se7"></a><p><b>Do you sell seeds, or donate seeds to community gardens?</b></p>
<blockquote>
<p>Sorry, no.  We have no central inventory of seeds, no store, and no mail order seed sales.  Our members exchange seeds
among themselves, but we just coordinate the seed exchange directory.  If you would like to purchase quality heritage seeds,
see our <a href="<?= RL_ROOT ?>rl.php">Resource List</a> of seed companies.  These are companies which our members have
found helpful, and of high quality.  If your non-profit group needs a donation of seeds, we invite you to write a letter to
our magazine.  Our members are often willing to help a worthwhile cause.</p>
</blockquote>
<a name="se8"></a><p><b>Can I still participate if I live outside of Canada?</b></p>
<blockquote>
<p>You should check with the Customs office of your country to see what kinds of seeds can be imported from Canada.  If you
live in the United States, there is no trouble mailing common vegetable and flower seeds across the border, but seeds of major
crops such as corn and wheat are not allowed.  Also, both Canadian and U.S. customs will not allow tubers, roots or bulbs
to cross the border without proper inspection and quarantine (this includes potatoes, garlic, potted plants, bareroot trees,
and any other material which may have soil on it).</p>
</blockquote>
<hr>

<h3>Membership</h3>
<a name="memb1"></a><p><b>How do I become a member?</b></p>
<blockquote>
<p>Complete details are on our <a href="<?= MBR_FORM_URL_EN ?>">membership and order form</a>.
If you have any questions,
please do not hesitate to <a href="mailto:office@seeds.ca">email</a> or call our office.</p>
</blockquote>
<a name="memb2"></a><p><b>I've lost my membership number!</b></p>
<blockquote>
<p>You need your membership number to request seeds through the seed exchange.  It is originally mailed to you
when you join, and it appears on the mailing label of your magazines.  If you can't find it, don't worry.  Just
<a href="mailto:office@seeds.ca">email</a> or call our office, and we'll gladly find it for you.</p>
</blockquote>
<a name="memb3"></a><p><b>Do you accept members from outside of Canada?</b></p>
<blockquote>
<p>We have several members in the U.S. and even some in Europe.  U.S. members can participate in our
seed exchange, though you should check which kinds of seeds are allowed to be imported and exported from your
country.  Most common vegetable and flower seeds are no trouble.</p>
<p>See our <a href="<?= MBR_FORM_URL_EN ?>">membership and order form</a> for our overseas membership rate.</p>
</blockquote>
<hr>

<h3>Our Publications</h3>
<a name="pub1"></a><p><b>How do I order your books?</b></p>
<blockquote>
<p>Complete details are on our <a href="<?= MBR_FORM_URL_EN ?>">membership and order form</a>.  To credit card
customers, sorry, we can only accept Canadian cheques or postal money orders.</p>
</blockquote>
<a name="pub2"></a><p><b>Can I order your books from outside of Canada?</b></p>
<blockquote>
<p>We'll ship our books anywhere.  For U.S. customers, please send a postal money order in U.S. dollars to
cover additional shipping charges.  For overseas customers, please contact our office for further shipping
information.</p>
</blockquote>
<?
}

?>
