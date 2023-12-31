<?php
include_once( "fcinit.php" );

function ViewHead( $sTitle, $raParms = array() )
{
    $bLightbox = @$raParms['bLightbox'] == true;

    $s = //"<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">"
        //."<html xmlns='http://www.w3.org/1999/xhtml'>"
         "<!DOCTYPE html>"
        ."<html lang='en'>"
        ."<head>"
        ."<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'/>"
        ."<title>$sTitle</title>"
        ."<link href='".PLANT_COMMON_C."styles.css' rel='stylesheet' type='text/css'/>"
        //."<link rel='stylesheet' type='text/css' href='".BOOTSTRAP."/css/bootstrap.min.css'>"
        ."<link rel='stylesheet' type='text/css' href='".W_CORE_URL."os/bootstrap3/dist/css/bootstrap.min.css'></link>"
        ."<style type='text/css'>"
        //."body { background:#212b02 url(images/bg1.png) no-repeat center top;}"
        //."body { background:#fff url(images/bg1.png) no-repeat center top;}"
    //.".style1 { color: #33FF66; padding-left:10px; }"
        ."</style>"

        // For some dumb reason the lightbox wants 1.7.2 and the login page was using 1.9.1
        .($bLightbox
            ? "<script src='//ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js' ></script>"
            : "<script src='//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js' ></script>")
        ."<script src='".PLANT_COMMON_C."main.js' type='text/javascript'></script>"
        ."<meta http-equiv='Cache-Control' content='no-cache'>"
        ."<meta http-equiv='Pragma' content='no-cache'>"
        ."<meta http-equiv='Expires' content='0'>"
        ."<link rel='shortcut icon' href='../plant_common/c/favicon.ico'>"
        ."<script src='".W_ROOT."std/js/SEEDStd.js'></script>";

    if( $bLightbox ) {
        $s .= "<link rel='stylesheet' href='lightbox.css' type='text/css'/>"
             ."<script src='lightbox.js'></script>"
             ."<style>"
             .".lb-outerContainer { max-width:500px; max-height:500px; padding:0 auto; }"
             .".lb-dataContainer  { width:0px; height:0px; }"    // for the text below image
             ."</style>";
    }

    $s .= "</head>";

    return( $s );
}

