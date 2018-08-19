<?
/*  UI controls for KeyFrame-enabled applications.

    Every component that can issue a form submission resides in its own form, and propagates the state information
    of every component. This is necessary because each component can conceivably be in a different html frame, so no
    form element can necessarily span more than one component.


    Parameters are divided into three categories:

        UI Parms:       Identified by the prefix _kfu{$cid}_
                        Control the given UI component
                        Each component issues changed parms as necessary, and reissues all other components' parms
                            unchanged, except "action"

        Update Parms:   Identified by the prefix _kfu{$cid}p_
                        Coded into all form-input controls
                        Collected into a separate namespace per component and processed by DoAction( $cid )
                        Never reissued
                        Client code must call DoAction($cid) at a suitable place (before db reads are performed)

        User Parms:     All other parms
                        Collected into a common namespace of the UI
                        Can be added / removed / changed by client code
                        Always reissued (with possible client alteration)




        UIParms:









    N.B. Always use kfr->ValueEnt() method to retrieve values in <FORM>.
         This prevents single and double quotes from breaking the INPUT data fields.

   A client page sets up the frame definition and calls dbPhrameUI().
   The function echoes a frameset and sets up frames that reload the client page with specific parms.  Each frame
   is drawn differently, depending on the parms.
   All frame communication occurs through _top, which passes parms appropriately to child frames.


   Parms:
       _kfuirow      = _key of current row.  If blank, list has no highlight, form has no content.
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


    KeyFrameUIList takes a KFUIListDefinition:

        array( "Name"      => "A",
               "Label"     => "Test Record",                        // the label used on the list and form controls
               "ListCols"  => array( array( "label"=>"Friend", "col"=>"name", "w"=>100 ),
                                     array( "label"=>"City",   "col"=>"city", "w"=> 50 ),
                                     array( "label"=>"Birth",  "col"=>"bday", "w"=> 50 )
                                   ),

               "ListSize"           => limits the size of the list - default is no limit
               "ListSizePad"        => boolean: draw empty rows at bottom of list to pad it to ListSize
               "ListURLPage"        => "url of the page where List links go.  Default = PHP_SELF",
               "ListURLTarget"      => "target of page where List links go.  Default = _top",
               "ListStatus"         => specifies the _status of rows to retrieve

               "fnListFilter"       => function that returns an SQL condition to filter the rows shown in list
                                       User must use addslashes() around unqualified values, since this goes to the db

               "fnListTranslate"    => "function that returns an array of altered values per row",

               "fnFormDraw"         => function that draws the form - default is a basic field-value form

               // Array of fields to include in the search tool (if not defined, all cols in the kfrel are included)
               "SearchToolCols"     => array( "label1"=>"col1", "label2"=>"col2", ...)

               "sFormTarget"        => target of form submit (a window name) - useful for multi-frame apps
               "sFormAction"        => action of form submit (a page name) - default is PHP_SELF
               "sFormMethod"        => method of form submit - default is POST

               "sSearchTarget"      => target of search tool submit (a window name) - useful for multi-frame apps
               "sSearchAction"      => action of search tool submit (a page name)
               "sSearchMethod"      => method of search tool submit - default is POST

               // Array of foreign key relationships to be set when a new row is inserted.
               // The fk_tablename is set to the current value of GetKey('cid')
               // This is the easiest way to define a parent relationship, if the parent is an active component
               "fkDefaults"         => array( "tablename1"=>"cid1", "tablename2"=>"cid2", ...)

               // Array of components whose lists and selection states are dependent on this component's selection.
               // When this component's selection changes, the dependents are cleared.
               "raDependents"       => array( "cid1", "cid2", ... )


               "DisallowNonPrefixedBaseFieldsInGPC" => 1 (default=0) By default, form fields don't need kfui prefixes.
                                        Non-prefixed base-table field names will be admitted to the updateParms (for all components),
                                        and removed from the subsequent parm stream in outgoing links and form data.
                                        By setting this option, only kfui{}p_* parms will be admitted to updateParms,
                                        and all non-prefixed parms will be propagated to the outgoing parm stream.
                                        i.e. we let you make easy forms by default, but you have to be careful that any
                                        "third-party" field names don't conflict with table field names, and if you have
                                        multiple components, they need unique field names. By setting the option, you have
                                        to prefix your form names correctly, but the system prevents name conflicts.
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


include_once( STDINC."BXForm.php" );
include_once( "KFRelation.php" );


if(!defined("SITEIMG_STDIMG"))  define("SITEIMG_STDIMG",STDIMG);


function KFUI_MagicAddSlashes( $s )   { return( get_magic_quotes_gpc() ? $s : addslashes($s) ); }
function KFUI_MagicStripSlashes( $s ) { return( get_magic_quotes_gpc() ? stripslashes($s) : $s ); }


class KeyFrameUI {
/***************
 */

    var $uiComps = array();     // array of $cid=>kfuiComponent.  The uiDef for each component is copied here.
    var $userParms = array();   // unrecognized parms to be reissued transparently
    var $kfuiParms = array();   // parms that apply across kfui (not just to components)

