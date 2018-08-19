<?php

/* SEEDFormUI.php
 *
 * Copyright (c) 2008-2014 Seeds of Diversity Canada
 *
 * SEEDFormUIFrame is a collection of SEEDFormUIComponents. The Frame manages the UI relationships of the Components.
 *
 * SEEDFormUIComponent is a set of UI controls for a particular data set or relation. It manages a KeyFrameRelation
 * and a SEEDForm for that relation.
 *
 *
 * The order of operations of a Frame:
 *      1) Assemble the Frame and Components
 *      2) Call Update for each Component (this loads each Component's http parms and updates its datasource as necessary)
 *      3) Check Component interactions e.g. if a Parent Component's key has changed, reset its children and descendant Components
 *      4) At this point, all Components are up to date (and their datasources are too), so the caller can use them to draw UI widgets.
 *         Components employ the Frame to marshal parms from other components (e.g. navigation controls) for urls and forms.
 *
 *
 * SEEDFormUIComponent: raCompConfig =
 *                            "Label"    => The display name for the component
 *
 *                            List parms:
 *                            "ListCols" => array( array( "label"=>"Uid",     "colalias"=>"_key",     "w"=>50), ... )
 *                            "ListSize" => 10,  // default 0 means no limit
 *                            "ListSizePad" => 1,
 *
 *                            "sListUrlPage"   => page address for generated outgoing links - default = $_SERVER['PHP_SELF']
 *                            "sListUrlTarget" => link target for generated outgoing links - default = "_top"
 *                            "sFormAction"    => page address for generated form posts - default = $_SERVER['PHP_SELF']
 *                            "sFormTarget"    => target for generated outgoing form posts - default = "_top"
 *                            "sFormMethod"    => method for generated outgoing form posts - default = "post"
 *
 *                            "SearchToolCols" => array( array(srchcols1), ..., array(srchcols2) )
 *                                                    where srchcols is a list of label=>col; a SearchTool condition is drawn for each list
 *                            "fnListFilter"    => "EV_Item_rowFilter",
 *                            "fnFormDraw"      => "UsersFormDraw",
 */

include_once( STDINC."SEEDForm.php" );
include_once( STDINC."SEEDFormUI.php" );

class SEEDFormUIFrame
/********************
    Contains a collection of SEEDFormUIComponents, manages their UI interrelationships,
    provides services for marshalling output parms for component links and forms.
 */
{
    var $raComps = array();         // list of components in the frame
    private $raFrameUserParms = array();    // any input parms that are not owned by components

    function __construct()  {}

    function AddComponent( $cid, &$kfrel, $raCompConfig = array() )
    {
// kfrel parm should be generalized or only passed to KeyFrameUIFrame::AddComponentKF()
        return( $this->raComps[$cid] = $this->factory_SEEDFormUIComponent( $cid, $kfrel, $raCompConfig ) );
    }

    function InitializeFrame( $raParms = NULL, $bGPC = true )
    {
        if( !$raParms ) $raParms = &$_REQUEST;  // obsolete, not using this because it's too messy - use SetFrameControlParm

        foreach( $this->raComps as $oComp )  $oComp->InitializeComp( $raParms, $bGPC );
        foreach( $raParms as $k => $v ) {
            if( is_integer($k) ) continue;      // ignore the (duplicate) integer keys in $_REQUEST
            // this is messy, at least in that it pulls cookies into the GET stream
                // if( substr( $k, 0, 2 ) != 'sf' )  $this->raFrameUserParms[$k] = $bGPC ? SEEDSafeGPC_MagicStripSlashes($v) : $v;
        }

        /* Process interactions between components
         */
        // AddComponent should have raParms['Parent']=cidParent
        // From these build a structure of parent-children.
        // The frame should record and propagate the previous key of each comp. If a parent's key changed, reset the children recursively.
        // Components can change their keys during list initialization (e.g. search control, default row) so those computations have to be done
        // before this point.
    }

    function GetComponentKey( $cid )
    /*******************************
        Return the current key of the given component
     */
    {
        $k = 0;

        if( isset($this->raComps[$cid]) ) {
            $k = $this->raComps[$cid]->kfuiCurrRow->GetKey();
        }
        return( $k );
    }

    function SetFrameControlParm( $k, $v )
    /*************************************
        A control in its own client-drawn form, whose value should be propagated with the Comp data
     */
    {
        $this->raFrameUserParms[$k] = $v;
    }

    function marshalStateParms( $cidExclude = "" )
    /*********************************************
     */
    {
        $ra = $this->raFrameUserParms;  // data defined by the client at the frame level (e.g. external control forms) propagated with Comp data

        foreach( $this->raComps as $oComp ) {
            if( $cidExclude != $oComp->oForm->GetCid() ) {
                $ra1 = $oComp->marshalStateParms( array(), false );
                $ra = array_merge( $ra,$ra1 );
            }
        }
        return( $ra );
    }

    function factory_SEEDFormUIComponent( $cid, KeyFrameRelation $kfrel, $raCompConfig )
    {
//TODO: deprecate raSFParms, use raCompConfig['raSEEDFormParms']
        return( new SEEDFormUIComponent( $cid, $kfrel, $this, $raCompConfig ) );
    }
}


