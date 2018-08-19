<?php

/* Seeds of Diversity's Google API
 *
 * Copyright (c) 2010-2018
 */

class SEEDSGoogle {
    /* Manage at code.google.com/apis/console
     * The server key should be used for calls from our server (NOTE our servers' ip addresses should be added to google console to prevent hijack use of our quotas).
     * The browser key should be used for JS calls by clients, so they don't compromise our server-key quota.
     */
    public $keyServer = "AIzaSyBcWhDV6LnUNBsfkzr1T7bVjLKU8MrEqzw";
    public $keyBrowser = "AIzaSyCyOCuADfmIYNUnCf-oJUwHH0n1EilPcwQ";

    function __construct() {}





}


class SEEDSGoogleMaps extends SEEDSGoogle {

    function GetScriptForBrowser( $lang )
    {
        $lang = strtolower($lang);
        return( "<script src='https://maps.googleapis.com/maps/api/js?key={$this->keyBrowser}&language={$lang}' type='text/javascript'></script>" );
    }



    function Geocode( $sPlace )
    /**************************
        Returns array( lat, lng ) or null
     */
    {
        $sPlace = str_replace( " ", "+", urlencode($sPlace) );
        $url = "https://maps.googleapis.com/maps/api/geocode/json?address=".$sPlace."&key={$this->keyServer}";
        $out = null;

        $ch = curl_init();
        curl_setopt( $ch, CURLOPT_URL, $url );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
        $chexec = curl_exec( $ch );
        if( $chexec ) {
            $geocode = json_decode( $chexec, true );
            if( @$geocode['status'] == 'OK' && isset($geocode['results'][0]['geometry']['location']) ) {
                $out = array( 'lat' => $geocode['results'][0]['geometry']['location']['lat'],
                              'lng' => $geocode['results'][0]['geometry']['location']['lng'] );
            }
        }

        return( $out );
    }

}











// When Google wants to know who's asking, all of our web sites say this
define( "SODC_GOOGLE_API_REFERRER", "www.seeds.ca" );


function GoogleAPISearchResults( $sQuery, $sSiteFolder = "" )
/************************************************************
    Return an array of the results for the given Google search.

    $sSiteFolder limits results to one site or site folder (basically a prefix in the url)
    e.g.  'www.seeds.ca', 'www.seeds.ca/library'
 */
{
    // We obtained these API keys from www.google.com/api on the dates and through the accounts shown.  Each is only good for the url and its subdirectories.
    $raGoogleAPISearchKeys = array(
        "www.seeds.ca/library" => "ABQIAAAAZH7Z2EuHYHx72zrJHRiVphQLC2HSeGkvOCO3K2tyU9UPX0VGWxQi4cUtotmdSFHIIZWY1Tmq5qWrSQ",   // 2010-02-17  Bob's Google Account
    );


 	$raOut = array();
 	$raOut['results'] = array();

    $sUserIP = $_SERVER['REMOTE_ADDR'];    // Google wants us to tell it who the user-client is, so it can try to distinguish us from a search-engine scraper
    $sKey = "";

    if( !empty($sSiteFolder) ) {
        $sQuery = "site:".urlencode($sSiteFolder)."+".urlencode($sQuery);
        // Google allows anyone to issue a query without an API key, but you're not supposed to
        $sKey = @$raGoogleAPISearchKeys[$sSiteFolder];
    } else {
        $sQuery = urlencode( $sQuery );
    }

    $url = "http://ajax.googleapis.com/ajax/services/search/web?v=1.0"
          ."&q=".$sQuery
          .(empty($sKey) ? "" : "&key=$sKey")
          ."&userip=".$sUserIP;

    $ch = curl_init();
    curl_setopt( $ch, CURLOPT_URL, $url );
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
    curl_setopt( $ch, CURLOPT_REFERER, SODC_GOOGLE_API_REFERRER );
    $sResponse = curl_exec( $ch );
    curl_close( $ch );

    // The response is JSON-encoded
    // When all of our servers use PHP 5, use json_decode() instead. For now, use this class from PEAR.
    include(STDINC."os/JSON.php");
    $oJSON = new Services_JSON();
    $jsonResponse = $oJSON->decode($sResponse);
//var_dump($sResponse);
//var_dump($jsonResponse,$jsonResponse->responseData); // there are lots more interesting parms here

    if( isset($jsonResponse->responseData->results) ) {
        foreach( $jsonResponse->responseData->results as $o ) {
            $raOut['results'][] = array( 'url' => $o->url, 'title' => $o->title, 'titlePlain' => $o->titleNoFormatting );
        }
    }

    return( $raOut );
}

function GoogleAPIGeocode( $sAddress )
/*************************************
    $sAddress can be just about anything
 */
{
/*
    $this->address = $address;
           $address = str_replace (" ", "+", urlencode($address));
           $details_url = "http://maps.googleapis.com/maps/api/geocode/json?address=".$address."&sensor=false";

           $ch = curl_init();
           curl_setopt($ch, CURLOPT_URL, $details_url);
           curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
           $chexec = curl_exec($ch);
           if (!$chexec) { throw new Exception( 'Your request has issued a malformed request.'); }
           $response = json_decode($chexec, true);

           // If Status Code is ZERO_RESULTS, OVER_QUERY_LIMIT, REQUEST_DENIED or INVALID_REQUEST
           if ($response['status'] != 'OK') { throw new Exception( 'No response recieved ('.$this->address.'):  '.$response['status']);}

            $set1 = false;
            $set2 = false;
            $set3 = false;
           foreach($response as $key => $value){
               foreach($value[0]['address_components'] as $key1 => $value1){
                    if($value1['types'][0] == 'administrative_area_level_2' || $value1['types'][0] == 'administrative_area_level_1'){
                        $currCounty = $value1['short_name'];
                        $set1 = true;
                    }

                    if($value1['types'][0] == 'country'){
                        $currCountry = $value1['short_name'];
                        $set2 = true;
                    }
                    if($value1['types'][0] == 'postal_town'){
                        $currTown = $value1['long_name'];
                        $set3 = true;
                    }
                    if($value1['types'][0] == 'locality'){
                        $currTown = $value1['long_name'];
                        $set3 = true;
                    }
                    if($set1 && $set2 && $set3){
                        break(2);
                    }
                }
           }
           $this->response = $response;
           $geometry = $response['results'][0]['geometry'];

            $longitude = $geometry['location']['lng'];
            $latitude = $geometry['location']['lat'];

            $array = array(
                'lat' => $geometry['location']['lat'],
                'lng' => $geometry['location']['lng'],
                'country' => $currCountry,
                'county' => $currCounty,
                'town' => $currTown

            );
            return $array;
*/
}

?>
