<?

// Canadian Seed Library
// Canadian Community Seed Bank Network

/*

CREATE TABLE ccsl_accession (

        _key        INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT,
        _created    DATETIME,
        _created_by INTEGER,
        _updated    DATETIME,
        _updated_by INTEGER,
        _status     INTEGER DEFAULT 0,

    pspecies        VARCHAR(200),
    pname           VARCHAR(200),
    oname           VARCHAR(200),
    source          VARCHAR(200),
    batch           VARCHAR(200),
    parent_str      VARCHAR(200),
    parent_acc      INTEGER
);

CREATE TABLE ccsl_accession_transaction (

        _key        INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT,
        _created    DATETIME,
        _created_by INTEGER,
        _updated    DATETIME,
        _updated_by INTEGER,
        _status     INTEGER DEFAULT 0,

    fk_ccsl_accession INTEGER NOT NULL,
    date_str        VARCHAR(200),       # typically a date, but can be anything, preferably chrono-sortable
    amount_grams    DECIMAL(7,2),
    note            TEXT
);

*/

$kfrdef_CCSL_Acc =
    array( "Tables"=>array( array( "Table" => 'ccsl_accession',
                                   "Fields" => array( array("col"=>"pspecies",   "type"=>"S"),
                                                      array("col"=>"pname",      "type"=>"S"),
                                                      array("col"=>"oname",      "type"=>"S"),
                                                      array("col"=>"source",     "type"=>"S"),
                                                      array("col"=>"batch",      "type"=>"S"),
                                                      array("col"=>"parent_str", "type"=>"S"),
                                                      array("col"=>"parent_acc", "type"=>"K") ) ) ) );

$kfrdef_CCSL_Acc_XAction =
    array( "Tables"=>array( array( "Table" => 'ccsl_accession_transaction',
                                   "Fields" => array( array("col"=>"fk_ccsl_accession", "type"=>"K"),
                                                      array("col"=>"date_str",          "type"=>"S"),
                                                      array("col"=>"amount_grams",      "type"=>"F"),
                                                      array("col"=>"note",              "type"=>"S") ) ),

                            array( "Table" => 'ccsl_accession',
                                   "Type" => "Parent",
                                   "Alias" => "ACC",
                                   "Fields" => array( array("col"=>"pspecies",   "type"=>"S"),
                                                      array("col"=>"pname",      "type"=>"S"),
                                                      array("col"=>"oname",      "type"=>"S"),
                                                      array("col"=>"source",     "type"=>"S"),
                                                      array("col"=>"batch",      "type"=>"S"),
                                                      array("col"=>"parent_str", "type"=>"S"),
                                                      array("col"=>"parent_acc", "type"=>"K") ) ) ) );


$kfuiDef_CCSL_Acc =
    array( "A" =>
           array( "Label" => "Seed Accession",
                  "ListCols" => array( array( "label"=>"Species",        "col"=>"pspecies",     "w"=>150),
                                       array( "label"=>"Cultivar",       "col"=>"pname",        "w"=>150),
                                       array( "label"=>"Source",         "col"=>"source",       "w"=>100),
                                       array( "label"=>"Batch",          "col"=>"batch",        "w"=>50 ),
                                       array( "label"=>"Parent",         "col"=>"parent_str",   "w"=>50 ),
                                       array( "label"=>"Parent_Acc",     "col"=>"parent_acc",   "w"=>50 ),
                                     ),
                  "ListSize" => 10,
                  "ListSizePad" => 1,
//                "fnHeader"        => "Item_header",
//                "fnListFilter"    => "Item_rowFilter",
//                "fnFormDraw"      => "ccsl_formDraw",
                ),


           "B" =>
           array( "Label" => "Seed Transaction",
                  "ListCols" => array( array( "label"=>"Species",        "col"=>"ACC_pspecies", "w"=>150),
                                       array( "label"=>"Cultivar",       "col"=>"ACC_pname",    "w"=>150),
                                       array( "label"=>"Source",         "col"=>"ACC_source",   "w"=>100),
                                       array( "label"=>"Batch",          "col"=>"ACC_batch",    "w"=>50 ),
                                       array( "label"=>"Date",           "col"=>"date_str",     "w"=>50 ),
                                       array( "label"=>"Grams",          "col"=>"amount_grams", "w"=>50 ),
                                     ),
                  "ListSize" => 10,
                  "ListSizePad" => 1,
//                "fnHeader"        => "Item_header",
//                "fnListFilter"    => "Item_rowFilter",
//                "fnFormDraw"      => "ccsl_formDraw",
                  "fkDefaults"      => array( "ccsl_accession" => "A" )
                ) );