class SEEDFormUIComponent
/************************
    A UI Component has one kfrel to define its data model, and a component id (cid) to distinguish it from other components in the SEEDFormUIFrame.
    It contains a SEEDForm (or derivation) to allow one or more rows to be edited.
    It provides UI widgets to create integrated lists, search controls, etc, for the kfrel.
    It has an ability to record one or more 'selected' rows, which are represented in the list control and should be drawn in the form.
 */
{
    var $kfrel;
    var $oSFFrame = NULL;       // this is NULL if the component is not created through SEEDFormUIFrame::factory_SEEDFormUIComponent
    var $raCompConfig = array(); // user config parms for this component (e.g. ListCols)
    var $raViewParms = array(); // normalized set of view control parms (sSortCol, bSortDown, etc) taken from SEEDForm::ControlGet() or defaults
    var $raWindowParms = array(); // normalized set of window control parms (iOffset, iLimit)

    var $bNewRow = false;         // set by sfAx_new control code
    var $oForm;    // SEEDForm created internally

// kfrel parm should be generalized or only passed to KeyFrameUIFrame::AddComponentKF()
//TODO: deprecate raSFParms, use raCompConfig['raSEEDFormParms']
    function __construct( $cid, KeyFrameRelation $kfrel, $oSFFrame = NULL, $raCompConfig = array() )
    {
        $this->kfrel = $kfrel;
        $this->oSFFrame = $oSFFrame;
        $this->raCompConfig = $raCompConfig;

        $this->oForm = $this->factory_SEEDForm( $cid,
                                                isset($raCompConfig['raSEEDFormParms']) ? $raCompConfig['raSEEDFormParms'] : array() );

        /* Normalize raCompConfig
         */
        if( empty($this->raCompConfig['sListUrlPage']) )    $this->raCompConfig['sListUrlPage'] = $_SERVER['PHP_SELF'];
        if( empty($this->raCompConfig['sListUrlTarget']) )  $this->raCompConfig['sListUrlTarget'] = "_top";
        if( empty($this->raCompConfig['sFormAction']) )     $this->raCompConfig['sFormAction'] = $_SERVER['PHP_SELF'];
        if( empty($this->raCompConfig['sFormMethod']) )     $this->raCompConfig['sFormMethod'] = "post";
        if( empty($this->raCompConfig['sFormTarget']) )     $this->raCompConfig['sFormTarget'] = "_top";

        $this->raCompConfig['ListSize'] = intval(@$this->raCompConfig['ListSize']);  // 0 means no limit
    }

    function InitializeComp( $raSerial = NULL, $bGPC = true )
    /********************************************************
     */
    {
        // Load this component's http parms including key, data, control, updates db as necessary.
        // If raSerial is not defined, it uses _REQUEST with GPC by default
        $raUParms = array();
        if( $raSerial ) {
            $raUParms['raSerial'] = $raSerial;
            $raUParms['bGPC'] = $bGPC;
        }
        $this->oForm->Update($raUParms);

        /* After the form Update (and any possible insert) set the currRow with the current form key.
         * From this point on, any changes to the currRow (e.g. default positioning in a List) is mirrored
         * in the oForm too, so either can be equally referenced for values. We mirror in the oForm so that
         * it can draw form elements with the changed kfr.
         *
         * The operation here will replace the oForm->oDS->kfr with an identical copy of itself, but a lot
         * of complicated things can happen with component kfrs, so it's worth keeping the code simple.
         */
        $this->kfuiCurrRow->SetKey( $this->oForm->GetKey() );

        if( $this->oForm->ControlGet('newrow') == 1 ) {
            // command has been issued to create a new row
            $this->bNewRow = true;
            $this->kfuiCurrRow->SetKey( 0 ); // clear the curr row; this also clears the kfr in the oForm
        }

        if( $this->oForm->ControlGet('deleterow') == 1 ) {
            // command has been issued to delete the current row
            //
            // This can be accomplished with sfAd=1 (would be handled by the oForm->Update above) but it's harder to
            // detect that here so that the UI can be reset to reflect the missing row
            if( $this->oForm->GetKey() && ($kfr = $this->kfuiCurrRow->GetKFR()) ) {
                $bDelOk = isset($this->raCompConfig['fnPreDelete']) ? call_user_func( $this->raCompConfig['fnPreDelete'], $kfr ) : true;
                if( $bDelOk ) {
                    $kfr->StatusSet( KFRECORD_STATUS_DELETED );
                    $kfr->PutDBRow();
                    $this->kfuiCurrRow->SetKey( 0 ); // clear the curr row; this also clears the kfr in the oForm
                }
            }
        }


        /* Create a normalized complete set of View parms from the sfu parms
         */
        // var_dump($this->oForm->raCtrlGlobal);
        $this->raViewParms['sSortCol']  = $this->oForm->ControlGet('sSortCol');
        $this->raViewParms['sGroupCol'] = $this->oForm->ControlGet('sGroupCol');
        $this->raViewParms['bSortDown'] = ($this->oForm->ControlGet('bSortDown') ? true : false);
        $this->raViewParms['iStatus'] = intval($this->oForm->ControlGet('iStatus'));
        // this is an alternate way to specify sort parms, which overrides the above
        if( $this->oForm->ControlGet('sortup') ) {
            $this->raViewParms['sSortCol'] = $this->oForm->ControlGet('sortup');
            $this->raViewParms['bSortDown'] = false;
        } else if( $this->oForm->ControlGet('sortdown') ) {
            $this->raViewParms['sSortCol'] = $this->oForm->ControlGet('sortdown');
            $this->raViewParms['bSortDown'] = true;
        }

        /* Create a normalized complete set of Window parms from the sfu parms
         */
        $this->raWindowParms['iOffset'] = intval($this->oForm->CtrlGlobal('iOffset'));
        if( !($this->raWindowParms['iLimit'] = intval($this->oForm->CtrlGlobal('iLimit'))) ) {
            if( !($this->raWindowParms['iLimit'] = $this->raCompConfig['ListSize']) ) {  // ListSize==0 means no limit
                $this->raWindowParms['iLimit'] = -1;
            }
        }
    }

    function factory_SEEDForm( $cid, $raSFParms )
    { // Override if the SEEDForm is a derived class
        return( new SEEDForm( $cid, $raSFParms ) );
    }
}

include_once( "KFUIForm.php" );

class KeyFrameUIFrame extends SEEDFormUIFrame
/********************
 */
{
    function __construct()
    {
        parent::__construct();
    }


//TODO: deprecate raSFParms, use raCompConfig['raSEEDFormParms']
    function factory_SEEDFormUIComponent( $cid, KeyFrameRelation $kfrel, $raCompConfig )
    {
        return( new KeyFrameUIComponent( $cid, $kfrel, $this, $raCompConfig ) );
    }
}



