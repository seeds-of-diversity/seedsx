<?php

/* SEEDSessionUGP
 *
 * Show permissions available by Users, Groups, Perms, plus SEEDPerms/Classes
 *
 * Copyright (c) 2010-2015 Seeds of Diversity Canada
 *
 *
 * Typical invocation:
 *
 *  if( !defined("SITEROOT") )  define("SITEROOT", "../../");
 *  include_once( SITEROOT."site.php" );
 *  include_once( SEEDCOMMON."SEEDSessionUGP.php" );
 */

include_once( "siteStart.php" );
include_once( STDINC."KeyFrame/KFUIComponent.php" );
include_once( STDINC."KeyFrame/KFUIForm.php" );
include_once( STDINC."SEEDPerms.php" );
include_once( SEEDCOMMON."console/console01kfui.php" );


//var_dump($_REQUEST);
//var_dump($_SESSION);


list($kfdb, $sess, $dummyLang) = SiteStartSessionAccount( array("SEEDSessionUGP"=>"A") );
$kfdb->SetDebug(1);

class MyConsole extends Console01KFUI
{
    public $oUGP;

    private $oSessDB = NULL;    // deprecate
    private $oAccountDB;

    function __construct( KeyFrameDB $kfdb, SEEDSessionAccount $sess, $raParms )
    {
        parent::__construct( $kfdb, $sess, $raParms );
        $this->oSessDB = new SEEDSessionAuthDB( $kfdb, $sess->GetUID() );   // deprecate
        $this->oAccountDB = new SEEDSessionAccountDB( $kfdb, $sess->GetUID() );

        // deprecated
        SEEDSessionAuthStatic::Init( $this->kfdb, $sess->GetUID() );  // SEEDSessionAuthStatic provides kfrels for each of the components
    }

    function TFmainUsersInit()          { $this->myInit( 'users' ); }
    function TFmainGroupsInit()         { $this->myInit( 'groups' ); }
    function TFmainPermissionsInit()    { $this->myInit( 'perms' ); }
    function TFmainAdminUserInit()      { $this->myInit( 'users' ); }
    function TFmainSEEDPermClassesInit(){ $this->myInit( 'seedpermclasses' ); }
    function TFmainSEEDPermsInit()      { $this->myInit( 'seedperms' ); }

    function TFmainUsersControl()       { return( "<DIV>".$this->oComp->SearchToolDraw()."</DIV>" ); }
    function TFmainGroupsControl()      { return( "<DIV>".$this->oComp->SearchToolDraw()."</DIV>" ); }
    function TFmainPermissionsControl() { return( "<DIV>".$this->oComp->SearchToolDraw()."</DIV>" ); }
    function TFmainAdminUserControl()   { return( "" ); }

    function TFmainUsersContent()       { return( $this->UserContent() ); }
    function TFmainGroupsContent()      { return( $this->GroupContent() ); }
    function TFmainPermissionsContent() { return( $this->PermContent() ); }
    function TFmainAdminUserContent()   { return( $this->AdminUserContent() ); }
    function TFmainSEEDPermClassesContent() { return( $this->CompListForm_Horz() ); }
    function TFmainSEEDPermsContent()   { return( $this->CompListForm_Horz() ); }

