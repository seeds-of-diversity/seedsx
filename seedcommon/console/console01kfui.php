<?php

/* console01kfui
 *
 * Console framework for KFUIComponents
 *
 * Copyright (c) 2012-2014 Seeds of Diversity Canada
 */

include_once( "console01.php" );
include_once( STDINC."KeyFrame/KFUIComponent.php" );

class Console01KFUI extends Console01
{
    /*  N.B. This is currently designed for only one component in the frame. For multiple components,
     *       there would have to be multiple oComps and a way to call InitializeFrame after the final AddComponent.
     */
    protected $oFrame = NULL;
    public $oComp = NULL;
    public $oCompB = NULL;  // if a secondary relation is on the screen, external code can use this.

    function __construct( KeyFrameDB $kfdb, SEEDSessionAccount $sess, $raParms ) { parent::__construct( $kfdb, $sess, $raParms ); }

    function CompInit( KeyFrameRelation $kfrel, $raCompParms, $cid = 'A' )
    /*********************************************************************
        Initialize a KFUIComponent for use in Control/Content
     */
    {
        if( $this->oFrame == NULL ) {
            $this->oFrame = new KeyFrameUIFrame();
            $this->oComp = $this->oFrame->AddComponent( $cid, $kfrel, $raCompParms ) or die( "AddComponent failed in CompInit" );
            $this->oFrame->InitializeFrame();
        }
    }

    function CompInitB( KeyFrameRelation $kfrelA, $raCompParmsA, KeyFrameRelation $kfrelB, $raCompParmsB )
    /*****************************************************************************************************
        Initialize two KFUIComponents for use in Control/Content
        Most of the code in this file only supports the 'A' component, but external code can use the 'B' component
     */
    {
        if( $this->oFrame == NULL ) {
            $this->oFrame = new KeyFrameUIFrame();
            $this->oComp  = $this->oFrame->AddComponent( 'A', $kfrelA, $raCompParmsA );
            $this->oCompB = $this->oFrame->AddComponent( 'B', $kfrelB, $raCompParmsB );
            $this->oFrame->InitializeFrame();
        }
    }

    function SetFrameControlParm( $k, $v )
    /*************************************
        A control in its own client-drawn form, whose value should be propagated with the Comp data
     */
    {
        $this->oFrame->SetFrameControlParm( $k, $v );
    }


    function CompListForm_Vert( $raParms = array() )
    {
        return( $this->complistform( $raParms, true ) );
    }

    function CompListForm_Horz( $raParms = array() )
    {
        return( $this->complistform( $raParms, false ) );
    }

    private function complistform( $raParms, $bVert )
    /************************************************
       raParms:
           widthList                   : css value (e.g. NNpx, NN%) for the width of the list
           bAllowNew    (default true) : show New Row button and show the form if GetKey is 0
           bAllowDelete (default true) : show Delete Row button
     */
    {
        // If the list does not have an explicit current row, the default row might not be set yet.
        // This sets the oComp->oForm->key, so things get drawn properly if the row is the default (first) row.
        $this->oComp->ListInit();

        $widthList = isset($raParms['widthList']) ? $raParms['widthList'] : '50%';
        $bAllowNew    = SEEDStd_ArraySmartVal( $raParms, 'bAllowNew', array( true, false ) );
        $bAllowDelete = SEEDStd_ArraySmartVal( $raParms, 'bAllowDelete', array( true, false ) );

        $sTemplate =
              "<table border='0' cellpadding='0' cellspacing='0' width='100%'><tr>"
             ."<td valign='top' width='$widthList'>[[ListDraw]]</td>"
             .($bVert ? "</tr><tr>" : "")   // this is the only difference between the vert and horz formats
             ."<td valign='top'>".$this->complistform_drawForm( $bAllowNew, $bAllowDelete )."</td>"
             ."</tr></table>";

        return( $this->oComp->DrawFromTemplate( $sTemplate ) );
    }


    private function complistform_drawForm( $bAllowNew, $bAllowDelete )
    {
        // draw the form only if there's a current row or if allowed to draw a blank form
        if( !$bAllowNew && !$this->oComp->oForm->GetKey() )  return( "&nbsp;" );

        $s = "<div style='border: black solid medium; padding:10px;'>"
            .(($bAllowDelete && $this->oComp->oForm->GetKey()) ? "<div style='float:right;margin-left:10px;'>[[ButtonDeleteRow]]</div>" : "")
            .($bAllowNew ? "<div style='float:right;'>[[ButtonNewRow]]</div>" : "")
            //."<br style='clear:both'/>"  no need to clear the floats because the title fits well on the left
            ."[[FormDraw]]"
            ."</div>";

        return( $s );
    }


    function CompListTable( $parms = array() )
    /*****************************************
        Show a component's view window rows in a table, with an optional edit form on the side

        Why not a <TABLE>?  If the form is taller than the item on the left, the list looks funny.
     */
    {
        $oTable = new Console01Table_KFUI( $this->oComp );

        $s = $oTable->DrawTable( $parms );

        return( $s );
    }
}


class Console01Table_KFUI extends Console01Table_base
{
    private $oComp;

    function __construct( $oComp )
    {
        $this->oComp = $oComp;
        parent::__construct( $this->oComp->kfrel, $this->oComp->oForm );  // base class will use this oForm instead of making its own
    }

    function DrawTable( $parms )
    {
        $this->oComp->ListInit();
        $raWindowRows = $this->oComp->ListGetWindowRows();

        $parms['kCurr'] = $this->oComp->GetCurrKey();
        if( $this->oComp->bNewRow ) {
            $parms['bNew'] = true;
        }
        $s = $this->base_drawTable( $raWindowRows, $parms );

        return( $s );
    }


    protected function Table_Item( KFRecord $kfr )
    /********************************************
        Draw the table item for this kfr.

        a) Override this method
        b) Set fnTableItemDraw callback
        c) Base method dumps the values
     */
    {
        $s = "";
        if( isset($this->oComp->raCompConfig['fnTableItemDraw']) ) {
            $s .= call_user_func( $this->oComp->raCompConfig['fnTableItemDraw'], $this->oComp, $kfr );
        } else {
            foreach( $kfr->ValuesRA() as $k => $v ) {
                if( strlen($v) > 10 )  $v = substr($v,0,10)."...";
                $s .= "$k : $v<BR/>";
            }
        }
        return( $s );
    }

    protected function Table_Form( SEEDForm $oForm_ignored_same_as_within_oComp )
    {
        return( $this->oComp->FormDraw() );
    }

    public function Table_ButtonNew( $sLabel_ignored = "", $raParms_ignored = array() )
    {
        return( $this->oComp->ButtonNewRow() );
    }

    public function Table_ButtonDelete( $kRow = 0, $sLabel_ignored = "", $raParms_ignored = array() )
    {
        return( $this->oComp->ButtonDeleteRow( $kRow ) );
    }
}

?>
