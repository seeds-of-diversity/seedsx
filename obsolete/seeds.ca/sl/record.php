<?php

include_once("../site.php");
include_once( STDINC."SEEDForm.php" );
include_once( STDINC."KeyFrame/KFUIForm.php" );
include_once( SEEDCOMMON."siteStart.php" );
include_once( SEEDCOMMON."sl/sl_desc_db.php" );
include_once( SEEDCOMMON."sl/sl_desc_report.php" );
include_once( SEEDCOMMON."sl/desc/userFormParser.php");
include_once( PAGE1_TEMPLATE );

$lang = site_define_lang();

list($kfdb,$sess) = SiteStartSessionAuth( array('SLDesc'=>'W') ) or die( "Cannot connect to database" );

//var_dump($_REQUEST);

$page1parms = array (
                "lang"      => $lang,
                "title"     => ($lang == "EN" ? "Canadian Seed Library" : "" ),
                "tabname"   => "HPD",
              );


class SLUser
{
    var $kfdb;
    var $sess;
    var $oSLDescDB = NULL;
    var $oSLDescDefs = NULL;    // should this be SL_DescReport in gcgc/varieties.php?
    public $oSLDescReport = NULL;

    var $raSites = array();     // array of array(_values)
    var $raVI = array();        // array of array(_values)

    var $kSiteCurr = 0;
    var $raSiteCur = array();
    var $kVICurr = 0;
    var $raVICurr = array();

    var $kfuSite = NULL;        // KFUIForm for MbrSite created on demand
    var $kfuVI = NULL;          // KFUIForm for VarInst created on demand

    function SLUser( &$kfdb, &$sess )
    {
        $this->kfdb =& $kfdb;
        $this->sess =& $sess;
        $this->oSLDescDB = new SL_DescDB( $this->kfdb, $sess->GetUID() );
        $this->oSLDescDefs = new SL_DescDefs();
        $this->oSLDescReport = new SLDescReportUI( $kfdb );
    }

    function Style()
    {
        return( "<STYLE>"
               .".slUserBody         { border:0px solid green;padding:1em; font-family:verdana,helvetica,sans serif;}"
               ."#slUserColLeft      { border-right:2px ridge gray; padding:10px; }"
               ."#slUserColMiddle    { }"
               ."#slUserColRight     { border-left:2px ridge gray; padding:10px; }"

               .".slUserCol, .slUserCol p, .slUserCol input"
                                   ."{ font-size:9pt;}"
               .".slUserColHead      { color:white; background-color:#aaa; text-align:center; font-size:11pt;font-weight:bold;padding:5px; }"
               .".slUserListItem     { font-family:10pt; margin:5px;}"
               .".slUserListItemCurr { font-family:10pt; margin:5px; font-weight:bold; color:green;}"
               .".slUserBox          { margin:5px; padding:5px; }"
               .".slUserBox p, .slUserBox td { font-size:10pt;}"
               .".slUserBoxBorder    { margin:5px; padding:10px; border:2px solid #333; }"
               .".slUserBoxBorder p, .slUserBoxBorder td { font-size:10pt;}"

               .".slUserForm label { width: 150px; float: left; margin: 2px 4px 6px 4px; text-align: right;}"
               .".slUserForm input { background: #eee;}"  // border: 1px solid #333;
               .".slUserForm input:hover { border: 1px solid #00f; background: #cec; }"
               .".slUserFormButton { background: #ccf; padding: 2px 8px; }"  // border: 1px solid #006;
               .".slUserFormButton:hover { border: 1px solid #f00; padding: 2px 8px; }" // background: #eef;
               .".slUserForm br    { clear: left; }"
               ."</STYLE>" );
    }

