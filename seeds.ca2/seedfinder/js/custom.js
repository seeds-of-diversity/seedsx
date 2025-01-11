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
/*
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
*/

    /************
      Variables
     */

    /* data-backto records whether we got to the companies screen from TopChoices or from Search Results, so the Back button can go back there.
     */
    var cvblock_topChoices =
            `<div class="col topChoices col-lg-2 col-md-3 col-sm-4 col-xs-12">
                 <a class="get_details" data-backto=".topChoices" data-kPcv="[[P__key]]">
                     <div class="h-100 widget-block panel-block">
                         <div class="h-100 panel panel-default">
                             <div class="panel-heading"><h3 class="panel-title">[[S_name_en]]</h3></div>
                             <div class="panel-body">[[P_name]]</div>
                             <div>[[sSynonyms]]</div>
                         </div>
                     </div>
                 </a>
             </div>`;
    var cvblock = 
            '<div class="col species col-lg-2 col-md-3 col-sm-4 col-xs-12">'
               +'<a class="get_details" data-backto=".species" data-kPcv="[[P__key]]">'
                   +'<div class="h-100 widget-block panel-block">'
                       +'<div class="h-100 panel panel-default">'
                           +'<div class="panel-heading"><h3 class="panel-title">[[S_name_en]]</h3></div>'
                           +'<div class="panel-body">[[P_name]]</div>'
                           +'<div style="margin-top:-10px;margin-bottom:5px;text-align:center;color:#233449">[[sSynonyms]]</div>'
                       +'</div>'
                   +'</div>'
               +'</a>'
           +'</div>';

    $(".fmt1").show();
    $(".fmt2").hide();
    
    var bProxy = true;
    var bLocal = false;

    var qurl  = bProxy ? "qcurl.php" : "https://seeds.ca/app/q/index.php";  
    var qurl2 = bLocal ? "http://localhost/~bob/seedsx/seeds.ca2/app/q2/index.php" :
                         "https://seeds.ca/app/q2/index.php";  

    /************
      Initialize the display with Rare Varieties
     */
    $.ajax({
        type: "POST",
        url: qurl2,
        data : { qcmd: "srcSrcCvCultivarList", sMode: "RareChoices" },
        success: function(data){
            data = window.JSON.parse(data);
            //console.log(data);
            
            var wrapper = $('<div class="sub-header col-lg-12 col-md-12 col-sm-12 col-xs-12"></div>');
            var message = $('<div class="alert alert-success message"><p align="center">'
                            // +(sfLang=="EN" ? 'Popular varieties' : 'Vari&eacute;t&eacute;s populaires')
                            +(sfLang=="EN" ? "here's a sample of the rarest varieties" : 'Les vari&eacute;t&eacute;s les plus rares')
                            +'</p></div>');

            $('.seeds-results').append(wrapper.append(message));
            
            if(data.bOk) {
                SeedFinderUI.drawResults(data.raOut);
            }
        },
        error : function( jqXHR, textStatus, errorThrown ) { console.log(errorThrown); }
    }).done(function() {
        $.scrollTo(0, 0);
    });


    /************
      Process the Find button
     */
	$("#finder").submit(function(e) {
        e.preventDefault();

		var $this = $(this);
		$('.seeds-results .species').remove();
		$('.seeds-results .topChoices').remove();
		$('.seeds-results .sub-header').remove();
		$('.seeds-results .details').remove();
		$('.seeds-results .details-header').remove();
		
	    $(".fmt1").show();
	    $(".fmt2").hide();
	   
        // do nothing if neither species nor variety selected
        let sp = $('#sfAp_sp').val();
        let cv = $('#sfAp_srch').val();
        if( !parseInt(sp) && !cv.trim() ) {
           console.log("Button not allowed when no crop or search term set");
           return;
       }
	   
console.log($(this).serialize() + "&cmd=find");
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

                    SeedFinderUI.drawResults(data.raOut);
				}
			}
		}).done(function() {
            $.scrollTo($('.results').position().top - 80, 500);
        });
	});

    /************
      Process the Research button
     */
    $("#form-research-form").submit(function(e) {
        e.preventDefault();

        var $this = $(this);

//        $('.seeds-results .species').remove();
//        $('.seeds-results .topChoices').remove();
//        $('.seeds-results .sub-header').remove();
//        $('.seeds-results .details').remove();
//        $('.seeds-results .details-header').remove();
//       
        let sp = $('#sfAp_sp').val();
        if( !parseInt(sp) ) {
           console.log("Button not allowed when no crop or search term set");
           return;
        }

        let sSpecies = $('#sfAp_sp option:selected').text();

        $.ajax({
            type: "GET",
            url: qurl2,
            data: { qcmd: "srcResearch-cvOverYears",
                    kSp: sp
            },
            success: function(data){
                data = window.JSON.parse(data);
                if(!data.bOk){
                    $('section.results h1').text(''); //'Search result');

                    var wrapper = $('<div class="sub-header col-lg-12 col-md-12 col-sm-12 col-xs-12"></div>');
                    var message = $('<div class="alert alert-danger message"><p align="center">'+sLocal_No_matches_found+'</p></div>');
                    wrapper.append(message);
                    $('.seeds-results').append(wrapper);
                }
                
                if(data.bOk) {
                    let sTitle = `Number of distinct cultivars of ${sSpecies} by year`;
                    let sChartDiv = "chart-div";
                    console.log(data.raOut);
                    let raRows = []; // [['2008', 100], ['2010', 200]]; 
                    data.raOut.forEach(function(o){ raRows.push([o.year+" ", parseInt(o.nCV)])});
                    let raCols = [{type:'string',label:"Date"}, 
                                  {type:'number','label':"Cultivars"}];
                    drawChart(sTitle, sChartDiv, raCols, raRows);
                }
            }
        }).done(function() {
            $.scrollTo($('.results').position().top - 80, 500);
        });
    });

	
	
	
	/* Show the companies that sell the variety given by data-kPcv
	 */
