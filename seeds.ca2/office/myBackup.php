<?php

define("SITEROOT","/home/seeds/public_html/");
include(SITEROOT."site2.php");

$pass1 = SiteKFDB_PASSWORD_seeds1;
$pass2 = SiteKFDB_PASSWORD_seeds2;
$dir      = "/home/seeds/_back1";
$date     = date("ymd");

// file_prefix => tables to be saved to file

$raTables1 = array( "ev"     => array( "ev_events" ),
                    "rl"     => array( "rl_companies" ),
                    "bull"   => array( "bull_list" ),
                    "csci"   => array( "csci_seeds", "csci_company", "csci_seeds_archive" ),
                    "doclib" => array( "doclib_document" ),
                    "sed"    => array( "sed_growers", "sed_seeds" ),
                    "sedcurr" => array( "sed_curr_growers", "sed_curr_seeds" ),
                    "docrep1_"  => array( "docrep_docs", "docrep_docdata", "docrep_docxdata" ),
                    "mbrorder" => array( "mbr_order_pending" ),
                    "pollcan" => array( "pollcan_flowers", "pollcan_insects", "pollcan_insectsxflowers", "pollcan_sites", "pollcan_users", "pollcan_visits"),
                    "sl"      => array( "sl_collection", "sl_accession", "sl_inventory", "sl_adoption", "sl_germ",
                                        "sl_species", "sl_species_syn", "sl_species_map", "sl_pcv", "sl_pcv_syn", "sl_pcv_meta" ),
                    "sldesc"  => array( "mbr_sites", "sl_varinst", "sl_desc_obs", "sl_desc_cfg_forms", "sl_desc_cfg_tags", "sl_desc_cfg_m" ),
                    "slsources" => array( "sl_sources" ),
                    "slcvsrc" => array( "sl_cv_sources" ),
                    "slcvsrcarch" => array( "sl_cv_sources_archive" ),
                    "SEEDLocal1_" => array( "SEEDLocal" ),
                    "SEEDMetaTable1_" => array( "SEEDMetaTable_StringBucket", "SEEDMetaTable_TablesLite", "SEEDMetaTable_TablesLite_Rows" ),
                    "SEEDPerms1_" => array( "SEEDPerms", "SEEDPerms_Classes" ),
                    "SEEDSession1_" => array( "SEEDSession_Users", "SEEDSession_Groups", "SEEDSession_UsersXGroups", "SEEDSession_Perms" ),
                    "SEEDBasket" => array( "SEEDBasket_Baskets", "SEEDBasket_Products", "SEEDBasket_ProdExtra", "SEEDBasket_BP" ),
);

$raTables2 = array( "mbr" => array( "mbr_contacts", "mbr_donations" ),
                    "mbrmail" => array( "mbr_mail_send", "mbr_mail_send_recipients" ),
                    "gcgc" => array( "gcgc_growers", "gcgc_varieties", "gcgc_gxv" ),
                    "tasks" => array( "task_tasks" ),
                    "docrep2_"  => array( "docrep_docs", "docrep_docdata", "docrep_docxdata" ),
                    "SEEDLocal2_" => array( "SEEDLocal" ),
                    "SEEDMetaTable2_" => array( "SEEDMetaTable_StringBucket", "SEEDMetaTable_TablesLite", "SEEDMetaTable_TablesLite_Rows" ),
                    "SEEDPerms2_" => array( "SEEDPerms", "SEEDPerms_Classes" ),
                    "SEEDSession2_" => array( "SEEDSession_Users", "SEEDSession_Groups", "SEEDSession_UsersXGroups", "SEEDSession_Perms" ),
);


/*******************************************************************************************************************
    Begin transfer section
 */
/*
$raDump1a = array_merge(
            $raTables1['docrep1_'],
            $raTables1['SEEDLocal1_'],
            $raTables1['SEEDMetaTable1_'],
            $raTables1['SEEDPerms1_'],
            $raTables1['SEEDSession1_'],
            array("SEEDSession")
);
$raDump1b = array_merge(
            $raTables1['ev'],
            $raTables1['rl'],
            $raTables1['doclib'],
            $raTables1['pollcan'],
            $raTables1['bull'],
            $raTables1['mbrorder'],
            array('g2011',
                  'hpd_species',
                  'hvd_catlist',
                  'hvd_onames',
                  'hvd_pnames',
                  'hvd_refs',
                  'hvd_sodclist',
                  'hvd_sourcelist',
                  'hvd_species' )
);
$raDump1c = array_merge(
            $raTables1['csci'],
            $raTables1['sed'],
            $raTables1['sedcurr'],
            $raTables1['sl'],
            $raTables1['sldesc'],
            $raTables1['slsources']
);
$raDump2a = array_merge(
            $raTables2['docrep2_'],
            $raTables2['SEEDLocal2_'],
            $raTables2['SEEDMetaTable2_'],
            $raTables2['SEEDPerms2_'],
            $raTables2['SEEDSession2_'],
            array("SEEDSession")

);
$raDump2b = array_merge(
            $raTables2['mbr'],
            $raTables2['mbrmail'],
            $raTables2['gcgc'],
            $raTables2['tasks']
);


// Transfers to newsite
$sTransfer = "\n\nTransfers to newsite:\n"
     .dumpTable( $raDump1a, "seeds", $pass1, "seeds", "/home/seeds2/public_html/transfer_1a.sql" )
     ."\n"
     .dumpTable( $raDump1b, "seeds", $pass1, "seeds", "/home/seeds2/public_html/transfer_1b.sql" )
     ."\n"
     .dumpTable( $raDump1c, "seeds", $pass1, "seeds", "/home/seeds2/public_html/transfer_1c.sql" )
     ."\n"
     .dumpTable( $raDump2a, "seeds2", $pass2, "seeds2", "/home/seeds2/public_html/transfer_2a.sql" )
     ."\n"
     .dumpTable( $raDump2b, "seeds2", $pass2, "seeds2", "/home/seeds2/public_html/transfer_2b.sql" );

*/
/*******************************************************************************************************************
    End transfer section
 */