    function myInit( $k )
    {
        $oAcctDB = new SEEDSessionAccountDB2( $this->kfdb, $this->sess->GetUID() );
        switch( $k ) {
            //case 'users':           $kfrel = $oAcctDB->GetKfrel('U'); break;
            case 'users':           $kfrel = SEEDSessionAuthStatic::KfrelUsers();             break;
            case 'groups':          $kfrel = SEEDSessionAuthStatic::KfrelGroups();            break;
            case 'perms':           $kfrel = SEEDSessionAuthStatic::KfrelPerms();             break;
            case 'seedpermclasses': $kfrel = SEEDPerms::KfrelSEEDPermClasses( $this->kfdb, $this->sess->GetUID() );  break;
            case 'seedperms':       $kfrel = SEEDPerms::KfrelSEEDPerms_P_C( $this->kfdb, $this->sess->GetUID() );    break;
        }

        $raCompParms = array(
            'users' => array(
                          "Label" => "User",
                          "ListCols" => array( array( "label"=>"Uid",     "colalias"=>"_key",        "w"=>50),
                                               array( "label"=>"Name",    "colalias"=>"realname",    "w"=>150),
                                               array( "label"=>"Email",   "colalias"=>"email",       "w"=>150), //, "colsel" => array("filter"=>"email <>''")),
                                               array( "label"=>"Language","colalias"=>"lang",        "w"=>15, "colsel" => array("filter"=>"")),
                                               array( "label"=>"Group1",  "colalias"=>"G_groupname", "w"=>70, "colsel" => array("filter"=>"")),
                                               ),
                          "ListSize" => 10,
//                          "ListSizePad" => 1,
                          "fnListRowTranslateRA" => array($this,"UsersListRowTranslateRA"),
                          "fnFormDraw"      => array($this,"UsersFormDraw"),
                          "SearchToolCols"  => array( array( 'Name'=>'realname','Email'=>'email','User #'=>'U._key','Group1'=>'G.groupname' ) ),
                        ),
            'groups' => array(
                          "Label" => "Group",
                          "ListCols" => array( array( "label"=>"Gid",  "colalias"=>"_key",     "w"=>50 ),
                                               array( "label"=>"Name", "colalias"=>"groupname","w"=>150),
                                        ),
                          "ListSize" => 10,
//                          "ListSizePad" => 1,
                          "SearchToolCols"  => array( array('Gid'=>'_key','Name'=>'groupname') ),

                        ),
            'perms' => array(
                      "Label" => "Permission",
                      "ListCols" => array( array( "label"=>"Perm",  "colalias"=>"perm",        "w"=>50,  "colsel" => array("filter"=>"")),
                                           array( "label"=>"Modes", "colalias"=>"modes",       "w"=>50),
                                           array( "label"=>"User",  "colalias"=>"U_realname",  "w"=>150, "colsel" => array("filter"=>"realname<>''")),
                                           array( "label"=>"Group", "colalias"=>"G_groupname", "w"=>150, "colsel" => array("filter"=>"groupname<>''")),
                                         ),
                      "ListSize" => 10,
//                      "ListSizePad" => 1,
                      "fnListRowTranslateRA" => array($this,"PermsListRowTranslateRA"),
                      "fnFormDraw"      => array($this,"PermsFormDraw"),
                      "SearchToolCols"  => array( array('Perm'=>'P.perm','Modes'=>'P.modes',
                                                        'User name'=>'U.realname','User email'=>'U.email','User #'=>'U._key',
                                                        'Group name'=>'G.groupname','Group #'=>'G._key') ),
                        ),
            'seedpermclasses' => array(
                      "Label" => "SEEDPermClasses",
                      "ListCols" => array( array( "label"=>"Key",   "colalias"=>"_key",        "w"=>50 ),
                                           array( "label"=>"App",   "colalias"=>"application", "w"=>50 ),
                                           array( "label"=>"Class", "colalias"=>"name",        "w"=>150),
                                         ),
                      "ListSize" => 10,
//                      "ListSizePad" => 1,
                      "SearchToolCols"  => array( array('Key'=>'_key','App'=>'application','Class'=>'name') ),
                        ),
            'seedperms' => array(
                      "Label" => "SEEDPerms",
                      "ListCols" => array( array( "label"=>"App",            "colalias"=>"C_application",   "w"=>100 ),
                                           array( "label"=>"Class",          "colalias"=>"C_name",          "w"=>150 ),
                                           array( "label"=>"User Id",        "colalias"=>"user_id",         "w"=>100 ),
                                           array( "label"=>"User Group",     "colalias"=>"user_group",      "w"=>100 ),
                                           array( "label"=>"Modes",          "colalias"=>"modes",           "w"=>30 ),
                                         ),
                      "ListSize" => 10,
                      "fnListRowTranslateRA" => array($this,"SEEDPermsListRowTranslateRA"),
                      "fnFormDraw"      => array($this,"SEEDPermsFormDraw"),
                      "SearchToolCols"  => array( array('User Id'=>'user_id','User Group'=>'user_group','App'=>'C_application','Class'=>'name','Modes'=>'modes') ),
                        ),
            );
        $this->CompInit( $kfrel, $raCompParms[$k] );
    }

    function ugpStyle()
    {
        $s = "<STYLE>"
             .".ugpForm { font-size:14px; }"
             .".ugpBox { height:200px; border:1px solid gray; padding:3px; font-family:sans serif; font-size:11pt; overflow-y:scroll }"
            ."</STYLE>";
        return( $s );
    }

