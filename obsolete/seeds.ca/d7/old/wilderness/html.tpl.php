<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $language->language; ?> dir="<?php echo $language->dir; ?>" <?php echo $rdf_namespaces; ?>>
  <head profile="<?php print $grddl_profile; ?>">
    <title><?php if (isset($head_title )) echo $head_title; ?></title>
    <meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" />
    <?php echo $head; ?>
    <?php echo $styles ?>
    <?php echo $scripts ?>
    <!--[if IE 6]><link rel="stylesheet" href="<?php echo base_path() . $directory; ?>/style.ie6.css" type="text/css" /><![endif]-->  
    <!--[if IE 7]><link rel="stylesheet" href="<?php echo base_path() . $directory; ?>/style.ie7.css" type="text/css" media="screen" /><![endif]-->
    <!--[if IE 8]><link rel="stylesheet" href="<?php echo base_path() . $directory; ?>/style.ie8.css" type="text/css" media="screen" /><![endif]-->
    <script type="text/javascript"><?php /* Needed to avoid Flash of Unstyle Content in IE */ ?> </script>
  </head>

  <body class="<?php print $classes; ?>" <?php print $attributes;?>>
<?php echo $page_top; ?>
<?php echo $page; ?>
<?php echo $page_bottom; ?>
  </body>
</html>
