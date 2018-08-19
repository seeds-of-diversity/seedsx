<?php

/*
 * Matt Potts
 * 2011-08-10
 * employeePay.php
 * This file is used for tracking pay for employees and tracking employee reimbursements.
 */

define("SITEROOT","../../");
include_once( SITEROOT."site.php" );
include_once( SEEDCOMMON."siteStart.php" );

list($kfdb,$sess) = SiteStartSessionAccount( array("PAY" => "W") );


function getCurrentDate()
{
	return date("Y-m-d");
}

?>

<!DOCTYPE HTML>
<html>
<head>
	<title>Employee Payment Page</title>
	<link rel="stylesheet" type="text/css" href="timeTrackingStyles.css" />

	<script type="text/javascript">

	/*
	*	This function is called to change the tab to page 1
	*/
	function openPage1()
	{
		document.getElementById("page1").style.display = "block";  // show page 1 and hide page 2
		document.getElementById("page2").style.display = "none";
		document.getElementById("hide").value = 1;  // used for tracking current page
	}

	/*
	*	This function is called to change the tab to page 2
	*/
	function openPage2()
	{
		document.getElementById("page2").style.display = "block";  // show page 2 and hide page 1
		document.getElementById("page1").style.display = "none";
		document.getElementById("hide").value = 2;  // used for tracking current page
	}

	/*
	*	Name:		validate()
	*	Purpose:	This function ensures correct values are inserted into the date and hours fields.
	*	Inputs:		none
	*	Outputs:	Error messages as needed
	*	Returns:	none
	*/
	function validate()
	{
		var hourCheck;
		var submitFlag;
		var dateCheck = checkDate();  // validation

		if (dateCheck == true)  // good value
	    {
		    document.getElementById("dateErrorBox").innerHTML = "";  			// erase any error messages
		    hourCheck = document.getElementById("hoursInputTextbox").value;  	// get hours value

			if ( (isNaN(hourCheck)) || (hourCheck == "") || (hourCheck.length > 4) ) // empty field or bad value
			{
				document.getElementById("hoursErrorBox").innerHTML = "Please enter a valid number!";
				submitFlag = false;
			}
			else
			{
				document.getElementById("hoursErrorBox").innerHTML = "";  // clear error messages
				submitFlag = true;
			}
	    }
	    else
	    {
		    document.getElementById("dateErrorBox").innerHTML = "YYYY/MM/DD";  // display error
		    submitFlag = false;
	    }

		if (submitFlag == true)
		{
			fillResultsTable();
		}
	}

	/*
	*	Name:		checkDate()
	*	Purpose:	This function runs multiple validation checks on the date value from the date input box
	*	Inputs:		None
	*	Outputs:	None
	*	Returns:	True if valid; false if invalid
	*/
	function checkDate()
	{
		var theDate = document.getElementById("dateInputTextbox").value;  // get user input
		var leapYearCheck;

		if (theDate.length !== 10) // ten is the acceptable length of a date string
		{
			return false;
		}

		if ( (theDate.charAt(4) == '-') && (theDate.charAt(7) == '-') )
		{
			theDate = theDate.replace(/-/g, "/");	// convert '-' characters to '/' characters
		}

		if ( (theDate.charAt(4) != '/') || (theDate.charAt(7) != '/') )  // if the date isn't formatted right...
		{
			return false;
		}

		var dateArray = theDate.split("/");	// split string at '/' character
		var year = dateArray[0];			// gets year from string
		var month = dateArray[1];			// gets month and adds one as months start with zero
		var day = dateArray[2]; 			// get day

		if ( (isNaN(year)) || (isNaN(month)) || (isNaN(day)) )  // inserted letters
		{
			return false;
		}

		if ( (day <= 0) || (month <= 0) || (year <= 0) )  // no zero or negative values
		{
			return false;
		}

		if ( (month < 1) || (month > 12) ) // year must be current year and month must be logical
		{
			return false;
		}

		if (day > 31)
		{
			return false;
		}

		if ( (day > 30) && ( (month == 2) || (month == 4) || (month == 6) || (month == 9) || (month == 11) ) ) // sanity check on day value
		{
			return false;
		}

		if ( (day > 29) && (month == 2) )
		{
			return false;
		}

		leapYearCheck = year % 4;	// leap year will yield zero

		if ( (month == 2) && (day == 29) )
		{
			if (leapYearCheck != 0) // feb. 29th can only happen on a year divisible by four (I'm aware of the century issue....)
			{
				return false;
			}
		}

		return true;  // all checks passed
	}

	/*
	*	Name:		validateDollarsTable()
	*	Purpose:	This function validates the dollars table before sending it to the server.
	*	Inputs:		none
	*	Outputs:	The formatted table
	*	Returns:	none
	*/
	function validateDollarsTable()
	{
		var dollars = document.getElementById("dollars").value;
		var reason = document.getElementById("reason").value;
		var submitFlag = true;

		if ( (isNaN(dollars)) || (dollars <= 0) )
		{
			document.getElementById("dollarError").innerHTML = "Please enter a valid dollar amount.";
			submitFlag = false;
		}
		else if (dollars > 1000000)
		{
			document.getElementById("dollarError").innerHTML = "That's probably not true.";
			submitFlag = false;
		}
		else
		{
			document.getElementById("dollarError").innerHTML = "";
		}

		if (reason.length > 35) // 35 is the maximum allowable amount of characters
		{
			document.getElementById("reasonError").innerHTML = "Please be more brief with your answer.";
			submitFlag = false;
		}
		else if (reason.length <= 0) // empty field
		{
			document.getElementById("reasonError").innerHTML = "Please tell us the reason.";
			submitFlag = false;
		}
		else
		{
			document.getElementById("reasonError").innerHTML = "";
		}

		if (submitFlag == true)
		{
			fillDollarsTable();
		}
	}

	/*
	*	Name:		fillResultsTable()
	*	Purpose:	This function takes the date and hours inputs and uses AJAX to query the server
	*				for table data before updating the page.
	*	Inputs:		none
	*	Outputs:	altered table
	*	Returns:	none
	*/
	function fillResultsTable()
	{
		var xmlhttp;
		var theDate = document.getElementById("dateInputTextbox").value;	// Store hours and date values and clear the hours textbox
		var theHours = document.getElementById("hoursInputTextbox").value;
		document.getElementById("hoursInputTextbox").value = "";

		if (window.XMLHttpRequest)
	  	{
	  		xmlhttp = new XMLHttpRequest();  // code for IE7+, Firefox, Chrome, Opera, Safari
	  	}
		else
	  	{
	 		xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");  // code for IE6, IE5
	  	}

 		xmlhttp.onreadystatechange = function()
	  	{
	  		if (xmlhttp.readyState == 4 && xmlhttp.status == 200)
	    	{
	    		document.getElementById("resultsTable").innerHTML=xmlhttp.responseText; // insert the table
	    	}
	  	};

		xmlhttp.open("POST","timeTracking.php",true);
		xmlhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
		xmlhttp.send("hoursInputTextbox=" + theHours + "&dateInputTextbox=" + theDate); // send the date and hours

	}

	/*
	*	Name:		fillDollarsTable()
	*	Purpose:	This function is used to fill a table containing employee reimbursement information
	*	Inputs:		none
	*	Outputs:	a formatted table
	*	Returns:	none
	*/
	function fillDollarsTable()
	{
		var xmlhttp;
		var dollars = document.getElementById("dollars").value;
		var reason = document.getElementById("reason").value;
		document.getElementById("dollars").value = "";
		document.getElementById("reason").value = "";

		if (window.XMLHttpRequest)
	  	{
	  		xmlhttp = new XMLHttpRequest();  // code for IE7+, Firefox, Chrome, Opera, Safari
	  	}
		else
	  	{
	 		xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");  // code for IE6, IE5
	  	}

 		xmlhttp.onreadystatechange = function()
	  	{
	  		if (xmlhttp.readyState == 4 && xmlhttp.status == 200)
	    	{
	    		document.getElementById("dollarResults").innerHTML=xmlhttp.responseText; // insert the table
	    	}
	  	};

		xmlhttp.open("POST","reimbursement.php",true);
		xmlhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
		xmlhttp.send("dollars=" + dollars + "&reason=" + reason); // send the dollar amount and reason
	}

	</script>
