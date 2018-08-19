<?php
define( "SITEROOT", "../../" );
include( SITEROOT."site.php" );
include_once( SEEDCOMMON."googleAPI.php" );
include( "_maps.php");

list($kfdb) = SiteStart();

$lang = site_define_lang();

$oMap = new BautaMap( $kfdb );

$oG = new SEEDSGoogleMaps();

$charset = "utf-8";

header( "Content-Type:text/html; charset=$charset" );

$sheetname = SEEDInput_Str( 'sheet', '2017' );
$year = null;

?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset='<?php echo $charset; ?>'>

    <style type="text/css">
      html, body, #map-canvas { height: 100%; margin: 0; padding: 0;}
    </style>
    <?php echo $oG->GetScriptForBrowser( $lang ); ?>
    <script type="text/javascript">
        var map;

        function initialize() {
            var mapOptions = {
                    center: { lat: 58.7481691, lng: -94.1181313},
                    zoom: 4
                };
            map = new google.maps.Map( document.getElementById('map-canvas'), mapOptions );

            /*
            var m = new google.maps.Marker( { position: { lat: 53.463614, lng: -113.490234 },
                                      map: map,
                                      icon: 'http://www.seeds.ca/img/dot1.gif',
                                      title: "Alberta Organic" } );
            var iw = new google.maps.InfoWindow({ content: "Alberta Organic" } );
            google.maps.event.addListener( m, 'click', function() { iw.open(map,m); } );
            */
            /*
            geocoder = new google.maps.Geocoder();
            geocoder.geocode( { 'address': "Winnipeg MB" },
                              function(results, status) {
                                  if( status == google.maps.GeocoderStatus.OK ) {
                                      var marker = new google.maps.Marker({ map: map,
                                                                            position: results[0].geometry.location
                                                                          });
                                  }
                              });
            */

<?php
    $raMarkers = $oMap->GetMarkers( $sheetname );
    foreach( $raMarkers as $raM ) {
        if( !$raM['latitude'] ) continue;

        // exclude these cats because they're reported separately
        if( in_array( $raM['cat'], array('partner') ) ) continue;

//if( !in_array( $raM['cat'], array('ar-ppb','trials','hub','pa') ) ) continue;

        $p = " { title: \"".$raM['name']."\","
            ."  position: {lat: ".$raM['latitude'].", lng: ".$raM['longitude']." }";
        if( @$raM['icon'] ) {
            $p .= ", icon: '".$raM['icon']."'";
        }
        // Put the hubs in front of everything else
        if( @$raM['cat'] == 'hub' ) {
            $p .= ", zIndex: 10000";
        }
        if( @$raM['cat'] == 'sff' ) {
            $p .= ", zIndex: 9000";
        }
        $p .= "}";

        echo "placeMarker( $p );\n";
    }

?>


        }

        function placeMarker( oMarkerOpts ) {
            oMarkerOpts['map'] = map;

            var m = new google.maps.Marker( oMarkerOpts );
            var iw = new google.maps.InfoWindow({ content: oMarkerOpts['title'] } );
            google.maps.event.addListener( m, 'click', function() { iw.open(map,m); } );
            //map.panTo(location);
        }

        google.maps.event.addDomListener(window, 'load', initialize);

    </script>
  </head>
  <body>
  <?php

//echo $oMap->DrawMap( array('maptype'=>'roadmap'));

//echo $oMap->DrawMap( array('maptype'=>'satellite'));

?>
  <script>
  function hideLegend()
  {
      document.getElementById('map-legend').style.display = 'none';
  }
  </script>

  <style>
  .legend-table td { padding-bottom:5px; }
  </style>
  <div class='legend' style='width:120px;position:absolute;right:50px;top:40px;border:1px solid black;z-index:2;padding:10px;font-family:arial,helvetica,sans serif;font-size:12px;background-color:#f8f8f8;border-radius:5px' id='map-legend' onclick='hideLegend();'>
    <table class='legend-table' border='0' cellpadding='0' cellspacing='0'>
      <tr><td><img style='vertical-align:middle;' src='http://www.seeds.ca/i/img/map/dot-red.png'/></td><td style='padding-left:10px;'><?php echo $lang=='EN' ? "Regional hub" : "Carrefour régional"; ?></td></tr>
      <tr><td><img style='vertical-align:middle;' src='http://www.seeds.ca/i/img/map/star-yellow.png'/></td><td style='padding-left:10px;'><?php echo $lang=='EN' ? "Training" : "Formation"; ?></td></tr>
      <tr><td><img style='vertical-align:middle;' src='http://www.seeds.ca/i/img/map/star-purple.png'/></td><td style='padding-left:10px;'><?php echo $lang=='EN' ? "Research" : "Recherche"; ?></td></tr>
      <tr><td><img style='vertical-align:middle;' src='http://www.seeds.ca/i/img/map/star-green.png'/></td><td style='padding-left:10px;'><?php echo $lang=='EN' ? "Trials" : "Essais"; ?></td></tr>
      <tr><td><img style='vertical-align:middle;' src='http://www.seeds.ca/i/img/map/star-blue.png'/></td><td style='padding-left:10px;'><?php echo $lang=='EN' ? "Grants" : "Subventions"; ?></td></tr>
      <tr><td><img style='vertical-align:middle;' src='http://www.seeds.ca/i/img/map/star-orange.png'/></td><td style='padding-left:10px;'><?php echo $lang=='EN' ? "Seed Collections" : "Collections de semences"; ?></td></tr>
    </table>
  </div>
  <div id="map-canvas"></div>
  </body>
</html>

?>
