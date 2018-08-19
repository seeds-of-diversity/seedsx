<?
/* Main french page of items for sale
 */

include_once( "../site.php" );
include_once( PAGE1_ROOT."page1.php" );
include_once( "_vend.php" );



$page1parms = array (
                "lang"      => "FR",
                "title"     => "� Vendre",
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
<h2>� Vendre</h2>
<p>Le Programme Semencier du Patrimoine Canada offre
ces publications � ses membres ainsi qu'au grand public.
Les profits r�sultant de leur
vente servent � financer nos diff�rents projets dans la
pr�servation des semences
traditionnelles ainsi que nos projets �ducatifs.</p>

<p><b>Les prix sont en dollars canadiens et incluent les
frais postaux, la manutention et les taxes en vigueur.</b></p>

<p>Pour toute information suppl�mentaire, veuillez
communiquer avec nous de la fa�on suivante: <?= SEEDStd_EmailAddress( "courriel", "semences.ca" ); ?> ou 1-866-509-7333.
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
<td width="90">Assortiments de cartes souhaits "Heritage" (int�rieur laiss� en blanc)</td>
-->
<td width="90">Copies ant�rieurs du bulletin <i>Seeds of Diversity</i></td>
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
<td valign="top" width="80"><a href="cd_pans.jpg"><img src="<?= SITEIMG ?>vend/cd_pansx.jpg" width="60" height="80" alt="Pansies Notecard"><br><font size="-1" face="Arial,Helvetica"><b>pens�es</b></font></a></td>
<td valign="top" width="80"><a href="cd_veg.jpg"><img src="<?= SITEIMG ?>vend/cd_vegx.jpg" width="60" height="80" alt="Vegetables Notecard"><br><font size="-1" face="Arial,Helvetica"><b>l�gumes</b></font></a></td>
<td valign="top" width="80"><a href="cd_iris.jpg"><img src="<?= SITEIMG ?>vend/cd_irisx.jpg" width="60" height="80" alt="Irises Notecard"><br><font size="-1" face="Arial,Helvetica"><b>iris</b></font></a></td>
</tr><tr>
<td valign="top" width="80"><a href="cd_morn.jpg"><img src="<?= SITEIMG ?>vend/cd_mornx.jpg" width="60" height="80" alt="Morning Glories Notecard"><br><font size="-1" face="Arial,Helvetica"><b>gloires du matin</b></font></a></td>
<td valign="top" width="80"><a href="cd_tulp.jpg"><img src="<?= SITEIMG ?>vend/cd_tulpx.jpg" width="60" height="80" alt="Tulips Notecard"><br><font size="-1" face="Arial,Helvetica"><b>tulipes</b></font></a></td>
<td valign="top" width="80"><a href="cd_mayb.jpg"><img src="<?= SITEIMG ?>vend/cd_maybx.jpg" width="60" height="80" alt="May Berries Notecard"><br><font size="-1" face="Arial,Helvetica"><b>baies printanni�res</b></font></a></td>
</tr>
<tr><td colspan="3">
<font size="-1" face="Arial,Helvetica">
<b>5" x 7" en blanc � l'int�rieur<br>
1,00 $ chacune, incluant les frais de poste (commande de 5 ou plus)<br>
0,50 $ chacune pour chaque commande de 50 ou plus de n'importe quel assortiment<br>
Enveloppes inclues.<br>
<?= VEND_GO_TO_ORDER_FORM; ?>
</b></font>
</td></tr></table>

<p>&nbsp;</p>

<p>Des cartes de souhaits avec images de fleurs du
patrimoine sont blancs � l'int�rieur.
Ces cartes sont inspir�es de la multitude de
catalogues de semences qui sont apparus vers le milieu des ann�es 1880.
</p>
<p>
Elles s'inspirent de l'�re victorienne et elles sont tr�s
recherch�es par les nostalgiques de cette
�poque. Les cartes, hautes en couleurs, rappellent la belle
�poque et le Programme
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
<H2>Copies ant�rieurs du bulletin <i>Seeds of Diversity</i> / <i>Heritage Seed Program</i></H2>
<table align="left" cellspacing="20"><tr><td><img src="<?= SITEIMG ?>vend/mag.gif" width="150" height="200"></td></tr>
<tr><td class='vend_caption'>
<b>4,00 $ le num�ro, incluant les frais de poste<br>
<?= VEND_GO_TO_ORDER_FORM; ?>
</td></tr></table>

<p>Des num�ros du bulletin depuis D�cembre 1988
jusqu'� aujourd'hui sont disponibles. Les bulletins de D�cembre 1988 et D�cembre 1989
sont disponibles en photocopies seulement.
Des num�ros du bulletin d'avant 1996 s'appelait "Heritage Seed Program Magazine"</p>

<p>V�rifier dans l'index par le titre de l'article et le nom de l'auteur.</p>

<p>Notre bureau poss�de une banque de donn�es et il
est possible d'y effectuer une recherche sur un l�gume particulier, un fruit, une sorte
de c�r�ale, une vari�t� d'herbe ou de fleur, par province, par pays, par nom du
mus�e, par nom d'organisation ou par nom de graineterie. Des mots-cl�s comme
"histoire", "croisement", "jardinage", "maladie",
"int�grit� g�n�tique", "comment faire" servent � faire les
recherches.</p>

<p>Vous recherchez des informations sur un sujet bien
pr�cis? Faites-nous parvenir votre demande avec une enveloppe pr�-affranchie et
pr�-adress�e et il nous fera plaisir de vous envoyer la liste des num�ros qui
correspondent avec votre sujet.</p>
</DIV>
<?
}

?>
