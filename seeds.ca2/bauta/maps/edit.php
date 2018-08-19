<?php

define( "SITEROOT", "../../" );
include( SITEROOT."site.php" );
include_once( STDINC."SEEDTable.php" );
include_once( SEEDCOMMON."console/console01.php" );
include_once( SEEDCOMMON."googleAPI.php" );
include( "_maps.php" );

list($kfdb, $sess, $lang) = SiteStartSessionAccount( array('BautaMap'=>'W') );

$oMap = new BautaMap( $kfdb );

$oG = new SEEDSGoogleMaps();

$sErr = "";

$oForm = new SEEDForm( 'A' );
$oFormB = new SEEDForm( 'B' );
$oFormB->Update();

if( @$_REQUEST['cmd'] == 'download' ) {
    /* Download to spreadsheet
     */
    // N.B. data is stored in utf8, which PHPExcel requires here
    SEEDTable_OutputXLSFromRASheets( $oMap->GetMarkersSheets(),
                                   array( 'columns' => array('cat','note','name','address','latitude','longitude'),
                                          'filename'=>'bautamaps.xls',
                                          'created_by'=>$sess->GetName(), 'title'=>'Bauta Maps' ) );
    exit;
}

// This data is gathered, stored, and output in utf8
// Console01Static::HTMLPage outputs utf8 by default
//header( "Content-Type:text/html; charset=utf-8" );


/* Dev machines might not be able to process SEEDForm edits because max_input_vars might be lower than the number of $_REQUEST parms.
 * Uncomment the var_dump($_REQUEST) and see how large the array is. Compare with max_input_vars in php.
 * Production server is set much larger.
 */
//var_dump($_REQUEST);
//$kfdb->SetDebug(2);


if( @$_REQUEST['cmd'] == 'upload' ) {
    /* Upload spreadsheet and overwrite the whole oTable
     */
    $def = array( 'raSEEDTableDef' => array( 'headers-required' => array('cat','name','address','latitude','longitude'),
                                             'charset'=>'utf-8' ),
                  'eLoadType' => "MultiSheet" );
    list($bOk,$raSheets,$sErr) = SEEDTable_LoadFromUploadedFile( 'uploadfile', $def );
    if( !$bOk ) {
        echo "<div style='border:1px solid black;color:red;padding:10px;'>$sErr</div>";
    } else {
        /* Delete the oTable and reload it from $raRows
         */
        $raDel = $oMap->GetMarkers();
        foreach( $raDel as $k => $raDummy ) {
            $oMap->oTable->DeleteRow( $k );
        }
        foreach( $raSheets as $sheetName => $raRows ) {
            foreach( $raRows as $raR ) {
                $oMap->StoreMarker( 0, $sheetName, $raR );
            }
        }
    }

} else {
    /* Load the TablesLite oTable from http parms.
     * Get the http parms in 2D array, iterate through that array to insert/update/delete in the oTable
     */
    $ra = $oForm->oFormParms->Deserialize( $_REQUEST, true );

    if( isset($ra['rows']) ) {
        foreach( $ra['rows'] as $raR ) {
            if( ($k = $raR['k']) ) {
                /* Update if name is given
                 * Delete if name is blank
                 */
                if( @$raR['values']['name']) {
                    $raT = $oMap->oTable->GetRowByKey2( $k, array('k1'=>'sheet','k2'=>'cat') );

                    if( $raR['values']['cat']     != $raT['cat']   ||
                        $raR['values']['name']    != $raT['name']  ||
                        $raR['values']['address'] != $raT['address'] )
                    {
                        $raStore = array( 'cat' => $raR['values']['cat'],
                                          'name' => $raR['values']['name'],
                                          'address' => $raR['values']['address'] );
                        // lat/long are not propagated by http because they are correlated to address. Recompute them if the address changes.
                        if( $raR['values']['address'] && $raR['values']['address'] == $raT['address'] ) {
                            $raStore['latitude'] = $raT['latitude'];
                            $raStore['longitude'] = $raT['longitude'];
                        }

                        $oMap->StoreMarker( $k, $raT['values']['sheet'], $raStore );
                    }
                } else {
                    $oMap->oTable->DeleteRow( $k );
                }
            } else {
                // k==0: insert if marker name not empty, otherwise ignore
                if( @$raR['values']['name']) {
                    $oMap->StoreMarker( 0, "SHEET_XYZ", $raR['values'] );
                }
            }
        }
    }
}


$raSheets = $oMap->GetMarkersSheets();

