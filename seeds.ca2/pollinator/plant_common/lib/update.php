<?php

function Update()
{
    if( @$_SESSION['user'] != "admin" ) { header( "Location: index.php?cmd=login" ); }

    $id = @$_GET['id'];
    $cmd = @$_REQUEST['cmd'];

    if( $id && $cmd ) {
        if( $cmd == 'delete' ) {
            die("Delete");
            $kfdb->Execute( "DELETE FROM main WHERE ID='".addslashes($id)."'" );

            $dataPath = "../img/".$id;
            if( is_dir($dataPath) ) {
                recursive_remove_directory( $dataPath );
            }
        }

        header( "Location: index.php" );

    } else {
        ?>
        <script>
        alert("Bad command!");
        </script>
        <?php
    }
}




// to use this function to totally remove a directory, write:
// recursive_remove_directory('path/to/directory/to/delete');

// to use this function to empty a directory, write:
// recursive_remove_directory('path/to/full_directory',TRUE);

function recursive_remove_directory($directory, $empty=FALSE)
{
	// if the path has a slash at the end we remove it here
	if(substr($directory,-1) == '/')
	{
		$directory = substr($directory,0,-1);
	}

	// if the path is not valid or is not a directory ...
	if(!file_exists($directory) || !is_dir($directory))
	{
		// ... we return false and exit the function
		return FALSE;

	// ... if the path is not readable
	}elseif(!is_readable($directory))
	{
		// ... we return false and exit the function
		return FALSE;

	// ... else if the path is readable
	}else{

		// we open the directory
		$handle = opendir($directory);

		// and scan through the items inside
		while (FALSE !== ($item = readdir($handle)))
		{
			// if the filepointer is not the current directory
			// or the parent directory
			if($item != '.' && $item != '..')
			{
				// we build the new path to delete
				$path = $directory.'/'.$item;

				// if the new path is a directory
				if(is_dir($path))
				{
					// we call this function with the new path
					recursive_remove_directory($path);

				// if the new path is a file
				}else{
					// we remove the file
					unlink($path);
				}
			}
		}
		// close the directory
		closedir($handle);

		// if the option to empty is not set to true
		if($empty == FALSE)
		{
			// try to delete the now empty directory
			if(!rmdir($directory))
			{
				// return false if not possible
				return FALSE;
			}
		}
		// return success
		return TRUE;
	}
}


function getRAPost( $k )
{
    $ra = array();
    if( isset($_POST[$k]) ) {
        foreach( $_POST[$k] as $v ) {
            $ra[] = addslashes($v);
        }
    }
    return( implode( ',', $ra ) );
}


function saveImg( $fileKey, $dataPath )
{
    $ok = false;

    $name = str_replace( "'", "", basename( $_FILES[$fileKey]['name']) );
    $dataPath_image = $dataPath."/".$name;
    //$Can_dataPath_image_habitut = $dataPath_Canada."/".$name;

    $img_thumb = $dataPath."/thumb_".$name;
    //$img_thumb_habitus_CA = $dataPath_Canada."/thumb_".$name;

    if( file_exists($dataPath_image) ) { unlink( $dataPath_image ); }
    //if (file_exists($Can_dataPath_image_habitut)) { unlink ($Can_dataPath_image_habitut); }


    Image::open($_FILES[$fileKey]['tmp_name'])
    ->cropResize(1200, 1200)
    ->save($dataPath_image);

    if( file_exists($dataPath_image) ) {
        //copy($dataPath_image,$Can_dataPath_image_habitut);
        echo "Plant successfully added!";

        Image::open($dataPath_image)
            ->cropResize(300, 300)
            ->save($img_thumb);

        //Image::open($Can_dataPath_image)
        //    ->cropResize(300, 300)
        //    ->save($img_thumb_habitus_CA);

        $ok = true;
    }
    return( $ok );
}





