<?php

/* Enumerate the logos available on the web site.
 *
 * These are available for download by the general public on the terms explained here.
 */

define( "SITEROOT", "../" );
include( SITEROOT."site.php" );

$dirLogo = "../i/img/logo";
$logos = getFiles( $dirLogo );


$s = "<style>"
    ."body              { background-color: #eee; }"
    ."body, p, h2, td   { font-family:verdana,arial,helvetica,sans serif; }"
    ."p, td             { font-size:10pt; }"
    ."</style>"

    ."<body>"
    ."<h2>Seeds of Diversity Logos</h2>"
    ."<table style='border:solid thin black; width:50%; margin-left:120px;padding:10px'><tr><td>"
    ."<p>These logos are available for promotional use by our volunteers and partners.</p>"
    ."<p>Please obtain permission from our office before reproducing logos, or using them on your web site. This is easy to do: just contact "
    .SEEDCore_EmailAddress( "office", "seeds.ca" )
    ." and tell us how you want to use the logo.</p>"
    ."<p>Download logos by clicking on the 'Download' links below.  Some logo files are formatted for use on web sites, and some are for print use."
    //."The links specify the number of pixels wide x number of pixels high, the colour depth, the file format, and the file size."
    ."</p>"
    ."</td></tr></table>"

    .drawLogo( "logoA_v",
               "Our official logo has the \"hands\" above the name. This is the preferred configuration if it fits your format." )
    .drawLogo( "logoA_h",
               "Alternatively, the name can go to the right of the hands and seeds. This is typically useful for a horizontal format." )
    .drawLogo( "logoA_b",
               "This horizontal configuration is appropriate in a bilingual document.  "
              ."e.g. it could be in a header of a bilingual message where two separate logos don't fit." )

    .drawLogo( "logoA_txt",
               "The name itself in this distinctive font can be used as a brand element, in place of the whole logo." )

    .drawLogo( "logoA",
               "The hands and seeds design can sometimes be used on its own, under one of these conditions: "
              ."<ul><li>The organization's name is clearly connected to this design. e.g. in nearby text, on opposite side of a card, etc</li>"
              ."<li>The context of the document leaves no doubt of the organization's identity.  e.g. decorative element in a Seeds of Diversity publication</li></ul>"
              ."This can be a useful element in a bilingual document" )

    ."<p style='margin:60px 120px;clear:both'>Copyright 2014-".date('Y')." Seeds of Diversity Canada</p>
</body>";

echo $s;


function drawLogo( $sPrefix, $sDescription )
{
    global $logos, $dirLogo;

    $s = "";

    $vEN = "";
    $vFR = "";

    $raLogos = array();
    foreach( $logos as $v ) {
        $ra = explode( '-', $v );
        if( $ra[0] == $sPrefix ) {
            $raLogos[] = $v;
            if( $ra[1] == 'en' && !$vEN )  $vEN = $v;
            if( $ra[1] == 'fr' && !$vFR )  $vFR = $v;
        }
    }

    if( count($raLogos) ) {
        if( $vEN ) $i1 = "<img src='$dirLogo/$vEN' width='100'/>";
        else       $i1 = "<img src='$dirLogo/{$raLogos[0]}' width='100'/>";

        if( $vFR ) $i2 = "<img src='$dirLogo/$vFR' width='100'/>";
        else       $i2 = "";

        $s .= "<div style='float:left'>$i1<br/><br/>$i2</div>";

        $s .= "<div style='margin-left: 120px'>"
             .$sDescription
             ."<div style='margin-left:30px'>"
             ."<table border='0' cellspacing='0' cellpadding='5'>";

        foreach( $raLogos as $v ) {
            $vreal = $dirLogo."/".$v;
            if( file_exists($vreal) ) {
                $imgsize = getimagesize($vreal);
                $filesize = intval(filesize($vreal)/1000);
                $ra = explode( '-', $v );
                $lang = ($ra[1] == 'en' ? "English, " : ($ra[1] == 'fr' ? "Fran&ccedil;ais, " : ""));

                $s .= "<tr><td><a href='$dirLogo/$v' target='_blank'>Download</a></td>"
                     ."<td style='font-size:10pt;'>$lang dimensions ".$imgsize[0]."x".$imgsize[1].", file size $filesize K</td><td>";
                if( $imgsize[0] <= 300 ) {
                    $s .= " (Screen resolution: for web sites only)";
                } else if( $imgsize[0] <= 1200 ) {
                    $s .= " (Medium resolution: for web sites or printing no larger than ".round($imgsize[0]/300,1)." inches wide)";
                } else {
                    $s .= " (High resolution: for printing no larger than ".round($imgsize[0]/300,1)." inches wide)";
                }
                $s .= "</td></tr>";
            }
        }
        $s .= "</table></div></div>";
    }

    $s = "<div style='margin:10px;padding:10px;clear:both;'><hr style='margin-left:120px;border:1px solid #ab6'/>".$s."</div>";
    return( $s );
}


function getFiles( $dir )
/************************
    Get all image filenames in the given directory
 */
{
    $pattern="/\.(jpg|jpeg|gif|bmp|png)$/i"; //valid image extensions
    $raFiles = array();

    if( $handle = opendir( realpath($dir) ) ) {
        while( false !== ($f = readdir( $handle )) ) {
            if( preg_match( $pattern, $f ) ) { //if this file is a valid image
                $raFiles[] = $f;
            }
        }
        closedir( $handle );
    }
    return( $raFiles );
}

?>
