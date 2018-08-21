<?php

// collection errors to alerts
// preserve window scroll on collection and germ forms - and inventory?
// initial user goes to collection screen, but not obvious how to see the seeds

include_once( SEEDCORE."SEEDSessionPerms.php" );
include_once( STDINC."SEEDUIWidgets.php" );

include_once( "_collection.php" );
include_once( "_accession.php" );
include_once( "_inventory.php" );
include_once( "_germtest.php" );


class SLCollectionAdmin extends Console01_Worker1
{
    public $oSVA;  // this tab's own SessionVarAccessor
    private $sSearchCond = "";
    public $oSLDBMaster;
    public $oUGP;    // SEEDSessionAuthDBRead for general use

    public $oPerms;      // for reading/writing arbitrary seedperms
    public $oPermsTest;  // SEEDPermsTest for this user, in the SLCollection namespace

    public $kInvCurr = 0;

    public $kCurrCollection = 0;

    public $IsAdmin;    // true if user is admin for the whole application; use CanAdminCollection() for per-collection admin

    private $oAcc;
    private $oInv;
    private $oGerm;
    public $oColl;

    public $sSPop = "";    // tell SEEDPopup which popup to 'show'

    private $kluge_HighlightNewKInv = 0;

    private $errMsg = "";

    function __construct( Console01 $oC, KeyFrameDB $kfdb, SEEDSession $sess )
    {
        parent::__construct( $oC, $kfdb, $sess );
        $this->oSVA = $this->oC->oSVA;  // could use the TabSetGetSVA() for this tab but we don't know the tsid/tabname here
        $this->oSLDBMaster = new SLDB_Master( $kfdb, $sess->GetUID() );
        $this->oUGP = new SEEDSessionAuthDBRead( $kfdb );
        $this->oPerms = new SEEDPermsWrite( New_SiteAppDB(), $this->sess->GetUID() );
        $this->oPermsTest = New_SEEDPermsFromUID( New_SiteAppDB(), $this->sess->GetUID(), "SLCollection" );

        // these don't have to be created here, but they contain oForms that have to be Updated before the UI is drawn, especially drawList
        $this->oAcc = new SLCollectionAccession( $this );
        $this->oInv = new SLCollectionInventory( $this );
        $this->oGerm = new SLCollectionGermination( $this );
        $this->oColl = new SLCollectionCollection( $this );

        $this->IsAdmin = $sess->TestPermRA( array( array('SLCollection' => 'A'), array('SL' => 'A') ) );
    }

    function Init( $kCollection )
    {
        $this->kCurrCollection = $kCollection;

        $this->oAcc->Init();    // dependent on kCurrCollection
        if( $this->oAcc->kluge_CreatedNewAcc ) {
            $ra = $this->oSLDBMaster->GetList( "I", "fk_sl_accession='{$this->oAcc->kluge_CreatedNewAcc}'");
            if( count($ra) ) {
                $this->kluge_HighlightNewKInv = $ra[0]['_key'];
            }
        }

        $this->oInv->Init();    // dependent on kCurrCollection
        $this->oGerm->Init();   // update oFormG before drawing the passport
    }

    function ErrMsg( $s ) { $this->errMsg .= $s; }

    function ContentDraw()
    {
        $s = "";

        $raPills = array( 'status' => array( 'Status' ) );
        if( $this->sess->IsLogin() && $this->kCurrCollection && $this->oColl->CanWriteCollection( $this->kCurrCollection ) ) {
            $raPills = array_merge( $raPills,
                                    array( 'newacc'   => array( 'Add new accession', ),
                                           'splitinv' => array( 'Split/move a sample',  ),
                                           'dist'     => array( 'Distribute seeds',  ),
                                           'germ'     => array( 'Germination tests',  ),
                                           'editacc'  => array( 'Edit accession', ),
                                           'batch'    => array( 'Batch process', ) ) );
        }

        // Need SearchControl to get drawList right.
        $oSearchControl = new SearchControl($this);
        $sCond1 = $oSearchControl->GetSearchCond();
        $sCond2 = $this->kCurrCollection ? "fk_sl_collection='{$this->kCurrCollection}'" : "";
        $sCond = $sCond1 . ($sCond1 && $sCond2 ? " AND " : "") . $sCond2;

        // Call drawList before drawing the body methods, because it sets the current inv.
        $sDrawList = $this->drawList( $sCond );


        $oUIPills = new SEEDUIWidgets_Pills( $raPills, 'pMode', array( 'oSVA' => $this->oSVA, 'ns' => '' ) );
        $sLeftCol = $oUIPills->DrawPillsVertical();

        $pMode = $this->sess->IsLogin() ? $oUIPills->GetCurrPill() : "status";
        switch( $pMode ) {
            case 'status':    $sBody = $this->contentDrawStatus();    break;
            case 'splitinv':  $sBody = $this->screenSplit();          break;
            case 'dist':      $sBody = $this->screenDist();           break;
            case 'germ':      $sBody = $this->screenGerm();           break;
            case 'newacc':    $sBody = $this->screenNewAcc();         break;
            case 'editacc':   $sBody = $this->screenEditAcc();        break;
            case 'batch':     $sBody = $this->screenBatchProcess();   break;

            default:          $sBody = "Under construction";   break;
        }
//$sBody .= $this->sSearchCond;
        //$s .= $this->drawSearch();

        $s .= "<div class='container-fluid' style='margin:0px'>"
                 .( !in_array($pMode, array('newacc','batch'))
                     ? ("<div class='row'>"
                           ."<div class='col-sm-5'>".$oSearchControl->ControlDraw()."</div>"
                           ."<div class='col-sm-7'>".$sDrawList."</div>"
                       ."</div><br/>")
                     : ""
                  )
                 ."<div class='row'>"
                     ."<div class='col-sm-2'>$sLeftCol</div>"
                     ."<div class='col-sm-10'>$sBody</div>"
                 ."</div>"
             ."</div>";

        return( $s );
    }

