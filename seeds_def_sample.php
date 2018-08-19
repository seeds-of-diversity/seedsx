<?php
/* seeds def
 *
 * Copy and edit this file to create the SITE_DB_DEF_FILE for your site.
 * It normally goes in the directory above the SITEROOT.
 * There is a different file name for each site's SITE_DB_DEF_FILE to allow them to co-exist in multi-site development environments.
 *
 * seeds1 is the main public site www.seeds.ca  (accessible by SEEDS.CA and OFFICE)
 * seeds2 is the office site office.seeds.ca   (accessible only by OFFICE)
 * seeds3 is the pollinator.ca site   (accessible only by POLLINATOR)
 *
 * Instructions:
 * 1) Set SEEDS_DEF_SITE_NAME to one of the options shown
 * 2) Change your db password in the applicable password lines, or just use the default if this is a development environment (it probably is)
 */

/*********************************************************************************************
 *  Here's the area where you edit
 *
 *  1)  Define SEEDS_DEF_SITE_NAME to one of these options (uncomment one of them)
 */
//define( "SEEDS_DEF_SITE_NAME", "SEEDS.CA" );
//define( "SEEDS_DEF_SITE_NAME", "OFFICE" );
//define( "SEEDS_DEF_SITE_NAME", "POLLINATOR" );

/* 2)  Set your DB password for the database(s) that this site uses
 */
if( SEEDS_DEF_SITE_NAME == "SEEDS.CA" || SEEDS_DEF_SITE_NAME == "OFFICE" ) {
    define("SiteKFDB_PASSWORD_seeds1", "seeds");   // edit here if this is SEEDS.CA or OFFICE
}
if( SEEDS_DEF_SITE_NAME == "OFFICE" ) {
    define("SiteKFDB_PASSWORD_seeds2", "seeds");  // edit here if this is OFFICE
}
if( SEEDS_DEF_SITE_NAME == "POLLINATOR" ) {
    define("SiteKFDB_PASSWORD_seeds3", "seeds");  // edit here if this is POLLINATOR
}
if( SEEDS_DEF_SITE_NAME == "SEEDS.CA" ) {
    define("SiteKFDB_HOST_seedliving",     "localhost");
    define("SiteKFDB_DB_seedliving",       "seedliving");  // edit here to set up seeds.ca/seedliving
    define("SiteKFDB_USERID_seedliving",   "seedliving");  // and here
    define("SiteKFDB_PASSWORD_seedliving", "seeds");       // and here
}

/* Don't edit below here
 *********************************************************************************************
 */

if( SEEDS_DEF_SITE_NAME == "SEEDS.CA" || SEEDS_DEF_SITE_NAME == "OFFICE" ) {
    define("SiteKFDB_HOST_seeds1",     "localhost");
    define("SiteKFDB_DB_seeds1",       "seeds");
    define("SiteKFDB_USERID_seeds1",   "seeds");
}
if( SEEDS_DEF_SITE_NAME == "OFFICE" ) {
    define("SiteKFDB_HOST_seeds2",     "localhost");
    define("SiteKFDB_DB_seeds2",       "seeds2");
    define("SiteKFDB_USERID_seeds2",   "seeds2");
}
if( SEEDS_DEF_SITE_NAME == "POLLINATOR" ) {
    define("SiteKFDB_HOST_seeds3",     "localhost");
    define("SiteKFDB_DB_seeds3",       "seeds3");
    define("SiteKFDB_USERID_seeds3",   "seeds3");
}


/* Set the default database for this site
 */
if( SEEDS_DEF_SITE_NAME == "SEEDS.CA" ) {
    define("SiteKFDB_HOST",     SiteKFDB_HOST_seeds1);
    define("SiteKFDB_DB",       SiteKFDB_DB_seeds1);
    define("SiteKFDB_USERID",   SiteKFDB_USERID_seeds1);
    define("SiteKFDB_PASSWORD", SiteKFDB_PASSWORD_seeds1);
}
if( SEEDS_DEF_SITE_NAME == "OFFICE" ) {
    define("SiteKFDB_HOST",     SiteKFDB_HOST_seeds2);
    define("SiteKFDB_DB",       SiteKFDB_DB_seeds2);
    define("SiteKFDB_USERID",   SiteKFDB_USERID_seeds2);
    define("SiteKFDB_PASSWORD", SiteKFDB_PASSWORD_seeds2);
}
if( SEEDS_DEF_SITE_NAME == "POLLINATOR" ) {
    define("SiteKFDB_HOST",     SiteKFDB_HOST_seeds3);
    define("SiteKFDB_DB",       SiteKFDB_DB_seeds3);
    define("SiteKFDB_USERID",   SiteKFDB_USERID_seeds3);
    define("SiteKFDB_PASSWORD", SiteKFDB_PASSWORD_seeds3);
}


// https://ssl.peaceworks.ca/svn/
?>
