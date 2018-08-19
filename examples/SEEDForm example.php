<?php
define( "SITEROOT", "../seeds.ca/" );
include( SITEROOT."site.php" );
include_once( STDINC."SEEDForm.php" );
include_once( STDINC."SEEDSession.php" );
include_once( STDINC."KeyFrame/KFRelation.php" );
include_once( STDINC."KeyFrame/KFUIForm.php" );
include( SEEDCOMMON."siteStart.php" );

list($kfdb,$sess) = SiteStartSession();

// SEEDFormHints watermark hinting:
//     - load this script
//     - <FORM onSubmit='SEEDFormHintsSubmit();'>
//     - <INPUT type='text/password' sfHintText='the presence of this attr makes this a hinting textbox' value='any current value'/>
echo "<SCRIPT type='text/javascript' src='".W_ROOT."std/js/SEEDFormHints.js'></SCRIPT>";

echo "<STYLE>"
    ."body, td { font-family:arial,helvetica; font-size:9pt; }"
    .".a { border: 1px solid #555; background-color: #eee; margin:1ex; padding:0 1ex; }"
    ."</STYLE>";


echo "<TABLE border='1'><TR valign='top'><TD>Request</TD><TD>"; var_dump($_REQUEST); echo "</TD>";
echo "<TD>Session</TD><TD>"; var_dump($_SESSION); echo "</TD></TR></TABLE>";


// Each Update extracts and updates its own cid. It makes sense to process each component separately because each form component can be governed by a different
// derivation of SEEDForm (as it is here).


echo "<FORM onSubmit='SEEDFormHintsSubmit();'>"
    ."<P>This is an example of several kinds of forms, all HTML-coded within one &lt;FORM&gt; element."
    .SEEDStd_StrNBSP('',10)
    ."<A HREF='{$_SERVER['PHP_SELF']}'>Restart with no active http parms</A>"
    ."</P>"
    ."<P><INPUT type='submit'/></P>"
    ."<TABLE border='1' width='100%'><TR valign='top'><TD width='50%'>"
    .showBasic()
    ."</TD><TD>"
    .showSession()
    ."</TD></TR><TR valign='top'><TD>"
    .showKF()
    ."</TD><TD>"
    .showPreStoreOverride()
    ."</TD></TR></TABLE>"
    ."</FORM>";



/***************************************************
 * SEEDForm
 *
 */
function showBasic()
{
    $s = "<DIV class='a'><H3>SEEDForm with example of urlparm storage and SEEDFormHints</H3>"
        ."<P>Base SEEDForm class: stores data in the http stream.</P>"
        ."<P></P>";

    $oSFA = new SEEDForm( "A", array( 'DSParms' => array( "urlparms" => array( 'firstname'=>'data1',
                                                                               'lastname'=>'data1',
                                                                               'city'=>'data2',
                                                                               'postcode'=>'data2' ) ) ) );
    $oSFA->Update();
    $raP = array('size'=>10);

    $s .= "<DIV style='border:0px solid #333;color:blue;float:right'>"
          ."data1=".@$oSFA->oDS->raBaseData['data1']."<BR/>"
          ."address=".@$oSFA->oDS->raBaseData['address']."<BR/>"
          ."data2=".@$oSFA->oDS->raBaseData['data2']
          ."</DIV>";

    $s .= "<STYLE>#CidA label { width:60px; display:inline-block; }</STYLE>"
         ."<DIV id='CidA'>"
         ."<B>Cid A</B>: First name and Last name are stored as urlparms in data1, City and Postcode are stored as urlparms in data2.<BR/>"
         .$oSFA->Text( 'firstname', '', array_merge($raP,array('attrs'=>"sfHintText='First name'")) )
         .SEEDStd_StrNBSP("",5).$oSFA->Text( 'lastname', '', array_merge($raP,array('attrs'=>"sfHintText='Last name'")) )
         ."<BR/>".$oSFA->Text( 'address', '', array('size'=>25, 'attrs'=>"sfHintText='Address'") )
         ."<BR/>".$oSFA->Text( 'city', '', array_merge($raP,array('attrs'=>"sfHintText='City'")) )
         .SEEDStd_StrNBSP("",5).$oSFA->Text( 'postcode', '', array_merge($raP,array('attrs'=>"sfHintText='Postal Code'")) )
         ."<BR/>".$oSFA->Text( 'pwrd', '', array('size'=>25, 'bPassword'=>true, 'attrs'=>"sfHintText='enter your password'") )
         ."</DIV>"
         ."<BR/>";


    $oSFB = new SEEDForm( "B" );
    $oSFB->Update();
    $s .= "<DIV style='border:0px solid #333;color:blue;float:right'>"
          ."petname=".@$oSFB->oDS->raBaseData['petname']."<BR/>"
          ."pettype=".@$oSFB->oDS->raBaseData['pettype']."<BR/>"
          ."petlicense=".@$oSFB->oDS->raBaseData['petlicense']
          ."</DIV>";

    $s .= "<DIV id='CidB'>"
         ."<B>Cid B</B>: the checkbox does not need a formDef, because there is no persistent value to 'turn off'.<BR/>"
         ."&nbsp;&nbsp;".$oSFB->Text( 'petname', 'Pet name', $raP )
         ."&nbsp;&nbsp;".$oSFB->Select( 'pettype', 'Pet Type', array('None'=>'None','Cat'=>'Cat','Dog'=>'Dog','Dragonfly'=>'Dragonfly') )
         ."&nbsp;&nbsp;".$oSFB->Checkbox( 'petlicense', 'Licensed' )
         ."</DIV>";

    $s .= "</DIV>"; // a

    return( $s );
}


