<?
define( "SITEROOT", "../../" );
include_once( SITEROOT."site.php" );
include_once( SITEINC."sitedb.php" );
include_once( STDINC."dbPhrameUI.php" );
include_once( SITEINC ."sodlogin.php" );


$la = new SoDLoginAuth;
if( !$la->SoDLoginAuth_Authenticate( $_REQUEST, "W BEANKEEPER" ) ) { exit; }


/*
CREATE TABLE beankeeper_schools (
        _rowid      INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT,
        _created    DATETIME,
        _created_by INTEGER,
        _updated    DATETIME,
        _updated_by INTEGER,
        _status     INTEGER DEFAULT 0,  # 0=normal, 1=hidden, 2=deleted

    schoolname      VARCHAR(100),
    contact         VARCHAR(100),
    contact_email   VARCHAR(100),
    address         VARCHAR(100),
    city            VARCHAR(100),
    province        VARCHAR(100),
    postcode        VARCHAR(100),
    n_kids          INTEGER,
    bSent           INTEGER,
    cv_sent         VARCHAR(100),
    n_seeds_sent    INTEGER,
    notes           TEXT,
    year            INTEGER
);

*/

$BKAdmin_RecordDef
= array( "tablename" => "beankeeper_schools",
         "fields" => array( array("name"=>"schoolname",      "type"=>"S"),
                            array("name"=>"contact",         "type"=>"S"),
                            array("name"=>"contact_email",   "type"=>"S"),
                            array("name"=>"address",         "type"=>"S"),
                            array("name"=>"city",            "type"=>"S"),
                            array("name"=>"province",        "type"=>"S"),
                            array("name"=>"postcode",        "type"=>"S"),
                            array("name"=>"n_kids",          "type"=>"I"),
                            array("name"=>"bSent",           "type"=>"I"),
                            array("name"=>"cv_sent",         "type"=>"S"),
                            array("name"=>"n_seeds_sent",    "type"=>"I"),
                            array("name"=>"notes",           "type"=>"S"),
                            array("name"=>"year",            "type"=>"I"),
                           ) );

$BKAdmin_Framedef
= array( "RelationType" => "Simple",
         "Label" => "Bean Keeper School",
         "RecordDef" => $BKAdmin_RecordDef,
         "ListCols" => array( array( "label"=>"Year",           "col"=>"year",        "w"=>20 ),
                              array( "label"=>"School",         "col"=>"schoolname",  "w"=>160 ),
                              array( "label"=>"Contact",        "col"=>"contact",     "w"=>160 ),
                              array( "label"=>"Beans sent",     "col"=>"bSent",       "w"=>30 ),
                              array( "label"=>"Cultivar sent",  "col"=>"cv_sent",     "w"=>160 ),
                              array( "label"=>"# seeds sent",   "col"=>"n_seeds_sent","w"=>20 )
                            ),
//       "ListSize" => 10,
         "fnHeader" => "BKAdmin_header",
//       "fnRowFilter" => "HPD_Species_rowFilter",
         "fnListTranslate" => "BKAdmin_listTranslate",
         "fnFormDraw" => "BKAdmin_formDraw" );


function BKAdmin_formDraw( $dPRec )
/**********************************
 */
{
    if( !$dPRec->dPR_value('year') )  $dPRec->dPR_setValue('year', 2006);

    echo "<TABLE width=100%><TR>";
    echo "<TD>Year</TD>";
    echo "<TD>".dbPhrameUI_formINPUT( $dPRec, '', 'year', 10 )."</TD>";
    echo "</TR><TR>";
    echo "<TD>School</TD>";
    echo "<TD>".dbPhrameUI_formINPUT( $dPRec, '', 'schoolname', 80 )."</TD>";
    echo "</TR><TR>";
    echo "<TD>Contact</TD>";
    echo "<TD>".dbPhrameUI_formINPUT( $dPRec, '', 'contact', 30 );
    echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
    echo "Contact Email ".dbPhrameUI_formINPUT( $dPRec, '', 'contact_email', 30 )."</TD>";
    echo "</TR><TR>";
    echo "<TD>Address</TD>";
    echo "<TD>".dbPhrameUI_formINPUT($dPRec, '', 'address', 80 )."</TD>";
    echo "</TR><TR>";
    echo "<TD>City</TD><TD>".dbPhrameUI_formINPUT( $dPRec, '', 'city', 30 );
    echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
    echo "Province ".dbPhrameUI_formINPUT( $dPRec, '', 'province', 10 );
    echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
    echo "Postcode ".dbPhrameUI_formINPUT( $dPRec, '', 'postcode', 10 )."</TD>";
    echo "</TR><TR>";
    echo "<TD># Kids</TD><TD>".dbPhrameUI_formINPUT( $dPRec, '', 'n_kids', 10 )."</TD>";
    echo "</TR><TR>";
    echo "<TD colspan=2>Beans Sent <INPUT type=checkbox name=bSent value=1".($dPRec->dPR_value('bSent') ? " CHECKED" : "").">";
    echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
    echo "Cultivar ".dbPhrameUI_formINPUT( $dPRec, '', 'cv_sent', 30 );
    echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
    echo "# seeds sent ".dbPhrameUI_formINPUT( $dPRec, '', 'n_seeds_sent', 10 )."</TD>";
    echo "</TR><TR>";
    echo "<TD>Notes</TD><TD><TEXTAREA NAME=notes COLS=70 ROWS=3 WRAP=SOFT>".$dPRec->dPR_valueEnt('notes')."</TEXTAREA></TD>";
    echo "</TR></TABLE>";
    echo "<BR>";
}


function BKAdmin_header($dpui)
/*****************************
 */
{
//  $phfltcat  = BXStd_SafeGPCGetStrPlain('phfltcat');

    echo "<H2>Bean Keepers Schools</H2>";
/*
 *    echo "<TD align=right><FORM action='{$_SERVER['PHP_SELF']}' target='_top'>";
 *]/ should be a way to automatically exclude the search parm, since it's specified below.
 *    echo dbPhrameUI_User_HiddenFormParms( $dpui, array("keepSel"=>false),array("phfltcat","phfltsrch") );
 *    echo "<SELECT name=phfltcat onChange='submit();'><OPTION value=''".(empty($phfltcat) ? " SELECTED" : "")."> -- All Categories -- </OPTION>";
 *    if( $dbc = db_open( "SELECT category FROM hpd_species GROUP BY category ORDER BY category" ) ) {
 *        while( $ra = db_fetch( $dbc ) ) {
 *            echo "<OPTION value='{$ra[0]}'".(($phfltcat==$ra[0]) ? " SELECTED" : "").">{$ra[0]}</OPTION>";
 *        }
 *    }
 *    echo "</SELECT>";
 *    echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
 *    echo dbPhrameUI_headerSearch( $dpui, "phfltsrch" );
 *    echo "<INPUT type=submit value=Go></FORM></TD>";
 */
}


function BKAdmin_listTranslate( $dPRec )
/***************************************
 */
{
    global $today;

    $ra = array();

    switch( $dPRec->dPR_value('bSent') ) {
        case 0:  $ra['bSent'] = "<FONT color=red>Not yet</FONT>"; break;
        case 1:  $ra['bSent'] = "Yes";     break;
    }


//  if( $dPRec->dPR_value('startdate') == "0000-00-00" )  $ra['startdate'] = "";
//  if( $dPRec->dPR_value('enddate')   == "0000-00-00" )  $ra['enddate'] = "";


    return( $ra );
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



dbPhrameUI( $BKAdmin_Framedef, $la->LoginAuth_UID() );

?>
