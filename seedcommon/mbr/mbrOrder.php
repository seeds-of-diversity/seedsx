<?php

/* mbrOrder.php
 *
 * Copyright (c) 2009-2020 Seeds of Diversity Canada
 *
 * Definitions common to all mbrOrder components
 */

include_once(STDINC."SEEDLocal.php");
include_once( "registrations.php" );

define("SEEDS_DB_TABLE_MBR_ORDERS",
"
CREATE TABLE mbr_order_pending (                                                      -- change to mbr_orders
        _key        INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT,
        _created    DATETIME,
        _created_by INTEGER,
        _updated    DATETIME,
        _updated_by INTEGER,
        _status     INTEGER DEFAULT 0,

--    sid             INTEGER,

    mail_firstname  VARCHAR(100),
    mail_lastname   VARCHAR(100),
    mail_company    VARCHAR(100),
    mail_addr       VARCHAR(100),
    mail_city       VARCHAR(100),
    mail_prov       VARCHAR(100),
    mail_postcode   VARCHAR(100),
    mail_country    VARCHAR(100),
    mail_phone      VARCHAR(100),
    mail_email      VARCHAR(100),
    mail_lang       BOOL,           -- 1=French
    mail_eBull      BOOL            DEFAULT 1,
    mail_where      VARCHAR(100),

    mbr_type        VARCHAR(100),
    donation        INTEGER,
    pub_ssh_en      INTEGER,
    pub_ssh_fr      INTEGER,
    pub_nmd         INTEGER,
    pub_shc         INTEGER,
    pub_rl          INTEGER,

    notes           TEXT,

    pay_total       DECIMAL(8,2)    DEFAULT 0,

    -- OBSOLETE, use ePayType
    -- pay_type        INTEGER,        -- 1=Cheque, 2=PayPal

    -- OBSOLETE, use eStatus
    -- pay_status      INTEGER,        -- 0=new, 1=confirmed, 2=paid, 3=paid, 4=cancelled
                                    -- 4=payment succeeded, 5=processing, 6=filled

    pp_name         VARCHAR(200),   -- Set by PPIPN
    pp_txn_id       VARCHAR(200),
    pp_receipt_id   VARCHAR(200),
    pp_payer_email  VARCHAR(200),
    pp_payment_status VARCHAR(200),
    eStatus         ENUM('New','Paid','Filled','Cancelled') NOT NULL DEFAULT 'New',
    ePayType        ENUM('PayPal','Cheque') NOT NULL DEFAULT 'PayPal',
    sExtra          TEXT
);
"
);



/* OBSOLETE but codes still appear in some old db tables, so don't delete this until those are uninteresting
define("MBR_PT_CHEQUE",   "1");
define("MBR_PT_PAYPAL",   "2");
$mbr_PayType =      array( MBR_PT_CHEQUE => "Cheque",
                           MBR_PT_PAYPAL => "PayPal"
                         );

define("MBR_PS_NEW",       "0");    //0
define("MBR_PS_CONFIRMED", "1");
define("MBR_PS_PAID",      "2");    //2
define("MBR_PS_FILLED",    "3");    //3
define("MBR_PS_CANCELLED", "4");    //1
define("MAX_MBR_PS", "4" );
$mbr_PayStatus =    array( MBR_PS_NEW       => "New, not confirmed",
                           MBR_PS_CONFIRMED => "Payment pending",
                           MBR_PS_PAID      => "Paid",
                           MBR_PS_FILLED    => "Paid, Order Filled",
                           MBR_PS_CANCELLED => "Cancelled"
                         );
*/


// eStatus codes: use constants in code so you get a syntax error if you spell them wrong
define("MBRORDER_STATUS_NEW",       "New");
define("MBRORDER_STATUS_PAID",      "Paid");
define("MBRORDER_STATUS_FILLED",    "Filled");
define("MBRORDER_STATUS_CANCELLED", "Cancelled");



/* Adding a new item for sale?
 *
 * Add it to:
 * - Your FormDrawOrderCol
 * - Your ValidateParmsOrderValid
 * - Your ValidateParmsOrderMakeKFR
 * - MbrOrder::computeOrder()
 * - MbrOrder::conciseSummary() sometimes
 */


class MbrOrderCommon {
    public $kfrelOrder = NULL;
    public $oL = NULL;

    private $oApp;

    function __construct( SEEDAppDB $oApp, $kfdb, $lang, $uid )
    {
        $this->oApp = $oApp;
        $this->kfrelOrder = new KeyFrameRelation( $kfdb, $this->kfreldefMbrOrder($oApp), $uid );
        $this->setLocalText( $lang );
    }

