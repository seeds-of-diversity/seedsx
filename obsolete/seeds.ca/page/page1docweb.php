<?

include_once( SEEDCOMMON."doc/docWebsite.php" );
include_once( PAGE1_TEMPLATE );


class Page1DocWebsite extends DocWebsite
/***************************************
    Serve a mini web site from a DocRep folder, drawn in a Page1 template

    Usage:
        $raDWparms = array( "lang"           => $lang,
                            "docid_home"     => ($lang == "FR" ? "xxfr_home" : "xxen_home"),
                            "docid_root"     => ($lang == "FR" ? "xxfr_rootfolder" : "xxen_rootfolder"),
                            "docid_extroots" => array( "main_web_image_root" ) );
        $raPage1parms = array( "lang" => $lang,
                               "title" => "Title",
                               "tabname" => "TABNAME",
                               "box1fn" => etc );

        $oD = new Page1DocWebsite( $raDWparms, $page1parms );
        $oD->Go();
 */
{
    var $raPage1parms;

    function Page1DocWebsite( $raDWparms, $raPage1parms ) { $this->DocWebsite( $raDWparms ); $this->raPage1parms = $raPage1parms; }

    function Main()
    /**************
        Override DocRepWebsite::Main()
            Init
            Try to serve Binary
            Expand Text doc into Page1 and return the result
     */
    {
        $this->Init();
        if( $this->BinaryServe() )                  return( true );     // true: served a binary object
        if( ($ret = $this->DrawPage()) === false )  return( false );    // false: error;  else text of page

        $this->raPage1parms['sBody'] = $ret;

        // this might not be in the best place, but it has to go before any text output and after we decide that the output is not binary
        header( "Content-type: text/html; charset=ISO-8859-1");

        return( Page1Str( $this->raPage1parms ) );
    }
}


?>
