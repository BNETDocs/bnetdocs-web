/*
 FILE ARCHIVED ON 22:40:13 Sep 10, 2011 AND RETRIEVED FROM THE
 INTERNET ARCHIVE ON 8:41:31 Mar 24, 2014.
 JAVASCRIPT APPENDED BY WAYBACK MACHINE, COPYRIGHT INTERNET ARCHIVE.

 ALL OTHER CONTENT MAY ALSO BE PROTECTED BY COPYRIGHT (17 U.S.C.
 SECTION 108(a)(3)).
*/
var pagecode = "";

function scheck(field)
{
	for (i = 0; i < field.length; i++)
	{
		field[i].checked = false; 
	}
	alert('definitely being executed');

	return true;
}


function doonload()
{
	if(document.createStyleSheet)
	{
		document.createStyleSheet('/web/20110910224013/http://bnetdocs.dementedminds.net/bdif/js_hide.css');
	}
	else
	{
		var styles = "@import url(' /web/20110910224013/http://bnetdocs.dementedminds.net/bdif/js_hide.css ');";
		var newSS=document.createElement('link');
		newSS.rel='stylesheet';
		newSS.href='data:text/css,'+escape(styles);
		document.getElementsByTagName("head")[0].appendChild(newSS);
	}
	
	//document.cookie='hidden='+"";
	var hidden=get_cookie("hidden");
	var divid="";
	if(hidden != "")
	{
		// Process
		var i=0
		for (i=0; i<=hidden.length; i=i+2)
		{
			// Open Div's
			divid = hidden.substr(i,2);
			toggle_visibility(divid);
			toggleHLS('x'+hidden.charAt(i+1)+'1');
		}
	}
	

}

function toggleHLS(id)
{
	var e = document.getElementById(id);
	if(e)
	{
		toggle_name(e);
	}

	return;
}

function toggle_visibility(id)
{}
	var e = document.getElementById(id);
	if(e)
	{
		if(e.style.display == 'block')
		{
			e.style.display = 'none';
			forgetid(id);
		}
		else
		{
			e.style.display = 'block';
			rememberid(id);
		}
	}

	return;
}

function rememberid(did)
{
	var hidden=get_cookie("hidden");
	var newmemory = '';
	var alreadyadded = "false";
	var did = did + '';
	hidden = hidden + '';
	if(hidden != "")
	{
		if(hidden.search(did) < 0)
		{ 
			document.cookie='hidden='+hidden+did;
		}
	}
	else
	{
		document.cookie='hidden='+did;
	}
}

function forgetid(did)
{
	var hidden=get_cookie("hidden");
	var newmemory = '';
	var did = did + '';
	newmemory = hidden.replace(did,'');
	document.cookie='hidden='+newmemory;
}

//Get cookie routine by Shelley Powers 
function get_cookie(Name)
{
	var search = Name + "="
	var returnvalue = "";
	if (document.cookie.length > 0)
	{
		offset = document.cookie.indexOf(search)
		// if cookie exists
		if (offset != -1)
		{ 
			offset += search.length
			// set index of beginning of value
			end = document.cookie.indexOf(";", offset);
			// set index of end of cookie value
			if (end == -1) end = document.cookie.length;
			returnvalue=unescape(document.cookie.substring(offset, end))
		}
	}

	return returnvalue;
}

function toggle_name(objLink)
{
	if(objLink.innerHTML == "+")
		objLink.innerHTML = "-";
	else
		objLink.innerHTML = "+";
}

function validate_required(field,alerttxt)
{
	with (field)
	{
		if (value==null || value=="")
		{
			alert(alerttxt);
			return false
		}
		else
		{
			return true
		}
	}
}

function validate_form(thisform)
{
	with (thisform)
	{
		if (validate_required(username,"Username must be filled out!") == false)
		{
			username.focus();
			return false
		}
		if (validate_required(password,"Password must be filled out!") == false)
		{
			password.focus();
			return false
		}

	}
}

function showimage()
{
	if (!document.images) return
	var picdir = '/images/newsicon/';
	document.images.thepic.src = picdir + document.thenews.pictureselector.options[document.thenews.pictureselector.selectedIndex].value + 'icon.png';
}

function confimdelete()
{
	var confirmation = confirm ("Please confirm deletion.");
	if (confirmation)
	{
		return true;
	}
	else
	{
		return false;
	}
}
