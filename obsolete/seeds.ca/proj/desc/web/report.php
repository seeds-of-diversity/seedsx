<?

include_once( "_dw.php" );
include_once( SITEINC."siteKFDB.php" );

$kfdb = SiteKFDB() or die( "Cannot connect to database" );

$dwid = BXStd_SafeGPCGetInt( "dwid" );

echo "<DIV style='font-family:verdana,helvetica,arial,sans serif'>";
echo "<DIV><IMG src='".SITEIMG."logo_EN.gif'></DIV>";
echo "<H3>Descriptive Keys Content</H3>";

if( !$dwid ) {
    /* List the submissions.
     */
    $nSub1 = $kfdb->KFDB_Query1( "SELECT count(*) FROM desc_submit_1 WHERE _status=0" );
    $nSub2 = $kfdb->KFDB_Query1( "SELECT count(*) FROM desc_submit_2 WHERE _status=0 AND k NOT LIKE 'common_%'" );

    echo "<P>$nSub1 submissions, $nSub2 data elements</P>";

    $dbc1 = $kfdb->KFDB_CursorOpen( "SELECT * from desc_submit_1 ORDER by _created" );
    while( $ra1 = $kfdb->KFDB_CursorFetch( $dbc1 ) ) {
        $ra2 = array();
        $dbc2 = $kfdb->KFDB_CursorOpen( "SELECT * from desc_submit_2 WHERE fk_ds1=".$ra1['_rowid'] );
        while( $raTmp = $kfdb->KFDB_CursorFetch( $dbc2 ) ) {
            $ra2[$raTmp['k']] = $raTmp['v'];
        }
        $kfdb->KFDB_CursorClose($dbc2);

        echo "<P><A HREF='{$_SERVER['PHP_SELF']}?dwid=${ra1['_rowid']}'>${ra1['sp']}";
        echo " - ".@$ra2['common_SoD_s__cultivarname'];
        echo " : ".@$ra2['common_SoD_s__observerid'];
        echo " : ".@$ra2['common_SoD_s__locationid'];
        echo " : ".@$ra2['common_SoD_s__date'];
        echo "</A></P>";
    }
} else {

    $ra1 = $kfdb->KFDB_QueryRA( "SELECT * from desc_submit_1 WHERE _rowid=".$dwid );
    $ra2 = array();

    $dbc2 = $kfdb->KFDB_CursorOpen( "SELECT * from desc_submit_2 WHERE fk_ds1=".$dwid );
    while( $raTmp = $kfdb->KFDB_CursorFetch( $dbc2 ) ) {
        $ra2[$raTmp['k']] = $raTmp['v'];
    }

    echo "<H2>".( @$ra2['common_SoD_s__cultivarname'] ? $ra2['common_SoD_s__cultivarname'] : "[Unknown name]")."</H2>";
    echo "<BR>Observer: ".@$ra2['common_SoD_s__observerid'];
    echo "<BR>Location: ".@$ra2['common_SoD_s__locationid'];
    echo "<BR>Date:     ".@$ra2['common_SoD_s__date'];

    foreach( $ra2 as $k => $v ) {
        if( substr( $k, 0, 7 ) == "common_" &&
            $k != "common_SoD_s__cultivarname" &&
            $k != "common_SoD_s__observerid" &&
            $k != "common_SoD_s__locationid" &&
            $k != "common_SoD_s__date" )
        {
            echo "<P>$k = $v</P>";
        }
    }

    unset( $raDef );
    switch( $ra1['sp'] ) {
        case 'bean':    $raDef = $raDefBean;    break;
        case 'tomato':  $raDef = $raDefTomato;  break;
    }

    if( isset($raDef) ) {
        foreach( $raDef as $k => $raV ) {
            if( isset($ra2[$k]) ) {
                $label = $raV[0];
                $r = explode("__", $k );
                if( substr( $r[0], strlen($r[0])-1 ) == 'm' ) {
                    $val = $raV[2][$ra2[$k]];
                } else {
                    $val = $ra2[$k];
                }
                echo "<P>$label = $val</P>";
            }
        }
    } else {
        foreach( $ra2 as $k => $v ) {
            echo "<P>$k = $v</P>";
        }
    }
}

?>