/*
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
                
                let kPcv = $this.attr('data-kPcv');
                let sSyn = "";
                
                // Get synonyms
                $.ajax({
                    async: false,
                    type: "POST",
                    url: "https://seeds.ca/app/q/index.php",
                    data: { qcmd: "rosetta-cultivaroverview", kPcv: kPcv },
                    success: function(data) {
                        data = window.JSON.parse(data);
                        if( typeof(data.raOut.raPY) != 'undefined' ) {
                            for(const syn of data.raOut.raPY) {
                                sSyn += (sSyn ? " / " : "") + syn.name;
                            }
                            sSyn = `<br/>(aka ${sSyn})<br/>`;
                        }        
                    },
                }).done(function() {
                    $.scrollTo($('.results').position().top - 80, 200);
                });
                
                // full-width column
                var fullwidth = '<div class="details-header col col-lg-12 col-md-12 col-sm-12 col-xs-12"></div>';

                var headAvailable = '<h2 align="center">' + $this.find('.panel-body').text() 
                  + " "+sp+" "+sSyn
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
*/
/*
    $(window).resize(function() {
        $('.seeds-results .panel').matchHeight();
    });
*/
/*
    $('.modeSelector').click(function(e) {
        $('.modeSelector').removeClass('modeSelected'); 
        $(this).addClass('modeSelected');

        switch( $(this).attr('id') ) {
            case 'modeSelector-find':       drawFinder();     break;
            case 'modeSelector-research':   drawResearch();   break;
        }
    });
*/
});


function drawChart( sTitle, sChartDiv, raCols, raRows ) 
{
    let data = new google.visualization.DataTable();

    raCols.forEach(function(o) { data.addColumn(o.type, o.label)});
    data.addRows(raRows);
    
    let options = {
            title: sTitle,
            vAxis: { minValue:0 }

  //        'width':$nWidth,
  //        'height':$nHeight
  //        .(@$raParms['maxH'] ? ",'hAxis.minValue':0,'hAxis.maxValue':{$raParms['maxH']}" : "")
    };
    var chart = new google.visualization.ColumnChart(document.getElementById(sChartDiv));
    chart.draw(data, options);
}


class SeedFinderUI
{
    static drawResults(raCvList)
    {
        // raCvList is the list of species/cultivars from the database, sorted by (sp, cv).  
        // Group cultivars by species and draw the results. 
        let oSpCv = {};
        for(const [key, value] of Object.entries(raCvList)) {
            if( !(value.S_name_en in oSpCv) ) {
                oSpCv[value.S_name_en] = {sp:value.S_name_en, raCV:[]};
            }
            let sSyn = "";
            if( typeof value.raPY != 'undefined' )  sSyn = value.raPY.join(", ");
            let o = { P_name: value.P_name + (sSyn ? ` <span style='font-size:75%;color:#233449'>(aka ${sSyn})</span>` : ""),
                      nSrc:   value.nSrc,
                      raSrc:  value.raSrc };
            oSpCv[value.S_name_en].raCV.push(o);
        }
        //console.log(oSpCv);

        for(const [key, value] of Object.entries(oSpCv)) {
            let b = 
                `<div class="w-100"></div>
                 <div class="col-xs-12 col-lg-8 offset-lg-2 topChoices ">
                     <div class="widget-block panel-block">
                         <div class="panel panel-default">
                             <div class="panel-heading"><h3 class="panel-title">[[S_name_en]]</h3></div>
                             <div class="panel-body">[[cvblocks]]</div>
                         </div>
                     </div>
                 </div>`;
                
            let sCVBlocks = "";
            value.raCV.forEach(function(v, k) {
                let nSrc = typeof v.nSrc != 'undefined' ? v.nSrc : 0;
                let sSrc = "";
                if(typeof v.raSrc != 'undefined')  v.raSrc.forEach(function(v,k) {
                                                                       let sCmpName = oSLSources[v].name_en;
                                                                       let web = oSLSources[v].web;
                                                                       if(web) {
                                                                           if(web.substr(0,8) != "https://") web = "https://"+web;
                                                                           // stopPropagation allows the link to work but stops the cv-block from closing (click outside the <a> to close the cv-block)
                                                                           sCmpName = `<a href='${web}' target='_blank' onclick='event.stopPropagation()'>${sCmpName}</a>`;
                                                                       }
                                                                       sSrc += sCmpName+"<br/>";
                                                                   });
                sCVBlocks += `<div class='cv-block'>
                           <div class='cv-name'>${v.P_name} <span class='cv-name-nSrc'>(${nSrc})</span></div>
                           <div class='cv-details' style='display:none'><h4>${v.P_name}</h4><p>${sSrc}</p></div>
                       </div>`;
            });
            
            b = b.replace( "[[cvblocks]]", sCVBlocks );
            //b = b.replace( "[[sSynonyms]]", value.sSynonyms ? `(aka ${value.sSynonyms})` : "" );
            //b = b.replace( "[[P__key]]", value.P__key );
            b = b.replace( "[[S_name_en]]", value.sp );
            $('.seeds-results').append( $(b) );   // parse the cvblock into DOM and append as the last child of seeds-results
        }
        
        // event listeners to open the details for each cultivar
        $('.cv-block').click(function() {
            event.stopPropagation();
            $(this).find('.cv-name').slideToggle(200);
            $(this).find('.cv-details').slideToggle(200);
        });
    }
}
