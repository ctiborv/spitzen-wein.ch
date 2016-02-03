<?php
class Kiwi_Username_Duplicity_Exception extends Kiwi_Exception
{
	public function __construct($username, $subcode = 9)
	{
		parent::__construct('User duplicity: ' . $username, $subcode);
	}
}
?>