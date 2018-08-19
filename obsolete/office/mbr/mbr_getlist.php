<?
/* Output member info list
 */

include_once( "../site.php" );
include_once( STDINC."KeyFrame/KFUIAppSimple.php" );
include_once( SEEDCOMMON."siteApp.php" );
include_once( "_mbr.php" );

list($kfdb, $sess) = SiteStartSessionAccount( array("MBR" => "R") );

$oList = new MbrGetList( $kfdb, $sess );


switch( SEEDSafeGPC_GetStrPlain('mode') ) {
    case 'XLS':
        $oList = new MbrGetListXLS( $kfdb, $sess );
        $oList->DrawList();
        break;
    case 'CSV':
        $oList = new MbrGetListCSV( $kfdb, $sess );
        $oList->DrawList();
        break;
    default:
        $oList = new MbrGetList( $kfdb, $sess );
        $oList->DrawForm();
        break;
}


class MbrGetList
{
    private $kfdb;
    private $sess;
    private $sql;

    private $p_yMbr;
    private $p_yDonor;
    private $p_bCanada;
    private $p_sLang;

    function MbrGetList( &$kfdb, &$sess )
    {
        $this->kfdb =& $kfdb;
        $this->sess =& $sess;
        $this->year = date("Y");

        $this->p_yMbr   = SEEDSafeGPC_GetInt('yMbr');       if( !$this->p_yMbr )   $this->p_yMbr = date("Y")-2;
        $this->p_yDonor = SEEDSafeGPC_GetInt('yDonor');     if( !$this->p_yDonor ) $this->p_yDonor = date("Y")-2;
        $this->p_bNotCanada = SEEDSafeGPC_GetInt('bNotCanada');  // inverted because we want the default to be 1
        $this->p_sLang = SEEDSafeGPC_Smart( 'sLang', array('','EN','BI') );

        $this->sql = "(YEAR(T1.expires) >= '$this->p_yMbr' OR YEAR(T1.donation_date) >= '$this->p_yDonor') AND "
                    ."T1.country".($this->p_bNotCanada ? "<>":"=")."'Canada' AND "
                    ."T1.postcode <> ''";  // sometimes Judy uses postcode='' to indicate that the address is no longer current (unknown address)
        if( $this->p_sLang ) {
            $this->sql .= " AND "
                         .($this->p_sLang=='BI' ? "" : "NOT ")
                         ."(lang='F' OR province IN ('QC','NB','QUE','N.B'))";  // there are still some old province codes in the db, not current
        }
    }

    function DrawForm()
    {
        $s = "<FORM method='get' action='{$_SERVER['PHP_SELF']}' target='_blank'>"
            .SEEDForm_Text( 'yMbr', $this->p_yMbr, "Members from and above" )."<BR/>"
            .SEEDForm_Text( 'yDonor', $this->p_yDonor, "Donors from and above" )."<BR/>"
            ."<FIELDSET style='width:400px'><LEGEND>Where</LEGEND>"
            .SEEDForm_Radio( 'bNotCanada', 0, $this->p_bNotCanada, 'Canada' )."<BR/>"
            .SEEDForm_Radio( 'bNotCanada', 1, $this->p_bNotCanada, 'Not Canada' )."<BR/>"
            ."</FIELDSET>"
            ."<FIELDSET style='width:400px'><LEGEND>Language</LEGEND>"
            .SEEDForm_Radio( 'sLang', '',   $this->p_sLang, 'All (no filter)' )."<BR/>"
            .SEEDForm_Radio( 'sLang', 'EN', $this->p_sLang, 'English (not Bilingual below)' )."<BR/>"
            .SEEDForm_Radio( 'sLang', 'BI', $this->p_sLang, 'Bilingual (lang=F OR province=QC OR province=NB)' )."<BR/>"
            ."</FIELDSET>"
            ."<INPUT type='submit' name='mode' value='XLS'/><BR/>"
            ."<INPUT type='submit' name='mode' value='CSV'/>"
            ."</FORM>";
        echo $s;
    }