    private function kfreldefMbrOrder( SEEDAppDB $oApp )
    {
        return( array( "Tables"=>array( array( "Table" => "{$this->oApp->GetDBName('seeds1')}.mbr_order_pending",
                                   "Fields" => array( array("col"=>"mail_firstname",  "type"=>"S"),
                                                      array("col"=>"mail_lastname",   "type"=>"S"),
                                                      array("col"=>"mail_company",    "type"=>"S"),
                                                      array("col"=>"mail_addr",       "type"=>"S"),
                                                      array("col"=>"mail_city",       "type"=>"S"),
                                                      array("col"=>"mail_prov",       "type"=>"S"),
                                                      array("col"=>"mail_postcode",   "type"=>"S"),
                                                      array("col"=>"mail_country",    "type"=>"S"),
                                                      array("col"=>"mail_phone",      "type"=>"S"),
                                                      array("col"=>"mail_email",      "type"=>"S"),
                                                      array("col"=>"mail_lang",       "type"=>"I"),
                                                      array("col"=>"mail_eBull",      "type"=>"I", "default"=>1),
                                                      array("col"=>"mail_where",      "type"=>"S"),
                                                      array("col"=>"mbr_type",        "type"=>"S"),
                                                      array("col"=>"donation",        "type"=>"F"),
                                                      array("col"=>"pub_ssh_en",      "type"=>"I"),
                                                      array("col"=>"pub_ssh_fr",      "type"=>"I"),
                                                      array("col"=>"pub_nmd",         "type"=>"I"),
                                                      array("col"=>"pub_shc",         "type"=>"I"),
                                                      array("col"=>"pub_rl",          "type"=>"I"),
                                                      array("col"=>"notes",           "type"=>"S"),
                                                      array("col"=>"pay_total",       "type"=>"F"),
                                                      //array("col"=>"pay_type",        "type"=>"I"),
                                                      //array("col"=>"pay_status",      "type"=>"I"),
                                                      array("col"=>"pp_name",         "type"=>"S"),
                                                      array("col"=>"pp_txn_id",       "type"=>"S"),
                                                      array("col"=>"pp_receipt_id",   "type"=>"S"),
                                                      array("col"=>"pp_payer_email",  "type"=>"S"),
                                                      array("col"=>"pp_payment_status","type"=>"S"),
                                                      array("col"=>"eStatus",         "type"=>"S", "default"=>'New'),
                                                      array("col"=>"eStatus2",        "type"=>"I"),
                                                      array("col"=>"dMailed",         "type"=>"S"),
                                                      array("col"=>"ePayType",        "type"=>"S", "default"=>'PayPal'),
                                                      ['col'=>"depositCode",          'type'=>'S'],
                                                      array("col"=>"sExtra",          "type"=>"S") ) ) ) ) );
    }

    private function setLocalText( $lang )
    /* Do this in a function because some strings are not constants - class var default values have to be constants
     */
    {
        $sL = array(
			/*  Ticket
			 */
            "order_num"
                => array( "EN" => "Order # ",
                          "FR" => "Ordre no. " ),
            "mailing_address"
                => array( "EN" => "Mailing Address",
                          "FR" => "Adresse Postal" ),
            "Membership"
                => array( "EN" => "Membership",
                          "FR" => "Adh&eacute;sion" ),


            "Charitable_donation"
                => array( "EN" => "Charitable Donation",
                          "FR" => "Don de charit&eacute;" ),
            "donation_of"
                => array( "EN" => "Donation of",
                          "FR" => "Don de" ),
            "Thank you"
                => array( "EN" => "Thank you",
                          "FR" => "Merci" ),
            "copy"
                => array( "EN" => "copy",
                          "FR" => "copie" ),

            "Registration"
                => array( "EN" => "Registration",
/* ?? */                  "FR" => "Conf&eacute;rences" ),
            "SL Adoption"
                => array( "EN" => "Seed Library Adoption",
                          "FR" => "Adoption &agrave; la Biblioth&egrave;que des semences" ),
            "adoption_of"
                => array( "EN" => "Adoption of",
                          "FR" => "Adoption de" ),
            "gift to"
                => array( "EN" => "gift to",
                          "FR" => "cadeau pour" ),
            "Misc Payment"
                => array( "EN" => "Miscellaneous Payment",
                          "FR" => "Paiement divers"),
            "incl_postage_etc"
                => array( "EN" => "includes postage and handling unless indicated otherwise,<BR/>and all applicable taxes",
                          "FR" => "inclut les frais postaux,<BR>la manutention et les taxes en vigueur" ),
            "Shipping"
                => array( "EN" => "Shipping",
                          "FR" => "Affranchissement"),

            "Please Pay"
                => array( "EN" => "Please Pay", "FR" => "Payez cet ordre SVP" ),
            "Order Paid"
                => array( "EN" => "Order Paid", "FR" => "Cet ordre est pay&eacute;" ),
            "Order Filled"
                => array( "EN" => "Order Filled", "FR" => "Cet ordre est complet" ),
            "Order Cancelled"
                => array( "EN" => "Order Cancelled", "FR" => "Cet ordre est d&eacute;command&eacute;" ),



            );
        $this->oL = new SEEDLocal( $sL, $lang );
    }
}


class MbrOrder extends MbrOrderCommon {
    /* Given a valid row in the mbrOrder table, compute the prices and draw the invoice.
     */
    public $kfrelOrder = NULL;

    var $kOrder = 0;
    var $kfr = NULL;    // the current mbrOrder row (Pre-confirmation: _key==0)
    var $raOrder = array();
    var $nTotal = 0;


    function __construct( SEEDAppDB $oApp, KeyFrameDB $kfdb, $lang, $kOrder = 0 )
    /***********************************************************
     */
    {
        parent::__construct( $oApp, $kfdb, $lang, 0 );
        $this->setKOrder( $kOrder );
    }

