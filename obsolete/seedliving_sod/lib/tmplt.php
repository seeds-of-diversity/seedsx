<?php
/**************************************************************
 Template Processing Functions Ver 1.0 - August 26,2007 -
-Provides a functions for template processing
***************************************************************/
define("APACHE_INCL","<!--#include");
define("APACHE_INCR","-->");

include_once( STDINC."SEEDForm.php" );        // SEEDFormTagResolver
include_once( STDINC."SEEDTemplate.php" );


class SLiv_SEEDTagParser extends SEEDTemplate_SEEDTagParser
{
    private $oSLiv;
    private $oBasicResolver;
    private $oSEEDForm;    // for drawing forms in templates

    function __construct( SEEDLiving $oSLiv, $raParms, $oDSVars )
    {
$lang = "EN";

        $this->oSLiv = $oSLiv;

        /* Process:
         *     SeedLiving tags
         *     SEEDForm tags in Vanilla mode (require Form prefix)
         *     SEEDLocal tags (require Local prefix)
         *     Basic tags (appended to the list by EnableBasicResolver)
         */

        $raParms['EnableBasicResolver'] = array('LinkBase'=>SL2URL.'/');

        if( !isset($raParms['raResolvers']) )  $raParms['raResolvers'] = array();

        // Process SeedLiving tags
        $raParms['raResolvers'][] = array( 'fn'=>array($this,'ResolveTag'), 'raParms'=>array('this-is-passed-to-third-arg-of-this-ResolveTag') );

        // Process Form tags in Vanilla mode (require Form prefix)
        $oSEEDForm = new SEEDForm( 'Plain' );
        $raParms['raResolvers'][] = array( 'fn'=>array($oSEEDForm,'ResolveTag'), 'raParms'=>array('bRequireFormPrefix'=>true) );

        // Process Local tags
        $oLS = new SEEDSessionAuthUI_Local();    // strings for the user account UI
        $oSEEDLocal = new SEEDLocal( $oLS->GetLocalStrings(), $lang );
        $raParms['raResolvers'][] = array( 'fn'=>array($oSEEDLocal,'ResolveTag'), 'raParms'=>array('bRequireLocalPrefix'=>true) );

        //$this->oBasicResolver = new SEEDTagBasicResolver( array('LinkBase'=>SL2URL.'/') );
        parent::__construct( $raParms, $oDSVars );
    }

    function ResolveTag( $raTag, SEEDTagParser $oTagDummy, $raParmsDummy )
    {
        $s = "";
        $bHandled = true;

        $target = @$raTag['target'];
        $p0 = $target;   // same as raParms[0]
        $p1 = @$raTag['raParms'][1];

        switch( strtolower($raTag['tag']) ) {
            // Interesting fact: oTmpl doesn't exist yet when this object is constructed because this object is part of the generator that makes oTmpl.
            case 'include':
                $s = $this->oTmpl->ExpandTmpl( $target );
                break;

            case 'sliv':
                if( $target == 'DrawSeedsSplash' ) {
                    // <!--#include virtual="sl.php?overlord=seedsSplash"-->
                    //   //$f = fopen("http://www.seeds.ca/seedliving/sl.php?overlord=seedsSplash", "r" ); fpassthru($f); fclose($f);
                    $s = $this->oSLiv->DrawSeedsSplash();
                }
                break;

// You can use [[Link:]] instead of this because LinkBase=SL2URL is defined in EnableBasicResolver
            case 'slivlink':
                $s = "<a href='".SL2URL."/$p0'>$p1</a>";
                break;

            case 'slivimg':
                $s = "<img src='".SL2URL."/$p0'>";
                break;

            case 'slivtags':
                $s = $this->oSLiv->DrawTags();
                break;

            default:
                $bHandled = false;
                break;
        }

        return( array($bHandled,$s) );
    }
}

class SLiv_SEEDTemplate_Generator extends SEEDTemplate_Generator
{
    private $oSLiv;
    function __construct( SEEDLiving $oSLiv, $raParms ) { $this->oSLiv = $oSLiv; parent::__construct( $raParms ); }

    function factory_SEEDTag( $raParms, $oDSVars ) { return( new SLiv_SEEDTagParser( $this->oSLiv, $raParms, $oDSVars ) ); }
}


class SLivTemplate
{
    private $oSLiv;
    private $oTmpl;