    function Init( $kS = NULL, $kV = NULL, $kSp = NULL, $kO = NULL, $kVa = NULL, $kP = NULL, $kVal = NULL, $kR = NULL)
    {
        // parms allow data to be reloaded after an update
        if( $kS === NULL )  $kS = SEEDSafeGPC_GetInt( 'kSite' );
        if( $kV === NULL )  $kV = SEEDSafeGPC_GetInt( 'kVI' );



        $this->kSiteCurr = $this->kVICurr = 0;
        $this->raSiteCurr = array();
        $this->raVICurr = array();


        $this->raSites = $this->oSLDescDB->GetListMbrSite( array('uid'=>$this->sess->GetUID()) );
        $this->raVI    = $this->oSLDescDB->GetListVarInst( array('uid'=>$this->sess->GetUID()) );

        // Only one of kSiteCurr or kVICurr is set simultaneously, because they indicate the display mode
        // However, if kVICurr is set, we also load the corresponding raSiteCurr

        if( $kS && count($this->raSites) ) {
            foreach( $this->raSites as $ra ) {
                if( $ra['_key'] == $kS ) {
                    $this->kSiteCurr = $kS;
                    $this->raSiteCurr = $ra;
                }
            }
        }

        if( $kV && count($this->raVI) ) {
            foreach( $this->raVI as $ra ) {
                if( $ra['_key'] == $kV ) {
                    $this->kVICurr = $kV;
                    $this->raVICurr = $ra;
                }
            }
            foreach( $this->raSites as $ra ) {
                if( $ra['_key'] == $this->raVICurr['fk_mbr_sites'] ) {
                    $this->raSiteCurr = $ra;
                }
            }
        }
    }


    function DrawSitesList()
    {
        $s = "<FORM method='post' action='${_SERVER['PHP_SELF']}'>".SEEDForm_Hidden( 'newSite', 1 )
            ."<P align='right'><INPUT type='submit' value='New Site'/></P></FORM>";

        if( count($this->raSites) ) {
            foreach( $this->raSites as $ra ) {
                $class = ($ra['_key']==$this->kSiteCurr ? 'slUserListItemCurr' : 'slUserListItem');
                $s .= "<DIV class='$class'><A HREF='{$_SERVER['PHP_SELF']}?kSite={$ra['_key']}'>".$ra['sitename']."</A></DIV>";
            }
        }
        return( $s );
    }

    function DrawVarietiesList()
    {
        $s = "";

        if( count($this->raSites) ) {    // allow new Variety Records to be created if there is at least one site registered
            $s = "<FORM method='post' action='${_SERVER['PHP_SELF']}'><DIV style='font-size:10pt;text-align:left;margin-top:1em;'>"
                .SEEDForm_Hidden( 'newVI', 1 )
                ."<INPUT type='submit' value='New Variety Record'/></DIV></FORM>";
        }

        if( count($this->raVI) ) {
            foreach( $this->raVI as $ra ) {
                $class = ($ra['_key']==$this->kVICurr ? 'slUserListItemCurr' : 'slUserListItem');
                $s .= SEEDStd_ArrayExpand( $ra, "<DIV class='$class'><A HREF='{$_SERVER['PHP_SELF']}?kVI=[[_key]]'>[[osp]] : [[oname]] : [[year]]</DIV>" );
            }
        }
        return( $s );
    }