    function setKOrder( $kOrder, $kfrPreConf = NULL )
    /************************************************
        Set the current mbrOrder row and data
        Pre-confirmation: kOrder==0, kfr._key == 0, kfr contains data from form parms
        Post-confirmatio: kfr comes from db record
     */
    {
        $this->kfr = NULL;
        $this->kOrder = $kOrder;
        if( $kOrder ) {
        	// Post-confirmation, compute from the db record
            $this->kfr = $this->kfrelOrder->GetRecordFromDBKey( $this->kOrder );
        } else {
        	// Pre-confirmation, compute from the given non-saved kfrPreConf
            $this->kfr = $kfrPreConf;
        }
    }

    var $raPubs = array(
        'ssh_en'    => array( 'price'=>12.0, 'concise'=>"English SSH", 'title'=>"How to Save Your Own Seeds" ),
        'ssh_fr'    => array( 'price'=>12.0, 'concise'=>"French SSH",  'title'=>"La conservation des semences du patrimoine" ),
        'nmd'       => array( 'price'=>6.0,  'concise'=>"NMD",         'title'=>"Niche Market Development and Business Planning" ),
        'shc'       => array( 'price'=>8.0,  'concise'=>"SHC",         'title'=>"Selling Heritage Crops" ),
        'everyseed' => array( 'price'=>35.0, 'concise'=>"Every Seed",  'title'=>"Every Seed Tells a Tale" ),
        'ssh_en6'   => array( 'price'=>15.0, 'concise'=>"English How-to-Save", 'title'=>"How to Save Your Own Seeds, 6th Edition" ),
        'ssh_fr6'   => array( 'price'=>15.0, 'concise'=>"French La-conservation", 'title'=>"La conservation des semences, 6i&egrave;me &Eacute;dition" ),
        'suechan2012' => array( 'price'=>15.0, 'concise'=>"Conserving Native Pollinators", 'title'=>"Conserving Native Pollinators in Ontario" ),
        'kent2012' => array( 'price'=>8.0, 'concise'=>"How to Make a Pollinator Garden", 'title'=>"How to Make a Pollinator Garden" ),

        // historical for old tickets
        'rl'        => array( 'price'=>2.0,  'concise'=>"Resource List",'title'=>"Resource List" ),
        );


// rename these to mbr1_30, mbr3_75  and add mbr1_40, mbr3_85
    var $raMbrTypes = array( "mbr1_35"    => array( "n"=>35, "EN"=>"One Year Membership with on-line Seed Directory",
                                                             "FR"=>"L'adh&eacute;sion (1 an) avec le Catalogue de semences en Web" ),
                             "mbr1_45sed" => array( "n"=>45, "EN"=>"One Year Membership with printed and on-line Seed Directory",
                                                             "FR"=>"L'adh&eacute;sion (1 an) avec le Catalogue de semences imprim&eacute; et en Web" ),
                             "mbr1_0"     => array( "n"=>0, "EN"=>"One Year Membership with on-line Seed Directory",
                                                             "FR"=>"L'adh&eacute;sion (1 an) avec le Catalogue de semences en Web" ),
                             "mbr1_10sed"  => array( "n"=>10, "EN"=>"One Year Membership with printed and on-line Seed Directory",
                                                             "FR"=>"L'adh&eacute;sion (1 an) avec le Catalogue de semences imprim&eacute; et en Web" ),

                             // historical
                             "reg1"       => array( "n"=>30, "EN"=>"One Year Membership with printed and on-line Seed Directory",
                                                             "FR"=>"L'adh&eacute;sion (1 an) avec le Catalogue de semences imprim&eacute; et en Web" ),
                             "mbr25noSED" => array( "n"=>25, "EN"=>"One Year Membership with on-line Seed Directory",
                                                             "FR"=>"L'adh&eacute;sion (1 an) avec le Catalogue de semences en Web" ),
                             "reg3"     => array( "n"=>75, "EN"=>"Three Year Membership",                "FR"=>"L'adh&eacute;sion (3 ans)" ),
                             "fixed"    => array( "n"=>25, "EN"=>"One Year Membership (student/senior)", "FR"=>"L'adh&eacute;sion (&eacute;tudiant ou retrait&eacute;)" ),
                             "overseas" => array( "n"=>50, "EN"=>"One Year Membership (overseas)",       "FR"=>"L'adh&eacute;sion (outre-mer)" )
        );


    function _computeOrderPub( $pub, $n, $kNMD = 0 )
    {
        $price = $this->raPubs[$pub]['price'];
        if( $pub == 'nmd' && $kNMD && $kNMD < 600 ) { $price = 5.50; }  // kluge - changed price from 5.5 to 6

        $amt = $n * $price;
        $this->raOrder['pubs'][] = array($pub, $n, $price, $amt);
        $this->nTotal += $amt;
    }

