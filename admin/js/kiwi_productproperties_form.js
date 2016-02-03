function Kiwi_ProductProperties_Form_Class()
{
	this.enableBtns = function(s)
	{
		cmds = new Array(2, 3, 4);
		size = cmds.length;
		cmdos = new Array(size);

		for (i = 0; i < size; i++)
			cmdos[i] = document.getElementById('kppfc_cmd' + cmds[i]);

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
}

var Kiwi_ProductProperties_Form = new Kiwi_ProductProperties_Form_Class();
