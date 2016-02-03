<?php
class Kiwi_No_Such_Client_Exception extends Kiwi_Exception
{
	public function __construct($identification, $subcode = 10)
	{
		parent::__construct('No client with: ' . $identification, $subcode);
	}
}
?>