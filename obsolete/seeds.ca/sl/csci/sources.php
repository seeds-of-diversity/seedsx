<?php

/* Matt Potts
 * 2011-07-14
 * sources.php
 * This is the page which displays the resource list containing affiliated companies and their locations.
 *
 */

// this page is used standalone and included from others, so constants may or may not be defined
define("SITEROOT","../../");
include_once( SITEROOT."site.php" );
include_once( SEEDCOMMON."siteStart.php" );
include_once( SEEDCOMMON."sl/csci.php" );
include_once( PAGE1_TEMPLATE );

$sYear = date( "Y", time() + (3600*24*60) );	// Advance the year 60 days before the new year
$title_en = "Source List - $sYear";			// english and french titles
$title_fr = "Liste des Sources - $sYear";

if (empty($lang))
{
	$lang = strtoupper(@$_REQUEST["lang"]); 	// ensure string is upper case for next comparison
}

if ($lang != "FR")	// if not french, force english
{
	$lang = "EN";
}

if ($lang != "FR")
{
	$pageTitle = $title_en;
}
else
{
	$pageTitle = $title_fr;
}

/*
 * 	Invoke Page1 to draw the events list with banners, etc
 */
function Page1Body()
{
	rl_list();
}


/*
 * 	Displays options in the side bar as a menu
 */
function Box1Fn()
{
	global $lang;

    if( $lang == "EN" )
    {
    	$s = "<div><a href='" . SITEROOT . "rl/rl.php'>Resource List</a></div>" .
             "<div><a href='" . SITEROOT . "rl/rl_prn.php' TARGET='rl-prn-window'>Resource List - printable version</a></div>";
    }
    else // french
    {
     	$s = "<div><a href='" . SITEROOT . "rl/lr.php'>Liste des Sources</a></div>" .
             "<div><a href='" . SITEROOT . "rl/lr_prn.php' TARGET='rl-prn-window'>Liste des Sources - Version imprimable</a></div>";
    }

    return( $s );
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

	echo "<hr><p class='rl_country'><b>" . $countryName . "</b></p>";  // display country as a title
	echo "<table cellpadding='10' cellspacing='10'>";

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
				echo "<p>There is no information available at this time</p>";
			}
			else
			{
				$row = $dbObject->CursorFetch($result);  // get a row from the database

				while ($row)
				{
					displayCompany($row, $lang);				// display info from row
					$row = $dbObject->CursorFetch($result);		// get another row
				}
			}
		}
		else
		{
			$dbObject->_errmsg = "Can't connect to seeds database";
			echo "<p>There is no information available at this time</p>";
		}
	}
	else
	{
		$dbObject->_errmsg = "bad user";
		echo "<p>There is no information available at this time</p>";
	}

	echo "</table>";
}


/*
 * Displays the body of the page (Mostly Bob's code)
 */
