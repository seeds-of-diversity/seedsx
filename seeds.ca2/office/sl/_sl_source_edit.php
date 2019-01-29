<?php

/* _sl_source_edit.php
 *
 * Copyright 2016 Seeds of Diversity Canada
 *
 * Implement the user interface for editing sl_cv_sources (for seed companies)
 */

class SLSourceEdit extends Console01_Worker
{
    private $oSVA;
    private $oSLDBSrc;
    private $kCompany = 0;

    private $oQSLSrc;

    function __construct( Console01 $oC, KeyFrameDB $kfdb, SEEDSession $sess )
    {
        parent::__construct( $oC, $kfdb, $sess, "EN" );

        $this->oSVA = $oC->TabSetGetSVA( 'main', 'Edit' );
        $this->oSLDBSrc = new SLDB_Sources( $this->kfdb, $this->sess->GetUID() );

        $oQ = new Q( $this->kfdb, $this->sess, null, array() );
        $this->oQSLSrc = new QServerSourceCV( $oQ, array() );
    }

    function Main()
    {
        $s = "";
//var_dump($_REQUEST);
$oSLSrc = $this->oQSLSrc;

        // The Company selector
        $oFormA = new SEEDFormSession( $this->sess, 'SLSourceEdit', 'A' );
        $oFormA->Update();

        // oFormB::Update needs this in seedDSPreStore to set the company for every row
        $this->kCompany = $oFormA->Value('kCompany');

        // The seeds
        $oFormB = new KeyFrameUIForm( $this->oSLDBSrc->GetKFRel('SRCCV'), 'B',
                                      array( "DSParms" => array('fn_DSPreStore'=>array($this,'seedDSPreStore') ) ) );
//$this->oSLDBSrc->kfdb->SetDebug(2);
        $oFormB->Update();
//$this->oSLDBSrc->kfdb->SetDebug(0);

        // Also process novelsp1/cv1
        if( ($sNovelSp = SEEDSafeGPC_GetStrPlain('novelsp1')) && ($sNovelCv = SEEDSafeGPC_GetStrPlain('novelcv1')) ) {
            if( $this->kCompany && ($kfr = $this->oSLDBSrc->GetKFRel('SRCCV')->CreateRecord()) ) {
                $kfr->SetValue( 'osp', $sNovelSp );
                $kfr->SetValue( 'ocv', $sNovelCv );
                $kfr->SetValue( 'fk_sl_sources', $this->kCompany );
                $kfr->PutDBRow();
            }
        }


        // Get the company names and the current company name
        $sCompanyName = "";
        $raSrc = $oSLSrc->GetSources();
        $raOpts = array( " -- Choose a Company -- " => 0 );
        foreach( $raSrc as $ra ) {
            if( $this->kCompany && $this->kCompany == $ra['SRC__key'] ) {
                $sCompanyName = $ra['SRC_name'];
            }
            $raOpts[$ra['SRC_name']] = $ra['SRC__key'];
        }

        $s .= $this->Style();

        $sUpload = $sUploadJS = "";
        if( $this->kCompany ) {
            $rQ = $this->oQSLSrc->Cmd( 'srcSrcCv', array('kSrc'=>$this->kCompany, 'kfrcParms'=>array('sSortCol'=>'osp,ocv')) );
            $raSpCv = $rQ['raOut'];
            // only allow upload of one company at a time
            $sUpload = "<p>Upload a spreadsheet of $sCompanyName</p>"
                      ."<form style='width:100%;text-align:left' action='".Site_path_self()."' method='post' enctype='multipart/form-data'>"
                      ."<div style='margin:0px auto;width:60%'>"
                      ."<input type='hidden' name='MAX_FILE_SIZE' value='10000000' />"
                      ."<input type='hidden' name='cmd' value='upload' />"
                      ."<input type='file' name='uploadfile'/><br/>"
                      ."<input type='submit' value='Upload'/>"
                      ."</div></form>";

            if( SEEDSafeGPC_GetStrPlain('cmd') == 'upload' ) {
                $def = array( 'raSEEDTableDef' => array( 'headers-required' => array('k','company','species','cultivar','organic'),
                              'charset'=>'utf-8' ) );
                list($bOk,$raRows,$sErr) = SEEDTable_LoadFromUploadedFile( 'uploadfile', $def );
                if( !$bOk ) {
                    $sUpload .= "<div class='slsrcedit_err'>$sErr</div>";
                } else {
                    $sUpload .= "Uploaded ".count($raRows)." rows";

                    $raCatDiff = $this->catDiff( $raSpCv, $raRows );
                    if( $raCatDiff['sErr'] ) {
                        $sUpload .= "<div class='slsrcedit_err'>{$raCatDiff['sErr']}</div>";
                    } else {

                        if( ($n = count($raCatDiff['inserted'])) ) { $sUpload .= "<br/>Inserted $n"; }
                        if( ($n = count($raCatDiff['changed'])) )  {
                            /* For every row changed in the uploaded file, write a JS directive
                             */
                            $sUpload .= "<br/>Changed $n";

                            foreach( $raCatDiff['changed'] as $ra ) {
                                if( $ra['bSpChanged'] ) {

                                } else {
// handle " in cultivar
                                    $sUploadJS .= "SLSrcEdit_EditKey( {$ra['k']}, \"{$ra['cultivar']}\" ); ";
                                }
                            }
                        }
                        if( ($n = count($raCatDiff['deleted'])) )  {
                            /* For every row deleted in the uploaded file, write a JS directive
                             */
                            $sUpload .= "<br/>Deleted $n";

                            foreach( $raCatDiff['deleted'] as $kDel ) {
                                $sUploadJS .= "SLSrcEdit_DeleteKey( $kDel ); ";
                            }
                        }
                    }
                }
            }
        }

        $sXLS = "qcmd=srcCSCI"
               .($this->kCompany ? "&kSrc={$this->kCompany}" : "")
               ."&qname=".urlencode($this->kCompany ? $sCompanyName : "All Companies")
               ."&qfmt=xls";
        $s .= "<div style='display:inline-block;float:right;border:1px solid #999;border-radius:10px;"
                         ."margin-left:10px;padding:10px;background-color:#f0f0f0;text-align:center'>"
                ."<a href='".Site_UrlQ()."?$sXLS' target='_blank' style='text-decoration:none'>"
                ."<img src='".W_ROOT."std/img/dr/xls.png' height='25'/>"
                .SEEDStd_StrNBSP("",5)."Download a spreadsheet of ".($this->kCompany ? $sCompanyName : "all companies")
                ."</a>"
                ."<hr style='width:85%;border:1px solid #aaa'/>"
                .$sUpload
            ."</div>";

        $s .= "<h4 class=''>Edit Seeds for a Company</h4>"
             ."<div style='padding:1em'>"
                 ."<form method='post' action=''>"
//                 ."<input type='hidden' name='cmd' value='company_download' />"
                 .$oFormA->Select2( 'kCompany', $raOpts, "", array() )
                 ."<input type='submit' value='Choose'/>"
                 ."</form>"
             ."</div>"

             ."<hr/>";

        if( $this->kCompany ) {
            $s .= "<form method='post' action=''>"
                 ."<input style='float:right' type='submit' value='Save'/>"
                 .$this->drawSeedTree( $oFormA->Value('kCompany'), $raSpCv, $oFormB )
                 // Propagate the company of FormA so it retains state when this form is saved
                 .$oFormA->Hidden('kCompany')
                 ."<input style='float:right' type='submit' value='Save'/>"
                 ."</form>";

            // The New buttons in the tree create new form entries dynamically. This tells the javascript which row number to use.
            $s .= "<script>slsrceditRowNum = ".$oFormB->GetRowNum().";</script>";

            // If there were changes uploaded from a file, write the JS directives here
            $s .= "<script>$sUploadJS</script>";

        } else {
            $s .= "<p style='width:60%'>Choose a company to edit. You can only edit one company at a time because the entire list of all companies' seeds "
                ."would be too long for the screen. However, you can download a complete list of all seeds by clicking on the link above.</p>";
        }

        return( $s );
    }

