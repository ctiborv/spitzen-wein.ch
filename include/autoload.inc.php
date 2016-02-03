<?php
function __autoload($class_name)
{
	$config_file = 'autoload_config.inc.php';
	require 'config/' . $config_file;
	if (!isset($by_suffix) || !is_array($by_suffix))
		throw new Config_Exception("Config file \"$config_file\" did not define the \"\$by_suffix\" array!");
	if (!isset($by_prefix) || !is_array($by_prefix))
		throw new Config_Exception("Config file \"$config_file\" did not define the \"\$by_prefix\" array!");
	if (!isset($externals) || !is_array($externals))
		throw new Config_Exception("Config file \"$config_file\" did not define the \"\$externals\" array!");

	$cn_low = strtolower($class_name);
	$include_dir = 'classes/';

	if (array_key_exists($cn_low, $externals))
	{
		$include_dir .= 'externals/';
		require_once $include_dir . $externals[$cn_low];
	}
	else
	{	
		foreach ($by_suffix as $suffix => $dir)
		{
			$slen = strlen($suffix);
			if ($suffix == substr($cn_low, -$slen))
			{
				$include_dir .= $dir . '/';
				break;
			}
		}

		foreach ($by_prefix as $prefix => $dir)
		{
			$plen = strlen($prefix);
			if ($prefix == substr($cn_low, 0, $plen))
			{
				$include_dir .= $dir . '/';
				break;
			}
		}
		
		require_once "$include_dir$cn_low.class.php";
	}
}
?>