<?php

/* SEEDEditor
 *
 * Copyright (c) 2007-2015 Seeds of Diversity Canada
 *
 * Interface to onscreen text editors
 *
 * To use CKEditor must define CKEDITOR_DIR.
 * To use TinyMCE must define TINYMCE_DIR.
 */


class SEEDEditor
{
    var $eType;
    var $sFieldName = "";
    var $sContent = "";

    private $bJS = false;

    function __construct( $eType )
    {
        $this->eType = SEEDStd_SmartVal( $eType, array('Plain', 'TinyMCE', 'TinyMCE-4', 'CKEditor') );
        switch( $this->eType ) {
            case 'TinyMCE':
                if( !defined("TINYMCE_DIR") ) {
                    $this->eType = NULL;
                }
                break;
            case 'TinyMCE-4':
                if( !defined("TINYMCE_4_DIR") ) {
                    $this->eType = NULL;
                }
                break;
            case 'CKEditor':
                if( defined("CKEDITOR_DIR") ) {
                    include_once( CKEDITOR_DIR."ckeditor.php" );
                } else {
                    $this->eType = NULL;
                }
                break;
            case 'Plain':
            default:
                break;
        }
    }

    function SetFieldName( $sName )  { $this->sFieldName = $sName; }
    function SetContent( $sContent ) { $this->sContent = $sContent; }

    function Editor( $raParms )
    /**************************
        $raParms:   width_px      = pixel width of text window
                    width_percent = percentage width of text window
                    width_css     = css value for width of text window
                    height_px     = pixel height of text window
                    height_css    = css value for height of text window
                    controls      = simple | advanced | Joomla ... add others e.g. extended, professional, wizard
     */
    {
        $s = "";

        if( isset($raParms['width_css']) ) {
            $sWidth = $raParms['width_css'];
        } else if( isset($raParms['width_px']) ) {
            $sWidth = $raParms['width_px']."px";
        } else if( isset($raParms['width_percent']) ) {
            $sWidth = $raParms['width_percent']."%";
        } else {
            $sWidth = '100%';
        }

        if( isset($raParms['height_css']) && $this->eType != "CKEditor" ) {  // this one might not work with CKEditor?
            $sHeight = $raParms['height_css'];
        } else if( isset($raParms['height_px']) ) {
            $h = $raParms['height_px'];
            $sHeight = $h."px";
        } else {
            $h = 300;
            $sHeight = $h."px";
        }

        if( $this->eType == "TinyMCE" ) {
            $eControls = SEEDStd_SmartVal( @$raParms['controls'], array('simple', 'advanced', 'Joomla') );

            if( $eControls == 'Joomla' ) {
                // looks like the default TinyMCE config in Joomla - familiar to people who use that
                $sTinyControls = 'theme : "advanced",'
                                .'width : "200",'
                                .'plugins : '
                                        .'"safari,spellchecker,pagebreak,style,layer,table,save,advhr,advimage,advlink,'
                                        .'emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,'
                                        .'contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,'
                                        .'xhtmlxtras,template",'
                                .'theme_advanced_buttons1 : '
                                        .'"save,|,bold,italic,underline,strikethrough,|,'
                                        .'justifyleft,justifycenter,justifyright,justifyfull,|,'
                                        .'styleselect,formatselect,|,iespell,fullscreen,print,help,|,spellchecker,cleanup,preview,code",'
                                .'theme_advanced_buttons2 : '
                                        .'"bullist,numlist,|,outdent,indent,blockquote,|,link,unlink,anchor,image,|,advhr,|,sub,sup,|,charmap,|,tablecontrols",'
                                .'theme_advanced_buttons3 : '
                                        .'"undo,redo,|,cut,copy,paste,pastetext,pasteword,|,search,replace,|,'
                                        .'removeformat,visualaid,",'
                                .'theme_advanced_toolbar_location : "top",'
                                .'theme_advanced_toolbar_align : "left",'
                                .'theme_advanced_statusbar_location : "bottom",'
                                .'theme_advanced_resizing : true,'
                                // these prevent references to https://seeds.ca/d?foo being replaced by ../d?foo which looks great during edit but very bad in an email  
                                .'relative_urls: false,'
                                .'convert_urls: false,'
                                .'remove_script_host : false,'
                                ;
            } else if( $eControls == 'advanced' ) {
                $sTinyControls = 'theme : "advanced",'
                                .'width : "200",'
                                .'plugins : '
                                        .'"safari,spellchecker,pagebreak,style,layer,table,save,advhr,advimage,advlink,'
                                        .'emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,'
                                        .'contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,'
                                        .'xhtmlxtras,template",'
                                .'theme_advanced_buttons1 : '
                                        .'"save,newdocument,|,bold,italic,underline,strikethrough,|,'
                                        .'justifyleft,justifycenter,justifyright,justifyfull,|,bullist,numlist,|,'
                                        .'outdent,indent,blockquote,formatselect,|,iespell,fullscreen,print,help",'
                                .'theme_advanced_buttons2 : '
                                        .'"undo,redo,|,cut,copy,paste,pastetext,pasteword,|,search,replace,|,link,unlink,anchor,image,|,'
                                        .'sub,sup,charmap,nonbreaking,forecolor,backcolor,|,advhr,insertdate,inserttime,preview,|,'
                                        .'tablecontrols",'
                                .'theme_advanced_buttons3 : '
                                        .'"insertlayer,moveforward,movebackward,absolute,|,styleprops,attribs,spellchecker,|,'
                                        .'cite,abbr,acronym,del,ins,|,visualchars,template,blockquote,pagebreak,|,'
                                        .'insertfile,insertimage,media,removeformat,visualaid,cleanup,code",'
                                .'theme_advanced_toolbar_location : "top",'
                                .'theme_advanced_toolbar_align : "left",'
                                .'theme_advanced_statusbar_location : "bottom",'
                                .'theme_advanced_resizing : true,'
                                // these prevent references to https://seeds.ca/d?foo being replaced by ../d?foo which looks great during edit but very bad in an email  
                                .'relative_urls: false,'
                                .'convert_urls: false,'
                                .'remove_script_host : false,'
                                ;
                //plugins : "safari,spellchecker,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,imagemanager,filemanager",
            } else {
                $sTinyControls = 'theme : "simple"';
            }

            if( !$this->bJS )  $s .= "<script type='text/javascript' src='".TINYMCE_DIR."jscripts/tiny_mce/tiny_mce.js'></script>";
            $this->bJS = true;
            $s .= "<script type='text/javascript'>tinyMCE.init({ mode : \"specific_textareas\","
                                                               ."editor_selector : \"SEEDTinyMCE_{$this->sFieldName}\","
                                                               .$sTinyControls
                                                               ."extended_valid_elements : \"excerpt,excerpt_start,excerpt_end,ref\""
                                                               ." });"
                 ."</script>"
                 ."<TEXTAREA id='{$this->sFieldName}' name='{$this->sFieldName}' style='width:$sWidth' class='SEEDTinyMCE_{$this->sFieldName}'>"
                 .SEEDStd_HSC($this->sContent)."</TEXTAREA>";

        } else if( $this->eType == "TinyMCE-4" ) {
            $s .= $this->TinyMCE_4( $sWidth );

        } else if( $this->eType == "CKEditor" ) {
            $oCKeditor = new CKeditor($this->sFieldName);
            $oCKeditor->BasePath = CKEDITOR_DIR;
            $oCKeditor->Value = $this->sContent;
            $oCKeditor->Height = $h;
            $oCKeditor->Width  = $sWidth;
// need to use output buffering to return the generated Javascript
            $oCKeditor->Create();
        } else {
            $s .= $this->textarea( $sWidth );
            //$s .= "<TEXTAREA NAME='{$this->sFieldName}' style='width:$sWidth;height:$sHeight' wrap='soft'>"
            //      .SEEDStd_HSC($this->sContent)."</TEXTAREA>";
        }

        return( $s );
    }


