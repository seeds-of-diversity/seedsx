<?php

// Any reason we can't build the kfr in oMbrOrder->kfr, remove the pre-conf parm from DrawTicket, make sure oMbrOrder
// does the right thing when oMbrOrder->kfr->Key == 0 (pre-confirmed order)

// mbr_PPcancel has to receive a sessionid and store the cancelled status - it's the only way that a user can really cancel from PayPal


/* mbrOrderCheckout
 *
 * Copyright (c) 2009-2015 Seeds of Diversity Canada
 *
 * Base implementation for an online checkout system
 */
include_once(STDINC."KeyFrame/KFUIForm.php");
include_once(STDINC."SEEDTemplate.php");
include_once(SEEDCOMMON."siteutil.php");
include_once("mbrOrder.php");
include_once( SEEDCOMMON."mbr/mbrSitePipe.php" );

/* State control:
 *
 * A PHP session is maintained during the checkout process, and also passed through PayPal to the finish screen.
 *
 * There are two phases for a checkout: pre-confirmation and post-confirmation.
 * During pre-confirmation, form values are propagated by http and session, there is no database record.
 * During post-confirmation, form values are locked into a db record, and the only meaningful session parm is the kOrder (i.e. mbr_orders._key)
 *
 * States are not propagated explicitly - would lead to confusion when the user uses the Back or History browser controls.  Instead, state
 * is deduced from the session or db record.
 * State transitions are accomplished by issuing state-transition codes by http.  Only transition codes that are allowed for the current state are accepted.
 *
 * There is no way to "look up" the db record from the UI / URL without the session id, and the session active.
 * i.e. we never put the _key on a url parm since this would allow a person to guess another person's url parm
 *
 * States:
 * MBROC_STATE_FORM     - show the form with current data from session (Note that an override of DrawForm() can implement any number of sequential forms setting state in session)
 *                        You get here if:
 *                            !session.kOrder (pre-confirmation) && !MBROC_ST_VALIDATE
 *                            MBROC_ST_START is issued,
 *                            MBROC_STATE_VALIDATE fails to validate user data
 *                        Transitions:
 *                            MBROC_ST_VALIDATE - submit the form to MBROC_STATE_VALIDATE
 *
 * MBROC_STATE_VALIDATE - store http parms in session, validate the user input, set session.bValidated;
 *                        If validation succeeds, show the order summary and ask for confirmation, else fall back to MBROC_STATE_FORM
 *                        You get here if:
 *                            !session.kOrder (pre-confirmation) && MBROC_ST_VALIDATE
 *                        Transitions:
 *                            MBROC_ST_FORM - back to form (this is implied if session.kOrder is blank and no transition code is issued)
 *                            MBROC_ST_CONFIRM - confirm that the user wants to go to MBROC_STATE_CONFIRMED
 *                            MBROC_ST_START - reload a new blank FORM with a blank session (this is how to cancel the process at this stage)
 *
 * MBROC_STATE_CONFIRMED - store values from session into db record if session.kOrder is blank, show the order summary with instructions for payment (how to get help, how to cancel)
 *                        You get here if:
 *                            !session.kOrder && MBROC_ST_CONFIRM && session.bValidated
 *                            session.kOrder && eStatus='New'
 *                        Transitions:
 *                            MBROC_ST_START - reload a new blank FORM with a blank session
 *                            MBROC_ST_CANCEL - set eStatus='Cancelled', drop kOrder from session, and show cancellation screen
 *                            You can change the ePayType as you wish, returning to this state with different payment instructions.
 *                            If paying by cheque, the transaction stays here until the office receives the cheque.  We should send
 *                            you an email every few days with a reminder and a cancel option.
 *                            If PayPal is successful, PPIPN sets eStatus='Paid', which pushes the state to MBROC_STATE_PAID
 *
 * MBROC_STATE_PAID     - show the order summary with thanks
 *                        You get here if:
 *                            eStatus='Paid'
 *                        Transitions:
 *                            Only the office administrator can change this to Filled or Cancelled
 *
 * MBROC_STATE_CANCELLED - show "Your order was cancelled"
 *                        You get here if:
 *                            eStatus='Cancelled'
 *
 * MBROC_STATE_FILLED   - show "Your order was filled"
 *                        You get here if:
 *                            eStatus='Filled'
 *
 *
 * Parm management:
 *
 * Start     - clears all session vars
 * Form      - shows session values, posts to http using kfu.
 * Validate  - marshalls from http using kfu, validates and normalizes, stores in session
 * Confirmed - stores session values to db
 *
 * Requirement: All form parms must be posted using kfu, including user forms in overrides of DrawForm*
 * Limitation: Can't currently store values in session across Start.  Remedies: Start knows what to retain, all form parms use prefix.
 * Feature: If a bad form posts directly to Confirmed, http parms are ignored.  Only parms that survive Validate can be stored in the db.
 */


// State codes
define("MBROC_STATE_FORM",     "FORM");
define("MBROC_STATE_VALIDATE", "VALIDATE");
define("MBROC_STATE_CONFIRMED","CONFIRMED");
define("MBROC_STATE_PAID",     "PAID");
define("MBROC_STATE_CANCELLED","CANCELLED");
define("MBROC_STATE_FILLED",   "FILLED");


// State transition control codes: issue these as form parms to push the state machine into different states
define("MBROC_ST_START",      "START");       // force a new blank MBROC_STATE_FORM
define("MBROC_ST_VALIDATE",   "VALIDATE");    // in pre-confirmed, show the MBROC_STATE_VALIDATE confirmation screen. Ignored in other states.
define("MBROC_ST_FORM",       "FORM");        // in pre-confirmed, show the form with current data. Ignored in other states.  This is equivalent to pre-confirmed with no transition code.
define("MBROC_ST_CONFIRM",    "CONFIRM");     // in pre-confirmed, create db record and set eStatus='New'. Ignored in other states.
define("MBROC_ST_CANCEL",     "CANCEL");      // in eStatus='New', set eStatus='Cancelled'.

//var_dump($_REQUEST);

class MbrOrderCheckout {
    var $kfdb;
    var $sess;
    var $lang;

    private $oApp;  // should match the above variables; replace those with this

//  var $kfrelMbrOrder;
    var $oMbrOrder;
    var $oL;
    var $oKForm;
    var $kfrOC = NULL;		// assemble form parms here during pre-conf stage
    var $raFormErrors = array();

    private $oTmpl;
    private $raConfig = array();// not used