</head>

<body onload="fillResultsTable(); fillDollarsTable()">
<div id="tabSpace">
	<input type="button" class="tabButtons" value="Pay" onclick="openPage1()" />			<!-- Tab buttons -->
	<input type="button" class="tabButtons" value="Reimbursements" onclick="openPage2()" />
</div>

<div id="page1">
	<form id="trackingForm" name="trackingForm" action="timeTracking.php" method="POST" >	<!-- The time sheet page -->
	<h2>Seeds of Diversity Time Sheet Entry Page<hr /></h2>

	<table id="page1Table" >
		<tr>
			<td>
				<table id="inputTable" >
					<tr>
						<td>
							<b>Enter the date: </b>
						</td>
						<td>
							<input type="date" id="dateInputTextbox" name="dateInputTextbox" value="<?php echo getCurrentDate(); ?>" /> <!-- default date inserted -->
						</td>
						<td id="dateErrorBox"> <!-- used for displaying a date related error -->
							&nbsp;
						</td>
					</tr>
					<tr>
						<td>
							&nbsp;	<!-- spacing -->
						</td>
					</tr>
					<tr>
						<td>
							<b>Enter the hours worked: </b>
						</td>
						<td>
							<input type="text" id="hoursInputTextbox" name="hoursInputTextbox" />
						</td>
						<td id="hoursErrorBox"> <!-- used for displaying a hours related error -->
							&nbsp;
						</td>
					</tr>
					<tr>
						<td>
							&nbsp;  <!-- spacing -->
						</td>
					</tr>
					<tr>
						<td>
							<input type="button" id="validateButton" value="Add to Timesheet" onclick="validate()" />
						</td>
					</tr>
				</table>
			</td>
			<td>
				<div id="resultsTable"> <!-- holds the timesheet data from the database -->
				</div>

				<span>&nbsp;</span>	<!-- right side padding -->
			</td>
		</tr>
	</table>
	</form>