    function __construct( SEEDLiving $oSLiv )
    {
        $this->oSLiv = $oSLiv;
        $o = new SLiv_SEEDTemplate_Generator( $this->oSLiv,
                                              // SEEDSession.html goes first so user.html can override its top-level templates (e.g. acctCreate, acctProfile)
                                              // This allows SEEDSession to manage the logic that draws various UI templates
                                              array( 'fTemplates' => array(STDINC."templates/SEEDSession.html",
                                                                           "./templates/user.html",
                                                                           "./templates/basket.html",
                                                                           "./templates/myseeds.html",
                                                                           "./templates/products.html",
                                                                           "./templates/seedtmpl.html"),
                                                     'SEEDTagParms' => array(
                                                         // SEEDTemplate will use the BasicResolver with this LinkBase
                                                         'EnableBasicResolver' => array('LinkBase'=>SL2URL.'/'),
                                                         // SEEDTemplate will process [[FormText:]],[[FormCheckbox:]],etc in Vanilla mode
                                                         'EnableSEEDForm' => true,
                                                         'SEEDFormCid' => 'Plain' ),
                                                     // array of SeedLivingParms are global vars in all templates
                                                     'vars' => $this->oSLiv->oSLivParms->GetRA()
        ) );
        $this->oTmpl = $o->MakeSEEDTemplate();
    }

    // NEW TEMPLATES
    function GetSEEDTemplate()  { return( $this->oTmpl ); }    // e.g. SEEDSession draws the UI with SeedLiving templates, so it uses this

    function Exists( $tmplname )
    {
        return( @$this->oTmpl->Exists( $tmplname ) );
    }

    function ExpandTmpl( $tmplname, $raVars = array() )
    {
        $this->oTmpl->SetVars( $raVars );
        $s = $this->oTmpl->ExpandTmpl( $tmplname ); //var_dump(strlen($s));
        return($s);
    }


    // OLD TEMPLATES
    function Load( $tmplFname, $sMark = "%%", $ttDeprecated )
    {
        if( !($content = file_get_contents($tmplFname)) )  return( false );

        $nOffset = 0;
        while( ($nStart = strpos($content, $sMark, $nOffset)) !== false ) {
            if( ($nEnd = strpos( $content, $sMark, $nStart+strlen($sMark) )) !== false ) {
                $chunk = substr( $content, $nStart, $nEnd - $nStart );
            } else {
                $chunk = substr( $content, $nStart );
            }
            $temp = explode("\n",$chunk,2);
            $tmplName = trim(substr($temp[0],strlen($sMark)));
            $tmplBody = $temp[1];
            $this->raTmpl[$tmplName] = $tmplBody;

            tkntbl_add( $ttDeprecated, $tmplName, $tmplBody, 1 );

            if( $nEnd === false )  break;

            $nOffset = $nEnd;
        }

        return( true );
    }

    function Expand( $srcpath, $tempptr, $type, $tokens )
    {
        tkntbl_init(array(&$tempa));

        // put tokens into one array
        foreach( $tokens as $t => $v ) {
            foreach( $tokens[$t]->tkn as $key => $value ) {
                $tempa->tkn[$key] = $value;
            }
        }

        $content = $type ? tmplt_proc_ssi( $tempptr, $tempa )
                         : tmplt_proc_ssi( @file_get_contents($srcpath.$tempptr), $tempa );

        $content = $this->processContent( $content, $tempa );

        return( $content );
    }

