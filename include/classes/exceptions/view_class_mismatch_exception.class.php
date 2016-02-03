<?php
class Undefined_View_Exception extends View_Manager_Exception
{
	public function __construct($view_id, $class1, $class2, $subcode = 3)
	{
		parent::__construct("View class mismatch: '$class1' vs '$class2' with view id: $view_id", $subcode);
	}
}
?>