function ViewPlantDetails( $id )
{
    global $kfdb;

    if( $id ) {
        $returnTable="";
        $raMain = $kfdb->QueryRowsRA("SELECT * FROM main WHERE ID='$id'" );
        foreach( $raMain as $row ) {
            $imageURL = array();
            //"images/default.jpg";

            if(strlen($row["image_habitus"])!=0){
                $imageURL[]= "../plant_common/img/".$row["ID"]."/".$row["image_habitus"];
            }
            if(strlen($row["image_flower"])!=0){
                $imageURL[]= "../plant_common/img/".$row["ID"]."/".$row["image_flower"];
            }
            if(strlen($row["image_fruit"])!=0){
                $imageURL[]= "../plant_common/img/".$row["ID"]."/".$row["image_fruit"];
            }
            if(strlen($row["image_leaves"])!=0){
                $imageURL[]= "../plant_common/img/".$row["ID"]."/".$row["image_leaves"];
            }
            $description = $row["info_text"];
            $sc_name = $row["Scientific_Name"];
            $com_name = $row["Common_Name"];
            $bee_code = rtrim($row["Bee_Resource"],",");
            $bee_names=array("Pollen","Honeydew","Resin","Nectar");
            $bee_short_code = array('/P/','/Hd/','/r/','/N/');

            $bees = explode(",", $bee_code);
            $bee_code = "";
            foreach ($bees as $bee) {
                $sBee = $kfdb->Query1( "SELECT bee FROM bee WHERE LOWER(code) like '%".trim(strtolower($bee))."%'" );
                $bee_code .= $sBee.",";
            }
            $bee_code = rtrim($bee_code,",");

            $plant_code = $row["Plant_Type"];

            $plants = explode(",", $plant_code);
            $plant_code = "";
            foreach ($plants as $plant) {
                $sPlant = $kfdb->Query1( "SELECT plant_type FROM plant_type WHERE LOWER(code) like '%".trim(strtolower($plant))."%'" );
                $plant_code .= $sPlant.",";
            }
            $plant_code = rtrim($plant_code,",");

            $plant_names="";
        }
    }

    $s = "<div style='text-align:center;margin-top:40px;'>"
        ."<h2 align='center'>Plant Details</h2>"
        ."</div>"
        ."<table width='500' border='0' class='table table-bordered'>"
        ."</tr>"
        ."<td colspan='2' style='margin:0 auto;'>";
    if(isset($imageURL[0])) {
        if(isset($imageURL[0])) { $s .= "<a rel='lightbox[plant]' href='".$imageURL[0]."'><img id='plant_img' src='".$imageURL[0]."' style='max-height:200px; max-width: 200px; padding-left:10px;'/></a>"; }
        if(isset($imageURL[1])) { $s .= "<a rel='lightbox[plant]' href='".$imageURL[1]."'><img id='plant_img' src='".$imageURL[1]."' style='max-height:200px; max-width: 200px; padding-left:30px;'/></a>"; }
        if(isset($imageURL[2])) { $s .= "<a rel='lightbox[plant]' href='".$imageURL[2]."'><img id='plant_img' src='".$imageURL[2]."' style='max-height:200px; max-width: 200px; padding-left:10px;'/></a>"; }
        if(isset($imageURL[3])) { $s .= "<a rel='lightbox[plant]' href='".$imageURL[3]."'><img id='plant_img' src='".$imageURL[3]."' style='max-height:200px; max-width: 200px; padding-left:30px;'/></a>"; }
    } else {
        $s .= "<div style='margin-left:auto; margin-right:auto;'><img id='plant_img' src='images/default.jpg' style='max-height:200px; max-width: 200px;'/></div>";
    }
    $s .= "</td></tr>"
         ."<tr><td colspan='2'>$description</td></tr>"
         ."<tr><td width='161'>Scientific Name:</td><td width='329'>$sc_name</td></tr>"
         ."<tr><td>Common Name:</td><td>$com_name</td></tr>"
         ."<tr><td>Bee Resource:</td><td>$bee_code</td></tr>"
         ."<tr><td>Plant Type:</td><td>$plant_code</td></tr>"
         ."</table>";

    return( $s );
}

