<?php
include_once( STDINC."KeyFrame/KFRelation.php" );
include_once( SEEDCOMMON."siteKFDB.php" );
include_once(STDINC."SEEDLocal.php");
// TODO: csci_seeds_archive is a cumulative backup of all (proofread) csci_seeds rows,
//       with the year that the row was verified.  csci_seeds should have a year column
//       too, to indicate the most recent verification, and to ensure that old rows are not
//       archived with the current year.


/* Canadian Seed Catalogue Inventory
 *
 * Copyright 2010-2011 Seeds of Diversity Canada
 *
 * Base definitions
 */

define("SEEDS_DB_TABLE_CSCI_COMPANY",
"
CREATE TABLE csci_company (

        _key        INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT,
        _created    DATETIME,
        _created_by INTEGER,
        _updated    DATETIME,
        _updated_by INTEGER,
        _status     INTEGER DEFAULT 0,


    name_en     VARCHAR(200),
    name_fr     VARCHAR(200),
    addr_en     VARCHAR(200),
    addr_fr     VARCHAR(200),
    city        VARCHAR(200),
    prov        VARCHAR(200),
    country     VARCHAR(200),
    postcode    VARCHAR(200),
    phone       VARCHAR(200),
    fax         VARCHAR(200),
    web         VARCHAR(200),
    web_alt     VARCHAR(200),
    email       VARCHAR(200),
    email_alt   VARCHAR(200),
    desc_en     TEXT,
    desc_fr     TEXT,
    cat_cost    INTEGER,
    year_est    INTEGER,

    -- internal
    comments    VARCHAR(200),
    bRLShow     INTEGER DEFAULT 0,

    -- when the 'This is Correct' checkbox is checked, tsVerified=NOW(),bNeedVerify=0
    -- when any data is changed by non-approvers, bNeedProof=1
    tsVerified  DATETIME,
    bNeedVerify INTEGER DEFAULT 1,
    bNeedProof  INTEGER DEFAULT 1,
    bNeedXlat   INTEGER DEFAULT 1
);
"
);

define("SEEDS_DB_TABLE_CSCI_SEEDS",
"
CREATE TABLE csci_seeds (

        _key        INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT,
        _created    DATETIME,
        _created_by INTEGER,
        _updated    DATETIME,
        _updated_by INTEGER,
        _status     INTEGER DEFAULT 0,

-- these five columns are what the table should contain
    fk_csci_company INTEGER NOT NULL,
    fk_sl_species   INTEGER NOT NULL,
    fk_sl_pcv       INTEGER NOT NULL,
    oname           VARCHAR(200),
    tsVerified      DATETIME,           -- YEAR(tsVerified) goes in csci_seeds_archive.year
    bNeedVerify     INTEGER DEFAULT 1,
    bNeedProof      INTEGER DEFAULT 1,

    index (fk_csci_company),
    index (fk_sl_species),
    index (fk_sl_pcv),

-- these three columns are what the table does contain
    company_name    VARCHAR(200),
    psp             VARCHAR(200),
    icv             VARCHAR(200),
    bOrganic        TINYINT DEFAULT 0,

-- for csci_seeds_archive
-- key_orig INTEGER NOT NULL,
-- index(key_orig),


    index (psp),
    index (icv)
);
"
);

class SL_CSCI
{
    public $kfrelSeeds;
    public $kfrelCompany;
    public $kfrelSeedsXCompany;
	public $lang;

    function SL_CSCI( $kfdb,$lang )
    {

        $this->kfrelSeeds         = new KeyFrameRelation( $kfdb, $this->kfrelDef_Seeds(), 0 );
        $this->kfrelCompany       = new KeyFrameRelation( $kfdb, $this->kfrelDef_Company(), 0 );
// Pending until Seeds has a foreign key to Company
//        $this->kfrelSeedsXCompany = new KeyFrameRelation( $kfdb, $this->kfrelDef_SeedsXCompany(), 0 );
		$this->_setLocalText( $lang );
    }

	function _setLocalText( $lang )

