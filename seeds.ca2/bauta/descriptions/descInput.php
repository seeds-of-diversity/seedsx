<?php

/* Crop Description interface
 *
 * Copyright (c) 2012-2017 Seeds of Diversity Canada
 *
 * UI Methods for recording sites and crop descriptors
 */

include_once( STDINC."KeyFrame/KFUIForm.php" );
include_once( SEEDCOMMON."sl/sl_desc_db.php" );
include_once( SEEDCOMMON."sl/sl_desc_report.php" );
include_once( SEEDCOMMON."sl/desc/_sl_desc.php");  // SLDescForm
include_once( SEEDCOMMON."sl/desc/userFormParser.php");

class CropDescUI
{
    private $kfdb;
    private $sess;
    private $oSLDescDB;
    private $oSLDescDefs;
    private $oSLDescReport;
    private $raSites;
    private $raVI;

    private $sMode = "";
    private $kSite = 0;
    private $kVI = 0;

    private $oFormSite = NULL;
    private $oFormVI = NULL;

    function __construct( KeyFrameDB $kfdb, $sess, $lang )
    {
        $this->kfdb = $kfdb;
        $this->sess = $sess;

        $this->oSLDescDB = new SL_DescDB( $this->kfdb, $sess->GetUID() );
        $this->oSLDescDefs = new SL_DescDefs( $this->oSLDescDB );
        $this->oSLDescReport = new SLDescReportUI( $kfdb, $lang );

        $this->loadData();

        if( ($this->kSite = SEEDSafeGPC_GetInt('editSite')) ) {
            $this->sMode = 'editSite';

        } else if( ($this->kSite = SEEDSafeGPC_GetInt('deleteSite')) ) {
            $this->sMode = 'deleteSite';

        } else if( SEEDSafeGPC_GetInt('newSite') == 1 ) {
            $this->sMode = 'newSite';
            $this->kSite = 0;

        } else if( ($this->kVI = SEEDSafeGPC_GetInt('editVI')) ) {
            $this->sMode = 'editVI';

        } else if( ($this->kVI = SEEDSafeGPC_GetInt('deleteVI')) ) {
            $this->sMode = 'deleteVI';

        } else if( SEEDSafeGPC_GetInt('newVI') == 1 ) {
            $this->sMode = 'newVI';
            $this->kVI = 0;

        } else {
            $this->sMode = "";
            $this->kSite = 0;
            $this->kVI = 0;
        }

        // Security: make sure the selected Site or VI belongs to the current user
        if( $this->kSite ) {
            $bFound = false;
            foreach( $this->raSites as $ra ) {
                if( $ra['_key'] == $this->kSite ) {
                    $bFound = true;
                    break;
                }
            }
            if( !$bFound ) {
                die( "Error: you are trying to access a Site record that isn't yours.  Please contact Seeds of Diversity if you need assistance." );
            }
        }
        if( $this->kVI ) {
            $bFound = false;
            foreach( $this->raVI as $ra ) {
                if( $ra['_key'] == $this->kVI ) {
                    $bFound = true;
                    break;
                }
            }
            if( !$bFound ) {
                die( "Error: you are trying to access an Observation record that isn't yours.  Please contact Seeds of Diversity if you need assistance." );
            }
        }
    }

    function loadData()
    {
        $this->raSites = $this->oSLDescDB->GetListMbrSite( array('uid'=>$this->sess->GetUID()) );
        $this->raVI    = $this->oSLDescDB->GetListVarInst( array('uid'=>$this->sess->GetUID()) );
    }

    function IsModal()
    {
        return( in_array( $this->sMode, array( 'newSite', 'editSite', 'deleteSite', 'newVI', 'editVI', 'deleteVI' ) ) );
    }

