<?
// TODO: flag new or changed events (indicate the date updated)

/* Show the Resource List Companies
 *
 * $lang       = "EN" or "FR"
 * RL_pageType = normal (default), print, edit (update mode should not occur here)
 */

// this page is used standalone and included from others, so constants may or may not be defined
if( !defined("SITEROOT") )  define("SITEROOT", "../");
if( !defined("RLROOT") )    define("RLROOT",   "./");


include_once( SITEROOT."site.php" );
include_once( SITEINC."sitedb.php" );
include_once( PAGE1_ROOT."page1.php" );
include_once( RLROOT."_rl_inc.php" );


/* Advance the year 60 days before the new year
 */
$sYear = date( "Y", time() + (3600*24*60) );

$title_en = "Resource List - $sYear";
$title_fr = "Liste des Sources - $sYear";

if( empty($lang) )  $lang = strtoupper( @$_REQUEST["lang"] );
if( $lang != "FR" )  $lang = "EN";

$pagetitle = ($lang != "FR" ? $title_en : $title_fr);


/* Authenticate anyone editing this page
 */
if( RL_pageType == "edit" ) {
    include_once( SITEINC ."sodlogin.php" );

    $la = new SoDLoginAuth;
    if( !$la->SoDLoginAuth_Authenticate( $_REQUEST, "W perm_rl" ) ) { exit; }
}
$auth_urlparms = (RL_pageType == "edit") ? "&".$la->login_auth_get_urlparms() : "";



/* Header
 */
if( RL_pageType == "edit" ) {
    echo "<H2 align=center>".$la->realname." is editing this page</H2>";
    echo "<TABLE align=center><TR><TD bgcolor='".CLR_BG_editEN."'>These items appear on SEEDS.CA</TD></TR>";
    echo "<TR><TD bgcolor='".CLR_BG_editFR."'>These items appear on SEMENCES.CA</TD></TR></TABLE><BR><BR>";
    echo "<HR width=50%><TABLE align=center>";
    echo "<TR><TD bgcolor='".CLR_BG_editEN."'><FONT size=5 color='".CLR_hdr."'><B>$title_en</B></FONT></TD></TR>";
    echo "<TR><TD bgcolor='".CLR_BG_editFR."'><FONT size=5 color='".CLR_hdr."'><B>$title_fr</B></FONT></TD></TR>";
    echo "</TABLE><HR width=50%>";
    rl_list();
} else if( RL_pageType == "print") {
    echo "<html><head><title>";
    if( $lang == "EN" )  echo $title_en ." - Seeds of Diversity Canada - Semences du patrimoine Canada";
    else                 echo $title_fr ." - Semences du patrimoine Canada - Seeds of Diversity Canada";
    echo "</title>";
    echo "<style>\n";
    echo ".rl_country { font-family: Verdana, Helvetica, Arial, sans-serif; font-size: 18; font-weight: bold; }\n";
    echo ".rl_companyname { font-family: Verdana, Helvetica, Arial, sans-serif; font-size: 10; font-weight: bold; }\n";
    echo ".rl_companyaddr { font-family: Verdana, Helvetica, Arial, sans-serif; font-size: 10; font-weight: normal; }\n";
    echo ".rl_companydesc { font-family: inherit;                               font-size: 10; font-weight: normal; }\n";
    echo ".P {font-size:50%;}\n";
    echo "</style>";
    echo "</head><body>";
    echo "<center><h1>". ($lang == "EN" ? "Seeds of Diversity Canada" : "Semences du patrimoine Canada" ) ."</h1>";
    echo "<h2>$pagetitle</h2></center>";
    rl_list();
} else {
    /* Normal
     * Invoke Page1 to draw the events list with banners, etc
     */
    function Page1Body() { rl_list(); }

    function Box1Fn() {
        global $lang;

        if( $lang == "EN" ) {
            $s = "<div><a href='".SITEROOT."rl/rl.php'>Resource List</a></div>"
                ."<div><a href='".SITEROOT."rl/rl_prn.php' TARGET='rl-prn-window'>Resource List - printable version</a></div>";
        } else {
            $s = "<div><a href='".SITEROOT."rl/lr.php'>Liste des Sources</a></div>"
                ."<div><a href='".SITEROOT."rl/lr_prn.php' TARGET='rl-prn-window'>Liste des Sources - Version imprimable</a></div>";
        }
        return( $s );
    }

    $page1parms = array (
                    "lang"      => $lang,
                    "title"     => $pagetitle,
                    "tabname"   => "Library",
                    "box1title" => ($lang != "FR" ? "Library" : "Biblioth&egrave;que"),
                    "box1fn"    => "Box1Fn",
    //              "box2title" => "Box2Title",
    //              "box2text"  => "Box2Text",
    //              "box2fn"    => "Box2Fn",

                 );
    Page1( $page1parms );
}



