<?php

//  This uses htmlentities so accented chars in iso-8859/cp1252 don't break json_encode(), which can only handle utf-8.

include_once( STDINC."SEEDLocal.php" );
include_once( SEEDCOMMON."siteStart.php" );



class QServer
{
    private $kfdb;    // used by oQSSources, oQSCultivars, etc
    private $oL;      // used by oQSSources, oQSCultivars, etc

    private $oQSSources = NULL;
    private $oQSCultivars = NULL;
    private $oQSVarKluge = NULL;

    public $qObj = array(
                'A' => '',
                'B' => '',
                'C' => '',
                'D' => '',
                'E' => '',
                'F' => '',
                'G' => '',
                'H' => '',
                'I' => '',
            );

    private $raQCodesEmpty = array( 'A'=>0, 'B'=>0, 'C'=>0, 'D'=>0, 'E'=>0, 'F'=>0, 'G'=>0, 'H'=>0, 'I'=>0 );

    function __construct( $lang = 'EN' )
    {
        list($this->kfdb) = SiteStart();

        $dummyStrs = array();
        $this->oL = new SEEDLocal( $dummyStrs, $lang );

        $this->oQSSources   = new QServerSources( $this->kfdb, $this->oL );
        $this->oQSCultivars = new QServerCultivars( $this->kfdb, $this->oL );
        $this->oQSVarKluge = new QServerVarKluge( $this->kfdb, $this->oL );

        $strs = $this->LocalStrs();                 $this->oL->AddStrs( $strs );
        $strs = $this->oQSSources->LocalStrs();     $this->oL->AddStrs( $strs );
        $strs = $this->oQSCultivars->LocalStrs();   $this->oL->AddStrs( $strs );
    }

    function Search( $sQuery )
    {
        list($nSrc,$sSrc) = $this->oQSSources->Search( $sQuery );
        list($nCV,$sCV) = $this->oQSCultivars->Search( $sQuery );
        //list($nKl,$sKl,$raKlCV) = $this->oQSVarKluge->Search( $sQuery );

        $n = $nSrc + $nCV;

        if( $n ) {
            $s = "<h2>".$this->oL->S2( "[[Found _n_ match(es) for]]", array('n' => $n) )."<b>".SEEDCore_HSC($sQuery)."</b></h2>"
                .$sSrc
                .$sCV;
        } else {
            $s = "<h2>".$this->oL->S('No matches found for')." <span class='qSearchTerm'>".SEEDCore_HSC($sQuery)."</span></h2>"
                ."<br/>"
                .$this->oL->S("Search_instruction")
                .$this->searchControl();
        }

        return( $s );
    }


/*
    function QObjFromCode( $qCode, &$qObj )
    [**************************************
        An item identified by qCode is being placed at the middle square. Fetch the whole qObj.
     *]
    {
        $k = substr( $qCode,3 );
        $qType = substr( $qCode, 0, 3 );

        switch( $qType ) {
            case 's__':
                foreach( $this->rel_s__ as $r => $fn ) {
                    $qObj[$r] = call_user_func( array($this,$fn), $qCode );
                }
                break;
            case 'sw_':  $this->qObjSLSourceMap( $k, $qObj );  break;

            case 'cv_':
                foreach( $this->rel_cv_ as $r => $fn ) {
                    $qObj[$r] = call_user_func( array($this,$fn), $qCode );
                }
                break;

        }
    }
*/

/*
    function QFetchSEEDSlider( $qCode, $parms )
    [******************************************
        Fetch the qcodes, html, htmlsmall for nine squares of a SEEDSlider.

        qCode is the qCode for the middle square
        parms['exclude'] = letters A-I to exclude from the result

        return array( 'A' => array( 'qcode'     => qcode for box A
                                   // 'raQCodes'  => qcode of box A relative to the given qCode being at E,
                                    'html'      => full html for A (if it moves to the middle box
                                    'htmlsmall' => brief html for A
                                  ),
                      'B' => array( ...
                      ...
                      'E' => array( ...
                      ...
                    )
     *]
    {
        $k = substr( $qCode,3 );
        $qType = substr( $qCode, 0, 3 );

        $raQObj = array();

        switch( $qType ) {
            case 'ini':    $raFn = $this->rel_ini;    break;
            case 's__':    $raFn = $this->rel_s__;    break;
            case 'cv_':    $raFn = $this->rel_cv_;    break;
            // Test1 appends box letter to qcode on each click (always making new qcodes, and recording your click history)
            case 't1_':    $raFn = $this->rel_t1_;    break;
            // Test2 only appends box letter on new qcodes, so going "back" doesn't make a new qcode (requiress exclude parm)
            case 't2_':    $raFn = $this->rel_t2_;    break;

            default:       $raFn = NULL;              break;
        }
        if( $raFn ) {
            foreach( $raFn as $box => $fn ) {
                if( isset($parms['exclude'])  && strpos(@$parms['exclude'],$box) !== false ) {
                    continue;
                }

                $raQObj[$box] = call_user_func( array($this,$fn), $qCode, $box, $parms );
            }
        }
        return( $raQObj );
    }
*/

    function QFetchSEEDSlider2( $raQCodes, $parms )
    {
        $raQObj = array();

        return( $raQObj );
    }

    function QFetchSEEDSlider9( $qCode, $parms )
    /*******************************************
        Given a qcode for the middle box of a SEEDSlider, fetch data for the nine associated boxes (if they have content).
        i.e. get the raQCodes for the middle box, and return data for those qcodes + the given qcode
        It doesn't matter what order they are returned

        The raQCodes of the middle box are the same as the keys of the returned array, and that raQCode list is also contained
        within the data for the central qcode since it is included in the array too.
            array( qcode1 => array( 'raQCodes'  => array( 'A' => qcodeA, 'B' => qcodeB, ... ),
                                    'htmlsmall' => brief html for qcode1,
                                    'html'      => full html for qcode1 ),
                   qcode2 => array( ... )
                 )
     */
    {
        $raQObj = array();

        $raQCodes = $this->getRAQCodes( $qCode );
        list($htmlsmall,$html) = $this->getHtml2( $qCode );
        $raQObj[$qCode] = array( 'raQCodes'  => $raQCodes,
                                 'htmlsmall' => $htmlsmall,
                                 'html'      => $html );

        foreach( $raQCodes as $box => $q ) {
            if( !$q ) continue;

            $raQ = $this->getRAQCodes( $q );
            list($htmlsmall,$html) = $this->getHtml2( $q );
            $raQObj[$q] = array( 'raQCodes'  => $raQ,
                                 'htmlsmall' => $htmlsmall,
                                 'html'      => $html );
        }

        return( $raQObj );
    }

    private function getRAQCodes( $qCode )
    /*************************************
        For the given qcode in the central box of a SEEDSlider, return an array of box=>qcode for the surrounding boxes
     */
    {
        $raQCodes = array();

        switch( substr($qCode,0,1) ) {
            case 's':    return( $this->oQSSources->GetRAQCodes( $qCode ) );
            case 'c':    return( $this->oQSCultivars->GetRAQCodes( $qCode ) );
            case 'v':    return( $this->oQSCultivars->GetRAQCodes( $qCode ) );  // oQSVarKluge
            default:     break;
        }

        switch( substr($qCode,0,4) ) {
            case "t2__":
                $raQCodes = array( "A"=>'t2_',"B"=>'t2_',"C"=>'t2_',"D"=>'t2_',"E"=>'t2_',
                                   "F"=>'t2_',"G"=>'t2_',"H"=>'t2_',"I"=>'t2_');
                break;

            default:
            case "init":
            case "qs__":
                $raQCodes = $this->raQCodesEmpty;
                $raQCodes['A'] = 'iniA';
                $raQCodes['B'] = 'iniB';
                $raQCodes['C'] = 'iniC';
                $raQCodes['D'] = 'iniD';
                $raQCodes['F'] = 'iniF';
                $raQCodes['G'] = 'iniG';
                $raQCodes['H'] = 'iniH';
                $raQCodes['I'] = 'iniI';
                break;
        }

        return( $raQCodes );
    }