    function computeOrder( $kOrder = 0, $kfrPreConf = NULL )
    /*******************************************************
        Given a kfr of an order, extract the order details  and compute the total price.
        This is used to draw the ticket and compute the price, before and after confirmation.
        Pre-confirmation:  the kfr is created from form parms, has _key==0
        Post-confirmation: the kfr comes from the db record
     */
    {
// DEPRECATE input parms; caller should use setKOrder
if( $kOrder ) $this->setKOrder( $kOrder );

        $this->nTotal = 0;
        $this->raOrder = array();
        $this->raOrder['pubs'] = array();
        $this->raOrder['registration'] = array();
        $this->raOrder['special'] = array();

        if( !$this->kfr )  return;

        /* For each part of the order, generate a concise summary, a line for the ticket and a running total
         */
        if( ($m = $this->kfr->Value('mbr_type')) && isset($this->raMbrTypes[$m]) ) {
            $amt = $this->raMbrTypes[$m]['n'];

            // kluge: replace all reg1 with mbr1_40 from 7338 up
            if( $m=='reg1' && ($this->kfr->Key()==0 || $this->kfr->Key() >= 7338) )  $amt = 40;

            $this->nTotal += $amt;
            $this->raOrder['mbr'] = $m;
        }

        if( ($n = floatval($this->kfr->value('donation'))) ) {
            $this->raOrder['donation'] = $n;
            $this->nTotal += $n;
        }

        if( ($n = $this->kfr->value('pub_ssh_en')) )  $this->_computeOrderPub( 'ssh_en', $n );
        if( ($n = $this->kfr->value('pub_ssh_fr')) )  $this->_computeOrderPub( 'ssh_fr', $n );
        if( ($n = $this->kfr->value('pub_nmd')) )     $this->_computeOrderPub( 'nmd', $n, $this->kfr->Key() );
        if( ($n = $this->kfr->value('pub_shc')) )     $this->_computeOrderPub( 'shc', $n );
        if( ($n = $this->kfr->value('pub_rl')) )      $this->_computeOrderPub( 'rl', $n );		// historical for old tickets

        if( !$this->kfr->IsEmpty( 'sExtra' ) ) {
            $ra = SEEDStd_ParmsURL2RA( $this->kfr->value('sExtra') );

            if( @$ra['mbrid'] ) {
                $this->raOrder['mbrid'] = $ra['mbrid'];
            }

            // Process registration info separately here, and ignore it in the general array further below
            $oReg = new MbrRegistrations();
            foreach( $oReg->raRegistrations as $code => $raReg ) {
                if( @$raReg['nametag'] ) {
                    if( @$ra["s{$code}_NametagName"] || @$ra["s{$code}_NametagOrg"] ) {
                        $this->raOrder['special']['nametag'] = @$ra["s{$code}_NametagName"]." | ".@$ra["s{$code}_NametagOrg"];
                    }
                }

                $sRadioChoices = "";
                if( isset($raReg['radio']) ) {
                    // Grab the radio choices and append them to the ticket lines below. This is kind of brutish, but there's no better definition.
                    foreach( $raReg['radio'] as $radiokey => $raRadioGroup ) {
                        if( ($v = @$ra[$radiokey]) && ($raChoice = @$raRadioGroup[$v]) ) {
                            $sRadioChoices .= ", ".$this->RegistrationText( $raChoice, 'ticket' );
                        }
                    }
                }
                if( isset($raReg['tickets']) ) {
                    foreach( $raReg['tickets'] as $ticketcode => $raTicket ) {
                        $k = "n{$code}_{$ticketcode}";
                        if( ($v = @$ra[$k]) ) { // $v is the number of tickets of each type
                            $amt = $raTicket['price'] * $v;
                            $this->nTotal += $amt;
                            $sTkt = ($this->oL->GetLang() == "EN" ? "ticket" : "billet").($v == 1 ? "" : "s");
                            $this->raOrder['registration'][] =
                                array( 'amount'=>$amt,
                                       'concise'=>$raTicket['concise']." ($v tickets)",
                                       'full'=>$this->RegistrationText($raTicket, 'full')
                                              .$sRadioChoices
                                              ." ($v $sTkt @ ".$this->dollar($raTicket['price']).")" );
                        }
                    }
                }
            }

            foreach( $ra as $k => $v ) {
                if( $k == "fMisc" ) {
                    $v = floatval($v);
                    $this->raOrder['misc'] = $v;
                    $this->nTotal += $v;
                }
                if( substr($k,0,7) == 'slAdopt' ) {
                    if( $k == 'slAdopt_amount' ) {
                        $v = floatval($v);
                        $this->raOrder['slAdopt_amount'] = $v;
                        $this->raOrder['slAdopt_cv'] = @$ra['slAdopt_cv'];
                        $this->raOrder['slAdopt_name'] = @$ra['slAdopt_name'];
                        $this->nTotal += $v;
                    }
                }
                if( $k == 'nPubEverySeed' ) {
                    $this->nTotal += $this->_computeOrderPub( 'everyseed', $v );
                    $shipping = floatval(@$ra['nPubEverySeed_Shipping']);
                    $this->raOrder['everyseed_shipping'] = $shipping;
                    $this->nTotal += $shipping;
                }
                if( $k == 'nPubEverySeed_shipping' ) {
                    // ignore: this is processed in nPubEverySeed
                }
                if( $k == 'nPubSSH-EN6' ) {
                    $this->nTotal += $this->_computeOrderPub( 'ssh_en6', $v );
                }
                if( $k == 'nPubSSH-FR6' ) {
                    $this->nTotal += $this->_computeOrderPub( 'ssh_fr6', $v );
                }
                if( $k == 'nPubSueChan2012' ) {
                    $this->nTotal += $this->_computeOrderPub( 'suechan2012', $v );
                }
                if( $k == 'nPubKent2012' ) {
                    $this->nTotal += $this->_computeOrderPub( 'kent2012', $v );
                }
                if( $k == 'nTorontoReg' ) {
                    $amt = 35.0 * $v;
                    $this->raOrder['registration'][] = array('amount'=>$amt, 'concise'=>"Toronto conf reg (for $v)", 'full'=>"25th Anniversary Celebration Toronto registration (for $v)");
                    $this->nTotal += $amt;
                }
                if( $k == 'nMontrealFete2009' ) {
                    $amt = 40.0 * $v;
                    $this->raOrder['registration'][] = array('amount'=>$amt, 'concise'=>"Montreal f&ecirc;te ($v billets)", 'full'=>"F&ecirc;te &agrave; Montr&eacute;al pour le 25&egrave;me anniversaire, 3 Oct 2009 ($v billets @ $40)");
                    $this->nTotal += $amt;
                }
                if( $k == 'cppi2009conf' ) {
                	$raEnum = array( 'reg'     => array('amount'=>150, 'concise'=>"Pollinator conference Montreal (2 days)", 'full'=>"Practical Pollinator Conservation conference, Montreal Oct 5-6 (2 days)"),
                	                 'monday'  => array('amount'=>85,  'concise'=>"Pollinator conference Montreal (Monday)", 'full'=>"Practical Pollinator Conservation conference, Monday Oct 5 only"),
                	                 'tuesday' => array('amount'=>85,  'concise'=>"Pollinator conference Montreal (Tuesday)", 'full'=>"Practical Pollinator Conservation conference, Tuesday Oct 6 only"),
                	                 'student' => array('amount'=>120, 'concise'=>"Pollinator conference Montreal (student 2 days)", 'full'=>"Practical Pollinator Conservation conference, Student rate (2 days)")
                	               );
                	if( isset($raEnum[$v]) ) {
                	    $this->raOrder['registration'][] = $raEnum[$v];
                	    $this->nTotal += $raEnum[$v]['amount'];
                	}
                }
                if( $k == 'bBulbils10' ) {
                    $this->nTotal += 10;
                    $this->raOrder['seeds'][] = array( 'k'=>'bulbils', 'amount'=>10 );
                }
                if( $k == 'bBulbils15' ) {
                    $this->nTotal += 15;
                    $this->raOrder['seeds'][] = array( 'k'=>'bulbils', 'amount'=>15 );
                }
            }
        }
    }

