<?php
class HTML_Builder_Exception extends Exception
{
	const base_code = 300;

	public function __construct($message, $subcode = 0)
	{
		parent::__construct($message, self::base_code + $subcode);
	}
}
?>