<?php
define( 'SITEROOT', "../../");
include( SITEROOT."site.php" );


list($kfdb) = SiteStart();

$oBucket = new SEEDMetaTable_StringBucket( $kfdb );
$t = $oBucket->GetStr( "PoolController", "sStatus" );


$signs = array( 'closed'      => 'closed-for-the-season.gif',
                'open'        => 'open.png',
                'closed-temp' => 'closed-temp.jpg'
              );


switch( $t ) {
    case 'closed':
    case 'open':
    case 'closed-temp':
        header( "Content-type:image/".substr($signs[$t],-3) );
        $f = fopen( $signs[$t], "r" );
        fpassthru( $f );
        break;

    default:
        header("Content-type:image/png");

        $im = @imagecreate(400, 200)
            or die("Cannot Initialize new GD image stream");
        $background_color = imagecolorallocate($im, 255, 255, 255);
        $text_color = imagecolorallocate($im, 0,0,0);
    
        imagettftext($im, 50, 0, 50,110, $text_color, './arial.ttf', $t);
        imagepng($im);
        imagedestroy($im);
}

?>
