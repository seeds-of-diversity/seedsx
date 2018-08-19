<P>This shows how to submit form elements through links.</P>

<H4>Method 1</H4>
<P>Type something into the input field and either press enter to submit the form the normal way, or click the link to submit it the sneaky way.</P>
<?php 
    if( !empty($_GET['fld']) )  echo "<P>You typed <B>".$_GET['fld']."</B> and clicked the link.</P>";
    if( !empty($_GET['bar']) )  echo "<P>You typed <B>".$_GET['bar']."</B> and hit enter to send the form.</P>";
?>
<FORM method='get' name='f'>
<INPUT type='text' name='bar'/>
<A href='#' onclick="v=document.f.bar.value;parent.top.location='js_form_submit_test.php?fld='+v">Click Here</A>
</FORM>

<H4>Method 2</H4>
<?php 
echo "<SCRIPT>"
    ."function goPost() { var d = document.forms[1]; d.elements['k'].value=23; d.submit(); }"
    ."</SCRIPT>";

echo "<FORM method='POST'>"
    ."<DIV>"
    ."<P onclick='goPost()' style='color:blue'>This looks like a link but it POSTS k=23</P>"
    ."<P>&nbsp;</P>"
    ."k: <INPUT type='text' name='k' value='".@$_REQUEST['k']."'/>&nbsp;&nbsp;<INPUT type='submit'>"
    ."</DIV></FORM>";
?>

<? var_dump($_REQUEST); ?>
