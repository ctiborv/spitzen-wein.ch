function Kiwi_Order_Form_Class()
{
	this.onSave = function()
	{
		return true; // v současnosti není co kontrolovat
	}

	this.Export = function(export_script)
	{
		var save_btn = document.getElementById('kordfc_cmd1');
		if (!save_btn.disabled && !confirm("Exportiert werden nur gespeicherten Daten. Weiter?"))
			return;

		return window.open(export_script, "_blank");
	}

	this.Print = function(print_script)
	{
		var save_btn = document.getElementById('kordfc_cmd1');
		if (!save_btn.disabled && !confirm("Drucken werden nur gespeicherten Daten. Weiter?"))
			return;

		return window.open(print_script, "_blank");
	}

	this.PDF = function(pdf_script)
	{
		return window.open(pdf_script, "_blank");
	}

	this.onChange = function()
	{
		var btn = document.getElementById('kordfc_cmd1');
		btn.disabled = false;
		btn.className = 'but3';
	}

	this.onKeyDown = function(e)
	{
		if (window.event) // IE
			var keynum = e.keyCode;
		else if (e.which) // Netscape/Firefox/Opera
			var keynum = e.which;

		var skip = new Array(9, 16, 17, 18, 19, 20, 33, 34, 35, 36, 37, 38, 39, 40, 144, 145);
		var slen = skip.length;

		for (var i = 0; i < slen; i++)
			if (keynum == skip[i]) return;

		this.onChange();
	}
}

var Kiwi_Order_Form = new Kiwi_Order_Form_Class();
