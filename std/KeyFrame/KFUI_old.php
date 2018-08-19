<?
/* UI controls for KeyFrame-enabled applications.
 */


/* Always use kfr_valueEnt() method to retrieve values in <FORM>.
 * This prevents single and double quotes from breaking the INPUT data fields.
 */

// ISSUE:  Any text that looks like a tag "<foo>" in INPUT fields is properly escaped by dPR_valueEnt(), but is
//         POSTed as unescaped text so it is stored in the db unescaped.  This means that it is invisible when echoed
//         into the list frame (though it is escaped again in the form field).
//         This is not a major problem unless we want tag-like things in our list-frame data.
//
//         I don't know what happens in TEXTAREA fields.  Put a tag <foo> there and see what it looks like in the db.
//         This is only an issue if those fields are shown without being htmlspecialchar'ed.
//         On the other hand, you probably want non-escaped tags in some TEXTAREA fields for formatting (?)

/* UI Manager for dbPhrameRecord-based database records.

   A client page sets up the frame definition and calls dbPhrameUI().
   The function echoes a frameset and sets up frames that reload the client page with specific parms.  Each frame
   is drawn differently, depending on the parms.
   All frame communication occurs through _top, which passes parms appropriately to child frames.


   Parms:
       _kfuirow      = _rowid of current row.  If blank, list has no highlight, form has no content.
       _kfuisortup   = name of column to sort ascending (only sent/used by list frames)
       _kfuisortdown = name of column to sort descending (only sent/used by list frames)
       _kfuilimit    = optional: list size.  This overrides ListSize in PhrameDef
       _kfuioff      = the sql offset to start the list.  Incompatible with _rflpos.  ListSize parm should be set in PhrameDef
       _kfuilpos     = the position (origin 1) to place the selected row in the displayed section of the list.  Incompatible with _rfoff


       _kfui       = 0 or "": echo a frameset whose frames recursively load the client page with specific _rf parms
                     1: echo a List frame
                     2: echo a Form frame

       _rf_colsel_{X} = current setting of the select control at the top of column {X}

       _rfaction   = insert | update | hide | delete | undelete (sent by form frames, used by _top frame)


       Search Tool:

       _kfsrchcol  = current column to search
       _kfsrchop   = operator for search (equal,like,start,end)
       _kfsrchval  = string to search for


    KeyFrameUI takes a KFUIDefinition:

        array( "Label"     => "Test Record",                        // the label used on the list and form controls
               "ListCols"  => array( array( "label"=>"Friend", "col"=>"name", "w"=>100 ),
                                     array( "label"=>"City",   "col"=>"city", "w"=> 50 ),
                                     array( "label"=>"Birth",  "col"=>"bday", "w"=> 50 )
                                   ),
               "ListURLPage"        => "url of the page where List links go.  Default = PHP_SELF",
               "ListURLTarget"      => "target of page where List links go.  Default = _top",

               "fnListFilter"       => "function that returns an SQL condition to filter the rows shown in list",
               "fnListTranslate"    => "function that returns an array of altered values per row",
             );



        -- SELECT table.name,table.bday FROM table WHERE city={N}

        array( "RelationType"    => "ChildSimple",                  // Simple table keyed on a fixed FK
               "RelationFKName"  => "city"
               "RelationFKValue" => N,                              // set by client before calling dbPhrameUI()
               "Label"           => "Test Record",
               "RecordDef"       => $tabledef1,
               "ListCols" => array( array( "label"=>"Friend", "col"=>"name",      "w"=>100 ),
                                    array( "label"=>"Birth",  "col"=>"bday",      "w"=> 50 )
                                  )
             );



        -- SELECT table.name,table.bday,cities.city_name FROM table,cities

        array( "RelationType"          => "Child",                  // Child table joined to a read-only Parent
               "RelationFKParentTable" => "cities",                 // the parent table
               "RelationFKName"        => "city"                    // the child's fk column
               "Label"                 => "Test Record",
               "RecordDef"             => $tabledef1,
               "ListCols" => array( array( "label"=>"Friend", "col"=>"name",      "w"=>100 ),
                                    array( "label"=>"City",   "col"=>"city_name", "w"=> 50, "rel"=>"parent" ),  // read-only values from the parent table
                                    array( "label"=>"Birth",  "col"=>"bday",      "w"=> 50 )
                                  )
             );


*/

