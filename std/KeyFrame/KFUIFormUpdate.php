<?

/* KFUIFormUpdate.php
 *
 * Copyright (c) 2010 Seeds of Diversity Canada
 *
 * Update a database using a set of KFUIFormParms (documented in KFUIFormParms.php)
 */


// TODO: SetFKDefaults
// TODO: Checkbox processing should happen at a SEEDForm level update, not sure how viable it is to make this a derivation of a SEEDFormUpdate class


include_once( "KFUIFormParms.php" );

class KeyFrameUIFormUpdate
/*************************
    Usage:
        new KeyFrameUIFormUpdate()
        set the FormParms using SetRAFormParms(), or GetHTTPParms()
        update database row(s) using UpdateAll(), or UpdateRow()
 */
{
    var $kfrel = NULL;
    var $raFormParms = NULL;    // 2D array of form parms specifying one or more rows of data and control codes. See SEEDFormParms
    var $kfuiFormDef = NULL;
    var $raParms = array();

    var $raProcess = array();
    var $raCheckboxes = array();
    var $raPresetOnInsert = array();

    function KeyFrameUIFormUpdate( &$kfrel, $kfuiFormDef = NULL, $raParms = array() )
    {
        $this->kfrel = &$kfrel;
        $this->kfuiFormDef = $kfuiFormDef;
        $this->raParms = $raParms;

        // Record the field names that are checkboxes, so their values can be reset properly
        if( $kfuiFormDef ) {
            foreach( $kfuiFormDef as $fld => $ra ) {
                if( @$ra['type'] == 'checkbox' )  $this->raCheckboxes[] = $fld;
                if( @$ra['presetOnInsert'] == true )  $this->raPresetOnInsert[] = $fld;
            }
        }
    }

    function SetRAFormParms( $raFormParms )
    /**************************************
        Given a 2D deserialized form parm array
     */
    {
        $this->raFormParms = $raFormParms;
    }

    function GetHTTPParms( $oFormParms, $ra1D = NULL, $bGPC = true )
    /***************************************************************
        Given a SEEDFormParms or derivative, and a 1-D parms array, deserialize to get a 2D form parm array
     */
    {
        if( !$ra1D )  $ra1D = $_REQUEST;
        $this->raFormParms = $oFormParms->Deserialize( $ra1D, $bGPC );
    }

    function GetDefaultHTTPParms()
    /*****************************
       Get parms from _REQUEST for a form with the default cid
     */
    {
        $oFormParms = new KeyFrameUIFormParms();
        $this->raFormParms = $oFormParms->Deserialize( $_REQUEST, true );
    }

    function UpdateAll()
    /*******************
        Update all rows specified by the $raFormParms
     */
    {
        if( !$this->raFormParms )  $this->GetDefaultHTTPParms();

        foreach( $this->raFormParms['rows'] as $r => $raRow ) {
            $this->update( $r, $raRow );
        }
    }

    function UpdateRow()
    /*******************
       Update one row (the first in the raFormParms) and return the kfr
     */
    {
        if( !$this->raFormParms )  $this->GetDefaultHTTPParms();

        return( isset($this->raFormParms['rows'][0]) ? $this->update( 0, $this->raFormParms['rows'][0] ) : NULL );
    }

    function UpdateToKFR( $r = 0, $raRow = NULL )
    /********************************************
        For a single row, fetch/create the kfr, update from parms, and return the kfr.
     */
    {
        if( !$this->raFormParms )  $this->GetDefaultHTTPParms();

        if( $raRow === NULL ) {
            if( !isset($this->raFormParms['rows'][0]) )  return( NULL );

            $r = 0;
            $raRow = $this->raFormParms['rows'][0];
        }

        // Get or create the row
        if( $raRow['k'] ) {
            $kfr = $this->kfrel->GetRecordFromDBKey( $raRow['k'] );
        } else {
            /* This is a new row.  Determine whether any of the values have been filled in, so we don't insert blank rows.
             */
            $bBlank = true;
            foreach( $raRow['values'] as $fld => $v ) {
                if( in_array( $fld, $this->raPresetOnInsert) )  continue;    // blank rows have this value preset, so skip it
                if( !empty($v) ) $bBlank = false;
            }
            if( !$bBlank ) {
                $kfr = $this->kfrel->CreateRecord();
            } else {
                // skip this row because it contains no data
                $kfr = NULL;
            }
        }
        if( $kfr ) {
            if( !empty($raRow['op']) && in_array($raRow['op'], array('d','h','r')) ) {
                // delete, hide, or reset the row's _status
                $kfr->StatusSet( $raRow['op']=='d' ? KFRECORD_STATUS_DELETED :
                                 $raRow['op']=='h' ? KFRECORD_STATUS_HIDDEN  :
                                                     KFRECORD_STATUS_NORMAL );
            }
            foreach( $raRow['values'] as $fld => $v ) {
                $kfr->SetValue( $fld, $v );
            }
            // Checkboxes do not send HTTP parms if they are unchecked. If a checkbox is defined in this row,
            // and there is no parm, assume that the checkbox was unchecked to zero.
            foreach( $this->raCheckboxes as $fld ) {
                if( !isset($raRow['values'][$fld]) ) {
                    $kfr->SetValue( $fld, 0 );
                }
            }
        }
        return( $kfr );
    }

    function update( $r, $raRow )
    {
        if( ($kfr = $this->UpdateToKFR( $r, $raRow )) ) {
            if( $this->Pre_PutDBRow( $kfr ) ) {
                $kfr->PutDBRow();

                // Update the key in the parms structure in case the caller wants to know it for an inserted row
                $this->raFormParms[$r]['k'] = $kfr->Key();
            }
        }
        return( $kfr );
    }

    function Pre_PutDBRow( &$kfr )
    /*****************************
        Allows client to adjust the contents of the kfr before committing it.
        Return false to abort the database write.

        There are two ways to do this:
            - override this method
            - use the base method and supply $raParms['callbackPrePutDBRow']
     */
    {
        if( isset($this->raParms['callbackPrePutDBRow']) ) { return( call_user_func($this->raParms['callbackPrePutDBRow'], $kfr ) ); }
        return( true );
    }
}


