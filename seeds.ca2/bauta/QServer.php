<?php
include_once( SEEDCOMMON."siteStart.php" );


class QServer
{
    private $kfdb;

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


    function __construct()
    {
        list($this->kfdb) = SiteStart();
    }

    function Search( $q )
    {
        $s = "";

        $raOut = array();
        if( ($dbc = $this->kfdb->CursorOpen("SELECT * FROM sl_sources WHERE name_en LIKE '%".addslashes($q)."%'")) ) {
            while( $ra = $this->kfdb->CursorFetch( $dbc ) ) {
                $raOut[] = SEEDStd_ArrayExpand( $ra, "<p><a href='{$_SERVER['PHP_SELF']}?qCode=s__[[_key]]'>[[name_en]]</a></p>" );
            }
        }
        if( count($raOut) ) {
            $s .= "<h3>Seed Companies</h3>"
                 ."<div style='margin: 0 0 20px 0'>"
                 .implode( " ", $raOut )
                 ."</div>";
        }

        return( $s );
    }

    function QObjFromCode( $qCode, &$qObj )
    /**************************************
        An item identified by qCode is being placed at the middle square. Fetch the whole qObj.
     */
    {
        $k = substr( $qCode,3 );
        $qType = substr( $qCode, 0, 3 );

        switch( $qType ) {
            case 's__':
                foreach( $this->rel_s__ as $r => $fn ) {
                    $qObj[$r] = call_user_func( array($this,$fn), $qCode );
                }
                break;
            case 'sw_':  $this->qObjSLSourceWhere( $k, $qObj );  break;

            case 'cv_':
                foreach( $this->rel_cv_ as $r => $fn ) {
                    $qObj[$r] = call_user_func( array($this,$fn), $qCode );
                }
                break;

        }
    }

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


    function QObjItemRelative( $qCode, $r )
    /**************************************
        An item of qCode is in the middle square. Return the related item r (A..I)
     */
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

    function qObjInit( $qCode )
    {
        $k = substr( $qCode,3 );
        $qType = substr( $qCode, 0, 3 );

        $raItem = $this->qObjEmpty();

        return( array( $qType, $k, $raItem ) );
    }

    function qObjEmpty()
    {
        $raItem = array( 'qCode'=>0, 'htmlSmall'=>'','html'=>'' );

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
            $s = SEEDStd_ArrayExpand( $ra,
                                      "<h2>[[name_en]]</h2>"
                                     ."<p>[[addr_en]]<br/>"
                                     ."[[city]] [[prov]] [[postcode]]</p>"
                                     ."<p>[[desc_en]]</p>" );
            $raItem['qCode'] = "s__$k";
            $raItem['html'] = $s;
            $raItem['htmlSmall'] = $ra['name_en'];
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
            $raItem['htmlSmall'] = "Locate ".$ra['name_en'];
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
            $raItem['htmlSmall'] = "Seeds available from ".$raSrc['name_en'];
            $raItem['html'] = "<h2>Seeds Available from ".$raSrc['name_en']."</h2>";

            if( ($dbc = $this->kfdb->CursorOpen( "SELECT * FROM sl_cv_sources WHERE fk_sl_sources='$k' ORDER BY osp,ocv" )) ) {
                $sp = '';
                while( $raCV = $this->kfdb->CursorFetch( $dbc ) ) {
                    if( $raCV['osp'] != $sp ) {
                        $sp = $raCV['osp'];
                        $s .= "<h3>$sp</h3>";
                    }
                    $s .= SEEDStd_ArrayExpand( $raCV, "<div><a href='{$_SERVER['PHP_SELF']}?qCode=cv_[[_key]]'>[[ocv]]</a></div>" );
                }
                $raItem['html'] .= $s;
            }
        }
        return( $raItem );
    }

    function qObjSourceClimate( $qCode )
    {
        list( $qType, $k, $raItem ) = $this->qObjInit( $qCode );

        $raSrc = $this->kfdb->QueryRA( "SELECT * FROM sl_sources WHERE _key='$k'" );
        if( @$raSrc['_key'] ) {
            $raItem['qCode'] = "scl$k";
            $raItem['htmlSmall'] = "Climate conditions near ".$raSrc['name_en'];
            $raItem['html'] = "<h2>Climate Conditions Near ".$raSrc['name_en']."</h2>";
        }
        return( $raItem );
    }

    function qObjSourceSoil( $qCode )
    {
        list( $qType, $k, $raItem ) = $this->qObjInit( $qCode );

        $raSrc = $this->kfdb->QueryRA( "SELECT * FROM sl_sources WHERE _key='$k'" );
        if( @$raSrc['_key'] ) {
            $raItem['qCode'] = "ssl$k";
            $raItem['htmlSmall'] = "Soil types near ".$raSrc['name_en'];
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
            $raItem['htmlSmall'] = SEEDStd_ArrayExpand( $raCV, "All about [[ocv]] [[osp]]" );
            $raItem['html'] = SEEDStd_ArrayExpand( $raCV, "<h2>Here's Everything we know about [[ocv]] [[osp]]</h2>"
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
            $raItem['htmlSmall'] = SEEDStd_ArrayExpand( $raCV, "Where can you get [[ocv]] [[osp]]" );
            $raItem['html'] = SEEDStd_ArrayExpand( $raCV, "<h2>Suppliers of [[ocv]] [[osp]]</h2>" );

            if( ($dbc = $this->kfdb->CursorOpen( "SELECT S._key as S__key,S.name_en as S_name_en "
                                                ."FROM sl_cv_sources C, sl_sources S WHERE S._key=C.fk_sl_sources AND "
                                                ." osp='".addslashes($raCV['osp'])."' and ocv='".addslashes($raCV['ocv'])."'" )) ) {
                while( $raS = $this->kfdb->CursorFetch( $dbc ) ) {
                    $s .= SEEDStd_ArrayExpand( $raS, "<p><a href='{$_SERVER['PHP_SELF']}?qCode=s__[[S__key]]'>[[S_name_en]]</a></p>" );
                }
                $raItem['html'] .= $s;
            }
        }
        return( $raItem );
    }

}