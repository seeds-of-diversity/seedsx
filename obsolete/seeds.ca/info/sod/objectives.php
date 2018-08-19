<?
define( "SITEROOT", "../../" );
include_once( SITEROOT."site.php" );
include_once( PAGE1_TEMPLATE );
include_once( "../_info.php" );


$page1parms = array (
                "lang"      => "EN",
                "title"     => "Our Objectives",
                "tabname"   => "ABOUT",
                "box1title" => "More Information",
                "box1fn"    => "box1fn_en",
                "box2title" => "Contact Us",
                "box2fn"    => "box2fn_en"
             );



Page1( $page1parms );


function Page1Body() {
?>
<h2>Our Objectives</h2>
<p>
<b>
<i>
To search out, preserve, perpetuate, study, and encourage the cultivation of heirloom and endangered varieties of
food crops by:
</i>
</b><br><br>
</p><p>
<img src='<?= SITEIMG."dot1.gif" ?>'>searching out heirloom and endangered varieties, particularly Canadian varieties; <br><br>
</p><p>
<img src='<?= SITEIMG."dot1.gif" ?>'>encouraging and enabling gardeners and farmers to grow, maintain, and disseminate these varieties through the annual seed exchange project; <br><br>
</p><p>
<img src='<?= SITEIMG."dot1.gif" ?>'>establishing and maintaining curatorial collections of Canadian varieties; <br><br>
</p><p>
<img src='<?= SITEIMG."dot1.gif" ?>'>co-operating with individuals, groups, and institutions in Canada and internationally in aid of maintaining, supplementing, and salvaging existing collections of heirloom and endangered varieties; <br><br>
</p><p>
<img src='<?= SITEIMG."dot1.gif" ?>'>encouraging commercial seed companies, nurseries, and related businesses to grow, maintain, and propagate, and commercially distribute these varieties as a means of perpetuating them. <br><br> <br><br>
</p>

<p>
<b><i>
To educate the public about the importance of heirloom and endangered varieties of food crops and the need for their continued cultivation and preservation by:
</i></b>
<br><br>
</p><p>
<img src='<?= SITEIMG."dot1.gif" ?>'>addressing the general public and interest groups including farmers, gardeners, museums, gene banks, clubs and schools; <br><br>
</p><p>
<img src='<?= SITEIMG."dot1.gif" ?>'>providing information on the proper methods of seed saving to maintain the genetic integrity of crop varieties; <br><br>
</p><p>
<img src='<?= SITEIMG."dot1.gif" ?>'>providing information on sources of heirloom and endangered varieties to those interested in obtaining and maintaining them; <br><br>
</p><p>
<img src='<?= SITEIMG."dot1.gif" ?>'>providing a forum for sharing information and open discussion on issues relating to the preservation of genetic diversity of crop plants through publications, lectures, media presentations, conferences, and exhibits; <br><br>
</p><p>
<img src='<?= SITEIMG."dot1.gif" ?>'>sharing information and experience with organizations in other nations to aid their efforts to maintain heirloom and endangered varieties.
</p>
<?
}

?>
