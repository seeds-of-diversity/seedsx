<?
include("../std/SEEDStd.php");

echo "<STYLE>p { font-size:9pt; }</STYLE>";

echo '<P>string SEEDStd_FmtNumber( float $number  [, $nDecimals  [, $sDecPoint [, $sDecThousandsSep ]]]</P>';

draw( 1234.5678 );
draw( 0.02 );


function draw( $number )
{
    echo "<BR/><P>number is $number</P>"
        .'<P>Default, full precision with comma separator ($number), should be 1,234.5678 = '.SEEDStd_FmtNumber($number)."</P>"
        .'<P>Round to 0 decimals with comma separator ($number,0), should be 1,235 = '.SEEDStd_FmtNumber($number,0)."</P>"
        .'<P>French notation, 1 decimal place ($number, 1, ",", " "), should be 1 234.6 = '.SEEDStd_FmtNumber($number,1,',',' ')."</P>"
        .'<P>English notation, 2 decimal places, no thousands separator ($number,2,".",""), should be 1234.57 = '.SEEDStd_FmtNumber($number,2,'.','')."</P>"
        ."<HR/>";
}

?>