    function seedDSPreStore( SEEDDataStore $oDS )
    {
        // Make sure every new row has a kCompany.
        // Not essential for edits because if (_key,ocv) is defined the existing kCompany in the db row will be unchanged.
        // It matters for new rows because the kCompany is not propagated in http for each row (it is always the same for every list).
        //
        // Also require osp && ocv
//var_dump($oDS->kfr->_values);
        if( !$this->kCompany )  return( false );
        if( $oDS->IsEmpty('osp') || $oDS->IsEmpty('ocv') )  return( false );

        if( $oDS->IsEmpty('fk_sl_sources') )  $oDS->SetValue( 'fk_sl_sources', $this->kCompany );

        return( true );
    }

    private function drawSeedTree( $kCompany, $raSpCv, $oForm )
    {
        $s = "";

        $prevSp = "";
        $raCV = array();

$s .= "<table><tr><td valign='top'>";

        foreach( $raSpCv as $ra1 ) {
            $sp = $ra1['SRCCV_osp'];
            $cv = $ra1['SRCCV_ocv'];
            if( $prevSp && $sp != $prevSp ) {
                $s .= $this->drawSeedTreeSection( $prevSp, $raCV, $oForm );
                $raCV = array();
            }
            $prevSp = $sp;
            $raCV[] = array( 'sp'=>$sp, 'cv'=>$cv, 'k'=>$ra1['SRCCV__key'], 'bOrganic'=>$ra1['SRCCV_bOrganic'] );
        }
        if( count($raCV) ) {
            $s .= $this->drawSeedTreeSection( $prevSp, $raCV, $oForm );
        }

$s .= "</td><td valign='top'>";

        $prevSp = "";
        $raCV = array();
        if( false && ($kfr = $this->oSLDBSrc->GetKFRel("SRCCV")->CreateRecordCursor( "fk_sl_sources='$kCompany'", array( 'sSortCol'=>'osp,ocv' ) )) ) {
            while( $kfr->CursorFetch() ) {
                $sp = $kfr->Value('osp');
                if( $prevSp && $sp != $prevSp ) {
                    $s .= $this->drawSeedTreeSection( $prevSp, $raCV, $oForm );
                    $raCV = array();
                }
                $prevSp = $sp;
                $raCV[] = array( 'sp'=>$sp, 'cv'=>$kfr->Value('ocv'), 'k'=>$kfr->Key(), 'bOrganic'=>$kfr->Value('bOrganic') );
            }
            if( count($raCV) ) {
                $s .= $this->drawSeedTreeSection( $prevSp, $raCV, $oForm );
            }
        }

$s .= "</td></tr></table>";


        $s .= "<h4>Add New Species</h4>"
             ."<p>To add a species that isn't in the list above, enter it here with any cultivar name</p>"
             ."<div class='slsrcedit_novelgroup'>"
                 ."<div class='slsrcedit_novel'>"
                     ."<input class='slsrcedit_novelsp' name='novelsp1' value=''/>"
                     ."<input class='slsrcedit_novelcv' name='novelcv1' value=''/>"
                     ."<div class='slsrcedit_cvBtns'>"
                         ."<img class='slsrcedit_novelBtns_new' height='14' src='".W_ROOT."img/ctrl/new01.png'/>"
                     ."</div>"
                 ."</div>"
             ."</div>";

        return( $s );
    }

