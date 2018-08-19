<?php

/* Crop Description config
 *
 * Copyright (c) 2014-2017 Seeds of Diversity Canada
 *
 */

if( !defined("SITEROOT") )  define("SITEROOT", "../../");
include_once( SITEROOT."site.php" );

include_once( SEEDCOMMON."console/console01kfui.php" );
include_once( SEEDCOMMON."sl/sl_db.php" );
include_once( SEEDCOMMON."sl/sl_desc_db.php" );

include_once( STDINC."SEEDEditor.php" );    // for Forms editor
include_once( STDINC."SEEDTable.php" );     // for upload from spreadsheet
include_once( SEEDCOMMON."sl/q/_QServerDesc.php" );


// Access to the application is given if any of the tabs are accessible
// Inaccessible tabs are Ghosted
$raPerms = array( 'Descriptors'=> array('SLDesc'=>'A'),
                  'Multiple'   => array('SLDesc'=>'A'),
                  'Forms'      => array('SLDesc'=>'A'),
                  'Forms2'     => array('SLDesc'=>'A'),
);
list($kfdb, $sess, $lang) = SiteStartSessionAccount( $raPerms );

//var_dump($_REQUEST);
$kfdb->SetDebug(1);

$sOut = $sErr = "";

/* The spreadsheet upload forms send "action"
 * The confirmation forms send "action2"
 */
if( in_array(($sAction = SEEDSafeGPC_GetStrPlain('action')), array('upload-t','upload-m') ) ) {
    /* Upload tags or multiples from spreadsheet
     */
    list($bOk,$sRet) = uploadXLS( $sAction );
    if( $bOk ) $sOut .= $sRet;
    else       $sErr .= $sRet;
}
if( in_array(($sAction = SEEDSafeGPC_GetStrPlain('action2')), array('update-t','update-m') ) ) {
    /* Update tags or multiples to db
     */
    list($bOk,$sRet) = updateDB( $sAction, $kfdb, $sess );
    if( $bOk ) $sOut .= $sRet;
    else       $sErr .= $sRet;
}


class MyConsole extends Console01KFUI
{
//    public $oW;
    public $oDescDB_Cfg;
    private $oFormSpeciesFilter;    // control for filtering lists to species prefix

    function __construct( KeyFrameDB $kfdb, SEEDSession $sess, $raParms )
    {
        $this->oDescDB_Cfg = new SLDescDB_Cfg( $kfdb, $sess->GetUID() );

        $this->oFormSpeciesFilter = new SEEDFormSession( $sess, "SLDescConfig", "S" );
        $this->oFormSpeciesFilter->Update();

        parent::__construct( $kfdb, $sess, $raParms );
    }

    function TFmainDescriptorsInit()   { $this->myInit( 'tags' ); }
    function TFmainMultipleInit()      { $this->myInit( 'multiple' ); }
    function TFmainFormsInit()         { $this->myInit( 'forms' ); }
    function TFmainForms2Init()        { }

    function TabSetPermission( $tsid, $tabname )
    {
        return( true );
    }