    function DrawMiddle()
    {
        $s = "";

        if( SEEDSafeGPC_GetInt('newSite') ) {
            $kfr = $this->oSLDescDB->kfrelMbrSite->CreateRecord();
            $s .= $this->DrawSiteForm( $kfr );

        } else if( SEEDSafeGPC_GetInt('editSite') && $this->kSiteCurr ) {
            $kfr = $this->oSLDescDB->kfrelMbrSite->GetRecordFromDBKey($this->kSiteCurr);
            $s .= $this->DrawSiteForm( $kfr );

        } else if( SEEDSafeGPC_GetInt( 'newVI') ) {
            $s .= $this->DrawVINewForm();

        } else if( SEEDSafeGPC_GetInt( 'editVI') && $this->kVICurr ) {
            $kfr = $this->oSLDescDB->kfrelSLVarInst->GetRecordFromDBKey($this->kVICurr);
            $s .= $this->DrawVIBox( $this->DrawVIEditForm( $kfr ), true );

        } else if( $this->kSiteCurr ) {
            $s .= "<DIV class='slUserBoxBorder'>"
                 ."<DIV style='float:right'><FORM method='post' action='${_SERVER['PHP_SELF']}'>"
                 .SEEDForm_Hidden('editSite',1)
                 ."<INPUT type='submit' value='Edit this Site'/>"
                 ."</FORM></DIV>"
                 ."<H3>".$this->raSiteCurr['sitename']."</H3>"
                 ."<TABLE border='0' cellpadding='5' cellspacing='0'>"
                 .SEEDStd_ArrayExpand( $this->raSiteCurr,
                     "<TR><TD valign='top'>Location:</TD><TD valign='top'>[[city]] [[province]] [[country]]</TD></TR>"
                    ."<TR><TD valign='top'>Postal code:</TD><TD valign='top'>[[postcode]]</TD></TR>"
                    ."<TR><TD valign='top'>Latitude / Longitude:</TD><TD valign='top'>".(@$this->raSiteCurr['latitude'] ? "[[latitude]] / [[longitude]]" : "")."</TD></TR>"
                 )
                 ."<TR><TD valign='top'>Soil type:</TD><TD valign='top'>"
                     .SEEDStd_ParmsUrlGet(@$this->raSiteCurr['metadata'], 'soiltype');
            if( ($sOther = SEEDStd_ParmsUrlGet(@$this->raSiteCurr['metadata'], 'soiltype_other')) ) {
                $s .= " ( $sOther )";
            }
            $s .= "</TD></TR>"
                 ."</TABLE>"
                 ."</DIV>";  // slUserBoxBorder

        } else if( $this->kVICurr ) {
            $s .= $this->DrawVIBox( $this->DrawVIReport(), false );

        } else {
            $s .= "<DIV class='slUserBox'>"
                 //."<H3 style='text-align:center'>Welcome to your own space in<BR/>Seeds of Diversity's Seed Library!</H3>"
                 ."<P>You can help document Canada's diverse plant varieties by recording observations from the plants in your garden (or your friends' gardens). "
                 ."All of the variety information that you record here will be accessible to the public on this web site, but your personal "
                 ."information (e.g. your name, email address, Site name) is never shown to anyone, and your location can't be identified.</P>"
                 ."<P><B>Your Sites</B><BR/>In the left-hand column, register all the places where your plant observations are made. e.g. your garden, "
                 ."a friend's garden, a farm, etc.  If you have more than one garden, please register them separately. This lets us cross-reference your "
                 ."plant observations with the soil types and climates of the places where the plants are grown.</P>"
                 ."<P><B>Your Variety Records</B><BR/>In the right-hand column, record your plant observations by variety, site, and year.</P>"
                 ."<P>For example, if you grow the same variety in two different years, please make a separate record for each year. "
                 ."As well, if you grow the same variety in two different gardens, please make a separate record for each garden (register "
                 ."them as two Sites in the left-hand column, and specify them in the Variety Record.</P>";

            if( !count($this->raSites) ) {
                $s .= "<P><B>To get started, click on 'New Site' in the left-hand column.</B><BR/>This lets you register your first garden. "
                     ."Then you can record plant varieties grown at that Site.</B></P>";
            }
            $s .= "</DIV>";
        }

        /**
         * Remove later
         */
        //$s = $this->SpeciesObsList();


        return( $s );
    }

    function DrawSiteForm( $kfr )
    {
        $kSite = $kfr->Key();  // 0 = new site

        $oKForm = $this->_getKFUFormSite( $kfr );

        $s = "<FORM method='post' action='${_SERVER['PHP_SELF']}'>"
            ."<DIV class='slUserBox'>"
            ."<FIELDSET class='slUserForm'>"
            ."<LEGEND style='font-weight:bold'>".($kSite ? 'Edit this Site' : 'Register a New Site')."</LEGEND>"
//            ."<TABLE border='0' cellpadding='0' cellspacing='10' align='center'>"
//            ."<TR>".$oKForm->TextTD( 'sitename', "Site name" )."</TR>"
            .$oKForm->Text( 'sitename', "Site name" )."<BR/>"
            .$oKForm->Text( 'city', "City" )."<BR/>"
            .$oKForm->Text( 'province', "Province" )."<BR/>"
            .$oKForm->Text( 'country', "Country" )."<BR/>"
            .$oKForm->Text( 'postcode', "Postal code" )."<BR/>"
            .$oKForm->Text( 'latitude', "Latitude" )."<BR/>"
            .$oKForm->Text( 'longitude', "Longitude" )."<BR/>"

//            ."<TR><TD valign='top'>Soil type</TD><TD valign='top'>"
            .$oKForm->Select( 'soiltype', "Soil type",
                              array( "Don't know" => "Don't know",
                                     "Sandy" => "Sandy", "Sandy Loam" => "Sandy Loam", "Loam" => "Loam", "Loamy Clay" => "Loamy Clay", "Clay" => "Clay", "Other" => "Other" ) )."<BR/>"
//            .SEEDForm_Select( $oKForm->oFormParms->sfParmField('soiltype'),
//                              $oKForm->Value('soiltype') )."</TD>"
            .$oKForm->Text( 'soiltype_other', "Soil type (Other)" )."<BR/>"

            .SEEDForm_Hidden( 'slu_action', 'siteform' )
            .SEEDForm_Hidden( 'kSite', $kSite )
            ."<BR/><LABEL>&nbsp;</LABEL><INPUT type='submit' value='".($kSite ? "Save" : "Add Site")."' class='slUserFormButton' />"
            ."</FIELDSET>"
            ."</DIV></FORM>";
        return( $s );
    }

