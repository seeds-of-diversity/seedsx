<?php
include_once(STDINC."KeyFrame/KFRelation.php");
include_once( STDINC."SEEDDate.php" );
include_once( SEEDCORE."SEEDTag.php" );
include_once( STDINC."SEEDWiki.php" );
include_once( SEEDCOMMON."siteutil.php" );  // SelectProvince


if( !defined("CLR_BG_editEN") ) define( "CLR_BG_editEN","#e0e0e0");
if( !defined("CLR_BG_editFR") ) define( "CLR_BG_editFR","#e0e0ff");


/*
    SS: title is not stored: created via "Seedy Saturday/Sunday {city}"
        city = the city or town
        location = the venue and address

    EV: title = the name of the event
        city = the city or town
        location = the venue and address

    VIRTUAL:
        title = the name of the event
        city = blank
        province = blank
        location = blank
 */
define("SEED_DB_TABLE_EV_EVENTS",
"
CREATE TABLE IF NOT EXISTS ev_events (
    # all events are stored here

        _key        INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT,
        _created    DATETIME,
        _created_by INTEGER,
        _updated    DATETIME,
        _updated_by INTEGER,
        _status     INTEGER DEFAULT 0,

    type        enum('SS','EV','VIRTUAL') NOT NULL DEFAULT 'SS',  # SS = Seedy Sat/Sun, EV = regular, VIRTUAL = no location
    date_start  DATE NOT NULL DEFAULT '2017-01-01',
    date_end    DATE NOT NULL DEFAULT '2017-01-01',
    date_alt    VARCHAR(200),
    date_alt_fr VARCHAR(200),
    time        VARCHAR(200),
    location    VARCHAR(200),
    city        VARCHAR(200),
    province    VARCHAR(200),
    title       VARCHAR(200),
    title_fr    VARCHAR(200),
    spec        VARCHAR(200),                           # control tags (like texttype for the details)
    details     TEXT,
    details_fr  TEXT,
    contact     VARCHAR(200),
    url_more    VARCHAR(200),                           # click for more info, poster, special page, etc
    latlong     VARCHAR(200),                           # latitude and longitude urlencoded (blank means it needs to be geocoded)
    attendance  INTEGER,
    notes_priv  TEXT,                                   # internal notes

    vol_kMbr    INTEGER NOT NULL DEFAULT 0,             # our main volunteer there
    vol_notes   TEXT,                                   # materials to send and notes about volunteer/event
    vol_dSent   VARCHAR(20),                            # YYYY-MM-DD when materials sent ('' == not sent yet, anything else means no need to send)

    INDEX (date_start),
    INDEX (province)
);
"
);


class EV_Events {
    public $gMapsAPIKey_www_seeds_ca = "ABQIAAAAZH7Z2EuHYHx72zrJHRiVphQ8I1Ru0X2yQKG6mkY9VLdWFUIWshQUkdimuessOVdYcpo36SexnaFD3g";

    public $lang;
    public $bPrn = false;

    public $kfrel;    // use GetKfrelEvents instead

    protected $oWiki;
    private   $oTag;            // SEEDTagParser
    private   $oBasicResolver;  // used by local SEEDTagParser resolver

    function __construct( KeyFrameDB $kfdb, $uid = 0, $lang = 'EN' )
    /***************************************************************
     */
    {
        $this->lang = $lang;
        $this->oWiki = new SEEDWikiParser();

        // When resolving SEEDTag fields, do local ResolveTag first then BasicResolver
        $this->oBasicResolver = new SEEDTagBasicResolver( array('bLinkTargetBlank'=>true,          // force links to open new windows/tabs
                                                                'bLinkPlainDefaultCaption'=>true) );    // use a nice default caption for the link
        $raResolvers = array( array( 'fn'=>array($this,'ResolveTag'), 'raParms'=>array() ),
                              array( 'fn'=>array($this->oBasicResolver,'ResolveTag'), 'raParms'=>array() )
                            );
        $this->oTag = new SEEDTagParser( array( 'raResolvers' => $raResolvers ) );

        $this->initKfrel( $kfdb, $uid );
    }

