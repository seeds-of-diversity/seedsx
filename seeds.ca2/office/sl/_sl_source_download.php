<?php

// sl_cv_sources should record the year
// sl_tmp_cv_sources should set the year for the update -- year can be an optional column in the spreadsheet, default to date('Y')
// sl_cv_sources_archive (archive companies but not seed banks?)

// garbage collector has to clear sl_tmp_cv_sources


/*

delete from seeds.sl_cv_sources where fk_sl_sources >= 3;

insert into seeds.sl_cv_sources (_key,_created,_updated,_status,
                                 fk_sl_sources,fk_sl_species,fk_sl_pcv,
                                 company_name,osp,ocv,bOrganic
                                )
select k,now(),now(),0,
       fk_sl_sources,fk_sl_species,fk_sl_pcv,
       company,species,cultivar,organic
from seeds.sl_tmp_cv_sources;

 */




/*
CREATE TABLE `sl0_cvs` (
  `k` int(11) DEFAULT NULL,
  `osp` text,
  `ocv` text,
  `company` text,
  `organic` int(11) DEFAULT NULL,
  `fk_sl_sources` int(11) DEFAULT NULL,
  `fk_sl_species` int(11) DEFAULT NULL,
  `fk_sl_pcv` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

insert into sl0_cvs (k,osp,ocv,company,organic,fk_sl_sources,fk_sl_species,fk_sl_pcv)
select _key,osp,ocv,company_name,bOrganic,fk_sl_sources,fk_sl_species,fk_sl_pcv from sl_cv_sources where fk_sl_sources>=3;

select count(*) from sl0_cvs c0 left join sl_cv_sources c on (c0.k=c._key)
where c.osp<>c0.osp or c.ocv<>c0.ocv or c.company_name<>c0.company or c.bOrganic<>c0.organic or
      c.fk_sl_sources<>c0.fk_sl_sources or
      c.fk_sl_species<>c0.fk_sl_species or
      c.fk_sl_pcv<>c0.fk_sl_pcv;

select count(*) from sl0_cvs;

select count(*) from sl0_cvs c0 left join sl_cv_sources c on (c0.k=c._key)
where c.osp=c0.osp and c.ocv=c0.ocv and c.company_name=c0.company and c.bOrganic=c0.organic and
      c.fk_sl_sources=c0.fk_sl_sources and
      c.fk_sl_species=c0.fk_sl_species and
      c.fk_sl_pcv=c0.fk_sl_pcv;


 */



/* _sl_source_download.php
 *
 * Copyright 2012-2016 Seeds of Diversity Canada
 *
 * Implement the user interface for Download (companies, seedbanks, collectors, etc)
 */

include_once( STDINC."SEEDCSV.php" );
include_once( STDINC."SEEDTable.php" );
include_once( STDINC."SEEDUIWidgets.php" );
include_once( SEEDCOMMON."console/console01ui.php" );   // DownloadUpload
include_once( SEEDCOMMON."sl/q/_QServerSourceCV.php" );

define( 'DIR_SL_DOWNLOAD', "../../sl_download/" );


class SLSourceDownload
{
    private $oW;
    private $kfdb; //deprecate
    private $sess; //deprecate

    private $companyTableDef = array( 'headers-required' => array('k','company','species','cultivar','organic','notes'),
                                      'headers-optional' => array() );


    private $npgsEachRow_species = array();  // for each loaded file, store the applicable species here - used in npgsEachRow
    private $nLoaded = 0;
    private $nAvail = 0;


    function __construct( SEEDApp_WorkerC $oW )
    {
        $this->oW = $oW;
        $this->kfdb = $oW->kfdb;    // deprecate, use oW->kfdb instead
        $this->sess = $oW->sess;    // deprecate, ...
    }