    function DoAction()
    {
// var_dump($_REQUEST);

        $cmd = SEEDSafeGPC_GetStrPlain( 'slu_action' );

        if( $cmd == 'siteform' ) {
            $oForm = $this->getKFUFormSite();
            $oForm->Load();   // get http parms into kfr

            $oForm->SetValue( 'uid', $this->sess->GetUID() );  // force this so a false uid can't be inserted in the http stream
            if( !$oForm->Value('sitename') ) {
                $oForm->SetValue( 'sitename', "Site #".(count($this->raSites)+1) );
            }
            $oForm->Store();

            // load new state
            $this->kSite = $oForm->GetKey();
            $this->loadData();
            $this->sMode = 'editSite';
        }
        else
        if( $cmd == 'viform' ) {
            $oForm = $this->getKFUFormVI();
            $oForm->Load();   // get http parms into kfr

// TODO
// validate that fk_mbr_sites is real and it belongs to $this->sess->GetUID()
// validate that year is reasonable

            if( $oForm->Value('fk_mbr_sites') && $oForm->Value('osp') && $oForm->Value('oname') && $oForm->value('year') ) {
                $oForm->Store();

                // load new state
                $this->kVI = $oForm->GetKey();
                $this->loadData();
                $this->sMode = 'editVI';
            } else {
                $this->sMode = 'newVI';
            }
        }
        else
        if( $cmd == 'descUpdate' ) {
            if( $this->kVI ) {
                $oDescForm = new SLDescForm( $this->oSLDescDB, $this->kVI );
                $oDescForm->Update();
            }
        }
    }

    function Style()
    {
        $s = "<style>"
            .".sldescuiCol          { margin-bottom:20px; clear:both; }"
            .".sldescuiColHead      { color:white; background-color:#aaa; text-align:center; font-size:11pt;font-weight:bold;padding:5px; }"
            .".sldescuiListItem     { }" //font-family:10pt; margin:5px;}"
            .".sldescuiListItemCurr { font-weight:bold; color:green; }" //font-family:10pt; margin:5px; }"

            ."</style>";


        return( $s );
    }

    function drawMySites()
    {
        $s = "<div class='sldescuiCol'>"
            ."<div class='sldescuiColHead'>My Sites</div>";

        foreach( $this->raSites as $ra ) {
            $class = ($ra['_key']==$this->kSite ? 'sldescuiListItemCurr' : 'sldescuiListItem');
            $s .= "<DIV class='$class'><A HREF='{$_SERVER['PHP_SELF']}?editSite={$ra['_key']}'>".$ra['sitename']."</A></DIV>";
        }

        if( !count($this->raSites) ) {
            $s .= "<div class='well well-sm' style='clear:both;color:green;'>Here's your first step! Click on New Site to register your first observing site.</div>";
        }

        $s.= "<FORM method='post' action='${_SERVER['PHP_SELF']}'>".SEEDForm_Hidden( 'newSite', 1 )
            ."<div style='float:right'><INPUT type='submit' value='New Site'/></div>"
            ."</FORM>";

        $s .= "</div><br/><br/>";

        return( $s );
    }

    function drawMyVI()
    {
        $s = "<div class='sldescuiCol'>"
            ."<div class='sldescuiColHead'>My Observations</div>";

        $s .= "<div style='clear:both'></div>";

        foreach( $this->raVI as $ra ) {
            $class = ($ra['_key']==$this->kVI ? 'sldescuiListItemCurr' : 'sldescuiListItem');
            $s .= SEEDStd_ArrayExpand( $ra, "<DIV class='$class'><a HREF='{$_SERVER['PHP_SELF']}?editVI=[[_key]]'>[[oname]] [[osp]] [[year]]</a></DIV>" );
        }

        if( count($this->raSites) ) {    // allow new Variety Records to be created if there is at least one site registered
            if( !count($this->raVI) ) {
                $s .= "<div class='well well-sm' style='clear:both;color:green;'>Now you can create a new observation record. Click on New Observation!</div>";
            }
            $s .= "<FORM method='post' action='${_SERVER['PHP_SELF']}'>".SEEDForm_Hidden( 'newVI', 1 )
                 ."<div style='float:right'><INPUT type='submit' value='New Observation'/></div>"
                 ."</FORM>";
        }

        $s .= "</div>";

        return( $s );
    }

