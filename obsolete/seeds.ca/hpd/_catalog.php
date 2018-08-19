<?
function format_catalog( $record, $parms ) {
    echo '<TR>';
    echo '<TD valign="top">' . $record['refdate'] . "</TD>";
    echo '<TD><A href="catalog.php?catalog=' . urlencode( $record['refcode'] ) . '">' . $record['name'] . "</A><BR>";

    if( $record['publisher'] ) {
        echo $record['publisher'] . "<BR>";
    }
    echo $record['place'];
    echo '</TD></TR>';
    //echo '<tr><td>' . $record['shortname'] . '</td>';
    //echo '<td><a href="catalog.php?catalog=' . urlencode( $record['refcode'] );
    //echo '">' . $record['name'] . '</a></td>';
    //echo '<td>' . $record['vol'] . '</td>';
    //echo '<td>' . $record['refdate'] . '</td>';
    //echo '<td>' . $record['place'] . '</td>';
    //echo '<td>' . $record['publisher'] . '</td></tr>';
}

function start_catalog_list() {
    echo '<TABLE cellspacing="15">';
    //echo '<TABLE>';
    //echo '<TH>Shortname</TH>';
    //echo '<TH>Name</TH>';
    //echo '<TH>Vol</TH>';
    //echo '<TH>Refdate</TH>';
    //echo '<TH>Place</TH>';
    //echo '<TH>Publisher</TH>';
}

function stop_catalog_list() {
    echo '</TABLE>';
    //echo '</TABLE>';
}
?>
