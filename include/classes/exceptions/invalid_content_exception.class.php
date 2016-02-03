<?php
class Invalid_Content_Exception extends Exception
{
	public function __construct($content, $expected)
	{
		parent::__construct("Invalid content: $content; expected: $expected", 121);
	}
}
?>