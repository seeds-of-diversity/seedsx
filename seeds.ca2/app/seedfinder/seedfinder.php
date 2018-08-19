<?php


class SeedFinder extends Console01_Worker1
{
    private $raRegions = array(
        0    => array( "- All of Canada -", "" ),
        'bc' => array( "B.C.", "C.-B." ),
        'pr' => array( "Prairies", "" ),
        'on' => array( "Ontario" ),
        'qc' => array( "Quebec", "Qu&eacute;bec" ),
        'at' => array( "Atlantic", "" )
    );

    function __construct( Console01 $oC, KeyFrameDB $kfdb, SEEDSession $sess, $lang )
    {
        parent::__construct( $oC, $kfdb, $sess, $lang );
    }

    function GetRegionOpts()
    {
        $raRegionOpts = array();

        foreach( $this->raRegions as $k => $raR ) {
            $raRegionOpts[$raR[$this->lang=='EN'?0:1]] = $k;
        }

        return( $raRegionOpts );
    }
}

?>