    function DrawVIBox( $sContent, $bEdit )
    /**************************************
     */
    {
        $s = "<DIV class='slUserBoxBorder'>";
        if( !$bEdit ) {
            $s .= "<DIV style='float:right'><FORM method='post' action='${_SERVER['PHP_SELF']}'>"
                 .SEEDForm_Hidden('editVI',1)
                 .SEEDForm_Hidden('kVI',$this->kVICurr)
                 ."<INPUT type='submit' value='Edit this Record'/>"
                 ."</FORM></DIV>";
        }
        $s .= SEEDStd_ArrayExpand( $this->raVICurr, "<H3>Variety Record for [[oname]] [[osp]]</H3>" )
             ."<TABLE border='0' cellpadding='5' cellspacing='0'>";
        if( $bEdit ) {
// TODO make Variety and Year editable
             $s .= SEEDStd_ArrayExpand( $this->raVICurr,
                 "<TR><TD valign='top'>Species:</TD><TD valign='top'>[[osp]]</TD></TR>"
                ."<TR><TD valign='top'>Variety:</TD><TD valign='top'>[[oname]]</TD></TR>"
                ."<TR><TD valign='top'>Year:</TD><TD valign='top'>[[year]]</TD></TR>" );
        }
        $s .= "<TR><TD valign='top'>Site:</TD><TD valign='top'>".@$this->raSiteCurr['sitename']."</TD></TR>"
             ."</TABLE>"
             .$sContent
             ."</DIV>";  // slUserBoxBorder
        return( $s );
    }

    function DrawVIReport()
    {
        return( $this->oSLDescReport->DrawVIRecord( $this->raVICurr['_key'] ) );

        $s = "Report for this record";
        $s .= "<br>";

        $osp  = $this->raVICurr['osp'];
        $cv   = $this->raVICurr['oname'];
        $year = $this->raVICurr['year'];

        $site = SEEDStd_ArrayExpand($this->raVICurr, @$this->raSiteCurr['sitename']);

        $defsRA = $this->oSLDescDefs->GetDefsRAFromOSP( $osp );
            $codesRA = array_keys($defsRA);
			$codeCount = 0;
       	foreach( $defsRA as $kDef => $def ) {
            $raDO = $this->oSLDescDB->GetListDescObs( array("osp"=>$osp, "oname"=>$cv, "desc_k" => $kDef ) ); //var_dump($raDO);
            	foreach($raDO as $ra){

            		if($ra['Site_sitename']==$site and $ra['VarInst_year']==$year){
            			$s .= $def['l_EN'].": ".$ra['v']."<br>";
            		}
            	}
            	$codeCount ++;
            }
        return( $s );

    }

    function DrawVINewForm()
    /***********************
     */
    {
        $kfr = $this->oSLDescDB->kfrelSLVarInst->CreateRecord();
        $kfr->SetValue( 'year', date("Y") );  // initialize to current year

        $oKForm = $this->_getKFUFormVI( $kfr );

        $raSiteSel = array();
        $raS = $this->oSLDescDB->GetListMbrSite( array('uid'=>$this->sess->GetUID()) );
        foreach( $raS as $ra ) {
            $raSiteSel[$ra['_key']] = $ra['sitename'];
        }

        $raPSPSel = array( 'apple'=>'Apple', 'barley'=>'Barley', 'bean'=>'Bean', 'garlic'=>'Garlic', 'lettuce'=>'Lettuce',
                           'onion'=>'Onion', 'pea'=>'Pea', 'potato'=>'Potato', 'squash'=>'Squash / Pumpkin', 'tomato'=>'Tomato','wheat'=>'Wheat' );

        $s = "<FORM method='post' action='${_SERVER['PHP_SELF']}'>"
            ."<DIV class='slUserBox'>"
            ."<FIELDSET class='slUserForm'>"
            ."<LEGEND style='font-weight:bold'>Start a New Variety Record</LEGEND>"
            .$oKForm->Select( 'fk_mbr_sites', "Site", $raSiteSel )."<BR/>"
            .$oKForm->Select( 'osp', "Species", $raPSPSel )."<BR/>"
            .$oKForm->Text( 'oname', "Variety name" )."<BR/>"
            .$oKForm->Text( 'year', "Year" )."<BR/>"
            .SEEDForm_Hidden( 'slu_action', 'vi_new' )
            ."<BR/><LABEL>&nbsp;</LABEL><INPUT type='submit' value='Create Variety Record' class='slUserFormButton' />"
            ."</FIELDSET>"
            ."</DIV></FORM>";
        return( $s );
    }

