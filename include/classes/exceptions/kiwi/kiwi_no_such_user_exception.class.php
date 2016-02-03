<?php
class Kiwi_No_Such_User_Exception extends Kiwi_Exception
{
	public function __construct($identification, $subcode = 8)
	{
		parent::__construct('No user with: ' . $identification, $subcode);
	}
}
?>