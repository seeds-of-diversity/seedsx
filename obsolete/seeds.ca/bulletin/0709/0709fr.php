<?
// e-Bulletin September 2007 French

define("SITEROOT","../../");
include(SITEROOT."site.php");
include( "../_bullDraw01.php" );

$bullDraw = new BulletinDraw( "Septembre", "2007", "0709", "FR" );

$bullDraw->Draw();





function doSideBarText( $bullDraw )
/**********************************
 */
{
    $s = "
        <p align='left'><font color='#336633' size='2' face='Geneva, Arial, Helvetica, sans-serif'>
        <b><strong>Astuce pour l�ail</strong></b></font>
        <font size='1' face='Geneva, Arial, Helvetica, sans-serif'><br>
        <br>
        <img src='garlic01.jpg' width='145'> <br><br>
        Vous pensez pr�server l�ail en le mettant au r�frig�rateur ou � de basses temp�ratures, mais cela ne fait que
        l�encourager � germer. Souvenez-vous que vous plantez les gousses d�ail en octobre; les basses temp�ratures
        lui disent qu�il est temps de pousser!
        <BR><BR>
        Conservez l�ail dans des sacs perm�ables � temp�rature ambiante, dans un endroit sombre et sec. Le placard
        de l�entr�e est g�n�ralement l�endroit id�al!
        </font></p>
        <p align='left'><font size='1' face='Geneva, Arial, Helvetica, sans-serif'>
        Visitez le
        <a href='http://www.ailcanadien.ca'>www.ailcanadien.ca</a>
        pour en savoir plus sur le programme <i>Grande Collection Canadienne d'Ail</i> de Semences du patrimoine et
        comment vous impliquer.</p>
      ";
    return( $s );
}


