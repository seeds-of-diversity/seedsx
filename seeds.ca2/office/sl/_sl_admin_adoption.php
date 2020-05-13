<?php

include_once( STDINC."KeyFrame/KFUIForm.php" );


class SLAdoptConsoleListEdit extends Console01_ListEdit
{
    var $oSLAdopt;
    function __construct( $oSLAdopt ) { $this->oSLAdopt = $oSLAdopt; parent::__construct( $oSLAdopt->oSLDBAdopt->kfrel ); }

    function DrawListItem( $kfrc, $raParms )
    {
        return( $this->oSLAdopt->drawAdoption( $kfrc, $raParms ) );
    }

    function DrawListForm( $kfrc, $raParms )
    {
        return( $this->oSLAdopt->drawAdoptForm( $this->oKFUForm ) );
    }

    function factory_KeyFrameUIForm( KeyFrameRelation $kfrel )
    {
        //$oKFU = new SLAdoptKFUIForm( $kfrel );  this just overrode DSPreStore, but there's an easier way
        $oKFU = new KeyFrameUIForm( $kfrel, NULL, array('DSParms' => array( 'fn_DSPreStore'=>array($this,'ds_DSPreStore') ) ) );
        return( $oKFU );
    }

    function ds_DSPreStore( $oDS )
    /*****************************
        This is called just before Console01_ListEdit::Update -> KeyFrameUIForm::Update saves a row to the db,
        so kfr values can be adjusted.
        If the user has typed a name in fk_sl_pcv, replace it with the matching key.
        If the user has typed a name in fk_mbr_contacts replace it with the key that matches [firstname lastname]
     */
    {
        $s = $oDS->Value('fk_sl_pcv');
        if( !empty($s) && !is_numeric($s) ) {
//broken because oDS->kfrel is private now, but this code is never called
            if( ($k = $oDS->kfrel->kfdb->Query1("SELECT _key FROM sl_pcv WHERE name='".addslashes($s)."'")) ) {
                $oDS->SetValue( 'fk_sl_pcv', $k );
            }
        }

        // note that the oDS->kfrel->kfdb is for the seeds1 db; we need the seeds2 db for this
        $s = $oDS->Value('fk_mbr_contacts');
        if( !empty($s) && !is_numeric($s) ) {
            if( ($k = $this->oSLAdopt->kfdb2->Query1("SELECT _key FROM mbr_contacts WHERE "
                                                    ."CONCAT(firstname, ' ', lastname)='".addslashes($s)."'")) ) {
                $oDS->SetValue( 'fk_mbr_contacts', $k );
            }
        }
        return( true );
    }
}

class SLAdoption
{
    var $kfdb;    // kfdb1 on seeds db
    var $kfdb2;   // seeds2 db to get mbr_contacts
    var $sess;
    var $oSLDBAdopt;
    var $oSLDBPCV;
    var $oConsoleList;

    var $bListBgToggle = false;
    var $sListBgLast = "";   // for toggling the background to join related items in sorted lists

    function __construct( &$kfdb1, &$kfdb2, &$sess )
    {
        $this->kfdb = &$kfdb1;
        $this->kfdb2 = &$kfdb2;
        $this->sess = &$sess;
        $this->oSLDBAdopt = new SLDB_DP( $kfdb1, $sess->GetUID() );
        $this->oSLDBPCV = new SLDB_PCV( $kfdb1, $sess->GetUID() );
        $this->oConsoleList = new SLAdoptConsoleListEdit( $this );
    }

    function Update()
    {
        $this->oConsoleList->Update();
    }

