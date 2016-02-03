<?php
error_reporting(0);
require_once 'project.inc.php';

function fatalni_chyba($text)
{
	header('Content-Type: text/html; charset=utf8');
	die($text);
}

if (!$link = mysql_connect($kiwi_config['db_server'], $kiwi_config['db_user'], $kiwi_config['db_password']))
	fatalni_chyba('Chyba připojování k databázovému serveru.');

if (!mysql_select_db($kiwi_config['db_name'], $link))
	fatalni_chyba('Chyba při výběru databáze.');

mysql_query("SET NAMES 'utf8'");
?>