    function DrawVIEditForm( $kfr )
    /******************************
     */
    {
    	include( SEEDCOMMON."sl/desc/".$this->raVICurr['osp'].".php" );
        $kVI = $kfr->Key();

        $oKForm = $this->_getKFUFormVI( $kfr );

        $s = "<FORM method='post' action='${_SERVER['PHP_SELF']}'>"
            ."<DIV class='slUserBox'>"
            ."<FIELDSET class='slUserForm-NO-NOT-THIS-IF-THE-DESCRIPTOR-FORMS-ARE-DRAWN-HERE'>"
            ."<LEGEND style='font-weight:bold'>Edit this Variety Record</LEGEND>";


        switch($this->raVICurr['osp']) {
        	case "apple"  : $s .=   appleForm( $this->oSLDescDB, $this->raVICurr['_key']); break;
        	case "bean"   : $s .=    beanForm( $this->oSLDescDB, $this->raVICurr['_key']); break;
        	case "garlic" : $s .=  garlicForm( $this->oSLDescDB, $this->raVICurr['_key']); break;
        	case "lettuce": $s .= lettuceForm( $this->oSLDescDB, $this->raVICurr['_key']); break;
        	case "onion"  : $s .=   onionForm( $this->oSLDescDB, $this->raVICurr['_key']); break;
        	case "pea"    : $s .=     peaForm( $this->oSLDescDB, $this->raVICurr['_key']); break;
        	case "pepper" : $s .=  pepperForm( $this->oSLDescDB, $this->raVICurr['_key']); break;
        	case "potato" : $s .=  potatoForm( $this->oSLDescDB, $this->raVICurr['_key']); break;
        	case "squash" : $s .=  squashForm( $this->oSLDescDB, $this->raVICurr['_key']); break;
        	case "tomato" : $s .=  tomatoForm( $this->oSLDescDB, $this->raVICurr['_key']); break;
        }

        $s .= SEEDForm_Hidden( 'slu_action', 'vi_edit' )
             .SEEDForm_Hidden( 'editVI', 1 )
             .SEEDForm_Hidden( 'kVI', $this->raVICurr['_key'] )
            ."<BR/><LABEL>&nbsp;</LABEL><INPUT type='submit' value='Save' class='slUserFormButton' />"
            ."</FIELDSET>"
            ."</DIV></FORM>";
        return( $s );
    }

	function DoAction()
    {
        $sAction = SEEDSafeGPC_Smart( 'slu_action', array('','siteform','vi_new','vi_edit') );

        if( $sAction == 'siteform' ) {
            if( $this->kSiteCurr ) {
                $kfr = $this->oSLDescDB->kfrelMbrSite->GetRecordFromDBKey( $this->kSiteCurr );
            } else {
                $kfr = $this->oSLDescDB->kfrelMbrSite->CreateRecord();
            }
            $oKFU = $this->_getKFUFormSite( $kfr );
            $oKFU->FetchHTTPParms();   // get http parms into kfr

            $kfr->SetValue( 'uid', $this->sess->GetUID() );  // put this after the parms fetch so a false uid can't be inserted in the http stream
            if( $kfr->IsEmpty('sitename') ) {
                $kfr->SetValue( 'sitename', "Site #".(count($this->raSites)+1) );
            }
            $kfr->PutDBRow();
            $this->Init( $kfr->Key(), 0 );  // reload the data cache
        } else if( $sAction == 'vi_new' ) {
            $kfr = $this->oSLDescDB->kfrelSLVarInst->CreateRecord();
            $oKFU = $this->_getKFUFormVI( $kfr );
            $oKFU->FetchHTTPParms();   // get http parms into kfr

// validate that fk_mbr_sites is real and it belongs to $this->sess->GetUID()
// validate that year is reasonable
            if( $kfr->value('fk_mbr_sites') && $kfr->value('osp') && $kfr->value('oname') && $kfr->value('year') ) {
                $kfr->PutDBRow();
                $this->Init( 0, $kfr->Key() );  // reload the data cache
            }

        } else if( $sAction == 'vi_edit' ) {
            foreach( $_REQUEST as $k => $v ) {
                if( substr( $k, 0, 7 ) == 'garlic_' ) {

                }
            }
        }
    }

