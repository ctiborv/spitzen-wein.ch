function Kiwi_ProductProperty_Form_Class()
{
	this.enableBtns = function(s)
	{
		cmds = new Array('kppyfc_cmd5', 'kppyfc_cmd6', 'kppyfc_cmd7');
		size = cmds.length;
		cmdos = new Array(size);

		for (i = 0; i < size; i++)
			cmdos[i] = document.getElementById(cmds[i]);

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
	};

	this.onDelete = function()
	{
		return confirm('Sind Sie sicher, dass Sie die ausgewählten Eigenschaften löschen möchten?');
	}

	this.onSave = function()
	{
		name = document.getElementById('kppyfc_name');
		if (name.value == '')
		{
			alert("Posten 'Eigenschaftsbezeichnung' ist obligatorisch!");
			name.focus();
			return false;
		}

		return true;
	}

	this.onChange = function()
	{
		btn = document.getElementById('kppyfc_cmd1');
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

var Kiwi_ProductProperty_Form = new Kiwi_ProductProperty_Form_Class();