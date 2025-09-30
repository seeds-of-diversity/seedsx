<?php

include_once( SEEDCORE."SEEDFile.php" );


function DrawSeedcatPage( $kfdb )
{
    $s = "";

    $p_seedcat = SEEDSafeGPC_GetStrPlain('seedcat');
    if( !$p_seedcat || $p_seedcat == 'index' ) {
    	$p_sort = SEEDSafeGPC_Smart( 'sort', array('n','y') );    // sort by name/year
        $s = "<TABLE border='0'><TR><TD valign='center'>"
            //."<H2>Classic Canadian Seed Catalogues</H2></TD>"
            ."<TD>".SEEDStd_StrNBSP("",10)."</TD>"
            ."<TD valign='center'><FORM method='post' action='{$_SERVER['PHP_SELF']}?q=heritage'>"
            .SEEDForm_Hidden( 'seedcat', 'index' )
            .SEEDForm_Select( 'sort', array('n'=>"Sort by Name", 'y'=>"Sort by Year"), $p_sort, array('selectAttrs'=>"onChange='submit();'") )
            ."</TD></TR></FORM></TABLE>";

        $s .= "<TABLE border='0'><TR valign='top'>"
             ."<TD>";
        if( ($kfr = getDocLibKFRC( $kfdb, "doc_type='seedcat'", array('sSortCol'=>($p_sort=='n' ? 'title' : 'pub_year')) )) ) {
            while( $kfr->CursorFetch() ) {
                $s .= $kfr->Expand( "<DIV style='margin:0 0 10px 5em'><A HREF='".Site_path_self()."?seedcat=[[doc_code]]'>" )
                     .($p_sort == 'n' ? $kfr->Expand( "[[publisher]] [[pub_year]]")
                                      : $kfr->Expand( "[[pub_year]] [[publisher]]"))
                     ."</A></DIV>";
            }
        }
        $s .= "</TD>";

/*
        $sSrch = SEEDSafeGPC_GetStrPlain('search');
        $s .= "<TD>"
             ."<DIV style='border:2px groove #aaa;background-color:#C8ECC4;margin-left:3em;padding:10px'>"
             ."<FORM method='get' action='${_SERVER['PHP_SELF']}'>"
             .SEEDForm_Hidden( 'seedcat', 'index' )
             .SEEDForm_Text('search',$sSrch)
             ."<INPUT type='submit' value='Search'/></FORM>";

        $raResults = array();// need a new api code for /lib?  = GoogleAPISearchResults( $sSrch, 'www.seeds.ca/lib/doc/sc' );
        foreach( $raResults['results'] as $ra ) {
            $url = $ra['url'];
            $sTitle = "";
            $raMatch = array();
            // find the doc code in the url, look up the doc title in the doclib
            if( preg_match('@/doc/sc/([^/]+)@i', $url, $raMatches ) ) {  // [1] is the matched string in parentheses
                $sTitle = $kfdb->Query1( "SELECT title FROM doclib_document WHERE _status=0 AND doc_code='".addslashes($raMatches[1])."'" );
            }
            if( empty($sTitle) )  $sTitle = "Title Not Found";
            $s .= "<P style='font-size:11pt;'><A HREF='$url' target='_blank'>$sTitle</A><BR/>"
                 .SEEDStd_StrNBSP('',5)
                 ."<SPAN style='font-size:9pt'><A HREF='$url' target='_blank'>${ra['titlePlain']}</A></SPAN></P>";
        }

        $s .= "</DIV></TD>";
*/
        $s .= "</TR></TABLE>";

    } else {
        // $p_seedcat should be a _key or doc_code in doclib_document where doc_type=='seedcat'

        if( ($kfr = getDocLibKFRC( $kfdb, is_numeric($p_seedcat) ? "_key='$p_seedcat'" : "doc_code='$p_seedcat'" )) ) {
            $kfr->CursorFetch();
            if( $kfr->value('doc_type') == 'seedcat' ) {
                $dirSC = "i/files/seedcat/".$kfr->value('doc_code')."/";
                //$dirreal = realpath( dirname($_SERVER['SCRIPT_FILENAME']))."/".SITEROOT.$dirSC;
                //$dirurl = dirname(Site_path_self()).$dirSC;
                $dirreal = SITEROOT_REALDIR.$dirSC;
                $dirurl = SITEROOT_URL.$dirSC;
                $nameCover = 'cover.png';
                if( !file_exists( $dirreal.$nameCover ) ) {
                    $nameCover = 'cover.jpg';
                    if( !file_exists( $dirreal.$nameCover ) ) {
                        $nameCover = "";
                    }
                }

                $s = "<TABLE border='0'><TR><TD valign='center'>"
                    //."<H2>Classic Canadian Seed Catalogues</H2>"
                    ."<H3 style='margin-left:3em'>".$kfr->value('title')."</H3></TD>"
                    ."<TD>".SEEDStd_StrNBSP("",20)."</TD>"
                    ."<TD valign='center'><P><A HREF='".Site_path_self()."?seedcat=index'>Back to Index</A></P></TD></TR></TABLE>"
                    ."<TABLE border='0' cellspacing='0' cellpadding='30'><TR><TD valign='top'>";
                if( $nameCover ) {
                    $nameCoverPdf = 'cover.pdf';  // assuming
                    $s .= "<DIV style='margin-left:20px;'><A HREF='".$dirurl.$nameCoverPdf."' target='_blank'>"
                         ."<IMG src='".$dirurl.$nameCover."' height='200'/></A>"
                         ."</DIV>";
                }
                $s .= "&nbsp;</TD><TD valign='top'>";

                $oFile = new SEEDFile();
                $oFile->Traverse( $dirreal, array('eFetch'=>'FILE', 'bRecurse'=>false) );

                $raPagesN = array();
                $raPagesOther = array();
                foreach( $oFile->GetTraverseItems() as $ra ) {
                    if( is_numeric(substr($ra[1],0,1)) ) {
                        $raPagesN[] = $ra[1];
                    } else if( !in_array( $ra[1], array('cover.pdf', 'cover back.pdf', 'cover inside.pdf', 'cover back inside.pdf') ) ) {
                        $raPagesOther[] = $ra[1];
                    }
                }

                sort( $raPagesN );
                sort( $raPagesOther );

                $s .= "<DIV style=''> Pages: <BR/><DIV style='margin-left:2em'>";
                foreach( $raPagesN as $fname ) {
                    $s .= "<SPAN style='width:7em;float:left;'><A HREF='".$dirurl.$fname."' target='_blank'>$fname</A></SPAN> ";
                }
                $s .= "</DIV></DIV>";


                $s .= "</TD></TR></TABLE>";
            }
        }
    }

    $s .= "<BR/><BR/><BR/>"
         ."<DIV style='font-size:9pt; border:1px solid #888;background-color:#eee; margin-left:3em; padding:0 1em;'>"
         //."<P>Our thanks to the Royal Botanical Gardens for their generous assistance.</P>"
         //."<P>This work was carried out with the aid of a grant from the "
         //."<A HREF='http://www.idrc.ca' target='_blank'>International Development Research Centre, Ottawa, Canada</A></P>"
         ."</DIV>";

    return( $s );
}


