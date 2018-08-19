/* SEEDSlider
 *
 * Copyright (c) 2013-2014 Seeds of Diversity Canada
 *
 * Manage a set of nine sliding boxes with content retrieved by AJAX.
 *
 * 1) Initialization
 *        - Fetch qcode=ini which returns html for main box, blanks for surrounding boxes
 *
 * 2) When a qcode is initialized in the middle box
 *        If it is in the QCodesList and raQCodes is defined
 *            - use "get" to fetch any qcodes from raQCodes that are not in QCodesList
 *            - set BoxQCodes as per raQCodes
 *            - SEEDSlider_Position draws the boxes
 *
 *        If it is not in the QCodesList
 *            - use "get9" to fetch all qcodes associated with the central qcode, including that central qcode
 *            - set BoxQCodes as per raQCodes fetched for the central qcode
 *            - SEEDSlider_Position draws the boxes
 *
 * 3) When a box is clicked
 *        If raQCodes is defined for that box's qcode
 *            - use "get" to fetch any qcodes from raQCodes that are not in QCodesList
 *            - set BoxQCodes as per raQCodes
 *            - SEEDSlider_Move animates the boxes and calls SEEDSlider_Position to draw the boxes 
 */

$(document).ready(function() { SEEDSlider_Init(); });

/* A dummy array just for interating through the letters A-I
 */
var oSEEDSlider_Boxes = { A:0, B:0, C:0, D:0, E:0, F:0, G:0, H:0, I:0 };


/* QCodesList:
       { qcode1 : { 'raQCodes'  : { A:q1, B:q2, C:q3, D:q4, F:q5, G:q6, H:q7, I:q8 },
                    'html'      : full html when this qcode is in box E,
                    'htmlsmall' : short html when this qcode is in a small box
                  },
         qcode2 : { ... }
       }  
 */
var oSEEDSlider_QCodesList = { 0 : { "raQCodes" : { A:0, B:0, C:0, D:0, E:0, F:0, G:0, H:0, I:0 },
                                     "html"      : "",
                                     "htmlsmall" : ""
                                   }
                             };

/* Store the current qcodes for each box
 */
var oSEEDSlider_BoxQCodes = { A:0, B:0, C:0, D:0, E:0, F:0, G:0, H:0, I:0 };


/* Store the absolute positions of the nine boxes, drawn by SEEDSlider_Position and animated by SEEDSlider_Move
 */
var oSEEDSlider_positions = {
               'A': { top: 0, left: 0, width: 0, height: 0 },
               'B': { top: 0, left: 0, width: 0, height: 0 },
               'C': { top: 0, left: 0, width: 0, height: 0 },
               'D': { top: 0, left: 0, width: 0, height: 0 },
               'E': { top: 0, left: 0, width: 0, height: 0 },
               'F': { top: 0, left: 0, width: 0, height: 0 },
               'G': { top: 0, left: 0, width: 0, height: 0 },
               'H': { top: 0, left: 0, width: 0, height: 0 },
               'I': { top: 0, left: 0, width: 0, height: 0 }
};


