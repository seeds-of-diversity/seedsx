<?php
/* mbrCommon.php
 *
 * mbr definitions that are shared between seeds.ca and office
 */


function MbrExpiryDate2Code( $sDate )
{
    if( $sDate == '2100-01-01' ) return( 'A' );
    if( $sDate == '2200-01-01' ) return( 'L' );
    if( $sDate == '2300-01-01' ) return( 'C' );   // change this date to 2300-01-01 in the database, the uploader, and here
    return( NULL );
}

function MbrExpiryCode2Label( $sCode, $lang = "EN" )
{
    if( $sCode == 'A' || $sCode == 'a' )  return( 'Automatic' );
    if( $sCode == 'L' )                   return( 'Lifetime' );
    if( $sCode == 'C' )                   return( 'Complimentary' );
    return( NULL );
}

function MbrExpiryCode2Date( $sCode )
{
    if( $sCode == 'A' || $sCode == 'a' )  return( '2100-01-01' );
    if( $sCode == 'L' )                   return( '2200-01-01' );
    if( $sCode == 'C' )                   return( '2300-01-01' );
    return( NULL );
}

function MbrExpiryDate2Label( $sDate, $lang = "EN" )
{
    if( ($code = MbrExpiryDate2Code( $sDate )) ) {
        return( MbrExpiryCode2Label( $code, $lang ) );
    }

    if( !( $y = intval( substr( $sDate, 0, 4 ) )) ) return( "" );

    return( ($lang ? "Dec" : "D&eacute;c")." ".$y );
}

function MbrDrawAddressBlockFromRA( $raMbr )
{
    return( MbrDrawAddressBlock( $raMbr['firstname'], $raMbr['lastname'], $raMbr['firstname2'], $raMbr['lastname2'], $raMbr['company'], $raMbr['dept'],
                                 $raMbr['address'], $raMbr['city'], $raMbr['province'], $raMbr['postcode'], $raMbr['country'] ) );
}

function MbrDrawAddressBlock( $firstname, $lastname, $firstname2, $lastname2, $company, $dept, $addr, $city, $prov, $postcode, $country, $fmt = 'HTML' )
{
    if( $fmt == 'HTML' ) {
        // The container should use style='white-space: nowrap' to prevent breaking in weird places e.g the middle of a postal code
        //                      and style='margin:...' to pad around the address block (no margin is set here)
        $topMargin = "";
        $leftMargin = "";
        $lnbreak = "<br/>";
    } else if( $fmt == 'PDF' ) {
        // PDF_Label gives no margin: leading \n is for top margin, spaces for left margin
        //
        // Maybe some complex formatting is possible using FPDF::GetStringWidth() e.g. breaking after a very long city+prov to put postcode on next line
        $topMargin = "\n";
        $leftMargin = "  ";
        $lnbreak = "\n";
    }

    // firstname(s)/lastname(s)
    $f1 = $firstname; $f2 = $firstname2;
    $l1 = $lastname;  $l2 = $lastname2;

    if( !$f2 && !$l2 ) {                // name1 only (which is blank if all are empty)
        $name = trim("$f1 $l1");
    } else if( !$f1 && !$l1 ) {         // name2 only
        $name = trim("$f2 $l2");
    } else if( $l1 == $l2 ) {           // both names, lastname is the same
        $name = trim("$f1 & $f2 $l2");
    } else {                            // both names, lastnames are different
        $name = trim("$f1 $l1 & $f2 $l2");
    }

    if( $company ) {
        if( $name ) $name .= $lnbreak.$leftMargin;
        $name .= $company;
    }
    if( $dept ) {
        if( $name ) $name .= $lnbreak.$leftMargin;
        $name .= $dept;
    }

    $text = $topMargin.$leftMargin.$name.$lnbreak.$leftMargin.$addr.$lnbreak.$leftMargin.$city." ".$prov." ".$postcode;
    if( !in_array( $country, array('','Canada','CANADA') ) ) {
        $text .= $lnbreak.$leftMargin.$country;
    }
    return( $text );
}

?>
