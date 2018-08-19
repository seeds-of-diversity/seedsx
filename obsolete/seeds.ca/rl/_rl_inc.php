<?
// show _updated and _verified in rl_cmp_show


if( !defined("RL_pageType") )  define("RL_pageType", "normal");

// define the rl_companies record

$rl_cmp_recdef = array( 'table'    => 'rl_companies',
                        'rowid'    => 'rl_cmp_id',
                        'disabled' => '_disabled',
                        'fields'   => array( array("name_en",    "s", ""),
                                             array("name_fr",    "s", ""),
                                             array("addr_en",    "s", ""),
                                             array("addr_fr",    "s", ""),
                                             array("city",       "s", ""),
                                             array("prov",       "s", ""),
                                             array("country",    "s", "Canada"),
                                             array("postcode",   "s", ""),
                                             array("phone",      "s", ""),
                                             array("fax",        "s", ""),
                                             array("web",        "s", ""),
                                             array("web_alt",    "s", ""),
                                             array("email",      "s", ""),
                                             array("email_alt",  "s", ""),
                                             array("desc_en",    "s", ""),
                                             array("desc_fr",    "s", ""),
                                             array("cat_cost",   "i", -1),
                                             array("year_est",   "i",  0),
                                             array("comments",   "s", ""),
                                             array("supporter",  "i",  0),
                                             array("xlat",       "i",  0) ) );


function rl_cmp_get( $i )
/************************
    Return an rl_companies row
 */
{
    return( db_query( "SELECT * FROM rl_companies WHERE rl_cmp_id=". $i ) );
}


function rl_cmp_cat_cost_str( $cat_cost, $lang )
/***********************************************
 */
{
    if( $cat_cost == 0 )      { $s = "  ". ( $lang=="EN" ? "Catalogue free." : "Catalogue gratuit." ); }
    else if( $cat_cost > 0  ) { $s = "  Catalogue $". $cat_cost; }
    else                      { $s = ""; }
    return( $s );
}

function rl_cmp_show_from_rec( $rec )
/************************************
    Can only use this if RL_pageType==update because we cannot pass required parms for other page types when this
    is used as a callback from BXAdminDeleteRecord.
 */
{
    $ra = rl_cmp_get( $rec->rowid );
    rl_cmp_show( $ra );
}

