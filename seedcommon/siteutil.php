<?php

include_once( SEEDCORE."SEEDEmail.php" );

function MailFromOffice( $to, $subject, $bodyText, $bodyHTML = "", $raParms = array() )
/**************************************************************************************
    $raParms['from'] = array( email_addr, [screen_name] )   e.g. array( "webmaster@seeds.ca", "Webmaster at Seeds" )
    $raParms['cc'] = array( cc1, cc2, ...)
    $raParms['bcc'] = array( bcc1, bcc2, ...)
 */
{

    if( isset($raParms['from']) && is_string($raParms['from']) ) {
        $sFromEmail = $raParms['from'];
        $sFromName = "";
    } else {
        $sFromEmail = isset($raParms['from'][0]) ? $raParms['from'][0] : 'office@seeds.ca';
        $sFromName  = isset($raParms['from'][1]) ? $raParms['from'][1] : "";
    }

    return( SEEDEmailSend( [$sFromEmail,$sFromName], $to, $subject, $bodyText, $bodyHTML, $raParms ) );
}


function Site_Log( $filename, $s )
/*********************************
 */
{
    if( $fp = fopen( SITE_LOG_ROOT.$filename, "a" ) ) {
        fputs( $fp, $s."\n" );
        fclose( $fp );
    }
}


function SelectProvince( $fldName, $value, $raParms = array() )
/**************************************************************
 */
{
    $sAttrs = @$raParms['sAttrs'];
    $lang = (@$raParms['lang']=='FR' ? 'FR' : 'EN');

    return( "<SELECT name='$fldName' $sAttrs>"
           .(@$raParms['bAll'] ? ("<OPTION value=''". ($value=='' ? " SELECTED" : "") .">-- ".($lang=='EN' ? "All" : "Tout")."--</OPTION>") : "")
// TODO: show the full province names by language
           ."<OPTION value='AB'". ($value=='AB' ? " SELECTED" : "") .">AB</OPTION>"
           ."<OPTION value='BC'". ($value=='BC' ? " SELECTED" : "") .">BC</OPTION>"
           ."<OPTION value='MB'". ($value=='MB' ? " SELECTED" : "") .">MB</OPTION>"
           ."<OPTION value='NB'". ($value=='NB' ? " SELECTED" : "") .">NB</OPTION>"
           ."<OPTION value='NL'". ($value=='NL' ? " SELECTED" : "") .">NL</OPTION>"
           ."<OPTION value='NS'". ($value=='NS' ? " SELECTED" : "") .">NS</OPTION>"
           ."<OPTION value='ON'". ($value=='ON' ? " SELECTED" : "") .">ON</OPTION>"
           ."<OPTION value='PE'". ($value=='PE' ? " SELECTED" : "") .">PE</OPTION>"
           ."<OPTION value='QC'". ($value=='QC' ? " SELECTED" : "") .">QC</OPTION>"
           ."<OPTION value='SK'". ($value=='SK' ? " SELECTED" : "") .">SK</OPTION>"
           ."<OPTION value='YK'". ($value=='YK' ? " SELECTED" : "") .">YK</OPTION>"
           ."<OPTION value='NT'". ($value=='NT' ? " SELECTED" : "") .">NT</OPTION>"
           ."<OPTION value='NU'". ($value=='NU' ? " SELECTED" : "") .">NU</OPTION>"
           ."</SELECT>" );
}


$SiteUtilRaProvinces1 = array(
    "AB" => "AB",
    "BC" => "BC",
    "MB" => "MB",
    "NB" => "NB",
    "NL" => "NL",
    "NS" => "NS",
    "ON" => "ON",
    "PE" => "PE",
    "QC" => "QC",
    "SK" => "SK",
    "YK" => "YK",
    "NT" => "NT",
    "NU" => "NU",
);