    function UserContent()
    /*********************
     */
    {
        $s = $this->CompListForm_Horz( array( 'bAllowNew' => false, 'bAllowDelete' => false ) );

        if( ($kUser = $this->oComp->oForm->GetKey()) ) {
            $raGroups = $this->oAccountDB->GetGroupsFromUser( $kUser, array('bNames'=>true) );
            $raPerms = $this->oAccountDB->GetPermsFromUser( $kUser );
            $raMetadata = $this->oAccountDB->GetUserMetadata( $kUser );

            $s .= $this->ugpStyle();

            $s .= "<TABLE cellpadding='20'><TR valign='top'>";

            // Groups list
            $s .= "<TD>"
                 ."<B>Groups</B><BR/><BR/>"
                 ."<DIV class='ugpBox'>";
            foreach( $raGroups as $kGroup => $sGroupname ) {
                $s .= "$sGroupname &nbsp;<SPAN style='float:right'>($kGroup)</SPAN><BR/>";
            }
            $s .= "</DIV>";

            // Group Add/Remove
            $s .= "<BR/>"
                 ."<FORM action='{$_SERVER['PHP_SELF']}' method='post'>"
                 .$this->oComp->EncodeHiddenFormParms()
                 .SEEDForm_Hidden( 'uid', $kUser )
                 .SEEDForm_Hidden( 'form', "UsersXGroups" )
                 .SEEDForm_Text( 'gid', '' )
                 ."<INPUT type='submit' name='cmd' value='Add'/><INPUT type='submit' name='cmd' value='Remove'/>"
                 ."</FORM></TD>";

            // Perms list
            $s .= "<TD>"
                 ."<B>Permissions</B><BR/><BR/>"
                 ."<DIV class='ugpBox'>";
            ksort($raPerms['perm2modes']);
            foreach( $raPerms['perm2modes'] as $k => $v ) {
                $s .= "$k &nbsp;<SPAN style='float:right'>( $v )</SPAN><BR/>";
            }
            $s .= "</DIV></TD>";

            // Metadata list
            $s .= "<TD>"
                 ."<B>Metadata</B><BR/><BR/>"
                 ."<DIV class='ugpBox'>";
            foreach( $raMetadata as $k => $v ) {
                $s .= "$k &nbsp;<SPAN style='float:right'>( $v )</SPAN><BR/>";
            }
            $s .= "</DIV>";

            // Metadata Add/Remove
            $s .= "<BR/>"
                 ."<FORM action='{$_SERVER['PHP_SELF']}' method='post'>"
                 .$this->oComp->EncodeHiddenFormParms()
                 .SEEDForm_Hidden( 'uid', $kUser )
                 .SEEDForm_Hidden( 'form', "UsersMetadata" )
                 ."k ".SEEDForm_Text( 'meta_k', '' )
                 ."<br/>"
                 ."v ".SEEDForm_Text( 'meta_v', '' )
                 ."<INPUT type='submit' name='cmd' value='Set'/><INPUT type='submit' name='cmd' value='Remove'/>"
                 ."</FORM></TD>";

            $s .= "</TR></TABLE>";
        }
        return( $s );
    }

