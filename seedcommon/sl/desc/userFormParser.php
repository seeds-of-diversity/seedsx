<?php
include_once( SEEDCOMMON."sl/desc/apple_defs.php" );
include_once( SEEDCOMMON."sl/desc/bean_defs.php" );
include_once( SEEDCOMMON."sl/desc/garlic_defs.php" );
include_once( SEEDCOMMON."sl/desc/lettuce_defs.php" );
include_once( SEEDCOMMON."sl/desc/onion_defs.php" );
include_once( SEEDCOMMON."sl/desc/pea_defs.php" );
include_once( SEEDCOMMON."sl/desc/pepper_defs.php" );
include_once( SEEDCOMMON."sl/desc/potato_defs.php" );
include_once( SEEDCOMMON."sl/desc/squash_defs.php" );
include_once( SEEDCOMMON."sl/desc/tomato_defs.php" );
include_once( SEEDCOMMON."sl/desc/common_defs.php" );

include_once( SEEDCOMMON."sl/sl_desc_db.php" );
include_once( "_sl_desc.php" );

//ini_set('display_errors',1);
//error_reporting(E_ALL);
function userFormParser($textFile,$oSLDescDB,$kVI){
  	$fh = fopen($textFile, "r");
  	$htmlForm ="";
  	if($fh){
    	while(!feof($fh)){
        	$line = fgets($fh);

			$start = strpos($line,"[[");
			$end = strpos($line,"]]");
			if($start >= 0 and $end){

			    $code = substr($line , $start + 2, $end - $start - 2);

				$first_ = strpos($code, "_");
				$def = substr($code,0,$first_);

			    $dblUnder = strpos($code, "__");
			    $q_ = "q_".substr($code,$dblUnder - 1,1);

				$oF = new SLDescForm($oSLDescDB, $kVI);
				$oF->Update();
				$raUserForm = array(array('cmd'=>$q_,'k'=>$code));

				switch($def){
    				case "apple"  : $oF->SetDefs( SLDescDefsApple::$raDefsApple ); break;
        			case "bean"   : $oF->SetDefs( SLDescDefsBean::$raDefsBean ); break;
        			case "garlic" : $oF->SetDefs( SLDescDefsGarlic::$raDefsGarlic ); break;
        			case "lettuce": $oF->SetDefs( SLDescDefsLettuce::$raDefsLettuce ); break;
        			case "onion"  : $oF->SetDefs( SLDescDefsOnion::$raDefsOnion ); break;
        			case "pea"    : $oF->SetDefs( SLDescDefsPea::$raDefsPea ); break;
        			case "pepper" : $oF->SetDefs( SLDescDefsPepper::$raDefsPepper ); break;
        			case "potato" : $oF->SetDefs( SLDescDefsPotato::$raDefsPotato ); break;
        			case "squash" : $oF->SetDefs( SLDescDefsSquash::$raDefsSquash ); break;
        			case "tomato" : $oF->SetDefs( SLDescDefsTomato::$raDefsTomato ); break;
        			case "common" : $oF->SetDefs( SLDescDefsCommon::$raDefsCommon ); break;
				}

				$form = $oF->DrawForm( $raUserForm );

				$line = str_replace('[['.$code.']]',"</p>".$form."<p>",$line);

			}
			$htmlForm .= "<p>".$line."</p>";
      	}
      fclose($fh);
	}

return($htmlForm);
}

?>