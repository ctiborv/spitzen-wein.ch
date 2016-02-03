<?php
class No_Such_Variable_Exception extends Exception
{
	public function __construct($var_name, $class_name = null)
	{
		$var = $class_name === null ? $var_name : ($class_name . '::' . $var_name);
		parent::__construct('No such variable: ' . $var, 102);
	}
}
?>