    function conciseSummary( $kOrder = 0 )
    /*************************************
     * Though this is only used by the order report, the code is centralized here to facilitate adding new items
     */
    {
        $s = "";

        $this->setKOrder( $kOrder );
        $this->computeOrder();

//        if( ($v = @$this->raOrder['mbrid']) ) {
//            $s .= "Member # $v<br/>";
//        }
        if( ($v = @$this->raOrder['mbr']) ) {
            $s .= $this->raMbrTypes[$v]['EN']."<BR/>";
        }
        if( ($v = @$this->raOrder['donation']) ) {
            $s .= SEEDCore_Dollar($v)." Donation<BR/>";
        }
        if( ($v = @$this->raOrder['slAdopt_amount']) ) {
            $s .= SEEDCore_Dollar($v)." Adoption<BR/>";
        }
        if( ($v = @$this->raOrder['misc']) ) {
            $s .= SEEDCore_Dollar($v)." Misc<BR/>";
        }
        if( @$this->raOrder['pubs'] ) {
            foreach( $this->raOrder['pubs'] as $ra ) {
                $s.= $ra[1]." x ".$this->raPubs[$ra[0]]['concise'];
                if( $ra[0] == 'everyseed' ) {
                    $s .= " + ".SEEDCore_Dollar(floatval(@$this->raOrder['everyseed_shipping']));
                }
                $s .= "<BR/>";
            }
        }
        if( @$this->raOrder['seeds'] ) {
            foreach( $this->raOrder['seeds'] as $ra ) {
                if( @$ra['k'] == 'bulbils' ) {
                    $s .= SEEDCore_Dollar(@$ra['amount'])." garlic bulbils<br/>";
                }
            }
        }
        if( @$this->raOrder['registration'] ) {
            foreach( $this->raOrder['registration'] as $ra ) {
                $s .= SEEDCore_Dollar($ra['amount'])." ".$ra['concise']."<BR/>";
            }
        }
        if( @$this->raOrder['special'] ) {
            foreach( $this->raOrder['special'] as $k => $v ) {
                if( $k == 'nametag' ) {
                    $s .= "Nametag: $v";
                } else {
                    $s .= SEEDCore_Dollar(@$v['amount'])." ".@$v['concise']."<BR/>";
                }
            }
        }
        return( $s );
    }

    function tinySummary()
    /*********************
        One line, used as the PayPal description
     */
    {
        $s = "";
        $this->computeOrder();
        if( $this->kfr ) {
            if( @$this->raOrder['mbr'] )           $s .= ($this->oL->GetLang() == 'EN' ? "Membership " : "Adh&eacute;sion ");
            if( @$this->raOrder['donation'] )      $s .= ($this->oL->GetLang() == 'EN' ? "Donation " : "Don ");
            if( @$this->raOrder['slAdopt_amount']) $s .= "Adoption ";
            if( @$this->raOrder['misc'] )          $s .= "Misc ";
            if( @$this->raOrder['pubs'] )          $s .= ( count($this->raOrder['pubs']) > 1 ? "Publications " : "Publication " );
            if( @$this->raOrder['registration'] )  $s .= "Registration ";
            if( @$this->raOrder['seeds'] )         $s .= ($this->oL->GetLang() == 'EN' ? "Seeds " : "Semences ");
        }
        return( $s );
    }

