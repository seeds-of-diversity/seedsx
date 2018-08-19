<?
define( "SITEROOT", "../../" );
include_once( SITEROOT."site.php" );
include_once( SITEINC."sitedb.php" );
include_once( HPD_ROOT."_hpd.php" );
include_once( STDINC."dbPhrameUI.php" );
include_once( SITEINC ."sodlogin.php" );


$la = new SoDLoginAuth;
if( !$la->SoDLoginAuth_Authenticate( $_REQUEST, "W hpd" ) ) { exit; }


$HPD_Species_Framedef
= array( "RelationType" => "Simple",
         "Label" => "Species",
         "RecordDef" => $HPD_Species_RecordDef,
         "ListCols" => array( array( "label"=>"Category",         "col"=>"category",  "w"=>25 ),
                              array( "label"=>"pspecies",         "col"=>"pspecies",  "w"=>150 ),
                              array( "label"=>"Index name",       "col"=>"iname",     "w"=>150 ),
                              array( "label"=>"Common name (EN)", "col"=>"name",      "w"=>150 ),
                              array( "label"=>"Common name (FR)", "col"=>"name_fr",   "w"=>100 ),
                              array( "label"=>"Botanical name",   "col"=>"botname",   "w"=>140 ),
                              array( "label"=>"Family",           "col"=>"botfamily", "w"=>100 ),
                            ),
         "ListSize" => 10,
         "fnHeader" => "HPD_Species_header",
         "fnRowFilter" => "HPD_Species_rowFilter",
         "fnFormDraw" => "HPD_Species_formDraw" );


function HPD_Species_formDraw( $dPRec )
/**************************************
 */
{
    echo "<TABLE></TR>";
    echo "<TD>pspecies</TD><TD>".dbPhrameUI_formINPUT( $dPRec, '', 'pspecies', 30 )."</TD>";
    echo "<TD width=40>&nbsp;</TD>";
    echo "<TD>Common</TD><TD>".dbPhrameUI_formINPUT( $dPRec, '', 'name', 30 )."</TD>";
    echo "</TR><TR>";
    echo "<TD>Botanical</TD><TD>".dbPhrameUI_formINPUT($dPRec, '', 'botname', 30 )."</TD>";
    echo "<TD width=40>&nbsp;</TD>";
    echo "<TD>Index Species</TD><TD>".dbPhrameUI_formINPUT( $dPRec, '', 'iname', 30 )."</TD>";
    echo "</TR><TR>";
    echo "<TD>Family</TD><TD>".dbPhrameUI_formINPUT( $dPRec, '', 'botfamily', 30 )."</TD>";
    echo "<TD width=40>&nbsp;</TD>";
    echo "<TD>Common French</TD><TD>".dbPhrameUI_formINPUT( $dPRec, '', 'name_fr', 30 )."</TD>";
    echo "</TR><TR>";
    echo "<TD>Category</TD><TD><SELECT name='category'>";
    if( $dbc = db_open( "SELECT category FROM hpd_species GROUP BY category ORDER BY category" ) ) {
        while( $ra = db_fetch( $dbc ) ) {
            echo "<OPTION value='{$ra[0]}'".(($dPRec->dPR_value('category')==$ra[0]) ? " SELECTED" : "").">{$ra[0]}</OPTION>";
        }
    }
    echo "</SELECT></TD>";
    echo "</TR></TABLE>";
    echo "<BR>";
}


function HPD_Species_header($dpui)
/*********************************
 */
{
    $phfltcat  = BXStd_SafeGPCGetStrPlain('phfltcat');

    echo "<TABLE width='100%'><TR><TD><H2>HPD Master Species Table</H2></TD><TD>&nbsp;</TD>";
    echo "<TD align=right><FORM action='{$_SERVER['PHP_SELF']}' target='_top'>";
// should be a way to automatically exclude the search parm, since it's specified below.
    echo dbPhrameUI_User_HiddenFormParms( $dpui, array("keepSel"=>false),array("phfltcat","phfltsrch") );
    echo "<SELECT name=phfltcat onChange='submit();'><OPTION value=''".(empty($phfltcat) ? " SELECTED" : "")."> -- All Categories -- </OPTION>";
    if( $dbc = db_open( "SELECT category FROM hpd_species GROUP BY category ORDER BY category" ) ) {
        while( $ra = db_fetch( $dbc ) ) {
            echo "<OPTION value='{$ra[0]}'".(($phfltcat==$ra[0]) ? " SELECTED" : "").">{$ra[0]}</OPTION>";
        }
    }
    echo "</SELECT>";
    echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
    echo dbPhrameUI_headerSearch( $dpui, "phfltsrch" );
    echo "<INPUT type=submit value=Go></FORM></TD>";
    echo "</TR></TABLE>";
}


function HPD_Species_rowFilter()
/*******************************
 */
{
    $phfltcat  = BXStd_SafeGPCGetStr('phfltcat');
    $phfltsrch = BXStd_SafeGPCGetStr('phfltsrch');

    $s = "";
    if( !empty($phfltcat['plain']) ) {
        $s .= "(category = '{$phfltcat['db']}')";
    }
    if( !empty($phfltsrch['plain']) ) {
        if( !empty($s) )  $s .= " AND ";
        $s .= "(pspecies  like '{$phfltsrch['db']}' OR ";
        $s .= " botname   like '{$phfltsrch['db']}' OR ";
        $s .= " name      like '{$phfltsrch['db']}' OR ";
        $s .= " iname     like '{$phfltsrch['db']}' OR ";
        $s .= " botfamily like '{$phfltsrch['db']}')";
    }
    return( $s );
}



dbPhrameUI( $HPD_Species_Framedef, $la->LoginAuth_UID() );

?>
