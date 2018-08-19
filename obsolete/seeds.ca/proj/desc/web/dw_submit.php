<?
/*
CREATE TABLE desc_submit_1 (    -- unique row per submission

        _rowid      INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT,
        _created    DATETIME,
        _created_by INTEGER,
        _updated    DATETIME,
        _updated_by INTEGER,
        _status     INTEGER DEFAULT 0,

    sp  VARCHAR(100)
);
CREATE TABLE desc_submit_2 (    -- descriptors keyed by submission

        _rowid      INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT,
        _created    DATETIME,
        _created_by INTEGER,
        _updated    DATETIME,
        _updated_by INTEGER,
        _status     INTEGER DEFAULT 0,

    fk_ds1  INTEGER,
    k       VARCHAR(100),
    v       TEXT
);
*/

include_once( "_dw.php" );
include_once( SITEINC."siteKFDB.php" );

$dw_sp = new BXStd_SafeGPCStr( "dw_sp" );
if( $dw_sp->IsEmpty() )  BXStd_HttpRedirect( "../index.php" );

$kfdb = SiteKFDB() or die( "Cannot connect to database" );

$submit_id = $kfdb->KFDB_InsertAutoInc( "INSERT INTO desc_submit_1 (_rowid,_created,sp) VALUES (NULL,NOW(),'".$dw_sp->DB()."')" );
if( !$submit_id )  die( "Cannot update database" );

foreach( $_REQUEST as $k => $v ) {
    if( $k == "dw_sp" ) continue;

    $k = BXStd_MagicAddSlashes( $k );
    $v = BXStd_MagicAddSlashes( $v );
    if( !empty($v) )
        $kfdb->KFDB_Execute( "INSERT INTO desc_submit_2 (_rowid,_created,fk_ds1,k,v) VALUES (NULL,NOW(),$submit_id,'$k','$v')" );
}


echo "<DIV style='font-family:verdana,helvetica,arial,sans serif'>";
echo "<DIV><IMG src='".SITEIMG."logo_EN.gif'></DIV>";
echo "<H3>Descriptive Keys Submission</H3>";
echo "<P>Thankyou for sending your observations.  Your information will be reviewed and posted on this web site soon.</P>";





?>
