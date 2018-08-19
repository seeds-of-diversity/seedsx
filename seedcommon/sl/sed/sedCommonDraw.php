<?php

/* SEDCommonDraw
 *
 * Copyright 2011-2017 Seeds of Diversity Canada
 *
 * Drawing and DB access for Seed Directory
 */

class SEDCommonDraw extends SEDCommonDB
/******************
    Methods to draw parts of the Seed Directory.
    Doesn't need a login.

    The reason we need a login-independent drawing class is that sess->GetUID() is not always the grower we want to see.
    e.g. the office sed interface.
    e.g. better yet, mailsend can draw peoples' seed directory info, and it doesn't have a SEEDSessionAccount. Moreover, it
         shouldn't, because SEEDSessionAccount will automatically log itself in as the user who starts the mailsend (if they have
         another window logged in on their browser). Then who knows what side-effect a poorly written email might have.

    Modes:
        LAYOUT   = paper directory format, slightly different than the online format re symbols and positioning
        VIEW-PUB = online format with no grower identification
        VIEW-MBR = online format with grower identification and links to order seeds
        EDIT     = with edit UI
        REVIEW   = online format showing skips and deletes but no UI
 */
{
    private $lang;
    public $eReportMode = "VIEW-PUB";  // set by SetReportMode() : "VIEW-PUB", "VIEW-MBR", "EDIT", "LAYOUT", "REVIEW"
    public $bHideDetail = false;

    function __construct( KeyFrameDB $kfdb1, $uid, $lang, $eReportMode )
    {
        parent::__construct( $kfdb1, $uid );
        $this->lang = $lang;
        $this->SetReportMode( $eReportMode );
    }

    function SetReportMode( $eReportMode )
    {
        $this->eReportMode = SEEDStd_SmartVal( $eReportMode, array("VIEW-PUB","VIEW-MBR","EDIT","LAYOUT","REVIEW") );     // defaults to public view
    }

    function DrawTypes( $cat, $bLink = false, $bUCWords = false )
    {
        $s = "";

        $raTypes = array();
        if( ($kfr = $this->GetKfrcS_Cat( $cat, "", array("sGroupCol"=>"type","sSortCol"=>"type")) ) ) {
            while( $kfr->CursorFetch() ) {
                // this seems weird, but the structure is needed for translation
                // key is sortable-label, label is displayed (with entities instead of accented chars), type is the db value sent in the link
                $raTypes[$kfr->Value('type')] = array( 'label' => $kfr->Value('type'), 'type' => $kfr->Value('type') );
            }
        }

        if( $this->lang != "EN" )  $raTypes = $this->translateTypes( $raTypes );

        foreach( $raTypes as $dummyK => $ra ) {
            $sLabel = $ra['label'];
            $sType = $ra['type'];

            $sLink = $bLink ? (" onclick='console01FormSubmit(\"SetType\",\"$cat\", \"".SEEDStd_HSC($sType)."\");'"): "";
            if( $bUCWords ) $sLabel = ucwords(strtolower($sLabel));
            $s .= "<DIV class='sedTypename'$sLink>$sLabel</DIV>";
        }
        return( $s );
    }

    private function translateTypes( $raTypesEN )
    /********************************************
        In:  array of typeEN => typeEN
        Out: array of typeFR => typeEN where possible

        The keys are sortable (unaccented versions of) labels, so &Eacute;pinards will sort as Epinard but display with the accent.
     */
    {
        $raFR = array();

        foreach( $raTypesEN as $k => $ra ) {
            if( isset($this->raTypesCanon[$k]['FR']) ) {
                // This would be great except for words like &Eacute;pinards (spinach) that start with a '&' which sorts to the top.
                // Something like $k = html_entity_decode( $this->raTypesCanon[$v]['FR'], ENT_COMPAT, 'ISO8859-1' );
                // would be great, to collapse the entity back to a latin-1 character except you have to set a French collation using setlocale
                // to make the sorting work, else the accented E sorts after z. And who knows how portable the setlocale will be - is fr_FR or fr_CA
                // language pack installed?
                // So the brute force method works best, though it will be a challenge if we want to get these names out of SEEDLocal if they have
                // accented letters at or near the first char.
                if( isset($this->raTypesCanon[$k]['FR_sort']) ) {
                    $kSort = $this->raTypesCanon[$k]['FR_sort'];   // use a non-accented version of the name for sorting, and accented version for display
                } else {
                    $kSort = $this->raTypesCanon[$k]['FR'];
                }
                $raFR[$kSort] = array( 'label' => $this->raTypesCanon[$k]['FR'], 'type' => $k );
            } else {
                $raFR[$k] = array( 'label' => $k, 'type' => $k );
            }
        }
        ksort( $raFR );
        return( $raFR );
    }

private $_lastCategory = "";
private $_lastType = "";

    function DrawSeedFromKFR( KFRecord $kfrS, $raParms = array() )
    {
        $sOut = "";

        $bNoSections = (@$raParms['bNoSections'] == true);  // true: you have to write the category/type headers yourself

        // mbrCode of the grower who offers this seed should only be displayed in interactive and layout modes, not public view mode
// it would make more sense for this to be available via a join
        $mbrCode = ($this->eReportMode != 'VIEW-PUB' &&
                    ($kfrG = $this->kfrelG->GetRecordFromDB( "mbr_id='".$kfrS->value('mbr_id')."'" )) )
                   ? $kfrG->value('mbr_code')
                   : "";

        if( !$bNoSections && $this->_lastCategory != $kfrS->value('category') ) {
            /* Start a new category
             */
            $sCat = $kfrS->value('category');
            if( $this->lang == 'FR' ) {
// should be a better accessor
                foreach( $this->raCategories as $ra ) {
                    if( $ra['db'] == $kfrS->value('category') ) {
                        $sCat = $ra['FR'];
                        break;
                    }
                }
            }
            $sOut .= "<DIV class='sed_category'><H2>$sCat</H2></DIV>";
            $this->_lastCategory = $kfrS->value('category');
            // when Searching on a duplicated Type, it is possible to view more than one category with the same type, so this causes the second category to show the Type
            $this->_lastType = "";
        }
        if( !$bNoSections && $this->_lastType != $kfrS->value('type') ) {
            /* Start a new type
             */
            $sType = $kfrS->value('type');
            if( $this->eReportMode == 'LAYOUT' ) {
                if( ($sFR = @$this->raTypesCanon[$sType]['FR']) ) {
                    $sType .= " @T@ $sFR";
                }
            } else {
                if( $this->lang == 'FR' && isset($this->raTypesCanon[$sType]['FR']) ) {
                    $sType = $this->raTypesCanon[$sType]['FR'];
                }
            }
            $sOut .= "<DIV class='sed_type'><H3>$sType</H3></DIV>";
            $this->_lastType = $kfrS->value('type');
        }

        /* FloatRight contains everything that goes in the top-right corner
         */
        $sFloatRight = "";
        if( $this->eReportMode == 'EDIT' && !$kfrS->value('bSkip') && !$kfrS->value('bDelete') ) {
            switch( $kfrS->Value('eOffer') ) {
                default:
                case 'member':        $sFloatRight .= "<div class='sed_seed_offer sed_seed_offer_member'>Offered to All Members</div>";  break;
                case 'grower-member': $sFloatRight .= "<div class='sed_seed_offer sed_seed_offer_growermember'>Offered to Members who offer seeds in the Directory</div>";  break;
                case 'public':        $sFloatRight .= "<div class='sed_seed_offer sed_seed_offer_public'>Offered to the General Public</div>"; break;
            }
        }
        if( $this->eReportMode != 'LAYOUT' )  $sFloatRight .= "<div class='sed_seed_mc'>$mbrCode</div>";

        /* Buttons1 is the standard set of buttons : Edit, Skip, Delete
         * Buttons2 is Un-Skip and Un-Delete
         */
        $sButtons1 = $this->getButtonsSeed1( $kfrS );
        $sButtons2 = $this->getButtonsSeed2( $kfrS );

        /* Draw the seed listing
         */
        $s = "<b>".$kfrS->value('variety')."</b>"
            .( $this->eReportMode == 'LAYOUT'
               ? (" @M@ <b>$mbrCode</b>".$kfrS->ExpandIfNotEmpty( 'bot_name', "<br/><b><i>[[]]</i></b>" ))
               : ($kfrS->ExpandIfNotEmpty( 'bot_name', " <b><i>[[]]</i></b>" )))
            ;
        if( $this->eReportMode == "VIEW-MBR" ) {
            // Make the variety and mbr_code blue and clickable
            $s = "<span style='color:blue;cursor:pointer;' onclick='console01FormSubmit(\"ClickSeed\",".$kfrS->Key().");'>$s</span>";
        }

        $s .= $sButtons1;

        $s .= "<br/>";

        if( $this->eReportMode != "EDIT" || !$this->bHideDetail ) {
            $s .= $kfrS->ExpandIfNotEmpty( 'days_maturity', "[[]] dtm. " )
               // this doesn't have much value and it's readily mistaken for the year of harvest
               //  .($this->bReport ? "@Y@: " : "Y: ").$kfrS->value('year_1st_listed').". "
                 .$kfrS->value('description')." "
                 .$kfrS->ExpandIfNotEmpty( 'origin', (($this->eReportMode == "LAYOUT" ? "@O@" : "Origin").": [[]]. ") )
                 .$kfrS->ExpandIfNotEmpty( 'quantity', "<b><i>[[]]</i></b>" );

             if( ($price = $kfrS->Value('price')) != 0.00 ) {
                 $s .= " ".($this->lang=='FR' ? "Prix" : "Price")." $".$price;
             }
        }

        if( in_array($this->eReportMode, array("EDIT","REVIEW")) ) {
            // Show colour-coded backgrounds for Deletes, Skips, and Changes
            if( $kfrS->value('bDelete') ) {
                $s = "<div class='sed_seed_delete'><b><i>".($this->lang=='FR' ? "Supprim&eacute;" : "Deleted")."</i></b>"
                    .SEEDStd_StrNBSP("   ")
                    .$sButtons2
                    ."<br/>$s</div>";
            } else if( $kfrS->value('bSkip') ) {
                $sStyle = ($this->eReportMode == 'REVIEW') ? "style='background-color:#aaa'" : "";    // because this is used without <style>
                $s = "<div class='sed_seed_skip' $sStyle><b><i>".($this->lang=='FR' ? "Pass&eacute;" : "Skipped")."</i></b>"
                    .SEEDStd_StrNBSP("   ")
                    .$sButtons2
                    ."<br/>$s</div>";
            } else if( $kfrS->value('bChanged') ) {
                $s = "<div class='sed_seed_change'>$s</div>";
            }
        }

        // Put the FloatRight at the very top of the output block
        $s = "<div style='float:right'>$sFloatRight</div>".$s;

        if( in_array( $this->eReportMode, array('VIEW-MBR', 'VIEW-PUB', 'EDIT')) ) {
            // Wrap the seed listing with an id
            $sOut .= "<div class='sed_seed' id='Seed".$kfrS->Key()."'>$s</div>";
        } else {
            $sOut .= $s;
        }

        return( $sOut );
    }

    protected function getButtonsSeed1( KFRecord $kfrS )
    {
        // This class uses this method to fetch UI buttons for Edit, Skip, Delete.
        // In the base instance, there are no such buttons because there is no UI.
        // Derived classes can provide these buttons.
        return( "" );
    }

    protected function getButtonsSeed2( KFRecord $kfrS )
    {
        // This class uses this method to fetch UI buttons for Un-Skip, Un-Delete.
        // In the base instance, there are no such buttons because there is no UI.
        // Derived classes can provide these buttons.
        return( "" );
    }

    public $raCategories = array(
            'flowers'    => array( 'db' => "FLOWERS AND WILDFLOWERS", 'EN' => "Flowers and Wildflowers", 'FR' => "Fleurs et gramin&eacute;es sauvages et ornementales" ),
            'vegetables' => array( 'db' => "VEGETABLES",              'EN' => "Vegetables",              'FR' => "L&eacute;gumes" ),
            'fruit'      => array( 'db' => "FRUIT",                   'EN' => "Fruits",                  'FR' => "Fruits" ),
            'herbs'      => array( 'db' => "HERBS AND MEDICINALS",    'EN' => "Herbs and Medicinals",    'FR' => "Fines herbes et plantes m&eacute;dicinales" ),
            'grain'      => array( 'db' => "GRAIN",                   'EN' => "Grains",                  'FR' => "C&eacute;r&eacute;ales" ),
            'trees'      => array( 'db' => "TREES AND SHRUBS",        'EN' => "Trees and Shrubs",        'FR' => "Arbres et arbustes" ),
            'misc'       => array( 'db' => "MISC",                    'EN' => "Miscellaneous",           'FR' => "Divers" ),
        );

    protected $raTypesCanon = array(
            'ALPINE COLUMBINE' => array( 'FR' => 'Ancolie des Alpes' ),
            'COLUMBINE' => array( 'FR' => 'Ancolie' ),
            'BACHELOR BUTTONS' => array( 'FR' => 'Bluet' ),
            'CALENDULA' => array( 'FR' => 'Souci' ),
            'CASTOR OIL PLANT' => array( 'FR' => 'Ricin' ),
            'COLUMBINE' => array( 'FR' => 'Ancolie' ),
            'COTTON' => array( 'FR' => 'Coton' ),
            'GAILLARDIA' => array( 'FR' => 'Gaillarde' ),
            'HOLLYHOCK'  => array( 'FR' => 'Tr&eacute;mi&egrave;re' ),
            'LATHYRUS (SWEET PEA)' => array( 'FR' => 'Pois de senteur' ),
            'LAVATERA' => array( 'FR' => 'Lavat&egrave;re' ),
            'MARIGOLD' => array( 'FR' => "Oeillets d'Inde" ),
            'MORNING GLORY' => array( 'FR' => 'Belle-de-jour' ),
            'NASTURTIUM' => array( 'FR' => 'Capucine' ),
            'OENOTHERA' => array( 'FR' => 'Onagre' ),
            'SUNFLOWER' => array( 'FR' => 'Tournesol' ),


            'APPLE' => array( 'FR' => 'Pommes' ),
            'BLACK ELDERBERRY' => array( 'FR' => 'Baies de sureau' ),
            'BLACKBERRY' => array( 'FR' => 'M&ucirc;res' ),
            'CURRANT' => array( 'FR' => 'Groseilles' ),
            'GRAPE' => array( 'FR' => 'Raisins' ),
            'GARDEN HUCKLEBERRY' => array( 'FR' => 'Airelles' ),
            'LITCHI TOMATO' => array( 'FR' => 'Morelle de balbis' ),
            'MEDLAR' => array( 'FR' => 'N&eacute;flier' ),
            'MELON' => array( 'FR' => 'Melons' ),
            'MELON/MUSKMELON' => array( 'FR' => 'Melons/Cantaloups' ),
            'RHUBARB' => array( 'FR' => 'Rhubarbe' ),
            'STRAWBERRY' => array( 'FR' => 'Fraises' ),
            'WATERMELON' => array( 'FR' => "Past&egrave;ques melon d'eau" ),


            'BARLEY' => array( 'FR' => 'Orge' ),
            'OATS' => array( 'FR' => 'Avoine' ),
            'PEARL MILLET' => array( 'FR' => 'Mil &agrave; chandelle' ),
            'SORGHUM' => array( 'FR' => 'Sorgho' ),
            'WHEAT' => array( 'FR' => 'Bl&eacute;' ),


            'ANGELICA' => array( 'FR' => 'Ang&eacute;lique' ),
            'ANISE HYSSOP' => array( 'FR' => 'Agastache' ),
            'BASIL' => array( 'FR' => 'Basilic' ),
            'BLACK CUMIN' => array( 'FR' => 'Cumin noir' ),
            'BORAGE' => array( 'FR' => 'Bourrache' ),
            'CARAWAY' => array( 'FR' => 'Carvi' ),
            'CATNIP' => array( 'FR' => 'Cataire' ),
            'CELERY (SMALLAGE)' => array( 'FR' => 'C&eacute;l&eacute;ri' ),
            'CHAMOMILE' => array( 'FR' => 'Camomille' ),
            'CHERVIL' => array( 'FR' => 'Cerfeuil' ),
            'CHICORY' => array( 'FR' => 'Chicor&eacute;e' ),
            'CHIVES' => array( 'FR' => 'Ciboulette' ),
            'COMFREY' => array( 'FR' => 'Consoude' ),
            'CORIANDER/CILANTRO' => array( 'FR' => 'Coriandre persil arabe' ),
            'CRESS' => array( 'FR' => 'Cresson' ),
            'DILL' => array( 'FR' => 'Aneth' ),
            "DYER'S BROOM" => array( 'FR' => 'Gen&ecirc;t des teinturiers' ),
            'EDIBLE BURDOCK' => array( 'FR' => 'Bardane' ),
            'ELECAMPANE' => array( 'FR' => 'Aun&eacute;e' ),
            'EVENING PRIMROSE' => array( 'FR' => 'Oenoth&egrave;re onagre' ),
            'FENNEL' => array( 'FR' => 'Fenouil' ),
            'FENUGREEK' => array( 'FR' => 'Fenugrec' ),
            'FEVERFEW' => array( 'FR' => 'Grande camomille' ),
            'HOPS' => array( 'FR' => 'Houblon' ),
            'HOREHOUND' => array( 'FR' => 'Marrube' ),
            'HORSERADISH (ROOTS)' => array( 'FR' => 'Raifort' ),
            'HORSERADISH' => array( 'FR' => 'Raifort' ),
            "LAMB'S QUARTERS" => array( 'FR' => 'Ch&eacute;noposium' ),
            'LEMON BALM' => array( 'FR' => 'T&ecirc;te de dragon' ),
            'LOVAGE' => array( 'FR' => 'Liv&egrave;che' ),
            'MADDER' => array( 'FR' => 'Garance' ),
            'MALVA (MALLOW)' => array( 'FR' => 'Mauve' ),
            'MEXICAN TARRAGON' => array( 'FR' => 'Estragon Mexicain' ),
            'NETTLE' => array( 'FR' => 'Ortie' ),
            'OREGANO' => array( 'FR' => 'Origan' ),
            'PARSLEY' => array( 'FR' => 'Persil' ),
            'PEPPERGRASS' => array( 'FR' => 'Cresson al&eacute;nois' ),
            'POKEWEED' => array( 'FR' => "Raisin d'Am&eacute;rique" ),
            'POLYGONUM' => array( 'FR' => 'Renou&eacute;e des teinturiers' ),
            'SAGE' => array( 'FR' => 'Sauge' ),
            'SALAD BURNET' => array( 'FR' => 'Sanguisorba pimprenelle' ),
            'SORREL' => array( 'FR' => 'Oseille' ),
            "ST. JOHN'S WORT" => array( 'FR' => 'Millepertuis' ),
            'SUMMER SAVORY' => array( 'FR' => 'Sarriette' ),
            'SWEET CICELY' => array( 'FR' => 'Cerfeuil musqu&eacute;' ),
            'TANSY' => array( 'FR' => 'Tanasie' ),
            'TEASEL' => array( 'FR' => 'Card&egrave;re cultiv&eacute;e' ),
            'THYME' => array( 'FR' => 'Thym' ),
            'TOBACCO' => array( 'FR' => 'Tabac' ),
            'TULSI (HOLY BASIL)' => array( 'FR' => 'Basilic sacr&eacute;' ),
            'VALERIAN' => array( 'FR' => 'Val&eacute;riane' ),
            'WOAD' => array( 'FR' => 'Pastel' ),
            'WORMWOOD' => array( 'FR' => 'Absinthe' ),
/*
            '' => array( 'FR' => '' ),
*/
            'CHINESE ARTICHOKE' => array( 'FR' => 'Crosnes du Japon' ),
            'GROUND ALMOND' => array( 'FR' => 'Souchet' ),
            'ROBINIA' => array( 'FR' => 'Robinier' ),
            'SUMAC' => array( 'FR' => 'Vinaigrier' ),


            'AMARANTH' => array( 'FR' => 'Amarante' ),
            'ARUGULA' => array( 'FR' => 'Roquette' ),
            'ASPARAGUS' => array( 'FR' => 'Asperges' ),
            'BEAN/ADZUKI' => array( 'FR' => 'F&egrave;ves - Adzuki' ),
            'BEAN/BUSH' => array( 'FR' => 'F&egrave;ves - Plants nains' ),
            'BEAN/FAVA (BROAD)' => array( 'FR' => 'F&egrave;ves - Fava (Larges)' ),
            'BEAN/OTHER' => array( 'FR' => 'F&egrave;ves et haricots - Divers' ),
            'BEAN/POLE' => array( 'FR' => 'F&egrave;ves - Plants grimpants' ),
            'BEAN/RUNNER' => array( 'FR' => "Haricots d'Espagne" ),
            'BEAN/SOY' => array( 'FR' => 'F&egrave;ves de soya' ),
            'BEAN/WAX/BUSH' => array( 'FR' => 'Haricots - Plants nains' ),
            'BEAN/WAX/POLE' => array( 'FR' => 'Haricots - Plants grimpants' ),
            'BEET' => array( 'FR' => 'Betteraves' ),
            'BEET/SUGAR' => array( 'FR' => 'Betteraves &agrave; sucre' ),
            'BROCCOLI' => array( 'FR' => 'Brocolis' ),
            'CABBAGE' => array( 'FR' => 'Choux' ),
            'CABBAGE/CHINESE' => array( 'FR' => 'Choux chinois' ),
            'CARROT' => array( 'FR' => 'Carottes' ),
            'CELERY' => array( 'FR' => 'C&eacute;leris' ),
            'CHICKPEA' => array( 'FR' => 'Pois chiches' ),
            'CORN SALAD' => array( 'FR' => 'M&acirc;che' ),
            'CORN/FLINT' => array( 'FR' => 'Ma&iuml;s corn&eacute;' ),
            'CORN/FLOUR' => array( 'FR' => 'Ma&iuml;s &agrave; farine' ),
            'CORN/POP' => array( 'FR' => 'Ma&iuml;s &agrave; souffler' ),
            'CORN/SWEET' => array( 'FR' => 'Ma&iuml;s sucr&eacute;' ),
            'COWPEA' => array( 'FR' => 'Doliques' ),
            'CUCUMBER/MEXICAN SOUR GHERKIN' => array( 'FR' => 'Concombres &agrave; confire', 'EN'=> 'Cucumber - Mexican Sour Gherkin' ),
            'CUCUMBER/PICKLING' => array( 'FR' => 'Concombres &agrave; mariner', 'EN'=> 'Cucumber - Pickling' ),
            'CUCUMBER/SLICING' => array( 'FR' => 'Concombres frais', 'EN'=> 'Cucumber - Slicing' ),
            'EGGPLANT' => array( 'FR' => 'Aubergines' ),
            'GARLIC' => array( 'FR' => 'Ail' ),
            'GOURD/EDIBLE' => array( 'FR' => 'Gourdes comestibles' ),
            'GREENS' => array( 'FR' => 'Verdure' ),
            'GROUND ALMOND' => array( 'FR' => 'Souchet comestible amande de terre' ),
            'GROUND CHERRY' => array( 'FR' => 'Cerises de terre' ),
            'JERUSALEM ARTICHOKE' => array( 'FR' => 'Topinambour' ),
            'KALE' => array( 'FR' => 'Choux fris&eacute;s' ),
            'KOHLRABI' => array( 'FR' => 'Kohlrabi' ),
            'LEEK' => array( 'FR' => 'Poireaux' ),
            'LETTUCE/HEAD' => array( 'FR' => 'Laitues en pomme' ),
            'LETTUCE/LEAF' => array( 'FR' => 'Laitues en feuille' ),
            'LETTUCE/ROMAINE' => array( 'FR' => 'Laitues romaines' ),
            'MUSTARD/GREENS' => array( 'FR' => 'Moutarde en feuille' ),
            'OKRA' => array( 'FR' => 'Okra' ),
            'ONION' => array( 'FR' => 'Oignons' ),
            'ONION/GREEN' => array( 'FR' => 'Oignons verts' ),
            'ONION/MULTIPLIER/ROOT' => array( 'FR' => 'Oignons' ),
            'ONION/MULTIPLIER/TOP' => array( 'FR' => 'Oignons &eacute;gyptiens' ),
            'ORACH' => array( 'FR' => 'Arroche' ),
            'PARSNIP' => array( 'FR' => 'Panais' ),
            'PEA' => array( 'FR' => 'Pois' ),
            'PEA/EDIBLE PODDED' => array( 'FR' => 'Pois mange-tout' ),
            'PEPPER/HOT' => array( 'FR' => 'Piments forts' ),
            'PEPPER/OTHER' => array( 'FR' => 'Poivrons divers' ),
            'PEPPER/SWEET' => array( 'FR' => 'Poivrons doux' ),
            'POTATO' => array( 'FR' => 'Pommes de terre' ),
            'RADISH' => array( 'FR' => 'Radis' ),
            'SALSIFY/SCORZONERA' => array( 'FR' => 'Salsifis' ),
            'SKIRRET' => array( 'FR' => 'Chervis' ),
            'SPINACH' => array( 'FR' => '&Eacute;pinards', 'FR_sort' => 'Epinards' ),  // '&' initial is not good for sorting
            'SPINACH/MALABAR' => array( 'FR' => '&Eacute;pinard de Malabar', 'FR_sort' => 'Epinard de Malabar' ),  // '&' initial is not good for sorting
            'SPINACH/STRAWBERRY' => array( 'FR' => '&Eacute;pinard-Fraise', 'FR_sort' => 'Epinard-Fraise' ),  // '&' initial is not good for sorting
            'SQUASH/MAXIMA' => array( 'FR' => 'Courges (Cucurbita maxima)' ),
            'SQUASH/MIXTA' => array( 'FR' => 'Courges (Cucurbita mixta)' ),
            'SQUASH/MOSCHATA' => array( 'FR' => 'Courges (Cucurbita moschata)' ),
            'SQUASH/PEPO' => array( 'FR' => 'Courges/Citrouilles (Cucurbita pepo)' ),
            'SWISS CHARD' => array( 'FR' => 'Bette &agrave; carde' ),
            'TOMATO/MISC OR MULTI-COLOUR' => array( 'FR' => 'Tomates - Couleurs diverses' ),
            'TOMATO/MISC SPECIES' => array( 'FR' => 'Tomates - Esp&egrave;ces diverses' ),
            'TOMATO/PINK TO PURPLE SKIN' => array( 'FR' => 'Tomates - Peaux roses &agrave; pourpres' ),
            'TOMATO/RED SKIN' => array( 'FR' => 'Tomates - Peaux rouges' ),
            'TOMATO/YELLOW TO ORANGE SKIN' => array( 'FR' => 'Tomates - Peaux jaunes &agrave; oranges' ),
            'TURNIP' => array( 'FR' => 'Navets' ),
            'TURNIP/RUTABAGA' => array( 'FR' => 'Rutabagas' ),
    );
}



