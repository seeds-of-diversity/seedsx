<?php

if( !defined("SITEROOT") )  define("SITEROOT", "../../");
include_once( SITEROOT."site.php" );

$s = "
<div id='consolePage0'>
<form onsubmit='oCP.PageSubmit();return false;'>
<p>Question 1: What's your name?</p>
<input type='text' class='cpvar_name'/>
<input type='submit' value='Next'/>
</form></div>

<div id='consolePage1' style='display:none'>
<form onsubmit='oCP.PageSubmit();return false;'>
<div>Your name is <span class='cpvar_name'></span></div>
<p>Question 2: Who's your cat?</p>
<input type='text' class='cpvar_cat'/>
<input type='submit' value='Next'/>
</form>
</div>

<div id='consolePage2' style='display:none'>
<form onsubmit='oCP.PageSubmit();return false;'>
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
            0: {
                 fnPre: function() {},
                 fnPost: function() {
                     let name = oCP.FormVal( 0, 'name' );
                     let p = 0;

                     if( name != '' ) {
                         //oCP.SetVar('name',name);
                         oCP.StoreVars(0);
                         p = 1;
                     }

                     return( p );
                 }
               },
            1: {
                 fnPre: function() {
                     oCP.LoadVars(1);
                     //$('#consolePage1 .cpvar_name').html(oCP.GetVar('name'));
                 },
                 fnPost: function() {
                     let cat = oCP.FormVal( 1, 'cat' ); //$('#consolePage1 .cpvar_cat').val();
                     let p = 1;

                     if( cat != '' ) {
                         //oCP.SetVar('cat',cat);
                         oCP.StoreVars(1);
                         p = 2;
                     }

                     return( p );
                 }
               },
            2: {
                 fnPre: function() {
                     oCP.LoadVars(2);
                     //$('#consolePage2 .cpvar_name').html(oCP.GetVar('name'));
                     //$('#consolePage2 .cpvar_cat').html(oCP.GetVar('cat'));
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