    function Main()
    {
        $s = "";
        $bFrontPage = true;  // make this false to hide the upload/download commands

        $done='0';

        $fileid = 'file_seedsource';

        $raPills = array( 'companies' => array( "Seed Companies"),
                          'pgrc'      => array( "Canada: Plant Gene Resources (PGRC)" ),
                          'npgs'      => array( "USA: National Plant Germplasm System (NPGS)" ),
                          'sound'     => array( "Sound Tests" ),
        );


        $s .= "<style>"
             // Bootstrap uses quirks.css to disallow font-size inheritance inside tables.
             // So li outside tables has font-size 14px but inside tables it's 16px
             .".nav-pills > li { font-size:14px; }"
             // Bootstrap puts a 20px top margin on <h> which is too much
             .".DownloadBodyHeading { margin-top:5px }"
             ."</style>";


        $oSVA = new SEEDSessionVarAccessor( $this->sess, 'SLDownload' );    // use the tab SVA instead
        $oUIPills = new SEEDUIWidgets_Pills( $raPills, 'pMode', array( 'oSVA' => $oSVA, 'ns' => '' ) );
        $sLeftCol = $oUIPills->DrawPillsVertical();


        $cmd = SEEDSafeGPC_GetStrPlain( 'cmd' );
        switch( $cmd ) {
            /* Seed Companies
             */
            case 'company_download':
                // this code is at the top of sl_sources.php
                exit;
                break;
            case 'company_upload':
                $oCUp = new CompanyUpload( $this->oW, $this->companyTableDef );
                $s .= $oCUp->DrawStep( -1 );  // -1 == use the Console class's own http parm to increment the step
                $bFrontPage = false;
                break;

            /* PGRC
             */
            case 'pgrcget':
                $s .= $this->pgrcDownloadFiles();
                break;
            case 'pgrcload':
                $s .= $this->pgrcLoadFiles();
                break;

            /* NPGS
             */
            case 'npgsget':
        	    $s .= $this->npgsDownloadFiles();
                break;
            case 'npgsload':
                $s .= $this->npgsLoadFiles();
            	break;
/*            case 'npgsload_old':
                //$raFiles = $this->npgsSpecies();
                foreach( $this->raNPGS as $raG ) {
                   $this->npgsEachRow_species = $raG; // keep here for use by npgsEachRow
                    // NPGS files are sometimes so big we shouldn't load them into memory - instead process each row in npgsEachRow()
                    $oCSV = new SEEDCSV( array( 'mapColNames' => array('genus','species','accename','avail'),
                                                'fnEachRow' => array($this, "npgsEachRow") ) );
                    $parms = array('bTab'=>false);
                    if( $oCSV->ReadFile( '../../sl_download/npgs/'.$raG['genus'].'.csv', $parms ) === null ) { echo $parms['sErrMsg']; }
                }

				$s .= $this->pcvTableFill();

                $s .= "<P>Loaded {$this->nLoaded} items. {$this->nAvail} available.</P>";
                break;
*/
/*
				foreach($this->raNPGS as $raG){
        			$table = $this->LoadCSVToDB('../../sl_download/npgs/'.$raG['genus'].'.csv');
        			$st="";
        			for($i=0;$i<count($raG['species']); $i++) {
						$st .= " '".$raG['species'][$i]."'";
						if($i != count($raG['species'])-1){
    						$st .= ',';
						}
					}
					$this->kfdb->Execute("DELETE FROM ".$table." WHERE species NOT IN (".$st.")");
        			$this->kfdb->Execute("INSERT INTO sl_cv_sources (fk_sl_sources,osp,ocv) SELECT '2', CONCAT(genus,' ', species), accename FROM ".$table." ");

				}

				//$t = $this->kfdb->QueryRowsRA("Select * From sl_cv_sources");
				//foreach($t as $t1){
				//    echo $t1['fk_sl_sources']." ".$t1['osp']." ".$t1['ocv']."</br>";
				//}
*/

            case 'sound_build':
                SLSourceRosetta_BuildDB::ClearSoundIndex( $this->oW->kfdb );
                SLSourceRosetta_BuildDB::BuildSoundIndex( $this->oW->kfdb );
                break;

            case 'sound_status':
                $nMatch1 = $nMatch2 = 0;

                $sqlMatch = "SELECT count(*) FROM seeds.sl_cv_sources C, seeds.sl_pcv P "
                           ."WHERE C._status='0' AND P._status='0' AND "
                           ."C.fk_sl_species<>0 AND C.ocv<>'' AND "        // skip blanks
                           ."C.fk_sl_species=P.fk_sl_species ";
//$this->oW->kfdb->Setdebug(2);
                $nPCV    = $this->oW->kfdb->Query1( "SELECT count(*) FROM seeds.sl_pcv WHERE _status='0'" );
                $nCVSrc  = $this->oW->kfdb->Query1( "SELECT count(*) FROM seeds.sl_cv_sources WHERE _status='0'     AND fk_sl_species<>0 AND ocv<>''" );
                $nMatch0 = $this->oW->kfdb->Query1( $sqlMatch."AND C.ocv=P.name" );
                $nMatch1 = $this->oW->kfdb->Query1( $sqlMatch."AND C.sound_soundex<>'' AND C.sound_soundex=P.sound_soundex" ); //C.ocv<>P.name AND
                $nMatch2 = $this->oW->kfdb->Query1( $sqlMatch."AND C.sound_metaphone<>'' AND C.sound_metaphone=P.sound_metaphone" ); //C.ocv<>P.name AND

                $s .= "<div style='width:30%;float:right;background-color:#eee;margin:0px 0px 10px 10px;padding:10px'>"
                     ."Out of $nCVSrc seed source records,<br/>"
                     ."$nMatch0 (".intval($nMatch0/$nCVSrc*100.0)."%) match pcv verbatim.</br>"
                     ."$nMatch1 (".intval($nMatch1/$nCVSrc*100.0)."%) match on soundex.</br>"
                     ."$nMatch2 (".intval($nMatch2/$nCVSrc*100.0)."%) match on metaphone.</br>"
                     ."</div>";
                break;


            case 'spfix':
                $s .= $this->speciesFix();
                break;

            case 'showslcvsources':
            	$show = $this->kfdb->QueryRowsRA("select * from sl_cv_sources");
				foreach ($show as $sh){
            	    echo "Osp:".$sh['osp']." Ocv:".$sh['ocv']." fl_sl_pcv:".$sh['fk_sl_pcv']." pm:".$sh['pm']." Soundslike:".$sh['soundslike']." tmp_match:".$sh['tmp_match']."<br>";
            	}
            	break;
            case 'showslpcv':
            	$show = $this->kfdb->QueryRowsRA("select * from sl_pcv");
            	var_dump($show);
				//foreach ($show as $sh){
            	//    echo $sh['psp']." ".$sh['name']." ".$sh['_key']."<br>";
            	//}
            	break;

			case 'updatefromslcvsources':

			if(@$_POST['t']==''){
				//echo "0 <br>";
				$this->fix();
				$this->normalizeSpecies();
				$this->normalizeCultivars();
				$this->soundslikeSetup();
				$this->temptableSetup();
				$this->compareInternal();
			}elseif(@$_POST['t']=='1'){
				//echo "1 <br>";
				$this->answerInternal();
			}elseif(@$_POST['t']=='2'){
				//echo "2 <br>";
				$this->compareExternal();
			}elseif(@$_POST['t']=='3'){
				//echo "3 <br>";
				$this->keepInternal();
			}elseif(@$_POST['t']=='4'){
				//echo "4 <br>";
				$this->temptableSetup();
				$this->compareInternal();
			}elseif(@$_POST['t']=='5'){
				//echo "5 <br>";
				$this->temptableSetup();
				$this->compareInternal();
			}elseif(@$_POST['t']=='6'){
				//echo "6 <br>";
				$this->temptableSetup();
				$this->compareInternal();
			}elseif(@$_POST['t']=='7'){
				//echo "7 <br>";
				$this->answerExternal();
			}elseif(@$_POST['t']=='8'){
				//echo "8 <br>";
				$this->addPcv();
			}elseif(@$_POST['t']=='9'){
				//echo "9 <br>";
				$this->compareExternal();
			}elseif(@$_POST['t']=='10'){
				//echo "10 <br>";
				$this->compareExternal();
			}

			break;
            	}

        if( $bFrontPage ) {

            $pMode = $oUIPills->GetCurrPill();
            switch( $pMode ) {
                case 'companies':
                    $oForm = new SEEDForm( 'Plain' );
                    $oQ = new Q( $this->oW->kfdb, $this->oW->sess, array() );
                    $oSLSrc = new QServerSourceCV( $oQ, array() );
                    $raSrc = $oSLSrc->GetSources();
                    $raOpts = array( " -- All Companies -- " => 0 );
                    foreach( $raSrc as $ra ) {
                        $raOpts[$ra['SRC_name']] = $ra['SRC__key'];
                    }
                    $sDownloadAction = $_SERVER['PHP_SELF'];
                    $sDownloadCtrl = "<input type='hidden' name='cmd' value='company_download' />"
                                    .$oForm->Select2( 'kCompany', $raOpts, "", array() );

                    $sDownloadAction = Site_UrlQ();
                    $sDownloadCtrl = $oForm->Hidden( 'qcmd', 'srcCSCI' )
                                    //.$oForm->Hidden( 'qname', "" )
                                    .$oForm->Hidden( 'qfmt', 'xls' )
                                    .$oForm->Select2( 'kSrc', $raOpts, "", array() );

                    $raParms = array( 'label'=>"Seed Company listings",
                                      'downloadaction'=>$sDownloadAction,
                                      'downloadctrl'=>$sDownloadCtrl,
                                      'uploadaction'=>$_SERVER['PHP_SELF'],
                                      'uploadctrl'=>
                                              "<input type='hidden' name='cmd' value='company_upload' />"
                                             ."<select name='eReplace' style='margin:0px 0px 10px 20px'>"
                                             ."<option value='".SLUploadCVSources::ReplaceVerbatimRows."'>Just copy the rows in the spreadsheet</option>"
                                             ."<option value='".SLUploadCVSources::ReplaceWholeCompanies."'>Replace entire companies mentioned in the spreadsheet</option>"
                                             ."<option value='".SLUploadCVSources::ReplaceWholeCSCI."'>Replace entire CSCI</option>"
                                             ."</select>",
                                      'seedTableDef'=>$this->companyTableDef,
                                    );
                    $s .= Console01UI_DownloadUpload( $this->oW, $raParms );
                    break;

                case 'pgrc':
                    $s .= "<H3 class='DownloadBodyHeading'>Canada: Plant Gene Resources (PGRC)</H3>"
                         ."<P><A href='{$_SERVER['PHP_SELF']}?cmd=pgrcget'>Download files from PGRC</A></P>"
                         ."<P><A href='{$_SERVER['PHP_SELF']}?cmd=pgrcload'>Load PGRC files</A></P>"
                         ."<P><A href='{$_SERVER['PHP_SELF']}?cmd=spfix'>Update species index</A></P>";
                    break;

                case 'npgs':
                    $s .= "<H3 class='DownloadBodyHeading'>US: National Plant Germplasm System (NPGS)</H3>"
                         ."<P><A href='{$_SERVER['PHP_SELF']}?cmd=npgsget'>Download files from NPGS</A></P>"
                         ."<P><A href='{$_SERVER['PHP_SELF']}?cmd=npgsload'>Load NPGS files</A></P>";
                    break;

                case 'sound':
                    $s .= "<h3 class='DownloadBodyHeading'>Sound Comparisons</h3>"
                         ."<p><a href='{$_SERVER['PHP_SELF']}?cmd=sound_build'>Build Soundex/Metaphone Index</a></p>"
                         ."<P><A href='{$_SERVER['PHP_SELF']}?cmd=sound_status'>Sound Index Status</A></P>"
                         ."<P><A href='{$_SERVER['PHP_SELF']}?cmd=comparesound'>Compare Sound</A></P>"
                         ."<P><A href='{$_SERVER['PHP_SELF']}?cmd=showslcvsources'>sl_cv_sources</A></P>"
                         ."<P><A href='{$_SERVER['PHP_SELF']}?cmd=showslpcv'>sl_pcv</A></P>"
                         ."<P><A href='{$_SERVER['PHP_SELF']}?cmd=addedtoslcvsources'>Added to sl_cv_sources</A></P>"
                         ."<P><A href='{$_SERVER['PHP_SELF']}?cmd=updatefromslcvsources'>Update from sl_cv_sources</A></P>";
                    break;
            }
        }




        $s = "<div class='container-fluid' style='margin:0px'>"
                ."<div class='row'>"
                    ."<div class='col-sm-2'>$sLeftCol</div>"
                    ."<div class='col-sm-10'>$s</div>"
                ."</div>"
             ."</div>";


        if( $bFrontPage ) {

        }

        return( $s );
    }



function fix(){//First "fix" everything, aka normalize it so there are no leading zeros ect.
    $this->kfdb->Execute("Update sl_cv_sources Set osp = TRIM(osp)");
	$this->kfdb->Execute("Update sl_cv_sources Set ocv = TRIM(ocv)");
}
function normalizeSpecies(){//Normalize Species
    $this->kfdb->Execute("Update sl_cv_sources s1, sl_pcv s2 Set s1.fk_sl_pcv = s2._key Where s1.osp=s2.psp and s1.ocv=s2.name");
    $this->kfdb->Execute("Update sl_cv_sources s1, sl_species s2 Set s1.tmp_kspecies = s2._key Where s1.osp = s2.name_en or s1.osp = s2.name_fr or s1.osp = s2.name_bot");
	$this->kfdb->Execute("Update sl_cv_sources s1, sl_species_syn s2 Set s1.tmp_kspecies = s2.fk_sl_species Where s1.osp = s2.name");
	//anything in sl_cv_sources at this point with tmp_kspecies == 0 is new species these should be ignored for the rest of the process
}
function normalizeCultivars(){//Normalize Cultivar Names
	$this->kfdb->Execute("Update sl_cv_sources s1, sl_pcv s2 Set s1.fk_sl_pcv = s2._key Where s1.tmp_kspecies = s2.fk_sl_species and s1.ocv = s2.name");
	$ocvlist = $this->kfdb->QueryRowsRA("Select sl_cv_sources.ocv From sl_cv_sources");
	foreach ($ocvlist as $ocv){
		$normalize = $this->kfdb->QueryRA("Select sl_species.name_en, sl_species.name_fr, sl_species.name_bot, sl_species._key, " .
			"sl_pcv._key, sl_pcv.fk_sl_species, sl_pcv_syn.fk_sl_pcv From sl_species " .
			"LEFT JOIN sl_pcv ON sl_species._key = sl_pcv.fk_sl_species " .
			"LEFT JOIN sl_pcv_syn ON sl_pcv._key = sl_pcv_syn.fk_sl_pcv " .
			"WHERE sl_pcv_syn.name = '".$ocv['ocv']."'");
		if (@$normalize != ''){
			$this->kfdb->Execute("Update sl_cv_sources s1 Set s1.fk_sl_pcv = '".$normalize[6]."' WHERE s1.ocv = '".$ocv['ocv']."'");
		}
	}
	//now any ocv where fk_sl_pcv == 0 are new cultivars or fuzzy matches
}
function soundslikeSetup(){//setup soundslike and set posible match to 1 for everything only needs to be run once
    $this->kfdb->Execute("Update sl_cv_sources set soundslike=metaphone(ocv)");
	$this->kfdb->Execute("Update sl_cv_sources set sl_cv_sources.pm='0'");
	//$this->kfdb->Execute("alter table sl_cv_sources add add_to_pcv varchar(128)");
	$this->kfdb->Execute("Update sl_cv_sources set sl_cv_sources.add_to_pcv='0'");
	$this->kfdb->Execute("Update sl_cv_sources set sl_cv_sources.tmp_match='0'");
}
function temptableSetup(){// create temp table for comparing entries in sl_cv_sources to each other
    $this->kfdb->Execute("Create temporary table sl_cv_sources_temp like sl_cv_sources");
	$this->kfdb->Execute("Insert into sl_cv_sources_temp select * from sl_cv_sources");
}
function compareInternal(){//Compare entries in sl_cv_sources to see if any match using the soundslike values
//ini_set('memory_limit', '-1');
		if (($dbc = $this->kfdb->CursorOpen("Select * From sl_cv_sources, sl_cv_sources_temp Where sl_cv_sources.soundslike = sl_cv_sources_temp.soundslike and sl_cv_sources.ocv != sl_cv_sources_temp.ocv and sl_cv_sources.pm = '0' and sl_cv_sources.tmp_match = '0'"))){
			$raRow = $this->kfdb->CursorFetch($dbc);
			//echo "CI <br>";
			if ($raRow['osp']!=''){
				$ospA = $raRow['osp'];
				$ocvA = $raRow['ocv'];
				$ospB = $raRow['8'];
				$ocvB = $raRow['9'];
				$slkey = $raRow['_key'];
				$this->kfdb->CursorClose($dbc);
				//$this->compareInternalQuestion($ospA,$ocvA,$ospB,$ocvB,$slkey);
				echo "sl_cv_sources vs sl_cv_sources <br> ".$ospA." ".$ocvA." Soundslike: ".$ospB." ".$ocvB."?<br>";
				echo "<form action='' method='POST'>"
    				//.$ospA." ".$ocvA." Soundslike: ".$ospB." ".$ocvB."?<br>"
					."<input type='hidden' name='ospA' Value='".$ospA."'>"
					."<input type='hidden' name='ocvA' Value='".$ocvA."'>"
					."<input type='hidden' name='ospB' Value='".$ospB."'>"
					."<input type='hidden' name='ocvB' Value='".$ocvB."'>"
					."<input type='hidden' name='_key' Value='".$slkey."'>"
					."<input type='hidden' name='t' Value='1'>"
					."<input type='submit' name='ansIn' Value='Yes'>"
					."<input type='submit' name='ansIn' Value='No'>"
					."</form><br>";
			}
			else{
				$this->doneInternal = TRUE;
				$this->kfdb->CursorClose($dbc);
				echo "Out of Matches In sl_cv_sources <br>";
			    echo "<form action='' method='POST'>"
			    ."<input type='hidden' name='t' Value='2'>"
			    ."<input type='submit' name='oomInt' Value='Ok'>"
			    ."</form><br>";

			}
			//$this->kfdb->CursorClose($dbc);
		}

}
function answerInternal(){
    if(@$_POST['ansIn'] == 'Yes'){
	    //echo "yes <br>";
	    $A1 = strval($_POST['ospA']);
		$A2 = strval($_POST['ocvA']);
		$B1 = strval($_POST['ospB']);
		$B2 = strval($_POST['ocvB']);
		$ospA = $_POST['ospA'];
		$ocvA = $_POST['ocvA'];
		$ospB = $_POST['ospB'];
		$ocvB = $_POST['ocvB'];
		$slkey = $_POST['_key'];
		$A = $A1." ".$A2;
		$B = $B1." ".$B2;
        echo "<form action='' method='POST'>" //Dont change osp/ocv anymore
        //update fk_sl_pcv to match _key of sl_pcv entry that it was matched to
        //add to sl_pcv_syn
        	."<input type='hidden' name='ospA' Value='".$ospA."'>"
			."<input type='hidden' name='ocvA' Value='".$ocvA."'>"
			."<input type='hidden' name='ospB' Value='".$ospB."'>"
			."<input type='hidden' name='ocvB' Value='".$ocvB."'>"
			."<input type='hidden' name='_key' Value='".$slkey."'>"
			."<input type='hidden' name='t' Value='3'>"
        	."Keep"
			."<input type='submit' name='ansIn2' Value='$A'>"
			." or "
			."<input type='submit' name='ansIn2' Value='$B'>"
			."</form><br>";



	}
	elseif(@$_POST['ansIn'] == 'No'){
		$A1 = strval($_POST['ospA']);
		$A2 = strval($_POST['ocvA']);
		$B1 = strval($_POST['ospB']);
		$B2 = strval($_POST['ocvB']);
	    $this->kfdb->Execute("Update sl_cv_sources s1 Set s1.pm='1' Where s1.osp='".$_POST['ospA']."' and s1.ocv='".$_POST['ocvA']."'");
    	$this->kfdb->Execute("Update sl_cv_sources s1 Set s1.pm='1' Where s1.osp='".$_POST['ospB']."' and s1.ocv='".$_POST['ocvB']."'");
	$this->kInt = TRUE;
	 echo "You answered No ".$A1." ".$A2." is not ".$B1." ".$B2."<br>";
	    echo "<form action='' method='POST'>"
			    ."<input type='hidden' name='t' Value='4'>"
			    ."<input type='submit' name='nxtInt' Value='Next'>"
			    ."</form><br>";
	}

}
function keepInternal(){
	$A1 = strval($_POST['ospA']);
	$A2 = strval($_POST['ocvA']);
	$B1 = strval($_POST['ospB']);
	$B2 = strval($_POST['ocvB']);
	$ospA = $_POST['ospA'];
	$ocvA = $_POST['ocvA'];
	$ospB = $_POST['ospB'];
	$ocvB = $_POST['ocvB'];
	$slkey = $_POST['_key'];
	$A = $A1." ".$A2;
	$B = $B1." ".$B2;


	if (@$_POST['ansIn2'] == $A){

		$this->kInt = TRUE;
		$this->kfdb->Execute("Update sl_cv_sources s1 Set s1.pm='1' Where s1.osp='".$_POST['ospB']."' and s1.ocv='".$_POST['ocvB']."'");
		$this->kfdb->Execute("Update sl_cv_sources s1 Set s1.tmp_match='$A' Where s1.osp='".$_POST['ospB']."' and s1.ocv='".$_POST['ocvB']."'");
		$this->kfdb->Execute("Update sl_cv_sources s1 Set s1.tmp_match='$A' Where s1.osp='".$_POST['ospA']."' and s1.ocv='".$_POST['ocvA']."'");
		$this->kfdb->Execute("Update sl_cv_sources s1 Set s1.add_to_pcv='1' Where s1.osp='".$_POST['ospB']."' and s1.ocv='".$_POST['ocvB']."'");
		echo "You kept $A <br>";
		echo "<form action='' method='POST'>"
			    ."<input type='hidden' name='t' Value='5'>"
			    ."<input type='submit' name='ansIntA' Value='Next'>"
			    ."</form><br>";//echo "<meta http-equiv='REFRESH' content='0;url={$_SERVER['PHP_SELF']}?cmd=updatefromslcvsources'>";
	}
	if (@$_POST['ansIn2'] == $B){

		$this->kInt = TRUE;
		$this->kfdb->Execute("Update sl_cv_sources s1 Set s1.pm='1' Where s1.osp='".$_POST['ospA']."' and s1.ocv='".$_POST['ocvA']."'");
		$this->kfdb->Execute("Update sl_cv_sources s1 Set s1.tmp_match='$B' Where s1.osp='".$_POST['ospB']."' and s1.ocv='".$_POST['ocvB']."'");
		$this->kfdb->Execute("UpdacompareExternal()te sl_cv_sources s1 Set s1.tmp_match='$B' Where s1.osp='".$_POST['ospA']."' and s1.ocv='".$_POST['ocvA']."'");
		$this->kfdb->Execute("Update sl_cv_sources s1 Set s1.add_to_pcv='1' Where s1.osp='".$_POST['ospA']."' and s1.ocv='".$_POST['ocvA']."'");
		echo "You kept $B <br>";
		echo "<form action='' method='POST'>"
			    ."<input type='hidden' name='t' Value='6'>"
			    ."<input type='submit' name='ansIntB' Value='Next'>"
			    ."</form><br>";//echo "<meta http-equiv='REFRESH' content='0;url={$_SERVER['PHP_SELF']}?cmd=updatefromslcvsources'>";
	}

}
function compareExternal(){
	//ini_set('memory_limit', '-1');
	if (($dbc = $this->kfdb->CursorOpen("select * from sl_cv_sources s1, sl_pcv s2 where s1.pm='0' and s1.osp=s2.psp and (INSTR(s2.soundslike,CONCAT(';',s1.soundslike)) or INSTR(s2.soundslike,CONCAT(s1.soundslike,';')) or (s2.soundslike = s1.soundslike) )"))){
		$raRow = $this->kfdb->CursorFetch($dbc);
		//var_dump($raRow);
		if ($raRow['osp']!=''){
			echo "sl_cv_sources vs sl_pcv <br> sl_cv_sources: ".$raRow['osp']." ".$raRow['ocv']." Soundslike: ".$raRow['psp']." ".$raRow['name']."?";
			$ospS = $raRow['osp'];
			$ocvS = $raRow['ocv'];
			$pspP = $raRow['psp'];
			$nameP = $raRow['name'];
			$slpcvkey = $raRow['_key'];
			$this->kfdb->CursorClose($dbc);
			echo "<form action='' method='POST'>"
				 ."<input type='hidden' name='osp' Value='".$ospS."'>"
				 ."<input type='hidden' name='ocv' Value='".$ocvS."'>"
				 ."<input type='hidden' name='psp' Value='".$pspP."'>"
				 ."<input type='hidden' name='name' Value='".$nameP."'>"
				 ."<input type='hidden' name='slpcvkey' Value='".$slpcvkey."'>"
				 ."<input type='hidden' name='t' Value='7'>"
				 ."<input type='submit' name='ansEx' Value='Yes'>"
				 ."<input type='submit' name='ansEx' Value='No'>"
				 ."</form><br>";
		}
		else{

			$this->kfdb->CursorClose($dbc);
			echo "Out of Matches Between sl_pcv and sl_cv_sources";
			echo "<form action='' method='POST'>"
			    ."<input type='hidden' name='t' Value='8'>"
			    ."<input type='submit' name='oomExt' Value='Ok'>"
			    ."</form><br>";
		}

		//$this->kfdb->CursorClose($dbc);
	}
}
function answerExternal(){
	$A1 = strval($_POST['osp']);
	$A2 = strval($_POST['ocv']);
	$A = $A1." ".$A2;
	if(@$_POST['ansEx'] == 'Yes') {
	$this->doneInternal = TRUE;
		$this->kfdb->Execute("Update sl_cv_sources s1 Set s1.fk_sl_pcv='".$_POST['slpcvkey']."' Where s1.osp='".$_POST['osp']."' and s1.ocv='".$_POST['ocv']."'");

		$this->kfdb->Execute("Insert Into sl_pcv_syn (fk_sl_pcv,name) select sl_cv_sources.fk_sl_pcv,sl_cv_sources.ocv from sl_cv_sources, sl_pcv_syn where sl_cv_sources.tmp_match = '$A' and sl_cv_sources.ocv <> sl_pcv_syn.name"); //insert any sl_cv_sources entries with matching tmp_match into synonyms if the dont already exist there this will include itself

		$this->kfdb->Execute("Update sl_cv_sources s1 Set s1.pm='1' Where s1.osp='".$_POST['osp']."' and s1.ocv='".$_POST['ocv']."'");

		$this->kfdb->Execute("Update sl_cv_sources s1 Set s1.add_to_pcv='1' Where s1.osp='".$_POST['osp']."' and s1.ocv='".$_POST['ocv']."'");

			echo "You answered Yes $A is a synonym<br>";
			echo "<form action='' method='POST'>"
			    ."<input type='hidden' name='t' Value='9'>"
			    ."<input type='submit' name='ansExtY' Value='Ok'>"
			    ."</form><br>";
    }
    elseif(@$_POST['ansEx'] == 'No'){
        $this->doneInternal = TRUE;
         //not a synonym, no longer possible match
		$this->kfdb->Execute("Update sl_cv_sources s1 Set s1.pm='1' Where s1.osp='".$_POST['osp']."' and s1.ocv='".$_POST['ocv']."'");

		echo "You answered No $A is not a synonym<br>";
			echo "<form action='' method='POST'>"
			    ."<input type='hidden' name='t' Value='10'>"
			    ."<input type='submit' name='ansExtN' Value='Ok'>"
			    ."</form><br>";

		}
}

function addSynonyms(){

}
function addPcv(){
	echo "All relavent entries Added to sl_pcv";
		$this->kfdb->Execute("Insert into sl_pcv (psp,name,fk_sl_species) select sl_cv_sources.osp, sl_cv_sources.ocv, sl_cv_sources.tmp_kspecies from sl_cv_sources where add_to_pcv ='0'");

}
function clearSl_cv_sources(){

}

