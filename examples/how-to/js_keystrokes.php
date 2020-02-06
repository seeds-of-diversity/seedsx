<h2>Detecting Keystrokes </h2>

<script type="text/javascript">

document.onkeypress = detectKey;
document.onkeydown = detectKey;

function detectKey(e) {
    e = e || window.event;    // for IE9 which doesn't pass the event to the function
    writeLine( e.type + ': '
               + 'keyCode=' + e.keyCode
               + ', charCode=' + e.charCode
               + ', key=' + e.key );
    return true;
}

function writeLine(msg) {
    document.getElementById('myWrite').innerHTML += msg + '<br />';
}
</script>

<p id="myWrite" style="height: 300px; overflow: auto; border: 1px solid #2EB2DC;"></p>

