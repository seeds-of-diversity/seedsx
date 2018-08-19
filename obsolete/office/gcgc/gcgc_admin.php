<?
// Past Varieties under Current Varieties

// Actiform->Draw() doesn't write the kfui->HiddenFormParms. It's only by some fluke that kfui knows which row we're on
// when an actiform action is performed (because the actiform doesn't propagate kfui state). Symptom is that immediately
// after Add Grower, the fluke doesn't happen because although we use a kluge to tell kfui which row we're on, something
// about the browser state doesn't know that and accidentally propagate it.

// SENT insert gxv, gxv:SENTYmd
// FORM grower:FORMYmd:vars, gxv:FORMYmd
// DROP VARIETY grower:DROPYmd:vars, deactivate gxv, gxv:DROPYmd

// Do the right thing with gxv
// Report all current growers, all current requests, email lists, addresses in xls


/* Use KFUIApp_ListForm to manage GCGC Growers
 */

if( !defined("SITEROOT") )  define("SITEROOT", "../");
include_once( SITEROOT."site.php" );
include_once( STDINC."SEEDForm.php" );
include_once( STDINC."SEEDTickList.php" );
include_once( STDINC."KeyFrame/KFUIAppSimple.php" );
include_once( SEEDCOMMON."siteApp.php" );
include_once( SEEDCOMMON."console/_console01.php" );
include_once( "_gcgc.php" );

list($kfdb, $sess) = SiteStartSessionAccount( array("gcgcadmin" => "W") );
//$kfdb->KFDB_SetDebug(2);
//print_r($_REQUEST);

if( empty($mode) )  $mode = SEEDSafeGPC_Smart( "mode", array("C","CS","GA","G","V","S","R") );

switch( $mode ) {
    case "C":   $sModeName = "Console";     break;
    case "GA":  $sModeName = "Add Grower";  break;
    case "G":   $sModeName = "Growers";     break;
    case "V":   $sModeName = "Varieties";   break;
    case "S":   $sModeName = "Samples";     break;
    case "R":   $sModeName = "Reports";     break;
}


echo console01_style();

SiteApp_Header( "Seeds of Diversity's Great Canadian Garlic Collection",
     "<TABLE border=0 cellpadding=10><TR>"
    ."<TD style='font-size:8pt'>"
    ."<A HREF='${_SERVER['PHP_SELF']}?mode=C'>Console</A><BR>"
    ."<A HREF='${_SERVER['PHP_SELF']}?mode=CS'>Console (Send)</A><BR>"
    ."<A HREF='${_SERVER['PHP_SELF']}?mode=GA'>Add Grower</A><BR>"
    ."<A HREF='${_SERVER['PHP_SELF']}?mode=V'>Varieties</A><BR>"
    ."<A HREF='${_SERVER['PHP_SELF']}?mode=R'>Reports</A><BR>"
    ."</TD><TD style='font-size:24pt'>"
    .$sModeName
    ."</TD><TD style='font-size:8pt'>"
    ."<A HREF='${_SERVER['PHP_SELF']}?mode=G'><FONT color='gray'>Growers</FONT></A><BR>"
    ."<A HREF='${_SERVER['PHP_SELF']}?mode=S'><FONT color='gray'>Samples</FONT></A>"
    ."</TD></TR></TABLE>" );

define("GCGC_LOGFILE", SITE_LOG_ROOT."GCGC.log");

$raParms = array( "kfLogFile"=>GCGC_LOGFILE );

