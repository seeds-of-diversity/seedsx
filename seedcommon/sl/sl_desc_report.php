<?php

/* Seed Library: sl_desc_report
 *
 * Copyright 2009-2017 Seeds of Diversity Canada
 *
 * Report descriptor data
 */

include_once( "sl_desc_db.php" );
include_once( "sl_desc_defs.php" );

include_once( "sl_sources_charts.php" );


class SLDescReportUI
/*******************
    Draw an interactive UI to report descriptor data
 */
{
    public $kfdb;
    public $lang;
    public $oSLDescDB;
    public $oSLDescDefs;

    public $kSpecCurr = "";
    public $kObsCurr = "";
    public $kVarCurr = "";
    public $kProvCurr = "All";
    public $kValsCurr = "";
    public $kRecCurr = "";

    private $sLinkToMe;
    private $sSubmitToMe;

    function __construct( KeyFrameDB $kfdb, $lang, $raParms = array() )
    {
        $this->kfdb = $kfdb;
        $this->lang = $lang;
        $this->oSLDescDB = new SL_DescDB( $this->kfdb, 0 );  // uid = 0; anonymous read
        $this->oSLDescDefs = new SL_DescDefs( $this->oSLDescDB );

        if( !($this->kSpecCurr = SEEDSafeGPC_GetStrPlain( 'kSpec' )) )  $this->kSpecCurr = 'apple';

        $this->kObsCurr  = SEEDSafeGPC_GetStrPlain( 'kObs' );
        $this->kVarCurr  = SEEDSafeGPC_GetStrPlain( 'kVar' );
        $this->kProvCurr = SEEDSafeGPC_Smart( 'kProv', array( "All" ) );
        $this->kValsCurr = SEEDSafeGPC_GetStrPlain( 'kVals');
        $this->kRecCurr  = SEEDSafeGPC_GetStrPlain( 'kRec');

        $this->raVI    = $this->oSLDescDB->GetListVarInst( /* here's where you filter the varinsts */ );

        // raParms['linkToMe']  = the_current_page?a=b&
        $this->sLinkToMe = SEEDStd_ArraySmartVal( $raParms, 'linkToMe', array( $_SERVER['PHP_SELF']."?" ), false );
        $this->sSubmitToMe = @$raParms['submitToMe'];
    }

    function Style()
    {
        $s = "<STYLE>"
            .".sldescui    { font-family:arial,helvetica,sans serif;width:90%;margin:0px auto;max-width:800px;text-align:center; }"
            .".sldescui h2 {}"

            .".sldescui td { font-size: 10pt; font-family:arial,helvetica,sans serif; padding:6px; }"
            .".sldescui table { border-collapse:collapse }"
            .".sldescrpt p  { font-size:10pt; margin-left:40px }"

            .".sldescrpt h1 { margin:5px -5px;padding:5px; font-size: 12pt; color:white; background-color:#888; }"
            .".sldescrpt_line0 { background-color:#fff; }"
            .".sldescrpt_line1 { background-color:#eee; }"
            .".sldescrpt_line_curr    a { color: blue; }"
            .".sldescrpt_line_notcurr a { color: gray; }"

            .".sldescrpt_chartcontainer { width:450px;border:1px solid grey; margin:10px; padding:10px; }"
            .".slsrcui_ctrl  { font-size:10pt; margin-left:40px; padding:10px;border:1px solid #888;background-color:#eee; }"
            .".slsrcui_link  {  font-weight:bold;}"
            .".slsrcui_cvlist { font-size:10pt; margin-left:40px }"

            ."</STYLE>";

        return( $s );
    }


    function DrawDrillDown()
    {
        if( $this->kValsCurr ) {
            $params = unserialize($this->kValsCurr);
            $sSpecies = ucwords($params['kSpec']);
            $sVariety = $params['kVar'];

            $sTitle = "Variety Characteristics of $sVariety $sSpecies";
        } else if( $this->kSpecCurr ) {
            $sSpecies = ucwords($this->kSpecCurr);
            $sVariety = "";
            $sTitle = "";//Show data about: $sSpecies";
        } else {
            $sSpecies = $sVariety = "";
            $sTitle = "Variety Characteristics";
        }

        $s = "<DIV class='sldescui'><h3>$sTitle</h3>";

        $s .= "<form action='".Site_path_self()."' method='post'>"
             .($this->lang=='EN'?"Show data about: ":"Montrer les donn&eacute;es sur: ")
             .$this->listOfSpeciesSelect()
             .$this->sSubmitToMe
             ."</form>";

//        $sSpeciesList = "<DIV style='float:right;border:1px solid grey;font-size:9pt;margin:30px;padding:20px'>"
//                       .$this->listOfSpecies()
//                       ."</DIV>";

//        if( !$this->kSpecCurr && !$this->kObsCurr && !$this->kValsCurr && !$this->kRecCurr ){
//        $s .= $sSpeciesList;
//        }



        if($this->kValsCurr and $this->kRecCurr == NULL){
            $s .= $this->valuesTable($this->kValsCurr);
//            $s .= "</BR><DIV><A HREF='{$_SERVER['PHP_SELF']}?'>Clear</A></DIV>";
        }

        if( $this->kSpecCurr && !$this->kRecCurr ) {
            $s .= //$this->selectOfProvinces()
                 "<table border='0' width='100%'><tr>"
                 ."<td valign='top'>"
                 .$this->listOfVarieties( $this->kSpecCurr )
                 ."</td>"
                 ."<td width='25'>&nbsp;</td>"
                 ."<td valign='top'>"
                 .$this->listOfCharacteristics( $this->kSpecCurr )
                 ."</td>"
                 ."</tr></table>"
                 //."</BR><DIV><A HREF='{$this->sLinkToMe}'>Clear</A></DIV>"
                 ;
        }

        if( $this->kRecCurr ) {
            $s .= $this->DrawVIRecord( $this->kRecCurr );
            //$s .= "</BR><DIV><A HREF='{$_SERVER['HTTP_REFERER']}'>Back</A></DIV>";
            //$s .= "</BR><DIV><A HREF='{$_SERVER['PHP_SELF']}?'>Clear</A></DIV>";
        }

        $s .= "</DIV>";

        return( $s );
    }

    function linkToMe( $ra )
    {
        return( $this->sLinkToMe.SEEDStd_ParmsRA2URL( $ra ) );
    }

    function listOfSpecies()
    {
        $s  = ""; //"<h1>Choose a Species</h1>";
        foreach( $this->oSLDescDefs->raSpecies as $v ) {
            $s .= "<DIV><A HREF='".$this->linkToMe( array('kSpec'=>$v) )."'>".ucwords($v)."</A></DIV>";
        }

        return($s);
    }

    function listofSpeciesSelect()
    {
        $raOptions = array();
        foreach( $this->oSLDescDefs->raSpecies as $v ) {
            $raOptions[$v] = ucwords($v);
        }
        return( SEEDForm_Select( 'kSpec', $raOptions, $this->kSpecCurr, array( 'selectAttrs'=>"onchange='submit()'") ) );
    }

    function listOfVarieties( $psp )
    {
    	$s = "";

        $defsRA = $this->oSLDescDefs->GetDefsRAFromOSP( $psp );

        $lObs = "";
        if( $this->kObsCurr ) {
            $lObs = @$defsRA[$this->kObsCurr]['l_EN'];
        }
        $s .= "<h3>".($lObs ? $lObs : ($this->lang=='EN'?"Varieties":"Vari&eacute;t&eacute;s"))."</h3>";


        // Get raCcv - a unique list of varieties that have desc_obs
// this would be more efficient by enumerating varinst but it would include varinst that don't have descobs (do we care?)
        $raDO = $this->oSLDescDB->GetListDescObs( array("osp"=>$psp) );
        $raCcv = array();
        foreach( $raDO as $obs ) {
            $raCcv[$obs['VarInst_ccv']] = @$raCcv[$obs['VarInst_ccv']] + 1;
        }
        ksort( $raCcv );

        // If the user clicked on a characteristic, show a table of the varieties and a summary of the observations
        if( $this->kObsCurr ) {
                $code =  $this->kObsCurr;

                foreach( $raCcv as $o => $nCcv ) {
                    $raDO = $this->oSLDescDB->GetListDescObs( array("osp"=>$psp, "oname"=>$o, "desc_k" => $code ) );
                    foreach($raDO as $ra){
                        $namesRA[] = $ra['VarInst_oname'];
                    }
                }
                $uniqueNamesRA = @array_unique($namesRA);
                $s .= "<table border='1'>";
                $s .= "<tr><td>Variety</td><td>Average</td><td>Most Common</td><td>Number of Entries</td><td>View Details</td></tr>";
                foreach($uniqueNamesRA as $na){
                    $raDO = $this->oSLDescDB->GetListDescObs( array("osp"=>$psp, "oname"=>$na, "desc_k" => $this->kObsCurr ) );

                    $vals ="";
                    $types ="";
                    $imgs = "";
                    $raCount =0;
                    foreach($raDO as $ra){
                        $r = $defsRA[$ra['k']];
                        if(@$r['m']){
                            $types[] = $r['m'][$ra['v']];
                            if(@$r['img']){
                                if(@$r['img'][$ra['v']]){
                                    $imgs[] = array($r['m'][$ra['v']] => $r['img'][$ra['v']]);
                                }
                            }
                        }else{
                            $vals[] = $ra['v'];
                        }
                        $raCount++;
                    }
                    $count = $raCount;
                    $sum = @array_sum($vals);
                    @$avg = $sum/$count;
                    if($vals){
                        $c = array_count_values($vals);
                        arsort($c);
                        $mc = key($c);
                    }else{
                        $c = array_count_values($types);
                        arsort($c);
                        $mc = key($c);
                    }
                    $params = array('kObs' => $this->kObsCurr,'kVar'=> $na ,'kSpec' => $this->kSpecCurr,'kProv' => $this->kProvCurr, 'Vals' => $vals, 'Types' => $types , 'Imgs' => $imgs, 'Back' => 'V');// + $vals;
                    $s .= "<tr><td>"
                         ."<a href='".$this->linkToMe( array( 'kSpec'=>$this->kSpecCurr,
                                                              'kVar'=>$na,
                                                              'kProv'=>$this->kProvCurr) )."'>"
                         .$na."</a></td>";
                        if($vals){
                            $s.= "<td>".number_format($avg,2)."</A></td>";
                        }else{
                            $s.= "<td>N/A</A></td>";
                        }
                        if(is_numeric($mc)){
                            $s .= "<td>".number_format($mc,2)."</td>";
                        }else{
                            $s .= "<td>".ucwords($mc)."</td>";
                        }
                        $s .= "<td>$count</td>"
                             ."<td>"
                             ."<a href='".$this->linkToMe( array( 'kSpec'=>$this->kSpecCurr,
                                                                  'kVals'=>serialize($params) ) )."'>"

                             ."View</a>"
                             ."</td>"
                             ."</tr>";
                }
                $s .= "</table>";

                $s .= "<P><a href='".$this->linkToMe( array('kSpec'=>$this->kSpecCurr) )."'>Show Varieties</a></P>";
            } else {
            	$n = 0;
                foreach( $raCcv as $sCcv => $nCcv ) {
                	if( $this->kVarCurr ) {
                        $sClassCurr = ($sCcv == $this->kVarCurr ? "sldescrpt_line_curr" : "sldescrpt_line_notcurr");
                	} else {
                	    $sClassCurr = "";
                	}
                	$s .= "<DIV class='$sClassCurr sldescrpt_line".($n % 2 ? "0" : "1")."'><A HREF='".$this->linkToMe( array('kSpec'=>$this->kSpecCurr, 'kVar'=>$sCcv, 'kProv'=>$this->kProvCurr) )."'>$sCcv</A></DIV>";
                    ++$n;
                }
            }

        return($s);
    }




	function DrawVIRecord( $kVI, $bBasic = true )
	/********************************************
	    Show the record for a variety/site/year
	 */
	{
        $raVI = $this->oSLDescDB->GetVarInst( $kVI );
        $raDO = $this->oSLDescDB->GetListDescObs( array( "kVarinst" => $kVI ) );
        $defsRA = $this->oSLDescDefs->GetDefsRAFromOSP( $raVI['csp'] );
//var_dump($raVI);
//var_dump($raDO);

        $s = "<table class='sldesc_VIRecord_table' border='0' cellspacing='5' cellpadding='5'>";
        if( $bBasic ) {
            $s .= "<tr><td width='250'><b>Species:</b></td><td width='200'>".ucwords($raVI['csp'])."</td></tr>"
                 ."<tr><td><b>Variety:</b></td><td> ".ucwords($raVI['ccv'])."</td></tr>"
                 ."<tr><td><b>Year:</b></td><td> ".$raVI['year']."</td></tr>"
                 ."<tr><td><b>Location:</b></td><td> ".$raVI['Site_province']."</td></tr>";
        }
        foreach( $raDO as $obs ) {
            if( !($def = @$defsRA[ $obs['k'] ]) ) continue;

            $v = @$obs['v'];
            $l = @$def['l_EN'];

            if( ($vl = @$def['m'][$v]) ) {  // the multi-choice text value corresponding to the numerical value
                $vl = ucwords( $vl );
                if( ($vimg = @$def['img'][$v]) ) {
                    $s .= "<tr><td><b>$l:</b></td><td>$vl</td><td><img src='".W_ROOT."seedcommon/sl/descimg/$vimg' height='75'/></td></tr>";
                } else {
            		$s .= "<tr><td><b>$l:</b></td><td>$vl</td></tr>";
                }
           	} else {
          	    $s .= "<tr><td><b>$l:</b></td><td>".$obs['v']."</td></tr>";
            }
        }
        $s .= "</table>";

        return( $s );
	}


    function getCurrDefsRA(){
        $ra = array();

        if( $this->kSpecCurr ) {
            $ra = $this->oSLDescDefs->GetDefsRAFromOSP( $this->kSpecCurr );
        }
        return( $ra );
    }

    function listOfProvinces(){
        $s = "";
        $osp = $this->kSpecCurr;
        foreach( $this->raVI as $ra ) {
            if($ra['osp']==$osp){
                $provRA[] = $ra['Site_province'];
            }
        }
        $prov_uniqueRA = @array_unique($provRA);
        $s .= "<div>"
             ."<a href='".$this->linkToMe( array( 'kSpec'=>$this->kSpecCurr,
                                                  'kProv'=>"All" ) )."'>"
             ."All</a></div>";
        foreach($prov_uniqueRA as $place){
            $s .= "<div>"
                 ."<a href='".$this->linkToMe( array( 'kSpec'=>$this->kSpecCurr,
                                                      'kProv'=>$place ) )."'>"
                 .$place."</a></div>";
        }

        return($s);
    }

    function selectOfProvinces(){
        $s = "<p>Select Province:";
        $x = "All";
        $osp = $this->kSpecCurr;
        $provRA = array();
        foreach( $this->raVI as $ra ) {
            if($ra['osp']==$osp){
                $provRA[] = $ra['Site_province'];
            }
        }
        $prov_uniqueRA = @array_unique($provRA);
        $s .= "<script type='text/javascript'>";
        $s .= "function test(val){
           var url = \"{$_SERVER['PHP_SELF']}?kSpec=".$this->kSpecCurr."&kProv=\" + val;
           window.location = url;
        }";

        $s .= "</script>";

        $s .= "<select id='province' name='province' onchange='test(value);'>";
        $s .= "<option  ". ($this->kProvCurr == 'All' ? 'selected' : ''). " value='All'>All</option>";
        foreach($prov_uniqueRA as $place){
            if($place!=""){
                $s .= "<option ". ($this->kProvCurr == $place ? 'selected' : '') . " value=$place>$place</option>";
            }
        }
        $s .= "</select><br></p>";

        return($s);
    }

    function listOfCharacteristics(){
        $defsRA = $this->getCurrDefsRA();
        $codesRA = array_keys($defsRA);
        $codeCount = 0;

        $s = "";

        $s .= "<h3>".($this->lang=='EN'?"Characteristics":"Caract&eacute;ristiques")
             .($this->kVarCurr ? ($this->lang=='EN'?" of {$this->kVarCurr}":" de {$this->kVarCurr}") : "")."</h3>";


        $cv= $this->kVarCurr;
        $site = $this->kProvCurr;
        $osp = $this->kSpecCurr;
        if($this->kVarCurr and $this->kSpecCurr and $this->kProvCurr){
            $codeCount = 0;
            $s .= "<table border='1' style='margin:0px auto;'>";
            $s .= "<tr>"
                 .($this->lang=='EN' ? "<td>Characteristic</td><td>Average</td><td>Most Common</td><td>Number of Entries</td><td>View Details</td>"
                                     : "<td>Caract&eacute;ristic</td><td>Moyenne</td><td>Le plus fr&eacute;quent</td><td>Nombre d'entr&eacute;es</td><td>Afficher les d&eacute;tails</td>")
                 ."</tr>";
            foreach($defsRA as $kDef => $def){
                $raDO = $this->oSLDescDB->GetListDescObs( array("osp"=>$osp, "oname"=>$cv, "desc_k" => $kDef /*$codesRA[$codeCount]*/ ) );

                if($raDO){
                    $vals ='';
                    $types ='';
                    $imgs = "";
                    $raCount =0;
                    foreach($raDO as $ra){
                        $r = $defsRA[$ra['k']];
                        if(@$r['m']){
                            $types[] = $r['m'][$ra['v']];
                            if(@$r['img']){
                                if(@$r['img'][$ra['v']]){
                                    $imgs[] = array($r['m'][$ra['v']] => $r['img'][$ra['v']]);
                                }
                            }
                        }else{
                            $vals[] = $ra['v'];
                        }
                        $raCount++;
                    }

                    $count = $raCount;
                    $sum = @array_sum($vals);
                    $avg = $sum/$count;
                    if($vals){
                        $c = array_count_values($vals);
                        arsort($c);
                        $mc = key($c);
                    }else{
                        $c = array_count_values($types);
                        arsort($c);
                        $mc = key($c);
                    }
                    $params = array('kObs' => $codesRA[$codeCount],'kVar'=> $this->kVarCurr ,'kSpec' => $this->kSpecCurr,'kProv' => $this->kProvCurr, 'Vals' => $vals, 'Types' => $types, 'Imgs' => $imgs, 'Back' => 'C');// + $vals;

                    $s .= "<tr>"
                         ."<td>"
                         ."<a href='".$this->linkToMe( array( 'kSpec'=>$this->kSpecCurr,
                                                              'kObs'=>$codesRA[$codeCount],
                                                              'kProv'=>$this->kProvCurr) )."'>"
                         .$def['l_EN']."</A></td>";
                        if($vals){
                            $s.= "<td>".number_format($avg,2)."</A></td>";
                        }else{
                            $s.= "<td>N/A</A></td>";
                        }
                        if(is_numeric($mc)){
                            $s .= "<td>".number_format($mc,2)."</td>";
                        }else{
                            $s .= "<td>".ucwords($mc)."</td>";
                        }
                        $s .= "<td>$count</td>"
                             ."<td>"
                             ."<a href='".$this->linkToMe( array( 'kSpec'=>$this->kSpecCurr,
                                                                  'kVals'=>serialize($params) ) )."'>"

                             .($this->lang=='EN'?"View":"Afficher")."</A></td>"
                             ."</tr>";
                }

                $codeCount ++;
             }
             $s .= "</table>";
        }
        elseif(false && $this->kObsCurr){
            $l = $defsRA[$this->kObsCurr];
            $s .= "<table border='1'>";
            $s .= "<tr><td>Characteristics</td></tr>";
            $s .= "<tr><td>"
                 ."<a href='".$this->linkToMe( array( 'kSpec'=>$this->kSpecCurr,
                                                      'kObs'=>$this->kObsCurr,
                                                      'kProv'=>$this->kProvCurr) )."'>"
                 .$l['l_EN']."</a></td></tr>";
            $s .= "</table>";
        }
        else{
            $codeCount = 0;
            $raD = array();

            foreach($defsRA as $d){

                $raDO = $this->oSLDescDB->GetListDescObs( array("osp"=>$osp, "oname"=>$cv, "desc_k" => $codesRA[$codeCount] ) );
                $newCodesRA = array();
                foreach($raDO as $ra){
                    if($ra['Site_province']==$site or $site=='All'){
                        $newCodesRA[] = $codesRA[$codeCount];
                    }
                }
                $raD = @array_unique($newCodesRA);
                $codeCount ++;
            }

            	$n = 0;
                foreach( $raD as $sD ) {
                	if( $this->kObsCurr ) {
                        $sClassCurr = ($sD == $this->kObsCurr ? "sldescrpt_line_curr" : "sldescrpt_line_notcurr");
                	} else {
                	    $sClassCurr = "";
                	}
                	$s .= "<DIV class='$sClassCurr sldescrpt_line".($n % 2 ? "0" : "1")."'><A HREF='".$this->linkToMe( array('kSpec'=>$this->kSpecCurr, 'kObs'=>$sD, 'kProv'=>$this->kProvCurr) )."'>".(@$defsRA[$sD]['l_EN'])."</A></DIV>";
                    ++$n;
                }



/*

                $s .= "<table border='1'>";
                $s .= "<tr><td>Characteristics</td></tr>";
                foreach($uniqueCodesRA as $cRA){
                    $x = $defsRA[$cRA];
                    $s .= "<tr><td><A HREF=\"{$_SERVER['PHP_SELF']}?kObs=".$cRA."&kSpec=".$this->kSpecCurr."&kProv=".$this->kProvCurr."\">".$x['l_EN']."</A></td></tr>";
                }
                $s .= "</table>";
*/
        }

        return($s);
    }

    function valuesTable($params){
        $s = "<P>&nbsp;</P>";

        $params = unserialize($params);

        $check = $this->oSLDescDB->GetListDescObs( array("osp"=>$params['kSpec'], "oname"=>$params['kVar'], "desc_k" => $params['kObs'] ) );
        $defsRA = $this->oSLDescDefs->GetDefsRAFromOSP($params['kSpec']);
//        $s .= "<H3>Results For:</H3>";
//        $s .= "<p>Species: ".ucwords($params['kSpec'])."</p>";
//        $s .= "<p>Province: ".$params['kProv']."</p>";
//        $s .= "<p>Variety: ".$params['kVar']."</p>";

        $s .= "<table border='1'>";
        $s .= "<tr>";
        $s .= "<th>Frequency</th>";
        $s .= "<th>".$defsRA[$params['kObs']]['l_EN']."</th>";
        if($params['Imgs']){
            $s .= "<th>Img</th>";
        }
        $s .= "<th>View Record</th>";
        $s .= "</tr>";
        $count =0;

        if(@$params['Types']){
            $Tsize = count($params['Types']);
            $TFre = array_count_values($params['Types']);
            $TUni = array_unique($params['Types']);

            foreach($TUni as $t){
                $s .= "<tr>";
                $s .= "<td>".$TFre[$t]."/$Tsize</td>";
                $s .= "<td>$t</td>";
                if($params['Imgs']){
                    if(@$params['Imgs'][$count][$t]){
                        $s .= "<td><img src='http://www.seeds.ca/sl/descimg/".$params['Imgs'][$count][$t]."'height=100></img>;</td>";
                    }else{
                        $s .= "<td>N/A</td>";
                    }
                }
                $r =array();

                foreach($check as $c){
                        if($defsRA[$params['kObs']]['m'][$c['v']] == $t){ //t is string v is numeric, must convert t back to numeric or v to string
                        $r[] = $c['fk_sl_varinst'];
                    }
                }
                $re ="";
                foreach($r as $rec){
                    $re .= "<a href='".$this->linkToMe( array( 'kSpec'=>$params['kSpec'],
                                                               'kRec'=>$rec,
                                                               'kObs'=>$params['kObs'],
                                                               'kVar'=>$params['kVar'],
                                                               'kProv'=>$params['kProv']) )."'>"
                          ."$rec</a> ";
                }
                $s .= "<td>$re</td>";
                $s .= "</tr>";
                $count++;
            }


            $x = $params['Types'];
            arsort($x);
            $y = array_count_values($x);
            $v = array_keys($y);
            $c = 0;
            $valsRA ="";
            foreach ($y as $f){
                $valsRA[]=array('val'=>$c,'freq'=>$f,'str'=>ucwords($v[$c]));
                $c ++;
            }



        }elseif(@$params['Vals']){
            $count =0;
            $Vsize = count($params['Vals']);
            $ValsFre = array_count_values($params['Vals']);
            $ValsUni = array_unique($params['Vals']);
            foreach($ValsUni as $v){
                $count++;
                $s .= "<tr>";
                $s .= "<td>".$ValsFre[$v]."/$Vsize</td>";
                $s .= "<td>$v</td>";
                $r =array();
                foreach($check as $c){
                    if($c['v'] == $v){
                        $r[] = $c['fk_sl_varinst'];
                    }
                }
                $re ="";
                foreach($r as $rec){
                    $re .= "<a href='".$this->linkToMe( array( 'kSpec'=>$params['kSpec'],
                                                               'kRec'=>$rec,
                                                               'kObs'=>$params['kObs'],
                                                               'kVar'=>$params['kVar'],
                                                               'kProv'=>$params['kProv']) )."'>"
                          ."$rec</a> ";
                }
                $s .= "<td>$re</td>";
                $s .= "</tr>";
            }

            $x = $params['Vals'];
            asort($x);
            $y = array_count_values($x);
            $v = array_keys($y);
            $c = 0;
            $valsRA ="";
            foreach ($y as $f){
                $valsRA[]=array('val'=>$v[$c],'freq'=>$f);
                $c ++;
            }
        }
        $s .= "</table>";
        $s .= "<br>";
        $title = $defsRA[$params['kObs']]['l_EN'];

        $s .= $this->showGraph($valsRA,ucwords($title));

/*
        if( $params['Back'] == 'V' ) {
            $s.= "<div>"
                ."<a href='".$this->linkToMe( array( 'kSpec'=>$params['kSpec'],
                                                     'kObs'=>$params['kObs'],
                                                     'kProv'=>$params['kProv']) )."'>"
                ."Back</a></div>";
        } else {
            $s.= "<div>"
                ."<a href='".$this->linkToMe( array( 'kSpec'=>$params['kSpec'],
                                                     'kVar'=>$params['kVar'],
                                                     'kProv'=>$params['kProv']) )."'>"
                ."Back</a></div>";
        }
*/
        return($s);
    }

    function getCurve($valsRA){
        $s = '';
        $c = count($valsRA);
        $maxFreq =$valsRA[0]['freq'];
        $minVal =$valsRA[0]['val'];
        $maxVal = $valsRA[0]['val'];
        foreach($valsRA as $v){
            if($v['freq'] > $maxFreq){
                $maxFreq = $v['freq'];
            }
            if($v['val'] > $maxVal){
                $maxVal = $v['val'];
            }
            if($v['val'] < $minVal){
                $minVal = $v['val'];
            }
            if(@$v['str']){
                $str = TRUE;
            }

        }

        $dataPointsRA = "";
        $curveRA = "";
        foreach($valsRA as $v){
            $xRA[] = $v['val'];
            $yRA[] = $v['freq'];

            $dataPointsRA[] = array('x'=> $v['val'],'y'=> '');
        }
        $curX = $xRA[0];
        $curY = $yRA[0];
        $count = 0;

        foreach($dataPointsRA as $dRA){
            if(@$yRA[$count-2]){$y1 = $yRA[$count-2];}elseif(@$yRA[$count-1]){$y1 = $yRA[$count-1];}else{$y1 = $yRA[$count];}
            if(@$yRA[$count-1]){$y2 = $yRA[$count-1];}else{$y2 = $yRA[$count];}
            if(@$yRA[$count]){$y3 = $yRA[$count];}
            if(@$yRA[$count+1]){$y4 = $yRA[$count+1];}else{$y4 = $yRA[$count];}
            if(@$yRA[$count+2]){$y5 = $yRA[$count+2];}elseif(@$yRA[$count+1]){$y5 = $yRA[$count+1];}else{$y5 = $yRA[$count];}

            $dataPointsRA[$count]['y'] = ($y1+($y2*2)+($y3*4)+($y4*2)+$y5)/10;
            $count ++;
        }



        return($dataPointsRA);
    }

    function showGraph($valsRA,$title){
        $maxFreq =$valsRA[0]['freq'];
        $minVal =$valsRA[0]['val'];
        $maxVal = $valsRA[0]['val'];
        $str = FALSE;
        foreach($valsRA as $v){
            if($v['freq'] > $maxFreq){
                $maxFreq = $v['freq'];
            }
            if($v['val'] > $maxVal){
                $maxVal = $v['val'];
            }
            if($v['val'] < $minVal){
                $minVal = $v['val'];
            }
            if(@$v['str']){
                $str = TRUE;
            }

        }
        $maxVal = ceil($maxVal);
        $minVal = floor($minVal);

        if ($minVal <= 5){
            $minVal = 0;
        }


        $o = new SLSourcesCharts( $this->kfdb );
        return( $o->SLDescFrequency( $title, $valsRA ) );


        if($str == FALSE){
            $dPtsRA = $this->getCurve($valsRA);
        }else{
            $dPtsRA = "";
        }

        $s ='';
        $s = "<img src='recordGraph.php?maxFreq=$maxFreq&minVal=$minVal&maxVal=$maxVal&valsRA=".serialize($valsRA)."&str=$str&title=$title&dPtsRA=".serialize($dPtsRA)."'></img>";

        return($s);
    }


}



