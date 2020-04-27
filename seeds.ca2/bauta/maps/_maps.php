<?php

// figure out the right categories and choose the right stars for the categories
// resume geocoding
// only show the stars that appear on the map
// category filtering

include_once( SEEDCORE."SEEDMetaTable.php" );


class BautaMap
{
    private $oApp;
    public  $oTable;
    public  $kTable;

    public $raCategories = array(
        'trials'          => array( 'label' => "Seed Production Trials",          'colour'=>'green',  'markerlabel'=>'R', 'icon'=>"http://www.seeds.ca/i/img/map/star-green-16.png" ),
        'ar-ppb'          => array( 'label' => "Applied Research - PPB",          'colour'=>'purple', 'markerlabel'=>'A', 'icon'=>"http://www.seeds.ca/i/img/map/star-purple-16.png" ),
        'ar-carrot'       => array( 'label' => "Applied Research - Carrot",       'colour'=>'purple', 'markerlabel'=>'A', 'icon'=>"http://www.seeds.ca/i/img/map/star-purple-16.png" ),
        'ar-consult'      => array( 'label' => "Applied Research - Consult",      'colour'=>'purple', 'markerlabel'=>'A', 'icon'=>"http://www.seeds.ca/i/img/map/star-purple-16.png" ),
        'ar-consult'      => array( 'label' => "Applied Research - Consult",      'colour'=>'purple', 'markerlabel'=>'A', 'icon'=>"http://www.seeds.ca/i/img/map/star-purple-16.png" ),
        'ar-commongarden' => array( 'label' => "Applied Research - Common Garden",'colour'=>'purple', 'markerlabel'=>'A', 'icon'=>"http://www.seeds.ca/i/img/map/star-purple-16.png" ),
        'growout'         => array( 'label' => "Seed Grow-outs",                  'colour'=>'green',  'markerlabel'=>'R', 'icon'=>"http://www.seeds.ca/i/img/map/star-green-16.png" ),
        'training'        => array( 'label' => "Training",                        'colour'=>'yellow', 'markerlabel'=>'T', 'icon'=>"http://www.seeds.ca/i/img/map/star-yellow-16.png" ),
        'training-intern' => array( 'label' => "Training - Internship",           'colour'=>'yellow', 'markerlabel'=>'T', 'icon'=>"http://www.seeds.ca/i/img/map/star-yellow-16.png" ),
        'ss'              => array( 'label' => "Training - Seed Exchange Grants", 'colour'=>'yellow', 'markerlabel'=>'T', 'icon'=>"http://www.seeds.ca/i/img/map/star-yellow-16.png" ),
        'pa'              => array( 'label' => "Public Access projects",          'colour'=>'orange', 'markerlabel'=>'P', 'icon'=>"http://www.seeds.ca/i/img/map/star-orange-16.png" ),
        'sff'             => array( 'label' => "Seed Facilitation Fund projects", 'colour'=>'blue',   'markerlabel'=>'S', 'icon'=>"http://www.seeds.ca/i/img/map/star-blue-16.png" ),
        'grant-cb'        => array( 'label' => "Capacity Building Grants",        'colour'=>'blue',   'markerlabel'=>'S', 'icon'=>"http://www.seeds.ca/i/img/map/star-blue-16.png" ),
        'seed-collection' => array( 'label' => "Seed Collections",                'colour'=>'orange', 'markerlabel'=>'P', 'icon'=>"http://www.seeds.ca/i/img/map/star-orange-16.png" ),
        'hub'             => array( 'label' => "Coordination Hubs",               'colour'=>'red',    'markerlabel'=>'H', 'icon'=>"http://www.seeds.ca/i/img/map/dot-red-16.png" ),
        'event'           => array( 'label' => "Events",                          'colour'=>'yellow', 'markerlabel'=>'T', 'icon'=>"http://www.seeds.ca/i/img/map/star-yellow-16.png" ),
        'partner'         => array( 'label' => "Partners",                        'colour'=>'orange', 'markerlabel'=>'P', 'icon'=>"http://www.seeds.ca/i/img/map/star-orange-16.png" ),
        'misc'            => array( 'label' => "Miscellaneous",                   'colour'=>'blue',   'markerlabel'=>'O' ),
    );


