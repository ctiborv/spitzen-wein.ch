<?php
class Project_Navigator
{
	protected static $_navigator = null;

	public function __construct()
	{
	}

	public static function get($name, $nobase = false)
	{
		if (self::$_navigator === null) self::populateNavigator();
		return self::$_navigator->get($name, $nobase);
	}

	public function __get($name)
	{
		return self::get($name);
	}

	public static function getRelative($name, $path = null)
	{
		if (self::$_navigator === null) self::populateNavigator();
		return self::$_navigator->getRelative($name, $path);
	}

	public static function getBase()
	{
		if (self::$_navigator === null) self::populateNavigator();
		return self::$_navigator->base;
	}

	public static function getPage($name, $nobase = false)
	{
		if (self::$_navigator === null) self::populateNavigator();
		return self::$_navigator->get($name, $nobase);
	}

	public static function getNavPoint($search)
	{
		if (self::$_navigator === null) self::populateNavigator();
		return self::$_navigator->getNavPoint($search);	
	}

	protected static function populateNavigator()
	{
		$config_file = 'navigator_config.inc.php';
		require 'include/config/' . $config_file;
		if (!isset($navigator_config))
			throw new Config_Exception("Config file \"$config_file\" did not define the \"\$navigator_config\" variable!");
		if (!array_key_exists('base', $navigator_config))
			throw new Config_Exception("Config file \"$config_file\" did not define the \"\base\" array field!");
		if (!array_key_exists('navpoints', $navigator_config) || !is_array($navigator_config['navpoints']))
			throw new Config_Exception("Config file \"$config_file\" did not define the \"navpoints\" subarray!");

		self::$_navigator = new Navigator();
		self::$_navigator->base = $navigator_config['base'];
		foreach ($navigator_config['navpoints'] as $pkey => $pitem)
			self::$_navigator->register($pkey, $pitem);

		self::$_navigator->lock();
	}
}