    private function getHtml2( $qCode )
    /**********************************
        Return the htmlsmall and html for the given qcode
     */
    {
        $htmlsmall = $html = "";

        switch( substr($qCode,0,1) ) {
            case 's':    return( $this->oQSSources->GetHTML( $qCode ) );
            case 'c':    return( $this->oQSCultivars->GetHTML( $qCode ) );
            case 'v':    return( $this->oQSCultivars->GetHTML( $qCode ) );  // oQSVarKluge
            default:     break;
        }

        switch( substr($qCode,0,4) ) {
            case "t2__":
                $htmlsmall = "small";
                $html = "large";
                break;

            case "qs__":
                $srch = substr( $qCode, 4 );
                $htmlsmall = "";
                $html = $this->Search( $srch );
                break;

            case "iniA":
                $htmlsmall = $this->drawIniHtmlsmall( "http://www.seeds.ca/bauta/logo/partners/usc-thumb.png",
                                                      "http://www.usc-canada.org" );
                break;
            case "iniB":
                $sLogo = "logo/esf-".($this->oL->GetLang() == 'EN'?'en':'fr').".png";
                $sLogoLink = $this->oL->GetLang() == 'EN' ? "http://www.seeds.ca/seedfinder" : "http://www.semences.ca/localisateurdesemences";
                $htmlsmall = $this->drawIniHtmlsmall( $sLogo, $sLogoLink );
                /*
                $sLink = $this->oL->GetLang() == 'EN' ? "http://www.seeds.ca/seedfinder" : "http://www.semences.ca/localisateurdesemences";
                $htmlsmall = "<table style='width:100%;height:100%'><tr valign='middle'><td>"
                            ."<a href='$sLink' target='_blank' style='text-decoration:none;'>"
                            ."<span style='font-weight:bold;font-size:10pt;'>".$this->oL->S('Ecological Seed Finder')."</span>"
                            ."</a>"
                            ."</td><td>"
                            ."<a href='$sLink' target='_blank' style='text-decoration:none;'>"
                            ."<img align='right' src='http://www.seeds.ca/bauta/logo/esf-thumb.png' style='width:40px;vertical-align:middle'/>"
                            ."</a>"
                            ."</td></tr></table>";
                */
                break;
            case "iniC":
                $htmlsmall = $this->drawIniHtmlsmall( "http://seeds.ca/seedliving/i/logo.png", "http://www.seedliving.ca" );
                //$html = "<img style='margin:0px auto' src='http://seeds.ca/seedliving/i/logo.png'/>"
                //       ."<p>Your Canadian source for buying, selling, and swapping homegrown seeds and plants.</p>";
                break;
            case "iniD":
                $sLogo = "http://www.seeds.ca/img/logo/logoA_h-".($this->oL->GetLang() == 'EN'?'en':'fr')."-750x.png";
                $sLogoLink = $this->oL->GetLang() == 'EN' ? "http://www.seeds.ca" : "http://www.semences.ca";
                $htmlsmall = $this->drawIniHtmlsmall( $sLogo, $sLogoLink );
                break;
            case "iniF":
                $htmlsmall = $this->drawIniHtmlsmall( "http://www.seeds.ca/bauta/logo/partners/acorn.png",
                                                      "http://www.acornorganic.org" );
                break;
            case "iniG":
                $htmlsmall = $this->drawIniHtmlsmall( "http://www.seeds.ca/bauta/logo/partners/farm_folk.png",
                                                      "http://www.farmfolkcityfolk.ca" );
                break;
            case "iniH":
                $htmlsmall = $this->drawIniHtmlsmall( "http://www.seeds.ca/bauta/logo/partners/organic_alberta.jpg",
                                                      "http://www.organicalberta.org/" );
                break;
            case "iniI":
                $htmlsmall = $this->drawIniHtmlsmall( "http://www.seeds.ca/bauta/logo/partners/everdale.jpg",
                                                      "http://www.everdale.org" );
                break;

            default:
            case "init":
                $htmlsmall = "";
                $html = $this->oL->S2('[[Main_splash]]')
                       .$this->searchControl();
                break;
        }

        return( array( $htmlsmall, $html ) );
    }

    private function drawIniHtmlsmall( $img, $link )
    {
        // somehow the <span> makes the vertical-align work, but you have to put that on both
        return( "<div style='width:100%;height:90%;'>"
               ."<a href='$link' target='_blank'>"
               ."<span style='display:inline-block;height:100%;vertical-align:middle'></span>"
               ."<img src='$img' style='max-width:100%;max-height:100%;vertical-align:middle;display:inline-block'/></a></div>" );
    }

    private function searchControl()
    {
        $s = "<input type='text' id='qSearch' name='qSearch' "
                ."onkeydown='if( event.keyCode == 13 ) document.getElementById(\"qSearchBtn\").click();'/>"
            ."<button id='qSearchBtn' onclick='SEEDSlider_Search($(\"#qSearch\").val());'>".$this->oL->S('Search')."</button>";
        return( $s );
    }

    // functions that provide Initial related qObj for SEEDSlider positions
    private $rel_ini = array(
        'A' => 'qObjEmpty',
        'B' => 'qObjInit',
        'C' => 'qObjEmpty',
        'D' => 'qObjInit',
        'E' => 'qObjInit',
        'F' => 'qObjInit',
        'G' => 'qObjEmpty',
        'H' => 'qObjInit',
        'I' => 'qObjEmpty',
    );

    // functions that provide Source related qObj for SEEDSlider positions
    private $rel_s__ = array(
        'A' => 'qObjSourceClimate',
        'B' => 'qObjEmpty',
        'C' => 'qObjEmpty',
        'D' => 'qObjSLSourceWhere',
        'E' => 'qObjSLSource',
        'F' => 'qObjSLSourceCV',
        'G' => 'qObjSourceSoil',
        'H' => 'qObjEmpty',
        'I' => 'qObjEmpty',
    );

    // functions that provide Cultivar related qObj for SEEDSlider positions
    private $rel_cv_ = array(
        'A' => 'qObjEmpty',
        'B' => 'qObjEmpty',
        'C' => 'qObjEmpty',
        'D' => 'qObjEmpty',
        'E' => 'qObjCultivar',
        'F' => 'qObjCultivarSrc',
        'G' => 'qObjEmpty',
        'H' => 'qObjEmpty',
        'I' => 'qObjEmpty',
    );

    // functions for testing
    private $rel_t1_ = array(
        'A' => 'qObjTest1',
        'B' => 'qObjTest1',
        'C' => 'qObjTest1',
        'D' => 'qObjTest1',
        'E' => 'qObjTest1',
        'F' => 'qObjTest1',
        'G' => 'qObjTest1',
        'H' => 'qObjTest1',
        'I' => 'qObjTest1',
    );
    private $rel_t2_ = array(
        'A' => 'qObjTest2',
        'B' => 'qObjTest2',
        'C' => 'qObjTest2',
        'D' => 'qObjTest2',
        'E' => 'qObjTest2',
        'F' => 'qObjTest2',
        'G' => 'qObjTest2',
        'H' => 'qObjTest2',
        'I' => 'qObjTest2',
    );

/*
    function QObjItemRelative( $qCode, $r )
    [**************************************
        An item of qCode is in the middle square. Return the related item r (A..I)
     *]
    {
        $k = substr( $qCode,3 );
        $qType = substr( $qCode, 0, 3 );

        $raItem = $this->qObjEmpty();

        if( $qType == 's__' ) {
            $raItem = call_user_func( array($this,$this->rel_s__[$r]), $qCode );
        }
        if( $qType == 'cv_' ) {
            $raItem = call_user_func( array($this,$this->rel_cv_[$r]), $qCode );
        }

        return( $raItem );
    }
*/

    private function qoCreate( $qCode )
    {
        $k = substr( $qCode,3 );
        $qType = substr( $qCode, 0, 3 );

        $raItem = $this->qObjEmpty();

        return( array( $qType, $k, $raItem ) );
    }

    function qObjEmpty()
    {
        $raItem = array( 'qCode'=>0, 'htmlsmall'=>'','html'=>'' );

        return( $raItem );
    }

    private function qObjInit( $qCode, $parms )
    {
        $k = substr( $qCode,3 );
        $qType = substr( $qCode, 0, 3 );

        $raItem = array();
        $raItem['qCode'] = $qCode;
        $raItem['raQCodes'] = array( 'A'=>'ini','B'=>'ini','C'=>'ini','D'=>'ini','E'=>'ini','F'=>'ini','G'=>'ini','H'=>'ini','I'=>'ini');
        $raItem['html'] = "Initialized big box";
        $raItem['htmlsmall'] = "Initial friend";

        return( $raItem );
    }

    function qObjTest1( $qCode, $box, $parms )
    {
        $q = $qCode.($box == 'E' ? "" : $box);
        $raItem = array();
        $raItem['qCode'] = $q;
        $raItem['htmlsmall'] = $q;
        $raItem['html'] = "<h2>$q</h2>";

        return( $raItem );
    }

    function qObjTest2( $qCode, $box, $parms )
    {
        $q = $qCode.($box == 'E' ? "" : $box);
        $raItem = array();
        $raItem['qCode'] = $q;
        $raItem['htmlsmall'] = $q;
        $raItem['html'] = "<h2>$q</h2>";

        return( $raItem );
    }

    function qObjSLSource( $qCode )
    {
        $k = substr( $qCode,3 );
        $qType = substr( $qCode, 0, 3 );

        $s = "";
        $raItem = $this->qObjEmpty();

        $ra = $this->kfdb->QueryRA( "SELECT * FROM sl_sources WHERE _key='$k'" );
        if( @$ra['_key'] ) {
            $s = SEEDCore_ArrayExpand( $ra,
                                      "<h2>[[name_en]]</h2>"
                                     ."<p>[[addr_en]]<br/>"
                                     ."[[city]] [[prov]] [[postcode]]</p>"
                                     ."<p>[[desc_en]]</p>" );
            $raItem['qCode'] = "s__$k";
            $raItem['html'] = $s;
            $raItem['htmlsmall'] = $ra['name_en'];
        }

        return( $raItem );
    }

    function qObjSLSourceWhere( $qCode )
    {
        $k = substr( $qCode,3 );
        $qType = substr( $qCode, 0, 3 );

        $s = "";
        $raItem = $this->qObjEmpty();

        $ra = $this->kfdb->QueryRA( "SELECT * FROM sl_sources WHERE _key='$k'" );
        if( @$ra['_key'] ) {
            $raItem['qCode'] = "sw_$k";
            $raItem['htmlsmall'] = "Locate ".$ra['name_en'];
            $raItem['html'] = "<h2>Imagine a map showing ".$ra['name_en']."</h2>";
        }
        return( $raItem );
    }

