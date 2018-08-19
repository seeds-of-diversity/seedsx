<?php

/* Translation interface for SEEDLocal
 *
 * Copyright (c) 2012-2015 Seeds of Diversity Canada
 *
 * mode=REST :
 *     input  = ns,k,lang[EN,FR,both if undefined] as http parms
 *     output = en and/or fr as <xml>
 */

if( !defined("SITEROOT") )  define("SITEROOT", "../");
include_once( SITEROOT."site.php" );
include_once( SEEDCOMMON."siteStart.php" );
include_once( STDINC."SEEDLocal.php" );

//var_dump($_REQUEST);


if( @$_REQUEST['mode'] == 'REST' ) {
    list($kfdb) = SiteStart();

    $ns   = SEEDSafeGPC_GetStrPlain( 'ns' );
    $k    = SEEDSafeGPC_GetStrPlain( 'k' );
    $lang = SEEDSafeGPC_GetStrPlain( 'lang' );

    $o = new SEEDLocalDB( $kfdb, $lang, $ns, $raParms = array() );
    $lookup = $o->SLookupDB( $ns, $k );
    if( $lookup['code'] == SEEDLocal::CODE_FOUND ) {
        // Don't convert to entities because the content is often meant to be used by the client as html ('<' has to stay non-entity).
        // The en and fr content had better be well formed if we ever use a proper xml parser.
        echo "<SEEDLocal:str ver='1'>"
            ."<SEEDLocal:en>".$lookup['EN']."</SEEDLocal:en>"
            ."<SEEDLocal:fr>".$lookup['FR']."</SEEDLocal:fr>"
            ."<SEEDLocal:ct>".$lookup['content_type']."</SEEDLocal:ct>"
            ."</SEEDLocal:str>";
    } else {
        echo "<SEEDLocal:error ver='1'>".$lookup['code']."</SEEDLocal:error>";
    }
    exit;
}


include_once( SEEDCOMMON."console/console01kfui.php" );
include_once( STDINC."SEEDEditor.php" );


list($kfdb, $sess, $dummyLang) = SiteStartSessionAccount( array("Traductions" => "W") );


if( STD_isLocal && ($nsKill = @$_REQUEST['killkill_localonly']) ) {
    // only on development machines, this wipes SEEDLocal
    $sql = "DELETE FROM SEEDLocal WHERE ns='".addslashes($nsKill)."'";
    echo $sql;
    $kfdb->Execute( $sql );
}



class MyConsole extends Console01KFUI
{
    function __construct( KeyFrameDB $kfdb, SEEDSessionAccount $sess, $raParms ) { parent::__construct( $kfdb, $sess, $raParms ); }

    function mainTraductionsInit()
    {
        $kfrel = new KeyFrameRelation( $this->kfdb,
                                       array( "Tables" => array( array( "Table" => 'SEEDLocal',
                                                                        "Type"  => 'Base',
                                                                        "Fields" => "Auto" ) ) ),
                                       $this->sess->GetUID(),
                                       array( "logfile" => SITE_LOG_ROOT."traductions.log" ));
        $raCompParms = array(
            "Label" => "Traduction",
            "ListCols" => array( array( "label"=>"App",     "colalias"=>"ns",    "w"=>30,   "colsel" => array() ),
                                 array( "label"=>"Key",     "colalias"=>"k",     "w"=>120),
                                 array( "label"=>"FR",      "colalias"=>"fr",    "w"=>150,  "trunc" => 30 ),
                                 array( "label"=>"EN",      "colalias"=>"en",    "w"=>150,  "trunc" => 30 ),
                               ),
            "ListSize" => 15,
            "ListSizePad" => 1,
            "fnFormDraw" => "TraductionsForm",
            "fnListRowTranslateRA" => "TraductionsListRowTranslateRA",
            "bReadonly"=> !($this->sess->CanWrite( "Traductions" ))
        );

        $this->CompInit( $kfrel, $raCompParms );
    }

