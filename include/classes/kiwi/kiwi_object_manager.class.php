<?php
class Kiwi_Object_Manager
{
	protected static $_index = array(); // indexed by koi
	protected static $_cparindex = array(); // indexed by class_name and params, indirect through _index

	protected function __construct()
	{
	}

	public static function load($class_name, $params = array(), $new = false)
	{
		$key_a = $params;
		array_unshift($key_a, $class_name);
		$key = self::hash($key_a);
		if (!array_key_exists($key, self::$_cparindex) || $new)
			return self::instantiate($key, $class_name, $params);
		else
			return self::$_index[self::$_cparindex[$key]];
	}

	protected static function instantiate($key, $class_name, $params)
	{
		$ref = new ReflectionClass($class_name);
		$cls = $ref->newInstanceArgs($params);
		if ($cls instanceof Kiwi_Object)
		{
			$koi = $cls->koi;
			self::$_index[$koi] = $cls;
			self::$_cparindex[$key] = $koi;
		}
		else
			throw new Kiwi_Object_Manager_Exception('Attempt to instantiate non Kiwi_Object');

		return $cls;
	}

	public static function free(Kiwi_Object $object)
	{
		$koi = $object->koi;
		if (array_key_exists($koi, self::$_index))
		{
			$cpar = self::$_index[$koi]['cpar'];
			unset(self::$_index[$koi]);
			unset(self::$_cparindex[$cpar]);
		}
		else
			throw new Kiwi_Object_Manager_Exception('Trying to free nonexistent Kiwi_Object');
	}

	protected static function hash($ar)
	{
		return implode('#', $ar);
	}
}
?>