    function qObjSLSourceCV( $qCode )
    {
        $k = substr( $qCode,3 );
        $qType = substr( $qCode, 0, 3 );

        $s = "";
        $raItem = $this->qObjEmpty();

        $raSrc = $this->kfdb->QueryRA( "SELECT * FROM sl_sources WHERE _key='$k'" );
        if( @$raSrc['_key'] ) {
            $raItem['qCode'] = "scv$k";
            $raItem['htmlsmall'] = "Seeds available from ".$raSrc['name_en'];
            $raItem['html'] = "<h2>Seeds Available from ".$raSrc['name_en']."</h2>";

            if( ($dbc = $this->kfdb->CursorOpen( "SELECT * FROM sl_cv_sources WHERE fk_sl_sources='$k' ORDER BY osp,ocv" )) ) {
                $sp = '';
                while( $raCV = $this->kfdb->CursorFetch( $dbc ) ) {
                    if( $raCV['osp'] != $sp ) {
                        $sp = $raCV['osp'];
                        $s .= "<h3>$sp</h3>";
                    }
                    $s .= SEEDCore_ArrayExpand( $raCV, "<div><a href='{$_SERVER['PHP_SELF']}?qCode=cv_[[_key]]'>[[ocv]]</a></div>" );
                }
                $raItem['html'] .= $s;
            }
        }
        return( $raItem );
    }

    function qObjSourceClimate( $qCode )
    {
        list( $qType, $k, $raItem ) = $this->qoCreate( $qCode );

        $raSrc = $this->kfdb->QueryRA( "SELECT * FROM sl_sources WHERE _key='$k'" );
        if( @$raSrc['_key'] ) {
            $raItem['qCode'] = "scl$k";
            $raItem['htmlsmall'] = "Climate conditions near ".$raSrc['name_en'];
            $raItem['html'] = "<h2>Climate Conditions Near ".$raSrc['name_en']."</h2>";
        }
        return( $raItem );
    }

    function qObjSourceSoil( $qCode )
    {
        list( $qType, $k, $raItem ) = $this->qoCreate( $qCode );

        $raSrc = $this->kfdb->QueryRA( "SELECT * FROM sl_sources WHERE _key='$k'" );
        if( @$raSrc['_key'] ) {
            $raItem['qCode'] = "ssl$k";
            $raItem['htmlsmall'] = "Soil types near ".$raSrc['name_en'];
            $raItem['html'] = "<h2>Soil Types Near ".$raSrc['name_en']."</h2>";
        }
        return( $raItem );
    }



    function qObjCultivar( $qCode )
    {
        $s = "";

        $raCV = $this->kfdb->QueryRA( "SELECT * FROM sl_cv_sources WHERE _key='$k'" );  // should be looking up sl_pcv
        if( @$raCV['_key'] ) {
            $raItem['qCode'] = "cv_$k";
            $raItem['htmlsmall'] = SEEDCore_ArrayExpand( $raCV, "All about [[ocv]] [[osp]]" );
            $raItem['html'] = SEEDCore_ArrayExpand( $raCV, "<h2>Here's Everything we know about [[ocv]] [[osp]]</h2>"
                                                         ."<p>Come back soon, that info is around here someplace</p>"
                                                         ."<p>There will be pictures here.</p>"
                                                         ."<p>And all the Crop Description records from that other page</p>");
        }
        return( $raItem );
    }

    function qObjCultivarSrc( $qCode )
    {
        $k = substr( $qCode,3 );
        $qType = substr( $qCode, 0, 3 );

        $s = "";
        $raItem = $this->qObjEmpty();

        $raCV = $this->kfdb->QueryRA( "SELECT * FROM sl_cv_sources WHERE _key='$k'" );  // should be looking up sl_pcv
        if( @$raCV['_key'] ) {
            $raItem['qCode'] = "cv_$k";
            $raItem['htmlsmall'] = SEEDCore_ArrayExpand( $raCV, "Where can you get [[ocv]] [[osp]]" );
            $raItem['html'] = SEEDCore_ArrayExpand( $raCV, "<h2>Suppliers of [[ocv]] [[osp]]</h2>" );

            if( ($dbc = $this->kfdb->CursorOpen( "SELECT S._key as S__key,S.name_en as S_name_en "
                                                ."FROM sl_cv_sources C, sl_sources S WHERE S._key=C.fk_sl_sources AND "
                                                ." osp='".addslashes($raCV['osp'])."' and ocv='".addslashes($raCV['ocv'])."'" )) ) {
                while( $raS = $this->kfdb->CursorFetch( $dbc ) ) {
                    $s .= SEEDCore_ArrayExpand( $raS, "<p><a href='{$_SERVER['PHP_SELF']}?qCode=s__[[S__key]]'>[[S_name_en]]</a></p>" );
                }
                $raItem['html'] .= $s;
            }
        }
        return( $raItem );
    }

     function LocalStrs()
    {
        return( array(
                  "Ecological Seed Finder"
                      => array( "EN" => "Ecological Seed Finder",
                                "FR" => "Localisateur de semences &eacute;cologiques" ),
                  "Main_splash"
                      => array( "EN" => "<h2>Explore Canadian Seeds!</h2>"
                                       ."<p class='qPara'>This seed explorer is your window into Canadian seed security. Learn where to find your favourite seeds, "
                                       ."discover new varieties that grow well in your area, and explore the diversity of Canada's seed movement.</p>"
                                       ."[[Search_instruction]]",
                                "FR" => "<h2>Explorateur de semences canadiennes!</h2>"
                                       ."<p class='qPara'>Cet outil de recherche de semences est votre fen&ecirc;tre ouverte sur la s&eacute;curit&eacute; "
                                       ."des semences au Canada. Trouvez les semences de vos vari&eacute;t&eacute;s favorites, d&eacute;couvrez de nouvelles "
                                       ."vari&eacute;t&eacute;s poussant bien dans votre r&eacute;gion et explorez la diversit&eacute; du mouvement des "
                                       ."semences du Canada.</p>"
                                       ."[[Search_instruction]]" ),
                  "Search_instruction"
                      => array( "EN" => "<h3>Search</h3><p>for a seed variety, company, or place</p>",
                                "FR" => "<h3>Recherche</h3><p>pour une vari&eacute;t&eacute; de semence, une entreprise ou un lieu</p>" ),

                  "Search"
                      => array( "EN" => "Search", "FR" => "Recherche" ),

                  "Found _n_ match(es) for"
                      => array( "EN" => 'Found [[var:n]] [[Plural_es:match|$n]] for ', //".($n>1 ? "matches" : "match")." for ")
                                "FR" => 'Trouv&eacute; [[var:n]] [[Plural_s:r&eacute;sultat|$n]] pour ' ),

                  "No matches found for"
                      => array( "EN" => "No matches found for",
                                "FR" => "Trouv&eacute; aucun r&eacute;sultat pour" ),
        ) );
    }
}

class QServerBase
{
    protected $kfdb;
    protected $oL;

    protected $raQCodesEmpty = array( 'A'=>0, 'B'=>0, 'C'=>0, 'D'=>0, 'E'=>0, 'F'=>0, 'G'=>0, 'H'=>0, 'I'=>0 );

    public $raA2I = array( 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I' );

    protected function __construct( KeyFrameDB $kfdb, SEEDLocal $oL )
    {
        $this->kfdb = $kfdb;
        $this->oL = $oL;
    }

    function StartLink( $qCode, $label )
    {
        // color from bootstrap <a>
        return( "<span class='QSEEDStartlink' onclick='SEEDSlider_Start(\"$qCode\");'>$label</span>" );
    }

    protected function GetKlugeVKey( $kSp, $ocv )
    /********************************************
        v___ is a kluge for referencing a variety name that isn't in sl_pcv.
        The key is the min(_key) of sl_cv_sources that has the given kSp,ocv
     */
    {
        $kV = $this->kfdb->Query1( "SELECT MIN(_key) FROM sl_cv_sources WHERE fk_sl_species='$kSp' AND ocv='".addslashes($ocv)."'" );
        return( $kV );
    }

}


class QServerSources extends QServerBase
{
    function __construct( KeyFrameDB $kfdb, SEEDLocal $oL )
    {
        parent::__construct( $kfdb, $oL );
    }

    function Search( $sQuery )
    {
        $s = "";

        $raOut = array();
        if( ($dbc = $this->kfdb->CursorOpen("SELECT * FROM sl_sources WHERE _status=0 AND "
                                                                       ."(name_en    LIKE '%".addslashes($sQuery)."%' "
                                                                       ."OR addr_en LIKE '%".addslashes($sQuery)."%' "
                                                                       ."OR city    LIKE '%".addslashes($sQuery)."%' "
                                                                       ."OR desc_en LIKE '%".addslashes($sQuery)."%') ORDER BY name_en" )) ) {
            while( $ra = $this->kfdb->CursorFetch( $dbc ) ) {
                $raOut[] = "<p>".$this->StartLink( "s___".$ra['_key'], SEEDCore_HSC($ra['name_en']))."</p>";
            }
        }
        if( count($raOut) ) {
            $s .= "<h3>".$this->oL->S('Seed Companies')."</h3>"
                 ."<div style='margin: 0 0 20px 0'>"
                 .implode( " ", $raOut )
                 ."</div>";
        }

        return( array( count($raOut), $s ) );
    }