/***************************************************
 * SEEDFormSession
 *
 */
function showSession()
{
    global $sess;

    $s = "<DIV class='a'><H3>SEEDFormSession</H3><P>This derivation stores data in a SEEDSession variable namespace. "
        ."Restart to clear http parms: the values are still here.</P>";

    // What's cool
    //
    // * insect and legs both appear in SEEDForm C and D, but there's no confict in the http parms because
    //   the control names are encoded sfCp_insect and sfDp_insect
    // * the checkboxes in D can be reset (http doesn't normally do that) because we declare them as checkboxes
    // * form D is drawn using a template that references controls by name
    // * form D declares Legs as an integer, so it appears in the form as a zero if it's blank and the user's input gets intval()

    $oSFC = new SEEDFormSession( $sess, "sfC", "C" );
    $oSFC->Update();
    $s .= "<DIV id='CidC'>"
         ."<B>Cid C</B>: note that the vars 'insect' and 'legs' are stored in two separate namespaces (C and D), with no conflict<BR/>"
         ."&nbsp;&nbsp;".$oSFC->Select( 'insect', 'Insect type', array('None'=>'None','Bee'=>'Bee','Fly'=>'Fly','Beetle'=>'Beetle') )
         ."&nbsp;&nbsp;".$oSFC->Text( 'legs', "Legs", array('size'=>2) )
         ."</DIV>"
         ."<BR/>";

     $oSFD = new SEEDFormSession( $sess, "sfD", "D",
                 array( "formdef" => array( 'flying' => array( 'type'=>'chbox' ),
                                            'pollinating' => array( 'type'=>'chbox' ),
                                            'striped' => array( 'type'=>'cheox' ) ),
                        "fields" => array( 'insect'      => array( 'control'=>'text', 'label'=>"Insect name", 'size'=>10 ),
                                           'legs'        => array( 'control'=>'text', 'label'=>"Legs",        'size'=>2, 'type'=>'integer' ),
                                           'flying'      => array( 'control'=>'checkbox', 'urlparm'=>'chex', 'label'=>"Flying" ),
                                           'pollinating' => array( 'control'=>'checkbox', 'urlparm'=>'chex', 'label'=>"Pollinating" ),
                                           'striped'     => array( 'control'=>'checkbox', 'urlparm'=>'chex', 'label'=>"Striped" ),
                  )
     ) );

     $oSFD->Update();
     $s .= "<DIV id='CidD'>"
          ."<B>Cid D</B>: uses a formDef to make the checkboxes work.<BR/>"
          ."&nbsp;&nbsp;".$oSFD->Text( 'insect', 'Insect name', array('size'=>10) )
          ."&nbsp;&nbsp;".$oSFD->Text( 'legs', 'Legs', array('size'=>2) )
          ."&nbsp;&nbsp;".$oSFD->Checkbox( 'flying', 'Flying' )
          ."&nbsp;&nbsp;".$oSFD->Checkbox( 'pollinating', 'Pollinating' )
          ."&nbsp;&nbsp;".$oSFD->Checkbox( 'striped', 'Striped' )
          ."<BR/>".$oSFD->Checkbox( 'bTest', "Can't turn this one off once it's clicked because it isn't in the formDef" )
          ."<BR/>"
          ."</DIV>";

    $s .= "</DIV>"; // a

    return( $s );
}

