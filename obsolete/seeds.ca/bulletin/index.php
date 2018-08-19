<?
/* User portal to Bulletin.
 *
 * Starting point for Bulletin sign-up.
 */
include_once( "../site.php" );
include_once( SEEDCOMMON."siteStart.php" );
include_once( STDINC."SEEDLocal.php" );
include_once( "_bull.php" );

$lang = site_define_lang();
list($kfdb) = SiteStart();
//$kfdb->SetDebug(1);

$oL = new SEEDLocalDBServer( $kfdb, $lang, 'www.seeds.ca', 'ebulletin', $raParms = array() );



echo bull_header();
echo $oL->S('Instructions');

echo "<BR/>"
    ."<FORM action='${_SERVER['PHP_SELF']}' method='post'>"
    ."<P style='margin-left:5ex'>".$oL->S('Enter your email address here').": <INPUT type='text' name='e'/></P>"
    ."<P style='margin-left:5ex'><INPUT type='submit' NAME='req' VALUE='Subscribe'>"
    .SEEDStd_StrNBSP("",5)
    ."<INPUT type='submit' NAME='req' VALUE='Unsubscribe'></P>"
    ."</FORM>";

echo "<BR/>"
    ."<P style='font-size:10pt'>".$oL->S('Privacy Policy')."</P>";

echo bull_footer();


function bull_header()
{
    global $oL;

    $s =  "<TABLE width='640' align='center'>"
         ."<TR><TD>"
         .$oL->S('Header')
         ."<HR width='75%'>";
    return( $s );
}

function bull_footer()
/*********************
 */
{
    $s = "</TD></TR></TABLE>"
        .site_footer();

    return( $s );
}


?>