    function TFmainDescriptorsControl() { return( $this->controlWithSpeciesSelector( 't' ) ); }
    function TFmainMultipleControl()    { return( $this->controlWithSpeciesSelector( 'm' ) ); }
    function TFmainFormsControl()       { return( $this->oComp->SearchToolDraw() ); }
    private function controlWithSpeciesSelector( $tab )
    {
        /* Make a species selector by getting all unique tag prefixes
         */
        $raTags = $this->oDescDB_Cfg->GetListCfgTags();
        $raSp = array();
        foreach( $raTags as $tag => $ra ) {
            if( ($i = strpos($tag,'_')) !== false ) {
                $sp = substr( $tag, 0, $i );
                $raSp[$sp] = $sp;
            }
        }
        asort($raSp);
        $raSp = array_merge( array('-- All --'=>''),$raSp);

        $s = "<div style='display:inline-block'>".$this->oComp->SearchToolDraw()."</div>"
            ."<div style='display:inline-block;margin-left:50px;'>"
                ."Species: "
                ."<form method='post' style='display:inline'>"
                .$this->oFormSpeciesFilter->Select2( "selSp", $raSp, "", array("attrs"=>'onChange="submit();"') )
                ."</form>"
            ."</div>"

            // Download/upload buttons
            ."<div style='display:inline-block;float:right;'>"
                ."<a href='".Site_UrlQ()."?cmd=".($tab=='t'?'descCfgTags':'descCfgMultiples')
                                        ."&sp=".urlencode($this->oFormSpeciesFilter->Value('selSp'))."&fmt=xls' target='_blank'>"
                ."Download</a><br/>"
                ."<a style='cursor:pointer' onclick='showUploadForm()'>Upload</a><br/>"
                ."</div>"

            ."<div style='display:inline-block;float:right;margin-right:5px;vertical-align:top'>"
                ."<img src='".W_ROOT."std/img/dr/xls.png' height='25'/>"
            ."</div>"

            ."<div id='uploadForm' style='display:none;float:right;clear:right;background-color:white;padding:5px;border:1px solid #777'>"
                ."<form method='post' enctype='multipart/form-data'>"
                    ."<input type='file' name='uploadfile' /> "
                    ."<input type='hidden' name='action' value='".($tab=='t'?'upload-t':'upload-m')."'/> "
                    ."<input type='submit' value='Upload spreadsheet'/>"
                ."</form>"
            ."</div>"
        ;

        $s .= "<script>
               function showUploadForm()
               {
                   $('#uploadForm').show();
               }
               </script>";

        return( $s );
    }


    function myInit( $k )
    {
        switch( $k ) {
            case 'tags':       $kfrel = $this->oDescDB_Cfg->GetKfrelCfgTags(); break;
            case 'multiple':   $kfrel = $this->oDescDB_Cfg->GetKfrelCfgM(); break;
            case 'forms':      $kfrel = $this->tmpKfrelForms(); break;
            default:           die( "No kfrel in init" );
        }

        $raCompParms = array(
            'tags'=> array(
                    "Label" => "Descriptor",

                    "ListCols" => array( array( "label"=>"tag", "colalias"=>"tag",      "w"=>100 ),
                                         array( "label"=>"en",  "colalias"=>"label_en", "w"=>200 ),
                                         array( "label"=>"fr",  "colalias"=>"label_fr", "w"=>200 ),
                                  ),
                    "ListSize" => 15,
//                        "ListSizePad" => 1,
//                        "SearchToolCols"  => array( "Company"=>"R.name_en",
//                                            "Index Species"=>"pspecies", "Orig Species"=>"ospecies",
//                                            "Index Cultivar"=>"pname",   "Orig Cultivar"=>"oname" ),
//                        "fnListFilter"    => "Item_rowFilter",
                    "fnFormDraw"      => array($this,"SLDescTagsFormDraw"),
                    "fnListFilter"    => array($this,"SLDescTagsListFilter"),
            ),
            'multiple' => array(
                    "Label" => "Multiple",
                    "ListCols" => array( array( "label"=>"tag",   "colalias"=>"tag",  "w"=>100 ),
                                         array( "label"=>"value", "colalias"=>"v",    "w"=>60),
                                         array( "label"=>"en",    "colalias"=>"l_en", "w"=>200 ),
                                         array( "label"=>"fr",    "colalias"=>"l_fr", "w"=>200 ),
                                  ),
                    "ListSize" => 15,
                    "ListSizePad" => 1,
                    "fnFormDraw" => array($this,"SLDescMFormDraw"),
                    "fnListFilter"    => array($this,"SLDescTagsListFilter"),
            ),
            'forms' => array(
                    "Label" => "Form",
                    "ListCols" => array( array( "label"=>"title",   "colalias"=>"title",   "w"=>100 ),
                                         array( "label"=>"species", "colalias"=>"species", "w"=>100 ),
                                  ),
                    "ListSize" => 15,
                    "ListSizePad" => 1,
                    "fnFormDraw" => array($this,"SLDescFormFormDraw"),
            ),
        );

        $this->CompInit( $kfrel, $raCompParms[$k] );
    }


// This could be implemented via docrep or something
/*

CREATE TABLE IF NOT EXISTS sl_desc_cfg_forms (
        _key        INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT,
        _created    DATETIME,
        _created_by INTEGER,
        _updated    DATETIME,
        _updated_by INTEGER,
        _status     INTEGER DEFAULT 0,

    title      VARCHAR(200) NOT NULL,
    species    VARCHAR(200) NOT NULL,
    form       TEXT
) DEFAULT CHARSET=latin1;
*/
    function tmpKfrelForms()
    {
        $def =
            array( "Tables" => array(
                array( "Table" => "sl_desc_cfg_forms",
                       "Type"  => "Base",
                       "Fields" => array( array( "col"=>"title",   "type"=>"S" ),
                                          array( "col"=>"species", "type"=>"S" ),
                                          array( "col"=>"form",    "type"=>"S" ),
            ))));

        return( new KeyFrameRelation( $this->kfdb, $def, 0 ) );
    }



