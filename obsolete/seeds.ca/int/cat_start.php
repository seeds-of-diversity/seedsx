<?
/* Catalogue inventory updater portal - admin permissions required
 *
 * Lists the companies, allows new company to be added - self-inserting page
 */
include_once( "../site.php" );
include_once( SITEINC."sitedb.php" );
include_once( "_cat.php" );
include_once( SITEINC ."sodlogin.php" );

$la = new SoDLoginAuth;
if( !$la->SoDLoginAuth_Authenticate( $_REQUEST, "W perm_cat" ) ) { exit; }

$new_cmp_name = @$_REQUEST['new_cmp_name'];

//page_header( "Catalogue Inventory Updater" );
//page_header2( "Catalogue Inventory - Companies" );


/* If there is a new company, add it to the table
 */
if( !empty($new_cmp_name) ) {
    $name = db_query1( "SELECT name FROM cat_company WHERE name='$new_cmp_name'" );
    if( empty($name) ) {
        $query = "INSERT INTO cat_company (name) VALUES ('$new_cmp_name')";
        if( !($cmp_id = db_insert_autoinc_id( $query )) ) die(db_errmsg($query));
        $year = 2004;
        $query = "INSERT INTO cat_catalog (cmp_id,issue,year) VALUES ($cmp_id,'$year',$year)";
        if( !($cat_id = db_insert_autoinc_id( $query )) ) die(db_errmsg($query));
    }
}



/* List the companies
 */

echo "<TABLE width=".ALT_PAGE_WIDTH." align=center><TR><TD>";

$dbc = cat_dbc_catTitles();
while( $ra = db_fetch( $dbc ) ) {
    echo "<P><A HREF='cat_cat.php?cat_id=${ra['cat_id']}&".$la->login_auth_get_urlparms()."'>${ra['name']} ${ra['issue']}</A></P>";
}


/* Field to add a new company - uses the inserter at the top of this page
 */
echo "<FORM action='${_SERVER['PHP_SELF']}' method=get>";
echo $la->login_auth_get_hidden();
echo "<P>Add new catalogue name: <INPUT TYPE=TEXT NAME=new_cmp_name WIDTH=30> <INPUT TYPE=SUBMIT VALUE=Save></P>";
echo "</FORM>";


echo "</TD></TR></TABLE>";


//page_footer();
?>
