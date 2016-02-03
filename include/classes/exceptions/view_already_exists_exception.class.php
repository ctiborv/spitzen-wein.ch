<?php
class View_Already_Exists_Exception extends View_Manager_Exception
{
	public function __construct($view_id, $subcode = 4)
	{
		parent::__construct("View object with this id already exists: $view_id", $subcode);
	}
}
?>