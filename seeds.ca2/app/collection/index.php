<?php

/* Collection Management for micro-seedbanks
 *
 * Copyright (c) 2014-2017 Seeds of Diversity Canada
 *
 */

if( !defined("SITEROOT") )  define("SITEROOT", "../../");
include_once( SITEROOT."site.php" );
include_once( STDINC."SEEDFormUI.php" );
include_once( SEEDCOMMON."siteStart.php" );

include_once( SEEDCOMMON."console/console01kfui.php" );
include_once( SEEDCOMMON."sl/sl_db.php" );

include_once( "seedcollection.php" );
include_once( "controls.php" );


// Access to the application is given if any of the tabs are accessible
// Inaccessible tabs are Ghosted
$raPerms = array( array('SLCollection' => 'W'), array('SL' => 'A') );   // SLCollection:W or SL:A
list($kfdb, $sess, $lang) = SiteStartSessionAccountNoUI( $raPerms );

//var_dump($_REQUEST);
//$kfdb->SetDebug(1);


$raConsoleParms = array(
    'HEADER' => "My Seed Collection",
    'CONSOLE_NAME' => "SLCollection",

    //'TABSETS' => array( "TFmain" => array( 'tabs' => array( 'Collection' => array( 'label' => "Collection" ),
    //                  ))),
    'css_files' => array( "seedcollection.css" ),
    'script_files' => array( W_ROOT."std/js/SEEDStd.js", W_ROOT."std/js/SEEDFormUI.js",
                             W_ROOT."std/js/SEEDStd.js", W_ROOT."std/js/SEEDPopover.js"),

    'bBootstrap' => true,
    'bLogo' => true
);
$oC = new MyConsole( $kfdb, $sess, $raConsoleParms );
$oSCA = new SLCollectionAdmin( $oC, $kfdb, $sess );

$raCollW = $oSCA->oColl->RACollWritable();
$raCollVisible = $oSCA->oColl->GetCollectionsVisibleByMe();

// Default to the collection screen first; only use the collection screen if there are no visible collections
$pScreen = count($raCollVisible) ? $oSCA->oSVA->SmartGPC( 'pScreen', array('collections','seeds') )
                                 : 'collections';


$oC->SetConfig( array( 'HEADER_LINKS' => array( $pScreen == 'collections'
                                 ? array( 'label'=>'My Seeds',       'href'=>"{$_SERVER['PHP_SELF']}?pScreen=seeds" )
                                 : array( 'label'=>'My Collections', 'href'=>"{$_SERVER['PHP_SELF']}?pScreen=collections" )
)));

if( $pScreen == 'collections' ) {
    $oSCA->Init( 0 );
    $s = $oSCA->oColl->ShowCollections();

} else {
    $oCollectionSelector = new CollectionSelector( $oSCA );
    $oSCA->Init( $oCollectionSelector->GetKey() );
    $oC->SetConfig(
            array( 'HEADER_TAIL' => $oCollectionSelector->GetSelectCtrl() )
    );

    $s = "<br/>".$oSCA->ContentDraw();
}


header( "Content-type: text/html; charset=ISO-8859-1");    // this should be on all pages so accented chars look right (on Linux anyway)

echo $oC->DrawConsole( $s );

?>
<script type='text/javascript'>

SEEDFormUIParms['urlQ'] = '<?php echo Site_UrlQ(); ?>';

// This is here because drawList makes a list with GET parms, which persist on the browser address bar. When you make an update that removes something from the
// list the iCurr gets messed up and the list has to be reset. But the GET parms are still on the address (even after the update form is posted) so the list
// responds to those instead of the the programmatic parms
var clean_uri = location.protocol + "//" + location.host + location.pathname;
window.history.replaceState({}, document.title, clean_uri);

<?php
if( true ) {
    ?>
    $(document).ready( function() {
        <?php if( $oSCA->sSPop ) { echo "SEEDPopoverShow = '".$oSCA->sSPop."';"; } ?>
        SEEDPopover();
    });
    <?php
}
?>


SEEDPopover_Def = {
	collection_none:
	    { placement:'right',
	      title:   "Start Your Seed Collection",
		  content: "This is where you'll administrate your seed collection. Create it now by clicking the 'Add' button. (You can make as many as you want)"
		},
	collection_other:
	    { placement:'right',
	      title:   "Someone Else's Collection",
		  content: "Other people can let you see their collections, but you can't make changes."
		},
	collection_mine:
	    { placement:'right',
	      title:   "Your Seed Collection",
		  content: "This shows a summary of your seed collection's options. Click Edit to change them."
		},
	collection_mine_new:
	    { placement:'right',
	      trigger: 'manual',    // so clicking in the form doesn't toggle the popover
		  title:   "Your New Seed Collection",
		  content: "Give your collection a meaningful name. The Lot # Prefix should be a few letters to identify your seed collection among others. Choose who else you want to be able to see your collection. "
		},
	collection_mine_edit:
	    { placement:'right',
		  trigger: 'manual',    // so clicking in the form doesn't toggle the popover
	      title:   "Edit Your Seed Collection",
		  content: "Give your collection a meaningful name. The Lot # Prefix should be a few letters to identify your seed collection among others. Choose who else you want to be able to see your collection. "
        },


};

</script>
<style>
.popover-title { background-color:#5cb85c; color:#fff; font-weight:bold }</style>  <?php // the bootstrap green background ?>
