<?
/*
    Serve PUB or maxver pages of the Pollination Canada web site


    if defined DOCWEBSITE_TEXT_MAXVER
        Requires login to DocRepMgr=>R
    else
        Requires no login

    DocRep=>R       - you can read DocRep PUB pages as served by this script
    DocRepMgr=>R    - you can read DocRep Maxver pages as served by this script
    DocRepMgr=>W    - you can edit DocRep (using a different script)


Links:
http://www.xerces.org/pubs_merch/xerces_publications.htm
http://www.xerces.org/pubs_merch/


 */

if( !defined("SITEROOT") )  define("SITEROOT", "../../");
include_once( SITEROOT."site.php" );
include_once( SEEDCOMMON."doc/docWebsite.php" );

$lang = site_define_lang();

$ra = array( "lang"    => $lang,
             "docid_home" => ($lang == "FR" ? "pcfr_home" : "pc_rootfolder/pc_home"),
             "docid_root" => ($lang == "FR" ? "pcfr_rootfolder" : "pc_rootfolder"),
             "docid_extroots" => array( "pc_web_image_root","pc" ),
             "vars" => array("dr_template" => "pc_template01"),     // this can be overridden by var "dr_template" in any page or folder
             "bDirHierarchy" => true,
);

$oD = new DocWebsite( $ra );
$oD->Go();

?>