function rl_list()
{
	global $lang, $pageTitle;

	echo "<h2>$pageTitle</h2>";

	// display paragraph content
	if( $lang == "FR" )
	{
		echo "<div>
				<p>Les graineteries et p�pini�res suivantes vendent des vari�t�s de l�gumes,
	            de fruits, de fleurs, de fines herbes et d'herbes m�dicinales du patrimoine, rares ou menac�es.
	            Certains catalogues d�crivent et indiquent clairement leurs semences traditionnelles; toutefois, d'autres
	            ne le mentionnent pas. Vous devez savoir ce que vous recherchez.</p>

	            <p>Lorsqu'il s'agit d'entreprises canadiennes, les montants sont indiqu�s en dollars canadiens.
	            Les compagnies am�ricaines ou europ�ennes feront parvenir les sachets de semences aux
	            r�sidents canadiens sans probl�me, mais les <b>douanes canadiennes refuseront et confisqueront toute importation de bulbes, plantules, rhiz�mes,
	            tubercules, pommes de terre ou plantes vivantes en pot ou en sac</b>. Il faut comprendre que les maladies fongiques ou la pr�sence d'intrus microscopiques ou non, nuisibles
	            � l'environnement sont � redouter, d'o� les pr�cautions quant � l'importation
	            de ces v�g�taux. Certaines exceptions sont possibles, mais il vous faudra v�rifier
	            auparavant avec le bureau d'Agriculture Canada.  Dans le cas d'entreprises �trang�res, les
	            graineteries r�put�es mondialement peuvent parfois fournir un certificat phytosanitaire �
	            Agriculture Canada; renseignez-vous aupr�s de l'entreprise commerciale sur cette possibilit�.</p>

	            <p>Puisque certaines banques am�ricaines exigent des sommes consid�rables pour encaisser les
	            ch�ques �trangers, plusieurs graineteries am�ricaines n'acceptent pas les ch�ques
	            canadiens. Il sera alors pr�f�rable d'effectuer votre paiement par mandat-poste ou par carte de
	            cr�dit. Dans le cas des compagnies europ�ennes, une traite bancaire ou un paiement par carte de
	            cr�dit est envoy� � la graineterie, dans la devise du pays.</p>

	            <p>Cette liste alphab�tique, selon chaque pays, est p�riodiquement remise � jour. Si vous
	            connaissez d'autres graineteries ou si des changements devaient �tre apport�s � cette liste,
	            n'h�sitez pas � communiquer par courriel � <a HREF='mailto:courriel@semences.ca'>courriel@semences.ca</a></p>

	            <p>Le nom d'une compagnie dans cette liste ne constitue en aucun cas une recommandation par le Programme
	            Semencier du Patrimoine, et nous ne cautionnons aucunement l'exactitude de son contenu.
	            <b>Cette liste ne doit servir qu'� informer seulement</b>.</p>";

       	echo "<p><a HREF='rl_prn.php' TARGET='rl-prn-window' ><b><i>Version imprimable de cette liste</i></b></a></p>";
	}
	else // default to English
	{
		echo "<div>
			    <p>The following seed companies and nurseries sell heirloom and rare or endangered varieties of vegetables, fruits,
	            flowers and herbs. In some catalogues, the heirloom varieties are noted as such, but in others they are not, so you
	            have to know what you are looking for.</p>

	            <p>All prices are in Canadian funds unless specified. The U.S. seed companies listed here will send seeds to Canada,
	            but usually cannot ship living plants, bulbs, potatoes, etc across the border.</p>

	            <p>Since some U.S. banks charge a large fee to cash non-U.S. cheques, many U.S. seed companies cannot accept cheques
	            from Canadian banks. It is often best to make your payments with money orders instead.</p>

	            <p>This resource list is updated regularly. If you know of other resources or changes that should be made to this
	            information, please e-mail <a HREF='mailto:office@seeds.ca'>office@seeds.ca</a></p>

	            <p>Inclusion of any company in this list does not constitute a recommendation by Seeds of Diversity Canada and no
	            claims are made to accuracy.  <b>This list is for information purposes only.</b></p>
	          </div>";

		echo "<p><a HREF='rl_prn.php' TARGET='rl-prn-window' ><b><i>Printable Version of this List</i></b></a></p>";
	}

	showSeedSuppliers("Canada", "Canada", $lang);
	showSeedSuppliers("US", $lang=="EN" ? "United States" : "�tats-Unis", $lang);
	showSeedSuppliers("Other", "Other", $lang);
}


// sets parameter array
$page1parms = array ("lang"     => $lang,
                    "title"     => $pageTitle,
                    "tabname"   => "Library",
                    "box1title" => ($lang != "FR" ? "Library" : "Biblioth&egrave;que"),
                    "box1fn"    => "Box1Fn"
                  /*"box2title" => "Box2Title",
                 	"box2text"  => "Box2Text",
                 	"box2fn"    => "Box2Fn"*/	);


Page1( $page1parms );


/*
 * Name:	displayCompany()
 * Purpose:	Prints a supplier record to the web page
 * Inputs:	$dbRow	- Database row recieved from a query.
 * 			$lang	- Current language
 * Outputs:	Outputs a single, formatted company record to the web page.
 * Returns:	None
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
    		$catalogueCost = " The catologue costs $" . $dbRow['cat_cost'];
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

    echo "<tr valign='top'><td><FONT face='Arial,Helvetica,Sans Serif'><b>${name}</b></FONT></td>";  // <FONT> tag will need to be changed for HTML5 compatibility
    echo "<td>$coordinates";

    // Do not urlencode these - that is only for parms.  urls that contain '/' are encoded, which breaks them.
    // This opens the potential for bad behaviour if our stored url contains parms
    if($dbRow['web'])
    {
    	echo "<br /><a HREF='http://${dbRow['web']}' TARGET='_rlwebref'>${dbRow['web']}</a>";
    }

    if($dbRow['email'])
    {
    	echo "<br /><a HREF='mailto:${dbRow['email']}'>${dbRow['email']}</a>";
    }

    echo "<br />${desc} ". $catalogueCost;
    echo "</td></tr>\n";
}

?>