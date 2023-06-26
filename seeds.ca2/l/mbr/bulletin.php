<?php

/* Support for e-bulletin content on our web site
 *
 */
include_once( STDINC."SEEDTemplate.php" );
include_once( SEEDCOMMON."mbr/mbrBulletin.php" );    // MbrBulletin
include_once( SITEROOT."/l/mbr/mbrPipe.php" );       // MbrPipeGetContactRA
include_once( SEEDCOMMON."siteutil.php" );           // MailFromOffice
include_once( SEEDLIB."mbr/MbrEbulletin.php" );
include_once( SEEDLIB."mbr/QServerMbr.php" );

class SoDBulletin
{
    const   hashSeed = "Your Seeds of Diversity e-bulletin";
    private $oApp;
    private $kfdb;
    private $lang;
    private $oEbull;    // new object
    private $oBull;     // old object to deprecate
    private $oForm;     // the form that lets people subscribe/unsubscribe their email address
    private $oTmpl;

    function __construct( SEEDAppConsole $oApp, KeyFrameDB $kfdb, $lang )
    {
        $this->oApp = $oApp;
        $this->kfdb = $kfdb;
        $this->lang = $lang;
        $this->oBull = new MbrBulletin( $kfdb );
        $this->oEbull = new MbrEbulletin($oApp);
        $this->oForm = new SEEDCoreForm();
        $this->oTmpl = $this->makeTemplates( $lang );
    }

    function ControlDraw()
    /*********************
        Draw the form that lets people subscribe/unsubscribe their email address
     */
    {
        $s = $this->oTmpl->ExpandTmpl( 'bullControlForm', ['formAction'=>$this->oApp->PathToSelf(), 'emailParmName'=>$this->oForm->Name('email')] );

        return( $s );
    }

    function HandleAction()
    /**********************
        bullCtlCmd = subscribe    : someone entered an email address on the control form and clicked subscribe
        bullCtlCmd = unsubscribe  : someone entered an email address on the control form and clicked unsubscribe

        subscribe   = {email}, id = {hash}  : someone clicked on a subscribe confirmation
        unsubscribe = {email}, id = {hash}  : someone clicked on an unsubscribe confirmation
     */
    {
        $s = "";

        if( ($cmd = SEEDInput_Str('bullCtlCmd')) ) {
            /* The user clicked Subscribe or Unsubscribe on the control form.
             * Maybe we tell them they're already subscribed, not subscribed, etc.
             * Maybe we send them an email to confirm their action.
             */
            list($s,$alertType) = $this->handleCtlCmd( $cmd );    // cmd is the label on the button, English or French

        } else if( isset($_REQUEST['subscribe']) || isset($_REQUEST['unsubscribe']) ) {
            /* Someone clicked on a link in a confirmation email.
             *
             * https://seeds.ca/ebulletin?subscribe=me@email.com&id={hash}
             * https://seeds.ca/ebulletin?unsubscribe=me@email.com&id={hash}
             *
             * Validate the link, fulfill the action, and tell them the result.
             */
            if( ($email = SEEDInput_Str('subscribe')) ) {
                $bSubscribe = true;
            } else if( ($email = SEEDInput_Str('unsubscribe')) ) {
                $bSubscribe = false;
            } else {
                goto done;
            }
            $md5 = SEEDInput_Str('id');
            list($s,$alertType) = $this->handleConfirm( $email, $md5, $bSubscribe );

        }
        if( $s )  $s = "<div class='alert alert-$alertType'>$s</div>";

        done:
        return( $s );
    }

    private function handleCtlCmd( $cmd )
    /************************************
        Someone clicked Subscribe or Unsubscribe (or French equivalents) on the control form.
        See if they typed an email address in the form.
        Decide whether the command makes sense (is that email address in our records).
        Send confirmations if appropriate.
        Return a string to explain to the user what we did.
     */
    {
        $sAlert = "";
        $alertType = "success";

        /* Get the email address from the form.
         * Could check if $e is a valid email address, but this is assumed to be handled by <input type='email> on modern browsers
         */
        if( !$cmd || !$this->oForm->Load() || !($e = $this->oForm->Value('email')) ) {
            goto done;
        }

        $raBull = $this->getBullStatus( $e );

        $raTmplVars = ['email'=>$e];

        switch( $cmd ) {
            case 'Subscribe':
            case 'French for Subscribe':
                if( $raBull['bIsSubscriber'] ) {
                    $sAlert = $this->oTmpl->ExpandTmpl( 'subscribe-youAreAlreadySubscribed', $raTmplVars );
                } else {
                    // Either the email is not subscribed anywhere, or they are a member with bNoEBull. Send confirmation either way.
                    if( $this->SendConfirm( $e, 'subscribe' ) ) {
                        $sAlert = $this->oTmpl->ExpandTmpl( 'subscribe-confirmationSent', $raTmplVars );
                    }
                }
                break;

            case 'Unsubscribe':
            case 'French for Unsubscribe';
                if( $raBull['bIsSubscriber'] ) {
                    if( $this->SendConfirm( $e, 'unsubscribe' ) ) {
                        $sAlert = $this->oTmpl->ExpandTmpl( 'unsubscribe-confirmationSent', $raTmplVars );
                    }
                } else if( $raBull['bIsMember'] ) {
                    // The email is a member but bNoEBull.
                    $sAlert = $this->oTmpl->ExpandTmpl( 'unsubscribe-butYouAreNoEbull', $raTmplVars );
                } else {
                    $sAlert = $this->oTmpl->ExpandTmpl( 'unsubscribe-youAreNotSubscribed', $raTmplVars );
                    $alertType = 'warning';
                }
                break;
        }

        // seems weird for the form to show this after an action
        $this->oForm->SetValue( 'email', "" );

        done:
        return( [$sAlert, $alertType] );
    }

