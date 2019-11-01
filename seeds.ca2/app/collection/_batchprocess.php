<?php

class SLCollectionBatchProcess
{
    private $oSCA;

    function __construct( SLCollectionAdmin $oSCA )
    {
        $this->oSCA = $oSCA;
    }

    function Process()
    {
        if( !$this->oSCA->kCurrCollection )  return( "Please choose a collection" );
        if( !$this->oSCA->oColl->CanWriteCollection( $this->oSCA->kCurrCollection ) )  return( "" ); // congratulations hacker

        switch( ($cmd = SEEDSafeGPC_GetStrPlain( 'bpCmd' )) ) {
            case 'locate':    $s = $this->locate();    break;
            case 'addnote':   $s = $this->addnote();   break;
            default:          $s = $this->forms();     break;
        }
        return( $s );
    }

    private function forms()
    {
        $s = "<h4>These super-powerful forms let you make changes to lots of records at once.<br/>Be careful, there is no undo!</h4>";

        $s .= "<div class='well'>"
                 ."<h4>Move ranges of Lot numbers to a single location</h4>"
                 ."<p>e.g. You're putting several envelopes into a jar, or moving them from one jar to another.</p>"

                 ."<form method='post' action='".$this->oSCA->oApp->PathToSelf()."'>"
                 .SEEDForm_Hidden( 'bpCmd', 'locate' )
                 ."<p>".SEEDForm_Text( 'range', '', '', 50 )."&nbsp;&nbsp;Range of Lot numbers e.g. 1-3,8,10-13</p>"
                 ."<p>".SEEDForm_Text( 'location', '', '' )."&nbsp;&nbsp;New location</p>"
                 ."<input type='submit' value='Save Location'/>"
                 ."</form>"
                 ."</div>";

        $s .= "<div class='well'>"
                 ."<h4>Add a note to range(s) of Lot numbers</h4>"
                 ."<form method='post' action='".$this->oSCA->oApp->PathToSelf()."'>"
                 .SEEDForm_Hidden( 'bpCmd', 'addnote' )
                 ."<p>".SEEDForm_Text( 'range', '', '', 50 )."&nbsp;&nbsp;Range of Lot numbers e.g. 1-3,8,10-13</p>"
                 ."<p>".SEEDForm_Text( 'note', '', '', 50 )."&nbsp;&nbsp;Note</p>"
                 ."<p>".SEEDForm_Checkbox( 'bPrepend', 1, '' )."&nbsp;&nbsp;Add at the beginning of the notes</p>"
                 ."<input type='submit' value='Save Note'/>"
                 ."</form>"
                 ."</div>";

        return( $s );
    }

    private function locate()
    {
        list($raRange,$sRangeNormal) = SEEDCore_ParseRangeStr( SEEDSafeGPC_GetStrPlain('range') );
        $location = SEEDSafeGPC_GetStrPlain('location');
        if( !$sRangeNormal || !$location ) return( "Nothing to do" );

        foreach( $raRange as $n ) {
            if( !$this->oSCA->kfdb->Query1( "SELECT _key FROM sl_inventory WHERE inv_number='$n' AND fk_sl_collection='{$this->oSCA->kCurrCollection}'" ) ) {
                return( "Error: there is no Lot # $n in this collection" );
            }
        }


        $s = "<h4>Move ranges of Lot numbers to a single location.</h4>";
        if( SEEDSafeGPC_GetInt('bpConfirm') ) {
            foreach( $raRange as $n ) {
                $s .= "Moving $n to $location<br/>";
                $this->oSCA->kfdb->Execute( "UPDATE sl_inventory SET location='".addslashes($location)."' "
                                           ."WHERE fk_sl_collection='{$this->oSCA->kCurrCollection}' AND inv_number='$n'" );
            }
        } else {
            $s .= "So, you want to move Lot # <b>$sRangeNormal</b> to $location?"
                ."<form method='post' action='".$this->oSCA->oApp->PathToSelf()."'>"
                .SEEDForm_Hidden( 'bpCmd', $_REQUEST['bpCmd'] )
                .SEEDForm_Hidden( 'bpConfirm', 1 )
                .SEEDForm_Hidden( 'range', $sRangeNormal )
                .SEEDForm_Hidden( 'location', $location )
                ."<input style='margin-top:10px' type='submit' value='Confirm'/>"
                ."</form>";
        }
        return( $s );
    }

    private function addnote()
    {
        list($raRange,$sRangeNormal) = SEEDCore_ParseRangeStr( SEEDSafeGPC_GetStrPlain('range') );
        $note = trim(SEEDSafeGPC_GetStrPlain('note'));
        $bPrepend = SEEDSafeGPC_GetInt('bPrepend');
        if( !$sRangeNormal || !$note ) return( "Nothing to do" );

        foreach( $raRange as $n ) {
            if( !$this->oSCA->kfdb->Query1( "SELECT _key FROM sl_inventory WHERE inv_number='$n' AND fk_sl_collection='{$this->oSCA->kCurrCollection}'" ) ) {
                return( "Error: there is no Lot # $n in this collection" );
            }
        }

        $s = "<h4>Add a note to ranges of Lot numbers</h4>";
        if( SEEDSafeGPC_GetInt('bpConfirm') ) {
            foreach( $raRange as $n ) {
                $s .= "Adding note to $n<br/>";

                $sDb = "[".$this->oSCA->MakeInvId( $this->oSCA->kCurrCollection, $n )." ".date("Y-m-d")."] ".$note;
                $sConcat = $bPrepend ? "A.notes=CONCAT('".addslashes($sDb)."','\n',A.notes)"
                                     : "A.notes=CONCAT(A.notes,'\n','".addslashes($sDb)."')";

                $this->oSCA->kfdb->Execute( "UPDATE sl_inventory I,sl_accession A SET $sConcat "
                                           ."WHERE I.fk_sl_accession=A._key AND "
                                                 ."fk_sl_collection='{$this->oSCA->kCurrCollection}' AND inv_number='$n'" );
            }
        } else {
            $s .= "So, you want to add this note to Lot # <b>$sRangeNormal</b>:<div style='border:1px solid #aaa;width:400px'>".SEEDStd_HSC($note)."</div>"
                ."<form method='post' action='".$this->oSCA->oApp->PathToSelf()."'>"
                .SEEDForm_Hidden( 'bpCmd', $_REQUEST['bpCmd'] )
                .SEEDForm_Hidden( 'bpConfirm', 1 )
                .SEEDForm_Hidden( 'range', $sRangeNormal )
                .SEEDForm_Hidden( 'note', $note )
                .SEEDForm_Hidden( 'bPrepend', $bPrepend )
                ."<input style='margin-top:10px' type='submit' value='Confirm'/>"
                ."</form>";
        }
        return( $s );
    }
}

?>