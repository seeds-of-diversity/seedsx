<?
/* Main french page of items for sale
 */

include_once( "../site.php" );
include_once( PAGE1_ROOT."page1.php" );
include_once( "_vend.php" );



$page1parms = array (
                "lang"      => "FR",
                "title"     => "À Vendre",
                "tabname"   => "VEND",
//              "box1title" => "Box1Title",
//              "box1text"  => "Box2Text",
//              "box1fn"    => "Box1Fn",
//              "box2title" => "Box2Title",
//              "box2text"  => "Box2Text",
//              "box2fn"    => "Box2Fn",

             );


define( "VEND_GO_TO_ORDER_FORM", "<A class='vend_orderform_link' HREF='".SITEROOT."mbr/membre.php'>Formulaire bon de commande</A>" );

Page1( $page1parms );


function Page1Body() {

vend_style();

?>
<h2>À Vendre</h2>
<p>Le Programme Semencier du Patrimoine Canada offre
ces publications à ses membres ainsi qu'au grand public.
Les profits résultant de leur
vente servent à financer nos différents projets dans la
préservation des semences
traditionnelles ainsi que nos projets éducatifs.</p>

<p><b>Les prix sont en dollars canadiens et incluent les
frais postaux, la manutention et les taxes en vigueur.</b></p>

<p>Pour toute information supplémentaire, veuillez
communiquer avec nous de la façon suivante: <?= SEEDStd_EmailAddress( "courriel", "semences.ca" ); ?> ou 1-866-509-7333.
Nous acceptons avec plaisir toute commande en gros.</p>


<p><?= VEND_GO_TO_ORDER_FORM; ?></p>

<table class='vend_top_array'><tr>
<td><a href="#ssh_f"><img src="<?= SITEIMG ?>vend/ssh6fr150.jpg" width="60" height="75"></a></td>
<!--
<td><a href="#ssh_e"><img src="<?= SITEIMG ?>vend/ssh_cv.gif" width="60" height="75"></a></td>
-->
<!--
<td><a href="#niche1"><img src="<?= SITEIMG ?>vend/niche1_cv.gif" width="60" height="75"></a></td>
<td><a href="#niche2"><img src="<?= SITEIMG ?>vend/niche2_cv.gif" width="60" height="75"></a></td>
<td><a href="#notecards"><img src="<?= SITEIMG ?>vend/cd_pansx.jpg" width="60" height="75"></a></td>
-->
<td><a href="#backissues"><img src="<?= SITEIMG ?>vend/mag.gif" width="60" height="75"></a></td>
</tr><tr>
<td width="90">La conservation des semences <span style='color:red'>NOUVEAU!</span></td>
<!--
<td width="90">How to Save Your Own Seeds (anglais)</td>
-->
<!--
<td width="90">Niche Market Development (anglais)</td>
<td width="90">Selling Heritage Crops (anglais)</td>
<td width="90">Assortiments de cartes souhaits "Heritage" (intérieur laissé en blanc)</td>
-->
<td width="90">Copies antérieurs du bulletin <i>Seeds of Diversity</i></td>
</tr></table>


<!-- *********** FRENCH SEED SAVING HANDBOOK *********** -->
<a name="ssh_f">
<? vend_ssh_f(); ?>

<!-- *********** ENGLISH SEED SAVING HANDBOOK *********** -->
<?php
// <a name="ssh_e"></a>
// vend_ssh_e();
?>

<!-- *********** NICHE MARKET DEVELOPMENT *********** -->
<?php
// <a name="niche1">
// vend_niche1( "FR" );
?>

<!-- *********** SELLING HERITAGE CROPS *********** -->
<?php
// <a name="niche2">
// vend_niche2( "FR" );
?>


<!-- *********** HERITAGE NOTECARDS *********** -->
<? /*
<a name="notecards">
</a><h2><font color="007700"><b>Assortiments de cartes souhaits "Heritage"</b></font></h2>
<table align="left" cellspacing="20" width="240">
<tr>
<td valign="top" width="80"><a href="cd_pans.jpg"><img src="<?= SITEIMG ?>vend/cd_pansx.jpg" width="60" height="80" alt="Pansies Notecard"><br><font size="-1" face="Arial,Helvetica"><b>pensées</b></font></a></td>
<td valign="top" width="80"><a href="cd_veg.jpg"><img src="<?= SITEIMG ?>vend/cd_vegx.jpg" width="60" height="80" alt="Vegetables Notecard"><br><font size="-1" face="Arial,Helvetica"><b>légumes</b></font></a></td>
<td valign="top" width="80"><a href="cd_iris.jpg"><img src="<?= SITEIMG ?>vend/cd_irisx.jpg" width="60" height="80" alt="Irises Notecard"><br><font size="-1" face="Arial,Helvetica"><b>iris</b></font></a></td>
</tr><tr>
<td valign="top" width="80"><a href="cd_morn.jpg"><img src="<?= SITEIMG ?>vend/cd_mornx.jpg" width="60" height="80" alt="Morning Glories Notecard"><br><font size="-1" face="Arial,Helvetica"><b>gloires du matin</b></font></a></td>
<td valign="top" width="80"><a href="cd_tulp.jpg"><img src="<?= SITEIMG ?>vend/cd_tulpx.jpg" width="60" height="80" alt="Tulips Notecard"><br><font size="-1" face="Arial,Helvetica"><b>tulipes</b></font></a></td>
<td valign="top" width="80"><a href="cd_mayb.jpg"><img src="<?= SITEIMG ?>vend/cd_maybx.jpg" width="60" height="80" alt="May Berries Notecard"><br><font size="-1" face="Arial,Helvetica"><b>baies printannières</b></font></a></td>
</tr>
<tr><td colspan="3">
<font size="-1" face="Arial,Helvetica">
<b>5" x 7" en blanc à l'intérieur<br>
1,00 $ chacune, incluant les frais de poste (commande de 5 ou plus)<br>
0,50 $ chacune pour chaque commande de 50 ou plus de n'importe quel assortiment<br>
Enveloppes inclues.<br>
<?= VEND_GO_TO_ORDER_FORM; ?>
</b></font>
</td></tr></table>

<p>&nbsp;</p>

<p>Des cartes de souhaits avec images de fleurs du
patrimoine sont blancs à l'intérieur.
Ces cartes sont inspirées de la multitude de
catalogues de semences qui sont apparus vers le milieu des années 1880.
</p>
<p>
Elles s'inspirent de l'ère victorienne et elles sont très
recherchées par les nostalgiques de cette
époque. Les cartes, hautes en couleurs, rappellent la belle
époque et le Programme
Semencier du Patrimoine est heureux de pouvoir vous les offrir.</p>

<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>

*/ ?>

<!-- *********** BACK ISSUES *********** -->
<a name="backissues">
</a>
<DIV class='sect1'>
<H2>Copies antérieurs du bulletin <i>Seeds of Diversity</i> / <i>Heritage Seed Program</i></H2>
<table align="left" cellspacing="20"><tr><td><img src="<?= SITEIMG ?>vend/mag.gif" width="150" height="200"></td></tr>
<tr><td class='vend_caption'>
<b>4,00 $ le numéro, incluant les frais de poste<br>
<?= VEND_GO_TO_ORDER_FORM; ?>
</td></tr></table>

<p>Des numéros du bulletin depuis Décembre 1988
jusqu'à aujourd'hui sont disponibles. Les bulletins de Décembre 1988 et Décembre 1989
sont disponibles en photocopies seulement.
Des numéros du bulletin d'avant 1996 s'appelait "Heritage Seed Program Magazine"</p>

<p>Vérifier dans l'index par le titre de l'article et le nom de l'auteur.</p>

<p>Notre bureau possède une banque de données et il
est possible d'y effectuer une recherche sur un légume particulier, un fruit, une sorte
de céréale, une variété d'herbe ou de fleur, par province, par pays, par nom du
musée, par nom d'organisation ou par nom de graineterie. Des mots-clés comme
"histoire", "croisement", "jardinage", "maladie",
"intégrité génétique", "comment faire" servent à faire les
recherches.</p>

<p>Vous recherchez des informations sur un sujet bien
précis? Faites-nous parvenir votre demande avec une enveloppe pré-affranchie et
pré-adressée et il nous fera plaisir de vous envoyer la liste des numéros qui
correspondent avec votre sujet.</p>
</DIV>
<?
}

?>
