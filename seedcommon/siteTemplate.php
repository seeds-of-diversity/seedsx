<?php
/*
 * SiteTemplate
 *
 * Copyright 2016-2017 Seeds of Diversity Canada
 *
 * Handle our advanced template functions.
 *
 * As much as possible, try to include code only when necessary and create objects only when necessary.
 */

include_once( STDINC."SEEDTemplateMaker.php" );
include_once( SEEDCOMMON."doc/docUtil.php" );


class MasterTemplate
{
    private $oTmpl;

    private $kfdb;  // used by $this->ResolveTag
    private $uid;
    private $lang;  // used by $this->ResolveTag
    private $oDesc = null;

    function __construct( KeyFrameDB $kfdb, $uid, $lang, $raParms )
    {
        $this->kfdb = $kfdb;
        $this->uid = $uid;
        $this->lang = $lang;

        /* $raParms['raSEEDTemplateMakerParms'] defines any resolvers etc that should precede the usual resolvers, as well as any
         * special parms for the SEEDTemplateMaker.
         *
         * EnableDocRep :
         *      site = 'public' | 'office'
         *      flag = the DocRep flag
         *      oDocRepDB = a DocRepDB object - the default uses the given kfdb and uid and is readonly, so you mostly won't need this
         *
         * EnableSEEDLocal :
         *      oLocal = a SEEDLocal object (if not defined, we create a minimal SEEDLocal so [[Lang:]] works)
         *      lang   = 'EN' | 'FR' (only needed if oLocal not defined)
         *
         * EnableSEEDForm  : define this in raSEEDTemplateMakerParms for now
         *
         * EnableSEEDSession : give info about the current user, or another user
         *
         * The BasicResolver is enabled by default.
         */
        $raTmplParms = SEEDCore_ArraySmartVal1( $raParms, 'raSEEDTemplateMakerParms', array() );

        $raTmplParms['fTemplates'][] = SEEDCOMMON."templates/seedui.html";

        $raTmplParms['raResolvers'][] = array('fn' => array($this,'ResolveTag'),
                                              'raParms' => SEEDStd_ArrayParmArray( $raParms, 'raResolverParms' ) );

        if( isset($raParms['EnableDocRep']) ) {
            include_once( STDINC."DocRep/DocRepTag.php" );

            $site = SEEDCore_ArraySmartVal1( $raParms['EnableDocRep'], 'site', 'public' );
            $flag = SEEDCore_ArraySmartVal1( $raParms['EnableDocRep'], 'flag', 'PUB', true );   // '' is allowed as a value, but PUB is the default if not set

            // The default oDocRepDB uses the given kfdb and uid, and is readonly. Give a different one otherwise.
            if( !($oDocRepDB = @$raParms['EnableDocRep']['oDocRepDB']) ) {
                $oDocRepDB = New_DocRepDB_WithMyPerms( $kfdb, $uid, array( 'bReadonly' => true ) );
            }
            $oHandler = new DocRepTagHandler( $oDocRepDB, $flag, array('site'=>$site) );
            $raTmplParms['raResolvers'][] = array( 'fn'=>array($oHandler,'ResolveTag'), 'raParms'=>array() );
        }

        if( isset($raParms['EnableSEEDLocal']) ) {
            include_once( STDINC."SEEDLocal.php" );

            if( !($oLocal = @$raParms['EnableSEEDLocal']['oLocal']) ) {
                $oLocal = new SEEDLocal( array(), $this->lang );
            }
            $raTmplParms['raResolvers'][] = array( 'fn'=>array($oLocal,'ResolveTag'), 'raParms'=>array() );
        }

        if( isset($raParms['EnableSEEDSession']) ) {
            include_once( STDINC."SEEDSessionAccountTag.php" );

            if( !($oSessTag = @$raParms['EnableSEEDSession']['oSessTag']) ) {
                // We don't want a typical instance of MasterTemplate to allow people to make templates that are security risks.
                // Therefore this default oSessTag doesn't allow you to show info for other peoples' accounts e.g. [[SEEDSessionAccount_Email: 1499]]
                // Also it will never show any password.
                // If you want to do these things supply your own oSessTag with the raParms that enable these security risks.
                $oSessTag = new SEEDSessionAccountTag( $this->kfdb, $this->uid, array() );
            }
            $raTmplParms['raResolvers'][] = array( 'fn'=>array($oSessTag,'ResolveTag'), 'raParms'=>array() );
        }

        // Normally you always use the basic resolver, and you probably only set parms if you want to override the defaults
        if( @$raParms['EnableBasicResolver'] != "DISABLE" ) {
            $raTmplParms['bEnableBasicResolver'] = true;
            $raTmplParms['raBasicResolverParms'] = array_merge(
                    array( 'LinkBase'=>"http://seeds.ca/", "ImgBase"=>"http://seeds.ca/d?n="),
                    (@$raParms['EnableBasicResolver'] ? $raParms['EnableBasicResolver'] : array()) ); // given parms in the second arg overwrite the defaults in the first arg
        }
        $this->oTmpl = SEEDTemplateMaker( $raTmplParms );
    }

