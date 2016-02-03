<?php
class Kiwi_Exception extends Exception
{
	const base_code = 500;

	public function __construct($message, $subcode = 0)
	{
		parent::__construct($message, self::base_code + $subcode);
	}
}
?>