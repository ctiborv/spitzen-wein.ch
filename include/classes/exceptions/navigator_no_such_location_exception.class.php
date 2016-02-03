<?php
class Navigator_No_Such_Location_Exception extends Navigator_Exception
{
	public function __construct($location)
	{
		$message = 'No such location registered: ' . $location;
		parent::__construct($message, 1);
	}
}
?>