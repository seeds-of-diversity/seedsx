<?

include_once( "../site.php" );
include_once( SEEDCOMMON."siteStart.php" );
include_once( STDINC."KeyFrame/KFRelation.php" );
include_once( STDINC."SEEDForm.php" );
include_once( STDINC."SEEDLocal.php" );
include_once( PAGE1_TEMPLATE );
include_once( "_mbr.php" );

$lang = "EN";   // get the correct language from the session
$mL = new SEEDLocal( $mbr_Text, $lang );


Page1( array( "lang"      => $lang,
              "title"     => $mL->S('form_title'),
              "tabname"   => "MBR",
//            "box1title" => "What's New",
//            "box1fn"    => "box1fn",
//            "box2title" => "Contact Us",
//            "box2fn"    => "box2fn",
            ) );


function Page1Body()
{
    echo "<H2>Order Cancelled</H2>";
    echo "<P align=center>Please contact our ".SEEDStd_EmailAddress( "office", "seeds.ca", "office" )." for assistance.</P>";
}

?>
