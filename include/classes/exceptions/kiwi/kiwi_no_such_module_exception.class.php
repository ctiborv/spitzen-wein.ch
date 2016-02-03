<?php
class Kiwi_No_Such_Module_Exception extends Kiwi_Exception
{
	public function __construct($identification, $subcode = 1)
	{
		parent::__construct('No module with: ' . $identification, $subcode);
	}
}
?>