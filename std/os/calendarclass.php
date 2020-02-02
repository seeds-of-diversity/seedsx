<?php

/* Based on calendarclass by Manuel Lemos
 *
 * @(#) $Id: calendarclass.class,v 1.3 2000/12/03 22:10:48 mlemos Exp $
 *
 */

include_once( SEEDCORE."SEEDDate.php" );


class calendar_class extends table_class
{
    private $year = 2000;
    private $month = 1;    // 1..12
    private $day = 0;      // 1..31

    private $daysInMonth = 0;
    private $dayOfWeek = 0;    // 0..6  is Sun..Sat

 var $week_day_names=array();
 var $error="";
 var $calendar_rows=0;

    function GetYear()      { return( $this->year ); }
    function GetMonth()     { return( $this->month ); }
    function GetDay()       { return( $this->day ); }

    function SetYear( $y )  { $this->year = $y; }
    function SetMonth( $m ) { $this->month = $m; }

    function GetMonthName( $m )
    /**************************
        $m is a number 1..12
     */
    {
        return( ($m >= 1 && $m <= 12) ? SEEDDate::$raMonths[$m]['en'] : "MONTH $m" );
    }

    function OutputCalendar()
    {
        $this->daysInMonth = SEEDDate::DaysInMonth( $this->month, $this->year );

        $this->dayOfWeek = SEEDDate::DayOfWeek( $this->year, $this->month, 1 );
        $this->calendar_rows = intval( ($this->dayOfWeek + $this->daysInMonth + 6) / 7 ) + 1;

        if( count($this->week_day_names) != 7 ) {
            $this->week_day_names = array("Sun","Mon","Tue","Wed","Thu","Fri","Sat");
        }
        return( $this->outputtable() );
    }




 Function fetchcustomcolumn(&$columndata)
 {
  return 1;
 }

 Function fetchcolumn(&$columndata)
 {
  $column=$columndata["column"];
  if(!($column<7))
   return 0;
  $row=$columndata["row"];
  if(($row==0))
  {
   $this->day=0;
   $columndata["data"]=$this->week_day_names[$column];
   $columndata["align"]="center";
  }
  else
  {
   $this->day=(($row-1)*7+$column+1-$this->dayOfWeek);
   if(($this->day>0 && $this->day<=$this->daysInMonth))
   {
    $columndata["data"]=strval($this->day)
                       .$this->DayContent();
    $columndata["align"]="center";
   }
   else
    $this->day=0;
  }
  return $this->fetchcustomcolumn($columndata);
 }

 Function fetchrow(&$rowdata)
 {
  $row=$rowdata["row"];
  return $row<$this->calendar_rows;
 }



 function DayContent()
 {
     return("");
 }



};



class table_class
{

	/*
	 * Public variables
	 *
	 */
	var $center=1;
	var $border=1;
	var $width='';
	var $style='';
	var $class='';
	var $page=0;
	var $rowsperpage=10;
	var $totalrows=0;
	var $headerlistingrowrange=1;
	var $footerlistingrowrange=0;
	var $headerlistingpages=0;
	var $footerlistingpages=1;
	var $listpages=3;
	var $pagevariable='page';
	var $pagelinkurl='';
	var $pagelinkvalues=array();
	var $pagelinkvaluesstring='';
	var $rangelinkseparator=' | ';
	var $firstprefix='<<';
	var $previousprefix='<';
	var $nextsuffix='>';
	var $lastsuffix='>>';
	var $rangeinfirstlast=1;
	var $rangeinpreviousnext=1;


	/*
	 * Public functions
	 *
	 */
	Function encodeoutput($output)
	{
		return HtmlEntities($output);
	}

	Function fetchcolumn(&$columndata)
	{
		return 0;
	}