    function __construct( KeyFrameDB $kfdb, SEEDSessionAccount $sess, $lang = "EN" )
    /*******************************************************************************
     */
    {
        // global $kfrdef_mbrOrder;

        $this->kfdb = $kfdb;
        $this->sess = $sess;
        $this->lang =  $lang;

// use SEEDConfig_NewAppConsole_LoginNotRequired()
        $this->oApp = SEEDConfig_NewAppConsole( ['sessPermsRequired'=>['PUBLIC'],
                                                 'sessUIConfig' => ['bTmpActivate'=>false,
                                                                    'bLoginNotRequired'=>true],
                                                 'lang'=>$lang] );    // no perms required but will detect current login if any

//      $this->kfrelMbrOrder = new KeyFrameRelation( $kfdb, $kfrdef_mbrOrder, 0 );      // $sess might have GetUID(), or it might not
        $this->oMbrOrder = new MbrOrder( $kfdb, $lang );
        $this->_setLocalText( $lang );
        $this->oKForm = new KeyFrameUIForm($this->oMbrOrder->kfrelOrder, "A");
        $this->oTmpl = $this->makeTemplate();
    }

    private function makeTemplate()
    {
        /* Templates in files:
         *    named templates are defined in a file;
         *    raConfig['fTemplates'] is an array of files whose named templates override the named templates in the base file
         */
        $raFTemplates = array_merge( array( SEEDCOMMON."mbr/mbrOrderCheckout.html" ),
                                     (isset($this->raConfig['fTemplates']) ? $this->raConfig['fTemplates'] : array() ) );

        /* Tag Resolution:
         *     SEEDForm tags in Vanilla mode (require Form prefix)
         *     SEEDLocal tags (require Local prefix)
         *     Basic tags (appended to the list by EnableBasicResolver)
         */
        $oForm = new SEEDForm( 'Plain' );
        $tagParms = array();
        $tagParms['raResolvers'] = array();
        $tagParms['raResolvers'][] = array( 'fn'=>array($oForm,'ResolveTag'),    'raParms'=>array('bRequireFormPrefix'=>true) );
        $tagParms['raResolvers'][] = array( 'fn'=>array($this->oL,'ResolveTag'), 'raParms'=>array('bRequireLocalPrefix'=>true) );
        $tagParms['EnableBasicResolver'] = array(); // no special parms, but you'd put them here

        /* Global variables for templates:
         *     e.g. site config, links to url root
         *     When each template is expanded, the method allows template-specific variables; these apply to all templates (and can be overridden)
         */
        $raTmplVars = array_merge( array( 'lang' => $this->oL->GetLang(),
                                          'SitePathSelf' => Site_path_self() ),
                                   (isset($raConfig['raTmplVars']) ? $raConfig['raTmplVars'] : array() ) );

        $o = new SEEDTemplate_Generator( array( 'fTemplates' => $raFTemplates,
                                                'SEEDTagParms' => $tagParms,
                                                'vars' => $raTmplVars
        ) );
        $oTmpl = $o->MakeSEEDTemplate();

        return( $oTmpl );
    }


    // keep mbrocKOrder out of mbrocdata so a hacker can't set it via http
    function getSessKOrder()   { return( $this->sess->VarGet('mbrocKOrder') ); }
    function setSessKOrder($k) { $this->sess->VarSet('mbrocKOrder', $k); }

    function DoStart()
    /*****************
        Force the checkout system to start over from the beginning
     */
    {
        $this->setSessKOrder(0);
// kluge: won't work if SEEDSession implements session vars differently
        unset($_SESSION['mbrocdata']);
    }

    function Checkout( $raParms = NULL, $bGPC = true )
    /*************************************************
     */
    {
        $s = "";

        if( !$raParms )  $raParms = $_REQUEST;

        $eMST = @$_REQUEST['mbrocst'];

        if( $eMST == MBROC_ST_START ) {
            $this->DoStart();
        }


        if( !$this->getSessKOrder() ) {
            /* Pre-confirmation (FORM, VALIDATE)
             */
            switch( $eMST ) {
                case MBROC_ST_CONFIRM:
                    $state = $this->ValidateParms() ? MBROC_STATE_CONFIRMED : MBROC_STATE_FORM;
                    if( $state == MBROC_STATE_CONFIRMED ) {
                        // ValidateParms succeeded, created $this->kfrOC
                        // Put it in the database, and load it up in oMbrOrder so ConfirmedDraw works

// set pay_status until we get the whole system over to eStatus
//$this->kfrOC->SetValue('pay_status',1);

                        // Kluge: before committing, we need to fill in pay_total.  This would be easier if the pre-conf order were stored in oMbrOrder instead of kfrOC
                        $this->oMbrOrder->setKOrder( 0, $this->kfrOC );
                        $this->oMbrOrder->computeOrder();
                        $this->kfrOC->SetValue('pay_total', $this->oMbrOrder->nTotal );

                        // The Office Orders always creates paid-cheque entries
                        if( defined('MbrOrderCheckoutOffice') ) {
                            $this->kfrOC->SetValue( 'eStatus', MBRORDER_STATUS_PAID );
                            $this->kfrOC->SetValue( 'ePayType', "Cheque" );
                        }

                        if( $this->kfrOC->PutDBRow() ) {
                            $this->setSessKOrder( $this->kfrOC->Key() );
                            $this->oMbrOrder->setKOrder( $this->kfrOC->Key() );
include_once( SEEDAPP."basket/sodBasketFulfil.php" );
                            $o = new SoDOrder_MbrOrder( $this->oApp );
                            $o->CreateFromMbrOrder( $this->kfrOC->Key() );
                        } else {
                            die("Sorry, our database cannot save your order.  Please contact our office for assistance.");
                        }
                    }
                    break;

                case MBROC_ST_VALIDATE:
                    $state = $this->ValidateParms() ? MBROC_STATE_VALIDATE : MBROC_STATE_FORM;
                    break;

                case MBROC_ST_FORM:
                default:
                    $state = MBROC_STATE_FORM;
                    break;
            }

        } else {
            /* Post-confirmation
             */
            $this->oMbrOrder->setKOrder( $this->getSessKOrder() );

            if( $eMST == MBROC_ST_CANCEL ) {
                $this->oMbrOrder->kfr->SetValue( 'eStatus', MBRORDER_STATUS_CANCELLED );
                $this->oMbrOrder->kfr->PutDBRow();
            }

            switch( $this->oMbrOrder->kfr->Value('eStatus') ) {
                case MBRORDER_STATUS_NEW:
                    $state = MBROC_STATE_CONFIRMED;
                    break;
                case MBRORDER_STATUS_PAID:
                    $state = MBROC_STATE_PAID;
                    break;
                case MBRORDER_STATUS_FILLED:
                    $state = MBROC_STATE_FILLED;
                    break;
                case MBRORDER_STATUS_CANCELLED:
                    $state = MBROC_STATE_CANCELLED;
                    break;
                default:    // shouldn't happen
                    $this->DoStart();
                    $state = MBROC_STATE_FORM;
                    break;
            }
        }

        /* Draw the appropriate Checkout page
         */
        $s .= MbrOrderStyle();

        $s .= "<script>SEEDUI_BoxExpandInit( '{$this->lang}', \"".W_CORE_URL."\");</script>";

        $s .= $this->oTmpl->ExpandTmpl( 'mbroStyle', array() );

        switch( $state ) {
            case MBROC_STATE_FORM:
                $s .= $this->FormDraw();          // show the form with current values
                break;
            case MBROC_STATE_VALIDATE:
                $s .= $this->ValidateDraw();      // parms are valid; show ticket and allow user to confirm, cancel or go back to form
                break;
            case MBROC_STATE_CONFIRMED:     // parms are valid, user has committed
                $s .= $this->ConfirmedDraw();
                break;
            case MBROC_STATE_PAID:
                $s .= $this->PaidDraw();
                break;
            case MBROC_STATE_FILLED:
                $s .= $this->FilledDraw();
                break;
            case MBROC_STATE_CANCELLED;
                $s .= $this->CancelledDraw();
                break;
        }

        return( $s );
    }

