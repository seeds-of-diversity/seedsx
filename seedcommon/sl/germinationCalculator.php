<?php

$page = $_GET['page'];

class calc {
     var $m;
     var $t;
	 var $c;
	 var $s;
          function calc1($m,$t,$c,$type)
          {
          			$ty = array("barley"    => "Barley",
          						"wheat"     => "Wheat",
          						"maize"     => "Maize",
          						"pea"       => "Pea",
          						"gbean"     => "Garden Bean",
          						"rseed"     => "Rape Seed",
          						"soybean"   => "Soybean",
          						"sunflower" => "Sunflower",
          						"linseed"   => "Linseed",
          						"sugarbeet" => "Sugarbeet",
          						"onion"     => "Onion",
          						"salad"     => "Salad",
          						"tomato"    => "Tomato");
                   	$result =$c['0'] - $c['1']*log10($m) -$c['2']*$t - $c['3']*$t*$t;

					$r = pow(10, $result); //r is in days

					$ryears = 0;
					if ($r > 365){
						$ryears = ($r/365);
						$ryears = floor($ryears);
						$r -= (365 * $ryears);
					}

					$rdays = round($r, 0);
                    $s = "Internal Moisture = $m%, Temperature $t&#176C, Seed Type: ".$ty[$type]." <br/><br/>";
                    $s .= "Result = In $ryears Year(s) $rdays Day(s) ";

					return($s);


          }

          function calc2($ig,$pt)
          {
			$probitTable = $pt;

			//echo($ig."<br>");
			if ($ig<=9){
				$v = $probitTable['0'][$ig['0']];
			}else{
     			$v = $probitTable[$ig['0']][$ig['1']];
			}
			$v -= 1;
			//echo($v."<br>");
			for ($i=0; $i<=9; $i++){
				for ($j=0; $j<=9; $j++){

					if(strval($v) == strval($probitTable[$i][$j])){
						$s = "In that time Initial Germination of $ig% Drops to $i$j%";
					}elseif(strval($v) <= strval($probitTable[$i][$j])){
						if(strval($j) != '0'){
						    $k = $j - 1;
						    $s = "Initial Germination of $ig% Drops to $i$k%";
						    return($s);
						}else{
						    $l = $i - 1;
						    $k = 9;
						    $s = "Initial Germination of $ig% Drops to $l$k%";
						    return($s);
						}

					}
				}
			}

                    return($s);
          }

          function calc3($m,$t,$c,$type,$ig,$pt,$ti){ //After x years what will $g be
				$si =$c['0'] - $c['1']*log10($m) -$c['2']*$t - $c['3']*$t*$t;
				$sigma = pow(10, $si);

				if ($ig<=9){
					$ki = $pt['0'][$ig['0']];
				}else{
     				$ki = $pt[$ig['0']][$ig['1']];
				}

				$kf = $ki - (1/$sigma)*($ti*365);


          		for ($i=0; $i<=9; $i++){
					for ($j=0; $j<=9; $j++){
						if(strval($kf) == strval($pt[$i][$j])){
							$ge = "$i$j";

							return($ge);
						}elseif(strval($kf) <= strval($pt[$i][$j])){
							if(strval($j) != '0'){
						    	$k = $j - 1;
						    	$ge = "$i$k";

						    	return($ge);
							}else{

						    	$l = $i - 1;
						    	$k = 9;
						    	$ge = "$l$k";
						    	if($ge<'0'){$ge='0';}
						    	return($ge);
						}

						}
					}
          		}



          }

          function calc4($m,$t,$c,$type,$ig,$pt,$g){ // how long will it take to get to x% $g
				$si =$c['0'] - $c['1']*log10($m) -$c['2']*$t - $c['3']*$t*$t;
				$sigma = pow(10, $si);

				if ($ig<=9){
					$ki = $pt['0'][$ig['0']];
				}else{
     				$ki = $pt[$ig['0']][$ig['1']];
				}
				if ($g<=9){
					$kf = $pt['0'][$g['0']];
				}else{
     				$kf = $pt[$g['0']][$g['1']];
				}

				$d = $sigma*($ki-$kf);
				return ($d);
          }

