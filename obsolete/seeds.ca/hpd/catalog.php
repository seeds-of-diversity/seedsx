<?
include_once( "../site.php" );
include_once( SITEINC."sitedb.php" );
include_once( "_hpd.php" );
include_once( "_func.php" );

$catalog = @$_REQUEST['catalog'];
if( empty($catalog) )  BXStd_HTTPRedirect( HPD_PAGE_START );

$catinfo = query( "catalog", array( 'catalog'=>$catalog ) );
if( !$catinfo ) {
    die( "<P>The catalog was not found.  Please <A HREF='".HPD_PAGE_START."'>start again.</A></P>" );
}

hpd_page_header( "Historic Seed Catalogue - {$catinfo[0]['name']} {$catinfo[0]['refdate']}" );
std_banner1( "Historic Seed Catalogue" );

echo "<TABLE width=". ALT_PAGE_WIDTH ." align='center'><TR><TD>";
echo "<p><FONT face='arial,helvetica,sans serif' size='+2'><B>". $catinfo[0]['name'] . "</B></FONT><BR>";
echo "<FONT face='arial,helvetica,sans serif' size=+1><B>". $catinfo[0]['place'] ."<BR>". $catinfo[0]['refdate'] ."</B></FONT></P>";
echo "</TD></TR>";

$seeds = query( "catalogcontents", array( 'catalog'=>$catalog ) );
//echo count( $seeds ) . "<BR>";

$prevspecies = "";
for( $i=0; !empty( $seeds[$i] ); $i++ ) {
    $seed = &$seeds[$i];
    if( $seed['species'] != $prevspecies ) {
        if( !empty($prevspecies) ) { echo "</BLOCKQUOTE></TD></TR>"; }
        echo "<TR><TD><HR></TD></TR>";
        echo "<TR><TD valign='top'><FONT size='+1' face='arial,helvetica,sans serif'><B>". $seed['species'] ."</B></FONT><BR></TD></TR>";
        echo "<TR><TD><BLOCKQUOTE>";
        $prevspecies = $seed['species'];
    }
    echo "<A HREF='".HPD_PAGE_CVFRAME."?species=". urlencode( $seed['species'] );
    if( empty( $seed['pname'] ) && empty( $seed['oname'] ) ) {
        echo "&cultivar=NULL'> (Unnamed)";
    } else {
        echo "&cultivar=". urlencode( $seed['pname'] ) ."'><B>". $seed['oname'] ."</B>";
    }
    echo "</A>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
    if( $seed['description'] )    echo $seed['description'];
    if( $seed['price_pkt'] )      echo " (".$seed['price_pkt']."/pkt)";
    if( $seed['price_special'] )  echo " (".$seed['price_special'].")";
    echo "<BR><BR>";
}

echo "</TABLE>";
hpd_page_footer();
?>
