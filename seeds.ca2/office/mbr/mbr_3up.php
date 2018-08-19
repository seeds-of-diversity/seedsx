<?php

// the address position is just right if it extends to 5 lines, could be 4 mm lower for most and that would probably not cut off the bottom if it's 5 lines

define( "SITEROOT", "../../" );
include_once( SITEROOT."site2.php" );
include_once( SEEDCOMMON."console/console01.php" );
include_once( SEEDCOMMON."mbr/mbrCommon.php" );
include_once( SEEDCORE."SEEDPrint.php" );


list($kfdb,$sess,$lang) = SiteStartSessionAccount( array( 'MBR'=>'R' ) );

$o3UpDonors = new Mbr3UpDonors( $kfdb );
$o3UpMbr    = new Mbr3UpMemberRenewals( $kfdb );
$oPrint     = new SEEDPrint3UpHTML();


$sMod = SEEDInput_Smart( 'module', array( 'donor', 'member') );
$o3Up = $sMod == 'donor' ? $o3UpDonors : $o3UpMbr;

$o3Up->Load();

$mode = $o3Up->GetMode();

if( $mode == '' || $mode == 'details' ) {
    /* Show the options form
     */
    $s = "<table><tr>"
            ."<td valign='top'><h2>Donations</h2>".$o3UpDonors->OptionsForm()."</td>"
            ."<td valign='top'><h2>Membership Renewals</h2>".$o3UpMbr->OptionsForm()."</td>"
            ."</tr></table>";

    if( $mode == 'details' ) {
        $s .= $o3Up->ShowDetails();
    }

    echo $s;

    exit;
}

/*
<div class='s_credit' style='position:absolute;right:0;top:1.25in;width:3.5in'>
  &#9744; Cheque (enclosed) &nbsp;&nbsp;&nbsp;&nbsp; &#9744; Visa &nbsp;&nbsp;&nbsp;&nbsp; &#9744; MasterCard<br/><br/>
  Credit card number:<span style='text-decoration: underline; white-space: pre;'>                                                        </span><br/><br/>
  Expiry date (month/year):<span style='text-decoration: underline; white-space: pre;'>                                               </span><br/><br/>
  Name on card:<span style='text-decoration: underline; white-space: pre;'>                                                                 </span><br/><br/><br/>
  Signature:<span style='text-decoration: underline; white-space: pre;'>                                                                        </span>
</div>
 */

$sTmpl = $o3Up->GetTemplate();
$raRows = $o3Up->GetRows();
$oPrint->Do3Up( $raRows, $sTmpl );

$raConsoleParms = array( 'bBootstrap'=>false );   // we want to control the CSS completely, thanks anyway Bootstrap

echo Console01Static::HTMLPage( $oPrint->GetBody(), $oPrint->GetHead(), "EN", $raConsoleParms );

class Mbr3UpDonors
{
    public $mode = "";

    private $kfdb;
    private $lang = "EN";

    private $raDonorEN, $raDonorFR, $raNonDonorEN, $raNonDonorFR;

    private $raDonor, $raNonDonor;

    function __construct( KeyFrameDB $kfdb )
    {
        $this->kfdb = $kfdb;
    }

    function GetMode()  { return( $this->mode ); }

    function GetRows()
    {
        switch( $this->mode ) {
            case '3UpDonors':     return( $this->raDonor );
            case '3UpNonDonors':  return( $this->raNonDonor );
        }
    }

    function OptionsForm()
    {
        $s = "<form>"
            ."<select name='mode'>"
                ."<option value='details'>details</option>"
                ."<option value='3UpDonors'>Donor slips</option>"
                ."<option value='3UpNonDonors'>Non-donor slips</option>"
            ."</select>"
            ."<br/><br/>"
            ."<select name='lang'>"
                ."<option value='EN'>English</option>"
                ."<option value='FR'>French</option>"
            ."</select>"
            ."<br/><br/>"
            ."<input type='hidden' name='module' value='donor'/>"
            ."<input type='submit'/>"
            ."</form>";

        return( $s );
    }

