<?
define( "SITEROOT", "../../" );
include_once( SITEROOT."site.php" );
include_once( SITEINC."sitedb.php" );
include_once( HPD_ROOT."_csci.php" );
include_once( SITEINC ."sodlogin.php" );

$la = new SoDLoginAuth;
if( !$la->SoDLoginAuth_Authenticate( $_REQUEST, "W csci" ) ) { exit; }

$p_catid   = BXStd_SafeGPCGetInt( "catid" );
$p_gpc_psp = BXStd_SafeGPCGetStr( "psp" );
$p_gpc_osp = BXStd_SafeGPCGetStr( "osp" );
$bExtra    = BXStd_SafeGPCGetInt( "extra" );

if( empty($p_gpc_osp['plain']) )  $p_gpc_osp = $p_gpc_psp;



/* insert rows
 */
if( $p_catid && !empty( $p_gpc_osp['plain'] ) ) {

    for( $i = 0; $i < 10; $i++ ) {
        $p_gpc_on = BXStd_SafeGPCGetStr( "on$i" );      // oname
        if( empty($p_gpc_on['plain']) ) continue;

        $p_gpc_pn = BXStd_SafeGPCGetStr( "pn$i" );      // pname
        $p_gpc_sx = BXStd_SafeGPCGetStr( "sx$i" );      // pspecies_ex

        if( empty($p_gpc_pn['plain']) )  $p_gpc_pn = $p_gpc_on;

        $vals = " $p_catid,'{$p_gpc_psp['db']}','{$p_gpc_sx['db']}','{$p_gpc_osp['db']}','{$p_gpc_pn['db']}','{$p_gpc_on['db']}'";

        $q = "INSERT INTO cat_item (_key,_created,_created_by,_updated,_updated_by,_status,".
                                   " cat_catalog_id,pspecies,pspecies_ex,ospecies,pname,oname) ".
                           "VALUES (NULL,NOW(),".$la->LoginAuth_UID().",NOW(),".$la->LoginAuth_UID().",0, $vals)";

        if( $key = db_insert_autoinc_id( $q ) ) {
            echo "Inserted $vals at row $key<BR>";
        } else {
            echo "Error inserting $vals<BR>";
        }

    }
}


/* Set parms
 */

echo "<STYLE>.parm { margin-bottom: 10px; }</STYLE>";

echo "<FORM method='post'>";

    echo "<H2>Add New Varieties from a Catalogue</H2>";
    echo "<DIV class='parm'>Catalogue: <SELECT name='catid'>";
    $dbc = db_open( "SELECT R.name_en AS name,C._key AS catid FROM rl_companies R LEFT JOIN cat_catalog C ON (R.rl_cmp_id = C.cat_company_id) WHERE R._disabled=0 AND R.country='Canada' ORDER BY R.name_en" );
    while( $ra = db_fetch( $dbc ) ) {
        $catid = intval( $ra['catid'] );

        if( $catid ) {
            echo "<OPTION value='$catid'".($p_catid==$catid ? " SELECTED" : "").">{$ra['name']}</OPTION>";
        }
    }
    echo "</SELECT></DIV>";


    echo "<DIV class='parm'>Index Species: <INPUT type=text name='psp' value='{$p_gpc_psp['plain']}'></DIV>";
    echo "<DIV class='parm'>Original Species: <INPUT type=text name='osp' value='{$p_gpc_osp['plain']}'></DIV>";


    echo "<DIV class='parm'>Show extra fields <INPUT type=checkbox name=extra value=1 ".($bExtra ? " CHECKED" : "")."></DIV>";


    echo "<DIV align=center><INPUT type=submit value=Save></DIV><BR>";


/* Fields to enter new names
 */
echo "<TABLE border=0>";
echo "<TR><TH>Original Name</TH>";
if( $bExtra ) {
    echo "<TH>Index Name</TH><TH>Ex</TH>";
}
echo "</TR>";
for( $i = 0; $i < 10; $i++ ) {
    echo "<TR>";
    echo "<TD><INPUT type=text name='on$i'></TD>";
    if( $bExtra ) {
        echo "<TD><INPUT type=text name='pn$i'></TD>";
        echo "<TD><INPUT type=text name='sx$i'></TD>";
    }
    echo "<TR>";
}
echo "</TABLE>";



    echo "<DIV align=center><INPUT type=submit value=Save></DIV><BR>";

echo "</FORM>";

exit;


$mode = BXStd_SafeGPCGetInt( "mode" );
$bExtra = BXStd_SafeGPCGetInt( "extra" );
$bChgd  = BXStd_SafeGPCGetInt( "chgd" );