    function drawMain()
    {
        $s = "";

        switch( $this->sMode ) {
            case 'newSite':
                $s = $this->drawSiteForm(); //( 0 );
                break;
            case 'editSite':
                $s = $this->drawSiteForm(); //( $this->kSite );
                break;
            case 'newVI':
                $s = $this->drawVIForm(); //( 0 );
                break;
            case 'editVI':
                $s = $this->drawVIForm(); //( $this->kVI );
                break;
            default:
                if( count($this->raSites) ) {
                    $s .= "<p style='font-weight:bold'>Welcome back, ".$this->sess->GetName()."!</p>"
                         ."<p style=''>Your crop observations are part of a growing database of knowledge about Canada's diverse food plants. "
                         ."Whenever you grow a plant variety you haven't tried before, remember to download an observation form and "
                         ."record how it grows in your specific location. We hope you learn a lot by comparing your observations with "
                         ."other growers!</p>";
                } else {
                    $s .= "<p><span style='font-weight:bold'>Welcome, ".$this->sess->GetName()."!</span><br/>"
                         ."We hope you enjoy being part of this initiative to document Canada's food plants.</p>"
                         ."<p>&nbsp;</p>";
                }

                $s .= "<div class='well'>"
                         ."<p><b>Your Sites</b><br/>"
                         ."Register all the places where your plant observations are made. e.g. your garden, "
                         ."a friend's garden, a farm, etc.  If you have more than one location, please register them separately. "
                         ."This lets us cross-reference your plant observations with the soil types and climates of the places where the "
                         ."plants were grown.</p>"
                         ."<p><b>Your Observations</b><br/>"
                         ."Record your plant observations by variety, site, and year.</p>"
                         ."<p>For example, if you grow the same variety in two different years, make a separate record for each year. "
                         ."As well, if you grow the same variety in two different places, please make a separate record for each site.</p>"
                         ."<div class='well' style='color:#F07020;background-color:#fff'>"
                             ."<b>Privacy Notice</b><br/>All the crop observations you record here will be accessible to the public, "
                             ."but your personal information (e.g. your name, email address, Site names) are never shown to anyone else, "
                             ."and your location can't be identified."
                         ."</div>"
                     ."</div>";


        }

        return( $s );
    }


    function _ctrl( $oForm, $fld, $label )
    {
        return( "<label for='sfAp_$fld' class='col-sm-2 control-label'>$label</label>"
               ."<div class='col-sm-10'>"
               ."<input type='text' class='form-control' id='sfAp_$fld' name='sfAp_$fld' placeholder='$label' "
                   ."value='".$oForm->ValueEnt($fld)."'/>"
               ."</div>" );
    }

    function _ctrlB( $oForm, $fld, $label )
    {
        return( "<label for='sfBp_$fld' class='col-sm-2 control-label'>$label</label>"
               ."<div class='col-sm-10'>"
               ."<input type='text' class='form-control' id='sfBp_$fld' name='sfBp_$fld' placeholder='$label' "
                   ."value='".$oForm->ValueEnt($fld)."'/>"
               ."</div>" );
    }