include_once( "../BXStd.php" );
include_once( "../BXForm.php" );
include_once( "KFRecord.php" );
//include_once( "LoginAuth.php" );


if(!defined("SITEIMG_STDIMG"))  define("SITEIMG_STDIMG",STDIMG);


class KeyFrameUI {
/***************
 */
    // from constructor parms
    var $kfuiDef;
    var $kfRec;

    // List controls
    var $iListSelRowid;
    var $sListSortCol;
    var $bListSortdown;
    var $iListOffset;
    var $iListLimit;        // from kfuiDef / overridden by GPC
    var $iListSelPos;       // 0-base position in visible rows (position is offset+selpos)
    var $iListStatus;       // filter _status.  Default==0.
    var $sListUrlPage;
    var $sListUrlTarget;

    var $raListColSelects;  // filters for columns if any


//  var $raSearchToolCols;
    // GPC parms - external
//  var $userParms;     // all parms that are not dbPhrameUI internal control


    function KeyFrameUI( $kfuiDef, &$kfrel )
    /***************************************
     */
    {
        $this->kfuiDef = $kfuiDef;
        $this->kfRec   = &$kfRec;

        $this->iListSelRowid = 0;
        $this->sListSortCol = "";
        $this->bListSortdown = false;
        $this->iListOffset = 0;
        $this->iListLimit = 0;
        $this->iListSelPos = 0;
        $this->iListStatus = 0;
        $this->sListUrlPage   = (!empty($kfuiDef['ListURLPage'])   ? $this->kfuiDef['ListURLPage']   : $_SERVER['PHP_SELF']);
        $this->sListUrlTarget = (!empty($kfuiDef['ListURLTarget']) ? $this->kfuiDef['ListURLTarget'] : "_top");

        $this->raColSelects = array();


//      $this->raSearchToolCols = isset($raPhramedef['SearchToolCols']) ? $raPhramedef['SearchToolCols'] : array();
    }

