<?php
class HTML_Parser_Exception extends HTML_Builder_Exception
{
	public function __construct($message, $subcode = 8)
	{
		parent::__construct($message, $subcode);
	}
}
?>