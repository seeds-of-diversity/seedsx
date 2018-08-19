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
Appel à tous les cultivateurs de tomates!
</P><P>
Dans le cadre d’un projet qui s’étalera sur plusieurs années et qui sera documenté méticuleusement, les
Semences du patrimoine vous invitent à faire pousser tous les cultivars de tomates canadiens connus.
Jusqu’à maintenant, nous avons identifié plus de 100 tomates d'origine canadienne. Les semences de celles-ci
sont toutefois difficiles à trouver et l’information les concernant est incomplète et peu accessible.
</P><P>
<IMG src='<?= SITEIMG ?>earlirouge4.jpg' align=right height='200'>
La plupart des cultivars canadiens ont été développés par Agriculture Canada, mais quelques uns nous proviennent
d’universités. Nous avons également quelques rares variétés qui ont été développées par des individus ayant fait
leur propres croisements et sélections dans leurs jardins. Nous inclurons également dans notre projet les semences
provenant de patrimoines familiaux, apportées au Canada par les immigrants et préservées pour un nombre de générations.
En plus de cultiver et propager les semences de tomates, nous comptons rassembler l’information pertinente aux origines
de chacun des cultivars.
</P><P>
Nous ferons parvenir aux participants de l’information sur la conservation des semences de tomates, de même qu’un
formulaire d’observation qu’ils devront remplir et nous retourner. Nous espérons rassembler le plus de gens possible
ayant de l’expérience dans la conservation des semences afin de multiplier ces variétés et ainsi pouvoir les offrir
dans l’annuaire d’échange des semences de l’an prochain. Tous les membres sont toutefois bienvenus à participer au
projet, ne serait-ce que pour faire pousser une seule des variétés et de remplir le formulaire d’observation.
</P>

<? /*
<P>
Les semences pour les variétés rares de tomates canadiennes sont offertes gratuitement aux membres des
Semences du patrimoine par Jim Ternier de Prairie Garden Seeds.  Nous aurons également des échantillons
en provenance des Ressources phytogénétiques de Saskatoon, et des collections de certains de nos membres
afin de compléter notre liste de ressources.
</P>

<P>
Si vous désirez contribuer à ce projet et cultiver une, ou plusieurs variétés rares de tomates canadiennes,
veuillez téléphoner à notre bureau au 1-866-509-7333 ou nous faire parvenir un courriel au 
<?= BXStd_EmailAddress( "courriel", "semences.ca" ) ?>.
Nous recherchons particulièrement des gens qui pourront nous aider à régénérer les stocks de variétés qui sont en
quantités très limitées.
</P>
*/ ?>

<DIV style='border:1px solid #333;background-color:#ddd;padding:1em;width:50%'">
Malheureusement nous n'avons pas des semences disponibles
</DIV>


<HR>
<H3>Voici des articles en français et anglais:</H3>
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
