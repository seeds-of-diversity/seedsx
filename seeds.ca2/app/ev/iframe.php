<?php

if( $_REQUEST['mode'] == 'map' ) {
?>
<html>
  <head>
    <title>JQVMap - World Map</title>
    <link href="dist/jqvmap.css" media="screen" rel="stylesheet" type="text/css">

    <script type="text/javascript" src="https://code.jquery.com/jquery-1.11.3.min.js"></script>
    <script type="text/javascript" src="dist/jquery.vmap.js"></script>
    <script src="dist/maps/jquery.vmap.canada.js"></script>

    <script type="text/javascript">
    jQuery(document).ready(function() {
      jQuery('#vmap').vectorMap({
          map: 'canada_en',
          backgroundColor: null,
          color: '#5c882d', //'#c23616',
          hoverColor: '#999999',
          enableZoom: false,
          showTooltip: true,
          onRegionClick: function(element, code, region)
          {
              jQuery('#selectedRegion').html('Seedy Events in '+region);
              jQuery('#texthere',window.parent.document).html('Seedy Events in '+region);
//              var message = 'You clicked "'
//                  + region
//                  + '" which has the code: '
//                  + code.toUpperCase();
//
//              alert(message);
          }
      });
    });
    </script>
  </head>
  <body>
     <div id="vmap" style="width: 600px; height: 400px;"></div>
     <h3 id="selectedRegion"></h3>
  </body>
</html>



<?php
    //echo "<div style='width:100%'><img src='map-canada.webp' style='width:100%'/></div>";
} else {
    echo "<div id='texthere'></div>";
    echo "<table><tr><th>Monday</th><th>Tuesday</th><th>Wednesday</th><th>Thursday</th><th>Friday</th><th>Saturday</th><th>Sunday</th></tr></table>";
    echo "<p>This is not a real calendar yet</p>";
    echo "<div class='row'><div class='col-md-6'>column 1</div><div class='col-md-6'>column 2</div></div>";
    echo "<div class='wp-block-columns'>
              <div class='wp-block-column' style='flex-basis:33.33%'>column 1</div>
              <div class='wp-block-column' style='flex-basis:66.66%'>column 2</div>
          </div>";
}
