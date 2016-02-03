<?php
class Kiwi_Bad_Module_Type_Exception extends Kiwi_Exception
{
	public function __construct($type, $expected = null, $subcode = 2)
	{
		$expect_str = $expected === null ? '' : " ($expected was expected)";
		parent::__construct('Bad module type: ' . $type . $expect_str, $subcode);
	}
}
?>