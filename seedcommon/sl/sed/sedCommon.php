<?php

/*
 * sedCommon
 *
 * Copyright (c) 2011-2017 Seeds of Diversity Canada
 *
 * Functions shared between the single-user and office interfaces to the Seed Exchange Directory
 */


/*
function mixCase($string) {
    return preg_replace( "/\b(\d*)([a-z])/e", '"$1".ucfirst("$2")', strtolower($string));

    return preg_replace_callback( "/\b(\d*)([a-z])/e", function($m) {return( $m[1].ucfirst($m[2])}, strtolower($string));
}
 */
include_once( STDINC."KeyFrame/KFUIForm.php" );
include_once( SEEDCOMMON."console/console01.php" );
include_once( "sedCommonDraw.php" );

class SEDCommon extends SEDCommonDraw
{
    public $bOffice = false;  // true: the derived class is the office version so you can do a lot more
    public $sess;
    public $bLogin = false;   // non-login users can see some seed info but not grower info, and definitely can't edit anything
    public $oL;
    public $oNavSVA;       // SessionVarAccessor for storing navigational info - some duplicated in forms that use sessions, should be in sync

    public $currentYear;
    public $kGrowerActive; // Grower.mbr_id - only used in modes where one grower's listings are shown

//    private $oSeedLE;      // Console01_ListEdit for seeds (used by SEDOffice and SEDMbr)
    public  $oConsoleTable = NULL;

    public $kKlugeProcessedASkipOrDelete = 0;  // when a skip or delete happens, scroll there instead of to an open form


    function __construct( KeyFrameDB $kfdb1, SEEDSessionAccount $sess, $lang, $eReportMode )
    {
        parent::__construct( $kfdb1, $sess->GetUID(), $lang, $eReportMode );

        $this->sess = $sess;
        $this->bLogin = $sess->IsLogin();

        // This has to be created using the seeds1-kfdb because SED SeedLocal strings are stored in seeds1
        $this->oL = new SEEDLocalDBServer( $kfdb1, $lang, "www.seeds.ca", "SED", array( 'Testing' => $sess->VarGetBool("SEEDTesting") ) );
        $this->oNavSVA = new SEEDSessionVarAccessor( $sess, 'sedNav' );

        if( !($this->currentYear = SEEDSafeGPC_GetInt("CurrentYear")) ) {
            $this->currentYear = date( "Y", time() + (3600*24*120) );      // the year of 120 days hence
        }

//        $this->oSLE = new SeedConsoleLE( $this );
    }

    function S( $k ) { return( $this->oL->S( $k ) ); }

    function SEDStyle()
    {
        $s = "<STYLE>"
            .".sed_grower     { font-family:verdana,helvetica,sans-serif; font-size:10pt; width:80%; }"
            .".sed_edit_form  { padding:0px 1em; }"
            .".sed_edit_form, .sed_edit_form td { "
                ."font-family:verdana,helvetica,sans-serif; font-size:9pt; }"
            .".sed_edit_form input { font-size:8pt;}"
            .".sed_edit_form h3 { font-size:12pt; }"

            .".sed_type { }"
            .".sed_type h3 { font-family: helvetica,arial,sans-serif; }"
            .".sed_seed {}" // font-family: helvetica,arial,sans-serif;font-size:10pt; margin-bottom:15pt; }"
            .".sed_seed_skip { color: #444; background-color:#ee9; }"
            .".sed_seed_delete { color: red; background-color:#fdf }"
            .".sed_seed_change { background-color: #99dd99 }"
            .".sed_seed_mc     { font-weight:bold;text-align:right }"
            .".sed_seed_offer  { font-family: helvetica,arial,sans-serif;font-size:10pt; padding:2px; float:right; background-color:#fff; }"
            .".sed_seed_offer_member       { color: #484; border:2px solid #484 }"
            .".sed_seed_offer_growermember { color: #08f; border:2px solid #08f }"
            .".sed_seed_offer_public       { color: #f80; border:2px solid #f80 }"
            ."</STYLE>";

        return( $s );
    }

    function CategoryDB2K( $catdb )
    {
        // convert from the db category code to the $raCategories key
        foreach( $this->raCategories as $k => $ra ) {
            if( $ra['db'] == $catdb )  return( $k );
        }
        return( "" );
    }

