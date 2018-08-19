<?php
/*
 * Matt Potts
 * 2011-07-28
 * timeTracking.php
 * This page responds by sending a string containing a table and/or inserting values
 * into the employeePay table. The table contains all of the dates and pay hours an employee has worked.
 */

define("SITEROOT","../../");
include_once( SITEROOT."site.php" );
include_once( SEEDCOMMON."siteStart.php" );


list($kfdb,$sess) = SiteStartSessionAccount( array("PAY" => "W") );

/*
 * Name:	fillTable()
 * Purpose:	Creates an HTML table and fills it with content from a MySQL database. In this case the
 * 			data is employee hours worked and associated dates.
 * Inputs:	none
 * Outputs:	none
 * Returns:	a string containing all of the web content to be echoed later.
 */
function fillTable()
{
    global $kfdb, $sess;

    $keyFrameObject = new KeyFrameDB(SiteKFDB_HOST, SiteKFDB_USERID, SiteKFDB_PASSWORD);  // create a database object
	$tableData = "<table><tr><th>Employee Number</th><th>Date</th><th>Hours Worked</th></tr>";

	if ($keyFrameObject)
	{
		if ($keyFrameObject->oConn) // successful user connection
		{
			$connectResult = $keyFrameObject->Connect("seeds2");

			if ($connectResult)  // successfully connected to the desired database
			{
				if ( (isset($_POST["hoursInputTextbox"])))	// checking for postback
				{
					$userNumber = $sess->GetUID();
					$hoursWorked = $_POST["hoursInputTextbox"];	// get the hours and date entered by the user
					$dateWorked = $_POST["dateInputTextbox"];

					// insert hours
					$query = "INSERT INTO employeePay(_userNumber, _dateWorked, _hoursWorked) VALUES (" . $userNumber . ", '" . $dateWorked . "', " . $hoursWorked . ")";
					$result = $keyFrameObject->CursorOpen($query);

					if (!$result)  // query failed
					{
						$keyFrameObject->_errmsg = "";
						$keyFrameObject->_errmsg = $keyFrameObject->GetErrMsg();
					}
				}

				$query = "SELECT _userNumber, _dateWorked, _hoursWorked FROM employeePay ORDER BY _dateWorked DESC";
				$result = $keyFrameObject->CursorOpen($query);

				if (!$result)  // bad query
				{
					$keyFrameObject->_errmsg = "";
					$keyFrameObject->_errmsg = $keyFrameObject->GetErrMsg();
					return "There is no information available at this time";
				}
				else
				{
					$num = $keyFrameObject->CursorGetNumRows($result);

					if ($num > 0)
					{
						$row = $keyFrameObject->CursorFetch($result, MYSQL_ASSOC);	// get a row from the table

						while ($row)
						{
							$tableData .= "<tr>";		// begin row

							foreach ($row as $field)	// loop through fields and set the table cells
							{
								$tableData .= "<td>" . $field . "</td>";
							}

							$tableData .= "</tr>";						// close row
							$row = $keyFrameObject->CursorFetch($result, MYSQL_ASSOC);	// get another row to continue the loop
						}
					}
				}
			}

			else
			{
				$keyFrameObject->_errmsg = "";
				$keyFrameObject->_errmsg = $keyFrameObject->GetErrMsg();
				return "There is no information available at this time";
			}
		}
		else
		{
			$keyFrameObject->_errmsg = "";
			$keyFrameObject->_errmsg = $keyFrameObject->GetErrMsg();
			return "There is no information available at this time";
		}
	}
	else
	{
		return "There is no information available at this time";
	}

	$tableData .= "</table>";
	return $tableData;
}


echo fillTable();  // send the table data

?>