    function DrawAdoptions()
    {
        $s = "";

        $this->sess->SmartGPC( "sladopt_show", array(0,1,2) );
        $this->sess->SmartGPC( "sladopt_sort", array('sDonor','sCV', 'sDate') );
        $this->sess->SmartGPC( "sladopt_find", array('sDonor','sCV','kPCV','kMbr') );
        $this->sess->SmartGPC( "sladopt_findstr", array() );
        $this->sess->SmartGPC( "sladopt_kpcv", array() );
        $this->sess->SmartGPC( "sladopt_kmbr", array() );

        $s .= "<TABLE border='0'><TR><TD valign='top'>"
             ."<FORM method='post' action='${_SERVER['PHP_SELF']}'>"
             ."Show: ".SEEDForm_Select( 'sladopt_show', array(0=>"All",1=>"No Donor #",2=>"No PCV"), $this->sess->VarGet('sladopt_show'), array('selectAttrs'=>"onChange='submit();'") )
             .SEEDStd_StrNBSP("",10)
             ."Sort: ".SEEDForm_Select( 'sladopt_sort', array( 'sDonor'=>"Donor name", 'sCV'=>"Cultivar name", 'sDate'=>"Donation date" ), $this->sess->VarGet('sladopt_sort'), array('selectAttrs'=>"onChange='submit();'") )
             .SEEDStd_StrNBSP("",10)
             ."Find ".SEEDForm_Select( 'sladopt_find', array( 'sDonor'=>"Donor name", 'sCV'=>"Cultivar name", 'kPCV'=>"kPCV", 'kMbr'=>"kMbr" ), $this->sess->VarGet('sladopt_find') )
             ." containing ".SEEDForm_Text( 'sladopt_findstr', $this->sess->VarGet('sladopt_findstr') )
//             .SEEDStd_StrNBSP("",10)
//             ."kPCV: ".SEEDForm_Text( 'sladopt_kpcv', $this->sess->VarGet('sladopt_kpcv'), "", 5 )
//             .SEEDStd_StrNBSP("",10)
//             ."kMbr: ".SEEDForm_Text( 'sladopt_kmbr', $this->sess->VarGet('sladopt_kmbr'), "", 5 )
             .SEEDStd_StrNBSP("",5)
             ."<INPUT type='submit' value='Search'>"
             ."</FORM>"
             ."</TD>"
             ."<TD valign='top'>"
             ."<FORM method='post' action='${_SERVER['PHP_SELF']}'>"    // separate form to add new adoption
             .SEEDStd_StrNBSP("",15)
             .$this->oConsoleList->Hidden('AddButton')
             ."<INPUT type='submit' value='Add New Adoption'>"
             ."</FORM></TD>"
             ."</TR></TABLE>";

        $raFlt = array();
        if( $this->sess->VarGet('sladopt_show') == 1 ) {  // No Donor #
            $raFlt['kAdoptMbr'] = 0;
        }
        if( $this->sess->VarGet('sladopt_show') == 2 ) {  // No PCV
            $raFlt['kPCV'] = 0;
        }
        if( $this->sess->VarGet('sladopt_kpcv') ) {
            $raFlt['kPCV'] = $this->sess->VarGet('sladopt_kpcv');
        }
        if( $this->sess->VarGet('sladopt_kmbr') ) {
            $raFlt['kAdoptMbr'] = $this->sess->VarGet('sladopt_kmbr');
        }
        if( $this->sess->VarGet('sladopt_findstr') ) {
            switch( $this->sess->VarGet('sladopt_find') ) {
                case 'sDonor':  $raFlt['sAdoptDonorLike']            = $this->sess->VarGet('sladopt_findstr');    break;
                case 'sCV':     $raFlt['sDP_RequestLikeOrPNameLike'] = $this->sess->VarGet('sladopt_findstr');    break;
                case 'kPCV':    $raFlt['kPCV']                       = $this->sess->VarGet('sladopt_findstr');    break;
                case 'kMbr':    $raFlt['kAdoptMbr']                  = $this->sess->VarGet('sladopt_findstr');    break;
            }
        }

        $kfParms = array();
        switch( $this->sess->VarGet('sladopt_sort') ) {
            case 'sDonor':    $kfParms['sSortCol'] = 'donor_name';    break;
            case 'sCV':       $kfParms['sSortCol'] = 'P.psp,P.name';  break;
            case 'sDate':     $kfParms['sSortCol'] = 'd_donation';    break;
        }


        if( ($kfrc = $this->oSLDBAdopt->GetRecordCursor( $raFlt, $kfParms ) ) ) {
            $s .= $this->oConsoleList->DrawList( $kfrc, array() );  // raParms is passed to drawAdoption and drawAdoptForm
            $kfrc->CursorClose();
        }
        return( $s );
    }

