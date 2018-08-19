<?php

/* SeedLiving Users Module
 *
 * Copyright (c) 2015 Seeds of Diversity Canada
 *
 */
include_once( STDINC."SEEDSessionAuthUI.php" );
include_once( SEEDCOMMON."siteutil.php" );    // MailFromOffice

class SLiv_SEEDSessionAccount_UI extends SEEDSessionAccount_UI
{
    // $kfdb has to be the seeds1 db, not the seedliving db (if they're different)
    function __construct( KeyFrameDB $kfdb, SEEDSessionAccount $sess, $raParms = array() )
    {
        parent::__construct( $kfdb, $sess, $raParms );
    }


    protected function MakeURL( $urlType, $raParms = array() )
    {
        $s = "";

        switch( $urlType ) {
            case 'acctProfileURL':               $s = "http://".$_SERVER['SERVER_NAME'].SL2URL."/accountProfile";   break;
            case 'acctCreateURL':                $s = "http://".$_SERVER['SERVER_NAME'].SL2URL."/accountCreate";    break;
            case 'acctCreate-1aEmailLinkURL':    $s = "http://".$_SERVER['SERVER_NAME'].SL2URL."/accountCreate";    break;
            case 'acctUpdateURL':                $s = "http://".$_SERVER['SERVER_NAME'].SL2URL."/accountProfileSave";  break;
        }
        return( $s );
    }

    protected function SendMail( $mailto, $subject, $body )
    {
        // this is used to send password reminders, confirmations, etc
        return( MailFromOffice( $mailto, $subject, "", nl2br($body), array( 'from' => array('office@seeds.ca','SeedLiving by Seeds of Diversity') )));
    }
}


class SLiv_Users
{
    private $oSLiv;

    private $oSessUI;
    private $raUserData = array();    // stored in SEEDSession_UserMetaData, originally stored in seedliving.users/accounts

    /* temp */
    public $kSLivUserid = 0;
    public $kSLivAccid = 0;

    function __construct( SEEDLiving $oSLiv )
    {
        $this->oSLiv = $oSLiv;

        $this->oSessUI = new SLiv_SEEDSessionAccount_UI( $this->oSLiv->kfdb1, $this->oSLiv->sess,
                                                         array( 'oTmpl' => $this->oSLiv->oTmpl->GetSEEDTemplate() ) );

        if( $this->IsLogin() ) {
            $this->fetchUserData();
        }
    }

    function GetCurrUID()  { return( $this->oSLiv->sess->GetUID() ); }
    function GetUID()  { return( $this->GetCurrUID() ); }    // deprecate to differentiate from methods that get other users' info
    function IsLogin() { return( $this->oSLiv->sess->IsLogin() ); }

    function Command( $cmd )
    {
        $bHandled = true;
        $sOut = "";

        switch( $cmd ) {
            case "userLogin-0":           $sOut = $this->UserLogin_0();    break;
            case "accountCreate":         $sOut = $this->CreateAccount();  break;    // handles acctCreate0, 1a, 1b
            // fyi userProfile is the public interface to show any user; accountProfile is just you
            case "accountProfile":        $sOut = $this->Profile();        break;
            case "accountProfileEdit":    $sOut = $this->ProfileForm();    break;
            case "accountProfileSave":    $sOut = $this->ProfileSave();    break;
            case "userLogout":            $this->Logout();                 break;

            // Although this would return bHandled==true, and the calling code does an exit on that, this nevertheless exits on its own.
            case "accountJXAuthenticate": $this->JXAuthenticate();         break;

            default:
                $bHandled = false;
        }
        return( array($bHandled, $sOut) );
    }

    function UserLogin_0()
    {
        global $tt,$gtt;

        // Draw the SEEDSessionUI:AccountLogin template (which is overridden in seedliving:user.html
        list($bHandled,$s) = $this->oSessUI->Command( 'acctLogin0', GetTokensRA(array($tt,$gtt)) );

        return($s );
    }

    function CreateAccount()
    {
        global $tt,$gtt;
        $sOut = "";

        // handle any of the acctCreate commands, default to acctCreate (same as acctCreate0)
        $cmd = SEEDSafeGPC_Smart( 'sessioncmd', array('acctCreate','acctCreate-1a','acctCreate-1b','acctUpdate','acctUpdate-1') );

        $raVars = GetTokensRA( array($tt,$gtt) );
        list($bHandled,$sOut) = $this->oSessUI->Command( $cmd, $raVars );

        return( $sOut );
    }

    function JXAuthenticate()
    /************************
        Handle an ajax call to authenticate a userid/password.
        Authentication already happened when SEEDSessionAccount was constructed.
     */
    {
        if( $this->IsLogin() ) {
            $this->resetSlivState();
            header("Cache-Control: no-cache");
            $this->jxSuccess();
        } else {
            $this->jxFail("Unknown user or password.");
        }
    }