    {
        $sL = array(
			"Title"
				=> array( "EN" => "Canadian Seed Catalogue Inventory",
						  "FR" => ""),
            "Looking"
            	=> array( "EN" => "Looking for seeds?",
            			  "FR" => ""),
            "Intro"
            	=> array( "EN" => "This is a list of vegetable and fruit seeds that were sold in recent years by Canadian seed companies. "
        						 ."Click on a section in the right-hand box to see the varieties available, and the companies that sold them. "
        						 ."Visit these companies, buy their seeds, and enjoy a beautiful, diverse garden this summer.",
        				  "FR" => ""),
        	"Found"
        		=> array( "EN" => "Found what you were looking for?",
        				  "FR" => ""),
        	"Donation"
        		=> array( "EN" => "Please consider making a donation. We receive no funding from seed companies for providing this list,"
								 ." and we rely on donations to keep it up to date.",
        				  "FR" => ""),
        	"Donate"
        		=> array( "EN" => "Donate",
        				  "FR" => ""),
        	"According"
        		=> array( "EN" => "According to our records, the following varieties were offered by Canadian seed and plant companies in 2010.",
        				  "FR" => ""),
        	"Info"
        		=> array( "EN" => "This information is provided as is, to further our knowledge and conservation of food biodiversity. "
            			   		 ."Seeds of Diversity makes no claims regarding accuracy, errors, or omissions, but we appreciate any updates that you can provide.",
        				  "FR" => ""),
        	"Seeds"
        		=> array( "EN" => "Vegetable and Fruit Seeds",
        				  "FR" => ""),
        	"Available"
        		=> array( "EN" => "Available in Canada",
        				  "FR" => ""),
            );
        $this->oL = new SEEDLocal( $sL, $lang );

    }

	function DrawText(){

		$s = "";
		$s .= "<TABLE border='0' cellspacing='0' cellpadding='0'><TR><TD valign='top'>"   // main text in first col, psp list in right col
        ."<H2>".$this->oL->S('Title')."</H2>";

    	$sPsp = SEEDSafeGPC_GetStrPlain( "psp" );

    	/* Introductory text
     	*/
    	$s .= 	"<TABLE border='0' cellpadding='10' cellspacing='0'>"
        		."<TR valign='top'><TD>"
        		."<P>".$this->oL->S('Looking')."</P>"
        		."<P>".$this->oL->S('Intro')."</P>"
        		."</TD></TR><TR><TD>";
    	if( !empty($sPsp) ) {
        	$s .= "<DIV style='border:1px solid gray; padding:1em; font-size:10pt;margin-right:2em;width:20em;'>"
            	."<B>".$this->oL->S('Found')."</B>"
            	."<P>".$this->oL->S('Donation')."</P>"
            	."<P style='text-align:center;font-size:14pt;'><A href='".MBR_ROOT."' style='color:#397A37'>".$this->oL->S('Donate')."</A></P>"
            	."</DIV>";
    	}
    	$s .= "&nbsp;</TD></TR>";
    	if( !empty($sPsp) ) {
        	$s .= "<TR><TD colspan='2'>"
            	."<DIV style='border:1px solid gray; padding:1em; font-size:8pt;margin-right:2em;'>"
            	."<P>".$this->oL->S('According')."</P>"
            	."<P>".$this->oL->S('Info')."</P>"
           		."</DIV>"
            	."</TD></TR>";
    	}
    	$s .= "</TABLE>";

    	/* Show cultivar list
     	*/
    	if( !empty($sPsp) ) {
        	$s .= $this->DrawSeedSourceList( $sPsp );
    	}
    	/* Show species list
     	*/
    	$s .= "</TD><TD valign='top' style='width:20em'>"
        	."<BR/><BR/>"
        	."<DIV class='P01_navbox01'>"
        	."<H3>".$this->oL->S('Seeds')."<BR/>".$this->oL->S('Available')."</H3>"
        	.$this->DrawSpeciesList()
        	."</DIV>"
        	."</TD></TR></TABLE>";
		return($s);
	}

