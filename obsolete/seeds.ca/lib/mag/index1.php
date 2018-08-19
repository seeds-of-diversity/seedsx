<?

define( "SITEROOT", "../../" );
include_once( SITEROOT."site.php" );
//include_once( SITEINC."siteStart.php" );
include_once( PAGE1_TEMPLATE );
//include_once( STDINC."DocRep/DocRep.php" );
//include_once( STDINC."DocRep/DocRepDB.php" );



//list($kfdb, $la) = SiteStartAuth( "W DocRepMgr" );

//$docrepDB = new DocRepDB( $kfdb, $la->LoginAuth_UID() );



$page1parms = array (
                "lang"      => "EN",
                "title"     => "Heritage Seed Program Articles",
                "tabname"   => "Library",
//              "box1title" => "Canadian Tomato Project",
//              "box1text"  => "Box2Text",
//              "box1fn"    => "Box1Fn",
//              "box2title" => "Box2Title",
//              "box2text"  => "Box2Text",
//              "box2fn"    => "Box2Fn"
             );



Page1( $page1parms );



function Page1Body() {
//  global $kfdb, $la;

    echo "<H2>Magazine Article Index</H2>";
    echo "<BLOCKQUOTE>";
    echo "<P><A HREF='SoD index 2006.html'>Seeds of Diversity 2004-2005</A></P>";
    echo "<P><A HREF='SoD index 2004.html'>Seeds of Diversity 2002-2003</A></P>";
    echo "<P><A HREF='SoD index 2002.html'>Seeds of Diversity 2000-2001</A></P>";
    echo "<P><A HREF='SoD index 2000.html'>Seeds of Diversity 1998-1999</A></P>";
    echo "<P><A HREF='SoD index 1998.html'>Seeds of Diversity 1996-1997</A></P>";
    echo "<P><A HREF='SoD index 1996.html'>Heritage Seed Program 1988-1995</A></P>";
    echo "</BLOCKQUOTE>";
}

?>
