<?php

/* DocRepAppCommon
 *
 * Copyright (c) 2007 Seeds of Diversity Canada
 *
 * Helpers for DocRep reader and writer applications
 */

include_once( "DocRep.php" );   // DocRep_Hash2Key

function DocRep_GetDocGPC( $prefix = '', $hashSeed = '', $kfdb = NULL )
/**********************************************************************
    k = $hashSeed ? a hashed document key : an integer docrep _key
    n = a docrep name
 */
{
    $kDoc = 0;

    if( ($k = SEEDSafeGPC_GetStrPlain( $prefix."k" )) ) {
        if( empty($hashSeed) ) {
            /* The application doesn't require k to be hashed
             */
            $kDoc = intval($k);
        } else {
            /* k should be a hashed docrep key
             */
            $kDoc = DocRep_Hash2Key( $k, $hashSeed );
        }
    } else if( ($n = SEEDSafeGPC_GetStrPlain( $prefix."n" )) ) {
        if( $kfdb ) {
// assuming that the caller will check DocRep permissions to this doc
            $kDoc = intval($kfdb->Query1( "SELECT _key FROM docrep_docs WHERE name='".addslashes($n)."'" ));
        }
    } else if( ($t = SEEDSafeGPC_GetStrPlain( $prefix."t" )) ) {
        list($kDoc,$t) = DocRep_HashTrack2Key( $t, $hashSeed );
        // save the tracking key
        if( $t ) {
//            $oMT = new SEEDMetaTable_TablesLite( $kfdb );
//            if( ($kTable = $oMT->OpenTable( 'DRTrackEmail' )) ) {
//                $oMT->PutRow( $kTable, 0, array(), $kDoc, $t );
//            }
        }
    }
    return( $kDoc );
}


function DocRepApp_GetTemplateAndVars( &$oDocRepDB, $kDoc, $flag, $raVarsCaller, $raDocAncestors = NULL, $kRoot = 0, $docid_template_override = 0 )
/**************************************************************************************************************************************************
    Get the template (if any) and all variables for the given doc.

    The search order for variables is doc, doc ancestors (in upward order), template, raVarsCaller
    i.e. doc's variables override its parents' etc, which override the template, which override the caller's vars

    Catch-22: The template can be defined in the doc/ancestor variables, and those variables override the template's
              So first list the doc/ancestor vars, overriding appropriately.
              Then see if a template is defined there.
              Then get the template's variables, and override them with the doc/ancestors'

    raVarsCaller   - vars provided by the caller -- these are overridden by doc/ancestor/template vars
    raDocAncestors - provide if available, otherwise we'll look it up
    kRoot          - the highest point in the tree to include ancestor vars (0 means the whole ancestor list)
    docid_template_override - name or number of the template (overrides all other templates)
 */
{
    $bDebug = false;

    if( !$raDocAncestors ) {
        $raDocAncestors = $oDocRepDB->GetDocAncestors( $kDoc );
    }

    // Get vars from doc and its ancestors, from the docroot down to the current doc.  doc vars override parent vars
    $raVarsDoc = array();
    $bRootFound = ($kRoot == 0);  // if kRoot==0 collect vars from the whole ancestor list, else just from kRoot down
    for( $n = count($raDocAncestors)-1; $n >= 0; --$n ) {
        if( !$bRootFound ) {
            if( $raDocAncestors[$n] != $kRoot ) continue;
            $bRootFound = true;
        }
        if( ($raDI = $oDocRepDB->GetDocInfo( $raDocAncestors[$n], $flag )) ) {
            foreach( $raDI['Data_metadata'] as $k => $v ) {
                $raVarsDoc[$k] = $v;
            }
        }
    }
    if( $bDebug ) {
        echo "<H3>DocRepApp_GetTemplateAndVars:vars from doc and ancestors</H3>";
        var_dump( $raVarsDoc );
        echo "<H3>DocRepApp_GetTemplateAndVars:looking for template</H3>";
    }

    // Get the template from 1) non-overridable parm, 2) author-controlled variable, 3) caller parm
    if( ($s = $docid_template_override) ) {
        if( $bDebug ) { echo "<P>Template $s found in parm:docid_template_override</P>"; }
    } else if( ($s = @$raVarsDoc['dr_template']) ) {
        if( $bDebug ) { echo "<P>Template $s found in doc/ancestor var:dr_template</P>"; }
    } else if( ($s = @$raVarsCaller['dr_template']) ) {
        if( $bDebug ) { echo "<P>Template $s found in coded var:dr_template</P>"; }
    }
    $kTemplate = $oDocRepDB->GetDocFromName($s);
    if( $bDebug ) { echo "<P>Template $s is docid={$kTemplate}</P>"; }

    // Get template vars
    $raVarsTmpl = array();
    if( $kTemplate && ($raDI = $oDocRepDB->GetDocInfo( $kTemplate, $flag )) ) {
        foreach( $raDI['Data_metadata'] as $k => $v ) {
            $raVarsTmpl[$k] = $v;
        }
    }

    // Combine the vars: raVarsCaller + template vars + ancestor+doc vars ; each overrides the previous
    $raVarsOutput = array_merge( $raVarsCaller, $raVarsTmpl, $raVarsDoc );
    if( $bDebug ) {
        echo "<H3>DocRepApp_GetTemplateAndVars:vars from php code, template, doc & ancestors, and the overlay of those in that order</H3>"
            ."<PRE>";
        var_dump( $raVarsCaller, $raVarsTmpl, $raVarsDoc, $raVarsOutput );
        echo "</PRE>";
    }

    return( array( $kTemplate, $raVarsOutput ) );
}

?>