</div>

<div id="page2">
	<form id="reimbursementForm" name="reimbursementForm" action="reimbursement.php" method="post" >
	<h2>Seeds of Diversity Reimbursement Page <hr /> </h2>
	<div id="dollarDiv" >
		<table id="dollarTable" >
			<tr>
				<td>
					<b>How much do we owe you?</b>
				</td>
				<td>
					$<input type="number" name="dollars" id="dollars" min="0" />
				</td>
				<td id="dollarError" >
					&nbsp;
				</td>
			</tr>
			<tr>
				<td>
					&nbsp;  <!-- spacing -->
				</td>
			</tr>
			<tr>
				<td>
					<b>Why do we owe you money?</b>
				</td>
				<td>
					&nbsp; <input type="text" name="reason" id="reason" /> <!-- spacing added to line up the text boxes -->
				</td>
				<td id="reasonError" >
					&nbsp;
				</td>
			</tr>
			<tr>
				<td>
					&nbsp;  <!-- spacing -->
				</td>
			</tr>
			<tr>
				<td>
					<input type="button" id="reimbursementButton" value="Submit Claim" onclick="validateDollarsTable()" />
				</td>
			</tr>
		</table>
	</div>
	<br /> <br />

	<div id="dollarResults" >
	</div>
	<br /> <br />
	</form>
</div>

<input type="hidden" id="hide" value="1" />
</body>
</html>

