<?
header("Location: http://www.seeds.ca/mbr");

exit;


define("SITEROOT","../../");
include_once( SITEROOT."site.php" );
include_once( SEEDCOMMON."siteStart.php" );
include_once( STDINC."KeyFrame/KFRelation.php" );


include_once( PAGE1_TEMPLATE );
include_once( SEEDCOMMON."mbr/mbrOrderCheckout.php" );


list($kfdb, $sess) = SiteStartSession();

site_define_lang(@$_SESSION['lang']);

Page1( array( "lang"      => SITE_LANG,
              "title"     => "Checkout",                    //$mL->S('form_title'),
              "tabname"   => "MBR",
));




function Page1Body()
{
    class MyMbrOrderCheckout extends MbrOrderCheckout {

        function MyMbrOrderCheckout( &$kfdb, &$sess ) { $this->MbrOrderCheckout( $kfdb, $sess, SITE_LANG ); }


        function FormDrawOrderCol()
        {
            $s = "<DIV class='mbro_box'>"
                ."<DIV class='mbro_boxheader'>Conference Registration</DIV>"
                ."<DIV class='mbro_boxbody'>"

                ."<P><B>Practical Pollinator Conservation</B> conference October 5-6, Montreal</FONT></P>"
                ."<P style='margin-left:2em;'>"
                        .$this->oKForm->Radio("cppi2009conf","","reg" )."Regular registration 2 days  ($150)"
                ."<BR/>".$this->oKForm->Radio("cppi2009conf","","student")."Student rate 2 days ($120)"
                ."<BR/>".$this->oKForm->Radio("cppi2009conf","","monday")."Monday Oct 5 only ($85)"
                ."<BR/>".$this->oKForm->Radio("cppi2009conf","","tuesday")."Tuesday Oct 6 only ($85)"
                ."<BR/><BR/>Registration includes buffet lunch and refreshments"
                ."</P>"
                ."</DIV></DIV>";

            return( $s );
        }

        function ValidateParmsOrderValid( $oSVar )
        {
            if( in_array( $oSVar->VarGet("cppi2009conf"), array( "reg","monday","tuesday","student" ) ) ) {
                return( true );
            } else {
                $this->raFormErrors[] = "Choose a registration category";
                return( false );
            }
        }

        function ValidateParmsOrderMakeKFR( $oSVar )
        {
            if( ($v = $oSVar->VarGet("cppi2009conf") ) ) {
                $s = SEEDStd_ParmsURLAdd( $this->kfrOC->Value("sExtra"), "cppi2009conf", $v );
                $this->kfrOC->SetValue( "sExtra", $s );
            }
        }
    }


    global $kfdb, $sess;

    $oMbrOC = new MyMbrOrderCheckout( $kfdb, $sess );
    $oMbrOC->Checkout();
}

?>