    function GroupContent()
    /**********************
    */
    {
        $s = $this->CompListForm_Horz();

        if( ($kGroup = $this->oComp->oForm->GetKey()) ) {
            $oAuth = new SEEDSessionAuthDB( $this->kfdb, $this->sess->GetUID() );
            $raMetadata = $oAuth->GetGroupMetadata( $kGroup );

            $raUsers = array();
            $raPerms = array();

            /* Get users: gid1 from Users + gid from UsersXGroups
             */
            $raUsers1 = $this->oSessDB->GetUsersFromGroup( $kGroup );  // returns array( kUser=>array(email, ... ), ... )
            $raUsers = array();
            foreach( $raUsers1 as $k => $ra ) {
                $raUsers[$k] = $ra['email'];
            }
            asort( $raUsers );      // sort by email

            /* Get perms as "perm"=>C ; C: R=1,W=2,A=4
             */
            // this could be a function in SEEDSession
            if( ($dbc = $this->kfdb->CursorOpen(
                // perms explicitly set for this group
                "SELECT P.perm AS perm, P.modes as modes "
               ."FROM SEEDSession_Perms P "
               ."WHERE P.gid='$kGroup'" )) )
            {
                while( $ra = $this->kfdb->CursorFetch( $dbc ) ) {
                    if( strchr($ra['modes'],'R') )  @$raPerms[$ra['perm']] |= 1;
                    if( strchr($ra['modes'],'W') )  @$raPerms[$ra['perm']] |= 2;
                    if( strchr($ra['modes'],'A') )  @$raPerms[$ra['perm']] |= 4;
                }
                $this->kfdb->CursorClose( $dbc );
            }

            $s .= $this->ugpStyle();

            $s .= "<TABLE cellpadding='20''><TR valign='top'><TD>"
                 ."<B>Users</B><BR/><BR/>"
                 ."<DIV class='ugpBox'>";
            foreach( $raUsers as $k => $email ) {
                $s .= "$email &nbsp;<SPAN style='float:right'>( $k )</SPAN><BR/>";
            }
            $s .= "</DIV>"
                 ."</TD><TD>"
                 ."<B>Permissions</B><BR/><BR/>"
                 ."<DIV class='ugpBox'>";
            foreach( $raPerms as $k => $v ) {
                $s .= "$k &nbsp;<SPAN style='float:right'>( ".(($v & 1)?"R":"").(($v & 2)?"W":"").(($v & 4)?"A":"")." )</SPAN><BR/>";
            }
            $s .= "</DIV></TD>"
                 ."<TD>User in group<BR/>"
                 ."<FORM action='{$_SERVER['PHP_SELF']}' method='post'>"
                 .$this->oComp->EncodeHiddenFormParms()
                 .SEEDForm_Hidden( 'gid', $kGroup )
                 .SEEDForm_Hidden( 'form', "UsersXGroups" )
                 .SEEDForm_Text( 'uid', '' )
                 ."<INPUT type='submit' name='cmd' value='Add'/><INPUT type='submit' name='cmd' value='Remove'/>"
                 ."</FORM></TD>";

            // Metadata list
            $s .= "<TD>"
                 ."<B>Metadata</B><BR/><BR/>"
                 ."<DIV class='ugpBox'>";
            foreach( $raMetadata as $k => $v ) {
                $s .= "$k &nbsp;<SPAN style='float:right'>( $v )</SPAN><BR/>";
            }
            $s .= "</DIV>";

            // Metadata Add/Remove
            $s .= "<BR/>"
                 ."<FORM action='{$_SERVER['PHP_SELF']}' method='post'>"
                 .$this->oComp->EncodeHiddenFormParms()
                 .SEEDForm_Hidden( 'gid', $kGroup )
                 .SEEDForm_Hidden( 'form', "GroupsMetadata" )
                 ."k ".SEEDForm_Text( 'meta_k', '' )
                 ."<br/>"
                 ."v ".SEEDForm_Text( 'meta_v', '' )
                 ."<INPUT type='submit' name='cmd' value='Set'/><INPUT type='submit' name='cmd' value='Remove'/>"
                 ."</FORM></TD>";

            $s .= "</TD></TR></TABLE>";
        }
        return( $s );
    }

