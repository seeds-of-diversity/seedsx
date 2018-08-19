<?

include_once( "_pw.php" );

pw_style();

echo "<H2>Pollinator Watch Recording Form</H2><H3>Sub-form A</H3>";

echo "<FORM action='pw_submit.php' method='post'>";

echo "<DIV class='d_inst'>";
echo "<P>If you cannot answer any of these questions, just leave them blank or check the \"don't know\" choice.</P>";
echo "</DIV>";

echo dw_sect( "Observer" );
echo "<PRE>";
?>
Observer name:  ______________________________________________. Date: _____________.
Home address:
Street number and name, _______________________________________,
City and province, ____________________________, Postal code, _____________.
<?
echo "</PRE>";

echo "<STYLE> .pw_box1 { background-color: #ddffdd; border: ridge green; padding: 0.5em; } </STYLE>";

echo dw_sect( "Location" );
echo "<DIV class='pw_box1'>";
echo "<DIV>Choose location <SELECT><OPTION value=0>Enter new location below</OPTION><OPTION value='-1'>Home</OPTION></SELECT></DIV>";
echo "</DIV>";

echo "<DIV>- or -</DIV>";

echo "<DIV class='pw_box1'>";
echo "<B>Enter new location</B>";
dw_q_s( "Name this location", "pw_SoD_s_loc_name" );
echo "<DIV>Latitude  <INPUT type='text' name='pw_SoD_i__loc_lat_degree' size=10> degrees, <INPUT type=text name='pw_SoD_i__loc_lat_minute' size=10> (to nearest minute)</DIV>";
echo "<DIV>Longitude <INPUT type='text' name='pw_SoD_i__loc_lon_degree' size=10> degrees, <INPUT type=text name='pw_SoD_i__loc_lon_minute' size=10></DIV>";
echo "<DIV>";
dw_q_s( "Address (if known)", "pw_SoD_s__loc_addr" );
dw_q_s( "City", "pw_SoD_s__loc_city" );
dw_q_s( "Province", "pw_SoD_s__loc_prov" );
echo "</DIV>";
echo "<DIV>- or -</DIV>";
echo "<DIV>";
dw_q_s( "Describe location", "pw_SoD_s__loc_describe" );
echo "<DIV style='font-size:small;'>e.g. direction and approx distance from nearest intersection, location in park, relation to clear landmarks, etc.</DIV>";
echo "</DIV>";
echo "</DIV>";


// This needs to be merged or categorized somehow.  Some of these overlap others.

dw_q_m( "What kind of landscape is this?", "pw_SoD_m__landscape1",
        array(
1=>"urban",
2=>"suburban",
3=>"rural",
4=>"backyard",
5=>"farmland",
6=>"wilderness",
0=>"other" ) );

dw_q_m( "", "pw_SoD_m__landscape2",
        array(
1=>"industrial",
2=>"campus",
3=>"park",
3=>"vacant lot",
3=>"housing",
3=>"golf course",
0=>"other" ) );

// I've renumbered this row because 3 was missing

dw_q_m( "", "pw_SoD_m__landscape3",
        array(
1=>"road or railway embankment",
2=>"forest",
3=>"riverbank",
4=>"farm field",
5=>"pasture or meadow",
6=>"hedgerow",
7=>"orchard",
8=>"landscaped garden",
0=>"other" ) );

dw_q_m( "", "pw_SoD_m__landscape4",
        array(
1=>"at edge of the landscape area",
2=>"within the landscape area",
0=>"other" ) );


dw_sect( "Today's observation" );
dw_q_d( "Date", "pw_SoD_d__date" );
dw_q_s( "Time", "pw_SoD_s__time" );
dw_q_i( "Approximate time spent observing (minutes)", "pw_SoD_s__timeduration" );

dw_sect( "Weather" );
dw_q_m( "Sky", "pw_SoD_m__weather_sky",
        array(
1=>"sunny",
2=>"cloudy",
3=>"overcast",
4=>"rain",
0=>"other / don't know" ) );

dw_q_m( "Sun", "pw_SoD_m__weather_sun",
        array(
1=>"direct sun",
2=>"shaded",
0=>"other / don't know" ) );

// I reorganized the wind section

dw_q_m( "Wind", "pw_SoD_m__weather_wind",
        array(
1=>"windy, steady",
2=>"windy in gusts",
3=>"light breeze, steady",
4=>"light breeze in gusts",
5=>"calm",
0=>"other / don't know" ) );

dw_q_m( "Temperature", "pw_SoD_m__weather_temp",
        array(
1=>"cold",
2=>"cool",
3=>"normal",
4=>"warm",
5=>"hot",
0=>"other / don't know" ) );

dw_sect( "Flowers visited by insects" );

