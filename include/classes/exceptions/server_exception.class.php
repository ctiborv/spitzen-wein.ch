<?php
class Server_Exception extends Exception
{
	public function __construct($message)
	{
		parent::__construct($message, 119);
	}
}
?>