    function Load()
    {
        $this->mode = SEEDInput_Smart( 'mode', array( '', 'details', '3UpDonors', '3UpNonDonors' ) );
        $this->lang = SEEDInput_Smart( 'lang', array( 'EN', 'FR') );

        if( $this->mode == 'details' ) {
            $this->kfdb->SetDebug(2);
        }

// donation_date is the most recent, `donation` is the total donations for year(donation_date)
// so you can always say Thanks for your donation of `donation` in year(`donation_date`)" and mean the total for that year.
// And you can limit the list to `donation_date` < (date() - some reasonable margin of the recent past)

        $lEN = "lang<>'F'";
        $lFR = "lang='F'";
        $dYes = "donation_date is not null AND year(donation_date)>='2015'";    // recent donors - null check is required for the NOT(expr)
        $dNo = "NOT($dYes) AND year(expires)>='2015'";                          // recent members who are not donors
        $dGlobal = "_status='0' AND country='Canada' AND "
                  ."address IS NOT NULL AND address<>'' AND "   // address is blanked out if mail comes back RTS
                  ."NOT bNoDonorAppeals AND "
                  ."NOT(donation_date is not null AND donation_date>'2017-07-01')";

        $sCondDonorEN = "$dYes AND $lEN";
        $sCondDonorFR = "$dYes AND $lFR";
        $sCondNonDonorMemberEN = "$dNo AND $lEN";
        $sCondNonDonorMemberFR = "$dNo AND $lFR";

        $this->raDonorEN    = $this->kfdb->QueryRowsRA("SELECT * FROM seeds2.mbr_contacts WHERE $dGlobal AND $sCondDonorEN order by cast( donation as decimal),lastname,firstname" );
        $this->raDonorFR    = $this->kfdb->QueryRowsRA("SELECT * FROM seeds2.mbr_contacts WHERE $dGlobal AND $sCondDonorFR order by cast(donation as decimal),lastname,firstname" );
        $this->raNonDonorEN = $this->kfdb->QueryRowsRA("SELECT * FROM seeds2.mbr_contacts WHERE $dGlobal AND $sCondNonDonorMemberEN order by lastname,firstname" );
        $this->raNonDonorFR = $this->kfdb->QueryRowsRA("SELECT * FROM seeds2.mbr_contacts WHERE $dGlobal AND $sCondNonDonorMemberFR order by lastname,firstname" );

        $this->raDonor    = $this->lang=='EN' ? $this->raDonorEN    : $this->raDonorFR;
        $this->raNonDonor = $this->lang=='EN' ? $this->raNonDonorEN : $this->raNonDonorFR;

        foreach( $this->raDonor as &$ra ) {
            $ra['SEEDPrint:addressblock'] = utf8_encode(MbrDrawAddressBlockFromRA( $ra ));
        }
        foreach( $this->raNonDonor as &$ra ) {
            $ra['SEEDPrint:addressblock'] = utf8_encode(MbrDrawAddressBlockFromRA( $ra ));
        }
    }

    function ShowDetails()
    {
        $s = "<p>Donors English: ".count($this->raDonorEN)."</p>"
            ."<p>Donors French: ".count($this->raDonorFR)."</p>"
            ."<p>Non-donor Members English: ".count($this->raNonDonorEN)."</p>"
            ."<p>Non-donor Members French: ".count($this->raNonDonorFR)."</p>"
            ."<p>&nbsp</p>"
            ."<p>English: ".(count($this->raDonorEN)+count($this->raNonDonorEN))."</p>"
            ."<p>French: ".(count($this->raDonorFR)+count($this->raNonDonorFR))."</p>";

        $s .= "<h3>Donors</h3>"
             ."<table border='1'>"
             .SEEDCore_ArrayExpandRows( $this->raDonor, "<tr><td>[[firstname]] [[lastname]] [[company]]</td><td>[[donation]]</td><td>[[donation_date]]</td></tr>" )
             ."</table>"
             ."<h3>Non-Donors</h3>"
             ."<table border='1'>"
             .SEEDCore_ArrayExpandRows( $this->raNonDonor, "<tr><td>[[firstname]] [[lastname]] [[company]]</td><td>[[donation]]</td><td>[[donation_date]]</td></tr>" )
             ."</table>";

         return( $s );
    }