    function TabSetControlDraw( $tsid, $tabname )
    {
        if( STD_isLocal && $tsid == 'main' && $tabname == 'Traductions' ) {
            $raNS = $this->kfdb->QueryRowsRA( "SELECT distinct(ns) FROM SEEDLocal" );
            $raOpts = array();
            foreach( $raNS as $ra ) {
                $raOpts[$ra[0]] = $ra[0];
            }

            return( "<FORM action='${_SERVER['PHP_SELF']}' method='get'>"
                   ."<INPUT type='submit' value='Dev Only : Clear all Strings in Namespace'/>"
                   .SEEDForm_Select2( 'killkill_localonly', $raOpts )
                   ."</FORM>" );
        }
        return( "" );
    }

    function TabSetContentDraw( $tsid, $tabname )
    {
        if( $tsid == 'main' && $tabname == 'Traductions' ) {
            return( $this->CompListForm_Horz() );
        }
        return( "" );
    }

}

$raConsoleParms = array(
    'HEADER' => "Traductions",
    'CONSOLE_NAME' => "Traductions",
    'TABSETS' => array( "main" => array( 'tabs'=> array( "Traductions" => array('label' => "Traductions") ) ) ),
    'bLogo' => true,
    'bBootstrap' => true,
);

$oC = new MyConsole( $kfdb, $sess, $raConsoleParms );

header( "Content-type: text/html; charset=ISO-8859-1");    // this should be on all pages so accented chars look right (on Linux anyway)

echo $oC->DrawConsole( "[[TabSet: main]]" );

function TraductionsForm( $oForm )
{
    $s = "<table cellpadding='0' cellspacing='5' style='margin:10px 20px'>"
        .$oForm->ExpandForm(
            "||| Application || [[ns]] || {width='30'} &nbsp; || &nbsp;    || <INPUT type='submit' value='Save'/>"
           ."||| Key         || [[k]]  || &nbsp;              || Text type || ". $oForm->Select2( 'content_type', array( 'Plain'=>'PLAIN','HTML'=>'HTML') )
         )
        ."</table>";

    if( $oForm->Value('content_type') == 'HTML' ) {
        // French
        $oEdit = new SEEDEditor( "TinyMCE" );
        $oEdit->SetFieldName( $oForm->Name( 'fr' ) );
        $oEdit->SetContent( $oForm->Value('fr') );
        $s .= "<DIV style='margin:10px 20px'>Fran&ccedil;ais<BR/>"
             .$oEdit->Editor( array('controls'=>'Joomla', 'width_css'=>'100%', 'height_px'=>200) )
             ."</DIV>";
        // English
//        $oEdit2 = new SEEDEditor( "TinyMCE" );
        $oEdit->SetFieldName( $oForm->Name( 'en' ) );
        $oEdit->SetContent( $oForm->Value('en') );
        $s .= "<DIV style='margin:10px 20px'>English<BR/>"
             .$oEdit->Editor( array('controls'=>'Joomla', 'width_css'=>'100%', 'height_px'=>200) )
             ."</DIV>";
    } else {
        $s .= "<DIV style='margin:10px 20px'>".$oForm->TextArea( 'fr', "Fran&ccedil;ais", 40, 4, array('width'=>'100%') )."</DIV>"
             ."<DIV style='margin:10px 20px'>".$oForm->TextArea( 'en', "English", 40, 4, array('width'=>'100%') )."</DIV>";
    }

    $s .= "<DIV style='margin:10px 20px'>".$oForm->TextArea( 'comments', "Comments", 40, 4, array('width'=>'100%') )."</DIV>";

    return( $s );
}

function TraductionsListRowTranslateRA( $raRow )
{
    $raRow['en'] = strip_tags( $raRow['en'] );
    $raRow['fr'] = strip_tags( $raRow['fr'] );
    return( $raRow );
}

?>