    private function handleConfirm( $email, $md5, $bSubscribe )
    /**********************************************************
        Someone clicked on a confirmation email.
     */
    {
        $sAlert = "";
        $alertType = "success";

        if( md5($email.self::hashSeed) != $md5 ) {
            $sAlert = $this->oTmpl->ExpandTmpl( 'confirm-badMD5', ['bSubscribe'=>$bSubscribe] );
            $alertType = 'danger';
            goto done;
        }

        $raBull = $this->getBullStatus( $email );

        if( $bSubscribe ) {
            /* The subscribe link is valid.
             * Add the email to bull_list and uncheck mbr_contacts.bNoEbull if applicable.
             *
             * Issue: If a current member subscribes this way they should remain subscribed more than 2 years after their
             *        membership expires, if we retain proof that they opted in. The separate bull_list and mbr_contacts lists
             *        satisfy this requirement but the criteria could be preserved in UsersMetaData.
             */
            list($eRetBull,$eRetMbr,$sResult) = $this->oEbull->AddSubscriber( $email, "", $this->lang, "bulletin-via-web" );
            $sAlert = $this->oTmpl->ExpandTmpl( 'confirm-subscribe', ['email'=>$email] );
        } else {
            /* The unsubscribe link is valid.
             * Remove the email from bull_list, and also set bNoEBull if it's in mbr_contacts
             */
            $this->oEbull->RemoveSubscriber( $email );
            $sAlert = $this->oTmpl->ExpandTmpl( 'confirm-unsubscribe', ['email'=>$email] );
        }

        done:
        return( [$sAlert, $alertType] );
    }

    private function getBullStatus( $email )
    {
        //$kfrBull = $this->oBull->GetKFR( $email );                // bull_list row for this email
        $kfrBull = $this->oEbull->oDB->GetKFRCond('B', "email='".addslashes($email)."'");

        $oQ = new QServerMbr( $this->oApp, ['config_bUTF8'=>false] );
        $rQ = $oQ->Cmd( 'mbr!getOffice', ['sEmail'=>$email] );
        $raMbr = $rQ['raOut'];

// Actually, check if the member's expiry is within 2 years

        $raBull = ['bIsBullSubscriber' => ($kfrBull != null),
                   'bIsMember' => isset($raMbr['_key']),
                   'bIsMemberSubscriber' => isset($raMbr['_key']) && !$raMbr['bNoEBull'],
        ];
        $raBull['bIsSubscriber'] = $raBull['bIsBullSubscriber'] || $raBull['bIsMemberSubscriber'];

        return( $raBull );
    }


    function SendConfirm( $email, $sCmd )
    /************************************
        Send a confirmation email with a link to our bulletin page.
        BulletinConfirm() has to pick up the parameters and do the right thing.

        $sCmd == (subscribe or unsubscribe) just to multiplex this function
     */
    {
        if( !in_array( $sCmd, array('subscribe','unsubscribe') ) )  return( false );

        $md5 = md5( $email.self::hashSeed );
        $link = "https://seeds.ca/ebulletin?$sCmd=".urlencode($email)."&id=$md5";

        $sEmailBody = $this->oTmpl->ExpandTmpl( $sCmd=='subscribe' ? 'emailConfirmSubscribe' : 'emailConfirmUnsubscribe', array('link'=>$link) );
        $sSubject = $this->oTmpl->ExpandTmpl( $sCmd=='subscribe' ? 'emailConfirmSubscribe-subject' : 'emailConfirmUnsubscribe-subject' );
        return( MailFromOffice( $email, $sSubject, str_replace('<br/>', "\n", $sEmailBody), $sEmailBody, array('from'=>"ebulletin@seeds.ca") ) );
    }

    private function makeTemplates( $lang )
    {
        $raFTemplates = array( SITEROOT."l/mbr/bulletin_tmpl.html" );
        $tagParms = array();
        $tagParms['raResolvers'] = array();
        $tagParms['EnableBasicResolver'] = array(); // no special parms, but you'd put them here

        // vars available in all templates
        $raTmplVars = array();
        $raTmplVars['lang'] = $lang;

        $o = new SEEDTemplate_Generator( array( 'fTemplates' => $raFTemplates,
                                                'SEEDTagParms' => $tagParms,
                                                'vars' => $raTmplVars ) );
        return( $o->MakeSEEDTemplate() );
    }
}


function BulletinDrawControl( SEEDAppConsole $oApp, KeyFrameDB $kfdb, $lang )
{
    $o = new SoDBulletin( $oApp, $kfdb, $lang );
    return( $o->ControlDraw() );
}

function BulletinHandleAction( SEEDAppConsole $oApp, KeyFrameDB $kfdb, $lang )
{
    $o = new SoDBulletin( $oApp, $kfdb, $lang );
    return( $o->HandleAction() );
}