    function GetRAQCodes( $qCode )
    /*****************************
        For the given qcode in the central box of a SEEDSlider, return an array of box=>qcode for the surrounding boxes
     */
    {
        $ra = $this->raQCodesEmpty;
        $ra['E'] = $qCode;

        if( ($kSrc = intval( substr($qCode,4) )) ) {
            switch( substr($qCode,0,4) ) {
                case 's___':
                    $ra['B'] = 'scl_'.$kSrc;        // source climate
                    $ra['D'] = 'smap'.$kSrc;        // source where
                    $ra['F'] = 'scv_'.$kSrc;        // source cultivars
                    $ra['H'] = 'sso_'.$kSrc;        // source soil
                    break;
                case 'scl_':
                    $ra['G'] = 'smap'.$kSrc;
                    $ra['H'] = 's___'.$kSrc;
                    $ra['I'] = 'scv_'.$kSrc;
                    break;
                case 'smap':
                    $ra['C'] = 'scl_'.$kSrc;
                    $ra['F'] = 's___'.$kSrc;
                    $ra['I'] = 'sso_'.$kSrc;
                    break;
                case 'scv_':
                    $ra['A'] = 'scl_'.$kSrc;
                    $ra['D'] = 's___'.$kSrc;
                    $ra['G'] = 'sso_'.$kSrc;
                    break;
                case 'sso_':
                    $ra['A'] = 'smap'.$kSrc;
                    $ra['B'] = 's___'.$kSrc;
                    $ra['C'] = 'scv_'.$kSrc;
                    break;
                default:
                    break;
            }
        }

        return( $ra );
    }

    function GetHTML( $qCode )
    /*************************
        Return the htmlsmall and html for the given qcode
     */
    {
        $html = $htmlsmall = "";

        if( ($kSrc = intval( substr($qCode,4) )) ) {
            switch( substr($qCode,0,4) ) {
                case 's___':  return( $this->htmlSource( $kSrc ) );
                case 'scl_':  return( $this->htmlSourceClimate( $kSrc ) );
                case 'smap':  return( $this->htmlSourceMap( $kSrc ) );
                case 'scv_':  return( $this->htmlSourceCultivars( $kSrc ) );
                case 'sso_':  return( $this->htmlSourceSoil( $kSrc ) );
                default:     break;
            }
        }
        return( array( $htmlsmall, $html ) );
    }

    private function htmlSource( $kSrc )
    {
        $html = $htmlsmall = "";

        $ra = $this->kfdb->QueryRA( "SELECT * FROM sl_sources WHERE _key='$kSrc'" );
        if( @$ra['_key'] ) {
            $sDesc = ($this->oL->GetLang()=='FR' && @$ra['desc_fr']) ? $ra['desc_fr'] : $ra['desc_en'];
            $html = SEEDCore_ArrayExpand( $ra,
                                         "<h2>".SEEDCore_HSC($ra['name_en'])."</h2>"
                                        ."<p>".SEEDCore_HSC($ra['addr_en'])."<br/>"
                                        .SEEDCore_HSC($ra['city'])." [[prov]] [[postcode]]</p>"
                                        ."<p><a href='http://[[web]]' target='_blank'>[[web]]</a></p>"
                                        ."<p class='qPara'>".SEEDCore_HSC($sDesc)."</p>" );
            $htmlsmall = "<span class='qMainSm'>".SEEDCore_HSC($ra['name_en'])."</span>";
        }

        return( array( $htmlsmall, $html ) );
    }

    private function htmlSourceClimate( $kSrc )
    {
        $html = $htmlsmall = "";

        $raSrc = $this->kfdb->QueryRA( "SELECT * FROM sl_sources WHERE _key='$kSrc'" );
        if( @$raSrc['_key'] ) {
            $htmlsmall = $this->oL->S2("[[Climate conditions near _name_]]", array( 'name' => SEEDCore_HSC($raSrc['name_en']) ));
            $html = "<h2>$htmlsmall</h2><p>".$this->oL->S("Coming soon")."</p>";
        }

        return( array( $htmlsmall, $html ) );
    }

    private function htmlSourceMap( $kSrc )
    {
        $html = $htmlsmall = "";

        $ra = $this->kfdb->QueryRA( "SELECT * FROM sl_sources WHERE _key='$kSrc'" );
        if( @$ra['_key'] ) {
            $htmlsmall = $this->oL->S2("[[Map for _name_]]", array( 'name' => SEEDCore_HSC($ra['name_en']) ));
            $addr = $ra['addr_en']." ".$ra['city']." ".$ra['prov']." ".$ra['country'];
            $mapUrl = "https://www.google.com/maps?t=m&amp;q=".urlencode($addr)."&amp;ie=UTF8&amp;z=12";

            /* To put three rows in a container such that the top and bottom contract to their content and the middle
             * row expands to fill the rest of the space, there appear to be two ways:
             *
             * 1) Make all absolute position divs, top with top:0;height:H1;, bottom with bottom:0;height:H3; and
             *    middle with top:H1;bottom:H3;
             *    This requires us to know the absolute heights (in this case we know that for the bottom row only)
             * 2) Use a table. Stackoverflow says "This is exactly why we shouldn't hassle people who use tables for layout".
             *    There are css properties to make divs act like table rows, which is supposed to be the same thing, but
             *    attempts to use the height tactics specified for those css seem to work with div but not with
             *    actual table elements.
             */
            $html =
                "<div class='QSEEDMapContainer'>"
                    ."<div class='QSEEDMapAutoHeight'>"
                        ."<h4>".SEEDCore_HSC($ra['name_en'])."</h4>".SEEDCore_HSC($addr)
                    ."</div>"
                    ."<div class='QSEEDMapRemainingHeight'>"
                        ."<iframe width='100%' height='100%' frameborder='0' scrolling='no' marginheight='0' marginwidth='0' "
                        ." src='$mapUrl&amp;output=embed'></iframe>"
                    ."</div>"
                    ."<div  class='QSEEDMapAutoHeight' style='width:100%;margin:auto 0px;'>"
                        ."<small><a target='_blank' href='$mapUrl&amp;source=embed' style='color:#0000FF;text-align:left'>".$this->oL->S('View Larger Map')."</a></small>"
                   ."</div>"
               ."</div>";


            // this didn't work
            $html1 =
                "<table style='height:100%;width:100%;border:none;'>"
               ."<tr><td style='height:auto'>"
                   ."<h4>".$ra['name_en']."</h4>".$addr
               ."</td></tr>"
               ."<tr style='height:100%'><td style='height:100%'>"
                   ."<div style='border:1px solid orange;'>"
                   ."<iframe width='100%' height='100%' frameborder='0' scrolling='no' marginheight='0' marginwidth='0' "
                   ." src='$mapUrl&amp;output=embed'></iframe>"
                   ."</div>"
               ."</td></tr>"
               ."<tr><td style='height:auto'>"
                   ."<div style='height:auto;width:100%;margin:auto 0px;'>"
                   ."<small><a target='_blank' href='$mapUrl&amp;source=embed' style='color:#0000FF;text-align:left'>".$this->oL->S('View Larger Map')."</a></small>"
                   ."</div>"
               ."</td></tr>"
               ."</table>";
        }

        return( array( $htmlsmall, $html ) );
    }

    private function htmlSourceCultivars( $kSrc )
    {
        $html = $htmlsmall = "scv";

        $raSrc = $this->kfdb->QueryRA( "SELECT * FROM sl_sources WHERE _key='$kSrc'" );
        if( @$raSrc['_key'] ) {
            $htmlsmall = $this->oL->S2("[[Seeds available from _name_]]", array( 'name' => SEEDCore_HSC($raSrc['name_en']) ));
            $html = "<h2>$htmlsmall</h2>";

            if( ($dbc = $this->kfdb->CursorOpen( "SELECT * FROM sl_cv_sources WHERE _status=0 AND fk_sl_sources='$kSrc' ORDER BY osp,ocv" )) ) {
                $sp = '';
                while( $raCVS = $this->kfdb->CursorFetch( $dbc ) ) {
                    if( $raCVS['osp'] != $sp ) {
                        $sp = $raCVS['osp'];
                        $html .= "<h3>$sp</h3>";
                    }
                    if( $raCVS['fk_sl_pcv'] ) {
                        $html .= "<div>".$this->StartLink( "c___".$raCVS['fk_sl_pcv'], SEEDCore_HSC($raCVS['ocv']))."</div>";
                    } else {
                        /* This name is not known in sl_pcv so kluge the link using the min(_key) of sl_cv_sources with that name
                         */
                        if( $raCVS['fk_sl_species'] ) {
                            if( ($kV = $this->GetKlugeVKey( $raCVS['fk_sl_species'], $raCVS['ocv'])) ) {
                                $html .= "<div>".$this->StartLink( "v___".$kV, SEEDCore_HSC($raCVS['ocv']))."</div>";
                            }
                        }
                    }
                }
            }
        }

        return( array( $htmlsmall, $html ) );
    }

    private function htmlSourceSoil( $kSrc )
    {
        $html = $htmlsmall = "";

        $raSrc = $this->kfdb->QueryRA( "SELECT * FROM sl_sources WHERE _key='$kSrc'" );
        if( @$raSrc['_key'] ) {
            $htmlsmall = $this->oL->S2("[[Soil types near _name_]]", array( 'name' => SEEDCore_HSC($raSrc['name_en']) ));
            $html = "<h2>$htmlsmall</h2><p>".$this->oL->S('Coming soon')."</p>";
        }

        return( array( $htmlsmall, $html ) );
    }

