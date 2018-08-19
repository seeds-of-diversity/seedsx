
// from QuirksMode finds absolute position of an element
function findPosX(obj)
{
	var curleft = 0;
	if (obj.offsetParent)
	{
		while (obj.offsetParent)
		{
			curleft += obj.offsetLeft;
			obj = obj.offsetParent;
		}
	}
	else if (obj.x)
		curleft += obj.x;
	return curleft;
}

// from QuirksMode finds absolute position of an element
function findPosY(obj)
{
	var curtop = 0;
	if (obj.offsetParent)
	{
		while (obj.offsetParent)
		{
			curtop += obj.offsetTop;  // alert(obj.offsetTop);
			obj = obj.offsetParent;
		}
	}
	else if (obj.y)
		curtop += obj.y;
	return curtop;
}

function dpCreateCal (txt, div, date)
{
  var usertxt = document.getElementById(txt);
  var today = new Date();
  var seldate = date;
  var userdate;

//Bob
  if( usertxt.value.length == 10 && usertxt.value.charAt(4)=='-' && usertxt.value.charAt(7)=='-' ) {
    // convert the non-Javascript date format YYYY-MM-DD to the accepted YYYY/MM/DD
    usertxt.value = usertxt.value.substr(0,4) + "/" + usertxt.value.substr(5,2) + "/" + usertxt.value.substr(8,2);
  }
//Bob

  // if the user doesn't select a date use today
  if (date == null)
    seldate = today;
  if (!isNaN(Date.parse(usertxt.value)))
    seldate = new Date(usertxt.value);

  dpCreateCalReal(txt, div, seldate.getFullYear(), seldate.getMonth(), seldate);
}

function dpCreateCalReal (txt, div, year, month, date)
{
  var monthDays = new Array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');
  var usertxt = document.getElementById(txt);
  var userdiv = document.getElementById(div);
  var cal, prev, next, navyear, navmonth;

  /*@cc_on
  var sels = document.getElementsByTagName('select');
  var i;
  for (i = 0; i < sels.length; i++)
    sels[i].style.visibility='hidden';
  @*/

  cal = '<div id="dpDiv">'; // style="position:absolute;top:' + (findPosY(usertxt) + usertxt.offsetHeight) + 'px;left:' + findPosX(usertxt) + 'px">';
  cal += '<div id="dpTitle">';
  cal += '<span id="dpTitleText">&nbsp;' + monthDays[month] + ' ' + year + '</span>';

  // calculate previous month
  navmonth = month - 1;
  navyear = year;
  if (month == 0)
  {
    navmonth = 11;
    navyear = year - 1;
  }
  prev = 'dpDestroyCal(\'' + div + '\');dpCreateCalReal(\'' + txt + '\',\'' + div + '\',' + navyear + ',' + navmonth + ',new Date(\'' + date.getFullYear() + '/' + (date.getMonth() + 1) + '/' + date.getDate() + '\'));return false;';
  // calculate next month
  navmonth = month + 1;
  navyear = year;
  if (month == 11)
  {
    navmonth = 0;
    navyear = year + 1;
  }
  next = 'dpDestroyCal(\'' + div + '\');dpCreateCalReal(\'' + txt + '\',\'' + div + '\',' + navyear + ',' + navmonth + ',new Date(\'' + date.getFullYear() + '/' + (date.getMonth() + 1) + '/' + date.getDate() + '\'));return false;';

  cal += '<a href="#" onclick="' + prev + '">&lt;</a> <a href="#" onclick="' + next + '">&gt;</a> <a href="#" onclick="dpDestroyCal(\'' + div + '\');return false;">X</a></div>';
  cal += '<div id="dpDayNames"><ul><li><a>Su</a></li><li><a>Mo</a></li><li><a>Tu</a></li><li><a>We</a></li><li><a>Th</a></li><li><a>Fr</a></li><li><a>Sa</a></li></ul></div>';

  cal += '<div id="dpDays">';
  var d = new Date(year, month, 1);
  var today = new Date();
  var i, j, yy, mm, dd, cssclass;
  if (d.getDay() <= 1)
    d.setDate(d.getDate() - 7);
  d.setDate(d.getDate() - d.getDay());
  for (j = 0; j < 6; j++)
  {
    cal += '<ul>';
    for (i = 0; i < 7; i++)
    {
      // save time by saving values
      yy = d.getFullYear();
      mm = d.getMonth();
      dd = d.getDate();
      // pick between other month and current month classes
      if (d.getMonth() != month)
        cssclass = 'dpDay dpOtherMonth';
      else
        cssclass = 'dpDay dpCurrentMonth';
      // pick selected date
      if (yy == date.getFullYear() && mm == date.getMonth() && dd == date.getDate())
        cssclass += ' dpCurrentDay';
      // pick today
      else if (yy == today.getFullYear() && mm == today.getMonth() && dd == today.getDate())
        cssclass += ' dpToday';
      // write day cell
      cal += '<li><a href="#" class="' + cssclass + '" onclick="dpDayClick(\'' + txt + '\',' + yy + ',' + (mm + 1) + ',' + dd + ');dpDestroyCal(\'' + div + '\');return false;">' + dd + '</a></li>';
      // move to next day
      d.setDate(d.getDate()+1);
    }
    cal += '</ul>';
  }
  cal += '</div></div>';
  userdiv.innerHTML = cal;
}

function dpDestroyCal (div)
{
  var userdiv = document.getElementById(div);
  userdiv.innerHTML = '';
  /*@cc_on
  var sels = document.getElementsByTagName('select');
  var i;
  for (i = 0; i < sels.length; i++)
    sels[i].style.visibility='visible';
  @*/
}

function dpDayClick (txt, year, month, day)
{
  var output = document.getElementById(txt);
  output.value = year + '/' + month + '/' + day;
}
