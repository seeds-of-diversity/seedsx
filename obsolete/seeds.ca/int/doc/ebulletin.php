<?php

define( "SITEROOT", "../../");
include_once( SITEROOT."site.php" );
include_once( SEEDCOMMON."siteStart.php" );
include_once( SEEDCOMMON."console/console01.php" );
include_once( SEEDCOMMON."doc/docutil.php" );



// Access to the application is given if any of the tabs are accessible
// Inaccessible tabs are Ghosted
$raPerms = array( 'eBulletin' => array("DocRepMgr"=>"W") );

list($kfdb, $sess) = SiteStartSessionAuth( $raPerms );

header( "Content-type: text/html; charset=ISO-8859-1");    // this should be on all pages so accented chars look right (on Linux anyway)



class MyConsole extends Console01
{
    public $oW;    // this is the Worker class (whichever worker the current tab needs)

    function MyConsole( &$kfdb, &$sess, &$raParms )
    {
        parent::__construct( $kfdb, $sess, $raParms );
    }

    function TabSetInit( $tsid, $tabname )
    {
        if( $tsid == 'main' ) {
        	switch( $tabname ) {
                case 'eBulletin':     $this->oW = new eBulletin( $this, $this->kfdb, $this->sess );  break;
        	}
            if( $this->oW ) $this->oW->Init();
        }
    }

    function TabSetPermission( $tsid, $tabname )
    {
        global $raPerms;

        // is_array is necessary below because testing ($ra=@...) fails when it gets empty array - you'd think any valid array would be non-null even if empty

        return( ($tsid == 'main' && is_array($ra = @$raPerms[$tabname]) && $this->sess->TestPermRA( $ra ))
                ? Console01::TABSET_PERM_SHOW
                : Console01::TABSET_PERM_GHOST );
    }

    function TabSetControlDraw( $tsid, $tabname )
    {
        if( $tsid == 'main' ) {
            switch( $tabname ) {
        	    case 'Users':
        	        $mymode = $this->oComp->oForm->CtrlGlobal('persist_mymode');
        	        return( "<TABLE><TR valign='top'><TD>"
        	               .$this->oComp->SearchToolDraw( array('mymode'=>$mymode) ) // user-defined global control parms have to be specified here
        	               ."</TD><TD>".SEEDStd_StrNBSP("",20)
        	               ."</TD><TD>"
        	               .$this->oComp->ControlFormDraw(
        	                       $this->oComp->oForm->Select2( 'persist_mymode', array("Mode 1"=>"Mode 1","Mode 2"=>"Mode 2"), "",
        	                                                     array( 'selected' => $mymode,
                                                                        'sfParmType' => 'ctrl_global',
                                                                        'attrs' => "onchange='submit()'")),
                                                                 array( 'persist_mymode'=>NULL ) )  // tell the control form not to propagate this parm in <HIDDEN>

        	               ."</TD></TR></TABLE>" );
        	    default:
        	        break;
        	}
        }
        return( "" );
    }

    function TabSetContentDraw( $tsid, $tabname )
    {
        if( $tsid == 'main' ) {
        	switch( $tabname ) {
                case 'eBulletin':
                    return( $this->oW->ContentDraw() );
        	}
        }
        return( "" );
    }

}



/*******************************************************/


class eBulletin extends Console01_Worker1
{

    private $oDocRepDB;

    function __construct( &$oC, &$kfdb, &$sess )
    {
        parent::__construct( $oC, $kfdb, $sess );
    }

    function Init()
    {
        $this->oDocRepDB = New_DocRepDB_WithMyPerms( $this->kfdb, $this->sess->GetUID(), array() );
    }

    function ContentDraw()
    {
        $s = "";

        $s .= "<table width='100%' border='0' cellspacing='0' cellpadding='0'>"
             ."<tr valign='top'>"
             ."<td width='20%' style='border-right: 1px solid #777'>".$this->drawBullIndex()."</td>"
             ."<td style='padding-left:10px'>".$this->drawBullEdit()."</td>"
             ."</tr></table>";

        return( $s );
    }

    function drawBullIndex()
    {
        $s = "";

        $kEbull = $this->oDocRepDB->GetDocFromName( 'root/web/ebulletin' );
        $raDoc = $this->oDocRepDB->ListChildTree( $kEbull, '', 1 ); // $sCond = "", $raKFRParms = array() )

        foreach( $raDoc as $k => $doc ) {
            $s .= "<p style='font-size:small'>".$doc['doc']['name']."</p>";
        }

        return( $s );
    }

    function drawBullEdit()
    {
        $s = "";

        $s .= "Edit";

        return( $s );
    }
}


$raConsoleParms = array(
    'HEADER' => "Seeds of Diversity eBulletin",
    'CONSOLE_NAME' => "eBulletin",
    'TABSETS' => array( "main" => array( 'tabs'=> array( "eBulletin"  => array( 'label' => "eBulletin" ) ) ) ) );

$oC = new MyConsole( $kfdb, $sess, $raConsoleParms );

echo $oC->DrawConsole( "[[TabSet: main]]" );

?>