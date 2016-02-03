function Kiwi_ProductPropertyValue_Form_Class()
{
	this.onSave = function()
	{
		var value = document.getElementById('kppvfc_value');
		if (value.value == '')
		{
			alert("Posten 'Wert der Eigenschaft' ist obligatorisch!");
			value.focus();
			return false;
		}

		extradata = document.getElementById('kppvfc_extradata');
		if (extradata != null)
		{
			var edtitle = document.getElementById('kppvfc_edtitle').value;
			if (extradata.type == 'text' && extradata.value == '')
			{
				alert("Posten '" + edtitle + "' ist obligatorisch!");
				extradata.focus();
				return false;
			}

			var datatype = document.getElementById('kppvfc_datatype');
			if (datatype.value == 3) // barva
			{
				if (!this.isValidColor(extradata.value))
				{
					alert("'" + edtitle + "'-Wert ist nicht korrekt!");
					extradata.focus();
					return false;
				}
			}
		}

		return true;
	}

	this.onRemoveIcon = function(script_qs)
	{
		var qc = 'ri';
		btn = document.getElementById('kppvfc_cmd1');
		if (btn.disabled)
			document.location.href = script_qs + '&' + qc;
		else if (confirm("Entfernen des Bildes verlieren Sie alle nicht gespeicherten Änderungen.\nWollen Sie das Bild löschen?"))
			document.location.href = script_qs + '&' + qc;
	}

	this.onChange = function()
	{
		btn = document.getElementById('kppvfc_cmd1');
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

	this.reColor = /^[0-9a-fA-F]{6}([,;\|][0-9a-fA-F]{6}){0,4}$/

	function isValidColor(s)
	{
		return this.reColor.test(s)
	}
}

var Kiwi_ProductPropertyValue_Form = new Kiwi_ProductPropertyValue_Form_Class();