<?

include_once( "_dw.php" );


if( $_REQUEST['page'] == 2 ) {
    $varieties = array("Pritchard VR", "Pritchard", "Ildi", "German Giant", "Yellow Plum", "Earliana", "Sugawara" );
} else {
    $varieties = array("Firesteel VR", "Fireball VR", "Stupice", "Fireball", "Betty Kreton's Sweet 100", "Firesteel", "Kanatto", "Tigerella" );
}
$nCV = count($varieties);

echo "<STYLE>";
echo "H2, P                 { font-family: verdana,arial,helvetica,sans serif; }";
echo "TD,TD INPUT,TD SELECT { font-family: verdana,arial,helvetica,sans serif; font-size: 9px; }";
echo "TD SELECT,TD INPUT    { width: 100px; }";
echo "</STYLE>";


echo "<H2>Tomatoes</H2>";

echo "<FORM action='dwc_submit.php' method='post'>";
echo "<INPUT type=hidden name=dw_sp value='tomato'>";

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


foreach( $raDefTomato as $k => $raV ) {
    dwc_q_smart( $k, $raDefTomato, $nCV );
}

echo "</TABLE><P><INPUT type=submit></FORM>";

?>
