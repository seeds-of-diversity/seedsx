<?
include( STDINC."SEEDWiki.php" );

class BulletinDraw extends SEEDWiki {
    var $sMonth, $sYear, $sIssueDigits, $lang;

    function BulletinDraw( $sMonth, $sYear, $sIssueDigits, $lang = 'EN' )
    /********************************************************************
     */
    {
        $this->sMonth        = $sMonth;
        $this->sYear         = $sYear;
        $this->sIssueDigits  = $sIssueDigits;
        $this->lang          = $lang;

        $varList['month'] = $sMonth;
        $varList['monthUpper'] = strtoupper( $sMonth );
        $varList['year'] = $sYear;
        $varList['lang'] = ($this->lang=="FR" ? "fr" : "en");

        $varList['altPage'] = ($this->lang=="FR"
                                  ? "http://www.seeds.ca/bulletin/{$this->sIssueDigits}/{$this->sIssueDigits}en.php"
                                  : "http://www.semences.ca/bulletin/{$this->sIssueDigits}/{$this->sIssueDigits}fr.php");
        $varList['title'] = ($this->lang=="FR"
                                  ? "Semences du patrimoine Canada - e-bulletin"
                                  : "Seeds of Diversity Canada - e-newsletter");
        $varList['map1'] =  ($this->lang=="FR"
                                  ? "<area shape='rect' coords='470,123,667,158' href='http://www.semences.ca/mbr/membre.php' target='_blank'>"
                                   ."<area shape='rect' coords='235,128,325,157' href='http://www.seeds.ca' target='_blank'>"
                                   ."<area shape='rect' coords='330,128,435,157' href='http://www.semences.ca' target='_blank'>"

                                  : "<area shape='rect' coords='569,123,667,158' href='http://www.seeds.ca/mbr/member.php' target='_blank'>"
                                   ."<area shape='rect' coords='276,128,365,157' href='http://www.seeds.ca' target='_blank'>"
                                   ."<area shape='rect' coords='370,128,480,157' href='http://www.semences.ca' target='_blank'>");
        $varList['contact'] = ($this->lang=="FR"
                                  ? "Contactez nous"
                                  : "Contact");
        $varList['address'] = ($this->lang=="FR"
                                  ? "<br>B.P. 36, Station Q"
                                   ."<br>Toronto, ON"
                                   ."<br>M4T 2L7"
                                   ."<br>1-866-509-7333"
                                   ."<br>courriel@semences.ca"
                                  : "<br>P.O. Box 36, Stn Q"
                                   ."<br>Toronto, ON"
                                   ."<br>M4T 2L7"
                                   ."<br>1-866-509-SEED"
                                   ."<br>mail@seeds.ca");

        $varList['privacy'] = ($this->lang=="FR"
                                  ? "<FONT color='#A4D4FF'>La protection de votre information personnel est importante pour nous!</FONT>"
                                   ."<BR>Vous recevez ce courriel car vous êtes un membre de Semences du patrimoine, ou parce que "
                                   ."vous vous êtes inscrit sur notre site Internet afin de bénéficier de ce e-bulletin gratuit. "
                                   ."<BR><BR>"
                                   ."Si vous souhaitez être retiré de cette liste d'expédition, SVP rédigez un courriel à "
//              .SEEDStd_EmailAddress( "ebulletin", "semences.ca", NULL, array("subject"=>"désabonnement"),
//                                     "style='text-decoration:none; color:A4D4FF'" )
                                   ."<A HREF='mailto:ebulletin@semences.ca?subject=desabonnement' style='text-decoration:none; color:A4D4FF'>"
                                   ."ebulletin@semences.ca</A>"
                                   ." en demandant un \"désabonnement\"."
                                   ."<BR><BR>"
                                   ."Les adresses électroniques des membres, de même que toutes les informations relatives aux membres, "
                                   ."sont traitées confidentiellement. Nous ne procurons jamais, ni ne vendons ou échangeons l'information "
                                   ."relative à nos membres à d'autres organismes, compagnies, ou individus."

                                  : "<FONT color='#A4D4FF'>We Respect Your Privacy!</FONT>"
                                   ."<BR>You have received this  e-Bulletin because you are a current or past member of Seeds of Diversity, or because you "
                                   ."subscribed to this free service on our web site."
                                   ."<BR><BR>"
                                   ."If you do not wish to receive future bulletins, please send an email to "
// I'd prefer this for the online pages, but I don't know whether Javascript can be emailed.
//              .SEEDStd_EmailAddress( "ebulletin", "seeds.ca", NULL, array("subject"=>"unsubscribe"),
//                                     "style='text-decoration:none; color:A4D4FF'" )
                                   ."<A HREF='mailto:ebulletin@seeds.ca?subject=unsubscribe' style='text-decoration:none; color:A4D4FF'>"
                                   ."ebulletin@seeds.ca</A>"
                                   ." asking to \"unsubscribe\"."
                                   ."<BR><BR>"
                                   ."Seeds of Diversity never exchanges, sells, or shares its email list with any other organisation, "
                                   ."company, or individual. Your email address is completely confidential.");
        $varList['credits'] =  ($this->lang=="FR"
                                  ? "<P align='left'><font color='#339933' size='1'>Conception par Allison Prindiville</font></p>"
                                   ."<P align='left'><font color='#339933' size='1'>&copy; {$this->sYear} Programme semencier du patrimoine Canada</p>"
                                  : "<P align='left'><font color='#339933' size='1'>Design by Allison Prindiville</font></p>"
                                   ."<P align='left'><font color='#339933' size='1'>Copyright &copy; {$this->sYear} Seeds of Diversity Canada</p>");

        $varList['happytext'] =  ($this->lang=="FR"
                                  ? "Semences du patrimoine n'est pas seulement qu'une collection de semences. C'est un réseau de gens qui "
                                   ."cultivent, échangent, apprennent, et qui prennent grand plaisir à partager les merveilles du patrimoine horticole."
                                  : "Seeds of Diversity is not just a collection of seeds. It is a network of people growing, exchanging, "
                                   ."learning, and delighting in the wonders of our shared horticultural inheritance.");

        $this->SEEDWiki( array(), $varList );
    }

