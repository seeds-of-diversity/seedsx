<?php

/*

-----


sed_curr_seeds and sed_curr_growers are exactly the same schema of these tables, but contain working data for the next SED.
Their contents should be the same as the previous year's data (including the same _keys) with working changes applied.
Rows inserted into sed_curr_* must use _key's > those in sed_seeds/growers.
Updates can be found by comparing data between tables.

four cols are added to each sed_curr_* for editing workflow
bSkip    - initially 0, set to 1 if skip    - this persists year to year
bDelete  - initially 0, set to 1 if delete
bChanged - initially 0, set to 1 if row altered

one col is added to sed_curr_grower for editing workflow
bDone    - initially 0, set to 1 when complete


** do not zero bSkip, do not drop sed_curr_seeds, because skip flags are kept there from year to year

To initialize editing process:
Backup sed_curr_*
UPDATE sed_curr_* SET bDelete=0,bChanged=0,bDone=0

During editing process
bSkip and bDelete can be toggled at will
bChanged flags the rows where changes or additions happen (proofread these)
Check off bDone when each grower is complete

To finalize editing process:
Backup sed_curr_*
Make sure all growers have a valid mbr_id
Make sure all growers have a valid mbr_code - until converted to KF, copy G.mbr_code to S.mbr_code
Link grower addresses to mbr_contact, or verify that they are the same
Remove old entries
    count the printouts and match them to growers.bDone
    set growers.bSkip=1 where bDone=0   -- or bDelete=1 if they're confirmed to be ex-growers
    update sed_curr_seeds S,sed_curr_growers G set S.bSkip=1 where S.mbr_id=G.mbr_id and G.bSkip;
    update sed_curr_seeds S,sed_curr_growers G set S.bDelete=1 where S.mbr_id=G.mbr_id and G.bDelete;
    make sure that there aren't any seeds without growers
    look at growers without seeds and decide whether to list them

(optional) Use KF to hide the skipped/deleted entries
    UPDATE sed_curr_* SET _status=1 WHERE bDelete=1     // or DELETE FROM sed_curr_* where bDelete=1;
    UPDATE sed_curr_* SET _status=2 WHERE bSkip=1       // or DELETE FROM sed_curr_* where bDelete=1;

It is safe (re accents and punctuation) to issue UPDATE sed_curr_seeds SET variety=upper(variety);


Set year in sed_curr_*
Set year_1st_listed where 0
** Add up totals in sed_curr_grower
** Evaluate conservation status for all sed_curr_seeds


To propagate sed_curr_* to sed_*
Copy all data, except workflow columns, where bSkip=0 and bDelete=0



*/


include_once( SEEDCOMMON."console/console01.php" );

//TODO: change mbr_id to fk_mbr_contacts
//      this won't affect any kfrel that don't use mbr_contacts so it's safe in seeds.ca, but it will be useful for office applications
//      consider changing S.mbr_id to S.fk_sed_curr_growers (except then it isn't consistent with sed_seeds)
$kfFields_S = array( array("col"=>"mbr_id",          "type"=>"I"),
                     array("col"=>"category",        "type"=>"S"),
                     array("col"=>"type",            "type"=>"S"),
                     array("col"=>"variety",         "type"=>"S"),
                     array("col"=>"bot_name",        "type"=>"S"),
                     array("col"=>"days_maturity",   "type"=>"S"),
                     array("col"=>"quantity",        "type"=>"S"),
                     array("col"=>"origin",          "type"=>"S"),
                     array("col"=>"year_1st_listed", "type"=>"I"),
                     array("col"=>"description",     "type"=>"S"),
                     array("col"=>"year",            "type"=>"I"),
                     array("col"=>"bSkip",           "type"=>"I"),
                     array("col"=>"bDelete",         "type"=>"I"),
                     array("col"=>"bChanged",        "type"=>"I") );

$kfFields_G = array( array("col"=>"mbr_id",          "type"=>"I"),  // could be fk_mbr_contacts (won't impact kfrel that don't involve mbr_contacts)
                     array("col"=>"mbr_code",        "type"=>"S"),  // keep this here instead of mbr_contacts so sed_seeds has a record of province
                     array("col"=>"frostfree",       "type"=>"S"),
                     array("col"=>"soiltype",        "type"=>"S"),
                     array("col"=>"organic",         "type"=>"I"),
                     array("col"=>"zone",            "type"=>"S"),
                     array("col"=>"notes",           "type"=>"S"),
                     array("col"=>"unlisted_phone",  "type"=>"I"),
                     array("col"=>"unlisted_email",  "type"=>"I"),
                     array("col"=>"cutoff",          "type"=>"S"),
                     array("col"=>"pay_cash",        "type"=>"I"),
                     array("col"=>"pay_cheque",      "type"=>"I"),
                     array("col"=>"pay_stamps",      "type"=>"I"),
                     array("col"=>"pay_ct",          "type"=>"I"),
                     array("col"=>"pay_mo",          "type"=>"I"),
                     array("col"=>"pay_other",       "type"=>"S"),
                     array("col"=>"nTotal",          "type"=>"I"),
                     array("col"=>"nFlower",         "type"=>"I"),
                     array("col"=>"nFruit",          "type"=>"I"),
                     array("col"=>"nGrain",          "type"=>"I"),
                     array("col"=>"nHerb",           "type"=>"I"),
                     array("col"=>"nTree",           "type"=>"I"),
                     array("col"=>"nVeg",            "type"=>"I"),
                     array("col"=>"nMisc",           "type"=>"I"),
                     array("col"=>"bSkip",           "type"=>"I"),
                     array("col"=>"year",            "type"=>"I"),
                     array("col"=>"bDelete",         "type"=>"I"),
                     array("col"=>"bChanged",        "type"=>"I"),
                     //array("col"=>"bDone",           "type"=>"I"),  obsolete
                     array("col"=>"bDoneMbr",        "type"=>"I"),
                     array("col"=>"bDoneOffice",     "type"=>"I") );


$kfrelDef_SEDCurrGrowersXContacts =    // Need to create cursor with G.mbr_id=M._key
    array( "Tables"=>array( array( "Table" => 'seeds.sed_curr_growers',
                                   "Type" => "Base",
                                   "Alias" => "G",
                                   "Fields" => $kfFields_G ),
                            array( "Table"=> 'seeds2.mbr_contacts',
                                   "Type" => "Related",
                                   "Alias" => "M",
                                   "Fields" => array( array("col"=>"firstname",       "type"=>"S"),
                                                      array("col"=>"lastname",        "type"=>"S"),
                                                      array("col"=>"firstname2",      "type"=>"S"),
                                                      array("col"=>"lastname2",       "type"=>"S"),
                                                      array("col"=>"company",         "type"=>"S"),
                                                      array("col"=>"dept",            "type"=>"S"),
                                                      array("col"=>"address",         "type"=>"S"),
                                                      array("col"=>"city",            "type"=>"S"),
                                                      array("col"=>"province",        "type"=>"S"),
                                                      array("col"=>"postcode",        "type"=>"S"),
                                                      array("col"=>"country",         "type"=>"S"),
                                                      array("col"=>"phone",           "type"=>"S"),
                                                      array("col"=>"email",           "type"=>"S"),
                                                      array("col"=>"lang",            "type"=>"S"),
                                                      array("col"=>"expires",         "type"=>"S") ) ),
                                    ) );


