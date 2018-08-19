<?php

/* Crop Description interface
 *
 * Copyright (c) 2012-2017 Seeds of Diversity Canada
 *
 * Interface to record crop descriptors (index.php and iframe.php both use this)
 */

// make popup height = $(window).height-50

/*
ENREGISTER UNE DESCRIPTION DE CULTURE (RECORD YOUR CROP DESCRIPTIONS)
Comment utiliser les descriptions de culture (How to use crop description records)
Commencer (Get started)
Fiche de description (Printable Crop Description Form)
Version imprimable

CONSULTER LES FICHES D’AUTRES PERSONNES (SEE WHAT OTHER PEOPLE REPORTED)
Montrer les données sur : (Show data about :)
Variétés (Varieties)
Caractéristiques (Characteristics)
Caractéristiques (Characteristics)
Moyenne (Average)
Le plus fréquent (most common)
Nombre d’entrées (Number of entries)
Afficher les détails (View details)
Afficher (View)

Buissonnant/Grimpant (Bush/Climbing)
Port de la plante (Growth habit)
Buissonnant (Bush)
Branches courtes dressées (Short upright branches)

 */


include_once( STDINC."SEEDSession.php" );
include_once( SEEDCOMMON."sl/sl_desc_db.php" );

class CropDesc
{
    public $kfdb;
    public $sess;
    public $lang;
    public $bLogin = false;

    function __construct()
    {
        list($this->kfdb, $this->sess, $this->lang) = SiteStartSessionAccountNoUI();
        $this->bLogin = $this->sess->IsLogin();

        if( $this->bLogin && @$_REQUEST['cdCmd']=='Logout' ) {
            $this->sess->LogoutSession();
            $this->bLogin = false;
        }
    }

    function DrawBody()
    {
        $s = "<div class='container'>"
                ."<div class='row'>"
                    ."<div class='col-md-6'>"
                        ."<div class='mybox'>".$this->DrawBox1()."</div>"
                    ."</div>"
                    ."<div class='col-md-6'>"
                        ."<div class='mybox'>".$this->DrawBox2()."</div>"
                    ."</div>"
                ."</div>"
                ."<div class='row'>"
                    ."<div class='col-md-6'>"
                        ."<div class='mybox'>".$this->DrawBox4()."</div>"
                    ."</div>"
                    ."<div class='col-md-6'>"
                        ."<div class='mybox'>".$this->DrawBox3()."</div>"
                    ."</div>"
                ."</div>"
            ."</div>";

        $s1 = "<div id='CropDescBody'>"
            ."<div id='CropDescBox1' class='CropDescBox'><div class='CropDescBoxInner'>".$this->DrawBox1()."</div></div>"
            ."<div id='CropDescBox2' class='CropDescBox'><div class='CropDescBoxInner'>".$this->DrawBox2()."</div></div>"
            ."<div id='CropDescBox3' class='CropDescBox'><div class='CropDescBoxInner'>".$this->DrawBox3()."</div></div>"
            ."<div id='CropDescBox4' class='CropDescBox'><div class='CropDescBoxInner'>".$this->DrawBox4()."</div></div>"
            ."</div>";

        if( ($n = intval(@$_REQUEST['p_nCDBodyCurrBox'])) ) {
            $s1 .= "<script>CDBodyCurrBox = $n;</script>";
        }

        return( $s );
    }

    function Style()
    {
        $s = "<style>"
            ."#CropDescBody { width:100%; height:100%; position:relative; }
              .CropDescBox  { border:3px solid #F07020; border-radius:10px;
                              background-color:#ffee77;
                              position: absolute;
                              text-align:center;
                              padding:10px;
                            }
              .CropDescBoxInner { overflow:auto;         // this puts scrollbars inside the padding instead of at the border
                                  width:100%; height:100%; }

              #CropDescBox1 .CropDescBoxInner { background:url(img/descGrid01.png) no-repeat center; background-size:contain; }
              #CropDescBox2 .CropDescBoxInner { background:url(img/descGrid02.png) no-repeat center; background-size:contain; }
              #CropDescBox3 .CropDescBoxInner { background:url(img/descGrid03.png) no-repeat center; background-size:contain; }
              #CropDescBox4 .CropDescBoxInner { background:url(img/descGrid04.png) no-repeat center; background-size:contain; }

              .CropDescBoxCurr h2 { font-size:20pt; }
              .CropDescBoxNotCurr h2 { font-size:10pt; font-weight:bold; }
              .CropDescBoxNotCurr p, .CropDescBoxNotCurr .cdContent { display:none; }

              .mybox { padding-right:30px; }
            "
            ."</style>";

        return( $s );
    }

    function Script()
    {
        $s = "<script src='descUI.js'></script>";

        return( $s );
    }


    function DrawBox1()
    {
        $sOnClick =  "window.open(\"popup.php?cmd=More\",\"_blank\",\"width=600,height=800,scrollbars=yes\")";
        $s = "<h2>".($this->lang=='EN'?"How to Use Crop Description Records":"Comment utiliser les descriptions de culture")."</h2>"
            ."<div class='cdContent'>"
                ."<p>This is a crop-records system made for farmers and gardeners like you. "
                ."Help document Canada's diverse plant varieties by recording simple observations about the plants you grow. "
                ."You'll find simple multiple-choice forms in paper and web formats that let you make systematic observations with other growers "
                ."across Canada and beyond.</p>"
                ."<div style='margin-left:30px'>"
                    ."<p><img style='display:inline-block' src='img/descGridThumbMobile.png' height='30'/> Use a mobile device to enter your observations, right in the field.</p>"
                    ."<p>OR</p>"
                    ."<p><img style='display:inline-block' src='img/descGridThumb04.png' height='30'/> Use printable forms in the field and enter your observations on-line later.</p>"
                    ."<p style='font-weight:bold'><a href='#' onclick='$sOnClick'>Tell me more!</a></p>"
                ."</div>"
            ."</div>";

        return( $s );
    }

