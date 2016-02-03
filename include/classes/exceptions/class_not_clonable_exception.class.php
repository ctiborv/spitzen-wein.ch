<?php
class Class_Not_Clonable_Exception extends Exception
{
	public function __construct($classname)
	{
		parent::__construct('Instances of this class cannot be cloned: ' . $classname, 115);
	}
}
?>