    function KFUI_List( $parms )
    /***************************
     */
    {
        kfui_ParseParms( $parms );


        echo "<STYLE>";
        echo ".kfuiListRow0 { background-color: #eeeeee; font-size: 14; }";
        echo ".kfuiListRow1 { background-color: #cccccc; font-size: 14; }";
        echo ".kfuiListRow2 { background-color: #4444ff; font-size: 14; color: white; }";
        echo "</STYLE>";
        echo "<TABLE align=center><TR><TD>";
        echo "<TABLE><TR><TH>&nbsp;</TH>";
        foreach( $this->kfuiDef['ListCols'] as $col ) {
            /* The triangles have to be available in SITEIMG_STDIMG, which has to be defined and has to be under the webroot).
             */
            echo "<TH width='{$col['w']}' valign='top'>";
            echo "<A ".kfui_UrlHREF(0,$this->iListSelRowid,array("_kfuisortup" => $col['col'])).">";
            echo "<IMG src='".SITEIMG_STDIMG."triangle_blue_up".(($this->sListSortCol==$col['col'] && !$this->bListSortdown) ? "" : "_empty").".gif' border=0>";
            echo "</A><BR>";
            echo $col['label']."<BR>";
            echo "<A ".kfui_Url(0,$this->iListSelRowid,array("_kfuisortdown" => $col['col'])).">";
            echo "<IMG src='".SITEIMG_STDIMG."triangle_blue_down".(($this->sListSortCol==$col['col'] && $this->bListSortdown) ? "" : "_empty").".gif' border=0>";
            echo "</A>";

    // Kluge: this only works for straightforward columns of the primary table.  We don't have a way yet to enumerate the
    //        values of foreign columns
    // Also:  integrate the header search form into a standard filter method so we can merge filter parms of these forms.
            if( @$col['showSelect'] == 1 ) {
//              echo "<FORM method=get target='".$this->sListURLTarget."' onChange='submit();'>". kfui_HiddenFormParms(0,$this->iListSelRowid);
//              echo "<SELECT name='_rfcolsel_${col['col']}'>";
//              echo "<OPTION value='*'".($p->raColSelects[$col['col']]=='*' ? " SELECTED" : "")."></OPTION>";
//              $dbc = db_open( "SELECT DISTINCT({$col['col']}) FROM {$p->oRec->tablename} ORDER BY 1" );
//              while( $raSel = db_fetch( $dbc ) ) {
//                  echo "<OPTION value='${raSel[0]}'".($raSel[0]==$p->raColSelects[$col['col']] ? " SELECTED" : "").">${raSel[0]}</OPTION>";
//              }
                echo "</SELECT></FORM>";
            }
            echo "</TH>";
        }
        echo "</TR>";

        $raCond = array();

        foreach( $this->raListColSelects as $k => $v ) {
            /* Filter for column select lists.  '*' means do not filter
             */
            if( $v == '*' ) continue;
            $raCond[] .= $k."='".BXStd_MagicAddSlashes($v)."'";
        }

        if( !empty($this->kfuiDef['fnListFilter']) ) {
            $cond = $this->kfuiDef['fnListFilter']($this);
            if( !empty($cond) ) {
                $raCond[] .= "(".$cond.")";
            }
        }
        $cond = implode( " AND ", $raCond );
        //echo $cond;

        $i = 0;
        $kfrParms = array();
        if( !empty($this->sListSortCol) ) {
            $kfrParms['sSortCol'] = $this->sListSortCol;
            $kfrParms['sSortDown'] = $this->bListSortDown;
        }
        $kfrParms['iOffset'] = $this->iListOffset;
        $kfrParms['iLimit'] = $this->iListLimit;
        $kfrParms['iStatus'] = $this->iListStatus;

        $this->kfRec->kfr_CursorOpen( $cond, $kfrParms );
        while( $this->kfRec->kfr_CursorFetch() ) {
            if( $this->iListSelRowid && $this->kfrRec->kfr_Rowid() == $this->iListSelRowid ) {
                $nClass = 2;
            } else {
                $nClass = ($i%2);
            }

            echo "<TR id='r".$this->kfrRec->kfr_Rowid()."' class='kfuiListRow$nClass'>";
            echo "<TD><A ".kfui_UrlHREF(0,$this->kfrRec->kfr_Rowid())."><IMG src='".SITEIMG_STDIMG."dot1.gif' border=0></A></TD>";

            /* Optionally translate the row values
             */
            unset($xlatRow);
            if( !empty($this->kfuiDef['fnListTranslate'] ) ) {
                $xlatRow = $this->kfuiDef['fnListTranslate']($this);
            }

            foreach( $this->kfuiDef['ListCols'] as $col ) {
                echo "<TD>";
                if( isset($xlatRow) && array_key_exists($col['col'],$xlatRow) ) {
                    echo $xlatRow[$col['col']];
                } else {
                    echo $this->kfRec->kfr_Value($col['col']);
                }
                echo "</TD>";
            }
            echo "</TR>";
            $i++;
        }
        echo "</TABLE>";
        echo "</TD>";
        if( $this->iListLimit ) {
            // if using limited size list, show list nav buttons
    // It's very inefficient to compute nRows just so we can set a jump-to-the-bottom button that is rarely used.
    // Instead record an end-of-list flag at the cursor loop and use this to disable DOWN buttons,
    // and set the BOT link to _rflpos=BOT, which will cause the inefficient calculation to be performed only when necessary.
            $nRows = 1000;

            $offBot   = $nRows - $this->iListLimit + 1;         if( $offBot < 0 )       $offBot = 0;
            $offU     = $this->iListOffset - 1;                 if( $offU   < 0 )       $offU = 0;
            $offD     = $this->iListOffset + 1;                 if( $offD   > $offBot ) $offD = $offBot;
            $offPageU = $this->iListOffset - $this->iListLimit; if( $offPageU < 0 )     $offPageU = 0;
            $offPageD = $this->iListOffset + $this->iListLimit; if( $offPageD > $offBot )  $offPageD = $offBot;

            echo "<TD valign=top><BR>";
            echo "<BR><A ".kfui_Url(0,$this->iListSelRowid,array("_kfuioff"=>0)        ).">TOP</A>";
            echo "<BR><A ".kfui_Url(0,$this->iListSelRowid,array("_kfuioff"=>$offPageU)).">PAGE</A>";
            echo "<BR><A ".kfui_Url(0,$this->iListSelRowid,array("_kfuioff"=>$offU)    ).">UP</A><BR>";
            echo "<BR><A ".kfui_Url(0,$this->iListSelRowid,array("_kfuilpos"=>($p->iListLimit/2))).">SEL</A><BR>";
            echo "<BR><A ".kfui_Url(0,$this->iListSelRowid,array("_kfuioff"=>$offD)    ).">DOWN</A>";
            echo "<BR><A ".kfui_Url(0,$this->iListSelRowid,array("_kfuioff"=>$offPageD)).">PAGE</A>";
            echo "<BR><A ".kfui_Url(0,$this->iListSelRowid,array("_kfuioff"=>$offBot)  ).">BOT</A>";
            echo "</TD>";
        }
        echo "</TR></TABLE>";
    }


