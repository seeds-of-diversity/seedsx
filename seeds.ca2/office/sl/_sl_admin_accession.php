<?php

include_once( SEEDLIB."sl/QServerSLCollectionReports.php" );

class SLAdminReports
{
    public $oW;
    private $oApp;
    private $oQCollReports;

    function __construct( Console01_Worker $oW, SEEDAppConsole $oApp )
    {
        $this->oW = $oW;
        $this->oApp = $oApp;

        $this->oQCollReports = new QServerSLCollectionReports( $this->oApp, ['config_bUTF8'=>false] );
    }

//see QServerRosetta::cultivarOverview
    function ReportsContentDraw()
    {
        $s = "<div>"
            ."<a href='".$this->oApp->PathToSelf()."?report=cultivar-summary'>Summary of All Varieties in the Seed Library Collection</a><br/>"
            ."<a href='".$this->oApp->PathToSelf()."?report=cultivar-summary-including-csci-cultivars'>"
                ."Summary of All Varieties in the Seed Library Collection + Seed Finder</a><br/>"
            ."<a href='".Site_path_self()."?report=adopted-summary'>Summary of Adopted Varieties</a><br/>"
            ."<a href='".$this->oApp->PathToSelf()."?report=germ-summary'>Germination Tests</a></br>"
            ."</div>";

        switch( SEEDInput_Str('report') ) {
            case 'cultivar-summary':                            $s .= $this->cultivarSummary( false );  break;
            case 'cultivar-summary-including-csci-cultivars':   $s .= $this->cultivarSummary( true );   break;
            case 'adopted-summary':                             $s .= $this->adoptedSummary();          break;
            case 'germ-summary':                                $s .= $this->germSummary();             break;
            default:
        }

        return( $s );
    }

    private function cultivarSummary( $bUnionCSCI )
    {
        $s = "";

        $qCmd = $bUnionCSCI ? 'collreport-cultivarsummaryUnionCSCI' : 'collreport-cultivarsummary';
        $sTitle = "Summary of All Varieties in the Seed Library Collection" . ($bUnionCSCI ? " + Seed Finder" : "");

        $rQ = $this->oQCollReports->Cmd( $qCmd, ['kCollection'=>1] );

        if( $rQ['bOk'] ) {
            $s1 = "";
            $c = 0;
            foreach( $rQ['raOut'] as $ra ) {
                $sTDClass = "class='td$c'";
                $s1 .= "<tr><td $sTDClass>{$ra['species']}</td><td $sTDClass>{$ra['cultivar']}</td>"
                      ."<td $sTDClass>{$ra['csci_count']}</td><td $sTDClass>{$ra['adoption']}</td>"
                      ."<td $sTDClass>{$ra['newest_lot_year']}</td><td $sTDClass>{$ra['total_grams']}</td>"
                      ."<td $sTDClass>".str_replace( " | ", "<br/>", $ra['notes'] )."</td></tr>";
                $c = $c ? 0 : 1;
            }
            $s .= $this->drawReport( $sTitle, $qCmd,
                          "<th>&nbsp;</th><th>&nbsp;</th><th>Companies</th><th>Adoption</th><th>Newest</th><th>Total grams</th><th>&nbsp;</th>",
                          $s1 );
        } else {
            $this->oW->oC->ErrMsg( $rQ['sErr'] );
        }
        return( $s );
    }

    private function adoptedSummary()
    {
        $s = "";

        $rQ = $this->oQCollReports->Cmd( 'collreport-adoptedsummary', ['kCollection'=>1] );

        if( $rQ['bOk'] ) {
            $s1 = "";
            $c = 0;
            foreach( $rQ['raOut'] as $ra ) {
                $sTDClass = "class='td$c'";
                $s1 .= "<tr><td $sTDClass>{$ra['species']}</td><td $sTDClass>{$ra['cultivar']}</td><td $sTDClass>{$ra['adoption']}</td>"
                     ."<td $sTDClass>{$ra['newest_lot_year']}</td><td $sTDClass>{$ra['total_grams']}</td>"
                     ."<td $sTDClass>".str_replace( " | ", "<br/>", $ra['notes'] )."</td></tr>";
                $c = $c ? 0 : 1;
            }
            $s .= $this->drawReport( "Summary of Adopted Varieties", 'collreport-adoptedsummary',
                          "<th>&nbsp;</th><th>&nbsp;</th><th>Adoption</th><th>Newest</th><th>Total grams</th><th>&nbsp;</th>",
                          $s1 );
        } else {
            $this->oW->oC->ErrMsg( $rQ['sErr'] );
        }
        return( $s );
    }

    private function germSummary()
    {
        $s = "";

        $rQ = $this->oQCollReports->Cmd( 'collreport-germsummary', ['kCollection'=>1] );

        if( $rQ['bOk'] ) {
            $s1 = "";
            $c = 0;
            foreach( $rQ['raOut'] as $ra ) {
                $sTDClass = "class='td$c'";
                $s1 .= "<tr><td $sTDClass>{$ra['species']}</td><td $sTDClass>{$ra['cultivar']}</td><td $sTDClass>{$ra['lot']}</td>"
                      ."<td $sTDClass>{$ra['g_weight']}</td>"
                      ."<td $sTDClass>".str_replace( " | ", "<br/>", $ra['tests'] )."</td></tr>";
                $c = $c ? 0 : 1;
            }
            $s .= $this->drawReport( "Germination Tests", 'collreport-germsummary',
                                     "<th>&nbsp;</th><th>&nbsp;</th><th>Lot</th><th>Grams</th><th>Tests</th>",
                                     $s1 );
        } else {
            $this->oW->oC->ErrMsg( $rQ['sErr'] );
        }
        return( $s );
    }

    private function drawReport( $sTitle, $qCmd, $sTableHeaders, $sTableBody )
    {
        $s = "<div><h3 style='display:inline-block;margin-right:3em;'>$sTitle</h3>
                <a style='display:inline-block' href='".$this->oApp->UrlQ('index2.php')."?qcmd=$qCmd&kCollection=1&qfmt=xls' target='_blank'>
                  <img src='".W_ROOT."std/img/dr/xls.png' height='25'/>
                </a>
              </div>"

            ."<style>
                .collReportTable th   { padding-right:15px; }
                .collReportTable .td0 { padding-right:15px; vertical-align:top; background-color:#ddd;}
                .collReportTable .td1 { padding-right:15px; vertical-align:top; background-color:white;}
              </style>"

            ."<table class='collReportTable'><tr>$sTableHeaders</tr> $sTableBody </table>";

        return( $s );
    }

}
