<?php
class Variable_Already_Registered_Exception extends Exception
{
	public function __construct($var_name, $class_name = null)
	{
		$var = $class_name === null ? $var_name : ($class_name . '::' . $var_name);
		parent::__construct('Variable already registered: ' . $var, 106);
	}
}
?>