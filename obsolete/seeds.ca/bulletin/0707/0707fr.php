<?
// e-Bulletin July 2007 French

define("SITEROOT","../../");
include(SITEROOT."site.php");
include( "../_bullDraw01.php" );

$bullDraw = new BulletinDraw( "Juillet", "2007", "0707", "FR" );

$bullDraw->drawHeader();
$bullDraw->drawTableStart();
$bullDraw->drawSideBarStart();

doSideBarText( $bullDraw );

$bullDraw->drawSideBarEnd();
$bullDraw->drawMainStart();

doMainText( $bullDraw );

$bullDraw->drawMainEnd();
$bullDraw->drawTableEnd();
$bullDraw->drawFooter();



function doSideBarText( $bullDraw )
/**********************************
 */
{
?>
      <p align="left"><font color="#336633" size="2" face="Geneva, Arial, Helvetica, sans-serif"><b><strong>Pensée
        verte</strong></b></font><font size="1" face="Geneva, Arial, Helvetica, sans-serif"><br>
        <br>
        <img src="images/sidebarbee.gif" width="145" height="137"> <br><br>
        </font><font size="1" face="Geneva, Arial, Helvetica, sans-serif">Saviez-vous
        que 70% de nos moissons alimentaires nécessitent l'intervention des insectes pour la pollinisation ?
        Également, saviez-vous que la majorité des plantes sauvages et petits animaux granivores ne peuvent
        vivre sans eux ? Il ne s'agit pas seulement d'abeilles et de papillons : il y a plus de 1 000 espèces
        d'insectes pollinisateurs au Canada ! Malheureusement, ces insectes subissent les contrecoups de la
        disparition des milieux naturels et des sources de nourriture, des maladies et des pesticides. Lorsque
        ces populations d'insectes sont menacées, il en va de même pour les fruits et légumes cultivés et les
        écosystèmes naturels qui en dépendent. </font></p>
      <p align="left"><font color="#000000" size="1" face="Geneva, Arial, Helvetica, sans-serif"><em>
      Plus d’information est nécessaire et ce, dès maintenant afin que des actions soient prises pour sauvegarder les populations de pollinisateurs.
        </em> </font></p>
      <p align="left"><font size="1" face="Geneva, Arial, Helvetica, sans-serif">Visitez
      le <a href='http://www.pollinisationcanada.ca'>www.pollinisationcanada.ca</a> pour en savoir plus sur le
      programme <font color="#000000"><em>Pollinisation Canada</em></font> de Semences du patrimoine et comment vous
      impliquer. </font></p>

<?
}