function ViewSearchPanel()
{
    global $kfdb, $bCanada, $result_string;

    $oView = new View( $kfdb );

    ob_start();
        ?>
    <form id="form1" name="form1" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>"  class="form-search">
    <table width="493" border="0">
        <tr>
            <td colspan="2">
                <?php echo $oView->ProvinceControl( $bCanada ); ?>
            </td>
            <td style="text-align:right">
                <!--  Search control -->
                <div class="input-append input-group">
                    <input type="text" id="keyWord" name="keyWord" value="<?php echo @$_POST['keyWord']; ?>" class="span2 search-query form-control">
                    <span class="input-group-btn"><button type="submit" id="formSubmit" name="formSubmit" value="frmSubmit" class="btn btn-success">Search</button></span>
                </div>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <?php echo $oView->SeasonControl(); ?>
            </td>
            <td style="text-align:right">
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <?php echo $oView->SubseasonControl(); ?>
            </td>
            <td colspan="2">
                <?php
                if( @$_SESSION['user']=="admin" ) {
                    echo "<a href='admin.php' class='btn btn-success btn-block'>Add a New Plant</a>";
                    // in admin it was 		<button name='new' id='new' class='btn btn-success btn-block' style='width:100%;'>Add A New Plant</button>
                }
                ?>
            </td>
        </tr>
        <tr>
            <td colspan="3">&nbsp;</td>
        </tr>
        <tr>
            <td colspan="3">
                <!-- Search Results-->
                <div id="results_div" style="height:400px; overflow-x: hidden; overflow-y:auto;">
                    <table width="500" border="0" class="table table-striped">
                    <?php
                    if( @$_SESSION['user']=="admin" || isset($_POST['formSubmit']) ) {
                        if( $result_string == "" ) {
                            echo "<tr><td>No search results</td></tr>";
                        } else {
                            echo $result_string;
                        }
                    } else {
                        echo "<p style='color:#468847'>Welcome to the online floral calendar for ".($bCanada ? "Canada's" : "Ontario's")." beekeepers. "
                            ."Using the dropdown menu, you can quickly determine which plants are in bloom and the value of each blooming plant as "
                            ."nectar or pollen resources for bees.  <br /><br />"
                            ."Floral calendars are an essential tool for beekeepers.  They provide information on the yearly cycles in the flow of "
                            ."nectar for honey production and availability of pollen.  This helps beekeepers maximize honey production while maintaining "
                            ."colony health. This is the first time this type of information has been made available for Canadian beekeepers in an "
                            ."electronic and easy-to-access format.<br /><br />"
                            ."This resource was created by the Canadian Pollination Initiative (NSERC-CANPOLIN) with financial support from the "
                            ."Ontario Ministry of Agriculture and Rural Affairs and the University of Guelph through the "
                            ."Knowledge Translation and Transfer (KTT) program. Seeds of Diversity is generously hosting the website.</p>";
                    }
                    ?>
                    </table>
                </div>
            </td>
        </tr>
    </table>
</form>

<?php

$s = ob_get_contents();
ob_end_clean();
return( $s );
}


