function Kiwi_Newsletter_Form_Class()
{
	this.enableBtns = function(s)
	{
		var cmds = new Array('5');
		var size = cmds.length;
		var cmdos = new Array(size);

		for (var i = 0; i < size; i++)
			cmdos[i] = document.getElementById('knlrfc_cmd' + cmds[i]);

		var d, cn;
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

	this.getDirtyElem = function()
	{
		var dirty = document.getElementById('knlrfc_dirty');
		if (dirty == null)
		{
			var fieldset = document.getElementById('knlrfc_fs');
			dirty = document.createElement('input');
			dirty.setAttribute('id', 'knlrfc_dirty');
			dirty.setAttribute('name', 'dirty');
			dirty.setAttribute('type', 'hidden');
			fieldset.appendChild(dirty);
		}

		return dirty;
	}

	this.checkFCKDirty = function()
	{
		var fcks = ['content'];
		var i, eI;
		for (i in fcks)
		{
			eI = FCKeditorAPI.GetInstance('knlrfc_' + fcks[i]);
			if (eI.IsDirty())
			{
				this.makeDirty();
				return;
			}
		}
	}

	this.isDirty = function()
	{
		var dirty = this.getDirtyElem();
		if (dirty.value == '')
			this.checkFCKDirty();
		return dirty.value != '';
	}

	this.makeDirty = function()
	{
		var dirty = this.getDirtyElem();
		dirty.value = '1';
	}

	this.onSave = function()
	{
		var title = document.getElementById('knlrfc_title');
		if (title.value == '')
		{
			alert('Der Name des Newsletter ist obligatorisch!');
			title.focus();
			return false;
		}

		var start = document.getElementById('knlrfc_start');
		if (start.value == '')
		{
			alert("Posten 'Start' ist obligatorisch!");
			start.focus();
			return false;
		}
		else if (!isDateTime(start.value))
		{
			alert("Datum und Uhrzeit 'Start' ist nicht korrekt!");
			start.focus();
			return false;
		}

		return true;
	}

	this.onBack = function(link)
	{
		document.location.href = link;
		return true;
	}

	this.onChange = function()
	{
		this.makeDirty();
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

		for (var i = 0; i < slen; i++)
			if (keynum == skip[i]) return;

		this.onChange();
	}

	this.onKeyDownAuto = function(e)
	{
		var keynum;
		if (window.event) // IE
			keynum = e.keyCode;
		else if (e.which) // Netscape/Firefox/Opera
			keynum = e.which;

		if (keynum == 32) this.onChangeAuto();
	}

	this.onDelete = function()
	{
		return confirm('Sind Sie sicher, dass Sie ausgewählte Posten löschen möchten?');
	}
}

var Kiwi_Newsletter_Form = new Kiwi_Newsletter_Form_Class();

function FCKeditor_OnComplete(editorInstance)
{
	editorInstance.Events.AttachEvent('OnBlur', FCKeditor_OnBlur);
	editorInstance.Events.AttachEvent('OnFocus', FCKeditor_OnFocus);
}

function FCKeditor_OnBlur(editorInstance)
{
	editorInstance.ToolbarSet.Collapse();
}

function FCKeditor_OnFocus(editorInstance)
{
	editorInstance.ToolbarSet.Expand();
}

function isDateTime(dateTimeStr)
{
	var datePat = /^(\d{1,2})\.(\d{1,2})\.(\d{4})( (\d{1,2}):(\d{2}))?$/

	var matchArray = dateTimeStr.match(datePat);
	if (matchArray == null) return false;

	var day = matchArray[1];
	var month = matchArray[2];
	var year = matchArray[3];

	var hour = matchArray[5];
	var minute = matchArray[6];

	if (month < 1 || month > 12) return false;
	if (day < 1 || day > 31) return false;
	if ((month==4 || month==6 || month==9 || month==11) && day==31) return false;
	if (month == 2)
	{
		isleap = (year % 4 == 0 && (year % 100 != 0 || year % 400 == 0));
		if (day > 29 || (day==29 && !isleap)) return false;
	}
	if (hour > 23) return false;
	if (minute > 59) return false;

	return true; // date is valid
}

$(document).ready(function()
{
	$('#knlrfc_start').datetimepicker({
		'stepMinute': 15
	});
});
