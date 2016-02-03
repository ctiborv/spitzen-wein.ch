<?php
class Undefined_View_Exception extends View_Manager_Exception
{
	public function __construct($view_id, $subcode = 1)
	{
		parent::__construct("No view with such id: $view_id", $subcode);
	}
}
?>