function doMainText( $bullDraw )
/*******************************
 */
{
    define( "P_STYLE_TITLE", "align='left' style='color:#77b377; font-size: 13pt; font-family:Geneva, Arial, Helvetica, sans-serif;'" );
    define( "P_STYLE_TEXT",  "align='left' style='color:#000000; font-size: 10pt; font-family:Arial, Helvetica, sans-serif;'" );

?>
      <p <?=P_STYLE_TITLE?> ><b>Dans ce numéro:</b></p>
      <p <?=P_STYLE_TEXT?> ><a href="#article1">Cet été, faites une différence. Joignez-vous au BUZZ de Pollinisation Canada !</a></p>
      <p <?=P_STYLE_TEXT?> ><a href="#article2">Faites une session d'observation simplifiée dès maintenant !</a></p>
      <p <?=P_STYLE_TEXT?> ><a href="#announcements">Nouveautés</a></p>
      <hr>

      <p><a name="article1"></a></p>
      <table width="100" border="0" align="right" cellpadding="4" cellspacing="2">
        <tr>
          <td width="450"><img src="images/pollinator1.gif" width="100" height="80"></td>
        </tr>
        <tr>
          <td><img src="images/pollinator4.gif" width="100" height="114"></td>
        </tr>
        <tr>
          <td><img src="images/pollinator3.gif" width="100" height="107"></td>
        </tr>
        <tr>
          <td><img src="images/pollinator2.gif" width="100" height="87"></td>
        </tr>
      </table>
      <p <?=P_STYLE_TITLE?> ><b>Cet été, faites une différence. Joignez-vous au BUZZ de Pollinisation Canada !</b></p>
      <p <?=P_STYLE_TEXT?> >
      Vous voulez vous impliquer dans la protection des pollinisateurs et de leurs habitats, mais ne savez pas comment?
      Le programme <em>Pollinisation Canada</em> de Semences du patrimoine vous offre maintenant les outils pour faire
      la différence !  Joignez vous à nous dès maintenant dans un programme scientifique de surveillance communautaire
      permettant à tous les Canadiens de participer à une étude nationale sur les pollinisateurs.
      </p>
      <p <?=P_STYLE_TEXT?> >Découvrez un tout nouvel écosystème dans votre cour arrière !</p>
      <p <?=P_STYLE_TEXT?> >
      La <U>surveillance</U> des populations d'insectes et de la diversité est au coeur du programme. L'observation se
      fait dans les jardins, parcs, sur le long cours des routes rurales, essentiellement, partout où les fleurs
      poussent et les participants prennent en note ce qu'ils voient. En acheminant ces informations, les participants
      au programme soutiennent les scientistes à mieux comprendre les interrelations entre les pollinisateurs, les
      écosystèmes, la diversité des plantes et les activités humaines.</p>
      <p <?=P_STYLE_TEXT?> >
      Tout notre matériel de formation, incluant la trousse de l'observateur, peut être téléchargé facilement et
      gratuitement depuis notre site à <a href="http://www.pollinisationcanada.ca" target="_blank">www.pollinisationcanada.ca.</a></p>


    <? $bullDraw->backToTop(); ?>


      <p><a name="article2"></a></p>
      <table width="112" border="0" align="right" cellpadding="4" cellspacing="2">
        <tr>
          <td width="450"><img src="images/observer.gif" width="100" height="106"></td>
        </tr>
      </table>
      <p <?=P_STYLE_TITLE?> ><b>Faites une session d'observation simplifiée dès maintenant !</b></p>
      <p <?=P_STYLE_TEXT?> >
      Le programme de surveillance de Pollinisation Canada utilise des feuilles de route afin de vous aider à
      observer les détails essentiels à ce programme de surveillance.</p>
      <p <?=P_STYLE_TEXT?> >
      Une trousse complète de l'observateur est disponible à
      <a href="http://www.pollinisationcanada.ca" target="_blank">www.pollinisationcanada.ca.</a></p>

      <p <?=P_STYLE_TEXT?> >
      Dans l'intérim, utilisez cette grille d'observation simplifiée. Choisissez un jardin, un champ, essentiellement,
      tout endroit où les fleurs poussent.</p>

      <p <?=P_STYLE_TEXT?> >
      Gardez l'oeil ouvert et trouvez les pollinisateurs en action.</p>

      <table width="100%" border="1" cellspacing="0" cellpadding="5">
        <tr bgcolor="#006633">
          <td colspan="2"><p <?=P_STYLE_TEXT?> ><font color="#FFFFFF">nombre d'insectes</font></p></td>
          <td colspan="2"><p <?=P_STYLE_TEXT?> ><font color="#FFFFFF">nombre d'insectes</font></p></td>
        </tr>
        <tr>
          <td width="8%">&nbsp;</td>
          <td width="45%"><p <?=P_STYLE_TEXT?> >Abeilles</p></td>
          <td width="6%">&nbsp;</td>
          <td width="41%"><p <?=P_STYLE_TEXT?> >Guêpes</p></td>
        </tr>
        <tr>
          <td>&nbsp;</td>
          <td><p <?=P_STYLE_TEXT?> >Papillons, papillons de nuit</p></td>
          <td>&nbsp;</td>
          <td><p <?=P_STYLE_TEXT?> >Mouches</p></td>
        </tr>
        <tr>
          <td>&nbsp;</td>
          <td><p <?=P_STYLE_TEXT?> >Coléoptères</p></td>
          <td>&nbsp;</td>
          <td><p <?=P_STYLE_TEXT?> >Autres/ne sais pas</p></td>
        </tr>
        <tr>
          <td colspan="4"><p <?=P_STYLE_TEXT?> >Endroit:</p></td>
        </tr>
        <tr>
          <td colspan="4"><p <?=P_STYLE_TEXT?> >Date, heure:</p></td>
        </tr>
        <tr bgcolor="#006633">
          <td colspan="4"><p <?=P_STYLE_TEXT?> ><font color="#FFFFFF">S.V.P. faites-moi parvenir plus d'information sur le programme Pollinisation Canada</font></p></td>
        </tr>
        <tr>
          <td colspan="4"><p <?=P_STYLE_TEXT?> >Nom:</p></td>
        </tr>
        <tr>
          <td colspan="4"><p <?=P_STYLE_TEXT?> >Adresse:</p></td>
        </tr>
        <tr>
          <td colspan="4"><p <?=P_STYLE_TEXT?> >Courriel:</p></td>
        </tr>
      </table>


    <? $bullDraw->backToTop(); ?>


      <p <?=P_STYLE_TEXT?> >
      <a name="announcements"></a>
      En 2003, Semences du patrimoine Canada et le Réseau d'évaluation et de surveillance écologiques d'Environnement
      Canada s’allient dans un but commun qui vise à adresser le manque de connaissance au sujet des abeilles
      domestiques et autres pollinisateurs ainsi que le peu de valeur qui leur est accordé.  Ensemble ils créent
      un programme scientifique de surveillance communautaire qui permet maintenant à tous les Canadiens de participer
      à une étude nationale sur les pollinisateurs.</p>

      <p <?=P_STYLE_TEXT?> >
      Aujourd’hui, Pollinisation Canada est un réseau pan canadien d'organismes éducatifs, agricoles et environnementaux
      ouvrant la route à la sensibilisation sur les pollinisateurs et leur préservation.</p>

      <p <?=P_STYLE_TEXT?> >
      Depuis la fondation du programme, nombre de partenaires se sont associés au programme en offrant le matériel
      éducatif à leur personnel, bénévoles, membres et invités. Plusieurs ont également ajouté le programme de
      Pollinisation Canada à leurs propres programmes.</p>


    <? $bullDraw->backToTop( false ); ?>
<?
}

?>
