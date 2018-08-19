<?php

define( "SITEROOT", "../../" );
include_once( SITEROOT."site.php" );
include_once( PAGE1_TEMPLATE );
include_once( "../_info.php" );
include_once( STDINC."DocRep/DocRep.php" );


$page1parms = array (
                "lang"      => "EN",
                "title"     => "About Seeds of Diversity",
                "tabname"   => "ABOUT",
                "box1title" => "More Information",
                "box1fn"    => "box1fn_en",
                "box2title" => "Contact Us",
                "box2fn"    => "box2fn_en"
             );



Page1( $page1parms );


function Page1Body() {
?>
<h2>About Seeds of Diversity</h2>
<p>
Seeds of Diversity is a Canadian charitable organization dedicated to the conservation,
documentation and use of public-domain, non-hybrid plants of Canadian significance.
Our 1400 members from coast to coast are gardeners, farmers, teachers,
scientists, agricultural historians, researchers and seed vendors.  Together we grow, propagate
and distribute over 2900 varieties
of vegetables, fruit, grains, flowers and herbs.  We are a living gene bank.</p>

<p>
Formerly known as the Heritage Seed Program, a project of the Canadian Organic Growers since 1984,
Seeds of Diversity Canada is now an independent charitable corporation operated by a volunteer board
of directors.  Our work is funded mainly by membership fees and private donations.</p>

<p>
Members receive our 40-page magazine <i>Seeds of Diversity</i> twice a year, plus our annual
Member Seed Directory which allows members to obtain samples
of over 2900 varieties of seeds and plants offered by other members in exchange for return postage.</p>

<?php

/*
DR_link( "../dr/AR2007EN.pdf",
         "Annual Report 2007",
         "",
         array( "icon"   => SITEIMG_STDIMG."icon-pdf-l.gif",
                "target" => "_blank",
                "author" => "Seeds of Diversity",
                "date"   => "2007" ) );
DR_link( "../dr/AR2006EN.pdf",
         "Annual Report 2006",
         "",
         array( "icon"   => SITEIMG_STDIMG."icon-pdf-l.gif",
                "target" => "_blank",
                "author" => "Seeds of Diversity",
                "date"   => "2006" ) );
DR_link( "../dr/AR2005EN.pdf",
         "Annual Report 2005",
         "",
         array( "icon"   => SITEIMG_STDIMG."icon-pdf-l.gif",
                "target" => "_blank",
                "author" => "Seeds of Diversity",
                "date"   => "2005" ) );
*/
}
?>