          function draw_graph($m1,$m2,$t,$c,$type,$ig,$pt){

          			$values = array($ig);


				$ti=0.1;
				for ($i=1; $i<=200; $i++){

					$temp = $this->calc3($m1,$t,$c,$type,$ig,$pt,$ti);

	                if ($temp>=0){
					$values[$i] = $temp;
	                }
	                 $ti+=0.1;
				}
				if($m2!="a"){
				$values2 = array($ig);
				$ti=0.1;
				for ($i=1; $i<=200; $i++){

					$temp = $this->calc3($m2,$t,$c,$type,$ig,$pt,$ti);

	                if ($temp>=0){
					$values2[$i] = $temp;
	                }
	                 $ti+=0.1;
				}
				}
				$ty = array("barley"    => "Barley",
          						"wheat"     => "Wheat",
          						"maize"     => "Maize",
          						"pea"       => "Pea",
          						"gbean"     => "Garden Bean",
          						"rseed"     => "Rape Seed",
          						"soybean"   => "Soybean",
          						"sunflower" => "Sunflower",
          						"linseed"   => "Linseed",
          						"sugarbeet" => "Sugarbeet",
          						"onion"     => "Onion",
          						"salad"     => "Salad",
          						"tomato"    => "Tomato");
				$infoArray = array($m1,$m2,$t,$ty[$type]);
          		echo ("<Script>window.open('drawGerminationGraph.php?arr=".serialize($values)."&arr2=".serialize($values2)."&info=".serialize($infoArray)."','Germination Graph','width=470','height=400')</Script>");


    	  }




}
$calc = new calc();

echo "<TITLE>Germination Calculator</TITLE>";
echo "<form name='choice' action='?page=choice' method='POST'>
Calculator Method<br>
<input type=submit name='Method_1' value='Known Moisture Content'><br>
<input type=submit name='Method_2' value='Known Storage Method'><br>
</form>";

if($page == "choice" and isset($_POST['Method_1'])){
echo "<form name='solveChoice' action='?page=solve' method='POST'>
Known Moisture Content <br>
<input type=submit name='Solve_1_1' value='Draw Graph for all Germ % and Time'><br>
<input type=submit name='Solve_1_2' value='Calculate Germ % after a given time'><br>
<input type=submit name='Solve_1_3' value='Calculate Time it takes to go from one Germ % to Another'><br>
";
}

if($page == "choice" and isset($_POST['Method_2'])){
echo "<form name='solveChoice' action='?page=solve' method='POST'>
Known Storage Method <br>
<input type=submit name='Solve_2_1' value='Draw Graph for all Germ % and Time'><br>
<input type=submit name='Solve_2_2' value='Calculate Germ % after a given time'><br>
<input type=submit name='Solve_2_3' value='Calculate Time it takes to go from one Germ % to Another'><br>
";
}

