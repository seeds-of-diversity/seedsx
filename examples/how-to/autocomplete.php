<?php
if( !defined("SITEROOT") )  define("SITEROOT", "../office/");
include_once( SITEROOT."site.php" );

?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>jQuery UI Autocomplete - Default functionality</title>
  <link rel="stylesheet" href="<?php echo W_ROOT_JQUERY_UI_THEME_SMOOTHNESS; ?>">
  <script src="<?php echo W_ROOT_JQUERY; ?>"></script>
  <script src="<?php echo W_ROOT_JQUERY_UI; ?>"></script>

  <style>
  body {
	font-family: "Trebuchet MS", "Helvetica", "Arial",  "Verdana", "sans-serif";
	font-size: 62.5%;
}

  </style>

<script type="text/javascript" charset="utf-8">
  (function($){
    $(function() {
      var availableTags = [
        "ActionScript",    "AppleScript",    "Asp",          "BASIC",
        "C",               "C++",            "Clojure",      "COBOL",
        "ColdFusion",      "Erlang",         "Fortran",      "Groovy",
        "Haskell",         "Java",           "JavaScript",   "Lisp",
        "Perl",            "PHP",            "Python",       "Ruby",
        "Scala",           "Scheme"
      ];
      $( "#myinput" ).autocomplete({
          source: availableTags
      });

      $('#myform').submit(function(){
          alert( $(this).serialize() );
          return false;
      });
    });
  })(jQuery);
</script>
</head>
<body>

<form id='myform'>
<div class="ui-widget">
  <label for="myinput">Choose: </label>
  <input id="myinput" name="myinput"/>
  <input type='submit' value='Submit'/>
  </div>
</form>


</body>
</html>