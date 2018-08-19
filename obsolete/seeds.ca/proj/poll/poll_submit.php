<?
define("SITEROOT", "../../");
include_once( SITEROOT."site.php" );
include_once( STDINC. "DocRep/DocRep.php" );
include_once( "_poll.php" );

poll_header( "poll_submit" );

?>

<H3>Submit Your Observations</H3>
<P>
Send your pollinator observations to be recorded in our national database by:
<BLOCKQUOTE>
<P>Mailing your completed forms to</P>
<P <? /*style='margin-left:2em' */ ?> ><FONT size=-1>Pollination Canada<BR>c/o Seeds of Diversity Canada<BR>P.O. Box 36 Stn Q<BR>Toronto ON M4T2L7</FONT></P>
</BLOCKQUOTE>
or
<BLOCKQUOTE>
Leaving your forms with one of Pollination Canada's partners.
</BLOCKQUOTE>
or
<BLOCKQUOTE>
<!--<P><A href='submit/form1.php'>Submit your observations online</A></P> -->
Submitting the information online here. <I><B>Coming soon!</B></I>
</BLOCKQUOTE>

</P>


<?

poll_footer();

?>
