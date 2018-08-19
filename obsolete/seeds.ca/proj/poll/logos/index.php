<?

$raLogos = array(
array(
"Logo_plain_colour_EN_1800.jpg",
"Logo_plain_bw_EN_1800.jpg",  //"Logo_plain_colour_EN_300.jpg",
"",  // "Logo_plain_bw_FR_1800.jpg",     something wrong with the colour - fails to load in firefox, looks wrong in ACDSee
"Logo_plain_bw_FR_1800.jpg",
),
array(
"Logo_plainweb_colour_EN_1800.jpg",
"Logo_plainweb_bw_EN_1800.jpg",
"Logo_plainweb_colour_FR_1800.jpg", 
"Logo_plainweb_bw_FR_1800.jpg",
),
array(
"Logo_banner2_colour_EN_1800.jpg",
"Logo_banner2_bw_EN_1800.jpg",
),
array(
"Logo_banner3_colour_EN_1800.jpg",
"Logo_banner3_bw_EN_1800.jpg",
),
array(
"Logo_banner_colour_EN_1800.jpg",
"Logo_banner_colour_FR_1800.jpg",
"Logo_banner_colour_BI_1800.jpg",
),
array(
"Logo_banner_bw_EN_1800.jpg",
"Logo_banner_bw_FR_1800.jpg",
"Logo_banner_bw_BI_1800.jpg",
),
array(
"Logo_bannerweb_colour_EN_1800.jpg",
"Logo_bannerweb_bw_EN_1800.jpg",
"Logo_bannerweb_colour_FR_1800.jpg",
"Logo_bannerweb_bw_FR_1800.jpg",
),
);


echo "<H2>Pollination Canada Logos</H2>";

echo "<TABLE border='0' cellpadding='20' cellspacing='0'>";
foreach( $raLogos as $raL ) {
    echo "<TR>";
    foreach( $raL as $sLogo ) {
        echo "<TD valign='top'>";
        if( !empty( $sLogo ) ) {
            echo "<A HREF='$sLogo' target='_blank'>"
                ."<IMG src='$sLogo' height='50'>"
                ."</A>";
        }
	echo "&nbsp;";
	echo "</TD>";
    }
    echo "</TR>";
}
echo "</TABLE>";

?>
