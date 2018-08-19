<?

define( "SITEROOT", "./" );
include_once( SITEROOT."site.php" );
include_once( PAGE1_TEMPLATE );


$page1parms = array (
                "lang"      => "FR",
                "title"     => "",
                "tabname"   => "Home",
                "box1title" => "Les nouvelles pages",
                "box1fn"    => "box1fn",
                "box2title" => "Contactez Nous",
                "box2fn"    => "box2fn",
             );

Page1( $page1parms );


function Page1Body() {
//<div style='border: medium solid green; margin:5px;padding: 5px; width:400px; font-family:comic sans ms,verdana,arial,helvetica,sans serif; color:green; text-align:center;'> CULTIVER LA BIODIVERSITÉ <BR><BR>Un conference au Jardin botanique de Montréal<BR>Dimanche le 5 novembre 2006<BR><BR><A href='conference/conference_fr.pdf'>Plus d'information</A></div>
    echo "<table align=right cellpadding=20 border=0 style='margin:1em;width:25em;'>";
//    echo "<tr><td><img align=right src='".SITEIMG."squash250.jpg'></td></tr>";
/*
echo "<TR><TD style='background-color:#C8ECC4;border:thin solid #333333;font-family:verdana,helvetica,sans serif;font-size:11pt;'>"
."<B style='font-size:12pt'>Les Semences du patrimoine f&ecirc;tent leurs 25 ans!</B><BR/>"
."<P>Le samedi 3 octobre 2009 &agrave; 18h00<BR/>"
."Centre Saint-Pierre, Montréal</P>"
."<UL style='font-size:10pt'>"
."<LI>Buffet offert par Festigo&ucirc;t</LI>"
."<LI>Conférence sur l'agriculture urbaine par le CRAPAUD</LI>"
."<LI>Historique de l'organisme par Bob Wildfong</LI>"
."<LI>Présentation de Slow Food</LI>"
."<LI>Conférence sur les permablitz de Montréal</LI>"
."</UL>"
."<TABLE border='0' style='font-family:verdana,helvetica,sans serif;font-size:11pt'><TR><TD valign='top'>Prix:</TD>"
."<TD valign='top'>35 $ avant le 15 septembre<BR/>"
."40 $ par la suite<BR/><BR/>"
."payer en ligne au <A HREF='http://www.semences.ca/mbr'>www.semences.ca</A> \"Formulaire\"<BR/>"
."</TD></TR></TABLE>"
."<P>Cette levée de fonds servira &agrave financer les projets de<BR/> conservation des semences de notre patrimoine.</P>"
."<P style='text-align:center'><A HREF='conference/Montreal_091003_FR.pdf' target='_blank'>Voici l'affiche</A></P>" 
."<P style='text-align:center;font-size:12pt;font-weight:bold;'>Ensemble pour la protection des semences depuis 25 ans!</P>" 
."</TD></TR>"
*/

echo "<STYLE>"
    .".homeNotice {}"
    ."</STYLE>";
echo "<TR><TD style='background-color:#CCDDEE;border:1px solid #333333;font-family:verdana,helvetica,sans serif;padding:1em;font-size:11pt;text-align:center'>"
."<P class='homeNotice' style=''><A href='http://www.seeds.ca/sl/csci'>Cherchez-vous des semences ?<BR/>Cliquez ici pour toutes les semences de l&eacute;gume et de fruit au Canada !</A></P>"
."</TD></TR>"
."<TR><TD>&nbsp;</TD></TR>"
."<TR><TD style='background-color:#CCDDEE;border:1px solid #333333;font-family:verdana,helvetica,sans serif;padding:1em;font-size:11pt;text-align:center'>"

."<A HREF='http://www.semences.ca/sl'><IMG src='http://www.seeds.ca/sl/img/sl01.png' width='200'/>"
."<BR/><BR/>"
."Voir notre Biblioth&egrave;que canadienne des semences !"
."<BR/>En fran&ccedil;ais bient&ocirc;t</A>"

//."<B>Le R&eacute;seau de Semenciers de L'Est du Canada (ECOSGN)</B><BR/><BR/>"
//."Cours de production de semences biologiques<BR/>"
//."21-22 octobre, 2009<BR/>"
//."Les C&egrave;dres, QC<BR/><BR/>"
//."<SPAN style='font-size:10pt;'><A HREF='http://www.semences.ca/conference/ECOSGN_0910_Cours_de_semences.pdf' target='_blank'>Details</A></SPAN><BR/>"
//."<SPAN style='font-size:10pt;'><A HREF='http://www.semences.ca/mbr' target='_blank'>Formulaire d'inscription</A></SPAN><BR/>"


."</TD></TR></TABLE>";

?>
<h2>Semences du patrimoine</h2>
<P>est le réseau national d'échange et de préservation de semences à pollinisation libre.</P>
<P>
Nous sommes un organisme sans but lucratif de jardiniers qui produisent
et préservent des semences de variétés traditionnelles de fleurs, de l&eacute;gumes,
d'herbes médicinales rares ou oubliées,
dans le but de sauvegarder cet important patrimoine génétique.
</P>
<P>
Nous sommes une banque vivante de gènes<br><br>
</P>
<?
}



function box1fn() {
    return(
         "<div><a href='".SITEROOT."ev/evenements.php'>Les Fêtes de Semences</a></div>"
        ."<div><a href='".SITEROOT."proj/tomato/index_fr.php'>Projet Tomates Canadiennes</a></div>"
        ."<div><a href='".SITEROOT."rl/lr.php'>Liste des sources de semences</a></div>" );
}


function box2fn() {
    return(
         "<div>". SEEDStd_EmailAddress( "courriel", "semences.ca", "", array("subject"=>"Question pour Semences du patrimoine") ) ."</div>"
        ."<div><a href='mbr/membre.php'>Formulaire d'adhésion et bon de commande</a></div>" );
//       "<div><a href='mbr/membre.php'>Order our Publications</a></div>";
//       "<div><a href='bulletin/'>Subscribe to our free email Bulletin</a></div>";
}

?>
