<?php
class HTML_Invalid_Rendering_Mode_Exception extends HTML_Builder_Exception
{
	public function __construct($render_mode, $subcode = 11)
	{
		parent::__construct("Invalid rendering mode specified: $render_mode", $subcode);
	}
}
?>