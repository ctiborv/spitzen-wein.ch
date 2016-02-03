<?php
/**
 * Project_DB
 *
 * @author Tomáš Windsor 
 * @copyright 2008
 * @abstract Manages the project DB connections 
 */
class Project_DB
{
	protected static $_dbcm = null;

	public function __construct()
	{
	}

	public static function get($name = 'main')
	{
		if (self::$_dbcm === null) self::createDBCM();
		return self::$_dbcm->$name;
	}

	public function __get($name)
	{
		return self::get($name);
	}

	protected static function createDBCM()
	{
		$config = new Project_Config;
		self::$_dbcm = new DB_Connection_Manager;
		$dbs = $config->db;
		foreach ($dbs as $name => $connection_data)
			self::$_dbcm->addConnection($name, $connection_data);
	}
}
?>