    function stateTransButton( $newState, $sL )
    {
        $sFormAction = $this->getFormAction();

        return( "<FORM action='$sFormAction' method='post'>"
               .$this->sess->FormHidden()
               ."<INPUT type='hidden' name='mbrocst' value='$newState'/>"
               ."<INPUT type='submit' value='".$this->oL->S($sL)."'>"
               ."</FORM>" );
    }

    function getFormAction()
    {
        // Get the action for a form to submit to this page.
        // In drupal 7 it is the base path with q= the page name.
        // In drupal 8 it is obtained from drupal.
        // Else it is PHP_SELF
        if( function_exists('drupal_get_path_alias') ) {
            // drupal 7
            $sFormAction = $_SERVER['PHP_SELF'].(($q = SEEDSafeGPC_GetStrPlain('q')) ? "?q=$q" : "");
        } else {
            $sFormAction = Site_path_self();
        }
        return( $sFormAction );
    }

    function FormDraw()
    /******************
        Show values from session, post to http using kfu
     */
    {
//$this->sess->LogoutSession();
        $s = "";

        /* Kluge: We use KFUIForm to draw the form and marshal input parms (with kfu prefix), but it uses a kfr to draw the form while we're keeping the data in session.
         * So copy the sess to a dummy kfr.
         * Note that the order form will have parms that don't exist in the kfrelOrder, so this is going to be a problem if KFRecord validates keys, and we can't use
         * the kfrel to enumerate the fields.
         */
        $kfr = $this->oMbrOrder->kfrelOrder->CreateRecord();
// implementation will not work if SEEDSession stores variables and namespaces differently. Maybe want to encapsulate this in SEEDSessionVarAccessor.
        if( isset($_SESSION['mbrocdata']) ) {
            foreach( $_SESSION['mbrocdata'] as $k => $v ) {
                $kfr->SetValue( $k, $v );
            }
        }

        $uid = 0;
        if( defined("MbrOrderCheckoutOffice") ) {
            $uid = SEEDInput_Str( 'mbro_office_mbrid' );    // can be a kMbr or an email

            // defeat the check below so the address form is always populated
            // (because office people might want to search for another person without completing the order)
            $kfr->SetValue( 'mail_firstname', '' );
            $kfr->SetValue( 'mail_lastname',  '' );
            $kfr->SetValue( 'mail_company',   '' );
            $kfr->SetValue( 'mail_address',   '' );

        } else if( $this->sess->IsLogin() ) {
            // The user is logged in, and $sess is a SEEDSessionAuth.
            // That doesn't necessarily mean they're a member in mbr_contacts.
            $uid = $this->sess->GetUID();
        }

        if( $uid ) {
            include_once( SEEDLIB."mbr/QServerMbr.php" );
            
            // Fetch user information via a non-permissioned cmd; either the user is logged and uid is their GetUID(), or this is an office application.
            $o = new QServerMbr( $this->oApp, ['config_bUTF8'=>false] );
            $rQ = $o->Cmd('mbr!getOffice',is_numeric($uid) ? ['kMbr'=>$uid] : ['sEmail'=>$uid]);

            if( $rQ['bOk'] && ($u = $rQ['raOut']) && $u['_key']) {
                // the login uid has a record in mbr_contacts
                $kfr->SetValue('mbrid',          $u['_key'] );
                $kfr->SetValue('mbrexpires',     @$u['expires'] );

                // if the form is basically empty, fill it with info from mbr_contacts
                if( $kfr->IsEmpty('mail_firstname') &&
                    $kfr->IsEmpty('mail_lastname') &&
                    $kfr->IsEmpty('mail_company') &&
                    $kfr->IsEmpty('mail_address') )
                {

                    if( @$u['province'] ) {
                        // try to match to the SelectProvince codes
                        if( strtolower(@$u['country']) == 'canada' )  $u['province'] .= '1';
                        else                                          $u['province'] .= '2';
                    }

                    foreach(['firstname', 'lastname', 'company', 'city', 'postcode','phone', 'email'] as $k ) {
                        $kfr->SetValue("mail_$k", @$u[$k] );
                    }
                    // non-matching keys
                    $kfr->SetValue('mail_addr',      @$u['address'] );
                    $kfr->SetValue('mail_prov',      @$u['province'] );
                    $kfr->SetValue('mail_postcode',  @$u['postcode'] );
                }
            }
        }

        $this->oKForm->SetKFR( $kfr );

        if( defined("MbrOrderCheckoutOffice") ) {
            $sLogin = "<div class='mbro_box' style='width:38%;float:right'>"
                 ."<div class='mbro_boxbody'>"
                     ."<form action='' method='post'>"
                     ."<h3>Office Orders</h3>Member number or email address<br/><input type='text' name='mbro_office_mbrid' value='".$this->oKForm->Value('mbrid')."'/>"
                     ." <input type='submit' value='Search'/>"
                     ."</form>"
                 ."</div>"
                 ."</div>";
        } else {
            /* Login form
             */
            $raTmpl = array( 'bLogin' => $this->sess->IsLogin(),
                             'sessionNameUID' => $this->sess->GetHTTPNameUID(),
                             'sessionNamePWD' => $this->sess->GetHTTPNamePWD(),
                             'mbrid'  => $this->oKForm->Value('mbrid'),    // you can be logged in but not a member so this can be zero
                             'mbrname' => $this->sess->GetName()
            );
            $sLogin = $this->oTmpl->ExpandTmpl( 'page1LoginBox', $raTmpl );
        }

        /* Draw the Form
         */

    $sFormAction = $this->getFormAction();

    /* The Login form and the Order form are separate <form>s so you can't nest them.
     * Maybe they can be a single form and the login happens but the state doesn't advance to VALIDATE ?
     *
     * Instead, this draws the Login form first and the Order form second.
     */
    $s .= "<div class='container-fluid'>"
         ."<div class='row'>"
             ."<div class='col-sm-7'>&nbsp;</div><div class='col-sm-5'>$sLogin</div>"
         ."</div>"

         // this <form> surrounds the order form and contact form but !not! the login form above
         ."<form id='mbrocForm1' action='$sFormAction' method='post' accept-charset='ISO-8859-1' onsubmit='document.charset=\"iso-8859-1\"'>"
         //$this->sess->FormHidden();
         ."<div class='row'>"
             ."<div class='col-sm-7' id='mbrocForm1col_order'>"
                 .$this->FormDrawOrderCol()."<br/><br/>".$this->_formDrawBottomPart()
             ."</div>"
             ."<div class='col-sm-5' id='mbrocForm1col_contactinfo'>"
                 .$this->_formDrawContactCol()
             ."</div>"
         ."</div>"

         // Put the Next button at the bottom, still within the Order form
        ."<div style='margin-top:20px'>"
            ."<input type='hidden' name='mbrocst' value='".MBROC_ST_VALIDATE."'/>"
            ."<input type='submit' value='".$this->oL->S('next_button')."'>"
        ."</div>"

        ."</form>"
        ."</div>";  // container-fluid

/*
        $s .= "<TABLE border='0' cellspacing='0' cellpadding='0' width='100%' style='clear:right'><TR valign='top'>"
            ."<TD id='mbrocForm1col_order'><DIV>". $this->FormDrawOrderCol() ."</DIV><BR/><BR/>"
            .$this->_formDrawBottomPart()."<BR/><BR/>"
            ."<INPUT type='hidden' name='mbrocst' value='".MBROC_ST_VALIDATE."'/>"
            ."<INPUT type='submit' value='".$this->oL->S('next_button')."'>"
            ."</TD>"
            ."<TD style='width:40%;'><div id='mbrocForm1col_contactinfo' style='clear:right'>". $this->_formDrawContactCol() ."</div></TD>"
            ."</TR></TABLE>"
*/

        return( $s );
    }

