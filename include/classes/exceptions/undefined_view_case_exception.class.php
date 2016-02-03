<?php
class Undefined_View_Case_Exception extends View_Manager_Exception
{
	public function __construct($view_name, $class_name, $subcode = 2)
	{
		parent::__construct("No such view case defined in view object $class_name: $view_name", $subcode);
	}
}
?>