    function drawSiteForm()
    {
        $oKForm = $this->getKFUFormSite();

        $s = "<form class='form-horizontal' role='form' method='post' action='${_SERVER['PHP_SELF']}'>"
            //."<DIV class='slUserBox'>"
            ."<FIELDSET class='slUserForm'>"
            ."<LEGEND style='font-weight:bold'>".($this->kSite ? 'Edit this Site' : 'Register a New Site')."</LEGEND>"

            .$oKForm->HiddenKey()

// isn't there a way to do this with offset-sm-2 ?
            ."<div class='col-sm-2'>&nbsp;</div>"
            ."<div class='col-sm-10'>"
                ."<p style='font-size:9pt;'>Privacy note: None of this Site information is ever visible to other people using the crop description records. "
                ."We only use your location to anonymously match your crop observations with others in your area.</p>"
            ."</div>"
            ."<div class='form-group'>"
            .$this->_ctrl( $oKForm, 'sitename', 'Site name' )
            ."</div>"

            ."<div class='form-group'>"
            .$this->_ctrl( $oKForm, 'address', 'Address' )
            .$this->_ctrl( $oKForm, 'city', 'City' )
            .$this->_ctrl( $oKForm, 'province', 'Province' )
            .$this->_ctrl( $oKForm, 'country', 'Country' )
            .$this->_ctrl( $oKForm, 'postcode', 'Postcode' )
            ."</div>"

            ."<div class='form-group'>"
            .$this->_ctrl( $oKForm, 'latitude', 'Latitude (optional)' )
            .$this->_ctrl( $oKForm, 'longitude', 'Longitude (optional)' )
            ."</div>"

//            .$oKForm->Text( 'city', "City" )."<BR/>"
//            .$oKForm->Text( 'province', "Province" )."<BR/>"
//            .$oKForm->Text( 'country', "Country" )."<BR/>"
//            .$oKForm->Text( 'postcode', "Postal code" )."<BR/>"
//            .$oKForm->Text( 'latitude', "Latitude" )."<BR/>"
//            .$oKForm->Text( 'longitude', "Longitude" )."<BR/>"

//            .$oKForm->Select( 'soiltype', "Soil type",
//                              array( "Don't know" => "Don't know",
//                                     "Sandy" => "Sandy", "Sandy Loam" => "Sandy Loam", "Loam" => "Loam", "Loamy Clay" => "Loamy Clay", "Clay" => "Clay", "Other" => "Other" ) )."<BR/>"
//            .$oKForm->Text( 'soiltype_other', "Soil type (Other)" )."<BR/>"

            .SEEDForm_Hidden( 'slu_action', 'siteform' )
//            .SEEDForm_Hidden( 'kSite', $this->kSite )

            ."<div class='form-group'>"
                ."<label class='col-sm-2'>&nbsp;</label>"
                ."<div class='col-sm-10'>"
                ."<input class='btn' type='submit' value='".($this->kSite ? "Save" : "Add Site")."' class='slUserFormButton' />"
                ."</div>"
            ."</div>"

            ."</FIELDSET>"
            //."</DIV>"
            ."</form>";

        return( $s );
    }

    private $raSpecies = array(
        'apple'      => array( 'hardform'=>true,  'l_en'=>"Apple" ),
        'barley'     => array( 'hardform'=>false, 'l_en'=>'Barley' ),
        'bean'       => array( 'hardform'=>true,  'l_en'=>'Bean' ),
        'beet'       => array( 'hardform'=>false, 'l_en'=>'Beet' ),
        'buckwheat'  => array( 'hardform'=>false, 'l_en'=>'Buckwheat' ),
        'brassica'   => array( 'hardform'=>false, 'l_en'=>'Cabbage, Kale, and other brassica relatives', 'showDefaultFullForm'=>true ),
        'carrot'     => array( 'hardform'=>false, 'l_en'=>'Carrot' ),
        'celery'     => array( 'hardform'=>false, 'l_en'=>'Celery' ),
        'corn'       => array( 'hardform'=>false, 'l_en'=>'Corn' ),
        'cucumber'   => array( 'hardform'=>false, 'l_en'=>'Cucumber' ),
        'eggplant'   => array( 'hardform'=>false, 'l_en'=>'Eggplant' ),
        'garlic'     => array( 'hardform'=>true,  'l_en'=>'Garlic' ),
        'lentil'     => array( 'hardform'=>false, 'l_en'=>'Lentil' ),
        'lettuce'    => array( 'hardform'=>true,  'l_en'=>'Lettuce' ),
        'onion'      => array( 'hardform'=>true,  'l_en'=>'Onion' ),
        'pea'        => array( 'hardform'=>true,  'l_en'=>'Pea' ),
        'potato'     => array( 'hardform'=>true,  'l_en'=>'Potato' ),
        'quinoa'     => array( 'hardform'=>false, 'l_en'=>'Quinoa' ),
        'radish'     => array( 'hardform'=>false, 'l_en'=>'Radish' ),
        'rye'        => array( 'hardform'=>false, 'l_en'=>'Rye' ),
        'spinach'    => array( 'hardform'=>false, 'l_en'=>'Spinach' ),
        'squash'     => array( 'hardform'=>true,  'l_en'=>'Squash / Pumpkin' ),
        'soybean'    => array( 'hardform'=>false, 'l_en'=>'Soybean' ),
        'tomato'     => array( 'hardform'=>true,  'l_en'=>'Tomato' ),
        'watermelon' => array( 'hardform'=>false, 'l_en'=>'Watermelon' ),
        'wheat'      => array( 'hardform'=>false, 'l_en'=>'Wheat' )
    );

