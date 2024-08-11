<?php

include_once( SEEDROOT."Keyframe/KeyframeForm.php" );
include_once( SEEDLIB."sl/profiles/sl_profiles_db.php" );
include_once( SEEDLIB."sl/profiles/sl_profiles_defs.php" );
include_once( SEEDLIB."sl/profiles/sl_profiles_report.php" );
include_once( SEEDLIB."sl/profiles/sl_profiles_form.php" );
//include_once( SEEDCOMMON."sl/desc/_sl_desc.php");  // SLDescForm
//include_once( SEEDCOMMON."sl/desc/userFormParser.php");

class CropProfiles
{
    public $oProfilesDB;
    public $oProfilesDefs;
    public $oProfilesReport;

    function __construct( SEEDAppConsole $oApp )
    {
        $this->oProfilesDB = new SLProfilesDB( $oApp );
        $this->oProfilesDefs = new SLProfilesDefs( $this->oProfilesDB );
        $this->oProfilesReport = new SLProfilesReport( $this->oProfilesDB, $this->oProfilesDefs, $oApp );
    }
}