if( $mode == "C" || $mode == "CS" ) {
    class myActiform extends KFUIApp_Class_Actiform {
        var $kfrelV;
        var $kfrelS;
        var $reqSelMode = "Request";

        function myActiform() {
            global $kfdb, $sess, $kfrelDef_GCGC_Varieties, $kfrelDef_GCGC_Samples;

            $this->kfrelV = new KeyFrameRelation( $kfdb, $kfrelDef_GCGC_Varieties, $sess->GetUID() );
            $this->kfrelS = new KeyFrameRelation( $kfdb, $kfrelDef_GCGC_Samples, $sess->GetUID() );

            $this->KFUIApp_Class_Actiform();
        }

        function Action()
        {
            global $kfdb, $sess;

            $raNotes = array();
            $raWorkflow = array();

            /* We have to remove all action parms from the KFUI so they don't get propagated.
             * There should be an Actiform definition for this so Actiform can do it.
             */
            foreach( $this->kfui->userParms as $k => $v ) {
                if( substr($k,0,10) == 'gcgcadmin_' )  unset($this->kfui->userParms[$k]);
            }

            /* Get the action. Note that a submission can be made through Javascript without setting this parm,
               so process action=="" to catch changes in status, etc.
             */
            $sAction = SEEDSafeGPC_Smart( "gcgcadmin_action",
                                          array("","Save","Request","Send","Form","Drop","Add Grower","Cancel Request") );

            // Add Grower (this comes from the AddGrower page, which transitions to Console)
            if( $sAction == "Add Grower" ) {
                if( !($kMbr = SEEDSafeGPC_GetInt("gcgcadmin_gakey")) ) {
                    return;
                }
                if( $kfdb->KFDB_Query1("SELECT _key FROM gcgc_growers WHERE fk_mbr_contacts='$kMbr'") ) {
                    // don't insert duplicates - easy to do if you click reload after insertion
                    return;
                }

                $kfr = $this->kfrel->CreateRecord();
                $kfr->SetValue("fk_mbr_contacts", $kMbr);
                $kfr->SetValue("status", "NEW");
                // do this so we have a Key in case we need it below
                if( !$kfr->PutDBRow() ) {
                    echo "<FONT color='red'>Error: Cannot Add Grower</FONT>";
                }
                $this->kfui->SetKey( $this->kfuiCid, $kfr->Key() );     // kluge: have to tell kfui about its new row
                $this->kfui->uiComps[$this->kfuiCid]->bFocusKeyRowInList = true;    // KLUGE! all of this needs to be done consistently in kfui.DoAction
                $raNotes[] = "Added grower";
                $raWorkflow[] = "NEW".date("Ymd");
            } else {
                $kfr =& $this->kfui->GetKFR($this->kfuiCid);
            }
            if( !$kfr || !$kfr->Key() ) return;


            // Change status if needed
            $gStatus = SEEDSafeGPC_Smart( "gcgcadmin_growerstatus", array("","NEW","ACTIVE","PENDING-ACTIVE","INACTIVE","STOPPED") );
            if( !empty($gStatus) && $gStatus != $kfr->value('status') ) {
                $kfr->SetValue( 'status', $gStatus );
                $raNotes[] = "Changed status to $gStatus";
                $raWorkflow[] = $gStatus.date("Ymd");
            }

            // Cancel Requests
            if( $sAction == "Cancel Request" ) {
                /* The variety to cancel is coded as the _key that corresponds to the index_name in the workflow.
                 * If two requests are made with the same name, we just remove the first one that matches.
                 *  -- Actually, if there are two requests for the same variety, the checkbox name will be the same
                 *     for both, so only one parm is found in _REQUEST, so only one of them is deleted even if you
                 *     check more than one
                 */
                $raV = array();
                $raVarNames = $this->getReqCurrVarNames();
                foreach( $raVarNames as $kVar => $sVarName ) {
                    /* Remove 'REQUEST{YYYYMMDD}:$sVarName' from the workflow
                     *
                     * Todo: Write this as a generic SEEDTickList function
                     */
                    $raWF2 = array();
                    $bFound = false;
                    foreach( explode("\n", $kfr->Value('workflow')) as $t ) {
                        if( !$bFound &&
                            substr( $t, 0, 11 ) == "REQUEST".date("Y") &&
                            substr( $t, 16 ) == $sVarName )
                        {
                            $bFound = true;
                            $raV[] = $sVarName;
                        } else {
                            $raWF2[] = $t;
                        }
                    }
                    $kfr->SetValue( "workflow", implode("\n", $raWF2) );
                    // increment the available samples when the request is cancelled
                    // N.B. this could lead to incorrectly high nAvailable if kfr Put fails
                    $kfdb->KFDB_Execute( "UPDATE gcgc_varieties SET nAvailable=nAvailable+1 "
                                         ."WHERE _key='$kVar'" );
                }
                if( count($raV) ) {
                    $raNotes[] = "Request cancelled:".implode(",",$raV);
                }
            }

            // Send varieties
            if( $sAction == "Send" ) {
                $raV = array();
                $raVarNames = $this->getReqCurrVarNames();
                foreach( $raVarNames as $kVar => $sVarName ) {
                    $raV[] = $sVarName;
                    // grower workflow
                    $raWorkflow[] = "SENT".date("Ymd").":".$sVarName;
                    // create the gxv record
                    $kfrS = $this->kfrelS->CreateRecord();
                    $kfrS->SetValue( 'fk_gcgc_growers', $kfr->Key() );
                    $kfrS->SetValue( 'fk_gcgc_varieties', $kVar );
                    $kfrS->SetValue( 'year_start', date("Y") );
                    $kfrS->SetValue( 'status', 'ACTIVE' );
                    $kfrS->SetValue( 'workflow', "SENT".date("Ymd")."\n" );
                    $kfrS->PutDBRow();
                }
                if( count($raV) ) {
                    $raNotes[] = "Sent:".implode(",",$raV);
                }
            }


            // Request varieties
            if( $sAction == "Request" ) {
                $raV = array();
                foreach( array(1,2,3) as $i ) {
                    if( ($kVar = SEEDSafeGPC_GetInt("gcgcadmin_sel_reqsent$i")) ) {
                        $sVar = $kfdb->KFDB_Query1("SELECT index_name FROM gcgc_varieties WHERE _key='$kVar'");
                        if( !empty($sVar) ) {
                            $raV[] = $sVar;
                            // decrement the available samples when they are requested
                            $kfdb->KFDB_Execute( "UPDATE gcgc_varieties SET nAvailable=nAvailable-1 "
                                                 ."WHERE nAvailable > 0 AND _key='$kVar'" );
                            // grower workflow
                            $raWorkflow[] = "REQUEST".date("Ymd").":".$sVar;
                        }
                    }
                }
                if( count($raV) ) {
                    $raNotes[] = "Requested:".implode(",",$raV);
                }
            }

            // Form or Drop
            if( $sAction == "Form" || $sAction == "Drop" ) {
                $raV = array();
                foreach( $_REQUEST as $k => $v ) {
                    if( (substr($k,0,19) != ($sAction=='Drop' ? 'gcgcadmin_checkdrop' : 'gcgcadmin_checkform')) ||
                        ($v != 1) ||
                       !($kS = intval( substr($k,19) )) ) continue;
                    $sVarName = $kfdb->KFDB_Query1("SELECT V.index_name FROM gcgc_gxv S, gcgc_varieties V "
                                                  ."WHERE V._key=S.fk_gcgc_varieties AND S._key='$kS'");
                    if( !empty($sVarName) ) {
                        $raV[] = $sVarName;

                        $sWorkflow = ($sAction == "Drop" ? "DROP" : "FORM").date("Ymd");
                        // grower workflow
                        $raWorkflow[] = $sWorkflow.":".$sVarName;
                        // update the gxv record
                        if( ($kfrS = $this->kfrelS->GetRecordFromDBKey( $kS )) ) {
                            $kfrS->SetValuePrepend( "workflow", $sWorkflow."\n" );
                            $kfrS->SetValue( "year_last_verified", date("Y") );
                            if( $sAction == "Drop" ) {
                                $kfrS->SetValue( "status", "DROPPED" );
                            }
                            $kfrS->PutDBRow();
                        }
                    }
                }
                if( count($raV) ) {
                    $raNotes[] = ($sAction == "Drop" ? "Dropped:" : "Form received:").implode(",",$raV);
                }
            }


            // Notes
            $notes = SEEDSafeGPC_GetStrPlain( "gcgcadmin_notes" );
            if( !empty($notes) ) {
                $raNotes[] = "<FONT color='green'>$notes</FONT>";
            }

            // Update the database
            if( count($raNotes) ) {
                $kfr->SetValuePrepend( "notes", "<FONT color='blue'>[".$sess->GetRealName()." ".date("Y-m-d",time())."]</FONT>\n".implode("\n",$raNotes )."\n" );
            }
            if( count($raWorkflow) ) {
                $kfr->SetValuePrepend( "workflow", implode("\n",$raWorkflow )."\n" );
            }

            if( !$kfr->PutDBRow() ) { echo "<P><FONT color='red'>ERROR</FONT></P>"; }

            $this->kfui->ReloadKFR($this->kfuiCid);     // seems to be needed in PHP4
        }

        function getReqCurrVarNames()
        // get varieties._key=>index_name that have been checked in Cancel Requests or Send checkboxes
        {
            global $kfdb;

            $raVarNames = array();

            foreach( $_REQUEST as $k => $v ) {
                if( (substr($k,0,17) != 'gcgcadmin_reqcurr') ||
                    ($v != 1) ||
                   !($kVar = intval( substr($k,17) )) ) continue;
                $sVarName = $kfdb->KFDB_Query1("SELECT index_name FROM gcgc_varieties WHERE _key='$kVar'");
                if( !empty($sVarName) ) {
                    $raVarNames[$kVar] = $sVarName;
                }
            }
            return( $raVarNames );
        }

        function Draw()
        {
            $kGrower = $this->kfui->GetKey($this->kfuiCid);
            if( $kGrower ) {
                echo "<FORM method='post' action='${_SERVER['PHP_SELF']}'>"
                    .$this->kfui->DrawHiddenFormParms($this->kfuiCid)
                    ."<TABLE border='0' width='100%' cellspacing='0' cellpadding='3'>"
             //     ."<TR><TD valign='top'><CENTER><INPUT type='submit' value='          Save          '></CENTER><BR></TD></TR>"
                    ."<TR>"
                    ."<TD valign='top' width='25%'><DIV class='console01_controlbox'>".$this->drawGrowerSummary()."</DIV></TD>"
                    ."<TD width='3%'>&nbsp;</TD>"
                    ."<TD valign='top' width='20%'><DIV class='console01_controlbox'>".$this->drawVarietyReqSent()."</DIV></TD>"
                    ."<TD width='3%'>&nbsp;</TD>"
                    ."<TD valign='top' width='20%'><DIV class='console01_controlbox'>".$this->drawVarietyCurrent()."</DIV></TD>"
                    ."<TD width='3%'>&nbsp;</TD>"
                    ."<TD valign='top' width='20%'><DIV class='console01_controlbox'>".$this->drawNotes()."</DIV>"
                    ."<BR><CENTER><INPUT type='submit' value='          Save          '></CENTER></TD>"
              //    ."</TR><TR><TD><BR><CENTER><INPUT type='submit' value='          Save          '></CENTER></TD>"
                    ."</TR></TABLE></FORM>";
            } else {
                echo "Choose a grower from the list above";
            }
        }

        function drawGrowerSummary()
        {
            global $kfdb;

            $s = "<DIV class='console01_controlbox_label'>Grower Summary</DIV>";

            $s .= mbr_drawAddress( $kfdb, $this->kfui->GetValue($this->kfuiCid, 'fk_mbr_contacts'), array("bPhone"=>1,"bEmail"=>1) )
//              .(intval(substr($this->kfui->GetValue($this->kfuiCid, "M_expires"),0,4)) < date("Y")
//                              ? "<FONT color='red'>NOT A MEMBER</FONT>"
//                              : "<FONT color='green'>CURRENT MEMBER</FONT>")
                ."<BR>"
                ."Status <SELECT name='gcgcadmin_growerstatus' onChange='submit();'>";
            foreach( array( "NEW"=>"New", "ACTIVE"=>"Active", "PENDING-ACTIVE"=>"Pending Confirmation",
                            "INACTIVE"=>"Inactive", "STOPPED"=>"Stopped" )
                     as $val=>$label ) {
                $s .= KFRForm_Option( $this->kfui->GetKFR($this->kfuiCid), 'status', $val, $label );
            }
            $s .= "</SELECT>"
                 ."<BR>"
                 ."<DIV class='console01_notes_readonly' style='max-width:25em'><PRE>".$this->kfui->GetValue($this->kfuiCid, 'notes')."</PRE></DIV>";

            return( $s );
        }

        function drawVarietyReqSent()
        {
            global $kfdb;

            $s = "<DIV class='console01_controlbox_label'>".$this->reqSelMode." ".date("Y")."</DIV>";

            $raReq     = SEEDTickList_GetRAParmsByTickPrefix( $this->kfui->GetValue($this->kfuiCid, 'workflow'),
                                                              "REQUEST".date("Y") );
            $raSent    = SEEDTickList_GetRAParmsByTickPrefix( $this->kfui->GetValue($this->kfuiCid, 'workflow'),
                                                              "SENT".date("Y") );
            if( count($raReq) ) {
                $s .= "<B>Current requests:</B>";
                $s .= "<TABLE border='0' cellspacing='5' cellpadding='0'>";
                $bAction = false;
                foreach( $raReq as $sVarName ) {
                    if( ($kVar = $kfdb->KFDB_Query1("SELECT _key FROM gcgc_varieties WHERE index_name='".addslashes($sVarName)."'")) ) {
                        $s .= "<TR>"
                             ."<TD valign='top'>$sVarName</TD>"
                             ."<TD valign='top' align='center'>";
                        if( in_array( $sVarName, $raSent ) ) {
                            $s .= "(Sent)";
                        } else {
                            $s .= "<INPUT type='checkbox' name='gcgcadmin_reqcurr".$kVar."' value='1'>";
                            $bAction = true;
                        }
                        $s .= "</TD></TR>";
                    }
                }
                $s .= "</TABLE>";
                if( $bAction ) $s .= "<INPUT type='submit' name='gcgcadmin_action' value='"
                                     .($this->reqSelMode=='Request' ? "Cancel Request" : "Send")."'>";
            }

            if( $this->reqSelMode == "Request" ) {
                $s .= "<P><B>Add Requests:</B></P>";
                $sVars = "<OPTION value=''> --- Choose a Variety --- </OPTION>";
                if( ($kfr = $this->kfrelV->CreateRecordCursor( "nAvailable > 0", array('sSortCol'=>'index_name') ) ) ) {
                    while( $kfr->CursorFetch() ) {
                        $sVars .= SEEDForm_Option( $kfr->Key(), ($kfr->Value('index_name')." (".$kfr->Value('nAvailable').")") );
                    }
                    $kfr->CursorClose();
                }

                $s .= "<SELECT name='gcgcadmin_sel_reqsent1'>".$sVars."</SELECT>\n<BR><BR>"
                     ."<SELECT name='gcgcadmin_sel_reqsent2'>".$sVars."</SELECT>\n<BR><BR>"
                     ."<SELECT name='gcgcadmin_sel_reqsent3'>".$sVars."</SELECT>\n<BR><BR>"
                     ."<INPUT type='submit' name='gcgcadmin_action' value='Request'>";
            }
            return( $s );
        }

        function drawVarietyCurrent()
        {
            $bEmpty = true;
            $s = "<DIV class='console01_controlbox_label'>Current Varieties</DIV>";
            $s .= "<TABLE border='0' cellspacing='5' cellpadding='0'>";
            if( ($kfr = $this->kfrelS->CreateRecordCursor( "G._key='".$this->kfui->GetKey($this->kfuiCid)."' AND S.status='ACTIVE'", array('sSortCol'=>'V_index_name') ) ) ) {
                while( $kfr->CursorFetch() ) {
                    $s .= "<TR>"
                         ."<TD valign='top'>".SEEDStd_StrNBSP($kfr->Value("V_index_name"))."<BR><FONT size=1>"
                         .$kfr->Value("year_start")."-".($kfr->Value("year_last_verified")?$kfr->Value("year_last_verified"):"")."</FONT></TD>"
                         ."<TD valign='top' align='center'><INPUT type='checkbox' name='gcgcadmin_checkform".$kfr->Key()."' value='1'></TD>"
                         ."<TD valign='top' align='center'><INPUT type='checkbox' name='gcgcadmin_checkdrop".$kfr->Key()."' value='1'></TD>"
                         ."</TR>";
                    $bEmpty = false;
                }
                $kfr->CursorClose();
            }
            if( !$bEmpty ) {
                $s .= "<TR><TD>&nbsp;</TD>"
                     ."<TD valign='top' align='center'><INPUT type='submit' name='gcgcadmin_action' value='Form'></TD>"
                     ."<TD valign='top' align='center'><INPUT type='submit' name='gcgcadmin_action' value='Drop'></TD>"
                     ."</TR>";
            }
            $s .= "</TABLE>";

            return( $s );
        }

        function drawNotes()
        {
            $s = "<DIV class='console01_controlbox_label'>Notes</DIV>"
                ."<TEXTAREA name='gcgcadmin_notes' cols=18 rows=6></TEXTAREA>";
            return( $s );
        }
    }

    $oActiform = new myActiform();
    if( $mode == "CS" ) $oActiform->reqSelMode = "Send";

    KFUIApp_ListReadOnly_Actiform( $kfdb, $kfrelDef_GCGC_GrowersXContacts, $kfuiDef_GCGC_GrowersXContacts, $oActiform, $sess->GetUID(), $raParms );
} else if( $mode == "GA" ) {
    doAddGrower( $kfdb, $sess, $raParms );
} else if( $mode == "G" ) {
    KFUIApp_ListForm( $kfdb, $kfrelDef_GCGC_GrowersXContacts, $kfuiDef_GCGC_GrowersXContacts, $sess->GetUID(), $raParms );
} else if( $mode == "V" ) {
    KFUIApp_ListForm( $kfdb, $kfrelDef_GCGC_Varieties, $kfuiDef_GCGC_Varieties, $sess->GetUID(), $raParms );
} else if( $mode == "S" ) {
    KFUIApp_ListForm( $kfdb, $kfrelDef_GCGC_Samples, $kfuiDef_GCGC_Samples, $sess->GetUID(), $raParms );
} else if( $mode == "R" ) {
    doReports( $kfdb, $sess, $raParms );
}


