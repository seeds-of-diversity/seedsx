<?

include_once( "_poll.php" );

poll_header( "index" );


/*

<link rel="stylesheet" type="text/css" href="/english/css/naturewatch.css">


<style>
  <!--

  .wrap1 {
    position:relative;
  }
  .wrap2 {
    position:relative;
  }
  #dropmenu0, #dropmenu1, #dropmenu2, #dropmenu3 {
    z-index:100;
  }

  .nwdropdown {
    font-family: arial,helvetica;
    text-decoration: none;
    color: #E7DEAD;
    font-weight: normal;
    size: 10pt;
  }

  .nwdropdown:hover {
    font-family: arial,helvetica;
    text-decoration: none;
    color: #000000;
    font-weight: normal;
    size: 10pt;
  }

  .nwtopmenu {
    font-family: arial,helvetica;
    text-decoration: none;
    color: #FFFFFF;
    font-weight: normal;
    size: 10pt;
  }

  .nwtopmenu:hover {
    font-family: arial,helvetica;
    text-decoration: none;
    color: #E7DEAD;
    font-weight: normal;
    size: 10pt;
  }

  .pwmenu {
    font-family: arial,helvetica;
    text-decoration: none;
    color: #FFFFFF;
    font-weight: normal;
    size: 10pt;
  }

  .pwmenu:hover {
    font-family: arial,helvetica;
    text-decoration: none;
    color: #008040;
    font-weight: normal;
    size: 10pt;
  }
  -->
</style>


<body bgcolor="#EBEDED" text="#000000" topmargin="0" leftmargin="0" marginwidth="0" marginheight="0" onLoad="setLoaded();">

<center>

<table width="784" cellspacing="0" cellpadding="0" border="0" bgcolor="#FFFFFF">
  <tr>
    <td valign=top><img src="/images/tl.gif" border=0></td>
    <td width="100%" background="/images/top.gif"><img src="/images/blank.gif" width="1" height="1" border="0"></td>
    <td align=right valign=top><img src="/images/tr.gif" border=0></td>
  </tr>
</table>


<table width="780" cellspacing="0" cellpadding="0" border="0" bgcolor="#FFFFFF">
  <tr>
    <td colspan="2">
      <table cellpadding="0" cellspacing="0" border="0" width="100%">
        <tr>
          <td valign="top"><img src="/english/images/plantwatch_logo.gif" width="181" height="89" border="0" alt="PlantWatch"></td>
          <td align="right" valign="top"><img src="/english/images/naturewatch_logo.gif" width="205" height="89" border="0" alt="NatureWatch"></td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td colspan="2" bgcolor="#000000" height="30" width="100%">
    <table cellpadding="1" cellspacing="1" border="0" width="100%">
  <tr>
    <td width="5"><img src="/images/blank.gif" width=1 height=1 border=0></td>
    <td nowrap><!-- Francais --><a href="/language_redirect.asp?language=francais" onClick="this.blur()" class="nwtopmenu">en fran&ccedil;ais</a></td>
    <td width="100%" align="center" class="nwtopmenu"><a href="/english/frogwatch/" class="nwtopmenu">FrogWatch</a> &nbsp; | &nbsp; <a href="/english/icewatch/" class="nwtopmenu">IceWatch</a> &nbsp; | &nbsp; <a href="/english/plantwatch/" class="nwtopmenu">PlantWatch</a> &nbsp; | &nbsp; <a href="/english/wormwatch/" class="nwtopmenu">WormWatch</a></td>

    <td width="30" nowrap><img src="/images/blank.gif" width=1 height=1 border=0></td>
    <td width="26" align="right"><!-- Download Data --><a href="/english/download.html"><img src=/images/icons/download_off.gif border=0 alt="Download Data"></a></td>
    <td width="26" align="right"><!-- Contact Us --><a href="/english/contact.html"><img src=/images/icons/contact_off.gif border=0 alt="Contact Us"></a></td>
    <td width="26" align="right"><!-- News --><a href="/english/news.html"><img src=/images/icons/news_off.gif border=0 alt="News"></a></td>
    <td width="26" align="right"><!-- Search --><a href="/cgi-bin/search/search.asp?language=english"><img src=/images/icons/search_off.gif border=0 alt="Search"></a></td>
    <td width="26" align="right"><!-- Printer Friendly --><a href="/cgi-bin/printer_friendly.asp"><img src=/images/icons/printerfriendly_off.gif border=0 alt="Printer Friendly"></a></td>
    <td width="5" align="right"><img src="/images/blank.gif" width=1 height=1 border=0></td>
  </tr>
</table></td>
  </tr>

  <tr>
    <td bgcolor="#04390D" valign="top" width="180">
      <table cellpadding="0" cellspacing="0" border="0">
        <tr><td><img src="/images/plantwatch_photos/03.jpg" width="181" height="130" border="0" alt="photo"></td></tr>
      </table>
   <table cellpadding="4" width="100%" cellspacing="0" border="0" background="/english/plantwatch/images/plantwatch_menu_background.jpg">
        <tr><td align="right">&nbsp;</td></tr>
      <tr><td align="right"><a class="pwmenu" href="/english/plantwatch/intro.html">What is PlantWatch?</a>&nbsp;</td></tr>
        <tr><td align="right"><a class="pwmenu" href="/english/plantwatch/why_monitor.html">Why Monitor Plants?</a>&nbsp;</td></tr>
        <tr><td align="right"><a class="pwmenu" href="/english/plantwatch/how_to_plantwatch.html">How To PlantWatch</a>&nbsp;</td></tr>
        <tr><td align="right"><a class="pwmenu" href="/english/plantwatch/identifying_plants.html">Identifying Plants</a>&nbsp;</td></tr>
        <tr><td align="right"><a class="pwmenu" href="/english/plantwatch/learn_plants.asp">Plant Descriptions</a>&nbsp;</td></tr>
        <tr><td align="right"><a class="pwmenu" href="/english/observations/intro.html?WatchProgram=PlantWatch">Submit Observations</a>&nbsp;</td></tr>
        <tr><td align="right"><a class="pwmenu" href="/english/plantwatch/view_results.html">View Results</a>&nbsp;</td></tr>
        <!--tr><td align="right"><a class="pwmenu" href="/cgi-bin/view_observations/view_plant_observations.asp?language=english">View Results</a>&nbsp;</td></tr-->
        <tr><td align="right"><a class="pwmenu" href="/english/plantwatch/faqs.html">Frequently Asked Questions</a>&nbsp;</td></tr>
        <tr><td align="right"><a class="pwmenu" href="/english/plantwatch/printable_observation_form.pdf">Observation Form</a>&nbsp;</td></tr>
        <tr><td align="right"><a class="pwmenu" href="/english/plantwatch/program_coordinators.html">Program Coordinators</a>&nbsp;</td></tr>
        <tr><td align="right"><a class="pwmenu" href="/english/plantwatch/glossary.html">Glossary</a>&nbsp;</td></tr>
        <tr><td align="right"><a class="pwmenu" href="/cgi-bin/quiz/plantwatch/step1.asp?language=english">PlantWatch Quiz</a>&nbsp;</td></tr>
        <tr><td align="right"><a class="pwmenu" href="/english/plantwatch/ootm/index.html">Observer of the Month</a>&nbsp;</td></tr>
        <tr><td>&nbsp;</td>
      </table>
    </td>
    <td bgcolor="#CCD7CD" valign="top" width="600">
      <table cellpadding="3" cellspacing="3" border="0" width="100%">
        <tr>
          <td valign="top">
*/

