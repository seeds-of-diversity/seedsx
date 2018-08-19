/* SEEDSession.js
 * 
 * AJAX support for SEEDSession login and account control
 *
 * Copyright (c) 2015 Seeds of Diversity Canada
 */

function SEEDSessionUser_Login( raParms )
/****************************************
    raParms =
        { urlAuth:    where to request authentication
          urlSuccess: where to go if login succeeds
        }  
 */
{
	// this works on firefox but not chrome
	$(document).bind("keydown", function(e) {
		if( e.keyCode == 13 ) {
			e.preventDefault();		// not sure whether this or the return value is the way to stop the default action
			ssu_loginCheck( raParms );
			return false;
		}
	}); 

	$('#SEEDSessionUser_loginButton').click(function() { 
		ssu_loginCheck( raParms );
	});
}
function ssu_loginCheck( raParms )
/*********************************
    Ask the server whether the given login credentials are valid
 */
{
// TODO: use https
	var urlAuth = raParms['urlAuth'];	// ajax request to authenticate parameters
	var urlSuccess = raParms['urlSuccess'];  // where to go if successful
	var formData = $("#SEEDSessionUser_loginForm").serialize();

	$("#SEEDSessionUser_resultMsg").html("");
	$('#SEEDSessionUser_loginWaitImg').show(); // alert("A"); // uncomment this, you can see the img shown but it doesn't animate in chrome
	var o = SEEDJX( urlAuth, formData );
	$('#SEEDSessionUser_loginWaitImg').hide();
	if( !o ) {
		$("#SEEDSessionUser_resultMsg").html("Sorry, there is a server problem");		
	} else {
		var bOk = o['bOk'];
		var sOut = o['sOut'];

		if( bOk ) {
			$("#SEEDSessionUser_resultMsg").html("<span style='color:green'>Welcome back! Loading your profile</span>");	// for when the server is slow opening the secureUser page
			$('#SEEDSessionUser_loginWaitImg').show();
			window.open( urlSuccess, "_self" );
		} else {
			$("#SEEDSessionUser_resultMsg").html(sOut);
		}
	}
}


$(document).ready(function()
{
	$("#SEEDSessionUser_createAccountForm").submit(function(e) {
		// This just checks that you've typed something. The createAccount-1 step validates that e.g. the email address is not already registered  
		if( !$("#acctCreate_uid").val() ) {
			e.preventDefault();
			alert( "Please type an email address" );
			return( false );
		}
		return( true );
    });
});