<?

include_once( SEEDCOMMON."siteKFDB.php" );
include_once( STDINC."KeyFrame/KFRecord.php" );
include_once( STDINC."KeyFrame/KFRelation.php" );
include_once( STDINC."SEEDDate.php" );
include_once( STDINC."SEEDWiki.php" );

/* event pages : simple
 */
$kfrdef_EVPages =
    array( "Tables"=>array( array( "Table" => 'ev_pages',
                                   "Fields" => array( array("col"=>"name",    "type"=>"S"),
                                                      array("col"=>"name_fr", "type"=>"S"),
                                                      array("col"=>"type",    "type"=>"S"),
                                                      array("col"=>"year",    "type"=>"I"),
                                                      array("col"=>"bEN",     "type"=>"I"),
                                                      array("col"=>"bFR",     "type"=>"I") ) ) ) );

/* event items : child simple
 *
 * KF does not join tables; the fk is forced as a simple integer
 */
$kfrdef_EVItems =
    array( "Tables"=>array( array( "Table" => 'ev_items',
                                   "Type"  => 'Base',
                                   "Fields" => array( array("col"=>"fk_ev_pages", "type"=>"I"),
                                                      array("col"=>"title",       "type"=>"S"),
                                                      array("col"=>"title_fr",    "type"=>"S"),
                                                      array("col"=>"city",        "type"=>"S"),
                                                      array("col"=>"province",    "type"=>"S"),
                                                      array("col"=>"month",       "type"=>"I", "default"=> 1),
                                                      array("col"=>"day",         "type"=>"I", "default"=> 1),
                                                      array("col"=>"date_alt",    "type"=>"S"),
                                                      array("col"=>"date_alt_fr", "type"=>"S"),
                                                      array("col"=>"time",        "type"=>"S"),
                                                      array("col"=>"details",     "type"=>"S"),
                                                      array("col"=>"details_fr",  "type"=>"S") ) ),
                            array( "Table" => "ev_pages",
                                   "Alias" => "Page",
                                   "Type"  => "Parent",
                                   "Fields" => array( array("col"=>"name",    "type"=>"S"),
                                                      array("col"=>"name_fr", "type"=>"S"),
                                                      array("col"=>"type",    "type"=>"S"),
                                                      array("col"=>"year",    "type"=>"I"),
                                                      array("col"=>"bEN",     "type"=>"I"),
                                                      array("col"=>"bFR",     "type"=>"I") ) ) ) );



function ev_style()
/******************
 */
{
    echo "<STYLE type='text/css'>";
    echo ".EVPageList { font-size: large; }";
    echo "</STYLE>";
}


function event_page_get( $p )
/****************************
    Return an event page row
 */
{
    return( db_query( "SELECT * FROM event_pages WHERE page_code=". $p ) );
}


function event_item_get( $i )
/****************************
    Return an event item row
 */
{
    return( db_query( "SELECT * FROM event_items WHERE _rowid=". $i ) );
}


function event_page_get_from_item( $i )
/**************************************
    Return the event page row of the page that contains the given item
 */
{
    $pagecode = db_query1( "SELECT page_code FROM event_items WHERE _rowid=". $i );
    return( event_page_get( $pagecode ) );
}


function event_item_show_from_rec( $rec )           // shows all languages
/****************************************
 */
{
    // event_item_show should take a $rec instead of $ra
    $ra = event_item_get( $rec->rowid );
    $raPage = event_page_get_from_item( $rec->rowid );
    event_item_show( $ra, $raPage, "*", 0 );
}


function _getValue( $kfrItem, $field, $lang )
/********************************************
    Get the English or French value, or the other one if empty
 */
{
    $e = $kfrItem->value($field);
    $f = $kfrItem->value($field."_fr");
    return((($lang=="EN" && !empty($e)) || ($lang=="FR" && empty($f))) ? $e : $f);
}


