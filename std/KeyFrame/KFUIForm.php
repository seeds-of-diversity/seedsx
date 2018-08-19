<?php

/* KFUIForm.php
 *
 * Copyright (c) 2008-2010 Seeds of Diversity Canada
 *
 * Functions that make KeyFrame form development easier
 */

include_once( "KFDataStore.php" );
include_once( STDINC."SEEDForm.php" );


class KeyFrameUIForm extends SEEDForm
/************************************
    Implement a SEEDForm that uses a kfrel as a data source.
    This data source can handle multiple simultaneous rows, using keys.
 */
{
    // var $oDS;  defined in SEEDForm - this derivation must create a KeyFrameDataStore before calling SEEDForm

    // kfr has dual use:  in Form Draw methods this is the source of values. Necessary to use SetKFR before drawing the form.
    //                    in Update method it holds the data for each row during the process.
    //                    It's hard to get around this because the DSValue method is used in both processes.

    var $kfrel;  // though this is stored in the oDS, it's more proper for clients to reference it here

    function __construct( KeyFrameRelation $kfrel, $cid = NULL, $raParms = array() )
    {
        $this->kfrel = $kfrel;
        $this->oDS = new KeyFrameDataStore( $kfrel, isset($raParms['DSParms']) ? $raParms['DSParms'] : array() );
        // This prevents rows with no data and a zero key from being inserted.  Probably, they are unfilled rows in a table form. Override by explicitly setting to false.
        if( !isset($raParms['bSkipBlankRows']) )  $raParms['bSkipBlankRows'] = true;
        parent::__construct( $cid, $raParms );
    }

    function SetKFR( KFRecord $kfr )      { $this->oDS->SetKFR( $kfr ); }

    function GetKey()             { return( ($kfr = $this->oDS->GetDataObj()) ? $kfr->Key() : 0 ); }

    // Additional Form Elements that are KeyFrame specific (these should use the same format as SEEDForm Elements)

    function HiddenKey()
    /*******************
        Write the current row's key into a hidden form parameter.
     */
    {
        return( $this->HiddenKeyParm( $this->GetKey() ) );    // use SEEDForm::HiddenKeyParm to encode the key as an sfParmKey
    }
}

?>
