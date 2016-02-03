<?php
class Data_Superfluous_Exception extends Exception
{
	public function __construct($resource1, $resource2)
	{
		parent::__construct("Data superfluous: $resource1 & $resource2", 113);
	}
}
?>