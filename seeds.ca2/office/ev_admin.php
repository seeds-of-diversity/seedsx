<?php

include_once( "../site2.php" );
include_once( STDINC."SEEDForm.php" );
include_once( STDINC."SEEDDate.php" );
include_once( SEEDCOMMON."siteStart.php" );
include_once( SEEDCOMMON."siteutil.php" );  // SiteUtilRaProvinces
include_once( SEEDCOMMON."ev/_ev.php" );
include_once( STDINC."SEEDEditor.php" );

include_once( SEEDCOMMON."console/console01kfui.php" );

include_once( SEEDROOT."Keyframe/KeyframeForm.php" );
include_once( SEEDLIB.'events/events.php' );

list($kfdb, $sess, $dummyLang) = SiteStartSessionAccount( array( "W events" ) );

$oApp = SiteAppConsole( ['db'=>'seeds2', 'sessPermsRequired'=>['W events']] );

header( "Content-type: text/html; charset=ISO-8859-1");


//$kfdb->SetDebug(1);

$iCurrYear = intval(date("Y",time()+3600*24*60));
$iYear = SEEDInput_Int("EVfltYear");


$raConsoleParms = array(
    'HEADER' => "Seeds of Diversity Events List",
    'CONSOLE_NAME' => "Events",
    'HEADER_LINKS' => array( ['label'=>'Volunteers', 'href'=>'ev_volunteers.php', 'target'=>'_blank'] ),
    'bBootstrap' => true,
    'script_files' => [W_CORE."js/SEEDCore.js", W_CORE."js/SFUTextComplete.js", W_CORE."js/MbrSelector.js"]
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
                         array( "label"=>"Volunteer", "colalias"=>"vol_kMbr", "w"=>100  ),
    ),
    'ListSize' => 8,
    'fnListFilter'    => "EV2_listFilter",
    'fnListRowTranslate' => "EV2_listTranslate",
    'fnFormDraw'      => "EV2_formDraw",
    'raSEEDFormParms' => array('DSParms'=>array('fn_DSPreStore'=>'EV2_DSPreStore')),
);

$oC = new Console01KFUI( $kfdb, $sess, $raConsoleParms );

$oEv = new EV_Events( $kfdb, $sess->GetUID() );

$oEvents = new EventsLib( $oApp );

$oC->CompInit( $oEv->GetKfrelEvents(), $raCompParms, 'A' );

$oFormEv = new KeyframeForm( $oEvents->oDB->KFRel('E'), 'A', [] );
$oFormEv->Load();
//var_dump($oFormEv->GetValuesRA());


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

