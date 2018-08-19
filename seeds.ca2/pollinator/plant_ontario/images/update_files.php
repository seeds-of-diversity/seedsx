<?php
require_once(BOOTSTRAP.'Image-master/autoload.php');
use Gregwar\Image\Image;

?>
<?php
if ($handle = opendir('../../plant_canada/images/plants/')) {
ini_set('max_execution_time', 300);
    /* This is the correct way to loop over the directory. */
    while (false !== ($entry = readdir($handle))) {
	if ($entry != "." && $entry != "..") {
        $dataPath = '../../plant_canada/images/plants/'.$entry;
		$folderName = '../../plant_ontario/images/plants/'.$entry;

		if (!is_dir($folderName)) {
			mkdir($folderName);
		}

		if ($handle1 = opendir($dataPath.'/')) {
			 while (false !== ($image = readdir($handle1))) {

			 if (($image != "." && $image != ".." && strlen($image))) {

       			$thisImage = $dataPath.'/'.$image;

				$newImage = $folderName.'/'.$image;
				$newThumb = $folderName.'/thumb_'.$image;

				echo "Image:".$thisImage.'<br>';
				//echo "New Image:".$newImage.'<br>';
				//echo "Thumb:".$newThumb.'<br>';


				Image::open($thisImage)
						->cropResize(1200, 1200)
						->save($newImage);

				Image::open($thisImage)
						->cropResize(300, 300)
						->save($newThumb);
			}
			}

		}
		}
    }

    closedir($handle);
}
?>