/* Dump all tables of seeds1 and seeds2
*/
$sCommands =
     dumpTables( $raTables1, "seeds", $pass1, "seeds" )
    ."\n"
    .dumpTables( $raTables2, "seeds", $pass1, "seeds2" )
    ."\n";


/* Compare new db dump tables with past, and delete if no change
 */
$sChanged = "";
$sDel = "";
foreach( array_merge( $raTables1, $raTables2 ) as $fprefix => $ratab ) {
    $f1short = $fprefix.$date.".sql";
    $f1 = $dir."/".$f1short;
    $bDel = false;
    if( !($od = opendir( $dir )) ) {
        die( "Cannot open $dir to check for duplicates" );
    }
    while( ($od_file = readdir($od)) !== false ) {
        $f2 = $dir."/".$od_file;
        $nPrefix = strlen($dir)+1+strlen($fprefix);
        if( $f1 != $f2 &&
            substr( $f1, 0, $nPrefix ) == substr( $f2, 0, $nPrefix ) &&
            is_numeric( substr( $f1, $nPrefix, 6 ) ) &&
            is_numeric( substr( $f2, $nPrefix, 6 ) ) )
        {
            // exec returns output to second argument, which is passed by reference so it has to be defined and real.
            // This can overflow PHP memory if a large file changes substantially, so output is redirected to /dev/null
            $raDummy = array();
            $iRet = 0;
            exec( "diff -I \"-- Dump completed on *\" $f1 $f2 > /dev/null", $raDummy, $iRet );
            if( $iRet == 0 ) {
                $sDel .= exec("unlink $f1")." $f1short duplicates $od_file\n";
                $bDel = true;
                break;
            }
        }
    }
    if( !$bDel ) {
        $sChanged .= "$f1short has new changes\n";
    }
}
$s = "Changed Tables\n\n"
    .$sChanged
    ."<hr/>\n"
    ."No Changes\n\n"
    .$sDel
    ."<hr/>\n"
    ."<pre>$sCommands</pre>"
    ."<hr/>\n";

//$s .= $sTransfer;

echo nl2br($s);   // to see this on a web browser, the backup directory has to be world-readable-writable
mail( "bob@seeds.ca", "myBackup", $s );





/*  See scripts/mysqldump_charsafe and scripts/mysql_charsafe_import

    See http://docforge.com/wiki/Mysqldump

    mysqldump -u USER --default-character-set=latin1 -N --result-file=file

        1) default charset latin1 with -N (do not write SET NAMES command) forces no charset conversion to the output
        2) result-file bypasses any charset weirdness in the shell (i.e. don't use shell redirection to store the file)

    mysql -u USER -D DB --default-character-set=latin1

        3) the absence of SET NAMES and default charset latin1 forces no charset conversion on input

        You can do this with "mysql --default-character-set=latin1 < file" or
        using the source command if you use that option on the client command.

    It would be a good idea to explicitly set the charset on the tables, so we aren't relying on the default table
    charsets to be the same.
 */



function dumpTables( $raTables, $userid, $password, $db )
{
    global $date, $dir;

    $s = "";
    foreach( $raTables as $fprefix => $ratab ) {
        $s .= dumpTable( $ratab, $userid, $password, $db, "$dir/$fprefix$date.sql" );
    }
    return( $s );
}

function dumpTable( $ratab, $userid, $password, $db, $file )
{
    $cmd = "/usr/bin/mysqldump -u $userid [pass] --default-character-set=latin1 -N --result-file=$file $db ". implode( " ", $ratab );
    $iRet = NULL;
    system( str_replace( "[pass]", "--password=$password", $cmd ), $iRet );
    $s = $iRet." : ".$cmd."\n";

    return( $s );
}


exit;


/* Back up all log files, and other files
 */
$cmd = "/bin/tar -czf $dir/log$date.taz /home/seeds/public_html/log/*";
system( $cmd, $sRet );
echo $sRet." : ".$cmd."<BR>";

$cmd = "/bin/tar -czf $dir/seeds_log$date.taz /home/seeds/seeds_log/*";
system( $cmd, $sRet );
echo $sRet." : ".$cmd."<BR>";

$cmd = "/bin/tar -czf $dir/log$date.taz /home/seeds/public_html/log/*";
$cmd = "/bin/cp /home/seeds/public_html/.htaccess $dir/htaccess$date";
system( $cmd, $sRet );
echo $sRet." : ".$cmd."<BR>";

echo "</PRE><HR/>";

?>
