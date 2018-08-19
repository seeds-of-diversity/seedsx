<?

class questions {
    var $i;

    function start() { $this->i = 1; echo "<TABLE style='margin-left:20' cellspacing=10>"; }
    function end()   { echo "</TABLE>"; }
    function row( $question, $choices )
    {
        echo "<TR><TD valign=top>".($this->i++).".</TD>";
        echo "<TD width='400' valign=top>$question</TD>";
        echo "<TD valign=top>$choices</TD></TR>";
    }
    function rowsub( $question, $choices )
    {
        echo "<TR><TD>&nbsp;</TD>";
        echo "<TD width='400' valign=top><DIV style='margin-left:20'>$question</DIV></TD>";
        echo "<TD valign=top>$choices</TD></TR>";
    }
}


function button_radio( $name, $value, $label )
/*********************************************
 */
{
    return( "<INPUT type=radio name='$name' value='$value'>&nbsp;$label&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" );
}

function button_radio_yn( $name )
/********************************
 */
{
    return( button_radio( $name, "1", "Yes" ) . button_radio( $name, "0", "No" ) );
}

function button_radio_set( $name, $ra, $format = "" )
/****************************************************
 */
{
    $s = "";
    foreach( $ra as $k => $v ) {
        switch( $format ) {
            case "TD": $s .= "<TD valign=top>"; break;
        }
        $s .= button_radio( $name, $k, $v );
        switch( $format ) {
            case "TD": $s .= "</TD>"; break;
            case "BR": $s .= "<BR>";  break;
        }
    }
    return( $s );
}