function SEEDSlider_Init()
/*************************
    Create the nine boxes and set up handlers
 */
{
    if( typeof sSEEDSlider_QUrl == 'undefined' )  alert( "sSEEDSlider_QUrl is not defined" );
    if( typeof sSEEDSlider_Lang == 'undefined' )  sSEEDSlider_Lang = "EN";

    if( typeof $("#SEEDSlider_container") == 'undefined' ||
        typeof $("#SEEDSlider_container").offset() == 'undefined' ||
        typeof $("#SEEDSlider_container").offset().top == 'undefined' ) 
    {
        alert( "#SEEDSlider_container element is not defined" );
    }

    var sInitDivs = "<div id='SEEDSlider_containerInner'>"
                  + "<div id='SEEDSlider_A' class='SEEDSlider_boxsm'><div class='SEEDSlider_boxcontent'></div></div>"
                  + "<div id='SEEDSlider_B' class='SEEDSlider_boxsm'><div class='SEEDSlider_boxcontent'></div></div>"
                  + "<div id='SEEDSlider_C' class='SEEDSlider_boxsm'><div class='SEEDSlider_boxcontent'></div></div>"
                  + "<div id='SEEDSlider_D' class='SEEDSlider_boxsm'><div class='SEEDSlider_boxcontent'></div></div>"
                  + "<div id='SEEDSlider_E' class='SEEDSlider_boxlg'><div class='SEEDSlider_boxcontent'></div></div>"
                  + "<div id='SEEDSlider_F' class='SEEDSlider_boxsm'><div class='SEEDSlider_boxcontent'></div></div>"
                  + "<div id='SEEDSlider_G' class='SEEDSlider_boxsm'><div class='SEEDSlider_boxcontent'></div></div>"
                  + "<div id='SEEDSlider_H' class='SEEDSlider_boxsm'><div class='SEEDSlider_boxcontent'></div></div>"
                  + "<div id='SEEDSlider_I' class='SEEDSlider_boxsm'><div class='SEEDSlider_boxcontent'></div></div>"
                  + "</div>";

    $("#SEEDSlider_container").html( sInitDivs );


    /* Handlers
     */
    $(window).resize( function() { SEEDSlider_Position(); } );

    $("#SEEDSlider_A").click(function() { SEEDSlider_click( "A" ); });
    $("#SEEDSlider_B").click(function() { SEEDSlider_click( "B" ); });
    $("#SEEDSlider_C").click(function() { SEEDSlider_click( "C" ); });
    $("#SEEDSlider_D").click(function() { SEEDSlider_click( "D" ); });
  //$("#SEEDSlider_E").click(function() { SEEDSlider_click( "E" ); });
    $("#SEEDSlider_F").click(function() { SEEDSlider_click( "F" ); });
    $("#SEEDSlider_G").click(function() { SEEDSlider_click( "G" ); });
    $("#SEEDSlider_H").click(function() { SEEDSlider_click( "H" ); });
    $("#SEEDSlider_I").click(function() { SEEDSlider_click( "I" ); });


    /* Debug
     */
    var bDebug = 0;
    if( bDebug ) {
        $("#SEEDSlider_A .SEEDSlider_boxcontent").html( "A" );
        $("#SEEDSlider_B .SEEDSlider_boxcontent").html( "B" );
        $("#SEEDSlider_C .SEEDSlider_boxcontent").html( "C" );
        $("#SEEDSlider_D .SEEDSlider_boxcontent").html( "D" );
        $("#SEEDSlider_E .SEEDSlider_boxcontent").html( "E" );
        $("#SEEDSlider_F .SEEDSlider_boxcontent").html( "F" );
        $("#SEEDSlider_G .SEEDSlider_boxcontent").html( "G" );
        $("#SEEDSlider_H .SEEDSlider_boxcontent").html( "H" );
        $("#SEEDSlider_I .SEEDSlider_boxcontent").html( "I" );
        $("#SEEDSlider_J .SEEDSlider_boxcontent").html( "J" );

        oSEEDSlider_BoxQCodes["A"] = 1;
        oSEEDSlider_BoxQCodes["B"] = 2;
        oSEEDSlider_BoxQCodes["C"] = 3;
        SEEDSlider_Position();
    } else {
        // Start with the central position containing qcode=ini
        SEEDSlider_Start( "init" );
    }
}

function SEEDSlider_Start( qcode )
/*********************************
    Begin with qcode at the central position
 */
{
    if( SEEDSlider_Fetch( qcode ) ) {    // make sure all required data is loaded
        SEEDSlider_SetQCodes( qcode );   // put the right qcodes in each box
        SEEDSlider_Position();           // draw the boxes
    }
}

function SEEDSlider_Search( sSearch )
/************************************
    Begin a search for the given string
 */
{
    //sSearch = encodeURIComponent( sSearch );
    if( sSearch == "" || typeof sSearch == 'undefined' ) return;

    SEEDSlider_Start( "qs__"+sSearch );
}

