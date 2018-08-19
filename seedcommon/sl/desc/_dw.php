<?

function dwc_q_f( $dwCode, $raDef, $nCV )
{
    dwc_q_i( $dwCode, $raDef, $nCV );
}
function dwc_q_i( $dwCode, $raDef, $nCV )
{
    if( ($ra = $raDef[$dwCode]) ) {
        echo "<TR><TD valign='top'>$ra[0]<HR></TD>";
        for( $i = 1; $i <= $nCV; ++$i ) {
            echo "<TD valign='top'><INPUT type='text' name='dwc{$i}_{$dwCode}'></TD>";
        }
        echo "</TR>";
    } else {
        echo "<P>$dwCode not found</P>";
    }
}
function dwc_q_m( $dwCode, $raDef, $nCV )
/****************************************
 */
{
    if( ($ra = $raDef[$dwCode]) ) {
        echo "<TR><TD valign='top'>$ra[0]<HR></TD>";
        for( $i = 1; $i <= $nCV; ++$i ) {
            echo "<TD valign='top'><SELECT name='dwc{$i}_{$dwCode}'><OPTION value=''></OPTION>";
            foreach( $ra[2] as $k => $v ) {
                echo "<OPTION value='$k'>$v</OPTION>";
            }
            echo "</SELECT></TD>\n";
        }
        echo "</TR>";
    } else {
        echo "<P>$dwCode not found</P>";
    }
}

function dwc_q_smart( $dwCode, $raDef, $nCV )
/********************************************
 */
{
    $r = explode("__", $dwCode );
    $r = substr( $r[0], strlen($r[0])-1 );
    switch( $r ) {
        case 'm':   dwc_q_m( $dwCode, $raDef, $nCV );       break;
        case 'f':   dwc_q_f( $dwCode, $raDef, $nCV );       break;
        case 'i':   dwc_q_i( $dwCode, $raDef, $nCV );       break;
        default:    echo "<P>Unknown code type $dwcode</P>";
    }
}



// s:blank == don't know
// d:blank == don't know
// m:blank == 0 == don't know
// b:blank == 0 == don't know
// intval(i:blank) == 0 == don't know
// floatval(f:blank) == 0 == don't know


function dw_commonTop( $label, $dw_sp )
{
/*
    echo "<DIV><IMG src='".SITEIMG."logo_EN.gif'></DIV>";
    echo "<H3 class='d_secthdr'>$label Observation Form</H3>";

    echo "<DIV style='border:medium solid grey; padding:15px; text-align:center;' class='d_a'>";

    echo "<DIV style='padding:0'>Seeds of Diversity uses this form to record observations of plant characteristics across the country. ";
    echo "You can add to Canadians' knowledge of our precious horticultural heritage by noting your observations of ";
    echo "heritage plants in your garden, and entering the information in our public database. ";
    echo "All characteristics are shown publicly, but your contact information is always kept confidential.</DIV>";

    echo "<TABLE border=0 cellpadding=20><TR>";
    echo "<TD class='d_a' align=center>The research and development for this project was fully funded by<BR/> the Ontario Trillium Foundation.</TD>";
    echo "<TD><IMG src='".SITEIMG."OTF_HORZTL_CLR_4_Microsoft.jpg' width=350></TD>";
    echo "</TR></TABLE>";

    echo "<DIV style='font-size:7pt;'>Illustrations: Seeds of Diversity Canada; International Plant Genetic Resources Institute</DIV>";

    echo "</DIV>";

    echo "<FORM action='dw_submit.php' method='post' action='${_SERVER['PHP_SELF']}'>";
    echo "<INPUT type=hidden name=dw_sp value='$dw_sp'>";
*/

    echo "<DIV class='d_inst'>";
    echo "<P>If you cannot answer any of these questions, just leave them blank or check the \"don't know\" choice.</P>";
    echo "</DIV>";

/*
    echo "<TABLE width='100%' border=0><TR><TD valign=top width=25%>";
    dw_q_s( "Variety name", "common_SoD_s__cultivarname" );
    echo "</TD><TD valign=top class='d_a'>";
    echo "<INPUT type='checkbox' name='common_SoD_b__cultivarnameunknown' value='1'>Variety is unknown";
    echo "</TD></TR></TABLE>";

    dw_q_s( "Observer", "common_SoD_s__observerid" );
    dw_q_s( "Location", "common_SoD_s__locationid" );

    dw_q_m( "Soil type where this variety was growing?", "common_SoD_m__soiltype",
            array( 1=>"Sandy", 2=>"Loamy", 3=>"Loamy sand", 4=>"Loamy clay", 5=>"Clay", 6=>"Other", 0=>"don't know" ) );

    dw_q_s( "If Other, describe your soil", "common_SoD_s__soiltypeother" );
*/
    dw_q_i( "Sample size: Approximately how many ".($dw_sp=='apple' ? "trees" : "plants")." are you observing for the purposes of this form?",
            "common_SoD_i__samplesize" );
}

function dw_commonBottom()
{
    echo "<INPUT type=submit value='Submit'>";
    echo "</FORM>";

    std_footer();
}

?>
