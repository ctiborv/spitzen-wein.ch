function toggleMenu(mid, depth)
{
	el = document.getElementById('pg' + mid);
	el.className = (el.className=='m0') ? ('ul' + depth) : 'm0';
	return false;
}

function wakeInput(field, text)
{
	if (field.value == text) field.value = '';
}

function sleepInput(field, text)
{
	if (field.value == '') field.value = text;
}
