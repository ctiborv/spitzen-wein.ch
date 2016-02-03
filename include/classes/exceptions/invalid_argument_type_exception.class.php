<?php
class Invalid_Argument_Type_Exception extends Exception
{
	public function __construct($var_name, $var_value, $class_name = null)
	{
		$var = $class_name === null ? $var_name : ($class_name . '::' . $var_name);
		parent::__construct('Invalid argument type - variable: ' . $var . ' value: ' . $var_value, 104);
	}
}
?>