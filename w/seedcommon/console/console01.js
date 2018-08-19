/* console01.js
 * 
 * Copyright (c) 2011-2013  Seeds of Diversity Canada
 *
 * Client-side implementation of the console.
 */

var console01scrollhere = "";    // set this to the element id where you want to scroll
var console01scrollYOffset = 0;  // set this to the pixel Y offset where you want to scroll

function console01_onload()
/*
 * Console01 calls this immediately after the page is loaded and formatted,
 * so all elements are positioned and sized, and any in-line code has been executed.
 */
{
    /* Scroll the window to a show a given element.
     * scrollIntoView() was a great function until Microsoft decided to ditch it in IE
     *
     * Three ways to define the scroll position:
     *     1) set var console01scrollYOffset=[pixel Y offset of top of window]
     *     2) set var console01scrollhere=[element id]
     *     3) use <div id='console01scrollhere'>
     */
    // this method should come first because it can dynamically override a "default" scroll position defined at the elementid 
	if( console01scrollYOffset > 0 ) {
        window.scrollTo(0,console01scrollYOffset);
    } else {
        e = document.getElementById(console01scrollhere);                 // arbitrary id set in the variable
        if( !e ) { e = document.getElementById("console01scrollhere"); }  // verbatim id set in a div tag
        if( e ) {
            y = getElementTopPosition( e );
            window.scrollTo(0,y - 50);
        }
    }
}

function getElementTopPosition( e )
{
    var y = 0;
    while( e ) {
        y += e.offsetTop + e.clientTop;
        e = e.offsetParent;
    }
    return( y );
}

function console01_getYOffset() {
    var y;  // var x;

    if( typeof( window.pageYOffset ) == 'number' ) {
        //Netscape/Firefox compliant
        y = window.pageYOffset;
        x = window.pageXOffset;
    } else if( document.body && ( document.body.scrollLeft || document.body.scrollTop ) ) {
        //DOM compliant
        y = document.body.scrollTop;
        x = document.body.scrollLeft;
    } else if( document.documentElement && ( document.documentElement.scrollLeft || document.documentElement.scrollTop ) ) {
        //IE6 standards compliant mode
        y = document.documentElement.scrollTop;
        x = document.documentElement.scrollLeft;
    }
    return( y );  // return [ x, y ];
}

function console01_Form_ScrollToHere(oForm)
/******************************************
 	Add a hidden element to the form and set it to the current window scroll position.
 	Usage: <form ... onsubmit='console01_Form_ScrollToHere(this);' > 
 */
{
	$("<input>").attr("type", "hidden")
				.attr("name", "c01FormYScroll")
				.val(console01_getYOffset())
				.appendTo((oForm));
}
