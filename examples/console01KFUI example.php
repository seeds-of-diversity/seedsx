<?php

if( !defined("SITEROOT") )  define("SITEROOT", "../office/");
include_once( SITEROOT."site.php" );


include_once( SEEDCOMMON."console/console01kfui.php" );


// Access to the application is given if any of the tabs are accessible
// Inaccessible tabs are Ghosted
$raPerms = array( 'Users'    => ['R MBR'],    // you probably have this perm
                  'Public'   => [],           // no perms needed
                  'Ghost'    => ['W DOES-NOT-EXIST'],
                               '|'   // the above are disjunctions for application access
);

list($kfdb2, $sess) = SiteStartSessionAccount( $raPerms );
$kfdb1 = SiteKFDB( SiteKFDB_DB_seeds1 ) or die( "Cannot connect to database" );
$bReadonly = !($sess->CanWrite( "MBR" ));

header( "Content-type: text/html; charset=ISO-8859-1");    // this should be on all pages so accented chars look right (on Linux anyway)



class MyConsole extends Console01KFUI
{
    public $oW;    // this is the Worker class (whichever worker the current tab needs)

    function __construct( &$kfdb, &$sess, &$raParms )   // kfdb is kfdb2
    {
        parent::__construct( $kfdb, $sess, $raParms );
    }

    function TabSetInit( $tsid, $tabname )
    {
        if( $tsid == 'main' ) {
        	switch( $tabname ) {
                case 'Users':     $this->oW = new exampleClass_Users( $this, $this->kfdb, $this->sess );  break;
                case 'Public':    global $kfdb1; $this->oW = new exampleClass_Public( $this, $kfdb1, $this->kfdb, $this->sess );  break;
                case 'Ghost':     die( "How did you get to the Ghost init?" );
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
                case 'Users':
                    $mymode = $this->oComp->oForm->CtrlGlobal('persist_mymode');
                    return( "<P>Using Mode : $mymode</P>".$this->CompListForm_Vert() );
                case 'Public':
                    return( $this->oW->ContentDraw() );
        	}
        }
        return( "" );
    }

}



/*******************************************************/

class exampleClass_Users extends Console01_Worker1
{
    function __construct( &$oC, &$kfdb, &$sess )
    {
        parent::__construct( $oC, $kfdb, $sess );
    }

    function Init()
    {
        SEEDSessionAuthStatic::Init( $this->kfdb, $this->sess->GetUID() );
        $kfrel = SEEDSessionAuthStatic::KfrelUsers();
        $raCompParms = array(
            "Label" => "User",
            "ListCols" => array( array( "label"=>"User #",     "colalias"=>"_key",     "w"=>20 ),
                                 array( "label"=>"Real name",  "colalias"=>"realname", "w"=>150 ),
                                 array( "label"=>"Email",      "colalias"=>"email",    "w"=>150 ),
                                 array( "label"=>"Language",   "colalias"=>"lang",     "w"=>150, "trunc" => 30 ),
                               ),
            "ListSize" => 15,
            "ListSizePad" => false,
            "fnFormDraw" => array($this,"drawForm"),
            "fnListRowTranslateRA" => array($this,"listRowTranslateRA"),
            "bReadonly"=> !($this->sess->CanWrite( "MBR" ))
        );

        $this->oC->CompInit( $kfrel, $raCompParms );
    }

    function drawForm( $oForm )
    {
        $raP = array( 'size' => 40 );

        $s = "<TABLE border='0' cellpadding='0' cellspacing='0' width='90%' align='center'>"
            ."<TR valign='top'>"
            .$oForm->TextTD( '_key', "Contact #",
                             $this->sess->CanAdmin('MBR') ? array('sRightTail'=>" (Admin)", 'size'=>10 ) :
                                                            array('readonly'=>true) )
            ."<TD>&nbsp;</TD><TD align='center'><INPUT type='submit' value='Save'></TD>"
            ."</TR>"
            ."<TR valign='top'>"
            .$oForm->TextTD( 'realname', "Name", $raP )
            ."</TR>"
            ."<TR valign='top'>"
            .$oForm->TextTD( 'email', "Email", $raP )
            ."</TR>"
            ."<TR valign='top'>"
            .$oForm->TextTD( 'lang', "Language", array( 'size'=>15 ) )
            ."</TR>"
            ."</TABLE>"
            ."<INPUT type='submit' value='Save'>";

        return( $s );
    }

    function listRowTranslateRA( $raValues )
    /***************************************
     */
    {
	    if( @$raValues['birthday'] == '1967' ) {
	        $raValues['birthday'] = "Centennial";
    	}

        return( $raValues );
    }
}


class exampleClass_Public extends Console01_Worker2
{
	public  $yCurrent;
    private $oMbrDB;

    function __construct( &$oC, &$kfdb1, &$kfdb2, &$sess )
    {
        parent::__construct( $oC, $kfdb1, $kfdb2, $sess );
    }

    function Init()
    {
    }

    function ContentDraw()
    {
        $s = "";

        $oSessDB = new SEEDSessionAuthDB( $this->kfdb1, 0 );  //uid==0 because this is read only
        $raAccounts = $oSessDB->GetUsersFromGroup( 2 );

        $s .= implode( ', ', $raAccounts );
        return( $s );
    }
}


$raConsoleParms = array(
    'HEADER' => "Console KFUI example",
    'CONSOLE_NAME' => "KFUI_example",
    'TABSETS' => array( "main" => array( 'tabs'=> array( "Users"  => array( 'label' => "Users" ),
                                                         "Public" => array( 'label' => "Public" ),
                                                         "Ghost"  => array( 'label' => "Ghost" ) ) ) )
);

$oC = new MyConsole( $kfdb2, $sess, $raConsoleParms );

echo $oC->DrawConsole( "[[TabSet: main]]" );

?>