function ccsl_formDraw( $kfr )
{
    echo "<TABLE cellpadding=5 width='50%' align='center'>";

    // type=EV: title is the title of the event, city is the location
    // type=SS: city/prov is the title of the event, title is repurposed as the location

    if( $kfr->value('Page_type')=="EV" ) {
        draw_field( "title", "Title", $kfr, $kfr->value('Page_bEN'), $kfr->value('Page_bFR'), 50 );
    }

    // both types have city and province here
    echo "<TR><TD align='left'>City:</TD>      <TD align='left'><INPUT TYPE=TEXT NAME=city     VALUE='".$kfr->kfr_valueEnt('city')."'     size=20>";
    echo "<SELECT NAME=province>"; echo option_province( $kfr->value('province') ); echo "</SELECT></TD></TR>";

    if( $kfr['Page_type']=="SS" ) {
        draw_field( "title", "Location", $kfr, $kfr->value('Page_bEN'), $kfr->value('Page_bFR'), 50 );
    }

    echo "<TR><TD align='left'>Date:</TD>      <TD align='left'><SELECT NAME=month>"; echo option_months( $kfr->value('month') ); echo "</SELECT>";
    echo "<SELECT NAME=day>"; option_days( $kfr->value('day') ); echo "</SELECT>, ".$kfr->value('Page_year')."</TD></TR>";
    draw_field( "date_alt", "Alternate Date&nbsp;Text", $kfr, $kfr->value('Page_bEN'), $kfr->value('Page_bFR'), 50 );
    echo "<TR><TD align='left'>Time:</TD>      <TD align='left'><INPUT TYPE=TEXT NAME=time     VALUE='".$kfr->kfr_valueEnt('time')."' size=70></TD></TR>";

    echo "<TR><TD align='left' valign=top>Details:</TD><TD align='left'>";
    echo "<TABLE cellpadding=5>";
    if( $kfr->value('Page_bEN') )  echo "<TR><TD bgcolor='".CLR_BG_editEN."'>(English) <TEXTAREA NAME=details COLS=52 ROWS=5 WRAP=SOFT>".$kfr->kfr_valueEnt('details')."</TEXTAREA></TD></TR>";
    if( $kfr->value('Page_bFR') )  echo "<TR><TD bgcolor='".CLR_BG_editFR."'>(Fran&ccedil;ais) <TEXTAREA NAME=details_fr COLS=52 ROWS=5 WRAP=SOFT>".$kfr->kfr_valueEnt('details_fr')."</TEXTAREA></TD></TR>";
    echo "</TABLE>";
    if( !$kfr->value('Page_bEN') )  echo '<INPUT TYPE=HIDDEN NAME=details VALUE="'. $kfr->kfr_valueEnt('details') .'">';
    if( !$kfr->value('Page_bFR') )  echo '<INPUT TYPE=HIDDEN NAME=details_fr VALUE="'. $kfr->kfr_valueEnt('details_fr') .'">';
    echo "</TD></TR>\n";
//  echo "<TR><TD align=center colspan=2><INPUT TYPE=SUBMIT VALUE='".(($i=="new") ? "Add":"Update")."'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
//  echo "<A HREF='page.php?p=".$p."&".$la->login_auth_get_urlparms()."'>Cancel</A></TD></TR>\n";
    echo "</TABLE>";
    echo "<INPUT type=submit>";
}


?>
