<?
/*
    Serve PUB or maxver pages of the Heritage Wheat Project web site


    if defined DOCWEBSITE_TEXT_MAXVER
        Requires login to DocRepMgr=>R
    else
        Requires no login

    DocRep=>R       - you can read DocRep PUB pages as served by this script
    DocRepMgr=>R    - you can read DocRep Maxver pages as served by this script
    DocRepMgr=>W    - you can edit DocRep (using a different script)

 */

if( !defined("SITEROOT") )  define("SITEROOT", "../../");
include_once( SITEROOT."site.php" );
include_once( SEEDCOMMON."doc/docWebsite.php" );

$lang = site_define_lang();

$ra = array( "lang"    => $lang,
             "docid_home" => ($lang == "FR" ? "web/hwp/fr/home" : "web/hwp/en/home"),
             "docid_root" => ($lang == "FR" ? "web/hwp/fr" : "web/hwp/en"),
             "docid_extroots" => array( "web/hwp/img" ),
             "vars" => array("dr_template" => "web/hwp/template01"),     // this can be overridden by var "dr_template" in any page or folder
             "bDirHierarchy" => true,
);

$oD = new DocWebsite( $ra );
$oD->Go();

?>