$kfrelDef_SEDCurrSeedsXGrowers =    // Need to create cursor with S.mbr_id=G.mbr_id
    array( "Tables"=>array( array( "Table" => 'seeds.sed_curr_seeds',
                                   "Type" => "Base",
                                   "Alias" => "S",
                                   "Fields" => $kfFields_S ),
                            array( "Table"=> 'seeds.sed_curr_growers',
                                   "Type" => "Parent",
                                   "Alias" => "G",
                                   "Fields" => $kfFields_G ) ) );


function sed_style()
/*******************
 */
{
    $s = "<STYLE>"
        .".sed_growers    { }"
        .".sed_grower     { padding:5px; }"
        .".sed_grower_skip  { color:gray; background-color:#ddd; }"
        .".sed_grower_delete{ color:red;  background-color:#fdf; }"
        .".sed_grower_done{ border:15px solid #9d9; }"
        .".sed_categories { font-family:verdana,helvetica,sans-serif; font-size:11pt;}"
        .".sed_types      { }"
        .".sed_typesfull  { }"

        ."</STYLE>";

    return( $s );
}


function sed_style_report()
/**************************
 */
{
    $s = "<STYLE>"
        .".sed_growers    { }"
        .".sed_grower     { font-size: 10pt; width:60%; }"
        .".sed_grower_skip  { color:gray; }"
        .".sed_grower_delete{ color:red; }"
        .".sed_grower_done{ background-color:#99DD99; }"
        .".sed_categories { }"
        .".sed_types      { }"
        .".sed_typesfull  { }"
        .".sed_type       { }"
        .".sed_type h3    { font-family:helvetica,sans-serif; }"
        .".sed_seed       { width:60%; }"
        .".sed_seed_skip  { color:gray; }"
        .".sed_seed_delete{ color:red; }"
        .".sed_seed_change{ background-color:#99DD99; }"
        .".sed_seed_mc    { float:right; }"
        .".sed_seed_form  { }"
        ."</STYLE>";
    return( $s );
}


/*****
    This design might benefit from some rethinking.  oSedList creates oGList and oSList, which use oSedList's methods to draw stuff, and those
    same methods use oGList and oSList.  So there are tightly linked objects pointing to each other.  It's essential that oSedList be passed by
    reference, because the links won't be made properly if it's copied.  Which caused a bug on php4, though it actually doesn't matter on php5
    since objects are always passed by reference.
****/

class SeedConsoleLE extends Console01_ListEdit
{

}




class GConsoleListEdit extends Console01_ListEdit
{
    var $oSedList;
    function __construct( $oSedList )
    {
        $this->oSedList = $oSedList;
        // Though only one ConsoleListEdit is drawn at a time, set them both up with different parm names so they can issue links to each other
        parent::__construct( $oSedList->oSed->kfrelG, array( 'httpNameSuffix'=>'G') );
    }

    function DrawListItem( $kfrc, $raParms )
    {
        return( $this->oSedList->drawGrower( $kfrc, $raParms['bGFull'] ) );
    }

    function DrawListForm( $kfrc, $raParms )
    {
        return( $this->oSedList->geditForm( $this->oKFUForm ) );
    }

    function factory_KeyFrameUIForm( KeyFrameRelation $kfrel )
    {
/* It's probably possible to factor this and the corresponding factory in SEDMbr, but the tricky part is the DSPreStore callback
 */
        // do the right thing when these checkboxes are unchecked (http parms are absent, stored value is 1, so change stored value to 0)
        $kfuFormDef = array('unlisted_phone' => array( 'type'=>'checkbox' ),
                            'unlisted_email' => array( 'type'=>'checkbox' ),
                            'organic'        => array( 'type'=>'checkbox' ),
                            'pay_cash'       => array( 'type'=>'checkbox' ),
                            'pay_cheque'     => array( 'type'=>'checkbox' ),
                            'pay_stamps'     => array( 'type'=>'checkbox' ),
                            'pay_ct'         => array( 'type'=>'checkbox' ),
                            'pay_mo'         => array( 'type'=>'checkbox' ),
                            'bDone'          => array( 'type'=>'checkbox' ) );

        $oKFU = new KeyFrameUIForm( $kfrel, NULL, array( 'formdef' => $kfuFormDef,
                                                         'DSParms'=> array('fn_DSPreStore'=>array(&$this,'grower_DSPreStore')) ) );
        return( $oKFU );
    }

    function grower_DSPreStore( $oDS )
    /*********************************
        This gets called after http parms are loaded into the kfr, and before the record is rewritten to the database.
        Fix things that don't get handled automatically.
        Return true to proceed with the db write.
     */
    {
// TODO: security - make sure the user can edit this grower
        if( !$oDS->Value('year') )  $oDS->SetValue( 'year', $this->oSedList->currentYear );

        $oDS->SetValue('bChanged',1);

        if( !$oDS->Key() ) {
            // Ensure that New Grower exists in mbr_contacts
            if( !$oDS->value('mbr_id') || !($this->oSedList->kfdb2->Query1("SELECT _key FROM mbr_contacts WHERE _key='".$oDS->Value('mbr_id')."'")) ) {
                $this->oSedList->oConsole->ErrMsg( "Member # ".$oDS->Value('mbr_id')." does not exist. Could not add new grower." );
                return( false );
            }
            // Ensure that New Grower is not already in sed_curr_growers
            if( $this->oSedList->kfdb->Query1("SELECT _key FROM sed_curr_growers WHERE mbr_id='".$oDS->value('mbr_id')."'") ) {
                $this->oSedList->oConsole->ErrMsg( "Member # ".$oDS->Value('mbr_id')." is already in this list. Could not add new grower." );
                return( false );
            }
        }
        return( true );
    }
}


class SConsoleListEdit extends Console01_ListEdit
{
    var $oSedList;
    function __construct( sedList $oSedList )
    {
        $this->oSedList = $oSedList;
        // Though only one ConsoleListEdit is drawn at a time, set them both up with different parm names so they can issue links to each other
        parent::__construct( $oSedList->oSed->kfrelS, array( 'httpNameSuffix'=>'S') );
    }

    function DrawListItem( $kfrc, $raParms )
    {
        return( $this->oSedList->drawSeed( $kfrc, $raParms ) );
    }

    function DrawListForm( $kfrc, $raParms )
    {
        return( $this->oSedList->seditForm( $this->oKFUForm ) );
    }

