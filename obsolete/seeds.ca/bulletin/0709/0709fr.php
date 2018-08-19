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
        <b><strong>Astuce pour l’ail</strong></b></font>
        <font size='1' face='Geneva, Arial, Helvetica, sans-serif'><br>
        <br>
        <img src='garlic01.jpg' width='145'> <br><br>
        Vous pensez préserver l’ail en le mettant au réfrigérateur ou à de basses températures, mais cela ne fait que
        l’encourager à germer. Souvenez-vous que vous plantez les gousses d’ail en octobre; les basses températures
        lui disent qu’il est temps de pousser!
        <BR><BR>
        Conservez l’ail dans des sacs perméables à température ambiante, dans un endroit sombre et sec. Le placard
        de l’entrée est généralement l’endroit idéal!
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
    Brian Woods, le chef de projet pour la Grande collection canadienne d'ail de Semences du patrimoine possède une
    nouvelle culture d'ail plutôt bonne dans le Comté de Prince Edward. C'est pour lui une joie de diriger le projet
    de l'ail et de faire pousser dix de ses propres variétés, dont neuf d'entre elles lui sont parvenues déjà nommées;
    il a appelé la dixième variété Chuck. Chuck lui est parvenu comme un cadeau sans nom de Gerald, le voisin de Brian
    plus haut sur Chuckery Hill. Chuck et les neuf autres font partie des 58 variétés d'ail qui sont cultivées au
    niveau national par les 111 participants du projet!
    </P>

    <P align='center'><A HREF='http://www.canadiangarlic.ca'><IMG border=0 src='logo_GCGC_fr.gif'></A>
    <TABLE align=center width='300'><TR><TD>
    <P $styleText>
    Les membres-bénévoles de Semences du patrimoine à travers le Canada reçoivent des échantillons gratuits de
    diverses variétés d'ail chaque année. Ils cultivent chaque variété pendant au moins 2 ans et remplissent un
    simple formulaire standardisé qui enregistre les caractéristiques de leur ail.
    </TD></TR></TABLE>
    </P>

    <P $styleText>
    Le projet est une telle réussite, et notre approvisionnement en ail si abondant, que nous voudrions
    accueillir 50 nouveaux cultivateurs cette année. Et nous espérons que vous serez l'un d'entre eux.
    </P>

    <P $styleTitle>
    Voici ce que nous demandons aux cultivateurs d'ail:
    </P>

    <P $styleText>
    <UL $styleText>
    <LI>Soyez membre de Semences du patrimoine (si vous n'êtes pas sûrs que votre adhésion est encore valable,
        contactez notre bureau à courriel@semences.ca).</LI>
    <LI>Cultivez au moins deux variétés d'ail pendant 2 ans (choisissez dans la liste ci-dessous pour votre graine
        d'ail gratuite, selon disponibilité).<BR>
        Tous les cultivateurs d’ail doivent cultiver « Music », une variété
        très commune. Elle agit en tant qu’élément de contrôle pour le projet, en nous permettant de comparer les
        résultats de cette variété à travers les régions et les sols viticoles, afin de mieux interpréter les
        différences de résultats des autres variétés.<BR>
        Nous fournissons trois bulbes de chaque variété. <B>Deux variétés rempliront environ 15 mètres carrés de votre jardin.</B>
        </LI>
    <LI>Remplissez le formulaire d'observation pour chaque variété chaque année (y compris vos propres variétés si vous en avez).</LI>
    </UL>
    </P>

    <P $styleText>
    Les nouveaux membres recevront leur premier ail à temps pour la plantation en octobre.
    </P>

    <P $styleText>
    Veuillez consulter <A HREF='http://www.ailcanadien.ca'>www.ailcanadien.ca</A> (en anglais seulement) pour plus
    d’information sur la Grande collection canadienne d'ail, y compris votre rôle en tant que cultivateur,
    des informations sur l’ail et comment le cultiver, et pour télécharger votre formulaire d’observation de
    l’ail dès maintenant.
    </P>

    <HR/>

    <P $styleTitle>
    Intéressés à vous joindre à Semences du patrimoine afin d’explorer et documenter toutes les diverses variétés d’ail cultivées au Canada?
    </P>
    <P $styleText>
    Contactez Brian Woods Chef de projet, à <A HREF='mailto:ail@semences.ca'>ail@semences.ca</A>.
    </P>

    <TABLE border=0 cellspacing=20><TR><TD colspan=3 $styleText>
    <CENTER><DIV $styleTitle><strong>Variétés disponibles pour les nouveaux cultivateurs d’ail</DIV>
    <BR>
    Veuillez choisir jusqu’à trois variétés et répondre à Brian Woods à
    at ail@semences.ca</strong>
    <BR>
    <I>Note: L’une de vos variétés doit être Music, dans un but de comparaison avec toutes les autres variétés.
       Si toutefois vous cultivez déjà Music, veuillez nous le faire savoir.</I></CENTER>
    </TD></TR>
    <TR>
    <TD colspan=3>
    <B>Music</B> -- veuillez impérativement demander cette variété, à moins que vous ne la cultiviez déjà
    </TD>
    </TR>
    <TR>
    <TD valign='top'>
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
    <TD valign='top'>
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
    <TD valign='top'>
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
