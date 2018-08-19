<?php
header("Content-type: image/png");


$arr = $_GET['arr'];
$arr2= $_GET['arr2'];
$info= $_GET['info'];
$arrval=unserialize($arr);
$arrval2 = unserialize($arr2);
$infoArray=unserialize($info);
//$arrval = array(12,123,21,32,77);//,85,166,176,163,121);

$height = 260;

$width = 430;

$im = imagecreate($width,$height+50);

$white = imagecolorallocate($im,255,255,255);

$gray = imagecolorallocate($im,200,200,200);

$black = imagecolorallocate($im,0,0,0);

$red = imagecolorallocate($im,255,0,0);

$green = imagecolorallocate($im,0,255,0);

$blue = imagecolorallocate($im,0,0,255);


$x = 121;

$y = 11;

$num = 0;

//while($x<=$width){ //&& $y<=$height){
while($x<=430){
$prcnt = ((($height-50)-($y-1))/($height-60))*100;
if($prcnt >= 0){
imageline($im, 121, $y, $width-10, $y, $gray);
}
imageline($im, $x, 11, $x, $height-50, $gray);
if($prcnt >= 0){
imagestring($im,2,101,$y-10,$prcnt.'%',$red);
}
imagestring($im,2,$x-3,$height-40,$num,$red);


$x += 15;

$y += 20;

$num++;

}echo $mc1;

$tx = 118.5;

$ty = 210;

foreach($arrval as $values){

$cx = $tx + 1.5;

$cy = 211-$values*2;
if($tx>118.5){
imageline($im,$tx,$ty,$cx,$cy,$blue);
}
//imagestring($im,5,$cx-3,$cy-13,'.',$blue);

$ty = $cy;

$tx = $cx;

}

$tx = 118.5;

$ty = 210;

foreach($arrval2 as $values){

$cx = $tx + 1.5;

$cy = 211-$values*2;
if($tx>118.5){
imageline($im,$tx,$ty,$cx,$cy,$green);
}
//imagestring($im,5,$cx-3,$cy-13,'.',$blue);

$ty = $cy;

$tx = $cx;

}


imageline($im, 120, 11, 120, $height-50, $black);

imageline($im, 120, $height-49, $width-10, $height-49, $black);

imagestring($im,3,210,$height-20,'Time (years)',$red);
imagestring($im,3,10,80,'Germination',$red);
imagestring($im,3,40,90,'(%)',$red);


if ($arrval2 != ""){
imagestring($im,3,10,$height+10,'---',$blue);
imagestring($im,3,45,$height+10,$infoArray['0'].'% Moisture content for '.$infoArray['3'].' at '.$infoArray['2'].' C',$black);
imagestring($im,3,10,$height+30,'---',$green);
imagestring($im,3,45,$height+30,$infoArray['1'].'% Moisture content for '.$infoArray['3'].' at '.$infoArray['2'].' C',$black);
}
else{
imagestring($im,3,10,$height+10,'---',$blue);
imagestring($im,3,45,$height+10,$infoArray['0'].'% Moisture content for '.$infoArray['3'].' at '.$infoArray['2'].' C',$black);

}

imagepng($im);
?>
