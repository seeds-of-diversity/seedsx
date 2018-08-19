<?

include_once( "_poll.php" );

poll_header( "f_morph" );

?>

<H3>Six Flower Shapes</H3>
<P>
Plants have evolved many strategies to attract pollinators.  Brightly coloured flowers help flying insects to find
them, and sweet nectar is a lure to bring insects deep within the flowers.  Plants need insects to carry pollen from
one flower to another, since the pollen contains the genes that create the next year's seeds.</P>
<P>
The problem, from the plant's point of view, is that while insects distribute pollen for plant reproduction, they also
eat the pollen. It's an important food source for many pollinators.  Furthermore, some insects can drink the sweet
nectar from a flower, without
contacting the pollen - they get a free lunch at the plant's expense.
</P>
<P>
Plants have evolved strategies that let the most effective insects reach their pollen, excluding those that don't
serve their true need - reproduction.  For example, butterflies have long tongues to reach nectar, but they don't
tend to brush pollen on their bodies while they feed.  This makes them ineffective pollinators of large flowers
because they take nectar without giving back a &#0147;pollination service&#0148;.  Bees and flies, on the other hand,
have fuzzy bodies that readily pick up pollen while they feed on nectar.  Some plants have adapted flower shapes that
allow bees to crawl inside, but keep butterflies out, or don't provide butterflies with an easy place to land.
</P>
<P>
Botanists use the following six categories to group flowers, based on their shapes.  You will tend to see that certain
insects prefer certain flower shapes.
</P>
<TABLE border='2' cellspacing='5' cellpadding='10'><TR>
<?
function morphA( $title, $name, $text )
{
echo "<TD><FONT size='+1'><B>$title</B></FONT><BR><FONT size='-1'>$text</FONT><BR>";
echo "<IMG src='".POLL_ROOT."img/$name.jpg' width=300 alt='$title'></TD>";
}

morphA( "Dome",     "f_haplomorphic1", "(<I>Haplomorphic</I>) rounded top. e.g. dandelions, scabiosa, gomphrena (shown)" );
morphA( "Cup",      "f_haplomorphic2", "(<I>Haplomorphic</I>) cup-shaped. e.g. buttercups, campanula, poppy (shown)" );
echo "</TR><TR>";
morphA( "Radial",   "f_actinomorphic", "(<I>Actinomorphic</I>) flat, many radial petals.  e.g. daisies, fleabane, china aster (shown)" );
morphA( "Numeric",  "f_pleomorphic",   "(<I>Pleomorphic</I>)  radial petals in symmetrical numeric patterns, generally 3-6 petals.  e.g. trillium (3), mustard (4), geranium (5, shown), hepatica (6)" );
echo "</TR><TR>";
morphA( "Trumpet",  "f_stereomorphic", "(<I>Stereomorphic</I>) trumpet-shaped, tubular. e.g. morning glory, petunia, daffodil, nicotiana (shown)" );
morphA( "Irregular","f_zygomorphic",   "(<I>Zygomorphic</I>) non-symmetrical petals.  e.g. violets, beans (shown), orchids, snapdragons" );

?>

</TR></TABLE>

<?
poll_footer();

?>
