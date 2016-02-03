<?php
class Navigator_Exception extends Exception
{
	const base_code = 200;

	public function __construct($message, $subcode = 0)
	{
		parent::__construct($message, self::base_code + $subcode);
	}
}
?>