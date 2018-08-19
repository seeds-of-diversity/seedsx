<html>
<head><link rel="stylesheet" href="../lib/css/SimpleCalendar.css" type='text/css' /></head>
<body>
<?php
error_reporting(E_ALL ^ E_WARNING);
require_once('../lib/donatj/SimpleCalendar.php');

$calendar = new donatj\SimpleCalendar();

//$calendar->setStartOfWeek('Sunday');

$calendar->addDailyHtml( 'Sample Event', 'today', 'tomorrow' );
$calendar->addDailyHtml( 'Sample Event 2', 'today', 'tomorrow' );
$calendar->addDailyHtml( 'Sample Event 3', 'October 31, 2013' );

echo "<div style='float:left;padding:20px'>"
    .$calendar->setDate('September 2013')
    .$calendar->show()
    ."</div>";

echo "<div style='float:left;padding:20px'>"
    .$calendar->setDate('October 2013')
    .$calendar->show()
    ."</div>";

?>
</body>
</html>