    function DrawList()
    {
        $this->OutStart();
        $kfr = $this->getKFR();

        $row = 0;
        $this->OutRow( $row, array($this->sql) );

        $raTable = array();
        while( $kfr->CursorFetch() ) {
            $salutation = $name = "";

            if( $kfr->value('bNoDonorAppeals') ) {
                $this->OutRow( $row, array( $kfr->Key().": ".$kfr->value('firstname')." ".$kfr->value('lastname')." ".$kfr->value('company')
                                           ." requests no donor appeals." ) );
                continue;
            }

//TODO: if the firstname contains a period, or just one letter, it's probably an initial or initials.  salutation should be firstname+lastname

            if( !$kfr->IsEmpty('firstname') ) {
                $salutation = $kfr->value('firstname');
                if( !$kfr->IsEmpty('lastname') ) {
                    $name = $kfr->value('firstname')." ".$kfr->value('lastname');
                } else {
                    $this->OutRow( $row, array( $kfr->Key()." has firstname '".$kfr->value('firstname')."' but no lastname" ) );
                }
            } else if( $kfr->IsEmpty('lastname') && !$kfr->IsEmpty('company') ) {
                $salutation = $name = $kfr->value('company');
            }

            if( empty($salutation) )  $this->OutRow( $row, array( $kfr->Key()." has no valid salutation" ) );
            if( empty($name) )        $this->OutRow( $row, array( $kfr->Key()." has no valid name" ) );

            $raAddrblock = array();
            if( $s1 = trim($kfr->Expand( "[[firstname]] [[lastname]]", false )) ) {  // really important not to expand characters to entities because we use these verbatim in mailing labels
                $raAddrblock[] = $s1;
            }
            if( $s1 = trim($kfr->Expand( "[[company]] [[dept]]", false )) ) {
                $raAddrblock[] = $s1;
            }
            $raAddrblock[] = $kfr->value('address');
            $raAddrblock[] = $kfr->Expand( "[[city]] [[province]]  [[postcode]]", false );

            $ra = array();
            $ra[] = $kfr->Key();
            $ra[] = $salutation;
            $ra[] = $name;
            $ra[] = @$raAddrblock[0];
            $ra[] = @$raAddrblock[1];
            $ra[] = @$raAddrblock[2];
            $ra[] = @$raAddrblock[3];
            $ra[] = $kfr->value('email');
            $ra[] = $kfr->value('phone');
            $ra[] = $kfr->value('lang');
            $yearMbr = substr( $kfr->value('expires'), 0, 4 );
            $ra[] = ($yearMbr=='2020' ? "Complimentary"
                  : ($yearMbr=='2100' ? "Auto"
                  : ($yearMbr=='2200' ? "Lifetime" : $yearMbr)));
            $ra[] = substr( $kfr->value('donation_date'), 0, 4 );
            $ra[] = $kfr->value('bNoEBull');
            $ra[] = $kfr->value('firstname');
            $ra[] = $kfr->value('lastname');
            $ra[] = $kfr->value('company');
            $ra[] = $kfr->value('dept');
            $ra[] = $kfr->value('address');
            $ra[] = $kfr->value('city');
            $ra[] = $kfr->value('province');
            $ra[] = $kfr->value('postcode');
            $ra[] = $kfr->value('country');
            $raTable[] = $ra;
        }
        $this->OutRow( $row, array("") );
        $this->OutRow( $row, array("Listing ".count($raTable)." people") );
        $this->OutRow( $row, array("") );

        $this->OutRow( $row, array("mbrid","sal","name","addr1","addr2","addr3","addr4","email","phone","lang","year","year_don","bNoEBull",
                                   "firstname","lastname","company","dept","address","city","province","postcode","country") );

        foreach( $raTable as $ra ) {
            $this->OutRow( $row, $ra );
        }

        $this->OutEnd();
    }

    function getKFR()
    {
        $kfrel = MbrContacts::KfrelBase( $this->kfdb, $this->sess->GetUID() );

        if( @$_REQUEST['debug'] )  $this->kfdb->SetDebug(2);

        $kfr = $kfrel->CreateRecordCursor( $this->sql, array( "sSortCol" => 'T1.postcode' ) );
        $this->kfdb->SetDebug(0);

        return( $kfr );
    }
}

class MbrGetListXLS extends MbrGetList
{
    private $xls;

    function MbrGetListXLS( &$kfdb, &$sess ) { $this->MbrGetList( $kfdb, $sess ); }

    function OutStart()
    {
        include_once( STDINC."KeyFrame/KFRTable.php" );
        $this->xls = new KFTableDump();
        $this->xls->xlsStart( "mbr_getlist.xls" );
    }
    function OutRow( &$row, $ra )
    {
        $i = 0;
        foreach( $ra as $h ) {
            $this->xls->xlsWrite( $row, $i++, $h );
        }
        ++$row;
    }
    function OutEnd()
    {
        $this->xls->xlsEnd();
    }
}

class MbrGetListCSV extends MbrGetList
{
    function MbrGetListCSV( &$kfdb, &$sess ) { $this->MbrGetList( $kfdb, $sess ); }

    function OutStart()
    {
        header( "Content-type: text/plain" );
    }
    function OutRow( &$row, $ra )
    {
        $i = 0;
        foreach( $ra as $h ) {
            echo $h."\t";
        }
        echo "\n";
        ++$row;
    }
    function OutEnd()
    {
    }
}


?>
