<?

/*
CREATE TABLE pollcan_users (
        _key        INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT,
        _created    DATETIME,
        _created_by INTEGER,
        _updated    DATETIME,
        _updated_by INTEGER,
        _status     INTEGER DEFAULT 0,

    name            VARCHAR(200),
    email           VARCHAR(200),
    address         VARCHAR(200),
    city            VARCHAR(200),
    province        VARCHAR(200),
    postcode        VARCHAR(200),
    ext_uid         INTEGER,

    password        VARCHAR(200)    -- tmp until we use SEEDSession properly
);

CREATE TABLE pollcan_sites (
        _key        INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT,
        _created    DATETIME,
        _created_by INTEGER,
        _updated    DATETIME,
        _updated_by INTEGER,
        _status     INTEGER DEFAULT 0,

    fk_pollcan_users    INTEGER NOT NULL,
    nSite               INTEGER NOT NULL,
    maxVisit            INTEGER NOT NULL,

    siteName            VARCHAR(200),
    distance_from_road  VARCHAR(200),
    latitude            VARCHAR(200),
    longitude           VARCHAR(200),
    address             VARCHAR(200),
    city                VARCHAR(200),
    province            VARCHAR(200),

    landscape           TEXT,               -- space-delimited codes for landscape types

    INDEX (fk_pollcan_users)
);


CREATE TABLE pollcan_visits (
        _key        INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT,
        _created    DATETIME,
        _created_by INTEGER,
        _updated    DATETIME,
        _updated_by INTEGER,
        _status     INTEGER DEFAULT 0,

    fk_pollcan_sites    INTEGER NOT NULL,
    nVisit              INTEGER NOT NULL,

    date_visit          DATE,
    time_start          TIME,
    time_duration       INTEGER,
    weather_sky         ENUM('sunny','cloudy','overcast') DEFAULT NULL,
    weather_shade       ENUM('shaded','not_shaded') DEFAULT NULL,
    weather_wind        ENUM('windy_steady','windy_gusts','light_steady','light_gusts','calm') DEFAULT NULL,
    weather_temp        ENUM('cold','cool','seasonal','warm','hot') DEFAULT NULL,

    INDEX (fk_pollcan_sites)
);


CREATE TABLE pollcan_flowers (
        _key        INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT,
        _created    DATETIME,
        _created_by INTEGER,
        _updated    DATETIME,
        _updated_by INTEGER,
        _status     INTEGER DEFAULT 0,

-- flowers are recorded per-site so the rows can be re-used on multiple visits

    fk_pollcan_sites    INTEGER NOT NULL,
    name                VARCHAR(200),

    INDEX (fk_pollcan_sites)
);

CREATE TABLE pollcan_insects (
        _key        INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT,
        _created    DATETIME,
        _created_by INTEGER,
        _updated    DATETIME,
        _updated_by INTEGER,
        _status     INTEGER DEFAULT 0,

-- Each row is a distinct insect observation, relating an insect to a site-visit.
-- If the insect has a description registered, point to it.

    fk_pollcan_visits      INTEGER NOT NULL,
    fk_pollcan_desc        INTEGER DEFAULT 0,

    name                   VARCHAR(200),        -- user can record their own insect name
    fk_pollcan_insects_std INTEGER DEFAULT 0,   -- user can choose an insect name from a standard picklist
    key_pollcan_insects    INTEGER DEFAULT 0,   -- user can choose an insect name from a picklist of their own names

    eType                  ENUM('BEE','FLY','WASP','BEETLE','BUTTERFLY','MOTH','OTHER','UNKNOWN') DEFAULT NULL,         -- for named only
    size_mm                INTEGER,
    nObserved              INTEGER,
    last_seen              ENUM('NEVER','NOT_THIS_SUMMER','THIS_SUMMER','PAST_MONTH') DEFAULT NULL,

    INDEX (fk_pollcan_visits)
);


-- there should be a way for people to register insects and pick from previously recorded insects
-- or there should be a way for people to pick-list from the very common insects

-- Maybe people should be able to register their descriptions with reference to site, but not be limited in cross-referencing them by site.

-- Each user should have a table of insects that they have seen before (linked to desc).
-- On each insect row, they can either pick from that list, or they can write in a new one.
-- (they'll have to register a new description form before they do this, so they can link to it).
-- When they write in a new name, that gets added to their personal insect list.  This list acts as a metric of diversity,
-- and a sighting checklist.


CREATE TABLE pollcan_insectsxflowers (
        _key        INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT,
        _created    DATETIME,
        _created_by INTEGER,
        _updated    DATETIME,
        _updated_by INTEGER,
        _status     INTEGER DEFAULT 0,

-- Intersect each insect observation with the flowers that the insect visited.
-- Each pollcan_insect can visit multiple flowers.
-- Each pollcan_flower can be visited by multiple insects, on multiple visits.

-- Allow the observer to specify "unknown flower"

    fk_pollcan_insects  INTEGER NOT NULL,
    fk_pollcan_flowers  INTEGER NOT NULL,

    INDEX (fk_pollcan_insects),
    INDEX (fk_pollcan_flowers)
);
*/