class SL_DescReport     // move this to seedcommon/sl
/******************
 */
{
    var $oSLDescDefs;

    function __construct( SL_DescDB $oSLDescDB )
    /*******************************************
     */
    {
        $this->oSLDescDefs = new SL_DescDefs( $oSLDescDB );
    }

    function Report_f_cm2in( $code, $raDO, $fInchInc = 1.0 )
    /*******************************************************
        Draw a table of inch ranges for the given array of DescObs
     */
    {
        $sOut = "";
        $raRange = array();
        foreach( $raDO as $obs ) {
            @$raRange[ intval(floatval($obs['v'])/2.5/$fInchInc) ] += 1;
        }
        ksort($raRange);    // since k/v are added in arbitrary order, sort keys to make foreach do the right thing
        $raDefs = $this->oSLDescDefs->GetDefsRAFromCode($code);
        $sOut .= "<DIV class='sldesc_report'>"
                ."<DIV class='sldesc_report_label'>{$raDefs[$code]['l_EN']}</DIV>"
                ."<DIV class='sldesc_report_body'>"
                ."<TABLE><TR><TH>Inches</TH><TH># reports</TH></TR>";
        foreach( $raRange as $k => $v ) {
            if( !$v ) continue;
            $sOut .= "<TR><TD style='padding-right:3em'>".($k*$fInchInc)." - ".(($k+1)*$fInchInc)."</TD><TD>$v</TD></TR>";
        }
        $sOut .= "</TABLE></DIV></DIV>";

        return( $sOut );
    }

    function Report_i_linear( $code, $raDO, $iInc = 1, $iMax = 0 )
    /*************************************************************
        Draw a table of integer ranges for the given array of DescObs
        $iMax=0 means no maximum
     */
    {
        $sOut = "";
        $raRange = array();
        foreach( $raDO as $obs ) {
            $n = $obs['v'];
            if( $iMax && $n > $iMax )  $n = $iMax;
            @$raRange[ intval($n / $iInc) ] += 1;
        }
        ksort($raRange);    // since k/v are added in arbitrary order, sort keys to make foreach do the right thing
        $raDefs = $this->oSLDescDefs->GetDefsRAFromCode($code);
        $sOut .= "<DIV class='sldesc_report'>"
                ."<DIV class='sldesc_report_label'>{$raDefs[$code]['l_EN']}</DIV>"
                ."<DIV class='sldesc_report_body'>"
                ."<TABLE><TR><TH>Range</TH><TH># reports</TH></TR>";
        foreach( $raRange as $k => $v ) {
            if( !$v ) continue;
            $sOut .= "<TR><TD style='padding-right:3em'>".(($k == $iMax) ? ($k*$iInc."+") : (($k*$iInc)." - ".(($k+1)*$iInc)))."</TD><TD>$v</TD></TR>";
        }
        $sOut .= "</TABLE></DIV></DIV>";

        return( $sOut );
    }

    function Report_i_geom( $code, $raDO, $fGeom = 2.0, $iMax = 0 )
    /**************************************************************
        Draw a table of integer ranges for the given array of DescObs
        The first range is 1, then 1+1 to (1+1)*$fGeom-1, then (1+1)*$fGeom to ((1+1)*$fGeom)*$fGeom-1)
        $iMax=0 means no maximum
     */
    {
        $sOut = "";
        $raRange = array();

        $iTop = $iMax;
        if( !$iTop ) {
            // find the top value
            foreach( $raDO as $obs ) {
                if( $obs['v'] > $iTop )  $iTop = $obs['v'];
            }
        }
        $raRangePoints = array( 1 );
        $n = 1;
        while( $n < $iTop ) {
            $n1 = intval($n * $fGeom);
            if( $n1 == $n ) $n1 = $n + 1;
            $n = $n1;
            if( $n > $iTop )  $n = $iTop;
            $raRangePoints[] = $n;
        }

        foreach( $raDO as $obs ) {
            $n = $obs['v'];
            if( $iMax && $n > $iMax )  $n = $iMax;

            // find the largest range point that is less than $n
            $r = 1;
            foreach( $raRangePoints as $rp ) {
                if( $rp > $n ) break;
                $r = $rp;
            }
            @$raRange[$r] += 1;
        }
        ksort($raRange);    // since k/v are added in arbitrary order, sort keys to make foreach do the right thing
        $raDefs = $this->oSLDescDefs->GetDefsRAFromCode($code);
        $sOut .= "<DIV class='sldesc_report'>"
                ."<DIV class='sldesc_report_label'>{$raDefs[$code]['l_EN']}</DIV>"
                ."<DIV class='sldesc_report_body'>"
                ."<TABLE><TR><TH>Range</TH><TH># reports</TH></TR>";
        for( $i = 0; $i < count($raRangePoints); ++$i ) {
            $r1 = $raRangePoints[$i];
            $v = @$raRange[$r1];
            if( !$v ) continue;

            $sOut .= "<TR><TD style='padding-right:3em'>";
            if( $i == count($raRangePoints)-1 ) {
                $sOut .= $r1;   // the top range point
                if( $iMax ) $sOut .= "+";
   // this does the right thing if iMax is set and there are values above iMax.  But if it's just the iTop, the last range should be (x to iTop) instead of iTop alone
            } else {
                $r2 = $raRangePoints[$i+1];
                if( $r1 == $r2-1 ) {
                    $sOut .= $r1;   // a range of one number
                } else {
                    $sOut .= $r1." - ".($r2-1);
                }
            }
            $sOut .= "</TD><TD>$v</TD></TR>";
        }
        $sOut .= "</TABLE></DIV></DIV>";

        return( $sOut );
    }
}


?>
