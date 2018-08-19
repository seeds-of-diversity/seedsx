<?


$EV_Item_RecordDef = array( 'tablename' => 'event_items',
                            'fields' => array( array("name"=>"page_code",  "type"=>"I" ),
                                               array("name"=>"title",      "type"=>"S" ),
                                               array("name"=>"title_fr",   "type"=>"S" ),
                                               array("name"=>"city",       "type"=>"S" ),
                                               array("name"=>"province",   "type"=>"S" ),
                                               array("name"=>"month",      "type"=>"I", "default"=> 1),
                                               array("name"=>"day",        "type"=>"I", "default"=> 1),
                                               array("name"=>"date_alt",   "type"=>"S" ),
                                               array("name"=>"date_alt_fr","type"=>"S" ),
                                               array("name"=>"time",       "type"=>"S" ),
                                               array("name"=>"details",    "type"=>"S" ),
                                               array("name"=>"details_fr", "type"=>"S" ) ) );


$EV_Item_FrameDef = array( "RelationType" => "ChildSimple",
                           "RelationFKName" => "page_code",
                           "RelationFKValue" => 0,                  // client must set this before calling dbPhrameUI()
                           "Label" => "Event",                      // client must set this
                           "RecordDef" => $EV_Item_RecordDef,
                           "ListCols" => array( array( "label"=>"City",           "col"=>"city",      "w"=>150),
                                                array( "label"=>"Title",          "col"=>"title",     "w"=>200),  // kluge: must be second column
                                                array( "label"=>"Province",       "col"=>"province",  "w"=>50 ),
                                                array( "label"=>"Month",          "col"=>"month",     "w"=>50 ),
                                                array( "label"=>"Day",            "col"=>"day",       "w"=>50 ),
                                                array( "label"=>"Alt Date",       "col"=>"date_alt",  "w"=>100),
                                                array( "label"=>"Time",           "col"=>"time",      "w"=>100),
                                               ),
                           "fnHeader" => "EV_Item_header",
                           "fnRowFilter" => "EV_Item_rowFilter",
                           "fnFormDraw" => "EV_Item_formDraw" );




?>