$kfrelDef_Sites =
    array( "Tables" => array(
           array( "Table" => "pollcan_sites",
                  "Type"  => "Base",
                  "Fields" => array( array( "col"=>"fk_pollcan_users",    "type"=>"K" ),
                                     array( "col"=>"nSite",               "type"=>"I" ),
                                     array( "col"=>"maxVisit",            "type"=>"I" ),
                                     array( "col"=>"siteName",            "type"=>"S" ),
                                     array( "col"=>"distance_from_road",  "type"=>"S" ),
                                     array( "col"=>"latitude",            "type"=>"S" ),
                                     array( "col"=>"longitude",           "type"=>"S" ),
                                     array( "col"=>"address",             "type"=>"S" ),
                                     array( "col"=>"city",                "type"=>"S" ),
                                     array( "col"=>"province",            "type"=>"S" ),
                                     array( "col"=>"landscape",           "type"=>"S" )))));


$kfrelDef_Visits =
    array( "Tables" => array(
           array( "Table" => "pollcan_visits",
                  "Type"  => "Base",
                  "Fields" => array( array( "col"=>"fk_pollcan_sites",   "type"=>"K" ),
                                     array( "col"=>"nVisit",             "type"=>"I" ),
                                     array( "col"=>"date_visit",         "type"=>"S" ),
                                     array( "col"=>"time_start",         "type"=>"S" ),
                                     array( "col"=>"time_duration",      "type"=>"S" ),
                                     array( "col"=>"weather_sky",        "type"=>"S" ),
                                     array( "col"=>"weather_shade",      "type"=>"S" ),
                                     array( "col"=>"weather_wind",       "type"=>"S" ),
                                     array( "col"=>"weather_temp",       "type"=>"S" ))),
           array( "Table" => "pollcan_sites",
                  "Alias" => "Site",
                  "Type"  => "Parent",
                  "Fields" => array( array( "col"=>"fk_pollcan_users",   "type"=>"K" ),    // see prepopulation in GetKFRVisitFromParms
                                     array( "col"=>"siteName",           "type"=>"S" )))));


$kfrelDef_Insects =
    array( "Tables" => array(
           array( "Table" => "pollcan_insects",
                  "Type"  => "Base",
                  "Fields" => array( array( "col"=>"fk_pollcan_visits",     "type"=>"K" ),
                                     array( "col"=>"fk_pollcan_desc",       "type"=>"K" ),
                                     array( "col"=>"name",                  "type"=>"S" ),
                                     array( "col"=>"fk_pollcan_insects_std","type"=>"K" ),
                                     array( "col"=>"key_pollcan_insects",   "type"=>"K" ),
                                     array( "col"=>"eType",                 "type"=>"S" ),
                                     array( "col"=>"size_mm",               "type"=>"S" ),
                                     array( "col"=>"nObserved",             "type"=>"I" ),
                                     array( "col"=>"last_seen",             "type"=>"S" ))),
           array( "Table" => "pollcan_visits",
                  "Alias" => "Visit",
                  "Type"  => "Parent",
                  "Fields" => array( array( "col"=>"fk_pollcan_sites",   "type"=>"K" ),
                                     array( "col"=>"nVisit",             "type"=>"I" ),     // for sorting list
                                   )),
           array( "Table" => "pollcan_sites",
                  "Alias" => "Site",
                  "Type"  => "Parent",
                  "Fields" => array( array( "col"=>"fk_pollcan_users",   "type"=>"K" ),    // see prepopulation in GetKFRVisitFromParms
                                     array( "col"=>"siteName",           "type"=>"S" ),
                                     array( "col"=>"nSite",              "type"=>"I" ),     // for sorting list
                                   ))));