    function speciesFix()
    {
        $this->kfdb->Execute( "UPDATE sl_cv_sources C,sl_species S SET C.fk_sl_species=S._key WHERE C.osp=S.name_en AND C.fk_sl_species=0" );
        $this->kfdb->Execute( "UPDATE sl_cv_sources C,sl_species S SET C.fk_sl_species=S._key WHERE C.osp=S.name_bot AND C.fk_sl_species=0" );
    }

    function pgrcDownloadFiles()
    {
        foreach( $this->raNPGS as $raG ) {
            $genus = $raG['genus'];
            foreach( $raG['species'] as $species ){
                echo $this->pgrcGetFile( $genus, $species );
                echo "<BR/>";
                flush();
            }
        }
        return( "<p>Done</p>" );
    }

    function pgrcGetFile( $genus, $species )
    {
        $s = "";

        $PGRCfile = $genus.'%20'.$species;
        $fname1 = $genus."_".$species;

        //$url = "http://pgrc3.agr.ca/cgi-bin/npgs/html/acc_list_post.pl?uniform=Any%20Status&recent=anytime&acimpt=Any%20Status"
        //      ."&D1=ALL%20-%20All%20Repositories&taxon=$PGRCsearch&records=10000&pyears=1";

        //$url = "http://pgrc3.agr.gc.ca/cgi-bin/npgs/html/acc_query.pl?lopi=&hipi=&plantid=&pedigree=&taxon=allium%20cepa&family=&cname=&D1=ALL+-+All+Repositories&acimpt=Any+Status&uniform=Any+Status&country=&state=&recent=anytime&pyears=1&received=&records=10000";

        $url = "http://pgrc3.agr.gc.ca/cgi-bin/npgs/html/acc_query.pl?taxon=$PGRCfile&records=10000";

        $file = DIR_SL_DOWNLOAD."pgrc/$fname1.htm";

        if( file_exists( $file ) ) {
            return( "PGRC file $fname1 already downloaded" );
        }

        $fp = fopen( $file, 'w' ) or die( "Can't open file ".$file );

        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FILE, $fp);