function link_catalog( $cmpName, $catid )
{
    return( "<A HREF='${_SERVER['PHP_SELF']}?mode=1&catid=$catid'>$cmpName</A>" );
}

function link_species( $spPlain )
{
    return( "<A HREF='${_SERVER['PHP_SELF']}?mode=2&species=".urlencode($spPlain)."'>$spPlain</A>" );
}

function link_mainlist()
{
    echo "<P><A HREF='${_SERVER['PHP_SELF']}'>Back to Species/Company List</A></P>";
}


















if( $mode == 0 ) {
    // Entry.  Drill down on company or species.

    echo "<H2>Select a Species</H2>";
    $dbc = db_open( "SELECT pspecies AS sp FROM cat_item WHERE _status=0 GROUP BY pspecies ORDER BY pspecies" );
    while( $ra = db_fetch( $dbc ) ) {
        echo link_species( $ra['sp'] );
        echo "<BR>";
    }

    echo "<H2>Select a Catalog</H2>";
    $dbc = db_open( "SELECT R.name_en AS name,C._key AS catid FROM rl_companies R LEFT JOIN cat_catalog C ON (R.rl_cmp_id = C.cat_company_id) WHERE R._disabled=0 AND R.country='Canada' ORDER BY R.name_en" );
    while( $ra = db_fetch( $dbc ) ) {
        $catid = intval( $ra['catid'] );

        if( $catid ) {
            echo link_catalog( $ra['name'], $catid );
        } else {
            echo $ra['name'];
        }
        echo "<BR>";
    }
} else {
    /* mode==1: Company
     * mode==2: Species
     *
     * Process any updates.  Species and Company updaters use the same method.
     *  pn{_key} is the new pname of _key
     *  on{_key}     "      oname       "
     *  psx{_key}    "      pspecies_ex "
     *
     * Note that these parms are expressed independently, so they are processed independently
     */
    foreach( $_REQUEST as $k => $v ) {
//  echo $k." ".$v." ".substr($k,0,2)." ".substr($k,2)."<BR>";
        if( empty($v) ) continue;

        $v = BXStd_MagicAddSlashes( $v );

        if( substr($k,0,3) == 'psx' ) {
            $col = "pspecies_ex";
            $key = intval( substr($k,3) );
        } else if( substr($k,0,2) == 'pn' ) {
            $col = "pname";
            $key = intval( substr($k,2) );
        } else if( substr($k,0,2) == 'on' ) {
            $col = "oname";
            $key = intval( substr($k,2) );
        } else {
            continue;
        }

//  Should be a static function that can compose the correct UPDATE statement based on this.
//  But it would have to do some work to figure out the type of $col to know whether to put quotes around $v
//
//  dbPhrameRecordUpdate( $CSCI_Item_Recorddef, $la->LoginAuth_UID(), $key, array( $col => $v ) );

        $q = "UPDATE cat_item SET $col='$v',_updated=NOW(),_updated_by=".$la->LoginAuth_UID()." WHERE _key=$key";
        if( db_exec( $q ) ) {
            echo "Updated $col '$v' on row $key<BR>";
        } else {
            echo "Error updating $col '$v' on row $key<BR>";
        }
    }
    link_mainlist();

    if( $mode == 1 ) {
        $catid = BXStd_SafeGPCGetInt( "catid" );
    } else {
        $sSpecies = BXStd_SafeGPCGetStr( "species" );
    }


    $sSearch = BXStd_SafeGPCGetStrPlain('srch');
    echo "<FORM method=post><DIV align=right>";
    if( $la->LoginAuth_UID() == 1 ) {
        echo "Extra: <INPUT type=checkbox name=extra value=1 ".($bExtra ? " CHECKED" : "")."> ";
        echo "Changed: <INPUT type=checkbox name=chgd value=1 ".($bChgd ? " CHECKED" : "")."> ";
    }
    echo "Search: <INPUT type=text name=srch value='".htmlspecialchars($sSearch,ENT_QUOTES)."' size=20> ";
    echo "<INPUT type=submit></DIV>";
    echo "<INPUT type=hidden name=mode value=$mode>";
    if( $mode == 1 ) {
        echo "<INPUT type=hidden name=catid value='$catid'>";
    } else {
        echo "<INPUT type=hidden name=species value='".htmlspecialchars($sSpecies['plain'],ENT_QUOTES)."'>";
    }
    echo "</FORM>";


    echo "<FORM action='${_SERVER['PHP_SELF']}' method='post'>";
    echo "<INPUT type=hidden name=srch value='".htmlspecialchars($sSearch,ENT_QUOTES)."'>";
    echo "<INPUT type=hidden name=mode value=$mode>";
    echo "<INPUT type=hidden name=extra value=$bExtra>";
    echo "<INPUT type=hidden name=chgd value=$bChgd>";
    if( $mode == 1 ) {
        echo "<INPUT type=hidden name=catid value='$catid'>";
    } else {
        echo "<INPUT type=hidden name=species value='".htmlspecialchars($sSpecies['plain'],ENT_QUOTES)."'>";
    }

    if( $mode == 1 ) {
        $raCmp = db_query( "SELECT R.name_en AS name FROM rl_companies R, cat_catalog C WHERE R.rl_cmp_id=C.cat_company_id AND C._key = $catid" );
        echo "<H2>Catalog Listing for ${raCmp['name']}</H2>";
    } else {
        echo "<H2>Catalog Listings for ${sSpecies['plain']}</H2>";
    }

    echo "<DIV align=center><INPUT type=submit value=Save></DIV><BR>";
    echo "<TABLE border=1>";

    echo "<TR><TH>".($mode==1 ? "Company" : "Species")."</TH><TH>Original Name</TH><TH>Ex</TH><TH>Index Name</TH><TH>Type here to Rename</TH></TR>";

    if( $mode == 1 ) {
        // Catalog
        $q =  "SELECT * FROM cat_item WHERE _status=0 AND cat_catalog_id=$catid";
        if( !empty($sSearch) ) {
            $q .= " AND (ospecies like '%$sSearch%' OR pspecies like '%$sSearch%' OR oname like '%$sSearch%' OR pname like '%$sSearch%')";
        }
        if( $bChgd ) {
            $q .= " AND pname <> oname";
        }
        $q .= " ORDER BY pspecies,pname";
    } else {
        // Species
        $q = "SELECT R.name_en AS cmpName, C._key AS catid,";
        $q .= " I._key AS _key, I._updated_by AS _updated_by,";
        $q .= " I.ospecies AS ospecies, I.oname AS oname, I.pspecies_ex AS pspecies_ex, I.pname AS pname";
        $q .= " FROM rl_companies R, cat_catalog C, cat_item I";
        $q .= " WHERE R.rl_cmp_id=C.cat_company_id AND C._key=I.cat_catalog_id AND I._status=0 AND I.pspecies='${sSpecies['db']}'";
        if( !empty($sSearch) ) {
            $q .= " AND (I.oname like '%$sSearch%' OR I.pname like '%$sSearch%')";
        }
        if( $bChgd ) {
            $q .= " AND I.pname <> I.oname";
        }
        $q .= " ORDER BY I.pname";
    }

    $savePName = "";
    $bP = 0;

    $dbc = db_open( $q );
    if( !$dbc ) db_error_die();
    while( $ra = db_fetch( $dbc ) ) {
        echo "<TR>";

        if( $mode == 1 ) {
            // pspecies
            echo "<TD>".link_species($ra['pspecies'])."</TD>";

        } else {
            // company catalog
            echo "<TD>".link_catalog($ra['cmpName'],$ra['catid'])."</TD>";

        }

        // ospecies, oname
        echo "<TD><FONT size=2>${ra['ospecies']}<BR>${ra['oname']}</FONT>";
        if( $bExtra ) {
            echo "<BR><INPUT type=text name=on{$ra['_key']} value=''>";
        }
        echo "</TD>";

        // pspecies_ex
        echo "<TD>${ra['pspecies_ex']}";
        if( $bExtra ) {
            echo "<BR><INPUT type=text name=psx{$ra['_key']} value=''>";
        }
        echo "</TD>";

        // pname
        if( $mode == 1 ) {
            echo "<TD>";
        } else {
            if( $ra['pname'] != $savePName ) { $bP = !$bP; }
            $savePName = $ra['pname'];
            echo "<TD bgcolor='".($bP ? "BBBBBB" : "FFFFFF")."'>";
        }

        if( $ra['pname'] != $ra['oname'] ) {
            echo "<FONT color=red>";
        }
        echo $ra['pname'];
        if( $ra['pname'] != $ra['oname'] ) {
            echo "</FONT><div align=right><FONT size=1>".LoginAuth_GetUserName($ra['_updated_by'])."</FONT></div>";
        }
        echo "</TD>";
        // Rename pname
        echo "<TD><INPUT type=text name=pn{$ra['_key']} value=''></TD>";
        echo "</TR>";
    }
    echo "</TABLE><BR>";
    echo "<DIV align=center><INPUT type=submit value=Save></DIV>";
    echo "</FORM>";

    link_mainlist();
}


?>