    function DrawSpeciesList( $raParms = array() )
    {
        $s = "";
        $sUrlParms = isset($raParms['raURLExtraParms']) ? ("&".SEEDStd_ParmsRA2URL( $raParms['raURLExtraParms'] )) : "";

        if( ($kfr = $this->kfrelSeeds->CreateRecordCursor( "", array('sSortCol'=>'psp', 'sGroupCol'=>'psp') )) ) {
        //if( $dbc = $this->kfrelSeeds->kfdb->CursorOpen( "SELECT psp FROM csci_seeds WHERE _status=0 GROUP BY psp ORDER BY psp" ) ) {
            while( $kfr->CursorFetch() ) {
                //$s .= "<A HREF='${_SERVER['PHP_SELF']}?psp=".urlencode($kfr->value('psp'))."$sUrlParms'>{$kfr->value('psp')}</A><BR/>";
            	$s .= "<A HREF='${_SERVER['PHP_SELF']}?q=CSCI&psp=".urlencode($kfr->value('psp'))."$sUrlParms'>{$kfr->value('psp')}</A><BR/>";
            }
        }
        return( $s );
    }

    function DrawSeedSourceList( $sPsp )
    {
        $s = "<H3>".SEEDStd_HSC($sPsp)." - Varieties Sold in Canada</H3>";

        if( ($kfr = $this->kfrelSeeds->CreateRecordCursor( "psp='".addslashes($sPsp)."'", array('sSortCol'=>'icv,company_name') )) ) {
            $icv = NULL;
            $raCompanies = array();
            for( $n = 0; $kfr->CursorFetch(); ++$n ) {
                if( !$n ) {
                    $icv = $kfr->value('icv');
                } else if( $icv != $kfr->value('icv') ) {
                    $s .= $this->_dssl_drawICV( $icv, $raCompanies );
                    $icv = $kfr->value('icv');
                    $raCompanies = array();
                }
                $web = $this->kfrelSeeds->kfdb->Query1("SELECT web FROM rl_companies WHERE name_en='".addslashes($kfr->value('company_name'))."'");
                $sCmp = $kfr->Expand( "<NOBR>[[company_name]]</NOBR>" );
                //$sCmp = $kfr->Expand( "<P>[[company_name]]</P>" );
                if( !empty($web) )  $sCmp = "<A HREF='http://$web' target='csci_company'>$sCmp</A>";
                $raCompanies[] = $sCmp;
            }
            if( count($raCompanies) ) {
                $s .= $this->_dssl_drawICV( $icv, $raCompanies );
            }
            if( !$n ) {
                $s .= "No records";
            }
        }
        return( $s );
    }

    function _dssl_drawICV( $icv, $raCompanies )
    /*******************************************
     */
    {
        return( "<P>"
               ."<B>$icv</B>"
               ."<P><BLOCKQUOTE><FONT size='-1'>"
               .implode( ",&nbsp; ", $raCompanies )    // this puts two spaces between names, but allows line breaking to happen without inserting leading spaces
               ."</FONT></BLOCKQUOTE></P>\n" );
    }

    function kfrelDef_Seeds()
    {
        return( array( "Tables" => array( array( "Table" => 'csci_seeds',
                                                 "Fields" => self::$kFld_CSCI_Seeds ) ) ) );
    }

    function kfrelDef_Company()
    {
        return( array( "Tables" => array( array( "Table" => 'csci_company',
                                                 "Fields" => self::$kFld_CSCI_Company ) ) ) );
    }

// Pending until Seeds has a foreign key to Company
//    function kfrelDef_SeedsXCompany()
//    {
//        return( array( "Tables" => array( array( "Table" => 'csci_seeds',
//                                                 "Fields" => self::$kFld_CSCI_Seeds ),
//                                          array( "Table" => 'csci_company',
//                                                 "Type" => 'Parent',
//                                                 "Fields" => self::$kFld_CSCI_Company) ) ) );
//   }