    public function LocalStrs()
    {
        return( array(
                  "Seed Companies"
                      => array( "EN" => "Seed Companies", "FR" => "Les sources commerciales" ),

                  "Seeds available from _name_"
                      => array( "EN" => "Seeds available from [[var:name]]", "FR" => "Les semences disponibles [[FR_de:\$name]]" ),

                  "Coming soon"
                      => array( "EN" => "Coming soon", "FR" => "&Agrave; venir" ),

                  "Climate conditions near _name_"
                      => array( "EN" => "Climate conditions near [[var:name]]", "FR" => "Les conditions climatiques pr&egrave;s [[FR_de:\$name]]" ),

                  "Soil types near _name_"
                      => array( "EN" => "Soil types near [[var:name]]", "FR" => "Les conditions du sol pr&egrave;s [[FR_de:\$name]]" ),

                  "Map for _name_"
                      => array( "EN" => "Map for [[var:name]]", "FR" => "Le carte pour [[var:name]]" ),

                  "View Larger Map"
                      => array( "EN" => "View Larger Map", "FR" => "Voir une carte plus grande" ),

        ) );
    }
}

class QServerCultivars extends QServerBase
{
    private $raCVInfo = array();

    function __construct( KeyFrameDB $kfdb, SEEDLocal $oL )
    {
        parent::__construct( $kfdb, $oL );
    }

    function Search( $sQuery )
    {
        $s = "";

        $raCV = array();

        $dbQ = addslashes($sQuery);
        $sSpNameCol = $this->oL->GetLang() == 'EN' ? "S.name_en" : "S.name_fr";

        /* Get all the properly-indexed psp:cvname names that match the query
         */
        if( ($dbc = $this->kfdb->CursorOpen( "SELECT S.psp as psp, P._key as kcv, P.name as name "
                                            ."FROM sl_pcv P JOIN sl_species S ON (P.fk_sl_species=S._key) "
                                            ."WHERE P._status=0 AND S._status=0 AND "
                                                  ."(P.name LIKE '%{$dbQ}%' OR "
                                                  ." S.psp LIKE '%{$dbQ}%' OR "
                                                  ." $sSpNameCol LIKE '%{$dbQ}%' OR "
                                                  ." S.name_bot LIKE '%{$dbQ}%') "
                                            ."ORDER BY S.psp,P.name")) ) {
            while( $ra = $this->kfdb->CursorFetch( $dbc ) ) {
                // store the results in an indexed array that can be searched by the sl_cv_sources kluge below
                $k = strtolower( $ra['psp']."|".$ra['name'] );
                $raCV[$k] = array( 'psp'=>$ra['psp'], 'kcv'=>$ra['kcv'], 'name'=>$ra['name'] );
            }
        }

        /* Kluge: get all the not-properly-indexed names in sl_cv_sources that aren't in the above list.
         *        sl_cv_sources must have fk_sl_species, but it can have ocv with fk_sl_pcv==0
         */
        if( ($dbc = $this->kfdb->CursorOpen( "SELECT S.psp as psp, C._key as kcvsrc, C.ocv as ocv "
                                            ."FROM sl_cv_sources C JOIN sl_species S ON (C.fk_sl_species=S._key) "
                                            ."WHERE C._status=0 AND S._status=0 AND "
                                                  ."fk_sl_pcv=0 AND "
                                                  ."(C.ocv LIKE '%{$dbQ}%' OR "
                                                  ." S.psp LIKE '%{$dbQ}%' OR "
                                                  ." $sSpNameCol LIKE '%{$dbQ}%' OR "
                                                  ." S.name_bot LIKE '%{$dbQ}%') "
                                                  ."ORDER BY ocv" )) ) {
            while( $ra = $this->kfdb->CursorFetch( $dbc ) ) {
                $k = strtolower( $ra['psp']."|".$ra['ocv'] );
                if( !isset($raCV[$k]) ) {
                    // add to the array to eliminate duplicates from this query
                     $raCV[$k] = array( 'psp'=>$ra['psp'], 'kcvsrc'=>$ra['kcvsrc'], 'ocv'=>$ra['ocv'] );
                }
            }
        }

        ksort($raCV);    // sort by psp|name

        if( count($raCV) ) {
            $s .= "<h3>".$this->oL->S('Cultivars')."</h3>"
                 ."<div style='margin: 0 0 20px 0'>";
            foreach( $raCV as $k => $ra ) {
                if( @$ra['kcv'] ) {
                    // found the name in sl_pcv
                    $s .= "<p>".$this->StartLink( "c___".$ra['kcv'], $ra['psp']." ".SEEDCore_HSC($ra['name']))."</p>";
                } else {
                    // found the name in sl_cv_sources
                    $s .= "<p>".$this->StartLink( "v___".$ra['kcvsrc'], $ra['psp']." ".SEEDCore_HSC($ra['ocv']))."</p>";
                }
            }
            $s .= "</div>";
        }

        return( array( count($raCV), $s ) );
    }

    function GetRAQCodes( $qCode )
    /*****************************
        For the given qcode in the central box of a SEEDSlider, return an array of box=>qcode for the surrounding boxes
     */
    {
        $ra = $this->raQCodesEmpty;
        $ra['E'] = $qCode;

$bVarKluge = false;
if( substr($qCode,0,1) == 'v' ) {
    $bVarKluge = true;
    $qCode = 'c'.substr($qCode,1);
}


        if( ($kCV = intval( substr($qCode,4) )) ) {
            switch( substr($qCode,0,4) ) {
                case 'c___':
                    $ra['A'] = 'cdsc'.$kCV;       // descriptors
                    $ra['B'] = 'cmsd'.$kCV;       // member seed directory
                    $ra['C'] = 'csrg'.$kCV;       // cultivar sources (graph)
                    $ra['D'] = 'csp_'.$kCV;       // other cultivars of this species
                    $ra['F'] = 'csrc'.$kCV;       // cultivar sources
                    $ra['H'] = 'ch__'.$kCV;       // historical references
                    $ra['I'] = 'csl_'.$kCV;       // Seed Library
                    break;

                case 'csrc':
                    $ra['A'] = 'cmsd'.$kCV;
                    $ra['B'] = 'csrg'.$kCV;
                    $ra['C'] = 'cdsc'.$kCV;
                    $ra['D'] = 'c___'.$kCV;
                    $ra['F'] = 'csp_'.$kCV;
                    $ra['G'] = 'ch__'.$kCV;
                    $ra['H'] = 'csl_'.$kCV;
                    break;

                case 'csrg':
                    $ra['A'] = 'ch__'.$kCV;
                    $ra['B'] = 'csl_'.$kCV;
                    $ra['D'] = 'cmsd'.$kCV;
                    $ra['F'] = 'cdsc'.$kCV;
                    $ra['G'] = 'c___'.$kCV;
                    $ra['H'] = 'csrc'.$kCV;
                    $ra['I'] = 'csp_'.$kCV;
                    break;

                case 'csp_':
                    $ra['A'] = 'csrg'.$kCV;
                    $ra['B'] = 'cdsc'.$kCV;
                    $ra['C'] = 'cmsd'.$kCV;
                    $ra['D'] = 'csrc'.$kCV;
                    $ra['F'] = 'c___'.$kCV;
                    $ra['G'] = 'csl_'.$kCV;
                    $ra['I'] = 'ch__'.$kCV;
                    break;

                case 'ch__':
                    $ra['A'] = 'csp_'.$kCV;
                    $ra['B'] = 'c___'.$kCV;
                    $ra['C'] = 'csrc'.$kCV;
                    $ra['F'] = 'csl_'.$kCV;
                    $ra['G'] = 'cdsc'.$kCV;
                    $ra['H'] = 'cmsd'.$kCV;
                    $ra['I'] = 'csrg'.$kCV;
                    break;

                case 'cmsd':
                    $ra['B'] = 'ch__'.$kCV;
                    $ra['C'] = 'csl_'.$kCV;
                    $ra['D'] = 'cdsc'.$kCV;
                    $ra['F'] = 'csrg'.$kCV;
                    $ra['G'] = 'csp_'.$kCV;
                    $ra['H'] = 'c___'.$kCV;
                    $ra['I'] = 'csrc'.$kCV;
                    break;

                case 'csl_':
                    $ra['A'] = 'c___'.$kCV;
                    $ra['B'] = 'csrc'.$kCV;
                    $ra['C'] = 'csp_'.$kCV;
                    $ra['D'] = 'ch__'.$kCV;
                    $ra['G'] = 'cmsd'.$kCV;
                    $ra['H'] = 'csrg'.$kCV;
                    $ra['I'] = 'cdsc'.$kCV;
                    break;

                case 'cdsc':
                    $ra['I'] = 'c___'.$kCV;
                    $ra['F'] = 'cmsd'.$kCV;
                    $ra['D'] = 'csrg'.$kCV;
                    $ra['H'] = 'csp_'.$kCV;
                    $ra['G'] = 'csrc'.$kCV;
                    $ra['C'] = 'ch__'.$kCV;
                    $ra['A'] = 'csl_'.$kCV;
                    break;

                default:
            }
        }

if( $bVarKluge ) {
    foreach( $this->raA2I as $k ) {
        if( $ra[$k] ) { $ra[$k] = 'v'.substr($ra[$k],1); }
    }
}
        return( $ra );
    }

