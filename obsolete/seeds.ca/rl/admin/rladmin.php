<?
define( "SITEROOT", "../../" );
include_once( SITEROOT."site.php" );
include_once( SITEINC."sitedb.php" );
include_once( STDINC."BXRecord2.php" );
include_once( STDINC."BXRecordFrame.php" );
include_once( "../_rl_inc.php" );


$recdef = array( 'tablename' => 'rl_companies',
                 'fields'    => array( array("name_en",    "s", ""),
                                       array("name_fr",    "s", ""),
                                       array("addr_en",    "s", ""),
                                       array("addr_fr",    "s", ""),
                                       array("city",       "s", ""),
                                       array("prov",       "s", ""),
                                       array("country",    "s", "Canada"),
                                       array("postcode",   "s", ""),
                                       array("phone",      "s", ""),
                                       array("fax",        "s", ""),
                                       array("web",        "s", ""),
                                       array("web_alt",    "s", ""),
                                       array("email",      "s", ""),
                                       array("email_alt",  "s", ""),
                                       array("desc_en",    "s", ""),
                                       array("desc_fr",    "s", ""),
                                       array("cat_cost",   "i", -1),
                                       array("year_est",   "i",  0),
                                       array("comments",   "s", ""),
                                       array("supporter",  "i",  0),
                                       array("xlat",       "i",  0) ) );


$framedef = array( "name"      => "Company",
                   "RecordDef" => $recdef,
                   "ListCols"  => array( array( "Name",    "name_en",  150 ),
                                         array( "Address", "addr_en",  100 ),
                                         array( "City",    "city",      50 ),
                                         array( "Prov",    "prov",      50 ),
                                         array( "Postcode","postcode",  50 ),
                                         array( "Phone",   "phone",     50 ),
                                         array( "Fax",     "fax",       50 ),
                                         array( "Web",     "web",       50 ),
                                         array( "Email",   "email",     50 )
                                       ),
                    "fnFormDraw" => "formDraw"
                    );