function pw_plantobs( $n ) {
    echo "<TR><TD valign='top'>";
    echo     "<INPUT type='text' name='pw_SoD_s__plant_name$n' size=30></TD>";
    echo "<TD valign='top'>";
    echo     "<INPUT type=radio name='pw_SoD_m__plant_type$n'   value=1> Wild<BR>";
    echo     "<INPUT type=radio name='pw_SoD_m__plant_type$n'   value=2> Domesticated<BR>";
    echo     "<INPUT type=radio name='pw_SoD_m__plant_type$n'   value=3> Weed<BR>";
    echo     "<INPUT type=radio name='pw_SoD_m__plant_type$n'   value=0> don't&nbsp;know</TD>";
    echo "<TD valign='top'>";
    echo     "<INPUT type=radio name='pw_SoD_m__plant_use$n'    value=1> Ornamental<BR>";
    echo     "<INPUT type=radio name='pw_SoD_m__plant_use$n'    value=2> Vegetable<BR>";
    echo     "<INPUT type=radio name='pw_SoD_m__plant_use$n'    value=3> Field Crop<BR>";
    echo     "<INPUT type=radio name='pw_SoD_m__plant_use$n'    value=4> Fruit tree/bush<BR>";
    echo     "<INPUT type=radio name='pw_SoD_m__plant_use$n'    value=0> don't&nbsp;know</TD>";
    echo "<TD valign='top'>";
    echo     "<INPUT type=radio name='pw_SoD_m__plant_origin$n' value=1> Native<BR>";
    echo     "<INPUT type=radio name='pw_SoD_m__plant_origin$n' value=2> Introduced<BR>";
    echo     "<INPUT type=radio name='pw_SoD_m__plant_origin$n' value=0> don't&nbsp;know</TD>";
    echo "</TR>";
}
function pw_insectobs( $n ) {
    echo "<TR><TD valign='top'><INPUT type='text' name='pw_SoD_s__insect_name$n' size=30></TD>";
    echo "<TD valign='top'>";
    echo     "<TABLE border=0><TR><TD valign=top>";
    echo     "<INPUT type=radio name='pw_SoD_m__insect_type$n'   value=1> Bee<BR>";
    echo     "<INPUT type=radio name='pw_SoD_m__insect_type$n'   value=2> Wasp<BR>";
    echo     "<INPUT type=radio name='pw_SoD_m__insect_type$n'   value=3> Fly<BR>";
    echo     "</TD><TD valign=top>";
    echo     "<INPUT type=radio name='pw_SoD_m__insect_type$n'   value=4> Butterfly<BR>";
    echo     "<INPUT type=radio name='pw_SoD_m__insect_type$n'   value=5> Moth<BR>";
    echo     "<INPUT type=radio name='pw_SoD_m__insect_type$n'   value=9> Other<BR>";
    echo     "<INPUT type=radio name='pw_SoD_m__insect_type$n'   value=0> don't&nbsp;know";
    echo     "</TD></TR></TABLE></TD>";
    echo "<TD valign='top'>";
    echo     "<INPUT type=radio name='pw_SoD_m__insect_n$n'      value=1> Enter # (1-10) <INPUT type=text size=10 name='pw_SoD_i__insect_nn'><BR>";
    echo     "<INPUT type=radio name='pw_SoD_m__insect_n$n'      value=2> More than 10<BR>";
    echo     "<INPUT type=radio name='pw_SoD_m__insect_n$n'      value=3> Too many to count<BR>";
    echo     "<INPUT type=radio name='pw_SoD_m__insect_n$n'      value=0> don't&nbsp;know</TD>";
    echo "<TD valign='top'>";
    echo     "<TEXTAREA name='pw_SoD_s__insect_flvisited' rows=4></TEXTAREA>";
//  echo "<TD valign='top'>";
//  echo     "<INPUT type=radio name='pw_SoD_m__insect_tn$n'     value=1> Enter # (1-10) <INPUT type=text size=10 name='pw_SoD_i__insect_tnn'><BR>";
//  echo     "<INPUT type=radio name='pw_SoD_m__insect_tn$n'     value=2> More than 10<BR>";
//  echo     "<INPUT type=radio name='pw_SoD_m__insect_tn$n'     value=3> Too many to count<BR>";
//  echo     "<INPUT type=radio name='pw_SoD_m__insect_tn$n'     value=0> don't know</TD>";
    echo "<TD valign='top'>";
    echo     "<INPUT type=text  name='pw_SoD_m__subform$n' size=10></TD>";
    echo "</TR>";
}

echo "<DIV class='d_inst'>Fill in a row for each kind of flower where insects were visiting.<BR>Name any flowers you can (use common or scientific names, as you think is most accurate).<BR>";
echo "Indicate whether the plant was wild, domesticated, or a weed in a cultivated setting.<BR>";
echo "If a domesticated plant, indicate whether it is ornamental, vegetable, field crop or fruit.<BR>";
echo "If known, indicate whether the plant is native or introduced.</DIV>";

echo "<TABLE border=1 cellpadding=10>";
echo "<TR><TH>Plant name</TH><TH>Domesticity</TH><TH>Domesticated type<BR>(if domesticated)</TH><TH>Origin</TH></TR>";
pw_plantobs( 1 );
pw_plantobs( 2 );
pw_plantobs( 3 );
pw_plantobs( 4 );
pw_plantobs( 5 );
echo "</TABLE>";


dw_sect( "Floral-visiting Insects" );

echo "<DIV class='d_inst'>Fill in a row for each kind of insect that you saw.<BR>Name any insects you can.<BR>";
echo "If you don't know their names, identify their types.<BR>";
echo "List the flowers that each kind of insect was visiting (using names from the form above).</DIV>";

echo "<TABLE border=1 cellpadding=10>";
echo "<TH>Insect name</TH><TH>Type</TH><TH>Number observed</TH><TH>Flowers visited</TH>" . /*<TH>Number of types</TH> */ "<TH>Subform B</TH>";
pw_insectobs( 1 );
pw_insectobs( 2 );
pw_insectobs( 3 );
pw_insectobs( 4 );
pw_insectobs( 5 );
echo "</TABLE>";


?>