if($page == "solve" and isset($_POST['Solve_1_1']) or isset($_POST['Calc_btn_1'])){
$sm = $_REQUEST['m'];
$st = $_REQUEST['t'];
$sig = $_REQUEST['ig'];
$stype = $_REQUEST['type'];

echo "<form name='calc1' action='?page=calc' method='POST'>
Known Moisture Content <br>
Draw Graph for all Germ % and Time <br>
Internal Moisture Content %: <input type=text name=m value=$sm><br>
Temperature in degrees &#176C  &nbsp: <input type=text name=t value=$st><br>
<select name=type>
  <option value=$stype>Select Seed Type</option>
  <option value='barley'>Barley</option>
  <option value='wheat'>Wheat</option>
  <option value='maize'>Maize</option>
  <option value='pea'>Pea</option>
  <option value='gbean'>Garden Bean</option>
  <option value='rseed'>Rape Seed</option>
  <option value='soybean'>Soybean</option>
  <option value='sunflower'>Sunflower</option>
  <option value='linseed'>Linseed</option>
  <option value='sugarbeet'>Sugarbeet</option>
  <option value='onion'>Onion</option>
  <option value='salad'>Salad</option>
  <option value='tomatMethodo'>Tomato</option>
</select>
<br>
Initial Germ %: <input type=text name=ig value=$sig><br>
<input type=submit name='Calc_btn_1' value='Draw'><br>
</form>";
}
if($page == "solve" and isset($_POST['Solve_1_2']) or isset($_POST['Calc_btn_2'])){
//after x years where will germ % be at
$sm = $_REQUEST['m'];
$st = $_REQUEST['t'];
$sig = $_REQUEST['ig'];
$stype = $_REQUEST['type'];
$sti = $_REQUEST['ti'];


echo "<form name='calc2' action='?page=calc' method='POST'>
Known Moisture Content <br>
Calculate Germ % after a given time <br>
Internal Moisture Content %: <input type=text name=m value=$sm><br>
Temperature in degrees &#176C  &nbsp: <input type=text name=t value=$st><br>
<select name=type>
  <option value=$stype>Select Seed Type</option>
  <option value='barley'>Barley</option>
  <option value='wheat'>Wheat</option>
  <option value='maize'>Maize</option>
  <option value='pea'>Pea</option>
  <option value='gbean'>Garden Bean</option>
  <option value='rseed'>Rape Seed</option>
  <option value='soybean'>Soybean</option>
  <option value='sunflower'>Sunflower</option>
  <option value='linseed'>Linseed</option>
  <option value='sugarbeet'>Sugarbeet</option>
  <option value='onion'>Onion</option>
  <option value='salad'>Salad</option>
  <option value='tomato'>Tomato</option>
</select>
<br>
Initial Germ %: <input type=text name=ig value=$sig><br>
Time (years): <input type=text name=ti value=$sti><br>
<input type=submit name='Calc_btn_2' value='Calculate'><br>
</form>";
}
if($page == "solve" and isset($_POST['Solve_1_3']) or isset($_POST['Calc_btn_3'])){
//how long will it take to get to germ % x%
$sm = $_REQUEST['m'];
$st = $_REQUEST['t'];
$sig = $_REQUEST['ig'];
$stype = $_REQUEST['type'];
$sti = $_REQUEST['g'];

echo"<form name='calc3' action='?page=calc' method='POST'>
Known Moisture Content <br>
Calculate Time it takes to go from one Germ % to Another <br>
Internal Moisture Content %: <input type=text name=m value=$sm><br>
Temperature in degrees &#176C  &nbsp: <input type=text name=t value=$st><br>
<select name=type>
  <option value=$stype>Select Seed Type</option>
  <option value='barley'>Barley</option>
  <option value='wheat'>Wheat</option>
  <option value='maize'>Maize</option>
  <option value='pea'>Pea</option>
  <option value='gbean'>Garden Bean</option>
  <option value='rseed'>Rape Seed</option>
  <option value='soybean'>Soybean</option>
  <option value='sunflower'>Sunflower</option>
  <option value='linseed'>Linseed</option>
  <option value='sugarbeet'>Sugarbeet</option>
  <option value='onion'>Onion</option>
  <option value='salad'>Salad</option>
  <option value='tomato'>Tomato</option>
</select>
<br>
Initial Germ %: <input type=text name=ig value=$sig><br>
Ending Germ %: <input type=text name=g value=$sg><br>
<input type=submit name='Calc_btn_3' value='Calculate'><br>
<form>";
}
if($page == "solve" and isset($_POST['Solve_2_1']) or isset($_POST['Calc_btn_4'])){
$smc = $_REQUEST['mc'];
$st = $_REQUEST['t'];
$sig = $_REQUEST['ig'];
$stype = $_REQUEST['type'];
$sti = $_REQUEST['ti'];

echo "<form name='calc4' action='?page=calc' method='POST'>
Known Storage Method <br>
Draw Graph for all Germ % and Time <br>
<select name=mc>
	<option value=$smc>Select Storage Type</option>

	<option value='1'>Open Air Room: 07 days 40% RH</option>
	<option value='2'>Open Air Room: 14 days 40% RH</option>
	<option value='3'>Open Air Room: 21 days 40% RH</option>
	<option value='4'>Open Air Room: 28 days 40% RH</option>

	<option value='5'>Open Air Room: 07 days 35% RH</option>
	<option value='6'>Open Air Room: 14 days 35% RH</option>
	<option value='7'>Open Air Room: 21 days 35% RH</option>
	<option value='8'>Open Air Room: 28 days 35% RH</option>

	<option value='9'>Open Air Room: 07 days 30% RH</option>
	<option value='10'>Open Air Room: 14 days 30% RH</option>
	<option value='11'>Open Air Room: 21 days 30% RH</option>
	<option value='12'>Open Air Room: 28 days 30% RH</option>

	<option value='13'>Silica Gel: 1 days 15% RH</option>
	<option value='14'>Silica Gel: 2 days 15% RH</option>
	<option value='15'>Silica Gel: 3 days 15% RH</option>
	<option value='16'>Silica Gel: 4 days 15% RH</option>
	<option value='17'>Silica Gel: 5 days 15% RH</option>
	<option value='18'>Silica Gel: 6 days 15% RH</option>
	<option value='19'>Silica Gel: 7 days 15% RH</option>

</select><br>
Temperature in degrees &#176C  &nbsp: <input type=text name=t value=$st><br>
<select name=type>
  <option value=$stype>Select Seed Type</option>
  <option value='barley'>Barley</option>
  <option value='wheat'>Wheat</option>
  <option value='maize'>Maize</option>
  <option value='pea'>Pea</option>
  <option value='gbean'>Garden Bean</option>
  <option value='rseed'>Rape Seed</option>
  <option value='soybean'>Soybean</option>
  <option value='sunflower'>Sunflower</option>
  <option value='linseed'>Linseed</option>
  <option value='sugarbeet'>Sugarbeet</option>
  <option value='onion'>Onion</option>
  <option value='salad'>Salad</option>
  <option value='tomato'>Tomato</option>
</select>
<br>
Initial Germ %: <input type=text name=ig value=$sig><br>
<input type=submit name='Calc_btn_4' value='Draw'><br>
</form>";
}
if($page == "solve" and isset($_POST['Solve_2_2']) or isset($_POST['Calc_btn_5'])){
$smc = $_REQUEST['mc'];
$st = $_REQUEST['t'];
$sig = $_REQUEST['ig'];
$stype = $_REQUEST['type'];

   echo "<form name='calc2' action='?page=calc' method='POST'>
Known Storage Method <br>
	Calculate Germ % after a given time <br>
    <select name=mc>
	<option value=$smc>Select Storage Type</option>

	<option value='1'>Open Air Room: 07 days 40% RH</option>
	<option value='2'>Open Air Room: 14 days 40% RH</option>
	<option value='3'>Open Air Room: 21 days 40% RH</option>
	<option value='4'>Open Air Room: 28 days 40% RH</option>

	<option value='5'>Open Air Room: 07 days 35% RH</option>
	<option value='6'>Open Air Room: 14 days 35% RH</option>
	<option value='7'>Open Air Room: 21 days 35% RH</option>
	<option value='8'>Open Air Room: 28 days 35% RH</option>

	<option value='9'>Open Air Room: 07 days 30% RH</option>
	<option value='10'>Open Air Room: 14 days 30% RH</option>
	<option value='11'>Open Air Room: 21 days 30% RH</option>
	<option value='12'>Open Air Room: 28 days 30% RH</option>

	<option value='13'>Silica Gel: 1 days 15% RH</option>
	<option value='14'>Silica Gel: 2 days 15% RH</option>
	<option value='15'>Silica Gel: 3 days 15% RH</option>
	<option value='16'>Silica Gel: 4 days 15% RH</option>
	<option value='17'>Silica Gel: 5 days 15% RH</option>
	<option value='18'>Silica Gel: 6 days 15% RH</option>
	<option value='19'>Silica Gel: 7 days 15% RH</option>

</select><br>
	Temperature in degrees &#176C  &nbsp: <input type=text name=t value=$st><br>
	<select name=type>
  		<option value=$stype>Select Seed Type</option>
  		<option value='barley'>Barley</option>
  		<option value='wheat'>Wheat</option>
  		<option value='maize'>Maize</option>
  		<option value='pea'>Pea</option>
  		<option value='gbean'>Garden Bean</option>
  		<option value='rseed'>Rape Seed</option>
  		<option value='soybean'>Soybean</option>
  		<option value='sunflower'>Sunflower</option>
  		<option value='linseed'>Linseed</option>
  		<option value='sugarbeet'>Sugarbeet</option>
 		<option value='onion'>Onion</option>
  		<option value='salad'>Salad</option>
  		<option value='tomato'>Tomato</option>
	</select>
	<br>
	Initial Germ %: <input type=text name=ig value=$sig><br>
	Time (years): <input type=text name=ti value=$sti><br>
	<input type=submit name='Calc_btn_5' value='Calculate'><br>
	</form>";
}
if($page == "solve" and isset($_POST['Solve_2_3']) or isset($_POST['Calc_btn_6'])){
	$smc = $_REQUEST['mc'];
$st = $_REQUEST['t'];
$sig = $_REQUEST['ig'];
$stype = $_REQUEST['type'];
$sg = $_REQUEST['g'];
	echo"<form name='calc3' action='?page=calc' method='POST'>
Known Storage Method <br>
Calculate Time it takes to go from one Germ % to Another <br>
<select name=mc>
	<option value=$smc>Select Storage Type</option>

	<option value='1'>Open Air Room: 07 days 40% RH</option>
	<option value='2'>Open Air Room: 14 days 40% RH</option>
	<option value='3'>Open Air Room: 21 days 40% RH</option>
	<option value='4'>Open Air Room: 28 days 40% RH</option>

	<option value='5'>Open Air Room: 07 days 35% RH</option>
	<option value='6'>Open Air Room: 14 days 35% RH</option>
	<option value='7'>Open Air Room: 21 days 35% RH</option>
	<option value='8'>Open Air Room: 28 days 35% RH</option>

	<option value='9'>Open Air Room: 07 days 30% RH</option>
	<option value='10'>Open Air Room: 14 days 30% RH</option>
	<option value='11'>Open Air Room: 21 days 30% RH</option>
	<option value='12'>Open Air Room: 28 days 30% RH</option>

	<option value='13'>Silica Gel: 1 days 15% RH</option>
	<option value='14'>Silica Gel: 2 days 15% RH</option>
	<option value='15'>Silica Gel: 3 days 15% RH</option>
	<option value='16'>Silica Gel: 4 days 15% RH</option>
	<option value='17'>Silica Gel: 5 days 15% RH</option>
	<option value='18'>Silica Gel: 6 days 15% RH</option>
	<option value='19'>Silica Gel: 7 days 15% RH</option>

</select><br>
Temperature in degrees &#176C  &nbsp: <input type=text name=t value=$st><br>
<select name=type>
  <option value=$stype>Select Seed Type</option>
  <option value='barley'>Barley</option>
  <option value='wheat'>Wheat</option>
  <option value='maize'>Maize</option>
  <option value='pea'>Pea</option>
  <option value='gbean'>Garden Bean</option>
  <option value='rseed'>Rape Seed</option>
  <option value='soybean'>Soybean</option>
  <option value='sunflower'>Sunflower</option>
  <option value='linseed'>Linseed</option>
  <option value='sugarbeet'>Sugarbeet</option>
  <option value='onion'>Onion</option>
  <option value='salad'>Salad</option>
  <option value='tomato'>Tomato</option>
</select>
<br>
Initial Germ %: <input type=text name=ig value=$sig><br>
Ending Germ %: <input type=text name=g value=$sg><br>
<input type=submit name='Calc_btn_6' value='Calculate'><br>
<form>";
}