    function kfui_ParseParms( $parms, $isGPC = true )
    /************************************************
        Parse the input parms.

        Not implemented: any way to override, or provide parms in a non-GPC array
     */
    {
        $iListSelRowid = BXStd_SafeGPCGetInt('_kfuirow');

        if( !empty($_REQUEST['_kfuisortup']) ) {
            $this->sListSortCol = BXStd_SafeGPCGetStrPlain('_kfuisortup');
            $this->bListSortdown = false;
        } else if( !empty($_REQUEST['_rfsortdown']) ) {
            $this->sListSortCol = BXStd_SafeGPCGetStrPlain('_kfuisortdown');
            $this->bListSortdown = true;
        }
    }


    function kfui_Url( $mode, $selRowid, $raParms = array() )
    /********************************************************
        Encode the current/modified parms in a URL.

        Also passes along user parms.
     */
    {
        /* _rf and _rfrow are set to given parms.
         * _rfsort* and _rfaction might be set by $raParms
         * if raParms does not override current _rfsort*, set it using the stored value
         */

        /* Set the parms given on the argument list (override current state)
         */
        $url = $this->sListUrlPage."?_kfuimode=$mode";
        if( $selRowid )  $url .= "&_kfuirow=$selRowid";

        /* Set the var parms given (override current state)
         */
        foreach( $raParms as $k => $v ) {
            $url .= "&$k=".urlencode($v);
        }

        /* Set the current parms, if they have not been overridden
         */
        if( !empty($this->sListSortCol) && !strstr( $url, "_kfuisort" ) ) {
            $url .= "&_kfuisort".($this->bListSortdown ? "down" : "up")."=".$this->sListSortCol;    // assume no need to urlencode
        }
        if( $this->iListOffset && !strstr($url,"_kfuioff") )   $url .= "&_kfuioff=".$this->iListOffset;
        if( $this->iListSelPos && !strstr($url,"_kfuilpos") )  $url .= "&_kfuilpos=".$this->iListSelPos;
    // should also set _rflimit if $p->iListLimit > 0 and was set via _rflimit

//      foreach( $this->raColSelects as $k => $v ) {
//          $url .= "&_kfuicolsel_$k=$v";
//      }

//      foreach( $_REQUEST as $k => $v ) {
//          if( strncasecmp($k, "_kfsrch", 7) == 0 && !empty($v) ) {
//              $url .= "&$k=".urlencode($v);
//          }
//      }

        /* Pass along the user parms
         */
//      foreach( $p->userParms as $k => $v ) {
//          $url .= "&$k=".urlencode($v);
//      }
        return( $url );
    }