function SEEDSlider_SetQCodes( qcode )
/*************************************
    For the given qcode in the central position, set qcodes for all boxes. Assume the required data is fetched.
 */
{
    if( typeof oSEEDSlider_QCodesList[qcode]['raQCodes'] == 'undefined' )  return( false );
    raQCodes = oSEEDSlider_QCodesList[qcode]['raQCodes'];

    for( var box in oSEEDSlider_Boxes ) {
        if( typeof raQCodes[box] == 'undefined' ) {
            oSEEDSlider_BoxQCodes[box] = 0;
        } else {
            oSEEDSlider_BoxQCodes[box] = raQCodes[box];
        }
    }
    oSEEDSlider_BoxQCodes['E'] = qcode;

    return( true );
}

function SEEDSlider_Position()
/*****************************
   Position the nine boxes within the window
 */
{
    var winWidth = $(window).width();
    var winHeight = $(window).height();
    var topContainer = $("#SEEDSlider_container").offset().top;
    
    // leave a top margin equal to the top position of SEEDSlider_container
    if( topContainer ) winHeight -= topContainer;
    
    var square = (winWidth < winHeight) ? winWidth : winHeight;

    // allow 20px padding all around
    square -= 40;
    var sliderWidth = winWidth - 40;
    var sliderHeight = winHeight - 40;
    
    // square = 2a + 2s + e
    // a is the width of a small box
    // e is the width of the large box
    // s is the spacing
//    var minA = 50;
//    var minE = 200;
//    var minS = 12;
    
    
    // attempt 1: a = e/4; s = a/4; all sized to square 
//    var a = (square * 2)/13;
//    var s = a / 4;
//    var e = a * 4;
    
//    if( a < minA ) alert( "a is too small" );
//    if( e < minE ) alert( "e is too small" );
    
//    var pos1 = a + s + e/2 - a/2;
//    var pos2 = a + s + e + s;

    
    /* Compute the metrics for width aw = ew/4; sw = aw/4; all sized to sliderWidth;
     */
    var aw = (sliderWidth * 2)/13;
    var sw = aw / 4;
    var ew = aw * 4;
    var posw1 = aw + sw + ew/2 - aw/2;
    var posw2 = aw + sw + ew + sw;
    
    /* Compute the metrics for height ah = eh/4; sh = ah/4; all sized to sliderHeight;
     */
    var ah = (sliderHeight * 2)/13;
    var sh = ah / 4;
    var eh = ah * 4;
    var posh1 = ah + sh + eh/2 - ah/2;
    var posh2 = ah + sh + eh + sh;
    
    

    /* Store the new positions, and set css to them.
     * The animation code will use the stored positions to move boxes to neighbouring positions.
     */
//TODO: display:none if the content is blank or qcode=0 and not E
    oSEEDSlider_positions["A"] = { top:0,       left:0,       width:aw, height:ah, display:'block' };  $("#SEEDSlider_A").css(oSEEDSlider_positions["A"]);
    oSEEDSlider_positions["B"] = { top:0,       left:posw1,   width:aw, height:ah, display:'block' };  $("#SEEDSlider_B").css(oSEEDSlider_positions["B"]);
    oSEEDSlider_positions["C"] = { top:0,       left:posw2,   width:aw, height:ah, display:'block' };  $("#SEEDSlider_C").css(oSEEDSlider_positions["C"]);
    oSEEDSlider_positions["D"] = { top:posh1,   left:0,       width:aw, height:ah, display:'block' };  $("#SEEDSlider_D").css(oSEEDSlider_positions["D"]);
    oSEEDSlider_positions["E"] = { top:ah + sh, left:aw + sw, width:ew, height:eh, display:'block' };  $("#SEEDSlider_E").css(oSEEDSlider_positions["E"]);
    oSEEDSlider_positions["F"] = { top:posh1,   left:posw2,   width:aw, height:ah, display:'block' };  $("#SEEDSlider_F").css(oSEEDSlider_positions["F"]);
    oSEEDSlider_positions["G"] = { top:posh2,   left:0,       width:aw, height:ah, display:'block' };  $("#SEEDSlider_G").css(oSEEDSlider_positions["G"]);
    oSEEDSlider_positions["H"] = { top:posh2,   left:posw1,   width:aw, height:ah, display:'block' };  $("#SEEDSlider_H").css(oSEEDSlider_positions["H"]);
    oSEEDSlider_positions["I"] = { top:posh2,   left:posw2,   width:aw, height:ah, display:'block' };  $("#SEEDSlider_I").css(oSEEDSlider_positions["I"]);
     
    
    //$("#SEEDSlider_A").css({ top:0,     left:0,     width:a, height:a });
    //$("#SEEDSlider_B").css({ top:0,     left:pos1,  width:a, height:a });
    //$("#SEEDSlider_C").css({ top:0,     left:pos2,  width:a, height:a });
    //$("#SEEDSlider_D").css({ top:pos1,  left:0,     width:a, height:a });
    //$("#SEEDSlider_E").css({ top:a + s, left:a + s, width:e, height:e });
    //$("#SEEDSlider_F").css({ top:pos1,  left:pos2,  width:a, height:a });
    //$("#SEEDSlider_G").css({ top:pos2,  left:0,     width:a, height:a });
    //$("#SEEDSlider_H").css({ top:pos2,  left:pos1,  width:a, height:a });
    //$("#SEEDSlider_I").css({ top:pos2,  left:pos2,  width:a, height:a });

    $("#SEEDSlider_containerInner").css({ width:sliderWidth, height:sliderHeight });

    // Make sure at least 3 rows of text fit in the boxsm.
    // Default boxsm font is 14px (from Bootstrap), and line-height is usually 1.5
    // Expecting our css to use padding:5px so subtract 10
    if( ah - 10 < 3 * 14*1.5 ) {
        $(".SEEDSlider_boxsm .SEEDSlider_boxcontent").css({ 'font-size':(ah-10)/3/1.5 });
    } else {
        // when you slowly increase the window height the font increases back to the threshold, 
        // but maximizing the screen from a small font keeps the small font 
        $(".SEEDSlider_boxsm .SEEDSlider_boxcontent").css({ 'font-size':14 });
    }

    // Draw the html/htmlsmall in each box
    for(var box in oSEEDSlider_BoxQCodes) {
        qcode = oSEEDSlider_BoxQCodes[box];
        
        if( typeof oSEEDSlider_QCodesList[qcode] == 'undefined' ) {
            $("#SEEDSlider_"+box+" .SEEDSlider_boxcontent").html("");
        } else {
            o = oSEEDSlider_QCodesList[qcode];
            if( box == 'E' ) {
                $("#SEEDSlider_"+box+" .SEEDSlider_boxcontent").html(o['html']);
            } else {
                $("#SEEDSlider_"+box+" .SEEDSlider_boxcontent").html(o['htmlsmall']);
                if( o['htmlsmall'] == "" ) {
                    $("#SEEDSlider_"+box+" .SEEDSlider_boxcontent").removeClass( "SEEDSlider_boxhastext" );
                } else {
                    $("#SEEDSlider_"+box+" .SEEDSlider_boxcontent").addClass( "SEEDSlider_boxhastext" );
                }
            }
        }
    }
}