    function GetTmpl()  { return( $this->oTmpl ); }

    function ResolveTag( $raTag, SEEDTagParser $oTagDummy_same_as_this_oTmpl_oSeedTag, $raParms = array() )
    /*************************************************************************
     */
    {
        //var_dump($raTag);
        $s = "";
        $bHandled = true;

        switch( strtolower($raTag['tag']) ) {
            case 'events':
                if( $raTag['target'] == 'SSList' ) {
                    /* [[events: SSList | date1 | date2 | nLimit ]]
                     * Show a small list of Seedy Saturdays between date1 and date2
                     */
                    if( !($d1 = @$raTag['raParms'][1]) ) {
                        if( STD_isLocal ) $s = "<div class='alert alert-danger'>**events:SSList** date1 not defined</div>";
                        goto done;
                    }
                    if( !($d2 = @$raTag['raParms'][2]) ) {
                        if( STD_isLocal ) $s = "<div class='alert alert-danger'>**events:SSList** date2 not defined</div>";
                        goto done;
                    }
                    $nLimit = intval(@$raTag['raParms'][3]);    // zero means no limit

                    include_once( SEEDCOMMON."ev/_ev.php" );
                    $oEv = new EV_Events( $this->kfdb, 0, $this->lang );
                    if( ($kfrc = $oEv->GetKFRC( array( 'type'=>'SS', 'dateAfter'=>$d1, 'dateBefore'=>$d2, 'sort'=>'dateUp' ))) ) {
                        $sDate = "";
                        while( $kfrc->CursorFetch() ) {
                            $sD = SEEDDateStr( SEEDDateDB2UnixTime( $kfrc->Value('date_start') ), $this->lang );
                            $sStyleDate = $this->oTmpl->GetVar( 'eventsSSListDateStyle' );
                            $sStyleCity = $this->oTmpl->GetVar( 'eventsSSListCityStyle' );

                            if( $sD != $sDate ) {
                                if( $sDate ) $s .= "</p>";
                                $s .= "<p><span style='$sStyleDate'>$sD</span>";
                                $sDate = $sD;
                            }
                            $urlEvents = $this->lang == 'FR' ? "http://semences.ca/evenements" : "http://seeds.ca/events";
                            $s .= $kfrc->Expand( "<br/><a href='$urlEvents' target='_blank' style='$sStyleCity'>[[city]], [[province]]</a>" );
                            if( $nLimit > 0 && (--$nLimit) == 0 ) break;    // don't test or decrement if already zero (no limit)
                        }
                        if( $sDate ) $s .= "</p>";
                    }
                    $bHandled = true;
                }
                break;

            case 'msd':
            case 'sed':     // deprecated
                // [[msd:seedlist|kMbr]]
                // MSDQ is configured to override read access on seeds so the bulk emailer can show each grower their skipped and deleted seeds.
                include_once( SEEDLIB."msd/msdq.php" );
                if( $raTag['target'] == 'seedlist' ) {
                    if( !($kMbr = intval($raTag['raParms'][1])) ) {
                        if( SEED_isLocal ) $s = "<div class='alert alert-danger'>**msd:seedlist** kMbr not defined</div>";
                        goto done;
                    }

                    if( !($sSeedListStyle = $this->oTmpl->GetVar('sSeedListStyle')) ) {
                        $sSeedListStyle="font-family:verdana,arial,helvetica,sans serif;margin-bottom:15px";
                    }

                    $oApp = SiteAppConsole();   // seeds1 and no perms required
                    $o = new MSDQ( $oApp, ['config_bUTF8'=>false, 'config_bAllowCanSeedRead'=>true] );
                    $rQ = $o->Cmd( 'msdSeedList-Draw', ['kUidSeller'=>$kMbr, 'eStatus'=>'ALL'] );
                    $s =
                    "<style>.sed_seed_skip {background-color:#ccc} .sed_seed {margin:10px}</style>"
                    .$rQ['sOut'];
/*
                    include_once( SEEDCOMMON."sl/sed/sedCommon.php" );
                    $oSed = new SEDCommonDraw( $this->kfdb, 0, $this->lang, "REVIEW" );    // uid == 0

                    // REVIEW mode includes bSkip and bDelete
                    $raKfParms = array("sSortCol"=>"category,type,variety");
                    if( ($kfrc = $oSed->GetKfrcS( "mbr_id='$kMbr'", $raKfParms )) ) {
                        while( $kfrc->CursorFetch() ) {
                            $s .= "<div style='$sSeedListStyle'>".$oSed->DrawSeedFromKFR( $kfrc )."</div>";
                        }
                    }
*/
                    $bHandled = true;
                }
                break;

            case 'cd':
                include_once( SEEDLIB."sl/desc/SLDesc.php" );
                if( !$this->oDesc ) $this->oDesc = new SLDescReadOnly( $this->kfdb, $this->lang );

                $s = $this->oDesc->DrawQuestion( $raTag['target'] );
                $bHandled = true;
                break;

            default:
                $bHandled = false;
        }

        done:
        return( array($bHandled,$s) );
    }
}


