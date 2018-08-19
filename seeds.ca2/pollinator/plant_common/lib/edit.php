<?php
include_once( "fcinit.php" );
include_once( "update.php" );

require_once(BOOTSTRAP.'Image-master/autoload.php');
use Gregwar\Image\Image;

if( @$_POST['editSubmit'] != "editSubmit" ) { header( "Location: admin.php" );   exit; }

if( !($id = SEEDSafeGPC_GetInt('Doc_ID')) ) { header( "Location: admin.php" );   exit; }

UpdateRecord( $kfdb, $id );

header("Location: admin.php?showplant=$id");

?>