function SEEDSlider_click( box )
/*******************************
 */
{
    if( box == "E" ) return;

    /* User clicked on a box (not E)
     *
     * Fetch the required data for incoming boxes
     * Animate boxes as the clicked box moves to E
     * Put E back where it belongs with its new html
     * Put the rest of the boxes back where they belong with their new htmlsmall
     */

    // don't respond to click if the box is an ini
    qcode = oSEEDSlider_BoxQCodes[box];
    if( qcode.substring(0,1) == 'i' ) {
        return;
    }

    if( SEEDSlider_FetchBox( box ) ) {  // make sure the 8 boxes around 'box' have their data
        SEEDSlider_Move( box );         // animate 'box' moving to the center and update qcodes
        //SEEDSlider_Position();        // redraw everything -- this is done by promise in SEEDSlider_Move
    }
}

function SEEDSlider_FetchBox( box )
/**********************************
    Make sure nine boxes have their data if 'box' were in the middle
 */
{
    // get the qcode of the selected box
    qcode = oSEEDSlider_BoxQCodes[box];
    if( qcode == 0 )  return( false );

    return( SEEDSlider_Fetch( qcode ) );
}

function SEEDSlider_Fetch( qcode )
/*********************************
    Make sure nine boxes have their data if the middle box has qcode
 */
{
    var sQCmd = "";

    o = oSEEDSlider_QCodesList[qcode];
    if( (typeof o != 'undefined') && (typeof o['raQCodes'] != 'undefined') ) {
        /* The central qcode is already loaded.
         * If all dependent qcodes are already loaded, return true
         * Otherwise fetch the qcodes that aren't loaded yet
         */
        var bFullyLoaded = true;
        var raLoadQCodes = Array();
        for( var box in o['raQCodes'] ) {
            q = o['raQCodes'][box];
            if( q != 0 ) {
                if( typeof oSEEDSlider_QCodesList[q]['htmlsmall'] == 'undefined' ) {  // test if q is loaded
                    bFullyLoaded = false;
                    raLoadQCodes = q;
                }
            }
        }
        if( bFullyLoaded ) {
            //alert( "Fully loaded" );
            return( true );
        }

        if( !raLoadQCodes.length ) {
            //alert( "Not sure why raLoadQCodes.length is zero" );
            return( true ); 
        }

        // Not all dependent qcodes are loaded. Fetch what's still needed.
        // Shouldn't need to be urlencoded, since only qs__ codes contain user chars and those have no neighbours
        sQCmd = "get|SEEDSlider=1&qcodes="+raLoadQCodes.join(',');

    } else {
        /* The central qcode is not loaded. Use get9 to fetch it, and all dependents.
         */
        // urlencoding only necessary for qs__ codes
        sQCmd = "get9|SEEDSlider=1&qcode="+encodeURIComponent( qcode );
    }

/*    
    // don't bother fetching the data for qcodes that are already loaded
    sExclude = "";
    for(var x in oSEEDSlider_BoxQCodes ) {
        q = oSEEDSlider_BoxQCodes[x];
        if( typeof oSEEDSlider_QCodesList[q]['html'] != 'undefined' ) {
            sExclude += x;
        }
    }
//    alert(sExclude);
*/


//    q = "get|qCode="+qcode+"&SEEDSlider=1&exclude=I";
    var newContent = "";

    //alert("Fetching " + sSEEDSlider_QUrl+"?lang="+sSEEDSlider_Lang+"&q="+sQCmd);
    $.ajax({
            type: "POST",
            async: false,
            url: sSEEDSlider_QUrl,
            data: {'q': sQCmd, lang : sSEEDSlider_Lang },
            success: function(data){
                newContent = data;
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert(errorThrown);
            }
    });


    /* Debugging QServer: since the stdout of the server comes to newContent, 
     * uncomment the line below and put die("FOO"); in QServer
     */
    //alert(newContent);

    o = SEEDSlider_parseJSON(newContent);

    /* o = { 'A' : { qCode : qcode_of_boxA, html : html_of_boxA, htmlsmall : small_html_of_boxA },
     *       'B' : { ... },
     *       ...
     *     }
     *     
     * 
     */

    /* o = { qcode1 : { raQCodes  : { 'A':q1, 'B':q2, 'C':q3, ... },
     *                  htmlsmall : "brief html",
     *                  html      : "full html" },
     *       qcode2 : { ... },
     *
     *       for however many qcodes were requested
     */
    for( var q in o ) {
        // store the qcode data
        if( typeof o[q]['raQCodes'] != 'undefined' ) {
            oSEEDSlider_QCodesList[q] = o[q];
        }
    }


/*    
    for(var x in oSEEDSlider_BoxQCodes ) { // pretty sure this is a shortcut way to count A-I
        if( typeof o[x] == 'undefined' ) continue;
        
    	qcode = o[x]['qCode'];
        oSEEDSlider_BoxQCodes[x] = qcode;

        if( typeof oSEEDSlider_QCodesList[qcode] == 'undefined' ) {
            oSEEDSlider_QCodesList[qcode] = new Object();
        }
        if( typeof o[x]['raQCodes'] != 'undefined' ) {
            oSEEDSlider_QCodesList[qcode]['raQCodes'] = o[x]['raQCodes'];
        }
        oSEEDSlider_QCodesList[qcode]['html'] = o[x]['html'];
        oSEEDSlider_QCodesList[qcode]['htmlsmall'] = o[x]['htmlsmall'];
    }
*/
    //alert(newContent);
    //alert(JSON.stringify(oSEEDSlider_BoxQCodes));
    //alert(JSON.stringify(oSEEDSlider_QCodesList));

    
    
// return false if we couldn't fetch good data
    return( true );
}

