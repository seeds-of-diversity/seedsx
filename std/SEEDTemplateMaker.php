<?php

/* SEEDTemplateMaker
 *
 * Copyright 2015 Seeds of Diversity Canada
 *
 * Simplify the creation of SEEDTemplates by standardizing typical parameters to SEEDTemplate_Generator
 */
include_once( "SEEDTemplate.php" );


function SEEDTemplateMaker( $raParms )
/*************************************
    raParms:
        sTemplates = string or array of strings each containing one or more named templates
        fTemplates = filename or array of filenames each containing one or more named templates
        raTemplates = array of named templates

        raResolvers = array of tag resolvers, to which the standard resolvers are appended

        oForm    = use this SEEDForm's ResolveTag()
        sFormCid = instantiate a SEEDForm using this cid and use its ResolveTag()
        bFormRequirePrefix = the resolver requires 'FormText' instead of 'Text', etc

        oLocal = use this SEEDLocal's ResolveTag()
        bLocalRequirePrefix = the resolver requires 'LocalLang' instead of 'Lang', etc

        bEnableBasicResolver = enable the basic resolver (default=true)
        raBasicResolverParms = parms for basic resolver

        vars = array of variables globally available to all templates (and unaffected by local overrides and SetVar because of scoping)
 */
{
    $raGen = array();

    /* Templates can be defined in strings containing %% tmpl names, files with the same format as strings, or arrays of named templates.
     * All of these can be given as single items or arrays of items.
     * e.g. sTemplates can be a string, or an array of strings
     *      fTemplates can be a filename, or an array of filenames
     *      raTemplates can be an array of templates, or an array of
     */
    if( @$raParms['sTemplates'] )  $raGen['sTemplates'] = $raParms['sTemplates'];
    if( @$raParms['fTemplates'] )  $raGen['fTemplates'] = $raParms['fTemplates'];
    if( @$raParms['raTemplates'] )  $raGen['raTemplates'] = $raParms['raTemplates'];

    /* Tag resolvers
     */
    $tagParms = array();
    $tagParms['raResolvers'] = isset($raParms['raResolvers']) ? $raParms['raResolvers'] : array();

// if( isset($raParms['EnableSEEDForm']) ) {
//     if( @$raParms['EnableSEEDForm']['oForm'] ) {
//


    $oForm = null;
    if( @$raParms['oForm'] ) {
        $oForm = $raParms['oForm'];
    } else if( @$raParms['sFormCid'] ) {
        $oForm = new SEEDForm( $raParms['sFormCid'] );
    }
    if( $oForm ) {
        $tagParms['raResolvers'][] = array( 'fn'=>array($oForm,'ResolveTag'),
                                            'raParms'=>array('bRequireFormPrefix'=>(@$raParms['bFormRequirePrefix']?true:false)) );
    }

    if( @$raParms['oLocal'] ) {
        $tagParms['raResolvers'][] = array( 'fn'=>array($raParms['oLocal'],'ResolveTag'),
                                            'raParms'=>array('bRequireLocalPrefix'=>(@$raParms['bLocalRequirePrefix']?true:false)) );
    }

    if( !isset($raParms['bEnableBasicResolver']) || $raParms['bEnableBasicResolver'] ) {
        $tagParms['EnableBasicResolver'] = isset($raParms['raBasicResolverParms']) ? $raParms['raBasicResolverParms'] : array();
    }

    $raGen['SEEDTagParms'] = $tagParms;
    $raGen['vars'] = isset($raParms['raVars']) ? $raParms['raVars'] : array();

    $raGen['SEEDTagParms']['bEatUnknownTags'] = !@$raParms['kluge_dontEatMyTag'];
    $o = new SEEDTemplate_Generator( $raGen );
    $oTmpl = $o->MakeSEEDTemplate();

    return( $oTmpl );
}

?>