    function PermContent()
    /*********************
     */
    {
    // the perms table doesn't have a unique key. It is a many:many map of perms to uids and gids.
    // The KFUI_List should be a list of DISTINCT(perm) instead.

        $s = $this->CompListForm_Horz();

        $raGroupsList = array();        // list all groups indexed by gid
        $raGroupsListOrdered = array(); // list all groups ordered by name
        $dbc = $this->kfdb->CursorOpen( "SELECT _key,groupname FROM SEEDSession_Groups ORDER BY groupname" );
        while( $ra = $this->kfdb->CursorFetch( $dbc ) ) {
            $raGroupsList[$ra[0]] = $ra[1];
            $raGroupsListOrdered[] = array($ra[0],$ra[1]);
        }
        $this->kfdb->CursorClose( $dbc );

        if( ($kPerm = $this->oComp->oForm->GetKey()) ) {
            $raGroups = array();
            $raUsers = array();

            $condPerm = "perm='".$this->oComp->GetCurrValue("perm")."'";

            /* Get groups and their modes
             */
            $dbc = $this->kfdb->CursorOpen( "SELECT G.groupname as groupname,P.modes as modes "
                                           ."FROM SEEDSession_Perms P,SEEDSession_Groups G "
                                           ."WHERE P.gid>=1 AND P.$condPerm AND P.gid=G._key" );
            while( $ra = $this->kfdb->CursorFetch( $dbc ) ) {
                if( strchr($ra['modes'],'R') )  @$raGroups[$ra['groupname']] |= 1;
                if( strchr($ra['modes'],'W') )  @$raGroups[$ra['groupname']] |= 2;
                if( strchr($ra['modes'],'A') )  @$raGroups[$ra['groupname']] |= 4;
            }
            $this->kfdb->CursorClose( $dbc );
            ksort( $raGroups );     // sort by groupname

// Put this in SEEDSession_Admin_GetUsersFromPerm() - needed by taskmanager.php
            /* Get users and their modes
             */
            $dbc = $this->kfdb->CursorOpen(
                            // users related to the perm
                            "SELECT P.uid as uid,P.modes as modes,U.email as email "
                            ."FROM SEEDSession_Perms P,SEEDSession_Users U "
                            ."WHERE P.uid<>0 AND P.uid IS NOT NULL AND P.$condPerm AND P.uid=U._key " // some uid are negative
                            ."AND P._status='0' AND U._status='0' "
                            // users in groups related to the perm
                            ."UNION "
                            ."SELECT UG.uid as uid,P.modes as modes,U.email as email "
                            ."FROM SEEDSession_Perms P,SEEDSession_UsersXGroups UG,SEEDSession_Users U "
                            ."WHERE P.$condPerm AND P.gid>=1 AND P.gid=UG.gid AND UG.uid=U._key "
                            ."AND P._status='0' AND UG._status='0' AND U._status='0' "
                            // users with primary group related to perm
                            ."UNION "
                            ."SELECT U._key as uid,P.modes as modes,U.email as email "
                            ."FROM SEEDSession_Users U,SEEDSession_Perms P "
                            ."WHERE P.$condPerm AND P.gid>=1 AND P.gid=U.gid1 "
                            ."AND P._status='0' AND U._status='0'" );
            while( $ra = $this->kfdb->CursorFetch( $dbc ) ) {
                if( strchr($ra['modes'],'R') )  @$raUsers[$ra['email']]['modes'] |= 1;
                if( strchr($ra['modes'],'W') )  @$raUsers[$ra['email']]['modes'] |= 2;
                if( strchr($ra['modes'],'A') )  @$raUsers[$ra['email']]['modes'] |= 4;
            }
            $this->kfdb->CursorClose( $dbc );
            ksort( $raUsers );      // sort by email

            $s .= "<TABLE cellpadding='20'><TR valign='top'><TD>"
                 ."<B>Groups</B><BR/><BR/>";
            foreach( $raGroups as $k=>$v ) {
                $s .= $k." ( ".(($v & 1)?"R":"").(($v & 2)?"W":"").(($v & 4)?"A":"")." )<BR/>";
            }
            $s .= "</TD><TD>";
            $s .= "<B>Users</B><BR/><BR/>";

            $s .= "<SELECT size='5' name='ugp_permusers'>";
            foreach( $raUsers as $k => $v ) {
                $v = $v['modes'];
                $s .= "<OPTION value='$v'>$k ( ".(($v & 1)?"R":"").(($v & 2)?"W":"").(($v & 4)?"A":"")." )</OPTION>";
            }
            $s .= "</SELECT>";
            $s .= "</TD></TR></TABLE>";
        }
        return( $s );
    }

    function AdminUserContent()
    {
        $s = $this->CompListForm_Horz( array( 'bAllowNew' => true, 'bAllowDelete' => true ) );

        return( $s );
    }

    function UsersListRowTranslateRA( $raValues )
    /********************************************
     */
    {
        if( $raValues['gid1'] && !empty($raValues['G_groupname']) ) {
            // the relation looks up the group name but it's helpful to show the gid too
            $raValues['G_groupname'] .= " (".$raValues['gid1'].")";
        }
        return( $raValues );
    }