        curl_exec($ch) OR die( "Error in curl_exec()" );

        fclose($fp);
        curl_close($ch);

        return( "Downloaded PGRC file $fname1" );
    }


    function pgrcLoadFiles()
    {
//$this->kfdb->SetDebug(2);
        $s = "";

        $this->kfdb->Execute( "DELETE FROM sl_cv_sources WHERE fk_sl_sources='1'" );

        foreach( $this->raNPGS as $raG ) {
            $nGCount = 0;
            $genus = $raG['genus'];
            foreach( $raG['species'] as $species ){
                $nGCount = 0;
                $fname1 = $genus."_".$species;
                $osp = $genus." ".$species;

                $file = DIR_SL_DOWNLOAD."pgrc/$fname1.htm";


                if( ($webpage = @file_get_contents($file)) ) {
                    $haystack = $webpage;
                    $matches = array();
                    @preg_match_all( "/<i>$osp<\/i>(.*?)<\/A>/s", $haystack, $matches );
                    if( $matches[1] ) {
                        foreach( $matches[1] as $ocv ) {
                            $ocv = trim( $ocv );
                            $this->kfdb->Execute( "INSERT INTO sl_cv_sources (fk_sl_sources,fk_sl_pcv,osp,ocv) VALUES(1,0,'$osp','$ocv')" );
                            ++$nGCount;
                        }
                    }
/*
                    preg_match('/<DL>(.*)<\/DL>/s', $haystack, $matches);
                    @preg_match_all('/<DT>.*?<p>/s',$matches[0], $lines);
                        foreach ($lines[0] as $l){
                            if(preg_match('/<\/i>.*?<BR>/s',$l,$x)){}elseif(preg_match('/<\/i>.*?<p>/s',$l,$x)){}
                                $PGRCn = str_replace('<BR>','',$x[0]);
                                $PGRCna = str_replace('<p>','',$PGRCn);
                                $PGRCnam = str_replace('<i>','',$PGRCna);
                                $PGRCname = str_replace('</i>','',$PGRCnam);
                                $PGRCname2 = str_replace('var.', '',$PGRCname);
                                $PGRCname3 = str_replace('subsp.', '',$PGRCname2);

                                //insert into database
                                $this->kfdb->Execute("INSERT INTO sl_cv_sources (osp,ocv) VALUES('$osp','".$PGRCname3."')");

                        }
*/
                    echo "PGRC file : $fname1 : loaded $nGCount<BR/>";
                } else {
                    echo "PGRC file not loaded: $fname1<BR/>";
                }
                flush();
            }
        }
        return( "<p>Done</p>" );
    }


    function npgsDownloadFiles()
    {
        foreach( $this->raNPGS as $raG ) {
            echo $this->npgsGetFile( $raG['genus'] );
            echo "<BR/>";
            flush();
        }
        return( "<p>Done</p>" );
    }

	function npgsGetFile( $genus )
	{
	    $s = "";
	    $url = "http://www.ars-grin.gov/~dbmuqs/cgi-bin/ex_mcpd.pl?genus=".$genus;
		$file = DIR_SL_DOWNLOAD.'npgs/'.$genus.'.csv';

		if( file_exists( $file ) ) {
		    return( "NPGS file $genus already downloaded" );
		}

		$fp = fopen( $file, 'w' ) or die( "Can't open file ".$file );

		$ch = curl_init($url);

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_FILE, $fp);

		curl_exec($ch) OR die( "Error in curl_exec()" );

		fclose($fp);
		curl_close($ch);

		return( "Downloaded NPGS file $genus" );
	}

    private $raNPGS = array(
        array( 'genus' => "Allium",     'species'=>array("cepa", "fistulosum", "porrum","sativum") ),
        array( 'genus' => "Amaranthus", 'species'=>array("crispus","hypochondriacus","tricolor") ),
        array( 'genus' => "Anethum",    'species'=>array("graveolens") ),
        array( 'genus' => "Asparagus",  'species'=>array("officinalis") ),
        array( 'genus' => "Atriplex",   'species'=>array("hortensis") ),
        array( 'genus' => "Avena",      'species'=>array("nuda","sativa") ),
        array( 'genus' => "Beta",       'species'=>array("vulgaris") ),
        array( 'genus' => "Borago",     'species'=>array() ),
        array( 'genus' => "Brassica",   'species'=>array("oleracea","napus","rapa","ruvo","juncea","nigra") ),
        array( 'genus' => "Capsicum",   'species'=>array("annuum","frutescens") ),
        array( 'genus' => "Cucumis",    'species'=>array("anguria","melo","sativus") ),
        array( 'genus' => "Cucurbita",  'species'=>array("argyrosperma","maxima","mixta","moschata","pepo") ),
        array( 'genus' => "Cichorium",  'species'=>array("endivia") ),
        array( 'genus' => "Citrullus",  'species'=>array("lanatus") ),
        array( 'genus' => "Daucus",     'species'=>array("carota") ),
        array( 'genus' => "Glycine",    'species'=>array("max") ),
        array( 'genus' => "Helianthus", 'species'=>array("annuus","tuberosum") ),
        array( 'genus' => "Hordeum",    'species'=>array("vulgare") ),
        array( 'genus' => "Lactuca",    'species'=>array("sativa") ),
        array( 'genus' => "Lens",       'species'=>array("culinaris") ),
        array( 'genus' => "Pastinaca",  'species'=>array("sativa") ),
        array( 'genus' => "Phaseolus",  'species'=>array("coccineus","lunatus","vulgaris") ),
        array( 'genus' => "Physalis",   'species'=>array("longifolia","philadelphica","pubescens") ),
        array( 'genus' => "Pisum",      'species'=>array("sativum") ),
        array( 'genus' => "Solanum",    'species'=>array("cheesmaniae","lycopersicum","melongena","tuberosum") ),
        array( 'genus' => "Triticum",   'species'=>array("aestivum","spelta","turgidum") ),
        array( 'genus' => "Vicia",      'species'=>array("faba") ),
        array( 'genus' => "Vigna",      'species'=>array("angularis","mungo","radiata","unguiculata") ),
        array( 'genus' => "Zea",        'species'=>array("mays") ),

        );

    function npgsLoadFiles()
    {
//$this->kfdb->SetDebug(2);
        $s = "";

        $this->kfdb->Execute( "DELETE FROM sl_cv_sources WHERE fk_sl_sources='2'" );

        foreach( $this->raNPGS as $raG ){
            $genus = $raG['genus'];
            $file = DIR_SL_DOWNLOAD.'npgs/'.$genus.'.csv';

            if( !file_exists( $file ) ) {
                $s .= "NPGS file $genus not found<BR/>";
                continue;
            }

            $this->kfdb->Execute( "DROP TABLE IF EXISTS sl_npgs_tmp" );
            $this->kfdb->Execute( "CREATE TABLE sl_npgs_tmp ( "
                             // columns loaded from csv
                             ."    genus         text,"
                             ."    species       text,"
                             ."    accename      text,"
                             ."    avail         text,"
                             // columns processed after load
                             ."    osp           text,"
                             ."    ocv           text,"
                             ."    k             integer default 0"
                             ."    )" );


            $raP = array( 'raCols' => array( 'genus'=>'text', 'species'=>'text', 'accename'=>'text', 'avail'=>'text' ),
                          'raTrimCols' => array( 'genus', 'species', 'accename' ),
                          'deleteIfBlank' => 'accename' );
            if( !SEEDCSV_LoadDataToDB( $this->kfdb, $file, "sl_npgs_tmp", $raP ) ) {
                $s .= "<P style='color:red'>".$raP['sErrMsg'].":".$this->kfdb->GetErrMsg()."</P>";
                continue;
            }
            $s .= "Loaded NPGS file $genus<BR/>";

            /* Remove rows for uninteresting species
             */
            $raDel = array();
            foreach( $raG['species'] as $sp ) {
                $raDel[] = "'".addslashes($sp)."'";
            }
            $this->kfdb->Execute( "DELETE FROM sl_npgs_tmp WHERE species NOT IN (".implode( ',', $raDel ).")" );

            $this->kfdb->Execute( "DELETE FROM sl_npgs_tmp WHERE "
                                     ."avail<>'Y' OR "
                                     ."accename='\"\"' "
                                     ."OR accename > 0"  // remove names that are integer numbers (if this doesn't work could use where accename REGEXP '^-?[0-9]+$'
                                );


            $this->kfdb->Execute( "UPDATE sl_npgs_tmp SET osp=concat(genus,' ',species), ocv=accename" );

            $this->kfdb->Execute( "INSERT INTO sl_cv_sources (fk_sl_sources,fk_sl_pcv,osp,ocv) SELECT 2,0,osp,ocv FROM sl_npgs_tmp" );
        }

        //$this->kfdb->Execute( "DROP TABLE IF EXISTS sl_npgs_tmp" );

        return( $s );
    }


    function npgsEachRow($raRow)
    {//echo ". <br/>";
    	//var_dump($raRow);
    	//set_time_limit(500);
        if( $raRow['genus'] == $this->npgsEachRow_species['genus'] &&
            in_array( $raRow['species'], $this->npgsEachRow_species['species'] ) &&
            !empty($raRow['accename']) )
        {

        	$fld_CVSources = array(
            array("col"=>"fk_sl_sources", "type"=>"K"),
            array("col"=>"osp",           "type"=>"S"),
            array("col"=>"ocv",           "type"=>"S"));

			$kfreldef_CVSources = array(
            "Tables" => array( array( "Table" => 'sl_cv_sources', "Alias" => 'C',
                                      "Fields" => $fld_CVSources ) ) );

			$kfrelCVSources = new KeyFrameRelation( $this->kfdb, $kfreldef_CVSources,$this->sess->GetUID() );
//$kfrelCVSources->kfdb->SetDebug(2);

            $raRow['avail'] = ($raRow['avail']=='Y');
            $this->nLoaded++;
            if( $raRow['avail'] ) $this->nAvail++;

/*
$kfr->SetValue( 'fk_sl_sources', '2');
            $kfr->SetValue( 'osp', $raRow['genus']." ".$raRow['species'] );
            $kfr->SetValue( 'ocv', $raRow['accename'] );
**/
            $kfr = $kfrelCVSources->CreateRecord();

            $kfr->SetValue( 'fk_sl_sources', '2');
            $kfr->SetValue( 'osp', $raRow['genus']." ".$raRow['species'] );
            $kfr->SetValue( 'ocv', $raRow['accename'] );

			//var_dump($kfr); echo "<BR/>";

			$kfr->PutDBRow();

            //var_dump($raRow);echo "<BR/>";
        }
        return( false );  // don't store the row in SEEDCSV's array because this could be a very large table
    }

	function npgsEachRow2($raRow)
    {
    	//var_dump($raRow);
    	   	$fld_CVSources = array(
            array("col"=>"fk_sl_sources", "type"=>"K"),
            array("col"=>"osp",           "type"=>"S"),
            array("col"=>"ocv",           "type"=>"S"));

			$kfreldef_CVSources = array(
            "Tables" => array( array( "Table" => 'sl_cv_sources', "Alias" => 'C',
                                      "Fields" => $fld_CVSources ) ) );

			$kfrelCVSources = new KeyFrameRelation( $this->kfdb, $kfreldef_CVSources,$this->sess->GetUID() );
//$kfrelCVSources->kfdb->SetDebug(2);


            $this->nLoaded++;

            $kfr = $kfrelCVSources->CreateRecord();
echo ". <br/>";
            $kfr->SetValue( 'fk_sl_sources', '2');
            $kfr->SetValue( 'osp', $raRow['1']." ".$raRow['2'] );
            $kfr->SetValue( 'ocv', $raRow['3'] );

			//var_dump($kfr); echo "<BR/>";

			$kfr->PutDBRow();

            //var_dump($raRow);echo "<BR/>";

        return( false );  // don't store the row in SEEDCSV's array because this could be a very large table
    }

    function companyEachRow($raRow)
    {

		//$c_names = array("Richters Herbs","Annapolis Seeds","Harmonic Herbs"); //hardcoded company names
		//$c_names_ID = array("1","3","12");									   //hardcoded company names IDs

		//set_time_limit(500);
    	$fld_CVSources = array(
            array("col"=>"fk_sl_sources", "type"=>"K"),
            array("col"=>"osp",           "type"=>"S"),
            array("col"=>"ocv",           "type"=>"S"),
            array("col"=>"bOrganic", 	  "type"=>"B"));

			$kfreldef_CVSources = array(
            "Tables" => array( array( "Table" => 'sl_cv_sources', "Alias" => 'C',
                                      "Fields" => $fld_CVSources ) ) );

			$kfrelCVSources = new KeyFrameRelation( $this->kfdb, $kfreldef_CVSources,$this->sess->GetUID() );
//$kfrelCVSources->kfdb->SetDebug(2);


            $kfr = $kfrelCVSources->CreateRecord();

            $kfr->SetValue( 'osp', $raRow['species'] );
            $kfr->SetValue( 'ocv', $raRow['cultivar'] );
            if ($raRow['organic'] == 'x'){
                $kfr->SetValue( 'bOrganic', '0' );
            }else{
                $kfr->SetValue( 'bOrganic', '1' );
            }
            //$i = 0;
          //  for($i;$i < count($c_names);$i++){Seed Library database
           // 	if ($raRow['Company'] == $c_names[$i]){
			//		$kfr->SetValue('fk_sl_sources', $c_names_ID[$i]);
           // 	}
         //   }
			//var_dump($kfr); echo "<BR/>";

			$kfr->PutDBRow();

        return( false );
    }

	function pcvTableFill(){

/*	    $s = "";


                    //Update keys of any in c_pcv that are already in sl_pcv so then the ones needed to be added are the ones with null keys
                    $this->kfdb->Execute("UPDATE csv_pcv,sl_pcv SET csv_pcv.k = sl_pcv._key WHERE csv_pcv.osp = sl_pcv.psp AND csv_pcv.ocv = sl_pcv.name");

                    //$qu = $this->kfdb->QueryRowsRA("SELECT osp,ocv,k FROM csv_pcv"); //testing only remove later

                    //foreach ($qu as $q){                                             //testing only remove later
                    //  echo $q['osp']." ".$q['ocv']." :Key:".$q['k']; echo "<br/>"; //testing only remove later
                    //}                                                                //testing only remove later

                    $this->kfdb->Execute("INSERT INTO sl_pcv (psp,name) SELECT osp,ocv FROM csv_pcv WHERE csv_pcv.k = '0'");

/*** should be slower than doing it all in database but if database keeps running out of memory this might be the way to go
$t = $this->kfdb->QueryRowsRA("SELECT sl_pcv._key, psp, name FROM sl_pcv");

foreach($t as $ar){
    $this->kfdb->Execute("UPDATE csv_pcv SET k = ".$ar['_key']." WHERE csv_pcv.osp = \"".$ar['psp']."\" AND csv_pcv.ocv = \"".$ar['name']."\""); //updates csv_pcv to contain already known keys
}

$x = $this->kfdb->QueryRowsRA("SELECT csv_pcv.osp, csv_pcv.ocv FROM csv_pcv WHERE csv_pcv.k = '0'");

foreach($x as $ar){
    $this->kfdb->Execute("INSERT INTO sl_pcv (psp,name) VALUES (\"".$ar['osp']."\",\"".$ar['ocv']."\")");
}
***/










                 $fld_CVSources = array(
            						 array("col"=>"fk_sl_sources", "type"=>"K"),
           							 array("col"=>"osp",           "type"=>"S"),
            						 array("col"=>"ocv",           "type"=>"S"));

					$kfreldef_CVSources = array(
            			 "Tables" => array( array( "Table" => 'sl_cv_sources', "Alias" => 'C',
                                      			   "Fields" => $fld_CVSources ) ) );


					$kfrelCVSources = new KeyFrameRelation( $this->kfdb, $kfreldef_CVSources,$this->sess->GetUID() );
//$kfrelCVSources->kfdb->SetDebug(2);*/

				$t = $this->kfdb->QueryRowsRA("SELECT sl_pcv._key, psp, name FROM sl_pcv");

				$s ="";

				//if(count($t)){Seed Library database
				    foreach($t as $ar){
				        //$s .= "<TR><TD>OLD: ".$ar['psp']."</TD><TD>".$ar['name']."</TD><TD>".$ar['_key']."<TD/></TR>";
						$this->kfdb->Execute("UPDATE sl_cv_sources SET fk_sl_pcv = ".$ar['_key']." WHERE sl_cv_sources.osp = '".$ar['psp']."' AND sl_cv_sources.ocv = '".$ar['name']."'");//updates sl_cv_sources to contain already known keys
				    }
				//}

				$x = $this->kfdb->QueryRowsRA("SELECT sl_cv_sources.osp, sl_cv_sources.ocv, sl_cv_sources.fk_sl_pcv FROM sl_cv_sources WHERE sl_cv_sources.fk_sl_pcv = '0'");

				$j=0;
				//if(count($x)){
				    foreach($x as $ar){
				        //$s .= "<TR><TD>NEW: ".$ar['osp']."</TD><TD>".$ar['ocv']."</TD><TD>".$ar['fk_sl_pcv']."<TD/></TR>";
						$this->kfdb->Execute("INSERT INTO sl_pcv (psp,name) VALUES (\"".$ar['osp']."\",\"".$ar['ocv']."\")");
				    	$j = $j + 1;
				    }
				//}

				$s .= "<P>Added ".$j." Entrys. </P>";

                 return ($s);

	}

}