function doAddGrower( &$kfdb, &$sess, $raParms )
/***********************************************
 */
{
    include_once(SITEROOT."mbr/_mbr.php");  // mbr_contacts

    $kfrel = new KeyFrameRelation( $kfdb, $kfrelDef_mbr_contacts, $sess->GetUID() );
// should be a more object-oriented way to override this, like $kfui->SetParm("A","ListSize",10);
    $kfuiDef_mbr_contacts['A']['ListSize'] = 10;
    $kfui = new KeyFrameUI( $kfuiDef_mbr_contacts );

    //if( isset($raParms['kfLogFile']) )  $kfrel->SetLogFile( $raParms['kfLogFile'] );

    /* This is read-only on mbr_contacts
     */
    $kfui->InitUIParms();
    $kfui->SetComponentKFRel( "A", $kfrel, (is_array(@$raParms['kfrelParms']) ? $raParms['kfrelParms'] : array()) );
    echo "<DIV class='console01_guideText'>Search for the new grower and choose them in the list</DIV><BR>";
    $kfui->Draw( "A", "Search" );
    $kfui->Draw( "A", "List" );

    echo "<TABLE border=1 width='100%'><TR>";
    echo "<TD valign='top'>";
    $kMbr = $kfui->GetKey("A");
    if( $kMbr ) {
        $kG = $kfdb->KFDB_Query1("SELECT _key FROM gcgc_growers WHERE fk_mbr_contacts='$kMbr'");
        if( $kG ) {
            echo "<P>This person is already a Garlic Grower</P>";
        } else {
            echo "<P>Is this the person that you want to add?</P>";
            echo "<TABLE border=0><TR><TD valign='top'>".mbr_drawAddress( $kfdb, $kMbr, array("bEmail"=>1) )
                ."</TD><TD valign='top'>"
                .(intval(substr($kfui->GetValue("A", "expires"),0,4)) < date("Y")
                                ? "<FONT color='red'>NOT A MEMBER</FONT>"
                                : "<FONT color='green'>CURRENT MEMBER</FONT>")
                ."</TD></TR></TABLE>";
            echo "<BR><FORM action='${_SERVER['PHP_SELF']}'><INPUT type='submit' name='gcgcadmin_action' value='Add Grower'>"   // send this action to the Console Actiform
                ."<INPUT type='hidden' name='gcgcadmin_gakey' value='$kMbr'>"
                ."<INPUT type='hidden' name='mode' value='C'>"
                ."</FORM>";
        }
    } else {
        echo "Choose a grower from the list above";
    }
    echo "</TD></TR></TABLE>";
}


