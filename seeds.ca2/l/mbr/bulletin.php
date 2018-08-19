<?php

/* Support for e-bulletin content on our web site
 *
 */
include_once( STDINC."SEEDTemplate.php" );
include_once( SEEDCOMMON."mbr/mbrBulletin.php" );    // MbrBulletin
include_once( SITEROOT."/l/mbr/mbrPipe.php" );       // MbrPipeGetContactRA
include_once( SEEDCOMMON."siteutil.php" );           // MailFromOffice


class SoDBulletin
{
    const   hashSeed = "Your Seeds of Diversity e-bulletin";
    private $kfdb;
    private $lang;
    private $oBull;
    private $oForm;     // the form that lets people subscribe/unsubscribe their email address
    private $oTmpl;

    function __construct( KeyFrameDB $kfdb, $lang )
    {
        $this->kfdb = $kfdb;
        $this->lang = $lang;
        $this->oBull = new MbrBulletin( $kfdb );
        $this->oForm = new SEEDForm();
        $this->oTmpl = $this->makeTemplates( $lang );
    }

    function ControlDraw()
    /*********************
        Draw the form that lets people subscribe/unsubscribe their email address
     */
    {
        $s = $this->oTmpl->ExpandTmpl( "bullControlForm", array( 'formAction' => Site_path_self(), 'emailParmName' => $this->oForm->Name('email') ) );

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

        if( ($cmd = SEEDSafeGPC_GetStrPlain( 'bullCtlCmd' )) ) {
            /* The user clicked Subscribe or Unsubscribe on the control form.
             * Maybe we tell them they're already subscribed, not subscribed, etc.
             * Maybe we send them an email to confirm their action.
             */
            list($s,$alertType) = $this->handleCtlCmd( $cmd );    // cmd is the label on the button, English or French

        } else if( isset($_REQUEST['subscribe']) || isset($_REQUEST['unsubscribe']) ) {
            /* Someone clicked on a link in a confirmation email.
             * Validate the link, fulfill the action, and tell them the result.
             */
            if( ($email = SEEDSafeGPC_GetStrPlain('subscribe')) ) {
                $bSubscribe = true;
            } else if( ($email = SEEDSafeGPC_GetStrPlain('unsubscribe')) ) {
                $bSubscribe = false;
            } else {
                return( "" );
            }
            $md5 = SEEDSafeGPC_GetStrPlain('id');
            list($s,$alertType) = $this->handleConfirm( $email, $md5, $bSubscribe );

        }
        if( $s ) {
            $s = "<div class='alert alert-$alertType'>$s</div>";
        }

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

        $raTmplVars = array( 'email'=>$e );

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

        return( array($sAlert, $alertType) );
    }

    private function handleConfirm( $email, $md5, $bSubscribe )
    /**********************************************************
        Someone clicked on a confirmation email.
     */
    {
        $sAlert = "";
        $alertType = "success";

        if( md5($email.self::hashSeed) != $md5 ) {
            $sAlert = $this->oTmpl->ExpandTmpl( 'confirm-badMD5', array( 'bSubscribe' => $bSubscribe ) );
            $alertType = 'danger';
            goto done;
        }

        $raBull = $this->getBullStatus( $email );

        if( $bSubscribe ) {
            /* The subscribe link is valid.
             * Add the name to bull_list.
             *
             * Issue 1: When we sent the link we checked that the email wasn't in bull_list or mbr_contacts, but this person might have joined as
             *          a member in the meantime. Not worth worrying about since bull_list and mbr_contacts.bNoEBull will be handled by
             *          seeds.SEEDSession_UsersMetaData some day anyway.
             * Issue 2: If the email belongs to a bNoEBull member, we should uncheck that preference.
             * Issue 3: If an expired member subscribes, that should override their 2-year contact limit. Also, if a current member subscribes
             *          this way they should remain subscribed more than 2 years after their membership expires, if we retain proof that they
             *          opted in. The separate bull_list and mbr_contacts lists satisfy these requirements, but the criteria could be preserved
             *          in UsersMetaData.
             */
            $this->oBull->AddSubscriber( $email, "", $this->lang, "bulletin-via-web" );
            $sAlert = $this->oTmpl->ExpandTmpl( 'confirm-subscribe', array( 'email'=>$email ) );

            if( $raBull['bIsMember'] && $raBull['raMbr'] && $raBull['raMbr']['bNoEBull'] ) {
                // This email belongs to a member who has bNoEBull.  Not sure what to do about it, but we should know that we have to do something.
                MailFromOffice( 'bob@seeds.ca,office@seeds.ca',
                                "Member ".$raBull['raMbr']['_key']." wants the e-bulletin",
                                "This member had the no-bulletin checkbox checked, but they just signed up for the e-bulletin on the web site.",
                                "This member had the no-bulletin checkbox checked, but they just signed up for the e-bulletin on the web site.",
                                array( 'from' => 'no-reply-website@seeds.ca' ) );
            }
        } else {
            /* The unsubscribe link is valid.
             * Remove the email from bull_list, and also set bNoEBull if it's in mbr_contacts
             */
            $this->oBull->RemoveSubscriber( $email );
            $sAlert = $this->oTmpl->ExpandTmpl( 'confirm-unsubscribe', array( 'email'=>$email ) );

            if( $raBull['bIsMember'] && $raBull['raMbr'] && !$raBull['raMbr']['bNoEBull'] ) {
                // This email belongs to a member. We have to unsubscribe them manually.
                MailFromOffice( 'bob@seeds.ca,office@seeds.ca',
                                "Member ".$raBull['raMbr']['_key']." unsubscribed from the e-bulletin",
                                "This member has unsubscribed, so please check the no-bulletin checkbox in the member database.",
                                "This member has unsubscribed, so please check the no-bulletin checkbox in the member database.",
                                array( 'from' => 'no-reply-website@seeds.ca' ) );
            }
        }

        done:
        return( array($sAlert, $alertType) );
    }

    private function getBullStatus( $email )
    {
        $kfr = $this->oBull->GetKFR( $email );                // bull_list row for this email
        $raMbr = MbrPipeGetContactRA( $this->kfdb, $email );  // mbr_contacts row for this email

// Actually, check if the member's expiry is within 2 years

        $raBull = array( 'bIsBullSubscriber' => (bool)($kfr),
                         'bIsMember' => isset($raMbr['_key']),
                         'bIsMemberSubscriber' => isset($raMbr['_key']) && !$raMbr['bNoEBull'],
                         'kfrBull' => $kfr,
                         'raMbr' => $raMbr,
        );
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
        $link = "http://seeds.ca/ebulletin?$sCmd=".urlencode($email)."&id=$md5";

        $sEmailBody = $this->oTmpl->ExpandTmpl( $sCmd=='subscribe' ? 'emailConfirmSubscribe' : 'emailConfirmUnsubscribe', array('link'=>$link) );
        $sSubject = $this->oTmpl->ExpandTmpl( $sCmd=='subscribe' ? 'emailConfirmSubscribe-subject' : 'emailConfirmUnsubscribe-subject' );
        return( MailFromOffice( $email, $sSubject, str_replace('<br/>', "\n", $sEmailBody), $sEmailBody, array('from'=>"do-not-reply-ebulletin@seeds.ca") ) );
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


function BulletinDrawControl( KeyFrameDB $kfdb, $lang )
{
    $o = new SoDBulletin( $kfdb, $lang );
    return( $o->ControlDraw() );
}

function BulletinHandleAction( KeyFrameDB $kfdb, $lang )
{
    $o = new SoDBulletin( $kfdb, $lang );
    return( $o->HandleAction() );
}


?>