<?
define("SITEROOT", "../../");
include_once( SITEROOT."site.php" );
include_once( STDINC. "DocRep/DocRep.php" );
include_once( "_poll.php" );

poll_header( "poll_forms" );

?>

<H3>Observation Forms</H3>
<P>
You can make observations of pollinators anywhere that you find flowers growing.  Watch in your garden, watch in
your local park, watch when you go hiking.  The key to providing useful information for the program is to observe
regularly and submit repeated observations from the same locations.  Once a month in the same locations would be ideal,
though any information that you can provide is helpful.
</P>
<P>
Download and print the observation forms and take them with you when you observe.  Do your best to identify the types of
insects and flowers.
</P>
<P>
You can send your observations directly <A HREF='poll_submit.php'>online</A>,
or you can <A HREF='poll_submit.php'>mail</A> the forms
to <A href='http://www.seeds.ca/'>Seeds of Diversity's</A> office.
</P>
<P align=center><FONT size=+1>Thanks for being a Pollinator Observer!</FONT></P>
<P>&nbsp;</P>
<?
// <P><IMG src='img/icon_pdf.gif'>&nbsp;&nbsp;&nbsp;<A href='lit/Pollinator Observation Form.pdf'>Download the Pollinator Observation Form</A></P>

$raParms = array( "icon"   => "img/std/icon-pdf-l.gif",
                  "target" => "_blank" );

DR_link( "form/Pollinator_Site_Form.pdf",
         "Pollinator Site Form",
         "Use this form when you do the first observation session at a new site. Describe the location and record your first pollinator count at that site.",
         $raParms );
DR_link( "form/Pollinator_Follow-up_Form.pdf",
         "Pollinator Follow-up Form",
         "Use this form to record repeat observations at locations that you've already described.",
         $raParms );
DR_link( "form/Pollinator_Description_Form.pdf",
         "Pollinator Description Form",
         "Use this form to describe pollinators that you don't recognise.",
         $raParms );
?>

<!--
<P>&nbsp;</P>
<P><A href='submit/form1.php'>Submit your observations online</A>
or mail your completed form to:</P>
<P style='margin-left:2em'><FONT size=-1>Seeds of Diversity Canada<BR>P.O. Box 36 Stn Q<BR>Toronto ON M4T2L7</FONT></P>
-->

<?

poll_footer();

?>