function rl_cmp_show( $ra, $lang = "*", $auth_urlparms = "" )
/************************************************************
    Draw a RL company record.

    RL_pageType:
        normal = 1x2 table with name in first cell, everything else in second cell
        print  = 2x2 table with name/address in the top row, desc in the bottom row colspan=2
        update = 3x2 table similar to print but with both languages shown
        edit   = same as update but with record-level edit controls

    Parms needed:
        normal   lang
        print    lang
        update   none
        edit     auth_urlparms
 */
{
    $name = $lang=="EN" ? $ra['name_en'] : $ra['name_fr'];  if( empty($name) )  $name = $ra['name_en'];
    $addr = $lang=="EN" ? $ra['addr_en'] : $ra['addr_fr'];  if( empty($addr) )  $addr = $ra['addr_en'];
    $desc = $lang=="EN" ? $ra['desc_en'] : $ra['desc_fr'];


    if( RL_pageType == "normal" || RL_pageType == "print" ) {
        // format the addr,phone,fax
        $coordinates  = "${addr},&nbsp;${ra['city']},&nbsp;";
        $coordinates .= $ra['prov'];   // could be translated from a standard code based on $lang and country
        $coordinates .= "&nbsp;${ra['postcode']}";
        $coord_sep = (RL_pageType == "print") ? "&nbsp;" : "<BR>";
        if( $ra['phone'] || $ra['fax'] )  $coordinates .= $coord_sep . $ra['phone'];
        if( $ra['fax'] )                  $coordinates .= $coord_sep . "fax:&nbsp;". $ra['fax'];

        if( RL_pageType == "normal" ) {
            echo "<TR valign='top'><TD><FONT face='Arial,Helvetica,Sans Serif'><B>${name}</B></FONT></TD>";
            echo "<TD>$coordinates";
            // Do not urlencode these - that is only for parms.  urls that contain '/' are encoded, which breaks them.
            // This opens the potential for bad behaviour if our stored url contains parms
            if( $ra['web'] )    echo "<BR><A HREF='http://${ra['web']}' TARGET='_rlwebref'>${ra['web']}</A>";
            if( $ra['email'] )  echo "<BR><A HREF='mailto:${ra['email']}'>${ra['email']}</A>";
            echo "<BR>${desc} ". rl_cmp_cat_cost_str( $ra['cat_cost'], $lang );
            echo "</TD></TR>\n";
        } else {  // print
            echo "<TR valign='top'><TD class='rl_companyname' width='20%'>${name}</TD>";
            echo "<TD align='left' class='rl_companyaddr'><nobr>$coordinates</nobr>&nbsp;&nbsp;";
            if( $ra['web'] )    echo "&nbsp;${ra['web']}";
            if( $ra['email'] )  echo "&nbsp;${ra['email']}";
            echo "</TD></TR>\n";
            echo "<TR><TD colspan=2 class='rl_companydesc'>${desc} ". rl_cmp_cat_cost_str( $ra['cat_cost'], $lang );
            echo "<BR><BR></TD></TR>\n";
        }
    } else { // edit, update
        if( RL_pageType == "edit" ) {
            echo "<TR><TD colspan=3>";
            echo     "<P><FONT size=2><A HREF='".RLROOT."admin/edit.php?i=${ra['rl_cmp_id']}${auth_urlparms}'><FONT COLOR='red'>[Edit]</FONT></A>&nbsp;";
            echo                     "<A HREF='".RLROOT."admin/delete.php?i=${ra['rl_cmp_id']}${auth_urlparms}'><FONT COLOR='red'>[Delete]</FONT></A>";
            //echo                     " - last updated {$ra['_updated']} - last verified {$ra['_verified']}";
            echo                     "</P></TD></TR>";
        } else {
            // update only shows one record, needs a table tag
            echo "<TABLE>";
        }
        echo "<TR valign='top'>";
        echo     "<TD align=left><TABLE><TR><TD bgcolor=".CLR_BG_editEN."><FONT SIZE=2><B>${ra['name_en']}</TD></TR>";
        echo                           "<TR><TD bgcolor=".CLR_BG_editFR."><FONT SIZE=2><B>${ra['name_fr']}</TD></TR></TABLE></TD>";
        echo     "<TD><TABLE><TR>";
        echo         "<TD align=left><TABLE><TR><TD bgcolor=".CLR_BG_editEN."><FONT SIZE=2><nobr>${ra['addr_en']}</nobr></TD></TR>";
        echo                               "<TR><TD bgcolor=".CLR_BG_editFR."><FONT SIZE=2><nobr>${ra['addr_fr']}</nobr></TD></TR></TABLE></TD>";
        echo         "<TD align=left><FONT size=2><nobr>${ra['city']} ${ra['prov']} ${ra['postcode']}</nobr></TD>";
        echo     "</TD></TR></TABLE></TD>";
        echo     "<TD align=left><FONT size=2><nobr>";
        echo         "Phone: ${ra['phone']} <BR> Fax: ${ra['fax']} <BR>";
        echo         "Web: <A HREF='http://${ra['web']}' TARGET='_rlwebref'>${ra['web']}</A> <BR>";
        echo         "Email: <A HREF='mailto:${ra['email']}'>${ra['email']}</A><BR>";
        echo         "Established: ". ($ra['year_est'] ? $ra['year_est'] : "");
        echo     "</nobr></FONT></TD></TR>\n";
        echo "<TR><TD colspan=3 bgcolor=".CLR_BG_editEN."><FONT size=2>${ra['desc_en']} ". rl_cmp_cat_cost_str( $ra['cat_cost'], "EN" ) ."</TD></TR>\n";
        echo "<TR><TD colspan=3 bgcolor=".CLR_BG_editFR."><FONT size=2>${ra['desc_fr']} ". rl_cmp_cat_cost_str( $ra['cat_cost'], "FR" ) ."</TD></TR>\n";
        if( $ra['xlat'] )     echo "<TR><TD colspan=3><FONT color=red size=2>Translation needed</FONT></TD></TR>";
        if( $ra['comments'] ) echo "<TR><TD colspan=3><FONT size=2>Comments: {$ra['comments']}</TD></TR>";
        if( RL_pageType == "edit" ) {
            echo "<TR><TD colspan=3>&nbsp;<HR width=100%>&nbsp;</TD></TR>";
        } else {
            echo "</TABLE>";
        }
    }
}

?>