    private function contentDrawStatus()
    {
        if( !($kfrI = $this->getCurrInv()) )  return( "" );

        $s = "<table width='100%' border='0'><tr>"
            ."<td valign='top' width='30%'>".$this->drawPassport( $kfrI->Value('fk_sl_accession'), $kfrI->Key() )."</td>"
            ."<td valign='top'>"
            .$this->drawErrMsg()
            ."</td>"
            ."</tr></table>";

        return( $s );
    }

    private function screenNewAcc()
    {
        if( !$this->kCurrCollection ) {
            return( "<div class='alert-danger' style='width:30%;padding:10px'>Please select a collection</div>" );
        }
        return( $this->oAcc->DrawNewAccession() );
    }

    private function screenEditAcc()
    {
        return( $this->oAcc->DrawEditAccession() );
    }

    private function screenSplit()
    {
        if( !($kfrI = $this->getCurrInv()) )  return( "" );

        $s = "<table width='100%' border='0'><tr>"
            ."<td valign='top' width='30%'>".$this->drawPassport( $kfrI->Value('fk_sl_accession'), $kfrI->Key() )."</td>"
            ."<td valign='top'>".$this->oInv->DrawSplit( $kfrI )."</td>"
            ."</tr></table>";

        return( $s );
    }

    private function screenDist()
    {
        if( !($kfrI = $this->getCurrInv()) )  return( "" );

        $s = "<table width='100%' border='0'><tr>"
            ."<td valign='top' width='30%'>".$this->drawPassport( $kfrI->Value('fk_sl_accession'), $kfrI->Key() )."</td>"
            ."<td valign='top'>"
            .$this->drawErrMsg()
            .$this->oInv->DrawDistribute( $kfrI )
            ."</td>"
            ."</tr></table>";

        return( $s );
    }


    private function screenGerm()
    {
        if( !($kfrI = $this->getCurrInv()) )  return( "" );

        $s = "<table width='100%' border='0'><tr>"
            ."<td valign='top' width='30%'>".$this->drawPassport( $kfrI->Value('fk_sl_accession'), $kfrI->Key() )."</td>"
            ."<td valign='top'>".$this->oGerm->DrawGermination( $kfrI )."</td>"
            ."</tr></table>";

        return( $s );
    }

    private function screenBatchProcess()
    {
        include_once( "_batchprocess.php" );
        $o = new SLCollectionBatchProcess( $this );
        return( $o->Process() );
    }

    private function drawErrMsg()
    {
        return( $this->errMsg ? "<div class='alert alert-danger'>{$this->errMsg}</div>" : "" );
    }