    private function drawSeedTreeSection( $sp, $raCV, SEEDForm $oForm )
    {
        $s = "";

        if( !count($raCV) )  goto done;

        /* <div class='slsrcedit_sp' slsrc_osp='osp'>
         *     <div class='slsrcedit_spName'> SPECIES NAME </div>
         *     <div class='slsrcedit_cvgroup'>
         *         <div class='slsrcedit_cv'
         *              kSRCCV='{k}'          the sl_cv_sources key (0 for a new row)
         *              iRow='{r}'            the sf iRow
         *              bOrganic='{b}'        stored here so we don't have to fill the http parm stream with these unless they change
         *             >
         *             <div class='slsrcedit_cvOrgBtn'></div>
         *             <div class='slsrcedit_cvName'> CULTIVAR NAME </div>
         *             <div class='slsrcedit_cvBtns'> New Edit Del buttons </div>
         *             <div class='slsrcedit_cvCtrlKey'> on any change, insert sfBk here and never remove it </div>
         *             <div class='slsrcedit_cvCtrlOrg'> on bOrganic toggle, insert sfBp_bOrganic with the new value </div>
         *             <div class='slsrcedit_cvCtrls'> sf input tags dynamically inserted here </div>
         *         </div>
         *     </div>
         * </div>
         *
         * New:        create a new row with hidden osp obtained from the slsrcedit_sp container
         * Edit:       insert key in cvCtrlKey, ocv text control in Ctrl area
         * Del toggle: insert key in cvCtrlKey; if Ctrl area contains op_del clear Ctrl; else insert op_del in Ctrl area (overwriting any Edit)
         * Org toggle: insert key in cvCtrlKey; insert bOrganic hidden tag in cvCtrlOrg.  This allows Edit and Org to coexist.
         */

        $spEsc = htmlspecialchars($sp,ENT_QUOTES);
        $s .= "<div class='slsrcedit_sp' osp='$spEsc'>"
                 ."<div class='slsrcedit_spName'>$spEsc</div>"
                 ."<div class='slsrcedit_spBtns'><img class='slsrcedit_spBtns_new' height='14' src='".W_ROOT."img/ctrl/new01.png'/></div>"
                 ."<div class='slsrcedit_cvgroup'>";
        $i = 1;
        foreach( $raCV as $r ) {
            $cOrganic = $r['bOrganic'] ? "slsrcedit_cvorganic" : "";

            $s .= "<div class='slsrcedit_cv slsrcedit_stripe".($i%2)." $cOrganic' "
                          ."kSRCCV='{$r['k']}' "
                          ."iRow='".$oForm->GetRowNum()."'"
                          ."bOrganic='".$r['bOrganic']."'>"
                     ."<div class='slsrcedit_cvOrgBtn'></div>"
                     ."<div class='slsrcedit_cvName'>".htmlspecialchars($r['cv'], ENT_QUOTES)."</div>"
                     ."<div class='slsrcedit_cvBtns'>"
                         ."<img class='slsrcedit_cvBtns_new' height='14' src='".W_ROOT."img/ctrl/new01.png'/>"
                         ."<img class='slsrcedit_cvBtns_edit' height='14' src='".W_ROOT."img/ctrl/edit01.png'/>"
                         ."<img class='slsrcedit_cvBtns_del' height='14' src='".W_ROOT."img/ctrl/delete01.png'/>"
                     ."</div>"

                     // if any change is requested, put the sfBk here and never remove it. No problem if it's issued when other ctrls don't exist.
                     ."<div class='slsrcedit_cvCtrlKey'></div>"
                     // put bOrganic hidden ctrl here if it the state has changed, so it is independent of other ctrls e.g. Edit
                     ."<div class='slsrcedit_cvCtrlOrg'></div>"
                     // if an edit/del is requested, put sfBp and sfBd ctrls here, and replace any previous content
                     ."<div class='slsrcedit_cvCtrls'></div>"
                 ."</div>";
            $i++;
            $oForm->IncRowNum();
        }

        $s .= "</div></div>";

        done:
        return( $s );
    }

