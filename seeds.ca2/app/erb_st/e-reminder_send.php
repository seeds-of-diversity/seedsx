<?php

define( SITEROOT, "../../" );
include_once( SITEROOT."site.php" );
include_once( STDINC."SEEDDate.php" );
include_once( STDINC."SEEDMetaTable.php" );
include_once( SEEDCOMMON."siteStart.php" );

list($kfdb) = SiteStart();

$oBucket = new SEEDMetaTable_StringBucket( $kfdb );

$sEmails = $oBucket->GetStr( 'erbst_snack', 'email_list' );
$sSubj   = $oBucket->GetStr( 'erbst_snack', 'email_subj' );
$sFrom   = $oBucket->GetStr( 'erbst_snack', 'email_from' );
$sBcc    = $oBucket->GetStr( 'erbst_snack', 'email_bcc' );
$sBody   = $oBucket->GetStr( 'erbst_snack', 'email_body' );
$bEnable = intval( $oBucket->GetStr( 'erbst_snack', 'bEnable' ) );


if( !$bEnable )  die( "e-reminder is not enabled" );


// parse sEmails and get the dates.  For each date, determine whether it is less than 7 days in advance, and send an individual mail
$s = "<TABLE cellpadding='10' cellspacing='10'>";
$raEmails = SEEDStd_EnumTuplesUnpack( SEEDStd_ParmsURL2RA( $sEmails ), array('e','d') );
foreach( $raEmails as $ra ) {
    $s .= "<TR>"
         ."<TD>{$ra['e']}</TD><TD>{$ra['d']}</TD>"
         ."<TD>";

    $d = SEEDDateDB2Unixtime( $ra['d'] );
    $d = intval(($d - time()) / 3600 / 24);
    if( $d < 0 ) {
        $s .= "PAST";
    } else {
        $s .= "$d days from now";
    }

    $s .= "</TD><TD>";
    if( $d > 0 && $d <=7 ) {
        $sNewBody = $sBody;
        $sNewBody = str_replace( "[[email_to]]", $ra['e'], $sNewBody );
        $sNewBody = str_replace( "[[date]]",     $ra['d'], $sNewBody );

        $s .= "Sending mail: ".mail( $ra['e'], $sSubj, $sNewBody, "From: $sFrom \r\nBcc: $sBcc \r\n" );
        $s .= "From: $sFrom \r\n Bcc: $sBcc \r\n";
    }

    $s .= "</TD></TR>";
}
$s .= "</TABLE>";

echo $s;

// echo "Sending mail : ".mail( $sEmails, $sSubj, $sBody, "From: Heather Hadden <franzandheather@sympatico.ca>" );

?>
