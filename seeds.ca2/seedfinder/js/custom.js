var sfLang = "EN";


$(document).ready(function() {

    /************
       Bilingual output
       
       All multi-language output strings in this file are localized here.
       To activate French, the app can write js code that changes the variable sfLang to "FR".
     */
    var sLocal_No_matches_found = sfLang=='EN' ? "No matches found" : "Aucun r&eacute;sultat trouv&eacute;";




    /************
      Slider
     */
/*
	$(".rslides").responsiveSlides({
		auto: true,             // Boolean: Animate automatically, true or false
		speed: 500,            // Integer: Speed of the transition, in milliseconds
		timeout: 7000,          // Integer: Time between slide transitions, in milliseconds
		pager: true,           // Boolean: Show pager, true or false
		nav: true,             // Boolean: Show navigation, true or false
		random: false,          // Boolean: Randomize the order of the slides, true or false
		pause: true,           // Boolean: Pause on hover, true or false
		pauseControls: true,    // Boolean: Pause when hovering controls, true or false
		prevText: "<i class=\"fa fa-arrow-circle-left\" aria-hidden=\"true\"></i>",   // String: Text for the "previous" button
		nextText: "<i class=\"fa fa-arrow-circle-right\" aria-hidden=\"true\"></i>",       // String: Text for the "next" button
		maxwidth: "",           // Integer: Max-width of the slideshow, in pixels
		navContainer: ".nav",       // Selector: Where controls should be appended to, default is after the 'ul'
		manualControls: "",     // Selector: Declare custom pager navigation
		namespace: "rslides",   // String: Change the default namespace used
		before: function(){},   // Function: Before callback
		after: function(){}     // Function: After callback
	});
*/	
	$('#scroll').click(function(e) {
		$.scrollTo($('#search').position().top - 80, 200);
		return false;
	});
	
	$(window).bind('scroll', function () {
		if ($(window).scrollTop() > 50) {
			$('.header').addClass('fixed');
			$('.banner').addClass('top-spacing');
		} else {
			$('.header').removeClass('fixed');
			$('.banner').removeClass('top-spacing');
		}
	});


    /************
      Variables
     */

    /* data-backto records whether we got to the companies screen from TopChoices or from Search Results, so the Back button can go back there.
     */
    var cvblock_topChoices =
            '<div class="col topChoices col-lg-2 col-md-3 col-sm-4 col-xs-12">'
               +'<a class="get_details" data-backto=".topChoices" data-kPcv="[[P__key]]">'
                   +'<div class="widget-block panel-block">'
                       +'<div class="panel panel-default">'
                           +'<div class="panel-heading"><h3 class="panel-title">[[S_name_en]]</h3></div>'
                           +'<div class="panel-body">[[P_name]]</div>'
                       +'</div>'
                   +'</div>'
               +'</a>'
           +'</div>';
    var cvblock = 
            '<div class="col species col-lg-2 col-md-3 col-sm-4 col-xs-12">'
               +'<a class="get_details" data-backto=".species" data-kPcv="[[P__key]]">'
                   +'<div class="widget-block panel-block">'
                       +'<div class="panel panel-default">'
                           +'<div class="panel-heading"><h3 class="panel-title">[[S_name_en]]</h3></div>'
                           +'<div class="panel-body">[[P_name]]</div>'
                       +'</div>'
                   +'</div>'
               +'</a>'
           +'</div>';

    $(".fmt1").show();
    $(".fmt2").hide();
    
    var bProxy = true;

    var qurl  = bProxy ? "qcurl.php" : "https://seeds.ca/app/q/index.php";  
                                       //"http://localhost/~bob/seeds.ca2/app/q/index.php";
    var qurl2  = "https://seeds.ca/app/q2/index.php";  
    //qurl2 = "http://localhost/~bob/seedsx/seeds.ca2/app/q2/index.php";

    /************
      Initialize the display with Popular Varieties
     */
    $.ajax({
        type: "POST",
        url: qurl2,
        data : { qcmd: "srcSrcCvCultivarList", sMode: "TopChoices" },
        success: function(data){
            data = window.JSON.parse(data);
            console.log(data);
            
            var wrapper = $('<div class="sub-header col-lg-12 col-md-12 col-sm-12 col-xs-12"></div>');
            var message = $('<div class="alert alert-success message"><p align="center">'
                            +(sfLang=="EN" ? 'Popular varieties' : 'Vari&eacute;t&eacute;s populaires')
                            +'</p></div>');

            $('.seeds-results').append(wrapper.append(message));
            
            $.each(data.raOut, function(key,value) {
                var b = cvblock_topChoices;
                b = b.replace( "[[P_name]]", value.P_name );
                b = b.replace( "[[P__key]]", value.P__key );
                b = b.replace( "[[S_name_en]]", sfLang=="EN" ? value.S_name_en : value.S_name_fr );
                $('.seeds-results').append( $( b ) );   // parse the cvblock into DOM and append as the last child of seeds-results
            });

            $('.seeds-results .panel').matchHeight();
        },
        error : function( jqXHR, textStatus, errorThrown ) { console.log(errorThrown); }
    }).done(function() {
        $.scrollTo(0, 0);
    });


    /************
      Process the Find button
     */

	$("#finder").submit(function(e) {
		var $this = $(this);
		$('.seeds-results .species').remove();
		$('.seeds-results .topChoices').remove();
		$('.seeds-results .sub-header').remove();
		$('.seeds-results .details').remove();
		$('.seeds-results .details-header').remove();
		
	    $(".fmt1").show();
	    $(".fmt2").hide();

		$.ajax({
			type: "GET",
			url: "qcurl.php",
			data: $(this).serialize() + "&cmd=find",
			success: function(data){
				if(!data.bOk){
					$('section.results h1').text(''); //'Search result');

					var wrapper = $('<div class="sub-header col-lg-12 col-md-12 col-sm-12 col-xs-12"></div>');
					var message = $('<div class="alert alert-danger message"><p align="center">'+sLocal_No_matches_found+'</p></div>');
					wrapper.append(message);
					$('.seeds-results').append(wrapper);
				}
				
				if(data.bOk){
					var title = data.raOut[Object.keys(data.raOut)[0]].S_name_en;
					$('section.results h1').text('Search result');
					if($this.find('#sfAp_sp').val() != 0){ $('section.results h1').text('Search results for ' + title); }
					
					if ($(e.target).find('.organic').prop('checked')) {
						var wrapper = $('<div class="sub-header col-lg-12 col-md-12 col-sm-12 col-xs-12"></div>');
						var message = $('<div class="alert alert-success message"><p align="center">Certified Organic!</p></div>');
						wrapper.append(message);
						$('.seeds-results').append(wrapper);
					}
					
//					$.each(data.raOut, function(key,value) {
					
					
//						var block = $('<div class="col species col-lg-2 col-md-2 col-sm-12 col-xs-12"><a class="get_details" data-kPcv="' + value.P__key + '" data-backto=".species"><div class="widget-block panel-block"><div class="panel panel-default"><div class="panel-body">' + value.P_name + '</div></div></div></a></div>');
//						$('.seeds-results').append(block);
//					});
		            $.each(data.raOut, function(key,value) {
		                var b = cvblock;
		                b = b.replace( "[[P_name]]", value.P_name );
		                b = b.replace( "[[P__key]]", value.P__key );
		                b = b.replace( "[[S_name_en]]", sfLang=="EN" ? value.S_name_en : value.S_name_fr );
		                $('.seeds-results').append( $( b ) );   // parse the cvblock into DOM and append as the last child of seeds-results
		            });

					
					
					
					$('.seeds-results .panel').matchHeight();
				}
			}
		}).done(function() {
            $.scrollTo($('.results').position().top - 80, 500);
        });
		e.preventDefault();
	});
	
	$('.seeds-results').delegate("a.back", "click", function(e) {
		$('.seeds-results .details').remove();
		$('.seeds-results .details-header').remove();
	    $(".fmt1").show();
	    $(".fmt2").hide();
		$('.seeds-results ' + $(e.target).data('backto')).css('display', 'block');
		$('.seeds-results .sub-header').css('display', 'block');
        $.scrollTo($('.results').position().top - 80, 200);
	});
	
	
	/* Show the companies that sell the variety given by data-kPcv
	 */
	$('.seeds-results').delegate("a.get_details", "click", function(e) {
		var $this = $(this);
		$('.seeds-results ' + $this.attr('data-backto')).css('display', 'none');
		$('.seeds-results .sub-header').css('display', 'none');
		$('.seeds-results .details').remove();
		$('.seeds-results .details-header').remove();

        var qdata3 = bProxy ? { cmd : "suppliers", kPcv: $(this).attr('data-kPcv') } : { qcmd: "srcSources", kPcv: $(this).attr('data-kPcv') };
        $.ajax({
            type: "POST",
            url: qurl,
            data: qdata3,
            success: function(data) {
                if( !bProxy ) data = window.JSON.parse(data);
                //console.log(data);
                
                var sp = $this.find('.panel-title').text();
                var cv = $this.find('.panel-body').text();
                var sProfile = "";
                
                $.ajax({
                    async: false,
                    type: "POST",
                    url: qurl,
                    data: { cmd: "profile", sp: sp, cv: cv },
                    success: function(data) {
                        sProfile = data;
                        
                    },
                }).done(function() {
                    $.scrollTo($('.results').position().top - 80, 200);
                });
                
                if( sProfile ) {
                    $(".profile-name").html( cv + " " + sp );
                    $(".profile").html(sProfile);
                    $(".fmt1").hide();
                    $(".fmt2").show();
                } else {
                    $(".fmt1").show();
                    $(".fmt2").hide();
                }
                
                
                // full-width column
                var fullwidth = '<div class="details-header col col-lg-12 col-md-12 col-sm-12 col-xs-12"></div>';

                var headAvailable = '<h2 align="center">' + $this.find('.panel-body').text() 
                  + " "+sp+" "
//                + "<br/>(aka Amish Knuttle, Amish Nuttle, Cornhill, Corn Hill, Seneca Cornhill, Mayflower)<br/>"
                                  + ' available from <strong>' + data.raOut.length + '</strong> suppliers</h2>';

                var button = '<p align="center"><a href="#" onclick="return false;" data-backto="' + $this.attr('data-backto') + '" '
                           + 'class="btn btn-success btn-lg back">Back</a></p><br />';
                          
                $('.seeds-results').append( $(fullwidth).append($(headAvailable)),
                                            $(fullwidth).append($(button) ) );

                $.each(data.raOut, function(key,value) {
                    var src = '<div class="panel-body">'
                                 +'<p>' + value.SRC_address + '<br />' 
                                        + value.SRC_city + ' ' + value.SRC_prov + '<br />' 
                                        + value.SRC_postcode 
                                 +'</p>' 
                                 +'<p><a href="http://' + value.SRC_web + '" target="_blank">' + value.SRC_web + '</a></p>'
                             +'</div>';
                    
                    var sDiv = sProfile ? '<div class="col details col-lg-4 col-md-6 col-sm-12 col-xs-12">' 
                                        : '<div class="col details col-lg-3 col-md-4 col-sm-12 col-xs-12">';
                    var block = $( sDiv
                                     + '<div class="widget-block panel-block">'
                                         +'<div class="panel panel-default">'
                                             + '<div class="panel-heading"><h3 class="panel-title">' + value.SRC_name + '</h3></div>'
                                             + src
                                         + '</div>'
                                     +'</div>'
                                 +'</div>');
                    $('.seeds-results').append(block);
                });

                $('.seeds-results .panel').matchHeight();
            }
        }).done(function() {
            $.scrollTo($('.results').position().top - 80, 200);
        });
        
        e.preventDefault();
    });

    $(window).resize(function() {
        $('.seeds-results .panel').matchHeight();
    });
});
