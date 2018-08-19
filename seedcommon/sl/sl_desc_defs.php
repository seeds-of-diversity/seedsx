<?php
/* Seed Library: desc_defs
 *
 * Copyright 2012 Seeds of Diversity Canada
 *
 * Descriptor definitions
 */

include_once( "desc/apple_defs.php" );
include_once( "desc/bean_defs.php" );
include_once( "desc/garlic_defs.php" );
include_once( "desc/lettuce_defs.php" );
include_once( "desc/onion_defs.php" );
include_once( "desc/pea_defs.php" );
include_once( "desc/pepper_defs.php" );
include_once( "desc/potato_defs.php" );
include_once( "desc/squash_defs.php" );
include_once( "desc/tomato_defs.php" );
include_once( "desc/common_defs.php" );


class SL_DescDefs
/****************
 */
{
    private $oSLDescDB;
    private $oDescDB;
    private $raDefs = array();

    public $raSpecies = array( 'apple',
//                               'barley',
                               'bean',
                               'garlic',
                               'lettuce',
                               'onion',
                               'pea',
                               'pepper',
                               'potato',
                               'squash',
                               'tomato',
  //                             'wheat'
                              );


    function __construct( SL_DescDB $oDescDB )
    {
        $this->oSLDescDB = $oDescDB;

        $this->oDescDB_Cfg = new SLDescDB_Cfg( $oDescDB->kfdb, $oDescDB->uid );     // added this in a klugey way

        $this->raDefs['apple'] = SLDescDefsApple::$raDefsApple;
        $this->raDefs['bean'] = SLDescDefsBean::$raDefsBean;
        $this->raDefs['garlic'] = SLDescDefsGarlic::$raDefsGarlic;
        $this->raDefs['lettuce'] = SLDescDefsLettuce::$raDefsLettuce;
        $this->raDefs['onion'] = SLDescDefsOnion::$raDefsOnion;
        $this->raDefs['pea'] = SLDescDefsPea::$raDefsPea;
        $this->raDefs['pepper'] = SLDescDefsPepper::$raDefsPepper;
        $this->raDefs['potato'] = SLDescDefsPotato::$raDefsPotato;
        $this->raDefs['squash'] = SLDescDefsSquash::$raDefsSquash;
        $this->raDefs['tomato'] = SLDescDefsTomato::$raDefsTomato;

        $this->raDefs['brassica'] = $this->getDefsFromDB( 'brassica' );
        $this->raDefs['corn']     = $this->getDefsFromDB( 'corn' );
    }

    private function getDefsFromDB( $species )
    {
        $defs = array();
        if( ($kfrTags = $this->oDescDB_Cfg->GetKfrelCfgTags()->CreateRecordCursor( "tag LIKE '$species%'" )) ) {
        while( $kfrTags->CursorFetch() ) {
            $tag = $kfrTags->value('tag');
            $defs[$tag] = array( 'l_EN'=>$kfrTags->Value('label_en'),
                                 'l_FR'=>$kfrTags->Value('label_fr'),
                                 'q_EN'=>$kfrTags->Value('q_en'),
                                 'q_FR'=>$kfrTags->Value('q_fr') );
            $raM = $this->oDescDB_Cfg->GetKfrelCfgM()->GetRecordSet( "tag='$tag'" );
            if( count($raM) ) {
                foreach( $raM as $kfrM ) {
                    $defs[$tag]['m'][$kfrM->Value('v')] = $kfrM->Value('l_en');
                    }
                }
            }
        }
        return( $defs );
    }

    function GetDefsRAFromCode( $code )
    /**********************************
     */
    {
        switch( substr( $code, 0, strpos($code,"_") ) ) {
        	case "garlic":  return( $this->raDefs['garlic'] );
        }
        return( array() );
    }
    function GetDefsRAFromOSP( $osp )
    /**********************************
     */
    {
        $osp = strtolower($osp);
        if( isset($this->raDefs[$osp]) ) {
            return( $this->raDefs[$osp] );
        } else {
            return( array() );
        }
/*
        switch( strtolower($osp) ) {
        	case "apple":  return( $this->raDefs['apple'] );
        	case "bean":  return( $this->raDefs['bean'] );
        	case "garlic":  return( $this->raDefs['garlic'] );
        	case "lettuce":  return( $this->raDefs['lettuce'] );
        	case "onion":  return( $this->raDefs['onion'] );
        	case "pea":  return( $this->raDefs['pea'] );
        	case "pepper":  return( $this->raDefs['pepper'] );
        	case "potato":  return( $this->raDefs['potato'] );
        	case "squash":  return( $this->raDefs['squash'] );
        	case "tomato":  return( $this->raDefs['tomato'] );
        }
        return( array() );
*/
    }
}

?>