var $klugeRowInit = 0; // if this is set, the list won't do an MD5 refresh. Used by kluged clients who SetKey a kfuiComp before Draw.
                       //     There has to be a better way to do a kfuiAppRowInit in KFUIAppSimple.
    /* Requirements:
            Given a key and offset, just show the list at the offset (key row might not be visible).
            Given a key and showkey command, position the key row in the middle of the list.
            Given an insert command, draw the list according to current state, but prepend the new row at top if not shown otherwise
            Store state so kfui can detect a change in state, select first row in list, and clear offset.  e.g. when Search parm changes
            Detect initial page - select first row in list.
            Detect a request for an Insert form - don't change the list state, but don't select any rows in the list.

            If this is initial (i.e. MD5 is not in parm stream)
                if New Row parm given -> uiact = New Row
                else if Row Init parm given -> set key = Row Init, uiact = Init Row
                else uiact = Reset
            else
                if state changed -> uiact = Reset
                else uiact = Normal

            switch( uiact )
                New Row: set key = 0, show New form
                Row Init: set key = Row Init
                Reset: offset = 0, set key = first row in list
                Normal: (none of the above)
            propagate to the next page the MD5 of the state




        View state: the filters on the relation
        UI state:   the parms that affect display of the view (sorting, offset, limit)
                    the current row
                    the current UI mode (new row, delete, etc)


        UImode: New - deselect the current row in the list, show the New Row form
                Set{key} - set the given row as current, show it
                Reset[{key}] - clear the ui state, set the given row and show it, if no {key} show first row in list
                [Normal] - just draw the list according to UI state

        So _kfuAuiaction can be New, SetKKKK, Reset[KKKK], and others
           _kfuAaction can be insert (with Update Parms), update (with Update Parms), delete, hide, unhide.
                An insert invokes uiaction=Set{new row}, delete can be combined with uiaction=Set{next row in list}

        uimode can also be set by a uiComp method prior to Draw.

        A new instance of kfui will do a Reset by default.  An option can change the default init behaviour to New.


        UIComp Init:
            uimode = Normal
            if( _kfu{cid}um is set ) {
                uimode = that
            } else {
                if( md5 not in parm stream ) {
                    uimode = default uimode_on_init (Reset unless an option overrides it)
                }
            }


        When MD5 missing, generate a unique namespace for storing UIstate parms in the session. This ensures window uniqueness.
        The MD5 and namespace have to be propagated in the parm stream. They are not the same thing, because namespace is regenerated
        whenever MD5 is missing, not when it changes.

        Use a factory method in kfui to make kfuiComponents.  This can be overridden by kfui derivatives, to make derived kfuiComponents.
        Then kfuiComponent can be public.


        Focusing a new row in the list probably means a two-step fetch - one to get the row, the other to get the currently visible part of the list.
        Probably the nicest way is to fetch both, then see if the new-row key is in the regular list - if so, just show the regular list, else prepend
        the new row at top and delete the bottom row to make a new list.  Then draw the list as usual.
        This fetch-first, draw-later method should be done anyway because it can be adapted to multiple draw methods e.g. XLS
     */

    function KeyFrameUI( $kfuiDef )
    /******************************
     */
    {
        foreach( $kfuiDef as $cid => $uiDef ) {
            if( $cid == "Parms" ) {
                $this->kfuiParms = $uiDef;
            } else {
                // the kfuiDef can be extended by testing here for $cid != 'A'..'Z'
                /*** Removing the & is a problem on php4 because kfuiComponent creates kfuiCurrRow, which stores kfuiComponent, but when this returns the kfuiCurrRow
                 *** doesn't seem to have a valid kfuiComponent any more.  All storage is by reference, but something is discarded when this new is stored without a &.
                 ***/
                $this->uiComps[$cid] = new kfuiComponent( $this, $cid, $uiDef );
            }
        }
    }


    function GetKey( $cid )
    /**********************
     */
    {
        return( intval(@$this->uiComps[$cid]->kfuiCurrRow->GetKey() ) );
    }

    function SetKey( $cid, $k )
    /**************************
        Use this carefully: to force focus to a different row, after InitUIParms, but before Draw.
        e.g. if an application creates a new row immediately after InitUIParms
     */
    {
        return( $this->uiComps[$cid]->kfuiCurrRow->SetKey( $k ) );
    }

    function GetKFR( $cid )
    /**********************
     */
    {
        return( $this->uiComps[$cid]->kfuiCurrRow->GetKFR() );
    }

    function ReloadKFR( $cid )
    /*************************
        The kfr in kfuiCurrRow can become out of sync if operations are done on a reference to it obtained by GetKFR().
        Operations performed on such a reference seem to affect the internal kfr correctly in PHP5.
        Not so PHP4, which changes some copy of the kfr and not the real one.
     */
    {
        return( $this->SetKey( $cid, $this->GetKey($cid) ) );
    }


    function GetValue( $cid, $field )
    /********************************
     */
    {
        return( $this->uiComps[$cid]->kfuiCurrRow->GetValue($field) );
    }


    function SetComponentKFRel( $cid, &$kfrel, $parms = array() )
    /************************************************************
     */
    {
        $this->uiComps[$cid]->kfrel =& $kfrel;
        $this->uiComps[$cid]->kfrelParms = $parms;
    }


    function SetComponentParm( $cid, $k, &$v, $op = "" )
    /***************************************************
     */
    {
        if( $op == "ref" ) {
            $this->uiComps[$cid]->uiDef[$k] =& $v;     // store a reference
        } else {
            $this->uiComps[$cid]->uiDef[$k] = $v;      // store a copy
        }
    }

    function DrawHiddenFormParms( $cid )
    /***********************************
No idea whether this works in all cases.  Added this to fix bug in the GCGC Admin, which was not preserving kfui state in the actiform.
This is naturally needed for any user form, but it will probably usually be done conveniently in a KFUIApp helper function.
     */
    {
        return( $this->uiComps[$cid]->HiddenFormParms( $this->GetKey( $cid ) ) );
    }

