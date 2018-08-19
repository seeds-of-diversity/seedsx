<?php

class SLSourcesCharts
{
    public $kfdb;

    function __construct( KeyFrameDB $kfdb )
    {
        $this->kfdb = $kfdb;
    }

    function SourcesOverview( $bUS, $kSpecies = 0, $sTitle = "" )
    /************************************************************
        Draw a pie chart showing the proportion of seeds from seed bank, commercial, non-profit sectors

        bUS : show US sources too
     */
    {
//$this->kfdb->SetDebug(2);
        $sCondSpecies = ($kSpecies ? " AND fk_sl_species='$kSpecies'" : "");
        $nGovt = $this->kfdb->Query1( "SELECT count(*) FROM sl_cv_sources WHERE fk_sl_sources in (1".($bUS ? ",2" : "").") $sCondSpecies" );
        $nComm = $this->kfdb->Query1( "SELECT count(*) FROM sl_cv_sources WHERE fk_sl_sources >= 3 $sCondSpecies" );
        if( $bUS && !$kSpecies )  $nComm += 18986;  // sum(gsi) from hvd_sourcelist

        if( !$kSpecies ) {
            $nNonProfit = 1500+ 3400 + 1220 + 125; // estimate sod + ssss + hss
        } else {
            $nNonProfit = $this->kfdb->Query1( "SELECT count(*) FROM sl_accession A,sl_pcv P WHERE A.fk_sl_pcv=P._key AND P.fk_sl_species='$kSpecies'" )
                        + $this->kfdb->Query1( "SELECT count(*) FROM sed_curr_seeds WHERE fk_sl_species='$kSpecies'" );
        }
        if( $bUS && !$kSpecies )  $nNonProfit += 18270; // estimate sse

        $raParms = array();

        $raParms['title'] = $sTitle ? $sTitle : "Seed Varieties in Canada".($bUS ? " and United States" : "");
        $raParms['cols']  = array( "Sector" => 'string', "Holdings" => 'number' );
        $raParms['rows']  = array(
                                array( "'Government Seed Banks'", $nGovt ),
                                array( "'Seed Companies'", $nComm ),
                                array( "'Non-profit Open Collections'", $nNonProfit ),
                                /*array( "'Private Collections'", 4000 )*/ );
        return( GoogleChart( $raParms ) );
    }

    function SourcesCommercial( $kSpecies, $bOrganic, $lang = "EN", $sTitle = "" )
    {
//$this->kfdb->SetDebug(2);
        $raParms = array();

        $sSql = "SELECT count(*) FROM sl_cv_sources C,sl_sources S WHERE C.fk_sl_sources=S._key AND S._key >= 3"
                                   .($kSpecies ? " AND C.fk_sl_species='$kSpecies'" : "")
                                   .($bOrganic ? " AND C.bOrganic" : "");
        $nBC = $this->kfdb->Query1( $sSql." AND S.prov='BC'" );
        $nPR = $this->kfdb->Query1( $sSql." AND S.prov in ('AB','SK','MB')" );
        $nON = $this->kfdb->Query1( $sSql." AND S.prov='ON'" );
        $nQC = $this->kfdb->Query1( $sSql." AND S.prov='QC'" );
        $nAT = $this->kfdb->Query1( $sSql." AND S.prov in ('NB','NS','PE','NF')" );

        $raRegions = SLSourcesCommon::RaRegions( $lang );

        $raParms['title'] = $sTitle;
        $raParms['type'] = 'column';
        $raParms['height'] = 250;
        $raParms['cols']  = array( "Province" => 'string', "Sources" => 'number' );  // both bilingual enough
        $raParms['rows']  = array(
                                array( "'".$raRegions['bc']."'", $nBC ),
                                array( "'".$raRegions['pr']."'", $nPR ),
                                array( "'".$raRegions['on']."'", $nON ),
                                array( "'Quebec'", $nQC ),
                                array( "'".$raRegions['at']."'", $nAT )
                            );

/*
Qu&eacute;bec is written literally to the chart.  Some options:


var options = {
   title: $('Qu&eacute;bec').html()
   ...
};

var options = {
   title: $('Qu&eacute;bec').text()
   ...
};

 */




        return( GoogleChart( $raParms ) );
    }


    function SLDescFrequency( $sTitle, $raVals )
    {
        $raParms['title'] = $sTitle;
        $raParms['type'] = 'column';
        $raParms['cols']  = array( "Measurement" => 'number', "Frequency" => 'number' );

        $raParms['rows']  = array();
        $maxVal = 0;
        foreach( $raVals as $ra ) {
            $raParms['rows'][] = array( $ra['val'], $ra['freq'] );
            if( $ra['val'] > $maxVal ) $maxVal = $ra['val'];
        }
        $raParms['maxH'] = $maxVal+5;

        return( GoogleChart( $raParms ) );
    }
}










function GoogleChart( $raParms )
{
    // optional
    $sChartType = (@$raParms['type'] == 'bar' ? "BarChart" : (@$raParms['type'] == 'column' ? "ColumnChart" : "PieChart"));
    $sChartDiv = SEEDStd_ArraySmartVal( $raParms, 'chart_div', array('chart_div'), false );
    $nWidth = SEEDStd_ArraySmartVal( $raParms, 'width', array(400), false );
    $nHeight = SEEDStd_ArraySmartVal( $raParms, 'height', array(300), false );

    // required
    $sTitle = @$raParms['title'];
    $raCols = $raParms['cols'];       // array( "Label1" => 'string', "Label2" => 'number' ),

    $raRows = $raParms['rows'];       // array(
                                      //     array( "'One'", 10 ),   -- note ' around string values
                                      //     array( "'Two'", 20 ),

    $sAddRows = "";
    $raAddRows = array();
    foreach( $raRows as $ra ) { $raAddRows[] = "[".implode(",", $ra)."]"; }
    $sAddRows = implode( ",", $raAddRows );


    $sJS = "<script type='text/javascript' src='https://www.google.com/jsapi'></script>"
          ."<script type='text/javascript'>"
          ."google.load('visualization', '1.0', {'packages':['corechart']});"
          ."google.setOnLoadCallback(drawChart);"
          ."function drawChart() {"
              ."var data = new google.visualization.DataTable();";
              foreach( $raCols as $label => $type ) {
                  $sJS .= "data.addColumn('$type', '$label');";
              }
    $sJS .=   "data.addRows(["
              .$sAddRows
              ."]);"
              ."var options = {"
                  ."'title':'$sTitle',"
                  ."'width':$nWidth,"
                  ."'height':$nHeight"
                  .(@$raParms['maxH'] ? ",'hAxis.minValue':0,'hAxis.maxValue':{$raParms['maxH']}" : "")
                  ."};"
              ."var chart = new google.visualization.$sChartType(document.getElementById('$sChartDiv'));"
              ."chart.draw(data, options);"
          ."}"
          ."</script>";

    $sJS .= "<div id='chart_div'></div>";

    return( $sJS );
}

?>
