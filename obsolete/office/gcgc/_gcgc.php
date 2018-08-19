<?

/*
CREATE TABLE gcgc_growers (

        _key        INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT,
        _created    DATETIME,
        _created_by INTEGER,
        _updated    DATETIME,
        _updated_by INTEGER,
        _status     INTEGER DEFAULT 0,

    fk_mbr_contacts INTEGER NOT NULL,
--  name            VARCHAR(200),
    status          enum('NEW','ACTIVE','PENDING-ACTIVE','INACTIVE','STOPPED') NOT NULL DEFAULT 'NEW',
    workflow        TEXT,
    notes           TEXT
);

CREATE TABLE gcgc_varieties (

        _key        INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT,
        _created    DATETIME,
        _created_by INTEGER,
        _updated    DATETIME,
        _updated_by INTEGER,
        _status     INTEGER DEFAULT 0,

    name            VARCHAR(200),   -- (name,origin) is UNIQUE
    origin          VARCHAR(200),
    index_name      VARCHAR(200),
    status          enum('NEW','ACTIVE','INACTIVE','DROPPED') NOT NULL DEFAULT 'NEW',
    workflow        TEXT,
    notes           TEXT,
    nAvailable      INTEGER DEFAULT 0
);

CREATE TABLE gcgc_gxv (

        _key        INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT,
        _created    DATETIME,
        _created_by INTEGER,
        _updated    DATETIME,
        _updated_by INTEGER,
        _status     INTEGER DEFAULT 0,

    fk_gcgc_growers     INTEGER NOT NULL,
    fk_gcgc_varieties   INTEGER NOT NULL,
    year_start          INTEGER,
    year_last_verified  INTEGER,
    status              enum('ACTIVE','INACTIVE','DROPPED'),
    workflow            TEXT,
    notes               TEXT,

    INDEX (fk_gcgc_growers),
    INDEX (fk_gcgc_varieties),
    INDEX (status)
);
*/

$kfFields_G = array( array("col"=>"fk_mbr_contacts",  "type"=>"K"),
              //     array("col"=>"name",             "type"=>"S"),
                     array("col"=>"status",           "type"=>"S"),
                     array("col"=>"workflow",         "type"=>"S"),     // Console uses SetValuePrepend to simulate S+
                     array("col"=>"notes",            "type"=>"S") );   // Console uses SetValuePrepend to simulate S+

$kfFields_V = array( array("col"=>"name",             "type"=>"S"),
                     array("col"=>"origin",           "type"=>"S"),
                     array("col"=>"index_name",       "type"=>"S"),
                     array("col"=>"status",           "type"=>"S"),
                     array("col"=>"workflow",         "type"=>"S"),
                     array("col"=>"notes",            "type"=>"S"),
                     array("col"=>"nAvailable",       "type"=>"I") );

$kfrelDef_GCGC_Growers =
    array( "Tables"=>array( array( "Table" => 'gcgc_growers',
                                   "Fields" => $kfFields_G ) ) );
$kfrelDef_GCGC_GrowersXContacts =
    array( "Tables"=>array( array( "Table" => 'gcgc_growers',
                                   "Type" => "Base",
                             //      "Alias" => "G",    this would be handy for specifying conditions on CreateRecordCursor, but how would it affect other existing conditions if any i.e. they would be using base field names instead of G.base
                                   "Fields" => $kfFields_G ),
                            array( "Table"=> 'mbr_contacts',
                                   "Type" => "Related",
                                   "Alias" => "M",
                                   "Fields" => array( array("col"=>"firstname", "type"=>"S"),
                                                      array("col"=>"lastname", "type"=>"S"),
                                                      array("col"=>"company", "type"=>"S"),
                                                      array("col"=>"dept", "type"=>"S"),
                                                      array("col"=>"lang", "type"=>"S"),
                                                      array("col"=>"email", "type"=>"S"),
                                                      array("col"=>"expires", "type"=>"S") ) ),
                                    ) );
// kluge to allow Samples form to use G_status in condition
$kfrelDef_GCGC_GrowersXContacts_withAliasG = $kfrelDef_GCGC_GrowersXContacts;
$kfrelDef_GCGC_GrowersXContacts_withAliasG["Tables"][0]["Alias"] = "G";

$kfrelDef_GCGC_Varieties =
    array( "Tables"=>array( array( "Table" => 'gcgc_varieties',
                                   "Fields" => $kfFields_V ) ) );