/*
 *    function RegisterUIComponent( $cid, $kfuiCompDef, &$kfrel )
 *    [**********************************************************
 *        $cid is a one-character identifier for the component
 *     *]
 *    {
 *        $this->uiDef[$cid]["def"] = $kfuiCompDef;      // make a copy, in case we change it internally
 *        $this->uiDef[$cid]["kfrel"] = &$kfrel;
 *        $this->uiDef[$cid]["uiParms"] = array();
 *        $this->uiDef[$cid]["updateParms"] = array();
 *
 *        if( $kfuiCompDef["type"] == "List" ) {
 *            $this->uiComps[$cid]["obj"] = new KFUIList( $cid, $kfuiCompDef, $this );
 *        }
 *    }
 */

    function InitUIParms( $parms = NULL, $isGPC = true )
    /***************************************************
     */
    {
        if( !$parms )  $parms = $_REQUEST;
        foreach( $parms as $k => $v ) {
            if( $isGPC ) {
                $v = KFUI_MagicStripSlashes( $v );
            }

            // _kfuA_* go in $this->uiComps["A"]->uiParms["*"] (remove the prefix)
            // _kfuAp_* go in $this->uiComps["A"]->updateParms["*"] (remove the prefix) for processing when _kfuA_action is performed
            if( substr( $k, 0, 4 ) == "_kfu" ) {
                $cid = substr( $k, 4, 1 );

                if( array_key_exists( $cid, $this->uiComps ) ) {
                    // This is a silly place to put this code - should be a method in kfuiComponent that marshalls parms
                    if( substr( $k, 5, 8 ) == "_colsel_" ) {
                        $this->uiComps[$cid]->uiParmsListColSelects[substr($k,13)] = $v;
                    } else if( substr( $k, 5, 6 ) == "_srch_" ) {
                        if( !empty($v) )  $this->uiComps[$cid]->uiParmsSearch[substr($k,11)] = $v;
                    } else if( substr( $k, 5, 1 ) == "_" ) {
                        $this->uiComps[$cid]->uiParms[substr($k,6)] = $v;
                        if( substr( $k, 5, 4 ) == "_row" ) {
                            $this->uiComps[$cid]->bNewRow = ($v ? false : true);    // if _row explicitly set to 0, make a new row, else list shows first row
                            $this->uiComps[$cid]->kfuiCurrRow->SetKey($v);
                        }
                    } else if( substr( $k, 5, 2 ) == "p_" ) {
                        $this->uiComps[$cid]->updateParms[substr($k,7)] = $v;
                    } else {
                        echo "<BR/>Error: invalid parameter $k<BR/><BR/>";
                    }
                } else {
                    // unknown component - could be used by another kfui in the system.  Treat like userParms.
                    $this->userParms[$k] = $v;
                }

            } else {
                $this->userParms[$k] = $v;
            }
        }

        /* Set default colsels - this should also be in each component instead of here
         */
        foreach( $this->uiComps as $cid => $v ) {
            foreach( $v->uiDef['ListCols'] as $col ) {
                if( @$col['showColsel'] && @$col['colselDefault'] && !@$this->uiComps[$cid]->uiParmsListColSelects[$col['alias']] ) {
                    $this->uiComps[$cid]->uiParmsListColSelects[$col['alias']] = $col['colselDefault'];
                }
            }
        }
    }

    function DoAction( $cid, $raParms = array() )
    /********************************************
        Perform the action (if any) specified by _kfu${name}_action.  i.e. $this->uiComps["A"]["uiParms"]["action"]
        InitUIParms() must be called before this function.

        raParms:
            fkDefaults = array of tablename=key to preload fk values of the relation - used only if the row is new (insert)
     */
    {
        $uiComp  =& $this->uiComps[$cid];
        $uiParms =& $uiComp->uiParms;


        switch( @$uiParms['action'] ) {
            case "insert":
                /* There are three ways to set default foreign keys.
                 * 1. In uiDef, "fkDefaults"=>array("table_name"=>"cid",...)
                 *    This connects fk_table_name to the current key of component cid.
                 *    Easiest way to set a parent dependency.
                 *
                 * 2. Put an explicit fk_table_name in the http parms, using a hidden form field.
                 *
                 * 3. SetComponentKFRel(... $parms = array( "fkDefaults" => array( "table_name", int ) )
                 *    This allows the client to specify a default fk_table_name value in the component set up.
                 *
                 * The first methods override the later methods.
                 */
                $kfr = $uiComp->kfrel->CreateRecordFromRA( $uiComp->updateParms, false, @$uiComp->kfrelParms['fkDefaults'] );
                if( isset($uiComp->uiDef['fkDefaults'] ) ) {
                    foreach( $uiComp->uiDef['fkDefaults'] as $fkTable => $fkCid ) {
                        $kfr->SetValue( "fk_".$fkTable, $uiComp->kfui->GetKey($fkCid) );
                    }
                }

// InitUIParms should find non-prefixed base values, put them in updateParms for every component, and exclude them from userParms (unless the option is set)
if( !@$uiComp->uiDef['DisallowNonPrefixedBaseFieldsInGPC'] ) {
    $kfr->UpdateBaseValuesFromRA( $this->userParms );
}
                $bPut = true;
                if( isset($uiComp->uiDef['fnPreStore']) ) {
                    $bPut = $uiComp->uiDef['fnPreStore']( $kfr );
                }
                if( $bPut )  $kfr->PutDBRow();

                // set new key in component
                $uiParms['row'] = $kfr->Key();
                $uiComp->kfuiCurrRow->SetKey( $kfr->Key() );
                $uiComp->bFocusKeyRowInList = true;
                break;

            case "update":
                if( $uiComp->kfuiCurrRow->GetKey() ) {
// Could use the uiComp->kfuiCurrRow instead of making a new one.
// Also, would be good to use the kfuiCurrRow, because it should be updated with the new values too
                    $kfr = $uiComp->kfrel->GetRecordFromDBKey( $uiComp->kfuiCurrRow->GetKey() );
                    $kfr->UpdateBaseValuesFromRA( $uiComp->updateParms );
// InitUIParms should find non-prefixed base values, put them in updateParms for every component, and exclude them from userParms (unless the option is set)
if( !@$uiComp->uiDef['DisallowNonPrefixedBaseFieldsInGPC'] ) {
    $kfr->UpdateBaseValuesFromRA( $this->userParms );
}
                    $bPut = true;
                    if( isset($uiComp->uiDef['fnPreStore']) ) {
                        $bPut = $uiComp->uiDef['fnPreStore']( $kfr );
                    }
                    if( $bPut )  $kfr->PutDBRow();
                }
                break;
            case "hide":
                db_exec( "UPDATE {$p->oRec->tablename} SET _status = (_status | 1) WHERE _key={$p->iSelKey}" );
                $p->oRec->dPR_Clear();
                $p->iSelKey = 0;
                break;
            case "delete":
                if( $uiComp->kfuiCurrRow->GetKey() ) {
                    $t = $uiComp->kfrel->baseTable['Table'];
                    $k = $uiComp->kfuiCurrRow->GetKey();
                    $uiComp->kfrel->kfdb->KFDB_Execute( "UPDATE $t SET _status = (_status | 2) WHERE _key=$k" );
                    $uiComp->kfuiCurrRow->Clear();
                //$p->iSelKey = 0;
                }
                break;
            case "undelete":
                db_exec( "UPDATE {$p->oRec->tablename} SET _status = 0 WHERE _key={$p->iSelKey}" );
                $p->oRec->dPR_Clear();
                $p->iSelKey = 0;
                break;
        }
    }

    function Draw( $cid, $widgetType = "", $parms = array() )
    /********************************************************
     */
    {
        switch( $widgetType ) {
            case "List":    $this->uiComps[$cid]->drawList( $parms );    break;
            case "Form":    $this->uiComps[$cid]->drawForm( $parms );    break;
            case "Controls":$this->uiComps[$cid]->drawControls( $parms );break;
            case "Search":  $this->uiComps[$cid]->drawSearch( $parms );  break;
            default:
                // Implement a way for the client to set drawing extensions in the kfuiDef
                break;
        }

/*
 *        if( $this->uiComps[$cid]["obj"] ) {
 *            $this->uiComps[$cid]["obj"]->Draw();
 *        } else {
 *            echo "<P>No object $cid</P>";
 *        }
 */
    }


    function GetUserName( $uid )
    /***************************
        The client can specify a function to return the user name, or it can override this method in a derived class.
     */
    {
        return( isset($this->kfuiParms['fnGetUserName']) ? ($this->kfuiParms['fnGetUserName']( $uid ))
                                                         : $uid );
    }

    function FormInput( $a, $label, $field, $size = 50 )
    // $a has to tell us the Record, so we can set the value, and it has to tell us the component
    // so we can prepend _kfu{A}p_ to the field name
    {
    }

}