function formDraw( $bxRec )
{
    $ra = $bxRec->_values;

    echo "<TABLE cellpadding=5 width='50%' align='center'>";

    formDraw_field( "name", "Name", $ra, 1, 1, 50 );
    formDraw_field( "addr", "Street Address", $ra, 1, 1, 50 );

?>
<TR><TD align="left">City:</TD>                <TD align="left"><INPUT TYPE=TEXT NAME=city     VALUE="<?= $ra['city']; ?>"      size=40></TD></TR>
<TR><TD align="left">Province/State:</TD>      <TD align="left"><INPUT TYPE=TEXT NAME=prov     VALUE="<?= $ra['prov']; ?>"      size=40></TD></TR>
<TR><TD align="left">Country:</TD>             <TD align="left"><SELECT          NAME=country><? option_country( $ra['country'] ); ?></SELECT></TD></TR>
<TR><TD align="left">Postal/Zip Code:</TD>     <TD align="left"><INPUT TYPE=TEXT NAME=postcode VALUE="<?= $ra['postcode']; ?>"  size=20></TD></TR>
<TR><TD align="left">Phone:</TD>               <TD align="left"><INPUT TYPE=TEXT NAME=phone    VALUE="<?= $ra['phone']; ?>"     size=20></TD></TR>
<TR><TD align="left">Fax:</TD>                 <TD align="left"><INPUT TYPE=TEXT NAME=fax      VALUE="<?= $ra['fax']; ?>"       size=20></TD></TR>
<TR><TD align="left">Web:</TD>                 <TD align="left"><INPUT TYPE=TEXT NAME=web      VALUE="<?= $ra['web']; ?>"       size=60></TD></TR>
<TR><TD align="left">Email:</TD>               <TD align="left"><INPUT TYPE=TEXT NAME=email    VALUE="<?= $ra['email']; ?>"     size=60></TD></TR>
<TR><TD align="left">Catalog:</TD>             <TD align="left"><INPUT TYPE=RADIO NAME=cat_cost1 VALUE='0'  <?= $ra['cat_cost']==0  ? "CHECKED" : "" ?> size=20>Free&nbsp;
                                                                <INPUT TYPE=RADIO NAME=cat_cost1 VALUE='1'  <?= $ra['cat_cost']>0  ? "CHECKED" : "" ?> size=20>$
                                                                <INPUT TYPE=TEXT  NAME=cat_cost  VALUE='<?= $ra['cat_cost'] > 0 ? $ra['cat_cost'] : "" ?>' size=3>
                                                                <INPUT TYPE=RADIO NAME=cat_cost1 VALUE='-1' <?= $ra['cat_cost']==-1 ? "CHECKED" : "" ?> size=20>Specified In Description&nbsp;
                                                                </TD></TR>
<TR><TD align="left">Year Established:</TD>    <TD align="left"><INPUT TYPE=TEXT NAME=year_est VALUE="<?= $ra['year_est']; ?>"  size=20></TD></TR>
<TR><TD align="left">Translation Needed:</TD>  <TD align="left"><INPUT TYPE=CHECKBOX NAME=xlat <?= $ra['xlat'] ? "CHECKED" : "" ?>    VALUE="1"      size=20></TD></TR>
<TR><TD align="left">Supporter:</TD>  <TD align="left"><INPUT TYPE=CHECKBOX NAME='supporter' <?= $ra['supporter'] ? "CHECKED" : "" ?>    VALUE="1"      size=20></TD></TR>

<?
    echo "<TR><TD align='left' valign=top>Description:</TD><TD align='left'>";
    echo     "<TABLE cellpadding=5>";
    echo     "<TR><TD bgcolor='".CLR_BG_editEN."'>(English) <TEXTAREA NAME=desc_en COLS=52 ROWS=10 WRAP=SOFT>".$ra['desc_en']."</TEXTAREA></TD></TR>";
    echo     "<TR><TD bgcolor='".CLR_BG_editFR."'>(Fran&ccedil;ais) <TEXTAREA NAME=desc_fr COLS=52 ROWS=10 WRAP=SOFT>".$ra['desc_fr']."</TEXTAREA></TD></TR>";
    echo     "</TABLE>";
    echo "</TD></TR>\n";

    echo "<TR><TD align='left'>Comments: (not shown publicly)</TD><TD><TEXTAREA NAME=comments COLS=52 ROWS=5 WRAP=SOFT>".$ra['comments']."</TEXTAREA></TD></TR>";

    echo "<TR><TD align=center colspan=2><INPUT TYPE=SUBMIT VALUE='".(($i=="new") ? "Add":"Update")."'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
    echo "<A HREF='start.php?".$la->login_auth_get_urlparms()."'>Cancel</A></TD></TR>\n";
    echo "</TABLE>";
}


function option_country( $c ) {
    echo "<OPTION value='Canada'".  ($c=='Canada'  ? " SELECTED" : "") .">Canada</OPTION>";
    echo "<OPTION value='US'".      ($c=='US'      ? " SELECTED" : "") .">US</OPTION>";
    echo "<OPTION value='England'". ($c=='England' ? " SELECTED" : "") .">England</OPTION>";
    echo "<OPTION value='France'".  ($c=='France'  ? " SELECTED" : "") .">France</OPTION>";
}

function formDraw_field( $name, $label, $ra, $bEN, $bFR, $size )
{
    echo "<TR><TD align='left'>$label:</TD><TD align='left'>";
    echo "<TABLE cellpadding=5>";
    if( $bEN )  echo "<TR><TD bgcolor=".CLR_BG_editEN.">(English) <INPUT TYPE=TEXT NAME={$name}_en VALUE=\"".$ra[$name.'_en']."\" size={$size}></TD></TR>\n";
    if( $bFR )  echo "<TR><TD bgcolor=".CLR_BG_editFR.">(Fran&ccedil;ais) <INPUT TYPE=TEXT NAME={$name}_fr VALUE=\"".$ra[$name.'_fr']."\" size={$size}></TD></TR>\n";
    echo "</TABLE>";
    if( !$bEN )  echo "<INPUT TYPE=HIDDEN NAME={$name}_en VALUE=\"".$ra[$name.'_en']."\">";
    if( !$bFR )  echo "<INPUT TYPE=HIDDEN NAME={$name}_fr VALUE=\"".$ra[$name.'_fr']."\">";
    echo "</TD></TR>";
}



BXRFrame( $framedef, 1 );  // TODO: set uid to the login

?>