    function GetHTML( $qCode )
    /*************************
        Return the htmlsmall and html for the given qcode
     */
    {
        $html = $htmlsmall = "";

        if( $this->getCVInfo($qCode) ) {
            $kCV = 0;
        //if( ($kCV = intval( substr($qCode,4) )) ) {
            switch( substr($qCode,0,4) ) {
                case 'c___': case 'v___':  return( $this->htmlCultivar( $kCV ) );
                case 'csrc': case 'vsrc':  return( $this->htmlCultivarSources( $kCV ) );
                case 'csrg': case 'vsrg':  return( $this->htmlCultivarSourcesGraph( $kCV ) );
                case 'csp_': case 'vsp_':  return( $this->htmlCultivarSpecies( $kCV ) );
                case 'ch__': case 'vh__':  return( $this->htmlCultivarHistory( $kCV ) );
                case 'cmsd': case 'vmsd':  return( $this->htmlCultivarMSD( $kCV ) );
                case 'csl_': case 'vsl_':  return( $this->htmlCultivarSL( $kCV ) );
                case 'cdsc': case 'vdsc':  return( $this->htmlCultivarDescriptors( $kCV ) );
                default:     break;
            }
        }
        return( array( $htmlsmall, $html ) );
    }

    private function getCVInfo( $qCode )
    {
        $this->raCVInfo = array();
        $bC = (substr($qCode,0,1)=='c');
        if( ($k = intval( substr($qCode,4) )) ) {
            if( $bC ) {
                $ra = $this->kfdb->QueryRA( "SELECT S.psp as psp, S._key as ksp, P.name as name "
                                           ."FROM sl_pcv P JOIN sl_species S ON (P.fk_sl_species=S._key) "
                                           ."WHERE P._status=0 AND S._status=0 AND "
                                           ."P._key='$k'" );
                $this->raCVInfo['kcv'] = $k;
                $this->raCVInfo['sp'] = $ra['psp'];
                $this->raCVInfo['cv'] = $ra['name'];
                $this->raCVInfo['ksp'] = $ra['ksp'];
            } else {
                $ra = $this->kfdb->QueryRA( "SELECT S.psp as psp, S._key as ksp, C.ocv as ocv "
                                           ."FROM sl_cv_sources C JOIN sl_species S ON (C.fk_sl_species=S._key) "
                                           ."WHERE C._status=0 AND S._status=0 AND "
                                           ."C._key='$k'" );
                $this->raCVInfo['kSLCVSources'] = $k;
                $this->raCVInfo['sp'] = $ra['psp'];
                $this->raCVInfo['cv'] = $ra['ocv'];
                $this->raCVInfo['ksp'] = $ra['ksp'];
            }
        }
        return( $k != 0 );
    }

    private function htmlCultivar( $kCV )
    {
        //$ra = $this->kfdb->QueryRA( "SELECT * FROM sl_pcv WHERE _key='$kCV'" );
        $ksp = $this->raCVInfo['ksp'];
        $sp = $this->raCVInfo['sp'];    $dbSp = addslashes($sp);
        $cv = $this->raCVInfo['cv'];    $dbCv = addslashes($cv);   $htmlCv = SEEDCore_HSC($cv);

        $htmlsmall = "<span class='qMainSm'>$sp : $htmlCv</span>";
        $html = "<h2>$htmlCv ($sp)</h2>";

        $nSeedbank = $this->kfdb->Query1( "SELECT count(*) FROM sl_cv_sources WHERE _status=0 AND fk_sl_sources in (1,2) AND "
                                         ."fk_sl_species='$ksp' AND ocv='$dbCv'" );
        $nCompany = $this->kfdb->Query1( "SELECT count(*) FROM sl_cv_sources WHERE _status=0 AND fk_sl_sources not in (1,2) AND "
                                         ."fk_sl_species='$ksp' AND ocv='$dbCv'" );
        $nMSD = $this->kfdb->Query1( "SELECT count(*) FROM sed_seeds WHERE _status=0 AND "
                                    ."type='$dbSp' AND variety='$dbCv'" );
        $nSL = isset($this->raCVInfo['kcv']) ? $this->kfdb->Query1( "SELECT count(*) FROM sl_accession WHERE _status=0 AND fk_sl_pcv='{$this->raCVInfo['kcv']}'" ) : 0;

        $html .= "<div style='border:1px solid #aaa;padding:15px;margin:15px;'>";
        if( $nCompany )   $html .= "<p>".$this->oL->S2('[[Sold by _n_ seed compan(ies)]]', array('n' => $nCompany))."</p>";
        if( $nSeedbank )  $html .= "<p>".$this->oL->S('Backed up in government seed banks')."</p>";
        if( $nMSD )       $html .= "<p>".$this->oL->S2('[[Grown by _n_ Canadian seed saver(s)]]', array('n' => $nMSD))."</p>";
        if( $nSL )        $html .= "<p>".$this->oL->S("Stored in Seeds of Diversity's Canadian Seed Library")."</p>";
        $html .= "</div>";

        return( array( $htmlsmall, $html ) );
    }

    private function htmlCultivarSources( $kCV )
    /*******************************************
        Show sources of the given cultivar
     */
    {
        //$ra = $this->kfdb->QueryRA( "SELECT * FROM sl_pcv WHERE _key='$kCV'" );
        $ksp = $this->raCVInfo['ksp'];
        $sp = $this->raCVInfo['sp'];    $dbSp = addslashes($sp);
        $cv = $this->raCVInfo['cv'];    $dbCv = addslashes($cv);  $htmlCv = SEEDCore_HSC($cv);

        $htmlsmall = $this->oL->S2("[[Suppliers of _cv_]]", array( 'cv' => $htmlCv ) );
        $html = "<h2>".$this->oL->S2("[[Suppliers of _cv_ (_sp_)]]", array('cv'=>$htmlCv, 'sp'=>$sp))."</h2>";

        $n = 0;
        if( ($dbc = $this->kfdb->CursorOpen( "SELECT S._key as _key,S.name_en as name_en FROM sl_sources S,sl_cv_sources C WHERE "
                                            ."S._status=0 AND C._status=0 AND "
                                            ."S._key=C.fk_sl_sources AND "
                                            ."C.fk_sl_species='$ksp' AND C.ocv='$dbCv'")) ) {
            while( $raS = $this->kfdb->CursorFetch( $dbc ) ) {
                $html .= "<p>".$this->StartLink( "s___".$raS['_key'], SEEDCore_HSC($raS['name_en']))."</p>";
                ++$n;
            }
        }
        if( !$n ) {
            $html .= "<p>".$this->oL->S2("[[No_commercial_sources _cv_ (_sp_)]]", array('cv'=>$htmlCv, 'sp'=>$sp))."</p>";
        }

        return( array( $htmlsmall, $html ) );
    }

    private function htmlCultivarSourcesGraph( $kCV )
    /************************************************
        Show a graph of sources of the given cultivar
     */
    {
        //$ra = $this->kfdb->QueryRA( "SELECT * FROM sl_pcv WHERE _key='$kCV'" );
        $ksp = $this->raCVInfo['ksp'];
        $sp = $this->raCVInfo['sp'];    $dbSp = addslashes($sp);
        //$cv = $this->raCVInfo['cv'];    $dbCv = addslashes($cv);

        $htmlsmall = $this->oL->S2("[[Suppliers of _sp_ Seeds]]", array('sp'=>$sp));// (graph)";
        $html = "<h2>$htmlsmall</h2>";

        $n = 0;
        if( ($dbc = $this->kfdb->CursorOpen( "SELECT S._key as _key,S.name_en as name_en FROM sl_sources S,sl_cv_sources C WHERE "
                                            ."S._key=C.fk_sl_sources AND "
                                            ."C.fk_sl_species='$ksp' GROUP BY 1 ORDER BY 2")) ) {
            while( $raS = $this->kfdb->CursorFetch( $dbc ) ) {
                $html .= "<p>".$this->StartLink( "s___".$raS['_key'], SEEDCore_HSC($raS['name_en']))."</p>";
                ++$n;
            }
        }
        if( !$n ) {
            $html .= $this->oL->S2("<p>[[No_commercial_sources _sp_]]</p>", array('sp'=>$sp));
        }


//        if( @$this->raCVInfo['kSp'] ) {
//            $html .= chart();
//            //$oChart = new SLSourcesCharts( $this->kfdb );
//            //$html .= $oChart->SourcesCommercial( $this->raCVInfo['kSp'], false, "EN", "" );
//        }

        return( array( $htmlsmall, $html ) );
    }

    private function htmlCultivarSpecies( $kCV )
    /*******************************************
        Show other cultivars of the same species
     */
    {
        //$ra = $this->kfdb->QueryRA( "SELECT * FROM sl_pcv WHERE _key='$kCV'" );
        $ksp = $this->raCVInfo['ksp'];
        $sp = $this->raCVInfo['sp'];    $dbSp = addslashes($sp);
        //$cv = $this->raCVInfo['cv'];    $dbCv = addslashes($cv);

        $htmlsmall = $this->oL->S2("[[Other Cultivars of _sp_]]", array( 'sp'=>$sp ));
        $html = "<h2>$htmlsmall</h2>";

        $n = 0;
        if( ($dbc = $this->kfdb->CursorOpen("SELECT _key,name FROM sl_pcv WHERE _status=0 AND fk_sl_species='$ksp' ORDER BY name")) ) {
            while( $raPCV = $this->kfdb->CursorFetch( $dbc ) ) {
                $html .= "<p>".$this->StartLink( "c___".$raPCV['_key'], SEEDCore_HSC($raPCV['name']))."</p>";
                ++$n;
            }
        }
        if( !$n ) {
            $html .= $this->oL->S2("<p>[[No_records_of_other_cultivars _sp_]]</p>", array('sp'=>$sp));
        }

        return( array( $htmlsmall, $html ) );
    }