class kfuiComponent
/******************
    Used internally by KeyFrameUI.
    The KeyFrameUI makes one of these for each component defined in the kfuiDef.

    Each uiComp has a "current row", which may be zero.
    The UI should define one uiComp for each independently active relation.
 */
{
    var $kfui;                              // recursive reference back to the owner object
    var $cid;                               // this component's name
    var $uiDef;                             // copy of the portion of the kfuiDef for this component
    var $kfrel = NULL;                      // reference to KeyFrameRelation for this component
    var $kfrelParms = array();              // parms such as fkDefaults that relate to the kfrel for this component
    var $kfuiCurrRow;                       // object that manages the current row of this component
    var $uiParms = array();                 // array( _kfu* parms for this component )
    var $uiParmsListColSelects = array();   // array( _kfuX_colsel_* parms for this component )
    var $uiParmsSearch = array();           // array( _kfuX_srch_* parms for this component )
    var $updateParms = array();             // array( form data parms for this component )
    var $condMD5 = "";
    var $bNewRow = false;                   // true if kfu?_row=0 : this is the explicit way to create a new row. Otherwise list sets CurrRow to first row.
    var $bFocusKeyRowInList = false;        // set to true to force the key row to be shown in the list

    // internal parms used during processing - deleted before/after each draw
    var $currParms = array();


// InitUIParms should send all uiParms to a local marshalling function to get uiParms*, currRow key (which should be constant intval of uiParms['row']


    function kfuiComponent( &$kfui, $cid, $uiDef )
    /*********************************************
     */
    {
        $this->kfui  =& $kfui;
        $this->cid   = $cid;
        $this->uiDef = $uiDef;  // copy
        $this->kfuiCurrRow = new kfuiCurrRow( $this );
    }


    function drawList( $parms )
    /**************************
        Draw a list box for this component's relation, controlled by uiParms
     */
    {
// Kluge: ListCols should use alias instead of col to refer to the columns
//        Old uiDefs use col, so translate them here, use alias in the code.
for( $i = 0; $i < count($this->uiDef['ListCols']); ++$i ) {
    if( empty($this->uiDef['ListCols'][$i]['alias']) )
        $this->uiDef['ListCols'][$i]['alias'] = $this->uiDef['ListCols'][$i]['col'];
}


        $this->currParms = array();
        $this->currParms['sListUrlPage']   = (!empty($this->uiDef['ListURLPage'])   ? $this->uiDef['ListURLPage']   : $_SERVER['PHP_SELF']);
        $this->currParms['sListUrlTarget'] = (!empty($this->uiDef['ListURLTarget']) ? $this->uiDef['ListURLTarget'] : "_top");

        if( !empty($this->uiParms['sortup']) ) {
            $this->currParms['sListSortCol'] = $this->uiParms['sortup'];
            $this->currParms['bListSortDown'] = false;
        } else if( !empty($this->uiParms['sortdown']) ) {
            $this->currParms['sListSortCol'] = $this->uiParms['sortdown'];
            $this->currParms['bListSortDown'] = true;
        } else {
            $this->currParms['sListSortCol'] = "";
            $this->currParms['bListSortDown'] = false;
        }

        $this->currParms['iListOffset'] = intval(@$this->uiParms['off']);
        $this->currParms['iListSelPos'] = intval(@$this->uiParms['lpos']);
        // iLimit is normally defined in uiDef, but it can be overridden by uiParms
        $this->currParms['iListLimit']  = intval(@$this->uiParms['limit']);
        if( !$this->currParms['iListLimit'] )  $this->currParms['iListLimit'] = intval(@$this->uiDef['ListSize']);

        /* Build the condition for CreateRecordCursor
         */
        $raCond = array();

        // user-defined condition - user must use addslashes() around values
        if( !empty($this->uiDef['fnListFilter']) ) {
            $tmpCond = $this->uiDef['fnListFilter'](/* $this */);
            if( !empty($tmpCond) ) {
                $raCond[] = "(".$tmpCond.")";
            }
        }

        // search tool in this component
        $tmpCond = $this->searchToolGetDBCond();
        if( !empty($tmpCond) ) {
            $raCond[] = $tmpCond;
        }

        // column select lists
        foreach( $this->uiParmsListColSelects as $k => $v ) {
            /* Filter for column select lists.  '*' means do not filter
             */
            if( $v == '*' ) continue;

            // $k is a column alias.  Convert it to the real column name for the SELECT query
            $raCond[] = $this->kfrel->GetRealColName($k)."='".addslashes($v)."'";
        }

        $cond = implode( " AND ", $raCond );

        $this->condMD5 = substr(md5($cond),0,6);

        /* Add an extra "OR" condition to include any new row that wouldn't necessarily fit the current filter.  This lets the user
         * see such rows once, before the filter hides them.
         *
         * N.B. ***** Do not include this in the MD5!  Else the list will assume that you've changed filters and will reset the list position.
         */
        if( $this->bFocusKeyRowInList && $this->kfuiCurrRow->GetKey() ) {
            $cond = "(".$this->kfrel->GetRealColName('_key')."='".$this->kfuiCurrRow->GetKey()."')".($cond ? " OR ($cond)" : "");
        }

        //echo $cond;
        //$this->kfrel->kfdb->KFDB_SetDebug(2);

        /* If the condition has changed, then we are looking at a whole new view.
         * Reset the currRow, and start it at offset zero.
         */
        if( !@$this->kfui->klugeRowInit && $this->condMD5 != @$this->uiParms['condMD5'] ) {
            $this->kfuiCurrRow->Clear();
            $this->currParms['iListOffset'] = $this->uiParms['off'] = 0;
        }   $this->currParms['iListSelPos'] = $this->uiParms['lpos'] = 0;


        /* Draw the list headings
         */
        echo "<STYLE>";
        echo ".kfuiListRow0    { background-color: #eeeeee; font-family: verdana,helvetica,sans serif; font-size:8pt; }";
        echo ".kfuiListRow1    { background-color: #cccccc; font-family: verdana,helvetica,sans serif; font-size:8pt; }";
        echo ".kfuiListRow2    { background-color: #4444ff; font-family: verdana,helvetica,sans serif; font-size:8pt; color: white; }";
        echo ".kfuiListRowEnd A:link { color:white; } ";
        echo ".kfuiListRowEnd A:visited { color:white; } ";
        echo ".kfuiListRowEnd  { background-color: #666666; font-family: verdana,helvetica,sans serif; font-size:8pt; color: white; }";
        echo ".kfuiListButtons { font-family: verdana,helvetica,sans serif; font-size:7pt; }";
        echo ".kfuiListHead    { font-family: verdana,helvetica,sans serif; font-size:10pt; }";
        echo "</STYLE>";
        echo "<TABLE align=center><TR><TD valign='top'>";
        echo "<TABLE><TR class='kfuiListHead'><TH>&nbsp;</TH>";
        foreach( $this->uiDef['ListCols'] as $col ) {
            /* The triangles have to be available in SITEIMG_STDIMG, which has to be defined and has to be under the webroot).
             */
            echo "<TH width='{$col['w']}' valign='top'>";
            echo "<A ".$this->EncodeUrlHREF($this->kfuiCurrRow->GetKey(),array("sortup" => $col['alias'])).">";
            echo "<IMG src='".SITEIMG_STDIMG."triangle_blue_up".(($this->currParms['sListSortCol']==$col['alias'] && !$this->currParms['bListSortDown']) ? "" : "_empty").".gif' border=0>";
            echo "</A><BR>";
            echo $col['label']."<BR>";
            echo "<A ".$this->EncodeUrlHREF($this->kfuiCurrRow->GetKey(),array("sortdown" => $col['alias'])).">";
            echo "<IMG src='".SITEIMG_STDIMG."triangle_blue_down".(($this->currParms['sListSortCol']==$col['alias'] && $this->currParms['bListSortDown']) ? "" : "_empty").".gif' border=0>";
            echo "</A>";


            if( @$col['showColsel'] ) {
                echo "<FORM method=post action='${_SERVER['PHP_SELF']}' target='".$this->currParms['sListURLTarget']."' onChange='submit();'>";
                echo $this->HiddenFormParms( $this->kfuiCurrRow->GetKey() );
                echo "<SELECT name='_kfu{$this->cid}_colsel_${col['alias']}'>";
                echo "<OPTION value='*'".($this->uiParmsListColSelects[$col['alias']]=='*' ? " SELECTED" : "").">- - -</OPTION>";
                if( ($kfr = $this->kfrel->CreateRecordCursor( "" )) ) {
                    $raUnique = array();
                    while( $kfr->CursorFetch() ) {
                        $raUnique[$kfr->value($col['alias'])] = 1;
                    }
                    $kfr = NULL;
                    ksort( $raUnique );
                    foreach( $raUnique as $k => $v ) {
                        echo "<OPTION value='$k'".($k==$this->uiParmsListColSelects[$col['alias']] ? " SELECTED" : "").">$k</OPTION>";
                    }
                }
                echo "</SELECT></FORM>";
            }
            echo "</TH>";
        }
        echo "</TR>";

        /* Get the rows from the db
         */
        $i = 0;
        $kfrParms = array();
        if( !empty($this->currParms['sListSortCol']) ) {
            $kfrParms['sSortCol'] = $this->currParms['sListSortCol'];
            $kfrParms['bSortDown'] = $this->currParms['bListSortDown'];
        }
        $kfrParms['iOffset'] = @$this->currParms['iListOffset'];
        $kfrParms['iLimit'] = @$this->currParms['iListLimit'];
        $kfrParms['iStatus'] = @$this->uiDef['ListStatus'];

        $nNumRows = 0;
        if( ($kfr = $this->kfrel->CreateRecordCursor( $cond, $kfrParms )) ) {
            $nNumRows = $kfr->CursorNumRows();
            while( $kfr->CursorFetch() ) {
                if( !$this->kfuiCurrRow->GetKey() && !$this->bNewRow ) {
                    $this->kfuiCurrRow->SetKey( $kfr->Key() );
                }
                if( $this->kfuiCurrRow->GetKey() && $kfr->Key() == $this->kfuiCurrRow->GetKey() ) {
                    $nClass = 2;
                } else {
                    $nClass = ($i%2);
                }

                echo "<TR id='r".$kfr->Key()."' class='kfuiListRow$nClass'>";
                echo "<TD><A ".$this->EncodeUrlHREF($kfr->Key())."><IMG src='".SITEIMG_STDIMG."dot1.gif' border=0></A></TD>";

                /* Optionally translate the row values
                 */
                $raXlatRow = NULL;
                if( !empty($this->uiDef['fnListTranslate'] ) ) {
                    $raXlatRow = $this->uiDef['fnListTranslate']($kfr);
                }

                foreach( $this->uiDef['ListCols'] as $col ) {
                    echo "<TD>";
                    if( $raXlatRow && array_key_exists($col['alias'],$raXlatRow) ) {
                        echo $raXlatRow[$col['alias']];
                    } else {
                        echo $kfr->Value($col['alias']);
                    }
                    echo "</TD>";
                }
                echo "</TR>";
                $i++;
            }
            $kfr->CursorClose();
        }
        $bEOL = !@$this->currParms['iListLimit'] || ($i < $this->currParms['iListLimit']);

        if( $bEOL ) {
            echo "<TR class='kfuiListRowEnd'><TD colspan='".(count($this->uiDef['ListCols'])+1)."'>&nbsp;&nbsp;&nbsp;";
            echo "<A ".$this->EncodeUrlHREF(0).">End of List</A></TD></TR>";
        }

        if( $bEOL && @$this->uiDef['ListSizePad'] ) {
            for( $j = 0; $j < ($this->uiDef['ListSize'] - $i - 1); ++$j ) {
                echo "<TR class='kfuiListRow0'><TD colspan='".(count($this->uiDef['ListCols'])+1)."'>&nbsp;</TD></TR>";
            }
        }
        echo "</TABLE>";
        echo "</TD>";
        if( @$this->currParms['iListLimit'] ) {
            // if using limited size list, show list nav buttons
    // It's very inefficient to compute nRows just so we can set a jump-to-the-bottom button that is rarely used.
    // Instead record an end-of-list flag at the cursor loop and use this to disable DOWN buttons,
    // and set the BOT link to _rflpos=BOT, which will cause the inefficient calculation to be performed only when necessary.
            $nRows = 1000;

            $iOffset = intval(@$this->currParms['iListOffset']);
            $iLimit  = intval(@$this->currParms['iListLimit']);

            $offBot   = $nRows - $iLimit + 1;       if( $offBot < 0 )           $offBot = 0;
            $offU     = $iOffset - 1;               if( $offU   < 0 )           $offU = 0;
            $offD     = $iOffset + 1;               if( $offD   > $offBot )     $offD = $offBot;
            $offPageU = $iOffset - $iLimit;         if( $offPageU < 0 )         $offPageU = 0;
            $offPageD = $iOffset + $iLimit;         if( $offPageD > $offBot )   $offPageD = $offBot;

            echo "<TD class='kfuiListButtons'>";
            //echo "<BR><BR>";
            echo "$iOffset above";

            $this->_drawListButton( "TOP",  "up", $iOffset, 0,         3 );
            $this->_drawListButton( "PAGE", "up", $iOffset, $offPageU, 2 );
            $this->_drawListButton( "UP",   "up", $iOffset, $offU,     1 );

            echo "<DIV style='padding:3px'><A ".$this->EncodeUrlHREF($this->kfuiCurrRow->GetKey(),array("lpos"=>($iLimit/2)))."><B>SEL</B></A></DIV>";

            $this->_drawListButton( "DOWN", "down", !$bEOL, $offD,     1 );
            $this->_drawListButton( "PAGE", "down", !$bEOL, $offPageD, 2 );
            $this->_drawListButton( "BOT",  "down", !$bEOL, $offBot,   3 );

    echo "<BR>$nNumRows shown";
            echo "</TD>";
        }
        echo "</TR></TABLE>";
    }

    function _drawListButton( $label, $dir, $bActive, $iDestOffset, $nTriangles )
    /****************************************************************************
        Draw the TOP, PAGE UP... buttons beside the list
     */
    {
        echo "<DIV style='padding:3px'>";
        if( $bActive ) {
            echo "<A ".$this->EncodeUrlHREF($this->kfuiCurrRow->GetKey(),array("off"=>$iDestOffset))." style='text-decoration:none;'>";
        }
        for( $i = 0; $i < $nTriangles; ++$i ) {
            echo "<IMG src='".SITEIMG_STDIMG."triangle_blue_$dir".($bActive ? "" : "_empty").".gif' border=0>";
        }
        echo "&nbsp;&nbsp;".($bActive ? "<B>$label</B></A>" : $label);
        echo "</DIV>";
    }

    function drawForm( $parms )
    /**************************
        parms:
            fkDefaults = array of tablename=key to preload fk values of the relation - used only if the row is new (insert)
     */
    {
        $kfr = $this->kfuiCurrRow->GetKFR();

        echo "<DIV style='border: black solid medium; padding:10px;'>";
        echo "<FORM";
        if( !empty($this->uiDef['sFormTarget']) ) echo " target='".$this->uiDef['sFormTarget']."'";
        if( !empty($this->uiDef['sFormAction']) ) echo " action='".$this->uiDef['sFormAction']."'";
                                             else echo " action='{$_SERVER['PHP_SELF']}'";
        if( !empty($this->uiDef['sFormMethod']) ) echo " method='".$this->uiDef['sFormMethod']."'";
                                             else echo " method='post'";
        echo ">";
        echo "<INPUT type=hidden name='_kfu{$this->cid}_action' value='".($this->kfuiCurrRow->GetKey() ? "update" : "insert")."'>";
        echo $this->HiddenFormParms( $this->kfuiCurrRow->GetKey() );

        if( $this->kfuiCurrRow->GetKey() ) {
            echo "<H2>Edit {$this->uiDef['Label']}</H2>";
        } else {
            echo "<H2>Enter new {$this->uiDef['Label']}</H2>";
        }

        if( !empty($this->uiDef['fnFormDraw']) ) {
// also pass something that lets the form encode kfui field names correctly
            $this->uiDef['fnFormDraw']($kfr);
        } else {
            /* Default form
             */
            echo "<TABLE cellpadding=5 width='50%' align='center'>";

            foreach( $this->kfrel->baseTable['Fields'] as $f ) {
                // don't put internal fields on the form
                if( $f['col'][0] == '_' ) continue;

                $val = $kfr->valueEnt($f['col']);
                $bFKFound = false;
                // draw preset foreign keys as non-editable
                if( substr( $f['col'],0,3 ) == "fk_" ) {
                    if( isset( $this->uiDef['fkDefaults'] ) ) {
                        foreach( $this->uiDef['fkDefaults'] as $fkTableName => $fkCid ) {
                            if( $f['col'] == "fk_".$fkTableName ) {
                                $val = $this->kfui->GetKey($fkCid);
                                $bFKFound = true;
                            }
                        }
                    }
                    if( !$bFKFound && isset($this->kfrelParms['fkDefaults']) ) {
                        foreach( $this->kfrelParms['fkDefaults'] as $fkTableName => $fkKey ) {
                            if( $f['col'] == "fk_".$fkTableName ) {
                                $val = $fkKey;
                                $bFKFound = true;
                            }
                        }
                    }
                }
// encode kfui field names correctly
                if( $bFKFound ) {
                    echo "<TR><TD>{$f['col']}</TD><TD>$val<INPUT type=hidden name='{$f['col']}' value='$val'></TD></TR>";
                } else {
                    echo "<TR><TD>{$f['col']}</TD><TD><INPUT type=text name='{$f['col']}' value='$val'></TD></TR>";
                }
            }
            echo "</TABLE>";
            echo "<INPUT type=submit value=Save>";
        }
        echo "</FORM>";
        echo "</DIV>";
    }

    function drawControls( $parm )
    /*****************************
     */
    {
        if( !@$this->uiDef['Controls_disallowNew'] ) {
            echo "<P><A ".$this->EncodeUrlHREF(0).">New {$this->uiDef['Label']}</A></P>";
        }
        if( !@$this->uiDef['Controls_disallowDelete'] ) {
            echo "<P><A ".$this->EncodeUrlHREF($this->kfuiCurrRow->GetKey(), array("action" => "delete")).">Delete this {$this->uiDef['Label']}</A></P>";
        }
        echo "<BR><DIV style='font-size:10; font-family:arial,helvetica,sans-serif;'>"
            ."<BR>Record created by ".$this->kfui->GetUserName($this->kfuiCurrRow->GetValue('_created_by'))."<BR>[".$this->kfuiCurrRow->GetValue('_created')."]"
            ."<BR>"
            ."<BR>Last updated by ".$this->kfui->GetUserName($this->kfuiCurrRow->GetValue('_updated_by'))."<BR>[".$this->kfuiCurrRow->GetValue('_updated')."]"
            ."</DIV>";
    }

    function drawSearch( $parm )
    /***************************
     */
    {
        $currCol = @$this->uiParmsSearch["col"];
        $currOp  = @$this->uiParmsSearch["op"];
        $currVal = @$this->uiParmsSearch["val"];

        echo "<FORM";
        if( !empty($this->uiDef['sSearchTarget']) ) echo " target='".$this->uiDef['sSearchTarget']."'";
        if( !empty($this->uiDef['sSearchAction']) ) echo " action='".$this->uiDef['sSearchAction']."'";
                                             else echo " action='{$_SERVER['PHP_SELF']}'";
        if( !empty($this->uiDef['sSearchMethod']) ) echo " method='".$this->uiDef['sSearchMethod']."'";
                                             else echo " method='post'";
        echo ">";
        echo $this->HiddenFormParms( $this->kfuiCurrRow->GetKey(),
                                     array( "srch_col"=>"",    // exclude these from the hidden set
                                            "srch_op"=>"",
                                            "srch_val"=>"",
// not needed now that condMD5 resets the view    "off"=>"", "lpos"=>"",  // reset positioning because list contents will change
                                            ) );

        echo "Search ";
        echo "<SELECT name='_kfu{$this->cid}_srch_col'>";
        echo "<OPTION value=''>Any</OPTION>";
        if( isset( $this->uiDef['SearchToolCols'] ) ) {
            foreach( $this->uiDef['SearchToolCols'] as $rLabel => $rCol ) {
                echo "<OPTION value='$rCol'".($rCol == $currCol ? " SELECTED" : "").">$rLabel</OPTION>";
            }
        } else {
            /* If the SearchToolCols are not defined, show a list of all cols in the kfrel
             */
            foreach( $this->kfrel->raColAlias as $rCol ) {
                echo "<OPTION value='$rCol'".($rCol == $currCol ? " SELECTED" : "").">$rCol</OPTION>";
            }
        }
        echo "</SELECT>";

        echo "&nbsp;&nbsp;<SELECT name='_kfu{$this->cid}_srch_op'>";
        echo BXFormOption( "like",  "contains",    $currOp );
        echo BXFormOption( "eq",    "equals",      $currOp );
        echo BXFormOption( "start", "starts with", $currOp );
        echo BXFormOption( "end",   "ends with",   $currOp );
        echo BXFormOption( "blank", "is blank",    $currOp );
        echo "</SELECT>";

        echo "&nbsp;&nbsp;<INPUT type=text name='_kfu{$this->cid}_srch_val' value='".htmlspecialchars($currVal,ENT_QUOTES)."'>";
        echo "&nbsp;&nbsp;<INPUT type=submit value='Search'>";
        echo "</FORM>";
    }

    function searchToolGetDBCond()
    /*****************************
     */
    {
        $cond = "";
        $currCol = @$this->uiParmsSearch["col"];
        $currOp  = @$this->uiParmsSearch["op"];
        $currVal = @$this->uiParmsSearch["val"];

        if( $currOp == 'blank' ) {
            if( !empty($currCol) ) {
                $cond = "($currCol='' OR $currCol IS NULL)";
            }
        } else if( !empty($currVal) ) {
            $raCond = array();
            if( empty($currCol) ) {
                /* Search all cols in SearchToolCols. If !SearchToolCols, search all cols in kfrel
                 */
                if( isset( $this->uiDef['SearchToolCols'] ) ) {
                    foreach( $this->uiDef['SearchToolCols'] as $rLabel => $rCol ) {
                        $raCond[] = $this->_searchToolGetDBCondTerm( $rCol, $currOp, $currVal );
                    }
                } else {
                    foreach( $this->kfrel->raColAlias as $rCol ) {
                        $raCond[] = $this->_searchToolGetDBCondTerm( $rCol, $currOp, $currVal );
                    }
                }
            } else {
                $raCond[] = $this->_searchToolGetDBCondTerm( $currCol, $currOp, $currVal );
            }
            if( count($raCond) )  $cond = "(". implode( " OR ", $raCond ) .")";
        }
        return( $cond );
    }

    function _searchToolGetDBCondTerm( $col, $op, $val )
    /***************************************************
     */
    {
        $s = $col." ".($op=="eq" ? "=" : "like")." ";
        $s .= "'";
        if( $op == "like" || $op == "end" )  $s .= "%";
        $s .= addslashes($val);
        if( $op == "like" || $op == "start" )  $s .= "%";
        $s .= "'";
        return( $s );
    }


    function EncodeUrl( $selKey, $raParms = array() )
    /************************************************
        Encode the current/modified parms in a URL.

        Also passes along user parms.
     */
    {
        $ra = $this->_marshalStateParms( $selKey, $raParms );

        $raPairs = array();
        foreach( $ra as $k => $v ) {
            $raPairs[] = "$k=".urlencode($v);
        }
        return( $this->currParms['sListUrlPage']."?".implode( "&", $raPairs ) );
    }

    function HiddenFormParms( $selKey, $raParms = array() )
    /******************************************************
        Same as EncodeURL but for Forms.
        Return a string of INPUT-hidden fields that retain the current kfui state modified by given parms

        $raParms with blank values causes those parms to be omitted from the output.
            1) use this to reset a parm to its default value
            2) use this to exclude a parm from the hidden set, when it is represented by another control in the form
     */
    {
        $ra = $this->_marshalStateParms( $selKey, $raParms );

        $s = "";
        foreach( $ra as $k => $v ) {
            if( !empty($v) ) $s .= BXFormHiddenStr( $k, $v );
        }
        return( $s );
    }

    function _marshalStateParms( $selKey, $raParms = array() )
    /*********************************************************
        Collect all the name-value pairs needed to communicate the kfui state to the next page.
        Returns an array that can be used to generate a URL, Form-hidden parms, or Session variables.

        $raParms = parms that should be set in the new URL/Form (without kfu prefix)
     */
    {
        $kfu = "_kfu".$this->cid."_";
        $p = array();

        foreach( $this->uiParms as $k => $v ) {
            if( $k == "action" ) continue;                                      // don't propagate the action verb from parsed parms - set it explicitly in the Form for one cycle
            if( $k == "condMD5" ) continue;                                     // set this below
            $p[$kfu.$k] = $v;
        }
        $p[$kfu.'condMD5'] = $this->condMD5;
        $p[$kfu.'row'] = $selKey;   // override uiParms key with the value from the arg list
        $bKeyChanged = ($selKey != @$this->uiParms['row']);

        foreach( $this->uiParmsListColSelects as $k => $v ) {
            $p[$kfu."colsel_$k"] = $v;
        }

        foreach( $this->uiParmsSearch as $k => $v ) {
            $p[$kfu."srch_$k"] = $v;
        }


        /* Encode the uiParms of other components
         */
        foreach( $this->kfui->uiComps as $cid1 => $v1 ) {
            if( $cid1 == $this->cid )  continue;

            foreach( $v1->uiParms as $k => $v ) {
                /* Don't propagate the action verb of other components.  This case occurs when an action has just been
                 * performed, and we're constructing a link to the next page.  Drop all action stuff here.
                 */
                if( $k == 'action' )  continue;

                /* raDependents: when a parent list changes selection, all dependent components should be reset because
                 * their content no longer applies.
                 */
                if( $bKeyChanged && isset($this->uiDef['raDependents']) &&
                      ($k == 'row' || $k == 'lpos' || $k == 'off') && in_array( $cid1, $this->uiDef['raDependents'] ) )
                    // don't define $cid1's position
                    continue;

                $p["_kfu${cid1}_$k"] = $v;
            }
        }


        /* Encode the user parms
         */
        foreach( $this->kfui->userParms as $k => $v ) {

//these should be excluded at InitUIParms
if( !@$this->uiDef['DisallowNonPrefixedBaseFieldsInGPC'] && $this->kfrel->IsBaseField($k) )  continue;

            $p[$k] = $v;
        }



        /* Finally, overwrite existing parms with the raParms.
         * Watch out for a switch between 'sortup' and 'sortdown'. One of those can't just overwrite the other.
         */
        foreach( $raParms as $k => $v ) {
            if( $k == "sortup" || $k == "sortdown" ) {
                unset( $p[$kfu.'sortup'] );
                unset( $p[$kfu.'sortdown'] );
            }
            $p[$kfu.$k] = $v;
        }

        return( $p );
    }


    function EncodeUrlHREF( $selKey, $raParms = array() )
    /****************************************************
     */
    {
        return( "HREF='".$this->EncodeUrl($selKey,$raParms)."' target='".$this->currParms['sListUrlTarget']."'" );
    }

}


