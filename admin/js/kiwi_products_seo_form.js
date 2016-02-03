function Kiwi_Products_SEO_Form_Class()
{
	this.loaded = false;

	this.onLoad = function()
	{
		this.loaded = true;
	}

	this.loadCheck = function()
	{
		if (!this.loaded)
		{
			alert('Prosím počkejte, než se stránka načte celá.');
			return false;
		}
		return true;
	}

	this.enableBtns = function(s)
	{
		var cmds = [1];
		var size = cmds.length;
		var cmdos = new Array(size);

		var i;
		for (i = 0; i < size; i++)
			cmdos[i] = document.getElementById('kpsfc_cmd' + cmds[i]);

		if (s || checkCount('check[]') != 0)
		{
			d = false;
			cn = 'but3';
		}
		else
		{
			d = true;
			cn = 'but3D';
		}

		for (i = 0; i < size; i++)
			if (cmdos[i])
			{
				cmdos[i].disabled = d;
				cmdos[i].className = cn;
			}
	}

	this.recoverURLs = function()
	{
		if (!this.loadCheck()) return false;

		elems = document.getElementsByName('r_url');
		elems_count = elems.length;
		var i;
		for (i = 0; i < elems_count; i++)
			elems[i].onclick();
		return false;
	}

	this.recoverPageTitles = function()
	{
		if (!this.loadCheck()) return false;

		elems = document.getElementsByName('r_title');
		elems_count = elems.length;
		var i;
		for (i = 0; i < elems_count; i++)
			elems[i].onclick();
		return false;
	}

	this.recoverURL = function(id, url)
	{
		if (!this.loadCheck()) return false;

		ur_e = document.getElementById('url' + id);
		if (ur_e.value != url)
			ur_e.value = url;
		this.setCheck(id, ur_e.defaultValue != url);
		return false;
	}

	this.recoverPageTitle = function(id, pt)
	{
		if (!this.loadCheck()) return false;

		pt_e = document.getElementById('pt' + id);
		if (pt_e.value != pt)
			pt_e.value = pt;
		this.setCheck(id, pt_e.defaultValue != pt);
		return false;
	}

	this.setCheck = function(id, val)
	{
		chb_e = document.getElementById('chb' + id);
		chb_e.checked = val;
		this.enableBtns(val);
	}

	this.setCCheck = function(id)
	{
		ur_e = document.getElementById('url' + id);
		pt_e = document.getElementById('pt' + id);
		this.setCheck(id, ur_e.value != ur_e.defaultValue || pt_e.value != pt_e.defaultValue);
	}

	this.onKeyUp = function(id, e)
	{
		if (window.event) // IE
			keynum = e.keyCode;
		else if (e.which) // Netscape/Firefox/Opera
			keynum = e.which;
/*
		skip = new Array(9, 16, 17, 18, 19, 20, 33, 34, 35, 36, 37, 38, 39, 40, 144, 145);
		slen = skip.length;

		var i;
		for (i = 0; i < slen; i++)
			if (keynum == skip[i]) return;
*/
		this.setCCheck(id);
	}
}

var Kiwi_Products_SEO_Form = new Kiwi_Products_SEO_Form_Class();

function myOnLoad()
{
	Kiwi_Products_SEO_Form.onLoad();
}

addEvent(window, 'load', myOnLoad, false);