$kfrelDef_Flowers =
    array( "Tables" => array(
           array( "Table" => "pollcan_flowers",
                  "Type"  => "Base",
                  "Fields" => array( array( "col"=>"fk_pollcan_sites",    "type"=>"K" ),
                                     array( "col"=>"name",                "type"=>"S" )))));


$pcde_Text = array(
    "Pollination Canada" => array(
            "EN" => "Pollination Canada",
            "FR" => "Pollinisation Canada" ),

    "img_login" => array(
            "EN" => "../img/poll_banner_en.gif",
            "FR" => "../img/poll_banner_fr.gif" ),

    "img_home_banner" => array(
            "EN" => "../img/poll_banner_en.gif",
            "FR" => "../img/poll_banner_fr.gif" ),


    "Observer" => array(
            "EN" => "Observer",
            "FR" => "Observateur" ),


    "My Sites" => array(
            "EN" => "My Sites",
            "FR" => "Mes Sites" ),

    "My Insects" => array(
            "EN" => "Insects I've Seen", //"My Insects",
            "FR" => "Mes Insectes" ),

    "My Flowers" => array(
            "EN" => "Flowers I've Seen", //"My Flowers",
            "FR" => "Mes Fleurs" ),

    "Add Site" => array(
            "EN" => "Add Site",
            "FR" => "Ajouter Site" ),

    "button_add" => array(
            "EN" => "Add",
            "FR" => "Ajouter" ),

    "button_update" => array(
            "EN" => "Update",
            "FR" => "Modifier" ),

    "button_cancel" => array(
            "EN" => "Cancel",
            "FR" => "Retour" ),


    /* App ADDSITE
     */
    "Add a Site" => array(
            "EN" => "Add a Site",
            "FR" => "Ajoutez un Site" ),

    "Edit Site" => array(
            "EN" => "Edit this Site",
            "FR" => "Modifiez cet Site" ),

    "AddSite_instructions" => array(
        "EN" => "Enter this information from the top of the Pollinator Site Form.",
/* ! */ "FR" => "Entrez cette information de la Formulaire du site d'observation." ),

    "Site #" => array(
            "EN" => "Site #",
            "FR" => "Site No." ),

    "Location Name" => array(
            "EN" => "Location Name",
            "FR" => "Nom du site" ),

    "Distance from road" => array(
            "EN" => "Distance from road (approx.)",
            "FR" => "Distance de la route (approx.)" ),

    "Latitude" => array(
            "EN" => "Latitude (decimal degrees)",
            "FR" => "Latitude (degré decimal)" ),

    "Longitude" => array(
            "EN" => "Longitude (decimal degrees)",
            "FR" => "Longitude (degré decimal)" ),

    "Address" => array(
            "EN" => "Address",
            "FR" => "Adresse" ),

    "City" => array(
            "EN" => "City/Town",
            "FR" => "Ville" ),

    "Province" => array(
            "EN" => "Province",
            "FR" => "Province" ),

    "Landscape" => array(
            "EN" => "Landscape",
            "FR" => "Environnement" ),

    "Site # and Location Name must not duplicate another site" => array(
            "EN" => "Site # and Location Name must not duplicate another site",
/* ! */     "FR" => "Site # and Location Name must not duplicate another site" ),


    /* App VISIT
     */
    "Add a Visit" => array(
            "EN" => "Add a Visit",
            "FR" => "Ajoutez un Visite" ),

    "Edit Visit" => array(
            "EN" => "Edit this Visit",
            "FR" => "Modifiez cet Visite" ),

    "Visits" => array(
            "EN" => "Visits",
            "FR" => "Visites" ),

    "AddVisit_instructions" => array(
        "EN" => "Enter this information from the Pollinator Site Form or Pollinator Follow-up Form.",
        "FR" => "Entrez cette information de la Formulaire du site d'observation ou de la Formulaire du suivi." ),

    "Visit #" => array(
            "EN" => "Visit #",
            "FR" => "Visite No." ),

    "Date" => array(
            "EN" => "Date",
            "FR" => "Date" ),

    "Start Time" => array(
            "EN" => "Start Time",
            "FR" => "Heure de départ" ),

    "Time spent observing" => array(
            "EN" => "Time spent observing (min)",
            "FR" => "Durée de l'observation (min)" ),

    "Weather" => array(
            "EN" => "Weather",
            "FR" => "Météo" ),

    "wth_Sky" => array(
            "EN" => "Sky",
            "FR" => "Ciel" ),

    "wth_sunny" => array(
            "EN" => "sunny",
            "FR" => "ensoleillé" ),

    "wth_cloudy" => array(
            "EN" => "cloudy",
            "FR" => "nuageux" ),

    "wth_overcast" => array(
            "EN" => "overcast",
            "FR" => "couvert" ),

    "wth_Shade" => array(
            "EN" => "Shade",
            "FR" => "Ombre" ),

    "wth_shaded" => array(
            "EN" => "shaded",
            "FR" => "ombragé" ),

    "wth_not_shaded" => array(
            "EN" => "not shaded",
            "FR" => "non ombragé" ),

    "wth_Wind" => array(
            "EN" => "Wind",
            "FR" => "Vent" ),

    "wth_windy_steady" => array(
            "EN" => "windy, steady",
            "FR" => "venteux" ),

    "wth_windy_gusts" => array(
            "EN" => "windy in gusts",
            "FR" => "rafales" ),

    "wth_light_steady" => array(
            "EN" => "light breeze, steady",
            "FR" => "vent léger, continu" ),

    "wth_light_gusts" => array(
            "EN" => "light breeze in gusts",
            "FR" => "vent léger, passager" ),

    "wth_calm" => array(
            "EN" => "calm",
            "FR" => "calme" ),

    "wth_Temperature" => array(
            "EN" => "Temperature",
            "FR" => "Température" ),

    "wth_cold" => array(
            "EN" => "cold",
            "FR" => "froide" ),

    "wth_cool" => array(
            "EN" => "cool",
            "FR" => "fraîche" ),

    "wth_seasonal" => array(
            "EN" => "seasonal",
            "FR" => "normale" ),

    "wth_warm" => array(
            "EN" => "warm",
            "FR" => "chaude" ),

    "wth_hot" => array(
            "EN" => "hot",
            "FR" => "très chaude" ),

    "I_name_size" => array(
            "EN" => "Insect Name and Size",
            "FR" => "Nom et taille de l'insecte" ),

    "I_type" => array(
            "EN" => "Type",
            "FR" => "Type" ),

    "I_nObserved" => array(
            "EN" => "Number Observed",
            "FR" => "Quantité observée" ),

    "I_seen" => array(
            "EN" => "Last Seen",
            "FR" => "Insecte déjà observé" ),

    "I_flowers" => array(
            "EN" => "Flowers",
            "FR" => "Fleurs visitées" ),

    "I_size_mm" => array(
            "EN" => "size (mm)",
            "FR" => "taille (mm)" ),

    "I_type_bee"       => array( "EN" => "Bee",       "FR" => "Abeille" ),
    "I_type_fly"       => array( "EN" => "Fly",       "FR" => "Mouche" ),
    "I_type_wasp"      => array( "EN" => "Wasp",      "FR" => "Guêpe" ),
    "I_type_beetle"    => array( "EN" => "Beetle",    "FR" => "Coléoptre" ),
    "I_type_butterfly" => array( "EN" => "Butterfly", "FR" => "Papillon" ),
    "I_type_moth"      => array( "EN" => "Moth",      "FR" => "Pap. nocturne" ),
    "I_type_other"     => array( "EN" => "Other",     "FR" => "Autre" ),
    "I_type_unknown"   => array( "EN" => "Unknown",   "FR" => "Ne saie pas" ),


    "I_seen_never"           => array( "EN" => "Never",           "FR" => "Jamais" ),
    "I_seen_not_this_summer" => array( "EN" => "Not this summer", "FR" => "Pas cet été" ),
    "I_seen_this_summer"     => array( "EN" => "This summer",     "FR" => "Cet été" ),
    "I_seen_past_moth"       => array( "EN" => "Past Month",      "FR" => "Ce mois-ci" ),


    "Insect" => array(
            "EN" => "Insect",
            "FR" => "Insecte" ),



    // Créez un compte
    // Votre compte
    // Ouvrez  une session
    // Retour au précédent
    // Changez Le E-mail
    // Changez Le Mot de passe
    // Vous avez déjà un compte?
    //   Veuillez entrer votre adresse de courriel
    //   Veuillez entrer votre mot de passe
    // Vous avez oublié votre mot de passe? Cliquez ici.
    // Vous n'avez pas de compte?
    //   Prénom:
    //   Nom:
    //   Veuillez entrer votre adresse de courriel:
    // Modifier les informations de votre compte
    // Vos paramètres
    // Ajouter - to add
    // Modifier - to modify
    // Supprimer - to remove


    /* App LOGIN
     */
    "Your email address" => array(
            "EN" => "Your email address",
            "FR" => "Votre adresse de courriel" ),

    "Password" => array(
            "EN" => "Your password",
            "FR" => "Votre mot de passe" ),

    "Login" => array(
            "EN" => "Sign in",
            "FR" => "Ouvrez une session" ),

    "Logout" => array(
            "EN" => "Sign out",
/* ! */     "FR" => "Fermez la session" ),

    "Don't have an account?" => array(
            "EN" => "Don't have an account?",
            "FR" => "Vous n'avez pas un compte?"),

    "Create" => array(
            "EN" => "Create",
            "FR" => "Créez" ),

    "Create an account" => array(
            "EN" => "Create an account",
            "FR" => "Créez un compte" ),

    "Unknown user or password" => array(
            "EN" => "Unknown user or password",
/* ! */     "FR" => "L'adresse courriel ou le mot de passe n'est pas correct" ),

    "Account_U_already_exists" => array(
            "EN" => "The account %1% already exists",
/* ! */     "FR" => "Le compte %1% existe" ),



    "AddUser_Mail_Subject" => array(
            "EN" => "Your Pollination Canada password",
            "FR" => "Votre mot de passe de Pollinisation Canada" ),

    "AddUser_Mail_Body" => array(
            "EN" => "You are receiving this email because you registered a new account for the Pollination Canada program.\n\n"
                   ."Your sign-in information is:\n\n"
                   ."     Email: %1%\n"
                   ."     Password: %2%\n\n"
                   ."Please sign in to your account at www.pollinationcanada.ca\n\n"
                   ."If you have received this message in error, please contact info@pollinationcanada.ca",
/* ! */     "FR" => "You are receiving this email because you registered a new account for the Pollination Canada program.\n\n"
                   ."Your sign-in information is:\n\n"
                   ."     Email: %1%\n"
                   ."     Password: %2%\n\n"
                   ."Please sign in to your account at www.pollinationcanada.ca\n\n"
                   ."If you have received this message in error, please contact info@pollinationcanada.ca" ),

    "AddUser_check_your_email" => array(
            "EN" => "<B>Your Pollination Canada password has been emailed to %1%</B>",
/* ! */     "FR" => "<B>Votre mot de passe de Pollinisation Canada est envoyé par courriel à l'adresse %1%</B>" ),
);



