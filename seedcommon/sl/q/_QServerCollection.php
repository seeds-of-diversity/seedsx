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
        if( strpos( $cmd, "-" ) !== false && !$this->oQ->sess->TestPerm( 'SLCollection', 'R' ) ) {
            $rQ['sErr'] = "Command requires Seed Collection read permission";

// also check per-collection read permission

            goto done;
        }

        // cmds containing -- require write access (at a minimum - cmd might have other more stringent requirements too)
        if( strpos( $cmd, "--" ) !== false && !$this->oQ->sess->TestPerm( 'SLCollection', 'W' ) ) {
            $rQ['sErr'] = "Command requires Seed Collection write permission";

// also check per-collection write permission

            goto done;
        }

        switch( strtolower($cmd) ) {
            case 'collection-getinv':
                list($rQ['bOk'],$rQ['raOut'],$rQ['sErr']) = $this->getInv($parms);
                break;
            case 'collection--add':
                list($kInvNew,$rQ['sErr']) = $this->collectionAdd($parms);
                if( $kInvNew ) { $rQ['bOk'] = true; $rQ['raOut'][0] = $kInvNew; }
                break;
            default:
                break;
        }

        done:
        return( $rQ );
    }

    function getInv( $parms )
    /************************
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

    function collectionAdd( $parms )
    /*******************************
        Add a new Inventory and Accession record.

        This does not add an Inv to an existing Acc because there is no access control on Acc (only on Coll which is independent
        of Acc). Therefore there is no way to prevent ajax users from specifying any random Acc they want.
        Instead, users add Inv to their Coll, a new Acc is created for every new Inv, and owners of Inv can then split them.

Maybe it would be convenient for $parms to support an array of new Inv, all for the same new Acc, but for now it's just as easy to
use Inv splitting.

        Parms:
            kColl           : collection (required)
            kInv            : normally zero, but optional forced value of inventory key (must not already exist)
            {Acc-data}      : see below
            g               : grams
            loc             : location
            parent_inv      : parent inventory number (with collection number or prefix if not from this collection e.g. CC-IIII)
            dCreation       : date inventoried or split
            bDeAcc          : why you'd create a deaccessioned inventory sample we can't guess, but you can if you want to

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
        $sErr = "";

        $kColl = intval(@$parms['kColl']);
        $kInv = intval(@$parms['kInv']);

        if( !($dCreation = @$parms['dCreation']) ) {
            $dCreation = date('Y-m-d');
        }

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


        /* Create the Accession
         */
        list($kfrA,$sErr) = $this->addAcc( $parms );
        if( !$kfrA ) goto done;


        /* If kInv is being forced, ensure it doesn't already exist (remember kInv is inv_number not _key)
         */
        if( $kInv && ($kfrI = $this->oSLDB->GetKFRCond( "I", "fk_sl_collection='$kColl' AND inv_number='$kInv'" )) ) {
            $sErr = "Inventory $kInv already exists";
            goto done;
        }

        if( !($kfrI = $this->oSLDB->GetKFRel( "I" )->CreateRecord()) ) goto done;

        $kfrI->SetValue( 'fk_sl_collection', $kColl );
        $kfrI->SetValue( 'fk_sl_accession', $kfrA->Key() );
        $kfrI->SetValue( 'inv_number', $kfrC->Value('inv_counter') );
        $this->oQ->kfdb->Execute( "UPDATE sl_collection SET inv_counter=inv_counter+1 WHERE _key='$kColl'" );
        $kfrI->SetValue( 'g_weight', @$parms['g'] );
        $kfrI->SetValue( 'location', @$parms['location'] );
// this doesn't mean anything because it can be from another collection
// $kfrI->SetValue( 'parent_kInv', $parms['parent_kInv'] );
        $kfrI->SetValue( 'bDeAcc', intval(@$parms['bDeAcc']) );
        $kfr->SetValue( 'dCreation', $dCreation );
        $kfr->PutDBRow();
        $kInvNew = $kfr->Key();

        done:
        return( array($kInvNew,$sErr) );
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
// TODO: oname should contain a copy of pcv.name
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

        $kfrA->SetValue( 'parent_acc', @$parms['kAccParent'] );
        $kfrA->SetValue( 'parent_src', @$parms['sParentSrc'] );
        $kfrA->SetValue( 'batch_id',   @$parms['sBatch'] );
        $kfrA->SetValue( 'spec',       @$parms['sSpec'] );
        $kfrA->SetValue( 'notes',      @$parms['sNotes'] );

        $kfrA->SetValue( 'x_d_harvest',  @$parms['dHarvest'] );
        $kfrA->SetValue( 'x_d_received', @$parms['dReceived'] );
        $kfrA->SetValue( 'x_member',     @$parms['sSource'] );

        $kfrA->PutDBRow();

       done:
        return( array($kfrA, $sErr) );
    }

}

?>
