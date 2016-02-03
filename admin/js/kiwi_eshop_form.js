function Kiwi_EShop_Form_Class()
{
	this.enableBtns = function(s)
	{
		cmds = new Array('kesfc_cmd4', 'kesfc_cmd5', 'kesfc_cmd6');
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

	this.onRemoveIcon = function(script_qs)
	{
		var qc = 'ri';
		btn = document.getElementById('kesfc_cmd1');
		if (btn.disabled)
			document.location.href = script_qs + '&' + qc;
		else if (confirm("Entfernen des Bildes verlieren Sie alle nicht gespeicherten Änderungen.\nWollen Sie das Bild löschen?"))
			document.location.href = script_qs + '&' + qc;
	}

	this.onDelete = function()
	{
		return confirm('Sind Sie sicher, dass Sie ausgewählte Posten löschen möchten?');
	}

	this.onSave = function()
	{
		name = document.getElementById('kesfc_name');
		if (name.value == '')
		{
			alert("Posten 'Menübezeichnung' ist obligatorisch!");
			name.focus();
			return false;
		}

		auto = document.getElementById('kesfc_auto');
		url = document.getElementById('kesfc_url');
		if (url)
		{
			if (!auto.checked && url.value == '')
			{
				alert('Im Falle der nicht nutzung der automatischen URL und Titel ist der URL obligatorisch!');
				url.focus();
				return false;
			}
		}

		return true;
	}

	this.onChange = function()
	{
		btn = document.getElementById('kesfc_cmd1');
		btn.disabled = false;
		btn.className = 'but3';
	}

	this.onChangeAuto = function()
	{
		cb = document.getElementById('kesfc_auto');
		url = document.getElementById('kesfc_url');
		htitle = document.getElementById('kesfc_htitle');
		dis = cb.checked;
		cls = cb.checked ? 'inpOFF': 'inpOUT';
		url.disabled = dis;
		url.className = cls;
		htitle.disabled = dis;
		htitle.className = cls;
		this.onChange();
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

	this.onKeyDownAuto = function(e)
	{
		if (window.event) // IE
			keynum = e.keyCode;
		else if (e.which) // Netscape/Firefox/Opera
			keynum = e.which;

		if (keynum == 32) this.onChangeAuto();
	}
}

var Kiwi_EShop_Form = new Kiwi_EShop_Form_Class();