<?

/* Functions that make KeyFrame form development easier
 */

// see sed.php for some of this (search for kfrUI)


function KFRForm_Text( $kfr, $label, $name, $size = 50, $parmStr = "" )
/**********************************************************************
 */
{
    $s = "";

    if( !empty( $label ) ) {
        $s .= "$label:&nbsp;";
    }
    $s .= "<INPUT type=text name='$name' value='".$kfr->ValueEnt($name)."' size=$size $parmStr>";
    return( $s );
}

function KFRForm_TextTD( $kfr, $label, $name, $size = 50, $parmStr = "" )
/************************************************************************
 */
{
    return( "<TD valign='top'>$label</TD><TD valign='top'>".KFRForm_Text( $kfr, "", $name, $size, $parmStr )."</TD>" );

}

function KFRForm_TextArea( $kfr, $label, $name, $cols = 60, $rows = 5, $parmStr = "" )
/*************************************************************************************
 */
{
    $s = "";

    if( !empty( $label ) ) {
        $s .= "$label:&nbsp;";
    }
    $s .= "<TEXTAREA name='$name' cols='$cols' rows='$rows' $parmStr>".$kfr->ValueEnt($name)."</TEXTAREA>";
    return( $s );
}

function KFRForm_TextAreaTD( $kfr, $label, $name, $cols = 60, $rows = 5, $parmStr = "" )
/***************************************************************************************
 */
{
    return( "<TD valign='top'>$label</TD><TD valign='top'>".KFRForm_TextArea( $kfr, "", $name, $cols, $rows, $parmStr )."</TD>" );

}


function KFRForm_Option( $kfr, $name, $value, $label )
/*****************************************************
 */
{
    return( "<OPTION value='".htmlspecialchars($value,ENT_QUOTES)."'".($value==$kfr->Value($name) ? " SELECTED" : "").">$label</OPTION>" );
}

function KFRForm_Hidden( $kfr, $name )
/*************************************
    Remove the guesswork about how to escape HTML-unfriendly characters in an arbitrary string
 */
{
    return( "<INPUT type=hidden name='$name' value='".htmlspecialchars($kfr->value($name),ENT_QUOTES)."'>" );
}


function KFRForm_Select( $kfr, $name, $raValues, $raParms = array() )
/********************************************************************
    Draw a select control using $raValues = [val1=>label1, val2=>label2,...]
 */
{
    $s = "<SELECT name='$name' ".@$raParms['selectAttrs'].">";
    foreach( $raValues as $val => $label ) {
        $s .= KFRForm_Option( $kfr, $name, $val, $label );
    }
    $s .= "</SELECT>";
    return( $s );
}
function KFRForm_SelectTD( $kfr, $label, $name, $raValues, $raParms = array() )
/******************************************************************************
 */
{
    return( "<TD valign='top'>$label</TD><TD valign='top'>".KFRForm_Select( $kfr, $name, $raValues, $raParms )."</TD>" );
}

function KFRForm_Checkbox( $kfr, $name )
/***************************************
 */
{
    return( "<INPUT type=checkbox name='$name' value=1".($kfr->value($name) ? " CHECKED" : "").">" );
}

?>