    private function jxSuccess( $sOut = "" )
    {
        echo json_encode( array( 'bOk' => true, 'sOut' => $sOut ) );
        exit;
    }

    private function jxFail( $sOut = "" )
    {
        echo json_encode( array( 'bOk' => false, 'sOut' => $sOut ) );
        exit;
    }


    function Logout()
    {
        $this->resetSlivState();

        if( method_exists( $this->oSLiv->sess, "LogoutSession" ) ) {
            $this->oSLiv->sess->LogoutSession();
        }
        $this->oSLiv->GotoLoginPage();    // doesn't return
    }

    private function resetSlivState()
    {
        // login and logout seem like nice times to do this
        global $gtt;
        $dbAccid = addslashes( ttn($gtt,"account_id") );
        $dbIp = addslashes( ttn($gtt,"account_ip") );
        $this->oSLiv->kfdb->Execute( "DELETE FROM carts WHERE cart_userid='$dbAccid'" );
        $this->oSLiv->kfdb->Execute( "DELETE FROM breadcrumbs WHERE bc_accountid='$dbAccid' OR bc_ip='$dbIp'" );
    }

    function Profile()
    {
// ADD user_screenname


        global $mas, $tt, $gtt, $temptt, $mas2, $dtt, $ott, $tmpl;

        $s = "";

        $dbAccid = addslashes($this->kSLivAccid);

		if(ttn($gtt,"account_validation")){
			if( ttn($gtt,"account_prepaid")=="P" || ttn($gtt,"account_preapproval")=="P" ) {
			    mas_qnr($mas,"UPDATE accounts SET account_prepaid = 'N', account_preapproval = 'N', account_accesslevel = 'B' WHERE account_id='$dbAccid'" );
				mas_qnr($mas,"DELETE FROM pres WHERE pre_accountid='$dbAccid'" );
				mas_qnr($mas,"DELETE FROM preas WHERE prea_accountid='$dbAccid'" );
			}
			$this->oSLiv->Tmpl( "Top", array($tt,$gtt) );
			tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"secureUserValidation"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
			$this->oSLiv->Tmpl( "Bottom", array($tt,$gtt) );

			return;
		}

		mas_q1($mas,$ott,"SELECT * FROM userAddressCheck WHERE u_userid='$dbAccid'" );
		if(!$mas->mas_row_cnt) {
			mas_q1($mas,$temptt,"SELECT user_id as `@id` FROM users WHERE user_accountid='$dbAccid'" );
			tkntbl_add($tt,"slAddressCheck","1",1);
//ackProfile
            $this->oSLiv->Tmpl( "userProfileForm", array($tt,$gtt,$temptt) );
			mas_qnr($mas,"INSERT INTO userAddressCheck VALUES('','$dbAccid')");
			criterr(NULL);
		}

		if( ttn($gtt,"account_donate")=="N" && ttn($gtt,"fee_enabled")=="N" ) {
		    tkntbl_add($tt,"slSkipOp",1,1);
		    mas_qnr($mas,"UPDATE accounts SET account_donate = 'Y' WHERE account_id = '$dbAccid'");
		    $this->oSLiv->Tmpl( "Top", array($tt,$gtt) );
		    tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpl,"slDonateCheckout"),OPENTAG,CLOSETAG,stdout,1,array(&$tt,&$gtt));
		    $this->oSLiv->Tmpl( "Bottom", array($tt,$gtt) );
		    criterr(NULL);
		}

		mas_q1($mas,$temptt,"SELECT * FROM carts_a WHERE cart_userid = '$dbAccid'");
		if($mas->mas_row_cnt){
		    mas_qnr($mas,"INSERT INTO carts (SELECT * FROM carts_a WHERE cart_userid = '$dbAccid')");
		    mas_qnr($mas,"DELETE FROM carts_a  WHERE cart_userid = '$dbAccid'");
		    list($ok,$s) = cart($mas,$mas2,$dtt,$tmpl,$gtt,$tt,$temptt, $oSLiv);
		    echo $s;
		    //header("Location: /".SEONAME."/mybasket/");
		    //criterr(NULL);
		}

		$raTmpl = array( "user_sfile" => $this->oSLiv->ImgSrc( 'users', $this->kSLivUserid, 1, true, false ) );

