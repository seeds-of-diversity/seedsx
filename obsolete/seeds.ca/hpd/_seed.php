<?
function format_seed( $record, $parms ) {
//  echo '<TR><TD><A href="'.HPD_PAGE_CVDETAIL.'?species=' . urlencode( $record["species"] ) . '" target=cultivar>' . $record["species"] . "</A> : ";
    echo "<TR><TD>${record["species"]} : ";
    if( empty( $record["pname"] ) ) {
        echo '<A href="'.HPD_PAGE_CVDETAIL.'?species=' . urlencode($record["species"]) . '&cultivar=NULL" target=cultivar>(Unnamed)</A>';
    } else if( strcasecmp( $record["oname"], $record["pname"] ) == 0 ) {
        echo '<A href="'.HPD_PAGE_CVDETAIL.'?species=' . urlencode($record["species"]) . "&cultivar=" . urlencode( $record["pname"] ) . '" target=cultivar>' . $record["pname"] . "</A>";
    } else {
        echo $record["oname"] . " (";
        echo '<A href="'.HPD_PAGE_CVDETAIL.'?species=' . urlencode($record["species"]) . "&cultivar=" . urlencode( $record["pname"] ) . '" target=cultivar>' . $record["pname"] . "</A>";
        echo ")";
    }
    echo "</TD></TR>";
}

function start_seed_list() {
    echo '<TABLE>';
}

function stop_seed_list() {
    echo '</TABLE>';
}
?>
