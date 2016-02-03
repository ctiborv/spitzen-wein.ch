<?php
class Template_Exception extends Exception
{
	const base_code = 600;

	public function __construct($message, $subcode = 0)
	{
		parent::__construct($message, self::base_code + $subcode);
	}
}
?>