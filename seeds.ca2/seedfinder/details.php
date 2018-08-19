<?php
	header('Content-Type: application/json');
	$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';

	$post = array(
		'qcmd' => 'srcSources',
		'kPcv' => $id
	);

	//$ch = curl_init('http://localhost/~bob/seeds.ca2/app/q/index.php');
	$ch = curl_init('https://seeds.ca/app/q/index.php');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post);

	// curl was giving error 60 Peer's Certificate issuer is not recognized so this means we trust seeds.ca
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

	$response = curl_exec($ch);
	curl_close($ch);

	echo $response;
?>