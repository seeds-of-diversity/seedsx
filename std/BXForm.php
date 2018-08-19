<?


function BXFormHiddenStr( $name, $value )
/****************************************
    Remove the guesswork about how to escape HTML-unfriendly characters in an arbitrary string
 */
{
    return( "<INPUT type=hidden name='$name' value='".htmlspecialchars($value,ENT_QUOTES)."'>" );
}


function BXFormText( $name, $value, $label = "", $size = 50 )
/************************************************************
 */
{
    $s = "";

    if( !empty( $label ) ) {
        $s .= "$label:&nbsp;";
    }
    $s .= "<INPUT type=text name='$name' value='".htmlspecialchars($value,ENT_QUOTES)."' size=$size>";
    return( $s );
}


function BXFormOption( $value, $label, $currValue = "" )
/*******************************************************
 */
{
    return( "<OPTION value='".htmlspecialchars($value,ENT_QUOTES)."'".($value==$currValue ? " SELECTED" : "").">$label</OPTION>" );
}


?>
