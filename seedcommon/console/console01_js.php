<?

// some JS tricks

// This draws a stack of divs. When you click on one, it positions a "form" beside that one and
// scrolls it into view.

for( $n = 1; $n < 100; ++$n ) {
    echo "<DIV id='d$n' style='width:50%;height:100px;border: 1px solid green;text-align:center;vertical-align:middle;'>"
    ."<A HREF='{$_SERVER['PHP_SELF']}?n=$n'>$n</A></DIV>";
}

if( ($n = @$_REQUEST['n']) ) {
    echo "<SCRIPT>document.all.d${n}.scrollIntoView(true);</SCRIPT>"
	    ."<DIV id='dForm' style='position:absolute;border:1px solid black;width:30%; height:400px;margin:10px;'>"
	    ."This is the form for $n"
	    ."</DIV>"
        ."<SCRIPT>"
        ."document.getElementById('dForm').style.top = document.getElementById('d${n}').offsetTop;"
        ."document.getElementById('dForm').style.left = document.getElementById('d${n}').offsetLeft "
                                                     ."+ document.getElementById('d${n}').offsetWidth;"
        ."</SCRIPT>";
}

?>
