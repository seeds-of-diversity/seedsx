<?php
define( "SITEROOT", "../" );
include_once( SITEROOT."site.php" );
include_once( "q/QServer.php" );

$mode = "";
$parms = array();

$lang = SEEDStd_ArraySmartVal( $_REQUEST, 'lang', array( 'EN', 'FR' ) );

$oQ = new QServer( $lang );

if( ($q = SEEDSafeGPC_GetStrPlain( 'q' )) ) {
    @list($mode,$sParms) = explode( '|', $q, 2 );    // @ suppresses warning re sParms if explode only returns one element
    $parms = SEEDStd_ParmsURL2RA( $sParms );
}

if( empty($mode) ) {
    $mode = SEEDSafeGPC_GetStrPlain( 'mode' );
    $parms = $_REQUEST;  // different for each mode
}

Site_Log( "q.log", $mode." !! ".$q."\n" );


$sOut = "";
$raOut = array();
$fmtOut = 'json';

switch( $mode ) {
    case 'get':
        if( @$parms['qcode'] ) {
            //if( @$parms['SEEDSlider'] ) {
            //    $raOut = $oQ->QFetchSEEDSlider( $parms['qcode'], $parms );
            //} else {
            //    $raOut = $oQ->QObjItemRelative( $parms['qcode'], @$parms['qParm'] );
            //}
        } else if( @$parms['qcodes'] ) {
            $raQCodes = explode( ',', $parms['qcodes'] );
            $raOut = $oQ->QFetchSEEDSlider2( $raQCodes, $parms );
        }
        break;
    case 'get9':
        if( @$parms['qcode'] ) {
            $raOut = $oQ->QFetchSEEDSlider9( $parms['qcode'], $parms );
        }
        break;
    default:
        break;
}


/* Output the data
 */
switch( $fmtOut ) {
    case 'plain':  echo $sOut;                    break;
    case 'json':   echo json_encode( $raOut );    break;
    case 'xml':                                   break;

    default: break;
}


exit;
?>

<?php
define( "SITEROOT", "../" );
include_once( SITEROOT."site.php" );
include_once( "QServer.php" );

$mode = "";
$parms = array();

$oQ = new QServer();

if( ($q = SEEDSafeGPC_GetStrPlain( 'q' )) ) {
    @list($mode,$sParms) = explode( '|', $q, 2 );    // @ suppresses warning re sParms if explode only returns one element
    $parms = SEEDStd_ParmsURL2RA( $sParms );
}

if( empty($mode) ) {
    $mode = SEEDSafeGPC_GetStrPlain( 'mode' );
    $parms = $_REQUEST;  // different for each mode
}

$sOut = "";
$raOut = array();
$fmtOut = 'json';

switch( $mode ) {
    case 'get':
        if( @$parms['qCode'] ) {
            $raOut = $oQ->QObjItemRelative( $parms['qCode'], $parms['qParm'] );
        }
//        $raOut = array( 'qCode'     => rand( 1, 1000 ),
//                        'html'      => ("Got ".$parms['qparm']." for ".$parms['qcode']),
//                        'htmlSmall' => ($parms['qparm']." = ".$parms['qcode'])
//                      );
        break;

    default:
        break;
}


/* Output the data
 */
switch( $fmtOut ) {
    case 'plain':  echo $sOut;                    break;
    case 'json':   echo json_encode( $raOut );    break;
    case 'xml':                                   break;

    default: break;
}



?>