class KeyFrameUIComponent extends SEEDFormUIComponent
/************************
 */
{
    var $kfrelParms = array();  // referenced by _kfuiComponentCurrRow
    var $kfuiCurrRow = NULL;

    var $kfViewCond = "";         // cond and parms that define the current KFView
    var $viewCondMD5 = "";

    private $oWindow = NULL;      // created if and when needed : computes view and window

    function __construct( $cid, KeyFrameRelation $kfrel, $oSFFrame = NULL, $raCompConfig = array() )
    /***********************************************************************************************
     */
    {
        parent::__construct( $cid, $kfrel, $oSFFrame, $raCompConfig );

        // The currRow helps us manage novel rows that need to be loaded from time to time (e.g. the first time a
        // list is shown, the curr row defaults to the first visible row; also sometimes a new row is created with a
        // forced key value and we'd like to have a faked kfr to draw the form). The kfr in the oForm is always mirrored
        // to be the same as kfuiCurrRow->kfr so either can be used to access values.
        $this->kfuiCurrRow = new _kfuiComponentCurrRow($this);
    }

    function factory_SEEDForm( $cid, $raSFParms )
    {
        return( new KeyFrameUIForm( $this->kfrel, $cid, $raSFParms ) );
    }

    function EncodeUrlHREF( $raChangeParms = array(), $bAllComponents = true )
    /*************************************************************************
     */
    {
        return( "HREF='".$this->raCompConfig['sListUrlPage']."?".$this->EncodeUrl($raChangeParms,$bAllComponents)."' target='".$this->raCompConfig['sListUrlTarget']."'" );
    }

    function EncodeUrlLink( $raChangeParms = array(), $bAllComponents = true )
    /*************************************************************************
     */
    {
        return( $this->raCompConfig['sListUrlPage']."?".$this->EncodeUrl($raChangeParms,$bAllComponents) );
    }

    function EncodeUrl( $raChangeParms = array(), $bAllComponents = true )
    /*********************************************************************
     */
    {
        return( SEEDStd_ParmsRA2URL( $this->marshalStateParms( $raChangeParms, $bAllComponents ) ) );
    }

    function EncodeHiddenFormParms( $raChangeParms = array(), $bAllComponents = true )
    /*********************************************************************************
     */
    {
        $s = "";
        $ra = $this->marshalStateParms( $raChangeParms, $bAllComponents );
        foreach( $ra as $k => $v ) {
            $s .= SEEDForm_Hidden( $k, $v );   // don't use formparms here because marshal already did that
        }
        return( $s );
    }

    function marshalStateParms( $raChangeParms, $bAllComponents )
    /************************************************************
        Gather a 1D array of this component's global navigation parms that need to be propagated to the next frame page.
        raChangeParms is an array of global control parms that overwrite the current parms (so they're different for the next frame page).
        raChangeParms can use the special parm 'kCurrRow', which is called sf{cid}k
        bAllComponents also gathers the parms of other components and the frame.

        e.g. $raChangeParms( 'kCurrRow'=>2, 'iOffset'=>3 )    moves the selection to _key=2 and the window to view offset 3
        e.g. $raChangeParms( 'iOffset'=>3 )                   moves the window to view offset 3 with the current key propagated
        e.g. $raChangeParms( 'kCurrRow'=>NULL )               sf{cid}k is not written, allowing the code to do that
        e.g. $raChangeParms( 'kCurrRow'=>0 )                  sf{cid}k=0 indicates a new row
     */
    {
        $ra = array();

        if( $this->kfuiCurrRow && $this->kfuiCurrRow->GetKey() ) {
            $ra[$this->oForm->oFormParms->sfParmKey()] = $this->kfuiCurrRow->GetKey();
        }
// TODO access this array more nicely
        foreach( $this->oForm->raCtrlGlobal as $k => $v ) {
            if( substr($k,0,7) == "colsel_" ||
                substr($k,0,5) == "srch_" ||
                substr($k,0,8) == "persist_" ||   // user can define persistent parms that will always be propagated
                in_array( $k, array("sortup","sortdown","sSortCol","bSortDown",
                                    "sGroupCol","iStatus","iOffset","iLimit") ) )
            {
                $ra[$this->oForm->oFormParms->sfParmControlGlobal($k)] = $v;
            }
        }
        $ra[$this->oForm->oFormParms->sfParmControlGlobal('viewCondMD5')] = $this->viewCondMD5;

        // Overwrite with the changed parms
        foreach( $raChangeParms as $k => $v ) {
            if( $k == 'kCurrRow' ) {
                // NULL means exclude this entirely so other code can write it
                // 0 means to write k=0
                if( $v === NULL ) {
                    unset($ra[$this->oForm->oFormParms->sfParmKey()]);
                } else {
                    $ra[$this->oForm->oFormParms->sfParmKey()] = $v;
                }
            } else if( substr( $k, 0, 3 ) == "op_" ) {  // this allows delete,hide,reset to be encoded but there is currently no UI management for the missing rows
                $ra[$this->oForm->oFormParms->sfParmOp(substr($k,3,1))] = 1;
            } else {
                // NULL means exclude this entirely so other code can write it
                // 0 means to write v=0, "" means to write ""
                if( $v === NULL ) {
                    unset($ra[$this->oForm->oFormParms->sfParmControlGlobal($k)]);
                } else {
                    $ra[$this->oForm->oFormParms->sfParmControlGlobal($k)] = $v;
                }
            }
        }

        if( $bAllComponents && $this->oSFFrame ) {
            $ra1 = $this->oSFFrame->marshalStateParms( $this->oForm->GetCid() );
            $ra = array_merge( $ra,$ra1 );
        }
        return( $ra );
    }


    function GetCurrKey()       { return( $this->kfuiCurrRow->GetKey() ); }
    function GetCurrValue( $k ) { return( $this->kfuiCurrRow->GetValue( $k ) ); }
    function GetCurrKFR()       { return( $this->kfuiCurrRow->GetKFR() ); }


    function ButtonNewRow()
    /**********************
     */
    {
    	$sLabel = "New ".(@$this->raCompConfig['Label'] ? $this->raCompConfig['Label'] : "Row");

        $s = "<FORM action='{$this->raCompConfig['sFormAction']}' method='{$this->raCompConfig['sFormMethod']}'"
            .(empty($this->raCompConfig['sFormTarget']) ? "" : " target='{$this->raCompConfig['sFormTarget']}'")
            .">"
            .$this->EncodeHiddenFormParms(array('kCurrRow'=>0,'newrow'=>true))
            ."<INPUT type='submit' value='$sLabel'/></FORM>";

        return( $s );
    }


    function ButtonDeleteRow( $kRow = 0 )
    /************************************
       Create a mini-form containing one button that will delete the given row
       0 = the current row (if no current row, this function generates a single &nbsp;)
     */
    {
        $raFormParms = array( 'deleterow' => true );
        if( $kRow ) {
// TODO: the update code is designed to delete the current row. Most UIs were good this way, but there could
//       be tables of rows with delete buttons on non-selected rows that shouldn't have to be selected first.
//       And this way, we're unselecting the current (non-deleted) row.
//       Should fix the update code to allow deletion of non-current rows.

            $raFormParms['kCurrRow'] = $kRow;
            //$raFormParms['kDelRow'] = $kRow;
        } else {
            // delete the current row
            if( !$this->oForm->GetKey() )  return( "&nbsp;" );
            //$raFormParms['kDelRow'] = $this->kfuiCurrRow->GetKey();
            //$raFormParms['kCurrRow'] = 0;  OR would it be cool to find the next row in the list?
        }

    	$sLabel = "Delete ".(@$this->raCompConfig['Label'] ? $this->raCompConfig['Label'] : "Row");

        $s = "<FORM action='{$this->raCompConfig['sFormAction']}' method='{$this->raCompConfig['sFormMethod']}'"
            .(empty($this->raCompConfig['sFormTarget']) ? "" : " target='{$this->raCompConfig['sFormTarget']}'")
            .">"
            .$this->EncodeHiddenFormParms($raFormParms)
            ."<INPUT type='submit' value='$sLabel'/></FORM>";

        return( $s );
    }

    function FormDraw()
    /******************
     */
    {
        $s = "<FORM action='{$this->raCompConfig['sFormAction']}' method='{$this->raCompConfig['sFormMethod']}'"
            .(empty($this->raCompConfig['sFormTarget']) ? "" : " target='{$this->raCompConfig['sFormTarget']}'")
            .">"
// raCompConfig should tell us whether the form allows the user to enter a key
            .$this->oForm->HiddenKey()    // the form always encodes the current key (0 if the form is blank)
            .$this->EncodeHiddenFormParms(array('kCurrRow'=>NULL))
            ."<h3>".($this->oForm->GetKey() ? "Edit" : "Enter new")." {$this->raCompConfig['Label']}</h3>";

        /* If the caller has defined a formdraw function, pass it the oForm.
         * Else draw a default form based on the kfrel fields.
         */
        if( isset($this->raCompConfig['fnFormDraw']) ) {
            $s .= call_user_func($this->raCompConfig['fnFormDraw'], $this->oForm );
        } else {
            $s .= "<TABLE cellpadding='5' width='50%' align='center'>";

            foreach( $this->kfrel->baseTable['Fields'] as $f ) {
                // don't put internal fields on the form
                if( $f['col'][0] == '_' ) continue;

                $val = $this->kfuiCurrRow->GetValueEnt($f['col']);
                $bFKFound = false;
                // draw preset foreign keys as non-editable
                if( substr( $f['col'],0,3 ) == "fk_" ) {
                   if( isset( $this->raCompConfig['fkDefaults'] ) ) {
                        foreach( $this->raCompConfig['fkDefaults'] as $fkTableName => $fkCid ) {
                            if( $f['col'] == "fk_".$fkTableName && $this->oSFFrame ) {
                                $val = $this->oSFFrame->GetComponentKey($fkCid);
                                $bFKFound = true;
                            }
                        }
                    }
                    if( !$bFKFound && isset($this->kfrelParms['fkDefaults']) ) {
                        foreach( $this->kfrelParms['fkDefaults'] as $fkTableName => $fkKey ) {
                            if( $f['col'] == "fk_".$fkTableName ) {
                                $val = $fkKey;
                                $bFKFound = true;
                            }
                        }
                    }
                }
// encode kfui field names correctly
                if( $bFKFound ) {
                    $s .= "<TR><TD>{$f['col']}</TD><TD>$val<INPUT type='hidden' name='{$f['col']}' value='$val'></TD></TR>";
                } else {
                    $s .= "<TR><TD>{$f['col']}</TD><TD>".$this->oForm->Text( $f['col'], "" )
                    /*"<INPUT type='text' name='{$f['col']}' value='$val'> */
                    ."</TD></TR>";
                }
            }
            $s .= "</TABLE>";
            $s .= "<INPUT type='submit' value='Save'>";
        }

        $s .= "</FORM>";

        return( $s );
    }


    function ListInit()
    {
        $this->oWindow = new _kfuiComponentWindow( $this );
        $this->oWindow->Init();
    }

    function ListGetWindowRows()
    {
        return( $this->oWindow->raWindowRows );
    }

    function ListDraw( $raListParms = array() )
    /******************************************
        raListParms:
            sListFilter = a client-supplied condition string, using addslashes on values (e.g. "city='Myer\'s Corners'")
     */
    {
        $s = "";

        if( !$this->oWindow ) {
            $this->ListInit();
        }

        $bShowDot = (@$this->raCompConfig['ListShowDot'] ? 1 : 0 );

        $raScrollOffsets = $this->oWindow->ScrollOffsets();


        /* Draw the list headings
         */
        $s .= "<STYLE>"
             ."table.sfuiListTable { border-collapse:separate; border-spacing:2px; }"   // allows white lines between List cells - the moz default, but BS collapses to zero
             .".sfuiList, .sfuiList TD, .sfuiList TH { font-family: verdana,helvetica, sans serif; }"
             .".sfuiListRow0    { background-color: #eee; font-size:8pt; }"
             .".sfuiListRow1    { background-color: #ccc; font-size:8pt; }"
             .".sfuiListRow2    { background-color: #44f; font-size:8pt; color: #fff; }"
             .".sfuiListRowEnd A:link    { color:#fff; } "
             .".sfuiListRowEnd A:visited { color:#fff; } "
             .".sfuiListRowEnd  { background-color: #777; font-size:8pt; color: #fff; }"
             .".sfuiListRowTop  { background-color: #777; font-size:8pt; color: #fff; }"
             .".sfuiListButtons { font-size:7pt; }"
             .".sfuiListHead    { font-size:10pt; }"
             .".sfuiListColsel  { font-size:8pt; background-color:#e4e4e4; }"
             ."</STYLE>";
        // List Table
        $s .= "<TABLE class='sfuiList' align='center'><TR><TD valign='top'>";
        // Controls are in a table within the top cell.
        // A form encloses these controls: when colsel lists are activated, they don't cause form submission from other
        // areas of the component, but all colsels are submitted together.
        $s .= "<TABLE class='sfuiListTable'><TR class='sfuiListHead'>"
             ."<FORM method='post' action='{$this->raCompConfig['sFormAction']}' target='{$this->raCompConfig['sFormTarget']}'>";
// $this->HiddenFormParms( $this->kfuiCurrRow->GetKey() );
        if( $bShowDot ) $s .= "<TH>&nbsp;</TH>";
        foreach( $this->raCompConfig['ListCols'] as $col ) {
            /* Draw the sorting arrows:
             *     sorting down: show solid down arrow      link to sort up
             *     sorting up:   show solid up arrow        link to sort down
             *     else:         show empty arrow           link to sort up
             *
             * The triangles have to be available in W_ROOT_STD/img, which has to be defined and has to be under the webroot).
             */
            $bSortingUp   = ($this->raViewParms['sSortCol']==$col['colalias'] && !$this->raViewParms['bSortDown']);
            $bSortingDown = ($this->raViewParms['sSortCol']==$col['colalias'] && $this->raViewParms['bSortDown']);

            $imgsrc = ( $bSortingDown ? (W_ROOT_STD."img/triangle_blue_down.gif") :
                       ($bSortingUp   ? (W_ROOT_STD."img/triangle_blue_up.gif") :
                                        (W_ROOT_STD."img/triangle_blue_up_empty.gif")));
            $href   = ( $bSortingDown ? $this->EncodeUrlHREF(array("sortup"   => $col['colalias'], "sortdown"=>"")) :
                       ($bSortingUp   ? $this->EncodeUrlHREF(array("sortdown" => $col['colalias'], "sortup"=>"")) :
                                        $this->EncodeUrlHREF(array("sortup"   => $col['colalias'], "sortdown"=>"")) ));

            $s .= "<TH width='{$col['w']}' valign='top'>"
                 ."<A $href><IMG src='$imgsrc' border='0'/></A>"
                 ."<BR/>".$col['label']."<BR/>";

            if( isset($col['colsel']) ) {
                $colsel = $this->oForm->CtrlGlobalIsSet('colsel_'.$col['colalias'])
                          ? $this->oForm->CtrlGlobal('colsel_'.$col['colalias']) : "*";
                $s .= "<SELECT onchange='submit();' class='sfuiListColsel' name='".$this->oForm->oFormParms->sfParmControlGlobal("colsel_".$col['colalias'])."'>"
                     ."<OPTION value='*'".($colsel=='*' ? " SELECTED" : "").">-- ALL --</OPTION>";
                if( ($kfr = $this->kfrel->CreateRecordCursor( @$col['colsel']['filter'] )) ) {
                    $raUnique = array();
                    while( $kfr->CursorFetch() ) {
                        $raUnique[$kfr->value($col['colalias'])] = 1;
                    }
                    $kfr = NULL;
                    ksort( $raUnique );
                    foreach( $raUnique as $k => $v ) {
                        $s .= "<OPTION value='$k'".($k==$colsel ? " SELECTED" : "").">$k</OPTION>";
                    }
                }
                $s .= "</SELECT>";
            }
            $s .= "</TH>";
        }
        $s .= "</FORM></TR>";  // end of List control area - form encloses all colsel controls

        /* Draw the List start header
         */
        $s .= "<TR class='sfuiListRowTop'><TD colspan='".$this->oWindow->WindowCols()."'>&nbsp;&nbsp;&nbsp;"
             .(($nAbove = $this->oWindow->RowsAboveWindow()) ? ($nAbove.($nAbove > 1 ? " rows" : " row")." above") : "Top of List");
        if( $nAbove ) {
            $s .= "<SPAN style='float:right;margin-right:3px;'>"
                 .$this->_listButton( "TOP", array( 'offset'=>$raScrollOffsets['top'] ) )
                 .SEEDStd_StrNBSP("",5)
                 .$this->_listButton( "PAGE", array( 'offset'=>$raScrollOffsets['pageup'], 'img'=>"up2" ) )
                 .SEEDStd_StrNBSP("",5)
                 .$this->_listButton( "UP", array( 'offset'=>$raScrollOffsets['up'], 'img'=>"up" ) );
        }
        $s .= "</TD></TR>";

        /* Draw the List rows
         */
        $i = 0;
        $kfrParms = array();

        foreach( $this->oWindow->raWindowRows as $kfr ) {
            // set the row colour
            if( $this->kfuiCurrRow->GetKey() && $kfr->Key() == $this->kfuiCurrRow->GetKey() ) {
                $nClass = 2;
            } else {
                $nClass = ($i%2);
            }

            $s .= "<TR id='r".$kfr->Key()."' class='sfuiListRow$nClass'>";
            if( $bShowDot ) {
                $s .= "<TD><A ".$this->EncodeUrlHREF(array('kCurrRow'=>$kfr->Key()))."><IMG src='".W_ROOT."std/img/dot1.gif' border='0'/></A></TD>";
            }

            /* Optionally translate the row values using a derived method and/or a callback function
             */
            $raValues = $this->ListRowTranslate( $kfr );

            foreach( $this->raCompConfig['ListCols'] as $col ) {
                $val = $raValues[$col['colalias']];
                if( ($n = intval(@$col['trunc'])) && $n < strlen($val) ) {
                    $val = substr( $val, 0, $n )."...";
                }
                $s .= "<TD style='cursor: pointer' onclick='location.replace(\"".$this->EncodeUrlLink(array('kCurrRow'=>$kfr->Key()))."\");'>"
                     .$val."</TD>";
            }
            $s .= "</TR>";
            $i++;
        }

        /* Draw the List end header
         * Window not limited : draw End of List
         * Window limited : draw End of List if last view item is shown, else show scroll
         */
        $s .= "<TR class='sfuiListRowEnd'><TD colspan='".$this->oWindow->WindowCols()."'>&nbsp;&nbsp;&nbsp;"
             .(($n = $this->oWindow->RowsBelowWindow()) ? ($n.($n > 1 ? " rows" : " row")." below")
                                                        : ("<A ".$this->EncodeUrlHREF(array('kCurrRow'=>0,'newrow'=>true)).">End of List</A>"));
        // List size buttons
        $s .= "<SPAN style='float:right;margin-right:3px;'>"
             .$this->_listButton( "[10]", array( 'limit'=>10 ) )
             .SEEDStd_StrNBSP("",5)
             .$this->_listButton( "[50]", array( 'limit'=>50 ) )
             .SEEDStd_StrNBSP("",5)
             // special case: the list can't yet compute scroll-up links when this button is chosen so offset must be cleared (see note above)
             .$this->_listButton( "[All]", array( 'limit'=>-1, 'offset'=>$raScrollOffsets['top'] ) );
        if( $this->oWindow->RowsBelowWindow() ) {
            $s .= SEEDStd_StrNBSP("",15)
                 .$this->_listButton( "BOTTOM", array( 'offset'=>$raScrollOffsets['bottom'] ) )
                 .SEEDStd_StrNBSP("",5)
                 .$this->_listButton( "PAGE", array( 'offset'=>$raScrollOffsets['pagedown'], 'img'=>"down2" ) )
                 .SEEDStd_StrNBSP("",5)
                 .$this->_listButton( "DOWN", array( 'offset'=>$raScrollOffsets['down'], 'img'=>"down" ) );
        }
        $s .= "</SPAN></TD></TR>";
        //Not sure this is wanted - if you do, just access the nWindow variables and uncomment
        //if( $this->oWindow->WindowIsLimited() ) {
        //    if( @$this->raCompConfig['ListSizePad'] ) {
        //        $nPad = $nWindowSize - count($this->oWindow->raWindowRows);
        //        for( $j = 0; $j < $nPad; ++$j ) {
        //            $s .= "<TR class='sfuiListRow0'><TD colspan='".$this->oWindow->WindowCols()."'>&nbsp;</TD></TR>";
        //        }
        //    }
        //}
        $s .= "</TABLE></TD>";

        /* If using a limited list size, and the selected row has scrolled off the list, draw a link to get back to it
         */
        if( $this->oWindow->CurrRowIsOutsideWindow() ) {
            $s .= "<TD class='sfuiListButtons' valign='center'>"
                 .$this->_listButton( "<FONT color='blue'>FIND<BR/>SELECTION</FONT>", array( 'offset'=>$this->oWindow->IdealOffset() ) )
                 ."</TD>";
        }
        $s .= "</TR></TABLE>";

        return( $s );
    }

    private function _listButton( $label, $raParms )
    /***********************************************
        Draw the TOP, PAGE UP... buttons
     */
    {
        $raChange = array();
        if( isset($raParms['offset']) )  $raChange['iOffset'] = $raParms['offset'];
        if( isset($raParms['limit']) )   $raChange['iLimit'] = $raParms['limit'];

        $img = @$raParms['img'];

        $s = "<A ".$this->EncodeUrlHREF($raChange)." style='color:white;text-decoration:none;font-size:7pt;'>"
            ."<B>$label</B>"
            .($img ? ("&nbsp;<IMG src='".W_ROOT_STD."img/triangle_blue_${img}_empty.gif' border='0'/>") : "")
            ."</A>";
        return( $s );
    }

    function ListFilter()
    /********************
        Provide a user-defined condition clause that will be AND-ed with other list conditions.
        a) Override this method
        b) Set fnListFilter callback in the comp config
        c) Default base method has no filter condition

        The condition must use addslashes on values!
     */
    {
        $sCond = "";
        if( isset($this->raCompConfig['fnListFilter']) ) {
            $sCond = call_user_func( $this->raCompConfig['fnListFilter'] );
        }
        return( $sCond );
    }

    function ListRowTranslate( $kfr )
    /********************************
        Translate values from the native db row to a human-readable row.

        This method provides the kfr because sometimes you want a db connection to look up stuff.
        An alternate method override is ListRowTranslateRA which provides the array values.
        Both return an array of values.

        a) Override ListRowTranslate/ListRowTranslateRA
        b) Set fnListRowTranslate/fnListRowTranslateRA callback in the comp config
        c) Default base method doesn't change the values
     */
    {
        if( isset($this->raCompConfig['fnListRowTranslate']) ) {
            $raValues = call_user_func( $this->raCompConfig['fnListRowTranslate'], $kfr );
        } else if( isset($this->raCompConfig['fnListRowTranslateRA']) ) {
            $raValues = call_user_func( $this->raCompConfig['fnListRowTranslateRA'], $kfr->ValuesRA() );
        } else {
            // note: in php5 the ListRowTranslateRA method gets a copy of the kfr values, so it can change the array without affecting the kfr
            $raValues = $this->ListRowTranslateRA( $kfr->ValuesRA() );
        }
        return( $raValues );
    }

    function ListRowTranslateRA( $raValues )  { return( $raValues ); }

    function TableDraw( $parms = array() )
    /*************************************
        Show the KF relation in a table.

        parms:
            bForm: true=draw a form beside the table
     */
    {
    }

    function TableItem( $raValues )
    /******************************
     */
    {
    }

// TODO: defer to SEEDFormUI::ControlFormDraw -- currently called SEEDFormUI::FormDraw()
    function ControlFormDraw( $sControl, $raChangeParms = array() )
    /**************************************************************
        Draw a control in a <FORM> that propagates the component's parms.
        You should be able to have any number of these forms on your UI, and they'll all propagate each others' state via CtrlGlobal

        $raChangeParms : manage which parms are excluded, altered, or added.  See marshalStateParms
     */
    {
        $s = "<FORM action='{$this->raCompConfig['sFormAction']}' method='{$this->raCompConfig['sFormMethod']}'"
            .(empty($this->raCompConfig['sFormTarget']) ? "" : " target='{$this->raCompConfig['sFormTarget']}'")
            .">"
            .$sControl
            .$this->EncodeHiddenFormParms( $raChangeParms )
            ."</FORM>";

         return( $s );
    }


    function SearchToolDraw( $raChangeParms = array() )
    /**************************************************
        raCompConfig:
            SearchToolTemplate = string containing [[fieldsN]] : replaced with <SELECT> of the fields for the Nth row of the control
                                                   [[opN]]     : replaced with <SELECT> of the operations
                                                   [[textN]]   : replaced with <INPUT text> of the search value
                                 If not specified, a default template is created for the number of rows needed
            SearchToolCols = array( array(label => col, label => col, ... ),
                                    array(label => col, label => col, ...), ... )
                             Each inner array defines the fields searchable by each row of the SearchTool.
                             The number of inner arrays defines the number of rows.
     */
    {
        $raSearchControlParms = $this->_searchToolParms();

        for( $i = 1; $i <= $raSearchControlParms['nRows']; ++$i ) {
            // NULL stops these vals from being written in HiddenFormParms
            $raChangeParms["srch_fld$i"] = NULL;
            $raChangeParms["srch_op$i"] = NULL;
            $raChangeParms["srch_val$i"] = NULL;
        }
        $s = "<FORM action='{$this->raCompConfig['sFormAction']}' method='{$this->raCompConfig['sFormMethod']}'"
            .(empty($this->raCompConfig['sFormTarget']) ? "" : " target='{$this->raCompConfig['sFormTarget']}'")
            .">"
            .$this->oForm->SearchControl( $raSearchControlParms )
            .$this->EncodeHiddenFormParms( $raChangeParms )
            ."</FORM>";

         return( $s );
    }

    function SearchToolGetCond()
    /***************************
     */
    {
        return( $this->oForm->SearchControlDBCond( $this->_searchToolParms() ) );
    }

    function _searchToolParms()
    {
        $raSearchControlParms = array();

        if( isset($this->raCompConfig['SearchToolDef']['filterCols']) ) {
            /* Defines the list of search fields for each row of the SearchTool text-search control.
             * This also implicitly defines the number of rows for that control.
             */
            $raSearchControlParms['filters'] = $this->raCompConfig['SearchToolDef']['filterCols'];

        } else if( isset($this->raCompConfig['SearchToolCols']) ) {
    // DEPRECATE, use SearchToolDef instead
            /* SearchToolCols defines the list of search fields for each row of the SearchTool control.
             * This also implicitly defines the number of rows.
             */
            $raSearchControlParms['filters'] = $this->raCompConfig['SearchToolCols'];
        } else {
            /* If the SearchToolCols are not defined, make a single-row Search Tool with a list of all cols in the kfrel
             */
            foreach( $this->kfrel->raColAlias as $rCol ) {
                $raSearchControlParms['filters'][0][$rCol] = $rCol;
            }
        }
        $raSearchControlParms['nRows'] = count($raSearchControlParms['filters']);


        if( isset($this->raCompConfig['SearchToolDef']['template']) ) {
            $raSearchControlParms['template'] = $this->raCompConfig['SearchToolDef']['template'];

        } else if( isset($this->raCompConfig['SearchToolTemplate']) ) {
    // DEPRECATE, use SearchToolDef instead
            $raSearchControlParms['template'] = $this->raCompConfig['SearchToolTemplate'];
        } else {
            $s = "<STYLE>#kfuiSearch,#kfuiSearch input,#kfuiSearch select { font-size:9pt;}"
                ."</STYLE>"
                ."<DIV id='kfuiSearch' style='display:inline-block'>";
            for( $i = 1; $i <= $raSearchControlParms['nRows']; ++$i ) {
                if( $i == 1 ) {
                    $s .= "<DIV style='width:4ex;display:inline-block;'>&nbsp;</DIV>";
                } else {
                    $s .= "<BR/><DIV style='width:4ex;display:inline-block;'>and&nbsp;</DIV>";
                }
                $s .= "[[fields$i]] [[op$i]] [[text$i]]";
            }
            $s .= "</DIV>"
                 ."&nbsp;&nbsp;<INPUT type='submit' value='Search'/>";
            $raSearchControlParms['template'] = $s;
        }

        if( isset($this->raCompConfig['SearchToolDef']['controls']) ) {
            $raSearchControlParms['controls'] = $this->raCompConfig['SearchToolDef']['controls'];
        }

        return( $raSearchControlParms );
    }

    function DrawFromTemplate( $sTemplate )
    {
        /* Kind of important ListDraw comes first, because it sets a default row (the first in the list) which is necessary for Form etc
         */
        while( true ) {
            if( (strpos( $sTemplate, "[[ListDraw]]" )) !== false ) {
                $sTemplate = str_replace( "[[ListDraw]]", $this->ListDraw(), $sTemplate );
            } else if( (strpos( $sTemplate, "[[FormDraw]]" )) !== false ) {
                $sTemplate = str_replace( "[[FormDraw]]", $this->FormDraw(), $sTemplate );
            } else if( (strpos( $sTemplate, "[[ButtonNewRow]]" )) !== false ) {
                $sTemplate = str_replace( "[[ButtonNewRow]]", $this->ButtonNewRow(), $sTemplate );
            } else if( (strpos( $sTemplate, "[[ButtonDeleteRow]]" )) !== false ) {
                $sTemplate = str_replace( "[[ButtonDeleteRow]]", $this->ButtonDeleteRow(), $sTemplate );
            } else {
                break;
            }
        }
        return( $sTemplate );
    }
}