    function PermsListRowTranslateRA( $raValues )
    /********************************************
     */
    {
        if( $raValues['gid'] && !empty($raValues['G_groupname']) ) {
            // the relation looks up the group name but it's helpful to show the gid too
            $raValues['G_groupname'] .= " (".$raValues['gid'].")";
        }

        if( $raValues['uid'] ) {
            if( !empty($raValues['U_realname']) ) {
                $raValues['U_realname'] .= " (".$raValues['uid'].")";
            } else if( !empty($raValues['U_email']) ) {
                $raValues['U_realname'] = $raValues['U_email']." (".$raValues['uid'].")";
            }
        }
        return( $raValues );
    }

    function SEEDPermsListRowTranslateRA( $raValues )
    /************************************************
     */
    {
        $uid = $raValues['user_id'];
        $gid = $raValues['user_group'];

        // uid 0 is just a placeholder in the SEEDPerms table
        // uid -1 is special: the anonymous user, which doesn't (necessarily) exist in SEEDSession_Users
        switch( $uid ) {
            case 0:  $raValues['user_id'] = "";                 break;
            case -1: $raValues['user_id'] = "Anonymous (-1)";   break;
            default: $raValues['user_id'] = $this->kfdb->Query1( "SELECT realname FROM SEEDSession_Users WHERE _key='$uid'" )." ($uid)";  break;
        }

        // gid 0 is just a placeholder in the SEEDPerms table
        switch( $gid ) {
            case 0:  $raValues['user_group'] = "";              break;
            default: $raValues['user_group'] = $this->kfdb->Query1( "SELECT groupname FROM SEEDSession_Groups WHERE _key='$gid'" )." ($gid)";  break;
        }

        /* Check for invalid Classes
         */
        if( empty($raValues['C_name']) ) {
            if( !$this->kfdb->Query1( "SELECT _key FROM SEEDPerms_Classes WHERE _key='{$raValues['C__key']}'" ) ) {
                $raValues['C_name'] = "<span style='color:red;font-weight:bold'>Invalid Class</span>";
            }
        }

        return( $raValues );
    }

    function UsersFormDraw( $oForm )
    /*******************************
     */
    {
        $s = "";

        $bAdminUser = ($this->TabSetGetCurrentTab( "TFmain" ) == 'AdminUser');
        if( $bAdminUser ) {
            // The setkey row-level control forces the key to the given value, if it is not already in use
            $sUserControl = $oForm->Text( 'setkey', "", array('value'=>$oForm->GetKey(),'sfParmType'=>'ctrl_row') );
        } else {
            // Just the plain number, not a control
            $sUserControl = $oForm->GetKey();
        }

        $s .= "<table class='ugpForm' border='0' cellpadding='3'>"
             .$oForm->ExpandForm(
                "||| User # || ".$sUserControl
               ."||| Real Name || [[realname]] "
               ."||| Email || [[email]] "
               // show password control if this is New User, otherwise not?  Maybe show it if you click a control?
               .(!$oForm->GetKey() ? "||| Password || [[password]]" : "")
               ."||| Language || [[lang]]"
               ."||| Group || ".$oForm->Select2('gid1', $this->groupOptionsRA( $oForm ) )
               ."||| eStatus || [[eStatus]]"
               ."||| Extra || [[sExtra]]"
              )
             ."</table>"
             ."<input type='submit' value='Save'/>";
        return( $s );
    }

    function PermsFormDraw( $oForm )
    /*******************************
     */
    {
        $s = "<table border='0' cellpadding='5'>"
            .$oForm->ExpandForm(
                "||| Perms || [[perm]]"
               ."||| Modes || [[modes]]"
               ."||| User || [[uid]]"
               ."||| Group || ".$oForm->Select2('gid', $this->groupOptionsRA( $oForm ) )
            )
            ."</table><br/>"
            ."<input type='submit' value='Save'/>";
        return( $s );
    }

    function SEEDPermsFormDraw( $oForm )
    /***********************************
     */
    {
        $raPermOpts = array_merge( array("--- Perm Class ---"=>0), SEEDPerms::GetRAClassesOpts( $this->kfdb, "", true ) );

        $s = "<table border='0' cellpadding='5'>"
            .$oForm->ExpandForm(
                "||| Class || ".$oForm->Select2('fk_SEEDPerms_Classes', $raPermOpts )
               ."||| User || [[user_id]]"
               ."||| Group || ".$oForm->Select2('user_group', $this->groupOptionsRA( $oForm ) )
               ."||| Modes || [[modes]]"
            )
            ."</table><br/>"
            ."<input type='submit' value='Save'/>";
        return( $s );
    }