class SEDCommonDB
/****************
    DB access functions.
    Language independent, and doesn't need a login.
 */
{
    public $kfdb;

    public $kfrelG;
    public $kfrelS;
    public $kfrelSxG;
    public $kfrelGxM = NULL;  // created by SEDOffice using kfdb2

    function __construct( KeyFrameDB $kfdb1, $uid )
    {
        $this->kfdb = $kfdb1;
        $this->_initKFRel( $uid );
    }

    function GetKfrcS( $cond, $raKfParms = array(), $eMode = "" )
    /************************************************************
        Get a Seed kfr cursor.  VIEW/LAYOUT mode: exclude bSkip and bDelete, EDIT mode: include bSkip and bDelete, default: use eReportMode
     */
    {
// eReportMode is really a DB level thing
        if( !$eMode )  $eMode = $this->eReportMode;
        if( !in_array($eMode, array('EDIT','REVIEW') ) ) {
            $cond = "($cond) AND NOT bSkip AND NOT bDelete";  // $cond is always something, right?
        }
        return( $this->kfrelS->CreateRecordCursor( $cond, $raKfParms ) );
    }
    function GetKfrcS_Cat( $category, $cond = "", $raKfParms = array(), $eMode = "" )
    {
        if( $cond )  $cond = "($cond) AND ";
        $cond .= "category='".$this->raCategories[$category]['db']."'";
        return( $this->GetKfrcS( $cond, $raKfParms, $eMode ) );
    }
    function GetKfrcS_CatType( $category, $type, $cond = "", $raKfParms = array(), $eMode = "" )
    {
        if( $cond )  $cond = "($cond) AND ";
        $cond .= "category='".$this->raCategories[$category]['db']."' AND type='".addslashes($type)."'";
        return( $this->GetKfrcS( $cond, $raKfParms, $eMode ) );
    }
    function GetKfrS_Key( $k, $eMode = "" )
    {
        if( !$eMode )  $eMode = $this->eReportMode;

        $kfr = $this->kfrelS->GetRecordFromDBKey( $k );
        if( $eMode != 'EDIT' ) {
            if( $kfr && ($kfr->Value('bSkip') || $kfr->Value('bDelete')) )  $kfr = NULL;
        }
        return( $kfr );
    }

    private function _initKFRel( $uid )
    {
        $kfrelDef_SEDCurrSeeds =
            array( "ver" => 2,
                   "Tables"=>array( "S" => array( "Table" => 'seeds.sed_curr_seeds',
                                                  "Fields" => "Auto" ) ) );
        $kfrelDef_SEDCurrGrowers =
            array( "Tables"=>array( array( "Table" => 'seeds.sed_curr_growers',
                                           "Fields" => "Auto" )));
// TODO: instead of requiring the cursor to be created with S.mbr_id=G.mbr_id, can we add a Condition section to this def
        $kfrelDef_SEDCurrSeedsXGrowers =    // Need to create cursor with S.mbr_id=G.mbr_id
            array( "Tables"=>array( array( "Table" => 'seeds.sed_curr_seeds',
                                           "Type" => "Base",
                                           "Alias" => "S",
                                           "Fields" => "Auto" ),
                                    array( "Table"=> 'seeds.sed_curr_growers',
                                           "Type" => "Parent",
                                           "Alias" => "G",
                                           "Fields" => "Auto" )));
        $kfrelDef_SEDCurrGrowersXContacts =    // Need to create cursor with G.mbr_id=M._key  (and of course it will only work with kfdb2)
            array( "Tables"=>array( array( "Table" => 'seeds.sed_curr_growers',
                                           "Type" => "Base",
                                           "Alias" => "G",
                                           "Fields" => "Auto" ),
                                    array( "Table"=> 'seeds2.mbr_contacts',
                                           "Type" => "Related",
                                           "Alias" => "M",
                                           "Fields" => array( array("col"=>"firstname",       "type"=>"S"),
                                                              array("col"=>"lastname",        "type"=>"S"),
                                                              array("col"=>"firstname2",      "type"=>"S"),
                                                              array("col"=>"lastname2",       "type"=>"S"),
                                                              array("col"=>"company",         "type"=>"S"),
                                                              array("col"=>"dept",            "type"=>"S"),
                                                              array("col"=>"address",         "type"=>"S"),
                                                              array("col"=>"city",            "type"=>"S"),
                                                              array("col"=>"province",        "type"=>"S"),
                                                              array("col"=>"postcode",        "type"=>"S"),
                                                              array("col"=>"country",         "type"=>"S"),
                                                              array("col"=>"phone",           "type"=>"S"),
                                                              array("col"=>"email",           "type"=>"S"),
                                                              array("col"=>"lang",            "type"=>"S"),
                                                              array("col"=>"expires",         "type"=>"S") ) ),
                                            ) );

        $raParms = array( 'logfile' => SITE_LOG_ROOT."sed.log" );
        $this->kfrelG   = new KeyFrameRelation( $this->kfdb, $kfrelDef_SEDCurrGrowers,       $uid, $raParms );
        $this->kfrelS   = new KeyFrameRelation( $this->kfdb, $kfrelDef_SEDCurrSeeds,         $uid, $raParms );
        $this->kfrelSxG = new KeyFrameRelation( $this->kfdb, $kfrelDef_SEDCurrSeedsXGrowers, $uid, $raParms );

        // Kluge: normal object design would put this in the override SEDOffice::_initKFRel, but it's so much easier to manage the code here.
        if( isset($this->kfdb2) ) {  // SEDOffice will have this
            $this->kfrelGxM = new KeyFrameRelation( $this->kfdb2, $kfrelDef_SEDCurrGrowersXContacts, $uid, $raParms );
        }
    }
}

?>