    function FormDrawOrderCol()
    /**************************
        Override this method to draw the order form column.
        Use $this->oKForm to draw form elements - it already contains all posted fields in its kfr (even though they might not be in the kfrel)
     */
    {
        return( "ORDER FORM" );
    }

    protected function FormBox( $heading, $body, $bExpandable = false )
    {
        $cBox  = $bExpandable ? 'seedui_boxexpand' : "";
        $cHead = $bExpandable ? 'seedui_boxexpand_head' : "";
        $cBody = $bExpandable ? 'seedui_boxexpand_body' : "";

        $s = "<div class='mbro_box $cBox'>"
                ."<div class='mbro_boxheader $cHead'>$heading</div>"
                 ."<div class='mbro_boxbody $cBody'>$body</div>"
             ."</div>";

        return( $s );
    }

    function _formDrawBottomPart()
    /*****************************
        The standard stuff at the bottom of the order form
     */
    {
        $s = $this->oL->S('form_end_info')."<br/>";

        $s .= $this->FormBox(
                $this->oL->S('Method of Payment'),
                "<p style='font-weight:bold'>".$this->oL->S('Select a method of payment')."</p>"
               .$this->oKForm->Radio('ePayType', "",'PayPal').$this->oL->S('credit_card')
               ."<div class='mbro_help' style='padding-left:50px;'>".$this->oL->S('credit_card_desc')."</div>"
               .$this->oKForm->Radio('ePayType', "",'Cheque').$this->oL->S('cheque_mo')
               ."<div class='mbro_help' style='padding-left:50px;'>".$this->oL->S('cheque_desc')."</div>",
                false );

        $s .= $this->FormBox(
                $this->oL->S('mail_note'),
                $this->oKForm->TextArea( 'notes', "", 0, 5, ['width'=>"100%"] ),
                false );

        return( $s );
    }

    private function mbr1_mail_line( $name, &$o, $size = 30 )
    {
        //return( $o->oKForm->TextTD($name, $o->oL->S($name), array("size"=>$size) ) );
        return( $o->oL->S($name)."<br/>".$o->oKForm->Text( $name, "", array('size'=>$size))."<br/>" );
    }

    function _formDrawContactCol()
    {
        $s = "";


        $mL = $this->oL;

        /***** mbrocForm1col_contactinfo
         */
        if( count($this->raFormErrors) ) {
            $s .= "<p style='color:red; font-weight:bold;'>";
            foreach( $this->raFormErrors as $sErr ) {
                $s .= $sErr."<br/><br/>";
            }
            $s .= "</p>";
        }


        $s .= "<div class='mbro_box'>"
             ."<div class='mbro_boxheader'>".$mL->S('your_address')."</div>"
             ."<div class='mbro_boxbody'>"
             ."<div class='mbro_ctrl'>";

        $s .= $this->oKForm->Hidden( "mbrid" );

        if( $this->oKForm->Value('mbrid') ) {
            $s .= "<p>Membership #: ".$this->oKForm->Value('mbrid')."</p>";
        }
        if( $this->oKForm->Value('mbrexpires') ) {
            $s .= "<p>Membership expiry: ".$this->oKForm->Value('mbrexpires')."</p>";
        }
        
        $s .= $this->mbr1_mail_line( "mail_firstname", $this )
             .$this->mbr1_mail_line( "mail_lastname",  $this )
             .$this->mbr1_mail_line( "mail_company",   $this )
             .$this->mbr1_mail_line( "mail_addr",      $this )
             .$this->mbr1_mail_line( "mail_city",      $this )

             .$mL->S('mail_prov')."<br/>";
        $sfProv = $this->oKForm->oFormParms->sfParmField("mail_prov");
        $s .= SelectProvinceOrState( "mbrocForm1", $sfProv, $this->lang, $this->oKForm->oDS->Value("mail_prov") ); //(!$kfr->IsEmpty('mail_prov') ? ($kfr->value('mail_prov').($kfr->value('mail_country')=='Canada' ? 1 : 2)) : "" ) );

        $s .= $this->mbr1_mail_line( "mail_postcode", $this )
             .$this->mbr1_mail_line( "mail_phone",    $this )
             .$this->mbr1_mail_line( "mail_email",    $this )

             ."<P>".$mL->S('mail_where')."<BR>".$this->oKForm->Text('mail_where',"",array("size"=>30))."</P>"
             ."</div>"           // mbro_ctrl

             ."<P>".$mL->S('ebull_desc')."</P>"

             ."<div class='mbro_ctrl'>"
             .$this->oKForm->Radio('mail_eBull',"",1)."&nbsp;".$mL->S('send_ebull')."<BR/>"
             .$this->oKForm->Radio('mail_eBull',"",0)."&nbsp;".$mL->S('no_thanks')
             ."</div>"           // mbro_ctrl
             ."<br/></div></div>"     // mbro_boxbody, mbro_box
             ."<br/>"

             ."<DIV class='mbro_help'>".$mL->S('overseas_instructions')."</DIV>"
             ."<DIV class='mbro_help' style='color:green'>".$mL->S('privacy_policy')."</DIV>";

        return( $s );
    }


