<?php
// $bCanada must be set

include_once( "view.php" );

$result_string = Search();

$s = "";

switch( @$_REQUEST['cmd'] ) {
    case 'contact':
        $s = $oTmpl->ExpandTmpl( "contact", $raTmplVars );
        break;

    case 'login':
        $raTmplVars['bLoginFail'] = false;

        if( isset($_POST['username']) && isset($_POST['password']) ) {
            if( $_POST['username'] == "admin" && $_POST['password'] == "nectar" ) {
                $_SESSION['user'] = "admin";
                header( "Location: admin.php" );
                exit;
            } else {
                $raTmplVars['bLoginFail'] = true;
            }
        }

        $raTmplVars['sHead'] = ViewHead( "Honey Plants : Login" );
        $s = $oTmpl->ExpandTmpl( "login", $raTmplVars );
        break;

    case 'logout':
        session_destroy();
        header("Location: index.php");
        exit;
        break;

    default:
        $raTmplVars['sSearchPanel'] = ViewSearchPanel();
        $s = $oTmpl->ExpandTmpl( "main", $raTmplVars );
        break;
}

echo $s;

?>