    function DrawTicket( $kOrder = 0, $kfrPreConf = NULL )
    /*****************************************************
        $kOrder != 0               : load the order from the given key
        $kOrder == 0, $kfrPreConf  : use the given pre-confirmed kfr (this is used in the Validate step)
        $kOrder == 0, !$kfrPreconf : use the currently loaded kfr
     */
    {
        if( $kOrder || $kfrPreConf )  $this->setKOrder( $kOrder, $kfrPreConf );
        $this->computeOrder();
//var_dump($this->raOrder);

        if( !$this->kfr )  return( "" );

        /* Draw the contact info from the kfr.
         * Draw the order from $this->raOrder (as determined by computeOrder)
         */
        $s =  "<TABLE id='mbro_Ticket' border='1' cellpadding='10' cellspacing='0'>"
             ."<TR><TH colspan=3>";
        if( $this->kOrder ) {
            $raL = array(MBRORDER_STATUS_NEW=>'Please Pay', MBRORDER_STATUS_PAID=>'Order Paid', MBRORDER_STATUS_FILLED=>'Order Filled', MBRORDER_STATUS_CANCELLED=>'Order Cancelled');
            $s .= "<H3>".$this->oL->S($raL[$this->kfr->value('eStatus')])."</H3>";
            $s .= $this->oL->S('order_num').($this->kOrder)."&nbsp;&nbsp;:&nbsp;&nbsp;".$this->kfr->value('_created');  /* ."<BR/><BR/>"; */
        }
        $s .= /* $this->oL->S('mailing_address'). */  "</TH></TR>"
             ."<TR><TD colspan=3>";

        if( ($v = intval(@$this->raOrder['mbrid'])) ) {
            $s .= "<b>".($this->oL->GetLang() == 'FR' ? "Membre no." : "Member #")."$v</b><br/>";
        }
        if( !$this->kfr->IsEmpty('mail_firstname') || !$this->kfr->IsEmpty('mail_lastname') ) {
            $s .= $this->kfr->Expand( "<B>[[mail_firstname]] [[mail_lastname]]</B><BR/>" );
        }
        $s .= $this->kfr->ExpandIfNotEmpty( 'mail_company', "<B>[[]]</B><BR/>" )
             .$this->kfr->Expand( "[[mail_addr]]<BR/>"
                                 ."[[mail_city]] [[mail_prov]] [[mail_postcode]] [[mail_country]]<BR/>" )
             .$this->kfr->ExpandIfNotEmpty( 'mail_phone', "[[]]<BR/>" )
             .$this->kfr->ExpandIfNotEmpty( 'mail_email', "[[]]<BR/>" );
        if( !$this->kfr->IsEmpty('notes') ) {
            $s .= "<BR/><B>Notes:</B><BR/>".nl2br($this->kfr->value('notes'))."<BR/>";
        }

        $s .= "</TD></TR>";

        /* Membership
         */
        if( @$this->raOrder['mbr'] ) {
            $raMbr = @$this->raMbrTypes[ $this->raOrder['mbr'] ];
            $s .= $this->ticketSectionHead('Membership');
            if( is_array($raMbr) ) {
            	$d = $raMbr['n'];

                // Kluge: use mbr1_40 instead
                if( $this->raOrder['mbr']=='reg1' && ($this->kfr->Key()==0 || $this->kfr->Key() >= 7338) )  $d = 40;

                $s .= "<TR><TD colspan='2'>".$raMbr[$this->oL->GetLang()]."</TD>"
                         ."<TD>".$this->dollar($d)."</TD></TR>";
            } else {
                $s .= "<TR><TD colspan=3><FONT color=red>Error: unknown membership type ".$this->raOrder('mbr')."</FONT></TD></TR>";
            }
        }

        /* Donation
         */
        if( @$this->raOrder['donation'] ) {
            $s .= $this->ticketSectionHead('Charitable_donation')
                 ."<TR><TD colspan=2>".$this->oL->S('donation_of')." ".$this->dollar($this->raOrder['donation']).".  "
                 ."<I><B>".$this->oL->S('Thank you')."!</B></I></TD><TD>".$this->dollar($this->raOrder['donation'])."</TD></TR>";
        }

        /* Adoption
         */
        if( @$this->raOrder['slAdopt_amount'] ) {
            $s .= $this->ticketSectionHead('SL Adoption')
                 ."<TR><TD colspan=2>".$this->oL->S('adoption_of')." ".$this->dollar($this->raOrder['slAdopt_amount']).", "
                 .@$this->raOrder['slAdopt_cv']
                 .(!empty($this->raOrder['slAdopt_name']) ? (", ".$this->raOrder['slAdopt_name']) : "").".  "
                 ."<I><B>".$this->oL->S('Thank you')."!</B></I></TD><TD>".$this->dollar($this->raOrder['slAdopt_amount'])."</TD></TR>";
        }

        /* Publications
         */
        if( @$this->raOrder['pubs'] && count($this->raOrder['pubs']) ) {
            /* No lookups or calculations are allowed here, to centralize all logic in computeOrder()
             */
            $s .= $this->ticketSectionHead( "", "Publications" );
            foreach( $this->raOrder['pubs'] as $k => $raP ) {      // pub, n, price, amt
                $s .= "<TR><TD>".$this->raPubs[$raP[0]]['title']."</TD>"
                     ."<TD>".$raP[1]." ".($raP[1] > 1 ? "copies" : $this->oL->S('copy'))." @ ".$this->dollar($raP[2])."</TD>"
                     ."<TD>".$this->dollar($raP[3])."</TD></TR>";
            }
            if( @$this->raOrder['everyseed_shipping'] ) {
                $s .= $this->ticketLine( $this->oL->S('Shipping'), "", $this->raOrder['everyseed_shipping'] );
            }
        }

        /* Misc and Special
         */
/*

        $s1 = $this->kfr->value( 'sExtra' );
        if( !empty($s1) ) {
            $ra = SEEDStd_ParmsURL2RA( $s1 );
            foreach( $ra as $k => $v ) {
                if( $k == 'nPubEverySeed' ) {
                    $shipping = @$ra['nPubEverySeed_shipping'];
                    $s .= "<TR><TH colspan=3>Publications</TH></TR>"
                         ."<TR><TD>Every Seed Tells a Tale</TD><TD>$v ".($v > 1 ? "copies" : $this->oL->S('copy'))." @ $35 + ".$this->dollar($shipping)." ".$this->oL->S('postage')."</TD><TD>".SEEDCore_Dollar($v*35+$shipping,$this->oL->GetLang())."</TD></TR>";
                }
                if( $k == 'nPubEverySeed_shipping' ) {
                    // this is processed in nPubEverySeed
                }
// computeOrder puts this in ['registrations']
                if( $k == 'nTorontoReg' && ($v = intval($v)) ) {
                    $s .= "<TR><TH colspan=3>Registrations</TH></TR>"
                         ."<TR><TD>25th Anniversary Celebration (Toronto) - $v registrant".($v>1 ? "s" : "")."</TD><TD>&nbsp;</TD><TD>".SEEDCore_Dollar($v*35)."</TD><TR>";
                }
            }
        }
*/

        if( @$this->raOrder['seeds'] && count($this->raOrder['seeds']) ) {
            foreach( $this->raOrder['seeds'] as $ra ) {
                if( $ra['k'] == 'bulbils' ) {
                    $s .= "<TR><TD colspan='2'>Garlic Bulbils</TD><TD>".$this->dollar($ra['amount'])."</TD></TR>";
                } else {
                    $s .= ""; // no other special values defined
                }
            }
        }


        if( @$this->raOrder['registration'] && count($this->raOrder['registration']) ) {
            $s .= "<TR><TH colspan='3'>".$this->oL->S('Registration')."</TH></TR>";
            foreach( $this->raOrder['registration'] as $ra ) {
                $s .= "<TR><TD colspan='2'>".$ra['full']."</TD><TD>".$this->dollar($ra['amount'])."</TD><TR>";
            }
        }

        if( @$this->raOrder['special'] && count($this->raOrder['special']) ) {
            foreach( $this->raOrder['special'] as $k => $v ) {
                if( $k == 'nametag' ) {
                    $s .= "<TR><TD colspan='2'>Name tag: $v</TD></TD>&nbsp;</TD></TR>";
                } else {
                    $s .= ""; // no other special values defined
                }
            }
        }


        if( @$this->raOrder['misc'] ) {
            $s .= "<TR><TH colspan=3>".$this->oL->S("Misc Payment")."</TH></TR>"
                 ."<TR><TD colspan='2'>".$this->oL->S("Misc Payment")."</TD><TD>".$this->dollar($this->raOrder['misc'])."</TD><TR>";
        }


        /* Total Payment
         */
        $s .= "<TR><TH colspan=3>&nbsp;</TH></TR>"
             ."<TR><TD colspan=2><B>Total</B> (".$this->oL->S('incl_postage_etc').")</TD>"
                 ."<TD>".($this->kfr->value('mail_country')=='Canada' ? "Cdn " : "US ").$this->dollar($this->nTotal)."</TD></TR>";

        if( defined("MBR_ADMIN") && MBR_ADMIN ) {
            $s .= "<TR><TD colspan=1>Payment Status</TD><TD colspan=2>"
                 .$this->kfr->value('ePayType')." - ".$this->kfr->value('eStatus')
                 ."</TD></TR>"
                 ."<TR><TD colspan=3>&nbsp</TD></TR>"
                 ."<TR><TH colspan=3>Other Information</TH></TR>"
                 ."<TR><TD colspan=3><B>e-Bulletin:</B> ".($this->kfr->value('mail_eBull') ? "Y" : "N")."</TD></TR>"
                 ."<TR><TD colspan=3><B>Preferred Language:</B> ".($this->kfr->value('mail_lang') ? "French" : "English")."</TD></TR>"
                 ."<TR><TD colspan=3><B>Where did you hear about Seeds of Diversity:</B><BR>".$this->kfr->value('mail_where')."</TD></TR>";
        }

        $s .= "</TABLE>";

        return( $s );
    }