    function kfui_UrlHREF( $mode, $selRowid, $raParms = array() )
    /************************************************************
     */
    {
        echo "HREF='".kfui_Url($mode,$selRowid,$raParms)."' target='".$this->sListUrlTarget."'";
    }
}









exit;


function dbPhrameUI( $raPhramedef, $iUid )
/*****************************************
 */
{
    $iMode = BXStd_SafeGPCGetInt('_rf');
    $sAction = BXStd_SafeGPCGetStrPlain('_rfaction');

    $p = new dpui_Parms( $raPhramedef, $iUid );

    /* Parse $_REQUEST so that we can pass control and user parms to child frames
     * (except for _rfaction and _rf which are handled here and not passed along)
     */
    $p->iSelRowid   = BXStd_SafeGPCGetInt('_rfrow');
    $p->iListOffset = BXStd_SafeGPCGetInt('_rfoff');
    $p->iListSelPos = BXStd_SafeGPCGetInt('_rflpos');
//_rflimit can override ListSize, but it is not propagated in dpui_Url and dpui_HiddenFormParms because we haven't recorded where $p->iListLimit came from
    $p->iListLimit  = BXStd_SafeGPCGetInt('_rflimit');  if( !$p->iListLimit )  $p->iListLimit = intval(@$p->raPhramedef["ListSize"]);

    if( !empty($_REQUEST['_rfsortup']) ) {
        $p->sSortCol = BXStd_SafeGPCGetStrPlain('_rfsortup');
        $p->bSortdown = false;
    } else if( !empty($_REQUEST['_rfsortdown']) ) {
        $p->sSortCol = BXStd_SafeGPCGetStrPlain('_rfsortdown');
        $p->bSortdown = true;
    }

    foreach( $_REQUEST as $k => $v ) {
        if( strncasecmp($k, "_rfcolsel_", 10) == 0 && !empty($v) ) {
            $p->raColSelects[substr($k,10)] = $v;
        }
    }

    /* Collect user parms.  These are the parms that _top must pass to child frames and child frames must pass to _top.
     *
     * Get all parms from GET and POST.
     * Exclude the _rf* parms, because we handle them specifically.
     * If we are inserting/updating, exclude all parms that are Record fields.
     * Put the remaining parms in userParms.  These are:
     *      - custom control parms from the client page.  e.g. list filter parms
     *      - external control parms.  e.g. login, uid, session
     */
    $p->userParms = array();
    foreach( $_POST as $k => $v ) {
        if( strncasecmp( $k, "_rf", 3 ) == 0 )  continue;
        if( strncasecmp( $k, "_kf", 3 ) == 0 )  continue;
        if( $iMode == 0 && ($sAction == "insert" || $sAction == "update") && $p->oRec->dPR_IsField( $k ) ) {
            continue;
        }
        $p->userParms[$k] = BXStd_MagicStripSlashes($v);
    }
    foreach( $_GET as $k => $v ) {
        if( strncasecmp( $k, "_rf", 3 ) == 0 )  continue;
        if( strncasecmp( $k, "_kf", 3 ) == 0 )  continue;
        if( $iMode == 0 && ($sAction == "insert" || $sAction == "update") && $p->oRec->dPR_IsField( $k ) ) {
            continue;
        }
        $p->userParms[$k] = BXStd_MagicStripSlashes($v);
    }

    /* Process actions.  _rfaction should only be sent to the _top frame, and not propagated to child frames.
     */
    if( $iMode == 0 ) {
        switch( @$_REQUEST['_rfaction'] ) {
            case "insert":
                $p->oRec->dPR_GetFromArrayGPC( $_REQUEST );
// maybe the Relation* stuff should be in the dPR instead of the dPUI
                if( $p->raPhramedef['RelationType'] == "ChildSimple" ) {
                    $p->oRec->dPR_setValue( $p->raPhramedef['RelationFKName'], $p->raPhramedef['RelationFKValue'] );
                }
                $p->oRec->dPR_PutDBRow();
                $p->iSelRowid = $p->oRec->dPR_rowid();
                break;
            case "update":
                $p->oRec->dPR_GetDBRow( $p->iSelRowid );
                $p->oRec->dPR_GetFromArrayGPC( $_REQUEST, false );
                $p->oRec->dPR_PutDBRow();
                break;
            case "hide":
                db_exec( "UPDATE {$p->oRec->tablename} SET _status = (_status | 1) WHERE _rowid={$p->iSelRowid}" );
                $p->oRec->dPR_Clear();
                $p->iSelRowid = 0;
                break;
            case "delete":
                db_exec( "UPDATE {$p->oRec->tablename} SET _status = (_status | 2) WHERE _rowid={$p->iSelRowid}" );
                $p->oRec->dPR_Clear();
                $p->iSelRowid = 0;
                break;
            case "undelete":
                db_exec( "UPDATE {$p->oRec->tablename} SET _status = 0 WHERE _rowid={$p->iSelRowid}" );
                $p->oRec->dPR_Clear();
                $p->iSelRowid = 0;
                break;
        }
    }


    /* Draw the appropriate frame
     */
    switch( $iMode ) {
        case 1:     // echo a list form
            dpui_doFrameList( $p );
            break;
        case 2:     // echo a list form
            dpui_doFrameForm( $p );
            break;
        case 0:     // echo a frameset whose frames call this function recursively
        default:
            dpui_doFrameSet( $p );
            break;
    }
}