    function ResolveTag( $raTag, SEEDTagParser $oTagDummy, $raParmsDummy )
    /*********************************************************************
        Called before SEEDTag's BasicResolver. Does these conversions:
            1) tags with no namespace that look like email addresses [[foo@seeds.ca]]          -> mailto:foo@seeds.ca
            2) tags with no namespace that don't look like email addresses [[seeds.ca/events]] -> http://seeds.ca/events

            N.B. This actually changes [[seeds.ca/events]] to [[http:seeds.ca/events]] without the forward slashes.
                 BasicResolver knows to do the right thing.
     */
    {
        $bHandled = false;
        $bRetagged = false;
        $s = "";

        if( $raTag['tag'] == '' ) {
            // If the tag has no namespace change it to http or mailto.
            $raTag['tag'] = (strpos($raTag['target'],'@') !== false) ? 'mailto' : 'http';
            $bRetagged = true;
        }

        // ReTagging works by returning true as the third return value and a new raTag as the fourth. Subsequent Resolvers will use the new raTag.
        return( array( $bHandled, $s, $bRetagged, $raTag ) );
    }

    function GetKfrelEvents() { return( $this->kfrel ); }

    function GetKFRC( $raParms )
    /***************************
        raParms:
            dateAfter   = limit events to >= this date : special values TODAY, YESTERDAY
            dateBefore  = limit events to <= this date
            province    = limit events to this province
            sort        = dateUp, dateDown, province
            type        = SS, EV, VIRTUAL, ""==all
     */
    {
        $raCond = array();
        $kfParms = array();
        $nLimit = intval(@$raParms['nLimit']);

        if( ($s = @$raParms['dateAfter']) ) {
            $timeCushion = 3600 * 8;        // depending on config, date can suffers from GMT: when it's after 4pm in BC it's already tomorrow

            if( $s == "TODAY" ) {
                $s = date("Y-m-d", time()-$timeCushion );
            }
            if( $s == "YESTERDAY" ) {
                $s = date("Y-m-d", time()-3600*24-$timeCushion  );
            }

            $raCond[] = "(date_start >= '".addslashes($s)."')";
        }
        if( ($s = @$raParms['dateBefore']) ) {
            $raCond[] = "(date_start <= '".addslashes($s)."')";
        }
        if( ($s = @$raParms['province']) ) {
            $raCond[] = "(province = '".addslashes($s)."')";
        }
        if( ($s = @$raParms['type']) ) {
            $raCond[] = "(type = '".addslashes($s)."')";
        }

        $sCond = implode( " AND ", $raCond );

        switch( @$raParms['sort'] ) {
            case 'dateUp':
                $kfParms['sSortCol'] = 'date_start';
                $kfParms['bSortDown'] = false;
                break;
            case 'dateDown':
                $kfParms['sSortCol'] = 'date_start';
                $kfParms['bSortDown'] = true;
                break;
            case 'province':
                $kfParms['sSortCol'] = 'province';
                $kfParms['bSortDown'] = false;
                break;
            default:
                break;
        }


        $kfrc = $this->kfrel->CreateRecordCursor( $sCond, $kfParms );

        return( $kfrc );
    }

    function Write( $kfr )
    /*********************
        Base method - override this
     */
    {
    }

    function GetTitle( $kfr )
    {
        if( $kfr->value("type") == "SS" ) {
            $city = $kfr->value('city');

            if( $this->lang == "FR" ) {
                $title = "F&ecirc;te des semences $city";
            } else if( ($u = SEEDDateDB2Unixtime( $kfr->value("date_start") )) &&
                       ($l = date('l',$u)) )
            {
                $title = "$city Seedy $l";    // e.g. Charlottetown Seedy Sunday
            } else {
                $title = "$city Seedy Saturday";
            }

        } else {
            $title = $this->_getValue( $kfr, "title" );
        }
        return( $title );
    }

    function geocode( &$kfr )
    /************************
        Look up the latlong from the address and store it in $kfr and db
     */
    {return( false );
        $request_url = "http://maps.google.com/maps/geo?output=xml"
                      ."&key=".$this->gMapsAPIKey_www_seeds_ca
                      ."&sensor=false"
                      ."&q=".urlencode($kfr->Expand("[[location]] [[city]], [[province]] Canada"));

        if( ($xml = simplexml_load_file($request_url)) ) {
            $status = $xml->Response->Status->code;
            if( strcmp($status, "200") == 0 ) {
                // Successful geocode
                $geocode_pending = false;
                $coordinates = $xml->Response->Placemark->Point->coordinates;
                list($lng,$lat) = explode( ",", $coordinates );   // Format: Longitude, Latitude, Altitude

                $kfr->SetValue( 'latlong', "lat=".urlencode($lat)."&long=".urlencode($lng) );
                $kfr->PutDBRow();
            }
        }
    }