    function factory_KeyFrameUIForm( KeyFrameRelation $kfrel )
    {
        // set up the Form Updater to call our DSPreStore method before writing the row
        $oKFU = new KeyFrameUIForm( $kfrel, NULL, array('DSParms'=>array('fn_DSPreStore'=>array(&$this,'seed_DSPreStore')) ) );
        return( $oKFU );
    }

    function seed_DSPreStore( $oDS )
    /*******************************
        This gets called after http parms are loaded into the kfr, and before the record is rewritten to the database.
        Fix things that don't get handled automatically.
        Return true to proceed with the db write.
     */
    {
// TODO: security - make sure the user can insert/edit seeds for the current grower. We use seedKGrower because that needs to be validated anyway.
//                  But don't propagate the mbr_id by http
        if( !$oDS->Key() ) {
            /* Adding a new seed listing
             */
            // First make sure that the screen is in seed-by-grower mode, and there's a valid grower shown
            if( $this->oSedList->sess->VarGet('seedMode') != 'seedKGrower' || intval($this->oSedList->oNavSVA->VarGet('seedKGrower')) < 1 ) {
                die( "Cannot add this seed. There is no active grower record." );
            }
            $oDS->SetValue( 'mbr_id', $this->oSedList->oNavSVA->VarGet('seedKGrower') );
            $oDS->SetValue( "year_1st_listed", $this->oSedList->currentYear );
            $oDS->SetValue( 'year', $this->oSedList->currentYear );
        }

        $oDS->SetValue( 'bChanged', 1 );

        return( true );
    }
}

// TODO: Since this is used by mbr_email (at least) it would be nice to segregate it in an sedoffice file, separate from the UI stuff for sedadmin.
include_once( SEEDCOMMON."sl/sed/sedCommon.php" ); // the sedoffice file should include this instead of sedadmin (this line is here for mbr_email)
class SEDOffice extends SEDCommon
{
    protected $kfdb2;

    function __construct( KeyFrameDB $kfdb1, KeyFrameDB $kfdb2, SEEDSessionAccount $sess, $lang, $eReportMode )
    {
        $this->kfdb2 = $kfdb2;
        parent::__construct( $kfdb1, $sess, $lang, $eReportMode );    // user is logged in
    }

    protected function GetMbrContactsRA( $kMbr )
    {
        return( $this->kfdb2->QueryRA( "SELECT * FROM mbr_contacts WHERE _key='$kMbr'" ) );
    }
}


class sedList {
    var $kfdb;
    var $kfdb2;         // seeds2 db, for retrieving mbr_contacts info
    var $sess;
    var $oNavSVA;       // SessionVarAccessor for storing navigational info - some duplicated in forms that use sessions, should be in sync
    var $oConsole;
    var $currentYear = 0;

    var $oSed;

    var $kfrelSxG;

    //var $bReport = false;               // true: output the report format

//    var $_lastCategory = "", $_lastType = "";   // mark boundaries with <H>

    var $oGList = NULL;
    //var $oSList = NULL;

    const cColorOffice = "#afa";
    const cColorMbr    = "#aaf";
    const cColorBoth   = "#fc6";
    const cColorNone   = "#777";

    const cBorderOffice = "5px solid #afa";
    const cBorderMbr    = "5px solid #aaf";
    const cBorderBoth   = "5px solid #fc6";
    const cBorderNone   = "1px solid #777";

    function __construct( KeyFrameDB $kfdb, KeyFrameDB $kfdb2, SEEDSessionAccount $sess, Console01 $oConsole, SEDOffice $oSed, $currentYear, $eReportMode )
    {
        global $kfrelDef_SEDCurrSeedsXGrowers, $kfrelDef_SEDCurrGrowersXContacts;

        $this->kfdb         = $kfdb;
        $this->kfdb2        = $kfdb2;
        $this->sess         = $sess;
        $this->oConsole     = $oConsole;
        $this->currentYear  = $currentYear;

        $this->oSed         = $oSed;  // new SEDOffice( $kfdb, $kfdb2, $sess, "EN", $eReportMode );

        $this->kfrelSxG     = new KeyFrameRelation( $kfdb, $kfrelDef_SEDCurrSeedsXGrowers , $sess->GetUID() );
        $this->kfrelGxC     = new KeyFrameRelation( $kfdb2, $kfrelDef_SEDCurrGrowersXContacts , $sess->GetUID() );

        $this->kfrelSxG->SetLogFile( SITE_LOG_ROOT."sed.log" );
        $this->kfrelGxC->SetLogFile( SITE_LOG_ROOT."sed.log" );

        $this->oGList = new GConsoleListEdit( $this );
        //$this->oSList = new SConsoleListEdit( $this );

        $this->oNavSVA = new SEEDSessionVarAccessor( $sess, 'sedNavGlobal' );
    }

    function update()
    {
        $this->oGList->Update();

        // Toggle the bDone, bSkip, or bDelete flag on a grower record - assume only one command can be issued at a time
        $k = 0;
        if( ($k = SEEDSafeGPC_GetInt( 'gdone' )) ) {
            $flag = 'bDoneOffice';
        } else if( ($k = SEEDSafeGPC_GetInt( 'gskip' )) ) {
            $flag = 'bSkip';
        } else if( ($k = SEEDSafeGPC_GetInt( 'gdel' )) ) {
            $flag = 'bDelete';
        }
        if( $k ) {
            $kfrG = $this->oSed->kfrelG->GetRecordFromDBKey( $k ) or die( "Cannot find grower # $k" );
            $kfrG->SetValue( $flag, !$kfrG->value($flag) );

            if( !$kfrG->PutDBRow() ) {
                die( "<P style='color:red'>Update didn't work.  Please email this message to Bob:</P>".$kfrG->kfrel->kfdb->KFDB_GetErrMsg() );
            }
            $this->oGList->SetScroll( $k );
        }

        // Seed updater is in SEDCommon
        $this->oSed->update();
    }

