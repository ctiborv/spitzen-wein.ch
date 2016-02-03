function Kiwi_News_Form_Class()
{
	this.enableBtns = function(s)
	{
		cmds = new Array('knefc_cmd2', 'knefc_cmd3', 'knefc_cmd4', 'knefc_cmd5');
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
		return confirm('Sind Sie sicher, dass Sie die ausgewählten News wirklich löschen möchten?');
	}
}

var Kiwi_News_Form = new Kiwi_News_Form_Class();
