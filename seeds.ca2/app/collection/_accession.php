<?php

class SLCollectionAccession
{
    private $oSCA;

    private $kfrelA;
    private $kfrelI;
    private $oFormA;
    private $oFormI;

    public $kluge_CreatedNewAcc = 0;

    function __construct( SLCollectionAdmin $oSCA )
    {
        $this->oSCA = $oSCA;

//        $this->oSLDB_AP   = new SLDB_A_P( $oSCA->kfdb, $oSCA->sess->GetUID() );
//        $this->oSLDB_IxAP = new SLDB_IxA_P( $oSCA->kfdb, $oSCA->sess->GetUID() );

        // do the form updates before other parts of the UI are drawn, to prevent weird behaviour, e.g. list having old values
        $this->kfrelA = $this->oSCA->oSLDBMaster->GetKfrelA_P();
        $this->kfrelI = $this->oSCA->oSLDBMaster->GetKfrelIxA_P();

        $this->oFormA = new KeyFrameUIForm( $this->kfrelA, 'A', array('DSParms'=>array('fn_DSPreStore'=>array($this,'DSPreStore_Acc'))) );
        $this->oFormI = new KeyFrameUIForm( $this->kfrelI, 'I', array('DSParms'=>array('fn_DSPreStore'=>array($this,'DSPreStore_Inv'))) );
    }

    function Init()
    {
        // update the forms. Do this after the constructor because this is dependent on the kCurrCollection, which is set after construction
        $this->oFormA->Update();    // create a new Accession before creating Inventory items that refer to it
        $this->oFormI->Update();

        if( $this->kluge_CreatedNewAcc && $this->oFormA->GetKey() ) {
            $this->kluge_CreatedNewAcc = $this->oFormA->GetKey();    // store the new key here so the UI can know there's a new Acc record
        }
    }

    function DrawNewAccession()
    {
        $s = $this->accessionForm( 0 );

        return( $s );
    }

    function DrawNewAccession2()
    {
        $s = $this->accessionForm2( 0 );

        return( $s );
    }

    function DrawEditAccession()
    {
        $kAcc = 0;
        if( $this->oSCA->kInvCurr && ($kfr = $this->oSCA->oSLDBMaster->GetKFR( "I", $this->oSCA->kInvCurr )) ) {
            $kAcc = $kfr->Value('fk_sl_accession');
        }
        $s = $this->accessionForm( $kAcc );

        return( $s );
    }

