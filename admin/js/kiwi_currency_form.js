function Kiwi_Currency_Form_Class()
{
	this.float = /^\d+[\.,]?\d*$/;

	this.onSave = function()
	{
		var prefix = 'kcrfc_cc';
		var i = 1;
		var inp;
		while (inp = document.getElementById(prefix + i))
		{
			if (inp.value == '')
			{
				alert("Alle Kurse sind obligatorisch!");
				inp.focus();
				return false;
			}
			if (!this.float.test(inp.value))
			{
				alert("Geben Sie eine positive Dezimalzahl!");
				inp.focus();
				return false;			
			}
			i++;
		}

		return true;
	}

	this.onChange = function()
	{
		btn = document.getElementById('kcrfc_cmd1');
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

var Kiwi_Currency_Form = new Kiwi_Currency_Form_Class();
