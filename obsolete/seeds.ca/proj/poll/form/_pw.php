<?
define( "SITEROOT", "../../../" );
include_once( SITEROOT."site.php" );


function pw_style()
{
    echo "<STYLE>";
    echo ".d_secthdr { font-family: verdana,helvetica,arial,sans-serif; }";
    echo ".d_inst { font-family: verdana,helvetica,arial,sans-serif; font-size:10pt; background-color:#eeffee; font-style: italic;       margin-bottom: 1em; }";
    echo ".d_q    { font-family: verdana,helvetica,arial,sans-serif; font-size:10pt; background-color:#eeeeee; }";
    echo ".d_a    { font-family: verdana,helvetica,arial,sans-serif; font-size:10pt;                           margin-bottom: 1em; }";


    echo "</STYLE>";
}


function dw_sect( $title )
{
    echo "<HR><H4 class='d_secthdr'>$title</H4>";
}


function dw_q_f( $q, $k )
{
    dw_q_i( $q, $k );
}
function dw_q_i( $q, $k )
{
    echo "<DIV class='d_q'>$q</DIV><DIV class='d_a'><INPUT type='text' name='$k' size=10></DIV>";
}
function dw_q_s( $q, $k )
{
    echo "<DIV class='d_q'>$q</DIV><DIV class='d_a'><INPUT type='text' name='$k' size=30></DIV>";
}
function dw_q_d( $q, $k )
{
    dw_q_s( $q, $k );
}
function dw_q_m( $q, $k, $ra, $raImg = array(), $parms = array() )
{
    // work out the img height/width, if applicable
    $imgAttrs = "";
    if( count($raImg) ) {
        if(      !empty($parms['imgW']) )  $imgAttrs = "width='${parms['imgW']}'";
        else if( !empty($parms['imgH']) )  $imgAttrs = "height='${parms['imgH']}'";
        else                               $imgAttrs = "height=80";
    }

    echo "<DIV class='d_q'>$q</DIV>";
    echo "<TABLE class='d_a' border=0 cellpadding=5>";
    $i = 0;
    foreach( $ra as $n => $label ) {
        if( ($i % 5 == 0) ) { if( $i ) { echo "</TR>"; }  echo "<TR>"; }
        echo "<TD valign=top><INPUT type=radio name='$k' value='$n'".($n ? "" : " CHECKED='CHECKED'").">$label";
        if( count($raImg) && !empty($raImg[$n]) ) echo "<BR><DIV align=center><IMG src='../img/${raImg[$n]}' $imgAttrs></DIV>";
        echo "</TD>";
        ++$i;
    }
    echo "</TR></TABLE>";
}
function dw_q_b( $q, $k )
{
    dw_q_m( $q, $k, array( 1=>"Yes", 2=>"No", 0=>"don't know - N/A" ) );
}


// s:blank == don't know
// d:blank == don't know
// m:blank == 0 == don't know
// b:blank == 0 == don't know
// intval(i:blank) == 0 == don't know
// floatval(f:blank) == 0 == don't know


function dw_commonTop( $label, $dw_sp )
{
    echo "<DIV><IMG src='".SITEIMG."logo_EN.gif'></DIV>";
    echo "<H3 class='d_secthdr'>$label Observation Form</H3>";

    echo "<DIV style='border:medium solid grey; padding:15px; text-align:center;' class='d_a'>";

    echo "<DIV style='padding:0'>Seeds of Diversity uses this form to record observations of plant characteristics across the country. ";
    echo "You can add to Canadians' knowledge of our precious horticultural heritage by noting your observations of ";
    echo "heritage plants in your garden, and entering the information in our public database. ";
    echo "All characteristics are shown publicly, but your contact information is always kept confidential.</DIV>";

    echo "<TABLE border=0 cellpadding=20><TR>";
    echo "<TD class='d_a' align=center>The research and development for this project was fully funded by<BR/> the Ontario Trillium Foundation.</TD>";
    echo "<TD><IMG src='".SITEIMG."OTF_HORZTL_CLR_4_Microsoft.jpg' width=350></TD>";
    echo "</TR></TABLE>";

    echo "<DIV style='font-size:7pt;'>Illustrations: Seeds of Diversity Canada; International Plant Genetic Resources Institute</DIV>";

    echo "</DIV>";

    echo "<FORM action='dw_submit.php' method='post'>";
    echo "<INPUT type=hidden name=dw_sp value='$dw_sp'>";


    echo "<DIV class='d_inst'>";
    echo "<P>If you cannot answer any of these questions, just leave them blank or check the \"don't know\" choice.</P>";
    echo "</DIV>";

    echo "<TABLE width='100%' border=0><TR><TD valign=top width=25%>";
    dw_q_s( "Variety name", "common_SoD_s__cultivarname" );
    echo "</TD><TD valign=top class='d_a'>";
    echo "<INPUT type='checkbox' name='common_SoD_b__cultivarnameunknown' value='1'>Variety is unknown";
    echo "</TD></TR></TABLE>";

    dw_q_s( "Observer", "common_SoD_s__observerid" );
    dw_q_s( "Location", "common_SoD_s__locationid" );

    dw_q_m( "Soil type where this variety was growing?", "common_SoD_m__soiltype",
            array( 1=>"Sandy", 2=>"Loamy", 3=>"Loamy sand", 4=>"Loamy clay", 5=>"Clay", 6=>"Other", 0=>"don't know" ) );

    dw_q_s( "If Other, describe your soil", "common_SoD_s__soiltypeother" );

    dw_q_i( "Sample size: Approximately how many ".($dw_sp=='apple' ? "trees" : "plants")." are you observing for the purposes of this form?",
            "common_SoD_i__samplesize" );
}

function dw_commonBottom()
{
    echo "<INPUT type=submit value='Submit'>";
    echo "</FORM>";

    std_footer();
}


?>
