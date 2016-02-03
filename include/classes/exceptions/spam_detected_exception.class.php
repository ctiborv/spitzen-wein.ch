<?php
class Spam_Detected_Exception extends Exception
{
	public function __construct($message = '')
	{
		parent::__construct($message, 117);
	}
}
?>