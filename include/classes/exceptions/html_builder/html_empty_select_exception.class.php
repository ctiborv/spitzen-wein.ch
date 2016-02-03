<?php
class HTML_Empty_Select_Exception extends HTML_Builder_Exception
{
	public function __construct($subcode = 3)
	{
		parent::__construct('Attempt to render an empty "select" element', $subcode);
	}
}
?>