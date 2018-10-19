<?php

/* _sl_source_sources.php
 *
 * Implement the user interface for Sources (companies, seedbanks, collectors, etc)
 */

class SLSourceSources
{
    public $oConsole;

    function __construct( Console01 $oConsole )
    {
        $this->oConsole = $oConsole;
        $raCompParms = array(
            "Label"=>"Seed Source",
            "fnTableItemDraw" => array($this,'SourceTableItemDraw'),
            "fnFormDraw" => array($this,'SourceFormDraw'),
            "raSEEDFormParms" => array( 'formdef' => array( 'bSupporter' => array( 'type'=>'checkbox' ),
                                                            'bNeedXlat' => array( 'type'=>'checkbox' ),
                                                            'bNeedVerify' => array( 'type'=>'checkbox' ),
                                                           // 'bNeedProof' => array( 'type'=>'checkbox' ),
                                      )),
            "SearchToolDef" => array( 'template' => $this->filterTemplate(),
                                      'controls' => array( 'sourcetype' => array( 'select',
                                                                                  array("All"=>'',"Companies"=>'company',"Seed Banks"=>'seedbank',"Collectors"=>'collector'))),
                                    //'filterCols' => array( label->col )
                                    ),
            //"SearchToolTemplate" => $this->filterTemplate(),
            "fnListFilter" => array($this,'listFilter'),
            //"SearchToolCols" => array( array( label=>col ) )
        );
        $this->oConsole->CompInit( $this->oConsole->oSLSrcCommon->kfrelSources, $raCompParms );
    }

    function SourceTableItemDraw( $oComp, $kfr )
    // $oComp is the same as $this->oConsole->oComp
    // $kfr is the record to draw
    // So if $kfr->Key()==$oComp->GetKey() we're drawing the current row
    {
        $sOnClick = "location.replace(\"".$oComp->EncodeUrlLink(array('kCurrRow'=>$kfr->Key()))."\");";
        $s = "<TABLE width='100%' style='cursor: pointer' onclick='$sOnClick'>"
            ."<TR valign='top'>"
            ."<TD bgcolor='".($kfr->Value('_status') ? '#fdd': CLR_BG_editEN)."' width='50%' >"
            .$this->oConsole->oSLSrcCommon->SourceItemDraw( $kfr, 'EN',
                    array(//"subst_name"=>"<A ".$oComp->EncodeUrlHREF(array('kCurrRow'=>$kfr->Key())).">[[name]]</A>",
                          "bEdit"=>true) )
            ."</TD><TD bgcolor='".CLR_BG_editFR."' width='50%' >"
            .$this->oConsole->oSLSrcCommon->SourceItemDraw( $kfr, 'FR',
                    array( //"subst_name"=>"<A ".$oComp->EncodeUrlHREF(array('kCurrRow'=>$kfr->Key())).">[[name]]</A>",
                          "bEdit"=>true) )
            //."</TD><TD>".$oComp->ButtonDeleteRow($kfr->Key())
            ."</TD></TR></TABLE>";

        return( $s );
    }

    function SourceFormDraw( $oForm )
    {
        $s = "<table cellpadding='5' width='100%' align='center'>"
            .$this->formDraw_field( "name", "Name", $oForm, 1, 1 )
            .$this->formDraw_field( "addr", "Street Address", $oForm, 1, 1 )
            .$oForm->ExpandForm(
                "||| City             || [[city]]"
               ."||| Province         || [[prov]]"
               ."||| Postal/Zip code  || [[postcode]]"
               ."||| Country          || ".$oForm->Select2( 'country',  array( "Canada"=>'Canada',
                                                                               "USA"=>'USA',
                                                                               "Other (type it in province)"=>'Other') )
               ."||| Phone            || [[phone]]"
               ."||| Web              || [[web]]"
               ."||| Email            || [[email]]"
               ."||| Year established || [[year_est | size:6]]"
               ."||| Supporter        || [[checkbox:bSupporter]]"
            )

            ."<tr><td align='left' colspan='2'>"
            ."Description<br/>"
            ."<div style='background-color:".CLR_BG_editEN."; padding:5px'><span style='font-size:8pt'>(English)</span><br/>"
                .$oForm->TextArea( 'desc_en', "", 0, 6, array( 'width'=>'100%', 'attrs'=>"wrap='soft'"))."</div>"
            ."<div style='background-color:".CLR_BG_editFR."; padding:5px'><span style='font-size:8pt'>(Fran&ccedil;ais)</span><br/>"
                .$oForm->TextArea( 'desc_fr', "", 0, 6, array( 'width'=>'100%', 'attrs'=>"wrap='soft'"))."</div>"
            ."</td></tr>"

            .$oForm->ExpandForm(
                "||| Translation needed  || [[checkbox:bNeedXlat]]"
               ."||| Verification needed || [[checkbox:bNeedVerify]]"
              // ."||| Proofreading needed || [[checkbox:bNeedProof]]"
               ."||| {colspan=2} Comments (not shown publicly) <br/> [[textarea: comments | width:100% attrs:wrap='soft']]"
               ."||| Source type || ".$oForm->Select2( 'sourcetype', array( 'Company'=>'company',
                                                                            'Seed Bank'=>'seedbank',
                                                                            'Collector'=>'collector') )
             )
            ."</table><input type='submit' value='Save'/>";

        return( $s );
    }

    function formDraw_field( $name, $label, $oForm, $bEN, $bFR )
    {
        $s = "<TR><TD align='left'>$label</TD><TD align='left'>"
            ."<TABLE cellpadding='5'>";
        if( $bEN )  $s .= "<TR><TD bgcolor=".CLR_BG_editEN."><span style='font-size:8pt'>(English)</span> ".$oForm->Text( $name.'_en', "", array( 'width'=>'100%' ))."</TD></TR>\n";
        if( $bFR )  $s .= "<TR><TD bgcolor=".CLR_BG_editFR."><span style='font-size:8pt'>(Fran&ccedil;ais)</span> ".$oForm->Text( $name.'_fr', "", array( 'width'=>'100%' ))."</TD></TR>\n";
        $s .= "</TABLE>";
        if( !$bEN )  $s .= $oForm->Hidden( $name.'_en' );
        if( !$bFR )  $s .= $oForm->Hidden( $name.'_fr' );
        $s .= "</TD></TR>";

        return( $s );
    }

    function DrawControlArea()
    {
        return( "<DIV>".$this->oConsole->oComp->SearchToolDraw()."</DIV>"
               ."<DIV>Sort by country/province or alphabetically</DIV>" );
    }

    function filterTemplate()
    {

         return( "Show: [[sourcetype]]"
                .SEEDStd_StrNBSP('',5)." and ".SEEDStd_StrNBSP('',5)
                ."[[fields1]] [[op1]] [[text1]]"
                ." <INPUT type='submit' value='Search'/>" );
    }

    function listFilter()
    /********************
        Return the filter string from the control area, modulo the oComp-managed part
     */
    {
        $r = addslashes(SEEDSafeGPC_GetStrPlain( 'filter_sourcetype' ));

        return( empty($r) ? "" : "sourcetype='$r'" );
    }

}


?>