function _evitem_text_draw( $kfrItem, $lang, $bBothLang, $bPrn )
/***************************************************************
    Draw the text of an event item, in either english or french
 */
{
    if( $bBothLang )  echo "<TR><TD bgcolor=".($lang=="EN" ? CLR_BG_editEN : CLR_BG_editFR).">";

    $t  = _getValue( $kfrItem, "title", $lang );
    $c  = $kfrItem->value("city") .", ". $kfrItem->value("province");
    $dx = _getValue( $kfrItem, "date_alt", $lang );

    $title    = ($kfrItem->value("Page_type")=="SS" ? $c : $t);
    $location = ($kfrItem->value("Page_type")=="SS" ? $t : $c);
    $date     = !empty($dx) ? $dx : SEEDDateStr( mktime(0,0,0,$kfrItem->value("month"),$kfrItem->value("day"),$kfrItem->value("Page_year")), $lang );

    $details = _getValue( $kfrItem, "details", $lang );

    $oWiki = new SEEDWikiParser();  // this would be better as a global since it's stateless

    if( $bPrn ) {
        // only used in unilingual mode

        // this is not the best format for EV-type events, but it works for SS

        echo "<DIV class='evPRNTitle'>$title</DIV>";
        echo "</TD><TD valign=top>";
        echo "<P><B>";
        echo $date . SEEDStd_StrNBSP("      ") . $kfrItem->value("time")."</B>";
        if( !empty($location) )  echo "<BR>".$location;
        echo "</P>";
        echo "<P>";
        if( $kfrItem->value("Page_year") < 2008 ) {
            // prior to 2008 we used plaintext, now use Wiki
            echo SEEDStd_StrBR($details);
        } else {
            echo ($oWiki ? $oWiki->Translate($details) : $details);
        }
        echo "</P>";
        echo "</TD></TR>\n<TR><TD align=left valign=top>";
    } else {
        echo "<H2><FONT FACE='Arial,Helvetica,Sans Serif'>$title</FONT></H2>";
        echo "<BLOCKQUOTE>";
        echo "<P><B>";
        if( !empty($location) )  echo $location."<BR>";
        echo $date ."<BR>".$kfrItem->value("time")."</B></P>";
        echo "<P>";
        if( $kfrItem->value("Page_year") < 2008 ) {
            // prior to 2008 we used plaintext, now use Wiki
            echo SEEDStd_StrBR($details);
        } else {
            echo ($oWiki ? $oWiki->Translate($details) : $details);
        }
        echo "</P></BLOCKQUOTE>\n";
    }

    if( $bBothLang )  echo "</TD></TR>";
}


function event_item_show_kfr( $kfrItem, $lang, $bEdit = 0, $auth_urlparms = "", $bPrn = 0 )
/******************************************************************************************
    Draw an event item.
    $lang=="EN","FR" - show the given language
    $lang=="*" - show all available languages with backgrounds
    $bEdit - draw edit controls
    $auth_urlparms - only needed for edit controls
 */
{
    echo "<A name='i".$kfrItem->kfr_Rowid()."'>";

    // type==EV: title is the title, city/prov is the location
    // type==SS: title is repurposed to contain the location, city/prov is used as the title

    if( $bEdit ) {
        echo "<HR>";
        echo "<P class='edit_ctrl_link'>";
        echo "<A HREF='".EV_ROOT."admin/edit.php?i=".$kfrItem->kfr_Rowid() . $auth_urlparms ."'>[Edit]</A>&nbsp;";
        echo "<A HREF='".EV_ROOT."admin/delete.php?i=".$kfrItem->kfr_Rowid() . $auth_urlparms ."'>[Delete]</A></P>";
    }

    if( $lang=="*" )  echo "<TABLE>";

    if( $lang=="EN" || ($lang=="*" && $kfrItem->value('Page_bEN') ) ) { _evitem_text_draw( $kfrItem, "EN", ($lang=="*"), $bPrn ); }
    if( $lang=="FR" || ($lang=="*" && $kfrItem->value('Page_bFR') ) ) { _evitem_text_draw( $kfrItem, "FR", ($lang=="*"), $bPrn ); }

    if( $lang=="*" )  echo "</TABLE>";
}