    function GetTemplate()
    {
        $lang = $this->lang;

$sTitle       = $lang=='EN' ? "Yes, I would like to help save Canadian seed diversity!"
                            : "Oui, je veux contribuer &agrave; sauvegarder la diversit&eacute; semenci&egrave;re du Canada!";
$sWantOneTime = $lang=='EN' ? "I want to make a one-time donation of" : "Je d&eacute;sire faire un don unique de";
$sWantMonthly = $lang=='EN' ? "I want to make a monthly donation of" : "Je d&eacute;sire faire un une contribution <u>mensuelle</u> de";
$sOther       = $lang=='EN' ? "Other" : "Autre";

$sRight       = $lang=='EN' ? "<p>Your charitable donation this year will help save hundreds of rare plant varieties next year.
  Seeds of Diversity will use your donation to find seeds that need rescuing, and organize seed savers across the country to grow them in 2018.</p>
  <p>You can also make your donation online at <b><u>www.seeds.ca/donate</u></b>.</p>"
                            : "<p>Votre don de charit&eacute; de cette ann&eacute;e aidera &agrave; sauver des centaines de vari&eacute;t&eacute;s rares l'an prochain.
  Semences du patrimoine utilisera votre don pour trouver des semences qui ont besoin d'&ecirc;tre secourues, et pour trouver des conservateurs de semences &agrave; travers le Canada afin de les cultiver en 2018.</p>
  <p>Vous pouvez &eacute;galement faire un don en ligne au <b><u>www.semences.ca/don</u></b>.</p>";

$sAddrChanged = $lang=='EN' ? "Has your address or contact information changed?"
                            : "Votre adresse ou vos coordonn&eacute;es ont-elles chang&eacute;?";
$sEmail       = $lang=='EN' ? "Email": "Courriel";
$sPhone       = $lang=='EN' ? "Phone": "T&eacute;l&eacute;phone";
$sMember      = $lang=='EN' ? "Member" : "Membre";

$sFooter      = $lang=='EN' ? "Seeds of Diversity is a registered charitable organization. We provide receipts for donations of $20 and over. Our charitable registration number is 89650 8150 RR0001."
                            : "Les Semences du patrimoine sont un organisme de bienfaisance enregistr&eacute;. Nous faisons parvenir un re&ccedil;u &agrave; fins d'imp&ocirc;t pour tous les dons de 20 $ et plus. Notre num&eacute;ro d'enregistrement est 89650 8157 RR0001";

//<img style='float:right;width:0.75in' src='http://seeds.ca/i/img/logo/logoA_v-en-bw-300x.png'/>
$s = "
<img style='position:absolute;top:0.125in;right:0.125in;width:0.75in' src='http://seeds.ca/i/img/logo/logoA_v-".($lang=='EN' ? "en":"fr")."-bw-300x.png'/>
<div class='s_title'>$sTitle</div>
<div class='s_form'>
  <table>
  <tr><td>&#9744; $sWantOneTime</td><td>&#9744; $20</td><td>&#9744; $50</td><td>&#9744; $100</td><td>&#9744; $200</td><td>&#9744; $sOther <span style='text-decoration: underline; white-space: pre;'>           </span></td></tr>
  <tr><td>&#9744; $sWantMonthly</td><td>&#9744; $10</td><td>&#9744; $20</td><td colspan='2'>&#9744; $sOther <span style='text-decoration: underline; white-space: pre;'>           </span></td></tr>
  </table>
</div>
<div class='s_right' style='position:absolute;right:0.125in;top:1.125in;width:4.25in'>
  $sRight
  <div style='border:1px solid #aaa;background-color:#f4f4f4;margin-left:0.75in;padding:0.125in'>
    <div>$sAddrChanged</div>
    <div style='margin-top:0.125in'>
    $sEmail: [[email]]<br/>
    $sPhone: [[phone]]</div>
  </div>
  <div style='font-size:8pt;margin-top:0.05in;float:right'>$sMember [[_key]]</div>
</div>
<div class='s_note' style='position:absolute;bottom:0.125in;left:0.325in;text-alignment:left'>
  $sFooter
</div>
";

        return( $s );
    }
}


class Mbr3UpMemberRenewals
{
    private $mode = "";

    private $kfdb;