class _kfuiComponentWindow extends SEEDFormUIListWindow
/*************************
    This encapsulates the view and window computation
 */
{
    private $oComp;

    public $raWindowRows = NULL;
    public $oView = NULL;


    function __construct( SEEDFormUIComponent $oComp ) {
        $this->oComp = $oComp;
        parent::__construct();
    }

    function Init( $raViewParms = array() )
    {
        list( $this->oView, $this->raWindowRows ) = $this->getWindowRows( $raViewParms );
        $this->setCurrRow();

        $bWindowLimited = ($this->oComp->raWindowParms['iLimit'] > 0);   // true: window has a limited size

        $this->InitListWindow(
            array( 'nViewSize'      => $this->oView->GetNumRows(),
                   'iWindowOffset'  => $this->oComp->raWindowParms['iOffset'],         // offset of the top of the window within the view
                   'bWindowLimited' => $bWindowLimited,
                   'nWindowSize'    => ($bWindowLimited ? $this->oComp->raWindowParms['iLimit'] : count($this->raWindowRows)),
                   // this has to be after setCurrRow because of dependency on kfuiCurrRow
                   'iCurrOffset' => ($bWindowLimited ? $this->oView->FindOffsetByKey($this->oComp->kfuiCurrRow->GetKey()) : 0)
        ));
    }

    function WindowCols()
    {
        return( count($this->oComp->raCompConfig['ListCols'])     // number of defined columns (optionally plus the red dot)
                + (@$this->oComp->raCompConfig['ListShowDot'] ? 1 : 0 ) );
    }

    function CurrRowIsOutsideWindow()
    {
        return( $this->oComp->kfuiCurrRow->GetKey() ? parent::CurrRowIsOutsideWindow() : false );
    }

    private function getWindowRows( $raListParms = array() )
    /*******************************************************
        Get the current view and the current window, based on raParms and the Component's controls (sort, filter, offset, limit)
     */
    {
//var_dump($this->oComp->oForm->raCtrlGlobal);echo "<BR><BR>";

        /* Build the condition for CreateRecordCursor
         */
        $raCond = array();

        // get client-supplied list filter from method arguments, derived method, and/or config-specified callback function
        if( !empty($raListParms['sListFilter']) )               $raCond[] = "(".$raListParms['sListFilter'].")";
        if( ($sCond = $this->oComp->ListFilter()) )             $raCond[] = "(".$sCond.")";

        // get list filter from this component's search tool
        if( ($sCond = $this->oComp->SearchToolGetCond()) )      $raCond[] = "(".$sCond.")";

        // get list filter from list column select lists
// TODO access this array more nicely
        foreach( $this->oComp->oForm->raCtrlGlobal as $k => $v ) {
            if( $v == '*' ) continue;
            if( substr($k,0,7) == 'colsel_' ) {
                $k = substr($k,7);  // should be a colalias
                $raCond[] = "(".$this->oComp->kfrel->GetRealColName($k)."='".addslashes($v)."')";
            }
        }

        $cond = implode( " AND ", $raCond );

        // remember the particular set of filters for this View, so we can detect when to reset the currRow (when the View changes e.g. due to a new Search).
        $this->oComp->viewCondMD5 = substr(md5($cond),0,10);
        if( $this->oComp->viewCondMD5 != $this->oComp->oForm->CtrlGlobal('viewCondMD5') ) {
            $this->oComp->kfuiCurrRow->Clear();
            $this->oComp->raWindowParms['iOffset'] = 0;
        }


        /* Add an extra "OR" condition to include any new row that wouldn't necessarily fit the current filter.  This lets the user
         * see such rows once, before the filter hides them.
         *

         * Actually, do this:
         * Get the current view
         * If the current row is in the view, use the scroll parms to determine whether it should be visible or whether the list is scrolled up or down off of the current row
         * If the current row is NOT in the view, add it at the top of the list.  This handles the case of a new row (where not in view), and operations performed on that new row afterward.

         *          * N.B. ***** Do not include this in the MD5!  Else the list will assume that you've changed filters and will reset the list position.
         */

/*
 *        if( $this->bFocusKeyRowInList && $this->kfuiCurrRow->GetKey() ) {
 *            $cond = "(".$this->kfrel->GetRealColName('_key')."='".$this->kfuiCurrRow->GetKey()."')".($cond ? " OR ($cond)" : "");
 *        }
 */

        /* If the view condition has changed, then we are looking at a whole new view.
         * Reset the currRow, and start it at offset zero.
         */
/*
 *        if( !@$this->kfui->klugeRowInit && $this->viewCondMD5 != @$this->uiParms['viewCondMD5'] ) {
 *            $this->kfuiCurrRow->Clear();
 *            $this->currParms['iListOffset'] = $this->uiParms['off'] = 0;
 *        }   $this->currParms['iListSelPos'] = $this->uiParms['lpos'] = 0;
 */

        //echo $cond;
        //$this->oComp->kfrel->kfdb->SetDebug(2);


        /* Compute the View and the Window
         */
//TODO: move this to a ListGetWindowRows() method, for datasource independence. Also have to support other uses of $oView below
        $oView = new KFRelationView( $this->oComp->kfrel, $cond, $this->oComp->raViewParms );
        $raWindowRows = $oView->GetDataWindow( $this->oComp->raWindowParms['iOffset'], $this->oComp->raWindowParms['iLimit'] );

        return( array( $oView, $raWindowRows ) );
    }

    private function setCurrRow()
    {
        /* Displaying the current row:
         *
         * 1) If bNewRow we are entering a new row. There is no current row, no selection.
         *
         * 2) If there is no current row and the window is not empty, make the first displayed row the current row.
         *
         * 3) If the current row is not in the window, there are two possible reasons:
         *      a) the current row was just inserted and doesn't match the window filter, or such a row was recently inserted and re-edited
         *         or re-selected (i.e. it doesn't match the filter but it isn't a brand new row either)
         *      b) the current row is in the view, but the window has been scrolled.
         *
         *    In case (a) the current row should be inserted into the window list and selected because the user expects to see it, even
         *    though it doesn't match the filter.  In case (b) the current row should obviously not be visible.
         *    Detect case (a) by searching the view. If the current row is not in the view, insert it at the top of the window and delete the bottom
         *    row from the window.
         */
        // (1) if bNewRow, there is no selection
        if( $this->oComp->bNewRow ) {
            $this->oComp->kfuiCurrRow->SetKey(0);  // this also clears the form's oDS->kfr
        }
        // (2) if there is no current row, make the first row the current row
        else if( !$this->oComp->kfuiCurrRow->GetKey() && @$this->raWindowRows[0] && $this->raWindowRows[0]->Key() ) {
            $this->oComp->kfuiCurrRow->SetKey( $this->raWindowRows[0]->Key() );
            // handled in Init()  $this->iOffsetCurrRow = $this->iWindowOffset;       // curr row is now at top of window
        }
        // (3) insert the current row if it is not in the window but likely of interest to the user
        else if( $this->oComp->kfuiCurrRow->GetKey() && @$_REQUEST['kluge_Added A Row Just Now Or We Really Like this One Row']) {
            $bFound = false;
            foreach( $this->raWindowRows as $kfr ) {
                if( $kfr->Key() == $this->oComp->kfuiCurrRow->GetKey() ) {
                    $bFound = true;
                    break;
                }
            }
            if( !$bFound ) {
                // scan the view
                // insert the current row into the window, maybe half way, and pop off the last one
                // fix problems with iOffsetCurrRow, offsets for scrolling down (should see the popped row)
            }
        }
    }
}