    function DrawBox2()
    {
        $s ="";

        $s .= "<h2>".($this->lang=='EN'?"Get Started":"Commencer")."</h2>"
             ."<div class='cdContent'>";

        if( $this->bLogin ) {
            /* You are logged in.  $this->sess is a fully-loaded SEEDSessionAuth
             */
            $s .= "<p>Hi ".$this->sess->GetName()."!  You're logged in to the Crop Descriptions Database.</p>";

            $o = new SL_VarInstDB( $this->kfdb );
            $ra = $o->GetListVarInst( array( 'uid'=>$this->sess->GetUID() ) );

            if( !count($ra) ) {
                $sTodo = "start recording your growing sites and plant observations";
            } else {
                $s .= "<p>You have ".count($ra)." crop description records so far.</p>";
                $sTodo = "update them, and record more crop observations";
            }

            $s .= "<p><img style='display:inline-block' src='img/descGridThumbMobile.png' height='30'/> <img style='display:inline-block' src='img/descGridThumb02.png' height='30'/> "
                 ."<a href='input.php' target='_blank'>Click here to $sTodo (opens a new window)</a></p>"
                 ."<p>&nbsp;</p>"
                 ."<p><form action='".Site_path_self()."' method='post'>"
                     ."<input type='submit' name='cdCmd' value='Logout'/>"
                     ."<input type='hidden' name='p_nCDBodyCurrBox' value='2'/>"  // force the UI to activate this box again
                 ."</form></p>";

        } else {
            /* You are not logged in.
             */
            $s .= SEEDSessionAccountUI_LittleLogin( $this->sess );
        }

        $s .= "</div>";

        return( $s );
    }

    function DrawBox3()
    {
        include_once( SEEDCOMMON."sl/sl_desc_report.php" );

        $oUI = new SLDescReportUI( $this->kfdb, $this->lang, array(
                    'linkToMe' => (Site_path_self()."?p_nCDBodyCurrBox=3&"),
                    'submitToMe' => "<input type='hidden' name='p_nCDBodyCurrBox' value='3'/>",
         ) );

        $s = "<h2>".($this->lang=='EN'?"See What Other People Reported":"CONSULTER LES FICHES D'AUTRES PERSONNES")."</h2>"
            ."<div class='cdContent'>"

            .$oUI->Style()
            .$oUI->DrawDrillDown()

            //."<p>Show observations about:</p>"
            //."<h4>Crop Species (arrow)</h4>"

            //."<h4>Varieties (arrow)</h4>"
            ."</div>"
            ;

        return( $s );
    }

    function DrawBox4()
    {
        $ra = array(
            array( 'Apple', 2016        ),
            array( 'Barley', 2016),
            array( 'Bean', 2016),
            array( 'Beet', 2016),
            array( 'Brassica', 2016),
            array( 'Buckwheat', 2016),
            array( 'Carrot', 2016),
            array( 'Celery', 2016),
            array( 'Chinese-Cabbage', 2016),
            array( 'Corn', 2015 ),
            array( 'Cucumber', 2016),
            array( 'Eggplant', 2016),
            array( 'Garlic', 2013 ),
            array( 'Lentil', 2016),
            array( 'Lettuce', 2016),
            array( 'Melon', 2016),
            array( 'Mustard-Green', 2016),
            array( 'Onion-Leek', 2016 ),
            array( 'Parsnip', 2016),
            array( 'Pea', 2016),
            array( 'Pepper', 2016),
            array( 'Potato', 2016),
            array( 'Quinoa', 2016),
            array( 'Radish', 2016),
            array( 'Rutabaga', 2016),
            array( 'Rye', 2016),
            array( 'Soybean', 2014 ),
            array( 'Spinach', 2016),
            array( 'Squash', 2016),
            array( 'Tomato', 2016),
            array( 'Turnip', 2016),
            array( 'Watermelon', 2014 ),
            array( 'Wheat', 2016)
        );


        $s = "<h2>".($this->lang=='EN'?"Printable Crop Description Forms":"Fiche de description<br/>Version imprimable")."</h2>"

/*
            ."<p>Download and print these forms to enter your crop observations in the field. <br/>They're the same "
            ."format as the web forms, so it's easy to enter the information on-line later.</p>";

        $s .= "<div>";

        foreach( $ra as $v ) {
            $s .= "<a href='http://www.seeds.ca/d?n=seedlibrary/desc/en/${v[0]}_Descriptor_${v[1]}.doc' target='_blank'>${v[0]}</a><br/>";
        }
        $s .= "</div>";
*/


            ."<p>"
            ."<select id='descPrintable'>";
        foreach( $ra as $v ) {
            $s .= "<option value='${v[0]}_Descriptor_${v[1]}'>${v[0]}</option>";
        }
        $s .= "</select>"
             ."&nbsp;"
             ."<input type='button' value='Download' onclick='getPrintable();'/>"
             ."</p>"
             ."<script>
                 function getPrintable() {
                     var v = jQuery('#descPrintable').val();

                     var d = 'http://www.seeds.ca/d?n=seedlibrary/desc/en/'+v+'.doc';

                     window.open(d,'_blank','width=800,height=1200,scrollbars=yes');
                 }
               </script>
              ";


        return( $s );
    }
}



?>
