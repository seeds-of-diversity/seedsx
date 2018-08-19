<?php

//TODO:  put this in a tab of mbr_getlist or some kind of sub-dialog


/* Output donation info list
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

    private $p_dDonorStart;
    private $p_dDonorEnd;

    function MbrGetList( &$kfdb, &$sess )
    {
        $this->kfdb =& $kfdb;
        $this->sess =& $sess;
        $this->year = date("Y");

        $this->p_dDonorStart = SEEDSafeGPC_GetStrPlain('dDonorStart');     if( !$this->p_dDonorStart ) $this->p_dDonorStart = date("Y")."-01-01";
        $this->p_dDonorEnd   = SEEDSafeGPC_GetStrPlain('dDonorEnd');       if( !$this->p_dDonorEnd )   $this->p_dDonorEnd = date("Y-m-d");

    }

    function DrawForm()
    {
        $s = "<FORM method='get' action='{$_SERVER['PHP_SELF']}' target='_blank'>"
           // .SEEDForm_Text( 'yMbr', $this->p_yMbr, "Members from and above" )."<BR/>"
            .SEEDForm_Text( 'dDonorStart', $this->p_dDonorStart, "Donors from (yyyy-mm-dd)" )
            .SEEDStd_StrNBSP("   ")
            .SEEDForm_Text( 'dDonorEnd', $this->p_dDonorEnd, "to (yyyy-mm-dd)" )
            .SEEDStd_StrNBSP("   ")." -- Blank fields mean no start/end<BR/><BR/>"
            // ."<FIELDSET style='width:400px'><LEGEND>Where</LEGEND>"
           // .SEEDForm_Radio( 'bNotCanada', 0, $this->p_bNotCanada, 'Canada' )."<BR/>"
           // .SEEDForm_Radio( 'bNotCanada', 1, $this->p_bNotCanada, 'Not Canada' )."<BR/>"
           // ."</FIELDSET>"
           // ."<FIELDSET style='width:400px'><LEGEND>Language</LEGEND>"
           // .SEEDForm_Radio( 'sLang', '',   $this->p_sLang, 'All (no filter)' )."<BR/>"
           // .SEEDForm_Radio( 'sLang', 'EN', $this->p_sLang, 'English (not Bilingual below)' )."<BR/>"
           // .SEEDForm_Radio( 'sLang', 'BI', $this->p_sLang, 'Bilingual (lang=F OR province=QC OR province=NB)' )."<BR/>"
           // ."</FIELDSET>"
            ."<INPUT type='submit' name='mode' value='XLS'/><BR/>"
            ."<INPUT type='submit' name='mode' value='CSV'/>"
            ."</FORM>";
        echo $s;
    }

    function DrawList()
    {
        $this->OutStart();

        $dbc = $this->kfdb->CursorOpen( "SELECT M._key as M_key,M.firstname as firstname,M.lastname as lastname,M.company as company,"
                                              ."M.dept as dept,M.address as address,M.city as city,M.province as province,M.postcode as postcode,"
                                              ."M.email as email,M.lang as lang,M.donation_date as donation_date,M.donation as donation,"
                                              ."P.name as pname,A.public_name as public_name "
                                              ."FROM mbr_contacts M LEFT JOIN seeds.sl_adoption A ON (M._key=A.fk_mbr_contacts AND M.donation_date=A.d_donation) LEFT JOIN seeds.sl_pcv P ON (P._key=A.fk_sl_pcv) "
                                              ."WHERE M.donation_date>='{$this->p_dDonorStart}' AND M.donation_date<='{$this->p_dDonorEnd}' ORDER BY M.donation_date" );
        $row = 0;
//        $this->OutRow( $row, array($this->sql) );

        $raTable = array();
        while( $ra = $this->kfdb->CursorFetch($dbc,KFDB_RESULT_ASSOC) ) {

            $raTable[] = $ra;
        }
        $this->OutRow( $row, array("") );
        $this->OutRow( $row, array("Listing ".count($raTable)." people") );
        $this->OutRow( $row, array("") );

        $this->OutRow( $row, array("mbrid","firstname","lastname","company","dept","address","city","province","postcode","email","lang",
                                   "donation_date","donation","pname","public_name") );

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
