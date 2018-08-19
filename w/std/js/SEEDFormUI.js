/* SEEDFormUI.js
 *
 * jquery support for UI widgets
 *
 * Copyright (c) 2015 Seeds of Diversity Canada
 */


$(document).ready( function() { 

    // don't set up the autocomplete if there isn't one - offset().left below causes a js error that kills other ready() functions
    if( typeof $('.SFU_AutoComplete').offset() == 'undefined' )  return;

    // Set up the AutoComplete select box
    //     Show Select on focus, and hide it when a value is selected.
    //     Except, it's nice to be able to hide Select without choosing a value.
    //     Select.hide on AutoComplete.blur didn't work because the Select doesn't receive the click.
    //     You can use a complex combination of focus and mousedown, but it's complex.
    //     Select.toggle on AutoComplete.click is great, except that you can't activate the Select by obtaining focus via tab key.
    //         In a complicated screen you won't do that much anyway.      
    $('.SFU_AutoComplete').click( 
        function(e){ 
            $('.SFUAC_Select').toggle();

            /* Position the Select just below the Search box. The right way seems to be absolute positioning with top and left
             * calculated using the Search Box. But absolute positions are relative to the closest positioned (e.g. position:relative) ancestor.
             * That means you have to set some ancestor to 
             *
             *     style='position:relative' class='SFUAC_Anchor'
             *
             * so we can find the Search box in static coordinates and compute its location relative to the Anchor, then position the Select 
             * relative to that Anchor.
             */
            var xSearch = $('.SFU_AutoComplete').offset().left;
            var ySearch = $('.SFU_AutoComplete').offset().top;
            var hSearch = $('.SFU_AutoComplete').outerHeight();
            var xAnchor = $('.SFUAC_Anchor').offset().left;
            var yAnchor = $('.SFUAC_Anchor').offset().top;
            $('.SFUAC_Select').css({ left:xSearch-xAnchor, top:ySearch+hSearch-yAnchor });
        });
    $('.SFUAC_Select').click( function(e){ e.preventDefault(); SEEDFormUIAutoComplete_SelectClick(); $('.SFUAC_Select').hide();});
    $('.SFUAC_Select').css({ display:'none', 
                             position:'absolute'
                           });
    $('.SFUAC_Select').attr({ size:$('.SFUAC_Select option').length }); 
    
    $('.SFU_AutoComplete').keyup( function(e){ SEEDFormUIAutoComplete_Change(); });
    
});

var SEEDFormUIParms = { urlQ : "https://www.seeds.ca/app/q/index.php" };
//var SEEDFormUIParms = { urlQ : "http://localhost/seedsCurr/seeds.ca2/app/q/index.php" };

function SEEDFormUIAutoComplete_Change()
{
	var urlQ = SEEDFormUIParms['urlQ'];	// ajax request to authenticate parameters
	var sSearch = $(".SFU_AutoComplete").val();  //var formData = $(".SFU_AutoComplete").serialize();alert(formData);

	// don't bother searching unless the user has typed at least 3 chars
	if( sSearch.length < 3 )  return;
	//if( !sSearch )  return;
	
	jxData = { cmd : 'rosettaPCVSearch',
			   lang : "EN",
			   srch : sSearch
			 };

// extend SEEDJX to take a function ( o ) that is called on async completion 
	var o = SEEDJX( urlQ, jxData );

	if( !o ) {
		alert( "Sorry there is a server problem" );
	} else {
		//var bOk = o['bOk'];
		//var sOut = o['sOut'];
		var raOut = o['raOut'];
		
		$(".SFUAC_Select option").each(function() {
		    $(this).remove();
		});

		c = raOut.length;
		if( c > 20 ) c = 20;
		for( var i = 0; i < c; ++i ) {
			var r = raOut[i];
// because json can't transmit cp1252 special chars, only utf8 - another reason to make everything utf8			
//			if( r['P_name']==null ) { r['P_name'] = r['P_name_utf8']; } obsolete, no longer sent
			$('.SFUAC_Select').append($('<option>', { value: r['P__key'], text: r['S_psp']+" : "+r['P_name']+" ("+r['P__key']+")" }) );
		}
	}

	// make the select control tall enough to contain all options
    $('.SFUAC_Select').attr({ size:$('.SFUAC_Select option').length }); 
}


function SEEDFormUIAutoComplete_SelectClick()
{
	var sel = $(".SFUAC_Select").val();
	
	$("#cultivarText").html( $('.SFUAC_Select option:selected').text() );
	$("#sfAp_fk_sl_pcv").val(sel);
}