    private function drawGrowersSidebar()
    {
        $s = "";
        $sErrLogins = "";

//        /* Show warnings for growers who have login accounts, but not "member growers" permissions
//         */
//        $ra = $this->kfdb->QueryRowsRA( "SELECT G.mbr_id,G.mbr_code FROM "
//                                       ."seeds.sed_curr_growers G JOIN seeds.SEEDSession_Users U ON (G.mbr_id=U._key) "
//                                       ."LEFT JOIN seeds.SEEDSession_UsersXGroups S ON (G.mbr_id=S.uid AND S.gid='4') "  // Group 4 is member growers
//                                       ."WHERE S._key is null AND U.gid1<>'4'" );
//        if( count($ra) ) {
//            foreach( $ra as $raMbr ) {
//                $sErrLogins .= "{$raMbr['mbr_code']} ({$raMbr['mbr_id']})<br/>";
//            }
//            $sErrLogins = "<div style='border:1px solid red;border-radius:5px;color:red;padding:10px;'>"
//                         ."<p>These growers don't have logins set up to edit their listings - tell Bob!</p>"
//                         ."<p>$sErrLogins</p></div>";
//        }


            $raProv = array( "AB" => "ALB",
                             "BC" => "B.C",
                             "MB" => "MAN",
                             "NB" => "N.B",
                             "NF" => "NFL",
                             "NS" => "N.S",
                             "ON" => "ONT",
                             "PE" => "PEI",
                             "QC" => "QUE",
                             "SK" => "SAS" );

        $sValid = " and _status=0 and not bSkip and not bDelete";


            $s .= "<STYLE>"
                 ."div.summary, "
                 ."div.summary td,"
                 ."div.summary th"
                      ." { font-family:sans serif;font-size:9pt; }"
                 ."</STYLE>";

            $s .= "<DIV class='summary' style='border:1px solid black;padding:10px;width:15%;float:right;'>"
                 .$sErrLogins
                 ."Borders:"
                 ."<DIV style='border:".self::cBorderMbr."'>Member clicked Done</DIV>"
                 ."<DIV style='border:".self::cBorderOffice."'>Office clicked Done</DIV>"
                 ."<DIV style='border:".self::cBorderBoth."'>Both clicked Done</DIV>"
                 ."<DIV style='border:".self::cBorderNone."'>Nobody clicked Done</DIV>"
                 ."<BR/><BR/>"
                 ."Backgrounds:"
                 ."<DIV style='background-color:#ddd'>Skipped</DIV>"
                 ."<DIV style='background-color:#fdf'>Deleted</DIV>"
                 ."<BR/><BR/>"
                 ."Growers: ".$this->kfdb->Query1( "SELECT count(*) from sed_curr_growers where _status=0" )."<BR/>"
                 ."Skipped: ".$this->kfdb->Query1( "SELECT count(*) from sed_curr_growers where _status=0 and bSkip" )."<BR/>"
                 ."Deleted: ".$this->kfdb->Query1( "SELECT count(*) from sed_curr_growers where _status=0 and bDelete" )."<BR/>"
                 ."<HR/>";

            /* Show How Many are Done
             */
            $s .= "<h3 style='text-align:center'>Done</h3>";
            $raGDoneOffice = $this->kfdb->QueryRowsRA( "SELECT LEFT(mbr_code,3) as p,count(*) as n FROM sed_curr_growers WHERE bDoneOffice $sValid GROUP BY 1" );
            $raGDoneMbr    = $this->kfdb->QueryRowsRA( "SELECT LEFT(mbr_code,3) as p,count(*) as n FROM sed_curr_growers WHERE bDoneMbr $sValid GROUP BY 1" );
            $raGDoneBoth   = $this->kfdb->QueryRowsRA( "SELECT LEFT(mbr_code,3) as p,count(*) as n FROM sed_curr_growers WHERE (bDoneOffice OR bDoneMbr) $sValid GROUP BY 1" );

            $s .= "<TABLE cellpadding='5'>"
                 ."<TR><TH>&nbsp</TH><TH>Office</TH><TH>Member</TH><TH>Total</TH></TR>";
            $nTotalOffice = 0;
            $nTotalMbr = 0;
            $nTotalBoth = 0;
            foreach( $raProv as $k => $v ) {
                $nDoneOffice = 0;
                $nDoneMbr = 0;
                $nDoneBoth = 0;
                foreach( $raGDoneOffice as $ra ) {
                    if( $ra['p'] == $v ) {
                        $nDoneOffice = intval(@$ra['n']);
                        break;
            	    }
                }
                foreach( $raGDoneMbr as $ra ) {
                    if( $ra['p'] == $v ) {
                        $nDoneMbr = intval(@$ra['n']);
                        break;
                    }
                }
                foreach( $raGDoneBoth as $ra ) {
                    if( $ra['p'] == $v ) {
                        $nDoneBoth = intval(@$ra['n']);
                        break;
                    }
                }
                $nTotalOffice += $nDoneOffice;
                $nTotalMbr    += $nDoneMbr;
                $nTotalBoth   += $nDoneBoth;
                $s .= "<TR><TD>$k</TD><TD>$nDoneOffice</TD><TD>$nDoneMbr</TD><TD>$nDoneBoth</TD></TR>";
            }
// Also show how many are not in the raProv array i.e. U.S.A
            $s .= "<TR><TD>&nbsp;</TD><TD style='border-top:1px solid black;'>$nTotalOffice</TD>"
                 ."<TD style='border-top:1px solid black;'>$nTotalMbr</TD>"
                 ."<TD style='border-top:1px solid black;'>$nTotalBoth</TD>"
                 ."</TR>";
            $s .= "</TABLE>Not including U.S."
                 ."</DIV>";

        return( $s );
    }

