<?

// Add _created_by, _updated_by, _verified_by and _disabled_by fields.
// Integrate created, updated and disabled into BXRecord (could have flags in the record def to activate these).
// if bCreated, set created and created_by values on INSERT.
// if bUpdated, set updated and updated_by values on UPDATE.
// if bDisabled, set disabled and disabled_by values on disable().
// if bDisabled, append condition (AND _disabled=0) on SELECT.


// urlencode the login_get_auth_urlparms because the username contains spaces - won't work on Netscape 4.7


define( "SITEROOT", "../../" );
define( "RLROOT",   "../" );

define( "RL_pageType", "update" );


/* Show an rl_cmp and allow it to be edited.
 *
 * $i = "new" OR rl_cmp_id
 */


$i = @$_REQUEST["i"];
if( $i == "new" ) {
} else {
    $i = intval($i);
    if( $i < 1 )  die( "Invalid Company id $i" );
}


include_once( SITEROOT."site.php" );
include_once( SITEINC."sitedb.php" );
include_once( RLROOT  ."_rl_inc.php" );
include_once( SITEINC ."sodlogin.php" );

$la = new SoDLoginAuth;
if( !$la->SoDLoginAuth_Authenticate( $_REQUEST, "W perm_rl" ) ) { exit; }


if( $i == "new" ) {
    // avoid undefined index warnings
    // use BXRecord_LoadDefaults
    $ra['name_en'] = $ra['name_fr'] = "";
    $ra['addr_en'] = $ra['addr_fr'] = "";
    $ra['city'] = $ra['prov'] = "";
    $ra['postcode'] = $ra['country'] = "";
    $ra['phone'] = $ra['fax'] = $ra['web'] = $ra['email'] = "";
    $ra['cat_cost'] = $ra['year_est'] = 0;
    $ra['desc_en'] = $ra['desc_fr'] = "";
    $ra['comments'] = "";
    $ra['xlat'] = 0;
    $ra['supporter'] = 0;
} else {
    $ra = rl_cmp_get( $i );
}


$title = ($i=="new" ? "Add" : "Edit") ." Company";
std_banner1( $title );

echo "<P align=center><FONT color=blue>Instructions at bottom of this page</P>";
?>


<?
function option_country( $c ) {
    echo "<OPTION value='Canada'".  ($c=='Canada'  ? " SELECTED" : "") .">Canada</OPTION>";
    echo "<OPTION value='US'".      ($c=='US'      ? " SELECTED" : "") .">US</OPTION>";
    echo "<OPTION value='England'". ($c=='England' ? " SELECTED" : "") .">England</OPTION>";
    echo "<OPTION value='France'".  ($c=='France'  ? " SELECTED" : "") .">France</OPTION>";
}

function myEnt($s) { return( htmlspecialchars($s,ENT_QUOTES) ); }

function draw_field( $name, $label, $ra, $bEN, $bFR, $size )
{
    echo "<TR><TD align='left'>$label:</TD><TD align='left'>";
    echo "<TABLE cellpadding=5>";
    if( $bEN )  echo "<TR><TD bgcolor=".CLR_BG_editEN.">(English) <INPUT TYPE=TEXT NAME={$name}_en VALUE='".myEnt($ra[$name.'_en'])."' size={$size}></TD></TR>\n";
    if( $bFR )  echo "<TR><TD bgcolor=".CLR_BG_editFR.">(Fran&ccedil;ais) <INPUT TYPE=TEXT NAME={$name}_fr VALUE='".myEnt($ra[$name.'_fr'])."' size={$size}></TD></TR>\n";
    echo "</TABLE>";
    if( !$bEN )  echo "<INPUT TYPE=HIDDEN NAME={$name}_en VALUE='".myEnt($ra[$name.'_en'])."'>";
    if( !$bFR )  echo "<INPUT TYPE=HIDDEN NAME={$name}_fr VALUE='".myEnt($ra[$name.'_fr'])."'>";
    echo "</TD></TR>";
}

?>


