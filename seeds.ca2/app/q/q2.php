<?php

/* This is the same as index.php but the user is authenticated on seeds2.SEEDSession.
 * Since sessions are not portable across seeds1 and seeds2, use this if you are launching a Q app while logged into seeds2. (if the app requires auth)
 */

define( "SITEROOT", "../../" );
include_once( SITEROOT."site2.php" );

define( "Q_DB", 'seeds2' );
include( "_q.php" );

?>