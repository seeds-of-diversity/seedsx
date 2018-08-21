<?php

class SLCollectionCollection
{
    private $oSCA;
    private $kfrelC;

    private $oForm;

    private $raCollW = array();   // writable collections (possibly adminable)
    private $raCollR = array();   // readonly collections

    function __construct( SLCollectionAdmin $oSCA )
    {
        $this->oSCA = $oSCA;

        $this->kfrelC = $this->oSCA->oSLDBMaster->GetKfrel('C');

        /* Update collections before enumerating them
         */
        $this->oForm = new KeyFrameUIForm( $this->kfrelC, 'C', array( 'DSParms'=> array('fn_DSPreStore'=>array($this,'collection_DSPreStore'),
                                                                                        'fn_DSPostStore'=>array($this,'collection_DSPostStore')) ) );
        $this->oForm->Update();


        // raCollW,raCollR store all collections readable by the current user
        if( $this->oSCA->IsAdmin ) {
            // current user has SEEDSession_Perms SLCollection=='A' or SL=='A'
            $sCond = "";
        } else if( !$this->oSCA->sess->IsLogin() ) {
            $sCond = "eReadAccess='PUBLIC'";
        } else {
            $sCond = "eReadAccess in ('PUBLIC','COLLECTORS') OR "
                    ."uid_owner='".$this->oSCA->sess->GetUID()."'";

            $raP = $this->oSCA->oPermsTest->GetClassesAllowed( "R", false );    // current user's R permclasses
            if( count($raP) ) {
                $sCond .= " OR ".SEEDCore_MakeRangeStrDB( $raP, 'permclass' );
            }
        }
        if( ($kfr = $this->kfrelC->CreateRecordCursor( $sCond )) ) {
            while( $kfr->CursorFetch() ) {
                if( $this->CanWriteCollection( $kfr->Key() ) ) {
                    $this->raCollW[$kfr->Key()] = $kfr->ValuesRA();
                } else {
                    $this->raCollR[$kfr->Key()] = $kfr->ValuesRA();
                }
            }
        }
    }

    public function RACollReadOnly() { return( $this->raCollR ); }
    public function RACollWritable() { return( $this->raCollW ); }  // some of these might be adminable too

    public function CanAdminCollection( $kColl )  { return( $this->testpermCollection( $kColl, 'A' ) ); }
    public function CanWriteCollection( $kColl )  { return( $this->testpermCollection( $kColl, 'W' ) ); }
    public function CanReadCollection( $kColl )   { return( $this->testpermCollection( $kColl, 'R' ) ); }

    private function testpermCollection( $kColl, $mode )
    /***************************************************
        True if the current user has the given permission on the collection.

        oSCA->sess->TestPerm('SLCollection') gives app-level permissions for users
            R means you can see whatever SEEDPerms allows
            W means you can edit whatever SEEDPerms allows
            A means you can do anything on any collection -- this is the only one that's used in practice, since the others are redundant

        oSCA->oPerms gives per-collection permission
            R means you can see what's in a collection
            W means you can edit the contents of a collection but not edit the sl_collection record
            A means you can edit the sl_collection record (and grant permissions)

        In addition, the eReadAccess field of sl_collection gives read-permission to various categories of users.

        Also, the uid_owner of the collection is always an Administrator of the collection.
     */
    {
        // test if current user has SEEDSession_Perms==A permission (admin of all collections)
        if( $this->oSCA->IsAdmin ) return( true );

        if( ($kfr = $this->kfrelC->GetRecordFromDBKey( $kColl )) ) {
            // test if current user is the owner (admin of this collection)
            if( $kfr->Value('uid_owner') == $this->oSCA->sess->GetUID() )  return( true );

            // test if reading a collection that is given general read access (the current user must be a registered collector because they're here)
            if( $mode=='R' && in_array( $kfr->Value('eReadAccess'), array('PUBLIC','COLLECTOR') ) )  return( true );

            // test if the current user has the required permclass mode
            if( ($permclass = $kfr->Value('permclass')) &&
                $this->oSCA->oPermsTest->IsClassModeAllowed( $permclass, $mode ) )  return( true );
        }

        return( false );
    }

    public function GetCollectionsVisibleByMe()
    {
        return( array_merge( array_keys($this->raCollW), array_keys($this->raCollR) ) );
    }

