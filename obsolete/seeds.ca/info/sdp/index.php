<?

define( "SITEROOT", "../../" );
include_once( SITEROOT."site.php" );
include_once( PAGE1_TEMPLATE );
include_once( "../_info.php" );
include_once( STDINC."DocRep/DocRep.php" );


$page1parms = array (
                "lang"      => "FR",
                "title"     => "",
                "tabname"   => "ABOUT",
                "box1title" => "Information",
                "box1fn"    => "box1fn_fr",
                "box2title" => "Contactez Nous",
                "box2fn"    => "box2fn_fr",
             );

Page1( $page1parms );


function Page1Body() {
?>
<h2>De Semences du patrimoine Canada</h2>
<p>
En tant que membre du Semences du Patrimoine, vous recevrez chaque
ann&eacute;e trois num&eacute;ros du magazine <i>Seeds of Diversity / Semences du patrimoine</i>.
On y traite de
divers sujets tel les vari&eacute;t&eacute;s indig&egrave;nes, la science, la
g&eacute;n&eacute;tique et
l'histoire. Enfin, on aborde aussi certains c&ocirc;t&eacute;s techniques
en donnant maints conseils pratiques sur le jardinage.
</p>
<p>
Vous recevrez aussi la liste annuelle des membres ainsi que les graines
qu'ils sont en mesure de fournir. Cela vous donnera ainsi acc&egrave;s &agrave;
une large gamme de semences de l&eacute;gumes, fruits ou vari&eacute;t&eacute;s
de fleurs. Vous
pourrez ainsi d&eacute;velopper vos int&eacute;r&ecirc;ts et connaissances au
niveau du jardinage ou de l'agriculture en g&eacute;n&eacute;ral.
</p>


<p>
<font size="4">
<img src="<?= SITEIMG ?>dot1.gif"><a href="objectifs.php">Nos Objectifs</a></font></p>
<p>
<font size="4">
<img src="<?= SITEIMG ?>dot1.gif"><a href="<?= SITEROOT ?>mbr/membre.php">Adh&eacute;sion</a></font></p>
<?

/*
DR_link( "../dr/AR2007FR.pdf",
         "Rapport annuel 2007",
         "",
         array( "icon"   => SITEIMG_STDIMG."icon-pdf-l.gif",
                "target" => "_blank",
                "author" => "Semences du patrimoine",
                "date"   => "2007" ) );
DR_link( "../dr/AR2006FR.pdf",
         "Rapport annuel 2006",
         "",
         array( "icon"   => SITEIMG_STDIMG."icon-pdf-l.gif",
                "target" => "_blank",
                "author" => "Semences du patrimoine",
                "date"   => "2006" ) );
DR_link( "../dr/AR2005FR.pdf",
         "Rapport annuel 2005",
         "",
         array( "icon"   => SITEIMG_STDIMG."icon-pdf-l.gif",
                "target" => "_blank",
                "author" => "Semences du patrimoine",
                "date"   => "2005" ) );
*/
}

?>