function getDocLibKFRC( $kfdb, $cond, $raKFRCParms = array() )
{
    $kfreldef = array( "Tables" => array(
                       array( "Table" => "doclib_document",
                              "Type"  => "Base",
                              "Fields" => "Auto" ) ) );
    $kfrel = new KeyFrameRelation( $kfdb, $kfreldef, 0 );
    $kfrc = $kfrel->CreateRecordCursor( $cond, $raKFRCParms );
    return( $kfrc );
}


// *** We probably want to implement this with docrep-sfile

define("SEED_DOCLIB_DOCUMENT",
"
CREATE TABLE IF NOT EXISTS doclib_document (
        _key        INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT,
        _created    DATETIME,
        _created_by INTEGER,
        _updated    DATETIME,
        _updated_by INTEGER,
        _status     INTEGER DEFAULT 0,

    doc_code     VARCHAR(200) NOT NULL DEFAULT '',
    doc_type     VARCHAR(200) NOT NULL DEFAULT '',
    title        VARCHAR(200) NOT NULL DEFAULT '',
    subtitle     VARCHAR(200) NOT NULL DEFAULT '',
    lang         VARCHAR(10) NOT NULL DEFAULT '',
    author       VARCHAR(200) NOT NULL DEFAULT '',
    publisher    VARCHAR(200) NOT NULL DEFAULT '',
    pub_date     VARCHAR(200) NOT NULL DEFAULT '',
    pub_location VARCHAR(200) NOT NULL DEFAULT '',
    pub_year     INTEGER NOT NULL DEFAULT 0,
    hardcopy_loc VARCHAR(200) NOT NULL DEFAULT '',
    n_pages      INTEGER NOT NULL DEFAULT 0,
    scan_dpi     INTEGER NOT NULL DEFAULT 0,

    notes       TEXT
);
"
);

?>