$kfrelDef_GCGC_Samples =
    array( "Tables"=>array( array( "Table" => 'gcgc_gxv',
                                   "Type" => "Base",
                                   "Alias" => "S",
                                   "Fields" => array( array("col"=>"fk_gcgc_growers",   "type"=>"K"),
                                                      array("col"=>"fk_gcgc_varieties", "type"=>"K"),
                                                      array("col"=>"year_start",        "type"=>"I"),
                                                      array("col"=>"year_last_verified","type"=>"I"),
                                                      array("col"=>"status",            "type"=>"S"),
                                                      array("col"=>"workflow",          "type"=>"S"),   // Console uses SetValuePrepend to simulate S+
                                                      array("col"=>"notes",             "type"=>"S") ) ),
                            array( "Table" => 'gcgc_growers',
                                   "Alias" => "G",
                                   "Type"  => 'Parent',
                                   "Fields"=> $kfFields_G ),
                            array( "Table" => 'gcgc_varieties',
                                   "Alias" => "V",
                                   "Type"  => 'Parent',
                                   "Fields"=> $kfFields_V ) ) );


$kfuiDef_GCGC_Growers =
    array( "A" =>
           array( "Label" => "Garlic Grower",
                  "ListCols" => array( array( "label"=>"Number",         "col"=>"fk_mbr_contacts", "w"=>20 ),
                                       array( "label"=>"Name",           "col"=>"name",            "w"=>150 ),
                                       array( "label"=>"Status",         "col"=>"status",          "w"=>150 ),
                                     ),
                  "ListSize" => 10,
//                "ListSizePad" => 1,
                  "SearchToolCols"  => array( "Member Id"=>"fk_mbr_contacts",
                                              "Last Name"=>"M.lastname",
                                              "First Name"=>"M.firstname",
                                              "Company"=>"M.company",
                                              "Dept"=>"M.dept" ),
//                "fnListFilter"    => "Item_rowFilter",
                  "fnFormDraw"      => "GCGC_G_formDraw",
                  "Controls_disallowNew" => 1,
                  "Controls_disallowDelete" => 1,
                ),
           "Parms" => array( "fnGetUserName" => "myGetUserName" )
           );

$kfuiDef_GCGC_GrowersXContacts =
    array( "A" =>
           array( "Label" => "Garlic Grower",
                  "ListCols" => array( array( "label"=>"Member Id",      "col"=>"fk_mbr_contacts", "w"=>20 ),
                                       array( "label"=>"Grower Name",    "col"=>"M_firstname",     "w"=>250 ),  // xlat to mbr_makeName
                                       array( "label"=>"Grower Status",  "col"=>"status",          "w"=>150 ),
                                       array( "label"=>"Member Status",  "col"=>"M_expires",       "w"=>150 ),
                                     ),
                  "ListSize" => 10,
//                "ListSizePad" => 1,
                  "SearchToolCols"  => array( "Member Id"=>"fk_mbr_contacts",
                                              "Last Name"=>"M.lastname",
                                              "First Name"=>"M.firstname",
                                              "Company"=>"M.company",
                                              "Dept"=>"M.dept" ),
//                "fnListFilter"    => "Item_rowFilter",
                  "fnFormDraw"      => "GCGC_G_formDraw",
                  "fnListTranslate" => "GCGC_G_listTranslate",
                  "Controls_disallowNew" => 1,
                  "Controls_disallowDelete" => 1,
                ),
           "Parms" => array( "fnGetUserName" => "myGetUserName" )
           );

$kfuiDef_GCGC_Varieties =
    array( "A" =>
           array( "Label" => "Garlic Variety",
                  "ListCols" => array( array( "label"=>"Index Name",     "col"=>"index_name",      "w"=>150 ),
                                       array( "label"=>"Name",           "col"=>"name",            "w"=>150 ),
                                       array( "label"=>"Origin",         "col"=>"origin",          "w"=>150 ),
                                       array( "label"=>"# Available",    "col"=>"nAvailable",      "w"=>100 ),
                                       array( "label"=>"Status",         "col"=>"status",          "w"=>100 ),
                                     ),
                  "ListSize" => 15,
//                "ListSizePad" => 1,
//                "SearchToolCols"  => array( "Company"=>"R.name_en",
//                                            "Index Species"=>"pspecies", "Orig Species"=>"ospecies",
//                                            "Index Cultivar"=>"pname",   "Orig Cultivar"=>"oname" ),
//                "fnListFilter"    => "Item_rowFilter",
                  "fnFormDraw"      => "GCGC_V_formDraw",
                  "Controls_disallowDelete" => 1,
                ),
           "Parms" => array( "fnGetUserName" => "myGetUserName" )
           );