    function _getKFUFormSite( &$kfr )
    {
        if( !$this->kfuSite ) {
            $kfrel = $this->oSLDescDB->GetKfrelMbrSite(); // because KeyFrameUIForm takes kfrel by reference
            $this->kfuSite = new KeyFrameUIForm( $kfrel, 'A',
                                                 array('DSParms' => array('urlparms' => array('soiltype'       => 'metadata',
                                                                                              'soiltype_other' => 'metadata') ) ));
        }
        $this->kfuSite->SetKFR( $kfr );
        return( $this->kfuSite );
    }

    function _getKFUFormVI( &$kfr )
    {
        if( !$this->kfuVI ) {
            $kfrel = $this->oSLDescDB->GetKfrelSLVarInst(); // because KeyFrameUIForm takes kfrel by reference
            $this->kfuVI = new KeyFrameUIForm( $kfrel, 'B',
                                               array('DSParms' => array('urlparms' => array('foo' => 'metadata',
                                                                                            'bar' => 'metadata' ) ) ));
        }
        $this->kfuVI->SetKFR( $kfr );
        return( $this->kfuVI );
    }
}


$oSLU = new SLUser( $kfdb, $sess );

//Page1( $page1parms );
Page1Body();

function Page1Body() {
    global $oSLU;

    $myName = $oSLU->sess->GetRealName();
    if( empty($myName) )  $myName = $oSLU->GetEmail();

    $oSLU->Init();
    $oSLU->DoAction();

    echo $oSLU->Style();

    echo "<TABLE border='0' cellspacing='0' cellpadding='0' width='100%'><TR><TD valign='top'>"
      //  ."<H2>Canadian Seed Library</H2></TD>"
        ."<TD valign='top'><DIV style='float:right;text-align:center;font-size:9pt;border:1px solid green;' class='slUserBoxBorder'>"
        ."Records for $myName<BR/>"
        ."<A HREF='".SITEROOT."login?loginMode=logout'>Sign Out</A>"
        ."</DIV></TD>"
        ."</TR></TABLE>"
        ."<DIV class='slUserBody'>"
        ."<TABLE border='0' cellpadding='0' cellspacing='0' width='100%'><TR>"
        ."<TD id='slUserColLeft' valign='top' width='20%'>"
        ."<DIV class='slUserCol'>"
        ."<DIV class='slUserColHead'>My Sites</DIV>"
        .$oSLU->DrawSitesList()
        ."</DIV>"
        ."</TD>"
        ."<TD id='slUserColMiddle' valign='top'>"
        .$oSLU->DrawMiddle()
        .$oSLU->oSLDescReport->Style().$oSLU->oSLDescReport->DrawDrillDown()
        ."</TD>"
        ."<TD id='slUserColRight' valign='top' width='20%'>"
        ."<DIV class='slUserCol'>"
        ."<DIV class='slUserColHead'>My Variety Records</DIV>"
        .$oSLU->DrawVarietiesList()
        ."</DIV>"
        ."</TD>"
        ."</TR></TABLE>"
        ."</DIV>";   // slUserBody

    echo "<DIV style='font-size:9pt; border:1px solid #888;background-color:#eee; padding:1em;'><P>We currently support Variety Records for the following species: "
        ."apples, barley, beans, garlic, lettuce, onions, peas, potatoes, squash (and pumpkins), tomatoes, and wheat.</P>"
        ."<P>This service is a component of the <A href='http://usc-canada.org/what-we-do/canada/bauta-en/'>Bauta Initiative on Canadian Seed Security</A>, "
        ."in partnership with <A href='http://usc-canada.org'>USC Canada</A>.</P>";

}

?>