    private function htmlCultivarHistory( $kCV )
    /*******************************************
        Show historical references of the given cultivar
     */
    {
        //$ra = $this->kfdb->QueryRA( "SELECT * FROM sl_pcv WHERE _key='$kCV'" );
        $sp = $this->raCVInfo['sp'];    $dbSp = addslashes($sp);
        $cv = $this->raCVInfo['cv'];    $dbCv = addslashes($cv);  $htmlCv = SEEDCore_HSC($cv);

        $htmlsmall = $this->oL->S2("[[History of _cv_]]", array('cv'=>$htmlCv));
        $html = $this->oL->S2("<h2>[[Historical References to _cv_]]</h2>", array('cv'=>$htmlCv));

        $n = 0;
        if( ($dbc = $this->kfdb->CursorOpen("SELECT * FROM hvd_catlist WHERE species='$dbSp' AND pname='$dbCv' ORDER BY refcode")) ) {
            while( $ra = $this->kfdb->CursorFetch( $dbc ) ) {
                $raRef = $this->kfdb->QueryRA( "SELECT * FROM hvd_refs WHERE refcode='{$ra['refcode']}'" );
                $html .= "<div>${raRef['shortname']}, ${raRef['refdate']}:<blockquote>${ra['description]']}</blockquote></div>";
                ++$n;
            }
        }
        if( !$n ) {
            $html .= $this->oL->S2("<p>[[No_historical_records]]</p>");
        }

        return( array( $htmlsmall, $html ) );
    }

    private function htmlCultivarMSD( $kCV )
    /***************************************
        Show listings from the MSD for the given cultivar
     */
    {
        //$ra = $this->kfdb->QueryRA( "SELECT * FROM sl_pcv WHERE _key='$kCV'" );
        $sp = $this->raCVInfo['sp'];    $dbSp = addslashes($sp);
        $cv = $this->raCVInfo['cv'];    $dbCv = addslashes($cv);  $htmlCv = SEEDCore_HSC($cv);

        $htmlsmall = $this->oL->S2( "[[What Growers Wrote About _cv_]]", array( 'cv' => $htmlCv ) );
        $html = $this->oL->S2( "<h2>[[What Seeds of Diversity's Growers Wrote About _cv_ (_sp_)]]</h2>", array( 'cv'=>$htmlCv, 'sp'=>$sp ) );

        $n = 0;
        if( ($dbc = $this->kfdb->CursorOpen( "SELECT description FROM sed_seeds WHERE _status=0 AND "
                                            ."type like '$dbSp%' AND variety='$dbCv'")) ) {
            while( $ra = $this->kfdb->CursorFetch( $dbc ) ) {
                $html .= "<p>".SEEDCore_HSC($ra['description'])."</p>";
                ++$n;
            }
        }
        if( !$n ) {
            $html .= $this->oL->S2("<p>[[No_msd_records _cv_]]</p>", array( 'cv'=>$htmlCv ));
        }

        return( array( $htmlsmall, $html ) );
    }

    private function htmlCultivarSL( $kCV )
    /**************************************
        Show details from the Seed Library for the given cultivar
     */
    {
        //$ra = $this->kfdb->QueryRA( "SELECT * FROM sl_pcv WHERE _key='$kCV'" );
        $sp = $this->raCVInfo['sp'];    $dbSp = addslashes($sp);
        $cv = $this->raCVInfo['cv'];    $dbCv = addslashes($cv);  $htmlCv = SEEDCore_HSC($cv);

        $htmlsmall = $this->oL->S2("[[_cv_ in the Canadian Seed Library]]", array( 'cv'=>$htmlCv ));
        $html = $this->oL->S2("<h2>[[Seeds of Diversity's Seed Library _cv_ (_sp_)]]</h2>", array( 'sp'=>$sp, 'cv'=>$htmlCv ));

        $raAcc = array();
        if( @$this->raCVInfo['kcv'] &&
            ($dbc = $this->kfdb->CursorOpen( "SELECT * FROM sl_accession "
                                            ."WHERE _status=0 AND NOT bDeAcc AND fk_sl_pcv='{$this->raCVInfo['kcv']}'" )) ) {
            while( $ra = $this->kfdb->CursorFetch($dbc) ) {
                $raAcc[] = $ra;
            }
        }

        if( count($raAcc) ) {
            $html .= "<div>We have ".count($raAcc)." sample".(count($raAcc)>1 ? "s":"")." of this variety in our collection.";
            foreach( $raAcc as $ra ) {
                // some of the amounts were weighed to a 1g precision, so show them as integers instead of X.000
                $sHave = ($ra['g_have']     < 1.0 ? $ra['g_have']     : intval($ra['g_have']));
                $sOrig = ($ra['g_original'] < 1.0 ? $ra['g_original'] : intval($ra['g_original']));
                $sPGRC = ($ra['g_pgrc']     < 1.0 ? $ra['g_pgrc']     : intval($ra['g_pgrc']));

                if( $sHave == 0.0 ) continue;

                $html .= "<table>"
                        ."<tr valign='top'><td><b>Accession no.</b></td><td style='padding-left:20px'><b>{$ra['_key']}</b></td><td>&nbsp;</td></tr>"
                        ."<tr valign='top'><td>&nbsp;</td><td>Quantity in storage</td><td style='padding-left:20px'>$sHave grams</td></tr>"
                        ."<tr valign='top'><td>&nbsp;</td><td>Back-up quantity</td><td style='padding-left:20px'>$sPGRC grams</td></tr>"
                        ."</table>";
            }
            $html .= "</div>";

        } else {
            $html .= $this->oL->S2("<p>[[No_SL_records]]</p>", array( 'cv'=>$htmlCv ));
        }

        return( array( $htmlsmall, $html ) );
    }

    function htmlCultivarDescriptors( $kCV )
    /***************************************
        Show crop descriptor data for the given cultivar
     */
    {
        $sp = $this->raCVInfo['sp'];    $dbSp = addslashes($sp);
        $cv = $this->raCVInfo['cv'];    $dbCv = addslashes($cv);  $htmlCv = SEEDCore_HSC($cv);

include_once( SEEDCOMMON."sl/sl_desc_report.php" );    // for htmlCultivarDescriptors

        $oDescUI = new SLDescReportUI( $this->kfdb, $this->oL->GetLang() );



        $htmlsmall = $this->oL->S2("[[Crop Description Records for _cv_]]", array( 'cv'=>$htmlCv ));
        $html = "<h2>$htmlsmall</h2>";

        $html .= "<style>"
                .".virecord-container       { width:85%;margin:0px auto;border:1px solid #888;background-color:white;padding:15px}"
                .".sldesc_VIRecord_table    { }"
                .".sldesc_VIRecord_table td { text-align:left; }"
                ."</style>";

        if( ($dbc = $this->kfdb->CursorOpen( "SELECT * FROM sl_varinst WHERE ".(@$this->raCVInfo['kcv'] ? "fk_sl_pcv='{$this->raCVInfo['kcv']}' OR " : "")." pname='$dbCv'")) )
        {
            while( $ra = $this->kfdb->CursorFetch( $dbc ) ) {
                $html .= "<div class='virecord-container'>"
                        .$oDescUI->DrawVIRecord( $ra['_key'], true )
                        ."</div>";
            }
        }




        return( array( $htmlsmall, $html ) );
    }

