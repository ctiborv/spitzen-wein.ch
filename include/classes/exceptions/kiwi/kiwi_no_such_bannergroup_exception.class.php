<?php
class Kiwi_No_Such_BannerGroup_Exception extends Kiwi_Exception
{
	public function __construct($identification, $subcode = 12)
	{
		parent::__construct('No banner group with: ' . $identification, $subcode);
	}
}
?>