$s = "<h2>Our Bauta Initiative Maps</h2>"
    ."<table cellpadding='10' border='1'><tr>"
    ."<td valign='top'>"
        ."Download all records to a spreadsheet<br/><br/>"
        ."<form action='${_SERVER['PHP_SELF']}' method='post' target='_blank'>"
        ."<input type='hidden' name='cmd' value='download'/>"
        ."<input type='submit' value='Download'/>"
        ."</form>"
    ."</td><td valign='top'>"
        ."Upload spreadsheet (this overwrites everything!)<br/><br/>"
        ."<form action='${_SERVER['PHP_SELF']}' method='post' enctype='multipart/form-data'>"
        ."<input type='hidden' name='MAX_FILE_SIZE' value='10000000' />"
        ."<input type='hidden' name='cmd' value='upload' />"
        ."<input type='file' name='uploadfile'/>"
        ."<input type='submit' value='Upload'/>"
        ."</form>"
    ."</td></tr></table>";

if( !count($raSheets) ) {
    $s .= "<p>No sheets loaded</p>";
    goto done;
}

$currSheetName = $oFormB->Value('chooseSheet');

$raOpts = array("-- Choose Sheet --"=>"");
foreach( $raSheets as $sheetname => $raRows ) {
    $raOpts[$sheetname] = $sheetname;
}
$s .= "<br/><form method='post'>"
     ."<div class='container'><div class='row'>"
       ."<div class='col-sm-3'>".$oFormB->Select2( 'chooseSheet', $raOpts, "", array( 'attrs'=>"onchange='submit()'" ) )."</div>"
       .($currSheetName ? ("<div class='col-sm-3'><a href='index.php?sheet=".urlencode($currSheetName)."' target='_blank'>Show me the map!</a></div>") : "")
     ."</div></div>"
     ."</select></form>"
     ."</br/></br/>";

if( !$currSheetName ) {
    goto done;
}


/* Geocode addresses that don't have lat/long.
 * It's good to look through all the records because the cache can fill in a lot of blanks.
 */
$raGeoCache = array();
$iGeocodeLimit = 20;
$bGeocodeDone = false;

$raRows = $oMap->GetMarkers( null, null );
foreach( $raRows as $rowkey => $raR ) {
    if( !@$raR['address'] )  continue;

    if( @$raR['latitude'] && @$raR['longitude'] ) {
        // This record has a geocode so add it to the cache
        $raGeo[$raR['address']] = array( 'lat'=>$raR['latitude'], 'long'=>$raR['longitude'] );
    } else {
        // This record doesn't have a geocode so check the cache or compute it
        $lat = $long = null;
        if( isset($raGeoCache[$raR['address']] ) ) {
            $lat  = $raGeoCache[$raR['address']]['lat'];
            $long = $raGeoCache[$raR['address']]['long'];
            $sErr .= "<p>Found in cache: {$raR['address']}</p>";
        } else if( $iGeocodeLimit ) {  // Only geocode a limited number of addresses, but go through the whole list to max the cache coying
            if( ($geocode = $oG->Geocode( $raR['address'] )) ) {
                $lat  = $geocode['lat'];
                $long = $geocode['lng'];
                $sErr .= "<p>Geocoding: {$raR['address']}</p>";
                $raGeoCache[$raR['address']] = array( 'lat'=>$lat, 'long'=>$long );
                --$iGeocodeLimit;
            } else {
                $sErr .= "<p>Could not geocode: {$raR['address']}</p>";
            }
        }
        if( $lat && $long ) {
            // Store the geocode in the db
            $raR['latitude'] = $lat;
            $raR['longitude'] = $long;
            $oMap->StoreMarker( $rowkey, $raR['sheet'], $raR );
            $bGeocodeDone = true;
        }
    }
}
if( $bGeocodeDone ) {
    // reload for the display below
    $raSheets = $oMap->GetMarkersSheets();
}


$s .=
"<style>
.row-striped:nth-of-type(odd){
  background-color: #efefef;
}

.row-striped:nth-of-type(even){
  background-color: #ffffff;
}</style>";


/* For the current sheet, show the records for each canonical category then show the records that don't match any known category
 */
foreach( $oMap->raCategories as $cat => $raCat ) {
    $s .= showcat( $oFormB->Value('chooseSheet'), $cat, $raCat['label'] );
}
$s .= showCat( $oFormB->Value('chooseSheet'), "", "Other" );



