<?

define( "SITEROOT", "../../" );
include_once( SITEROOT."site.php" );
include_once( EV_ROOT."_ev_inc.php" );
include_once( STDINC."KeyFrame/KFUI.php" );
include_once( SITEINC ."sodlogin.php" );


$la = new SoDLoginAuth;
if( !$la->SoDLoginAuth_Authenticate( $_REQUEST, "W EV" ) ) { exit; }


$p = BXStd_SafeGPCGetInt('phflt');
if( !$p )  $p = db_query1("SELECT MAX(page_code) from event_pages");
if( $p ) { $raPage = db_query("SELECT * FROM event_pages WHERE page_code=$p"); }


$EV_Item_FrameDef['RelationFKValue'] = $p;

if( $raPage['type'] == "SS" ) {
    // Title is repurposed as the location
    // Kluge: change the label - assume that it is the second column in the List Def
    $EV_Item_FrameDef['ListCols'][1]['label'] = "Location";
}




dbPhrameUI( $EV_Item_FrameDef, $la->LoginAuth_UID() );

// END



function EV_Item_header($dpui)
/*****************************
 */
{
    global $raPage, $p;

    echo "<TABLE width='100%'><TR><TD><H2>{$raPage['name']} | {$raPage['name_fr']}</H2></TD><TD>&nbsp;</TD>";
    echo "<TD align=right><FORM action='{$_SERVER['PHP_SELF']}' target='_top'>";
    echo dbPhrameUI_User_HiddenFormParms( $dpui, array("keepSel"=>false),array("phflt") );
    echo "<SELECT name=phflt>";
//    echo "<OPTION value=''".($p ? "" : " SELECTED")."> -- CHOOSE -- </OPTION>";
    if( $dbc = db_open( "SELECT page_code,name,name_fr FROM event_pages ORDER BY page_code" ) ) {
        while( $ra = db_fetch( $dbc ) ) {
            echo "<OPTION value='{$ra[0]}'".(($p==$ra[0]) ? " SELECTED" : "").">{$ra[1]} | {$ra[2]}</OPTION>";
        }
    }
    echo "</SELECT><INPUT type=submit value='Choose Page'></FORM></TD></TR></TABLE>";

    echo "<P><A HREF='".EV_ROOT."evpage.php?p=$p&lang=EN' target='_blank'><FONT color=red>See this page in english</FONT></A><BR>";
    echo    "<A HREF='".EV_ROOT."evpage.php?p=$p&lang=FR' target='_blank'><FONT color=red>See this page in french</FONT></A></P>";
}


function EV_Item_rowFilter()
/***************************
 */
{
    global $p;

    // KLUGE:  Filtering is supposed to be done by RelationFKValue above, but my test data has some page_code==0
    //         so these show up when the "NULL" page is selected.  Ensure no rows returned in this case.
    return( $p ? "" : "(0=1)" );
}


function EV_Item_formDraw( $dPRec )
/**********************************
 */
{
    global $raPage, $p;

    if( !$p )  return;

    echo "<TABLE cellpadding=5 width='50%' align='center'>";

    // type=EV: title is the title of the event, city is the location
    // type=SS: city/prov is the title of the event, title is repurposed as the location

    if( $raPage['type']=="EV" ) {
        draw_field( "title", "Title", $dPRec, $raPage['bEN'], $raPage['bFR'], 50 );
    }

    // both types have city and province here
    echo "<TR><TD align='left'>City:</TD>      <TD align='left'><INPUT TYPE=TEXT NAME=city     VALUE='".$dPRec->dPR_valueEnt('city')."'     size=20>";
    echo "<SELECT NAME=province>"; echo option_province( $dPRec->dPR_value('province') ); echo "</SELECT></TD></TR>";

    if( $raPage['type']=="SS" ) {
        draw_field( "title", "Location", $dPRec, $raPage['bEN'], $raPage['bFR'], 50 );
    }

    echo "<TR><TD align='left'>Date:</TD>      <TD align='left'><SELECT NAME=month>"; echo option_months( $dPRec->dPR_value('month') ); echo "</SELECT>";
    echo "<SELECT NAME=day>"; option_days( $dPRec->dPR_value('day') ); echo "</SELECT>, ".$raPage['year']."</TD></TR>";
    draw_field( "date_alt", "Alternate Date&nbsp;Text", $dPRec, $raPage['bEN'], $raPage['bFR'], 50 );
    echo "<TR><TD align='left'>Time:</TD>      <TD align='left'><INPUT TYPE=TEXT NAME=time     VALUE='".$dPRec->dPR_valueEnt('time')."' size=70></TD></TR>";

    echo "<TR><TD align='left' valign=top>Details:</TD><TD align='left'>";
    echo "<TABLE cellpadding=5>";
    if( $raPage['bEN'] )  echo "<TR><TD bgcolor='".CLR_BG_editEN."'>(English) <TEXTAREA NAME=details COLS=52 ROWS=5 WRAP=SOFT>".$dPRec->dPR_valueEnt('details')."</TEXTAREA></TD></TR>";
    if( $raPage['bFR'] )  echo "<TR><TD bgcolor='".CLR_BG_editFR."'>(Fran&ccedil;ais) <TEXTAREA NAME=details_fr COLS=52 ROWS=5 WRAP=SOFT>".$dPRec->dPR_valueEnt('details_fr')."</TEXTAREA></TD></TR>";
    echo "</TABLE>";
    if( !$raPage['bEN'] )  echo '<INPUT TYPE=HIDDEN NAME=details VALUE="'. $dPRec->dPR_valueEnt('details') .'">';
    if( !$raPage['bFR'] )  echo '<INPUT TYPE=HIDDEN NAME=details_fr VALUE="'. $dPRec->dPR_valueEnt('details_fr') .'">';
    echo "</TD></TR>\n";
//  echo "<TR><TD align=center colspan=2><INPUT TYPE=SUBMIT VALUE='".(($i=="new") ? "Add":"Update")."'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
//  echo "<A HREF='page.php?p=".$p."&".$la->login_auth_get_urlparms()."'>Cancel</A></TD></TR>\n";
    echo "</TABLE>";
}



