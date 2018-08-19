<?

/* CRON calls this indirectly @daily.
 * Since the cron user does not have all the php modules that the httpd process does, cron calls cron.php which sends an http request here
 * to accomplish tasks that require e.g. database access.
 */


mail( "bob@seeds.ca", "cronwww.php executed on office", "" );

?>
