<?php
class HTML_Input_Exception extends HTML_Builder_Exception
{
	public function __construct($message, $subcode = 5)
	{
		parent::__construct($message, $subcode);
	}
}
?>