function rl_list()
/*****************
 */
{
    global $lang, $pagetitle, $auth_urlparms;

    if( RL_pageType != "print" && RL_pageType != "edit" ) {
        echo "<H2>$pagetitle</H2>";
    }

    /* Blurb
     */
    if( RL_pageType != "edit" ) {
        $fontsize = (RL_pageType == "print") ? 12 : 16;
        if( $lang == "EN" ) {
            echo "<div style='font-size:$fontsize'>";
            ?>
            <P>The following seed companies and nurseries sell heirloom and rare or endangered varieties of vegetables, fruits,
            flowers and herbs. In some catalogues, the heirloom varieties are noted as such, but in others they are not, so you
            have to know what you are looking for.</P>
            <P>All prices are in Canadian funds unless specified. The U.S. seed companies listed here will send seeds to Canada,
            but usually cannot ship living plants, bulbs, potatoes, etc across the border.</P>
            <P>Since some U.S. banks charge a large fee to cash non-U.S. cheques, many U.S. seed companies cannot accept cheques
            from Canadian banks. It is often best to make your payments with money orders instead.</P>
            <P>This resource list is updated regularly. If you know of other resources or changes that should be made to this
            information, please e-mail
            <?= (RL_pageType=="print") ? "" : "<A HREF='mailto:office@seeds.ca'>" ?>office@seeds.ca<?= (RL_pageType=="print") ? "" : "</A>" ?>
            </P>
            <P>Inclusion of any company in this list does not constitute a recommendation by Seeds of Diversity Canada and no
            claims are made to accuracy.  <B>This list is for information purposes only.</B></P>
            
            <? 
            	echo "</div>";
            	if( RL_pageType != "print" ) {
                  echo "<P><A HREF='rl_prn.php' TARGET='rl-prn-window'><FONT SIZE='4'><B><I>Printable Version of this List</I></B></FONT></A></P>";
               	}
            ?>
            <?
        } else 
        
        {
            echo "<div style='font-size:$fontsize'>";
            ?>
            <P>Les graineteries et pépinières suivantes vendent des variétés de légumes,
            de fruits, de fleurs, de fines herbes et d'herbes médicinales du patrimoine, rares ou menacées.
            Certains catalogues décrivent et indiquent clairement leurs semences traditionnelles; toutefois, d'autres
            ne le mentionnent pas. Vous devez savoir ce que vous recherchez.</P>
            <P>Lorsqu'il s'agit d'entreprises canadiennes, les montants sont indiqués en dollars canadiens.
            Les compagnies américaines ou européennes feront parvenir les sachets de semences aux
            résidents canadiens sans problème, mais les
            <B>douanes canadiennes refuseront et confisqueront toute importation de bulbes, plantules, rhizômes,
            tubercules, pommes de terre ou plantes vivantes en pot ou en sac</B>.
            Il faut comprendre que les maladies fongiques ou la présence d'intrus microscopiques ou non, nuisibles
            à l'environnement sont à redouter, d'où les précautions quant à l'importation
            de ces végétaux. Certaines exceptions sont possibles, mais il vous faudra vérifier
            auparavant avec le bureau d'Agriculture Canada.  Dans le cas d'entreprises étrangères, les
            graineteries réputées mondialement peuvent parfois fournir un certificat phytosanitaire à
            Agriculture Canada; renseignez-vous auprès de l'entreprise commerciale sur cette possibilité.</P>
            <P>Puisque certaines banques américaines exigent des sommes considérables pour encaisser les
            chèques étrangers, plusieurs graineteries américaines n'acceptent pas les chèques
            canadiens. Il sera alors préférable d'effectuer votre paiement par mandat-poste ou par carte de
            crédit. Dans le cas des compagnies européennes, une traite bancaire ou un paiement par carte de
            crédit est envoyé à la graineterie, dans la devise du pays.</P>
            <P>Cette liste alphabétique, selon chaque pays, est périodiquement remise à jour. Si vous
            connaissez d'autres graineteries ou si des changements devaient être apportés à cette liste,
            n'hésitez pas à communiquer par courriel à
            <?= (RL_pageType=="print") ? "" : "<A HREF='mailto:courriel@semences.ca'>" ?>courriel@semences.ca<?= (RL_pageType=="print") ? "" : "</A>" ?>
            </P>
            <P>Le nom d'une compagnie dans cette liste ne constitue en aucun cas une recommandation par le Programme
            Semencier du Patrimoine, et nous ne cautionnons aucunement l'exactitude de son contenu.
            <B>Cette liste ne doit servir qu'à informer seulement</B>.</P>
            
            <? 
            	echo "</div>";
            	if( RL_pageType != "print" ) {
                   echo "<P><A HREF='lr_prn.php' TARGET='rl-prn-window'><FONT size='4'><B><I>Version imprimable de cette liste</I></B></FONT></A></P>";
               	}
            ?>
            <?
        }
    }


    /* Page-level Editing Controls
     */
    if( RL_pageType == "edit" ) {
        echo "<P><A HREF='".RLROOT."rl.php'><FONT COLOR='red'>[Normal View on SEEDS.CA]</A></FONT></P>";
        echo "<P><A HREF='".RLROOT."lr.php'><FONT COLOR='red'>[Normal View on SEMENCES.CA]</A></FONT></P>";
        echo "<P><A HREF='".RLROOT."admin/edit.php?i=new" . $auth_urlparms ."'><FONT COLOR='red'>[Add New Company]</A></FONT></P>";
    }


    function country_list( $country, $country_name, $lang, $auth_urlparms )
    {
        echo "<HR><P class='rl_country'><B>${country_name}</B></P>";
        if( RL_pageType == "normal") {
            echo "<TABLE cellpadding='10' cellspacing='10'>";
        } else {
            echo "<TABLE width='100%' cellpadding='0' cellspacing='2'>";
        }
 
        $dbc = db_open( "SELECT * FROM csci_country WHERE country='${country}' AND _disabled=0 ORDER BY name_en");

        while( $ra = db_fetch( $dbc ) ) {
            rl_cmp_show( $ra, $lang, $auth_urlparms );
        }
        echo "</TABLE>";
    }

    country_list( "Canada",  "Canada",                                     $lang, $auth_urlparms );
    country_list( "US",      $lang=="EN" ? "United States" : "États-Unis", $lang, $auth_urlparms );
    country_list( "England", $lang=="EN" ? "England"       : "Angleterre", $lang, $auth_urlparms );
    country_list( "France",  "France",                                     $lang, $auth_urlparms );


    if( RL_pageType == "print" ) {
        std_footer( $lang );
    }
}
?>
