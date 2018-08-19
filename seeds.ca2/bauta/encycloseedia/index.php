<?php
define( "SITEROOT", "../../" );
include_once( SITEROOT."site.php" );
include_once( "../QServer.php" );


$qObj = array( 'A' => array( 'qCode'=>0, 'htmlSmall'=>'', 'html'=>'' ),
               'B' => array( 'qCode'=>0, 'htmlSmall'=>'', 'html'=>'' ),
               'C' => array( 'qCode'=>0, 'htmlSmall'=>'', 'html'=>'' ),
               'D' => array( 'qCode'=>0, 'htmlSmall'=>'', 'html'=>'' ),
               'E' => array( 'qCode'=>0, 'htmlSmall'=>'', 'html'=>'' ),
               'F' => array( 'qCode'=>0, 'htmlSmall'=>'', 'html'=>'' ),
               'G' => array( 'qCode'=>0, 'htmlSmall'=>'', 'html'=>'' ),
               'H' => array( 'qCode'=>0, 'htmlSmall'=>'', 'html'=>'' ),
               'I' => array( 'qCode'=>0, 'htmlSmall'=>'', 'html'=>'' ),
);

if( false ) {  // true to debug the slider
    foreach( $qObj as $k => $v ) {
        $qObj[$k]['qCode'] = substr(md5(rand()),0,3);
        $qObj[$k]['htmlSmall'] = $k;
        $qObj[$k]['html'] = "Big $k";
    }
}

$oQ = new QServer();


if( ($q = SEEDSafeGPC_GetStrPlain( 'qSearch' )) ) {
    $qObj['E']['html'] = $oQ->Search( $q );
} else
if( ($q = SEEDSafeGPC_GetStrPlain( 'qCode' )) ) {
    $oQ->QObjFromCode( $q, $qObj );
} else {
    $qObj['E']['html'] = "<h2>Search</h2><p>for a seed variety, company, or place</p>";
}

$sInitialJS =
    "QObjInitial = ".json_encode( $qObj ).";";




function writeBorder()
{
    $s = "
    <div class='c01PhrameBlock-tl c01PhrameBlockWhite-tl'></div>
    <div class='c01PhrameBlock-tc c01PhrameBlockWhite-tc'></div>
    <div class='c01PhrameBlock-tr c01PhrameBlockWhite-tr'></div>
    <div class='c01PhrameBlock-cl c01PhrameBlockWhite-cl'></div>
    <div class='c01PhrameBlock-cc c01PhrameBlockWhite-cc'></div>
    <div class='c01PhrameBlock-cr c01PhrameBlockWhite-cr'></div>
    <div class='c01PhrameBlock-bl c01PhrameBlockWhite-bl'></div>
    <div class='c01PhrameBlock-bc c01PhrameBlockWhite-bc'></div>
    <div class='c01PhrameBlock-br c01PhrameBlockWhite-br'></div>
    ";
    return( $s );
}



$sBootstrap = W_ROOT."os/bootstrap3/dist/";


?>
<html>
<head>
<link rel='stylesheet' type='text/css' href='<?php echo W_ROOT; ?>seedcommon/SEEDSlider/SEEDSlider.css'></link>
<link rel='stylesheet' type='text/css' href='<?php echo W_ROOT; ?>seedcommon/console/console01phrameset/console01phrameset.css'></link>

<script src="http://code.jquery.com/jquery-1.9.1.js"></script>
<script src="http://code.jquery.com/ui/1.10.3/jquery-ui.js"></script>
<script src="<?php echo W_ROOT; ?>seedcommon/SEEDSlider/SEEDSlider.js"></script>

    <!-- Bootstrap -->
    <link href="<?php echo $sBootstrap; ?>css/bootstrap.min.css" rel="stylesheet">

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
    <![endif]-->

</head>
<!-- <body style='background-color:#aff'> -->
<body style='background-image:url(../back1.jpg);background-size:100% auto'>


<div style='width:100%'>
<img src='http://www.seedsecurity.ca/templates/default/css/header/title/title.png'
     style='margin-left:auto;margin-right:auto;display:block;margin-bottom:20px'/>
</div>

<div style='float:right;margin-top:-70px'>
<form action='index.php' method='post'>
<input type='text' name='qSearch' value=''/> <input type='submit' value='Search'/>
</form>
</div>

<div style='width:800px;margin:auto;'>

<div id='SEEDSlider_container'>
<div id='SEEDSlider_A' class='SEEDSlider_box1'><?php echo writeBorder()/*. $sHtml['A']*/; ?></div>
<div id='SEEDSlider_B' class='SEEDSlider_box1'><?php echo writeBorder()/*. $sHtml['B']*/; ?></div>
<div id='SEEDSlider_C' class='SEEDSlider_box1'><?php echo writeBorder()/*. $sHtml['C']*/; ?></div>
<div id='SEEDSlider_D' class='SEEDSlider_box1'><?php echo writeBorder()/*. $sHtml['D']*/; ?></div>
<div id='SEEDSlider_E' class='SEEDSlider_box2'><?php echo writeBorder()/*. $sHtml['E']*/; ?></div>
<div id='SEEDSlider_F' class='SEEDSlider_box1'><?php echo writeBorder()/*. $sHtml['F']*/; ?></div>
<div id='SEEDSlider_G' class='SEEDSlider_box1'><?php echo writeBorder()/*. $sHtml['G']*/; ?></div>
<div id='SEEDSlider_H' class='SEEDSlider_box1'><?php echo writeBorder()/*. $sHtml['H']*/; ?></div>
<div id='SEEDSlider_I' class='SEEDSlider_box1'><?php echo writeBorder()/*. $sHtml['I']*/; ?></div>
</div>

</div>

<script>
<?php echo $sInitialJS; ?>
</script>

</div>
</body></html>

