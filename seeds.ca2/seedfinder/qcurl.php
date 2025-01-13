<?php

header('Content-Type: application/json');

$qurl  = "https://seeds.ca/app/q/index.php";
$qurl2 = "https://seeds.ca/app/q2/index.php";

//$qurl  = "http://localhost/~bob/seedsx/seeds.ca2/app/q/index.php";
//$qurl2 = "http://localhost/~bob/seedsx/seeds.ca2/app/q2/index.php";


switch( @$_REQUEST['cmd'] ) {
    case 'topchoices':
        $post = array(
            'qcmd' => 'srcCultivars',
            'sMode' => 'TopChoices'
        );
        break;

    case 'find':
        $qurl = $qurl2;
        $post = ['qcmd' => 'srcSrcCvCultivarList'];
        if( ($p = intval(@$_REQUEST['sfAp_sp'])) )       $post['kSp'] = $p;
        if( ($p = intval(@$_REQUEST['sfAp_organic'])) )  $post['bOrganic'] = $p;
        if( ($p = intval(@$_REQUEST['sfAp_bulk'])) )     $post['bBulk'] = $p;
        if( ($p = @$_REQUEST['sfAp_region']) )           $post['sRegions'] = $p;
        if( ($p = @$_REQUEST['sfAp_srch']) )             $post['sSrchP'] = $p;
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
        $qurl = $qurl2;   // "https://seeds.ca/office/sl/profiles/q.php";
        $post = ['qcmd' => 'slprofile-minireport',
            'kPcv' => intval(@$_REQUEST['kPcv']),
        ];
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

done:;