<FORM action=edit2.php method=post>
<?= $la->login_auth_get_hidden(); ?>
<INPUT TYPE=HIDDEN NAME=i    VALUE='<?= $i ?>'>

<TABLE cellpadding=5 width="50%" align="center">
<?
    draw_field( "name", "Name", $ra, 1, 1, 50 );
    draw_field( "addr", "Street Address", $ra, 1, 1, 50 );

?>
<TR><TD align="left">City:</TD>                <TD align="left"><INPUT TYPE=TEXT NAME=city     VALUE='<?= myEnt($ra['city']) ?>'      size=40></TD></TR>
<TR><TD align="left">Province/State:</TD>      <TD align="left"><INPUT TYPE=TEXT NAME=prov     VALUE='<?= myEnt($ra['prov']) ?>'      size=40></TD></TR>
<TR><TD align="left">Country:</TD>             <TD align="left"><SELECT          NAME=country><? option_country( $ra['country'] ); ?></SELECT></TD></TR>
<TR><TD align="left">Postal/Zip Code:</TD>     <TD align="left"><INPUT TYPE=TEXT NAME=postcode VALUE='<?= myEnt($ra['postcode']) ?>'  size=20></TD></TR>
<TR><TD align="left">Phone:</TD>               <TD align="left"><INPUT TYPE=TEXT NAME=phone    VALUE='<?= myEnt($ra['phone']) ?>'     size=20></TD></TR>
<TR><TD align="left">Fax:</TD>                 <TD align="left"><INPUT TYPE=TEXT NAME=fax      VALUE='<?= myEnt($ra['fax']) ?>'       size=20></TD></TR>
<TR><TD align="left">Web:</TD>                 <TD align="left"><INPUT TYPE=TEXT NAME=web      VALUE='<?= myEnt($ra['web']) ?>'       size=60></TD></TR>
<TR><TD align="left">Email:</TD>               <TD align="left"><INPUT TYPE=TEXT NAME=email    VALUE='<?= myEnt($ra['email']) ?>'     size=60></TD></TR>
<TR><TD align="left">Catalog:</TD>             <TD align="left"><INPUT TYPE=RADIO NAME=cat_cost1 VALUE='0'  <?= $ra['cat_cost']==0  ? "CHECKED" : "" ?> size=20>Free&nbsp;
                                                                <INPUT TYPE=RADIO NAME=cat_cost1 VALUE='1'  <?= $ra['cat_cost']>0  ? "CHECKED" : "" ?> size=20>$
                                                                <INPUT TYPE=TEXT  NAME=cat_cost  VALUE='<?= $ra['cat_cost'] > 0 ? $ra['cat_cost'] : "" ?>' size=3>
                                                                <INPUT TYPE=RADIO NAME=cat_cost1 VALUE='-1' <?= $ra['cat_cost']==-1 ? "CHECKED" : "" ?> size=20>Specified In Description&nbsp;
                                                                </TD></TR>
<TR><TD align="left">Year Established:</TD>    <TD align="left"><INPUT TYPE=TEXT NAME=year_est VALUE='<?= $ra['year_est']; ?>'  size=20></TD></TR>
<TR><TD align="left">Translation Needed:</TD>  <TD align="left"><INPUT TYPE=CHECKBOX NAME=xlat <?= $ra['xlat'] ? "CHECKED" : "" ?>    VALUE="1"      size=20></TD></TR>
<TR><TD align="left">Supporter:</TD>  <TD align="left"><INPUT TYPE=CHECKBOX NAME='supporter' <?= $ra['supporter'] ? "CHECKED" : "" ?>    VALUE="1"      size=20></TD></TR>

