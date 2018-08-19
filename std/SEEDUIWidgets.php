<?php

/* SEEDUIWidgets
 *
 * Copyright (c) 2015 Seeds of Diversity Canada
 *
 * Classes that encapsulate functionality of UI widgets
 */


class SEEDUIWidgets_Pills
/************************
    Draw a list of Bootstrap pills that you can select

        $raPills         = array( name => array( label ), ... )
        $httpKeyName     = name of the http parm that contains pill name when you click on it
        $raParms['oSVA'] = a SEEDSessionVarAccessor where the current pill name is stored
 */
{
    private $raPills;
    private $httpKeyName;
    private $currPill = "";

    function __construct( $raPills, $httpKeyName, $raParms )
    {
        $this->raPills = $raPills;
        $this->httpKeyName = $httpKeyName;

        $oSVA = @$raParms['oSVA'];

        $p = SEEDSafeGPC_GetStrPlain( $this->httpKeyName );
        if( !$p && $oSVA ) {
            $p = $oSVA->VarGet( "CurrPill" );
        }
        // make this check after oSVA because the raPills can change depending on application modes (so a missing previous CurrPill should default to first pill)
        if( !$p || !isset($this->raPills[$p]) ) {
            // get the first pill in the array
            reset($this->raPills); $p = key($this->raPills);
        }
        if( $oSVA )  $oSVA->VarSet( "CurrPill", $p );

        $this->currPill = $p;
    }

    function GetCurrPill()  { return( $this->currPill ); }

    function DrawPillsVertical()
    {
        $s = "<style>"
            .".nav-pills > li.notactive > a { background-color:#eee; }"
            ."</style>"
            ."<ul class='nav nav-pills nav-stacked'>";
        foreach( $this->raPills as $k => $ra ) {
            $active = ($k == $this->currPill) ? "active" : "notactive";
            $s .= "<li class='$active'><a href='{$_SERVER['PHP_SELF']}?{$this->httpKeyName}=$k'>{$ra[0]}</a></li>";
        }
        $s .= "</ul>";

        return( $s );
    }

}








?>