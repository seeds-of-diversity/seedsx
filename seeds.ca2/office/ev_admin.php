<?php

// Judy also keeps track of this information in a spreadsheet (I think it has locations, organizers, contacts, etc)
// so it could be efficient to include all those and allow spreadsheet download.  Would there be a way to let her add extra columns?


include_once( "../site2.php" );
include_once( STDINC."SEEDForm.php" );
include_once( STDINC."SEEDDate.php" );
include_once( SEEDCOMMON."siteStart.php" );
include_once( SEEDCOMMON."siteutil.php" );  // SiteUtilRaProvinces
include_once( SEEDCOMMON."ev/_ev.php" );
include_once( STDINC."SEEDEditor.php" );

include_once( SEEDCOMMON."console/console01kfui.php" );

// DB is seeds2 : authentication is done on seeds2.SEEDSession_Users, all table references are to seeds.ev_events
// This works on www8 because seeds2 user can see seeds and seeds2 databases.
// This works on www12 because seeds2_def.php connects the single user that can see all databases
list($kfdb, $sess, $dummyLang) = SiteStartSessionAccount( array( "events" => "W" ) );

header( "Content-type: text/html; charset=ISO-8859-1");


//$kfdb->SetDebug(1);

$iCurrYear = intval(date("Y",time()+3600*24*60));
$iYear = SEEDSafeGPC_GetInt("EVfltYear");


$raConsoleParms = array(
    'HEADER' => "Seeds of Diversity Events List",
    'CONSOLE_NAME' => "Events",
    'bBootstrap' => true,
);
$raCompParms = array(
    'Label'    => "Event",
    'ListCols' => array( array( "label"=>"Type",     "colalias"=>"type",       "w"=>50 ),
                         array( "label"=>"Title",    "colalias"=>"title",      "w"=>150 ),
                         array( "label"=>"City",     "colalias"=>"city",       "w"=>150 ),
                         array( "label"=>"Province", "colalias"=>"province",   "w"=> 50 ),
                         array( "label"=>"Date",     "colalias"=>"date_start", "w"=> 50 ),
                         array( "label"=>"Location", "colalias"=>"location",   "w"=>200),
                         //array( "label"=>"Alt Date", "colalias"=>"date_alt",   "w"=>100),
                         array( "label"=>"Time",     "colalias"=>"time",       "w"=>100),
    ),
    'ListSize' => 10,
    'fnListFilter'    => "EV2_listFilter",
    'fnListTranslate' => "EV2_listTranslate",
    'fnFormDraw'      => "EV2_formDraw",
    'raSEEDFormParms' => array('DSParms'=>array('fn_DSPreStore'=>'EV2_DSPreStore')),
);

$oC = new Console01KFUI( $kfdb, $sess, $raConsoleParms );

$oEv = new EV_Events( $kfdb, $sess->GetUID() );

$oC->CompInit( $oEv->GetKfrelEvents(), $raCompParms );

$oC->SetFrameControlParm( 'EVfltYear', $iYear );   // this causes the EVfltYear value to be propagated with oComp form submissions

$s = jsControls()
    ."<br/>"
    ."<div style='clear:both'>"
    ."<div style='float:left'>"
    .$oC->oComp->SearchToolDraw()
    ."</div>"
    ."<div style='float:left;margin-left:10em;'>"
    .EV2_drawFilterControl()
    ."</div>"
    ."</div>";

$s .= $oC->CompListForm_Vert();

echo $oC->DrawConsole( $s, false );     // false: don't use console:ExpandTemplate because it is horrible if any form fields contain seedtags
                                        //        Please use some other kind of template processor in the console so seedtags in form fields don't
                                        //        have to get processed (they are literals in this situation)

function jsControls()
{
    $s = "
<script>
$(document).ready(function(){
    $('.typeSelect').click( function() { doTypeSelectUI(); } );

    doTypeSelectUI();

    function doTypeSelectUI() {
        var v = $('.typeSelect').val();

        if( v == 'SS' ) {
            $('#ev_titlebox').hide();
            $('#ev_citybox').show();
            $('#ev_locationbox').show();
        }
        if( v == 'EV' ) {
            $('#ev_titlebox').show();
            $('#ev_citybox').show();
            $('#ev_locationbox').show();
        }
        if( v == 'VIRTUAL' ) {
            $('#ev_titlebox').show();
            $('#ev_citybox').hide();
            $('#ev_locationbox').hide();
        }
    }

});
</script>
";

    return( $s );
}






