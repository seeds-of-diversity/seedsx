<?php

/* SEEDTemplate
 *
 * Copyright 2015 Seeds of Diversity Canada
 *
 * A templating engine that loads named templates from arbitrary storage, and applies multiple template processors.
 *
 * SEEDTemplate::$raParms
 *      'processors' => list of template processors, in order of execution
 *                      e.g. array( 'SEEDTag' => array( 'fnExpand', ... ),
 *                                  'H2o'     => array( 'fnExpand', ... ),
 *                                  'Wiki'    => array( 'fnExpand', ... ), ... )
 *
 *      'vars'       => variables referenced by template processors; the store is globally writable, not scoped by child templates
 *                      (if you want scoped local variables in templates, implement that in your processor)
 *
 *      'loader'     => how to get a named template. All processors use the same loader, because the templates can contain any or
 *                      all supported template languages. Our loader is therefore independent of any processor's built-in loader.
 *                      array( 'oLoader' => an object supporting LoadStr(), LoadRA(), Get()
 *                                          if not defined, factory_Loader() is called instead
 *
 *      'sTemplates' => a string (or an array of strings) containing one or more named templates, passed to oLoader->LoadStr().
 *      'fTemplates' => a filename (or an array of filenames) containing one or more named templates, read and passed to oLoader->LoadStr().
 *      'raTemplates'=> an array (NOT an array of arrays) "name1"=>"tmpl","name2"=>"tmpl2", passed to oLoader->LoadRA().
 *                      If neither defined, assume the provided loader already has the templates.
 *                      In all but trivial applications it probably makes sense to provide your own loader derived from SEEDTemplateLoader.
 */

class SEEDTemplate
{
    protected $oDSVars = null;
    protected $oLoader = null;
    protected $raProcessors = array();

    function __construct( $raParms = array(), SEEDDataStore $oDSVars = null )
    {
        $this->raProcessors = $raParms['processors'];   // required

        // use a global shared datastore, or make a local one
        $this->oDSVars = $oDSVars ? $oDSVars : new SEEDDataStore();

        if( @$raParms['vars'] ) $this->SetVars( $raParms['vars'] );

        $this->oLoader = @$raParms['loader']['oLoader'] ? $raParms['loader']['oLoader']
                                                        : $this->factory_Loader();

        if( @$raParms['sTemplates'] ) {
            if( is_array( $raParms['sTemplates'] ) ) {
                foreach( $raParms['sTemplates'] as $sT )  $this->oLoader->LoadStr( $sT );
            } else {
                $this->oLoader->LoadStr( $raParms['sTemplates'] );
            }
        }
        if( @$raParms['raTemplates'] ) {
            if( is_array( $raParms['raTemplates'] ) ) {
                foreach( $raParms['raTemplates'] as $raT )  $this->oLoader->LoadRA( $raT );
            } else {
                $this->oLoader->LoadRA( $raParms['raTemplates'] );
            }
        }
        if( @$raParms['fTemplates'] ) {
            if( is_array( $raParms['fTemplates'] ) ) {
                foreach( $raParms['fTemplates'] as $fT ) {
                    if( ($sTmpl = file_get_contents($fT)) ) {
                        $this->oLoader->LoadStr( $sTmpl );
                    }
                }
            } else {
                if( ($sTmpl = file_get_contents($raParms['fTemplates'])) ) {
                    $this->oLoader->LoadStr( $sTmpl );
                }
            }
        }
    }

    function GetVarsRA()            { return( $this->oDSVars->GetValuesRA() ); }   // only implemented for plain array base class
    function SetVarsRA( $raVars )   { $this->oDSVars->SetValuesRA( $raVars ); }
    function GetVar( $k )           { return( $this->oDSVars->Value($k) ); }
    function SetVar( $k, $v )       { $this->oDSVars->SetValue( $k, $v ); }
    // deprecated for method name consistency
    function SetVars( $raVars )     { $this->SetVarsRA( $raVars ); }

    function ClearVars()            { $this->oDSVars->Clear(); }


    function Exists( $tmplname )
    {
        return( $this->oLoader->Exists( $tmplname ) );
    }

    function ExpandStr( $s, $raVars = array() )
    /******************************************
        Process the given string with the template processors.
        Vars are merged into the current datastore, scoped to this template.
     */
    {
        $raOldVars = $this->GetVarsRA();    // scope the given template vars to this template
// TODO: scoping means you can't set global vars that persist after an include. Create global vars in SEEDTemplate that are set by SetGlobalVar and checked after regular vars
        $this->SetVars( $raVars );
        foreach( $this->raProcessors as $k => $ra ) {
            $s = call_user_func( $ra['fnExpand'], $s, $this->oDSVars, $this->oLoader );
        }
        $this->ClearVars();
        $this->SetVars( $raOldVars );

        return( $s );
    }

    function ExpandTmpl( $tmplname, $raVars = array() )
    /**************************************************
        Process the given named template with the template processors.
        Vars are merged into the current datastore, and persist afterward.
     */
    {
        $sTmpl = $this->oLoader->Get( $tmplname );
        return( $sTmpl ? $this->ExpandStr( $sTmpl, $raVars ) : "" );
    }

