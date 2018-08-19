<?

include_once( "_poll.php" );

poll_header( "insects" );


?>

<H3>Insect profiles</H3>
<P>
There are thousands of species of pollinating insects in Canada, but we group them into simple categories for amateur
observers. When you see an insect visiting a flower, try to notice which type it is, based on the groups below.
If you know the family, or the exact species, report it as a special note with your observations.
</P>
<TABLE border='2' cellspacing='5' cellpadding='10'><TR>
<TD><? i_href( "i_bees" ) ?>
<FONT size='+1'><B>Bees</B></FONT><BR><FONT size='-1'>(<I>Hymenoptera</I> order)</FONT><BR>
<IMG src='<?= POLL_ROOT ?>img/i_bee 300x185.jpg' width=300 height=185 alt='Bees'></A></TD>
<TD><? i_href( "i_wasps" ) ?>
<FONT size='+1'><B>Wasps</B></FONT><BR><FONT size='-1'>(<I>Hymenoptera</I> order)</FONT><BR>
<IMG src='<?= POLL_ROOT ?>img/i_wasp 300x185.jpg' width=300 height=185 alt='Wasps'></A></TD>
</TR><TR>
<TD><? i_href( "i_flies" ) ?>
<FONT size='+1'><B>Flies</B></FONT><BR><FONT size='-1'>(<I>Diptera</I> order)</FONT><BR>
<IMG src='<?= POLL_ROOT ?>img/i_fly 300x185.jpg' width=300 height=185 alt='Flies'></A></TD>
<TD><? i_href( "i_butterflies" ) ?>
<FONT size='+1'><B>Butterflies and Moths</B></FONT><BR><FONT size='-1'>(<I>Lepidoptera</I> order)</FONT><BR>
<IMG src='<?= POLL_ROOT ?>img/i_butterfly 300x185.jpg' width=300 height=185 alt='Butterflies and Moths'></A></TD>
</TR><TR>
<TD colspan=2><? i_href( "i_beetles" ) ?>
<FONT size='+1'><B>Beetles</B></FONT><BR><FONT size='-1'>(<I>Coleoptera</I> order)</FONT><BR>
<IMG src='<?= POLL_ROOT ?>img/i_beetle 300x185.jpg' width=300 height=185 alt='Beetles'></A></TD>

</TR></TABLE>


<?

poll_footer();

?>
