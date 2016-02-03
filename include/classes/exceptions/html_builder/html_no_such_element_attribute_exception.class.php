<?php
class HTML_No_Such_Element_Attribute_Exception extends HTML_Builder_Exception
{
	public function __construct($var_name, $subcode = 2)
	{
		parent::__construct('No such html element attribute: ' . $var_name, 2);
	}
}
?>