<?php

/* console01ui
 *
 * Basic console UI pieces
 *
 * Copyright (c) 2016 Seeds of Diversity Canada
 */

include_once( STDINC."SEEDTable.php" );


function Console01UI_DownloadUpload( SEEDApp_WorkerC $oW, $raParms )
/*******************************************************************
    Draw a UI to download a spreadsheet and upload it again.
        Download needs a link that makes a spreadsheet, and appropriate filtering controls.
        Upload needs a SEEDTable definition, and a function to facilitate the upload process (like a Stepper).
 */
{
    $s = "";

    $oTable = new SEEDTable( $raParms['seedTableDef'] );

    $s .= "<h4 class='DownloadBodyHeading'>Download a spreadsheet of {$raParms['label']}</h4>"
         ."<div style='padding:1em'>"
             ."<form method='post' action='{$raParms['downloadaction']}'>"
             .$raParms['downloadctrl']
             ."<input type='submit' value='Download'/>"
             ."</form>"
         ."</div>"
         ."<hr/>"

         ."<h4 class='DownloadBodyHeading'>Upload a spreadsheet of {$raParms['label']}</h4>"
         ."<div style='border:1px solid #aaa;padding:10px;margin:20px;width:500px'>"
             ."<style>"
             .".console01_instructions table th {font-size:10pt}"
             ."</style>"
             ."<div class='console01_instructions' style='margin:0px 0px 15px 15px;'>"
             ."<p>The first row of the spreadsheet must have these names (in any order).".$oTable->SampleHead()."</p>"
             ."</div>"
             ."<form action='{$raParms['uploadaction']}' method='post' enctype='multipart/form-data'>"
             ."<input type='hidden' name='MAX_FILE_SIZE' value='10000000' />"
             .@$raParms['uploadctrl']
             ."<table style='margin-left:20px' border='0'><tr>"
             ."<td><input style='display:inline' type='file' name='upfile'/></td>"
             ."<td><input style='display:inline' type='submit' name='action' value='Upload' style='float:right'/></td>"
             ."</tr><tr>"
             ."<td style='padding-top:5px'><select name='upfile-format'>"
                 ."<option value='xls'>Spreadsheet file</xls>"
                 ."<option value='csv-utf8'>CSV in utf8</xls>"
                 ."<option value='csv-win1252'>CSV in win1252</xls>"
             ."</td><td>"
             ."</tr></table>"
             .@$raParms['uploadctrlbottom']
             ."</form>"
         ."</div>";
    return( $s );
}