function dpui_doFrameSet( $p )
/*****************************
    Echo a frameset whose frames call this function with different _rf parms.
 */
{
    echo "<FRAMESET rows='50%,*' border='2' frameborder='yes' framespacing='0' resize='yes'>";
    echo "<FRAME name='rflist' src='".dpui_Url($p,1,$p->iSelRowid)."' border='0' frameborder='yes' framespacing='0' scrolling='yes'>";
    echo "<FRAME name='rfform' src='".dpui_Url($p,2,$p->iSelRowid)."' border='0' frameborder='yes' framespacing='0' scrolling='yes'>";
    echo "</FRAMESET>";
    echo "<noframes><P>This page uses frames.</P></noframes>";
}


function dpui_doFrameList( $p )
/******************************
    Echo a list frame
 */
{
}


function dpui_doFrameForm( $p )
/******************************
    Echo a form frame
 */
{
    if( $p->iSelRowid )  $p->oRec->dPR_GetDBRow( $p->iSelRowid );

    echo "<TABLE cellpadding=20 border=1 align=center><TR>";
    if( $p->iSelRowid ) {
        echo "<TD valign='top' align=left>";
        echo "<P><A HREF='".dpui_Url($p,0,0)."' target='_top'>New {$p->raPhramedef['Label']}</A></P>";
//     echo "<P>Edit {$p->raPhramedef['Label']}</P>";
        echo "<P><A HREF='".dpui_Url($p,0,$p->iSelRowid,array("_rfaction" => "delete"))."' target='_top'>Delete this {$p->raPhramedef['Label']}</A></P>";
        echo "<BR><DIV style='font-size:10; font-family:arial,helvetica,sans-serif;'>";
        echo "<BR>Record created by<BR>".LoginAuth_GetUserName($p->oRec->dPR_value('_created_by'))."<BR>[".$p->oRec->dPR_value('_created')."]<BR>";
        echo "<BR>Last updated by<BR>".LoginAuth_GetUserName($p->oRec->dPR_value('_updated_by'))."<BR>[".$p->oRec->dPR_value('_updated')."]</DIV>";
        echo "</TD>";
    }
    echo "<TD valign='top'>";
    echo "<FORM target='_top' action='{$_SERVER['PHP_SELF']}' method=post>";
    echo "<TABLE width=100%><TR><TD valign=top>";
    if( $p->iSelRowid ) {
        echo "<H2>Edit {$p->raPhramedef['Label']}</H2>";
        echo dpui_HiddenFormParms( $p, 0, $p->iSelRowid, array("_rfaction" => "update") );
    } else {
        echo "<H2>Enter new {$p->raPhramedef['Label']}</H2>";
        echo dpui_HiddenFormParms( $p, 0, 0, array("_rfaction" => "insert") );
    }
    echo "</TD><TD valign=top><INPUT type=submit value=Save></TD></TR></TABLE>";
    $p->raPhramedef['fnFormDraw']($p->oRec);
    echo "<INPUT type=submit value=Save>";
    echo "</FORM>";
    echo "</TD></TR></TABLE>";
}