function garlic_form( $bExpert = 0 )
/***********************************
    <FORM> and hidden fields written by caller
 */
{
    $q = new questions;

    echo "<H3>When you plant the cloves</H3>";
    $q->start();
    $q->row( "What was the date that you planted this sample?",
             "<INPUT type=text name='d_SoD_PLANTED_DATE'>" );
    $q->row( "How many cloves did you plant?",
             "<INPUT type=text name='d_SoD_PLANTED_NUM'>" );
    $q->row( "How far apart did you plant the cloves (cm)?",
             "<INPUT type=text name='d_SoD_PLANTED_DISTANCE'>" );
    $q->row( "Did you mulch over winter? ",
             button_radio_yn( "d_SoD_PLANTED_MULCH_YN" ) );
    $q->rowsub( "How thick was the mulch (approx cm)?",
                "<INPUT type=text name='d_SoD_PLANTED_MULCH_THICK'>" );
    $q->rowsub( "What material?",
                "<INPUT type=text name='d_SoD_PLANTED_MULCH_MATERIAL'>" );
    $q->end();


    echo "<H3>Cultivation</H3>";
    $q->start();
    $q->row( "What is the soil texture?",
             button_radio_set( "d_SoD_SOILTYPE[]", array( "sand" => "Sandy",
                                                          "loamsand" => "Loamy sand",
                                                          "loamclay" => "Loamy clay",
                                                          "clay" => "Clay" ) ) .
             "<BR>".
             button_radio( "d_SoD_SOILTYPE[]", "", "Other" ) .
             "<INPUT type=text name='d_SoD_SOILTYPE[]'>".
             "<BR>".
             button_radio( "d_SoD_SOILTYPE[]", "", "Don't know" ) );

    $q->row( "Was the garlic watered regularly during spring and summer (rain or irrigation)?",
             button_radio_yn( "d_SoD_WATERED_YN" ) );

    $q->row( "Did you fertilize the garlic?",
             button_radio_yn( "d_SoD_FERTILIZED_YN" ) );
    $q->rowsub( "With what, about how much?", "<INPUT type=text name='d_SoD_FERTILIZED_NOTE'>" );

    $q->row( "Did you control weeds in the garlic bed?",
             button_radio_set( "d_SoD_WEEDCONTROL", array( "none" => "No, it was weedy",
                                                           "mulch" => "Mulched",
                                                           "hand" => "Hand weeded" ) ) );
    $q->end();


    echo "<H3>Mid-Season</H3>";
    $q->start();

    // RG: 7, IPGRI 7.1.1
//  if( $bExpert )
        $q->row( "What colour were the leaves before they died back?",
                 "<TABLE><TR>".
                 button_radio_set( "d_SoD_FOLIAGE_COLOUR",
                                   array( "1" => "Light green",
                                          "2" => "Yellow green",
                                          "3" => "Green",
                                          "4" => "Grey green" ),
                                   "TD" ) .
                 "</TR><TR>".
                 button_radio_set( "d_SoD_FOLIAGE_COLOUR",
                                   array( "5" => "Dark green",
                                          "6" => "Bluish green",
                                          "7" => "Purplish green",
                                          "0" => "Don't know" ),
                                   "TD" ) .
                 "</TR></TABLE>" );

    // RG: 8, IPGRI 7.1.2
    $q->row( "Average leaf length (cm)?", "<INPUT type=text name='d_SoD_LEAF_LENGTH'></LI>" );

    // RG: 10, IPGRI 7.1.5
    $q->row( "At what angle did most of the leaves extend from the stem?",
             "<TABLE><TR>".
             button_radio_set( "d_SoD_FOLIAGE_ATTITUDE",
                               array( "7" => "Close to vertical",
                                      "5" => "Close to 45 degrees",
                                      "3" => "Close to horizontal",
                                      "0" => "Don't know" ),
                               "TD" ) .
             "</TR></TABLE>" );

    // RG: 9, IPGRI 7.1.3
    if( $bExpert )
        $q->row( "Average leaf width at the stem (cm)?", "<INPUT type=text name='d_SoD_LEAF_WIDTH'>" );
    $q->end();

    echo "<H3>Scapes</H3>";
    $q->start();
    $q->row( "Did this garlic produce scapes?",
             button_radio_yn( "d_SoD_SCAPE_EXIST" ) .
             button_radio(    "d_SoD_SCAPE_EXIST", "-1", "Sometimes" ) );

    $q->row( "Did you remove the scapes when they appeared?",
             button_radio_yn( "d_SoD_SCAPE_REMOVED_YN" ) .
             button_radio(    "d_SoD_SCAPE_REMOVED_YN", "-1", "Some" ) );

    $q->row( "What shape was the scape stem?",
             button_radio_set( "d_SoD_SCAPE_STEM_SHAPE",
                               array( "coiled" => "Coiled",
                                      "curved" => "Curved",
                                      "straight" => "Straight",
                                      "mixed" => "Mixed" ) ) );

    $q->row( "How many bulbils were in each scape, on average?",
             button_radio_set( "d_SoD_SCAPE_BULBILS_NUM", array( "5" => "1-9", "15" => "10-19", "25" => "20+" ) ) );
    $q->end();


    echo "<H3>Harvest</H3>";
    $q->start();
    $q->row( "What was the date that you harvested this sample?",
             "<INPUT type=text name='d_SoD_HARVEST_DATE'>" );

    // RG: 17, GRIN
    $q->row( "How tall were the plants on average, from the ground to the top leaf node (cm)?",
             "<INPUT type=text name='d_GRIN_PLANTHEIGHT'>" );

    $q->row( "How many bulbs did you harvest?",
             "<INPUT type=text name='d_SoD_HARVEST_BULB_NUM'>" );

    // RG: 18, GRIN
    $q->row( "What is the average bulb diameter, at the widest point (cm)?",
             "<INPUT type=text name='d_GRIN_BULBDIAM'>" );

    // RG: 21, IPGRI 7.1.12   N.B.  GRIN defines a larger set, difficult to tell mapping
    $q->row( "What is the shape of the bulb, viewed from the side?",
             button_radio_set( "d_SoD_BULBSHAPE'", array( "1" => "Round", "2" => "Teardrop", "3" => "Flat wide oval" ) ) );

    // RG: 22, IPGRI 7.1.16  N.B. I have added some colours from Julian's scheme
    $q->row( "What colour are the fresh bulb skins (remove the first few bulb wrappers to find a clean skin)?",
             "<TABLE><TR>".
             button_radio_set( "d_SoD_BULB_COLOUR",
                               array( "1" => "White",
                                      "2" => "Cream",
                                      "3" => "Beige" ),
                               "TD" ) .
             "</TR><TR>".
             button_radio_set( "d_SoD_BULB_COLOUR",
                               array( "101" => "Brown",
                                      "102" => "Striped Brown",
                                      "103" => "Red" ),
                               "TD" ) .
             "</TR><TR>".
             button_radio_set( "d_SoD_BULB_COLOUR",
                               array(
//                                    "4" => "White stripes",         WHAT IS THIS
                                      "5" => "Light Violet",
                                      "6" => "Violet",
//                                    "7" => "Dark Violet",
                                      "104" => "Striped Violet" ),
                               "TD" ) .
             "</TR></TABLE>" );

    // RG: 23, IPGRI 7.1.16.2
    $q->row( "What colour are the clove skins?",
             "<TABLE><TR>".
             button_radio_set( "d_SoD_CLOVE_COLOUR",
                               array( "1" => "White",
                                      "2" => "Yellow and Light Brown",
                                      "3" => "Brown" ),
                               "TD" ) .
             "</TR><TR>".
             button_radio_set( "d_SoD_CLOVE_COLOUR",
                               array( "4" => "Red",
                                      "5" => "Violet" ),
                               "TD" ) .
             "</TR></TABLE>" );

    // RG: 24, IPGRI 7.1.19
    $q->row( "How many cloves are in each bulb, on average?",
             "<TABLE><TR>".
             button_radio_set( "d_SoD_CLOVE_NUM",
                               array( "1" => "1",
                                      "2" => "2-4",
                                      "3" => "5-10",
                                      "4" => "11-15" ),
                               "TD" ) .
             "</TR><TR>".
             button_radio_set( "d_SoD_CLOVE_NUM",
                               array( "5" => "16-20",
                                      "6" => "21-50",
                                      "7" => "50+" ),
                               "TD" ) .
             "</TR></TABLE>" );


    $q->row( "How are the cloves arranged in the bulb?",
             button_radio_set( "d_SoD_CLOVE_ARRANGE[]",
                               array( "1" => "One circle of wedge-shaped cloves",
                                      "2" => "Two or more rings of cloves, uniform sizes, evenly spaced",
                                      "3" => "Cloves irregularly spaced, different sizes" ),
                               "BR" ) .
             button_radio( "d_SoD_CLOVE_ARRANGE[]", "", "Other" ) .
             "<INPUT type=text name='d_SoD_CLOVE_ARRANGE[]'>" );

    $q->row( "Are the bulb wrappers easy to peel when dry?",
             button_radio_yn( "d_SoD_BULB_PEELING" ) .
             button_radio(    "d_SoD_BULB_PEELING", "-1", "Don't know" ) );

    $q->row( "Are the cloves easy to peel when dry?",
             button_radio_yn( "d_SoD_CLOVE_PEELING" ) .
             button_radio(    "d_SoD_CLOVE_PEELING", "-1", "Don't know" ) );
    $q->end();

    echo "<H3>Notes</H3>";
    echo "<TEXTAREA name='d_SoD_NOTES'></TEXTAREA>";
}

print_r( $_REQUEST );

echo "<FORM action='${_SERVER['PHP_SELF']}'>";
garlic_form();
echo "<BR>";
echo "<INPUT type=submit>";
echo "</FORM>";


?>
