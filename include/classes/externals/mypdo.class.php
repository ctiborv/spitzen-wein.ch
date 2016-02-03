<?php
/**
 * MyPDO
 *
 * @author Jakub Vrána
 * @copyright 2005 
 * @abstract rozšíření třídy PDO, které umožňuje jednotné předávání uživatelského jména a hesla a předávání spouštěcích parametrů metodám query() a exec()
 * @filesource http://www.root.cz/clanky/pdo-php-5-1/
 */
class MyPDO extends PDO
{
	public function __construct($dsn, $username = "", $password = "", $driver_options =
		array())
	{
		switch (preg_replace('~:.*~', '', $dsn))
		{
			case 'firebird':
				return parent::__construct("$dsn;User=$username;Password=$password", "", "", $driver_options);
			case 'odbc':
				return parent::__construct("$dsn;UID=$username;PWD=$password", "", "", $driver_options);
			case 'pgsql':
				return parent::__construct("$dsn user=$username password=$password", "", "", $driver_options);
			default:
				return parent::__construct($dsn, $username, $password, $driver_options);
		}
	}

	public function query($statement, $input_parameters = array())
	{
		$result = parent::prepare($statement);
		return ($result && $result->execute($input_parameters) ? $result : false);
	}

	public function exec($statement, $input_parameters = array())
	{
		if (empty($input_parameters))
			return parent::exec($statement);
		else
		{
			$result = parent::prepare($statement);
			return ($result && $result->execute($input_parameters) ? $result->rowCount() : false);
		}
	}
}
?>