function EV2_drawFilterControl()
{
    global $kfdb, $iCurrYear;

    /* Fetch the values for the 'year' global filter
     * 0 = This year
     * 1 = All
     */
    $raYearOpts[0] = "-- Future --";
    $raYearOpts[1] = $iCurrYear;
    $raYearOpts[2] = "-- All --";
    if( ($ra = $kfdb->QueryRowsRA( "SELECT distinct(YEAR(date_start)) FROM seeds.ev_events ORDER BY 1 DESC" )) ) {
        foreach( $ra as $ra1 ) {
            $y = $ra1[0];
            if( $y && $y != $iCurrYear ) $raYearOpts[$y] = $y;
        }
    }

    $s = "<FORM action='${_SERVER['PHP_SELF']}'>"
        ."Year: "
        .SEEDForm_Select( 'EVfltYear', $raYearOpts, SEEDSafeGPC_GetInt("EVfltYear"), array( "selectAttrs" => "onChange='submit();'" ) )
        ."</FORM>";

    return( $s );
}

function EV2_listFilter()
{
    global $iCurrYear, $iYear;


    switch( $iYear ) {
        case 0:  return( "date_start >= CURRENT_DATE()" );       // Future
        case 1:  return( "(YEAR(date_start) = '$iCurrYear')" );  // this year
        case 2:  return( "" );                                   // all years
        default: return( "(YEAR(date_start) = '$iYear')" );      // the selected year
    }
}

function EV2_listTranslate()
{

}

function EV2_DSPreStore( $oDS )
{
    $t = 0;

    if( !$oDS->Value('date_start') || !($t = SEEDDateDB2Unixtime($oDS->Value('date_start'))) ) {
        global $oC;
        $oC->ErrMsg( "No date was given, defaulting to TODAY" );

        $oDS->SetValue( 'date_start', date('Y-m-d') );
    } else if( $t < time() ) {
        global $oC;
        $oC->ErrMsg( "Warning: the date occurs in the past" );
    }

    return( true );
}

//TODO put this in seedcommon/SEEDBootstrap.php along with a const for W_ROOT_BOOTSTRAP
//     should this be in a SEEDBootstrap:: class?
function BS_Row( $s )
{
    return( "<div class='row'>".$s."</div>" );
}

function BS_Row2( $raCols, $raParms = array() )
/*********************************************
    Put any number of columns in a row

    $raCols: array( array( col_class, col_content ), ...
 */
{
    $s = "<div class='row'>";

    foreach( $raCols as $raCol ) {
        $s .= "<div class='${raCol[0]}'>${raCol[1]}</div>";
    }

    $s .= "</div>";  // row

    return( $s );
}