    function Draw()
    /**************
     */
    {
        global $Template1;

        echo $this->WikiTranslate( $Template1 );
    }

    function ProcessTag( $raTag )
    /****************************
        Overrides SEEDWiki::ProcessTag to implement [[Draw:Sidebar]] and [[Draw:Body]]
     */
    {
        if( $raTag['tag'] == "Draw" ) {
            if( $raTag['parms'][0] == "Sidebar" )  return( doSideBarText($this) );
            if( $raTag['parms'][0] == "Body" )     return( doMainText($this) );

        } else {
            return( SEEDWiki::ProcessTag( $raTag ) );
        }
    }

    function backToTop()
    /***********************
     */
    {
        echo "<p align='left'><font color='#000000' size='1' face='Arial, Helvetica, sans-serif'>"
            ."<a href='#top'>".($this->lang=="FR" ? "Revenir au début" : "Back to the top")."</a></font></p>"
            ."<p align='left'>&nbsp;</p><hr align='left'>";
    }
}



$Template1 = <<<MyTemplate1

<html><head>
<title>[[Var:title]] [[Var:month]] [[Var:year]]</title>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
</head><body>

<!-- header image -->
<table cellpadding=0 cellspacing=0 border=0>
<tr><td colspan=2>
<img src='../../img/logo/bulletin01a_[[Var:lang]].png' width='666' height='154' border='0' usemap='#Map1'>
<map name='Map1'>
  <area shape='rect' coords=' 50, 60,530, 90' href='http://www.seeds.ca' target='_blank'>
  <area shape='rect' coords=' 50, 95,530,125' href='http://www.semences.ca' target='_blank'>
  [[Var:map1]]
</map>
</td></tr>
<tr><td bgcolor='77b377' width='162'>
<P style='color:#397a37; font-size:15pt; font-weight:bold; text-align:center;
          font-family:Antique Oakland,Geneva,Verdana,Arial,Helvetica,sans-serif'>
[[Var:monthUpper]]<BR>[[Var:year]]</font><br>
<A href='[[Var:altPage]]'><img src='../img/seeds_header3_[[Var:lang]].gif' border='0'></A>
</P></td>
<td width='504'>
<img src='../img/seeds_header2.gif' width='504' height='96' border='0'>
</td></tr></table>

<!-- Table1 -->
<table width='666' border='0' cellspacing='0' cellpadding='8' style='text-align: left'>
<tr>

<!-- Sidebar -->
<td width='146' align='left' valign='top' bgcolor='#c8ecc4' style='vertical-align: top'>
[[Draw:Sidebar]]
<P align='left'><font color='#000000' size='2' face='Arial, Helvetica, sans-serif'>
<strong><font color='#336633'>[[Var:contact]]</font></strong>
[[Var:address]]
</font></P>

<!-- Privacy -->
<TABLE border=0 cellpadding=10 width=146><TR><TD bgcolor='#205285'>
<FONT color='#FFFFFF' face='Arial,Helvetica,sans-serif' size=1>
[[Var:privacy]]
</FONT></TD></TR></TABLE>

<!-- Credits -->
[[Var:credits]]
</TD> <!-- Sidebar end -->

<!-- Main box -->
<TD style='vertical-align: top'>
<P align='left'><em><font color='#000000' size='2' face='Arial, Helvetica, sans-serif'>&#8220;[[Var:happytext]]&#8221;</font></em></P>
<hr>
[[Draw:Body]]
</TD></TR></TABLE> <!-- Table1 end -->
</BODY></HTML>

MyTemplate1;

?>