    function drawVIForm()
    {
        $s = "";

        $oForm = $this->getKFUFormVI();

        if( !$oForm->Value('year') ) {
            $oForm->SetValue( 'year', date("Y") );  // initialize to current year
        }

        $raSiteSel = array();
        $raS = $this->oSLDescDB->GetListMbrSite( array('uid'=>$this->sess->GetUID()) );
        if( count($raS) > 1 ) {
            $raSiteSel[0] = "--- Choose Site ---";  // only put this in if there are more than one site
        }
        foreach( $raS as $ra ) {
            $raSiteSel[$ra['_key']] = $ra['sitename'];
        }

        $raSpSel = array( ''=>'--- Choose Species ---' );
        foreach( $this->raSpecies as $k => $ra ) {
            $raSpSel[$k] = $ra['l_en'];
        }

        $s = "<form class='form-horizontal' role='form' method='post' action='${_SERVER['PHP_SELF']}'>"
            //."<DIV class='slUserBox'>"
            ."<FIELDSET class='slUserForm'>"
            ."<LEGEND style='font-weight:bold'>".($this->kVI ? 'Edit this Observation Record' : 'Start a New Observation Record')."</LEGEND>"
            .$oForm->HiddenKey();

        if( !count($this->raVI) ) {
            // Say this when it's the first observation
            $s .= "<p style='font-weight:bold'>Your first crop observation!</p>";
        }
        if( !$this->kVI ) {
            // Say this when it's a new observation (not updating an existing record)
            $s .= "<p>Do you have a plant that you've described using our printed forms? Or that you're planning to describe?</p>"
                 ."<ul>"
                 ."<li>Choose the Site where the observations are made (if you haven't registered that Site yet, click the New Site button now, and register it).</li>"
                 ."<li>Select the crop species.</li>"
                 ."<li>Enter the variety name. If you don't know the variety name, it won't be possible to connect your description with other growers' observations.</li>"
                 ."<li>Enter the year of your observations. e.g. if you filled in a printed form last year, and you're entering them now, use that year. "
                 ."If you're planning to make observations in the current year, use this year. You can come back here any time to fill in the form.</li>"
                 ."<li>Then click the Create Observation button to start filling in the observation form.</li>"
                 ."</ul>";
        }

        $s .= "<div class='form-group'>"
                ."<label for='sfBp_fk_mbr_sites' class='col-sm-2 control-label'>Site</label>"
                ."<div class='col-sm-10'>"
                .$oForm->Select( 'fk_mbr_sites', "", $raSiteSel, array( 'classes'=> "form-control" ) )
                ."</div>"
            ."</div>"
            //.$oForm->Select( 'fk_mbr_sites', "Site", $raSiteSel )."<BR/>"

            ."<div class='form-group'>"
                ."<label for='sfBp_osp' class='col-sm-2 control-label'>Species</label>"
                ."<div class='col-sm-10'>"
                .$oForm->Select( 'osp', "", $raSpSel, array( 'classes'=> "form-control" ) )
                ."</div>"
            ."</div>"
            //.$oForm->Select( 'osp', "Species", $raPSPSel )."<BR/>"

            ."<div class='form-group'>"
            .$this->_ctrlB( $oForm, 'oname', 'Variety name' )
            ."</div>"
            ."<div class='form-group'>"
            .$this->_ctrlB( $oForm, 'year', 'Year' )
            ."</div>"
            //.$oForm->Text( 'oname', "Variety name" )."<BR/>"
            //.$oForm->Text( 'year', "Year" )."<BR/>"


            .SEEDForm_Hidden( 'slu_action', 'viform' )

            ."<div class='form-group'>"
                ."<label class='col-sm-2'>&nbsp;</label>"
                ."<div class='col-sm-10'>"
                ."<input class='btn' type='submit' value='".($this->kVI ? "Save" : "Create Observation Record")."' />"
                ."</div>"
            ."</div>"

            ."</FIELDSET>"
            //."</DIV>"
            ."</form>";




//        $s .= "<hr/>";
        $s .= "<br/>";

        if( $this->kVI ) {
            $sLink = $_SERVER['PHP_SELF']."?editVI=".$this->kVI;

            if( SEEDSafeGPC_GetStrPlain('slu_action')=='viform' ) {
                // just submitted a new viForm save, so show the obsform first because the summary is empty and it's natural
                $bShowForm = true;
            } else {
                $bShowForm = SEEDSafeGPC_GetInt( 'showObsForm' );
            }

            //$cSum = $bShowForm ? "btn-primary btn-sm" : "btn-primary btn-lg";
            //$cForm = $bShowForm ? "btn-primary btn-lg" : "btn-primary btn-sm";

            $s .= "<ul class='nav nav-tabs'>"
                 ."<li class='".(!$bShowForm ? 'active' : '')."'><a href='$sLink'>Summary</a></li>"
                 ."<li class='".( $bShowForm ? 'active' : '')."'><a href='$sLink&showObsForm=1'>Show Observation Form</a></li>"
                 ."</ul>";


            $eForm = 'default';


            if( $bShowForm ) {
                $raForms = $this->getFormsForSp( $oForm->Value('osp') );

                /* Let the user select a form, unless there is only one
                 */
                if( count($raForms) == 1 ) {
                    // first element of associative array
                    reset($raForms);
                    $eForm = key($raForms);
                } else {
                    if( !($eForm = SEEDSafeGPC_GetStrPlain( 'showVIForm' )) ) {
                        // first element of associative array
                        reset($raForms);
                        $eForm = key($raForms);
                    }

                    $s .= "<div class='panel panel-default'><div class='panel-body'>"
                         ."<form action='".Site_path_self()."' method='post'>"
                         .SEEDForm_Hidden( 'editVI', $this->kVI )    // propagate to top
                         .SEEDForm_Hidden( 'showObsForm', 1 )    // propagate to top
                         ."Choose a form: "
                         .SEEDForm_Select( 'showVIForm', $raForms, $eForm, array('selectAttrs'=>"onchange='submit()'") )
                         //."<input type='submit' value='Show Form'/>"
                         ."</form>"
                         ."</div></div>";
                }

                $s .= $this->drawObservationForm( $eForm );
            } else {
                $s .= "<div style='border-left:1px solid #ddd;border-bottom:1px solid #ddd'>"
                     .$this->oSLDescReport->DrawVIRecord( $this->kVI, false )   // don't show the basic info (already in the form above)
                     ."</div>";
            }
        }

        return( $s );
    }