    function drawAdoption( $kfr, $raParms )
    {
        if( $this->sess->VarGet('sladopt_sort') == 'sDonor' ) {
            if( $this->sListBgLast != $kfr->value('donor_name') ) {
                $this->sListBgLast = $kfr->value('donor_name');
                $this->bListBgToggle = !$this->bListBgToggle;
            }
        }
        if( $this->sess->VarGet('sladopt_sort') == 'sCV' ) {
            $sCV = $kfr->value("P_psp")." ".$kfr->value("P_name");
            if( $this->sListBgLast != $sCV ) {
                $this->sListBgLast = $sCV;
                $this->bListBgToggle = !$this->bListBgToggle;
            }
        }

        $sDonor = "";
        if( ($kMbrDonor = intval( $kfr->value('fk_mbr_contacts')) ) ) {
            $raQ = $this->kfdb2->QueryRA("SELECT * FROM mbr_contacts WHERE _key='$kMbrDonor'" );
            if( $raQ['_key'] == $kMbrDonor ) {
                $sDonor = SEEDStd_ArrayExpand( $raQ, "[[firstname]] [[lastname]] [[company]], [[city]]" );
            }
        }

        $s = "<DIV class='slAdminConsoleListItem' style='background-color:".($this->bListBgToggle ? "#ddd" : "#fff")."'>"
            ."Donor: ".$kfr->value('donor_name')    // unless fk_mbr_contacts
            ." <A HREF='${_SERVER['PHP_SELF']}?sladopt_find=kMbr&sladopt_findstr=$kMbrDonor'>($kMbrDonor)</A> "
            ."<SPAN style='background-color:#eff'>&nbsp;$sDonor&nbsp;</SPAN>"
            .SEEDStd_StrNBSP("",10)
            .$this->oConsoleList->ExpandTags( $kfr->Key(), " <A HREF='{$_SERVER['PHP_SELF']}?[[LinkParmEdit]]' style='color:red'>[Edit]</A><BR/>" )
            ."Recognized as: ".($kfr->IsEmpty('public_name') ? "<I>(same)</I>" : $kfr->value('public_name'))."<BR/>"
            ."Amount: ".$kfr->value('amount')."<BR/>"
            ."Date of donation: ".$kfr->value('d_donation')."<BR/>"
//    array( "col"=>"x_d_donation",        "type"=>"S" ),   // remove when migrated to date
            ."Variety requested: ".$kfr->value('sPCV_request')."<BR/>"

            ."PCV : ";
        if( $kfr->value('fk_sl_pcv') ) {
            $kfrPCV = $this->oSLDBPCV->GetRecordByKey($kfr->value('fk_sl_pcv'));
            $s .= $kfrPCV->value('psp')." : ".$kfrPCV->value('name')." ";
        }
        $s .= " <A HREF='${_SERVER['PHP_SELF']}?sladopt_find=kPCV&sladopt_findstr=".$kfr->value('fk_sl_pcv')."'>(".$kfr->value('fk_sl_pcv').")</A><BR/>"
             .$kfr->ExpandIfNotEmpty( 'notes', "Notes: [[]]" )

             ."<DIV style='border:1px solid #aaa;background-color:#eee;width:50%'>"
             .$kfr->ExpandIfNotEmpty( 'bAckDonation',    "Ack Donation<BR/>" )
             .$kfr->ExpandIfNotEmpty( 'bDoneCV',         "Variety Chosen<BR/>" )
             .$kfr->ExpandIfNotEmpty( 'bAckCV',          "Ack Variety Chosen<BR/>" )
             .$kfr->ExpandIfNotEmpty( 'bDoneHaveSeed',   "Seed Collected<BR/>" )
             .$kfr->ExpandIfNotEmpty( 'bAckHaveSeed',    "Ack Seed Collected<BR/>" )
             .$kfr->ExpandIfNotEmpty( 'bDoneBulkStored', "Multiplied and Stored<BR/>" )
             .$kfr->ExpandIfNotEmpty( 'bAckBulkStored',  "Ack Multiplied and Stored, Seeds sent to donor<BR/>" )
             .$kfr->ExpandIfNotEmpty( 'bDoneAvail',      "Seeds Available<BR/>" )
             .$kfr->ExpandIfNotEmpty( 'bAckAvail',       "Ack Seeds Available<BR/>" )
             .$kfr->ExpandIfNotEmpty( 'bDoneBackup',     "Seeds Backed up<BR/>" )
             .$kfr->ExpandIfNotEmpty( 'bAckBackup',      "Ack Seeds Backed up<BR/>" )
             ."</DIV></DIV>";

        return( $s );
    }

