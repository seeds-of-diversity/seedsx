<?
include_once( "../_proj.php" );


function CTP_articles() {
    proj_link( SITEROOT."lib/mag/1990_08_rafuse.php",
               "Canadian Bred Tomatoes",
               "Article from Heritage Seed Program magazine",
               array( "target" => "_blank",
                      "author" => "Christine Rafuse",
                      "date"   => "August 1990" ) );

    proj_link( "cdntomatoes.php",
               "Canadian Tomato Cultivars",
               "A list of tomatoes bred or adapted in Canada.",
               array( "target" => "_blank",
                      "author" => "Jim Ternier",
                      "date"   => "2006" ) );

    proj_link( SITEROOT."lib/cv/Tomato-Rosabec.php",
               "The Rosabec Tomato",
               "The history of a beautiful pink-skinned Canadian tomato.",
               array( "target" => "_blank",
                      "author" => "Jim Ternier",
                      "date"   => "2004" ) );


    proj_link( "SeedExtr_EN.pdf",
               "A Method of Extracting Seeds from Tomatoes",
               "",
               array( "icon"   => SITEIMG_STDIMG."icon-pdf-l.gif",
                      "target" => "_blank",
                      "author" => "Agriculture and Agri-Food Canada, Plant Gene Resources of Canada",
                      "date"   => "2005" ) );
    proj_link( "SeedExtr_FR.pdf",
               "Une méthode d'extraction des graines des tomates",
               "",
               array( "icon"   => SITEIMG_STDIMG."icon-pdf-l.gif",
                      "target" => "_blank",
                      "author" => "Agriculture et Agroalimentaire Canada, Ressources phytogénétiques du Canada",
                      "date"   => "2005" ) );

    proj_link( "Desc_Tomato_EN.pdf",
               "Tomato Information Sheet",
               "Observation form for participants in the Canadian Tomato Project.",
               array( "icon"   => SITEIMG_STDIMG."icon-pdf-l.gif",
                      "target" => "_blank",
                      "author" => "Agriculture and Agri-Food Canada, Plant Gene Resources of Canada",
                      "date"   => "2005" ) );
    proj_link( "Desc_Tomato_FR.pdf",
               "Feuille d'information sur les tomates",
               "",
               array( "icon"   => SITEIMG_STDIMG."icon-pdf-l.gif",
                      "target" => "_blank",
                      "author" => "Agriculture et Agroalimentaire Canada, Ressources phytogénétiques du Canada",
                      "date"   => "2005" ) );
}


?>