    private function getFormsForSp( $osp )
    {
        $raForms = array();

        if( $this->raSpecies[$osp]['hardform'] ) {
            // hard-coded forms are referenced by the species name
            $raForms[$osp] = "Full Form";
        }

// decide whether to store forms in docrep, or in a special table using SLDescDB
        if( ($dbc = $this->kfdb->CursorOpen( "SELECT * FROM sl_desc_cfg_forms WHERE _status=0 AND species='$osp'" )) ) {
            while( $ra = $this->kfdb->CursorFetch($dbc) ) {
                // soft forms are referenced by their _key
                $raForms[$ra['_key']] = $ra['title'];
            }
        }

        // Generate a full form from soft data if no forms are defined or if this is specified
        if( @$this->raSpecies[$osp]['showDefaultFullForm'] || count($raForms)==0 ) {
            // default form is referenced by 'default'
            $raForms['default'] = "Full Form";
        }

        return( $raForms );
    }

    function drawObservationForm( $eForm )
    {
        if( !$this->kVI )  return( "" );

        $kfr = $this->oSLDescDB->GetKfrelSLVarInst()->GetRecordFromDBKey( $this->kVI );
        if( !$kfr )  return( "" );

        $kVI = $kfr->Key();
        $osp = $kfr->Value('osp');

//        $oKForm = $this->_getKFUFormVI( $kfr );

        $s = "<FORM method='post' action='${_SERVER['PHP_SELF']}'>"
            ."<DIV style='border:1px solid #eee;padding:10px'>";
//            ."<FIELDSET class='slUserForm-NO-NOT-THIS-IF-THE-DESCRIPTOR-FORMS-ARE-DRAWN-HERE'>"
//            ."<LEGEND style='font-weight:bold'>Edit this Variety Record</LEGEND>";

        /* eForm is either:
               default    = make a form from all the soft-coded tags for the current species
               {psp}      = use a hard-coded form (should be one of the hard-coded species names)
               {number}   = the _key of a sl_desc_cfg_form
         */
        if( @$this->raSpecies[$eForm]['hardform'] ) {
            include( SEEDCOMMON."sl/desc/".$kfr->Value('osp').".php" );

            switch( $kfr->Value('osp') ) {
                case "apple"  : $s .=   appleForm( $this->oSLDescDB, $this->kVI); break;
                case "bean"   : $s .=    beanForm( $this->oSLDescDB, $this->kVI); break;
                case "garlic" : $s .=  garlicForm( $this->oSLDescDB, $this->kVI); break;
                case "lettuce": $s .= lettuceForm( $this->oSLDescDB, $this->kVI); break;
                case "onion"  : $s .=   onionForm( $this->oSLDescDB, $this->kVI); break;
                case "pea"    : $s .=     peaForm( $this->oSLDescDB, $this->kVI); break;
                case "pepper" : $s .=  pepperForm( $this->oSLDescDB, $this->kVI); break;
                case "potato" : $s .=  potatoForm( $this->oSLDescDB, $this->kVI); break;
                case "squash" : $s .=  squashForm( $this->oSLDescDB, $this->kVI); break;
                case "tomato" : $s .=  tomatoForm( $this->oSLDescDB, $this->kVI); break;
            }
        } else {
            include_once( SEEDCOMMON."sl/desc/_sl_desc.php" );
            $oF = new SLDescForm( $this->oSLDescDB, $this->kVI );
            $oF->Update();

            $oF->LoadDefs( $osp );

            $s .= $oF->Style();

            if( $eForm == 'default' ) {
                $s .= $oF->DrawDefaultForm( $osp );

            } else if( ($kForm = intval($eForm)) ) {
                $ra = $this->kfdb->QueryRA( "SELECT * from sl_desc_cfg_forms WHERE _key='$kForm'" );
                if( $ra['species'] == $osp ) {
                    $s .= $oF->DrawFormExpandTags( $ra['form'] );
                }
            }
        }


        $s .= SEEDForm_Hidden( 'slu_action', 'descUpdate' )
             .SEEDForm_Hidden( 'editVI', $this->kVI )
             //.SEEDForm_Hidden( 'kVI', $this->kVI )
            ."<BR/><LABEL>&nbsp;</LABEL><INPUT type='submit' value='Save' class='slUserFormButton' />"
//            ."</FIELDSET>"
            ."</DIV></FORM>";

        return( $s );
    }

