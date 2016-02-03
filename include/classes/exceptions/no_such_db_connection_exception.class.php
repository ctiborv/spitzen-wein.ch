<?php
class No_Such_DB_Connection_Exception extends Exception
{
	public function __construct($connection_name)
	{
		parent::__construct('No such DB connection: ' . $connection_name, 110);
	}
}
?>