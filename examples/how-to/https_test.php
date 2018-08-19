<?

if( $_SERVER['HTTPS']=='on' ) {
    echo "HTTPS is ON";
print_r($_REQUEST);
} else {
    header( "Location: https://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']."?foo=bar" );
//    echo "<HEAD><META HTTP-EQUIV='Refresh' Content='5; url=https://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']."?foo=bar'></HEAD>";
    echo "HTTPS is OFF - Redirecting";
}

?>