	Function outputcolumns($row)
	{
		$columndata=array('row'=>$row,'column'=>0);
		$output='';
		for(;;)
		{
			$columndata['data']='';
			$columndata['header']=0;
			$columndata['backgroundcolor']='';
			$columndata['width']='75';
			$columndata['class']='';
			$columndata['style']='';
			$columndata['align']='';
			$columndata['verticalalign']='';
			if(!($this->fetchcolumn($columndata)))
			{
				break;
			}
			$output=($output.(($columndata['header']) ? '<th'.(strcmp($columndata['class'],'') ? ' class="'.$columndata['class'].'"' : '').(strcmp($columndata['style'],'') ? ' style="'.$columndata['style'].'"' : '').(strcmp($columndata['width'],'') ? ' width="'.$columndata['width'].'"' : '').(strcmp($columndata['backgroundcolor'],'') ? ' bgcolor="'.$columndata['backgroundcolor'].'"' : '').(strcmp($columndata['align'],'') ? ' align="'.$columndata['align'].'"' : '').(strcmp($columndata['verticalalign'],'') ? ' valign="'.$columndata['verticalalign'].'"' : '').'>'.$columndata['data']."</th>\n" : '<td'.(strcmp($columndata['class'],'') ? ' class="'.$columndata['class'].'"' : '').(strcmp($columndata['style'],'') ? ' style="'.$columndata['style'].'"' : '').(strcmp($columndata['width'],'') ? ' width="'.$columndata['width'].'"' : '').(strcmp($columndata['backgroundcolor'],'') ? ' bgcolor="'.$columndata['backgroundcolor'].'"' : '').(strcmp($columndata['align'],'') ? ' align="'.$columndata['align'].'"' : '').(strcmp($columndata['verticalalign'],'') ? ' valign="'.$columndata['verticalalign'].'"' : '').'>'.$columndata['data']."</td>\n"));
			$columndata['column']=($columndata['column']+1);
		}
		return $output;
	}

	Function fetchrow(&$rowdata)
	{
		return 0;
	}

	Function outputheader()
	{
		return (($this->headerlistingrowrange) ? $this->outputlistingrowrange() : '').(($this->headerlistingpages) ? $this->outputlistingpages() : '');
	}

	Function outputfooter()
	{
		return (($this->footerlistingpages) ? $this->outputlistingpages() : '').(($this->footerlistingrowrange) ? $this->outputlistingrowrange() : '');
	}

	Function outputrows()
	{
		$rowdata=array('row'=>0,'backgroundcolor'=>'');
		$output='';
		for(;;)
		{
			if(!($this->fetchrow($rowdata)))
			{
				break;
			}
			$output=($output.'<tr'.(IsSet($rowdata['id']) ? ' id="'.$rowdata['id'].'"' : '').(strcmp($rowdata['backgroundcolor'],'') ? ' bgcolor="'.$rowdata['backgroundcolor'].'"' : '').(IsSet($rowdata['highlightcolor']) && strcmp($rowdata['highlightcolor'],'') && strcmp($rowdata['backgroundcolor'],'') && IsSet($rowdata['id']) ? ' onmouseover="if(document.layers) { document.layers[\''.(IsSet($rowdata['id']) ? $rowdata['id'] : '').'\'].bgColor=\''.$rowdata['highlightcolor'].'\' } else { if(document.all) { document.all[\''.(IsSet($rowdata['id']) ? $rowdata['id'] : '').'\'].style.background=\''.$rowdata['highlightcolor'].'\' } else { if(this.style) { this.style.background=\''.$rowdata['highlightcolor'].'\' } } }" onmouseout="if(document.layers) { document.layers[\''.(IsSet($rowdata['id']) ? $rowdata['id'] : '').'\'].bgColor=\''.(strcmp($rowdata['backgroundcolor'],'') ? $rowdata['backgroundcolor'] : '').'\' } else { if(document.all) { document.all[\''.(IsSet($rowdata['id']) ? $rowdata['id'] : '').'\'].style.background=\''.(strcmp($rowdata['backgroundcolor'],'') ? $rowdata['backgroundcolor'] : '').'\' } else { if(this.style) { this.style.background=\''.(strcmp($rowdata['backgroundcolor'],'') ? $rowdata['backgroundcolor'] : '').'\' } } }"' : '').">\n".$this->outputcolumns($rowdata['row'])."</tr>\n");
			$rowdata['row']=($rowdata['row']+1);
		}
		return $output;
	}