    protected function _getValue( $kfrEV, $field, $bEntities = true )
    /**************************************************************
        Get the English or French value, or the other one if empty
     */
    {
        $e = ($bEntities ? $kfrEV->valueEnt($field)       : $kfrEV->value($field));
        $f = ($bEntities ? $kfrEV->valueEnt($field."_fr") : $kfrEV->value($field."_fr"));
        return((($this->lang=="EN" && !empty($e)) || ($this->lang=="FR" && empty($f))) ? $e : $f);
    }


    function DrawEvent( $kfrEV )
    /***************************
        Draw the text of an event item, in either english or french

        !bPrn: format in a single div with info set out in a blockquote
        bPrn:  format with a table model with the title in first col, other info in second col
     */
    {
//        if( $kfrEV->IsEmpty('latlong') ) { $this->geocode( $kfrEV ); }

        $city     = $kfrEV->Expand( "[[city]], [[province]]" );
        $location = $kfrEV->valueEnt("location"); // ($kfrEV->value("type")=="SS" ? $t : $c);

        $title = $this->GetTitle( $kfrEV );

        switch( $kfrEV->value("type") ) {
            case 'EV':
            default:
                // show title, city, province, location as recorded
                break;

            case 'SS':
                // show city, location, title has been set to "$city Seedy Saturday"
                break;

            case 'VIRTUAL':
                // show title, don't show city, province, location
                $city = "";
                $location = "";
        }

        $date = $this->_getValue( $kfrEV, "date_alt" );
        if( empty($date) ) {
            $date = SEEDDateDB2Str( $kfrEV->value("date_start"), $this->lang );
        }

        if( $this->bPrn ) {
            // only used in unilingual mode

            // this is not the best format for EV-type events, but it works for SS

            $s .= "<DIV class='evPRNTitle'>$title</DIV>";
            $s .= "</TD><TD valign=top>";
            $s .= "<P><B>"
                 .$date . SEEDStd_StrNBSP("",6) . $kfrEV->value("time")."</B>"
                 .( !empty($location) ? ("<BR>".$location) : "")
                 ."</P>";
            $s .= $this->_drawEventDetails( $kfrEV );
            if( $kfrEV->IsEmpty("contact") ) {
                $s .= "<P>Contact: ".$kfrEV->valueEnt("contact")."</P>";
            }
            $s .= "</TD></TR>\n<TR><TD align=left valign=top>";
        } else {
            $s = "<DIV class='EV_Event'>";

            $s .= "<H3>$title</H3>"
                 //."<BLOCKQUOTE>"
                 ."<P><B>"
                 .$date .SEEDStd_StrNBSP("",10).$kfrEV->value("time")."<br/>"
                 .(!empty($location) ? ($location."<br/>") : "")
                 .(!empty($city) ? ($city."<br/>") : "")
                 ."</B></P>";
            $s .= $this->_drawEventDetails( $kfrEV );
            if( !$kfrEV->IsEmpty("contact") ) {
                $s .= "<P>Contact: "
                     .($this->oWiki ? $this->oWiki->TranslateLinksOnly($kfrEV->value("contact")) : $kfrEV->value("contact"))
                     ."</P>";
            }
            if( !$kfrEV->IsEmpty("url_more") ) {
                $s .= "<p>".($this->lang == 'FR' ? "Plus d'information" : "More information").": "
                     .$this->oTag->ProcessTags( $kfrEV->value("url_more") )
                     ."</p>";
/*
 A new More Information link

                $sUrl = $kfrEV->value("url_more");
                $s .= "<a style='text-decoration:none;' target='_blank' href='$sUrl'>"
                     ."<div class='btn btn-success'>"
                     .($this->lang == 'FR' ? "Plus d'information" : "More information")
                     ."</div>"
                     ."</a>";
*/
            }
            $s .= //"</BLOCKQUOTE>"
            "</DIV>\n";
        }

        return( $s );
    }