    function update( $bOffice = false )
    {
//        $this->oSLE->Update();

        /* Toggle the bSkip, or bDelete flag on a seed record - assume only one command can be issued at a time
         * Two ways to do this:  $_REQUEST['sskip'] = kRecord                                      -- same for delete
         *                       $_REQUEST['C01FormAction'] = 'sskip'  and C01FormArg1 = kRecord   -- same for delete
         */
        $k = 0;
        if( ($k = SEEDSafeGPC_GetInt( 'sskip' )) ||
            (SEEDSafeGPC_GetStrPlain('c01FormAction') == 'sskip' && ($k = SEEDSafeGPC_GetInt('c01FormArg1'))) )
        {
            $flag = 'bSkip';
        } else if( ($k = SEEDSafeGPC_GetInt( 'sdel' )) ||
                   (SEEDSafeGPC_GetStrPlain('c01FormAction') == 'sdel' && ($k = SEEDSafeGPC_GetInt('c01FormArg1'))) )
        {
            $flag = 'bDelete';
        }

        if( $k ) {
            $kfrS = $this->kfrelS->GetRecordFromDBKey( $k ) or die( "Cannot find seed offer # $k" );

            if( !$this->bOffice && $kfrS->Value('mbr_id') != $this->kGrowerActive ) {
                // Only update the current grower's seeds.  This is mainly to prevent cross-user hacking in the SEDMbr application.
                die( "<P style='color:red'>Cannot skip/delete this grower's seeds</P>" );
            }
            $kfrS->SetValue( $flag, !$kfrS->value($flag) );
            $kfrS->SetValue( 'year', $this->currentYear );
            if( !$kfrS->PutDBRow() ) {
                die( "<P style='color:red'>Update didn't work.  Please email this message to Bob at Seeds of Diversity:</P>".$kfrS->kfrel->kfdb->GetErrMsg() );
            }
            poststore_mirror_seedbasket( $kfrS );
            $this->kKlugeProcessedASkipOrDelete = $k;

//            $this->oSLE->SetScroll( $k );
        }
    }

    function drawGrowerBlock( $kfrG, $bFull = true )
    {
        $raMbr = $this->GetMbrContactsRA( $kfrG->value("mbr_id") );  // derived class fetches mbr_contacts row
// check for failure

        $s = $kfrG->Expand( "<B>[[mbr_code]]: ".@$raMbr['firstname']." ".@$raMbr['lastname']." ([[mbr_id]]) "
             .($kfrG->value('organic') ? $this->S('Organic') : "")."</B><BR/>" );

        if( $bFull ) {
            $s .= SEEDStd_ExpandIfNotEmpty( @$raMbr['company'], "<B>[[]]</B><BR/>" )
                 .SEEDStd_ArrayExpand( $raMbr, "[[address]], [[city]] [[province]] [[postcode]]<BR/>" );

            if( !$kfrG->value('unlisted_phone') )  $s .= SEEDStd_ExpandIfNotEmpty( @$raMbr['phone'], "Tel: [[]]<BR/>" );
            if( !$kfrG->value('unlisted_email') )  $s .= SEEDStd_ExpandIfNotEmpty( @$raMbr['email'], "<I>E-mail: [[]]</I><BR/>" );

            $s .= $kfrG->ExpandIfNotEmpty( 'cutoff', "No requests after: [[]]<BR/>" );

            $s1 = $kfrG->ExpandIfNotEmpty( 'frostfree', "[[]] frost free days. " )
                 .$kfrG->ExpandIfNotEmpty( 'soiltype',  "Soil: [[]]. " )
                 .$kfrG->ExpandIfNotEmpty( 'zone',      "Zone: [[]]. " );
            if( $s1 )  $s .= $s1."<BR/>";

            $ra = array();
            foreach( array('nFlower' => array('flower','flowers'),
                           'nFruit'  => array('fruit','fruits'),
                           'nGrain'  => array('grain','grains'),
                           'nHerb'   => array('herb','herbs'),
                           'nTree'   => array('tree/shrub','trees/shrubs'),
                           'nVeg'    => array('vegetable','vegetables'),
                           'nMisc'   => array('misc','misc')
                           ) as $k => $raV ) {
                if( $kfrG->value($k) == 1 )  $ra[] = "1 ".$raV[0];
                if( $kfrG->value($k) >  1 )  $ra[] = $kfrG->value($k)." ".$raV[1];
            }
            $s .= $kfrG->value('nTotal')." listings: ".implode( ", ", $ra ).".<BR/>";

            if( ($sPM = $this->drawPaymentMethod($kfrG)) ) {
                $s .= "<I>Payment method: $sPM</I><BR/>";
            }

            $s .= $kfrG->ExpandIfNotEmpty( 'notes', "Notes: [[]]<BR/>" );
        }

        return( $s );
    }

    function drawPaymentMethod( $kfrG )
    {
        $ra = array();
        if( $kfrG->value('pay_cash') )        $ra[] = "Cash";
        if( $kfrG->value('pay_cheque') )      $ra[] = "Cheque";
        if( $kfrG->value('pay_stamps') )      $ra[] = "Stamps";
        if( $kfrG->value('pay_ct') )          $ra[] = "Canadian-Tire";
        if( $kfrG->value('pay_mo') )          $ra[] = "Money order";
        if( !$kfrG->IsEmpty('pay_other') )    $ra[] = $kfrG->value('pay_other');

        return( implode( ", ", $ra ) );
    }

