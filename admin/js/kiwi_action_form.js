function Kiwi_Action_Form_Class()
{
	this.onSave = function()
	{
		var title = document.getElementById('kactfc_title');
		if (title.value == '')
		{
			alert('Položka "Název akce" je povinná!');
			title.focus();
			return false;
		}
		return true;
	}

	this.onRemovePic = function(script_qs)
	{
		var btn = document.getElementById('kactfc_cmd1');
		if (btn.disabled)
			document.location.href = script_qs + '&rp';
		else if (confirm("Odstraněním obrázku přijdete o všechny neuložené změny formuláře.\nPřejete si obrázek odstranit?"))
			document.location.href = script_qs + '&rp';
	}

	this.onChange = function()
	{
		var btn = document.getElementById('kactfc_cmd1');
		btn.disabled = false;
		btn.className = 'but3';
	}

	this.onKeyDown = function(e)
	{
		var keynum;
		if (window.event) // IE
			keynum = e.keyCode;
		else if (e.which) // Netscape/Firefox/Opera
			keynum = e.which;

		var skip = new Array(9, 16, 17, 18, 19, 20, 33, 34, 35, 36, 37, 38, 39, 40, 144, 145);
		var slen = skip.length;

		for (i = 0; i < slen; i++)
			if (keynum == skip[i]) return;

		this.onChange();
	}
}

var Kiwi_Action_Form = new Kiwi_Action_Form_Class();
