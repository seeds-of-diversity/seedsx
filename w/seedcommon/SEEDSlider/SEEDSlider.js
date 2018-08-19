/* SEEDSlider
 *
 * Copyright 2013-2014 Seeds of Diversity Canada
 *
 * Manage a set of nine sliding boxes with content retrieved by AJAX.
 */

// initial parameters
var boxSm = 125;
var boxLg = 400;
var boxPad = 50;
var qURL = SEEDSiteRootURL+'bauta/q.php';


// internal
var pos1 = boxSm+boxPad+boxLg/2-boxSm/2;  // half way
var pos2 = boxSm+boxPad+boxLg+boxPad;     // all the way
var posBox = { 'A': { top: 0,    left: 0,    h: boxSm, w: boxSm },
               'B': { top: 0,    left: pos1, h: boxSm, w: boxSm },
               'C': { top: 0,    left: pos2, h: boxSm, w: boxSm },
               'D': { top: pos1, left: 0,    h: boxSm, w: boxSm },
               'E': { top: boxSm+boxPad, left: boxSm+boxPad, h: boxLg, w: boxLg },
               'F': { top: pos1, left: pos2, h: boxSm, w: boxSm },
               'G': { top: pos2, left: 0,    h: boxSm, w: boxSm },
               'H': { top: pos2, left: pos1, h: boxSm, w: boxSm },
               'I': { top: pos2, left: pos2, h: boxSm, w: boxSm }
};
var SSliderData = { 'A': { qCode: 0, html:"", htmlSmall:"" },
                    'B': { qCode: 0, html:"", htmlSmall:"" },
                    'C': { qCode: 0, html:"", htmlSmall:"" },
                    'D': { qCode: 0, html:"", htmlSmall:"" },
                    'E': { qCode: 0, html:"", htmlSmall:"" },
                    'F': { qCode: 0, html:"", htmlSmall:"" },
                    'G': { qCode: 0, html:"", htmlSmall:"" },
                    'H': { qCode: 0, html:"", htmlSmall:"" },
                    'I': { qCode: 0, html:"", htmlSmall:"" },
};


var counter = 1;

var borderDivsWhite = 
   "<div class='c01PhrameBlock-tl c01PhrameBlockWhite-tl'></div>" +
   "<div class='c01PhrameBlock-tc c01PhrameBlockWhite-tc'></div>" +
   "<div class='c01PhrameBlock-tr c01PhrameBlockWhite-tr'></div>" +
   "<div class='c01PhrameBlock-cl c01PhrameBlockWhite-cl'></div>" +
   "<div class='c01PhrameBlock-cc c01PhrameBlockWhite-cc'></div>" +
   "<div class='c01PhrameBlock-cr c01PhrameBlockWhite-cr'></div>" +
   "<div class='c01PhrameBlock-bl c01PhrameBlockWhite-bl'></div>" +
   "<div class='c01PhrameBlock-bc c01PhrameBlockWhite-bc'></div>" +
   "<div class='c01PhrameBlock-br c01PhrameBlockWhite-br'></div>";
var borderDivsGray = 
	   "<div class='c01PhrameBlock-tl c01PhrameBlockGray-tl'></div>" +
	   "<div class='c01PhrameBlock-tc c01PhrameBlockGray-tc'></div>" +
	   "<div class='c01PhrameBlock-tr c01PhrameBlockGray-tr'></div>" +
	   "<div class='c01PhrameBlock-cl c01PhrameBlockGray-cl'></div>" +
	   "<div class='c01PhrameBlock-cc c01PhrameBlockGray-cc'></div>" +
	   "<div class='c01PhrameBlock-cr c01PhrameBlockGray-cr'></div>" +
	   "<div class='c01PhrameBlock-bl c01PhrameBlockGray-bl'></div>" +
	   "<div class='c01PhrameBlock-bc c01PhrameBlockGray-bc'></div>" +
	   "<div class='c01PhrameBlock-br c01PhrameBlockGray-br'></div>";


