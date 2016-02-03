<?php
class File_Not_Found_Exception extends Exception
{
	public function __construct($file)
	{
		parent::__construct('File not found: ' . $file, 114);
	}
}
?>