if($page == "calc"){
$m = $_POST['m'];
$t = $_POST['t'];
$type = $_POST['type'];
$ig = $_POST['ig'];
$ti = $_POST['ti'];
$g = $_POST['g'];
$mc = $_POST['mc'];


$pt = array('0' => array(0.00,2.67,2.95,3.12,3.25,3.36,3.45,3.52,3.59,3.66),
		    '1' => array(3.72,3.77,3.82,3.87,3.92,3.96,4.01,4.05,4.08,4.12),
		    '2' => array(4.16,4.19,4.23,4.26,4.29,4.33,4.36,4.39,4.42,4.45),
		    '3' => array(4.48,4.50,4.53,4.56,4.59,4.61,4.64,4.67,4.69,4.72),
		    '4' => array(4.75,4.77,4.80,4.82,4.85,4.87,4.90,4.92,4.95,4.97),
		    '5' => array(5.00,5.03,5.05,5.08,5.10,5.13,5.15,5.18,5.20,5.23),
		    '6' => array(5.25,5.28,5.31,5.33,5.36,5.39,5.41,5.44,5.47,5.50),
		    '7' => array(5.52,5.55,5.58,5.61,5.64,5.67,5.71,5.74,5.77,5.81),
		    '8' => array(5.84,5.88,5.92,5.95,5.99,6.04,6.08,6.13,6.18,6.23),
		    '9' => array(6.28,6.34,6.41,6.48,6.55,6.64,6.75,6.88,7.05,7.33)
		   );
$m1Arr= array("","13.9091","12.8182","11.7274","10.6365","13.7273","12.45457","11.1819","9.9092","13.5455","12.091","10.6365","9.182","13.6429","12.2858","10.9287","9.5716","8.2145","6.8574","5.5004");
$m2Arr= array("","9.9091","8.8182","7.7274","6.6365","9.7273","8.45457","7.1819","5.9092","9.5455","8.091","6.6365","5.182","9.6429","8.2858","6.9287","5.5716","4.2145","2.8574","1.5004");
if($mc){$m=$mc;}
     if(!$m){
          echo("You must enter Moisture Content!");
          exit;
     }
     if(!$t and $t!='0'){
          echo("You must enter Temperature!");
          exit;
     }
     if(!$type){
          echo("You must enter a Seed Type!");
          exit;
     }
     if(!eregi("[0-9]", $m)){
          echo("Moisture Content MUST be a number!");
          exit;
     }
     if(!eregi("[0-9]", $t)){
          echo("Temperature MUST be a number!");
          exit;
     }
     if($type == "barley"){
     	  $c = array('9.983','5.896','0.04','0.000428');
     }
     if($type == "wheat"){
     	  $c = array('10.1','5.73','0.0563','0.000478');
     }
     if($type == "maize"){
     	  $c = array('8.579','4.910','0.0329','0.000428');
     }
     if($type == "pea"){
     	  $c = array('9.86','5.39','0.0329','0.000478');
     }
     if($type == "gbean"){
     	  $c = array('9.08','5.2','0.0057','0.00079');
     }
     if($type == "rseed"){
     	  $c = array('7.718','4.54','0.0329','0.000478');
     }
     if($type == "soybean"){
     	  $c = array('7.748','3.979','0.053','0.000228');
     }
     if($type == "sunflower"){
     	  $c = array('6.74','4.160','0.0329','0.000478');
     }
     if($type == "linseed"){
     	  $c = array('7.76','4.86','0.0329','0.000478');
     }
     if($type == "sugarbeet"){
     	  $c = array('8.943','4.723','0.0329','0.000478');
     }
     if($type == "onion"){
     	  $c = array('6.975','3.470','0.04','0.000428');
     }
     if($type == "salad"){
     	  $c = array('8.218','4.797','0.0489','0.000365');
     }
     if($type == "tomato"){
     	  $c = array('6.5017','3.1807','0.0324','0.000431');
     }



	if(!$ig){
    	  echo("You must enter Initial Germination!");
          exit;
	}
	if(!eregi("[0-9]", $ig)){
          echo("Initial Germination MUST be a number!");
          exit;
    }
    if($ig['0']=='0'){
          echo("Initial Germination Cannot have Leading Zeros!");
          exit;
    }
    if($ig>'99'){
          echo("Initial Germination too High Auto Set to 99%<br>");
          $ig = '99';

    }


	if (isset($_POST['Calc_btn_1'])) {
		//$s = $calc->calc1($m,$t,$c,$type);
		//$p = $calc->calc2($ig,$pt);

		$m1 = $m;
		$m2 = "a";

		$calc->draw_graph($m1,$m2,$t,$c,$type,$ig,$pt);


        //echo ($s);
		//echo ($p);
    }
    elseif (isset($_POST['Calc_btn_2'])) {
        if(!$ti){
    	  	echo("You must enter Time!");
          	exit;
		}
		if(!eregi("[0-9]", $ti)){
          	echo("Time MUST be a number!");
          	exit;
    	}
		$ge = $calc->calc3($m,$t,$c,$type,$ig,$pt,$ti);
        echo ("After $ti years the germ % will be aprox. $ge%");
    }
    elseif (isset($_POST['Calc_btn_3'])) {
        if(!$g){
    	  	echo("You must enter Ending Germ %!");
          	exit;
		}
		if(!eregi("[0-9]", $g)){
          	echo("Ending Germ % MUST be a number!");
          	exit;
    	}
		$d = $calc->calc4($m,$t,$c,$type,$ig,$pt,$g);

		$y = 0;
		if ($d > 365){!
			$y = ($d/365);
			$y = floor($y);
			$d -= (365 * $y);
		}

		$rd = round($d, 0);

        echo ("It will take aprox. $y years and $rd days for germ % to get to $g%");
    }
    elseif (isset($_POST['Calc_btn_4'])) {
		//$s = $calc->calc1($m,$t,$c,$type);
		//$p = $calc->calc2($ig,$pt);

		$m1 = $m1Arr[$mc];
		$m2 = $m2Arr[$mc];

		$calc->draw_graph($m1,$m2,$t,$c,$type,$ig,$pt);



        //echo ($s);
		//echo ($p);
    }
    elseif (isset($_POST['Calc_btn_5'])) {
    	if(!$ti){
    	  	echo("You must enter Time!");
          	exit;
		}
		if(!eregi("[0-9]", $ti)){
          	echo("Time MUST be a number!");
          	exit;
    	}

    	$m1 = $m1Arr[$mc];
		$m2 = $m2Arr[$mc];

		$ge1 = $calc->calc3($m1,$t,$c,$type,$ig,$pt,$ti);
        echo ("Using the max moisture content $m1, After $ti years the germ % will be aprox. $ge1%");
		echo ("<br/>");
        $ge2 = $calc->calc3($m2,$t,$c,$type,$ig,$pt,$ti);
        echo ("Using the min moisture content $m2, After $ti years the germ % will be aprox. $ge2%");

    }
    elseif (isset($_POST['Calc_btn_6'])) {
		$m1 = $m1Arr[$mc];
		$m2 = $m2Arr[$mc];

		$d1 = $calc->calc4($m1,$t,$c,$type,$ig,$pt,$g);
		$d2 = $calc->calc4($m2,$t,$c,$type,$ig,$pt,$g);

		$d = $d1;
		$y = 0;
		if ($d > 365){!
			$y = ($d/365);
			$y = floor($y);
			$d -= (365 * $y);
		}
		$rd = round($d, 0);

        echo ("Using the max moisture content $m1, It will take aprox. $y years and $rd days for germ % to get to $g%");
		echo ("<br/>");
        $d = $d2;
		$y = 0;
		if ($d > 365){!
			$y = ($d/365);
			$y = floor($y);
			$d -= (365 * $y);
		}
		$rd = round($d, 0);

        echo ("Using the min moisture content $m2, It will take aprox. $y years and $rd days for germ % to get to $g%");
    }

}


?>