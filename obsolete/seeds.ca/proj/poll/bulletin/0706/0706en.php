<?

// Pollination Canada bulletin June 2007 English

define("SITEROOT","../../../../");
include(SITEROOT."site.php");
include( "../_bullDraw01.php" );

$bullDraw = new BulletinDraw( "June", "2007", "0706", "EN" );

$s = $bullDraw->Template();

$s = str_replace( "[[BODY]]", doBodyText( $bullDraw ), $s );
$s = str_replace( "[[SIDEBAR]]", doSideBarText( $bullDraw ), $s );


echo $s;


function doSideBarText( $bullDraw )
/**********************************
 */
{
    $s1 = <<<SIDEBAR_TEXT
                      <p><font color="#336633" size="3" face="Arial, Helvetica, sans-serif"><strong>Upcoming
                          Issues</strong></font></p>
                        <p><font color="#336633" size="2" face="Verdana, Arial, Helvetica, sans-serif"><b><strong>July</strong></b></font><font size="2" face="Verdana, Arial, Helvetica, sans-serif">
                          <br>
                          Why syrphids are <br>
                          so important</font></p>
                        <p><font color="#336633" size="2" face="Verdana, Arial, Helvetica, sans-serif"><b><strong>August</strong></b></font><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><br>
                          Start to notice more wasps in the flower ecosystem -
                          why?</font></p>
                        <p><font color="#336633" size="2" face="Verdana, Arial, Helvetica, sans-serif"><b><strong>September</strong></b></font><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><br>
                          Pollinators' last chance to collect food for the long
                          winter - important to record visitation on goldenrod
                          and asters</font></p>
                        <p><font color="#336633" size="2" face="Verdana, Arial, Helvetica, sans-serif"><b><strong>October</strong></b></font><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><br>
                          Summer 2007 pollinator counts &#8211; How you&#8217;ve
                          made a difference!</font></p>

SIDEBAR_TEXT;
    return( $s1 );
}

function doBodyText( $bullDraw )
/*******************************
 */
{
    $s1 = <<<BODY_TEXT
                <p><font color="#336633" size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong>You
                    have joined Canada&#8217;s largest study of pollinating insects!
                    <br>
                    You are now part of a network of educational, agricultural,
                    and environmental institutions across Canada who lead the
                    way in pollinator education and conservation.</strong></font></p>
                  <p><strong><font color="#336633" size="2" face="Verdana, Arial, Helvetica, sans-serif">We
                    are proud to send you our very first issue of the Pollination
                    Canada e-newsletter! You can look forward to receiving an
                    <br>
                    e-newsletter once a month from now until October.</font></strong></p>
                  <hr> <p><b><font color="990066" size="4" face="Verdana, Arial, Helvetica, sans-serif">Pollination
                    Canada Partners</font></b></p>
                  <p><font size="2" face="Verdana, Arial, Helvetica, sans-serif">We&#8217;d
                    like to thank our special Pollination Canada Partners <strong>(check
                    out the left side bar to see who&#8217;s on board)</strong>.
                    Each of our partners has received Pollination Canada promotional
                    brochures to hand out to their members and visitors and can
                    help the Canadian public gain access to Pollination Canada
                    training materials, and more. Some of our partners are themselves
                    participating in the program and have even integrated pollinator
                    education into their own programs, so make sure to visit their
                    website to see what kind of programming they are offering
                    this summer. </font></p>
                  <hr> <p><b><font color="990066" size="4" face="Verdana, Arial, Helvetica, sans-serif">Pollination
                    Canada Observer&#8217;s Kit </font></b></p>
                  <ul>
                    <li>
                      <p><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Download
                        our most recent edition of the Observer&#8217;s Manual
                        at <a href="http://pollinationcanada.ca/index.php?n=pc_observers_kit" target="_blank">http://pollinationcanada.ca/index.php?n=pc_observers_kit</a></font></p>
                    </li>
                    <li>
                      <p><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Coming
                        soon at www.pollinationcanada.ca the Bee Photo Album to
                        help you with bee identification.</font></p>
                    </li>
                    <li><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Download
                      the Pollination Canada poster <a href="http://pollinationcanada.ca/index.php?n=pc_download" target="_blank">http://pollinationcanada.ca/index.php?n=pc_download</a>
                      for your bulletin board.</font></li>
                  </ul>
                  <p><font size="2" face="Verdana, Arial, Helvetica, sans-serif">In
                    addition, free educational materials for children grades 4-6
                    is also available for download from the North American Protection
                    Campaign website at <a href="http://www.nappc.org/curriculum/" target="_blank">www.nappc.org/curriculum/</a>.
                    </font></p>
                  <hr> <p><b><font color="990066" size="4" face="Verdana, Arial, Helvetica, sans-serif">Training
                    Sessions and Material</font></b></p>
                  <p><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Many
                    of you have asked about receiving training from one of our
                    staff. Our team is currently preparing training sessions in
                    key locations across Canada. Coming soon: Check out NEWS section
                    of the Pollination Canada website for a training session near
                    you. </font></p>
                  <p><font size="2" face="Verdana, Arial, Helvetica, sans-serif">For
                    partner organizations, we&#8217;re also developing &#8220;train-the-trainer&#8221;
                    materials that will allow you to train your staff and volunteers
                    to effectively involve the public in pollinator monitoring.
                    Stay tuned for more details.</font></p>
BODY_TEXT;
    return( $s1 );
}

?>