    function ValidateParms()
    /***********************
        Capture http parms into session, return true if parms are good enough to commit to Confirmed.
        If not, store an error message.
        Also create $this->kfrOC if parms validate - this is a complete kfr ready to be drawn on the ticket
        and/or committed to the db.
     */
    {
        $this->raFormErrors = array();
        $this->kfrOC = NULL;

        $oSVar = new SEEDSessionVarAccessor( $this->sess, "mbrocdata" );

        /* Capture http parms into session
         */
        $raKFUParms = $this->oKForm->oFormParms->Deserialize( $_REQUEST, true );
        if( isset($raKFUParms['rows'][0] ) ) {		// there are no http parms at the Confirm step (they should all be in the session)
            foreach( $raKFUParms['rows'][0]['values'] as $k => $v ) {
                $oSVar->VarSet( $k, $v );
            }
        }

//var_dump($_SESSION);echo "<BR/><BR/>";

        /* Evaluate session parms, ensure that they are complete and make sense
         */
        $bValid = true;
        // REQUIRE (firstname | lastname | company)
        // REQUIRE (addr & city & prov & postcode & country)

        if( $oSVar->VarEmpty('mail_firstname') && $oSVar->VarEmpty('mail_lastname') && $oSVar->VarEmpty('mail_company') ) {
            $this->raFormErrors[] = $this->oL->S('name_or_company_needed');
            $bValid = false;
        }
        if( $oSVar->VarEmpty('mail_addr')     || $oSVar->VarEmpty('mail_city') ||
            $oSVar->VarEmpty('mail_postcode') || strlen($oSVar->VarGet('mail_prov')) != 3 )
        {
            $this->raFormErrors[] = $this->oL->S('address_needed');
            $bValid = false;
        }
        // Derived class checks validity of order parms
        $v1 = $this->ValidateParmsOrderValid( $oSVar );
        $bValid = $bValid && $v1;

        if( $bValid ) {
            /* Normalize session parms into mbrOrder.kfr
             * This method is used in two cases:
             *      Validate : use the kfr to display the ticket
             *      Confirm  : use the kfr to store the db record, and display the ticket
             */
            $this->kfrOC = $this->oMbrOrder->CreateBlankKFR();
            $this->kfrOC->SetValue( 'eStatus', MBRORDER_STATUS_NEW );

            foreach( array("mail_firstname", "mail_lastname",
                           "mail_company",   "mail_addr",
                           "mail_city",      "mail_postcode",
                           "mail_phone",     "mail_email",
                           "mail_where",     "mail_eBull",
                           "notes" ) as $k ) {
                $this->kfrOC->SetValue( $k, $oSVar->VarGet($k) );
            }

            // mail_prov should have form ON1
            $this->kfrOC->SetValue("mail_prov",    substr( $oSVar->VarGet("mail_prov"), 0, 2 ) );
            $this->kfrOC->SetValue("mail_country", substr( $oSVar->VarGet("mail_prov"), 2, 1 ) == '2' ? "USA" : "Canada" );

            $this->kfrOC->SetValue("mail_lang", ($this->oL->GetLang() == "FR") ? 1 : 0 );  // 0:EN, 1:FR

            if( !$oSVar->VarEmpty('fMisc') ) {
                if( floatval($oSVar->VarGet('fMisc')) < 0 )  $oSVar->VarSet( 'fMisc', 0 );  // don't let them type negative numbers in misc
                $this->kfrOC->SetValue( "sExtra", SEEDStd_ParmsURLAdd( $this->kfrOC->Value("sExtra"),
                                                                       "fMisc", $oSVar->VarGet("fMisc") ) );
            }
            if( !$oSVar->VarEmpty('mbrid') ) {
                $this->kfrOC->SetValue( "sExtra", SEEDStd_ParmsURLAdd( $this->kfrOC->Value("sExtra"),
                                                                       "mbrid", $oSVar->VarGet("mbrid") ) );
            }

            $this->kfrOC->SmartValue( 'ePayType', array( 'PayPal', 'Cheque' ), $oSVar->VarGet('ePayType') );

            // derived class stores order parms in kfr
            $this->ValidateParmsOrderMakeKFR( $oSVar );
//var_dump($this->kfrOC->_values );

        }
        return( $bValid );
    }

    function ValidateParmsOrderValid( $oSessionVarAccessor )
    /* Override this to report validity of order parms
     */
    {
        return( true );
    }

    function ValidateParmsOrderMakeKFR( $oSessionVarAccessor )
    /* Override this to normalize order parms and store in kfr
     */
    {
        return( true );
    }

    function ValidateDraw()
    {
        $s = "<H2>".$this->oL->S('confirm_order')."</H2>"
            .$this->oMbrOrder->DrawTicket( 0, $this->kfrOC )
            ."<br/>"
            ."<TABLE border='0'><TR>"
            ."<TD valign='top' style='font-size:9pt;'>".$this->oL->S('if_order_not_correct')."<BR/><BR/>"
            .$this->stateTransButton( MBROC_ST_FORM, 'change_button' )
            ."</TD>"
            ."<TD valign='top' style='font-size:9pt;padding-left:120px'>".$this->oL->S('confirm_order')."<BR/><BR/>"
            .$this->stateTransButton( MBROC_ST_CONFIRM, 'confirm_button' )
            ."</TD></TR></TABLE>";
        return( $s );
    }