function ViewPlantForm( $id )
{
    global $kfdb;

    $image_habitus = $image_flower = $image_fruit = $image_leaves = "";
    $description = $sc_name = $com_name = "";
    $bee_code = $plant_code = $location_code = $season_code = "";

    if( $id ) {
        $returnTable = "";

        $row = $kfdb->QueryRA( "SELECT * FROM main WHERE ID='$id'" );

        $sDir = "../plant_common/img/$id/";
        if( $row["image_habitus"] )  $image_habitus = $sDir.$row["image_habitus"];
        if( $row["image_flower"] )   $image_flower  = $sDir.$row["image_flower"];
        if( $row["image_fruit"] )    $image_fruit   = $sDir.$row["image_fruit"];
        if( $row["image_leaves"] )   $image_leaves  = $sDir.$row["image_leaves"];

        $description = $row["info_text"];
        $sc_name = $row["Scientific_Name"];
        $com_name = $row["Common_Name"];

        $bee_code = rtrim($row["Bee_Resource"],",");
        $plant_code = rtrim($row["Plant_Type"],",");
        $location_code = rtrim($row["Location"],",");
        $season_code = rtrim($row["Season"],",");

        $seasons = explode(",", $season_code);
    }

    $s = "<table width='500' border='0' class='table table-striped table-bordered'>";

    // Images
    $s .= "
        <tr><td><label style='font-weight:normal'>Image 1 (Habitus):</label></td>
            <td><input type='file' name='img_habitus' id='img_habitus' title='Choose 1st Image'>"
          .drawImg( 1, $image_habitus, "image_habitus", "Habitus" )
          ."</td></tr>
        <tr><td><label style='font-weight:normal'>Image 2 (Flower):</label></td>
            <td><input type='file' name='img_flower' id='img_flower' title='Choose 2nd Image'>"
          .drawImg( 2, $image_flower, "image_flower", "Flower" )
          ."</td></tr>
        <tr><td><label style='font-weight:normal'>Image 3 (Fruit):</label></td>
            <td><input type='file' name='img_fruit' id='img_fruit' title='Choose 3rd Image'>"
          .drawImg( 3, $image_fruit, "image_fruit", "Fruit" )
          ."</td></tr>
        <tr><td><label style='font-weight:normal'>Image 4 (Leaves):</label></td>
            <td><input type='file' name='img_leaves' id='img_leaves' title='Choose 4th Image'>"
          .drawImg( 4, $image_leaves, "image_leaves", "Leaves" )
          ."</td></tr>
      ";

    // Names
    $s .= "
      <tr>
        <td width='161'>Scientific Name:</td>
        <td width='329'><input name='sc_name' type='text' id='sc_name' value='".SEEDCore_HSC($sc_name)."' style='width:350px;'/></td>
      </tr>
      <tr>
        <td>Common Name:</td>
        <td><input name='common_name' type='text' id='sc_name' value='".SEEDCore_HSC($com_name)."' style='width:350px;'/></td>
      </tr>
    ";


    // Description
    $s .= "
      <tr>
        <td>Description:</td>
        <td><textarea name='plant_desc' cols='80' rows='5' class='text-success' id='plant_desc' style='width:350px;'>$description</textarea></td>
      </tr>
    ";

    // Bee Resource
    $s .= "
      <tr>
        <td>Bee Resource:</td>
        <td>"
          .checkbox( 'beeSelect', $bee_code, 'P', "Pollen" )
          .checkbox( 'beeSelect', $bee_code, 'N', "Nectar" )
          .checkbox( 'beeSelect', $bee_code, 'Hd', "Honeydew" )
          .checkbox( 'beeSelect', $bee_code, 'r', "Resin" )
      ."</td>
      </tr>
    ";

    // Plant Type
    $s .= "
      <tr>
        <td>Plant Type:</td>
        <td>"
          .checkbox( 'plantSelect', $plant_code, 'W', "Wild (Native or Escaped)" )
          .checkbox( 'plantSelect', $plant_code, 'C', "Cultivated or Crop" )
          .checkbox( 'plantSelect', $plant_code, 'U', "Widespread" )
          .checkbox( 'plantSelect', $plant_code, 'R', "Weedy" )
          .checkbox( 'plantSelect', $plant_code, 'A', "Wetlands" )
      ."</td>
      </tr>
    ";

    // Location
    $s .= "
      <tr>
        <td>Location:</td>
        <td>"
          .checkbox( 'locationSelect', $location_code, 'AB', "Alberta" )
          .checkbox( 'locationSelect', $location_code, 'BC', "British Columbia" )
          .checkbox( 'locationSelect', $location_code, 'MB', "Manitoba" )
          .checkbox( 'locationSelect', $location_code, 'NB', "New Brunswick" )
          .checkbox( 'locationSelect', $location_code, 'NF', "Newfoundland &amp; Labrador" )
          .checkbox( 'locationSelect', $location_code, 'NT', "North West Territories" )
          .checkbox( 'locationSelect', $location_code, 'NS', "Nova Scotia" )
          .checkbox( 'locationSelect', $location_code, 'NU', "Nunavut" )
          .checkbox( 'locationSelect', $location_code, 'ON', "Ontario" )
          .checkbox( 'locationSelect', $location_code, 'PE', "Prince Edward Island" )
          .checkbox( 'locationSelect', $location_code, 'QC', "Quebec" )
          .checkbox( 'locationSelect', $location_code, 'SK', "Saskatchewan" )
          .checkbox( 'locationSelect', $location_code, 'YT', "Yukon" )
       ."</td>
      </tr>
    ";

    // Seasons
    $s .= "
      <tr>
        <td>Seasons:</td>
        <td>
          <table width='400' border='0'>
            <tr>
              <td width='71'>&nbsp;</td>
              <td width='40'>Early</td>
              <td width='40'>Mid</td>
              <td width='40'>Late</td>
            </tr>
            <tr>
              <td>Spring</td>"
                ."<td>".checkbox( 'seasonSelect', $season_code, "eSp", "" )."</td>"
                ."<td>".checkbox( 'seasonSelect', $season_code, "mSp", "" )."</td>"
                ."<td>".checkbox( 'seasonSelect', $season_code, "lSp", "" )."</td>"
          ."</tr>
            <tr>
              <td>Summer</td>"
                ."<td>".checkbox( 'seasonSelect', $season_code, "eSu", "" )."</td>"
                ."<td>".checkbox( 'seasonSelect', $season_code, "mSu", "" )."</td>"
                ."<td>".checkbox( 'seasonSelect', $season_code, "lSu", "" )."</td>"
          ."</tr>
            <tr>
              <td>Fall</td>"
                ."<td>".checkbox( 'seasonSelect', $season_code, "eF", "" )."</td>"
                ."<td>".checkbox( 'seasonSelect', $season_code, "mF", "" )."</td>"
                ."<td>".checkbox( 'seasonSelect', $season_code, "lF", "" )."</td>"

            ."</tr>
          </table>
        </td>
      </tr>
    ";

    // Submit button
    $s .= "<tr><td colspan='2'>";
    if( $id ) {
        $s .= "<input type='hidden' name='Doc_ID' value='$id'/>";
        $b = "editSubmit";
    } else {
        $b = "newSubmit";
    }
    $s .= "<button type='submit' id='$b' name='$b' value='$b' class='btn btn-success'>Submit</button>"
         ."</td></tr>"
         ."</table>";

    return( $s );
}

