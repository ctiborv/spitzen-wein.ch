<?php
require_once 'include/utils.inc.php';
require_once 'include/kiwi_exception.inc.php';
require_once 'include/project.inc.php';
require_once 'include/dbc.inc.php';
require_once 'include/kiwi_modules.inc.php';

$m = $mi = $smi = $t = 0;

// Zpracování vstupů
if (isset($_GET['m']))
	if (($m = (int)$_GET['m']) < 1)
		throw new Exception("Neplatná hodnota parametru \"m\": $m");

if (isset($_GET['mi']))
	if (($mi = (int)$_GET['mi']) < 1)
		throw new Exception("Neplatná hodnota parametru \"mi\": $mi");

if (isset($_GET['smi']))
	if (($smi = (int)$_GET['smi']) < 1)
		throw new Exception("Neplatná hodnota parametru \"smi\": $smi");

if (isset($_GET['t']))
	if (!isValidModuleType($t = (int)$_GET['t']))
		throw new Exception("Neplatná hodnota parametru \"t\": $t");

if ($m == 0 && $t == 0)
	throw new Exception("Nedostatek vstupních dat pro zpracování skriptu");

if ($m != 0 && $t != 0)
	throw new Exception("Nepřípustná kombinace vstupních parametrů");

if ($m)
{
	$result = mysql_query("SELECT Type FROM modules WHERE ID=$m");
	if ($row = mysql_fetch_row($result))
		$mtype = $row[0];
	else
		throw new Exception("Neplatný identifikátor modulu");

	$page = getModulePage($mtype) . "?m=$m";
	if ($mi) $page .= "&mi=$mi";
	if ($smi) $page .= "&smi=$smi";
}
else
{
	$page = getModulePage($t);
	if ($mi) $page .= "?mi=$mi";
	if ($smi) $page .= "&smi=$smi";
}

redirectPage($page);
?>
