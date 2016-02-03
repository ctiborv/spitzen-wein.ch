<?php
class DB_Connection_Already_Registered_Exception extends Exception
{
	public function __construct($connection_name)
	{
		parent::__construct('DB connection already registered: ' . $connection_name, 111);
	}
}
?>