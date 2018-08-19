<?

include( "index1.php" );

echo "AAA";
exit;


define( "SITEROOT", "../../" );
include_once( SITEROOT."site.php" );
include_once( SITEINC."siteStart.php" );
include_once( PAGE1_TEMPLATE );
include_once( STDINC."DocRep/DocRep.php" );
include_once( STDINC."DocRep/DocRepDB.php" );



list($kfdb, $la) = SiteStartAuth( "W DocRepMgr" );

$docrepDB = new DocRepDB( $kfdb, $la->LoginAuth_UID() );



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


function showTree( $keyTree )
/****************************
 */
{
    global $docrepDB;

    $raDocs = $docrepDB->ListChildren( $keyTree );
    foreach( $raDocs as $k => $v ) {
        if( $v['type'] == 'FOLDER' ) {
            echo "<H4>".$v['title']."</H4><BLOCKQUOTE>";
            showTree( $k );
            echo "</BLOCKQUOTE><BR>";
        } else {
            echo DR_link( SITEROOT."library/doc.php?k=".$k,@$v['title'],@$v['desc'], array( "author"=>@$v['author'],
                                                                               "date"=>@$v['date'],
                                                                         //      "pub"=>@$v['metadata']['pub'],
                                                                               "target"=>"seedDoc" ) );
        }
    }
}


function Page1Body() {
    global $kfdb, $la;

    echo "<H2>Heritage Seed Program Articles</H2>";

    echo "<A href='../../int/docmgr/docmanager.php'><P style='color:red'>[Add an article]</P></A>";
    echo "<A href='../../int/docmgr/docmgr_kfui.php'><P style='color:red'>[Edit article properties]</P></A>";

    $keyTree = $kfdb->KFDB_Query1( "SELECT _key FROM docrep_docs WHERE name='Folder:Magazine:Heritage Seed Program'" );
    showTree( $keyTree );
}

?>
