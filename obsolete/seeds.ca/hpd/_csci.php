<?

/*

Upgrade Plan:
    Convert rl_companies to KeyFrame
    Convert cat_catalog->csci_catalog, linked to rl_companies
    Convert cat_item->csci_item, linked to csci_catalog


CREATE TABLE cat_catalog (

        _key        INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT,
        _created    DATETIME,
        _created_by INTEGER,
        _updated    DATETIME,
        _updated_by INTEGER,
        _status     INTEGER DEFAULT 0,

    cat_company_id  INTEGER NOT NULL,       # fk to rl_companies.rl_cmp_id --- should be fk_rl_companies -> rl_companies._key
    issue           VARCHAR(200) NOT NULL,  # name of this issue (e.g. if there is a spring and fall issue in same year)
    year            INTEGER NOT NULL

#todo
#   comment         VARCHAR(200)        # internal
);

CREATE TABLE cat_item (

        _key        INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT,
        _created    DATETIME,
        _created_by INTEGER,
        _updated    DATETIME,
        _updated_by INTEGER,
        _status     INTEGER DEFAULT 0,

    cat_catalog_id  INTEGER NOT NULL,   # fk to cat_catalog._key --- should be renamed to fk_cat_catalog/fk_csci_catalog
    pspecies        VARCHAR(200),
    pspecies_ex     VARCHAR(200),
    ospecies        VARCHAR(200),
    pname           VARCHAR(200),
    oname           VARCHAR(200),
    found           INTEGER,            # helper during editing, used as desired - preset to 0, checkboxes set to other values
    verified        INTEGER DEFAULT 0   # proofreader sets this

#todo
#   comment         VARCHAR(200)        # internal
);
*/


$CSCI_Company_Recorddef = array( "tablename" => "cat_company",
                                 "fields" => array( array("name"=>"name",            "type"=>"S", "default"=>""),
                                                    array("name"=>"curr_catalog_id", "type"=>"I", "default"=>0 ) ) );

$CSCI_Catalog_Recorddef = array( "tablename" => "cat_catalog",
                                 "fields" => array( array("name"=>"cat_company_id",  "type"=>"F", "fktable"=>"cat_company"),
                                                    array("name"=>"issue",           "type"=>"S", "default"=>"" ),
                                                    array("name"=>"year",            "type"=>"I", "default"=>0 ) ) );

$CSCI_Item_Recorddef   = array( "tablename" => "cat_item",
                                "RelationType" => "Child",
                                "RelationFKTable" => "cat_catalog",
                                "RelationFKName" => "cat_catalog_id",
                                "fields" => array( array("name"=>"cat_catalog_id",  "type"=>"F"),
                                                   array("name"=>"ospecies",        "type"=>"S", "default"=>"" ),
                                                   array("name"=>"oname",           "type"=>"S", "default"=>"" ),
                                                   array("name"=>"pspecies",        "type"=>"S", "default"=>"" ),
                                                   array("name"=>"pspecies_ex",     "type"=>"S", "default"=>"" ),
                                                   array("name"=>"pname",           "type"=>"S", "default"=>"" ) ),
                                "roFields" => array( array( "name"=>"cmp_name", "type"=>"FI", "fkcol"=>"cat_company_id", "default"=>0 ) )
                              );


$CSCI_Item_Simple_Recorddef =
        array( "tablename" => "cat_item",
               "RelationType" => "Simple",
               "fields" => array( array("name"=>"cat_catalog_id",  "type"=>"F"),
                                  array("name"=>"ospecies",        "type"=>"S"),
                                  array("name"=>"oname",           "type"=>"S"),
                                  array("name"=>"pspecies",        "type"=>"S"),
                                  array("name"=>"pspecies_ex",     "type"=>"S"),
                                  array("name"=>"pname",           "type"=>"S") )
             );


$CSCI_Company_Framedef = array( "Label" => "Company",
                                "RecordDef" => $CSCI_Company_Recorddef,
                                "ListCols" => array( array( "label"=>"Name", "col"=>"name", "w"=>150 ) ),
                                "fnFormDraw" => "CSCI_Company_formDraw" );

$CSCI_Catalog_Framedef = array( "Label" => "Catalogue",
                                "RecordDef" => $CSCI_Catalog_Recorddef,
                                "ListCols" => array( array( "label"=>"Issue", "col"=>"issue", "w"=>150 ),
                                                     array( "label"=>"Year",  "col"=>"year", "w"=>150 ) ),
                                "fnFormDraw" => "CSCI_Catalog_formDraw" );

