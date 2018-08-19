<?php

$kfuidef =
    array( "A" =>
           array( "Label" => "Doclib Document",
                  "ListCols" => array( array( "label"=>"Title",          "col"=>"title",          "w"=>'300'), // set default programmatically below
                                       array( "label"=>"Code",           "col"=>"doc_code",       "w"=>'200'),
                                       array( "label"=>"Type",           "col"=>"doc_type",       "w"=>'50'),
                                       array( "label"=>"Year",           "col"=>"pub_year",       "w"=>'50'),
                                      ),
                  "ListSize" => 15,
//                "ListSizePad" => 1,
//                "fnListFilter"    => "Task_listFilter",
//                "fnFormDraw"      => "Task_formDraw",
//                "fnListTranslate" => "Task_listTranslate"
                ) );

$kfreldef = array( "Tables" => array(
                   array( "Table" => "doclib_document",
                          "Type"  => "Base",
                          "Fields" => "Auto" ) ) );

include_once( "../site.php" );
include_once( STDINC."SEEDForm.php" );
include_once( STDINC."KeyFrame/KFUIAppSimple.php" );
include_once( STDINC."KeyFrame/KFRForm.php" );
include_once( SEEDCOMMON."siteApp.php" );


list($kfdb2, $sess, $dummyLang) = SiteStartSessionAccount( array("DOCLIB" => "W") );
$kfdb1 = SiteKFDB( SiteKFDB_DB_seeds1 ) or die( "Cannot connect to database" );


SiteApp_KFUIAppHeader( "Seeds of Diversity DocLib Documents" );


$raParms = array();

KFUIApp_ListForm( $kfdb1, $kfreldef, $kfuidef, $sess->GetUID(), $raParms );

?>
