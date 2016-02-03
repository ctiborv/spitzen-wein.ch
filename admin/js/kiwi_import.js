/* Client-side access to querystring name=value pairs
	Version 1.3
	28 May 2008
	
	License (Simplified BSD):
	http://adamv.com/dev/javascript/qslicense.txt
*/
function Querystring(qs) { // optionally pass a querystring to parse
	this.params = {};
	
	if (qs == null) qs = location.search.substring(1, location.search.length);
	if (qs.length == 0) return;

// Turn <plus> back to <space>
// See: http://www.w3.org/TR/REC-html40/interact/forms.html#h-17.13.4.1
	qs = qs.replace(/\+/g, ' ');
	var args = qs.split('&'); // parse out name/value pairs separated via &
	
// split out each name=value pair
	for (var i = 0; i < args.length; i++) {
		var pair = args[i].split('=');
		var name = decodeURIComponent(pair[0]);
		
		var value = (pair.length==2)
			? decodeURIComponent(pair[1])
			: name;
		
		this.params[name] = value;
	}
}

Querystring.prototype.get = function(key, default_) {
	var value = this.params[key];
	return (value != null) ? value : default_;
}

Querystring.prototype.contains = function(key) {
	var value = this.params[key];
	return (value != null);
}

function log(msg)
{
	var log_ul = document.getElementById('import-log');
	var li = document.createElement('li');
	var txt = document.createTextNode(msg);
	li.appendChild(txt);
	log_ul.appendChild(li);
}

function logX(msg)
{
	var log_ul = document.getElementById('import-log');
	var li = document.createElement('li');
	var span = document.createElement('span');
	var txt = document.createTextNode(msg);
	span.appendChild(txt);
	li.appendChild(span);
	log_ul.appendChild(li);
}

function scrollToEnd()
{
	if (document.documentElement.scrollHeight)
		document.documentElement.scrollTop = document.documentElement.scrollHeight;
	else
		document.body.scrollTop = document.body.scrollHeight;
}

function getContinueLink()
{
	var link;
	var qs = new Querystring();
	if (qs.contains('continue'))
		link = window.location.href;
	else
		link = window.location.href + '&continue';
	return link;
}

function logContinueLink()
{
	var log_ul = document.getElementById('import-log');
	var li = document.createElement('li');
	var a = document.createElement('a');
	var qs = new Querystring();
	if (qs.contains('continue'))
		a.href = '';
	else
		a.href = window.location.search + '&continue';
	var txt = document.createTextNode('Weiter... (Wenn Sie das Fenster nicht schliessen, wird automatisch in 10 Sekunden weiter folgen)');
	a.appendChild(txt);
	li.appendChild(a);
	log_ul.appendChild(li);
	setTimeout('window.location=getContinueLink()', 10000);
}