    private function accessionForm( $kAcc )
    {
        $s = "";

// $this already has a Console01, maybe it can be a Console01KFUI?
$raParms = array();

        if( $kAcc && ($kfr = $this->kfrelA->GetRecordFromDBKey( $kAcc )) ) {
            $this->oFormA->SetKFR($kfr);
        }

        $sLeft = "<table border='0' cellpadding='0' width='90%' style='position:relative' class='SFUAC_Anchor'>"
             .$this->oFormA->HiddenKey()
             .$this->oFormA->ExpandForm(
                "" // nobody needs the accession key so don't confuse us by showing it  ($kAcc ? "||| Accession # || [[Value:_key]]" : "")
               ."||| Cultivar       || <span id='cultivarText' style='font-size:9pt'>[[Value:P_psp]] : [[Value:P_name]] ([[Value:P__key]])</span> "
                                     ."[[dummy_pcv | size:10 class:SFU_AutoComplete | placeholder='Search']] "
                                     ."[[hidden:fk_sl_pcv]]"
                                     ."<select class='SFUAC_Select'></select>"
               ."||| Original Name  || [[oname | width:100%]]"
               ."||| {colspan='2'} <hr/>"

               ."||| Grower/Source  || [[x_member | width:100%]]"
               ."||| Batch          || [[batch_id | width:100%]]"
               ."||| Date Harvested || [[x_d_harvest | width:100%]]"
               ."||| Notes || "
               ."||| {colspan='2'} [[Textarea:notes| width:100%]]"
               ."|||                || <div style='margin-top:10px'><input type='submit' value='Save'/></div>"
               ."||| {colspan='2'} <hr/>"

               ."||| Parent Desc    || [[parent_src | width:100%]]"
               ."||| {colspan='2'} <B>External Origin:</B>"
               ."||| Date Received  || [[x_d_received]]"
               ."||| {colspan='2'} <B>Internal Origin:</B>"
               ."||| Parent Lot #   || [[parent_acc]]"
            )
           ."</table>";

        $sRight = "";

        if( $this->oFormA->GetKey() ) {
            $raKFR = $this->kfrelI->GetRecordSet( "A._key='".$this->oFormA->GetKey()."'" );
            $raKFR[] = $this->kfrelI->CreateRecord();
        } else {
            // New Accession:  make two empty inventory subforms
            $raKFR = array();
            $raKFR[] = $this->kfrelI->CreateRecord();
            $raKFR[] = $this->kfrelI->CreateRecord();
        }

        $kfrC = $this->oSCA->oSLDBMaster->GetKFR( "C", $this->oSCA->kCurrCollection );
        $nNextInv = $kfrC ? $kfrC->Value('inv_counter') : 0;

        $iRow = 0;
        foreach( $raKFR as $kfr ) {

            $this->oFormI->SetKFR( $kfr );
            $this->oFormI->SetRowNum( $iRow++ );

            $this->oFormI->SetValue( 'fk_sl_accession', $this->oFormA->GetKey() );

            $sRight .= $this->oSCA->drawInvForm( $this->oFormI, $nNextInv );
        }

        $s .= "<form method='post' action='{$_SERVER['PHP_SELF']}'>"
             ."<table class='table' style='width:100%'><tr>"
             ."<td style='width:60%'>$sLeft</td>"
             ."<td>$sRight</td>"
             ."</tr></table>"
             ."<input type='hidden' name='pMode' value='editacc'/>"    // newacc goes to editacc
             ."</form>";

        return( $s );
    }

    private function accessionForm2( $kAcc )
    {
        $s = "";

        $s .= $this->oSCA->oTmpl->ExpandTmpl( 'mycollForms', array() );


// $this already has a Console01, maybe it can be a Console01KFUI?
$raParms = array();

        if( $kAcc && ($kfr = $this->kfrelA->GetRecordFromDBKey( $kAcc )) ) {
            $this->oFormA->SetKFR($kfr);
        }

        $sLeft = "<table border='0' cellpadding='0' width='90%' style='position:relative' class='SFUAC_Anchor'>"
             .$this->oFormA->HiddenKey()
             .$this->oFormA->ExpandForm(
                "" // nobody needs the accession key so don't confuse us by showing it  ($kAcc ? "||| Accession # || [[Value:_key]]" : "")
               ."||| Cultivar       || <span id='cultivarText' style='font-size:9pt'>[[Value:P_psp]] : [[Value:P_name]] ([[Value:P__key]])</span> "
                                     ."[[dummy_pcv | size:10 class:SFU_AutoComplete2 | placeholder='Search']] "
                                     ."[[hidden:fk_sl_pcv]]"
                                     ."<select class='SFUAC_Select'></select>"
               ."||| Original Name  || [[oname | width:100%]]"
               ."||| {colspan='2'} <hr/>"

               ."||| Grower/Source  || [[x_member | width:100%]]"
               ."||| Batch          || [[batch_id | width:100%]]"
               ."||| Date Harvested || [[x_d_harvest | width:100%]]"
               ."||| Notes || "
               ."||| {colspan='2'} [[Textarea:notes| width:100%]]"
               ."|||                || <div style='margin-top:10px'><input type='submit' value='Save'/></div>"
               ."||| {colspan='2'} <hr/>"

               ."||| Parent Desc    || [[parent_src | width:100%]]"
               ."||| {colspan='2'} <B>External Origin:</B>"
               ."||| Date Received  || [[x_d_received]]"
               ."||| {colspan='2'} <B>Internal Origin:</B>"
               ."||| Parent Lot #   || [[parent_acc]]"
            )
           ."</table>";

        $sRight = "";

        if( $this->oFormA->GetKey() ) {
            $raKFR = $this->kfrelI->GetRecordSet( "A._key='".$this->oFormA->GetKey()."'" );
            $raKFR[] = $this->kfrelI->CreateRecord();
        } else {
            // New Accession:  make two empty inventory subforms
            $raKFR = array();
            $raKFR[] = $this->kfrelI->CreateRecord();
            $raKFR[] = $this->kfrelI->CreateRecord();
        }

        $kfrC = $this->oSCA->oSLDBMaster->GetKFR( "C", $this->oSCA->kCurrCollection );
        $nNextInv = $kfrC ? $kfrC->Value('inv_counter') : 0;

        $iRow = 0;
        foreach( $raKFR as $kfr ) {

            $this->oFormI->SetKFR( $kfr );
            $this->oFormI->SetRowNum( $iRow++ );

            $this->oFormI->SetValue( 'fk_sl_accession', $this->oFormA->GetKey() );

            $sRight .= $this->oSCA->drawInvForm( $this->oFormI, $nNextInv );
        }

        $s .= "<form method='post' action='{$_SERVER['PHP_SELF']}'>"
             ."<table class='table' style='width:100%'><tr>"
             ."<td style='width:60%'>$sLeft</td>"
             ."<td>$sRight</td>"
             ."</tr></table>"
             ."<input type='hidden' name='pMode' value='editacc'/>"    // newacc goes to editacc
             ."</form>";

        return( $s );
    }