    private function TinyMCE_4( $sWidth )
    {
        $s = "";

//  selector: "textarea#elm1",
        $sControls = "
            theme: 'modern',
        //  width: 300,
        //  height: 300,
            plugins: [
              'advlist autolink link image lists charmap print preview hr anchor pagebreak spellchecker',
              'searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking',
              'save table contextmenu directionality emoticons template paste textcolor'
            ],
            content_css: 'css/content.css',
            toolbar: 'undo redo | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image | print preview media fullpage | forecolor backcolor',
            style_formats: [
                {title: 'Bold text', inline: 'b'},
                {title: 'Red text', inline: 'span', styles: {color: '#ff0000'}},
              //  {title: 'Red header', block: 'h1', styles: {color: '#ff0000'}},
              //  {title: 'Example 1', inline: 'span', classes: 'example1'},
              //  {title: 'Example 2', inline: 'span', classes: 'example2'},
              //  {title: 'Table styles'},
              //  {title: 'Table row 1', selector: 'tr', classes: 'tablerow1'}
            ],
            removed_menuitems: 'newdocument',
        ";

        if( !$this->bJS )  $s .= "<script type='text/javascript' src='".TINYMCE_4_DIR."js/tinymce/tinymce.min.js'></script>";
        $this->bJS = true;
        $s .= "<script type='text/javascript'>tinyMCE.init({ selector : \"textarea#{$this->sFieldName}\","
                                                           .$sControls
                                                           ."extended_valid_elements : \"excerpt,excerpt_start,excerpt_end,ref\""
                                                           ." });"
             ."</script>"
             .$this->textarea( $sWidth);

        return( $s );
    }

    private function textarea( $sWidth )
    {
        return( "<textarea id='{$this->sFieldName}' name='{$this->sFieldName}' style='width:$sWidth' class=''>"
               .SEEDStd_HSC($this->sContent)."</textarea>" );
    }
}

?>
