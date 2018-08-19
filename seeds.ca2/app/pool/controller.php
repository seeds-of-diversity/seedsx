<?php
define( 'SITEROOT', "../../");
include( SITEROOT."site.php" );

if( @$_GET['logout'] == 1 ) { $_COOKIE['pool-password'] = $_REQUEST['pool-password'] = ""; setcookie( "pool-password", "" ); }


($password = @$_REQUEST['pool-password']) || ($password = @$_COOKIE['pool-password']);
if( $password != "foobar" ) {
    echo "<h3>Welcome to Our Pool Controller</h3>"
        ."<form method='post'>"
        ."<p>Please login with your password: <input type='password' name='pool-password'/> <input type='submit' value='Login'/></p>"
        ."</form>";

    exit;
}

setcookie( "pool-password", "foobar" );

list($kfdb) = SiteStart();

$oBucket = new SEEDMetaTable_StringBucket( $kfdb );
$t = $oBucket->GetStr( "Dunbar", "myip" );

$errno = 0;
$errstr = "";
$fp = fsockopen($t, 12345, $errno, $errstr, 30);
if (!$fp) {
    echo "$errstr ($errno)<br />";
} else {
    $out = "GET / HTTP/1.1\r\n";
    $out .= "Host: $t\r\n";
    $out .= "Connection: Close\r\n\r\n";
    fwrite($fp, $out);
    while (!feof($fp)) {
        echo fgets($fp, 128);
    }
    fclose($fp);
}



//$s = "Location: http://$t:12345";

//echo $s; exit;

//header( $s );

?>
