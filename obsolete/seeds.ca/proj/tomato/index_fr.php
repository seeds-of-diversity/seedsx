<?
/* CTP home page - FR
 */

define( "SITEROOT", "../../" );
include_once( SITEROOT."site.php" );
include_once( PAGE1_ROOT."page1.php" );
include_once( "_ctp.php" );



$page1parms = array (
                "lang"      => "FR",
                "title"     => "Projet Tomates Canadiennes",
                "tabname"   => "Projects",
//              "box1title" => "Canadian Tomato Project",
//              "box1text"  => "Box2Text",
//              "box1fn"    => "Box1Fn",
//              "box2title" => "Box2Title",
//              "box2text"  => "Box2Text",
//              "box2fn"    => "Box2Fn"
             );



Page1( $page1parms );


function Page1Body() {
?>
<H2>Notre projet tomates canadiennes</H2>
<P>
Appel � tous les cultivateurs de tomates!
</P><P>
Dans le cadre d�un projet qui s��talera sur plusieurs ann�es et qui sera document� m�ticuleusement, les
Semences du patrimoine vous invitent � faire pousser tous les cultivars de tomates canadiens connus.
Jusqu�� maintenant, nous avons identifi� plus de 100 tomates d'origine canadienne. Les semences de celles-ci
sont toutefois difficiles � trouver et l�information les concernant est incompl�te et peu accessible.
</P><P>
<IMG src='<?= SITEIMG ?>earlirouge4.jpg' align=right height='200'>
La plupart des cultivars canadiens ont �t� d�velopp�s par Agriculture Canada, mais quelques uns nous proviennent
d�universit�s. Nous avons �galement quelques rares vari�t�s qui ont �t� d�velopp�es par des individus ayant fait
leur propres croisements et s�lections dans leurs jardins. Nous inclurons �galement dans notre projet les semences
provenant de patrimoines familiaux, apport�es au Canada par les immigrants et pr�serv�es pour un nombre de g�n�rations.
En plus de cultiver et propager les semences de tomates, nous comptons rassembler l�information pertinente aux origines
de chacun des cultivars.
</P><P>
Nous ferons parvenir aux participants de l�information sur la conservation des semences de tomates, de m�me qu�un
formulaire d�observation qu�ils devront remplir et nous retourner. Nous esp�rons rassembler le plus de gens possible
ayant de l�exp�rience dans la conservation des semences afin de multiplier ces vari�t�s et ainsi pouvoir les offrir
dans l�annuaire d��change des semences de l�an prochain. Tous les membres sont toutefois bienvenus � participer au
projet, ne serait-ce que pour faire pousser une seule des vari�t�s et de remplir le formulaire d�observation.
</P>

<? /*
<P>
Les semences pour les vari�t�s rares de tomates canadiennes sont offertes gratuitement aux membres des
Semences du patrimoine par Jim Ternier de Prairie Garden Seeds.  Nous aurons �galement des �chantillons
en provenance des Ressources phytog�n�tiques de Saskatoon, et des collections de certains de nos membres
afin de compl�ter notre liste de ressources.
</P>

<P>
Si vous d�sirez contribuer � ce projet et cultiver une, ou plusieurs vari�t�s rares de tomates canadiennes,
veuillez t�l�phoner � notre bureau au 1-866-509-7333 ou nous faire parvenir un courriel au�
<?= BXStd_EmailAddress( "courriel", "semences.ca" ) ?>.
Nous recherchons particuli�rement des gens qui pourront nous aider � r�g�n�rer les stocks de vari�t�s qui sont en
quantit�s tr�s limit�es.
</P>
*/ ?>

<DIV style='border:1px solid #333;background-color:#ddd;padding:1em;width:50%'">
Malheureusement nous n'avons pas des semences disponibles
</DIV>


<HR>
<H3>Voici des articles en fran�ais et anglais:</H3>
<?
CTP_articles();

}


function box1fn() {
    echo "<div><a href='".SITEROOT."about/faq.htm'>Frequently Asked Questions</a></div>";
}


function box2fn() {
    echo "<div><a href='mailto:mail@seeds.ca'>Email</a></div>";
}

?>
