<?php

/* DocRepImg
 *
 * Copyright 2012-2018 Seeds of Diversity Canada
 *
 * Show and manage files stored in the SFile repository
 */

include_once( SEEDCORE."SEEDImgMan.php" );
include_once( STDINC."DocRep/DocRepDB.php" );


class DocRepImgMan extends SEEDImgMan
{
    public  $oDocRepDB;
    private $raParms;

    function __construct( DocRepDB $oDocRepDB, $raParms = array() )
    {
        $this->oDocRepDB = $oDocRepDB;
        $this->raParms = $raParms;
        parent::__construct();
    }

    function K2Img( $k )
    {
        $sName = $this->oDocRepDB->GetDocName( $k );
        // remove the prefix
        return( $sName );
    }

    function Img2K( $img )
    {
        return( $this->oDocRepDB->kfdb->Query1(
                    "SELECT DR._key FROM docrep_docs DR,docrep_docdata DD WHERE "
                   ." DD.fk_docrep_docs=DR._key AND"
                   ." DR.maxVer=DD.ver AND"
                   ." DD.src='SFILE' AND"
                   ." DD.sfile_name = '".addslashes($img)."'" ) );
    }

    function Img2Filename( $img )       { return( $this->oDocRepDB->GetSFileFilename($img) ); }
    function Filename2Img( $filename )  { return( substr( $filename, strlen( $this->oDocRepDB->GetSFileFilename("") ) ) ); }
    function Img2Url( $img )            { return( "OVERRIDE" ); }
    function RenameImage( $k, $newImg ) { return( $this->oDocRepDB->InsertSFile( $k, 'DOC', $newImg ) ); }
    function DeleteImage( $k )
    {
        $ok = false;
        if( $this->oDocRepDB->TrashDoc( $k ) ) {
            $this->oDocRepDB->TrashPurgeDoc( $k );
            $ok = true;
        }
        return( $ok );
    }

    function AddImage( $newImg )
    {
        $ok = false;
        $ra['dr_name'] = $newImg; //'sfile/'.$newImg;
        $ra['dr_permclass'] = SEEDStd_ArraySmartVal( $this->raParms, 'permclass', array( 0 ) );
        //$ra['dr_posUnder'] =
        //$ra['dr_posAfter'] =
        if( ($oDoc = new DocRepDoc_Insert($this->oDocRepDB)) ) {
             $ok = $oDoc->InsertSFile( $newImg, "", $ra );
        }
        return( $ok );
    }

    function GetAllImg( $dir )
    {
        $raImg = array();
        if( !($dbc = $this->oDocRepDB->kfdb->CursorOpen(
                    "SELECT DR._key as kDoc, DD.sfile_name as sfile_name FROM docrep_docs DR,docrep_docdata DD WHERE"
                   ." DD.fk_docrep_docs=DR._key AND"
                   ." DR.maxVer=DD.ver AND"
                   ." DD.src='SFILE'"
                   .($dir ? (" AND DD.sfile_name LIKE '".addslashes($dir)."/%'") : "") ) ) )
        {
            die( "<P>Cannot open docrep</P>" );
        }
        while( $ra = $this->oDocRepDB->kfdb->CursorFetch( $dbc ) ) {
            $raImg[] = $ra['sfile_name'];
        }
        $this->oDocRepDB->kfdb->CursorClose( $dbc );
        return( $raImg );
    }

}


class DocRepSFileClean
{
    private $oIM;
    private $dir = "";

    function __construct( DocRepImgMan $oIM )
    {
        $this->oIM = $oIM;
    }

    function drawImgName( $k, $img )
    {
        return( "<b>$img <a href='show.php?id=$k' target='_blank'>($k)</a></b>" );
    }

    function drawImgPicture( $img )
    {
        //$url = $this->oIM->Img2Url( $img );
        //$url = $this->oIM->oDocRepDB->GetSFileFilename($img);var_dump($img);
        return( "<a HREF='url' target='img'><IMG src='".SITEROOT."d?n=$img' height='60'></a>" );
    }

