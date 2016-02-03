<?php
class Navigator_Unregistered_Location_Exception extends Navigator_Exception
{
	public function __construct($target)
	{
		$message = 'Target location should be registered: ' . $target;
		parent::__construct($message, 3);
	}
}
?>