//				 if(file_exists(IMAGEROOT."users/".ttn($temptt,"user_id")."_1.jpg")){
//				 	tkntbl_snprintf($gtt,"user_image",1,MAX_RESULTS,"users/%s_1.jpg",ttn($temptt,"user_id"));
//				 } else tkntbl_add($temptt,"user_image","noImageAvailable.jpg",1);

        /* Calculations */
        $raTmpl['totalPurchased']    = $this->oSLiv->kfdb->Query1( "SELECT count(*) FROM sales WHERE sale_buyerid='$dbAccid'" );
        $raTmpl['totalSale']         = $this->oSLiv->kfdb->Query1( "SELECT count(*) FROM seeds WHERE seed_userid='{$this->kSLivUserid}'" );
        $raTmpl['totalSwap']         = $this->oSLiv->kfdb->Query1( "SELECT count(*) FROM seeds WHERE seed_userid='{$this->kSLivUserid}' AND (seed_tradetable='Y' OR seed_trade='Y')" );
        $raTmpl['totalSold']         = $this->oSLiv->kfdb->Query1( "SELECT count(*) FROM sales WHERE sale_accountid='$dbAccid'" );
        $raTmpl['totalEvents']       = $this->oSLiv->kfdb->Query1( "SELECT count(*) FROM events WHERE event_enabled='Y'" );
        $raTmpl['totalNews']         = $this->oSLiv->kfdb->Query1( "SELECT count(*) FROM news WHERE new_enabled='Y'" );
        $raTmpl['userSwapAmount']    = $this->oSLiv->kfdb->Query1( "SELECT SUM(us_blocks) FROM userSwapCount WHERE us_enabled='Y' AND us_accountid='$dbAccid'" );

        mas_q1($mas,$temptt,"SELECT * FROM pres WHERE pre_accountid = '$dbAccid'" );
        mas_q1($mas,$temptt,"SELECT * FROM unlimited WHERE u_accountid = '$dbAccid'" );
        mas_q1($mas,$temptt,"SELECT * FROM preas WHERE prea_accountid = '$dbAccid'" );

        $swapOpen = $this->oSLiv->kfdb->Query1( "SELECT count(*) FROM tradingtable WHERE tt_seeduserid='$dbAccid' AND tt_approved='Y' AND tt_completed='N'" );
        $swapReq  = $this->oSLiv->kfdb->Query1( "SELECT count(*) FROM swaprequests WHERE sr_approved='N' AND sr_user2id='$dbAccid'" );
        $raTmpl['totalSwapRequests'] = $swapReq + $swapOpen;

        if( ttn($gtt,"fee_enabled") == "Y" ) {
            tmplt_proc_ex_tt(TEMPLROOT,tkntbl_search($tmpl,"slAccountDesc"),OPENTAG,CLOSETAG,1,stdout,$tt,"slAccountDesc",array(&$tt,&$temptt,&$gtt));
        }

        // Draw the SEEDSessionUI:AccountProfile template (which is overridden in seedliving:user.html
        list($bHandled,$s) = $this->oSessUI->Command( 'acctProfile', array_merge($raTmpl,$this->GetRAUserData(),GetTokensRA(array($tt,$gtt,$temptt)) ) );

        //$s = $this->oSLiv->Tmpl2( "AccountProfile", array($tt,$gtt,$temptt), array_merge($raTmpl,$this->GetRAUserData()) );

        return( $s );
    }

    function GetRACurrUserData()
    {
        return( $this->raUserData );
    }
    function GetRAUserData()    // deprecate to differentiate from methods that get other users' data
    {
        return( $this->GetRACurrUserData() );
    }

    function FetchUserMetadata( $kUser )
    /***********************************
        Get metadata for any given user (use GetRACurrUserData for the current logged in user)
     */
    {
        $ra = array();

        $raMD = $this->oSLiv->sess->oAuthDB->GetUserMetadata( $kUser, false );

        foreach( $this->userDataKeys as $kSLiv => $kUM ) {
            $ra[$kSLiv] = @$raMD[$kUM];
        }

        return( $ra );
    }


    /* Map the seedliving template names to the seedsession usermetadata names
     */