function checkbox( $name, $code_set, $code, $label )
{
    return( "<label class='checkbox' style='font-weight:normal'><input name='{$name}[]' type='checkbox'".(strpos($code_set, $code) !== false ? ' checked' : '')." value='$code' />$label</label>" );
}

function drawImg( $n, $img, $name, $label )
{
    return( $img ? ("<div style='float:left; padding-left:25px'>Image $n ($label) <br/>"
                   ."<img id='plant_img' src='$img' style='max-height:200px;max-width: 200px'/> <br/>"
                   ."<label><input name='delImages[]' type='checkbox' value='$name' /> Delete $label Image</label></div>" )
                 : "" );
}



class View
{
    private $kfdb;

    function __construct( KeyFrameDatabase $kfdb )
    {
        $this->kfdb = $kfdb;
    }

    function ProvinceControl( $bCanada )
    {
        /* Provinces dropdown
        */
        $post_Location = isset($_POST['location']) ? $_POST['location'] : "-1";

        $s = "<select name='location' size='1' id='location' class='form-control'>";
        if( $bCanada ) {
            $s .= "<option value='' ".($post_Location == "-1" ? ' selected="selected"' : '').">Select Location</option>";

            $raLoc = $this->kfdb->QueryRowsRA( "SELECT * FROM location" );
            foreach( $raLoc as $ra ) {
                if( $ra["Active_Province"] == 1 ) {
                    $s .= "<option value='".$ra["Location_Code"]."'"
                         .($ra["Location_Code"] == $post_Location ? ' selected="selected"' : '').">".$ra["Location_Description"]."</option>";
              }
            }
        } else {
            $s .= "<option selected='selected' value='ON'>Ontario</option>";
        }
        $s .= "</select>";

        return( $s );
    }

    function SeasonControl()
    {
        /* Season dropdown
         */
        $post_Season = isset($_POST['season']) ? $_POST['season'] : "-1";

        $s = "<select name='season' size='1' id='season' class='form-control'>"
            ."<option value='' ".($post_Season == "-1" ? ' selected="selected"' : '').">Select Season</option>";
        $raSeason = $this->kfdb->QueryRowsRA( "SELECT * FROM season" );
        foreach( $raSeason as $ra ) {
            $s .= "<option value=".$ra["Season_Code"].($ra["Season_Code"] == $post_Season ? ' selected="selected"' : '').">"
                 .$ra["Season_Description"]."</option>";
        }
        $s .= "</select>";

        return( $s );
    }

