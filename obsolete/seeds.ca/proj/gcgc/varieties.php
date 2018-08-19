<?
/* Show information about varieties
 */

define( "SITEROOT", "../../" );
include_once( SITEROOT."site.php" );
include_once( SEEDCOMMON."siteStart.php" );
include_once( SEEDCOMMON."sl/sl_desc_db.php" );
include_once( SEEDCOMMON."sl/sl_desc_report.php" );
include_once( PAGE1_TEMPLATE );
include_once( "_gcgc.php" );

list($kfdb) = SiteStart();



Page1( $page1parms );


function Page1Body() {
    global $kfdb;

    $cv = SEEDSafeGPC_GetStrPlain( "cv" );

    $oSLD = new SL_DescDB($kfdb);
    $oSLDReport = new SL_DescReport();

    $raNames = array();
    if( ($dbc = $kfdb->KFDB_CursorOpen( "SELECT name FROM gcgc_varieties ORDER BY 1" )) ) {
        while( $ra = $kfdb->KFDB_CursorFetch( $dbc ) ) {
            $raNames[] = $ra['name'];
        }
        $kfdb->KFDB_CursorClose( $dbc );
    }


    if( !empty($cv) ) {
        $ra = $kfdb->KFDB_QueryRA( "SELECT * FROM gcgc_varieties WHERE name='".addslashes($cv)."'" );
        if( !@$ra['name'] ) {
            $cv = "";   // fail to the default case below
        } else {
            echo "<TABLE border=0 width='100%'>"
                ."<TR>"
                ."<TD width='120' valign='top' style='font-size:8pt;padding-top:10em;padding-right:15px;border-right:thin solid black;'>"
                ."<P><A HREF='{$_SERVER['PHP_SELF']}'>List of Varieties</A></P><BR>";

            foreach( $raNames as $name ) {
                echo "<P><A HREF='{$_SERVER['PHP_SELF']}?cv=".urlencode($name)."'>$name</A></P>";
            }
            echo "</TD>"
                ."<TD valign='top'>";

            echo "<H2>Garlic Variety : $cv</H2>";

            echo "<STYLE>"
                .".sldesc_report { margin-bottom:1em;}"
                .".sldesc_report_label   {font-weight:bold}"
                .".sldesc_report_body    {font-size:9pt; margin-left:3em;}"
                .".sldesc_report_body th {font-size:9pt; padding-left:0;margin-left:0;text-align:left;}"
                .".sldesc_report_body td {font-size:9pt;}"
                ."</STYLE>";

            // show the photo
            $img = "img/".htmlspecialchars($cv,ENT_QUOTES).".jpg";
            echo "<DIV style='float:right; border:thin solid black; padding:1.5em; margin: 2em;'>"
                ."<A HREF='$img'><IMG src='$img' width='300'></A>"
                ."<DIV style='text-align:center; padding:0.5em'>$cv</DIV>"
                ."</DIV>";


            /* Show observations
             */
            $raVI = $oSLD->GetListVarInst( array("osp"=>"garlic", "oname"=>$cv) );
            if( count($raVI) ) {
                echo "<P>Our members have reported ".count($raVI)." trials of this variety.</P>"
                    ."<BR/>";
            }

            // bulb diameter
            $code = "garlic_GRIN_f__BULBDIAM";
            $raDO = $oSLD->GetListDescObs( array("osp"=>"garlic", "oname"=>$cv, "desc_k" => $code ) );
            if( count($raDO) ) {
                echo $oSLDReport->Report_f_cm2in( $code, $raDO, "0.5" );
            }
            // bulb diameter
            $code = "garlic_GRIN_f__PLANTHEIGHT";
            $raDO = $oSLD->GetListDescObs( array("osp"=>"garlic", "oname"=>$cv, "desc_k" => $code ) );
            if( count($raDO) ) {
                echo $oSLDReport->Report_f_cm2in( $code, $raDO, "4.0" );
            }
            // cloves per bulb
            $code = "garlic_SoD_i__clovesperbulb";
            $raDO = $oSLD->GetListDescObs( array("osp"=>"garlic", "oname"=>$cv, "desc_k" => $code ) );
            if( count($raDO) ) {
                echo $oSLDReport->Report_i_geom( $code, $raDO, "1.4" );
            }


            echo "</TD></TR></TABLE>";
        }
    }

    if( empty($cv) ) {
        echo "<H2>Garlic Varieties</H2>";

        $i = 0;
        echo "<TABLE border=0 cellpadding=30><TR>";
        foreach( $raNames as $name ) {
            if( $i % 20 == 0 ) {
                if( $i != 0 ) echo "</TD>";
                echo "<TD valign='top'>";
            }
            echo "<P><A HREF='{$_SERVER['PHP_SELF']}?cv=".urlencode($name)."'>$name</A></P>";
            ++$i;
        }
        echo "</TD></TR></TABLE>";
    }
}

?>