    function drawAutoMatchLine( $oldFile, $newFile )
    {
        $imgOld = $this->oIM->Filename2Img( $oldFile );
        $imgNew = $this->oIM->Filename2Img( $newFile );
        $kOld = $this->oIM->Img2K( $imgOld );

        return( "<TR valign='center'><TD class='box'>"
                ."<FORM method='post'>"
                .SEEDForm_Hidden('sim_k', $kOld)
                .SEEDForm_Hidden('sim_img1',$imgNew)
                ."<INPUT type='submit' name='sim_action' value='Match'/></FORM></TD>"
                ."<TD class='box'>".$this->drawImgName( $kOld, $imgOld )."</TD>"
                ."<TD class='box'>&nbsp;&nbsp;&nbsp; to &nbsp;&nbsp;&nbsp;<B>$imgNew</B></TD>"
                ."<TD align='center'>".$this->drawImgPicture( $imgNew )."</TD></TR>" );
    }

    function ShowMissingFiles( $bMatchMode = false )
    /***********************************************
     Report all files that are listed in the database but not found in the directory
     Allow them to be renamed/deleted in the database.

     In bMatchMode, draw radio buttons beside each missing file (there is a form surrounding this part of the page)
     else, draw a Rename/Delete form beside each missing file
     */
    {
        $s = "<H3>Missing Files:</H3>"
            ."<TABLE border='0'>";
        foreach( $this->oIM->raMissingFiles as $f ) {
            $img = $this->oIM->Filename2Img( $f );
            $k = $this->oIM->Img2K( $img );

            $s .= "<TR><TD valign='top'>";
            $label = $this->drawImgName( $k, $img );
            if( $bMatchMode ) {
                $s .= SEEDForm_Radio( 'sim_k', $k, 0, "").$label;   // sim_img1 is in the Extra Files section
            } else {
                $s .= $label;
            }
            $s .= "</TD><TD valign='top'>";
            if( !$bMatchMode ) {
                $s .= "<FORM method='post'>"
                    .SEEDForm_Hidden( 'sim_k', $k )
                    .SEEDForm_Text( 'sim_img1', $img, "", 25 )
                    ." <INPUT type='submit' name='sim_action' value='Rename'/>"
                    ." <INPUT type='submit' name='sim_action' value='Delete'/></FORM>";
            }
            $s .= "</TD></TR>\n";
        }

        $s .= "</TABLE>"
            ."<P>{$this->oIM->nMissingTested} records tested</P>"
            ."<P>{$this->oIM->nMissingFound} files missing</P>";

        return( $s );
    }

    function ShowExtraFiles( $dir, $bMatchMode = false )
    /***************************************************
     Report all files found in the directory that are not listed in the database.
     Allow them to be added.

     In bMatchMode, draw radio buttons beside each extra file (there is a form surrounding this part of the page)
     else, draw an Add form beside each extra file
     */
    {
        $s = "<H3>Extra Files:</H3>"
            ."<TABLE border='0'>";
        foreach( $this->oIM->raExtraFiles as $f ) {
            $img = $this->oIM->Filename2Img( $f );
            $s .= "<TR>";
            if( $bMatchMode ) {
                $s .= "<TD valign='center'>".SEEDForm_Radio( 'sim_img1', $img, '', "" )."</TD>";    // sim_k is in the Missing Files section
            }
            $s .= "<TD valign='center' align='center'>"
                .$this->drawImgPicture( $img )
                ."</TD><TD valign='center'>"
                ."<B>$img</B></TD>";
            if( !$bMatchMode ) {
                $s .= "<TD valign='center'><FORM method='post'>".SEEDForm_Hidden( "sim_img1", $img )."<INPUT type='submit' name='sim_action' value='Add'></FORM></TD>";
            }
            $s .= "</TR>\n";
        }
        $s .= "</TABLE>"
            ."<P>{$this->oIM->nExtraTested} records tested</P>"
            ."<P>{$this->oIM->nExtraFound} extra files found</P>";
        if( $this->oIM->nExtraFound && !$bMatchMode ) {
            $s .= "<FORM method='post'>"
                .SEEDForm_HiddenStr( "sim_dir", $dir ? $dir : "." )
                ."<P><INPUT type='submit' name='sim_action' value='Add All'></P></FORM>\n";
        }
        return( $s );
    }