    function DSPreStore_Acc( KeyFrameDataStore $oDS )
    /************************************************
        This is one way to find out whether a oForm->Update is creating a new record or updating an old record.
        If creating a new record, we want to tell the UI to highlight it.
     */
    {
        if( !$this->oSCA->kCurrCollection )  return( false );    // can't do this unless there's a current collection
        if( !$this->oSCA->oColl->CanWriteCollection( $this->oSCA->kCurrCollection ) )  return( false ); // congratulations hacker

        $this->kluge_CreatedNewAcc = !$this->oFormA->GetKey();
        return( true );
    }

    function DSPreStore_Inv( KeyFrameDataStore $dummy )    // don't need the oDS because we know it's $this->oFormI
    /**************************************************
        When a new inventory item is created for an existing accession, the fk_sl_accession is set via Hidden().
        But when a new inventory item is created simultaneously with a NEW accession, we create the accession then
        set the fk_sl_accession here.

        $this->oFormA is guaranteed to have been updated with the related Accession
     */
    {
        $ok = true;

        $oI = $this->oFormI;

        if( !$this->oSCA->kCurrCollection )  return( false );    // can't do this unless there's a current collection
        if( !$this->oSCA->oColl->CanWriteCollection( $this->oSCA->kCurrCollection ) )  return( false ); // congratulations hacker

        if( !$oI->GetKey() ) {
            /* New inventory item:
             *   - disallow if weight is zero
             *   - ensure that fk_sl_accession is set
             *   - fill in the current date if it is not set
             *   - set the inv_number
             */

            if( $oI->Value( 'g_weight' ) == 0.0 ) return( false );  // this is an empty row (of course this is valid if the inv item is not new)

            if( ($kAcc = $this->oFormA->GetKey()) ) {
                $oI->SetValue( 'fk_sl_accession', $kAcc );
            } else {
                $ok = false;
                $this->oSCA->oC->ErrMsg( "Cannot add new lot: accession id is zero" );
            }

            if( !$oI->Value( 'dCreation' ) )  $oI->SetValue( 'dCreation', date('Y-m-d') );

            $oI->SetValue( 'fk_sl_collection', $this->oSCA->kCurrCollection );
            $kfrC = $this->oSCA->oSLDBMaster->GetKFR( "C", $this->oSCA->kCurrCollection );
            $oI->SetValue( 'inv_number', $kfrC->Value('inv_counter') );
            $this->oSCA->kfdb->Execute( "UPDATE sl_collection SET inv_counter=inv_counter+1 WHERE _key='{$this->oSCA->kCurrCollection}'" );
        }
        return( $ok );
    }
}

?>
