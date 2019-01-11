<?php

/* _QServerCollection
 *
 * Copyright 2016-2019 Seeds of Diversity Canada
 *
 * Serve queries about sl_collection, sl_accession, sl_inventory
 */

include_once( SEEDLIB."sl/sldb.php" );

class QServerCollection
{
    private $oQ;
    private $oSLDB;

    function __construct( Q $oQ, SEEDAppSessionAccount $oApp, $raParms = array() )
    {
        $this->oQ = $oQ;
        $this->oSLDB = new SLDBCollection( $oApp, array() );
    }

    function Cmd( $cmd, $parms )
    {
        $rQ = $this->oQ->GetEmptyRQ();

        // cmds containing -- require write access (at a minimum - cmd might have other more stringent requirements too)
        // cmds containing - require write access (at a minimum - cmd might have other more stringent requirements too)
        if( strpos( $cmd, "--" ) !== false && !$this->oQ->sess->TestPerm( 'SLCollection', 'W' ) ) {
            $rQ['sErr'] = "Command requires Seed Collection write permission";

// also check per-collection write permission

            goto done;
        } else if( strpos( $cmd, "-" ) !== false && !$this->oQ->sess->TestPerm( 'SLCollection', 'R' ) ) {
            $rQ['sErr'] = "Command requires Seed Collection read permission";

// also check per-collection read permission

            goto done;
        }


        switch( strtolower($cmd) ) {
            case 'collection-getlot':
                list($rQ['bOk'],$rQ['raOut'],$rQ['sErr']) = $this->getLot($parms);
                break;
            case 'collection--addlot':
                list($rQ['bOk'],$rQ['raOut'],$rQ['sErr']) = $this->addLot($parms);
                break;
            case 'collection--reservelot':
                list($rQ['bOk'],$rQ['raOut'],$rQ['sErr']) = $this->reserveLot($parms);
                break;
            default:
                break;
        }

        done:
        return( $rQ );
    }

    private function getLot( $parms )
    /********************************
        Return a IxAxCxPxS record for the given inventory item.

        1) kInv          = inventory _key
        2) kColl + nInv  = collection _key and inv_number
     */
    {
        $bOk = false;
        $raOut = array();
        $sErr = "";

        $kInv  = intval(@$parms['kInv']);
        $kColl = intval(@$parms['kColl']);
        $nInv  = intval(@$parms['nInv']);

        if( $kInv ) {
            $cond = "_key='$kInv'";
        } else if( $kColl && $nInv ) {
            $cond = "(I.fk_sl_collection='$kColl' AND I.inv_number='$nInv')";
        } else {
            $sErr = "incomplete parameters";
            goto done;
        }

        if( ($kfr = $this->oSLDB->GetKFRCond( "IxAxPxS", $cond )) ) {
            // add more as you need them
            $raOut['I__key'] = $kfr->Key();
            $raOut['I_inv_number'] = $kfr->Value('inv_number');
            $raOut['P__key'] = $kfr->Value('P__key');
            $raOut['P_name'] = $this->oQ->QCharset( $kfr->Value('P_name') );
            $raOut['S_name_en'] = $this->oQ->QCharset( $kfr->Value('S_name_en') );

            $bOk = true;
        }

        done:
        return( array($bOk,$raOut,$sErr) );
    }

