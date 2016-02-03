<?php
class HTML_Select_Not_Multiple_Exception extends HTML_Builder_Exception
{
	public function __construct($subcode = 4)
	{
		parent::__construct('Attempt to select more than one option in an non-multiple select', $subcode);
	}
}
?>