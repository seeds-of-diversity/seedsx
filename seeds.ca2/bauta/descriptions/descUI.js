/* descUI.js
 *
 * Copyright (c) 2014 Seeds of Diversity Canada
 *
 * User interface for Crop Descriptions.
 */

var CDBodyCurrBox = 0;

function CDPosition( bAnimate ) {
    // Adjustable parameters
    var pad = 20;          // space between the boxes
    var padOuter = 0; //0.1;    // % of body for outer padding
    var smallWmax = 200;   // max size of small boxes
    var smallHmax = 200;   // max height of small boxes

    if( CDBodyCurrBox < 1 || CDBodyCurrBox > 4 ) {
        CDBodyCurrBox = 1;
        bAnimate = false;
    }
    

    // Find the rectangle of the #CropDescBody div
    var bodyTop = $("#CropDescBody").offset().top;
    var bodyLeft = $("#CropDescBody").offset().left;
    var bodyWidth = $(window).width() - bodyLeft;
    var bodyHeight = $(window).height() - bodyTop;

    var padX = padOuter * bodyWidth;
    var padY = padOuter * bodyHeight;

    // The big box is 2x the size of the small boxes, unless that makes the small boxes > smallWmax & smallHmax
    var smallW = (bodyWidth - padX*2 - pad) / 3;
    if( smallW > smallWmax )  smallW = smallWmax;
    var bigW = bodyWidth - padX*2 - pad - smallW;

    var smallH = (bodyHeight - padY*2 - pad) / 3;
    if( smallH > smallHmax )  smallH = smallHmax;
    var bigH = bodyHeight - padY*2 - pad - smallH;

    if( bAnimate ) {
    if( CDBodyCurrBox == 1 ) {
        $("#CropDescBox1").animate({ top:padY,                 left:padX,                 width:bigW,   height:bigH,   display:'block' });
        $("#CropDescBox2").animate({ top:padY + bigH - smallH, left:bigW + padX + pad,    width:smallW, height:smallH, display:'block' });
        $("#CropDescBox3").animate({ top:bigH + padY + pad,    left:padX + bigW - smallW, width:smallW, height:smallH, display:'block' });
        $("#CropDescBox4").animate({ top:bigH + padY + pad,    left:bigW + padX + pad,    width:smallW, height:smallH, display:'block' });
    } else if( CDBodyCurrBox == 2 ) {
        $("#CropDescBox1").animate({ top:padY + bigH - smallH, left:padX,                 width:smallW, height:smallH, display:'block' });
        $("#CropDescBox2").animate({ top:padY,                 left:padX + smallW + pad,  width:bigW,   height:bigH,   display:'block' });
        $("#CropDescBox3").animate({ top:bigH + padY + pad,    left:padX,                 width:smallW, height:smallH, display:'block' });
        $("#CropDescBox4").animate({ top:bigH + padY + pad,    left:padX + smallW + pad,  width:smallW, height:smallH, display:'block' });
    } else if( CDBodyCurrBox == 3 ) {
        $("#CropDescBox1").animate({ top:padY,                 left:padX + bigW - smallW, width:smallW, height:smallH, display:'block' });
        $("#CropDescBox2").animate({ top:padY,                 left:bigW + padX + pad,    width:smallW, height:smallH, display:'block' });
        $("#CropDescBox3").animate({ top:padY + pad + smallH,  left:padX,                 width:bigW,   height:bigH,   display:'block' });
        $("#CropDescBox4").animate({ top:padY + pad + smallH,  left:bigW + padX + pad,    width:smallW, height:smallH, display:'block' });
    } else if( CDBodyCurrBox == 4 ) {
        $("#CropDescBox1").animate({ top:padY,                 left:padX,                 width:smallW, height:smallH, display:'block' });
        $("#CropDescBox2").animate({ top:padY,                 left:padX + smallW + pad,  width:smallW, height:smallH, display:'block' });
        $("#CropDescBox3").animate({ top:smallH + padY + pad,  left:padX,                 width:smallW, height:smallH, display:'block' });
        $("#CropDescBox4").animate({ top:smallH + padY + pad,  left:padX + smallW + pad,  width:bigW,   height:bigH,   display:'block' });
    }
    }

    if( !bAnimate ) {
    if( CDBodyCurrBox == 1 ) {
        $("#CropDescBox1").css({ top:padY,                 left:padX,                 width:bigW,   height:bigH,   display:'block' });
        $("#CropDescBox2").css({ top:padY + bigH - smallH, left:bigW + padX + pad,    width:smallW, height:smallH, display:'block' });
        $("#CropDescBox3").css({ top:bigH + padY + pad,    left:padX + bigW - smallW, width:smallW, height:smallH, display:'block' });
        $("#CropDescBox4").css({ top:bigH + padY + pad,    left:bigW + padX + pad,    width:smallW, height:smallH, display:'block' });
    } else if( CDBodyCurrBox == 2 ) {
        $("#CropDescBox1").css({ top:padY + bigH - smallH, left:padX,                 width:smallW, height:smallH, display:'block' });
        $("#CropDescBox2").css({ top:padY,                 left:padX + smallW + pad,  width:bigW,   height:bigH,   display:'block' });
        $("#CropDescBox3").css({ top:bigH + padY + pad,    left:padX,                 width:smallW, height:smallH, display:'block' });
        $("#CropDescBox4").css({ top:bigH + padY + pad,    left:padX + smallW + pad,  width:smallW, height:smallH, display:'block' });
    } else if( CDBodyCurrBox == 3 ) {
        $("#CropDescBox1").css({ top:padY,                 left:padX + bigW - smallW, width:smallW, height:smallH, display:'block' });
        $("#CropDescBox2").css({ top:padY,                 left:bigW + padX + pad,    width:smallW, height:smallH, display:'block' });
        $("#CropDescBox3").css({ top:padY + pad + smallH,  left:padX,                 width:bigW,   height:bigH,   display:'block' });
        $("#CropDescBox4").css({ top:padY + pad + smallH,  left:bigW + padX + pad,    width:smallW, height:smallH, display:'block' });
    } else if( CDBodyCurrBox == 4 ) {
        $("#CropDescBox1").css({ top:padY,                 left:padX,                 width:smallW, height:smallH, display:'block' });
        $("#CropDescBox2").css({ top:padY,                 left:padX + smallW + pad,  width:smallW, height:smallH, display:'block' });
        $("#CropDescBox3").css({ top:smallH + padY + pad,  left:padX,                 width:smallW, height:smallH, display:'block' });
        $("#CropDescBox4").css({ top:smallH + padY + pad,  left:padX + smallW + pad,  width:bigW,   height:bigH,   display:'block' });
    }
    }
}

