<?php

if( !defined( "SITEROOT" ) ) exit;

include_once( SEEDCOMMON."sl/q/Q.php" );
include_once( "../../bauta/q/QServer.php" );    // deprecate

/* url: q/index.php?qcmd={cmd}
 *                 &qfmt={fmt}
 *                 &qname={name}
 *                 &qtitle={title}
 *                 &other-cmd-specific-parms
 */

$oApp = SEEDConfig_NewAppConsole( ['db'=>Q_DB] );     // either seeds1 or seeds2 depending on which you are logged into (use index.php or q2.php from your ajax)

if( ($cmd = SEEDSafeGPC_GetStrPlain('qcmd')) || /* deprecate */ ($cmd = SEEDSafeGPC_GetStrPlain('cmd')) ) {
    $raQParms = array();
    foreach( $_REQUEST as $k => $v ) {
        if( $k != 'cmd' ) {
            $raQParms[$k] = SEEDSafeGPC_MagicStripSlashes($v);
        }
    }

    list($kfdb,$sess,$lang) = SiteStartSessionAccountNoUI();
    $oQ = new Q( $kfdb, $sess, $oApp, array('lang' => $lang) );

    // the charset returned by this query will always be utf8, unless this is reversed below
    $oQ->bUTF8 = true;
    $sCharset = "utf-8";
    $rQ = $oQ->Cmd( $cmd, $raQParms );

    if( !($name  = (@$raQParms['qname']))  && !($name  = (@$rQ['raMeta']['name'])) )  $name = $cmd;
    if( !($title = (@$raQParms['qtitle'])) && !($title = (@$rQ['raMeta']['title'])) ) $title = $cmd;

    $fmtOut = SEEDSafeGPC_Smart( 'qfmt', array( 'json' ) );
    if( isset($_REQUEST['fmt']) && !isset($_REQUEST['qfmt']) ) { // deprecate
        $fmtOut = SEEDSafeGPC_Smart( 'fmt', array( 'json' ) );
    }

    switch( $fmtOut ) {
        case 'plain':    echo $rQ['sOut'];         break;
        case 'plainRA':  var_dump( $rQ['raOut'] ); break;
        case 'json':
            // Allow any domain to make ajax requests - see CORS
            // Note that this is even necessary for http://www.seeds.ca to access https://www.seeds.ca/.../q because the
            // CORS access control policy is per (scheme|domain|port)
            header( "Access-Control-Allow-Origin: *" );
            echo json_encode( $rQ );
            break;
        /* not tested but used for cross-site ajax
        case 'jsonp':
            $raOut['name'] = "response";
            echo $_GET['callback']."(".json_encode($raOut).");";
            break;
        */
        case 'csv':
            if( $rQ['bOk'] ) {
                include_once( STDINC."SEEDTable.php" );
                $sCharset = 'utf-8';
                header( "Content-Type:text/plain; charset=$sCharset" );

                SEEDTable_OutputCSVFromRARows( $rQ['raOut'],
                                   array( //'columns' => array_keys($rQ['raOut'][0]),  use default columns
                                          ) );
            }
            break;

        case 'xls':
            if( $rQ['bOk'] ) {
                include_once( STDINC."SEEDTable.php" );

                // PHPExcel sends the header( Content-Type )
                // N.B. the data has to be utf8 or PHPExcel will fail to write it
                SEEDTable_OutputXLSFromRARows( $rQ['raOut'],
                                   array( 'columns' => array_keys($rQ['raOut'][0]),
                                          'filename'=>"$name.xls",
                                          'created_by'=>$sess->GetName(),
                                          'title'=>'$title'
                                          ) );
            }
            break;

        case 'xml':
            if( $rQ['bOk'] ) {
                $sCharset = 'utf-8';
                header( "Content-Type:text/xml; charset=$sCharset" );

                $s = "<q name='$name'>";
                foreach( $rQ['raOut'] as $row ) {
                    $s .= "<qrow>";
                    foreach( $row as $k => $v ) {
                        $k = str_replace( ' ', '-', $k );
                        $s .= "<$k>$v</$k>";
                    }
                    $s .= "</qrow>";
                }
                $s .= "</q>";

                echo $s;
            }
            break;

        default:
            break;
    }

    Site_Log( "q.log", date("Y-m-d H:i:s")."\t"
                      .$_SERVER['REMOTE_ADDR']."\t"
                      .intval(@$rQ['bOk'])."\t"
                      .$cmd."\t"
                      .(@$rQ['sLog'] ? : "") );

    exit;
}


// above is the right way to do it
// THE REST IS DEPRECATED

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
/* not tested but used for cross-site ajax
    case 'jsonp':
        $raOut['name'] = "response";
        echo $_GET['callback']."(".json_encode($raOut).");";
        break;
*/
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