/***************************************************
 * SEEDFormSession with PreStore()
 *
 */
function showPreStoreOverride()
{
    global $sess;

    $s = "<DIV class='a'><H3>SEEDFormSession with PreStore() Override</H3>"
        ."<P>The PreStore() override lets the implementation edit the input before it is stored. "
        ."The method is defined in the DataStore, so it works for every derivation.</P>";


    class mySEEDFormSession extends SEEDFormSession
    {
        function __construct($sess)
        {
            parent::__construct( $sess, "sfE", "E",
                                    array( "DSParms" => array('fn_DSPreStore'=>array(&$this,'myDSPreStore') ) ) );
        }

        function myDSPreStore()
        {
            $d = $this->oDS->Value('date');
            if( is_numeric($d) ) {
                $d *= 3600 * 24;
                $sDate = date( "l M j, Y", $d );
                $this->oDS->SetValue('date',$sDate);
            }
            return( true );
        }
    }

    $oSFE = new mySEEDFormSession( $sess );
    $oSFE->Update();
    $s .= "Cid E:"
         ."&nbsp;&nbsp;".$oSFE->Text( 'date', 'Date' )." type a number and we'll convert it as days from the epoch"
         ."<BR/>"
         ."</DIV>";

    return( $s );
}


/***************************************************
 * KeyFrameForm
 *
 */
function showKF()
{
    global $kfdb;
    $kfreldefSB = array( "Tables" => array( array( "Table" => 'SEEDMetaTable_StringBucket',
                                                   "Type"  => 'Base',
                                                   "Fields" => array( array("col"=>"ns", "type"=>"S", "default"=>'SEEDForm_example'),
                                                                      array("col"=>"k",  "type"=>"S"),
                                                                      array("col"=>"v",  "type"=>"S" ) ) ) ) );
    $kfrelSB = new KeyFrameRelation( $kfdb, $kfreldefSB, 0 );

    $s = "<DIV class='a'><H3>KeyFrameForm</H3><P>This derivation uses KF to store data in a database. "
        ."This implementation uses SEEDMetaTable_StringBucket.</P>";

    $oSFF = new KeyFrameUIForm( $kfrelSB, "F", array( "formdef" => array( 'ns' => array( 'presetOnInsert'=>true ) ) ) );  // presetOnInsert excludes ns from the bSkipBlankRows test
    $oSFF->Update();  // $oSFF->kfr is used here to hold the data for each row that is updated
    $s .= "Cid F:<BR/>";
    $i = 0;
    if( ($kfr = $kfrelSB->CreateRecordCursor( "ns='SEEDForm_example'" )) ) {
        $oSFF->SetKFR( $kfr );  // $oSFF->kfr is used here to hold the data for each row that is being drawn
        while( $kfr->CursorFetch() ) {
            $oSFF->SetRowNum( $i++ );   // need this when there's more than one field with the same name (multiple rows)
            $s .= $oSFF->HiddenKey()
                 ."Key ".$kfr->Key().":"
                 ."&nbsp;&nbsp;".$oSFF->Text( 'k', 'k' )
                 ."&nbsp;&nbsp;".$oSFF->Text( 'v', 'v' )
                 ."<BR/>";
        }
    }
    $kfr = $kfrelSB->CreateRecord();
    $oSFF->SetRowNum( $i++ );
    $s .= $oSFF->HiddenKey()
         ."Key ".$kfr->Key().":"
         ."&nbsp;&nbsp;".$oSFF->Text( 'k', 'k' )
         ."&nbsp;&nbsp;".$oSFF->Text( 'v', 'v' )
         ."<BR/>"
         ."</DIV>";

    return( $s );
}

echo "<TABLE border='1'><TR valign='top'><TD>Session</TD><TD>"; var_dump($_SESSION); echo "</TD></TR></TABLE>";

?>