$kfuiDef_GCGC_Samples =
    array( "A" =>
           array( "Label" => "Garlic Sample",
                  "ListCols" => array( array( "label"=>"Grower",         "col"=>"G_fk_mbr_contacts", "w"=>150 ),
                                       array( "label"=>"Variety",        "col"=>"V_index_name",      "w"=>150 ),
                                       array( "label"=>"Start",          "col"=>"year_start",        "w"=>50 ),
                                       array( "label"=>"Verified",       "col"=>"year_last_verified","w"=>50 ),
                                       array( "label"=>"Status",         "col"=>"status",            "w"=>150 ),
                                     ),
                  "ListSize" => 15,
//                "ListSizePad" => 1,
//                "SearchToolCols"  => array( "Company"=>"R.name_en",
//                                            "Index Species"=>"pspecies", "Orig Species"=>"ospecies",
//                                            "Index Cultivar"=>"pname",   "Orig Cultivar"=>"oname" ),
//                "fnListFilter"    => "Item_rowFilter",
                  "fnFormDraw"      => "GCGC_S_formDraw",
                ),
           "Parms" => array( "fnGetUserName" => "myGetUserName" )
           );



include_once( STDINC."KeyFrame/KFRForm.php" );
include_once( SITEINC."mbrutil.php" );              // mbr_drawAddress


function myGetUserName( $uid )
/*****************************
 */
{
    global $sess;

    return( ($raUser = SEEDSessionAuth_Admin_GetUserInfoWithoutSession( $sess, $uid )) ? $raUser['realname'] : $uid );
}


function GCGC_G_listTranslate( $kfr )
/************************************
 */
{
    /* Instead of showing all of the mbr_contacts name columns (firstname,lastname,etc) we have one column (firstname)
       where we substitute the full contact name
     */
    $ra['M_firstname'] = mbr_makeName(array("firstname" =>$kfr->Value("M_firstname"),
                                            "lastname"  =>$kfr->Value("M_lastname"),
                                            "firstname2"=>$kfr->Value("M_firstname2"),
                                            "lastname2" =>$kfr->Value("M_lastname2"),
                                            "company"   =>$kfr->Value("M_company"),
                                            "dept"      =>$kfr->Value("M_dept") ) );

    /* Show the membership expiry in colour to indicate whether current
     */
    if( intval(substr($kfr->Value("M_expires"),0,4)) < date("Y") ) {
        $ra['M_expires'] = "<FONT color='red'>".$kfr->Value("M_expires")."</FONT>";
    }

    return( $ra );
}

function GCGC_G_formDraw( $kfr )
/*******************************
 */
{
    global $kfdb;

    echo "<P><FONT face='arial,helvetica' size=1><B>You are now \"under the hood\"</B>. Use this form to manage the nuts and bolts of the Grower records. The Console page does "
        ."a lot of things automatically, which you can <BR> break here if you don't know what you're doing. Use the "
        ."Console for regular administration, and only use this to see what's going on or if you need to fix something.</FONT></P>";

    // The New function of kfui doesn't do all the things we need, so only allow editing of existing rows.
    if( !$kfr->Key() )  return;

    echo "<TABLE border=0><TR><TD valign='top'>";

    echo "<TABLE border=0>";
    echo "<TR>".KFRForm_TextTD( $kfr, "Grower number", 'fk_mbr_contacts' )."</TR>";
    echo "<TR><TD>Status</TD>       <TD><SELECT name='status'>";
    foreach( array('NEW','ACTIVE','PENDING-ACTIVE','INACTIVE','STOPPED') as $k ) {
        echo KFRForm_Option( $kfr, 'status', $k, $k );
    }
    echo "</SELECT></TD></TR>";
    echo "<TR><TD>Notes</TD><TD><TEXTAREA name='notes' cols=60 rows=6>".$kfr->ValueEnt('notes')."</TEXTAREA></TD></TR>";
    echo "<TR><TD>Workflow</TD><TD><TEXTAREA name='workflow' cols=60 rows=6>".$kfr->ValueEnt('workflow')."</TEXTAREA></TD></TR>";
    echo "</TABLE>";

    echo "</TD><TD valign='top'>".mbr_drawAddress( $kfdb, $kfr->value('fk_mbr_contacts'), array("bEmail"=>1) )."</TD>"
        ."</TR></TABLE>";


    echo "<INPUT type=submit value='Save'>";
}


