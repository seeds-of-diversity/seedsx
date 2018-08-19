<?
include_once( "../_proj.php" );

$page1parms = array (
                "lang"      => "EN",
                "title"     => "Great Canadian Garlic Collection",
                "tabname"   => "Projects",
                "css"       => "gcgc.css",
                "box1title" => "Great Canadian Garlic Collection",
//              "box1text"  => "Box2Text",
                "box1fn"    => "Box1Fn",
                "box2title" => "Contact Us",
//              "box2text"  => "Box2Text",
                "box2fn"    => "Box2Fn"
             );


function box1fn() {
    return(
        "<div><a href='index.php'>The GCGC</a></div>"
       ."<div><a href='gcgc1.php'>Your Role</a></div>"
       ."<div><a href='gcgc2.php'>About Garlic</a></div>"
       ."<div><a href='gcgc3.php'>Growing Garlic</a></div>" );
}


function box2fn() {
    return(
        "<div>". SEEDStd_EmailAddress( "mail", "seeds.ca" ) ."</div>"
       ."<div><a href='".SITEROOT."mbr/member.php'>How to Join</a></div>"
       ."<div><a href='".SITEROOT."mbr/member.php'>Order our Publications</a></div>"
       ."<div><a href='".SITEROOT."bulletin/'>Subscribe to our free email Bulletin</a></div>" );

}


?>
