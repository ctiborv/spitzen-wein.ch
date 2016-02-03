<?php
class Kiwi_Object_Manager_Exception extends Kiwi_Exception
{
	public function __construct($message, $subcode = 7)
	{		
		parent::__construct($message, $subcode);
	}
}
?>