    function ConfirmedDraw()
    {
    	/* This screen has a special UI that switches the ePayType without changing any other state.
    	 * This http parm is not used anywhere else
    	 */
        $s = "";

        $ePayType = SEEDSafeGPC_Smart( 'ePayType', array("","PayPal","Cheque") );
        if( !empty($ePayType) ) {
            $this->oMbrOrder->kfr->SetValue( 'ePayType', $ePayType );
            $this->oMbrOrder->kfr->PutDBRow();
        }

        $s .= "<H2>".$this->oL->S('Order_confirmed')." - "
            .($this->oMbrOrder->kfr->value('ePayType') == 'PayPal' ? $this->oL->S('Pay_by_credit') : $this->oL->S('Pay_by_cheque_mo'))."</H2>";

        $sFormAction = $this->getFormAction();

        if( $this->oMbrOrder->kfr->value('ePayType') == 'PayPal' ) {
            $s .= "<table border='0'><tr><td style='padding:10px'>"
                .$this->confirmedDrawPayPalButton()
                ."</td>"
                ."<td>".$this->oL->S('paypal_instructions1')."</td>"
                ."</tr></table>"
                .$this->oL->S('paypal_instructions2')
                ."<FORM action='$sFormAction' method='post'>"
                .$this->sess->FormHidden()
                ."<INPUT type='hidden' name='ePayType' value='Cheque'>"
                ."<INPUT type='submit' value='".$this->oL->S('pay_by_cheque_instead')."'>"
                ."</FORM>";

        } else {
            $s .= $this->oL->S('cheque_instructions')
                ."<FORM action='$sFormAction' method='post'>"
                .$this->sess->FormHidden()
                ."<INPUT type='hidden' name='ePayType' value='PayPal'>"
                ."<INPUT type='submit' value='".$this->oL->S('pay_by_credit_card_instead')."'>"
                ."</FORM>";
        }
        $sTicket = $this->oMbrOrder->DrawTicket();     // oMbrOrder->kfr is already loaded with the current order
        $s .= $sTicket;

        // If Paypal, they have to go through the payment now. If Cheque, let them start a new order.
        // Office checkout is always Cheque.
        if( $this->oMbrOrder->kfr->value('ePayType') == 'Cheque' ) {
            $s .= "<BR/><P>".$this->stateTransButton( MBROC_ST_START, 'Start_a_New_Order' )."</P>";
        }
        $s .= "<BR/><P>".$this->stateTransButton( MBROC_ST_CANCEL, 'Cancel_this_order' )."</P>";

        if( !defined("MbrOrderCheckoutOffice") ) {
            MailFromOffice( 'bob@seeds.ca', 'Order Ticket', "", $sTicket, $raParms = array() );
        }

        return( $s );
    }

    function PaidDraw()
    {
    	$s = "<H2>".$this->oL->S('Order_paid')." - ".$this->oL->S('Thank you')."!</H2>"
    	    .$this->oL->S('assistance')
            .$this->oMbrOrder->DrawTicket()
            ."<BR/><P>".$this->stateTransButton( MBROC_ST_START, 'Start_a_New_Order' )."</P>";

        return( $s );
    }

    function FilledDraw()
    {
    	$s = "<H2>".$this->oL->S('Order_filled')." - ".$this->oL->S('Thank you')."!</H2>"
    	    .$this->oL->S('assistance')
            .$this->oMbrOrder->DrawTicket()
            ."<BR/><P>".$this->stateTransButton( MBROC_ST_START, 'Start_a_New_Order' )."</P>";
        return( $s );
    }

    function CancelledDraw()
    {
    	$s = "<H2>".$this->oL->S('Order_cancelled')."</H2>"
    	    .$this->oL->S('assistance')
            .$this->oMbrOrder->DrawTicket()
            ."<BR/><P>".$this->stateTransButton( MBROC_ST_START, 'Start_a_New_Order' )."</P>";
        return( $s );
    }

//https://www.paypal.com/cgi-bin/webscr?cmd=p/pdn/howto_checkout-outside
    function confirmedDrawPayPalButton( $raVars = array() )
    {
		$raPP = array();

        /* SYSTEM
         */
		$raPP['cmd']                   = '_xclick';                  // Buy Now button  - see value='_donations' for donation button
		$raPP['business']              = 'mail@seeds.ca';
        $raPP['quantity']              = 1;                          // multiplies the 'amount' to make a bulk payment!  Must be 1.
        $raPP['no_shipping']           = 1;                          // the buyer is not prompted for a shipping address (in addition to billing address)
        $raPP['no_note']               = 1;                          // the buyer is not prompted to enter a note
//TODO: explicitly set charset to iso, instead of relying on their default
        //$raPP['charset']               = 'utf-8';                  // PayPal "sends data to you" in the charset specified here. It also seems to expect data to be sent to it in this charset.
                                                                     // We used to set this to UTF-8, but accented characters in our posted parms (like Adh�sion in item_name) caused immediate PayPal fatal error about character encoding.
        /* TRANSACTION
         */
        $raPP['item_name']             = $this->oMbrOrder->tinySummary();
        $raPP['amount']                = $this->oMbrOrder->kfr->value('pay_total');
        $raPP['currency_code']         = ($this->oMbrOrder->kfr->value('mail_country')=='Canada'? "CAD" : "USD");
        $raPP['item_number']           = $this->oMbrOrder->kfr->Key();    // Passthrough variables: PayPal doesn't use these, but transmits them to IPN.
        $raPP['invoice']               = $this->oMbrOrder->kfr->Key();    // Though PayPal docs say that they are neither used nor recorded by PayPal, we've seen PayPal complain when an invoice number duplicates an earlier one.
     // $raPP['custom']                = merchant_custom_value

        /* PAGES - these can also be set as an option in the PayPal account, but we want to propagate parms and use different checkoutApp urls
         */
        $raPP['notify_url']            = 'https://www.seeds.ca/l/mbr/mbr_PPipn.php'; // Instant Payment Notification
// TODO: assemble the full url for PHP_SELF with the PHPSESSID propagated.  cancel_return should somehow cause mbrocst=MBROC_ST_CANCEL
        $raPP['return']                = $this->oL->GetLang() == 'EN' ? "https://www.seeds.ca/store" : "https://www.semences.ca/boutique";
        $raPP['cancel_return']         = $this->oL->GetLang() == 'EN' ? "https://www.seeds.ca/store" : "https://www.semences.ca/boutique";
        $raPP['rm']                    = 2;                          // Return Method: how parms sent to return page: 1:GET, 2:POST
        $raPP['cbt']                   = $this->oL->S('Return to SoD button'); // The text on the Continue button on completion

        /* DECORATION
         */
     // $raPP['image_url']             = 'http://www.seeds.ca/img/logo/logo02_EN_150.png';  // logo image max 150x50
     // $raPP['cpp_header_image']      = 'http://www.seeds.ca/img/logo/logo02_EN_750.png';  // header image max 750x90
     // $raPP['cpp_headerback_color']  = 'FFFFFF';                                          // header background colour
     // $raPP['cpp_headerborder_color'] = '555555';                                         // header border colour
     // $raPP['cpp_payflow_color']     = 'AAAAAA';                                          // page background colour below header

        /* PREPOPULATE
         */
        $raPP['first_name']            = $this->oMbrOrder->kfr->value('mail_firstname');
        $raPP['last_name']             = $this->oMbrOrder->kfr->value('mail_lastname');
        $raPP['address1']              = $this->oMbrOrder->kfr->value('mail_addr');
        $raPP['city']                  = $this->oMbrOrder->kfr->value('mail_city');
        $raPP['state']                 = $this->oMbrOrder->kfr->value('mail_prov');
        $raPP['zip']                   = $this->oMbrOrder->kfr->value('mail_postcode');
        $raPP['country']               = ($this->oMbrOrder->kfr->value('mail_country')=='Canada' ? "CA" : "US");

        if( $this->oL->GetLang() == 'FR' )  $raPP["lc"] = "FR";       // language code (assume that English default is reliable)


/*
    <input type="hidden" name="tax" value="0">
    <input type="hidden" name="shipping" value="5.00">
    <input type="hidden" name="lc" value="US">
    <input type="hidden" name="bn" value="PP-DonationsBF">
    <input type="image" src="https://www.paypal.com/en_US/i/btn/x-click-but04.gif" border="0" name="submit" alt="Make payments with PayPal - it's fast, free and secure!">
    <img alt="" border="0" src="https://www.paypal.com/de_DE/i/scr/pixel.gif" width="1" height="1">
*/

        foreach( $raVars as $k => $v ) {
            $raPP[$k] = $v;
        }
        $s = "\n<FORM action='https://www.paypal.com/cgi-bin/webscr' method='post'>"
            ."\n<INPUT type='image' src='https://www.paypal.com/en_US/i/btn/x-click-but6.gif' name='submit' alt='".$this->oL->S('secure_payment_paypal')."'>";
        foreach( $raPP as $k => $v ) {
            $s .= "\n<INPUT type='hidden' name='$k' value='".SEEDStd_HSC($v)."'/>";
        }
        $s .= "\n</FORM>";

        return( $s );
    }