class kfuiCurrRow {
/****************
    This encapsulates the logic that manages the current row of a uiComp.
    Although this is created when the uiComp is created, it cannot be used until the uiComp->kfrel is set.
    Note that if $this->key is not set, this will behave as if the row is new (same as setting key=0).
*/
    var $uiComp;    // owner

    var $kfr = NULL;
    var $key = 0;

    function kfuiCurrRow( &$uiComp ) {
        $this->uiComp =& $uiComp;
    }

    function Clear()        { $this->SetKey( 0 ); }
    function SetKey( $k )   { $this->key = $k; $this->kfr = NULL; }     // destroys any existing kfr
    function GetKey()       { return( $this->key ); }
    function GetValue( $f ) { $this->prepare(); return( $this->kfr ? $this->kfr->Value( $f ) : "" ); }
    function GetKFR()       { $this->prepare(); return( $this->kfr ); }

    function prepare() {
        if( !$this->kfr ) {
            if( $this->key ) {
	        // First try to load a record.  If there isn't one, create a blank record and set the key below
                $this->kfr = $this->uiComp->kfrel->GetRecordFromDBKey( $this->key );
            }
            if( !$this->kfr ) {
                $this->kfr = $this->uiComp->kfrel->CreateRecord( @$this->uiComp->kfrelParms['fkDefaults'] );
                if( $this->kfr && $this->key ) {
                    // the lookup failed above, so this is a new row with a forced key value
                    $this->kfr->SetKey( $this->key );
                }
            }
        }
    }
}

?>