    private function processContent( $content, $tempa )
    {
        $tokleft = "[SL]";
        $tokright = "[/SL]";

        while( ($spos = strpos($content,$tokleft)) !== false && ($epos = strpos($content,$tokright)) !== false ) {
            $logic = substr( $content, $spos, ($epos+strlen($tokright))-$spos );

            if( strpos( $logic, "#if" ) !== false ) {
                $sEndif = $tokleft."#endif".$tokright;
                $lpos = strpos( $content, $sEndif );
                $logiccmp = substr( $content, $spos, $lpos-$spos+strlen($sEndif) );
                $logicbak = $logiccmp;
                /*extract condition and true and false statements*/
                $condition = trim(str_replace(array($tokleft."#if",$tokright),"",$logic));
                if( strpos( $logiccmp, "#else" ) !== false ) {
                    $temp = explode( $tokleft."#else".$tokright, $logiccmp );
                    $true = trim(str_replace(array($tokleft."#if",$condition.$tokright),"",$temp[0]));
                    $false = trim(str_replace(array($sEndif),"",$temp[1]));
                } else {
                    /* just extract true statement */
                    $true = trim(str_replace(array($tokleft."#if",$condition.$tokright,$sEndif),"",$logiccmp));
                    $false="";
                }
                /* test condition */
                if( strpos($condition,"=") !== false ) {
                    $temp2 = explode( "=", $condition );
                    $rvalue = ttn($tempa,$temp2[0]) == str_replace("'","",$temp2[1]) ? $true : $false;
                } elseif( strpos($condition,"!") !== false ) {
                    $temp2 = explode("!",$condition);
                    $rvalue = ttn($tempa,$temp2[0]) != str_replace("'","",$temp2[1]) ? $true : $false;
                } else {
                    $rvalue = ttn($tempa,$condition) ? $true : $false;
                }

            } elseif( strpos($logic,"#for") !== false ) {
                /* loop future support */
            } elseif( strpos($logic,"#select") !== false ) {
                /* control box future support */
            } elseif( strpos($logic,"#query") !== false ) {
                /* control box future support */
            } elseif( strpos($logic,"#include") !== false ) {
                $sEndInclude = $tokleft."#endinclude".$tokright;
                $lpos = strpos($content,$sEndInclude);
                $logiccmp = substr( $content, $spos, $lpos-$spos+strlen($sEndInclude) );
                $logicbak = $logiccmp;
                $condition = trim(str_replace(array($tokleft."#include",$tokright,$tokleft."#endinclude"),"",$logiccmp));
                $rvalue = file_get_contents("http://".$_SERVER['HTTP_X_FORWARDED_HOST'].$condition);
                //echo "http://".$_SERVER['HTTP_X_FORWARDED_HOST'].$condition;
                //foreach($tempa->tkn as $key => $value){
                //    echo $tokleft.$key.$tokright."<br>";
                //    $rvalue = str_replace($tokleft.$key.$tokright,$value,$rvalue);
                //}
            } else {
                $logicbak = $logic;
                $logic = str_replace(array($tokleft,$tokright),"",$logic);
                if( strpos($logic,":") !== false ) {
                    $temp = explode(":",$logic);
                    if( ($n = ttn($tempa,$temp[0])) ) {
                        switch($temp[1]){
                            case "$":   $rvalue = is_numeric( $n ) ? number_format($n,2) : $n;  break;  // because some people put "$2.00 or trade" which makes a number_format warning
                            case "N":   $rvalue = $n."&nbsp;";                                  break;
                            case "NC":  $rvalue = $n.":&nbsp;";                                 break;
                            case "D":   $rvalue = date("Y-m-d",$n);                             break;
                            case "DF":  $rvalue = date("F j, Y",$n);                            break;
                            case "$$0": $rvalue = "$".number_format($n,0);                      break;
                            case "$$":  $rvalue = "$".number_format($n);                        break;
                            case "F":   $rvalue = number_format($n,0);                          break;
                            case "UC":  $rvalue = ucwords(strtolower($n));                      break;
                            case "L":   $rvalue = strtolower($n);                               break;
                            case "U":   $rvalue = strtoupper(strtolower($n));                   break;
                            case "NP":  $rvalue = str_replace("#","",$n);                       break;
                            case "SUB": $rvalue = substr($n,strlen($n)-1,1);                    break;
                            case "CON": $rvalue = substr($n,0,350);                             break;
                            case "PER": $rvalue = ($n*100);                                     break;
                        }
                    } else {
                        switch( $temp[1] ) {
                            case "$":  $rvalue = "&nbsp;";        break;
                            case "N":  $rvalue = "&nbsp;";        break;
                            case "NC": $rvalue = ":&nbsp;";       break;
                            case "NA": $rvalue = "N/A";           break;
                            default:   $rvalue = "";              break;
                        }
                    }
                } else {
                    $rvalue = ttn($tempa,$logic);
                }
            }
            $content = str_replace($logicbak,$rvalue,$content);
        }
        return( $content );
    }
}



function tmplt_mail(&$token,&$tmpltt,$chunk){

	//tkntbl_snprintf($token,"mailcommand",1,MAX_RESULTS,"/usr/sbin/sendmail -t -f%s",ttn($token,"email_from"));
	//$pmail = popen(ttn($token,"mailcommand"),"w");

	tkntbl_snprintf($token,"email_headers",1,MAX_RESULTS,"To:%s\n",ttn($token,"email_to"));
	tkntbl_snprintf($token,"email_headers",2,MAX_RESULTS,"From:%s\n",ttn($token,"email_from"));
	//tkntbl_snprintf($token,"email_headers",2,MAX_RESULTS,"Subject:%s\n",ttn($token,"email_subject"));
	tkntbl_add($token, "email_headers", "MIME-Version: 1.0\n", 2);
	tkntbl_add($token, "email_headers", "Content-type: text/html; charset=iso-8859-1\n", 2);



	//if($pmail){
		//if(!tmplt_proc_ex(TEMPLROOT,tkntbl_search($tmpltt,$chunk),OPENTAG,CLOSETAG,$pmail,1,array($token))) criterr("<br>Chunk Error : %s",$chunk);
	//} else  criterr("Fatal Mail Error");
	tmplt_proc_ex_tt(TEMPLROOT,tkntbl_search($tmpltt,$chunk),OPENTAG,CLOSETAG,1,stdout,$token,"message",array(&$token));
	mail(NULL,ttn($token,"email_subject"),ttn($token,"message"),ttn($token,"email_headers"));

	//fputs($pmail,"\n\n.\n\n");
	//pclose($pmail);

}

