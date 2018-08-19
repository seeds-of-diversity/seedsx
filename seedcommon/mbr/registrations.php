<?php

/* All conference registration definitions go here.
 * It might be nice to put this in a database, but at least this is nicer than the non-centrality of coding
 * the definitions in various places.
 */

class MbrRegistrations
{
    public $raRegistrations;

    function __construct()
    {
        $this->raRegistrations = array(

          /* ECOSGN Montreal Nov 7-9, 2014
           */
          'ecosgn2014' => array(
              'bActive'=>false,

              'header' => array(
                'EN' =>
                   "<table border='0' cellpadding='20' cellspacing='5'><tr>
                    <td valign='top' style='text-align:center'>
                      <img src='http://www.seeds.ca/d/?n=ecosgn/logo/logo01-en--300.png' width='150'/><br/>
                      <img src='http://www.seeds.ca/bauta/logo/BFICSS-logo-en-300.png' width='150'/><br/><br/>
                      <p style='font-weight:bold;font-size:12pt;text-align:center'>
                        <a target='_blank' href='http://www.seedsecurity.ca/en/119-ecosgn-conference'>Full conference information
                        is available on www.seedsecurity.ca</a></p>
                    </td><td valign='top'>
                      <p style='text-align: center;color:#778E1D;font-size:14pt;font-weight:bold'>SEED CONNECTIONS CONFERENCE</p>
                      <p style='color:rgb(121, 89, 59);font-weight:bold;font-size:12pt;'><strong>November 7<sup>th</sup>, 8<sup>th</sup>, 9<sup>th</sup>, 2014 </strong>
                      <br/><strong>MacDonald Campus of McGill University,&nbsp;</strong>
                      <br/><strong>Sainte-Anne-de-Bellevue, Quebec</strong></p>
                      <p>Building on the incredible success of their 2012 conference, The Eastern Canadian Organic Seed Growers' Network (ECOSGN) is partnering with
                      The Bauta Family Initiative on Canadian Seed Security and Seeds of Diversity Canada this year to host their second major ecological seed
                      conference for Eastern Canada.</p>
                      <p>ECOSGN's <i>Seed Connections</i> conference is a fully bilingual event bringing together farmers, seed-savers, seed companies,
                      community gardeners, researchers, and experts on organic seed production to share knowledge, skills, and experience over a packed, 3-day agenda!
                      Whether you are a beginner gardener or an expert seed producer, if you are interested in ecological seed in eastern Canada - this is the
                      conference to attend!</p>
                      <p><b>If you have dietary requests or mobility restrictions (and are attending the field trip), please let us know using the Notes section below.</p>
                    </td>
                    </tr></table>",
                'FR' =>
                   "<table border='0' cellpadding='20' cellspacing='5'><tr>
                    <td valign='top' style='text-align:center'>
                      <img src='http://www.seeds.ca/d/?n=ecosgn/logo/logo01-fr--300.png' width='150'/><br/>
                      <img src='http://www.seeds.ca/bauta/logo/BFICSS-logo-fr-300.png' width='150'/><br/><br/>
                      <p style='font-weight:bold;font-size:12pt;text-align:center'>
                        <a target='_blank' href='http://www.seedsecurity.ca/fr/121-ecosgn-symposium-de-semences-2014'>Plus d'informations &agrave;
                        www.semencessecures.ca</a></p>
                    </td><td valign='top'>
                      <p style='text-align: center;color:#778E1D;font-size:14pt;font-weight:bold'>CONNEXIONS SEMENCES</p>
                      <p style='color:rgb(121, 89, 59);font-weight:bold;font-size:12pt;'><strong>Les 7, 8 et 9 novembre 2014</strong>
                      <br/><strong>Le campus MacDonald de l'Universit&eacute; McGill,&nbsp;</strong>
                      <br/><strong>Sainte-Anne-de-Bellevue, Qu&eacute;bec</strong></p>
                      <p>Profitant de l'incroyable r&eacute;ussite du symposium qu'il a organis&eacute; en 2012, l'organisme ECOSGN (Le R&eacute;seau des
                      cultivateurs de semences biologiques de l'Est du Canada) fait &eacute;quipe cette ann&eacute;e avec l'Initiative de la famille Bauta sur
                      la s&eacute;curit&eacute; des semences au Canada pour animer son deuxi&egrave;me grand &eacute;v&eacute;nement sur les semences
                      &eacute;cologiques dans l'Est du Canada.</p>
                      <p>Connexion Semences : le Symposium de semences ECOSGN 2014 sera un &eacute;v&eacute;nement enti&egrave;rement bilingue qui r&eacute;unira
                      des agriculteurs, des conservateurs de semences, des semenci&egrave;res, des jardiniers communautaires, des chercheurs et des experts en
                      mati&egrave;re de production de semences biologiques. Tous y seront pour partager leurs connaissances, leurs comp&eacute;tences et leurs
                      exp&eacute;riences dans le cadre d'un programme de trois jours bien rempli! Que vous en soyez &agrave; vos premiers pas en mati&egrave;re de jardinage
                      ou que vous soyez un producteur de semences chevronn&eacute;, c'est l'&eacute;v&eacute;nement tout indiqu&eacute; si vous vous
                      int&eacute;ressez aux semences &eacute;cologiques dans l'Est du Canada.</p>
                      <p><b>Si vous avez des demandes sp&eacute;ciales pour un r&eacute;gime particulier ou une mobilit&eacute; r&eacute;duite (et que vous d&eacute;sirez participer &agrave; la visite guid&eacute;e), laissez-le nous savoir dans la section \"note\" ci-dessous.</p>
                    </td>
                    </tr></table>",
              ),
              'footer' => array(
                'EN' => "<p>Note: Please register for lunches and dinner before Nov 1. Meals may not be available for last-minute registrants.</p>",
                'FR' => "<p>Note: Please register for lunches and dinner before Nov 1. Meals may not be available for last-minute registrants.</p>"
              ),

              'nametag' => true,

              // concise text is only shown in the internal order report so it's only in English
              'tickets' => array(
                  'Friday' => array(
                      'type' => 'Ticket',
                      'formtextEN' => "Friday Field Day (includes lunch) Nov 7",
                      'formtextFR' => "Journ&eacute;e du vendredi sur le terrain (inclut d&icirc;ner) 7 novembre",
                      'concise'=>"ECOSGN 2014 Friday Field Day",
                      'full_EN'=>"ECOSGN Friday Field Day Nov 7, 2014",
                      'full_FR'=>"ECOSGN vendredi journ&eacute;e sur le terrain 7 novembre",
                      'price' => 70 ),

                  'SatSun' => array(
                      'type' => 'Ticket',
                      'formtextEN' => "Full Conference Pass (includes Sat & Sun lunch and Sat evening banquet) Nov 8-9",
                      'formtextFR' => "Laissez-passer complet pour le samedi & le dimanche (inclut les d&icirc;ners ainsi que le banquet) 8-9 novembre",
                      'concise'=>"ECOSGN 2014 conference regular",
                      'full_EN'=>"ECOSGN conference Nov 8-9, 2014",
                      'full_FR'=>"ECOSGN symposium 8-9 novembre",
                      'price' => 200 ),

                  'SatSun_Student' => array(
                      'type' => 'Ticket',
                      'formtextEN' => "Full Student Conference Pass (includes Sat & Sun lunch and Sat evening banquet.) Nov 8-9",
                      'formtextFR' => "Laissez-passer complet pour le samedi & le dimanche - <b>&eacute;tudiants</b> (inclut les d&icirc;ners ainsi que le banquet) 8-9 novembre",
                      'concise'=>"ECOSGN 2014 conference student",
                      'full_EN'=>"ECOSGN conference Nov 8-9, 2014 (student rate)",
                      'full_FR'=>"ECOSGN symposium 8-9 novembre (&eacute;tudiant)",
                      'price' => 125 ),

                  'Sat' => array(
                      'type' => 'Ticket',
                      'formtextEN' => "Saturday Day Pass (meals included)",
                      'formtextFR' => "Laissez-passer du samedi  (inclut les repas)",
                      'concise'=>"ECOSGN 2014 conference Sat",
                      'full_EN'=>"ECOSGN conference one day Sat Nov 8, 2014",
                      'full_FR'=>"ECOSGN symposium samedi 8 novembre",
                      'price' => 135 ),

                  'Sun' => array(
                      'type' => 'Ticket',
                      'formtextEN' => "Sunday Day Pass (lunch included)",
                      'formtextFR' => "Laissez-passer du dimanche (inclut le d&icirc;ner)",
                      'concise'=>"ECOSGN 2014 conference Sunday",
                      'full_EN'=>"ECOSGN conference one day Sun Nov 9, 2014",
                      'full_FR'=>"ECOSGN symposium dimanche 9 novembre",
                      'price' => 100 ),
              ),
          ),


          /* ECOSGN Montreal Nov 7-9, 2014  -- increased prices after Oct 24
           */
          'ecosgn2014-late' => array(
              'bActive'=>false,

              'header' => array(
                'EN' =>
                   "<table border='0' cellpadding='20' cellspacing='5'><tr>
                    <td valign='top' style='text-align:center'>
                      <img src='http://www.seeds.ca/d/?n=ecosgn/logo/logo01-en--300.png' width='150'/><br/>
                      <img src='http://www.seeds.ca/bauta/logo/BFICSS-logo-en-300.png' width='150'/><br/><br/>
                      <p style='font-weight:bold;font-size:12pt;text-align:center'>
                        <a target='_blank' href='http://www.seedsecurity.ca/en/119-ecosgn-conference'>Full conference information
                        is available on www.seedsecurity.ca</a></p>
                    </td><td valign='top'>
                      <p style='text-align: center;color:#778E1D;font-size:14pt;font-weight:bold'>SEED CONNECTIONS CONFERENCE</p>
                      <p style='color:rgb(121, 89, 59);font-weight:bold;font-size:12pt;'><strong>November 7<sup>th</sup>, 8<sup>th</sup>, 9<sup>th</sup>, 2014 </strong>
                      <br/><strong>MacDonald Campus of McGill University,&nbsp;</strong>
                      <br/><strong>Sainte-Anne-de-Bellevue, Quebec</strong></p>
                      <p>Building on the incredible success of their 2012 conference, The Eastern Canadian Organic Seed Growers' Network (ECOSGN) is partnering with
                      The Bauta Family Initiative on Canadian Seed Security and Seeds of Diversity Canada this year to host their second major ecological seed
                      conference for Eastern Canada.</p>
                      <p>ECOSGN's <i>Seed Connections</i> conference is a fully bilingual event bringing together farmers, seed-savers, seed companies,
                      community gardeners, researchers, and experts on organic seed production to share knowledge, skills, and experience over a packed, 3-day agenda!
                      Whether you are a beginner gardener or an expert seed producer, if you are interested in ecological seed in eastern Canada - this is the
                      conference to attend!</p>
                      <p><b>If you have dietary requests or mobility restrictions (and are attending the field trip), please let us know using the Notes section below.</p>
                    </td>
                    </tr></table>",
                'FR' =>
                   "<table border='0' cellpadding='20' cellspacing='5'><tr>
                    <td valign='top' style='text-align:center'>
                      <img src='http://www.seeds.ca/d/?n=ecosgn/logo/logo01-fr--300.png' width='150'/><br/>
                      <img src='http://www.seeds.ca/bauta/logo/BFICSS-logo-fr-300.png' width='150'/><br/><br/>
                      <p style='font-weight:bold;font-size:12pt;text-align:center'>
                        <a target='_blank' href='http://www.seedsecurity.ca/fr/121-ecosgn-symposium-de-semences-2014'>Plus d'informations &agrave;
                        www.semencessecures.ca</a></p>
                    </td><td valign='top'>
                      <p style='text-align: center;color:#778E1D;font-size:14pt;font-weight:bold'>CONNEXIONS SEMENCES</p>
                      <p style='color:rgb(121, 89, 59);font-weight:bold;font-size:12pt;'><strong>Les 7, 8 et 9 novembre 2014</strong>
                      <br/><strong>Le campus MacDonald de l'Universit&eacute; McGill,&nbsp;</strong>
                      <br/><strong>Sainte-Anne-de-Bellevue, Qu&eacute;bec</strong></p>
                      <p>Profitant de l'incroyable r&eacute;ussite du symposium qu'il a organis&eacute; en 2012, l'organisme ECOSGN (Le R&eacute;seau des
                      cultivateurs de semences biologiques de l'Est du Canada) fait &eacute;quipe cette ann&eacute;e avec l'Initiative de la famille Bauta sur
                      la s&eacute;curit&eacute; des semences au Canada pour animer son deuxi&egrave;me grand &eacute;v&eacute;nement sur les semences
                      &eacute;cologiques dans l'Est du Canada.</p>
                      <p>Connexion Semences : le Symposium de semences ECOSGN 2014 sera un &eacute;v&eacute;nement enti&egrave;rement bilingue qui r&eacute;unira
                      des agriculteurs, des conservateurs de semences, des semenci&egrave;res, des jardiniers communautaires, des chercheurs et des experts en
                      mati&egrave;re de production de semences biologiques. Tous y seront pour partager leurs connaissances, leurs comp&eacute;tences et leurs
                      exp&eacute;riences dans le cadre d'un programme de trois jours bien rempli! Que vous en soyez &agrave; vos premiers pas en mati&egrave;re de jardinage
                      ou que vous soyez un producteur de semences chevronn&eacute;, c'est l'&eacute;v&eacute;nement tout indiqu&eacute; si vous vous
                      int&eacute;ressez aux semences &eacute;cologiques dans l'Est du Canada.</p>
                      <p><b>Si vous avez des demandes sp&eacute;ciales pour un r&eacute;gime particulier ou une mobilit&eacute; r&eacute;duite (et que vous d&eacute;sirez participer &agrave; la visite guid&eacute;e), laissez-le nous savoir dans la section \"note\" ci-dessous.</p>
                    </td>
                    </tr></table>",
              ),
              'footer' => array(
                'EN' => "<p>Note: Please register for lunches and dinner before Nov 1. Meals may not be available for last-minute registrants.</p>",
                'FR' => "<p>Note: Please register for lunches and dinner before Nov 1. Meals may not be available for last-minute registrants.</p>"
              ),

              'nametag' => true,

              // concise text is only shown in the internal order report so it's only in English
              'tickets' => array(
/*                  'Friday' => array(
                      'type' => 'Ticket',
                      'formtextEN' => "Friday Field Day (includes lunch) Nov 7",
                      'formtextFR' => "Journ&eacute;e du vendredi sur le terrain (inclut d&icirc;ner) 7 novembre",
                      'concise'=>"ECOSGN 2014 Friday Field Day",
                      'full_EN'=>"ECOSGN Friday Field Day Nov 7, 2014",
                      'full_FR'=>"ECOSGN vendredi journ&eacute;e sur le terrain 7 novembre",
                      'price' => 70 ),
*/
                  'SatSun-late' => array(
                      'type' => 'Ticket',
                      'formtextEN' => "Full Conference Pass (includes Sat & Sun lunch and Sat evening banquet) Nov 8-9",
                      'formtextFR' => "Laissez-passer complet pour le samedi & le dimanche (inclut les d&icirc;ners ainsi que le banquet) 8-9 novembre",
                      'concise'=>"ECOSGN 2014 conference regular",
                      'full_EN'=>"ECOSGN conference Nov 8-9, 2014",
                      'full_FR'=>"ECOSGN symposium 8-9 novembre",
                      'price' => 220 ),

                  'SatSun_Student-late' => array(
                      'type' => 'Ticket',
                      'formtextEN' => "Full Student Conference Pass (includes Sat & Sun lunch and Sat evening banquet.) Nov 8-9",
                      'formtextFR' => "Laissez-passer complet pour le samedi & le dimanche - <b>&eacute;tudiants</b> (inclut les d&icirc;ners ainsi que le banquet) 8-9 novembre",
                      'concise'=>"ECOSGN 2014 conference student",
                      'full_EN'=>"ECOSGN conference Nov 8-9, 2014 (student rate)",
                      'full_FR'=>"ECOSGN symposium 8-9 novembre (&eacute;tudiant)",
                      'price' => 140 ),

                  'Sat-late' => array(
                      'type' => 'Ticket',
                      'formtextEN' => "Saturday Day Pass (meals included)",
                      'formtextFR' => "Laissez-passer du samedi  (inclut les repas)",
                      'concise'=>"ECOSGN 2014 conference Sat",
                      'full_EN'=>"ECOSGN conference one day Sat Nov 8, 2014",
                      'full_FR'=>"ECOSGN symposium samedi 8 novembre",
                      'price' => 145 ),

                  'Sun-late' => array(
                      'type' => 'Ticket',
                      'formtextEN' => "Sunday Day Pass (lunch included)",
                      'formtextFR' => "Laissez-passer du dimanche (inclut le d&icirc;ner)",
                      'concise'=>"ECOSGN 2014 conference Sunday",
                      'full_EN'=>"ECOSGN conference one day Sun Nov 9, 2014",
                      'full_FR'=>"ECOSGN symposium dimanche 9 novembre",
                      'price' => 110 ),
              ),
          ),

          'ecosgn2014speaker' => array(
              'bActive'=>false,

              'header' => array(
                'EN' =>
                   "<table border='0' cellpadding='20' cellspacing='5'><tr>
                    <td valign='top' style='text-align:center'>
                      <img src='http://www.seeds.ca/d/?n=ecosgn/logo/logo01-en--300.png' width='150'/><br/>
                      <img src='http://www.seeds.ca/bauta/logo/BFICSS-logo-en-300.png' width='150'/><br/><br/>
                      <p style='font-weight:bold;font-size:12pt;text-align:center'>
                        <a target='_blank' href='http://www.seedsecurity.ca/en/119-ecosgn-conference'>Full conference information
                        is available on www.seedsecurity.ca</a></p>
                    </td><td valign='top'>
                      <p style='text-align: center;color:#778E1D;font-size:14pt;font-weight:bold'>SEED CONNECTIONS CONFERENCE</p>
                      <p style='color:rgb(121, 89, 59);font-weight:bold;font-size:12pt;'><strong>November 7<sup>th</sup>, 8<sup>th</sup>, 9<sup>th</sup>, 2014 </strong>
                      <br/><strong>MacDonald Campus of McGill University,&nbsp;</strong>
                      <br/><strong>Sainte-Anne-de-Bellevue, Quebec</strong></p>
                      <p>Building on the incredible success of their 2012 conference, The Eastern Canadian Organic Seed Growers' Network (ECOSGN) is partnering with
                      The Bauta Family Initiative on Canadian Seed Security and Seeds of Diversity Canada this year to host their second major ecological seed
                      conference for Eastern Canada.</p>
                      <p>ECOSGN's <i>Seed Connections</i> conference is a fully bilingual event bringing together farmers, seed-savers, seed companies,
                      community gardeners, researchers, and experts on organic seed production to share knowledge, skills, and experience over a packed, 3-day agenda!
                      Whether you are a beginner gardener or an expert seed producer, if you are interested in ecological seed in eastern Canada - this is the
                      conference to attend!</p>
                      <p><b>If you have dietary requests or mobility restrictions (and are attending the field trip), please let us know using the Notes section below.</p>
                    </td>
                    </tr></table>
                    <p style='border:1px solid #888;border-radius:5px;margin:10px;padding:10px;font-size:large;text-align:center'><strong>Registration for Conference Speakers Only</strong><br/><br/>Regular conference registration is at <a href='http://www.seeds.ca/ecosgn'>www.seeds.ca/ecosgn</a></p>
                    ",
                'FR' =>
                   "<table border='0' cellpadding='20' cellspacing='5'><tr>
                    <td valign='top' style='text-align:center'>
                      <img src='http://www.seeds.ca/d/?n=ecosgn/logo/logo01-fr--300.png' width='150'/><br/>
                      <img src='http://www.seeds.ca/bauta/logo/BFICSS-logo-fr-300.png' width='150'/><br/><br/>
                      <p style='font-weight:bold;font-size:12pt;text-align:center'>
                        <a target='_blank' href='http://www.seedsecurity.ca/fr/121-ecosgn-symposium-de-semences-2014'>Plus d'informations &agrave;
                        www.semencessecures.ca</a></p>
                    </td><td valign='top'>
                      <p style='text-align: center;color:#778E1D;font-size:14pt;font-weight:bold'>CONNEXIONS SEMENCES</p>
                      <p style='color:rgb(121, 89, 59);font-weight:bold;font-size:12pt;'><strong>Les 7, 8 et 9 novembre 2014</strong>
                      <br/><strong>Le campus MacDonald de l'Universit&eacute; McGill,&nbsp;</strong>
                      <br/><strong>Sainte-Anne-de-Bellevue, Qu&eacute;bec</strong></p>
                      <p>Profitant de l'incroyable r&eacute;ussite du symposium qu'il a organis&eacute; en 2012, l'organisme ECOSGN (Le R&eacute;seau des
                      cultivateurs de semences biologiques de l'Est du Canada) fait &eacute;quipe cette ann&eacute;e avec l'Initiative de la famille Bauta sur
                      la s&eacute;curit&eacute; des semences au Canada pour animer son deuxi&egrave;me grand &eacute;v&eacute;nement sur les semences
                      &eacute;cologiques dans l'Est du Canada.</p>
                      <p>Connexion Semences : le Symposium de semences ECOSGN 2014 sera un &eacute;v&eacute;nement enti&egrave;rement bilingue qui r&eacute;unira
                      des agriculteurs, des conservateurs de semences, des des semenci&egrave;res, des jardiniers communautaires, des chercheurs et des experts en
                      mati&egrave;re de production de semences biologiques. Tous y seront pour partager leurs connaissances, leurs comp&eacute;tences et leurs
                      exp&eacute;riences dans le cadre d'un programme de trois jours bien rempli! Que vous en soyez &agrave; vos premiers pas en mati&egrave;re de jardinage
                      ou que vous soyez un producteur de semences chevronn&eacute;, c'est l'&eacute;v&eacute;nement tout indiqu&eacute; si vous vous
                      int&eacute;ressez aux semences &eacute;cologiques dans l'Est du Canada.</p>
                      <p><b>Si vous avez des demandes sp&eacute;ciales pour un r&eacute;gime particulier ou une mobilit&eacute; r&eacute;duite (et que vous d&eacute;sirez participer &agrave; la visite guid&eacute;e), laissez-le nous savoir dans la section \"note\" ci-dessous.</p>
                    </td>
                    </tr></table>
                    <p style='border:1px solid #888;border-radius:5px;margin:10px;padding:10px;font-size:large;text-align:center'><strong>Registration for Conference Speakers Only</strong><br/><br/>Regular conference registration is at <a href='http://www.seeds.ca/ecosgn'>www.seeds.ca/ecosgn</a></p>
                    ",
              ),
              'footer' => array(
                'EN' => "<p>Note: Please register for lunches and dinner before Nov 1. Meals may not be available for last-minute registrants.</p>",
                'FR' => "<p>Note: Please register for lunches and dinner before Nov 1. Meals may not be available for last-minute registrants.</p>"
              ),

              'nametag' => true,

              // concise text is only shown in the internal order report so it's only in English
              'tickets' => array(
/*                  'Friday' => array(
                      'type' => 'Ticket',
                      'formtextEN' => "Friday Field Day (includes lunch) Nov 7",
                      'formtextFR' => "Journ&eacute;e du vendredi sur le terrain (inclut d&icirc;ner) 7 novembre",
                      'concise'=>"ECOSGN 2014 Friday Field Day",
                      'full_EN'=>"ECOSGN Friday Field Day Nov 7, 2014",
                      'full_FR'=>"ECOSGN vendredi journ&eacute;e sur le terrain 7 novembre",
                      'price' => 70 ),
*/
                  'SatSun' => array(
                      'type' => 'Ticket',
                      'formtextEN' => "Full Conference Pass (includes all meals) - SPEAKER Nov 8-9",
                      'formtextFR' => "Laissez-passer complet (inclut tous les repas) - PR&Eacute;SENTATEUR 8-9 novembre",
                      'concise'=>"ECOSGN 2014 conference SPEAKER",
                      'full_EN'=>"ECOSGN conference 2014 SPEAKER",
                      'full_FR'=>"ECOSGN symposium 2014 SPEAKER",
                      'price' => 0 ),
              ),
          ),


          /* Victoria 2014 AGM
           */
          'Victoria2014' => array(
              'bActive' => false,

              'header' => array(
                'EN' => ("<p><b>Seeds of Diversity Gala 30th Anniversary</b><br/><b>Victoria BC, Sunday October 26, 2014</b></p>"
                        ."<p style='margin-left:2em'>See <a href='http://www.seeds.ca/events'>our events list</a> for details. "
                        ."Pre-registration includes workshops, special guest speaker, and anniversary luncheon.</p>"),
               // if !isset shows english instead 'FR' => (),
              ),
              'footer' => array(
                'EN' => "<p>Note: Please register before October 21. Lunch may not be available for last-minute registrants.</p>",
              ),

              'tickets' => array(
                  'reg' => array(
                      'type' => 'Ticket',
                      'formtextEN' => "Registration for Workshops, Speaker, and Luncheon",
                      'concise'=>"Victoria AGM/Workshops/Lunch",
                      'fullEN'=>"Victoria October 26, registration for workshops, speaker, luncheon",
                      'price' => 40 ),
              ),
              // choices appended to lines written in the ticket
              'radio' => array(
                  'workshop' => array( 'one' => array( 'formtextEN' => "Workshop #1: Seed Saving", 'ticketEN' => "Workshop #1: Seed Saving" ),
                                       'two' => array( 'formtextEN' => "Workshop #2: Roundtable Discussion with Seed Companies", 'ticketEN' => "Workshop #2: Roundtable" ),
                  ),
              ),
          ),

          /* Saskatoon friendraiser 2013
           */
          'Saskatoon2013' => array(
              'bActive' => false,

              'header' => array(
                'EN' =>
                    ("<P><B>Seeds in Saskatoon, Saturday November 9 2013</B></P>"
                     ."<p style='margin-left:2em'>Meet Seeds of Diversity and Enjoy a great Seedy Day<br/>"
                     ."3:30pm - 8:30pm, come and go or stay the whole time <br/>"
                     ."<a href='http://www.seeds.ca/events' target='_blank'>See Details</a></p>"),
// if FR !isset the EN text is used instead
//                'FR' =>
//                    (""
//                    .""),
              ),
              'footer' => array(
                'EN' => "<p>Note: Please register for dinner before Nov 5. Meals may not be available for last-minute registrants.</p>",
              ),
              'nametag' => true,

              'tickets' => array(
                  'WorkshopBasic' => array(
                      'type' => 'Ticket',
                      'formtextEN' => "Seed Saving Workshop (3:30-4:30pm)",
                      'concise'=>"Saskatoon Seed Workshop afternoon",
                      'fullEN'=>"Saskatoon Nov 9, Seed Saving Workshop (3:30-4:30pm)",
                      'price' => 10 ),

                  'WorkshopAdvanced' => array(
                      'type' => 'Ticket',
                      'formtextEN' => "Seed Saving Workshop, Advanced (7:00-8:30pm)",
                      'concise'=>"Saskatoon Seed Workshop evening",
                      'fullEN'=>"Saskatoon Nov 9, Seed Saving Workshop, Advanced (7:00-8:30pm)",
                      'price' => 10 ),

                  'Dinner' => array(
                      'type' => 'Ticket',
                      'formtext_EN' => "Buffet dinner (5:00-7:00pm)",
                      'concise'=>"Saskatoon Dinner ticket",
                      'full_EN'=>"Saskatoon Nov 9, Buffet dinner (5:00-7:00pm)",
                      'price' => 20 ),
              ),
          ),

          /* ECOSGN Montreal conference Nov 9-11, 2012
           */
          'Montreal2012' => array(
              'bActive'=>false,

              'header' => array(
                'EN' =>
                    ("<P><B>Seed Connections: Broadcasting Seed from Coast to Coast, Nov 9-11 2012</B>".SEEDStd_StrNBSP("",5)
                     ."<A href='http://www.seeds.ca/ecosgn' target='_blank'>Details</A></P>"
                     ."<P>A conference for novice and expert seed producers held simultaneously in Vancouver and Montreal.</P>"
                     ."<P style='margin-left:2em;'>For <U>Vancouver</U> register with <A href='http://www.bcseeds.org'>BC Seeds</A></P>"
                     ."<P style='margin-left:2em;'>For <U>Montreal</U> register here</P>"),
                'FR' =>
                     ("<P><B>Seed Connections: Broadcasting Seed from Coast to Coast, Nov 9-11 2012</B>".SEEDStd_StrNBSP("",5)
                      ."<A href='http://www.seeds.ca/conference/Montreal_121109_EN.pdf' target='_blank'>Details</A></P>"
                      ."<P>A conference for novice and expert seed producers held simultaneously in Vancouver and Montreal.</P>"
                      ."<P style='margin-left:2em;'>For <U>Vancouver</U> register with <A href='http://www.bcseeds.org'>BC Seeds</A></P>"
                      ."<P style='margin-left:2em;'>For <U>Montreal</U> register here</P>")
              ),
              'footer' => array(
                'EN' => "<p>Note: Please register for lunches and dinner before Nov 7. Meals may not be available for last-minute registrants.</p>",
                'FR' => "<p>Note: Please register for lunches and dinner before Nov 7. Meals may not be available for last-minute registrants.</p>"
              ),

              'nametag' => true,

              // concise text is only shown in the internal order report so it's only in English
              'tickets' => array(
                  'Friday' => array(
                      'type' => 'Ticket',
                      'formtextEN' => "Friday full-day preconference seed course and on-farm seed cleaning workshop (lunch not included)",
                      'formtextFR' => "Friday full-day preconference seed course and on-farm seed cleaning workshop (lunch not included)",
                      'concise'=>"ECOSGN 2012 preconference",
                      'full_EN'=>"ECOSGN preconference Nov 9, 2012",
                      'full_FR'=>"ECOSGN preconference Nov 9, 2012",
                      'price' => 60 ),

                  'SatSun' => array(
                      'type' => 'Ticket',
                      'formtextEN' => "Saturday and Sunday conference, lunch included both days (Regular rate)",
                      'formtextFR' => "Saturday and Sunday conference, lunch included both days (Regular rate)",
                      'concise'=>"ECOSGN 2012 conference regular",
                      'full_EN'=>"ECOSGN conference Nov 10-11, 2012",
                      'full_FR'=>"ECOSGN conference Nov 10-11, 2012",
                      'price' => 110 ),

                  'SatSun_Student' => array(
                      'type' => 'Ticket',
                      'formtextEN' => "Saturday and Sunday conference, lunch included both days (Student rate: ID required at the door)",
                      'formtextFR' => "Saturday and Sunday conference, lunch included both days (Student rate: ID required at the door)",
                      'concise'=>"ECOSGN 2012 conference student",
                      'full_EN'=>"ECOSGN conference Nov 10-11, 2012 (student rate)",
                      'full_FR'=>"ECOSGN conference Nov 10-11, 2012 (student rate)",
                      'price' => 50 ),

                  'Sat' => array(
                      'type' => 'Ticket',
                      'formtextEN' => "One day: Saturday only, lunch included",
                      'formtextFR' => "One day: Saturday only, lunch included",
                      'concise'=>"ECOSGN 2012 conference Sat",
                      'full_EN'=>"ECOSGN conference one day Sat Nov 10, 2012",
                      'full_FR'=>"ECOSGN conference one day Sat Nov 10, 2012",
                      'price' => 65 ),

                  'Sun' => array(
                      'type' => 'Ticket',
                      'formtextEN' => "One day: Sunday only, lunch included",
                      'formtextFR' => "One day: Sunday only, lunch included",
                      'concise'=>"ECOSGN 2012 conference Sun",
                      'full_EN'=>"ECOSGN conference one day Sun Nov 11, 2012",
                      'full_FR'=>"ECOSGN conference one day Sun Nov 11, 2012",
                      'price' => 65 ),

                  'SatDinner' => array(
                      'type' => 'Dinner',
                      'formtextEN' => "Saturday fundraising dinner for Seeds of Diversity and keynote speaker (includes a partial tax receipt)",
                      'formtextFR' => "Saturday fundraising dinner for Seeds of Diversity and keynote speaker (includes a partial tax receipt)",
                      'concise'=>"ECOSGN 2012 saturday dinner",
                      'full_EN'=>"ECOSGN conference Nov 10, 2012 fundraising dinner",
                      'full_FR'=>"ECOSGN conference Nov 10, 2012 fundraising dinner",
                      'price' => 55 ),
              ),


              'sometext' => array(
                'EN' =>
                    (""),
                'FR' =>
                    ("")
              )
          ),

          /* 25th Anniversary Party in Montreal
           */
         //   .(($this->oL->GetLang() == "EN") ?
         //   ("<P><B>25th Anniversary Party, October 3, Montr�al</B>".SEEDStd_StrNBSP("",5)
         //   ."<A href='http://www.seeds.ca/conference/Montreal_091003_EN.jpg' target='_blank'>Details</A></P>"
         //   ."<P style='margin-left:2em;'>".$this->oKForm->Text("nMontrealFete2009","",array("size"=>5))." # tickets ($40)</P>"
         //   ) :
         //   ("<P><B>F&ecirc;te du 25&egrave;me anniversaire, le samedi 3 Octobre, Montr�al</B>".SEEDStd_StrNBSP("",5)
         //   ."<A href='http://www.seeds.ca/conference/Montreal_091003_FR.pdf' target='_blank'>Details</A></P>"
         //   ."<P style='margin-left:2em;'>".$this->oKForm->Text("nMontrealFete2009","",array("size"=>5))." Combien de billets (40 $)</P>"
         //   ) )

          /* ECOSGN seed course 2010
           */
         //   ."<HR>"
         //   ."<P><B>".($this->oL->GetLang() == "EN" ? "Eastern Canadian Organic Seed Growers Network" : "R&eacute;seau de Semenciers de L'Est du Canada")."</B>".SEEDStd_StrNBSP("",5)
         //   .($this->oL->GetLang() ==  "EN"
         //       ? "<A href='http://www.seeds.ca/conference/ECOSGN_0910_Seed_Course.pdf' target='_blank'>Details</A>"
         //       : "<A href='http://www.seeds.ca/conference/ECOSGN_0910_Cours_de_semences.pdf' target='_blank'>Details</A>" )
         //   ."</P>"
         //   ."<P style='margin-left:2em;'>".$this->oMbrOrder->raRegistrations['reg_ecosgn_0910']['full_'.$this->oL->GetLang()]
         //   ."<BR/>".$this->oKForm->Text('reg_ecosgn_0910',"",array("size"=>5))
         //   ." ".($this->oL->GetLang() == "EN" ? " # tickets" : " Combien de billets")
         //   ." @ ".$this->oMbrOrder->dollar($this->oMbrOrder->raRegistrations['reg_ecosgn_0910']['amount'])
         //   ."<BR/><BR/>To book lodging, please pay $25 per night in the Miscellaneous/Paiement divers section below.</P>"


          /* ECOSGN seed course 2009
           */

    /*
                 ."<P align='center'>".$this->oL->S('see_descriptions_here')."</P>"
                 ."<P><B>".($this->oL->GetLang() == "EN" ? "Eastern Canadian Organic Seed Growers Network" : "R&eacute;seau de Semenciers de L'Est du Canada")."</B>".SEEDStd_StrNBSP("",5)
                 .($this->oL->GetLang() ==  "EN"
                     ? "<A href='http://www.seeds.ca/conference/ECOSGN_0910_Seed_Course.pdf' target='_blank'>Details</A>"
                     : "<A href='http://www.seeds.ca/conference/ECOSGN_0910_Cours_de_semences.pdf' target='_blank'>Details</A>" )
                 ."</P>"
                 ."<P style='margin-left:2em;'>".$this->oMbrOrder->raRegistrations['reg_ecosgn_0910']['full_'.$this->oL->GetLang()]
                 ."<BR/>".$this->oKForm->Text('reg_ecosgn_0910',"",array("size"=>5))
                 ." ".($this->oL->GetLang() == "EN" ? " # tickets" : " Combien de billets")
                 ." @ ".$this->oMbrOrder->dollar($this->oMbrOrder->raRegistrations['reg_ecosgn_0910']['amount'])
                 ."<BR/><BR/>To book lodging, please pay $25 per night in the Miscellaneous/Paiement divers section below.</P>"
*/

        );

        /*
            25th Anniversary Conference (Toronto) 2009

        echo "<H3>Celebrate our 25th Anniversary<BR> on Sunday April 5, 2009<BR>9:00 - 4:00<BR>at the Toronto Botanical Gardens</H3>"
            ."<P><A HREF='http://www.seeds.ca/en.php?n=event_toronto_090405' target='_blank'>Click here for details</A></P>"
            ."<P>Admission includes organic lunch: $35 before March 24, $40 after March 24</P>"
            ."<P>Register now: <INPUT type='text' name='nTorontoReg' value='' size='3'><BR/>"   // would fill value from raSExtra
            ."Please enter the number of people here and type their names in the Notes area below.</P>";
         */

    }

}
