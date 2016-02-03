function Kiwi_Modules_Form_Class()
{
	this.enableBtns = function(s)
	{
		cmds = new Array('kmofc_cmd2', 'kmofc_cmd3', 'kmofc_cmd4', 'kmofc_cmd5');
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
		return confirm('Sind Sie sicher, dass Sie die ausgewählten Module löschen möchten?');
	}
}

var Kiwi_Modules_Form = new Kiwi_Modules_Form_Class();