function event_item_show( $ra, $raPage, $lang, $bEdit = 0, $auth_urlparms = "" )
/*******************************************************************************
    Draw an event item.
    $ra is an event_items row.  $raPage is its event_pages row.
    $lang=="EN","FR" - show the given language
    $lang=="*" - show all available languages with backgrounds
    $bEdit - draw edit controls
    $auth_urlparms - only needed for edit controls
 */
{
    echo "<A name='i".$ra["_key"]."'>";

    // type==EV: title is the title, city/prov is the location
    // type==SS: title is repurposed to contain the location, city/prov is used as the title

    if( $bEdit ) {
        echo "<HR>";
        echo "<P><A HREF='".EV_ROOT."admin/edit.php?i=". $ra["_key"] . $auth_urlparms ."'><FONT COLOR='red'>[Edit]</FONT></A>&nbsp;";
        echo "<A HREF='".EV_ROOT."admin/delete.php?i=". $ra["_key"] . $auth_urlparms ."'><FONT COLOR='red'>[Delete]</FONT></A></P>";
    }

    if( $lang=="*" )  echo "<TABLE>";

    if( $lang=="EN" || ($lang=="*" && $raPage['bEN']) ) {
        /* Show English
         */
        if( $lang=="*" )  echo "<TR><TD bgcolor=".CLR_BG_editEN.">";

        $title    = ($raPage["type"]=="SS" ? ($ra["city"] .", ". $ra["province"]) : $ra["title"]);
        $location = ($raPage["type"]=="SS" ? $ra["title"] : ($ra["city"] ." ". $ra["province"]));
        $date     = $ra["date_alt"] ? $ra["date_alt"] : SEEDDateStr( mktime(0,0,0,$ra["month"],$ra["day"],$raPage["year"]), "EN" );

        echo "<H2><FONT FACE='Arial,Helvetica,Sans Serif'>". $title ."</FONT></H2>";
        echo "<BLOCKQUOTE>";
        echo "<P><B>". $location ."<BR>";
        echo $date ." ". ($ra["time"]!="" ? "<BR>". $ra["time"] : "") . "</B></P>";
        echo "<P>". SEEDStd_StrBR($ra["details"]) ."</P></BLOCKQUOTE>";

        if( $lang=="*" )  echo "</TD></TR>";
    }

    if( $lang=="FR" || ($lang=="*" && $raPage['bFR']) ) {
        /* Show French
         */
        if( $lang=="*" )  echo "<TR><TD bgcolor=".CLR_BG_editFR.">";

        $title    = ($raPage["type"]=="SS" ? ($ra["city"] .", ". $ra["province"]) : $ra["title_fr"]);
        $location = ($raPage["type"]=="SS" ? $ra["title_fr"] : ($ra["city"] ." ". $ra["province"]));
        $date     = $ra["date_alt_fr"] ? $ra["date_alt_fr"] : SEEDDateStr( mktime(0,0,0,$ra["month"],$ra["day"],$raPage["year"]), "FR" );

        echo "<H2><FONT FACE='Arial,Helvetica,Sans Serif'>". $title ."</FONT></H2>";
        echo "<BLOCKQUOTE>";
        echo "<P><B>". $location ."<BR>";
        echo $date ." ". ($ra["time"]!="" ? "<BR>". $ra["time"] : "") . "</B></P>";
        echo "<P>". SEEDStd_StrBR($ra["details_fr"]) ."</P></BLOCKQUOTE>";

        if( $lang=="*" )  echo "</TD></TR>";
    }

    if( $lang=="*" )  echo "</TABLE>";
}

?>