    private function addLot( $parms )
    /********************************
        Add a new Accession and some number of Inventory records (at least one).
        Since accession processing is sometimes done in a few steps, the minimal information to retrieve nLot1, {nLot2, ... } is:
            kColl, kPCV, g1, {g2, ...}
        Others e.g. locations should be added later.

        This does not add an Inv to an existing Acc because there is no access control on Acc (only on Coll which is independent
        of Acc). Therefore there is no way to prevent ajax users from specifying any random Acc they want.
        Instead, users add Inv to their Coll and a new Acc is created.
        It is also possible to place a total amount of seeds in one Inv and then split it (which preserves the Acc for every new Inv).

        Parms:
            kColl              : collection (required)
            nLot1, {nLot2 ...} : normally not provided, but optional forced value of Lot number (must not already exist)
            {Acc-data}         : see below
            g1, {g2, ... }     : grams
            loc1, {loc2 ...}   : location
            parent_inv         : parent inventory number (with collection number or prefix if not from this collection e.g. CC-IIII)
            dCreation          : date inventoried or split
            bDeAcc             : why you'd create a deaccessioned inventory sample we can't guess, but you can if you want to

        Acc-data must include one of the following:
            kPCV
            (kSp,ocv)
            (osp,ocv) with the requirement that osp must be a coherent sl_species name

        and other optional fields:
            dHarvest
            etc
     */
    {
        $ok = false;
        $raRet = array();
        $sErr = "";

        $kColl = intval(@$parms['kColl']);

        /* Check existence and write access to Collection
         */
        if( !($kfrC = $this->oSLDB->GetKFR( "C", $kColl )) ) {
            $sErr = "Collection $kColl not found";  // or it was zero
            goto done;
        }
// TODO: test write access on collection
$bCanWrite = true;
        if( !$bCanWrite ) {
            $sErr = "Collection $kColl does not allow write access";
            goto done;
        }


        if( !($dCreation = @$parms['dCreation']) ) {
            $dCreation = date('Y-m-d');
        }


        /* Create the Accession
         */
        list($kfrA,$sErr) = $this->addAcc( $parms );
        if( !$kfrA ) goto done;


//kluge to make mycollection work for now
        foreach( ['g1','g2'] as $v ) {
            if( !($g = @$parms[$v]) )  continue;

            if( !($kfrI = $this->oSLDB->GetKFRel( "I" )->CreateRecord()) ) goto done;

            $kfrI->SetValue( 'fk_sl_collection', $kColl );
            $kfrI->SetValue( 'fk_sl_accession', $kfrA->Key() );

            if( $v=='g1' ) { $nLot = @$parms['nLot1']; $loc = @$parms['loc1']; }
            if( $v=='g2' ) { $nLot = @$parms['nLot2']; $loc = @$parms['loc2']; }
            if( $nLot ) {
                /* If nLot is being forced, ensure it doesn't already exist
                 */
                if( ($kfrTest = $this->oSLDB->GetKFRCond( "I", "fk_sl_collection='$kColl' AND inv_number='$nLot'" )) ) {
                    $sErr = "Lot $nLot already exists";
                    goto done;
                }
                $kfrI->SetValue( 'inv_number', $nLot );
            } else {
                $kfrC = $this->oSLDB->GetKFR( "C", $kColl );    // have to reload the second time through the loop
                $kfrI->SetValue( 'inv_number', $kfrC->Value('inv_counter') );
                $this->oQ->kfdb->Execute( "UPDATE sl_collection SET inv_counter=inv_counter+1 WHERE _key='$kColl'" );
            }
            $kfrI->SetValue( 'g_weight', $g );
            $kfrI->SetValue( 'location', $loc ); //@$parms['location'] );
            $kfrI->SetValue( 'bDeAcc', intval(@$parms['bDeAcc']) );
            $kfrI->SetValue( 'dCreation', $dCreation );
            $kfrI->PutDBRow();
            if( $v=='g1' ) { $raRet['nLot1'] = $kfrI->Value('inv_number'); $raRet['kInv1'] = $kfrI->Key(); }
            if( $v=='g2' ) { $raRet['nLot2'] = $kfrI->Value('inv_number'); $raRet['kInv2'] = $kfrI->Key(); }
        }

        $ok = true;

        done:
        return( array($ok,$raRet,$sErr) );
    }

    private function addAcc( $parms )
    /********************************
        Add a new Accession record.

        parms:
            cultivar identifier is required, one of:
                (kPCV)
                (kSp,ocv)
                (osp,ocv) where osp must be a coherent sl_species name
            dHarvest
            etc
     */
    {
        $sErr = "";

        $kPCV = intval(@$parms['kPCV']);
        $kSp = intval(@$parms['kPCV']);
        $osp = @$parms['osp'];
        $ocv = @$parms['ocv'];

        if( !($kfrA = $this->oSLDB->GetKFRel( "A" )->CreateRecord()) ) goto done;

        if( $kPCV ) {
            $kfrA->SetValue( 'fk_sl_pcv', $kPCV );
            $kfrA->SetValue( 'oname', $ocv );
        } else if( $kSp && $ocv ) {
// TODO: kSp?
            $kfrA->SetValue( 'oname', $ocv );
        } else if( $osp && $ocv ) {
// TODO: osp?
            $kfrA->SetValue( 'oname', $ocv );
        } else {
            $sErr = "Cultivar must be specified";
            $kfrA = null;
            goto done;
        }

        $kfrA->SetValue( 'parent_acc', @$parms['parent_inv'] );   // rename db field to parent_inv
        $kfrA->SetValue( 'parent_src', @$parms['sParentSrc'] );
        $kfrA->SetValue( 'batch_id',   @$parms['sBatch'] );
        $kfrA->SetValue( 'spec',       @$parms['sSpec'] );
        $kfrA->SetValue( 'notes',      @$parms['sNotes'] );

        $kfrA->SetValue( 'x_d_harvest',  @$parms['dHarvest'] );
        $kfrA->SetValue( 'x_d_received', @$parms['dReceived'] );
        $kfrA->SetValue( 'x_member',     @$parms['supplier'] );

        $kfrA->PutDBRow();

       done:
        return( array($kfrA, $sErr) );
    }


    private function reserveLot( $parms )
    /************************************
        Increment the inv_counter to reserve some lot numbers and return those numbers

        Parms:
            kColl : collection (required)
            n     :  number of lot numbers to reserve
     */
    {
        $ok = false;
        $raOut = array();
        $sErr = "";

        if( !($kColl = intval(@$parms['kColl'])) || !($n = intval(@$parms['n'])) )  goto done;

        if( !($kfrC = $this->oSLDB->GetKFR( "C", $kColl )) ) {
            $sErr = "Collection $kColl not found";  // or it was zero
            goto done;
        }
// TODO: test write access on collection
$bCanWrite = true;
        if( !$bCanWrite ) {
            $sErr = "Collection $kColl does not allow write access";
            goto done;
        }

        $nLot = $kfrC->Value('inv_counter');
        $this->oQ->kfdb->Execute( "UPDATE sl_collection SET inv_counter=inv_counter+$n WHERE _key='$kColl'" );

        for( $i = 1; $i <= $n; ++$i ) {
            $raOut["nLot$i"] = $nLot;
            ++$nLot;
        }

        $ok = true;

        done:
        return(array($ok,$raOut,$sErr));
    }
}

?>