    function DrawGrowers()
    /*********************
        Show the full list of Growers
     */
    {
        $s = "";

        if( $this->oSed->eReportMode != 'LAYOUT' ) {
            $s .= $this->drawGrowersSidebar();
        }

        // Get list of Canadian growers (except for SoDC)
        $raGCdn = array();
        if( ($kfrGxM = $this->oSed->kfrelGxM->CreateRecordCursor( "G.mbr_id=M._key AND M.country='CANADA' AND G.mbr_id<>1" )) ) {
            while( $kfrGxM->CursorFetch() ) {
                $raGCdn[] = $kfrGxM->value('mbr_id');
            }
            $kfrGxM->CursorClose();
        }

        // Draw Grower controls
        $sGrowerFilter = "";
        $bGFull = true;
        if( $this->oSed->eReportMode != 'LAYOUT' ) {
            $sGrowerFilter = $this->sess->SmartGPC('sGrowerFilter');
            $bGFull = $this->sess->SmartGPC('bGrowerFormat',array(0,1));

            $raSelMbrCode = array(""=>"--- All Growers ---");
            if( ($dbc = $this->kfdb2->CursorOpen( "SELECT LEFT(G.mbr_code,3) AS mbr_code FROM mbr_contacts M, seeds.sed_curr_growers G "
                                                      ."WHERE G.mbr_id=M._key AND M._key <> 1 AND M.country='CANADA' AND G._status=0 "
                                                      ."GROUP BY 1 ORDER BY 1")) ) {
                while( $ra = $this->kfdb2->CursorFetch( $dbc ) ) {
                    $raSelMbrCode[$ra['mbr_code']] = $ra['mbr_code'];
                }
                $this->kfdb2->CursorClose( $dbc );
            }
            $raSelMbrCode['USA'] = 'USA';

            $s .= "<TABLE border='0'><TR><TD valign='top'>"
                 ."<FORM action='${_SERVER['PHP_SELF']}'>"
                 ."Show Growers: ".SEEDForm_Select( 'sGrowerFilter', $raSelMbrCode, $sGrowerFilter, array('selectAttrs'=>"onChange='submit();'") )
                 .SEEDStd_StrNBSP("",10)
                 ."Format: ".SEEDForm_Select( 'bGrowerFormat', array(0=>"Condensed",1=>"Full"), $bGFull, array('selectAttrs'=>"onChange='submit();'") )
                 ."</FORM>"
                 ."</TD<TD valign='top'>"
                 ."<FORM method='post' action='${_SERVER['PHP_SELF']}'>"	// separate form to add new grower
                 .SEEDStd_StrNBSP("",15)
                 .$this->oGList->Hidden('AddButton')
                 ."<INPUT type='submit' name='x' value='Add New Grower'>"
                 ."</FORM></TD></TR></TABLE>";
        }


        $cond = ($this->oSed->eReportMode=='LAYOUT' ? " AND NOT G.bSkip AND NOT G.bDelete" : "");

        if( $this->oSed->eReportMode=='LAYOUT' ) {
            $this->oGList->SetScroll( 0, false );  // these shouldn't be set anyway, but just in case
        }

        $s .= "<DIV class='sed_growers'>";
        if( $this->oSed->eReportMode=='LAYOUT' )  $s .= "<H2>Growers</H2>";

        // Draw Canadian Growers
        if( empty($sGrowerFilter) )  $s .= "<H3>Canada</H3>";

        if( empty($sGrowerFilter) || $sGrowerFilter != 'USA' ) {
            /* SoDC
             */
            if( empty($sGrowerFilter) && ($kfrG = $this->oSed->kfrelGxM->CreateRecordCursor( "(G.mbr_id=M._key) AND G.mbr_id=1".$cond )) ) {
                $s .= $this->oGList->DrawList( $kfrG, array('bGFull'=>$bGFull) );
        // kluge: we have multiple DrawList, and it doesn't know that it should only draw the Add New form the first time
                if( $this->oGList->p_kSelect == 0 )  $this->oGList->p_bEdit = false;
                $kfrG->CursorClose();
            }

            /* Canada
             */
            $condCdn = $cond;
            if( !empty($sGrowerFilter) ) {
                $condCdn .= " AND LEFT(G.mbr_code,3) ='$sGrowerFilter'";
            }
            if( ($kfrG = $this->oSed->kfrelGxM->CreateRecordCursor( "(G.mbr_id=M._key) AND (G.mbr_id IN (".implode(",",$raGCdn).") AND G.mbr_id<>1)".$condCdn, array("sSortCol"=>"G.mbr_code")) ) ) {
                $s .= $this->oGList->DrawList( $kfrG, array('bGFull'=>$bGFull) );
                $kfrG->CursorClose();
        // kluge: we have multiple DrawList, and it doesn't know how that it should only draw the Add New form the first time
                if( $this->oGList->p_kSelect == 0 )  $this->oGList->p_bEdit = false;
            }
        }

        // Draw US Growers
        if( empty($sGrowerFilter) )  $s .= "<H3>U.S.A.</H3>";

        if( empty($sGrowerFilter) || $sGrowerFilter == 'USA' ) {
            if( ($kfrG = $this->oSed->kfrelGxM->CreateRecordCursor( "(G.mbr_id=M._key) AND (G.mbr_id NOT IN (1,".implode(",",$raGCdn)."))".$cond, array("sSortCol"=>"G.mbr_code")) ) ) {
                $s .= $this->oGList->DrawList( $kfrG, array('bGFull'=>$bGFull) );
                $kfrG->CursorClose();
            }
        }
        $s .= "</DIV>";   // sed_growers

        return( $s );
    }


