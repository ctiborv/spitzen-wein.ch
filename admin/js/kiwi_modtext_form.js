// Disabled, because FCKeditor doesn't work well like that in IE
// (OnSelectionChange event is fired only sometimes, not on every change like in FF)

function Kiwi_ModText_Form_Class()
{
/*
	this.updateUI = function()
	{
		var oSubmit = document.getElementById('km_text_cmd1') ;
		var oNameOrig = document.getElementById('km_text_nam0');
		var oName = document.getElementById('km_text_nam');
		var submitable = false;

		if (oName.value != '')
		{
			if (oName.value == oNameOrig.value)
			{
				var editorInstance = FCKeditorAPI.GetInstance('km_text_ta1');
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
		name = document.getElementById('km_text_nam');
		if (name.value == '')
		{
			alert("Die Bezeichnung ist obligatorisch!");
			name.focus();
			return false;
		}
		return true;
	}
}

var Kiwi_ModText_Form = new Kiwi_ModText_Form_Class();

/*
function performCheck(editorInstance)
{
	Kiwi_ModText_Form.updateUI();
}

function FCKeditor_OnComplete(editorInstance)
{
	editorInstance.Events.AttachEvent('OnSelectionChange', performCheck);
}
*/

