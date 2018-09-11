<?php
/* seeds def
 *
 * Copy and edit this file to create the SITE_DB_DEF_FILE for your site.
 * It normally goes in the directory above the SITEROOT.
 * There is a different file name for each site's SITE_DB_DEF_FILE to allow them to co-exist in multi-site development environments.
 */

// 1) Uncomment the database definitions used by this site, and enter the password(s).
//      seeds1 is the main public site www.seeds.ca
//      seeds2 is the office site office.seeds.ca
//      seeds3 is the pollinator.ca site

define("SiteKFDB_HOST_seeds1",     "localhost");
define("SiteKFDB_DB_seeds1",       "seeds");
define("SiteKFDB_USERID_seeds1",   "seeds");
define("SiteKFDB_PASSWORD_seeds1", "seeds");   // edit here
/*
define("SiteKFDB_HOST_seeds2",     "localhost");
define("SiteKFDB_DB_seeds2",       "seeds2");
define("SiteKFDB_USERID_seeds2",   "seeds2");
define("SiteKFDB_PASSWORD_seeds2", "YOUR OFFICE DB PASSWORD");  // edit here
*/
/*
define("SiteKFDB_HOST_seeds3",     "localhost");
define("SiteKFDB_DB_seeds3",       "seeds3");
define("SiteKFDB_USERID_seeds3",   "seeds3");
define("SiteKFDB_PASSWORD_seeds3", "YOUR POLLINATOR.CA DB PASSWORD");   // edit here
*/


// 2) Set the default database for this site.
//      for www.seeds.ca, leave it alone
//      for office, change seeds1 to seeds2
//      for pollinator, change seeds1 to seeds3

define("SiteKFDB_HOST",     SiteKFDB_HOST_seeds1);
define("SiteKFDB_DB",       SiteKFDB_DB_seeds1);
define("SiteKFDB_USERID",   SiteKFDB_USERID_seeds1);
define("SiteKFDB_PASSWORD", SiteKFDB_PASSWORD_seeds1);


// https://ssl.peaceworks.ca/svn/
?>
