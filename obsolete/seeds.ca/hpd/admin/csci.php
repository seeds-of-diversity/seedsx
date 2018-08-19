<?
/* Use KFUIApp_ListForm to manage CSCI Items
 */

$kfFields_Cat = array( array("col"=>"cat_company_id",  "type"=>"K" ),
                       array("col"=>"issue",           "type"=>"S" ),
                       array("col"=>"year",            "type"=>"I" ) );


//$kfrelDef_CSCI_Cat =
//    array( "Tables"=>array( array( "Table" => 'cat_catalog',
//                                   "Fields" => $kfFields_Cat ) ) );

$kfrelDef_CSCI_Item =
    array( "Tables"=>array( array( "Table" => 'cat_item',
                                   "Alias" => 'I',
                                   "Type"  => 'Base',
                                   "Fields" => array( array("col"=>"cat_catalog_id",  "type"=>"K"),
                                                      array("col"=>"pspecies",        "type"=>"S"),
                                                      array("col"=>"pspecies_ex",     "type"=>"S"),
                                                      array("col"=>"ospecies",        "type"=>"S"),
                                                      array("col"=>"pname",           "type"=>"S"),
                                                      array("col"=>"oname",           "type"=>"S"),
                                                      array("col"=>"found",           "type"=>"I"),
                                                      array("col"=>"verified",        "type"=>"I") ) ),
                            array( "Table" => 'cat_catalog',
                                   "Alias" => "C",
                                   "Type"  => 'Parent',
                                   "Fields"=> $kfFields_Cat ),
                            array( "Table" => 'rl_companies',
                                   "Alias" => "R",
                                   "Type"  => 'Grandparent',
                                   "KFCompat"=>"no",
                                   "Fields"=> array( array("col"=>"rl_cmp_id","type"=>"K"), // need this to force join
                                                     array("col"=>"name_en", "type"=>"S" ) )
                                    ) ) );


$kfuiDef_CSCI_Item =
    array( "A" =>
           array( "Label" => "Catalogue Item",
                  "ListCols" => array( array( "label"=>"Catalogue",      "col"=>"cat_catalog_id", "w"=>20 ),
                                       array( "label"=>"Parent-Company", "col"=>"R_name_en", "w"=>150 ),
                                       array( "label"=>"Index Species",  "col"=>"pspecies", "w"=>150 ),
                                       array( "label"=>"Orig Species",   "col"=>"ospecies", "w"=>150 ),
                                       array( "label"=>"Index Species Ex","col"=>"pspecies_ex", "w"=>80 ),
                                       array( "label"=>"Index Cultivar",  "col"=>"pname", "w"=>150 ),
                                       array( "label"=>"Orig Cultivar",   "col"=>"oname", "w"=>150 ),
                                     ),
                  "ListSize" => 10,
                  "ListSizePad" => 1,
                  "SearchToolCols"  => array( "Company"=>"R.name_en",
                                              "Index Species"=>"pspecies", "Orig Species"=>"ospecies",
                                              "Index Cultivar"=>"pname",   "Orig Cultivar"=>"oname" ),
                  "fnListFilter"    => "Item_rowFilter",
//                "fnFormDraw"      => "Item_formDraw",
                ) );


function Item_rowFilter()
/************************
 */
{
    // force the join constraints here, since these are not KFCompat tables
    return( "I.cat_catalog_id=C._key AND C.cat_company_id=R.rl_cmp_id" );
}



if( !defined("SITEROOT") )  define("SITEROOT", "../../");
include_once( SITEROOT."site.php" );
include_once( SITEINC."siteStart.php" );
include_once( STDINC."KeyFrame/KFUIAppSimple.php" );
include_once( HPD_ROOT."_csci.php" );

list($kfdb, $la) = SiteStartAuth( "W csci" );
//$kfdb->KFDB_SetDebug(2);

echo "<H2>Seeds of Diversity's Canadian Seed Catalogue Inventory</H2>";

KFUIApp_ListForm( $kfdb, $kfrelDef_CSCI_Item, $kfuiDef_CSCI_Item, $la->LoginAuth_UID() );


?>