    function ticketSectionHead( $lCode, $s = "" )
    {
        return( "<TR><TH colspan='3'>".($lCode ? $this->oL->S($lCode) : "").$s."</TH></TR>" );
    }

    function ticketLine( $s1, $s2, $d )
    {
        return( "<TR><TD".(empty($s2) ? " colspan='2'" : "").">$s1</TD>"
                .(empty($s2) ? "" : "<TD>$s2</TD>")
                ."<TD>".$this->dollar($d)."</TD></TR>" );
    }

    function dollar($amt)   // deprecate, this is the same as $this->oL->Dollar($amt)
    {
        return( SEEDCore_Dollar($amt,$this->oL->GetLang()) );
    }

    function CreateBlankKFR()
    {
        return( $this->kfrelOrder->CreateRecord() );
    }

    function RegistrationText( $raText, $k )
    /***************************************
        Get the EN/FR text for $k.

        $raText can be $raReg     -- which is $raRegistrations[eventcode]
            or $raTicket      -- which is $raRegistrations[eventcode]['tickets'][ticketcode]
            because they have the same structure

        For $k=='foo' there are three ways to find the appropriate text (say for English):
            'foo' => string                    -- untranslated string is the same for EN and FR
            'fooEN' => string                  -- English
            'foo_EN' => string                 -- just because
            'foo' => array( 'EN' => string,    -- sometimes it just looks better that way

        If none of these exist, then return a FR equivalent if available.  e.g. events in B.C. are
        probably not translated, but they'll appear in EN on the French web site
     */
    {
        if( isset($raText[$k]) && is_string($raText[$k]) ) {        // a non-translated text with no EN/FR suffix
            return( $raText[$k] );
        }

        $lang = $this->oL->GetLang();
        $langOther = ($lang == 'EN' ? "FR" : "EN");

        if( isset($raText[$k.$lang]) )           { return( $raText[$k.$lang] ); }
        if( isset($raText[$k.'_'.$lang]) )       { return( $raText[$k.'_'.$lang] ); }
        if( isset($raText[$k][$lang]) )          { return( $raText[$k][$lang] ); }

        if( isset($raText[$k.$langOther]) )      { return( $raText[$k.$langOther] ); }
        if( isset($raText[$k.'_'.$langOther]) )  { return( $raText[$k.'_'.$langOther] ); }
        if( isset($raText[$k][$langOther]) )     { return( $raText[$k][$langOther] ); }

        return( "" );
    }

}