    public function ShowCollections()
    {
        $s = "";

        $pCmd = SEEDSafeGPC_GetStrPlain( 'pCmd' );

        $oForm = $this->oForm;

        $raC = $this->GetCollectionsVisibleByMe();

        $sMyColls = "";
        $sOpenColls = "";
        $nPrivateColls = $this->oSCA->kfdb->Query1( "SELECT count(*) FROM seeds.sl_collection WHERE _status='0'" )
                         - count($raC);

        if( count($raC) &&
            ($kfr = $this->kfrelC->CreateRecordCursor( SEEDCore_MakeRangeStrDB($raC,"_key") )) )
        {
            while( $kfr->CursorFetch() ) {
                $sClass = "well";

                if( $this->CanAdminCollection($kfr->Key()) ) {
                    // my collections
                    if( intval($pCmd) == $kfr->Key() ) {
                        $sC = $this->collectionForm( $oForm, $kfr );
                        $sClass .= " SEEDPopover SPop_collection_mine_edit";
                        $this->oSCA->sSPop = "collection_mine_edit";
                    } else {
                        $sC = $this->collectionDraw( $kfr );
                        if( !$sMyColls && $pCmd != 'new' ) {
                            $sClass .= " SEEDPopover SPop_collection_mine";
                            $this->oSCA->sSPop = "collection_mine";
                        }
                    }
                    $sMyColls .= "<div class='$sClass'>$sC</div>";
                } else {
                    // open collections
                    if( !$sOpenColls ) {
                        $sClass .= " SEEDPopover SPop_collection_other";
                    }
                    $sC = $this->collectionDraw( $kfr );
                    $sOpenColls .= "<div class='$sClass'>$sC</div>";
                }
            }
        }
        if( $pCmd == 'new' ) {
            $sMyColls .= "<div class='well SEEDPopover SPop_collection_mine_new'>"
                        .$this->collectionForm( $oForm, $oForm->kfrel->CreateRecordCursor() )
                        ."</div>";
            $this->oSCA->sSPop = "collection_mine_new";
        } else if( $this->oSCA->sess->IsLogin() ) {
            if( !$sMyColls ) {
                $sMyColls .= "<div class='well SEEDPopover SPop_collection_none'>You don't have any seed collections here yet, but you can create one now.</div>";
                $this->oSCA->sSPop = "collection_none";
            }
            $sMyColls .= $this->button( "Add", 'collections', 'new' );
        } else {
            $sMyColls = "<div class='well'>Login to manage or create your own seed collections"
                       .SEEDSessionAccountUI_LittleLogin( $this->oSCA->sess )
                       ."</div>";
        }

        $s .= "<div style='margin:0 20%'>"
            ."<h3>My Seed Collections</h3>"
            .$sMyColls;
        if( $sOpenColls ) {
            $s .= "<h3>Other Seed Collections</h3>"
                 ."<p style='margin-left:20px'>These are collections that are publicly visible, or that the owner has granted you permission to see.</p>"
                 .$sOpenColls;
        }
        if( $nPrivateColls ) {
            $s .= "<h3>Private Seed Collections</h3>"
                 ."<p style='margin-left:20px'>There are also $nPrivateColls private seed collections that other people are using.</p>";
        }

        $s .= "</div>";

        return( $s );    }

    function collection_DSPreStore( SEEDDataStore $oDS )
    {
        if( ($kColl = $oDS->Key()) ) {
            // updating an existing record
            if( !$this->CanAdminCollection( $kColl ) )  return( false );    // you do not have permission to update this record - good job getting this far, hacker!
        } else {
            // Creating a new record.  Do the right thing about permclasses in PostStore.

// kluge: this is also preventing blank records from being saved - uid_owner below is defeating the blank-record check - use presetOnInsert parm on SEEDForm to do this correctly
            if( !$oDS->Value('name') )  return( false );
        }

        // If no one is the owner yet (regardless of whether this is a new record), then you are the owner
        if( !$oDS->Value('uid_owner') ) {
            $oDS->SetValue( 'uid_owner', $this->oSCA->sess->GetUID() );
        }

        return( true );
    }