function EV2_formDraw( $oForm )
/******************************
    Mobile-ready (uses BS_Row instead of TextTD)
 */
{
    //$oForm->raParms['bBootstrap'] = true;
    global $SiteUtilRaProvinces1;
    global $oEv;

    if( ($kEv = $oForm->GetKey()) ) {
        if( ($kfr = $oEv->GetKfrelEvents()->GetRecordFromDBKey( $kEv )) ) {
            $sPreviewText = $oEv->DrawEvent( $kfr );
        } else {
            $sPreviewText = "{Error getting preview}";
        }
    } else {
        $sPreviewText = "Preview will go here";
    }


    $s = "<div class='container'>"

        ."<div class='row'>"

        // left column
        ."<div class='col-md-6'>"
            .BS_Row2( array( array( 'col-md-8', $oForm->Select2( 'type',
                                                                array( "Seedy Saturday"=>'SS', "Event"=>'EV', "Virtual"=>'VIRTUAL' ),
                                                                "", array('class'=>'typeSelect') ) ),
                             array( 'col-md-4', "<input type='submit' value='Save'/>" )
                   ))

            ."<br/>"
            ."<div id='ev_titlebox' style='margin-bottom:10px'>"
            .BS_Row( $oForm->Text( 'title',    "Title",    array('size'=>40, 'bsCol'=>"md-10,md-2") ) )
            .BS_Row( $oForm->Text( 'title_fr', "(French)", array('size'=>40, 'bsCol'=>"md-10,md-2") ) )
            ."</div>"

            ."<div id='ev_citybox' style='margin-bottom:10px'>"
            .BS_Row2( array( array( 'col-md-2', "<b>City/town</b>" ),
                             array( 'col-md-10', $oForm->Text( 'city', "", array('size'=>30) )
                                                .$oForm->Select2( 'province', $SiteUtilRaProvinces1 ) )
                   ))
            ."</div>"

            ."<div id='ev_locationbox' style='margin-bottom:10px'>"
            .BS_Row( $oForm->Text( 'location', "Location", array('size'=>30, 'bsCol'=>"md-10,md-2") ) )
            ."</div>"

            ."<div class='well'>"
                .BS_Row2( array( array( 'col-md-6', $oForm->Date( 'date_start', "Date" )."<br/>"
                                                //.$oForm->Date( 'date_end', "Date end" )."<br/>"   use date_alt instead of a range
                                                  .$oForm->Text( 'time', "Time" ) ),
                                array( 'col-md-6', "<b>Date Alternate</b><br/>"
                                                  .$oForm->Text( 'date_alt', "(en)" )."<br/>"
                                                  .$oForm->Text( 'date_alt_fr', "(fr)" ) )
                   ))
            ."</div>"
            .BS_Row( $oForm->Text( 'contact', "Contact", array('size'=>30, 'bsCol'=>"md-10,md-2") ) )
            .BS_Row( $oForm->Text( 'url_more', "Link to<br/> more info", array('size'=>30, 'bsCol'=>"md-10,md-2") ) )
            ."<br/>"

            ."<label>Details (English)</label><br/>"
            .$oForm->TextArea( 'details', "", 60, 8, array('attrs'=>"wrap='soft'") )."<br/>"
            ."<label>(French)</label><br/>"
            .$oForm->TextArea( 'details_fr', "", 60, 8, array('attrs'=>"wrap='soft'") )."<br/>"

            ."<br/><br/>"
            ."<input type='submit' value='Save'/>"

            ."<br/><br/>"
            ."<div style='padding:1em;margin:0 auto;width:95%;border:thin solid black;font-size:8pt;font-family:verdana,sans serif;'>"
                ."<B>Location</B>: name of venue, address<BR/>"
                ."<B>Date</B>: must be YYYY-MM-DD<BR/>"
                ."<B>Alternate Date Text</B>: enter a Date too, so the list can sort properly, but this will be shown instead. "
                ."e.g. if date is unknown enter 2014-01-01 for Date, TBA as Alternate - the list will show TBA as the date and it will put the event at "
                ."Jan 1, 2014<br/>"
                ."<B>Contact</B>: name, phone, email here instead of in details so we can delete that personal info later.<BR/>"
                ."<BR/>"
                ."Contact and Details use special tags [[mailto:my@email.ca] ] and [[http://my.website.ca] ]"  // escape the [[ because console01 expands template tags
            ."</div>"

        ."</div>"  // left column

        // right column
        ."<div class='col-md-6'>"
            ."<div class='well well-sm' style='background-color:#ded'>"
            .$sPreviewText
            ."</div>"
        ."</div>"  // right column

       ."</div>"  // row

//    spec        VARCHAR(200),                           # control tags (like texttype for the details)
//    latlong     VARCHAR(200),                           # latitude and longitude urlencoded (blank means it needs to be geocoded)
//    attendance  INTEGER,
//    notes_priv  TEXT,                                   # internal notes

        ."</div>";  // container

    return( $s );
}

exit;

include_once( STDINC."KeyFrame/KFUIAppSimple.php" );
include_once( STDINC."KeyFrame/KFRForm.php" );


$kfdbSeeds = SiteKFDB(SiteKFDB_DB_seeds1);
//$kfdbSeeds->SetDebug(1);

$raAppParms = array();


SiteApp_KFUIAppHeader( "Seeds of Diversity Events Lists" );


$kfuidef =
        array( "A" =>
               array( "Label" => "Event",
                      "ListCols" => array( // array( "label"=>"Page",           "col"=>"Page_name", "w"=>150),
                                           array( "label"=>"City",           "col"=>"city",      "w"=>150),
                                           array( "label"=>"Province",       "col"=>"province",  "w"=>50 ),
// Use title for EV events, not SS events  array( "label"=>"Title",          "col"=>"title",     "w"=>200),  // kluge: must be second column
                                           array( "label"=>"Location",          "col"=>"location",     "w"=>200),  // kluge: must be second column
                                           array( "label"=>"Date",           "col"=>"date_start",     "w"=>50 ),
//                                           array( "label"=>"Day",            "col"=>"day",       "w"=>50 ),
                                           array( "label"=>"Alt Date",       "col"=>"date_alt",  "w"=>100),
                                           array( "label"=>"Time",           "col"=>"time",      "w"=>100),
                                         ),
                      "ListSize" => 10,
                      "ListSizePad" => 1,
//                    "fnHeader"        => "EV_Item_header",
                      "fnListFilter"    => "EV_Item_listFilter",
                      "fnListTranslate" => "EV_Item_listTranslate",
                      "fnFormDraw"      => "EV_Item_formDraw",
                    ) );

