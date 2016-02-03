<?php
class HTML_Form_Exception extends HTML_Builder_Exception
{
	public function __construct($message, $subcode = 6)
	{
		parent::__construct($message, $subcode);
	}
}
?>