	Function outputtable()
	{
		return $this->outputheader().($this->center ? '<center>' : '').'<table'.(strcmp($this->class,'') ? ' class="'.$this->class.'"' : '').(strcmp($this->style,'') ? ' style="'.$this->style.'"' : '').(strcmp($this->width,'') ? ' width="'.$this->width.'"' : '').">\n".($this->border>0 ? "<tr>\n<td><center><table border=\"".strval($this->border)."\">\n" : '').$this->outputrows().($this->border>0 ? "</table></center></td>\n</tr>" : '')."\n</table>".($this->center ? '</center>' : '')."\n".$this->outputfooter();
	}

	Function pagerange($page)
	{
		$firstrow=($page*$this->rowsperpage);
		return (strval($firstrow+1).'-'.strval((($firstrow+$this->rowsperpage<$this->totalrows) ? $firstrow+$this->rowsperpage : $this->totalrows)));
	}

	Function pagelink($page,$data)
	{
		if(!strcmp($this->pagelinkvaluesstring,''))
		{
			Reset($this->pagelinkvalues);
			$end=(GetType($key=Key($this->pagelinkvalues))!='string');
			for(;!$end;)
			{
				$this->pagelinkvaluesstring=($this->pagelinkvaluesstring.'&'.$key.'='.$this->pagelinkvalues[$key]);
				Next($this->pagelinkvalues);
				$end=(GetType($key=Key($this->pagelinkvalues))!='string');
			}
		}
		return '<a href="'.((!strcmp($this->pagelinkurl,'')) ? $GLOBALS["PHP_SELF"] : $this->pagelinkurl).'?'.$this->pagevariable.'='.strval($page).$this->pagelinkvaluesstring.'">'.((!strcmp($data,'')) ? $this->pagerange($page) : $data).'</a>';
	}

	Function listingrowrange()
	{
		return ($this->pagerange($this->page).' / '.strval($this->totalrows));
	}

	Function outputlistingrowrange()
	{
		return (($this->totalrows>0) ? '<center><b>'.$this->listingrowrange()."</b></center>\n" : '');
	}

	Function listingpages()
	{
		$output='';
		$this->pagelinkvaluesstring='';
		if($this->page>0)
		{
			$link_page=($this->page-$this->listpages);
			if($link_page<0)
				$link_page=0;
			$output=($output.$this->pagelink(0,($this->encodeoutput($this->firstprefix).(($this->rangeinfirstlast) ? ' ('.$this->pagerange(0).')' : ''))).$this->rangelinkseparator);
			$link_page++;
			for(;$link_page<$this->page;)
			{
				$output=($output.$this->pagelink($link_page,((($link_page+1)==$this->page) ? ($this->encodeoutput($this->previousprefix).(($this->rangeinpreviousnext) ? ' ('.$this->pagerange($link_page).')' : '')) : '')).$this->rangelinkseparator);
				$link_page++;
			}
		}
		$output=($output.$this->pagerange($this->page));
		$maximum_page=(intval(($this->totalrows-1)/$this->rowsperpage));
		if($this->page<$maximum_page)
		{
			$link_page=($this->page+1);
			$last_page=($this->page+$this->listpages);
			if($last_page>$maximum_page)
				$last_page=$maximum_page;
			for(;$link_page<=($last_page-1);)
			{
				$output=($output.$this->rangelinkseparator.$this->pagelink($link_page,((($link_page-1)==$this->page) ? ((($this->rangeinpreviousnext) ? '('.$this->pagerange($link_page).') ' : '').$this->encodeoutput($this->nextsuffix)) : '')));
				$link_page++;
			}
			$output=($output.$this->rangelinkseparator.$this->pagelink($maximum_page,((($this->rangeinfirstlast) ? '('.$this->pagerange($maximum_page).') ' : '').$this->encodeoutput($this->lastsuffix))));
		}
		return $output;
	}

	Function outputlistingpages()
	{
		return (($this->totalrows>0) ? '<center><b>'.$this->listingpages()."</b></center>\n" : '');
	}
};


?>