<?

class Page2 {
    var $def;
    var $lang;
    var $step;      // origin-1 iteration number, 0 indicates END
    var $raParms;

    function Page2( $def, $raParms = array(), $lang="EN" ) {
        $this->def = $def;
        $this->raParms = $raParms;
        $this->lang = $lang;
        $this->step = 1;
    }

    function Page2_GetStep() {
        // This method is also a bounds normalizer
        if( $this->step < 1 || $this->step > count($this->def['Steps']) )  $this->step = 1;

        return( $this->step );
    }

    function Page2_GetNextStep() {
        $step = $this->Page2_GetStep();
        ++$step;
        if( $step > count($this->def['Steps']) )  $step = 0;      // indicate END
        return( $step );
    }

    function Page2_SetStep( $step ) {
        $this->step = $step;
        $this->Page2_GetStep();   // normalize bounds
    }


    function Page2_Parms( $k ) {
        return( @$this->raParms[$k] );
    }


    function Page2_Page( $step = "" ) {
        /* Either specify the step number as a parm, or set it prior to this using SetStep
         */
        if( !empty($step) )  $this->Page2_SetStep( $step );

        echo "<STYLE>";
        echo ".page2header { font-size:x-large; font-weight:bold; }";
        echo "#page2title  { margin-top: 1em; margin-bottom: 1em; }";
        echo "#page2step   { font-family:arial,helvetica,sans-serif; font-size: large; margin-top: 1em; margin-bottom: 1em; }";
        echo "</STYLE>";



        echo "<DIV class='page2header'>";
        echo ($this->lang=="FR" ? "<IMG SRC='".SITEIMG."logo_FR.gif'>" : "<IMG SRC='".SITEIMG."logo_EN.gif'>");
        if( isset( $this->def['Title_'.$this->lang] ) )  echo "<DIV id='page2title'>".$this->def['Title_'.$this->lang]."</DIV>";

        echo "<DIV id='page2step'>".($this->lang=="FR" ? "" : "Step")."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
        for( $i = 1; $i <= count($this->def['Steps']); ++$i ) {
            echo "<FONT color=".($i==$this->step ? "black" : "#dddddd").">$i</FONT>";
            echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
        }
        if( isset( $this->def['Steps'][$this->step - 1]['Title_'.$this->lang] ) ) {
            echo ":";
            echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
            echo $this->def['Steps'][$this->step - 1]['Title_'.$this->lang];
        }
        echo "</DIV>";
        echo "</DIV>";

        $this->def['Steps'][$this->step - 1]['fn']( $this );
    }
}

?>
