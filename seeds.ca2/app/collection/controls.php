<?php

class SearchControl
{
    private $oSCA;
    private $raSearchControlConfig;
    private $oSNavForm;
    private $sSearchCond;

    function __construct( SLCollectionAdmin $oSCA )
    {
        $this->oSCA = $oSCA;

        $raT = array('Species'=>'S.name_en',//'S_psp',
                     'Cultivar'=>'P.name',
                     'Botanical name'=>'S.name_bot',
                     'Orig Acc name' =>'A.oname',
                     'Acc #'=>'A._key',
                     'Lot #'=>'I.inv_number',
                     'Location'=>'I.location',
                     'Notes'=>'A.notes');
        $this->raSearchControlConfig =
            array( 'filters' => array( $raT, $raT, $raT ),
                   'template' => "<STYLE>#sedSeedSearch,#sedSeedSearch input,#sedSeedSearch select { font-size:9pt;}"
                                ."</STYLE>"
                                ."<DIV id='sedSeedSearch'>"
                                ."<DIV style='width:4ex;display:inline-block;'>&nbsp;</DIV>[[fields1]] [[op1]] [[text1]]<BR/>"
                                ."<DIV style='width:4ex;display:inline-block;'>and&nbsp;</DIV>[[fields2]] [[op2]] [[text2]]<BR/>"
                                ."<DIV style='width:4ex;display:inline-block;'>and&nbsp;</DIV>[[fields3]] [[op3]] [[text3]]<BR/>"
                                ."</DIV>" );
        // submode navigation bar for Seeds
        $this->oSNavForm = new SEEDFormSession( $oSCA->sess, 'collectionSearch', 'S' );
        $this->oSNavForm->Update();

        // this code is the same as in sed - should factor it

        if( SEEDSafeGPC_GetInt('bSrchReset') ) {
            $this->oSNavForm->CtrlGlobalSet('srch_fld1','');
            $this->oSNavForm->CtrlGlobalSet('srch_fld2','');
            $this->oSNavForm->CtrlGlobalSet('srch_fld3','');
            $this->oSNavForm->CtrlGlobalSet('srch_op1','');
            $this->oSNavForm->CtrlGlobalSet('srch_op2','');
            $this->oSNavForm->CtrlGlobalSet('srch_op3','');
            $this->oSNavForm->CtrlGlobalSet('srch_val1','');
            $this->oSNavForm->CtrlGlobalSet('srch_val2','');
            $this->oSNavForm->CtrlGlobalSet('srch_val3','');
        }

        $this->sSearchCond = $this->oSNavForm->SearchControlDBCond( $this->raSearchControlConfig );
    }

    function GetSearchCond()
    {
        return( $this->sSearchCond );
    }

    public function ControlDraw()
    {
        $s = "";

        $s .= "<FORM method='post' action='".$this->oSCA->oApp->PathToSelf()."'>"
             ."<TABLE border='0' cellpadding='10' style='border:1px solid gray;background-color:#eee;'><TR valign='top'>"
             ."<TD style='font-family:verdana,helvetica,sans serif;font-size:10pt;color:green;'>"
             .$this->oSNavForm->SearchControl( $this->raSearchControlConfig )
             ."</TD><TD>"
             ."<INPUT type='submit' value='Search'/><BR/><INPUT type='reset' onclick='document.getElementById(\"bSrchReset\").value=1;submit();'/>"
             ."<INPUT type='hidden' id='bSrchReset' name='bSrchReset' value='0'/>"
             ."</TD></TR></TABLE>"
             ."</FORM>";

        return( $s );
    }
}


class CollectionSelector
{
    private $oForm;
    private $oSCA;
    private $raColl = array();    // array of visible collections

    function __construct( SLCollectionAdmin $oSCA )
    {
        $this->oSCA = $oSCA;

        // Get any change to the collection selector
        $this->oForm = new SEEDFormSession( $oSCA->sess, 'collectionChoose', 'C' );
        $this->oForm->Update();

        // figure out which collections are visible, so the logic below can do the right thing if there's only one
        $raC = $this->oSCA->oColl->GetCollectionsVisibleByMe();
        $i = 0;
        foreach( $raC as $kC ) {
            if( ($kfr = $this->oSCA->oSLDBMaster->GetKFR( "C", $kC )) ) {
                $this->raColl[$i++] = $kfr->ValuesRA();
            }
        }
    }

    function GetKey()
    /****************
        Return current collection
     */
    {
        switch( count($this->raColl) ) {
            case 0:
                return( 0 );
            case 1:
                return( $this->raColl[0]['_key'] );
            default:
                return( intval($this->oForm->Value('selSC')) ? : $this->raColl[0]['_key'] );
        }
    }

    function GetSelectCtrl()
    /***********************
        <select> control to choose collection
     */
    {
        $raOpts = array();
        //if( count($this->raColl) > 1 ) $raOpts['All'] = 0;
        $raOpts['-- My Seed Collections --'] = '_disabled_';
        foreach( $this->raColl as $ra ) {
            if( $this->oSCA->oColl->CanAdminCollection($ra['_key']) ) {
                $raOpts[$ra['name']] = $ra['_key'];
            }
        }
        $raOpts['-- Other Seed Collections --'] = '_disabled_';
        foreach( $this->raColl as $ra ) {
            if( !$this->oSCA->oColl->CanAdminCollection($ra['_key']) ) {
                $raOpts[$ra['name']] = $ra['_key'];
            }
        }

        $raSelParms = array( 'attrs' => "onchange='submit();'",
                            'bEnableDisabledOptions' => true       // opts with value _disabled_ are disabled
                           );
        return( "<form method='post'>".$this->oForm->Select2( 'selSC', $raOpts, "", $raSelParms )."</form>" );
    }
}


?>
