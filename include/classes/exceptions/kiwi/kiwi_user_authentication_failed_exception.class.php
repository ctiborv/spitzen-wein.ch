<?php
class Kiwi_User_Authentication_Failed_Exception extends Kiwi_Exception
{
	public function __construct($message, $subcode = 11)
	{
		parent::__construct('Authentication failed: ' . $message, $subcode);
	}
}
?>