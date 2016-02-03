<?php
class Headers_Already_Sent_Exception extends Exception
{
	public function __construct()
	{
		parent::__construct('HTTP headers already sent', 101);
	}
}
?>