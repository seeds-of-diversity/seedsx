<?php
include( "../site.php" );

$lang = site_define_lang();

$sLogo = "logo/BFICSS-logo-".($lang=='EN' ? "en" : "fr")."-white_x-394.png";
$sLogoLink = $lang=='EN' ? "http://www.seedsecurity.ca" : "http://www.semencessecures.ca";

?>

<!doctype html>
<html>
<head>

<!--  JQuery  -->
<script src="<?php echo W_ROOT_JQUERY_1_11_0; ?>"></script>

<?php
//<script type='text/javascript' src='https://www.google.com/jsapi'></script>
?>

<!--  SEEDSlider  -->
<script src="<?php echo W_ROOT; ?>seedcommon/SEEDSlider/SEEDSlider2.js"></script>
<script>
var sSEEDSlider_QUrl = "<?php echo "http://{$_SERVER['SERVER_NAME']}".SITEROOT_URL?>bauta/q.php";
var sSEEDSlider_Lang = "<?php echo $lang; ?>";
</script>
<link rel='stylesheet' type='text/css' href='<?php echo W_ROOT; ?>seedcommon/SEEDSlider/SEEDSlider.css'></link>

<!--  Bootstrap  -->
<link rel='stylesheet' type='text/css' href='<?php echo W_ROOT; ?>os/bootstrap3/dist/css/bootstrap.min.css'></link>
<meta name='viewport' content='width=device-width, initial-scale=1.0'>

<!--  Q content style -->
<link rel='stylesheet' type='text/css' href='q.css'></link>

</head>
<body style='background-image:url(back1.jpg);background-size:100% auto'>


<?php
/*
 * Don't do this with a form because that would cause a real submission and page refresh. Keep it all on the browser side.
 */
?>
<div style='float:right;margin-top:20px;margin-right:15px;width:20%'>
  <div class="input-group input-group-sm">
    <input type='text' id='qSearch2' name='qSearch2' class='form-control'
           onkeydown='if( event.keyCode == 13 ) document.getElementById("qSearchBtn2").click();'/>
    <span class="input-group-btn">
    <button class="btn btn-default" type="button" id='qSearchBtn2' value='Search' onclick='SEEDSlider_Search($("#qSearch2").val());'>
      <span class='glyphicon glyphicon-search'></span>
    </button>
    </span>
  </div>
</div>


<!-- position logo and limit the <a> to that image so it isn't easy to hit when reaching for the search box -->
<?php
$bSEEDIFrame = true;
if( $bSEEDIFrame ) {
    // Leave a space at the top for the search control - padding works better because margin forces the float to clear
    ?>
    <div style='padding-top:50px'>&nbsp;</div>
    <?php
} else {
    // Show the Bauta logo and link
    ?>
    <div style='width:400px;margin-left:auto;margin-right:auto'>
        <a href='<?php echo $sLogoLink; ?>' target='_blank'>
        <img src='<?php echo $sLogo; ?>'
             style='margin-left:auto;margin-right:auto;display:block;margin-bottom:20px'/>
        </a>
    </div>
    <?php
}
?>

<?php
/*
<p>This is above the SEEDSlider</p>
*/
?>
<div id='SEEDSlider_container'></div>
<?php
/*
<p>This is below the SEEDSlider</p>
*/
?>

</body>
</html>