    static $kFld_CSCI_Company = array(
        array("col"=>"name_en",       "type"=>"S"),
        array("col"=>"name_fr",       "type"=>"S"),
        array("col"=>"addr_en",       "type"=>"S"),
        array("col"=>"addr_fr",       "type"=>"S"),
        array("col"=>"city",          "type"=>"S"),
        array("col"=>"prov",          "type"=>"S"),
        array("col"=>"country",       "type"=>"S", "default"=>"Canada"),
        array("col"=>"postcode",      "type"=>"S"),
        array("col"=>"phone",         "type"=>"S"),
        array("col"=>"fax",           "type"=>"S"),
        array("col"=>"web",           "type"=>"S"),
        array("col"=>"web_alt",       "type"=>"S"),
        array("col"=>"email",         "type"=>"S"),
        array("col"=>"email_alt",     "type"=>"S"),
        array("col"=>"desc_en",       "type"=>"S"),
        array("col"=>"desc_fr",       "type"=>"S"),
        array("col"=>"cat_cost",      "type"=>"I", "default"=> -1),
        array("col"=>"year_est",      "type"=>"I"),
        array("col"=>"comments",      "type"=>"S"),
        array("col"=>"bRLShow",       "type"=>"I"),
        array("col"=>"tsVerified",    "type"=>"S"),
        array("col"=>"bNeedVerify",   "type"=>"I"),
        array("col"=>"bNeedProof",    "type"=>"I"),
        array("col"=>"bNeedXlat",     "type"=>"I") );

    static $kFld_CSCI_Seeds = array(
        //array("col"=>"fk_csci_company", "type"=>"K"),
        //array("col"=>"fk_sl_species",   "type"=>"K"),
        //array("col"=>"fk_sl_pcv",       "type"=>"K"),
        //array("col"=>"oname",           "type"=>"S"),
        array("col"=>"company_name",  "type"=>"S"),  // PHASE OUT, REPLACE with fk
        array("col"=>"psp",           "type"=>"S"),  // PHASE OUT, REPLACE with fk
        array("col"=>"icv",           "type"=>"S"),  // PHASE OUT, REPLACE with fk, oname
        );
}





/********************************************** Added by Matt ***************************************************/




/*
 * 	This class is responsible for showing seed supplier info in a web page. It's intended use is to be in a
 * 	Joomla module.
 */
class ResourceList
{
	private $language;
	private $serverName;

	/*
	 * 	This is the constructor for ResourceList. It initializes $serverName with the name of the current
	 * 	server and initializes $language with the language of the page;
	 */
	function ResourceList()
	{
		$this->serverName = $_SERVER["SERVER_NAME"];
		if ($this->serverName == "www.semences.ca")
		{
			$this->language = "FR";
		}
		else
		{
			$this->language = "EN";
		}

	}


	function getLanguage()
	{
		return $this->language;
	}

	/*
	 * Name:	displayCompany()
	 * Purpose:	Prints a supplier record to the web page
	 * Inputs:	$dbRow	- Database row recieved from a query.
	 * 			$lang	- Current language
	 * Outputs:	Outputs a single, formatted company record to the web page.
	 * Returns:	$page 	- The web page elements created for the module.
	 */
	function displayCompany($dbRow, $lang)
	{

		if ($lang == "FR")  // french
		{
			$name = $dbRow['name_fr'];
			$addr = $dbRow['addr_fr'];
			$desc = $dbRow['desc_fr'];
		}
		else // default to english
		{
			$name = $dbRow['name_en'];
			$addr = $dbRow['addr_en'];
			$desc = $dbRow['desc_en'];
		}

	    if($dbRow['cat_cost'] > 0) // catalogue not free
	    {
	    	if ($lang == "FR")
	    	{
	    		$catalogueCost = " Le catalogue co�te $" . $dbRow['cat_cost'];
	    	}
	    	else
	    	{
	    		$catalogueCost = " The catalogue costs $" . $dbRow['cat_cost'];
	    	}
	    }
	    else  // free catalogue
	    {
	    	if ($lang == "FR")
	    	{
	    		$catalogueCost = " Catalogue gratuit.";
	    	}
	    	else
	    	{
	    		$catalogueCost = " Catalogue free.";
	    	}
	    }

	    $coordinates  = "${addr},&nbsp;${dbRow['city']},&nbsp;";	// format the address, phone, fax, province, and postal code
	    $coordinates .= $dbRow['prov'];
	    $coordinates .= "&nbsp;${dbRow['postcode']}";
	    $coord_sep = "<br />";
	    $coordinates .= $coord_sep . $dbRow['country'];

	    if($dbRow['phone'])
	    {
	    	$coordinates .= $coord_sep . "Phone: " . $dbRow['phone'];
	    }

	    if($dbRow['fax'])
	    {
	    	$coordinates .= $coord_sep . "Fax: ". $dbRow['fax'];
	    }

	    $page = "<TR valign='top'><TD><FONT face='Arial,Helvetica,Sans Serif'><B>${name}</B></FONT></TD>";
	    $page .= "<TD>" . $coordinates;

	    // Do not urlencode these - that is only for parms.  urls that contain '/' are encoded, which breaks them.
	    // This opens the potential for bad behaviour if our stored url contains parms
	    if($dbRow['web'])
	    {
	    	$page .= "<br /><A HREF='http://${dbRow['web']}' TARGET='_rlwebref'>${dbRow['web']}</A>";
	    }

	    if($dbRow['email'])
	    {
	    	$page .= "<br /><A HREF='mailto:${dbRow['email']}'>${dbRow['email']}</A>";
	    }

	    $page .= "<br />${desc} ". $catalogueCost;
	    $page .= "</TD></TR>\n";

	    return $page;
	}


