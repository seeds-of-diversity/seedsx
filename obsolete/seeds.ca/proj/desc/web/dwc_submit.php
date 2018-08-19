<?
/* Process multiple submissions from a chart.
 * Each variety is a separate submission. All varieties must be the same species.
 *
 * dw_sp    = species name
 * dwc0_*   = common fields that are written to every submission
 * dwc{n}_* = submitted data fields. One of these has to be dwc{n}_common_SoD_s__cultivarname
 *
 * Limitation: implementation assumes that {n} is a single digit
 */

include_once( "_dw.php" );
include_once( SITEINC."siteKFDB.php" );

$dw_sp = new BXStd_SafeGPCStr( "dw_sp" );
if( $dw_sp->IsEmpty() )  BXStd_HttpRedirect( "../index.php" );

$kfdb = SiteKFDB() or die( "Cannot connect to database" );

/* Collect the parms into an array, keyed by the dwc{n}_ number
 */
$raParms = array();
foreach( $_REQUEST as $k => $v ) {
    $k = BXStd_MagicStripSlashes( $k );
    $v = BXStd_MagicStripSlashes( $v );

    if( empty($v) ) continue;
    if( substr( $k, 0, 3 ) != "dwc" ) continue;

    $raParms[substr( $k, 3, 1 )][substr( $k, 5 )] = $v;
}


$nFields = $nCols = 0;

for( $i == 1; $i <= 9; ++$i ) {
    if( !count($raParms[$i]) ) continue;

    if( count($raParms[$i]) == 1 && isset($raParms[$i]['common_SoD_s__cultivarname']) )  continue;


    $submit_id = $kfdb->KFDB_InsertAutoInc( "INSERT INTO desc_submit_1 (_rowid,_created,sp) VALUES (NULL,NOW(),'".$dw_sp->DB()."')" );
    if( !$submit_id )  die( "Cannot update database" );

    ++$nCols;

    /* Insert the common parms
     */
    foreach( $raParms[0] as $k => $v ) {
        $k = addslashes($k);
        $v = addslashes($v);
        $kfdb->KFDB_Execute( "INSERT INTO desc_submit_2 (_rowid,_created,fk_ds1,k,v) VALUES (NULL,NOW(),$submit_id,'$k','$v')" );
        ++$nFields;
    }

    /* Insert the data parms for this column
     */
    foreach( $raParms[$i] as $k => $v ) {
        $k = addslashes($k);
        $v = addslashes($v);
        $kfdb->KFDB_Execute( "INSERT INTO desc_submit_2 (_rowid,_created,fk_ds1,k,v) VALUES (NULL,NOW(),$submit_id,'$k','$v')" );
        ++$nFields;
    }
}


echo "<DIV style='font-family:verdana,helvetica,arial,sans serif'>";
echo "<DIV><IMG src='".SITEIMG."logo_EN.gif'></DIV>";
echo "<H3>Descriptive Keys Submission</H3>";
echo "<P>Thankyou for sending your observations.  Your information will be reviewed and posted on this web site soon.</P>";
echo "<P>&nbsp;</P>";
echo "<P>Added $nFields fields from $nCols columns.</P>";



?>
