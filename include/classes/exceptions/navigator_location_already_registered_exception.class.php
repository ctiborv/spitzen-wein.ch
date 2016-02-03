<?php
class Navigator_Location_Already_Registered_Exception extends Navigator_Exception
{
	public function __construct($location)
	{
		$message = 'Location already registered: ' . $location;
		parent::__construct($message, 2);
	}
}
?>