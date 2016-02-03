<?php
class HTML_Content_Exception extends HTML_Builder_Exception
{
	public function __construct($message, $subcode = 1)
	{
		parent::__construct($message, $subcode);
	}
}
?>