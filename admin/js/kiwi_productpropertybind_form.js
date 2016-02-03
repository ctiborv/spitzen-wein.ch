function Kiwi_ProductPropertyBind_Form_Class()
{
	this.onSave = function()
	{
/*
		tocost = document.getElementById('kppbfc_tocost');
		if (!isFloat(tocost.value))
		{
			alert('Neplatná hodnota položky "Vliv na cenu"!');
			tocost.focus();
			return false;
		}
*/
		return true;
	}

	this.onBack = function(link)
	{
		document.location.href = link;
		return true;
	}

	this.onRemovePhoto = function(script_qs)
	{
		btn = document.getElementById('kppbfc_cmd1');
		if (btn.disabled)
			document.location.href = script_qs + '&rp';
		else if (confirm("Entfernen des Bildes verlieren Sie alle nicht gespeicherten Änderungen.\nWollen Sie das Bild löschen?"))
			document.location.href = script_qs + '&rp';
	}

	this.onChange = function()
	{
		btn = document.getElementById('kppbfc_cmd1');
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

var Kiwi_ProductPropertyBind_Form = new Kiwi_ProductPropertyBind_Form_Class();

function isFloat(s)
{
	reFloat = /^((\d+(\.\d*)?)|((\d*\.)?\d+))$/
	return reFloat.test(s)
}