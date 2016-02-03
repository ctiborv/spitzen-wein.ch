function Kiwi_Product_Form_Class()
{
	this.getDirtyElem = function()
	{
		var dirty = document.getElementById('kprofc_dirty');
		if (dirty == null)
		{
			var fieldset = document.getElementById('kprofc_fs');
			dirty = document.createElement('input');
			dirty.setAttribute('id', 'kprofc_dirty');
			dirty.setAttribute('name', 'dirty');
			dirty.setAttribute('type', 'hidden');
			fieldset.appendChild(dirty);
		}

		return dirty;
	}

	this.checkFCKDirty = function()
	{
		var fcks = ['ldsc'];
		var i, eI;
		for (i in fcks)
		{
			eI = FCKeditorAPI.GetInstance('kprofc_' + fcks[i]);
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

	this.onCopy = function()
	{
		if (this.isDirty())
			return confirm("Das Kopieren des Produkts verlieren Sie alle nicht gespeicherten Änderungen. Wollen Sie das Produkt kopieren?");
	}

	this.onSave = function()
	{
		var title = document.getElementById('kprofc_title');
		if (title.value == '')
		{
			alert('Der Name des Produkts ist obligatorisch!');
			title.focus();
			return false;
		}

		var auto = document.getElementById('kprofc_auto');
		var url = document.getElementById('kprofc_url');
		if (url)
		{
			if (!auto.checked && url.value == '')
			{
				alert('Im Falle der nicht nutzung der automatischen URL und Titel ist der URL obligatorisch!');
				url.focus();
				return false;
			}
		}

		var cost1 = document.getElementById('kprofc_cost1');
		if (!this.isFloat(cost1.value))
		{
			alert('Ungültiger Wert für das Feld "Original Preis"!');
			cost1.focus();
			return false;
		}

		var cost2 = document.getElementById('kprofc_cost2');
		if (!this.isFloat(cost2.value))
		{
			alert('Ungültiger Wert für das Feld "Unsere Preis"!');
			cost2.focus();
			return false;
		}

		var wscost = document.getElementById('kprofc_wscost');
		if (!this.isFloat(wscost.value))
		{
			alert('Ungültiger Wert für das Feld "Grosshandelpreis"!');
			wscost.focus();
			return false;
		}

		return true;
	}

	this.onBack = function(link)
	{
		document.location.href = link;
		return true;
	}

	this.onGroupProduct = function(script_qs)
	{
		if (this.isDirty())
			if (!confirm("Nicht gespeicherte Änderungen werden verloren.\nWeiter?"))
				return false;
		document.location.href = script_qs + '&eg';
	}

	this.onRemovePhoto = function(type, pi, script_qs)
	{
		var qc;
		if (type != '')
			qc = 'rp' + type + '=' + pi;
		else
			qc = 'rp';
		if (this.isDirty())
			if (!confirm("Entfernen des Bildes verlieren Sie alle nicht gespeicherten Änderungen.\nWollen Sie das Bild löschen?"))
				return false;

		document.location.href = script_qs + '&' + qc;
	}

	this.onChange = function()
	{
		this.makeDirty();
	}

	this.onChangeAuto = function()
	{
		var cb = document.getElementById('kprofc_auto');
		var url = document.getElementById('kprofc_url');
		var htitle = document.getElementById('kprofc_htitle');
		var dis = cb.checked;
		var cls = cb.checked ? 'inpOFF': 'inpOUT';
		url.disabled = dis;
		url.className = cls;
		htitle.disabled = dis;
		htitle.className = cls;
		this.onChange();
	}

	this.onChangeValue = function(propid, sid)
	{
		var elem = document.getElementById('kprofc_propt' + propid)
		if (elem == 0) return;

		if (sid == -1)
		{
			elem.className = 'inpOUT';
			elem.focus();
		}
		else
		{
			elem.className = 'invisible';
		}
	}

	this.onAddValue = function(propid, script_qs)
	{
		var sid = document.getElementById('kprofc_prop' + propid);
		var tid = document.getElementById('kprofc_propt' + propid);

		if (sid.value < 1 && tid.value == '')
		{
			alert('Wählen Sie zuerst einen Wert aus dem Menü "Eigenschaften". Eventuell wählen Sie ein anderes Wert und geben Sie den Wert manuell!');
			if (sid.value == -1) tid.focus();
			else sid.focus();
			return;
		}

		if (this.isDirty())
			if (!confirm("Hinzufügen der Eigenschaft, verlieren Sie alle nicht gespeicherten Änderungen.\nMöchten Sie wirklich die Eigenschaft des Produkt einfügen?"))
				return false;

		var qs;
		if (sid.value > 0)
			qs = 'apv=' + sid.value;
		else
			qs = 'anpv=' + propid + ':' + encodeURIComponent(tid.value);

		document.location.href = script_qs + '&' + qs;
	}

	this.onRemValue = function()
	{
		if (this.isDirty())
			return confirm("Entfernen der Eigenschaft verlieren Sie alle nicht gespeicherten Änderungen.\nWollen Sie wirklich die Eigenschaft löschen?");
		return true;
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

	this.isFloat = function(s)
	{
		var reFloat = /^((\d+(\.\d*)?)|((\d*\.)?\d+))$/
		return reFloat.test(s)
	}
}

var Kiwi_Product_Form = new Kiwi_Product_Form_Class();

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

$(document).ready(function(e) {
	try {
		$("select.msdropdown").msDropDown();
	} catch (e) {
		alert(e.message);
	}
});
