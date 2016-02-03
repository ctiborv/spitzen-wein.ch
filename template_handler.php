<?php
// gzip support
if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip'))
	ob_start("ob_gzhandler");
else
	ob_start();

if (!isset($_GET['template']))
{
	header("Content-Type: text/html; charset=UTF-8");
	echo 'ERROR: template query string variable not set!';
	die();
}

require_once 'include/essentials.inc.php';

$template = $_GET['template'];
if ($template == '') $template = 'index';

// po implementaci a začlenění statické třídy Server by následující nemělo být zapotřebí
//$_SERVER['QUERY_STRING'] = preg_replace("/^template=[^&]*&?(.*)$/", "$1", $_SERVER['QUERY_STRING']);

Template_Handler::render($template);

header("Content-Type: text/html; charset=UTF-8");
// header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
// header("Pragma: no-cache"); // HTTP/1.0

echo Template_Handler::getRenderer()->text;
?>