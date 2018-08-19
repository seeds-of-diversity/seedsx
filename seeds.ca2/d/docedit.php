<?php

/*
 * docedit.php
 *
 * Copyright 2013-2014 Seeds of Diversity Canada
 *
 * Manage our document repository
 */

if( !defined("SITEROOT") )  define("SITEROOT", "../");
include_once( SITEROOT."site.php" );

$DocMgrParms['title'] = "Seeds of Diversity Website Documents (www.seeds.ca / www.semences.ca)";
include_once( SEEDCOMMON."doc/DocMgrConsole.php" );

?>
