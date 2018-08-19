<?php
header("Content-type: image/png");

$maxFreq = $_GET['maxFreq'];
$maxVal = $_GET['maxVal'];
$minVal = $_GET['minVal'];
$vaRA = $_GET['valsRA'];
$dPtsRA = $_GET['dPtsRA'];
$str = $_GET['str'];
$title = $_GET['title'];
$valsRA = unserialize($vaRA);
$dataPtsRA = unserialize($dPtsRA);
$maxFreq += 1;

$minVal =0;
if($maxVal == $minVal){
    $maxVal += 1;
    $minVal -= 1;
}
if($maxVal > 10){
	$maxVal =(ceil($maxVal/10)*10);
}

$wWidth  = ($maxVal-$minVal) *30;  //window width    x
$wHeight =  $maxFreq * 20;         //window height   y
$wWidth  = 500;  //window width    x
$wHeight =  500;         //window height   y


$oWidth   = 20; //offest   x
$oHeight  = 20; //offset   y
$oNum     =  6; //offset   numbers
$oIndent  = 20; //offset   indent

$gWidth  = $wWidth  - $oWidth;  //graph width    x
$gHeight = $wHeight - $oHeight; //graph height   y

$im = imagecreate($wWidth,$wHeight);
$white = imagecolorallocate($im,255,255,255);
$gray = imagecolorallocate($im,200,200,200);
$grey = imagecolorallocate($im,150,150,150);
$black = imagecolorallocate($im,0,0,0);
$red = imagecolorallocate($im,255,0,0);
$green = imagecolorallocate($im,0,255,0);
$blue = imagecolorallocate($im,0,0,255);
$orange = imagecolorallocate($im,255,150,0);
$yellow = imagecolorallocate($im,255,255,0);
$purple = imagecolorallocate($im,255,0,255);
$cyan = imagecolorallocate($im,0,255,255);


$xInit = $oWidth +$oIndent ;
$yInit = $gHeight -$oIndent;

////////////////////////////////////////
//freq
$currFreq = 0;
$x = $xInit;
$y = $yInit;
while($y >= $oHeight){
    imageline($im,$x,$y,$gWidth,$y,$gray);
    imagestring($im,3,$x-$oIndent,$y-$oNum,$currFreq, $black);
	$y -= ($gHeight-$oHeight-$oIndent)/($maxFreq);
	$currFreq++;

}
////////////////////////////////////////
//vals
$currVal = $minVal;
$x = $xInit;
$y = $yInit;
while($currVal <= $maxVal){
	if ($currVal <= 9){
		if($str == TRUE){
   			imagestringup($im,3,$x,$y-2,$valsRA[$currVal]['str'],$black);
		}else{
		    imagestring($im,3,$x-($oNum/2),$y,$currVal,$black);
		}
	}else{
		if($str == TRUE){
			imagestringup($im,3,$x,$y-2,$valsRA[$currVal]['str'],$black);
		}else{
		    imagestring($im,3,$x-$oNum,$y,$currVal,$black);
		}
	}
	for($i=1;$i<=((ceil($maxVal/10)*10)/10);$i++){
		$x += ($gWidth-$oWidth-$oIndent)/($maxVal-$minVal);
		$currVal++;
	}
}
////////////////////////////////////////
//data line
if($str == FALSE){
    $x = $xInit;
	$y = $yInit;
	$xpre = $x;
	$ypre = $y;
	foreach($dataPtsRA as $dRA){
		if($xpre == $xInit and $ypre == $yInit){
		    $xpre = ((($gWidth-$oWidth-$oIndent)/($maxVal-$minVal))*($dRA['x'])) - ((($gWidth-$oWidth-$oIndent)/($maxVal-$minVal))*($minVal)) ;
			$ypre =  $gHeight-(($gHeight-$oHeight-$oIndent)/($maxFreq))*($dRA['y']);
			$xpre += $xInit;
			$ypre -= $oIndent;
		}
    	$x = ((($gWidth-$oWidth-$oIndent)/($maxVal-$minVal))*($dRA['x'])) - ((($gWidth-$oWidth-$oIndent)/($maxVal-$minVal))*($minVal)) ;
		$y =  $gHeight-(($gHeight-$oHeight-$oIndent)/($maxFreq))*($dRA['y']);
    	imageline($im,$xpre,$ypre,$x+$xInit,$y-$oIndent,$orange);
		$xpre =$x+$xInit;
		$ypre =$y-$oIndent;
	}
}
////////////////////////////////////////
//data bars
$x = $xInit;
$y = $yInit;
$oVal = 0;
$rgb = 0;
foreach($valsRA as $vRA){
	$x = ((($gWidth-$oWidth-$oIndent)/($maxVal-$minVal))*($vRA['val'])) - ((($gWidth-$oWidth-$oIndent)/($maxVal-$minVal))*($minVal)) ;
	$y =  $gHeight-(($gHeight-$oHeight-$oIndent)/($maxFreq))*($vRA['freq']);

	if($rgb%6 == 0){
		$colour = $red;
	}elseif($rgb%6 == 1){
		$colour = $green;
	}elseif($rgb%6 == 2){
		$colour = $blue;
	}
	elseif($rgb%6 == 3){
		$colour = $yellow;
	}
	elseif($rgb%6== 4){
		$colour = $purple;
	}
	elseif($rgb%6 == 5){
		$colour = $cyan;
	}
	$rgb ++;


	imagefilledrectangle($im,$x+$xInit-1,$gHeight-$oIndent,$x+$xInit+1,$y-$oIndent,$colour);
	if($str == FALSE){
		if($vRA['val']<10){
			imagestring($im,1,$x+$xInit-(strlen($vRA['val'])*($oNum/2)),$y-$oIndent-($oNum*2)-$oVal,$vRA['val'],$black);
		}else{
			imagestring($im,1,$x+$xInit-(strlen($vRA['val'])*($oNum/3)),$y-$oIndent-($oNum*2)-$oVal,$vRA['val'],$black);
		}
	}
	if($oVal == 0){
	    $oVal = 10;
	}else{
	    $oVal = 0;
	}
}
////////////////////////////////////////
//border
imageline($im,$xInit,$yInit,$xInit,$oHeight,$black);
imageline($im,$xInit,$yInit,$gWidth,$yInit,$black);
////////////////////////////////////////
//labels
imagestringup($im,3,1,$gHeight/2,'Frequency',$black);
imagestring($im,3,(($gWidth-$oIndent-$oWidth-strlen($title))/2)-strlen($title),$gHeight,$title,$black);
////////////////////////////////////////
imagepng($im);
?>