function tmplt_proc_ex($srcpath,$tempptr,$tokleft,$tokright,$out,$type,$tokens)
{
    global $oSLiv;
    $o = new SLivTemplate( $oSLiv );
    $content = $o->Expand( $srcpath, $tempptr, $type, $tokens );
    echo $content;

    return true;
}


function tmplt_proc_ex_tt($srcpath,$tempptr,$tokleft,$tokright,$comm,$type,&$token,$chunkname,$tokens)
{
    global $oSLiv;
    $o = new SLivTemplate( $oSLiv );
    $content = $o->Expand( $srcpath, $tempptr, true, $tokens );

    if( $comm == 1 ) {
        $token->tkn_add( $chunkname, $content );
    } else {
        $token->tkn_concat( $chunkname, $content );
    }

    return true;
}



function tmplt_enumtkns($chunk,$tokleft,$tokright){
	$content = strip_tags($chunk);

	$c=0;

	while($lpos = strpos($content,$tokleft)){
		//find pos of the right token
		$rpos = strpos($content,$tokright);

		//replace the token string
 		$temp[$c]= substr($content,$lpos,(($rpos+strlen($tokright)) - $lpos));
		$content = str_replace(substr($content,$lpos,(($rpos+strlen($tokright)) - $lpos)),"",$content);
		$c++;
	}

	for($c=0;$c<count($temp);$c++){
		$t = str_replace($tokright,"",str_replace($tokleft,"",$temp[$c]));
		$t_ = explode(":",$t);
		$temp[$c] = $t_[0];
	}

	return $temp;

}
function tmplt_load( $token, $tempname, $sMark )
{
    global $oSLiv;
    return( $oSLiv->oTmpl->Load( $tempname, $sMark, $token ) );
}

function tmplt_create($srcpath,$tempname,$temptoken){
	$c=0;
	//get file list
	$d = dir($srcpath);
	while (false !== ($entry = $d->read())) {
  		 if(strstr($entry,".htm") || strstr($entry,".html")){
		 	$filelist[$c] = $entry;
			$c++;
		 }
	}
	//open new file
	if(!$fp = fopen($srcpath.$tempname,"w")) die("Unable to create ".$srcpath.$tempname);
	for($c=0;$c<count($filelist);$c++){
		//output header
		fwrite($fp,$temptoken." ".str_replace(array(".html",".htm"),"",$filelist[$c])."\n");
		//output content
		fwrite($fp,file_get_contents($srcpath.$filelist[$c])."\n\n");
	}
	fclose($fp);

}
function tmplt_proc_ssi($out,$token)
{
	$found=1;
	$first=0;
	$last=0;

	if($out){
		while($found){
			//if($_SERVER['TERM']) echo "hello\n";

			$first = strpos($out,APACHE_INCL);
			$last = strpos($out,APACHE_INCR,$first);

			if(!$first && !$last) $found=0;
			if(!strlen($first)) $found=0;

			if($found){
					$ssi = substr($out,$first,(($last+strlen(APACHE_INCR))-$first));

					$filename = trim(str_replace(array("virtual=\"","\" "),"",substr($out,($first+strlen(APACHE_INCL)),($last-($first+strlen(APACHE_INCL))))));
					//if($_SERVER['TERM']) echo $filename."\n";

					foreach($token->tkn as $key => $value){
						//echo $key."\n";
						$filename = str_replace("[IBG]".$key."[/IBG]",$value,$filename);
					}
					//if($_SERVER['TERM']) echo $filename."\n";

					if(!$temp = file_get_contents($_SERVER['DOCUMENT_ROOT'].$filename)) $temp = file_get_contents("http://".$_SERVER['HTTP_X_FORWARDED_HOST'].$filename);
					$out = str_replace($ssi,$temp,$out);

			}
		}
	}

	return $out;
}
?>