function SelectProvinceOrState( $formName, $elemName, $lang = "EN", $selcode = "", $raParms = array() )
/******************************************************************************************************
 */
{
    $id = @$raParms['id'];
    if( !$id ) $id = $elemName;

    $raCdnEN = array( array( "AB1", "Alberta" ),
                      array( "BC1", "British Columbia" ),
                      array( "MB1", "Manitoba" ),
                      array( "NB1", "New Brunswick" ),
                      array( "NL1", "Newfoundland / Labrador" ),
                      array( "NT1", "Northwest Territories" ),
                      array( "NS1", "Nova Scotia" ),
                      array( "NU1", "Nunavut" ),
                      array( "ON1", "Ontario" ),
                      array( "PE1", "Prince Edward Island" ),
                      array( "QC1", "Quebec" ),
                      array( "SK1", "Saskatchewan" ),
                      array( "YT1", "Yukon Territory" ) );

    $raCdnFR = array( array( "AB1", "Alberta" ),
                      array( "BC1", "Colombie-Britannique" ),
                      array( "PE1", "&Icirc;le-du-Prince-&Eacute;douard" ),
                      array( "MB1", "Manitoba" ),
                      array( "NB1", "Nouveau-Brunswick" ),
                      array( "NS1", "Nouvelle-&Eacute;cosse" ),
                      array( "NU1", "Nunavut" ),
                      array( "ON1", "Ontario" ),
                      array( "QC1", "Qu&eacute;bec" ),
                      array( "SK1", "Saskatchewan" ),
                      array( "NL1", "Terre-Neuve-et-Labrador" ),
                      array( "NT1", "Territoires du Nord-Ouest" ),
                      array( "YT1", "Yukon" ) );

    $raUS    = array( array( "AL2", "Alabama" ),
                      array( "AK2", "Alaska" ),
                      array( "AZ2", "Arizona" ),
                      array( "AR2", "Arkansas" ),
                      array( "CA2", "California" ),
                      array( "CO2", "Colorado" ),
                      array( "CT2", "Connecticut" ),
                      array( "DE2", "Delaware" ),
                      array( "DC2", "District of Columbia" ),
                      array( "FL2", "Florida" ),
                      array( "GA2", "Georgia" ),
                      array( "HI2", "Hawaii" ),
                      array( "ID2", "Idaho" ),
                      array( "IL2", "Illinois" ),
                      array( "IN2", "Indiana" ),
                      array( "IA2", "Iowa" ),
                      array( "KS2", "Kansas" ),
                      array( "KY2", "Kentucky" ),
                      array( "LA2", "Louisiana" ),
                      array( "ME2", "Maine" ),
                      array( "MD2", "Maryland" ),
                      array( "MA2", "Massachusetts" ),
                      array( "MI2", "Michigan" ),
                      array( "MN2", "Minnesota" ),
                      array( "MS2", "Mississippi" ),
                      array( "MO2", "Missouri" ),
                      array( "MT2", "Montana" ),
                      array( "NE2", "Nebraska" ),
                      array( "NV2", "Nevada" ),
                      array( "NH2", "New Hampshire" ),
                      array( "NJ2", "New Jersey" ),
                      array( "NM2", "New Mexico" ),
                      array( "NY2", "New York" ),
                      array( "NC2", "North Carolina" ),
                      array( "ND2", "North Dakota" ),
                      array( "OH2", "Ohio" ),
                      array( "OK2", "Oklahoma" ),
                      array( "OR2", "Oregon" ),
                      array( "PA2", "Pennsylvania" ),
                      array( "RI2", "Rhode Island" ),
                      array( "SC2", "South Carolina" ),
                      array( "SD2", "South Dakota" ),
                      array( "TN2", "Tennessee" ),
                      array( "TX2", "Texas" ),
                      array( "UT2", "Utah" ),
                      array( "VT2", "Vermont" ),
                      array( "VA2", "Virginia" ),
                      array( "WA2", "Washington" ),
                      array( "WV2", "West Virginia" ),
                      array( "WI2", "Wisconsin" ),
                      array( "WY2", "Wyoming" ) );

    $s = "
<SCRIPT language='JavaScript'>
function setCountry() {
    var f = self.document.forms[".$formName."];
    var x = f.elements[".$elemName."].selectedIndex;
    var y = f.elements[".$elemName."].options[x].value;
    var z = y.substr( 2, 1 );

    self.document.getElementById('drawProv_country_txt').innerHTML = ((z == '2') ? 'USA' : 'Canada');
}
</SCRIPT>
";

    $s .= "<SELECT id='$id' name='$elemName'  width=165  onChange='return setCountry()'>"
         ."<OPTION VALUE=''>".($lang=="EN" ? "Please Select One":"Veuillez faire un choix")."</OPTION>"
         ."<OPTION VALUE=''>-- Canada --</OPTION>";

    foreach( ($lang == "FR" ? $raCdnFR : $raCdnEN) as $v ) {
        $s .= "<OPTION VALUE='{$v[0]}'". (($selcode == $v[0]) ? " SELECTED" : "") .">{$v[1]}</OPTION>\n";
    }
    $s .= "<OPTION VALUE=''>-- U S A --</OPTION>";

    foreach( $raUS as $v ) {
        $s .= "<OPTION VALUE='{$v[0]}'". (($selcode == $v[0]) ? " SELECTED" : "") .">{$v[1]}</OPTION>\n";
    }
    $s .= "</SELECT>"
         ."<BR><SPAN id='drawProv_country_txt'> </SPAN>";

    return( $s );
}

?>
