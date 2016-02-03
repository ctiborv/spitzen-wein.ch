<?php
class Image_Handler_Exception extends Exception
{
	public function __construct($image, $message)
	{
		parent::__construct("Error while handling image: $image ($message)", 122);
	}
}
?>