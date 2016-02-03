<?php
class Template_Invalid_Argument_Exception extends Template_Exception
{
	public function __construct($name, $value, $subcode = 2)
	{
		parent::__construct("Invalid value of template argument '$name': $value", $subcode);
	}
}
?>