    function drawAdoptForm( $oKForm )  // FORM tag is already written
    {
        $sDonor = "";
        if( ($kMbrDonor = intval($oKForm->oDS->value('fk_mbr_contacts'))) ) {
            $raQ = $this->kfdb2->QueryRA("SELECT * FROM mbr_contacts WHERE _key='$kMbrDonor'" );
            if( $raQ['_key'] == $kMbrDonor ) {
                $sDonor = SEEDStd_ArrayExpand( $raQ, "[[firstname]] [[lastname]] [[company]]<BR/>[[address]]<BR/>[[city]] [[province]] [[postcode]]<BR/>[[email]]" );
            }
        }
        $s = "<STYLE>"
          //  .".c01_leForm { font-size:10pt; }"
            .".c01_leForm label  { font-family:verdana,helvetica,sans-serif; width:200px; }"
            .".c01_leForm legend { font-family:verdana,helvetica,sans-serif; }"
            .".c01_leForm td     { font-family:verdana,helvetica,sans-serif; font-size:10pt; }"
            .".c01_leForm input  { background-color: #fff }"
            .".slAdopt_inst      { font-size:8pt;font-style:italic; }"
            ."</STYLE>"
            ."<FIELDSET style='background-color:#eee'><LEGEND>Fill in this section when the donation is made</LEGEND>"
            ."<TABLE border='0' cellspacing='0' cellpadding='10'><TR>"
            .$oKForm->TextTD('fk_mbr_contacts', "Donor contact #",         array('sRightTail'=> "<DIV style='float:right;background-color:#eff;padding:1ex'>$sDonor</DIV><SPAN class='slAdopt_inst'><BR/>This is the donor's contact id, but you can enter a search name. If the donor is not in the contact database, enter their name in the next box and fill in this number later.</SPAN>") )
            ."</TR><TR>"
            .$oKForm->TextTD('donor_name',   "Donor Name",    array('sRightTail'=> "<SPAN class='slAdopt_inst'><BR/>Who gets the tax receipt (not shown publicly)</SPAN>") )
            ."</TR><TR>"
            .$oKForm->TextTD('public_name',  "Recognized as", array('sRightTail'=> "<SPAN class='slAdopt_inst'><BR/>The name shown publicly (e.g. Anonymous, gift to... )</SPAN>") )
            ."</TR><TR>"
            .$oKForm->TextTD('amount',       "Amount",        array('sRightTail'=> "<SPAN class='slAdopt_inst'><BR/></SPAN>") )
            ."</TR><TR>"
            .$oKForm->TextTD('d_donation',   "Donation date", array('sRightTail'=> "<SPAN class='slAdopt_inst'><BR/>YYYY-MM-DD</SPAN>") )
            ."</TR><TR>"
            .$oKForm->TextTD('sPCV_request', "Requested",     array('size'=>40, 'sRightTail'=> "<SPAN class='slAdopt_inst'><BR/>What they asked for (e.g. a specific variety, as needed, etc)</SPAN>" ) )
            ."</TR><TR>"
            .$oKForm->TextAreaTD('notes', "Notes", 40, 3)
            ."</TR></TABLE>"
            ."</FIELDSET>"
            ."<BR/>";


        $s .= "<FIELDSET><LEGEND>Variety Adopted</LEGEND>"
             ."<TABLE border='0' cellspacing='0' cellpadding='10'>";
        if( ($kpcv = $oKForm->oDS->Value('fk_sl_pcv')) ) {
            $ra = $oKForm->kfrel->kfdb->QueryRA( "SELECT psp,name FROM sl_pcv WHERE _key='$kpcv'" );
            $s .= "<TR><TD valign='top' colspan='2' style='font-size:12pt;color:#008'>".$ra['psp']." : ".$ra['name']."</TD></TR>";
        }
        $s .= "<TR>"
             .$oKForm->TextTD('fk_sl_pcv', "PCV", array('sRightTail'=> "<SPAN class='slAdopt_inst'><BR/>This is the variety number, but you can enter a search name. If the variety name is not found, you have to add it on the Cultivars page.</SPAN>") )
             ."</TR></TABLE>";
        $s .= "</FIELDSET>"
             ."<BR/><INPUT type=submit name='submitAdopt' value='Save' />";
        return( $s );
    }


