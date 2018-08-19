/* SEEDStd.js
 *
 * Standard helper functions
 * 
 * Copyright (c) 2015-2016 Seeds of Diversity Canada
 */

var SEEDJX_bDebug = false;   // set this true where you call SEEDJX to get private debug information

function SEEDJX( urlAuth, jxData )
{
	var bSuccess = false;
	var oRet = null;
	$.ajax({
		type: "POST",
        async: false,
		url: urlAuth,
		data: jxData,
		//dataType: "json",
		success: function(data) {
			// To debug the server, put die("whatever") in the server code and uncomment below
			if( SEEDJX_bDebug ) alert("data="+data);
			
			bSuccess = true;
			oRet = SEEDJX_ParseJSON(data);
		},
		error: function(jqXHR, textStatus, errorThrown) {
			if( SEEDJX_bDebug ) {
				alert(errorThrown);
				//alert(jqXHR);
				//alert(textStatus);
			}
		}
	});
	return( bSuccess ? oRet : null );
}

function SEEDJX_ParseJSON( data )
{
    if( !data ) return( null );
    return( window.JSON && window.JSON.parse && data ? window.JSON.parse(data) : eval(data) ); 
}


function SEEDStd_PostAValue( url, n, v )
/***************************************
    This can be a handy way to cause a link to send a value via post

    <a href="" onclick="SEEDStd_PostAValue( '{url}', '{n}', '{v}' );">

    is the same as

    <a href="{url}?{n}={v}">

    except it sends by post
 */
{
    form = document.createElement( 'form' );
    form.setAttribute( 'method', 'POST' );
    form.setAttribute( 'action', url );
    e = document.createElement( 'input' );
    e.setAttribute( 'type', 'hidden' );
    e.setAttribute( 'name', n );
    e.setAttribute( 'value', v );
    form.appendChild( e );
    document.body.appendChild( form );
    form.submit();
}
