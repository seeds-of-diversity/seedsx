function SEEDDateString( date, bShowDate, bShowTime )
/****************************************************
 */
{
    s = "";
    if( bShowDate ) {
        var raDays = new Array("Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday");
        var raMonths = new Array("January","February","March","April","May","June","July","August","September","October","November","December");
        var year = date.getYear();

        year += (year > 99 && year < 200) ? 1900 : ( year < 100 ? 2000 : 0 );

        s += raDays[date.getDay()]+"&nbsp;"+raMonths[date.getMonth()]+"&nbsp;"+date.getDate()+"&nbsp;"+year;
    }
    if( bShowTime ) {
        if( bShowDate ) { s+="&nbsp;&nbsp;"; }
        s += date.getHours()+":"+(date.getMinutes() < 10 ? "0" : "")+date.getMinutes()+":"+(date.getSeconds() < 10 ? "0" : "")+date.getSeconds();
    }
    return( s );
}


function SEEDDateStringToday()
/*****************************
 */
{
    return( SEEDDateString( (new Date()), 1, 0 ) );
}


function SEEDDateStringNow()
/***************************
 */
{
    return( SEEDDateString( (new Date()), 1, 1 ) );
}


function SEEDDateStringNowTime()
/*******************************
 */
{
    return( SEEDDateString( (new Date()), 0, 1 ) );
}
