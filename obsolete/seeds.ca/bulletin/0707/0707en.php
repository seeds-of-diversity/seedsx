<?
// e-Bulletin July 2007 English

define("SITEROOT","../../");
include(SITEROOT."site.php");
include( "../_bullDraw01.php" );

$bullDraw = new BulletinDraw( "July", "2007", "0707", "EN" );

$bullDraw->drawHeader();
$bullDraw->drawTableStart();
$bullDraw->drawSideBarStart();

doSideBarText( $bullDraw );

$bullDraw->drawSideBarEnd();
$bullDraw->drawMainStart();

doMainText( $bullDraw );

$bullDraw->drawMainEnd();
$bullDraw->drawTableEnd();
$bullDraw->drawFooter();



function doSideBarText( $bullDraw )
/**********************************
 */
{
?>
      <p align="left"><font color="#336633" size="2" face="Geneva, Arial, Helvetica, sans-serif"><b><strong>Green
        Tip</strong></b></font><font size="1" face="Geneva, Arial, Helvetica, sans-serif"><br>
        <br>
        <img src="images/sidebarbee.gif" width="145" height="137"> <br><br>
        70% of our food crops need insects for pollination. Most wild plants, and
        small animals that eat seeds, could not survive without them. Not only
        bees and butterflies; there are over 1000 species of pollinating insects
        in Canada! Unfortunately, these beneficial insects are under pressure
        from loss of habitat, loss of food sources, disease, and pesticides. As
        insect populations are threatened, so are the fruit and vegetable produce,
        and the wild ecosystems that depend on these pollinators. </font></p>
      <p align="left"><font color="#000000" size="1" face="Geneva, Arial, Helvetica, sans-serif"><em>Information
        is needed now so that steps can be taken to protect pollinator populations.
        </em> </font></p>
      <p align="left"><font size="1" face="Geneva, Arial, Helvetica, sans-serif">Visit
        <a href="http://www.pollinationcanada.ca" target="_blank">www.pollinationcanada.ca</a>
        to learn more about Seeds of Diversity's <em>Pollination
        Canada</em> program and how to get involved.</font></p>
<?
}