    function drawGrowerForm( $oKForm, $bOffice = false )
    {
        $raMbr = $this->GetMbrContactsRA( $oKForm->oDS->value("mbr_id") );  // derived class fetches mbr_contacts row

        $bNew = !$oKForm->oDS->Key();

        $s = "<DIV class='sed_edit_form'>"
            ."<H3>".($bNew ? "Add a New Grower" : ("Edit Grower ".$oKForm->oDS->value('mbr_code')." : "
                                                   .SEEDStd_ArrayExpand( $raMbr, "[[firstname]] [[lastname]] [[company]]</H3>" ) ) );
        if( !$bNew ) {
            $s .= "<DIV style='background-color:#DDDDDD; padding:1em;font-size:9pt;'>"
                 .($bOffice ?
                   "If the name, address, phone number, or email are different, notify Judy to change the master contact database." :
                   "If your name, address, phone number, or email have changed, please notify our office" )
                 ."</DIV><BR/>";
        }
        $s .= "<TABLE border='0'>";
        $nSize = 30;
        $raTxtParms = array('size'=>$nSize);
        if( $bNew ) {
            $s .= $bOffice ? ("<TR>".$oKForm->TextTD( 'mbr_id', "Member #", $raTxtParms  )."</TR>")
                           : ("<TR><td>Member #</td><td>".$oKForm->Value('mbr_id')."</td></tr>" );
        }
        //if( $this->sess->CanAdmin('sed') ) {  // Only administrators can change a grower's code
        if( $this->bOffice ) {  // Only the office application can change a grower's code
            $s .= "<TR>".$oKForm->TextTD( 'mbr_code', "Member Code", $raTxtParms )."</TR>";
        }
        $s .= "<TR>".$oKForm->CheckboxTD( 'unlisted_phone', "Phone", array('sRightTail'=>" do not publish" ) )."</TR>"
             ."<TR>".$oKForm->CheckboxTD( 'unlisted_email', "Email", array('sRightTail'=>" do not publish" ) )."</TR>"
             ."<TR>".$oKForm->TextTD( 'frostfree', "Frost free", $raTxtParms )."<TD></TD></TR>"
             ."<TR>".$oKForm->TextTD( 'soiltype', "Soil type", $raTxtParms )."<TD></TD></TR>"
             ."<TR>".$oKForm->CheckboxTD( 'organic', "Organic" )."</TR>"
             ."<TR>".$oKForm->TextTD( 'zone', "Zone", $raTxtParms )."</TR>"
             ."<TR>".$oKForm->TextTD( 'cutoff', "Cutoff", $raTxtParms )."</TR>"

             ."<TR><TD valign='top'>Payment</TD><TD valign='top'>"
             .$oKForm->Checkbox( 'pay_cash',   "Cash" ).SEEDStd_StrNBSP("",4)
             .$oKForm->Checkbox( 'pay_cheque', "Cheques" ).SEEDStd_StrNBSP("",4)
             .$oKForm->Checkbox( 'pay_stamps', "Stamps" )
             ."<BR/>"
             .$oKForm->Checkbox( 'pay_ct', "CT" ).SEEDStd_StrNBSP("",7)
             .$oKForm->Checkbox( 'pay_mo', "Money Order" )
             ."<BR/>"
             .$oKForm->Text( 'pay_other', "Other ", array('size'=> $nSize-10 ) )
             ."</TD></TR>"
             ."<TR>".$oKForm->TextAreaTD( 'notes', "Notes", 35, 8, array( 'attrs'=>"wrap='soft'"))."</TD></TR>"
             //."<TR>".$oKForm->CheckboxTD( 'bDone', "This Grower is Done:" )."</TR>"
             ."</TABLE>"
             ."<BR><INPUT type=submit value='Save' />"
             ."</DIV>";

        return( $s );
    }

