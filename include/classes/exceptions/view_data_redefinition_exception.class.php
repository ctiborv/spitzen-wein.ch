<?php
class View_Data_Redefinition_Exception extends View_Manager_Exception
{
	public function __construct($view_id, $property, $subcode = 5)
	{
		parent::__construct("Attempt to redefine property '$property' of the view object identified as: $view_id", $subcode);
	}
}
?>