    function _setLocalText( $lang )
    /* Do this in a function because some strings are not constants - class var default values have to be constants
     */
    {
        $sL = array(
            "next_button"
                => array( "EN" => "Next >>",
                          "FR" => "Continuer >>" ),
            "change_button"
                => array( "EN" => "<< Change",
                          "FR" => "<< Changer" ),
            "confirm_button"
                => array( "EN" => "Confirm >>",
                          "FR" => "Confirmer >>" ),

            "Click to show"
                => array( "EN" => "Click to show",
                          "FR" => "Cliquez" ),



            /*** _formDrawContactCol ***/
            "Login"
                => array( "EN" => "Login (Optional)",
                          "FR" => "Connexion (Optionnel)" ),

            "your_address"
                => array( "EN" => "Your Address",
                          "FR" => "Votre adresse" ),

            "mail_firstname"
                => array( "EN" => "First Name",
                          "FR" => "Pr&eacute;nom" ),

            "mail_lastname"
                => array( "EN" => "Last Name",
                          "FR" => "Nom" ),

            "mail_company"
                => array( "EN" => "Organization or company",   // (if applicable)"
                          "FR" => "Organisme ou compagnie" ),  // (si applicable)"

            "mail_addr"
                => array( "EN" => "Address",
                          "FR" => "Adresse" ),

            "mail_city"
                => array( "EN" => "City or Town",
                          "FR" => "Ville" ),

            "mail_prov"
                => array( "EN" => "Province/State",
                          "FR" => "Province/&Eacute;tat" ),

            "mail_postcode"
                => array( "EN" => "Postal Code / Zip",
                          "FR" => "Code postal" ),

            "mail_phone"
                => array( "EN" => "Telephone (with area code)",
                          "FR" => "T&eacute;l&eacute;phone" ),

            "mail_email"
                => array( "EN" => "Email",
                          "FR" => "Courriel" ),

            "mail_where"
                => array( "EN" => "Where did you learn about <nobr>Seeds of Diversity?</nobr>",
                          "FR" => "Comment avez-vous appris l'existence de notre programme?" ),

            "send_ebull"
                => array( "EN" => "Please send Seeds of Diversity's e-Bulletin to the email address above",
/* ?? */                  "FR" => "Ajoutez-moi &agrave; la liste du e-Bulletin de Semences du patrimoine" ),

            "no_thanks"
                => array( "EN" => "No thankyou",
        /* ?? */          "FR" => "Non" ),

            "ebull_desc"
                => array( "EN" => "Seeds of Diversity's e-Bulletin is a free monthly email newsletter about seeds,"
                                 ." biodiversity and our horticultural conservation projects.",
        /* ?? */          "FR" => "L'e-Bulletin de Semences du patrimoine est un communiqu&eacute; par courriel au sujet des semences,"
                                 ." la biodiversit&eacute; et nos projets horticoles de conservation." ),

            "overseas_instructions"
                => array( "EN" => "Overseas requests (outside Canada and U.S.) please contact our office for details on shipping, ".
                                  "pricing and currency.<BR>Phone 226-600-7782 or email ".SEEDCore_EmailAddress("office","seeds.ca"),
/* ?? */                  "FR" => "Outre-mer (ailleurs qu'au Canada ou aux E.-U.), contactez-nous pour l'information d'adh&eacute;sion<BR>".
                                  "T&eacute;l&eacute;phonez 226-600-7782 ou envoyez un courriel &agrave; ".SEEDCore_EmailAddress("courriel","semences.ca") ),

            "privacy_policy"
                => array( "EN" => "<B>Privacy Policy:</B>  Seeds of Diversity never sells or exchanges membership information"
                                 ." with any other organization or company.  Our members' personal contact information is always"
                                 ." kept strictly confidential.",
        /* ?? */          "FR" => "Toutes les informations relatives aux membres sont trait&eacute;es de mani&egrave;re confidentielle."
                                 ." Nous ne procurons jamais, ni ne vendons ou &eacute;changeons l'information relative &agrave; nos membres"
                                 ." &agrave; d'autres organismes, compagnies ou individus." ),


        /*** _formDrawBottomPart ***/

            "form_end_info"
                => array( "EN" => "<P>All prices include postage and handling, unless indicated otherwise, and all applicable taxes.</P>".
                                  "<P>Any questions? Please call 226-600-7782 or email ".SEEDCore_EmailAddress("office","seeds.ca")."</P>",
                          "FR" => "<P>Les prix incluent les frais postaux, la manutention et les taxes en vigueur.</P>".
                                  "<P>Questions?  T&eacute;l&eacute;phonez 226-600-7782 ou envoyez un courriel &agrave; ".SEEDCore_EmailAddress("courriel","semences.ca")."</P>" ),

            "Misc Payment"
                => array( "EN" => "Other Payments",
                          "FR" => "Paiement divers"),

            "Misc_payment_instructions"
                => array( "EN" => "For payments not itemized on this form, please enter the amount here and "
                                 ."provide a detailed explanation in the Notes section below.<BR/><BR/>&nbsp;&nbsp;&nbsp;Amount: $ ",
                          "FR" => "Pour les paiements qui n'apparaissent pas sur ce formulaire, veuillez s'il vous pla&icirc;t "
                                 ."entrer le montant ici et fournir une explication d&eacute;tail&eacute;e dans la section &#0171; Remarque &#0187; ci-dessous.<BR/><BR/>Montant: $ " ),

            "Method of Payment"
                => array( "EN" => "Method of Payment",
                          "FR" => "Modalit&eacute; de Paiement" ),

            "Select a method of payment"
                => array( "EN" => "Please select a method of payment",
                          "FR" => "Choisissez un modalit&eacute; de paiement" ),
            "credit_card"
                => array( "EN" => "Credit Card",
                          "FR" => "Carte de cr&eacute;dit" ),

            "cheque_mo"
                => array( "EN" => "Cheque / Money Order",
                          "FR" => "Ch&eacute;que / Mandat postal" ),

            "credit_card_desc"
                => array( "EN" => "Use our secure credit card service for safe payment.  Your order will be processed ".
                                  "within five business days.  Please allow 2-3 weeks for delivery.",
                          "FR" => "Employez notre service de carte de cr&eacute;dit pour paiement s&ucirc;re. ".
                                  "Votre ordre sera trait&eacute; dans cinq jours d'affaires. ".
                                  "Veuillez accorder 2 ou 3 semaines pour la livraison." ),

            "cheque_desc"
                => array( "EN" => "Pay by cheque or money order.  Please allow 4-5 weeks for delivery.",
                          "FR" => "Payer par ch&egrave;que ou mandat postal.  Veuillez accorder 4 ou 5 semaines pour la livraison." ),

            "mail_note"
                => array( "EN" => "Send us a Note",
                          "FR" => "Envoyez-nous une remarque" ),


        /*** Validate ***/

            "name_or_company_needed"
                => array( "EN" => "Name or company name is needed",
                          "FR" => "Nom ou compagnie est n&eacute;cessaire" ),

            "address_needed"
                => array( "EN" => "Complete address is needed",
                          "FR" => "Addresse compl&egrave;te est n&eacute;cessaire" ),

            "confirm_order"
                => array( "EN" => "Please Confirm Your Order",
                          "FR" => "Veuillez confirmer votre ordre" ),
            "if_order_not_correct"
                => array( "EN" => "If this order is not correct",
                          "FR" => "Si cet ordre n'est pas correct" ),

        /*** Confirmed + Paid + Filled + Cancelled ***/

            "Thank you"
                => array( "EN" => "Thank you",
                          "FR" => "Merci" ),
            "Order_confirmed"
                => array( "EN" => "Order Confirmed",
                          "FR" => "L'ordre est confirm&eacute;" ),
            "Order_paid"
                => array( "EN" => "Order Paid",
                          "FR" => "L'ordre est pay&eacute;" ),
            "Order_filled"
                => array( "EN" => "Order Filled",
                          "FR" => "L'ordre est complet" ),
            "Order_cancelled"
                => array( "EN" => "Order Cancelled",
                          "FR" => "L'ordre est d&eacute;command&eacute;" ),

            "Pay_by_credit"
                => array( "EN" => "Pay by Credit Card",
                          "FR" => "Payer par carte de cr&eacute;dit" ),
            "Pay_by_cheque_mo"
                => array( "EN" => "Pay by Cheque or Money Order",
                          "FR" => "Payer par ch&egrave;que ou mandat postal" ),
            "assistance"
                => array( "EN" => "<P>If you need assistance, please call 226-600-7782 or email "
                                 .SEEDCore_EmailAddress( "office", "seeds.ca" ).".</P>",
                          "FR" => "<P>Si vous avez besoin d'assistance, t&eacute;l&eacute;phonez 226-600-7782 ou envoyez un courriel &agrave; "
                                 .SEEDCore_EmailAddress("courriel","semences.ca")."</P>" ),
            "cheque_instructions"
                => array( "EN" => "<P>Please print this summary page and mail it with a cheque or money order payable to ".
                                  "<B>Seeds of Diversity Canada</B>.".
                                  "<DIV style='margin:1em'><B>Seeds of Diversity Canada<br/>#1 - 12 Dupont St West<br/>Waterloo ON N2L 2X6</B></DIV></P>".
                                  "<P>Please allow 4 - 5 weeks for delivery</P>",
                          "FR" => "<P>Veuillez faire imprimer ce page et exp&eacute;dier avec un ch&egrave;que ou mandat postal. ".
                                  "Libellez votre ch&egrave;que au nom de <B>Programme semencier du patrimoine Canada</B>.".
                                  "<DIV style='margin:1em'>Programme Semencier du Patrimoine Canada<BR>#1 - 12 Dupont St West<br/>Waterloo ON N2L 2X6</DIV></P>".
                                  "<P>Veuillez accorder 4 ou 5 semaines pour la livraison.</P>" ),
            "paypal_instructions1"
                => array( "EN" => "<p><strong>Click here to pay by credit card</strong><br/>You don't need a PayPal account to pay for your order, just a credit card.</p>",
                                   //."<div style='padding:10px;margin:10px; border:1px solid orange;background-color:#fe9;width:75%;max-width:600px'>As of Dec 20, 2016, PayPal has advised us that it will require all purchasers to create PayPal accounts. We understand that some people simply want to pay with their credit card without needing a PayPal account, and we apologize for this inconvenience as we look for an alternative payment system.<br/><br/>If you have come here specifically to make a <b>donation</b> with your credit card, we gratefully invite you to do so through our secure charity page at <a href='https://www.canadahelps.org/en/charities/seeds-of-diversity-canada-programme-semencier-du-patrimoin/' target='_blank'>CanadaHelps.org</a>.<br/><br/>We can also process membership renewals by phone at (226) 600-7782 (please leave a voice message and we will call you back).<br/><br/>- Your office team at Seeds of Diversity</div>",
/* TODO */                "FR" => "<P>Cliquez ici pour employer notre page s&ucirc;re de PayPal pour le paiement de carte de cr&eacute;dit.</P>" ),
            "paypal_instructions2"
                => array( "EN" => "<P>Your order will be processed within five business days.  Please allow 2-3 weeks for delivery. ".
                                  "We suggest that you print this page for your records.</P>",
                          "FR" => "<P>Votre ordre sera trait&eacute; dans cinq jours d'affaires. ".
                                  "Veuillez accorder 2 ou 3 semaines pour la livraison. ".
                                  "Nous sugg&eacute;rons que vous imprimiez cette page.</P>" ),

            "pay_by_credit_card_instead"
                => array( "EN" => "Pay by Credit Card Instead",
                          "FR" => "Payer par carte de cr&egrave;dit au lieu" ),
            "pay_by_cheque_instead"
                => array( "EN" => "Pay by Cheque Instead",
                          "FR" => "Payer par ch&egrave;que au lieu" ),
            "Start_a_New_Order"
                => array( "EN" => "Start a New Order",
                          "FR" => "Commencer un nouvel ordre" ),

            "secure_payment_paypal"
                => array( "EN" => "Secure payment through PayPal",
                          "FR" => "Paiement s&ucirc;r via PayPal" ),

            "Cancel_this_order"
                => array( "EN" => "Cancel this order",
                          "FR" => "Annuler cet ordre" ),

        /*** PayPal form ***/

            "Return to SoD button"
                => array( "EN" => "Return to Seeds of Diversity",
                          "FR" => "Retour au Semences du patrmoine"),







        );
        $this->oL = new SEEDLocal( $sL, $lang );
    }
}

?>