    function drawGrower( &$kfrG, $bGFull = true )
    /********************************************
        Draw the info for the current grower.

        Note that $kfrG is actually Grower x Contacts
     */
    {
        $s = "";

        if( $this->oSed->eReportMode == 'LAYOUT' ) {
            $s .= "<DIV class='sed_grower'>";
        } else {
            if( $kfrG->Value('bDoneOffice') && $kfrG->Value('bDoneMbr') ) {
                $sBorder = self::cBorderBoth;
            } else if( $kfrG->Value('bDoneOffice') ) {
                $sBorder = self::cBorderOffice;
            } else if( $kfrG->Value('bDoneMbr') ) {
                $sBorder = self::cBorderMbr;
            } else {
                $sBorder = self::cBorderNone;
            }

            $s .= "<DIV class='sed_grower' style='border:$sBorder;' id='Grower".$kfrG->Key()."'>";

            if( $kfrG->value('bDelete') ) {
                $s .= "<DIV class='sed_grower_delete'>"
                     ."<B><I>Deleted</I></B><BR/>";
            } else if( $kfrG->value('bSkip') ) {
                $s .= "<DIV class='sed_grower_skip'>"
                     ."<B><I>Skipped</I></B><BR/>";
            } else {
                $s .= "<DIV>";
            }

            if( $this->oSed->eReportMode == 'EDIT' ) {
                $s .= "<SPAN style='float:left'>"
                     .SEEDStd_StrNBSP("",3)
                     .$this->oGList->ExpandTags( $kfrG->Key(), "<A HREF='{$_SERVER['PHP_SELF']}?[[LinkParmEdit]]' style='color:red'>[Edit Grower]</A>" )
                     .SEEDStd_StrNBSP("",5)
                     ."<A HREF='{$_SERVER['PHP_SELF']}?c01tf_main=Seeds&sfSp_mode=grower&sfSp_kGrower=".$kfrG->value('mbr_id')."'"
                         ." style='color:blue'>[Edit this Grower's Seeds]</A>"
                     ."</SPAN><SPAN style='float:right'>"
                     ."<A HREF='{$_SERVER['PHP_SELF']}?gdone=".$kfrG->Key()."' style='color:red'>[".($kfrG->value('bDone')?"Un-":"")."Done]</A>"
                     .SEEDStd_StrNBSP("",3)
                     ." <A HREF='{$_SERVER['PHP_SELF']}?gskip=".$kfrG->Key()."' style='color:red'>[".($kfrG->value('bSkip')?"Un-":"")."Skip]</A>"
                     .SEEDStd_StrNBSP("",3)
                     ." <A HREF='{$_SERVER['PHP_SELF']}?gdel=".$kfrG->Key()."' style='color:red'>[".($kfrG->value('bDelete')?"Un-":"")."Delete]</A>"
                     ."</SPAN><BR/>";
            }

            $yExpires = intval(substr($kfrG->value('M_expires'), 0, 4));
            $sStyle = $yExpires>=($this->currentYear-1) ? "" : "background-color:#f55;font-weight:bold;color:black";
       	    $s .= "<BR/><SPAN style='$sStyle'>[$yExpires]</SPAN> ";
        }

        $s .= $this->oSed->drawGrowerBlock( $kfrG, $bGFull );

        if( $this->oSed->eReportMode != 'LAYOUT' ) {
            $dLogin = $this->kfdb->Query1( "SELECT max(_created) FROM seeds.SEEDSession WHERE uid='".$kfrG->Value('mbr_id')."'" );
            $dMbrGUpdate = $this->kfdb->Query1( "SELECT max(_updated) FROM seeds.sed_curr_growers "
                                               ."WHERE mbr_id='".$kfrG->Value('mbr_id')."' AND _updated_by=mbr_id" );
            $dMbrSUpdate = $this->kfdb->Query1( "SELECT max(_updated) FROM seeds.sed_curr_seeds "
                                               ."WHERE mbr_id='".$kfrG->Value('mbr_id')."' AND _updated_by=mbr_id" );
            $dOfficeGUpdate = $this->kfdb->Query1( "SELECT max(_updated) FROM seeds.sed_curr_growers "
                                                  ."WHERE mbr_id='".$kfrG->Value('mbr_id')."' AND _updated_by<>mbr_id" );
            $dOfficeSUpdate = $this->kfdb->Query1( "SELECT max(_updated) FROM seeds.sed_curr_seeds "
                                                  ."WHERE mbr_id='".$kfrG->Value('mbr_id')."' AND _updated_by<>mbr_id" );


            $s .= "<p>Last member login: $dLogin<br/>"
                 ."Last member G update: $dMbrGUpdate<br/>"
                 ."Last member S update: $dMbrSUpdate<br/>"
                 ."Last office G update: $dOfficeGUpdate<br/>"
                 ."Last office S update: $dOfficeSUpdate</p>";


            if( $kfrG->Value('bDoneOffice') && $kfrG->Value('bDoneMbr') ) {
                $s .= "<p style='background-color:".self::cColorBoth."'>Member and office clicked Done</p>";
            } else if( $kfrG->Value('bDoneOffice') ) {
                $s .= "<p style='background-color:".self::cColorOffice."'>Office clicked Done</p>";
            } else if( $kfrG->Value('bDoneMbr') ) {
                $s .= "<p style='background-color:".self::cColorMbr."'>Member clicked Done</p>";
            } else {
                $s .= "<p style='background-color:".self::cColorNone."'>Nobody clicked Done</p>";
            }



        $yThresh = 2014;
        $dThresh = $yThresh.'-10-01'; // a date separating last year's editing from this year's

        $sM1 = "mbr_id='".$kfrG->Value('mbr_id')."'";
        $sM = $sM1." AND mbr_id=_updated_by and _updated>='$dThresh'";
        $s .= "<P>Reg/Change Skip/Delete : "
             .$this->oSed->kfdb->Query1( "SELECT count(*) FROM sed_curr_seeds WHERE $sM AND not bChanged AND not bSkip AND not bDelete" )."/"
             .$this->oSed->kfdb->Query1( "SELECT count(*) FROM sed_curr_seeds WHERE $sM AND     bChanged AND not bSkip AND not bDelete" )."&nbsp;&nbsp;"
             .$this->oSed->kfdb->Query1( "SELECT count(*) FROM sed_curr_seeds WHERE $sM AND                      bSkip AND not bDelete" )."/"
             .$this->oSed->kfdb->Query1( "SELECT count(*) FROM sed_curr_seeds WHERE $sM AND                                    bDelete" )
             ."&nbsp;&nbsp;&nbsp; Self $yThresh+<BR/>";
        $sM = "mbr_id='".$kfrG->Value('mbr_id')."' AND mbr_id<>_updated_by and _updated>='$dThresh'";
        $s .= "Reg/Change Skip/Delete : "
             .$this->oSed->kfdb->Query1( "SELECT count(*) FROM sed_curr_seeds WHERE $sM AND not bChanged AND not bSkip AND not bDelete" )."/"
             .$this->oSed->kfdb->Query1( "SELECT count(*) FROM sed_curr_seeds WHERE $sM AND     bChanged AND not bSkip AND not bDelete" )."&nbsp;&nbsp;"
             .$this->oSed->kfdb->Query1( "SELECT count(*) FROM sed_curr_seeds WHERE $sM AND                      bSkip AND not bDelete" )."/"
             .$this->oSed->kfdb->Query1( "SELECT count(*) FROM sed_curr_seeds WHERE $sM AND                                    bDelete" )
             ."&nbsp;&nbsp;&nbsp; Office $yThresh+<BR/>";
        $sM = "mbr_id='".$kfrG->Value('mbr_id')."' AND mbr_id=_updated_by and _updated<'$dThresh'";
        $s .= "Reg/Change Skip/Delete : "
             .$this->oSed->kfdb->Query1( "SELECT count(*) FROM sed_curr_seeds WHERE $sM AND not bChanged AND not bSkip AND not bDelete" )."/"
             .$this->oSed->kfdb->Query1( "SELECT count(*) FROM sed_curr_seeds WHERE $sM AND     bChanged AND not bSkip AND not bDelete" )."&nbsp;&nbsp;"
             .$this->oSed->kfdb->Query1( "SELECT count(*) FROM sed_curr_seeds WHERE $sM AND                      bSkip AND not bDelete" )."/"
             .$this->oSed->kfdb->Query1( "SELECT count(*) FROM sed_curr_seeds WHERE $sM AND                                    bDelete" )
             ."&nbsp;&nbsp;&nbsp; Self < $yThresh<BR/>";
        $sM = "mbr_id='".$kfrG->Value('mbr_id')."' AND mbr_id<>_updated_by and _updated<'$dThresh'";
        $s .= "Reg/Change Skip/Delete : "
             .$this->oSed->kfdb->Query1( "SELECT count(*) FROM sed_curr_seeds WHERE $sM AND not bChanged AND not bSkip AND not bDelete" )."/"
             .$this->oSed->kfdb->Query1( "SELECT count(*) FROM sed_curr_seeds WHERE $sM AND     bChanged AND not bSkip AND not bDelete" )."&nbsp;&nbsp;"
             .$this->oSed->kfdb->Query1( "SELECT count(*) FROM sed_curr_seeds WHERE $sM AND                      bSkip AND not bDelete" )."/"
             .$this->oSed->kfdb->Query1( "SELECT count(*) FROM sed_curr_seeds WHERE $sM AND                                    bDelete" )
             ."&nbsp;&nbsp;&nbsp; Office < $yThresh</P>";

            $bChangedG = $this->oSed->kfdb->Query1( "SELECT bChanged FROM sed_curr_growers WHERE $sM1" );
            $bChangedS = $this->oSed->kfdb->Query1( "SELECT bChanged FROM sed_curr_seeds WHERE $sM1 AND bChanged LIMIT 1" );

            $s .= ($bChangedG ? "<div style='display:inline-block;border:1px solid #aaa;padding:10px;margin:10px;background-color:#d2eeb8'>Grower record changed</div>" : "")
                 .($bChangedS ? "<div style='display:inline-block;border:1px solid #aaa;padding:10px;margin:10px;background-color:#b8d2ee'>Seed record(s) changed</div>" : "");


            $s .= "</DIV>";  // the variable div
        }



        $s .= "</DIV>\n";  // sed_grower

        return( $s );
    }