    function SubseasonControl()
    {
        /* Sub-season dropdown
        */
        $post_sub_season = isset($_POST['sub_season']) ? $_POST['sub_season'] : "-1";
        $post_Season     = isset($_POST['season']) ? $_POST['season'] : "-1";

        $s = "<select name='sub_season' size='1' id='sub_season' class='form-control'>"
            ."<option value='' ".($post_Season == "-1" ? ' selected="selected"' : '').">Select Sub-Season</option>";

        $raSubseason = $this->kfdb->QueryRowsRA( "SELECT * FROM subseason" );
        foreach( $raSubseason as $ra ) {
            $s .= "<option value=".$ra["Subseason_Code"].($ra["Subseason_Code"] == $post_sub_season ? ' selected="selected"' : '').">"
                 .$ra["Subseason_Description"]."</option>";
        }
        $s .= "</select>";

        return( $s );
    }
}


function Search()
{
    global $kfdb;

    $searchResult = "";

    if( !isset($_POST['formSubmit']) )  return( "" );

    // filter province
    $str = SEEDInput_Get('location');
    $locationSQL = $str['plain'] ? " AND Location like '%{$str['db']}%' " : "";

    // filter season and subseason
    $str1 = SEEDInput_Get('season');
    $str2 = SEEDInput_Get('sub_season');
    $seasonSQL = "";
    if( $str1['plain'] ) {
        $seasonSQL = " AND Season like '%".(($str2['plain'] == "f" || $str1['plain'] == "") ? $str1['db'] : ($str2['db'].$str1['db']))."%' ";
    }

    // filter keyword
    $strKeyword = SEEDInput_Get('keyWord');

    $raMain = $kfdb->QueryRowsRA( "SELECT * FROM main "
                                 ."WHERE (Scientific_Name like '%{$strKeyword['db']}%' OR Common_Name like '%{$strKeyword['db']}%') "
                                 .$locationSQL.$seasonSQL
                                 ." ORDER BY Scientific_Name ASC" );
    $count=1;
    foreach( $raMain as $row ) {
        $imageURL = "";

        // get a picture, use reverse priority so the winner is the last one that exists
        if( $row["image_leaves"] )     $imageURL = $row["image_leaves"];
        if( $row["image_fruit"] )      $imageURL = $row["image_fruit"];
        if( $row["image_flower"] )     $imageURL = $row["image_flower"];
        if( $row["image_habitus"] )    $imageURL = $row["image_habitus"];

        if( $imageURL ) {
            $imageURL = "../plant_common/img/".$row["ID"]."/".$imageURL;
        } else {
            $imageURL = "images/default.jpg";
        }

        $editRow = "";
        $deleteRow = "";

        if( @$_SESSION['user']=="admin" ) {
            $editRow   = "<td><a href='admin.php?cmd=edit&id={$row['ID']}' id='{$row['ID']}' class='edit' >Edit</a> </td>";
            $deleteRow = "<td><a href='admin.php?cmd=delete&id={$row['ID']}' id='{$row['ID']}' class='delete' >Delete</a> </td>";
            $newRow    = "<td><button name='new' id='new' class='btn btn-success btn-block'>Sign in</button></td>";
        }

        $searchResult .= "<tr><td>$count</td><td><a href='$imageURL' id='{$row['ID']}' class='preview'>"
                        .$row["Scientific_Name"]." ({$row["Common_Name"]})</a></td>"
                        .$editRow
                        .$deleteRow
                        ."</tr>";
        ++$count;
    }

    return( $searchResult );
}



function ResolveTag( $raTag, SEEDTagParser $oTag, $raParms = array() )
/*********************************************************************
 */
{
    //var_dump($raTag);
    $s = "";
    $bHandled = true;

    switch( strtolower($raTag['tag']) ) {
        case 'foo':
            break;
        default:
            $bHandled = false;
    }

    done:
    return( array($bHandled,$s) );
}


?>