function showCat( $sheet, $cat, $title ) // chooseSheet, cat, raCat['label']
{
    global $raSheets, $oMap;

    $s = "<div style='font-size:14px;font-weight:bold'>$title ($cat)</div>"
         ."<div class='container-fluid' style='margin:5px 0 10px 30px'>"
         ."<div class='row row-striped'>"
             ."<div class='col-sm-3'><strong>Internal note</strong></div>"
             ."<div class='col-sm-3'><strong>Display name</strong></div>"
             ."<div class='col-sm-3'><strong>Address for geocoding</strong></div>"
             ."<div class='col-sm-3'><strong>Lat/Long</strong></div>"
         ."</div>";

    foreach( $raSheets[$sheet] as $ra ) {
        if( $ra['cat'] ) {
            if( $cat ) {
                // cat is specified, skip this record if it isn't cat
                if( $ra['cat'] != $cat ) continue;
            } else {
                // cat is "" which means Other, skip this record if it is canonical
                if( isset($oMap->raCategories[$ra['cat']]) ) continue;
            }
        }

        $s .= "<div class='row row-striped'>"
             ."<div class='col-sm-3'>{$ra['note']}</div>"
             ."<div class='col-sm-3'>{$ra['name']}</div>"
             ."<div class='col-sm-3'>{$ra['address']}</div>"
             ."<div class='col-sm-3'>{$ra['latitude']}/{$ra['longitude']}</div>"
             ."</div>";
    }
    $s .= "</div>"; // container

    return( $s );
}



goto skipthis;

$s .= "<form method='post' action='{$_SERVER['PHP_SELF']}'>";

$oForm->iR = 1;
foreach( $oMap->raCategories as $cat => $raCat ) {
    $s .= "<h3>{$raCat['label']} ($cat)</h3>";

    $raRows = $oMap->GetMarkers( null, $cat );
    foreach( $raRows as $rowkey => $raR ) {
        $s .= writeMarkerControls( $raR['sheet'], $cat, $rowkey, $raR, $oForm );
//        $s .= $oForm->HiddenKey().$oForm->Hidden( 'cat', $cat )
//             ."<div>".$oForm->Text( "name" )."&nbsp;&nbsp;".$oForm->Text( "address" )."</div>";
        ++$oForm->iR;
    }
//    $s .= writeMarkerControls( 2016, $cat, 0, array(), $oForm );

//    $s .= $oForm->HiddenKey().$oForm->Hidden( 'cat', $cat )
//         ."<div>".$oForm->Text( "name" )."&nbsp;&nbsp;".$oForm->Text( "address" )."</div>";
    ++$oForm->iR;
}


/* Now go through the markers and show us any that aren't in raCategories
 */
$s .= "<h3>Other</h3>";

$raRows = $oMap->GetMarkers();
foreach( $raRows as $rowkey => $raR ) {
    $cat = $raR['cat'];
    if( isset($oMap->raCategories[$cat]) ) continue;
    $s .= writeMarkerControls( $raR['sheet'], $cat, $rowkey, $raR, $oForm, true );
    ++$oForm->iR;
}

$s .= "<input type='submit'/></form>";

skipthis:


$s .= "<hr style='width:100%'/>";

//     .$oMap->DrawMap(array('maptype'=>'roadmap'))
//     ."<br/>"
//     .$oMap->DrawMap(array('maptype'=>'satellite'));


done:

if( $sErr ) $s = "<div style='border:1px solid black;background-color:#eee;padding:10px;float:right;width:30%;'>$sErr</div>"
                .$s;

echo Console01Static::HTMLPage( $s, "", "EN", array( 'bBodyMargin'=>true ) );


function writeMarkerControls( $sheet, $cat, $rowkey, $raRow, $oForm, $bShowCat = false )
{
    $s = "";

    $oForm->SetValue( 'sheet', $sheet );
    $oForm->SetValue( 'cat', $cat );
    $oForm->SetValue( 'name', @$raRow['name'] );
    $oForm->SetValue( 'address', @$raRow['address'] );

    $s .= $oForm->HiddenKeyParm( $rowkey )
         ."<div>"
         .$oForm->Text( "year", "", array( 'size'=>5 ) )."&nbsp;&nbsp;"
         .($bShowCat ? ($oForm->Text( 'cat', "", array( 'size'=>15 ) )."&nbsp;&nbsp;" ) : $oForm->Hidden( 'cat' ))
         .$oForm->Text( "name", "", array( 'size'=>40 ) )."&nbsp;&nbsp;".$oForm->Text( "address", "", array( 'size'=>60 ) )
         ."&nbsp;&nbsp;".@$raRow['latitude']."&nbsp;/&nbsp;".@$raRow['longitude']."</div>";

    return( $s );
}

?>