    private function _drawEventDetails( $kfrEV )
    /*******************************************
     */
    {
        $details = $this->_getValue( $kfrEV, "details", false );    // do not expand entities because this is allowed to contain HTML
        $details = trim($details);                                  // get rid of trailing blank lines

        if( intval(substr($kfrEV->value("date_start"),0,4)) < 2008 ) {
            // prior to 2008 we used plaintext, now use Wiki
            $s = SEEDStd_StrBR($details);
        } else {
            $details = nl2br($details);
//correct new way            $s = $this->oTag->ProcessTags( $details );

//REMOVE
            $s = ($this->oWiki ? $this->oWiki->TranslateLinksOnly($details) : $details);
        }
        return( "<P style='width:80%'>".$s."</P>" );
    }


    private function initKfrel( &$kfdb, $uid )
    /*****************************************
     */
    {
        $def =
            array( "Tables"=>array( array( "Table" => 'seeds.ev_events',
                                           "Type"  => 'Base',
                                           "Fields" => array( array("col"=>"type",        "type"=>"S"),
                                                              array("col"=>"date_start",  "type"=>"S"),
                                                              array("col"=>"date_end",    "type"=>"S"),
                                                              array("col"=>"date_alt",    "type"=>"S"),
                                                              array("col"=>"date_alt_fr", "type"=>"S"),
                                                              array("col"=>"time",        "type"=>"S"),
                                                              array("col"=>"location",    "type"=>"S"),
                                                              array("col"=>"city",        "type"=>"S"),
                                                              array("col"=>"province",    "type"=>"S"),
                                                              array("col"=>"title",       "type"=>"S"),
                                                              array("col"=>"title_fr",    "type"=>"S"),
                                                              array("col"=>"spec",        "type"=>"S"),
                                                              array("col"=>"details",     "type"=>"S"),
                                                              array("col"=>"details_fr",  "type"=>"S"),
                                                              array("col"=>"contact",     "type"=>"S"),
                                                              array("col"=>"url_more",    "type"=>"S"),
                                                              array("col"=>"latlong",     "type"=>"S"),
                                                              array("col"=>"attendance",  "type"=>"I"),
                                                              array("col"=>"notes_priv",  "type"=>"S"),
                                                              array("col"=>"vol_kMbr",    "type"=>"I"),
                                                              array("col"=>"vol_notes",   "type"=>"S"),
                                                              array("col"=>"vol_dSent",   "type"=>"S"),
                                           ) ) ) );

        $this->kfrel = new KeyFrameRelation( $kfdb, $def, $uid,
                                             array( 'logfile' => SITE_LOG_ROOT."events.log" ) );
    }
}



/* Public site display.
 *
 * This is here so drupal can see it
 */

class EV_PublicList extends EV_Events {

    function __construct( $lang, $kfdb, $uid = 0 )
    {
        parent::__construct( $kfdb, $uid, $lang );
    }


    function DrawStart()
    /*******************
     */
    {
        if( $this->bPrn ) {
            $s = "<TABLE cellpadding='10' cellspacing='10' border='0'>";
        } else {
//          $s = "<DIV>";
            $s = "";
        }
        return( $s );
    }

    function DrawEnd()
    /*****************
     */
    {
        if( $this->bPrn ) {
            $s = "</TABLE>";
        } else {
//          $s = "</DIV>";
            $s = "";
        }
        return( $s );
    }
}