    function collection_DSPostStore( KFRecord $kfr )
    {
        if( !$kfr->Key() || !$this->CanAdminCollection( $kfr->Key() ) )  return;

        if( !$kfr->Value('permclass') ) {
            // Create a new permclass for this collection.
            // By convention it is named SLCollection:kColl where kColl is a string value of the sl_collection._key
            $permclass = $this->oSCA->oPerms->CreatePermClass( "SLCollection", strval($kfr->Key()) );
            //$permclass = SEEDPermsStatic::CreatePermClass( $this->oSCA->kfdb, "SLCollection", strval($kfr->Key()) );
            $kfr->SetValue( 'permclass', $permclass );
            $kfr->PutDBRow();
        }

        /* Update the SEEDPerms for the friends who have R, W, A permissions
         */
        if( ($permclass = $kfr->Value('permclass')) ) {
            $this->updatePerms( $kfr->Value('perm_r'), $permclass, "R" );
            $this->updatePerms( $kfr->Value('perm_w'), $permclass, "W" );
            $this->updatePerms( $kfr->Value('perm_a'), $permclass, "A" );
        }
    }

    private function updatePerms( $pEmails, $permclass, $mode )
    {
        list($raOldUsers,$raOldGroups) = $this->oSCA->oPerms->GetUsersFromPermClass( $permclass, $mode );
        if( count($raOldGroups) ) {
            /* Cannot allow SEEDPerms groups:
             *     removing from a group is impossible;
             *     adding the given pEmails will result in adding all members of the group as individual users, because they're all listed
             */
            echo "Management of permissions for mode $mode has been blocked by a Perms Group.";
            return;
        }

        $raEmails = preg_split( '/\s+/', $pEmails );
        if( count($raEmails)==1 && empty($raEmails[0]) )  $raEmails = array();    // preg_split returns array('') from a blank input

        $raUsers = $this->oSCA->oUGP->GetKUserFromEmailRA( $raEmails, true );    // array of kUser=>email

        // Add friends who don't have the given perms
        foreach( $raEmails as $email ) {
            if( ($kUser = array_search( $email, $raUsers )) ) {
                // this is a real user
                $this->oSCA->oPerms->AddPermForUser( $kUser, $permclass, $mode );
            } else {
                echo "$email is not a real user";
            }
        }

        // Subtract friends whose perms are no longer in the given list
        foreach( $raOldUsers as $kUser ) {
            if( !isset($raUsers[$kUser]) ) {
                $this->oSCA->oPerms->RemovePermForUser( $kUser, $permclass, $mode );
            }
        }
    }

    private function button( $label, $pScreen, $pColl )
    {
        return( "<form method='post'>"
                //." onclick='location.replace(\"{$_SERVER['PHP_SELF']}?pMode=collections&pCmd=".$kfr->Key()."\");'/>"
               ."<input type='submit' value='$label'/>"
               ."<input type='hidden' name='pScreen' value='$pScreen'/>"
               ."<input type='hidden' name='pCmd' value='$pColl'/>"          // used by pScreen==collections
               .($pScreen=='seeds' ? "<input type='hidden' name='sfCp_selSC' value='$pColl'/>" : "")
               ."</form>" );
    }

    private function collectionDraw( $kfr )
    {
        $s = "";

        $bReadOnly = !$this->CanAdminCollection( $kfr->Key() );

        $button1 = $bReadOnly ? "Read Only" : $this->button( 'Edit', 'collections', $kfr->Key() );
        $button2 = $this->button( 'See the collection', 'seeds', $kfr->Key() );

        $sOwner = $this->oSCA->oUGP->GetEmail( $kfr->Value('uid_owner') );
        if( !$sOwner ) $sOwner = "(unknown)";

        $s .= "<div style='float:right'>$button1<br/><br/>$button2</div>"
             .$kfr->Expand(
                 "<b>[[name]]</b><br/>"
                ."Owner: $sOwner<br/>"
                ."Lot # prefix: [[inv_prefix]]<br/>"
                ."Next lot number: [[inv_counter]]<br/>" );

        $s .= "Visible to: ";
        switch( $kfr->Value('eReadAccess') ) {
            case 'PUBLIC':     $s .= "Everyone";   break;
            case 'COLLECTORS': $s .= "All other collectors";  break;
            default:           $s .= $bReadOnly ? "Designated friends (including you)" : "Just you and your friends"; break;
        }

        if( !$bReadOnly ) {
            // Users who can see this collection but can't admin it should probably not see the details of permissions
            $sFriendsR = $kfr->Value('permclass') ? nl2br($this->getUsersWithPerm($kfr->Value('permclass'),'R')) : "";
            $sFriendsW = $kfr->Value('permclass') ? nl2br($this->getUsersWithPerm($kfr->Value('permclass'),'W')) : "";
            $sFriendsA = $kfr->Value('permclass') ? nl2br($this->getUsersWithPerm($kfr->Value('permclass'),'A')) : "";

            $s .="<br/>Your friends: "
                 ."<table id='collection_perms' width='100%' border='0'><tr>"
                     ."<td valign='top'>Read: who can see my collection<br/><div>$sFriendsR</div></td>"
                     ."<td valign='top'>Edit: who can edit my collection<br/><div>$sFriendsW</div></td>"
                     ."<td valign='top'>Admin: who can edit this form<br/><div>$sFriendsA</div></td>"
                 ."</tr></table>";
        }

        if( $this->oSCA->IsAdmin ) {
            // only if you have UGP SLCollection=='A' or SL=='A'
            $s .= "<div style='margin:10px'><i><b>Admin</b></i><br/>"
                 ."<i>Permclass: ".$this->oSCA->oPermsTest->GetClassName( $kfr->Value('permclass'), true )."</i>"
                 ."</div>";
        }

        return( $s );
    }