function MbrOrderStyle()
{
    return("
<STYLE type='text/css'>

    .mbro_boxbody         { background-color:#eee; padding:15px;
                          }
    .mbro_boxbody,
    .mbro_boxbody p       { font-size:10pt; font-family: verdana,helvetica,sans-serif;
                          }

    .mbro_ctrl            { font-size:9pt; margin-left:10px;
                          }
    .mbro_ctrl p,
    .mbro_ctrl td,
    .mbro_ctrl input,
    .mbro_ctrl select     { font-size:9pt;
                          }
    .mbro_help,
    .mbro_help p,
    .mbro_help li         { font-size:9pt;
                          }

    #mbro_Ticket          { border-width: 1px 1px 1px 1px;
                            border: grey solid thin;
                          }
    #mbro_Ticket th       { color:white;
                            background-color:green;
                          }
    #mbro_Ticket td       { border: grey solid thin;
                            padding: 5px 1em;
                          }

    .mbro_infobox         { border:1px solid #777; margin:10px; padding:5px; }

/*****
 * mbrOrderCheckout
 */
    #obsolete_mbrocForm1col_order  {
                            padding-right: 1em;
                          }

    #obsolete_mbrocForm1col_contactinfo {
                            border-left: medium ridge #ccc;
                            border-bottom: medium ridge #ccc;
                            padding-left: 1em;
                            padding-bottom: 2em;
                          }

/*****
 * mbro_expand:  mbro_boxbody is initially display:none, script slides it down and up
 */
.mbro_expand .mbro_boxbody { display: none; }

.mbro_expand-note         { font-size:9pt; float:left; margin-left:5px; }
.mbro_expand-button       { position: relative; float: left;
                            height: 14px; width: 14px;
                            overflow: hidden;
                          }
.mbro_expand-button img   { position: absolute; left: 0;   /* js shifts this image right-left to alternate the symbol */
                          }

</STYLE>

<script>
/*
jQuery(document).ready(function($){

    $('.mbro_expand').click( function(e) {
        oHeader = $(this).find('.mbro_boxheader');
        clickY = e.pageY;
        headTop = oHeader.offset().top;
        headBottom = headTop + oHeader.innerHeight();  // includes padding
        if( clickY > headTop && clickY < headBottom ) {
            // clicked on the header (otherwise clicked on the form so don't scroll)

            var pos = $(this).find('img').css('left');
            if(pos == '0px') {
                $(this).find('img').css('left', '-14px');
                $(this).find('.mbro_boxbody').slideDown(500);
                $(this).find('.mbro_expand-note').html('');
            } else {
                $(this).find('img').css('left', '0px');
                $(this).find('.mbro_boxbody').slideUp(500);
                //$(this).find('.mbro_expand-note').html('Click to show');
            }
        }
    });
});
*/
</script>
");
}


function MbrOrder_Setup( $oSetup, &$sReport, $bCreate = false )
/**************************************************************
    Test whether the tables exist.
    bCreate: create the tables and insert initial data if they don't exist.

    Return true if exists (or create is successful if bCreate); return a text report in sReport

    N.B. $oSetup is a SEEDSetup.  This file doesn't include SEEDSetup.php because the setup code is very rarely used.
         Instead, the code that calls this function knows about SEEDSetup.
 */
{
    return( $oSetup->SetupTable( "mbr_order_pending", SEEDS_DB_TABLE_MBR_ORDERS, $bCreate, $sReport ) );
}

?>
