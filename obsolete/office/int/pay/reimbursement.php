<?php

/*
 * Matt Potts
 * reimbursement.php
 * 2011-08-17
 * This page returns a table containing all of the reimbursements an employee has received and requested.
 */

define("SITEROOT","../../");
include_once( SITEROOT."site.php" );
include_once( SEEDCOMMON."siteStart.php" );

list($kfdb,$sess) = SiteStartSessionAccount( array("PAY" => "W") );

function sendTable()
{
    global $kfdb, $sess;

	$keyFrameObject = new KeyFrameDB(SiteKFDB_HOST, SiteKFDB_USERID, SiteKFDB_PASSWORD);  // create a database object
	$table = "<table><tr>
				<th class=\"dollarHeaders\">User Number</th>
				<th class=\"dollarHeaders\">Amount</th>
				<th id=\"reasonHeader\">Reason</th>
				<th class=\"dollarHeaders\">Request Date</th>
				<th class=\"dollarHeaders\">Approved</th>
				</tr>";
	if ($keyFrameObject)
	{
		if ($keyFrameObject->oConn) // successful user connection
		{
			$connectResult = $keyFrameObject->Connect("seeds2");

			if ($connectResult)  // successfully connected to the desired database
			{
				if ( (isset($_POST["dollars"])) && (isset($_POST["reason"])) )	// checking for postback
				{
					$userNumber = $sess->GetUID();
					$reason = $_POST["reason"];	// get the hours and date entered by the user
					$dollars = $_POST["dollars"];
					$date = date("Y-m-d");

					// insert hours
					$query = "INSERT INTO reimbursements (_userNumber, _dollarAmount, _reason, _requestDate, _approved) VALUES (" . $userNumber . ", " . $dollars . ", '" . $reason . "', '" . $date . "', 'no')";
					$result = $keyFrameObject->CursorOpen($query);

					if (!$result)  // query failed
					{
						$keyFrameObject->_errmsg = "";
						$keyFrameObject->_errmsg = $keyFrameObject->GetErrMsg();
					}
				}

				$query = "SELECT _userNumber, _dollarAmount, _reason, _requestDate, _approved FROM reimbursements ORDER BY _requestDate DESC";
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
							$table .= "<tr>";		// begin row

							foreach ($row as $field)	// loop through fields and set the table cells
							{
								$table .= "<td>" . $field . "</td>";
							}

							$table .= "</tr>";						// close row
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

	$table .= "</table>";
	return $table;
}

echo sendTable();

?>