function UpdateRecord( $kfdb, $id )
/**********************************
    $id == 0 : insert a record, return the new id or false
    $id > 0  : update a record, return the id or false
 */
{
    $ok = false;

    $bUpdate = ($id != 0);

    // todo: it might be that this is only to avoid db sql insertion and the (added) addslashes below make this redundant
    $image_habitus = str_replace("'","",$_FILES['img_habitus']['name']);
    $image_flower  = str_replace("'","",$_FILES['img_flower']['name']);
    $image_fruit   = str_replace("'","",$_FILES['img_fruit']['name']);
    $image_leaves  = str_replace("'","",$_FILES['img_leaves']['name']);


    if( $bUpdate ) {
        // Update
        $img_sql = ($image_habitus ? ("image_habitus='".addslashes($image_habitus)."', ") : "")
                  .($image_flower  ? ("image_flower='".addslashes($image_flower)."', ") : "")
                  .($image_fruit   ? ("image_fruit='".addslashes($image_fruit)."', ") : "")
                  .($image_leaves  ? ("image_leaves='".addslashes($image_leaves)."', ") : "");

        $plant_desc_sql  = "info_text='".SEEDInput_GetStrDB('plant_desc')."' ";
        $sc_name_sql     = "Scientific_Name='".SEEDInput_GetStrDB('sc_name')."', ";
        $common_name_sql = "Common_Name='".SEEDInput_GetStrDB('common_name')."', ";


        $bee_sql      = "Bee_Resource='".getRAPost('beeSelect')."', ";
        $plant_sql    = "Plant_Type='".getRAPost('plantSelect')."', ";
        $location_sql = "Location='".getRAPost('locationSelect')."', ";
        $season_sql   = "Season= '".getRAPost('seasonSelect')."', ";


        // Delete images if selected
        if( isset($_POST['delImages']) ) {
            foreach( $_POST['delImages'] as $delImage ) {
                if( in_array( $delImage, array('image_habitus','image_flower','image_fruit','image_leaves') ) ) {
                    $kfdb->Execute( "UPDATE main SET $delImage='' WHERE ID='$id'" );
                }
            }
        }

        $sql = "UPDATE main SET "
              .$sc_name_sql
              .$common_name_sql
              .$bee_sql
              .$plant_sql
              .$location_sql
              .$season_sql
              .$img_sql
              .$plant_desc_sql
              ."WHERE ID='$id'";
        //$kfdb->SetDebug(2);
        if( $kfdb->Execute($sql) ) {
            $ok = true;
        }
    } else {
        // Insert
        $plant_desc  = SEEDSafeGPC_GetStrDB('plant_desc');
        $sc_name     = SEEDSafeGPC_GetStrDB('sc_name');
        $common_name = SEEDSafeGPC_GetStrDB('common_name');

        $bee_sql      = getRAPost('beeSelect');
        $plant_sql    = getRAPost('plantSelect');
        $location_sql = getRAPost('locationSelect');
        $season_sql   = getRAPost('seasonSelect');

        $id = $kfdb->InsertAutoInc( "INSERT into main (Scientific_Name,Common_Name,Bee_Resource,Season,Plant_Type,Location,"
                                                     ."image_flower,image_fruit,image_leaves,image_habitus,info_text,data) "
                                   ."VALUES('$sc_name','$common_name','$bee_sql','$season_sql','$plant_sql','$location_sql',"
                                          ."'$image_flower','$image_fruit','$image_leaves','$image_habitus','$plant_desc','')" );
        if( $id ) {
            $ok = true;
        }
    }

    if( !$ok )  goto done;

    $dataPath = "../plant_common/images/plants/$id";
    if( !is_dir($dataPath) ) {
        mkdir($dataPath);
    }

    //Ontario Data Path
    //$dataPath = "../plant_ontario/images/plants/".$editId;
    //Canada data path
    //$dataPath_Canada = "../plant_canada/images/plants/".$editId;
    //if (!is_dir($dataPath)) {
    //    mkdir($dataPath);
    //}
    //if (!is_dir($dataPath_Canada)) {
    //    mkdir($dataPath_Canada);
    //}

    if( $image_habitus ) {
        if( !saveImg( 'img_habitus', $dataPath ) ) {
            echo "There was an error uploading the file (Image Habitut), please try again!<br>";
            $ok = false;
        }
    }

    if( $image_flower ) {
        if( !saveImg( 'img_flower', $dataPath ) ) {
            echo "There was an error uploading the file(Image Flower), please try again!<br>";
            $ok = false;
        }
    }

    if( $image_fruit ) {
        if( !saveImg( 'img_fruit', $dataPath ) ) {
            echo "There was an error uploading the file(Image Fruit), please try again!<br>";
            $ok = false;
        }
    }

    if( $image_leaves ) {
        if( !saveImg( 'img_leaves', $dataPath ) ) {
            echo "There was an error uploading the file(Image Leaves), please try again!<br>";
            $ok = false;
        }
    }

    done:

    return( $ok ? $id : false );
}



?>