class CompanyUpload
{
    private $oW;
    private $kfdb;
    private $sess;
    private $companyTableDef;    // SEEDTable columns for upload/download

    private $tmpFname; // DIR_SL_DOWNLOAD."slsrctmp.csv"

    private $oStep;

    function __construct( SEEDApp_WorkerC $oW, $companyTableDef )
    {
        $this->oW = $oW;
        $this->kfdb = $oW->kfdb;    // deprecate, use $oW->kfdb
        $this->sess = $oW->sess;    // deprecate, ...
        $this->companyTableDef = $companyTableDef;

/*
        $this->tmpFname = realpath(dirname($_SERVER['SCRIPT_FILENAME']))."/".DIR_SL_DOWNLOAD."slsrctmp.csv";
//TODO: this is necessary on Windows because realpath returns '\' and mysql hates them.  Does realpath have an option to do the right thing?
        $this->tmpFname = str_replace( "\\", '/', $this->tmpFname );
*/

        $stepDef = array( "Title_EN" => "Upload Seed Company Spreadsheet",
                          "Steps" => array( array( "fn"=>array($this,'Step1_Upload'),   "Title_EN"=>"Upload File" ),
                                            array( "fn"=>array($this,'Step2_Validate'), "Title_EN"=>"Validate Data" ),
                                        //  array( "fn"=>array($this,'Step3_Archive'),  "Title_EN"=>"Archive Old Data" ),
                                            array( "fn"=>array($this,'Step3_Review'),   "Title_EN"=>"Review" ),
                                            array( "fn"=>array($this,'Step4_Commit'),   "Title_EN"=>"Commit" ),
                                          ) );
        $raParms = array(); // some clients have to store kfdb here but this one is using this::fn for stepper so raParms is unused
        $this->oStep = new Console01_Stepper( $stepDef, $raParms );
    }

    function DrawStep( $a )  { return( $this->oStep->DrawStep( $a ) ); }

    function Step1_Upload( $raParms )
    /********************************
        Get rows from the spreadsheet file, put them in a temporary table, validate and prepare to commit
     */
    {
        $bOk = false;
        $s = "";

        // Determine how to handle rows in sl_cv_sources that aren't mentioned in the spreadsheet
        // For now, assume this is given by an http parm but it should probably be a parm to the constructor
        $eReplace = SEEDInput_Smart( 'eReplace', array( SLUploadCVSources::ReplaceVerbatimRows, SLUploadCVSources::ReplaceWholeCompanies, SLUploadCVSources::ReplaceWholeCSCI ) );

        /* Load the uploaded spreadsheet into an array
         */
        $raSEEDTableLoadParms = array();
        switch( SEEDSafeGPC_GetStrPlain('upfile-format') ) {
            case 'xls':
            default:
                $raSEEDTableLoadParms['bCSV'] = false;
                $raSEEDTableLoadParms['charset-file'] = "utf-8";    // not actually used because xls is always utf-8
                break;
            case 'csv-utf8':
                $raSEEDTableLoadParms['bCSV'] = true;
                $raSEEDTableLoadParms['charset-file'] = "utf-8";
                break;
            case 'csv-win1252':
                $raSEEDTableLoadParms['bCSV'] = true;
                $raSEEDTableLoadParms['charset-file'] = "Windows-1252";
                break;
        }

        list($ok,$raRows,$sErrMsg) = SEEDTable_LoadFromUploadedFile( 'upfile', array( 'raSEEDTableDef'=> $this->companyTableDef,
                                                                                      'raSEEDTableLoadParms' => $raSEEDTableLoadParms ) );
        if( !$ok ) {
            $this->oW->oC->ErrMsg( $sErrMsg );
            goto done;
        }
        $s .= "<p>File uploaded successfully.</p>";

        /* Copy the spreadsheet rows into a temporary table.
         * Remove blank rows (company or species blank).
         * Rows are grouped in the table by a number kUpload, which will be propagated to a confirm command so it can copy those rows to sl_cv_sources.
         */
        $oUpload = new SLUploadCVSources( $this->oW, 0, $eReplace );
        list($ok,$sOk,$sWarn,$sErr) = $oUpload->Load( $raRows );
        $s .= $this->output( $ok, $sOk, $sWarn, $sErr );
        if( !$ok ) goto done;

        if( !$oUpload->kUpload ) goto done;    // shouldn't happen, but bad if it does

        $this->oW->oC->oSVA->VarSet( 'companyUploadKey', $oUpload->kUpload );
        $this->oW->oC->oSVA->VarSet( 'companyUploadReplace', $eReplace );

        $bOk = true;

        done:
        return( $this->stepperRet( $bOk, $s, false ) );
    }