function doReports( &$kfdb, &$sess, $raParms )
/*********************************************
 */
{
    echo "<DIV style='font-size:9pt'>"
        ."<A HREF='${_SERVER['PHP_SELF']}?mode=R&modeRep=A'>Active growers with outstanding requests</A><BR>"
        ."<A HREF='gcgc_report.php?report=B' target='_blank'>Active growers with outstanding requests - Address List</A><BR>"
        ."<A HREF='gcgc_report.php?report=G_allcsv' target='_blank'>All growers - Address List (CSV)</A><BR>"
        ."<A HREF='gcgc_report.php?report=G_all' target='_blank'>All growers - Address List (Excel)</A><BR>"
        ."</DIV>";

    $modeRep = SEEDSafeGPC_GetStrPlain("modeRep");
    switch( $modeRep ) {
        case "A":
            // Show samples that have been requested, and need to be sent
            echo "<H3>Active growers with outstanding requests (not in order of date)</H3>";

            $nGrowers = $nSamples = 0;
            echo "<TABLE border=1 cellpadding=10>";
            $dbc = $kfdb->KFDB_CursorOpen( "SELECT * FROM gcgc_growers WHERE (status='NEW' OR status='ACTIVE') AND "
                                          ."(workflow LIKE '%REQUEST".date("Y")."%' AND "
                                          ." workflow NOT LIKE '%SENT".date("Y")."%')" );
            while( $ra = $kfdb->KFDB_CursorFetch($dbc) ) {
                echo "<TR>";
                $raMbr = $kfdb->KFDB_QueryRA("SELECT * FROM mbr_contacts WHERE _key='{$ra['fk_mbr_contacts']}'" );
// kluging link to the grower row using _kfuA_row : should be a kfui method to generate this, or a console function
                echo "<TD valign='top'><A HREF='{$_SERVER['PHP_SELF']}?mode=CS&_kfuA_row={$ra['_key']}' target='gcgc_console'>".mbr_makeName($raMbr)."</A></TD><TD valign='top'>";

                foreach( explode("\n", $ra['workflow']) as $w ) {
                    if( substr( $w, 0, 11 ) == "REQUEST".date("Y") ) {
                        echo substr( $w, 16 )." : ";
                        ++$nSamples;
                    }
                }
                echo "</TD></TR>";
                ++$nGrowers;
            }
            echo "</TABLE>";
            echo "<P>Growers = $nGrowers<BR>Samples = $nSamples</P>";
            break;
        default:
            break;
    }
}

?>