    function drawSeedForm( $oKForm )
    {
        if( !$oKForm->GetKey() ) {
// mbr_id shouldn't be in the form, or propagated, for security when members are editing
            $oKForm->SetValue( 'mbr_id', $this->kGrowerActive );
            $oKForm->SetValue( "year_1st_listed", $this->currentYear );
            $oKForm->SetValue( "type", $this->sess->VarGet('p_seedType') );
        }
        if( $oKForm->Value('price') == "0.00" ) $oKForm->SetValue( 'price', "" );  // blank looks better than the default zero

        $s = "";

        $s .= "
<script>
function eOfferChange() {
    var eOffer = $('#".$oKForm->Name('eOffer')."').val();

    var txtMembers = \"The name and description of these seeds will be visible to the public on the web-based Seed Directory, but only members will be able to see your contact information to request seeds.\";
    var txtGrowers = \"Only members who offer seeds in the Directory will be allowed to request these seeds. Other members and the general public will only see the name and description in the web-based Seed Directory, and these seeds will be indicated as Grower Members Only in the printed directory.\";
    var txtPublic  = \"Anyone who visits the online Seed Directory will be able to request these seeds, whether or not they are a member of Seeds of Diversity. <b>Your name and contact information will be visible to the public.</b> The printed Seed Directory is still only available to members.\";

    switch( eOffer ) {
        default:
        case 'members':        $('#eOfferInstructions').html( txtMembers ); break;
        case 'grower-members': $('#eOfferInstructions').html( txtGrowers ); break;
        case 'public':         $('#eOfferInstructions').html( txtPublic ); break;
    }
}
</script>
";

        $s .= "<DIV class='sed_edit_form'>"
             ."<H2>".($oKForm->GetKey() ? ("Edit: ".$oKForm->Value('type')." - ".$oKForm->ValueEnt('variety')) : "New Seed Offer")."</H2>";

        $sMbrCode = $oKForm->kfrel->kfdb->Query1("SELECT mbr_code FROM seeds.sed_curr_growers WHERE mbr_id='".$oKForm->oDS->Value('mbr_id')."'" );

        $nSize = 30;
        $raTxtParms = array('size'=>$nSize);
        $s .= "<TABLE border='0'>"
             ."<TR><TD><B>".$sMbrCode."</B></TD>"
             ."<TD>".SEEDStd_StrNBSP("",20)."<INPUT type=submit value='Save'/></TD></TR>"

             ."<tr><td colspan='2'>&nbsp;</td></tr>"

             ."<tr><td valign='top'>I offer these seeds to</td><td valign='top'>"
                 .$oKForm->Select2( 'eOffer',
                                    array("All members"=>'member',
                                          "Members who list seeds"=>'grower-member',
                                          "General public"=>'public'),
                                    "", array('attrs'=>"onchange='eOfferChange()'") )
                 ."<div id='eOfferInstructions' style='display:inline-block;margin-left:20px'></div>"
                 ."<script>eOfferChange();</script>"    // initialize the eOffer message
                 ."</td></tr>"

             ."<tr><td colspan='2'>&nbsp;</td></tr>"

             ."<tr style='margin-top:10px'><td valign='top'>Price</td><td valign='top'>$"
                 .$oKForm->Text( 'price', "", $raTxtParms )
                 ."<div id='priceInstructions' style='display:inline-block;margin-left:20px'>We recommend $3.50 for seeds and $12.00 for roots and tubers. That is the default if you leave this field blank. Members who offer seeds (like you!) get an automatic discount of $1 per item.</div>"
                 ."</td></tr>"

             ."<tr><td colspan='2'>&nbsp;</td></tr>"



             ."<TR><TD colspan='2'>Year first listed: ".$oKForm->oDS->Value('year_1st_listed')
             .$oKForm->Hidden( "year_1st_listed" )."</TD></TR>"
             ."<TR><TD valign='top'>Category</TD><TD valign='top'>"
             .$oKForm->Select( "category", "", array( "VEGETABLES" => "VEGETABLES",
                                                      "FLOWERS AND WILDFLOWERS" => "FLOWERS AND WILDFLOWERS",
                                                      "FRUIT"=>"FRUIT",
                                                      "GRAIN"=>"GRAIN",
                                                      "HERBS AND MEDICINALS"=>"HERBS AND MEDICINALS",
                                                      "MISC"=>"MISC",
                                                      "TREES AND SHRUBS"=>"TREES AND SHRUBS" ) )
             ."</TD></TR>"
             ."<TR>".$oKForm->TextTD( 'type',          "Type",     $raTxtParms )."</TR>"
             ."<TR>".$oKForm->TextTD( 'variety',       "Variety",  $raTxtParms )."</TR>"
             ."<TR>".$oKForm->TextTD( 'bot_name',      "Botanical",$raTxtParms )."</TR>"
             ."<TR>".$oKForm->TextTD( 'days_maturity', "Days",     $raTxtParms )."</TR>"
             ."<TR>".$oKForm->TextTD( 'quantity',      "Quantity", $raTxtParms )."</TR>"
             ."<TR>".$oKForm->TextTD( 'origin',        "Origin",   $raTxtParms )."</TR>"
             ."<TR>".$oKForm->TextAreaTD( 'description', "Description", 35, 8, array( 'attrs'=>"wrap='soft'") )."</TR>"

                 ."</TABLE>"
             ."<BR><INPUT type=submit value='Save'/>"
             ."</DIV>"
//             .$this->oSLE->ExpandTags( $oKForm->oDS->Key(), "<P style='text-align:center'><A HREF='{$_SERVER['PHP_SELF']}?[[LinkParmScroll]]'>Close Form</A></P> " );
;


        return( $s );
    }

    /*****************************************************
     *  Base methods that have to be extended by derived classes
     */

    protected function GetMbrContactsRA( $kMbr )
    {
        return( array() );   // OVERRIDE to return an mbr_contacts array for the given member
    }


    /*****************************************************
     *  Derived methods that extend the parent class
     *
     *  sedCommonDraw::DrawSeedFromKFR() will draw Edit/Skip/Delete and Un-skip/Un-Delete buttons
     *                                   but needs its child (UI) class to tell it how
     *
     */

    protected function getButtonsSeed1( KFRecord $kfrS )
    {
        // sedCommonDraw uses this method to fetch buttons for Edit, Skip, Delete (if available in the current mode)
        $s = "";

        if( $this->eReportMode == "EDIT" && $this->sess->CanWrite('sed') ) {
            $s = SEEDStd_StrNBSP("  ")
                .$this->oConsoleTable->Table_ButtonEdit( $kfrS->Key(), $this->S('Edit'), array('bInline'=>true) );
            if( !$kfrS->Value('bSkip') ) {
                // Do not use the Console01Table delete button - we implement bDelete toggle instead
                $s .= SEEDStd_StrNBSP("  ")
                     .$this->oConsoleTable->Table_Button( $this->oL->GetLang() == 'FR' ? "Passer" : "Skip",
                                                          $this->raBtnSkipDel( array('sskip'=>$kfrS->Key()) ) );
            }
            if( !$kfrS->Value('bDelete') ) {
                $s .= SEEDStd_StrNBSP("  ")
                     .$this->oConsoleTable->Table_Button( $this->oL->GetLang() == 'FR' ? "Supprimer" : "Delete",
                                                          $this->raBtnSkipDel( array('sdel'=>$kfrS->Key()) ) );
            }
        }
        return( $s );
    }