function draw_field( $name, $label, $rec, $bEN, $bFR, $size )
/*
    CANNOT HANDLE double-quotes IN TEXT OR HIDDEN FIELDS!    - BREAKS HTML
*/
{
    echo "<TR><TD align='left'>$label:</TD><TD align='left'>";
    echo "<TABLE cellpadding=5>";
    if( $bEN )  echo "<TR><TD bgcolor=".CLR_BG_editEN.">(English) <INPUT TYPE=TEXT NAME={$name} VALUE=\"".$rec->dPR_valueEnt($name)."\" size={$size}></TD></TR>\n";
    if( $bFR )  echo "<TR><TD bgcolor=".CLR_BG_editFR.">(Fran&ccedil;ais) <INPUT TYPE=TEXT NAME={$name}_fr VALUE=\"".$rec->dPR_valueEnt($name.'_fr')."\" size={$size}></TD></TR>\n";
    echo "</TABLE>";
    if( !$bEN )  echo "<INPUT TYPE=HIDDEN NAME={$name} VALUE=\"".$rec->dPR_valueEnt($name)."\">";
    if( !$bFR )  echo "<INPUT TYPE=HIDDEN NAME={$name}_fr VALUE=\"".$rec->dPR_valueEnt($name.'_fr')."\">";
    echo "</TD></TR>";
}


function option_months( $sel ) {
    for( $i = 1; $i <= 12; ++$i ) {
        echo "<OPTION value='". $i ."'". ( $i==$sel ? " SELECTED" : "") .">". strftime( "%B", mktime(0,0,0,$i,1) ). "</OPTION>";
    }
}

function option_days( $sel ) {
    for( $i = 1; $i <= 31; ++$i ) {
        echo "<OPTION value='". $i ."'". ( $i==$sel ? " SELECTED" : "") .">". $i ."</OPTION>";
    }
}

function option_province( $province ) {
    echo "<OPTION value='AB'". ($province=='AB' ? " SELECTED" : "") .">AB</OPTION>";
    echo "<OPTION value='BC'". ($province=='BC' ? " SELECTED" : "") .">BC</OPTION>";
    echo "<OPTION value='MB'". ($province=='MB' ? " SELECTED" : "") .">MB</OPTION>";
    echo "<OPTION value='NB'". ($province=='NB' ? " SELECTED" : "") .">NB</OPTION>";
    echo "<OPTION value='NF'". ($province=='NF' ? " SELECTED" : "") .">NF</OPTION>";
    echo "<OPTION value='NS'". ($province=='NS' ? " SELECTED" : "") .">NS</OPTION>";
    echo "<OPTION value='ON'". ($province=='ON' ? " SELECTED" : "") .">ON</OPTION>";
    echo "<OPTION value='PE'". ($province=='PE' ? " SELECTED" : "") .">PE</OPTION>";
    echo "<OPTION value='QC'". ($province=='QC' ? " SELECTED" : "") .">QC</OPTION>";
    echo "<OPTION value='SK'". ($province=='SK' ? " SELECTED" : "") .">SK</OPTION>";
    echo "<OPTION value='YK'". ($province=='YK' ? " SELECTED" : "") .">YK</OPTION>";
    echo "<OPTION value='NT'". ($province=='NT' ? " SELECTED" : "") .">NT</OPTION>";
    echo "<OPTION value='NU'". ($province=='NU' ? " SELECTED" : "") .">NU</OPTION>";
}

?>
