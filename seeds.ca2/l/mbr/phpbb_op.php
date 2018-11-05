<?php

// Included inline in phpbb so the code is in scope.
// Put this in forum/index.php after the setup stuff:
//      include( "../l/mbr/phpbb_op.php" );
//
// It's hard to use much of the SoD codebase here because phpbb prevents access to any superglobals

/* Note that this is not authenticated so commands should not reveal any secrets or be able to do anything abnormal
 */
if( ($pCmd = $request->variable('seeds-admin','')) ) {

    include( "../../_config/seeds_def1.php" );
    include( "../../seeds/Keyframe/KeyframeDB.php" );
    $kfdb = new KeyFrameDatabase( SiteKFDB_USERID, SiteKFDB_PASSWORD, SiteKFDB_HOST );
    $kfdb->Connect( "seeds" ) or die( "kfdb connection failed" );

    $kfdb_bb = new KeyFrameDatabase( SiteKFDB_USERID_phpbb, SiteKFDB_PASSWORD_phpbb, SiteKFDB_HOST_phpbb );
    $kfdb_bb->Connect( SiteKFDB_DB_phpbb ) or die( "kfdb_bb connection failed" );

    require($phpbb_root_path ."includes/functions_user.php");


    if( $pCmd == 'update-users' ) {
        /* Copy new members into phpbb_users
         */
        $nAdded = 0;

        $raUsers = $kfdb->QueryRowsRA( "SELECT * FROM seeds.SEEDSession_Users WHERE eStatus='ACTIVE' AND gid1 IN (1,2,4)" );
        foreach( $raUsers as $ra ) {
            $bb_id = intval( $kfdb->Query1( "SELECT v FROM seeds.SEEDSession_UsersMetadata WHERE _status='0' AND uid='{$ra['_key']}' AND k='phpbb_id'" ) );

            if( $bb_id )  continue;

            $uid = $ra['_key'];
            $uname = "member".$uid;

            $user_row = array(
                'username'              => $uname, //$ra['realname'],
                'user_password'         => phpbb_hash($ra['password']),
                'user_email'            => $ra['email'],
                'group_id'              => 2, // by default, the REGISTERED user group is id 2
                //'user_timezone'         => (float) $data['tz'],
                'user_lang'             => ($ra['lang'] == 'F' ? "fr" : "en"),
                'user_type'             => USER_NORMAL,
                //'user_ip'               => $user->ip,     this is the script's user ip, not the new user's ip
                'user_regdate'          => time(),
            );

            if( ($phpbb_userid = user_add( $user_row )) ) {
                $groupid = 8;	// SoDMembers

                if( ($sErr = group_user_add( $groupid, $phpbb_userid )) ) {
	            echo "<div style='color:red'>Warning adding $uname to group $groupid: $sErr</div>";
                }
                $kfdb->Execute( "DELETE FROM seeds.SEEDSession_UsersMetadata WHERE uid='$uid' AND k='phpbb_id'");
                $kfdb->Execute( "INSERT INTO seeds.SEEDSession_UsersMetadata (_created,_updated,uid,k,v) VALUES (NOW(),NOW(),$uid,'phpbb_id',$phpbb_userid)" );
                echo "Added $phpbb_userid : {$user_row['username']} {$user_row['user_email']}<br/>";
                ++$nAdded;
            }
if( $nAdded == 50 ) exit;
        }
        echo "Added $nAdded members";
    }

    if( $pCmd == 'quser' ) {
        if( !($pUser = $request->variable('seeds-uid','')) ) {
            echo "Specify seeds-uid";
            exit;
        }

        if( is_numeric($pUser) ) {
            $raUser = $kfdb->QueryRA( "SELECT * FROM seeds.SEEDSession_Users WHERE _key='".intval($pUser)."'" );
        } else {
            $raUser = $kfdb->QueryRA( "SELECT * FROM seeds.SEEDSession_Users WHERE email='".addslashes($pUser)."'" );
        }
        if( !($kUser = @$raUser['_key']) ) {
            echo "Unknown user $pUser";
            exit;
        }
        $kBB = intval( $kfdb->Query1( "SELECT v FROM seeds.SEEDSession_UsersMetadata WHERE uid='$kUser' AND k='phpbb_id'" ) );

        echo "Member $kUser, forum id $kBB<br/>";

        if( $kBB ) {
            $raBB = $kfdb_bb->QueryRA( "SELECT * FROM phpbb_users WHERE user_id='$kBB'" );

            echo "Username ".@$raBB['username']."</br/>";

            echo "Password is "
                .( $raBB['user_password'] == phpbb_hash($raUser['password']) ? "the same as" : "different than")
                ." SoD</br/>";
        }
     }

    exit;
}


/* Also modified:
 *
   prosilver/template/ucp_agreement.html : replaced the agreement text and buttons with a message that this is for members and see the store for more information
   prosilver/theme/imageset.css : replaced the phpbb logo
        .imageset.site_logo {
            //background-image: url("./images/site_logo.gif");
            background-image: url("//seeds.ca/i/img/logo/img/logoA_h-en-300x.png");
            background-size: 100% 95%;
            background-color: #eee;
            border-radius: 5px;
 */

?>
