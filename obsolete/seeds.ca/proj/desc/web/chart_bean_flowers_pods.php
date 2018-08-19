<?

include_once( "_dw.php" );



$varieties = array("Blue Jay", "Odawa", "Croatian Blue", "Ianetti", "Jacob's Cattle", "Porter", "Cascade Giant", "Luisa's Romano");
$nCV = count($varieties);

echo "<STYLE>";
echo "H2, P                 { font-family: verdana,arial,helvetica,sans serif; }";
echo "TD,TD INPUT,TD SELECT { font-family: verdana,arial,helvetica,sans serif; font-size: 9px; }";
echo "TD SELECT,TD INPUT    { width: 100px; }";
echo "</STYLE>";


echo "<H2>Beans - Flowers and Pods</H2>";

echo "<FORM action='dwc_submit.php' method='post'>";
echo "<INPUT type=hidden name=dw_sp value='bean'>";

echo "<P>Observer <INPUT type='text' name='dwc0_common_SoD_s__observerid'></P>";
echo "<P>Location <INPUT type='text' name='dwc0_common_SoD_s__locationid'></P>";
echo "<P>Date <INPUT type='text' name='dwc0_common_SoD_s__date'></P>";




echo "<TABLE width=100%>";

echo "<TR><TH width=100>&nbsp;</TH>";
for( $i = 0; $i < count($varieties); ++$i ) {
    echo "<TH>".$varieties[$i]."</TH>";
    echo "<INPUT type='hidden' name='dwc".($i + 1)."_common_SoD_s__cultivarname' value='".htmlspecialchars($varieties[$i],ENT_QUOTES)."'>";
}
echo "</TR>";




dwc_q_m( "bean_NOR_m__PLAN_LOCAT", $raDefBean, $nCV );
dwc_q_m( "bean_NOR_m__FLOW_BRACT", $raDefBean, $nCV );
dwc_q_m( "bean_NOR_m__FLOW_STAND", $raDefBean, $nCV );
dwc_q_m( "bean_NOR_m__FLOW_WINGS", $raDefBean, $nCV );
dwc_q_f( "bean_NOR_f__PLANT_CM"  , $raDefBean, $nCV );
dwc_q_m( "bean_NOR_m__TERMI_SHAPE", $raDefBean, $nCV );
dwc_q_m( "bean_NOR_m__TERMI_SIZE", $raDefBean, $nCV );
dwc_q_m( "bean_NOR_m__TERMI_APEX", $raDefBean, $nCV );
dwc_q_m( "bean_NOR_m__GRAI_COLOR", $raDefBean, $nCV );
dwc_q_f( "bean_SoD_f__podlength" , $raDefBean, $nCV );
dwc_q_m( "bean_NOR_m__POD_SECTIO", $raDefBean, $nCV );
dwc_q_m( "bean_NOR_m__POD_GROUND", $raDefBean, $nCV );
dwc_q_m( "bean_NOR_m__POD_INTENS", $raDefBean, $nCV );
dwc_q_m( "bean_NOR_m__POD_PIGMEN", $raDefBean, $nCV );
dwc_q_m( "bean_NOR_m__POD_PIGCOL", $raDefBean, $nCV );
dwc_q_m( "bean_NOR_m__POD_STRING", $raDefBean, $nCV );
dwc_q_m( "bean_NOR_m__POD_CURVAT", $raDefBean, $nCV );
dwc_q_m( "bean_NOR_m__POD_SHACUR", $raDefBean, $nCV );
dwc_q_m( "bean_NOR_m__POD_SHATIP", $raDefBean, $nCV );
dwc_q_m( "bean_NOR_m__POD_LEBEAK", $raDefBean, $nCV );
dwc_q_m( "bean_NOR_m__POD_CURBEA", $raDefBean, $nCV );
dwc_q_m( "bean_NOR_m__POD_PROMIN", $raDefBean, $nCV );
dwc_q_m( "bean_NOR_m__POD_TEXTUR", $raDefBean, $nCV );

echo "</TABLE><P><INPUT type=submit></FORM>";

?>