    function drawCategories()
    /************************
        Show the list of Categories with links to drill down on Types
     */
    {
        $s = "<DIV class='sed_categories'>"
            ."<H3>Categories</H3>"
            ."<UL>";

        if( ($kfr = $this->oSed->kfrelS->CreateRecordCursor( "", array( "raGroup"=>array("category"=>"category"),
                                                                        "sSortCol"=>"category") )) )
        {
            while( $kfr->CursorFetch() ) {
                $seedCat = $kfr->value('category');
                if( $seedCat != 'VEGETABLES' ) {
                    $s .= "<LI><A HREF='{$_SERVER['PHP_SELF']}?seedCat=".urlencode($seedCat)."&seedType='>$seedCat</A></LI>";
                } else {
                    $s .= "<LI>VEGETABLES</LI>";

                    if( ($kfr2 = $this->oSed->kfrelS->CreateRecordCursor( "category='".addslashes($seedCat)."'",
                                                                          array("raGroup"=>array("type"=>"type"),
                                                                                "sSortCol"=>"type")) ) )
                    {
                        $s .= "<TABLE cellpadding='10' border='0' style='margin-left:2em'><TR><TD valign='top'>";
                        $i = 0;
                        while( $kfr2->CursorFetch() ) {
                            $s .= "<A HREF='{$_SERVER['PHP_SELF']}?seedCat=".urlencode($seedCat)
                                 ."&seedType=".urlencode($kfr2->value('type'))."'>".SEEDStd_StrNBSP($kfr2->value('type'))."</A><BR/>";
                            if( ++$i == 20 ) {
                                $s .= "</TD><TD valign='top'>";
                                $i = 0;
                            }
                        }
                        $s .= "</TD></TR></TABLE>";
                        $kfr2->CursorClose();
                    }
                }
            }
        }
        $s .= "</UL></DIV>";

        return( $s );
    }

    function drawCategoryContent( $sCat )
    /************************************
        Show the full content of Types and Varieties within the given Category
     */
    {
        return( $this->drawSeeds( "category='".addslashes($sCat)."'", array("sSortCol"=>"type,variety") ) );
    }


    function drawTypeContent( $sCat, $sType )
    /****************************************
        Show the full content of Varieties within the given Type
     */
    {
        return( $this->drawSeeds( "category='".addslashes($sCat)."' AND type='".addslashes($sType)."'", array("sSortCol"=>"variety") ) );
    }


    function drawSeedSearch( $sSearch, $sCond = NULL )
    /*************************************************
        Show the seed listings whose types, variety names, bot names, descriptions, or origins contain $sSearch
     */
    {
        if( empty($sCond) ) {
            // make the condition from the sSearch string
            $sCond = "type        LIKE '%".addslashes($sSearch)."%' OR "
                    ."variety     LIKE '%".addslashes($sSearch)."%' OR "
                    ."bot_name    LIKE '%".addslashes($sSearch)."%' OR "
                    ."origin      LIKE '%".addslashes($sSearch)."%' OR "
                    ."description LIKE '%".addslashes($sSearch)."%'";
        }
        return( $this->drawSeeds( $sCond, array("sSortCol"=>"category,type,variety") ) );
    }


    function drawSeedsByGrower( $iGrower )
    /*************************************
        Show all listings of the given grower (0 == all growers)
     */
    {
        $cond = ($iGrower ? "mbr_id=$iGrower" : "");

        return( $this->drawSeeds( $cond, array("sSortCol"=>"category,type,variety") ) );
    }


    function drawSeedsAll()
    /**********************
        set $this->oSed->eReportMode=='LAYOUT' to get the report format
     */
    {
        // despite its appearance, this actually shows all seeds in category/type/variety order for all growers
        return( $this->drawSeedsByGrower( 0 ) );
    }


    function drawSeeds( $cond, $raSelectParms )
    /******************************************
        Show the full content of Types and Varieties within the given Category
     */
    {
        $s = "";

        /* Allow Add New Seed if in seedKGrower mode. Can't create new seed records in other modes because need a grower owner for seed_curr_seeds.mbr_id
         */
        $bAllowNew = (@$this->oSed->klugeSeedsMode == 'grower' && intval($this->oSed->kGrowerActive));

        // Draw seed controls
        $bHideSkip = $bHideDelete = $this->oSed->bHideDetail = false;
        if( $this->oSed->eReportMode != 'LAYOUT' ) {

            $ctrlHide = $this->oSed->oNavSVA->SmartGPC( "ctrlHide", array( 0,1,2,3,4 ) );
            switch( $ctrlHide ) {
                case 0:
                    break;
                case 1: $bHideDelete = true;
                    break;
                case 2: $bHideSkip = $bHideDelete = true;
                    break;
                case 3: $this->oSed->bHideDetail = true;
                    break;
                case 4: $bHideSkip = $bHideDelete = $this->oSed->bHideDetail = true;
                    break;
            }
            $s .= "<TABLE border='0'><TR><TD valign='top'>"
                 ."<FORM action='${_SERVER['PHP_SELF']}'>"

                 .SEEDForm_Select( 'ctrlHide', array(0=>"Show Everything",1=>"Hide Deleted",2=>"Hide Skipped and Deleted",
                                                     3=>"Hide Details",4=>"Hide Details, Skipped, and Deleted"),
                                   $ctrlHide, array('selectAttrs'=>"onChange='submit();'") )
//                 ."<BR/>"



//                 ."Detail: ".SEEDForm_Select( 'hideDetail', array(0=>"Show",1=>"Hide"), $this->sess->VarGet('hideDetail'), array('selectAttrs'=>"onChange='submit();'") )
//                 .SEEDStd_StrNBSP("",10)
//                 ."Skips: ".SEEDForm_Select( 'hideSkip', array(0=>"Show",1=>"Hide"), $this->sess->VarGet('hideSkip'), array('selectAttrs'=>"onChange='submit();'") )
//                 .SEEDStd_StrNBSP("",10)
//                 ."Deletes: ".SEEDForm_Select( 'hideDelete', array(0=>"Show",1=>"Hide"), $this->sess->VarGet('hideDelete'), array('selectAttrs'=>"onChange='submit();'") )
                 .SEEDStd_StrNBSP("",10)
                 ."Type: ".SEEDForm_Text( 'p_seedType', $this->sess->SmartGPC( "p_seedType" ) )
                 ."</FORM>"
                 ."</TD>";
/*
            if( $bAllowNew ) {
                $s .= "<TD valign='top'>"
                     ."<FORM method='post' action='${_SERVER['PHP_SELF']}'>"	// separate form to add new seed
                     .SEEDStd_StrNBSP("",15)
                   ."AddButton"//  .$this->oSed->oSLE->Hidden('AddButton')
                     ."<INPUT type='submit' name='x' value='Add New Seed'/>"
                     ."</FORM></TD>";
            }
*/
            $s .= "</TR></TABLE>";
        }

        if( !empty($cond) ) {
            $cond = "(".$cond.")";
        } else {
            $cond = "1=1";
        }

        if( $this->sess->VarGet('p_seedType') )  $cond .= " AND type='".addslashes($this->sess->VarGet('p_seedType'))."'";
        if( $this->oSed->eReportMode=='LAYOUT' || $bHideSkip )       $cond .= " AND bSkip=0";
        if( $this->oSed->eReportMode=='LAYOUT' || $bHideDelete )     $cond .= " AND bDelete=0";

        if( $kfrcS = $this->oSed->kfrelS->CreateRecordCursor( $cond, $raSelectParms ) ) {
$raDrawParms = isset($this->oSed->oConsoleTableDrawParms) ? $this->oSed->oConsoleTableDrawParms : array();
$raDrawParms['bAllowNew'] = $bAllowNew;  // this is not evaluated where the raDrawParms originates but we know it here
            $s .= "" //$this->drawSeedBegin()
                 .$this->oSed->oConsoleTable->DrawTableKFRCursor( $kfrcS, $raDrawParms )

//                 .$this->oSed->oSLE->DrawList( $kfrS )
                 // .$this->drawSeedEnd()
                 ;
        }
        return( $s );
    }