function GCGC_V_formDraw( $kfr )
/*******************************
 */
{
    echo "<TABLE border=0>";
    echo "<TR>".KFRForm_TextTD( $kfr, "Name", 'name' )."</TR>";
    echo "<TR>".KFRForm_TextTD( $kfr, "Origin", 'origin' )."</TR>";
    echo "<TR>".KFRForm_TextTD( $kfr, "Index Name", 'index_name' )."</TR>";
    echo "<TR><TD>Status</TD>       <TD><SELECT name='status'>";
    foreach( array('NEW','ACTIVE','INACTIVE','DROPPED') as $k ) {
        echo KFRForm_Option( $kfr, 'status', $k, $k );
    }
    echo "</SELECT></TD></TR>";
    echo "<TR>".KFRForm_TextTD( $kfr, "# Available", 'nAvailable' )."</TR>";
    echo "<TR><TD>Notes</TD><TD><TEXTAREA name='notes' cols=50 rows=6>".$kfr->ValueEnt('notes')."</TEXTAREA></TD></TR>";
    echo "<TR><TD>Workflow</TD><TD><TEXTAREA name='workflow' cols=50 rows=6>".$kfr->ValueEnt('workflow')."</TEXTAREA></TD></TR>";
    echo "</TABLE>";

    echo "<INPUT type=submit value='Save'>";
}


function GCGC_S_formDraw( $kfr )
/*******************************
 */
{
    global $kfdb, $kfrelDef_GCGC_GrowersXContacts_withAliasG;

    echo "<P><FONT face='arial,helvetica' size=1><B>You are now \"under the hood\"</B>. Use this form to manage the nuts and bolts of the Grower records. The Console page does "
        ."a lot of things automatically, which you can <BR> break here if you don't know what you're doing. Use the "
        ."Console for regular administration, and only use this to see what's going on or if you need to fix something.</FONT></P>";

    // The New function of kfui doesn't do all the things we need, so only allow editing of existing rows.
    if( !$kfr->Key() )  return;

    echo "<TABLE border=0>";
    echo "<TR><TD>Grower</TD><TD><SELECT name='fk_gcgc_growers'>";

    $kfrelG = new KeyFrameRelation( $kfdb, $kfrelDef_GCGC_GrowersXContacts_withAliasG, 0 );
    if( $kfrG = $kfrelG->CreateRecordCursor( /* DON'T FILTER BY GROWER STATUS "(G.status='NEW' OR G.status='ACTIVE')" */ ) ) {
        while( $kfrG->CursorFetch() ) {
            $name = mbr_makeName(array("firstname" =>$kfrG->Value("M_firstname"),
                                       "lastname"  =>$kfrG->Value("M_lastname"),
                                       "firstname2"=>$kfrG->Value("M_firstname2"),
                                       "lastname2" =>$kfrG->Value("M_lastname2"),
                                       "company"   =>$kfrG->Value("M_company"),
                                       "dept"      =>$kfrG->Value("M_dept") ) );
            echo KFRForm_Option( $kfr, 'fk_gcgc_growers', $kfrG->Key(), $name );
        }
        $kfrG->CursorClose();
    }
    echo "</SELECT></TD></TR>";

    echo "<TR><TD>Variety</TD><TD><SELECT name='fk_gcgc_varieties'>";
    if( $dbc = $kfdb->KFDB_CursorOpen( "SELECT _key,index_name FROM gcgc_varieties WHERE _status=0 AND (status='NEW' OR status='ACTIVE') ORDER by index_name" ) );
    while( $ra = $kfdb->KFDB_CursorFetch( $dbc ) ) {
        echo KFRForm_Option( $kfr, 'fk_gcgc_varieties', $ra['_key'], $ra['index_name'] );
    }
    $kfdb->KFDB_CursorClose( $dbc );
    echo "</SELECT></TD></TR>";

    echo "<TR>".KFRForm_TextTD( $kfr, "Year started", 'year_start', 10 )."</TR>";
    echo "<TR>".KFRForm_TextTD( $kfr, "Year last verified", 'year_last_verified', 10 )."</TR>";

    echo "<TR><TD>Status</TD>       <TD><SELECT name='status'>";
    foreach( array('NEW','ACTIVE','INACTIVE','DROPPED') as $k ) {
        echo KFRForm_Option( $kfr, 'status', $k, $k );
    }
    echo "</SELECT></TD></TR>";
    echo "<TR><TD>Notes</TD><TD><TEXTAREA name='notes' cols=50 rows=6>".$kfr->ValueEnt('notes')."</TEXTAREA></TD></TR>";
    echo "<TR><TD>Workflow</TD><TD><TEXTAREA name='workflow' cols=50 rows=6>".$kfr->ValueEnt('workflow')."</TEXTAREA></TD></TR>";
    echo "</TABLE>";

    echo "<INPUT type=submit value='Save'>";
}

?>