    function Step2_Validate( $raParms )
    {
        $s = "";
        $ok = false;

        $kUpload = $this->oW->oC->oSVA->VarGet( 'companyUploadKey' ) or die( "No kUpload" );
        $eReplace = $this->oW->oC->oSVA->VarGet( 'companyUploadReplace' ) or die( "No eReplace" );
        $oUpload = new SLUploadCVSources( $this->oW, $kUpload, $eReplace );

        list($ok,$sOk,$sWarn,$sErr) = $oUpload->Validate();
        $s = $this->output( $ok, $sOk, $sWarn, $sErr );

        return( $this->stepperRet( $ok, $s, false ) );
    }

    function Step3_Review( $raParms )
    {
        $s = "";
        $ok = false;

        $kUpload = $this->oW->oC->oSVA->VarGet( 'companyUploadKey' ) or die( "No kUpload" );
        $eReplace = $this->oW->oC->oSVA->VarGet( 'companyUploadReplace' ) or die( "No eReplace" );
        $oUpload = new SLUploadCVSources( $this->oW, $kUpload, $eReplace );

        list($ok,$sOk,$sWarn,$sErr) = $oUpload->Archive();
        $s = $this->output( $ok, $sOk, $sWarn, $sErr );

        return( $this->stepperRet( $ok, $s, false ) );
    }

    function Step4_Commit( $raParms )
    {
        $s = "";
        $ok = false;

        $kUpload = $this->oW->oC->oSVA->VarGet( 'companyUploadKey' ) or die( "No kUpload" );
        $eReplace = $this->oW->oC->oSVA->VarGet( 'companyUploadReplace' ) or die( "No eReplace" );
        $oUpload = new SLUploadCVSources( $this->oW, $kUpload, $eReplace );

        list($ok,$sOk,$sWarn,$sErr) = $oUpload->Commit();
        $s = $this->output( $ok, $sOk, $sWarn, $sErr );

        $ok = true;

        done:
        return( $this->stepperRet( $ok, $s, true ) );
    }

    private function output( $ok, $sOk, $sWarn, $sErr )
    {
        $s = "";

        if( $ok ) {
            $s .= $sOk;
            if( $sWarn ) {
                $s .= "<div class='alert alert-warning'>$sWarn<br/><br/>Please correct these problems and upload again.</div>";
            }
        } else {
            $s .= "<div class='alert alert-danger'>$sErr</div>";
        }

        return( $s );
    }

    function Step2_obsolete()
    {

// $this->kfdb->SetDebug(2);
        $s = "";
        $ok = true;


        $raP = array( 'raCols' => array( 'k'=>'integer', 'company'=>'text', 'species'=>'text', 'variety'=>'text', 'organic'=>'text' ),
                      'raTrimCols' => array( 'company', 'species', 'variety' ),
                      'raDeleteIfBlank' => array('company','species') );
        if( !SEEDCSV_LoadDataToDB( $this->kfdb, $this->tmpFname, "sl_src_tmp", $raP ) ) {
            $s .= "<P style='color:red'>".$raP['sErrMsg'].":".$this->kfdb->GetErrMsg()."</P>";
            $ok = false;
        }


// TODO: if spold and cvold columns exist, load them and show them in the sample table below
//       if k does not exist, set it to zero so the key-check code can try to match it up
//       if company does not exist, this is a single-source input file so load whatever exists of k,sp,cv,spold,cvold and set the company


        /* Show the user what was uploaded so they can decide whether to continue
         */
        if( ($dbc = $this->kfdb->CursorOpen( "SELECT * FROM sl_src_tmp LIMIT 10" )) ) {
            $s .= "<style>"
                 .".cu_ReadFileTable {float:right;margin-left:20px;}"
                 .".cu_ReadFileTable td, .cu_ReadFileTable th {font-size:small}"
                 ."</style>"
                 ."<table class='cu_ReadFileTable' border='1' cellpadding='5' cellspacing='0'>"
                 ."<tr><th colspan='5'>The spreadsheet must have columns with these names</th><th colspan='2'>and optionally these</th></tr>"
                 ."<TR><TH>key</TH><TH>company</TH><TH>species</TH><TH>variety</TH><TH>organic</TH><th>sp_old</th><th>var_old</th></TR>";
            while( $ra = $this->kfdb->CursorFetch($dbc) ) {
                $s .= SEEDStd_ArrayExpand( $ra, "<TR><TD>[[k]]&nbsp;</TD><TD>[[company]]&nbsp;</TD><TD>[[species]]&nbsp;</TD><TD>[[variety]]&nbsp;</TD><TD>[[organic]]&nbsp;</TD><td>[[sp_old]]&nbsp;</td><td>[[var_old]]&nbsp;</td></TR>" );
            }
            $s .= "</TABLE>";
        }


        $nRows      = $this->kfdb->Query1( "SELECT count(*) FROM sl_src_tmp" );
        $nSpecies   = $this->kfdb->Query1( "SELECT count(distinct species) FROM sl_src_tmp" );
        $nVarieties = $this->kfdb->Query1( "SELECT count(distinct species,variety) FROM sl_src_tmp" );
        $nCompanies = $this->kfdb->Query1( "SELECT count(distinct company) FROM sl_src_tmp" );
        $nOrganic   = $this->kfdb->Query1( "SELECT count(*) FROM sl_src_tmp WHERE bOrganic" );

        $s .= "<P>Uploaded $nRows listings for :</P>"
             ."<UL><LI>$nCompanies companies</LI><LI>$nSpecies species</LI><LI>$nVarieties distinct varieties</LI><LI>$nOrganic listings are organic</LI></UL>"
             ."<P>The sample to the right is the first ten rows. If any of this doesn't look right, click Cancel and start over.</P>"
             ."<P>If you continue, all the current listings for these companies will be deleted and replaced.</P>"
             ."<P><U><B>Stop now if this isn't what you want.</B></U></P>";

        $s .= "<BR/>";

        if( !$nRows )  $ok = false;

        return( $this->stepperRet( $ok, $s, false ) );
    }

    function Step3_obsolete_Validate( $raParms )
    {
        $s = "";
        $ok = true;

        if( false /* current info is archived */ ) {
            // archive the current info or die
//     select C._key,C.company_name,C.psp,C.icv,C.year from csci_seeds C left join csci_seeds_archive A on (C._key=A.key_orig and C.year=A.year) where A._key is null;
//     insert into csci_seeds_archive (_created,_created_by,_updated,_updated_by,key_orig,company_name,psp,icv,year)
//         select NOW(),1499,NOW(),1499,C._key,C.company_name,C.psp,C.icv,C.year from csci_seeds C left join csci_seeds_archive A on (C._key=A.key_orig and C.year=A.year) where A._key is null;
        }

        // Check for duplicates
        $raFound = array();
        if( ($dbc = $this->kfdb->CursorOpen( "SELECT A.company as company,A.species as species,A.variety as variety,A.k as k,B.k"
                                            ." FROM sl_src_tmp A,sl_src_tmp B WHERE A.company=B.company AND A.species=B.species AND"
                                            ." A.variety=B.variety and A.k<B.k;" ) ) )
        {
            while( $ra = $this->kfdb->CursorFetch( $dbc ) ) {
                $raFound[] = $ra;
            }
        }
        if( count($raFound) ) {
        	$s .= "<P style='color:red'>Found duplicate rows:</P><UL style='color:red'>";
            foreach( $raFound as $v ) {
                $s .= "<LI>{$v['species']} : {$v['variety']} ({$v['company']})</LI>";
            }
            $s .= "</UL>";
//            return( $s );
        }








// validate: for all new rows that match old rows, and the new rows have keys, are the keys the same?
//     select count(*) from


//     count (company match,sp match,cv match)
//     count (company match,sp match,cv match,key match)
// for all new rows that match old rows, but new rows have blank keys, replace the keys?
// for all rows whose keys match but text doesn't, validate company,sp,cv change
//


/*
                    $s .= $this->pcvTableFill();//puts everything from sl_cv_sources into sl_pcv
*/

        done:
$ok = true;
        return( $this->stepperRet( $ok, $s, false ) );
    }


    private function stepperRet( $ok, $s, $bLast = false )
    {
        return( array( 's' => $s,
                       'btnHiddenParms' => array( 'cmd'=>'company_upload' ),
                       'buttons' => (($ok && !$bLast ? "next " : "")." repeat cancel") ) );
    }

}


