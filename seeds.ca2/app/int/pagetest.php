<?php

if( !defined("SITEROOT") )  define("SITEROOT", "../../");
include_once( SITEROOT."site.php" );

$s = "
<div id='consolePageStart' class='consolePage'>
<form>
<p>Question 1: What's your name?</p>
<input type='text' class='cpvar_name'/>
<input type='submit' value='Next'/>
</form></div>

<div id='consolePage1' class='consolePage' style='display:none'>
<form>
<div>Your name is <span class='cpvar_name'></span></div>
<p>Question 2: Who's your cat?</p>
<input type='text' class='cpvar_cat'/>
<input type='submit' value='Next'/>
</form>
</div>

<div id='consolePage2' class='consolePage' style='display:none'>
<form>
<p>Confirm</p>
<div>Your name is <span class='cpvar_name'></span></div>
<div>Your cat is <span class='cpvar_cat'></span></div>
<input type='submit'/>
</form>
</div>
";



$s .= "
<script>
var config = {
        pages: {
            Start: {
                 model: 'LoadStore',
                 fnPre: function() {},
                 fnPost: function() {
                     return( oCP.FormVal('name') != '' ? 1 : '' );
                 }
               },
            1: {
                 model: 'LoadStore',
                 fnPre: function() {},
                 fnPost: function() {
                     return( oCP.FormVal('cat') != '' ? 2 : '' );
                 }
               },
            2: {
                 model: 'LoadStore',
                 fnPre: function() {},
                 fnPost: function() {
                     finalReport();
                     return( 2 );
                 },
               }
        }
};

var oCP = new ConsolePage( config );
oCP.Ready();

function finalReport()
{
    alert( 'You are '+oCP.GetVar('name')+' and your cat is '+oCP.GetVar('cat') );
}

</script>";




echo Console02Static::HTMLPage( utf8_encode($s), "", 'EN', array('raScriptFiles'=>array(W_CORE.'js/console02.js')) );   // sCharset defaults to utf8

?>