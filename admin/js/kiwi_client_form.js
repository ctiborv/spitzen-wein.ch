function Kiwi_Client_Form_Class()
{
	this.onSave = function()
	{
		// nekompletni, skript zatim neuklada, takze neni treba resit
		var name = document.getElementById('kclifc_FirstName');
		if (name && name.value == '')
		{
			alert('Posten "Name" ist obligatorisch!');
			name.focus();
			return false;
		}

		return true;
	}

	this.onChange = function()
	{
		btn = document.getElementById('kclifc_cmd1');
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

var Kiwi_Client_Form = new Kiwi_Client_Form_Class();
