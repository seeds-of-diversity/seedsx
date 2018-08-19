<?

$kfuiDef_CSCI_Company =
    array( "A" =>
           array( "Label" => "Company",
                  "ListCols" => array( array( "label"=>"Company Name",      "col"=>"name_en",      "w"=>150 ),
                                       array( "label"=>"Province/State",    "col"=>"prov",         "w"=>50 ),
                                       array( "label"=>"Country",           "col"=>"country",      "w"=>50 ),
                                       array( "label"=>"Phone",             "col"=>"phone",        "w"=>100 ),
                                       array( "label"=>"Email",             "col"=>"email",        "w"=>100 ),
                                       array( "label"=>"Web",               "col"=>"web",          "w"=>100 ),
                                       array( "label"=>"Show in RL",        "col"=>"bRLShow",      "w"=>20 ),
                                       array( "label"=>"Needs Verify",      "col"=>"bNeedVerify",  "w"=>20 ),
                                       array( "label"=>"Needs Proof",       "col"=>"bNeedProof",   "w"=>20 ),
                                       array( "label"=>"Needs Translation", "col"=>"bNeedXlat",    "w"=>20 ),
                                     ),
                  "ListSize" => 10,
                  "ListSizePad" => 1,
//                "SearchToolCols"  => array( "Company"=>"R.name_en",
//                                            "Index Species"=>"pspecies", "Orig Species"=>"ospecies",
//                                            "Index Cultivar"=>"pname",   "Orig Cultivar"=>"oname" ),
//                "fnListFilter"    => "Item_rowFilter",
//                "fnFormDraw"      => "Item_formDraw",
                ) );



if( !defined("SITEROOT") )  define("SITEROOT", "../../../");
include_once( SITEROOT."site.php" );
include_once( SEEDCOMMON."siteStart.php" );
include_once( STDINC."KeyFrame/KFUIAppSimple.php" );
include_once( HPD_ROOT."csci/_csci.php" );

list($kfdb, $sess) = SiteStartSessionAuth( array("CSCI"=>"W") );
//$kfdb->KFDB_SetDebug(2);

echo "<H2>Seeds of Diversity's Canadian Seed Catalogue Inventory</H2><H2>Companies</H2>";

KFUIApp_ListForm( $kfdb, $kfrelDef_CSCI_Company, $kfuiDef_CSCI_Company, $sess->GetUID() );


?>
