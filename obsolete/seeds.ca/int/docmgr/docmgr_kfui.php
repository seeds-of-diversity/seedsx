<?
/* Use KFUIApp_ListForm to manage DocRep documents
 *
 * There are two modes - Base=Doc and Base=Data
 */



/* Doc mode
 */
$kfuiDef_DocRepDoc =
    array( "A" =>
           array( "Label" => "Document",
                  "ListCols" => array( array( "label"=>"Name",           "col"=>"name", "w"=>100 ),
                                       array( "label"=>"Type",           "col"=>"type", "w"=>50 ),
                                       array( "label"=>"Status",         "col"=>"status", "w"=>50 ),
                                       array( "label"=>"Title",          "col"=>"Data_meta_title", "w"=>150 ),
                                       array( "label"=>"Author",          "col"=>"Data_meta_author", "w"=>100 ),
                                     ),
                  "ListSize" => 10,
                  "ListSizePad" => 1,
//                  "SearchToolCols"  => array( "Company"=>"R.name_en",
//                                              "Index Species"=>"pspecies", "Orig Species"=>"ospecies",
//                                              "Index Cultivar"=>"pname",   "Orig Cultivar"=>"oname" ),
//                  "fnListFilter"    => "Item_rowFilter",
//                "fnFormDraw"      => "Item_formDraw",
                ) );
/* Data mode
 */
$kfuiDef_DocRepData =
    array( "A" =>
           array( "Label" => "Doc Data",
                  "ListCols" => array( array( "label"=>"Name",           "col"=>"Doc_name", "w"=>100 ),
                                       array( "label"=>"Type",           "col"=>"Doc_type", "w"=>50 ),
                                       array( "label"=>"Status",         "col"=>"Doc_status", "w"=>50 ),
                                       array( "label"=>"Title",          "col"=>"meta_title", "w"=>150 ),
                                       array( "label"=>"Author",          "col"=>"meta_author", "w"=>100 ),
                                     ),
                  "ListSize" => 10,
                  "ListSizePad" => 1,
//                  "SearchToolCols"  => array( "Company"=>"R.name_en",
//                                              "Index Species"=>"pspecies", "Orig Species"=>"ospecies",
//                                              "Index Cultivar"=>"pname",   "Orig Cultivar"=>"oname" ),
//                  "fnListFilter"    => "Item_rowFilter",
//                "fnFormDraw"      => "Item_formDraw",
                ) );



if( !defined("SITEROOT") )  define("SITEROOT", "../../");
include_once( SITEROOT."site.php" );
include_once( SITEINC."siteStart.php" );
include_once( STDINC."KeyFrame/KFUIAppSimple.php" );
include_once( STDINC."DocRep/DocRepDB.php" );

list($kfdb, $la) = SiteStartAuth( "W DocRepMgr" );
//$kfdb->KFDB_SetDebug(2);


if( $_REQUEST['docmgr_mode'] == 'doc' ) $mode = 'doc';
else                                    $mode = 'data';


echo "<H2>Seeds of Diversity's Online Document Repository".($mode=='doc'? " - Doc Mode" : " - Data Mode")."</H2>";

echo "<P><A HREF='${_SERVER['PHP_SELF']}?docmgr_mode=doc'>Doc Mode</A>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
echo "<A HREF='${_SERVER['PHP_SELF']}?docmgr_mode=data'>Data Mode</A></P>";


if( $mode == 'doc' ) {
    KFUIApp_ListForm( $kfdb, $kfrelDef_DocXData, $kfuiDef_DocRepDoc, $la->LoginAuth_UID() );  // get kfrel from DocRepDB object
} else {
    KFUIApp_ListForm( $kfdb, $kfrelDef_DocDataXDoc, $kfuiDef_DocRepData, $la->LoginAuth_UID() );  // get kfrel from DocRepDB object
}


?>
