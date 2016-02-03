<?php
class Project_Config
{
	protected static $_config = null;

	public function __construct()
	{
	}

	public static function get($name)
	{
		if (self::$_config === null) self::populateConfig();
		return self::$_config->$name;
	}

	final public function __get($name)
	{
		return self::get($name);
	}

	final public function __isset($name)
	{
		if (self::$_config === null) self::populateConfig();
		return isset(self::$_config->$name);
	}

	protected static function populateConfig()
	{
		require __DIR__ . '/../config/project_config.inc.php';
		if (!isset($config) || !is_array($config))
			throw new Config_Exception('Config file "project_config.inc.php" did not define the "config" array!');

		self::$_config = new Var_Pool();
		foreach ($config as $ckey => $citem)
			self::$_config->register($ckey, $citem);

		self::$_config->lock();
	}
}