<?php

if( !defined("SITEROOT") )  define("SITEROOT", "../");
include_once( SITEROOT."site2.php" );

$raConfig = [
    'rootdir' => '/home/bob/junk/imgtest/',
    'imgmanlib' => ['targetExt'=>'jpg']
];

include_once( SEEDAPP."imgman/ImgManager.php" );