/* Fetch the values for the 'year' global filter
 * 0 = This year
 * 1 = All
 */
$raYearOpts[0] = "-- Future --";
$raYearOpts[1] = $iCurrYear;
$raYearOpts[2] = "-- All --";
if( ($dbc = $kfdbSeeds->KFDB_CursorOpen( "SELECT distinct(YEAR(date_start)) FROM ev_events ORDER BY 1 DESC" )) ) {
    while( $ra = $kfdbSeeds->KFDB_CursorFetch($dbc) ) {
        if( $ra[0] && $ra[0] != $iCurrYear )  $raYearOpts[$ra[0]] = $ra[0];
    }
}

$raAppParms['kfLogFile'] = SITE_LOG_ROOT."events.log";
$raAppParms['raUFlt'] = array( array( "label" => "Year",
                                      "name" => "EVfltYear",
                                      "raValues" => $raYearOpts,
                                      "currValue" => SEEDSafeGPC_GetInt("EVfltYear") ) );


KFUIApp_ListForm( $kfdbSeeds, $kfreldef_EVEvents, $kfuidef, $sess->GetUID(), $raAppParms );


function EV_Item_listFilter()
/****************************
    Filter the list only to items of the current page
 */
{
    global $iCurrYear;

    $iYear = SEEDSafeGPC_GetInt("EVfltYear");

    switch( $iYear ) {
        case 0:  return( "date_start > NOW()" );                 // Future
        case 1:  return( "(YEAR(date_start) = '$iCurrYear')" );  // this year
        case 2:  return( "" );                                   // all years
        default: return( "(YEAR(date_start) = '$iYear')" );      // the selected year
    }
}

function EV_Item_listTranslate( $kfr )
/*************************************
    Change numerical month to named month in the list view
 */
{
    return( array( "month" => date("M", mktime(0,0,0,$kfr->value('month'),1,1978))));
}

