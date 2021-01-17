<?php

/* KFDataStore
 *
 * Copyright 2010-2017 Seeds of Diversity Canada
 *
 * Implement a SEEDDataStore using a KeyFrameRecord
 */

include_once( "KFRelation.php" );
include_once( SEEDCORE."SEEDDataStore.php" );


class KeyFrameDataStore extends SEEDDataStore
/**********************
    Implement a SEEDDataStore using a KeyFrameRecord
 */
{
    private $kfrel;
    private $kfr = NULL;

    function __construct( KeyFrameRelation $kfrel, $raParms = array() )
    {
        $this->kfrel = $kfrel;
        parent::__construct( $raParms );
    }

    function GetValuesRA() { return( $this->kfr ? $this->kfr->ValuesRA() : array() ); }

    // Sometimes forms use auxiliary code that need a kfr, so it isn't enough to just get/set values from this interface.
    function GetKFR()                 { return( $this->kfr ); }
    function SetKFR( KFRecord $kfr )  { $this->kfr = $kfr; }

    /* Override the Data-side methods.
     * The Application-side methods are normally not overridden.
     */

    function DSValue( $k )        { return( $this->kfr ? $this->kfr->Value($k) : "" ); }
    function DSSetValue( $k, $v ) { if( $this->kfr )  $this->kfr->SetValue( $k, $v ); }
    function DSOp( $op )
    {
        if( $this->kfr && in_array($op, array('d','h','r')) ) {
            // delete, hide, or reset the row's _status
            $this->kfr->StatusSet(  $op=='d' ? KFRECORD_STATUS_DELETED :
                                   ($op=='h' ? KFRECORD_STATUS_HIDDEN  :
                                               KFRECORD_STATUS_NORMAL) );
        }
    }

    function DSLoad( $k, $r )
    /************************
        Ignore the row number, use the key.  If k==0 create a new kfr.  Else load up the record from the db.
     */
    {
        $this->kfr = ( $k ? $this->kfrel->GetRecordFromDBKey( $k ) : $this->kfrel->CreateRecord() );
        return( $this->kfr != null );
    }

    // use SEEDDataStore's logic for DSPreStore
    // function DSPreStore()  { return( true ); }  // really intended for the app to override if desired

    function DSStore()
    {
        return( $this->kfr && $this->kfr->PutDBRow() ? $this->kfr : null );
    }

    function DSKey()
    {
        return( $this->kfr ? $this->kfr->Key() : null );
    }

    function DSSetKey( $k )
    /**********************
        Force the kfr to use the given key. This will be committed on PutDBRow.
     */
    {
        return( $this->kfr ? $this->kfr->KeyForce( $k ) : false );
    }

    function DSGetDataObj() { return( $this->kfr ); }
}

?>