class SLUploadCVSources
/**********************
    DB layer for uploading company / seedbank data from spreadsheets to sl_cv_sources

    __construct( kUpload == 0 ) prepares the object for a potential Load(). Nothing else will work.
    __construct( kUpload != 0 ) prepares the object to work with the rows in sl_tmp_cv_sources identified by kUpload.

    Load() copies an array of uploaded rows to sl_cv_sources and validates the data. If successful, a kUpload is returned
    and further methods can be used on the table rows identified by that code.

    Validate() indexes and validates the fk_sl_* columns and computes which rows represent changes.
    It can be called multiple times at different stages of a multi-page process: if it has already run it just
    returns the result of the previous run.

    If existing rows are not found in the spreadsheet, use eReplace to decide what to do with them.
        ReplaceVerbatimRows   : don't delete unreferenced rows (copies portions of companies without affecting other portions)
        ReplaceWholeCompanies : delete all rows from companies mentioned in the tmpTable (replaces companies)
        ReplaceWholeCSCI      : delete and replace the whole CSCI (removes companies not mentioned in tmpTable)
 */
{
    // When overwriting sl_cv_sources with uploaded rows, specify what to do with existing rows not mentioned in the new data?
    const ReplaceVerbatimRows = 1;       // only replace the sl_cv_sources rows given in the spreadsheet
    const ReplaceWholeCompanies = 2;     // replace companies mentioned in the spreadsheet (i.e. delete old rows of those companies)
    const ReplaceWholeCSCI = 3;          // replace all companies (i.e. delete old companies not mentioned in the spreadsheet)

    private $oW;
    private $tmpTable = "seeds.sl_tmp_cv_sources";

    public $kUpload = 0;    // unique value placed in sl_tmp_cv_sources.kUpload to group the rows of this upload (0 means Load failed)
    public $eReplace;

    function __construct( SEEDApp_WorkerC $oW, $kUpload, $eReplace )
    {
        $this->oW = $oW;
        $this->kUpload = $kUpload;
        $this->eReplace = $eReplace;
    }

    function Load( $raRows )
    /***********************
        Copy data from $raRows to sl_tmp_cv_sources

            $raRows must contain at least
                'k'        => copy of sl_cv_sources._key
                'company'  => copy of sl_sources.name_en
                'species'  => copy of sl_cv_sources.osp
                'cultivar' => copy of sl_cv_sources.ocv
                'organic'  => copy of sl_cv_sources.bOrganic (allows a variety of boolean ways to say 'yes')
                'notes'    => copy of the notes that people use while editing the spreadsheet

            Optional columns:
                'year'

        Validate data

            Companies must all be known and convertible to fk_sl_sources
            No duplicate (company,species,cultivar) allowed
            Warnings for unknown species and cultivars
     */
    {
        $sOk = $sWarn = $sErr = "";
        $bOk = false;
//$this->oW->kfdb->SetDebug(2);

// There's code here to handle multiple updates simultaneously. That means you don't want to drop and recreate the table all the time.
// Let's keep the multiple-update facility but not use it and just drop/create the table at the start of every update.
$this->oW->kfdb->Execute( "DROP TABLE IF EXISTS {$this->tmpTable}" );
$this->oW->kfdb->Execute( $this->sqlTmpTableCreate );

        /* This number groups this upload's rows in the db table. It doesn't matter what the number is, as long as it's different from others in the kUpload column
         */
        $this->kUpload = $this->uniqueNumber();


        /* Copy the rows to a temporary table, alerting where rows have invalid blank content
         *     (A)  k && company && species            = existing row with possible changes
         *     (B) !k && company && species            = new row
         *
         *     (C)  k && !company && !species          = this means delete row k
         *     (D) !k && !company && !species          = ignore empty row
         *
         *     (E) company xor species                 = not allowed
         */
        $sqlRows = array();
        $nRow = 0;
        foreach( $raRows as $ra ) {
            $nRow++;

            $k = intval($ra['k']);
            $company = trim(addslashes($ra['company']));
            $species = trim(addslashes($ra['species']));
            $cultivar = trim(addslashes($ra['cultivar']));
            $organic = in_array( trim($ra['organic']), array(1,'1','y','Y','yes','YES') ) ? 1 : 0;
            $notes  = trim(addslashes($ra['notes']));
            $year = intval(@$ra['year']) or ($year = date("Y"));

            // (D) skip blank lines (but increment the nRow counter)
            if( !$k && !$company && !$species )  continue;

            // (E) all valid cases require both company+species or neither
            if( empty($company) xor empty($species) ) {
                $sWarn .= "Row ".($nRow+1)." has a blank ".(empty($species) ? "species" : "company").", so it will be skipped.<br/>";    // +1 because of the header row
                continue; //goto done;
            }

            // (A,B,C) copy to sl_tmp_cv_sources for processing
            $sqlRows[] = "($k,'$company','$species','$cultivar',$organic,'$notes',$year,$this->kUpload,now(),0)";
        }
        $nRowsAffected = 0;
        if( count($sqlRows) ) {
            if( !$this->oW->kfdb->Execute( "INSERT INTO {$this->tmpTable} (k,company,osp,ocv,organic,notes,year,kUpload,_created,_status) "
                                          ."VALUES ".implode( ',', $sqlRows ) ) )
            {
                $s1 = "Database error inserting : ".$this->oW->kfdb->GetErrMsg();
                $this->oW->oC->ErrMsg( $s1 );
                $sErr .= $s1;
                goto done;
            }
            $nRowsAffected = $this->oW->kfdb->GetAffectedRows();
        }

        $sOk .= "Uploaded $nRowsAffected rows from the spreadsheet.<br/>";

//Check for duplicates and fail if they exist
//Ignore rows where k!=0, company='' because those are for deletion

        $bOk = true;

        done:
        return( array($bOk,$sOk,$sWarn,$sErr) );
    }


    function Validate()
    /******************
        Index the company, species, and cultivar columns. Compute the operations represented by each row.

        If the table has already been indexed, just return validation codes.

        Row types admitted by Load():
            (A)  k && company && species            = existing row with possible changes
            (B) !k && company && species            = new row
            (C)  k && !company && !species          = this means delete row k
     */
    {
        $sOk = $sWarn = $sErr = "";
        $bOk = false;

        if( !$this->kUpload ) goto done;

        $bIndexed = $this->oW->kfdb->Query1( "SELECT count(*) FROM {$this->tmpTable} WHERE kUpload='{$this->kUpload}' AND op<>' ' LIMIT 1" );

        /* Index the tmp table if there are no ops computed yet
         */
        if( !$bIndexed ) {
            $sOk = "<p>Indexing the table</p>";

            // Index companies
            $this->oW->kfdb->Execute( "UPDATE {$this->tmpTable} T,seeds.sl_sources S SET T.fk_sl_sources=S._key "
                                     ."WHERE S._status='0' AND T.kUpload='{$this->kUpload}' AND T.company<>'' AND T.company=S.name_en" );

            // Index species and cultivars using Rosetta
            SLSourceRosetta_BuildDB::BuildSpeciesIndex( $this->oW->kfdb, $this->tmpTable, "kUpload='$this->kUpload'" );
            SLSourceRosetta_BuildDB::BuildCultivarIndex( $this->oW->kfdb, $this->tmpTable, "kUpload='$this->kUpload'" );


            /* Compute Operations to perform on the rows
             *
             *  N  = new:     tmp.k==0
             *  U  = update1: tmp.k<>0, tmp.fk_sl_sources<>0, some data and year changed
             *  V  = update2: tmp.k<>0, tmp.fk_sl_sources<>0, some data changed but year is the same
             *  Y  = year:    tmp.k<>0, tmp.fk_sl_sources<>0, only year changed
             *  - = same:     tmp.k<>0, tmp.fk_sl_sources<>0, data and year not changed
             *  D = delete1:  tmp.k<>0, tmp.fk_sl_sources==0
             *  X = delete2:  tmp.k is missing in the set of rows that should match sl_cv_sources rows
             *
             * The tests below are very stringent, assuming nothing, so outlying cases wind up "uncomputed" and flagged
             *
             * Archiving
             *     Rows are archived when their year changes or when they are deleted.
             *     (U V Y -) include all combinations of changes to data and year. Changes of year (U Y) trigger an archive.
             *     (V) does not trigger an archive.
             *     That way the archive contains only old rows no longer contained in current sl_cv_sources, and you can
             *     make current-year corrections without having to correct any archived copy too.
             */
            $condUpdateCase = "(T.kUpload='{$this->kUpload}' AND C._key=T.k AND T.fk_sl_sources<>'0')";
            $condDataSame = "(C.fk_sl_sources=T.fk_sl_sources AND C.osp=T.osp AND C.ocv=T.ocv AND C.bOrganic=T.organic AND C.notes=T.notes)";

            // N (tmp.k==0)
            $this->oW->kfdb->Execute( "UPDATE {$this->tmpTable} SET op='N' WHERE kUpload='{$this->kUpload}' AND k='0'" );

            // U (data and year changed)
            $this->oW->kfdb->Execute(
                "UPDATE {$this->tmpTable} T,seeds.sl_cv_sources C SET T.op='U' "
               ."WHERE $condUpdateCase AND "
                     ."NOT $condDataSame AND C.year<>T.year" );

            // V (data changed but year the same)
            $this->oW->kfdb->Execute(
                "UPDATE {$this->tmpTable} T,seeds.sl_cv_sources C SET T.op='V' "
               ."WHERE $condUpdateCase AND "
                     ."NOT $condDataSame AND C.year=T.year" );

            // Y (only the year changed)
            $this->oW->kfdb->Execute(
                "UPDATE {$this->tmpTable} T,seeds.sl_cv_sources C SET T.op='Y' "
               ."WHERE $condUpdateCase AND "
                     ."$condDataSame AND C.year<>T.year" );

            // - (perfect match on all columns including year)
            $this->oW->kfdb->Execute(
                "UPDATE {$this->tmpTable} T,seeds.sl_cv_sources C SET T.op='-' "
               ."WHERE $condUpdateCase AND "
                     ."$condDataSame AND C.year=T.year" );

            // D (company and osp are blank)
            $this->oW->kfdb->Execute( "UPDATE {$this->tmpTable} SET op='D' WHERE kUpload='{$this->kUpload}' AND k<>'0' AND company='' AND osp=''" );

            // X (rows in sl_cv_sources don't exist in tmp) - implement this by adding them to tmp
            if( $this->eReplace != self::ReplaceVerbatimRows ) {
                $this->oW->kfdb->Execute(
                    "INSERT INTO {$this->tmpTable} (k,kUpload,op) "
                    ."SELECT SRCCV._key,{$this->kUpload},'X' FROM seeds.sl_cv_sources SRCCV LEFT JOIN {$this->tmpTable} T "
                        ."ON SRCCV._key=T.k WHERE T.k IS NULL AND "
                        .($this->eReplace == self::ReplaceWholeCSCI
                            // if replacing all companies then delete all rows that are missing in tmpTable (except seed banks)
                            ? "SRCCV.fk_sl_sources >= '3'"
                            // if replacing specific companies then delete missing rows from those companies only
                            : ("SRCCV.fk_sl_sources IN (SELECT distinct(fk_sl_sources) FROM {$this->tmpTable} "
                                                      ."WHERE kUpload='$kUpload' AND fk_sl_sources<>'0')")) );
            }
        }


        /* Report on upload status
         */
        $raReport = $this->ReportPendingUpload( $this->kUpload, $this->eReplace );
        $sOk .= "<p>Processing {$raReport['nRows']} rows for {$raReport['nDistinctCompanies']} companies.</p>";

        $sOk .= "<style>"
               .".companyUploadResultsTable    { border-collapse-collapse; text-align:center }"
               .".companyUploadResultsTable th { text-align:center }"
               .".companyUploadResultsTable td { border:1px solid #aaa; padding:3px; text-align:center }"

               ."</style>";
        $sOk .= "<table class='companyUploadResultsTable'><tr><th>Existing</th><th width='50%'>Upload</th></tr>"
               ."<tr><td colspan='2'>{$raReport['nRowsSame']} rows are identical including the year</td></tr>"
               ."<tr><td colspan='2'>{$raReport['nRowsY']} rows are exactly the same except for the year (will be archived)</td></tr>"
               ."<tr><td colspan='2'>{$raReport['nRowsU']} rows have changed from previous year (will be archived)</td></tr>"
               ."<tr><td colspan='2'>{$raReport['nRowsV']} rows have corrections for current-year (won't be archived)</td></tr>"
               ."<tr><td>&nbsp;</td><td>{$raReport['nRowsN']} rows are new</td></tr>"
               ."<tr><td>&nbsp;</td><td>{$raReport['nRowsD1']} rows are marked in the spreadsheet for deletion</td></tr>"
               ."<tr><td>{$raReport['nRowsD2']} rows will be deleted because they are missing in the upload</td><td>&nbsp;</td></tr>"
               ."<tr><td>&nbsp;</td><td><span style='color:red'>{$raReport['nRowsUncomputed']} rows are not computed</span></td></tr>"
               ."</table><br/>";


        /* Require all rows to have a valid company (or blank as per action C-delete).
         */
        $raFail = $this->oW->kfdb->QueryRowsRA( "SELECT company FROM {$this->tmpTable} WHERE kUpload='{$this->kUpload}' AND "
                                                      ."company<>'' AND fk_sl_sources='0' GROUP BY 1" );
        if( count($raFail) ) {
            $sErr .= "These companies are not known. Please add to Sources list and try again."
                    ."<ul>".SEEDCore_ArrayExpandRows( $raFail, "<li>[[company]]</li>")."</ul>";
            goto done;
        }

        /* Warn about species that are not indexed, unless company is blank (action C-delete).
         */
        if( count($raReport['raUnknownSpecies']) ) {
            $sWarn .= "<p>These species are not known. Please add to Species list or Species Synonyms and try again.</p>"
                     ."<ul style='background-color:#f8f8f8;max-height:200px;overflow-y:scroll'>"
                     .SEEDCore_ArrayExpandRows( $raReport['raUnknownSpecies'], "<li>[[osp]]</li>")."</ul><br/>";
            //goto done;
        }

        /* Warn about cultivars that are not indexed, unless company is blank (action C-delete).
         */
        if( count($raReport['raUnknownCultivars']) ) {
            $sWarn .= "<p>These cultivars are not known. They will be matched by name as much as possible, but you should add them to the Cultivars list.</p>"
                     ."<ul style='background-color:#f8f8f8;max-height:200px;overflow-y:scroll'>"
                     .SEEDCore_ArrayExpandRows( $raReport['raUnknownCultivars'], "<li>[[osp]] : [[ocv]]</li>")."</ul>";
        }

        $bOk = true;

        done:
        return( array($bOk,$sOk,$sWarn,$sErr) );
    }

    function ReportPendingUpload( $kUpload, $eReplace )
    /**************************************************
        Report on the status of the given pending upload.
        This can only be used after the tmp.op column is computed
     */
    {
        $raReport = array(
            'nRows'              => $this->oW->kfdb->Query1( "SELECT count(*) FROM {$this->tmpTable} WHERE kUpload='$kUpload'" ),
            'nRowsUncomputed'    => $this->oW->kfdb->Query1( "SELECT count(*) FROM {$this->tmpTable} WHERE kUpload='$kUpload' AND op=''" ),
            'nRowsSame'          => $this->oW->kfdb->Query1( "SELECT count(*) FROM {$this->tmpTable} WHERE kUpload='$kUpload' AND op='-'" ),
            'nRowsN'             => $this->oW->kfdb->Query1( "SELECT count(*) FROM {$this->tmpTable} WHERE kUpload='$kUpload' AND op='N'" ),
            'nRowsU'             => $this->oW->kfdb->Query1( "SELECT count(*) FROM {$this->tmpTable} WHERE kUpload='$kUpload' AND op='U'" ),
            'nRowsV'             => $this->oW->kfdb->Query1( "SELECT count(*) FROM {$this->tmpTable} WHERE kUpload='$kUpload' AND op='V'" ),
            'nRowsY'             => $this->oW->kfdb->Query1( "SELECT count(*) FROM {$this->tmpTable} WHERE kUpload='$kUpload' AND op='Y'" ),
            'nRowsD1'            => $this->oW->kfdb->Query1( "SELECT count(*) FROM {$this->tmpTable} WHERE kUpload='$kUpload' AND op='D'" ),
            'nRowsD2'            => $this->oW->kfdb->Query1( "SELECT count(*) FROM {$this->tmpTable} WHERE kUpload='$kUpload' AND op='X'" ),
            'nDistinctCompanies' => $this->oW->kfdb->Query1( "SELECT count(distinct fk_sl_sources) FROM {$this->tmpTable} WHERE kUpload='$kUpload' AND fk_sl_sources<>'0'" ),

            // rows with unmatched species, ignoring those where species is blank or company is blank (those are rows to be deleted)
            'raUnknownSpecies'   => $this->oW->kfdb->QueryRowsRA( "SELECT osp FROM {$this->tmpTable} WHERE kUpload='$kUpload' "
                                                                 ."AND fk_sl_species='0' AND osp<>'' AND company<>'' GROUP BY 1" ),
            // rows with unmatched cultivars, not counting those where species was unmatched (reported above and prerequisite)
            'raUnknownCultivars' => $this->oW->kfdb->QueryRowsRA( "SELECT osp,ocv FROM {$this->tmpTable} WHERE kUpload='$kUpload' "
                                                                 ."AND fk_sl_pcv='0' AND fk_sl_species<>'0' GROUP BY 1,2" ),
        );

        return( $raReport );
    }

    function Archive()
    /*****************
        Archive is an accumulation of sl_cv_sources rows that have been deleted or updated from year-to year.
        Updating a current-year row does not trigger that row to be copied.

        For any rows in sl_cv_sources that are going to be A) deleted or B) updated and the year is being increased, make sure they
        are copied to the archive table.
     */
    {
        // This assumes that rows are only copied to archive when the year column is changed or a row is deleted.
        // If you repeat this operation during the same upload you will get duplicate rows.
        //
        // There could be a test here to see if the copying has already happened, but it's easier to just make a garbage collector
        // that removes those duplicates if they ever happen.

        $ok = false;
        $sOk = $sWarn = $sErr = "";

        /* Archive U = change in data and year
         *         Y = change in year (data the same, so this records that the entry also existed in the previous year)
         *         D = marked for deletion in the spreadsheet
         *         X = to be deleted because it's missing in the spreadsheet
         */
        $raReport = $this->ReportPendingUpload( $this->kUpload, $this->eReplace );

        $sOk = "<p>Archiving</p>"
              ."<ul>"
              ."<li>{$raReport['nRowsU']} rows where the data and year are being updated</li>"
              ."<li>{$raReport['nRowsY']} rows where the year is being updated (data not changed)</li>"
              ."<li>{$raReport['nRowsD1']} rows that are marked for deletion in the spreadsheet</li>"
              ."<li>{$raReport['nRowsD2']} rows that are missing from the spreadsheet and will be deleted</li>"
              ."</ul>";

        $uid = $this->oW->sess->GetUID();

        $ok = $this->oW->kfdb->Execute(
                "INSERT INTO seeds.sl_cv_sources_archive "
                    ."(sl_cv_sources_key,fk_sl_sources,fk_sl_pcv,fk_sl_species,osp,ocv,bOrganic,year,notes,op,"
                    ." _created,_updated,_created_by,_updated_by) "
               ."SELECT C._key,C.fk_sl_sources,C.fk_sl_pcv,C.fk_sl_species,C.osp,C.ocv,C.bOrganic,C.year,C.notes,T.op,"
                      ."now(),now(),'$uid','$uid' "
               ."FROM seeds.sl_cv_sources C, {$this->tmpTable} T "
               ."WHERE C._key=T.k AND kUpload='{$this->kUpload}' AND T.op IN ('U','Y','D','X')" );
        if( !$ok ) {
            $sErr = $this->oW->kfdb->GetErrMsg();
        }

        done:
        return( array($ok,$sOk,$sWarn,$sErr) );
    }

    function Commit()
    /****************
        Update sl_cv_sources with the rows in the given upload

        N:     insert new rows
        U,V,Y: overwrite existing rows
        D,X:   delete rows
     */
    {
        $ok = false;
        $sOk = $sWarn = $sErr = "";

        $uid = $this->oW->sess->GetUID();

        // N
        $ok = $this->oW->kfdb->Execute(
                "INSERT INTO seeds.sl_cv_sources "
                   ."(fk_sl_sources,fk_sl_pcv,fk_sl_species,company_name,osp,ocv,bOrganic,year,notes,_created,_updated,_created_by,_updated_by) "
               ."SELECT fk_sl_sources,fk_sl_pcv,fk_sl_species,company,osp,ocv,organic,year,notes,now(),now(),'$uid','$uid' "
               ."FROM {$this->tmpTable} WHERE op='N'" );
        if( !$ok ) {
            $sErr = $this->oW->kfdb->GetErrMsg();
            goto done;
        }
        $sOk .= "<div class='alert alert-success'>Committed ".$this->oW->kfdb->GetAffectedRows()." new rows</div>";

        // U,V,Y
        $ok = $this->oW->kfdb->Execute(
                "UPDATE seeds.sl_cv_sources C,{$this->tmpTable} T "
               ."SET C.fk_sl_sources=T.fk_sl_sources,C.fk_sl_pcv=T.fk_sl_pcv,C.fk_sl_species=T.fk_sl_species,"
                   ."C.company_name=T.company,C.osp=T.osp,C.ocv=T.ocv,C.bOrganic=T.organic,C.year=T.year,C.notes=T.notes,_updated=now(),_updated_by='$uid' "
               ."WHERE C._key=T.k AND T.op in ('U','V','Y')" );
        if( !$ok ) {
            $sErr = $this->oW->kfdb->GetErrMsg();
            goto done;
        }
        $sOk .= "<div class='alert alert-success'>Committed ".$this->oW->kfdb->GetAffectedRows()." changed rows</div>";

        // D,X
        $ok = $this->oW->kfdb->Execute(
                "DELETE C FROM seeds.sl_cv_sources C,{$this->tmpTable} T "
               ."WHERE C._key=T.k AND T.op in ('D','X')" );
        if( !$ok ) {
            $sErr = $this->oW->kfdb->GetErrMsg();
            goto done;
        }
        $sOk .= "<div class='alert alert-success'>Deleted ".$this->oW->kfdb->GetAffectedRows()." rows identified for removal</div>";

        done:
        return( array($ok,$sOk,$sWarn,$sErr) );
    }

    private function uniqueNumber()
    /******************************
        Make a unique number by incrementing the _key of a table that we know exists during the lifetime of an upload.
        This could be any table with an auto-inc.
     */
    {
        if( ($k = $this->oW->kfdb->InsertAutoInc( "INSERT INTO seeds.sl_cv_sources (_key) VALUES (NULL)" )) ) {
            $this->oW->kfdb->Execute( "DELETE FROM seeds.sl_cv_sources WHERE _key='$k'" );
        }
        return( $k );
    }

    private $sqlTmpTableCreate = "
CREATE TABLE seeds.sl_tmp_cv_sources (
    -- These columns are required in the spreadsheet
    -- osp and ocv are named this way to enable compatible code with SLSourceRosetta
    k             integer not null default 0,            -- sl_cv_sources._key, preserved here for re-integration
    company       varchar(200) not null default '',      -- must match sl_sources.name_en
    osp           varchar(200) not null default '',      -- copy of sl_cv_sources.osp
    ocv           varchar(200) not null default '',      -- copy of sl_cv_sources.ocv
    organic       tinyint not null default 0,            -- copy of sl_cv_sources.bOrganic
    year          integer not null default 0,
    notes         text,

    -- These columns are generated when the spreadsheet is uploaded
    kUpload       integer not null,                      -- each upload has a unique number for grouping rows of that upload
    _created      datetime,                              -- time when this row was uploaded - for garbage collection of orphaned uploads
    _status       integer not null default 0,            -- mainly so we can apply queries written for sl_cv_sources

    -- Computed after loading
    fk_sl_sources integer default 0,                     -- validates integrity of (company)
    fk_sl_species integer default 0,                     -- attempts to match (species) with a species identifier, but allows 0 so Rosetta can work on it
    fk_sl_pcv     integer default 0,                     -- attempts to match (fk_sl_species,cultivar), but allows 0 so Rosetta can work on it
    op            CHAR not null default ' ',             -- ' ' = not computed yet, 'N' = new, 'U' = update, 'D' = delete1, 'X' = delete2, 'Y' = year updated, '-' = no change

    -- These are obsolete, probably
    -- sp_old        varchar(200) not null default '',
    -- var_old       varchar(200) not null default '',

    -- Indexes
    index (k),
    index (osp(20)),
    index (ocv(20)),
    -- index (sp_old(20)),
    -- index (var_old(20)),
    index (fk_sl_sources),
    index (fk_sl_species),
    index (fk_sl_pcv),
    index (kUpload)
) CHARSET latin1;
";

}

?>
