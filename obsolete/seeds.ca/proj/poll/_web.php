<?

if( !defined("POLL_ROOT") )  { define("POLL_ROOT", "./"); }

define( "CLR_green_light", "#B8EEB8" );

include_once( STDINC."SEEDPerms.php" );
include_once( STDINC."DocRep/DocRepWebsite.php" );

// Put this in SEEDCOMMON.
// We'll want to do a different thing with SEEDPerms on index.php and test.php. Implement user groups for test.php

class MyWebsite extends DocRepWebsite
{
    var $raPermsR = array();

    function MyWebsite( &$kfdb, $uid = 0, $lang = "EN" )
    {
// docutil: New_SEEDPermsFromUID

        $oPerms = new SEEDPerms( $kfdb, DOCREP_SEEDPERMS_APP, array($uid), array() );
        $this->raPermsR = $oPerms->GetClassesAllowed( "R", false );

        $this->DocRepWebsite( $kfdb, $uid, $lang );
    }

    function drawBodyBegin()
    /***********************
     */
    {
        DocRepWebsite::drawBodyBegin();

        echo "<STYLE>"
            ."#DocRepWebsite_tabs            { background-color: ".CLR_green_light."; }"
            ."#DocRepWebsite_leftcol         { background-color: ".CLR_green_light."; }"
            ."</STYLE>";
    }

    function drawBodyEnd()
    /*********************
     */
    {
        echo "<BR>";
        echo "<TABLE border='0' cellspacing='0' cellpadding='4' width='100%' bgcolor='#FFFFFF' id='footer'>";
        echo "<TR><TD valign=top nowrap style='font-size:8pt;'>";
        if( $this->lang == "EN" ) {
            echo "Pollination Canada is a joint venture of<BR/>"
                ."<A HREF='http://www.seeds.ca'>Seeds of Diversity Canada</A> "
                ."and <BR/> <A href='http://www.eman-rese.ca'>Environment Canada's Ecological Monitoring<BR/> and Assessment Network Coordinating Office</A>.";
        } else {
            echo "Pollinisation Canada est un projet conjoint entre<BR/>"
                ."<A HREF='http://www.semences.ca'>Semences du Patrimoine du Canada</A> et <BR/>"
                ."<A HREF='http://www.eman-rese.ca'>le Réseau d'évaluation et de surveillance <BR/>"
                ."écologique d'Environnement Canada (RESE)</A>.";
        }
        echo "</TD><TD >";
    //  echo "<A href='http://www.ec.gc.ca/'><IMG src='".POLL_ROOT."img/canada.gif' border=0 alt='Canada'></A>";
        echo SEEDStd_StrNBSP( '', 8 );
        echo "<A href='http://www.seeds.ca/'><IMG src='".POLL_ROOT."img/sodlogo.gif' border=0 alt='Seeds of Diversity Canada'></A>";
        echo SEEDStd_StrNBSP( '', 8 );
        echo "<A href='http://www.eman-rese.ca/'><IMG src='".POLL_ROOT."img/EMAN_colour_red_text_with_title.gif' border=0 alt='EMAN/RESE'></A>";
        echo "</TD></TR></TABLE>";

        DocRepWebsite::drawBodyEnd();
    }

    function drawBanner()
    /********************
     */
    {
        echo "<DIV id='DocRepWebsite_banner'>";
        echo "<A href=".POLL_ROOT." style='text-decoration:none;'>";
        if( $this->lang == "EN" ) {
            echo "<img src='img/poll_banner_en.gif' alt='Pollination Canada' border='0'>";
        } else {
            echo "<img src='img/poll_banner_fr.gif' alt='Pollinisation Canada' border='0'>";
        }
        echo "</A><BR><BR>";
        echo "</DIV>";
    }

    function GetPermClassesR()
    /*************************
     */
    {
        return( $this->raPermsR );
    }
}



function pc_getParms()
/*********************
    Get the parms for MyWebsite
    Parms are language dependent, based on server name, overridden by optional lang GPC

    Output:
    $raParms['lang'] = "EN" | "FR"
    $raParms['Draw'] = parms for DocRepWebsite::Draw()
 */
{
    $raParms['lang'] = strtoupper( SEEDSafeGPC_Smart( "lang", array( "", "EN", "FR", "en", "fr" ) ) );
    if( empty($raParms['lang']) ) {
        $raParms['lang'] = (stristr($_SERVER['SERVER_NAME'], "pollinisationcanada.ca") === false ? "EN" : "FR");
    }

    $raParms['Draw'] = ( $raParms['lang'] == "EN"
                         ? array( "head_title"=>"Pollination Canada",   "rootfolder_name"=>"pc_rootfolder" )
                         : array( "head_title"=>"Pollinisation Canada", "rootfolder_name"=>"pcfr_rootfolder" ) );
    return( $raParms );
}

?>
