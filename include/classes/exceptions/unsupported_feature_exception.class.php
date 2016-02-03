<?php
class Unsupported_Feature_Exception extends Exception
{
	public function __construct($message = '')
	{
		parent::__construct($message, 116);
	}
}
?>