    function factory_Loader()
    {
        // base class uses the base loader
        return( new SEEDTemplateLoader() );
    }
}

class SEEDTemplateLoader {
    protected $raTmpl = array();

    function __construct() {}

    function LoadStr( $sTmplGroup, $sMark = '%%' )
    /*********************************************
        Explode a group of templates from a string.
        This can be called multiple times if there are multiple template groups.
        Later additions overwrite earlier.

        Template format:
            # Comments above a template could be designated by hashes but actually anything before the first {sMark} is ignored

            {sMark} name-of-template
            template content
            # lines starting with hashes are removed (an arg could define the comment syntax if you don't like this)
            more template content

            {sMark} name-of-another-template
            template content
     */
    {
        $nOffset = 0;
        while( ($nStart = strpos($sTmplGroup, $sMark, $nOffset)) !== false ) {
            if( ($nEnd = strpos( $sTmplGroup, $sMark, $nStart+strlen($sMark) )) !== false ) {
                $chunk = substr( $sTmplGroup, $nStart, $nEnd - $nStart );
            } else {
                $chunk = substr( $sTmplGroup, $nStart );
            }
            $temp = explode("\n",$chunk,2);
            $tmplName = trim(substr($temp[0],strlen($sMark)));
            $tmplBody = $temp[1];

            // Comment lines start with #
            //if( $this->raParms['bCommentsStartWithHash'] )  -- true by default
            $raBody = explode( "\n", $tmplBody );
            $tmplBody = "";
            foreach( $raBody as $l ) {
                if( substr( $l, 0, 1 ) != '#' )  $tmplBody .= $l."\n";
            }

            $this->raTmpl[$tmplName] = $tmplBody;

            if( $nEnd === false )  break;

            $nOffset = $nEnd;
        }
        return( true );
    }

    function LoadRA( $raTmpl )
    /*************************
        Add templates to the store, later additions overwriting earlier
     */
    {
        $this->raTmpl = array_merge( $this->raTmpl, $raTmpl );
    }

    function Exists( $tmplname )
    {
        return( isset( $this->raTmpl[$tmplname] ) );
    }

    function Get( $tmplname )
    {
        return( @$this->raTmpl[$tmplname] );
    }
}


class SEEDTemplateLoader_H2o extends SEEDTemplateLoader
/***************************
    H2o wants a loader class that it can bind to and call read(), which has to be able to find the SEEDTagLoader
 */
{
    function __construct() { parent::__construct(); }

    function read( $tmplname )
    {
        // h2o takes this as a loader class, and calls read() when it needs a template. The return has to be a nodelist.
        // The fail case is that parse() reads "", so make sure it is a string (instead of null or 0)
        $sTmpl = $this->Get($tmplname);
        return( $this->runtime->parse( $sTmpl ? $sTmpl : "") );
    }

    function read_cache( $f )  { return $this->read($f); }
    function setOptions()      {}
}