    function TabSetContentDraw( $tsid, $tabname )
    {
        $s = "";
        switch( $tabname ) {
            case 'Descriptors':  $s .= $this->CompListForm_Vert();    break;
            case 'Multiple':     $s .= $this->CompListForm_Vert();    break;
            case 'Forms':        $s .= $this->CompListForm_Vert();    break;
            case 'Forms2':       $s .= $this->forms2Content();        break;
        }
        return( $s );
    }

    function SLDescTagsListFilter()
    /******************************
        Filter the list by species prefix
     */
    {
        $s = "";

        if( ($filter = $this->oFormSpeciesFilter->Value('selSp')) ) {
            $s = "tag like '".addslashes($filter)."_%'";
        }

        return( $s );
    }

    function SLDescTagsFormDraw( $oForm )
    {
        $s = "<TABLE class='slAdminForm' border='0'>"
            ."<TR>".$oForm->TextTD( "tag", "Tag", array('size'=>50) )."<td colspan='2'>&nbsp;</td></TR>"
            ."<TR>".$oForm->TextTD( "label_en", "Label EN", array('size'=>50) )
                   .$oForm->TextTD( "label_fr", "Label FR", array('size'=>50) )."</TR>"
            ."<TR>".$oForm->TextAreaTD( "q_en", "Question EN" )
                   .$oForm->TextAreaTD( "q_fr", "Question FR" )."</TR>"
            ."</TABLE>"
            ."<INPUT type='submit' value='Save'>";
        return( $s );
    }

    function SLDescMFormDraw( $oForm )
    {
        $s = "<TABLE class='slAdminForm' border='0'>"
            ."<TR>".$oForm->TextTD( "tag", "Tag", array('size'=>50) )
                   .$oForm->TextTD( "v",   "Value", array('size'=>50) )."</TR>"
            ."<TR>".$oForm->TextTD( "l_en", "Label EN", array('size'=>50) )
                   .$oForm->TextTD( "l_fr", "Label FR", array('size'=>50) )."</TR>"
            ."</TABLE>"
            ."<INPUT type='submit' value='Save'>";
        return( $s );
    }

    function SLDescFormFormDraw( $oForm )
    {
        $oEdit = new SEEDEditor( "TinyMCE" );
        $oEdit->SetFieldName( $oForm->Name( 'form' ) );
        $oEdit->SetContent( $oForm->Value('form') );
        $sEditor = "<DIV>"
                  .$oEdit->Editor( array('controls'=>'Joomla', 'width_css'=>'100%', 'height_px'=>200) )
                  ."</DIV>";

        $s = "";

        if( $oForm->GetKey() ) {
// use localhost for development site
            $sWindow = "window.open('http://seeds.ca/bauta/descriptions/popup.php?cmd=CDForm&k=".$oForm->GetKey()."',"
                      ."'_blank','width=800,height=600,scrollbars=yes');";

            $s .= "<div style='float:right; border:1px solid #555; padding:20px;border-radius:5px;background-color:#eee;margin-right:20px'>"
                 ."<input type='button' value='Preview' onclick=\"$sWindow\"/>"
                 ."</div>";
        }

        $s .= "<TABLE class='slAdminForm' border='0'>"
             ."<TR>".$oForm->TextTD( "title", "Title", array('size'=>100) )."</TR>"
             ."<TR>".$oForm->TextTD( "species", "Species", array('size'=>20) )."</TR>"
             ."<tr><td>&nbsp;</td><td valign='top'>$sEditor</td></tr>"
             //."<TR>".$oForm->TextTD( "form", "Form", array('size'=>100) )."</TR>"
             ."</TABLE>"
             ."<INPUT type='submit' value='Save'>";

        return( $s );
    }

