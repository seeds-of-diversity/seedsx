/* SeedLiving javascript
 *
 * Copyright 2013-2016 Seeds of Diversity Canada
 */

$(document).ready(function()
{
    slivInit();
});


var bSlivFriendlyURLs = true;

function slivPathGetName()
/*************************
    Get the current page name from the url

    www.seedliving.ca                      (name is blank)
    www.seedliving.ca/sl2/name
    www.seedliving.ca/sl.php?overlord=name
    host/.../seedliving                    (name is blank)
    host/.../seedliving/sl2/name
    host/.../seedliving/sl.php?overlord=name
 */
{
    var name = "";
    var m;

    if( (m = window.location.href.match( /overlord=([^&]*)/ )) ) {
        name = m[1];
    } else {
        s = /\/seedliving\/([^/\?]*)/;    // Eclipse syntax doesn't like the \? but it's a literal ? to terminate "/sl2/foo?a=b", not a regex specifier.
                                          // If Eclipse stops highlighting an error here, please remove this comment.
        if( (m = window.location.pathname.match( s )) ) {
            name = m[1];
        }
    }
    return( name ); 
}

function slivPathMake( page )
/****************************
    Make a link path from a page name

    Host url                       Output
    --------                       ------
    www.seedliving.ca              /page
    www.seeds.ca/seedliving        /seedliving/page
    localhost/.../seedliving       /.../seedliving/page
 */
{
    var ret = "";
    var pth = window.location.pathname;
    var i = pth.indexOf( "/seedliving" );

    if( i == -1 ) {
        // there is no /seedliving component
        ret = "/"+page;
    } else {
        ret = pth.substring( 0, i ) + "/seedliving";
        if( page ) {
            ret += (bSlivFriendlyURLs ? "/" : "/sl.php?overlord=") + page; 
        }
    }
    return( ret );
}


function slivInit()
/******************
    Init JQuery hooks
 */
{
    // <foo class='basketAdd' seedid='123'>
    //     Click here to add product-123 to the basket 
    // </foo>
    $(".basketAdd").click(function(){
        $.ajax({
            type: "POST",
            url: slivPathMake("updateCart-"+$(this).attr("seedid")) + "/",
            success: function(data){
                if(data=="0") alert("Sorry: All items in your cart have to be in the same currency, since PayPal only allows one type of currency per transaction.");
                else if(data=="1") alert("Item is already in your basket");
                else {
                    $("#cartTotal").html(data);
                    alert("Item has been added to your basket");
                }
            }
        });
    });
}