<?
    echo "<TR><TD align='left' valign=top>Description:</TD><TD align='left'>";
    echo     "<TABLE cellpadding=5>";
    echo     "<TR><TD bgcolor='".CLR_BG_editEN."'>(English) <TEXTAREA NAME=desc_en COLS=52 ROWS=10 WRAP=SOFT>".myEnt($ra['desc_en'])."</TEXTAREA></TD></TR>";
    echo     "<TR><TD bgcolor='".CLR_BG_editFR."'>(Fran&ccedil;ais) <TEXTAREA NAME=desc_fr COLS=52 ROWS=10 WRAP=SOFT>".myEnt($ra['desc_fr'])."</TEXTAREA></TD></TR>";
    echo     "</TABLE>";
    echo "</TD></TR>\n";

    echo "<TR><TD align='left'>Comments: (not shown publicly)</TD><TD><TEXTAREA NAME=comments COLS=52 ROWS=5 WRAP=SOFT>".myEnt($ra['comments'])."</TEXTAREA></TD></TR>";

    echo "<TR><TD align=center colspan=2><INPUT TYPE=SUBMIT VALUE='".(($i=="new") ? "Add":"Update")."'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
    echo "<A HREF='start.php?".$la->login_auth_get_urlparms()."'>Cancel</A></TD></TR>\n";
?>
</TABLE>

</FORM>


<HR>
<H3>Instructions</H3>
<P>"Name" and "Country" are the only required fields.  You can leave all the others blank as needed.</P>


<DL>
<DT>Name</DT>
<DD>The name of the company can be entered in English or French.  Currently, only the English name is used, since the
only example of a company with two names so far is SoDC/Sdp.  For now, you can just fill in the English</DD>

<DT>Street Address</DT>
<DD>This is the street, number, P.O. Box, not including the city, postcode, etc.  It can be entered in French too,
if it makes sense to have a different format for the street address.  If the french is blank, the english is shown on
semences.ca.  <B>Policy is to leave french street address blank for non-Canadian companies</B>.  i.e. all American
companies have english-only street names.</DD>

<DT>City<DT>
<DD>The city, town, village</DD>

<DT>Province/State</DT>
<DD>You can enter anything you like here.  e.g. Ontario, ON, ONT can be entered and will be shown that way.  It would be
best to have a consistent format for provinces</DD>

<DT>Country</DT>
<DD>Currently limited to Canada, US, England, France.  If you want to add a company that isn't on this list, Bob can add
that country.  This is limited because it is the key by which the companies are organized in the list.</DD>

<DT>Postal Code</DT>
<DD>Postal Code, Zip Code, etc</DD>

<DT>Phone, Fax</DT>
<DD>There is no imposed format for these, since we have to accommodate international phone numbers for European companies.
Please use a consistent area-code format for North American companies.  These numbers are not interpreted in any special
way, so they can have non-numerical content as long as it is bilingual.  e.g. fax could be "same" if same as phone, but
that will be shown on both english and french versions of the list - probably better to just enter the number.</DD>

<DT>Web</DT>
<DD>Enter the company's web site address without the "http://".  That part is added automatically.  This must be a valid
web site, because it is presented on the list as a hyperlink.  e.g. you can't enter something like "see description" here.  If there
is a special case, explain it in the description and put the main web site here, or leave this blank if it makes sense.</DD>

<DT>Email</DT>
<DD>Enter the company's main email address.  As with Web, this is presented as a hyperlink so it must be a valid
email address.  e.g. you can't enter "coming soon" here.</DD>

<DT>Catalog</DT>
<DD>Check one of the three buttons.  If you check free, the list will show "Catalog Free"/"Catalogue gratuit".  If the
catalog has a cost, check the middle button and enter the amount - the list will show "Catalog(ue) $X".  If there are
special conditions, such as different prices in Cdn/US, special rates, or anything that doesn't fit the first two cases,
check the third button.
This disables any automatic mention of the catalog, and you can explain what you want to in the description.</DD>

<DT>Year Established</DT>
<DD>The year that the company was established.</DD>

<DT>Translation Needed</DT>
<DD>This is an internal flag, not shown on the public site.  Check it if something needs to be translated.</DD>

<DT>Description</DT>
<DD>Enter a description of the company, its products and services.  The French and English should match as closely as
possible.</DD>

<DT>Comments</DT>
<DD>This is an internal text field, not shown on the public site.  Use this to make notes/reminders to yourself and other
administrators.</DD>
</DL>

</BODY>
</HTML>
