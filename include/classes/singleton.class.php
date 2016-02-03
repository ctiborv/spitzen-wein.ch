<?php
if (!function_exists('get_called_class'))
{
	class class_tools
	{
		static $i = 0;
		static $fl = null;

		static function get_called_class()
		{
			$bt = debug_backtrace();

			if (self::$fl == $bt[2]['file'].$bt[2]['line'])
				self::$i++;
			else
			{
				self::$i = 0;
				self::$fl = $bt[2]['file'].$bt[2]['line'];
			}

			$lines = file($bt[2]['file']);

			preg_match_all('/([a-zA-Z0-9\_]+)::'.$bt[2]['function'].'/',
				$lines[$bt[2]['line']-1],
				$matches);

			if (self::$i >= count($matches[1]))
				self::$i = count($matches[1]) - 1; // temporary workaround by Duke (not thoroughly tested)
			return $matches[1][self::$i];
		}
	}

	function get_called_class()
	{
		return class_tools::get_called_class();
	}
}


class Singleton
{
	protected static $_instances = array();

	protected final function __construct()
	{
		$this->initialize();
	}

	protected function initialize()
	{
	}

	public static function getInstance()
	{
		$_class = get_called_class();
		if (!array_key_exists($_class, self::$_instances))
			self::$_instances[$_class] = new $_class;

		return self::$_instances[$_class];
	}

	public function __clone()
	{
		throw new Exception('Cannot clone singleton object');
	}
}

?>