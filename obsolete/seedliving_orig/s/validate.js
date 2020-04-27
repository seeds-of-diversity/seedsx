// JavaScript Document
var validator = 1;
$(document).ready( function() {
	/* Reset Captcha */
	$(".rfr").ready(function() {
		$(".rfr").click(function() {
			var daform;
			 $(this).parents().each(function(){
			 	if($(this).is("form")){
					if($(this).attr("id") == "validateform"){
						daform = $(this);
					}	
				}
			 });
			 
			 $(daform).find("#captcha_image").attr({ 
				src: "/sl/captcha/"+Math.round(Math.random()*1000000)
			});
			$(daform).find("#captcha_code").val("");
		});
	});
	/* Reset Captcha */
	
	/* Validate Form - Button Click */
	$("input[id=validate]").click( function() {
		var msg="";
		var theform;
		/* Find the parent form of the button */
		$(this).parents().each(function(){
				if($(this).is("form")){
					theform = $(this);
					if($(this).attr("id") == "validateform"){
						$(this).find("*").each( function() {
						        $(this).change( function() {
									 if( $(this).val() != "") {
									   $(this).css({backgroundColor:"white"});
									 }
								});
								if($(this).attr("required")=="required"){
										/* No Value */
										if(!$(this).val() && !msg){
											msg = $(this).attr("title")+ " is a required field";
											$(this).focus();
											$(this).css({backgroundColor:"yellow"});
										}
										
										if ($(this).is("select") && $(this).val() == "" && !msg) {
												msg = $(this).attr("title")+ " is a required field";
												$(this).focus();
												$(this).css({backgroundColor:"yellow"});
										} 
										if ($(this).attr("type") == "checkbox" && !msg){
											if(!$(this).attr("checked")){
												msg = $(this).attr("title");
												$(this).focus();
												$(this).css({backgroundColor:"yellow"});
											}
										} 
										
										
										
										/* Check Captcha */
										if($(this).val() && $(this).attr("id") == "captcha_code" && !msg){
											var cap = $.ajax({
											   url: "/sl/slCheckCaptcha-"+$(this).val(),
											   async: false
											}).responseText;
											if(cap==0){
												msg = "We're sorry. The security code that you entered did not match the one displayed. A new code will now be shown. Please try again.";
												$(this).focus();
												$(this).css({backgroundColor:"yellow"});
												$(this).val("");
												$(theform).find("#captcha_image").attr("src","/sl/captcha/?"+Math.round(Math.random()*1000000));
											}
										}
										
										
										/* Check Email */
										if($(this).attr("validateas")=="email" && !msg){
											if(!echeck($(this).val())){
												msg = "Email is invalid";
												$(this).focus();
												$(this).val("");
											}
										}
										
										/* Check Phone */
										if($(this).attr("validateas")=="phone" && !msg){
												alert(validatePhoneNumber($(this).val()));
										}
										
										/* Call Back - Used on the VOW signup to check database for duplicate Emails */
										if($(this).attr("callback") && !msg){
												eval($(this).attr("callback")+"('"+$(this).val()+"')");
												
												if(cmsg){
													msg = cmsg;
													$(this).focus();
													$(this).val("");
													cmsg="";
												}
										}
										
										/* Deprecated - replaced with above */
										if($(this).attr("action") && !msg){
											var check = $.ajax({
												   url: $(this).attr("action")+"&"+$(theform).serialize(),
												   async: false
											}).responseText;
											if(check){
												msg = check+" "+$(this).attr("title");
												$(this).focus();
												$(this).css({backgroundColor:"yellow"});
												$(this).val("");
											}
										}	
								} /* is required logic */
								
								
								/* Used for confirmation i.e. passwords */
								if($(this).attr("sameas") && !msg){
									     
										if($(this).val() != $("#"+$(this).attr("sameas")).val()){
											msg = $(this).attr("sameastxt")
											$("#"+$(this).attr("sameas")).focus();
											$(this).css({backgroundColor:"yellow"});
											$(this).val("");
											$("#"+$(this).attr("sameas")).val("");
										}
								}	
						}); /* Traversing the From */
					} /* Checking form ID attr*/
				} /* Is Form logic*/
		}); /* Search for Parents Loop */
		
						if(!msg){
							if($(theform).attr("type") == "ajax"){
								 eval($(theform).attr("callback")+"()");
							} else if($(theform).attr("type") == "ajaxmsg"){
								$.ajax({
                                                           type: "Post",
									url:$(theform).attr("action")+"?",
									data: $(theform).serialize(),
									cache: false,
									success: function(data){
									  alert($(theform).attr("msg"));
                                                                        $(theform).each(function(){
                                                                            this.reset();
                                                                          });
									}
								 });
							} else $(theform).submit();
						
						} else alert(msg); /* Display Errors */
								
	});/* Validate Form - Button Click */
	
});
function validatePhoneNumber(elementValue){   
	var phoneNumberPattern = /^\(?(\d{3})\)?[- ]?(\d{3})[- ]?(\d{4})$/;   
	return phoneNumberPattern.test(elementValue);   
}  
function echeck(str) {

		var at="@"
		var dot="."
		var lat=str.indexOf(at)
		var lstr=str.length
		var ldot=str.indexOf(dot)
		if (str.indexOf(at)==-1){
		   return false
		}

		if (str.indexOf(at)==-1 || str.indexOf(at)==0 || str.indexOf(at)==lstr){
		   return false
		}

		if (str.indexOf(dot)==-1 || str.indexOf(dot)==0 || str.indexOf(dot)==lstr){

		    return false
		}

		 if (str.indexOf(at,(lat+1))!=-1){
		   
		    return false
		 }

		 if (str.substring(lat-1,lat)==dot || str.substring(lat+1,lat+2)==dot){
		    
		    return false
		 }

		 if (str.indexOf(dot,(lat+2))==-1){
		   
		    return false
		 }
		
		 if (str.indexOf(" ")!=-1){
		    
		    return false
		 }

 		 return true					
}