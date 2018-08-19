<?

// Pollination Canada Bulletin Template


// 4x4 table, first col has rowspan=2 containing static partners list.
// Second col has header graphic in first row, everything else in second row.
// Header/Footer take care of the 4x4 table, everything except content of bottom-right cell.
//
// Bottom-right cell is divided into a 2x3 table, first col has rowspan=3


class BulletinDraw {
    var $sMonth, $sYear, $sIssueDigits, $lang;

    function BulletinDraw( $sMonth, $sYear, $sIssueDigits, $lang = 'EN' )
    /********************************************************************
     */
    {
        $this->sMonth        = $sMonth;
        $this->sYear         = $sYear;
        $this->sIssueDigits  = $sIssueDigits;
        $this->lang          = $lang;
    }


    function Template()
    /*******************
     */
    {
        $sLangMark     = ($this->lang=="FR" ? "fr" : "en");
        $sPageOpposite = ($this->lang=="FR" ? "http://www.seeds.ca/bulletin/{$this->sIssueDigits}/{$this->sIssueDigits}en.php"
                                            : "http://www.semences.ca/bulletin/{$this->sIssueDigits}/{$this->sIssueDigits}fr.php");

        $s = "<html><head>"
            ."<title>".($this->lang == "FR" ? "Pollinisation Canada - e-bulletin" :
                                              "Pollination Canada - e-newsletter")
            ." {$this->sMonth} {$this->sYear}</title>"
            ."<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>"
            ."</head>"
            ."<body bgcolor='#FFFFFF' link='#666633' vlink='#666633' alink='#0000FF' leftmargin='0' topmargin='0' marginwidth='0' marginheight='0'>";

// Keep the following in non-echoed form, so editor shows table nesting
$s .= <<<TemplateStr1

<!-- 4x4 table, first col has rowspan=2 -->
<table width="750" height="100%" border="0" align="center" cellpadding="0" cellspacing="0" bordercolor="#FFCC66" bgcolor="#FFCC33">
  <tr>
<!-- first col has rowspan=2 -->
    <td width="166" rowspan="2" valign="top" bgcolor="#FFCC00"><div align="right">
        <!-- Partners -->
        <table width="150" border="0" cellspacing="0" cellpadding="8">
          <tr>
            <td><p align="left"><img src="../img/fly-1.gif" width="150" height="81"></p>
              <p align="left"><font color="#990066" size="3" face="Verdana, Arial, Helvetica, sans-serif">
<!-- TRANSLATE -->
              <b><strong>Partners</strong></b></font></p>
              <p><font color="#666633" size="2" face="Verdana, Arial, Helvetica, sans-serif">
                <a href="http://www.calhort.org" target="_blank">Calgary Horticultural Society</a><br>
                <br>
                <a href="http://www.nature.ca" target="_blank">Canadian Museum of Nature</a><br>
                <br>
                Canadian Pollinator Protection Initiative<br>
                <br>
                <a href="http://www.everdale.org" target="_blank">Everdale Environmental Learning Centre</a><br>
                <br>
<!-- Fix link on FSRN -->
                Food Security Research Network</font></p>
              <p><font color="#666633" size="2" face="Verdana, Arial, Helvetica, sans-serif"><img src="../img/fly-2.gif" width="150" height="98"><br>
                <br>
                <a href="http://www.ie.uottawa.ca/English/welcome.html" target="_blank">Green Campus (University of Ottawa)</a><br>
                <br>
                <a href="http://www.greenteacher.com" target="_blank">Green Teacher</a><br>
                <br>
                Green Thumbs<br>
                Growing Kids, a project of the<br>
                Toronto Kiwanis Boys and Girls Clubs<br>
                <br>
                <a href="http://botanicalgardens.acadiau.ca" target="_blank">Harriet
                Irving Botanical Gardens</a><br>
                <br>
                Jack's Lake Cottager's Association<br>
                </font><font color="#666633" size="2" face="Verdana, Arial, Helvetica, sans-serif"><br>
                <img src="../img/Picture32.gif" width="150" height="92"> </font></p>
              <p><a href="http://www.canadanursery.com" target="_blank"><font color="#666633" size="2" face="Verdana, Arial, Helvetica, sans-serif">Landscape
                Manitoba</font></a><font color="#666633" size="2" face="Verdana, Arial, Helvetica, sans-serif"><br>
                <br>
                <a href="http://www.musee-abeille.com" target="_blank">Mus&eacute;e
                de l'Abeille</a><br>
                <br>
                Niagara Falls <br>
                Nature Club<br>
                <br>
                <a href="http://www.ontariobee.com" target="_blank">Ontario Beekeepers'
                Association</a><br>
                <br>
                <a href="http://www.ottawariverinstitute.ca" target="_blank">Ottawa
                River Institute</a></font><font color="#666633" size="2" face="Verdana, Arial, Helvetica, sans-serif"><br>
                <br>
                <a href="http://www.raresites.org" target="_blank">rare Charitable
                Research Reserve</a></font></p>
              <p><font color="#666633" size="2" face="Verdana, Arial, Helvetica, sans-serif"><a href="http://www.rbg.ca" target="_blank">Royal
                Botanical Gardens</a><br>
                <br>
                <a href="http://sciencenorth.on.ca" target="_blank">Science North</a><br>
                <br>
                <a href="http://www.mala.ca/MilnerGardens/" target="_blank">Shoots
                with Roots <br>
                at Milner Gardens <br>
                &amp; Woodland</a><br>
                <br>
                <a href="http://www.sierraclub.ca" target="_blank">Sierra Club
                of Canada</a><br>
                <br>
                <a href="http://www.coastbotanicalgarden.org/" target="_parent">Sunshine
                Coast Botanical Garden Society</a><br>
                <br>
                <a href="http://www.xerces.org" target="_blank">The Xerces Society
                for Invertebrate Conservation</a><br>
                <br>
                Upper Credit Field Naturalist Club<br>
                <br>
                <a href="http://www.city.vancouver.bc.ca/parks/parks/vandusen/website/index.htm" target="_blank">VanDusen
                Botanical Garden</a><br>
                <br>
                <a href="http://www.vicnhs.bc.ca" target="_blank">Victoria Natural
                History Society</a><br>
                <br>
                <a href="http://www.wascana.sk.ca" target="_blank">Wascana Centre</a><br>
                <br>
                <a href="http://www.wingsofparadise.com" target="_blank">Wings of Paradise</a></font></p>

                <br>
                <br>
TemplateStr1;

        if( $this->lang == "FR" ) {
            $s .= "<p align='left'><font color='#666633' size='1'>Conception par Allison Prindiville</font></p>"
                 ."<p align='left'><font color='#666633' size='1'>Photos &copy Jim Dyer</font></p>"
                 ."<p align='left'><font color='#666633' size='1'>&copy; {$this->sYear} Programme semencier du patrimoine Canada</p>";
        } else {
            $s .= "<p align='left'><font color='#666633' size='1'>Design by Allison Prindiville</font></p>"
                 ."<p align='left'><font color='#666633' size='1'>Photos &copy Jim Dyer</font></p>"
                 ."<p align='left'><font color='#666633' size='1'>Copyright &copy; {$this->sYear} Seeds of Diversity Canada</p>";
        }

$s .= "
              </td>
          </tr>
        </table>
        <p align='left'>&nbsp;</p>
      </div></td>
    <!-- top-right cell contains header image -->
    <td height='285' valign='top' bgcolor='#FFFFFF'>
      <div align='center'>
        <table width='650' border='0' align='left' cellpadding='5' cellspacing='0'>
          <tr>
            <td bgcolor='#FFFFFF'>
              <div align='left'><img src='../img/header01_en.gif' width='650' height='275'></div></td>
          </tr>
        </table>
      </div></td>
  </tr>
";
$s .= <<<TemplateStr2
  <!-- bottom-right cell contains the newsletter content, decoration, privacy statement
  <tr>
    <td width="684" height="1575" valign="top" nowrap>
      <table width="100%" height="100" border="0" cellpadding="5" cellspacing="0" bgcolor="#FFFFFF">
        <tr>
          <td width="75%">
            <table width="650" border="0" cellspacing="0" cellpadding="5">
              <tr>
                <td valign='top' width="540" rowspan="3">
                [[BODY]]
                  <hr>
                  <BR/><BR/>
                  <p align='center'><font size="2" face="Verdana, Arial, Helvetica, sans-serif">
                  Pollination Canada is a joint venture of<BR/>
                  <A HREF='http://www.seeds.ca'>Seeds of Diversity Canada</A> and <BR/>
                  <A href='http://www.eman-rese.ca'>Environment Canada's Ecological Monitoring<BR/>
                  and Assessment Network Coordinating Office</A>.
                  <BR/><BR/>
                  <BR/><BR/>
                  <A href='http://www.seeds.ca/'><IMG src='../img/sodlogo.gif' border=0 alt='Seeds of Diversity Canada'></A>
                  <BR/><BR/>
                  <A href='http://www.eman-rese.ca/'><IMG src='../img/EMAN_colour_red_text_with_title.gif' border=0 alt='EMAN/RESE'></A>
                  </font></p>
                    </td>
                <td width="90" bgcolor="#FFFFFF"><div align="center"><img src="../img/bee.gif" width="150" height="30"></div>
                </td>
              </tr>
              <tr>
                <td width="90" height="545" bgcolor="#FFFFFF">
                  <table width="153" border="0" align="center" cellpadding="8" cellspacing="0" bordercolor="#000000">
                    <tr>
                      <td width="153" height="262" bgcolor="#FFFFCC">
                      [[SIDEBAR]]
                        <div align="center"></div></td>
                    </tr>
                  </table>
                  <p><img src="../img/bee-swirl.gif" width="150" height="154"></p>
                  </td>
              </tr>
              <tr>
                <td bgcolor="#FFFFFF"> <div align="center">
                    <table width="150" height="684" border="0" cellpadding="5" cellspacing="0">
                      <tr>
                        <td height="181"><img src="../img/fly-3.gif" width="150" height="171"></td>
                      </tr>
                      <tr>
                        <td><img src="../img/factoid_fly-1.gif" width="150" height="142"></td>
                      </tr>
                      <tr>
                        <td><img src="../img/Picture25.gif" width="150" height="160"></td>
                      </tr>
                      <tr>
                        <td><img src="../img/Picture29.gif" width="150" height="150"></td>
                      </tr>
                    </table>
                  </div></td>
              </tr>
            </table>
            <table width="650" border="0" cellspacing="0" cellpadding="5">
              <tr>
                <td colspan="2"><hr></td>
              </tr>
              <tr>
                <td width="89"><img src="../img/Logo_web_colour_eng_vect_sm.gif" width="150" height="123"></td>
                <td width="541" bgcolor="#FFFFFF"><p>&nbsp;</p>
                  <p><strong><font color="#990066" size="2" face="Verdana, Arial, Helvetica, sans-serif"><b>Privacy
                    Policy<br>
                    </b></font></strong><font size="1" face="Verdana, Arial, Helvetica, sans-serif">You
                    have received this e-newsletter because you are a Pollination
                    Canada Partner. <br>
                    If you do not wish to receive future bulletins, please send
                    an email to <a href="mailto:newsletter@pollinationcanada.ca">newsletter@pollinationcanada.ca</a>
                    asking to &#8220;unsubscribe&#8221;. Seeds of Diversity Canada and Pollination Canada never
                    exchange, sell or share email lists with any other organisation,
                    company or individual. Your email address is completely confidential.</font></p></td>
              </tr>
              <tr bgcolor="#990066">
                <td colspan="2"><font color="#FFFFFF" size="2" face="Arial, Helvetica, sans-serif"><strong>Pollination
                  Canada, c/o Seeds of Diversity P.O. Box 36, Stn Q, Toronto,
                  ON M4T 2L7</strong></font></td>
              </tr>
            </table>
          </td>
        </tr>
      </table>
TemplateStr2;

        $s .= "</td></tr></table>"  // end of 4x4 table
             ."</body></html>";

        return( $s );
    }
}

?>