function DrawEvents( KeyFrameDB $kfdb, $lang )
{
	$s = "";

    $oEv = new EV_PublicList( $lang, $kfdb, 0 );

    $s .= "<STYLE type='text/css'>"
         .".EV_Event { padding-bottom: 1em; }"
         .".EV_navbox { border: 2px solid #9a9;border-radius:5px; font-size:10pt; margin-left:2em;padding:1em; background-color:#efffef;}"
         ."</STYLE>\n";

    $pDate1 = SEEDSafeGPC_GetStrPlain("date1");             // show events >= this date
    $pDate2 = SEEDSafeGPC_GetStrPlain("date2");             // show events <= this date
    $pProv  = SEEDSafeGPC_GetStrPlain("prov");              // show events in this province
    $pSort1 = SEEDSafeGPC_GetStrPlain("sort1");
    $pPrn   = (SEEDSafeGPC_GetInt("prn") ? true : false);   // show printable page

    if( empty($pDate1) )  $pDate1 = date("Y-m-d", time() );
    if( empty($pSort1) )  $pSort1 = "d";

//    $oDP = new SEEDDateCalendar();
//    $s .= $oDP->Setup();

    /* Control box floating at right
     */
    $sCal1 = "<input type='date' name='date1' value='$pDate1'/>";
    $sCal2 = "<input type='date' name='date2' value='$pDate2'/>";

    $sCal = "<div class='EV_navbox'>"
         // in drupal, PHP_SELF is index.php so q=drupal takes us to the current page. if natively displayed, q is ignored
         ."<FORM method='post' action='".Site_path_self()."'>" //${_SERVER['PHP_SELF']}?q=events'>"
         ."<INPUT type='hidden' name='q' value='events'/>"
         ."<INPUT type='submit' value='Show Events'>"
         ."<TABLE border='0' cellpadding='2' cellspacing='0' style='font-size:10pt'>"
         ."<TR><TD>From Date</TD><TD>".$sCal1."</TD></TR>"
         ."<TR><TD>To Date</TD><TD>".$sCal2."</TD></TR>"
   //     ."<TR>".SEEDForm_TextTD( "date2", $pDate2, "To Date", 10 )."</TR>"
         ."<TR><TD valign='top'>Province</TD><TD valign='top'>".SelectProvince( "prov", $pProv, array("lang"=>$oEv->lang, "sAttrs"=>"onChange='submit();'", "bAll"=>1 ))."</TD></TR>"

         ."<TR><TD valign='top'>Sort by</TD><TD valign='top'>"
         ."<SELECT name='sort1' onChange='submit();'>"
         ."<OPTION value='d'".($pSort1=='d' ? " SELECTED" : "").">Date</OPTION>"
         ."<OPTION value='p'".($pSort1=='p' ? " SELECTED" : "").">Province</OPTION></SELECT></TD></TR>"
         ."</TABLE>"
         ."</FORM></DIV>";


    $sNotices =
              "<div style='margin:2em;border:1px solid #99a;background-color:#efffef;border-radius:5px;padding:1em;text-align:center;'>"
             ."<a href='http://www.seeds.ca/Seedy-Saturday' target='_blank'>"
             ."Organizing a Seedy Saturday? <br/> Volunteering at a Seedy Saturday?<br/> Here's everything you want to know!</a>"
             ."</div>"
             ."<div style='margin:2em;border:1px solid #99a;background-color:#efffef;border-radius:5px;padding:1em;text-align:center;'>"
                 ."<p><strong>Do you have leftover seeds from your seed swap or seed library?</strong></p>"
                 ."<p>We can help circulate them to our representatives who will put them to good use at other community seed swaps. Every week during the winter, we mail books and table-top materials to Seedy Saturdays and Seedy Sundays all across the country, so we can easily include your leftover seeds in our packages. Our volunteers will make sure the seeds get to their local seed swap tables and seed libraries, so you'll know that they won't go to waste.</p><p>Mail your leftover seeds to:</p><p><strong>Seeds of Diversity Canada<br/>#1-12 Dupont St West<br/>Waterloo ON N2L 2X6</strong></p>"
             ."</div>";

    if( $oEv->lang == "EN" ) {
        $s .= ""
//             ."<H2>Seedy Events</H2>"  // "<H2>Seedy Saturdays and Seedy Sundays</H2>"
             ."<P>This is a listing of events for our members and others interested in plant biodiversity, heritage gardening,"
             ." organic gardening, and seeds. If you know of any other upcoming events, please send the information to "
             //.SEEDCore_EmailAddress( "office", "seeds.ca" )
             ."<a href='mailto:office@seeds.ca'>office@seeds.ca</a>"
             ."</P>";


    } else {
        $s .= "" // "<H2>&Eacute;v&eacute;nements</H2>"  // "<H2>F&ecirc;tes des Semences</H2>"
             ."<P>Voici des &eacute;v&eacute;nements d'int&eacute;r&ecirc;t pour nos membres ou autres personnes pr&eacute;occup&eacute;es par la biodiversit&eacute;, la culture"
             ." de vari&eacute;t&eacute;s du patrimoine, la culture biologique ainsi que les semences."
             ." Si vous savez d'autres &eacute;v&eacute;nements, SVP courriel "
             //.SEEDCore_EmailAddress("courriel", "semences.ca")
             ."<a href='mailto:courriel@semences.ca'>courriel@semences.ca</a>"
             ."</P>";
    }


    $s .= "<div style='float:right;width:35%'>"
         .$sCal
         .$sNotices
         ."</div>";

/*
?>
    <script src="http://maps.google.com/maps?file=api&v=2&key=ABQIAAAAZH7Z2EuHYHx72zrJHRiVphQ8I1Ru0X2yQKG6mkY9VLdWFUIWshQUkdimuessOVdYcpo36SexnaFD3g"
            type="text/javascript"></script>

    <script type="text/javascript">
    //<![CDATA[

    var iconBlue = new GIcon();
    iconBlue.image = 'http://labs.google.com/ridefinder/images/mm_20_blue.png';
    iconBlue.shadow = 'http://labs.google.com/ridefinder/images/mm_20_shadow.png';
    iconBlue.iconSize = new GSize(12, 20);
    iconBlue.shadowSize = new GSize(22, 20);
    iconBlue.iconAnchor = new GPoint(6, 20);
    iconBlue.infoWindowAnchor = new GPoint(5, 1);

    var iconRed = new GIcon();
    iconRed.image = 'http://labs.google.com/ridefinder/images/mm_20_red.png';
    iconRed.shadow = 'http://labs.google.com/ridefinder/images/mm_20_shadow.png';
    iconRed.iconSize = new GSize(12, 20);
    iconRed.shadowSize = new GSize(22, 20);
    iconRed.iconAnchor = new GPoint(6, 20);
    iconRed.infoWindowAnchor = new GPoint(5, 1);

    var customIcons = [];
    customIcons["blue"] = iconBlue;
    customIcons["red"] = iconRed;

    function load() {
      if (GBrowserIsCompatible()) {
        var map = new GMap2(document.getElementById("map"));
        map.addControl(new GSmallMapControl());
        //map.addControl(new GMapTypeControl());
        map.setCenter(new GLatLng(58.447733,-93.691406), 3);

        GDownloadUrl("ev_xml.php", function(data) {
          var xml = GXml.parse(data);
          var markers = xml.documentElement.getElementsByTagName("marker");
          for (var i = 0; i < markers.length; i++) {
            var sDate = markers[i].getAttribute("date");
            var sAddress = markers[i].getAttribute("address");
            var type = markers[i].getAttribute("type");
            var point = new GLatLng(parseFloat(markers[i].getAttribute("lat")),
                                    parseFloat(markers[i].getAttribute("lng")));
            var marker = createMarker(point, sDate, sAddress, type);
            map.addOverlay(marker);
          }
        });
      }
    }

    function createMarker(point, sDate, sAddress, type) {
      var marker = new GMarker(point, customIcons[type]);
      var html = "<b>" + sAddress + "</b> <br/>" + sDate;
      GEvent.addListener(marker, 'click', function() {
        marker.openInfoWindowHtml(html);
      });
      return marker;
    }
    //]]>


  </script>
<?php

    echo "<div id='map' style='width: 640px; height: 480px'></div>";
?>
 <script>
   load();
  </script>
<?
*/

    /* Printable-version link
     */
    if( !$pPrn ) {
        $l = "prn=1"
            .(!empty($pDate1) ? "&date1=$pDate1" : "")
            .(!empty($pDate2) ? "&date2=$pDate2" : "")
            .(!empty($pProv)  ? "&prov=$pProv"   : "")
            .(!empty($pSort1) ? "&sort1=$pSort1" : "");

//      $s .= "<P><A HREF='${_SERVER['PHP_SELF']}?$l' target='_blank'>Printable version</A></P>";
    }


    $sCond = "(date_start >= '".addslashes($pDate1)."')"
            .(empty($pDate2) ? "" : " AND (date_start <= '".addslashes($pDate2)."')")
            .(empty($pProv)  ? "" : " AND (province = '".addslashes($pProv)."')");
    $raParms = array();
    if( $pSort1 == "d" ) {
        $raParms['sSortCol'] = 'date_start';
        $raParms['bSortDown'] = 0;
    } else if( $pSort1 == "p" ) {
        $raParms['sSortCol'] = 'province,city';
        $raParms['bSortDown'] = 0;
    }

    $oEv->bPrn = $pPrn;

    // show all the super events
    $s .= "<DIV class='EV_List'>";
    $s .= $oEv->DrawStart();
    if( ($kfr = $oEv->kfrel->CreateRecordCursor( $sCond." AND spec like '% top %'", $raParms )) ) {
        while( $kfr->CursorFetch() ) {
            $s .= "<div style='width:80%;border:1px solid #666;border-radius:10px;padding:10px;background-color:#eee;'>"
                 .$oEv->DrawEvent( $kfr )
                 ."</div>";
        }
    }
    $s .= $oEv->DrawEnd();
    $s .= "</DIV>";

    // show all the regular events
    $s .= "<DIV class='EV_List'>";
    $s .= $oEv->DrawStart();
    if( ($kfr = $oEv->kfrel->CreateRecordCursor( $sCond." AND spec not like '% top %'", $raParms )) ) {
        while( $kfr->CursorFetch() ) {
            $s .= $oEv->DrawEvent( $kfr );
        }
    }
    $s .= $oEv->DrawEnd();
    $s .= "</DIV>";



/*
 *    echo "<DIV>";
 *
 *    $bFirst = true;
 *]/  $kfr = $kfrel->CreateRecordCursor( "b$lang<>0", array( "sSortCol"=>"_key","bSortDown"=>1 ) );
 *    while( $kfr->CursorFetch() ) {
 *        $cl = ($bFirst ? "class='EVPageList'" : "");
 *        echo "<P $cl><A HREF='evpage.php?lang=$lang&p=".$kfr->Key()."'>". ($lang=="EN" ? $kfr->value("name") : $kfr->value("name_fr")) ."</A></FONT></P>";
 *        $bFirst = false;
 *    }
 *
 *    echo "</DIV>";
 */

/*
<center>
<br>
<br>
<br>
<hr width="40%">
<br>
<table><tr><td>
<a href="../events/ev_0205.htm">May - September 2002</a><br>
<a href="../events/ev_0109.htm">December 2001 - February 2002</a><br>
<a href="../events/ev_0105.htm">May - September 2001</a><br>
<a href="../events/ev_0005.htm">June - September 2000</a><br>
<a href="../events/ev_0001.htm">January - June 2000</a><br>
<a href="../events/ev_9909.htm">October 1999 - February 2000</a><br>
<a href="../events/ev_9905.htm">May - September 1999</a><br>
<a href="../events/ev_9901.htm">February - May 1999</a><br>
<a href="../events/ev_9809.htm">September 1998 - January 1999</a><br>
<br>
<a href="../events/ss_0201.htm">Seedy Saturdays - 2002</a><br>
<a href="../events/ss_0101.htm">Seedy Saturdays - 2001</a><br>
<a href="../events/ss_0001.htm">Seedy Saturdays - 2000</a><br>
<a href="../events/ss_9901.htm">Seedy Saturdays - 1999</a><br>
</td></tr></table>
</center>


<table><tr><td>
<a href="../evenements/ev_0301.htm">�v�nements Janvier - Avril 2003</a><br>
<a href="../evenements/ev_0205.htm">�v�nements Mai - Septembre 2002</a><br>
<a href="../evenements/ss_0201.htm">Les F�tes des Semences - 2002</a>
</td></tr></table>

*/


    return( $s );
}




function Events_Setup( $oSetup, &$sReport, $bCreate = false )
/************************************************************
    Test whether the tables exist.
    bCreate: create the tables and insert initial data if they don't exist.

    Return true if exists (or create is successful if bCreate); return a text report in sReport

    N.B. $oSetup is a SEEDSetup.  This file doesn't include SEEDSetup.php because the setup code is very rarely used.
         Instead, the code that calls this function knows about SEEDSetup.
 */
{
    return( $oSetup->SetupTable( "ev_events", SEED_DB_TABLE_EV_EVENTS, $bCreate, $sReport ) );
}


?>
