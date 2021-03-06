<?php
class View_Not_Initialized_Exception extends View_Manager_Exception
{
	public function __construct($view_class, $view_id = null, $subcode = 6)
	{
		$vid_str = $view_id === null ? '' : " identified as '$view_id'";
		parent::__construct("View '$view_class'$vid_str not initialized", $subcode);
	}
}
?>