    protected function getButtonsSeed2( KFRecord $kfrS )
    {
        // sedCommonDraw uses this method to fetch buttons for Un-Skip, Un-Delete (if available in the current mode)
        $s = "";

        if( $this->eReportMode == "EDIT" ) {
            if( $kfrS->value('bDelete') ) {
                $s = $this->oConsoleTable->Table_Button( $this->oL->GetLang() == 'FR' ? "Annuler" : "Un-Delete",
                                                         $this->raBtnSkipDel( array('sdel'=>$kfrS->Key()) ) );
            } else if( $kfrS->value('bSkip') ) {
                $s = $this->oConsoleTable->Table_Button( $this->oL->GetLang() == 'FR' ? "Annuler" : "Un-Skip",
                                                         $this->raBtnSkipDel( array('sskip'=>$kfrS->Key()) ) );
            }
        }
        return( $s );
    }

    private function raBtnSkipDel( $raPropagate )
    {
        // Given the array of parms for a skip/del button to propagate, return the button parms
        return( array( 'bInline'=>true,
                       'bAutoScroll'=>true,
                       'raPropagate'=>$raPropagate ) );
    }
}

/* Base class for SEDMbrGrower and SEDOfficeGrower
 */
class SEDGrowerWorker extends Console01_Worker1
{
    function __construct( &$oC, &$kfdb, &$sess )
    {
        parent::__construct( $oC, $kfdb, $sess );
    }

    function NewGrowerForm()
    {
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

        $oKForm = new KeyFrameUIForm( $this->oC->oSed->kfrelG, NULL, array( 'formdef' => $kfuFormDef,
                                                                            'DSParms'=> array('fn_DSPreStore'=>array($this,'growerForm_DSPreStore')) ) );

        return( $oKForm );
    }

    function growerForm_DSPreStore( $oDS )
    /*************************************
        The Grower oForm should override its DSPreStore with this, to fix up the record before it is written to the db.
        Return true to proceed with the db write.
    */
    {
        $oSed = $this->oC->oSed;

        if( !$oSed->bOffice ) {
            if( $oDS->Value('mbr_id') != $oSed->sess->GetUID() ) {
                // Mbr instance can't update someone else's information (e.g. through a cross-user hack)
                die( "Cannot update grower information - mismatched grower code" );
            }

// *** Do this for seeds too
            //kluge: prior to proofreading we did "update seeds.sed_curr_growers set _updated_by_mbr=_updated where _updated_by=mbr_id"
            //This is handy if we want to see what the member might have done, but office editing overwrote the timestamps
            $oDS->SetValue( '_updated_by_mbr', date("y-m-d") );  // this really should be the new _updated but that's hard to get
        }

        if( !$oDS->Value('year') )  $oDS->SetValue( 'year', $oSed->currentYear );

        $oDS->SetValue( 'bChanged', 1 );

        return( true );
    }

}


/* Base class for SEDMbrSeeds and SEDOfficeSeeds
 */
class SEDSeedsWorker extends Console01_Worker1
{
    protected $oSNavForm = NULL;  // use this for a form in the Controls area

    function __construct( &$oC, &$kfdb, &$sess )
    {
        parent::__construct( $oC, $kfdb, $sess );

// use SEDSearchControl
        $this->oSNavForm = new SEEDFormSession( $sess, 'sedSNav', 'S' );
        $this->oSNavForm->Update();
    }

    /* oKForm for Seeds, with DSPreStore
     */
    function NewSeedForm()
    {
        $oForm = new KeyFrameUIForm( $this->oC->oSed->kfrelS, NULL, array('DSParms'=>array('fn_DSPreStore'=>array($this,'seedForm_DSPreStore'),
                                                                                           'fn_DSPostStore'=>array($this,'seedForm_DSPostStore')
        )));

        return( $oForm );
    }

    function seedForm_DSPreStore( $oDS )
    /***********************************
        The Seeds oForm should override its DSPreStore with this, to fix up the record before it is written to the db.
        Return true to proceed with the db write.
    */
    {
        $oSed = $this->oC->oSed;

        if( !$oDS->Key() ) {
            /* Adding a new seed listing
             */
            if( $oSed->kGrowerActive < 1 )  die( "Cannot add this seed. There is no active grower record." );

            $oDS->SetValue( 'mbr_id', $oSed->kGrowerActive );
            $oDS->SetValue( "year_1st_listed", $oSed->currentYear );
            $oDS->SetValue( 'year', $oSed->currentYear );
        }

        if( !$oSed->bOffice && $oDS->Value('mbr_id') != $oSed->kGrowerActive ) {
            // Prevent cross-user hacking: only update seeds for the current user.
            // In the office version you can edit seeds in the order they appear in the directory, not just per-grower,
            // so don't enforce a current grower
            die( "Cannot update this seed - mismatched grower code" );
        }

        // Force ALL CAPS on Type and Variety
        $oDS->SetValue( 'type', strtoupper($oDS->Value( 'type' )) );
        $oDS->SetValue( 'variety', strtoupper($oDS->Value( 'variety' )) );

        // mysql can't convert a '' to a 0.00
        if( $oDS->Value('price') == '' ) $oDS->SetValue( 'price', 0.00 );

        $oDS->SetValue( 'bChanged', 1 );

        return( true );
    }