function KFUIFormUpdate( $kfrel, &$raFormParms, $kfuiFormDef = NULL )   // DEPRECATE
/********************************************************************
    Process Insert, Update, Delete, Hide, and UnDelete/Unhide operations on the given kfrel

    $raFormParms is a 2-D form of KFUIFormParms specifying one or more rows of data that may or may not be the same as the content of the database.
    This function makes the database match the given parms (and control codes such as delete-row).

    $kfuiFormDef is an optional KFUIFormDef that defines the form elements that the parms came from. This is only needed if the form has checkboxes.

    Keys for new rows (that are zero in the raFormParms) are set in that structure after the update
 */
{
    $raProcess = array();
    $raCheckboxes = array();
    $raPresetOnInsert = array();

    // Record the field names that are checkboxes, so their values can be reset properly
    if( $kfuiFormDef ) {
        foreach( $kfuiFormDef as $fld => $ra ) {
            if( @$ra['type'] == 'checkbox' )  $raCheckboxes[] = $fld;
            if( @$ra['presetOnInsert'] == true )  $raPresetOnInsert[] = $fld;

        }
    }

    foreach( $raFormParms['rows'] as $r => $raRow ) {
        if( $raRow['k'] ) {
            $kfr = $kfrel->GetRecordFromDBKey( $raRow['k'] );
        } else {
            /* This is a new row.  Determine whether any of the values have been filled in, so we don't insert blank rows.
             */
            $bBlank = true;
            foreach( $raRow['values'] as $fld => $v ) {
                if( in_array( $fld, $raPresetOnInsert) )  continue;    // blank rows have this value preset, so skip it
                if( !empty($v) ) $bBlank = false;
            }
            if( $bBlank )  continue;    // skip this row because it contains no data

            $kfr = $kfrel->CreateRecord();
        }
        if( $kfr ) {
            if( !empty($raRow['op']) && in_array($raRow['op'], array('d','h','r')) ) {
                // delete, hide, or reset the row's _status
                $kfr->StatusSet( $raRow['op']=='d' ? KFRECORD_STATUS_DELETED :
                                 $raRow['op']=='h' ? KFRECORD_STATUS_HIDDEN  :
                                                     KFRECORD_STATUS_NORMAL );
            }
            foreach( $raRow['values'] as $fld => $v ) {
                $kfr->SetValue( $fld, $v );
            }
            // Checkboxes do not send HTTP parms if they are unchecked. If a checkbox is defined in this row,
            // and there is no parm, assume that the checkbox was unchecked to zero.
            foreach( $raCheckboxes as $fld ) {
                if( !isset($raRow['values'][$fld]) ) {
                    $kfr->SetValue( $fld, 0 );
                }
            }
            //$kfr->PutDBRow();

            // Update the key in the parms structure in case the caller wants to know it for an inserted row
            $raFormParms[$r]['k'] = $kfr->Key();

            $kfr = NULL;
        }
    }
}

?>
