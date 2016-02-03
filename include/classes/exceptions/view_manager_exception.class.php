<?php
class View_Manager_Exception extends Exception
{
	const base_code = 400;

	public function __construct($message, $subcode = 0)
	{
		parent::__construct($message, self::base_code + $subcode);
	}
}
?>