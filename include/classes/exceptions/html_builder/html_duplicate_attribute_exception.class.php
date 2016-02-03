<?php
class HTML_Duplicate_Attribute_Exception extends HTML_Builder_Exception
{
	public function __construct($attribute, $value, $subcode = 7)
	{
		parent::__construct("Value of the \"$attribute\" attribute must be unique: \"$value\"", $subcode);
	}
}
?>