    private function catDiff( $raDb, $raXls )
    /****************************************
        Check 1: all rows must contain the same company name
        Check 2: raXls cannot contain duplicates of [sp,cv]
        Check 3: if raXls[sp,cv] matches raDb[sp,cv] the key cannot be different (different number or blank)
        Check 4: all raXls[k] that are not blank must also be in raDb[k]

        Case 0: raXls[k,sp,cv,organic]=raDb[k,sp,cv,organic] and k not blank : non-changed row
        Case 1: raXls[k]=raDb[k] but sp,cv,organic different  : change
        Case 2: raXls[k] is blank  : insert
        Case 3: raXls[k] not blank but sp blank  : delete
        Case 4: raDb[k] is missing from raXls  : delete
     */
    {
        $raOut = array( 'sErr'=>"", 'inserted' => array(), 'changed' => array(), 'deleted' => array() );
//var_dump($raDb);
        $raDbIndex = array();
        $raDbIndex2 = array();
        foreach( $raDb as $ra ) {
            $raDbIndex[$ra['SRCCV__key']] = $ra['SRCCV_osp']." / ".$ra['SRCCV_ocv'];
            $raDbIndex2[$ra['SRCCV__key']] = array('sp'=>$ra['SRCCV_osp'],'cv'=>$ra['SRCCV_ocv'],'organic'=>$ra['SRCCV_bOrganic']);
        }

        $raXlsNames = array();  // for Check1 finding duplicate names
        $sCompany = "";
        foreach( $raXls as $ra ) {
            $spcv = $ra['species']." / ".$ra['cultivar'];

            // Check 1 : no duplicate spcv in spreadsheet
            if( in_array( $spcv, $raXlsNames ) ) {
                $raOut['sErr'] = "Error: $spcv is duplicated in the spreadsheet";
                goto done;
            }
            $raXlsNames[] = $spcv;

            // Check 2: all rows have the same company name
            if( !$ra['company'] ) {
                $raOut['sErr'] = "Error: at least one row has a blank company name";
                goto done;
            }
            if( !$sCompany ) {
                // first row, all other rows' company names must match this
                $sCompany = $ra['company'];
            } else if( $ra['company'] != $sCompany ) {
                $raOut['sErr'] = "Error: only one company may be uploaded at a time";
                goto done;
            }

            // Check 3: existing matching spcv cannot have different key (or blank) in the spreadsheet
            if( ($kDb = array_search( $spcv, $raDbIndex )) && ($kDb != $ra['k']) ) {
                $raOut['sErr'] = "Error: the key for $spcv is different in the spreadsheet (should be $kDb)";
                goto done;
            }

            // Check 4: all non-blank k in the spreadsheet must be in the db
            if( $ra['k'] && !isset($raDbIndex[$ra['k']]) ) {
                $raOut['sErr'] = "Error: the key for $spcv ({$ra['k']}) is not in the database";
                goto done;
            }


            // Case 2: Insert rows with blank keys
            if( !$ra['k'] ) {
                $raOut['inserted'][] = $ra;
                continue;
            }

            // Case 3: Delete by blanking a species of a keyed row
            if( !$ra['species'] ) {    // && $ra['k']
                $raOut['deleted'][] = $ra['k'];
                continue;
            }

            // Case 1: k is the same but sp,cv,organic is different
            if( @$raDbIndex2[$ra['k']] ) {    // && $ra['k']
                $bSpChanged  = ($raDbIndex2[$ra['k']]['sp'] != $ra['species']);
                $bCvChanged  = ($raDbIndex2[$ra['k']]['cv'] != $ra['cultivar']);
                $bOrgChanged = ($raDbIndex2[$ra['k']]['organic'] != $ra['organic']);

                if( $bSpChanged || $bCvChanged || $bOrgChanged ) {
                    $ra['bSpChanged'] = $bSpChanged;
                    $ra['bCvChanged'] = $bCvChanged;
                    $ra['bOrgChanged'] = $bOrgChanged;

                    $raOut['changed'][] = $ra;
                    continue;
                }
            }
        }

        // Case 4: Delete by removing a keyed row from the spreadsheet
        // foreach raDB[k] check for raXls[k]

        done:
        return( $raOut );
    }


