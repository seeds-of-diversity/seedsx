<H2> Snipped from QuirksMode - Detecting Keystrokes </H2>

<script type="text/javascript">
<!--

window.onload =
function () {
    document['onkeypress'] = detectEvent;
}

function detectEvent(e) {
	var evt = e || window.event;
    s = evt.type + ': ';
    s += 'keyCode=' + evt.keyCode;
    s += ', charCode=' + evt.charCode;
	writeData( s );
	writeData('');
    if(evt.charCode==97) window.top.location='http://www.seeds.ca';
	return true;
}

function writeData(msg) {
	document.getElementById('writeroot').innerHTML += msg + '<br />';
}

// -->
</script>

<style type="text/css">
#writeroot {
	height: 300px;
	overflow: auto;
	border: 1px solid #2EB2DC;
}
</style>


<p id="writeroot"></p>