    private $raMbr, $raMbrEN, $raMbrFR;

    function __construct( KeyFrameDB $kfdb )
    {
        $this->kfdb = $kfdb;
    }

    function GetMode()  { return( $this->mode ); }

    function GetRows()
    {
        return( $this->raMbr );
    }

    function OptionsForm()
    {
        $s = "<form>"
            ."<select name='mode'>"
                ."<option value='details'>details</option>"
                ."<option value='3Up'>Renewal slips</option>"
            ."</select>"
            ."<br/><br/>"
            ."<select name='lang'>"
                ."<option value='EN'>English</option>"
                ."<option value='FR'>French</option>"
            ."</select>"
            ."<br/><br/>"
            ."<input type='hidden' name='module' value='member'/>"
            ."<input type='submit'/>"
            ."</form>";

        return( $s );
    }

    function Load()
    {
        $this->mode = SEEDInput_Smart( 'mode', array( '', 'details', '3Up' ) );
        $this->lang = SEEDInput_Smart( 'lang', array( 'EN', 'FR') );

        if( $this->mode == 'details' ) {
            $this->kfdb->SetDebug(2);
        }

        $lEN = "lang<>'F'";
        $lFR = "lang='F'";
        $dGlobal = "_status='0' AND country='Canada' AND "
                  ."address IS NOT NULL AND address<>'' AND "   // address is blanked out if mail comes back RTS
                  ."NOT bNoDonorAppeals AND "                   // they probably see this as the same thing
                  ."expires IS NOT NULL AND year(expires) IN (2016,2017)";

        $this->raMbrEN = $this->kfdb->QueryRowsRA("SELECT * FROM seeds2.mbr_contacts WHERE $dGlobal AND $lEN order by lastname,firstname" );
        $this->raMbrFR = $this->kfdb->QueryRowsRA("SELECT * FROM seeds2.mbr_contacts WHERE $dGlobal AND $lFR order by lastname,firstname" );

        $this->raMbr   = $this->lang=='EN' ? $this->raMbrEN : $this->raMbrFR;

        foreach( $this->raMbr as &$ra ) {
            $ra['SEEDPrint:addressblock'] = utf8_encode(MbrDrawAddressBlockFromRA( $ra ));
        }
    }

    function ShowDetails()
    {
        $s = "";

        return( $s );
    }

