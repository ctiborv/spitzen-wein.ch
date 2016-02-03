function Kiwi_Orders_Form_Class()
{
	this.enableBtns = function(s)
	{
		cmds = [1];
		size = cmds.length;
		cmdos = new Array(size);

		for (i = 0; i < size; i++)
			cmdos[i] = document.getElementById('korfc_cmd' + cmds[i]);

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
		return confirm('Sind Sie sicher, dass Sie die ausgewählten Bestellungen löschen möchten?');
	}
}

var Kiwi_Orders_Form = new Kiwi_Orders_Form_Class();
