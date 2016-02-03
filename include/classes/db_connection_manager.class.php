<?php
/**
 * DB_Connection_Manager
 *
 * @author Tomáš Windsor 
 * @copyright 2008
 */
class DB_Connection_Manager
{
	protected $_connections;

	const DEFAULT_MYSQL_PORT = 3306;

	public function __construct()
	{
		$this->_connections = array();
	}

	public function get($name)
	{
		if (array_key_exists($name, $this->_connections))
		{
			if ($this->_connections[$name]['connected'] !== true)
				$this->connect($name);
			return $this->_connections[$name]['pdo'];
		}
		else
			throw new No_Such_DB_Connection_Exception($name);
	}

	final public function __get($name)
	{
		return $this->get($name);
	}

	public function addConnection($name, $connection_data, $connect_now = false)
	{
		if (array_key_exists($name, $this->_connections))
			throw new DB_Connection_Already_Exists_Exception($name);
		else
		{
			$this->_connections[$name] = array
			(
				'connection_data' => $connection_data,
				'pdo' => null,
				'connected' => false
			);
			if ($connect_now) $this->connect($name);
		}
	}

	protected function connect($name)
	{
		if (array_key_exists('host', $this->_connections[$name]['connection_data']))
			$host = $this->_connections[$name]['connection_data']['host'];
		else
			$host = 'localhost';

		if (array_key_exists('port', $this->_connections[$name]['connection_data']))
			$port = $this->_connections[$name]['connection_data']['port'];
		else
			$port = self::DEFAULT_MYSQL_PORT;

		if (array_key_exists('user', $this->_connections[$name]['connection_data']))
			$user = $this->_connections[$name]['connection_data']['user'];
		else
			$user = '';

		if (array_key_exists('password', $this->_connections[$name]['connection_data']))
			$password = $this->_connections[$name]['connection_data']['password'];
		else
			$password = '';

		if (array_key_exists('dbname', $this->_connections[$name]['connection_data']))
			$dbname = $this->_connections[$name]['connection_data']['dbname'];
		else
			$dbname = null;

		$this->_connections[$name]['connected'] = true;

		try
		{
			$dsn = "mysql:host=$host";
			if ($port !== null) $dsn .= ";port=$port";
			if ($dbname !== null) $dsn .= ";dbname=$dbname";

			$dbh = new MyPDO
			(
				$dsn,
				$user,
				$password,
				array(/*PDO::ATTR_PERSISTENT => true*/)
				// not using persistent connections because of buggy PDO
				// see: http://bugs.php.net/bug.php?id=42499
			);
		}
		catch (PDOException $e)
		{
			throw new DB_Connection_Failure_Exception($name);	
		}

		$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$dbh->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
		$dbh->exec('SET CHARACTER SET utf8');

		$this->_connections[$name]['pdo'] = $dbh;
	}
}
?>