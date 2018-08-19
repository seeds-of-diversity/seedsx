<?php

function makeTextBox( $sText, $iFontSize )
{
    if( !$sText ) {
        $im = @imagecreate( 1, 1 );
        $background_color = imagecolorallocate($im, 255, 255, 255);  // white
        goto done;
    }

    $box = imagettfbbox( $iFontSize, 0, './arial.ttf', $sText );  // angle 0
    $width = abs($box[4] - $box[0]);
    $height = abs($box[5] - $box[1]);

    $im = @imagecreate( $width + 20, $height + 25 )
        or die("Cannot Initialize new GD image stream");
    $background_color = imagecolorallocate($im, 255, 255, 255);  // white
    $text_color = imagecolorallocate($im, 0,0,0);                // black

    imagettftext( $im, $iFontSize, 0, 10, $height+7, $text_color, './arial.ttf', $sText );  // angle 0, xoffset 10, yoffset h+8 (bottom of char)

    done:
    return( $im );
}

function SelectStatus( $currVal )
{
    $s = SEEDForm_Select2( 'Status',
                           array( 'Open' => 'open',
                                  'Temporarily closed' => 'closed-temp',
                                  'Closed for the season' => 'closed' ),
                           $currVal );
    return( $s );
}


function StrTime2UTC( $sTime )
/*****************************
    Convert a datetime string to a UTC timestamp assuming we're in the EDT timezone
 */
{
    if( ($iTime = intval(strtotime( $sTime ))) ) {
        $iTime += 18000;    // EDT is +5 hours
    }
    return( $iTime );
}

?>
