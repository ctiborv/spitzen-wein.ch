function Kiwi_ModNews_Form_Class()
{
	this.onSave = function()
	{
		name = document.getElementById('km_news_nam');
		if (name.value == '')
		{
			alert('Die Bezeichnung ist obligatorisch!');
			name.focus();
			return false;
		}

		count = document.getElementById('km_news_npp');
		if (count.value == '')
		{
			alert('Anzahl der News für die Seite ist obligatorisch!');
			count.focus();
			return false;
		}
		else if (!isInteger(count.value) || count.value >= 100 || count.value <= 0)
		{
			alert('Inhalt der Anzahl der News für die Seite ist nicht korrekt!');
			count.focus();
			return false;
		}
		return true;
	}

	this.onChange = function()
	{
		btn = document.getElementById('km_news_cmd1');
		btn.disabled = false;
		btn.className = 'but3';
	}

	this.onKeyDown = function(e)
	{
		if (window.event) // IE
			keynum = e.keyCode;
		else if (e.which) // Netscape/Firefox/Opera
			keynum = e.which;

		skip = new Array(9, 16, 17, 18, 19, 20, 33, 34, 35, 36, 37, 38, 39, 40, 144, 145);
		slen = skip.length;

		for (i = 0; i < slen; i++)
			if (keynum == skip[i]) return;

		this.onChange();
	}
}

var Kiwi_ModNews_Form = new Kiwi_ModNews_Form_Class();
var reInteger = /^\d+$/

function isInteger(s)
{
	return reInteger.test(s)
}
