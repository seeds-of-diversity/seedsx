<?
header( "Location: http://www.pollinator.ca/canpolin" );
exit;

/*
    Serve PUB or maxver pages of the CPPI web site


    if defined DOCWEBSITE_TEXT_MAXVER
        Requires login to DocRepMgr=>R
    else
        Requires no login

    DocRep=>R       - you can read DocRep PUB pages as served by this script
    DocRepMgr=>R    - you can read DocRep Maxver pages as served by this script
    DocRepMgr=>W    - you can edit DocRep (using a different script)

 */

if( !defined("SITEROOT") )  define("SITEROOT", "./");
include_once( SITEROOT."site.php" );
include_once( SEEDCOMMON."doc/docWebsite.php" );

$lang = site_define_lang();

$ra = array( "lang"    => $lang,
             "docid_home" => ($lang == "FR" ? "cppi/web/home_fr" : "cppi/web/home"),
             "docid_root" => ($lang == "FR" ? "cppi/web" : "cppi/web"),
//             "docid_template" => "cppi_template01",
             "docid_extroots" => array( "cppi/img" ),
             "vars" => array( "dr_template" => "cppi/template/template01" ),
             "bDirHeirarchical" => true,
);

$oD = new DocWebsite( $ra );
$oD->Go();

?>