/* Landscape Types are used in several ways:
 *      The keys are stored in pollcan_sites.landscape to record landscapes per site.
 *      The keys are transformed into Form parms for the PCDE Site form, with a prefix to marshall them on the input end.
 *      The keys are transformed into SEEDLocal keys with prefix "landtype_".
 *      The keys are the actual English terms that appear on the Site form.
 *      The values are the French terms that appear on the Site form.
 *      This means that there is no indirection layer that would allow an English term to be, say, re-spelled, without affecting existing data.
 */
$raLandscapeTypes =
array( "urban"      => "urbain",              "suburban"   => "banlieue",                    "rural"    => "rural",      "park"     => "parc",
       "wilderness" => "nature",              "embankment" => "fossé (route/chemin de fer)", "vacant"   => "lot vacant", "cropland" => "terre agricole",
       "meadow"     => "pâturage ou prairie", "orchard"    => "verger",                      "hedgerow" => "haie",       "garden"   => "jardin aménagé",
       "forest"     => "forêt",               "riverbank"  => "bord de l'eau",               "public"   => "public" );





class PollCan_DataEntry
/**********************
 */
{
    var $kfdb;
    var $sess;
    var $lang;
    var $kfrelSites;
    var $kfrelVisits;
    var $kfrelInsects;
    var $kfrelFlowers;

    var $bDebug = false;    // set this to true to get lots of info.  Also sets the AddUser password to a preset value in case your dev server doesn't have mail

    function PollCan_DataEntry( &$kfdb, &$sess, $lang )
    /**************************************************
     */
    {
        global $pcde_Text;
        global $kfrelDef_Sites;
        global $kfrelDef_Visits;
        global $kfrelDef_Insects;
        global $kfrelDef_Flowers;

        $this->kfdb = &$kfdb;
        $this->sess = &$sess;
        $this->lang = $lang;
        $this->oLocal = new SEEDLocal( $pcde_Text, $lang );

        $uid = $this->GetUserKey();     // $sess->GetUID() is not defined in this instantiation of session

        $this->kfrelSites   = new KeyFrameRelation( $kfdb, $kfrelDef_Sites,   $uid );
        $this->kfrelVisits  = new KeyFrameRelation( $kfdb, $kfrelDef_Visits,  $uid );
        $this->kfrelInsects = new KeyFrameRelation( $kfdb, $kfrelDef_Insects, $uid );
        $this->kfrelFlowers = new KeyFrameRelation( $kfdb, $kfrelDef_Flowers, $uid );

        /* make a SEEDLocal array out of the Landscape Types
         */
        global $raLandscapeTypes;
        $ra = array();
        foreach( $raLandscapeTypes as $k => $v ) {
            $ra["landtype_$k"] = array( "EN" => $k, "FR" => $v );
        }
        $this->oLocal->AddStrsCopy($ra);
    }


    function IsUserActive()  { return( $this->GetUserKey() != 0 ); }
    function GetUserKey()    { return( intval($this->sess->VarGet('pcde_kUser')) ); }
    function GetUserEmail()  { return( $this->sess->VarGet('pcde_userEmail') ); }
    function UserLogout()    { $this->UserActivate( 0, "" ); }

    function UserActivate( $kUser, $email )
    {
        $this->sess->VarSet( 'pcde_kUser', intval($kUser) );
        $this->sess->VarSet( 'pcde_userEmail', $email );
    }

    function S( $idLocal, $ra=array() ) { return( $this->oLocal->S( $idLocal, $ra ) ); }


    function Style()
    /***************
     */
    {
        echo "<STYLE>"
            ."body { font-family: verdana,geneva,arial,helvetica,sans-serif; font-size:12px; }"
            ."th { font-size:10px; background-color:#AAAAAA; }"
            ."#pc_home_table {}"
            ."#pc_homecol_left   { padding:10px; border-right:2px ridge grey; }"
            ."#pc_homecol_right  { padding:10px; border-left: 2px ridge grey; }"
            .".pc_home_box, .pc_home_box p, .pc_home_box input { font-size:10px; }"
            .".pc_home_boxheader { font-size:12px; font-weight:bold; color:white; background-color:#AAAAAA; text-align:center; padding:5px; }"
            .".pc_box { border:2px solid #333333; padding:10px; margin:5px; }"
            .".pc_box, .pc_box td { font-size:10px; }"
            .".pc_form_table {"
                ."border:1px solid #666666; padding:0; margin:0;"
            ."}"
            .".pc_form_table th { border:1px solid #666666; padding:2px; margin:0; font-size:10px; background-color:#CCCCCC; }"
            .".pc_form_table td { border:1px solid #666666; padding:2px; margin:0; font-size:10px; }"
            .".pc_form_table input, .pc_form_table select { font-size:10px; }"
            ."</STYLE>";
    }


    function DrawHomeBox( $sTitle, $sContent )
    /*****************************************
     */
    {
        $s = "<DIV class='pc_home_box'>"
             ."<DIV width='20%' class='pc_home_boxheader'>$sTitle</DIV>"
             .$sContent
             ."</DIV>";

        return( $s );
    }


    function EstablishUserSession()
    /******************************
        Establish the current user session, if it exists
     */
    {
        // Currently does nothing, because the whole user session is just a non-zero pcde_kUser session var
        // that is set at LOGIN
    }
}

?>