function dpui_HiddenFormParms( $p, $mode, $selRowid, $raParms = array(), $raExcludeUserParms = array() )
/*******************************************************************************************************
    Same as dpui_Url but for Forms.
    Return a string of INPUT-hidden fields that retain the current $_REQUEST modified by given parms

    Pass along or modify the _rf* parms first, then pass along the user parms.
    $raParms is a list of name=value pairs to include/override.
    $raExcludeUserParms is a list of parm names to exclude
 */
{
    /* _rf and _rfrow are set to given parms.
     * _rfsort* and _rfaction might be set by $raParms
     * if raParms does not override current _rfsort*, set it using the stored value
     */
    $s = "<INPUT type=hidden name='_rf' value='$mode'>";
    if( $selRowid )  $s .= "<INPUT type=hidden name='_rfrow' value='$selRowid'>";
    foreach( $raParms as $k => $v ) {
        $s .= BXFormHiddenStr( $k, $v );
    }
    if( !empty($p->sSortCol) && !strstr( $s, "_rfsort" ) ) {
        $s .= "<INPUT type=hidden name='_rfsort".($p->bSortdown ? "down" : "up")."' value='".$p->sSortCol."'>";
    }
    if( $p->iListOffset && !strstr($s,"_rfoff") ) {
        $s .= "<INPUT type=hidden name='_rfoff' value='".$p->iListOffset."'>";
    }
    if( $p->iListSelPos && !strstr($s,"_rflpos") ) {
        $s .= "<INPUT type=hidden name='_rflpos' value='".$p->iListSelPos."'>";
    }
    foreach( $p->raColSelects as $k => $v ) {
        $s .= "<INPUT type=hidden name='_rfcolsel_$k' value='$v'>";
    }
// should also set _rflimit if $p->iListLimit > 0 and was set via _rflimit

    foreach( $_REQUEST as $k => $v ) {
        if( strncasecmp($k, "_kfsrch", 7) == 0 && !empty($v) ) {
            $s .= BXFormHiddenStr( $k, $v );
        }
    }


    /* Pass along the user parms
     */
    foreach( $p->userParms as $k => $v ) {
        if( in_array( $k, $raExcludeUserParms ) )  continue;
        $s .= BXFormHiddenStr( $k, $v );
    }
    return( $s );
}


function dbPhrameUI_User_HiddenFormParms( $p, $parms = array(), $raExcludeUserParms = array() )
/**********************************************************************************************
    The Public wrapper for HiddenFormParms

    $parms:
        "keepSel" =>  true: keep the current iSelRowid,iListOffset,iListSelPos;  false: lose selection

    $raExcludeUserParms: array("p1","p2","p3",...)
        do not propagate these user parameters (e.g. they are set by controls in the user form)
 */
{
    $bKeepSel = @$parms['keepSel'];

    $raCtrlParms = array();
    if( !$bKeepSel ) {
        // cancel any offset, listpos
        $raCtrlParms['_rfoff'] = "0";
        $raCtrlParms['_rflpos'] = "0";
    }

    return( dpui_HiddenFormParms( $p, 0, $bKeepSel ? $p->iSelRowid : 0, $raCtrlParms, $raExcludeUserParms ) );
}