function EV2_listTranslate( $kfr )
{
    $ra = $kfr->ValuesRA();

    // make a more readable date
    if( ($t = @strtotime($ra['date_start'])) ) {
        $ra['date_start'] = date('Y-M-d', $t );
    }

    // look up volunteer name
    if( ($kMbr = $ra['vol_kMbr']) ) {
        $ra['vol_kMbr'] = $kfr->kfrel->kfdb->Query1( "SELECT concat(firstname,' ',lastname,' in ',city) FROM seeds2.mbr_contacts WHERE _key='$kMbr'" );
    }

    return( $ra );
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
 */
{
    //$oForm->raParms['bBootstrap'] = true;
    global $oEv;
    global $kfdb;
    global $oFormEv;
    global $oC;
    global $oEvents;

// When you click on a list item sfAk causes the new form to Load() the record, but
// on the first instance of the app (or after a search!) there is no sfAk parm.  Console01KFUI figures out the current record
// from the first item in the list and sets up its form.
// Set the new form to the same record as the old form.
//if( !$oFormEv->GetKey() ) {
    $k = $oC->oComp->oForm->GetKey();
    $kfr = $k ? $oEvents->oDB->GetKFR( 'E', $k ) : $oEvents->oDB->KFRel('E')->CreateRecord();   // do the right thing if $k is zero (New record)
    $oFormEv->SetKFR( $kfr );
//}



    if( ($kEv = $oForm->GetKey()) ) {
        if( ($kfr = $oEv->GetKfrelEvents()->GetRecordFromDBKey( $kEv )) ) {
            $sPreviewText = $oEv->DrawEvent( $kfr, $oEvents );
        } else {
            $sPreviewText = "{Error getting preview}";
        }
    } else {
        $sPreviewText = "Preview will go here";
    }

    $raMbrVol = ($kVol = $oForm->Value('vol_kMbr')) ? $kfdb->QueryRA( "SELECT * FROM seeds2.mbr_contacts WHERE _key='$kVol'" ) : [];

    $sForm = EV_formdraw( $oFormEv, $raMbrVol );

    $s = "<div class='container'><div class='row'>"
            ."<div class='col-md-6'>"
                .$sForm
            ."</div>"
            ."<div class='col-md-6'>"
                ."<div class='well well-sm' style='background-color:#ded'>"
                .$sPreviewText
                ."</div>"
            ."</div>"
        ."</div></div>";

//    spec        VARCHAR(200),                           # control tags (like texttype for the details)
//    latlong     VARCHAR(200),                           # latitude and longitude urlencoded (blank means it needs to be geocoded)
//    attendance  INTEGER,
//    notes_priv  TEXT,                                   # internal notes

    $s .= EV2_volSearchJS();

    return( $s );
}


function EV2_volSearchJS()
{
    $urlQ = SITEROOT_URL."app/q/q2.php";    // same as q/index.php but authenticates on seeds2

    $s = "<script>
$(document).ready( function() {
    // 'o' is not used anywhere; this just sets up the MbrSelector control to run independently
    let o = new MbrSelector( { urlQ:'".$urlQ."', idTxtSearch:'sfAp_dummy_kMbr', idOutReport:'vol-label', idOutKey:'sfAp_vol_kMbr' } );
});
</script>";

    return( $s );
}


function EV_formdraw( $oForm, $raMbrVol )
{
    global $SiteUtilRaProvinces1;

    $sMainSummary = "";

    $sMainForm =
             BS_Row2( array( array( 'col-md-8', $oForm->Select( 'type',
                                                                [ "Seedy Saturday"=>'SS', "Event"=>'EV', "Virtual"=>'VIRTUAL' ],
                                                                "", ['classes'=>'typeSelect'] ) ),
                             array( 'col-md-4', "<input type='submit' value='Save'/>" )
                   ))

            ."<br/>"
            ."<div id='ev_titlebox' style='margin-bottom:10px'>"
            ."<div class='row'>".$oForm->Text( 'title',    "Title",    array('size'=>40, 'bsCol'=>"md-10,md-2") )."</div>"
            ."<div class='row'>".$oForm->Text( 'title_fr', "(French)", array('size'=>40, 'bsCol'=>"md-10,md-2") )."</div>"
            ."</div>"

            ."<div id='ev_citybox' style='margin-bottom:10px'>"
            .BS_Row2( array( array( 'col-md-2', "<b>City/town</b>" ),
                             array( 'col-md-10', $oForm->Text( 'city', "", array('size'=>30) )
                                                .$oForm->Select( 'province', $SiteUtilRaProvinces1 ) )
                   ))
            ."</div>"

            ."<div id='ev_locationbox' style='margin-bottom:10px'>"
            ."<div class='row'>".$oForm->Text( 'location', "Location", array('size'=>30, 'bsCol'=>"md-10,md-2") )."</div>"
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
            ."<br/>"

            ."<label>Details (English)</label><br/>"
            .$oForm->TextArea( 'details', ['width'=>'100%', 'attrs'=>"wrap='soft'"] )."<br/>"
            ."<label>(French)</label><br/>"
            .$oForm->TextArea( 'details_fr', ['width'=>'100%', 'attrs'=>"wrap='soft'"] )."<br/>"

            ."<br/>"
            ."<div class='row'>".$oForm->Text( 'contact', "Contact", array('size'=>30, 'bsCol'=>"md-10,md-2") )."</div>"
            ."<div class='row'>".$oForm->Text( 'url_more', "Link to<br/> more info", array('size'=>30, 'bsCol'=>"md-10,md-2") )."</div>"
            ."<br/><hr/>"


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
            ."</div>";

    // Our registration for the event
    $sRegSummary = "";
    $sRegForm = "";

    // Volunteer coordination
    $sVolSummary = "";
    $sVolForm =
             "<div style='position:relative'>"     // specify position because SFU_TextComplete puts the <select> relative to first "positioned" ancestor
            ."<h3>Volunteer Coordination</h3>"
            ."<label>Our main volunteer there</label><br/>"

            //[[text:dummy_kMbr | size:10 class:SFU_TextComplete | placeholder='Search']]
            //[[hidden:vol_kMbr]]
            ."<span id='vol-label'>".(@$raMbrVol['_key'] ? "{$raMbrVol['firstname']} {$raMbrVol['lastname']} in {$raMbrVol['city']} ({$raMbrVol['_key']})" : "")."</span>"
            ."&nbsp;&nbsp;"
            .$oForm->Text( 'dummy_kMbr', '', ['size'=>10,'classes'=>'SFU_TextComplete','attrs'=>"placeholder='Search'"] )
            .$oForm->Hidden( 'vol_kMbr' )
            ."<br/>"
            ."<label>Materials to ship and notes</label><br/>"
            .$oForm->TextArea( 'vol_notes', ['width'=>'100%', 'attrs'=>"wrap='soft'"] )."<br/>"
            ."<label>Date materials mailed (YYYY-MM-DD or N/A)</label><br/>"
            .$oForm->Text( 'vol_dSent' )."<br/>"
            ."<input type='submit' value='Save'/>"
            ."</div>";


    $s = "<style>
              .ev-form-doClose { font-size: x-small; content: 'Close'; border:1px solid #888; padding:5px; margin:10px;
                                 background-color:#8af; color:white; width:5em; text-align:center; }
          </style>"
        ."<div class='ev-form well'>"
            ."<div class='ev-form-doOpen'>Event Form</div>"
            ."<div class='ev-form-doClose'>Close</div>"
            ."<div class='ev-form-bodyClosed'>$sMainSummary</div>"
            ."<div class='ev-form-bodyOpen'>$sMainForm</div>"
        ."</div>"
        ."<div class='ev-form well'>"
            ."<div class='ev-form-doOpen'>Registration</div>"
            ."<div class='ev-form-doClose'>Close</div>"
            ."<div class='ev-form-bodyClosed'>$sRegSummary</div>"
            ."<div class='ev-form-bodyOpen'>$sRegForm</div>"
        ."</div>"
        ."<div class='ev-form well'>"
            ."<div class='ev-form-doOpen'>Volunteer Coordination</div>"
            ."<div class='ev-form-doClose'>Close</div>"
            ."<div class='ev-form-bodyClosed'>$sVolSummary</div>"
            ."<div class='ev-form-bodyOpen'>$sVolForm</div>"
        ."</div>";

    $s .= "<script>
$(document).ready( function() {
        $('.ev-form-doOpen').show();
        $('.ev-form-doClose').hide();
        $('.ev-form-bodyOpen').hide();
        $('.ev-form-bodyClosed').show();

        $('.ev-form-doOpen').click( function() {
            $(this).hide();
            let f = $(this).closest('.ev-form');
            f.find('.ev-form-doClose').show();
            f.find('.ev-form-bodyOpen').show();
            f.find('.ev-form-bodyClosed').hide();
        });
        $('.ev-form-doClose').click( function() {
            $(this).hide();
            let f = $(this).closest('.ev-form');
            f.find('.ev-form-doOpen').show();
            f.find('.ev-form-bodyOpen').hide();
            f.find('.ev-form-bodyClosed').show();
        });
});

SEEDCore_CleanBrowserAddress();

</script>
";


    return( $s );
}

?>
