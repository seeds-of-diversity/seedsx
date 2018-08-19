/* SEEDIFrame
 *
 * Copyright 2014 Seeds of Diversity Canada
 *
 * Manage an iframe that gives a window to a Seeds of Diversity app on another web site.
 *
 * The other site should have:
       <div>
       <iframe id="SEEDIFrame" style="" src="http://www.seeds.ca/bauta/explorer.php"></iframe>
       </div>
       <script type="text/javascript" src="http://www.seeds.ca/w/seedcommon/SEEDIFrame/SEEDIFrame.js"></script>
 */

function SEEDIFrameResize($) { 
    var w = $(window).width() - 300;
    var top = $('#SEEDIFrame').offset().top;
    var h = $(window).height() - top;

    // Minimal size (because the subtractions above can lead to negative sizes)
    if( w < 100 ) w = 100;
    if( h < 100 ) h = 100;
    
    // Parameters for min/max sizes
    if( typeof nSEEDIFrameMinWidth  != 'undefined' &&  w < nSEEDIFrameMinWidth )   w = nSEEDIFrameMinWidth;
    if( typeof nSEEDIFrameMaxWidth  != 'undefined' &&  w > nSEEDIFrameMaxWidth )   w = nSEEDIFrameMaxWidth;
    if( typeof nSEEDIFrameMinHeight != 'undefined' &&  h < nSEEDIFrameMinHeight )  h = nSEEDIFrameMinHeight;
    if( typeof nSEEDIFrameMaxHeight != 'undefined' &&  h > nSEEDIFrameMaxHeight )  h = nSEEDIFrameMaxHeight;

    //alert( "w="+w+" h="+h );
    $('#SEEDIFrame').width( w ); 
    $('#SEEDIFrame').height( h ); 
}

jQuery(document).ready( function($) { SEEDIFrameResize($); } );
jQuery(window).resize( function() { SEEDIFrameResize(jQuery); } );


