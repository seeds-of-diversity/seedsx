<?

include("../std/SEEDStd.php");
var_dump( SEEDStd_ParseAttrs( " border=1 cellspacing=0 style='margin-left: 1em;'" ) );
var_dump( SEEDStd_ParseAttrs( ' font="3" color=green' ) );

?>