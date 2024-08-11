<?php

header('Content-Type: application/json');

$qurl = "https://seeds.ca/app/q/index.php";
//$qurl = "http://localhost/~bob/seedsx/seeds.ca2/app/q/index.php";

switch( @$_REQUEST['cmd'] ) {
    case 'topchoices':
        $post = array(
            'qcmd' => 'srcCultivarSearch',
            'sMode' => 'TopChoices'
        );
        break;

    case 'find':
        $post = array(
            'qcmd' => 'srcCultivarSearch',
        );
        if( ($p = intval(@$_REQUEST['sfAp_sp'])) )       $post['kSp'] = $p;
        if( ($p = intval(@$_REQUEST['sfAp_organic'])) )  $post['bOrganic'] = $p;
        if( ($p = intval(@$_REQUEST['sfAp_bulk'])) )     $post['bBulk'] = $p;
        if( ($p = @$_REQUEST['sfAp_region']) )           $post['sRegions'] = $p;
        if( ($p = @$_REQUEST['sfAp_srch']) )             $post['sSrch'] = $p;
        break;

    case 'suppliers':
        $post = array(
            'qcmd' => 'srcSources'
        );
        if( !($post['kPcv'] = intval(@$_REQUEST['kPcv'])) ) {
            goto done;
        }
        break;

    case 'profile':
        $qurl = "https://seeds.ca/office/sl/profiles/q.php";
        $post = array(
            'sp' => @$_REQUEST['sp'],
            'cv' => @$_REQUEST['cv'],
        );
        break;

    default:
        goto done;
}

$ch = curl_init( $qurl );
curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
curl_setopt( $ch, CURLOPT_POSTFIELDS, $post );

// curl was giving error 60 Peer's Certificate issuer is not recognized so this means we trust seeds.ca
curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );

$s = curl_exec( $ch );

curl_close( $ch );

$s = preg_replace( "/null\:(\{)([^\}]*)(\})\,/", '', $s );
echo $s;

done:

?>
