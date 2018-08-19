<?php

include_once( SEEDAPP."SEEDApp.php" );

class MbrOrderReport
{
    public $pRow = 0;
    public $pAction = "";
    public $fltStatus = "";
    public $fltYear = 0;
    private $yCurrent;

    private $oW;

    function __construct( SEEDApp_Worker $oW )
    {
        $this->oW = $oW;
        $this->yCurrent = intval(date("Y"));

        $this->pRow = SEEDInput_Int( 'row' );
        $this->pAction = SEEDInput_Str( 'action' );

        // Filters
        $this->fltStatus = $oW->sess->SmartGPC( 'fltStatus', array("", MBRORDER_STATUS_FILLED, MBRORDER_STATUS_CANCELLED) );
        if( !($this->fltYear = intval($oW->sess->SmartGPC( 'fltYear', array() ))) ) {
            $this->fltYear = $this->yCurrent;
        }
    }

    function DrawFormFilters()
    {
        $s = "";

        $raYearOpt = array();
        for( $y = $this->yCurrent; $y > 2010; --$y ) {
            $raYearOpt[strval($y)] = $y;
        }
        $raYearOpt["2010 and before"] = 2010;

        $s .= "<form action='${_SERVER['PHP_SELF']}'>"
             ."<p>Show: "
             .SEEDForm_Select2( 'fltStatus',
                        array( "Pending / Paid" => "",
                               "Filled"         => MBRORDER_STATUS_FILLED,
                               "Cancelled"      => MBRORDER_STATUS_CANCELLED ),
                        $this->fltStatus,
                        array( "selectAttrs" => "onChange='submit();'" ) )
             .SEEDStd_StrNBSP("",5)
             .SEEDForm_Select2( 'fltYear',
                        $raYearOpt,
                        $this->fltYear,
                        array( "selectAttrs" => "onChange='submit();'" ) )
             .(!$this->fltStatus ? "&nbsp;&nbsp;<-- the year selector is ignored for pending orders" : "")
             ."</p></form>";

        return( $s );
    }
}

?>