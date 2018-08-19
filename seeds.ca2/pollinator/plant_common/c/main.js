/*
 * Image preview script 
 * powered by jQuery (http://www.jquery.com)
 * 
 * written by Alen Grakalic (http://cssglobe.com)
 * 
 * for more info visit http://cssglobe.com/post/1695/easiest-tooltip-and-image-preview-using-jquery
 *
 */
 
this.imagePreview = function(){	
	/* CONFIG */
		
		xOffset = 10;
		yOffset = 30;
		
		// these 2 variable determine popup's distance from the cursor
		// you might want to adjust to get the right result
		
	/* END CONFIG */
	$("a.preview").hover(function(e){
		this.t = this.title;
		this.title = "";	
		var c = (this.t != "") ? "<br/>" + this.t : "";
		$("body").append("<p id='preview'><img src='"+ this.href +"' alt='Image preview' style='width:120px; height:120px;' />"+ c +"</p>");								 
		$("#preview")
			.css("top",(e.pageY - xOffset) + "px")
			.css("left",(e.pageX + yOffset) + "px")
			.fadeIn("fast");						
    },
	function(){
		this.title = this.t;	
		$("#preview").remove();
    });	
	$("a.preview").mousemove(function(e){
		$("#preview")
			.css("top",(e.pageY - xOffset) + "px")
			.css("left",(e.pageX + yOffset) + "px");
	});	
	
};


// starting the script on page load
$(document).ready(function(){
	imagePreview();
	
	$("a.preview").click(function(event){
		event.preventDefault();
		var thisId = this.id;
		
		jxData = { jx     : 'plantdetails',
		           thisID : thisId,
                   lang   : "EN"
                 };

        o = SEEDJX( "jx.php", jxData );
/*    
		$.ajax({
		type: "GET",
		data: "jx=showplant&thisID="+thisId,
		url: "jx.php"
		}).done(function(data){
			$('#sp_logo').hide();
			 //$('#plant_details').html("foo");
			 $('#new_Plant').hide();
			 $('#edit_Plant').hide();
			  $('#plant_details').show();
			 
		});
*/        
        if( o['bOk'] ) {
            $('#plant_details').html(o['sOut']);
            $('#sp_logo').hide();
            $('#new_Plant').hide();
            $('#edit_Plant').hide();
            $('#plant_details').show();
        }
	});	
	
	$("a.edit").click(function(event){
		event.preventDefault();
		var thisId = this.id;
		
        jxData = { jx     : 'plantedit',
                   thisID : thisId,
                   lang   : "EN"
                 };

        o = SEEDJX( "jx.php", jxData );

/*		
		$.ajax({
		type: "GET",
		data: "jx=edit&thisID="+thisId,
		url: "jx.php"
		}).done(function(data){
			$('#new_Plant').hide();
			$('#plant_details').hide();
			$('#sp_logo').hide();
			 $('#edit_Plant').html(data);
			 $('#edit_Plant').show();
		});
*/	
	
        if( o['bOk'] ) {
            $('#edit_Plant').html(o['sOut']);
            $('#sp_logo').hide();
            $('#new_Plant').hide();
            $('#plant_details').hide();
            $('#edit_Plant').show();
        }
	});	
	
	$("#new_index").click(function(event){
	event.preventDefault();
	window.location.replace("admin.php");
	});
	
	$("#new").click(function(event){
		event.preventDefault();
		$("#new_Plant").show();
		$("#edit_Plant").hide();
		$('#plant_details').hide();
	});

});