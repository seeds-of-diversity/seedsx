<?php
/*-------------------------------------------------------------*/
/* Anlanda Studios Custom PHP functions                        */
/*-------------------------------------------------------------*/
function criterr($err){
    $tempa = array();
	$i=0;
	for($c=1;$c<func_num_args();$c++){
		$tempa[$i] = func_get_arg($c);
		$i++;
	}

	$err = vsprintf($err,$tempa);

	die($err);

}
function load_env(&$tt,&$ctt,&$gtt){

	foreach($_REQUEST as $key => $values){
		if(is_array($values)){
			for($c=0;$c<count($values);$c++){
				tkntbl_snprintf($tt,$key,2,MAX_RESULTS,"%s%s",(ttn($tt,$key)?"-":""),$values[$c]);
			}
		} else tkntbl_add($tt, $key, $values, 1);
	}

	foreach($_FILES as $key => $values){
		tkntbl_add($tt, $key, $values, 1);
	}

	foreach($_COOKIE as $key => $values){
		tkntbl_add($ctt, $key, $values, 1);
	}

	foreach($_SERVER as $key => $values){
		tkntbl_add($gtt, $key, $values, 1);
	}
	foreach($tt->tkn as $key => $values){
		if(@$_COOKIE[$key]) tkntbl_rmv($tt,$key);
	}

}
function rand_string($len, $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789')
{
   $string = '';
   for ($i = 0; $i < $len; $i++)
   {
       $pos = rand(0, strlen($chars)-1);
       $string .= $chars{$pos};
   }
   return $string;
}
function xml_header(){
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-store, no-cache, must-revalidate");
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache");
	header("content-type:text/xml");
}
function jsalert($msg){
	echo "<script>alert('".$msg."')</script>";
}
function idxProcQuery($token,&$rtoken){
	foreach($token->tkn as $key => $value){

		if($value){
			if(!strstr($key,"@@")){
			if(strstr($key,"@")){
				if(strstr($value,"-")) $temp_a = explode("-",$value);
				if(strstr($value,",")) $temp_a = explode(",",$value);
				if(count($temp_a)>1){
					tkntbl_snprintf($rtoken,"query",2,MAX_RESULTS,"%s (",(ttn($rtoken,"query")?" AND ": ""));
					for($c=0;$c<count($temp_a);$c++){
						if(strstr($key,"like_"))
							tkntbl_snprintf($rtoken,"query",2,MAX_RESULTS,"%s INSTR(REPLACE(`%s`,' ',''),'%s') ",($c>0?" OR ": ""),str_replace("like_","",$key),str_replace(" ","",$temp_a[$c]));
						else tkntbl_snprintf($rtoken,"query",2,MAX_RESULTS,"%s `%s` = '%s' ",($c>0?" OR ": ""),$key,$temp_a[$c]);
					}
					tkntbl_snprintf($rtoken,"query",2,MAX_RESULTS," ) ");
				} else {
					if(strstr($key,"min_@") || strstr($key,"max_@")){
						if(strstr($key,"min_@") && $value && ttn($token,str_replace("min_@","max_@",$key)))
							tkntbl_snprintf($rtoken,"query",2,MAX_RESULTS,"%s (`%s` >= '%s' AND  `%s` <= '%s' )",(ttn($rtoken,"query")? " AND " : ""),str_replace("min_","",$key),$value,str_replace("min_","",$key),ttn($token,str_replace("min_@","max_@",$key)));

						if(strstr($key,"min_@") && $value && !ttn($token,str_replace("min_@","max_@",$key))) tkntbl_snprintf($rtoken,"query",2,MAX_RESULTS,"%s (`%s` >= '%s')",(ttn($rtoken,"query")? " AND " : ""),str_replace("min_","",$key),$value);
				if(strstr($key,"max_@") && $value && !ttn($token,str_replace("max_@","min_@",$key))) tkntbl_snprintf($rtoken,"query",2,MAX_RESULTS,"%s (`%s` <= '%s')",(ttn($rtoken,"query")? " AND " : ""),str_replace("max_","",$key),$value);


					} elseif(strstr($key,"like_")) {
						tkntbl_snprintf($rtoken,"query",2,MAX_RESULTS," INSTR(REPLACE(`%s`,' ',''),'%s') ",str_replace("like_","",$key),str_replace(" ","",$value));
					} else tkntbl_snprintf($rtoken,"query",2,MAX_RESULTS,"%s `%s` = '%s'",(ttn($rtoken,"query")?" AND ": ""),$key,$value);
				}
			}
		}
	}
}
	if(ttn($rtoken,"query")) tkntbl_snprintf($rtoken,"query",1,MAX_RESULTS," WHERE %s ",ttn($rtoken,"query"));


}
function smart_resize_image( $file, $width = 0, $height = 0, $proportional = false, $output = 'file', $delete_original = true, $use_linux_commands = false )
  {
    if ( $height <= 0 && $width <= 0 ) {
      return false;
    }

    $info = getimagesize($file);
    $image = '';

    $final_width = 0;
    $final_height = 0;
    list($width_old, $height_old) = $info;

    if ($proportional) {
      if ($width == 0) $factor = $height/$height_old;
      elseif ($height == 0) $factor = $width/$width_old;
      else $factor = min ( $width / $width_old, $height / $height_old);

      $final_width = round ($width_old * $factor);
      $final_height = round ($height_old * $factor);

    }
    else {
      $final_width = ( $width <= 0 ) ? $width_old : $width;
      $final_height = ( $height <= 0 ) ? $height_old : $height;
    }

    switch ( $info[2] ) {
      case IMAGETYPE_GIF:
        $image = imagecreatefromgif($file);
      break;
      case IMAGETYPE_JPEG:
        $image = imagecreatefromjpeg($file);
      break;
      case IMAGETYPE_PNG:
        $image = imagecreatefrompng($file);
      break;
      default:
        return false;
    }

    $image_resized = imagecreatetruecolor( $final_width, $final_height );

    if ( ($info[2] == IMAGETYPE_GIF) || ($info[2] == IMAGETYPE_PNG) ) {
      $trnprt_indx = imagecolortransparent($image);

      // If we have a specific transparent color
      if ($trnprt_indx >= 0) {

        // Get the original image's transparent color's RGB values
        $trnprt_color    = imagecolorsforindex($image, $trnprt_indx);

        // Allocate the same color in the new image resource
        $trnprt_indx    = imagecolorallocate($image_resized, $trnprt_color['red'], $trnprt_color['green'], $trnprt_color['blue']);

        // Completely fill the background of the new image with allocated color.
        imagefill($image_resized, 0, 0, $trnprt_indx);

        // Set the background color for new image to transparent
        imagecolortransparent($image_resized, $trnprt_indx);


      }
      // Always make a transparent background color for PNGs that don't have one allocated already
      elseif ($info[2] == IMAGETYPE_PNG) {

        // Turn off transparency blending (temporarily)
        imagealphablending($image_resized, false);

        // Create a new transparent color for image
        $color = imagecolorallocatealpha($image_resized, 0, 0, 0, 127);

        // Completely fill the background of the new image with allocated color.
        imagefill($image_resized, 0, 0, $color);

        // Restore transparency blending
        imagesavealpha($image_resized, true);
      }
    }

    imagecopyresampled($image_resized, $image, 0, 0, 0, 0, $final_width, $final_height, $width_old, $height_old);

    if ( $delete_original ) {
      if ( $use_linux_commands )
        exec('rm '.$file);
      else
        @unlink($file);
    }

    switch ( strtolower($output) ) {
      case 'browser':
        $mime = image_type_to_mime_type($info[2]);
        header("Content-type: $mime");
        $output = NULL;
      break;
      case 'file':
        $output = $file;
      break;
      case 'return':
        return $image_resized;
      break;
      default:
      break;
    }

    switch ( $info[2] ) {
      case IMAGETYPE_GIF:
        imagegif($image_resized, $output);
      break;
      case IMAGETYPE_JPEG:
        imagejpeg($image_resized, $output);
      break;
      case IMAGETYPE_PNG:
        imagepng($image_resized, $output);
      break;
      default:
        return false;
    }

    return true;
  }

?>