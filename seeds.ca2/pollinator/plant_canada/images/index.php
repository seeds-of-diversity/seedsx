<!DOCTYPE html> 
<html> 
	<head> 
    <meta charset="utf-8">
	<title>Electronic Floral Calender</title> 
	<meta name="viewport" content="width=device-width, initial-scale=1"> 
	<link rel="stylesheet" href="http://code.jquery.com/mobile/1.1.1/jquery.mobile-1.1.1.min.css" />
	<script src="http://code.jquery.com/jquery-1.7.1.min.js"></script>
	<script src="http://code.jquery.com/mobile/1.1.1/jquery.mobile-1.1.1.min.js"></script>
    
	<script src="main.js"></script>
    
    <style>
	p{
	text-align:justify;
	}
	</style>
</head> 
<body> 
<!-- Start of first page -->

<div data-role="page" id="main">

	<?php include("header.php"); ?>

	<div data-role="content">
	        <p>
        Welcome to the online floral calendar for Ontario's beekeepers. Using the folowing search link, you can quickly determine which plants are in bloom and the value of each blooming plant as nectar or pollen resources for bees. <br><br>
        	Floral calendars are an essential tool for beekeepers. They provide information on the yearly cycles in the flow of nectar for honey production and availability of pollen. This helps beekeepers maximize honey production while maintaining colony health.
            <br><br>
        </p>
        <br>
        <ul data-role="listview">
            <li><a href="#search">Search Plants</a></li>
            <li><a href="#about">About</a></li>
            <li><a href="#contact">Contact Us</a></li>
        </ul>
	
    <div>
    
    </div>
    
	</div><!-- /content -->

	
</div><!-- /page -->

<!-- Start of second page -->
<div data-role="page" id="about">

	<?php include("header.php"); ?>

	<div data-role="content">
	
		<p>Floral calendars are an essential tool for beekeepers. They provide information on the yearly cycles in the flow of nectar for honey production and availability of pollen. This helps beekeepers maximize honey production while maintaining colony health. This is the first time this type of information has been made available for Canadian beekeepers in an electronic and easy-to-access format.
		<br><br>
		This resource was created by the Canadian Pollination Initiative (NSERC-CANPOLIN) with financial support from the Ontario Ministry of Agriculture and Rural Affairs and the University of Guelph through the Knowledge Translation and Transfer (KTT) program. Seeds of Diversity is generously hosting the website.</p>
		
	</div><!-- /content -->

	<?php include("footer.php"); ?><!-- /footer -->
</div><!-- /page -->

<!-- Search page -->
<div data-role="page" id="search">

	<?php include("header.php"); ?>

	<div data-role="content">
     <h2>Search Plants</h2>
     <hr>
		<?php include("search.php"); ?>
	</div><!-- /content -->

	<?php include("footer.php"); ?>
</div><!-- /page -->

<!-- Result page -->
<div data-role="page" id="result">

	<?php include("header.php"); ?>

	<div data-role="content">
     <h2>Search Plants</h2>
     <hr>
		<!-- Seach Results-->
        
<div id="results_list"></div>	

</div><!-- /content -->

	<?php include("footer.php"); ?>
</div><!-- /page -->

<!-- Contact Us page -->
<div data-role="page" id="contact">

	<?php include("header.php"); ?>

	<div data-role="content">
     <h2>Contact Us</h2>
     <div class="inner_copy">
    <h4>By Email</h4>
    <hr />
    <div><a href="mailto:canpolin@uoguelph.ca" target="_blank">canpolin@uoguelph.ca</a>&nbsp;</div>
    <div><br />
    </div>
    <h4>By Mail</h4>
    <hr />
    <div>University of Guelph&nbsp;<br />
      c/o School of Environmental Sciences&nbsp;<br />
      SES-BOVEY, University of Guelph&nbsp;<br />
      Guelph, Ontario, Canada&nbsp;<br />
      N1G 2W1&nbsp;<br />
    </div>
    <div><br />
    </div>
    <h4>By Phone</h4>
    <hr />
    <div><a href="tel:519.824.4120%20ext%2058022" value="+15198244120" target="_blank">519.824.4120 ext 58022</a></div>
    <div><br />
    </div>
    <h4>By Fax</h4>
    <hr />
    <div><a href="tel:519.837.0442" value="+15198370442" target="_blank">519.837.0442</a></div>
  </div>
	</div><!-- /content -->

	<?php include("footer.php"); ?>
</div><!-- /page -->
</body>