    function GetCorrespondence( $kAdopt )
    {

    }
}





function SL_AdoptionAdmin( &$kfdb1, &$kfdb2, &$sess )
{
    $s = "";

    $oSLA = new SLAdoption( $kfdb1, $kfdb2, $sess );
    $oSLA->Update();
    $s = $oSLA->DrawAdoptions();

    return( $s );
}

class SLAdminAdoption
{
    public $oConsole;
    public $kfdb1, $kfdb2;

    var $bListBgToggle = false;
    var $sListBgLast = "";   // for toggling the background to join related items in sorted lists

    var $oSLDBAdopt;
    var $oSLDBPCV;


    function __construct( &$oConsole )
    {
        $this->oConsole =& $oConsole;
    	$this->kfdb2 = &$oConsole->kfdb;

        global $kfdb1; $this->kfdb1 = &$kfdb1;

        $this->oSLDBAdopt = new SLDB_DP( $kfdb1, $oConsole->sess->GetUID() );
        $this->oSLDBPCV = new SLDB_PCV( $kfdb1, $oConsole->sess->GetUID() );


        $raCompParms = array(
            "Label"=>"Adoptions",
            "fnTableItemDraw" => array($this,'AdoptionTableItemDraw'),
            "fnFormDraw" => array($this,'AdoptionFormDraw')
        );
        $kfreldef_SL_Adoption =
            array( "Tables"=>array( array( "Table" => 'sl_adoption',
                                           "Type"  => 'Base',
                                           "Fields" => "Auto" ),
                                    array( "Table" => 'sl_pcv',
                                           "Alias" => 'P',
                                           "Type" => 'LEFT JOIN',
                                           "LeftJoinOn" => "T1.fk_sl_pcv=P._key",
                                           "Fields" => array( array("col"=>'psp', "type"=>"S"),
                                                              array("col"=>"name",  "type"=>"S") )) ) );
        $kfrel = new KeyFrameRelation( $this->kfdb1, $kfreldef_SL_Adoption, $this->oConsole->sess->GetUID() );

        $this->oConsole->CompInit( $kfrel, $raCompParms );
    }

