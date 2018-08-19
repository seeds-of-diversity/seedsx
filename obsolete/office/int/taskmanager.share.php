<?php
/* Shared by:
    taskmanager.php
    login/index.php
 */


define("SEEDS2_DB_TABLE_TASK_TASKS",
"
CREATE TABLE task_tasks (
        _key        INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT,
        _created    DATETIME,
        _created_by INTEGER,
        _updated    DATETIME,
        _updated_by INTEGER,
        _status     INTEGER DEFAULT 0,

    fk_SEEDSession_Users    INTEGER NOT NULL,
    category                VARCHAR(100),
    priority                ENUM('NOW','URGENT','NORMAL','LATER','ARCHIVE') NOT NULL DEFAULT 'NORMAL',
    private                 BOOL NOT NULL DEFAULT 0,
    startdate               DATE,
    enddate                 DATE,
    status                  ENUM('NEW','IN PROGRESS','BLOCKED','DONE','CANCELLED') NOT NULL DEFAULT 'NEW',
    title                   VARCHAR(100),
    details                 TEXT,
    comments                TEXT,

    INDEX (fk_SEEDSession_Users),
    INDEX (priority),
    INDEX (category),
    INDEX (status)
);
"
);

// formatted for use with KFRForm_Select
$raTaskPriority = array( "NOW" => "Now", "URGENT" => "Urgent", "NORMAL" => "Normal", "LATER" => "Later", "ARCHIVE" => "Archive" );
$raTaskStatus   = array( "NEW" => "New", "IN PROGRESS" => "In&nbsp;Progress", "BLOCKED" => "Blocked", "DONE" => "Done", "CANCELLED" => "Cancelled" );


$kfrelDef_taskmanager =
    array( "Tables"=>array( array( "Table" => 'task_tasks',
                                   "Type" => "Base",
                                   "Fields" => array( array("col"=>"fk_SEEDSession_Users", "type"=>"K"),
                                                      array("col"=>"category",             "type"=>"S"),
                                                      array("col"=>"priority",             "type"=>"S", "default"=>"NORMAL"),
                                                      array("col"=>"private",              "type"=>"I"),
//                                                    array("col"=>"startdate",            "type"=>"S"),
                                                      array("col"=>"enddate",              "type"=>"S"),
                                                      array("col"=>"status",               "type"=>"S", "default"=>"NEW"),
                                                      array("col"=>"title",                "type"=>"S"),
                                                      array("col"=>"details",              "type"=>"S"),
                                                      array("col"=>"comments",             "type"=>"S") ) ),
                            array( "Table" => 'SEEDSession_Users',
                                   "Type" => "Lookup",
                                   "Alias" => "Users",
                                   "Fields" => array( array("col"=>"realname",          "type"=>"S") ) ) ) );





function TasksGetUrgentList( $kfdb, $sess )
/******************************************
 */
{
    global $kfrelDef_taskmanager;

    $raTasks = array();

    if( $sess->CanRead( "TASK" ) ) {
        $kfrel = new KeyFrameRelation( $kfdb, $kfrelDef_taskmanager, $sess->GetUID() );

        if( ($kfr = $kfrel->CreateRecordCursor( "fk_SEEDSession_Users='".$sess->GetUID()."' AND (status <> 'DONE' AND status <> 'CANCELLED') AND "
                                                ."(priority in ('NOW','URGENT') OR (enddate IS NOT NULL AND enddate <> '0000-00-00' AND enddate <= DATE_ADD(CURDATE(),INTERVAL 7 DAY)))", array("sSortCol"=>"enddate") )) ) {
            while( $kfr->CursorFetch() ) {
                $raTasks[] = array( "_key"     => $kfr->Key(),
                                    "category" => $kfr->Value('category'),
                                    "priority" => $kfr->Value('priority'),
                                    "private"  => $kfr->Value('private'),
                                    "enddate"  => ($kfr->Value('enddate') != '0000-00-00' ? $kfr->Value('enddate') : ""),
                                    "status"   => $kfr->Value('status'),
                                    "title"    => $kfr->Value('title')
                                   );
            }
        }
    }
    return( $raTasks );
}


function Tasks_Setup( $oSetup, &$sReport, $bCreate = false )
/***********************************************************
    Test whether the tables exist.
    bCreate: create the tables and insert initial data if they don't exist.

    Return true if exists (or create is successful if bCreate); return a text report in sReport

    N.B. $oSetup is a SEEDSetup.  This file doesn't include SEEDSetup.php because the setup code is very rarely used.
         Instead, the code that calls this function knows about SEEDSetup.
 */
{
    return( $oSetup->SetupTable( "task_tasks", SEEDS2_DB_TABLE_TASK_TASKS, $bCreate, $sReport ) );
}


?>