?>

<P><B>Pollinators</B> are the insects that pollinate flowers.  You know about bees and butterflies, but did you know that
there are over 1000 species of pollinating insects in Canada?  Together they are an indispensible natural resource,
and their daily work is essential for over a billion dollars of apples, pears, cucumbers, melons and many other kinds of
Canadian farm produce.</P>

<P>
<TABLE border=0 align=right><TR><TD class='img1'><IMG src='img/i_butterfly 300x185.jpg' width=300 height=185><DIV class='img1caption'>A Monarch butterfly visits a thistle flower.</DIV></TD></TR></TABLE>

These insects are under pressure from loss of habitat, loss of food sources, disease and pesticides.  Populations of
wild and domesticated insects are threatened.  We cannot take them for granted any longer.
</P>
<H3>Be a Pollinator Observer!</H3>
<P>
Watch for pollinating insects in your garden, in your local park, and along country roads. A joint venture between
<A href='http://www.seeds.ca/'>Seeds of Diversity Canada</A> and
<A href="http://www.eman-rese.ca/">Environment Canada's Ecological Monitoring and Assessment Network Coordinating Office</A>
(EMANCO), allows volunteers throughout Canada to join in a nationwide survey of pollinators.  It's easy to help, and your
observations can assist scientists to understand these beneficial insects better.
</P>

<H3>
How Does the Project Work?
</H3>
<P>
Volunteers across Canada can download training materials that include detailed pictures of the most important families
of pollinating insects.  When volunteers observe insects visiting flowers in gardens, parks or roadsides, they use
a check-off form to record the insect families, number and some simple details about the time and place. Observations
are submitted by mail, or online directly to the online Pollination Canada database.
</P><P>
When many observations are made, they form an invaluable indication of the habits and population trends of the
important pollinators, especially when observations are repeated several times at the same site.  Entomologists will
use this information to evaluate where further detailed studies are needed, and gardeners will benefit from a
greater appreciation of pollination and the insects that we depend upon.
</P>

<? /*
<p>
  The PlantWatch program enables "citizen scientists" to get involved by recording flowering times for selected plant species and
  reporting these dates to researchers through the Internet or by mail.  When you submit your data electronically, it's added
  instantly to Web maps showing bloom dates across Canada, so your observations make a difference right away!
</p>

*/

poll_footer();

?>
