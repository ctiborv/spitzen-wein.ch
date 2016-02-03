<?php
class Root_Tag_Not_Found_Exception extends View_Manager_Exception
{
	public function __construct($tag, $subcode = 6)
	{
		parent::__construct("No \"$tag\" element found", $subcode);
	}
}
?>