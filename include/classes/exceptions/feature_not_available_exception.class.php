<?php
class Feature_Not_Available_Exception extends Exception
{
	public function __construct($message)
	{
		parent::__construct("Feature not available: $message", 100);
	}
}
?>