    private function getKFUFormSite()
    {
        if( !$this->oFormSite ) {
            $kfrel = $this->oSLDescDB->GetKfrelMbrSite(); // because KeyFrameUIForm takes kfrel by reference
            $kfr = $this->kSite
                       ? $kfrel->GetRecordFromDBKey( $this->kSite )
                       : $kfrel->CreateRecord();

            $this->oFormSite = new KeyFrameUIForm( $kfrel, 'A' );
                                                 //array('DSParms' => array('urlparms' => array('soiltype'       => 'metadata',
                                                 //                                             'soiltype_other' => 'metadata') ) ));
            $this->oFormSite->SetKFR( $kfr );
        }
        return( $this->oFormSite );
    }

    private function getKFUFormVI()
    {
        if( !$this->oFormVI ) {
            $kfrel = $this->oSLDescDB->GetKfrelSLVarInst(); // because KeyFrameUIForm takes kfrel by reference
            $kfr = $this->kVI
                       ? $kfrel->GetRecordFromDBKey( $this->kVI )
                       : $kfrel->CreateRecord();

            $this->oFormVI = new KeyFrameUIForm( $kfrel, 'B' );
                                               //array('DSParms' => array('urlparms' => array('foo' => 'metadata',
                                               //                                             'bar' => 'metadata' ) ) ));
            $this->oFormVI->SetKFR( $kfr );
        }
        return( $this->oFormVI );
    }



}

?>