function resetThem()
{
    $("#SEEDSlider_A").css({ top:posBox['A']['top'], left:posBox['A']['left'], height:posBox['A']['h'], width:posBox['A']['w'], display:'block' });
    $("#SEEDSlider_B").css({ top:posBox['B']['top'], left:posBox['B']['left'], height:posBox['B']['h'], width:posBox['B']['w'], display:'block' });
    $("#SEEDSlider_C").css({ top:posBox['C']['top'], left:posBox['C']['left'], height:posBox['C']['h'], width:posBox['C']['w'], display:'block' });
    $("#SEEDSlider_D").css({ top:posBox['D']['top'], left:posBox['D']['left'], height:posBox['D']['h'], width:posBox['D']['w'], display:'block' });
    $("#SEEDSlider_E").css({ top:posBox['E']['top'], left:posBox['E']['left'], height:posBox['E']['h'], width:posBox['E']['w'], display:'block' });
    $("#SEEDSlider_F").css({ top:posBox['F']['top'], left:posBox['F']['left'], height:posBox['F']['h'], width:posBox['F']['w'], display:'block' });
    $("#SEEDSlider_G").css({ top:posBox['G']['top'], left:posBox['G']['left'], height:posBox['G']['h'], width:posBox['G']['w'], display:'block' });
    $("#SEEDSlider_H").css({ top:posBox['H']['top'], left:posBox['H']['left'], height:posBox['H']['h'], width:posBox['H']['w'], display:'block' });
    $("#SEEDSlider_I").css({ top:posBox['I']['top'], left:posBox['I']['left'], height:posBox['I']['h'], width:posBox['I']['w'], display:'block' });
}

