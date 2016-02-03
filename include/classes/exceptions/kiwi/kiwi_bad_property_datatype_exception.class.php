<?php
class Kiwi_Bad_Property_DataType_Exception extends Kiwi_Exception
{
	public function __construct($datatype, $subcode = 6)
	{		
		parent::__construct('Bad property datatype: ' . $datatype, $subcode);
	}
}
?>