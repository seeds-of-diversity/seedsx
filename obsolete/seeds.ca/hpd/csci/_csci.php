<?
/* Canadian Seed Catalogue Inventory
 *
 *  csci_company        = one row for each company
 *  csci_catalog        = one row for each catalog published by each company
 *  csci_item           = one row for each item in each catalog


CREATE TABLE csci_company (

        _key        INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT,
        _created    DATETIME,
        _created_by INTEGER,
        _updated    DATETIME,
        _updated_by INTEGER,
        _status     INTEGER DEFAULT 0,


    name_en     VARCHAR(200),
    name_fr     VARCHAR(200),
    addr_en     VARCHAR(200),
    addr_fr     VARCHAR(200),
    city        VARCHAR(200),
    prov        VARCHAR(200),
    country     VARCHAR(200),
    postcode    VARCHAR(200),
    phone       VARCHAR(200),
    fax         VARCHAR(200),
    web         VARCHAR(200),
    web_alt     VARCHAR(200),
    email       VARCHAR(200),
    email_alt   VARCHAR(200),
    desc_en     TEXT,
    desc_fr     TEXT,
    cat_cost    INTEGER,
    year_est    INTEGER,

    -- internal
    comments    VARCHAR(200),
    bRLShow     INTEGER DEFAULT 0,

    -- when the "This is Correct" checkbox is checked, tsVerified=NOW(),bNeedVerify=0
    -- when any data is changed by non-approvers, bNeedProof=1
    tsVerified  DATETIME,
    bNeedVerify INTEGER DEFAULT 1,
    bNeedProof  INTEGER DEFAULT 1,
    bNeedXlat   INTEGER DEFAULT 1
);


CREATE TABLE csci_seeds (

        _key        INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT,
        _created    DATETIME,
        _created_by INTEGER,
        _updated    DATETIME,
        _updated_by INTEGER,
        _status     INTEGER DEFAULT 0,

    company_name    VARCHAR(200),
    psp             VARCHAR(200),
    icv             VARCHAR(200),

    index (psp),
    index (icv)
);


*/


$kfrelDef_CSCI_Company =
    array( "Tables"=>array( array( "Table" => 'csci_company',
                                   "Fields" => array( array("col"=>"name_en",       "type"=>"S"),
                                                      array("col"=>"name_fr",       "type"=>"S"),
                                                      array("col"=>"addr_en",       "type"=>"S"),
                                                      array("col"=>"addr_fr",       "type"=>"S"),
                                                      array("col"=>"city",          "type"=>"S"),
                                                      array("col"=>"prov",          "type"=>"S"),
                                                      array("col"=>"country",       "type"=>"S", "default"=>"Canada"),
                                                      array("col"=>"postcode",      "type"=>"S"),
                                                      array("col"=>"phone",         "type"=>"S"),
                                                      array("col"=>"fax",           "type"=>"S"),
                                                      array("col"=>"web",           "type"=>"S"),
                                                      array("col"=>"web_alt",       "type"=>"S"),
                                                      array("col"=>"email",         "type"=>"S"),
                                                      array("col"=>"email_alt",     "type"=>"S"),
                                                      array("col"=>"desc_en",       "type"=>"S"),
                                                      array("col"=>"desc_fr",       "type"=>"S"),
                                                      array("col"=>"cat_cost",      "type"=>"I", "default"=> -1),
                                                      array("col"=>"year_est",      "type"=>"I"),
                                                      array("col"=>"comments",      "type"=>"S"),
                                                      array("col"=>"bRLShow",       "type"=>"I"),
                                                      array("col"=>"tsVerified",    "type"=>"S"),
                                                      array("col"=>"bNeedVerify",   "type"=>"I"),
                                                      array("col"=>"bNeedProof",    "type"=>"I"),
                                                      array("col"=>"bNeedXlat",     "type"=>"I") ) ) ) );


?>
