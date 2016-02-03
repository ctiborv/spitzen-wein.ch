function checkUncheckAll(field, valor)
{
	for (i = 0; i < field.length; i++)
		field[i].checked = valor.checked;
}

function checkCount(field)
{
	fields = document.getElementsByName(field);
	c = 0;
	for (i = 0; i < fields.length; i++)
		if (fields[i].checked) c++;

	return c;
}

function addEvent(elm, evType, fn, useCapture)
{
	if (elm.addEventListener)
	{
		elm.addEventListener(evType, fn, useCapture);
		return true;
	}
	else if (elm.attachEvent)
	{
		var r = elm.attachEvent('on' + evType, fn);
		return r;
	}
	else
		elm['on' + evType] = fn;
}