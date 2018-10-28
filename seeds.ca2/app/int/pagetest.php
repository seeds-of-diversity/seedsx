<?php

if( !defined("SITEROOT") )  define("SITEROOT", "../../");
include_once( SITEROOT."site.php" );

$s = "
<div id='consolePage0'>
<form onsubmit='oCP.PageSubmit();return false;'>
<p>Question 1: What's your name?</p>
<input type='text' id='myname'/>
<input type='submit' value='Next'/>
</form></div>

<div id='consolePage1' style='display:none'>
<form onsubmit='oCP.PageSubmit();return false;'>
<div>Your name is <span id='myname'></span></div>
<p>Question 2: Who's your cat?</p>
<input type='text' id='mycat'/>
<input type='submit' value='Next'/>
</form>
</div>

<div id='consolePage2' style='display:none'>
<form onsubmit='oCP.PageSubmit();return false;'>
<p>Confirm</p>
<div>Your name is <span id='myname'></span></div>
<div>Your cat is <span id='mycat'></span></div>
<input type='submit'/>
</form>
</div>
";



$s .= "
<script>
var config = {
                    pages: {
                        0: {
                             fnPre: function() {},
                             fnPost: function() {
                                 let name = $('#consolePage0 #myname').val();
                                 let p = 0;

                                 if( name != '' ) {
                                     oCP.SetVar('name',name);
                                     p = 1;
                                 }

                                 return( p );
                             }
                           },
                        1: {
                             fnPre: function() {
                                 $('#consolePage1 #myname').html(oCP.GetVar('name'));
                             },
                             fnPost: function() {
                                 let cat = $('#consolePage1 #mycat').val();
                                 let p = 1;

                                 if( cat != '' ) {
                                     oCP.SetVar('cat',cat);
                                     p = 2;
                                 }

                                 return( p );
                             }
                           },
                        2: {
                             fnPre: function() {
                                 $('#consolePage2 #myname').html(oCP.GetVar('name'));
                                 $('#consolePage2 #mycat').html(oCP.GetVar('cat'));
                             },
                             fnPost: function() {
                                 finalReport();
                                 return( 2 );
                             },
                           }
                    }
};

var oCP = new ConsolePage( config );

function finalReport()
{
    alert( 'You are '+oCP.GetVar('name')+' and your cat is '+oCP.GetVar('cat') );
}

</script>";




echo Console02Static::HTMLPage( utf8_encode($s), "", 'EN', array('raScriptFiles'=>array(W_CORE.'js/console02.js')) );   // sCharset defaults to utf8

?>