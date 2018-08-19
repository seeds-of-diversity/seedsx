<?

$catTitleSQL = "SELECT T2.cat_id as cat_id, T1.name as name, T2.issue as issue FROM cat_company T1,cat_catalog T2 WHERE T1.cmp_id=T2.cmp_id";


function cat_dbc_catTitles()
/***************************
 */
{
    global $catTitleSQL;

    $dbc = db_open( $catTitleSQL. " ORDER BY T1.name DESC" );
    if( !$dbc )  db_error_die();
    return( $dbc );
}

function cat_catTitle( $cat_id )
/*******************************
 */
{
    global $catTitleSQL;

    $ra = db_query( $catTitleSQL. " AND T2.cat_id=$cat_id" );
    if( !$ra )  db_error_die();
    return( $ra );
}

?>