class _kfuiComponentCurrRow {
/**************************
    This encapsulates the logic that manages the current row of a uiComp.
    Although this is created when the uiComp is created, it cannot be used until the uiComp->kfrel is set.
    Note that if $this->key is not set, this will behave as if the row is new (same as setting key=0).

        SetKey( 0 ) or Clear()                  : a new blank row
        SetKey( key of an existing row )        : loads the existing row
        SetKey( key of a non-existing row )     : creates a blank row, forcing the given key


    The currRow helps us manage novel rows that need to be loaded from time to time (e.g. the first time a
    list is shown, the curr row defaults to the first visible row; also sometimes a new row is created with a
    forced key value and we'd like to have a faked kfr to draw the form). The kfr in the oForm is always mirrored
    to be the same as kfuiCurrRow->kfr so either can be used to access values.
*/
    var $uiComp;    // owner

    var $kfr = NULL;

    function __construct( $uiComp ) {
        $this->uiComp = $uiComp;
    }

    function Clear()        { $this->SetKey( 0 ); }
    function GetKey()       { return( $this->kfr ? $this->kfr->Key() : 0 ); }
    function GetValue( $f ) { return( $this->kfr ? $this->kfr->Value($f) : "" ); }
    function GetValueEnt($f){ return( $this->kfr ? $this->kfr->ValueEnt($f) : "" ); }
    function GetKFR()       { return( $this->kfr ); }

    function SetKey( $k ) {
        $this->kfr = NULL;

        if( $k ) {
            // First try to load a record.
            $this->kfr = $this->uiComp->kfrel->GetRecordFromDBKey( $k );
        }
        if( !$this->kfr ) {
            // key is 0 or the record was not found (there is a case where a component tries to use a new non-existent key).
            // Create a new blank record.
            $this->kfr = $this->uiComp->kfrel->CreateRecord( @$this->uiComp->kfrelParms['fkDefaults'] );
            if( $this->kfr && $k ) {
                // This is the special case where a non-existent forced key is set (the lookup failed above).
                $this->kfr->SetKey( $k );
            }
        }
        $this->uiComp->oForm->SetKFR($this->kfr);
    }
}

?>