function moveThem( toE )
{
	// animate the movement
	// wait for done
	// switch html & reposition
	
    var o = {
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

    // After the move and reset, each position will have the text from the given former position.
    // This is just a reordering of the above (A and I are conjugates, B and H, D and F, ... )
    var oReverse = {
        "A": { 'A':0,   'B':0,   'C':0,   'D':0,   'E':'A', 'F':'B', 'G':0,   'H':'D', 'I':'E' },
        "B": { 'A':0,   'B':0,   'C':0,   'D':'A', 'E':'B', 'F':'C', 'G':'D', 'H':'E', 'I':'F' },
        "C": { 'A':0,   'B':0,   'C':0,   'D':'B', 'E':'C', 'F':0,   'G':'E', 'H':'F', 'I':0 },
        "D": { 'A':0,   'B':'A', 'C':'B', 'D':0,   'E':'D', 'F':'E', 'G':0,   'H':'G', 'I':'H' },
        "E": { 'A':0,   'B':0,   'C':0,   'D':0,   'E':0,   'F':0,   'G':0,   'H':0,   'I':0 },
        "F": { 'A':'B', 'B':'C', 'C':0,   'D':'E', 'E':'F', 'F':0,   'G':'H', 'H':'I', 'I':0 },
        "G": { 'A':0,   'B':'D', 'C':'E', 'D':0,   'E':'G', 'F':'H', 'G':0,   'H':0,   'I':0 },
        "H": { 'A':'D', 'B':'E', 'C':'F', 'D':'G', 'E':'H', 'F':'I', 'G':0,   'H':0,   'I':0 },
        "I": { 'A':'E', 'B':'F', 'C':0,   'D':'H', 'E':'I', 'F':0,   'G':0,   'H':0,   'I':0 },
        };

    var r = o[toE];
    var sAfter = {
            'A':{qCode:0, html:'', htmlSmall:''},
            'B':{qCode:0, html:'', htmlSmall:''},
            'C':{qCode:0, html:'', htmlSmall:''},
            'D':{qCode:0, html:'', htmlSmall:''},
            'E':{qCode:0, html:'', htmlSmall:''},
            'F':{qCode:0, html:'', htmlSmall:''},
            'G':{qCode:0, html:'', htmlSmall:''},
            'H':{qCode:0, html:'', htmlSmall:''},
            'I':{qCode:0, html:'', htmlSmall:''}
    };
    for(var x in oReverse[toE]) {
        var y = oReverse[toE][x];
        if( y == 0 ) {
            borderDivs = borderDivsWhite; //borderDivs = (Math.random() > 0.5 ? borderDivsWhite : borderDivsGray);

            if( SSliderData[x]['qCode'] ) {
            	// user clicked on box toE, and x is a box that needs to be fetched 
            	// (there will be 3 or 5 values of x and that set will include toE)
            	// e.g. if user clicked on box F, toE='F' and x is 'C', 'F', and 'I',
            	//      so three calls are made on (qCode(F),C) (qCode(F),F) (qCode(F),I)
            	q = "get|qCode="+SSliderData[x]['qCode']+"&qParm="+x;
            	newContent = "";
            	$.ajax({
            		type: "POST",
            		async: false,
            		url: qURL,
            		data: {'q': q },
            		success: function(data){
            			newContent = data;
            		}
            	});

            	o = parseJSON( newContent );
            	sAfter[x]['qCode'] = o['qCode'];
            	sAfter[x]['html'] = borderDivs + "<div style='overflow-y:auto;max-height:375px'>" + o['html'] + "</div>";
            	sAfter[x]['htmlSmall'] = borderDivs + o['htmlSmall'];
            } else {
            	// the box was a dummy so keep it that way
            	sAfter[x]['qCode'] = 0;
            	sAfter[x]['html'] = borderDivs;
            	sAfter[x]['htmlSmall'] = borderDivs;
            }
        } else {
            sAfter[x]['qCode'] = SSliderData[y]['qCode'];
            sAfter[x]['html'] = SSliderData[y]['html'];
            sAfter[x]['htmlSmall'] = SSliderData[y]['htmlSmall'];
            //sAfter[x] = $("#SEEDSlider_"+y).html();
        }//alert(x+" "+sAfter[x]['htmlSmall']);
    }

    for(var x in r) {
        lFrom = x;
        lTo = r[x];
        //alert(lFrom+" to "+lTo);

        if( lTo == 0 ) {
            $("#SEEDSlider_"+lFrom).css( {'display':'none'} );
        } else {
            $("#SEEDSlider_"+lFrom).animate({ top:posBox[lTo]['top'], left:posBox[lTo]['left'], height:posBox[lTo]['h'], width:posBox[lTo]['w'] });
        }
    }

    $(".SEEDSlider_box1, .SEEDSlider_box2").promise().done(function(){ 
        for(var x in SSliderData) {
            SSliderData[x] = sAfter[x];
            if( x == 'E' ) {
                $("#SEEDSlider_"+x).html(sAfter[x]['html']);
            } else {
                $("#SEEDSlider_"+x).html(sAfter[x]['htmlSmall']);
            }
        }
        resetThem(); 
    });
}


function parseJSON(data) {
    return( window.JSON && window.JSON.parse ? window.JSON.parse( data ) : eval( data ) ); 
}


$(document).ready(function(){

    resetThem();

    $("#SEEDSlider_A").click(function(){
        moveThem( "A" );
        });
    $("#SEEDSlider_B").click(function(){
        moveThem( "B" );
        });
    $("#SEEDSlider_C").click(function(){
        moveThem( "C" );
        });
    $("#SEEDSlider_D").click(function(){
        moveThem( "D" );
        });
    $("#SEEDSlider_F").click(function(){
        moveThem( "F" );
        });
    $("#SEEDSlider_G").click(function(){
        moveThem( "G" );
        });
    $("#SEEDSlider_H").click(function(){
        moveThem( "H" );
        });
    $("#SEEDSlider_I").click(function(){
        moveThem( "I" );
        });


    // initialize using data that the client put in QObjInitial
    for(var x in SSliderData) {
        SSliderData[x]['qCode'] = QObjInitial[x]['qCode'];   //alert(x+" "+SSliderData[x]['qCode']);
        SSliderData[x]['htmlSmall'] = borderDivsWhite + QObjInitial[x]['htmlSmall'];
        SSliderData[x]['html'] = borderDivsWhite + "<div style='overflow-y:auto;max-height:375px'>" + QObjInitial[x]['html'] + "</div>";

        if( x == 'E' ) {
            $("#SEEDSlider_"+x).html( SSliderData[x]['html'] );
        } else {
            $("#SEEDSlider_"+x).html( SSliderData[x]['htmlSmall'] );

        }
//        SSliderData[x]['htmlSmall'] = $("#SEEDSlider_"+x).html();
//        SSliderData[x]['html'] = $("#SEEDSlider_"+x).html();
    }
});
