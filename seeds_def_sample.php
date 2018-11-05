<?php

/* seeds def
 *
 * Copy and edit this file to create the SITE_DB_DEF_FILE for your site.
 * It goes in the CONFIG_DIR (normally ~/_config)
 *
 * Change your db password in the applicable password lines, or just use the default if this is a development environment (it probably is)
 */

// old way
define("SiteKFDB_HOST_seeds1",     "localhost");
define("SiteKFDB_USERID_seeds1",   "seeds");
define("SiteKFDB_PASSWORD_seeds1", "seeds");   // edit here
define("SiteKFDB_DB_seeds1",       "seeds");

define("SiteKFDB_HOST_seeds2",     "localhost");
define("SiteKFDB_USERID_seeds2",   "seeds");
define("SiteKFDB_PASSWORD_seeds2", "seeds");   // edit here
define("SiteKFDB_DB_seeds2",       "seeds2");

define("SiteKFDB_HOST_seeds3",     "localhost");
define("SiteKFDB_USERID_seeds3",   "seeds");
define("SiteKFDB_PASSWORD_seeds3", "seeds");   // edit here
define("SiteKFDB_DB_seeds3",       "seeds3");

define("SiteKFDB_HOST_seeds4",     "localhost");
define("SiteKFDB_USERID_seeds4",   "seeds");
define("SiteKFDB_PASSWORD_seeds4", "seeds");   // edit here
define("SiteKFDB_DB_seeds4",       "seeds4");

define("SiteKFDB_HOST_seedliving",     "localhost");
define("SiteKFDB_USERID_seedliving",   "seedliving");
define("SiteKFDB_PASSWORD_seedliving", "seeds");
define("SiteKFDB_DB_seedliving",       "seedliving");

define("SiteKFDB_HOST_phpbb",      "localhost");
define("SiteKFDB_USERID_phpbb",    "seeds");
define("SiteKFDB_PASSWORD_phpbb",  "seeds");
define("SiteKFDB_DB_phpbb",        "seeds4");

define( "SiteKFDB_HOST_floralcal",     "localhost");
define( "SiteKFDB_USERID_floralcal",   "seeds" );
define( "SiteKFDB_PASSWORD_floralcal", "seeds" );
define( "SiteKFDB_DB_floralcal",       "seeds3" );

/* Defaults for your current site
 */
define("SiteKFDB_HOST",     SiteKFDB_HOST_seeds1);
define("SiteKFDB_DB",       SiteKFDB_DB_seeds1);
define("SiteKFDB_USERID",   SiteKFDB_USERID_seeds1);
define("SiteKFDB_PASSWORD", SiteKFDB_PASSWORD_seeds1);

/* Or use this
define("SiteKFDB_HOST",     SiteKFDB_HOST_seeds2);
define("SiteKFDB_DB",       SiteKFDB_DB_seeds2);
define("SiteKFDB_USERID",   SiteKFDB_USERID_seeds2);
define("SiteKFDB_PASSWORD", SiteKFDB_PASSWORD_seeds2);
*/
/* Or use this
define("SiteKFDB_HOST",     SiteKFDB_HOST_seeds3);
define("SiteKFDB_DB",       SiteKFDB_DB_seeds3);
define("SiteKFDB_USERID",   SiteKFDB_USERID_seeds3);
define("SiteKFDB_PASSWORD", SiteKFDB_PASSWORD_seeds3);
*/

// new way
$config_KFDB = array(
    'seeds1'     => array( 'kfdbUserid'   => SiteKFDB_USERID,
                           'kfdbPassword' => SiteKFDB_PASSWORD,
                           'kfdbDatabase' => 'seeds' ),

    'seeds2'     => array( 'kfdbUserid'   => SiteKFDB_USERID,
                           'kfdbPassword' => SiteKFDB_PASSWORD,
                           'kfdbDatabase' => 'seeds2' ),

    'seeds3'     => array( 'kfdbUserid'   => SiteKFDB_USERID,
                           'kfdbPassword' => SiteKFDB_PASSWORD,
                           'kfdbDatabase' => 'seeds3' ),

    'seeds4'     => array( 'kfdbUserid'   => SiteKFDB_USERID,
                           'kfdbPassword' => SiteKFDB_PASSWORD,
                           'kfdbDatabase' => 'seeds4' ),

    'phpbb'      => array( 'kfdbUserid'   => SiteKFDB_USERID,
                           'kfdbPassword' => SiteKFDB_PASSWORD,
                           'kfdbDatabase' => 'seeds4' ),

    'seedliving' => array( 'kfdbUserid'   => SiteKFDB_USERID,
                           'kfdbPassword' => SiteKFDB_PASSWORD,
                           'kfdbDatabase' => 'seedliving' ),

    'floralcal'  => array( 'kfdbUserid'   => SiteKFDB_USERID,
                           'kfdbPassword' => SiteKFDB_PASSWORD,
                           'kfdbDatabase' => 'seeds3' ),
);

?>
