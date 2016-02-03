function log(msg)
{
	var log_ul = document.getElementById('export-log');
	var li = document.createElement('li');
	var txt = document.createTextNode(msg);
	li.appendChild(txt);
	log_ul.appendChild(li);
}

function logX(msg)
{
	var log_ul = document.getElementById('export-log');
	var li = document.createElement('li');
	var span = document.createElement('span');
	var txt = document.createTextNode(msg);
	span.appendChild(txt);
	li.appendChild(span);
	log_ul.appendChild(li);
}

function logLink(msg, link)
{
	var log_ul = document.getElementById('export-log');
	var li = document.createElement('li');
	var a = document.createElement('a');
	var br = document.createElement('br');
	var txt = document.createTextNode(msg);
	var a_txt = document.createTextNode(link);
	a.href = link;
	a.appendChild(a_txt);
	li.appendChild(txt);
	li.appendChild(br);
	li.appendChild(a);
	log_ul.appendChild(li);
}

function scrollToEnd()
{
	if (document.documentElement.scrollHeight)
		document.documentElement.scrollTop = document.documentElement.scrollHeight;
	else
		document.body.scrollTop = document.body.scrollHeight;
}