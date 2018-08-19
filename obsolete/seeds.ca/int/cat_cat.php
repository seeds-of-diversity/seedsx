<?
// TODO: do I have to urlescape HIDDEN fields?
// Trim all input fields


/* Catalogue Inventory - Catalogue Updater - admin permissions required
 *
 * Given a cat_id, show the items in that catalogue and allow new items to be added - self-inserting page.
 *
 * From catalogue list page
 * $cat_id  = catalogue id
 *
 * From PHP_SELF (updater parms)
 * $osN     = new ospecies (N is the field number linking this to onN)
 * $onN     = new oname (N is the field number linking this to osN)
 * $n       = number of possible fields - the range of 1..N
 * $dM      = delete cat_item where item_id=M
 */
include_once( "../site.php" );
include_once( SITEINC."sitedb.php" );
include_once( "_cat.php" );
include_once( SITEINC ."sodlogin.php" );

$la = new SoDLoginAuth;
if( !$la->SoDLoginAuth_Authenticate( $_REQUEST, "W perm_cat" ) ) { exit; }

$cat_id = @$_REQUEST['cat_id'];
$raCmp = array();
if( !empty($cat_id) ) {
    $raTitle = cat_catTitle( $cat_id );
}
if( !isset($raTitle['name'] ) )  BXStd_HttpRedirect( "cat_start.php" );


page_header( "Catalogue Inventory Updater" );
page_header2( "Catalogue Inventory - ${raTitle['name']} ${raTitle['issue']}" );


/* See if any items need to be deleted
 */
foreach( $_REQUEST as $k => $v ) {
    if( $k{0} == 'd' && $v == 1 ) {
        $k = substr( $k, 1 );
        if( ctype_digit( $k ) ) {
            db_exec( "DELETE FROM cat_item WHERE item_id=$k" ); // or db_error_die();  don't die when I reload the page
        }
    }
}

/* See if any items need to be added.  $osN and $onN should both exist, where N is an arbitrary field number 1..M,
 * with no relationship to db row numbers and not necessarily contiguous with other field numbers
 */
foreach( $_REQUEST as $k => $v ) {
    if( substr( $k, 0, 2 ) == 'os' && ctype_digit( substr( $k, 2 ) ) ) {
        $nField = substr( $k, 2 );
        $os = $v;
        $on = @$_REQUEST['on'.$nField];
        if( !empty($os) && !empty($on) ) {
            // check for dups
            $os = BXStd_MagicAddSlashes( $os );
            $on = BXStd_MagicAddSlashes( $on );

            if( !db_query( "SELECT * FROM cat_item WHERE cat_id=$cat_id AND ospecies='$os' AND oname='$on'" ) ) {
                db_exec( "INSERT INTO cat_item (cat_id,ospecies,oname) VALUES ($cat_id,'$os','$on')" ) or db_error_die();
            }
        }
    }
}


echo "<TABLE width=".ALT_PAGE_WIDTH." align=center border=0><TR><TD>";


/* Show the list of items for this catalog
 */
$i = 1;     // the field number for new os,on fields
echo "<FORM action='${_SERVER['PHP_SELF']}' method=post>";
echo "<INPUT TYPE=HIDDEN NAME=cat_id VALUE=$cat_id>";
echo $la->login_auth_get_hidden();
echo "<TABLE border=0><TR><TD>&nbsp;</TD><TD>&nbsp;</TD><TD><B>Delete</B></TD></TR><TR>";

$lots = @$_REQUEST['lots'];
if( !empty($lots) ) {
    /* Show this section first, with lots of space for new entries
     */
    $lots = BXStd_MagicAddSlashes( $lots );
    $dbc = db_open( "SELECT * FROM cat_item WHERE cat_id=$cat_id AND ospecies = '$lots' ORDER BY ospecies,oname" );
    if( !$dbc )  db_error_die();
    drawRows( $dbc, $i, true );
}


$dbc = db_open( "SELECT * FROM cat_item WHERE cat_id=$cat_id".(empty($lots) ? "" : " AND ospecies <> '$lots'")." ORDER BY ospecies,oname" );
if( !$dbc )  db_error_die();
drawRows( $dbc, $i );

echo "<TR><TD>Species</TD><TD>Name</TD><TD>&nbsp;</TD>";
fieldsForNewNames( "", $i );
echo "</TABLE>";
echo "<P><INPUT TYPE=SUBMIT VALUE=Save></P>";
echo "</FORM>";

echo "</TABLE>";

page_footer();


function fieldsForNewNames( $os, &$i, $bLots = false )
/*****************************************************
 */
{
    for( $j = 0; $j < ($bLots ? 20 : (empty($os) ? 10 : 3)); ++$j ) {
        if( empty($os) ) {
            // Let the user enter the ospecies
            echo "<TR><TD width=100>";
            echo "<INPUT TYPE=TEXT NAME=os$i WIDTH=30>";
            echo "</TD><TD>";
        } else {
            // use the given ospecies
            echo "<TR><TD width=100>&nbsp;</TD><TD>";
            echo "<INPUT TYPE=HIDDEN NAME=os$i VALUE='$os'>";
        }
        echo "<INPUT TYPE=TEXT WIDTH=30 NAME=on$i></TD><TD>&nbsp;</TD></TR>";
        ++$i;
    }
    if( !empty($os) ) {
        echo "<TR><TD>&nbsp;</TD><TD colspan=2><INPUT TYPE=RADIO NAME=lots VALUE='$os'>give me lots more space for $os</TD></TR>";
        echo "<TR><TD colspan=3><HR></TD></TR>";
    }
}


function drawRows( $dbc, &$i, $bLots = false )
/*********************************************
 */
{
    $os = "";
    while( $ra = db_fetch( $dbc ) ) {
        if( $ra['ospecies'] != $os ) {
            /* Finish off this ospecies section and start the next one
             */
            if( !empty($os) ) {
                /* Input fields to add new onames for this ospecies
                 */
                fieldsForNewNames( $os, $i );
            }

            echo "<TR><TD width=100><B>".$ra['ospecies']."</B>";
            $os = $ra['ospecies'];
        } else {
            echo "<TR><TD width=100>&nbsp;";
        }
        echo "</TD><TD>".$ra['oname']."</TD>";
        echo "<TD><INPUT TYPE=CHECKBOX NAME=d${ra['item_id']} VALUE=1></TD></TR>";
    }
    if( !empty($os) ) {
        fieldsForNewNames( $os, $i, $bLots );
    }
}

?>