    private function collectionForm( $oForm, $kfr )
    {
        if( !$kfr->Key() ) {
            // initialize new record
            $kfr->SetValue( 'inv_counter', 1 );
        }
        $oForm->SetKFR( $kfr );

        if( $kfr->Key() ) {
            $sOwner = $this->oSCA->oUGP->GetEmail( $kfr->Value('uid_owner') );
            if( !$sOwner ) $sOwner = "(unknown)";
        }

        $sAdmin = "";
        if( $this->oSCA->IsAdmin ) {
            $sOpts = array_merge( array("Create a new permclass" => 0),
                                  $this->oSCA->oPerms->GetRAClassesOpts("SLCollection", true) );
            $sAdmin = "||| <br/><b>Admin</b> || &nbsp;"
                     ."||| Permclass || ".$oForm->Select2( 'permclass', $sOpts )." || &nbsp;";
        }

        if( $kfr->Value('permclass') ) {
            $kfr->SetValue( 'perm_r', $this->getUsersWithPerm( $kfr->Value('permclass'), "R" ) );
            $kfr->SetValue( 'perm_w', $this->getUsersWithPerm( $kfr->Value('permclass'), "W" ) );
            $kfr->SetValue( 'perm_a', $this->getUsersWithPerm( $kfr->Value('permclass'), "A" ) );
        }

        $s = "<form method='post' action='{$_SERVER['PHP_SELF']}'>"
            ."<table border='0' style='width:100%'>"
            .$oForm->ExpandForm(
                "||| {width='30%'} Name  || {width='30%'} [[name]] || {width='30%'} &nbsp;"
               .($kfr->Key() ? "||| Owner || $sOwner || &nbsp;" : "")
               ."||| Lot # prefix || [[inv_prefix]] || &nbsp;"
               ."||| Next lot number || [[inv_counter|readonly]] || &nbsp;"
               ."||| Collection is visible to || ".$oForm->Select2( 'eReadAccess', array('Just you and your friends'=>'0',
                                                                                         'All registered collectors'=>'COLLECTORS',
                                                                                         'Everyone'=>'PUBLIC') )." || &nbsp;"
               ."||| &nbsp; || &nbsp; || &nbsp;"
               ."||| Your friends<br/><span style='font-size:8pt'>Read: who can see my collection</span><br/>[[textarea:perm_r|width:90%]] "
                   ."|| <br/><span style='font-size:8pt'>Edit: who can edit my collection</span><br/>[[textarea:perm_w|width:90%]] "
                   ."|| <br/><span style='font-size:8pt'>Admin: who can edit this form</span><br/>[[textarea:perm_a|width:90%]]"

               .$sAdmin
             )
            ."</table>"
            .$oForm->HiddenKey()
            //."<input type='hidden' name='pScreen' value='collections'/>" // $oForm->Hidden( 'pMode', 'collections' )  no this encodes sfCp_pMode
            ."<input type='submit' value='Save'/>"
            ."</form>";

        return( $s );
    }

    private function getUsersWithPerm( $permclass, $mode )
    /*****************************************************
        Get the userids who have the given perm mode on the given collection's permclass
     */
    {
        $s = "";

        $raUsers = SEEDSessionPerms_GetUseridsFromPermClass( New_SiteAppDB(), $permclass, $mode, true );

        foreach( $raUsers as $uid => $raU ) {
            $s .= $raU['email']."\n";
        }

        return( $s );
    }
}

?>