    function seedForm_DSPostStore( KFRecord $kfrS, KeyFrameUIForm $oFormS )
    {
        // An insert or update occurred. Mirror this sed_curr_seeds row in SEEDBasket_Products.

        poststore_mirror_seedbasket( $kfrS );

/*
        $kfdb->Execute( "DELETE PE FROM seeds.SEEDBasket_ProdExtra PE, seeds.SEEDBasket_Products P  "
                       ."WHERE P._key=PE.fk_SEEDBasket_Products AND P.product_type='seeds'" );
        $kfdb->Execute( "DELETE FROM seeds.SEEDBasket_Products WHERE product_type='seeds'" );

        $sSql1 = "INSERT INTO seeds.SEEDBasket_Products (_key,uid_seller,product_type,quant_type,item_price"
                .",img,v_t1,v_t2,v_t3,sExtra" // text fields need explicit defaults
                .") VALUES ";
        $sSql2 = "INSERT INTO seeds.SEEDBasket_ProdExtra (fk_SEEDBasket_Products,k,v) VALUES ";
        $kP = 1000;

        if( ($kfrc = $this->oC->oSed->GetKfrcS( "1=1", array(), "VIEW" )) ) {   // not bSkip and not bDelete
            while( $kfrc->CursorFetch() ) {
            [*
                $kfrP = $oSBDB->GetKfrel("P")->CreateRecord();
                $kfrP->SetValue( 'uid_seller', $kfrc->Value('mbr_id') );
                $kfrP->SetValue( 'product_type', "seeds" );
                $kfrP->SetValue( 'quant_type', "ITEM-1" );
                $kfrP->SetValue( 'item_price', 3 );
                $kfrP->PutDBRow();

                $oSBDB->SetProdExtraList( $kfrP->Key(),
                                          array( 'category'      => $kfrc->Value( 'category' ),
                                                 'species'       => $kfrc->Value( 'type' ),
                                                 'variety'       => $kfrc->Value( 'variety' ),
                                                 'bot_name'      => $kfrc->Value( 'bot_name' ),
                                                 'days_maturity' => $kfrc->Value( 'days_maturity' ),
                                                 'quantity'      => $kfrc->Value( 'quantity' ),
                                                 'origin'        => $kfrc->Value( 'origin' ),
                                                 'description'   => $kfrc->Value( 'description' )
                                          ) );
            *]
                if( $kP > 1000 ) $sSql1 .= ",";
                $sSql1 .= "($kP,'".$kfrc->Value('mbr_id')."','seeds','ITEM-1','3'"
                         .",'','','','',''" // img,v_t1,v_t2,v_t3,sExtra
                         .")";

                if( $kP > 1000 ) $sSql2 .= ",";
                $sSql2 .= "($kP,'category','".     addslashes($kfrc->Value('category'))."'),"
                         ."($kP,'species','".      addslashes($kfrc->Value('type'))."'),"
                         ."($kP,'variety','".      addslashes($kfrc->Value('variety'))."'),"
                         ."($kP,'bot_name','".     addslashes($kfrc->Value('bot_name'))."'),"
                         ."($kP,'days_maturity','".addslashes($kfrc->Value('days_maturity'))."'),"
                         ."($kP,'quantity','".     addslashes($kfrc->Value('quantity'))."'),"
                         ."($kP,'origin','".       addslashes($kfrc->Value('origin'))."'),"
                         ."($kP,'description','".  addslashes($kfrc->Value('description'))."')";

                ++$kP;
            }
        }
        //$kfdb->SetDebug(1);
        $kfdb->Execute( $sSql1 );
        $kfdb->Execute( $sSql2 );

        $kfdb->Execute( "UPDATE seeds.SEEDBasket_Products SET item_price='3.50' WHERE product_type='seeds'" );
        $kfdb->Execute( "UPDATE seeds.SEEDBasket_Products P, seeds.SEEDBasket_ProdExtra PE SET P.item_price='12.00' "
                       ."WHERE P.product_type='seeds' AND P._key=PE.fk_SEEDBasket_Products AND PE.k='species' AND "
                       ."(PE.V IN ('POTATO','JERUSALEM ARTICHOKE','ONION','GARLIC'))" );
*/
    }
}


/* A search control based on SEEDForm:SearchControl for searching sed_seeds(_curr)
 */
class SEDSearchControl
{
    private $oForm;
    private $raSearchControlConfig;