function CDClick( iBox )
{
    CDBodyCurrBox = iBox;

    $("#CropDescBox"+iBox).removeClass("CropDescBoxNotCurr");
    $("#CropDescBox"+iBox).addClass("CropDescBoxCurr");
    for( var i=1; i<=4; ++i ) {
        if( i == iBox ) continue;
        $("#CropDescBox"+i).removeClass("CropDescBoxCurr");
        $("#CropDescBox"+i).addClass("CropDescBoxNotCurr");
    }
}


$(document).ready( function() { 
	// to load a page with one of the boxes active, set CDBodyCurrBox before document.ready
	if( CDBodyCurrBox < 1 || CDBodyCurrBox > 4 ) {
        CDBodyCurrBox = 1;
        bAnimate = false;
    }
    CDClick( CDBodyCurrBox );  
    CDPosition( false );

    // handlers
    $(window).resize( function() { CDPosition(); } );

    $("#CropDescBox1").click( function(){ CDClick( 1 );  CDPosition( true ); });
    $("#CropDescBox2").click( function(){ CDClick( 2 );  CDPosition( true ); });
    $("#CropDescBox3").click( function(){ CDClick( 3 );  CDPosition( true ); });
    $("#CropDescBox4").click( function(){ CDClick( 4 );  CDPosition( true ); });
});
