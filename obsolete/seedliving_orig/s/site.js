	// JavaScript Document
var cmsg;
$(document).ready(function(){
	var pth = window.location.pathname;
	init();
	var loc = pth.split("/");
	if(loc[2]){
		if(loc[2] == "login-1") login();
		else{
			if (eval("typeof " + loc[2] + " == 'function'")) {
				eval(loc[2]+"()");
			} else {
				loc = loc[2].split("-");
				if (eval("typeof " + loc[0] + " == 'function'")){
					eval(loc[0]+"()");
				}
			}
		}
	}
});
function community(){
	$("#slZoneSel").change(function(){
		if($(this).val()){
			var id = parseFloat($(this).val());
			$(".slzone").hide();
			var c=0;
			$(".slzone").each(function(){
				if($(this).attr("st") && $(this).attr("end")){
					if(parseFloat($(this).attr("st"))<=id && parseFloat($(this).attr("end"))>=id){
						$("#slNoZone").hide();
						$(this).show();
						c++;
					}
				}
			});
			if(!c){
				$("#slNoZone").html("There are no tips for zone "+$("#slZoneSel :selected").text());
				$("#slNoZone").show();
			}
		} else { $(".slzone").show(); $("#slNoZone").hide(); }
	});
}
function slDonate(){
	$("#totalSel").change(function(){
		$("#gtot").html("<strong>$"+$(this).val()+".00 (CAD)</strong>");
		$("#grandtotal").val($(this).val());
	});
}
function secureUser(){
	$("#totalSel").change(function(){
		$("#gtot").html("<strong>$"+$(this).val()+".00 (CAD)</strong>");
		$("#grandtotal").val($(this).val());
	});
}
function init(){
	$(".action").click(function(){
		eval($(this).attr("callback"));
	});
	
	$("#slShowNews").click(function(){
		$(this).hide();
		$("#newsListhide").show();
	});
	
	
	$(".basket").click(function(){
		$.ajax({
			type: "POST",
			url: "/sl/updateCart-"+$(this).attr("seedid")+"/",
			success: function(data){
				if(data=="0") alert("Error: All purchased items have to be in the same currency. Meaning you can not have items in your cart of different currency. We are sorry for the inconvenience and are currently looking to resolve this matter.");
				else if(data=="1") alert("Item is already in your cart");
				else{
					$("#cartTotal").html(data);
					alert("Item has been added to your cart");
				}
			}
		});			   
		
		
	});
	
	$(".basket2").click(function(){
		$.ajax({
			type: "POST",
			url: "/sl/updateCart-"+$(this).attr("seedid")+"/",
			success: function(data){
				if(data=="0") alert("Error: All purchased items have to be in the same currency. Meaning you can not have items in your cart of different currency. We are sorry for the inconvenience and are currently looking to resolve this matter.");
				else if(data=="1") alert("Item is already in your cart");
				else{
					$("#cartTotal").html(data);
					alert("Item has been added to your cart");
					window.location = "/sl/mycart/";
				}
			}
		});			   
	});
	
	
	$("#generalSearch").focus(function(){
		$("#generalSearch").val("");
		$("#generalSearch").css("text-align","left");
		$("#generalButton").css("border-color","#666666");
	});
	
	$(".imgOver").click(function(){
		$("#imgMain").attr("src",$(this).attr("src"));
	});
	
	$("#uc_text").focus(function(){
		$(".failure").html("");	
	});						   
	$("#commentSubmit").click(function(){			   	
		if(!$("#uc_text").val()) $(".failure").html("You have not entered a comment.");	
		else {
			$.ajax({
				type: "POST",
				url: "/sl/userCommentSave/",
				data: "uc_text="+$("#uc_text").val()+"&user="+$("#account").val(),
				success: function(data){
					if(data) {
						$(".failure").html(data);
						$.ajax({
							type: "POST",
							url: "/sl/userCommentLoad/",
							data: "@id="+$("#account").val(),
							success: function(data){
								if(data) {
									$("#commentsSpan").html(data);
								}
							}
						});
					}
				}
			});
		}
	});
	
	$("#seedCommentSubmit").click(function(){			   	
		if(!$("#sc_text").val()) $(".failure").html("You have not entered a comment.");	
		else {
			$.ajax({
				type: "POST",
				url: "/sl/seedCommentSave/",
				data: "sc_text="+$("#sc_text").val()+"&seed="+$("#seed").val(),
				success: function(data){
					if(data) {
						$(".failure").html(data);
						$.ajax({
							type: "POST",
							url: "/sl/seedCommentLoad/",
							data: "@id="+$("#seed").val(),
							success: function(data){
								if(data) {
									$("#commentsSpan").html(data);
								}
							}
						});
					}
				}
			});
		}
	});
	
}
function secureNewEdit(){
	$('#new_desc').wysiwyg({ 
    controls: { 
      strikeThrough : { visible : false }, 
      underline     : { visible : true }, 
       
      separator00 : { visible : true }, 
       
      justifyLeft   : { visible : true }, 
      justifyCenter : { visible : false }, 
      justifyRight  : { visible : false }, 
      justifyFull   : { visible : false }, 
       
      separator01 : { visible : false }, 
       
      indent  : { visible : false }, 
      outdent : { visible : false }, 
       
      separator02 : { visible : false }, 
       
      subscript   : { visible : false }, 
      superscript : { visible : false }, 
       
      separator03 : { visible : true }, 
       
      undo : { visible : true }, 
      redo : { visible : true }, 
       
      separator04 : { visible : true }, 
       
      insertOrderedList    : { visible : true }, 
      insertUnorderedList  : { visible : true }, 
      insertHorizontalRule : { visible : true }, 
 
      separator07 : { visible : true }, 
	  
	  insertImage: { visible : false }, 
       
      cut   : { visible : true }, 
      copy  : { visible : true }, 
      paste : { visible : true } 
    } 
  }); 
}
function postNews(){
	$('#new_desc').wysiwyg({ 
    controls: { 
      strikeThrough : { visible : false }, 
      underline     : { visible : true }, 
       
      separator00 : { visible : true }, 
       
      justifyLeft   : { visible : true }, 
      justifyCenter : { visible : false }, 
      justifyRight  : { visible : false }, 
      justifyFull   : { visible : false }, 
       
      separator01 : { visible : false }, 
       
      indent  : { visible : false }, 
      outdent : { visible : false }, 
       
      separator02 : { visible : false }, 
       
      subscript   : { visible : false }, 
      superscript : { visible : false }, 
       
      separator03 : { visible : true }, 
       
      undo : { visible : true }, 
      redo : { visible : true }, 
       
      separator04 : { visible : true }, 
       
      insertOrderedList    : { visible : true }, 
      insertUnorderedList  : { visible : true }, 
      insertHorizontalRule : { visible : true }, 
 
      separator07 : { visible : true }, 
	  
	  insertImage: { visible : false }, 
       
      cut   : { visible : true }, 
      copy  : { visible : true }, 
      paste : { visible : true } 
    } 
  }); 
	
	$("#new_zone").change(function(){
		$("#new_zone2").val($(this).val());		
	});
}
function newAdminApprove(){
		$('#new_desc').wysiwyg({ 
    controls: { 
      strikeThrough : { visible : false }, 
      underline     : { visible : true }, 
       
      separator00 : { visible : true }, 
       
      justifyLeft   : { visible : true }, 
      justifyCenter : { visible : false }, 
      justifyRight  : { visible : false }, 
      justifyFull   : { visible : false }, 
       
      separator01 : { visible : false }, 
       
      indent  : { visible : false }, 
      outdent : { visible : false }, 
       
      separator02 : { visible : false }, 
       
      subscript   : { visible : false }, 
      superscript : { visible : false }, 
       
      separator03 : { visible : true }, 
       
      undo : { visible : true }, 
      redo : { visible : true }, 
       
      separator04 : { visible : true }, 
       
      insertOrderedList    : { visible : true }, 
      insertUnorderedList  : { visible : true }, 
      insertHorizontalRule : { visible : true }, 
 
      separator07 : { visible : true }, 
	  
	  insertImage: { visible : false }, 
       
      cut   : { visible : true }, 
      copy  : { visible : true }, 
      paste : { visible : true } 
    } 
  });
}
function secureMassEmailSend(){
	$('#massmsg').wysiwyg({ 
    controls: { 
      strikeThrough : { visible : false }, 
      underline     : { visible : true }, 
       
      separator00 : { visible : true }, 
       
      justifyLeft   : { visible : true }, 
      justifyCenter : { visible : false }, 
      justifyRight  : { visible : false }, 
      justifyFull   : { visible : false }, 
       
      separator01 : { visible : false }, 
       
      indent  : { visible : false }, 
      outdent : { visible : false }, 
       
      separator02 : { visible : false }, 
       
      subscript   : { visible : false }, 
      superscript : { visible : false }, 
       
      separator03 : { visible : true }, 
       
      undo : { visible : true }, 
      redo : { visible : true }, 
       
      separator04 : { visible : true }, 
       
      insertOrderedList    : { visible : true }, 
      insertUnorderedList  : { visible : true }, 
      insertHorizontalRule : { visible : true }, 
 
      separator07 : { visible : true }, 
	  
	  insertImage: { visible : false }, 
       
      cut   : { visible : true }, 
      copy  : { visible : true }, 
      paste : { visible : true } 
    } 
  }); 
}
function secureEventEdit(){
	$('#event_desc').wysiwyg({ 
    controls: { 
      strikeThrough : { visible : false }, 
      underline     : { visible : true }, 
       
      separator00 : { visible : true }, 
       
      justifyLeft   : { visible : true }, 
      justifyCenter : { visible : false }, 
      justifyRight  : { visible : false }, 
      justifyFull   : { visible : false }, 
       
      separator01 : { visible : false }, 
       
      indent  : { visible : false }, 
      outdent : { visible : false }, 
       
      separator02 : { visible : false }, 
       
      subscript   : { visible : false }, 
      superscript : { visible : false }, 
       
      separator03 : { visible : true }, 
       
      undo : { visible : true }, 
      redo : { visible : true }, 
       
      separator04 : { visible : true }, 
       
      insertOrderedList    : { visible : true }, 
      insertUnorderedList  : { visible : true }, 
      insertHorizontalRule : { visible : true }, 
 
      separator07 : { visible : true }, 
	  
	  insertImage: { visible : false }, 
       
      cut   : { visible : true }, 
      copy  : { visible : true }, 
      paste : { visible : true } 
    } 
  }); 
}
function secureMassEmail(){
	$('#massmsg').wysiwyg({ 
    controls: { 
      strikeThrough : { visible : false }, 
      underline     : { visible : true }, 
       
      separator00 : { visible : true }, 
       
      justifyLeft   : { visible : true }, 
      justifyCenter : { visible : false }, 
      justifyRight  : { visible : false }, 
      justifyFull   : { visible : false }, 
       
      separator01 : { visible : false }, 
       
      indent  : { visible : false }, 
      outdent : { visible : false }, 
       
      separator02 : { visible : false }, 
       
      subscript   : { visible : false }, 
      superscript : { visible : false }, 
       
      separator03 : { visible : true }, 
       
      undo : { visible : true }, 
      redo : { visible : true }, 
       
      separator04 : { visible : true }, 
       
      insertOrderedList    : { visible : true }, 
      insertUnorderedList  : { visible : true }, 
      insertHorizontalRule : { visible : true }, 
 
      separator07 : { visible : true }, 
	  
	  insertImage: { visible : false }, 
       
      cut   : { visible : true }, 
      copy  : { visible : true }, 
      paste : { visible : true } 
    } 
  }); 
}
function secureSwap(){
	$(".slSwapComplete").click(function(){
		var id = $(this).attr("ttid"); 
		$.ajax({
			type: "POST",
			url: "/sl/slCompleteSwap-"+id+"/",
			success: function(data){
				$("#"+id).html("Completed");	
			}
		});
	});
}
function tradetable(){
	$("#slTradeTableSearch ul li").click(function(){
		if($(this).attr("filtertype")=="desc") $(this).attr("filtertype","asc");
		else $(this).attr("filtertype","desc");
		
		$.ajax({
			type: "POST",
			url: "/sl/tradetable/",
			data: "filter="+$(this).attr("filter")+"&filtertype="+$(this).attr("filtertype"),
			success: function(data){
				$("#slTradeTableContents").html(data);	
			}
		});		   
	});
	$("#slTradeTableContents .slTTRow").click(function(){
		var id = $(this).attr("seedid");
		$.ajax({
			type: "POST",
			url: "/sl/slCheckSwapCount/",
			success: function(data){
				if(data=="0") alert("You must register and be a validated SeedLiving member in order to swap.");
				else if(data=="1") {
						var answer = confirm("You do not have any prepaid swaps. Click OK to purchase prepaid swaps.");
						if(answer){
							window.open("/sl/slPurchaseSwaps/","_self");	   
						}
				} else {
					window.open("/sl/slDoTTSwap-"+id+"/","_self");
				}
			}
		});
	});
}
function swap(){
	$("#swapSearch").focus(function(){
		$("#swapSearch").val("");
		$("#swapSearch").css("text-align","left");
		$("#swapButton").css("border-color","#666666");
	});
}
function secureSeeds(){
	$("#secureTable").tablesorter({headers:{0:{sorter:false},11:{sorter:false}}});
	
	$("#seed_tradeopt").change(function(){
		if($(this).val()=="T"){
			var answer = confirm("Putting your item on the trading table means its available at no cost, you will not be required to enter a price. Are you sure you want to do this?");
			if(answer){
				$("#seed_price").attr("disabled","disabled");
				$("#seed_price").attr("required","");
				$("#seed_shipcost").attr("required","");
			} else {
				$(this).val("N");	
			}
		} else if($(this).val()=="S"){
			$("#seed_price").attr("disabled","disabled");
			$("#seed_price").attr("required","");
		}
	});
	
	$("#seed_shipcost").change(function(){
		if(!IsNumeric($(this).val())){
			$(this).val("");
		}
	});
	
	$("#seed_shipcost2").change(function(){
		if(!IsNumeric($(this).val())){
			$(this).val("");
		}
	});
function IsNumeric(sText)
{
   var ValidChars = "0123456789.";
   var IsNumber=true;
   var Char;

 
   for (i = 0; i < sText.length && IsNumber == true; i++) 
      { 
      Char = sText.charAt(i); 
      if (ValidChars.indexOf(Char) == -1) 
         {
		  alert("Invalid shipping cost. Must be a numeric value.");
          IsNumber = false;
         }
      }
   return IsNumber;
   
   }
	
	var tog = false; // or true if they are checked on load 
 	$('#toggle').click(function() { 
    	$("input[type=checkbox]").attr("checked",!tog); 
  	tog = !tog; 
 	}); 
	
	$('#slSetFeature').click(function() { 
 		if($("#featureSeedSelect").val()){
			window.open("/sl/setFeatured-"+$("#featureSeedSelect").val(),"_self");
		} else {
			alert("Please select a seed to feature from the list menu");	
		}
	}); 
	
	$('.slRemoveFeature').click(function() { 
		window.open("/sl/removeFeatured-"+$(this).attr("seedid"),"_self");
	});
	
}
function mycart(){
	$("#secureTable").tablesorter({headers:{0:{sorter:false},4:{sorter:false},5:{sorter:false}}});
	
	var tog = false; // or true if they are checked on load 
 	var cartid="";
	var row=0;
	$('#toggle').click(function() { 
    	$("input[type=checkbox]").attr("checked",!tog); 
  	tog = !tog; 
 	});
	
	$("#slCartActions").change(function(){
		if($(this).val()){
		   $(".tableToggle").each( function () {
				if(this.checked){
					row++;
					$("#"+$(this).attr("cartid")).hide();
					if(cartid) cartid = cartid+","+$(this).attr("cartid");
					else cartid = $(this).attr("cartid");
				}
			});
		  if(cartid) {
			  $("#slCartActions")[0].selectedIndex = 0;
			   $.ajax({
					type: "POST",
					url: "/sl/slDeleteCart/",
					data: "@id="+cartid,
					async: false,
					success: function(data){
						alert("Item removed from your cart");
					}
				}); 
			   var cartC = ($("#secureTable").attr('rows').length-2-row);
			   if(cartC>0) $("#cartTotal").html("<a href=\"/sl/mycart/\">"+cartC+"</a>");
			   else {
					window.open("/sl/emptycart","_self");   
			   }
		  }
		  else alert("Please select an item to remove");  
		}
	});
}
function secureCategories(){
	$("#secureTable").tablesorter({headers:{0:{sorter:false},6:{sorter:false}}});
	
	var tog = false; // or true if they are checked on load 
 	$('#toggle').click(function() { 
    	$("input[type=checkbox]").attr("checked",!tog); 
  	tog = !tog; 
 	}); 
}
function secureNews(){
	$("#secureTable").tablesorter({headers:{0:{sorter:false},6:{sorter:false}}});
	
	var tog = false; // or true if they are checked on load 
 	$('#toggle').click(function() { 
    	$("input[type=checkbox]").attr("checked",!tog); 
  	tog = !tog; 
 	}); 
	
	$("#new_expired").datepicker();
}
function secureTags(){
	$("#secureTable").tablesorter({headers:{0:{sorter:false},6:{sorter:false}}});
	
	var tog = false; // or true if they are checked on load 
 	$('#toggle').click(function() { 
    	$("input[type=checkbox]").attr("checked",!tog); 
  	tog = !tog; 
 	}); 
}
function secureEventAdd(){
	$("#event_startdate").datepicker();
	$("#event_enddate").datepicker();
	$('#event_desc').wysiwyg({ 
    controls: { 
      strikeThrough : { visible : false }, 
      underline     : { visible : true }, 
       
      separator00 : { visible : true }, 
       
      justifyLeft   : { visible : true }, 
      justifyCenter : { visible : false }, 
      justifyRight  : { visible : false }, 
      justifyFull   : { visible : false }, 
       
      separator01 : { visible : false }, 
       
      indent  : { visible : false }, 
      outdent : { visible : false }, 
       
      separator02 : { visible : false }, 
       
      subscript   : { visible : false }, 
      superscript : { visible : false }, 
       
      separator03 : { visible : true }, 
       
      undo : { visible : true }, 
      redo : { visible : true }, 
       
      separator04 : { visible : true }, 
       
      insertOrderedList    : { visible : true }, 
      insertUnorderedList  : { visible : true }, 
      insertHorizontalRule : { visible : true }, 
 
      separator07 : { visible : true }, 
	  
	  insertImage: { visible : false }, 
       
      cut   : { visible : true }, 
      copy  : { visible : true }, 
      paste : { visible : true } 
    } 
  }); 
}
function secureEvents(){
	$("#secureTable").tablesorter({headers:{0:{sorter:false},6:{sorter:false}}});	
	
	var tog = false; // or true if they are checked on load 
 	$('#toggle').click(function() { 
    	$("input[type=checkbox]").attr("checked",!tog); 
  	tog = !tog; 
 	}); 
	
	$("#event_startdate").datepicker();
	$("#event_enddate").datepicker();
}
function login(){
	$(document).bind("keydown", function(e) {  
		if (e.keyCode == 13) {  
			var formData = $("#loginForm").serialize();
			$.ajax({
				type: "POST",
				url: "/sl/authenticate/",
				data: formData,
				success: function(data){
					init();
					if(data) $(".failure").html(data);
					else window.open("/sl/secureUser/","_self");
				}
			});
			
			return false;
		}  
	}); 

	$('#loginButton').click(function() { 
		var formData = $("#loginForm").serialize();
		$.ajax({
			type: "POST",
			url: "/sl/authenticate/",
			data: formData,
			success: function(data){
				init();
				if(data) $(".failure").html(data);
				else window.open("/sl/secureUser/","_self");
			}
		});
	});
}
function account(){
		$("#slSellerFees_pp").attr("disabled","disabled");
	    $("#slSellerFees_pp").attr("required","");
	
	
	$(".seller").click(function(){
		if($(this).val()=="no"){
			$("#slSellerFees").hide();
			$("#slSellerFees_pp").attr("disabled","disabled");
			$("#slSellerFees_pp").attr("required","");
		}
		if($(this).val()=="yes"){
			$("#slSellerFees").show();
			$("#slSellerFees_pp").attr("disabled","disabled");
			$("#slSellerFees_pp").attr("required","");
		}
	});
	
	
	
	$("#slSellerUL4").click(function(){
		$("#slSellerFees_pa").attr("disabled","disabled");
		$("#slSellerFees_pp").attr("disabled","disabled");
		$("#slSellerFees_pp").attr("required","");
		$("#slSellerFees_pa").attr("required","");
	});
	
	$("#slSellerUL2").click(function(){
		$("#slSellerFees_pa").attr("disabled","disabled");
		$("#slSellerFees_pp").attr("disabled","disabled");
		$("#slSellerFees_pp").attr("required","");
		$("#slSellerFees_pa").attr("required","");
	});
	
	
	$("#slSellerPP").click(function(){
		$("#slSellerFees_pa").attr("disabled","disabled");
		$("#slSellerFees_pp").attr("disabled","");
		$("#slSellerFees_pp").attr("required","required");
		$("#slSellerFees_pa").attr("required","");
	});
	
	$("#slSellerPA").click(function(){
		$("#slSellerFees_pa").attr("required","required");
		$("#slSellerFees_pp").attr("required","");
		$("#slSellerFees_pa").attr("disabled","");
		$("#slSellerFees_pp").attr("disabled","disabled");
	});
	
	$('#registerButton').click(function() { 
		var formData = $("#registerForm").serialize();
		$.ajax({
			type: "POST",
			url: "/sl/saveAccount/",
			data: formData,
			success: function(data){
				init();
				if(data) $(".failure").html(data);
				else window.open("/sl/login/","_self");
			}
		});
	});
}
function tableActions(table){
	 var id="";
	 $(".tableToggle").each( function () {
		if(this.checked){
			if(id) id = id + "-" + $(this).attr("eventid");
			else id = $(this).attr("eventid");
			
			if($("#tableAction").val() == "Delete"){
				$("#"+$(this).attr("eventid")).remove();
			}
			
			if($("#tableAction").val() == "Enable"){
				$("#enable_"+$(this).attr("eventid")).html("Y");
			}
			
			if($("#tableAction").val() == "Disable"){
				$("#enable_"+$(this).attr("eventid")).html("N");
			}
		}
	});
	 
	

	$.ajax({
		type: "POST",
		url: "/sl/tableactions/",
		data: "table="+table+"&action="+$("#tableAction").val()+"&id="+id,
		success: function(data){
			$(".failure").html(data);	
		}
	}); 
}
function checkUserName(){
   $.ajax({
	type: "POST",
	url: "/sl/slCheckUserName/",
	data: "@user="+$("#account_username").val(),
	success: function(data){
		cmsg = data;
	}
	});
}
function postEvent(){
	$("#event_startdate").datepicker();
	$("#event_enddate").datepicker();
}
function checkUserEmail(){
    $.ajax({
	type: "POST",
	url: "/sl/slCheckUserEmail/",
	data: "@email="+$("#account_email").val(),
	success: function(data){
		cmsg = data;
	}
	});
}
function Set_Cookie( name, value, expires, path, domain, secure )
{
// set time, it's in milliseconds
var today = new Date();
today.setTime( today.getTime() );

/*
if the expires variable is set, make the correct
expires time, the current script below will set
it for x number of days, to make it for hours,
delete * 24, for minutes, delete * 60 * 24
*/
if ( expires )
{
expires = expires * 1000 * 60 * 60 * 24;
}
var expires_date = new Date( today.getTime() + (expires) );

document.cookie = name + "=" +escape( value ) +
( ( expires ) ? ";expires=" + expires_date.toGMTString() : "" ) +
( ( path ) ? ";path=" + path : "" ) +
( ( domain ) ? ";domain=" + domain : "" ) +
( ( secure ) ? ";secure" : "" );
}
function IsNumeric(sText)
{
   var ValidChars = "0123456789.";
   var IsNumber=true;
   var Char;

 
   for (i = 0; i < sText.length && IsNumber == true; i++) 
      { 
      Char = sText.charAt(i); 
      if (ValidChars.indexOf(Char) == -1) 
         {
		  cmsg = "Is not value input for cost";
          IsNumber = false;
         }
      }
   return IsNumber;
   
   }