function doMainText( $bullDraw )
/*******************************
 */
{
    $styleTitle = "style='color:#397a37; font-size: 13pt; font-family:Geneva, Arial, Helvetica, sans-serif;'";
    $styleText  = "style='color:#000000; font-size: 10pt; font-family:Arial, Helvetica, sans-serif;'";

    $s = "
      <P $styleTitle><b>On recherche: 50 cultivateurs d'ail pour la Grande collection canadienne d'ail de Semences du patrimoine!</b></p>

    <P $styleText>
    Brian Woods, le chef de projet pour la Grande collection canadienne d'ail de Semences du patrimoine poss�de une
    nouvelle culture d'ail plut�t bonne dans le Comt� de Prince Edward. C'est pour lui une joie de diriger le projet
    de l'ail et de faire pousser dix de ses propres vari�t�s, dont neuf d'entre elles lui sont parvenues d�j� nomm�es;
    il a appel� la dixi�me vari�t� Chuck. Chuck lui est parvenu comme un cadeau sans nom de Gerald, le voisin de Brian
    plus haut sur Chuckery Hill. Chuck et les neuf autres font partie des 58 vari�t�s d'ail qui sont cultiv�es au
    niveau national par les 111 participants du projet!
    </P>

    <P align='center'><A HREF='http://www.canadiangarlic.ca'><IMG border=0 src='logo_GCGC_fr.gif'></A>
    <TABLE align=center width='300'><TR><TD>
    <P $styleText>
    Les membres-b�n�voles de Semences du patrimoine � travers le Canada re�oivent des �chantillons gratuits de
    diverses vari�t�s d'ail chaque ann�e. Ils cultivent chaque vari�t� pendant au moins 2 ans et remplissent un
    simple formulaire standardis� qui enregistre les caract�ristiques de leur ail.
    </TD></TR></TABLE>
    </P>

    <P $styleText>
    Le projet est une telle r�ussite, et notre approvisionnement en ail si abondant, que nous voudrions
    accueillir 50 nouveaux cultivateurs cette ann�e. Et nous esp�rons que vous serez l'un d'entre eux.
    </P>

    <P $styleTitle>
    Voici ce que nous demandons aux cultivateurs d'ail:
    </P>

    <P $styleText>
    <UL $styleText>
    <LI>Soyez membre de Semences du patrimoine (si vous n'�tes pas s�rs que votre adh�sion est encore valable,
        contactez notre bureau � courriel@semences.ca).</LI>
    <LI>Cultivez au moins deux vari�t�s d'ail pendant 2 ans (choisissez dans la liste ci-dessous pour votre graine
        d'ail gratuite, selon disponibilit�).<BR>
        Tous les cultivateurs d�ail doivent cultiver ��Music��, une vari�t�
        tr�s commune. Elle agit en tant qu��l�ment de contr�le pour le projet, en nous permettant de comparer les
        r�sultats de cette vari�t� � travers les r�gions et les sols viticoles, afin de mieux interpr�ter les
        diff�rences de r�sultats des autres vari�t�s.<BR>
        Nous fournissons trois bulbes de chaque vari�t�. <B>Deux vari�t�s rempliront environ 15 m�tres carr�s de votre jardin.</B>
        </LI>
    <LI>Remplissez le formulaire d'observation pour chaque vari�t� chaque ann�e (y compris vos propres vari�t�s si vous en avez).</LI>
    </UL>
    </P>

    <P $styleText>
    Les nouveaux membres recevront leur premier ail � temps pour la plantation en octobre.
    </P>

    <P $styleText>
    Veuillez consulter <A HREF='http://www.ailcanadien.ca'>www.ailcanadien.ca</A> (en anglais seulement) pour plus
    d�information sur la Grande collection canadienne d'ail, y compris votre r�le en tant que cultivateur,
    des informations sur l�ail et comment le cultiver, et pour t�l�charger votre formulaire d�observation de
    l�ail d�s maintenant.
    </P>

    <HR/>

    <P $styleTitle>
    Int�ress�s � vous joindre � Semences du patrimoine afin d�explorer et documenter toutes les diverses vari�t�s d�ail cultiv�es au Canada?
    </P>
    <P $styleText>
    Contactez Brian Woods Chef de projet, � <A HREF='mailto:ail@semences.ca'>ail@semences.ca</A>.
    </P>

    <TABLE border=0 cellspacing=20><TR><TD colspan=3 $styleText>
    <CENTER><DIV $styleTitle><strong>Vari�t�s disponibles pour les nouveaux cultivateurs d�ail</DIV>
    <BR>
    Veuillez choisir jusqu�� trois vari�t�s et r�pondre � Brian Woods �
    at ail@semences.ca</strong>
    <BR>
    <I>Note: L�une de vos vari�t�s doit �tre Music, dans un but de comparaison avec toutes les autres vari�t�s.
       Si toutefois vous cultivez d�j� Music, veuillez nous le faire savoir.</I></CENTER>
�   </TD></TR>
    <TR>
    <TD colspan=3>
    <B>Music</B> -- veuillez imp�rativement demander cette vari�t�, � moins que vous ne la cultiviez d�j�
    </TD>
    </TR>
    <TR>
�   <TD valign='top'>
        Alison's            <BR>
        Asian Tempest       <BR>
        Baba Franchuk's     <BR>
        Chesnok Red         <BR>
        China Rose          <BR>
        Carpathian          <BR>
        Denman              <BR>
        F7                  <BR>
        F21                 <BR>
        F23                 <BR>
        Fauquier            <BR>
<!--    French              <BR>  -->
        Georgian Crystal    <BR>
        German Red          <BR>
        Inchellium Red      <BR>
        Israeli             <BR>
        Italian             <BR>
        Khabar              <BR>
    </TD>
�   <TD valign='top'>
        Kiev                <BR>
        Killarney           <BR>
        Korean Purple       <BR>
        Limburg             <BR>
        Malpasse            <BR>
        Mediterranean       <BR>
        Montana Giant       <BR>
        Montana Roja        <BR>
        Moravia             <BR>
        Mountaintop         <BR>
        Nootka Rose         <BR>
<!--    Northern Quebec     <BR>  -->
<!--    Oregon Blue         <BR>  -->
        Persian Star        <BR>
        Polish              <BR>
        Purple Max          <BR>
        Puslinch            <BR>
        Racey               <BR>
    </TD>
�   <TD valign='top'>
        Red Italian         <BR>
        Red Rezan           <BR>
        Romanian Red        <BR>
<!--    Russian             <BR>  -->
        Salt Spring         <BR>
        Siberian            <BR>
        Sicilian Gold       <BR>
        Sicilian White      <BR>
        Spanish             <BR>
        Spanish Roja        <BR>
        Stein               <BR>
        Sweet Haven         <BR>
        Thai                <BR>
<!--    Tibetan             <BR>  -->
        Transylvanian       <BR>
        Ukrainian Mavniv    <BR>
        Yugoslavian         <BR>
    </TD>
    </TR></TABLE>
    ";

    return( $s );
}

?>