    function forms2Content()
    {
        $s = "";

        include_once( SEEDCOMMON."doc/docUtil.php" );

        $oDocRepDB = New_DocRepDB_WithMyPerms( $this->kfdb, 0, array() );
        $kFolder = $oDocRepDB->GetDocFromName( 'web/cropdesc/forms' );
        if( !$kFolder ) {
            $s = "No folder 'cropdesc/forms'";
            goto done;
        }

        $raTree = $oDocRepDB->GetSubtree( $kFolder, 1, array() );
        $s .= "<table>";
        foreach( $raTree as $kDoc => $ra ) {
            $oDoc = $oDocRepDB->GetDocRepDoc( $kDoc );
            $s .= "<tr><td style='padding-right:30px;'>".$oDoc->GetTitle("")."</td>"
                     ."<td style='padding-right:30px;'>".($name = $oDoc->GetName())."</td>"
                     ."<td><a href='".SITEROOT_URL."d/doc.php?n=".urlencode($name)."' target='_blank'>Preview</a></td>"
                 ."</tr>";
        }
        $s .= "</table>";

        done:
        return( $s );
    }

}

$raConsoleParms = array(
    'HEADER' => "Crop Description Config on ${_SERVER['SERVER_NAME']}",
    'HEADER_LINKS' => array( array( 'href'=>"#",
                                    'onclick'=>'window.open("http://seeds.ca/d?n=web/sl/doc/descCfgInstructions","_blank","width=600,height=800,scrollbars=yes")', 'label'=>"Instructions"
                                  )),
    'CONSOLE_NAME' => "SLDescCfg",
    'TABSETS' => array( "TFmain" => array( 'tabs' => array( 'Descriptors' => array( 'label' => "Descriptors" ),
                                                            'Multiple' => array( 'label' => "Multiple" ),
                                                            'Forms' => array( 'label' => "Forms" ),
                                                            'Forms2' => array( 'label' => "Forms2" ),
    ))),
    'bBootstrap' => true
);
$oC = new MyConsole( $kfdb, $sess, $raConsoleParms );
$oC->UserMsg( $sOut );
$oC->ErrMsg( $sErr );

echo $oC->DrawConsole( "[[TabSet: TFmain]]" );