    private function drawPassport( $kAcc, $kInv )
    /********************************************
        Show Inv and Acc info for the given Accession. Highlight the given Inv.
     */
    {
        $kfrA = $this->oSLDBMaster->GetKFR( "AxPxS", $kAcc );
        $raInvList = $this->oSLDBMaster->GetList( "IxAxPxS", "A._key='$kAcc'", array('sSortCol'=>'_key') );

        $s = "";

$s .= "<style>.seedcoll_invtable { border:0px solid #aaa; border-radius:5px; } .seedcoll_invtable td { font-size:9pt; padding:3px 6px; }</style>";

        $g = 0.0;
        $sInv = "<div class='well' style=''><table class='seedcoll_invtable' order='0'>";
        foreach( $raInvList as $raI ) {
            // should get this by adding Collection to the relation
            $sInvPrefix = ($kfrC = $this->oSLDBMaster->GetKFR( "C", $raI['fk_sl_collection'] )) ? $kfrC->Value('inv_prefix') : "X";
            if( !$raI['bDeAcc'] )  $g += floatval($raI['g_weight']);

            $sGerm = "";
            $raGerm = $this->oSLDBMaster->GetList( "G", "fk_sl_inventory='{$raI['_key']}'", array('sSortCol'=>'dStart','bSortDown'=>true) );
            foreach( $raGerm as $raG ) {
                $sGerm .= "<br/>{$raG['nGerm']}% on {$raG['dStart']}";
            }
/*
            $s .= "<div class='well' style='background-color:#cee;".($raI['_key'] == $kInv ? "border:3px solid #66a" : "")."'>"
                 ."Lot $sInvPrefix-{$raI['inv_number']}"
                 .($raI['bDeAcc'] ? " <b>Deaccessioned</b>" : "")
                 ."<br/>"
                 ."{$raI['g_weight']} grams".SEEDStd_ExpandIfNotEmpty($raI['location'], " at [[]]")."<br/>"
                 .SEEDStd_ExpandIfNotEmpty($raI['dCreation'], "created [[]]" )
                 .$sGerm
                 ."</div>";
*/
            $sInv .= "<tr><td>$sInvPrefix-{$raI['inv_number']}</td>"
                        ."<td>{$raI['g_weight']} g</td>"
                        ."<td>".SEEDStd_ExpandIfNotEmpty( $raI['location'], " @ [[]]  " ).($raI['bDeAcc'] ? "Deaccessioned" : "")."</td></tr>";
        }
        if( count($raInvList) > 1 ) {
            $sInv .= "<tr><td>&nbsp;</td><td style='border-top:1px solid black'>$g g</td><td>&nbsp;</td></tr>";
        }
        $sInv .= "</table></div>";
        $s .= $sInv;

//        $s .= "<p>Total amount: $g grams</p>";

        $s .= "<div class='well' style='background-color:#dfd'>"
             // nobody needs to know the accession key so don't confuse us by showing it
             //."<p>Accession $kAcc</p>"
             .$kfrA->Expand( "<p>[[S_name_en]] : [[P_name]] (cv [[P__key]])</p>" )
             ."<table border='0' cellpadding='2' cellspacing='10'>"
                 .$kfrA->Expand(
                     "<tr><td>Original name:</td><td>[[oname]]</td></tr>"
                    ."<tr><td>Batch:</td><td>[[batch_id]]</td></tr>"
                    ."<tr><td>Grower/Source:</td><td>[[x_member]]</td></tr>"
                    ."<tr><td>Harvest:</td><td>[[x_d_harvest]]</td></tr>"
                    ."<tr><td>Received:</td><td>[[x_d_received]]</td></tr>"
                  )
              ."</table>"
              ."</div>";

        return( $s );
    }