$CSCI_Item_Framedef    = array( "RelationType" => "Child",
                                "RelationFKParentTable" => "cat_catalog",
                                "RelationFKName" => "cat_catalog_id",
                                "Label" => "Catalogue Item",
                                "RecordDef" => $CSCI_Item_Recorddef,
                                "ListCols" => array( array( "label"=>"Catalogue",      "col"=>"cat_catalog_id", "w"=>50 ),
                                                     array( "label"=>"Parent-Company", "col"=>"cmp_name", "w"=>150 ),
                                                     array( "label"=>"Species",        "col"=>"ospecies", "w"=>150 ),
                                                     array( "label"=>"Cultivar",       "col"=>"oname", "w"=>150 ),
                                                     array( "label"=>"Index Species",  "col"=>"pspecies", "w"=>150 ),
                                                     array( "label"=>"Index Species Ex","col"=>"pspecies_ex", "w"=>80 ),
                                                     array( "label"=>"Index Cultivar", "col"=>"pname", "w"=>150 ) ),
                                "ListSize" => 12,
                                "fnHeader" => "CSCI_Item_header",
                                "fnRowFilter" => "CSCI_Item_rowFilter",
                                "fnFormDraw" => "CSCI_Item_formDraw" );


function CSCI_Company_formDraw( $dPRec )
{
    echo "Name<BR><INPUT type=text name='name' value='".$dPRec->dPR_value('name')."'>";
}


function CSCI_Catalog_formDraw( $dPRec )
{
    echo "Issue<BR><INPUT type=text name='issue' value='".$dPRec->dPR_value('issue')."'><BR>";
    echo "Year<BR><INPUT type=text name='year' value='".$dPRec->dPR_value('year')."'>";
}

function CSCI_Item_formDraw( $dPRec )
{
    echo "<TABLE>";
    echo "<TR><TD>Species</TD><TD>".dbPhrameUI_formINPUT( $dPRec, "", "ospecies", 60 )."</TD></TR>";
    echo "<TR><TD>Cultivar</TD><TD>".dbPhrameUI_formINPUT( $dPRec, "", "oname", 60 )."</TD></TR>";
    echo "<TR><TD>Index Species Ex</TD><TD>".dbPhrameUI_formINPUT( $dPRec, "", "pspecies_ex", 60 )."</TD></TR>";
    echo "<TR><TD>Index Species</TD><TD>".dbPhrameUI_formINPUT( $dPRec, "", "pspecies", 60 )."</TD></TR>";
    echo "<TR><TD>Index Cultivar</TD><TD>".dbPhrameUI_formINPUT( $dPRec, "", "pname", 60 )."</TD></TR>";
    echo "</TABLE>";
}


function CSCI_Item_header($dpui)
/*******************************
 */
{
    $phflt = BXStd_SafeGPCGetStrPlain('phflt');

    echo "<TABLE width='100%'><TR><TD><H2>Catalogue Items</H2></TD><TD>&nbsp;</TD>";
    echo "<TD align=right><FORM action='{$_SERVER['PHP_SELF']}' target='_top'>";
    echo dbPhrameUI_User_HiddenFormParms( $dpui, array("keepSel"=>false),array("phflt","phfltsrch") );

    echo dbPhrameUI_headerSearch( $dpui, "phfltsrch" );

    echo "<SELECT name=phflt><OPTION value=''".(empty($phflt) ? " SELECTED" : "")."> -- All Species -- </OPTION>";
    if( $dbc = db_open( "SELECT pspecies FROM cat_item GROUP BY pspecies ORDER BY pspecies" ) ) {
        while( $ra = db_fetch( $dbc ) ) {
            echo "<OPTION value='{$ra[0]}'".(($phflt==$ra[0]) ? " SELECTED" : "").">{$ra[0]}</OPTION>";
        }
    }
    echo "</SELECT><INPUT type=submit></FORM></TD></TR></TABLE>";
}


function CSCI_Item_rowFilter()
{
    $phflt = BXStd_SafeGPCGetStr('phflt');
    $phfltsrch = BXStd_SafeGPCGetStr('phfltsrch');


    $cond = !empty($phflt['plain']) ? "(pspecies = '{$phflt['db']}')" : "1=1";
    if( !empty($phfltsrch['plain']) ) {
        $cond .= " AND (ospecies like '{$phfltsrch['db']}' OR ";
        $cond .=      " oname like '{$phfltsrch['db']}' OR ";
        $cond .=      " pspecies like '{$phfltsrch['db']}' OR ";
        $cond .=      " pspecies_ex like '{$phfltsrch['db']}' OR ";
        $cond .=      " pname like '{$phfltsrch['db']}')";
    }
    return( $cond );
}











?>