function EV_Item_formDraw( $kfr )
/********************************
 */
{
    echo "<TABLE cellpadding=5 width='95%'>";

//  echo "<TR>".KFRFORM_SelectTD( $kfr, "Event type:", "type", array("SS"=>"Seedy Saturday/Sunday", "EV"=>"Event") )."</TR>";
    echo "<INPUT type='hidden' name='type' value='SS'>";

    // type=EV: title is the title of the event, city is the location
    // type=SS: city/prov is the title of the event, title is repurposed as the location

    if( $kfr->value('Page_type')=="EV" ) {
        draw_field( "title", "Title", $kfr, $kfr->value('Page_bEN'), $kfr->value('Page_bFR'), 50 );
    }

    // both types have city and province here
    echo "<TR><TD valign='top'>City:</TD><TD valign='top'>".KFRForm_Text( $kfr, "", "city", 20 );
    echo "<SELECT NAME='province'>"; echo option_province( $kfr->value('province') ); echo "</SELECT></TD>";

    echo "<TD valign='top' rowspan='5'>";
    echo "<DIV style='padding:1em;width:30em;float:right; border:thin solid black;font-size:8pt;font-family:verdana,sans serif;'>"
        ."<B>Location</B>: name of venue, address<BR>"
        ."<B>Date</B>: must be YYYY-MM-DD<BR>"
        ."<B>Alternate Date Text</B>: enter a Date too, so the list can sort properly, but this will be shown instead."
        ."e.g. if date is unknown enter 2009-01-01 for Date, TBA as Alternate - the list will show TBA as the date and it will put the event at"
        ."Jan 1, 2009<BR>"
        ."<B>Contact</B>: name, phone, email here instead of in details so we can delete that personal info later.<BR>"
        ."<BR>"
        ."Contact and Details use special tags [[mailto:my@email.ca]] and [[http://my.website.ca]] </DIV>";
    echo "</TD></TR>";

    echo "<TR>".KFRForm_TextTD( $kfr, "Location:", "location", 30 )."</TR>";

    if( $kfr->value('Page_type')=="SS" ) {
        draw_field( "title", "Location", $kfr, $kfr->value('Page_bEN'), $kfr->value('Page_bFR'), 50 );
    }

//  echo "<TR><TD align='top'>Date:</TD>      <TD align='left'>".KFRForm_Text( $kfr, "", "date_start", 20 );
    $oDP = new SEEDDateCalendar();
    echo $oDP->Setup();
    echo "<TR><TD align='top'>Date:</TD>      <TD align='left'>".$oDP->DrawCalendarControl( "date_start", $kfr->value('date_start') );
//  echo "<SELECT NAME=day>"; option_days( $kfr->value('day') ); echo "</SELECT>, ".$kfr->value('Page_year');
    if( $kfr->value("date_start") ) {
        echo "<SPAN style='font-size:9pt;color:grey'>";

        $y = substr( $kfr->value("date_start"), 0, 4 );
        $m = substr( $kfr->value("date_start"), 5, 2 );
        $d = substr( $kfr->value("date_start"), 8, 2 );
        echo SEEDStd_StrNBSP("",10) . SEEDDateStr( mktime(0,0,0,$m,$d,$y), "EN" );
        echo " / "                  . SEEDDateStr( mktime(0,0,0,$m,$d,$y), "FR" );
        echo "</SPAN>";
    }
    echo "</TD></TR>";

    echo "<TR>".KFRForm_TextTD( $kfr, "Time:", "time", 30 )."</TR>";

    draw_field( "date_alt", "Alternate Date&nbsp;Text", $kfr, $kfr->value('Page_bEN'), $kfr->value('Page_bFR'), 50 );

    echo "<TR>".KFRForm_TextTD( $kfr, "Contact:", "contact", 100 )."</TR>";

    echo "<TR><TD align='left' valign=top>Details:"
        ."<BR><BR><BR><BR><BR><BR>"
        ."<INPUT type=submit value=Save>"
        ."</TD><TD colspan=2 align='left'>";
    echo "<TABLE cellpadding=5 width='100%'>";
//  if( $kfr->value('Page_bEN') ) {
        echo "<TR><TD valign='top' bgcolor='".CLR_BG_editEN."' width='100%'>(English) ";
//        SEEDEditor_Text( 'details', $kfr->value('details'), array("height_px" => 200, "width" => "100%", "editor"=>"PLAIN") );
        echo "<TEXTAREA style='width:100%' wrap='SOFT' rows='13' name='details'>".$kfr->valueEnt('details')."</TEXTAREA>";
        echo "</TD></TR>";
//  }
//  if( $kfr->value('Page_bFR') ) {
        echo "<TR><TD valign='top' bgcolor='".CLR_BG_editFR."' width='100%'>(Fran&ccedil;ais) ";
//        SEEDEditor_Text( 'details_fr', $kfr->value('details_fr'), array("height_px" => 200, "width" => "100%", "editor"=>"PLAIN") );
        echo "<TEXTAREA style='width:100%' wrap='SOFT' rows='13' name='details_fr'>".$kfr->valueEnt('details_fr')."</TEXTAREA>";
        echo "</TD></TR>";
//  }
    echo "</TABLE>";
//  if( !$kfr->value('Page_bEN') )  echo KFRForm_Hidden( $kfr, "details" );
//  if( !$kfr->value('Page_bFR') )  echo KFRForm_Hidden( $kfr, "defails_fr" );
    echo "</TD></TR>\n";
//  echo "<TR><TD align=center colspan=2><INPUT TYPE=SUBMIT VALUE='".(($i=="new") ? "Add":"Update")."'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
//  echo "<A HREF='page.php?p=".$p."&".$la->login_auth_get_urlparms()."'>Cancel</A></TD></TR>\n";
    echo "</TABLE>";
}



function draw_field( $name, $label, $kfr, $bEN, $bFR, $size )
/************************************************************
*/
{
    echo "<TR><TD align='left'>$label:</TD><TD align='left'>";
    echo "<TABLE cellpadding=5>";
    echo "<TR><TD bgcolor=".CLR_BG_editEN.">(English) <INPUT TYPE=TEXT NAME='$name' VALUE='".$kfr->ValueEnt($name)."' size='$size'></TD></TR>\n";
    echo "<TR><TD bgcolor=".CLR_BG_editFR.">(Fran&ccedil;ais) <INPUT TYPE=TEXT NAME='{$name}_fr' VALUE='".$kfr->ValueEnt($name.'_fr')."' size='$size'></TD></TR>\n";
    echo "</TABLE>";
//  if( !$bEN )  echo "<INPUT TYPE=HIDDEN NAME={$name} VALUE=\"".$kfr->ValueEnt($name)."\">";
//  if( !$bFR )  echo "<INPUT TYPE=HIDDEN NAME={$name}_fr VALUE=\"".$kfr->ValueEnt($name.'_fr')."\">";
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