    private function drawList( $sSearchCond )
    {
        $s = "";

        $raListCols = array( array( 'label'=>'Species',  'col'=>'S_name_en', 'w'=>'20%' ),
                             array( 'label'=>'Cultivar', 'col'=>'P_name',    'w'=>'40%', 'trunc'=>50 ),
                             array( 'label'=>'Year',     'col'=>'A_x_d_harvest','w'=>'10%', 'align'=>'left'),
                             array( 'label'=>'Lot',      'col'=>'inv_number','w'=>'10%', 'align'=>'left'),
                             array( 'label'=>'Location', 'col'=>'location',  'w'=>'10%', 'align'=>'left'),
                             array( 'label'=>'g',        'col'=>'g_weight',  'w'=>'10%', 'align'=>'right')
        );

        /* Create the SEEDFormUI using parms stored in the sva. It will override these with any http parms.
         * We store the kCurr in sva so it can be compared with View[iCurr][_key]. If it doesn't match,
         * it means the view parms have changed, so iCurr is no longer valid
         */
        $iCurrI  = intval( $this->oSVA->VarGet( 'drawListI' ) );
        $kCurrI  = intval( $this->oSVA->VarGet( 'drawListK' ) );
        $iListWO = intval( $this->oSVA->VarGet( 'drawListWO' ) );
        $oUI = new SEEDFormUI( 'L', array( 'raUIParms' =>array( 'iCurr'         => array('v'=> $iCurrI),
                                                                'kCurr'         => array('v'=> $kCurrI),
                                                                'iWindowOffset' => array('v'=> $iListWO) ) ) );

        $oList = new SEEDFormUIList( $oUI );  // adds sortup and sortdown uiParms to oUI

        $raViewParms = array();
        if( ($iSort = $oUI->GetUIParm('sortup')) && isset($raListCols[$iSort-1]) ) {
// kluge sort by col ASC,_key ASC to ensure the same result set every time, so iCurr points to the same row every time
// but KFRelation doesn't know how to do compound ORDER BY and col,_key DESC doesn't work because that always implies ASC for the col
// so upgrade KFRel to allow multiple order by parameters
            $raViewParms = array('sSortCol'=>$raListCols[$iSort-1]['col']." ASC,_key",'bSortDown'=>false);
        } else if( ($iSort = $oUI->GetUIParm('sortdown')) && isset($raListCols[$iSort-1]) ) {
            $raViewParms = array('sSortCol'=>$raListCols[$iSort-1]['col']." DESC,_key",'bSortDown'=>true);
        } else {
            $raViewParms = array('sSortCol'=>"_key",'bSortDown'=>false);
        }
        $kfrel = $this->oSLDBMaster->GetKfrel( "IxAxPxS" );
        $oView = new KFRelationView( $kfrel, $sSearchCond, $raViewParms );
        $nViewSize = $oView->GetNumRows();

        // refresh iCurr and kCurr in case http parms changed them
        $iCurrI = $oUI->Get_iCurr();
        $kCurrI = $oUI->Get_kCurr();
        $iListWO = $oUI->Get_iWindowOffset();
$nWindowSize = 5;

        /* Tests:
         *     1) nViewSize could be 0.  Avoid tests that try to figure out what to do with iCurrI==0 and kCurrI==0 because those are irrelevant
         *     2) if the view was re-sorted or re-searched, iCurrI no longer points to the current row. Use kCurrI to try to find iCurrI, else clear kCurrI.
         *     3) if kCurrI is zero (new screen, previous curr row no longer in the view) and nViewSize>0, reset to the top row.
         */

        if( !$nViewSize ) {
            $kCurrI = $iCurrI = $iListWO = 0;
        }

        /* Test if the view has changed, so iCurrI no longer points to the kCurrI row
         */
        if( $kCurrI && ( !($kfrCurr = $oView->GetDataRow( $iCurrI )) || $kfrCurr->Key() != $kCurrI ) ) {
            // The view has changed. First attempt to find the new index of the stored key.
            $iCurrI = $kCurrI ? $oView->FindOffsetByKey( $kCurrI ) : -1;
            if( $iCurrI != -1 ) {
                $iListWO = $iCurrI - intval($nWindowSize / 2);
                $iListWO = SEEDStd_Range( $iListWO, 0, null );
            } else {
                // FindOffsetByKey failed to find the key
                // This can happen if the screen is new, or if a search just restarted the view
                $kCurrI = $iCurrI = $iListWO = 0;
            }
        }

        // If the list is new or has been reset (e.g. by a search, or by the code above) try to find the key of the iCurrI row
        if( !$kCurrI && $nViewSize && ($kfrCurr = $oView->GetDataRow($iCurrI)) ) {
            $kCurrI = $kfrCurr->Key();
        }

        $oUI->Set_iCurr($iCurrI);
        $oUI->Set_kCurr($kCurrI);
        $oUI->Set_iWindowOffset($iListWO);
        // this does not actually override any sfu http parms hanging around. we are now using js to remove sfu nav parms from the browser address bar, but
        // if not for that, the solution here fails because http parms override raParms. The other way to do it would be to have a bReset in the raParms
        // that overrides http.  That's a good idea anyway.

        if( $this->kluge_HighlightNewKInv ) {
            // Forget about all the other stuff. There's a new accession so highlight the new item.
            $iCurrI = $oView->FindOffsetByKey( $this->kluge_HighlightNewKInv );
            $kCurrI = $this->kluge_HighlightNewKInv;
            $iListWO = $iCurrI - intval($nWindowSize / 2);
            $iListWO = SEEDStd_Range( $iListWO, 0, null );

            $oUI->Set_iCurr($iCurrI);
            $oUI->Set_kCurr($kCurrI);
            $oUI->Set_iWindowOffset($iListWO);
        }


        // Now SEEDFormUI knows the window/curr offsets, as modified by http parms.
        // Fetch only the data that's needed for the window.
        $raListParms = array( 'tableWidth'=>'100%',
                              'cols' => $raListCols,
                              'nWindowSize' => $nWindowSize,
                              'fnRowTranslate' => array($this,'fnDrawListRowTranslate'),
                              'bUse_key' => true,
                              'kCurr' => $kCurrI
                            );
        $raListParms['iViewOffset'] = $oUI->Get_iWindowOffset();
        $raListParms['nViewSize'] = $nViewSize;
//$kfrel->kfdb->SetDebug(2);
        $raList = $oView->GetDataWindowRA( $oUI->Get_iWindowOffset(), $raListParms['nWindowSize'] );
//$kfrel->kfdb->SetDebug(0);
        //$raList = array_slice( $raList, $raListParms['iViewOffset'], $raListParms['nWindowSize']);

        $s = $oList->Style()
            ."<div style='margin-top:-25px;'>"
            .$oList->ListDrawInteractive( $raList, $raListParms )
            ."</div>";

        $this->oSVA->VarSet( 'drawListK', $kCurrI );
        $this->oSVA->VarSet( 'drawListI', $iCurrI );
        $this->oSVA->VarSet( 'drawListWO', $iListWO );

        $this->kInvCurr = $kCurrI;

        return( $s );
    }