function SEEDSlider_parseJSON(data) {
    return( window.JSON && window.JSON.parse ? window.JSON.parse( data ) : eval( data ) ); 
}


function SEEDSlider_Move( boxClicked )
/*************************************
    Animate boxClicked moving to E, and update the oSEEDSlider_BoxQCodes. 
    Assume all required data has been fetched.
 */
{
    qcode = oSEEDSlider_BoxQCodes[boxClicked];
    if( qcode == 0 ) return( false );
    
    var moveTo_grid = {
        "A": { 'A':'E', 'B':'F', 'C':0,   'D':'H', 'E':'I', 'F':0,   'G':0,   'H':0,   'I':0 },
        "B": { 'A':'D', 'B':'E', 'C':'F', 'D':'G', 'E':'H', 'F':'I', 'G':0,   'H':0,   'I':0 },
        "C": { 'A':0,   'B':'D', 'C':'E', 'D':0,   'E':'G', 'F':'H', 'G':0,   'H':0,   'I':0 },
        "D": { 'A':'B', 'B':'C', 'C':0,   'D':'E', 'E':'F', 'F':0,   'G':'H', 'H':'I', 'I':0 },
        "E": { 'A':0,   'B':0,   'C':0,   'D':0,   'E':0,   'F':0,   'G':0,   'H':0,   'I':0 },
        "F": { 'A':0,   'B':'A', 'C':'B', 'D':0,   'E':'D', 'F':'E', 'G':0,   'H':'G', 'I':'H' },
        "G": { 'A':0,   'B':0,   'C':0,   'D':'B', 'E':'C', 'F':0,   'G':'E', 'H':'F', 'I':0 },
        "H": { 'A':0,   'B':0,   'C':0,   'D':'A', 'E':'B', 'F':'C', 'G':'D', 'H':'E', 'I':'F' },
        "I": { 'A':0,   'B':0,   'C':0,   'D':0,   'E':'A', 'F':'B', 'G':0,   'H':'D', 'I':'E' }
        };
    
    var moveTo = moveTo_grid[boxClicked];  // array of boxFrom => boxTo for the set of moves per the clicked box

    for(var boxFrom in moveTo) {
        boxTo = moveTo[boxFrom];
        //alert(lFrom+" to "+lTo);

        if( boxTo == 0 ) {
            $("#SEEDSlider_"+boxFrom).css( {'display':'none'} );
        } else {
            $("#SEEDSlider_"+boxFrom).animate( oSEEDSlider_positions[boxTo] ); // arg is an object containing css properties (top, left, width, height)
        }
    }

    $(".SEEDSlider_boxsm, .SEEDSlider_boxlg").promise().done(function(){ 
        SEEDSlider_SetQCodes( qcode );
        SEEDSlider_Position();
    });
}
