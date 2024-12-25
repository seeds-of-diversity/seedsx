<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<?php

//echo "HTML";
include( "dr_common.php" );

?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $language->language; ?> dir="<?php echo $language->dir; ?>" <?php echo $rdf_namespaces; ?>>
  <head profile="<?php print $grddl_profile; ?>">
    <title><?php if (isset($head_title )) echo $head_title; ?></title>
    <meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" />
    <?php echo $head; ?>
    <?php echo $styles ?>
    <?php echo $scripts ?>
    <link rel="stylesheet" href="<?php echo $tpl_sThemeCommonDir; ?>/style-seeds.css" type="text/css" />
    <!--[if IE 6]><link rel="stylesheet" href="<?php echo $tpl_sThemeDir; ?>/style.ie6.css" type="text/css" /><![endif]-->
    <!--[if IE 7]><link rel="stylesheet" href="<?php echo $tpl_sThemeDir; ?>/style.ie7.css" type="text/css" media="screen" /><![endif]-->
    <!--[if IE 8]><link rel="stylesheet" href="<?php echo $tpl_sThemeDir; ?>/style.ie8.css" type="text/css" media="screen" /><![endif]-->

    <?php
/*
    green blockheader top and bot are from wilderness blockheader.
    green dark is by Taa
    green med is halfway between top and bot.  (a simple ascension of 99 53% 60 is too bright)
    green light is Taa's dark, lightened up to 90%

    blue dark is by Taa
    blue med and light have the H of Taa's blue, an S that looks conservative, and L that ascends linearly

    yellow dark is by Taa
    yellow med has the HS of Taa's yellow and nearly the L of green med
    yellow med and light have the HS of Taa's yellow, and L that ascends about linearly (med looks better a little darker)

    brown dark is by Steven
    an ascension of L makes orange and pink, so...
    brown med and light have H lowered to 50 to preserve the contrast, and L ascends in steps that look good

    magenta dark is by Taa
    magenta med and light have the HS of Taa's magenta and L that ascends like other colours

    blockheaders other than green are the med colour +/- 7L just like the green wilderness blockheader

                     RGB        HSL
    Green,   dark:   #3e7223    99� 53%  29%  (Taa)           blockheader top: #a7d388  95� 46% 68%
             med:    #94c96e    95  46%  61%                              bot: #80bf52  95� 46% 54%
             light:  #e1f3d8    99  53%  90%

    Blue,    dark:   #0c6094   203  85%  31%  (Taa)                       top:  7DB6D9 203  55  67
             med:    #61a6d1   203  55%  60%
             light:  #d7e9f4   203  55%  90%                              bot:  4597C9 203  55  53

    Yellow,  dark:   #fdb912    43  98%  53%  (Taa)                       top:  FEDD8B  43  98  77
             med:    #fdd368    43  98%  70%
             light:  #fef0cd    43  98%  90%                              bot:  FDC944  43  98  63

    Brown,   dark:   #944a00    30 100%  29%                              top:  C8915B  30  50  57
             med:    #bf8040    30  50%  50%
             light:  #ecd9c6    30  50%  85%                              bot:  A46E37  30  50  43

    Magenta, dark:   #ac3e8c   317  47%  46%  (Taa)                       top:  D284BC 317  46  67
             med:    #c969ae   317  46%  60%
             light:  #f1daeb   317  46%  90%                              bot:  BE509F 317  46  53
 */

        switch( $tpl_sTheme ) {
            case 'blue':    $sMed = '61a6d1';  $sLight = 'd7e9f4';  break;
            case 'yellow':  $sMed = 'fdd368';  $sLight = 'fef0cd';  break;
            case 'brown':   $sMed = 'bf8040';  $sLight = 'ecd9c6';  break;
            case 'magenta': $sMed = 'c969ae';  $sLight = 'f1daeb';  break;
            case 'green':
            default:        $sMed = '91c877' /*'94c96e'*/;  $sLight = 'e1f3d8';  break;
        }
    ?>
    <style>
    .Sheet-border { background-color:#<?php echo $sMed; ?>; }
    .Sheet-body   { }
    #SeedHeader   { background-color_notused:#<?php echo $sLight; ?>;
                    background: url(<?php echo $tpl_sThemeCommonDir."/sod_images/h01_${tpl_sTheme}.jpg";?>) right top no-repeat;
                  }
    #SeedMenuTop { background-color:#<?php echo $sMed; ?>; }

    #SeedIconsLeft { width:120px; height:600px; background-image: url("<?php echo $tpl_sThemeCommonDir."/sod_images/icons01left-$tpl_sTheme.png"; ?>"); }

    .header_icons { }
    .body         { }
    .BlockHeader .t { color: <?php echo ($tpl_sTheme=='yellow' ? "#333" : "#fff"); ?>; }
    .BlockHeader .l, .BlockHeader .r { background-image: url("<?php echo $tpl_sThemeCommonDir."/images/BlockHeader_{$tpl_sTheme}.png"; ?>"); }

    </style>

    <script type="text/javascript"><?php /* Needed to avoid Flash of Unstyle Content in IE */ ?> </script>
  </head>

  <body class="<?php print $classes; ?>" <?php print $attributes;?>>
<?php echo $page_top; ?>
<?php echo $page; ?>
<?php echo $page_bottom; ?>
  </body>
</html>
