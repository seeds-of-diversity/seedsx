<?
// e-Bulletin September 2007 English

define("SITEROOT","../../");
include(SITEROOT."site.php");
include( "../_bullDraw01.php" );

$bullDraw = new BulletinDraw( "September", "2007", "0709", "EN" );
$bullDraw->Draw();


function doSideBarText( $bullDraw )
/**********************************
 */
{
    $s = "
      <p align='left'><font color='#336633' size='2' face='Geneva, Arial, Helvetica, sans-serif'><b><strong>Garlic
        Tip</strong></b></font><font size='1' face='Geneva, Arial, Helvetica, sans-serif'><br>
        <br>
        <img src='garlic01.jpg' width='145'> <br><br>
        You might expect garlic to keep well in the fridge, or at low temperatures, but this just encourages it to sprout.
        Remember that you plant garlic cloves in October; cold temperatures tell them it's time to grow!
        <BR><BR>
        Store garlic in breathable bags at room temperature, in a dry, dark place. The hall closet is usually ideal!
        </font></p>
      <p align='left'><font size='1' face='Geneva, Arial, Helvetica, sans-serif'>Visit
        <a href='http://www.canadiangarlic.ca' target='_blank'>www.canadiangarlic.ca</a>
        to learn more about Seeds of Diversity's <em>Great Canadian Garlic Collection</em> and how to get involved.</font></p>
        ";

    return( $s );
}


function doMainText( $bullDraw )
/*******************************
 */
{
    $styleTitle = "style='color:#397a37; font-size: 13pt; font-family:Geneva, Arial, Helvetica, sans-serif;'";
    $styleText  = "style='color:#000000; font-size: 10pt; font-family:Arial, Helvetica, sans-serif;'";

    $s = "
      <P $styleTitle><b>Wanted: 50 Garlic Growers for Seeds of Diversity's Great Canadian Garlic Collection project!</b></p>

    <P $styleText>
    Brian Woods, the Project Coordinator for Seeds of Diversity's Great Canadian Garlic Collection project has quite a nice crop
    of new garlic in Prince Edward County.  It’s a joy for him to lead the garlic project and grow ten varieties
    of his own, nine of which came to him already named; the tenth he just called Chuck. Chuck arrived as an un-named
    gift from Gerald, Brian’s neighbour up on Chuckery Hill.  Chuck and the other nine are part of the
    58 garlic varieties that are grown nationwide by the 111 current participants in the project!
    </P>

    <P align='center'><A HREF='http://www.canadiangarlic.ca'><IMG border=0 src='logo_GCGC_en.gif'></A>
    <TABLE align=center width='300'><TR><TD>
    <P $styleText>Seeds of Diversity member-volunteers throughout Canada receive free samples of diverse varieties of garlic
    each year.  They grow each variety for at least two years and fill out a simple standardized form that records
    their garlics' characteristics.</TD></TR></TABLE>
    </P>

    <P $styleText>
    The project is so successful, and our garlic supply so bountiful, that we would like to welcome 50 new growers
    this year.  And we hope that you’ll be one of them.
    </P>

    <P $styleTitle>
    Here's what we ask of Garlic Growers:
    </P>

    <P $styleText>
    <UL $styleText>
    <LI>Be a member of Seeds of Diversity (if you’re not sure whether your membership is still valid, contact our office at office@seeds.ca).</LI>
    <LI>Grow two or more varieties of garlic for two years (choose from the list below for your
        free seed garlic, while quantities last).<BR>
        All Garlic Growers must grow \"Music\", a very common variety. It acts as a control for the project,
        allowing us to compare that variety’s performance across growing regions and soils, to better interpret
        differences in results from other varieties.<BR>
<!-- TRANSLATE -->
        We supply three bulbs of each variety.  <B>Two varieties will fill about 15 square feet of your garden.</B>
<!-- /TRANSLATE -->
        </LI>
    <LI>Fill out the observation form for each variety each year (including your own varieties if you have any).</LI>
    </UL>
    </P>

    <P $styleText>
    New members will receive their first garlic in time for planting in October.
    </P>

    <P $styleText>
    Check out <A HREF='http://www.canadiangarlic.ca'>www.canadiangarlic.ca</A> for more information on the
    Great Canadian Garlic Collection project, including your role as a grower, information about garlic and how
    to grow it, and to download your Garlic Observation Form right now.
    </P>

    <HR/>

    <P $styleTitle>
    Interested in joining Seeds of Diversity to explore and document the many varieties of garlic grown in Canada?
    </P>
    <P $styleText>
    Contact Brian Woods, Project Coordinator, at <A HREF='mailto:garlic@seeds.ca'>garlic@seeds.ca</A> or
    <A HREF='mailto:ail@semences.ca'>ail@semences.ca</A>.
    </P>

    <TABLE border=0 cellspacing=20><TR><TD colspan=3 $styleText>
    <CENTER><DIV $styleTitle><strong>Varieties available for first time Garlic Growers</DIV>
    <BR>
    Please choose up to three varieties and reply to Brian Woods
    at garlic@seeds.ca.</strong>
    <BR>
    <I>Note: One of your varieties must be Music, for comparison with all other varieties. If you
    are already growing Music, please let us know.</I></CENTER>
    </TD></TR>
    <TR>
    <TD colspan=3>
    <B>Music</B> -- request this variety unless you already grow it
    </TD>
    </TR>
    <TR>
    <TD valign='top'>
        Alison's            <BR>
        Asian Tempest       <BR>
        Baba Franchuk's     <BR>
        Chesnok Red         <BR>
        China Rose          <BR>
        Carpathian          <BR>
        Denman              <BR>
        F7                  <BR>
        F21                 <BR>
        F23                 <BR>
        Fauquier            <BR>
<!--    French              <BR>  -->
        Georgian Crystal    <BR>
        German Red          <BR>
        Inchellium Red      <BR>
        Israeli             <BR>
        Italian             <BR>
        Khabar              <BR>
    </TD>
    <TD valign='top'>
        Kiev                <BR>
        Killarney           <BR>
        Korean Purple       <BR>
        Limburg             <BR>
        Malpasse            <BR>
        Mediterranean       <BR>
        Montana Giant       <BR>
        Montana Roja        <BR>
        Moravia             <BR>
        Mountaintop         <BR>
        Nootka Rose         <BR>
<!--    Northern Quebec     <BR>  -->
<!--    Oregon Blue         <BR>  -->
        Persian Star        <BR>
        Polish              <BR>
        Purple Max          <BR>
        Puslinch            <BR>
        Racey               <BR>
    </TD>
    <TD valign='top'>
        Red Italian         <BR>
        Red Rezan           <BR>
        Romanian Red        <BR>
<!--    Russian             <BR>  -->
        Salt Spring         <BR>
        Siberian            <BR>
        Sicilian Gold       <BR>
        Sicilian White      <BR>
        Spanish             <BR>
        Spanish Roja        <BR>
        Stein               <BR>
        Sweet Haven         <BR>
        Thai                <BR>
<!--    Tibetan             <BR>  -->
        Transylvanian       <BR>
        Ukrainian Mavniv    <BR>
        Yugoslavian         <BR>
    </TD>
    </TR></TABLE>
    ";

    return( $s );
}

?>