    function __construct( SEEDAppDB $oApp, $uid )
    {
        $this->oApp = $oApp;

        $this->oTable = new SEEDMetaTable_TablesLite( $oApp, $uid );
        $this->kTable = $this->oTable->OpenTable( "BautaMaps1" );

    }

    function GetMarkers( $sheet = null, $cat = null )
    /***********************************************
        sheet is stored as k1
        cat is stored as k2
     */
    {
        $raTable = $this->oTable->GetRows( $this->kTable, ['k1'=>$sheet, 'k2'=>$cat, 'k1map'=>'sheet', 'k2map'=>'cat'] );
        $raOut = array();
        foreach( $raTable as $k => $ra ) {
            // $k is the key of the TablesLite row, $ra is the values with k1/k2 mapped to sheet/cat
            $raOut[$k] = $ra + ['k'=>$k, 'icon'=>@$this->raCategories[$vCat]['icon']];
        }
        return( $raOut );
    }

    function GetMarkersSheets( $cat = null )
    /***************************************
        sheet is stored as k1
        cat is stored as k2
     */
    {
        $raSheets = array();

        $raSheetNames = $this->oTable->EnumKeys( $this->kTable, 'k1' );
        foreach( $raSheetNames as $sheetName ) {
            $raSheets[$sheetName] = $this->GetMarkers( $sheetName, $cat );
        }
        return( $raSheets );
    }


    function StoreMarker( $kRow, $sheetName, $ra )
    /*********************************************
        sheet is stored as k1
        cat is stored as k2
     */
    {
        $this->oTable->PutRow( $this->kTable, $kRow, ['note'      => @$ra['note'],
                                                      'name'      => @$ra['name'],
                                                      'address'   => @$ra['address'],
                                                      'latitude'  => @$ra['latitude'],
                                                      'longitude' => @$ra['longitude'] ],
                               $sheetName, $ra['cat'] );
    }

/*



    function DrawMap( $raParms = array() )
    {
        $raMarkers = array();
        foreach( $this->raCategories as $k => $raCat ) {
            $lines = trim( $this->oBucket->GetStr( $this->nsBucket, $k ) );
            if( !$lines )  continue;

            $raLines = explode( "\n", $lines );
            $marker = "markers=color:{$raCat['colour']}|label:{$raCat['markerlabel']}";
            if( @$raCat['icon'] ) $marker .= "|icon:{$raCat['icon']}";
            foreach( $raLines as $raL ) {
                // raL is array( "name | address", ... )
                $raFields = @explode( "|", $raL );
                if( ($address = trim(@$raFields[1])) ) {
                    $marker .= "|".urlencode($address);
                }
            }
            $raMarkers[] = $marker;
        }

        $center = SEEDStd_ArraySmartVal( $raParms, 'center', array("Churchill MB") );
        $zoom = 3;
        $size = "600x400";
        $maptype = SEEDStd_ArraySmartVal( $raParms, 'maptype', array("satellite") );
        $sMarkers = (implode('&',$raMarkers));

        $sMapUrl = "http://maps.googleapis.com/maps/api/staticmap?center=$center&zoom=$zoom&size=$size&maptype=$maptype&$sMarkers";

        return( "<img src='$sMapUrl'/>" );
    }

    function GetMarkersRA()
    {
        $raMarkers = array();
        foreach( $this->raCategories as $k => $raCat ) {
            $lines = trim( $this->oBucket->GetStr( $this->nsBucket, $k ) );
            if( !$lines )  continue;

            $raLines = explode( "\n", $lines );

            $ra = array();
            foreach( $raLines as $raL ) {
                // raL is array( "name | address", ... )
                $ra['label'] = $raCat['markerlabel'];
                $ra['colour'] = $raCat['colour'];
                $ra['icon'] = @$raCat['icon'];

                $raFields = @explode( "|", $raL );

                $ra['title'] = count($raFields) ? $raFields[0] : "";

                if( count($raFields) > 2 ) {
                    $latlng = @explode(' ', trim($raFields[2] ) );
                    if( $latlng[0] && $latlng[1] ) {
                        $ra['latlng'] = $latlng;
                    }
                }
                $raMarkers[] = $ra;
            }
        }
        return( $raMarkers );
    }
*/

}
