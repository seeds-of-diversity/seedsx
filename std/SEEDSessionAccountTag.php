<?php

/* SEEDSessionAccountTag
 *
 * Copyright 2017 Seeds of Diversity Canada
 *
 * Handle SEEDTag tags for SEEDSession account information
 */

//include_once( "SEEDSession.php" );
include_once( "SEEDSessionAuthDB.php" );

class SEEDSessionAccountTag
{
    private $kfdb;
    private $oAuthDB;
    private $uidDefault;
    private $raSATParms;

    function __construct( KeyFrameDB $kfdb, $uidDefault, $raSATParms )
    {
        $this->kfdb = $kfdb;
        $this->oAuthDB = new SEEDSessionAuthDBRead( $kfdb );
        $this->uidDefault = $uidDefault;
        $this->raSATParms = $raSATParms;
    }

    function ResolveTag( $raTag, SEEDTagParser $oTagParser, $raParms )
    /*****************************************************************
        Call here from SEEDTagParser::HandleTag to resolve tags having to do with SEEDSessionAccount information
     */
    {
        $s = "";
        $bHandled = true;

        $tag = strtolower($raTag['tag']);

        if( substr($tag, 0, 19) != 'seedsessionaccount_' ) {
            $bHandled = false;
            goto done;
        }

        $bAllowKMbr = intval(@$this->raSATParms['bAllowKMbr']);     // allow arbitrary account e.g. [[SEEDSessionAccount_Name: 1499]]
        $bAllowPwd  = intval(@$this->raSATParms['bAllowPwd']);      // allow the password to be shown

        /* bAllowKMbr allows the target to contain a kMbr for showing information about any account, but it is an optional parameter
         */
        if( $bAllowKMbr && $raTag['target'] ) {
            $uid = intval($raTag['target']);
        } else {
            $uid = $this->uidDefault;
        }
        if( !$uid ) {
            $bHandled = false;
            goto done;
        }

        list($kUser,$raUser) = $this->oAuthDB->GetUserInfo( $uid, false );
        if( !$kUser ) {
            $bHandled = false;
            goto done;
        }

        switch( $tag ) {
            case 'seedsessionaccount_key':      $s = $uid;                                        break;
            case 'seedsessionaccount_email':    $s = @$raUser['email'];                           break;
            case 'seedsessionaccount_realname': $s = @$raUser['realname'];                        break;
            case 'seedsessionaccount_name':     $s = @$raUser['realname'] ? : @$raUser['email'];  break;
            case 'seedsessionaccount_password': $s = $bAllowPwd ? @$raUser['password'] : "";      break;

            case 'seedsessionaccount_trusttest':
                // Parms like bAllowKMbr and bAllowPwd will affect whether certain information will be revealed.
                // Some templates would like to know whether something will be revealed, so they can say something else if not.
                $oTagParser->SetVar( 'bSEEDSessionAllowArbitraryUser', $bAllowKMbr );
                $oTagParser->SetVar( 'bSEEDSessionAllowPassword', $bAllowPwd );
                if( $bAllowPwd ) {
                    // Tell the template whether the user still has the auto-generated password, because a template might legitimately show that
                    // but really shouldn't show their own chosen password
                    $pwd = $this->kfdb->Query1( "SELECT password FROM seeds_1.SEEDSession_Users WHERE _key='$uid'" );
                    if( strlen($pwd) == 5 ) {
                        $oTagParser->SetVar( 'bSEEDSessionPasswordAutoGen', 1 );
                    }
                }
                break;

            default:
                $bHandled = false;
                break;
        }

        done:
        return( array($bHandled,$s) );
    }
}

?>