class SEEDTemplate_Generator
/***************************
    Makes a SEEDTemplate that processes H2o and/or SEEDTag
 */
{
    const USE_H2O = 1;
    const USE_SEEDTAG = 2;

// Wanted this class to be a stateless SEEDTemplate factory, but the callbacks need to know things e.g. oLoader for [[include:]]
// and the class heirarchy makes it hard to store that info in the generated objects. So instead this is a slightly stateful Generator that's
// built to create elegant-looking processor-independent SEEDTemplate objects which are almost actually elegant.
//
// Probably what was intended was for this to be a derivation of a generic SEEDTemplate, providing a loader and a set of processors
    protected $flags = 0;
    protected $oDSVars = null;
    protected $oSeedTag = null;
    protected $oH2o = null;
    protected $oLoader = null;

    private $raParms;

    function __construct( $raParms = array() )
    {
        $this->raParms = $raParms;

        $this->oDSVars = new SEEDDataStore();    // used by factory_SEEDTag so put this before that is called
        if( @$raParms['vars'] ) $this->oDSVars->SetValuesRA( $raParms['vars'] );

        // by default, process H2o then SEEDTag for the reasons explained below
        $this->flags = isset($raParms['use']) ? $raParms['use']
                                                : (self::USE_SEEDTAG | self::USE_H2O);

        // create the required processors, and possibly the loader
        if( $this->flags & self::USE_H2O ) {
            include_once( "os/h2o/h2o.php" );
            $this->oH2o = $this->factory_H2o();
        }
        if( $this->flags & self::USE_SEEDTAG ) {
            include_once( SEEDCORE."SEEDTag.php" );
            $this->oSeedTag = $this->factory_SEEDTag( @$this->raParms['SEEDTagParms'] ? $this->raParms['SEEDTagParms'] : array(),
                                                      $this->oDSVars );    // make SEEDTag use the same var datastore as SEEDTemplate and H2o
        }
        // if the above processor factories didn't create the loader, create it using the default factory
        if( !$this->oLoader ) {
            $this->oLoader = $this->factory_Loader();
        }
    }

// There is no particular reason why some code is in the constructor and some is here. The object is not stateless so it doesn't matter
// where the code is
    function MakeSEEDTemplate()
    {
        $raST = array();

        /* Processing all H2o tags before SEEDTags
         * Con: [[SetVar:a|b]] does not work with {{a}}, but you can always use [[Var:a]] instead
         * Pro: otherwise all SEEDTags within false h2o conditionals {% if 0 %} [[tag:]] {%endif%} would be processed *with side effects*
         */
        if( $this->flags & self::USE_H2O ) {
            $raST['processors']['H2o'] = array( 'fnExpand' => array($this,'ExpandH2o') );
        }
        if( $this->flags & self::USE_SEEDTAG ) {
            $raST['processors']['SEEDTag'] = array( 'fnExpand' => array($this,'ExpandSEEDTag') );
        }

        $raST['loader']['oLoader'] = $this->oLoader;

        // use the base methods for reading/loading templates
        if( @$this->raParms['sTemplates'] )  $raST['sTemplates'] = $this->raParms['sTemplates'];
        if( @$this->raParms['raTemplates'] ) $raST['raTemplates'] = $this->raParms['raTemplates'];
        if( @$this->raParms['fTemplates'] )  $raST['fTemplates'] = $this->raParms['fTemplates'];

        $oTmpl = new SEEDTemplate( $raST, $this->oDSVars );    // use global datastore for all template processors

// Kluge: to process [[include:]] the HandleTag needs SEEDTemplate
        if( $this->oSeedTag )  $this->oSeedTag->oTmpl = $oTmpl;

        return( $oTmpl );
    }

    function ExpandSEEDTag( $s, SEEDDataStore $oDSVars, SEEDTemplateLoader $oLoader )
    {
        // oDSVars from SEEDTemplate is the shared global datastore that's also used in this object, and oSeedTag
        // That means if the template has a [[SetVar:]] everyone will see the new value
        // It also means the argument can be ignored here
        return( $this->oSeedTag->ProcessTags($s) );
    }

    function ExpandH2o( $s, SEEDDataStore $oDSVars, SEEDTemplateLoader $oLoader )
    {
        // H2o takes a simple array of readonly vars, and it has no SetVar like SEEDTag does.
        // The SEEDDataStore passed here by SEEDTemplate::ExpandStr is the same one used by ExpandSEEDTag so any [[SetVar:]]
        // in the template will be visible by h2o as long as SEEDTags are processed before h2o tags
        $raVars = $oDSVars->GetValuesRA();
        $this->oH2o->nodelist = $this->oH2o->parse($s);
        return( $this->oH2o->render($raVars) );
    }

    protected function factory_SEEDTag( $raParms, $oDSVars )    // typically oDSVars is the same var datastore as this class and H2o use
    {
        return( new SEEDTemplate_SEEDTagParser( $raParms, $oDSVars ) );
    }

    protected function factory_H2o()
    /* Create the H2o object and its loader
     */
    {
        if( !$this->oLoader ) {
            $this->oLoader = @$this->raParms['H2oLoader']
                                            ? $this->raParms['H2oLoader']
                                            : $this->factory_H2oLoader( $this->raParms );
        }
        $h2oParms = array( 'loader' => $this->oLoader );
        return( new H2o( null, $h2oParms ) );
    }

    protected function factory_H2oLoader( $raParms = array() )
    {
        return( new SEEDTemplateLoader_H2o($raParms) );
    }

    protected function factory_Loader()
    {
        // the default
        return( new SEEDTemplateLoader() );
    }
}

class SEEDTemplate_SEEDTagParser extends SEEDTagParser
{
    public  $oTmpl;        // set by kluge to allow [[include:]]

    private $oBasicResolver = null;

    function __construct( $raParms, $oDSVars )
    {
        if( !isset($raParms['raResolvers']) )  $raParms['raResolvers'] = array();

        if( isset($raParms['EnableBasicResolver']) ) {
            // the value of EnableBasicResolver is the raParms for SEEDTagBasicResolver
            $this->oBasicResolver = new SEEDTagBasicResolver( $raParms['EnableBasicResolver'] );
            $raParms['raResolvers'][] = array( 'fn'=>array($this->oBasicResolver,'ResolveTag'), 'raParms'=>array() );
        }

        parent::__construct( $raParms, $oDSVars );
    }

    function HandleTag( $raTag )
    {
        $bHandled = false;
        $s = "";

        // Template related tags are handled here. They could also be handled by a ResolveTag method added to raResolvers
        switch( strtolower($raTag['tag']) ) {
            case 'include':
                $s = $this->oTmpl->ExpandTmpl( $raTag['target'] );
                $bHandled = true;
                break;
        }
        if( $bHandled ) goto done;

        // the base SEEDTagParser will call each ResolveTag defined in the constructor, then employ its own base tags
        $s = parent::HandleTag( $raTag );

        done:
        return( $s );
    }
}

?>