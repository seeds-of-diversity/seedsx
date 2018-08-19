<?
/* Serve a document from the Document Repository
 */

if( !defined("SITEROOT") )  define("SITEROOT", "../");
include_once( SITEROOT."site.php" );
include_once( SITEINC."siteStart.php" );
include_once( STDINC."DocRep/DocRep.php" );
include_once( STDINC."DocRep/DocRepDB.php" );

list($kfdb, $la) = SiteStartAuth( "W DocRepMgr" );
//$kfdb->KFDB_SetDebug(2);


define( "DOCREP_UPLOAD_DIR", SITEROOT."../docrep_upload/" );

$docrepDB = new DocRepDB( $kfdb, $la->LoginAuth_UID() );


if( ($keyDoc = SEEDSafeGPC_GetInt( "k" ) ) ) {
    $docrepDB->ServeFile( $keyDoc );
} else {
    die( "Document unspecified" );
}


?>
