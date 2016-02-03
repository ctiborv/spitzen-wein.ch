<?php
class Kiwi_No_Such_Product_Exception extends Kiwi_Exception
{
	public function __construct($identification, $subcode = 3)
	{
		parent::__construct('No product with: ' . $identification, $subcode);
	}
}
?>