    private function Style()
    {
        $s = "<style>
              .slsrcedit_spName  { display:inline-block; width:363px; font-family:verdana,helvetica,sans serif;font-size:10pt; font-weight:bold; }
              .slsrcedit_spBtns  { display:inline-block; margin-left:10px; }
              .slsrcedit_cvgroup { margin:0px 0px 10px 50px; }
              .slsrcedit_cvOrgBtn { display:inline-block; width:10px; height:10px; margin-right:3px; }
              .slsrcedit_cvName  { display:inline-block; width:300px; font-family:verdana,helvetica,sans serif;font-size:10pt; }

              /* OrgBtn and Name have different colours depending on their container's .slsrcedit_cvorganic
               */
              .slsrcedit_cvOrgBtn { background-color: #aaa; }
              .slsrcedit_cvorganic
                  .slsrcedit_cvOrgBtn { background-color: #ada; }
              .slsrcedit_cvorganic
                  .slsrcedit_cvName { color: green; background-color:#cec; }

              .slsrcedit_cvBtns  { display:inline-block; margin-left:10px; }
              .slsrcedit_cvCtrls { display:inline-block; width:50px; font-family:verdana,helvetica,sans serif;font-size:10pt; margin-left:10px; }
              .slsrcedit_cvCtrlKey  { display:inline-block; }
              .slsrcedit_cvCtrlOrg  { display:inline-block; }

              .slsrcedit_stripe1 { background-color:#f4f4f4; }
              .slsrcedit_stripe_new { background-color:#abf; }

              .slsrcedit_err { border:1px solid black;color:red;padding:10px; }
        </style>";

        $s .= <<<SLSrcEditScript
              <script>

              var slsrceditRowNum = 0;

              function SLSrcEdit_GetClosestDivCV( e )
              {
                  var div_sp = e.closest(".slsrcedit_sp");                      // from the clicked element, search up for the sp div
                  var div_cv = e.closest(".slsrcedit_cv");                      // from the clicked element, search up for the cv div
                  return( SLSrcEdit_GetDivCVDetails( div_sp, div_cv ) );
              }

              function SLSrcEdit_GetDivCVDetails( div_sp, div_cv )
              {
                  var o = { divCV        : div_cv,
                            divCVOrg     : div_cv.find(".slsrcedit_cvOrgBtn"),  // the button that changes bOrganic; also where we keep that <hidden>
                            divCVName    : div_cv.find(".slsrcedit_cvName"),    //   then down from there to the name
                            divCVCtrls   : div_cv.find(".slsrcedit_cvCtrls"),   //   and the div where input controls are written (except bOrganic)
                            divCVCtrlKey : div_cv.find(".slsrcedit_cvCtrlKey"), //   and the div where the sf key is written
                            divCVCtrlOrg : div_cv.find(".slsrcedit_cvCtrlOrg"), //   and the div where the sf bOrganic ctrl is written
                            kSRCCV       : div_cv.attr("kSRCCV"),               // the sl_cv_sources._key
                            iRow         : div_cv.attr("iRow"),                 // the oForm->iR
                            bOrganic     : div_cv.attr("bOrganic") == 1,        // the bOrganic state (changes when you click the bOrganic button)
                            osp          : div_sp.attr("osp")                   // the osp of this cv
                  };
                  o['ocv'] = o['divCVName'].html();
                  return( o );
              }

              function SLSrcEdit_SetCVKey( oDivCV )
              {
                  k = oDivCV['kSRCCV'];

                  oDivCV['divCVCtrlKey'].html( "<input type='hidden' name='sfBk"+oDivCV['iRow']+"' value='"+k+"'/>" );
              }

              function SLSrcEdit_ToggleOrganic( oDivCV )
              {
                  var newVal = (oDivCV['bOrganic'] ? 0 : 1);

                  if( oDivCV['bOrganic'] ) {
                      oDivCV['divCV'].removeClass( 'slsrcedit_cvorganic' );
                  } else {
                      oDivCV['divCV'].addClass( 'slsrcedit_cvorganic' );
                  }
                  oDivCV['divCV'].attr( 'bOrganic', newVal );
                  oDivCV['divCVCtrlOrg'].html( "<input type='hidden' name='sfBp"+oDivCV['iRow']+"_bOrganic' value='"+newVal+"' />" );
                  SLSrcEdit_SetCVKey( oDivCV );
              }

              function SLSrcEdit_AddNewRow( oDivCV, bBelow )
              {
                  //oDivCV['divCVName'].css( 'color', '#000' );
                  //oDivCV['divCVName'].css( 'text-decoration', 'none' );

                  var s = slsrceditNewCV;
                  s = s.replace( /%%i%%/g, slsrceditRowNum++ );    // global replace
                  s = s.replace( /%%osp%%/, oDivCV['osp'] );

                  var oNewCV = null;
                  if( bBelow ) {
                      oDivCV['divCV'].after( s );
                      oNewCV = oDivCV['divCV'].next();
                  } else {
                      oDivCV['divCV'].before( s );
                      oNewCV = oDivCV['divCV'].prev();
                  }

                  /* jQuery doesn't automatically connect handlers for new DOM elements, so connect them now.
                   */

                  // Connect the Organic button to the ToggleOrganic function.
                  $(oNewCV.find('.slsrcedit_cvOrgBtn')).click(function() {
                      var oDivCVNew = SLSrcEdit_GetClosestDivCV( $(this) );
                      SLSrcEdit_ToggleOrganic( oDivCVNew );
                  });
                  // Connect the New button so you can make yet another new row.
                  $(oNewCV.find('.slsrcedit_cvBtns_new')).click(function() {
                      var oDivCVNew = SLSrcEdit_GetClosestDivCV( $(this) );
                      SLSrcEdit_AddNewRow( oDivCVNew, true );
                  });
                  // Define a function for deleting the new row.
                  $(oNewCV.find('.slsrcedit_cvBtns_delnew')).click(function() {
                      var oDivCVNew = SLSrcEdit_GetClosestDivCV( $(this) );
                      oDivCVNew['divCV'].remove();
                  });

              }

              function SLSrcEdit_Edit( oDivCV )
              {
                  // Edit implies an Undo-delete
                  oDivCV['divCVName'].css( 'color', '#000' );
                  oDivCV['divCVName'].css( 'text-decoration', 'none' );

                  oDivCV['divCVCtrls'].html( "<input type='text' name='sfBp"+oDivCV['iRow']+"_ocv' value=\""+oDivCV['ocv']+"\"/>" );
                  SLSrcEdit_SetCVKey( oDivCV );
              }

              function SLSrcEdit_Delete( oDivCV )
              {
                  oDivCV['divCVName'].css( 'color', 'red' );
                  oDivCV['divCVName'].css( 'text-decoration', 'line-through' );

                  oDivCV['divCVCtrls'].html( "<input type='hidden' name='sfBd"+oDivCV['iRow']+"' value='1'/>" );
                  SLSrcEdit_SetCVKey( oDivCV );
              }

              function SLSrcEdit_DeleteKey( kDel )
              {
                  var oDivCV = SLSrcEdit_GetDivCVFromKey( kDel );
                  SLSrcEdit_Delete( oDivCV );
              }
              function SLSrcEdit_EditKey( kDel, sCultivar )
              {
                  var oDivCV = SLSrcEdit_GetDivCVFromKey( kDel );
                  if( sCultivar ) oDivCV['ocv'] = sCultivar;
                  SLSrcEdit_Edit( oDivCV );
              }

              function SLSrcEdit_GetDivCVFromKey( kSRCCV )
              {
                  var j = ".slsrcedit_cv[kSRCCV='"+kSRCCV+"']";
                  var div_cv = $(j);
                  var div_sp = div_cv.closest(".slsrcedit_sp");    // search up for the sp div
                  var oDivCV = SLSrcEdit_GetDivCVDetails( div_sp, div_cv );
                  return( oDivCV );
              }

              $(document).ready(function() {
                  /* Click on the cultivar name to cancel all changes
                   */
                  $(".slsrcedit_cvName").click(function() {
                      var oDivCV = SLSrcEdit_GetClosestDivCV( $(this) );

                      oDivCV['divCVName'].css( 'color', '#000' );
                      oDivCV['divCVName'].css( 'text-decoration', 'none' );

                      oDivCV['divCVCtrls'].html( "" );
                      oDivCV['divCVCtrlKey'].html( "" );
                      oDivCV['divCVCtrlOrg'].html( "" );
                  });

                  /* Click the New button to create a whole divCV with an empty text input field, and an empty kSrccv
                   */
                  $(".slsrcedit_cvBtns_new").click(function() {
                      var oDivCV = SLSrcEdit_GetClosestDivCV( $(this) );
                      SLSrcEdit_AddNewRow( oDivCV, true );
                  });
                  /* The New button beside the species name opens a new row above the first cv
                   */
                  $(".slsrcedit_spBtns_new").click(function() {
                      var div_sp = $(this).closest(".slsrcedit_sp");                      // from the clicked element, search up for the sp div
                      var div_cv = div_sp.find(".slsrcedit_cvgroup .slsrcedit_cv:first");

                      var oDivCV = SLSrcEdit_GetDivCVDetails( div_sp, div_cv );
                      SLSrcEdit_AddNewRow( oDivCV, false );
                  });

                  /* Click the delete button to delete a cultivar
                   */
                  $(".slsrcedit_cvBtns_del").click(function() {
                      var oDivCV = SLSrcEdit_GetClosestDivCV( $(this) );
                      SLSrcEdit_Delete( oDivCV );
                  });

                  /* Click the Edit button to create a text input field, initialize with the cultivar name and kSrccv
                   */
                  $(".slsrcedit_cvBtns_edit").click(function() {
                      var oDivCV = SLSrcEdit_GetClosestDivCV( $(this) );
                      SLSrcEdit_Edit( oDivCV );
                  });

                  /* Click the Org button while in N mode to change bOrganic to true
                   */
                  $(".slsrcedit_cvOrgBtn").click(function() {
                      var oDivCV = SLSrcEdit_GetClosestDivCV( $(this) );
                      SLSrcEdit_ToggleOrganic( oDivCV );
                  });

              });


              var wroot = "../../w/";
              var slsrceditNewCV =
                      "<div class='slsrcedit_cv slsrcedit_stripe_new' iRow='%%i%%' kSRCCV='0' bOrganic='0'>                \
                           <div class='slsrcedit_cvOrgBtn'></div>                                                          \
                           <div class='slsrcedit_cvName'>                                                                  \
                               <input type='hidden' name='sfBp%%i%%_osp' value='%%osp%%'/>                                 \
                               <input type='text' name='sfBp%%i%%_ocv' value=''/>                                          \
                           </div>                                                                                          \
                           <div class='slsrcedit_cvBtns' style='margin-left:1px'>                                          \
                               <img class='slsrcedit_cvBtns_new'    height='14' src='"+wroot+"img/ctrl/new01.png'/>        \
                               <img class='slsrcedit_cvBtns_delnew' height='14' src='"+wroot+"img/ctrl/delete01.png'>      \
                           </div>                                                                                          \
                           <div class='slsrcedit_cvCtrlKey'><input type='hidden' name='sfBk%%i%%' value='0'/></div>        \
                           <div class='slsrcedit_cvCtrlOrg'></div>                                                         \
                       </div>";

              </script>
SLSrcEditScript;

        return( $s );
    }

}

?>