    /* Always surround calls to drawSeed with the Begin and End calls
     */
//    function drawSeedBegin()    { $this->_lastCategory = $this->_lastType = ""; return( "" ); } // return blank to be consistent with a string retval
//    function drawSeedEnd()      { if( $this->_lastCategory != "" )  return(""); } // "</DIV></DIV>"; }
// THIS IS NO LONGER BEING CALLED
/*
    function drawSeed( &$kfrS, $raParms = array() )
    [**********************************************
        Draw the info for the current seed listing
     *]
    {
        $s = "";

//        if( $this->_lastType     != $kfrS->value('type')     && $this->_lastType     != "" )  $s .= "</DIV>\n\n";
//        if( $this->_lastCategory != $kfrS->value('category') && $this->_lastCategory != "" )  $s .= "</DIV>\n\n";


        if( $this->_lastCategory != $kfrS->value('category') ) {
            [* Start a new category
             *]
            $s .= "<DIV class='sed_category'><H2>Category: ".$kfrS->value('category')."</H2></DIV>";
            $this->_lastCategory = $kfrS->value('category');
        }
        if( $this->_lastType != $kfrS->value('type') ) {
            [* Start a new type
             *]
            $s .= "<DIV class='sed_type'><H3>".$kfrS->value('type')."</H3></DIV>";
            $this->_lastType = $kfrS->value('type');
        }

        [* Draw the seed listing
         *]
        $s .= "<DIV class='sed_seed' id='Seed".$kfrS->Key()."'>";
        if( $this->oSed->eReportMode != 'LAYOUT' ) {
            if( $kfrS->value('bDelete') ) {
                $s .= "<DIV class='sed_seed_delete'>";
            } else if( $kfrS->value('bSkip') ) {
                $s .= "<DIV class='sed_seed_skip'>";
            } else if( $kfrS->value('bChanged') ) {
                $s .= "<DIV class='sed_seed_change'>";
            } else {
                $s .= "<DIV>";
            }

            if( $kfrS->value('bDelete') )  $s .= "<B><I>Deleted</I></B><BR>";
            if( $kfrS->value('bSkip') )    $s .= "<B><I>Skipped</I></B><BR>";
        }


        $s .= "<B>".$kfrS->value('variety')
             .$kfrS->ExpandIfNotEmpty( 'bot_name', " <I>[[]]</I>" );
        if( $this->oSed->eReportMode=='LAYOUT' ) $s .= " @M@ ";

// this would be way better with a join to G, or maybe a lookup table in memory
$mbr_code = $this->oSed->kfrelG->kfdb->Query1("SELECT mbr_code FROM sed_curr_growers WHERE mbr_id='".$kfrS->value('mbr_id')."'");
        $s .= " <SPAN class='sed_seed_mc'>".$mbr_code."</SPAN></B>";

        [* [Edit] link
         *]
        if( $this->oSed->eReportMode != 'LAYOUT' && $this->sess->CanWrite('sedadmin') ) {
            $s .= $this->oSed->oSLE->ExpandTags( $kfrS->Key(), " <A HREF='{$_SERVER['PHP_SELF']}?[[LinkParmEdit]]' style='color:red'>[Edit]</A>" )
                 .SEEDStd_StrNBSP("     ")
                 ." <A HREF='{$_SERVER['PHP_SELF']}?sskip=".$kfrS->Key()."' style='color:red'>[".($kfrS->value('bSkip')?"Un-":"")."Skip]</A>"
                 ." <A HREF='{$_SERVER['PHP_SELF']}?sdel=".$kfrS->Key()."' style='color:red'>[".($kfrS->value('bDelete')?"Un-":"")."Delete]</A>";
        }

        $s .= "<BR/>";

        if( $this->oSed->eReportMode=='LAYOUT' || !$this->bHideDetail ) {
            $s .= $kfrS->ExpandIfNotEmpty( 'days_maturity', "[[]] dtm. " )
               //  .($this->bReport ? "@Y@: " : "Y: ").$kfrS->value('year_1st_listed').". "  // this doesn't have much value and it's readily mistaken for the year of harvest
               //  ."<BR/>"
                 .$kfrS->value('description')." "
                 .$kfrS->ExpandIfNotEmpty( 'origin', (($this->oSed->eReportMode=='LAYOUT' ? "@O@" : "O").": [[]]. ") )
                 .$kfrS->ExpandIfNotEmpty( 'quantity', "<B><I>[[]]</I></B>" );
        }

        if( $this->oSed->eReportMode != 'LAYOUT' ) {
            $s .= "</DIV>";      // variable div
        }
        $s .= "</DIV>\n";        // sed_seed

        return( $s );
    }
*/

    function seditForm( $oKForm )    // FORM tag is already written
    /****************************
        User clicked on [New Seed Offer] or [Edit] link.  Show the form.
        $oKForm is a KeyFrameUIForm with the current row (or a new row) set in its kfr

        This is only called if $kSeed || (seedMode==seedKGrower and intval(seedKGrower) > 0)
        i.e. editing an existing kSeed, or creating a new kSeed in "grower list" mode
     */
    {
        $s = $this->oSed->drawSeedForm( $oKForm );
        return( $s );
    }


    function geditForm( $oKForm )    // FORM tag is already written
    /****************************
        User clicked on [New Grower] or [Edit] link.  Show the form.
        $oKForm is a KeyFrameUIForm with the current row (or a new row) set in its kfr

        Already written by Console01_ListEdit:
        <FORM ...><BUTTON Add></FORM>
        <FORM ...>
        <HIDDEN FormSubmitted>
        <HIDDEN key>
     */
    {
        $s = $this->oSed->drawGrowerForm( $oKForm, true )
            .$this->oGList->ExpandTags( $oKForm->oDS->Key(),
                                        "<P style='text-align:center'><A HREF='{$_SERVER['PHP_SELF']}?[[LinkParmScroll]]'>Close Form</A></P>" );
        return( $s );
    }
}

?>
