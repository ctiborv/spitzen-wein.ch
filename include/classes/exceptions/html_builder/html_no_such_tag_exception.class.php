<?php
class HTML_No_Such_Tag_Exception extends HTML_Builder_Exception
{
	public function __construct($var_name, $subcode = 9)
	{
		parent::__construct('No such tag: ' . $var_name, $subcode);
	}
}
?>