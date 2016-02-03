<?php
class Unsupported_Redirection_Code_Exception extends Exception
{
	public function __construct($code)
	{
		parent::__construct("Unsupported HTTP redirection code: $code", 108);
	}
}
?>