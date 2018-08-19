<?
/* Simple Application models for KFUI
 */

include_once( "KFUI.php" );


function KFUIApp_ListForm( &$kfdb, $kfrelDef, $kfuiDef, $uid = 0, $raParms = array() )
/*************************************************************************************
    Use this to find and manage data in a KFRelation.

    Draw a search control, a list control, and a form control for the given KFRelation/KFUI

    Usage: Instantiate a kfdb database connection
           Define a kfrel for your data relation
           Define a kfui with one component called "A" matching your kfrel, with a List and optional Form
           Specify a uid (integer) - this is written to _created_by and _updated_by

    raParms: kfLogFile = fully qualified filename where updates will be logged
             kfrelParms = array(parms passed to SetComponentKFRel)
             bReadonly = true:disallow updates
             fnDrawSearch = function to draw the Search control
             kfuiAppRowInit = a _key.  Initialize the list to select this row.
 */
{
    $kfrel = new KeyFrameRelation( $kfdb, $kfrelDef, $uid );
    $kfui = new KeyFrameUI( $kfuiDef );

    if( isset($raParms['kfLogFile']) )  $kfrel->SetLogFile( $raParms['kfLogFile'] );
    $bReadonly = @$raParms['bReadonly'];

    $kfui->InitUIParms();
    $kfui->SetComponentKFRel( "A", $kfrel, (is_array(@$raParms['kfrelParms']) ? $raParms['kfrelParms'] : array()) );
    if( !$bReadonly ) {
        $kfui->DoAction("A");
    }
    if( @$raParms['kfuiAppRowInit'] ) {
        $kfui->SetKey( "A", $raParms['kfuiAppRowInit'] );
        $kfui->klugeRowInit = $raParms['kfuiAppRowInit'];
    }
    if( @$raParms['fnDrawSearch'] ) {
        $raParms['fnDrawSearch']( $kfui );
    } else if( @$raParms['raUFlt'] ) {
        /* User filter(s) defined
         * We position these to the right of the standard search control.
         */
        echo "<TABLE border=0><TR><TD valign='top'>";
        $kfui->Draw( "A", "Search" );
        echo "</TD><FORM action='${_SERVER['PHP_SELF']}'>";
        foreach( $raParms['raUFlt'] as $uflt ) {
            echo "<TD valign='top'>".SEEDStd_StrNBSP("",20).$uflt['label']." "
                .SEEDForm_Select( $uflt['name'], $uflt['raValues'], $uflt['currValue'], array( "selectAttrs" => "onChange='submit();'" ) )
                ."</TD>";
        }
        echo "</FORM></TR></TABLE>";

    } else {
        $kfui->Draw( "A", "Search" );
    }
    $kfui->Draw( "A", "List" );
    echo "<TABLE border=1 width='100%'><TR>";
    if( $kfui->GetKey( "A" ) && !$bReadonly ) {
        echo "<TD valign='top'><BR>";
        $kfui->Draw( "A", "Controls" );
        echo "</TD>";
    }
    echo "<TD valign='top'>";
    $kfui->Draw( "A", "Form" );
    echo "</TD></TR></TABLE>";
}



/* only used by gcgcadmin

class KFUIApp_Class_Actiform {
    [* Usage: create a derived class that overrides the Draw and Action methods.
     *        KFUIApp fills in the variables and calls those methods
     *]
    var $kfui = NULL;   // filled in by KFUIApp
    var $kfuiCid = "";  // filled in by KFUIApp
    var $kfrel = NULL;  // filled in by KFUIApp

    function KFUIApp_Class_Actiform() {}

    function Init( $kfui, $cid )
    {
        $this->kfui = $kfui;
        $this->kfuiCid = $cid;
        $this->kfrel = $kfui->uiComps[$cid]->kfrel;
    }
    function Draw()   { echo "<DIV></DIV>"; }
    function Action() {}
}



function KFUIApp_ListReadOnly_Actiform( &$kfdb, $kfrelDef, $kfuiDef, $oActiform, $uid = 0, $raParms = array() )
[**************************************************************************************************************
    Use this to choose a row from a list, and perform actions on data external to the KFRelation.

    Draw a search control and a read-only list control for the given KFRelation/KFUI.
    Draw a client-defined form under the list, with client-defined action.

    Usage: Instantiate a kfdb database connection
           Define a kfrel for your data relation
           Define a kfui with one component called "A" matching your kfrel, with a List control
           Create an Actiform with overrides to define the Draw and Action methods.
           Specify a uid (integer) - this is written to _created_by and _updated_by

    raParms: kfLogFile = fully qualified filename where updates will be logged
             kfrelParms = array(parms passed to SetComponentKFRel)
             bReadonly = true:disallow updates
 *]
{
    $kfrel = new KeyFrameRelation( $kfdb, $kfrelDef, $uid );
    $kfui = new KeyFrameUI( $kfuiDef );

    if( isset($raParms['kfLogFile']) )  $kfrel->SetLogFile( $raParms['kfLogFile'] );

    $kui->InitUIParms();
    $kfui->SetComponentKFRel( "A", $kfrel, (is_array(@$raParms['kfrelParms']) ? $raParms['kfrelParms'] : array()) );
    [* This does not call $kfui->DoAction because the list is readonly mode (just as if $raParms['bReadonly'] were set)
     *]

    $oActiform->Init( $kfui, "A" );
    $oActiform->Action();
    $kfui->Draw( "A", "Search" );
    $kfui->Draw( "A", "List" );
    echo "<TABLE border=1 width='100%'><TR>";
//  if( $kfui->GetKey( "A" ) && !$bReadonly ) {
//      echo "<TD valign='top'><BR>";
//      $kfui->Draw( "A", "Controls" );
//      echo "</TD>";
//  }
    echo "<TD valign='top'>";
    $oActiform->Draw();
    echo "</TD></TR></TABLE>";
}

// Improved ListForm:
//  specify logfile
//  specify prefix(s) of user action parms (to be removed from the parm stream)
//  provide a way for Actiform to add a new row to the relation, with an update to kfui.
//  i.e. add row before kfui->InitUIParms and init kfui with the new key
//       or add the row later and reset kfui before drawing the UI.

*/

?>
