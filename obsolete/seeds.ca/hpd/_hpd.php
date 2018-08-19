<?
define( "HPD_PAGE_START", SITEROOT."hpd/hpd.php" );
define( "HPD_PAGE_CVFRAME", SITEROOT."hpd/cv.php" );
define( "HPD_PAGE_CVDETAIL", SITEROOT."hpd/cvdetail.php" );


function hpd_page_header( $title = "" )
/**************************************
 */
{
    echo "<HTML><HEAD><TITLE>Heritage Plants Database - Seeds of Diversity Canada";
    if( !empty($title) ) {
        echo " - $title";
    }
    echo "</TITLE></HEAD>\n<BODY>";
    echo "<TABLE width=".ALT_PAGE_WIDTH." align=center cellpadding=0 cellspacing=0 border=0><TR><TD>";
    echo "<CENTER><IMG SRC='".SITEIMG."hpd_header.gif'>";
    echo "<P><A HREF='".SITEROOT."en.php' target='_top'>Home</A>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
    echo    "<A HREF='".HPD_PAGE_START."' target='_top'>Start Again</A>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
    echo    "<A HREF='".HPD_PAGE_START."?help=1' target='_top'>Help</A>";
    echo "</P></CENTER>";

    $page = basename( $_SERVER['PHP_SELF'] );
    if( $page == "sourceresults.php" ) {
    ?>
        <tr><td colspan="2" align="center">
            <a href="<?= HPD_PAGE_START ?>" target="_top">Regular Search</a>&nbsp;&nbsp;
            <a href="sourcesearch.php" target="_top">Search by Availability</a>&nbsp;&nbsp;
            <a href="<?= HPD_PAGE_START ?>?help=1" target="_top">Search help</a>
            </td>
        </tr>
    <?
    }
        //<TR><TD><A HREF="http://www.opensky.ca/cgi-bin/validate.cgi?url=referer&amp;input=yes">Validate this page</A></TD></TR>

}

function hpd_page_footer()
/*************************
 */
{
    echo "</TD></TR></TABLE>";
    std_footer();
    ?>
    </BODY>
    </HTML>
    <?
}



$HPD_Species_RecordDef
= array( "tablename" => "hpd_species",
         "fields" => array( array("name"=>"pspecies",        "type"=>"S", "default"=>"" ),
                            array("name"=>"botname",         "type"=>"S", "default"=>"" ),
                            array("name"=>"botfamily",       "type"=>"S", "default"=>"" ),
                            array("name"=>"iname",           "type"=>"S", "default"=>"" ),
                            array("name"=>"name",            "type"=>"S", "default"=>"" ),
                            array("name"=>"name_fr",         "type"=>"S", "default"=>"" ),
                            array("name"=>"category",        "type"=>"S", "default"=>"" ) ) );



function csci_drawCompanyList( $species, $cv, $bDrawLabel = true )
/*****************************************************************
 */
{
    echo "<P>";
    if( $bDrawLabel ) {
        echo "<B>$cv</B>";
    }
    echo "<BLOCKQUOTE><FONT size='-1'>";
    $sp_q = addslashes( $species );
    $cv_q = addslashes( $cv );
// Ideally there would be a dPR_CursorOpen that would do this based on the data model.  This would ensure proper
// use of _status and other future data model implementations.
    $q = "SELECT G1.name_en AS name, G1.web AS web FROM cat_item T1,cat_catalog P1,rl_companies G1 ";
    $q .= "WHERE T1.cat_catalog_id=P1._key AND P1.cat_company_id=G1.rl_cmp_id ";
    $q .= "AND T1.pspecies='$sp_q' AND T1.pname='$cv_q' AND T1._status=0";
    if( $dbc = db_open( $q ) ) {
        $l = array();
        while( $ra = db_fetch( $dbc ) ) {
            $sOut = $ra['name'];
            if( !empty($ra['web'] ) )  $sOut = "<A HREF='http://{$ra['web']}' target='csci_company'>$sOut</A>";
            $l[] = $sOut;
        }
        echo implode( ", &nbsp;", $l );
    }
    echo "</FONT></BLOCKQUOTE></P>\n";
}


?>
