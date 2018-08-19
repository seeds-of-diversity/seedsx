<?
define( "SITEROOT", "../../" );
include_once( SITEROOT."site.php" );
include_once( PAGE1_TEMPLATE );
include_once( "../_info.php" );


$page1parms = array (
                "lang"      => "FR",
                "title"     => "Nos Objectifs",
                "tabname"   => "ABOUT",
                "box1title" => "Information",
                "box1fn"    => "box1fn_fr",
                "box2title" => "Contactez Nous",
                "box2fn"    => "box2fn_fr"
             );



Page1( $page1parms );


function Page1Body() {
?>
<h2>Nos Objectifs</h2>
<p>
<img src="<?= SITEIMG ?>dot1.gif">
<b>
    De rechercher, pr&eacute;server, perp&eacute;tuer,
    &eacute;tudier et encourager la culture de vari&eacute;t&eacute;s
    l&eacute;gumi&egrave;res traditionnelles ou menac&eacute;es
    par les actions suivantes:
</b>
</p>
<ul>
<li>
    en effectuant des recherches sur les vari&eacute;t&eacute;s
    l&eacute;gumi&egrave;res du patrimoine menac&eacute;es, et
    particuli&egrave;rement sur les vari&eacute;t&eacute;s canadiennes;
</li>
<li>
    en encourageant et en incitant les jardiniers et les cultivateurs
    &agrave; cultiver, &agrave; produire et &agrave; multiplier ces
    vari&eacute;t&eacute;s par l'entremise du programme d'&eacute;change
    annuel des semences;
</li>
<li>
    en &eacute;tablissant et en maintenant sous curatelle des collections de
    vari&eacute;t&eacute;s canadiennes;
</li>
<li>
    en collaborant avec des particuliers, des cultivateurs, des
    regroupements et d'autres institutions, au Canada ou ailleurs dans le
    monde, de fa&ccedil;on &agrave; maintenir, soutenir, sauvegarder et
    r&eacute;cup&eacute;rer des collections existantes de
    vari&eacute;t&eacute;s traditionnelles ou menac&eacute;es;
</li>
<li>
    en encourageant les graineteries commerciales, les p&eacute;pini&egrave;res
    ainsi que d'autres entreprises impliqu&eacute;es dans ce type de commerce
    &agrave; cultiver, maintenir, propager et distribuer commercialement ces
    vari&eacute;t&eacute;s de mani&egrave;re &agrave; en assurer
    leur survivance;
</li>
</ul>

<p>
<img src="<?= SITEIMG ?>dot1.gif">
<b>
    D'&eacute;duquer et de renseigner le public sur l'importance de la
    sauvegarde des vari&eacute;t&eacute;s l&eacute;gumi&egrave;res,
    fruiti&egrave;res, c&eacute;r&eacute;ali&egrave;res, florales et
    m&eacute;dicinales du patrimoine et sur le besoin de perp&eacute;tuer
    leur culture.
<br><br>
</b>
</p>

<p>
<img src="<?= SITEIMG ?>dot1.gif">
<b>
    D'impliquer le public en g&eacute;n&eacute;ral, les cultivateurs, les
    producteurs de semences, les regroupements, les associations, les
    mus&eacute;es, les banques de g&egrave;nes, les clubs, les &eacute;coles
    et les maisons d'enseignement.
<br><br>
</b>
</p>

<p>
<img src="<?= SITEIMG ?>dot1.gif">
<b>
    De fournir les informations appropri&eacute;es sur les diff&eacute;rentes
    m&eacute;thodes de pr&eacute;servation et de conservation des semences
    afin d'assurer l'int&eacute;grit&eacute; g&eacute;n&eacute;tique des
    vari&eacute;t&eacute;s du patrimoine.
<br><br>
</b>
</p>

<p>
<img src="<?= SITEIMG ?>dot1.gif">
<b>
    De renseigner toute personne qui d&eacute;sire obtenir et produire des
    semences sur la provenance ou l'obtention originale des
    vari&eacute;t&eacute;s traditionnelles ou menac&eacute;es offertes par
    le programme.
<br><br>
</b>
</p>

<p>
<img src="<?= SITEIMG ?>dot1.gif">
<b>
    De fournir un forum de discussion afin d'encourager les &eacute;changes
    de renseignements sur des sujets pertinents &agrave; la pr&eacute;servation
    de la diversit&eacute; biologique, par des publications, des
    expos&eacute;s, des conf&eacute;rences, des pr&eacute;sentations
    m&eacute;diatiques ainsi que des expositions.
<br><br>
</b>
</p>

<p>
<img src="<?= SITEIMG ?>dot1.gif">
<b>
    De partager toute information ou exp&eacute;rience avec des organismes
    internationaux oeuvrant dans le m&ecirc;me domaine et de les
    soutenir dans leurs efforts de pr&eacute;servation et de conservation des
    vari&eacute;t&eacute;s traditionnelles ou menac&eacute;es.
</b>
</p>

<br><br>
<p align="center">Traduction par Louise Chevrefils</p>
<?
}

?>