function siteTemplateGo( DocRepDB $oDocRepDB, $flag, $raDRVars, $kDoc, $kTemplate )
{
    $oDocRepWiki = new DocRepWiki( $oDocRepDB, $flag,
                                   // tell SEEDWiki to allow unknown tags to pass through, so MasterTemplate can process them
                                   array('kluge_dontEatMyTag'=>true) );
    $oDocRepWiki->AddVars( $raDRVars );
    $sDoc = $kTemplate ? $oDocRepWiki->TranslateDocWithTemplate( $kDoc, $kTemplate )
                       : $oDocRepWiki->TranslateDoc( $kDoc );

    $lang = $oDocRepWiki->Lang();
    $vars = $oDocRepWiki->raParms['vars'];
    $kfdb = $oDocRepDB->kfdb;
    $uid = $oDocRepDB->uid;
    $oMTmpl = new MasterTemplate( $kfdb, $uid, $lang, array( 'EnableSEEDLocal'=>array(), 'EnableDocRep'=>array('flag'=>$flag) ) );
    if( $kTemplate ) {
        // this is what DocRepWiki is doing above. that mechanism has to be replicated here, using the code in DocRepTag::drawDocInSkin
        $oMTmpl->GetTmpl()->SetVar( 'drDoc', $kTemplate );
        $oMTmpl->GetTmpl()->SetVar( 'drDocPrime', $kDoc );
    } else {
        $oMTmpl->GetTmpl()->SetVar( 'drDoc', $kDoc );
    }
    $sDoc = $oMTmpl->GetTmpl()->ExpandStr( $sDoc, array_merge( $vars, array( 'uid' => $uid ) ) );

    return( $sDoc );
}

?>
