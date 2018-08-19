<?php

/*  Erb Street Mennonite Church C.E. contacting list
 */

define("ERBST_CE_LIST",
"
CREATE TABLE erbst_ce_list (
        _key        INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT,
        _created    DATETIME,
        _created_by INTEGER,
        _updated    DATETIME,
        _updated_by INTEGER,
        _status     INTEGER DEFAULT 0,

    grade           VARCHAR(100),   -- groups rows by grade (Primary, Middler, etc)
    name            VARCHAR(100),   -- person contacted
    contacted_by    VARCHAR(100),   -- CE person who contacted them
    comments        TEXT,
    eStatus         VARCHAR(100)
);
"
);

include("../site.php");
include(STDINC."KeyFrame/KFUIForm.php");
include(SEEDCOMMON."siteStart.php");

list($kfdb,$sess) = SiteStartSession();

$kfrdef =
    array( "Tables"=>array( array( "Table" => 'erbst_ce_list',
                                   "Fields" => "Auto" ) ) );


$kfrel = new KeyFrameRelation( $kfdb, $kfrdef, 0 );
$kfuiFormDef = array( "grade" => array( "label" => "Grade", "type" => "hidden", "readonly"=>true, "presetOnInsert" =>true ),
                      "name" => array( "label" => "Name", "type" => "text", "size" => 20 ),
                      "contacted_by" => array( "label" => "Contacted by", "type" => "text", "size" => 15 ),
                      "eStatus" => array( "label" => "Status", "type" => "text", "size" => 15 ),
                      "comments" => array( "label" => "Comments", "type" => "textarea", "rows" => 2 ),
                      "_sf_op_h" => array( "label" => "Delete")
                    );

$raSections = array(
    'J' => array('vbs', "VBS Volunteers"),
    'K' => array('vbs_kids', "VBS Children/Youth"),
    'A' => array('preschool', "Preschool"),
    'B' => array('primary', "Primary"),
    'C' => array('middler', "Middler"),
    'D' => array('jr_youth', "Jr. Youth"),
    'E' => array('youth', "Youth"),
    'F' => array('assembly', "Assembly"),
    'G' => array('games', "Games Leaders"),
    'H' => array('snack', "Snack Coordinator"),
    'I' => array('other', "Others Approached"),
);



if( @$_REQUEST['report']==1 ) {
    include_once( STDINC."KeyFrame/KFRTable.php" );

    $kfrc = $kfrel->CreateRecordCursor( "" );
    $xls = new KFTableDump();
    $xls->XLS_Dump( $kfrc, array( "header_filename" => "ErbStCE.xls",
                                  "cols" => array( "grade", "name", "contacted_by", "eStatus", "comments") ) );
    exit;
}

//$kfdb->SetDebug(2);
//var_dump($_REQUEST);
//echo "<BR/><BR/>";
//var_dump($_SESSION);


// Make an oForm for each section, though everything is the same except the cid.
// Alternatively, we could use one oForm and set the cid on each section.
$raOForm = array();
foreach( $raSections as $cid => $v ) {
    $raOForm[$cid] = new KeyFrameUIForm( $kfrel, $cid, array('formdef' => $kfuiFormDef) );
}

foreach( $raSections as $cid=>$v ) {
    $raOForm[$cid]->Update();

    if( isset($_REQUEST['p_'.$v[0]]) )  $_SESSION['erbstce'.$v[0]] = intval($_REQUEST['p_'.$v[0]]);
}

echo "<STYLE>"
    ."h2,h3 {font-family:verdana,helvetica,sans serif;}"
    ."th,td {font-family:verdana,helvetica,sans serif; font-size:10pt;}"
    .".nonedit th, .nonedit td {font-family:verdana,helvetica,sans serif; font-size:8pt;}"
    ."th { background-color:#ccccdd;}"
    ."</STYLE>";

echo "<H2>Erb Street C.E. Contacts</H2>"
    ."<P><I>Click headings to edit each section</I>"
    .SEEDStd_StrNBSP("",20)
    ."<A HREF='".$_SERVER['PHP_SELF']."?report=1'>Download spreadsheet</A>"
    ."</P>";


echo "<FORM method='post' action='${_SERVER['PHP_SELF']}'>";

foreach( $raSections as $cid=>$v ) {
    $grade = $v[0];
    $bEdit = intval(@$_SESSION['erbstce'.$grade]);

    echo "<H3 id='header_${v[0]}'><A href='${_SERVER['PHP_SELF']}?p_${v[0]}=".intval(!$bEdit)."'>".$v[1]."</A></H3>";

    if( $bEdit ) {
        $oForm = &$raOForm[$cid];

        echo $oForm->FormTableStart()
            .$oForm->FormTableHeader();

        if( ($kfr = $kfrel->CreateRecordCursor("grade='$grade'")) ) {
            while( $kfr->CursorFetch()) {
                $oForm->SetKFR( $kfr );
                echo $oForm->FormTableRow();
            }
        }
        $kfr = $kfrel->CreateRecord();
        $kfr->SetValue("grade",$grade);
        $oForm->SetKFR( $kfr );
        echo $oForm->FormTableRow();

        echo $oForm->FormTableEnd();
        echo "<BR><INPUT type='submit' name='submit_${v[0]}' value='Save'>";
    } else {
        // Just draw the names and basic data
        echo "<TABLE class='nonedit' cellpadding='5' cellspacing='0' border='1' width='80%'>"
            ."<TR><TH width='15%'>Name</TH><TH width='15%'>Contacted by</TH><TH width='15%'>Status</TH><TH width='55%'>Comments</TH></TR>";

        if( ($kfr = $kfrel->CreateRecordCursor("grade='$grade'")) ) {
	    while( $kfr->CursorFetch()) {
                echo $kfr->Expand( "<TR valign='top'><TD>[[name]]&nbsp;</TD><TD>[[contacted_by]]&nbsp;</TD><TD>[[eStatus]]&nbsp;</TD><TD>[[comments]]&nbsp;</TD></TR>" );
            }
        }
        echo "</TABLE>";
    }
}

/* This comes afterward because the JS is executed while the doc is drawn. If the JS is placed in the loop above, the scroll cannot put the
 * header at the top of the window if the current section is less than the window height.
 */
foreach( $raSections as $cid=>$v ) {
    if( isset($_REQUEST['p_'.$v[0]]) || isset($_REQUEST['submit_'.$v[0]]) ) {
        echo "<SCRIPT>document.all.header_${v[0]}.scrollIntoView(true);</SCRIPT>";
    }
}

echo "</FORM>";

?>