    function AdoptionTableItemDraw( $oComp, $kfr )
    // $oComp is the same as $this->oConsole->oComp
    // $kfr is the record to draw
    // So if $kfr->Key()==$oComp->GetKey() we're drawing the current row
    {
        if( $this->oConsole->sess->VarGet('sladopt_sort') == 'sDonor' ) {
            if( $this->sListBgLast != $kfr->value('donor_name') ) {
                $this->sListBgLast = $kfr->value('donor_name');
                $this->bListBgToggle = !$this->bListBgToggle;
            }
        }
        if( $this->oConsole->sess->VarGet('sladopt_sort') == 'sCV' ) {
            $sCV = $kfr->value("P_psp")." ".$kfr->value("P_name");
            if( $this->sListBgLast != $sCV ) {
                $this->sListBgLast = $sCV;
                $this->bListBgToggle = !$this->bListBgToggle;
            }
        }

        $sDonor = "";
        if( ($kMbrDonor = intval( $kfr->value('fk_mbr_contacts')) ) ) {
            $raQ = $this->kfdb2->QueryRA("SELECT * FROM mbr_contacts WHERE _key='$kMbrDonor'" );
            if( $raQ['_key'] == $kMbrDonor ) {
                $sDonor = SEEDStd_ArrayExpand( $raQ, "[[firstname]] [[lastname]] [[company]], [[city]]" );
            }
        }

        $s = "<DIV class='slAdminConsoleListItem' style='background-color:".($this->bListBgToggle ? "#ddd" : "#fff")."'>"
            ."<A ".$this->oConsole->oComp->EncodeUrlHREF(array('kCurrRow'=>$kfr->Key())).">[Edit]</A><br/>"
            ."Donor: ".$kfr->value('donor_name')    // unless fk_mbr_contacts
            ." <A HREF='${_SERVER['PHP_SELF']}?sladopt_find=kMbr&sladopt_findstr=$kMbrDonor'>($kMbrDonor)</A> "
            ."<SPAN style='background-color:#eff'>&nbsp;$sDonor&nbsp;</SPAN>"
            .SEEDStd_StrNBSP("",10)
            //.$this->oConsoleList->ExpandTags( $kfr->Key(), " <A HREF='{$_SERVER['PHP_SELF']}?[[LinkParmEdit]]' style='color:red'>[Edit]</A><BR/>" )
            ."Recognized as: ".($kfr->IsEmpty('public_name') ? "<I>(same)</I>" : $kfr->value('public_name'))."<BR/>"
            ."Amount: ".$kfr->value('amount')."<BR/>"
            ."Date of donation: ".$kfr->value('d_donation')."<BR/>"
//    array( "col"=>"x_d_donation",        "type"=>"S" ),   // remove when migrated to date
            ."Variety requested: ".$kfr->value('sPCV_request')."<BR/>"

            ."PCV : ";
        if( $kfr->value('fk_sl_pcv') ) {
            if( ($kfrPCV = $this->oSLDBPCV->GetRecordByKey($kfr->value('fk_sl_pcv')))) {
                $s .= $kfrPCV->value('psp')." : ".$kfrPCV->value('name')." ";
            }
        }
        $s .= " <A HREF='${_SERVER['PHP_SELF']}?sladopt_find=kPCV&sladopt_findstr=".$kfr->value('fk_sl_pcv')."'>(".$kfr->value('fk_sl_pcv').")</A><BR/>"
             .$kfr->ExpandIfNotEmpty( 'notes', "Notes: [[]]" )

             ."<DIV style='border:1px solid #aaa;background-color:#eee;width:50%'>"
             .$kfr->ExpandIfNotEmpty( 'bAckDonation',    "Ack Donation<BR/>" )
             .$kfr->ExpandIfNotEmpty( 'bDoneCV',         "Variety Chosen<BR/>" )
             .$kfr->ExpandIfNotEmpty( 'bAckCV',          "Ack Variety Chosen<BR/>" )
             .$kfr->ExpandIfNotEmpty( 'bDoneHaveSeed',   "Seed Collected<BR/>" )
             .$kfr->ExpandIfNotEmpty( 'bAckHaveSeed',    "Ack Seed Collected<BR/>" )
             .$kfr->ExpandIfNotEmpty( 'bDoneBulkStored', "Multiplied and Stored<BR/>" )
             .$kfr->ExpandIfNotEmpty( 'bAckBulkStored',  "Ack Multiplied and Stored, Seeds sent to donor<BR/>" )
             .$kfr->ExpandIfNotEmpty( 'bDoneAvail',      "Seeds Available<BR/>" )
             .$kfr->ExpandIfNotEmpty( 'bAckAvail',       "Ack Seeds Available<BR/>" )
             .$kfr->ExpandIfNotEmpty( 'bDoneBackup',     "Seeds Backed up<BR/>" )
             .$kfr->ExpandIfNotEmpty( 'bAckBackup',      "Ack Seeds Backed up<BR/>" )
             ."</DIV></DIV>";

        return( $s );
    }





/*

        $s = "<TABLE width='100%'><TR valign='top'><TD bgcolor='".CLR_BG_editEN."' width='50%'>"
            .$this->oConsole->oSLSrcCommon->SourceItemDraw( $kfr, 'EN',
                    array("subst_name"=>"<A ".$oComp->EncodeUrlHREF(array('kCurrRow'=>$kfr->Key())).">[[name]]</A>",
                          "bEdit"=>true) )
            ."</TD><TD bgcolor='".CLR_BG_editFR."'>"
            .$this->oConsole->oSLSrcCommon->SourceItemDraw( $kfr, 'FR',
                    array("subst_name"=>"<A ".$oComp->EncodeUrlHREF(array('kCurrRow'=>$kfr->Key())).">[[name]]</A>",
                          "bEdit"=>true) )
            ."</TD><TD>".$oComp->ButtonDeleteRow($kfr->Key())
            ."</TD></TR></TABLE>";

        return( $s );
    }
*/

