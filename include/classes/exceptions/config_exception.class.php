<?php
class Config_Exception extends Exception
{
	public function __construct($message, $code = 120)
	{
		parent::__construct($message, $code);
	}
}
?>