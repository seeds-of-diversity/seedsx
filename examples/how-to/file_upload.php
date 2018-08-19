<?

function show_contents( $fname )
{
    echo "<P>$fname contains:</P>";
    echo "<PRE>";
    if( ($f = fopen( $fname, "r" )) ) {
        echo fread( $f, filesize( $fname ) );
        fclose( $f );
    } else {
        echo "Could not open". $fname;
    }
    echo "</PRE>";
}


if( @$_REQUEST['step'] != '1' ) {
    // step 0

    echo "<FORM action='${_SERVER['PHP_SELF']}' method=post enctype='multipart/form-data'>";
    echo "<INPUT type=hidden name=step value='1'>";
    echo "<INPUT type=file name=upFile>";
    echo "<BR>";
    echo "<INPUT type=submit>";
    echo "</FORM>";

} else {
    // step 1
    error_reporting(E_ALL);

    $upFile = @$_FILES['upFile'];
    print_r($upFile);

    echo "<P>The file that you posted is <B>${upFile['name']}</B>, type <B>${upFile['type']}</B>, size <B>${upFile['size']}</B> bytes.</P>";
    echo "<P>It was stored on the server as <B>${upFile['tmp_name']}</B></P>";
    if( is_uploaded_file($upFile['tmp_name']) ) {
        echo "<P>It is an upload file</P>";
    } else {
        die( "<P>It is not an upload file</P>" );
    }
    echo "<P>Perms=<B>". decoct(fileperms($upFile['tmp_name'])). "</B> Owner=<B>". fileowner($upFile['tmp_name']) ."</B></P>";
    show_contents( $upFile['tmp_name'] );

    echo "<HR>";

    $destFile = "/home/seeds/public_html". dirname($_SERVER['PHP_SELF']) ."/". $upFile['name'];
    echo "<P>Trying to move ${upFile['tmp_name']} to $destFile</P>";
    if( move_uploaded_file( $upFile['tmp_name'], $destFile ) ) {
        echo "<P>Moved to ". $destFile .".  Perms=". decoct(fileperms($destFile)). " Owner=". fileowner($destFile) ."</P>";
        if( chmod( $destFile, 0644 ) ) {
            clearstatcache();
            echo "<P>Changed perms to ". decoct(fileperms($destFile)) ."</P>";
        } else {
            echo "<P>Cannot change perms ". $destFile ."</P>";
        }
    } else {
        echo "<P>Cannot move file to ". $destFile ."</P>";
    }


    show_contents( $destFile );
}


?>
