<?php

class SLCollectionInventory
{
    private $oSCA;

    private $kfrelI;

    private $oFormSplit;
    private $oFormDist;

    function __construct( SLCollectionAdmin $oSCA )
    {
        $this->oSCA = $oSCA;

        // do the form updates before other parts of the UI are drawn, to prevent weird behaviour, e.g. list having old values
        $this->kfrelI = $this->oSCA->oSLDBMaster->GetKfrelIxA_P();

        $this->oFormSplit = new KeyFrameUIForm( $this->kfrelI, 'J', array() );
        $this->oFormDist  = new KeyFrameUIForm( $this->kfrelI, 'K', array() );
    }

    function Init()
    {
        if( !$this->oSCA->kCurrCollection ||                                             // can't do this unless there's a current collection
            !$this->oSCA->oColl->CanWriteCollection( $this->oSCA->kCurrCollection ) )    // congratulations on getting this far, hacker
        {
            return( false );
        }


        /* Split:
         *     $this->oFormSplit contains data for the inv that is being split
         *         location is the only parm that the form allows the user to change
         *         g_weight_new is an additional parm = the weight of a new inv, to be subtracted from the old one
         *         location_new is an additional parm = the location of the new inv
         */
        $oF = $this->oFormSplit;
        if( $oF->Load() ) {
            $new_g   = floatval( $oF->Value( 'g_weight_new' ) );
            $new_loc = $oF->Value( 'location_new' );

            // If a weight is given for a new inv ($new_g)
            //     Create a new inv with dup info from the old inv, but with the new weight and location
            //     Deduct the weight from the old sample - IF THERE IS ENOUGH.
            //     Also potentially alter the location of the old sample.
            // Else
            //     Just potentially alter the location of the old sample.
            if( $new_g > 0.0 ) {
                $old_g = floatval( $oF->Value('g_weight') );

                if( $old_g < $new_g ) {
                    $this->oSCA->ErrMsg( "The sample isn't big enough to split out $new_g grams" );
                } else {
                    if( ($kInvNext = $this->getNextInvNumberAndInc()) ) {
// factor this with DSPreStore_Inv
// and security check

// probably want to append a note to the accession when you split a sample
                        $kfrI2 = $this->kfrelI->CreateRecord();
                        $kfrI2->SetValue( 'g_weight', $new_g );
                        $kfrI2->SetValue( 'location', $new_loc );
                        $kfrI2->SetValue( 'fk_sl_accession', $oF->Value('fk_sl_accession') );
                        $kfrI2->SetValue( 'fk_sl_collection', $oF->Value('fk_sl_collection') );
                        $kfrI2->SetValue( 'inv_number', $kInvNext );
                        $kfrI2->SetValue( 'dCreation', date('Y-m-d') );
                        $kfrI2->PutDBRow();

                        $oF->SetValue( 'g_weight', $old_g - $new_g );
                    }
                }
            }
            // this stores any changes to the location of the old sample, as well as any change to g_weight above
            $oF->Store();
        }

        /* Distribute:
         *     $this->oFormDist contains data for the inv that is being distributed
         *     g_dist is an additional parm = the amount to subtract from g_weight
         */
        $oF = $this->oFormDist;
        if( $oF->Load() ) {
            $dist_g = floatval( $oF->Value('g_dist') );

            if( $dist_g > 0.0 ) {
                // deduct the weight of the new sample from the weight of the old sample
                $old_g = floatval( $oF->Value('g_weight') );
                if( $old_g < $dist_g ) {
                    $this->oSCA->ErrMsg( "The sample isn't big enough to distribute $dist_g grams" );
                } else {
                    $oF->SetValue( 'g_weight', $old_g - $dist_g );
// probably want to append a note to the accession when you distribute - to whom, why
// if the sample has been split to different accessions, do we still aggregate these notes to the accession?
//   Sure, why not, because you gave ownership of a split sample to somebody, so now you can look at what they're doing with it.
                    $oF->Store();
                }
            }
        }
    }

    function DrawSplit( KFRecord $kfrI )
    {
        $s = "<form method='post' action='".$this->oSCA->oApp->PathToSelf()."'>"
            ."<div style='margin:0 20px'>";

        $kfrC = $this->oSCA->oSLDBMaster->GetKFR( "C", $this->oSCA->kCurrCollection );
        $sInvPrefix = $kfrC ? $kfrC->Value('inv_prefix') : "X";
        $nNextInv = $kfrC ? $kfrC->Value('inv_counter') : 0;
        $sNextInv = $kfrC ? ($kfrC->Value('inv_prefix')."-".$nNextInv) : "unknown";

        // draw form for given Inv
        $this->oFormSplit->SetKFR( $kfrI );
        $s .= $this->oSCA->drawInvForm( $this->oFormSplit, 0, false, true );  // doesn't need a nNextInv, show g_weight readonly

        // instead of making a new form for a new inv, add a few values to the same form-row. The reason is we have to do math between the forms,
        // and it's hard to get both records at the same time.
        $s .= "<fieldset>"
             ."<legend>Add New Inventory <span style='font-size:10pt'>( next number is $sNextInv )</span></legend>"
             ."<table border='0'>"
                 .$this->oFormSplit->ExpandForm(
                      "||| Weight (g)    || [[g_weight_new]]"
                     ."||| Location      || [[location_new]]"
                  )
             ."</table>"
             ."</fieldset>"
             ."<p>&nbsp;</p>";

        $s .= "<input type='submit' value='Split / Move'/>"
             ."<input type='hidden' name='pMode' value='status'/>"
             ."</div>"
             ."</form>";

        return( $s );
    }

    function DrawDistribute( KFRecord $kfrI )
    {
        $s = "<form method='post' action='".$this->oSCA->oApp->PathToSelf()."'>"
            ."<div style='margin:0 20px'>";

        $kfrC = $this->oSCA->oSLDBMaster->GetKFR( "C", $this->oSCA->kCurrCollection );
        $sInvPrefix = $kfrC ? $kfrC->Value('inv_prefix') : "X";
        $nNextInv = $kfrC ? $kfrC->Value('inv_counter') : 0;
        $sNextInv = $kfrC ? ($kfrC->Value('inv_prefix')."-".$nNextInv) : "unknown";

        // draw form for given Inv
        $this->oFormDist->SetKFR( $kfrI );
        $s .= $this->oSCA->drawInvForm( $this->oFormDist, 0, false, true, false );  // doesn't need a nNextInv, show g_weight readonly

        // instead of making a new form for a new inv, add a few values to the same form-row. The reason is we have to do math between the forms,
        // and it's hard to get both records at the same time.
        $s .= "<fieldset>"
             ."<legend>Amount to remove</span></legend>"
             ."<table border='0'>"
                 .$this->oFormDist->ExpandForm(
                      "||| Weight (g)    || [[g_dist]]"

                  )
             ."</table>"
             ."</fieldset>"
             ."<p>&nbsp;</p>";

        $s .= "<input type='submit' value='Update'/>"
             ."<input type='hidden' name='pMode' value='status'/>"
             ."</div>"
             ."</form>";

        return( $s );
    }

    private function getNextInvNumberAndInc()
    {
        $kInv = 0;

        if( ($kfrC = $this->oSCA->oSLDBMaster->GetKFR( "C", $this->oSCA->kCurrCollection )) &&
            ($kInv = $kfrC->Value('inv_counter')) )
        {
            $this->oSCA->kfdb->Execute( "UPDATE sl_collection SET inv_counter=inv_counter+1 WHERE _key='{$this->oSCA->kCurrCollection}'" );
        }
        return( $kInv );
    }
}

?>
