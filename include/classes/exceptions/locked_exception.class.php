<?php
class Locked_Exception extends Exception
{
	public function __construct($classname)
	{
		parent::__construct('Attempt to modify locked object: ' . $classname, 118);
	}
}
?>