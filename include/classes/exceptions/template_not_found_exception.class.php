<?php
class Template_Not_Found_Exception extends Template_Exception
{
	public function __construct($message, $subcode = 4)
	{
		parent::__construct($message, $subcode);
	}
}
?>