    function groupOptionsRA( $oForm )
    {
        $raGroupsAll = array( "--- No Group ---" => 0 );
        if( ($dbc = $oForm->kfrel->kfdb->CursorOpen("SELECT * from SEEDSession_Groups WHERE _status='0'")) ) {
            while( $ra = $oForm->kfrel->kfdb->CursorFetch( $dbc ) ) {
                $raGroupsAll[$ra['groupname']." (".$ra['_key'].")"] = $ra['_key'];
            }
        }
        return( $raGroupsAll );
    }
}




$raConsoleParms = array(
    'HEADER' => "Session Users, Groups, and Permissions on ${_SERVER['SERVER_NAME']}:".SiteKFDB_DB,
//    'HEADER_LINKS' => array( array( 'href' => 'mbr_email.php',    'label' => "Email Lists",  'target' => '_blank' ),
//                             array( 'href' => 'mbr_mailsend.php', 'label' => "Send 'READY'", 'target' => '_blank' ) ),
    'CONSOLE_NAME' => "SessionUGP",
    'TABSETS' => array( "TFmain" => array( 'tabs' => array( 'Users' => array( 'label' => "Users" ),
                                                             'Groups' => array( 'label' => "Groups" ),
                                                             'Permissions' => array( 'label' => "Permissions" ),
                                                             'AdminUser' => array( 'label' => "Admin User" ),
                                                             'SEEDPermClasses' => array( 'label' => "SEEDPermClasses" ),
                                                             'SEEDPerms' => array( 'label' => "SEEDPerms" ) ) ) ),
    'bLogo' => true,
    'bBootstrap' => true,
);
$oC = new MyConsole( $kfdb, $sess, $raConsoleParms );

header( "Content-type: text/html; charset=ISO-8859-1");    // this should be on all pages so accented chars look right (on Linux anyway)

if( SEEDSafeGPC_GetStrPlain( 'form' ) == 'UsersXGroups'  ) {
    $uid = SEEDSafeGPC_GetInt( 'uid' );
    $gid = SEEDSafeGPC_GetInt( 'gid' );
    $cmd = SEEDSafeGPC_GetStrPlain( 'cmd' );

    SEEDSessionAuthStatic::Init( $kfdb, $sess->GetUID() );
    if( $cmd == "Add" ) {
        SEEDSessionAuthStatic::AddUserToGroup( $uid, $gid );  // validates existence of uid and gid
    } else if( $cmd == "Remove" ) {
        SEEDSessionAuthStatic::RemoveUserFromGroup( $uid, $gid );  // validates existence of uid and gid
    }
}

if( SEEDSafeGPC_GetStrPlain( 'form' ) == 'UsersMetadata'  ) {
    $uid = SEEDSafeGPC_GetInt( 'uid' );
    $mdk = SEEDSafeGPC_GetStrPlain( 'meta_k' );
    $mdv = SEEDSafeGPC_GetStrPlain( 'meta_v' );
    $cmd = SEEDSafeGPC_GetStrPlain( 'cmd' );

    $oAuth = new SEEDSessionAuthDB( $kfdb, $sess->GetUID() );
    if( $cmd == "Set" ) {
        $oAuth->SetUserMetadata( $uid, $mdk, $mdv );  // overwrites existing mdk if any
    } else if( $cmd == "Remove" ) {
        $oAuth->DeleteUserMetadata( $uid, $mdk );
    }
}

if( SEEDSafeGPC_GetStrPlain( 'form' ) == 'GroupsMetadata'  ) {
    $gid = SEEDSafeGPC_GetInt( 'gid' );
    $mdk = SEEDSafeGPC_GetStrPlain( 'meta_k' );
    $mdv = SEEDSafeGPC_GetStrPlain( 'meta_v' );
    $cmd = SEEDSafeGPC_GetStrPlain( 'cmd' );

    $oAuth = new SEEDSessionAuthDB( $kfdb, $sess->GetUID() );
    if( $cmd == "Set" ) {
        $oAuth->SetGroupMetadata( $gid, $mdk, $mdv );  // overwrites existing mdk if any
    } else if( $cmd == "Remove" ) {
        $oAuth->DeleteGroupMetadata( $gid, $mdk );
    }
}

echo $oC->DrawConsole( "[[TabSet: TFmain]]" );

?>
