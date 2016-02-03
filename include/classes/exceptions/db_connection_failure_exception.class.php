<?php
class DB_Connection_Failure_Exception extends Exception
{
	public function __construct($connection_name)
	{
		parent::__construct('DB connection failed: ' . $connection_name, 109);
	}
}
?>