    function fnDrawListRowTranslate( $raRow )
    {
        if( ($kfrC = $this->oSLDBMaster->GetKFR( "C", $raRow['fk_sl_collection'] )) ) {
            $raRow['inv_number'] = $kfrC->Value('inv_prefix')."-".$raRow['inv_number'];
        }

        $raRow['g_weight'] = ($raRow['g_weight'] ? $raRow['g_weight'] : '0')." g";

        if( !$raRow['A_x_d_harvest'] ) $raRow['A_x_d_harvest'] = $raRow['A_x_d_received'];

        if( $raRow['bDeAcc'] )  $raRow['P_name'] = "<span class='color:red'>{$raRow['P_name']} (Deaccessioned)</span>";

        return( $raRow );
    }

    function drawInvForm( $oFormI, $nNextInv, $bShowDeacc = true, $bWeightRO = false, $bShowLoc = true )
    {
        if( $oFormI->GetKey() ) {
            $sInvPrefix = ($kfrC = $this->oSLDBMaster->GetKFR( "C", $oFormI->Value('fk_sl_collection'))) ? $kfrC->Value('inv_prefix') : "X";
        } else {
            // this is a blank form
            if( !$this->kCurrCollection )  return( "" );    // don't allow new inventory on All Collections

            $kfrC = $this->oSLDBMaster->GetKFR( "C", $this->kCurrCollection );
            $sNextInv = $kfrC ? ($kfrC->Value('inv_prefix')."-".($nNextInv++)) : "unknown";
        }

        $s = "<fieldset>" //"<DIV style='border:1px solid #333;margin:20px;padding:10px;'>"
            ."<legend>".($oFormI->GetKey() ? ("Lot # $sInvPrefix-".$oFormI->Value('inv_number'))
                                           : "Add New Lot <span style='font-size:10pt'>( next number is $sNextInv )</span>" )
            ."</legend>"
            .$oFormI->HiddenKey()
            .$oFormI->Hidden( 'fk_sl_collection' )
            .$oFormI->Hidden( 'fk_sl_accession' )
            ."<table border='0'>"
                .$oFormI->ExpandForm(
                     "||| Weight (g)    || ".($bWeightRO ? "[[Value:g_weight]]" : "[[g_weight]]")
                    .($bShowLoc ? "||| Location      || [[location]]" : "")
." ".($oFormI->GetKey() ? ($this->kfdb->Query1( "SELECT loc_old FROM sl_inventory WHERE _key='".$oFormI->GetKey()."'")) : "")
//                    ."||| Split from    || [[parent_kInv]]"
//                    ."||| Split date    || [[dCreation]]"
                    .($bShowDeacc ? "||| Deaccessioned || [[bDeAcc]]" : "")
                 )
             ."</table>"
             ."</fieldset><P>&nbsp;</P>";

        return( $s );
    }

    function MakeInvId( $kC, $inv_number )
    {
        $sInvPrefix = ($kfrC = $this->oSLDBMaster->GetKFR( "C", $kC )) ? $kfrC->Value('inv_prefix') : "X";

        return( $sInvPrefix."-$inv_number" );
    }

    private function getCurrInv()
    {
        if( $this->kInvCurr &&
            ($kfrI = $this->oSLDBMaster->GetKFR( "I", $this->kInvCurr )) &&
            $kfrI->Value('fk_sl_accession') )
        {
            return( $kfrI );
        }
        return( null );
    }
}


class MyConsole extends Console01
{
    public $oW = NULL;

    function __construct( KeyFrameDB $kfdb, SEEDSessionAccount $sess, $raParms ) { parent::__construct( $kfdb, $sess, $raParms ); }
}

?>