    function __construct( SEEDSession $sess )   // could extend this with a template parm
    {
        $raT = array( 'Species'=>'type',
                      'Cultivar'=>'variety',
                      'Botanical name'=>'bot_name',
                      'Origin'=>'origin',
                      'Description'=>'description',
                      'Days to Maturity'=>'days_maturity',
                      'Grower number'=>'mbr_id' );
        $this->raSearchControlConfig =
            array( 'filters' => array( $raT, $raT, $raT ),
                   'template' => "<style>#sedSeedSearch,#sedSeedSearch input,#sedSeedSearch select {font-size:9pt;}"
                                ."</style>"
                                ."<div id='sedSeedSearch'>"
                                ."<div style='width:4ex;display:inline-block;'>&nbsp;</div>[[fields1]] [[op1]] [[text1]]<br/>"
                                ."<div style='width:4ex;display:inline-block;'>and&nbsp;</div>[[fields2]] [[op2]] [[text2]]<br/>"
                                ."<div style='width:4ex;display:inline-block;'>and&nbsp;</div>[[fields3]] [[op3]] [[text3]]<br/>"
                                ."</div>" );

        $this->oForm = new SEEDFormSession( $sess, 'sedSNav', 'S' );
        $this->oForm->Update();
    }

    function Clear()
    {
// this assumes the template hard-coded above; would have to be overridden if a template were passed into the constructor
        $this->oForm->CtrlGlobalSet('srch_fld1','');
        $this->oForm->CtrlGlobalSet('srch_fld2','');
        $this->oForm->CtrlGlobalSet('srch_fld3','');
        $this->oForm->CtrlGlobalSet('srch_op1','');
        $this->oForm->CtrlGlobalSet('srch_op2','');
        $this->oForm->CtrlGlobalSet('srch_op3','');
        $this->oForm->CtrlGlobalSet('srch_val1','');
        $this->oForm->CtrlGlobalSet('srch_val2','');
        $this->oForm->CtrlGlobalSet('srch_val3','');
    }

    function GetDBCond()
    {
        return( $this->oForm->SearchControlDBCond( $this->raSearchControlConfig ) );
    }

    function DrawSearchControl()
    {
        return( $this->oForm->SearchControl( $this->raSearchControlConfig ) );
    }

    function DrawHidden( $fld, $op, $val )
    /*************************************
        Put these in a form with a submit button to force a particular search
     */
    {
        return( $this->oForm->Hidden2( 'srch_fld1', array('value'=>$fld, 'sfParmType'=>'ctrl_global') )
               .$this->oForm->Hidden2( 'srch_op1',  array('value'=>$op,  'sfParmType'=>'ctrl_global') )
               .$this->oForm->Hidden2( 'srch_val1', array('value'=>$val, 'sfParmType'=>'ctrl_global') ) );
    }
}


/* Console01Table that draws a list-form table for seeds
 */
class SedSeedConsole01Table extends Console01TableKFRCursor
{
    private $oSed;

    function __construct( SEDCommon $oSed, KeyFrameUIForm $oKForm = NULL )    // base class allows null form (it creates a tmp blank form using the kfrelS)
    {
        $this->oSed = $oSed;

        parent::__construct( $this->oSed->kfrelS, $oKForm );
    }

    function Table_Item( KFRecord $kfrS )    // sed_curr_seeds record
    {
        return( $this->oSed->DrawSeedFromKFR( $kfrS ) );
    }

    function Table_Form( SEEDForm $oForm )  // $oForm is NULL if no item is selected
    {
        return( $this->oSed->drawSeedForm( $oForm ) );
    }
}



function poststore_mirror_seedbasket( KFRecord $kfrS )
{
return;
    /* When a sed_curr_seeds record changes, call here to mirror the record to SEEDBasket_Products
     */
    $kfdb = $kfrS->kfrel->kfdb;

        include_once( SEEDCORE."SEEDBasketDB.php" );
        $oSBDB = new SEEDBasketDB( $kfdb, 0, SITE_LOG_ROOT );

        $kProd = ($kfrPE = $oSBDB->GetKFRCond( "PE", "k='kSED' AND v='".$kfrS->Key()."'" ))
                 ? $kfrPE->Value( 'fk_SEEDBasket_Products' ) : 0;

//$kfdb->SetDebug(2);
        if( $kfrS->Value('bDelete') || $kfrS->Value('bSkip') ) {
            // The seed record is not active, so if there is a Product record delete it.
            // Some day you will just change the _status of the Product record but for now keep it simple.
            if( $kProd ) {
                $kfdb->Execute( "DELETE FROM seeds.SEEDBasket_Products WHERE _key='$kProd'" );
                $kfdb->Execute( "DELETE FROM seeds.SEEDBasket_ProdExtra WHERE fk_SEEDBasket_Products='$kProd'" );
            }
        } else {
            // If the Product record exists, update it. If not, insert one.

            $kfrP = $kProd ? $oSBDB->GetKFR( "P", $kProd ) : $oSBDB->GetKfrel("P")->CreateRecord();

            $kfrP->SetValue( 'uid_seller', $kfrS->Value('mbr_id') );
            $kfrP->SetValue( 'product_type', "seeds" );
            $kfrP->SetValue( 'quant_type', "ITEM-1" );
            if( !($price = floatval($kfrS->Value('price'))) ) {     // floatval because "0.00" is not false if it's a string
                $price = in_array( $kfrS->Value('type'), array('POTATO','JERUSALEM ARTICHOKE','ONION','GARLIC') ) ? "12.00" : "3.50";
            }
            $kfrP->SetValue( 'item_price', $price );
            $kfrP->PutDBRow();

            $oSBDB->SetProdExtraList( $kfrP->Key(),
                                          array( 'category'      => $kfrS->Value( 'category' ),
                                                 'species'       => $kfrS->Value( 'type' ),
                                                 'variety'       => $kfrS->Value( 'variety' ),
                                                 'bot_name'      => $kfrS->Value( 'bot_name' ),
                                                 'days_maturity' => $kfrS->Value( 'days_maturity' ),
                                                 'quantity'      => $kfrS->Value( 'quantity' ),
                                                 'origin'        => $kfrS->Value( 'origin' ),
                                                 'description'   => $kfrS->Value( 'description' ),
                                                 'eOffer'        => $kfrS->Value( 'eOffer' ),
                                                 'kSED'          => $kfrS->Key()                    // link this Product with this sed_curr_seed
                                          ) );
        }
//$kfdb->SetDebug(0);
}