//TODONEXT : eliminate this map by renaming the sliv side
    private $userDataKeys = array(
        // Seedliving   SEEDSession_UsersMetaData
        'user_screenname'=> 'user_screenname',
        'user_firstname'=> 'user_firstname',
        'user_lastname'=> 'user_lastname',
        'user_address' => 'user_address',
        'user_city'    => 'user_city',
        'user_state'   => 'user_province',
        'user_zip'     => 'user_postcode',
        'user_country' => 'user_country',
        'user_phone'   => 'user_phone',
        'user_desc'    => 'user_profile_desc',
        'user_ip'      => 'user_ip',
    );
    private function fetchUserData()
    {
        $raMD = $this->oSLiv->sess->oAuthDB->GetUserMetadata( $this->GetUID(), false );

        $this->kSLivUserid = intval(@$raMD['sliv_userid']);
        $this->kSLivAccid  = intval(@$raMD['sliv_accid']);

        $this->kSLivUserid = $this->GetUID();
        $this->kSLivAccid = $this->GetUID();


        if( !$this->kSLivUserid || !$this->kSLivAccid )  die( "UserMetadata:sliv_userid is not set" );

        $this->raUserData['kSLivUserid'] = $this->kSLivUserid;
        $this->raUserData['kSLivAccid'] = $this->kSLivAccid;

        foreach( $this->userDataKeys as $kSLiv => $kUM ) {
            $this->raUserData[$kSLiv] = @$raMD[$kUM];
        }
    }

    private function putUserData()
    {
        $o = new SEEDSessionAuthDB( $this->oSLiv->kfdb1, $this->GetUID() );
        foreach( $this->userDataKeys as $kSLiv => $kUM ) {
            $o->SetUserMetaData( $this->GetUID(), $kUM, @$this->raUserData[$kSLiv] );
        }
    }

    function ProfileForm()
    {
        if( !($uid = $this->GetUID()) )  return;
        if( !$this->kSLivUserid ) return;

        global $tt, $gtt, $temptt;

/*
TODONEXT
        $dbAccid = addslashes(ttn($gtt,"account_id"));
        $userid = $this->oSLiv->kfdb->Query1( "SELECT user_id FROM users WHERE user_accountid='$dbAccid'" );
        if( !$userid || $userid != ttn($tt,"@id") ) {
            $this->oSLiv->GotoPage( "userLogout" );
        }
*/
        $raTmpl = array( "user_sfile" => $this->oSLiv->ImgSrc( 'users', $this->kSLivUserid ) );
        $raTmpl['email'] = $this->oSLiv->sess->GetEmail();

        // Draw the SEEDSessionUI:AccountUpdate template (which is overridden in seedliving:user.html
        list($bHandled,$s) = $this->oSessUI->Command( 'acctUpdate-0', array_merge($raTmpl,$this->GetRAUserData(),GetTokensRA(array($tt,$gtt,$temptt)) ) );

//        $s = $this->oSLiv->Tmpl2( "AccountUpdate", array($tt,$gtt,$temptt), array_merge($raTmpl,$this->GetRAUserData()) );

        return( $s );
    }

    function ProfileSave()
    {
        if( !($uid = $this->GetUID()) || !$this->kSLivUserid )  return;

        // use SEEDSessionUI:AccountUpdate-1 to save the data from AccountUpdate-0
        list($bHandled,$s) = $this->oSessUI->Command( 'acctUpdate-1' );

/*
        foreach( $this->userDataKeys as $kSLiv => $kUM ) {
            if( isset($_REQUEST[$kSLiv]) ) {
                $this->raUserData[$kSLiv] = SEEDSafeGPC_GetStrPlain( $kSLiv );
            }
        }
ackProfile
        $this->raUserData['user_ip'] = $_SERVER['REMOTE_ADDR'];
        $this->putUserData();
*/

        slUpdateRequest( null, "users", "U", $this->GetUID() );    // This would be "A" when an account is created

/*
        global $tt, $mas, $ftt, $gtt, $mas4;

        header("Cache-Control: no-cache");
        tkntbl_rmv($tt,"overlord");
        tkntbl_rmv($tt,"user_image");
        mas_lts($mas,$ftt,"users");
        tkntbl_add($tt,"user_ip",ttn($gtt,"REMOTE_ADDR"),1);
        tkntbl_add($tt,"user_accountid",ttn($gtt,"account_id"),1);
        if(!ttn($tt,"@id")){
            tkntbl_rmv($tt,"@id");
            tkntbl_add($tt,"user_tsadd",time(),1);
            mas_gri($mas,$tt,$ftt,1,"users");
            tkntbl_add($tt,"@id",mas_insert_id($mas),1);
            slUpdateRequest( null, "users", "A", ttn($tt,"@id") );
            $dbUid = addslashes(ttn($tt,"@id"));
            $dbAccid = addslashes(ttn($gtt,"account_id"));
            $this->oSLiv->kfdb->Execute( "UPDATE accounts SET account_userid='$dbUid' WHERE account_id='$dbAccid'" );
        } else {
            tkntbl_add($tt,"user_tsmod",time(),1);
            mas_gru($mas,$tt,$ftt,"user_id",ttn($tt,"@id"),1,"users");
            slUpdateRequest( null, "users", "U", ttn($tt,"@id") );
        }
*/

// ackProfile put this in SEEDSessionAccountUI:AccountUpdate-1
$o = new imgman($this->oSLiv->oDocRepDB);
$o->uploadSfile( 'users', 'user_image', $this->kSLivUserid );

        $this->oSLiv->GotoPage( "accountProfile" );
    }
}

?>