function doMainText( $bullDraw )
/*******************************
 */
{
    define( "P_STYLE_TITLE", "align='left' style='color:#77b377; font-size: 13pt; font-family:Geneva, Arial, Helvetica, sans-serif;'" );
    define( "P_STYLE_TEXT",  "align='left' style='color:#000000; font-size: 10pt; font-family:Arial, Helvetica, sans-serif;'" );

//    <style type='text/css'>
//    .bullTitle  { color:#77b377; font-size: 13pt; font-family:Geneva, Arial, Helvetica, sans-serif; }
//    #bullMain   { color:#000000; font-size: 10pt; font-family:Arial, Helvetica, sans-serif; }
//    #bullMain td{ color:#000000; font-size: 10pt; font-family:Arial, Helvetica, sans-serif; }
//    </style>
//    <DIV id='bullMain'>


?>

      <p <?=P_STYLE_TITLE?> ><b>In this issue:</b></p>
      <p <?=P_STYLE_TEXT?> ><a href="#article1">Summer is here. Make it count! Join the Pollination Buzz</a></p>
      <p <?=P_STYLE_TEXT?> ><a href="#article2">Try a beginner observation today!</a></p>
      <p <?=P_STYLE_TEXT?> ><a href="#announcements">Announcements</a></p>
      <hr>

      <p><a name="article1"></a></p>
      <table width="100" border="0" align="right" cellpadding="4" cellspacing="2">
        <tr>
          <td width="450"><img src="images/pollinator1.gif" width="100" height="80"></td>
        </tr>
        <tr>
          <td><img src="images/pollinator4.gif" width="100" height="114"></td>
        </tr>
        <tr>
          <td><img src="images/pollinator3.gif" width="100" height="107"></td>
        </tr>
        <tr>
          <td><img src="images/pollinator2.gif" width="100" height="87"></td>
        </tr>
      </table>
      <p <?=P_STYLE_TITLE?> ><b>Summer is here. Make it count!<br>
        Join the Pollination Canada Buzz</b></p>
      <p <?=P_STYLE_TEXT?> >You
        want to get involved in conserving insect pollinators and their habitat,
        but don&#8217;t know how? Seeds of Diversity's<em> Pollination Canada</em>
        program now provides you with the tools to make a difference! Join this
        new citizen science program that engages the Canadian public to participate
        in Canada&#8217;s largest survey of insect pollinators.</p>
      <p <?=P_STYLE_TEXT?> >Discover a whole new ecosystem in your backyard!</p>
      <p <?=P_STYLE_TEXT?> >The heart of the
        program is actual monitoring of insect populations and diversity. By observing
        pollinators in gardens, local parks, along country roads, basically anywhere
        flowers are growing, and then sending in these observations, you can help
        scientists to better understand the crucial relationships between pollinators,
        ecosystems, plant diversity, and human activity.</p>
      <p <?=P_STYLE_TEXT?> >The
        Pollinator Observer&#8217;s Manual and other training material are offered
        free of charge on the Pollination Canada website at
        <a href="http://www.pollinationcanada.ca" target="_blank">www.pollinationcanada.ca.</a></p>


    <? $bullDraw->backToTop(); ?>


      <p><a name="article2"></a></p>
      <table width="112" border="0" align="right" cellpadding="4" cellspacing="2">
        <tr>
          <td width="450"><img src="images/observer.gif" width="100" height="106"></td>
        </tr>
      </table>
      <p <?=P_STYLE_TITLE?> ><b>Try a beginner observation today!</b></p>
      <p <?=P_STYLE_TEXT?> >The Pollination Canada monitoring program uses a field-tested set of forms
        to guide you through a systematic observation session of flowers and pollinators.</p>
      <p <?=P_STYLE_TEXT?> >A
        complete Observer's Kit is available at <a href="http://www.pollinationcanada.ca" target="_blank">www.pollinationcanada.ca</a>.
        </p>
      <p <?=P_STYLE_TEXT?> >For now, try this
        simplified observation. Look in a garden, a meadow, or any place nearby
        where flowers are growing.</p>
      <p <?=P_STYLE_TEXT?> >See if you can spot pollinators in action.</p>
      <table width="100%" border="1" cellspacing="0" cellpadding="5">
        <tr bgcolor="#006633">
          <td colspan="2"> <p <?=P_STYLE_TEXT?> ><font color="#FFFFFF"># of insects</font></p></td>
          <td colspan="2"> <p <?=P_STYLE_TEXT?> ><font color="#FFFFFF"># of insects</font></p></td>
        </tr>
        <tr>
          <td width="8%">&nbsp;</td>
          <td width="45%"><p <?=P_STYLE_TEXT?> >Bees</p></td>
          <td width="6%">&nbsp;</td>
          <td width="41%"><p <?=P_STYLE_TEXT?> >Wasps</p></td>
        </tr>
        <tr>
          <td>&nbsp;</td>
          <td><p <?=P_STYLE_TEXT?> >Butterflies / moths</p></td>
          <td>&nbsp;</td>
          <td><p <?=P_STYLE_TEXT?> >Flies</p></td>
        </tr>
        <tr>
          <td>&nbsp;</td>
          <td><p <?=P_STYLE_TEXT?> >Beetles</p></td>
          <td>&nbsp;</td>
          <td><p <?=P_STYLE_TEXT?> >Other / not sure</p></td>
        </tr>
        <tr>
          <td colspan="4"><p <?=P_STYLE_TEXT?> >Location:</p></td>
        </tr>
        <tr>
          <td colspan="4"><p <?=P_STYLE_TEXT?> >Date and time:</p></td>
        </tr>
        <tr bgcolor="#006633">
          <td colspan="4"><p <?=P_STYLE_TEXT?> ><font color="#FFFFFF">Send me more information about Pollination Canada</font></p></td>
        </tr>
        <tr>
          <td colspan="4"><p <?=P_STYLE_TEXT?> >Name:</p></td>
        </tr>
        <tr>
          <td colspan="4"><p <?=P_STYLE_TEXT?> >Address:</p></td>
        </tr>
        <tr>
          <td colspan="4"><p <?=P_STYLE_TEXT?> >Email:</p></td>
        </tr>
      </table>


    <? $bullDraw->backToTop(); ?>


      <p <?=P_STYLE_TEXT?> ><a name="announcements"></a>In
        2003, Seeds of Diversity Canada and Environment Canada&#8217;s Ecological
        Monitoring and Assessment Network Coordinating Office (EMANCO) embarked
        on a joint venture to address the alarming lack of appreciation and knowledge
        about native bee species and other pollinators. Together they created
        a new citizen science program that engages the Canadian public in a nationwide
        survey of insect pollinators.</p>
      <p <?=P_STYLE_TEXT?> >Today,
        the Pollination Canada program is a network of educational, agricultural,
        and environmental institutions across Canada who lead the way in pollinator
        education and conservation.</p>
      <p <?=P_STYLE_TEXT?> >Since
        its inception, a growing number of partners have joined Pollination Canada
        by offering Pollination Canada educational materials to their staff, volunteers,
        members, and visitors, and many have integrated the Pollination Canada
        program in their programs.
        </p>


    <? $bullDraw->backToTop( false ); ?>


<?
//  </DIV>
}




?>