	/*
	 * Name:	showSeedSuppliers()
	 * Purpose:	This function displays the seed suppliers to Seeds of diversity. They are arranged alphabetically and grouped
	 * 			in Canada, USA, or other. Previously known as country_list().
	 * Inputs:	$country	 	- The name of the country as stored in the database.
	 * 			$countryName 	- The name of the country as displayed to the user on the web page.
	 * 			$lang			- The default language of the web page
	 * Outputs:	Displays all available companies in the database who supply seeds
	 * Returns:	None
	 */
	function showSeedSuppliers($country, $countryName, $lang)
	{
		$dbObject = new KeyFrameDB(SiteKFDB_HOST, SiteKFDB_USERID, SiteKFDB_PASSWORD);

		$webpage = "<HR><P class='rl_country'><B>" . $countryName . "</B></P>";  // display country as a title
		$webpage .= "<TABLE cellpadding='10' cellspacing='10'>";
		if ($dbObject->oConn) // successful user connection
		{
			$result = $dbObject->Connect("seeds");

			if ($result)  // successfully connected to the desired database
			{
				if ($country == "Other")
				{
					$query = "SELECT * FROM csci_company WHERE country!='Canada' AND country!='US' ORDER BY name_en";  // get countries
				}
				else  // Canada or US
				{
					$query = "SELECT * FROM csci_company WHERE country='" . $country . "' ORDER BY name_en";  // get countries
				}

				$result = $dbObject->CursorOpen($query);

				if (!$result)  // bad query
				{
					$dbObject->_errmsg = "query failed";
					$webpage = "<p>There is no information available at this time</p>";
					return $webpage;
				}
				else
				{
					$row = $dbObject->CursorFetch($result);  // get a row from the database

					while ($row)
					{
						$companyContent = $this->displayCompany($row, $lang);	// display info from row
						$webpage .= $companyContent;					// adds company info to the main page info
						$row = $dbObject->CursorFetch($result);			// get another row
					}
				}
			}
			else
			{
				$dbObject->_errmsg = "Can't connect to seeds database";
				$webpage = "<p>There is no information available at this time</p>";
				return $webpage;
			}
		}
		else
		{
			$webpage = "<p>There is no information available at this time</p>";
			return $webpage;
		}

		$webpage .= "</TABLE>";

		return $webpage;
	}


}

function SL_CSCI_DrawPublicPage($kfdb, $lang)
	{
		$oCSCI = new SL_CSCI( $kfdb,$lang );
		return($oCSCI->DrawText());
	}


/*
 * 	This function uses the ResourceList class and builds the module for the webpage
 */
function executePage()
{
	$resourceList = new ResourceList();
	$currentLanguage = $resourceList->getLanguage();

	// get the page content
	$webPage = $resourceList->showSeedSuppliers("Canada", "Canada", $currentLanguage);
	$webPage .= $resourceList->showSeedSuppliers("US", $currentLanguage=="EN" ? "United States" : "�tats-Unis", $currentLanguage);
	$webPage .= $resourceList->showSeedSuppliers("Other", "Other", $currentLanguage);

	return $webPage;
}

?>
