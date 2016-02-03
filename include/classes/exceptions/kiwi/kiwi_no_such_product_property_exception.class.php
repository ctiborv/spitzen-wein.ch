<?php
class Kiwi_No_Such_Product_Property_Exception extends Kiwi_Exception
{
	public function __construct($identification, $subcode = 4)
	{
		parent::__construct('No product property with: ' . $identification, $subcode);
	}
}
?>