<?
/*
    Serve PUB or maxver pages of the English Home Page


    if defined DOCWEBSITE_TEST_MAXVER
        Requires login to DocRepMgr=>R
    else
        Requires no login

    DocRep=>R       - you can read DocRep PUB pages as served by this script
    DocRepMgr=>R    - you can read DocRep Maxver pages as served by this script
    DocRepMgr=>W    - you can edit DocRep (using a different script)
 */

if( !defined("SITEROOT") )  define("SITEROOT", "./");
include_once( SITEROOT."site.php" );
include_once( STDINC."SEEDDate.php" );  // SEEDDateDB2Str
include_once( SITEROOT."page/page1docweb.php" );
include_once( SEEDCOMMON."ev/_ev.php" );

//$lang = site_define_lang();
$lang = "EN";

if(@$_REQUEST['test']==1) define("DOCWEBSITE_TEST_MAXVER",1);


$kfdb = SiteKFDB() or die( "Cannot connect to database" );


class MyEV extends EV_Events
/* EV_Events fetches events and calls virtual method Write() for each.
 * This class writes the events to sOut
 */
{
    // public
    var $sOut = "";
    // private
    var $lang;
    var $prevDate = "";

    function MyEV( &$kfdb, $lang = "EN" ) { $this->EV_Events( $kfdb ); $this->lang = $lang; }

    function Write( $kfr ) {
        $alt = $kfr->value('date_alt'.($this->lang=='FR' ? '_fr' : ''));
        $d = !empty($alt) ? $alt : SEEDDateDB2Str( $kfr->value('date_start'), $this->lang );

        if( $d != $this->prevDate ) {
            $this->sOut .= "<A HREF='events'>$d</A><br/>";
            $this->prevDate = $d;
        }
        $this->sOut .= "<B>".$this->GetTitle( $kfr )."</B><br/>";
        if( $kfr->Value('type') == 'EV' ) {
            $this->sOut .= $kfr->value('city').", ".$kfr->value('province')."<br/>";
        }
        $this->sOut .= "<br/>";
    }
}


$raDWparms = array( "lang"    => $lang,
             "docid_home" => ($lang == "FR" ? "web/main/fr/home" : "web/main/en/home"),
             "docid_root" => ($lang == "FR" ? "web/main/fr" : "web/main/en"),
             "docid_extroots" => array( "web/main/img", "web/main/doc" ),
         //    "vars" => array("dr_template" => "web/main/template01"),     // this can be overridden by var "dr_template" in any page or folder
             "bDirHierarchy" => true,
);


//$raDWparms = array( "lang"    => $lang,
//                    "docid_home" => ($lang == "FR" ? "homefr_home" : "home_home"),
//                    "docid_root" => ($lang == "FR" ? "homefr_rootfolder" : "home_rootfolder"),
//                   "docid_extroots" => array( "main_web_image_root" ),
//                  );

$page1parms = array( "lang" => $lang,
                     "title" => $lang ? "Home" : "Accueil",
                     "tabname"   => "Home",
                     "box1title" => "What's New",
                     "box1fn"    => "box1fn",
                     "box2title" => "Contact Us",
                     "box2fn"    => "box2fn",
             );



class myDocRepWiki extends DocRepWiki
{
    function myDocRepWiki( $oDocRepDB, $sFlag, $raDRWparms ) { $this->DocRepWiki( $oDocRepDB, $sFlag, $raDRWparms ); }

    function HandleLink( $raLink )
    {
        global $kfdb;

        if( $raLink['namespace'] == 'myEventList' ) {
            $oEV = new MyEV( $kfdb );
            $oEV->FetchFuture( 100 );
            return( $oEV->sOut."<BR/><BR/><A href='".EV_ROOT."events.php'>Details and more events...</DIV></TD></TR>" );
        }
        if( $raLink['namespace'] == 'SITEROOT' ) {
            return( SITEROOT );
        }
        return( parent::HandleLink( $raLink ) );
    }
}



class myWebsite extends Page1DocWebsite
{
    function myWebsite( $raDWparms, $page1parms ) { $this->Page1DocWebsite( $raDWparms, $page1parms ); }

    function factory_DocRepWiki( $raDRWparms )
    {
        $oDocRepWiki = new myDocRepWiki( $this->oDocRepDB, $this->raParms['dr_flag'], $raDRWparms );
        return( $oDocRepWiki );
    }
}


$oD = new myWebsite( $raDWparms, $page1parms );
$oD->Go();


function box1fn() {
    return(
//   "<div><a href='".SITEROOT."ev/events.php'><B>NEW</B> Seedy Saturdays and Sundays 2008</a></div>"
     "<div><a href='".SITEROOT."sl/csci'>Canadian Seed Catalogue Inventory</a></div>"
    ."<div><a href='".SITEROOT."rl/rl.php'>List of Heritage Seed Companies</a></div>"
    ."<div><a href='".SITEROOT."proj/tomato/'>Canadian Tomato Project</a></div>"
    ."<div><a href='".SITEROOT."proj/poll/'>Pollination Canada</a></div>" );
}

// TODO:  Move this to a global function
//        This box is identical in info-about
//        Also add a parm to the function that causes it to return the box's title (can be overridden in Page1 parms)
function box2fn() {
    return(
         "<div>". SEEDStd_EmailAddress( "mail", "seeds.ca", "", array("subject"=>"Question for Seeds of Diversity") ) ."</div>"
        ."<div><a href='mbr/member.php'>How to Join</a></div>"
        ."<div><a href='mbr/member.php'>Order our Publications</a></div>"
        ."<div><a href='bulletin/'>Subscribe to our free email Bulletin</a></div>" );
}

?>
