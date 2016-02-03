// Disabled, because FCKeditor doesn't work well like that in IE
// (OnSelectionChange event is fired only sometimes, not on every change like in FF)

function Kiwi_NewsItem_Form_Class()
{
/*
	this.updateUI = function()
	{
		var oSubmit = document.getElementById('kni_cmd1') ;
		var oNameOrig = document.getElementById('kni_nam0');
		var oName = document.getElementById('kni_nam');
		var submitable = false;

		if (oName.value != '')
		{
			if (oName.value == oNameOrig.value)
			{
				var editorInstance = FCKeditorAPI.GetInstance('kni_ta1');
				submitable = editorInstance.IsDirty();
			}
			else submitable = true;
		}

		if (submitable)
		{
			oSubmit.disabled = false;
			oSubmit.className = 'but3';
		}
		else
		{
			oSubmit.disabled = true;
			oSubmit.className = 'but3D';
		}
	}
*/
	this.onSave = function()
	{
		name = document.getElementById('kni_nam');
		author = document.getElementById('kni_author');
		when = document.getElementById('kni_when');
		start = document.getElementById('kni_start');
		end = document.getElementById('kni_end');

		if (name.value == '')
		{
			alert('Die Bezeichnung ist obligatorisch!');
			name.focus();
			return false;
		}

		if (author.value == '')
		{
			alert('Der Autor ist obligatorisch!');
			author.focus();
			return false;
		}

		if (when.value == '')
		{
			alert('Datum- News ist obligatorisch!');
			when.focus();
			return false;
		}
		else if (!isDate(when.value))
		{
			alert('Datum- News ist nicht korrekt');
			when.focus();
			return false;
		}

		if (start.value == '')
		{
			alert("Posten 'Erscheinen ab' ist obligatorisch!");
			start.focus();
			return false;
		}
		else if (!isDate(start.value))
		{
			alert("Datum 'Erscheinen ab' ist nicht korrekt!");
			start.focus();
			return false;
		}

		if (end.value == '')
		{
			alert("Posten 'Erscheinen bis' ist obligatorisch!");
			end.focus();
			return false;
		}
		else if (!isDate(end.value))
		{
			alert("Datum 'Erscheinen bis' ist nicht korrekt!");
			end.focus();
			return false;
		}

		return true;
	}
}

var Kiwi_NewsItem_Form = new Kiwi_NewsItem_Form_Class();

function FCKeditor_OnBlur(editorInstance)
{
	editorInstance.ToolbarSet.Collapse();
}

function FCKeditor_OnFocus(editorInstance)
{
	editorInstance.ToolbarSet.Expand();
}

/*
function performCheck(editorInstance)
{
	Kiwi_NewsItem_Form.updateUI();
}
*/

function FCKeditor_OnComplete(editorInstance)
{
//	editorInstance.Events.AttachEvent('OnSelectionChange', performCheck);
	editorInstance.Events.AttachEvent('OnBlur', FCKeditor_OnBlur);
	editorInstance.Events.AttachEvent('OnFocus', FCKeditor_OnFocus);
}

function isDate(dateStr)
{
	datePat = /^(\d{1,2})\.(\d{1,2})\.(\d{4})$/

	matchArray = dateStr.match(datePat);
	if (matchArray == null) return false;

	day = matchArray[1];
	month = matchArray[2];
	year = matchArray[3];

	if (month < 1 || month > 12) return false;
	if (day < 1 || day > 31) return false;
	if ((month==4 || month==6 || month==9 || month==11) && day==31) return false;
	if (month == 2)
	{
		isleap = (year % 4 == 0 && (year % 100 != 0 || year % 400 == 0));
		if (day > 29 || (day==29 && !isleap)) return false;
	}
	return true; // date is valid
}
