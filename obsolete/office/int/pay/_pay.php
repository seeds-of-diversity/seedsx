<?php

// mysql script for creating the tables I use for employees;
define("SEEDS2_DB_TABLE_EMPLOYEEPAY", "

CREATE TABLE employeePay
(
	_key 			int(11) PRIMARY KEY NOT NULL AUTO_INCREMENT,
	_userNumber		int(11),
	_dateWorked		date,
	_hoursWorked	double
);

");

define("SEEDS2_DB_TABLE_REIMBURSEMENTS", "

CREATE TABLE reimbursements
(
	_key			int(11) PRIMARY KEY NOT NULL AUTO_INCREMENT,
	_userNumber		int(11),
	_dollarAmount	double,
	_reason			varchar(100),
	_requestDate	date,
	_approved		varchar(4)
);

");

function Pay_Setup( $oSetup, &$sReport, $bCreate = false )
/*********************************************************
    Test whether the tables exist.
    bCreate: create the tables and insert initial data if they don't exist.

    Return true if exists (or create is successful if bCreate); return a text report in sReport

    N.B. $oSetup is a SEEDSetup.  This file doesn't include SEEDSetup.php because the setup code is very rarely used.
         Instead, the code that calls this function knows about SEEDSetup.
 */
{
    return( ($oSetup->SetupTable( "employeePay", SEEDS2_DB_TABLE_EMPLOYEEPAY, $bCreate, $sReport )) &&
    	  	($oSetup->SetupTable( "reimbursements", SEEDS2_DB_TABLE_REIMBURSEMENTS, $bCreate, $sReport )) );
}

?>