function uploadXLS( $sAction )
/*****************************
    Upload a spreadsheet of tags or multiples.
    If it looks good, write it into the Console message area as a confirmation form with the data in hidden elements.
 */
{
    $sOut = "";

    if( $sAction == 'upload-t' ) {
        $def = array( 'raSEEDTableDef' => array( 'headers-required' => array('tag','label_en','label_fr','q_en','q_fr'),
                                                 'charset'=>'utf-8' ) );
    } else {
        $def = array( 'raSEEDTableDef' => array( 'headers-required' => array('tag','V','l_en','l_fr'),
                                                 'charset'=>'utf-8' ) );
    }

    list($bOk,$raRows,$sErr) = SEEDTable_LoadFromUploadedFile( 'uploadfile', $def );
    if( !$bOk ) {
        $sOut = $sErr;
        goto done;
    }

    // Check spreadsheet for bad data
    foreach( $raRows as $ra ) {
        if( false ){
            $bOk = false;
            $sOut .= "bad data";
        }
    }
    if( !$bOk )  goto done;

    /* Make confirmation form
     */
    $sOut .= "<p>Uploaded ".count($raRows)." rows from the spreadsheet. The following changes will be made if you confirm.</p>"
            ."<form method='post'>"
            ."<input type='submit' value='Confirm'/>&nbsp;&nbsp;<button onclick='window.location(\"{$_SERVER['PHP_SELF']}\");'>Cancel</button>"
            .SEEDForm_Hidden( "action2", $sAction=='upload-t' ? "update-t" : "update-m" )
            ."<br/><br/>";

    $i = 1;
    $sT = "<table border='1'><tr><th>tag</th>"
                                .($sAction=='upload-t' ? "<th>label_en</th><th>label_fr</th><th>q_en</th><th>qfr</th>"
                                                       : "<th>v</th><th>l_en</th><th>l_fr</th>" )
                           ."</tr>";
    foreach( $raRows as $ra ) {
        if( !$ra['tag'] )  continue;
        if( $sAction == 'upload-t' ) {
            $sOut .= SEEDForm_Hidden( "tag$i",       $ra['tag'] )
                    .SEEDForm_Hidden( "label_en$i",  $ra['label_en'] )
                    .SEEDForm_Hidden( "label_fr$i",  $ra['label_fr'] )
                    .SEEDForm_Hidden( "q_en$i",      $ra['q_en'] )
                    .SEEDForm_Hidden( "q_fr$i",      $ra['q_fr'] );
            $sT .= SEEDStd_ArrayExpand( $ra, "<tr><td valign='top'>[[tag]]</td>"
                                                ."<td valign='top'>[[label_en]]</td>"
                                                ."<td valign='top'>[[label_fr]]</td>"
                                                ."<td valign='top'>[[q_en]]</td>"
                                                ."<td valign='top'>[[q_fr]]</td></tr>" );
        } else {
            $sOut .= SEEDForm_Hidden( "tag$i",   $ra['tag'] )
                    .SEEDForm_Hidden( "l_en$i",  $ra['l_en'] )
                    .SEEDForm_Hidden( "l_fr$i",  $ra['l_fr'] )
                    .SEEDForm_Hidden( "v$i",     $ra['v'] );
            $sT .= SEEDStd_ArrayExpand( $ra, "<tr><td valign='top'>[[tag]]</td>"
                                                ."<td valign='top'>[[v]]</td>"
                                                ."<td valign='top'>[[l_en]]</td>"
                                                ."<td valign='top'>[[l_fr]]</td></tr>" );
        }
        ++$i;
    }
    $sT .= "</table>";

    $sOut .= $sT;

    done:
    return( array($bOk,$sOut) );
}

function updateDB( $sAction, $kfdb, $sess )
/******************************************
    If the user confirms a spreadsheet upload, update the db with the data propagated with the confirmation form.
 */
{
    $bOk = false;
    $sOut = "";

// this is in MyConsole too
$oDescDB_Cfg = new SLDescDB_Cfg( $kfdb, $sess->GetUID() );

    for( $i = 1; ; ++$i ) {
        $tag = SEEDSafeGPC_GetStrPlain( "tag$i" );
        if( !$tag ) break;

        if( $sAction == 'update-t' ) {
            $kfrel = $oDescDB_Cfg->GetKfrelCfgTags();
            if( !($kfr = $kfrel->GetRecordFromDB( "tag='".addslashes($tag)."'")) ) {
                $kfr = $kfrel->CreateRecord();
                $kfr->SetValue( 'tag', $tag );
            }
            foreach( array('label_en', 'label_fr', 'q_en', 'q_fr') as $k ) {
                $kfr->SetValue( $k, SEEDSafeGPC_GetStrPlain($k.$i) );
            }
            if( $kfr->PutDBRow() ) {
                $sOut .= "Updated tag $tag<br/>";
                $bOk = true;
            } else {
                $sOut .= "Error updating tag $tag";
                goto done;
            }
        } else {
            // add new multiples, update changed multiples, delete others
        }
    }

    if( false ) {
        $oQ = new Q( $kfdb, $sess, array() );
        $oQD = new QServerDesc( $oQ );

        // write to db
        foreach( $raRows as $ra ) {
            if( $sAction == 'update-t' ) {
                $rQ = $oQD->Cmd( 'descCfg--updateTag', $ra );
            } else {
                $rQ = $oQD->Cmd( 'descCfg--updateMultiple', $ra );
            }
            if( !$rQ['bOk'] ) $sOut .= "<br/>Update error: ".$rQ['sErr'];
        }
    }

    done:
    return( array($bOk,$sOut) );
}

?>