    function GetTemplate()
    {
        $lang = $this->lang;

$sLocalStyle =
            "<style>"
           .".right_p {margin:0.02in 0pt;padding:0.02in 0pt;}"
           ."</style>";

$sTitle       = $lang=='EN' ? "Yes, please renew my membership to Seeds of Diversity!"
                            : "Oui, je d&eacute;sire renouveler mon abonnement aux Semences du patrimoine!";
$sForm = $lang=='EN'
            ? ("<div style='font-size:8pt;margin:0.05in'><i>Memberships include a subscription to our magazine, monthly e-bulletin and an online seed directory every year.</i></div>"
              ."<table style='font-size:11pt'>"
              ."<tr><td><b>&#9744; One year</b></td><td style='margin-left:0.5in'><b>$35</b></td><td style='padding-left:0.5in'>&#9744; Please send me a printed copy of the Member Seed Directory - <b>Add $10 <u>per year</u></b></td>"
              ."<tr><td><b>&#9744; Three year</b></td><td style='margin-left:0.5in'><b>$100</b></td><td>&nbsp;</td>"
              ."<tr><td><b>&#9744; Lifetime</b></td><td style='margin-left:0.5in'><b>$1000</b></td><td>&nbsp;</td>"
              ."</table>")
            : ("<div style='font-size:8pt;margin:0.05in'><i>L'adh&eacute;sion annuelle comprend un abonnement de la revue, l'e-bulletin mensuel, et l'acc&egrave;s en ligne au catalogue des semences.</i></div>"
              ."<table style='font-size:11pt'>"
              ."<tr><td><b>&#9744; Un an</b></td><td style='margin-left:0.5in'><b>35 $</b></td><td style='padding-left:0.5in'>&#9744; Je souhaite une copie papier du catalogue des semences - <b>10 $ <u>par ann&eacute;e</u></b></td>"
              ."<tr><td><b>&#9744; Trois ans</b></td><td style='margin-left:0.5in'><b>100 $</b></td><td>&nbsp;</td>"
              ."<tr><td><b>&#9744; &Agrave; vie</b></td><td style='margin-left:0.5in'><b>1000 $</b></td><td>&nbsp;</td>"
              ."</table>");

$sWantMonthly = $lang=='EN' ? "I want to make a monthly donation of" : "Je d&eacute;sire faire un une contribution <u>mensuelle</u> de";
$sOther       = $lang=='EN' ? "Other" : "Autre";

$sRight = $lang=='EN'
            ? ("<p class='right_p' style='font-weight:bold;'>Add a Donation</p>"
              ."<p class='right_p'>We count on your generosity to help protect Canada's unique plant diversity. "
              ."Membership fees only pay the cost of service to members. Please support our projects by adding a tax-receiptable donation.</p>"
              ."<p class='right_p' ><b>&#9744; I would like to add a one-time donation of $ _______</b></p>"
              ."<p class='right_p' style='font-size:7pt'>(Flip to the other side to make a monthly donation.)</p>")
            : ("<p class='right_p' style='font-weight:bold;'>Faites un don</p>"
              ."<p class='right_p'>Nous comptons sur votre g&eacute;n&eacute;rosit&eacute; pour contribuer &agrave; la sauvegarde "
              ."de notre diversit&eacute; horticole. Le montant de l'adh&eacute;sion ne couvre que"
              ."le service aux membres. Soutenez nos projets en faisant un don.</p>"
              ."<p class='right_p' ><b>&#9744; Je souhaite faire un don unique de $ _______</b></p>"
              ."<p class='right_p' style='font-size:7pt'>(Pour offrir des dons sur une base mensuelle, voir au verso.)</p>");

$sAddrChanged = $lang=='EN' ? "Has your address or contact information changed?"
                            : "Votre adresse ou vos coordonn&eacute;es ont-elles chang&eacute;?";
$sEmail       = $lang=='EN' ? "Email": "Courriel";
$sPhone       = $lang=='EN' ? "Phone": "T&eacute;l&eacute;phone";
$sMember      = $lang=='EN' ? "Member" : "Membre";

$sFooter      = $lang=='EN' ? "Seeds of Diversity is a registered charitable organization. We provide receipts for donations of $20 and over. Our charitable registration number is 89650 8150 RR0001."
                            : "Les Semences du patrimoine sont un organisme de bienfaisance enregistr&eacute;. Nous faisons parvenir un re&ccedil;u &agrave; fins d'imp&ocirc;t pour tous les dons de 20 $ et plus. Notre num&eacute;ro d'enregistrement est 89650 8157 RR0001";

//<img style='float:right;width:0.75in' src='http://seeds.ca/i/img/logo/logoA_v-en-bw-300x.png'/>
$sTmpl = $sLocalStyle."
<img style='position:absolute;top:0.125in;right:0.125in;width:0.75in' src='http://seeds.ca/i/img/logo/logoA_v-".($lang=='EN' ? "en":"fr")."-bw-300x.png'/>
<div class='s_title'>[[Var:sTitle]]</div>
<div class='s_form'>
  [[Var:sForm]]
</div>
<div class='s_right' style='position:absolute;right:0.125in;top:1.125in;width:4.25in'>
  [[Var:sRight]]
  <div style='border:1px solid #aaa;background-color:#f4f4f4;margin:0 0.125in 0 0.75in;padding:0.125in'>
    <div>$sAddrChanged</div>
    <div style='margin-top:0.125in'>
    $sEmail: [[email]]<br/>
    $sPhone: [[phone]]</div>
  </div>
  <div style='font-size:8pt;margin:0.05in 0.125in 0 0;float:right'>$sMember [[_key]]</div>
</div>
<div class='s_note' style='position:absolute;bottom:0.125in;left:0.325in;text-alignment:left'>
  $sFooter
</div>
";

$s = str_replace( "[[Var:sTitle]]", $sTitle, $sTmpl );
$s = str_replace( "[[Var:sForm]]", $sForm, $s );
$s = str_replace( "[[Var:sRight]]", $sRight, $s );


        return( $s );
    }
}

?>
