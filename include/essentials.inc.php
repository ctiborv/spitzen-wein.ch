<?php
require_once 'mod_debug_handlers.inc.php';
require_once 'utils.inc.php';
require_once 'autoload.inc.php';

if (!extension_loaded('pdo'))
	throw new Feature_Not_Available_Exception('PDO module not installed');

try
{
	date_default_timezone_set(Project_Config::get('timezone'));
}
catch (No_Such_Variable_Exception $e)
{
}

try
{
	setlocale(LC_ALL, Project_Config::get('locale'));
}
catch (No_Such_Variable_Exception $e)
{
}

try
{
	setlocale(LC_NUMERIC, Project_Config::get('locale_numeric'));
}
catch (No_Such_Variable_Exception $e)
{
}
?>