function dbPhrameUI_formINPUT( $dPRec, $label, $field, $size = 50 )
/******************************************************************
 */
{
    $s = "";

    if( !empty( $label ) ) {
        $s .= "$label:&nbsp;";
    }
    $s .= "<INPUT type=text name='$field' value='".$dPRec->dPR_valueEnt($field)."' size=$size>";
    return( $s );
}

function kfUI_formOption( $dPRec, $field, $value, $label )
/*********************************************************
 */
{
    return( "<OPTION value='$value'".($value==$dPRec->dPR_value($field) ? " SELECTED" : "").">$label</OPTION>" );
}


// this can be quite independent of dbPhrame (doesn't use $p)
function dbPhrameUI_headerSearch( $p, $userParm, $size = 30 )
/************************************************************
    Implement a search tool in the header.
    1) Call this function in the header somewhere, choosing a unique url-parm name for $userParm.
    2) Mention the $userParm name in dbPhrameUI_User_HiddenFormParms(...,raExcludeUserParms,...) in the header code since we create the control here.
    3) Get $userParm from GPC in fnRowFilter code and use it to construct the filter (we don't help you with this step at all)

 */
{
    $sSearch = BXStd_SafeGPCGetStrPlain( $userParm );
    echo "Search: <INPUT type=text name=$userParm value='".htmlspecialchars($sSearch,ENT_QUOTES)."' size=$size> ";
}


/* =================
 *    SEARCH TOOLS
 * =================
 */

function KeyFrameUI_SearchTool( $p )
/***********************************
    Output the Search Tool controls.  Call this within a FORM.
 */
{
    $col = BXStd_SafeGPCGetStrPlain("_kfsrchcol");
    $op  = BXStd_SafeGPCGetStrPlain("_kfsrchop");
    $val = BXStd_SafeGPCGetStrPlain("_kfsrchval");

    echo "Search ";
    echo "<SELECT name=_kfsrchcol>";
    echo "<OPTION value=''>Any</OPTION>";
    foreach( $p->raSearchToolCols as $ra ) {
        echo BXFormOption( $ra['col'], $ra['label'], $col );
    }
    echo "</SELECT>";

    echo "&nbsp;&nbsp;<SELECT name=_kfsrchop>";
    echo BXFormOption( "like",  "contains",    $op );
    echo BXFormOption( "eq",    "equals",      $op );
    echo BXFormOption( "start", "starts with", $op );
    echo BXFormOption( "end",   "ends with",   $op );
    echo "</SELECT>";

    echo "&nbsp;&nbsp;<INPUT type=text name=_kfsrchval value='".htmlspecialchars($val,ENT_QUOTES)."'>";
}

function KeyFrameUI_SearchToolGetDBCond( $p )
/********************************************
 */
{
    $cond = "";

    $col    = BXStd_SafeGPCGetStrPlain("_kfsrchcol");
    $op     = BXStd_SafeGPCGetStrPlain("_kfsrchop");
    $tmp    = BXStd_SafeGPCGetStr("_kfsrchval");
    $val_db = $tmp['db'];

    if( !empty($val_db) ) {
        $cond = "(";
        if( empty($col) ) {
            $bFirst = true;
            foreach( $p->raSearchToolCols as $ra ) {
                if( $bFirst ) {
                    $bFirst = false;
                } else {
                    $cond .= " OR ";
                }
                $cond .= _kfUI_SearchToolDBCondTerm( $ra['col'], $op, $val_db );
            }
        } else {
            $cond .= _kfUI_SearchToolDBCondTerm( $col, $op, $val_db );
        }
        $cond .= ")";
    }
    return( $cond );
}

function _kfUI_SearchToolDBCondTerm( $col, $op, $val_db )
{
    $s = $col." ".($op=="eq" ? "=" : "like")." ";
    $s .= "'";
    if( $op == "like" || $op == "end" )  $s .= "%";
    $s .= $val_db;
    if( $op == "like" || $op == "start" )  $s .= "%";
    $s .= "'";
    return( $s );
}

?>
