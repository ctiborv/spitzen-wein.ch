<?php
class Template_Invalid_Structure_Exception extends Template_Exception
{
	public function __construct($message, $subcode = 3)
	{
		parent::__construct($message, $subcode);
	}
}
?>