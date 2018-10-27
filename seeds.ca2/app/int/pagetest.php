<?php

if( !defined("SITEROOT") )  define("SITEROOT", "../../");
include_once( SITEROOT."site.php" );

$s .= "
<div id='consolePage0'>
<p>Question 1: What's your name?</p>
<input type='text' id='myname'/>
<button onclick='consolePageSubmit()'>Next</button>
</div>

<div id='consolePage1' style='display:none'>
<div>Your name is <span id='myname'></span></div>
<p>Question 2: Who's your cat?</p>
<input type='text' id='mycat'/>
<button onclick='consolePageSubmit()'>Next</button>
</div>

<div id='consolePage2' style='display:none'>
<p>Confirm</p>
<div>Your name is <span id='myname'></span></div>
<div>Your cat is <span id='mycat'></span></div>
<input type='submit' onclick='consolePageSubmit()'/>
</div>
";



$s .= "
<script>
ConsolePageStart( { nMaxPage: 2,
                    vars: {},
                    fns: {
                        0: {
                             pre: function() {},
                             post: function() {
                                 var name = $('#consolePage0 #myname').val();
                                 var p = 0;

                                 if( name != '' ) {
                                     consolePageObj['vars']['name'] = name;
                                     p = 1;
                                 }

                                 return( p );
                             }
                           },
                        1: {
                             pre: function() {
                                 $('#consolePage1 #myname').html(consolePageObj['vars']['name']);
                             },
                             post: function() {
                                 var cat = $('#consolePage1 #mycat').val();
                                 var p = 1;

                                 if( cat != '' ) {
                                     consolePageObj['vars']['cat'] = cat;
                                     p = 2;
                                 }

                                 return( p );
                             }
                           },
                        2: {
                             pre: function() {
                                 $('#consolePage2 #myname').html(consolePageObj['vars']['name']);
                                 $('#consolePage2 #mycat').html(consolePageObj['vars']['cat']);
                             },
                             post: function() {
                                 finalReport();
                                 return( 2 );
                             },
                           }
                    }
} );

function finalReport()
{
    alert( 'You are '+consolePageObj['vars']['name']+' and your cat is '+consolePageObj['vars']['cat'] );
}

</script>";




echo Console02Static::HTMLPage( utf8_encode($s), "", 'EN', array('raScriptFiles'=>array(W_CORE.'js/console02.js')) );   // sCharset defaults to utf8

?>