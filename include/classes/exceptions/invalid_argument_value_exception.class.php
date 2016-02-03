<?php
class Invalid_Argument_Value_Exception extends Exception
{
	public function __construct($var_name, $var_value, $class_name = null)
	{
		$var = $class_name === null ? $var_name : ($class_name . '::' . $var_name);
		parent::__construct('Invalid argument value - variable: ' . $var . ' value: ' . $var_value, 105);
	}
}
?>