    function Go( $dir = "" )
    {
        $s = "";

        $this->dir = $dir;

        $s .= $this->oIM->Update();

//         $mode = $this->sess->SmartGPC( 'drsfc_mode',
//                     array( "", "Check for Missing and Extra Files",
//                                "Check for Missing and Extra Files with Match",
//                                "Check Image Size" ) );
        $mode = "Check for Missing and Extra Files";// with Match";

        switch( $mode ) {
            case "Check for Missing and Extra Files with Match":  $s .= $this->CheckMissingAndExtraFilesWithMatch();    break;
            case "Check for Missing and Extra Files":             $s .= $this->CheckMissingAndExtraFiles();             break;
            case "Check Image Size":                              $s .= $this->CheckImageSize();                        break;
            default: break;
        }

        $s .= "<BR/>"
             ."<FORM method='post'><INPUT type='submit' name='drsfc_mode' value='Check for Missing and Extra Files with Match'/></FORM>"
             ."<FORM method='post'><INPUT type='submit' name='drsfc_mode' value='Check for Missing and Extra Files'/></FORM>"
             ."<FORM method='post'><INPUT type='submit' name='drsfc_mode' value='Check Image Size'/></FORM>";

        return( $s );
    }

    function CheckMissingAndExtraFilesWithMatch( $bReload = true )  // TODO: don't need to reload the cache when an operation was just performed
    {
        $s = "<H3>Matches for sfile/{$this->dir}</H3>"
            ."<DIV class='mainbox'>";

        $this->oIM->getMissingAndExtraFiles( $this->dir, $bReload );

        // look for a file name that is a truncation of a db name, not counting the file extension   i.e. a suffix was removed in the file system name
        $s .= "<H3>Suffixes removed</H3>"
             ."<TABLE border='0' bgcolor='#ddd'>";
        foreach( $this->oIM->raExtraFiles as $f ) {
            // trim the file extension
            if( ($i = strrpos( $f, '.' )) !== false ) {
                $f1 = substr( $f, 0, $i );
            }
            foreach( $this->oIM->raMissingFiles as $f2 ) {
                if( substr( $f2, 0, strlen($f1) ) == $f1 ) {
                    $s .= $this->drawAutoMatchLine( $f2, $f );
                }
            }
        }
        $s .= "</TABLE>";

        // look for a db name that is a truncation of a file name, not counting the file extension   i.e. a suffix was added in the file system name
        $s .= "<H3>Suffixes added</H3>"
             ."<TABLE border='0' bgcolor='#ddd'>";
        foreach( $this->oIM->raMissingFiles as $f ) {
            // trim the file extension
            if( ($i = strrpos( $f, '.' )) !== false ) {
                $f1 = substr( $f, 0, $i );
            }
            foreach( $this->oIM->raExtraFiles as $f2 ) {
                if( substr( $f2, 0, strlen($f1) ) == $f1 ) {
                    $s .= $this->drawAutoMatchLine( $f, $f2 );
                }
            }
        }
        $s .= "</TABLE>";

        // show all potential matches
        $s .= "<H3>All Possible Matches</H3>"
           ."<FORM method='post'>"
           ."<TABLE border='1'><TR valign='top'>"
           ."<TD class='box'>".$this->ShowMissingFiles( true )."</TD>"
           ."<TD style='padding-top:12em;'><INPUT type='submit' name='sim_action' value='Match'/></TD>"
           ."<TD class='box' valign='top'>".$this->ShowExtraFiles( $this->dir, true )."</TD>"
           ."</TR></TABLE></FORM>";

        $s .= "</DIV>";

        return( $s );
    }

    function CheckMissingAndExtraFiles( $bReload = true )
    {
        $this->oIM->getMissingAndExtraFiles( $this->dir, $bReload );

        $s = "<H3>Missing and Extra files in sfile/{$this->dir}</H3>"
            ."<TABLE border='1' bgcolor='#ddd'><TR valign='top'>"
            ."<TD class='box'>".$this->ShowMissingFiles( false )."</TD>"
            ."<TD class='box'>".$this->ShowExtraFiles( $this->dir, false )."</TD>"
            ."</TR></TABLE>";
        return( $s );
    }

    function CheckImageSize()
    {
        $s = "";

        return( $s );
    }
}

?>
