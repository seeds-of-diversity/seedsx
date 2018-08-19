<?
/* This should work with or without cookies
 */

session_start();
if(isset($_GET['restart'])) {
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time()-42000, '/');
    }
    session_destroy();
    session_start();
    echo "<P>Destroyed current session, started a new session</P>";
} else if(isset($_GET['regenerate'])) {
    session_regenerate_id();
    echo "<P>Retained current session, generated a new session id.</P>";
}

$_SESSION['session_test_count'] = intval(@$_SESSION['session_test_count']) + 1;

echo "<P>Session Var Counter = ".$_SESSION['session_test_count']."</P>"
    ."<P>session id : ".session_id()."</P>"
    ."<P>session name : ".session_name()."</P>"
    ."<P>SID : ".SID."</P>";

echo "<P><A HREF='{$_SERVER['PHP_SELF']}?".SID."'>Link to this page (increments counter)</A></P>";

echo "<FORM>";
if(!isset($_COOKIE[session_name()])) {
    echo "<INPUT type='hidden' name='".session_name()."' value='".session_id()."'>";
}
echo "<INPUT type='submit' value='Reload page with form (increments counter)'></FORM>";

echo "<P><A HREF='session_test.php?restart=1&".SID."'>Start a new session using session_destroy();session_start();</P>"
    ."<P><A HREF='session_test.php?regenerate=1&".SID."'>Change the session id using session_regenerate_id();</P>";

?>