    public function LocalStrs()
    {
        return( array(
                  "Variety"
                      => array( "EN" => "Variety",
                                "FR" => "Vari&eacute;t&eacute;" ),
                  "Cultivars"
                      => array( "EN" => "Cultivars",
                                "FR" => "Vari&eacute;t&eacute;s" ),    // ?

                  "What Growers Wrote About _cv_"
                      => array( "EN" => "What Growers Wrote About [[var:cv]]",
                                "FR" => "Les producteurs &eacute;crit ceci [[FR_de:\$cv]]" ),

                  "What Seeds of Diversity's Growers Wrote About _cv_ (_sp_)"
                      => array( "EN" => "What Seeds of Diversity's Growers Wrote About [[var:cv]] ([[var:sp]])",
                                "FR" => "Les membres producteurs de Semences du patrimoine &eacute;crit ceci [[FR_de:\$cv]] ([[var:sp]])" ),

                  "No_msd_records _cv_"
                      => array( "EN" => "We don't have grower records for this variety. Do you grow [[var:cv]]? We'd love to learn more.",
                                "FR" => "Nous n'avons pas de dossiers aupr&egrave;s des producteurs pour cette vari&eacute;t&eacute;. Cultivez-vous [[var:cv]]? Nous aimerions en savoir plus." ),


            /* Status of cultivar
             */
                  "Sold by _n_ seed compan(ies)"
                      => array( "EN" => "Sold by [[var:n]] seed [[Plural_y:compan|\$n]]",    // $ is for SEEDTagParse, not for php
                                "FR" => "Vendu par [[var:n]] [[Plural_s:compagnie|\$n]] de semences" ),

                  "Backed up in government seed banks"
                      => array( "EN" => "Backed up in government seed banks",
                                "FR" => "Sauvegard&eacute;s dans les banques de semences gouvernementales" ),

                  "Grown by _n_ Canadian seed saver(s)"
                      => array( "EN" => "Grown by [[var:n]] Canadian seed [[Plural_s:saver|\$n]]",    // $ is for SEEDTagParse, not for php
                                "FR" => "Produite par [[var:n]] [[Plural_s:producteur|\$n]] de semences canadiennes" ),

                  "Stored in Seeds of Diversity's Canadian Seed Library"
                      => array( "EN" => "Stored in Seeds of Diversity's Canadian Seed Library",
                                "FR" => "Conserv&eacute; dans la biblioth&egrave;que des semences canadienne de Semences du patrimoine" ),

            /* Suppliers of cultivar / species
             */
                  "Suppliers of _cv_"
                      => array( "EN" => "Suppliers of [[var:cv]]",
                                "FR" => "Sources commerciales [[FR_de:\$cv]]" ),

                  "Suppliers of _cv_ (_sp_)"
                      => array( "EN" => "Suppliers of [[var:cv]] ([[var:sp]])",
                                "FR" => "Sources commerciales [[FR_de:\$cv]] ([[var:sp]])" ),

                  "No_commercial_sources _cv_ (_sp_)"
                      => array( "EN" => "We don't know of any commercial sources for [[var:cv]] ([[var:sp]])",
                                "FR" => "Nous ne connaissons pas de sources commerciales pour [[var:cv]] ([[var:sp]])" ),

                  "No_commercial_sources _sp_"
                      => array( "EN" => "We don't know of any commercial sources for [[var:sp]]",
                                "FR" => "Nous ne connaissons pas de sources commerciales pour [[var:sp]]" ),

                  "Suppliers of _sp_ Seeds"
                      => array( "EN" => "Suppliers of [[var:sp]] Seeds",
                                "FR" => "Sources commerciales de semences [[FR_de:\$sp]]" ),
            /* Other cultivars of the species
             */
                  "Other Cultivars of _sp_"
                      => array( "EN" => "Other Cultivars of [[var:sp]]",
                                "FR" => "Les autres vari&eacute;t&eacute;s [[FR_de:\$sp]]" ),

                  "No_records_of_other_cultivars _sp_"
                      => array( "EN", "We don't have records of other cultivars of [[var:sp]]",
                                "FR", "Nous n'avons pas les enregistrements d'autres cultivars [[FR_de:\$sp]]" ),

            /* Historical references
             */
                  "History of _cv_"
                      => array( "EN" => "History of [[var:cv]]",
                                "FR" => "L'histoire [[FR_de:\$cv]]" ),

                  "Historical References to _cv_"
                      => array( "EN" => "Historical References to [[var:cv]]",
                                "FR" => "Les r&eacute;f&eacute;rences historiques &agrave; [[var:cv]]" ),

                  "No_historical_records"
                      => array( "EN" => "We don't have historical records for this variety. Do you? We'd love to learn more.",
                                "FR" => "Nous n'avons pas de documents historiques pour cette vari&eacute;t&eacute;. Avez-vous? Nous aimerions en savoir plus." ),

            /* CSL
             */
                  "_cv_ in the Canadian Seed Library"
                      => array( "EN" => "[[var:cv]] in the Canadian Seed Library",
                                "FR" => "[[var:cv]] dans la biblioth&egrave;que des semences canadienne" ),

                  "Seeds of Diversity's Seed Library _cv_ (_sp_)"
                      => array( "EN" => "Seeds of Diversity's Seed Library : [[var:cv]] ([[var:sp]])",
                                "FR" => "La biblioth&egrave;que des semences de Semences du patrimoine : [[var:cv]] ([[var:sp]])" ),

                  "No_SL_records"
                      => array( "EN" => "We haven't added this variety to our Canadian Seed Library yet. You could adopt it with a donation!",
                                "FR" => "Nous n'avons pas encore ajout&eacute; cette vari&eacute;t&eacute; &agrave; notre biblioth&egrave;que des semences. "
                                       ."Vous pouvez l'adopter avec un don!" ),

            /* Descriptors
             */
                  "Crop Description Records for _cv_"
                      => array( "EN" => "Crop Description Records for [[var:cv]]",
                                "FR" => "Crop Description Records for [[var:cv]]" ),
        ) );
    }
}


class QServerVarKluge extends QServerBase
{
    function __construct( KeyFrameDB $kfdb, SEEDLocal $oL )
    {
        parent::__construct( $kfdb, $oL );
    }

    function Search( $sQuery )
    {
        $s = "";

        $raCV = array();
        $raOut = array();

        return( array( count($raOut), $s, $raCV ) );
    }

    function GetRAQCodes( $qCode )
    /*****************************
        For the given qcode in the central box of a SEEDSlider, return an array of box=>qcode for the surrounding boxes
     */
    {
        $ra = $this->raQCodesEmpty;
        $ra['E'] = $qCode;

        if( ($kCV = intval( substr($qCode,4) )) ) {
            switch( substr($qCode,0,4) ) {
                case 'v___':
                    $ra['F'] = 'vs__'.$kCV;       // cultivar sources
                    break;
                case 'vs__':
                    $ra['D'] = 'v___'.$kCV;       // cultivar sources
                    break;
                    default:
            }
        }
        return( $ra );
    }

    function GetHTML( $qCode )
    /*************************
        Return the htmlsmall and html for the given qcode
     */
    {
        $html = $htmlsmall = "";

        if( ($kCV = intval( substr($qCode,4) )) ) {
            switch( substr($qCode,0,4) ) {
                case 'v___':  return( $this->htmlVarKluge( $kCV ) );
                case 'vs__':  return( $this->htmlVarKlugeSources( $kCV ) );
                default:     break;
            }
        }
        return( array( $htmlsmall, $html ) );
    }

    private function htmlVarKluge( $kV )
    {
        $ra = $this->kfdb->QueryRA( "SELECT * FROM sl_cv_sources WHERE _key='$kV'" );
        $htmlCv = SEEDCore_HSC($ra['ocv']);
        $psp = $ra['fk_sl_species'] ? $this->kfdb->Query1( "SELECT psp FROM sl_species WHERE _key='${ra['fk_sl_species']}'" ) : "";

        $htmlsmall = "<span class='qMainSm'>$psp : $htmlCv</span>";
        $html = "<h2>$htmlCv ($psp)</h2>";

        return( array( $htmlsmall, $html ) );
    }

    private function htmlVarKlugeSources( $kV )
    {
        $ra = $this->kfdb->QueryRA( "SELECT * FROM sl_cv_sources WHERE _key='$kV'" );
        $htmlCv = SEEDCore_HSC($ra['ocv']);
        $psp = $ra['fk_sl_species'] ? $this->kfdb->Query1( "SELECT psp FROM sl_species WHERE _key='${ra['fk_sl_species']}'" ) : "";

        $htmlsmall = "Sources of $htmlCv";
        $html = "<h2>Sources of $htmlCv ($psp)</h2>";

        return( array( $htmlsmall, $html ) );
    }
}

function chart()
{return( "" );
    $raParms = array( 'title' => "My Chart",
                      'cols'  => array( "Label1" => "string", "Label2" => 'number' ),
                      'rows'  => array( array( "'One'", 10 ),
                                        array( "'Two'", 20 ) )
    );
    return( "<DIV style='height:400px;width:450'>".GoogleChart( $raParms )."</DIV>" );
}

function GoogleChart_X( $raParms )
{
    // optional
    $sChartType = (@$raParms['type'] == 'bar' ? "BarChart" : (@$raParms['type'] == 'column' ? "ColumnChart" : "PieChart"));
    $sChartDiv = SEEDStd_ArraySmartVal( $raParms, 'chart_div', array('chart_div'), false );
    $nWidth = SEEDStd_ArraySmartVal( $raParms, 'width', array(400), false );
    $nHeight = SEEDStd_ArraySmartVal( $raParms, 'width', array(300), false );

    // required
    $sTitle = @$raParms['title'];
    $raCols = $raParms['cols'];       // array( "Label1" => 'string', "Label2" => 'number' ),

    $raRows = $raParms['rows'];       // array(
                                      //     array( "'One'", 10 ),   -- note ' around string values
                                      //     array( "'Two'", 20 ),

    $sAddRows = "";
    $raAddRows = array();
    foreach( $raRows as $ra ) { $raAddRows[] = "[".implode(",", $ra)."]"; }
    $sAddRows = implode( ",", $raAddRows );


    $sJS = ""//"<script type='text/javascript' src='https://www.google.com/jsapi'></script>"
          ."<script type='text/javascript'>"
          ."google.load('visualization', '1.0', {'packages':['corechart']});"
          ."google.setOnLoadCallback(drawChart);"
          ."function drawChart() {"
              ."var data = new google.visualization.DataTable();";
              foreach( $raCols as $label => $type ) {
                  $sJS .= "data.addColumn('$type', '$label');";
              }
    $sJS .=   "data.addRows(["
              .$sAddRows
              ."]);"
              ."var options = {"
                  ."'title':'$sTitle',"
                  ."'width':$nWidth,"
                  ."'height':$nHeight"
                  .(@$raParms['maxH'] ? ",'hAxis.minValue':0,'hAxis.maxValue':{$raParms['maxH']}" : "")
                  ."};"
              ."var chart = new google.visualization.$sChartType(document.getElementById('$sChartDiv'));"
              ."chart.draw(data, options);"
          ."}"
          ."</script>";

    $sJS .= "<div id='chart_div'></div>";

    return( $sJS );
}