/*  No need to define these since they're always provided as an sql package

CREATE TABLE sed_growers (
        _key        INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT,
        _created    DATETIME,
        _created_by INTEGER,
        _updated    DATETIME,
        _updated_by INTEGER,
        _status     INTEGER DEFAULT 0,

    mbr_id          INTEGER NOT NULL,                   -- could be fk_mbr_contacts (no impact on kfrel that don't involve mbr_contacts)
    mbr_code        CHAR(10),                           -- keep this here instead of mbr_contacts so sed_seeds has a record of province
    frostfree       VARCHAR(200),
    soiltype        VARCHAR(200),
    organic         BOOL        DEFAULT 0,
    zone            VARCHAR(200),
    notes           TEXT,
    unlisted_phone  BOOL        DEFAULT 0,
    unlisted_email  BOOL        DEFAULT 0,
    cutoff          VARCHAR(200),
    pay_cash        BOOL        DEFAULT 0,
    pay_cheque      BOOL        DEFAULT 0,
    pay_stamps      BOOL        DEFAULT 0,
    pay_ct          BOOL        DEFAULT 0,
    pay_mo          BOOL        DEFAULT 0,              -- money order
    pay_other       VARCHAR(200),

    nTotal          INTEGER     DEFAULT 0,
    nFlower         INTEGER     DEFAULT 0,
    nFruit          INTEGER     DEFAULT 0,
    nGrain          INTEGER     DEFAULT 0,
    nHerb           INTEGER     DEFAULT 0,
    nTree           INTEGER     DEFAULT 0,
    nVeg            INTEGER     DEFAULT 0,
    nMisc           INTEGER     DEFAULT 0,

    year            INTEGER,


-- Uncomment for sed_curr_seeds
--  bSkip           BOOL         DEFAULT 0,
--  bDelete         BOOL         DEFAULT 0,
--  bChanged        BOOL         DEFAULT 0,
-- // obsolete  bDone           BOOL         DEFAULT 0,
--  bDoneMbr        BOOL         DEFAULT 0,  -- the member clicked Done themselves
--  bDoneOffice     BOOL         DEFAULT 0,  -- we clicked Done in the office
--  _updated_by_mbr DATETIME,


    INDEX sed_growers_mbr_id   (mbr_id),
    INDEX sed_growers_mbr_code (mbr_code)
);


DROP TABLE IF EXISTS sed_seeds;
CREATE TABLE sed_seeds (
        _key        INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT,
        _created    DATETIME,
        _created_by INTEGER,
        _updated    DATETIME,
        _updated_by INTEGER,
        _status     INTEGER DEFAULT 0,

    mbr_id          INTEGER NOT NULL,                   -- not fk_sed_curr_growers because that won't work with sed_seeds
    category        VARCHAR(200) NOT NULL DEFAULT '',
    type            VARCHAR(200) NOT NULL DEFAULT '',
    variety         VARCHAR(200) NOT NULL DEFAULT '',
    bot_name        VARCHAR(200) NOT NULL DEFAULT '',
    days_maturity   VARCHAR(200) NOT NULL DEFAULT '',
    quantity        VARCHAR(200) NOT NULL DEFAULT '',
    origin          VARCHAR(200) NOT NULL DEFAULT '',
    year_1st_listed INTEGER NOT NULL DEFAULT 0,
    description     TEXT,
    year            INTEGER NOT NULL DEFAULT 0,         -- the year of this SED


-- Uncomment for sed_curr_seeds
--  bSkip           BOOL         DEFAULT 0,
--  bDelete         BOOL         DEFAULT 0,
--  bChanged        BOOL         DEFAULT 0,
--  _updated_by_mbr DATETIME,


    INDEX sed_seeds_mbr_id     (mbr_id),
    INDEX sed_seeds_catgy      (category(20)),
    INDEX sed_seeds_type       (type(20)),
    INDEX sed_seeds_variety    (variety(20)),
);

*/

?>
