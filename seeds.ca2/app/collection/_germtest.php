<?php

class SLCollectionGermination
{
    private $oSCA;

    private $kfrelG;

    private $oFormG;

    function __construct( SLCollectionAdmin $oSCA )
    {
        $this->oSCA = $oSCA;

        // do the form updates before other parts of the UI are drawn, to prevent weird behaviour, e.g. list having old values
        $this->kfrelG = $this->oSCA->oSLDBMaster->GetKfrel( 'G' );

        $this->oFormG = new KeyFrameUIForm( $this->kfrelG, 'G', array('DSParms'=>array('fn_DSPreStore'=>array($this,'DSPreStore_Germ'))) );
    }

    function Init()
    {
        $this->oFormG->Update();
    }

    function DSPreStore_Germ( KeyFrameDataStore $dummy )    // don't need the oDS because we know it's $this->oFormG
    {
        if( !$this->oSCA->kCurrCollection )  return( false );    // can't do this unless there's a current collection
        if( !$this->oSCA->oColl->CanWriteCollection( $this->oSCA->kCurrCollection ) )  return( false ); // congratulations hacker

        // nGerm is the % recorded in the db but you can enter the fake value nGerm_count to calculate it
        if( !$this->oFormG->Value('nGerm') &&
            ($sown = $this->oFormG->Value('nSown')) &&
            ($count = $this->oFormG->Value('nGerm_count')) )
        {
            $this->oFormG->SetValue( 'nGerm', $count * 100 / $sown );
        }

        return( true );
    }

    function DrawGermination( KFRecord $kfrI )
    /*****************************************
        Show all the germination records for this Inv, allow them to edited, allow new.
     */
    {
        $s = "";

        $kEditG = substr(($p=SEEDSafeGPC_GetStrPlain('pCmd')),0,8) == 'editgerm' ? intval(substr($p,8)) : 0;

        if( !$kEditG ) {
            // put an empty form at the top if not editing an existing record
            $kfrG = $this->kfrelG->CreateRecord();
            $kfrG->SetValue( 'fk_sl_inventory', $kfrI->Key() );
            $s .= "<div class='well' style='margin:20px'>"
                 .$this->germForm( $kfrG )
                 ."</div>";
        }

        $s .= "<div class='well' style='margin:20px'>"
             ."<h4>Germination History</h4>";
        if( ($kfrG = $this->oSCA->oSLDBMaster->GetKFRC( 'G', "fk_sl_inventory='".$kfrI->Key()."'", array('sSortCol'=>'dStart DESC,_key','bSortDown'=>true) )) ) {
            $n = $kfrG->CursorNumRows();
            while( $kfrG->CursorFetch() ) {
                $colour = $n % 2 ? "#e5e5e5": "#fefefe;";

                if( $kfrG->Key() == $kEditG ) {
                    $sDraw = $this->germForm( $kfrG, $n );
                    $colour = "#e0f0f0";
                } else {
                    $sDraw = $this->germDraw( $kfrG, $n );
                }

                $s .= "<div style='margin-left:2em;padding:5px;background-color:$colour;'>"
                     .$sDraw
                     ."</div>"
                     ."<hr style='width:90%;border-color:#555;margin:5px auto;'/>";
                --$n;
            }
        }
        $s .= "</div>";

        return( $s );
    }

    private function germDraw( $kfrG, $iTest )
    {
        $button = $this->button( 'Edit', $kfrG->Key() );

        $sErr = "";
        if( $kfrG->Value('dStart') == '0000-00-00' ) {
            $sErr .= "No start date<br/>";
        } else if( !($tstart = strtotime( $kfrG->Value('dStart'))) ) {
            $sErr .= "No start date<br/>";
        } else if( $kfrG->Value('dEnd') != '0000-00-00' && (strtotime($kfrG->Value('dEnd')) - $tstart < 0) ) {
            $sErr .= "End date is prior to start date<br/>";
        }
        if( intval($kfrG->Value('nSown')) <= 0 ) {
            $sErr .= "Number of seeds is missing<br/>";
        }
        if( ($g = intval($kfrG->Value('nGerm'))) < 0 || $g > 100 ) {
            $sErr .= "Germination rate must be between zero and 100<br/>";
        }
        if( $sErr ) $sErr = "<div class='alert alert-danger' style='float:right;margin-left:20px'>$sErr</div>";


        $s = "<div style='float:right;margin-left:20px'>$button</div>"
            .$sErr
            ."<h5>Test $iTest</h5>";

        $s .= $kfrG->Expand(
                "[[nGerm]] % from [[nSown]] seeds tested [[dStart]]".(($d = $kfrG->Value('dEnd')) != '0000-00-00' ? " to $d" : "")
               .$kfrG->ExpandIfNotEmpty( 'notes', "<div style='margin-left:10px'>[[notes]]</div>" )
              );

        return( $s );
    }

    private function germForm( $kfrG, $iTest = 0 )
    {
        $s = $iTest ? "<h4>Editing Test $iTest</h4>" : "";

        $this->oFormG->SetKFR( $kfrG );
        $s .= "<form method='post' action='".$this->oSCA->oApp->PathToSelf()."'>"
             ."<table border='0' width='100%'>"
             .$this->oFormG->ExpandForm(
                "||| Dates ||  [[date:dStart | | placeholder='Start']] [[nbsp:10]] [[date:dEnd | | placeholder='End']]"
               ."||| # seeds tested || [[nSown]] || &nbsp;"
               // nGerm is the % stored in the db, but you can enter the fake value nGerm_count to calculate it
               ."||| # seeds sprouted || [[nGerm_count]] || or germ rate [[nGerm]] % &nbsp;"
               ."||| {colspan='3'} Notes<br/>[[textarea:notes|width:100% height:50px]]"
              )
             ."</table>"
             .$this->oFormG->Hidden( 'fk_sl_inventory' )
             .$this->oFormG->HiddenKey()
             ."<input type='submit' value='Save'/>"
             ."</form>";

        return( $s );
    }

    private function button( $label, $kGerm )
    {
        return( "<form method='post'>"
               ."<input type='submit' value='$label'/>"
               ."<input type='hidden' name='pCmd' value='editgerm$kGerm'/>"
               ."</form>" );
    }

}

?>