    function AdoptionFormDraw( $oKForm )
    {
        $sDonor = "";
        if( ($kMbrDonor = intval($oKForm->oDS->value('fk_mbr_contacts'))) ) {
            $raQ = $this->kfdb2->QueryRA("SELECT * FROM mbr_contacts WHERE _key='$kMbrDonor'" );
            if( $raQ['_key'] == $kMbrDonor ) {
                $sDonor = SEEDStd_ArrayExpand( $raQ, "[[firstname]] [[lastname]] [[company]]<BR/>[[address]]<BR/>[[city]] [[province]] [[postcode]]<BR/>[[email]]" );
            }
        }
        $s = "<STYLE>"
          //  .".c01_leForm { font-size:10pt; }"
            .".c01_leForm label  { font-family:verdana,helvetica,sans-serif; width:200px; }"
            .".c01_leForm legend { font-family:verdana,helvetica,sans-serif; }"
            .".c01_leForm td     { font-family:verdana,helvetica,sans-serif; font-size:10pt; }"
            .".c01_leForm input  { background-color: #fff }"
            .".slAdopt_inst      { font-size:8pt;font-style:italic; }"
            ."</STYLE>"
            ."<FIELDSET style='background-color:#eee'><LEGEND>Fill in this section when the donation is made</LEGEND>"
            ."<TABLE border='0' cellspacing='0' cellpadding='10'><TR>"
            .$oKForm->TextTD('fk_mbr_contacts', "Donor contact #",         array('sRightTail'=> "<DIV style='float:right;background-color:#eff;padding:1ex'>$sDonor</DIV><SPAN class='slAdopt_inst'><BR/>This is the donor's contact id, but you can enter a search name. If the donor is not in the contact database, enter their name in the next box and fill in this number later.</SPAN>") )
            ."</TR><TR>"
            .$oKForm->TextTD('donor_name',   "Donor Name",    array('sRightTail'=> "<SPAN class='slAdopt_inst'><BR/>Who gets the tax receipt (not shown publicly)</SPAN>") )
            ."</TR><TR>"
            .$oKForm->TextTD('public_name',  "Recognized as", array('sRightTail'=> "<SPAN class='slAdopt_inst'><BR/>The name shown publicly (e.g. Anonymous, gift to... )</SPAN>") )
            ."</TR><TR>"
            .$oKForm->TextTD('amount',       "Amount",        array('sRightTail'=> "<SPAN class='slAdopt_inst'><BR/></SPAN>") )
            ."</TR><TR>"
            .$oKForm->TextTD('d_donation',   "Donation date", array('sRightTail'=> "<SPAN class='slAdopt_inst'><BR/>YYYY-MM-DD</SPAN>") )
            ."</TR><TR>"
            .$oKForm->TextTD('sPCV_request', "Requested",     array('size'=>40, 'sRightTail'=> "<SPAN class='slAdopt_inst'><BR/>What they asked for (e.g. a specific variety, as needed, etc)</SPAN>" ) )
            ."</TR><TR>"
            .$oKForm->TextAreaTD('notes', "Notes", 40, 3)
            ."</TR></TABLE>"
            ."</FIELDSET>"
            ."<BR/>";


        $s .= "<FIELDSET><LEGEND>Variety Adopted</LEGEND>"
             ."<TABLE border='0' cellspacing='0' cellpadding='10'>";
        if( ($kpcv = $oKForm->oDS->Value('fk_sl_pcv')) ) {
            $ra = $oKForm->kfrel->kfdb->QueryRA( "SELECT psp,name FROM sl_pcv WHERE _key='$kpcv'" );
            $s .= "<TR><TD valign='top' colspan='2' style='font-size:12pt;color:#008'>".$ra['psp']." : ".$ra['name']."</TD></TR>";
        }
        $s .= "<TR>"
             .$oKForm->TextTD('fk_sl_pcv', "PCV", array('sRightTail'=> "<SPAN class='slAdopt_inst'><BR/>This is the variety number, but you can enter a search name. If the variety name is not found, you have to add it on the Cultivars page.</SPAN>") )
             ."</TR></TABLE>";
        $s .= "</FIELDSET>"
             ."<BR/><INPUT type=submit name='submitAdopt' value='Save' />";
        return( $s );
    }
}

?>
