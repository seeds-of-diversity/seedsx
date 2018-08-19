/* SeedBreeze Javascript
 */
 
 function SeedBreeze_HelloWorld()
 {
     alert( "Hello World!" );
 }
 
 
 
//$("document").ready( function() {
jQuery(document).ready(function($){

    /* Processor for SEEDCore_EmailAddress2
     */
    $(".SEEDCore_mailto").each( function() {
        var a       = $(this).attr("a");
        var b       = $(this).attr("b"); 
        var caption = $(this).attr("c"); 
        var mparms  = $(this).attr("d"); 
        
        var addr = a + "@" + b;
        
        if( !caption ) caption = addr;
        if( mparms )   addr = addr + "?" + mparms;
        
        $(this).attr( "href", "mailto:"+addr );
        $(this).html( caption );
    });
    
});
